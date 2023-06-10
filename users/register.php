<?php

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Access-Control-Allow-Methods, Authorization, X-Requested-With");

$data = json_decode(file_get_contents('php://input'));

include '../connection.php';

$name = $data->name;
$email = $data->email;
$password = $data->password;
$created_at = date('Y-m-d H:i:s');
$updated_at = date('Y-m-d H:i:s');

$email_check = "SELECT email FROM users WHERE email = ?";

$stmt = $conn->prepare($email_check);
$stmt->bind_param('s', $email);
$stmt->execute();

if( $stmt->get_result()->fetch_assoc() ) {
    $stmt->close();
    
    echo json_encode([
        "success" => false,
        'message' => 'The email has already been taken.'
    ]);
} else {
    $errors = [];

    if( !$name ) {
        $errors["name"] = ['The name field is required'];
    }

    if( !$email ) {
        $errors["email"] = ['The email field is required'];
    }

    if( !$password ) {
        $errors["password"] = ['The password field is required'];
    }

    if( !$errors == [] ) {
        echo json_encode([
            "errors" => $errors
        ]);
    } else {
        $password = md5($password);

        $query = "INSERT INTO users (name, email, password, created_at, updated_at) VALUES (?, ?, ?, '$created_at', '$updated_at')";

        $stmt = $conn->prepare($query);
        $stmt->bind_param('sss', $name, $email, $password);
        $result = $stmt->execute();
        $stmt->close();

        if( $result ) {
            $get_email = "SELECT * FROM users WHERE email = ?";

            $stmt = $conn->prepare($get_email);
            $stmt->bind_param('s', $email);
            $stmt->execute();
            $user = $stmt->get_result()->fetch_assoc();
            unset($user['password']);
            $stmt->close();

            echo json_encode([
                "success" => true,
                'message' => 'You have successfully registered.',
                "data" => $user
            ]);
        } else {
            echo json_encode([
                "success" => false,
                'message' => 'An error has occurred. Please try again.',
                "data" => null
            ]);
        }
    }
}