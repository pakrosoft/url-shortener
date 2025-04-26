<?php
// db.php

$dbHost = 'localhost';      // Or your database host
$dbName = 'url_shortener';  // Your database name
$dbUser = 'root';           // Your database username (use a dedicated user in production!)
$dbPass = '';               // Your database password (use a strong password in production!)

// Data Source Name (DSN)
$dsn = "mysql:host=$dbHost;dbname=$dbName;charset=utf8mb4";

$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // Turn on errors in the form of exceptions
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       // Make the default fetch be an associative array
    PDO::ATTR_EMULATE_PREPARES   => false,                  // Turn off emulation mode for real prepared statements
];

try {
    $pdo = new PDO($dsn, $dbUser, $dbPass, $options);
} catch (\PDOException $e) {
    // In a real application, you might log this error and show a user-friendly message
    throw new \PDOException($e->getMessage(), (int)$e->getCode());
    // For development, you might just die:
    // die("Database connection failed: " . $e->getMessage());
}

// Define the base URL for your shortener (IMPORTANT: Change this to your actual domain)
// Make sure it ends with a trailing slash '/'
define('BASE_URL', 'http://localhost/url-shortener/'); // Example for local XAMPP setup

?>