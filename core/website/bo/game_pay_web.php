<?php
COMMON('baseCore','alipay','alipay/alipay_submit.class','alipay/alipay_notify.class');
COMMON('alipay_mobile/alipay_service',"alipay.mobile.config",'alipay_mobile/alipay_notify');
DAO('game_pay_dao');
class game_pay_web extends baseCore{

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

    public function game_pay(){
        try{
            $pay = $_POST;

            $money = $this->DAO->get_money($pay['money_id']);
            if(GAME_NAME){
                $YD_order_id = GAME_NAME.$this->orderid($money['app_id']);
            }else{
                $YD_order_id = "WEB".$this->orderid($money['app_id']);
            }
            $game = $this->DAO->get_game_info($money['app_id']);

            $time = strtotime("now");
            $ip = $this->client_ip();

            //获取厂商订单号
            $AppOrderId = $this->get_game_orderid($pay, $money, $YD_order_id, $time, $game);
            if($money['app_id'] =='1059'){
                $pay['payexpanddata'] = $AppOrderId['payexpanddata'];
                $AppOrderId = $AppOrderId['order_id'];
            }
            if(!$AppOrderId){
                throw new Exception("获取厂商订单号错误");
            }
            $ch = $pay['ch'];
            //订单入库
            $order = $this->insert_YD_order($YD_order_id, $AppOrderId, $pay, $money, $time, $ip, $ch);
//            if (in_array($this->client_ip(), array('117.25.82.200','117.27.77.232')) && in_array($money['app_id'], array('1076','1001','1078','1079','1081','1082','1083'))) {
//                $order['pay_money'] = 0.01;
//            }
            if(in_array($pay['usr_id'],$this->qa_user_id)){
                $order['pay_money'] = 0.01;
            }
            if($ch == '2'){
                if($game['payee_ch'] == '2'){
                    $this->go_hn_wx_pay($order, $pay, $game);
                }else{
                    $this->go_hn_wx_pay($order, $pay, $game);
//                    $this->go_wx_pay($order, $pay, $game);
                }
            }else{
                if($game['payee_ch'] == '2'){
                    $this->go_hn_wap_alipay($order, $pay, $game);
                }else{
                    $this->go_wap_alipay($order, $pay, $game);
                }
            }

        }catch (Exception $e){
            $this->DAO->charge_log(0, GAME_ID,0, "充值发起失败",strtotime("now"),0,var_export($e,true),0,'');
            $_SESSION['order_id'] = '';
            $_SESSION['error'] = '充值过程服务器发生错误，请重试';
            $this->redirect("pay.php");
        }
    }

