<?php
COMMON('weixinCore', 'pageCore');
DAO('weixin_dao');
class account_wx extends weixinCore{

    public $DAO;

    public function __construct(){
        parent::__construct();
        $this->DAO = new weixin_dao();
        $this->mmc = new Memcache();
        $this->mmc->connect(MMCHOST, MMCPORT);
        if(isset($_GET['code'])){
            $_SESSION['weixin_code'] = $_GET['code'];
        }
    }

    public function check_login(){
        if(!$_SESSION['wx_openid']){
            $this->get_auth_usr_token();
        }
        $_SESSION['usr_info'] = $this->DAO->get_wx_usr_info($_SESSION['wx_openid']);
        $this->build_wx_user_info();

        if(!$_SESSION['usr_info']['user_id']){
            $this->login_view();exit;
        }
    }

    public function index_view(){
        $this->check_login();
        $login_return = $_SESSION['login_return'];

        if($login_return){
            $_SESSION['login_return'] = '';
            unset($_SESSION['login_return']);
            $this->redirect($login_return);
        }else{
            $this->redirect("my.php?act=info");
        }
    }

    public function info_view(){
        $gifts = $this->DAO->get_user_gifts($_SESSION['usr_info']['user_id']);
        $this->assign("gifts", $gifts);
        $this->display("my-gifts.html");
    }

    public function do_mobile_verify(){
        $mobile = $_POST;

        $_SESSION['login_mobile'] = $mobile['tel'];
        $_SESSION['login_v_code'] = $mobile['v-code'];

        if(!isset($_SESSION['reg_core']) || $_SESSION['reg_core']!=$mobile['tel']."_".$mobile['v-code'] || $mobile['v-code']==""){
            $_SESSION['error'] = '验证码错误';
            $this->redirect("my.php?act=login");exit;
        }

        $usr_info = $this->DAO->get_user_by_mobile($mobile['tel']);
        if($usr_info && $usr_info['wx_id'] != ""){
            $_SESSION['error'] = "[".$mobile['tel'].']已绑定到其他微信号，请更换手机号或先解绑。';
            $this->redirect("my.php?act=login");exit;
        }

        if($usr_info && $usr_info['password'] != md5($mobile['pwd'])){
            $_SESSION['error'] = '登入密码错误(您在66173网站注册时候的密码)';
            $this->redirect("my.php?act=login");exit;
        }

        if(strlen($mobile['pwd'])<6 || strlen($mobile['pwd'])>18){
            $_SESSION['error'] = '密码长度6-18位之间';
        }

        $wx_open_id = $_SESSION['wx_usr_info']['openid'];

        if($usr_info){
            $this->DAO->update_usr_wxid($_SESSION['wx_openid'], $usr_info['user_id']);
            $user_id = $usr_info['user_id'];
        }else{
            $user_id = $this->DAO->insert_wx_user($this->create_guid(),$_SESSION['wx_usr_info'], $mobile['tel'], $mobile['pwd'], $this->client_ip());
        }

        $login_return = $_SESSION['login_return'];
        session_unset();
        $_SESSION['wx_openid'] = $wx_open_id;
        $_SESSION['login_return'] = $login_return;
        $this->redirect("my.php");
    }

    public function register_sms($mobile, $code){
        if($code!=$_SESSION['page-hash']){
            $msg['res']="0";//1成功0失败
            $msg['msg']="非法操作";
            die(json_encode($msg));
        }
        if(!$this->is_mobile($mobile)){
            $msg['res']="0";//1成功0失败
            $msg['msg']="手机号格式错误";
            die(json_encode($msg));
        }

        $mobile_count = memcache_get($this->mmc,"mobile-count".session_id());
        if(count($mobile_count)>3){
            die(json_encode(array("res">0, "msg"=>"验证太多次手机号")));
        }
        $mobile_count[$mobile] = 1;
        memcache_set($this->mmc,'mobile-count'.session_id(),$mobile_count,1,86400);

        $usr_info = $this->DAO->get_user_by_mobile($mobile);

        if($usr_info['wx_id'] != ""){
            die(json_encode(array("res">0, "msg"=>"[".$mobile."]已绑定到其他微信号，请更换手机号或先解绑。")));
        }

        $nowtime=strtotime("now");
        if(isset($_SESSION['last_send_time'])) {
            if ($nowtime - $_SESSION['last_send_time'] < 60) {
                $msg['res'] = "0";//1成功0失败
                $msg['msg'] = "获取验证码太频繁，请稍后再试";
                die(json_encode($msg));
            }
        }
        $_SESSION['last_send_time']=$nowtime;

        $code = str_split($_SERVER["REQUEST_TIME"] .rand(1001, 9999));
        $code = substr(implode('', array_rand($code, 6)), 0, 6);
//        $send_result = $this->request('http://is.go.cc/api/sms/sendsms',
//            'appid=8a48b5514b0b8727014b241da79c11e1&mobile=' . $mobile . '&templateid='.$this->SMS_TYPE['MOBILE_BIND_VERIFY_CODE'].'&data='.json_encode(array($code)));
        $send_result = $this->send_sms($mobile,array($code),"23267");
        //$this->err_log()
        //$send_result = json_decode(base64_decode(substr($send_result, 1)));
        $this->err_log(var_export($send_result,1),'sms');
        if($send_result){
            $_SESSION['reg_core']=$mobile."_".$code;
            $msg['res']="1";//1成功0失败
            $msg['msg']="验证消息发送成功。";
//            $msg['code']=$code;
            die(json_encode($msg));
        }else{
            $_SESSION['reg_core']='';
            $msg['res']="0";//1成功0失败
            $msg['msg']="验证消息发送失败,请重试";
            echo json_encode($msg);
        }
    }

    public function login_view(){
        $this->page_hash();
        $this->V->assign("info", $_SESSION['wx_usr_info']);
        $this->V->display("bind-tel.html");
        $_SESSION['error'] = '';
    }
}