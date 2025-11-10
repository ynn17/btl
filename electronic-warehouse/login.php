<?php
    session_start();
    $_SESSION['user'] = [
        'id' => 1,
        'email' => 'admin@example.com',
        'company_name' => 'Admin Company'
    ];
    header('Location: admin.html');
    exit;
    ?>