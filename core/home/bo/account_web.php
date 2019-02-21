<?php
COMMON('baseCore', 'pageCore','imageCore','uploadHelper','class.phpmailer','oauth.qq');
DAO('account_dao','my_dao','common_dao');
class account_web  extends baseCore{
    public $DAO;
    public $COMDAO;
    public function __construct(){
        parent::__construct();
        $this->DAO=new account_dao();
        $this->COMDAO=new common_dao();
        $notices=$this->COMDAO->get_mod_articles(14);
        $links=$this->COMDAO->get_friendly_links();
        if(!empty($_GET['promoter_id'])){
            $promoter_code = $this->COMDAO->get_promoter_code($_GET['promoter_id']);
            if($promoter_code){
                $_SESSION['promoter_id'] = $_GET['promoter_id'];
            }
        }
        $this->assign("notices", $notices);
        $this->assign("links", $links);

        //$this->V->template_dir  = VIEW.DS."v2";
        //$this->open_debug();
    }

    public function login(){
        //$this->open_debug();
        if(isset($_SESSION['user_id'])){
            $this->redirect("account.php?act=user_center");
            exit;
        }
        $games = $this->COMDAO->get_hot_games();
        $result = $this->get_http_host();
        $domain_name = $result['domain_name'];
        $this->assign('domain_name',$domain_name);
        $this->assign("games", $games);
        $this->display("account/login.html");
    }

    public function do_login(){
        if(isset($_SESSION['user_id'])){
            $this->redirect("account.php?act=user_center");
            exit;
        }

        $moblie = $_POST['mobile'];
        $pwd = $_POST['password'];
        $source='http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
        if(!isset($moblie) || strlen($moblie) == 0){
            $this->DAO->insert_login_log("",$moblie,$this->client_ip(),md5($pwd),$source,$_SERVER['HTTP_USER_AGENT'],"登录用户名为空","0");
            $_SESSION['do_login_error'] = '用户名不能为空～';
            $this->redirect("account.php?act=login");
            exit;
        }

        if(!isset($pwd) || strlen($pwd) < 6 || strlen($pwd) > 18 ){
            $this->DAO->insert_login_log("",$moblie,$this->client_ip(),md5($pwd),$source,$_SERVER['HTTP_USER_AGENT'],"密码长度不正确","0");
            $_SESSION['do_login_error'] = '密码错误～';
            $this->redirect("account.php?act=login");
            exit;
        }

        $usr_info = $this->DAO->get_user_by_mobile($moblie);
        if(!$usr_info){
            $this->DAO->insert_login_log("",$moblie,$this->client_ip(),md5($pwd),$source,$_SERVER['HTTP_USER_AGENT'],"手机用户不存在","0");
            $_SESSION['do_login_error'] = '用户不存在～';
            $this->redirect("account.php?act=login");
            exit;
        }

        if(md5($pwd) != $usr_info['password']){
            $this->DAO->insert_login_log($usr_info['user_id'],$moblie,$this->client_ip(),md5($pwd),$source,$_SERVER['HTTP_USER_AGENT'],"登录密码错误","0");
            $_SESSION['do_login_error'] = '登录密码错误～';
            $this->redirect("account.php?act=login");
            exit;
        }else{
            $this->DAO->insert_login_log($usr_info['user_id'],$moblie,$this->client_ip(),md5($pwd),$source,$_SERVER['HTTP_USER_AGENT'],"登录成功","1");
            unset($_SESSION['do_login_error']);
            $_SESSION['user_id'] = $usr_info['user_id'];
            $_SESSION['nick_name'] = $usr_info['nick_name'];
            $_SESSION['mobile']=$usr_info['mobile'];
            $_SESSION['sex']=$usr_info['sex'];
            $_SESSION['email']=$usr_info['email'];
            $_SESSION['birthday']=$usr_info['birthday'];
            $_SESSION['buy_mobile']=$usr_info['buy_mobile'];
            $_SESSION['buy_qq']=$usr_info['qq'];
            $_SESSION['guid'] = $usr_info['guid'];
            $_SESSION['is_agent'] = $usr_info['is_agent'];
            //记住登陆状态
            if(isset($_POST['cookie_check']) && $_POST['cookie_check']=1){
                $_SESSION['cookie_check']=1;
            }
            if(isset($_SESSION['login_back_url']) && !empty($_SESSION['login_back_url'])){
                $this->redirect($_SESSION['login_back_url']);
            }else{
                $this->redirect("account.php?act=user_center");
            }
        }
    }

