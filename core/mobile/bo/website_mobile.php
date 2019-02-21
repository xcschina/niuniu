<?php
COMMON('baseCore', 'pageCore');
DAO('website_dao','reserve_dao','account_dao');

class website_mobile extends baseCore{

    public $DAO;
    public $game_id;
    public $app_id;
    public $R_DAO;
    public $account_dao;

    public function __construct(){
        parent::__construct();
        $this->DAO = new website_dao();
        $this->R_DAO = new reserve_dao();
        $this->account_dao =new account_dao();
        $this->game_id = 1047;
        $this->app_id = 1821;
    }

    public function dsgl(){
        $_SESSION['logout_back_url'] = $_SERVER['REQUEST_URI'];
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
        }else{
            $user_game_info = $this->get_my_games($_SESSION['user_id'], $this->game_id);
            if($user_game_info){
                $orders = $this->DAO->get_order_list($_SESSION['user_id'],$this->game_id);
                if($orders['pay_money']){
                    $user_game_info['pay_money'] = $orders['pay_money'];
                }else{
                    $user_game_info['pay_money'] = 0;
                }
                $info = 1;
            }else{
                $info = 0;
            }
            $gift = 0;
            if($user_game_info['RoleLevel'] >=1){
                $gift = $gift + 1;
            }
            if($user_game_info['RoleLevel'] >= 35){
                $gift = $gift + 1;
            }
            if($user_game_info['RoleLevel'] >= 70){
                $gift = $gift + 1;
            }
            if($user_game_info['pay_money'] >=200){
                $gift = $gift + 1;
            }
            if($user_game_info['pay_money'] >=500){
                $gift = $gift + 1;
            }
            if($user_game_info['pay_money'] >=1000){
                $gift = $gift + 1;
            }
            $user_game_info['gift'] = $gift;
        }
        $gift_log = $this->DAO->get_gift_log($_SESSION['user_id'],$this->game_id);
        $gift_id = array();
        foreach($gift_log as $key=>$data){
            array_push($gift_id,$data['batch_id']);
        }
        $this->wx_share();
        $this->page_hash();
        $this->assign("info",$info);
        $this->assign("gift_id",$gift_id);
        $this->assign("user_game_info",$user_game_info);
        $this->display("website/dsgl.html");
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

    public function do_login(){
        $result = array("code"=>0,"msg"=>'网络请求出错.');
        $mobile = $_POST['mobile'];
        $hash = $_POST['pagehash'];
        if(time()-$_SESSION['login_time'] < 5){
            $result['msg'] = '操作太频繁，请重新输入';
            die(json_encode($result));
        }
        if($_SESSION['page-hash'] != $hash){
            $result['msg'] = '参数异常！ 001';
            die(json_encode($result));
        }
        if(empty($_POST['verifyCode']) || strtoupper($_POST['verifyCode'])!= $_SESSION['c']){
            $result['msg'] = '图形验证码不正确';
            die(json_encode($result));
        }
        $source = 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
        if(!$this->is_mobile($mobile)){
            $result['msg'] = '手机号码格式不正确';
            die(json_encode($result));
        }
        if(!isset($_SESSION['reg_core']) ||
            $_SESSION['reg_core']!=$_POST['mobile']."_".$_POST['sms_code'] ||
            $_POST['sms_code']==""){
            $result['msg'] = '验证码错误';
            die(json_encode($result));
        }
        if(strtotime("now") - $_SESSION['last_send_time']>300){
            $result['msg'] = '验证码超时'.$_SESSION['last_send_time'];
            die(json_encode($result));
        }
        //验收是否是旧用户
        $usr_info = $this->DAO->get_user_by_mobile($mobile);
        if($usr_info){
            //登录
            $this->DAO->insert_login_log($usr_info['user_id'],$mobile,$this->client_ip(),$usr_info['password'],$source,$_SERVER['HTTP_USER_AGENT'],"验证码登录成功","1");
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
            $result = array("code"=>2,"msg"=>'很遗憾！我亲爱的小伙伴，你还没注册牛牛端大圣归来，请先下载并注册！');
        }
        die(json_encode($result));
    }

