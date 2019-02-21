<?php
COMMON('baseCore');
DAO("account_dao");
class account_admin extends baseCore {
    public $DAO;
    public function __construct(){
        parent::__construct();
        $this->DAO = new account_dao();
    }

    public function user_center(){
        if(!isset($_SESSION['user_id'])){
            $this->redirect("account.php?act=login");
            exit;
        }
        $info = $this->DAO->get_user_by_userid($_SESSION['user_id']);
        $this->assign('info',$info);
        $this->display('account/user_center.html');
    }

    public function register(){
        $this->page_hash();
        $this->display('account/register.html');
    }

    public function sms_code(){
        $result = array('code'=>0,'msg'=>'网络错误');
        if(!$_POST['pagehash'] || $_POST['pagehash'] != $_SESSION['page-hash']){
            $result['msg'] = "发生错误，错误代码E001,请重新刷新页面。";
            die(json_encode($result));
        }
        $today = date('Y-m-d',time());
        $phone = $_POST['mobile'];
        if(!$this->is_mobile($phone)){
            $result['msg'] = "手机号码格式错误,请重试";
            die(json_encode($result));
        }
        $nowtime = time();
        if(isset($_SESSION['last_send_time'])){
            if($nowtime-$_SESSION['last_send_time']<12){
                $result['msg'] = "获取验证码太频繁，请稍后再试";
                die(json_encode($result));
            }else{
                $_SESSION['last_send_time'] = $nowtime;
            }
        }else{
            $_SESSION['last_send_time'] = $nowtime;
        }
        if(isset($_POST['type'])){
            $user_info = $this->DAO->get_user_by_mobile($phone);
            if(!$user_info){
                $result['type'] = 1;
                $result['msg'] = '该手机号未注册或未绑定';
                die(json_encode($result));
            }
        }
        $this->send_verify($this->client_ip(),$phone,$today);
        $code = str_split($_SERVER["REQUEST_TIME"] .rand(1001, 9999));
        $code = substr(implode('', array_rand($code, 6)), 0, 6);
//        $send_result = $this->send_sms($phone,array($code),"201723");
        $send_result = $this->bm_send_sms($phone,array($code),2);
        $this->err_log(var_export($send_result,1),'niuguo_sms');
        if($send_result){
            $this->DAO->add_send_sms_log($this->client_ip(),$phone,$code,$today,$_SERVER['HTTP_REFERER'],1);
            $_SESSION['reg_core'] = $phone."_".$code;
            $result['code'] = "1";//1成功0失败
            $result['msg'] = "验证消息发送成功！";
//            $result['sms_code'] = $code;
            die(json_encode($result));
        }else{
            $this->DAO->add_send_sms_log($this->client_ip(),$phone,$code,$today,$_SERVER['HTTP_REFERER'],0);
            $result['msg'] = "验证消息发送失败,请重试";
            echo json_encode($result);
        }
    }

