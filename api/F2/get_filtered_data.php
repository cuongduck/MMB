<?php
require_once '../../config/database.php';
require_once '../../includes/functions.php';
header('Content-Type: application/json');

try {
    $period = isset($_GET['period']) ? $_GET['period'] : 'today';

    // Lấy điều kiện thời gian từ functions.php và thay thế WHERE thành AND
    $dateRangeQuery = getDateRangeQuery($period);
    $dateRangeQuery = str_replace('WHERE', 'AND', $dateRangeQuery);

    $query = "SELECT 
        -- Tổng sản lượng
        SUM(COALESCE(L1_Tong_Goi, 0)) as l1_production,
        SUM(COALESCE(L2_Tong_Goi, 0)) as l2_production,
        SUM(COALESCE(L3_Tong_Goi, 0)) as l3_production,
        SUM(COALESCE(L4_Tong_Goi, 0)) as l4_production,
        SUM(COALESCE(L1_Tong_Goi, 0) + COALESCE(L2_Tong_Goi, 0) + COALESCE(L3_Tong_Goi, 0) + COALESCE(L4_Tong_Goi, 0)) as total_production,
        SUM(COALESCE(L1_SL_KH, 0) + COALESCE(L2_SL_KH, 0) + COALESCE(L3_SL_KH, 0) + COALESCE(L4_SL_KH, 0)) as total_plan,
        
        -- OEE từng line
        ROUND(
            CASE 
                WHEN SUM(COALESCE(L1_SL_KH, 0)) > 0 
                THEN (SUM(COALESCE(L1_Tong_Goi, 0)) * 100.0) / SUM(COALESCE(L1_SL_KH, 0))
                ELSE 0 
            END, 
        2) as l1_oee,
        
        ROUND(
            CASE 
                WHEN SUM(COALESCE(L2_SL_KH, 0)) > 0 
                THEN (SUM(COALESCE(L2_Tong_Goi, 0)) * 100.0) / SUM(COALESCE(L2_SL_KH, 0))
                ELSE 0 
            END, 
        2) as l2_oee,

        ROUND(
            CASE 
                WHEN SUM(COALESCE(L3_SL_KH, 0)) > 0 
                THEN (SUM(COALESCE(L3_Tong_Goi, 0)) * 100.0) / SUM(COALESCE(L3_SL_KH, 0))
                ELSE 0 
            END, 
        2) as l3_oee,

        ROUND(
            CASE 
                WHEN SUM(COALESCE(L4_SL_KH, 0)) > 0 
                THEN (SUM(COALESCE(L4_Tong_Goi, 0)) * 100.0) / SUM(COALESCE(L4_SL_KH, 0))
                ELSE 0 
            END, 
        2) as l4_oee,
        
        -- Tổng OEE
        ROUND(
            CASE 
                WHEN SUM(COALESCE(L1_SL_KH, 0) + COALESCE(L2_SL_KH, 0) + COALESCE(L3_SL_KH, 0) + COALESCE(L4_SL_KH, 0)) > 0 
                THEN (
                    SUM(COALESCE(L1_Tong_Goi, 0) + COALESCE(L2_Tong_Goi, 0) + COALESCE(L3_Tong_Goi, 0) + COALESCE(L4_Tong_Goi, 0)) * 100.0
                ) / SUM(COALESCE(L1_SL_KH, 0) + COALESCE(L2_SL_KH, 0) + COALESCE(L3_SL_KH, 0) + COALESCE(L4_SL_KH, 0))
                ELSE 0 
            END,
        2) as total_oee,
        
        -- Tiêu hao hơi từng line
        ROUND(
            CASE 
                WHEN SUM(COALESCE(L1_Tong_Goi, 0)) > 0 
                THEN (SUM(COALESCE(L1_Hap, 0) + COALESCE(L1_Chien, 0)) * 1000.0) / SUM(COALESCE(L1_Tong_Goi, 0))
                ELSE 0 
            END,
        2) as l1_steam,
        
        ROUND(
            CASE 
                WHEN SUM(COALESCE(L2_Tong_Goi, 0)) > 0 
                THEN (SUM(COALESCE(L2_Hap, 0) + COALESCE(L2_Chien, 0)) * 1000.0) / SUM(COALESCE(L2_Tong_Goi, 0))
                ELSE 0 
            END,
        2) as l2_steam,
        
                
        ROUND(
            CASE 
                WHEN SUM(COALESCE(L3_Tong_Goi, 0)) > 0 
                THEN (SUM(COALESCE(L3_Hap, 0) + COALESCE(L3_Chien, 0)) * 1000.0) / SUM(COALESCE(L3_Tong_Goi, 0))
                ELSE 0 
            END,
        2) as l3_steam,
        
        -- Tổng tiêu hao hơi
        ROUND(
            CASE 
                WHEN SUM(COALESCE(L1_Tong_Goi, 0) + COALESCE(L2_Tong_Goi, 0) + COALESCE(L3_Tong_Goi, 0)) > 0 
                THEN (
                    SUM(COALESCE(L1_Hap, 0) + COALESCE(L1_Chien, 0) + 
                        COALESCE(L2_Hap, 0) + COALESCE(L2_Chien, 0) + COALESCE(L3_Hap, 0) + COALESCE(L3_Chien, 0)) * 1000.0
                ) / SUM(COALESCE(L1_Tong_Goi, 0) + COALESCE(L2_Tong_Goi, 0) + COALESCE(L3_Tong_Goi, 0))
                ELSE 0 
            END,
        2) as total_steam,
         -- Lấy dữ liệu từ So_dien_F2
        COALESCE(
            (SELECT SUM(COALESCE(F2_Tong, 0)) 
             FROM So_dien_F2 
             WHERE 1=1 $dateRangeQuery),
            0
        ) as total_power,
        
        COALESCE(
            (SELECT SUM(COALESCE(Tong_Mi, 0))
             FROM So_dien_F3 
             WHERE 1=1 $dateRangeQuery),
            0
        ) as power_target,
        
        COALESCE(
            (SELECT SUM(COALESCE(F2_Line1, 0))
             FROM So_dien_F2 
             WHERE 1=1 $dateRangeQuery),
            0
        ) as l1_power,
        
        COALESCE(
            (SELECT SUM(COALESCE(F2_Line2, 0))
             FROM So_dien_F2 
             WHERE 1=1 $dateRangeQuery),
            0
        ) as l2_power,
        
        COALESCE(
            (SELECT SUM(COALESCE(F2_Line3, 0))
             FROM So_dien_F2 
             WHERE 1=1 $dateRangeQuery),
            0
        ) as l3_power,
        
        COALESCE(
            (SELECT SUM(COALESCE(F2_Line4, 0))
             FROM So_dien_F2 
             WHERE 1=1 $dateRangeQuery),
            0
        ) as l4_power,
        
        COALESCE(
            (SELECT SUM(COALESCE(F2_MNK, 0))
             FROM So_dien_F2 
             WHERE 1=1 $dateRangeQuery),
            0
        ) as mnk_power,
        
        COALESCE(
            (SELECT SUM(COALESCE(F2_KS_chiller, 0))
             FROM So_dien_F2 
             WHERE 1=1 $dateRangeQuery),
            0
        ) as ahu_power       
    FROM OEE 
    WHERE 1=1 $dateRangeQuery";

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