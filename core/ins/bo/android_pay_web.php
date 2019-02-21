<?php
COMMON('sdkCore','uploadHelper','alipay_mobile/alipay_service','alipay_mobile/alipay_notify');
DAO('android_pay_dao');

class android_pay_web extends sdkCore{

    public $DAO;
    public $qa_user_id;

    public function __construct(){
        parent::__construct();
        $this->DAO = new android_pay_dao();
        $this->qa_user_id = array(2233916,1441047,1883479,57);
        if(isset($_SERVER['HTTP_USER_AGENT1'])){
            $header = base64_decode(substr($_SERVER['HTTP_USER_AGENT1'],1));
            $header = explode("&",$header);
            foreach($header as $k=>$param){
                $param = explode("=",$param);

                if($param[0] == 'sdkver'){
                    $this->sdkver = $param[1];
                }elseif($param[0] == 'channel'){
                    $this->guild_code = $param[1];
                }elseif($param[0] == 'ver'){
                    $this->gameVer = $param[1];
                }
            }
        }
    }

    public function ali_return(){
        $status = 0;
        if($_SESSION['ali_status']==1){
            $status = 1;
        }
        $this->assign("desc", $_SESSION['msg']);
        $this->assign("pop_type", $status);
        $this->V->display("ali_return.html");
    }

    public function view($good_id= ''){
        $USR_HEADER = $this->get_usr_session('USR_HEADER');
        $app_id = $USR_HEADER['app_id'];
        $MSG = $this->get_usr_session('msg');
        $SUCCESS = $this->get_usr_session('success');
        $pay_result = $this->get_usr_session('pay_result');

        if(!$USR_HEADER){
            $this->V->assign("msg", "错误：游戏参数丢失");
            $this->V->display("error.html");
            EXIT;
        }
        $app_info = $this->DAO->get_app_info($app_id);
        $goods = $this->DAO->get_good_data($app_id);
        $goods_info = $this->DAO->get_good_info($app_id,$good_id);
        if(!$goods_info){
            $this->V->assign("msg", "错误：未能获取到商品id");
            $this->V->display("error.html");
            EXIT;
        }elseif(!empty($USR_HEADER['channel'])){

            $good_discount = $this->DAO->get_good_discount($app_id,$USR_HEADER['channel']);
            if(!$good_discount){
                $goods_info['discount'] = 10;
                $goods_info['pay_price'] = $goods_info['good_price'];
            }else{
                $goods_info['discount'] = !empty($good_discount['discount']) ? $good_discount['discount'] : 10;
                $goods_info['pay_price'] = number_format($goods_info['good_price'] * $goods_info['discount'] / 10, 1, '.', '');
            }
        }else{
            $goods_info['discount'] = 10;
            $goods_info['pay_price'] = $goods_info['good_price'];
        }
        if(!empty($USR_HEADER['goodmultiple'])){
            $goods_info['pay_price'] = $goods_info['pay_price'] * $USR_HEADER['goodmultiple'];
            $goods_info['good_price'] = $goods_info['good_price'] * $USR_HEADER['goodmultiple'];
        }
        $userDao = new user_dao();
        $user_info = $userDao->get_user_info($USR_HEADER['usr_id']);
        if(!$user_info || empty($user_info['nnb'])){
            $user_info['nnb']=0;
        }
        $status=0;
        if(empty($user_info['nnb']) || ($user_info['nnb']<$goods_info['pay_price'])){
            $status=1;
        }
        $pay_status = 0;
        if($app_info['payee_ch'] > '2' && $goods_info['pay_price'] > '3000'){
            $pay_status = 1;
        }
        $this->get_user_game_nd($USR_HEADER['usr_id'],$USR_HEADER['app_id'],$goods_info['good_price']);

        $this->V->assign("ip", $this->client_ip());
        $this->V->assign("pay_status", $pay_status);
        $this->V->assign("status", $status);
        $this->V->assign("user_info", $user_info);
        $this->V->assign("param", $USR_HEADER);
        $this->V->assign("goods_info", $goods_info);
        $this->V->assign("goods", $goods);
        $this->V->assign("msg", $MSG);
        $this->V->assign("success", $SUCCESS);
        $this->V->assign("pay_result", $pay_result);
        $this->V->assign("good_id", $good_id);
        $this->V->assign("sdkver", $USR_HEADER['sdkver']);
        if($USR_HEADER['app_id']=='5001'){
            $this->display("pay_index3.html");
        }elseif($USR_HEADER['sdkver']>'2.0.0.0'){
            $this->display("pay_index4.html");
        }else{
            $this->display("pay_index2.html");
        }

        $this->set_usr_session('msg','');
        $this->set_usr_session('success','');
        $this->set_usr_session('pay_result','');
    }

    public function get_user_game_nd($usr_id,$app_id,$pay_money){
        $nd_stats = 0;
        $user_info = $this->DAO->get_nd_user_info($usr_id,$app_id);
        if ($user_info['nd_num'] >= $pay_money && $user_info['nd_lock'] == '0') {
            $nd_stats = 1;
            $this->V->assign("nd_num", $user_info['nd_num']);
        }
        $this->V->assign("nd_stats", $nd_stats);
    }

