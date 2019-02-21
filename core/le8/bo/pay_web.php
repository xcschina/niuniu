<?php
COMMON('baseCore', 'pageCore');
COMMON('alipay_mobile/alipay_service',"alipay.mobile.config",'alipay_mobile/alipay_notify');

class pay_web extends baseCore {

    public $DAO;

    public function __construct() {
        parent::__construct();
        $this->user_id=$_SESSION['user_id'];
    }

    public function ali_pay($order){
        //$order['channel_pay_money'] = $order['channel_pay_money']/100;

        $pms1 = array (
            "req_data" => '<direct_trade_create_req><subject>['. $order['title'] .
                ']</subject><out_trade_no>' . $order['order_id'] .
                '</out_trade_no><total_fee>' . $order['channel_pay_money'] .
                "</total_fee><seller_account_name>" . ALI_MOBILE_seller_email .
                "</seller_account_name><notify_url>" . ALI_LE8_notify_url .
                "</notify_url><out_user></out_user><merchant_url>" . ALI_LE8_merchant_url.
                "</merchant_url>" . "<call_back_url>" . ALI_LE8_call_back_url.
                "</call_back_url></direct_trade_create_req>",
            "service" => ALI_MOBILE_Service_Create,
            "sec_id" => ALI_MOBILE_sec_id,
            "partner" => ALI_MOBILE_partner,
            "req_id" => $order['order_id'],
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
            "call_back_url" => ALI_LE8_call_back_url,
            "format"    => ALI_MOBILE_format,
            "v"         => ALI_MOBILE_v
        );
        $alipay->alipay_Wap_Auth_AuthAndExecute($pms2, ALI_MOBILE_key);
    }
}