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
      // 지문인식기로부터 특정 학생의 primaryKEY 값을 받아온다.
      $userData = $_POST['primaryKEY'];

      // 해당 유저의 시리얼 넘버의 값을 공백으로 변경하겠다.
      $queryDeleteData  = mysqli_query($conn, "UPDATE student_inf SET serial_num = '' WHERE serial_num = '$userData'");
      // 해당 유저의 지문 값으로 찍혀있던 출석 정보를 삭제한다.
      $queryStudentInfo = mysqli_fetch_assoc($conn, "SELECT * FROM student_inf WHERE serial_num = '$userData'");
      mysqli_query($conn, "DELETE FROM attendance_inf WHERE std_num = '{$queryStudentInfo['std_num']}'");

      // <!-- 성공했다면, true값 반환
      if (!isset($queryDeleteData)) {
        $ResultDelete = 'false';
      } else {
        $ResultDelete = 'true';
      }
      // -->

      // 결과값 반환
      $encoded_json = json_encode($ResultDelete);
      print_r($encoded_json);
    }
?>
