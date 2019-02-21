<?php
COMMON('baseCore');
class super_pay_web extends baseCore{

    public $DAO;
    public $id;
    public $real_app_id;
    public $qa_user_id;
    public $usr_params;

    protected $exchanges;
    protected $api_serv_url;
    protected $api_user_url;
    protected $api_order_url;

    public function __construct(){
        parent::__construct();
        $this->DAO = new super_pay_dao();
        $this->qa_user_id = array('71');
    }

    public function create_super_ch_order(){
//        $this->open_debug();
        $USR_HEADER = $this->get_usr_session('SUPER_ORDER_HEADER');
        $app_id = $USR_HEADER['appId'];
        $super_ch_info = $this->DAO->get_super_app_info($app_id);
        if(empty($super_ch_info)){
            $this->result_error('游戏已下架');
        }
        $ch_info = $this->DAO->get_ch_by_appid($app_id,$USR_HEADER['platform']);
        if(empty($ch_info)){
            $this->result_error('渠道信息异常');
        }elseif($ch_info['status']=='4'){
            $this->result_error('充值已关闭请联系客服');
        }

        if($ch_info['is_open']=='1'){
            $channel_money = $this->DAO->get_channel_sum_moeny($app_id,$USR_HEADER['platform']);
            if(($channel_money['sum']+$ch_info['warn_money']) > $ch_info['recharge_count']){
                $balance = $ch_info['recharge_count'] - $channel_money['sum'];
                if($balance < 0){
                    $balance = 0;
                }
                $notice_mmc = $this->DAO->get_mmc_warn_money($ch_info['id']);
                if(empty($notice_mmc)){
                    $send_result = $this->send_sms($ch_info['mobile'],array($ch_info['channel'],$balance),"234120");
                    $this->err_log(var_export($send_result,1),'h5_super_warn_send');
                    if($send_result==0){
                        $this->DAO->set_mmc_warn_money($ch_info['id'],$ch_info);
                    }
                }
            }
            if($channel_money['sum'] >= $ch_info['recharge_count']){
                $this->result_error('充值限额不足,请及时联系客服。');
            }
        }
        $time = strtotime("now");
        if(!$USR_HEADER['sdkgoodsid']){
            $this->result_error('商品ID未传递');
        }
        $money = $this->DAO->get_super_money_info($app_id,$USR_HEADER['sdkgoodsid']);
        $money['unit_price'] = $money['good_price'];
        if(!empty($USR_HEADER['goodmultiple'])){
            $money['good_price'] = $money['unit_price'] * $USR_HEADER['goodmultiple'];
        }
        if($ch_info['is_open']=='1'){
            if(($channel_money['sum'] + $money['good_price']) > $ch_info['recharge_count']){
                $surplus =  $ch_info['recharge_count'] - $channel_money['sum'];
                $this->result_error("平台余额为".$surplus."元, 请联系客服。");
            }
        }
        $super_order_id = $this->super_orderid($USR_HEADER['appId']);
        $app_order_id = $USR_HEADER['billno'];
        $order_info = $this->create_super_order($money, $USR_HEADER, $super_order_id, $app_order_id, $time);
        if($USR_HEADER['platform'] =='hwquick' && $order_info){
            if($order_info['buyer_id'] == '1735700000005035'){
                $money['good_price'] = '0.01';
            }
            $this->hwquick_sign($money,$super_order_id,$ch_info);
        }elseif($USR_HEADER['platform'] =='zhijian' && $order_info){
            $this->zhijian_sign($money,$super_order_id,$ch_info);
        }elseif($USR_HEADER['platform'] =='jbsj' && $order_info){
            $this->jbsj_sign($money,$super_order_id,$ch_info);
        }elseif($USR_HEADER['platform'] =='hjy' && $order_info){
            $this->hjy_sign($money,$super_order_id,$ch_info,$order_info);
        }elseif($USR_HEADER['platform'] =='tianxing' && $order_info){
            $this->tianxing_sign($money,$super_order_id,$ch_info,$order_info);
        }elseif($USR_HEADER['platform'] =='xunrui' && $order_info){
            if($order_info['buyer_id'] == '965878' || $order_info['buyer_id'] == '960833'){
                $money['good_price'] = '0.01';
            }
            $this->xunrui_sign($money,$super_order_id,$ch_info);
        }elseif($USR_HEADER['platform'] =='lanmo' && $order_info){
            if($order_info['buyer_id'] == '689658' || $order_info['buyer_id'] == '689638'){
                $money['good_price'] = '0.01';
            }
            $this->lanmo_sign($money,$super_order_id,$ch_info);
        }elseif($USR_HEADER['platform'] =='iqiyi' && $order_info){
            if($order_info['buyer_id'] == '5451917916' || $order_info['buyer_id'] == '2663931557'){
                $money['good_price'] = '0.01';
            }
            $this->iqiyi_sign($money,$super_order_id,$ch_info,$order_info);
        }elseif($USR_HEADER['platform'] =='xiaomi_h5' && $order_info){
            if($order_info['buyer_id'] == '4685756'){
                $money['good_price'] = '0.01';
            }
            $this->xiaomi_sign($money,$super_order_id,$ch_info);
        }elseif($USR_HEADER['platform'] =='oppo_h5' && $order_info){
            if($order_info['buyer_id'] == '398749980'){
                $money['good_price'] = '0.01';
            }
            $this->oppo_sign($money,$super_order_id,$ch_info);
        }else{
            $this->result_error("订单创建失败！");
        }
    }
    public function zhijian_sign($money,$super_order_id,$ch_info){
        $zhijian_data = array(
            'orderid'=>$super_order_id,
            'cpgameid'=>$ch_info['param1'],
            'cpguid'=>$ch_info['app_id'],
            'goodsname'=>$money['good_name'],
            'fee'=>$money['good_price'],
            'timestamp'=>time(),
            'app_key'=>$ch_info['app_key']
        );
        $result = array("result" => 1,"desc"=>'订单请求成功','data'=>$zhijian_data);
        $this->err_log(var_export($result,1),'h5_zhijian_callback');
        die(json_encode($result));
    }