    public function super_pay($good_id= ''){
        $USR_HEADER = $this->get_usr_session('USR_HEADER');
        $app_id = $USR_HEADER['app_id'];
        $MSG = $this->get_usr_session('msg');
        $SUCCESS = $this->get_usr_session('success');
        $pay_result = $this->get_usr_session('pay_result');

        if(!$USR_HEADER){
            $this->V->assign("msg", "错误：游戏参数丢失");
            $this->V->display("error.html");
            EXIT;
        }

        $goods_info = $this->DAO->get_super_good_info($app_id,$good_id);
        if(!$goods_info){
            $this->V->assign("msg", "错误：未能获取到商品id");
            $this->V->display("error.html");
            EXIT;
        }else{
            $goods_info['discount'] = 10;
            $goods_info['pay_price'] = $goods_info['good_price'];
            if(!empty($USR_HEADER['goodmultiple'])){
                $goods_info['pay_price'] = $goods_info['good_price'] * $USR_HEADER['goodmultiple'];
            }
        }
        $userDao = new user_dao();
        $user_info = $userDao->get_user_info($USR_HEADER['usr_id']);
        $this->V->assign("user_info", $user_info);
        $this->V->assign("param", $USR_HEADER);
        $this->V->assign("goods_info", $goods_info);
        $this->V->assign("msg", $MSG);
        $this->V->assign("success", $SUCCESS);
        $this->V->assign("pay_result", $pay_result);
        $this->V->assign("good_id", $good_id);
        $this->display("super_pay_view.html");

        $this->set_usr_session('msg','');
        $this->set_usr_session('success','');
        $this->set_usr_session('pay_result','');
    }

    public function ali_merchant($order_id, $result, $errmsg){
        if($result==1 && $order_id){
            $this->V->display("pay_succeed.html");
        }elseif($result==2 && $order_id && $errmsg){
            $this->V->assign("errmsg", $errmsg);
            $this->V->display("pay_fail.html");
        }
    }
    public function nnb_merchant($order_id, $result, $errmsg,$user_id){
        if($result==1 && $order_id){
            if($user_id){
                $userDao=new user_dao();
                $user_info = $userDao->get_user_info($user_id);
                $this->V->assign("user_info", $user_info);
            }
            $this->V->display("nnb_succeed.html");
        }elseif($result==2 && $order_id && $errmsg){
            $this->V->assign("errmsg", $errmsg);
            $this->V->display("nnb_fail.html");
        }
    }

    //同步通知
    public function ali_callback(){
        $msg ='';
        $success ='';
        // 构造通知函数信息
        $alipay = new alipay_notify(ALI_MOBILE_partner, ALI_MOBILE_key, ALI_MOBILE_sec_id, ALI_MOBILE_input_charset);
        // 计算得出通知验证结果
        $verify_result = $alipay->return_verify();

        if ($verify_result) {
        	$order_id       = $_GET ['out_trade_no']; // 外部交易号
        	$order_result   = $_GET ['result']; // 订单状态，是否成功
        	$ali_order_id   = $_GET ['trade_no']; // 交易号

        	if ($order_result == 'success') {
                $order = $this->DAO->get_order_info($order_id);

                if($order['Status']==0){
                    $this->DAO->update_order_success($order_id, $ali_order_id);
                    $success = "[订单号：".$order_id."]<br /><span class=\"green\">恭喜您，充值成功，稍后进入游戏查看。</span>";
                    $this->set_usr_session("pay_result", $order_id);
                }elseif($order['Status']==1){
                    $success = "[订单号：".$order_id."]<br /><span class=\"green\">恭喜您，充值成功，稍后进入游戏查看。</span>";
                    $this->set_usr_session("pay_result", $order_id);
                }elseif($order['Status']==2){
                    $success = "[订单号：".$order_id."]<br /><span class=\"green\">恭喜您，充值成功，可以进入游戏查看。</span>";
                    $this->set_usr_session("pay_result", $order_id);
                }else{
                    $msg = "[订单号：".$order_id."]<br />支付宝验证发生错误，请重试。<br /><span class=\"red\">[错误代码：10001]</span>";
                }
        	} else {
                //$this->DAO->update_order_success($order_id, $ali_order_id, '', 3);
                $msg = "支付宝充值失败，请重试。<br /><span class=\"red\">[订单号：".$order_id."]</span><br /><span class=\"red\">[错误代码：10003]</span>";
        	}
        } else {
            $msg = "支付宝验证发生错误，请重试。<br /><span class=\"red\">[错误代码：10001]</span>";
        }

        $this->set_usr_session("msg", $msg);
        $this->set_usr_session("success", $success);
        $this->view();
    }

    public function set_usr_session($key, $data){
        $this->DAO->set_usr_session($key, $data);
    }

    public function get_usr_session($key=''){
        return $this->DAO->get_usr_session($key);
    }

    public function check_order_status($orderid){
        $order = $this->DAO->get_order_info($orderid);
        $status = '订单支付出现异常,请重试。';
        if($order){
            switch($order['Status']){
                case 1:
                    $status = '订单支付完成';
                    break;
                case 2 :
                    $status = '订单支付完成，道具已发放。';
                    break;
                case 3:
                    $status = '订单支付失败';
                    break;
                case 4:
                    $status = '订单交易已取消';
                    break;
                case 0:
                    $status = '订单还未支付';
                    break;
            }
        }
        echo '0'.base64_encode(json_encode(array("status" => $status)));
    }

    public function update_order_status($orderid, $status, $third_orderid){
        $order = $this->DAO->get_order_info($orderid);
        if($order && $order['Status'] == 0){
            $this->DAO->update_order_success($orderid, $third_orderid, '', $status);
        }
    }

