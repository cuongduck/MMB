<?php
header('Content-Type: application/json');
require_once '../../config/database.php';
require_once '../../config/database_F1.php';
require_once '../../includes/functions.php';

$period = isset($_GET['period']) ? $_GET['period'] : 'today';

try {
    // Lấy điều kiện thời gian từ functions.php
    $dateRangeQuery = getDateRangeQuery($period);
    
    // Thêm LIMIT chỉ khi period là 'today'
    $limitClause = ($period === 'today') ? "LIMIT 8" : "";

    // Truy vấn dữ liệu F2
    $f2Query = "WITH time_periods AS (
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
            L1_Hap, L1_Chien, L2_Hap, L2_Chien, L3_Hap, L3_Chien, L4_Hap, L4_Chien,
            L1_Tong_Goi, L2_Tong_Goi, L3_Tong_Goi, L4_Tong_Goi, L1_SL_KH, L2_SL_KH, L3_SL_KH, L4_SL_KH
        FROM OEE 
        $dateRangeQuery
        ORDER BY Time DESC
        $limitClause
    )
    SELECT 
        period as label,
        ROUND(
            CASE 
                WHEN SUM(COALESCE(L1_SL_KH, 0) + COALESCE(L2_SL_KH, 0) + 
                        COALESCE(L3_SL_KH, 0) + COALESCE(L4_SL_KH, 0)) > 0 THEN
                    (SUM(COALESCE(L1_Hap, 0) + COALESCE(L1_Chien, 0) + 
                     COALESCE(L2_Hap, 0) + COALESCE(L2_Chien, 0) + 
                     COALESCE(L3_Hap, 0) + COALESCE(L3_Chien, 0) + 
                     COALESCE(L4_Hap, 0) + COALESCE(L4_Chien, 0)) * 1000.0) / 
                    SUM(COALESCE(L1_Tong_Goi, 0) + COALESCE(L2_Tong_Goi, 0) + 
                       COALESCE(L3_Tong_Goi, 0) + COALESCE(L4_Tong_Goi, 0))
                ELSE 0
            END, 
        2) as f2_steam_per_product
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

    $f2Result = $conn->query($f2Query);
    
    if (!$f2Result) {
        throw new Exception("F2 query error: " . $conn->error);
    }

    // Truy vấn dữ liệu F3
    $f3Query = "WITH time_periods AS (
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
            L5_Hap, L5_Chien, L6_Hap, L6_Chien,
            L5_Tong_Goi, L6_Tong_Goi, L5_SL_KH, L6_SL_KH 
        FROM OEE 
        $dateRangeQuery
        ORDER BY Time DESC
        $limitClause
    )
    SELECT 
        period as label,
        ROUND(
            CASE 
                WHEN SUM(COALESCE(L5_SL_KH, 0) + COALESCE(L6_SL_KH, 0)) > 0 THEN
                    (SUM(COALESCE(L5_Hap, 0) + COALESCE(L5_Chien, 0) + 
                     COALESCE(L6_Hap, 0) + COALESCE(L6_Chien, 0)) * 1000.0) / 
                    SUM(COALESCE(L5_Tong_Goi, 0) + COALESCE(L6_Tong_Goi, 0))
                ELSE 0
            END, 
        2) as f3_steam_per_product
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

    $f3Result = $conn->query($f3Query);
    
    if (!$f3Result) {
        throw new Exception("F3 query error: " . $conn->error);
    }

    // Truy vấn dữ liệu CSD và FS
    $f1Query = "WITH time_periods AS (
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
            CSD_hoi, FS_hoi, CSD_SL_thuc_te, FS_SL_thuc_te, CSD_SL_KH, FS_SL_KH
        FROM OEE 
        $dateRangeQuery
        ORDER BY Time DESC
        $limitClause
    )
    SELECT 
        period as label,
        ROUND(
            CASE 
                WHEN SUM(COALESCE(CSD_SL_KH, 0)) > 0 THEN
                    (SUM(COALESCE(CSD_hoi, 0)) * 1000.0) / (SUM(COALESCE(CSD_SL_thuc_te, 0)) * 0.33)
                ELSE 0
            END,
        2) as csd_steam_per_product,
        ROUND(
            CASE 
                WHEN SUM(COALESCE(FS_SL_KH, 0)) > 0 THEN
                    (SUM(COALESCE(FS_hoi, 0)) * 1000.0) / SUM(COALESCE(FS_SL_thuc_te, 0))
                ELSE 0
            END,
        2) as fs_steam_per_product
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

    $f1Result = $conn_F1->query($f1Query);
    
    if (!$f1Result) {
        throw new Exception("F1 query error: " . $conn_F1->error);
    }

    // Kết hợp dữ liệu
    $periods = [];
    
    // Lấy dữ liệu từ F2
    while ($row = $f2Result->fetch_assoc()) {
        $periods[$row['label']] = [
            'f2_steam' => floatval($row['f2_steam_per_product']),
            'f3_steam' => 0,
            'csd_steam' => 0,
            'fs_steam' => 0
        ];
    }
    
    // Lấy dữ liệu từ F3
    while ($row = $f3Result->fetch_assoc()) {
        if (isset($periods[$row['label']])) {
            $periods[$row['label']]['f3_steam'] = floatval($row['f3_steam_per_product']);
        } else {
            $periods[$row['label']] = [
                'f2_steam' => 0,
                'f3_steam' => floatval($row['f3_steam_per_product']),
                'csd_steam' => 0,
                'fs_steam' => 0
            ];
        }
    }
    
    // Lấy dữ liệu từ F1 (CSD và FS)
    while ($row = $f1Result->fetch_assoc()) {
        if (isset($periods[$row['label']])) {
            $periods[$row['label']]['csd_steam'] = floatval($row['csd_steam_per_product']);
            $periods[$row['label']]['fs_steam'] = floatval($row['fs_steam_per_product']);
        } else {
            $periods[$row['label']] = [
                'f2_steam' => 0,
                'f3_steam' => 0,
                'csd_steam' => floatval($row['csd_steam_per_product']),
                'fs_steam' => floatval($row['fs_steam_per_product'])
            ];
        }
    }
    
    // Sắp xếp dữ liệu theo thời gian
    ksort($periods);
    
    // Tạo dữ liệu kết quả
    $dates = [];
    $f2SteamValues = [];
    $f3SteamValues = [];
    $csdSteamValues = [];
    $fsSteamValues = [];
    
    foreach ($periods as $label => $data) {
        $dates[] = $label;
        $f2SteamValues[] = $data['f2_steam'];
        $f3SteamValues[] = $data['f3_steam'];
        $csdSteamValues[] = $data['csd_steam'];
        $fsSteamValues[] = $data['fs_steam'];
    }
    
    echo json_encode([
        'success' => true,
        'dates' => $dates,
        'f2_steam' => $f2SteamValues,
        'f3_steam' => $f3SteamValues,
        'csd_steam' => $csdSteamValues,
        'fs_steam' => $fsSteamValues,
        'period' => $period
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>