<?php
require_once '../../config/database_F1.php';
require_once '../../includes/auth.php';
header('Content-Type: application/json');

echo json_encode([
    'isAdmin' => isAdmin(),
    'isLoggedIn' => isLoggedIn(),
    'username' => $_SESSION['username'] ?? '',  // Thêm username
    'role' => $_SESSION['role'] ?? ''  // Thêm role
]);
?>