<?php
// backend/api/inventory.php — Inventory Management API
// GET: list variants with stock, PUT: update stock, POST: add variant

include_once __DIR__ . '/config.php';
include_once __DIR__ . '/webhooks.php';

$method = $_SERVER['REQUEST_METHOD'];

// ==========================================
// GET — List all product variants with stock info
// ==========================================
if ($method === 'GET') {
    // Inventory (stock levels, SKUs, thresholds) is staff-only business data
    requireRole(['admin', 'manager']);
    $query = "
        SELECT pv.*, p.name as product_name, p.brand, p.image_url, c.name as category_name
        FROM product_variants pv
        JOIN products p ON pv.product_id = p.id
        LEFT JOIN categories c ON p.category_id = c.id
    ";

    $conditions = [];
    $params = [];

    // Filter: low stock only
    if (isset($_GET['low_stock']) && $_GET['low_stock'] === 'true') {
        $conditions[] = "pv.stock_quantity <= pv.low_stock_threshold";
    }

    // Filter: by product
    if (isset($_GET['product_id'])) {
        $conditions[] = "pv.product_id = :product_id";
        $params[':product_id'] = $_GET['product_id'];
    }

    // Filter: search
    if (isset($_GET['search']) && $_GET['search'] !== '') {
        $searchTerm = '%' . $_GET['search'] . '%';
        $conditions[] = "(p.name LIKE :search OR pv.sku LIKE :search2)";
        $params[':search'] = $searchTerm;
        $params[':search2'] = $searchTerm;
    }

    if (!empty($conditions)) {
        $query .= " WHERE " . implode(" AND ", $conditions);
    }

    $query .= " ORDER BY pv.stock_quantity ASC";

    $stmt = $conn->prepare($query);
    foreach ($params as $key => $val) {
        $stmt->bindValue($key, $val);
    }
    $stmt->execute();
    $variants = $stmt->fetchAll();

    // Add computed fields
    foreach ($variants as &$v) {
        $v['is_low_stock'] = $v['stock_quantity'] <= $v['low_stock_threshold'];
        $v['stock_status'] = $v['stock_quantity'] <= 0 ? 'out_of_stock' :
                            ($v['stock_quantity'] <= $v['low_stock_threshold'] ? 'low_stock' : 'in_stock');
    }

    echo json_encode($variants);
}

// ==========================================
// PUT — Update stock quantity
// ==========================================
elseif ($method === 'PUT') {
    $user = requireRole(['admin', 'manager']);
    $data = getRequestBody();

    if (!empty($data->id)) {
        $fields = [];
        $params = [':id' => $data->id];

        if (isset($data->stock_quantity)) {
            $fields[] = "stock_quantity = :stock_quantity";
            $params[':stock_quantity'] = intval($data->stock_quantity);
        }
        if (isset($data->low_stock_threshold)) {
            $fields[] = "low_stock_threshold = :threshold";
            $params[':threshold'] = intval($data->low_stock_threshold);
        }

        if (empty($fields)) {
            jsonResponse(["message" => "No fields to update."], 400);
        }

        $query = "UPDATE product_variants SET " . implode(", ", $fields) . " WHERE id = :id";
        $stmt = $conn->prepare($query);
        foreach ($params as $key => $val) {
            $stmt->bindValue($key, $val);
        }

        if ($stmt->execute()) {
            // Log stock history if quantity changed
            if (isset($data->stock_quantity)) {
                $conn->prepare("INSERT INTO stock_history (variant_id, change_qty, reason) VALUES (?, ?, ?)")
                    ->execute([$data->id, intval($data->stock_quantity), "Manual update"]);
                // Low stock webhook
                $check = $conn->prepare("SELECT pv.*, p.name FROM product_variants pv JOIN products p ON pv.product_id = p.id WHERE pv.id = ? AND pv.stock_quantity <= pv.low_stock_threshold");
                $check->execute([$data->id]);
                if ($row = $check->fetch()) {
                    triggerWebhook('low_stock_alert', ['product' => $row['name'], 'sku' => $row['sku'], 'stock' => $row['stock_quantity']]);
                }
            }
            jsonResponse(["message" => "Stock updated successfully."]);
        } else {
            jsonResponse(["message" => "Unable to update stock."], 503);
        }
    } else {
        jsonResponse(["message" => "Variant ID is required."], 400);
    }
}

// ==========================================
// POST — Add new variant to a product
// ==========================================
elseif ($method === 'POST') {
    $user = requireRole(['admin', 'manager']);
    $data = getRequestBody();

    if (!empty($data->product_id) && !empty($data->size) && !empty($data->color) && !empty($data->sku)) {
        // Check for duplicate SKU
        $checkStmt = $conn->prepare("SELECT id FROM product_variants WHERE sku = ?");
        $checkStmt->execute([$data->sku]);
        if ($checkStmt->rowCount() > 0) {
            jsonResponse(["message" => "SKU already exists."], 400);
        }

        $query = "INSERT INTO product_variants (product_id, size, color, sku, stock_quantity, low_stock_threshold) 
                  VALUES (:product_id, :size, :color, :sku, :stock_quantity, :threshold)";
        $stmt = $conn->prepare($query);

        $stock = isset($data->stock_quantity) ? intval($data->stock_quantity) : 0;
        $threshold = isset($data->low_stock_threshold) ? intval($data->low_stock_threshold) : 5;

        $stmt->bindParam(':product_id', $data->product_id);
        $stmt->bindParam(':size', $data->size);
        $stmt->bindParam(':color', $data->color);
        $stmt->bindParam(':sku', $data->sku);
        $stmt->bindParam(':stock_quantity', $stock);
        $stmt->bindParam(':threshold', $threshold);

        if ($stmt->execute()) {
            jsonResponse(["message" => "Variant added successfully.", "id" => $conn->lastInsertId()], 201);
        } else {
            jsonResponse(["message" => "Unable to add variant."], 503);
        }
    } else {
        jsonResponse(["message" => "Product ID, size, color, and SKU are required."], 400);
    }
}

// ==========================================
// DELETE — Delete a variant
// ==========================================
elseif ($method === 'DELETE') {
    $user = requireRole(['admin', 'manager']);

    if (isset($_GET['id'])) {
        $stmt = $conn->prepare("DELETE FROM product_variants WHERE id = ?");
        if ($stmt->execute([$_GET['id']])) {
            jsonResponse(["message" => "Variant deleted successfully."]);
        } else {
            jsonResponse(["message" => "Unable to delete variant."], 503);
        }
    } else {
        jsonResponse(["message" => "Variant ID is required."], 400);
    }
}

else {
    jsonResponse(["message" => "Method not allowed."], 405);
}
?>
