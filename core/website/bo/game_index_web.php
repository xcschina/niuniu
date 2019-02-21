<?php
COMMON('baseCore','alipay','alipay/alipay_submit.class','alipay/alipay_notify.class');
COMMON('alipay_mobile/alipay_service',"alipay.mobile.config",'alipay_mobile/alipay_notify');
COMMON('paramUtils','RNCryptor/RNEncryptor');
DAO("game_site_dao");
class game_index_web extends baseCore{

    protected $exchanges;
    protected $api_serv_url;
    protected $api_user_url;
    protected $api_order_url;
    protected $DAO;

    public function __construct(){
        parent::__construct();
        $this->DAO = new game_site_dao();
    }
    
    public function pay_view(){
        $_SESSION['request_url'] = ltrim($_SERVER['REQUEST_URI'],'/');
        if($_SESSION['msg']=='' && GAME_ID=='1028'){
            $_SESSION['msg'] = '角色后面带_sx,必须同时输入';
        }
        $game = $this->DAO->get_game_info(GAME_ID);
        $exchanges = $this->DAO->get_exchanges(GAME_ID);
        $servers = $this->request($game['web_serv_url']);
        $servers = json_decode($servers);
        $servers = $this->object_to_array($servers->serv);
        $this->assign("servers", $servers);
        $this->assign("info", $game);
        $this->assign("exchanges", $exchanges);
        $this->display("pay_index.html");
        $_SESSION['msg'] = '';
        $_SESSION['limit_error'] = '';
        $_SESSION['money_id'] = '';
        $_SESSION['serv_id'] = '';
        $_SESSION['usr_name'] = '';
        $_SESSION['player_id'] = '';
    }

    public function new_pay_view(){
        $game = $this->DAO->get_game_info(GAME_ID);
        $exchanges = $this->DAO->get_exchanges(GAME_ID);
        unset($_SESSION['cp_order_id']);
        unset($_SESSION['payExpandData']);
        if($_GET["order_id"]){
            $_SESSION['cp_order_id'] = $_GET["order_id"];
        }
        if($_GET['payExpandData']){
            $_SESSION['payExpandData'] = $_GET["payExpandData"];
        }
        $this->assign("serv_name", $_GET["serv_name"]);
        $this->assign("serv_id", $_GET["serv_id"]);
        $this->assign("usr_name", $_GET["usr_name"]);
        $this->assign("usr_id", $_GET["usr_id"]);
        $this->assign("player_id", $_GET["player_id"]);
        $this->assign("info", $game);
        $this->assign("exchanges", $exchanges);
        $this->display("pay_index2.html");
        $_SESSION['msg'] = '';
    }

    public function new_pay_by_goodid($goodid){
        $game = $this->DAO->get_game_info(GAME_ID);
        $exchanges = $this->DAO->get_exchanges_by_goodid(GAME_ID,$goodid);
        unset($_SESSION['cp_order_id']);
        unset($_SESSION['payExpandData']);
        if($_GET["order_id"]){
            $_SESSION['cp_order_id'] = $_GET["order_id"];
        }
        if($_GET['payExpandData']){
            $_SESSION['payExpandData'] = $_GET["payExpandData"];
        }
        $this->assign("serv_name", $_GET["serv_name"]);
        $this->assign("serv_id", $_GET["serv_id"]);
        $this->assign("usr_name", $_GET["usr_name"]);
        $this->assign("usr_id", $_GET["usr_id"]);
        $this->assign("player_id", $_GET["player_id"]);
        $this->assign("info", $game);
        $this->assign("exchanges", $exchanges);
        $this->display("pay_index2.html");
        $_SESSION['msg'] = '';
    }

    public function request_usr_name($serv_id, $usr_name){
        $game = $this->DAO->get_game_info(GAME_ID);
        $usr_name = htmlspecialchars_decode($usr_name, ENT_QUOTES);
        $params = "serv_id=".$serv_id."&usr_name=".$usr_name;

        $player = $this->request($game['web_user_url'].'?'.$params, '', array(), 10);
        echo $player;
    }

    public function request_usr_by_palyer_id($usr_name){
        $game = $this->DAO->get_game_info(GAME_ID);
        $usr_name = htmlspecialchars_decode($usr_name, ENT_QUOTES);
        $params = "player_id=".$usr_name;
        $player = $this->request($game['web_user_url'].'?'.$params, '', array(), 10);
        echo $player;
    }

