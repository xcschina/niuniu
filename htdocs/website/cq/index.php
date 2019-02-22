<?php
//$time = strtotime("now");
//$time = substr($time,-1);
//header("Content-Type: text/html; charset=utf-8");
//require_once 'config.php';
//COMMON('paramUtils');
//BO("game_index_web");
//$bo = new game_index_web();
//if($time>0 && $bo->client_ip()!='117.27.76.246'){
//    header('HTTP/1.1 301 Moved Permanently');
//    header('Location: http://mir.5solo.com');
//}else{
//    $bo->pay_view();
//}
header('HTTP/1.1 301 Moved Permanently');
header('Location: http://mir.5solo.com');