    public function user_center(){
        if(!isset($_SESSION['user_id'])){
            $this->redirect("account.php?act=login");
            exit;
        }
//        $login_log=$this->DAO->get_login_log($_SESSION['user_id']);
//        $login_info['last_login_ip']="";
//        $login_info['last_login_city']="";
//        $login_info['last_login_time']="";
//        $login_info['now_login_ip']=$this->client_ip();
//        foreach($login_log as $index=>$log){
//            if($index==1){
//                $login_info['last_login_ip']=$log['login_ip'];
//                $login_info['last_login_time']=date('Y-m-d H:i:s', $log['create_time']);
//                $ip_city=$this->get_ip_lookup($log['login_ip']);
//                if(!$ip_city){
//                    $login_info['last_login_city']="未知/本地";
//                }else{
//                    $login_info['last_login_city']=$ip_city['province'].$ip_city['city'];
//                }
//            }
//        }
        $my_dao=new my_dao();
        $unread_msg_count=$my_dao->get_unread_msg_count($_SESSION['user_id']);
        $result = $this->get_http_host();
        $domain_name = $result['domain_name'];
        $this->assign('domain_name',$domain_name);
        $this->assign("unread_msg",$unread_msg_count);
        //$this->assign("login_info",$login_info);
        $this->display("account/user_center.html");
    }

    public function modify_user(){
        if(!isset($_SESSION['user_id'])){
            $this->redirect("account.php?act=login");
            exit;
        }
        $user_info=$this->DAO->get_user_by_userid($_SESSION['user_id']);
        $this->assign("user_info",$user_info);
        $result = $this->get_http_host();
        $domain_name = $result['domain_name'];
        $this->assign('domain_name',$domain_name);
        $this->display("account/modify_user.html");
    }


    public function logout(){
        unset($_SESSION['user_id']);
        unset($_SESSION['nick_name']);
        unset($_SESSION['mobile']);
        unset($_SESSION['sex']);
        unset($_SESSION['email']);
        unset($_SESSION['birthday']);
        unset($_SESSION['qq_openid']);
        $this->redirect("");
    }

    public function forget(){
        $this->display("account/forget.html");
        unset($_SESSION['f_mobile']);
        unset($_SESSION['f_error']);
    }


    public function  register($type=''){
        $games = $this->COMDAO->get_hot_games();
        if ($type == '66173test')
            $this->assign("type","1");
        $result = $this->get_http_host();
        $domain_name = $result['domain_name'];
        $this->assign('domain_name',$domain_name);
        $this->assign("games", $games);
        $this->display("account/register.html");
        unset($_SESSION['m_error']);
        unset($_SESSION['code_error']);
        unset($_SESSION['p_error']);
        unset($_SESSION['cp_error']);
        unset($_SESSION['mobile']);
    }


