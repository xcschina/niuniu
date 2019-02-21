<?php
COMMON('baseCore','alipay','alipay/alipay_submit.class','alipay/alipay_notify.class');
COMMON('alipay_mobile/alipay_service',"alipay.mobile.config",'alipay_mobile/alipay_notify');
DAO('game_pay_dao');
class new_game_pay_web extends baseCore{

    public $DAO;
    public $bjbDAO;
    public $id;
    public $real_app_id;
    public $qa_user_id;

    protected $exchanges;
    protected $api_serv_url;
    protected $api_user_url;
    protected $api_order_url;

    public function __construct(){
        parent::__construct();
        //$this->open_debug();
        $this->DAO = new game_site_dao();
        $this->bjbDAO = new game_pay_dao();
        $this->qa_user_id = array('71','164626','141366');
        //164626 郑群
    }

    public function default_game_pay($company=''){
        try{
            $pay = $_POST;

            $ch = $pay['ch'];
            $time = strtotime("now");
            $ip = $this->client_ip();
            $money = $this->DAO->get_money($pay['money_id']);
            if(GAME_NAME){
                $web_order_id = GAME_NAME.$this->orderid($money['app_id']);
            }else{
                $web_order_id = "WEB".$this->orderid($money['app_id']);
            }

            $game = $this->DAO->get_game_info($money['app_id']);
            if(empty($pay['usr_id'])){
                die("用户信息获取失败");
            }
            $cp_order_id = $this->get_game_order_id($pay, $money, $YD_order_id, $time, $game);
            //获取厂商订单号
            if(!$cp_order_id){
                die("获取厂商订单号错误");
            }
            //订单入库
            $order = $this->insert_web_order($web_order_id, $cp_order_id, $pay, $money, $time, $ip, $ch);

            if(in_array($pay['usr_id'],$this->qa_user_id)){
                $order['pay_money'] = 0.01;
            }
            $this->go_pay($company,$order, $pay, $game);

        }catch (Exception $e){
            $this->DAO->charge_log(0, GAME_ID,0, "充值发起失败",strtotime("now"),0,var_export($e,true),0,'');
            $_SESSION['order_id'] = '';
            $_SESSION['error'] = '充值过程服务器发生错误，请重试';
            $this->redirect("pay.php");
        }
    }

    public function go_pay($company,$order, $pay, $game){
        $company_array = array(
            'yun273',
            '93kk',
            '66173',
            '66173yx'
        );
        if(!in_array($company,$company_array)){
            $company == '66173yx';
        }
        $this->ali_pay($order, $pay, $game,$company);

//        if($game['payee_ch'] == '2'){
//            $this->wx_pay($order, $pay, $game,$company);
//        }else{
//            $this->ali_pay($order, $pay, $game,$company);
//        }
    }

    public function create_guids() {
        $charid = strtolower(md5(uniqid(mt_rand(), true)));
        $uuid = substr($charid, 0, 32);
        return $uuid;
    }

    public function make_wx($data){
        ksort($data);
        $str = '';
        $new_data=array();
        foreach($data as $key => $val ){
            if(!empty($val)){
                $new_data[$key]=$val;
                $str.=$key."=".$val."&";
            }
        }
        $str = $str."key=".BJ_WX_APPKEY;
        $new_data['sign']=strtoupper(md5($str));
        return $new_data;
    }

    public function array_to_xml($arr=array()){
        $xml = '<xml>';
        foreach ($arr as $key => $val){
            if(is_array($val)){
                $xml .= "<".$key.">".$this->array_to_xml($val)."</".$key.">";
            }else{
                $xml .= "<".$key.">".$val."</".$key.">";
            }
        }
        $xml .= "</xml>";
        return $xml;
    }

    public function go_wx_pay($order, $pay, $game){
        $data = array(
            'funcode' => 'WP001',//定值
            'version' => '1.0.0',//定值
            'appId' => XZ_WX_WAP_APPID,
            'mhtOrderNo' => $order['order_id'],
            'mhtOrderName' => $order['title'],
            'mhtOrderType' => '01',//定值（普通消费）
            'mhtCurrencyType' => '156',//定值 156人民币
//            'mhtOrderAmt' => '1',//金额 单位(人民币):分; 整数,无小数点
            'mhtOrderAmt' => $order['pay_money']*100,//金额 单位(人民币):分; 整数,无小数点
            'mhtOrderDetail' => $game['app_name'].'-'.$order['title'],
            'mhtOrderStartTime' => date("YmdHis", time()),
            'notifyUrl' => XZ_WX_WAP_notify_url,
            'frontNotifyUrl'=>XZ_WX_WAP_front_notify_url,
            'mhtCharset'=>'UTF-8',
            'deviceType'=>'0601',
            'payChannelType'=>'13',
            'mhtLimitPay'=>'0',
            'outputType'=>'2',
            'mhtSignType'=>'MD5'
        );
        ksort($data);
        $data_str = "";
        foreach($data as $key => $param){
            if($param !=''){
                $data_str = $data_str."&".$key."=".$param;
            }
        }
        $data_str = substr($data_str, 1);
        $sgin = md5($data_str."&".md5(XZ_WX_WAP_KEY));
        $data['mhtSignature'] = $sgin;
        $result = $this->request('https://pay.ipaynow.cn',$data);
        //参数验证
        $this->xz_wx_result_verify($result,XZ_WX_WAP_KEY);
    }

