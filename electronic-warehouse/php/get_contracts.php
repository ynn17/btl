<?php
include 'db_connect.php';
header('Content-Type: application/json');
try {
    $user_id = $_GET['user_id'] ?? 0;
    if ($user_id <= 0) {
        echo json_encode([]);
        exit;
    }
    $stmt = $pdo->prepare("SELECT id, components AS details FROM rfq WHERE user_id = ?");
    $stmt->execute([$user_id]);
    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
} catch (Exception $e) {
    error_log("Error in get_contracts.php: " . $e->getMessage(), 3, 'D:/xampp/htdocs/electronic-warehouse/php/error.log');
    echo json_encode([]);
}
?>