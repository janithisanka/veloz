<?php
require_once 'includes/config.php';

$conn = getDBConnection();

// Get filter parameters
$brandFilter = isset($_GET['brand']) ? (int)$_GET['brand'] : 0;
$categoryFilter = isset($_GET['category']) ? (int)$_GET['category'] : 0;
$conditionFilter = isset($_GET['condition']) ? sanitize($_GET['condition']) : '';
$fuelFilter = isset($_GET['fuel']) ? sanitize($_GET['fuel']) : '';
$transmissionFilter = isset($_GET['transmission']) ? sanitize($_GET['transmission']) : '';
$priceMin = isset($_GET['price_min']) ? (int)$_GET['price_min'] : 0;
$priceMax = isset($_GET['price_max']) ? (int)$_GET['price_max'] : 0;
$search = isset($_GET['search']) ? sanitize($_GET['search']) : '';
$sort = isset($_GET['sort']) ? sanitize($_GET['sort']) : 'newest';

// Build query
$where = ["c.is_available = 1"];
$params = [];
$types = '';

if ($brandFilter > 0) {
    $where[] = "c.brand_id = ?";
    $params[] = $brandFilter;
    $types .= 'i';
}

if ($categoryFilter > 0) {
    $where[] = "c.category_id = ?";
    $params[] = $categoryFilter;
    $types .= 'i';
}

if ($conditionFilter) {
    $where[] = "c.condition_type = ?";
    $params[] = $conditionFilter;
    $types .= 's';
}

if ($fuelFilter) {
    $where[] = "c.fuel_type = ?";
    $params[] = $fuelFilter;
    $types .= 's';
}

if ($transmissionFilter) {
    $where[] = "c.transmission = ?";
    $params[] = $transmissionFilter;
    $types .= 's';
}

if ($priceMin > 0) {
    $where[] = "c.price >= ?";
    $params[] = $priceMin;
    $types .= 'i';
}

if ($priceMax > 0) {
    $where[] = "c.price <= ?";
    $params[] = $priceMax;
    $types .= 'i';
}

if ($search) {
    $where[] = "(c.model LIKE ? OR cb.name LIKE ? OR c.description LIKE ?)";
    $searchTerm = "%$search%";
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $types .= 'sss';
}

$whereClause = 'WHERE ' . implode(' AND ', $where);

// Sort
$orderBy = match($sort) {
    'price_low' => 'c.price ASC',
    'price_high' => 'c.price DESC',
    'year_new' => 'c.year DESC',
    'year_old' => 'c.year ASC',
    default => 'c.created_at DESC'
};

// Get cars
$sql = "SELECT c.*, cb.name as brand_name, cc.name as category_name
        FROM cars c
        LEFT JOIN car_brands cb ON c.brand_id = cb.id
        LEFT JOIN car_categories cc ON c.category_id = cc.id
        $whereClause
        ORDER BY $orderBy";

if (count($params) > 0) {
    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $cars = $stmt->get_result();
} else {
    $cars = $conn->query($sql);
}

// Get filter options
$brands = $conn->query("SELECT * FROM car_brands ORDER BY name");
$categories = $conn->query("SELECT * FROM car_categories ORDER BY name");

// Page title
$pageTitle = 'Our Cars';
if ($conditionFilter === 'brand_new') $pageTitle = 'Brand New Cars';
if ($conditionFilter === 'recondition') $pageTitle = 'Reconditioned Cars';

