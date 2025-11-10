<?php
    include 'db_connect.php';
    header('Content-Type: application/json');
    try {
        $stmt = $pdo->query("SELECT p.id, p.name, p.category, p.description, p.image, p.stock, p.price FROM products p");
        $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($products as &$product) {
            $stmt = $pdo->prepare("SELECT price FROM price_tiers WHERE product_id = ? AND ? BETWEEN min_quantity AND max_quantity LIMIT 1");
            $stmt->execute([$product['id'], 1]); // Default quantity 1 for base price
            $tierPrice = $stmt->fetchColumn();
            if ($tierPrice !== false) {
                $product['price'] = floatval($tierPrice);
            } else {
                $product['price'] = floatval($product['price']);
            }
        }
        echo json_encode($products);
    } catch (Exception $e) {
        error_log("Error in get_products.php: " . $e->getMessage(), 3, 'D:/xampp/htdocs/electronic-warehouse/php/error.log');
        echo json_encode([]);
    }
    ?>