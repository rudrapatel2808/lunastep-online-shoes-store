<?php
// backend/api/reviews.php — Reviews API
// GET: get reviews for a product, POST: submit review

include_once __DIR__ . '/config.php';

$method = $_SERVER['REQUEST_METHOD'];

// ==========================================
// GET — Get reviews for a product
// ==========================================
if ($method === 'GET') {
    if (isset($_GET['product_id'])) {
        $product_id = $_GET['product_id'];
        $query = "SELECT r.*, u.first_name, u.last_name 
                  FROM reviews r 
                  JOIN users u ON r.user_id = u.id 
                  WHERE r.product_id = ? 
                  ORDER BY r.created_at DESC";
        $stmt = $conn->prepare($query);
        $stmt->execute([$product_id]);
        $reviews = $stmt->fetchAll();

        // Also get average rating
        $avgStmt = $conn->prepare("SELECT AVG(rating) as avg_rating, COUNT(*) as total FROM reviews WHERE product_id = ?");
        $avgStmt->execute([$product_id]);
        $stats = $avgStmt->fetch();

        echo json_encode([
            "reviews" => $reviews,
            "avg_rating" => round($stats['avg_rating'], 1),
            "total_reviews" => intval($stats['total'])
        ]);
    } else {
        jsonResponse(["message" => "Missing product_id parameter."], 400);
    }
}

// ==========================================
// POST — Submit a review (logged-in customers)
// ==========================================
elseif ($method === 'POST') {
    $data = getRequestBody();

    // Reviews require login; the reviewer is ALWAYS the session user (no spoofing)
    $user = requireLogin();
    $user_id = $user['id'];

    if (!empty($data->product_id) && !empty($data->rating)) {
        // Validate rating
        $rating = intval($data->rating);
        if ($rating < 1 || $rating > 5) {
            jsonResponse(["message" => "Rating must be between 1 and 5."], 400);
        }

        // Check if user already reviewed this product
        $checkStmt = $conn->prepare("SELECT id FROM reviews WHERE product_id = ? AND user_id = ?");
        $checkStmt->execute([$data->product_id, $user_id]);
        if ($checkStmt->rowCount() > 0) {
            jsonResponse(["message" => "You have already reviewed this product."], 400);
        }

        $query = "INSERT INTO reviews (product_id, user_id, rating, comment) VALUES (:product_id, :user_id, :rating, :comment)";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':product_id', $data->product_id);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->bindParam(':rating', $rating);

        $comment = isset($data->comment) ? $data->comment : null;
        $stmt->bindParam(':comment', $comment);

        if ($stmt->execute()) {
            jsonResponse(["message" => "Review submitted successfully!"], 201);
        } else {
            jsonResponse(["message" => "Unable to submit review."], 503);
        }
    } else {
        jsonResponse(["message" => "Product ID, user ID, and rating are required."], 400);
    }
}

else {
    jsonResponse(["message" => "Method not allowed."], 405);
}
?>
