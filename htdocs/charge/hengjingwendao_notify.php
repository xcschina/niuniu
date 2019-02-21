<?php
//ini_set("display_errors","on");
require_once 'config.php';
COMMON('alipay/alipay_notify.class','baseCore','weixin.class');
DAO('index_dao');
$bo = new baseCore();
$bo->err_log($bo->client_ip().":".var_export($_POST,1),'hengjingwendao_notify');

if(!$_POST['OrderID'] || !$_POST['MerchantOrderID'] || !$_POST['Sign'] ||!$_POST['State']){
    die("参数错误");
}


$order_id = $_POST['OrderID'];
$merchant_order_id = $_POST['MerchantOrderID'];
$state  = $_POST['State'];
$sign = $_POST['Sign'];
$state_info = $_POST['StateInfo'];

$sign_str = strtolower(md5($order_id.$merchant_order_id.$state.pHENGJINGWENDAO_KEY));
if($sign != $sign_str){
    die("加密错误");
}


$dao = new index_dao();
if ($state=="101"){
    $dao->sql = "update hengjingwendao set status=1, callback_time=?, status_info=? where order_id=? and hj_order_id=?";
}else{
    $dao->sql = "update hengjingwendao set callback_time=?, status_info=? where order_id=? and hj_order_id=?";
}
$dao->params = array(strtotime("now"),$state_info, $merchant_order_id, $order_id);
$dao->doExecute();
die("充值完成");