<?php
// vendor/dashboard.php - Located in D:\XAMPP\htdocs\nexera_africa\vendor\

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/db.php'; 

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'vendor') {
    header("Location: ../login.php");
    exit("Access Denied: Vendors only.");
}

$user_id = $_SESSION['user_id'];
$message = '';
$error = '';

// 1. Fetch Admin ID (Assuming the first admin found, or ID 1)
$admin_query = $db->query("SELECT id FROM users WHERE role = 'admin' LIMIT 1");
$admin_data = $admin_query->fetch();
$admin_id = $admin_data ? $admin_data['id'] : 1;

// 2. NEW: Count unread messages from Admin
$unread_stmt = $db->prepare("SELECT COUNT(*) FROM messages WHERE sender_id = ? AND receiver_id = ? AND is_read = 0");
$unread_stmt->execute([$admin_id, $user_id]);
$unread_msg_count = $unread_stmt->fetchColumn();

// 3. Handle deletion of listing
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete'])) {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die('Invalid CSRF token');
    }
    $id = (int)$_POST['id'];
    $stmt = $db->prepare("DELETE FROM listings WHERE id = ? AND user_id = ?");
    if ($stmt->execute([$id, $user_id])) {
        $message = $lang['delete_success'] ?? "Listing deleted successfully.";
    } else {
        $error = $lang['delete_error'] ?? "Failed to delete listing.";
    }
}

// 4. Fetch all listings
$stmt = $db->prepare("SELECT l.*, i.image_path 
                      FROM listings l 
                      LEFT JOIN images i ON l.id = i.listing_id 
                      WHERE l.user_id = ? 
                      GROUP BY l.id 
                      ORDER BY l.created_at DESC");
$stmt->execute([$user_id]);
$listings = $stmt->fetchAll(PDO::FETCH_ASSOC);

include __DIR__ . '/../includes/header.php'; 
?>

<style>
    .vendor-dashboard { max-width: 1200px; margin: 30px auto; padding: 20px; font-family: sans-serif; background: #fff !important; color: #333 !important; }
    .dash-table { width: 100%; border-collapse: collapse; background: #fff !important; box-shadow: 0 2px 10px rgba(0,0,0,0.1); color: #333 !important; }
    .dash-table th, .dash-table td { padding: 12px; border-bottom: 1px solid #eee; text-align: left; }
    .dash-table th { background: #f8f9fa !important; color: #111 !important; }
    
    /* Notification Bar Styles */
    .msg-notification-bar {
        background: #fff3e0;
        border-left: 5px solid #ff9800;
        padding: 15px;
        margin-bottom: 20px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        border-radius: 4px;
    }
    .unread-badge {
        background: red;
        color: white;
        padding: 2px 8px;
        border-radius: 10px;
        font-size: 12px;
        margin-left: 5px;
    }

    .thumb-preview { width: 50px; height: 50px; object-fit: cover; border-radius: 4px; cursor: zoom-in; border: 1px solid #ddd; }
    .status-active { color: #28a745 !important; font-weight: bold; }
    .status-pending { color: #fd7e14 !important; font-weight: bold; }
</style>

<div class="vendor-dashboard">

    <div class="msg-notification-bar">
        <div>
            <strong style="color: #e65100;">💬 <?php echo $lang['admin_support'] ?? 'Admin Support'; ?></strong>
            <p style="margin: 5px 0 0 0; font-size: 0.9em;">
                <?php if($unread_msg_count > 0): ?>
                    You have <span class="unread-badge"><?php echo $unread_msg_count; ?></span> new messages from Admin.
                <?php else: ?>
                    No new messages. Click to contact Admin.
                <?php endif; ?>
            </p>
        </div>
        <a href="../chat.php?admin_id=<?php echo $admin_id; ?>" style="background: #ff9800; color: white !important; padding: 8px 15px; border-radius: 5px; text-decoration: none; font-weight: bold; font-size: 0.9em;">
            <?php echo $lang['message'] ?? 'Open Chat'; ?>
        </a>
    </div>

    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; border-bottom: 1px solid #eee; padding-bottom: 10px;">
        <h1 style="color: #222 !important;"><?php echo $lang['vendor_dashboard'] ?? 'Vendor Dashboard'; ?></h1>
        <a href="../create_listing.php" style="background: #28a745; color: #fff !important; padding: 10px 15px; border-radius: 5px; text-decoration: none; font-weight: bold;">
            + <?php echo $lang['create_new_listing'] ?? 'Create New Listing'; ?>
        </a>
    </div>

    <?php if ($message) echo "<p style='color:green; font-weight:bold;'>$message</p>"; ?>
    <?php if ($error) echo "<p style='color:red; font-weight:bold;'>$error</p>"; ?>

    <table class="dash-table">
        <thead>
            <tr>
                <th><?php echo $lang['preview'] ?? 'Preview'; ?></th>
                <th><?php echo $lang['title'] ?? 'Title'; ?></th>
                <th><?php echo $lang['status'] ?? 'Status'; ?></th>
                <th><?php echo $lang['created_at'] ?? 'Created At'; ?></th>
                <th><?php echo $lang['actions'] ?? 'Actions'; ?></th>
            </tr>
        </thead>
        <tbody>
            <?php if ($listings): ?>
                <?php foreach ($listings as $listing): ?>
                    <tr>
                        <td>
                            <?php $img = !empty($listing['image_path']) ? '../' . $listing['image_path'] : '../assets/no-image.png'; ?>
                            <img src="<?php echo $img; ?>" class="thumb-preview" onclick="openZoom(this.src)">
                        </td>
                        <td style="font-weight: 500; color: #111 !important;">
                            <?php echo htmlspecialchars($listing['title']); ?>
                        </td>
                        <td>
                            <span class="status-<?php echo strtolower($listing['status']); ?>">
                                <?php echo ucfirst($listing['status']); ?>
                            </span>
                        </td>
                        <td style="color: #666 !important;">
                            <?php echo date('M d, Y', strtotime($listing['created_at'])); ?>
                        </td>
                        <td>
                            <form method="POST" style="display:inline;">
                                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token'] ?? ''; ?>">
                                <input type="hidden" name="id" value="<?php echo $listing['id']; ?>">
                                <button type="submit" name="delete" onclick="return confirm('Delete?')" style="color:#dc3545; background:none; border:none; cursor:pointer; font-weight: bold;">
                                    <?php echo $lang['delete'] ?? 'Delete'; ?>
                                </button>
                            </form>
                            <span style="color: #ccc;"> | </span>
                            <a href="../view_listing.php?id=<?php echo $listing['id']; ?>" class="view-link" style="color: #007bff;"><?php echo $lang['view'] ?? 'View'; ?></a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="5" style="text-align:center; padding: 40px; color: #888 !important;">
                        No listings found.
                    </td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>