<?php
/**
 * Enquiries Management Page
 * Sri Lakshmi Admin Panel
 */
error_reporting(E_ALL);
ini_set('display_errors', 1);
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');

require_once 'auth.php';
requireLogin();

require_once __DIR__ . '/../config/database.php';

$conn = getConnection();

// Auto-create enquiries table if missing
try {
    $conn->exec("CREATE TABLE IF NOT EXISTS enquiries (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        email VARCHAR(255) NOT NULL,
        phone VARCHAR(50),
        subject VARCHAR(255),
        message TEXT,
        service VARCHAR(255),
        status ENUM('new','read','replied') DEFAULT 'new',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
} catch (Exception $e) {}

// Handle status update
if (isset($_GET['mark_read']) && is_numeric($_GET['mark_read'])) {
    $stmt = $conn->prepare("UPDATE enquiries SET status = 'read' WHERE id = ?");
    $stmt->execute([$_GET['mark_read']]);
    header('Location: enquiries.php?marked=1');
    exit;
}

if (isset($_GET['mark_replied']) && is_numeric($_GET['mark_replied'])) {
    $stmt = $conn->prepare("UPDATE enquiries SET status = 'replied' WHERE id = ?");
    $stmt->execute([$_GET['mark_replied']]);
    header('Location: enquiries.php?replied=1');
    exit;
}

if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $stmt = $conn->prepare("DELETE FROM enquiries WHERE id = ?");
    $stmt->execute([$_GET['delete']]);
    header('Location: enquiries.php?deleted=1');
    exit;
}

// Fetch all enquiries
$enquiries = [];
try {
    $enquiries = $conn->query("SELECT * FROM enquiries ORDER BY created_at DESC")->fetchAll();
} catch (Exception $e) {
    $enquiries = [];
}

$newCount = 0;
foreach ($enquiries as $e) {
    if ($e['status'] === 'new') $newCount++;
}

