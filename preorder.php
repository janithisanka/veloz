<?php
require_once 'includes/config.php';

$conn = getDBConnection();

$success = false;
$errors = [];

// Get brands for dropdown
$brands = $conn->query("SELECT * FROM car_brands ORDER BY name");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitize($_POST['customer_name'] ?? '');
    $email = sanitize($_POST['email'] ?? '');
    $phone = sanitize($_POST['phone'] ?? '');
    $brand = sanitize($_POST['brand'] ?? '');
    $model = sanitize($_POST['model'] ?? '');
    $year_from = (int)($_POST['year_from'] ?? 0);
    $year_to = (int)($_POST['year_to'] ?? 0);
    $budget_min = (float)($_POST['budget_min'] ?? 0);
    $budget_max = (float)($_POST['budget_max'] ?? 0);
    $fuel = sanitize($_POST['fuel_preference'] ?? 'any');
    $transmission = sanitize($_POST['transmission_preference'] ?? 'any');
    $color = sanitize($_POST['color_preference'] ?? '');
    $notes = sanitize($_POST['additional_notes'] ?? '');

    if (empty($name)) $errors[] = "Name is required";
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Valid email is required";
    if (empty($phone)) $errors[] = "Phone number is required";

    if (empty($errors)) {
        // Check if preorders table exists
        $tableCheck = $conn->query("SHOW TABLES LIKE 'preorders'");
        if ($tableCheck->num_rows === 0) {
            $conn->query("CREATE TABLE IF NOT EXISTS preorders (
                id INT AUTO_INCREMENT PRIMARY KEY,
                customer_name VARCHAR(100) NOT NULL,
                email VARCHAR(100) NOT NULL,
                phone VARCHAR(20) NOT NULL,
                brand VARCHAR(100),
                model VARCHAR(100),
                year_from INT,
                year_to INT,
                budget_min DECIMAL(15,2),
                budget_max DECIMAL(15,2),
                fuel_preference ENUM('any','petrol','diesel','hybrid','electric') DEFAULT 'any',
                transmission_preference ENUM('any','automatic','manual','cvt') DEFAULT 'any',
                color_preference VARCHAR(100),
                additional_notes TEXT,
                status ENUM('pending','sourcing','found','confirmed','shipped','delivered','cancelled') DEFAULT 'pending',
                admin_notes TEXT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            )");
        }

        $stmt = $conn->prepare("INSERT INTO preorders (customer_name, email, phone, brand, model, year_from, year_to, budget_min, budget_max, fuel_preference, transmission_preference, color_preference, additional_notes) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssssiiddssss", $name, $email, $phone, $brand, $model, $year_from, $year_to, $budget_min, $budget_max, $fuel, $transmission, $color, $notes);

        if ($stmt->execute()) {
            $success = true;
        } else {
            $errors[] = "Failed to submit. Please try again.";
        }
        $stmt->close();
    }
}

$pageTitle = 'Pre-Order Import';

