<?php
require_once '../includes/config.php';

if (!isAdminLoggedIn()) {
    redirect('index.php');
}

$conn = getDBConnection();

// Handle status update
if (isset($_POST['update_status'])) {
    $quoteId = (int)$_POST['quote_id'];
    $status = sanitize($_POST['status']);
    $notes = sanitize($_POST['admin_notes']);

    $stmt = $conn->prepare("UPDATE quote_requests SET status = ?, admin_notes = ? WHERE id = ?");
    $stmt->bind_param("ssi", $status, $notes, $quoteId);
    $stmt->execute();
    $stmt->close();
    redirect('quotes.php?updated=1');
}

// Handle delete
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $quoteId = (int)$_GET['delete'];
    $conn->query("DELETE FROM quote_requests WHERE id = $quoteId");
    redirect('quotes.php?deleted=1');
}

// Get filter
$statusFilter = isset($_GET['status']) ? sanitize($_GET['status']) : '';

$where = $statusFilter ? "WHERE qr.status = '$statusFilter'" : "";

// Get quotes
$quotes = $conn->query("
    SELECT qr.*, c.model, c.year, c.price, c.main_image, cb.name as brand_name
    FROM quote_requests qr
    LEFT JOIN cars c ON qr.car_id = c.id
    LEFT JOIN car_brands cb ON c.brand_id = cb.id
    $where
    ORDER BY qr.created_at DESC
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quote Requests - <?php echo SITE_NAME; ?> Admin</title>
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
                        <h1>Quote Requests</h1>
                        <p>Manage customer quote requests</p>
                    </div>
                </div>

                <?php if (isset($_GET['updated'])): ?>
                    <div class="alert alert-success">Quote updated successfully.</div>
                <?php endif; ?>

                <?php if (isset($_GET['deleted'])): ?>
                    <div class="alert alert-success">Quote deleted successfully.</div>
                <?php endif; ?>

                <!-- Filters -->
                <div class="card mb-4">
                    <div class="card-body">
                        <div class="filter-tabs">
                            <a href="quotes.php" class="<?php echo !$statusFilter ? 'active' : ''; ?>">All</a>
                            <a href="?status=pending" class="<?php echo $statusFilter === 'pending' ? 'active' : ''; ?>">Pending</a>
                            <a href="?status=contacted" class="<?php echo $statusFilter === 'contacted' ? 'active' : ''; ?>">Contacted</a>
                            <a href="?status=converted" class="<?php echo $statusFilter === 'converted' ? 'active' : ''; ?>">Converted</a>
                            <a href="?status=closed" class="<?php echo $statusFilter === 'closed' ? 'active' : ''; ?>">Closed</a>
                        </div>
                    </div>
                </div>

                <!-- Quotes -->
                <div class="quotes-list">
                    <?php if ($quotes->num_rows > 0): ?>
                        <?php while ($quote = $quotes->fetch_assoc()): ?>
                            <div class="card quote-card">
                                <div class="card-body">
                                    <div class="quote-grid">
                                        <div class="quote-customer">
                                            <h3><?php echo htmlspecialchars($quote['customer_name']); ?></h3>
                                            <p>
                                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16">
                                                    <path d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                                </svg>
                                                <?php echo htmlspecialchars($quote['email']); ?>
                                            </p>
                                            <p>
                                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16">
                                                    <path d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                                                </svg>
                                                <?php echo htmlspecialchars($quote['phone']); ?>
                                            </p>
                                            <?php if ($quote['location']): ?>
                                                <p>
                                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16">
                                                        <path d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                                        <path d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                    </svg>
                                                    <?php echo htmlspecialchars($quote['location']); ?>
                                                </p>
                                            <?php endif; ?>
                                            <p class="preferred-contact">
                                                Preferred: <strong><?php echo ucfirst($quote['preferred_contact']); ?></strong>
                                            </p>
                                        </div>

                                        <div class="quote-car">
                                            <?php if ($quote['car_id']): ?>
                                                <div class="quote-car-info">
                                                    <?php if ($quote['main_image']): ?>
                                                        <img src="../uploads/cars/<?php echo htmlspecialchars($quote['main_image']); ?>" alt="Car">
                                                    <?php endif; ?>
                                                    <div>
                                                        <strong><?php echo htmlspecialchars($quote['brand_name'] . ' ' . $quote['model']); ?></strong>
                                                        <span><?php echo $quote['year']; ?></span>
                                                        <span class="price"><?php echo formatPrice($quote['price']); ?></span>
                                                    </div>
                                                </div>
                                            <?php else: ?>
                                                <p class="text-muted">General Inquiry</p>
                                            <?php endif; ?>

                                            <?php if ($quote['message']): ?>
                                                <div class="quote-message">
                                                    <strong>Message:</strong>
                                                    <p><?php echo nl2br(htmlspecialchars($quote['message'])); ?></p>
                                                </div>
                                            <?php endif; ?>
                                        </div>

                                        <div class="quote-actions">
                                            <span class="badge badge-<?php echo $quote['status']; ?>"><?php echo ucfirst($quote['status']); ?></span>
                                            <small class="text-muted"><?php echo date('M d, Y H:i', strtotime($quote['created_at'])); ?></small>

                                            <form method="POST" class="quote-status-form">
                                                <input type="hidden" name="quote_id" value="<?php echo $quote['id']; ?>">
                                                <select name="status">
                                                    <option value="pending" <?php echo $quote['status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                                    <option value="contacted" <?php echo $quote['status'] === 'contacted' ? 'selected' : ''; ?>>Contacted</option>
                                                    <option value="converted" <?php echo $quote['status'] === 'converted' ? 'selected' : ''; ?>>Converted</option>
                                                    <option value="closed" <?php echo $quote['status'] === 'closed' ? 'selected' : ''; ?>>Closed</option>
                                                </select>
                                                <textarea name="admin_notes" placeholder="Add notes..."><?php echo htmlspecialchars($quote['admin_notes'] ?? ''); ?></textarea>
                                                <div class="quote-form-actions">
                                                    <button type="submit" name="update_status" class="btn btn-sm btn-primary">Update</button>
                                                    <a href="?delete=<?php echo $quote['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this quote?')">Delete</a>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <div class="card">
                            <div class="card-body">
                                <div class="empty-state">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="48" height="48">
                                        <path d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                                    </svg>
                                    <h3>No quote requests</h3>
                                    <p>Quote requests from customers will appear here.</p>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>

    <script src="../assets/js/admin.js"></script>
</body>
</html>
<?php $conn->close(); ?>
