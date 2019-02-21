<?php
header("Content-Type: application/json; charset=utf-8");
require_once 'config.php';
COMMON('paramUtils');
$adid = paramUtils::strByGET("adid");
$appid = paramUtils::strByGET("appid");
$adtid = paramUtils::strByGET("idfa");
$source = paramUtils::strByGET("source");
BO('clickServ');
$bo = new clickServ();
$bo->err_log(var_export($_GET,1),'check_idfa_log');

if(empty($appid)||empty($adtid)||empty($adid)){
    die(json_encode(array('error'=>'缺少必要参数!')));
}

if($bo->get_game_idfa($appid,$adtid)){
    die(json_encode(array($adtid=>1)));
}else{
    die(json_encode(array($adtid=>0)));
}

