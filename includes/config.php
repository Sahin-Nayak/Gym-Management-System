<?php
// ============================================
// DATABASE CONFIGURATION
// ============================================

define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'gym_management');

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$conn->set_charset("utf8mb4");

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
date_default_timezone_set('Asia/Kolkata');
define('SITE_NAME', 'FitZone Gym');
define('SITE_URL', 'http://localhost/gym-management-system');
define('UPLOAD_PATH',           __DIR__ . '/../uploads/members/');
define('UPLOAD_PATH_TRAINERS',  __DIR__ . '/../uploads/trainers/');
define('UPLOAD_PATH_EQUIPMENT', __DIR__ . '/../uploads/equipment/');
define('UPLOAD_PATH_GALLERY',   __DIR__ . '/../uploads/gallery/');
define('UPLOAD_URL',            SITE_URL . '/uploads/members/');
define('UPLOAD_URL_TRAINERS',   SITE_URL . '/uploads/trainers/');
define('UPLOAD_URL_EQUIPMENT',  SITE_URL . '/uploads/equipment/');
define('UPLOAD_URL_GALLERY',    SITE_URL . '/uploads/gallery/');

function redirect($url) {
    header("Location: $url");
    exit();
}

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function isAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

function isTrainer() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'trainer';
}

function isMember() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'member';
}

function sanitize($data) {
    global $conn;
    return $conn->real_escape_string(htmlspecialchars(trim($data)));
}

function generateInvoiceNumber() {
    return 'INV-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -5));
}

function flashMessage($key, $message = null, $type = 'success') {
    if ($message) {
        $_SESSION['flash'][$key] = ['message' => $message, 'type' => $type];
    } elseif (isset($_SESSION['flash'][$key])) {
        $flash = $_SESSION['flash'][$key];
        unset($_SESSION['flash'][$key]);
        return $flash;
    }
    return null;
}
?>