    public function niu_pay_list(){
        $USR_HEADER = $this->get_usr_session('USR_HEADER');
        if(!$USR_HEADER||!$USR_HEADER['usr_id']){
            $this->V->assign("msg", "错误：缺少游戏参数丢失!");
            $this->V->display("error.html");
            EXIT;
        }
        //获取用户的牛币
        $userDao = new user_dao();
        $user_info = $userDao->get_user_info($USR_HEADER['usr_id']);
        $this->V->assign("ip", $this->client_ip());
        $this->V->assign("user_info", $user_info);
        $this->display("niu_pay_list.html");
    }

    public function nnb_recharge_list($user_id){
        $USR_HEADER = $this->get_usr_session('USR_HEADER');
        if(empty($user_id)){
            $user_id = $USR_HEADER['user_id'];
        }
        if($user_id) {
            $userDao = new user_dao();
            $user_info = $userDao->get_user_info($user_id);
            $nnb_log = $userDao->get_user_nnb_log($user_id,1);
            $this->V->assign("app_id", $USR_HEADER['app_id']);
            $this->V->assign("nnb_log", $nnb_log);
            $this->V->assign("user_info", $user_info);
        }
        $this->V->display('nnb_pay_list.html');
    }

    public function nnb_recharge_more(){
        $USR_HEADER = $this->get_usr_session('USR_HEADER');
        $result = array(
            'result'=> 0,
            'desc'=> '网络异常。',
            'info'=> ''
        );
        if(!$USR_HEADER['user_id']){
            $result['desc'] = '参数异常。';
            die(json_encode($result));
        }
        if($_POST['page'] > 1){
            $this->page = $_POST['page'];
        }else{
            $this->page = 2;
        }
        $userDao = new user_dao();
        $nnb_log = $userDao->get_user_nnb_log($USR_HEADER['user_id'],$this->page);
        if($nnb_log){
            $result['result'] = 1;
            $result['desc'] = '查询成功。';
            $result['info'] = $nnb_log;
        }else{
            $result['result'] = 1;
            $result['desc'] = '查询成功';
            $result['info'] = "";
        }
        die(json_encode($result));
    }

    public function pay_list($user_id){
        $USR_HEADER = $this->get_usr_session('USR_HEADER');
        if(empty($user_id)){
            $user_id = $USR_HEADER['user_id'];
        }
        if($user_id) {
            $status = -1;
//            if (isset($_POST['status']) && intval($_POST['status']) > -1) {
//                $status = intval($_POST['status']);
//            }
            $order_list = $this->DAO->order_list($user_id, $status,$this->page);
            foreach ($order_list as $key => $order) {
                $app = $this->DAO->get_app_info($order['app_id']);
                $order_list[$key] = array_merge($order, array('AppName' => $app['game_name'], 'en_name' => $app['en_name']));
            }
            $this->V->assign("status", $status);
            $this->V->assign("order_list", $order_list);
        }
        $this->V->display('pay_list.html');
    }

    public function pay_more(){
        $USR_HEADER = $this->get_usr_session('USR_HEADER');
        $result = array(
            'result'=> 0,
            'desc'=> '网络异常。',
            'info'=> ''
        );
        if($_POST['page'] > 1){
            $this->page = $_POST['page'];
        }else{
            $this->page = 2;
        }
        if(!$USR_HEADER['user_id']){
            $result['desc'] = '参数异常。';
            die(json_encode($result));
        }else{
            $status = -1;
//            if (isset($_POST['status']) && intval($_POST['status']) > -1) {
//                $status = intval($_POST['status']);
//            }
            $order_list = $this->DAO->order_list($USR_HEADER['user_id'], $status, $this->page);
            foreach ($order_list as $key => $order) {
                $app = $this->DAO->get_app_info($order['app_id']);
                $order_list[$key] = array_merge($order, array('AppName' => $app['game_name'], 'en_name' => $app['en_name']));
            }
        }
        if($order_list){
            $result['result'] = 1;
            $result['desc'] = '查询成功。';
            $result['info'] = $order_list;
        }else{
            $result['result'] = 1;
            $result['desc'] = '查询成功';
            $result['info'] = "";
        }
        die(json_encode($result));
    }

    public function check_user_agent($user_agent, $app_id){
        $app_info = $this->DAO->get_app_info($app_id);
        if(!$app_info){
            die("参数错误");
        }

        $ua = rawurldecode($user_agent);
        $ua = base64_decode($ua);

        $decrypted = mcrypt_decrypt(MCRYPT_RIJNDAEL_128, $app_info['app_key'], $ua, MCRYPT_MODE_CBC, AES_IV);

        $header = explode("&", $decrypted);
        return $header;
    }

    public function super_check_user_agent($user_agent, $app_id){
        $app_info = $this->DAO->get_super_app_info($app_id);
        if(!$app_info){
            die("参数错误");
        }

        $ua = rawurldecode($user_agent);
        $ua = base64_decode($ua);

        $decrypted = mcrypt_decrypt(MCRYPT_RIJNDAEL_128, $app_info['app_key'], $ua, MCRYPT_MODE_CBC, AES_IV);

        $header = explode("&", $decrypted);
        return $header;
    }