$success = isset($_GET['marked'])  ? 'Marked as read'   :
           (isset($_GET['replied']) ? 'Marked as replied' :
           (isset($_GET['deleted']) ? 'Enquiry deleted'   : ''));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Enquiries | Sri Lakshmi Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/admin.css">
    <style>
        .enquiry-card {
            background: var(--white);
            border-radius: var(--radius-lg);
            padding: 1.5rem;
            margin-bottom: 1rem;
            box-shadow: var(--shadow);
            border-left: 4px solid var(--gray-300);
        }
        .enquiry-card.new {
            border-left-color: var(--secondary);
            background: #fffaf0;
        }
        .enquiry-card.replied {
            border-left-color: #28a745;
            opacity: 0.85;
        }
        .enquiry-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 1rem;
            flex-wrap: wrap;
            gap: 0.5rem;
        }
        .enquiry-title {
            font-weight: 600;
            color: var(--primary);
            font-size: 1.1rem;
        }
        .enquiry-meta {
            color: var(--gray-600);
            font-size: 0.85rem;
            margin-top: 0.25rem;
        }
        .enquiry-meta a {
            color: var(--primary);
            text-decoration: none;
        }
        .enquiry-meta a:hover {
            text-decoration: underline;
        }
        .enquiry-status {
            padding: 0.25rem 0.75rem;
            border-radius: 50px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
        }
        .status-new { background: #fff3cd; color: #856404; }
        .status-read { background: #d1ecf1; color: #0c5460; }
        .status-replied { background: #d4edda; color: #155724; }
        .enquiry-message {
            background: var(--gray-50);
            padding: 1rem;
            border-radius: var(--radius);
            margin: 1rem 0;
            color: var(--gray-700);
            white-space: pre-wrap;
        }
        .enquiry-service-tag {
            display: inline-block;
            background: var(--primary);
            color: var(--secondary);
            padding: 0.25rem 0.75rem;
            border-radius: 50px;
            font-size: 0.75rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }
        .enquiry-actions {
            display: flex;
            gap: 0.5rem;
            flex-wrap: wrap;
            margin-top: 1rem;
        }
        .enquiry-actions .btn {
            padding: 0.4rem 0.8rem;
            font-size: 0.8rem;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header class="admin-header">
        <div class="header-left">
            <h1 class="admin-title">Enquiries <?php if ($newCount > 0): ?><span style="background:var(--secondary);color:var(--primary);padding:0.2rem 0.6rem;border-radius:50px;font-size:0.7rem;margin-left:0.5rem;"><?php echo $newCount; ?> NEW</span><?php endif; ?></h1>
        </div>
        <div class="header-right">
            <a href="../index.html" target="_blank" class="btn btn-outline">
                <i class="fas fa-home"></i> View Site
            </a>
            <a href="logout.php" class="btn btn-danger">
                <i class="fas fa-sign-out-alt"></i> Logout
            </a>
        </div>
    </header>

    <!-- Navigation -->
    <nav class="admin-nav">
        <a href="index.php" class="nav-item">
            <i class="fas fa-tachometer-alt"></i> Dashboard
        </a>
        <a href="categories.php" class="nav-item">
            <i class="fas fa-list"></i> Categories
        </a>
        <a href="products.php" class="nav-item">
            <i class="fas fa-box"></i> Products
        </a>
        <a href="banners.php" class="nav-item">
            <i class="fas fa-images"></i> Banners
        </a>
        <a href="ourwork.php" class="nav-item">
            <i class="fas fa-briefcase"></i> Our Work
        </a>
        <a href="enquiries.php" class="nav-item active">
            <i class="fas fa-envelope"></i> Enquiries
        </a>
    </nav>

    <!-- Main Content -->
    <main class="admin-main">
        <?php if ($success): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i> <?php echo $success; ?>
            </div>
        <?php endif; ?>

        <div class="card">
            <div class="card-header">
                <h2><i class="fas fa-envelope-open-text"></i> Customer Enquiries</h2>
                <span class="badge"><?php echo count($enquiries); ?> Total</span>
            </div>
        </div>

        <?php if (empty($enquiries)): ?>
            <div class="empty-state" style="text-align:center;padding:3rem;background:var(--white);border-radius:var(--radius-lg);box-shadow:var(--shadow);">
                <i class="fas fa-inbox" style="font-size:3rem;color:var(--gray-400);margin-bottom:1rem;"></i>
                <h3 style="color:var(--gray-600);">No Enquiries Yet</h3>
                <p style="color:var(--gray-500);">Customer enquiries from the contact form will appear here.</p>
            </div>
        <?php else: ?>
            <?php foreach ($enquiries as $enq): ?>
                <div class="enquiry-card <?php echo htmlspecialchars($enq['status']); ?>">
                    <div class="enquiry-header">
                        <div>
                            <?php if (!empty($enq['service'])): ?>
                                <div class="enquiry-service-tag"><i class="fas fa-tag"></i> <?php echo htmlspecialchars($enq['service']); ?></div>
                            <?php endif; ?>
                            <div class="enquiry-title"><?php echo htmlspecialchars($enq['name']); ?></div>
                            <div class="enquiry-meta">
                                <a href="mailto:<?php echo htmlspecialchars($enq['email']); ?>"><i class="fas fa-envelope"></i> <?php echo htmlspecialchars($enq['email']); ?></a>
                                <?php if (!empty($enq['phone'])): ?>
                                    &nbsp;|&nbsp;
                                    <a href="tel:<?php echo htmlspecialchars($enq['phone']); ?>"><i class="fas fa-phone"></i> <?php echo htmlspecialchars($enq['phone']); ?></a>
                                <?php endif; ?>
                                <br>
                                <i class="fas fa-clock"></i> <?php echo date('M d, Y h:i A', strtotime($enq['created_at'])); ?>
                            </div>
                        </div>
                        <span class="enquiry-status status-<?php echo htmlspecialchars($enq['status']); ?>"><?php echo htmlspecialchars($enq['status']); ?></span>
                    </div>

                    <?php if (!empty($enq['subject'])): ?>
                        <div style="margin-bottom:0.5rem;"><strong>Subject:</strong> <?php echo htmlspecialchars($enq['subject']); ?></div>
                    <?php endif; ?>

                    <div class="enquiry-message"><?php echo htmlspecialchars($enq['message']); ?></div>

                    <div class="enquiry-actions">
                        <?php if ($enq['status'] === 'new'): ?>
                            <a href="?mark_read=<?php echo $enq['id']; ?>" class="btn btn-outline"><i class="fas fa-eye"></i> Mark as Read</a>
                        <?php endif; ?>
                        <?php if ($enq['status'] !== 'replied'): ?>
                            <a href="?mark_replied=<?php echo $enq['id']; ?>" class="btn btn-primary"><i class="fas fa-check"></i> Mark as Replied</a>
                        <?php endif; ?>
                        <a href="mailto:<?php echo htmlspecialchars($enq['email']); ?>?subject=Re: <?php echo htmlspecialchars($enq['subject'] ?: 'Your Enquiry'); ?>" class="btn btn-outline"><i class="fas fa-reply"></i> Reply via Email</a>
                        <a href="?delete=<?php echo $enq['id']; ?>" class="btn btn-danger" onclick="return confirm('Delete this enquiry?');"><i class="fas fa-trash"></i> Delete</a>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </main>

    <script src="js/admin.js"></script>
</body>
</html>
