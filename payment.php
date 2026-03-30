<?php
include __DIR__ . '/config/db.php';
include __DIR__ . '/includes/functions.php';
include 'includes/header.php';

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

/* ==============================
    FETCH LISTING DATA
============================== */
$stmt = $db->prepare("SELECT * FROM listings WHERE id = ? AND status = 'approved'");
$stmt->execute([$id]);
$listing = $stmt->fetch();

if (!$listing) {
    die("<div class='container'><h3>Listing not found or not yet approved.</h3></div>");
}

$message = "";

/* ==============================
    BACKEND RECORDING (POST)
============================== */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validate_csrf_token($_POST['csrf_token'])) {
        die('Invalid CSRF token');
    }

    $method = 'pi_network';
    $txid = sanitize_input($_POST['pi_txid']); // This comes from the Pi SDK response
    $user_id = $_SESSION['user_id'];
    $amount_pi = $listing['price_pi'];

    // Insert into payments table
    $stmt = $db->prepare("INSERT INTO payments (user_id, listing_id, payment_method, amount, transaction_reference, payment_status) VALUES (?, ?, ?, ?, ?, 'pending')");
    
    if ($stmt->execute([$user_id, $id, $method, $amount_pi, $txid])) {
        $message = "<div style='background:#d4edda; color:#155724; padding:15px; border-radius:5px; margin:20px 0;'>
                        <strong>Success!</strong> Payment submitted. Pi Transaction ID: $txid. 
                        Our team will verify the GCV rate compliance shortly.
                    </div>";
    } else {
        $message = "<div style='background:#f8d7da; color:#721c24; padding:15px; border-radius:5px; margin:20px 0;'>Payment recording failed.</div>";
    }
}
?>

<div class="container" style="max-width: 600px; margin: 40px auto; background: #fff; padding: 30px; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.1);">
    <h2 style="text-align: center; color: #333;">Complete Your Purchase</h2>
    <hr>
    
    <div style="margin: 20px 0; text-align: center;">
        <p style="font-size: 1.1rem;">Item: <strong><?php echo htmlspecialchars($listing['title']); ?></strong></p>
        <p style="font-size: 1.5rem; color: #ff9800; font-weight: bold;">
            Total: <?php echo htmlspecialchars($listing['price_pi']); ?> Pi
        </p>
        <p style="font-size: 0.8rem; color: #666;">Rate: 1 Pi = 314,159 USD (GCV)</p>
    </div>

    <?php echo $message; ?>

    <div id="pi-payment-section">
        <button id="pay-button" onclick="onPiPaymentRequest()" 
            style="width: 100%; padding: 15px; background: #6d28d9; color: #fff; border: none; border-radius: 8px; font-weight: bold; cursor: pointer; font-size: 1.1rem;">
            Pay with Pi Wallet 🥧
        </button>
    </div>

    <form id="payment-form" method="POST" style="display:none;">
        <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
        <input type="hidden" name="pi_txid" id="pi_txid">
        <input type="hidden" name="payment_method" value="pi">
    </form>
</div>

<script src="https://sdk.minepi.com/pi-sdk.js"></script>
<script>
    // Initialize Pi SDK
    // Pi.init({ version: "2.0", sandbox: true }); 

    async function onPiPaymentRequest() {
        try {
            /* Logic for actual Pi Apps:
               1. Request payment from Pi.createPayment()
               2. User confirms in Pi Wallet
               3. On success, Pi gives you a 'paymentId' or 'txid'
            */
            
            // SIMULATION for your local XAMPP testing:
            const fakeTxId = "pi_tx_" + Math.random().toString(36).substr(2, 9);
            alert("Redirecting to Pi Wallet... (Simulation)");
            
            // On success callback:
            document.getElementById('pi_txid').value = fakeTxId;
            document.getElementById('payment-form').submit();
            
        } catch (err) {
            console.error(err);
            alert("Payment cancelled or failed.");
        }
    }
</script>

<?php include 'includes/footer.php'; ?>