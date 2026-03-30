<?php
/**
 * NEXERA AFRICA - Registration System
 * Fixed: Language Loading Order & Variable Definitions
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 1. Load basic dependencies
include __DIR__ . '/config/db.php'; 
include __DIR__ . '/includes/functions.php';

// 2. LOAD LANGUAGE BEFORE REGISTRATION LOGIC (The Fix)
$current_lang = $_SESSION['lang'] ?? 'en'; 
$lang_file = __DIR__ . "/languages/lang_{$current_lang}.php";

if (file_exists($lang_file)) {
    include $lang_file;
} else {
    // Safety fallback to prevent "Undefined variable $lang"
    $lang = []; 
}

define('MAX_ADMINS', 3);
$message = ""; 

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || !validate_csrf_token($_POST['csrf_token'])) {
        die('Security violation: Invalid CSRF token.');
    }

    if ($_POST['password'] !== $_POST['confirm_password']) {
        // Fallback text if $lang key is missing
        $err_pass = $lang['error_pass_match'] ?? 'Passwords do not match!';
        $message = "<div style='background:#f8d7da; color:#721c24; padding:10px; border-radius:5px; margin-bottom:15px; text-align:center;'>$err_pass</div>";
    } elseif (!isset($_POST['accept_terms']) || $_POST['accept_terms'] !== '1') {
        $err_terms = $lang['error_terms'] ?? 'Please accept the Terms.';
        $message = "<div style='background:#fff3cd; color:#856404; padding:10px; border-radius:5px; margin-bottom:15px; text-align:center;'>$err_terms</div>";
    } else {
        $username = sanitize_input($_POST['username']);
        $email    = sanitize_input($_POST['email']);
        $phone    = sanitize_input($_POST['phone']);
        $role     = sanitize_input($_POST['role']);
        $hashed_password = password_hash($_POST['password'], PASSWORD_BCRYPT);

        if ($role === 'admin') {
            $stmt = $db->prepare("SELECT COUNT(*) FROM users WHERE role = 'admin'");
            $stmt->execute();
            if ($stmt->fetchColumn() >= MAX_ADMINS) {
                $err_limit = $lang['error_admin_limit'] ?? 'Administrator limit reached.';
                $message = "<div style='background:#f8d7da; color:#721c24; padding:10px; border-radius:5px; margin-bottom:15px; text-align:center;'>$err_limit</div>";
                $role = null; 
            }
        }

        if ($role) {
            $check_stmt = $db->prepare("SELECT id FROM users WHERE email = ?");
            $check_stmt->execute([$email]);

            if ($check_stmt->fetch()) {
                $err_exists = $lang['error_email_exists'] ?? 'Email already registered.';
                $message = "<div style='background:#f8d7da; color:#721c24; padding:10px; border-radius:5px; margin-bottom:15px; text-align:center;'>$err_exists</div>";
            } else {
                $insert = $db->prepare("INSERT INTO users (username, email, phone, password, role, verified) VALUES (?, ?, ?, ?, ?, 1)");
                if ($insert->execute([$username, $email, $phone, $hashed_password, $role])) {
                    header('Location: login.php?msg=success');
                    exit;
                }
            }
        }
    }
}

$current_token = generate_csrf_token();
include 'includes/header.php';
?>

<style>
    .reg-page-wrapper { width: 100%; min-height: 100vh; padding: 60px 15px 100px 15px; background: #f0f2f5; display: flex; justify-content: center; align-items: flex-start; box-sizing: border-box; }
    .reg-card { width: 100%; max-width: 500px; background: #fff; padding: 35px; border-radius: 15px; box-shadow: 0 10px 30px rgba(0,0,0,0.1); box-sizing: border-box; }
    .reg-title { text-align: center; margin: 0 0 10px 0; color: #222; font-size: 28px; font-weight: 800; }
    .reg-subtitle { text-align: center; color: #777; margin-bottom: 25px; font-size: 14px; }
    .form-group { margin-bottom: 15px; }
    .form-group label { display: block; margin-bottom: 5px; font-weight: 600; color: #444; }
    .form-group input, .form-group select { width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 8px; box-sizing: border-box; }
    .terms-container { background: #f9f9f9; border: 1px solid #eee; padding: 15px; border-radius: 8px; max-height: 120px; overflow-y: auto; font-size: 12px; color: #666; line-height: 1.6; margin-bottom: 15px; }
    .terms-row { display: flex; align-items: flex-start; gap: 10px; margin: 20px 0; cursor: pointer; }
    .terms-row input { width: 18px; height: 18px; flex-shrink: 0; margin-top: 2px; }
    .terms-text { font-size: 13px; color: #555; line-height: 1.4; }
    .btn-submit { width: 100%; padding: 15px; background: #FF9800; color: #fff; border: none; border-radius: 8px; font-size: 18px; font-weight: bold; cursor: pointer; margin-bottom: 15px; }
    .login-footer { text-align: center; margin-top: 15px; font-size: 14px; color: #666; display: block; }
    .login-footer a { color: #FF9800; text-decoration: none; font-weight: bold; }
</style>

<div class="reg-page-wrapper">
    <div class="reg-card">
        <h1 class="reg-title">NEXERA AFRICA</h1>
        <p class="reg-subtitle"><?php echo $lang['join_ecosystem'] ?? 'Join our Pi-powered digital ecosystem'; ?></p>

        <?php echo $message; ?>

        <form method="POST">
            <input type="hidden" name="csrf_token" value="<?php echo $current_token; ?>">

            <div class="form-group">
                <label><?php echo $lang['full_name'] ?? 'Full Name'; ?></label>
                <input type="text" name="username" required>
            </div>

            <div class="form-group">
                <label><?php echo $lang['email'] ?? 'Email Address'; ?></label>
                <input type="email" name="email" required>
            </div>

            <div class="form-group">
                <label><?php echo $lang['phone'] ?? 'Phone Number'; ?></label>
                <input type="text" name="phone" required>
            </div>

            <div class="form-group">
                <label><?php echo $lang['password'] ?? 'Password'; ?></label>
                <input type="password" name="password" required>
            </div>

            <div class="form-group">
                <label><?php echo $lang['confirm_password'] ?? 'Confirm Password'; ?></label>
                <input type="password" name="confirm_password" required>
            </div>

            <div class="form-group">
                <label><?php echo $lang['account_type'] ?? 'Account Type'; ?></label>
                <select name="role">
                    <option value="user"><?php echo $lang['normal_user'] ?? 'User'; ?></option>
                    <option value="vendor"><?php echo $lang['vendor'] ?? 'Vendor'; ?></option>
                    <option value="admin"><?php echo $lang['admin'] ?? 'Admin'; ?></option>
                </select>
            </div>

            <div class="terms-container">
                <strong><?php echo $lang['terms_title'] ?? 'Terms & Conditions'; ?></strong><br>
                <?php echo $lang['terms_body'] ?? 'Please agree to the terms.'; ?><br><br>
                <strong><?php echo $lang['products_listing'] ?? 'Rules'; ?></strong><br>
                <?php echo $lang['products_listing_body'] ?? 'No fraudulent behavior.'; ?>
            </div>

            <label class="terms-row">
                <input type="checkbox" name="accept_terms" value="1" required>
                <span class="terms-text"><?php echo $lang['accept_checkbox'] ?? 'I agree to the terms'; ?></span>
            </label>

            <button type="submit" class="btn-submit"><?php echo $lang['register_now'] ?? 'Register Now'; ?></button>
            
            <div class="login-footer">
                <?php echo $lang['already_have_account'] ?? 'Have an account?'; ?> 
                <a href="login.php"><?php echo $lang['login_here'] ?? 'Login'; ?></a>
            </div>
        </form>
    </div>
</div>

<?php include 'includes/footer.php'; ?>