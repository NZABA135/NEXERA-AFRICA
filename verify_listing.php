<?php
session_start();
require 'config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

$id = (int)$_GET['id'];

$stmt = $db->prepare("
    UPDATE listings 
    SET verified = 1,
        verified_by = ?,
        verified_at = NOW()
    WHERE id = ?
");
$stmt->execute([$_SESSION['user_id'], $id]);

header("Location: admin_dashboard.php");
exit;