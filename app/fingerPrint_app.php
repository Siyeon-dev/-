<?php
//<-- DATABASE 연결
$username = "root";
$password = "pringles";
$hostname = "localhost";
$db_name  = "finger_print";
$conn   = mysqli_connect($hostname, $username, $password, $db_name);
//-->

// 연결 오류 발생시 error문 출력
if (mysqli_connect_errno()) {
    echo "Failed to connect to MySQL" . mysqli_connect_error();
    // 연결 성공시 Database에서 일치하는 data가 있는지 확인
} else {
    $valueKey = $_POST['primaryKEY'];

    /************************************************************/
    // 교수님으로부터 특정 날짜에 대한 등,하교 값을 저장하는 TABLE을 생성하고 -> ok
    // x날에 해당 TABLE 변경된 등하교 값이 있다면, 그 시간으로 지정        -> ok
    // 없다면, 기존 상수 값으로 지정 if- else문으로 !!!!!!!!!!!!!!!    -> ok
    /************************************************************/


    // <!-- 해당 primaryKEY값을 가지고 있는 학생 검색
    $querySearchSTD = mysqli_query($conn, "SELECT * FROM student_inf
      WHERE std_num = '$valueKey'");
    $userData = mysqli_fetch_assoc($querySearchSTD);
    // -->

    $userNum  = $userData['std_num'];  // 해당 학생 학번 저장 *******************************************************
    $userName = $userData['std_name']; // 해당 학생 이름 저장
    $userInfo = array("userName"=>$userName);  // 지문인증 학생의 정보를 저장할 배열

    // <!-- 현재 날짜 값 구하기
    date_default_timezone_set('Asia/Seoul');
    $timestamp  = strtotime("+1 days");
    $dayTime    = date("y-m-d");
    $time       = date("H:i:s");
    $tomorrow   = date("y-m-d", $timestamp);
    // -->

    $week = date("N");  // 주말 데이터 확인

    // <-- 출석하는 날에 DB에는 어떤 등, 하교 값이 저장되어있는지 가져오기
    $todayTimeTableQuery = mysqli_query($conn, "SELECT * FROM calendar_lunar_solar
                           WHERE solar_date = '$dayTime'");
    $todayTimeTable = mysqli_fetch_assoc($todayTimeTableQuery);
    $CST_IN_TIME = $todayTimeTable['start_time'];  // 등교해야 하는 시간
    $CST_OUT_TIME = $todayTimeTable['end_time'];   // 하교해야 되는 시간
    // -->

    // <!-- 현재 시간과 기준 시간을 연산하기 위해 strtotime으로 변환
    $strNow       = strtotime("now");
    $strTargetIn  = strtotime($CST_IN_TIME);
    $strTargetOut = strtotime($CST_OUT_TIME);

    // <!-- 오늘 날짜에 등교 하였는지 검사
    $querySearchInTime = mysqli_query($conn, "SELECT * FROM attendance_inf
        WHERE std_num = '$userNum' AND created BETWEEN '$dayTime' AND '$tomorrow'");   //****************************************
    $resultTimeInf = mysqli_fetch_assoc($querySearchInTime);  // 연관 배열
    $resultDataExist = mysqli_num_rows($querySearchInTime);   // 데이터가 존재하는지 확인
    // -->

    // <!-- 오늘 날짜가 공휴일로 지정되어 있는지 확인
    $queryCalendar = mysqli_query($conn, "SELECT * FROM calendar_lunar_solar WHERE solar_date = '$dayTime'");
    $holidayCal = mysqli_fetch_assoc($queryCalendar);
    // 국가지정 공휴일이거나, 교수님에 의해 지정된 공휴일 확인

    // <!-- 주말이 아닌 휴일은 $holiday에 true, 아니라면 false
    $holiday = false;
    if($holidayCal['memo'] != '' || $holiday['created'] == 1) {
        $holiday = true;
    } else {
        $holiday = false;
    }
    //-->
    //********************************************************************************************************************************************
    // <!-- IN_TIME 등록
    if ($resultTimeInf['in_time'] == null){
        // 자동 결석자 생성 파일에 의해서 이미 데이터가 생긴 경우라면, 등교 데이터를 찍어내지 않는다.
        if($resultDataExist != 1) {
            // <!-- 정상 등교
            if ($strNow < $strTargetIn || $week == 6 || $week == 7 || $holiday == true) {
                echo "등교";
                mysqli_query($conn, "INSERT INTO attendance_inf (std_num, in_time, state_attend) VALUES ('$userNum', '$time', true)");
                // 지각
            } else {
                echo "지각";

                mysqli_query($conn, "INSERT INTO attendance_inf (std_num, in_time, state_late, state_attend) VALUES ('$userNum', '$time', true, true)");
            }
            // -->
        }
        // <!-- OUT_TIME 등록
    } else if ($resultTimeInf['out_time'] == '00:00:00'){   // 하교시간 재수정 방지
        // // 정상 하교
        $userInfo["data"] = "퇴근";
        if ($strNow > $strTargetOut || $week == 6 || $week == 7 || $holiday == true) {
            $userInfo["data"] = "하교";
            mysqli_query($conn, "UPDATE attendance_inf SET out_time = '$time', state_attend = true WHERE std_num = '$userNum' AND created BETWEEN '$dayTime'AND '$tomorrow'");
            // 조퇴
        } else {
            $userInfo["data"] = "조퇴";
            mysqli_query($conn, "UPDATE attendance_inf SET out_time = '$time', state_early = true, state_attend = true WHERE std_num = '$userNum' AND created BETWEEN '$dayTime' AND '$tomorrow'");
        }
    }
    // --!>

    $json = json_encode($userInfo);
    print_r($json);
}
?>
