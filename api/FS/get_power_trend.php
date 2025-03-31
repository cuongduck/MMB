<?php
header('Content-Type: application/json');
require_once '../../config/database_F1.php';
require_once '../../includes/functions.php';

$period = isset($_GET['period']) ? $_GET['period'] : 'today';

try {
    $dateRangeQuery = getDateRangeQuery($period);
    
    $query = "SELECT 
        Time,
       COALESCE(F1_DongGoi_Mam, 0) as DG,
        COALESCE(F1_Total_MuoiCot, 0) Muoi_cot,
        COALESCE(FS_Cooling, 0) as Cooling,
        COALESCE(FS_CB, 0) as Che_bien,
        COALESCE(F1_MNK_100, 0) as MNK_100,
        COALESCE(FS_DieuHoa_CB, 0) as DH_Che_bien,
        COALESCE(F1_VP, 0) as VP,
        COALESCE(F1_Kho, 0) as Kho,
        COALESCE(FS_Tong, 0) as total
FROM FS_So_Dien
    $dateRangeQuery
    ORDER BY Time ASC";

    $result = $conn_F1->query($query);
    
    if (!$result) {
        throw new Exception($conn_F1->error);
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
            'DG', 'Muoi_cot', 'Cooling', 'Che_bien', 
            'MNK_100', 'DH_Che_bien', 'VP', 'Kho'
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