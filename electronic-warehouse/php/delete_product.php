<?php
header('Content-Type: application/json');
$id = $_GET['id'];

try {
    $conn = new mysqli("localhost", "root", "", "electronic_warehouse");
    if ($conn->connect_error) {
        throw new Exception("Kết nối thất bại: " . $conn->connect_error);
    }

    $stmt = $conn->prepare("DELETE FROM products WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    echo json_encode(['success' => true, 'message' => 'Sản phẩm đã được xóa!']);
} catch (Exception $e) {
    error_log("Error in delete_product.php: " . $e->getMessage(), 3, 'D:/xampp/htdocs/electronic-warehouse/admin/error.log');
    echo json_encode(['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()]);
} finally {
    if (isset($conn) && $conn instanceof mysqli) {
        $conn->close();
    }
}
?>