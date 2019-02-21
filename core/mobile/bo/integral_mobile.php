<?php
COMMON('baseCore', 'pageCore','weixinCore');
DAO('integral_dao');

class integral_mobile extends baseCore{
    public $DAO;
    public $R_DAO;
    public $usr_params;

    public function __construct(){
        parent::__construct();
        $this->DAO = new integral_dao();
        $this->R_DAO = new reserve_dao();
        $this->cons_usr_header();
    }

    public function index_view($app_id,$channel,$u_id,$sex,$headimgurl,$sign){
        $user_id = $_SESSION['usr_info']['user_id'] = $u_id;
        $_SESSION['wx_usr_info']['sex'] = $sex;
        $_SESSION['wx_usr_info']['headimgurl'] = $headimgurl;
        $user_info = $this->DAO->get_user_info($user_id);
        $app_info = $this->DAO->get_app_info($app_id);
        $user_sign = md5($u_id.'_'.$app_id.'_'.$app_info['app_key']);
        if($sign){
            if($sign != $user_sign){
                die("链接出错啦");
            }
        }
        if(!$app_info['app_id']){
            die("链接出错啦");
        }
        if(!$app_info['version_url']){
            die("游戏已下架。");
        }
        //查询是否有记录。
        $user_apptb = $this->DAO->get_user_apptb($app_id,$user_id);
        $ip = $this->client_ip();
        if(empty($user_apptb)){
            $this->DAO->add_user_apptb($user_id,$app_id,$user_info['token'],$ip,$channel);
            //sdk登录日志
        }else{
            $this->DAO->update_user_apptb($user_apptb,$user_info['token'],$ip,$channel);
            //sdk登录日志
        }
        $mobile = '';
        if($user_info['mobile']){
            $mobile = substr_replace($user_info['mobile'],"******", 3, 6);
        }
        $goods_list = $this->DAO->get_goods_list($app_info['relation_id']);
        if($goods_list){
            foreach($goods_list as $key=>$data){
                $goods_list[$key]['consume'] = $app_info['nnb_scale']*$data['good_price'];
            }
        }
        if(!$_SESSION['usr_info']['user_id']){
            $app_info['version_url'] = '';
        }
        $this->wx_share();
        $this->V->assign('if_du',0);//开启debug
        $this->V->assign('user_id',$user_id);
        $this->V->assign('sex',$_SESSION['wx_usr_info']['sex']);
        $this->V->assign('img',$_SESSION['wx_usr_info']['headimgurl']);
        $this->V->assign('game_info',$app_info);
        $this->V->assign('mobile',$mobile);
        $this->V->assign('channel',$channel);
        $this->V->assign('token',$user_info['token']);
        $this->V->assign("user_info",$user_info);
        $this->V->assign("goods_list",$goods_list);
        $this->V->assign("app_id",$app_id);
        $this->V->display("website/ylc.html");
    }

    public function sms_code(){
        $result = array('result' => 0 ,'desc'=>'网络请求出错');
        $phone = $this->rsa_decrypt_params(urldecode($_POST['mobile']));
        $nowtime = strtotime("now");
        if(!$phone){
            $result['desc'] = "请输入您的手机号";
            die("0".base64_encode(json_encode($result)));
        }
        if(!$this->is_mobile($phone)){
            $result['desc'] = "手机号码不正确,请重试";
            die("0".base64_encode(json_encode($result)));
        }
        if(isset($_SESSION['last_send_time'])){
            if($nowtime-$_SESSION['last_send_time']<120){
                $result['desc'] = "获取验证码太频繁，请稍后再试";
                die("0".base64_encode(json_encode($result)));
            }else{
                $_SESSION['last_send_time'] = $nowtime;
            }
        }else{
            $_SESSION['last_send_time'] = $nowtime;
        }
        $code = str_split($_SERVER["REQUEST_TIME"] .rand(1001, 9999));
        $code = substr(implode('', array_rand($code, 6)), 0, 6);
        $send_result = $this->send_sms($phone,array($code),"232268");
        $this->err_log(var_export($send_result,1),'sms');
        if($send_result){
            $_SESSION['reg_core'] = $phone."_".$code;
            $result['result'] = "1";//1成功0失败
            $result['desc'] = "验证消息发送成功！";
//            $result['sms_code'] = $code;
            die("0".base64_encode(json_encode($result)));
        }else{
            $result['desc'] = "验证消息发送失败,请重试";
            die("0".base64_encode(json_encode($result)));
        }
    }

