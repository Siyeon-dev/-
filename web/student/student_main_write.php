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

      $value_id      = $decoded_json['id'];           // 작성자
      $value_pw      = $decoded_json['title'];        // 바뀐 패스워드
      $value_name    = $decoded_json['description'];  // 바뀐 이름
      $value_id_past = $decoded_json['change_time'];  // 바꾸기 전 아이디


      // 데이터 JSON 형식으로 인코딩
      $encoded_json = json_encode();
      print_r($encoded_json);
    }
?>
