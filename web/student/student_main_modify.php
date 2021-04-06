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

      $value_pw      = $decoded_json['password']; // 수정할 패스워드
      $value_name    = $decoded_json['name'];     // 수정할 이름
      $value_id      = $decoded_json['id'];       // 현재 학번

      // student_inf table에서 바꾸고자 하는 아이디가 이미 존재하는지 탐색
      $query_check = mysqli_query($conn, "SELECT * FROM student_inf
      WHERE std_num = '$value_id'");
      $result_check = mysqli_num_rows($query_check);

      $result_check = true;

      // 동일한 아이디 값이 있다면, 데이터 수정 x
      if($result_check == 0) {
        $result_check = false;
      } else {
        // 비밀번호 수정
        mysqli_query($conn, "UPDATE student_inf
        SET std_password = '$value_pw' WHERE std_num ='$value_id'");
        // 이름 수정
        mysqli_query($conn, "UPDATE student_inf
        SET std_name = '$value_name' WHERE std_num ='$value_id'");
      }

      $valueArray = array($result_check);
      // 데이터 JSON 형식으로 인코딩
      $encoded_json = json_encode($valueArray);
      print_r($encoded_json);
    }
?>
