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
    $email = isset($data['email']) ? $conn->real_escape_string($data['email']) : '';
    $company_name = isset($data['company_name']) ? $conn->real_escape_string($data['company_name']) : '';

    if ($id == 0) {
        echo json_encode(['success' => false, 'message' => 'Thêm người dùng cần mật khẩu, vui lòng sử dụng đăng ký!']);
    } else if (empty($email) || empty($company_name)) {
        throw new Exception("Email và tên công ty không được để trống");
    } else {
        $stmt = $conn->prepare("UPDATE users SET email = ?, company_name = ? WHERE id = ?");
        $stmt->bind_param("ssi", $email, $company_name, $id);
        if (!$stmt->execute()) {
            throw new Exception("Thực thi câu lệnh thất bại: " . $conn->error);
        }
        echo json_encode(['success' => true, 'message' => 'Người dùng được cập nhật thành công!']);
    }
} catch (Exception $e) {
    error_log("Error in update_user.php: " . $e->getMessage(), 3, 'D:/xampp/htdocs/electronic-warehouse/admin/error.log');
    echo json_encode(['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()]);
} finally {
    if (isset($conn) && $conn instanceof mysqli) {
        $conn->close();
    }
}
?>