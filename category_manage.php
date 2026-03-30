<?php
// category_manage.php
ob_start(); // Start buffering to prevent header errors
session_start();

// 1. DATABASE & CONFIG (Path updated to config/db.php)
$db_path = __DIR__ . '/config/db.php';
if (file_exists($db_path)) {
    require_once $db_path;
} else {
    die("Fatal Error: Database configuration file not found at: " . $db_path);
}

// 2. AUTHENTICATION & REDIRECT LOGIC (Must happen before any HTML output)
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Get category from URL
$category_name = $_GET['name'] ?? '';
if (!$category_name) {
    // We haven't included header.php yet, so we can't use $lang easily here unless db.php loads it.
    // If db.php doesn't load lang, use a hardcoded fallback.
    die('Category not specified.');
}

// Check if user is admin/vendor for CRUD (Ensuring get_user_role or session role is used)
// If you don't have get_user_role function defined elsewhere, use $_SESSION['role']
$user_role = $_SESSION['role'] ?? ''; 
$is_admin_or_vendor = in_array($user_role, ['admin', 'vendor']);

// --- Handle Delete ---
if ($is_admin_or_vendor && isset($_GET['delete'])) {
    $listing_id = (int)$_GET['delete'];
    
    // 1. Delete physical images
    $img_stmt = $db->prepare("SELECT image_path FROM images WHERE listing_id = ?");
    $img_stmt->execute([$listing_id]);
    $images = $img_stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($images as $img) {
        if (!empty($img['image_path']) && file_exists($img['image_path'])) {
            unlink($img['image_path']);
        }
    }
    
    // 2. Delete from database tables
    $db->prepare("DELETE FROM images WHERE listing_id = ?")->execute([$listing_id]);
    $db->prepare("DELETE FROM listings WHERE id = ?")->execute([$listing_id]);
    
    // Redirect now works because header.php hasn't been included yet
    header("Location: category_manage.php?name=" . urlencode($category_name));
    exit;
}

// 3. DATA FETCHING
// Fetch listings for this category
$stmt = $db->prepare("
    SELECT l.*, c.name as category_name, i.image_path
    FROM listings l
    LEFT JOIN images i ON l.id = i.listing_id
    JOIN categories c ON l.category_id = c.id
    WHERE c.name = ? AND l.status='active'
    GROUP BY l.id
    ORDER BY l.id DESC
");
$stmt->execute([$category_name]);
$listings = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch all categories for dropdown
$cat_stmt = $db->query("SELECT * FROM categories");
$categories = $cat_stmt->fetchAll(PDO::FETCH_ASSOC);

// 4. INCLUDE THE UI (Output starts here)
include 'includes/header.php';
?>

<script src="https://sdk.minepi.com/pi-sdk.js"></script>
<script>
    Pi.init({ version: "2.0", sandbox: true }); 

    async function payWithPi(listingId, amount, title) {
        try {
            const payment = await Pi.createPayment({
                amount: amount,
                memo: "Order for " + title,
                metadata: { listingId: listingId },
            }, {
                onReadyForServerApproval: (paymentId) => {
                    return fetch('approve_payment.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ paymentId: paymentId, action: 'approve' })
                    });
                },
                onReadyForServerCompletion: (paymentId, txid) => {
                    return fetch('approve_payment.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ paymentId: paymentId, txid: txid, action: 'complete' })
                    }).then(() => {
                        window.location.href = "order_success.php";
                    });
                },
                onCancel: (paymentId) => { console.log("Payment cancelled"); },
                onError: (error, payment) => { console.error("Pi Error:", error); },
            });
        } catch (err) {
            alert("<?php echo $lang['pi_browser_required'] ?? 'Please open this in the Pi Browser to use Pi Payments.'; ?>");
        }
    }
</script>

