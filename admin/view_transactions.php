<?php
include '../includes/header.php';
require_admin();

$stmt = $db->query("SELECT p.*, u.username AS buyer, l.title FROM payments p JOIN users u ON p.user_id = u.id JOIN listings l ON p.listing_id = l.id");
$transactions = $stmt->fetchAll();
?>
<h1><?php echo $lang['view_transactions']; ?></h1>
<table>
    <tr>
        <th>Buyer</th>
        <th>Listing</th>
        <th>Amount</th>
        <th>Method</th>
        <th>Reference</th>
        <th><?php echo $lang['payment_status']; ?></th>
        <th><?php echo $lang['created_at']; ?></th>
    </tr>
    <?php foreach ($transactions as $tx): ?>
        <tr>
            <td><?php echo $tx['buyer']; ?></td>
            <td><?php echo $tx['title']; ?></td>
            <td><?php echo $tx['amount']; ?></td>
            <td><?php echo $tx['payment_method']; ?></td>
            <td><?php echo $tx['transaction_reference']; ?></td>
            <td><?php echo $tx['payment_status']; ?></td>
            <td><?php echo $tx['created_at']; ?></td>
        </tr>
    <?php endforeach; ?>
</table>
<?php include '../includes/footer.php'; ?>