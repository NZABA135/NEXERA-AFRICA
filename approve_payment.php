<?php
include 'includes/db.php'; // Ensure your DB connection is included
session_start();

// Get the JSON data from the Pi SDK frontend
$input = json_decode(file_get_contents('php://input'), true);
$paymentId = $input['paymentId'] ?? '';
$action = $input['action'] ?? ''; // 'approve' or 'complete'

if (!$paymentId || !isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'Invalid request']);
    exit;
}

// Pi API Configuration
$apiKey = "YOUR_PI_API_KEY"; // Get this from the Pi Developer Portal

if ($action === 'approve') {
    // 1. Call Pi API to approve the payment
    $ch = curl_init("https://api.minepi.com/v2/payments/$paymentId/approve");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Authorization: Key $apiKey"
    ]);
    
    $response = curl_exec($ch);
    curl_close($ch);
    
    // 2. Logic to record the pending order in your database
    // $stmt = $db->prepare("INSERT INTO orders (user_id, payment_id, status) VALUES (?, ?, 'pending')");
    // $stmt->execute([$_SESSION['user_id'], $paymentId]);

    echo $response;

} elseif ($action === 'complete') {
    $txid = $input['txid'] ?? '';

    // 1. Call Pi API to complete the payment
    $ch = curl_init("https://api.minepi.com/v2/payments/$paymentId/complete");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['txid' => $txid]));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Authorization: Key $apiKey",
        "Content-Type: application/json"
    ]);

    $response = curl_exec($ch);
    curl_close($ch);

    // 2. Update order to 'paid' in your database
    // $db->prepare("UPDATE orders SET status='paid', txid=? WHERE payment_id=?")->execute([$txid, $paymentId]);

    echo $response;
}
?>