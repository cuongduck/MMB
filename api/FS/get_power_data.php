<?php
header('Content-Type: application/json');
require_once '../../config/database_F1.php';
require_once '../../includes/functions.php';

$period = isset($_GET['period']) ? $_GET['period'] : 'today';

try {
    $dateRangeQuery = getDateRangeQuery($period);
    
    $query = "SELECT 
        SUM(COALESCE(F1_DongGoi_Mam, 0)) as DG,
        SUM(COALESCE(F1_Total_MuoiCot, 0)) Muoi_cot,
        SUM(COALESCE(FS_Cooling, 0)) as Cooling,
        SUM(COALESCE(FS_CB, 0)) as Che_bien,
        SUM(COALESCE(F1_MNK_100, 0)) as MNK_100,
        SUM(COALESCE(FS_DieuHoa_CB, 0)) as DH_Che_bien,
        SUM(COALESCE(F1_VP, 0)) as VP,
        SUM(COALESCE(F1_Kho, 0)) as Kho,
        SUM(COALESCE(FS_Tong, 0)) as total
    FROM FS_So_Dien
    $dateRangeQuery";

    $result = $conn->query($query);
    
    if (!$result) {
        throw new Exception($conn->error);
    }

    $data = $result->fetch_assoc();
    
    // Chuy??n t??t c?? giив tr?? sang float
    $data = array_map('floatval', $data);
    
    // L??y total 
    $total = $data['total'];
    unset($data['total']);
    
    // L??y giив tr?? cho bi??u ????
    $values = array_values($data);
    
    // Tикnh ph??n tr??m d??a trи║n t??ng 
    $percentages = array_map(function($value) use ($total) {
        return $total > 0 ? round(($value / $total) * 100, 1) : 0;
    }, $values);

    $response = [
        'labels' => [
            'DG', 'Muoi_cot', 'Cooling', 'Che_bien', 
            'MNK_100', 'DH_Che_bien', 'VP', 'Kho'
        ],
        'values' => $values,
        'total' => $total,
        'percentages' => $percentages
    ];

    echo json_encode($response);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'error' => true,
        'message' => $e->getMessage()
    ]);
}
?>