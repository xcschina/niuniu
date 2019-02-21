<?php
COMMON('baseCore','pageCore','jsonUtils','paramUtils');
COMMON('alipay_mobile/alipay_service',"alipay.mobile.config",'alipay_mobile/alipay_notify');
BO("pay_web");
DAO('le8_pay_dao');
class le8_pay_web extends baseCore{

    public $DAO;
    public $id;
    public $real_app_id;
    public $qa_user_id;

    public function __construct(){
        parent::__construct();
        $this->DAO = new le8_pay_dao();
    }

    public function pay_index_view($user_id, $pay_money, $trade_order_id, $appid, $goods_name, $sign, $nn_user_id, $user_type, $timestamp){
        $sign_str = md5($user_id.$pay_money.$trade_order_id.$timestamp.$appid.$user_type.$nn_user_id.NIUBI_KEY);
        if($sign_str != $sign){
            die("加密错误");
        }
        $this->err_log("le8id:".$user_id,"le8");
        $user_info = $this->get_user_info($user_id, $nn_user_id, $user_type);
        $_SESSION['user_id'] = $user_info['user_id'];
        $_SESSION['le8_user_id'] = $user_info['le8_user_id'];
        $_SESSION['nnb'] = $user_info['nnb'];
        $_SESSION['mobile'] = $user_info['mobile'];
        $_SESSION['nick_name'] = $user_info['nick_name'];
        $_SESSION['trade_order_id'] = $trade_order_id;
        $_SESSION['le_appid'] = $appid;
        $_SESSION['pay_money'] = $pay_money/100;
        $_SESSION['goods_name'] = urldecode($goods_name);
        $_SESSION['le8_sign'] = $sign;

        $this->page_hash();
        if($_SESSION['nnb']>=($pay_money/100)){
            $ch_pay_money = 0;
        }else{
            $ch_pay_money = ($pay_money/100);
        }
        $this->assign("pay_money", $_SESSION['pay_money']);
        $this->assign("ch_pay_money", $ch_pay_money);
        $this->display("index.html");
    }

    public function do_pay($hash){
        if($hash != $_SESSION['page-hash']){
            die("不正确的请求");
        }
        if(!$_SESSION['user_id'] || !$_SESSION['le8_user_id'] || $_SESSION['nnb']==''
            || !$_SESSION['trade_order_id'] || !$_SESSION['le_appid'] || !$_SESSION['pay_money'] || !$_SESSION['goods_name']){
            die("请重新付款");
        }
        $order = $this->DAO->get_trade_order($_SESSION['trade_order_id']);
        if(!$order){
            $order_id = $this->orderid(9999);

            $this->DAO->insert_le8_order($_SESSION['trade_order_id'], $order_id, $_SESSION['user_id'], $_SESSION['le8_user_id'],
                                        $_SESSION['pay_money'], $_SESSION['nnb'], $_SESSION['goods_name'], $_SESSION['le_appid']);
            sleep(1);
            $order = $this->DAO->get_trade_order($_SESSION['trade_order_id']);
        }

        if($order['status']>0){
            die("订单已经付过款了");
        }

        $user_info = $this->DAO->get_le8_user_info($_SESSION['le8_user_id']);
        if(!$user_info){
            die("请重新付款2");
        }

        $channel_pay_money = $_SESSION['pay_money'];
        if($user_info['nnb']>=$_SESSION['pay_money']){
            $channel_pay_money = 0;
            $this->DAO->user_lock_nnb(1, $user_info['user_id']);
            $this->DAO->pay_order_nnb($order, $_SESSION['pay_money'], 0, 1);
            $order['channel_pay_money'] = 0;
            $order['nnb'] = $_SESSION['pay_money'];

            $guid = $this->create_guid();
            $ip = $this->client_ip();
            $this->DAO->nnb_log($guid, $user_info['user_id'], $order['pay_money'], $order['order_id'], $ip);
        }else{
            $this->DAO->pay_order_channel($order, $_SESSION['pay_money']);
        }

        if($channel_pay_money>0){
            $pay_web = new pay_web();
            $pay_web->ali_pay($order);
        }else{
            $this->DAO->update_order_status(1, $order['id']);
            $url = $this->return_le8($order,1);

            $this->assign("order_id", $order['order_id']);
            $this->assign("result", 'success');
            $this->assign("url", $url);
            $this->assign("info", $order);
            $this->display("pay_result.html");
        }
    }

    private function register_le8_user($le_user_id){
        die("服务器升级中，暂时不能注册。");
        $guid = $this->create_guid();
        $user = array(
            "guid"=>$guid, "reg_ip"=>$this->client_ip(), "reg_time"=>strtotime("now"),
            "login_type"=>0,'reg_from'=>5, "token"=>$guid, "nick_name"=>"L".$le_user_id,
            "user_name"=>"L".$le_user_id, 'channel'=>"le8", "le8_user_id"=>$le_user_id,"login_name"=>""
        );
        $user_id = $this->DAO->register_user($user);
        $user['user_id'] = $user_id;

        $this->DAO->update_user_login_name("n".$user_id, $user_id);
        return $user;
    }

