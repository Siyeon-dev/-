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

  // JSON 데이터가 들어왔을 때, 들어온 값으로 month를 셋팅하고,
  // 그렇지 않을 때, 현재 month의 값으로 데이터를 셋팅한다.
  if ($decoded_json != 0) {
    $getMonth = $decoded_json['month'];     // JSON month 데이터 받아오기 currentMonth
    $getYear  = $decoded_json['year'];      // JSON year  데이터 받아오기 currentYear
    $month = $getYear . "-" . $getMonth;    // Front로부터 받아온 데이터를  yyyy-mm의 포맷으로 저장
    $endMonthDay = date('t', strtotime($month));       // 이번 달의 마지막 날
  } else {
    $month       = date('y-m'); // 이번 달
    $endMonthDay = date('t');   // 이번 달의 마지막 날
  }

  // <!--프론트앤드 요구 테이블
  $dataSource = array(
    "categories"    => array(
      "category"      => array()
    ),
    "dataset"       => array(),
    "days"          => array(),
    "startTimes"    => array(),
    "endTimes"      => array()
  );
  // -->
  //////////////////////////////////////////////////////////////////////////
  // 1일부터 X까지의 데이터셋 example:{0, 0, 0, 0 ,0, 1, 1} -- 0은 평일, 1은 주말////
  //////////////////////////////////////////////////////////////////////////

  // <!-- 이번 달의 첫 날부터 마지막 날까지
  for ($dayCount = 1; $dayCount < $endMonthDay + 1; $dayCount++) {

    $tempMonthData = $month . "-" . $dayCount; // yy-mm-dd 의 형태로 데이터 포맷 저장

    // 달력의 n번째 날짜를 DB 검색 후, 연관 배열로 저장
    $querySeachMonthInf = mysqli_query($conn, "SELECT * FROM calendar_lunar_solar 
         WHERE solar_date = '$tempMonthData' ORDER BY solar_date ASC");

    $searchMonthInf = mysqli_fetch_assoc($querySeachMonthInf);

    // 해당 월의 저장된 등, 하교 시간을 불러온다.
    // startTimes 에는 등교 시간이,
    // endTimes 에는 하교 시간이 저장된다.
    array_push($dataSource['startTimes'], $searchMonthInf['start_time']);
    array_push($dataSource['endTimes'], $searchMonthInf['end_time']);

    // <!-- 오늘 날짜가 무슨 요일인지 확인  {0 ~ 6}  0 = 일요일, 6 = 토요일
    $dateTs = strtotime($tempMonthData);
    $weekday = date("w", $dateTs);
    // -->

    // <!--  $dataSource['days'] 배열에 국가 공휴일이거나 주말이라면 1, 평일이라면 0을 넣는다.
    if ($searchMonthInf['memo'] != NULL || $weekday == 6 || $weekday == 0) {
      if (date(d) == $dayCount && $getMonth == date(m)) {
        array_push($dataSource['days'], "2");
      } else {
        array_push($dataSource['days'], "1");
      }
    } else {
      if (date(d) == $dayCount && $getMonth == date(m)) {
        array_push($dataSource['days'], "2");
      } else {
        array_push($dataSource['days'], "0");
      }
    }
  }
  // -->

  $queryAllStd = mysqli_query($conn, "SELECT * FROM student_inf ORDER BY std_name ASC"); // 모든 학생 데이터 검색



  //////////////////////////////////////////////////////////////////////////
  //전체 학생의 이름 리스트, 해당 학생별 1일부터 x일까지 출석 유무 데이터 확인///
  //////////////////////////////////////////////////////////////////////////
  $iCount = 0;  // 배열 인덱스 접근 변수
 $endMonthDayFlag = false; 
  if ($endMonthDay != 31) {
	$endMonthDay += 1;
	$endMonthDayFlag = true;
  }


 $inMonthDay = $month . "-1" . " ";           		// 해당월의 첫번째 날
	
	if ($endMonthDay == 31 && $endMonthDayFlag == false) {
		$time = strtotime($inMonthDay); 
		$month = date("Y-m",strtotime("+1 month", $time));
		$endMonthDay = 1; 
	}

 $outMonthDay = $month . '-' . $endMonthDay;   // 해당 월의 마지막 날

  // <!-- 학생 날짜별 출지결 데이터를 JSON 데이터로 변환할 배열에 저장
  while ($rows = mysqli_fetch_assoc($queryAllStd)) {
    // <!-- 학생 이름 배열에 모든 학생의 이름 저장
    array_push(
      $dataSource['categories']['category'],
      $rows['std_name']
    );
    // -->

    // <!-- 학생별 출지결 데이터를 담을 배열 동적할당
    array_push($dataSource['dataset'], array(
      "data" => array(),
      "start_time" => array(),
      "end_time" => array()
    ));

    $tmpData = array(); // 해당 학생의 1일 ~ x일 까지의 출지결 정보를 담을 배열

    // 해당 학생의 출석 정보 검색
    $querySearchData = mysqli_query($conn, "SELECT * FROM attendance_inf WHERE std_num = $rows[std_num] AND created  BETWEEN  '$inMonthDay ' AND '$outMonthDay' ORDER BY created ASC");

    // <!--  해당 학생의 state에 맞는 데이터를 $tmpData에 저장
    while ($data = mysqli_fetch_assoc($querySearchData)) {
      
      array_push($dataSource['dataset'][$iCount]['start_time'], $data['in_time']);
      array_push($dataSource['dataset'][$iCount]['end_time'], $data['out_time']);
      

      if ($data['state_late']     == 1) {
        array_push($dataSource['dataset'][$iCount]['data'], "1");
        continue;
      }; // 지각, 출석
      if ($data['state_early']    == 1) {
        array_push($dataSource['dataset'][$iCount]['data'], "0");
        continue;
      }; // 조퇴
      if ($data['state_attend']   == 1) {
        array_push($dataSource['dataset'][$iCount]['data'], "0");
      }; // 정상등교 출석
      if ($data['state_absence']  == 1) {
        array_push($dataSource['dataset'][$iCount]['data'], "2");
      }; // 결석
      if ($data['state_late'] == NULL && $data['state_early'] == NULL && $data['state_attend'] == NULL && $data['state_absence'] == NULL) {
        array_push($dataSource['dataset'][$iCount]['data'], "NULL");
      };
    }
    // -->
    // JSON 데이터 타입으로 변환될 배열에 해당 학생의 출결 정보를 저장
    $iCount++;  // 다음 인덱스 접근
  }
  // --!>

  // <!-- JSON 형태로 데이터 포맷 encode
  $json = json_encode($dataSource, JSON_PRETTY_PRINT);
  print_r($json);
  // -->
}
