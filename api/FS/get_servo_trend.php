<?php
header('Content-Type: application/json');
require_once '../../config/database_F1.php';
require_once '../../includes/functions.php';

try {
    // Lấy tham số từ request
    $startTime = isset($_GET['start_time']) ? $_GET['start_time'] : null;
    $endTime = isset($_GET['end_time']) ? $_GET['end_time'] : null;
    $limit = isset($_GET['limit']) ? intval($_GET['limit']) : 1000;

    // Xây dựng câu truy vấn
    $query = "SELECT 
        Time,
        T_motor1, T_drive1,
        T_motor2, T_drive2,
        T_motor3, T_drive3,
        T_motor4, T_drive4,
        T_motor5, T_drive5,
        T_motor6, T_drive6,
        T_motor7, T_drive7,
        T_motor8, T_drive8,
        T_motor9, T_drive9
    FROM FS_trend";

    // Thêm điều kiện lọc nếu có start_time và end_time
    $whereConditions = [];
    if ($startTime) {
        $whereConditions[] = "Time >= '" . $conn_F1->real_escape_string($startTime) . "'";
    }
    if ($endTime) {
        $whereConditions[] = "Time <= '" . $conn_F1->real_escape_string($endTime) . "'";
    }

    // Nối điều kiện WHERE nếu có
    if (!empty($whereConditions)) {
        $query .= " WHERE " . implode(" AND ", $whereConditions);
    }

    // Sắp xếp và giới hạn
    $query .= " ORDER BY Time ASC LIMIT $limit";

    $result = $conn_F1->query($query);
    
    if (!$result) {
        throw new Exception($conn_F1->error);
    }

    // Thu thập dữ liệu
    $data = $result->fetch_all(MYSQLI_ASSOC);

    echo json_encode($data);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}
?>