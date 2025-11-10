<?php
header('Content-Type: application/json');
try {
    $conn = new mysqli("localhost", "root", "", "electronic_warehouse");
    if ($conn->connect_error) {
        throw new Exception("Kết nối thất bại: " . $conn->connect_error);
    }
    $result = $conn->query("SELECT id, email, company_name FROM users");
    if ($result === false) {
        throw new Exception("Truy vấn thất bại: " . $conn->error);
    }
    $users = $result->fetch_all(MYSQLI_ASSOC) ?: [];
    error_log("User list count: " . count($users), 3, 'D:/xampp/htdocs/electronic-warehouse/php/error.log');
    echo json_encode($users);
} catch (Exception $e) {
    error_log("Error in get_user_list.php: " . $e->getMessage(), 3, 'D:/xampp/htdocs/electronic-warehouse/php/error.log');
    echo json_encode([]);
} finally {
    if (isset($conn) && $conn instanceof mysqli) {
        $conn->close();
    }
}
?>