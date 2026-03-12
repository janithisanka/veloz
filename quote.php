<?php
require_once 'includes/config.php';

$conn = getDBConnection();

$carId = isset($_GET['car']) ? (int)$_GET['car'] : 0;
$selectedCar = null;

if ($carId > 0) {
    $stmt = $conn->prepare("
        SELECT c.*, cb.name as brand_name
        FROM cars c
        LEFT JOIN car_brands cb ON c.brand_id = cb.id
        WHERE c.id = ?
    ");
    $stmt->bind_param("i", $carId);
    $stmt->execute();
    $selectedCar = $stmt->get_result()->fetch_assoc();
    $stmt->close();
}

$success = false;
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitize($_POST['name'] ?? '');
    $email = sanitize($_POST['email'] ?? '');
    $phone = sanitize($_POST['phone'] ?? '');
    $location = sanitize($_POST['location'] ?? '');
    $message = sanitize($_POST['message'] ?? '');
    $preferred_contact = sanitize($_POST['preferred_contact'] ?? 'phone');
    $car_id = (int)($_POST['car_id'] ?? 0);

    // Validation
    if (empty($name)) $errors[] = "Name is required";
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Valid email is required";
    if (empty($phone)) $errors[] = "Phone number is required";

    if (empty($errors)) {
        $stmt = $conn->prepare("INSERT INTO quote_requests (car_id, customer_name, email, phone, location, message, preferred_contact) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $carIdValue = $car_id > 0 ? $car_id : null;
        $stmt->bind_param("issssss", $carIdValue, $name, $email, $phone, $location, $message, $preferred_contact);

        if ($stmt->execute()) {
            $success = true;
        } else {
            $errors[] = "Failed to submit quote request. Please try again.";
        }
        $stmt->close();
    }
}

// Get available cars for dropdown
$cars = $conn->query("
    SELECT c.id, c.model, c.year, c.price, cb.name as brand_name
    FROM cars c
    LEFT JOIN car_brands cb ON c.brand_id = cb.id
    WHERE c.is_available = 1
    ORDER BY cb.name, c.model
");

$pageTitle = 'Request a Quote';

include 'includes/header.php';
?>

    <!-- Page Header -->
    <section class="page-header">
        <div class="container">
            <h1>Request a Quote</h1>
            <p>Get the best price for your dream car</p>
        </div>
    </section>

    <!-- Quote Form -->
    <section class="quote-section">
        <div class="container">
            <?php if ($success): ?>
                <div class="success-message">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="64" height="64">
                        <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <h2>Thank You!</h2>
                    <p>Your quote request has been submitted successfully. Our team will contact you shortly.</p>
                    <div class="success-actions">
                        <a href="cars.php" class="btn btn-primary">Browse More Cars</a>
                        <a href="<?php echo SITE_URL; ?>" class="btn btn-outline">Back to Home</a>
                    </div>
                </div>
            <?php else: ?>
                <div class="quote-grid">
                    <div class="quote-form-container">
                        <div class="card">
                            <div class="card-header">
                                <h2>Fill in Your Details</h2>
                                <p>We'll get back to you with the best offer</p>
                            </div>
                            <div class="card-body">
                                <?php if (!empty($errors)): ?>
                                    <div class="alert alert-error">
                                        <ul>
                                            <?php foreach ($errors as $error): ?>
                                                <li><?php echo $error; ?></li>
                                            <?php endforeach; ?>
                                        </ul>
                                    </div>
                                <?php endif; ?>

                                <form method="POST" class="quote-form">
                                    <div class="form-group">
                                        <label for="car_id">Interested Vehicle</label>
                                        <select name="car_id" id="car_id">
                                            <option value="">General Inquiry / Custom Order</option>
                                            <?php while ($car = $cars->fetch_assoc()): ?>
                                                <option value="<?php echo $car['id']; ?>" <?php echo ($selectedCar && $selectedCar['id'] == $car['id']) ? 'selected' : ''; ?>>
                                                    <?php echo htmlspecialchars($car['brand_name'] . ' ' . $car['model'] . ' ' . $car['year']); ?> - <?php echo formatPrice($car['price']); ?>
                                                </option>
                                            <?php endwhile; ?>
                                        </select>
                                    </div>

                                    <div class="form-row">
                                        <div class="form-group">
                                            <label for="name">Your Name *</label>
                                            <input type="text" name="name" id="name" required placeholder="Enter your full name">
                                        </div>
                                        <div class="form-group">
                                            <label for="phone">Phone Number *</label>
                                            <input type="tel" name="phone" id="phone" required placeholder="e.g., 077 123 4567">
                                        </div>
                                    </div>

                                    <div class="form-row">
                                        <div class="form-group">
                                            <label for="email">Email Address *</label>
                                            <input type="email" name="email" id="email" required placeholder="your@email.com">
                                        </div>
                                        <div class="form-group">
                                            <label for="location">Your Location</label>
                                            <input type="text" name="location" id="location" placeholder="e.g., Colombo">
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label for="preferred_contact">Preferred Contact Method</label>
                                        <div class="radio-group">
                                            <label class="radio-option">
                                                <input type="radio" name="preferred_contact" value="phone" checked>
                                                <span>Phone Call</span>
                                            </label>
                                            <label class="radio-option">
                                                <input type="radio" name="preferred_contact" value="whatsapp">
                                                <span>WhatsApp</span>
                                            </label>
                                            <label class="radio-option">
                                                <input type="radio" name="preferred_contact" value="email">
                                                <span>Email</span>
                                            </label>
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label for="message">Your Message / Requirements</label>
                                        <textarea name="message" id="message" rows="4" placeholder="Tell us about your requirements, budget, or any specific features you're looking for..."></textarea>
                                    </div>

                                    <button type="submit" class="btn btn-primary btn-lg btn-block">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="20" height="20">
                                            <path d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                        </svg>
                                        Submit Quote Request
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>

                    <div class="quote-sidebar">
                        <?php if ($selectedCar): ?>
                            <div class="card selected-car-card">
                                <div class="card-header">
                                    <h3>Selected Vehicle</h3>
                                </div>
                                <div class="card-body">
                                    <?php if ($selectedCar['main_image']): ?>
                                        <img src="uploads/cars/<?php echo htmlspecialchars($selectedCar['main_image']); ?>" alt="<?php echo htmlspecialchars($selectedCar['brand_name'] . ' ' . $selectedCar['model']); ?>">
                                    <?php endif; ?>
                                    <h4><?php echo htmlspecialchars($selectedCar['brand_name'] . ' ' . $selectedCar['model']); ?></h4>
                                    <p><?php echo $selectedCar['year']; ?> | <?php echo ucfirst($selectedCar['fuel_type']); ?> | <?php echo ucfirst($selectedCar['transmission']); ?></p>
                                    <span class="price"><?php echo formatPrice($selectedCar['price']); ?></span>
                                </div>
                            </div>
                        <?php endif; ?>

                        <div class="card contact-card">
                            <div class="card-header">
                                <h3>Contact Us Directly</h3>
                            </div>
                            <div class="card-body">
                                <p>Prefer to talk to us directly? Reach out through any of these channels:</p>
                                <ul class="contact-options">
                                    <li>
                                        <a href="tel:<?php echo htmlspecialchars($siteSettings['phone'] ?? ''); ?>">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="20" height="20">
                                                <path d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                                            </svg>
                                            <?php echo htmlspecialchars($siteSettings['phone'] ?? '+94 77 123 4567'); ?>
                                        </a>
                                    </li>
                                    <?php if (!empty($siteSettings['whatsapp'])): ?>
                                        <li>
                                            <a href="https://wa.me/<?php echo htmlspecialchars($siteSettings['whatsapp']); ?>" target="_blank">
                                                <svg viewBox="0 0 24 24" fill="currentColor" width="20" height="20">
                                                    <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
                                                </svg>
                                                WhatsApp Chat
                                            </a>
                                        </li>
                                    <?php endif; ?>
                                    <li>
                                        <a href="mailto:<?php echo htmlspecialchars($siteSettings['email'] ?? 'info@velozautohaus.lk'); ?>">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="20" height="20">
                                                <path d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                            </svg>
                                            <?php echo htmlspecialchars($siteSettings['email'] ?? 'info@velozautohaus.lk'); ?>
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </div>

                        <div class="card info-card">
                            <div class="card-body">
                                <h4>Why Choose Us?</h4>
                                <ul>
                                    <li>Lowest prices guaranteed</li>
                                    <li>Direct imports from Japan</li>
                                    <li>Complete documentation support</li>
                                    <li>After-sales service</li>
                                    <li>Flexible payment options</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </section>

<?php include 'includes/footer.php'; ?>