    public function do_register(){
        $result = array('code'=>0,'msg'=>"服务器升级，暂时不能注册",'type'=>0);
        die(json_encode($result));
        $_SESSION['mobile'] = $_POST['mobile'];
        if(empty($_POST['verify_code']) || strtoupper($_POST['verify_code']) != $_SESSION['c']){
            $result['type'] = 4;
            $result['msg'] = '图形码错误';
            die(json_encode($result));
        }
        if(!$this->is_mobile($_POST['mobile'])){
            $result['type'] = 1;
            $result['msg'] = '手机号码格式不正确';
            die(json_encode($result));
        }
        if(!isset($_SESSION['reg_core']) ||
            $_SESSION['reg_core'] != $_POST['mobile']."_".$_POST['sms_code'] ||
            $_POST['sms_code']==""){
            $result['type'] = 2;
            $result['msg'] = '验证码错误';
            die(json_encode($result));
        }
        if(time()-$_SESSION['last_send_time']>300){
            $result['type'] = 2;
            $result['msg'] = '验证码超时';
            die(json_encode($result));
        }
        if(strlen($_POST['password'])<6 || strlen($_POST['password'])>18){
            $result['type'] = 3;
            $result['msg'] = '密码长度6-18位之间';
            die(json_encode($result));
        }
        if(!$_POST['agree']){
            $result['msg'] = '您还未同意协议';
            die(json_encode($result));
        }
        $user_info = $this->DAO->get_user_by_mobile($_POST['mobile']);
        if($user_info){
            if($user_info['mobile'] == $_POST['mobile']){
                $result['type'] = 1;
                $result['msg'] = '手机号码已被注册';
                die(json_encode($result));
            }
        }
        $usr_id = $this->DAO->insert_user_info(md5($_POST['password']),$_POST['mobile'],$this->client_ip());
        $usr_info = $this->DAO->get_user_by_userid($usr_id);
        $_SESSION['user_id'] = $usr_info['user_id'];
        $_SESSION['nick_name'] = $usr_info['nick_name'];
        $_SESSION['mobile'] = $usr_info['mobile'];
        $_SESSION['buy_mobile'] = $usr_info['buy_mobile'];
        $_SESSION['is_agent'] = 0;
        $_SESSION['user_type'] = $usr_info['user_type'];
        $result['code'] = 1;
        $result['msg'] = '注册成功';
        die(json_encode($result));
    }

    public function send_verify($ip,$phone,$today){
        $ip_count = $this->DAO->get_ip_count($ip,$today);
        if($ip_count['num'] >= 5){
            $result['code'] = "0";//1成功0失败
            $result['msg'] = "本日该手机号接受短信已上限!!!";
            die(json_encode($result));
        }
        $phone_count = $this->DAO->get_phone_count($phone,$today);
        if($phone_count['num'] >= 5){
            $result['code'] = "0";//1成功0失败
            $result['msg'] = "本日该手机号接受短信已上限";
            die(json_encode($result));
        }
    }

    public function is_mobile($str){
        return preg_match('/^13[0-9]{1}[0-9]{8}$|15[012356789]{1}[0-9]{8}$|18[0-9]{9}$|14[57]{1}[0-9]{8}$|17[0678]{1}[0-9]{8}$/', $str) == 1 ? true : false;
    }

    public function login(){
        if(isset($_SESSION['user_id'])){
            $this->redirect("account.php?act=user_center");
            exit;
        }
        $this->page_hash();
        $this->assign('login_num',$_SESSION['login_num']);
        $this->display("account/login.html");
    }

