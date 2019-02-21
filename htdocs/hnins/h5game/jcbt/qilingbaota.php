<?php
header("Content-Type: text/html; charset=utf-8");
require_once 'config.php';
COMMON('paramUtils');
BO('feedback');
$bo = new feedback();
$bo->client_ip();
if($new_ip[0]=='17'){
    //审核
    $bo->V->display("h5game/jcbt/examine.html");}
    else{
    //正式
    $bo->V->display("h5game/jcbt/qilingbaota.html");
}
