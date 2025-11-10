<?php
header('Content-Type: application/json');
try {
    $conn = new mysqli("localhost", "root", "", "electronic_warehouse");
    if ($conn->connect_error) {
        throw new Exception("Kết nối thất bại: " . $conn->connect_error);
    }
    $result = $conn->query("SELECT o.id, u.email AS user_email, o.order_date, o.total, 
                           COALESCE((SELECT SUM(oi.price * oi.quantity) FROM order_items oi WHERE oi.order_id = o.id), 0) AS total_price
                           FROM orders o 
                           JOIN users u ON o.user_id = u.id");
    $orders = $result->fetch_all(MYSQLI_ASSOC) ?: [];
    foreach ($orders as &$order) {
        $items = $conn->query("SELECT product_name, quantity, CAST(price AS DECIMAL(10,2)) as price FROM order_items WHERE order_id = " . $order['id']);
        $order['items'] = $items->fetch_all(MYSQLI_ASSOC) ?: [];
    }
    error_log("Order list count: " . count($orders), 3, 'D:/xampp/htdocs/electronic-warehouse/php/error.log');
    echo json_encode($orders);
} catch (Exception $e) {
    error_log("Error in get_order_list.php: " . $e->getMessage(), 3, 'D:/xampp/htdocs/electronic-warehouse/php/error.log');
    echo json_encode([]);
} finally {
    if (isset($conn) && $conn instanceof mysqli) {
        $conn->close();
    }
}
?>