    public function xz_wx_result_verify($result,$wx_wap_key){
        $msg = '';
        $pop_type = 1;
        if(!$result){
            $msg = '网络异常，请重新支付.';
        }
        $result_array = explode("&",$result);
        $param = array();
        foreach ($result_array as $key => $item){
            $item_array = array();
            $item_array = explode("=",$item);
            if(!empty($item_array)){
                $param[$item_array[0]] = urldecode($item_array[1]);
            }
        }
        if($param['responseCode'] =='A001'){
            //验证sign
            $old_sign = $param['signature'];
            unset($param['signature']);
            ksort($param);
            $data_str = "";
            foreach($param as $key => $data){
                $data_str = $data_str."&".$key."=".$data;
            }
            $data_str = substr($data_str, 1);
            $sgin = md5($data_str."&".md5($wx_wap_key));
            if($sgin != $old_sign){
                $msg = '加密出错';
                $this->err_log(var_export($result,1),'zx_wap_pre_payment');
                $this->err_log(var_export($param,1),'zx_wap_pre_payment');
            }else{
                $pop_type = 2;
                $this->V->assign('pop_type',$pop_type);
                $this->V->assign('wx_token',$param['tn']);
                $this->V->display("weixin_pay_view.html");
            }
        }else{
            $msg = '错误代码'.$param['responseCode'];
        }
        $this->V->assign('pop_type',$pop_type);
        $this->V->assign('msg',$msg);
        $this->V->display("weixin_pay_view.html");
    }

    public function new_wap_alipay($order, $pay, $game){
        COMMON('pay/ali_h5_pay/AopClient','pay/ali_h5_pay/AlipayTradeWapPayContentBuilder','pay/ali_h5_pay/AlipayTradeService');
        require PREFIX.DS."common".DS.'pay/ali_h5_pay/93kk_config.php';
        //商户订单号，商户网站订单系统中唯一订单号，必填
        $out_trade_no = $order['order_id'];
        //订单名称，必填
        $subject = $game['app_name'].'_'.$order['title'];
        //付款金额，必填
        $total_amount = $order['pay_money'];
        //商品描述，可空
        $body = $order['title'];
        //超时时间
        $timeout_express="1m";
        $payRequestBuilder = new AlipayTradeWapPayContentBuilder();
        $payRequestBuilder->setBody($body);
        $payRequestBuilder->setSubject($subject);
        $payRequestBuilder->setOutTradeNo($out_trade_no);
        $payRequestBuilder->setTotalAmount($total_amount);
        $payRequestBuilder->setTimeExpress($timeout_express);
        $payResponse = new AlipayTradeService($config);
        $result = $payResponse->wapPay($payRequestBuilder, $config['return_url'], $config['notify_url']);
        return ;
    }

