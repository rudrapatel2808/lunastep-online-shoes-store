<?php
// backend/api/products.php — Products API
// GET: list all, single by ID, search, with variants
// POST/PUT/DELETE: admin CRUD (Phase 4)

include_once __DIR__ . '/config.php';

$method = $_SERVER['REQUEST_METHOD'];

// ==========================================
// GET — List products or single product
// ==========================================
if ($method === 'GET') {
    if (isset($_GET['id'])) {
        // Get single product with variants
        $id = $_GET['id'];
        $stmt = $conn->prepare("
            SELECT p.*, c.name as category_name 
            FROM products p 
            LEFT JOIN categories c ON p.category_id = c.id 
            WHERE p.id = ?
        ");
        $stmt->execute([$id]);
        $product = $stmt->fetch();

        if ($product) {
            // Get variants for this product
            $varStmt = $conn->prepare("SELECT * FROM product_variants WHERE product_id = ?");
            $varStmt->execute([$id]);
            $product['variants'] = $varStmt->fetchAll();

            echo json_encode($product);
        } else {
            jsonResponse(["message" => "Product not found."], 404);
        }
    } else {
        // Get all products with optional filters
        $query = "SELECT p.*, c.name as category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id";
        $conditions = [];
        $params = [];

        // Category filter
        if (isset($_GET['category']) && $_GET['category'] !== '') {
            $conditions[] = "c.name = :category";
            $params[':category'] = $_GET['category'];
        }

        // Search filter
        if (isset($_GET['search']) && $_GET['search'] !== '') {
            $searchTerm = '%' . $_GET['search'] . '%';
            $conditions[] = "(p.name LIKE :search OR p.brand LIKE :search2 OR p.description LIKE :search3)";
            $params[':search'] = $searchTerm;
            $params[':search2'] = $searchTerm;
            $params[':search3'] = $searchTerm;
        }

        if (!empty($conditions)) {
            $query .= " WHERE " . implode(" AND ", $conditions);
        }

        $query .= " ORDER BY p.created_at DESC";

        // Pagination
        $limit = isset($_GET['limit']) ? intval($_GET['limit']) : 50;
        $offset = isset($_GET['offset']) ? intval($_GET['offset']) : 0;
        $query .= " LIMIT :lim OFFSET :off";

        $stmt = $conn->prepare($query);
        foreach ($params as $key => $val) {
            $stmt->bindValue($key, $val);
        }
        $stmt->bindValue(':lim', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':off', $offset, PDO::PARAM_INT);
        $stmt->execute();
        $products = $stmt->fetchAll();

        echo json_encode($products);
    }
}

// ==========================================
// POST — Create new product (Admin only)
// ==========================================
elseif ($method === 'POST') {
    $user = requireRole(['admin', 'manager']);
    $data = getRequestBody();

    if (!empty($data->name) && !empty($data->base_price)) {
        $query = "INSERT INTO products (category_id, brand, name, description, base_price, discount_price, image_url) 
                  VALUES (:category_id, :brand, :name, :description, :base_price, :discount_price, :image_url)";
        $stmt = $conn->prepare($query);

        $category_id = isset($data->category_id) ? $data->category_id : null;
        $brand = isset($data->brand) ? $data->brand : null;
        $description = isset($data->description) ? $data->description : null;
        $discount_price = isset($data->discount_price) ? $data->discount_price : null;
        $image_url = isset($data->image_url) ? $data->image_url : null;

        $stmt->bindParam(':category_id', $category_id);
        $stmt->bindParam(':brand', $brand);
        $stmt->bindParam(':name', $data->name);
        $stmt->bindParam(':description', $description);
        $stmt->bindParam(':base_price', $data->base_price);
        $stmt->bindParam(':discount_price', $discount_price);
        $stmt->bindParam(':image_url', $image_url);

        if ($stmt->execute()) {
            $newId = $conn->lastInsertId();
            jsonResponse(["message" => "Product created successfully.", "id" => $newId], 201);
        } else {
            jsonResponse(["message" => "Unable to create product."], 503);
        }
    } else {
        jsonResponse(["message" => "Product name and price are required."], 400);
    }
}

// ==========================================
// PUT — Update product (Admin only)
// ==========================================
elseif ($method === 'PUT') {
    $user = requireRole(['admin', 'manager']);
    $data = getRequestBody();

    if (!empty($data->id)) {
        $fields = [];
        $params = [':id' => $data->id];

        if (isset($data->name)) { $fields[] = "name = :name"; $params[':name'] = $data->name; }
        if (isset($data->brand)) { $fields[] = "brand = :brand"; $params[':brand'] = $data->brand; }
        if (isset($data->description)) { $fields[] = "description = :description"; $params[':description'] = $data->description; }
        if (isset($data->base_price)) { $fields[] = "base_price = :base_price"; $params[':base_price'] = $data->base_price; }
        if (isset($data->discount_price)) { $fields[] = "discount_price = :discount_price"; $params[':discount_price'] = $data->discount_price; }
        if (isset($data->category_id)) { $fields[] = "category_id = :category_id"; $params[':category_id'] = $data->category_id; }
        if (isset($data->image_url)) { $fields[] = "image_url = :image_url"; $params[':image_url'] = $data->image_url; }

        if (empty($fields)) {
            jsonResponse(["message" => "No fields to update."], 400);
        }

        $query = "UPDATE products SET " . implode(", ", $fields) . " WHERE id = :id";
        $stmt = $conn->prepare($query);
        foreach ($params as $key => $val) {
            $stmt->bindValue($key, $val);
        }

        if ($stmt->execute()) {
            jsonResponse(["message" => "Product updated successfully."]);
        } else {
            jsonResponse(["message" => "Unable to update product."], 503);
        }
    } else {
        jsonResponse(["message" => "Product ID is required."], 400);
    }
}

// ==========================================
// DELETE — Delete product (Admin only)
// ==========================================
elseif ($method === 'DELETE') {
    $user = requireRole(['admin', 'manager']);

    if (isset($_GET['id'])) {
        $stmt = $conn->prepare("DELETE FROM products WHERE id = ?");
        if ($stmt->execute([$_GET['id']])) {
            jsonResponse(["message" => "Product deleted successfully."]);
        } else {
            jsonResponse(["message" => "Unable to delete product."], 503);
        }
    } else {
        jsonResponse(["message" => "Product ID is required."], 400);
    }
}

else {
    jsonResponse(["message" => "Method not allowed."], 405);
}
?>
