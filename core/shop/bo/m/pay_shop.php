<?php
// -------------------------------------------
// 店铺系统 - 支付 <zbc> <2016-04-26>
// -------------------------------------------
COMMON('alipay_mobile/alipay_service',"alipay.mobile.config",'alipay_mobile/alipay_notify');
// COMMON('yeepay/yeepayCommon');
BO('m'.DS.'common_shop');

class pay_shop extends common_shop {

    private $pay_order; // 订单
    public function __construct($pay_order=array()){
        parent::__construct();
        $this->pay_order = $pay_order;
    }

    /**
     * 支付宝
     */
    public function ali_pay(){
        $pms1 = array (
            "req_data" => '<direct_trade_create_req><subject>' . $this->pay_order['title'] .
            ']</subject><out_trade_no>' . $this->pay_order['order_id'] .
            '</out_trade_no><total_fee>' . $this->pay_order['pay_money'] .
            "</total_fee><seller_account_name>" . ALI_MOBILE_seller_email .
            "</seller_account_name><notify_url>" . ALI_MOBILE_notify_url .
            "</notify_url><out_user></out_user><merchant_url>" . ALI_SHOP_MOBILE_merchant_url.
            "</merchant_url>" . "<call_back_url>" . ALI_SHOP_MOBILE_call_back_url.
            "</call_back_url></direct_trade_create_req>",
            "service" => ALI_MOBILE_Service_Create,
            "sec_id"  => ALI_MOBILE_sec_id,
            "partner" => ALI_MOBILE_partner,
            "req_id"  => $this->pay_order['order_id'],
            "format"  => ALI_MOBILE_format,
            "v"       => ALI_MOBILE_v
            );

            // 构造请求函数
        $alipay = new alipay_service();
        $token = $alipay->alipay_wap_trade_create_direct($pms1, ALI_MOBILE_key, ALI_MOBILE_sec_id );
        $pms2 = array (
            "req_data"  => "<auth_and_execute_req><request_token>" . $token . "</request_token></auth_and_execute_req>",
            "service"       => ALI_MOBILE_Service_authAndExecute,
            "sec_id"        => ALI_MOBILE_sec_id,
            "partner"       => ALI_MOBILE_partner,
            "call_back_url" => ALI_SHOP_MOBILE_call_back_url,
            "format"        => ALI_MOBILE_format,
            "v"             => ALI_MOBILE_v
            );
        $alipay->alipay_Wap_Auth_AuthAndExecute($pms2, ALI_MOBILE_key);
    }


    /**
     * 微信
     */
    public function weixin_pay(){
    }


    /**
     * 易宝支付 [暂未使用]
     */
    // public function yee_pay(){
    //  header("Content-Type: text/html; charset=GBK");
    //  $yeepay = new yeepay();

    //  $p2_Order = $this->pay_order['order_id'];
    //  $p3_Amt   = $this->pay_order['pay_money'];
    //  $p4_Cur   = "CNY";
    //  $p5_Pid   = $this->pay_order['title'];
    //  $p6_Pcat  = "手机游戏";
    //  $p7_Pdesc = 'dNF';
    //  $p8_Url   = "http://charge.66173.cn/yeepay.php";
    //  $pa_MP    = $this->pay_order['order_id'];

 //        #支付通道编码
    //  $pd_FrpId        = "";
    //  $pr_NeedResponse = 1;

    //  $form['p2_Order'] = $this->pay_order['order_id'];
    //  $form['p3_Amt']   = $p3_Amt;
    //  $form['p4_Cur']   = $p4_Cur;
    //  $form['p5_Pid']   = $p5_Pid;
    //  $form['p6_Pcat']  = $p6_Pcat;
    //  $form['p7_Pdesc'] = $p7_Pdesc;
    //  $form['p8_Url']   = $p8_Url;
    //  $form['pa_MP']    = $pa_MP;
    //  $form['pd_FrpId'] = $pd_FrpId;
    //  $form['pr_NeedResponse'] = 1;

 //        #调用签名函数生成签名串
    //  $hmac = $yeepay->getReqHmacString($p2_Order,$p3_Amt,$p4_Cur,$p5_Pid,$p6_Pcat,$p7_Pdesc,$p8_Url,$pa_MP,$pd_FrpId,$pr_NeedResponse);
    //  $form['hmac'] = $hmac;
    //  $yeepay->redirect_yeepay($form);
    // }


}