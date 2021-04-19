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
        $fingerPrints = mysqli_query($conn, "SELECT serial_num, std_num FROM student_inf");
        
	$fingerPrintArray = array();
	while($data = mysqli_fetch_assoc($fingerPrints)) {
		if ($data['serial_num'] != '') {
			array_push($fingerPrintArray, array(
				"serial_num"=> $data['serial_num'],
				"std_num"=> $data['std_num']
			));
		}
	}
         // 데이터 전송
	$json = json_encode($fingerPrintArray);        
	print_r($json);
    }
?>

