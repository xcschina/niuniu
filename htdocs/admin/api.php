<?php
header("Content-Type: text/html; charset=utf-8");
require_once 'config.php';
COMMON("paramUtils");
BO('api_admin','auto_pack_admin');
ini_set("display_errors","On");
error_reporting(E_ALL);
$act = paramUtils::strByGET("act", false);
$_SESSION['usr_id']=71;
$bo = new api_admin();
$pack = new auto_pack_admin();

switch ($act){
    case "verify":
        $apk = paramUtils::strByGET("apk", false);
        $bo->apk_verify($apk);
        break;
    case "do_pack":
        $bo->do_pack();
        break;
    case "do_all_pack":
        $bo->do_all_pack();
        break;
    case "auto_pack":
        $pack->auto_pack();
        break;
}