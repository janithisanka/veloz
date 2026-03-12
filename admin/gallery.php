<?php
require_once '../includes/config.php';

if (!isAdminLoggedIn()) {
    redirect('index.php');
}

$conn = getDBConnection();
$errors = [];
$success = '';

// Create table if not exists
$conn->query("CREATE TABLE IF NOT EXISTS gallery (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(200),
    description TEXT,
    image_path VARCHAR(255) NOT NULL,
    gallery_type ENUM('delivery', 'imported', 'customer') DEFAULT 'delivery',
    is_active TINYINT(1) DEFAULT 1,
    sort_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");

// Handle add
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_gallery'])) {
    $title = sanitize($_POST['title']);
    $description = sanitize($_POST['description']);
    $gallery_type = sanitize($_POST['gallery_type']);

    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $allowed = ['jpg', 'jpeg', 'png', 'webp'];
        $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));

        if (!in_array($ext, $allowed)) {
            $errors[] = "Invalid image format";
        } else {
            if (!is_dir(GALLERY_PATH)) {
                mkdir(GALLERY_PATH, 0755, true);
            }
            $filename = 'gallery_' . time() . '_' . uniqid() . '.' . $ext;
            if (move_uploaded_file($_FILES['image']['tmp_name'], GALLERY_PATH . $filename)) {
                $stmt = $conn->prepare("INSERT INTO gallery (title, description, image_path, gallery_type) VALUES (?, ?, ?, ?)");
                $stmt->bind_param("ssss", $title, $description, $filename, $gallery_type);
                if ($stmt->execute()) {
                    $success = "Image added to gallery";
                }
                $stmt->close();
            } else {
                $errors[] = "Failed to upload image";
            }
        }
    } else {
        $errors[] = "Image is required";
    }
}

// Handle delete
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $img = $conn->query("SELECT image_path FROM gallery WHERE id = $id")->fetch_assoc();
    if ($img && file_exists(GALLERY_PATH . $img['image_path'])) {
        unlink(GALLERY_PATH . $img['image_path']);
    }
    $conn->query("DELETE FROM gallery WHERE id = $id");
    redirect('gallery.php?deleted=1');
}

// Handle toggle active
if (isset($_GET['toggle']) && is_numeric($_GET['toggle'])) {
    $id = (int)$_GET['toggle'];
    $conn->query("UPDATE gallery SET is_active = NOT is_active WHERE id = $id");
    redirect('gallery.php');
}

// Get filter
$typeFilter = isset($_GET['type']) ? sanitize($_GET['type']) : '';
$where = $typeFilter ? "WHERE gallery_type = '$typeFilter'" : "";

$gallery = $conn->query("SELECT * FROM gallery $where ORDER BY sort_order ASC, created_at DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gallery - <?php echo SITE_NAME; ?> Admin</title>
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
                        <h1>Customer Gallery</h1>
                        <p>Manage imported cars & happy customer photos</p>
                    </div>
                </div>

                <?php if ($success): ?><div class="alert alert-success"><?php echo $success; ?></div><?php endif; ?>
                <?php if (isset($_GET['deleted'])): ?><div class="alert alert-success">Image deleted.</div><?php endif; ?>
                <?php if (!empty($errors)): ?><div class="alert alert-error"><?php foreach ($errors as $e) echo "<p>$e</p>"; ?></div><?php endif; ?>

                <div class="content-grid">
                    <!-- Add Form -->
                    <div class="card">
                        <div class="card-header"><h2>Add Image</h2></div>
                        <div class="card-body">
                            <form method="POST" enctype="multipart/form-data">
                                <div class="form-group">
                                    <label for="title">Title</label>
                                    <input type="text" name="title" id="title" placeholder="e.g., Toyota Aqua - Customer Delivery">
                                </div>
                                <div class="form-group">
                                    <label for="gallery_type">Type</label>
                                    <select name="gallery_type" id="gallery_type">
                                        <option value="delivery">Customer Delivery</option>
                                        <option value="imported">Imported Car</option>
                                        <option value="customer">Happy Customer</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="description">Description</label>
                                    <textarea name="description" id="description" rows="2" placeholder="Optional description"></textarea>
                                </div>
                                <div class="form-group">
                                    <label for="image">Image *</label>
                                    <input type="file" name="image" id="image" accept="image/*" required>
                                </div>
                                <button type="submit" name="add_gallery" class="btn btn-primary">Add to Gallery</button>
                            </form>
                        </div>
                    </div>

                    <!-- Gallery List -->
                    <div class="card">
                        <div class="card-header">
                            <h2>Gallery Images</h2>
                            <div class="filter-tabs">
                                <a href="gallery.php" class="<?php echo !$typeFilter ? 'active' : ''; ?>">All</a>
                                <a href="?type=delivery" class="<?php echo $typeFilter === 'delivery' ? 'active' : ''; ?>">Delivery</a>
                                <a href="?type=imported" class="<?php echo $typeFilter === 'imported' ? 'active' : ''; ?>">Imported</a>
                                <a href="?type=customer" class="<?php echo $typeFilter === 'customer' ? 'active' : ''; ?>">Customer</a>
                            </div>
                        </div>
                        <div class="card-body">
                            <?php if ($gallery->num_rows > 0): ?>
                                <div class="image-gallery-edit">
                                    <?php while ($item = $gallery->fetch_assoc()): ?>
                                        <div class="gallery-admin-item">
                                            <div class="gallery-item">
                                                <img src="../uploads/gallery/<?php echo htmlspecialchars($item['image_path']); ?>" alt="<?php echo htmlspecialchars($item['title']); ?>">
                                                <a href="?delete=<?php echo $item['id']; ?>" class="delete-image" onclick="return confirm('Delete this image?')">
                                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><path d="M6 18L18 6M6 6l12 12"/></svg>
                                                </a>
                                            </div>
                                            <div class="gallery-item-info">
                                                <strong><?php echo htmlspecialchars($item['title'] ?: 'Untitled'); ?></strong>
                                                <span class="badge badge-<?php echo $item['gallery_type'] === 'delivery' ? 'success' : ($item['gallery_type'] === 'imported' ? 'info' : 'warning'); ?>">
                                                    <?php echo ucfirst($item['gallery_type']); ?>
                                                </span>
                                                <a href="?toggle=<?php echo $item['id']; ?>" class="btn btn-sm btn-outline">
                                                    <?php echo $item['is_active'] ? 'Hide' : 'Show'; ?>
                                                </a>
                                            </div>
                                        </div>
                                    <?php endwhile; ?>
                                </div>
                            <?php else: ?>
                                <p class="text-muted">No gallery images yet. Add some above!</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
    <script src="../assets/js/admin.js"></script>
</body>
</html>
<?php $conn->close(); ?>
