<?php
COMMON('sdkCore','alipay_secure/alipay_config','alipay_secure/alipay_function','RNCryptor/RNEncryptor');
DAO('ios_pay_dao','user_dao');

class ios_pay extends sdkCore{

    public $DAO;
    public $qa_user_id;

    public function __construct(){
        parent::__construct();
        $this->DAO = new ios_pay_dao();
        $this->qa_user_id = array(71);
    }

    public function ios_secure_order(){
        $timestamp = $_GET['timestamp'];
        $USR_HEADER = $this->get_usr_session('USR_HEADER');
        $app_id = $USR_HEADER['app_id'];
        $app_info = $this->DAO->get_app_info($app_id);
        if($USR_HEADER['md5'] !== md5($app_id.$USR_HEADER['player_id'].$USR_HEADER['serv_id'].$USR_HEADER['user_id'].$USR_HEADER['usertoken'].$app_info['app_key'])){
            $result = array("errcode"=>1,"message"=>"参数不正确");
        }else{
            if(!$this->usr_params['sid']){
                $result = array("errcode"=>1,"message"=>"缺少游戏必要参数");
                die("0".base64_encode(json_encode($result)));
            }
            $this->sid_verify($this->usr_params['sid']);
            $time = strtotime("now");
            $money = $this->DAO->get_money_info($app_id,$USR_HEADER['money_id']);
            if(!$money){
                $result = array("errcode"=>1,"message"=>"商品信息异常。");
                die("0".base64_encode(json_encode($result)));
            }
            if(!$USR_HEADER['cp_order_id']){
                $result = array("errcode"=>1,"message"=>"游戏订单号创建失败。");
                die("0".base64_encode(json_encode($result)));
            }
            $app_order_id = $USR_HEADER['cp_order_id'];
            $SYS_order_id = $this->orderid($USR_HEADER['app_id']);
            $this->create_apple_order($money, $USR_HEADER, $SYS_order_id, $app_order_id, $time);
            $this->err_log(var_export($result,1),'ios_secure_order');
            $sign = $app_id.$SYS_order_id.$timestamp.(int)$money['good_price'].$app_info['app_key'];
            $result = array(
                "errcode" => 0,
                "message" => "订单生成成功",
                "orderid" => $SYS_order_id,
                'cp_order_id' => $app_order_id,
                "goodsname" => $money['good_amount'].$money['good_unit'],
                "goodsdesc" => $money['good_intro'],
                "goodsfee" => (int)$money['good_price'],
                "currency_type" => $USR_HEADER['currency_type'],
                "timestamp" => $timestamp,
                "paytype" => $USR_HEADER['pay_type'],
                "sign" => md5($sign)
            );
        }
        die("0".base64_encode(json_encode($result)));
    }

    public function sid_verify($sid){
        $black_sid_info = $this->DAO->get_black_sid($sid);
        if($black_sid_info){
            if($black_sid_info['is_del']!=1){
                $result = array("errcode"=>1,"message"=>"你的设备有充值存在异常！请联系客服");
                die("0".base64_encode(json_encode($result)));
            }
        }else{
            $sid_count = $this->DAO->verify_sid_count($sid);
            if($sid_count['count'] > 5){
                $id = $this->DAO->add_black_sid($sid,$this->usr_params['adtid']);
                $result = array("errcode"=>1,"message"=>"你的设备有充值存在异常，请联系客服");
                die("0".base64_encode(json_encode($result)));
            }
        }
    }

