<?php

// Database Configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'library_db');

// Base URL
define('BASE_URL', '/library_system/');

// Database Connection
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if ($conn->connect_error) {
    die("Database Connection Failed: " . $conn->connect_error);
}

$conn->set_charset('utf8mb4');

// Start Session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Redirect Helper
function redirect($url)
{
    header("Location: $url");
    exit();
}

// Input Cleaning
function clean($conn, $value)
{
    return mysqli_real_escape_string($conn, trim($value));
}

// Login Check
function requireLogin()
{
    if (!isset($_SESSION['admin_id'])) {
        redirect(BASE_URL . 'login.php');
    }
}
?>