<?php
include 'includes/header.php';

$id = (int)$_GET['id'];
$stmt = $db->prepare("SELECT l.*, u.username AS seller FROM listings l JOIN users u ON l.user_id = u.id WHERE l.id = ? AND status = 'active'");
$stmt->execute([$id]);
$listing = $stmt->fetch();

if (!$listing) {
    die('Listing not found.');
}

$img_stmt = $db->prepare("SELECT image_path FROM images WHERE listing_id = ?");
$img_stmt->execute([$id]);
$images = $img_stmt->fetchAll();
?>
<h1><?php echo $listing['title']; ?></h1>
<p><?php echo $listing['description']; ?></p>
<p><?php echo $lang['price']; ?>: <?php echo $listing['price']; ?> <?php echo $listing['currency']; ?></p>
<p><?php echo $lang['seller']; ?>: <?php echo $listing['seller']; ?></p>
<p><?php echo $lang['location']; ?>: <?php echo $listing['location']; ?></p>
<p><?php echo $lang['created_at']; ?>: <?php echo $listing['created_at']; ?></p>
<?php foreach ($images as $img): ?>
    <img src="<?php echo $img['image_path']; ?>" alt="Listing image" width="200">
<?php endforeach; ?>
<a href="payment.php?id=<?php echo $id; ?>">Pay Now</a>
<?php include 'includes/footer.php'; ?>