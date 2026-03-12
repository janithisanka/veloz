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
    $description = sanitize($_POST['description']);
    $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;

    if (empty($name)) {
        $errors[] = "Category name is required";
    } else {
        if ($id > 0) {
            $stmt = $conn->prepare("UPDATE car_categories SET name = ?, description = ? WHERE id = ?");
            $stmt->bind_param("ssi", $name, $description, $id);
            if ($stmt->execute()) {
                $success = "Category updated successfully";
            }
            $stmt->close();
        } else {
            $stmt = $conn->prepare("INSERT INTO car_categories (name, description) VALUES (?, ?)");
            $stmt->bind_param("ss", $name, $description);
            if ($stmt->execute()) {
                $success = "Category added successfully";
            }
            $stmt->close();
        }
    }
}

// Handle delete
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $check = $conn->query("SELECT COUNT(*) as count FROM cars WHERE category_id = $id")->fetch_assoc();
    if ($check['count'] > 0) {
        $errors[] = "Cannot delete category with existing cars";
    } else {
        $conn->query("DELETE FROM car_categories WHERE id = $id");
        $success = "Category deleted successfully";
    }
}

// Get all categories
$categories = $conn->query("
    SELECT cc.*, COUNT(c.id) as car_count
    FROM car_categories cc
    LEFT JOIN cars c ON cc.id = c.category_id
    GROUP BY cc.id
    ORDER BY cc.name
");

// Get category for editing
$editCategory = null;
if (isset($_GET['edit']) && is_numeric($_GET['edit'])) {
    $editId = (int)$_GET['edit'];
    $result = $conn->query("SELECT * FROM car_categories WHERE id = $editId");
    $editCategory = $result->fetch_assoc();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Categories - <?php echo SITE_NAME; ?> Admin</title>
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
                        <h1>Manage Categories</h1>
                        <p>Add and manage car categories</p>
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
                            <h2><?php echo $editCategory ? 'Edit Category' : 'Add New Category'; ?></h2>
                        </div>
                        <div class="card-body">
                            <form method="POST">
                                <?php if ($editCategory): ?>
                                    <input type="hidden" name="id" value="<?php echo $editCategory['id']; ?>">
                                <?php endif; ?>

                                <div class="form-group">
                                    <label for="name">Category Name *</label>
                                    <input type="text" name="name" id="name" required value="<?php echo $editCategory ? htmlspecialchars($editCategory['name']) : ''; ?>" placeholder="e.g., SUV">
                                </div>

                                <div class="form-group">
                                    <label for="description">Description</label>
                                    <textarea name="description" id="description" rows="3" placeholder="Brief description of this category"><?php echo $editCategory ? htmlspecialchars($editCategory['description']) : ''; ?></textarea>
                                </div>

                                <div class="form-actions">
                                    <button type="submit" class="btn btn-primary"><?php echo $editCategory ? 'Update' : 'Add'; ?> Category</button>
                                    <?php if ($editCategory): ?>
                                        <a href="categories.php" class="btn btn-outline">Cancel</a>
                                    <?php endif; ?>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Categories List -->
                    <div class="card">
                        <div class="card-header">
                            <h2>All Categories</h2>
                        </div>
                        <div class="card-body">
                            <?php if ($categories->num_rows > 0): ?>
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>Category</th>
                                            <th>Cars</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php while ($category = $categories->fetch_assoc()): ?>
                                            <tr>
                                                <td>
                                                    <strong><?php echo htmlspecialchars($category['name']); ?></strong>
                                                    <?php if ($category['description']): ?>
                                                        <br><small class="text-muted"><?php echo htmlspecialchars($category['description']); ?></small>
                                                    <?php endif; ?>
                                                </td>
                                                <td><?php echo $category['car_count']; ?> cars</td>
                                                <td>
                                                    <div class="action-buttons">
                                                        <a href="?edit=<?php echo $category['id']; ?>" class="btn btn-sm btn-outline">Edit</a>
                                                        <?php if ($category['car_count'] == 0): ?>
                                                            <a href="?delete=<?php echo $category['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this category?')">Delete</a>
                                                        <?php endif; ?>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                            <?php else: ?>
                                <p class="text-muted">No categories added yet.</p>
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