    public function ios_secure_result($app_id){
        $result = array('result'=>1,'desc'=>'网络请求异常！');
        $this->err_log(var_export($_POST,1),"ios_secure_callback");

        $app_info = $this->DAO->get_app_info($app_id);
        if(!$app_info){
            $result['desc']='游戏信息异常';
            die("0".base64_encode(json_encode($result)));
        }
        $verify_result = $this->param_verify($app_info);
        if(!empty($verify_result)){
            $result['desc']=$verify_result;
            die("0".base64_encode(json_encode($result)));
        }
        //查询订单
        $this->err_log(var_export($_POST,1),"ios_secure_callback");
        $order_info = $this->DAO->get_apple_orders_info($_POST);
        if(!$order_info){
            $result['desc'] = '查询失败';
            die("0".base64_encode(json_encode($result)));
        }elseif($order_info['status']=='0'){
            $_POST['receipt_md5'] = md5($_POST['receipt']);
            $receipt_md5 = $this->DAO->get_apple_receipt_md5($_POST['receipt_md5']);
            if(!empty($_POST['apple_order_id'])){
                $apple_order_id = $this->DAO->get_apple_order_id($_POST['apple_order_id']);
            }
            if(empty($receipt_md5) && empty($apple_order_id)){
                $this->DAO->update_apple_order($order_info['id'],$_POST,1);// 客户端支付
                $this->ios_verify_by_order($order_info['id']);
                $result = array('result'=>2,'desc'=>'请求成功！');
            }else{
                $this->err_log(var_export($_POST,1),'Repeat_order');
                $result = array('result'=>2,'desc'=>'请求成功2！');
            }
        }else{
            $result = array('result'=>2,'desc'=>'请求成功3！');
        }
        die("0".base64_encode(json_encode($result)));
    }

    public function ios_verify_by_order($id){
        $order = $this->DAO->get_appstore_order($id);
        if($order){
            $sandbox = 3;
            $post_data = array("receipt-data" => $order['receipt']);
            $callback = $this->request_appstore(appstore_url,$post_data);
            if($callback['status']=='21007'){
                $sandbox = 1;
                $result = $this->request_appstore(appstore_sandbox_url,$post_data);
                $this->err_log(var_export($callback,1),'app_callback_log');
                if($result['status']=='0'){
                    $this->err_log(var_export($result,1),'app_callback_success_log');
                    $this->apple_order_verify($order,$result,$sandbox);
                    $this->DAO->update_apple_order_status(2,$result['status'],$sandbox,$order['id']);
                    $this->apple_order_callbak($order['id']);
                }elseif(!empty($result)){
                    $this->DAO->update_apple_order_status(4,$result['status'],$sandbox,$order['id']);
                }else{
                    $this->DAO->update_apple_order_status(1,$result['status'],$sandbox,$order['id']);
                }
            }elseif($callback['status']=='0'){
                $this->err_log(var_export($callback,1),'app_callback_success_log');
                $sandbox = 2;
                $this->apple_order_verify($order,$callback,$sandbox);
                $this->DAO->update_apple_order_status(2,$callback['status'],$sandbox,$order['id']);
                $this->apple_order_callbak($order['id']);
            }elseif(!empty($callback)){
                $sandbox = 2;
                $this->DAO->update_apple_order_status(4,$callback['status'],$sandbox,$order['id']);
            }else{
                $this->DAO->update_apple_order_status(1,'999',$sandbox,$order['id']);
            }
        }
    }

    public function apple_order_verify($order,$callback,$sandbox){
        $result = array('result'=>1,'desc'=>'订单验证失败！');
        if(!($order['apple_order_id'] == $callback['receipt']['transaction_id'])){
            $this->DAO->update_apple_order_status(5,$callback['status'],$sandbox,$order['id']);//订单异常
            $result['desc'] = '订单信息不匹配';
            die("0".base64_encode(json_encode($result)));
        }
        $money_info = $this->DAO->get_money_by_goodid($order['goods_id']);
        if(!($money_info['good_code'] == $callback['receipt']['product_id'])){
            $this->DAO->update_apple_order_status(6,$callback['status'],$sandbox,$order['id']);//商品ID不配合
            $result['desc'] = '商品信息异常';
            die("0".base64_encode(json_encode($result)));
        }
    }


