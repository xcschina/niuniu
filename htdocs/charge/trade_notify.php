<?php
ini_set("display_errors","on");
require_once 'config.php';
DAO('index_dao');
COMMON('alipay.mobile.config','baseCore');
COMMON('alipay_mobile/alipay_service','alipay_mobile/alipay_notify');
$bo = new baseCore();
$bo->err_log($bo->client_ip().":".var_export($_POST,1),'trade_center_notify_');

$_POST['notify_data'] = $_REQUEST['notify_data'];

$alipay = new alipay_notify(ALI_MOBILE_partner, ALI_MOBILE_key, ALI_MOBILE_sec_id, ALI_MOBILE_input_charset); // 构造通知函数信息
$verify_result = $alipay->notify_verify(); // 计算得出通知验证结果

if($verify_result){
    $status = getDataForXML($_POST ['notify_data'], '/notify/trade_status'); // 返回token
    $buyer_email    = getDataForXML($_POST['notify_data'],"/notify/buyer_email");
    $order_id       = getDataForXML($_POST['notify_data'],"/notify/out_trade_no");
    $user_id       = getDataForXML($_POST['notify_data'],"/notify/user_id");
    $money       = getDataForXML($_POST['notify_data'],"/notify/total_fee");
    $ali_order_id   = getDataForXML($_POST['notify_data'],"/notify/trade_no");
    $pay_time   = getDataForXML($_POST['notify_data'],"/notify/gmt_payment");
    try{
        if($status == 'TRADE_FINISHED' || $status == 'TRADE_SUCCESS'){

            $dao = new index_dao();
            $dao->sql = "select * from orders where order_id=?";
            $dao->params = array($order_id);
            $dao->doResult();
            $order = $dao->result;
            if(empty($order)){
                echo "fail";
            }

            $goods_info = $dao->get_goods_info($order['product_id']);
            if($goods_info['type'] == '5' || $goods_info['type'] == '6'){
                $goods_info['new_stock'] = $goods_info['stock']-$order['amount'];
                $dao->update_goods($goods_info);
                if($goods_info['new_stock'] <= 0){
                    $dao->update_pub($goods_info['id']);
                }
            }else{
                $dao->update_pub($goods_info['id']);
            }
            if(!$dao->result){
                $bo->err_log('失败','alipay');
                echo "fail";
            }else{
                if($order['status']!=2){
                    //更新订单状态
                    $dao->sql = "update orders set channel_order_id=?,status=?,payer=?,pay_time=? where order_id=?";
                    $dao->params = array($ali_order_id, 1, $buyer_email, strtotime($pay_time),$order_id);
                    $dao->doExecute();
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