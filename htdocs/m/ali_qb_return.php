<?php
header("Content-Type: text/html; charset=utf-8");
require_once 'config.php';
COMMON("paramUtils");
BO('product_mobile');

$sign = paramUtils::strByGET("sign", false);
$order_id = paramUtils::strByGET("out_trade_no", false);
$ali_order_id = paramUtils::strByGET("trade_no", false);

$bo = new product_mobile();
$bo->ali_qb_return();