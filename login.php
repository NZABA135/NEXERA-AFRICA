<?php
// login.php - Logic MUST come before any HTML output
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$error_message = "";

// --- Handle Login Logic ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validate_csrf_token($_POST['csrf_token'])) {
        die('Invalid CSRF token');
    }

    $username = sanitize_input($_POST['username']);
    $password = $_POST['password'];

    // Fetch user details
    $stmt = $db->prepare("SELECT id, password, role, blocked FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        
        if ($user['blocked']) {
            $error_message = "Your account has been blocked. Please contact support.";
        } else {
            // Success: Set session variables
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['username'] = $username;
            $_SESSION['login_success'] = true; // Trigger for the Welcome Pop-up

            // Role-based redirection
            if ($user['role'] === 'admin') {
                header("Location: admin_dashboard.php");
                exit;
            } else {
                header("Location: marketplace.php");
                exit;
            }
        }
    } else {
        $error_message = "Invalid username or password.";
    }
}

// NOW we include the header because we know no redirection is happening
include 'includes/header.php'; 
?>

<div style="max-width:450px; margin:50px auto; padding:20px; font-family: Arial, sans-serif;">
    <h2 style="text-align:center; color:Green;">Login to NEXERA</h2>

    <?php if (isset($_GET['msg']) && $_GET['msg'] === 'registered'): ?>
        <div style="background:#d4edda; color:#155724; padding:15px; border-radius:5px; border:1px solid #c3e6cb; margin-bottom:20px; text-align:center;">
            <strong>Registration Successful!</strong><br>Please log in with your credentials.
        </div>
    <?php endif; ?>

    <?php if ($error_message): ?>
        <div style="background:#f8d7da; color:#721c24; padding:15px; border-radius:5px; border:1px solid #f5c6cb; margin-bottom:20px; text-align:center;">
            <?php echo $error_message; ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="" style="background:#fff; padding:30px; border-radius:8px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); overflow: hidden;">
        <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">

        <div style="margin-bottom:20px; width: 100%;">
            <label style="display:block; margin-bottom:8px; font-weight:bold; color: #333;"><?php echo $lang['username']; ?>:</label>
            <input type="text" name="username" required 
                   style="width:100%; padding:12px; border:1px solid #ccc; border-radius:4px; box-sizing: border-box; display: block;">
        </div>

        <div style="margin-bottom:25px; width: 100%;">
            <label style="display:block; margin-bottom:8px; font-weight:bold; color: #333;"><?php echo $lang['password']; ?>:</label>
            <input type="password" name="password" required 
                   style="width:100%; padding:12px; border:1px solid #ccc; border-radius:4px; box-sizing: border-box; display: block;">
        </div>

        <button type="submit" style="width:100%; padding:12px; background:#ff9800; color:#fff; border:none; border-radius:4px; cursor:pointer; font-size:1rem; font-weight:bold; transition: background 0.3s;">
            <?php echo $lang['login']; ?>
        </button>

        <p style="text-align:center; margin-top:20px; font-size:0.9rem;">
            Don't have an account? <a href="register.php" style="color:#1e88e5; text-decoration:none; font-weight:bold;">Register Here</a>
        </p>
    </form>
</div>

<?php include 'includes/footer.php'; ?>