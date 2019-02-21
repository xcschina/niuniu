<?php
COMMON('baseCore');
DAO('guild_reserve_dao','account_dao');
class guild_reserve extends baseCore{
    public $DAO;
    public $account_dao;

    public function __construct(){
        parent::__construct();
        $this->DAO = new guild_reserve_dao();
        $this->account_dao =new account_dao();
    }

    public function index($act_id){
//        $this->open_debug();
        //非手机用户注销登录
        if(!$_SESSION['user_id'] || !$_SESSION['mobile']){
            unset($_SESSION['user_id']);
            unset($_SESSION['nick_name']);
            unset($_SESSION['mobile']);
            unset($_SESSION['sex']);
            unset($_SESSION['email']);
            unset($_SESSION['birthday']);
            unset($_SESSION['qq_openid']);
            unset($_SESSION['rec_core']);
            unset($_SESSION['act_id']);
        }
        $_SESSION['act_id'] = $act_id;
        $act_info = $this->DAO->get_guild_act_info($act_id);
        if(empty($act_info) || empty($act_info['guild_code']) || empty($act_info['guild_id'])|| empty($act_info['game_id'])){
            die("活动参数异常。");
        }
        if(!$act_info['start_time'] || !$act_info['end_time']){
            die("活动时间异常");
        }
        $reserve_time = 0;//活动默认状态
        if($act_info['start_time']> time()){
            $reserve_time = 1;//活动未开始
        }else if(time() > $act_info['end_time']){
            $reserve_time = 2;//活动已结束
        }else if($act_info['end_time'] >time() && time() > $act_info['start_time']){
            $reserve_time = 3;//活动进行中
        }
        //下载解封处理
        $is_down = 0;
        if($act_info['undo_time']  && (time() > $act_info['undo_time'])){
            $is_down = 1;
        }
        //我的预约
        $is_reserve = 0;
        if(!empty($_SESSION['user_id'])){
            $user_reserve_log = $this->DAO->get_my_reserve_log($act_info['id'],$_SESSION['user_id']);
            if(!empty($user_reserve_log)){
                $is_reserve = 1;
            }
        }
        //获取公会的信息
        $guild_info = $this->DAO->get_guild_info($act_info['guild_id']);
        //预约人数
        $reserve_all_count = $this->DAO->get_all_reserve_count($act_info['id']);
        $reserve_count = $this->DAO->get_guild_reserve_count($act_info['id'],$act_info['guild_id']);
        if(!$reserve_count || $reserve_time == 1){
            $reserve_count = 0;
        }
        $down_url = $this->get_guild_down_url($act_info);
        $rank_list = $this->DAO->get_guild_rank($act_info);
        $my_rank = $this->DAO->get_my_guild_rank($act_info);
        $this->wx_share($share_url,$user_code);
        $this->page_hash();
        $this->assign("reserve_all_count", $reserve_all_count);
        $this->assign("reserve_count", $reserve_count);
        $this->assign("act_info", $act_info);
        $this->assign("down_url", $down_url);
        $this->assign("my_rank", $my_rank);
        $this->assign("rank_list", $rank_list);
        $this->assign("guild_info", $guild_info);
        $this->assign("reserve_time", $reserve_time);
        $this->assign("is_down", $is_down);
        $this->assign("act_id", $act_id);
        $this->assign("is_reserve", $is_reserve);
        $this->display('guild_reserve_view.html');
    }

    public function create_guids() {
        $charid = strtolower(md5(uniqid(mt_rand(), true)));
        $uuid = substr($charid, 0, 32);
        return $uuid;
    }

    public function get_guild_down_url($act_info){
       $app_info = $this->DAO->get_app_info($act_info['game_id']);
       $pick_name = str_replace('apk/','',$app_info['apk_url']);
       $guild_pick_name = str_replace('.apk','_'.$act_info['guild_code'].'.apk',$pick_name);
       $url = 'http://apk.66173.cn/'.$act_info['game_id']."/".$guild_pick_name;
       return $url;
    }