    public function jbsj_sign($money,$super_order_id,$ch_info){
        $data = array(
            'orderid'=>$super_order_id,
            'timestamp'=>time(),
            'fee'=>$money['good_price'],
            'amount'=>$money['good_amount'],
            'gameid'=>$ch_info['app_id']
        );
        $result = array("result" => 1,"desc"=>'订单请求成功','data'=>$data);
        $this->err_log(var_export($result,1),'h5_jbsj_callback');
        die(json_encode($result));
    }

    public function hjy_sign($money,$super_order_id,$ch_info,$order_info){
        $data = array(
            'server'=>$order_info['serv_id'],
            'role'=>$order_info['role_id'],
            'goodsId'=>$money['good_code'],
            'goodsName'=>$money['good_name'],
            'money'=>$money['good_price'],
            'cpOrderId'=>$super_order_id
        );
        $result = array("result" => 1,"desc"=>'订单请求成功','data'=>$data);
        $this->err_log(var_export($result,1),'h5_jbsj_callback');
        die(json_encode($result));
    }

    public function tianxing_sign($money,$super_order_id,$ch_info,$order_info){
        $data = array(
            'server'=>$order_info['serv_id'],
            'role'=>$order_info['role_id'],
            'goodsId'=>$money['good_code'],
            'goodsName'=>$money['good_name'],
            'money'=>$money['good_price']*100,
            'cpOrderId'=>$super_order_id
        );
        $result = array("result" => 1,"desc"=>'订单请求成功','data'=>$data);
        $this->err_log(var_export($result,1),'h5_tianxing_callback');
        die(json_encode($result));
    }

