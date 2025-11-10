<?php
include 'db_connect.php';
header('Content-Type: application/json');

try {
    $productId = $_GET['product_id'] ?? 0;
    $quantity = (int)($_GET['quantity'] ?? 1);

    $stmt = $pdo->prepare("SELECT price FROM price_tiers WHERE product_id = ? AND ? BETWEEN min_quantity AND max_quantity ORDER BY min_quantity DESC LIMIT 1");
    $stmt->execute([$productId, $quantity]);
    $tierPrice = $stmt->fetchColumn();

    if ($tierPrice !== false) {
        echo json_encode(['price' => floatval($tierPrice)]);
    } else {
        $stmt = $pdo->prepare("SELECT price FROM products WHERE id = ?");
        $stmt->execute([$productId]);
        $basePrice = $stmt->fetchColumn();
        echo json_encode(['price' => floatval($basePrice)]);
    }
} catch (Exception $e) {
    error_log("Error in get_price.php: " . $e->getMessage(), 3, 'D:/xampp/htdocs/electronic-warehouse/php/error.log');
    echo json_encode(['price' => null]);
}
?>