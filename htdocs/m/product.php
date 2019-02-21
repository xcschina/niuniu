<?php
header("Content-Type: text/html; charset=utf-8");
require_once 'config.php';
COMMON("paramUtils");
BO('product_mobile');

$act = paramUtils::strByGET("act", false);
$id = paramUtils::intByGET("id", false);

$bo = new product_mobile();

if (strtoupper($_SERVER['REQUEST_METHOD']) !== 'POST') {
    switch ($act){
        case'info':

            header("HTTP/1.1 301 Moved Permanently");
            header ("Location:shop".$id);
//            exit;
//            $bo->product_info($id);
            break;
        default:
            $bo->product_info($id);
            break;
    }
}else{
    switch ($act){
        case'order':
            $bo->product_order($id);
            break;
        case'pay':
            $bo->order_pay($id);
            break;
        default:
            $bo->product_info($id);
            break;
    }
}