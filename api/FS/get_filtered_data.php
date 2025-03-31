<?php
require_once '../../config/database_F1.php';
require_once '../../includes/functions.php';
header('Content-Type: application/json');

try {
    $period = isset($_GET['period']) ? $_GET['period'] : 'today';

    // Lấy điều kiện thời gian từ functions.php và thay thế WHERE thành AND
    $dateRangeQuery = getDateRangeQuery($period);
    $dateRangeQuery = str_replace('WHERE', 'AND', $dateRangeQuery);

    $query = "SELECT 
        -- Tổng sản lượng thực tê
        SUM(COALESCE(FS_SL_thuc_te	, 0)) as FS_production,
                -- Tổng sản lượng kế hoạch
        SUM(COALESCE(FS_SL_KH	, 0)) as FS_production_plan,
         -- Tổng OEE
        ROUND(
            CASE 
                WHEN SUM(COALESCE(FS_SL_KH, 0)) > 0 
                THEN (
                    SUM(COALESCE(FS_SL_thuc_te	, 0)) * 100.0
                ) / SUM(COALESCE(FS_SL_KH, 0))
                ELSE 0 
            END,
        2) as total_oee,

        -- Tổng tiêu hao hơi
        ROUND(
            CASE 
                WHEN SUM(COALESCE(FS_SL_thuc_te, 0)) > 0 
                THEN (SUM(COALESCE(FS_hoi, 0)) * 1000.0) / (SUM(COALESCE(FS_SL_thuc_te, 0)) * 0.33)
                ELSE 0 
            END,
        2) as total_steam,
         -- Lấy dữ liệu từ FS_So_Dien
        COALESCE(
            (SELECT SUM(COALESCE(FS_Tong, 0)) 
             FROM FS_So_Dien 
             WHERE 1=1 $dateRangeQuery),
            0
        ) as total_power
     
      
    FROM OEE 
    WHERE 1=1 $dateRangeQuery";

    $result = $conn_F1->query($query);
    if (!$result) {
        throw new Exception($conn_F1->error);
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