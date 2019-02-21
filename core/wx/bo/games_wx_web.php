<?php
COMMON('weixinCore', 'pageCore');
BO("account_wx");
DAO('games_dao','weixin_dao');
class games_wx_web extends weixinCore{

    protected $DAO;

    public function __construct(){
        parent::__construct();
        $this->DAO = new games_dao();
//        if(strpos($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger') !== false ) {
//            if(isset($_GET['code'])){
//                $_SESSION['weixin_code'] = $_GET['code'];
//                if(!$_SESSION['wx_openid']){
//                    $this->get_auth_usr_token();
//                }
//                if(!$_SESSION['usr_info']['user_id']){
//                    $account = new account_wx();
//                    if(WX_COMPANY=='HM'){
//                        $_SESSION['usr_info'] = $this->DAO->get_hm_wx_usr_info($_SESSION['wx_openid']);
//                    }else{
//                        $_SESSION['usr_info'] = $this->DAO->get_wx_usr_info($_SESSION['wx_openid']);
//                    }
//                    $account->build_wx_user_info();
//                }
//            }
//            $this->assign("is_weixin","1");
//        }
//        $this->cons_usr_header();
    }

    public function wx_program_pay(){
        $this->err_log(var_export($_POST,1),'yyq_wx_pay');
        $result = array('msg'=>'网络请求一次','error'=>'1');
        $params = $_POST;
        if(empty($params)){
            $result['msg'] = '缺少必要参数';
            die(json_encode($result));
        }
        $app_info = $this->DAO->get_wx_app_info($params['app_id']);
        if($params['md5'] !== md5($params['app_id'].$params['player_id'].$params['serv_id'].$params['usr_id'].$params['usertoken'].$app_info['app_key'])){
            $result = array('msg'=>'参数不正确','error'=>'1');
        }else{
            $time = strtotime("now");
            $money = $this->DAO->get_money_info($params['app_id'],$params['money_id']);
            $money['unit_price'] = $money['good_price'];
            $SYS_order_id = $this->orderid($params['app_id']);
            $app_order_id = $params['cp_order_id'];
            $order_info = $this->create_order($money, $params, $SYS_order_id, $app_order_id, $time,2);
            $result = $this->get_wx_result($order_info,$money,$params,$app_info,$SYS_order_id,$app_order_id);
        }
        die(json_encode($result));
    }

    public function get_wx_result($order_info,$money,$USR_HEADER,$app_info,$SYS_order_id,$app_order_id){
        $openid = $this->get_user_openid($USR_HEADER['usr_id'],$app_info['wx_app_id']);
        $wx_data = $this->make_wx_data($app_info,$money,$order_info['order_id'],WX_SECURE_notify_url,$openid);
        $xml_data = $this->array_to_xml($wx_data);
        $request = $this->request(WX_API_url,$xml_data);
        $request_data = json_decode(json_encode(simplexml_load_string($request, 'SimpleXMLElement', LIBXML_NOCDATA)), true);
        if($request_data['return_code']=='FAIL'){
            $result = array('msg'=>$request_data['return_msg'],'error'=>'1');
            die(json_encode($result));
        }elseif($request_data['return_code']=='SUCCESS' && $request_data['result_code']=='SUCCESS'){
            $prepay_id = $request_data['prepay_id'];
        }elseif($request_data['return_code']=='SUCCESS' && $request_data['result_code']=='FAIL'){
            $result = array('msg'=>$request_data['err_code_des'].$request_data['err_code'],'error'=>'1');
            die(json_encode($result));
        }else{
            $result = array('msg'=>'请求订单异常.'.$request_data,'error'=>'1');
            die(json_encode($result));
        }
        $time = time();
        $nonceStr = $this->create_guids();
        $package = 'prepay_id='.$prepay_id;
        $pay_sign = "appId=".$app_info['wx_app_id']."&nonceStr=".$nonceStr."&package=".$package."&signType=MD5&timeStamp=".$time."&key=".WX_APP_KEY;
        $pay_sign = strtoupper(md5($pay_sign));
        $result = array(
            "error"=>0,
            "timeStamp"=>$time,
            "nonceStr"=>$nonceStr,
            "package"=>$package,
            "paySign"=>$pay_sign,
            "msg"=>"预支付订单发送成功");
        $this->err_log(var_export($request_data,1),'xcx_wx_pay_order');
        return $result;
    }


