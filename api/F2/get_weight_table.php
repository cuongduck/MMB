<?php
header('Content-Type: application/json');
require_once '../../config/database.php';
require_once '../../includes/functions.php';

$period = isset($_GET['period']) ? $_GET['period'] : 'today';

try {
    $dateRangeQuery = getDateRangeQuery($period);
    
     $query = "SELECT 
    Time,
    L1_ten_SP,
    CASE 
        WHEN COUNT(CASE WHEN L1_TLTB != 0 THEN 1 END) > 0
        THEN AVG(CASE WHEN L1_TLTB != 0 THEN L1_TLTB END)
        ELSE 0 
    END as L1_TLTB,
    L1_TL_Chuan,
    CASE 
        WHEN L1_TLTB = 0 OR L1_TL_Chuan = 0 THEN 0 
        ELSE (L1_TLTB - L1_TL_Chuan) 
    END as Chenh_lech_L1,
    
    L2_ten_SP,
    CASE 
        WHEN COUNT(CASE WHEN L2_TLTB != 0 THEN 1 END) > 0
        THEN AVG(CASE WHEN L2_TLTB != 0 THEN L2_TLTB END)
        ELSE 0 
    END as L2_TLTB,
    L2_TL_Chuan,
    CASE 
        WHEN L2_TLTB = 0 OR L2_TL_Chuan = 0 THEN 0 
        ELSE (L2_TLTB - L2_TL_Chuan) 
    END as Chenh_lech_L2,
    
    L3_ten_SP,
    CASE 
        WHEN COUNT(CASE WHEN L3_TLTB != 0 THEN 1 END) > 0
        THEN AVG(CASE WHEN L3_TLTB != 0 THEN L3_TLTB END)
        ELSE 0 
    END as L3_TLTB,
    L3_TL_Chuan,
    CASE 
        WHEN L3_TLTB = 0 OR L3_TL_Chuan = 0 THEN 0 
        ELSE (L3_TLTB - L3_TL_Chuan) 
    END as Chenh_lech_L3,
    
    L4_ten_SP,
    CASE 
        WHEN COUNT(CASE WHEN L4_TLTB != 0 THEN 1 END) > 0
        THEN AVG(CASE WHEN L4_TLTB != 0 THEN L4_TLTB END)
        ELSE 0 
    END as L4_TLTB,
    L4_TL_Chuan,
    CASE 
        WHEN L4_TLTB = 0 OR L4_TL_Chuan = 0 THEN 0 
        ELSE (L4_TLTB - L4_TL_Chuan) 
    END as Chenh_lech_L4
FROM `OEE`
$dateRangeQuery
GROUP BY Time
ORDER BY Time DESC";

    $result = $conn->query($query);
    
    if (!$result) {
        throw new Exception($conn->error);
    }

       $data = [];
    while ($row = $result->fetch_assoc()) {
        $time = $row['Time'];
        $data[] = [
            'time' => $time,
            'L1_sp' => $row['L1_ten_SP'],
            'L1_chuan' => floatval($row['L1_TL_Chuan']),
            'L1_TLTB' => floatval($row['L1_TLTB']),
            'L1_chenh_lech' => floatval($row['Chenh_lech_L1']),
            'L2_sp' => $row['L2_ten_SP'],
            'L2_chuan' => floatval($row['L2_TL_Chuan']),
            'L2_TLTB' => floatval($row['L2_TLTB']),
            'L2_chenh_lech' => floatval($row['Chenh_lech_L2']),
            'L3_sp' => $row['L3_ten_SP'],
            'L3_chuan' => floatval($row['L3_TL_Chuan']),
            'L3_TLTB' => floatval($row['L3_TLTB']),
            'L3_chenh_lech' => floatval($row['Chenh_lech_L3']),
            'L4_sp' => $row['L4_ten_SP'],
            'L4_chuan' => floatval($row['L4_TL_Chuan']),
            'L4_TLTB' => floatval($row['L4_TLTB']),
            'L4_chenh_lech' => floatval($row['Chenh_lech_L4']),
        ];
    }

    echo json_encode([
        'data' => $data
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'error' => true,
        'message' => $e->getMessage()
    ]);
}
?>