<?php
COMMON('baseCore', 'pageCore');
DAO('reserve_dao','account_dao');
class reserve_web extends baseCore{
    public $DAO;
    public $COMDAO;
    public $account_dao;

    public function __construct(){
        parent::__construct();
        $this->DAO = new reserve_dao();
        $this->account_dao =new account_dao();
    }

    public function insert_url_open_log(){
        $url = $_SERVER['REQUEST_URI'];
        $act_id = $_GET['act_id'];
        $code = $_GET['code'];
        $this->DAO->insert_open_log($url,$act_id,$code,$this->client_ip());
    }

    public function reserve_total($act_id){
        $act_info = $this->DAO->get_act_info($act_id);
        if(empty($act_info['virtual_num'])){
            $virtual_num = 0;
        }else{
            $virtual_num = $act_info['virtual_num'];
        }
        $real_reserve_num = $this->DAO->get_reserve_conut($act_id);
        $reserve_conut = $real_reserve_num + $virtual_num;
        return $reserve_conut;
    }

    public function user_draw_num($act_id,$user_id){
        $draw_num = 0;
        $user_code = 'g'.$user_id.'wl';
        //获取用户分享次数
        $user_shara_num = $this->DAO->get_draw_count($user_code,$act_id);
        if($user_shara_num > 2){
            $user_shara_num = 2;
        }
        $user_reserve_log = $this->DAO->get_reserve_log($act_id,$user_id);
        if($user_reserve_log){
            $user_shara_num = $user_shara_num + 1;
        }
        //获取用户已抽奖次数
        $is_draw_num = $this->DAO->get_draw_log_count($user_id,$act_id);
        if($is_draw_num >= 3){
            $is_draw_num = 3;
        }
        //用户可以抽奖次数
        if($user_shara_num > $is_draw_num){
            $draw_num = $user_shara_num - $is_draw_num;
        }
        return $draw_num;
    }

