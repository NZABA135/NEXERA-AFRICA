<?php
// marketplace.php
ob_start(); // Start output buffering to prevent header errors
session_start();

// 1. DATABASE & CONFIG (Updated Path)
// Using __DIR__ ensures the path is absolute and reliable
$db_path = __DIR__ . '/config/db.php';

if (file_exists($db_path)) {
    require_once $db_path;
} else {
    // Helpful error message for debugging
    die("Fatal Error: Database configuration file not found at: " . $db_path);
}

// 2. SESSION CHECK (Must happen before any HTML)
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// --- Handle new listing upload ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['upload_listing'])) {
    $title = sanitize_input($_POST['title']);
    $description = sanitize_input($_POST['description']);
    $price = (float)$_POST['price'];
    $currency = sanitize_input($_POST['currency']);
    $location = sanitize_input($_POST['location'] ?? ''); 
    $category_id = (int)$_POST['category'];
    $user_id = $_SESSION['user_id'];

    $image_path = '';
    if (!empty($_FILES['image']['name'])) {
        $target_dir = "uploads/";
        if (!is_dir($target_dir)) mkdir($target_dir, 0755, true);
        $filename = time() . '_' . basename($_FILES['image']['name']);
        $target_file = $target_dir . $filename;
        if (move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
            $image_path = $target_file;
        }
    }

    $stmt = $db->prepare("INSERT INTO listings (user_id, category_id, title, description, price, currency, location, status) VALUES (?, ?, ?, ?, ?, ?, ?, 'active')");
    if ($stmt->execute([$user_id, $category_id, $title, $description, $price, $currency, $location])) {
        $listing_id = $db->lastInsertId();
        if ($image_path) {
            $img_stmt = $db->prepare("INSERT INTO images (listing_id, image_path) VALUES (?, ?)");
            $img_stmt->execute([$listing_id, $image_path]);
        }
        $success_msg = $lang['listing_success'] ?? 'Listing uploaded successfully!';
    }
}

// --- Fetch Products ---
$top_stmt = $db->query("SELECT l.*, i.image_path FROM listings l LEFT JOIN images i ON l.id = i.listing_id WHERE l.status = 'active' GROUP BY l.id ORDER BY l.created_at DESC LIMIT 4");
$top_products = $top_stmt->fetchAll(PDO::FETCH_ASSOC);

$search = isset($_GET['search']) ? sanitize_input($_GET['search']) : '';
$category = isset($_GET['category']) ? sanitize_input($_GET['category']) : '';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 10;

$where = "WHERE l.status = 'active'";
$params = [];
if ($search) {
    $where .= " AND (l.title LIKE ? OR l.description LIKE ?)";
    $params[] = "%$search%"; $params[] = "%$search%";
}
if ($category) {
    $where .= " AND l.category_id = ?";
    $params[] = $category;
}

$count_stmt = $db->prepare("SELECT COUNT(*) FROM listings l $where");
$count_stmt->execute($params);
$total = $count_stmt->fetchColumn();
$total_pages = ceil($total / $per_page);
$offset = ($page - 1) * $per_page;

$sql = "SELECT l.*, u.username AS seller, i.image_path FROM listings l JOIN users u ON l.user_id = u.id LEFT JOIN images i ON l.id = i.listing_id $where GROUP BY l.id ORDER BY l.created_at DESC LIMIT ?, ?";
$stmt = $db->prepare($sql);
$i = 1;
foreach ($params as $param) { $stmt->bindValue($i++, $param); }
$stmt->bindValue($i++, (int)$offset, PDO::PARAM_INT);
$stmt->bindValue($i++, (int)$per_page, PDO::PARAM_INT);
$stmt->execute();
$listings = $stmt->fetchAll(PDO::FETCH_ASSOC);

$categories = $db->query("SELECT * FROM categories")->fetchAll(PDO::FETCH_ASSOC);

// 4. NOW INCLUDE THE UI COMPONENTS
include 'includes/header.php';
?>

<script src="https://sdk.minepi.com/pi-sdk.js"></script>
<script>
    Pi.init({ version: "2.0", sandbox: true });

    async function payWithPi(listingId, amount, title) {
        try {
            const payment = await Pi.createPayment({
                amount: amount,
                memo: "Buying: " + title,
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
                onCancel: (paymentId) => { console.log("User cancelled."); },
                onError: (error, payment) => { console.error("Pi Error:", error); },
            });
        } catch (err) {
            alert("<?php echo $lang['pi_browser_required'] ?? 'Please open in Pi Browser to pay.'; ?>");
        }
    }

    function zoomImage(src) {
        const modal = document.getElementById('imageModal');
        const modalImg = document.getElementById('modalImg');
        modal.style.display = "flex";
        modalImg.src = src;
    }
</script>

<style>
    #imageModal {
        display: none; position: fixed; z-index: 9999; left: 0; top: 0; width: 100%; height: 100%;
        background-color: rgba(0,0,0,0.9); justify-content: center; align-items: center; cursor: pointer;
    }
    #modalImg { max-width: 90%; max-height: 90%; border-radius: 5px; }
    .zoomable { cursor: zoom-in; transition: opacity 0.3s; }
    .zoomable:hover { opacity: 0.8; }
    .pi-pay-btn {
        background-color: #6748d7; color: white; border: none; padding: 8px 12px;
        border-radius: 5px; cursor: pointer; font-weight: bold; width: 100%; margin-top: 10px;
    }
</style>

