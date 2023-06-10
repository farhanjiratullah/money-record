<?php

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");
header("Access-Control-Allow-Methods: PUT");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Access-Control-Allow-Methods, Authorization, X-Requested-With");

$data = json_decode(file_get_contents('php://input'));

include '../connection.php';

$id = $data->id;
$user_id = $data->user_id;
$type = $data->type;
$date = $data->date;
$total = $data->total;
$details = json_encode($data->details);

$updated_at = date('Y-m-d H:i:s');

$incomeOutcomeDateUpdatedQuery = "SELECT * FROM histories
                            WHERE user_id = ?
                            AND
                            date = ?
                            AND
                            type = ?
                            ";

$stmt = $conn->prepare($incomeOutcomeDateUpdatedQuery);
$stmt->bind_param('iss', $user_id, $date, $type);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 1) {
    $stmt->close();

    echo json_encode([
        "success" => false,
        'message' => 'You can\'t update with that specific date. Because there\'s already existing data',
        "data" => null
    ]);
} else {
    $history = "UPDATE histories
                SET
                user_id = ?,
                type = ?,
                date = ?,
                total = ?,
                details = ?,
                updated_at = '$updated_at'
                WHERE
                id = ?
                ";

    $stmt = $conn->prepare($history);
    $stmt->bind_param('issisi', $user_id, $type, $date, $total, $details, $id);
    $result = $stmt->execute();

    if ($result) {
        $stmt->close();

        echo json_encode([
            "success" => true,
            'message' => $type == 'Pemasukan' ? "Successfully updated the income." : "Successfully updated the outcome.",
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
