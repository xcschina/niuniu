<?php
COMMON('baseCore','alipay','alipay/alipay_submit.class','alipay/alipay_notify.class');
COMMON('alipay_mobile/alipay_service',"alipay.mobile.config",'alipay_mobile/alipay_notify');
class game_pay_web extends baseCore{

    public $DAO;
    public $id;
    public $real_app_id;
    public $qa_user_id;
    public $usr_params;

    protected $exchanges;
    protected $api_serv_url;
    protected $api_user_url;
    protected $api_order_url;

    public function __construct(){
        parent::__construct();
        $this->DAO = new game_pay_dao();
        $this->qa_user_id = array('71','164626','336622');
        //164626 郑群
        $this->cons_usr_header();
    }

    public function game_pay(){
        try{
            $params = $_POST;
            $post_data= array();
            foreach($params as $key=>$data){
                $post_data[$key] =  $this->rsa_decrypt_params(urldecode($data));
            }
            $pay = array(
                'ch' => 2,
                'money_id' => $post_data['sdkgoodsid'],
                'mode' => 1,
                'player_id' => $post_data['roleId'],
                'timestamp' => time(),
                'app_id' => $post_data['appId'],
                'usr_name' => $post_data['roleName'],
                'payexpanddata' => $post_data['extraInfo'],
                'bank' => 0,
                'wappay' => 1,
                'usr_id' => $post_data['userId'],//缺少userID
                'serv_id' => $post_data['serverId'],
                'cp_order_id' => $post_data['billno'],
                'mac' => "",
                'idfa' => "",
                'idfv' => "",
                'web_channel' => $post_data['channel'],
            );
            $money = $this->DAO->get_money($pay['money_id'],$pay['app_id']);
            if(empty($money)){
                die('0'.base64_encode(json_encode(array("result"=>0,"desc"=>"商品信息出错"))));
            }
            $YD_order_id = "H5".$this->orderid($money['app_id']);
            $game = $this->DAO->get_game_info($money['app_id']);

            $time = strtotime("now");
            $ip = $this->client_ip();

            //获取厂商订单号
            $AppOrderId = $this->get_game_orderid($pay, $money, $YD_order_id, $time, $game);
            if(!$AppOrderId){
                throw new Exception("获取厂商订单号错误");
            }
            $ch = $pay['ch'];
            //订单入库
            $order = $this->insert_YD_order($YD_order_id, $AppOrderId, $pay, $money, $time, $ip, $ch);

            if(in_array($pay['usr_id'],$this->qa_user_id)){
                $order['pay_money'] = 0.01;
            }
            if($game['payee_ch']==3 || $order['app_id']=='1000'){
                $this->go_wx_official_pay($order, $pay, $game);
            }elseif($game['payee_ch']==2){
                $this->go_test_wx_pay($order, $pay, $game);
            }
        }catch (Exception $e){
            $this->DAO->charge_log(0, GAME_ID,0, "充值发起失败",strtotime("now"),0,var_export($e,true),0,'');
            $_SESSION['order_id'] = '';
            $_SESSION['error'] = '充值过程服务器发生错误，请重试';
            $this->redirect("pay.php");
        }
    }
    public function go_wx_official_pay($order, $pay, $game){
        $data = array(
            'appid' => BJ_WX_APPID,
            'mch_id' => BJ_MCH_ID,
            'nonce_str' => $this->create_guids(),
            'body' => $order['title'],
            'out_trade_no' => $order['order_id'],
            'total_fee' => $order['pay_money']*100,
            'spbill_create_ip' => $this->client_ip(),
            'notify_url' => BJ_WX_SECURE_notify_url,
            'openid' => $_SESSION['wx_openid'],
            'trade_type' => 'JSAPI',
        );
        $new_data = $this->make_wx($data,BJ_WX_APPKEY);
        $xml_data = $this->array_to_xml($new_data);
        $request = $this->request(WX_API_url,$xml_data,array('Content-type: text/xml'));
        $request_data = json_decode(json_encode(simplexml_load_string($request, 'SimpleXMLElement', LIBXML_NOCDATA)), true);
        $this->err_log(var_export($request_data,1),'bj_wx_h5_log');
        if($request_data['return_code']=='FAIL'){
            $result = array("result"=>0,"desc"=>$request_data['return_msg']);
            die("0".base64_encode(json_encode($result)));
        }elseif($request_data['return_code']=='SUCCESS' && $request_data['result_code']=='SUCCESS'){
            $wx_order_id = $request_data['prepay_id'];
            $wx_nonce = $request_data['nonce_str'];
//                $this->DAO->update_wx_order();
        }elseif($request_data['return_code']=='SUCCESS' && $request_data['result_code']=='FAIL'){
            $result = array("result"=>0,"desc"=>$request_data['err_code_des'].$request_data['err_code']);
            die("0".base64_encode(json_encode($result)));
        }else{
            $result = array("result"=>0,"desc"=>'请求订单异常.'.$request_data);
            die("0".base64_encode(json_encode($result)));
        }

        $result_data = array(
            "appId"=>BJ_WX_APPID,
            "timeStamp"=>time(),
            "nonceStr"=>$wx_nonce,
//            "prepay_id"=>$wx_order_id,
            "package"=>"prepay_id=".$wx_order_id,
            "signType"=>'MD5',
//            "paySign"=>$new_data['sign']
        );
        ksort($result_data);
        $sign_str = urldecode(http_build_query($result_data));
        $sign_str = $sign_str."&key=".BJ_WX_APPKEY;
        $result_data['paySign']=md5($sign_str);
        $result_data['prepay_id']=$wx_order_id;
        $result_data['wxAppId']=BJ_WX_APPID;
        $result = array(
            "result" =>1,
            "desc" =>"订单请求成功",
            "data" =>$result_data,
        );
        die("0".base64_encode(json_encode($result)));
    }

    public function go_test_wx_pay($order, $pay, $game){
//        if($game['payee_ch']=='3'){
//            $wx_app_id = '';
//            $notifyUrl = '';
//            $frontNotifyUrl = '';
//            $mhtSubAppId = '';
//            $consumerId = $_SESSION['wx_openid'];
//        }elseif($game['payee_ch']=='2'){
//
//        }else{
//
//        }
        $data = array(
            'funcode' => 'WP001',//定值
            'version' => '1.0.0',//定值
            'appId' => WXGZH_appID,
            'mhtOrderNo' => $order['order_id'],
            'mhtOrderName' => $order['title'],
            'mhtOrderType' => '01',//定值（普通消费）
            'mhtCurrencyType' => '156',//定值 156人民币
//            'mhtOrderAmt' => '1',//金额 单位(人民币):分; 整数,无小数点
            'mhtOrderAmt' => $order['pay_money']*100,//金额 单位(人民币):分; 整数,无小数点
            'mhtOrderDetail' => $game['app_name'].'-'.$order['title'],
            'mhtOrderStartTime' => date("YmdHis", time()),
            'notifyUrl' => WXGZH_callback,
            'frontNotifyUrl'=>XZ_WX_WAP_front_notify_url,
            'mhtCharset'=>'UTF-8',
            'deviceType'=>'0600',
            'payChannelType'=>'13',
            'mhtLimitPay'=>'0',
            'mhtSubAppId'=> WXGZH_mhtSubAppId,
            'consumerId'=>$_SESSION['wx_openid'],
//            'consumerId'=>"oHk_ht6Scu6p-gqfLQi-d6Ccxals",
            'outputType'=>'1',
            'mhtSignType'=>'MD5'
        );

        ksort($data);
        $data_str = "";
        foreach($data as $key => $param){
            if($param !=''){
                $data_str = $data_str."&".$key."=".$param;
            }
        }
        $wx_wap_key = WXGZH_key;
        $data_str = substr($data_str, 1);
        $sgin = md5($data_str."&".md5($wx_wap_key));
        $data['mhtSignature'] = $sgin;
        $url = 'https://pay.ipaynow.cn';
        $data_str = $data_str.'&mhtSignature='.$sgin;
        $result = $this->request('https://pay.ipaynow.cn',$data);
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
            $sign = md5($data_str."&".md5($wx_wap_key));
            if($sign != $old_sign){
                $msg = '加密出错';
                $this->err_log(var_export($result,1),'zx_wap_pre_payment');
                $this->err_log(var_export($param,1),'zx_wap_pre_payment');
                die('0'.base64_encode(json_encode(array("result"=>0,"desc"=>"wx".$msg))));
            }else{
                $tn_array = explode("&",$param['tn']);
                $tn_param = array();
                foreach ($tn_array as $key => $tn_item){
                    $item_array = array();
                    $item_array = explode("=",$tn_item);
                    if(!empty($item_array)){
                        $tn_param[$item_array[0]] = urldecode($item_array[1]);
                    }
                }
                die('0'.base64_encode(json_encode(array("result"=>1,"desc"=>"订单请求成功","data" => $tn_param))));
            }
        }else{
            $msg = '错误代码'.$param['responseCode'];
            $this->err_log(var_export($data,1),'zx_wap_pre_payment');
            $this->err_log(var_export($param,1),'zx_wap_pre_payment');
            die('0'.base64_encode(json_encode(array("result"=>0,"desc"=>"请求失败".$msg))));
        }
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
        $this->err_log($post_string,'order_test');
        $this->err_log($result,'order_test');

