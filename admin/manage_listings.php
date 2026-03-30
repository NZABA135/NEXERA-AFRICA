<?php
include '../includes/header.php';
require_admin();

if (isset($_POST['action'])) {
    if (!validate_csrf_token($_POST['csrf_token'])) {
        die('Invalid CSRF token');
    }
    $id = (int)$_POST['id'];
    $action = $_POST['action'];
    $status = $action === 'approve' ? 'active' : 'rejected';

    $stmt = $db->prepare("UPDATE listings SET status = ? WHERE id = ?");
    $stmt->execute([$status, $id]);
}

$stmt = $db->query("SELECT l.*, u.username AS seller FROM listings l JOIN users u ON l.user_id = u.id WHERE status = 'pending'");
$listings = $stmt->fetchAll();
?>
<h1><?php echo $lang['manage_listings']; ?></h1>
<table>
    <tr>
        <th><?php echo $lang['title']; ?></th>
        <th><?php echo $lang['seller']; ?></th>
        <th><?php echo $lang['status']; ?></th>
        <th>Actions</th>
    </tr>
    <?php foreach ($listings as $listing): ?>
        <tr>
            <td><?php echo $listing['title']; ?></td>
            <td><?php echo $listing['seller']; ?></td>
            <td><?php echo $listing['status']; ?></td>
            <td>
                <form method="POST">
                    <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                    <input type="hidden" name="id" value="<?php echo $listing['id']; ?>">
                    <button name="action" value="approve"><?php echo $lang['approve']; ?></button>
                    <button name="action" value="reject"><?php echo $lang['reject']; ?></button>
                </form>
            </td>
        </tr>
    <?php endforeach; ?>
</table>
<?php include '../includes/footer.php'; ?>