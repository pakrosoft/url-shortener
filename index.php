<?php
session_start(); // Start session to store messages or results
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PHP URL Shortener</title>
    <style>
        /* Basic styling */
        body { font-family: sans-serif; line-height: 1.6; padding: 20px; }
        .container { max-width: 600px; margin: auto; background: #f4f4f4; padding: 20px; border-radius: 5px; }
        label { display: block; margin-bottom: 5px; }
        input[type="url"], input[type="text"] { width: 100%; padding: 8px; margin-bottom: 10px; box-sizing: border-box;}
        input[type="submit"] { background: #333; color: #fff; padding: 10px 15px; border: none; cursor: pointer; }
        .message { padding: 10px; margin-bottom: 15px; border-radius: 4px; }
        .success { background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .error { background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .short-url { margin-top: 15px; background: #e9e9e9; padding: 10px; word-wrap: break-word; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Simple URL Shortener</h1>

        <?php
        // Display messages if they exist in the session
        if (isset($_SESSION['message'])): ?>
            <div class="message <?php echo $_SESSION['message_type']; ?>">
                <?php
                echo $_SESSION['message'];
                // Clear the message after displaying it
                unset($_SESSION['message']);
                unset($_SESSION['message_type']);
                ?>
            </div>
        <?php endif; ?>

        <?php
        // Display the generated short URL if it exists in the session
        if (isset($_SESSION['short_url'])): ?>
            <div class="short-url">
                Short URL: <a href="<?php echo htmlspecialchars($_SESSION['short_url']); ?>" target="_blank"><?php echo htmlspecialchars($_SESSION['short_url']); ?></a>
            </div>
            <?php unset($_SESSION['short_url']); // Clear after displaying ?>
        <?php endif; ?>


        <form action="shorten.php" method="post">
            <label for="long_url">Enter URL to shorten:</label>
            <input type="url" name="long_url" id="long_url" placeholder="https://example.com/very/long/url/here" required>
            <input type="submit" value="Shorten URL">
        </form>
    </div>
</body>
</html>