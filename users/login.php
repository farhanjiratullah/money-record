<?php

include '../connection.php';

$email = $_POST['email'];
$password = md5($_POST['password']);

$query = "SELECT * FROM users WHERE email = ? AND password = ?";

$stmt = $conn->prepare($query);
$stmt->bind_param('ss', $email, $password);
$stmt->execute();

if ($user = $stmt->get_result()->fetch_assoc()) {
    unset($user['password']);
    $stmt->close();

    echo json_encode([
        "success" => true,
        'message' => 'You have successfully logged in.',
        "data" => $user
    ]);
} else {
    echo json_encode([
        "success" => false,
        'message' => 'You have failed logged in.',
        "data" => null
    ]);
}
