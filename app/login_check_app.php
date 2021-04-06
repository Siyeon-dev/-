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

      $user_id = $_POST['userID'];
      $user_pw = $_POST['userDevice'];

      $query_mobile_value = mysqli_query($conn, "SELECT * FROM student_inf WHERE mobild_device = '$user_pw'");
      $rows_mobile = mysqli_num_rows($query_mobile_value);

      if ($rows_mobile == 0) {
	mysqli_query($conn, "UPDATE student_inf SET mobild_device ='$user_pw' WHERE std_num='$user_id'");
      }

      //학생용 table 접근 query문 $query_std
      $query_std  = mysqli_query($conn, "SELECT * FROM student_inf
      WHERE std_num = '$user_id' AND mobild_device = '$user_pw'");

      $rows_std = mysqli_num_rows($query_std);      // 학생 데이터 존재 여부 저장 (true : 1, false : 2)
      $rows_name = mysqli_fetch_array($query_std);  // 해당 학생의 이름 데이터 저장

      $flag_std = false;
      
      if ($rows_std == 1) {
        $flag_std = true;
        $dayTime    = date("y-m-d");        
        $query_std_time = mysqli_query($conn, "SELECT * FROM attendance_inf WHERE created = '$dayTime' AND std_num = '$user_id'");
        $userData = mysqli_fetch_assoc($query_std_time);

        $in_time = $userData['in_time'];
        $out_time = $userData['out_time'];
      
        $flag_array = array("in_time"=>$in_time,"out_time"=>$out_time ,"name"=>$rows_name['std_name']);
        $encoded_json = json_encode($flag_array);
        print_r($encoded_json);

      } else {
        $flag_std = false;
	 $flag_array = array("name"=>'로그인정보가없습니다.');
        $encoded_json = json_encode($flag_array);
        print_r($encoded_json);
      }
    }
?>
