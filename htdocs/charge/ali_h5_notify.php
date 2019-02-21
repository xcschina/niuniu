<?php
ini_set("display_errors","on");
require_once 'config.php';
COMMON('alipay.mobile.config','baseCore');
COMMON('alipay_mobile/alipay_service','alipay_mobile/alipay_notify');
DAO('index_dao');
$bo = new baseCore();
$bo->err_log($bo->client_ip().":".var_export($_POST,1),'ali_h5_');

$_POST['notify_data'] = $_REQUEST['notify_data'];

$alipay = new alipay_notify(ALI_MOBILE_partner, ALI_MOBILE_key, ALI_MOBILE_sec_id, ALI_MOBILE_input_charset); // 构造通知函数信息
$verify_result = $alipay->notify_verify(); // 计算得出通知验证结果

if ($verify_result){
    $status = getDataForXML($_POST ['notify_data'], '/notify/trade_status'); // 返回token
    $buyer_email    = getDataForXML($_POST['notify_data'],"/notify/buyer_email");
    $order_id       = getDataForXML($_POST['notify_data'],"/notify/out_trade_no");
    $ali_order_id   = getDataForXML($_POST['notify_data'],"/notify/trade_no");
    $pay_time   = getDataForXML($_POST['notify_data'],"/notify/gmt_payment");
    try{
        if ($status == 'TRADE_FINISHED' || $status == 'TRADE_SUCCESS') {
            $dao = new index_dao();
            $dao->sql = "select * from orders where order_id=?";
            $dao->params = array($order_id);
            $dao->doResult();
            $order = $dao->result;

            if(!$dao->result){
                $bo->err_log('失败','alipay');
                echo "fail";
            }else{
                if($order['status']!=2){
                    //更新订单状态
                    $dao->sql = "update orders set channel_order_id=?,status=?,payer=?,pay_time=? where order_id=?";
                    $dao->params = array($ali_order_id, 1, $buyer_email, strtotime($pay_time),$order_id);
                    $dao->doExecute();
                    //更新商品库存
//                    $dao->sql = "select * from products where id=?";
//                    $dao->params = array($order['product_id']);
//                    $dao->doResult();
//                    $product = $dao->result;
//                    if($product['type'] == 4 || $product['type'] == 5 || $product['type'] == 6){
//                        $dao->sql = "update products set stock=stock-".$order['amount']." where id=?";
//                        $dao->params = array($order['product_id']);
//                        $dao->doExecute();
//                    }
                    //更新用户VIP状态
                    $dao->sql = "update user_info set is_vip=?,last_buy_time=? where user_id=?";
                    $dao->params = array(1, strtotime("now"), $order['buyer_id']);
                    $dao->doExecute();

                    $bo->send_mail("2874759177@qq.com;3311363532@qq.com;252432349@qq.com;270772735@qq.com;5992025@qq.com",'单号'.$order_id."付款",'网站有人付款，单号'.$order_id."，通道支付宝");
                }
                echo "success";
            }
        } else {
            //支付失败
            //$this->DAO->update_order_success($order_id, $ali_order_id, $buyer_email, 3);
            echo "fail";
        }
    }catch (Exception $e){
        $this->DAO->charge_log(0, $order_id,0,$e->getMessage(), strtotime("now"));
    }
} else {
    echo "fail";
}