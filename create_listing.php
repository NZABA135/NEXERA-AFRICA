<?php
include 'includes/header.php';
require_vendor(); // Ensure only vendors/admins can access

// 1. Fetch UNIQUE categories to prevent duplicates in the dropdown
// Using GROUP BY name ensures we only see one of each category name
$cat_stmt = $db->query("SELECT id, name FROM categories GROUP BY name ORDER BY name ASC");
$categories = $cat_stmt->fetchAll(PDO::FETCH_ASSOC);

$errors = [];
$success = '';
$editing = false;

// --- Check if editing ---
if (isset($_GET['edit'])) {
    $edit_id = (int)$_GET['edit'];
    $stmt = $db->prepare("SELECT * FROM listings WHERE id = ?");
    $stmt->execute([$edit_id]);
    $edit_data = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($edit_data) {
        $editing = true;
        $_POST['title'] = $edit_data['title'];
        $_POST['description'] = $edit_data['description'];
        $_POST['price'] = $edit_data['price'];
        $_POST['currency'] = $edit_data['currency'];
        $_POST['category_id'] = $edit_data['category_id'];
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validate_csrf_token($_POST['csrf_token'])) {
        die('Invalid CSRF token');
    }

    // Sanitize and fetch form inputs
    $title = sanitize_input($_POST['title']);
    $description = sanitize_input($_POST['description']);
    $price = (float)$_POST['price'];
    $currency = sanitize_input($_POST['currency']);
    $category_id = (int)$_POST['category_id'];
    $user_id = $_SESSION['user_id'];

    // Validate required fields
    if (!$title || !$description || !$price || !$currency || !$category_id) {
        $errors[] = 'All fields are required.';
    }

    // Handle image uploads
    $uploaded_files = [];
    if (!empty($_FILES['images']['name'][0])) {
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
        $upload_dir = 'uploads/';

        // Ensure upload directory exists
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }

        foreach ($_FILES['images']['tmp_name'] as $index => $tmp_name) {
            $file_name = basename($_FILES['images']['name'][$index]);
            $file_type = $_FILES['images']['type'][$index];

            if (!in_array($file_type, $allowed_types)) {
                $errors[] = "File {$file_name} type not allowed.";
                continue;
            }

            $target_file = $upload_dir . time() . '_' . $file_name;
            if (move_uploaded_file($tmp_name, $target_file)) {
                $uploaded_files[] = $target_file;
            } else {
                $errors[] = "Failed to upload {$file_name}.";
            }
        }
    }

    // Insert or update listing if no errors
    if (empty($errors)) {
        if ($editing) {
            // Update existing listing
            $stmt = $db->prepare("UPDATE listings 
                                  SET title=?, description=?, price=?, currency=?, category_id=? 
                                  WHERE id=?");
            if ($stmt->execute([$title, $description, $price, $currency, $category_id, $edit_id])) {
                $listing_id = $edit_id;
                $success = 'Listing updated successfully!';
            } else {
                $errors[] = 'Failed to update listing.';
            }
        } else {
            // Insert new listing
            $stmt = $db->prepare("INSERT INTO listings 
                                  (user_id, category_id, title, description, price, currency, location, status) 
                                  VALUES (?, ?, ?, ?, ?, ?, ?, 'active')");
            $location = ''; // Set a default or fetch from POST if added later
            if ($stmt->execute([$user_id, $category_id, $title, $description, $price, $currency, $location])) {
                $listing_id = $db->lastInsertId();
                $success = 'Listing created successfully!';
            } else {
                $errors[] = 'Failed to create listing.';
            }
        }

        // Save uploaded images
        foreach ($uploaded_files as $file_path) {
            $img_stmt = $db->prepare("INSERT INTO images (listing_id, image_path) VALUES (?, ?)");
            $img_stmt->execute([$listing_id, $file_path]);
        }
    }
}
?>

<div class="container">
    <h1><?php echo $editing ? 'Edit Listing' : ($lang['create_new_listing'] ?? 'Create New Listing'); ?></h1>

    <?php if ($success): ?>
        <p class="success" style="color: green; font-weight: bold;"><?php echo $success; ?></p>
    <?php endif; ?>

    <?php if ($errors): ?>
        <ul class="errors" style="color: red;">
            <?php foreach ($errors as $error): ?>
                <li><?php echo htmlspecialchars($error); ?></li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>

    <form id="create_listing_form" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">

        <label><?php echo $lang['title'] ?? 'Title'; ?>:</label>
        <input type="text" name="title" value="<?php echo htmlspecialchars($_POST['title'] ?? ''); ?>" required>

        <label><?php echo $lang['description'] ?? 'Description'; ?>:</label>
        <textarea name="description" rows="5" required><?php echo htmlspecialchars($_POST['description'] ?? ''); ?></textarea>

        <label><?php echo $lang['price'] ?? 'Price'; ?>:</label>
        <input type="number" step="0.01" name="price" value="<?php echo htmlspecialchars($_POST['price'] ?? ''); ?>" required>

        <label><?php echo $lang['currency'] ?? 'Currency'; ?>:</label>
        <select name="currency" required>
            <option value="PI" <?php echo (($_POST['currency'] ?? '') === 'PI') ? 'selected' : ''; ?>>PI</option>
        </select>

        <label><?php echo $lang['category'] ?? 'Category'; ?>:</label>
        <select name="category_id" required>
            <option value=""><?php echo $lang['select_category'] ?? 'Select a Category'; ?></option>
            <?php foreach ($categories as $cat): ?>
                <option value="<?php echo $cat['id']; ?>" <?php echo (($_POST['category_id'] ?? '') == $cat['id']) ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($cat['name']); ?>
                </option>
            <?php endforeach; ?>
        </select>

        <label><?php echo $lang['images'] ?? 'Images'; ?>:</label>
        <input type="file" name="images[]" multiple>

        <div style="margin-top: 20px;">
            <button type="submit"><?php echo $editing ? 'Update Listing' : 'Submit Listing'; ?></button>
        </div>
    </form>
</div>

<?php include 'includes/footer.php'; ?>