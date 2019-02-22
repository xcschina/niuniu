<?php
// --------------------------------------
//     店铺系统 <zbc> < 2016/4/29 >
// --------------------------------------

header("Content-Type: text/html; charset=utf-8");
require_once 'config.php';
COMMON("paramUtils");
BO('index');

$params['sign']         = paramUtils::strByGET("sign", false);
$params['order_id']     = paramUtils::strByGET("out_trade_no", false);
$params['ali_order_id'] = paramUtils::strByGET("trade_no", false);

$bo = new index();
$bo->redirect('order_shop','shop_ali_pay_return',$params); 