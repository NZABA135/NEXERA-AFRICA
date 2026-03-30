<?php
include 'includes/header.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

// Updated Query: Now also checks for unread messages from the vendor to the admin
$stmt = $db->prepare("
    SELECT l.*, u.username, u.id as vendor_id, u.is_banned,
            (SELECT image_path FROM images WHERE listing_id = l.id LIMIT 1) as image,
            (SELECT COUNT(*) FROM messages WHERE sender_id = u.id AND receiver_id = ? AND is_read = 0) as unread_count
    FROM listings l
    JOIN users u ON l.user_id = u.id
    ORDER BY l.created_at DESC
");
$stmt->execute([$_SESSION['user_id']]);
$listings = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<style>
    .admin-container {
        color: #333 !important; /* Forces dark text */
        background: #fff;
        padding: 20px;
    }
    .admin-table {
        border-collapse: collapse;
        width: 100%;
        background-color: #ffffff;
        color: #333 !important;
    }
    .admin-table th {
        background-color: #f4f4f4;
        color: #333;
        font-weight: bold;
        border: 1px solid #ddd;
    }
    .admin-table td {
        border: 1px solid #ddd;
        color: #333 !important;
    }
    .admin-table a {
        text-decoration: none;
    }
    .btn-view {
        color: #2e7d32; /* Green for viewing */
        font-weight: bold;
    }
</style>

<div class="admin-container">
    <h1 style="color: #333;"><?php echo $lang['admin_dashboard']; ?></h1>

    <table class="admin-table" cellpadding="10">
        <thead>
            <tr>
                <th><?php echo $lang['images']; ?></th>
                <th><?php echo $lang['title']; ?></th>
                <th><?php echo $lang['vendor']; ?></th>
                <th><?php echo $lang['price']; ?></th>
                <th><?php echo $lang['status']; ?></th>
                <th>Vendor Status</th>
                <th><?php echo $lang['actions']; ?></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($listings as $listing): ?>
            <tr>
                <td align="center">
                    <img src="<?php echo $listing['image'] ?? 'uploads/placeholder.jpg'; ?>" 
                         width="80" style="border-radius:5px; object-fit: cover; border: 1px solid #eee;">
                </td>

                <td style="font-weight: 500;"><?php echo htmlspecialchars($listing['title']); ?></td>

                <td>
                    <strong><?php echo htmlspecialchars($listing['username']); ?></strong>
                </td>

                <td><?php echo $listing['price'].' '.$listing['currency']; ?></td>

                <td>
                    <?php echo $listing['verified'] ? "<span style='color: green;'>✅ " . $lang['approved'] . "</span>" : "<span style='color: orange;'>⏳ " . $lang['pending'] . "</span>"; ?>
                </td>

                <td>
                    <?php if($listing['is_banned']): ?>
                        <span style="color:red; font-weight: bold;">❌ Banned</span>
                    <?php else: ?>
                        <span style="color:green; font-weight: bold;">✅ <?php echo $lang['active']; ?></span>
                    <?php endif; ?>
                </td>

                <td>
                    <a href="view_listing.php?id=<?php echo $listing['id']; ?>" class="btn-view">
                        👁️ <?php echo $lang['view'] ?? 'View'; ?>
                    </a> |

                    <?php if(!$listing['verified']): ?>
                        <a href="verify_listing.php?id=<?php echo $listing['id']; ?>" style="color: #2196F3;">
                            <?php echo $lang['approve']; ?>
                        </a> |
                    <?php endif; ?>

                    <a href="chat.php?vendor_id=<?php echo $listing['vendor_id']; ?>" 
                       style="color: #ff9800; font-weight: bold;">
                        💬 <?php echo $lang['message'] ?? 'Message'; ?>
                        <?php if($listing['unread_count'] > 0): ?>
                            <span style="background: red; color: white; padding: 2px 6px; border-radius: 50%; font-size: 10px; margin-left: 5px;">
                                <?php echo $listing['unread_count']; ?>
                            </span>
                        <?php endif; ?>
                    </a> |

                    <a href="delete_listing_admin.php?id=<?php echo $listing['id']; ?>"
                       style="color: #d32f2f;"
                       onclick="return confirm('<?php echo $lang['confirm_delete']; ?>')">
                       <?php echo $lang['delete']; ?>
                    </a>

                    <div style="margin-top: 10px; border-top: 1px solid #eee; padding-top: 5px;">
                        <?php if(!$listing['is_banned']): ?>
                            <a href="ban_vendor.php?id=<?php echo $listing['vendor_id']; ?>"
                               style="color:red; font-size: 0.85em;"
                               onclick="return confirm('Ban this vendor? All their listings will disappear.')">
                               <?php echo $lang['block_user']; ?>
                            </a>
                        <?php else: ?>
                            <a href="unban_vendor.php?id=<?php echo $listing['vendor_id']; ?>"
                               style="color:green; font-size: 0.85em;">
                               Unban Vendor
                            </a>
                        <?php endif; ?>
                    </div>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php include 'includes/footer.php'; ?>