<?php
include 'db_connect.php';
header('Content-Type: application/json');
try {
    $product_id = $_GET['id'] ?? 0;
    if ($product_id <= 0) {
        echo json_encode([]);
        exit;
    }
    $stmt = $pdo->prepare("SELECT p.id, p.name, p.description, p.image, p.stock, p.price FROM products p WHERE p.id = ?");
    $stmt->execute([$product_id]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($product) {
        $product['price'] = floatval($product['price']); // Chuyển đổi thành số
    }
    echo json_encode($product ? $product : []);
} catch (Exception $e) {
    error_log("Error in get_product.php: " . $e->getMessage(), 3, 'C:/xampp/htdocs/electronic-warehouse/php/error.log');
    echo json_encode([]);
}
?>