<?php
session_start();
include 'db_connect.php';
header('Content-Type: application/json');
try {
    if (!isset($_SESSION['user_id'])) {
        echo json_encode(['discount_rate' => 0]);
        exit;
    }
    $stmt = $pdo->prepare("SELECT discount_rate FROM loyalty_program WHERE user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $loyalty = $stmt->fetch(PDO::FETCH_ASSOC);
    echo json_encode(['discount_rate' => $loyalty ? $loyalty['discount_rate'] : 0]);
} catch (Exception $e) {
    error_log("Error in get_loyalty_status.php: " . $e->getMessage(), 3, 'D:/xampp/htdocs/electronic-warehouse/php/error.log');
    echo json_encode(['discount_rate' => 0]);
}
?>