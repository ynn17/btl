<?php
session_start();
include 'db_connect.php';
header('Content-Type: application/json');
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Vui lòng đăng nhập!']);
    exit;
}
$data = json_decode(file_get_contents('php://input'), true);
$cart_id = $data['cart_id'];
$quantity = $data['quantity'];

// Check stock
$stmt = $pdo->prepare("SELECT c.product_id, p.stock FROM cart c JOIN products p ON c.product_id = p.id WHERE c.id = ?");
$stmt->execute([$cart_id]);
$item = $stmt->fetch();
if (!$item || $item['stock'] < $quantity) {
    echo json_encode(['success' => false, 'message' => 'Sản phẩm không đủ tồn kho!']);
    exit;
}

// Calculate price
$stmt = $pdo->prepare("SELECT p.price, 
       (SELECT JSON_ARRAYAGG(
           JSON_OBJECT(
               'min_quantity', pt.min_quantity,
               'max_quantity', pt.max_quantity,
               'price', pt.price
           )
       ) FROM price_tiers pt WHERE pt.product_id = p.id) as price_tiers
FROM products p WHERE p.id = ?");
$stmt->execute([$item['product_id']]);
$product = $stmt->fetch();
$product['price_tiers'] = json_decode($product['price_tiers'], true) ?? [];
$price = $product['price'];
foreach ($product['price_tiers'] as $tier) {
    if ($quantity >= $tier['min_quantity'] && $quantity <= $tier['max_quantity']) {
        $price = $tier['price'];
        break;
    }
}
// Apply loyalty discount
$stmt = $pdo->prepare("SELECT discount_rate FROM loyalty_program WHERE user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$loyalty = $stmt->fetch();
if ($loyalty && $loyalty['discount_rate'] > 0) {
    $price = $price * (1 - $loyalty['discount_rate'] / 100);
}

// Update cart
$stmt = $pdo->prepare("UPDATE cart SET quantity = ?, price = ? WHERE id = ?");
$success = $stmt->execute([$quantity, $price, $cart_id]);

echo json_encode([
    'success' => $success,
    'message' => $success ? 'Cập nhật số lượng thành công!' : 'Lỗi khi cập nhật số lượng!'
]);
?>