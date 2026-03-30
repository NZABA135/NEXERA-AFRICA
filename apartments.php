<?php
include 'includes/header.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$stmt = $db->prepare("
    SELECT l.*, i.image_path 
    FROM listings l
    LEFT JOIN images i ON l.id = i.listing_id
    JOIN categories c ON l.category_id = c.id
    WHERE c.name = ? AND l.status='active'
");
$stmt->execute(['Apartments']);
$listings = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<h1>Apartments</h1>

<div class="category-content">
<?php if ($listings): ?>
    <?php foreach ($listings as $listing): ?>
        <div class="item-card">
            <img src="<?php echo $listing['image_path'] ?? 'uploads/placeholder.jpg'; ?>" width="100%" alt="Listing Image">
            <h3><?php echo htmlspecialchars($listing['title']); ?></h3>
            <p><?php echo htmlspecialchars($listing['description']); ?></p>
            <p><?php echo $listing['price'] . ' ' . $listing['currency']; ?></p>
        </div>
    <?php endforeach; ?>
<?php else: ?>
    <p>No Apartments listings found.</p>
<?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>