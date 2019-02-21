<?php
//ini_set("display_errors","on");
require_once 'config.php';
COMMON('baseCore');
DAO('index_dao');
$bo = new baseCore();
$bo->err_log($bo->client_ip().":".var_export($_POST,1),'yunzhimeng_notify');
//旧地址的回调
//if($_POST['State'] == 101){
//    $dao = new index_dao();
//    $dao->sql = "update yunzhimeng set status=1, merchant_order_id=?, callback_time=? where order_id=?";
//    $dao->params = array($_POST['OrderID'], time(), $_POST['MerchantOrderID']);
//    $dao->doExecute();
//    die('True');
//}else{
//    if(!$_POST['State']){
//        die('Error');
//    }
//    if($_POST['State'] == 102){
//        $status = 0;
//    }elseif($_POST['State'] == 103){
//        $status = 2;
//    }else{
//        $status = 4;
//    }
//    $dao = new index_dao();
//    $dao->sql = "update yunzhimeng set status=?, merchant_order_id=?, callback_time=? where order_id=?";
//    $dao->params = array($status,$_POST['OrderID'], time(), $_POST['MerchantOrderID']);
//    $dao->doExecute();
//    die('True');
//}

if($_POST['status'] == 1){
    $dao = new index_dao();
    $dao->sql = "update yunzhimeng set status=1, merchant_order_id=?, callback_time=? where order_id=?";
    $dao->params = array($_POST['orderno'], time(), $_POST['billno']);
    $dao->doExecute();
    die('OK');
}else{
    if(!$_POST['status']){
        die('Error');
    }
    $dao = new index_dao();
    $dao->sql = "update yunzhimeng set status=?, merchant_order_id=?, callback_time=? where order_id=?";
    $dao->params = array($_POST['status'],$_POST['orderno'], time(), $_POST['billno']);
    $dao->doExecute();
    die('OK');
}