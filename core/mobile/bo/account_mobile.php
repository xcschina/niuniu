<?php
COMMON('baseCore', 'pageCore','imageCore','uploadHelper','class.phpmailer','oauth.qq');
DAO('account_dao','my_dao');
class account_mobile  extends baseCore{
    public $DAO;

    public function __construct(){
        parent::__construct();
        $this->DAO=new account_dao();
    }

    public function login(){
        if(isset($_SESSION['user_id'])){
            $this->redirect("account.php?act=user_center");
            exit;
        }
        $this->page_hash();
        $this->display("member/login.html");
    }

    public function do_login(){
        $result = array("code"=>0,"msg"=>"网络出错");
        if(isset($_SESSION['user_id'])){
            $this->redirect("account.php?act=user_center");
            exit;
        }
        if($_SESSION['page-hash']!=$_POST['pagehash']){
            if($_POST['login'] == "ajax_login"){
                $result['msg'] = "出错啦～";
               die(json_encode($result));
            }else{
                $_SESSION['m_login_error'] = '出错了～';
                $this->redirect("account.php?act=login");
            }
            exit;
        }
        $moblie = $_POST['mobile'];
        $pwd = $_POST['password'];
        $source = 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
        if(!isset($moblie) || strlen($moblie) == 0){
            $this->DAO->insert_login_log("",$moblie,$this->client_ip(),md5($pwd),$source,$_SERVER['HTTP_USER_AGENT'],"登录用户名为空","0");
            if($_POST['login'] =="ajax_login"){
                $result['msg'] = "用户名不能为空～";
                die(json_encode($result));
            }else{
                $_SESSION['m_login_error'] = '用户名不能为空～';
                $this->redirect("account.php?act=login");
            }
            exit;
        }

        if(!isset($pwd) || strlen($pwd) < 6 || strlen($pwd) > 18 ){
            $this->DAO->insert_login_log("",$moblie,$this->client_ip(),md5($pwd),$source,$_SERVER['HTTP_USER_AGENT'],"密码长度不正确","0");
            if($_POST['login'] =="ajax_login"){
                $result['msg'] = "密码长度不正确～";
                die(json_encode($result));
            }else{
                $_SESSION['m_login_error'] = '密码长度不正确～';
                $this->redirect("account.php?act=login");
            }
            exit;
        }

        $usr_info = $this->DAO->get_user_by_mobile($moblie);
        if(!$usr_info){
            $this->DAO->insert_login_log("",$moblie,$this->client_ip(),md5($pwd),$source,$_SERVER['HTTP_USER_AGENT'],"手机用户不存在","0");
            if($_POST['login'] =="ajax_login"){
                $result['msg'] = "用户不存在～";
                die(json_encode($result));
            }else{
                $_SESSION['m_login_error'] = '用户不存在～';
                $this->redirect("account.php?act=login");
            }
            exit;
        }


        if(md5($pwd) != $usr_info['password']){
            $this->DAO->insert_login_log($usr_info['user_id'],$moblie,$this->client_ip(),md5($pwd),$source,$_SERVER['HTTP_USER_AGENT'],"登录密码错误","0");
            if($_POST['login'] =="ajax_login"){
                $result['msg'] = "登录密码错误～";
                die(json_encode($result));
            }else{
                $_SESSION['m_login_error'] = '登录密码错误～';
                $this->redirect("account.php?act=login");
            }
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
            $_SESSION['buy_qq']=$usr_info['qq'];
            $_SESSION['is_agent']=$usr_info['is_agent'];

            if(isset($_SESSION['login_back_url']) && !empty($_SESSION['login_back_url'])){
                if($_POST['login'] =="ajax_login"){
                    $result['code'] = 1;
                    $result['msg'] = "登录成功";
                    $result['url'] = 'http://'.$_SERVER['HTTP_HOST'].'/'.$_SESSION['login_back_url'];
                    die(json_encode($result));
                }else{
                    $this->redirect($_SESSION['login_back_url']);
                }
            }else{
                if($_POST['login'] =="ajax_login"){
                    $result['code'] = 1;
                    $result['msg'] = "登录成功";
                    $result['url'] = 'http://'.$_SERVER['HTTP_HOST'].'/'."account.php?act=user_center";
                    die(json_encode($result));
                }else{
                    $this->redirect("account.php?act=user_center");
                }
            }
        }
    }


