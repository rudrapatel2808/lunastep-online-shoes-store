<?php
// backend/api/users.php — Admin User Management API
include_once __DIR__ . '/config.php';

$method = $_SERVER['REQUEST_METHOD'];
requireRole('admin');

if ($method === 'GET') {
    if (isset($_GET['id'])) {
        $stmt = $conn->prepare("SELECT id, first_name, last_name, email, phone, role, created_at FROM users WHERE id = ?");
        $stmt->execute([$_GET['id']]);
        $user = $stmt->fetch();
        $user ? jsonResponse($user) : jsonResponse(["message" => "User not found."], 404);
    }
    $search = isset($_GET['search']) ? '%' . $_GET['search'] . '%' : null;
    $role = $_GET['role'] ?? null;
    $q = "SELECT id, first_name, last_name, email, phone, role, created_at FROM users WHERE 1=1";
    $p = [];
    if ($role) { $q .= " AND role = ?"; $p[] = $role; }
    if ($search) { $q .= " AND (first_name LIKE ? OR last_name LIKE ? OR email LIKE ?)"; array_push($p, $search, $search, $search); }
    $q .= " ORDER BY created_at DESC";
    $stmt = $conn->prepare($q);
    $stmt->execute($p);
    echo json_encode($stmt->fetchAll());
}
elseif ($method === 'PUT') {
    $data = getRequestBody();
    if (empty($data->id)) jsonResponse(["message" => "User id required."], 400);
    $fields = []; $params = [];
    if (isset($data->role)) { $fields[] = "role = ?"; $params[] = $data->role; }
    if (isset($data->first_name)) { $fields[] = "first_name = ?"; $params[] = $data->first_name; }
    if (isset($data->last_name)) { $fields[] = "last_name = ?"; $params[] = $data->last_name; }
    if (isset($data->phone)) { $fields[] = "phone = ?"; $params[] = $data->phone; }
    if (empty($fields)) jsonResponse(["message" => "Nothing to update."], 400);
    $params[] = $data->id;
    $stmt = $conn->prepare("UPDATE users SET " . implode(", ", $fields) . " WHERE id = ?");
    $stmt->execute($params);
    jsonResponse(["message" => "User updated."]);
}
elseif ($method === 'DELETE') {
    if (!isset($_GET['id'])) jsonResponse(["message" => "User id required."], 400);
    $stmt = $conn->prepare("DELETE FROM users WHERE id = ? AND role != 'admin'");
    $stmt->execute([$_GET['id']]);
    jsonResponse(["message" => $stmt->rowCount() ? "User deleted." : "Cannot delete admin or user not found."]);
}
else { jsonResponse(["message" => "Method not allowed."], 405); }
?>
