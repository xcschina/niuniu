<?php
// -------------------------------------------
// 店铺系统 - 账号登录 <zbc> <2016-04-26>
// 从主站复制而来，稍作修改
// -------------------------------------------
COMMON('oauth.qq');
BO('m'.DS.'common_shop');
DAO('m'.DS.'account_shop_dao');

class account_shop extends common_shop {

    public $DAO;
    public $shop_list_view  = ''; //店铺列表页
    public $shop_login_view = '/index.php?act=user_login_view';

    public function __construct(){
        parent::__construct();
        $this->DAO = new account_shop_dao();
    }

    public function login(){
        if(isset($_SESSION['user_id'])){
            $this->redirect($this->shop_list_view);
            exit;
        }
        $this->display(self::TPL.'/account/login.html');
    }

    public function do_login(){
        if(isset($_SESSION['user_id'])){
            $this->redirect($this->shop_list_view); 
            exit;
        }
        $moblie = $_POST['mobile'];
        $pwd    = $_POST['password'];
        $source = 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
        if(!isset($moblie) || strlen($moblie) == 0){
            $this->DAO->insert_login_log("",$moblie,$this->client_ip(),md5($pwd),$source,$_SERVER['HTTP_USER_AGENT'],"登录用户名为空","0");
            $_SESSION['m_login_error'] = '用户名不能为空～';
            $this->redirect($this->shop_login_view);
            exit;
        }

        if(!isset($pwd) || strlen($pwd) < 6 || strlen($pwd) > 18 ){
            $this->DAO->insert_login_log("",$moblie,$this->client_ip(),md5($pwd),$source,$_SERVER['HTTP_USER_AGENT'],"密码长度不正确","0");
            $_SESSION['m_login_error'] = '密码错误～';
            $this->redirect($this->shop_login_view);
            exit;
        }

        $usr_info = $this->DAO->get_user_by_mobile($moblie);
        if(!$usr_info){
            $this->DAO->insert_login_log("",$moblie,$this->client_ip(),md5($pwd),$source,$_SERVER['HTTP_USER_AGENT'],"手机用户不存在","0");
            $_SESSION['m_login_error'] = '用户不存在～';
            $this->redirect($this->shop_login_view);
            exit;
        }

        if(md5($pwd) != $usr_info['password']){
            $this->DAO->insert_login_log($usr_info['user_id'],$moblie,$this->client_ip(),md5($pwd),$source,$_SERVER['HTTP_USER_AGENT'],"登录密码错误","0");
            $_SESSION['m_login_error'] = '登录密码错误～';
            $this->redirect($this->shop_login_view);
            exit;
        }else{
            $this->DAO->insert_login_log($usr_info['user_id'],$moblie,$this->client_ip(),md5($pwd),$source,$_SERVER['HTTP_USER_AGENT'],"登录成功","1");
            unset($_SESSION['m_login_error']);
            $_SESSION['user_id'] = $usr_info['user_id'];
            $_SESSION['nick_name'] = $usr_info['nick_name'];
            $_SESSION['mobile']=$usr_info['mobile'];
            $_SESSION['sex']=$usr_info['sex'];
            $_SESSION['email']=$usr_info['email'];
            $_SESSION['birthday']=$usr_info['birthday'];
            $_SESSION['buy_mobile']=$usr_info['buy_mobile'];
            $_SESSION['is_agent']=$usr_info['is_agent'];
            if(isset($_SESSION['login_back_url']) && !empty($_SESSION['login_back_url'])){
                $this->redirect($_SESSION['login_back_url']);
            }else{
                $this->redirect($this->shop_list_view);
            }
        }
    }

    public function qq_login_view(){
        $config['appid'] = QQ_APP_ID;
        $config['appkey'] = QQ_APP_KEY;
        $config['callback'] = "http://shop.66173.cn/qq_callback.php";
        $o_qq = new oauth_qq($config);
        $o_qq->login();
    }

    public function qq_do_login(){
        die("服务器升级中，暂时不能登录");
        $config['appid'] = QQ_APP_ID;
        $config['appkey'] = QQ_APP_KEY;
        $config['callback'] = "http://shop.66173.cn/qq_callback.php";
        $o_qq = new oauth_qq($config);

        if(!$o_qq->callback()){
            die('error1');
        }
        if(!$o_qq->get_openid()){
            die('error2');
        }

        $usr_info = $this->DAO->get_user_by_qq_id($_SESSION['qq_openid']);
        if(!$usr_info){
            //echo 'qq用户注册';
            $usr_id = $this->DAO->insert_user_info('','',$this->client_ip(),$_SESSION['qq_openid']);
            $usr_info = $this->DAO->get_user_by_userid($usr_id);
        }
        $_SESSION['user_id'] = $usr_info['user_id'];
        $_SESSION['nick_name'] = $usr_info['nick_name'];
        $_SESSION['mobile'] = $usr_info['mobile'];
        $_SESSION['sex'] = $usr_info['sex'];
        $_SESSION['email'] = $usr_info['email'];
        $_SESSION['birthday'] = $usr_info['birthday'];
        $_SESSION['buy_mobile'] = $usr_info['buy_mobile'];
        $_SESSION['buy_qq'] = $usr_info['buy_qq'];
        if(isset($_SESSION['login_back_url']) && !empty($_SESSION['login_back_url'])){
            $this->redirect($_SESSION['login_back_url']);
        }else{
            $this->redirect($this->shop_login_view);
        }
    }
}