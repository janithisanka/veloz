<?php
require_once '../includes/config.php';

if (!isAdminLoggedIn()) {
    redirect('index.php');
}

$conn = getDBConnection();
$errors = [];
$success = '';

// Create table if not exists
$conn->query("CREATE TABLE IF NOT EXISTS posts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(200) NOT NULL,
    content TEXT,
    image_path VARCHAR(255),
    post_type ENUM('news', 'social', 'promo') DEFAULT 'news',
    external_link VARCHAR(500),
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");

// Handle add
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_post'])) {
    $title = sanitize($_POST['title']);
    $content = sanitize($_POST['content']);
    $post_type = sanitize($_POST['post_type']);
    $external_link = sanitize($_POST['external_link']);

    if (empty($title)) {
        $errors[] = "Title is required";
    } else {
        $filename = '';
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $allowed = ['jpg', 'jpeg', 'png', 'webp'];
            $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));

            if (!in_array($ext, $allowed)) {
                $errors[] = "Invalid image format";
            } else {
                if (!is_dir(POSTS_PATH)) {
                    mkdir(POSTS_PATH, 0755, true);
                }
                $filename = 'post_' . time() . '_' . uniqid() . '.' . $ext;
                if (!move_uploaded_file($_FILES['image']['tmp_name'], POSTS_PATH . $filename)) {
                    $errors[] = "Failed to upload image";
                    $filename = '';
                }
            }
        }

        if (empty($errors)) {
            $stmt = $conn->prepare("INSERT INTO posts (title, content, image_path, post_type, external_link) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("sssss", $title, $content, $filename, $post_type, $external_link);
            if ($stmt->execute()) {
                $success = "Post added successfully";
            }
            $stmt->close();
        }
    }
}

// Handle delete
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $img = $conn->query("SELECT image_path FROM posts WHERE id = $id")->fetch_assoc();
    if ($img && !empty($img['image_path']) && file_exists(POSTS_PATH . $img['image_path'])) {
        unlink(POSTS_PATH . $img['image_path']);
    }
    $conn->query("DELETE FROM posts WHERE id = $id");
    redirect('posts.php?deleted=1');
}

// Handle toggle active
if (isset($_GET['toggle']) && is_numeric($_GET['toggle'])) {
    $id = (int)$_GET['toggle'];
    $conn->query("UPDATE posts SET is_active = NOT is_active WHERE id = $id");
    redirect('posts.php');
}

// Get filter
$typeFilter = isset($_GET['type']) ? sanitize($_GET['type']) : '';
$where = $typeFilter ? "WHERE post_type = '$typeFilter'" : "";

$posts = $conn->query("SELECT * FROM posts $where ORDER BY created_at DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Posts - <?php echo SITE_NAME; ?> Admin</title>
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
                        <h1>Posts & Updates</h1>
                        <p>Manage news, social media posts, and promotions</p>
                    </div>
                </div>

                <?php if ($success): ?><div class="alert alert-success"><?php echo $success; ?></div><?php endif; ?>
                <?php if (isset($_GET['deleted'])): ?><div class="alert alert-success">Post deleted.</div><?php endif; ?>
                <?php if (!empty($errors)): ?><div class="alert alert-error"><?php foreach ($errors as $e) echo "<p>$e</p>"; ?></div><?php endif; ?>

                <div class="content-grid">
                    <!-- Add Form -->
                    <div class="card">
                        <div class="card-header"><h2>Add Post</h2></div>
                        <div class="card-body">
                            <form method="POST" enctype="multipart/form-data">
                                <div class="form-group">
                                    <label for="title">Title *</label>
                                    <input type="text" name="title" id="title" placeholder="e.g., New Toyota Aqua Batch Arrived!" required>
                                </div>
                                <div class="form-group">
                                    <label for="post_type">Type</label>
                                    <select name="post_type" id="post_type">
                                        <option value="news">News</option>
                                        <option value="social">Social Media</option>
                                        <option value="promo">Promotion</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="content">Content</label>
                                    <textarea name="content" id="content" rows="3" placeholder="Post description or content"></textarea>
                                </div>
                                <div class="form-group">
                                    <label for="external_link">External Link (optional)</label>
                                    <input type="url" name="external_link" id="external_link" placeholder="https://instagram.com/p/...">
                                </div>
                                <div class="form-group">
                                    <label for="image">Image</label>
                                    <input type="file" name="image" id="image" accept="image/*">
                                </div>
                                <button type="submit" name="add_post" class="btn btn-primary">Add Post</button>
                            </form>
                        </div>
                    </div>

                    <!-- Posts List -->
                    <div class="card">
                        <div class="card-header">
                            <h2>All Posts</h2>
                            <div class="filter-tabs">
                                <a href="posts.php" class="<?php echo !$typeFilter ? 'active' : ''; ?>">All</a>
                                <a href="?type=news" class="<?php echo $typeFilter === 'news' ? 'active' : ''; ?>">News</a>
                                <a href="?type=social" class="<?php echo $typeFilter === 'social' ? 'active' : ''; ?>">Social</a>
                                <a href="?type=promo" class="<?php echo $typeFilter === 'promo' ? 'active' : ''; ?>">Promos</a>
                            </div>
                        </div>
                        <div class="card-body">
                            <?php if ($posts->num_rows > 0): ?>
                                <div class="table-responsive">
                                    <table class="table">
                                        <thead>
                                            <tr>
                                                <th>Image</th>
                                                <th>Title</th>
                                                <th>Type</th>
                                                <th>Status</th>
                                                <th>Date</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php while ($post = $posts->fetch_assoc()): ?>
                                                <tr>
                                                    <td>
                                                        <?php if (!empty($post['image_path'])): ?>
                                                            <img src="../uploads/posts/<?php echo htmlspecialchars($post['image_path']); ?>" alt="" style="width:60px;height:45px;object-fit:cover;border-radius:4px;">
                                                        <?php else: ?>
                                                            <span style="color:#999;font-size:12px;">No image</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <strong><?php echo htmlspecialchars($post['title']); ?></strong>
                                                        <?php if (!empty($post['external_link'])): ?>
                                                            <br><a href="<?php echo htmlspecialchars($post['external_link']); ?>" target="_blank" style="font-size:12px;color:#d4af37;">View Link</a>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <span class="badge badge-<?php echo $post['post_type'] === 'news' ? 'info' : ($post['post_type'] === 'promo' ? 'warning' : 'success'); ?>">
                                                            <?php echo ucfirst($post['post_type']); ?>
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <span class="badge badge-<?php echo $post['is_active'] ? 'success' : 'secondary'; ?>">
                                                            <?php echo $post['is_active'] ? 'Active' : 'Hidden'; ?>
                                                        </span>
                                                    </td>
                                                    <td><?php echo date('M d, Y', strtotime($post['created_at'])); ?></td>
                                                    <td>
                                                        <a href="?toggle=<?php echo $post['id']; ?>" class="btn btn-sm btn-outline">
                                                            <?php echo $post['is_active'] ? 'Hide' : 'Show'; ?>
                                                        </a>
                                                        <a href="?delete=<?php echo $post['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this post?')">Delete</a>
                                                    </td>
                                                </tr>
                                            <?php endwhile; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php else: ?>
                                <p class="text-muted">No posts yet. Add some above!</p>
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
