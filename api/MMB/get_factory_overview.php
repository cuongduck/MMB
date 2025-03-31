
<?php
require_once '../../config/database.php';      // Database chính
require_once '../../config/database_F1.php';   // Database F1 cho CSD và FS
require_once '../../includes/functions.php';
header('Content-Type: application/json');

try {
    $period = isset($_GET['period']) ? $_GET['period'] : 'today';

    // Lấy điều kiện thời gian từ functions.php
    $dateRangeQuery = getDateRangeQuery($period);
    $dateRangeQueryAnd = str_replace('WHERE', 'AND', $dateRangeQuery);

    // ----------------- LẤY DỮ LIỆU F2 -------------------
    $f2Query = "SELECT 
        -- Tổng sản lượng
        SUM(COALESCE(L1_Tong_Goi, 0) + COALESCE(L2_Tong_Goi, 0) + COALESCE(L3_Tong_Goi, 0) + COALESCE(L4_Tong_Goi, 0)) as production,
        SUM(COALESCE(L1_SL_KH, 0) + COALESCE(L2_SL_KH, 0) + COALESCE(L3_SL_KH, 0) + COALESCE(L4_SL_KH, 0)) as production_plan,
        
        -- Tổng OEE
        ROUND(
            CASE 
                WHEN SUM(COALESCE(L1_SL_KH, 0) + COALESCE(L2_SL_KH, 0) + COALESCE(L3_SL_KH, 0) + COALESCE(L4_SL_KH, 0)) > 0 
                THEN (
                    SUM(COALESCE(L1_Tong_Goi, 0) + COALESCE(L2_Tong_Goi, 0) + COALESCE(L3_Tong_Goi, 0) + COALESCE(L4_Tong_Goi, 0)) * 100.0
                ) / SUM(COALESCE(L1_SL_KH, 0) + COALESCE(L2_SL_KH, 0) + COALESCE(L3_SL_KH, 0) + COALESCE(L4_SL_KH, 0))
                ELSE 0 
            END,
        2) as oee,
        
        -- Tổng tiêu hao hơi
        ROUND(
            CASE 
                WHEN SUM(COALESCE(L1_Tong_Goi, 0) + COALESCE(L2_Tong_Goi, 0) + COALESCE(L3_Tong_Goi, 0)) > 0 
                THEN (
                    SUM(COALESCE(L1_Hap, 0) + COALESCE(L1_Chien, 0) + 
                        COALESCE(L2_Hap, 0) + COALESCE(L2_Chien, 0) + 
                        COALESCE(L3_Hap, 0) + COALESCE(L3_Chien, 0)) * 1000.0
                ) / SUM(COALESCE(L1_Tong_Goi, 0) + COALESCE(L2_Tong_Goi, 0) + COALESCE(L3_Tong_Goi, 0))
                ELSE 0 
            END,
        2) as steam
    FROM OEE 
    $dateRangeQuery";
    
    $f2Result = $conn->query($f2Query);
    if (!$f2Result) {
        throw new Exception("F2 data query error: " . $conn->error);
    }
    
    $f2Data = $f2Result->fetch_assoc();
    
    // Điện năng F2 từ bảng So_dien_F2
    $f2PowerQuery = "SELECT 
        COALESCE(SUM(COALESCE(F2_Tong, 0)), 0) as power
    FROM So_dien_F2 
    $dateRangeQuery";
    
    $f2PowerResult = $conn->query($f2PowerQuery);
    if (!$f2PowerResult) {
        throw new Exception("F2 power query error: " . $conn->error);
    }
    
    $f2PowerData = $f2PowerResult->fetch_assoc();
    $f2Data['power'] = $f2PowerData['power'];
    
    // ----------------- LẤY DỮ LIỆU F3 -------------------
    $f3Query = "SELECT 
        -- Tổng sản lượng
        SUM(COALESCE(L5_Tong_Goi, 0) + COALESCE(L6_Tong_Goi, 0) + COALESCE(L7_Tong_Goi, 0) + COALESCE(L8_Tong_Goi, 0)) as production,
        SUM(COALESCE(L5_SL_KH, 0) + COALESCE(L6_SL_KH, 0) + COALESCE(L7_SL_KH, 0) + COALESCE(L8_SL_KH, 0)) as production_plan,
        
        -- Tổng OEE
        ROUND(
            CASE 
                WHEN SUM(COALESCE(L5_SL_KH, 0) + COALESCE(L6_SL_KH, 0) + COALESCE(L7_SL_KH, 0) + COALESCE(L8_SL_KH, 0)) > 0 
                THEN (
                    SUM(COALESCE(L5_Tong_Goi, 0) + COALESCE(L6_Tong_Goi, 0) + COALESCE(L7_Tong_Goi, 0) + COALESCE(L8_Tong_Goi, 0)) * 100.0
                ) / SUM(COALESCE(L5_SL_KH, 0) + COALESCE(L6_SL_KH, 0) + COALESCE(L7_SL_KH, 0) + COALESCE(L8_SL_KH, 0))
                ELSE 0 
            END,
        2) as oee,
        
        -- Tổng tiêu hao hơi
        ROUND(
            CASE 
                WHEN SUM(COALESCE(L5_Tong_Goi, 0) + COALESCE(L6_Tong_Goi, 0)) > 0 
                THEN (
                    SUM(COALESCE(L5_Hap, 0) + COALESCE(L5_Chien, 0) + 
                        COALESCE(L6_Hap, 0) + COALESCE(L6_Chien, 0)) * 1000.0
                ) / SUM(COALESCE(L5_Tong_Goi, 0) + COALESCE(L6_Tong_Goi, 0))
                ELSE 0 
            END,
        2) as steam
    FROM OEE 
    $dateRangeQuery";
    
    $f3Result = $conn->query($f3Query);
    if (!$f3Result) {
        throw new Exception("F3 data query error: " . $conn->error);
    }
    
    $f3Data = $f3Result->fetch_assoc();
    
    // Điện năng F3 từ bảng So_dien_F3
    $f3PowerQuery = "SELECT 
        COALESCE(SUM(COALESCE(F3_TramDien_Tong, 0)), 0) as power
    FROM So_dien_F3 
    $dateRangeQuery";
    
    $f3PowerResult = $conn->query($f3PowerQuery);
    if (!$f3PowerResult) {
        throw new Exception("F3 power query error: " . $conn->error);
    }
    
    $f3PowerData = $f3PowerResult->fetch_assoc();
    $f3Data['power'] = $f3PowerData['power'];
    
    // ----------------- LẤY DỮ LIỆU CSD -------------------
    $csdQuery = "SELECT 
        -- Tổng sản lượng
        SUM(COALESCE(CSD_SL_thuc_te, 0)) as production,
        SUM(COALESCE(CSD_SL_KH, 0)) as production_plan,
        
        -- Tổng OEE
        ROUND(
            CASE 
                WHEN SUM(COALESCE(CSD_SL_KH, 0)) > 0 
                THEN (
                    SUM(COALESCE(CSD_SL_thuc_te, 0)) * 100.0
                ) / SUM(COALESCE(CSD_SL_KH, 0))
                ELSE 0 
            END,
        2) as oee,
        
        -- Tổng tiêu hao hơi
        ROUND(
            CASE 
                WHEN SUM(COALESCE(CSD_SL_thuc_te, 0)) > 0 
                THEN (SUM(COALESCE(CSD_hoi, 0)) * 1000.0) / (SUM(COALESCE(CSD_SL_thuc_te, 0)) * 0.33)
                ELSE 0 
            END,
        2) as steam
    FROM OEE 
    $dateRangeQuery";
    
    $csdResult = $conn_F1->query($csdQuery);
    if (!$csdResult) {
        throw new Exception("CSD data query error: " . $conn_F1->error);
    }
    
    $csdData = $csdResult->fetch_assoc();
    
    // Điện năng CSD từ bảng CSD_So_Dien
    $csdPowerQuery = "SELECT 
        COALESCE(SUM(COALESCE(F1_MDB2_1_1, 0)), 0) as power
    FROM CSD_So_Dien 
    $dateRangeQuery";
    
    $csdPowerResult = $conn_F1->query($csdPowerQuery);
    if (!$csdPowerResult) {
        throw new Exception("CSD power query error: " . $conn_F1->error);
    }
    
    $csdPowerData = $csdPowerResult->fetch_assoc();
    $csdData['power'] = $csdPowerData['power'];
    
    // ----------------- LẤY DỮ LIỆU FS -------------------
    $fsQuery = "SELECT 
        -- Tổng sản lượng
        SUM(COALESCE(FS_SL_thuc_te, 0)) as production,
        SUM(COALESCE(FS_SL_KH, 0)) as production_plan,
        
        -- Tổng OEE
        ROUND(
            CASE 
                WHEN SUM(COALESCE(FS_SL_KH, 0)) > 0 
                THEN (
                    SUM(COALESCE(FS_SL_thuc_te, 0)) * 100.0
                ) / SUM(COALESCE(FS_SL_KH, 0))
                ELSE 0 
            END,
        2) as oee,
        
        -- Tổng tiêu hao hơi
        ROUND(
            CASE 
                WHEN SUM(COALESCE(FS_SL_thuc_te, 0)) > 0 
                THEN (SUM(COALESCE(FS_hoi, 0)) * 1000.0) / (SUM(COALESCE(FS_SL_thuc_te, 0)) * 0.33)
                ELSE 0 
            END,
        2) as steam
    FROM OEE 
    $dateRangeQuery";
    
    $fsResult = $conn_F1->query($fsQuery);
    if (!$fsResult) {
        throw new Exception("FS data query error: " . $conn_F1->error);
    }
    
    $fsData = $fsResult->fetch_assoc();
    
    // Điện năng FS từ bảng FS_So_Dien
    $fsPowerQuery = "SELECT 
        COALESCE(SUM(COALESCE(FS_Tong, 0)), 0) as power
    FROM FS_So_Dien 
    $dateRangeQuery";
    
    $fsPowerResult = $conn_F1->query($fsPowerQuery);
    if (!$fsPowerResult) {
        throw new Exception("FS power query error: " . $conn_F1->error);
    }
    
    $fsPowerData = $fsPowerResult->fetch_assoc();
    $fsData['power'] = $fsPowerData['power'];
    
    // KẾT HỢP DỮ LIỆU TỪ TẤT CẢ CÁC XƯỞNG
    $result = [
        // Dữ liệu F2
        'f2_production' => (int)$f2Data['production'],
        'f2_production_plan' => (int)$f2Data['production_plan'],
        'f2_oee' => (float)$f2Data['oee'],
        'f2_steam' => (float)$f2Data['steam'],
        'f2_power' => (float)$f2Data['power'],
        
        // Dữ liệu F3
        'f3_production' => (int)$f3Data['production'],
        'f3_production_plan' => (int)$f3Data['production_plan'],
        'f3_oee' => (float)$f3Data['oee'],
        'f3_steam' => (float)$f3Data['steam'],
        'f3_power' => (float)$f3Data['power'],
        
        // Dữ liệu CSD
        'csd_production' => (int)$csdData['production'],
        'csd_production_plan' => (int)$csdData['production_plan'],
        'csd_oee' => (float)$csdData['oee'],
        'csd_steam' => (float)$csdData['steam'],
        'csd_power' => (float)$csdData['power'],
        
        // Dữ liệu FS
        'fs_production' => (int)$fsData['production'],
        'fs_production_plan' => (int)$fsData['production_plan'],
        'fs_oee' => (float)$fsData['oee'],
        'fs_steam' => (float)$fsData['steam'],
        'fs_power' => (float)$fsData['power'],
        
        // Tổng hợp toàn nhà máy
        'total_production' => (int)$f2Data['production'] + (int)$f3Data['production'] + (int)$csdData['production'] + (int)$fsData['production'],
        'total_production_plan' => (int)$f2Data['production_plan'] + (int)$f3Data['production_plan'] + (int)$csdData['production_plan'] + (int)$fsData['production_plan'],
        'period' => $period
    ];
    
    // Tính OEE trung bình cho toàn nhà máy
    $validOeeCount = 0;
    $totalOee = 0;
    
    if ((float)$f2Data['oee'] > 0) {
        $totalOee += (float)$f2Data['oee'];
        $validOeeCount++;
    }
    
    if ((float)$f3Data['oee'] > 0) {
        $totalOee += (float)$f3Data['oee'];
        $validOeeCount++;
    }
    
    if ((float)$csdData['oee'] > 0) {
        $totalOee += (float)$csdData['oee'];
        $validOeeCount++;
    }
    
    if ((float)$fsData['oee'] > 0) {
        $totalOee += (float)$fsData['oee'];
        $validOeeCount++;
    }
    
    $result['total_oee'] = $validOeeCount > 0 ? round($totalOee / $validOeeCount, 2) : 0;
    
    echo json_encode($result);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'error' => true,
        'message' => $e->getMessage()
    ]);
}
?>