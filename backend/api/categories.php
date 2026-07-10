<?php
// backend/api/categories.php — Categories CRUD
include_once __DIR__ . '/config.php';

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $stmt = $conn->query("SELECT * FROM categories ORDER BY name");
    echo json_encode($stmt->fetchAll());
}
elseif ($method === 'POST') {
    requireRole('admin');
    $data = getRequestBody();
    if (empty($data->name)) jsonResponse(["message" => "Category name required."], 400);
    $desc = isset($data->description) ? $data->description : null;
    $stmt = $conn->prepare("INSERT INTO categories (name, description) VALUES (?, ?)");
    $stmt->execute([$data->name, $desc]);
    jsonResponse(["message" => "Category created.", "id" => $conn->lastInsertId()], 201);
}
elseif ($method === 'PUT') {
    requireRole('admin');
    $data = getRequestBody();
    if (empty($data->id)) jsonResponse(["message" => "Category id required."], 400);
    $fields = []; $params = [];
    if (isset($data->name)) { $fields[] = "name = ?"; $params[] = $data->name; }
    if (isset($data->description)) { $fields[] = "description = ?"; $params[] = $data->description; }
    if (empty($fields)) jsonResponse(["message" => "Nothing to update."], 400);
    $params[] = $data->id;
    $stmt = $conn->prepare("UPDATE categories SET " . implode(", ", $fields) . " WHERE id = ?");
    $stmt->execute($params);
    jsonResponse(["message" => "Category updated."]);
}
elseif ($method === 'DELETE') {
    requireRole('admin');
    if (!isset($_GET['id'])) jsonResponse(["message" => "Category id required."], 400);
    $stmt = $conn->prepare("DELETE FROM categories WHERE id = ?");
    $stmt->execute([$_GET['id']]);
    jsonResponse(["message" => "Category deleted."]);
}
else { jsonResponse(["message" => "Method not allowed."], 405); }
?>
