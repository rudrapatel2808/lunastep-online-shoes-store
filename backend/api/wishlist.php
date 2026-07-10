<?php
// backend/api/wishlist.php — Wishlist API (logged-in customers)
include_once __DIR__ . '/config.php';

$method = $_SERVER['REQUEST_METHOD'];
$user = requireLogin();

if ($method === 'GET') {
    $stmt = $conn->prepare("SELECT w.*, p.name, p.brand, p.base_price, p.discount_price, p.image_url
        FROM wishlist w JOIN products p ON w.product_id = p.id WHERE w.user_id = ? ORDER BY w.created_at DESC");
    $stmt->execute([$user['id']]);
    echo json_encode($stmt->fetchAll());
}
elseif ($method === 'POST') {
    $data = getRequestBody();
    if (empty($data->product_id)) jsonResponse(["message" => "product_id required."], 400);
    $stmt = $conn->prepare("INSERT IGNORE INTO wishlist (user_id, product_id) VALUES (?, ?)");
    $stmt->execute([$user['id'], $data->product_id]);
    jsonResponse(["message" => "Added to wishlist."], 201);
}
elseif ($method === 'DELETE') {
    if (!isset($_GET['product_id'])) jsonResponse(["message" => "product_id required."], 400);
    $stmt = $conn->prepare("DELETE FROM wishlist WHERE user_id = ? AND product_id = ?");
    $stmt->execute([$user['id'], $_GET['product_id']]);
    jsonResponse(["message" => "Removed from wishlist."]);
}
else { jsonResponse(["message" => "Method not allowed."], 405); }
?>
