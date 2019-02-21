<?php
//ini_set("display_errors","on");
require_once 'config.php';
COMMON('alipay/alipay_notify.class','baseCore','weixin.class');
DAO('index_dao');
$bo = new baseCore();
$bo->err_log($bo->client_ip().":".var_export($_POST,1),'luoke_notify');

if($_POST['order_status'] == 3){
    $dao = new index_dao();
    $dao->sql = "update luoke set status=1, luoke_order_id=?, callback_time=? where order_id=?";
    $dao->params = array($_POST['order_id'], time(), $_POST['merchant_order_no']);
    $dao->doExecute();
    die('True');
}else{
    if(!$_POST['order_status']){
        die('Error');
    }
    $dao = new index_dao();
    $dao->sql = "update luoke set status=?, luoke_order_id=?, callback_time=? where order_id=?";
    $dao->params = array($_POST['order_status'],$_POST['order_id'], time(), $_POST['merchant_order_no']);
    $dao->doExecute();
    die('True');
}