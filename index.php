<?php
require_once 'includes/auth.php';
require_once 'config/database.php';
requireLogin();
include 'includes/header.php';

// Lấy factory từ parameter URL
$factory = isset($_GET['factory']) ? $_GET['factory'] : 'MMB'; // Giữ giá trị mặc định là 'MMB'

// Kiểm tra route
$page = isset($_GET['page']) ? $_GET['page'] : 'factory';

// Load trang tương ứng
switch($page) {
    case 'production_plan':
        include 'pages/production_plan.php';
        break;
    case 'line_details':
        include 'pages/line_details.php';
        break;
    case 'factory':
        // Sử dụng cấu trúc switch-case để đơn giản hóa code
        switch($factory) {
            case 'F3':
                include 'pages/factory_F3.php';
                break;
            case 'F2':
                include 'pages/factory_F2.php';
                break;
            case 'FS':
                include 'pages/factory_FS.php';
                break;
            case 'CSD':
                include 'pages/factory_CSD.php';
                break;
            case 'MMB':
                include 'pages/factory.php';
                break;
            default:
                include 'pages/factory.php'; // Mặc định nếu factory không hợp lệ
                break;
        }
        break;
    default:
        // Xử lý các case khác hoặc mặc định là factory
        switch($factory) {
            case 'F3':
                include 'pages/factory_F3.php';
                break;
            case 'F2':
                include 'pages/factory_F2.php';
                break;
            case 'FS':
                include 'pages/factory_FS.php';
                break;
            case 'CSD':
                include 'pages/factory_CSD.php';
                break;
            case 'MMB':
                include 'pages/factory.php';
                break;
            default:
                include 'pages/factory.php'; // Mặc định nếu factory không hợp lệ
                break;
        }
        break;
}
?>