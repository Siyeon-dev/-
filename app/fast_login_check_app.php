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
  $mobileData = $decoded_json['userDevice'];      // email    - 학번
  // 교수용 table 접근 query문 $query_pfr
  $query_mobile  = mysqli_query($conn, "SELECT * FROM student_inf WHERE mobild_device = '$mobileData'");
  $rows_mobile = mysqli_num_rows($query_mobile);

  if ($rows_mobile == 1) {
    $flag_mobile = true;

$today = date('Y-m-d');
$tommorow = date('Y-m-d', strtotime("+1 day", strtotime($today)));
$row = mysqli_fetch_assoc($query_mobile);

$query_time = mysqli_query($conn, "SELECT * FROM attendance_inf WHERE std_num = '$row[std_num]' AND created BETWEEN '$today' AND '$tommorow'");
$data = mysqli_fetch_assoc($query_time);
 // 프론트에게 JSON 형식으로 로그인 시도에 대한 결과값 배열 전달.
 
   if ($data['out_time'] == '00:00:00') {
     $data['out_time'] = null;
   }

   $flag_array = array("flag_mobile"=>$flag_mobile, "std_name"=>$row['std_name'], "in_time"=>$data['in_time'], "out_time"=>$data['out_time']);
    $encoded_json = json_encode($flag_array);
    print_r($encoded_json);

  } else {
    $flag_mobile = false;
    $flag_array = array("flag_mobile"=>$flag_mobile);
    $encoded_json = json_encode($flag_array);
    print_r($encoded_json);
  }

}

