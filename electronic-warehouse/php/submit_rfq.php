<?php
session_start();
include 'db_connect.php';
header('Content-Type: application/json');
try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        echo json_encode(['success' => false, 'message' => 'Phương thức không hợp lệ']);
        exit;
    }
    if (!isset($_SESSION['user_id'])) {
        echo json_encode(['success' => false, 'message' => 'Vui lòng đăng nhập để gửi yêu cầu báo giá']);
        exit;
    }
    $user_id = $_SESSION['user_id'];
    $company_name = $_POST['companyName'] ?? '';
    $email = $_POST['email'] ?? '';
    $delivery_time = $_POST['deliveryTime'] ?? '';
    $requirements = $_POST['requirements'] ?? '';
    $components = $_POST['components'] ?? [];
    $quantities = $_POST['quantities'] ?? [];
    if (empty($company_name) || empty($email) || empty($delivery_time) || empty($components) || empty($quantities) || count($components) !== count($quantities)) {
        echo json_encode(['success' => false, 'message' => 'Vui lòng nhập đầy đủ thông tin hợp lệ']);
        exit;
    }
    $pdo->beginTransaction();
    $stmt = $pdo->prepare("INSERT INTO rfq (user_id, company_name, email, delivery_time, requirements, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
    $stmt->execute([$user_id, $company_name, $email, $delivery_time, $requirements]);
    $rfq_id = $pdo->lastInsertId();
    $stmt = $pdo->prepare("INSERT INTO rfq_items (rfq_id, component_name, quantity) VALUES (?, ?, ?)");
    for ($i = 0; $i < count($components); $i++) {
        if (!empty($components[$i]) && $quantities[$i] > 0) {
            $stmt->execute([$rfq_id, $components[$i], $quantities[$i]]);
        }
    }
    $pdo->commit();
    echo json_encode(['success' => true, 'message' => 'Yêu cầu báo giá đã được gửi thành công']);
} catch (Exception $e) {
    $pdo->rollBack();
    error_log("Error in submit_rfq.php: " . $e->getMessage(), 3, 'D:/xampp/htdocs/electronic-warehouse/php/error.log');
    echo json_encode(['success' => false, 'message' => 'Lỗi server: ' . $e->getMessage()]);
}
?>