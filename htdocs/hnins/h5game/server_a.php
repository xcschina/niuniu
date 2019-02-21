<?php
header("Content-Type: text/html; charset=utf-8");
require_once 'config.php';
COMMON('paramUtils');
BO('feedback');
$bo = new feedback();
$bo->client_ip();
$new_ip = explode(".",$bo->client_ip());
if($new_ip[0]=='117' || $bo->client_ip()=='112.49.108.6'){
    //审核
//    $bo->V->display("h5game/examine_sever.html");
    $bo->V->display("h5game/apple_examine_sever.html");
}else{
    //正式
    $bo->V->display("h5game/formal_sever.html");
}