    public function xunrui_sign($money,$super_order_id,$ch_info){
        $data = array(
            'product'=>$money['good_name'],
            'money'=>$money['good_price']*100,
            'orderid'=>$super_order_id,
            'app_id'=>$ch_info['app_id']
        );
        $result = array("result" => 1,"desc"=>'订单请求成功','data'=>$data);
        $this->err_log(var_export($result,1),'h5_xunrui_callback');
        die(json_encode($result));
    }

    public function lanmo_sign($money,$super_order_id,$ch_info){
        $data = array(
            'product'=>$money['good_name'],
            'money'=>$money['good_price']*100,
            'orderid'=>$super_order_id,
            'app_id'=>$ch_info['app_id']
        );
        $result = array("result" => 1,"desc"=>'订单请求成功','data'=>$data);
        $this->err_log(var_export($result,1),'h5_lanmo_callback');
        die(json_encode($result));
    }

    public function iqiyi_sign($money,$super_order_id,$ch_info,$order_info){
        $data = array(
            'server_id'=>$order_info['serv_id'],
            'money'=>$money['good_price'],
            'extra_param'=>$super_order_id,
            'game_id'=>$ch_info['app_id']
        );
        $result = array("result" => 1,"desc"=>'订单请求成功','data'=>$data);
        $this->err_log(var_export($result,1),'h5_iqiyi_callback');
        die(json_encode($result));
    }

    public function xiaomi_sign($money,$super_order_id,$ch_info){
        $data = array(
            'price'=>$money['good_price']*100,
            'extend'=>$super_order_id,
            'amount'=>1,
            'sname'=>$money['good_name'],
            'app_id'=>$ch_info['app_id'],
            'game_id'=>$ch_info['param1']
        );
        $result = array("result" => 1,"desc"=>'订单请求成功','data'=>$data);
        $this->err_log(var_export($result,1),'h5_xiaomi_callback');
        die(json_encode($result));
    }

    public function oppo_sign($money,$super_order_id,$ch_info){
        $data = array(
            'price'=>$money['good_price']*100,
            'appName'=>$ch_info['app_name'],
            'appVersion'=>$ch_info['version'],
            'appKey'=>$ch_info['app_key'],
            'productDesc'=>$money['good_intro'],
            'productName'=>$money['good_name'],
            'orderId'=>$super_order_id,
        );
        $result = array("result" => 1,"desc"=>'订单请求成功','data'=>$data);
        $this->err_log(var_export($result,1),'h5_oppo_callback');
        die(json_encode($result));
    }

    public function hwquick_sign($money,$super_order_id,$ch_info){

        $huawei_data = array(
            'productName'=>$money['good_name'],
            'productDesc'=>'游戏代币充值',
            'applicationID'=>$ch_info['app_id'],
            'requestId'=>$super_order_id,
            'amount'=>sprintf("%.2f",$money['good_price']),
            'merchantId'=>$ch_info['param1'],
            'sdkChannel'=>'3',
            'urlver'=>'2',
        );
        ksort($huawei_data);
        $arg = '';
        foreach($huawei_data as $key=>$item){
            if(!empty($item)){
                $arg = $arg.$key.'='.$item.'&';
            }
        }
        $content = substr($arg,0,strlen($arg)-1);
        $strSign = $this->do_rsa2($content,$ch_info['super_id'].'_hwquick_pay_private_key.pem');
        if(empty($strSign)){
            $this->err_log(var_export($huawei_data,1),'h5_hwquick_callback_error');
            $this->err_log(var_export($content,1),'h5_hwquick_callback_error');
            $this->result_error("hw加密出错!");
        }
        $huawei_data['sign'] = $strSign;
        $huawei_data['signType'] = 'RSA256';
        $huawei_data['userName'] = '海南盈趣互动娱乐有限公司';
        $huawei_data['param2'] = $ch_info['param2'];
        $result = array("result" => 1,"desc"=>'订单请求成功','data'=>$huawei_data,'content'=>$content);
        $this->err_log(var_export($result,1),'h5_hwquick_callback');
        die(json_encode($result));
    }