    // public function synchro_login(){
    //     // 店铺登陆
    //     $par['i']    = $usr_info['user_id']; // 用户id
    //     $par['t']    = time(); // 时间戳
    //     $par['sign'] = md5(http_build_query($par));
    //     $urls = array('http://shop.66173.cn/index.php?act=user_synlogin_do');
    //     foreach ($urls as $url) {
    //         $res = file_get_contents($url.'&'.http_build_query($par));
    //     }
    //     // p($res);
    //     // die;
    // }

    // public function synchro_session_login(){
    //     // 店铺登陆
    //     $par['s']    = session_id();
    //     $par['t']    = time();
    //     $par['sign'] = md5(http_build_query($par));
    //     $urls = array('http://shop.66173.cn/index.php?act=user_synlogin_do');
    //     foreach ($urls as $url) {
    //         $res = file_get_contents($url.'&'.http_build_query($par));
    //     }
    //     p($res);
    // }

    public function user_center(){
        if(!isset($_SESSION['user_id'])){
            $this->redirect("account.php?act=login");
            exit;
        }
        $login_log=$this->DAO->get_login_log($_SESSION['user_id']);
        $login_info['last_login_ip']="";
        $login_info['last_login_city']="";
        $login_info['last_login_time']="";
        $login_info['now_login_ip']=$this->client_ip();
        foreach($login_log as $index=>$log){
            if($index==1){
                $login_info['last_login_ip']=$log['login_ip'];
                $login_info['last_login_time']=date('Y-m-d H:i:s', $log['create_time']);
                $ip_city=$this->get_ip_lookup($log['login_ip']);
                if(!$ip_city){
                    $login_info['last_login_city']="未知/本地";
                }else{
                    $login_info['last_login_city']=$ip_city['province'].$ip_city['city'];
                }
            }
        }
        $this->assign("login_info",$login_info);
        $this->assign("op","");
        if($_GET['act']=='setting_view'){
            $this->assign("op","setting_view");
        }
        $my_dao=new my_dao();
        $unread_msg_count=$my_dao->get_unread_msg_count($_SESSION['user_id']);
        $this->assign("unread_msg",$unread_msg_count);
        $f_back_url = $_SERVER['HTTP_REFERER']?:'http://m.66173.cn';
        $this->assign('f_back_url', $f_back_url);
        $this->display("member/user_center.html");
    }

    public function modify_user(){
        if(!isset($_SESSION['user_id'])){
            $this->redirect("account.php?act=login");
            exit;
        }
        $user_info=$this->DAO->get_user_by_userid($_SESSION['user_id']);
        $this->assign("user_info",$user_info);
        $this->display("member/modify_user.html");
    }


    public function logout(){
        unset( $_SESSION['user_id']);
        unset( $_SESSION['nick_name']);
        unset( $_SESSION['mobile']);
        unset($_SESSION['sex']);
        unset($_SESSION['email']);
        unset($_SESSION['birthday']);
        unset($_SESSION['qq_openid']);
        $this->redirect("");
    }

    public function forget(){
        $this->page_hash();
        $this->display("member/forget.html");
    }


    public function  register(){
        $this->page_hash();
        $this->display("register.html");
        unset($_SESSION['m_error']);
        unset($_SESSION['code_error']);
        unset($_SESSION['p_error']);
        unset($_SESSION['cp_error']);
        unset($_SESSION['mobile']);
    }


