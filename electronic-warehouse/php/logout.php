<?php
session_start();
include 'db_connect.php';
header('Content-Type: application/json');
try {
    session_unset();
    session_destroy();
    echo json_encode(['success' => true, 'message' => 'Đăng xuất thành công']);
} catch (Exception $e) {
    error_log("Error in logout.php: " . $e->getMessage(), 3, 'D:/xampp/htdocs/electronic-warehouse/php/error.log');
    echo json_encode(['success' => false, 'message' => 'Lỗi server: ' . $e->getMessage()]);
}
?>