    public function do_login(){
        $result = array('code'=>0,'type'=>0,'msg'=>'网络出错');
        if(isset($_SESSION['user_id'])){
            $this->redirect("account.php?act=user_center");
            exit;
        }
        $params = $_POST;
        $moblie = $_POST['mobile'];
        $pwd = $_POST['password'];
        $source = 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
        if(!isset($moblie) || strlen($moblie) == 0){
            $this->DAO->insert_login_log("",$moblie,$this->client_ip(),md5($pwd),$source,$_SERVER['HTTP_USER_AGENT'],"登录账号为空","0");
            $_SESSION['login_num'] = $_SESSION['login_num']+1;
            $result['type'] = 1;
            $result['count'] = $_SESSION['login_num'];
            $result['msg'] = '登录账号不能为空';
            die(json_encode($result));
        }
        if(!isset($pwd) || strlen($pwd) < 6 || strlen($pwd) > 18 ){
            $this->DAO->insert_login_log("",$moblie,$this->client_ip(),md5($pwd),$source,$_SERVER['HTTP_USER_AGENT'],"密码长度不正确","0");
            $_SESSION['login_num'] = $_SESSION['login_num']+1;
            $result['type'] = 2;
            $result['count'] = $_SESSION['login_num'];
            $result['msg'] = '密码长度不正确';
            die(json_encode($result));
        }
        $usr_info = $this->DAO->get_user_by_mobile($moblie);
        if(!$usr_info){
            $this->DAO->insert_login_log("",$moblie,$this->client_ip(),md5($pwd),$source,$_SERVER['HTTP_USER_AGENT'],"手机用户不存在","0");
            $_SESSION['login_num'] = $_SESSION['login_num']+1;
            $result['type'] = 1;
            $result['count'] = $_SESSION['login_num'];
            $result['msg'] = '手机用户不存在';
            die(json_encode($result));
        }
        if($params['count'] > 3 || $_SESSION['login_num'] > 3){
            if(empty($params['verifyCode']) || strtoupper($params['verifyCode'])!= $_SESSION['c']){
                $result['type'] = 3;
                $_SESSION['login_num'] = $_SESSION['login_num']+1;
                $result['msg'] = '图形验证码不正确';
                die(json_encode($result));
            }
        }
        if(md5($pwd) != $usr_info['password']){
            $this->DAO->insert_login_log($usr_info['user_id'],$moblie,$this->client_ip(),md5($pwd),$source,$_SERVER['HTTP_USER_AGENT'],"登录密码错误","0");
            $_SESSION['login_num'] = $_SESSION['login_num']+1;
            $result['type'] = 2;
            $result['count'] = $_SESSION['login_num'];
            $result['msg'] = '登录密码错误';
            die(json_encode($result));
        }else{
            $this->DAO->insert_login_log($usr_info['user_id'],$moblie,$this->client_ip(),md5($pwd),$source,$_SERVER['HTTP_USER_AGENT'],"登录成功","1");
            unset($_SESSION['do_login_error']);
            $_SESSION['user_id'] = $usr_info['user_id'];
            $_SESSION['nick_name'] = $usr_info['nick_name'];
            $_SESSION['mobile'] =  $usr_info['mobile'];
            $_SESSION['sex'] = $usr_info['sex'];
            $_SESSION['email'] = $usr_info['email'];
            $_SESSION['birthday'] = $usr_info['birthday'];
            $_SESSION['buy_mobile'] = $usr_info['buy_mobile'];
            $_SESSION['buy_qq'] = $usr_info['qq'];
            $_SESSION['guid'] = $usr_info['guid'];
            $_SESSION['is_agent'] = $usr_info['is_agent'];
            //记住登陆状态
            if(isset($_POST['cookie_check']) && $_POST['cookie_check']=1){
                $_SESSION['cookie_check'] = 1;
            }
            unset($_SESSION['login_num']);
            $result['code'] = 1;
            $result['msg'] = '登录成功';
            die(json_encode($result));
        }
    }

    public function password_forget(){
        $this->page_hash();
        $this->display("account/password_forget.html");
    }

    public function verify_mobile(){
        $result = array('code'=>0,'msg'=>'网络错误');
        $params = $_POST;
        if(!$params['pagehash'] || $params['pagehash']!=$_SESSION['page-hash']){
            $result['msg'] = "发生错误，错误代码E001,请重新刷新页面。";
            die(json_encode($result));
        }
        if(!$this->is_mobile($params['mobile'])){
            $result['type'] = 1;
            $result['msg'] = "手机号码格式错误，请重新输入";
            die(json_encode($result));
        }
        if(!isset($_SESSION['reg_core']) ||
            $_SESSION['reg_core'] != $params['mobile']."_".$params['sms_code'] ||
            $params['sms_code']==""){
            $result['type'] = 2;
            $result['msg'] = '验证码错误';
            die(json_encode($result));
        }
        if(strtotime("now")-$_SESSION['last_send_time']>300){
            $result['type'] = 2;
            $result['msg'] = '验证码超时';
            die(json_encode($result));
        }
        $user_info = $this->DAO->get_user_by_mobile($params['mobile']);
        if(!$user_info){
            if($user_info['mobile'] != $params['mobile']){
                $result['type'] = 1;
                $result['msg'] = '手机号码不存在';
                die(json_encode($result));
            }
        }
        $result['msg'] = $params['desc'] = "手机验证成功";
        $result['code'] = $params['type'] = 1;
        $this->DAO->insert_modify($params,$user_info['user_id'],$this->client_ip());
        $_SESSION['verify'] = md5(rand(1000,9999));
        $result['verify'] =  $_SESSION['verify'];
        $result['mobile'] = $user_info['mobile'];
        die(json_encode($result));

    }