    public function do_register(){
        $error=false;
        $_SESSION['mobile']=$_POST['mobile'];
        if(!$this->is_mobile($_POST['mobile'])){
            $_SESSION['m_reg_error'] = '手机号码格式不正确';
            $error=true;
        }

        if(!$error && !isset($_SESSION['reg_core']) ||
            $_SESSION['reg_core']!=$_POST['mobile']."_".$_POST['sms_code'] ||
            $_POST['sms_code']==""){
            $_SESSION['m_reg_error'] = '验证码错误';
            $error=true;
        }

        if(!$error && strtotime("now")-$_SESSION['last_send_time']>300){
            $_SESSION['m_reg_error'] = '验证码超时';
            $error=true;
        }
        if(!$error && strlen($_POST['password'])<6 || strlen($_POST['password'])>18){
            $_SESSION['m_reg_error'] = '密码长度6-18位之间';
            $error=true;
        }
        if(!$error && $_POST['cpassword']!=$_POST['password']){
            $_SESSION['m_reg_error'] = '两次密码不一致';
            $error=true;
        }
        if(!$error){
            $user_info=$this->DAO->get_user_by_mobile($_POST['mobile']);
            if($user_info){
                if($user_info['mobile']==$_POST['mobile']){
                    $_SESSION['m_reg_error'] = '手机号码已被注册～';
                    $error=true;
                }
            }
        }

        if($error){
            $this->register();
            unset( $_SESSION['m_reg_error']);
        }else{
            $usr_id = $this->DAO->insert_user_info(md5($_POST['password']),$_POST['mobile'],$this->client_ip());
//            if(!empty($_SESSION['promoter_id'])){
//                $this->DAO->update_usr_info_popu($_SESSION['promoter_id'],$usr_id);
//            }
            //$usr_info = $this->DAO->get_user_by_userid($usr_id);
            $_SESSION['user_id'] = $usr_id;
            $_SESSION['nick_name'] = "";
            $_SESSION['mobile'] = $_POST['mobile'];
            $_SESSION['sex'] = 0;
            $_SESSION['email'] = "";
            $_SESSION['birthday'] = "";
            $_SESSION['buy_mobile'] = $_POST['mobile'];
            $_SESSION['buy_qq'] = "";
            $_SESSION['is_agent']=0;
            $_SESSION['user_type'] = 1;
            if(isset($_SESSION['login_back_url']) && !empty($_SESSION['login_back_url'])){
                $this->redirect($_SESSION['login_back_url']);
            }else{
                $this->redirect("");
            }
        }
    }

    public function do_forget(){
        $error=false;
        $_SESSION['fg_mobile']=$_POST['mobile'];
//        if(empty($_SESSION['user_id'])){
//            $this->redirect("account.php?act=login");
//            exit;
//        }
        if(!$this->is_mobile($_POST['mobile'])){
            $_SESSION['f_error'] = '手机号码格式不正确';
            $error=true;
        }

        if(!$error && (!isset($_SESSION['reg_core']) ||
                $_SESSION['reg_core']!=$_POST['mobile']."_".$_POST['sms_code'] ||
                $_POST['sms_code']=="")){
            $_SESSION['fg_error'] = '验证码错误';
            $error=true;
        }

        if(!$error && strtotime("now")-$_SESSION['last_send_time']>300){
            $_SESSION['fg_error'] = '验证码超时';
            $error=true;
        }

        if(!$error){
            $user_info=$this->DAO->get_user_by_mobile($_POST['mobile']);
            if(!$user_info){
                $_SESSION['fg_error'] = '该手机未注册～';
                $error=true;
            }
        }
        if(!$error && strlen($_POST['password'])<6 || strlen($_POST['password'])>18){
            $_SESSION['fg_error'] = '密码长度6-18位之间';
            $error=true;
        }
        if(!$error && $_POST['cpassword']!=$_POST['password']){
            $_SESSION['fg_error'] = '两次密码不一致';
            $error=true;
        }

        if($error){
            if($user_info){
                $this->DAO->insert_operation_log($user_info['user_id'],"2",0,$_SESSION['f_error']);
            }
            $this->forget();
            unset($_SESSION['fg_error']);
        }else{
            $this->DAO->update_user_psw(md5($_POST['password']),$_POST['mobile'],$user_info['user_id'],$this->create_guid());
            $this->DAO->insert_operation_log($user_info['user_id'],"2",1,"密码由“".$user_info['password']."”变更为“".md5($_POST['password'])."”");
            $this->redirect("account.php?act=login");
        }
    }


