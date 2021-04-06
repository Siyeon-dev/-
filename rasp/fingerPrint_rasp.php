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
      // 지문을 찍는 특정 학생의 primaryKEY 값과
      // 출석인지 하교인지를 알 수 있는 tab 값을 받아온다.
      $valueKey = $_POST['primaryKEY'];
      $tabState = $_POST['tab'];

      /************************************************************/
      // 교수님으로부터 특정 날짜에 대한 등,하교 값을 저장하는 TABLE을 생성하고
      // x날에 해당 TABLE 변경된 등하교 값이 있다면, 그 시간으로 지정
      // 없다면, 기존 상수 값으로 지정 if- else문으로 !!!!!!!!!!!!!!!
      /************************************************************/

      // <!-- 해당 primaryKEY값을 가지고 있는 학생 검색
      if($valueKey != null) {
          $querySearchSTD = mysqli_query($conn, "SELECT * FROM student_inf
          WHERE serial_num = '$valueKey'");
          $userData = mysqli_fetch_assoc($querySearchSTD);
      }
      // -->

      $userNum  = $userData['std_num'];  // 해당 학생 학번 저장
      $userName = $userData['std_name']; // 해당 학생 이름 저장
      $userInfo = array("userName"=>$userName);  // 반환할 학생 정보를 저장할 배열

      // <!-- 현재 날짜 값 구하기
      date_default_timezone_set('Asia/Seoul');
      $timestamp  = strtotime("+1 days");
      $dayTime    = date("y-m-d");
      $time       = date("H:i:s");
      $tomorrow   = date("y-m-d", $timestamp);
      $week = date("N");  // 주말 데이터 확인
      // -->

      // <!-- 0시 ~ 6시 사이에는 출석할 수 없다.
      $strNow = strtotime($time);
      $strNotIn = strtotime('00:00:00');
      $strNotOut = strtotime('06:00:00');
      
      if ($strTime >= $strNotIn && $strTime <= $strNotOut) {
        exit;
      }
      // --!> 0시 ~ 6시 사이에는 출석할 수 없다.

      // <-- 출석하는 날에 DB에는 어떤 등, 하교 시간값이 저장되어있는지 가져오기
      $todayTimeTableQuery = mysqli_query($conn, "SELECT * FROM calendar_lunar_solar WHERE solar_date = '$dayTime'");
      $todayTimeTable = mysqli_fetch_assoc($todayTimeTableQuery);

      $CST_IN_TIME = $todayTimeTable['start_time'];
      $CST_OUT_TIME = $todayTimeTable['end_time'];
      // -->

        // <!-- 현재 시간과 기준 시간으로 연산하기 위해 strtotime으로 변환
      $strNow       = strtotime("now");
      $strTargetIn  = strtotime($CST_IN_TIME);
      $strTargetOut = strtotime($CST_OUT_TIME);

      // <!-- 오늘 날짜에 등교 하였는지 검사
      $querySearchInTime = mysqli_query($conn, "SELECT * FROM attendance_inf
        WHERE std_num = '$userNum' AND created BETWEEN '$dayTime' AND '$tomorrow'");
      $resultTimeInf = mysqli_fetch_assoc($querySearchInTime);  // 연관 배열
      $resultDataExist = mysqli_num_rows($querySearchInTime);   // 데이터가 존재하는지 확인
      // -->

      // <!-- 오늘 날짜가 공휴일로 지정되어 있는지 확인
      // 국가지정 공휴일이거나, 교수님에 의해 지정된 공휴일 확인
      $queryCalendar = mysqli_query($conn, "SELECT * FROM calendar_lunar_solar WHERE solar_date = '$dayTime'");
      $holidayCal = mysqli_fetch_assoc($queryCalendar);

      // <!-- 주말이 아닌 휴일은 $holiday에 true, 아니라면 false
        $holiday = false;
        if($holidayCal['memo'] != '' || $holiday['created'] == 1) {
        $holiday = true;
      } else {
        $holiday = false;
      }
      //-->

    // <!-- IN_TIME 등록
    //     tabState = true -> 입실 탭 (rasp)
    //                false-> 퇴실 탭 (rasp)
    if ($resultTimeInf['in_time'] == null && $valueKey != null && $tabState == "true"){
        // 자동 결석자 생성 파일에 의해서 이미 데이터가 생긴 경우라면, 등교 데이터를 찍어내지 않는다.
        if($resultDataExist != 1) {
            // <!-- 정상 등교
                $userInfo["data"] = true;
                $userInfo["check"] = false; // 입실 내역이 존재하지 않을 때 false; (지문인식기에서 출석 여부 판단용)
            if ($strNow < $strTargetIn || $week == 6 || $week == 7 || $holiday == true) {
                $userInfo['status'] = "정상 등교";
                mysqli_query($conn, "INSERT INTO attendance_inf (std_num, in_time, state_attend) 
                        VALUES ('$userNum', '$time', true)");
            // 지각
            } else {
                $userInfo['status'] = "지각";
                mysqli_query($conn, "INSERT INTO attendance_inf (std_num, in_time, state_late, state_attend) 
                        VALUES ('$userNum', '$time', true, true)");
            }
        }
    // <!-- OUT_TIME 등록
    } else if ($resultTimeInf['out_time'] == '00:00:00' && $tabState == "false"){   // 하교시간 재수정 방지
          // <!-- 정상 하교
              $userInfo["data"] = true;
              $userInfo["check"] = true;    // 입실 내역이 존재할 때 true; (지문인식기에서 출석 여부 판단용)
          if ($strNow > $strTargetOut || $week == 6 || $week == 7 || $holiday == true) {
              $userInfo['status'] = "정상 하교";
              mysqli_query($conn, "UPDATE attendance_inf SET out_time = '$time', state_attend = true 
                        WHERE std_num = '$userNum' AND created BETWEEN '$dayTime'AND '$tomorrow'");
          // 조퇴
          } else {
              $userInfo['status'] = "조퇴";
              mysqli_query($conn, "UPDATE attendance_inf SET out_time = '$time', state_early = true, state_attend = true 
                        WHERE std_num = '$userNum' AND created BETWEEN '$dayTime' AND '$tomorrow'");
          }
    } else {
      // 출석, 하교 등록 실패 경우 false 반환
      $userInfo["data"] = false;
    }
      // --!>

      // 데이터 전송
      $json = json_encode($userInfo);
      print_r($json);
    }
?>
