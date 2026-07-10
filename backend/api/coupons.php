<?php
// backend/api/coupons.php — Coupon API
include_once __DIR__ . '/config.php';

$method = $_SERVER['REQUEST_METHOD'];

// GET — validate a coupon or list all (admin)
if ($method === 'GET') {
    if (isset($_GET['code'])) {
        $stmt = $conn->prepare("SELECT * FROM coupons WHERE code = ? AND active = 1 AND (expires_at IS NULL OR expires_at > NOW()) AND (max_uses IS NULL OR times_used < max_uses)");
        $stmt->execute([$_GET['code']]);
        $coupon = $stmt->fetch();
        if ($coupon) { jsonResponse(["valid" => true, "coupon" => $coupon]); }
        else { jsonResponse(["valid" => false, "message" => "Invalid or expired coupon."], 404); }
    }
    requireRole('admin');
    $stmt = $conn->query("SELECT * FROM coupons ORDER BY created_at DESC");
    echo json_encode($stmt->fetchAll());
}
// POST — create coupon (admin)
elseif ($method === 'POST') {
    requireRole('admin');
    $data = getRequestBody();
    if (empty($data->code) || !isset($data->discount_percent)) jsonResponse(["message" => "code and discount_percent required."], 400);
    $stmt = $conn->prepare("INSERT INTO coupons (code, discount_percent, max_uses, expires_at) VALUES (?, ?, ?, ?)");
    $max = isset($data->max_uses) ? $data->max_uses : null;
    $exp = isset($data->expires_at) ? $data->expires_at : null;
    $stmt->execute([strtoupper($data->code), $data->discount_percent, $max, $exp]);
    jsonResponse(["message" => "Coupon created.", "id" => $conn->lastInsertId()], 201);
}
// PUT — increment usage (called after order with coupon)
elseif ($method === 'PUT') {
    $data = getRequestBody();
    if (empty($data->code)) jsonResponse(["message" => "code required."], 400);
    $stmt = $conn->prepare("UPDATE coupons SET times_used = times_used + 1 WHERE code = ?");
    $stmt->execute([$data->code]);
    jsonResponse(["message" => "Coupon usage recorded."]);
}
// DELETE — deactivate coupon (admin)
elseif ($method === 'DELETE') {
    requireRole('admin');
    if (!isset($_GET['id'])) jsonResponse(["message" => "Coupon id required."], 400);
    $stmt = $conn->prepare("UPDATE coupons SET active = 0 WHERE id = ?");
    $stmt->execute([$_GET['id']]);
    jsonResponse(["message" => "Coupon deactivated."]);
}
else { jsonResponse(["message" => "Method not allowed."], 405); }
?>
