<?php
// edit_profile.php
include 'includes/functions.php'; 
require_once 'config/db.php';     

if (session_status() === PHP_SESSION_NONE) { session_start(); }

// 1. Security Check: Redirect to login if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$success_message = "";
$error_message = "";

// 2. Handle the Update Logic
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validate_csrf_token($_POST['csrf_token'])) {
        die('Invalid CSRF token');
    }

    $new_email = sanitize_input($_POST['email']);
    $new_password = $_POST['password'];

    try {
        if (!empty($new_password)) {
            // Update both Email and Password
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $stmt = $db->prepare("UPDATE users SET email = ?, password = ? WHERE id = ?");
            $stmt->execute([$new_email, $hashed_password, $user_id]);
        } else {
            // Update only Email
            $stmt = $db->prepare("UPDATE users SET email = ? WHERE id = ?");
            $stmt->execute([$new_email, $user_id]);
        }
        $success_message = "Profile updated successfully!";
    } catch (PDOException $e) {
        $error_message = "Error updating profile: " . $e->getMessage();
    }
}

// 3. Fetch current data to populate the form
$stmt = $db->prepare("SELECT username, email FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

include 'includes/header.php'; 
?>

<div style="max-width: 500px; margin: 50px auto; font-family: Arial, sans-serif;">
    <div style="background: white; padding: 30px; border-radius: 10px; box-shadow: 0 4px 15px rgba(0,0,0,0.1);">
        <h2 style="text-align: center; color: #333;">Edit Your Profile</h2>
        <p style="text-align: center; color: #777; margin-bottom: 25px;">Update your account details below.</p>

        <?php if ($success_message): ?>
            <div style="background: #d4edda; color: #155724; padding: 10px; border-radius: 5px; margin-bottom: 20px; text-align: center;">
                <?php echo $success_message; ?>
            </div>
        <?php endif; ?>

        <?php if ($error_message): ?>
            <div style="background: #f8d7da; color: #721c24; padding: 10px; border-radius: 5px; margin-bottom: 20px; text-align: center;">
                <?php echo $error_message; ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="">
            <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">

            <div style="margin-bottom: 15px;">
                <label style="display: block; font-weight: bold; margin-bottom: 5px;">Username (Cannot be changed):</label>
                <input type="text" value="<?php echo htmlspecialchars($user['username']); ?>" disabled 
                       style="width: 100%; padding: 10px; background: #f9f9f9; border: 1px solid #ddd; border-radius: 4px; color: #888;">
            </div>

            <div style="margin-bottom: 15px;">
                <label style="display: block; font-weight: bold; margin-bottom: 5px;">Email Address:</label>
                <input type="email" name="email" value="<?php echo htmlspecialchars($user['email'] ?? ''); ?>" required 
                       style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box;">
            </div>

            <div style="margin-bottom: 25px;">
                <label style="display: block; font-weight: bold; margin-bottom: 5px;">New Password:</label>
                <input type="password" name="password" placeholder="Leave blank to keep current password" 
                       style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box;">
            </div>

            <div style="display: flex; gap: 10px;">
                <button type="submit" style="flex: 2; padding: 12px; background: #ff9800; color: white; border: none; border-radius: 4px; font-weight: bold; cursor: pointer;">
                    Update Profile
                </button>
                <a href="profile.php" style="flex: 1; text-align: center; padding: 12px; background: #eee; color: #333; text-decoration: none; border-radius: 4px; font-weight: bold;">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</div>

<?php include 'includes/footer.php'; ?>