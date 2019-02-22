<?php
header("Content-Type: text/html; charset=utf-8");
require_once 'config.php';
COMMON('paramUtils');
BO("game_index_web");

$pagehash = paramUtils::strByREQUEST("pagehash", false);
$money_id = paramUtils::strByREQUEST("money_id", false);
if(empty($money_id)){
    die('充值金额有误,请点击左上角按钮退出后,重新操作。');
}
if($pagehash!=$_SESSION['page-hash']){
    die('页面信息异常,请点击左上角按钮退出后,重新操作。');
}
$bo = new game_index_web();
$bo->to_pay($money_id);