<?php
header("Content-Type: text/html; charset=utf-8");
require_once 'config.php';

$url = 'https://lcwslogpy.newyx.jiulingwan.com/niuniuan/login/login?timestamps='.time();
header("Location: ".$url);
die();

//if($new_ip[0]=='17'){
//    //审核
//    $bo->V->display("h5game/jcbt/examine.html");}
//    else{
//    //正式
//    $bo->V->display("h5game/jcbt/qilingbaota.html");
//}