    public function index($act_id,$code=''){
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
        $this->insert_url_open_log();
        unset($_SESSION['rec_core']);
        if(!empty($code)){
            $_SESSION['rec_core'] = $code;
        }
        if(!$act_id){
            die("活动参数异常");
        }
        $act_info = $this->DAO->get_act_info($act_id);
        if(empty($act_info) || !$act_info['game_id']){
            die("链接出错了");
        }
        $act_status = 0;//活动默认状态
        //活动开始时间判断
        if(!$act_info['start_time'] || !$act_info['end_time']){
            die("活动时间异常");
        }
        if($act_info['start_time']> time()){
            $act_status = 1;//活动未开始
        }else if(time() > $act_info['end_time']){
            $act_status = 2;//活动已结束
        }else if($act_info['end_time'] >time() && time() > $act_info['start_time']){
            $act_status = 3;//活动进行中
        }
        //下载解封处理
        $is_down = 0;
        if($act_info['undo_time']  && (time() > $act_info['undo_time'])){
            $is_down = 1;
        }
        $user_id = $_SESSION['user_id'];
        //预约人数
        $reserve_conut = $this->reserve_total($act_id);
        //用户抽奖次数,分享次数
        $new_draw_count = $this->user_draw_num($act_id,$user_id);
        $is_reserve = 0;
        if(!empty($user_id)){
            $user_code = 'g'.$user_id.'wl';
            //被分享次数
            $share_count = $this->DAO->get_draw_count($user_code,$act_id);
            $user_reserve_log = $this->DAO->get_reserve_log($act_id,$user_id);
            if(!empty($user_reserve_log)){
                $is_reserve = 1;
            }
        }else{
            $user_code = '';
            $share_count = 0;
        }

        //最强攻略
        $new = $this->DAO->get_game_articles($act_info['game_id']);
        $share_url='http://m.66173.cn/reserve_act.php?act=index&act_id='.$act_id;
        $this->wx_share($share_url,$user_code);
        $this->page_hash();
        $this->assign("user_code", $user_code);
        $this->assign("is_reserve", $is_reserve);
        $this->assign("act_status", $act_status);
        $this->assign("is_down", $is_down);
        $this->assign("draw_count", $new_draw_count);
        $this->assign("share_count", $share_count);
        $this->assign("reserve_conut", $reserve_conut);
        $this->assign("share_num", $share_count);
        $this->assign("draw_param", $draw_param);
        $this->assign("new", $new);
        $this->assign("act_info", $act_info);
        $this->assign("act_id", $act_id);
        $this->assign("rec_code", $user_code);
        $this->display('reserve_view.html');
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

    public function create_guids() {
        $charid = strtolower(md5(uniqid(mt_rand(), true)));
        $uuid = substr($charid, 0, 32);
        return $uuid;
    }

    public function draw_param($act_id){
        //该活动转盘配置
        $draw_param = $this->DAO->get_act_draw_info($act_id);
        $awards = array();
        foreach($draw_param as $key => $data){
            $awards[$key]['name']=$data['title'];
            if(empty($data['type'])){
                $awards[$key]['type']='no';
            }
        }
        return $awards;
    }

    public function reserve(){
        $result = array('code'=> 3,'msg'=>'参数异常！');
        $act_id = $_POST['act_id'];
        $phone = $_POST['mobile'];
        $rec_code = $_POST['rec_code'];
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
        $my_rec_code = 'g'.$usr_id.'wl';
        if($rec_code == $my_rec_code){
            $rec_code = '';
        }
        $reserve_log = $this->DAO->get_reserve_log($act_id,$usr_id);
        if(empty($reserve_log)){
            //插入预约日志
            $this->DAO->insert_reserve_log($act_id,$usr_id,$rec_code,$this->client_ip());
            //领取礼包
            $act_info = $this->DAO->get_act_info($act_id);
            if($act_info['gifts_id']){
                $act_code = $this->DAO->get_gift_code_by_userid($act_info['gifts_id'],$usr_id);
                if(!$act_code){
                    $last_gift = $this->DAO->query_last_gift($act_info['gifts_id']);
                    if($last_gift){
                        $this->DAO->update_game_gifts($last_gift['id'],$usr_id);
                        $this->DAO->add_reserve_draw_log($usr_id,$act_id,$last_gift['batch_id']);
                    }
                }
            }
            $result['code'] = 1;
            $result['msgTitle'] = '恭喜您预约成功！';
            die(json_encode($result));
        }else if($reserve_log && empty($reserve_log['code'])){
            //更新预约日志
            $this->DAO->update_reserve_log($act_id,$usr_id,$rec_code);
            $result['code'] = 2;
            $result['msg'] = '您已预约成功';
            die(json_encode($result));
        }else if($reserve_log){
            $result['code'] = 2;
            $result['msg'] = '您已预约过了！';
            die(json_encode($result));
        }
    }

    public function login_view(){
        $result = array("result"=>'error',"user_id"=>'','mobile'=>'',"rec_code"=>'','is_rec'=>'');
        $user_id = $_SESSION['user_id'];
        $rec_code = $_SESSION['rec_code'];
        $act_id = $_SESSION['act_id'];
        if(empty($user_id)){
            $result['rec_code'] = $rec_code;
            die(json_encode($result));
        }
        $usr_info = $this->account_dao->get_user_by_userid($user_id);
        if(empty($usr_info)){
            $result['rec_code'] = $rec_code;
            die(json_encode($result));
        }
        if($this->is_mobile($usr_info['mobile']) || $this->is_mobile($usr_info['nick_name'])){
            $result['mobile'] = $usr_info['mobile']?$usr_info['mobile']:$usr_info['nick_name'];
        }
        $reserve_log = $this->DAO->get_reserve_log($act_id,$rec_code);
        if(!empty($reserve_log['code'])){
            $result['is_rec'] = 1;
        }
        $result['user_id'] = $user_id;
        die(json_encode($result));

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
            $_SESSION['rec_code'] = 'g'.$usr_info['user_id'].'wl';
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
            $password = rand(100000,999999);
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
            $_SESSION['rec_code'] = 'g'.$user_info['user_id'].'wl';
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

    public function draw(){
        $result = array("code" => 0, "desc" => '网络请求出错.');
        $user_id = $_POST['user_id'];
        $act_id = $_POST['act_id'];
        $hash = $_POST['pagehash'];
        if($_SESSION['page-hash'] != $hash){
            $result['desc'] = '参数异常！ 001';
            die(json_encode($result));
        }
        if(!$act_id){
            $result['desc'] = "活动ID异常";
            die(json_encode($result));
        }
        if(!$user_id){
            $result['code'] = 1;
            $result['desc'] = "用户未登陆";
            die(json_encode($result));
        }
        //获取用户抽奖机会
        $user_code = 'g'.$user_id.'wl';
        $new_draw_count = $this->user_draw_num($act_id,$user_id);
        if(empty($new_draw_count)){
            $result['code'] = 3;
            $result['draw_num'] = 0 ;
            $result['desc'] = "你还未有抽奖机会。";
            die(json_encode($result));
        }
        //获取已抽奖记录
        $draw_log_count = $this->DAO->get_draw_log_count($user_id,$act_id);
        if($draw_log_count >= 3 || empty($new_draw_count)){
            $result['code'] = 3;
            $result['restLotteryTime'] = 0 ;
            $result['desc'] = "亲，你已用光抽奖机会,你已无抽奖机会。";
            die(json_encode($result));
        }elseif($draw_log_count <= $draw_chance){
            $result['desc'] = "参数异常";
            die(json_encode($result));
        }elseif($draw_log_count > $draw_chance){
            $this->go_draw($user_id,$act_id);
        }
    }

    private function getRand($proArr){
        $data = '';
        $proSum = array_sum($proArr); //概率数组的总概率精度
        foreach ($proArr as $k => $v) { //概率数组循环
            $randNum = mt_rand(1, $proSum);
            if ($randNum <= $v) {
                $data = $k;
                break;
            } else {
                $proSum -= $v;
            }
        }
        unset($proArr);
        return $data;
    }

    public function go_draw($user_id,$act_id){
        $result = array("code" => 0, "desc" => '网络请求出错.');
        $params = $this->DAO->get_act_draw_info($act_id);
        foreach ($params as $k => $v) {
            $arr[$v['sort_id']] = $v['chance'];
        }
        $prize_id = $this->getRand($arr); //根据概率获取奖项id
        foreach ($params as $k => $v) { //获取前端奖项位置
            if ($v['sort_id'] == $prize_id) {
                $prize_site = $k;
                break;
            }
        }
        $draw_num = $this->user_draw_num($act_id,$user_id);
        $all = $params;
        $desc = $all[$prize_id - 1];//中奖项
        $result['awards'] = $this->draw_param($act_id);
        $result['code'] = 2;
        $result['msgTitle'] = '恭喜你抽中'.$desc['title'];
        $result['awardKey'] = $prize_site;//前端奖项从-1开始
        $result['restLotteryTime'] = $draw_num - 1;//剩余抽奖次数
        //抽奖记录
        $draw_id = $this->DAO->add_draw_log($user_id,$act_id,$desc);
        sleep(1);
        $draw_log = $this->DAO->get_reserve_draw_log($draw_id);
        if($draw_log['draw_type']=='2'){
            $gift_code = $this->update_game_gifts($desc['prize_id'],$user_id,$result);
        }elseif($draw_log['draw_type']=='3'){
            $this->update_coupon_log($desc['prize_id'],$user_id,$result);
        }elseif($draw_log['draw_type']=='1'){
            $result['msgCon'] = '请及时联系客服，QQ：252432349';
        }
        die(json_encode($result));
    }

    public function update_game_gifts($batch_id,$user_id,$result){
        $last_gift = $this->DAO->query_last_gift($batch_id);
        if(!$last_gift){
            $result['msgTitle'] = '该奖品已被抢光';
            die(json_encode($result));
        }else{
            $this->DAO->update_game_gifts($last_gift['id'],$user_id);
        }
        return $last_gift;
    }

    public function update_coupon_log($coupon_id,$user_id,$result){
        $coupon = $this->DAO->get_coupon_last_log($coupon_id);
        if(!empty($coupon)){
            $coupon_type = $this->DAO->get_type_coupon($coupon_id);
            if($coupon_type['valid_days']>0){
                $endtime = time() + ($coupon_type['valid_days']*3600);
                $this->DAO->update_coupon_log_date($coupon['id'],$endtime,$user_id);
            }
            $this->DAO->update_coupon_log($coupon['id'],$user_id);
        }else{
            $result['msgTitle'] = '该奖品已被抢光';
            die(json_encode($data));
        }
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
        $my_gifts = $this->DAO->get_user_act_gifts($act_id,$user_id);
        if($my_gifts){
            foreach ($my_gifts as $key=>$data){
                if($data['draw_type']=='2' && !empty($data['prize_id'])){
                    $gift_info = $this->DAO->get_gift_code_by_userid($data['prize_id'],$user_id);
                    $my_gifts[$key]['gift_code'] = $gift_info['code'];
                }
            }
        }
        $result['result'] = 1;
        $result['desc'] = '查询成功';
        $result['data'] = $my_gifts;
        die(json_encode($result));
    }
//    public function correct($act_id){
//        echo '开始时间：'.time();
//        $draw_list = $this->DAO->get_reserve_draw_list($act_id);
//        foreach($draw_list as $key=>$data){
//            if($data['user_id'] && $data['prize_id']){
//                $gift_info = $this->DAO->get_gift_code_by_userid($data['prize_id'],$data['user_id']);
//                if(!$gift_info){
//                    $last_gift = $this->DAO->query_last_gift($data['prize_id']);
//                    if($last_gift){
//                        $this->DAO->update_game_gifts($last_gift['id'],$data['user_id']);
//                    }
//                }
//            }
//        }
//        echo '结束时间：'.time();
//    }

}
?>