    public function ali_pay_return(){
        //构造通知函数信息
        $alipay = new alipay_notify(ALI_MOBILE_partner, ALI_MOBILE_key, ALI_MOBILE_sec_id, ALI_MOBILE_input_charset);
        // 计算得出通知验证结果
        $verify_result = $alipay->return_verify();

        $this->assign("order_id", $_GET['out_trade_no']);
        $this->assign("result", $_GET['result']);

        if ($verify_result) {
            $order_id       = $_GET ['out_trade_no']; // 订单号
            $order_result   = $_GET ['result']; // 订单状态，是否成功
            $ali_order_id   = $_GET ['trade_no']; // 交易号
            $order_info = $this->DAO->get_order_info($order_id);
            $url = $this->return_le8($order_info, 1);

            $this->assign("url", $url);
            $this->assign("info", $order_info);
            if(!$order_info){
                die("没有查到该订单");
            }
        }
        $this->display("pay_result.html");
    }

    public function return_le8($order, $status){
        $user_id = $order['le8_user_id'];
        $nn_user_id = $order['nn_user_id'];
        $nn_order_id = $order['order_id'];
        $trade_order_id = $order['le8_order_id'];
        $pay_money = $order['pay_money'];
        $timestamp = strtotime("now");
        $sign = md5($user_id. $nn_user_id. $nn_order_id. $trade_order_id. $pay_money. $status. $timestamp. LEBA_KEY);
        $url = "http://ioshs.57k.com/api.php?m=order&a=front&user_id=".$order['le8_user_id']."&nn_user_id=".$order['nn_user_id']."&nn_order_id".$order['order_id']
            ."&trade_order_id=".$order['le8_order_id']."&pay_money=".$order['pay_money']."&status=1&timestamp=".$timestamp."&sign=".$sign;
        return $url;
    }

    public function do_login($user_name, $user_pwd, $timestamp, $sign){
        $err_result = array("result"=>"0","user_id"=>"","error"=>"");
        $sign_str = md5($user_name. $user_pwd. $timestamp. NIUBI_KEY);
        if($sign != $sign_str){
            $err_result['error'] = "sign错误";
            die(jsonUtils::encode($err_result));
        }
        $user_name = urldecode($user_name);
        $user_pwd = urldecode($user_pwd);

        $user_info = $this->DAO->get_nn_user($user_name);
        if(!$user_info){
            $err_result['error'] = "不存在此账号";
            die(jsonUtils::encode($err_result));
        }
        if($user_info['password'] != md5($user_pwd)){
            $err_result['error'] = "密码错误";
        }else{
            $err_result['user_id'] = $user_info['user_id'];
            $err_result['result'] = 1;
        }
        die(jsonUtils::encode($err_result));
    }

    public function push_le8_user($user_id, $nn_user_id, $nn_user_name){
        $url = "http://ioshs.57k.com/api.php?m=order&a=member";
        $timestamp = strtotime("now");
        $sign = md5($user_id.$nn_user_id.$nn_user_name.$timestamp.LEBA_KEY);
        $data = array("user_id"=>$user_id,"nn_user_id"=>$nn_user_id,"nn_user_name"=>$nn_user_name,"timestamp"=>$timestamp,"sign"=>$sign);
        $data = http_build_query($data);
        $url = $url."&".$data;
        $result = $this->request($url, array());
        $this->err_log($data,"le8");
        $this->err_log(var_export($result,1),"le8");
    }

    public function charge_order(){
        $orders = $this->DAO->get_payed_orders();
        if(!$orders){
            die("执行完毕");
        }
        foreach($orders as $k=>$order){
            $timestamp = strtotime("now");
            $url = "http://ioshs.57k.com/api.php?m=order&a=back";
            $id = $order['id'];

            $sign = md5($order['le8_user_id'].$order['nn_user_id'].$order['order_id'].$order['le8_order_id'].($order['pay_money']*100)."1".$timestamp.LEBA_KEY);
            $data = array("user_id"=>$order['le8_user_id'], "nn_user_id"=>$order['nn_user_id'], "nn_order_id"=>$order['order_id'], "trade_order_id"=>$order['le8_order_id'], "pay_money"=>$order['pay_money']*100,
                "status"=>1, "timestamp"=>$timestamp, "sign"=>$sign);
            $url = $url."&".http_build_query($data);
            $result = $this->request($url, array());
            $result = json_decode($result);
            if($result->result == 1 ){
                $this->DAO->finish_order($id,2);
            }
//            else{
//                $this->DAO->finish_order($id,3);
//            }
        }
    }

