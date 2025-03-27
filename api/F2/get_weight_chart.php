<?php
header('Content-Type: application/json');
require_once '../../config/database.php';
require_once '../../includes/functions.php';

$type = isset($_GET['type']) ? $_GET['type'] : '5min';

try {
    if ($type === 'shift') {
        // Truy vấn theo ca, bỏ qua giá trị 0 khi tính trung bình
        $query = "SELECT 
            CASE
                WHEN (TIME(Time) >= '06:35:00' AND TIME(Time) < '15:35:00') THEN 'Ca 1'
                WHEN (TIME(Time) >= '15:50:00' AND TIME(Time) < '23:35:00') THEN 'Ca 2'
                WHEN ((TIME(Time) >= '23:36:00' AND TIME(Time) <= '23:59:59') OR
                      (TIME(Time) >= '00:00:00' AND TIME(Time) < '06:35:00')) THEN 'Ca 3'
            END as Shift,
            AVG(CASE WHEN L1_TLTB > 0 THEN L1_TLTB END) as L1_TLTB,
            AVG(CASE WHEN L1_TL_Chuan > 0 THEN L1_TL_Chuan END) as L1_TL_Chuan,
            AVG(CASE WHEN L2_TLTB > 0 THEN L2_TLTB END) as L2_TLTB,
            AVG(CASE WHEN L2_TL_Chuan > 0 THEN L2_TL_Chuan END) as L2_TL_Chuan,
            AVG(CASE WHEN L3_TLTB > 0 THEN L3_TLTB END) as L3_TLTB,
            AVG(CASE WHEN L3_TL_Chuan > 0 THEN L3_TL_Chuan END) as L3_TL_Chuan,
            AVG(CASE WHEN L4_TLTB > 0 THEN L4_TLTB END) as L4_TLTB,
            AVG(CASE WHEN L4_TL_Chuan > 0 THEN L4_TL_Chuan END) as L4_TL_Chuan
        FROM OEE 
        WHERE Time >= CURDATE()
        GROUP BY 
            CASE
                WHEN (TIME(Time) >= '06:35:00' AND TIME(Time) < '15:35:00') THEN 'Ca 1'
                WHEN (TIME(Time) >= '15:50:00' AND TIME(Time) < '23:35:00') THEN 'Ca 2'
                WHEN ((TIME(Time) >= '23:36:00' AND TIME(Time) <= '23:59:59') OR
                      (TIME(Time) >= '00:00:00' AND TIME(Time) < '06:35:00')) THEN 'Ca 3'
            END
        ORDER BY FIELD(Shift, 'Ca 1', 'Ca 2', 'Ca 3')";
    } else if ($type === 'hour') {
        // Truy vấn theo giờ, bỏ qua giá trị 0
        $query = "SELECT 
            Time,
            L1_TLTB, L1_TL_Chuan,
            L2_TLTB, L2_TL_Chuan,
            L3_TLTB, L3_TL_Chuan,
            L4_TLTB, L4_TL_Chuan
        FROM OEE 
        WHERE L1_TLTB > 0 OR L2_TLTB > 0 OR L3_TLTB > 0 OR L4_TLTB > 0 
        ORDER BY id DESC 
        LIMIT 12";
    } else {
        // Truy vấn mặc định 5 phút
        $query = "SELECT 
            Time,
            L1_TLTB, L1_TL_Chuan,
            L2_TLTB, L2_TL_Chuan,
            L3_TLTB, L3_TL_Chuan,
            L4_TLTB, L4_TL_Chuan
        FROM TLTB 
        ORDER BY Time DESC 
        LIMIT 15";
    }

    $result = $conn->query($query);
    
    if (!$result) {
        throw new Exception($conn->error);
    }

    $data = [];
    if ($type === 'shift') {
        while ($row = $result->fetch_assoc()) {
            $data[] = [
                'shift' => $row['Shift'],
                'L1' => [
                    'actual' => $row['L1_TLTB'] ? round(floatval($row['L1_TLTB']), 2) : 0,
                    'target' => $row['L1_TL_Chuan'] ? round(floatval($row['L1_TL_Chuan']), 2) : 0
                ],
                'L2' => [
                    'actual' => $row['L2_TLTB'] ? round(floatval($row['L2_TLTB']), 2) : 0,
                    'target' => $row['L2_TL_Chuan'] ? round(floatval($row['L2_TL_Chuan']), 2) : 0
                ],
                'L3' => [
                    'actual' => $row['L3_TLTB'] ? round(floatval($row['L3_TLTB']), 2) : 0,
                    'target' => $row['L3_TL_Chuan'] ? round(floatval($row['L3_TL_Chuan']), 2) : 0
                ],
                'L4' => [
                    'actual' => $row['L4_TLTB'] ? round(floatval($row['L4_TLTB']), 2) : 0,
                    'target' => $row['L4_TL_Chuan'] ? round(floatval($row['L4_TL_Chuan']), 2) : 0
                ]
            ];
        }
    } else {
        while ($row = $result->fetch_assoc()) {
            $data[] = [
                'time' => date('H:i', strtotime($row['Time'])),
                'L1' => [
                    'actual' => floatval($row['L1_TLTB']),
                    'target' => floatval($row['L1_TL_Chuan'])
                ],
                'L2' => [
                    'actual' => floatval($row['L2_TLTB']),
                    'target' => floatval($row['L2_TL_Chuan'])
                ],
                'L3' => [
                    'actual' => floatval($row['L3_TLTB']),
                    'target' => floatval($row['L3_TL_Chuan'])
                ],
                'L4' => [
                    'actual' => floatval($row['L4_TLTB']),
                    'target' => floatval($row['L4_TL_Chuan'])
                ]
            ];
        }
        $data = array_reverse($data);
    }

    echo json_encode($data);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'error' => true,
        'message' => $e->getMessage()
    ]);
}
?>