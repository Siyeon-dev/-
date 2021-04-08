<?php
//////////////////////////////////////////////////////
//          **** createData.php ****
// 교수용 페이지의 테이블 구조의 한계를 보완하기 위한 php 입니다.
// 1. 교수용 페이지의 테이블은 key != value인 상황입니다.
// 2. 출석 일자(key) != 출석 데이터(value)
// 3. 따라서 출석 일자 수에 맞는 출석 데이터가 필요합니다.
// 4. 따라서, 데이터가 들어가 있지 않을 경우, 일자 수에 맞추기 위한
//    데이터를 생성해야 합니다.
// 5. 아래 쿼리문을 조절하여 dumy 데이터를 생성하여
// 6. 테이블 구조의 한계를 보완합니다.
//////////////////////////////////////////////////////

$username = "root";
$password = "pringles";
$hostname = "localhost";
$db_name  = "finger_print";
$conn   = mysqli_connect($hostname, $username, $password, $db_name);


// 1. order by disc  으로 정렬 후 가장 최근의 날짜 데이터를 가져온다
// 2. 최근의 날짜부터 어제까지의 날짜까지 더미 데이터를 생성한다. (모든 학생에게)

// 연결 오류 발생시 error문 출력
if (mysqli_connect_errno()) {
    echo "Failed to connect to MySQL" . mysqli_connect_error();
    //DB 연결 성공
} else {
    // 전체 학생에 대한
    for ($iCount = 1; $iCount <= 31; $iCount++) {
        $query = mysqli_query($conn, "SELECT * FROM student_inf WHERE std_num ='$iCount'");
        $std_num = mysqli_fetch_array($query);
        echo $std_num['std_num'];
        for ($day = 1; $day < 8; $day++) {

            mysqli_query($conn, "INSERT INTO attendance_inf (std_num) VALUES ('$std_num[std_num]')");
            mysqli_query($conn, "UPDATE attendance_inf SET created ='2021-04-{$day} 00:00:00' WHERE created
                                                BETWEEN '2021-04-08' AND '2021-04-09' AND std_num = '{$iCount}'");
        }
    }
}