    public function game_notice(){
        $app_id = $this->usr_params['app_id'];
        $apple_id = '';
        if(!$app_id){
            die("参数错误");
        }
        if(!empty($this->usr_params['apple_id'])){
            $apple_id = $this->usr_params['apple_id'];
        }
        if(!empty($apple_id)){
            $app_info = $this->DAO->get_apple_info($app_id,$apple_id);
        }
        if(empty($app_info)){
            $app_info = $this->DAO->get_app_info($app_id);
        }
        if($app_info['notice_status']==1){
            if($this->usr_params['channel']=='lydj' && $this->usr_params['app_id']=='1076'){
                $app_info['app_name']='嘟嘟私服176';
            }
            if($this->usr_params['channel']=='cqll' && $this->usr_params['app_id']=='1076'){
                $app_info['app_name']='热血龙城';
            }
            $this->assign("info", $app_info);
            $this->display("game_notice.html");
        }
    }

    public function s_notice(){
        $super_id = $this->usr_params['app_id'];
        $channel = $this->usr_params['channel'];
        if(!$super_id){
            die("缺少必须要参数");
        }
        if(!$channel){
            die("渠道信息异常");
        }
        $app_info = $this->DAO->get_super_channel_info($super_id,$channel);
        if(empty($app_info)){
            die("信息获取失败");
        }
        if($app_info['notice_status']==1){
            $this->assign("info", $app_info);
            $this->display("game_notice.html");
        }
    }

    public function gw_verify($ver=''){
        $ver = $this->format_version($ver);
        $app_id = $this->usr_params['app_id'];
        $apple_id = '';
        if(!$app_id){
            die("参数错误");
        }
        if(!empty($this->usr_params['apple_id'])){
            $apple_id = $this->usr_params['apple_id'];
        }
        if(!empty($apple_id)){
            $app_info = $this->DAO->get_apple_info($app_id,$apple_id);
        }
        if(empty($app_info)){
            $app_info = $this->DAO->get_app_info($app_id);
        }
        $result = array("result"=>0,
            "desc" => "未匹配到",
            "is_open" => "0",
            "gw_dz" => ""
        );

        if(!empty($ver) && ($app_info['version'] >= $ver)){
            $new_ip = explode(".",$this->client_ip());
            $result["result"] = 1;
            if(empty($app_info['web_status'])){
                $result["gw_dz"] = $app_info['web_url'];
                $result["desc"] = '数据查询成功';
            }elseif($app_info['web_status']== '1'){
                $order_info = $this->DAO->get_sid_in_apple_order($this->usr_params['sid']);
                $result["desc"] = '查询成功！！！';
                if($order_info){
                   $result["gw_dz"] = $app_info['web_url'];
               }
            }
            if($new_ip[0]=='17'){
                $result["result"] = 0;
                $result["gw_dz"] = '';
                $result["is_open"] = '1';
            }
        }
        die("0".base64_encode(json_encode($result)));
    }

    public function web_verify($ver=''){
        $ver = $this->format_version($ver);
        $app_id = $this->usr_params['app_id'];
        $apple_id = '';
        if(!$app_id){
            die("参数错误");
        }
        if(!empty($this->usr_params['apple_id'])){
            $apple_id = $this->usr_params['apple_id'];
        }
        if(!empty($apple_id)){
            $app_info = $this->DAO->get_apple_info($app_id,$apple_id);
        }
        if(empty($app_info)){
            $app_info = $this->DAO->get_app_info($app_id);
        }
        $result = array("result"=>0,
            "desc" => "未匹配到",
            "is_open" => "0",
            "web_url" => ""
            );

        if(!empty($ver) && ($app_info['version'] >= $ver)){
            $new_ip = explode(".",$this->client_ip());
            $result["result"] = 1;
            $result["web_url"] = $app_info['web_url'];
            if($new_ip[0]=='17'){
                $result["result"] = 0;
                $result["web_url"] = '';
                $result["is_open"] = '1';
            }
        }
        die("0".base64_encode(json_encode($result)));
    }

    public function sdk_version($ver=''){
        $ver = $this->format_version($ver);
        $app_id = $this->usr_params['app_id'];
        $apple_id = '';
        if(!$app_id){
            die("参数错误");
        }
        if(!empty($this->usr_params['apple_id'])){
            $apple_id = $this->usr_params['apple_id'];
        }
        if(!empty($apple_id)){
            $app_info = $this->DAO->get_apple_info($app_id,$apple_id);
        }
        if(empty($app_info)){
            $app_info = $this->DAO->get_app_info($app_id);
        }
        $result = array("result"=>1,
            "desc" => "",
            "type" => 1,
            "is_notice" => $app_info['notice_status'],
            "notice_dz" => "http://ins.66173.cn/notice.php",
            "gw_dz" => '',
            "is_open" => '0',
            "autonym"=> $app_info['autonym']?$app_info['autonym']:'',
            "up_dz" => "");

        if(!empty($ver) && ($app_info['version'] >= $ver)){
            $new_ip = explode(".",$this->client_ip());
            $sdk_ver = $this->format_version($this->usr_params['sdk_ver']);
            if($sdk_ver < '4.0.0.4'){
                $result["gw_dz"] = $app_info['web_url'];
            }
            if($new_ip[0]=='17'){
                $result["gw_dz"] = '';
                $result["is_open"] = '1' ;
            }
        }
        if(!empty($ver) && ($app_info['version'] > $ver)){
            if((time() > $app_info['version_time']) && !empty($app_info['version_url'])){
                $result['desc'] = "up_go";
                $result['type'] = 2;
                $result['up_dz'] = $app_info['version_url'];
            }elseif((time() > $app_info['version_time']) && empty($app_info['version_url'])){
                $result['desc'] = "url is null";
            }
        }
        die("0".base64_encode(json_encode($result)));
    }

