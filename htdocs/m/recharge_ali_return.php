<?php
header("Content-Type: text/html; charset=utf-8");
require_once 'config.php';
COMMON('paramUtils');
BO("trading_mobile");
error_reporting(E_ALL);

$bo = new trading_mobile();

$sign = paramUtils::strByGET("sign", false);
$order_id = paramUtils::strByGET("out_trade_no", false);
$ali_order_id = paramUtils::strByGET("trade_no", false);

$bo->recharge_ali_pay_return();