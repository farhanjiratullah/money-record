<?php

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Access-Control-Allow-Methods, Authorization, X-Requested-With");

$data = json_decode(file_get_contents('php://input'));

include '../connection.php';

$user_id = $data->user_id;
$today = new DateTime($data->today);
$this_month = $today->format('Y-m');

$day7 = $today->format('Y-m');
$day6 = date_sub($today, new DateInterval('P1D'))->format('Y-m-d');
$day5 = date_sub($today, new DateInterval('P1D'))->format('Y-m-d');
$day4 = date_sub($today, new DateInterval('P1D'))->format('Y-m-d');
$day3 = date_sub($today, new DateInterval('P1D'))->format('Y-m-d');
$day2 = date_sub($today, new DateInterval('P1D'))->format('Y-m-d');
$day1 = date_sub($today, new DateInterval('P1D'))->format('Y-m-d');
$week = [$day1, $day2, $day3, $day4, $day5, $day6, $day7];

$weekly = [0, 0, 0, 0, 0, 0, 0];
$month_income = 0.0;
$month_outcome = 0.0;

$sql_month = "SELECT * FROM histories
            WHERE user_id = ?
            AND
            date LIKE ?
            ORDER BY date DESC
            ";
$stmt = $conn->prepare($sql_month);
$thisMonthPattern = '%' . $this_month . '%';
$stmt->bind_param('is', $user_id, $thisMonthPattern);
$result = $stmt->execute();

if ($result) {
    while ($rowMonth = $stmt->get_result()->fetch_assoc()) {
        $type = $rowMonth['type'];

        if ($type == 'Pemasukan') {
            $month_income += floatval($rowMonth['total']);
        } else {
            $month_outcome += floatval($rowMonth['total']);
        }
    }

    $stmt->close();
} else {
    echo json_encode([
        "success" => false,
        'message' => 'Query result error.',
        "data" => null
    ]);
}

$sql_week = "SELECT * FROM histories
            WHERE user_id = ?
            AND
            date >= ?
            ORDER BY date DESC
            ";

$stmt = $conn->prepare($sql_week);
$stmt->bind_param('is', $user_id, $day1);
$result = $stmt->execute();

if ($result) {
    while ($rowWeek = $stmt->get_result()->fetch_assoc()) {
        $type = $rowWeek['type'];
        $date = $rowWeek['date'];

        if ($type == 'Pemasukan') {
            for ($i = 0; $i < count($week); $i++) {
                if ($date = $week[$i]) {
                    $weekly[$i] += floatval($rowWeek['total']);
                }
            }
        }
    }

    $stmt->close();
} else {
    echo json_encode([
        "success" => false,
        'message' => 'Query result error.',
        "data" => null
    ]);
}

echo json_encode([
    'success' => true,
    'message' => 'Successfully fetched income and outcome',
    'data' => [
        'today' => $weekly[6],
        'yesterday' => $weekly[5],
        'week' => $weekly,
        'month' => [
            'income' => $month_income,
            'outcome' => $month_outcome
        ]
    ]
]);
