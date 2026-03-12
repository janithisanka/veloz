<?php
require_once 'includes/config.php';

$conn = getDBConnection();

$carId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($carId <= 0) {
    redirect('cars.php');
}

// Get car details
$stmt = $conn->prepare("
    SELECT c.*, cb.name as brand_name, cc.name as category_name
    FROM cars c
    LEFT JOIN car_brands cb ON c.brand_id = cb.id
    LEFT JOIN car_categories cc ON c.category_id = cc.id
    WHERE c.id = ?
");
$stmt->bind_param("i", $carId);
$stmt->execute();
$car = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$car) {
    redirect('cars.php');
}

// Get additional images
$images = $conn->query("SELECT * FROM car_images WHERE car_id = $carId ORDER BY sort_order");

// Get similar cars
$similarCars = $conn->query("
    SELECT c.*, cb.name as brand_name
    FROM cars c
    LEFT JOIN car_brands cb ON c.brand_id = cb.id
    WHERE c.id != $carId
    AND c.is_available = 1
    AND (c.brand_id = {$car['brand_id']} OR c.category_id = {$car['category_id']})
    ORDER BY RAND()
    LIMIT 4
");

$pageTitle = $car['brand_name'] . ' ' . $car['model'] . ' ' . $car['year'];

include 'includes/header.php';
?>

    <!-- Breadcrumb -->
    <div class="breadcrumb">
        <div class="container">
            <a href="<?php echo SITE_URL; ?>">Home</a>
            <span>/</span>
            <a href="cars.php">Cars</a>
            <span>/</span>
            <span><?php echo htmlspecialchars($car['brand_name'] . ' ' . $car['model']); ?></span>
        </div>
    </div>

    <!-- Car Details -->
    <section class="car-details-section">
        <div class="container">
            <div class="car-details-grid">
                <!-- Image Gallery -->
                <div class="car-gallery">
                    <div class="main-image">
                        <?php if ($car['main_image']): ?>
                            <img src="uploads/cars/<?php echo htmlspecialchars($car['main_image']); ?>" alt="<?php echo htmlspecialchars($pageTitle); ?>" id="mainImage">
                        <?php else: ?>
                            <div class="no-image">No Image Available</div>
                        <?php endif; ?>
                        <span class="car-badge <?php echo $car['condition_type']; ?>">
                            <?php echo $car['condition_type'] === 'brand_new' ? 'Brand New' : 'Recondition'; ?>
                        </span>
                    </div>

                    <?php if ($images->num_rows > 0 || $car['main_image']): ?>
                        <div class="thumbnail-gallery">
                            <?php if ($car['main_image']): ?>
                                <div class="thumbnail active" onclick="changeImage('uploads/cars/<?php echo htmlspecialchars($car['main_image']); ?>', this)">
                                    <img src="uploads/cars/<?php echo htmlspecialchars($car['main_image']); ?>" alt="Thumbnail">
                                </div>
                            <?php endif; ?>
                            <?php while ($img = $images->fetch_assoc()): ?>
                                <div class="thumbnail" onclick="changeImage('uploads/cars/<?php echo htmlspecialchars($img['image_path']); ?>', this)">
                                    <img src="uploads/cars/<?php echo htmlspecialchars($img['image_path']); ?>" alt="Thumbnail">
                                </div>
                            <?php endwhile; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Car Info -->
                <div class="car-info">
                    <div class="car-title">
                        <h1><?php echo htmlspecialchars($car['brand_name'] . ' ' . $car['model']); ?></h1>
                        <p class="car-subtitle"><?php echo $car['year']; ?> | <?php echo htmlspecialchars($car['category_name'] ?? ''); ?></p>
                    </div>

                    <div class="car-price-box">
                        <span class="price-label">Price</span>
                        <span class="price-value"><?php echo formatPrice($car['price']); ?></span>
                        <?php if (!$car['is_available']): ?>
                            <span class="sold-badge">SOLD</span>
                        <?php endif; ?>
                    </div>

                    <!-- Quick Specs -->
                    <div class="quick-specs">
                        <div class="spec-item">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="24" height="24">
                                <path d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                            <span class="spec-label">Year</span>
                            <span class="spec-value"><?php echo $car['year']; ?></span>
                        </div>
                        <div class="spec-item">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="24" height="24">
                                <path d="M13 10V3L4 14h7v7l9-11h-7z"/>
                            </svg>
                            <span class="spec-label">Engine</span>
                            <span class="spec-value"><?php echo htmlspecialchars($car['engine_capacity']); ?></span>
                        </div>
                        <div class="spec-item">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="24" height="24">
                                <path d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                            </svg>
                            <span class="spec-label">Fuel</span>
                            <span class="spec-value"><?php echo ucfirst($car['fuel_type']); ?></span>
                        </div>
                        <div class="spec-item">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="24" height="24">
                                <path d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                                <path d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                            <span class="spec-label">Transmission</span>
                            <span class="spec-value"><?php echo ucfirst($car['transmission']); ?></span>
                        </div>
                        <?php if ($car['mileage'] > 0): ?>
                            <div class="spec-item">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="24" height="24">
                                    <path d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                                </svg>
                                <span class="spec-label">Mileage</span>
                                <span class="spec-value"><?php echo number_format($car['mileage']); ?> km</span>
                            </div>
                        <?php endif; ?>
                        <?php if ($car['color']): ?>
                            <div class="spec-item">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="24" height="24">
                                    <path d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01"/>
                                </svg>
                                <span class="spec-label">Color</span>
                                <span class="spec-value"><?php echo htmlspecialchars($car['color']); ?></span>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Action Buttons -->
                    <?php if ($car['is_available']): ?>
                        <div class="car-actions">
                            <a href="quote.php?car=<?php echo $car['id']; ?>" class="btn btn-primary btn-lg btn-block">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="20" height="20">
                                    <path d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                                </svg>
                                Request Quote
                            </a>
                            <?php if (!empty($siteSettings['whatsapp'])): ?>
                                <a href="https://wa.me/<?php echo htmlspecialchars($siteSettings['whatsapp']); ?>?text=Hi, I'm interested in the <?php echo urlencode($car['brand_name'] . ' ' . $car['model'] . ' ' . $car['year']); ?> (<?php echo formatPrice($car['price']); ?>)" class="btn btn-whatsapp btn-lg btn-block" target="_blank">
                                    <svg viewBox="0 0 24 24" fill="currentColor" width="20" height="20">
                                        <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
                                    </svg>
                                    Chat on WhatsApp
                                </a>
                            <?php endif; ?>
                            <a href="tel:<?php echo htmlspecialchars($siteSettings['phone'] ?? ''); ?>" class="btn btn-outline btn-lg btn-block">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="20" height="20">
                                    <path d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                                </svg>
                                Call Us
                            </a>
                        </div>
                    <?php else: ?>
                        <div class="sold-notice">
                            <p>This vehicle has been sold. Browse our other available vehicles.</p>
                            <a href="cars.php" class="btn btn-primary">View Available Cars</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Additional Details -->
            <div class="car-details-tabs">
                <div class="tabs-nav">
                    <button class="tab-btn active" data-tab="description">Description</button>
                    <button class="tab-btn" data-tab="features">Features</button>
                    <button class="tab-btn" data-tab="specifications">Specifications</button>
                </div>

                <div class="tabs-content">
                    <div class="tab-panel active" id="description">
                        <?php if ($car['description']): ?>
                            <p><?php echo nl2br(htmlspecialchars($car['description'])); ?></p>
                        <?php else: ?>
                            <p>Contact us for more details about this vehicle.</p>
                        <?php endif; ?>
                    </div>

                    <div class="tab-panel" id="features">
                        <?php if ($car['features']): ?>
                            <ul class="features-list">
                                <?php
                                $features = explode(',', $car['features']);
                                foreach ($features as $feature):
                                    $feature = trim($feature);
                                    if ($feature):
                                ?>
                                    <li>
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16">
                                            <path d="M5 13l4 4L19 7"/>
                                        </svg>
                                        <?php echo htmlspecialchars($feature); ?>
                                    </li>
                                <?php
                                    endif;
                                endforeach;
                                ?>
                            </ul>
                        <?php else: ?>
                            <p>Contact us for feature details.</p>
                        <?php endif; ?>
                    </div>

                    <div class="tab-panel" id="specifications">
                        <table class="specs-table">
                            <tr>
                                <th>Brand</th>
                                <td><?php echo htmlspecialchars($car['brand_name']); ?></td>
                            </tr>
                            <tr>
                                <th>Model</th>
                                <td><?php echo htmlspecialchars($car['model']); ?></td>
                            </tr>
                            <tr>
                                <th>Year</th>
                                <td><?php echo $car['year']; ?></td>
                            </tr>
                            <tr>
                                <th>Condition</th>
                                <td><?php echo $car['condition_type'] === 'brand_new' ? 'Brand New' : 'Reconditioned'; ?></td>
                            </tr>
                            <tr>
                                <th>Engine Capacity</th>
                                <td><?php echo htmlspecialchars($car['engine_capacity']); ?></td>
                            </tr>
                            <tr>
                                <th>Fuel Type</th>
                                <td><?php echo ucfirst($car['fuel_type']); ?></td>
                            </tr>
                            <tr>
                                <th>Transmission</th>
                                <td><?php echo ucfirst($car['transmission']); ?></td>
                            </tr>
                            <?php if ($car['mileage'] > 0): ?>
                                <tr>
                                    <th>Mileage</th>
                                    <td><?php echo number_format($car['mileage']); ?> km</td>
                                </tr>
                            <?php endif; ?>
                            <?php if ($car['color']): ?>
                                <tr>
                                    <th>Color</th>
                                    <td><?php echo htmlspecialchars($car['color']); ?></td>
                                </tr>
                            <?php endif; ?>
                            <?php if ($car['body_type']): ?>
                                <tr>
                                    <th>Body Type</th>
                                    <td><?php echo htmlspecialchars($car['body_type']); ?></td>
                                </tr>
                            <?php endif; ?>
                            <tr>
                                <th>Seats</th>
                                <td><?php echo $car['seats']; ?></td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Similar Cars -->
    <?php if ($similarCars->num_rows > 0): ?>
    <section class="similar-cars">
        <div class="container">
            <div class="section-header">
                <h2>Similar Vehicles</h2>
            </div>
            <div class="cars-grid">
                <?php while ($similar = $similarCars->fetch_assoc()): ?>
                    <div class="car-card">
                        <div class="car-image">
                            <?php if ($similar['main_image']): ?>
                                <img src="uploads/cars/<?php echo htmlspecialchars($similar['main_image']); ?>" alt="<?php echo htmlspecialchars($similar['brand_name'] . ' ' . $similar['model']); ?>">
                            <?php else: ?>
                                <div class="no-image">No Image</div>
                            <?php endif; ?>
                            <span class="car-badge <?php echo $similar['condition_type']; ?>">
                                <?php echo $similar['condition_type'] === 'brand_new' ? 'Brand New' : 'Recondition'; ?>
                            </span>
                        </div>
                        <div class="car-details">
                            <h3><?php echo htmlspecialchars($similar['brand_name'] . ' ' . $similar['model']); ?></h3>
                            <p class="car-year"><?php echo $similar['year']; ?></p>
                            <div class="car-footer">
                                <span class="car-price"><?php echo formatPrice($similar['price']); ?></span>
                                <a href="car-details.php?id=<?php echo $similar['id']; ?>" class="btn btn-sm">View Details</a>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <script>
        function changeImage(src, element) {
            document.getElementById('mainImage').src = src;
            document.querySelectorAll('.thumbnail').forEach(t => t.classList.remove('active'));
            element.classList.add('active');
        }

        // Tabs functionality
        document.querySelectorAll('.tab-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const tab = this.dataset.tab;
                document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
                document.querySelectorAll('.tab-panel').forEach(p => p.classList.remove('active'));
                this.classList.add('active');
                document.getElementById(tab).classList.add('active');
            });
        });
    </script>

<?php include 'includes/footer.php'; ?>
