<?php
// backend/config/database.php

$host = 'localhost';
$db_name = 'shoestore_db';
$username = 'root'; // default XAMPP/WAMP username
$password = ''; // default XAMPP/WAMP password

try {
    $conn = new PDO("mysql:host={$host};dbname={$db_name}", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    // Setting default fetch mode to associative array
    $conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch(PDOException $exception) {
    echo "Connection error: " . $exception->getMessage();
}
?>
