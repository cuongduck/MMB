<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard IoT MMB</title>
    <link rel="icon" type="image/x-icon" href="favicon.ico">
    <link rel="apple-touch-icon" href="favicon.png">
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/style.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="assets/css/kansui.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="assets/css/status-card.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="assets/css/speed_trend.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="assets/css/ir_chart.css?v=<?php echo time(); ?>">
    <!--   <link rel="stylesheet" href="assets/css/production_plan.css?v=<?php echo time(); ?>">-->
    <!-- Load all libraries locally - without CDN -->
    <script src="assets/js/tailwindcss.js?v=<?php echo time(); ?>"></script>
    <script src="assets/js/chart.js?v=<?php echo time(); ?>"></script>
    <script src="assets/js/chartjs-plugin-datalabels.js?v=<?php echo time(); ?>"></script>
    <script src="assets/js/chartjs-plugin-annotation.min.js?v=<?php echo time(); ?>"></script>
    <script src="assets/js/chartjs-plugin-zoom.min.js?v=<?php echo time(); ?>"></script>
    <script src="assets/js/moment.min.js?v=<?php echo time(); ?>"></script>
    <script src="assets/js/chartjs-adapter-moment.min.js?v=<?php echo time(); ?>"></script>
    <!-- Các script khác -->
    <script>
        // Thêm function để toggle user menu
        function toggleUserMenu() {
            const userMenu = document.getElementById('userMenu');
            if (userMenu) {
                userMenu.classList.toggle('hidden');
            }
        }
    </script>