include 'includes/header.php';
?>

    <!-- Page Header -->
    <section class="page-hero">
        <div class="container">
            <span class="hero-badge">Direct Japan Import</span>
            <h1>Pre-Order Your <span>Dream Car</span></h1>
            <p>Tell us what you're looking for and we'll source it directly from Japanese auctions at the best price</p>
        </div>
    </section>

    <!-- How It Works -->
    <section class="process-section">
        <div class="container">
            <div class="section-header">
                <span class="section-badge">How It Works</span>
                <h2>Simple 4-Step Import Process</h2>
            </div>
            <div class="process-grid">
                <div class="process-step">
                    <div class="step-number">01</div>
                    <h3>Tell Us What You Want</h3>
                    <p>Fill in the form below with your preferred brand, model, budget, and specifications.</p>
                </div>
                <div class="process-step">
                    <div class="step-number">02</div>
                    <h3>We Source From Japan</h3>
                    <p>Our team searches Japanese auctions and dealers to find the perfect match for you.</p>
                </div>
                <div class="process-step">
                    <div class="step-number">03</div>
                    <h3>Confirm & Ship</h3>
                    <p>Once you approve, we handle all shipping, customs clearance and documentation.</p>
                </div>
                <div class="process-step">
                    <div class="step-number">04</div>
                    <h3>Receive Your Car</h3>
                    <p>Your vehicle arrives in Sri Lanka ready for registration. We assist with the entire process.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Pre-Order Form -->
    <section class="preorder-section">
        <div class="container">
            <?php if ($success): ?>
                <div class="success-message">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="64" height="64"><path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <h2>Pre-Order Submitted!</h2>
                    <p>Thank you! Our team will review your requirements and contact you within 24 hours with available options.</p>
                    <div class="success-actions">
                        <a href="cars.php" class="btn btn-primary">Browse Available Cars</a>
                        <a href="<?php echo SITE_URL; ?>" class="btn btn-outline">Back to Home</a>
                    </div>
                </div>
            <?php else: ?>
                <div class="preorder-grid">
                    <div class="preorder-form-container">
                        <div class="card">
                            <div class="card-header">
                                <h2>Your Requirements</h2>
                                <p>Tell us exactly what you're looking for</p>
                            </div>
                            <div class="card-body">
                                <?php if (!empty($errors)): ?>
                                    <div class="alert alert-error">
                                        <ul><?php foreach ($errors as $err): ?><li><?php echo $err; ?></li><?php endforeach; ?></ul>
                                    </div>
                                <?php endif; ?>

                                <form method="POST" class="preorder-form">
                                    <h3 class="form-section-title">Vehicle Preferences</h3>

                                    <div class="form-row">
                                        <div class="form-group">
                                            <label for="brand">Preferred Brand</label>
                                            <select name="brand" id="brand">
                                                <option value="">Any Brand</option>
                                                <?php while ($b = $brands->fetch_assoc()): ?>
                                                    <option value="<?php echo htmlspecialchars($b['name']); ?>"><?php echo htmlspecialchars($b['name']); ?></option>
                                                <?php endwhile; ?>
                                            </select>
                                        </div>
                                        <div class="form-group">
                                            <label for="model">Preferred Model</label>
                                            <input type="text" name="model" id="model" placeholder="e.g., Corolla, Civic, Swift">
                                        </div>
                                    </div>

                                    <div class="form-row">
                                        <div class="form-group">
                                            <label for="year_from">Year From</label>
                                            <select name="year_from" id="year_from">
                                                <option value="0">Any</option>
                                                <?php for ($y = date('Y') + 1; $y >= 2015; $y--): ?>
                                                    <option value="<?php echo $y; ?>"><?php echo $y; ?></option>
                                                <?php endfor; ?>
                                            </select>
                                        </div>
                                        <div class="form-group">
                                            <label for="year_to">Year To</label>
                                            <select name="year_to" id="year_to">
                                                <option value="0">Any</option>
                                                <?php for ($y = date('Y') + 1; $y >= 2015; $y--): ?>
                                                    <option value="<?php echo $y; ?>"><?php echo $y; ?></option>
                                                <?php endfor; ?>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="form-row">
                                        <div class="form-group">
                                            <label for="fuel_preference">Fuel Type</label>
                                            <select name="fuel_preference" id="fuel_preference">
                                                <option value="any">Any</option>
                                                <option value="petrol">Petrol</option>
                                                <option value="diesel">Diesel</option>
                                                <option value="hybrid">Hybrid</option>
                                                <option value="electric">Electric</option>
                                            </select>
                                        </div>
                                        <div class="form-group">
                                            <label for="transmission_preference">Transmission</label>
                                            <select name="transmission_preference" id="transmission_preference">
                                                <option value="any">Any</option>
                                                <option value="automatic">Automatic</option>
                                                <option value="manual">Manual</option>
                                                <option value="cvt">CVT</option>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label for="color_preference">Color Preference</label>
                                        <input type="text" name="color_preference" id="color_preference" placeholder="e.g., White, Black, Silver">
                                    </div>

                                    <h3 class="form-section-title">Budget (LKR)</h3>

                                    <div class="form-row">
                                        <div class="form-group">
                                            <label for="budget_min">Minimum Budget</label>
                                            <input type="number" name="budget_min" id="budget_min" placeholder="e.g., 5000000" step="100000">
                                        </div>
                                        <div class="form-group">
                                            <label for="budget_max">Maximum Budget</label>
                                            <input type="number" name="budget_max" id="budget_max" placeholder="e.g., 10000000" step="100000">
                                        </div>
                                    </div>

                                    <h3 class="form-section-title">Your Contact Details</h3>

                                    <div class="form-row">
                                        <div class="form-group">
                                            <label for="customer_name">Full Name *</label>
                                            <input type="text" name="customer_name" id="customer_name" required placeholder="Your full name">
                                        </div>
                                        <div class="form-group">
                                            <label for="phone">Phone Number *</label>
                                            <input type="tel" name="phone" id="phone" required placeholder="e.g., 076 088 1409">
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label for="email">Email Address *</label>
                                        <input type="email" name="email" id="email" required placeholder="your@email.com">
                                    </div>

                                    <div class="form-group">
                                        <label for="additional_notes">Additional Requirements</label>
                                        <textarea name="additional_notes" id="additional_notes" rows="4" placeholder="Any specific features, grades, or other requirements..."></textarea>
                                    </div>

                                    <button type="submit" class="btn btn-primary btn-lg btn-block">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="20" height="20"><path d="M5 13l4 4L19 7"/></svg>
                                        Submit Pre-Order Request
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>

                    <div class="preorder-sidebar">
                        <div class="card info-card">
                            <div class="card-body">
                                <h3>Why Pre-Order?</h3>
                                <ul class="benefits-list">
                                    <li>
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="20" height="20"><path d="M5 13l4 4L19 7"/></svg>
                                        Get the exact car you want
                                    </li>
                                    <li>
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="20" height="20"><path d="M5 13l4 4L19 7"/></svg>
                                        Best auction prices from Japan
                                    </li>
                                    <li>
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="20" height="20"><path d="M5 13l4 4L19 7"/></svg>
                                        Complete vehicle history report
                                    </li>
                                    <li>
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="20" height="20"><path d="M5 13l4 4L19 7"/></svg>
                                        Full customs & registration support
                                    </li>
                                    <li>
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="20" height="20"><path d="M5 13l4 4L19 7"/></svg>
                                        Delivery within 4-6 weeks
                                    </li>
                                    <li>
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="20" height="20"><path d="M5 13l4 4L19 7"/></svg>
                                        Transparent pricing, no hidden costs
                                    </li>
                                </ul>
                            </div>
                        </div>

                        <div class="card contact-card">
                            <div class="card-body">
                                <h3>Need Help?</h3>
                                <p>Talk to our import specialists directly:</p>
                                <ul class="contact-options">
                                    <li>
                                        <a href="tel:<?php echo htmlspecialchars($siteSettings['phone'] ?? '+94 76 088 1409'); ?>">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="20" height="20"><path d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                                            <?php echo htmlspecialchars($siteSettings['phone'] ?? '+94 76 088 1409'); ?>
                                        </a>
                                    </li>
                                    <?php if (!empty($siteSettings['whatsapp'])): ?>
                                    <li>
                                        <a href="https://wa.me/<?php echo htmlspecialchars($siteSettings['whatsapp']); ?>?text=Hi, I want to pre-order a car from Japan" target="_blank">
                                            <svg viewBox="0 0 24 24" fill="currentColor" width="20" height="20"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                                            WhatsApp Us
                                        </a>
                                    </li>
                                    <?php endif; ?>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </section>

<?php include 'includes/footer.php'; ?>
