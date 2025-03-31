<?php
define('DB_HOST', 'localhost');
define('DB_USER', 'xxx');
define('DB_PASS', 'xxx');
define('DB_NAME', 'pnlekoychosting_Report');

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$conn->set_charset("utf8mb4");
?>