    public function do_register(){
        $url = $_SESSION['login_back_url']?:'account.php?act=check_IDcard_view';
        $source='http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
        $error=false;
        $_SESSION['mobile']=$_POST['mobile'];
        if(empty($_POST['verify_code']) || strtoupper($_POST['verify_code'])!= $_SESSION['c']){
            $_SESSION['vcode_error'] = '图形码错误';
            $error=true;
        }


        if(!$this->is_mobile($_POST['mobile'])){
            $_SESSION['m_error'] = '手机号码格式不正确';
            $error=true;
        }

        if(!$error && !isset($_SESSION['reg_core']) ||
            $_SESSION['reg_core']!=$_POST['mobile']."_".$_POST['sms_code'] ||
            $_POST['sms_code']==""){
            $_SESSION['code_error'] = '验证码错误';
            $error=true;
        }

        if(!$error && strtotime("now")-$_SESSION['last_send_time']>300){
            $_SESSION['code_error'] = '验证码超时';
            $error=true;
        }
        if(!$error && strlen($_POST['password'])<6 || strlen($_POST['password'])>18){
            $_SESSION['p_error'] = '密码长度6-18位之间';
            $error=true;
        }
        if(!$error && $_POST['cpassword']!=$_POST['password']){
            $_SESSION['cp_error'] = '两次密码不一致';
            $error=true;
        }
        if(!$error){
            $user_info=$this->DAO->get_user_by_mobile($_POST['mobile']);
            if($user_info){
                if($user_info['mobile']==$_POST['mobile']){
                    $_SESSION['m_error'] = '手机号码已被注册～';
                    $error=true;
                }
            }
        }

        if($error){
            $this->register();
            unset( $_SESSION['m_error']);
            unset( $_SESSION['code_error']);
            unset( $_SESSION['p_error']);
            unset( $_SESSION['cp_error']);
            unset( $_SESSION['vcode_error']);
        }else{
            $usr_id = $this->DAO->insert_user_info(md5($_POST['password']),$_POST['mobile'],$this->client_ip());
            $this->DAO->insert_login_log($usr_id,$_POST['mobile'],$this->client_ip(),md5($_POST['password']),$source,$_SERVER['HTTP_USER_AGENT'],"注册成功","1");
            sleep(1);
            //$user_info = $this->DAO->get_user_by_userid($usr_id);
            $_SESSION['user_id'] = $usr_id;
            $_SESSION['nick_name'] = "";
            $_SESSION['mobile'] = $_POST['mobile'];
            $_SESSION['sex'] = 0;
            $_SESSION['email'] = "";
            $_SESSION['birthday'] = "";
            $_SESSION['buy_mobile'] = $_POST['mobile'];
            $_SESSION['buy_qq']= null;
            // $this->redirect($url);
            $this->redirect('account.php?act=check_IDcard_view'); // 暂时全部注册后都跳到实名认证页面
        }
    }

    public function add_user($num,$key){
        if(empty($num) || empty($key)){
            die("参数异常!!!");
        }
        if($key !='nnwl112233'){
            die("密码出错!!!");
        }
        for($i=1; $i<=$num; $i++) {
            $usr_id = $this->DAO->insert_guest_user_info(md5($key),$this->client_ip());
//            sleep(1);
            $usr_name='n'.$usr_id;
            $this->DAO->update_guest_usr_info($usr_name,$usr_id);
        }
        die('添加完成'.time());
    }


