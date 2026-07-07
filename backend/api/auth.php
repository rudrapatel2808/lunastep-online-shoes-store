<?php
// backend/api/auth.php — Authentication API
// Endpoints: register, login, admin-login, logout, me

include_once __DIR__ . '/config.php';

$data = getRequestBody();
$action = isset($_GET['action']) ? $_GET['action'] : '';
$method = $_SERVER['REQUEST_METHOD'];

// ==========================================
// GET actions
// ==========================================
if ($method === 'GET') {
    if ($action === 'me') {
        // Return current logged-in user info
        $user = getLoggedInUser();
        if ($user) {
            jsonResponse(["loggedIn" => true, "user" => $user]);
        } else {
            jsonResponse(["loggedIn" => false], 200);
        }
    }

    // --- FULL PROFILE (logged-in user, from DB) ---
    if ($action === 'profile') {
        $sessionUser = getLoggedInUser();
        if (!$sessionUser) {
            jsonResponse(["loggedIn" => false, "message" => "Not logged in."], 401);
        }
        $stmt = $conn->prepare("SELECT id, first_name, last_name, email, phone, role, created_at FROM users WHERE id = ?");
        $stmt->execute([$sessionUser['id']]);
        $user = $stmt->fetch();
        if ($user) {
            jsonResponse(["loggedIn" => true, "user" => $user]);
        } else {
            jsonResponse(["loggedIn" => false, "message" => "User not found."], 404);
        }
    }

    jsonResponse(["message" => "Invalid action."], 400);
}

// ==========================================
// POST actions
// ==========================================
if ($method === 'POST') {

    // --- REGISTER (Customer) ---
    if ($action === 'register') {
        if (!empty($data->first_name) && !empty($data->last_name) && !empty($data->email) && !empty($data->password)) {
            // Validate input
            if (!filter_var($data->email, FILTER_VALIDATE_EMAIL)) {
                jsonResponse(["message" => "Please enter a valid email address."], 400);
            }
            if (strlen($data->password) < 6) {
                jsonResponse(["message" => "Password must be at least 6 characters."], 400);
            }
            // Check if email exists
            $checkStmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
            $checkStmt->execute([$data->email]);
            if ($checkStmt->rowCount() > 0) {
                jsonResponse(["message" => "Email already registered."], 400);
            }

            $query = "INSERT INTO users (first_name, last_name, email, password_hash, phone, role) VALUES (:first_name, :last_name, :email, :password_hash, :phone, 'customer')";
            $stmt = $conn->prepare($query);

            $password_hash = password_hash($data->password, PASSWORD_DEFAULT);
            $phone = isset($data->phone) ? $data->phone : null;

            $stmt->bindParam(':first_name', $data->first_name);
            $stmt->bindParam(':last_name', $data->last_name);
            $stmt->bindParam(':email', $data->email);
            $stmt->bindParam(':password_hash', $password_hash);
            $stmt->bindParam(':phone', $phone);

            if ($stmt->execute()) {
                jsonResponse(["message" => "Account created successfully!"], 201);
            } else {
                jsonResponse(["message" => "Unable to register user."], 503);
            }
        } else {
            jsonResponse(["message" => "Please fill in all required fields."], 400);
        }
    }

    // --- LOGIN (Customer) ---
    elseif ($action === 'login') {
        if (!empty($data->email) && !empty($data->password)) {
            $query = "SELECT id, first_name, last_name, email, password_hash, role FROM users WHERE email = :email";
            $stmt = $conn->prepare($query);
            $stmt->bindParam(':email', $data->email);
            $stmt->execute();

            if ($stmt->rowCount() > 0) {
                $user = $stmt->fetch();
                if (password_verify($data->password, $user['password_hash'])) {
                    // Set session
                    establishSession($user);

                    jsonResponse([
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
                    jsonResponse(["message" => "Invalid email or password."], 401);
                }
            } else {
                jsonResponse(["message" => "Invalid email or password."], 401);
            }
        } else {
            jsonResponse(["message" => "Email and password are required."], 400);
        }
    }

    // --- ADMIN / MANAGER LOGIN ---
    elseif ($action === 'admin-login') {
        if (!empty($data->email) && !empty($data->password)) {
            $query = "SELECT id, first_name, last_name, email, password_hash, role FROM users WHERE email = :email AND role IN ('admin', 'manager')";
            $stmt = $conn->prepare($query);
            $stmt->bindParam(':email', $data->email);
            $stmt->execute();

            if ($stmt->rowCount() > 0) {
                $user = $stmt->fetch();
                if (password_verify($data->password, $user['password_hash'])) {
                    establishSession($user);

                    jsonResponse([
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
                    jsonResponse(["message" => "Invalid credentials."], 401);
                }
            } else {
                jsonResponse(["message" => "Invalid credentials or insufficient privileges."], 401);
            }
        } else {
            jsonResponse(["message" => "Email and password are required."], 400);
        }
    }

    // --- UPDATE PROFILE (logged-in user) ---
    elseif ($action === 'update-profile') {
        $sessionUser = getLoggedInUser();
        if (!$sessionUser) {
            jsonResponse(["message" => "Not logged in."], 401);
        }

        $fields = [];
        $params = [':id' => $sessionUser['id']];

        if (isset($data->first_name) && trim($data->first_name) !== '') { $fields[] = "first_name = :first_name"; $params[':first_name'] = $data->first_name; }
        if (isset($data->last_name)  && trim($data->last_name)  !== '') { $fields[] = "last_name = :last_name";   $params[':last_name']  = $data->last_name; }
        if (isset($data->phone)) { $fields[] = "phone = :phone"; $params[':phone'] = $data->phone; }
        if (!empty($data->password)) {
            if (strlen($data->password) < 6) { jsonResponse(["message" => "Password must be at least 6 characters."], 400); }
            $fields[] = "password_hash = :password_hash";
            $params[':password_hash'] = password_hash($data->password, PASSWORD_DEFAULT);
        }

        if (empty($fields)) { jsonResponse(["message" => "No changes provided."], 400); }

        $query = "UPDATE users SET " . implode(", ", $fields) . " WHERE id = :id";
        $stmt = $conn->prepare($query);
        foreach ($params as $k => $v) { $stmt->bindValue($k, $v); }

        if ($stmt->execute()) {
            // Keep the session's display name in sync
            if (isset($params[':first_name'])) $_SESSION['first_name'] = $params[':first_name'];
            $sel = $conn->prepare("SELECT id, first_name, last_name, email, phone, role, created_at FROM users WHERE id = ?");
            $sel->execute([$sessionUser['id']]);
            jsonResponse(["message" => "Profile updated successfully.", "user" => $sel->fetch()]);
        } else {
            jsonResponse(["message" => "Unable to update profile."], 503);
        }
    }

    // --- LOGOUT ---
    elseif ($action === 'logout') {
        session_unset();
        session_destroy();
        jsonResponse(["message" => "Logged out successfully."]);
    }

    else {
        jsonResponse(["message" => "Invalid action."], 400);
    }
} else {
    jsonResponse(["message" => "Method not allowed."], 405);
}
?>
