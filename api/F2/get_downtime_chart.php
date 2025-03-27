<?php
header('Content-Type: application/json');
require_once '../../config/database.php';
require_once '../../includes/functions.php';

$period = isset($_GET['period']) ? $_GET['period'] : 'today';

try {
    $originalDateRangeQuery = getDateRangeQuery($period);
    $dateRangeQuery = str_replace('Time', 'Date', $originalDateRangeQuery);

    // Query chính cho total F2 (chỉ lấy dữ liệu cho các Line L1, L2, L3, L4)
    $mainQuery = "SELECT 
        Ten_Loi as ErrorName,
        SUM(Thoi_Gian_Dung) as Duration,
        GROUP_CONCAT(Ghi_Chu SEPARATOR '; ') as Details
    FROM Downtime 
    $dateRangeQuery
    AND Line IN ('L1', 'L2', 'L3', 'L4')
    GROUP BY Ten_Loi
    ORDER BY Duration DESC";

    // Query phụ để lấy thông tin line (chỉ lấy dữ liệu cho các Line L1, L2, L3, L4)
    if (strpos($dateRangeQuery, 'WHERE') !== false) {
        $lineQuery = "SELECT 
            Line,
            COUNT(*) as StopCount,
            SUM(Thoi_Gian_Dung) as TotalDuration
        FROM Downtime 
        $dateRangeQuery
        AND Line IN ('L1', 'L2', 'L3', 'L4')
        GROUP BY Line
        ORDER BY Line";
    } else {
        $lineQuery = "SELECT 
            Line,
            COUNT(*) as StopCount,
            SUM(Thoi_Gian_Dung) as TotalDuration
        FROM Downtime 
        $dateRangeQuery
        WHERE Line IN ('L1', 'L2', 'L3', 'L4')
        GROUP BY Line
        ORDER BY Line";
    }

    $mainResult = $conn->query($mainQuery);
    $lineResult = $conn->query($lineQuery);
    
    if (!$mainResult || !$lineResult) {
        throw new Exception($conn->error);
    }

    $data = [
        'totalF2' => [],
        'lineData' => []
    ];

    // Xử lý data cho total F2
    $totalDuration = 0; // Khởi tạo tổng thời gian dừng
    while ($row = $mainResult->fetch_assoc()) {
        $totalDuration += floatval($row['Duration']);
        $data['totalF2'][] = [
            'name' => $row['ErrorName'],
            'value' => floatval($row['Duration']),
            'details' => $row['Details']
        ];
    }

    // Nếu không có dữ liệu, đặt totalF3 thành 0
    if (empty($data['totalF2'])) {
        $data['totalF2'][] = [
            'name' => '',
            'value' => 0,
            'details' => ''
        ];
    }

    // Xử lý data cho line
    while ($row = $lineResult->fetch_assoc()) {
        $data['lineData'][] = [
            'line' => $row['Line'],
            'stopCount' => intval($row['StopCount']),
            'duration' => floatval($row['TotalDuration'])
        ];
    }

    echo json_encode($data);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'error' => true,
        'message' => $e->getMessage()
    ]);
}
?>