<?php
COMMON('baseCore');
DAO('user_certification_dao');

class user_certification_mobile extends baseCore{
    public $DAO;

    public function __construct(){
        parent::__construct();
        $this->DAO = new user_certification_dao();
    }

    //实名认证
    public function user_certification(){
        if(!$_POST['pagehash'] || $_POST['pagehash']!=$_SESSION['page-hash']){
            $result['res']="0";//1成功0失败
            $result['msg']="发生错误，错误代码E001,请重新刷新页面。";
            die('0' . base64_encode(json_encode($result)));
        }
        $result              = array('code' => 0, 'msg' => '网络出错！');
        $params['user_id']   = $_SESSION['user_id'];
        $num = $this->DAO->get_user_certification($_SESSION['user_id'])['num'];
        if($_SESSION['user_id']==null){
            $result['msg'] = '请先登录';
            $result['url'] = '/Login';
            die('0' . base64_encode(json_encode($result)));
        }
        if($num){
            $result['msg'] = '已实名认证过';
            die('0' . base64_encode(json_encode($result)));
        }
        $params['real_name'] = $_POST['real_name'];
        $params['id_card']   = $_POST['id_card'];
        if($params['real_name']==null ||$params['id_card']==null){
            $result['msg'] = '缺少必要参数';
            die('0' . base64_encode(json_encode($result)));
        }
        $id                  = $this->DAO->insert_user_certification($params);
        if(!$id){
            $result['msg'] = '实名认证失败';
            die('0' . base64_encode(json_encode($result)));
        }
        $result['code'] = 1;
        $result['url'] = 'accountCenter';
        $result['msg']  = '实名认证成功';
        die('0' . base64_encode(json_encode($result)));

    }

