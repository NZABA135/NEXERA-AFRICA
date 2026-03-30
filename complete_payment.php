<?php
// api/complete_payment.php
header('Content-Type: application/json');
session_start();
include '../includes/db.php';

$data = json_decode(file_get_contents('php://input'), true);
$paymentId = $data['paymentId'] ?? null;
$txid = $data['txid'] ?? null;
$pi_api_key = 'YOUR_PI_APP_SECRET_KEY';

if (!$paymentId || !$txid) {
    echo json_encode(['error' => 'Missing payment data']);
    exit;
}

// 1. Complete the payment via Pi Network API
$ch = curl_init("https://api.minepi.com/v2/payments/$paymentId/complete");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['txid' => $txid]));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Authorization: Key $pi_api_key",
    "Content-Type: application/json"
]);

$response = curl_exec($ch);
$result = json_decode($response, true);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($http_code == 200) {
    // 2. SUCCESS: Update your database
    // Extract metadata you sent from the frontend (listingId)
    $listingId = $result['metadata']['listingId'] ?? 0;
    $userId = $_SESSION['user_id'];
    $amount = $result['amount'];

    try {
        $stmt = $db->prepare("INSERT INTO orders (user_id, listing_id, pi_payment_id, txid, amount, status) VALUES (?, ?, ?, ?, ?, 'paid')");
        $stmt->execute([$userId, $listingId, $paymentId, $txid, $amount]);
        
        echo json_encode(['status' => 'completed', 'message' => 'Order placed successfully!']);
    } catch (Exception $e) {
        echo json_encode(['error' => 'Payment complete but DB update failed', 'details' => $e->getMessage()]);
    }
} else {
    echo json_encode(['error' => 'Completion failed', 'details' => $response]);
}
?>