    public function get_user_openid($user_id,$wx_app_id){
       $user_info = $this->DAO->get_user_unionid($user_id);
       if(empty($user_info['unionid'])){
           $result = array('msg'=>'用户信息异常','error'=>'1');
           die(json_encode($result));
       }
       $wx_user_info = $this->DAO->get_wx_user_openid($user_info['unionid'],$wx_app_id);
       if(empty($wx_user_info['open_id'])){
           $result = array('msg'=>'用户未关注，无法支付','error'=>'1');
           die(json_encode($result));
       }
       return $wx_user_info['open_id'];
    }

    public function make_wx_data($app_info,$money,$out_trade_no,$notify_url,$openid){
        $data = array(
            'appid' => $app_info['wx_app_id'],
            'mch_id' => $app_info['mch_id'],
            'nonce_str' => $this->create_guids(),
            'body' => $money['good_name'],
            'out_trade_no' => $out_trade_no,
            'total_fee' => $money['good_price']*100,
            'spbill_create_ip' => $this->client_ip(),
            'notify_url' => $notify_url,
            'trade_type' => 'JSAPI',
            'openid' => $openid
        );
        ksort($data);
        $str = '';
        $new_data=array();
        foreach($data as $key => $val ){
            if(!empty($val)){
                $new_data[$key]=$val;
                $str.=$key."=".$val."&";
            }
        }
        $str = $str."key=".WX_APP_KEY;
        $new_data['sign']=strtoupper(md5($str));
        return $new_data;
    }

