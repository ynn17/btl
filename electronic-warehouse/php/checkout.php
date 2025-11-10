<?php
session_start();
include 'db_connect.php';
header('Content-Type: application/json');
try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_SESSION['user_id'])) {
        echo json_encode(['success' => false, 'message' => 'Yêu cầu không hợp lệ']);
        exit;
    }
    $user_id = $_SESSION['user_id'];
    $payment_method = $_POST['payment_method'] ?? '';

    $pdo->beginTransaction();
    $stmt = $pdo->prepare("SELECT product_id, product_name, quantity, price FROM cart WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $cartItems = $stmt->fetchAll(PDO::FETCH_ASSOC);
    if (empty($cartItems)) {
        echo json_encode(['success' => false, 'message' => 'Giỏ hàng trống']);
        exit;
    }

    // Kiểm tra tất cả product_id có tồn tại trong products
    $validProductIds = [];
    foreach ($cartItems as $item) {
        $stmt = $pdo->prepare("SELECT id FROM products WHERE id = ?");
        $stmt->execute([$item['product_id']]);
        if ($stmt->fetchColumn() !== false) {
            $validProductIds[] = $item['product_id'];
        } else {
            echo json_encode(['success' => false, 'message' => "Sản phẩm với ID {$item['product_id']} không tồn tại"]);
            exit;
        }
    }

    $total = array_sum(array_map(fn($item) => $item['price'] * $item['quantity'], $cartItems));
    $stmt = $pdo->prepare("INSERT INTO orders (user_id, total, total_price, order_date) VALUES (?, ?, ?, NOW())");
    $stmt->execute([$user_id, count($cartItems), $total]);
    $order_id = $pdo->lastInsertId();

    foreach ($cartItems as $item) {
        $stmt = $pdo->prepare("INSERT INTO order_items (order_id, product_id, product_name, quantity, price) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$order_id, $item['product_id'], $item['product_name'], $item['quantity'], $item['price']]);

        // Giảm số lượng tồn kho trong bảng products
        $stmt = $pdo->prepare("UPDATE products SET stock = stock - ? WHERE id = ? AND stock >= ?");
        $stmt->execute([$item['quantity'], $item['product_id'], $item['quantity']]);
        if ($stmt->rowCount() === 0) {
            throw new Exception("Không đủ tồn kho cho sản phẩm ID {$item['product_id']}");
        }
    }

    $stmt = $pdo->prepare("DELETE FROM cart WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $pdo->commit();

    // Update loyalty program
    $stmt = $pdo->prepare("UPDATE loyalty_program SET order_count = order_count + 1 WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $stmt = $pdo->prepare("UPDATE loyalty_program SET discount_rate = GREATEST(discount_rate, LEAST(10.00, order_count * 1.00)) WHERE user_id = ?");
    $stmt->execute([$user_id]);

    echo json_encode(['success' => true, 'message' => 'Đơn hàng đã được đặt thành công!']);
} catch (Exception $e) {
    $pdo->rollBack();
    error_log("Error in checkout.php: " . $e->getMessage(), 3, 'D:/xampp/htdocs/electronic-warehouse/php/error.log');
    echo json_encode(['success' => false, 'message' => 'Lỗi server: ' . $e->getMessage()]);
}
?>