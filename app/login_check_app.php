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
      header("Content-Type: application/json; charset=UTF-8");
      $json = file_get_contents('php://input');
      $decoded_json = json_decode($json, true);

      $user_id = $decoded_json['userID'];
      $user_pw = $decoded_json['userDevice'];
      
      $query_mobile_value = mysqli_query($conn, "SELECT * FROM student_inf WHERE mobild_device ='$user_pw'");
      $rows_mobile = mysqli_num_rows($query_mobile_value);
      
      $query_std  = mysqli_query($conn, "SELECT * FROM student_inf
      WHERE std_num = '$user_id'");
      $rows_std = mysqli_fetch_assoc($query_std);

      if ($rows_mobile == 0) {

        if ($rows_std['mobild_device'] == '') { 
	    mysqli_query($conn, "UPDATE student_inf SET mobild_device ='$user_pw' WHERE std_num='$user_id'");
        } else {
          $flag_std = false;
          $flag_array = array("std_name"=>false);
          $encoded_json = json_encode($flag_array);
          print_r($encoded_json);
	  exit();
        }           
      }

      //학생용 table 접근 query문 $query_std
      $query_std  = mysqli_query($conn, "SELECT * FROM student_inf
      WHERE std_num = '$user_id' AND mobild_device = '$user_pw'");

      $rows_std = mysqli_num_rows($query_std);      // 학생 데이터 존재 여부 저장 (true : 1, false : 2)
      $rows_name = mysqli_fetch_array($query_std);  // 해당 학생의 이름 데이터 저장

      $flag_std = false;
      
      if ($rows_std == 1) {
        $flag_std = true;
	
	$timestamp  = strtotime("+1 days");
    	$today    = date("y-m-d");
    	$tomorrow   = date("y-m-d", $timestamp);

        $query_std_time = mysqli_query($conn, "SELECT * FROM attendance_inf WHERE std_num = '$user_id' AND created BETWEEN '$today' AND '$tomorrow'");
        $userData = mysqli_fetch_assoc($query_std_time);

        $in_time = $userData['in_time'];
        $out_time = $userData['out_time'];

	if ($out_time == '00:00:00')
		$out_time = null;

    if ($in_time == '00:00:00')
                $in_time = null;

	$outGoingDatasQuery = mysqli_query($conn, "SELECT * FROM outgo_inf WHERE idx_attendance = $userData[idx]");
    
    	$outGoingDataArray = array();
   	 while ($data = mysqli_fetch_assoc($outGoingDatasQuery)) {
        	if($data['out_time'] == '00:00:00')
			$data['out_time'] = null;

		array_push($outGoingDataArray, array(
        	    	"in_time" => $data['in_time'],
            		"out_time" => $data['out_time'],
            		"reason" => $data['reason']
       	 	));
    	}

      
        $flag_array = array("in_time"=>$in_time,"out_time"=>$out_time ,"std_name"=>$rows_name['std_name'], "out_list"=>$outGoingDataArray);
        $encoded_json = json_encode($flag_array);
        print_r($encoded_json);

      } else {
        $flag_std = false;
	 $flag_array = array("std_name"=>false);
        $encoded_json = json_encode($flag_array);
        print_r($encoded_json);
      }
    }
?>
