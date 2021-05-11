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
  $getData1 = null;
  $getDate2 = null;

  if (isset($decoded_json)) {
    $getDate1 = $decoded_json[0];
    $getDate2 = $decoded_json[1];
    $getDate2 = strtotime($getDate2 . '+1 days');
    $getDate2 = date('y-m-d', $getDate2);
    
    // <!-- $getDate1에 무조건 작은 날짜가 들어오도록 자리바꾸기
    if (strtoTime($getDate1) > strtoTime($getDate2)) {
      $temp = $getDate1;
      $getDate1 = $getDate2;
      $getDate2 = $temp;
    }
    // -->
  } else {
    $month    = date('y-m');           // 이번 달 시작 (y-m)
    //$getDate2 = date('t');             // 이번 달 끝   (d)                                                                                      
    $getData1 = $month . "-01";          // 이번 달 시작 (y-m-d) 포맷으로 가공
    //$getDate2 = $month."-".$getDate2;  // 이번 달 끝   (y-m-d) 포맷으로 가공
    $getDate2 = date("Y-m-d");
    $getDate2 = strtotime($getDate2 . '+1 days');
    $getDate2 = date('y-m-d', $getDate2);
  }
  // -->

  // 데이터를 저장할 배열 선언
  $dataSource = array(
    "desserts" => array()
  );

  //$iCount = 0;  // 각각의 desserts에 접근하기 위한 카운트
  $queryAllStd = mysqli_query($conn, "SELECT * FROM student_inf ORDER BY std_name ASC");  // 전체 학생 검색 쿼리문

  //********************************************************************************************************
  $s = new DateTime($getData1); // 시작일
  $e = new DateTime($getData2); // 종료일
  $d    = date_diff($s, $e);    // 시작일 - 종료일 : 일수
  $days = $d->days;             // 일수 값 저장

  $diffDays = $days + 1;  // 쉬는 날을 제외 한 순수 날짜 수

  // // //<!-- 이번 달의 첫 날부터 마지막 날까지
  for ($i = 0; $i <= $days; $i++) {

    // 달력의 n번째 날짜를 DB 검색 후, 연관 배열로 저장
    $querySeachMonthInf = mysqli_query($conn, "SELECT * FROM calendar_lunar_solar 
        WHERE solar_date BETWEEN '$getData1' AND '$getData2'");

    $searchMonthInf = mysqli_fetch_assoc($querySeachMonthInf);


    //<!-- 오늘 날짜가 무슨 요일인지 확인  {0 ~ 6}  0 = 일요일, 6 = 토요일
    $tempData1 = strtotime($getData1 . "+{$i} days");
    $tempData1 = date('Y-m-d', $tempData1);
    $dateTs = strtotime($tempData1);
    $weekday = date("w", $dateTs);
    //-->

    // <!--  $dataSource['days'] 배열에 국가 공휴일이거나 주말이라면 1, 평일이라면 0을 넣는다.
    if ($searchMonthInf['memo'] != NULL || $weekday == 6 || $weekday == 0) {
      $diffDays--;
    }
  }
  // //-->

  //********************************************************************************************************
  // <!-- 각각의 학생에 접근하겠다.
  while ($rows = mysqli_fetch_assoc($queryAllStd)) {

  
    // 특정 학생의 출석 데이터를 가져온다
    $querySearchData = mysqli_query($conn, "SELECT * FROM attendance_inf WHERE std_num = $rows[std_num] AND
          created BETWEEN '$getDate1' AND '$getDate2' ORDER BY created ASC");



    // -->
    // 학생의 오늘 출석 정보
    //*******************************************************************************************************
    $timestamp = strtotime("+1 days");
    $today = date("y-m-d");  // 오늘 날짜 확인
    $yesterday = date("y-m-d", $timestamp);

    // 특정 학생의 오늘 출퇴근 시간을 검색한다
    $querySearchTodayTime = mysqli_query($conn, "SELECT * FROM attendance_inf WHERE std_num = '$rows[std_num]'
      AND created BETWEEN '$today' AND '$yesterday'");

    $SearchTodayTime = mysqli_fetch_assoc($querySearchTodayTime);

    $todayIntime   = $SearchTodayTime['in_time'];   // 오늘의 출근시간
    $todayOuttime  = $SearchTodayTime['out_time'];  // 오늘의 퇴근시간
    //********************************************************************************************************
	if($todayIntime == '00:00:00')
		$todayIntime = null;

	if($todayOuttime == '00:00:00')
                $todayOuttime = null;


    // <!-- 전체 데이터를 정리해서 $dataSource 배열에 저장한다.
    array_push($dataSource["desserts"], array(
      "name"       => $rows['std_name'],   // 이름
      "in_time"    =>$todayIntime,        // 오늘 등교 시간
      "out_time"   =>$todayOuttime        // 오늘 하교 시간
    ));
  }

  // $dataSource 배열을 JSON 형태로 변환하여 front-end에게 전달
  $json = json_encode($dataSource, JSON_PRETTY_PRINT);
  print_r($json);
}
