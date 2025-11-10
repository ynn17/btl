<?php
include 'db_connect.php';
header('Content-Type: application/json');
try {
    $stmt = $pdo->query("SELECT id, stock FROM products");
    $stockData = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($stockData);
} catch (Exception $e) {
    error_log("Error in get_stock.php: " . $e->getMessage(), 3, 'D:/xampp/htdocs/electronic-warehouse/php/error.log');
    echo json_encode([]);
}
?>