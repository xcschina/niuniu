<?php
//微信菜单设置
header("Content-Type: text/html; charset=utf-8");
require_once 'config.php';
ini_set("display_errors","on");
COMMON('baseCore','paramUtils','weixin.class');
$ret = wxcommon::getToken();
$token = $ret['access_token'];
$industry = '{"industry_id1":"6","industry_id2":"1"}';
$url = 'https://api.weixin.qq.com/cgi-bin/message/template/send?access_token='.$token;

//oHk_htwqLMYxAMVDhakHrx5ErRog
$data = '{
           "touser":"oHk_ht_su5JTQnnX5LxB2DNv1Kmk",
           "template_id":"yV48emV8WusS1AgOpMKc7LQWdSXPHbPbZTMUM-t9zVg",
           "url":"",
           "topcolor":"#ea0000",
           "data":{
                   "first": {
                       "value":"感谢您在66173购买，我们已收到您的付款，客服随后发货，请耐心等待: )",
                       "color":"#0259aa"
                   },
                   "keyword1":{
                       "value":"100",
                       "color":"#0259aa"
                   },
                   "keyword2": {
                       "value":"老牛的新鲜大便",
                       "color":"#0259aa"
                   },
                   "keyword3": {
                       "value":"在线支付",
                       "color":"#0259aa"
                   },
                   "keyword4": {
                       "value":"XX-XXX-XXX-XXX",
                       "color":"#0259aa"
                   },
                   "keyword5": {
                       "value":"2015-01-01",
                       "color":"#0259aa"
                   },
                   "remark":{
                       "value":"如有问题请致电0591-87766173或联系QQ：3311363532",
                       "color":"#555555"
                   }
           }
       }';
$bo = new baseCore();
$content = $bo->request($url, $data);
print_r($content);