<?php
COMMON('weixinCore', 'pageCore');
BO("account_wx");
DAO('warrant_dao','weixin_dao');
class warrant_wx extends weixinCore{
    protected $DAO;

    public function __construct(){
        parent::__construct();
        $this->DAO = new warrant_dao();
        if(strpos($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger') !== false ) {
            if(isset($_GET['code'])){
                $_SESSION['weixin_code'] = $_GET['code'];
                if(!$_SESSION['wx_openid']){
                    $this->get_auth_usr_token();
                }
                if(!$_SESSION['usr_info']['user_id']){
                    $account = new account_wx();
                    if(WX_COMPANY=='HM'){
                        $_SESSION['usr_info'] = $this->DAO->get_hm_wx_usr_info($_SESSION['wx_openid']);
                    }else{
                        $_SESSION['usr_info'] = $this->DAO->get_wx_usr_info($_SESSION['wx_openid']);
                    }
                    $account->build_wx_user_info();
                }
            }
            $this->assign("is_weixin","1");
        }
        $this->cons_usr_header();
    }

    public function ylc($app_id,$channel){
        $game_params = $this->DAO->get_app_info($app_id);
        if(empty($game_params)){
            die("查无此游戏");
        }
        $game_id = $game_params['app_id'];
        $this->check_login($game_id,$channel);
        if(!$_SESSION['wx_openid'] || !$_SESSION['usr_info']['user_id']){
            die("参数异常，请重新登录");
        }
        $user_id = $_SESSION['usr_info']['user_id'];
        $token = $this->create_guid();
        $this->DAO->update_usr_token($user_id,$token);
        $sign = md5($_SESSION['usr_info']['user_id'].'_'.$game_params['app_id'].'_'.$game_params['app_key']);
        header('HTTP/1.1 301 Moved Permanently');
        header('Location: http://wx.66173yx.com/integral.php?act=index&app_id='.$game_id.'&channel='.$channel);
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
        if(!$channel){
            $channel = '66173';
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
            'token'=>$this->create_guid(),
            'channel'=>$channel,
            'chr'=>chr(rand(97,122)).chr(rand(97,122))
        );
        if(WX_COMPANY=='HM'){
            $id = $this->DAO->insert_hn_user_info($user_info);
        }else{
            $id = $this->DAO->h5_insert_user_info($user_info);
        }
        $_SESSION['usr_info']['user_id'] = $id;
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


}