    public function do_qqlogin_phone_bind(){
        $error=false;
        if(empty($_SESSION['user_id'])){
            $this->redirect("account.php?act=login");
            exit;
        }

        if(!$error && strlen($_POST['password'])<6 || strlen($_POST['password'])>18){
            $_SESSION['modify_phone_set_error'] = '密码长度6-18位之间';
            $error=true;
        }
        if(!$error && $_POST['cpassword']!=$_POST['password']){
            $_SESSION['modify_phone_set_error'] = '两次密码不一致';
            $error=true;
        }

        if(!$error && !$this->is_mobile($_POST['mobile'])){
            $_SESSION['modify_phone_error'] = '手机号码格式不正确';
            $error=true;
        }


        $other_user=$this->DAO->get_user_by_mobile($_POST['mobile']);
        if(!$error && $other_user && $other_user['user_id']!=$_SESSION['user_id']){
            $_SESSION['modify_phone_error'] = '手机号码已经被注册';
            $error=true;
        }

        if(!$error && (!isset($_SESSION['reg_core']) ||
                $_SESSION['reg_core']!=$_POST['mobile']."_".$_POST['sms_code'] ||
                $_POST['sms_code']=="")){
            $_SESSION['modify_phone_code_error'] = '验证码错误';
            $error=true;
        }

        if(!$error && strtotime("now")-$_SESSION['last_send_time']>300){
            $_SESSION['modify_phone_code_error'] = '验证码超时';
            $error=true;
        }

        if(!$error){
            $this->DAO->update_user_phone($_POST['mobile'],$_SESSION['user_id']);//绑定号码
            $this->DAO->update_user_psw(md5($_POST['password']),$_POST['mobile'],$_SESSION['user_id'],$this->create_guid());//设置密码
            $this->DAO->insert_operation_log($_SESSION['user_id'],"5",1,"手机由“”变更为“".$_POST['mobile']."”");
            $this->DAO->insert_operation_log($_SESSION['user_id'],"1",1,"密码由“”变更为“".md5($_POST['password'])."”");
            $_SESSION['mobile']=$_POST['mobile'];
            $this->modify_phone("1");
        }else{
            if(isset($_SESSION['modify_phone_set_error'])){
                $error_des=$_SESSION['modify_phone_set_error'];
            }else if (isset($_SESSION['modify_phone_error'])){
                $error_des=$_SESSION['modify_phone_error'];
            }else{
                $error_des=$_SESSION['modify_phone_code_error'];
            }
            $this->DAO->insert_operation_log($_SESSION['user_id'],"5",0,$error_des);
            $this->modify_phone("2");
            unset($_SESSION['modify_phone_set_error']);
            unset($_SESSION['modify_phone_error']);
            unset($_SESSION['modify_phone_code_error']);
        }
    }

    public function do_forget(){
        $error=false;
        $_SESSION['f_mobile']=$_POST['mobile'];
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
            $_SESSION['f_error'] = '验证码错误';
            $error=true;
        }

        if(!$error && strtotime("now")-$_SESSION['last_send_time']>300){
            $_SESSION['f_error'] = '验证码超时';
            $error=true;
        }

        if(!$error){
            $user_info=$this->DAO->get_user_by_mobile($_POST['mobile']);
            if(!$user_info){
                $_SESSION['f_error'] = '手机号码不存在～';
                $error=true;
            }
        }
        if(!$error && strlen($_POST['password'])<6 || strlen($_POST['password'])>18){
            $_SESSION['f_error'] = '密码长度6-18位之间';
            $error=true;
        }
        if(!$error && $_POST['cpassword']!=$_POST['password']){
            $_SESSION['f_error'] = '两次密码不一致';
            $error=true;
        }

