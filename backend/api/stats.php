<?php
// backend/api/stats.php — Dashboard Statistics (Admin only)

include_once __DIR__ . '/config.php';

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    // Dashboard statistics are staff-only (revenue, customer counts, etc.)
    requireRole(['admin', 'manager']);

    // Total revenue
    $revStmt = $conn->query("SELECT COALESCE(SUM(total_amount), 0) as total_revenue FROM orders WHERE status != 'cancelled'");
    $revenue = $revStmt->fetch()['total_revenue'];

    // Today's revenue
    $todayStmt = $conn->query("SELECT COALESCE(SUM(total_amount), 0) as today_revenue FROM orders WHERE DATE(created_at) = CURDATE() AND status != 'cancelled'");
    $todayRevenue = $todayStmt->fetch()['today_revenue'];

    // Total orders
    $ordStmt = $conn->query("SELECT COUNT(*) as total_orders FROM orders");
    $totalOrders = $ordStmt->fetch()['total_orders'];

    // Total customers
    $custStmt = $conn->query("SELECT COUNT(*) as total_customers FROM users WHERE role = 'customer'");
    $totalCustomers = $custStmt->fetch()['total_customers'];

    // Orders by status
    $statusStmt = $conn->query("SELECT status, COUNT(*) as count FROM orders GROUP BY status");
    $ordersByStatus = [];
    while ($row = $statusStmt->fetch()) {
        $ordersByStatus[$row['status']] = intval($row['count']);
    }

    // Low stock count
    $lowStockStmt = $conn->query("SELECT COUNT(*) as low_stock FROM product_variants WHERE stock_quantity <= low_stock_threshold");
    $lowStock = $lowStockStmt->fetch()['low_stock'];

    jsonResponse([
        "total_revenue" => floatval($revenue),
        "today_revenue" => floatval($todayRevenue),
        "total_orders" => intval($totalOrders),
        "total_customers" => intval($totalCustomers),
        "orders_by_status" => $ordersByStatus,
        "low_stock_alerts" => intval($lowStock)
    ]);
} else {
    jsonResponse(["message" => "Method not allowed."], 405);
}
?>
