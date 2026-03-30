<?php
include 'includes/header.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$category_name = sanitize_input($_GET['name']);

$stmt = $db->prepare("SELECT id FROM categories WHERE name = ?");
$stmt->execute([$category_name]);
$category = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$category) {
    die("Category not found.");
}

$listings_stmt = $db->prepare("
    SELECT * FROM listings 
    WHERE category_id = ? AND status = 'active'
");
$listings_stmt->execute([$category['id']]);
$listings = $listings_stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container">
    <h1><?php echo htmlspecialchars($category_name); ?></h1>

    <?php foreach ($listings as $listing): ?>
        <div class="listing-card">
            <h3><?php echo htmlspecialchars($listing['title']); ?></h3>
            <p><?php echo htmlspecialchars($listing['description']); ?></p>
            <strong><?php echo $listing['price'] . " " . $listing['currency']; ?></strong>
        </div>
        <hr>
    <?php endforeach; ?>
</div>

<?php include 'includes/footer.php'; ?>