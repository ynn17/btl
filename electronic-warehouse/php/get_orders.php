<?php
    include 'db_connect.php';
    header('Content-Type: application/json');
    try {
        $user_id = $_GET['user_id'] ?? 0;
        if ($user_id <= 0) {
            echo json_encode([]);
            exit;
        }
        $stmt = $pdo->prepare("SELECT id, order_date, total, total_price FROM orders WHERE user_id = ?");
        $stmt->execute([$user_id]);
        $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($orders as &$order) {
            $order['total_price'] = floatval($order['total_price']); // Chuyển đổi thành số
        }
        echo json_encode($orders);
    } catch (Exception $e) {
        error_log("Error in get_orders.php: " . $e->getMessage(), 3, 'D:/xampp/htdocs/electronic-warehouse/php/error.log');
        echo json_encode([]);
    }
    ?>