    public function set_password(){
        die("服务器升级，暂时不能更改密码。");
        $result = array('code'=>0,'msg'=>'网络错误');
        $params = $_POST;
        if(!$params['pagehash'] || $params['pagehash'] != $_SESSION['page-hash']){
            $result['msg']="发生错误，错误代码E001,请重刷页面。";
            die(json_encode($result));
        }
        if(!$params['verify'] || $params['verify'] != $_SESSION['verify']){
            $result['msg'] = "发生错误，请重刷页面";
            die(json_encode($result));
        }
        if(!$this->is_mobile($params['mobile'])){
            $result['msg'] = "手机号码错误，请重刷页面";
            die(json_encode($result));
        }
        $user_info = $this->DAO->get_user_by_mobile($params['mobile']);
        if(!$user_info){
            if($user_info['mobile'] != $params['mobile']){
                $result['msg'] = '手机号码不存在';
                die(json_encode($result));
            }
        }
        if(strlen($params['password'])<6 || strlen($params['password'])>18){
            $result['type'] = 1;
            $result['msg'] = '密码长度6-18位之间';
            die(json_encode($result));
        }
        if($params['cpassword'] != $params['password']){
            $result['type'] = 1;
            $result['msg'] = '两次密码不一致';
            die(json_encode($result));
        }
        $params['desc'] = "密码由“".$user_info['password']."”变更为“".md5($params['password'])."”";
        $params['type']  = 2;
        $this->DAO->insert_modify($params,$user_info['user_id'],$this->client_ip());
        $this->DAO->update_user_psw(md5($params['password']),$params['mobile'],$user_info['user_id'],$this->create_guid());
        $this->DAO->insert_operation_log($user_info['user_id'],"2",1,"密码由“".$user_info['password']."”变更为“".md5($params['password'])."”");
        $result['code'] = 1;
        $result['msg'] = '密码修改成功';
        unset( $_SESSION['user_id']);
        unset( $_SESSION['nick_name']);
        unset( $_SESSION['mobile']);
        unset($_SESSION['sex']);
        unset($_SESSION['email']);
        unset($_SESSION['birthday']);
        unset($_SESSION['qq_openid']);
        die(json_encode($result));
    }

    public function real_name_verify(){
        if(!isset($_SESSION['user_id'])){
            $this->redirect("account.php?act=login");
            exit;
        }
        $info = $this->DAO->get_user_by_userid($_SESSION['user_id']);
        if($info['id_number'] && $info['user_name']){
            $is_checked = 1;
            $idcard['id_number'] =  substr($info['id_number'],0,2).'*************'.substr($info['id_number'],-2);
            $idcard['user_name'] =  '**'.mb_substr($info['user_name'],-1,1,"UTF-8");
        }
        $this->assign('info',$info);
        $this->assign('is_checked',$is_checked);
        $this->assign('idcard',$idcard);
        $this->display('account/realname_verify.html');
    }

    public function do_real_verify(){
        die("服务器升级，暂时不能实名认证。");
        $result = array('code'=>'0','msg'=>'网络出错');
        if(!isset($_SESSION['user_id'])){
            $this->redirect("account.php?act=login");
            exit;
        }
        $params = $_POST;
        if(strpos($params['user_name'],'·') || strpos($params['user_name'],'•')){
            $smb = true;
        }else{
            $smb = false;
        }
        $preg = preg_match("/^[\x{4e00}-\x{9fa5}]{1,5}[·•][\x{4e00}-\x{9fa5}]{2,15}$/u",$params['user_name']);
        $preg1 = preg_match("/^[\x{4e00}-\x{9fa5}]{2,15}$/u",$params['user_name']);
        if(mb_strlen($params['user_name']) < 2 || ($smb && !$preg) || (!$smb && !$preg1)){
            $result['type'] = 1;
            $result['msg'] = '真实姓名错误';
            die(json_encode($result));
        }
        $idc = $this->is_idcard($params['id_number']);
        if(!$idc){
            $result['type'] = 2;
            $result['msg'] = '身份证号码错误';
            die(json_encode($result));
        }
        $this->DAO->update_user_info($params,$_SESSION['user_id']);
        $result['code'] = 1;
        $result['msg'] = '认证成功';
        die(json_encode($result));
    }

