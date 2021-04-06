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

    $std_nowName = $decoded_json['nowname'];        // 수정 될 때, 기존 이름
    $std_fixedName = $decoded_json['fixname'];      // 수정 될 때, 변경 이름
    $addStudent = $decoded_json['Sadd'];            // 추가 될 때, 이름
    $delStduent = $decoded_json['Sdelet'];          // 삭제 될 때, 이름

    if(isset($addStudent))
        $switchType = 0;
    else if(isset($delStduent))
        $switchType = 3;
    else
        $switchType = 2;



//    // <!--프론트앤드 요구 테이블
//    $dataSource = array(
//        "categories"=> array()
//    );

    switch ($switchType) {
        // Create
        case 0:
            $createStudent = mysqli_query($conn, "INSERT INTO student_inf (std_num, std_name)
                                        VALUES('$addStudent', '$addStudent')");
            break;
        // Read
        case 1:
//            $allStudentQuery = mysqli_query($conn, "SELECT * FROM student_inf");
//            while($allStudent = mysqli_fetch_assoc($allStudentQuery)) {
//                array_push($dataSource["categories"], $allStudent['std_name']);
//            }
            break;
        // Update
        case 2:
            $updateStudent = mysqli_query($conn, "UPDATE student_inf
            SET std_name = '$std_fixedName' WHERE std_name = '$std_nowName'");
            break;
         // Delete
        case 3:
            $deleteStudent = mysqli_query($conn, "DELETE FROM student_inf WHERE std_name = '$delStduent'");
            break;
    }

//    // <!-- JSON 형태로 데이터 포맷 encode
//    $json = json_encode($dataSource, JSON_PRETTY_PRINT);
//    print_r($json);
//    // -->
}
?>