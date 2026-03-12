<?php
require_once '../includes/config.php';

if (!isAdminLoggedIn()) {
    redirect('index.php');
}

$conn = getDBConnection();

// Get statistics
$stats = [];

// Total cars
$result = $conn->query("SELECT COUNT(*) as count FROM cars");
$stats['total_cars'] = $result->fetch_assoc()['count'];

// Available cars
$result = $conn->query("SELECT COUNT(*) as count FROM cars WHERE is_available = 1");
$stats['available_cars'] = $result->fetch_assoc()['count'];

// Pending quotes
$result = $conn->query("SELECT COUNT(*) as count FROM quote_requests WHERE status = 'pending'");
$stats['pending_quotes'] = $result->fetch_assoc()['count'];

// Total quotes this month
$result = $conn->query("SELECT COUNT(*) as count FROM quote_requests WHERE MONTH(created_at) = MONTH(CURRENT_DATE())");
$stats['monthly_quotes'] = $result->fetch_assoc()['count'];

// Pending pre-orders
$result = $conn->query("SELECT COUNT(*) as count FROM preorders WHERE status = 'pending'");
$stats['pending_preorders'] = $result ? $result->fetch_assoc()['count'] : 0;

// Gallery images
$result = $conn->query("SELECT COUNT(*) as count FROM gallery WHERE is_active = 1");
$stats['gallery_images'] = $result ? $result->fetch_assoc()['count'] : 0;

// Recent quotes
$recentQuotes = $conn->query("
    SELECT qr.*, c.model, cb.name as brand_name
    FROM quote_requests qr
    LEFT JOIN cars c ON qr.car_id = c.id
    LEFT JOIN car_brands cb ON c.brand_id = cb.id
    ORDER BY qr.created_at DESC
    LIMIT 5
");

// Recent cars
$recentCars = $conn->query("
    SELECT c.*, cb.name as brand_name
    FROM cars c
    LEFT JOIN car_brands cb ON c.brand_id = cb.id
    ORDER BY c.created_at DESC
    LIMIT 5
");

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - <?php echo SITE_NAME; ?> Admin</title>
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body>
    <div class="admin-wrapper">
        <?php include 'includes/sidebar.php'; ?>

        <main class="admin-main">
            <?php include 'includes/header.php'; ?>

            <div class="admin-content">
                <div class="page-header">
                    <h1>Dashboard</h1>
                    <p>Welcome back, <?php echo htmlspecialchars($_SESSION['admin_username']); ?>!</p>
                </div>

                <!-- Statistics Cards -->
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-icon bg-primary">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M19 17h2l-2-4V9a7 7 0 10-14 0v4l-2 4h2m15 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                            </svg>
                        </div>
                        <div class="stat-details">
                            <h3><?php echo $stats['total_cars']; ?></h3>
                            <p>Total Cars</p>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-icon bg-success">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <div class="stat-details">
                            <h3><?php echo $stats['available_cars']; ?></h3>
                            <p>Available Cars</p>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-icon bg-warning">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                            </svg>
                        </div>
                        <div class="stat-details">
                            <h3><?php echo $stats['pending_quotes']; ?></h3>
                            <p>Pending Quotes</p>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-icon bg-info">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                            </svg>
                        </div>
                        <div class="stat-details">
                            <h3><?php echo $stats['monthly_quotes']; ?></h3>
                            <p>Quotes This Month</p>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-icon bg-warning">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                            </svg>
                        </div>
                        <div class="stat-details">
                            <h3><?php echo $stats['pending_preorders']; ?></h3>
                            <p>Pending Pre-Orders</p>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-icon bg-primary">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                        </div>
                        <div class="stat-details">
                            <h3><?php echo $stats['gallery_images']; ?></h3>
                            <p>Gallery Images</p>
                        </div>
                    </div>
                </div>

                <!-- Recent Data -->
                <div class="dashboard-grid">
                    <!-- Recent Quote Requests -->
                    <div class="card">
                        <div class="card-header">
                            <h2>Recent Quote Requests</h2>
                            <a href="quotes.php" class="btn btn-sm">View All</a>
                        </div>
                        <div class="card-body">
                            <?php if ($recentQuotes->num_rows > 0): ?>
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>Customer</th>
                                            <th>Car</th>
                                            <th>Status</th>
                                            <th>Date</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php while ($quote = $recentQuotes->fetch_assoc()): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($quote['customer_name']); ?></td>
                                                <td><?php echo $quote['brand_name'] ? htmlspecialchars($quote['brand_name'] . ' ' . $quote['model']) : 'General Inquiry'; ?></td>
                                                <td><span class="badge badge-<?php echo $quote['status']; ?>"><?php echo ucfirst($quote['status']); ?></span></td>
                                                <td><?php echo date('M d, Y', strtotime($quote['created_at'])); ?></td>
                                            </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                            <?php else: ?>
                                <p class="text-muted">No quote requests yet.</p>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Recently Added Cars -->
                    <div class="card">
                        <div class="card-header">
                            <h2>Recently Added Cars</h2>
                            <a href="cars.php" class="btn btn-sm">View All</a>
                        </div>
                        <div class="card-body">
                            <?php if ($recentCars->num_rows > 0): ?>
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>Car</th>
                                            <th>Price</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php while ($car = $recentCars->fetch_assoc()): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($car['brand_name'] . ' ' . $car['model'] . ' ' . $car['year']); ?></td>
                                                <td><?php echo formatPrice($car['price']); ?></td>
                                                <td>
                                                    <?php if ($car['is_available']): ?>
                                                        <span class="badge badge-success">Available</span>
                                                    <?php else: ?>
                                                        <span class="badge badge-danger">Sold</span>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                            <?php else: ?>
                                <p class="text-muted">No cars added yet.</p>
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
