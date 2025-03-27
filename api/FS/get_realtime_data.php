<?php
require_once '../../config/database_F1.php';
header('Content-Type: application/json');

try {
    $line = isset($_GET['line']) ? $_GET['line'] : 'csd';
    
    // Truy vấn dữ liệu realtime từ bảng CSD_Realtime
    $query = "SELECT 
        Status as status,
        Speed as speed,
        Ten_SP as product,
        Sl_thuc_te as production,
        TIME_FORMAT(Time, '%H:%i:%s') as time
    FROM FS_Realtime 
    WHERE ID = 1"; // Giả sử luôn dùng bản ghi ID 1 cho dữ liệu realtime
    
    $result = $conn->query($query);
    
    if (!$result) {
        throw new Exception($conn->error);
    }
    
    $row = $result->fetch_assoc();
    

    echo json_encode($row);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'error' => true,
        'message' => $e->getMessage()
    ]);
}
?>