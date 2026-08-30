<?php
// backend/api/orders.php — Orders API
// POST: create order, PUT: update status, GET: list/filter

include_once __DIR__ . '/config.php';
include_once __DIR__ . '/webhooks.php';

$method = $_SERVER['REQUEST_METHOD'];

// ==========================================
// POST — Create new order
// ==========================================
if ($method === 'POST') {
    $data = getRequestBody();

    if (!empty($data->items) && !empty($data->total_amount) && !empty($data->shipping_address) && !empty($data->payment_method)) {
        try {
            $conn->beginTransaction();

            // Generate order number
            $order_number = 'ORD-' . strtoupper(uniqid());

            // Get user ID from session only (never trust client-supplied user_id)
            $user = getLoggedInUser();
            $user_id = $user ? $user['id'] : null;

            $query = "INSERT INTO orders (user_id, order_number, total_amount, shipping_address, payment_method) 
                      VALUES (:user_id, :order_number, :total, :address, :payment)";
            $stmt = $conn->prepare($query);
            $stmt->bindParam(':user_id', $user_id);
            $stmt->bindParam(':order_number', $order_number);
            $stmt->bindParam(':total', $data->total_amount);
            $stmt->bindParam(':address', $data->shipping_address);
            $stmt->bindParam(':payment', $data->payment_method);
            $stmt->execute();

            $order_id = $conn->lastInsertId();

            // Insert order items
            $item_query = "INSERT INTO order_items (order_id, variant_id, quantity, price_at_purchase) VALUES (:order_id, :variant_id, :quantity, :price)";
            $item_stmt = $conn->prepare($item_query);

            // Prepared statements for stock deduction & history
            $stock_stmt = $conn->prepare("UPDATE product_variants SET stock_quantity = stock_quantity - ? WHERE id = ? AND stock_quantity >= ?");
            $hist_stmt = $conn->prepare("INSERT INTO stock_history (variant_id, change_qty, reason) VALUES (?, ?, ?)");

            foreach ($data->items as $item) {
                $variant_id = isset($item->variant_id) ? $item->variant_id : null;
                $qty = intval($item->quantity);
                $item_stmt->bindParam(':order_id', $order_id);
                $item_stmt->bindParam(':variant_id', $variant_id);
                $item_stmt->bindParam(':quantity', $qty);
                $item_stmt->bindParam(':price', $item->price);
                $item_stmt->execute();

                // Deduct stock & log history
                if ($variant_id) {
                    $stock_stmt->execute([$qty, $variant_id, $qty]);
                    if ($stock_stmt->rowCount() === 0) {
                        throw new Exception("Insufficient stock for variant #{$variant_id}");
                    }
                    $hist_stmt->execute([$variant_id, -$qty, "Order {$order_number}"]);
                }
            }

            $conn->commit();

            // Fire n8n webhook
            triggerWebhook('order_placed', ['order_number' => $order_number, 'total' => $data->total_amount]);

            jsonResponse([
                "message" => "Order placed successfully!",
                "order_number" => $order_number,
                "order_id" => $order_id
            ], 201);

        } catch (Exception $e) {
            $conn->rollBack();
            error_log("Order creation failed: " . $e->getMessage());
            jsonResponse(["message" => "Unable to place order. Please try again."], 503);
        }
    } else {
        jsonResponse(["message" => "Incomplete order data."], 400);
    }
}

