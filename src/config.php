<?php
session_start();

// Paths
define('BASE_PATH', realpath(__DIR__ . '/../') . '/');
define('PUBLIC_PATH', BASE_PATH . 'public/');
define('UPLOAD_DIR', PUBLIC_PATH . 'uploads/');

// DB credentials - change if needed
$dbHost = '127.0.0.1';
$dbName = 'social_network';
$dbUser = 'root';
$dbPass = ''; // if using XAMPP default is empty

// PDO options
$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
];

try {
    $pdo = new PDO("mysql:host=$dbHost;dbname=$dbName;charset=utf8mb4", $dbUser, $dbPass, $options);
} catch (PDOException $e) {
    die('DB Connection failed: ' . $e->getMessage());
}
error_reporting(E_ALL);
ini_set('display_errors', 1);
