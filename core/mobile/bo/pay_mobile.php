<?php
COMMON('baseCore', 'pageCore','alipay','alipay/alipay_submit.class');
COMMON('alipay_mobile/alipay_service',"alipay.mobile.config",'alipay_mobile/alipay_notify');
COMMON('yeepay/yeepayCommon');

class pay_mobile extends baseCore {

    public $DAO;

    public function __construct() {
        parent::__construct();
        $this->type = array(
            1=>"首充号",
            2=>"首充号续充",
            3=>"代充",
            4=>"账号",
            5=>"游戏币",
            6=>"道具",
            7=>"礼包"
        );
        $this->user_id=$_SESSION['user_id'];
    }

    public function ali_pay($product){
        if($this->user_id==57){
            $product->pay_money = 0.01;
        }
        $pms1 = array (
            "req_data" => '<direct_trade_create_req><subject>[' . $product->title.
                ']</subject><out_trade_no>' . $product->order_id .
                '</out_trade_no><total_fee>' . $product->pay_money .
                "</total_fee><seller_account_name>" . ALI_MOBILE_seller_email .
                "</seller_account_name><notify_url>" . ALI_MOBILE_notify_url .
                "</notify_url><out_user></out_user><merchant_url>" . ALI_MOBILE_merchant_url.
                "</merchant_url>" . "<call_back_url>" . ALI_MOBILE_call_back_url.
                "</call_back_url></direct_trade_create_req>",
            "service" => ALI_MOBILE_Service_Create,
            "sec_id" => ALI_MOBILE_sec_id,
            "partner" => ALI_MOBILE_partner,
            "req_id" => $product->order_id,
            "format" => ALI_MOBILE_format,
            "v" => ALI_MOBILE_v
        );

        // 构造请求函数
        $alipay = new alipay_service();
        $token = $alipay->alipay_wap_trade_create_direct($pms1, ALI_MOBILE_key, ALI_MOBILE_sec_id );
        $pms2 = array (
            "req_data"  => "<auth_and_execute_req><request_token>" . $token . "</request_token></auth_and_execute_req>",
            "service"   => ALI_MOBILE_Service_authAndExecute,
            "sec_id"    => ALI_MOBILE_sec_id,
            "partner"   => ALI_MOBILE_partner,
            "call_back_url" => ALI_MOBILE_call_back_url,
            "format"    => ALI_MOBILE_format,
            "v"         => ALI_MOBILE_v
        );
        $alipay->alipay_Wap_Auth_AuthAndExecute($pms2, ALI_MOBILE_key);
    }

    public function qb_ali_pay($product){
        if($_SESSION['user_id']==71){
            $product['pay_money']=0.01;
            $product['title']=$product['title'].'开发测试订单';
        }
        $pms1 = array (
            "req_data" => '<direct_trade_create_req><subject>' . $product['title'].
                ']</subject><out_trade_no>' . $product['order_id'].
                '</out_trade_no><total_fee>' . $product['pay_money'].
                "</total_fee><seller_account_name>" . ALI_MOBILE_seller_email .
                "</seller_account_name><notify_url>" . ALI_MQB_notify_url .
                "</notify_url><out_user></out_user><merchant_url>" . ALI_MOBILE_merchant_url.
                "</merchant_url>" . "<call_back_url>" . ALI_MQB_call_back_url.
                "</call_back_url></direct_trade_create_req>",
            "service" => ALI_MOBILE_Service_Create,
            "sec_id" => ALI_MOBILE_sec_id,
            "partner" => ALI_MOBILE_partner,
            "req_id" => $product['order_id'],
            "format" => ALI_MOBILE_format,
            "v" => ALI_MOBILE_v
        );

        // 构造请求函数
        $alipay = new alipay_service();
        $token = $alipay->alipay_wap_trade_create_direct($pms1, ALI_MOBILE_key, ALI_MOBILE_sec_id );
        $pms2 = array (
            "req_data"  => "<auth_and_execute_req><request_token>" . $token . "</request_token></auth_and_execute_req>",
            "service"   => ALI_MOBILE_Service_authAndExecute,
            "sec_id"    => ALI_MOBILE_sec_id,
            "partner"   => ALI_MOBILE_partner,
            "call_back_url" => ALI_MQB_call_back_url,
            "format"    => ALI_MOBILE_format,
            "v"         => ALI_MOBILE_v
        );
        $alipay->alipay_Wap_Auth_AuthAndExecute($pms2, ALI_MOBILE_key);
    }
}