    public function ios_version($ver=''){
        $ver = $this->format_version($ver);
        $app_id = $this->usr_params['app_id'];
        $apple_id = '';
        if(!$app_id){
            die("参数错误");
        }
        if(!empty($this->usr_params['apple_id'])){
            $apple_id = $this->usr_params['apple_id'];
        }
        if(!empty($apple_id)){
            $app_info = $this->DAO->get_apple_info($app_id,$apple_id);
        }
        if(empty($app_info)){
            $app_info = $this->DAO->get_app_info($app_id);
        }
        $result = array("result"=>1,
            "desc" => "",
            "type" => 1,
            "is_notice" => $app_info['notice_status'],
            "notice_url" => "http://ins.66173.cn/notice.php",
            "web_url" => '',
            "is_open" => '0',
            "autonym"=> $app_info['autonym']?$app_info['autonym']:'',
            "up_url" => "");

        if(!empty($ver) && ($app_info['version'] >= $ver)){
            $new_ip = explode(".",$this->client_ip());
            $result["web_url"] = $app_info['web_url'];
            if($new_ip[0]=='17'){
                $result["web_url"] = '';
                $result["is_open"] = '1' ;
            }
        }
        if(!empty($ver) && ($app_info['version'] > $ver)){
            if((time() > $app_info['version_time']) && !empty($app_info['version_url'])){
                $result['desc'] = "up_go";
                $result['type'] = 2;
                $result['up_url'] = $app_info['version_url'];
            }elseif((time() > $app_info['version_time']) && empty($app_info['version_url'])){
                $result['desc'] = "url is null";
            }
        }
        die("0".base64_encode(json_encode($result)));
    }

    public function s_version($ver=''){
        $result = array(
            "result"=>0, "desc" => "网络请求异常。", "type" => 1,
            "is_notice" => 0, "notice_url" => "", "up_url" => "","data"=> array("appver" => "", "forceupdate" => "2",
                "title" => "", "content" => "", "url" => ""));

        $ver = $this->format_version($ver);
        $super_id = $this->usr_params['app_id'];
        $channel = $this->usr_params['channel'];
        if(!$super_id){
            $result['desc'] = '缺少必须要参数';
            die("0".base64_encode(json_encode($result)));
        }
        if(!$channel){
            $result['desc'] = '渠道信息异常';
            die("0".base64_encode(json_encode($result)));
        }
        $app_info = $this->DAO->get_super_channel_info($super_id,$channel);
        if(empty($app_info)){
            $result['desc'] = '信息获取失败';
            die("0".base64_encode(json_encode($result)));
        }
        if(!empty($app_info['notice_status'])){
            $result['is_notice'] = $app_info['notice_status'];
            $result['notice_url'] = 'http://ins.66173.cn/s_notice.php';
        }
        $ip_array = array('117.27.77.97', '117.27.76.8', '117.25.82.144');
        if($app_info['up_status']=='1'){
            if($app_info['version'] > $this->gameVer && $app_info['version_url']){
                $result['result'] = 1;
                $result['desc'] = "up_go";
                $result['type'] = 2;
//                $result['up_url'] = $app_info['version_url'];
                $data = array("appver" => $app_info['version'], "forceupdate" => "2",
                    "title" => $app_info['up_title'], "content" => $app_info['up_desc'], "url" => $app_info['version_url']);
                $result['data'] = $data;
            }
        }elseif($app_info['up_status'] == '2' && in_array($this->client_ip(),$ip_array)){
            if($app_info['version'] > $this->gameVer && $app_info['version_url']){
                $result['result'] = 1;
                $result['desc'] = "test_go";
                $result['type'] = 2;
//                $result['up_url'] = $app_info['version_url'];
                $data = array("appver" => $app_info['version'], "forceupdate" => "2",
                    "title" => $app_info['up_title'], "content" => $app_info['up_desc'], "url" => $app_info['version_url']);
                $result['data'] = $data;
            }
        }
//        $result['default'] = "";
        die("0".base64_encode(json_encode($result)));
    }

    public function format_version($version){
        if(strpos($version, ".")){
            $version = explode(".",$version);
            $new_version = "";
            foreach($version as $k=>$v){
                if((int)$v){
                    $new_version .=(int)$v;
                }else{
                    $new_version .="0";
                }
                if($k<count($version)-1 && $k<3){
                    $new_version.=".";
                }else{
                    break;
                }
            }
        }else{
            $new_version = $version.".";
        }

        if($l = (3-substr_count($new_version,"."))){
            for($i=0;$i<$l;$i++){
                $new_version .=".0";
            }
        }
        return $new_version;
    }


    public function game_version(){
        $app_id = $this->usr_params['app_id'];
        if(!$app_id){
            die("参数错误");
        }
        $app_info = $this->DAO->get_app_info($app_id);
        $type = 1;
        $url="";
        $ip_array=array('117.27.77.97','117.27.76.52','117.25.82.91','218.66.11.138');
        if($app_info['up_status']=='1'){
            if($app_info['version'] > $this->gameVer && $app_info['version_url']){
                $type = 2;
                $url = $app_info['version_url'];
            }
        }elseif($app_info['up_status'] == '2' && in_array($this->client_ip(),$ip_array)){
            if($app_info['version'] > $this->gameVer && $app_info['version_url']){
                $type = 2;
                $url = $app_info['version_url'];
            }
        }
        if($app_id == '1077' && $this->usr_params['channel'] == '66173') {
            $app_info['notice_status'] = 0;
        }
        $result = array("result"=>1,"desc"=>"","type"=>$type, "data"=>array("appver"=>$app_info['version'],"forceupdate"=>"2",
                    "title"=>$app_info['up_title'],"content"=>$app_info['up_desc'],"url"=>$url),"is_notice"=>$app_info['notice_status'],
                    "autonym"=>$app_info['autonym'],"is_web"=>$app_info['is_web']);
        die("0".base64_encode(json_encode($result)));
    }

