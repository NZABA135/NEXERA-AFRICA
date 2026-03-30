<?php
// chat.php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 1. Security Check: Must be logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$current_user_id = $_SESSION['user_id'];

// 2. Identify the person we are talking to
$other_user_id = 0;
$url_param = "";

if (isset($_GET['vendor_id'])) {
    $other_user_id = intval($_GET['vendor_id']);
    $url_param = "vendor_id=" . $other_user_id;
} elseif (isset($_GET['admin_id'])) {
    $other_user_id = intval($_GET['admin_id']);
    $url_param = "admin_id=" . $other_user_id;
}

// 3. Handle Message Submission (BEFORE any HTML output to avoid Header error)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $other_user_id !== 0 && !empty(trim($_POST['message']))) {
    $msg_text = trim($_POST['message']);
    $send_stmt = $db->prepare("INSERT INTO messages (sender_id, receiver_id, message) VALUES (?, ?, ?)");
    $send_stmt->execute([$current_user_id, $other_user_id, $msg_text]);
    
    // Redirect to same page to clear POST data and show new message
    header("Location: chat.php?" . $url_param);
    exit;
}

// 4. Now include header (Starts HTML output)
include 'includes/header.php';

// 5. Error handling if no user is selected
if ($other_user_id === 0) {
    echo "<div style='padding: 50px; text-align: center; color: #333;'>
            <h2>⚠️ " . ($lang['category_not_specified'] ?? 'User Not Selected') . "</h2>
            <p>Please select a contact from your dashboard to start a conversation.</p>
            <a href='index.php' style='color: #2e7d32; font-weight: bold;'>← Back to Home</a>
          </div>";
    include 'includes/footer.php';
    exit;
}

// 6. Fetch other user's name
$stmt = $db->prepare("SELECT username FROM users WHERE id = ?");
$stmt->execute([$other_user_id]);
$other_user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$other_user) {
    echo "<div style='padding: 50px; text-align: center; color: #333;'><h2>User not found.</h2></div>";
    include 'includes/footer.php';
    exit;
}

// 7. Mark messages as read
$read_stmt = $db->prepare("UPDATE messages SET is_read = 1 WHERE sender_id = ? AND receiver_id = ?");
$read_stmt->execute([$other_user_id, $current_user_id]);

// 8. Fetch Conversation History
$history_stmt = $db->prepare("
    SELECT * FROM messages 
    WHERE (sender_id = ? AND receiver_id = ?) 
       OR (sender_id = ? AND receiver_id = ?) 
    ORDER BY created_at ASC
");
$history_stmt->execute([$current_user_id, $other_user_id, $other_user_id, $current_user_id]);
$messages = $history_stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<style>
    .chat-container {
        max-width: 800px;
        margin: 20px auto;
        border: 1px solid #ddd;
        border-radius: 12px;
        overflow: hidden;
        background: #fff;
        display: flex;
        flex-direction: column;
        box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    }
    .chat-header {
        background: #2e7d32;
        color: white;
        padding: 15px 20px;
        font-size: 1.2em;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    #chat-box {
        height: 450px;
        overflow-y: auto;
        padding: 20px;
        background: #f9f9f9;
        display: flex;
        flex-direction: column;
    }
    .msg {
        margin-bottom: 15px;
        max-width: 75%;
        padding: 12px 16px;
        border-radius: 18px;
        font-size: 14px;
        line-height: 1.4;
        position: relative;
    }
    .msg-me {
        align-self: flex-end;
        background: #e3f2fd;
        color: #0d47a1;
        border: 1px solid #bbdefb;
        border-bottom-right-radius: 2px;
    }
    .msg-them {
        align-self: flex-start;
        background: #ffffff;
        color: #333;
        border: 1px solid #e0e0e0;
        border-bottom-left-radius: 2px;
    }
    .msg-time {
        font-size: 10px;
        display: block;
        margin-top: 5px;
        opacity: 0.6;
    }
    .chat-form {
        padding: 15px;
        background: #fff;
        border-top: 1px solid #eee;
        display: flex;
        gap: 10px;
    }
    .chat-input {
        flex: 1;
        padding: 12px;
        border: 1px solid #ddd;
        border-radius: 25px;
        outline: none;
        color: #333;
    }
    .chat-submit {
        background: #ff9800;
        color: white;
        border: none;
        padding: 0 25px;
        border-radius: 25px;
        cursor: pointer;
        font-weight: bold;
        transition: 0.3s;
    }
    .chat-submit:hover {
        background: #e65100;
    }
</style>

<div class="chat-container">
    <div class="chat-header">
        <span>💬 <?php echo $lang['message'] ?? 'Chat'; ?>: <strong><?php echo htmlspecialchars($other_user['username']); ?></strong></span>
        <a href="index.php" style="color: white; text-decoration: none; font-size: 0.8em;">✕ Close</a>
    </div>

    <div id="chat-box">
        <?php if (empty($messages)): ?>
            <p style="text-align: center; color: #999; margin-top: 50px;">No messages yet. Send a message to start the conversation.</p>
        <?php else: ?>
            <?php foreach ($messages as $m): ?>
                <?php $is_me = ($m['sender_id'] == $current_user_id); ?>
                <div class="msg <?php echo $is_me ? 'msg-me' : 'msg-them'; ?>">
                    <?php echo htmlspecialchars($m['message']); ?>
                    <span class="msg-time"><?php echo date('H:i', strtotime($m['created_at'])); ?></span>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <form class="chat-form" method="POST">
        <input type="text" name="message" class="chat-input" 
               placeholder="Type your message..." required autocomplete="off">
        <button type="submit" class="chat-submit">
            <?php echo $lang['submit'] ?? 'Send'; ?>
        </button>
    </form>
</div>

<script>
    // Auto-scroll to latest message
    const chatBox = document.getElementById('chat-box');
    chatBox.scrollTop = chatBox.scrollHeight;

    // Optional: Refresh page every 30 seconds to check for new messages
    setTimeout(function(){
       location.reload();
    }, 30000);
</script>

<?php include 'includes/footer.php'; ?>