<?php
//ini_set("display_errors","on");
require_once 'config.php';
COMMON('alipay/alipay_notify.class','baseCore','weixin.class');
DAO('index_dao');
$bo = new baseCore();
$bo->err_log($bo->client_ip().":".var_export($_POST,1),'alipay');

$alipay_config['partner']		= ALI_partner;
$alipay_config['key']			= ALI_key;
$alipay_config['sign_type']    = ALI_sign_type;
$alipay_config['input_charset']= ALI_input_charset;
$alipay_config['cacert']    = ALI_cacert;
$alipay_config['transport']    = ALI_transport;

//计算得出通知验证结果
$alipayNotify = new AlipayNotify($alipay_config);
$verify_result = $alipayNotify->verifyNotify();
if($verify_result) {
    //验证成功
    //获取支付宝的通知返回参数，可参考技术文档中服务器异步通知参数列表
	//商户订单号
	$out_trade_no = $_POST['out_trade_no'];
	//支付宝交易号
	$trade_no = $_POST['trade_no'];
	//交易状态
	$trade_status = $_POST['trade_status'];
    $bank_seq_no = '';
    $refund ='';
    if(isset($_POST['bank_seq_no'])){
        $bank_seq_no = $_POST['bank_seq_no'];
    }
    if(isset($_POST['refund_status'])){
         $refund = 1;
    }

    if($_POST['trade_status'] == 'TRADE_FINISHED' || $_POST['trade_status'] == 'TRADE_SUCCESS') {
        try{
            $dao = new index_dao();
            $dao->sql = "select * from orders where order_id=?";
            $dao->params = array($out_trade_no);
            $dao->doResult();

            if(!$dao->result){
                $bo->err_log('失败','alipay');
                echo "fail";
            }else{
                $order = $dao->result;
                if($order['status']!=2 && $refund==''){
                    $dao->sql = "update orders set channel_order_id=?,status=?,payer=?,bank_order_id=?,pay_time=? where order_id=?";
                    $dao->params = array($trade_no, 1, $_POST['buyer_email'], $bank_seq_no, strtotime($_POST['gmt_payment']),$out_trade_no);
                    $dao->doExecute();
                    if($bo->client_ip()!='218.104.231.54'){
                        $bo->send_mail("2874759177@qq.com;3311363532@qq.com;252432349@qq.com;270772735@qq.com;5992025@qq.com;",
                            '单号'.$out_trade_no."付款",'网站有人付款，单号'.$trade_no."，通道支付宝");
                    }
                    $dao->sql = "update user_info set is_vip=?,last_buy_time=? where user_id=?";
                    $dao->params = array(1, strtotime("now"), $order['buyer_id']);
                    $dao->doExecute();
                }

                $dao->sql = "select * from user_info where user_id=?";
                $dao->params = array($order['buyer_id']);
                $dao->doResult();
                $user = $dao->result;
                if($user && $user['wx_id'] && $refund==''){
                    $ret = wxcommon::getToken();
                    $token = $ret['access_token'];
                    pay_msg($user['wx_id'], $token, $order['pay_money'], $order['title'], $order['order_id']);
                }
                echo "success";
            }
        }catch (Exception $e){
            $bo->err_log($e->getMessage(),'alipay');
        }
    }else{
        echo "fail2";
    }
}else {
    echo "fail3";
}