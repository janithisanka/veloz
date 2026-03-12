<?php
require_once '../includes/config.php';

if (!isAdminLoggedIn()) {
    redirect('index.php');
}

$conn = getDBConnection();
$errors = [];
$success = false;

// Get brands and categories
$brands = $conn->query("SELECT * FROM car_brands ORDER BY name");
$categories = $conn->query("SELECT * FROM car_categories ORDER BY name");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Collect form data
    $brand_id = (int)$_POST['brand_id'];
    $category_id = (int)$_POST['category_id'];
    $model = sanitize($_POST['model']);
    $year = (int)$_POST['year'];
    $condition_type = sanitize($_POST['condition_type']);
    $price = (float)$_POST['price'];
    $mileage = (int)$_POST['mileage'];
    $engine_capacity = sanitize($_POST['engine_capacity']);
    $fuel_type = sanitize($_POST['fuel_type']);
    $transmission = sanitize($_POST['transmission']);
    $color = sanitize($_POST['color']);
    $body_type = sanitize($_POST['body_type']);
    $seats = (int)$_POST['seats'];
    $features = sanitize($_POST['features']);
    $description = sanitize($_POST['description']);
    $is_featured = isset($_POST['is_featured']) ? 1 : 0;
    $is_available = isset($_POST['is_available']) ? 1 : 0;

    // Validation
    if (empty($model)) $errors[] = "Model is required";
    if ($year < 1990 || $year > date('Y') + 1) $errors[] = "Invalid year";
    if ($price <= 0) $errors[] = "Price must be greater than 0";

    // Handle main image upload
    $main_image = '';
    if (isset($_FILES['main_image']) && $_FILES['main_image']['error'] === UPLOAD_ERR_OK) {
        $allowed = ['jpg', 'jpeg', 'png', 'webp'];
        $filename = $_FILES['main_image']['name'];
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

        if (!in_array($ext, $allowed)) {
            $errors[] = "Invalid image format. Allowed: JPG, PNG, WEBP";
        } else {
            $newFilename = 'car_' . time() . '_' . uniqid() . '.' . $ext;
            $uploadPath = UPLOAD_PATH . $newFilename;

            if (!is_dir(UPLOAD_PATH)) {
                mkdir(UPLOAD_PATH, 0755, true);
            }

            if (move_uploaded_file($_FILES['main_image']['tmp_name'], $uploadPath)) {
                $main_image = $newFilename;
            } else {
                $errors[] = "Failed to upload main image";
            }
        }
    }

    if (empty($errors)) {
        $stmt = $conn->prepare("INSERT INTO cars (brand_id, category_id, model, year, condition_type, price, mileage, engine_capacity, fuel_type, transmission, color, body_type, seats, features, description, main_image, is_featured, is_available) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

        $stmt->bind_param("iisisdiisssssissii",
            $brand_id, $category_id, $model, $year, $condition_type, $price, $mileage,
            $engine_capacity, $fuel_type, $transmission, $color, $body_type, $seats,
            $features, $description, $main_image, $is_featured, $is_available
        );

        if ($stmt->execute()) {
            $car_id = $conn->insert_id;

            // Handle additional images
            if (isset($_FILES['additional_images'])) {
                $allowed = ['jpg', 'jpeg', 'png', 'webp'];
                foreach ($_FILES['additional_images']['tmp_name'] as $key => $tmp_name) {
                    if ($_FILES['additional_images']['error'][$key] === UPLOAD_ERR_OK) {
                        $filename = $_FILES['additional_images']['name'][$key];
                        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

                        if (in_array($ext, $allowed)) {
                            $newFilename = 'car_' . $car_id . '_' . time() . '_' . uniqid() . '.' . $ext;
                            $uploadPath = UPLOAD_PATH . $newFilename;

                            if (move_uploaded_file($tmp_name, $uploadPath)) {
                                $imgStmt = $conn->prepare("INSERT INTO car_images (car_id, image_path, sort_order) VALUES (?, ?, ?)");
                                $imgStmt->bind_param("isi", $car_id, $newFilename, $key);
                                $imgStmt->execute();
                                $imgStmt->close();
                            }
                        }
                    }
                }
            }

            redirect('cars.php?saved=1');
        } else {
            $errors[] = "Failed to save car: " . $conn->error;
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add New Car - <?php echo SITE_NAME; ?> Admin</title>
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
                        <h1>Add New Car</h1>
                        <p>Add a new vehicle to your inventory</p>
                    </div>
                    <a href="cars.php" class="btn btn-outline">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="20" height="20">
                            <path d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                        </svg>
                        Back to Cars
                    </a>
                </div>

                <?php if (!empty($errors)): ?>
                    <div class="alert alert-error">
                        <ul>
                            <?php foreach ($errors as $error): ?>
                                <li><?php echo $error; ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <form method="POST" enctype="multipart/form-data" class="car-form">
                    <div class="form-grid">
                        <!-- Basic Information -->
                        <div class="card">
                            <div class="card-header">
                                <h2>Basic Information</h2>
                            </div>
                            <div class="card-body">
                                <div class="form-row">
                                    <div class="form-group">
                                        <label for="brand_id">Brand *</label>
                                        <select name="brand_id" id="brand_id" required>
                                            <option value="">Select Brand</option>
                                            <?php while ($brand = $brands->fetch_assoc()): ?>
                                                <option value="<?php echo $brand['id']; ?>"><?php echo htmlspecialchars($brand['name']); ?></option>
                                            <?php endwhile; ?>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label for="model">Model *</label>
                                        <input type="text" name="model" id="model" required placeholder="e.g., Corolla Axio">
                                    </div>
                                </div>

                                <div class="form-row">
                                    <div class="form-group">
                                        <label for="year">Year *</label>
                                        <input type="number" name="year" id="year" required min="1990" max="<?php echo date('Y') + 1; ?>" value="<?php echo date('Y'); ?>">
                                    </div>
                                    <div class="form-group">
                                        <label for="category_id">Category</label>
                                        <select name="category_id" id="category_id">
                                            <option value="">Select Category</option>
                                            <?php while ($category = $categories->fetch_assoc()): ?>
                                                <option value="<?php echo $category['id']; ?>"><?php echo htmlspecialchars($category['name']); ?></option>
                                            <?php endwhile; ?>
                                        </select>
                                    </div>
                                </div>

                                <div class="form-row">
                                    <div class="form-group">
                                        <label for="condition_type">Condition *</label>
                                        <select name="condition_type" id="condition_type" required>
                                            <option value="brand_new">Brand New</option>
                                            <option value="recondition">Recondition</option>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label for="price">Price (LKR) *</label>
                                        <input type="number" name="price" id="price" required min="0" step="1000" placeholder="e.g., 8500000">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Technical Specifications -->
                        <div class="card">
                            <div class="card-header">
                                <h2>Technical Specifications</h2>
                            </div>
                            <div class="card-body">
                                <div class="form-row">
                                    <div class="form-group">
                                        <label for="engine_capacity">Engine Capacity</label>
                                        <input type="text" name="engine_capacity" id="engine_capacity" placeholder="e.g., 1500cc">
                                    </div>
                                    <div class="form-group">
                                        <label for="mileage">Mileage (km)</label>
                                        <input type="number" name="mileage" id="mileage" min="0" value="0">
                                    </div>
                                </div>

                                <div class="form-row">
                                    <div class="form-group">
                                        <label for="fuel_type">Fuel Type *</label>
                                        <select name="fuel_type" id="fuel_type" required>
                                            <option value="petrol">Petrol</option>
                                            <option value="diesel">Diesel</option>
                                            <option value="hybrid">Hybrid</option>
                                            <option value="electric">Electric</option>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label for="transmission">Transmission *</label>
                                        <select name="transmission" id="transmission" required>
                                            <option value="automatic">Automatic</option>
                                            <option value="manual">Manual</option>
                                            <option value="cvt">CVT</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="form-row">
                                    <div class="form-group">
                                        <label for="color">Color</label>
                                        <input type="text" name="color" id="color" placeholder="e.g., Pearl White">
                                    </div>
                                    <div class="form-group">
                                        <label for="body_type">Body Type</label>
                                        <input type="text" name="body_type" id="body_type" placeholder="e.g., Sedan">
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label for="seats">Number of Seats</label>
                                    <input type="number" name="seats" id="seats" min="2" max="12" value="5">
                                </div>
                            </div>
                        </div>

                        <!-- Description & Features -->
                        <div class="card">
                            <div class="card-header">
                                <h2>Description & Features</h2>
                            </div>
                            <div class="card-body">
                                <div class="form-group">
                                    <label for="features">Features (comma separated)</label>
                                    <textarea name="features" id="features" rows="3" placeholder="e.g., Push Start, Cruise Control, Reverse Camera, Alloy Wheels"></textarea>
                                </div>

                                <div class="form-group">
                                    <label for="description">Description</label>
                                    <textarea name="description" id="description" rows="5" placeholder="Enter detailed description of the vehicle..."></textarea>
                                </div>
                            </div>
                        </div>

                        <!-- Images -->
                        <div class="card">
                            <div class="card-header">
                                <h2>Images</h2>
                            </div>
                            <div class="card-body">
                                <div class="form-group">
                                    <label for="main_image">Main Image *</label>
                                    <input type="file" name="main_image" id="main_image" accept="image/*" required>
                                    <small class="form-help">Recommended size: 800x600 pixels. Formats: JPG, PNG, WEBP</small>
                                    <div id="main_image_preview" class="image-preview"></div>
                                </div>

                                <div class="form-group">
                                    <label for="additional_images">Additional Images</label>
                                    <input type="file" name="additional_images[]" id="additional_images" accept="image/*" multiple>
                                    <small class="form-help">You can select multiple images</small>
                                    <div id="additional_images_preview" class="image-preview-grid"></div>
                                </div>
                            </div>
                        </div>

                        <!-- Status -->
                        <div class="card">
                            <div class="card-header">
                                <h2>Status</h2>
                            </div>
                            <div class="card-body">
                                <div class="form-group checkbox-group">
                                    <label class="checkbox-label">
                                        <input type="checkbox" name="is_available" value="1" checked>
                                        <span>Available for sale</span>
                                    </label>
                                </div>

                                <div class="form-group checkbox-group">
                                    <label class="checkbox-label">
                                        <input type="checkbox" name="is_featured" value="1">
                                        <span>Featured car (show on homepage)</span>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary btn-lg">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="20" height="20">
                                <path d="M5 13l4 4L19 7"/>
                            </svg>
                            Save Car
                        </button>
                        <a href="cars.php" class="btn btn-outline btn-lg">Cancel</a>
                    </div>
                </form>
            </div>
        </main>
    </div>

    <script src="../assets/js/admin.js"></script>
    <script>
        // Image preview functionality
        document.getElementById('main_image').addEventListener('change', function(e) {
            const preview = document.getElementById('main_image_preview');
            preview.innerHTML = '';
            if (this.files && this.files[0]) {
                const img = document.createElement('img');
                img.src = URL.createObjectURL(this.files[0]);
                preview.appendChild(img);
            }
        });

        document.getElementById('additional_images').addEventListener('change', function(e) {
            const preview = document.getElementById('additional_images_preview');
            preview.innerHTML = '';
            if (this.files) {
                Array.from(this.files).forEach(file => {
                    const img = document.createElement('img');
                    img.src = URL.createObjectURL(file);
                    preview.appendChild(img);
                });
            }
        });
    </script>
</body>
</html>
<?php $conn->close(); ?>