    public function pay_affirm(){
        $order_info = $_POST;
        if(empty($order_info['usr_id'])){
            $order_info['usr_id']==0;
        }
        $game = $this->DAO->get_game_info(GAME_ID);
        $money = $this->DAO->get_money($order_info['money_id']);
        if(!$money){
            die("充值分档未配置");
        }
        $order = $this->DAO->get_order_num($order_info);
        if($money['limit_num'] != '0'){
            if($order['num']>=$money['limit_num']){
                $_SESSION['limit_error'] = "该商品已超过购买次数啦，请重新选择其他商品";
                $_SESSION['money_id'] = $order_info['money_id'];
                $_SESSION['serv_id'] = $order_info['serv_id'];
                $_SESSION['usr_name'] = $order_info['usr_name'];
                $_SESSION['player_id'] = $order_info['player_id'];
                $this->redirect($_SESSION['request_url']);
                exit;
            }
        }
        //充值游戏
        $this->V->assign('referrer',$order_info['referrer']);
        $this->V->assign('game_name',$order_info['game_name']);
        $this->V->assign('game_id',$order_info['game_id']);
        //服务器serv_id
        $this->V->assign('serv_id',$order_info['serv_id']);

        $this->V->assign('serv_name',$order_info['serv_name']);
        //角色名
        $this->V->assign('player_id',$order_info['player_id']);
        $this->V->assign('usr_id',$order_info['usr_id']);
        $this->V->assign('usr_name',$order_info['usr_name']);
        $this->V->assign("time", strtotime("now"));
        $this->V->assign("info",$game);
        //充值代币
        $this->V->assign('money',$money);
        $this->V->assign("encrypt_id", $order_info['encrypt_id']);
        $this->V->display("pay_affirm.html");
        $_SESSION['limit_error'] = '';
        $_SESSION['money_id'] = '';
        $_SESSION['serv_id'] = '';
        $_SESSION['usr_name'] = '';
        $_SESSION['request_url'] = '';
        $_SESSION['player_id'] = '';
        $_SESSION['error'] = '';
    }

    public function quick_payment($params,$status=''){
        $user_header = array('mac'=>'', 'idfa'=>'', 'idfv'=>'', 'web_channel'=>'',);
        if(isset($_SERVER['HTTP_USER_AGENT1'])){
            $header = base64_decode(substr($_SERVER['HTTP_USER_AGENT1'],1));
            $header = explode("&",$header);
            foreach($header as $k=>$param){
                $param = explode("=",$param);
                switch($param[0]){
                    case 'mac':
                        $user_header['mac'] = $param[1];
                        break;
                    case 'adtid':
                        $user_header['idfa'] = $param[1];
                        break;
                    case 'sq':
                        $user_header['idfv'] = $param[1];
                        break;
                    case 'channel':
                        $user_header['web_channel'] = $param[1];
                        break;
                    case 'apple_id':
                        $user_header['web_channel'] = $param[1];
                        break;

                }
            }
        }
        $app_info = $this->params_verify($params,$status);
        unset($_SESSION['web_cookie']);
        if(empty($params['money_id'])){
            $this->select_good_view($params,$app_info,$user_header);
        }else{
            $this->verify_good_view($params,$app_info,$user_header);
        }
    }

    public function bj_quick_payment($params,$status=''){
        $user_header = array('mac'=>'', 'idfa'=>'', 'idfv'=>'', 'web_channel'=>'',);
        if(isset($_SERVER['HTTP_USER_AGENT1'])){
            $header = base64_decode(substr($_SERVER['HTTP_USER_AGENT1'],1));
            $header = explode("&",$header);
            foreach($header as $k=>$param){
                $param = explode("=",$param);
                switch($param[0]){
                    case 'mac':
                        $user_header['mac'] = $param[1];
                        break;
                    case 'adtid':
                        $user_header['idfa'] = $param[1];
                        break;
                    case 'sq':
                        $user_header['idfv'] = $param[1];
                        break;
                    case 'channel':
                        $user_header['web_channel'] = $param[1];
                        break;
                    case 'apple_id':
                        $user_header['web_channel'] = $param[1];
                        break;

                }
            }
        }
        $app_info = $this->params_verify($params,$status);
        unset($_SESSION['web_cookie']);
        if(empty($params['money_id'])){
            $this->select_good_view($params,$app_info,$user_header);
        }else{
            $this->bj_verify_good_view($params,$app_info,$user_header);
        }
    }


