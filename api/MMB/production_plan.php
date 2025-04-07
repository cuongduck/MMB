<?php
// api/MMB/production_plan.php
require_once '../../config/database.php';
require_once '../../includes/auth.php';

// Đảm bảo session đã được khởi tạo
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Kiểm tra đăng nhập
if (!isset($_SESSION['user_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['status' => 'error', 'message' => 'Phiên đăng nhập hết hạn']);
    exit;
}

// Xử lý các yêu cầu API
$action = isset($_GET['action']) ? $_GET['action'] : '';

switch ($action) {
    case 'get_lines':
        getProductionLines();
        break;
    case 'get_products':
        getProducts();
        break;
    case 'get_plans':
        getProductionPlans();
        break;
    case 'check_overlap':
        checkTimeOverlap();
        break;
    case 'add_plan':
        if (isAdmin()) {
            addProductionPlan();
        } else {
            header('Content-Type: application/json');
            echo json_encode(['status' => 'error', 'message' => 'Không có quyền thêm kế hoạch']);
        }
        break;
    case 'update_plan':
        if (isAdmin()) {
            updateProductionPlan();
        } else {
            header('Content-Type: application/json');
            echo json_encode(['status' => 'error', 'message' => 'Không có quyền cập nhật kế hoạch']);
        }
        break;
    case 'delete_plan':
        if (isAdmin()) {
            deleteProductionPlan();
        } else {
            header('Content-Type: application/json');
            echo json_encode(['status' => 'error', 'message' => 'Không có quyền xóa kế hoạch']);
        }
        break;
    default:
        header('Content-Type: application/json');
        echo json_encode(['status' => 'error', 'message' => 'Hành động không hợp lệ']);
        break;
}

// Lấy danh sách các line sản xuất
function getProductionLines() {
    global $conn;
    
    $query = "SELECT id, line_name, line_code, factory FROM production_lines ORDER BY factory, line_name";
    $result = $conn->query($query);
    
    $lines = [];
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $lines[] = $row;
        }
    }
    
    header('Content-Type: application/json');
    echo json_encode(['status' => 'success', 'data' => $lines]);
}

// Lấy danh sách sản phẩm
function getProducts() {
    global $conn;
    
    $query = "SELECT id, product_name, product_code, product_group, color_code FROM products ORDER BY product_group, product_name";
    $result = $conn->query($query);
    
    $products = [];
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $products[] = $row;
        }
    }
    
    header('Content-Type: application/json');
    echo json_encode(['status' => 'success', 'data' => $products]);
}

// Lấy kế hoạch sản xuất theo bộ lọc
function getProductionPlans() {
    global $conn;
    
    $filter_type = isset($_GET['filter_type']) ? $_GET['filter_type'] : 'daily';
    $filter_value = isset($_GET['filter_value']) ? $_GET['filter_value'] : date('Y-m-d');
    $factory_filter = isset($_GET['factory_filter']) ? $_GET['factory_filter'] : 'all';
    
    // Xây dựng điều kiện WHERE tùy theo loại bộ lọc thời gian
    $where_clause = "";
    
    switch ($filter_type) {
        case 'daily':
            $date = $conn->real_escape_string($filter_value);
            $where_clause = "DATE(p.start_time) = '$date' OR DATE(p.end_time) = '$date' OR ('$date' BETWEEN DATE(p.start_time) AND DATE(p.end_time))";
            break;
        case 'weekly':
            // Format: YYYY-Www (e.g., 2025-W14)
            if (preg_match('/^(\d{4})-W(\d{1,2})$/', $filter_value, $matches)) {
                $year = $matches[1];
                $week = $matches[2];
                $where_clause = "YEAR(p.start_time) = $year AND WEEK(p.start_time, 1) = $week OR YEAR(p.end_time) = $year AND WEEK(p.end_time, 1) = $week";
            }
            break;
        case 'monthly':
            // Format: YYYY-MM (e.g., 2025-04)
            if (preg_match('/^(\d{4})-(\d{1,2})$/', $filter_value, $matches)) {
                $year = $matches[1];
                $month = $matches[2];
                $where_clause = "YEAR(p.start_time) = $year AND MONTH(p.start_time) = $month OR YEAR(p.end_time) = $year AND MONTH(p.end_time) = $month";
            }
            break;
    }
    
    if (empty($where_clause)) {
        $where_clause = "DATE(p.start_time) = CURDATE() OR DATE(p.end_time) = CURDATE()";
    }
    
    // Thêm điều kiện lọc theo xưởng
    $factory_condition = "";
    switch ($factory_filter) {
        case 'f2':
            // Line 1, 2, 3, 4 thuộc Mì_F2
            $factory_condition = "AND l.line_code IN ('LINE1', 'LINE2', 'LINE3', 'LINE4')";
            break;
        case 'f3':
            // Line 5, 6, 7, 8 thuộc Mì_F3
            $factory_condition = "AND l.line_code IN ('LINE5', 'LINE6', 'LINE7', 'LINE8')";
            break;
        case 'f1':
            // Line CSD và FS thuộc F1
            $factory_condition = "AND l.line_code IN ('CSD', 'FS')";
            break;
        default:
            // Không lọc, hiển thị tất cả
            $factory_condition = "";
            break;
    }
    
    $query = "SELECT p.id, p.line_id, p.product_id, p.start_time, p.end_time, 
              p.planned_quantity, p.actual_quantity, p.total_personnel, p.notes, p.created_by, p.created_at,
              l.line_name, l.line_code, l.factory,
              pr.product_name, pr.product_code, pr.product_group, pr.color_code,
              u.username as created_by_name
              FROM production_plans p
              JOIN production_lines l ON p.line_id = l.id
              JOIN products pr ON p.product_id = pr.id
              JOIN users u ON p.created_by = u.id
              WHERE $where_clause $factory_condition
              ORDER BY l.line_name, p.start_time";
    
    $result = $conn->query($query);
    
    $plans = [];
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $plans[] = $row;
        }
    }
    
    header('Content-Type: application/json');
    echo json_encode(['status' => 'success', 'data' => $plans]);
}

