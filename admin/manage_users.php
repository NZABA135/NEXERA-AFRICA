<?php
include '../includes/header.php';
require_admin();

if (isset($_POST['action'])) {
    if (!validate_csrf_token($_POST['csrf_token'])) {
        die('Invalid CSRF token');
    }
    $id = (int)$_POST['id'];
    $action = $_POST['action'];

    if ($action === 'block') {
        $stmt = $db->prepare("UPDATE users SET blocked = 1 WHERE id = ?");
    } elseif ($action === 'delete') {
        $stmt = $db->prepare("DELETE FROM users WHERE id = ?");
    }
    $stmt->execute([$id]);
}

$stmt = $db->query("SELECT * FROM users");
$users = $stmt->fetchAll();
?>
<h1><?php echo $lang['manage_users']; ?></h1>
<table>
    <tr>
        <th><?php echo $lang['username']; ?></th>
        <th><?php echo $lang['email']; ?></th>
        <th><?php echo $lang['role']; ?></th>
        <th><?php echo $lang['status']; ?></th>
        <th>Actions</th>
    </tr>
    <?php foreach ($users as $user): ?>
        <tr>
            <td><?php echo $user['username']; ?></td>
            <td><?php echo $user['email']; ?></td>
            <td><?php echo $user['role']; ?></td>
            <td><?php echo $user['blocked'] ? 'Blocked' : 'Active'; ?></td>
            <td>
                <form method="POST">
                    <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                    <input type="hidden" name="id" value="<?php echo $user['id']; ?>">
                    <button name="action" value="block"><?php echo $lang['block_user']; ?></button>
                    <button name="action" value="delete"><?php echo $lang['delete']; ?></button>
                </form>
            </td>
        </tr>
    <?php endforeach; ?>
</table>
<?php include '../includes/footer.php'; ?>