<div class="container" style="max-width: 1200px; margin: auto; padding: 20px;">
    
    <?php if (isset($success_msg)): ?>
        <div style="background: #d4edda; color: #155724; padding: 15px; border-radius: 5px; margin-bottom: 20px; text-align: center;">
            <?php echo $success_msg; ?>
        </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
        <div style="background: #343a40; color: white; padding: 15px; border-radius: 8px; margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center;">
            <span><strong><?php echo $lang['admin_mode'] ?? 'Admin Mode Active'; ?></strong></span>
            <a href="admin_dashboard.php" style="background: #ffc107; color: #333; padding: 8px 15px; border-radius: 5px; text-decoration: none; font-weight: bold;"><?php echo $lang['dashboard']; ?></a>
        </div>
    <?php endif; ?>

    <?php if ($top_products && !$search && !$category): ?>
        <h2>🔥 <?php echo $lang['top_products'] ?? 'Top Products'; ?></h2>
        <div style="display: flex; gap: 20px; overflow-x: auto; padding-bottom: 20px; margin-bottom: 40px;">
            <?php foreach ($top_products as $top): ?>
                <div style="min-width: 250px; background: #fff; border-radius: 10px; box-shadow: 0 4px 8px rgba(0,0,0,0.1); overflow: hidden; display: flex; flex-direction: column;">
                    <img src="<?php echo !empty($top['image_path']) ? htmlspecialchars($top['image_path']) : 'assets/no-image.png'; ?>" class="zoomable" onclick="zoomImage(this.src)" style="width: 100%; height: 180px; object-fit: cover;">
                    <div style="padding: 15px; flex-grow: 1;">
                        <h4><?php echo htmlspecialchars($top['title']); ?></h4>
                        <p style="color: #ff9800; font-weight: bold;"><?php echo htmlspecialchars($top['price']); ?> <?php echo htmlspecialchars($top['currency']); ?></p>
                        <button class="pi-pay-btn" onclick="payWithPi(<?php echo $top['id']; ?>, <?php echo $top['price']; ?>, '<?php echo addslashes($top['title']); ?>')">
                            <?php echo $lang['pay_with_pi'] ?? 'Pay with Pi'; ?>
                        </button>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <form method="GET" style="display: flex; gap: 10px; margin-bottom: 30px;">
        <input type="text" name="search" placeholder="<?php echo $lang['search']; ?>..." value="<?php echo htmlspecialchars($search); ?>" style="flex: 3; padding: 10px; border-radius: 5px; border: 1px solid #ddd;">
        <select name="category" style="flex: 1; padding: 10px; border-radius: 5px;">
            <option value=""><?php echo $lang['all_categories'] ?? 'All Categories'; ?></option>
            <?php foreach ($categories as $cat): ?>
                <option value="<?php echo $cat['id']; ?>" <?php echo ($category == $cat['id']) ? 'selected' : ''; ?>>
                    <?php 
                        $cat_key = strtolower(str_replace(' ', '_', $cat['name']));
                        echo $lang[$cat_key] ?? htmlspecialchars($cat['name']); 
                    ?>
                </option>
            <?php endforeach; ?>
        </select>
        <button type="submit" style="padding: 10px 20px; background: #333; color: #fff; border: none; border-radius: 5px;"><?php echo $lang['search']; ?></button>
    </form>

    <h3><?php echo $lang['all_listings'] ?? 'All Listings'; ?></h3>
    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 20px;">
    <?php if ($listings): ?>
        <?php foreach ($listings as $listing): ?>
            <div style="border: 1px solid #eee; border-radius: 8px; padding: 15px; background: #fff; box-shadow: 0 2px 5px rgba(0,0,0,0.05); display: flex; flex-direction: column; justify-content: space-between;">
                <div>
                    <img src="<?php echo !empty($listing['image_path']) ? htmlspecialchars($listing['image_path']) : 'assets/no-image.png'; ?>" class="zoomable" onclick="zoomImage(this.src)" style="width: 100%; height: 200px; object-fit: cover; border-radius: 5px; margin-bottom: 10px;">
                    <h3 style="margin: 0; font-size: 1.1rem;"><?php echo htmlspecialchars($listing['title']); ?></h3>
                    <p style="color: #444; font-weight: bold;"><?php echo htmlspecialchars($listing['price']); ?> <?php echo htmlspecialchars($listing['currency']); ?></p>
                </div>
                <div>
                    <button class="pi-pay-btn" onclick="payWithPi(<?php echo $listing['id']; ?>, <?php echo $listing['price']; ?>, '<?php echo addslashes($listing['title']); ?>')">
                        <?php echo $lang['pay_with_pi'] ?? 'Pay with Pi'; ?>
                    </button>
                    <div style="margin-top: 10px; display: flex; justify-content: space-between;">
                        <a href="view_listing.php?id=<?php echo $listing['id']; ?>" style="color: #007bff; text-decoration: none; font-size: 0.9rem;">
                            <?php echo $lang['view_details'] ?? 'View Details'; ?>
                        </a>
                        <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                            <a href="edit_listing_admin.php?id=<?php echo $listing['id']; ?>" style="color: #d9534f; font-weight: bold; font-size: 0.85rem;">
                                <?php echo $lang['edit'] ?? 'Edit'; ?>
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <p style="color: #888;"><?php echo $lang['no_listings_found'] ?? 'No listings found.'; ?></p>
    <?php endif; ?>
    </div>
</div>

<div id="imageModal" onclick="this.style.display='none'">
    <img id="modalImg" src="">
</div>

<?php include 'includes/footer.php'; ?>