// Kiểm tra trùng lặp thời gian
function checkTimeOverlap() {
    global $conn;
    
    $line_id = isset($_POST['line_id']) ? intval($_POST['line_id']) : 0;
    $start_time = isset($_POST['start_time']) ? $_POST['start_time'] : '';
    $end_time = isset($_POST['end_time']) ? $_POST['end_time'] : '';
    $plan_id = isset($_POST['plan_id']) ? intval($_POST['plan_id']) : 0;
    
    if (!$line_id || !$start_time || !$end_time) {
        header('Content-Type: application/json');
        echo json_encode(['status' => 'error', 'message' => 'Thiếu thông tin cần thiết']);
        return;
    }
    
    // Điều kiện loại trừ kế hoạch đang chỉnh sửa (nếu có)
    $exclude_condition = $plan_id > 0 ? "AND id != $plan_id" : "";
    
    $start_time = $conn->real_escape_string($start_time);
    $end_time = $conn->real_escape_string($end_time);
    
    $query = "SELECT COUNT(*) as overlap_count FROM production_plans 
              WHERE line_id = $line_id $exclude_condition
              AND (
                  (start_time <= '$start_time' AND end_time > '$start_time') OR
                  (start_time < '$end_time' AND end_time >= '$end_time') OR
                  ('$start_time' <= start_time AND '$end_time' >= end_time)
              )";
    
    $result = $conn->query($query);
    $row = $result->fetch_assoc();
    
    $has_overlap = $row['overlap_count'] > 0;
    
    header('Content-Type: application/json');
    echo json_encode(['status' => 'success', 'has_overlap' => $has_overlap]);
}

// Thêm kế hoạch sản xuất mới
function addProductionPlan() {
    global $conn;
    
    $line_id = isset($_POST['line_id']) ? intval($_POST['line_id']) : 0;
    $product_id = isset($_POST['product_id']) ? intval($_POST['product_id']) : 0;
    $start_time = isset($_POST['start_time']) ? $_POST['start_time'] : '';
    $end_time = isset($_POST['end_time']) ? $_POST['end_time'] : '';
    $planned_quantity = isset($_POST['planned_quantity']) ? intval($_POST['planned_quantity']) : 0;
    $total_personnel = isset($_POST['total_personnel']) ? intval($_POST['total_personnel']) : 0;
    $notes = isset($_POST['notes']) ? $_POST['notes'] : '';
    $user_id = $_SESSION['user_id'];
    
    if (!$line_id || !$product_id || !$start_time || !$end_time || $planned_quantity <= 0) {
        header('Content-Type: application/json');
        echo json_encode(['status' => 'error', 'message' => 'Vui lòng điền đầy đủ thông tin']);
        return;
    }
    
    // Kiểm tra trùng lặp thời gian
    $check_query = "SELECT COUNT(*) as overlap_count FROM production_plans 
                   WHERE line_id = $line_id
                   AND (
                       (start_time <= '$start_time' AND end_time > '$start_time') OR
                       (start_time < '$end_time' AND end_time >= '$end_time') OR
                       ('$start_time' <= start_time AND '$end_time' >= end_time)
                   )";
    
    $check_result = $conn->query($check_query);
    $check_row = $check_result->fetch_assoc();
    
    if ($check_row['overlap_count'] > 0) {
        header('Content-Type: application/json');
        echo json_encode(['status' => 'error', 'message' => 'Thời gian đã bị trùng lặp với kế hoạch khác']);
        return;
    }
    
    $start_time = $conn->real_escape_string($start_time);
    $end_time = $conn->real_escape_string($end_time);
    $notes = $conn->real_escape_string($notes);
    
    $query = "INSERT INTO production_plans (line_id, product_id, start_time, end_time, planned_quantity, total_personnel, notes, created_by)
              VALUES ($line_id, $product_id, '$start_time', '$end_time', $planned_quantity, $total_personnel, '$notes', $user_id)";
    
    if ($conn->query($query)) {
        header('Content-Type: application/json');
        echo json_encode(['status' => 'success', 'message' => 'Thêm kế hoạch thành công']);
    } else {
        header('Content-Type: application/json');
        echo json_encode(['status' => 'error', 'message' => 'Lỗi: ' . $conn->error]);
    }
}

