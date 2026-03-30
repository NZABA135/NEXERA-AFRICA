<?php
session_start();
require 'config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

$id = (int)$_GET['id'];

$stmt = $db->prepare("DELETE FROM listings WHERE id = ?");
$stmt->execute([$id]);

header("Location: admin_dashboard.php");
exit;