    public function to_pay($money_id){
        $params = $_SESSION['web_cookie']['params'];
        $app_info =  $_SESSION['web_cookie']['app_info'];
        $header = $_SESSION['web_cookie']['header'];
        $money_info = $this->DAO->get_money($money_id);
        $params['money_id'] = $money_info['good_code'];
        $this->verify_good_view($params,$app_info,$user_header);
    }

    public function select_good_view($params,$app_info,$header){
        $_SESSION['web_cookie']['params'] = $params;
        $_SESSION['web_cookie']['app_info'] = $app_info;
        $_SESSION['web_cookie']['header'] = $header;
        $money_list = $this->DAO->get_exchanges($params['app_id']);
        $this->page_hash();
        $this->V->assign("page_hash", $this->page_hash());
        $this->V->assign("info", $app_info);
        $this->V->assign("exchanges", $money_list);
        $this->V->display("select_good_view.html");
    }

    public function params_verify($params,$status=''){
        if(!$params){
            if($status){
                $this->V->display("close.html");
                exit();
            }else{
                die('缺少必要参数，请关闭页面后重试。');
            }
        }
        if(!$params['app_id']){
            die('缺少必要参数，请关闭页面后重试1。');
        }
        if(!$params['player_id']){
            die('缺少必要参数，请关闭页面后重试2。');
        }
        if(!$params['player_name']){
            die('缺少必要参数，请关闭页面后重试3。');
        }
        if(!$params['serv_id']){
            die('缺少必要参数，请关闭页面后重试4。');
        }
        if(!$params['user_id']){
            die('缺少必要参数，请关闭页面后重试5。');
        }
        if(!$params['usertoken']){
            die('缺少必要参数，请关闭页面后重试6。');
        }
        $app_info = $this->DAO->get_game_info($params['app_id']);
        if(!$app_info){
            die('未查询到相关游戏。请联系客服。');
        }
        $sgin =$params['app_id'].$params['player_id'].$params['serv_id'].$params['user_id'].$params['usertoken'].$app_info['app_key'];
        if($params['md5'] != md5($sgin)){
            die('验证失败，请关闭窗口重新请求。');

        }
        return $app_info;
    }
    public function go_pay($params,$app_info){
        try{
            $pay = $params;
            $pay['usr_id'] = $params['user_id'];
            $pay['usr_name'] = $params['player_name'];
            $pay['ch'] = 1;
            $game = $app_info;
            $money = $this->DAO->get_money_info($params['app_id'],$params['money_id']);
            if(!$money){
                die("充值分档未配置");
            }
            if(GAME_NAME){
                $YD_order_id = GAME_NAME.$this->orderid($money['app_id']);
            }else{
                $YD_order_id = "WEB".$this->orderid($money['app_id']);
            }
            $game = $app_info;
            $time = strtotime("now");
            $ip = $this->client_ip();

            //获取厂商订单号
            $AppOrderId = $this->get_game_orderid($params, $money, $YD_order_id, $time, $game);
            if(!$AppOrderId){
                throw new Exception("获取厂商订单号错误");
            }
            $ch = $pay['ch'];
            //订单入库
            $order = $this->insert_YD_order($YD_order_id, $AppOrderId, $pay, $money, $time, $ip, $ch);
//            if (in_array($this->client_ip(), array('117.25.82.193','117.25.83.168')) && in_array($money['app_id'], array('1060','1071','1072'))) {
//                $order['pay_money'] = 0.01;
//            }

            $this->go_wap_alipay($order, $pay, $game);
        }catch (Exception $e){
//            $this->DAO->charge_log(0, GAME_ID,0, "充值发起失败",strtotime("now"),0,var_export($e,true),0,'');
            die('充值过程服务器发生错误，请重试');
        }
    }

