<?php
// ==========================================
// ✅ Basic Site Config for Punjab Classified
// ==========================================

// Start session globally
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}



// Set default timezone (optional)
date_default_timezone_set("Asia/Kolkata");

// ==========================================
// ✅ Base URL (adjust this if you're in subfolder)
// ==========================================
$base_url = 'http://localhost:8000/';
define('POST_AD_URL', $base_url . 'ad-form.php');
define('ARTICLES_URL', $base_url . 'articles.php');
define('ARTICLES_POST_URL', $base_url . 'Blog-form.php');



 // ← change as per your Laragon folder name


// ==========================================
// ✅ Database Configuration
// ==========================================
$db_host = 'localhost';       // Usually localhost
$db_user = 'root';            // Default user in Laragon/XAMPP
$db_pass = '12345';                // Leave empty in Laragon/XAMPP
$db_name = 'live_pnb_cllsified_db'; // Your DB name

// Connect to MySQL
$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);

// Check connection
if ($conn->connect_error) {
    die("❌ Database connection failed: " . $conn->connect_error);
}

// Optional: Set charset
$conn->set_charset("utf8mb4");


// ✅ Fix path to functions.php
include_once(__DIR__ . DIRECTORY_SEPARATOR . 'functions.php');

?>