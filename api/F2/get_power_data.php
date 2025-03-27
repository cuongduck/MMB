<?php
header('Content-Type: application/json');
require_once '../../config/database.php';
require_once '../../includes/functions.php';

$period = isset($_GET['period']) ? $_GET['period'] : 'today';

try {
    $dateRangeQuery = getDateRangeQuery($period);
    
    $query = "SELECT 
        SUM(COALESCE(F2_KS_chiller, 0)) as KS_CL,
        SUM(COALESCE(F2_Nem, 0)) as Nem,
        SUM(COALESCE(F2_MNK, 0)) as mnk,
        SUM(COALESCE(AHU_Mi, 0)) as ahu_chiller,
        SUM(COALESCE(F2_DH_L4, 0)) as F2_DH_L4,
        SUM(COALESCE(F2_Line1, 0)) as line1,
        SUM(COALESCE(F2_Line2, 0)) as line2,
        SUM(COALESCE(F2_Line3, 0)) as line3,
        SUM(COALESCE(F2_Line4, 0)) as line4,
        SUM(COALESCE(F2_Tong, 0)) as total
    FROM So_dien_F2
    $dateRangeQuery";

    $result = $conn->query($query);
    
    if (!$result) {
        throw new Exception($conn->error);
    }

    $data = $result->fetch_assoc();
    
    // Chuy??n t??t c?? giив tr?? sang float
    $data = array_map('floatval', $data);
    
    // L??y total t?? F2_TramDien_Tong
    $total = $data['total'];
    unset($data['total']);
    
    // L??y giив tr?? cho bi??u ????
    $values = array_values($data);
    
    // Tикnh ph??n tr??m d??a trи║n t??ng c??a F2_TramDien_Tong
    $percentages = array_map(function($value) use ($total) {
        return $total > 0 ? round(($value / $total) * 100, 1) : 0;
    }, $values);

    $response = [
        'labels' => [
            'KS_chiller', 'Nem', 'MNK', 'AHU_Mi', 
            'F2_DH_L4', 'Line 1', 'Line 2', 'Line 3', 'Line 4'
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