    public function my_gift(){
        $result = array("result"=>0,"desc"=>'网络请求异常。',"status"=>0);
        $user_id = $_SESSION['user_id'];
        $act_id = $_SESSION['act_id'];
        if(empty($user_id)){
            $result = array("result"=>0,"desc"=>'请先登录。',"status"=>0);
            die(json_encode($result));
        }
        if(empty($act_id)){
            $result = array("result"=>0,"desc"=>'网络异常,请刷新后重新点击。',"status"=>0);
            die(json_encode($result));
        }
        $act_info = $this->DAO->get_guild_act_info($act_id);
        $my_gifts = $this->DAO->get_gift_code_by_userid($act_info['gift_id'],$user_id);
        $result['result'] = 1;
        $result['desc'] = '查询成功';
        $result['data'] = $my_gifts;
        die(json_encode($result));
    }

    public function reserve(){
//        $this->open_debug();
        $result = array('code'=> 3,'msg'=>'参数异常！');
        $act_id = $_POST['act_id'];
        $phone = $_POST['mobile'];
        $usr_id = $_POST['user_id'];
        $hash = $_POST['pagehash'];
        if($_SESSION['page-hash'] != $hash){
            $result['msg'] = '参数异常！ 001';
            die(json_encode($result));
        }
        $ip_reserve_sum = $this->DAO->get_reserve_ip_sum($this->client_ip());
        if($ip_reserve_sum['num'] > 3){
            $result['code'] = 2;
            $result['msg'] = 'ip异常，请联系客服！';
            die(json_encode($result));
        }
        if(empty($usr_id)){
            $result['code'] = 0;
            $result['msg'] = '缺少用户登录参数！';
            die(json_encode($result));
        }
        //用户参数验证
        if($usr_id != $_SESSION['user_id'] || $act_id != $_SESSION['act_id']){
            $result['msg'] = '参数异常！ 001';
            die(json_encode($result));
        }
        $user_info = $this->account_dao->get_user_by_userid($usr_id);
        if(empty($user_info) || !$user_info['mobile']){
            $result['code'] = 0;
            $result['msg'] = '用户参数异常！';
            die(json_encode($result));
        }
        $act_info = $this->DAO->get_guild_act_info($act_id);
        $guild_reserve_info = $this->DAO->get_guild_reserve_info($act_id);
        if(empty($guild_reserve_info)){
            $result['code'] = 0;
            $result['msg'] = '活动参数异常！';
            die(json_encode($result));
        }
        $reserve_log = $this->DAO->get_reserve_log($guild_reserve_info['activity_id'],$usr_id);
        if(empty($reserve_log)){
            //插入预约日志
            $this->DAO->insert_reserve_log($act_id,$usr_id,$this->client_ip(),$guild_reserve_info);
            $this->insert_reserver_rank($guild_reserve_info);
            //领取礼包
            if($act_info['gift_id']){
                $act_code = $this->DAO->get_gift_code_by_userid($act_info['gift_id'],$usr_id);
                if(!$act_code){
                    $last_gift = $this->DAO->query_last_gift($act_info['gift_id']);
                    if($last_gift){
                        $this->DAO->update_game_gifts($last_gift['id'],$usr_id);
                    }
                }
            }
            $result['code'] = 1;
            $result['msgTitle'] = '恭喜您预约成功！';
            die(json_encode($result));
        }else{
            $result['code'] = 2;
            $result['msg'] = '您已预约过了！';
            die(json_encode($result));
        }
    }

    public function insert_reserver_rank($act_info){
       $act_guild_rank = $this->DAO->get_act_guild_rank($act_info);
       if(empty($act_guild_rank)){
           //插入
           $this->DAO->insert_act_guild_rank($act_info);
       }else{
           $num = 1;
           if(isset($act_guild_rank['reserve_num'])){
               $num = $act_guild_rank['reserve_num'] + 1;
           }
           //更新
           $this->DAO->update_act_guild_rank($act_info,$num);
       }
    }

    public function do_login(){
        die("服务器升级，暂时无法登录。");
        $result = array("code"=>0,"msg"=>'网络请求出错.');
        $mobile = $_POST['mobile'];
        $sms_code = $_POST['sms_code'];
        $hash = $_POST['pagehash'];

        if($_SESSION['page-hash'] != $hash){
            $result['desc'] = '参数异常！ 001';
            die(json_encode($result));
        }

        $source = 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];

