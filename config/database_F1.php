<?php
define('DB_HOST', 'localhost');
define('DB_USER', 'pnless');
define('DB_PASS', 'Masss');
define('DB_NAME', 'pnlekoss');

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$conn->set_charset("utf8mb4");
?>