    public function create_guids() {
        $charid = strtolower(md5(uniqid(mt_rand(), true)));
        $uuid = substr($charid, 0, 32);
        return $uuid;
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

    protected function create_order($money, $header, $YD_order_id, $app_order_id, $time,$pay_channel=1){
        $order['app_id']        = $header['app_id'];
        $order['order_id']       = $YD_order_id;
        $order['app_order_id']  = $app_order_id;
        $order['pay_channel']   = $pay_channel;
        $order['buyer_id']    = $header['usr_id'];
        $order['role_id']     = $header['player_id'];
        $order['product_id']  = $money['id'];
        $order['unit_price']  = $money['unit_price'];
        $order['title']       = $money['good_name'];
        $order['role_name']   = $header['player_name'];
        $order['amount']      = $money['good_amount'];
        $order['pay_money']   = $money['good_price'];
        $order['discount']    = isset($money['discount'])?$money['discount']:10;
        $order['pay_price']   = isset($money['pay_price'])?$money['pay_price']:$money['good_price'];
        $order['status']      = 0;
        $order['buy_time']    = $time;
        $order['ip']     = $this->client_ip();
        $order['serv_id']   = $header['serv_id'];
        $order['channel']   =  isset($header['channel'])?$header['channel']:'';
        $order['payExpandData'] = isset($header['payexpanddata'])?$header['payexpanddata']:'';
        $order['serv_name']   =  isset($header['serv_name'])?$header['serv_name']:'';
        if(!$this->DAO->create_order($order)){
            $result = array('msg'=>'订单创建失败','error'=>'1');
            die(json_encode($result));
        }
        return $order;
    }

    public function wx_program_login(){
        $result = array('msg'=>'网络请求一次','error'=>'1');
        $params = $_POST;
        if(!$params['pid']){
            $result['msg'] = '缺少pid';
            die(json_encode($result));
        }
        if(!$params['code']){
            $result['msg'] = '缺少必要参数code';
            die(json_encode($result));
        }
        $wx_info = $this->DAO->get_wx_app_info($params['pid']);
        if(empty($wx_info)){
            $result['msg'] = '无效的pid';
            die(json_encode($result));
        }
        $post_data = array();
        $post_data['appid'] = $wx_info['wx_app_id'];
        $post_data['secret'] = $wx_info['wx_app_secret'];
        $post_data['js_code'] = $params['code'];
        $post_data['grant_type'] = 'authorization_code';

        $url = 'https://api.weixin.qq.com/sns/jscode2session?appid=APPID&secret=SECRET&js_code=JSCODE&grant_type=authorization_code';
        $wx_result = $this->request($url,$post_data);
        $this->err_log(var_export($wx_result,1),'wx_pro_result');
        $wx_result = json_decode($wx_result);
        if($params['game_type'] == 2 && $wx_result->openid){
            $user_info = $this->get_wx_user_hm_openid($wx_result->openid,$params['pid']);
            if(!$user_info['user_id']){
                $result['msg'] = '用户查询失败';
                die(json_encode($result));
            }
            $result = array('msg'=>'请求成功','error'=>'0','token'=>$user_info['token'],'user_id'=>$user_info['user_id']);
            die(json_encode($result));
        }elseif($wx_result->unionid){
            $wx_user_openid = $this->DAO->get_wx_user_openid($wx_result->unionid,$wx_info['wx_app_id']);
            if(empty($wx_user_openid)){
                $this->DAO->add_wx_user_log($wx_result->session_key,$wx_result->openid,$wx_result->unionid,$post_data);
            }
            $user_info = $this->get_wx_user_info($wx_result->unionid,$params['pid']);
            if(!$user_info['user_id']){
                $result['msg'] = '用户查询失败';
                die(json_encode($result));
            }
            $result = array('msg'=>'请求成功','error'=>'0','token'=>$user_info['token'],'user_id'=>$user_info['user_id']);
            die(json_encode($result));
//        }elseif($wx_result->session_key){
//            $encryptedData = $params['data'];
//            $iv = $params['iv'];
//            $app_id = $wx_info['wx_app_id'];
//            $sessionKey = $wx_result->session_key;
//
//            $result_data = $this->decryptData($encryptedData,$iv,$app_id,$sessionKey);
//            if($result_data['unionid']){
//                $user_info = $this->get_wx_user_info($result_data['unionid'],$params['pid']);
//                if($user_info['user_id']){
//                    $result = array('msg'=>'请求成功','error'=>'0','token'=>$user_info['token'],'user_id'=>$user_info['user_id']);
//                }else{
//                    $result['msg'] = '用户数据查询失败';
//                }
//            }else{
//                $result['msg'] = '用户数据获取失败';
//            }
//            die(json_encode($result));
        }else{
            $result['msg'] = '用户数据获取失败111';
            die(json_encode($result));
        }
        die(json_encode($result));
    }


    public function get_wx_user_hm_openid($open_id,$app_id){
        $user_info = $this->DAO->get_hm_wx_usr_info($open_id);
        if(empty($user_info)){
            $user_info = $this->register_hn_openid_user($open_id,$app_id);
        }
        return $user_info;
    }

    public function get_wx_user_info($unionid,$app_id){
        $user_info = $this->DAO->get_wx_unionid_info($unionid);
        if(empty($user_info)){
            $user_info = $this->register_unionid_user($unionid,$app_id);
        }
        return $user_info;
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
        $this->check_login($game_id,$channel);
        if(!$_SESSION['wx_openid'] || !$_SESSION['usr_info']['user_id']){
            die("游戏参数异常，请重新登录1");
        }
        $user_id = $_SESSION['usr_info']['user_id'];
        $token = $this->create_guid();
        $this->DAO->update_usr_token($user_id,$token);
        $info = $this->DAO->set_user_info($user_id);
        header('HTTP/1.1 301 Moved Permanently');
        if(WX_COMPANY=='HM'){
            header('Location: http://ins.66173yx.com/games.php?game_id='.$game_id.'&u_id='.$_SESSION['usr_info']['user_id'].'&channel='.$channel.'&token='.$token.'&i='.$pid.'&c='.$ch.'&t='.$_GET['t']);
        }else{
            header('Location: http://ins.66173.cn/games.php?game_id='.$game_id.'&u_id='.$_SESSION['usr_info']['user_id'].'&channel='.$channel.'&token='.$token.'&i='.$pid.'&c='.$ch.'&t='.$_GET['t']);
        }
    }

    public function check_login($game_id,$channel){
        if(!$_SESSION['wx_openid']){
            $this->get_auth_usr_token();
        }
        if(WX_COMPANY=='HM'){
            $_SESSION['usr_info'] = $this->DAO->get_hm_wx_usr_info($_SESSION['wx_openid']);
        }else{
            $_SESSION['usr_info'] = $this->DAO->get_wx_usr_info($_SESSION['wx_openid']);
        }
        $this->build_wx_user_info();
        if(empty($_SESSION['usr_info'])){
            $this->user_register($game_id,$channel);
        }
    }

    public function user_register($game_id,$channel){
        if($_SESSION['wx_usr_info']['nickname']){
            $nick_name = $_SESSION['wx_usr_info']['nickname'];
        }else{
            $nick_name = '微信用户'.time();
        }
        $user_info= array(
            'nick_name'=>$nick_name,
            'wx_id'=>$_SESSION['wx_openid'],
            'reg_ip'=>$this->client_ip(),
            'reg_from'=>11,//微信H5
            'user_type'=>1,//普通用户
            'm_verified'=>0,
            'login_type'=>0,//guest账号
            'from_app_id'=>$game_id,
            'channel'=>$channel,
            'token'=>$this->create_guid(),
        );
        if(WX_COMPANY=='HM'){
            $id = $this->DAO->insert_hn_user_info($user_info);
        }else{
            $id = $this->DAO->insert_user_info($user_info);
        }
        $_SESSION['usr_info']['user_id'] = $id;
    }

    public function register_unionid_user($unionid,$game_id){
        if(empty($nick_name)){
            $nick_name = 'WeChat'.time();
        }
        $user_info= array(
            'nick_name'=>$nick_name,
            'unionid'=>$unionid,
            'reg_ip'=>$this->client_ip(),
            'reg_from'=>12,//小程序用户
            'user_type'=>1,//普通用户
            'm_verified'=>0,
            'login_type'=>0,//guest账号
            'from_app_id'=>$game_id,
            'token'=>$this->create_guid(),
        );
        $id = $this->DAO->insert_unionid_user_info($user_info);
        $user_info['user_id'] = $id;
        return $user_info;
    }

    public function register_hn_openid_user($open_id,$game_id){
        if(empty($nick_name)){
            $nick_name = 'WeChat'.time();
        }
        $user_info= array(
        'nick_name'=>$nick_name,
        'wx_id'=>$open_id,
        'reg_ip'=>$this->client_ip(),
        'reg_from'=>13,//小游戏用户
        'user_type'=>1,//普通用户
        'm_verified'=>0,
        'login_type'=>0,//guest账号
        'from_app_id'=>$game_id,
        'channel'=>'66173',
        'token'=>$this->create_guid(),
        );
        $id = $this->DAO->insert_hn_user_info($user_info);
        $user_info['user_id'] = $id;
        return $user_info;
    }


    public function cons_usr_header(){
        if (isset($_SERVER['HTTP_USER_AGENT1'])) {
            $private_key='-----BEGIN RSA PRIVATE KEY-----
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
            openssl_private_decrypt(base64_decode($_SERVER['HTTP_USER_AGENT1']),$decrypted,$private_key);
            $header = base64_decode(substr($decrypted, 1));
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
                    $params['osVer'] = $param[1];
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
            }
            $this->usr_params = $params;
        }
    }

