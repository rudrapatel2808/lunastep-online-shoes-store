<?php
// backend/api/orders.php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST, GET, PUT");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

include_once '../config/database.php';

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'POST') {
    // Create new order
    $data = json_decode(file_get_contents("php://input"));
    
    if (!empty($data->items) && !empty($data->total_amount) && !empty($data->shipping_address) && !empty($data->payment_method)) {
        try {
            $conn->beginTransaction();
            
            // Insert order
            $order_number = 'ORD-' . strtoupper(uniqid());
            $query = "INSERT INTO orders (user_id, order_number, total_amount, shipping_address, payment_method) VALUES (:user_id, :order_number, :total, :address, :payment)";
            $stmt = $conn->prepare($query);
            
            $user_id = isset($data->user_id) ? $data->user_id : null; // Guest if null
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
                $variant_id = isset($item->variant_id) ? $item->variant_id : null; // In a full implementation, you'd resolve the variant ID
                
                $item_stmt->bindParam(':order_id', $order_id);
                $item_stmt->bindParam(':variant_id', $variant_id);
                $item_stmt->bindParam(':quantity', $item->quantity);
                $item_stmt->bindParam(':price', $item->price);
                $item_stmt->execute();
            }
            
            $conn->commit();
            
            http_response_code(201);
            echo json_encode([
                "message" => "Order placed successfully.",
                "order_number" => $order_number
            ]);
            
        } catch (Exception $e) {
            $conn->rollBack();
            http_response_code(503);
            echo json_encode(["message" => "Unable to place order: " . $e->getMessage()]);
        }
    } else {
        http_response_code(400);
        echo json_encode(["message" => "Incomplete order data."]);
    }
} elseif ($method === 'GET') {
    // Get orders (simplified)
    $query = "SELECT * FROM orders ORDER BY created_at DESC";
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $orders = $stmt->fetchAll();
    echo json_encode($orders);
} else {
    http_response_code(405);
    echo json_encode(["message" => "Method not allowed."]);
}
?>
