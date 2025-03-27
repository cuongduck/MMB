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
    ORDER BY Time ASC";

    $result = $conn->query($query);
    
    if (!$result) {
        throw new Exception($conn->error);
    }

    $data = [];
    while ($row = $result->fetch_assoc()) {
        $time = $row['Time'];
        unset($row['Time']);
        
        // Convert all values to float
        $row = array_map('floatval', $row);
        
        $data[] = [
            'time' => $time,
            ...$row
        ];
    }

    echo json_encode([
        'labels' => [
            'KS_chiller', 'Nem', 'MNK', 'AHU_Mi', 
            'F2_DH_L4', 'Line 1', 'Line 2', 'Line 3', 'Line 4'
        ],
        'datasets' => $data
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'error' => true,
        'message' => $e->getMessage()
    ]);
}
?>