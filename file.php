<?php

include './vendor/PHPExcel/Classes/PHPExcel.php';
include './OrdersImport.php';

if( $_POST['sendMsg'] ) {
    if ( $_FILES["xlsx"]["type"] == "application/vnd.ms-excel" ){
        $inputFileType = 'Excel5';
    }elseif ( $_FILES["xlsx"]["type"] == "application/octet-stream" || $_FILES["xlsx"]["type"] == "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet" ){
        $inputFileType = 'Excel2007';
    }else {
        echo '上传文件类型有误';    
    }
    if ($_FILES["xlsx"]["error"] > 0) {
        echo 'Error:'.$_FILES["xlsx"]["error"];
    }
    if (empty($_FILES["xlsx"]["name"])) {
        echo '上传文件不存在';
    }
    if ($_FILES["xlsx"]["size"] > 3 * 1024 * 1024 || $_FILES["xlsx"]['size'] == 0) {
        echo '上传文件大小不能超过3M且不能为0M';
    }
    $filename = './upload/'.$_FILES["xlsx"]['name'];
    if( file_exists( $filename ) ){
        unlink($filename); //如果存在相同文件则删除
    }
    move_uploaded_file($_FILES["xlsx"]["tmp_name"], $filename);
    set_time_limit(0);
    $ordersImport = new OrdersImport();
    $result = $ordersImport->getImportData($inputFileType,$filename);
    if ( $result['code'] == 2 ) {
        echo $result['msg'];
    }
    exit;
}