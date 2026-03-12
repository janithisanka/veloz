<?php
require_once '../includes/config.php';

if (!isAdminLoggedIn()) {
    redirect('index.php');
}

$conn = getDBConnection();
$success = '';

// Create table if not exists
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

// Handle status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $id = (int)$_POST['preorder_id'];
    $status = sanitize($_POST['status']);
    $admin_notes = sanitize($_POST['admin_notes']);

    $stmt = $conn->prepare("UPDATE preorders SET status = ?, admin_notes = ? WHERE id = ?");
    $stmt->bind_param("ssi", $status, $admin_notes, $id);
    if ($stmt->execute()) {
        $success = "Pre-order updated successfully";
    }
    $stmt->close();
}

// Handle delete
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $conn->query("DELETE FROM preorders WHERE id = $id");
    redirect('preorders.php?deleted=1');
}

// Get filter
$statusFilter = isset($_GET['status']) ? sanitize($_GET['status']) : '';
$where = $statusFilter ? "WHERE status = '$statusFilter'" : "";

$preorders = $conn->query("SELECT * FROM preorders $where ORDER BY created_at DESC");

// Count by status
$counts = [];
$countResult = $conn->query("SELECT status, COUNT(*) as cnt FROM preorders GROUP BY status");
while ($row = $countResult->fetch_assoc()) {
    $counts[$row['status']] = $row['cnt'];
}
$totalCount = array_sum($counts);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pre-Orders - <?php echo SITE_NAME; ?> Admin</title>
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
                        <h1>Pre-Orders</h1>
                        <p>Manage customer vehicle pre-order requests</p>
                    </div>
                </div>

                <?php if ($success): ?><div class="alert alert-success"><?php echo $success; ?></div><?php endif; ?>
                <?php if (isset($_GET['deleted'])): ?><div class="alert alert-success">Pre-order deleted.</div><?php endif; ?>

                <!-- Status Summary Cards -->
                <div class="stats-grid" style="margin-bottom: 24px;">
                    <div class="stat-card">
                        <div class="stat-number"><?php echo $totalCount; ?></div>
                        <div class="stat-label">Total</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-number"><?php echo $counts['pending'] ?? 0; ?></div>
                        <div class="stat-label">Pending</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-number"><?php echo $counts['sourcing'] ?? 0; ?></div>
                        <div class="stat-label">Sourcing</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-number"><?php echo ($counts['found'] ?? 0) + ($counts['confirmed'] ?? 0); ?></div>
                        <div class="stat-label">Found/Confirmed</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-number"><?php echo ($counts['shipped'] ?? 0) + ($counts['delivered'] ?? 0); ?></div>
                        <div class="stat-label">Shipped/Delivered</div>
                    </div>
                </div>

                <!-- Filter Tabs -->
                <div class="filter-tabs" style="margin-bottom: 24px;">
                    <a href="preorders.php" class="<?php echo !$statusFilter ? 'active' : ''; ?>">All (<?php echo $totalCount; ?>)</a>
                    <a href="?status=pending" class="<?php echo $statusFilter === 'pending' ? 'active' : ''; ?>">Pending</a>
                    <a href="?status=sourcing" class="<?php echo $statusFilter === 'sourcing' ? 'active' : ''; ?>">Sourcing</a>
                    <a href="?status=found" class="<?php echo $statusFilter === 'found' ? 'active' : ''; ?>">Found</a>
                    <a href="?status=confirmed" class="<?php echo $statusFilter === 'confirmed' ? 'active' : ''; ?>">Confirmed</a>
                    <a href="?status=shipped" class="<?php echo $statusFilter === 'shipped' ? 'active' : ''; ?>">Shipped</a>
                    <a href="?status=delivered" class="<?php echo $statusFilter === 'delivered' ? 'active' : ''; ?>">Delivered</a>
                    <a href="?status=cancelled" class="<?php echo $statusFilter === 'cancelled' ? 'active' : ''; ?>">Cancelled</a>
                </div>

                <?php if ($preorders->num_rows > 0): ?>
                    <?php while ($order = $preorders->fetch_assoc()): ?>
                        <div class="card" style="margin-bottom: 16px;">
                            <div class="card-header" style="display:flex;justify-content:space-between;align-items:center;">
                                <div>
                                    <h3 style="margin:0;">
                                        <?php echo htmlspecialchars($order['brand'] . ' ' . $order['model']); ?>
                                        <?php if ($order['brand'] && $order['model']): ?>
                                        <?php elseif ($order['brand']): ?>
                                            <?php echo htmlspecialchars($order['brand']); ?> (Any Model)
                                        <?php else: ?>
                                            Any Vehicle
                                        <?php endif; ?>
                                    </h3>
                                    <p style="margin:4px 0 0;font-size:13px;color:#999;">
                                        #<?php echo $order['id']; ?> &bull;
                                        <?php echo htmlspecialchars($order['customer_name']); ?> &bull;
                                        <?php echo date('M d, Y', strtotime($order['created_at'])); ?>
                                    </p>
                                </div>
                                <span class="badge badge-<?php
                                    $statusColors = [
                                        'pending' => 'warning',
                                        'sourcing' => 'info',
                                        'found' => 'success',
                                        'confirmed' => 'success',
                                        'shipped' => 'info',
                                        'delivered' => 'success',
                                        'cancelled' => 'danger'
                                    ];
                                    echo $statusColors[$order['status']] ?? 'secondary';
                                ?>" style="font-size:13px;padding:6px 14px;">
                                    <?php echo ucfirst($order['status']); ?>
                                </span>
                            </div>
                            <div class="card-body">
                                <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:16px;margin-bottom:20px;">
                                    <div>
                                        <strong style="display:block;font-size:12px;color:#999;text-transform:uppercase;margin-bottom:4px;">Contact</strong>
                                        <p style="margin:0;font-size:14px;"><?php echo htmlspecialchars($order['customer_name']); ?></p>
                                        <p style="margin:2px 0;font-size:13px;color:#999;"><?php echo htmlspecialchars($order['email']); ?></p>
                                        <p style="margin:0;font-size:13px;color:#999;"><?php echo htmlspecialchars($order['phone']); ?></p>
                                    </div>
                                    <div>
                                        <strong style="display:block;font-size:12px;color:#999;text-transform:uppercase;margin-bottom:4px;">Preferences</strong>
                                        <?php if ($order['year_from'] || $order['year_to']): ?>
                                            <p style="margin:0;font-size:13px;">Year: <?php echo ($order['year_from'] ?: 'Any'); ?> - <?php echo ($order['year_to'] ?: 'Any'); ?></p>
                                        <?php endif; ?>
                                        <p style="margin:2px 0;font-size:13px;">Fuel: <?php echo ucfirst($order['fuel_preference']); ?></p>
                                        <p style="margin:0;font-size:13px;">Transmission: <?php echo ucfirst($order['transmission_preference']); ?></p>
                                        <?php if ($order['color_preference']): ?>
                                            <p style="margin:2px 0;font-size:13px;">Color: <?php echo htmlspecialchars($order['color_preference']); ?></p>
                                        <?php endif; ?>
                                    </div>
                                    <div>
                                        <strong style="display:block;font-size:12px;color:#999;text-transform:uppercase;margin-bottom:4px;">Budget</strong>
                                        <?php if ($order['budget_min'] || $order['budget_max']): ?>
                                            <p style="margin:0;font-size:14px;font-weight:600;color:#eab308;">
                                                <?php echo $order['budget_min'] ? formatPrice($order['budget_min']) : 'Any'; ?>
                                                -
                                                <?php echo $order['budget_max'] ? formatPrice($order['budget_max']) : 'Any'; ?>
                                            </p>
                                        <?php else: ?>
                                            <p style="margin:0;font-size:13px;color:#999;">Not specified</p>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <?php if ($order['additional_notes']): ?>
                                    <div style="background:#1a1a2e;padding:12px 16px;border-radius:8px;margin-bottom:16px;">
                                        <strong style="font-size:12px;color:#999;text-transform:uppercase;">Customer Notes</strong>
                                        <p style="margin:6px 0 0;font-size:14px;"><?php echo nl2br(htmlspecialchars($order['additional_notes'])); ?></p>
                                    </div>
                                <?php endif; ?>

                                <!-- Update Form -->
                                <form method="POST" style="display:flex;gap:12px;align-items:flex-end;flex-wrap:wrap;">
                                    <input type="hidden" name="preorder_id" value="<?php echo $order['id']; ?>">
                                    <div class="form-group" style="margin-bottom:0;flex:0 0 160px;">
                                        <label>Status</label>
                                        <select name="status">
                                            <?php foreach (['pending','sourcing','found','confirmed','shipped','delivered','cancelled'] as $s): ?>
                                                <option value="<?php echo $s; ?>" <?php echo $order['status'] === $s ? 'selected' : ''; ?>>
                                                    <?php echo ucfirst($s); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="form-group" style="margin-bottom:0;flex:1;">
                                        <label>Admin Notes</label>
                                        <input type="text" name="admin_notes" value="<?php echo htmlspecialchars($order['admin_notes'] ?? ''); ?>" placeholder="Internal notes...">
                                    </div>
                                    <button type="submit" name="update_status" class="btn btn-primary btn-sm">Update</button>
                                    <a href="?delete=<?php echo $order['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this pre-order?')">Delete</a>
                                </form>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="card">
                        <div class="card-body">
                            <p class="text-muted">No pre-orders found.</p>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </main>
    </div>
    <script src="../assets/js/admin.js"></script>
</body>
</html>
<?php $conn->close(); ?>
