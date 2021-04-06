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
      $array = array("excel"=> array());

      require_once "/var/www/html/PHPExcel-d3373c97e1bd4fceb0687d2e289998bccda514f1/PHPExcel-d3373c97e1bd4fceb0687d2e289998bccda514f1/Classes/PHPExcel.php"; // PHPExcel.php을 불러와야 하며, 경로는 사용자의 설정에 맞게 수정해야 한다.

      $objPHPExcel = new PHPExcel();

      require_once "/var/www/html/PHPExcel-d3373c97e1bd4fceb0687d2e289998bccda514f1/PHPExcel-d3373c97e1bd4fceb0687d2e289998bccda514f1/Classes/PHPExcel/IOFactory.php"; // IOFactory.php을 불러와야 하며, 경로는 사용자의 설정에 맞게 수정해야 한다.

      $filename = '/var/www/html/test.xlsx'; // 읽어들일 엑셀 파일의 경로와 파일명을 지정한다.

      try {

        // 업로드 된 엑셀 형식에 맞는 Reader객체를 만든다.

          $objReader = PHPExcel_IOFactory::createReaderForFile($filename);

          // 읽기전용으로 설정

          $objReader->setReadDataOnly(true);

          // 엑셀파일을 읽는다

          $objExcel = $objReader->load($filename);

          // 첫번째 시트를 선택

          $objExcel->setActiveSheetIndex(0);

          $objWorksheet = $objExcel->getActiveSheet();

          $rowIterator = $objWorksheet->getRowIterator();

          foreach ($rowIterator as $row) { // 모든 행에 대해서

                     $cellIterator = $row->getCellIterator();

                     $cellIterator->setIterateOnlyExistingCells(false);

          }

          $maxRow = $objWorksheet->getHighestRow();

          for ($i = 1 ; $i <= $maxRow ; $i++) {

                     $name = $objWorksheet->getCell('A' . $i)->getValue(); // A열
                     $num = $objWorksheet->getCell('B' . $i)->getValue(); // B열
                     array_push($array["excel"], array($name, $num));
                     $queryInsertInf = mysqli_query($conn, "INSERT INTO student_inf (std_num, std_name, std_password)
                     VALUES ('$num', '$name', '1234')");
          }

      }
       catch (exception $e) {

          echo '엑셀파일을 읽는도중 오류가 발생하였습니다.';

        }

        $json = json_encode($array, JSON_PRETTY_PRINT);
        print_r($json);
    }
?>
