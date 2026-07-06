<?php
// backend/api/products.php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

include_once '../config/database.php';

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    if (isset($_GET['id'])) {
        // Get single product
        $id = $_GET['id'];
        $stmt = $conn->prepare("SELECT p.*, c.name as category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id WHERE p.id = ?");
        $stmt->execute([$id]);
        $product = $stmt->fetch();
        
        if ($product) {
            echo json_encode($product);
        } else {
            http_response_code(404);
            echo json_encode(["message" => "Product not found."]);
        }
    } else {
        // Get all products
        $query = "SELECT p.*, c.name as category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id";
        
        // Handle category filter
        if (isset($_GET['category'])) {
            $category = $_GET['category'];
            $query .= " WHERE c.name = :category";
        }
        
        $stmt = $conn->prepare($query);
        
        if (isset($_GET['category'])) {
            $stmt->bindParam(':category', $_GET['category']);
        }
        
        $stmt->execute();
        $products = $stmt->fetchAll();
        
        echo json_encode($products);
    }
} else {
    http_response_code(405);
    echo json_encode(["message" => "Method not allowed."]);
}
?>
