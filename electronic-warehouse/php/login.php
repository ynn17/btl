<?php
session_start();
include 'db_connect.php';
header('Content-Type: application/json');
try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        echo json_encode(['success' => false, 'message' => 'Phương thức không hợp lệ']);
        exit;
    }
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    if (empty($email) || empty($password)) {
        echo json_encode(['success' => false, 'message' => 'Vui lòng nhập email và mật khẩu']);
        exit;
    }
    $stmt = $pdo->prepare("SELECT id, email, password, company_name, role FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['company_name'] = $user['company_name'];
        $_SESSION['role'] = $user['role']; // Lưu role vào phiên
        echo json_encode(['success' => true, 'message' => 'Đăng nhập thành công']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Email hoặc mật khẩu không đúng']);
    }
} catch (Exception $e) {
    error_log("Error in login.php: " . $e->getMessage(), 3, 'D:/xampp/htdocs/electronic-warehouse/php/error.log');
    echo json_encode(['success' => false, 'message' => 'Lỗi server: ' . $e->getMessage()]);
}
?>