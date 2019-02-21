<?php
//ini_set("display_errors","on");
require_once 'config.php';
COMMON('alipay/alipay_notify.class','baseCore','weixin.class');
DAO('index_dao');
$bo = new baseCore();
$bo->err_log($bo->client_ip().":".var_export($_POST,1),'shushan_notify');

if($_POST['State'] == 101){
    $dao = new index_dao();
    $dao->sql = "update shushan set status=1, shushan_order_id=?, callback_time=? where order_id=?";
    $dao->params = array($_POST['OrderID'], time(), $_POST['MerchantOrderID']);
    $dao->doExecute();
    die('True');
}else{
    if(!$_POST['State']){
        die('Error');
    }
    if(102 == $_POST['State']){
        $status = 0;
    }elseif(103 == $_POST['State']){
        $status = 2;
    }else{
        $status = 4;
    }
    $dao = new index_dao();
    $dao->sql = "update shushan set status=?, shushan_order_id=?, callback_time=? where order_id=?";
    $dao->params = array($status,$_POST['OrderID'], time(), $_POST['MerchantOrderID']);
    $dao->doExecute();
    die('True');
}