include 'includes/header.php';
?>

    <!-- Page Header -->
    <section class="page-header">
        <div class="container">
            <h1><?php echo $pageTitle; ?></h1>
            <p>Browse our selection of quality Japanese vehicles</p>
        </div>
    </section>

    <!-- Cars Listing -->
    <section class="cars-listing">
        <div class="container">
            <div class="listing-layout">
                <!-- Filters Sidebar -->
                <aside class="filters-sidebar">
                    <form method="GET" class="filters-form">
                        <?php if ($search): ?>
                            <input type="hidden" name="search" value="<?php echo htmlspecialchars($search); ?>">
                        <?php endif; ?>

                        <div class="filter-group">
                            <h3>Search</h3>
                            <input type="text" name="search" placeholder="Search cars..." value="<?php echo htmlspecialchars($search); ?>">
                        </div>

                        <div class="filter-group">
                            <h3>Condition</h3>
                            <label class="filter-option">
                                <input type="radio" name="condition" value="" <?php echo !$conditionFilter ? 'checked' : ''; ?>>
                                <span>All</span>
                            </label>
                            <label class="filter-option">
                                <input type="radio" name="condition" value="brand_new" <?php echo $conditionFilter === 'brand_new' ? 'checked' : ''; ?>>
                                <span>Brand New</span>
                            </label>
                            <label class="filter-option">
                                <input type="radio" name="condition" value="recondition" <?php echo $conditionFilter === 'recondition' ? 'checked' : ''; ?>>
                                <span>Reconditioned</span>
                            </label>
                        </div>

                        <div class="filter-group">
                            <h3>Brand</h3>
                            <select name="brand">
                                <option value="">All Brands</option>
                                <?php while ($brand = $brands->fetch_assoc()): ?>
                                    <option value="<?php echo $brand['id']; ?>" <?php echo $brandFilter == $brand['id'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($brand['name']); ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>

                        <div class="filter-group">
                            <h3>Category</h3>
                            <select name="category">
                                <option value="">All Categories</option>
                                <?php while ($category = $categories->fetch_assoc()): ?>
                                    <option value="<?php echo $category['id']; ?>" <?php echo $categoryFilter == $category['id'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($category['name']); ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>

                        <div class="filter-group">
                            <h3>Fuel Type</h3>
                            <select name="fuel">
                                <option value="">All Types</option>
                                <option value="petrol" <?php echo $fuelFilter === 'petrol' ? 'selected' : ''; ?>>Petrol</option>
                                <option value="diesel" <?php echo $fuelFilter === 'diesel' ? 'selected' : ''; ?>>Diesel</option>
                                <option value="hybrid" <?php echo $fuelFilter === 'hybrid' ? 'selected' : ''; ?>>Hybrid</option>
                                <option value="electric" <?php echo $fuelFilter === 'electric' ? 'selected' : ''; ?>>Electric</option>
                            </select>
                        </div>

                        <div class="filter-group">
                            <h3>Transmission</h3>
                            <select name="transmission">
                                <option value="">All</option>
                                <option value="automatic" <?php echo $transmissionFilter === 'automatic' ? 'selected' : ''; ?>>Automatic</option>
                                <option value="manual" <?php echo $transmissionFilter === 'manual' ? 'selected' : ''; ?>>Manual</option>
                                <option value="cvt" <?php echo $transmissionFilter === 'cvt' ? 'selected' : ''; ?>>CVT</option>
                            </select>
                        </div>

                        <div class="filter-group">
                            <h3>Price Range (LKR)</h3>
                            <div class="price-inputs">
                                <input type="number" name="price_min" placeholder="Min" value="<?php echo $priceMin ?: ''; ?>">
                                <span>-</span>
                                <input type="number" name="price_max" placeholder="Max" value="<?php echo $priceMax ?: ''; ?>">
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary btn-block">Apply Filters</button>
                        <a href="cars.php" class="btn btn-outline btn-block">Reset</a>
                    </form>
                </aside>

                <!-- Cars Grid -->
                <div class="listing-main">
                    <div class="listing-header">
                        <p class="results-count"><?php echo $cars->num_rows; ?> vehicles found</p>
                        <div class="listing-sort">
                            <label>Sort by:</label>
                            <select onchange="window.location.href=this.value">
                                <option value="?<?php echo http_build_query(array_merge($_GET, ['sort' => 'newest'])); ?>" <?php echo $sort === 'newest' ? 'selected' : ''; ?>>Newest First</option>
                                <option value="?<?php echo http_build_query(array_merge($_GET, ['sort' => 'price_low'])); ?>" <?php echo $sort === 'price_low' ? 'selected' : ''; ?>>Price: Low to High</option>
                                <option value="?<?php echo http_build_query(array_merge($_GET, ['sort' => 'price_high'])); ?>" <?php echo $sort === 'price_high' ? 'selected' : ''; ?>>Price: High to Low</option>
                                <option value="?<?php echo http_build_query(array_merge($_GET, ['sort' => 'year_new'])); ?>" <?php echo $sort === 'year_new' ? 'selected' : ''; ?>>Year: Newest</option>
                                <option value="?<?php echo http_build_query(array_merge($_GET, ['sort' => 'year_old'])); ?>" <?php echo $sort === 'year_old' ? 'selected' : ''; ?>>Year: Oldest</option>
                            </select>
                        </div>
                    </div>

                    <?php if ($cars->num_rows > 0): ?>
                        <div class="cars-grid">
                            <?php while ($car = $cars->fetch_assoc()): ?>
                                <div class="car-card">
                                    <div class="car-image">
                                        <?php if ($car['main_image']): ?>
                                            <img src="uploads/cars/<?php echo htmlspecialchars($car['main_image']); ?>" alt="<?php echo htmlspecialchars($car['brand_name'] . ' ' . $car['model']); ?>">
                                        <?php else: ?>
                                            <div class="no-image">No Image</div>
                                        <?php endif; ?>
                                        <span class="car-badge <?php echo $car['condition_type']; ?>">
                                            <?php echo $car['condition_type'] === 'brand_new' ? 'Brand New' : 'Recondition'; ?>
                                        </span>
                                    </div>
                                    <div class="car-details">
                                        <h3><?php echo htmlspecialchars($car['brand_name'] . ' ' . $car['model']); ?></h3>
                                        <p class="car-year"><?php echo $car['year']; ?></p>
                                        <div class="car-specs">
                                            <span><?php echo htmlspecialchars($car['engine_capacity']); ?></span>
                                            <span><?php echo ucfirst($car['fuel_type']); ?></span>
                                            <span><?php echo ucfirst($car['transmission']); ?></span>
                                        </div>
                                        <?php if ($car['mileage'] > 0): ?>
                                            <p class="car-mileage"><?php echo number_format($car['mileage']); ?> km</p>
                                        <?php endif; ?>
                                        <div class="car-footer">
                                            <span class="car-price"><?php echo formatPrice($car['price']); ?></span>
                                            <a href="car-details.php?id=<?php echo $car['id']; ?>" class="btn btn-sm">View Details</a>
                                        </div>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        </div>
                    <?php else: ?>
                        <div class="no-results">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="64" height="64">
                                <path d="M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0z"/>
                                <path d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0"/>
                            </svg>
                            <h3>No cars found</h3>
                            <p>Try adjusting your filters or search criteria</p>
                            <a href="cars.php" class="btn btn-primary">View All Cars</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </section>

    <!-- Mobile Filter Button -->
    <button class="mobile-filter-btn" id="mobileFilterBtn">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="20" height="20">
            <path d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
        </svg>
        Filters
    </button>

<?php include 'includes/footer.php'; ?>
