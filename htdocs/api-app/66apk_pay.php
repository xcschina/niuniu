<?php
header("Content-Type: text/html; charset=utf-8");
header('Access-Control-Allow-Origin:*');
require_once 'config.php';
COMMON("paramUtils");
BO('apk_pay_web');
$act = paramUtils::strByGET("act", false);

$bo = new apk_pay_web();
switch ($act) {
    case"nnb_pay":
        $bo->nnb_pay();
        break;
    case"qb_pay":
        $bo->qb_pay();
        break;
    case"qb_order":
        $bo->qb_order();
        break;
    case"nnb_order":
        $bo->nnb_order();
        break;
    default:
        $result = array("result" => "0", "desc" => "接口参数异常。");
        die("0".base64_encode(json_encode($result)));
        break;
}