<?php
header('Content-Type: application/json');
try {
    $conn = new mysqli("localhost", "root", "", "electronic_warehouse");
    if ($conn->connect_error) {
        throw new Exception("Kết nối thất bại: " . $conn->connect_error);
    }

    $products = (int)$conn->query("SELECT COUNT(*) as count FROM products")->fetch_assoc()['count'];
    $users = (int)$conn->query("SELECT COUNT(*) as count FROM users")->fetch_assoc()['count'];
    $orders = (int)$conn->query("SELECT COUNT(*) as count FROM orders")->fetch_assoc()['count'];
    $revenue = (float)$conn->query("SELECT COALESCE(SUM(oi.price * oi.quantity), 0) as revenue FROM order_items oi JOIN orders o ON oi.order_id = o.id")->fetch_assoc()['revenue'];

    error_log("Dashboard data - Products: $products, Users: $users, Orders: $orders, Revenue: $revenue", 3, 'D:/xampp/htdocs/electronic-warehouse/php/error.log');
    echo json_encode(['products' => $products, 'users' => $users, 'orders' => $orders, 'revenue' => $revenue]);
} catch (Exception $e) {
    error_log("Error in get_dashboard.php: " . $e->getMessage(), 3, 'D:/xampp/htdocs/electronic-warehouse/php/error.log');
    echo json_encode(['error' => $e->getMessage()]);
} finally {
    if (isset($conn) && $conn instanceof mysqli) {
        $conn->close();
    }
}
?>