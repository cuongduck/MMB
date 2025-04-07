<?php
require_once 'includes/auth.php';
require_once 'config/database.php';
requireLogin();

// Xác định factory mặc định dựa trên Brandy của người dùng
$defaultFactory = 'MMB'; // Mặc định
if (isset($_SESSION['brandy'])) {
    switch ($_SESSION['brandy']) {
        case 'Noodle':
            $defaultFactory = 'F2';
            break;
        case 'ED':
            $defaultFactory = 'CSD';
            break;
        default:
            $defaultFactory = 'MMB';
            break;
    }
}

// Lấy factory từ parameter URL, nếu không có thì dùng mặc định
$factory = isset($_GET['factory']) ? $_GET['factory'] : $defaultFactory;

// Kiểm tra quyền truy cập vào factory
$hasAccess = true;

if (isset($_SESSION['brandy']) && $_SESSION['brandy'] != 'Chung' && !isAdmin()) {
    // Người dùng Noodle chỉ được xem F2, F3
    if ($_SESSION['brandy'] == 'Noodle' && !in_array($factory, ['F2', 'F3'])) {
        $hasAccess = false;
        $factory = 'F2'; // Chuyển hướng về F2
    }
    // Người dùng ED chỉ được xem CSD, FS
    elseif ($_SESSION['brandy'] == 'ED' && !in_array($factory, ['CSD', 'FS'])) {
        $hasAccess = false;
        $factory = 'CSD'; // Chuyển hướng về CSD
    }
}

// Kiểm tra route
$page = isset($_GET['page']) ? $_GET['page'] : 'factory';

// Include header.php
include 'includes/header.php';

// Load trang tương ứng
switch($page) {
    case 'production_plan':
        include 'pages/production_plan.php';
        break;
    case 'line_product_management': // Thêm case này
        include 'pages/line_product_management.php';
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