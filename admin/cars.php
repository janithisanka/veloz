<?php
require_once '../includes/config.php';

if (!isAdminLoggedIn()) {
    redirect('index.php');
}

$conn = getDBConnection();

// Handle delete action
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $carId = (int)$_GET['delete'];

    // Get car images to delete files
    $images = $conn->query("SELECT image_path FROM car_images WHERE car_id = $carId");
    while ($img = $images->fetch_assoc()) {
        $filePath = UPLOAD_PATH . $img['image_path'];
        if (file_exists($filePath)) {
            unlink($filePath);
        }
    }

    // Delete main image
    $mainImg = $conn->query("SELECT main_image FROM cars WHERE id = $carId")->fetch_assoc();
    if ($mainImg && $mainImg['main_image']) {
        $filePath = UPLOAD_PATH . $mainImg['main_image'];
        if (file_exists($filePath)) {
            unlink($filePath);
        }
    }

    $conn->query("DELETE FROM cars WHERE id = $carId");
    redirect('cars.php?deleted=1');
}

// Handle availability toggle
if (isset($_GET['toggle']) && is_numeric($_GET['toggle'])) {
    $carId = (int)$_GET['toggle'];
    $conn->query("UPDATE cars SET is_available = NOT is_available WHERE id = $carId");
    redirect('cars.php');
}

// Get filter parameters
$brandFilter = isset($_GET['brand']) ? (int)$_GET['brand'] : 0;
$conditionFilter = isset($_GET['condition']) ? sanitize($_GET['condition']) : '';
$search = isset($_GET['search']) ? sanitize($_GET['search']) : '';

// Build query
$where = [];
$params = [];
$types = '';

if ($brandFilter > 0) {
    $where[] = "c.brand_id = ?";
    $params[] = $brandFilter;
    $types .= 'i';
}

if ($conditionFilter) {
    $where[] = "c.condition_type = ?";
    $params[] = $conditionFilter;
    $types .= 's';
}

if ($search) {
    $where[] = "(c.model LIKE ? OR cb.name LIKE ?)";
    $searchTerm = "%$search%";
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $types .= 'ss';
}

$whereClause = count($where) > 0 ? 'WHERE ' . implode(' AND ', $where) : '';

// Get cars
$sql = "SELECT c.*, cb.name as brand_name, cc.name as category_name
        FROM cars c
        LEFT JOIN car_brands cb ON c.brand_id = cb.id
        LEFT JOIN car_categories cc ON c.category_id = cc.id
        $whereClause
        ORDER BY c.created_at DESC";

if (count($params) > 0) {
    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $cars = $stmt->get_result();
} else {
    $cars = $conn->query($sql);
}

// Get brands for filter
$brands = $conn->query("SELECT * FROM car_brands ORDER BY name");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Cars - <?php echo SITE_NAME; ?> Admin</title>
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
                        <h1>Manage Cars</h1>
                        <p>Add, edit, and manage your vehicle inventory</p>
                    </div>
                    <a href="car-add.php" class="btn btn-primary">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="20" height="20">
                            <path d="M12 4v16m8-8H4"/>
                        </svg>
                        Add New Car
                    </a>
                </div>

                <?php if (isset($_GET['deleted'])): ?>
                    <div class="alert alert-success">Car deleted successfully.</div>
                <?php endif; ?>

                <?php if (isset($_GET['saved'])): ?>
                    <div class="alert alert-success">Car saved successfully.</div>
                <?php endif; ?>

                <!-- Filters -->
                <div class="card mb-4">
                    <div class="card-body">
                        <form method="GET" class="filter-form">
                            <div class="form-row">
                                <div class="form-group">
                                    <input type="text" name="search" placeholder="Search cars..." value="<?php echo htmlspecialchars($search); ?>">
                                </div>
                                <div class="form-group">
                                    <select name="brand">
                                        <option value="">All Brands</option>
                                        <?php while ($brand = $brands->fetch_assoc()): ?>
                                            <option value="<?php echo $brand['id']; ?>" <?php echo $brandFilter == $brand['id'] ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($brand['name']); ?>
                                            </option>
                                        <?php endwhile; ?>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <select name="condition">
                                        <option value="">All Conditions</option>
                                        <option value="brand_new" <?php echo $conditionFilter === 'brand_new' ? 'selected' : ''; ?>>Brand New</option>
                                        <option value="recondition" <?php echo $conditionFilter === 'recondition' ? 'selected' : ''; ?>>Recondition</option>
                                    </select>
                                </div>
                                <button type="submit" class="btn">Filter</button>
                                <a href="cars.php" class="btn btn-outline">Reset</a>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Cars Table -->
                <div class="card">
                    <div class="card-body">
                        <?php if ($cars->num_rows > 0): ?>
                            <div class="table-responsive">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>Image</th>
                                            <th>Car Details</th>
                                            <th>Condition</th>
                                            <th>Price</th>
                                            <th>Status</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php while ($car = $cars->fetch_assoc()): ?>
                                            <tr>
                                                <td>
                                                    <div class="car-thumb">
                                                        <?php if ($car['main_image']): ?>
                                                            <img src="../uploads/cars/<?php echo htmlspecialchars($car['main_image']); ?>" alt="<?php echo htmlspecialchars($car['model']); ?>">
                                                        <?php else: ?>
                                                            <div class="no-image">No Image</div>
                                                        <?php endif; ?>
                                                    </div>
                                                </td>
                                                <td>
                                                    <strong><?php echo htmlspecialchars($car['brand_name'] . ' ' . $car['model']); ?></strong><br>
                                                    <small class="text-muted">
                                                        <?php echo $car['year']; ?> | <?php echo $car['engine_capacity']; ?> | <?php echo ucfirst($car['transmission']); ?>
                                                    </small>
                                                </td>
                                                <td>
                                                    <span class="badge badge-<?php echo $car['condition_type'] === 'brand_new' ? 'primary' : 'info'; ?>">
                                                        <?php echo $car['condition_type'] === 'brand_new' ? 'Brand New' : 'Recondition'; ?>
                                                    </span>
                                                </td>
                                                <td><?php echo formatPrice($car['price']); ?></td>
                                                <td>
                                                    <?php if ($car['is_available']): ?>
                                                        <span class="badge badge-success">Available</span>
                                                    <?php else: ?>
                                                        <span class="badge badge-danger">Sold</span>
                                                    <?php endif; ?>
                                                    <?php if ($car['is_featured']): ?>
                                                        <span class="badge badge-warning">Featured</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <div class="action-buttons">
                                                        <a href="car-edit.php?id=<?php echo $car['id']; ?>" class="btn btn-sm btn-outline" title="Edit">
                                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16">
                                                                <path d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                                            </svg>
                                                        </a>
                                                        <a href="?toggle=<?php echo $car['id']; ?>" class="btn btn-sm btn-outline" title="Toggle Availability">
                                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16">
                                                                <path d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
                                                            </svg>
                                                        </a>
                                                        <a href="?delete=<?php echo $car['id']; ?>" class="btn btn-sm btn-danger" title="Delete" onclick="return confirm('Are you sure you want to delete this car?')">
                                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16">
                                                                <path d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                            </svg>
                                                        </a>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="empty-state">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="48" height="48">
                                    <path d="M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0z"/>
                                    <path d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0"/>
                                </svg>
                                <h3>No cars found</h3>
                                <p>Start by adding your first car to the inventory.</p>
                                <a href="car-add.php" class="btn btn-primary">Add New Car</a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script src="../assets/js/admin.js"></script>
</body>
</html>
<?php $conn->close(); ?>
