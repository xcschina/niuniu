<?php
//ini_set("display_errors","on");
require_once 'config.php';
COMMON('alipay/alipay_notify.class','baseCore','weixin.class');
DAO('index_dao');
$bo = new baseCore();
$bo->err_log($bo->client_ip().":".var_export($_POST,1),'kamen_notify');

if($_POST['Status'] == 'True'){
    $dao = new index_dao();
    $dao->sql = "update kamen set status=1, kamen_order_id=?, callback_time=? where order_id=?";
    $dao->params = array($_POST['OrderNo'], $_POST['ChargeTime'], $_POST['CustomerOrderNo']);
    $dao->doExecute();
    die('<?xml version="1.0" encoding="utf-8"?><root><ret><status>True</status></ret></root>');
}