    public function do_login(){
        $result = array("result"=>0,"desc"=>'网络请求出错');
        if($_SESSION['usr_info']['user_id']){
            $result['result'] = 1;
            $result['desc'] = '已登录成功';
            die('0'.base64_encode(json_encode($result)));
        }
        $mobile = $this->rsa_decrypt_params(urldecode($_POST['mobile']));
        $code = $_POST['sms_code'];
        if(!$this->is_mobile($mobile)){
            $result['desc'] = '手机号码不正确';
            die('0'.base64_encode(json_encode($result)));
        }
        $source = 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
        if(!isset($_SESSION['reg_core']) ||
            $_SESSION['reg_core'] != $mobile."_".$code ||
            $code == ""){
            $result['desc'] = '验证码错误';
            die('0'.base64_encode(json_encode($result)));
        }
        $usr_info = $this->DAO->get_user_by_mobile($mobile);
        if(!$usr_info){
            $chr = chr(rand(97,122)).chr(rand(97,122));
            $usr_id = $this->DAO->insert_user_info($chr,$mobile,$this->client_ip());
//            $usr_info = $this->DAO->get_user_info($usr_id);
            $this->DAO->insert_login_log($usr_id,$mobile,$this->client_ip(),"",$source,$_SERVER['HTTP_USER_AGENT'],"注册成功","1");
            $_SESSION['usr_info']['user_id'] = $usr_id;
            $_SESSION['usr_info']['nick_name'] = "";
            $_SESSION['usr_info']['mobile'] = $mobile;
            $_SESSION['usr_info']['sex'] = 0;
            $_SESSION['usr_info']['email'] = "";
            $_SESSION['usr_info']['birthday'] = "";
            $_SESSION['usr_info']['buy_mobile'] = $mobile;
            $_SESSION['usr_info']['buy_qq']= null;
            $_SESSION['usr_info']['is_agent'] = 0;
            $_SESSION['usr_info']['user_type'] = 1;
            $result['result'] = 1;
            $result['desc'] = '登录成功';
            die('0'.base64_encode(json_encode($result)));
        }else{
            //登录
            $this->DAO->insert_login_log($usr_info['user_id'],$mobile,$this->client_ip(),$usr_info['password'],$source,$_SERVER['HTTP_USER_AGENT'],"验证码登录成功","1");
            $token = $this->create_guid();
            $this->DAO->update_usr_token($usr_info['user_id'],$token);
            $_SESSION['usr_info']['user_id'] = $usr_info['user_id'];
            $_SESSION['usr_info']['nick_name'] = $usr_info['nick_name'];
            $_SESSION['usr_info']['mobile'] = $usr_info['mobile'];
            $_SESSION['usr_info']['sex'] = $usr_info['sex'];
            $_SESSION['usr_info']['email'] = $usr_info['email'];
            $_SESSION['usr_info']['birthday'] = $usr_info['birthday'];
            $_SESSION['usr_info']['buy_mobile'] = $usr_info['buy_mobile'];
            $_SESSION['usr_info']['buy_qq'] = $usr_info['qq'];
            $_SESSION['usr_info']['guid'] = $usr_info['guid'];
            $_SESSION['usr_info']['is_agent'] = $usr_info['is_agent'];
            $result['result'] = 1;
            $result['desc'] = '登录成功';
            die('0'.base64_encode(json_encode($result)));
        }
    }

    public function is_mobile($mobile){
        return preg_match('/^13[0-9]{1}[0-9]{8}$|15[012356789]{1}[0-9]{8}$|18[0-9]{9}$|14[57]{1}[0-9]{8}$|17[0678]{1}[0-9]{8}$/', $mobile) == 1 ? true : false;
    }

    public function bind_mobile(){
        $result = array("result"=>0,"desc"=>'网络请求出错');
        $params = $_POST;
        $params['mobile'] = $this->rsa_decrypt_params(urldecode($_POST['mobile']));
        if(!$_SESSION['usr_info']['user_id']){
            $result['desc'] = "请先登录";
            die('0'.base64_encode(json_encode($result)));
        }
        if(!$this->is_mobile($params['mobile'])){
            $result['desc'] = '手机号码不正确';
            die('0'.base64_encode(json_encode($result)));
        }
        if(!isset($_SESSION['reg_core']) ||
            $_SESSION['reg_core'] != $params['mobile']."_".$params['sms_code'] ||
            $params['sms_code'] == ""){
            $result['desc'] = '验证码错误';
            die('0'.base64_encode(json_encode($result)));
        }
        $user_info = $this->DAO->get_user_by_mobile($params['mobile']);
        if($user_info){
            $result['result'] = 2;
            $result['desc'] = '手机号已被绑定';
            die('0'.base64_encode(json_encode($result)));
        }
        $this->DAO->update_user_info($params,$_SESSION['usr_info']['user_id']);
        $result['result'] = 1;
        $result['desc'] = '绑定成功';
        die('0'.base64_encode(json_encode($result)));
    }

    public function wx_warrant(){
        header('HTTP/1.1 301 Moved Permanently');
        $url = "http://wx.66173yx.com/warrant.php?act=ylc&app_id=".$_POST['app_id']."&channel=".$_POST['channel'];
        $url = "https://open.weixin.qq.com/connect/oauth2/authorize?appid=wxf965c4a0c7bf9851&redirect_uri=".urlencode($url)."&response_type=code&scope=snsapi_userinfo&state=STATE#wechat_redirect";
        header('Location: '.$url);
    }