    //支付宝wap支付
    public function go_wap_alipay($order, $pay, $game){
        $pms1 = array (
            "req_data" => '<direct_trade_create_req><subject>' . $game['app_name'].$order['subject'].
                '</subject><out_trade_no>' . $order['order_id'] .
                '</out_trade_no><total_fee>' . $order['pay_money'] .
                "</total_fee><seller_account_name>" . ALI_MOBILE_seller_email .
                "</seller_account_name><notify_url>http://charge.66173.cn/gamesite_notify.php</notify_url><out_user></out_user><merchant_url>" . SITE_URL.
                "</merchant_url>" . "<call_back_url>" . SITE_URL."ali_return.php".
                "</call_back_url></direct_trade_create_req>",
            "service" => ALI_MOBILE_Service_Create,
            "sec_id" => ALI_MOBILE_sec_id,
            "partner" => ALI_MOBILE_partner,
            "req_id" => $order['order_id'],
            "format" => ALI_MOBILE_format,
            "v" => ALI_MOBILE_v
        );
        if($this->is_mobile_client()){
            $pms1['app_pay']='Y';
        }
        // 构造请求函数
        $alipay = new alipay_service();
        $token = $alipay->alipay_wap_trade_create_direct($pms1, ALI_MOBILE_key, ALI_MOBILE_sec_id );
        $pms2 = array (
            "req_data"  => "<auth_and_execute_req><request_token>" . $token . "</request_token></auth_and_execute_req>",
            "service"   => ALI_MOBILE_Service_authAndExecute,
            "sec_id"    => ALI_MOBILE_sec_id,
            "partner"   => ALI_MOBILE_partner,
            "call_back_url" => SITE_URL,
            "format"    => ALI_MOBILE_format,
            "v"         => ALI_MOBILE_v
        );
        if($this->is_mobile_client()){
            $pms2['app_pay']='Y';
        }
        $alipay->alipay_Wap_Auth_AuthAndExecute($pms2, ALI_MOBILE_key);
    }

    //平台订单创建
    public function insert_YD_order($YD_order_id, $app_order_id, $pay, $money, $time, $ip, $ch=1){
        $order['app_id']      = GAME_ID;
        $order['order_id']       = $YD_order_id;
        $order['app_order_id']  = $app_order_id;
        $order['pay_channel']   = 1;
        if(empty($pay['usr_id'])){
            $pay['usr_id']=57;
        }
        $order['buyer_id']    = $pay['usr_id'];
        $order['role_id']     = $pay['player_id'];
        $order['product_id']  = $money['id'];
        $order['unit_price']  = $money['good_price'];
        $order['title']       = $money['good_name'];
        $order['role_name']   = $pay['usr_name'];
        $order['amount']      = $money['good_amount'];
        $order['pay_money']   = $money['good_price'];
        $order['status']      = 0;
        $order['buy_time']    = $time;
        $order['ip']     = $this->client_ip();
        $order['serv_id']   = $pay['serv_id'];
        $order['channel']   = 'ong';
        $order['payExpandData'] = '';
//        $order['payExpandData'] = $_SESSION['payExpandData']?$_SESSION['payExpandData']:'';
        $order['pay_from'] = 2;
        if(!$this->DAO->create_order($order)){
            die("订单号创建失败");
        }
        return $order;
    }

    public function get_game_orderid($pay, $money, $order_id, $time, $game){
        if($pay['cp_order_id']){
            return $pay['cp_order_id'];
        }
        if(in_array($pay['usr_id'], $this->qa_user_id)){
            $this->is_request_debug = true;
        }

        if($pay['usr_id']==0)$pay['user_id']='';

        $sign_str = $game['app_id']. $pay['serv_id']. $pay['player_id']. $order_id. $money['good_amount'].$money['good_price']. $time. $game['app_key'];
        $sign = md5($sign_str);

        $post_string = "app_id=".$game['app_id']."&serv_id=".urlencode($pay['serv_id'])."&player_id=".urlencode($pay['player_id']).
            "&order_id=".urlencode($order_id)."&coin=".urlencode($money['good_amount'])."&money=".urlencode($money['good_price']).
            "&create_time=".urlencode($time)."&sign=".urlencode($sign)."&good_code=".$money['good_code'];
        if(!empty($pay['usr_id'])){
            $post_string = $post_string."&usr_id=".$pay['usr_id'];
        }
        $result = $this->request($game['web_order_url'].'?'.$post_string, '', array(), 10);
        $this->err_log($sign_str,'game_order');
        $this->err_log($post_string,'game_order');

        $result = json_decode($result);
        if(!$result){
            die("游戏服务出错,请联络客服");
        }

        if(!$result->err_code && $result->desc){
            return $result->desc;
        }else{
            die('订单号'.$order_id.'创建失败[错误代码：10996]');
        }
    }