    public function ios_verify_order(){
        $appstore_orders = $this->DAO->get_appstore_orders();
        if(!$appstore_orders){
            die("执行完毕");
        }
        foreach($appstore_orders as $k=>$order){
            $sandbox = 3;
            $post_data = array("receipt-data" => $order['receipt']);
            $callback = $this->request_appstore(appstore_url,$post_data);
            $this->err_log(var_export($callback,1),'app_callback_logs');
            if($callback['status']=='21007'){
                $sandbox = 1;
                $result = $this->request_appstore(appstore_sandbox_url,$post_data);
                $this->err_log(var_export($callback,1),'app_callback_log');
                if($result['status']=='0'){
                    $this->err_log(var_export($result,1),'app_callback_success_log');
                    $this->apple_order_verify($order,$result,$sandbox);
                    $this->DAO->update_apple_order_status(2,$result['status'],$sandbox,$order['id']);
                    $this->apple_order_callbak($order['id']);
                }elseif(!empty($result)){
                    $this->DAO->update_apple_order_status(4,$result['status'],$sandbox,$order['id']);
                }else{
                    $this->DAO->update_apple_order_status(1,$result['status'],$sandbox,$order['id']);
                }
            }elseif($callback['status']=='0'){
                $this->err_log(var_export($callback,1),'app_callback_success_log');
                $sandbox = 2;
                $this->apple_order_verify($order,$callback,$sandbox);
                $this->DAO->update_apple_order_status(2,$callback['status'],$sandbox,$order['id']);
                $this->apple_order_callbak($order['id']);
            }elseif(!empty($callback)){
                $sandbox = 2;
                $this->DAO->update_apple_order_status(4,$callback['status'],$sandbox,$order['id']);
            }else{
                $this->DAO->update_apple_order_status(1,'999',$sandbox,$order['id']);
            }
        }

        die("操作成功");
    }
    public function apple_order_callbak($id){
        $order = $this->DAO->get_apple_payed_order($id);
        if($order){
            $timestamp = strtotime("now");
            $url = $order['sdk_charge_url'];
            $id = $order['id'];
            $app_key = $order['app_key'];
            $order_id = $order['order_id'];
            $app_id = $order['app_id'];
            $serv_id = $order['serv_id'];
            $usr_id = $order['buyer_id'];
            $player_id = $order['role_id'];
            $app_order_id = $order['cp_order_id'];
            $coin = 1;
            $money = $order['pay_money'];
            $add_time = $order['buy_time'];
            $good_code = $order['good_code'];
            $payExpandData = $order['payExpandData'];
            $money_info = $this->DAO->get_money_info($app_id,$good_code);
            $coin = $money_info['good_amount'];
            if(!empty($order['apple_id'])){
                $apple_info = $this->DAO->get_apple_id_info($order['app_id'],$order['apple_id']);
                if(!empty($apple_info['callback_url'])){
                    $url = $apple_info['callback_url'];
                }
            }
            if($app_id=='1076'){
                $good_code = preg_replace('/\D/s', '', $good_code);
//                $url = 'http://h5.chmteam.com:8081/charge_server/pay/by/niuniu';
                $url = 'http://47.89.253.30:8081/charge_server/pay/by/niuniu';
            }
            $apple_order = $this->DAO->get_apple_order_id($order['apple_order_id']);
            if(($apple_order['cp_order_id']!=$app_order_id) && $apple_order['status'] > 2){
                $this->DAO->update_order_status($id, 6);//
                return ;
            }
            $this->DAO->update_apple_order_charge($id);
            $sign_str = md5($app_id.$serv_id.$usr_id.$player_id.$app_order_id.$coin.$money.$add_time.$app_key);
            $post_ary = array("app_id"=>$app_id, "serv_id"=>$serv_id, "usr_id"=>$usr_id, "player_id"=>$player_id,
                "app_order_id"=>$app_order_id, "coin"=>$coin, "money"=>$money, "add_time"=>$add_time,"payExpandData"=>$payExpandData,
                "good_code"=>$good_code, "sign"=>$sign_str, "order_id"=>$order_id,"sandbox"=>$order['sandbox']);
            if($app_id == '1070'){
                $post_ary = json_encode($post_ary);
            }
            $result = $this->request($url, $post_ary);

            $result = json_decode($result);
            $this->err_log(var_export($post_ary,1),"apple_order_call_back");
            $this->err_log(var_export($url,1),"apple_order_call_back");
            $this->err_log(var_export($result,1),"apple_order_call_back");
            if("1" == $result->success){
                $this->DAO->update_order_status($id, 3);
            }
        }
    }


