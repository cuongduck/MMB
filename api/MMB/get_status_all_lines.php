<?php
require_once '../../config/database.php';
header('Content-Type: application/json');

try {
    // Lấy tham số period từ URL nếu có
    $period = isset($_GET['period']) ? $_GET['period'] : 'today';
    
    // Truy vấn dữ liệu từ bảng mmb_line_status
    $query = "SELECT * FROM mmb_line_status ORDER BY ID DESC LIMIT 1";
    
    $result = $conn->query($query);
    
    if (!$result) {
        throw new Exception("Query error: " . $conn->error);
    }
    
    $row = $result->fetch_assoc();
    
    if (!$row) {
        throw new Exception("No data found in mmb_line_status table");
    }
    
    // Chuẩn bị dữ liệu cho các line
    $lines = [
        'L1' => [
            'status' => $row['L1_Status'] ?? 'unknown',
            'product' => $row['L1_ten_SP'] ?? '',
            'speed' => $row['L1_Speed'] ?? 0
        ],
        'L2' => [
            'status' => $row['L2_Status'] ?? 'unknown',
            'product' => $row['L2_ten_SP'] ?? '',
            'speed' => $row['L2_Speed'] ?? 0
        ],
        'L3' => [
            'status' => $row['L3_Status'] ?? 'unknown',
            'product' => $row['L3_ten_SP'] ?? '',
            'speed' => $row['L3_Speed'] ?? 0
        ],
        'L4' => [
            'status' => $row['L4_Status'] ?? 'unknown',
            'product' => $row['L4_ten_SP'] ?? '',
            'speed' => $row['L4_Speed'] ?? 0
        ],
        'L5' => [
            'status' => $row['L5_Status'] ?? 'unknown',
            'product' => $row['L5_ten_SP'] ?? '',
            'speed' => $row['L5_Speed'] ?? 0
        ],
        'L6' => [
            'status' => $row['L6_Status'] ?? 'unknown',
            'product' => $row['L6_ten_SP'] ?? '',
            'speed' => $row['L6_Speed'] ?? 0
        ],
        'L7' => [
            'status' => $row['L7_Status'] ?? 'unknown',
            'product' => $row['L7_ten_SP'] ?? '',
            'speed' => $row['L7_Speed'] ?? 0
        ],
        'L8' => [
            'status' => $row['L8_Status'] ?? 'unknown',
            'product' => $row['L8_ten_SP'] ?? '',
            'speed' => $row['L8_Speed'] ?? $row['L8_ten_SPL8_Speed'] ?? 0 // Xử lý trường hợp tên cột có thể là L8_ten_SPL8_Speed
        ],
        'FS' => [
            'status' => $row['FS_Status'] ?? 'unknown',
            'product' => $row['FS_ten_SP'] ?? '',
            'speed' => $row['FS_Speed'] ?? 0
        ],
        'CSD' => [
            'status' => $row['CSD_Status'] ?? 'unknown',
            'product' => $row['CSD_ten_SP'] ?? '',
            'speed' => $row['CSD_Speed'] ?? 0
        ]
    ];
    
    echo json_encode([
        'success' => true,
        'data' => $lines,
        'period' => $period,
        'timestamp' => $row['ID'] ?? null
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>