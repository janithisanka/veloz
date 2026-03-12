<?php
require_once '../includes/config.php';

if (!isAdminLoggedIn()) {
    redirect('index.php');
}

$conn = getDBConnection();
$success = '';

// Handle settings update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $settings = [
        'phone' => sanitize($_POST['phone']),
        'email' => sanitize($_POST['email']),
        'address' => sanitize($_POST['address']),
        'whatsapp' => sanitize($_POST['whatsapp']),
        'facebook' => sanitize($_POST['facebook']),
        'instagram' => sanitize($_POST['instagram']),
        'about_text' => sanitize($_POST['about_text'])
    ];

    foreach ($settings as $key => $value) {
        $stmt = $conn->prepare("INSERT INTO site_settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = ?");
        $stmt->bind_param("sss", $key, $value, $value);
        $stmt->execute();
        $stmt->close();
    }

    $success = "Settings updated successfully";
}

// Get current settings
$settingsResult = $conn->query("SELECT * FROM site_settings");
$settings = [];
while ($row = $settingsResult->fetch_assoc()) {
    $settings[$row['setting_key']] = $row['setting_value'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings - <?php echo SITE_NAME; ?> Admin</title>
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body>
    <div class="admin-wrapper">
        <?php include 'includes/sidebar.php'; ?>

        <main class="admin-main">
            <?php include 'includes/header.php'; ?>

            <div class="admin-content">
                <div class="page-header">
                    <div>
                        <h1>Site Settings</h1>
                        <p>Configure your website settings</p>
                    </div>
                </div>

                <?php if ($success): ?>
                    <div class="alert alert-success"><?php echo $success; ?></div>
                <?php endif; ?>

                <form method="POST">
                    <div class="settings-grid">
                        <!-- Contact Information -->
                        <div class="card">
                            <div class="card-header">
                                <h2>Contact Information</h2>
                            </div>
                            <div class="card-body">
                                <div class="form-group">
                                    <label for="phone">Phone Number</label>
                                    <input type="text" name="phone" id="phone" value="<?php echo htmlspecialchars($settings['phone'] ?? ''); ?>" placeholder="+94 77 123 4567">
                                </div>

                                <div class="form-group">
                                    <label for="email">Email Address</label>
                                    <input type="email" name="email" id="email" value="<?php echo htmlspecialchars($settings['email'] ?? ''); ?>" placeholder="info@velozautohaus.lk">
                                </div>

                                <div class="form-group">
                                    <label for="whatsapp">WhatsApp Number</label>
                                    <input type="text" name="whatsapp" id="whatsapp" value="<?php echo htmlspecialchars($settings['whatsapp'] ?? ''); ?>" placeholder="+94771234567">
                                    <small class="form-help">Include country code without + or spaces</small>
                                </div>

                                <div class="form-group">
                                    <label for="address">Business Address</label>
                                    <textarea name="address" id="address" rows="3" placeholder="Your business address"><?php echo htmlspecialchars($settings['address'] ?? ''); ?></textarea>
                                </div>
                            </div>
                        </div>

                        <!-- Social Media -->
                        <div class="card">
                            <div class="card-header">
                                <h2>Social Media</h2>
                            </div>
                            <div class="card-body">
                                <div class="form-group">
                                    <label for="facebook">Facebook Page URL</label>
                                    <input type="url" name="facebook" id="facebook" value="<?php echo htmlspecialchars($settings['facebook'] ?? ''); ?>" placeholder="https://facebook.com/velozautohaus">
                                </div>

                                <div class="form-group">
                                    <label for="instagram">Instagram Profile URL</label>
                                    <input type="url" name="instagram" id="instagram" value="<?php echo htmlspecialchars($settings['instagram'] ?? ''); ?>" placeholder="https://instagram.com/velozautohaus">
                                </div>
                            </div>
                        </div>

                        <!-- About Section -->
                        <div class="card full-width">
                            <div class="card-header">
                                <h2>About Section</h2>
                            </div>
                            <div class="card-body">
                                <div class="form-group">
                                    <label for="about_text">About Text</label>
                                    <textarea name="about_text" id="about_text" rows="5" placeholder="Write about your company..."><?php echo htmlspecialchars($settings['about_text'] ?? ''); ?></textarea>
                                    <small class="form-help">This text will appear in the About section on your website</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary btn-lg">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="20" height="20">
                                <path d="M5 13l4 4L19 7"/>
                            </svg>
                            Save Settings
                        </button>
                    </div>
                </form>
            </div>
        </main>
    </div>

    <script src="../assets/js/admin.js"></script>
</body>
</html>
<?php $conn->close(); ?>
