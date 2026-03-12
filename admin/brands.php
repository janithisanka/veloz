<?php
require_once '../includes/config.php';

if (!isAdminLoggedIn()) {
    redirect('index.php');
}

$conn = getDBConnection();
$errors = [];
$success = '';

// Handle add/edit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitize($_POST['name']);
    $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;

    if (empty($name)) {
        $errors[] = "Brand name is required";
    } else {
        if ($id > 0) {
            // Update
            $stmt = $conn->prepare("UPDATE car_brands SET name = ? WHERE id = ?");
            $stmt->bind_param("si", $name, $id);
            if ($stmt->execute()) {
                $success = "Brand updated successfully";
            }
            $stmt->close();
        } else {
            // Insert
            $stmt = $conn->prepare("INSERT INTO car_brands (name) VALUES (?)");
            $stmt->bind_param("s", $name);
            if ($stmt->execute()) {
                $success = "Brand added successfully";
            }
            $stmt->close();
        }
    }
}

// Handle delete
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    // Check if brand has cars
    $check = $conn->query("SELECT COUNT(*) as count FROM cars WHERE brand_id = $id")->fetch_assoc();
    if ($check['count'] > 0) {
        $errors[] = "Cannot delete brand with existing cars";
    } else {
        $conn->query("DELETE FROM car_brands WHERE id = $id");
        $success = "Brand deleted successfully";
    }
}

// Get all brands with car counts
$brands = $conn->query("
    SELECT cb.*, COUNT(c.id) as car_count
    FROM car_brands cb
    LEFT JOIN cars c ON cb.id = c.brand_id
    GROUP BY cb.id
    ORDER BY cb.name
");

// Get brand for editing
$editBrand = null;
if (isset($_GET['edit']) && is_numeric($_GET['edit'])) {
    $editId = (int)$_GET['edit'];
    $result = $conn->query("SELECT * FROM car_brands WHERE id = $editId");
    $editBrand = $result->fetch_assoc();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Brands - <?php echo SITE_NAME; ?> Admin</title>
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
                        <h1>Manage Brands</h1>
                        <p>Add and manage car brands</p>
                    </div>
                </div>

                <?php if (!empty($errors)): ?>
                    <div class="alert alert-error">
                        <?php foreach ($errors as $error): ?>
                            <p><?php echo $error; ?></p>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <?php if ($success): ?>
                    <div class="alert alert-success"><?php echo $success; ?></div>
                <?php endif; ?>

                <div class="content-grid">
                    <!-- Add/Edit Form -->
                    <div class="card">
                        <div class="card-header">
                            <h2><?php echo $editBrand ? 'Edit Brand' : 'Add New Brand'; ?></h2>
                        </div>
                        <div class="card-body">
                            <form method="POST">
                                <?php if ($editBrand): ?>
                                    <input type="hidden" name="id" value="<?php echo $editBrand['id']; ?>">
                                <?php endif; ?>

                                <div class="form-group">
                                    <label for="name">Brand Name *</label>
                                    <input type="text" name="name" id="name" required value="<?php echo $editBrand ? htmlspecialchars($editBrand['name']) : ''; ?>" placeholder="e.g., Toyota">
                                </div>

                                <div class="form-actions">
                                    <button type="submit" class="btn btn-primary"><?php echo $editBrand ? 'Update' : 'Add'; ?> Brand</button>
                                    <?php if ($editBrand): ?>
                                        <a href="brands.php" class="btn btn-outline">Cancel</a>
                                    <?php endif; ?>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Brands List -->
                    <div class="card">
                        <div class="card-header">
                            <h2>All Brands</h2>
                        </div>
                        <div class="card-body">
                            <?php if ($brands->num_rows > 0): ?>
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>Brand Name</th>
                                            <th>Cars</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php while ($brand = $brands->fetch_assoc()): ?>
                                            <tr>
                                                <td><strong><?php echo htmlspecialchars($brand['name']); ?></strong></td>
                                                <td><?php echo $brand['car_count']; ?> cars</td>
                                                <td>
                                                    <div class="action-buttons">
                                                        <a href="?edit=<?php echo $brand['id']; ?>" class="btn btn-sm btn-outline">Edit</a>
                                                        <?php if ($brand['car_count'] == 0): ?>
                                                            <a href="?delete=<?php echo $brand['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this brand?')">Delete</a>
                                                        <?php endif; ?>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                            <?php else: ?>
                                <p class="text-muted">No brands added yet.</p>
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
