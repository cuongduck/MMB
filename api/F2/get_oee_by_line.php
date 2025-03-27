<?php
header('Content-Type: application/json');
require_once '../../config/database.php';
require_once '../../includes/functions.php';

$period = isset($_GET['period']) ? $_GET['period'] : 'today';

try {
    // Lấy điều kiện thời gian từ functions.php
    $dateRangeQuery = getDateRangeQuery($period);
    
    // Thay thế WHERE thành AND vì điều kiện WHERE đã có trong mỗi subquery
    $dateRangeQuery = str_replace('WHERE', 'AND', $dateRangeQuery);
    
    $query = "SELECT 
        'Line 1' as line_name,
        ROUND(
            CASE 
                WHEN SUM(COALESCE(L1_SL_KH, 0)) > 0 
                THEN (SUM(COALESCE(L1_Tong_Goi, 0)) * 100.0) / SUM(COALESCE(L1_SL_KH, 0))
                ELSE 0 
            END,
        2) as avg_oee
        FROM OEE 
        WHERE L1_SL_KH IS NOT NULL $dateRangeQuery
        
        UNION ALL
        
        SELECT 
        'Line 2' as line_name,
        ROUND(
            CASE 
                WHEN SUM(COALESCE(L2_SL_KH, 0)) > 0 
                THEN (SUM(COALESCE(L2_Tong_Goi, 0)) * 100.0) / SUM(COALESCE(L2_SL_KH, 0))
                ELSE 0 
            END,
        2) as avg_oee
        FROM OEE 
        WHERE L2_SL_KH IS NOT NULL $dateRangeQuery
        
        UNION ALL
        
        SELECT 
        'Line 3' as line_name,
        ROUND(
            CASE 
                WHEN SUM(COALESCE(L3_SL_KH, 0)) > 0 
                THEN (SUM(COALESCE(L3_Tong_Goi, 0)) * 100.0) / SUM(COALESCE(L3_SL_KH, 0))
                ELSE 0 
            END,
        2) as avg_oee
        FROM OEE 
        WHERE L3_SL_KH IS NOT NULL $dateRangeQuery
        
        UNION ALL
        
        SELECT 
        'Line 4' as line_name,
        ROUND(
            CASE 
                WHEN SUM(COALESCE(L4_SL_KH, 0)) > 0 
                THEN (SUM(COALESCE(L4_Tong_Goi, 0)) * 100.0) / SUM(COALESCE(L4_SL_KH, 0))
                ELSE 0 
            END,
        2) as avg_oee
        FROM OEE 
        WHERE L4_SL_KH IS NOT NULL $dateRangeQuery
        
        ORDER BY avg_oee DESC";

    $result = $conn->query($query);
    
    if (!$result) {
        throw new Exception($conn->error);
    }

    $lines = [];
    $values = [];

    while ($row = $result->fetch_assoc()) {
        if ($row['avg_oee'] !== null) {  // Chỉ thêm vào kết quả nếu có giá trị OEE
            $lines[] = $row['line_name'];
            $values[] = floatval($row['avg_oee']);
        }
    }

    echo json_encode([
        'lines' => $lines,
        'values' => $values
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'error' => true,
        'message' => $e->getMessage()
    ]);
}
?>