    public function verify_good_view($params,$app_info,$header){
        $order_info = array(
            'game_id' => $params['app_id'],
            'serv_id' => $params['serv_id'],
            'user' => $params['player_name'],
            'money_id' => $params['money_id'],
            'serv_name' => $params['serv_name'],
            'mode' => 1,
            'player_id' => $params['player_id'],
            'usr_id' => $params['user_id'],
            'usr_name' => $params['player_name'],
            'game_name' => $app_info['game_name'],
            'encrypt_id' => '',
            'mac' => $header['mac']?$header['mac']:'',
            'idfa' => $header['idfa']?$header['idfa']:'',
            'idfv' => $header['idfv']?$header['idfv']:'',
            'web_channel' => $header['web_channel']?$header['web_channel']:'',

        );
        if(empty($order_info['usr_id'])){
            $order_info['usr_id']==0;
            die('缺少用户ID');
        }
        $game = $app_info;
        $money = $this->DAO->get_money_info($order_info['game_id'],$order_info['money_id']);
        if(!$money){
            die("充值分档未配置_".$order_info['money_id']);
        }
        unset($_SESSION['web_cookie']);
//        var_dump($_SESSION['web_cookie']['header']);
        //充值游戏
        $this->V->assign('referrer',$order_info['referrer']);
        $this->V->assign('game_name',$order_info['game_name']);
        $this->V->assign('game_id',$order_info['game_id']);
        //服务器serv_id
        $this->V->assign('serv_id',$order_info['serv_id']);

        $this->V->assign('serv_name',$order_info['serv_name']);
        //角色名
        $this->V->assign('player_id',$order_info['player_id']);
        $this->V->assign('usr_id',$order_info['usr_id']);
        $this->V->assign('usr_name',$order_info['usr_name']);
        $this->V->assign("time", strtotime("now"));
        $this->V->assign("info",$game);
        //充值代币
        $this->V->assign('money',$money);
        $this->V->assign('cp_order_id',$params['cp_order_id']);
        $this->V->assign('payexpanddata',$params['payexpanddata']);
        $this->V->assign("encrypt_id", $order_info['encrypt_id']);
        $this->V->assign("mac", $order_info['mac']);
        $this->V->assign("idfa", $order_info['idfa']);
        $this->V->assign("idfv", $order_info['idfv']);
        $this->V->assign("web_channel", $order_info['web_channel']);
        $this->V->display("pay_affirm2.html");
        $_SESSION['limit_error'] = '';
        $_SESSION['money_id'] = '';
        $_SESSION['serv_id'] = '';
        $_SESSION['usr_name'] = '';
        $_SESSION['request_url'] = '';
        $_SESSION['player_id'] = '';
        $_SESSION['error'] = '';
    }

    public function bj_verify_good_view($params,$app_info,$header){
        $order_info = array(
            'game_id' => $params['app_id'],
            'serv_id' => $params['serv_id'],
            'user' => $params['player_name'],
            'money_id' => $params['money_id'],
            'serv_name' => $params['serv_name'],
            'mode' => 1,
            'player_id' => $params['player_id'],
            'usr_id' => $params['user_id'],
            'usr_name' => $params['player_name'],
            'game_name' => $app_info['game_name'],
            'encrypt_id' => '',
            'mac' => $header['mac']?$header['mac']:'',
            'idfa' => $header['idfa']?$header['idfa']:'',
            'idfv' => $header['idfv']?$header['idfv']:'',
            'web_channel' => $header['web_channel']?$header['web_channel']:'',

        );
        if(empty($order_info['usr_id'])){
            $order_info['usr_id']==0;
            die('缺少用户ID');
        }
//        $user_bj_info = $this->DAO->get_user_platform_num($order_info['usr_id']);
//        if($user_bj_info){
//            $this->V->assign('user_bjb',$user_bj_info['bjb']);
//        }
        $game = $app_info;
        $money = $this->DAO->get_money_info($order_info['game_id'],$order_info['money_id']);
        if(!$money){
            die("充值分档未配置_".$order_info['money_id']);
        }
        $device_sid = $this->verify_device_sid($params['sid']);
        if($device_sid == '1'){
            $this->page_hash();
            $this->V->assign('bjb_pay','show');
        }
        unset($_SESSION['web_cookie']);
//        var_dump($_SESSION['web_cookie']['header']);
        //充值游戏
        $this->V->assign('referrer',$order_info['referrer']);
        $this->V->assign('game_name',$order_info['game_name']);
        $this->V->assign('game_id',$order_info['game_id']);
        //服务器serv_id
        $this->V->assign('serv_id',$order_info['serv_id']);

        $this->V->assign('serv_name',$order_info['serv_name']);
        //角色名
        $this->V->assign('player_id',$order_info['player_id']);
        $this->V->assign('usr_id',$order_info['usr_id']);
        $this->V->assign('usr_name',$order_info['usr_name']);
        $this->V->assign("time", strtotime("now"));
        $this->V->assign("info",$game);
        //充值代币
        $this->V->assign('money',$money);
        $this->V->assign('cp_order_id',$params['cp_order_id']);
        $this->V->assign('payexpanddata',$params['payexpanddata']);
        $this->V->assign("encrypt_id", $order_info['encrypt_id']);
        $this->V->assign("mac", $order_info['mac']);
        $this->V->assign("idfa", $order_info['idfa']);
        $this->V->assign("idfv", $order_info['idfv']);
        $this->V->assign("web_channel", $order_info['web_channel']);
        $this->V->display("bj_pay_affirm2.html");
        $_SESSION['limit_error'] = '';
        $_SESSION['money_id'] = '';
        $_SESSION['serv_id'] = '';
        $_SESSION['usr_name'] = '';
        $_SESSION['request_url'] = '';
        $_SESSION['player_id'] = '';
        $_SESSION['error'] = '';
    }