        $result = json_decode($result);
        if(!$result){
            die("游戏服务出错,请联络客服");
        }

        if(!$result->err_code && $result->desc){
            return $result->desc;
        }else{
            $_SESSION['error'] = "[订单号：".$order_id."]<br />订单号创建失败。<br /><span class=\"red\">[错误代码：10996]</span>";
            $this->redirect("index.php");
        }
    }

    //平台订单创建
    public function insert_YD_order($YD_order_id, $app_order_id, $pay, $money, $time, $ip, $ch=1){
        $order['app_id']      = $pay['app_id'];
        $order['order_id']       = $YD_order_id;
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
    public function do_login(){
        $game_id = $_GET['i'];
        $ch =  $_GET['c'];
        $type =  $_GET['t'];
        header('HTTP/1.1 301 Moved Permanently');
        $url = "http://wx.66173yx.com/games.php?game_id=".$game_id."&ch=".$ch."&t=".$type;
        if(!empty($type)){
            $url = "https://open.weixin.qq.com/connect/oauth2/authorize?appid=wxf965c4a0c7bf9851&redirect_uri=".urlencode($url)."&response_type=code&scope=snsapi_userinfo&state=STATE#wechat_redirect";
        }else{
            $url = "https://open.weixin.qq.com/connect/oauth2/authorize?appid=wxf965c4a0c7bf9851&redirect_uri=".urlencode($url)."&response_type=code&scope=snsapi_base&state=STATE#wechat_redirect";
        }
        header('Location: '.$url);
    }

    public function game_login($game_id,$channel,$token,$user_id){
        if(empty($game_id)){
            die("缺少游戏id");
        }
        //token验证
        if(!$_SESSION['usr_info']['user_id']){
            $this->do_login();
            die("token丢失，请重新登录1");
        }
        $user_id = $_SESSION['usr_info']['user_id'];
        $user_info = $this->DAO->get_user_info($user_id);
        if($user_info['token']!=$token){
            $this->do_login();
            die("token已失效，请重新登录2");
        }
        $game_info = $this->DAO->get_h5_game_info($game_id);
        if(!$game_info['version_url']){
            die("游戏已下架。");
        }
        //查询是否有记录。
        $user_apptb = $this->DAO->get_user_apptb($game_id,$user_id);
        $ip = $this->client_ip();
        if(empty($user_apptb)){
            $this->DAO->add_user_apptb($user_id,$game_id,$token,$ip,$channel);
            //sdk登录日志
        }else{
            $this->DAO->update_user_apptb($token,$ip,$channel,$user_apptb);
            //sdk登录日志
        }
        $mobile = '';
        if($user_info['mobile']){
            $mobile = substr_replace($user_info['mobile'],"******", 3, 6);
        }
        $this->V->assign('if_du',0);//开启debug
        $this->V->assign('user_id',$user_id);
        $this->V->assign('sex',$_SESSION['wx_usr_info']['sex']);
        $this->V->assign('user_name',$user_info['login_name']);
        $this->V->assign('img',$_SESSION['wx_usr_info']['headimgurl']);
        $this->V->assign('appid',$game_id);
        $this->V->assign('game_info',$game_info);
        $this->V->assign('m_verified',$user_info['m_verified']);//0 未验证  1 已验证
        $this->V->assign('mobile',$mobile);
        $this->V->assign('registerTime',$user_info['reg_time']);
        $this->V->assign('game_url',$game_info['version_url']);
        $this->V->assign('token',$token);
        $this->V->assign('channel',$channel);
        $this->V->display('games.html');
    }


    public function user_verify($game_id,$user_id,$sign){
        if(empty($game_id)){
            die(json_encode(array("result"=>0,"desc"=>"缺少游戏参数")));
        }
        if(empty($user_id)){
            die(json_encode(array("result"=>0,"desc"=>"缺少用户信息")));
        }
        if(empty($sign)){
            die(json_encode(array("result"=>0,"desc"=>"缺少sign")));
        }
        $app_info = $this->DAO->get_game_info($game_id);
        if(empty($app_info)){
            die(json_encode(array("result"=>0,"desc"=>"游戏信息错误")));
        }
        $new_sign = $game_id.$user_id.$app_info['app_key'];
        if(md5($new_sign)==$sign){
            $user_info = $this->DAO->get_user_info($user_id);
            if($user_info['mobile']){
                die(json_encode(array("result"=>1,"desc"=>"查询成功","mobile"=>$user_info['mobile'],'is_mobile'=>1)));
            }else{
                die(json_encode(array("result"=>1,"desc"=>"查询成功","mobile"=>'','is_mobile'=>0)));
            }
        }else{
            die(json_encode(array("result"=>0,"desc"=>"sign验证失败")));
        }
        die(json_encode(array("result"=>0,"desc"=>"网络异常")));
    }

    public function add_role(){
        $app_id = $this->usr_params['app_id'];
        $user_id = $this->usr_params['user_id'];
        $server_id = $this->usr_params['server_id'];
        $role_id = $this->usr_params['role_id'];
        if(!$app_id || !$user_id ||!$server_id||!$role_id){
            $this->err_log(var_export($this->usr_params,1),'h5_role_error');
            die('0'.base64_encode(json_encode(array("result"=>0,"desc"=>"缺少必要参数"))));
        }
        $user_info = $this->DAO->get_user_app_info($app_id,$user_id,$server_id,$role_id);
        $ip = $this->client_ip();
        if(empty($user_info)){
            $this->DAO->add_user_app_info($this->usr_params,$ip);
        }else{
            $this->DAO->update_user_app_info($this->usr_params,$ip,$user_info['ID']);
        }
        die('0'.base64_encode(json_encode(array("result"=>1,"desc"=>"成功"))));
    }

    public function add_device(){
        $app_id = $this->usr_params['app_id'];
        $user_id = $this->usr_params['user_id'];
        $ip = $this->client_ip();
        if(!$app_id){
            $this->err_log(var_export($this->usr_params,1),'h5_device_error');
            die('0'.base64_encode(json_encode(array("result"=>0,"desc"=>"缺少必要参数"))));
        }
        $this->DAO->add_device_log($this->usr_params,$ip,$this->create_guid());
        die('0'.base64_encode(json_encode(array("result"=>1,"desc"=>"成功"))));
    }

    public function add_login(){
        $app_id = $this->usr_params['app_id'];
        $user_id = $this->usr_params['user_id'];
        $ip = $this->client_ip();
        if(!$app_id || !$user_id){
            $this->err_log(var_export($this->usr_params,1),'h5_login_error');
            die('0'.base64_encode(json_encode(array("result"=>0,"desc"=>"缺少必要参数"))));
        }
        $this->DAO->add_user_log($this->usr_params,$ip,'H5自动登录');
        die('0'.base64_encode(json_encode(array("result"=>1,"desc"=>"成功"))));
    }

    public function account_pwd(){
        $mobile = $this->rsa_decrypt_params(urldecode($_POST['mobile']));
        $password = $this->rsa_decrypt_params(urldecode($_POST['password']));
        if(!$mobile){
            die('0'.base64_encode(json_encode(array("result"=>0,"desc"=>"手机号不能为空"))));
        }
        if(!$this->is_mobile((int)$mobile)){
            die('0'.base64_encode(json_encode(array("result"=>0,"desc"=>"手机号格式错误"))));
        }
        if(empty($password)){
            die('0'.base64_encode(json_encode(array("result"=>0,"desc"=>"密码不能为空"))));
        }

        $mobile_info = $this->DAO->get_user_by_mobile($mobile);
        if(!$mobile_info){
            die('0'.base64_encode(json_encode(array("result"=>0,"desc"=>"该手机号未注册。"))));
        }
        if($mobile_info['password']===md5($password)){
//            if(!$mobile_info['hn_wx_id']){
//                die('0'.base64_encode(json_encode(array("result"=>0,"desc"=>"该手机号未绑定账号"))));
//            }
            //登录成功
            $game_id = $_SESSION['h5_game_id'];
            $channel = $_SESSION['h5_channel'];
            $game_info = $this->DAO->get_h5_game_info($game_id);
            if(!$game_info['version_url']){
                die('0'.base64_encode(json_encode(array("result"=>0,"desc"=>"游戏已下架。"))));
            }
            $user_info = $mobile_info;
            $user_id = $user_info['user_id'];
            //查询是否有记录。
            $user_apptb = $this->DAO->get_user_apptb($game_id,$user_id);
            $ip = $this->client_ip();
            $token = $this->create_guid();
            $this->DAO->update_usr_token($user_id,$token);
            if(empty($user_apptb)){
                $this->DAO->add_user_apptb($user_id,$game_id,$token,$ip,$channel);
                //sdk登录日志
            }else{
                $this->DAO->update_user_apptb($token,$ip,$channel,$user_apptb);
                //sdk登录日志
            }
            $mobile = '';
            if($user_info['mobile']){
                $mobile = substr_replace($user_info['mobile'],"******", 3, 6);
            }
            $_SESSION['h5_user_info']['if_du'] = 0;
            $_SESSION['h5_user_info']['user_id'] = $user_id;
            $_SESSION['h5_user_info']['sex'] = 3;
            $_SESSION['h5_user_info']['user_name'] = $user_info['login_name'];
            $_SESSION['h5_user_info']['img'] = '';
            $_SESSION['h5_user_info']['appid'] = $game_id;
            $_SESSION['h5_user_info']['game_info'] = $game_info;
            $_SESSION['h5_user_info']['m_verified'] = $user_info['m_verified'];
            $_SESSION['h5_user_info']['registerTime'] = $user_info['reg_time'];
            $_SESSION['h5_user_info']['game_url'] = $game_info['version_url'];
            $_SESSION['h5_user_info']['mobile'] = $mobile;
            $_SESSION['h5_user_info']['token'] = $token;
            $_SESSION['h5_user_info']['channel'] = $channel;
            $user_info['app_id'] = $game_id;
            $user_info['channel'] = $channel;
            $user_info['token'] = $token;
            $this->DAO->add_user_log($user_info,$ip,'H5登录');
            die('0'.base64_encode(json_encode(array("result"=>1,"desc"=>"登录成功。"))));
        }else{
            die('0'.base64_encode(json_encode(array("result"=>0,"desc"=>"密码错误。"))));
        }
    }

    public function account_code(){;
        $mobile = $this->rsa_decrypt_params(urldecode($_POST['mobile']));
        $verifycode = $this->rsa_decrypt_params(urldecode($_POST['verifycode']));
        if(!$mobile){
            die('0'.base64_encode(json_encode(array("result"=>0,"desc"=>"手机号不能为空"))));
        }
        if(!$this->is_mobile((int)$mobile)){
            die('0'.base64_encode(json_encode(array("result"=>0,"desc"=>"手机号格式错误"))));
        }
        if(empty($verifycode)){
            die('0'.base64_encode(json_encode(array("result"=>0,"desc"=>"验证码不能为空"))));
        }
        if(!isset($verifycode) || $_SESSION['check_reg_core']!=$mobile."_".$verifycode || $verifycode==""){
            die('0'.base64_encode(json_encode(array("result"=>0,"desc"=>"验证码错误"))));
        }

        if(!$error && strtotime("now")-$_SESSION['code_last_send_time']>300){
            die('0'.base64_encode(json_encode(array("result"=>0,"desc"=>"验证码超时"))));
        }
        $mobile_info = $this->DAO->get_user_by_mobile($mobile);

        if(!$mobile_info){
            die('0'.base64_encode(json_encode(array("result"=>0,"desc"=>"该手机号未注册。"))));
        }else{
//            if(!$mobile_info['hn_wx_id']){
//                die('0'.base64_encode(json_encode(array("result"=>0,"desc"=>"该手机号未绑定账号"))));
//            }
            //登录成功
            $game_id = $_SESSION['h5_game_id'];
            $channel = $_SESSION['h5_channel'];
            $game_info = $this->DAO->get_h5_game_info($game_id);
            if(!$game_info['version_url']){
                die('0'.base64_encode(json_encode(array("result"=>0,"desc"=>"游戏已下架。"))));
            }
            $user_info = $mobile_info;
            $user_id = $user_info['user_id'];
            //查询是否有记录。
            $user_apptb = $this->DAO->get_user_apptb($game_id,$user_id);
            $ip = $this->client_ip();
            $token = $this->create_guid();
            $this->DAO->update_usr_token($user_id,$token);
            if(empty($user_apptb)){
                $this->DAO->add_user_apptb($user_id,$game_id,$token,$ip,$channel);
                //sdk登录日志
            }else{
                $this->DAO->update_user_apptb($token,$ip,$channel,$user_apptb);
                //sdk登录日志
            }
            $mobile = '';
            if($user_info['mobile']){
                $mobile = substr_replace($user_info['mobile'],"******", 3, 6);
            }
            $_SESSION['h5_user_info']['if_du'] = 0;
            $_SESSION['h5_user_info']['user_id'] = $user_id;
            $_SESSION['h5_user_info']['sex'] = 3;
            $_SESSION['h5_user_info']['user_name'] = $user_info['login_name'];
            $_SESSION['h5_user_info']['img'] = '';
            $_SESSION['h5_user_info']['appid'] = $game_id;
            $_SESSION['h5_user_info']['game_info'] = $game_info;
            $_SESSION['h5_user_info']['m_verified'] = $user_info['m_verified'];
            $_SESSION['h5_user_info']['registerTime'] = $user_info['reg_time'];
            $_SESSION['h5_user_info']['game_url'] = $game_info['version_url'];
            $_SESSION['h5_user_info']['mobile'] = $mobile;
            $_SESSION['h5_user_info']['token'] = $token;
            $_SESSION['h5_user_info']['channel'] = $channel;
            $user_info['app_id'] = $game_id;
            $user_info['channel'] = $channel;
            $user_info['token'] = $token;
            $this->DAO->add_user_log($user_info,$ip,'H5登录');
            die('0'.base64_encode(json_encode(array("result"=>1,"desc"=>"登录成功。"))));
        }
    }

    public function account_reg(){;
        $mobile = $this->rsa_decrypt_params(urldecode($_POST['mobile']));
        $password = $this->rsa_decrypt_params(urldecode($_POST['password']));
        $verifycode = $_POST['verifycode'];

        if(!$mobile){
            die('0'.base64_encode(json_encode(array("result"=>0,"desc"=>"手机号不能为空"))));
        }
        if(!$this->is_mobile((int)$mobile)){
            die('0'.base64_encode(json_encode(array("result"=>0,"desc"=>"手机号格式错误"))));
        }
        if(empty($password)){
            die('0'.base64_encode(json_encode(array("result"=>0,"desc"=>"密码不能为空"))));
        }
        if(empty($verifycode)){
            die('0'.base64_encode(json_encode(array("result"=>0,"desc"=>"验证码不能为空"))));
        }

        if(!isset($verifycode) || $_SESSION['h5_reg_core']!=$mobile."_".$verifycode || $verifycode==""){
            die('0'.base64_encode(json_encode(array("result"=>0,"desc"=>"验证码错误"))));
        }

        if(!$error && strtotime("now")-$_SESSION['reg_last_send_time']>300){
            die('0'.base64_encode(json_encode(array("result"=>0,"desc"=>"验证码超时"))));
        }
        if(!$error && strlen($password)<6 || strlen($password)>18){
            die('0'.base64_encode(json_encode(array("result"=>0,"desc"=>"密码长度6-18位之间"))));
        }
        $mobile_info = $this->DAO->get_user_by_mobile($mobile);

        if($mobile_info){
            die('0'.base64_encode(json_encode(array("result"=>0,"desc"=>"该手机号已注册。"))));
        }else{
            //登录成功
            $usr_id = $this->DAO->insert_user_info(md5($password),$mobile,$this->client_ip());

            $game_id = $_SESSION['h5_game_id'];
            $channel = $_SESSION['h5_channel'];
            $game_info = $this->DAO->get_h5_game_info($game_id);
            if(!$game_info['version_url']){
                die('0'.base64_encode(json_encode(array("result"=>0,"desc"=>"游戏已下架。"))));
            }
            $mobile_info = $this->DAO->get_user_by_mobile($mobile);
            if(!$mobile_info){
                die('0'.base64_encode(json_encode(array("result"=>0,"desc"=>"注册失败。"))));
            }
            $user_info = $mobile_info;
            $user_id = $usr_id;
            //查询是否有记录。
            $user_apptb = $this->DAO->get_user_apptb($game_id,$user_id);
            $ip = $this->client_ip();
            $token = $this->create_guid();
            $this->DAO->update_usr_token($user_id,$token);
            if(empty($user_apptb)){
                $this->DAO->add_user_apptb($user_id,$game_id,$token,$ip,$channel);
            }else{
                $this->DAO->update_user_apptb($token,$ip,$channel,$user_apptb);
            }
            $mobile = '';
            if($user_info['mobile']){
                $mobile = substr_replace($user_info['mobile'],"******", 3, 6);
            }
            $_SESSION['h5_user_info']['if_du'] = 0;
            $_SESSION['h5_user_info']['user_id'] = $user_id;
            $_SESSION['h5_user_info']['sex'] = 3;
            $_SESSION['h5_user_info']['user_name'] = $user_info['login_name'];
            $_SESSION['h5_user_info']['img'] = '';
            $_SESSION['h5_user_info']['appid'] = $game_id;
            $_SESSION['h5_user_info']['game_info'] = $game_info;
            $_SESSION['h5_user_info']['m_verified'] = $user_info['m_verified'];
            $_SESSION['h5_user_info']['registerTime'] = $user_info['reg_time'];
            $_SESSION['h5_user_info']['game_url'] = $game_info['version_url'];
            $_SESSION['h5_user_info']['mobile'] = $mobile;
            $_SESSION['h5_user_info']['token'] = $token;
            $_SESSION['h5_user_info']['channel'] = $channel;
            $user_info['app_id'] = $game_id;
            $user_info['channel'] = $channel;
            $user_info['token'] = $token;
            $this->DAO->add_user_log($user_info,$ip,'H5登录');
            die('0'.base64_encode(json_encode(array("result"=>1,"desc"=>"登录成功。"))));
        }
    }

    public function reset_pwd(){
        $mobile = $this->rsa_decrypt_params(urldecode($_POST['mobile']));
        $password = $this->rsa_decrypt_params(urldecode($_POST['password']));
        $verifycode = $this->rsa_decrypt_params(urldecode($_POST['verifycode']));
        $passwordAgain = $this->rsa_decrypt_params(urldecode($_POST['passwordAgain']));
        if(!$mobile){
            die('0'.base64_encode(json_encode(array("result"=>0,"desc"=>"手机号不能为空"))));
        }
        if(!$this->is_mobile((int)$mobile)){
            die('0'.base64_encode(json_encode(array("result"=>0,"desc"=>"手机号格式错误"))));
        }
        if(empty($password)){
            die('0'.base64_encode(json_encode(array("result"=>0,"desc"=>"密码不能为空"))));
        }
        if(empty($passwordAgain)){
            die('0'.base64_encode(json_encode(array("result"=>0,"desc"=>"二次密码不能为空"))));
        }
        if($passwordAgain != $password){
            die('0'.base64_encode(json_encode(array("result"=>0,"desc"=>"两次密码不一致"))));
        }
        if(empty($verifycode)){
            die('0'.base64_encode(json_encode(array("result"=>0,"desc"=>"验证码不能为空"))));
        }
        if(!$error && !isset($verifycode) || $_SESSION['check_reg_core']!=$mobile."_".$verifycode || $verifycode==""){
            die('0'.base64_encode(json_encode(array("result"=>0,"desc"=>"验证码错误"))));
        }

        if(!$error && strtotime("now")-$_SESSION['code_last_send_time']>300){
            die('0'.base64_encode(json_encode(array("result"=>0,"desc"=>"验证码超时"))));
        }
        if(!$error && strlen($password)<6 || strlen($password)>18){
            die('0'.base64_encode(json_encode(array("result"=>0,"desc"=>"密码长度6-18位之间"))));
        }

        $mobile_info = $this->DAO->get_user_by_mobile($mobile);

        if(!$mobile_info){
            die('0'.base64_encode(json_encode(array("result"=>0,"desc"=>"该手机号未注册。"))));
        }else{
//            if(!$mobile_info['hn_wx_id']){
//                die('0'.base64_encode(json_encode(array("result"=>0,"desc"=>"该手机号未绑定账号"))));
//            }
            //更新密码

            $this->DAO->update_user_psw(md5($password),$mobile,$mobile_info['user_id'],$this->create_guid());
            $mobile_info['app_id'] = $_SESSION['h5_game_id'];
            $mobile_info['channel'] = $_SESSION['h5_channel'];
            $this->DAO->add_user_log($mobile_info,$ip,'重置密码');
            die('0'.base64_encode(json_encode(array("result"=>1,"desc"=>"密码重置成功。"))));
        }
    }

    public function log_out(){
        $_SESSION['h5_user_info']['app_id'] = $_SESSION['h5_user_info']['appid'] ;
        $user_info = $_SESSION['h5_user_info'];
        unset($_SESSION['h5_user_info']);
        $ip = $this->client_ip();
        $this->DAO->add_user_log($user_info,$ip,'登出');
        die('0'.base64_encode(json_encode(array("result"=>1,"desc"=>"注销成功。"))));
    }

    public function check_mobile(){
        $mobile = $this->rsa_decrypt_params(urldecode($_POST['mobile']));
        $user_id = $this->usr_params['user_id'];
        if(!$mobile){
            die('0'.base64_encode(json_encode(array("result"=>0,"desc"=>"手机号不能为空"))));
        }
        if(!$this->is_mobile((int)$mobile)){
            die('0'.base64_encode(json_encode(array("result"=>0,"desc"=>"手机号格式错误"))));
        }
        if(!$user_id){
            die('0'.base64_encode(json_encode(array("result"=>0,"desc"=>"用户信息获取失败"))));
        }
        $user_info = $this->DAO->get_user_info($user_id);
        if($user_info['mobile']){
            $data = array(
                'mobile'=>substr_replace($user_info['mobile'],"******", 3, 6)
            );
            die('0'.base64_encode(json_encode(array("result"=>3,"desc"=>"成功",'data'=>$data))));
        }else{
            $mobile_info = $this->DAO->get_user_by_mobile($mobile);
            if($mobile_info){
                die('0'.base64_encode(json_encode(array("result"=>2,"desc"=>"该手机已绑定,请使用新手机号"))));
            }else{
                $nowtime=strtotime("now");
                if(isset($_SESSION['last_send_time'])){
                    if($nowtime-$_SESSION['last_send_time']<180){
                        die('0'.base64_encode(json_encode(array("result"=>0,"desc"=>"获取验证码太频繁，请稍后再试"))));
                    }else{
                        $_SESSION['last_send_time']=$nowtime;
                    }
                }else{
                    $_SESSION['last_send_time']=$nowtime;
                }
                $code = str_split($_SERVER["REQUEST_TIME"] .rand(1001, 9999));
                $code = substr(implode('', array_rand($code, 6)), 0, 6);
//                $send_result = $this->send_voice($mobile,$code); //语音验证码
//                $this->err_log(var_export($send_result,1),'voice');
                $send_result = $this->send_sms($mobile,array($code),"240478");//短信验证码
                $this->err_log(var_export($send_result,1),'H5sms');
                if($send_result){
                    $_SESSION['reg_core']=$user_id."_".$mobile."_".$code;
                    die('0'.base64_encode(json_encode(array("result"=>1,"desc"=>"验证码发送成功"))));
                }else{
                    die('0'.base64_encode(json_encode(array("result"=>0,"desc"=>"验证码发送失败，请稍后再试！"))));
                }
            }
        }
    }

    public function check_code(){
        $mobile = $this->rsa_decrypt_params(urldecode($_POST['mobile']));
        if(!$mobile){
            die('0'.base64_encode(json_encode(array("result"=>0,"desc"=>"手机号不能为空"))));
        }
        if(!$this->is_mobile((int)$mobile)){
            die('0'.base64_encode(json_encode(array("result"=>0,"desc"=>"手机号格式错误"))));
        }
        $mobile_info = $this->DAO->get_user_by_mobile($mobile);
        if($mobile_info){
//            if(!$mobile_info['hn_wx_id']){
//                die('0'.base64_encode(json_encode(array("result"=>0,"desc"=>"该手机号未绑定账号"))));
//            }
            $nowtime=strtotime("now");
            if(isset($_SESSION['code_last_send_time'])){
                if($nowtime-$_SESSION['code_last_send_time']<180){
                    die('0'.base64_encode(json_encode(array("result"=>0,"desc"=>"获取验证码太频繁，请稍后再试"))));
                }else{
                    $_SESSION['code_last_send_time']=$nowtime;
                }
            }else{
                $_SESSION['code_last_send_time']=$nowtime;
            }
            $code = str_split($_SERVER["REQUEST_TIME"] .rand(1001, 9999));
            $code = substr(implode('', array_rand($code, 6)), 0, 6);
//                $send_result = $this->send_voice($mobile,$code); //语音验证码
//                $this->err_log(var_export($send_result,1),'voice');
            $send_result = $this->send_sms($mobile,array($code),"240478");//短信验证码
            $this->err_log(var_export($send_result,1),'H5sms_check');
            if($send_result){
                $_SESSION['check_reg_core']=$mobile."_".$code;
                die('0'.base64_encode(json_encode(array("result"=>1,"desc"=>"验证码发送成功"))));
            }else{
//                $_SESSION['check_reg_core']=$mobile."_".$code;
//                die('0'.base64_encode(json_encode(array("result"=>0,"desc"=>"验证码发送失败，请稍后再试！".$_SESSION['check_reg_core']))));
                die('0'.base64_encode(json_encode(array("result"=>0,"desc"=>"验证码发送失败，请稍后再试！"))));
            }
        }else{
            die('0'.base64_encode(json_encode(array("result"=>0,"desc"=>"该手机号未注册过账号"))));


        }
    }

    public function reg_code(){
        $mobile = $this->rsa_decrypt_params(urldecode($_POST['mobile']));
        if(!$mobile){
            die('0'.base64_encode(json_encode(array("result"=>0,"desc"=>"手机号不能为空"))));
        }
        if(!$this->is_mobile((int)$mobile)){
            die('0'.base64_encode(json_encode(array("result"=>0,"desc"=>"手机号格式错误"))));
        }
        $mobile_info = $this->DAO->get_user_by_mobile($mobile);
        if(empty($mobile_info)){
            $nowtime=strtotime("now");
            if(isset($_SESSION['reg_last_send_time'])){
                if($nowtime-$_SESSION['reg_last_send_time']<180){
                    die('0'.base64_encode(json_encode(array("result"=>0,"desc"=>"获取验证码太频繁，请稍后再试"))));
                }else{
                    $_SESSION['reg_last_send_time']=$nowtime;
                }
            }else{
                $_SESSION['reg_last_send_time']=$nowtime;
            }
            $code = str_split($_SERVER["REQUEST_TIME"] .rand(1001, 9999));
            $code = substr(implode('', array_rand($code, 6)), 0, 6);
//                $send_result = $this->send_voice($mobile,$code); //语音验证码
//                $this->err_log(var_export($send_result,1),'voice');
            $send_result = $this->send_sms($mobile,array($code),"240478");//短信验证码
            $this->err_log(var_export($send_result,1),'H5sms_reg');
            if($send_result){
                $_SESSION['h5_reg_core']=$mobile."_".$code;
                die('0'.base64_encode(json_encode(array("result"=>1,"desc"=>"验证码发送成功"))));
            }else{
//                $_SESSION['h5_reg_core']=$mobile."_".$code;
//                die('0'.base64_encode(json_encode(array("result"=>0,"desc"=>"验证码发送失败，请稍后再试！".$code))));
                die('0'.base64_encode(json_encode(array("result"=>0,"desc"=>"验证码发送失败，请稍后再试！"))));
            }
        }else{
            die('0'.base64_encode(json_encode(array("result"=>0,"desc"=>"该手机号已注册过账号"))));


        }
    }


    public function rsa_decrypt_params($data){
        $PrivateKey = '-----BEGIN RSA PRIVATE KEY-----
MIICXgIBAAKBgQC4h7DNhpOBaHDrK51CEizEFGQ6sWhRFPdAj/k1BAvYPMpkqKRa
k1unHE3QOKDq5Fo4CJs7gbPNU+m13tudfU7oXs9lL26ZgDDvIJP9ogZdg4uH0Z3o
40QUtkE8eQ1PfA1I2zwxWWq/nqNmZZk4rfT8W+cxjqyt8YdkOv/ARXRuCwIDAQAB
AoGASfTIHXckQycyqm4udr6GBogNq6BSqLx4Y/3P6TmX7bBfhXw5crvAbfdgEGXB
yKKArhh07qKLB23sKyIIbtQ7/SNJaAjHzvWlJmaxKI/yauoS12gY9CIzgGhUgecV
HKlKQNndjgs1L5YKsAKmSNTXZnhEtUYnvv0bj5D1RXz6UkECQQDfZ5y9dB68F9V4
uWx/RhMSHATEhyOssYTHNlyP3yjFcNjGOU3UGIQJzGr7Lgwu1B4j2AvwFFTbRFz6
/LO5U2RbAkEA03QR83ie2kemFXxscHqsXtx7cJO8qf5QHZ8doRrvCalNP0SUpSkd
UQV4291FTNCkXgoQUyrObP/yXjy5XmuMEQJBAMxB330YfkFbSUJnxltXpngYRgOp
y2RJqiy590dTseNTmd8i5ZXWFGMhE280Ws82AZikH8YR0MPpbVnNUkVPiaECQQDT
F4+IIIVs4ZQi5PiYfU6w0KkGZOY2SmSOfbcliu7RFUvBemRuURIsPSs/SrERE0TT
gHZ1oEk9NXIus65WXUURAkEAzhojCIHmqf29XNfQPoFU94+1tBsix3DDuau/kF1x
Qb3BSxmkNsUhsBN8KSWqCPG64K1KxEoT8wVl/pu3MYCMkQ==
-----END RSA PRIVATE KEY-----';
//                $PrivateKey = '-----BEGIN RSA PRIVATE KEY-----
//MIICXQIBAAKBgQDAbfx4VggVVpcfCjzQ+nEiJ2DLnRg3e2QdDf/m/qMvtqXi4xhw
//vbpHfaX46CzQznU8l9NJtF28pTSZSKnE/791MJfVnucVcJcxRAEcpPprb8X3hfdx
//KEEYjOPAuVseewmO5cM+x7zi9FWbZ89uOp5sxjMnlVjDaIczKTRx+7vn2wIDAQAB
//AoGAZzUWajxKTZeJqh5FjBgmwZi5M7voFynZAjRWAkCkqZye0FfY7e70kA92C1AL
//aVqySnNr4WYZuGorEeOFGqHIv1XSowTLgfLkVBZ/SXiep2QYJrR0YevjysvLnTfb
//mrdWCqWSj+0AlQg+AvDA/qtvBVMxKymbpo+4bj5H2pPPZ1ECQQDi1PwJQJBYPbpL
//vGmP3AmWg467tCeQ+aJGgtQTOK5BH+p0BWFVDX583R437vllkKI8EXgZfqQfsQcj
//7XUAXyZVAkEA2SyFbO8roH9JLrEoxxKGeiGZvhPfNl9nXLhX0OFS0ywQaVBJno39
//9W5bX5iP5Jzeb3UWsZ/TxzhGc/b4WjAlbwJBAOFuIn1feRT5Y+hY++BJIg4/+N57
//EMd4ENpas0HXFvcKLQvZPP42Rvr5FksoaRuTPmjMQ7uyrJICccI3AAy6g3ECQQDE
//AyH9+zRmLNxRj0advsOvUcpgu7DYc21oS12/Qs+tl3TMiNGZkNDphwxjkOA217sP
//4B92fCn6AnncSslHJXNzAkBo6ujxqIfrZMOG3ON9nXxkWlq39GFS6CzXWscHA3Xz
//FMVT1WWU3FR2Kf2QSKiMGv02YcI2xfowim3JnT6600N0
//-----END RSA PRIVATE KEY-----';
        openssl_private_decrypt(base64_decode($data),$decrypted,$PrivateKey);
        return $decrypted;
    }

    public function bind_mobile(){
        $mobile = $this->rsa_decrypt_params(urldecode($_POST['mobile']));
        $code = $this->rsa_decrypt_params(urldecode($_POST['verifycode']));
        $password = $this->rsa_decrypt_params(urldecode($_POST['password']));
        $passwordAgain = $this->rsa_decrypt_params(urldecode($_POST['passwordAgain']));
        $user_id = $this->usr_params['user_id'];
        if(empty($password)){
            die('0'.base64_encode(json_encode(array("result"=>0,"desc"=>"密码不能为空"))));
        }
        if(empty($passwordAgain)){
            die('0'.base64_encode(json_encode(array("result"=>0,"desc"=>"二次密码不能为空"))));
        }
        if($passwordAgain != $password){
            die('0'.base64_encode(json_encode(array("result"=>0,"desc"=>"两次密码不一致"))));
        }
        if(!$error && strlen($password)<6 || strlen($password)>18){
            die('0'.base64_encode(json_encode(array("result"=>0,"desc"=>"密码长度6-18位之间"))));
        }
        if(empty($mobile)){
            die('0'.base64_encode(json_encode(array("result"=>0,"desc"=>"手机号不能为空"))));
        }
        if(!$this->is_mobile((int)$mobile)){
            die('0'.base64_encode(json_encode(array("result"=>0,"desc"=>"手机号格式错误"))));
        }
        if($_SESSION['reg_core']!=$user_id."_".$mobile."_".$code || empty($code)){
            die('0'.base64_encode(json_encode(array("result"=>0,"desc"=>"验证码错误"))));
        }
        if(strtotime("now")-$_SESSION['last_send_time']>300){
            die('0'.base64_encode(json_encode(array("result"=>0,"desc"=>"验证码超时"))));
        }
        if(!$user_id){
            die('0'.base64_encode(json_encode(array("result"=>0,"desc"=>"用户信息获取失败"))));
        }
        $user_info = $this->DAO->get_user_info($user_id);
        if($user_info['mobile']){
            $data = array(
                'mobile'=>substr_replace($user_info['mobile'],"******", 3, 6)
            );
            die('0'.base64_encode(json_encode(array("result"=>1,"desc"=>"成功",'data'=>$data))));
        }else{
            $mobile_info = $this->DAO->get_user_by_mobile($mobile);
            if($mobile_info){
                die('0'.base64_encode(json_encode(array("result"=>0,"desc"=>"该手机已绑定,请使用新手机号"))));
            }else{
                $this->DAO->update_user_molie($user_id,$mobile);
                $this->DAO->update_user_psw(md5($password),$mobile,$user_id,$this->create_guid());
                $data = array(
                    'mobile'=>substr_replace($mobile,"******", 3, 6)
                );
                die('0'.base64_encode(json_encode(array("result"=>1,"desc"=>"成功",'data'=>$data))));
            }
        }
    }


    public function cons_usr_header(){
        if (isset($_SERVER['HTTP_USER_AGENT1'])) {
//            $decrypted = $this->rsa_decrypt_params($_SERVER['HTTP_USER_AGENT1']);
//            if(empty($decrypted)){
//                die('0'.base64_encode(json_encode(array("result"=>0,"desc"=>"头部出错"))));
//            }
            $header = base64_decode(substr($_SERVER['HTTP_USER_AGENT1'], 1));
            $header = explode("&", $header);
            $params = array();
            foreach ($header as $k => $param) {

                $param = explode("=", $param);
                $params[$param[0]] = $param[1];
                if ($param[0] == 'channel') {
                    $params['channel'] = $param[1];
                }
                if ($param[0] == 'appId') {
                    $params['app_id'] = $param[1];
                }
                if ($param[0] == 'userId') {
                    $params['user_id'] = $param[1];
                }
                if ($param[0] == 'userName') {
                    $params['user_name'] = $param[1];
                }
                if ($param[0] == 'roleId') {
                    $params['role_id'] = $param[1];
                }
                if ($param[0] == 'roleName') {
                    $params['role_name'] = $param[1];
                }
                if ($param[0] == 'roleLevel') {
                    $params['role_level'] = $param[1];
                }
                if ($param[0] == 'serverId') {
                    $params['server_id'] = $param[1];
                }
                if ($param[0] == 'serverName') {
                    $params['server_name'] = $param[1];
                }
                if ($param[0] == 'system') {
                    $params['system'] = $param[1];
                }
                if ($param[0] == 'browser') {
                    $params['browser'] = $param[1];
                }
                if ($param[0] == 'lang') {
                    $params['lang'] = $param[1];
                }
                if ($param[0] == 'osVer') {
                    $params['os_ver'] = $param[1];
                }
                if ($param[0] == 'deviceType') {
                    $params['deviceType'] = $param[1];
                }
                if ($param[0] == 'timestamp') {
                    $params['timestamp'] = $param[1];
                }
                if ($param[0] == 'token') {
                    $params['token'] = $param[1];
                }
                if ($param[0] == 'safety') {
                    $params['safety'] = $param[1];
                }
                if ($param[0] == 'sdkVer') {
                    $params['sdk_ver'] = $param[1];
                }
                if ($param[0] == 'broswerVer') {
                    $params['broswer_ver'] = $param[1];
                }
            }
            $this->usr_params = $params;
        }
    }


    public function games_view($pid,$ch=''){
        $game_params = $this->DAO->get_user_pid($pid);
        if(empty($game_params)){
            die("无效的pid");
        }
        if(!$game_params['app_id'] ||!$game_params['channel']){
            die("-缺少必要参数-");
        }
        $game_id = $game_params['app_id'];
        $channel = $game_params['channel'];
        if(!empty($ch)){
            $channel = $channel.$ch;
        }
        //判断是否有存用户信息
        if(empty($_SESSION['h5_user_info']) || $_SESSION['h5_game_id'] != $game_id){
            $_SESSION['h5_game_id'] = $game_id;
            $_SESSION['h5_channel'] = $channel;
            //为登录页面
            $this->V->assign('appid',$game_id);
            $this->V->assign('channel',$channel);
            if($game_id == '1106'){
                $this->V->display('games3.html');
            }else{
                $this->V->display('games2.html');
            }
        }else{
            if(empty($_SESSION['h5_user_info']['user_id'])){
                unset($_SESSION['h5_user_info']);
                if($game_id == '1106'){
                    $this->V->display('games3.html');
                }else{
                    $this->V->display('games2.html');
                }
            }else{
                $this->V->assign('if_du',$_SESSION['h5_user_info']['if_du']);//开启debug
                $this->V->assign('user_id',$_SESSION['h5_user_info']['user_id']);
                $this->V->assign('sex',$_SESSION['h5_user_info']['sex']);
                $this->V->assign('user_name',$_SESSION['h5_user_info']['user_name']);
                $this->V->assign('img',$_SESSION['h5_user_info']['img']);
                $this->V->assign('appid',$_SESSION['h5_user_info']['appid']);
                $this->V->assign('game_info',$_SESSION['h5_user_info']['game_info']);
                $this->V->assign('m_verified',$_SESSION['h5_user_info']['m_verified']);//0 未验证  1 已验证
                $this->V->assign('mobile',$_SESSION['h5_user_info']['mobile']);
                $this->V->assign('registerTime',$_SESSION['h5_user_info']['registerTime']);
                $this->V->assign('game_url',$_SESSION['h5_user_info']['game_url']);
                $this->V->assign('token',$_SESSION['h5_user_info']['token']);
                $this->V->assign('channel',$_SESSION['h5_user_info']['channel']);
                $this->V->assign('family',$_GET['family']);
                $this->V->assign('cpext',$_GET['cpext']);
            }
            if($game_id == '1106'){
                $this->V->display('games3.html');
            }elseif( $game_id == '1001'){
                $this->V->display('games2_new.html');
            }else{
                $this->V->display('games2.html');
            }
        }

    }

    public function error_view(){
        $this->V->display('error_view.html');
    }

    public function h5_game_view(){
        $this->V->display('mykd_game.html');
    }

    public function nn_h5_game_view(){
        $this->V->display('merit_game.html');
    }

    public function pay(){
        $data = $_POST;
        $params = array();
        foreach($data as $name=>$item){
            $params[$name] = $this->rsa_decrypt_params(urldecode($item));
        }
        $this->wap_game_pay($params);
    }

    public function wap_game_pay($params){
        $money = $this->DAO->get_money($params['sdkgoodsid'],$params['appId']);
        $order_id = "WAP".$this->orderid($money['app_id']);
        $game = $this->DAO->get_game_info($money['app_id']);
        $isScan = 2;
        $time = strtotime("now");
        $ip = $this->client_ip();

        //获取厂商订单号
        $AppOrderId = $params['billno'];
        if(!$AppOrderId){
            $result = array(
                'result'=>0,
                'desc'=>"获取厂商订单号错误",
            );
            die('0'.base64_encode(json_encode($result)));
        }
        $ch = $params['way'];
        $params['web_channel'] = $params['channel'];
        //订单入库
        $order = $this->insert_wap_order($order_id, $AppOrderId, $params, $money, $time, $ip, $ch);
        if(in_array($params['userId'],$this->qa_user_id)){
            $order['pay_money'] = 0.01;
        }

        if(strpos($params['pageUrl'],'66173yx.com')){

            if($params['way'] == 2){

                if($params['isWX'] == 1){
//                $link = $this->go_bj_wx_pay($order, $params, $game);
                }else{
                    $link = $this->hn_wap_wx_pay($order, $params, $game);
                    $result['desc']='支付请求失败，请重新请求。';
                    if(empty($link)){
                        die('0'.base64_encode(json_encode($result)));
                    }
                    if($params['isPC'] == 1){
                        $isScan = 1;
//                    $result['id'] = $order_id;
                    }
                }
            }else{
                $link = $this->go_hn_wap_alipay($order, $params, $game);

            }
        }else{
            if($params['way'] == 2){

                if($params['isWX'] == 1){
//                $link = $this->go_bj_wx_pay($order, $params, $game);
                }else{
                    $link = $this->bj_wap_wx_pay($order, $params, $game);
                    $result['desc']='支付请求失败，请重新请求。';
                    if(empty($link)){
                        die('0'.base64_encode(json_encode($result)));
                    }
                    if($params['isPC'] == 1){
                        $isScan = 1;
//                    $result['id'] = $order_id;
                    }
                }
            }else{
                $link = $this->go_bj_wap_alipay($order, $params, $game);
            }
        }

        $result['result'] = 1;
        $result['desc'] = '请求成功';
        $result['isScan'] = $isScan;
        $result['link'] = $link;
        die('0'.base64_encode(json_encode($result)));
    }

    public function hn_wap_wx_pay($order, $params, $game){
        $data = array(
            'funcode' => 'WP001',//定值
            'version' => '1.0.0',//定值
            'mhtOrderNo' => $order['order_id'],
            'mhtOrderName' => $order['title'],
            'mhtOrderType' => '01',//定值（普通消费）
            'mhtCurrencyType' => '156',//定值 156人民币
            'mhtOrderAmt' => $order['pay_money']*100,//金额 单位(人民币):分; 整数,无小数点
            'mhtOrderDetail' => $game['app_name'].'-'.$order['title'],
            'mhtOrderStartTime' => date("YmdHis", time()),
            'notifyUrl' => XZ_HN_WX_WAP_notify_url,
            'mhtCharset'=>'UTF-8',
            'payChannelType'=>'13',
            'mhtLimitPay'=>'0',
            'mhtSignType'=>'MD5'
        );
        if($params['isPC'] == 1){
            $wx_app_id = HN_ICAN_APPID;
            $wx_app_key = HN_ICAN_APPKEY;
            $data['appId'] = $wx_app_id;
            $data['deviceType'] = '08';
            $data['outputType'] = '1';
        }else{
            $wx_app_id = XZ_HN_WX_WAP_APPID;
            $wx_app_key = XZ_HN_WX_WAP_KEY;
            $data['appId'] = $wx_app_id;
            $data['deviceType'] = '0601';
            $data['outputType'] = '2';
            $data['frontNotifyUrl'] = XZ_HN_WX_WAP_front_notify_url;
        }
        ksort($data);
        $data_str = "";
        foreach($data as $key => $param){
            if($param !=''){
                $data_str = $data_str."&".$key."=".$param;
            }
        }
        $data_str = substr($data_str, 1);
        $sgin = md5($data_str."&".md5($wx_app_key));
        $data['mhtSignature'] = $sgin;
        $result = $this->request('https://pay.ipaynow.cn',$data);
        if(!$result){
            return "";
        }
        $result_array = explode("&",$result);
        $params = array();
        foreach ($result_array as $key => $item){
            $item_array = array();
            $item_array = explode("=",$item);
            if(!empty($item_array)){
                $params[$item_array[0]] = urldecode($item_array[1]);
            }
        }
        if($params['tn']){
            return $params['tn'];
        }else{
            return "";
        }
    }

    public function bj_wap_wx_pay($order, $params, $game){
        $trade_type = 'MWEB';
        if($params['isPC'] == 1){
            $trade_type = 'NATIVE';
        }
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
        $new_data = $this->make_wx($data,BJ_WX_APPKEY);
        $xml_data = $this->array_to_xml($new_data);
        $request = $this->request(WX_API_url,$xml_data,array('Content-type: text/xml'));
        $request_data = json_decode(json_encode(simplexml_load_string($request, 'SimpleXMLElement', LIBXML_NOCDATA)), true);
        $this->err_log(var_export($request_data,1),'bj_wx_pay_log');
        if($request_data['return_code']=='SUCCESS' && $request_data['result_code']=='SUCCESS'){
            if($trade_type == 'MWEB'){
                return $request_data['mweb_url'];
            }elseif($trade_type == 'NATIVE'){
                return $request_data['code_url'];
            }else{
                return "";
            }
        }else{
            return "";
        }
    }

    public function make_wx($data,$wx_key){
        ksort($data);
        $str = '';
        $new_data=array();
        foreach($data as $key => $val ){
            if(!empty($val)){
                $new_data[$key]=$val;
                $str.=$key."=".$val."&";
            }
        }
        $str = $str."key=".$wx_key;
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

    public function create_guids() {
        $charid = strtolower(md5(uniqid(mt_rand(), true)));
        $uuid = substr($charid, 0, 32);
        return $uuid;
    }

    //平台订单创建
    public function insert_wap_order($order_id, $app_order_id, $params, $money, $time, $ip, $ch=1){
        $order['app_id']      = $params['appId'];
        $order['order_id']       = $order_id;
        $order['app_order_id']  = $app_order_id;
        $order['pay_channel']   = $ch;
        $order['buyer_id']    = $params['userId'];
        $order['role_id']     = $params['roleId'];
        $order['product_id']  = $money['id'];
        $order['unit_price']  = $money['good_price'];
        $order['title']       = $money['good_name'];
        $order['role_name']   = $params['roleName'];
        $order['amount']      = $money['good_amount'];
        $order['pay_money']   = $money['good_price'];
        $order['status']      = 0;
        $order['buy_time']    = $time;
        $order['ip']     = $this->client_ip();
        $order['serv_id']   = $params['serverId'];
        $order['channel']   = $params['channel']?$pay['channel']:'nnwl';
        $order['payExpandData'] = $params['extraInfo']?$pay['extraInfo']:'';
        $order['pay_from'] = 6;//h5游戏
        $order['mac'] = $params['mac']?$params['mac']:'';
        $order['idfa'] = $params['idfa']?$params['idfa']:'';
        $order['idfv'] = $params['idfv']?$params['idfv']:'';
        $order['web_channel'] = $params['web_channel']?$params['web_channel']:'';
        if(!$this->DAO->create_order($order)){
            die("订单号创建失败");
        }
        return $order;
    }

    public function go_bj_wap_alipay($order, $pay, $game){
        $call_back_url = $pay['pageUrl'];
        $merchant_url = $pay['pageUrl'];
        $notify_url = 'http://charge.66173.cn/bj_gamesite_notify.php';

        $pms1 = array (
            "req_data" => "<direct_trade_create_req>".
                "<subject>".$game['app_name'].$order['subject']."</subject>".
                "<out_trade_no>".$order['order_id']."</out_trade_no>".
                "<total_fee>". $order['pay_money']."</total_fee>".
                "<seller_account_name>".BJ_ALI_MOBILE_seller_email."</seller_account_name>".
                "<notify_url>".$notify_url."</notify_url>".
                "<out_user></out_user>".
                "<merchant_url>".$merchant_url."</merchant_url>".
                "<call_back_url>".$call_back_url."</call_back_url>".
                "</direct_trade_create_req>",
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

        // 构造请求函数
        $alipay = new alipay_service();
        $token = $alipay->alipay_wap_trade_create_direct($pms1, BJ_ALI_MOBILE_key, ALI_MOBILE_sec_id );
        $pms2 = array (
            "req_data"  => "<auth_and_execute_req><request_token>".$token."</request_token></auth_and_execute_req>",
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
        return $alipay->alipay_Wap_Auth_AuthAndExecute2($pms2, BJ_ALI_MOBILE_key);
    }

    public function go_hn_wap_alipay($order, $pay, $game){
        $url = explode("&",$pay['pageUrl']);
        $call_back_url = $url['0'];
        $merchant_url = $url['0'];
        $notify_url = 'http://charge.66173.cn/hn_gamesite_notify.php';

        $pms1 = array (
            "req_data" => "<direct_trade_create_req>".
                "<subject>".$game['app_name'].$order['subject']."</subject>".
                "<out_trade_no>".$order['order_id']."</out_trade_no>".
                "<total_fee>". $order['pay_money']."</total_fee>".
                "<seller_account_name>".ALI_MOBILE_seller_email."</seller_account_name>".
                "<notify_url>".$notify_url."</notify_url>".
                "<out_user></out_user>".
                "<merchant_url>".$merchant_url."</merchant_url>".
                "<call_back_url>".$call_back_url."</call_back_url>".
                "</direct_trade_create_req>",
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

        // 构造请求函数
        $alipay = new alipay_service();
        $token = $alipay->alipay_wap_trade_create_direct($pms1, ALI_MOBILE_key, ALI_MOBILE_sec_id );
        $pms2 = array (
            "req_data"  => "<auth_and_execute_req><request_token>".$token."</request_token></auth_and_execute_req>",
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
        return $alipay->alipay_Wap_Auth_AuthAndExecute2($pms2, ALI_MOBILE_key);
    }


    public function get_auth_user_token($game_id){
        $game_info = $this->DAO->get_user_pid($game_id);
        if(!$game_info){
            die('无效的游戏ID');
        }
        if (!isset($_SESSION['weixin_code'])) {
            $this->do_weixin_login($game_id,$_GET);
        }
        $app_info = $this->DAO->get_game_info($game_info['app_id']);

        if($app_info['payee_ch']=='3' || $game_info['app_id']=='1001'){
            $wx_appid = 'wx145b6dcbc2f651b5';//北京
            $wx_appsecret = 'c9a57dc61750b745859ca4bd6393b306';
        }elseif($app_info['payee_ch']=='1'){
            $wx_appid = 'wxbaed68c7f2f3a62c';//福建
            $wx_appsecret = '8bae09f8cf665a6b1267c0ccbd77cd61';
        }elseif($app_info['payee_ch']=='2'){
            $wx_appid = 'wxf965c4a0c7bf9851';//海南
            $wx_appsecret = '370761a5cdf594f23f69def4d9fdc3cf';
        }else{
            die('未知的类型');
        }
        $usr_token = $this->request("https://api.weixin.qq.com/sns/oauth2/access_token?appid=" . $wx_appid . "&secret=" . $wx_appsecret . "&code=" . $_SESSION['weixin_code'] . "&grant_type=authorization_code");
        $usr_token = json_decode($usr_token, true);
//        var_dump("https://api.weixin.qq.com/sns/oauth2/access_token?appid=" . $wx_appid . "&secret=" . $wx_appsecret . "&code=" . $_SESSION['weixin_code'] . "&grant_type=authorization_code");
//        var_dump($usr_token);
//        exit();
        if (isset($usr_token['openid']) && isset($usr_token['access_token'])) {
            $_SESSION['wx_openid'] = $usr_token['openid'];
            $_SESSION['wx_token'] = $usr_token['access_token'];
            $_SESSION['wx_token_time'] = strtotime("now");
            $_SESSION['wx_token_expires_in'] = $usr_token['expires_in'];
        } else {
            if($game_info['app_id']=='1001'){
                $this->do_weixin_login($game_id,$_GET);
            }
            die("发生错误，请重新进入。WX_ERR002");
        }
    }

    public function do_weixin_login($game_id,$params){
        header('HTTP/1.1 301 Moved Permanently');
        $game_info = $this->DAO->get_user_pid($game_id);
        if(!$game_info){
            die('无效的游戏ID');
        }
        $app_info = $this->DAO->get_game_info($game_info['app_id']);
        $wx_appid='wxf965c4a0c7bf9851';//海南
        if($app_info['payee_ch']=='3' || $game_info['app_id']=='1001'){
            $wx_appid='wx145b6dcbc2f651b5';//北京
        }elseif($app_info['payee_ch']=='1'){
            $wx_appid='wxbaed68c7f2f3a62c';//福建
        }
        $url='';
        if(strpos($_SERVER['HTTP_HOST'],'66173.cn')){
            $url ='http://wx.66173.cn';
        }elseif (strpos($_SERVER['HTTP_HOST'],'66173yx.com')){
            $url ='http://wx.66173yx.com';
        }elseif (strpos($_SERVER['HTTP_HOST'],'yun273.com')){
            $url ='http://wx.yun273.com';
        }
        if(empty($url)){
            die('异常的游戏地址');
        }
        // 微信浏览器，允许访问
        $family = $params['family'];
        $cpext = $params['cpext'];
        $url = $url."/h5_game.php?game_id=".$game_id;
        if($ch){
            $url = $url."&ch=".$ch;
        }
        if($family){
            $url = $url."&family=".$family;
        }
        if($cpext){
            $url = $url."&cpext=".$cpext;
        }
//        $url = "https://open.weixin.qq.com/connect/oauth2/authorize?appid=wx145b6dcbc2f651b5&redirect_uri=".urlencode($url)."&response_type=code&scope=snsapi_base&state=STATE#wechat_redirect";
        $url = "https://open.weixin.qq.com/connect/oauth2/authorize?appid=".$wx_appid."&redirect_uri=".urlencode($url)."&response_type=code&scope=snsapi_base&state=STATE#wechat_redirect";
        header('Location: '.$url);
    }
}
?>
