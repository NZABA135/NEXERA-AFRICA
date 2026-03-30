<?php
// Common functions
require_once __DIR__ . '/../config/db.php';

// CSRF protection
function generate_csrf_token() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function validate_csrf_token($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

// Sanitize input
function sanitize_input($input) {
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

// Upload images
function upload_images($files, $listing_id) {
    global $db;
    $upload_dir = __DIR__ . '/../uploads/';
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }
    $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
    $max_size = 5 * 1024 * 1024; // 5MB

    foreach ($files['name'] as $key => $name) {
        if ($files['error'][$key] === 0 && in_array($files['type'][$key], $allowed_types) && $files['size'][$key] <= $max_size) {
            $ext = pathinfo($name, PATHINFO_EXTENSION);
            $filename = uniqid() . '.' . $ext;
            $path = $upload_dir . $filename;
            if (move_uploaded_file($files['tmp_name'][$key], $path)) {
                $stmt = $db->prepare("INSERT INTO images (listing_id, image_path) VALUES (?, ?)");
                $stmt->execute([$listing_id, 'uploads/' . $filename]);
            }
        }
    }
}

// Get user role
function get_user_role($user_id) {
    global $db;
    $stmt = $db->prepare("SELECT role FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    return $stmt->fetchColumn();
}

// Redirect if not logged in
function require_login() {
    if (!isset($_SESSION['user_id'])) {
        header('Location: login.php');
        exit;
    }
}

// Redirect if not admin
function require_admin() {
    require_login();
    if (get_user_role($_SESSION['user_id']) !== 'admin') {
        header('Location: index.php');
        exit;
    }
}

// Redirect if not vendor
function require_vendor() {
    if (!isset($_SESSION['user_id'])) {
        header("Location: login.php");
        exit();
    }

    $role = get_user_role($_SESSION['user_id']);

    if ($role !== 'vendor' && $role !== 'admin') {
        header("Location: index.php");
        exit();
    }
}

// Simulate email verification
function simulate_email_verification($email) {
    // In production, send email with link. Here, simulate.
    echo "Verification email sent to $email (simulated). User is now verified.";
}

// Pagination function
function paginate($total, $per_page, $current_page) {
    $total_pages = ceil($total / $per_page);
    $offset = ($current_page - 1) * $per_page;
    return ['total_pages' => $total_pages, 'offset' => $offset];
}
?>