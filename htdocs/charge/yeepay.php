<?php
header("Content-Type: text/html; charset=utf-8");
require_once 'config.php';
//ini_set("display_errors","on");
COMMON("paramUtils");
COMMON('yeepay/yeepayCommon','baseCore');
DAO('index_dao');
$bo = new baseCore();
$bo->err_log($bo->client_ip().":".var_export($_REQUEST,1),'yeepay');
$yeepay = new yeepay();
$return = $yeepay->getCallBackValue($r0_Cmd,$r1_Code,$r2_TrxId,$r3_Amt,$r4_Cur,$r5_Pid,$r6_Order,$r7_Uid,$r8_MP,$r9_BType,$hmac);
$bRet = $yeepay->CheckHmac($r0_Cmd,$r1_Code,$r2_TrxId,$r3_Amt,$r4_Cur,$r5_Pid,$r6_Order,$r7_Uid,$r8_MP,$r9_BType,$hmac);
if($bRet){
    if($r1_Code=="1"){

        #	需要比较返回的金额与商家数据库中订单的金额是否相等，只有相等的情况下才认为是交易成功.
        #	并且需要对返回的处理进行事务控制，进行记录的排它性处理，在接收到支付结果通知后，判断是否进行过业务逻辑处理，不要重复进行业务逻辑处理，防止对同一条交易重复发货的情况发生.
        $dao = new index_dao();
        $dao->sql = "select * from orders where order_id=?";
        $dao->params = array($r6_Order);
        $dao->doResult();

        if(!$dao->result){
            die("fail");
        }else{
            if($dao->result['status']!=2){
                $dao->sql = "update orders set channel_order_id=?,status=?,payer=?,pay_time=? where order_id=?";
                $dao->params = array($r2_TrxId, 1, $r7_Uid, $_REQUEST['ru_Trxtime'],$r6_Order);
                $dao->doExecute();
                $bo->send_mail("3311363532@qq.com;252432349@qq.com;270772735@qq.com;5992025@qq.com",'单号'.$r6_Order."付款",'网站有人付款，单号'.$r6_Order."，通道易宝");

                $order = $dao->result;
                $dao->sql = "update user_info set is_vip=?,last_buy_time=? where user_id=?";
                $dao->params = array(1, strtotime("now"), $order['buyer_id']);
                $dao->doExecute();
            }
        }

        if($r9_BType=="1"){
            echo "交易成功";
            echo  "<br />在线支付页面返回";
        }elseif($r9_BType=="2"){
            #如果需要应答机制则必须回写流,以success开头,大小写不敏感.
            die("success");
        }
    }
}else{
    echo "交易信息被篡改";
}