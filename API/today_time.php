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
  // <!-- JSON 데이터를 받아온다면, 받아온 데이터를 바탕으로 정보를 찾는다.
  // 아니라면, 이번 달의 데이터를 바탕으로 정보를 찾는다.
  $today = $decoded_json['today'];
  $tommorow = date('Y-m-d', strtotime("+1 day", strtotime($today)));
  // 데이터를 저장할 배열 선언
  $dataSource = array(
    "timeDataSet" => array()
  );

  $studentList = array(
      '1601214', '1901321', '1901053', '1901060'
  );
  
  foreach ($studentList as $student) {
       // 특정 학생의 출석 데이터를 가져온다
    $querySearchData = mysqli_query($conn, "SELECT * FROM attendance_inf WHERE std_num = $student AND
    created BETWEEN '$today' AND '$tommorow' ORDER BY created ASC");

    $SearchTodayTime = mysqli_fetch_assoc($querySearchData);

    $todayIntime   = $SearchTodayTime['in_time'];   // 오늘의 출근시간
    $todayOuttime  = $SearchTodayTime['out_time'];  // 오늘의 퇴근시간
    //********************************************************************************************************

    // <!-- 전체 데이터를 정리해서 $dataSource 배열에 저장한다.
    array_push($dataSource["timeDataSet"], array(
    "student_num"       => $student,   // 이름
    "in_time"    =>$todayIntime,        // 오늘 등교 시간
    "out_time"   =>$todayOuttime        // 오늘 하교 시간
    ));
  }

  // $dataSource 배열을 JSON 형태로 변환하여 front-end에게 전달
  $json = json_encode($dataSource, JSON_PRETTY_PRINT);
  print_r($json);
}
