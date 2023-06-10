<?php

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Access-Control-Allow-Methods, Authorization, X-Requested-With");

$data = json_decode(file_get_contents('php://input'));

include '../connection.php';

$user_id = $data->user_id;
$type = $data->type;
$date = $data->date ?? null;

$histories = "SELECT id, type, date, total FROM histories
            WHERE user_id = ?
            AND
            type = ?
            ";

if ($date) {
    $histories .= " AND date = ?";
}

$histories .= " ORDER BY date DESC";

$stmt = $conn->prepare($histories);
if ($date) {
    $stmt->bind_param('iss', $user_id, $type, $date);
} else if ($date == null) {
    $stmt->bind_param('is', $user_id, $type);
}
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $historiesData = [];

    while ($historyRows = $result->fetch_assoc()) {
        $historiesData[] = $historyRows;
    }

    $stmt->close();

    echo json_encode([
        'success' => true,
        'message' => $type == 'Pemasukan' ? 'Successfully fetched all the incomes' : 'Successfully fetched all the outcomes',
        'data' => $historiesData
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => $type == 'Pemasukan' ? 'There\'s no income histories' : 'There\'s no outcome histories',
        'data' => null
    ]);
}