    //设置支付密码
    public function set_pay_passward(){
        $_SESSION['login_back_url'] = ltrim($_SERVER['REQUEST_URI'],'/');
        $num = $this->DAO->get_user_detail($_SESSION['user_id'])['num'];
        if(!$num){
            $this->DAO->insert_user_detail($_SESSION['user_id']);
        }
        $result  = array('code' => 0, 'msg' => '网络出错！');
        if($_SESSION['user_id']==null){
            $result['msg'] = '请先登录';
            $result['code'] = 2;
            $result['url'] = 'Login';
            die('0' . base64_encode(json_encode($result)));
        }
        $account_info = $this->DAO->get_account_info($_SESSION['user_id']);
        if($account_info['pay_passward']){
            $result['msg'] = '已设置过密码';
            $result['url'] = 'accountCenter';
            die('0' . base64_encode(json_encode($result)));
        }
        if($_POST['pay_passward'] != $_POST['confirm_pay_passward']){
            $result['msg'] = '两次密码不同';
            die('0' . base64_encode(json_encode($result)));
        }
        if(!isset($_SESSION['reg_core']) ||
            $_SESSION['reg_core'] != $_POST['mobile'] . "_" . $_POST['sms_code'] ||
            $_POST['sms_code'] == ""){
            $_SESSION['m_reg_error'] = '验证码错误';
            $result['msg']           = '验证码错误';
            die('0' . base64_encode(json_encode($result)));
        }

        if(strtotime("now") - $_SESSION['last_send_time'] > 300){
            $_SESSION['m_reg_error'] = '验证码超时';
            $result['msg']           = '验证码超时';
            die('0' . base64_encode(json_encode($result)));
        }
        $this->DAO->update_user_pay_passward($_POST['pay_passward'], $_SESSION['user_id']);
        $pay_passward = $this->DAO->get_account_info($_SESSION['user_id'])['pay_passward'];
        if($pay_passward){
            $result['code'] = 1;
            $result['msg']  = '支付密码设置成功';
            $result['url'] = '/accountCenter';
            die('0' . base64_encode(json_encode($result)));
        }

    }
    //设置支付密码
    public function re_set_pay_passward(){
        $_SESSION['login_back_url'] = ltrim($_SERVER['REQUEST_URI'],'/');
        $result  = array('code' => 0, 'msg' => '网络出错！');
        if($_SESSION['user_id']==null){
            $result['msg'] = '请先登录';
            $result['code'] = 2;
            $result['url'] = 'Login';
            die('0' . base64_encode(json_encode($result)));
        }
        $num = $this->DAO->get_user_detail($_SESSION['user_id'])['num'];
        if(!$num){
            $this->DAO->insert_user_detail($_SESSION['user_id']);
        }
        if($_POST['pay_passward'] != $_POST['confirm_pay_passward']){
            $result['msg'] = '两次密码不同';
            die('0' . base64_encode(json_encode($result)));
        }
        if(!isset($_SESSION['reg_core']) ||
            $_SESSION['reg_core'] != $_POST['mobile'] . "_" . $_POST['sms_code'] ||
            $_POST['sms_code'] == ""){
            $_SESSION['m_reg_error'] = '验证码错误';
            $result['msg']           = '验证码错误';
            die('0' . base64_encode(json_encode($result)));
        }

        if(strtotime("now") - $_SESSION['last_send_time'] > 300){
            $_SESSION['m_reg_error'] = '验证码超时';
            $result['msg']           = '验证码超时';
            die('0' . base64_encode(json_encode($result)));
        }
        $this->DAO->update_user_pay_passward($_POST['pay_passward'], $_SESSION['user_id']);
        $pay_passward = $this->DAO->get_account_info($_SESSION['user_id'])['pay_passward'];
        if($pay_passward){
            $result['code'] = 1;
            $result['msg']  = '支付密码设置成功';
            $result['url'] = '/accountCenter';
            die('0' . base64_encode(json_encode($result)));
        }

    }
    //设置支付宝账号
    public function set_pay_account(){
        $_SESSION['login_back_url'] = ltrim($_SERVER['REQUEST_URI'],'/');
        $result  = array('code' => 0, 'msg' => '网络出错！');
        if($_SESSION['user_id']==null){
            $result['msg'] = '请先登录';
            $result['code'] = 2;
            $result['url'] = 'Login';
            die('0' . base64_encode(json_encode($result)));
        }
        $num = $this->DAO->get_user_detail($_SESSION['user_id'])['num'];
        if(!$num){
            $this->DAO->insert_user_detail($_SESSION['user_id']);
        }
        $account_info = $this->DAO->get_account_info($_SESSION['user_id']);
        if($account_info['pay_account']){
            $result['msg'] = '已设置过支付账号';
            $result['url'] = 'accountCenter';
            die('0' . base64_encode(json_encode($result)));
        }
        if($_POST['pay_account'] != $_POST['confirm_pay_account']){
            $result['msg'] = '两次支付宝账号不同';
            die('0' . base64_encode(json_encode($result)));
        }
        if(!isset($_SESSION['reg_core']) ||
            $_SESSION['reg_core'] != $_POST['mobile'] . "_" . $_POST['sms_code'] ||
            $_POST['sms_code'] == ""){
            $_SESSION['m_reg_error'] = '验证码错误';
            $result['msg']           = '验证码错误';
            die('0' . base64_encode(json_encode($result)));
        }
//
        if(strtotime("now") - $_SESSION['last_send_time'] > 300){
            $_SESSION['m_reg_error'] = '验证码超时';
            $result['msg']           = '验证码超时';
            die('0' . base64_encode(json_encode($result)));
        }
        $this->DAO->update_user_pay_account_and_name($_POST['pay_account'],$_POST['pay_name'],$_SESSION['user_id']);
        $pay_account = $this->DAO->get_account_info($_SESSION['user_id'])['pay_account'];
        if($pay_account){
            $result['code'] = 1;
            $result['msg']  = '支付宝账号设置成功';
            $result['url'] = 'trading_center.php?act=account_center';
            die('0' . base64_encode(json_encode($result)));
        }

    }
    //验证账号是否正确
    public function confirm_pay_account(){
        $_SESSION['login_back_url'] = ltrim($_SERVER['REQUEST_URI'],'/');
        $result  = array('code' => 0, 'msg' => '网络出错！');
        if($_SESSION['user_id']==null){
            $result['msg'] = '请先登录';
            $result['code'] = 2;
            $result['url'] = 'Login';
            die('0' . base64_encode(json_encode($result)));
        }
        $user_info = $this->DAO->get_account_info($_SESSION['user_id']);
        if($user_info['pay_account']==$_POST['pay_account'] && $user_info['pay_name']==$_POST['pay_name']){
            $result['code'] = 1;
            $result['msg']  = '认证成功';
            die('0' . base64_encode(json_encode($result)));
        }
    }

    //设置安全手机
    public function set_security_mobile(){
        $_SESSION['login_back_url'] = ltrim($_SERVER['REQUEST_URI'],'/');
        $result  = array('code' => 0, 'msg' => '网络出错！');
        if($_SESSION['user_id']==null){
            $result['msg'] = '请先登录';
            $result['code'] = 2;
            $result['url'] = 'Login';
            die('0' . base64_encode(json_encode($result)));
        }
        $num = $this->DAO->get_user_detail($_SESSION['user_id'])['num'];
        if(!$num){
            $this->DAO->insert_user_detail($_SESSION['user_id']);
        }
        if($_POST['mobile'] != $_POST['confirm_mobile']){
            $result['msg'] = '两次手机号不同';
            die('0' . base64_encode(json_encode($result)));
        }
        $this->DAO->update_user_security_mobile($_POST['mobile'], $_SESSION['user_id']);
        $security_mobile = $this->DAO->get_security_mobile($_SESSION['user_id'])['security_mobile'];
        if($security_mobile){
            $result['code'] = 1;
            $result['msg']  = '安全手机设置成功';
            $result['url'] = 'trading_center.php?act=account_center';
            die('0' . base64_encode(json_encode($result)));
        }
        $result['code'] = 0;
        $result['msg']  = '安全手机设置出错';
        die('0' . base64_encode(json_encode($result)));
    }