</head>
<body>
<!-- Main Header -->
<div class="header-main">
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center gap-3">
                <div class="factory-nav">
                <?php 
                // Xác định Brandy của người dùng
                $userBrandy = isset($_SESSION['brandy']) ? $_SESSION['brandy'] : 'Chung';
                
                // Hiển thị các tab dựa vào Brandy
                if ($userBrandy == 'Chung' || isAdmin()): // Admin và Chung thấy tất cả
                ?>
                    <a href="index.php?factory=MMB" class="factory-btn <?php echo (isset($_GET['factory']) && $_GET['factory'] == 'MMB' || !isset($_GET['factory']) && !isset($_GET['page'])) ? 'active' : ''; ?>">MMB</a>
                    <a href="index.php?factory=F2" class="factory-btn <?php echo (isset($_GET['factory']) && $_GET['factory'] == 'F2') ? 'active' : ''; ?>">Mì_F2</a>
                    <a href="index.php?factory=F3" class="factory-btn <?php echo (isset($_GET['factory']) && $_GET['factory'] == 'F3') ? 'active' : ''; ?>">Mì_F3</a>
                    <a href="index.php?factory=CSD" class="factory-btn <?php echo (isset($_GET['factory']) && $_GET['factory'] == 'CSD') ? 'active' : ''; ?>">CSD</a>
                    <a href="index.php?factory=FS" class="factory-btn <?php echo (isset($_GET['factory']) && $_GET['factory'] == 'FS') ? 'active' : ''; ?>">Mắm</a>
                    <a href="index.php?page=production_plan" class="factory-btn <?php echo isset($_GET['page']) && $_GET['page'] == 'production_plan' ? 'active' : ''; ?>">KHSX</a>
                <?php 
                elseif ($userBrandy == 'Noodle'): // Noodle chỉ thấy F2, F3 và KHSX
                ?>
                    <a href="index.php?factory=F2" class="factory-btn <?php echo (isset($_GET['factory']) && $_GET['factory'] == 'F2' || (!isset($_GET['factory']) && !isset($_GET['page']))) ? 'active' : ''; ?>">Mì_F2</a>
                    <a href="index.php?factory=F3" class="factory-btn <?php echo (isset($_GET['factory']) && $_GET['factory'] == 'F3') ? 'active' : ''; ?>">Mì_F3</a>
                    <a href="index.php?page=production_plan" class="factory-btn <?php echo isset($_GET['page']) && $_GET['page'] == 'production_plan' ? 'active' : ''; ?>">KHSX</a>
                <?php 
                elseif ($userBrandy == 'ED'): // ED chỉ thấy CSD, FS và KHSX
                ?>
                    <a href="index.php?factory=CSD" class="factory-btn <?php echo (isset($_GET['factory']) && $_GET['factory'] == 'CSD' || (!isset($_GET['factory']) && !isset($_GET['page']))) ? 'active' : ''; ?>">CSD</a>
                    <a href="index.php?factory=FS" class="factory-btn <?php echo (isset($_GET['factory']) && $_GET['factory'] == 'FS') ? 'active' : ''; ?>">Mắm</a>
                    <a href="index.php?page=production_plan" class="factory-btn <?php echo isset($_GET['page']) && $_GET['page'] == 'production_plan' ? 'active' : ''; ?>">KHSX</a>
                <?php endif; ?>
                </div>
            </div>
            <div class="d-flex align-items-center gap-3">
                <div class="relative">
                    <button onclick="toggleUserMenu()" class="user-btn flex items-center">
                        <span><?php echo isset($_SESSION['username']) ? htmlspecialchars($_SESSION['username']) : 'User'; ?></span>
                        <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>
                    <div id="userMenu" class="user-menu hidden absolute right-0 mt-2 w-48 bg-white rounded shadow-lg">
                        <a href="change_password.php" class="block px-4 py-2 text-gray-700 hover:bg-gray-100">
                            Đổi mật khẩu
                        </a>
                        <?php if (isAdmin()): ?>
                        <a href="tao_tk.php" class="block px-4 py-2 text-gray-700 hover:bg-gray-100">
                            Tạo tài khoản
                        </a>
                        <?php endif; ?>
                        <a href="logout.php" class="block px-4 py-2 text-gray-700 hover:bg-gray-100">
                            Thoát
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- Sub Header -->
<!-- Thay thế phần date-filter trong file includes/header.php -->
<div class="sub-header">
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center">
            <h2 class="factory-title mb-0">
                <?php
                // Hiển thị tiêu đề dựa trên factory
                if (isset($_GET['factory'])) {
                    switch($_GET['factory']) {
                        case 'MMB':
                            echo 'Nhà Máy MMB';
                            break;
                        case 'F3':
                            echo 'Xưởng Mì F3';
                            break;
                        case 'F2':
                            echo 'Xưởng Mì F2';
                            break;
                        case 'CSD':
                            echo 'Xưởng CSD';
                            break;
                        case 'FS':
                            echo 'Xưởng Mắm';
                            break;
                        default:
                            echo 'Nhà máy MMB';
                    }
                } else if (isset($_GET['page']) && $_GET['page'] == 'production_plan') {
                    echo 'Kế Hoạch Sản Xuất';
                } else {
                    echo 'Nhà máy MMB';
                }
                ?>
            </h2>
            <div class="date-filter d-flex gap-2">
                <?php if (isset($_GET['page']) && $_GET['page'] == 'production_plan'): ?>
                    <!-- Hiển thị nút cho trang Kế hoạch sản xuất -->
                    <a href="?page=production_plan&tab=daily" class="btn <?php echo (!isset($_GET['tab']) || $_GET['tab'] == 'daily') ? 'active' : ''; ?>">Theo Ngày</a>
                    <a href="?page=production_plan&tab=weekly" class="btn <?php echo (isset($_GET['tab']) && $_GET['tab'] == 'weekly') ? 'active' : ''; ?>">Theo Tuần</a>
                    <a href="?page=production_plan&tab=monthly" class="btn <?php echo (isset($_GET['tab']) && $_GET['tab'] == 'monthly') ? 'active' : ''; ?>">Theo Tháng</a>
                <?php else: ?>
                    <!-- Hiển thị nút mặc định cho các trang khác -->
                    <button class="btn active" data-period="today">Hôm nay</button>
                    <button class="btn" data-period="yesterday">Hôm qua</button>
                    <button class="btn" data-period="week">Tuần này</button>
                    <button class="btn" data-period="last_week">Tuần trước</button>
                    <button class="btn" data-period="month">Tháng này</button>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>