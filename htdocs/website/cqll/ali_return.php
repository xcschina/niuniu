<?php
header("Content-Type: text/html; charset=utf-8");
require_once 'config.php';
COMMON('paramUtils');
BO("game_index_web");
error_reporting(E_ALL);

$bo = new game_index_web();
$bo->open_debug();
$sign = paramUtils::strByGET("sign", false);
$order_id = paramUtils::strByGET("out_trade_no", false);
$ali_order_id = paramUtils::strByGET("trade_no", false);

$bo->ali_pay_return2();