    public function get_my_games($user_id, $appid){
        $data = $this->DAO->get_memcache($user_id,$appid);
        if(!$data){
            $hosts = [ ES_HOST.":".ES_PORT];
            $client = Elasticsearch\ClientBuilder::create()->setHosts($hosts)->build();
            $params = array();
            $params['index'] = 'sdk_log';
            $params['type']  = 'stats_user_app';
            $json = '{"query":{"bool":{"must":[{"term":{"AppID":'.$appid.'}},{"term":{"UserID":'.$user_id.'}}],"must_not":[{"match":{"AreaServerID":""}}]}},"sort":[{"ActTime":{"order":"asc"}}],
            "aggregations":{"AreaServerID":{"terms":{"field":"AreaServerID"},"aggregations":{"AreaServerName":{"terms":{"field":"AreaServerName"},
            "aggregations":{"RoleLevel":{"terms":{"field":"RoleLevel"},"aggregations":{"RoleName":{"terms":{"field":"RoleName"}}}}}}}}}}';
            $params['body'] = $json;
            $results = $client->search($params);
            $es_data = $results['aggregations']['AreaServerID'];
            $data = array();
            foreach ($es_data['buckets'] as $key => $val) {
                $data["new_RoleLevel"] = $val['AreaServerName']['buckets'][0]['RoleLevel']['buckets'][0]['key'];
                if($data["new_RoleLevel"] > $data['RoleLevel']){
                    $data["RoleLevel"] = $data["new_RoleLevel"];
                    $data["AreaServerID"] = $val['key'];
                    $data["AreaServerName"] = $val['AreaServerName']['buckets'][0]['key'];
                    $data["RoleName"] = $val['AreaServerName']['buckets'][0]['RoleLevel']['buckets'][0]['RoleName']['buckets'][0]['key'];
                }
            }
            unset($data['new_RoleLevel']);
            $this->DAO->set_memcache($data,$user_id,$appid);
        }
        return $data;
    }

    public function sms_code(){
        $phone = $_POST['mobile'];
        $nowtime = strtotime("now");
        $hash = $_POST['pagehash'];
        if($_SESSION['page-hash'] != $hash){
            $result['code'] = 0;
            $result['msg'] = '参数异常！ 001';
            die(json_encode($result));
        }
        if(!$phone){
            $msg['res'] = "0";//1成功0失败
            $msg['msg'] = "请输入您的手机号";
            die(json_encode($msg));
        }

        if(!$this->is_mobile($phone)){
            $msg['res'] = "0";//1成功0失败
            $msg['msg'] = "验证消息发送失败,请重试";
            die(json_encode($msg));
        }
        if(isset($_SESSION['last_send_time'])){
            if($nowtime-$_SESSION['last_send_time']<180){
                $msg['res'] = "0";//1成功0失败
                $msg['msg'] = "获取验证码太频繁，请稍后再试";
                die(json_encode($msg));
            }else{
                $_SESSION['last_send_time'] = $nowtime;
            }
        }else{
            $_SESSION['last_send_time'] = $nowtime;
        }
        $code = str_split($_SERVER["REQUEST_TIME"] .rand(1001, 9999));
        $code = substr(implode('', array_rand($code, 6)), 0, 6);
        $send_result = $this->send_sms($phone,array($code),"201723");
        $this->err_log(var_export($send_result,1),'sms');
        if($send_result){
            $_SESSION['reg_core'] = $phone."_".$code;
            $msg['res'] = "1";//1成功0失败
            $msg['msg'] = "验证消息发送成功！";
//            $msg['code'] = $code;
            die(json_encode($msg));
        }else{
            $msg['res'] = "0";//1成功0失败
            $msg['msg'] = "验证消息发送失败,请重试2";
            die(json_encode($msg));
        }
    }

    public function receive_gift(){
        $result = array('code'=> 3,'msg'=>'参数异常！');
        $params = $_POST;
        $hash = $_POST['pagehash'];
        if($_SESSION['page-hash'] != $hash){
            $result['msg'] = '参数异常！ 001';
            die(json_encode($result));
        }
        $gift_id = array(411,419,413,414,415,416);
        if(!$params['batch_id'] || !in_array($params['batch_id'],$gift_id)){
            $result['msg'] = '礼包参数异常';
            die(json_encode($result));
        }
        $ip_reserve_sum = $this->DAO->get_reserve_ip_sum($this->client_ip(),$params['batch_id']);
        if($ip_reserve_sum['num'] > 3){
            $result['msg'] = 'ip异常，请联系客服！';
            die(json_encode($result));
        }
        if(empty($params['user_id']) || empty($this->game_id)){
            $result['msg'] = '无法获取用户信息,请重新登录！';
            die(json_encode($result));
        }
        //用户参数验证
        if($params['user_id'] != $_SESSION['user_id'] ){
            $result['msg'] = '参数异常！ 001';
            die(json_encode($result));
        }
        $start_time = strtotime("2017-6-12 12:00");
        $end_time = strtotime("2017-6-25 00:00");
        if($start_time > time()){
            $result['code'] = 2;
            $result['msg'] = "活动未开始，敬请期待";
            die(json_encode($result));
        }else if(time() >  $end_time){
            $result['code'] = 2;
            $result['msg'] = "活动已结束，请关注其他活动";
            die(json_encode($result));
        }
        $gift_info = $this->DAO->get_gift_info($params['batch_id'],$params['user_id'],$this->game_id);
        if($gift_info){
            $result['code'] = 2;
            $result['msg'] = "您已领取过，不能再领取了";
            die(json_encode($result));
        }
        $user_game_info = $this->get_my_games($params['user_id'], $this->game_id);
        $orders = $this->DAO->get_order_list($params['user_id'],$this->game_id);
        $user_game_info['pay_money'] = 0;
        foreach ($orders as $key=>$data){
            $user_game_info['pay_money'] += $data['pay_money'];
        }
        $gift = $this->DAO->get_gift($params['batch_id']);
        if($gift){
            $this->DAO->update_code_status($gift,$user_game_info,$this->client_ip(),$this->game_id, $params['user_id']);
            $result['code'] = 1;
            $result['data'] = $gift;
            die(json_encode($result));
        }else{
            $result['code'] = 2;
            $result['msg'] = "来晚一步，礼包被领完了，运营正在加班补充，请稍等。";
            die(json_encode($result));
        }
    }

    public function my_gift(){
        $result = array("code"=>2,"msg"=>'网络请求出错.');
        $hash = $_POST['pagehash'];
        if($_SESSION['page-hash'] != $hash){
            $result['code'] = 2;
            $result['msg'] = '参数异常！ 001';
            die(json_encode($result));
        }
        if(!$_POST['user_id']){
            $result['code'] = 0;
            $result['msg'] = "请先登录";
            die(json_encode($result));
        }
        $gift_list = $this->DAO->get_my_gift($_POST['user_id'],$this->game_id);
        $result['code'] = 1;
        $result['msg'] = "请求成功";
        $result['data'] = $gift_list;
        die(json_encode($result));
    }

    public function do_logout(){
        $this->DAO->del_memcache($_SESSION['user_id'],$this->game_id);
        unset( $_SESSION['user_id']);
        unset( $_SESSION['nick_name']);
        unset( $_SESSION['mobile']);
        unset($_SESSION['sex']);
        unset($_SESSION['email']);
        unset($_SESSION['birthday']);
        unset($_SESSION['qq_openid']);
        $this->redirect($_SESSION['logout_back_url']);
    }

    public function login_control(){
        $_SESSION['login_time'] = time();
        die(json_encode("1"));
    }

    public function activity($act_id,$code='',$game_id,$batch_id){
        $_SESSION['logout_back'] = $_SERVER['REQUEST_URI'];
        if(!$_SESSION['user_id'] || !$_SESSION['mobile']){
            unset($_SESSION['user_id']);
            unset($_SESSION['nick_name']);
            unset($_SESSION['mobile']);
            unset($_SESSION['sex']);
            unset($_SESSION['email']);
            unset($_SESSION['birthday']);
            unset($_SESSION['qq_openid']);
            unset($_SESSION['core']);
            unset($_SESSION['act_id']);
        }
        $_SESSION['act_id'] = $act_id;
        unset($_SESSION['core']);
        if(!empty($code)){
            $_SESSION['core'] = $code;
        }
        if(!$act_id || $act_id != $game_id){
            die("活动链接出错啦");
        }
        $this->DAO->insert_open_log($act_id,$_SERVER['REQUEST_URI'],$code);
        $user_id = $_SESSION['user_id'];
        $share = 0;  //是否可领取礼包
        if($user_id){
            $visit = $this->DAO->get_user_visit($act_id,$user_id);
            if($visit){
                $share = 1;
            }
        }
        $game_articles = $this->DAO->get_game_articles($act_id);
        $game_info = $this->DAO->get_game_info($game_id);
        $gift_list = $this->DAO->get_gifts($batch_id,$user_id);
        $this->wx_share();
        $this->page_hash();
        $this->assign("game_articles",$game_articles);
        $this->assign("game_info",$game_info);
        $this->assign("share",$share);
        $this->assign("gift_list",$gift_list);
        $this->display("website/qtzs.html");
    }

    public function draw_gift($game_id,$batch_id){
        $result = array('code'=> 3,'msg'=>'参数异常！');
        $params = $_POST;
        $hash = $_POST['pagehash'];
        if($_SESSION['page-hash'] != $hash){
            $result['msg'] = '参数异常！ 001';
            die(json_encode($result));
        }
        if(!$batch_id){
            $result['msg'] = '礼包参数异常';
            die(json_encode($result));
        }
        $ip_draw_sum = $this->DAO->get_reserve_ip_sum($this->client_ip(),$batch_id);
        if($ip_draw_sum['num'] > 3){
            $result['code'] = 2;
            $result['msg'] = 'ip异常，请联系客服！';
            die(json_encode($result));
        }
        if(empty($params['user_id'])){
            $result['code'] = 0;
            $result['msg'] = '缺少用户登录参数！';
            die(json_encode($result));
        }
        if($params['user_id'] != $_SESSION['user_id'] || $params['act_id'] != $_SESSION['act_id'] || $game_id != $params['act_id']){
            $result['msg'] = '参数异常！ 001';
            die(json_encode($result));
        }
        $user_info = $this->DAO->get_user_info($params['user_id']);
        if(empty($user_info) || !$user_info['mobile']){
            $result['code'] = 0;
            $result['msg'] = '用户参数异常！';
            die(json_encode($result));
        }
        $start_time = strtotime("2017-08-31");
        $end_time = strtotime("2017-09-10");
        if($start_time > time()){
            $result['code'] = 2;
            $result['msg'] = "活动未开始，敬请期待";
            die(json_encode($result));
        }else if(time() >  $end_time){
            $result['code'] = 2;
            $result['msg'] = "活动已结束，请关注更多精彩活动";
            die(json_encode($result));
        }
        $visit = $this->DAO->get_user_visit($params['act_id'],$params['user_id']);
        if(!$visit){
            $result['code'] = 2;
            $result['msg'] = "亲爱的用户，您还未分享活动或者好友还未点击您的分享链接, 登录后分享出去的链接才有效哦～";
            die(json_encode($result));
        }
        $gift_info = $this->DAO->get_gift_info($batch_id,$params['user_id'],$game_id);
        $gift = $this->DAO->get_gifts($batch_id,$params['user_id']);
        if($gift_info || $gift){
            $result['code'] = 2;
            $result['msg'] = "您已领取过，不能再领取了";
            die(json_encode($result));
        }
        $gift = $this->DAO->get_gift_list($batch_id);
        if($gift){
            $this->DAO->update_gift_status($gift,$this->client_ip(),$game_id, $params['user_id']);
            $result['code'] = 1;
            $result['data'] = array($gift);
            die(json_encode($result));
        }else{
            $result['code'] = 2;
            $result['msg'] = "来晚一步，礼包已抢光，请关注更多精彩活动。";
            die(json_encode($result));
        }
    }

    public function user_gift($game_id){
        $result = array("code"=>2,"msg"=>'网络请求出错.');
        $hash = $_POST['pagehash'];
        if($_SESSION['page-hash'] != $hash){
            $result['code'] = 2;
            $result['msg'] = '参数异常！ 001';
            die(json_encode($result));
        }
        if(!$_POST['user_id']){
            $result['code'] = 0;
            $result['msg'] = "请先登录";
            die(json_encode($result));
        }
        $gift_list = $this->DAO->get_my_gift($_POST['user_id'],$game_id);
        $result['code'] = 1;
        $result['msg'] = "请求成功";
        $result['data'] = $gift_list;
        die(json_encode($result));
    }

    public function login(){
        die("服务器升级，暂时无法登录。");
        $result = array("code"=>0,"msg"=>'网络请求出错.');
        $mobile = $_POST['mobile'];
        $hash = $_POST['pagehash'];
        if(time()-$_SESSION['login_time'] < 5){
            $result['msg'] = '操作太频繁，请重新输入';
            die(json_encode($result));
        }
        if($_SESSION['page-hash'] != $hash){
            $result['msg'] = '参数异常！ 001';
            die(json_encode($result));
        }
        $source = 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
        if(!$this->is_mobile($mobile)){
            $result['msg'] = '手机号码格式不正确';
            die(json_encode($result));
        }
        if(!isset($_SESSION['reg_core']) ||
            $_SESSION['reg_core']!=$_POST['mobile']."_".$_POST['sms_code'] ||
            $_POST['sms_code']==""){
            $result['msg'] = '验证码错误';
            die(json_encode($result));
        }
        if(strtotime("now") - $_SESSION['last_send_time']>300){
            $result['msg'] = '验证码超时';
            die(json_encode($result));
        }
        //验收是否是旧用户
        $usr_info = $this->DAO->get_user_by_mobile($mobile);
        if($usr_info){
            //登录
            $this->DAO->insert_login_log($usr_info['user_id'],$mobile,$this->client_ip(),$usr_info['password'],$source,$_SERVER['HTTP_USER_AGENT'],"验证码登录成功","1");
            $_SESSION['user_id'] = $usr_info['user_id'];
            $_SESSION['nick_name'] = $usr_info['nick_name'];
            $_SESSION['mobile'] = $usr_info['mobile'];
            $_SESSION['sex'] = $usr_info['sex'];
            $_SESSION['email'] = $usr_info['email'];
            $_SESSION['birthday'] = $usr_info['birthday'];
            $_SESSION['buy_mobile'] = $usr_info['buy_mobile'];
            $_SESSION['buy_qq'] = $usr_info['qq'];
            $_SESSION['guid'] = $usr_info['guid'];
            $_SESSION['code'] = $usr_info['user_id'];
            $_SESSION['is_agent'] = $usr_info['is_agent'];
            $result = array("code"=>1,"msg"=>'登录成功.');
        }else{
            unset($_SESSION['user_id']);
            unset($_SESSION['nick_name']);
            unset($_SESSION['mobile']);
            unset($_SESSION['sex']);
            unset($_SESSION['email']);
            unset($_SESSION['birthday']);
            unset($_SESSION['qq_openid']);
            unset($_SESSION['core']);
            unset($_SESSION['act_id']);
            $ip_count = $this->account_dao->get_login_ip_count($this->client_ip());
            if($ip_count['num'] > 3){
                $result['msg'] = '您的ip已多次参与活动,请联系客服解决。';
                die(json_encode($result));
            }
            //注册
            $password = rand(100000,999999);
            $usr_id = $this->account_dao->insert_user_info(md5($password),$mobile,$this->client_ip());
            sleep(1);
            //短信推送注册信息
            $send_result = $this->send_sms($mobile,array($password),"200875"); //172456 短信模板ID
            $this->err_log(var_export($send_result,1),'register_sms_m');
            $user_info = $this->DAO->get_user_info($usr_id);
            $_SESSION['user_id'] = $user_info['user_id'];
            $_SESSION['nick_name'] = $user_info['nick_name'];
            $_SESSION['mobile'] = $user_info['mobile'];
            $_SESSION['sex'] = $user_info['sex'];
            $_SESSION['email'] = $user_info['email'];
            $_SESSION['birthday'] = $user_info['birthday'];
            $_SESSION['buy_mobile'] = $user_info['buy_mobile'];
            $_SESSION['buy_qq'] = $user_info['qq'];
            $_SESSION['guid'] = $user_info['guid'];
            $_SESSION['is_agent'] = $user_info['is_agent'];
            $_SESSION['code'] = $user_info['user_id'];
            $result = array("code"=>1,"msg"=>'注册成功.');
        }
        die(json_encode($result));
    }

    public function logout($game_id){
        $this->DAO->del_memcache($_SESSION['user_id'],$game_id);
        unset( $_SESSION['user_id']);
        unset( $_SESSION['nick_name']);
        unset( $_SESSION['mobile']);
        unset($_SESSION['sex']);
        unset($_SESSION['email']);
        unset($_SESSION['birthday']);
        unset($_SESSION['qq_openid']);
        unset($_SESSION['code']);
        unset($_SESSION['act_id']);
        $this->redirect($_SESSION['logout_back']);
    }

    public function send_code(){
        $phone = $_POST['mobile'];
        $nowtime = strtotime("now");
        $result = array("code"=>0,"msg"=>"网络出错啦");
        $hash = $_POST['pagehash'];
        if($_SESSION['page-hash'] != $hash){
            $result['msg'] = '参数异常！ 001';
            die(json_encode($result));
        }
        if(!$phone){
            $result['msg'] = "请输入您的手机号";
            die(json_encode($result));
        }
        if(!$this->is_mobile($phone)){
            $result['msg'] = "手机格式不正确，验证消息发送失败";
            die(json_encode($result));
        }
        if(isset($_SESSION['last_send_time'])){
            if($nowtime-$_SESSION['last_send_time']<120){
                $result['msg'] = "获取验证码太频繁，请稍后再试";
                die(json_encode($result));
            }else{
                $_SESSION['last_send_time'] = $nowtime;
            }
        }else{
            $_SESSION['last_send_time'] = $nowtime;
        }
        $code = str_split($_SERVER["REQUEST_TIME"] .rand(1001, 9999));
        $code = substr(implode('', array_rand($code, 6)), 0, 6);
        $send_result = $this->send_sms($phone,array($code,5),"226704");
        $this->err_log(var_export($send_result,1),'sms');
        if($send_result){
            $_SESSION['reg_core'] = $phone."_".$code;
            $result['code'] = "1";//1成功0失败
            $result['msg'] = "验证消息发送成功！";
//            $result['reg_core'] = $code;
            die(json_encode($result));
        }else{
            $result['msg'] = "验证消息发送失败,请重试";
            die(json_encode($result));
        }
    }

    public function isMobile(){
        $user_agent = $_SERVER['HTTP_USER_AGENT'];
        $mobile_agents = Array("240x320","acer","acoon","acs-","abacho","ahong","airness","alcatel","amoi","android","anywhereyougo.com","applewebkit/525","applewebkit/532","asus","audio","au-mic","avantogo","becker","benq","bilbo","bird","blackberry","blazer","bleu","cdm-","compal","coolpad","danger","dbtel","dopod","elaine","eric","etouch","fly ","fly_","fly-","go.web","goodaccess","gradiente","grundig","haier","hedy","hitachi","htc","huawei","hutchison","inno","ipad","ipaq","ipod","jbrowser","kddi","kgt","kwc","lenovo","lg ","lg2","lg3","lg4","lg5","lg7","lg8","lg9","lg-","lge-","lge9","longcos","maemo","mercator","meridian","micromax","midp","mini","mitsu","mmm","mmp","mobi","mot-","moto","nec-","netfront","newgen","nexian","nf-browser","nintendo","nitro","nokia","nook","novarra","obigo","palm","panasonic","pantech","philips","phone","pg-","playstation","pocket","pt-","qc-","qtek","rover","sagem","sama","samu","sanyo","samsung","sch-","scooter","sec-","sendo","sgh-","sharp","siemens","sie-","softbank","sony","spice","sprint","spv","symbian","tablet","talkabout","tcl-","teleca","telit","tianyu","tim-","toshiba","tsm","up.browser","utec","utstar","verykool","virgin","vk-","voda","voxtel","vx","wap","wellco","wig browser","wii","windows ce","wireless","xda","xde","zte");
        $is_mobile = false;
        foreach ($mobile_agents as $device) {//这里把值遍历一遍，用于查找是否有上述字符串出现过
            if (stristr($user_agent, $device)) { //stristr 查找访客端信息是否在上述数组中，不存在即为PC端。
                $is_mobile = true;
                break;
            }
        }
        return $is_mobile;
    }

    public function yhwz(){
        $start_time = strtotime("2018-1-1");
        $end_time = strtotime("2018-1-16");
        if($start_time > time() || time() > $end_time){
            $time = 2;
        }else{
            $time = 1;
        }
        $total = $this->DAO->get_reserve_num();
        $total_num = 68899 + $total['num'];
        $new_article = $this->DAO->get_new_article($this->app_id);
        $activity_list = $this->DAO->get_article_list($this->app_id,22);
        $news_list = $this->DAO->get_article_list($this->app_id,23);
        $notice_list = $this->DAO->get_article_list($this->app_id,24);
        $this->wx_share();
        $this->page_hash();
        $this->assign("iosLink", "https://itunes.apple.com/cn/app/id1330552759?mt=8");
        if($_GET['ch']=='tap'){
            $this->assign("androidLink", "http://apk.66173yx.com/ad/yhwz_tap1.apk");
        }elseif($_GET['ch']=='wl'){
            $this->DAO->insert_log($_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'],$this->client_ip(),'9999');
            $this->assign("androidLink", "http://apk.66173yx.com/ad/yhwz_wl.apk");
            $this->assign("gt", "1");
        }else{
            $this->assign("androidLink", "http://apk.66173yx.com/ad/yhwz_gw.apk");
        }
        $this->assign("time",$time);
        $this->assign("total_num",$total_num);
        $this->assign("new_article",$new_article);
        $this->assign("activity_list",$activity_list);
        $this->assign("news_list",$news_list);
        $this->assign("notice_list",$notice_list);
        if($this->isMobile()){
            $this->display("website/yh/index.html");
        }else{
            $this->display("website/yh_pc/index.html");
        }
    }

    public function lczg(){
//        $start_time = strtotime("2018-1-1");
//        $end_time = strtotime("2018-1-16");
//        if($start_time > time() || time() > $end_time){
//            $time = 2;
//        }else{
//            $time = 1;
//        }
        $this->app_id = 1824;
        $new_article = $this->DAO->get_new_article($this->app_id);
        $activity_list = $this->DAO->get_article_list($this->app_id,22);
        $news_list = $this->DAO->get_article_list($this->app_id,23);
        $notice_list = $this->DAO->get_article_list($this->app_id,25);
        $this->wx_share();
        $this->page_hash();
        $this->assign("iosLink", "https://itunes.apple.com/cn/app/id1330552759?mt=8");
        $this->assign("androidLink", "http://apk.66173yx.com/ad/yhwz_gw.apk");
//        $this->assign("time",$time);
        $this->assign("total_num",$total_num);
        $this->assign("new_article",$new_article);
        $this->assign("activity_list",$activity_list);
        $this->assign("news_list",$news_list);
        $this->assign("notice_list",$notice_list);
        if($this->isMobile()){
            $this->display("website/lczg/index.html");
        }else{
            $this->display("website/lczg/pc_index.html");
        }
    }

    public function reserve_gift(){
        $result = array('code'=> 0,'msg'=>'参数异常！');
        $params = $_POST;
        $params['batch_id'] = 424;
        $hash = $params['pagehash'];
        if($_SESSION['page-hash'] != $hash){
            $result['msg'] = '参数异常！ 001';
            die(json_encode($result));
        }
        if(!$this->is_mobile($params['mobile'])){
            $result['msg'] = '手机号码格式不正确';
            die(json_encode($result));
        }
        if(!isset($_SESSION['reg_core']) ||
            $_SESSION['reg_core']!=$params['mobile']."_".$params['sms_code'] ||
            $params['sms_code']==""){
            $result['msg'] = '验证码错误';
            die(json_encode($result));
        }
        if(strtotime("now") - $_SESSION['last_send_time']>300){
            $result['msg'] = '验证码超时,请重新获取';
            die(json_encode($result));
        }
        $start =  strtotime(date('Y-m-d', time()));
        $end =  strtotime(date('Ymd')) + 86400;
        $ip_reserve_sum = $this->DAO->get_reserve_ip_log($this->client_ip(),$start,$end);
        if($ip_reserve_sum['num'] > 4){
            $result['msg'] = 'ip异常，请联系客服！';
            die(json_encode($result));
        }
        $start_time = strtotime("2018-1-1");
        $end_time = strtotime("2018-1-16");
        if($start_time > time()){
            $result['msg'] = "活动未开始，敬请期待";
            die(json_encode($result));
        }else if(time() >  $end_time){
            $result['msg'] = "活动已结束，请关注其他活动";
            die(json_encode($result));
        }
        $gift_info = $this->DAO->get_reserve_gift_info($params);
        if($gift_info){
            $result['code'] = 1 ;
            $result['gift_code'] = $gift_info['code'];
            $result['msg'] = "您已预约成功，无需再次预约";
            die(json_encode($result));
        }
        $fp = fopen(PREFIX."/htdocs/gift.txt","w+");//增加排它锁
        if(!flock($fp,LOCK_EX|LOCK_NB)) {
            $result['msg'] = "排队失败，请重新尝试";
            die(json_encode($result));
        }
        $gift_batch = $this->DAO->get_all_gift_info($params['batch_id']);
        if($gift_batch['end_time'] < strtotime("now")){
            flock($fp,LOCK_UN);
            $result['msg'] = "礼包已过期";
            die(json_encode($result));
        }
        if(!$gift_batch || $gift_batch['remain'] <1){
            flock($fp,LOCK_UN);
            $result['msg'] = "来晚一步，礼包被领完了，运营正在加班补充，请稍等。";
            die(json_encode($result));
        }
        $gift = $this->DAO->get_gift($params['batch_id']);
        if($gift){
            $this->DAO->update_gift_code_status($gift);
            if($this->isMobile()){
                $from = 1;
            }else{
                $from = 2;
            }
            $this->DAO->insert_reserve_log($params,$gift,$this->client_ip(),$this->app_id,$from);
            flock($fp,LOCK_UN);
            $result['code'] = 1;
            $result['gift_code'] = $gift['code'];
            $result['msg'] = "您已成功预约活动";
            die(json_encode($result));
        }else{
            flock($fp,LOCK_UN);
            $result['msg'] = "来晚一步，礼包被领完了，运营正在加班补充，请稍等。";
            die(json_encode($result));
        }
    }

    public function article_detail($id){
        $info = $this->DAO->get_article_info($id);
        $this->assign("info",$info);
        if($this->isMobile()){
            $this->display("website/yh/detail.html");
        }else{
            $this->display("website/yh_pc/detail.html");
        }
    }


    public function article_detail2($id,$game_id){
        $this->open_debug();
        $info = $this->DAO->get_article_info($id);
        $this->assign("info",$info);
        if($game_id == '6025'){

        }
        $this->display("website/lczg/news_detail.html");
    }

    public function jyjy($act_id,$code){
        $game_id = 1820;
        $batch_id_new = 425;//新手礼包
        $batch_id_unique = 427;//独家礼包
        $batch_id_privilege = 426;//特权礼包
        $_SESSION['logout_back'] = $_SERVER['REQUEST_URI'];
        if(!$_SESSION['user_id'] || !$_SESSION['mobile']){
            unset($_SESSION['user_id']);
            unset($_SESSION['nick_name']);
            unset($_SESSION['mobile']);
            unset($_SESSION['sex']);
            unset($_SESSION['email']);
            unset($_SESSION['birthday']);
            unset($_SESSION['qq_openid']);
            unset($_SESSION['core']);
            unset($_SESSION['act_id']);
        }
        $_SESSION['act_id'] = $act_id;
        unset($_SESSION['core']);
        if(!empty($code)){
            $_SESSION['core'] = $code;
        }
        if(!$act_id || $act_id != $game_id){
            die("活动链接出错啦");
        }
        $this->DAO->insert_open_log($act_id,$_SERVER['REQUEST_URI'],$code);
        $user_id = $_SESSION['user_id'];
        if ($user_id){
            $gift_list_new = $this->DAO->get_gifts($batch_id_new,$user_id);
            $gift_list_unique = $this->DAO->get_gifts($batch_id_unique,$user_id);
            $gift_list_privilege = $this->DAO->get_gifts($batch_id_privilege,$user_id);
            $this->assign("gift_list_new",$gift_list_new);
            $this->assign("gift_list_unique",$gift_list_unique);
            $this->assign("gift_list_privilege",$gift_list_privilege);
        }
        $game_info = $this->DAO->get_game_info($game_id);
        $this->wx_share();
        $this->page_hash();
        $this->assign("game_info",$game_info);
        $this->display("website/jyjy.html");
    }

    //领取礼包base64加密
    public function draw_gift_secret(){
        $game_id = 1820;
        $params = $_POST;
        $result = array('code'=> 4,'msg'=>'参数异常！','type'=>$params['type']);
        $hash = $params['pagehash'];
        if($_SESSION['page-hash'] != $hash){
            $result['msg'] = '参数异常！ 001';
            die("0".base64_encode(json_encode($result)));
        }
        if(empty($params['user_id'])){
            $result['code'] = 0;
            $result['msg'] = '缺少用户登录参数！';
            die("0".base64_encode(json_encode($result)));
        }
        if($params['user_id'] != $_SESSION['user_id'] || $params['act_id'] != $_SESSION['act_id'] || $game_id != $params['act_id']){
            $result['msg'] = '参数异常！ 001';
            die("0".base64_encode(json_encode($result)));
        }
        if ($params['type']=='new'){
            $batch_id = 425;//新手礼包
        }elseif ($params['type']=='unique'){
            $batch_id = 427;//独家礼包
        }elseif ($params['type']=='privilege'){
            $batch_id = 426;//特权礼包
        }else{
            $result['msg'] = '参数异常,类型错误！';
            die("0".base64_encode(json_encode($result)));
        }
        $ip_draw_sum = $this->DAO->get_reserve_ip_sum($this->client_ip(),$batch_id);
        if($ip_draw_sum['num'] > 3){
            $result['msg'] = 'ip异常，请联系客服！';
            die("0".base64_encode(json_encode($result)));
        }
        $user_info = $this->DAO->get_user_info($params['user_id']);
        if(empty($user_info) || !$user_info['mobile']){
            $result['code'] = 0;
            $result['msg'] = '用户参数异常！';
            die("0".base64_encode(json_encode($result)));
        }
        $start_time = strtotime("2018-02-11");
        $end_time = strtotime("2018-03-31");
        if($start_time > time()){
            $result['code'] = 3;
            $result['msg'] = "活动未开始，敬请期待";
            die("0".base64_encode(json_encode($result)));
        }else if(time() >  $end_time){
            $result['code'] = 3;
            $result['msg'] = "活动已结束，请关注更多精彩活动";
            die("0".base64_encode(json_encode($result)));
        }
        $gift_info = $this->DAO->get_gift_info($batch_id,$params['user_id'],$game_id);
        $gift = $this->DAO->get_gifts($batch_id,$params['user_id']);
        if($gift_info || $gift){
            $result['code'] = 2;
            $result['msg'] = "您已领取过，不能再领取了";
            die("0".base64_encode(json_encode($result)));
        }
        if ($params['type']=='unique'){
            //验证用户是否在活动期间登录过牛果APP
            $hosts = [ ES_HOST.":".ES_PORT];
            $client = Elasticsearch\ClientBuilder::create()->setHosts($hosts)->build();
            $es_params = array();
            $es_params['index'] = 'sdk_log';
            $es_params['type']  = 'user_op_log';
            $json = '{"query":{"bool":{ "must":[{"term": {"appid":"1051"}},{"term": {"userid":'.(int)$params['user_id'].'}},
                    {"range": {"addtime": {"gte":'.$start_time.' ,"lte":'.$end_time.'}}}]}}}';
            $es_params['body'] = $json;
            $results = $client->search($es_params);
            if (empty($results['hits']['hits'])){
                $result['code'] = 2;
                $result['msg'] = "您没有在活动期间登录牛果APP，请先下载登录";
                die("0".base64_encode(json_encode($result)));
            }
        }elseif ($params['type']=='privilege'){
            //验证用户是否分享
            $visit = $this->DAO->get_user_visit($params['act_id'],$params['user_id']);
            if(!$visit){
                $result['code'] = 2;
                $result['msg'] = "亲爱的用户，您还未分享活动或者好友还未点击您的分享链接, 登录后分享出去的链接才有效哦～";
                die("0".base64_encode(json_encode($result)));
            }
        }
        $gift = $this->DAO->get_gift_list($batch_id);
        if($gift){
            $this->DAO->update_gift_status($gift,$this->client_ip(),$game_id, $params['user_id']);
            $result['code'] = 1;
            $result['giftCode'] = $gift['gift_code'];
            die("0".base64_encode(json_encode($result)));
        }else{
            $result['code'] = 3;
            $result['msg'] = "来晚一步，礼包已抢光，请关注更多精彩活动。";
            die("0".base64_encode(json_encode($result)));
        }
    }

    //登录base64加密
    public function login_secret(){
        $result = array("code"=>0,"msg"=>'网络请求出错.');
        $mobile = $_POST['mobile'];
        $hash = $_POST['pagehash'];
        if(time()-$_SESSION['login_time'] < 5){
            $result['msg'] = '操作太频繁，请重新输入';
            die("0".base64_encode(json_encode($result)));
        }
        if($_SESSION['page-hash'] != $hash){
            $result['msg'] = '参数异常！ 001';
            die("0".base64_encode(json_encode($result)));
        }
        $source = 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
        if(!$this->is_mobile($mobile)){
            $result['msg'] = '手机号码格式不正确';
            die("0".base64_encode(json_encode($result)));
        }
        if(!isset($_SESSION['reg_core']) ||
            $_SESSION['reg_core']!=$_POST['mobile']."_".$_POST['sms_code'] ||
            $_POST['sms_code']==""){
            $result['msg'] = '验证码错误';
            die("0".base64_encode(json_encode($result)));
        }
        if(strtotime("now") - $_SESSION['last_send_time']>300){
            $result['msg'] = '验证码超时';
            die("0".base64_encode(json_encode($result)));
        }
        //验收是否是旧用户
        $usr_info = $this->DAO->get_user_by_mobile($mobile);
        if($usr_info){
            //登录
            $this->DAO->insert_login_log($usr_info['user_id'],$mobile,$this->client_ip(),$usr_info['password'],$source,$_SERVER['HTTP_USER_AGENT'],"验证码登录成功","1");
            $_SESSION['old_code'] = $_POST['code'];
            $_SESSION['user_id'] = $usr_info['user_id'];
            $_SESSION['nick_name'] = $usr_info['nick_name'];
            $_SESSION['mobile'] = $usr_info['mobile'];
            $_SESSION['sex'] = $usr_info['sex'];
            $_SESSION['email'] = $usr_info['email'];
            $_SESSION['birthday'] = $usr_info['birthday'];
            $_SESSION['buy_mobile'] = $usr_info['buy_mobile'];
            $_SESSION['buy_qq'] = $usr_info['qq'];
            $_SESSION['guid'] = $usr_info['guid'];
            $_SESSION['code'] = $usr_info['user_id'];
            $_SESSION['is_agent'] = $usr_info['is_agent'];
            $result = array("code"=>1,"msg"=>'登录成功.','share_code'=>$usr_info['user_id']);
        }else{
            unset($_SESSION['user_id']);
            unset($_SESSION['nick_name']);
            unset($_SESSION['mobile']);
            unset($_SESSION['sex']);
            unset($_SESSION['email']);
            unset($_SESSION['birthday']);
            unset($_SESSION['qq_openid']);
            unset($_SESSION['core']);
            unset($_SESSION['act_id']);
            //注册
            $password = rand(100000,999999);
            $usr_id = $this->account_dao->insert_user_info(md5($password),$mobile,$this->client_ip());
            sleep(1);
            //短信推送注册信息
            $send_result = $this->send_sms($mobile,array($password),"201718"); //短信模板ID
            $this->err_log(var_export($send_result,1),'register_sms_m');
            $_SESSION['old_code'] = $_POST['code'];
            $_SESSION['user_id'] = $usr_id;
            $_SESSION['nick_name'] = "";
            $_SESSION['mobile'] = $mobile;
            $_SESSION['sex'] = 0;
            $_SESSION['email'] = "";
            $_SESSION['birthday'] = "";
            $_SESSION['buy_mobile'] = $mobile;
            $_SESSION['buy_qq'] = "";
            $_SESSION['is_agent'] = 0;
            $_SESSION['code'] = $usr_id;
            $result = array("code"=>1,"msg"=>'注册成功.','share_code'=>$usr_id);
        }
        die("0".base64_encode(json_encode($result)));
    }

    //验证码base64加密
    public function sms_code_secret(){
        $phone = $_POST['mobile'];
        $nowtime = strtotime("now");
        $hash = $_POST['pagehash'];
        if($_SESSION['page-hash'] != $hash){
            $result['code'] = 0;
            $result['msg'] = '参数异常！ 001';
            die("0".base64_encode(json_encode($result)));
        }
        if(!$phone){
            $msg['res'] = "0";//1成功0失败
            $msg['msg'] = "请输入您的手机号";
            die("0".base64_encode(json_encode($msg)));
        }

        if(!$this->is_mobile($phone)){
            $msg['res'] = "0";//1成功0失败
            $msg['msg'] = "验证消息发送失败,请重试";
            die("0".base64_encode(json_encode($msg)));
        }
        if(isset($_SESSION['last_send_time'])){
            if($nowtime-$_SESSION['last_send_time']<180){
                $msg['res'] = "0";//1成功0失败
                $msg['msg'] = "获取验证码太频繁，请稍后再试";
                die("0".base64_encode(json_encode($msg)));
            }else{
                $_SESSION['last_send_time'] = $nowtime;
            }
        }else{
            $_SESSION['last_send_time'] = $nowtime;
        }
        $code = str_split($_SERVER["REQUEST_TIME"] .rand(1001, 9999));
        $code = substr(implode('', array_rand($code, 6)), 0, 6);
        $send_result = $this->send_sms($phone,array($code),"201723");
        $this->err_log(var_export($send_result,1),'sms');
        //测试专用
//        $_SESSION['reg_core'] = $phone."_".$code;
//        $msg['res'] = "1";//1成功0失败
//        $msg['msg'] = "验证消息发送成功！";
//        $msg['code'] = $code;
//        die("0".base64_encode(json_encode($msg)));
        if($send_result){
            $_SESSION['reg_core'] = $phone."_".$code;
            $msg['res'] = "1";//1成功0失败
            $msg['msg'] = "验证消息发送成功！";
//            $msg['code'] = $code;
            die("0".base64_encode(json_encode($msg)));
        }else{
            $msg['res'] = "0";//1成功0失败
            $msg['msg'] = "验证消息发送失败,请重试2";
            die("0".base64_encode(json_encode($msg)));
        }
    }

    public function app_promote(){
        //牛果APP推广页
        $this->wx_share();
        $this->display("website/app_promote.html");
    }

    public function get_gift(){
        $type = $_POST['type'];
        if ($type=='1'){
            $batch_id = 430;//安卓礼包
        }elseif($type=='2'){
            $batch_id = 431;//iOS礼包
        }else{
            $result['code'] = 2;
            $result['msg'] = '礼包类型异常';
            die(json_encode($result));
        }
        $ip_code = $this->DAO->get_reserve_ip_code($this->client_ip(),$batch_id);
        if(count($ip_code) > 1){
            $result['code'] = 1;
            $result['giftCode'] = $ip_code[0]['code'];
            $result['giftCode_1'] = $ip_code[1]['code'];
            $result['msg'] = '已领取过';
            die(json_encode($result));
        }
        $gift = $this->DAO->get_gift_list($batch_id);
        if($gift){
            $this->DAO->update_gift_status($gift,$this->client_ip(),'1821', '71');
            $result['code'] = 1;
            $result['giftCode'] = $gift['gift_code'];
//            $_SESSION['yh_user_code_'.$batch_id]= $gift['gift_code'];
            $result['msg'] = '领取成功';
            die(json_encode($result));
        }else{
            $result['code'] = 2;
            $result['msg'] = "下一波礼包正在路上";
            die(json_encode($result));
        }


    }

    public function zxsy($act_id,$code){
        $game_id = 1822;
        $batch_id_new = 432;//新手礼包
        $batch_id_privilege = 433;//特权礼包
        $_SESSION['logout_back'] = $_SERVER['REQUEST_URI'];
        if(!$_SESSION['user_id'] || !$_SESSION['mobile']){
            unset($_SESSION['user_id']);
            unset($_SESSION['nick_name']);
            unset($_SESSION['mobile']);
            unset($_SESSION['sex']);
            unset($_SESSION['email']);
            unset($_SESSION['birthday']);
            unset($_SESSION['qq_openid']);
            unset($_SESSION['core']);
            unset($_SESSION['act_id']);
        }
        $_SESSION['act_id'] = $act_id;
        unset($_SESSION['core']);
        if(!empty($code)){
            $_SESSION['core'] = $code;
        }
        if(!$act_id || $act_id != $game_id){
            die("活动链接出错啦");
        }
        if($_SESSION['old_code'] && $_SESSION['old_code'] != $code){
            $this->DAO->insert_open_log($act_id,$_SERVER['REQUEST_URI'],$_SESSION['old_code']);
            unset($_SESSION['old_code']);
        }else{
            $this->DAO->insert_open_log($act_id,$_SERVER['REQUEST_URI'],'');
        }
        $user_id = $_SESSION['user_id'];
        if($user_id){
            $gift_list_new = $this->DAO->get_gifts($batch_id_new,$user_id);
            $gift_list_privilege = $this->DAO->get_gifts($batch_id_privilege,$user_id);
            $this->assign("gift_list_new",$gift_list_new);
            $this->assign("gift_list_privilege",$gift_list_privilege);
        }
        $game_info = $this->DAO->get_game_info($game_id);
        $this->wx_share();
        $this->page_hash();
        $this->assign("code",$code);
        $this->assign("game_info",$game_info);
        $this->display("website/zxsy.html");
    }

    //领取礼包base64加密
    public function receive_gift_secret(){
        $game_id = 1822;
        $params = $_POST;
        $result = array('code'=> 4,'msg'=>'参数异常！','type'=>$params['type']);
        $hash = $params['pagehash'];
        if($_SESSION['page-hash'] != $hash){
            $result['msg'] = '参数异常！ 001';
            die("0".base64_encode(json_encode($result)));
        }
        if(empty($params['user_id'])){
            $result['code'] = 0;
            $result['msg'] = '缺少用户登录参数！';
            die("0".base64_encode(json_encode($result)));
        }
        if($params['user_id'] != $_SESSION['user_id'] || $params['act_id'] != $_SESSION['act_id'] || $game_id != $params['act_id']){
            $result['msg'] = '参数异常！ 001';
            die("0".base64_encode(json_encode($result)));
        }
        if($params['type']=='login'){
            $batch_id = 432;//新手礼包
        }elseif($params['type']=='share'){
            $batch_id = 433;//特权礼包
        }else{
            $result['msg'] = '参数异常,类型错误！';
            die("0".base64_encode(json_encode($result)));
        }
        $ip_draw_sum = $this->DAO->get_reserve_ip_sum($this->client_ip(),$batch_id);
        if($ip_draw_sum['num'] > 3){
            $result['msg'] = 'ip异常，请联系客服！';
            die("0".base64_encode(json_encode($result)));
        }
        $user_info = $this->DAO->get_user_info($params['user_id']);
        if(empty($user_info) || !$user_info['mobile']){
            $result['code'] = 0;
            $result['msg'] = '用户参数异常！';
            die("0".base64_encode(json_encode($result)));
        }
        $start_time = strtotime("2018-04-15");
        $end_time = strtotime("2018-12-30");
        if($start_time > time()){
            $result['code'] = 3;
            $result['msg'] = "活动未开始，敬请期待";
            die("0".base64_encode(json_encode($result)));
        }else if(time() >  $end_time){
            $result['code'] = 3;
            $result['msg'] = "活动已结束，请关注更多精彩活动";
            die("0".base64_encode(json_encode($result)));
        }
        $gift_info = $this->DAO->get_gift_info($batch_id,$params['user_id'],$game_id);
        $gift = $this->DAO->get_gifts($batch_id,$params['user_id']);
        if($gift_info || $gift){
            $result['code'] = 2;
            $result['msg'] = "您已领取过，不能再领取了";
            die("0".base64_encode(json_encode($result)));
        }
        if($params['type']=='share'){
            //验证用户是否分享
            $visit = $this->DAO->get_user_visit($params['act_id'],$params['user_id']);
            if(!$visit){
                $result['code'] = 2;
                $result['msg'] = "亲爱的用户，您还未分享活动或者好友还未点击您的分享链接, 登录后分享出去的链接才有效哦～";
                die("0".base64_encode(json_encode($result)));
            }
        }
        $gift = $this->DAO->get_gift_list($batch_id);
        if($gift){
            $this->DAO->update_gift_status($gift,$this->client_ip(),$game_id, $params['user_id']);
            $result['code'] = 1;
            $result['msg'] = '领取成功';
            $result['type'] = $params['type'];
            $result['giftCode'] = $gift['gift_code'];
            die("0".base64_encode(json_encode($result)));
        }else{
            $result['code'] = 3;
            $result['msg'] = "来晚一步，礼包已抢光，请关注更多精彩活动。";
            die("0".base64_encode(json_encode($result)));
        }
    }

    public function lhrg($act_id,$code){
        $game_id = 1823;
        $batch_id_new = 434;//新手礼包
        $batch_id_privilege = 435;//特权礼包
        $batch_id_unique = 436;//独家礼包
        $_SESSION['logout_back'] = $_SERVER['REQUEST_URI'];
        if(!$_SESSION['user_id'] || !$_SESSION['mobile']){
            unset($_SESSION['user_id']);
            unset($_SESSION['nick_name']);
            unset($_SESSION['mobile']);
            unset($_SESSION['sex']);
            unset($_SESSION['email']);
            unset($_SESSION['birthday']);
            unset($_SESSION['qq_openid']);
            unset($_SESSION['core']);
            unset($_SESSION['act_id']);
        }
        $_SESSION['act_id'] = $act_id;
        unset($_SESSION['core']);
        if(!empty($code)){
            $_SESSION['core'] = $code;
        }
        if(!$act_id || $act_id != $game_id){
            die("活动链接出错啦");
        }
        if($_SESSION['old_code'] && $_SESSION['old_code'] != $code){
            $this->DAO->insert_open_log($act_id,$_SERVER['REQUEST_URI'],$_SESSION['old_code']);
            unset($_SESSION['old_code']);
        }else{
            $this->DAO->insert_open_log($act_id,$_SERVER['REQUEST_URI'],'');
        }
        $user_id = $_SESSION['user_id'];
        if($user_id){
            $gift_list_new = $this->DAO->get_gifts($batch_id_new,$user_id);
            $gift_list_privilege = $this->DAO->get_gifts($batch_id_privilege,$user_id);
            $gift_list_unique = $this->DAO->get_gifts($batch_id_unique,$user_id);
            $share_num = $this->DAO->get_share_num($game_id,$user_id);
            $this->assign("share_num",$share_num);
            $this->assign("gift_list_new",$gift_list_new);
            $this->assign("gift_list_privilege",$gift_list_privilege);
            $this->assign("gift_list_unique",$gift_list_unique);
        }
        $game_info = $this->DAO->get_game_info($game_id);
        $this->wx_share();
        $this->page_hash();
        $this->assign("code",$code);
        $this->assign("game_info",$game_info);
        $this->display('website/lhrg.html');
    }

    public function lhrg_draw_gift(){
        $game_id = 1823;
        $params = $_POST;
        $result = array('code'=> 4,'msg'=>'参数异常！','type'=>$params['type']);
        $hash = $params['pagehash'];
        if($_SESSION['page-hash'] != $hash){
            $result['msg'] = '参数异常！ 001';
            die("0".base64_encode(json_encode($result)));
        }
        if(empty($params['user_id'])){
            $result['code'] = 0;
            $result['msg'] = '缺少用户登录参数！';
            die("0".base64_encode(json_encode($result)));
        }
        if($params['user_id'] != $_SESSION['user_id'] || $params['act_id'] != $_SESSION['act_id'] || $game_id != $params['act_id']){
            $result['msg'] = '参数异常！ 001';
            die("0".base64_encode(json_encode($result)));
        }
        if($params['type']=='login'){
            $batch_id = 434;//新手礼包
        }elseif($params['type']=='privilege'){
            $batch_id = 435;//特权礼包
        }elseif($params['type']=='unique'){
            $batch_id = 436;//独家礼包
        }else{
            $result['msg'] = '参数异常,类型错误！';
            die("0".base64_encode(json_encode($result)));
        }
        $ip_draw_sum = $this->DAO->get_reserve_ip_sum($this->client_ip(),$batch_id);
        if($ip_draw_sum['num'] > 3){
            $result['msg'] = 'ip异常，请联系客服！';
            die("0".base64_encode(json_encode($result)));
        }
        $user_info = $this->DAO->get_user_info($params['user_id']);
        if(empty($user_info) || !$user_info['mobile']){
            $result['code'] = 0;
            $result['msg'] = '用户参数异常！';
            die("0".base64_encode(json_encode($result)));
        }
        $start_time = strtotime("2018-04-20");
        $end_time = strtotime("2018-05-30");
        if($start_time > time()){
            $result['code'] = 3;
            $result['msg'] = "活动未开始，敬请期待";
            die("0".base64_encode(json_encode($result)));
        }else if(time() >  $end_time){
            $result['code'] = 3;
            $result['msg'] = "活动已结束，请关注更多精彩活动";
            die("0".base64_encode(json_encode($result)));
        }
        $gift_info = $this->DAO->get_gift_info($batch_id,$params['user_id'],$game_id);
        $gift = $this->DAO->get_gifts($batch_id,$params['user_id']);
        if($gift_info || $gift){
            $result['code'] = 2;
            $result['msg'] = "您已领取过，不能再领取了";
            die("0".base64_encode(json_encode($result)));
        }
        //验证用户是否分享
        $visit = $this->DAO->get_share_num($params['act_id'],$params['user_id']);
        if($params['type']=='privilege'){
            if(!$visit['num']){
                $result['code'] = 2;
                $result['msg'] = "亲爱的用户，您还未分享活动或者好友还未点击您的分享链接, 登录后分享出去的链接才有效哦～";
                die("0".base64_encode(json_encode($result)));
            }
        }
        if($params['type']=='unique'){
            if($visit['num']<3){
                $result['code'] = 2;
                $result['msg'] = "亲爱的用户，您还未分享活动或者还未达到三个好友点击您的分享链接, 登录后分享出去的链接才有效哦～";
                die("0".base64_encode(json_encode($result)));
            }
        }
        $gift = $this->DAO->get_gift_list($batch_id);
        if($gift){
            $this->DAO->update_gift_status($gift,$this->client_ip(),$game_id, $params['user_id']);
            $result['code'] = 1;
            $result['msg'] = '领取成功';
            $result['type'] = $params['type'];
            $result['giftCode'] = $gift['gift_code'];
            die("0".base64_encode(json_encode($result)));
        }else{
            $result['code'] = 3;
            $result['msg'] = "来晚一步，礼包已抢光，请关注更多精彩活动。";
            die("0".base64_encode(json_encode($result)));
        }
    }

    public function cqll(){
        $this->display('website/cqll.html');
    }
}