    public function modify_psw(){
        if(!isset($_SESSION['user_id'])){
            $this->redirect("account.php?act=login");
            exit;
        }
        $this->display("member/modify_psw.html");
    }

    public function modify_phone($type){
        if(!isset($_SESSION['user_id'])){
            $this->redirect("account.php?act=login");
            exit;
        }
        if($type=="1"){
            $this->assign("type","mobile_info");
        }else{
            $this->assign("type","mobile_change");
        }
        $this->display("member/modify_phone.html");
    }

    public function modify_email($type){
        if(!isset($_SESSION['user_id'])){
            $this->redirect("account.php?act=login");
            exit;
        }
        if($type=="1"){
            $this->assign("type","email_info");
        }else{
            $this->assign("type","email_change");
        }
        $this->display("member/modify_email.html");
    }

    public function do_modify_psw(){
        $error=false;
        if(empty($_SESSION['user_id'])){
            $this->redirect("account.php?act=login");
            exit;
        }
        if($_SESSION['qq_openid']){exit;}
        if(!trim($_POST['opassword'])){
            $_SESSION['m_modify_p_error'] = '旧密码不能为空';
            $error=true;
        }

        if(!$error && !trim($_POST['password'])){
            $_SESSION['m_modify_p_error'] = '新密码不能为空';
            $error=true;
        }

        if(!$error && strlen($_POST['password']) < 6 || strlen($_POST['password']) > 18){
            $_SESSION['m_modify_p_error'] = '密码为字母和数字，支持大小写，长度6-18个字符';
            $error=true;
        }

        if(!$error && trim($_POST['password']) != trim($_POST['cpassword'])){
            $_SESSION['m_modify_p_error'] = '两次密码不一致';
            $error=true;
        }

        if(!$error && $_POST['opassword'] == $_POST['password']){
            $_SESSION['m_modify_p_error'] = '密码没有发生变化';
            $error=true;
        }

        $usr_info = $this->DAO->get_user_by_userid($_SESSION['user_id']);
        if(!$error && md5($_POST['opassword']) != $usr_info['password']){
            $_SESSION['m_modify_p_error'] = '旧密码错误';
            $error=true;
        }

        if(!$error){
            $this->DAO->update_user_psw(md5($_POST['password']),$usr_info['mobile'],$_SESSION['user_id'],$this->create_guid());
            $this->DAO->insert_operation_log($_SESSION['user_id'],"1",1,"密码由“".$usr_info['password']."”变更为“".md5($_POST['password'])."”");
            $this->redirect("account.php?act=login");
        }else{
            $this->DAO->insert_operation_log($_SESSION['user_id'],"1",0, $_SESSION['m_modify_p_error']);
            $this->redirect("account.php?act=logout");
            unset($_SESSION['m_modify_p_error']);
        }
    }

