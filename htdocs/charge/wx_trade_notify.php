<?php
header("Content-Type: text/html; charset=utf-8");
require_once 'config.php';
COMMON('baseCore','paramUtils');

$bo = new baseCore();
$data = json_decode(json_encode(simplexml_load_string($GLOBALS["HTTP_RAW_POST_DATA"], 'SimpleXMLElement', LIBXML_NOCDATA)), true);
$bo->err_log($bo->client_ip()."\r".var_export($data,1),'wx_trade_notify');
DAO('index_dao');
$bo = new android_pay();
if($data['return_code']=='SUCCESS' && $data['result_code']=='SUCCESS'){
    try{
        if($data['out_trade_no'] && $data['transaction_id'] && $data['openid']){
            $dao = new index_dao();
            $dao->sql = "select * from orders where order_id=?";
            $dao->params = array($data['out_trade_no']);
            $dao->doResult();
            $order = $dao->result;

            if($order['status']==2){
                //订单已完成，更新用户email
                //更新订单状态
                $dao->sql = "update orders set channel_order_id=?,status=?,payer=?,pay_time=? where order_id=?";
                $dao->params = array($data['transaction_id'], 1, $data['openid'], strtotime($pay_time),$data['out_trade_no']);
                $dao->doExecute();
            }
            echo 'success';
        } else {
            echo "fail";
        }
    }catch (Exception $e){
        $this->err_log($e->getMessage(), "trade_wx_notify");
    }
}elseif($data['result_code']=='FAIL'){
    $bo->err_log($verify_result,'trade_wx_notify');
    echo "fail";
}else{
    $bo->err_log($verify_result,'trade_wx_notify');
    echo "fail";
}
?>