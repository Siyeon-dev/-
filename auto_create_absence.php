<?php
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
      // 1. 모든 학생 이름을 while로써 접근
      $queryAllStd = mysqli_query($conn, "SELECT * FROM student_inf");

      // <!-- 현재 날짜와 어제 날짜
      $timeToday = date("y-m-d");
      // -->

      $week = date("N"); // 주말 여부

      // <!-- 공휴일 DB 검색 후, 휴일 유무 저장
      $queryCheckHoliday = mysqli_query($conn, "SELECT * FROM calendar_lunar_solar WHERE solar_date = '$timeToday'");
      $checkHoliday = mysqli_fetch_assoc($queryCheckHoliday);
      if ($checkHoliday['memo'] != '' || $checkHoliday['created'] == 1) {
        $existHoliday = true;    // 휴일 있음
      } else {
        $existHoliday = false;   // 휴일 없음
      }
      // -->
      


      // <!-- 모든 학생의 결석 유무를 확인하고, 결석자는 기록
      while($allStd = mysqli_fetch_assoc($queryAllStd)) {

            // 결석하였는지 검색
            $querySearchData = mysqli_query($conn, "SELECT * FROM attendance_inf
            WHERE std_num = '$allStd[std_num]' AND created = '$timeToday'");
            $attendanceData = mysqli_num_rows($querySearchData);

            $stdNum = $allStd['std_num']; // 해당 학생의 학번

            // <!-- 출석 데이터가 없으면, 새로 만들고, state_absence에 true 값 전달
            if($attendanceData == 0) {
                // 주말, 휴일은 결석 데이터를 만들지 않는다.
                if($week == 6 || $week == 7 || $existHoliday == true) {
                    mysqli_query($conn, "INSERT INTO attendance_inf (std_num)
                    VALUES ($stdNum)");
                // 평일은 결석 데이터를 만든다.
                } else {
                    mysqli_query($conn, "INSERT INTO attendance_inf (std_num, state_absence)
                    VALUES ($stdNum, true)");
                }
            }
            // -->
      }
      // -->
    }
?>