    public function bj_game_pay(){
        try{
            $pay = $_POST;

            $money = $this->DAO->get_money($pay['money_id']);
            if(GAME_NAME){
                $YD_order_id = GAME_NAME.$this->orderid($money['app_id']);
            }else{
                $YD_order_id = "WEB".$this->orderid($money['app_id']);
            }
            $game = $this->DAO->get_game_info($money['app_id']);

            $time = strtotime("now");
            $ip = $this->client_ip();

            //获取厂商订单号
            $AppOrderId = $this->get_game_orderid($pay, $money, $YD_order_id, $time, $game);
            if($money['app_id'] =='1059'){
                $pay['payexpanddata'] = $AppOrderId['payexpanddata'];
                $AppOrderId = $AppOrderId['order_id'];
            }
            if(!$AppOrderId){
                throw new Exception("获取厂商订单号错误");
            }
            $ch = $pay['ch'];
            //订单入库
            $order = $this->insert_YD_order($YD_order_id, $AppOrderId, $pay, $money, $time, $ip, $ch);
//            if (in_array($this->client_ip(), array('117.25.82.200','117.27.77.232')) && in_array($money['app_id'], array('1076','1001','1078','1079','1081','1082','1083'))) {
//                $order['pay_money'] = 0.01;
//            }
            if(in_array($pay['usr_id'],$this->qa_user_id)){
                $order['pay_money'] = 0.01;
            }
            if($ch == '3'){
                $this->bjb_pay($order, $pay, $game);
                exit();
            }
            if($ch == '2'){
                $this->go_bj_wx_pay($order, $pay, $game);
            }else{
                $this->go_bj_wap_alipay($order, $pay, $game);
            }


        }catch (Exception $e){
            $this->DAO->charge_log(0, GAME_ID,0, "充值发起失败",strtotime("now"),0,var_export($e,true),0,'');
            $_SESSION['order_id'] = '';
            $_SESSION['error'] = '充值过程服务器发生错误，请重试';
            $this->redirect("pay.php");
        }
    }
    public function bjb_pay($order, $pay, $game){
//        if($game['app_id']!='6026'){
//            $msg = '游戏不在使用范围,请联系客服';
//            die($msg);
//        }
        if($_SESSION['page-hash'] !=$_POST['page_hash']){
            $msg = '页面存在异常,请退出后重新支付';
            die($msg);
        }
        $user_bjb = $this->bjbDAO->get_user_bjb($order['buyer_id']);
        if(empty($user_bjb)){
            $msg = '支付失败,你的账号没有支付的权限';
            die($msg);
        }
        if($user_bjb['bjb_lock']){
            $msg = '平台充值币已被锁定,请联系管理员';
            die($msg);
        }
        if(empty($order['pay_money'])){
            $msg = '支付金额异常';
            die($msg);
        }
        if(($order['pay_money'] > $user_bjb['bjb']) || empty($user_bjb['bjb'])){
            $msg = '平台币不足';
            die($msg);
        }

        $this->bjbDAO->user_bjb_lock(1, $order['buyer_id']);
        $this->DAO->update_bjb_pay_info($order['order_id']);
        $order['bjb_num'] = $order['pay_money'];

        $this->bjbDAO->update_user_bjb($order);
        $this->bjbDAO->bjb_log($this->create_guid(),$order,2);
        $msg = '充值成功,';
        die($msg);
    }
    public function go_bj_wx_pay($order, $pay, $game){
        $trade_type = 'MWEB';
//        if($params['isPC'] == 1){
//            $trade_type = 'NATIVE';
//        }
        $data = array(
            'appid' => BJ_WX_APPID,
            'mch_id' => BJ_MCH_ID,
            'nonce_str' => $this->create_guids(),
            'body' => $order['title'],
            'out_trade_no' => $order['order_id'],
            'total_fee' => $order['pay_money'] * 100,
            'spbill_create_ip' => $this->client_ip(),
            'notify_url' => BJ_WX_SECURE_notify_url,
            'trade_type' => $trade_type
        );
        $new_data = $this->make_wx($data);
        $xml_data = $this->array_to_xml($new_data);
        $request = $this->request(WX_API_url,$xml_data,array('Content-type: text/xml'));
        $request_data = json_decode(json_encode(simplexml_load_string($request, 'SimpleXMLElement', LIBXML_NOCDATA)), true);
        $this->err_log(var_export($request_data,1),'bj_wx_pay_log');
        $pop_type = 1;
        if($request_data['return_code']=='SUCCESS' && $request_data['result_code']=='SUCCESS'){
            if($trade_type == 'MWEB') {
                if($game['app_type']=='2'){
                    $redirect_url = 'http://'.$_SERVER['HTTP_HOST']."/wx_callback.php";
                    $request_data['mweb_url']=$request_data['mweb_url'].'&redirect_url='.urlencode($redirect_url);
                    $this->err_log(var_export($request_data['mweb_url'],1),'bj_wx_pay_url');
                }
                $wx_token = $request_data['mweb_url'];
                header("location: $wx_token");
            }else{
                $msg = '失败失败-001';
            }
        }else{
            $msg = '失败失败-002';
        }
        $this->V->assign('msg',$msg);

        $this->V->assign('pop_type',$pop_type);
        $this->V->assign('wx_token',$wx_token);
        $this->V->display("weixin_pay_view_new.html");
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

    public function go_hn_wx_pay($order, $pay, $game){
        $data = array(
            'funcode' => 'WP001',//定值
            'version' => '1.0.0',//定值
            'appId' => XZ_HN_WX_WAP_APPID,
            'mhtOrderNo' => $order['order_id'],
            'mhtOrderName' => $order['title'],
            'mhtOrderType' => '01',//定值（普通消费）
            'mhtCurrencyType' => '156',//定值 156人民币
//            'mhtOrderAmt' => '1',//金额 单位(人民币):分; 整数,无小数点
            'mhtOrderAmt' => $order['pay_money']*100,//金额 单位(人民币):分; 整数,无小数点
            'mhtOrderDetail' => $game['app_name'].'-'.$order['title'],
            'mhtOrderStartTime' => date("YmdHis", time()),
            'notifyUrl' => XZ_HN_WX_WAP_notify_url,
            'frontNotifyUrl'=>XZ_HN_WX_WAP_front_notify_url,
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
        $sgin = md5($data_str."&".md5(XZ_HN_WX_WAP_KEY));
        $data['mhtSignature'] = $sgin;
        $result = $this->request('https://pay.ipaynow.cn',$data);
        //参数验证
        $this->xz_wx_result_verify($result,XZ_HN_WX_WAP_KEY);
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

    //支付宝wap支付
    public function go_wap_alipay($order, $pay, $game){
        $pms1 = array (
            "req_data" => '<direct_trade_create_req><subject>' . $game['app_name'].$order['subject'].
                '</subject><out_trade_no>' . $order['order_id'] .
                '</out_trade_no><total_fee>' . $order['pay_money'] .
                "</total_fee><seller_account_name>" . ALI_MOBILE_seller_email .
                "</seller_account_name><notify_url>http://charge.66173.cn/gamesite_notify.php</notify_url><out_user></out_user><merchant_url>" . SITE_URL.
                "</merchant_url>" . "<call_back_url>" . SITE_URL."ali_return.php".
                "</call_back_url></direct_trade_create_req>",
            "service" => ALI_MOBILE_Service_Create,
            "sec_id" => ALI_MOBILE_sec_id,
            "partner" => ALI_MOBILE_partner,
            "req_id" => $order['order_id'],
            "format" => ALI_MOBILE_format,
            "v" => ALI_MOBILE_v
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
        $token = $alipay->alipay_wap_trade_create_direct($pms1, ALI_MOBILE_key, ALI_MOBILE_sec_id );
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
        $alipay->alipay_Wap_Auth_AuthAndExecute($pms2, ALI_MOBILE_key);
    }

    public function go_bj_wap_alipay($order, $pay, $game){
        $pms1 = array (
            "req_data" => '<direct_trade_create_req><subject>' . $game['app_name'].$order['subject'].
                '</subject><out_trade_no>' . $order['order_id'] .
                '</out_trade_no><total_fee>' . $order['pay_money'] .
                "</total_fee><seller_account_name>" . BJ_ALI_MOBILE_seller_email .
                "</seller_account_name><notify_url>http://charge.66173.cn/bj_gamesite_notify.php</notify_url><out_user></out_user><merchant_url>" . SITE_URL.
                "</merchant_url>" . "<call_back_url>" . SITE_URL."ali_return.php".
                "</call_back_url></direct_trade_create_req>",
            "service" => ALI_MOBILE_Service_Create,
            "sec_id" => ALI_MOBILE_sec_id,
            "partner" => BJ_ALI_MOBILE_partner,
            "req_id" => $order['order_id'],
            "format" => ALI_MOBILE_format,
            "v" => ALI_MOBILE_v
        );
        if($this->is_mobile_client()){
            $pms1['app_pay']='Y';
        }
        $call_back_url = SITE_URL."ali_return.php";
//        if($order['web_channel']){
//            $call_back_url = SITE_URL."ali_return.php";
//        }
        // 构造请求函数
        $alipay = new alipay_service();
        $token = $alipay->alipay_wap_trade_create_direct($pms1, BJ_ALI_MOBILE_key, ALI_MOBILE_sec_id );
        $pms2 = array (
            "req_data"  => "<auth_and_execute_req><request_token>" . $token . "</request_token></auth_and_execute_req>",
            "service"   => ALI_MOBILE_Service_authAndExecute,
            "sec_id"    => ALI_MOBILE_sec_id,
            "partner"   => BJ_ALI_MOBILE_partner,
            "call_back_url" => $call_back_url,
            "format"    => ALI_MOBILE_format,
            "v"         => ALI_MOBILE_v
        );
        if($this->is_mobile_client()){
            $pms2['app_pay']='Y';
        }
        $alipay->alipay_Wap_Auth_AuthAndExecute($pms2, BJ_ALI_MOBILE_key);
    }


    public function go_hn_wap_alipay($order, $pay, $game){
        $pms1 = array (
            "req_data" => '<direct_trade_create_req><subject>' . $game['app_name'].$order['subject'].
                '</subject><out_trade_no>' . $order['order_id'] .
                '</out_trade_no><total_fee>' . $order['pay_money'] .
                "</total_fee><seller_account_name>" . HN_ALI_MOBILE_seller_email .
                "</seller_account_name><notify_url>http://charge.66173.cn/hn_gamesite_notify.php</notify_url><out_user></out_user><merchant_url>" . SITE_URL.
                "</merchant_url>" . "<call_back_url>" . SITE_URL."ali_return.php".
                "</call_back_url></direct_trade_create_req>",
            "service" => ALI_MOBILE_Service_Create,
            "sec_id" => ALI_MOBILE_sec_id,
            "partner" => HN_ALI_MOBILE_partner,
            "req_id" => $order['order_id'],
            "format" => ALI_MOBILE_format,
            "v" => ALI_MOBILE_v
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
        $token = $alipay->alipay_wap_trade_create_direct($pms1, HN_ALI_MOBILE_key, ALI_MOBILE_sec_id );
        $pms2 = array (
            "req_data"  => "<auth_and_execute_req><request_token>" . $token . "</request_token></auth_and_execute_req>",
            "service"   => ALI_MOBILE_Service_authAndExecute,
            "sec_id"    => ALI_MOBILE_sec_id,
            "partner"   => HN_ALI_MOBILE_partner,
            "call_back_url" => $call_back_url,
            "format"    => ALI_MOBILE_format,
            "v"         => ALI_MOBILE_v
        );
        if($this->is_mobile_client()){
            $pms2['app_pay']='Y';
        }
        $alipay->alipay_Wap_Auth_AuthAndExecute($pms2, HN_ALI_MOBILE_key);
    }


    //获取厂商订单号
    public function get_game_orderid($pay, $money, $order_id, $time, $game){
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

    //平台订单创建
    public function insert_YD_order($YD_order_id, $app_order_id, $pay, $money, $time, $ip, $ch=1){
        $order['app_id']      = GAME_ID;
        $order['order_id']       = $YD_order_id;
        $order['app_order_id']  = $app_order_id;
        $order['pay_channel']   = $ch;
        if(empty($pay['usr_id'])){
            $pay['usr_id']=57;
        }
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
        $order['ip']     = $this->client_ip();
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
}
?>
