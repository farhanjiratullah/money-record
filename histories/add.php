<?php

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Access-Control-Allow-Methods, Authorization, X-Requested-With");

$data = json_decode(file_get_contents('php://input'));

include '../connection.php';

$user_id = $data->user_id;
$type = $data->type;
$date = $data->date;
$total = $data->total;
$details = json_encode($data->details);
$created_at = date('Y-m-d H:i:s');
$updated_at = date('Y-m-d H:i:s');

$incomeOutcomeTodayCheck = "SELECT * FROM histories
                            WHERE user_id = ?
                            AND
                            date = ?
                            ";

$stmt = $conn->prepare($incomeOutcomeTodayCheck);
$stmt->bind_param('is', $user_id, $date);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $stmt->close();

    echo json_encode([
        "success" => false,
        'message' => 'You can\'t add income or outcome for today',
        "data" => null
    ]);
} else {
    $history = "INSERT INTO histories
                SET
                user_id = ?,
                type = ?,
                date = ?,
                total = ?,
                details = ?,
                created_at = '$created_at',
                updated_at = '$updated_at'
                ";

    $stmt = $conn->prepare($history);
    $stmt->bind_param('issis', $user_id, $type, $date, $total, $details);
    $result = $stmt->execute();

    if ($result) {
        $stmt->close();

        echo json_encode([
            "success" => false,
            'message' => $type == 'Pemasukan' ? "Successfully created a new income" : "Successfully created a new outcome",
            "data" => null
        ]);
    } else {
        echo json_encode([
            "success" => false,
            'message' => 'Query result error.',
            "data" => null
        ]);
    }
}