    public function do_modify_phone(){
        $error=false;
        if(empty($_SESSION['user_id'])){
            $this->redirect("account.php?act=login");
            exit;
        }

        $usr_info = $this->DAO->get_user_by_userid($_SESSION['user_id']);
        if(md5($_POST['password']) != $usr_info['password']){
            $_SESSION['m_modify_phone_error'] = '验证密码错误';
            $error=true;
        }

        if(!$error && !$this->is_mobile($_POST['mobile'])){
            $_SESSION['m_modify_phone_error'] = '手机号码格式不正确';
            $error=true;
        }
        if(!$error && $_POST['mobile']== $usr_info['moblie']){
            $_SESSION['m_modify_phone_error'] = '手机号码没有发生变化';
            $error=true;
        }

        $other_user=$this->DAO->get_user_by_mobile($_POST['mobile']);
        if(!$error && $other_user && $other_user['user_id']!=$_SESSION['user_id']){
            $_SESSION['m_modify_phone_error'] = '手机号码已经被注册';
            $error=true;
        }

        if(!$error && (!isset($_SESSION['reg_core']) ||
                $_SESSION['reg_core']!=$_POST['mobile']."_".$_POST['sms_code'] ||
                $_POST['sms_code']=="")){
            $_SESSION['m_modify_phone_error'] = '验证码错误';
            $error=true;
        }

        if(!$error && strtotime("now")-$_SESSION['last_send_time']>300){
            $_SESSION['m_modify_phone_error'] = '验证码超时';
            $error=true;
        }

        if(!$error){
            $this->DAO->update_user_phone($_POST['mobile'],$_SESSION['user_id']);
            $this->DAO->insert_operation_log($_SESSION['user_id'],"5",1,"手机由“".$usr_info['mobile']."”变更为“".$_POST['mobile']."”");
            $_SESSION['mobile']=$_POST['mobile'];
            if(isset($_SESSION['login_back_url']) && !empty($_SESSION['login_back_url'])){
                $this->redirect($_SESSION['login_back_url']);
            }else{
                $this->modify_phone("1");
            }
        }else{
            $this->DAO->insert_operation_log($_SESSION['user_id'],"5",0,$_SESSION['m_modify_phone_error']);
            $this->modify_phone("2");
            unset($_SESSION['m_modify_phone_error']);
        }
    }

    public function do_qqlogin_phone_bind(){
        $error=false;
        if(empty($_SESSION['user_id'])){
            $this->redirect("account.php?act=login");
            exit;
        }

        if(!$error && strlen($_POST['password'])<6 || strlen($_POST['password'])>18){
            $_SESSION['m_modify_phone_error'] = '密码为字母和数字，支持大小写，长度6-18个字符';
            $error=true;
        }
        if(!$error && $_POST['cpassword']!=$_POST['password']){
            $_SESSION['m_modify_phone_error'] = '两次密码不一致';
            $error=true;
        }

        if(!$error && !$this->is_mobile($_POST['mobile'])){
            $_SESSION['m_modify_phone_error'] = '手机号码格式不正确';
            $error=true;
        }


        $other_user=$this->DAO->get_user_by_mobile($_POST['mobile']);
        if(!$error && $other_user && $other_user['user_id']!=$_SESSION['user_id']){
            $_SESSION['m_modify_phone_error'] = '手机号码已经被注册';
            $error=true;
        }

        if(!$error && (!isset($_SESSION['reg_core']) ||
                $_SESSION['reg_core']!=$_POST['mobile']."_".$_POST['sms_code'] ||
                $_POST['sms_code']=="")){
            $_SESSION['m_modify_phone_error'] = '验证码错误';
            $error=true;
        }

        if(!$error && strtotime("now")-$_SESSION['last_send_time']>300){
            $_SESSION['m_modify_phone_error'] = '验证码超时';
            $error=true;
        }

        if(!$error){
            $this->DAO->update_user_phone($_POST['mobile'],$_SESSION['user_id']);//绑定号码
            $this->DAO->update_user_psw(md5($_POST['password']),$_POST['mobile'],$_SESSION['user_id'],$this->create_guid());//设置密码
            $this->DAO->insert_operation_log($_SESSION['user_id'],"5",1,"手机由“”变更为“".$_POST['mobile']."”");
            $this->DAO->insert_operation_log($_SESSION['user_id'],"1",1,"密码由“”变更为“".md5($_POST['password'])."”");
            $_SESSION['mobile']=$_POST['mobile'];
            $_SESSION['buy_mobile'] = $_POST['mobile'];
            $this->modify_phone("1");
        }else{
            $this->DAO->insert_operation_log($_SESSION['user_id'],"5",0,$_SESSION['m_modify_phone_error']);
            $this->modify_phone("2");
            unset($_SESSION['m_modify_phone_error']);

        }
    }

