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
      
      
      if (isset($decoded_json)) {
        $getDate1 = $decoded_json[0];
        $getDate2 = $decoded_json[1];

        // <!-- $getDate1에 무조건 작은 날짜가 들어오도록 자리바꾸기
        if(strtoTime($getDate1) > strtoTime($getDate2)) {
          $temp = $getDate1;
          $getDate1 = $getDate2;
          $getDate2 = $temp;
        }
        // -->

      } else {
        $month       = date('y-m');           // 이번 달 시작 (y-m)
        $endMonthDay = date('t');             // 이번 달 끝   (d)

        $getDate1    = $month."-01";          // 이번 달 시작 (y-m-d) 포맷으로 가공
        $getDate2 = $month."-".$endMonthDay;  // 이번 달 끝   (y-m-d) 포맷으로 가공
      }
      // -->
      
      // 데이터를 저장할 배열 선언
      $dataSource = array(
        "desserts1"=>array(),
        "desserts2"=>array(),
        "desserts3"=>array(),
      );


      $iCount = 0;  // 각각의 desserts에 접근하기 위한 카운트

      $queryAllStd = mysqli_query($conn, "SELECT * FROM student_inf ORDER BY std_name ASC");  // 전체 학생 검색 쿼리문


      // <!-- 각각의 학생에 접근하겠다.
      while($rows = mysqli_fetch_assoc($queryAllStd)) {

        $allAttend  = 0;  // 전체 출석
        $pureAttend = 0;  // 지각, 조퇴를 제외한 순수한 출석
        $late       = 0;  // 지각
        $absence    = 0;  // 결석
        //$early      = 0;  // 조퇴


        // 특정 학생의 출석 데이터를 가져온다
        $querySearchData = mysqli_query($conn, "SELECT * FROM attendance_inf WHERE std_num = $rows[std_num] AND
          created BETWEEN '$getDate1' AND '$getDate2' ORDER BY created ASC");


        // <!-- 특정 학생의 출석 정보를 저장한다.
        while($data = mysqli_fetch_assoc($querySearchData)) {
          if ($data['state_late']     == 1) {$late++; $pureAttend--;};
          if ($data['state_attend']   == 1) {$pureAttend++; $allAttend++;};
          if ($data['state_absence']  == 1) $absence++;
          //if ($data['state_early']    == 1) {$early++; $pureAttend--;};
        }
        // -->

        // 학생의 오늘 출석 정보
        //*******************************************************************************************************
        $tempAttendInf = $pureAttend."/".$allAttend; // front-end 요구사항

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

        // <!-- 전체 데이터를 정리해서 $dataSource 배열에 저장한다.
        if($iCount < 15) {          // 첫 15명까지는 desserts1 배열에 저장
            array_push($dataSource["desserts1"], array(
              "name"       =>$rows['std_name'],   // 이름
              "attendance" =>$tempAttendInf,      // 출석
              "late"       =>$late,               // 지각
              "absence"    =>$absence,            // 결석
              "in_time"    =>$todayIntime,        // 오늘 등교 시간
              "out_time"   =>$todayOuttime        // 오늘 하교 시간
              //"early"      =>$early,              // 조퇴
            ));
        } else if ($iCount < 32) {    // 다음 16명은 desserts2 배열에 저장
            array_push($dataSource["desserts2"], array(
              "name"       =>$rows['std_name'],   // 이름
              "attendance" =>$tempAttendInf,      // 출석
              "late"       =>$late,               // 지각
              "absence"    =>$absence,            // 결석
              "in_time"    =>$todayIntime,        // 오늘 등교 시간
              "out_time"   =>$todayOuttime        // 오늘 하교 시간
              //"early"      =>$early,              // 조퇴
            ));
        } else {              // 나머지 인원은 desserts3에 배열에 저장
            array_push($dataSource["desserts3"], array(
              "name"       =>$rows['std_name'],   // 이름
              "attendance" =>$tempAttendInf,      // 출석
              "late"       =>$late,               // 지각
              "absence"    =>$absence,            // 결석
              "in_time"    =>$todayIntime,        // 오늘 등교 시간
              "out_time"   =>$todayOuttime        // 오늘 하교 시간
              //"early"      =>$early,              // 조퇴
            ));
        }
        // -->>
        $iCount++;
      }
      // -->

      // $dataSource 배열을 JSON 형태로 변환하여 front-end에게 전달
      $json = json_encode($dataSource, JSON_PRETTY_PRINT);
      print_r($json);
    }
?>