    public function zhijian_login($params){
        $result = array("result" => 0, "desc" => "游戏信息异常");
        $channel_info = $this->DAO->get_ch_by_appid($params['appId'], $params['platform']);
        if(empty($channel_info)){
            $result['desc'] = '游戏信息异常';
        }
        $sign_str = 'channelid='.$params['channelid'].'&channeluid='.$params['channeluid'].'&cpgameid='.$params['cpgameid'].'&ext='.htmlspecialchars_decode($params['ext']);
        $sign_str = $sign_str.'&qqesnickname='.$params['qqesnickname'].'&qqestimestamp='.$params['qqestimestamp'].'&qqesuid='.$params['qqesuid'].'&'.$channel_info['app_key'];
        $result = array("result" => 1, "desc" => "请求成功","sign"=>md5($sign_str));
        die(json_encode($result));
    }

    public function jbsj_login($params){
        $result = array("result" => 0, "desc" => "游戏信息异常");
        $channel_info = $this->DAO->get_ch_by_appid($params['appId'], $params['platform']);
        if(empty($channel_info)){
            $result['desc'] = '游戏信息异常';
        }
        $sign_str = $params['username'].$channel_info['app_key'].$params['serverid'].$params['time'];
        $result = array("result" => 1, "desc" => "请求成功","sign"=>md5($sign_str));
        die(json_encode($result));
    }

    public function hjy_login($params){
        $result = array("result" => 0, "desc" => "游戏信息异常");
        $channel_info = $this->DAO->get_ch_by_appid($params['appId'], $params['platform']);
        if(empty($channel_info)){
            $result['desc'] = '游戏信息异常';
        }
        $sign_str = 'gameId='.$params['gameId'].'&time='.$params['time'].'&uid='.$params['uid'].'&userName='.$params['userName'].'&key='.$channel_info['app_key'];
        $result = array("result" => 1, "desc" => "请求成功","sign"=>md5($sign_str),"sign_str"=>$sign_str);
        die(json_encode($result));
    }

    public function zhijian_pay($params){
        $result = array("result" => 0, "desc" => "游戏信息异常");
        $channel_info = $this->DAO->get_ch_by_appid($params['appId'], $params['platform']);
        if(empty($channel_info)){
            $result['desc'] = '游戏信息异常';
        }
        $sign_str = 'channelid='.$params['channelid'].'&channeluid='.$params['channeluid'].'&cpgameid='.$params['cpgameid'].'&cpguid='.$params['cpguid'].'&ext='.htmlspecialchars_decode($params['ext']);
        $sign_str = $sign_str.'&fee='.$params['fee'].'&goodsname='.$params['goodsname'].'&order='.$params['order'].'&qqesuid='.$params['qqesuid'].'&timestamp='.$params['timestamp'].'&'.$channel_info['app_key'];
        $result = array("result" => 1, "desc" => "请求成功","sign"=>md5($sign_str));
        die(json_encode($result));
    }

    public function jbsj_pay($params){
        $result = array("result" => 0, "desc" => "游戏信息异常");
        $channel_info = $this->DAO->get_ch_by_appid($params['appId'], $params['platform']);
        if(empty($channel_info)){
            $result['desc'] = '游戏信息异常';
        }
        $sign_str = 'basicword'.$params['orderno'].$params['uid'].$channel_info['app_id'].$params['fee'].$params['fee'];
        $sign_str = $sign_str.$params['amount'].$params['payment'].$params['timestamp'];
        $result = array("result" => 1, "desc" => "请求成功","sign"=>md5($sign_str));
        die(json_encode($result));
    }

