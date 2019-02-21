<?php
header("Content-Type: text/html; charset=utf-8");
require_once 'config.php';
COMMON('paramUtils');
BO('feedback');
$bo = new feedback();
$bo->client_ip();
$new_ip = explode(".",$bo->client_ip());
$bo->V->display("h5game/cqll/1316832183.html");

//if($new_ip[0]=='17'){
//    //审核
//    $bo->V->display("h5game/examine_sever.html");
//}else{
//    //正式
//    $bo->V->display("h5game/formal_sever.html");
//}