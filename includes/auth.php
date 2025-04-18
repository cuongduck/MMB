<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start([
        'cookie_lifetime' => 30 * 24 * 60 * 60, // 30 days
        'cookie_secure' => true,
        'cookie_httponly' => true,
        'cookie_samesite' => 'Lax'
    ]);
}

function isLoggedIn() {
    if (isset($_SESSION['user_id'])) {
        loadUserRoles(); // Đảm bảo thông tin quyền được tải
        return true;
    }
    
    if (isset($_COOKIE['remember_token'])) {
        global $conn;
        $token = $_COOKIE['remember_token'];
        
        $sql = "SELECT id, username, Group_member, Brandy FROM users 
                WHERE remember_token = ? 
                AND remember_token IS NOT NULL 
                AND remember_token != ''";
                
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $token);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($user = $result->fetch_assoc()) {
            // Cập nhật last_login khi user đăng nhập qua remember token
            $update_login = "UPDATE users SET last_login = NOW() WHERE id = ?";
            $update_stmt = $conn->prepare($update_login);
            $update_stmt->bind_param("i", $user['id']);
            $update_stmt->execute();
            
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['group_member'] = $user['Group_member'];
            $_SESSION['brandy'] = $user['Brandy'];
            
            // Tạo token mới sau mỗi lần đăng nhập thành công
            setRememberMe($user['id']);
            return true;
        }
        
        clearRememberMe();
    }
    
    return false;
}

// Thêm hàm mới để đảm bảo thông tin quyền luôn được tải
function loadUserRoles() {
    if (!isset($_SESSION['group_member']) && isset($_SESSION['user_id'])) {
        global $conn;
        $user_id = $_SESSION['user_id'];
        
        $sql = "SELECT username, Group_member, Brandy FROM users WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($user = $result->fetch_assoc()) {
            $_SESSION['username'] = $user['username'];
            $_SESSION['group_member'] = $user['Group_member'];
            $_SESSION['brandy'] = $user['Brandy'];
        }
    }
}

function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: login.php');
        exit();
    }
}

function setRememberMe($userId) {
    global $conn;
    
    // Tạo token bảo mật
    $token = bin2hex(random_bytes(32));
    
    // Cập nhật database với thời gian đăng nhập
    $sql = "UPDATE users SET remember_token = ?, last_login = NOW() WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $token, $userId);
    
    if ($stmt->execute()) {
        // Thiết lập cookie với các tham số bảo mật HTTPS
        setcookie('remember_token', $token, [
            'expires' => time() + (30 * 24 * 60 * 60), // 30 ngày
            'path' => '/',
            'domain' => $_SERVER['HTTP_HOST'],
            'secure' => true,
            'httponly' => true,
            'samesite' => 'Lax'
        ]);
        return true;
    }
    return false;
}

function clearRememberMe() {
    global $conn;
    
    if (isset($_COOKIE['remember_token'])) {
        $token = $_COOKIE['remember_token'];
        
        // Xóa token trong database
        $sql = "UPDATE users SET remember_token = NULL WHERE remember_token = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $token);
        $stmt->execute();
        
        // Xóa cookie
        setcookie('remember_token', '', [
            'expires' => time() - 3600,
            'path' => '/',
            'domain' => $_SERVER['HTTP_HOST'],
            'secure' => true,
            'httponly' => true,
            'samesite' => 'Lax'
        ]);
    }
}

function logout() {
    clearRememberMe();
    session_destroy();
    header('Location: login.php');
    exit();
}

function isAdmin() {
    // Đảm bảo thông tin quyền được tải
    loadUserRoles();
    
    // Kiểm tra cả hai điều kiện: username là 'admin' HOẶC group_member là 'Full_Control'
    if ((isset($_SESSION['username']) && $_SESSION['username'] === 'admin') || 
        (isset($_SESSION['group_member']) && $_SESSION['group_member'] === 'Full_Control')) {
        return true;
    }
    return false;
}
?>