<?php
COMMON('baseCore', 'pageCore','alipay','alipay/alipay_submit.class');
COMMON('alipay_mobile/alipay_service',"alipay.mobile.config",'alipay_mobile/alipay_notify');
DAO('qb_dao');

class qb_mobile extends baseCore{

    public $DAO;
    public $id;

    public function __construct(){
        parent::__construct();
        $this->DAO = new qb_dao();
    }

    public function qb_view(){
        $_SESSION['login_back_url'] = 'qb.php';
//        if(!$_SESSION['user_id']){
//            $this->redirect("account.php?act=login");
//            exit;
//        }
        $rate = $this->get_qq_rate(1);
        $this->assign('rate',$rate);
        $this->display('qb_view.html');
    }

    public function buy_qb(){
        $params = $_POST;
        if(!$params || !$params['amount'] || !$params['qq']){
            die("缺少必要参数.");
        }
        $rate = $this->get_qq_rate(1);
        $this->page_hash();
        $this->assign('rate',$rate);
        $this->assign('params',$params);
        $this->display('buy_qb_view.html');
    }

    public function pay_qb(){
        $params = $_POST;
        $this->params_valida($params);
        $rate = $this->get_qq_rate(1);
        $order_id = $this->orderid('QB');
        $product = $this->insert_order($order_id,$params,$rate);
        if($params['pay_mode']=='alipay'){
            $this->ali_pay($product);
        }
//        elseif($params['pay_mode']=='wechat'){
//            $this->wx_pay($product);
//        }
    }

    protected function params_valida($data){
        if(!$_SESSION['user_id']){
            die('角色信息丢失,请刷新后重新登录.');
        }
        if($data['pagehash']!=$_SESSION['page-hash']){
            die('参数异常,请刷新页面后重新购买.');
        }
        if(!$data['amount']|| $data['amount'] < 10 || $data['amount'] > 5000 ){
            die('QB数量必须是10-5000内的整数');
        }
        if(!$data['charge_qq']){
            die('缺少必要参数.');
        }
        if(!$data['pay_mode']){
            die('请选择支付方式.');
        }
        if(!in_array($data['pay_mode'],array("wechat","alipay"))){
            die('未知的支付方式.');
        }
    }

    protected function ali_pay($product){
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

    protected function insert_order($order_id,$params,$rate){
        $params['unit_price'] = $rate/10;
        $params['money'] = $params['amount']*$rate/10;
        $params['pay_money'] = $params['money'];
        $params['title'] = $params['pay_money'].'元充值'.$params['amount'].'QB';

        if($params['pay_mode']=='alipay'){
            $params['pay_channel'] = 1 ;
        }
        $params['buyer_id'] = $_SESSION['user_id'];

        //客服环节加入
        $service = $this->DAO->get_service();
        if(empty($service)){
            $service['id']='114';
        }
        $params['service_id'] = $service['id'];
        $params['order_id'] = $order_id;
        $params['buy_remark'] = $params['message'];
        $this->DAO->insert_order($params);
        return $params;
    }

    public function get_qq_rate($id){
        $rate = 10;
        $info = $this->DAO->get_qq_rate($id);
        if($info['qq_discount']){
            $rate = $info['qq_discount'];
        }
        return $rate;
    }

}