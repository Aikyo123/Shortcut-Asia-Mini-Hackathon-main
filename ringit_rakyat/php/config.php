<?php
// =============================================
// RingitRakyat - Database Configuration
// =============================================

define('DB_HOST', 'localhost');
define('DB_USER', 'root');       // Change if your MySQL user is different
define('DB_PASS', '');           // Change to your MySQL password (blank for default XAMPP)
define('DB_NAME', 'ringit_rakyat');

function getDB() {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    if ($conn->connect_error) {
        die(json_encode(['error' => 'Database connection failed: ' . $conn->connect_error]));
    }
    $conn->set_charset('utf8mb4');
    return $conn;
}

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Helper: check if logged in
function requireLogin() {
    if (!isset($_SESSION['user_id'])) {
        header('Location: ../login.html');
        exit();
    }
}

// Helper: return JSON
function jsonResponse($data, $code = 200) {
    http_response_code($code);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit();
}
?>
