<?php
define('DB_HOST', 'localhost');
define('DB_USER', 'psss');
define('DB_PASS', 'Mass');
define('DB_NAME', 'pnless');

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$conn->set_charset("utf8mb4");
?>