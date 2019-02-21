<?php
ini_set("display_errors","on");
header("Content-Type: text/html; charset=utf-8");
require_once 'config.php';
COMMON('alipay/alipay_notify.class','baseCore','weixin.class');
$param = array (
    'service' => 'alipay.wap.trade.create.direct',
  'sign' => 'd017c3ccce50d0f6575c265d1440ba47',
  'sec_id' => 'MD5',
  'v' => '1.0',
  'notify_data' => '&lt;notify&gt;&lt;payment_type&gt;1&lt;/payment_type&gt;&lt;subject&gt;sssss]&lt;/subject&gt;&lt;trade_no&gt;2016102121001004920273147596&lt;/trade_no&gt;&lt;buyer_email&gt;79713910@qq.com&lt;/buyer_email&gt;&lt;gmt_create&gt;2016-10-21 16:56:26&lt;/gmt_create&gt;&lt;notify_type&gt;trade_status_sync&lt;/notify_type&gt;&lt;quantity&gt;1&lt;/quantity&gt;&lt;out_trade_no&gt;999920161021165615551376&lt;/out_trade_no&gt;&lt;notify_time&gt;2016-10-21 16:56:27&lt;/notify_time&gt;&lt;seller_id&gt;2088911899697331&lt;/seller_id&gt;&lt;trade_status&gt;TRADE_SUCCESS&lt;/trade_status&gt;&lt;is_total_fee_adjust&gt;N&lt;/is_total_fee_adjust&gt;&lt;total_fee&gt;0.01&lt;/total_fee&gt;&lt;gmt_payment&gt;2016-10-21 16:56:26&lt;/gmt_payment&gt;&lt;seller_email&gt;crab17173@163.com&lt;/seller_email&gt;&lt;price&gt;0.01&lt;/price&gt;&lt;buyer_id&gt;2088002290691924&lt;/buyer_id&gt;&lt;notify_id&gt;8c6de063c33890200e166ff43c1f0ccn3m&lt;/notify_id&gt;&lt;use_coupon&gt;N&lt;/use_coupon&gt;&lt;/notify&gt;',
);

$bo = new baseCore();
$url = "http://charge.66173.cn/ali_le8_notify.php";
$result = $bo->request($url, $param);

print_r($result);