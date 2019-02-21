<?php
//ini_set("display_errors","on");
require_once 'config.php';
COMMON('alipay/alipay_notify.class','baseCore','weixin.class');
DAO('index_dao');
$bo = new baseCore();
$bo->err_log($bo->client_ip().":".var_export($_GET,1),'guanming_notify');

if(!$_GET['username'] || !$_GET['gameapi'] ||!$_GET['sporderid']||!$_GET['sign']){
    $_GET['msg']='参数错误';
    $bo->err_log($bo->client_ip().":".var_export($_GET,1),'guanming_notify');
    die("参数错误");
}

$retcode = $_GET['retcode'];
$username = $_GET['username'];
$gameapi = $_GET['gameapi'];
$sporderid = $_GET['sporderid'];
$sign = $_GET['sign'];
$sign_str = 'retcode='.$retcode.'&username='.$username.'&gameapi='.$gameapi.'&sporderid='.$sporderid.'||'.GUANMING_KEY;
if($sign !=  strtolower(md5($sign_str))){
    $_GET['msg']='加密错误';
    $bo->err_log($bo->client_ip().":".var_export($_GET,1),'guanming_notify');
    die("加密错误");
}


$dao = new index_dao();
if ($retcode=="1"){
    $dao->sql = "update guanming set status=2, callback_time=? where order_id=? and username=? ";
}elseif($retcode=='0'){
    $dao->sql = "update guanming set status=3, callback_time=? where order_id=? and username=? ";
}else{
    $dao->sql = "update guanming set status=4, callback_time=? where order_id=? and username=? ";
}
$dao->params = array(strtotime("now"), $sporderid, $username);
$dao->doExecute();
die("充值完成");