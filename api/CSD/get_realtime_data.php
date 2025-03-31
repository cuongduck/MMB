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
        Co2 as co2,
        Brix as brix,
        TIME_FORMAT(Time, '%H:%i:%s') as time
    FROM CSD_Realtime 
    WHERE ID = 1"; // Giả sử luôn dùng bản ghi ID 1 cho dữ liệu realtime
    
    $result = $conn_F1->query($query);
    
    if (!$result) {
        throw new Exception($conn_F1->error);
    }
    
    $row = $result->fetch_assoc();
    
    // Định dạng dữ liệu cho phù hợp với hiển thị
 $row['speed'] = number_format($row['speed']) . '';
    
    echo json_encode($row);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'error' => true,
        'message' => $e->getMessage()
    ]);
}
?>