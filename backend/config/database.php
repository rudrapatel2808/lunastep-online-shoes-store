<?php
// backend/config/database.php

$host = 'localhost';
$db_name = 'shoestore_db';
$username = 'root'; // default XAMPP/WAMP username
$password = ''; // default XAMPP/WAMP password

try {
    $conn = new PDO(
        "mysql:host={$host};dbname={$db_name};charset=utf8mb4",
        $username,
        $password
    );
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    // Default fetch mode: associative array
    $conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    // Use real prepared statements (defense-in-depth against SQL injection)
    $conn->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
} catch (PDOException $exception) {
    // Never leak DB internals to the client
    error_log("DB connection failed: " . $exception->getMessage());
    http_response_code(503);
    echo json_encode(["message" => "Service temporarily unavailable. Please try again later."]);
    exit;
}
?>
