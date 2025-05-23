<?php
require_once 'config/database.php';
require_once 'includes/auth.php';

// Đảm bảo người dùng đã đăng nhập
requireLogin();

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $current_password = trim($_POST['current_password']);
    $new_password = trim($_POST['new_password']);
    $confirm_password = trim($_POST['confirm_password']);
    
    // Validate input
    if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
        $error = 'Vui lòng nhập đầy đủ thông tin';
    } elseif ($new_password !== $confirm_password) {
        $error = 'Mật khẩu mới xác nhận không khớp';
    } elseif (strlen($new_password) < 6) {
        $error = 'Mật khẩu mới phải có ít nhất 6 ký tự';
    } else {
        // Lấy thông tin mật khẩu hiện tại từ database
        $user_id = $_SESSION['user_id'];
        $sql = "SELECT password FROM users WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($user = $result->fetch_assoc()) {
            // Xác thực mật khẩu hiện tại
            if (password_verify($current_password, $user['password'])) {
                // Mật khẩu chính xác, tiến hành cập nhật
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                
                $update_sql = "UPDATE users SET password = ? WHERE id = ?";
                $update_stmt = $conn->prepare($update_sql);
                $update_stmt->bind_param("si", $hashed_password, $user_id);
                
                if ($update_stmt->execute()) {
                    $message = "Đổi mật khẩu thành công!";
                } else {
                    $error = "Lỗi cập nhật mật khẩu: " . $conn->error;
                }
            } else {
                $error = 'Mật khẩu hiện tại không đúng';
            }
        } else {
            $error = 'Không thể xác thực người dùng';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/x-icon" href="favicon.ico">
    <title>Đổi Mật Khẩu - Hệ Thống IoT MMB</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50">
    <div class="min-h-screen flex items-center justify-center">
        <div class="max-w-md w-full space-y-8 p-10 bg-white rounded-xl shadow-lg">
            <div>
                <h2 class="text-center text-3xl font-bold text-gray-900">
                    Đổi Mật Khẩu
                </h2>
                <p class="mt-2 text-center text-sm text-gray-600">
                    Hệ Thống IoT MMB
                </p>
            </div>
            
            <?php if ($error): ?>
                <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4" role="alert">
                    <p><?php echo htmlspecialchars($error); ?></p>
                </div>
            <?php endif; ?>

            <?php if ($message): ?>
                <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4" role="alert">
                    <p><?php echo htmlspecialchars($message); ?></p>
                </div>
            <?php endif; ?>

            <form class="mt-8 space-y-6" method="POST">
                <div class="rounded-md shadow-sm space-y-4">
                    <div>
                        <label for="current_password" class="block text-sm font-medium text-gray-700">
                            Mật khẩu hiện tại
                        </label>
                        <input id="current_password" name="current_password" type="password" required 
                               class="mt-1 block w-full px-3 py-2 bg-white border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                    </div>

                    <div>
                        <label for="new_password" class="block text-sm font-medium text-gray-700">
                            Mật khẩu mới
                        </label>
                        <input id="new_password" name="new_password" type="password" required 
                               class="mt-1 block w-full px-3 py-2 bg-white border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                    </div>

                    <div>
                        <label for="confirm_password" class="block text-sm font-medium text-gray-700">
                            Xác nhận mật khẩu mới
                        </label>
                        <input id="confirm_password" name="confirm_password" type="password" required 
                               class="mt-1 block w-full px-3 py-2 bg-white border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                    </div>
                </div>

                <div class="flex items-center justify-between gap-4">
                    <a href="index.php" 
                       class="flex-1 py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 text-center">
                        Quay lại
                    </a>
                    <button type="submit" 
                            class="flex-1 py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        Cập nhật
                    </button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>