// ==========================================
// GET — List orders
// ==========================================
elseif ($method === 'GET') {
    // Single order by ID
    if (isset($_GET['id'])) {
        $stmt = $conn->prepare("
            SELECT o.*, u.first_name, u.last_name, u.email as customer_email
            FROM orders o 
            LEFT JOIN users u ON o.user_id = u.id 
            WHERE o.id = ?
        ");
        $stmt->execute([$_GET['id']]);
        $order = $stmt->fetch();

        if ($order) {
            // Only the owner or staff may view an order
            $viewer = requireLogin();
            if (!in_array($viewer['role'], ['admin', 'manager'], true) && (string)$order['user_id'] !== (string)$viewer['id']) {
                jsonResponse(["message" => "Access denied."], 403);
            }
            // Get order items
            $itemStmt = $conn->prepare("
                SELECT oi.*, p.name as product_name, p.image_url, pv.size, pv.color
                FROM order_items oi 
                LEFT JOIN product_variants pv ON oi.variant_id = pv.id 
                LEFT JOIN products p ON pv.product_id = p.id
                WHERE oi.order_id = ?
            ");
            $itemStmt->execute([$order['id']]);
            $order['items'] = $itemStmt->fetchAll();

            echo json_encode($order);
        } else {
            jsonResponse(["message" => "Order not found."], 404);
        }
    }
    // Filter by user_id (own orders, or any if staff)
    elseif (isset($_GET['user_id'])) {
        $viewer = requireLogin();
        $targetId = $_GET['user_id'];
        if (!in_array($viewer['role'], ['admin', 'manager'], true) && (string)$targetId !== (string)$viewer['id']) {
            jsonResponse(["message" => "Access denied."], 403);
        }
        $stmt = $conn->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC");
        $stmt->execute([$targetId]);
        echo json_encode($stmt->fetchAll());
    }
    // All orders (admin/manager only)
    else {
        requireRole(['admin', 'manager']);
        $query = "
            SELECT o.*, u.first_name, u.last_name,
                   CONCAT(COALESCE(u.first_name,''), ' ', COALESCE(u.last_name,'')) as customer_name,
                   u.email as customer_email
            FROM orders o 
            LEFT JOIN users u ON o.user_id = u.id 
            ORDER BY o.created_at DESC
        ";
        
        // Limit results
        $limit = isset($_GET['limit']) ? intval($_GET['limit']) : 50;
        $query .= " LIMIT " . $limit;

        $stmt = $conn->prepare($query);
        $stmt->execute();
        $orders = $stmt->fetchAll();

        // For each order, get the first item name for display
        foreach ($orders as &$order) {
            $itemStmt = $conn->prepare("
                SELECT p.name 
                FROM order_items oi 
                LEFT JOIN product_variants pv ON oi.variant_id = pv.id 
                LEFT JOIN products p ON pv.product_id = p.id
                WHERE oi.order_id = ? 
                LIMIT 1
            ");
            $itemStmt->execute([$order['id']]);
            $item = $itemStmt->fetch();
            $order['first_item_name'] = $item ? $item['name'] : 'Unknown';
        }

        echo json_encode($orders);
    }
}

// ==========================================
// PUT — Update order status (Admin only)
// ==========================================
elseif ($method === 'PUT') {
    $user = requireRole(['admin', 'manager']);
    $data = getRequestBody();

    // Accept id from URL param OR body
    $orderId = !empty($_GET['id']) ? intval($_GET['id']) : (isset($data->id) ? intval($data->id) : null);
    if (!$orderId) jsonResponse(["message" => "Order ID is required."], 400);
    $data->id = $orderId;

    $fields = []; $params = [];

    if (!empty($data->status)) {
        $valid = ['pending', 'processing', 'shipped', 'delivered', 'cancelled'];
        if (!in_array($data->status, $valid)) jsonResponse(["message" => "Invalid status."], 400);
        $fields[] = "status = ?"; $params[] = $data->status;
    }
    if (isset($data->tracking_number)) {
        $fields[] = "tracking_number = ?"; $params[] = $data->tracking_number;
    }
    if (empty($fields)) jsonResponse(["message" => "Nothing to update."], 400);

    $params[] = $data->id;
    $stmt = $conn->prepare("UPDATE orders SET " . implode(", ", $fields) . " WHERE id = ?");
    $stmt->execute($params);

    // Fire webhook on status change
    if (!empty($data->status)) {
        triggerWebhook('order_' . $data->status, ['order_id' => $data->id, 'status' => $data->status]);
    }
    jsonResponse(["message" => "Order updated."]);    
}

else {
    jsonResponse(["message" => "Method not allowed."], 405);
}
?>
