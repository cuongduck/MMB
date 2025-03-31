<?php
header('Content-Type: application/json');
require_once '../../config/database_F1.php';

try {
    // Lấy 1000 giá trị gần nhất từ bảng CSD_trend
    $query = "SELECT 
        Time, 
        Co2
    FROM CSD_trend 
    ORDER BY Time DESC 
    LIMIT 1000";

    $result = $conn_F1->query($query);
    if (!$result) {
        throw new Exception($conn_F1->error);
    }

    // Thu thập dữ liệu
    $times = [];
    $co2Values = [];

    while ($row = $result->fetch_assoc()) {
        // Định dạng thời gian
        $timestamp = strtotime($row['Time']);
        $formattedTime = date('H:i', $timestamp);
        
        // Thêm vào mảng theo thứ tự thời gian tăng dần
        array_unshift($times, $formattedTime);
        array_unshift($co2Values, floatval($row['Co2']));
    }
    
    // Giá trị target và khoảng cho phép cho CO2
    $co2Target = 7.0; // Giá trị mục tiêu cho CO2
    $co2Min = 6.6;    // Giới hạn dưới
    $co2Max = 7.4;    // Giới hạn trên
    
    // Chuẩn bị response
    $response = [
        'times' => $times,
        'co2' => [
            'values' => $co2Values,
            'target' => $co2Target,
            'min' => $co2Min,
            'max' => $co2Max,
            'lowerLimit' => $co2Min,
            'upperLimit' => $co2Max
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