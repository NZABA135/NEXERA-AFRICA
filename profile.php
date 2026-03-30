<?php
// profile.php
include 'includes/header.php';

// 1. Security Check: Redirect to login if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// 2. Fetch user data from the database
$user_id = $_SESSION['user_id'];
$stmt = $db->prepare("SELECT username, role, email, created_at FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

if (!$user) {
    echo "User not found.";
    exit;
}
?>

<div style="max-width: 600px; margin: 50px auto; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;">
    
    <div style="background: white; border-radius: 12px; box-shadow: 0 8px 24px rgba(0,0,0,0.1); overflow: hidden;">
        
        <div style="background: linear-gradient(135deg, #ff9800, #e65100); padding: 40px; text-align: center; color: white;">
            <div style="width: 100px; height: 100px; background: rgba(255,255,255,0.2); border: 4px solid white; border-radius: 50%; margin: 0 auto 15px; line-height: 90px; font-size: 3rem; font-weight: bold;">
                <?php echo strtoupper(substr($user['username'], 0, 1)); ?>
            </div>
            <h2 style="margin: 0; font-size: 1.8rem;"><?php echo htmlspecialchars($user['username']); ?></h2>
            <span style="background: rgba(0,0,0,0.2); padding: 4px 12px; border-radius: 20px; font-size: 0.8rem; text-transform: uppercase; letter-spacing: 1px;">
                <?php echo htmlspecialchars($user['role']); ?>
            </span>
        </div>

        <div style="padding: 30px;">
            <h3 style="border-bottom: 2px solid #f1f1f1; padding-bottom: 10px; color: #333;">Account Information</h3>
            
            <table style="width: 100%; border-collapse: collapse; margin-top: 15px;">
                <tr>
                    <td style="padding: 12px 0; color: #777; width: 40%;">Username:</td>
                    <td style="padding: 12px 0; font-weight: bold; color: #333;"><?php echo htmlspecialchars($user['username']); ?></td>
                </tr>
                <tr>
                    <td style="padding: 12px 0; color: #777;">Email Address:</td>
                    <td style="padding: 12px 0; font-weight: bold; color: #333;"><?php echo htmlspecialchars($user['email'] ?? 'Not set'); ?></td>
                </tr>
                <tr>
                    <td style="padding: 12px 0; color: #777;">Account Type:</td>
                    <td style="padding: 12px 0; font-weight: bold; color: #333;"><?php echo ucfirst($user['role']); ?></td>
                </tr>
                <tr>
                    <td style="padding: 12px 0; color: #777;">Member Since:</td>
                    <td style="padding: 12px 0; font-weight: bold; color: #333;"><?php echo date("F j, Y", strtotime($user['created_at'])); ?></td>
                </tr>
            </table>

            <div style="margin-top: 30px; display: flex; gap: 10px;">
                <a href="edit_profile.php" style="flex: 1; text-align: center; background: #2196F3; color: white; padding: 12px; border-radius: 6px; text-decoration: none; font-weight: bold;">Edit Profile</a>
                <a href="index.php" style="flex: 1; text-align: center; background: #f1f1f1; color: #333; padding: 12px; border-radius: 6px; text-decoration: none; font-weight: bold;">Back to Home</a>
            </div>
        </div>
    </div>

    <p style="text-align: center; color: #999; font-size: 0.85rem; margin-top: 20px;">
        Your information is secured with NEXERA encryption.
    </p>
</div>

<?php include 'includes/footer.php'; ?>