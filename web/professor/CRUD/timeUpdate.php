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


    // 하루만 지정이 된다면, getDate2 도 시작 날짜와 같게 맞춰준다. (쿼리문에서 BETWEEN 이 사용되기 때문에)
    if(empty($getDate2))
        $getDate2 = $getDate1;

    // <!-- $getDate1에 무조건 작은 날짜가 들어오도록 자리바꾸기
    if (strtoTime($getDate1) > strtoTime($getDate2)) {
        $temp = $getDate1;
        $getDate1 = $getDate2;
        $getDate2 = $temp;
    }
    // -->
    $typeOfInAndOut = undefined; // 입실인지, 아닌지를 저장한다.
    $changeTime = "" . ":" . "";     // 변경할 시간을 저장한다.

    mysqli_query($conn, "UPDATE calendar_lunar_solar SET '{$typeOfInAndOut}'='{$changeTime}'
                            WHERE solar_date BETWEEN '{$getDate1}' AND '{$getDate2}'");
}
?>