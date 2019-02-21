<?php
//if(stripos($_SERVER['HTTP_HOST'], 'gao7gao8')){
//    header('HTTP/1.1 301 Moved Permanently');
//    header("Location:http://qjb.go.cc/pay.php");
//}
header("Content-Type: text/html; charset=utf-8");
require_once 'config.php';
COMMON('paramUtils');
BO("site_index_web");
$bo = new site_index_web();
$bo->pay_view();