    public function do_modify_email(){
        $error=false;
        if(empty($_SESSION['user_id'])){
            $this->redirect("account.php?act=login");
            exit;
        }

        $usr_info = $this->DAO->get_user_by_userid($_SESSION['user_id']);
        if(md5($_POST['password']) != $usr_info['password']){
            $_SESSION['m_modify_email_error'] = '登录密码错误';
            $error=true;
        }

        if(!$error && !$this->is_email($_POST['email'])){
            $_SESSION['m_modify_email_error'] = '邮箱格式不正确';
            $error=true;
        }
        if(!$error && $_POST['email']== $usr_info['email']){
            $_SESSION['m_modify_email_error'] = '邮箱没有发生变化';
            $error=true;
        }

        $other_user=$this->DAO->get_user_by_email($_POST['email']);
        if(!$error && $other_user && $other_user['user_id']!=$_SESSION['user_id']){
            $_SESSION['m_modify_email_error'] = '邮箱已经被注册';
            $error=true;
        }

        if(!$error && (!isset($_SESSION['reg_core']) ||
                $_SESSION['email_core']!=$_POST['email']."_".$_POST['sms_code'] ||
                $_POST['sms_code']=="")){
            $_SESSION['m_modify_email_error'] = '验证码错误';
            $error=true;
        }

        if(!$error && strtotime("now")-$_SESSION['last_send_em_time']>300){
            $_SESSION['m_modify_email_error'] = '验证码超时';
            $error=true;
        }

        if(!$error){
            $this->DAO->update_user_email($_POST['email'],$_SESSION['user_id']);
            $this->DAO->insert_operation_log($_SESSION['user_id'],"6",1,"邮箱由“".$usr_info['email']."”变更为“".$_POST['email']."”");
            $_SESSION['email']=$_POST['email'];
            $this->modify_email("1");
        }else{

            $this->DAO->insert_operation_log($_SESSION['user_id'],"6",0,$_SESSION['m_modify_email_error']);
            $this->modify_email("2");
            unset($_SESSION['m_modify_email_error']);
        }
    }