    /**
     * 检验数据的真实性，并且获取解密后的明文.
     * @param $encryptedData string 加密的用户数据
     * @param $iv string 与用户数据一同返回的初始向量
     * @param $data string 解密后的原文
     *
     * @return int 成功0，失败返回对应的错误码
     */
    public function decryptData($encryptedData,$iv,$app_id,$sessionKey,&$data){
        $result = array('msg'=>'网络请求一次','error'=>'1');
        if(strlen($sessionKey) != 24) {
            $result['msg'] = 'sessionKey错误';
            die(json_encode($result));
        }
        $aesKey=base64_decode($sessionKey);
        if(strlen($iv) != 24) {
            $result['msg'] = 'iv错误';
            die(json_encode($result));
        }
        $aesIV=base64_decode($iv);
        $aesCipher=base64_decode($encryptedData);
        $wx_result=openssl_decrypt($aesCipher, "AES-128-CBC", $aesKey, 1, $aesIV);
        $dataObj=json_decode($wx_result);
        if($dataObj == NULL) {
            $result['msg'] = '查询错误';
            die(json_encode($result));
        }
        if($dataObj->watermark->appid != $app_id){
            $result['msg'] = 'iv错误';
            die(json_encode($result));
        }
        $data = $wx_result;
        return $data;
    }



}