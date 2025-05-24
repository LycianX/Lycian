<?php
include 'config.php';
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $stmt = $pdo->prepare("INSERT INTO applications (user_id, car_number, description) VALUES (?, ?, ?)");
    $stmt->execute([$_SESSION['user_id'], $_POST['car_number'], $_POST['description']]);
    header("Location: applications.php");
    exit;
}
?>