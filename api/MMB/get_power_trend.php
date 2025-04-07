<?php
header('Content-Type: application/json');
require_once '../../config/database.php';
require_once '../../includes/functions.php';

$period = isset($_GET['period']) ? $_GET['period'] : 'today';

try {
    $dateRangeQuery = getDateRangeQuery($period);
    
    // Lấy giới hạn số lượng bản ghi dựa trên period
    $limit = "";
    if ($period === "today") {
        $limit = "LIMIT 24"; // Giới hạn 24 bản ghi cho period today
    }
    
    $query = "SELECT 
        Time,
        Xuong_F2,
        Hon_chuan,
        F1_Mam,
        F1_CSD,
        DNP,
        F3_Xuong,
        Tri_Viet,
        Utility,
        Total_MMB
    FROM So_dien_MMB
    $dateRangeQuery
    ORDER BY Time DESC 
    $limit";

    $result = $conn->query($query);
    
    if (!$result) {
        throw new Exception($conn->error);
    }

    // Tên các khu vực
    $labels = [
        'Xưởng F2',
        'Hồn Chuẩn',
        'F1 Mắm',
        'F1 CSD',
        'DNP',
        'F3 Xưởng',
        'Trí Việt',
        'Utility'
    ];
    
    // Mảng chứa dữ liệu theo thời gian
    $datasets = [];
    while ($row = $result->fetch_assoc()) {
        $dataset = [
            'time' => $row['Time'],
            'Xuong_F2' => floatval($row['Xuong_F2']),
            'Hon_chuan' => floatval($row['Hon_chuan']),
            'F1_Mam' => floatval($row['F1_Mam']),
            'F1_CSD' => floatval($row['F1_CSD']),
            'DNP' => floatval($row['DNP']),
            'F3_Xuong' => floatval($row['F3_Xuong']),
            'Tri_Viet' => floatval($row['Tri_Viet']),
            'Utility' => floatval($row['Utility']),
            'Total_MMB' => floatval($row['Total_MMB'])
        ];
        $datasets[] = $dataset;
    }
    
    // Đảo ngược mảng để hiển thị theo thứ tự thời gian tăng dần
    $datasets = array_reverse($datasets);
    
    echo json_encode([
        'labels' => $labels,
        'datasets' => $datasets
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'error' => true,
        'message' => $e->getMessage()
    ]);
}
?>