    public function get_update_info($app_info,$app_id){
        $result = array('type'=>1,'url'=>"");
        $url_foot = substr($app_info['version_url'],0,strlen($app_info['version_url'])-4);
        if(!empty($this->guild_code)){
            $url = $url_foot."_".$this->guild_code.".apk";
            $data = $this->isValidURL($app_id,$this->guild_code,$url);
        }else{
            $url = $url_foot."_66173.apk";
            $data = $this->isValidURL($app_id,'66173',$url);
        }
        if(strpos($data, '200') !== false){
            $result['type'] = 2;
            $result['url'] = $url;
        }
        return $result;
    }

    public function isValidURL($app_id,$guild_code,$url) {
        if(!$app_id || !$guild_code){
            return false;
        }
        $data = $this->DAO->get_valid_url($app_id,$guild_code);
        if(!$data){
            $headers = @get_headers($url);
            $data=$headers[0];
            $this->DAO->set_valid_url($app_id,$guild_code,$data);
        }
        return $data;
    }

    public function charge_order(){
//        $this->open_debug();
        $orders = $this->DAO->get_payed_orders();
        if(!$orders){
            die("执行完毕");
        }
        foreach($orders as $k=>$order){
            $timestamp = strtotime("now");
            $url = $order['sdk_charge_url'];
            $id = $order['id'];
            $app_key = $order['app_key'];
            $order_id = $order['order_id'];
            $app_id = $order['app_id'];
            $serv_id = $order['serv_id'];
            $usr_id = $order['buyer_id'];
            $player_id = $order['role_id'];
            $app_order_id = $order['app_order_id'];
            $coin = $order['amount'];
            $money = $order['pay_money'];
            $add_time = $order['buy_time'];
            $good_code = $order['good_code'];
            $payExpandData = $order['payExpandData'];
//            if($order['charge_time']){
//                continue;
//            }
            if(!empty($order['web_channel'])){
                $apple_info = $this->DAO->get_apple_id_info($order['app_id'],$order['web_channel']);
                if(!empty($apple_info['callback_url'])){
                    $url = $apple_info['callback_url'];
                }
            }
            $this->DAO->update_order_charge($id);
            $sign_str = md5($app_id.$serv_id.$usr_id.$player_id.$app_order_id.$coin.$money.$add_time.$app_key);
            $post_ary = array("app_id"=>$app_id, "serv_id"=>$serv_id, "usr_id"=>$usr_id, "player_id"=>$player_id,
                                "app_order_id"=>$app_order_id, "coin"=>$coin, "money"=>$money, "add_time"=>$add_time,
                                "good_code"=>$good_code,"payExpandData"=>$payExpandData, "sign"=>$sign_str, "order_id"=>$order_id);
            if($app_id == '1099'){
                $post_ary['payExpandData']=htmlspecialchars_decode($post_ary['payExpandData']);
            }
            if($app_id == '1070'){
                $post_ary = json_encode($post_ary);
            }

            #$url = $url."&".http_build_query($post_ary);
            $result = $this->request($url, $post_ary);
            $status = $this->DAO->get_order_debug_info();
            if($status['web'] == 1){
                $this->err_log(var_export($post_ary,1),"order_debug");
                $this->err_log(var_export($url,1),"order_debug");
                $this->err_log(var_export($result,1),"order_debug");
            }

            $result = json_decode($result);

            if("1" == $result->success){
                $this->DAO->update_order_status($id, 2);
            }
        }
    }

    public function ry_charge_order(){
        $ry_array = $this->DAO->get_ry_array();
        if($_SESSION['ry_array'] == $ry_array){
           $ry_str = $_SESSION['ry_str'];
        }else{
            $_SESSION['ry_array'] = $ry_array;
            $ry_str = '';
            $new_array = array();
            foreach ($ry_array as $key=>$data){
                array_push($new_array,$data['channel']);
            }
            $ry_str = implode("','",$new_array);
            $ry_str = "'".$ry_str."'";
            $_SESSION['ry_str'] = $ry_str;
        }
        $orders = $this->DAO->get_ry_payed_orders($ry_str);
        if(!$orders){
            die("执行完毕");
        }
        $ry_url='http://log.reyun.com/receive/tkio/payment';
        foreach($orders as $k=>$order){
            $ry_info = $this->DAO->get_ry_appid($order);
            if(!$ry_info){
                continue;
            }
            if($order['pay_channel'] == '1'){
                $pay_channel = 'alipay';
            }elseif($order['pay_channel'] == '2'){
                $pay_channel = 'weixinpay';
            }else{
                $pay_channel = 'niuniu'.$order['pay_channel'];

            }
            $appid =$ry_info['ry_appid'];
            $who =$order['buyer_id'];
            $context = array(
                '_deviceid'=>$order['idfa'],
                '_transactionid'=>$order['order_id'],
                '_paymenttype'=>$pay_channel,
                '_currencytype'=>'CNY',
                '_currencyamount'=>$order['pay_money'],
                '_idfa'=>$order['idfa']?$order['idfa']:'',
                '_idfv'=>$order['idfv']?$order['idfv']:''
            );

            $data = json_encode(array('appid'=>$appid, 'who'=>$who, 'context'=>$context,));
            $result = $this->request($ry_url, $data);
            $result = json_decode($result);
            if("0" == $result->status) {
                $this->err_log(var_export($data,1),'ry_order_callbak');
                $this->err_log(var_export($result,1),'ry_order_callbak');
                $this->DAO->update_ry_order_status($order,1);
                $this->update_ad_info($order);
            }else{
                $this->err_log(var_export($result,1),'ry_order_callbak_error');
                $this->DAO->update_ry_order_status($order,2);

            }
        }
        die("执行完毕".time());
    }

