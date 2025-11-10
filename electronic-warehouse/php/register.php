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
    $password = password_hash($_POST['password'] ?? '', PASSWORD_DEFAULT);
    $company_name = $_POST['companyName'] ?? '';
    if (empty($email) || empty($password) || empty($company_name)) {
        echo json_encode(['success' => false, 'message' => 'Vui lòng nhập đầy đủ thông tin']);
        exit;
    }
    $stmt = $pdo->prepare("INSERT INTO users (email, password, company_name) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE password=VALUES(password), company_name=VALUES(company_name)");
    $stmt->execute([$email, $password, $company_name]);
    $user_id = $pdo->lastInsertId() ?: $pdo->query("SELECT id FROM users WHERE email = '$email'")->fetchColumn();
    $_SESSION['user_id'] = $user_id;
    $_SESSION['user_email'] = $email;
    $_SESSION['company_name'] = $company_name;
    echo json_encode(['success' => true, 'message' => 'Đăng ký thành công']);
} catch (Exception $e) {
    error_log("Error in register.php: " . $e->getMessage(), 3, 'D:/xampp/htdocs/electronic-warehouse/php/error.log');
    echo json_encode(['success' => false, 'message' => 'Lỗi server: ' . $e->getMessage()]);
}
?>