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
    $name = isset($data['name']) ? $conn->real_escape_string($data['name']) : '';
    $price = isset($data['price']) ? floatval($data['price']) : 0.0;
    $stock = isset($data['stock']) ? intval($data['stock']) : 0;
    $category = isset($data['category']) ? $conn->real_escape_string($data['category']) : '';
    $description = isset($data['description']) ? $conn->real_escape_string($data['description']) : '';
    $image = isset($data['image']) ? $conn->real_escape_string($data['image']) : '';

    if ($id == 0) {
        $stmt = $conn->prepare("INSERT INTO products (name, price, stock, category, description, image) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sdssss", $name, $price, $stock, $category, $description, $image);
    } else {
        $stmt = $conn->prepare("UPDATE products SET name = ?, price = ?, stock = ?, category = ?, description = ?, image = ? WHERE id = ?");
        $stmt->bind_param("sdssssi", $name, $price, $stock, $category, $description, $image, $id);
    }
    if (!$stmt->execute()) {
        throw new Exception("Thực thi câu lệnh thất bại: " . $conn->error);
    }
    echo json_encode(['success' => true, 'message' => 'Sản phẩm ' . ($id == 0 ? 'được thêm' : 'được cập nhật') . ' thành công!']);
} catch (Exception $e) {
    error_log("Error in update_product.php: " . $e->getMessage(), 3, 'D:/xampp/htdocs/electronic-warehouse/admin/error.log');
    echo json_encode(['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()]);
} finally {
    if (isset($conn) && $conn instanceof mysqli) {
        $conn->close();
    }
}
?>