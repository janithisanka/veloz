<?php
/**
 * Veloz Autohaus - Installation Script
 * Run this once to create the database and tables
 */

// Database Configuration
$db_host = 'localhost';
$db_user = 'root';
$db_pass = '';
$db_name = 'veloz_autohaus';

$success = false;
$error = '';
$messages = [];

// Check if installation is requested
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['install'])) {
    try {
        // Connect to MySQL
        $conn = new mysqli($db_host, $db_user, $db_pass);
        if ($conn->connect_error) {
            throw new Exception("Cannot connect to MySQL: " . $conn->connect_error);
        }

        // Create database
        $conn->query("CREATE DATABASE IF NOT EXISTS `$db_name` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        $messages[] = "Database '$db_name' created successfully.";

        // Select database
        $conn->select_db($db_name);

        // Read and execute SQL file
        $sqlFile = __DIR__ . '/includes/database.sql';
        if (!file_exists($sqlFile)) {
            throw new Exception("SQL file not found: $sqlFile");
        }

        $sql = file_get_contents($sqlFile);

        // Remove CREATE DATABASE and USE statements (we already did that)
        $sql = preg_replace('/CREATE DATABASE.*?;/is', '', $sql);
        $sql = preg_replace('/USE.*?;/is', '', $sql);

        // Execute multi query
        if ($conn->multi_query($sql)) {
            do {
                if ($result = $conn->store_result()) {
                    $result->free();
                }
            } while ($conn->next_result());
        }

        if ($conn->error) {
            // Ignore duplicate entry errors for sample data
            if (strpos($conn->error, 'Duplicate entry') === false) {
                $messages[] = "Warning: " . $conn->error;
            }
        }

        $messages[] = "All tables created successfully.";
        $messages[] = "Default admin user created (username: admin, password: admin123)";
        $messages[] = "Sample data inserted.";

        $conn->close();
        $success = true;

    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

// Check if already installed
$alreadyInstalled = false;
try {
    $checkConn = @new mysqli($db_host, $db_user, $db_pass, $db_name);
    if (!$checkConn->connect_error) {
        $result = $checkConn->query("SHOW TABLES LIKE 'cars'");
        if ($result && $result->num_rows > 0) {
            $alreadyInstalled = true;
        }
        $checkConn->close();
    }
} catch (Exception $e) {
    // Database doesn't exist yet
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Install - Veloz Autohaus</title>
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #0d1b2a 0%, #1d3557 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .install-container {
            background: white;
            max-width: 500px;
            width: 100%;
            border-radius: 16px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            overflow: hidden;
        }
        .install-header {
            background: #e63946;
            color: white;
            padding: 30px;
            text-align: center;
        }
        .install-header h1 {
            font-size: 28px;
            margin-bottom: 8px;
        }
        .install-header h1 span {
            font-weight: 400;
        }
        .install-header p {
            opacity: 0.9;
        }
        .install-body {
            padding: 30px;
        }
        .info-box {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 24px;
        }
        .info-box h3 {
            font-size: 16px;
            margin-bottom: 12px;
            color: #333;
        }
        .info-box ul {
            list-style: none;
            font-size: 14px;
            color: #666;
        }
        .info-box li {
            padding: 6px 0;
            display: flex;
            justify-content: space-between;
        }
        .info-box li span:last-child {
            font-family: monospace;
            background: #e9ecef;
            padding: 2px 8px;
            border-radius: 4px;
        }
        .btn {
            display: block;
            width: 100%;
            padding: 14px 24px;
            background: #e63946;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.3s;
        }
        .btn:hover {
            background: #c1121f;
        }
        .btn:disabled {
            background: #ccc;
            cursor: not-allowed;
        }
        .btn-success {
            background: #06d6a0;
        }
        .btn-success:hover {
            background: #05b587;
        }
        .message {
            padding: 12px 16px;
            border-radius: 8px;
            margin-bottom: 16px;
            font-size: 14px;
        }
        .message-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .message-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .message ul {
            margin: 8px 0 0 20px;
        }
        .already-installed {
            text-align: center;
        }
        .already-installed svg {
            width: 64px;
            height: 64px;
            color: #06d6a0;
            margin-bottom: 16px;
        }
        .already-installed h2 {
            color: #333;
            margin-bottom: 8px;
        }
        .already-installed p {
            color: #666;
            margin-bottom: 24px;
        }
        .links {
            display: flex;
            gap: 12px;
        }
        .links a {
            flex: 1;
            padding: 12px;
            text-align: center;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s;
        }
        .link-primary {
            background: #e63946;
            color: white;
        }
        .link-primary:hover {
            background: #c1121f;
        }
        .link-secondary {
            background: #f8f9fa;
            color: #333;
            border: 1px solid #ddd;
        }
        .link-secondary:hover {
            background: #e9ecef;
        }
    </style>
</head>
<body>
    <div class="install-container">
        <div class="install-header">
            <h1>Veloz<span>Autohaus</span></h1>
            <p>Installation Wizard</p>
        </div>
        <div class="install-body">
            <?php if ($alreadyInstalled && !$success): ?>
                <div class="already-installed">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <h2>Already Installed</h2>
                    <p>Veloz Autohaus is already set up and ready to use.</p>
                    <div class="links">
                        <a href="./" class="link-primary">Visit Website</a>
                        <a href="./admin/" class="link-secondary">Admin Panel</a>
                    </div>
                </div>
            <?php elseif ($success): ?>
                <div class="message message-success">
                    <strong>Installation Successful!</strong>
                    <ul>
                        <?php foreach ($messages as $msg): ?>
                            <li><?php echo htmlspecialchars($msg); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <div class="links">
                    <a href="./" class="link-primary">Visit Website</a>
                    <a href="./admin/" class="link-secondary">Admin Panel</a>
                </div>
            <?php else: ?>
                <?php if ($error): ?>
                    <div class="message message-error">
                        <strong>Error:</strong> <?php echo htmlspecialchars($error); ?>
                    </div>
                <?php endif; ?>

                <div class="info-box">
                    <h3>Database Configuration</h3>
                    <ul>
                        <li><span>Host:</span> <span><?php echo $db_host; ?></span></li>
                        <li><span>Username:</span> <span><?php echo $db_user; ?></span></li>
                        <li><span>Database:</span> <span><?php echo $db_name; ?></span></li>
                    </ul>
                </div>

                <div class="info-box">
                    <h3>This will create:</h3>
                    <ul>
                        <li><span>Database tables for cars, brands, categories</span></li>
                        <li><span>Quote request management system</span></li>
                        <li><span>Admin user (admin / admin123)</span></li>
                        <li><span>Sample data to get you started</span></li>
                    </ul>
                </div>

                <form method="POST">
                    <button type="submit" name="install" class="btn">
                        Install Veloz Autohaus
                    </button>
                </form>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
