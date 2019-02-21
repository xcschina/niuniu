<?php
COMMON('baseCore');
class super_api_web extends baseCore{

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
        $this->DAO = new super_api_dao();
        $this->qa_user_id = array('71');
    }
    public function oppo_token_valify($data){
        $oppo_url = 'https://iopen.game.oppomobile.com/sdkopen/user/fileIdInfo';
        $result = array('result'=>'0','desc'=>'网络请求异常!');
        $ch_info = $this->DAO->get_ch_info($data['super_id'],$data['ch_code']);
        if(empty($ch_info)){
            $result['desc']='缺少渠道信息';
            die(json_encode($result));
        }
        $ssoid = $data['user_id'];
        $token = str_replace(" ","+",$data['token']);
        $request_serverUrl   = $oppo_url."?fileId=".$ssoid."&token=".urlencode($token);
        $time = microtime(true);
        $dataParams['oauthConsumerKey'] 	= $ch_info['app_key'];
//        $dataParams['oauthConsumerKey'] 	= $this->conf['appvKey'];
        $dataParams['oauthToken'] 			= urlencode($token);
        $dataParams['oauthSignatureMethod'] = "HMAC-SHA1";
        $dataParams['oauthTimestamp'] 		= intval($time*1000);
        $dataParams['oauthNonce'] 			= intval($time) + rand(0,9);
        $dataParams['oauthVersion'] 		= "1.0";
        $requestString 						= $this->_assemblyParameters($dataParams);
        $oauthSignature = $ch_info['param1']."&";
//        $oauthSignature = $this->conf['appSecret']."&";
        $sign 			= $this->get_sha1_sign($oauthSignature,$requestString);
        $request_info 		= $this->OauthPostExecuteNew($sign,$requestString,$request_serverUrl);
        $request_info 		= 	json_decode($request_info);
        if($request_info->resultCode == 200){
            $result['result']='1';
            $result['desc']='验证成功';
            die(json_encode($result));
        }else{
            $result['desc']=$request_info->resultCode.'_'.$request_info->resultMsg;
            die(json_encode($result));
        }
        die(json_encode($result));
    }
    /**
     * 请求的参数串组合
     */
    private function _assemblyParameters($dataParams){
        $requestString 				= "";
        foreach($dataParams as $key=>$value){
            $requestString = $requestString . $key . "=" . $value . "&";
        }
        return $requestString;
    }

    private function get_sha1_sign($oauthSignature,$requestString){
        return urlencode(base64_encode(hash_hmac( 'sha1', $requestString,$oauthSignature,true)));
    }


    /**
     * Oauth身份认证请求
     * @param string $Authorization 请求头值
     * @param string $serverUrl     请求url
     */

    public function OauthPostExecuteNew($sign,$requestString,$request_serverUrl){
        $opt = array(
            "http"=>array(
                "method"=>"GET",
                'header'=>array("param:".$requestString, "oauthsignature:".$sign),
            )
        );
        $res = file_get_contents($request_serverUrl, null, stream_context_create($opt));
        return $res;
    }
    public function xiaomi_token_valify($data){
        unset($_SESSION['channel_key']);
        $xiaomi_url = 'http://mis.migc.xiaomi.com/api/biz/service/verifySession.do';
        $result = array('result'=>'0','desc'=>'网络请求异常!');
        $ch_info = $this->DAO->get_ch_info($data['super_id'],$data['ch_code']);
        if(empty($ch_info)){
            $result['desc']='缺少渠道信息';
            die(json_encode($result));
        }
        $sign_str = "appId=".urlencode($ch_info['app_id'])."&session=".urlencode($data['session'])."&uid=".urlencode($data['uid']);
        $sign = hash_hmac("sha1", $sign_str, $ch_info['param1']);
        $xiaomi_url = $xiaomi_url."?".$sign_str.'&signature='.$sign;
        $request_info = $this->request($xiaomi_url);
        $request_info = json_decode($request_info,true);
        if($request_info['errcode'] == 200){
            $result['result']='1';
            $result['desc']='验证成功';
            die(json_encode($result));
        }else{
            $result['desc']= '错误代码：'.$request_info['errcode'];
            die(json_encode($result));
        }
        die(json_encode($result));
    }

    public function huawei_app_token_valify($data){
        unset($_SESSION['channel_key']);
        $huawei_url = 'https://gss-cn.game.hicloud.com/gameservice/api/gbClientApi';
        $result = array('result'=>'0','desc'=>'网络请求异常!');
        $ch_info = $this->DAO->get_ch_info($data['super_id'],$data['ch_code']);
        if(empty($ch_info)){
            $result['desc']='缺少渠道信息';
            die(json_encode($result));
        }
        $data['token'] = str_replace(" ","+",$data['token']);
        $token = str_replace("[66173]","=",$data['token']);
        $post_info = explode('[NNWL]',$token);
        $sign_data = array(
            'method' =>'external.hms.gs.checkPlayerSign',
            'appId' =>$ch_info['app_id'],
            'cpId' =>$ch_info['param1'],
            'ts' =>$post_info[2],
            'playerId' =>$data['user_id'],
            'playerLevel' =>$post_info[1],
            'playerSSign' =>$post_info[0]
        );

        ksort($sign_data);
        $content = http_build_query($sign_data);
        $_SESSION['channel_key'] = $data['super_id'].'_'.$data['ch_code'].'_token_private_key.pem';
        $strSign = $this->do_rsa2($content);
        $sign_data['cpSign'] = $strSign;
        $this->err_log(var_export($sign_data,1),'huawei_requst');
        $this->err_log(var_export($huawei_url,1),'huawei_requst');
        $this->err_log(var_export($_SESSION['channel_key'],1),'huawei_requst');
        $request_info = $this->request($huawei_url,$sign_data);
        $request_info = json_decode($request_info);
        if($request_info->rtnCode == 0){
            $result['result']='1';
            $result['desc']='验证成功';
            die(json_encode($result));
        }else{
            $result['desc']=$request_info->rtnCode.'_'.$request_info->errMsg;
            die(json_encode($result));
        }
        die(json_encode($result));
    }

    public function uc_app_token_valify($data){
        $result = array('result'=>'0','desc'=>'网络请求异常!');
        $app_id = $data['app_id'];
        $game_id = $data['game_id'];
        $channel = $data['channel'];
        $sid = $data['sid'];
        $timestamp = $data['timestamp'];
        $sign = $data['sign'];
        $super_info = $this->DAO->get_suepr_info($app_id);
        if(empty($super_info)){
            $result['desc']='游戏信息异常';
            die('0'.base64_encode(json_encode($result)));
        }
        $ch_info = $this->DAO->get_ch_info($app_id,$channel);
        if(empty($ch_info)){
            $result['desc']='缺少渠道信息';
            die('0'.base64_encode(json_encode($result)));
        }
        $sign_str = $app_id.$sid.$timestamp.$super_info['app_key'];
        if($sign != md5($sign_str)){
            $result['desc']='验证未通过';
            die('0'.base64_encode(json_encode($result)));
        }
        $url = 'http://sdk.9game.cn/cp/account.verifySession';
        $post_data = array(
            'id'=>$timestamp,
            'data'=> array(
                'sid'=>$sid
            ),
            'game'=>array(
                'gameId'=>$game_id)
        );
        $str = 'sid='.$sid.$ch_info['app_key'];
        $post_data['sign']=strtolower(md5($str));
        $request = $this->request($url,json_encode($post_data));

        $request = json_decode($request);
        if($request->state && $request->state->code !='1'){
            $result['desc']=$request->state->msg."【".$request->state->code."】";
            die('0'.base64_encode(json_encode($result)));
        }else{
            $result['result'] = 1;
            $result['guid'] = $request->data->accountId;
            $result['username'] = $request->data->nickName;
            $result['timestamp'] = time();
            $result['sign'] = md5($app_id.$result['guid'].$result['timestamp'].$super_info['app_key']);
            $result['desc']= '验证成功';
        }
        die('0'.base64_encode(json_encode($result)));
    }

    public function uc_order_verify($data){
        $result = array('result'=>'0','desc'=>'网络请求异常!');
        $app_id = $data['app_id'];
        $game_id = $data['game_id'];
        $channel = $data['channel'];
        $callbackInfo  = $data['callbackInfo'];
        $amount = $data['amount'];
        $cpOrderId = $data['cpOrderId'];
        $accountId = $data['accountId'];
        $timestamp = $data['timestamp'];
        $sign = $data['sign'];
        $super_info = $this->DAO->get_suepr_info($app_id);
        if(empty($super_info)){
            $result['desc']='游戏信息异常';
            die('0'.base64_encode(json_encode($result)));
        }
        $ch_info = $this->DAO->get_ch_info($app_id,$channel);
        if(empty($ch_info)){
            $result['desc']='缺少渠道信息';
            die('0'.base64_encode(json_encode($result)));
        }
        $sign_str = $app_id.$cpOrderId.$timestamp.$super_info['app_key'];
        if($sign != md5($sign_str)){
            $result['desc']='验证未通过';
            die('0'.base64_encode(json_encode($result)));
        }
        $url = 'http://sdk.9game.cn/cp/account.verifySession';
        $post_data = array(
            'callbackInfo' => $callbackInfo,
            'amount' => $amount,
            'notifyUrl' => 'http://callback.66173.cn/uc.php?id='.$ch_info['id'],
            'cpOrderId' => $cpOrderId,
            'accountId' => $accountId,
        );
        ksort($post_data);
        $arg = '';
        foreach($post_data as $key=>$item){
            $arg = $arg.$key.'='.$item;
        }
//        if(empty($post_data['notifyUrl'])){
//            $arg = substr($arg,0,strlen($arg)-1);
//        }
        $arg = $arg.$ch_info['app_key'];
//        var_dump($arg);
        $result['result'] = 1;
        $result['sign'] = strtolower(md5($arg));
        $result['notifyUrl'] = $post_data['notifyUrl'];
        $result['desc']= '订单加密成功';
        die('0'.base64_encode(json_encode($result)));
    }

    public function get_huawei_order_sign($data){
        unset($_SESSION['channel_key']);
        $result = array('result'=>'0','desc'=>'网络请求异常!');
        foreach ($data as $name=>$val){
            if(empty($val)){
                unset($data[$name]);
                break;
            }
        }
        $ch_info = $this->DAO->get_ch_info($data['app_id'],$data['channel']);
        if(empty($ch_info)){
            $result['desc']='渠道信息异常';
            $this->result_msg($result);
        }
        $post_data = array(
            'productName' => $data['productName'],
            'productDesc' => $data['productDesc'],
            'amount' => $data['amount'],
            'requestId' => $data['requestId'],
            'country' => $data['country'],
            'currency' => $data['currency'],
            'sdkChannel' => $data['sdkChannel'],
            'merchantId' => $ch_info['param1'],
            'applicationID' => $ch_info['app_id']
        );
//        var_dump($post_data);
        $_SESSION['channel_key'] = $data['app_id'].'_'.$data['channel'].'_private_key.pem';
        ksort($post_data);
        $arg = '';
        foreach($post_data as $key=>$item){
            if(!empty($item)){
                $arg = $arg.$key.'='.$item.'&';
            }
        }
        $arg = substr($arg,0,strlen($arg)-1);
//        $content = http_build_query($post_data);
//        var_dump($content);
        $strSign = $this->do_rsa2($arg);
        if(empty($strSign)){
            $result['desc'] = '加密失败';
            $this->result_msg($result);
        }else{
            $result['result'] = '1';
            $result['sign'] = $strSign;
            $result['desc'] = '请求成功';
            $this->result_msg($result);
        }
    }

    public function do_rsa2($content){
        $filename = PREFIX.'/common/super/keys/'.$_SESSION['channel_key'];
        if (!file_exists($filename)) {
            return '';
        }
        $priKey = @file_get_contents($filename);
        $openssl_private_key = @openssl_get_privatekey($priKey);
        @openssl_sign($content, $signature, $openssl_private_key, 'sha256WithRSAEncryption');
        $sign=@base64_encode($signature);
        return $sign;
    }

    public function make_sign($data){
        ksort($data);
        $arg = '';
        foreach($data as $key=>$item){
            if(!empty($item)){
                $arg = $arg.$key.'='.$item.'&';
            }
        }
        $arg = substr($arg,0,strlen($arg)-1);
        if(get_magic_quotes_gpc()) $arg = stripslashes($arg);

        return $this->rsaSign($arg,'RSA');
    }

    public function rsaSign($arg,$type){
        if(!in_array($type,['RSA','RSA2'])) {
            return false;
        }
        try{
            $priKey = file_get_contents(PREFIX.'/common/super/keys/'.$_SESSION['channel_key']);

            //转换为openssl密钥，必须是没有经过pkcs8转换的私钥
            $res = openssl_get_privatekey($priKey);
            if($type == 'RSA') {
                openssl_sign($arg, $sign, $res);
            }else{
                openssl_sign($arg, $sign, $res,OPENSSL_ALGO_SHA256);
            }

            openssl_free_key($res);
            //base64编码
            $sign = base64_encode($sign);
            return $sign;
        }catch (\Exception $e) {
            return false;
        }
    }

    public function tx_order_record($data){
        foreach ($data as $name=>$val){
            if(empty($val)){
                $this->err_log(var_export($data,1),'tx_order_error');
                break;
            }
        }
        $app_info = $this->DAO->get_ch_by_appid($data['appid'],$data['channel']);
        $sign_str = $data['appid'].$data['openid'].$data['payToken'].$data['niuorderid'].$data['timestamp'].$app_info['app_key'];

        if($data['sign'] == md5($sign_str)){
            $this->err_log(var_export($data,1),'tx_order_success');
            $order = $this->DAO->get_tx_order($data['niuorderid']);
            if(!$order){
                $this->DAO->add_tx_order($data);
            }
        }else{
            $data['sign_str'] = $sign_str;
            $this->err_log(var_export($data,1),'tx_order_sign_error');
            die("0".base64_encode(json_encode(array('result'=>'0'))));
        }
        die("0".base64_encode(json_encode(array('result'=>'1'))));
    }

    public function tx_user_record($data){
        foreach ($data as $name=>$val){
            if(empty($val)){
                $this->err_log(var_export($data,1),'tx_user_error');
                break;
            }
        }
        $app_info = $this->DAO->get_ch_by_appid($data['appid'],$data['channel']);
        $sign_str = $data['appid'].$data['openid'].$data['payToken'].$data['pf_key'].$data['timestamp'].$app_info['app_key'];

        if($data['sign'] == md5($sign_str)){
            $this->err_log(var_export($data,1),'tx_user_success');
            $order = $this->DAO->get_tx_user($data['openid']);
            if(!$order){
                $this->DAO->add_tx_user($data);
            }else{
                $this->DAO->update_tx_user($data);
                $this->update_overtime_orders($data);
            }
        }else{
            $data['sign_str'] = $sign_str;
            $this->err_log(var_export($data,1),'tx_user_sign_error');
            die("0".base64_encode(json_encode(array('result'=>'3'))));
            //验证失败
        }
        die("0".base64_encode(json_encode(array('result'=>'1'))));
    }

    protected function update_overtime_orders($data){
        if(!empty($data['openid'])){
            $time = strtotime(date("Y-m-d",strtotime("-8 day")));
            $this->err_log(var_export($data,1),'overtime_orders');
            $order = $this->DAO->get_overtime_orders($data['openid'],$time);
            if(!empty($order['num'])){
                $this->err_log(var_export($time,1),'overtime_orders');
                $this->DAO->update_overtime_orders($data['openid'],$time);
            }
        }

    }

    public function auto_verify(){
        $order = $this->DAO->get_tx_order_list();
        if(!$order){
            die("操作成功!");
        }
        foreach($order as $item){
            //时间判断间隔15s
            $now_time = time();
            if ( 0 != $item['times'] && 15>$now_time-(int)$item['modify_time']){
                continue;
            }
            $user_info = $this->DAO->get_tx_user($item['openid']);
            if (empty($user_info)){
                continue;
            }
            $tx_data = array();
            $ch_info = $this->DAO->get_ch_info($item['appid'],$item['channel']);
            $tx_data['openid'] = $item['openid'];
            $tx_data['openkey'] = $user_info['pay_token'];
            $tx_data['appid'] = $ch_info['app_id'];
            $tx_data['ts'] = $now_time;
            $tx_data['pf'] = $user_info['pf'];
            $tx_data['pfkey'] = $user_info['pf_key'];
            $tx_data['zoneid'] = '1';
            ksort($tx_data);
            $sig = http_build_query($tx_data);
            $one = "GET&".urlencode('/v3/r/mpay/get_balance_m')."&".urlencode($sig);
            if(in_array($item['appid'],array('9032'))){
                $two = $ch_info['param2']."&";
            }else{
                $two = $ch_info['app_key']."&";
            }
            $sign = $this->getSignature($one,$two);
            $sign = urlencode($sign);
            if(in_array($item['appid'],array('9032'))){
                $url = 'https://ysdktest.qq.com/mpay/get_balance_m';
            }else{
                $url = 'https://ysdk.qq.com/mpay/get_balance_m';
            }
            $cookie_data = array(
                'org_loc'=>urlencode(' /mpay/get_balance_m'),
                'appip'=>urlencode($this->client_ip())
            );
            if($item['platform'] == '1'){
                $cookie_data['session_id']=urlencode('openid');
                $cookie_data['session_type']=urlencode('kp_actoken');
            }elseif($item['platform'] == '2'){
                $cookie_data['session_id']=urlencode('hy_gameid');
                $cookie_data['session_type']=urlencode('wc_actoken');
            }else{
                $cookie_data['session_id']=urlencode('hy_gameid');
                $cookie_data['session_type']=urlencode('st_dummy');
            }
            $cookie_str = "session_id=".$cookie_data['session_id'].";session_type=".$cookie_data['session_type'].";org_loc=".$cookie_data['org_loc'].";appip=".$cookie_data['appip'];
            $url = $url.'?'.$sig."&sig=".$sign;
//            var_dump($cookie_str);
//            var_dump($url);
            $res = json_decode($this->request($url,'',array(),10,$cookie_str),true);
            $this->err_log(var_export($sig,1),'tx_verify');
            $this->err_log(var_export($one,1),'tx_verify');
            $this->err_log(var_export($two,1),'tx_verify');
            $this->err_log(var_export($url,1),'tx_verify');
            $this->err_log(var_export($cookie_str,1),'tx_verify');
            $this->err_log(var_export($res,1),'tx_verify');
            $this->err_log(var_export($item,1),'tx_verify');
            if (0 === $res['ret']){
                //判断余额是否增加
                $order_info = $this->DAO->get_order_info($item['niuorderid']);
                if ($res['save_amt']>(float)$user_info['money'] && $res['save_amt']>=((float)$user_info['money']+(float)$order_info['pay_money'])){
                    //判断成功
                    $item['status'] = 2;
                    //更改用户金额
                    $new_money = (float)$user_info['money']+(float)$order_info['pay_money'];
                    $this->DAO->update_user_money($new_money,$item['openid']);
                    //更改订单状态
                    $this->DAO->update_order_status($item['niuorderid']);
                }else{
                    $this->err_log(var_export($res['save_amt']>(float)$user_info['money'] && $res['save_amt'],1),'tx_verify');
                    $this->err_log(var_export(((float)$user_info['money']+(float)$order_info['pay_money']),1),'tx_verify');
                    if($res['save_amt']>(float)$user_info['money'] && $res['save_amt']){
                        $item['status'] = 3;
                        $item['remark'] = '订单金额异常,用户金额:'.(float)$user_info['money'].'腾讯后台金额:'.$res['save_amt']."总额:".((float)$user_info['money']+(float)$order_info['pay_money']);
                    }else{
                        $item['status'] = 3;
                        $item['remark'] = '总金额已超出,用户金额:'.(float)$user_info['money'].'腾讯后台金额:'.$res['save_amt']."总额:".((float)$user_info['money']+(float)$order_info['pay_money']);
                    }
                }
            }
            $item['times']++;
            if ($item['times']>10 && $item['status'] == 1){
                //写入状态不再执行
                $item['status'] = 3;
                $item['remark'] = $res['msg'];
            }
            $this->DAO->update_order_list(array(
                "status"=>$item['status'],
                "times"=>$item['times'],
                "modify_time"=>$now_time,
                "niuorderid"=>$item['niuorderid'],
                "remark"=>$item['remark']
            ));
        }

    }

    public function creater_vivo_order($data){
        $channel_info = $this->DAO->get_ch_info($data['app_id'],$data['channel']);
        if(empty($channel_info)){
            die("0".base64_encode(json_encode(array('result'=>'2','desc'=>'渠道未配置'))));
        }
        $post_data = array(
            'version' => '1.0.0',
            'cpId' => $channel_info['param1'],
            'appId' => $channel_info['app_id'],
            'cpOrderNumber' => $data['cpOrderNumber'],
            'notifyUrl' => 'http://callback.66173.cn/vivo.php?id='.$channel_info['id'],
            'orderTime' => date("YmdHms"),
            'orderAmount' => $data['orderAmount'],
            'orderTitle' => $data['orderTitle'],
            'orderDesc' => $data['orderDesc'],
            'extInfo' => $data['extInfo']
        );
        ksort($post_data);
        $sign_data = "";
        foreach($post_data as $key => $param){
            if($param !=''){
                $sign_data = $sign_data."&".$key."=".$param;
            }
        }
        $sign_data = substr($sign_data, 1);
        $sign = strtolower(md5($sign_data.'&'.strtolower(md5($channel_info['app_key']))));
        $post_data['signMethod'] = 'MD5';
        $post_data['signature'] = $sign;
        $url = 'https://pay.vivo.com.cn/vcoin/trade';
        $res = $this->request($url,$post_data,'','','');
        $res = json_decode($res);
        if($res->respCode == 200){
            $this->err_log($this->client_ip()."\r".var_export($post_data,1),'vivo_success_log');
            $this->err_log($this->client_ip()."\r".var_export($res,1),'vivo_success_log');
            $result = array(
              'result' => 1,
              'sign' => $res->accessKey,
              'orderno' => $res->orderNumber,
              'desc' => '请求成功'
            );
            die("0".base64_encode(json_encode($result)));
        }else{
            die("0".base64_encode(json_encode(array('result'=>'2','desc'=>'异常码'.$res->respCode))));
        }
    }

    public function meizu_order_sign($params){
        $result = array('result'=>'0','desc'=>'网络请求异常!');
        $super_info = $this->DAO->get_suepr_info($params['app_id']);
        if(empty($super_info)){
            $result['desc']='游戏信息异常';
            die('0'.base64_encode(json_encode($result)));
        }
        $ch_info = $this->DAO->get_ch_info($params['app_id'],$params['channel']);
        if(empty($ch_info)){
            $result['desc']='缺少渠道信息';
            die('0'.base64_encode(json_encode($result)));
        }
        $data = array(
            'app_id'=>$params['game_id'],
            'cp_order_id'=>$params['cp_order_id'],
            'uid'=>$params['uid'],
            'product_id'=>$params['product_id'],
            'product_subject'=>$params['product_subject'],
            'product_body'=>$params['product_body'],
            'product_unit'=>$params['product_unit'],
            'buy_amount'=>$params['buy_amount'],
            'product_per_price'=>$params['product_per_price'],
            'total_price'=>$params['total_price'],
            'create_time'=>$params['create_time'],
            'pay_type'=>$params['pay_type'],
            'user_info'=>$params['user_info'],
        );
        ksort($data);
        $data_str = '';
        foreach($data as $key => $param){
            if($param !='null'){
                $data_str = $data_str."&".$key."=".$param;
            }
        }
        $data_str = substr($data_str, 1);
        $sign_str = $data_str.':'.$ch_info['param1'];
        $result['result']= 1;
//        $result['sign_str']= $sign_str;
        $result['sign']= md5($sign_str);
        $result['desc']= '订单加密成功';
        die('0'.base64_encode(json_encode($result)));
    }

    public function sangxing_login_sign($params){
        $result = array('result'=>'0','desc'=>'网络请求异常!');
        $super_info = $this->DAO->get_suepr_info($params['app_id']);
        if(empty($super_info)){
            $result['desc']='游戏信息异常';
            die('0'.base64_encode(json_encode($result)));
        }
        $ch_info = $this->DAO->get_ch_info($params['app_id'],$params['channel']);
        if(empty($ch_info)){
            $result['desc']='缺少渠道信息';
            die('0'.base64_encode(json_encode($result)));
        }
        $sign_str = $params['app_id'].$params['package_name'].$params['timestamp'].$super_info['app_key'];
        if($params['sign']!=md5($sign_str)){
            $result['desc']='签名错误';
            die('0'.base64_encode(json_encode($result)));
        }
        $priKey = file_get_contents(PREFIX.'/common/super/keys/'.$params['app_id'].'_'.$params['channel'].'_private_key.pem');
        $data = $params['game_id'].'&'.$params['package_name'];
        $sign = $this->sangxing_sign($data,$priKey);
        if(empty($sign)){
            $result['desc']='验证失败';
            die('0'.base64_encode(json_encode($result)));
        }
        $result['result'] = 1;
        $result['sign'] = $sign;
        $result['desc']='加密成功';
        die('0'.base64_encode(json_encode($result)));
    }

    public function sangxing_order_sign($params){
        $result = array('result'=>'0','desc'=>'网络请求异常!');
        $super_info = $this->DAO->get_suepr_info($params['app_id']);
        if(empty($super_info)){
            $result['desc']='游戏信息异常';
            die('0'.base64_encode(json_encode($result)));
        }
        $ch_info = $this->DAO->get_ch_info($params['app_id'],$params['channel']);
        if(empty($ch_info)){
            $result['desc']='缺少渠道信息';
            die('0'.base64_encode(json_encode($result)));
        }
        $sign_str = $params['app_id'].$params['cporderid'].$params['timestamp'].$super_info['app_key'];
        if($params['sign'] != md5($sign_str)){
            $result['desc']='签名错误';
            die('0'.base64_encode(json_encode($result)));
        }

        $url = 'http://siapcn1.ipengtai.com:7002/payapi/order';
        $ch_goodcode = $this->DAO->get_ch_goodcode($params['app_id'],$params['goodcode'],$params['channel']);
        if(empty($ch_goodcode['channel_goods'])){
            $result['desc']='渠道商品未配置';
            die('0'.base64_encode(json_encode($result)));
        }
        $post_data = array(
            'appid'=>$params['game_id'],
            'waresid'=>(int)$ch_goodcode['channel_goods'],
            'waresname'=>$ch_goodcode['good_name'],
            'cporderid'=>$params['cporderid'],
            'price'=>(float)$params['price'],
            'currency'=>'RMB',
            'appuserid'=>$params['appuserid']
        );
        ksort($post_data);
        $content = json_encode($post_data);
        $priKey = file_get_contents(PREFIX.'/common/super/keys/'.$params['app_id'].'_'.$params['channel'].'_private_key.pem');
        $sign = $this->sangxing_sign($content,$priKey);
        $reqData = "transdata=".urlencode($content)."&sign=".urlencode($sign)."&signtype=RSA";
        $request = $this->request($url,$reqData);
        $request = explode("&",$request);
        $request = explode("=",$request[0]);
        $request = json_decode(urldecode($request[1]),true);

        if($request['transid']){
            $timestamp = (string)time();
            $request_str = $params['app_id'].$request['transid'].$timestamp.$super_info['app_key'];
            $result['result'] = 1;
            $result['orderno'] = $request['transid'];
            $result['timestamp'] = $timestamp;
            $result['sign'] = md5($request_str);
            $result['desc'] = '加密成功';
            die('0'.base64_encode(json_encode($result)));
        }else{
            $result['desc'] = $request['code']."_".$request['errmsg'];
            die('0'.base64_encode(json_encode($result)));
        }

    }

    public function sangxing_token_verify($params){
        $result = array('result'=>'0','desc'=>'网络请求异常!');
        $super_info = $this->DAO->get_suepr_info($params['app_id']);
        if(empty($super_info)){
            $result['desc']='游戏信息异常';
            die('0'.base64_encode(json_encode($result)));
        }
        $ch_info = $this->DAO->get_ch_info($params['app_id'],$params['channel']);
        if(empty($ch_info)){
            $result['desc']='缺少渠道信息';
            die('0'.base64_encode(json_encode($result)));
        }
        $sign_str = $params['app_id'].$params['signValue'].$params['timestamp'].$super_info['app_key'];
        if($params['sign']!=md5($sign_str)){
            $result['desc']='签名错误';
            die('0'.base64_encode(json_encode($result)));
        }

        $priKey = file_get_contents(PREFIX.'/common/super/keys/'.$params['app_id'].'_'.$params['channel'].'_private_key.pem');
        $data = 'appid='.$params['game_id'].'&token='.$params['signValue'];
        $sign = $this->sangxing_sign($data,$priKey);
        if(empty($sign)){
            $result['desc']='验证失败';
            die('0'.base64_encode(json_encode($result)));
        }

        $url = 'https://siapcn1.ipengtai.com/api/oauth/get_token_info';
        $post_data = array(
            'appid'=>$params['game_id'],
            'token'=>$params['signValue'],
            'sign'=>$sign
        );
        $request = $this->request($url,$post_data);
        $request = json_decode($request,true);
        if($request['Code'] == 0){
            //加密 (md5(app_id+guid+timestamp+appkey)))
            $request_data = json_decode($request['data'],true);
            $time = (string)time();
            $request_str = $params['app_id'].(string)$request_data['uid'].$time.$super_info['app_key'];
            $result['result'] = 1;
            $result['guid'] = (string)$request_data['uid'];
            $result['username'] = $request_data['nickname'];
            $result['timestamp'] = $time;
            $result['sign'] = md5($request_str);
            $result['desc']='验证成功';
        }else{
            $result['desc']= '错误码为：'.$request['Msg'];
            die('0'.base64_encode(json_encode($result)));
        }
        die('0'.base64_encode(json_encode($result)));
    }

    public function qihu_token_verify($params){
        $result = array('result'=>'0','desc'=>'网络请求异常!');
        $super_info = $this->DAO->get_suepr_info($params['app_id']);
        if(empty($super_info)){
            $result['desc']='游戏信息异常';
            die('0'.base64_encode(json_encode($result)));
        }
        $ch_info = $this->DAO->get_ch_info($params['app_id'],$params['channel']);
        if(empty($ch_info)){
            $result['desc']='缺少渠道信息';
            die('0'.base64_encode(json_encode($result)));
        }
        $sign_str = $params['app_id'].$params['sid'].$params['timestamp'].$super_info['app_key'];
        if($params['sign']!= md5($sign_str)){
            $result['desc']='签名错误';
            die('0'.base64_encode(json_encode($result)));
        }
        $url = 'https://openapi.360.cn/user/me.json';
        $url = $url.'?access_token='.$params['sid'];
        $request = $this->request($url);
        $request = json_decode($request,true);
        if($request['id']){
            $time = (string)time();
            $request_str = $params['app_id'].(string)$request['id'].$time.$super_info['app_key'];
            $result['result'] = 1;
            $result['guid'] = (string)$request['id'];
            $result['username'] = $request['name'];
            $result['timestamp'] = $time;
            $result['sign'] = md5($request_str);
            $result['desc']='验证成功';
        }else{
            $result['desc']= '验证失败！';
            die('0'.base64_encode(json_encode($result)));
        }
        die('0'.base64_encode(json_encode($result)));
    }

    public function qihu_order_sign($params){
        $result = array('result'=>'0','desc'=>'网络请求异常!');
        $super_info = $this->DAO->get_suepr_info($params['app_id']);
        if(empty($super_info)){
            $result['desc']='游戏信息异常';
            die('0'.base64_encode(json_encode($result)));
        }
        $ch_info = $this->DAO->get_ch_info($params['app_id'],$params['channel']);
        if(empty($ch_info)){
            $result['desc']='缺少渠道信息';
            die('0'.base64_encode(json_encode($result)));
        }
        $sign_str = $params['app_id'].$params['product_id'].$params['user_id'].$params['app_order_id'].$params['timestamp'].$super_info['app_key'];
        if($params['sign'] != md5($sign_str)){
            $result['desc']='签名错误';
            die('0'.base64_encode(json_encode($result)));
        }
        $money_info = $this->DAO->get_ch_goods_info($params['app_id'],$params['product_id']);
        if(empty($money_info)){
            $result['desc']='商品信息异常。';
            die('0'.base64_encode(json_encode($result)));
        }
        $url = 'https://mgame.360.cn/srvorder/get_token.json';
        $qihu_data = array(
            'app_key' => $ch_info['app_key'],
            'product_id' => $params['product_id'],
            'product_name' => $money_info['good_name'],
            'amount' => $money_info['good_price']*100,
            'app_uid' => $params['app_uid'],
            'app_uname' => $params['app_uid'],
            'app_ext1' => $params['app_ext1'],
            'app_ext2' => $params['app_ext2'],
            'user_id' => $params['user_id'],
            'sign_type' => $params['sign_type'],
            'app_order_id' => $params['app_order_id']
        );

        foreach($qihu_data as $k=>$v){
            if(empty($v)){
                unset($qihu_data[$k]);
            }

        }
        ksort($qihu_data);
        $sign_str = implode('#',$qihu_data);
        $sign_str = $sign_str.'#'.$ch_info['param1'];
        $qihu_data['sign'] = md5($sign_str);
//        $qihu_data['product_name'] = urlencode($qihu_data['product_name']);
        $request = $this->request($url,$qihu_data);
        $request = json_decode($request,true);
        if($request['token_id']){
            $timestamp = (string)time();
            $result['result'] = 1;
            $result['notifyUrl'] = 'http://callback.93kk.com/qihu.php?id='.$ch_info['id'];
            $request_str = $params['app_id'].$request['order_token'].$request['token_id'].$result['notifyUrl'].$timestamp.$super_info['app_key'];
            $result['order_token'] = $request['order_token'];
            $result['token_id'] = $request['token_id'];
            $result['timestamp'] = $timestamp;
            $result['sign'] = md5($request_str);
            $result['desc'] = '加密成功';
            die('0'.base64_encode(json_encode($result)));
        }else{
            $result['result'] = 1;
            $result['notifyUrl'] = 'http://callback.93kk.com/qihu.php?id='.$ch_info['id'];
            $request_str = $params['app_id'].$result['notifyUrl'].$timestamp.$super_info['app_key'];
            $result['sign'] = md5($request_str);
            $result['desc'] = '获取成功';
            die('0'.base64_encode(json_encode($result)));
        }

    }


    public function request($url, $post='', $header = array(), $timeout = 10,$cookie){
        if($this->is_request_debug){
            $starttime = microtime(true);
        }
        try{
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10); //设置连接时间
            curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION,1);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
            curl_setopt($ch, CURLOPT_ENCODING, 'UTF-8');

            if($post){
                curl_setopt($ch, CURLOPT_POST, 1);
                if(is_array($post)){
                    $post = http_build_query($post,'&');
                }
                curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
            }

            if($header){
                curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
            }
            if($cookie){
                curl_setopt($ch, CURLOPT_COOKIE, $cookie);
            }
            if(curl_errno($ch)) {
                $this->err_log($url,'request');
            }

            $result = curl_exec($ch);
            $this->curl_status = curl_getinfo($ch,CURLINFO_HTTP_CODE);

            if($this->is_request_debug){
                $curl_info = curl_getinfo($ch);
                $endtime = microtime(true);
                $dur = $endtime - $starttime;
                file_put_contents(PREFIX.DS.'logs/'.date('Ymd').'_url.log','IP：'.$this->client_ip().'时间:'.date('Y-m-d H:i:s', intval($starttime)).'|用时:'.$dur.
                    "\r\n结果:".$result.'|curl_info:'.json_encode($curl_info)."\r\n----------\r\n",FILE_APPEND);
            }
            curl_close($ch);
            return trim($result);
        }catch (Exception $e){
            print_r($e);
        }
    }

    public function getSignature($str, $key) {
        $signature = "";
        if (function_exists('hash_hmac')) {
            $signature = base64_encode(hash_hmac("sha1", $str, $key, true));
        } else {
            $blocksize = 64;
            $hashfunc = 'sha1';
            if (strlen($key) > $blocksize) {
                $key = pack('H*', $hashfunc($key));
            }
            $key = str_pad($key, $blocksize, chr(0x00));
            $ipad = str_repeat(chr(0x36), $blocksize);
            $opad = str_repeat(chr(0x5c), $blocksize);
            $hmac = pack(
                'H*', $hashfunc(
                    ($key ^ $opad) . pack(
                        'H*', $hashfunc(
                            ($key ^ $ipad) . $str
                        )
                    )
                )
            );
            $signature = base64_encode($hmac);
        }
        return $signature;
    }

    public function result_msg($result = array()){
        if($result){
            die("0".base64_encode(json_encode($result)));
        }else{
            die("0".base64_encode(json_encode(array('result'=>'999','desc'=>'异常码-404'))));
        }
    }

    function sangxing_sign($data, $priKey) {
        //转换为openssl密钥
        $res = openssl_get_privatekey($priKey);

        //调用openssl内置签名方法，生成签名$sign
        openssl_sign($data, $sign, $res, OPENSSL_ALGO_MD5);

        //释放资源
        openssl_free_key($res);

        //base64编码
        $sign = base64_encode($sign);
        return $sign;
    }
}
?>
