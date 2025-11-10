<?php
    include 'db_connect.php';
    header('Content-Type: application/json');
    try {
        $user_id = $_GET['user_id'] ?? 0;
        if ($user_id <= 0) {
            echo json_encode([]);
            exit;
        }
        $stmt = $pdo->prepare("SELECT oi.product_name, oi.quantity, oi.price FROM order_items oi JOIN orders o ON oi.order_id = o.id WHERE o.user_id = ?");
        $stmt->execute([$user_id]);
        $history = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($history as &$item) {
            $item['price'] = floatval($item['price']); // Chuyển đổi thành số
        }
        echo json_encode($history);
    } catch (Exception $e) {
        error_log("Error in get_purchase_history.php: " . $e->getMessage(), 3, 'D:/xampp/htdocs/electronic-warehouse/php/error.log');
        echo json_encode([]);
    }
    ?>