<?php
header('Content-Type: application/json');

try {
    // Kết nối cơ sở dữ liệu với thông tin thực tế
    $conn = new mysqli("localhost", "root", "", "electronic_warehouse");
    if ($conn->connect_error) {
        throw new Exception("Kết nối thất bại: " . $conn->connect_error);
    }

    // Lấy và kiểm tra input
    $user_id = isset($_POST['user_id']) ? (int)$_POST['user_id'] : 0;
    $product_id = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;
    $quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 1;
    $product_name = isset($_POST['product_name']) ? $conn->real_escape_string($_POST['product_name']) : 'Unknown';

    if ($user_id <= 0 || $product_id <= 0 || $quantity <= 0) {
        throw new Exception("Dữ liệu không hợp lệ");
    }

    // Thêm vào giỏ hàng
    $sql = "INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE quantity = quantity + ?";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        throw new Exception("Lỗi chuẩn bị câu lệnh: " . $conn->error);
    }
    $stmt->bind_param("iiii", $user_id, $product_id, $quantity, $quantity);
    $stmt->execute();

    // Lấy thông tin sản phẩm và tồn kho
    $stock_sql = "SELECT stock, category FROM products WHERE id = ?";
    $stock_stmt = $conn->prepare($stock_sql);
    if (!$stock_stmt) {
        throw new Exception("Lỗi chuẩn bị câu lệnh stock: " . $conn->error);
    }
    $stock_stmt->bind_param("i", $product_id);
    $stock_stmt->execute();
    $stock_result = $stock_stmt->get_result();
    $product = $stock_result->fetch_assoc();
    $stock = $product ? $product['stock'] : 0;

    // Đề xuất linh kiện liên quan (giả sử cùng danh mục)
    $suggestions = [];
    if ($product && $product['category']) {
        $suggest_sql = "SELECT name FROM products WHERE category = ? AND id != ? LIMIT 2";
        $suggest_stmt = $conn->prepare($suggest_sql);
        if (!$suggest_stmt) {
            throw new Exception("Lỗi chuẩn bị câu lệnh suggest: " . $conn->error);
        }
        $suggest_stmt->bind_param("si", $product['category'], $product_id);
        $suggest_stmt->execute();
        $suggest_result = $suggest_stmt->get_result();
        while ($row = $suggest_result->fetch_assoc()) {
            $suggestions[] = $row['name'];
        }
    }

    echo json_encode([
        "success" => true,
        "message" => "Đã thêm $product_name vào giỏ hàng!",
        "stock" => $stock,
        "product_name" => $product_name,
        "suggestions" => $suggestions
    ]);

} catch (Exception $e) {
    error_log("Error in cart_add.php: " . $e->getMessage(), 3, 'D:/xampp/htdocs/electronic-warehouse/php/error.log');
    echo json_encode([
        "success" => false,
        "message" => "Có lỗi xảy ra. Vui lòng thử lại: " . $e->getMessage()
    ]);
} finally {
    if (isset($conn) && $conn instanceof mysqli) {
        $conn->close();
    }
}
?>