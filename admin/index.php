<?php
include '../includes/header.php';
require_admin();
?>
<h1><?php echo $lang['admin_dashboard']; ?></h1>
<ul>
    
    <li><a href="view_transactions.php"><?php echo $lang['view_transactions']; ?></a></li>
</ul>
<?php include '../includes/footer.php'; ?>