    public function verify_device_sid($sid){
        if(empty($sid)){
            return '2';
        }
        $white_sid = $this->DAO->get_white_sid($sid);
        if(empty($white_sid)){
            return '3';
        }
        return '1';
    }

    public function ali_pay_return(){
        //构造通知函数信息
        $alipay = new alipay_notify(ALI_MOBILE_partner, ALI_MOBILE_key, ALI_MOBILE_sec_id, ALI_MOBILE_input_charset);
        // 计算得出通知验证结果
        $verify_result = $alipay->return_verify();

        $this->assign("order_id", $_GET['out_trade_no']);
        $this->assign("result", $_GET['result']);

        if ($verify_result) {
            $_SESSION['msg'] = '充值成功,稍后到游戏查看';
        }else{
            $_SESSION['msg'] = '充值失败';
        }
        header('HTTP/1.1 301 Moved Permanently');
        header('Location: '.SITE_URL);
    }

    public function ali_pay_return2(){
        //构造通知函数信息
        $order_id = $_GET['out_trade_no'];
        $order_info = $this->DAO->get_order_info($order_id);
        if($order_info['collect_company']==2){
            $alipay = new alipay_notify(HN_ALI_MOBILE_partner, HN_ALI_MOBILE_key, ALI_MOBILE_sec_id, ALI_MOBILE_input_charset);
        }else if($order_info['collect_company']==1){
            $alipay = new alipay_notify(ALI_MOBILE_partner, ALI_MOBILE_key, ALI_MOBILE_sec_id, ALI_MOBILE_input_charset);
        }else{
            $alipay = new alipay_notify(BJ_ALI_MOBILE_partner, BJ_ALI_MOBILE_key, ALI_MOBILE_sec_id, ALI_MOBILE_input_charset);
        }
        // 计算得出通知验证结果
        $verify_result = $alipay->return_verify();

        $_SESSION['ali_status']= 0;
        if ($verify_result) {
            $_SESSION['ali_status'] = 1;
            $_SESSION['msg'] = '充值成功,稍后到游戏查看';
        }else{
            $_SESSION['msg'] = '充值失败';
        }
        header('HTTP/1.1 301 Moved Permanently');
        header('Location: http://ins.66173.cn/ali_return.php');
    }

    public function check_user_agent($user_agent, $app_id){
        $app_info = $this->DAO->get_game_info($app_id);
        if(!$app_info){
            die("参数错误");
        }

        $ua = rawurldecode($user_agent);
        $ua = base64_decode($ua);

        $decrypted = mcrypt_decrypt(MCRYPT_RIJNDAEL_128, $app_info['app_key'], $ua, MCRYPT_MODE_CBC, AES_IV);

        $header = explode("&", $decrypted);
        return $header;
    }

    public function wx_callback(){
        $this->V->display("wx_callback.html");
    }
}
