<?php
header('Content-Type: application/json');
require_once '../../config/database_F1.php';
require_once '../../includes/functions.php';

// Lấy tham số từ request
$limit = isset($_GET['limit']) ? intval($_GET['limit']) : 100;
$offset = isset($_GET['offset']) ? intval($_GET['offset']) : 0;

try {
    // Đếm tổng số bản ghi (không lọc theo period)
    $countQuery = "SELECT COUNT(*) as total FROM FS_trend";
    $countResult = $conn_F1->query($countQuery);
    
    if (!$countResult) {
        throw new Exception($conn_F1->error);
    }
    
    $totalRecords = $countResult->fetch_assoc()['total'];
    
    // Query lấy dữ liệu từ bảng FS_trend mà không có điều kiện WHERE
    $query = "SELECT 
        id,
        Time, 
        Speed
    FROM FS_trend 
    ORDER BY Time ASC 
    LIMIT $limit OFFSET $offset";
    
    $result = $conn_F1->query($query);
    if (!$result) {
        throw new Exception($conn_F1->error);
    }
    
    // Thu thập dữ liệu
    $data = [];
    while ($row = $result->fetch_assoc()) {
        // Chuyển đổi thành timestamp JavaScript (milliseconds)
        $timestamp = strtotime($row["Time"]) * 1000;
        
        $data[] = [
            'id' => $row["id"],
            'time' => $row["Time"],
            'timestamp' => $timestamp,
            'speed' => (float)$row["Speed"]
        ];
    }
    
    // Chuẩn bị dữ liệu trả về
    $response = [
        'status' => 'success',
        'total' => $totalRecords,
        'limit' => $limit,
        'offset' => $offset,
        'data' => $data
    ];
    
    echo json_encode($response);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}
?>