        if($error){
            if($user_info){
                $this->DAO->insert_operation_log($user_info['user_id'],"2",0,$_SESSION['f_error']);
            }
            $this->forget();
            unset($_SESSION['f_error']);
        }else{
            $this->DAO->update_user_psw(md5($_POST['password']),$_POST['mobile'],$user_info['user_id'],$this->create_guid());
            $this->DAO->insert_operation_log($user_info['user_id'],"2",1,"密码由“".$user_info['password']."”变更为“".md5($_POST['password'])."”");
            $this->redirect("account.php?act=login");
        }
    }


    public function modify_psw(){
        $result = $this->get_http_host();
        $domain_name = $result['domain_name'];
        $this->assign('domain_name',$domain_name);
        $this->display("account/modify_psw.html");
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
        $this->display("account/modify_phone.html");
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
        $this->display("account/modify_email.html");
    }

    public function do_modify_psw(){
        $error=false;
        if(empty($_SESSION['user_id'])){
            $this->redirect("account.php?act=login");
            exit;
        }
        if($_SESSION['qq_openid']){exit;}
        if(!trim($_POST['opassword'])){
            $_SESSION['modify_psw_op_error'] = '旧密码不能为空';
            $error=true;
        }

        if(!$error && !trim($_POST['password'])){
            $_SESSION['modify_psw_p_error'] = '新密码不能为空';
            $error=true;
        }

        if(!$error && strlen($_POST['password']) < 6 || strlen($_POST['password']) > 18){
            $_SESSION['modify_psw_p_error'] = '密码为字母和数字，支持大小写，长度6-18个字符';
            $error=true;
        }

        if(!$error && trim($_POST['password']) != trim($_POST['cpassword'])){
            $_SESSION['modify_psw_cp_error'] = '两次密码不一致';
            $error=true;
        }

        if(!$error && $_POST['opassword'] == $_POST['password']){
            $_SESSION['modify_psw_p_error'] = '密码没有发生变化';
            $error=true;
        }

        $usr_info = $this->DAO->get_user_by_userid($_SESSION['user_id']);
        if(!$error && md5($_POST['opassword']) != $usr_info['password']){
            $_SESSION['modify_psw_op_error'] = '旧密码错误';
            $error=true;
        }

        if(!$error){
            $this->DAO->update_user_psw(md5($_POST['password']),$usr_info['mobile'],$_SESSION['user_id'],$this->create_guid());
            $this->DAO->insert_operation_log($_SESSION['user_id'],"1",1,"密码由“".$usr_info['password']."”变更为“".md5($_POST['password'])."”");
            $this->redirect("account.php?act=login");
        }else{
            if(isset($_SESSION['modify_psw_op_error'])){
                $error_des=$_SESSION['modify_psw_op_error'];
            }else if (isset($_SESSION['modify_psw_p_error'])){
                $error_des=$_SESSION['modify_psw_p_error'];
            }else{
                $error_des=$_SESSION['modify_psw_cp_error'];
            }
            $this->DAO->insert_operation_log($_SESSION['user_id'],"1",0,$error_des);
            $this->modify_psw();
            unset($_SESSION['modify_psw_op_error']);
            unset($_SESSION['modify_psw_p_error']);
            unset($_SESSION['modify_psw_cp_error']);
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
            $_SESSION['modify_phone_p_error'] = '验证密码错误';
            $error=true;
        }

        if(!$error && !$this->is_mobile($_POST['mobile'])){
            $_SESSION['modify_phone_error'] = '手机号码格式不正确';
            $error=true;
        }
        if(!$error && $_POST['mobile']== $usr_info['moblie']){
            $_SESSION['modify_phone_error'] = '手机号码没有发生变化';
            $error=true;
        }

        $other_user=$this->DAO->get_user_by_mobile($_POST['mobile']);
        if(!$error && $other_user && $other_user['user_id']!=$_SESSION['user_id']){
            $_SESSION['modify_phone_error'] = '手机号码已经被注册';
            $error=true;
        }

        if(!$error && (!isset($_SESSION['reg_core']) ||
                $_SESSION['reg_core']!=$_POST['mobile']."_".$_POST['sms_code'] ||
                $_POST['sms_code']=="")){
            $_SESSION['modify_phone_code_error'] = '验证码错误';
            $error=true;
        }

        if(!$error && strtotime("now")-$_SESSION['last_send_time']>300){
            $_SESSION['modify_phone_code_error'] = '验证码超时';
            $error=true;
        }

        if(!$error){
            $this->DAO->update_user_phone($_POST['mobile'],$_SESSION['user_id']);
            $this->DAO->insert_operation_log($_SESSION['user_id'],"5",1,"手机由“".$usr_info['mobile']."”变更为“".$_POST['mobile']."”");
            $_SESSION['mobile']=$_POST['mobile'];
            $this->modify_phone("1");
        }else{
            if(isset($_SESSION['modify_phone_p_error'])){
                $error_des=$_SESSION['modify_phone_p_error'];
            }else if (isset($_SESSION['modify_phone_error'])){
                $error_des=$_SESSION['modify_phone_error'];
            }else{
                $error_des=$_SESSION['modify_phone_code_error'];
            }
            $this->DAO->insert_operation_log($_SESSION['user_id'],"5",0,$error_des);
            $this->modify_phone("2");
            unset($_SESSION['modify_phone_p_error']);
            unset($_SESSION['modify_phone_error']);
            unset($_SESSION['modify_phone_code_error']);
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
            $_SESSION['modify_email_p_error'] = '验证密码错误';
            $error=true;
        }

        if(!$error && !$this->is_email($_POST['email'])){
            $_SESSION['modify_phone_error'] = '邮箱格式不正确';
            $error=true;
        }
        if(!$error && $_POST['email']== $usr_info['email']){
            $_SESSION['modify_email_error'] = '邮箱没有发生变化';
            $error=true;
        }

        $other_user=$this->DAO->get_user_by_email($_POST['email']);
        if(!$error && $other_user && $other_user['user_id']!=$_SESSION['user_id']){
            $_SESSION['modify_email_error'] = '邮箱已经被注册';
            $error=true;
        }

        if(!$error && (!isset($_SESSION['email_core']) ||
                $_SESSION['email_core']!=$_POST['email']."_".$_POST['sms_code'] ||
                $_POST['sms_code']=="")){
            $_SESSION['modify_email_code_error'] = '验证码错误';
            $error=true;
        }

        if(!$error && strtotime("now")-$_SESSION['last_send_em_time']>300){
            $_SESSION['modify_email_code_error'] = '验证码超时';
            $error=true;
        }

        if(!$error){
            $this->DAO->update_user_email($_POST['email'],$_SESSION['user_id']);
            $this->DAO->insert_operation_log($_SESSION['user_id'],"6",1,"邮箱由“".$usr_info['email']."”变更为“".$_POST['email']."”");
            $_SESSION['email']=$_POST['email'];
            $this->modify_email("1");
        }else{
            if(isset($_SESSION['modify_email_p_error'])){
                $error_des=$_SESSION['modify_email_p_error'];
            }else if (isset($_SESSION['modify_email_error'])){
                $error_des=$_SESSION['modify_email_error'];
            }else{
                $error_des=$_SESSION['modify_email_code_error'];
            }
            $this->DAO->insert_operation_log($_SESSION['user_id'],"6",0,$error_des);
            $this->modify_email("2");
            unset($_SESSION['modify_email_p_error']);
            unset($_SESSION['modify_email_error']);
            unset($_SESSION['modify_email_code_error']);
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
            $_SESSION['modify_user_error']="昵称不能为空";
            $error=true;
        }
        if(!$error && strlen(trim($usr['nick_name'])) < 4 && !preg_match("/^[\x{4e00}-\x{9fa5}\w]{2,20}$/u", $usr['nick_name'])){
            $_SESSION['modify_user_error']="昵称不合法";
            $error=true;
        }
        if(!$error && $usr['nick_name'] != $_SESSION['nick_name'] && $this->DAO->get_user_by_nickname($usr['nick_name'],$_SESSION['user_id'])){
            $_SESSION['modify_user_error']="昵称已经被人使用啦";
            $error=true;
        }
        if(!$error && $usr['birthday']==""){
            $_SESSION['modify_user_error']="请选择生日";
            $error=true;
        }
        if(!$error && $usr['sex']==""){
            $_SESSION['modify_user_error']="请选择性别";
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
        unset($_SESSION['modify_user_error']);
    }

    //增加图形验证码
    public function register_verify_code(){
        $v_code = $_POST['v_code'];
        if(strtoupper($v_code)!=$_SESSION['c']){
            $msg['res']="0";//1成功0失败
            $msg['msg']="图形验证码错误";
            die(json_encode($msg));
        }else{
            $msg['res']="1";//1成功0失败
            $msg['msg']="ok";
            die(json_encode($msg));
        }
    }
    /**
     * 发送语音验证码
     */

    public function register_voice(){
        $phone = $_POST['mobile'];
        $v_code = $_POST['v_code'];
        if(strtoupper($v_code)!=$_SESSION['c']){
            $msg['res']="0";//1成功0失败
            $msg['msg']="图形验证码错误";
            die(json_encode($msg));}
        if(!$phone){
            $msg['res']="0";//1成功0失败
            $msg['msg']="请输入您的手机号";
            die(json_encode($msg));
        }
        if (!preg_match("/^13[0-9]{9}$|14[0-9]{9}|15[0-9]{9}$|18[0-9]{9}$|17[0-9]{9}$/",$phone)){
            $msg['res']="0";//1成功0失败
            $msg['msg']="请输入正确手机号";
            die(json_encode($msg));
        }
        if(!$this->is_mobile($phone)){
            $msg['res']="0";//1成功0失败
            $msg['msg']="验证消息发送失败,请重试";
            die(json_encode($msg));
        }

        $nowtime=strtotime("now");
        if(isset($_SESSION['last_send_time'])){
            if($nowtime-$_SESSION['last_send_time']<180){
                $msg['res']="0";//1成功0失败
                $msg['msg']="获取验证码太频繁，请稍后再试";
                die(json_encode($msg));
            }else{
                $_SESSION['last_send_time']=$nowtime;
            }
        }else{
            $_SESSION['last_send_time']=$nowtime;
        }
        $code = str_split($_SERVER["REQUEST_TIME"] .rand(1001, 9999));
        $code = substr(implode('', array_rand($code, 6)), 0, 6);
        $send_result = $this->send_voice($phone,$code);
        $this->err_log(var_export($send_result,1),'voice');
        if($send_result){
            $_SESSION['reg_core']=$phone."_".$code;
            $msg['res']="1";//1成功0失败
            $msg['msg']="语音验证码发送成功！";
            die(json_encode($msg));
        }else{
            $msg['res']="0";//1成功0失败
            $msg['msg']="语音验证码发送失败，请稍后再试！";
            echo json_encode($msg);
        }
    }
    /**
     * 发送验证码短信
     */
    public function register_sms(){
        $phone = $_POST['mobile'];
        $v_code = $_POST['v_code'];
       if(strtoupper($v_code)!=$_SESSION['c']){
            $msg['res']="0";//1成功0失败
            $msg['msg']="图形验证码错误";
            die(json_encode($msg));}
        if(!$phone){
            $msg['res']="0";//1成功0失败
            $msg['msg']="请输入您的手机号";
            die(json_encode($msg));
        }
        if (!preg_match("/^13[0-9]{9}$|14[0-9]{9}|15[0-9]{9}$|18[0-9]{9}$|17[0-9]{9}$/",$phone)){
            $msg['res']="0";//1成功0失败
            $msg['msg']="请输入正确手机号";
            die(json_encode($msg));
        }
        if(!$this->is_mobile($phone)){
            $msg['res']="0";//1成功0失败
            $msg['msg']="验证消息发送失败,请重试";
            die(json_encode($msg));
        }

        $nowtime=strtotime("now");
        if(isset($_SESSION['last_send_time'])){
            if($nowtime-$_SESSION['last_send_time']<180){
                $msg['res']="0";//1成功0失败
                $msg['msg']="获取验证码太频繁，请稍后再试";
                die(json_encode($msg));
            }else{
                $_SESSION['last_send_time']=$nowtime;
            }
        }else{
            $_SESSION['last_send_time']=$nowtime;
        }

        $code = str_split($_SERVER["REQUEST_TIME"] .rand(1001, 9999));
        $code = substr(implode('', array_rand($code, 6)), 0, 6);
//        $send_result = $this->request('http://is.go.cc/api/sms/sendsms',
//            'appid=8a48b5514b0b8727014b241da79c11e1&mobile=' . $phone . '&templateid='.$this->SMS_TYPE['MOBILE_BIND_VERIFY_CODE'].'&data='.json_encode(array($code)));
//        $send_result = json_decode(base64_decode(substr($send_result, 1)));
//        $send_result = $this->send_sms($phone,array($code),"23267");
        $send_result = $this->bm_send_sms($phone,array($code));
        $this->err_log(var_export($send_result,1),'sms');
        if($send_result){
            $_SESSION['reg_core']=$phone."_".$code;
            $msg['res']="1";//1成功0失败
            $msg['msg']="验证消息发送成功！";
            die(json_encode($msg));
        }else{
            $msg['res']="0";//1成功0失败
            $msg['msg']="验证消息发送失败,请重试2";
            echo json_encode($msg);
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
        echo json_encode($msg);
    }

    public function qq_login_view(){
        $config['appid'] = QQ_APP_ID;
        $config['appkey'] = QQ_APP_KEY;
        $config['callback'] = "http://www.66173.cn/qq_callback.php";
        $o_qq = new oauth_qq($config);
        $o_qq->login();
    }

    public function qq_do_login(){
        $config['appid'] = QQ_APP_ID;
        $config['appkey'] = QQ_APP_KEY;
        $config['callback'] = "http://www.66173.cn/qq_callback.php";
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
            $usr_info = array("user_id"=>$usr_id,"nick_name"=>"","mobile"=>"","sex"=>0,"email"=>"","birthday"=>"","buy_mobile"=>"","qq"=>null);
            //$usr_info = $this->DAO->get_user_by_userid($usr_id);
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
        $_SESSION['buy_qq']= $usr_info['qq'];
        if(isset($_SESSION['login_back_url']) && !empty($_SESSION['login_back_url'])){
            $this->redirect($_SESSION['login_back_url']);
        }else{
            $this->redirect("account.php?act=user_center");
        }
    }

    // 实名认证 <zbc> <2016-5-17>
    public function check_IDcard_view(){
        if(!isset($_SESSION['user_id'])){
            $this->redirect("account.php?act=login");
            exit;
        }
        $user_info=$this->DAO->get_user_by_userid($_SESSION['user_id']);
        if($user_info['id_number'] && $user_info['user_name']){
            $is_checked = 1;
            $idcard['id_number'] =  mb_substr($user_info['id_number'], 0, 3, 'utf-8').'**********';
            $idcard['user_name'] =  mb_substr($user_info['user_name'], 0, 1, 'utf-8').'**';
        }
        $result = $this->get_http_host();
        $domain_name = $result['domain_name'];
        $this->assign('domain_name',$domain_name);
        $this->assign('is_checked',$is_checked);
        $this->assign('idcard',$idcard);
        $this->display("account/check_IDcard.html");
    }
    public function check_IDcard_do($id, $name){
        if(!isset($_SESSION['user_id'])){
            $this->redirect("account.php?act=login");
            exit;
        }
        $bak['status'] = 'ok';
        if(!preg_match("/^[\x{4e00}-\x{9fa5}]{2,15}$/u",$name)){
            $bak['err']['msg'] = '您输入的姓名无效！';
            $bak['err']['obj'] = 'name';
            $bak['status'] = 'err';
        } 
        if(!preg_match("/^[1-9]\d{7}((0\d)|(1[0-2]))(([0|1|2]\d)|3[0-1])\d{3}$|^[1-9]\d{5}[1-9]\d{3}((0\d)|(1[0-2]))(([0|1|2]\d)|3[0-1])\d{3}([0-9]|X)$/",$id)){
            $bak['err']['msg'] = '您输入的身份证号码无效1！';
            $bak['err']['obj'] = 'id';
            $bak['status'] = 'err';
        }
        if($bak['status'] == 'ok'){
            $this->DAO->update_user_idcard($id, $name, $_SESSION['user_id']);
        }
        die(json_encode($bak));
    }








}