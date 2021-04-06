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

      $user_id  = $_POST['userID'];        // 학번
      $user_num = $_POST['primaryKEY'];   // KEY 값

      //<!-- 테스트용 데이터
      // $user_id = '2';
      // $user_num = 'a';
      //--!>

      // <!--해당 학생의 row에 primaryKEY 값 있는지 확인하는 query
      $querySearchKEY  = mysqli_query($conn, "SELECT * FROM student_inf
      WHERE std_num = '{$user_id}' AND serial_num ='null'");
      $flag_query = mysqli_num_rows($querySearchKEY);  // primaryKEY 값이 존재하지 않는다면, 1이 저장된다. (등록 가능한 상황)
      // --!>

      // <!-- primaryKEY 이미 등록되어있는지?
      // 등록되어 있다면, successRegist는 false
      if ($flag_query == 1) {
        $successRegist = true;
        // <!-- 해당 학생에 primaryKEY 값 등록
        $queryRegistKEY = mysqli_query($conn, "UPDATE student_inf SET serial_num = '$user_num' WHERE std_num = '$user_id'");
        $queryStdName = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM student_inf
                                                        WHERE std_num = '{$user_id}'"));
        $stdName = $queryStdName['std_name'];
        // --!>
      } else {
          $successRegist = false;
      }
      // --!>


      // JSON 형식으로 rasp에 data 전송
      $flag_array = array("flag_success"=>$successRegist, "userName"=>$stdName, "a"=>$user_id, "b"=>$user_num);
      $encoded_json = json_encode($flag_array);
      print_r($encoded_json);
    }
?>