    public function hjy_pay($params){
        $result = array("result" => 0, "desc" => "游戏信息异常");
        $channel_info = $this->DAO->get_ch_by_appid($params['appId'], $params['platform']);
        if(empty($channel_info)){
            $result['desc'] = '游戏信息异常';
        }
        $sign_str = 'cpOrderId='.$params['cpOrderId'].'&gameId='.$params['gameId'].'&goodsId='.$params['goodsId'].'&goodsName='.$params['goodsName'];
        $sign_str = $sign_str.'&money='.$params['money'].'&role='.$params['role'].'&server='.$params['server'].'&time='.$params['time'];
        $sign_str = $sign_str.'&uid='.$params['uid'].'&key='.$channel_info['param1'];
        $result = array("result" => 1, "desc" => "请求成功","sign"=>md5($sign_str));
        die(json_encode($result));
    }

    public function do_rsa2($content,$pem_name){
        if(empty($pem_name)){
            return '';
        }
        $filename = PREFIX.'/common/super/keys/'.$pem_name;
        if (!file_exists($filename)) {
            return '';
        }
        $priKey = @file_get_contents($filename);
        $openssl_private_key = @openssl_get_privatekey($priKey);
        @openssl_sign($content, $signature, $openssl_private_key, 'sha256WithRSAEncryption');
        $sign=@base64_encode($signature);
        return $sign;
    }


    protected function create_super_order($money, $header, $order_id, $app_order_id, $time){
        $order['app_id']        = $header['appId'];
        $order['order_id']       = $order_id;
        $order['app_order_id']  = $app_order_id;
        $order['pay_channel']   = 1;
        $order['buyer_id']    = $header['userId'];
        $order['role_id']     = $header['roleId'];
        $order['product_id']  = $money['id'];
        $order['unit_price']  = $money['unit_price'];
        $order['title']       = $money['good_name'];
        $order['role_name']   = $header['roleName'];
        $order['amount']      = $money['good_amount'];
        $order['pay_money']   = $money['good_price'];
        $order['discount']    = 10;
        $order['pay_price']   = $money['good_price'];
        $order['status']      = 0;
        $order['buy_time']    = $time;
        $order['ip']     = $this->client_ip();
        $order['serv_id']   = $header['serverId'];
        $order['channel']   = $header['platform'];
        $order['payExpandData'] = isset($header['extraInfo'])?$header['extraInfo']:'';
        $order['serv_name'] = $header['serverName'];
        if(!$this->DAO->create_super_order($order)){
            $this->result_error("订单创建失败!");
        }
        return $order;
    }

    public function super_orderid($app_id){
        $order_id = "sp".$app_id.date('YmdHis').rand(10000000000,99999999999);
        $_SESSION['order_id'] = $order_id;
        return $order_id;
    }

    public function super_params_check($data){
        if(!is_array($data)){
            $this->result_error('缺少必要参数！');
        }
        foreach($data as $key=>$item){
            switch ($key){
                case "appId":
                    if(empty($item)){
                        $this->result_error('缺少游戏ID！');
                    }
                    break;
                case "userId":
                    if(empty($item)){
                        $this->result_error('缺少用户ID！');
                    }
                    break;
                case "roleId":
                    if(empty($item)){
                        $this->result_error('缺少角色ID！');
                    }
                    break;
                case "serverId":
                    if(empty($item)){
                        $this->result_error('缺少区服ID！');
                    }
                    break;
                case "billno":
                    if(empty($item)){
                        $this->result_error('缺少商户订单！');
                    }
                    break;
                case "sdkgoodsid":
                    if(empty($item)){
                        $this->result_error('商品信息异常！');
                    }
                    break;
            }
        }
        return true;
    }

    public function set_usr_session($key, $data){
        $this->DAO->set_usr_session($key, $data);
    }

    public function get_usr_session($key=''){
        return $this->DAO->get_usr_session($key);
    }

