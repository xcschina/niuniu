<?php
header("Content-Type: text/html; charset=utf-8");
require_once 'config.php';
COMMON('paramUtils');
BO('feedback');
$bo = new feedback();
$bo->client_ip();
$bo->V->display("h5game/cqll/danjilianlian.html");

//$new_ip = explode(".",$bo->client_ip());
//if($new_ip[0]=='17'){
//    //审核
//    $bo->V->display("h5game/wk2_examine_sever.html");
//}else{
//    //正式
//    $bo->V->display("h5game/wk2_examine_sever.html");
//
////    $bo->V->display("h5game/wk_formal_sever.html");
//}