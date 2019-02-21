<?php
header("Content-Type: application/json; charset=utf-8");
require_once 'config.php';
COMMON('paramUtils');
$cpa_id = paramUtils::strByGET("adid");
$appid = paramUtils::strByGET("appid");
$idfa = paramUtils::strByGET("idfa");
$ip = paramUtils::strByGET("ip");
BO('clickServ');
$bo = new clickServ();
$bo->err_log(var_export($_GET,1),'lmsw_activate_log');
if(empty($idfa) || $idfa == 'null' || $idfa == 'NULL' || $idfa == '00000000-0000-0000-0000-000000000000'){
    $idfa = '';
}
if(empty($appid)||empty($idfa)||empty($cpa_id)){
    die(json_encode(array('status'=>0,'message'=>'缺少必要参数。')));
}
$bo->lmsw_callback($cpa_id,$appid,$idfa);
die(json_encode(array('status'=>0,'message'=>'网络异常。')));

