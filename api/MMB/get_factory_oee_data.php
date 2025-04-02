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

    // Truy vấn dữ liệu OEE cho F2
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
                    CONCAT('Tuần ', FLOOR(DATEDIFF(Time, 
                    DATE_FORMAT(Time, '%Y-%m-01')) / 7) + 1)
            END as period,
            L1_Tong_Goi, L2_Tong_Goi, L3_Tong_Goi, L4_Tong_Goi,
            L1_SL_KH, L2_SL_KH, L3_SL_KH, L4_SL_KH
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
                        COALESCE(L3_SL_KH, 0) + COALESCE(L4_SL_KH, 0)) > 0 
                THEN (
                    SUM(COALESCE(L1_Tong_Goi, 0) + COALESCE(L2_Tong_Goi, 0) + 
                        COALESCE(L3_Tong_Goi, 0) + COALESCE(L4_Tong_Goi, 0)) * 100.0
                ) / SUM(COALESCE(L1_SL_KH, 0) + COALESCE(L2_SL_KH, 0) + 
                       COALESCE(L3_SL_KH, 0) + COALESCE(L4_SL_KH, 0))
                ELSE 0
            END,
        2) as f2_oee
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

    // Truy vấn dữ liệu OEE cho F3
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
                    CONCAT('Tuần ', FLOOR(DATEDIFF(Time, 
                    DATE_FORMAT(Time, '%Y-%m-01')) / 7) + 1)
            END as period,
            L5_Tong_Goi, L6_Tong_Goi, L7_Tong_Goi, L8_Tong_Goi,
            L5_SL_KH, L6_SL_KH, L7_SL_KH, L8_SL_KH
        FROM OEE 
        $dateRangeQuery
        ORDER BY Time DESC
        $limitClause
    )
    SELECT 
        period as label,
        ROUND(
            CASE 
                WHEN SUM(COALESCE(L5_SL_KH, 0) + COALESCE(L6_SL_KH, 0) + 
                        COALESCE(L7_SL_KH, 0) + COALESCE(L8_SL_KH, 0)) > 0 
                THEN (
                    SUM(COALESCE(L5_Tong_Goi, 0) + COALESCE(L6_Tong_Goi, 0) + 
                        COALESCE(L7_Tong_Goi, 0) + COALESCE(L8_Tong_Goi, 0)) * 100.0
                ) / SUM(COALESCE(L5_SL_KH, 0) + COALESCE(L6_SL_KH, 0) + 
                       COALESCE(L7_SL_KH, 0) + COALESCE(L8_SL_KH, 0))
                ELSE 0
            END,
        2) as f3_oee
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

    // Truy vấn dữ liệu OEE cho F1 (CSD và FS)
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
                    CONCAT('Tuần ', FLOOR(DATEDIFF(Time, 
                    DATE_FORMAT(Time, '%Y-%m-01')) / 7) + 1)
            END as period,
            CSD_SL_thuc_te, CSD_SL_KH,
            FS_SL_thuc_te, FS_SL_KH
        FROM OEE 
        $dateRangeQuery
        ORDER BY Time DESC
        $limitClause
    )
    SELECT 
        period as label,
        ROUND(
            CASE 
                WHEN SUM(COALESCE(CSD_SL_KH, 0)) > 0 
                THEN (
                    SUM(COALESCE(CSD_SL_thuc_te, 0)) * 100.0
                ) / SUM(COALESCE(CSD_SL_KH, 0))
                ELSE 0
            END,
        2) as csd_oee,
        ROUND(
            CASE 
                WHEN SUM(COALESCE(FS_SL_KH, 0)) > 0 
                THEN (
                    SUM(COALESCE(FS_SL_thuc_te, 0)) * 100.0
                ) / SUM(COALESCE(FS_SL_KH, 0))
                ELSE 0
            END,
        2) as fs_oee,
        ROUND(
            CASE 
                WHEN SUM(COALESCE(CSD_SL_KH, 0) + COALESCE(FS_SL_KH, 0)) > 0 
                THEN (
                    SUM(COALESCE(CSD_SL_thuc_te, 0) + COALESCE(FS_SL_thuc_te, 0)) * 100.0
                ) / SUM(COALESCE(CSD_SL_KH, 0) + COALESCE(FS_SL_KH, 0))
                ELSE 0
            END,
        2) as f1_oee
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

    // Truy vấn chi tiết sản lượng từng line
    $detailQuery = "WITH time_periods AS (
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
                    CONCAT('Tuần ', FLOOR(DATEDIFF(Time, 
                    DATE_FORMAT(Time, '%Y-%m-01')) / 7) + 1)
            END as period,
            
            /* Line 1-8 */
            L1_Tong_Goi, L1_SL_KH, 
            L2_Tong_Goi, L2_SL_KH,
            L3_Tong_Goi, L3_SL_KH,
            L4_Tong_Goi, L4_SL_KH,
            L5_Tong_Goi, L5_SL_KH,
            L6_Tong_Goi, L6_SL_KH,
            L7_Tong_Goi, L7_SL_KH,
            L8_Tong_Goi, L8_SL_KH,
            
            /* CSD và FS phụ thuộc vào bảng/DB khác, sẽ query riêng */
            0 as CSD_SL_thuc_te, 0 as CSD_SL_KH,
            0 as FS_SL_thuc_te, 0 as FS_SL_KH
            
        FROM OEE 
        $dateRangeQuery
        ORDER BY Time DESC
        $limitClause
    )
    
    SELECT 
        period as label,
        
        /* Tính tổng sản lượng thực tế và kế hoạch của từng line */
        SUM(COALESCE(L1_Tong_Goi, 0)) as L1_actual,
        SUM(COALESCE(L1_SL_KH, 0)) as L1_plan,
        SUM(COALESCE(L2_Tong_Goi, 0)) as L2_actual,
        SUM(COALESCE(L2_SL_KH, 0)) as L2_plan,
        SUM(COALESCE(L3_Tong_Goi, 0)) as L3_actual,
        SUM(COALESCE(L3_SL_KH, 0)) as L3_plan,
        SUM(COALESCE(L4_Tong_Goi, 0)) as L4_actual,
        SUM(COALESCE(L4_SL_KH, 0)) as L4_plan,
        
        SUM(COALESCE(L5_Tong_Goi, 0)) as L5_actual,
        SUM(COALESCE(L5_SL_KH, 0)) as L5_plan,
        SUM(COALESCE(L6_Tong_Goi, 0)) as L6_actual,
        SUM(COALESCE(L6_SL_KH, 0)) as L6_plan,
        SUM(COALESCE(L7_Tong_Goi, 0)) as L7_actual,
        SUM(COALESCE(L7_SL_KH, 0)) as L7_plan,
        SUM(COALESCE(L8_Tong_Goi, 0)) as L8_actual,
        SUM(COALESCE(L8_SL_KH, 0)) as L8_plan,
        
        /* CSD và FS được lấy từ query riêng */
        0 as CSD_actual,
        0 as CSD_plan,
        0 as FS_actual,
        0 as FS_plan,
        
        /* OEE Xưởng F2 (Line 1-4) */
        ROUND(
            CASE 
                WHEN SUM(COALESCE(L1_SL_KH, 0) + COALESCE(L2_SL_KH, 0) + 
                        COALESCE(L3_SL_KH, 0) + COALESCE(L4_SL_KH, 0)) > 0 
                THEN (
                    SUM(COALESCE(L1_Tong_Goi, 0) + COALESCE(L2_Tong_Goi, 0) + 
                        COALESCE(L3_Tong_Goi, 0) + COALESCE(L4_Tong_Goi, 0)) * 100.0
                ) / SUM(COALESCE(L1_SL_KH, 0) + COALESCE(L2_SL_KH, 0) + 
                       COALESCE(L3_SL_KH, 0) + COALESCE(L4_SL_KH, 0))
                ELSE 0
            END,
        2) as f2_oee,
        
        /* OEE Xưởng F3 (Line 5-8) */
        ROUND(
            CASE 
                WHEN SUM(COALESCE(L5_SL_KH, 0) + COALESCE(L6_SL_KH, 0) + 
                        COALESCE(L7_SL_KH, 0) + COALESCE(L8_SL_KH, 0)) > 0 
                THEN (
                    SUM(COALESCE(L5_Tong_Goi, 0) + COALESCE(L6_Tong_Goi, 0) + 
                        COALESCE(L7_Tong_Goi, 0) + COALESCE(L8_Tong_Goi, 0)) * 100.0
                ) / SUM(COALESCE(L5_SL_KH, 0) + COALESCE(L6_SL_KH, 0) + 
                       COALESCE(L7_SL_KH, 0) + COALESCE(L8_SL_KH, 0))
                ELSE 0
            END,
        2) as f3_oee
        
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

    $detailResult = $conn->query($detailQuery);
    
    if (!$detailResult) {
        throw new Exception("Detail query error: " . $conn->error);
    }

    // Truy vấn dữ liệu F1 (CSD và FS)
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
                    CONCAT('Tuần ', FLOOR(DATEDIFF(Time, 
                    DATE_FORMAT(Time, '%Y-%m-01')) / 7) + 1)
            END as period,
            CSD_SL_thuc_te, CSD_SL_KH,
            FS_SL_thuc_te, FS_SL_KH
        FROM OEE 
        $dateRangeQuery
        ORDER BY Time DESC
        $limitClause
    )
    SELECT 
        period as label,
        SUM(COALESCE(CSD_SL_thuc_te, 0)) as CSD_actual,
        SUM(COALESCE(CSD_SL_KH, 0)) as CSD_plan,
        SUM(COALESCE(FS_SL_thuc_te, 0)) as FS_actual,
        SUM(COALESCE(FS_SL_KH, 0)) as FS_plan,
        ROUND(
            CASE 
                WHEN SUM(COALESCE(CSD_SL_KH, 0) + COALESCE(FS_SL_KH, 0)) > 0 
                THEN (
                    SUM(COALESCE(CSD_SL_thuc_te, 0) + COALESCE(FS_SL_thuc_te, 0)) * 100.0
                ) / SUM(COALESCE(CSD_SL_KH, 0) + COALESCE(FS_SL_KH, 0))
                ELSE 0
            END,
        2) as f1_oee,
        ROUND(
            CASE 
                WHEN SUM(COALESCE(CSD_SL_KH, 0)) > 0 
                THEN (
                    SUM(COALESCE(CSD_SL_thuc_te, 0)) * 100.0
                ) / SUM(COALESCE(CSD_SL_KH, 0))
                ELSE 0
            END,
        2) as csd_oee,
        ROUND(
            CASE 
                WHEN SUM(COALESCE(FS_SL_KH, 0)) > 0 
                THEN (
                    SUM(COALESCE(FS_SL_thuc_te, 0)) * 100.0
                ) / SUM(COALESCE(FS_SL_KH, 0))
                ELSE 0
            END,
        2) as fs_oee
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

    // Kết hợp dữ liệu chi tiết từ tất cả các line
    $periods = [];
    $f1Data = [];
    
    // Lưu dữ liệu F1 vào mảng
    while ($row = $f1Result->fetch_assoc()) {
        $f1Data[$row['label']] = [
            'CSD_actual' => floatval($row['CSD_actual']),
            'CSD_plan' => floatval($row['CSD_plan']), 
            'FS_actual' => floatval($row['FS_actual']),
            'FS_plan' => floatval($row['FS_plan']),
            'f1_oee' => floatval($row['f1_oee']),
            'csd_oee' => floatval($row['csd_oee']),
            'fs_oee' => floatval($row['fs_oee'])
        ];
    }
    
    // Kết hợp dữ liệu chi tiết các line và F1
    while ($row = $detailResult->fetch_assoc()) {
        $label = $row['label'];
        
        // Lấy dữ liệu F1 nếu có
        $csdActual = isset($f1Data[$label]) ? $f1Data[$label]['CSD_actual'] : 0;
        $csdPlan = isset($f1Data[$label]) ? $f1Data[$label]['CSD_plan'] : 0;
        $fsActual = isset($f1Data[$label]) ? $f1Data[$label]['FS_actual'] : 0;
        $fsPlan = isset($f1Data[$label]) ? $f1Data[$label]['FS_plan'] : 0;
        $f1Oee = isset($f1Data[$label]) ? $f1Data[$label]['f1_oee'] : 0;
        $csdOee = isset($f1Data[$label]) ? $f1Data[$label]['csd_oee'] : 0;
        $fsOee = isset($f1Data[$label]) ? $f1Data[$label]['fs_oee'] : 0;
        
        // Tính tổng sản lượng thực tế và kế hoạch của tất cả các line
        $totalActual = 
            floatval($row['L1_actual']) + floatval($row['L2_actual']) + 
            floatval($row['L3_actual']) + floatval($row['L4_actual']) +
            floatval($row['L5_actual']) + floatval($row['L6_actual']) + 
            floatval($row['L7_actual']) + floatval($row['L8_actual']) +
            $csdActual + $fsActual;
            
        $totalPlan = 
            floatval($row['L1_plan']) + floatval($row['L2_plan']) + 
            floatval($row['L3_plan']) + floatval($row['L4_plan']) +
            floatval($row['L5_plan']) + floatval($row['L6_plan']) + 
            floatval($row['L7_plan']) + floatval($row['L8_plan']) +
            $csdPlan + $fsPlan;
        
        // Tính OEE toàn nhà máy
        $factoryOeeValue = ($totalPlan > 0) ? round(($totalActual * 100.0) / $totalPlan, 2) : 0;
        
        $periods[$label] = [
            'f2_oee' => floatval($row['f2_oee']),
            'f3_oee' => floatval($row['f3_oee']),
            'f1_oee' => $f1Oee,
            'csd_oee' => $csdOee,
            'fs_oee' => $fsOee,
            'factory_oee' => $factoryOeeValue
        ];
    }

    // Sắp xếp lại mảng theo thời gian
    ksort($periods);

    // Tạo mảng kết quả
    $dates = [];
    $f2OEE = [];
    $f3OEE = [];
    $f1OEE = [];
    $csdOEE = [];
    $fsOEE = [];
    $factoryOEE = [];

    foreach ($periods as $label => $values) {
        $dates[] = $label;
        $f2OEE[] = $values['f2_oee'];
        $f3OEE[] = $values['f3_oee'];
        $f1OEE[] = $values['f1_oee'];
        $csdOEE[] = $values['csd_oee'];
        $fsOEE[] = $values['fs_oee'];
        $factoryOEE[] = $values['factory_oee'];
    }

    echo json_encode([
        'dates' => $dates,
        'f2_oee' => $f2OEE,
        'f3_oee' => $f3OEE,
        'f1_oee' => $f1OEE,
        'csd_oee' => $csdOEE,
        'fs_oee' => $fsOEE,
        'factory_oee' => $factoryOEE,
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