    public function update_ad_info($order){
        $ry_ad_info = $this->DAO->get_ry_ad_info($order);
        if(!empty($ry_ad_info['spreadurl'])){
            $this->DAO->update_ry_order_ad_id($order,$ry_ad_info);
        }
    }


    public function super_charge_order(){
        $this->open_debug();
        $orders = $this->DAO->get_super_payed_orders();
        if(!$orders){
            die("执行完毕");
        }
        foreach($orders as $k=>$order){
            $timestamp = strtotime("now");
            $url = $order['sdk_charge_url'];
            $id = $order['id'];
            $app_key = $order['app_key'];
            $order_id = $order['order_id'];
            $app_id = $order['app_id'];
            $serv_id = $order['serv_id'];
            $usr_id = $order['buyer_id'];
            $player_id = $order['role_id'];
            $app_order_id = $order['app_order_id'];
            $coin = $order['amount'];
            $money = $order['pay_money'];
            $add_time = $order['buy_time'];
            $good_code = $order['good_code'];
            $payExpandData = $order['payExpandData'];
            $new_user_id =  $usr_id.'_[]'.$order['channel'];
            $this->DAO->update_super_order_charge($id);
            if($app_id == '7027'){
                $platform = $order['channel'];
                $sign_str = md5($app_id.$serv_id.$usr_id.$player_id.$app_order_id.$coin.$money.$add_time.$app_key);
                $post_ary = array(
                    "app_id"=>$app_id,
                    "serv_id"=>$serv_id,
                    "usr_id"=>$usr_id,
                    "player_id"=>$player_id,
                    "app_order_id"=>$app_order_id,
                    "coin"=>$coin,
                    "money"=>$money,
                    "pay_time"=>$add_time,
                    "good_code"=>$good_code,
                    "payExpandData"=>$payExpandData,
                    "sign"=>$sign_str,
                    "order_id"=>$order_id);
                $this->err_log(var_export($app_id.$serv_id.$usr_id.$player_id.$app_order_id.$coin.$money.$add_time.$app_key,1),'super_callback_error');
                $this->err_log(var_export($sign_str,1),'super_callback_error');
            }else{
                $sign_str = md5($app_id.$serv_id.$new_user_id.$player_id.$app_order_id.$coin.$money.$add_time.$app_key);
                $post_ary = array(
                    "app_id"=>$app_id,
                    "serv_id"=>$serv_id,
                    "usr_id"=>$new_user_id,
                    "player_id"=>$player_id,
                    "app_order_id"=>$app_order_id,
                    "coin"=>$coin,
                    "money"=>$money,
                    "pay_time"=>$add_time,
                    "good_id"=>$good_code,
                    "payExpandData"=>$payExpandData,
                    "sign"=>$sign_str,
                    "order_id"=>$order_id);
            }
//            if($app_id=='7026' && $order['channel'] =='yyb'){
//                $url = 'https://lcwslogpy.newyx.jiulingwan.com/niuniush/payment';
//            }
            if($app_id=='7034' && ($order['channel'] =='hwquick' || $order['channel'] =='iqiyi'|| $order['channel'] =='oppo_h5'|| $order['channel'] =='xiaomi_h5')){
                $url = 'http://pay.90wmoyu.com/platform_nnzfh5/pay_nnh5.php';
            }
//
//            if($app_id=='7033' && ($order['channel'] =='oppo' || $order['channel'] =='vivo')){
//                $url = 'http://pay.90wmoyu.com/platform_nnzf/pay_nnzf.php';
//            }else
            if($app_id=='7033' && $order['channel']!='nnwl' && $order['channel']!='wuhanduoyou'){
                $url = 'http://pay.90wmoyu.com/platform_nnzf/pay_nnzf.php';
//                $url = 'http://pay.90wmoyu.com/platform_nnzf3/pay_nnzf.php';
            }
            #$url = $url."&".http_build_query($post_ary);
            $result = $this->request($url, $post_ary);
            $this->err_log(var_export($url,1),'super_callback_error');
            $this->err_log(var_export($post_ary,1),'super_callback_error');
            $this->err_log(var_export($result,1),'super_callback_error');
            $result = json_decode($result);
            $this->err_log(var_export($result,1),'super_callback_error');

            if("1" == $result->result || "1" == $result->success){
                $this->DAO->update_super_order_status($id, 2);
            }
        }
    }

    public function update_order_moeny(){
        $orders = $this->DAO->get_role_order_list();
        if(!$orders){
            die("执行完毕");
        }
        echo '开始时间：'.time();
        foreach($orders as $k=>$order){
            $role_info = $this->DAO->get_role_info($order);
            if($role_info){
                $role_money = $this->DAO->get_role_money($order);
                $role_platform_money = $this->DAO->get_role_platfor_money($order);
                if($role_info['pay_money'] != $role_money || $role_info['platform_money'] != $role_platform_money){
                    $this->DAO->up_role_info($role_info,$role_money,$role_platform_money);
                }
                $this->DAO->up_order_info($order,2);
            }else{
                $role_money = $this->DAO->get_role_money($order);
                $role_platform_money = $this->DAO->get_role_platfor_money($order);
                $this->DAO->add_role_info($order,$role_money,$role_platform_money);
                $this->DAO->up_order_info($order,1);
            }
        }
        echo '结束时间：'.time();
    }

