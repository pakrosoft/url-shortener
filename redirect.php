<?php
require_once 'db.php'; // Include DB connection

// Check if a 'code' parameter is provided in the URL
if (isset($_GET['code']) && !empty($_GET['code'])) {

    $shortCode = trim($_GET['code']);

    // Basic sanitization (ensure it matches expected format, e.g., alphanumeric)
    // This regex should match the characters used in generateShortCode() and the .htaccess RewriteRule
    if (!preg_match('/^[a-zA-Z0-9]+$/', $shortCode)) {
         // Code contains invalid characters
         header("HTTP/1.0 400 Bad Request");
         echo "Invalid short code format.";
         exit;
    }


    try {
        // Prepare SQL statement to find the long URL
        $stmt = $pdo->prepare("SELECT long_url FROM urls WHERE short_code = :short_code LIMIT 1");
        $stmt->bindParam(':short_code', $shortCode);
        $stmt->execute();

        $result = $stmt->fetch(); // Fetch the result as an associative array

        if ($result) {
            // Found the URL!
            $longUrl = $result['long_url'];

            // --- Optional: Increment click count ---
            $updateStmt = $pdo->prepare("UPDATE urls SET clicks = clicks + 1 WHERE short_code = :short_code");
            $updateStmt->bindParam(':short_code', $shortCode);
            $updateStmt->execute();
            // --- End Optional Click Count ---


            // Perform the redirect
            // Use 301 Moved Permanently for better SEO if the mapping is permanent
            // Use 302 Found for temporary redirects
            header("Location: " . $longUrl, true, 301);
            exit; // Important: Stop script execution after sending the header

        } else {
            // Short code not found in the database
            header("HTTP/1.0 404 Not Found");
            echo "Short URL not found.";
            // You could redirect to a custom 404 page or the homepage
            // header('Location: ' . BASE_URL . 'not_found.php');
            exit;
        }

    } catch (PDOException $e) {
        // Handle database errors during lookup
        header("HTTP/1.0 500 Internal Server Error");
        // Log the detailed error
        error_log("Database Error during redirect: " . $e->getMessage() . " for code: " . $shortCode);
        echo "An error occurred while retrieving the URL. Please try again later.";
        exit;
    }

} else {
    // No code provided, redirect to the homepage or show an error
    header('Location: ' . BASE_URL); // Redirect to the main page
    exit;
}
?>