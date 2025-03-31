<?php
// File: database_F1.php
define('DB_HOST_F1', 'localhost'); // Đổi tên biến để tránh xung đột
define('DB_USER_F1', 'xxx');
define('DB_PASS_F1', 'xxx');
define('DB_NAME_F1', 'pnlekoychosting_fs');

$conn_F1 = new mysqli(DB_HOST_F1, DB_USER_F1, DB_PASS_F1, DB_NAME_F1);

if ($conn_F1->connect_error) {
    die("Connection failed: " . $conn_F1->connect_error);
}

$conn_F1->set_charset("utf8mb4");
?>