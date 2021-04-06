<?php
  // CORS. 크로스 호스팅 제한 해제
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
      // Handling JSON POST request //
      // Takes raw data from the request
      // Converts it into a PHP Array
      header("Content-Type: application/json; charset=UTF-8");
      $json = file_get_contents('php://input');
      $decoded_json = json_decode($json, true);

      $value_id = $decoded_json['id'];  // 로그인 한 학생의 학번 값
      //$value_id = '1601111';          // test용

      if ($decoded_json == 0) { // 언제부터' 데이터가 들어왔을 때 실행
        //$getDate1 = $decoded_json[''];
        //$getDate2 = $decoded_json[''];

      } else {
        $month       = date('y-m');           // 이번 달 시작 (y-m)
        $endMonthDay = date('t');             // 이번 달 끝   (d)

        $getDate1    = $month."-1";          // 이번 달 시작 (y-m-d) 포맷으로 가공
        $getDate2 = $month."-".$endMonthDay;  // 이번 달 끝   (y-m-d) 포맷으로 가공
      }



      // student_main에서 필요로하는 데이터 목록 배열화
      $result_array = array(
        "basic_user_inf"  => array(),
        "today_in_out"    => array(),
        "statistic_right" => array(),
        "statistic_left"  => array(),
        "notice_report"   => array()
      );


      // basic_user_inf / 학생의 기본 정보
      //std_name -> 학생 이름     std_num -> 학번
      $query_basic_user_inf = mysqli_query($conn, "SELECT * FROM student_inf
      WHERE std_num = '$value_id'");
      while($rows = mysqli_fetch_assoc($query_basic_user_inf)) {
          $row_array['std_name'] = $rows['std_name'];
          $row_array['std_num'] = $rows['std_num'];
          array_push($result_array['basic_user_inf'], $row_array);
      }

      // 현재 시간 구하기
      $time = time();
      $time_now1 = date("Y-m-d",strtotime("now", $time));
      $time_now2 = date("Y-m-d",strtotime("+1 days", $time));

      // today_in_out / 오늘의 등교, 하교 시간
      //in_time -> 등교 시간     out_time -> 하교 시간
      $query_today_in_out = mysqli_query($conn, "SELECT * FROM attendance_inf
      WHERE (std_num = '$value_id') AND (created BETWEEN '$time_now1' AND '$time_now2')");
      while($rows = mysqli_fetch_assoc($query_today_in_out)) {
          $row_array1['in_time'] = $rows['in_time'];
          $row_array1['out_time'] = $rows['out_time'];
          array_push($result_array['today_in_out'], $row_array1);
      }


      // statistic_right / 검색하는 기간 사이의 출결 시간
      // in_time -> 등교 시간      out_time -> 하교 시간      created -> 해당 날짜
      $time_from = date("Y-m-d",strtotime("-30 days", $time));
      $query_statistic_right = mysqli_query($conn, "SELECT * FROM attendance_inf
      WHERE (std_num = '$value_id') AND (created BETWEEN '$time_from' AND '$time_now2')");
        while($rows = mysqli_fetch_assoc($query_statistic_right)) {
          $row_array2['in_time'] = $rows['in_time'];
          $row_array2['out_time'] = $rows['out_time'];
          $row_array2['created'] = $rows['created'];
          array_push($result_array['statistic_right'], $row_array2);
      }


      $value_attend   = 0;  // 출석
      $value_absence  = 0;  // 결석
      $value_early    = 0;  // 조퇴
      $value_late     = 0;  // 지각


      // statistic_left / 출석, 지각, 결석 횟수
      //  attend -> 출석      absence -> 결석      late -> 지각      early_leave -> 조퇴
      $query_statistic_left = mysqli_query($conn, "SELECT * FROM attendance_inf
      WHERE std_num = '$value_id' AND created BETWEEN '$getDate1' AND '$getDate2'");
        while($rows = mysqli_fetch_assoc($query_statistic_left)) {

        if($rows['state_late'] == 1)     $value_late++;
        if($rows['state_attend'] == 1)   $value_attend++;
        if($rows['state_early'] == 1)    $value_early++;
        if($rows['state_absence'] == 1)  $value_absence++;
      }

      $row_array3['attend']       = $value_attend;
      $row_array3['absence']      = $value_absence;
      $row_array3['late']         = $value_late;
      $row_array3['early_leave']  = $value_early;
      array_push($result_array['statistic_left'], $row_array3);

      // notice_report / 보고 알림
      // user_id -> 보고 작성자   title -> 보고 제목   description -> 보고 내용
      // change_date -> 변경일    change_in_time -> 변경 등교 시간   change_out_time -> 변경 하교 시간
      // created -> 작성일        flag -> 교수님 승인 여부
      $query = mysqli_query($conn, "SELECT * FROM report
      WHERE user_id = '$value_id'");
        while($rows = mysqli_fetch_assoc($query)) {
          $row_array4['user_id'] = $rows['user_id'];
          $row_array4['title'] = $rows['title'];
          $row_array4['description'] = $rows['description'];
          $row_array4['change_date'] = $rows['change_date'];
          $row_array4['change_in_time'] = $rows['change_in_time'];
          $row_array4['change_out_time'] = $rows['change_out_time'];
          $row_array4['created'] = $rows['created'];
          $row_array4['flag'] = $rows['flag'];
          array_push($result_array['notice_report'], $row_array4);
      }

      $encoded_json = json_encode($result_array);
      print_r($encoded_json);
    }
?>
