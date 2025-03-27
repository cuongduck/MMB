<?php
header('Content-Type: application/json');
require_once '../../config/database_F1.php';
require_once '../../includes/functions.php';

$period = isset($_GET['period']) ? $_GET['period'] : 'today';

try {
    $dateRangeQuery = getDateRangeQuery($period);
    
    $query = "SELECT 
        Time,
    COALESCE(CSD_RO, 0) as RO,
    COALESCE(PET_BlowMoulder, 0) as PET_Blow,
    COALESCE(PET_Chiller, 0) as PET_Chiller,
    COALESCE(F1_DG_CSD, 0) as DG_CSD,
    COALESCE(F1_MCC_CSD, 0) as MCC_CSD,
    COALESCE(F1_MNK_PET, 0) as MNK_Pet,
    COALESCE(F1_ChillerCSD, 0) as CSD_Chiller,
    COALESCE(F1_DieuHoa_CSD, 0) as CSD_DH,
    COALESCE(F1_MNK_75, 0) as MNK_75,
    COALESCE(CSD_AHU, 0) as CSD_AHU,
    COALESCE(F1_Kho, 0) as Kho,
    COALESCE(F1_VP, 0) as VP,
    COALESCE(F1_MDB2_1_1, 0) as total
FROM CSD_So_Dien
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
                       'RO', 'PET_Blow', 'PET_Chiller', 'DG_CSD', 
            'VP', 'MCC_CSD', 'MNK_Pet', 'CSD_Chiller', 'CSD_DH',
            'MNK_75', 'CSD_AHU', 'Kho'
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