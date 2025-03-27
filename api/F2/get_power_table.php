<?php
header('Content-Type: application/json');
require_once '../../config/database.php';
require_once '../../includes/functions.php';

$period = isset($_GET['period']) ? $_GET['period'] : 'today';

try {
    $dateRangeQuery = getDateRangeQuery($period);
    
    $query = "SELECT 
        Time,
             COALESCE(F2_KS_chiller, 0) as KS_CL,
             COALESCE(F2_Nem, 0) as Nem,
             COALESCE(F2_MNK, 0) as mnk,
             COALESCE(AHU_Mi, 0) as ahu_chiller,
             COALESCE(F2_DH_L4, 0) as F2_DH_L4,
             COALESCE(F2_Line1, 0) as line1,
             COALESCE(F2_Line2, 0) as line2,
             COALESCE(F2_Line3, 0) as line3,
             COALESCE(F2_Line4, 0) as line4,
             COALESCE(F2_Tong, 0) as total
    FROM So_dien_F2
    $dateRangeQuery
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
            'KS_CL' => floatval($row['thong_gio']),
            'Nem' => floatval($row['van_phong']),
            'mnk' => floatval($row['mnk']),
            'ahu_chiller' => floatval($row['ahu_chiller']),
            'F2_DH_L4' => floatval($row['F2_DH_L4']),
            'line1' => floatval($row['line1']),
            'line2' => floatval($row['line2']),
            'line3' => floatval($row['line3']),
            'line4' => floatval($row['line4']),
            'total' => floatval($row['total'])
        ];
    }

    echo json_encode(['data' => $data]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'error' => true,
        'message' => $e->getMessage()
    ]);
}
?>