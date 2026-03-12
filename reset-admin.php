<?php
/**
 * Admin Password Reset Script
 * Run once then delete this file for security
 */
require_once 'includes/config.php';

$newPassword = 'admin123';
$hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

$conn = getDBConnection();

// Check if admin exists
$result = $conn->query("SELECT id FROM admins WHERE username = 'admin'");

if ($result->num_rows > 0) {
    // Update existing admin
    $stmt = $conn->prepare("UPDATE admins SET password = ? WHERE username = 'admin'");
    $stmt->bind_param("s", $hashedPassword);
    if ($stmt->execute()) {
        echo "<h2>Admin password has been reset successfully!</h2>";
        echo "<p><strong>Username:</strong> admin</p>";
        echo "<p><strong>Password:</strong> admin123</p>";
        echo "<p><a href='admin/'>Go to Admin Panel</a></p>";
        echo "<p style='color:red;'><strong>IMPORTANT:</strong> Delete this file (reset-admin.php) after use!</p>";
    } else {
        echo "Error updating password: " . $conn->error;
    }
    $stmt->close();
} else {
    // Insert new admin
    $stmt = $conn->prepare("INSERT INTO admins (username, password, email) VALUES ('admin', ?, 'admin@velozautohaus.lk')");
    $stmt->bind_param("s", $hashedPassword);
    if ($stmt->execute()) {
        echo "<h2>Admin account created successfully!</h2>";
        echo "<p><strong>Username:</strong> admin</p>";
        echo "<p><strong>Password:</strong> admin123</p>";
        echo "<p><a href='admin/'>Go to Admin Panel</a></p>";
        echo "<p style='color:red;'><strong>IMPORTANT:</strong> Delete this file (reset-admin.php) after use!</p>";
    } else {
        echo "Error creating admin: " . $conn->error;
    }
    $stmt->close();
}

$conn->close();
?>
