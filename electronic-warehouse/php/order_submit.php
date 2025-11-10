<?php
session_start();
include 'db_connect.php';
header('Content-Type: application/json');
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Vui lòng đăng nhập!']);
    exit;
}
$data = json_decode(file_get_contents('php://input'), true);
$user_id = $_SESSION['user_id'];

// Check cart
$stmt = $pdo->prepare("SELECT c.*, p.stock FROM cart c JOIN products p ON c.product_id = p.id WHERE c.user_id = ?");
$stmt->execute([$user_id]);
$cart = $stmt->fetchAll(PDO::FETCH_ASSOC);
if (empty($cart)) {
    echo json_encode(['success' => false, 'message' => 'Giỏ hàng trống!']);
    exit;
}

// Check stock for all items
foreach ($cart as $item) {
    if ($item['stock'] < $item['quantity']) {
        echo json_encode(['success' => false, 'message' => "Sản phẩm {$item['product_name']} không đủ tồn kho!"]);
        exit;
    }
}

// Create order
try {
    $pdo->beginTransaction();
    $total_price = array_sum(array_map(fn($item) => $item['quantity'] * $item['price'], $cart));
    $stmt = $pdo->prepare("INSERT INTO orders (user_id, total, total_price) VALUES (?, ?, ?)");
    $total = array_sum(array_map(fn($item) => $item['quantity'], $cart));
    $stmt->execute([$user_id, $total, $total_price]);
    $order_id = $pdo->lastInsertId();

    // Insert order items
    $stmt = $pdo->prepare("INSERT INTO order_items (order_id, product_id, product_name, quantity, price) VALUES (?, ?, ?, ?, ?)");
    foreach ($cart as $item) {
        $stmt->execute([$order_id, $item['product_id'], $item['product_name'], $item['quantity'], $item['price']]);
        // Update stock
        $stmt_update = $pdo->prepare("UPDATE products SET stock = stock - ? WHERE id = ?");
        $stmt_update->execute([$item['quantity'], $item['product_id']]);
    }

    // Update loyalty program
    $stmt = $pdo->prepare("UPDATE loyalty_program SET order_count = order_count + 1 WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $stmt = $pdo->prepare("UPDATE loyalty_program SET discount_rate = ? WHERE user_id = ? AND order_count >= 5");
    $stmt->execute([5.00, $user_id]); // 5% discount for 5+ orders

    // Clear cart
    $stmt = $pdo->prepare("DELETE FROM cart WHERE user_id = ?");
    $stmt->execute([$user_id]);

    $pdo->commit();
    echo json_encode(['success' => true, 'message' => 'Đặt hàng thành công!']);
} catch (Exception $e) {
    $pdo->rollBack();
    echo json_encode(['success' => false, 'message' => 'Lỗi khi đặt hàng: ' . $e->getMessage()]);
}
?>