    public function update_ry_sid(){
        $callback_info = $this->DAO->get_ry_callback();
        if(!$callback_info){
            die("执行完毕");
        }
        echo '开始时间：'.time();
        foreach($callback_info as $k=>$ry_info){
            $device = $this->DAO->get_device_sid($ry_info['imei']);
            if($device){
                $this->DAO->up_ry_info($ry_info['id'],1,$device['SID']);
            }else{
                $device = $this->DAO->get_device_andord_id($ry_info['imei']);
                if($device){
                    $this->DAO->up_ry_info($ry_info['id'],1,$device['SID']);
                }else{
                    $this->DAO->up_ry_info($ry_info['id'],3,'');
                }
            }
        }
        echo '结束时间：'.time();
    }

    public function update_order_group(){
        $this->open_debug();
        $orders = $this->DAO->get_order_list();
        if(!$orders){
            die("执行完毕");
        }
        echo '开始时间：'.time();
        foreach($orders as $k=>$order){
            if($order['channel']=='ong'){
                $this->DAO->update_order_info($order,3); //web充值
            }else{
                //公会账号
                $ch_info = $this->DAO->get_channel_info($order['channel'],10);
                if($ch_info){
                    $this->DAO->update_order_info($order,1);//公会
                }else{
                    //正则匹配渠道
                    $information_ch = preg_replace('/(.*[a-zA-Z]+)\d*$/','$1',$order['channel']);
                    $information = $this->DAO->get_channel_info($information_ch,12);
                    if($information){
                        $this->DAO->update_order_info($order,2);//信息流
                    }else{
                        $this->DAO->update_order_info($order,4); //其他
                    }
                }
            }
        }
        echo '结束时间：'.time();
    }


    public function add_order($params,$appid){
        $result = array('ret'=>'1','msg'=>'订单入库失败');
        $qq_call_back = 'http://123.206.28.165:8080/gamedata_new_iOS/ios_qq_vip/ios_send_qq_vip_gift.jsp';
        $params['appid'] = $appid;
        $this->err_log(var_export($params,1),'qq_cp_order');
        $app_info = $this->DAO->get_app_info($appid);
        $order = $this->DAO->get_qq_member_order($params['payid']);
        if($order['status']==2){
            $result = array('ret'=>'-5','msg'=>'已经发过货');
//            $this->DAO->update_qq_member_order($order['id']);
            die(json_encode($result));
        }elseif(!$order){
            $id = $this->DAO->add_qq_member_order($params,$appid);
            $this->do_up_member_order($params,$appid,$app_info,$id);
        }elseif($order['status']==1){
            $this->do_up_member_order($params,$appid,$app_info,$order['id']);
        }else{
            $result = array('ret'=>'1','msg'=>'异常的订单状态');
        }
        die(json_encode($result));
    }

    public function do_up_member_order($params,$appid,$app_info,$id){
        $qq_call_back = 'http://123.206.28.165:8080/gamedata_new_iOS/ios_qq_vip/ios_send_qq_vip_gift.jsp';
        $params['sign'] = strtolower(md5($appid.$params['drid'].$params['dsid'].$params['payid'].$params['taskid'].$params['time'].$params['uid'].$app_info['app_key']));
        $request = $this->request($qq_call_back,$params);
        $this->err_log(var_export($id,1),'qq_cp_callback');
        $this->err_log(var_export($params,1),'qq_cp_callback');
        $this->err_log(var_export($request,1),'qq_cp_callback');
        $request = json_decode($request);
        if($request->ret=='0'){
            $this->DAO->update_qq_member_order($id);
            $result = array('ret'=>0,'msg'=>'成功');
        }elseif($request->ret=='-5'){
            $this->err_log(var_export($id,1),'qq_cp_repay');
            $this->err_log(var_export($params,1),'qq_cp_repay');
            $this->err_log(var_export($request,1),'qq_cp_repay');
            $this->DAO->update_qq_member_order($id);
            $result = array('ret'=>$request->ret,'msg'=>$request->msg);
        }else{
            $result = array('ret'=>$request->ret,'msg'=>$request->msg);
        }
        die(json_encode($result));
    }

//    public function up_user_city(){
//        $user_list = $this->DAO->get_no_city_list();
//        if(!$user_list){
//            die("执行完毕");
//        }
//        $url='http://ip.taobao.com/service/getIpInfo.php?ip=';
//        foreach($user_list as $k=>$item){
//            if(!$item['reg_ip']||!$item['u_id']){
//                continue;
//            }
//            $post_url = $url.$item['reg_ip'];
//            $result = $this->request($post_url);
//            $result = json_decode($result);
//            if($result->code == '0'){
//                $country = $result->data->country;
//                $region = $result->data->region;
//                $city = $result->data->city;
//                $user_city_info = $this->DAO->get_user_city_info($item['u_id']);
//                if(empty($user_city_info)){
//                    $this->DAO->add_user_city_info($country,$region,$city,$item['u_id']);
//                }
//            }
//        }
//        die("执行完毕".time());
//    }
}
