<?php
echo "wonderful";
exit();
/*接口版本：v3*/
header('Content-Type: application/json; charset=utf-8');
require_once 'config.php';
COMMON('paramUtils');
BO('android_pay');
$api = new android_pay();
try{
    $data = $api->make_wx_data(10,'112233');
    $new_data = $api->array_to_xml($data);
    $results = $api->request('https://api.mch.weixin.qq.com/pay/unifiedorder',$new_data);
    $array_data = json_decode(json_encode(simplexml_load_string($results, 'SimpleXMLElement', LIBXML_NOCDATA)), true);
    var_dump($array_data);
//    var_dump($api->object_to_array($result));
//    if($result['state']<>101 && $result['state']<>102){
//        $this->error_msg("接口发生错误【".$result['state-info']."】");
//    }else{
//        $hj_order_id = $result['order-id'];
//        $order_id = $result['merchant-order-id'];
//        $qb = $_SESSION['QBS'][$product_id];
//
//        $this->DAO->insert_order($order_id, $product_id, $qb['price'], $amount, $qb['par-value'], $qb['name'], $qq, $hj_order_id);
//        $this->succeed_msg("购买成功[".$result['state-info']."]");
//    }
}catch (Exception $e){
    $api->err_log(var_export($params,1),"app-secure-pay");
    $api->err_log(var_export($e,1),"app-secure-pay");
}

function set_ua($ua){
    $ua = str_replace(" ","+",$ua);
    $ua = base64_decode(substr($ua,1));
    $ua = explode("&",$ua);
    return $ua;
}