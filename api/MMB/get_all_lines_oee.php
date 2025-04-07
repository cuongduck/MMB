<?php
header('Content-Type: application/json');
require_once '../../config/database.php';
require_once '../../config/database_F1.php';
require_once '../../includes/functions.php';

$period = isset($_GET['period']) ? $_GET['period'] : 'today';

try {
    // Lấy điều kiện thời gian từ functions.php
    $dateRangeQuery = getDateRangeQuery($period);
    
    // Thay thế WHERE thành AND vì điều kiện WHERE đã có trong mỗi subquery
    $dateRangeQueryAnd = str_replace('WHERE', 'AND', $dateRangeQuery);
    
    // Lấy dữ liệu Line 1-4 (F2)
    $queryF2 = "SELECT 
        'Line 1' as line_name,
        ROUND(
            CASE 
                WHEN SUM(COALESCE(L1_SL_KH, 0)) > 0 
                THEN (SUM(COALESCE(L1_Tong_Goi, 0)) * 100.0) / SUM(COALESCE(L1_SL_KH, 0))
                ELSE 0 
            END,
        2) as avg_oee
        FROM OEE 
        WHERE L1_SL_KH IS NOT NULL $dateRangeQueryAnd
        
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
        WHERE L2_SL_KH IS NOT NULL $dateRangeQueryAnd
        
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
        WHERE L3_SL_KH IS NOT NULL $dateRangeQueryAnd
        
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
        WHERE L4_SL_KH IS NOT NULL $dateRangeQueryAnd";

    $resultF2 = $conn->query($queryF2);
    
    if (!$resultF2) {
        throw new Exception("F2 query error: " . $conn->error);
    }

    // Lấy dữ liệu Line 5-8 (F3)
    $queryF3 = "SELECT 
        'Line 5' as line_name,
        ROUND(
            CASE 
                WHEN SUM(COALESCE(L5_SL_KH, 0)) > 0 
                THEN (SUM(COALESCE(L5_Tong_Goi, 0)) * 100.0) / SUM(COALESCE(L5_SL_KH, 0))
                ELSE 0 
            END,
        2) as avg_oee
        FROM OEE 
        WHERE L5_SL_KH IS NOT NULL $dateRangeQueryAnd
        
        UNION ALL
        
        SELECT 
        'Line 6' as line_name,
        ROUND(
            CASE 
                WHEN SUM(COALESCE(L6_SL_KH, 0)) > 0 
                THEN (SUM(COALESCE(L6_Tong_Goi, 0)) * 100.0) / SUM(COALESCE(L6_SL_KH, 0))
                ELSE 0 
            END,
        2) as avg_oee
        FROM OEE 
        WHERE L6_SL_KH IS NOT NULL $dateRangeQueryAnd
        
        UNION ALL
        
        SELECT 
        'Line 7' as line_name,
        ROUND(
            CASE 
                WHEN SUM(COALESCE(L7_SL_KH, 0)) > 0 
                THEN (SUM(COALESCE(L7_Tong_Goi, 0)) * 100.0) / SUM(COALESCE(L7_SL_KH, 0))
                ELSE 0 
            END,
        2) as avg_oee
        FROM OEE 
        WHERE L7_SL_KH IS NOT NULL $dateRangeQueryAnd
        
        UNION ALL
        
        SELECT 
        'Line 8' as line_name,
        ROUND(
            CASE 
                WHEN SUM(COALESCE(L8_SL_KH, 0)) > 0 
                THEN (SUM(COALESCE(L8_Tong_Goi, 0)) * 100.0) / SUM(COALESCE(L8_SL_KH, 0))
                ELSE 0 
            END,
        2) as avg_oee
        FROM OEE 
        WHERE L8_SL_KH IS NOT NULL $dateRangeQueryAnd";

    $resultF3 = $conn->query($queryF3);
    
    if (!$resultF3) {
        throw new Exception("F3 query error: " . $conn->error);
    }

    // Lấy dữ liệu CSD
    $queryCSD = "SELECT 
        'CSD' as line_name,
        ROUND(
            CASE 
                WHEN SUM(COALESCE(CSD_SL_KH, 0)) > 0 
                THEN (SUM(COALESCE(CSD_SL_thuc_te, 0)) * 100.0) / SUM(COALESCE(CSD_SL_KH, 0))
                ELSE 0
            END,
        2) as avg_oee
        FROM OEE 
        $dateRangeQuery AND CSD_SL_KH IS NOT NULL AND CSD_SL_KH > 0";

    $resultCSD = $conn_F1->query($queryCSD);
    
    if (!$resultCSD) {
        throw new Exception("CSD query error: " . $conn_F1->error);
    }

    // Lấy dữ liệu FS
    $queryFS = "SELECT 
        'FS' as line_name,
        ROUND(
            CASE 
                WHEN SUM(COALESCE(FS_SL_KH, 0)) > 0 
                THEN (SUM(COALESCE(FS_SL_thuc_te, 0)) * 100.0) / SUM(COALESCE(FS_SL_KH, 0))
                ELSE 0
            END,
        2) as avg_oee
        FROM OEE 
        $dateRangeQuery AND FS_SL_KH IS NOT NULL AND FS_SL_KH > 0";

    $resultFS = $conn_F1->query($queryFS);
    
    if (!$resultFS) {
        throw new Exception("FS query error: " . $conn_F1->error);
    }

    // Kết hợp kết quả
    $lines = [];
    $values = [];

    // Thêm dữ liệu Line 1-4
    while ($row = $resultF2->fetch_assoc()) {
        $lines[] = $row['line_name'];
        $values[] = floatval($row['avg_oee']);
    }

    // Thêm dữ liệu Line 5-8
    while ($row = $resultF3->fetch_assoc()) {
        $lines[] = $row['line_name'];
        $values[] = floatval($row['avg_oee']);
    }

    // Thêm dữ liệu CSD
    while ($row = $resultCSD->fetch_assoc()) {
        $lines[] = $row['line_name'];
        $values[] = floatval($row['avg_oee']);
    }

    // Thêm dữ liệu FS
    while ($row = $resultFS->fetch_assoc()) {
        $lines[] = $row['line_name'];
        $values[] = floatval($row['avg_oee']);
    }

    // Trả về kết quả
    echo json_encode([
        'lines' => $lines,
        'values' => $values,
        'period' => $period
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'error' => true,
        'message' => $e->getMessage()
    ]);
}
?>