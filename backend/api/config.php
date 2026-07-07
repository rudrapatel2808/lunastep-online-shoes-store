<?php
// backend/api/config.php — Shared API Configuration & Security
// Include this at the top of every API file

// ---- Hardened session cookie (set BEFORE session_start) ----
$isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
         || (($_SERVER['SERVER_PORT'] ?? '') == 443);
session_set_cookie_params([
    'lifetime' => 0,
    'path'     => '/',
    'httponly' => true,        // JS cannot read the session cookie (XSS mitigation)
    'samesite' => 'Lax',       // CSRF mitigation for cross-site requests
    'secure'   => $isHttps,
]);
session_start();

// ---- CORS — reflect only trusted origins (never '*' with credentials) ----
$allowedOrigins = [
    'http://localhost', 'http://127.0.0.1',
    'https://localhost', 'https://127.0.0.1',
    'http://localhost:5500', 'http://127.0.0.1:5500', // VS Code Live Server
];
$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
if ($origin !== '' && in_array($origin, $allowedOrigins, true)) {
    header("Access-Control-Allow-Origin: " . $origin);
    header("Vary: Origin");
    header("Access-Control-Allow-Credentials: true");
}

header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");

// ---- Security headers ----
header("X-Content-Type-Options: nosniff");
header("X-Frame-Options: DENY");
header("Referrer-Policy: same-origin");

// Handle preflight OPTIONS request
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'OPTIONS') {
    http_response_code(204);
    exit;
}

// Include database connection
include_once __DIR__ . '/../config/database.php';

// --- Helper Functions ---

/**
 * Send a JSON response and exit
 */
function jsonResponse($data, $statusCode = 200) {
    http_response_code($statusCode);
    echo json_encode($data);
    exit;
}

/**
 * Get and JSON-decode the request body. Returns an object (never null) so
 * property access is always safe.
 */
function getRequestBody() {
    $decoded = json_decode(file_get_contents("php://input"));
    return is_object($decoded) ? $decoded : new stdClass();
}

/**
 * Check if user is logged in, return user data or false
 */
function getLoggedInUser() {
    if (isset($_SESSION['user_id'])) {
        return [
            'id'         => $_SESSION['user_id'],
            'role'       => $_SESSION['role'] ?? 'customer',
            'email'      => $_SESSION['email'] ?? '',
            'first_name' => $_SESSION['first_name'] ?? ''
        ];
    }
    return false;
}

/**
 * Establish an authenticated session for a user (regenerates the session id
 * to prevent session fixation).
 */
function establishSession($user) {
    session_regenerate_id(true);
    $_SESSION['user_id']    = $user['id'];
    $_SESSION['role']       = $user['role'];
    $_SESSION['email']      = $user['email'];
    $_SESSION['first_name'] = $user['first_name'];
}

/**
 * Require login. Sends 401 and exits if not authenticated.
 */
function requireLogin() {
    $user = getLoggedInUser();
    if (!$user) {
        jsonResponse(["message" => "Authentication required."], 401);
    }
    return $user;
}

/**
 * Require a specific role. Sends 401/403 if not authorized.
 */
function requireRole($roles) {
    $user = getLoggedInUser();
    if (!$user) {
        jsonResponse(["message" => "Authentication required."], 401);
    }
    if (!is_array($roles)) $roles = [$roles];
    if (!in_array($user['role'], $roles)) {
        jsonResponse(["message" => "Access denied."], 403);
    }
    return $user;
}
?>