        if(!$this->is_mobile($mobile)){
            $result['desc'] = '手机号码格式不正确'.$mobile;
            die(json_encode($result));
        }
        if(!isset($_SESSION['reg_core']) ||
            $_SESSION['reg_core']!=$_POST['mobile']."_".$_POST['sms_code'] ||
            $_POST['sms_code']==""){
            $result['desc'] = '验证码错误';
            die(json_encode($result));
        }
        if(strtotime("now") - $_SESSION['last_send_time']>300){
            $result['desc'] = '验证码超时'.$_SESSION['last_send_time'];
            die(json_encode($result));
        }
        //验收是否是旧用户
        $usr_info = $this->account_dao->get_user_by_mobile($mobile);
        if($usr_info){
            //登录
            $this->account_dao->insert_login_log($usr_info['user_id'],$moblie,$this->client_ip(),$usr_info['password'],$source,$_SERVER['HTTP_USER_AGENT'],"验证码登录成功","1");
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
            $result = array("code"=>1,"msg"=>'登录成功.');
        }else{
            if($this->client_ip()=='150.242.233.54'){
                $result['desc'] = '网络异常！ 001';
                die(json_encode($result));
            }
            $ip_count = $this->account_dao->get_login_ip_count($this->client_ip());
            if($ip_count['num'] > 3){
                $result['desc'] = '您的ip已多次参与预约,请联系客服解决。';
                die(json_encode($result));
            }
            //注册
            $password = rand(10000000,99999999);
            $usr_id = $this->account_dao->insert_user_info(md5($password),$mobile,$this->client_ip());
            sleep(1);
            //短信推送注册信息
            $send_result = $this->send_sms($mobile,array($password),"172456");
            $this->err_log(var_export($send_result,1),'register_sms_m');

            $user_info = $this->account_dao->get_user_by_userid($usr_id);
            $_SESSION['user_id'] = $user_info['user_id'];
            $_SESSION['nick_name'] = $user_info['nick_name'];
            $_SESSION['mobile']=$user_info['mobile'];
            $_SESSION['sex']=$user_info['sex'];
            $_SESSION['email']=$user_info['email'];
            $_SESSION['birthday']=$user_info['birthday'];
            $_SESSION['buy_mobile']=$user_info['buy_mobile'];
            $_SESSION['buy_qq']=$user_info['qq'];
            $_SESSION['guid'] = $user_info['guid'];
            $_SESSION['is_agent'] = $user_info['is_agent'];
            $result = array("code"=>1,"msg"=>'注册成功.');
        }
        die(json_encode($result));
    }

    public function sms_code(){
        $phone = $_POST['mobile'];
        $nowtime=strtotime("now");
        if(!$phone){
            $msg['res']="0";//1成功0失败
            $msg['msg']="请输入您的手机号";
            die(json_encode($msg));
        }
        if(!$this->is_mobile($phone)){
            $msg['res']="0";//1成功0失败
            $msg['msg']="验证消息发送失败,请重试";
            die(json_encode($msg));
        }
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
        $send_result = $this->send_sms($phone,array($code),"23267");
        $this->err_log(var_export($send_result,1),'sms');
        if($send_result){
            $_SESSION['reg_core']=$phone."_".$code;
            $msg['res']="1";//1成功0失败
            $msg['msg']="验证消息发送成功！";
//            $msg['code']=$code;
            die(json_encode($msg));
        }else{
            $msg['res']="0";//1成功0失败
            $msg['msg']="验证消息发送失败,请重试2";
            echo json_encode($msg);
        }
    }

    public function wx_share($share_url,$user_code){
        $ret = $this->DAO->get_wx_access_token();
        if(!$ret){
            COMMON('weixin.class');
            $ret = wxcommon::getToken();
            $this->DAO->set_wx_access_token($ret);
        }
        $ACCESS_TOKEN = $ret['access_token'];
        $jsapi_data = $this->DAO->get_wx_access_jsapi_data($ACCESS_TOKEN);
        if(!$jsapi_data){
            $url = 'https://api.weixin.qq.com/cgi-bin/ticket/getticket?access_token='.$ACCESS_TOKEN.'&type=jsapi';
            $content = file_get_contents($url);
            $jsapi_data =json_decode($content, true);
            $this->DAO->set_wx_access_jsapi_data($ACCESS_TOKEN,$jsapi_data);
        }
        $guid = $this->create_guids();
        $time = time();


        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
        $share_url = $protocol.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
//        if($user_code){
//            $share_url = $share_url.'&code='.$user_code;
//        }
        $sign = "jsapi_ticket=".$jsapi_data['ticket']."&noncestr=".$guid."&timestamp=".$time."&url=".$share_url;
        $signature = sha1($sign);
        $this->assign("noncestr", $guid);
        $this->assign("timestamp", $time);
        $this->assign("signature", $signature);
    }


}
?>
