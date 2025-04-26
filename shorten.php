<?php
session_start();
require_once 'db.php'; // Include DB connection and BASE_URL

// Function to generate a random short code
function generateShortCode($length = 6) {
    // Characters to use in the short code (alphanumeric)
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charLength - 1)];
    }
    return $randomString;
}

// Check if the form was submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['long_url'])) {

    $longUrl = trim($_POST['long_url']);

    // --- Validation ---
    if (empty($longUrl)) {
        $_SESSION['message'] = 'URL cannot be empty.';
        $_SESSION['message_type'] = 'error';
        header('Location: index.php');
        exit;
    }

    // Use filter_var for robust URL validation
    if (!filter_var($longUrl, FILTER_VALIDATE_URL)) {
        $_SESSION['message'] = 'Please enter a valid URL (including http:// or https://).';
        $_SESSION['message_type'] = 'error';
        header('Location: index.php');
        exit;
    }

    // --- Check if URL already exists (optional, can save generating new codes) ---
    // Note: This check might be slow on large datasets without indexing long_url
    /*
    $stmt = $pdo->prepare("SELECT short_code FROM urls WHERE long_url = :long_url LIMIT 1");
    $stmt->bindParam(':long_url', $longUrl);
    $stmt->execute();
    $existing = $stmt->fetch();

    if ($existing) {
        $_SESSION['short_url'] = BASE_URL . $existing['short_code'];
        $_SESSION['message'] = 'URL already shortened!';
        $_SESSION['message_type'] = 'success'; // Or 'info'
        header('Location: index.php');
        exit;
    }
    */

    // --- Generate a Unique Short Code ---
    $maxAttempts = 10; // Prevent infinite loops
    $attempt = 0;
    $shortCode = '';

    do {
        $shortCode = generateShortCode(6); // Generate a 6-character code
        // Check if this code already exists in the database
        $stmt = $pdo->prepare("SELECT id FROM urls WHERE short_code = :short_code LIMIT 1");
        $stmt->bindParam(':short_code', $shortCode);
        $stmt->execute();
        $codeExists = $stmt->fetch();
        $attempt++;
        if ($attempt > $maxAttempts) {
             // Handle the rare case where we couldn't find a unique code after several tries
             // Could increase code length, log an error, or use a different generation strategy
            $_SESSION['message'] = 'Error generating unique code. Please try again later.';
            $_SESSION['message_type'] = 'error';
            header('Location: index.php');
            // Consider logging this error for investigation
            error_log("Failed to generate unique short code for URL: " . $longUrl);
            exit;
        }
    } while ($codeExists); // Keep generating until a unique one is found

    // --- Insert into Database ---
    try {
        $stmt = $pdo->prepare("INSERT INTO urls (long_url, short_code) VALUES (:long_url, :short_code)");
        $stmt->bindParam(':long_url', $longUrl);
        $stmt->bindParam(':short_code', $shortCode);
        $stmt->execute();

        // Success! Store the short URL in the session and redirect back
        $_SESSION['short_url'] = BASE_URL . $shortCode;
        $_SESSION['message'] = 'URL shortened successfully!';
        $_SESSION['message_type'] = 'success';
        header('Location: index.php');
        exit;

    } catch (PDOException $e) {
        // Handle potential database errors (e.g., unique constraint violation if collision happens despite check - very rare)
        $_SESSION['message'] = 'Database error: Could not save URL. ' . $e->getMessage(); // Show specific error only in development
        // $_SESSION['message'] = 'Database error: Could not save URL. Please try again.'; // User-friendly production message
        $_SESSION['message_type'] = 'error';
        // Log the detailed error
        error_log("Database Error: " . $e->getMessage() . " for URL: " . $longUrl);
        header('Location: index.php');
        exit;
    }

} else {
    // If accessed directly or without POST data, redirect to the form
    header('Location: index.php');
    exit;
}
?>