    /**
     * 发送验证码短信
     */
    public function register_sms(){
        if(!$_POST['pagehash'] || $_POST['pagehash']!=$_SESSION['page-hash']){
            $msg['code']="0";//1成功0失败
            $msg['msg']="发生错误，错误代码E001,请重新刷新页面。";
            die('0' . base64_encode(json_encode($msg)));
        }
        $today = date('Y-m-d', time());
        $phone = $_POST['mobile'];
        if(!$this->is_mobile($phone)){
            $msg['code'] = "0";//1成功0失败
            $msg['msg'] = "手机号码格式错误,请重试";
            die('0' . base64_encode(json_encode($msg)));
        }

        $nowtime      = strtotime("now");
        $last_session = $_SESSION['last_send_time'];
        if(isset($_SESSION['last_send_time'])){
            if($nowtime - $_SESSION['last_send_time'] < 120){
                $msg['code'] = "0";//1成功0失败
                $msg['msg'] = "获取验证码太频繁，请稍后再试";
                die('0' . base64_encode(json_encode($msg)));
            } else{
                $_SESSION['last_send_time'] = $nowtime;
            }
        } else{
            $_SESSION['last_send_time'] = $nowtime;
        }
        $this->send_verify($this->client_ip(), $phone, $today);
        $code = str_split($_SERVER["REQUEST_TIME"] . rand(1001, 9999));
        $code = substr(implode('', array_rand($code, 6)), 0, 6);
        $data = array(
            'ip' => $this->client_ip(),
            'phone' => $phone,
            'code' => $code,
            'today' => $today,
            'HTTP_REFERER' => $_SERVER['HTTP_REFERER'],
            'session' => $last_session,
            'now_time' => $nowtime,
        );
        $this->err_log(var_export($data, 1), 'm_sms_log');
        if(empty($_SERVER['HTTP_REFERER']) || $_SERVER['HTTP_REFERER'] == 'http://m.66173.cn/user_certification.php?act=send_sms'){
            $msg['code'] = "0";//1成功0失败
            $msg['msg'] = "请求太频繁！！！";
            die('0' . base64_encode(json_encode($msg)));
        }

        $send_result = $this->bm_send_sms($phone, array($code));
        $this->err_log(var_export($send_result, 1), 'm_sms');
        if($send_result){
            $this->DAO->add_send_sms_log($this->client_ip(), $phone, $code, $today, $_SERVER['HTTP_REFERER'], 1);
            $_SESSION['reg_core'] = $phone . "_" . $code;
            $msg['code']           = "1";//1成功0失败
            $msg['msg']           = "验证消息发送成功！";
//            $msg['reg_core']          = $code;
            die('0' . base64_encode(json_encode($msg)));
        } else{
            $this->DAO->add_send_sms_log($this->client_ip(), $phone, $code, $today, $_SERVER['HTTP_REFERER'], 0);
            $msg['code'] = "0";//1成功0失败
            $msg['msg'] = "验证消息发送失败,请重试";
            die('0' . base64_encode(json_encode($msg)));
        }
    }