    public function login_le8(){
        $user_name = urlencode("sasaki2");
        $user_pwd = urlencode("123654");
        $timestamp = strtotime("now");
        $sign = md5($user_name.$user_pwd.$timestamp.LEBA_KEY);
        $url = "http://ioshs.57k.com/api.php?m=member&a=login";
        $data = array("user_name"=>$user_name, "user_pwd"=>$user_pwd, "timestamp"=>$timestamp, "sign"=>$sign);
        $result = $this->request($url, $data);
        $result = json_decode($result);
        print_r($result);
    }

    public function register_le8(){
        error_reporting(E_ALL);
        $this->open_debug();
        $user_name = urlencode("niuniutest_".rand(1,100));
        $user_pwd = urlencode("123654");
        $timestamp = strtotime("now");
        $sign = md5($user_name.$user_pwd.$timestamp.LEBA_KEY);
        $url = "http://ioshs.57k.com/api.php?m=member&a=register";
        $data = array("user_name"=>$user_name, "user_pwd"=>$user_pwd, "timestamp"=>$timestamp, "sign"=>$sign);
        $result = $this->request($url, $data);
        print_r($data);
        echo 'test';
        print_r($result);
        $result = json_decode($result);
        print_r($result);
    }

    public function le8_games(){
        $timestamp = strtotime("now");
        $sign = md5($timestamp.LEBA_KEY);
        $url = "http://ioshs.57k.com/api.php?m=member&a=game";
        $data = array("timestamp"=>$timestamp, "sign"=>$sign);
        $url = $url."&".http_build_query($data);
        $result = $this->request($url, array());
        $result = json_decode($result);
        print_r($result);
    }

    public function get_user_info($le8_user_id, $nn_user_id, $user_type){
        if($user_type==1){
            $user_info = $this->DAO->get_user_info($nn_user_id);
            if(!$user_info){
                die("用户信息错误");
            }
            if(!$user_info['le8_user_id']){
                $this->DAO->update_l8_user($nn_user_id, $le8_user_id);
                $user_info['le8_user_id'] = $le8_user_id;
            }
            if($user_info['le8_user_id']!=$le8_user_id){
                $this->err_log($user_info['le8_user_id']."==>".$le8_user_id."==>".$nn_user_id,"le8");
                die("用户绑定信息错误");
            }
            return $user_info;
        }

        $user_info = $this->DAO->get_le8_user_info($le8_user_id);
        if(!$user_info){
            $user_info = $this->register_le8_user($le8_user_id);
        }
        $this->push_le8_user($le8_user_id, $user_info['user_id'], $user_info['nick_name']);
        return $user_info;
    }

    public function register_check(){
        $user_name = paramUtils::strByPOST("user_name", false);
        $timestamp = paramUtils::strByPOST("timestamp", false);
        $sign = paramUtils::strByPOST("sign", false);
        $result = array("result"=>"0","error"=>"");

        if(strtolower(substr($user_name, 0,1))=="n"){
            $result['error'] = "账户已被使用";
            die(json_encode($result));
        }

        $sign_str = md5($user_name.$timestamp.LEBA_KEY);
        if($sign_str!=$sign){
            $result['error'] = "加密出错";
            die(json_encode($result));
        }

        $result['result'] = "1";
        die(json_encode($result));
    }

    public function register(){
        die("服务器升级中，暂时不能注册。");
        $user_name = paramUtils::strByPOST("user_name", false);
        $user_pwd = paramUtils::strByPOST("user_pwd", false);
        $timestamp = paramUtils::strByPOST("timestamp", false);
        $sign = paramUtils::strByPOST("sign", false);

        $result = array("result"=>"0","error"=>"");

        if(strtolower(substr($user_name, 0,1))=="n"){
            $result['error'] = "账户已被使用";
            die(json_encode($result));
        }

        $sign_str = md5($user_name.$user_pwd.$timestamp.LEBA_KEY);
        if($sign_str!=$sign){
            $result['error'] = "加密出错";
            die(json_encode($result));
        }

        $user_info = $this->DAO->get_user_info_byname($user_name);
        if($user_info){
            $result['error'] = "用户已存在";
            die(json_encode($result));
        }
        $guid = $this->create_guid();
        $user = array(
            "guid"=>$guid, "reg_ip"=>$this->client_ip(), "reg_time"=>strtotime("now"),
            "login_type"=>0,'reg_from'=>6, "token"=>$guid, "nick_name"=>$user_name,
            "user_name"=>$user_name, 'channel'=>"le8", "le8_user_id"=>"","login_name"=>$user_name
        );
        $user_id = $this->DAO->register_user($user);

        $result['result'] = "1";
        $result['user_id'] = $user_id;
        die(json_encode($result));
    }
}
?>
