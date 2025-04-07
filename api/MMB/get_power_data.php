<?php
header('Content-Type: application/json');
require_once '../../config/database.php';
require_once '../../includes/functions.php';

$period = isset($_GET['period']) ? $_GET['period'] : 'today';

try {
    $dateRangeQuery = getDateRangeQuery($period);
    
    $query = "SELECT 
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
    LIMIT 1";

    $result = $conn->query($query);
    
    if (!$result) {
        throw new Exception($conn->error);
    }

    $row = $result->fetch_assoc();

    if (!$row) {
        throw new Exception("Không tìm thấy dữ liệu điện năng cho period: $period");
    }

    // Tính tổng điện năng
    $total = floatval($row['Total_MMB']);
    
    // Mảng chứa dữ liệu cho từng khu vực
    $labels = [
        'Xưởng F2',
        'Hon Chuan',
        'F1 Mắm',
        'F1 CSD',
        'DNP',
        'Xưởng F3',
        'Trí Việt',
        'Utility'
    ];
    
    $values = [
        floatval($row['Xuong_F2']),
        floatval($row['Hon_chuan']),
        floatval($row['F1_Mam']),
        floatval($row['F1_CSD']),
        floatval($row['DNP']),
        floatval($row['F3_Xuong']),
        floatval($row['Tri_Viet']),
        floatval($row['Utility'])
    ];
    
    // Tính phần trăm
    $percentages = array_map(function($value) use ($total) {
        return $total > 0 ? round(($value / $total) * 100, 1) : 0;
    }, $values);
    
    echo json_encode([
        'labels' => $labels,
        'values' => $values,
        'percentages' => $percentages,
        'total' => $total
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'error' => true,
        'message' => $e->getMessage()
    ]);
}
?>