    public function send_verify($ip, $phone, $today){
        $ip_count = $this->DAO->get_ip_count($ip, $today);
        if($ip_count['num'] >= 5){
            $msg['code'] = "0";//1成功0失败
            $msg['msg'] = "本日该手机号接受短信已上限!!!";
            die('0' . base64_encode(json_encode($msg)));
        }
        $phone_count = $this->DAO->get_phone_count($phone, $today);
        if($phone_count['num'] >= 5){
            $msg['code'] = "0";//1成功0失败
            $msg['msg'] = "本日该手机号接受短信已上限";
            die('0' . base64_encode(json_encode($msg)));
        }
    }
    //提现明细
    public function get_user_withdraw_info(){
        $_SESSION['login_back_url'] = ltrim($_SERVER['REQUEST_URI'],'/');
        $result  = array('code' => 0, 'msg' => '网络出错！');
        if($_SESSION['user_id']==null){
            $result['msg'] = '请先登录';
            $result['code'] = 2;
            $result['url'] = 'Login';
            die('0' . base64_encode(json_encode($result)));
        }
        $user_id = $_SESSION['user_id'];
        $data = $this->DAO->get_user_withdraw_info($user_id);
        if($data){
            $result['code'] = 1;
            $result['msg']  = '获取数据成功';
            die('0' . base64_encode(json_encode($result)));
        }
        $result['msg']  = '暂无数据';
        die('0' . base64_encode(json_encode($result)));
    }
    //查看收入
    public function get_user_balance_detail(){
        if(!$_SESSION['user_id']){
            $this->redirect("/Login");
        }
        $data = $this->DAO->get_user_balance_detail($_SESSION['user_id']);
        $this->assign('data',$data);
        $this->display('moyu/deal_detail.html');
    }
    //验证密码
    public function verify_passward(){
        $_SESSION['login_back_url'] = ltrim($_SERVER['REQUEST_URI'],'/');
        $result  = array('code' => 0, 'msg' => '网络出错！');
        if($_SESSION['user_id']==null){
            $result['msg'] = '请先登录';
            $result['code'] = 2;
            $result['url'] = 'Login';
            die('0' . base64_encode(json_encode($result)));
        }
        $user_id = $_SESSION['user_id'];
        $pay_passward = $this->DAO->get_user_pay_passward($user_id)['pay_passward'];
        if($pay_passward!=$_POST['pay_passward']){
            $result['msg']  = '密码错误';
        }
        $result['code'] = 1;
        $result['msg']  = '密码正确';
        die('0' . base64_encode(json_encode($result)));
    }
    //获取提现账户和余额
    public function get_user_info(){
        $_SESSION['login_back_url'] = ltrim($_SERVER['REQUEST_URI'],'/');
        $result  = array('code' => 0, 'msg' => '网络出错！');
        if($_SESSION['user_id']==null){
            $result['msg'] = '请先登录';
            $result['code'] = 2;
            $result['url'] = 'Login';
            die('0' . base64_encode(json_encode($result)));
        }
        $balance = $this->DAO->get_account_info($_SESSION['user_id'])['balance'];
        $pay_account = $this->DAO->get_account_info($_SESSION['user_id'])['pay_account'];
        $data['balance'] = $balance;
        $data['pay_account'] = $pay_account;
        if(empty($pay_account)){
            $result['code'] = 0;
            $result['msg']  = '请设置支付宝账号';
            die('0' . base64_encode(json_encode($result)));
        }

        $result['code'] = 1;
        $result['msg']  = '查询成功';
        $result['data']  = $data;
        die('0' . base64_encode(json_encode($result)));

    }
    //发起提现
    public function put_up_withdraw(){
        $_SESSION['login_back_url'] = ltrim($_SERVER['REQUEST_URI'],'/');
        $result  = array('code' => 0, 'msg' => '网络出错！');
        if($_SESSION['user_id']==null){
            $result['msg'] = '请先登录';
            $result['code'] = 2;
            $result['url'] = 'Login';
            die('0' . base64_encode(json_encode($result)));
        }
        if(!$_POST['pagehash'] || $_POST['pagehash'] != $_SESSION['page-hash']){
            $result['msg'] = '请求出错,请刷新页面重新提交。';
            die('0' . base64_encode(json_encode($result)));
        }
        if(empty($_POST['money'])){
            $result['msg']  = '请设置提现金额';
            die('0' . base64_encode(json_encode($result)));

        }
        $balance = $this->DAO->get_account_info($_SESSION['user_id'])['balance'];
        if($balance<$_POST['money']){
            $result['msg']  = '提现金额过大';
            die('0' . base64_encode(json_encode($result)));
        }
        //更新账户余额
        $this->DAO->update_user_balance($_POST['money'],$_SESSION['user_id']);
        $params['user_id'] = $_SESSION['user_id'];
        $params['money'] =  $_POST['money'];
        $params['charge_money'] = $_POST['money']*0.02;
        $params['actual_money'] = $_POST['money'] - $params['charge_money'];
        $id = $this->DAO->insert_user_withdraw($params);
        if($id>0){
            $result['code'] = 1;
            $result['msg']  = '提现申请成功';
            $result['url']  = '/user_certification.php?act=user_with_draw_success';
            die('0' . base64_encode(json_encode($result)));
        }
    }
    public function my_income(){
        $_SESSION['login_back_url'] = ltrim($_SERVER['REQUEST_URI'],'/');
        if(!$_SESSION['user_id']){
            $this->redirect("/Login");
        }
        $account_info = $this->DAO->get_account_info($_SESSION['user_id']);
        $user_withdraw = $this->DAO->get_user_withdraw_info($_SESSION['user_id']);
        $this->assign('account_info',$account_info);
        $this->assign('user_withdraw',$user_withdraw);
        $this->display('moyu/my_income.html');
    }
    public function withdraw_view(){
        if(!$_SESSION['user_id']){
            $this->redirect("/Login");
        }
        $account_info = $this->DAO->get_account_info($_SESSION['user_id']);
        $this->page_hash();
        $this->assign('account_info',$account_info);
        $this->display('moyu/with_draw_view.html');
    }
    public function user_with_draw_success(){
        $this->display('moyu/user_with_draw_success.html');
    }


}