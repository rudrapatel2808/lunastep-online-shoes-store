<?php
// backend/api/cart.php — Server-Side Cart API
// For logged-in users: GET, POST, PUT, DELETE
// Guest users continue using localStorage on frontend

include_once __DIR__ . '/config.php';

$method = $_SERVER['REQUEST_METHOD'];
$user = getLoggedInUser();

// Cart requires login
if (!$user) {
    jsonResponse(["message" => "Cart API requires login. Guest users use localStorage."], 200);
}

$userId = $user['id'];

// ==========================================
// GET — Get cart items for logged-in user
// ==========================================
if ($method === 'GET') {
    $query = "
        SELECT ci.*, pv.size, pv.color, pv.sku, pv.stock_quantity,
               p.name as product_name, p.base_price, p.discount_price, p.image_url, p.brand
        FROM cart_items ci
        JOIN product_variants pv ON ci.variant_id = pv.id
        JOIN products p ON pv.product_id = p.id
        WHERE ci.user_id = ?
        ORDER BY ci.created_at DESC
    ";
    $stmt = $conn->prepare($query);
    $stmt->execute([$userId]);
    $items = $stmt->fetchAll();

    // Calculate totals
    $subtotal = 0;
    foreach ($items as &$item) {
        $price = $item['discount_price'] ? floatval($item['discount_price']) : floatval($item['base_price']);
        $item['unit_price'] = $price;
        $item['line_total'] = $price * $item['quantity'];
        $subtotal += $item['line_total'];
    }

    echo json_encode([
        "items" => $items,
        "item_count" => count($items),
        "subtotal" => $subtotal
    ]);
}

// ==========================================
// POST — Add item to cart
// ==========================================
elseif ($method === 'POST') {
    $data = getRequestBody();

    if (!empty($data->variant_id)) {
        // Check if already in cart
        $checkStmt = $conn->prepare("SELECT id, quantity FROM cart_items WHERE user_id = ? AND variant_id = ?");
        $checkStmt->execute([$userId, $data->variant_id]);

        if ($checkStmt->rowCount() > 0) {
            // Update quantity
            $existing = $checkStmt->fetch();
            $newQty = $existing['quantity'] + (isset($data->quantity) ? intval($data->quantity) : 1);
            $updateStmt = $conn->prepare("UPDATE cart_items SET quantity = ? WHERE id = ?");
            $updateStmt->execute([$newQty, $existing['id']]);
            jsonResponse(["message" => "Cart updated.", "quantity" => $newQty]);
        } else {
            // Insert new
            $qty = isset($data->quantity) ? intval($data->quantity) : 1;
            $stmt = $conn->prepare("INSERT INTO cart_items (user_id, variant_id, quantity) VALUES (?, ?, ?)");
            if ($stmt->execute([$userId, $data->variant_id, $qty])) {
                jsonResponse(["message" => "Added to cart.", "id" => $conn->lastInsertId()], 201);
            } else {
                jsonResponse(["message" => "Unable to add to cart."], 503);
            }
        }
    } else {
        jsonResponse(["message" => "variant_id is required."], 400);
    }
}

// ==========================================
// PUT — Update cart item quantity
// ==========================================
elseif ($method === 'PUT') {
    $data = getRequestBody();

    if (!empty($data->id) && isset($data->quantity)) {
        if ($data->quantity <= 0) {
            // Remove item
            $stmt = $conn->prepare("DELETE FROM cart_items WHERE id = ? AND user_id = ?");
            $stmt->execute([$data->id, $userId]);
            jsonResponse(["message" => "Item removed from cart."]);
        } else {
            $stmt = $conn->prepare("UPDATE cart_items SET quantity = ? WHERE id = ? AND user_id = ?");
            $stmt->execute([intval($data->quantity), $data->id, $userId]);
            jsonResponse(["message" => "Cart updated."]);
        }
    } else {
        jsonResponse(["message" => "Item ID and quantity are required."], 400);
    }
}

// ==========================================
// DELETE — Remove item or clear cart
// ==========================================
elseif ($method === 'DELETE') {
    if (isset($_GET['id'])) {
        // Remove specific item
        $stmt = $conn->prepare("DELETE FROM cart_items WHERE id = ? AND user_id = ?");
        $stmt->execute([$_GET['id'], $userId]);
        jsonResponse(["message" => "Item removed from cart."]);
    } else {
        // Clear entire cart
        $stmt = $conn->prepare("DELETE FROM cart_items WHERE user_id = ?");
        $stmt->execute([$userId]);
        jsonResponse(["message" => "Cart cleared."]);
    }
}

else {
    jsonResponse(["message" => "Method not allowed."], 405);
}
?>
