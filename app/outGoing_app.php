<?php
header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, X-Requested-With");

// DATABASE 연결
$username = "root";
$password = "pringles";
$hostname = "localhost";
$db_name  = "finger_print";
$conn   = mysqli_connect($hostname, $username, $password, $db_name);

// 연결 오류 발생시 error문 출력
if (mysqli_connect_errno()) {
    echo "Failed to connect to MySQL" . $conn->connect_error;
} else {
    // Handling JSON POST request
    // Takes raw data from the request
    // Converts it into a PHP object
    header("Content-Type: application/json; charset=UTF-8");
    $json = file_get_contents('php://input'); // JSON 포맷의 Data를 넘겨받는다.
    $decoded_json = json_decode($json, true);   // JSON 포맷을 Parsing 한다.

    // decoded 된 json 데이터에서 사용자의 id(input_user_id)와 pw(input_user_pw)를 가져온다.
    $userDevice = $decoded_json['userDevice'];      // email    - 학번
    $reason = $decoded_json['reason'];

    $timestamp  = strtotime("+1 days");
    $today    = date("y-m-d");
    $tomorrow   = date("y-m-d", $timestamp);

    $userQuery = mysqli_query($conn, "SELECT * FROM attendance_inf WHERE 
    std_num IN (SELECT std_num FROM student_inf WHERE mobild_device = '$userDevice') AND
    created BETWEEN '$today' AND '$tomorrow'");

    $userQueryResult = mysqli_fetch_assoc($userQuery);

    // 출석을 안한 경우 null 반환
    if ($userQueryResult == null) {
        // 데이터 전송
        $json = json_encode(null);
        print_r($json);
        exit();
    }

    $nowTime = date("H:i:s");
    

    $outGoingQuery = mysqli_query($conn, "SELECT * FROM outgo_inf WHERE idx_attendance = $userQueryResult[idx] ORDER BY idx DESC limit 1");
    $outGoingResult = mysqli_fetch_assoc($outGoingQuery);

    // 새로운 외출 등록
    if ($outGoingResult == null || $outGoingResult['outgoing_time'] != '00:00:00') {
        $insertDataQuery = mysqli_query($conn, "INSERT INTO outgo_inf (idx_attendance, reason, in_time) VALUES ($userQueryResult[idx], '$reason', '$nowTime')");
    } else {
        $outGoingTime = gmdate("H:i:s", strtotime($nowTime) - strtotime($outGoingResult['in_time']));
        $updateDataQuery = mysqli_query($conn, "UPDATE outgo_inf SET out_time = '$nowTime', outgoing_time = '$outGoingTime' WHERE idx = $outGoingResult[idx]");
    }
    
    $outGoingDatasQuery = mysqli_query($conn, "SELECT * FROM outgo_inf WHERE idx_attendance = $userQueryResult[idx]");
    
    $outGoingDataArray = array();
    while ($data = mysqli_fetch_assoc($outGoingDatasQuery)) {
     	if ($data['out_time'] == '00:00:00')
                $data['out_time'] = null;
	 array_push($outGoingDataArray, array(
            "in_time" => $data['in_time'],
            "out_time" => $data['out_time'],
            "reason" => $data['reason']
        ));
    }

    $nameQuery = mysqli_query($conn, "SELECT * FROM student_inf WHERE mobild_device = '$userDevice'");
    $name = mysqli_fetch_assoc($nameQuery);
    
    if ($userQueryResult['out_time'] == '00:00:00')
	$userQueryResult['out_time'] = null; 
    // 데이터 전송
    $json = json_encode(["std_name"=> $name['std_name'], "in_time"=> $userQueryResult['in_time'],"out_time"=>$userQueryResult['out_time'], "out_list"=>$outGoingDataArray]);
    print_r($json);
}

