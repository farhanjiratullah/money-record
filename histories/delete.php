<?php

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Access-Control-Allow-Methods, Authorization, X-Requested-With");

$data = json_decode(file_get_contents('php://input'));

include '../connection.php';

$id = $data->id;

$deleteQuery = "DELETE FROM histories
                            WHERE id = ?
                            ";

$stmt = $conn->prepare($deleteQuery);
$stmt->bind_param('i', $id);
$result = $stmt->execute();

if ($result) {
    $stmt->close();

    echo json_encode([
        "success" => true,
        'message' => 'Successfully deleted the history',
        "data" => null
    ]);
} else {
    echo json_encode([
        "success" => false,
        'message' => 'Query result error.',
        "data" => null
    ]);
}
