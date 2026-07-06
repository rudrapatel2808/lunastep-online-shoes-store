<?php
// backend/api/auth.php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

include_once '../config/database.php';

$data = json_decode(file_get_contents("php://input"));
$action = isset($_GET['action']) ? $_GET['action'] : '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($action === 'register') {
        if (!empty($data->first_name) && !empty($data->last_name) && !empty($data->email) && !empty($data->password)) {
            // Check if email exists
            $checkStmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
            $checkStmt->execute([$data->email]);
            if ($checkStmt->rowCount() > 0) {
                http_response_code(400);
                echo json_encode(["message" => "Email already registered."]);
                exit;
            }

            $query = "INSERT INTO users (first_name, last_name, email, password_hash, phone) VALUES (:first_name, :last_name, :email, :password_hash, :phone)";
            $stmt = $conn->prepare($query);

            $password_hash = password_hash($data->password, PASSWORD_DEFAULT);

            $stmt->bindParam(':first_name', $data->first_name);
            $stmt->bindParam(':last_name', $data->last_name);
            $stmt->bindParam(':email', $data->email);
            $stmt->bindParam(':password_hash', $password_hash);
            
            $phone = isset($data->phone) ? $data->phone : null;
            $stmt->bindParam(':phone', $phone);

            if ($stmt->execute()) {
                http_response_code(201);
                echo json_encode(["message" => "User was registered successfully."]);
            } else {
                http_response_code(503);
                echo json_encode(["message" => "Unable to register user."]);
            }
        } else {
            http_response_code(400);
            echo json_encode(["message" => "Incomplete data."]);
        }
    } elseif ($action === 'login') {
        if (!empty($data->email) && !empty($data->password)) {
            $query = "SELECT id, first_name, last_name, email, password_hash, role FROM users WHERE email = :email";
            $stmt = $conn->prepare($query);
            $stmt->bindParam(':email', $data->email);
            $stmt->execute();

            if ($stmt->rowCount() > 0) {
                $user = $stmt->fetch();
                if (password_verify($data->password, $user['password_hash'])) {
                    // Start session or generate token (simplified here)
                    session_start();
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['role'] = $user['role'];
                    
                    http_response_code(200);
                    echo json_encode([
                        "message" => "Login successful.",
                        "user" => [
                            "id" => $user['id'],
                            "first_name" => $user['first_name'],
                            "last_name" => $user['last_name'],
                            "email" => $user['email'],
                            "role" => $user['role']
                        ]
                    ]);
                } else {
                    http_response_code(401);
                    echo json_encode(["message" => "Invalid credentials."]);
                }
            } else {
                http_response_code(401);
                echo json_encode(["message" => "Invalid credentials."]);
            }
        } else {
            http_response_code(400);
            echo json_encode(["message" => "Incomplete data."]);
        }
    } else {
        http_response_code(400);
        echo json_encode(["message" => "Invalid action."]);
    }
} else {
    http_response_code(405);
    echo json_encode(["message" => "Method not allowed."]);
}
?>
