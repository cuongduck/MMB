<?php
header('Content-Type: application/json');
require_once '../../config/database_F1.php';

try {
    // Lấy 1000 giá trị gần nhất từ bảng CSD_trend
    $query = "SELECT 
        Time, 
        Brix
    FROM CSD_trend 
    ORDER BY Time DESC 
    LIMIT 1000";

    $result = $conn->query($query);
    if (!$result) {
        throw new Exception($conn->error);
    }

    // Thu thập dữ liệu
    $times = [];
    $brixValues = [];

    while ($row = $result->fetch_assoc()) {
        // Định dạng thời gian
        $timestamp = strtotime($row['Time']);
        $formattedTime = date('H:i', $timestamp);
        
        // Thêm vào mảng theo thứ tự thời gian tăng dần
        array_unshift($times, $formattedTime);
        array_unshift($brixValues, floatval($row['Brix']));
    }
    
    // Giá trị target và khoảng cho phép cho Brix
    $brixTarget = 16.7; // Giá trị mục tiêu cho Brix
    $brixMin = 16.5;    // Giới hạn dưới
    $brixMax = 16.9;    // Giới hạn trên
    
    // Chuẩn bị response
    $response = [
        'times' => $times,
        'brix' => [
            'values' => $brixValues,
            'target' => $brixTarget,
            'min' => $brixMin,
            'max' => $brixMax,
            'lowerLimit' => $brixMin,
            'upperLimit' => $brixMax
        ]
    ];
    
    echo json_encode($response);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'error' => true,
        'message' => $e->getMessage()
    ]);
}
?>