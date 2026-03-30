<?php
// edit_listing_admin.php - Located in D:\XAMPP\htdocs\nexera_africa\

// 1. Safe Session Start
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 2. Load Database Connection
$db_file = __DIR__ . '/config/db.php';
if (file_exists($db_file)) {
    require_once $db_file;
} else {
    die("Error: Config file not found at " . $db_file);
}

// 3. Include Header
include_once __DIR__ . '/includes/header.php';

// 4. Security: Verify Admin Role
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

$message = "";

// 5. Logic: Handle Edit and Redirect
if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    
    // Handle Form Submission
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $title = sanitize_input($_POST['title']);
        $description = sanitize_input($_POST['description']);
        $price = (float)$_POST['price'];

        try {
            $update_stmt = $db->prepare("UPDATE listings SET title = ?, description = ?, price = ? WHERE id = ?");
            if ($update_stmt->execute([$title, $description, $price, $id])) {
                $message = "<div style='background: #d4edda; color: #155724; padding: 15px; border-radius: 4px; margin-bottom: 20px;'>
                                <strong>Success!</strong> Listing updated. Redirecting to dashboard...
                            </div>";
                header("refresh:2;url=dashboard_admin.php");
            } else {
                $message = "<div style='background: #f8d7da; color: #721c24; padding: 15px; border-radius: 4px; margin-bottom: 20px;'>Error updating record.</div>";
            }
        } catch (PDOException $e) {
            $message = "<div style='color: red;'>Database Error: " . $e->getMessage() . "</div>";
        }
    }

    // Fetch current data (including image)
    $stmt = $db->prepare("SELECT * FROM listings WHERE id = ?");
    $stmt->execute([$id]);
    $listing = $stmt->fetch();

    if (!$listing) {
        die("<div style='padding:20px;'>Listing not found. <a href='dashboard_admin.php'>Go Back</a></div>");
    }
} else {
    header("Location: dashboard_admin.php");
    exit();
}
?>

<div style="max-width: 900px; margin: 30px auto; font-family: 'Segoe UI', sans-serif; padding: 25px; border: 1px solid #ddd; border-radius: 12px; background: #fff; box-shadow: 0 4px 12px rgba(0,0,0,0.1);">
    
    <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 2px solid #eee; padding-bottom: 15px; margin-bottom: 25px;">
        <h2 style="margin: 0; color: #333;">Editing Listing #<?php echo $id; ?></h2>
        <a href="admin_dashboard.php" style="text-decoration: none; color: #007bff; font-weight: 600;">&larr; Back to Dashboard</a>
    </div>
    
    <?php echo $message; ?>

    <div style="display: flex; flex-wrap: wrap; gap: 30px;">
        
        <div style="flex: 1; min-width: 300px; text-align: center;">
            <label style="display:block; font-weight:bold; margin-bottom:10px; text-align: left; color: #555;">Product Preview</label>
            <div style="border: 1px solid #eee; padding: 10px; border-radius: 8px; background: #fafafa;">
                <?php 
                // Adjust 'image' to your actual database column name
                $imagePath = !empty($listing['image']) ? 'assets/images/' . $listing['image'] : 'assets/images/no-image.png'; 
                ?>
                <img src="<?php echo htmlspecialchars($imagePath); ?>" alt="Product Image" style="max-width: 100%; height: auto; border-radius: 4px; box-shadow: 0 2px 5px rgba(0,0,0,0.1);">
                
                <?php if(empty($listing['image'])): ?>
                    <p style="color: #999; font-size: 0.9rem; margin-top: 10px;">No image uploaded for this listing.</p>
                <?php endif; ?>
            </div>
        </div>

        <div style="flex: 2; min-width: 300px;">
            <form method="POST" action="">
                <div style="margin-bottom: 20px;">
                    <label style="display:block; font-weight:bold; margin-bottom:8px;">Listing Title</label>
                    <input type="text" name="title" value="<?php echo htmlspecialchars($listing['title']); ?>" required style="width: 100%; padding: 12px; border: 1px solid #ccc; border-radius: 6px; font-size: 1rem;">
                </div>

                <div style="margin-bottom: 20px;">
                    <label style="display:block; font-weight:bold; margin-bottom:8px;">Description</label>
                    <textarea name="description" rows="8" style="width: 100%; padding: 12px; border: 1px solid #ccc; border-radius: 6px; font-size: 1rem; line-height: 1.5;" required><?php echo htmlspecialchars($listing['description']); ?></textarea>
                </div>

                <div style="margin-bottom: 25px;">
                    <label style="display:block; font-weight:bold; margin-bottom:8px;">Price ($)</label>
                    <input type="number" name="price" value="<?php echo htmlspecialchars($listing['price']); ?>" step="0.01" required style="width: 100%; padding: 12px; border: 1px solid #ccc; border-radius: 6px; font-size: 1rem;">
                </div>

                <div style="display: flex; gap: 15px;">
                    <button type="submit" style="background: #28a745; color: white; padding: 12px 30px; border: none; border-radius: 6px; cursor: pointer; font-weight: bold; font-size: 1rem; flex: 1;">
                        Update Listing
                    </button>
                    <a href="dashboard_admin.php" style="text-decoration: none; color: #333; background: #e9ecef; padding: 12px 30px; border-radius: 6px; font-weight: bold; text-align: center; flex: 1;">
                        Cancel
                    </a>
                </div>
            </form>
        </div>

    </div>
</div>

<?php include_once __DIR__ . '/includes/footer.php'; ?>