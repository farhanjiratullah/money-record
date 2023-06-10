<?php

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Access-Control-Allow-Methods, Authorization, X-Requested-With");

$data = json_decode(file_get_contents('php://input'));

include '../connection.php';

$user_id = $data->user_id;
$date = $data->date;

$history = "SELECT * FROM histories
            WHERE user_id = ?
            AND
            date = ?";

$stmt = $conn->prepare($history);
$stmt->bind_param('is', $user_id, $date);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $historyData = [];

    while ($historyRows = $result->fetch_assoc()) {
        $historyData[] = $historyRows;
    }

    $stmt->close();

    echo json_encode([
        'success' => true,
        'message' => 'Successfully fetched the history',
        'data' => $historyData[0]
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'History not found.',
        'data' => null
    ]);
}