    public function do_modify_user(){
        $usr = $_POST;
        $error=false;
        if(empty($_SESSION['user_id'])){
            $this->redirect("account.php?act=login");
            exit;
        }
        $_SESSION['modify_info_error']="修改成功";
        if(!$error && trim($usr['nick_name'])==""){
            $_SESSION['m_modify_user_error']="昵称不能为空";
            $error=true;
        }
        if(!$error && strlen(trim($usr['nick_name'])) < 4 && !preg_match("/^[\x{4e00}-\x{9fa5}\w]{2,20}$/u", $usr['nick_name'])){
            $_SESSION['m_modify_user_error']="昵称不合法";
            $error=true;
        }
        if(!$error && $usr['nick_name'] != $_SESSION['nick_name'] && $this->DAO->get_user_by_nickname($usr['nick_name'],$_SESSION['user_id'])){
            $_SESSION['m_modify_user_error']="昵称已经被人使用啦";
            $error=true;
        }
        if(!$error && $usr['birthday']==""){
            $_SESSION['m_modify_user_error']="请选择生日";
            $error=true;
        }
        if(!$error && $usr['sex']==""){
            $_SESSION['m_modify_user_error']="请选择性别";
            $error=true;
        }
        if(!$error){
            $this->DAO->update_usr_info($usr,$_SESSION['user_id']);
            $this->DAO->insert_operation_log($_SESSION['user_id'],"3",1,"昵称由“".$_SESSION['nick_name']."”变更为“".$usr['nick_name']."”，
            生日由“".$_SESSION['birthday']."”变更为“".$usr['birthday']."”，性别由“".$_SESSION['sex']."”变更为“".$usr['sex']."”");
            $_SESSION['nick_name'] = $usr['nick_name'];
            $_SESSION['birthday'] = $usr['birthday'];
            $_SESSION['sex']=$usr['sex'];
        }else{
            $this->DAO->insert_operation_log($_SESSION['user_id'],"3",0,$_SESSION['modify_user_error']);
        }
        $this->redirect("account.php?act=modify_user");
        unset($_SESSION['m_modify_user_error']);
    }

    /**
     * 发送验证码短信
     */
    public function register_sms(){
        if(!$_POST['pagehash'] || $_POST['pagehash']!=$_SESSION['page-hash']){
            $msg['res']="0";//1成功0失败
            $msg['msg']="发生错误，错误代码E001,请重新刷新页面。";
            die(json_encode($msg));
        }
        if(empty($_SERVER['HTTP_REFERER']) || $_SERVER['HTTP_REFERER']=='http://m.66173.cn/account.php?act=register_sms'){
             exit();
        }
        $today = date('Y-m-d',time());
        $phone = $_POST['mobile'];
        if(!$this->is_mobile($phone)){
            $msg['res']="0";//1成功0失败
            $msg['msg']="手机号码格式错误,请重试";
            die(json_encode($msg));
        }

        $nowtime=strtotime("now");
        $last_session = $_SESSION['last_send_time'];
        if(isset($_SESSION['last_send_time'])){
            if($nowtime-$_SESSION['last_send_time']<12){
                $msg['res']="0";//1成功0失败
                $msg['msg']="获取验证码太频繁，请稍后再试";
                die(json_encode($msg));
            }else{
                $_SESSION['last_send_time']=$nowtime;
            }
        }else{
            $_SESSION['last_send_time']=$nowtime;
        }

        $this->send_verify($this->client_ip(),$phone,$today);

        $code = str_split($_SERVER["REQUEST_TIME"] .rand(1001, 9999));
        $code = substr(implode('', array_rand($code, 6)), 0, 6);
        $data = array(
            'ip'=>$this->client_ip(),
            'phone'=>$phone,
            'code'=>$code,
            'today'=>$today,
            'HTTP_REFERER'=>$_SERVER['HTTP_REFERER'],
            'session'=>$last_session,
            'now_time'=>$nowtime,
        );
        $this->err_log(var_export($data,1),'m_sms_log');
        if(empty($_SERVER['HTTP_REFERER']) || $_SERVER['HTTP_REFERER']=='http://m.66173.cn/account.php?act=register_sms'){
            $msg['res']="0";//1成功0失败
            $msg['msg']="请求太频繁！！！";
            die(json_encode($msg));
        }
//        $send_result = $this->send_sms($phone,array($code),"23267");
        $send_result = $this->bm_send_sms($phone,array($code));
        $this->err_log(var_export($send_result,1),'m_sms');
        if($send_result){
            $this->DAO->add_send_sms_log($this->client_ip(),$phone,$code,$today,$_SERVER['HTTP_REFERER'],1);
            $_SESSION['reg_core']=$phone."_".$code;
            $msg['res']="1";//1成功0失败
            $msg['msg']="验证消息发送成功！";
//            $msg['code']=$code;
            die(json_encode($msg));
        }else{
            $this->DAO->add_send_sms_log($this->client_ip(),$phone,$code,$today,$_SERVER['HTTP_REFERER'],0);
            $msg['res']="0";//1成功0失败
            $msg['msg']="验证消息发送失败,请重试";
            echo json_encode($msg);
        }
    }

    public function send_verify($ip,$phone,$today){
//        $ip_count = $this->DAO->get_ip_count($ip,$today);
//        if($ip_count['num'] >= 5){
//            $msg['res'] = "0";//1成功0失败
//            $msg['msg'] = "本日该手机号接受短信已上限!!!";
//            die(json_encode($msg));
//        }
        $phone_count = $this->DAO->get_phone_count($phone,$today);
        if($phone_count['num'] >= 5){
            $msg['res'] = "0";//1成功0失败
            $msg['msg'] = "本日该手机号接受短信已上限";
            die(json_encode($msg));
        }
    }

    public function email_code(){
        $email = $_POST['email'];
        if(!$email){
            $msg['res']="0";//1成功0失败
            $msg['msg']="邮箱不能为空";
            die(json_encode($msg));
        }

        if(!$this->is_email($email)){
            $msg['res']="0";//1成功0失败
            $msg['msg']="非法邮箱，请重新输入";
            die(json_encode($msg));
        }

        $code = str_split($_SERVER["REQUEST_TIME"] . rand(1001, 9999));
        $code = substr(implode('', array_rand($code, 6)), 0, 6);

        $content = '<p style=\"line-height: 20px;\"><strong>亲爱的用户：</strong></p>
                    <p style=\"line-height: 20px;\">&nbsp; &nbsp;您正在进行邮箱绑定操作，请将以下验证码复制粘贴到网页（此验证码5分钟内有效，超时需要刷新网页重新获取）</p>
                    <p style=\"line-height: 20px;\">&nbsp; &nbsp;验证码：<strong>'.$code.'</strong></p>
                    <p style=\"line-height: 20px;\">&nbsp; &nbsp;如果没有申请绑定邮箱，请忽略此邮件！</p>
                    <p style=\"line-height: 20px;\">&nbsp; &nbsp;感谢您对<a href=\"http://66173.cn\">66173</a>的支持，祝您生活愉快！</p>
                    <p style=\"line-height: 20px;\"><span style=\"margin-left: 200px;\">'.date('Y-m-d H:i', strtotime("now")).'</span></p>
                    <p style=\"line-height: 20px;\">&nbsp; &nbsp;（这是一封系统自动产生的Email,请勿回复）</p>';
        $result=$this->send_mail($email,'邮箱验证码',$content);
        if($result->message=='error'){
            $msg['res']="0";//1成功0失败
            $msg['msg']="验证码发送失败";
            echo(json_encode($msg));
            exit;
        }

        $_SESSION['email_core']=$email."_".$code;
        $_SESSION['last_send_em_time']=strtotime("now");
        $msg['res']="1";//1成功0失败
        $msg['msg']="验证码发送成功！";
//        $msg['code']=$code;
        die(json_encode($msg));
    }

    public function qq_login_view(){
        $config['appid'] = QQ_APP_ID;
        $config['appkey'] = QQ_APP_KEY;
        $config['callback'] = "http://m.66173.cn/qq_callback.php";
        $o_qq = new oauth_qq($config);
        $o_qq->login();
    }

    public function qq_do_login(){
        $config['appid'] = QQ_APP_ID;
        $config['appkey'] = QQ_APP_KEY;
        $config['callback'] = "http://m.66173.cn/qq_callback.php";
        $o_qq = new oauth_qq($config);

        if(!$o_qq->callback()){
            die('error1');
        }
        if(!$o_qq->get_openid()){
            die('error2');
        }

        $usr_info = $this->DAO->get_user_by_qq_id($_SESSION['qq_openid']);
        $source='http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
        if(!$usr_info){
            //echo 'qq用户注册';
            $usr_id = $this->DAO->insert_user_info('','',$this->client_ip(),$_SESSION['qq_openid']);
            //$usr_info = $this->DAO->get_user_by_userid($usr_id);
            $usr_info = array("user_id"=>$usr_id,"nick_name"=>"","mobile"=>"","sex"=>0,"email"=>"","birthday"=>"","buy_mobile"=>"","buy_qq"=>null);
            $this->DAO->insert_qq_login_log("","",$this->client_ip(),$_SESSION['qq_openid'],$source,$_SERVER['HTTP_USER_AGENT'],"新qq登录成功","1");
        }else{
            $this->DAO->insert_qq_login_log("","",$this->client_ip(),$_SESSION['qq_openid'],$source,$_SERVER['HTTP_USER_AGENT'],"qq登录成功","1");
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
            $this->redirect("account.php?act=user_center");
        }
    }
}