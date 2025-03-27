<?php
header('Content-Type: application/json');
require_once '../../config/database_F1.php';
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
            CSD_hoi, FS_hoi, FS_SL_thuc_te, CSD_SL_thuc_te
        FROM OEE 
        $dateRangeQuery
        ORDER BY Time DESC
        $limitClause
    )
    SELECT 
        period as label,
        ROUND(AVG(COALESCE(CSD_hoi, 0)), 2) as CSD_hoi,
        ROUND(AVG(COALESCE(FS_hoi, 0)), 2) as FS_hoi,
        ROUND(AVG(COALESCE(CSD_SL_thuc_te, 0)), 2) as CSD_products,
        ROUND(AVG(COALESCE(FS_SL_thuc_te, 0)), 2) as FS_products,
            CASE 
                WHEN SUM(COALESCE(CSD_SL_thuc_te, 0) + COALESCE(FS_SL_thuc_te, 0)) > 0 THEN
                    (SUM(COALESCE(CSD_hoi, 0)  + 
                     COALESCE(FS_hoi, 0)) * 1000.0) / 
                    SUM(COALESCE(CSD_SL_thuc_te, 0) + COALESCE(FS_SL_thuc_te, 0))
                ELSE 0
            END as steam_per_product,
        ROUND(
            CASE 
                WHEN SUM(COALESCE(CSD_SL_thuc_te, 0)) > 0 THEN
                    (SUM(COALESCE(CSD_hoi, 0)) * 1000.0) / (SUM(COALESCE(CSD_SL_thuc_te, 0)) * 0.33)
                ELSE 0
            END,
        3) as CSD_steam_per_product,
        ROUND(
            CASE 
                WHEN SUM(COALESCE(FS_SL_thuc_te, 0)) > 0 THEN
                    SUM(COALESCE(FS_hoi, 0) * 1000.0) / 
                    SUM(COALESCE(FS_SL_thuc_te, 0))
                ELSE 0
            END,
        2) as FS_steam_per_product
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
    $CSDhoi = [];
    $FShoi = [];
    $steamPerProduct = [];
    $CSDSteamPerProduct = [];
    $FSSteamPerProduct = [];

    while ($row = $result->fetch_assoc()) {
        $dates[] = $row['label'];
        $CSDhoi[] = floatval($row['CSD_hoi']);
        $FShoi[] = floatval($row['FS_hoi']);
        $steamPerProduct[] = floatval($row['steam_per_product']);
        $CSDSteamPerProduct[] = floatval($row['CSD_steam_per_product']);
        $FSSteamPerProduct[] = floatval($row['FS_steam_per_product']);
    }

    echo json_encode([
        'dates' => $dates,
        'CSDhoi' => $CSDhoi,
        'FShoi' => $FShoi,
        'steamPerProduct' => $steamPerProduct,
        'CSDSteamPerProduct' => $CSDSteamPerProduct,
        'FSSteamPerProduct' => $FSSteamPerProduct,
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