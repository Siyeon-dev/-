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
  $user_id = $decoded_json['email'];      // email    - 학번
  $user_pw = $decoded_json['password'];   // password - 비밀번호

  // 교수용 table 접근 query문 $query_pfr

$query_std  = $conn->query("SELECT * FROM student_inf JOIN professor_inf
                                    WHERE std_num = '$user_id' AND std_password = '$user_pw'");
  $query_pfr  = $conn->query("SELECT * FROM professor_inf
                                    WHERE pfr_num = '$user_id' AND pfr_password = '$user_pw'");
  
$rows_std = $query_std->fetch_row($query_std);
  $rows_pfr = $query_pfr->fetch_row();
  
  $flag_std = false; // 유저가 학생일 때 true
  $flag_pfr = false; // 유저가 교수일 때 true
  $flag_err = false; // 입력받은 정보가 존재하지 않을 때, true


  // $rows_pfr == 1 일 때, 유저는 교수
  if ($rows_std[0] == 1) {
    $flag_std = true;
  } else  if ($rows_pfr[0] == 1) {
    $flag_pfr = true;
  } else {
    $flag_err = true;
  }

  // 프론트에게 JSON 형식으로 로그인 시도에 대한 결과값 배열 전달.
  $flag_array = array("std" => $flag_std, "pfr" => $flag_pfr, "err" => $flag_err, "id" => $user_id);
  $encoded_json = json_encode($flag_array);
  print_r($encoded_json);
}
