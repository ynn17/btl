<?php
include 'db_connect.php';
header('Content-Type: application/json');

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $productId = $_POST['product_id'] ?? 0;
        $quantity = (int)($_POST['quantity'] ?? 1);
        $userId = $_POST['user_id'] ?? 0;
        $productName = $_POST['product_name'] ?? 'Unknown';

        // Lưu yêu cầu báo giá vào bảng quotes (giả sử bảng có các cột: id, user_id, product_id, quantity, status, created_at)
        $stmt = $pdo->prepare("INSERT INTO quotes (user_id, product_id, quantity, status, created_at) VALUES (?, ?, ?, 'pending', NOW())");
        $stmt->execute([$userId, $productId, $quantity]);

        echo json_encode(['success' => true, 'message' => 'Yêu cầu báo giá đã được gửi. Chúng tôi sẽ liên hệ sớm!']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Phương thức không hợp lệ']);
    }
} catch (Exception $e) {
    error_log("Error in request_quote.php: " . $e->getMessage(), 3, 'D:/xampp/htdocs/electronic-warehouse/php/error.log');
    echo json_encode(['success' => false, 'message' => 'Có lỗi xảy ra. Vui lòng thử lại sau.']);
}
?>