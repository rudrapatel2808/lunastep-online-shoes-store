<?php
// backend/api/reviews.php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST, GET");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

include_once '../config/database.php';

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    if (isset($_GET['product_id'])) {
        $product_id = $_GET['product_id'];
        $query = "SELECT r.*, u.first_name, u.last_name FROM reviews r JOIN users u ON r.user_id = u.id WHERE r.product_id = ? ORDER BY r.created_at DESC";
        $stmt = $conn->prepare($query);
        $stmt->execute([$product_id]);
        $reviews = $stmt->fetchAll();
        
        echo json_encode($reviews);
    } else {
        http_response_code(400);
        echo json_encode(["message" => "Missing product_id."]);
    }
} elseif ($method === 'POST') {
    $data = json_decode(file_get_contents("php://input"));
    
    if (!empty($data->product_id) && !empty($data->user_id) && !empty($data->rating)) {
        $query = "INSERT INTO reviews (product_id, user_id, rating, comment) VALUES (:product_id, :user_id, :rating, :comment)";
        $stmt = $conn->prepare($query);
        
        $stmt->bindParam(':product_id', $data->product_id);
        $stmt->bindParam(':user_id', $data->user_id);
        $stmt->bindParam(':rating', $data->rating);
        
        $comment = isset($data->comment) ? $data->comment : null;
        $stmt->bindParam(':comment', $comment);
        
        if ($stmt->execute()) {
            http_response_code(201);
            echo json_encode(["message" => "Review added successfully."]);
        } else {
            http_response_code(503);
            echo json_encode(["message" => "Unable to add review."]);
        }
    } else {
        http_response_code(400);
        echo json_encode(["message" => "Incomplete data."]);
    }
} else {
    http_response_code(405);
    echo json_encode(["message" => "Method not allowed."]);
}
?>