    public function ali_pay($order, $pay, $game,$company){
        $pms1 = array (
            "req_data" => '<direct_trade_create_req>
                              <subject>'.$game['app_name'].$order['subject'].'</subject>
                              <out_trade_no>'.$order['order_id'].'</out_trade_no>
                              <total_fee>'.$order['pay_money'].'</total_fee>
                              <seller_account_name>'.ALI_MOBILE_seller_email.'</seller_account_name>
                              <notify_url>'.notify_url.'</notify_url>
                              <out_user></out_user>
                              <merchant_url>'.SITE_URL.'</merchant_url>
                              <call_back_url>'.SITE_URL.'ali_return.php</call_back_url>
                            </direct_trade_create_req>',
            "service" => ALI_MOBILE_Service_Create,
            "sec_id" =>  ALI_MOBILE_sec_id,
            "partner" =>  ALI_MOBILE_partner,
            "req_id" => $order['order_id'],
            "format" =>  ALI_MOBILE_format,
            "v" =>  ALI_MOBILE_v
        );
        if($this->is_mobile_client()){
            $pms1['app_pay']='Y';
        }
        $call_back_url = SITE_URL;
        if($order['web_channel']){
            $call_back_url = SITE_URL."ali_return.php";
        }
        // 构造请求函数
        $alipay = new alipay_service();
        $token = $alipay->alipay_wap_trade_create_direct($pms1, ALI_MOBILE_key, ALI_MOBILE_sec_id);
        $pms2 = array (
            "req_data"  => "<auth_and_execute_req><request_token>" . $token . "</request_token></auth_and_execute_req>",
            "service"   => ALI_MOBILE_Service_authAndExecute,
            "sec_id"    => ALI_MOBILE_sec_id,
            "partner"   => ALI_MOBILE_partner,
            "call_back_url" => $call_back_url,
            "format"    => ALI_MOBILE_format,
            "v"         => ALI_MOBILE_v
        );
        if($this->is_mobile_client()){
            $pms2['app_pay']='Y';
        }
        $alipay->alipay_Wap_Auth_AuthAndExecute($pms2,  ALI_MOBILE_key);
    }
    //平台订单创建
    public function insert_web_order($web_order_id, $app_order_id, $pay, $money, $time, $ip, $ch=1){
        $order['app_id']      = GAME_ID;
        $order['order_id']     = $web_order_id;
        $order['app_order_id']  = $app_order_id;
        $order['pay_channel']   = $ch;
        $order['buyer_id']    = $pay['usr_id'];
        $order['role_id']     = $pay['player_id'];
        $order['product_id']  = $money['id'];
        $order['unit_price']  = $money['good_price'];
        $order['title']       = $money['good_name'];
        $order['role_name']   = $pay['usr_name'];
        $order['amount']      = $money['good_amount'];
        $order['pay_money']   = $money['good_price'];
        $order['status']      = 0;
        $order['buy_time']    = $time;
        $order['ip']     = $ip;
        $order['serv_id']   = $pay['serv_id'];
        $order['channel']   = 'ong';
        $order['payExpandData'] = $pay['payexpanddata']?$pay['payexpanddata']:'';
        $order['pay_from'] = 2;
        $order['mac'] = $pay['mac']?$pay['mac']:'';
        $order['idfa'] = $pay['idfa']?$pay['idfa']:'';
        $order['idfv'] = $pay['idfv']?$pay['idfv']:'';
        $order['web_channel'] = $pay['web_channel']?$pay['web_channel']:'';
        if(!$this->DAO->create_order($order)){
            die("订单号创建失败");
        }
        return $order;
    }

    //获取厂商订单号
    public function get_game_order_id($pay, $money, $order_id, $time, $game){
        if($pay['cp_order_id']){
            return $pay['cp_order_id'];
        }
        if(in_array($pay['usr_id'], $this->qa_user_id)){
            $this->is_request_debug = true;
        }

        if($pay['usr_id']==0)$pay['usr_id']='';

        $sign_str = $game['app_id']. $pay['serv_id']. $pay['player_id']. $order_id. $money['good_amount'].$money['good_price']. $time. $game['app_key'];
        $sign = md5($sign_str);

        $post_string = "app_id=".$game['app_id']."&serv_id=".urlencode($pay['serv_id'])."&player_id=".urlencode($pay['player_id']).
            "&order_id=".urlencode($order_id)."&coin=".urlencode($money['good_amount'])."&money=".urlencode($money['good_price']).
            "&create_time=".urlencode($time)."&sign=".urlencode($sign)."&good_code=".$money['good_code'];
        if(!empty($pay['usr_id'])){
            $post_string = $post_string."&usr_id=".$pay['usr_id'];
        }
        $result = $this->request($game['web_order_url'].'?'.$post_string, '', array(), 10);
        $this->err_log($sign_str,'order_test');
        $this->err_log($game['web_order_url'].'?'.$post_string,'order_test');
        $this->err_log($post_string,'order_test');
        $this->err_log($result,'order_test');

        $result = json_decode($result);
        if(!$result){
            die("游戏服务出错,请联络客服");
        }

        if(!$result->err_code && $result->desc){
            if($game['app_id']=='1059'){
                $result_data = array(
                    'order_id'=>$result->desc,
                    'payexpanddata'=>$result->payExpandData,
                );
                return $result_data;
            }
            return $result->desc;
        }else{
            $_SESSION['error'] = "[订单号：".$order_id."]<br />订单号创建失败。<br /><span class=\"red\">[错误代码：10996]</span>";
            $this->redirect("index.php");
        }
    }
}
?>