    public function apple_charge_order(){
//        ini_set("display_errors","On");
//        error_reporting(E_ALL);
        $orders = $this->DAO->get_apple_payed_orders();
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
            $app_order_id = $order['cp_order_id'];
            $coin = 1;
            $money = $order['pay_money'];
            $add_time = $order['buy_time'];
            $good_code = $order['good_code'];
            $payExpandData = $order['payExpandData'];
            $money_info = $this->DAO->get_money_info($app_id,$good_code);
            $coin = $money_info['good_amount'];
//            $this->DAO->update_apple_order_charge($id);
            if(!empty($order['apple_id'])){
                $apple_info = $this->DAO->get_apple_id_info($order['app_id'],$order['apple_id']);
                if(!empty($apple_info['callback_url'])){
                    $url = $apple_info['callback_url'];
                }
            }
            if($app_id=='1076'){
                $good_code = preg_replace('/\D/s', '', $good_code);
//                $url = 'http://h5.chmteam.com:8081/charge_server/pay/by/niuniu';
                $url = 'http://47.89.253.30:8081/charge_server/pay/by/niuniu';
            }

            $apple_order = $this->DAO->get_apple_order_id($order['apple_order_id']);
            if(($apple_order['cp_order_id']!=$app_order_id) && $apple_order['status'] > 2){
                $this->DAO->update_order_status($id, 6);//
                return ;
            }
            $sign_str = md5($app_id.$serv_id.$usr_id.$player_id.$app_order_id.$coin.$money.$add_time.$app_key);
            $post_ary = array("app_id"=>$app_id, "serv_id"=>$serv_id, "usr_id"=>$usr_id, "player_id"=>$player_id,
                "app_order_id"=>$app_order_id, "coin"=>$coin, "money"=>$money, "add_time"=>$add_time,"payExpandData"=>$payExpandData,
                "good_code"=>$good_code, "sign"=>$sign_str, "order_id"=>$order_id,"sandbox"=>$order['sandbox']);
            if($app_id == '1070'){
                $post_ary = json_encode($post_ary);
            }
            $result = $this->request($url, $post_ary);
            $status = $this->DAO->get_order_debug_info();
            $this->err_log(var_export($post_ary,1),"apple_order_call_back");
            $this->err_log(var_export($url,1),"apple_order_call_back");
            $this->err_log(var_export($result,1),"apple_order_call_back");
            if($status['apple'] == 1){
                $this->err_log(var_export($post_ary,1),"apple_debug");
                $this->err_log(var_export($url,1),"apple_debug");
                $this->err_log(var_export($result,1),"apple_debug");
            }
            $result = json_decode($result);
            if("1" == $result->success){
                $this->DAO->update_order_status($id, 3);
            }
        }
    }

    public function appstore_verify($params){
        $url_live = "https://buy.itunes.apple.com/verifyReceipt";
        $url_sandbox = "https://sandbox.itunes.apple.com/verifyReceipt";
        $sandbox = 2;
        $post_data = json_encode(array("receipt-data" => $params['receipt']));
        $callback = $this->request_appstore($url_live,$post_data);

        if($callback['status']=='21007'){
            $sandbox = 1;
            $result = $this->request_appstore($url_sandbox,$post_data);
            if($result['status']=='0'){
                $this->err_log(var_export($callback,1),'app_callback_log');
                $this->DAO->update_apple_order_status($params['id'],$result['status'],$sandbox);
                return "success";
            }else{
                $this->err_log(var_export($callback,1),'app_callback_log');
                $this->DAO->update_apple_order_status($params['id'],$result['status'],$sandbox);
                return "fail";
            }
        }elseif($callback['status']=='0'){
            $this->err_log(var_export($callback,1),'app_callback_log');
            $this->DAO->update_apple_order_status($params['id'],$callback['status'],$sandbox);
            return "success";
        }elseif(!empty($callback)){
            $this->err_log(var_export($callback,1),'app_callback_log');
            $this->DAO->update_apple_order_status($params['id'],$callback['status'],$sandbox);
            return "fail";
        }else{
            return $this->appstore_verify($params);
        }
        return "fail";
    }

    public function request_appstore($url,$data){
        $reslut = $this->request($url,$data);
        $reslut = json_decode($reslut,1);
        return $reslut;
    }

    protected function create_apple_order($money, $header, $YD_order_id, $app_order_id, $time){
        $order['app_id']        = $header['app_id'];
        $order['order_id']       = $YD_order_id;
        $order['cp_order_id']  = $app_order_id;
        $order['buyer_id']    = $header['user_id'];
        $order['serv_id']   = $header['serv_id'];
        $order['role_id']     = $header['player_id'];
        $order['role_name']   = $header['player_name'];
        $order['title']       = $money['good_name'];
        $order['goods_id']  = $money['id'];
        $order['pay_money']   = $money['good_price'];
        $order['ip']     = $this->client_ip();
        $order['channel']   = $header['channel'];
        $order['buy_time']    = $time;
        $order['status']      = 0;
        $order['payExpandData'] = $header['payexpanddata'];
        $order['apple_id'] = isset($header['apple_id'])?$header['apple_id']:'';
        $order['sid'] = isset($this->usr_params['sid'])?$this->usr_params['sid']:'';
        $order['sid_md5'] = isset($this->usr_params['sid'])?md5($this->usr_params['sid']):'';
        $order['idfa'] = isset($this->usr_params['adtid'])?$this->usr_params['adtid']:'';
        if(!$this->DAO->create_apple_order($order)){
            $result = array("errcode"=>1,"message"=>"订单创建失败!");
            die("0".base64_encode(json_encode($result)));
        }
        return $order;
    }

    public function param_verify($app_info){
        $msg = '';
        $data = $_POST;
        if(!$data){
            $msg = '参数丢失';
            return $msg;
        }
        if(!$data['receipt'] || !$data['apple_order_id'] || !$data['cp_order_id'] || !$data['niu_order_id'] || !$data['timestamp'] || !$data['sign']){
            $msg = '参数异常！';
            return $msg;
        }
        if(strlen($data['receipt'])<20){
            $msg = 'receipt异常！';
            return $msg;
        }
        $sign = $app_info['app_id'].$data['apple_order_id'].$data['cp_order_id'].$data['niu_order_id'].$data['timestamp'].$app_info['app_key'];
        if(md5($sign)!= $data['sign']){
            $msg = '加密出错！';
            return $msg;
        }
        return $msg;
    }

    public function array_to_xml($arr=array()){
        $xml = '<xml>';
        foreach ($arr as $key => $val){
            if(is_array($val)){
                $xml .= "<".$key.">".$this->array_to_xml($val)."</".$key.">";
            }else{
                $xml .= "<".$key.">".$val."</".$key.">";
            }
        }
        $xml .= "</xml>";
        return $xml;
    }

    public function set_usr_session($key, $data){
        $this->DAO->set_usr_session($key, $data);
    }

    public function get_usr_session($key=''){
        return $this->DAO->get_usr_session($key);
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

}