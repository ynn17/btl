<?php
header('Content-Type: application/json');
try {
    $conn = new mysqli("localhost", "root", "", "electronic_warehouse");
    if ($conn->connect_error) {
        throw new Exception("Kết nối thất bại: " . $conn->connect_error);
    }
    $result = $conn->query("SELECT id, name, CAST(price AS DECIMAL(10,2)) as price, stock, category, description, image FROM products");
    if ($result === false) {
        throw new Exception("Truy vấn thất bại: " . $conn->error);
    }
    $products = $result->fetch_all(MYSQLI_ASSOC) ?: [];
    error_log("Product list count: " . count($products), 3, 'D:/xampp/htdocs/electronic-warehouse/php/error.log');
    echo json_encode($products);
} catch (Exception $e) {
    error_log("Error in get_product_list.php: " . $e->getMessage(), 3, 'D:/xampp/htdocs/electronic-warehouse/php/error.log');
    echo json_encode([]);
} finally {
    if (isset($conn) && $conn instanceof mysqli) {
        $conn->close();
    }
}
?>