    public function logout(){
        unset($_SESSION['usr_info']['user_id']);
        unset($_SESSION['usr_info']['nick_name']);
        unset($_SESSION['usr_info']['mobile']);
        unset($_SESSION['usr_info']['sex']);
        unset($_SESSION['usr_info']['email']);
        unset($_SESSION['usr_info']['birthday']);
        unset($_SESSION['usr_info']['buy_mobile']);
        unset($_SESSION['usr_info']['buy_qq']);
        unset($_SESSION['usr_info']['guid']);
        unset($_SESSION['usr_info']['is_agent']);
        unset($_SESSION['usr_info']);
        unset($_SESSION['wx_openid']);
        die('0'.base64_encode(json_encode(array('result'=>1,'desc'=>'成功退出'))));
    }

    public function draw_list(){
        $result = array('result'=>0,'desc'=>'网络请求错误');
        if(!$_SESSION['usr_info']['user_id']){
            $result['desc'] = '';
        }
        $app_id = $_POST['app_id'];
        $app_info = $this->DAO->get_app_info($app_id);
        if(!$app_info['relation_id']){
            $result['desc'] = '链接出错啦';
            die('0'.base64_encode(json_encode($result)));
        }
        $goods_list = $this->DAO->get_goods_list($app_info['relation_id']);
        if($goods_list){
            foreach($goods_list as $key=>$data){
                $goods_list[$key]['consume'] = $app_info['nnb_scale']*$data['good_price'];
            }
        }
        $result['result'] = 1;
        $result['desc'] = '查询成功';
        $result['data'] = $goods_list;
        die('0'.base64_encode(json_encode($result)));
    }

    public function exchange(){
        $result = array('result'=>0,'desc'=>'网络出错啦');
        $goods_id = $_POST['id'];
        if(!$_SESSION['usr_info']['user_id']){
            $result['result'] = 4;
            $result['desc'] = '请先登录';
            die('0'.base64_encode(json_encode($result)));
        }
        if(!$goods_id){
            $result['desc'] = '请选择兑换商品';
            die('0'.base64_encode(json_encode($result)));
        }
        $user_info = $this->DAO->get_user_info($_SESSION['usr_info']['user_id']);
        if(!$user_info['mobile']){
            $result['result'] = 3;
            $result['desc'] = '请先绑定手机';
            die('0'.base64_encode(json_encode($result)));
        }
        $goods_info = $this->DAO->get_goods_info($goods_id);
        $integral = $goods_info['good_price']*$goods_info['nnb_scale'];
        if($user_info['integral']<$integral){
            $result['result'] = 2;
            $result['desc'] = '您的积分不足';
           die('0'.base64_encode(json_encode($result)));
        }
        $result['result'] = 1;
        $result['desc'] = '您已成功预订该奖品';
        $result['integral'] = $user_info['integral'];
        die('0'.base64_encode(json_encode($result)));
    }

    public function add_device(){
        $app_id = $this->usr_params['app_id'];
        $user_id = $this->usr_params['user_id'];
        $ip = $this->client_ip();
        if(!$app_id || !$user_id){
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
        $this->DAO->add_user_log($this->usr_params,$ip,'H5登录');
        die('0'.base64_encode(json_encode(array("result"=>1,"desc"=>"成功"))));
    }

    public function login_registered(){
        unset($_SESSION['usr_info']['user_id']);
        unset($_SESSION['usr_info']['nick_name']);
        unset($_SESSION['usr_info']['mobile']);
        unset($_SESSION['usr_info']['sex']);
        unset($_SESSION['usr_info']['email']);
        unset($_SESSION['usr_info']['birthday']);
        unset($_SESSION['usr_info']['buy_mobile']);
        unset($_SESSION['usr_info']['buy_qq']);
        unset($_SESSION['usr_info']['guid']);
        unset($_SESSION['usr_info']['is_agent']);
        $this->do_login();
    }

    public function cons_usr_header(){
        if (isset($_SERVER['HTTP_USER_AGENT1'])) {
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

    public function wx_share(){
        $ret = $this->R_DAO->get_wx_access_token();
        if(!$ret){
            COMMON('weixin.class');
            $ret = wxcommon::getToken();
            $this->R_DAO->set_wx_access_token($ret);
        }
        $ACCESS_TOKEN = $ret['access_token'];
        $jsapi_data = $this->R_DAO->get_wx_access_jsapi_data($ACCESS_TOKEN);
        if(!$jsapi_data){
            $url = 'https://api.weixin.qq.com/cgi-bin/ticket/getticket?access_token='.$ACCESS_TOKEN.'&type=jsapi';
            $content = file_get_contents($url);
            $jsapi_data = json_decode($content, true);
            $this->R_DAO->set_wx_access_jsapi_data($ACCESS_TOKEN,$jsapi_data);
        }
        $guid = $this->create_guids();
        $time = time();
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
        $share_url = $protocol.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
        $sign = "jsapi_ticket=".$jsapi_data['ticket']."&noncestr=".$guid."&timestamp=".$time."&url=".$share_url;
        $signature = sha1($sign);
        $this->assign("noncestr", $guid);
        $this->assign("timestamp", $time);
        $this->assign("signature", $signature);
    }

    public function create_guids() {
        $charid = strtolower(md5(uniqid(mt_rand(), true)));
        $uuid = substr($charid, 0, 32);
        return $uuid;
    }
}