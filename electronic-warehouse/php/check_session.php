<?php
error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE);
ini_set('display_errors', 0);
header('Content-Type: application/json');
try {
    session_start();
    include 'db_connect.php';
    if (isset($_SESSION['user_id'])) {
        // Lấy role từ phiên (đã được thiết lập trong login.php)
        $role = $_SESSION['role'] ?? 'user';
        $isAdmin = ($role === 'admin');

        echo json_encode([
            'loggedIn' => true,
            'user' => [
                'id' => $_SESSION['user_id'],
                'email' => $_SESSION['user_email'],
                'company_name' => $_SESSION['company_name']
            ],
            'isAdmin' => $isAdmin
        ]);
    } else {
        echo json_encode(['loggedIn' => false]);
    }
} catch (Exception $e) {
    error_log("Error in check_session.php: " . $e->getMessage(), 3, 'D:/xampp/htdocs/electronic-warehouse/php/error.log');
    echo json_encode(['loggedIn' => false, 'error' => 'Lỗi server khi kiểm tra phiên']);
}
?>