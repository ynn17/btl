<?php
session_start();
include 'db_connect.php';
header('Content-Type: application/json');
try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_SESSION['user_id'])) {
        echo json_encode(['success' => false, 'message' => 'Yêu cầu không hợp lệ']);
        exit;
    }
    parse_str(file_get_contents('php://input'), $data);
    $user_id = $_SESSION['user_id'];
    $stmt = $pdo->prepare("DELETE FROM cart WHERE user_id = ?");
    $stmt->execute([$user_id]);
    echo json_encode(['success' => true, 'message' => 'Giỏ hàng đã được xóa']);
} catch (Exception $e) {
    error_log("Error in cart_clear.php: " . $e->getMessage(), 3, 'D:/xampp/htdocs/electronic-warehouse/php/error.log');
    echo json_encode(['success' => false, 'message' => 'Lỗi server: ' . $e->getMessage()]);
}
?>