    function is_idcard($id){
        $id = strtoupper($id);
        $regx = "/(^\d{15}$)|(^\d{17}([0-9]|X)$)/";
        $arr_split = array();
        if(!preg_match($regx, $id)){
            return FALSE;
        }
        if(15==strlen($id)){     //检查15位
            $regx = "/^(\d{6})+(\d{2})+(\d{2})+(\d{2})+(\d{3})$/";
            @preg_match($regx, $id, $arr_split);
            //检查生日日期是否正确
            $dtm_birth = "19".$arr_split[2] . '/' . $arr_split[3]. '/' .$arr_split[4];
            if(!strtotime($dtm_birth)){
                return FALSE;
            }else{
                return TRUE;
            }
        }else{     //检查18位
            $regx = "/^(\d{6})+(\d{4})+(\d{2})+(\d{2})+(\d{3})([0-9]|X)$/";
            @preg_match($regx, $id, $arr_split);
            $dtm_birth = $arr_split[2] . '/' . $arr_split[3]. '/' .$arr_split[4];
            if(!strtotime($dtm_birth)){//检查生日日期是否正确
                return FALSE;
            }else{
                //检验18位身份证的校验码是否正确。
                //校验位按照ISO 7064:1983.MOD 11-2的规定生成，X可以认为是数字10。
                $arr_int = array(7, 9, 10, 5, 8, 4, 2, 1, 6, 3, 7, 9, 10, 5, 8, 4, 2);
                $arr_ch = array('1', '0', 'X', '9', '8', '7', '6', '5', '4', '3', '2');
                $sign = 0;
                for( $i = 0; $i < 17; $i++ ){
                    $b = (int) $id{$i};
                    $w = $arr_int[$i];
                    $sign += $b * $w;
                }
                $n  = $sign % 11;
                $val_num = $arr_ch[$n];
                if($val_num != substr($id,17, 1)){
                    return FALSE;
                }else{
                    return TRUE;
                }
            }
        }
    }

    public function do_user_info(){
        die("服务器升级，暂时不能更改用户信息。");
        $result = array('code'=>0,'msg'=>'网络错误','type'=>0);
        $params = $_POST;
        if(!isset($_SESSION['user_id'])){
            $this->redirect('account.php?act=login');
            exit;
        }
        if(!$params['nick_name']){
            $result['type'] = 1;
            $result['msg'] = '用户昵称不能为空';
            die(json_encode($result));
        }
        if(strlen(trim($params['nick_name'])) < 4 && !preg_match("/^[\x{4e00}-\x{9fa5}\w]{2,20}$/u", $params['nick_name'])){
            $result['type'] = 1;
            $result['msg'] = '用户昵称不合法';
            die(json_encode($result));
        }
        if($params['nick_name'] != $_SESSION['nick_name'] && $this->DAO->get_user_by_nickname($params['nick_name'],$_SESSION['user_id'])){
            $result['type'] = 1;
            $result['msg'] = '用户昵称已经被人使用啦';
            die(json_encode($result));
        }
        if(strtotime($params['birthday'])>strtotime(date('Y-m-d'))){
            $result['type'] = 2;
            $result['msg'] = '生日超过当前日期';
            die(json_encode($result));
        }
        $_SESSION['nick_name'] = $params['nick_name'];
        $this->DAO->update_user_msg($params,$_SESSION['user_id']);
        $result['code'] = 1;
        $result['msg'] = '更新成功';
        die(json_encode($result));
    }

    public function phone_bind(){
        if(!isset($_SESSION['user_id'])){
            $this->redirect("account.php?act=login");
            exit;
        }
        $info = $this->DAO->get_user_by_userid($_SESSION['user_id']);
        $this->assign('info',$info);
        $this->display('account/phone_bind.html');
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
}