    public function result_error($msg){
        $result = array("result" => 0, "desc" => "网络异常");
        if($msg){
            $result['desc'] = $msg;
        }
        $this->err_log(var_export($result,1),'h5_hwquick_callback_error');
        die(json_encode($result));
    }

    public function rsa_to_params($data){
        $new_array = array();
        if(!is_array($data)){
            return $new_array;}
        foreach($data as $name=>$val){
            $new_array[$name] = $this->rsa_decrypt_params(urldecode($val));
        }
        return $new_array;
    }

    public function rsa_decrypt_params($data){
        $PrivateKey = '-----BEGIN RSA PRIVATE KEY-----
MIICXgIBAAKBgQC4h7DNhpOBaHDrK51CEizEFGQ6sWhRFPdAj/k1BAvYPMpkqKRa
k1unHE3QOKDq5Fo4CJs7gbPNU+m13tudfU7oXs9lL26ZgDDvIJP9ogZdg4uH0Z3o
40QUtkE8eQ1PfA1I2zwxWWq/nqNmZZk4rfT8W+cxjqyt8YdkOv/ARXRuCwIDAQAB
AoGASfTIHXckQycyqm4udr6GBogNq6BSqLx4Y/3P6TmX7bBfhXw5crvAbfdgEGXB
yKKArhh07qKLB23sKyIIbtQ7/SNJaAjHzvWlJmaxKI/yauoS12gY9CIzgGhUgecV
HKlKQNndjgs1L5YKsAKmSNTXZnhEtUYnvv0bj5D1RXz6UkECQQDfZ5y9dB68F9V4
uWx/RhMSHATEhyOssYTHNlyP3yjFcNjGOU3UGIQJzGr7Lgwu1B4j2AvwFFTbRFz6
/LO5U2RbAkEA03QR83ie2kemFXxscHqsXtx7cJO8qf5QHZ8doRrvCalNP0SUpSkd
UQV4291FTNCkXgoQUyrObP/yXjy5XmuMEQJBAMxB330YfkFbSUJnxltXpngYRgOp
y2RJqiy590dTseNTmd8i5ZXWFGMhE280Ws82AZikH8YR0MPpbVnNUkVPiaECQQDT
F4+IIIVs4ZQi5PiYfU6w0KkGZOY2SmSOfbcliu7RFUvBemRuURIsPSs/SrERE0TT
gHZ1oEk9NXIus65WXUURAkEAzhojCIHmqf29XNfQPoFU94+1tBsix3DDuau/kF1x
Qb3BSxmkNsUhsBN8KSWqCPG64K1KxEoT8wVl/pu3MYCMkQ==
-----END RSA PRIVATE KEY-----';
        openssl_private_decrypt(base64_decode($data),$decrypted,$PrivateKey);
        return $decrypted;
    }

