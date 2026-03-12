<?php
// Show errors (remove in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database Configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'veloz_autohaus');

// Site Configuration
define('SITE_NAME', 'Veloz AutoHaus Colombo');
define('SITE_URL', 'http://localhost/VelozAutoHaus');
define('UPLOAD_PATH', __DIR__ . '/../uploads/cars/');
define('GALLERY_PATH', __DIR__ . '/../uploads/gallery/');
define('POSTS_PATH', __DIR__ . '/../uploads/posts/');

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Database connection
function getDBConnection() {
    // Disable exception throwing for mysqli
    mysqli_report(MYSQLI_REPORT_OFF);

    try {
        $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        if ($conn->connect_error) {
            throw new Exception($conn->connect_error);
        }
        $conn->set_charset("utf8mb4");
        return $conn;
    } catch (Exception $e) {
        // Check if MySQL server is running
        try {
            $testConn = new mysqli(DB_HOST, DB_USER, DB_PASS);
            if ($testConn->connect_error) {
                throw new Exception($testConn->connect_error);
            }
            $testConn->close();

            // MySQL is running but database doesn't exist - show install message
            die("<!DOCTYPE html><html><head><title>Installation Required</title>
            <meta http-equiv='refresh' content='0;url=" . SITE_URL . "/install.php'>
            </head><body>
            <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 50px auto; padding: 30px; background: #fff; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); text-align: center;'>
                <h2 style='color: #e63946;'>Installation Required</h2>
                <p>The database has not been set up yet.</p>
                <p><a href='" . SITE_URL . "/install.php' style='display: inline-block; margin-top: 20px; padding: 12px 24px; background: #e63946; color: white; text-decoration: none; border-radius: 8px;'>Run Installation</a></p>
            </div></body></html>");
        } catch (Exception $e2) {
            die("<!DOCTYPE html><html><head><title>Database Error</title></head><body>
            <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 50px auto; padding: 30px; background: #fff; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);'>
                <h2 style='color: #e63946;'>Database Connection Error</h2>
                <p>Could not connect to MySQL server. Please ensure:</p>
                <ul>
                    <li>XAMPP is running</li>
                    <li>MySQL service is started</li>
                </ul>
                <p><strong>Error:</strong> " . htmlspecialchars($e2->getMessage()) . "</p>
            </div></body></html>");
        }
    }
}

// Helper function to sanitize input
function sanitize($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}

// Format price in LKR
function formatPrice($price) {
    return 'Rs. ' . number_format($price, 0, '.', ',');
}

// Check if admin is logged in
function isAdminLoggedIn() {
    return isset($_SESSION['admin_id']);
}

// Redirect function
function redirect($url) {
    header("Location: $url");
    exit();
}
?>
