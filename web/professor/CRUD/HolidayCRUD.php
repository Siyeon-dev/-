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
    echo "Failed to connect to MySQL" . mysqli_connect_error();
// 연결 성공시 Database에서 일치하는 data가 있는지 확인
} else {
    header("Content-Type: application/json; charset=UTF-8");
    $json = file_get_contents('php://input');
    $decoded_json = json_decode($json, true);

    $getDate1 = $decoded_json[0][0];  // 시작 날짜
    $getDate2 = $decoded_json[0][1];  // 끝 날짜
    $changeTime = $decoded_json[4]['HH'] . ":" . $decoded_json[4]['mm'];
    $memo = "test holiday";
    // $decoded_json[1] == 1  이면 공휴일 생성
    // $decoded_json[2] == 1  이면 공휴일 삭제
    // $decoded_json[3] == 1  이면 등하교 시간 수정
    if($decoded_json[1] == 1)
        $type = 0;
    else if ($decoded_json[2] == 1)
        $type = 3;
    else if($decoded_json[3] == 1) {
        // $decoded_json[5] == 1  등교 시간 수정
        // $decoded_json[6] == 1  등교 시간 수정
        if($decoded_json[5] == 1) {
            $type = 1;
            $typeOfInAndOut = 'start_time';
        } else if($decoded_json[6] == 1) {
            $type = 1;
            $typeOfInAndOut = 'end_time';
        }
    }

    // 예외처리, 사용자로부터 시간 , 분 중 입력받지 못한 데이터가 있다면,
    // default 데이터로 입력한다.
    //          1) 입실 09:00:00
    //          2) 퇴실 21:00:00
    if(empty($decoded_json[4]['HH']) || empty($decoded_json[4]['mm'])) {
        if($decoded_json[5] == 1) {
            $type = 1;
            $typeOfInAndOut = 'start_time';
            $changeTime = '09:00:00';
        } else if($decoded_json[6] == 1) {
            $type = 1;
            $typeOfInAndOut = 'end_time';
            $changeTime = '21:00:00';
        }
    }


    // <!-- $getDate1에 무조건 작은 날짜가 들어오도록 자리바꾸기
    if (strtoTime($getDate1) > strtoTime($getDate2)) {
        $temp = $getDate1;
        $getDate1 = $getDate2;
        $getDate2 = $temp;

        // 만일 하루만 변경한다고 하면, BETWEEN 을 사용하기 때문에
        // getDate1 과 getDate2 의 값을 동일하게 맞춰준다.
        if(empty($getDate1))
            $getDate1 = $getDate2;
    }
    // -->


    // <!-- CRUD SWITCH !
    switch ($type) {
        // Create
        case 0:
            mysqli_query($conn, "UPDATE calendar_lunar_solar
            SET memo = '{$memo}', created = 1 WHERE solar_date BETWEEN '{$getDate1}' AND '{$getDate2}'");
            break;
        // 출석 시간 수정 쿼리
        case 1:
            echo "UPDATE calendar_lunar_solar SET $typeOfInAndOut ='{$changeTime}'
                            WHERE solar_date BETWEEN '{$getDate1}' AND '{$getDate2}'";

            mysqli_query($conn, "UPDATE calendar_lunar_solar SET $typeOfInAndOut ='{$changeTime}'
                            WHERE solar_date BETWEEN '{$getDate1}' AND '{$getDate2}'");
            break;
        // Update
        case 2:
            // 특정 날짜의 공휴일을 수정한다.
            mysqli_query($conn, "UPDATE calendar_lunar_solar
            SET memo = '{$memo}', created = 1 WHERE solar_date BETWEEN '{$getDate1}' AND '{$getDate2}'");
            break;
        // Delete
        case 3:
            // 특정 날짜의 공휴일을 삭제한다.
            mysqli_query($conn, "UPDATE calendar_lunar_solar
            SET memo = '', created = 0 WHERE solar_date BETWEEN '{$getDate1}' AND '{$getDate2}'");
            break;
    }
    // -->
}
?>