<style>
    .category-content {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
        gap: 20px;
        padding: 20px;
    }
    .item-card {
        border: 1px solid #eee;
        padding: 15px;
        border-radius: 10px;
        background: #fff;
        box-shadow: 0 2px 5px rgba(0,0,0,0.05);
        text-align: center;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
    }
    .img-container {
        width: 100%;
        height: 180px;
        overflow: hidden;
        border-radius: 5px;
        margin-bottom: 10px;
        cursor: pointer;
    }
    .img-container img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.3s;
    }
    .img-container img:hover { transform: scale(1.05); }
    .pi-btn {
        background-color: #6748d7;
        color: white;
        border: none;
        padding: 10px;
        border-radius: 5px;
        cursor: pointer;
        font-weight: bold;
        width: 100%;
        margin-top: 10px;
    }
    .pi-btn:hover { background-color: #5436b5; }
    #lightbox-overlay {
        display: none;
        position: fixed;
        z-index: 9999;
        top: 0; left: 0; width: 100%; height: 100%;
        background: rgba(0,0,0,0.9);
        justify-content: center; align-items: center;
    }
    #lightbox-overlay img { max-width: 90%; max-height: 90%; border: 2px solid white; }
</style>

<div class="container" style="max-width: 1200px; margin: auto; padding: 20px;">
    <h1><?php echo htmlspecialchars($category_name); ?> - <?php echo $lang['products'] ?? 'Listings'; ?></h1>

    <?php if ($is_admin_or_vendor): ?>
        <div class="admin-tabs" style="margin-bottom: 20px;">
            <a href="create_listing.php" style="background: #28a745; color: white; padding: 10px 15px; text-decoration: none; border-radius: 5px; font-weight: bold;">
                + <?php echo $lang['create_new_listing'] ?? 'Create Listing'; ?>
            </a>
        </div>
    <?php endif; ?>

    <div class="category-content">
    <?php if ($listings): ?>
        <?php foreach ($listings as $listing): ?>
            <div class="item-card">
                <div>
                    <div class="img-container" onclick="showImage('<?php echo !empty($listing['image_path']) ? htmlspecialchars($listing['image_path']) : 'assets/no-image.png'; ?>')">
                        <img src="<?php echo !empty($listing['image_path']) ? htmlspecialchars($listing['image_path']) : 'assets/no-image.png'; ?>" alt="Product">
                    </div>
                    <h3><?php echo htmlspecialchars($listing['title']); ?></h3>
                    <p style="font-size: 0.9em; color: #666;">
                        <?php echo htmlspecialchars(substr($listing['description'], 0, 80)) . '...'; ?>
                    </p>
                </div>
                <div>
                    <p style="font-weight: bold; font-size: 1.2em; color: #2ecc71;">
                        <?php echo number_format($listing['price'], 2) . ' ' . htmlspecialchars($listing['currency']); ?>
                    </p>
                    <button class="pi-btn" onclick="payWithPi(<?php echo $listing['id']; ?>, <?php echo $listing['price']; ?>, '<?php echo addslashes($listing['title']); ?>')">
                        <?php echo $lang['pay_with_pi'] ?? 'Pay with Pi'; ?>
                    </button>

                    <?php if ($is_admin_or_vendor): ?>
                        <div style="margin-top:15px; font-size: 0.8em; border-top: 1px solid #eee; padding-top:10px;">
                            <a href="create_listing.php?edit=<?php echo $listing['id']; ?>" style="color: #007bff; text-decoration: none; font-weight: bold;">
                                <?php echo $lang['edit'] ?? 'Edit'; ?>
                            </a> |
                            <a href="category_manage.php?name=<?php echo urlencode($category_name); ?>&delete=<?php echo $listing['id']; ?>" 
                               style="color: red; text-decoration: none; font-weight: bold;"
                               onclick="return confirm('<?php echo $lang['confirm_delete'] ?? 'Are you sure?'; ?>');">
                               <?php echo $lang['delete'] ?? 'Delete'; ?>
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <p style="padding: 20px; color: #888;">
            <?php echo $lang['no_listings_found'] ?? 'No listings found in this category.'; ?>
        </p>
    <?php endif; ?>
    </div>
</div>

<div id="lightbox-overlay" onclick="this.style.display='none'">
    <img id="lightbox-img" src="">
</div>

<script>
function showImage(src) {
    document.getElementById('lightbox-img').src = src;
    document.getElementById('lightbox-overlay').style.display = 'flex';
}
</script>

<?php include 'includes/footer.php'; ?>