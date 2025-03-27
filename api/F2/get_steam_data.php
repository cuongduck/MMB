<?php
header('Content-Type: application/json');
require_once '../../config/database.php';
require_once '../../includes/functions.php';

$period = isset($_GET['period']) ? $_GET['period'] : 'today';

try {
    // Lấy điều kiện thời gian từ functions.php
    $dateRangeQuery = getDateRangeQuery($period);
    
    // Thêm LIMIT chỉ khi period là 'today'
    $limitClause = ($period === 'today') ? "LIMIT 8" : "";

    $baseQuery = "WITH time_periods AS (
        SELECT 
            Time,
            CASE 
                WHEN '$period' = 'today' THEN
                    DATE_FORMAT(Time, '%H:00')
                WHEN '$period' = 'yesterday' THEN
                    CASE 
                        WHEN (TIME(Time) >= '06:35:00' AND TIME(Time) < '15:35:00') THEN 'Ca 1'
                        WHEN (TIME(Time) >= '15:50:00' AND TIME(Time) < '23:35:00') THEN 'Ca 2'
                        WHEN ((TIME(Time) >= '23:36:00' AND TIME(Time) <= '23:59:59') OR
                              (TIME(Time) >= '00:00:00' AND TIME(Time) < '06:35:00')) THEN 'Ca 3'
                    END
                WHEN '$period' IN ('week', 'last_week') THEN
                    DATE_FORMAT(Time, '%d/%m')
                WHEN '$period' = 'month' THEN
                    CONCAT('Tuần ', FLOOR(DATEDIFF(Time, DATE_FORMAT(Time, '%Y-%m-01')) / 7) + 1)
            END as period,
            L1_Hap, L1_Chien, L2_Hap, L2_Chien, L3_Hap, L3_Chien,
            L1_Tong_Goi, L2_Tong_Goi, L3_Tong_Goi
        FROM OEE 
        $dateRangeQuery
        ORDER BY Time DESC
        $limitClause
    )
    SELECT 
        period as label,
        ROUND(AVG(COALESCE(L1_Hap, 0)), 2) as line1_hap,
        ROUND(AVG(COALESCE(L1_Chien, 0)), 2) as line1_chien,
        ROUND(AVG(COALESCE(l2_Hap, 0)), 2) as line2_hap,
        ROUND(AVG(COALESCE(l2_Chien, 0)), 2) as line2_chien,
        ROUND(AVG(COALESCE(l3_Hap, 0)), 2) as line3_hap,
        ROUND(AVG(COALESCE(l3_Chien, 0)), 2) as line3_chien,        
        ROUND(AVG(COALESCE(L1_Tong_Goi, 0)), 2) as line1_products,
        ROUND(AVG(COALESCE(l2_Tong_Goi, 0)), 2) as line2_products,
        ROUND(AVG(COALESCE(l3_Tong_Goi, 0)), 2) as line3_products,        
        ROUND(
            CASE 
                WHEN SUM(COALESCE(L1_Tong_Goi, 0) + COALESCE(l2_Tong_Goi, 0) + COALESCE(l3_Tong_Goi, 0)) > 0 THEN
                    (SUM(COALESCE(L1_Hap, 0) + COALESCE(L1_Chien, 0) + 
                     COALESCE(l2_Hap, 0) + COALESCE(l2_Chien, 0) + COALESCE(l3_Hap, 0) + COALESCE(l3_Chien, 0)) * 1000.0) / 
                    SUM(COALESCE(L1_Tong_Goi, 0) + COALESCE(l2_Tong_Goi, 0) + COALESCE(l3_Tong_Goi, 0))
                ELSE 0
            END, 
        2) as steam_per_product,
        ROUND(
            CASE 
                WHEN SUM(COALESCE(L1_Tong_Goi, 0)) > 0 THEN
                    (SUM(COALESCE(L1_Hap, 0) + COALESCE(L1_Chien, 0)) * 1000.0) / 
                    SUM(COALESCE(L1_Tong_Goi, 0))
                ELSE 0
            END,
        2) as line1_steam_per_product,
        ROUND(
            CASE 
                WHEN SUM(COALESCE(l2_Tong_Goi, 0)) > 0 THEN
                    (SUM(COALESCE(l2_Hap, 0) + COALESCE(l2_Chien, 0)) * 1000.0) / 
                    SUM(COALESCE(l2_Tong_Goi, 0))
                ELSE 0
            END,
        2) as line2_steam_per_product,
        ROUND(
            CASE 
                WHEN SUM(COALESCE(l3_Tong_Goi, 0)) > 0 THEN
                    (SUM(COALESCE(l3_Hap, 0) + COALESCE(l3_Chien, 0)) * 1000.0) / 
                    SUM(COALESCE(l3_Tong_Goi, 0))
                ELSE 0
            END,
        2) as line3_steam_per_product        
    FROM time_periods
    WHERE period IS NOT NULL
    GROUP BY period
    ORDER BY 
        CASE 
            WHEN '$period' = 'today' THEN Time
            WHEN '$period' = 'yesterday' THEN 
                CASE period
                    WHEN 'Ca 1' THEN 1
                    WHEN 'Ca 2' THEN 2
                    WHEN 'Ca 3' THEN 3
                END
            WHEN '$period' IN ('week', 'last_week') THEN Time
            WHEN '$period' = 'month' THEN 
                SUBSTRING(period, 6)
        END";

    $result = $conn->query($baseQuery);
    
    if (!$result) {
        throw new Exception($conn->error);
    }

    $dates = [];
    $line1Hap = [];
    $line1Chien = [];
    $line2Hap = [];
    $line2Chien = [];
    $line3Hap = [];
    $line3Chien = [];    
    $steamPerProduct = [];
    $line1SteamPerProduct = [];
    $line2SteamPerProduct = [];
    $line3SteamPerProduct = [];    

    while ($row = $result->fetch_assoc()) {
        $dates[] = $row['label'];
        $line1Hap[] = floatval($row['line1_hap']);
        $line1Chien[] = floatval($row['line1_chien']);
        $line2Hap[] = floatval($row['line2_hap']);
        $line2Chien[] = floatval($row['line2_chien']);
        $line3Hap[] = floatval($row['line3_hap']);
        $line3Chien[] = floatval($row['line3_chien']);        
        $steamPerProduct[] = floatval($row['steam_per_product']);
        $line1SteamPerProduct[] = floatval($row['line1_steam_per_product']);
        $line2SteamPerProduct[] = floatval($row['line2_steam_per_product']);
        $line3SteamPerProduct[] = floatval($row['line3_steam_per_product']);        
    }

    echo json_encode([
        'dates' => $dates,
        'line1Hap' => $line1Hap,
        'line1Chien' => $line1Chien,
        'line2Hap' => $line2Hap,
        'line2Chien' => $line2Chien,
        'line3Hap' => $line3Hap,
        'line3Chien' => $line3Chien,        
        'steamPerProduct' => $steamPerProduct,
        'line1SteamPerProduct' => $line1SteamPerProduct,
        'line2SteamPerProduct' => $line2SteamPerProduct,
        'line3SteamPerProduct' => $line3SteamPerProduct,        
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