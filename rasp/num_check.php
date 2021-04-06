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
      $std_num = $_POST['std_num']; // 학번 저장

      // <!--DB에 학생이 존재하는지 검색
      $query_exist_num = mysqli_query($conn, "SELECT * FROM student_inf
      WHERE std_num = '$std_num'");
      $asocArray = mysqli_fetch_assoc($query_exist_num);
      $exist_num = mysqli_num_rows($query_exist_num);
      // --!>
      $userName = $asocArray['std_name'];
      // <!-- 학생이 존재한다면 true값 반환
      if ($exist_num == 1) {
        $resultCheckNum = 'true';
      } else {
        $resultCheckNum = 'false';
      }
      
      $userSeiral = $asocArray['serial_num'];
      
      if ($userSeiral == '') {
        $resultCheckSeiral = 'true';
      } else {
        $resultCheckSeiral = 'false';
      }
      // --!>

      // <!-- 요청한 환경으로 JSON Data 반환
      $resultArray = array("flag_exist"=>$resultCheckNum, "userName"=>$userName, 'flag_serial'=>$resultCheckSeiral);
      $encodedJson = json_encode($resultArray);
      print_r($encodedJson);
      // --!>
    }
?>
