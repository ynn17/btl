<?php
header('Content-Type: application/json');
$data = json_decode(file_get_contents('php://input'), true);
if (json_last_error() !== JSON_ERROR_NONE || !is_array($data)) {
    throw new Exception("Dữ liệu JSON không hợp lệ");
}

try {
    $conn = new mysqli("localhost", "root", "", "electronic_warehouse");
    if ($conn->connect_error) {
        throw new Exception("Kết nối thất bại: " . $conn->connect_error);
    }

    $id = isset($data['id']) ? (int)$data['id'] : 0;
    $status = isset($data['status']) ? $conn->real_escape_string($data['status']) : '';
    if ($id <= 0 || empty($status)) {
        throw new Exception("ID hoặc trạng thái không hợp lệ");
    }
    $stmt = $conn->prepare("INSERT INTO order_status_log (order_id, status) VALUES (?, ?) ON DUPLICATE KEY UPDATE status = ?");
    $stmt->bind_param("iss", $id, $status, $status);
    if (!$stmt->execute()) {
        throw new Exception("Thực thi câu lệnh thất bại: " . $conn->error);
    }
    echo json_encode(['success' => true, 'message' => 'Trạng thái đơn hàng được cập nhật!']);
} catch (Exception $e) {
    error_log("Error in update_order.php: " . $e->getMessage(), 3, 'D:/xampp/htdocs/electronic-warehouse/admin/error.log');
    echo json_encode(['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()]);
} finally {
    if (isset($conn) && $conn instanceof mysqli) {
        $conn->close();
    }
}
?>