    public function rsa2_decrypt_params($content){
        $devPrivate = "MIIEuwIBADANBgkqhkiG9w0BAQEFAASCBKUwggShAgEAAoIBAQDjzQUtZ7J7b7Q9jW7fUfODXzbawVw+vF8HEEbXYQ/ityJYA7DYfWN1UP3vnwURf7PiZ5gNXIKzab4gITRp4DS+c7geJhVnTisoIxrFSTUoSfoAjSSUOaxyVBp5bNVbrHdLZunLt+mSt6WvPXF4aZVN8InS4QtpAceTYxozI8uJ+SdHPuSEDbG0DB0dYKbQxoCQo63o6qCo/uQYlwJqUOWH5AiKzwG5M4mKSHK5+nFHyKt0etSZiAcCFSfCxc6w9Hx2QLbPlk9Wb0Zx0cqwGRz7cDAqu4TpM8xfCH5q9M5yQM+jxPdnviAnr7+VdMy0EY7F+/hrgHewq2END3uVd8klAgMBAAECgf9FsPSEPw5IdPyXpbmI+pQXL5peffmEWMZryG4kAtWp8AFRlxgRfXTkl8HJpmrSqiKobaNaPyNEiHiDem7XKgSHwjvEMjH2xhQz227ECNP6/89NmD9j3q8iqDySE2cpgYPEP7omKJ7lhaPKYx+dKyvX2jDVDMl29xg7eU62M2Bmfqc4oI5ibeRDWAiKhBxKsscLP7RBHUNMHPLOu1HcnnRTusdmXJmvyK6HkPYFFdO8DUvP9DNTCMC/6MmPkFf6MM1Zg6Mr8tETutDzTlKQ5auK9zl7fv7iTxRa1QD4N5sH1KsrvO4ggtsQBP6TrYCA2cqihad6+/QynUI3CMNzQcUCgYEA8f05rdJMpVAJnedp7MH7ogr3VLxUfEu3E2yT7Ybh29Sj0V5GHrD8STAlKwLBDNFFojf+zeCq6zKlznyE3NpeSZUqIFANl5UtBcF+mUWQyXrs7oCviO1S2tu2bwbddp+C+22CYU8OF5DuATfsmv/ui+VxSUKzT4qD0IsxNhcS+WcCgYEA8P1+yMy9xlglW0K+xDL7dUE84EbXakadc22RNWQx7d+XUrIKpbw6tgsiAiS0ePneXFpTBgv4OZplarwsTGliVAjFYcM/FrxhK73PaGqiVAjoqZtMuNTefxi1qRTLQmVwh6vSkzNihS1ChzC+oCsdWqz5ZqA4oLMzk6apXDcl9ZMCgYAQ4qeMrnj0rBIPt58Xiy2gz/0UJ5QJnErpCxGDaH8IFN1ddaOU7qqb/MULLEUGPPAL8rZP7VZf1Qfm0Z9/vakIn2TyHkPkiN88YJUR4t3IgVBZEBeviUfXx68CLktzxIuiObD4U0jbchx5b0qOQj+F+XufLg1PBo5OFfhYGuITDwKBgDvA/0DW029sx8Z7JEYNxh/qzydlKWCmpb/LOSgd8etjd2f/0XgK1hvxYrtZUo50llgb5V4odaIC0IbIpctEjib8DcUR3oDKsOVhqR3g4uMnWllDsqBZ19l3zQNhroyGDoL1bb8mDJWtBUP0KDAawKqNHUH/FEt0Y6OGvZOp8PWVAoGBALETG1EUDSGmffKUuS5iiVjsspZLu80rc8Q5bYMOwyVa0Sd2yOZH4GmekWjADKNyuvtL0S9b2Ju4zwUwqFZ+AXqa0DX4xZo+ErW5zjOieZoG+CQTpm2z9zbfBpC0668/cl969F1cp+2Y1R3re0BnyEYMAvthliZowxR5EZYAPyQ7";
        $begin_private_key = "-----BEGIN PRIVATE KEY-----\r\n";
        $end_private_key = "-----END PRIVATE KEY-----\r\n";
        $openssl_private_key =$begin_private_key.$devPrivate.$end_private_key;
        openssl_sign($content, $signature, $openssl_private_key, 'sha256WithRSAEncryption');
        $sign= base64_encode($signature);
        return $sign;
    }

    public function hw_verfiy($params){
        $result = array('result' => 3);
        if($params['result']!=0){
            die(json_encode($result));
        }else{
            $sp_order_id = $params['requestId'];
            $ch_order_id = $params['orderId'];
            $ch_pay_time = $params['notifyTime'];
            $super_order = $this->DAO->get_super_order($sp_order_id);
            if(empty($ch_order_id) || empty($super_order)) {
                $result['result'] = 98;
                die(json_encode($result));
            }
            if($super_order['status'] != '2') {
                $this->DAO->update_super_order_info($sp_order_id, time(), $ch_order_id);
            }
            $result['result'] = 0;
            die(json_encode($result));
        }
    }
}
?>
