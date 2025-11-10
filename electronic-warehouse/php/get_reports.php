<?php
header('Content-Type: application/json');
try {
    $conn = new mysqli("localhost", "root", "", "electronic_warehouse");
    if ($conn->connect_error) {
        throw new Exception("Kết nối thất bại: " . $conn->connect_error);
    }

    $monthly_revenue = $conn->query("SELECT SUM(total_price) as revenue FROM orders WHERE total_price > 0 AND MONTH(order_date) = MONTH(CURRENT_DATE)")->fetch_assoc()['revenue'] ?: 0;
    $products_sold = $conn->query("SELECT SUM(quantity) as sold FROM order_items")->fetch_assoc()['sold'] ?: 0;

    echo json_encode(['monthly_revenue' => $monthly_revenue, 'products_sold' => $products_sold]);
} catch (Exception $e) {
    error_log("Error in get_reports.php: " . $e->getMessage(), 3, 'D:/xampp/htdocs/electronic-warehouse/admin/error.log');
    echo json_encode(['error' => $e->getMessage()]);
} finally {
    if (isset($conn) && $conn instanceof mysqli) {
        $conn->close();
    }
}
?>