<?php
// backend/api/orders.php — Orders API
// POST: create order, PUT: update status, GET: list/filter

include_once __DIR__ . '/config.php';

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

            // Get user ID from session (null for guests)
            $user = getLoggedInUser();
            $user_id = $user ? $user['id'] : (isset($data->user_id) ? $data->user_id : null);

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

            foreach ($data->items as $item) {
                $variant_id = isset($item->variant_id) ? $item->variant_id : null;
                $item_stmt->bindParam(':order_id', $order_id);
                $item_stmt->bindParam(':variant_id', $variant_id);
                $item_stmt->bindParam(':quantity', $item->quantity);
                $item_stmt->bindParam(':price', $item->price);
                $item_stmt->execute();
            }

            $conn->commit();

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
            SELECT o.*, u.first_name, u.last_name 
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

    if (!empty($data->id) && !empty($data->status)) {
        $validStatuses = ['pending', 'processing', 'shipped', 'delivered', 'cancelled'];
        if (!in_array($data->status, $validStatuses)) {
            jsonResponse(["message" => "Invalid status. Must be one of: " . implode(', ', $validStatuses)], 400);
        }

        $stmt = $conn->prepare("UPDATE orders SET status = :status WHERE id = :id");
        $stmt->bindParam(':status', $data->status);
        $stmt->bindParam(':id', $data->id);

        if ($stmt->execute()) {
            jsonResponse(["message" => "Order status updated to '{$data->status}'."]);
        } else {
            jsonResponse(["message" => "Unable to update order status."], 503);
        }
    } else {
        jsonResponse(["message" => "Order ID and status are required."], 400);
    }
}

else {
    jsonResponse(["message" => "Method not allowed."], 405);
}
?>