// Cập nhật kế hoạch sản xuất
function updateProductionPlan() {
    global $conn;
    
    $plan_id = isset($_POST['plan_id']) ? intval($_POST['plan_id']) : 0;
    $line_id = isset($_POST['line_id']) ? intval($_POST['line_id']) : 0;
    $product_id = isset($_POST['product_id']) ? intval($_POST['product_id']) : 0;
    $start_time = isset($_POST['start_time']) ? $_POST['start_time'] : '';
    $end_time = isset($_POST['end_time']) ? $_POST['end_time'] : '';
    $planned_quantity = isset($_POST['planned_quantity']) ? intval($_POST['planned_quantity']) : 0;
    $actual_quantity = isset($_POST['actual_quantity']) ? intval($_POST['actual_quantity']) : 0;
    $total_personnel = isset($_POST['total_personnel']) ? intval($_POST['total_personnel']) : 0;
    $notes = isset($_POST['notes']) ? $_POST['notes'] : '';
    
    if (!$plan_id || !$line_id || !$product_id || !$start_time || !$end_time || $planned_quantity <= 0) {
        header('Content-Type: application/json');
        echo json_encode(['status' => 'error', 'message' => 'Vui lòng điền đầy đủ thông tin']);
        return;
    }
    
    // Kiểm tra trùng lặp thời gian
    $check_query = "SELECT COUNT(*) as overlap_count FROM production_plans 
                   WHERE line_id = $line_id AND id != $plan_id
                   AND (
                       (start_time <= '$start_time' AND end_time > '$start_time') OR
                       (start_time < '$end_time' AND end_time >= '$end_time') OR
                       ('$start_time' <= start_time AND '$end_time' >= end_time)
                   )";
    
    $check_result = $conn->query($check_query);
    $check_row = $check_result->fetch_assoc();
    
    if ($check_row['overlap_count'] > 0) {
        header('Content-Type: application/json');
        echo json_encode(['status' => 'error', 'message' => 'Thời gian đã bị trùng lặp với kế hoạch khác']);
        return;
    }
    
    $start_time = $conn->real_escape_string($start_time);
    $end_time = $conn->real_escape_string($end_time);
    $notes = $conn->real_escape_string($notes);
    
    $query = "UPDATE production_plans 
              SET line_id = $line_id, 
                  product_id = $product_id, 
                  start_time = '$start_time', 
                  end_time = '$end_time', 
                  planned_quantity = $planned_quantity, 
                  actual_quantity = $actual_quantity,
                  total_personnel = $total_personnel,
                  notes = '$notes'
              WHERE id = $plan_id";
    
    if ($conn->query($query)) {
        header('Content-Type: application/json');
        echo json_encode(['status' => 'success', 'message' => 'Cập nhật kế hoạch thành công']);
    } else {
        header('Content-Type: application/json');
        echo json_encode(['status' => 'error', 'message' => 'Lỗi: ' . $conn->error]);
    }
}

// Xóa kế hoạch sản xuất
function deleteProductionPlan() {
    global $conn;
    
    $plan_id = isset($_POST['plan_id']) ? intval($_POST['plan_id']) : 0;
    
    if (!$plan_id) {
        header('Content-Type: application/json');
        echo json_encode(['status' => 'error', 'message' => 'ID kế hoạch không hợp lệ']);
        return;
    }
    
    $query = "DELETE FROM production_plans WHERE id = $plan_id";
    
    if ($conn->query($query)) {
        header('Content-Type: application/json');
        echo json_encode(['status' => 'success', 'message' => 'Xóa kế hoạch thành công']);
    } else {
        header('Content-Type: application/json');
        echo json_encode(['status' => 'error', 'message' => 'Lỗi: ' . $conn->error]);
    }
}
?>