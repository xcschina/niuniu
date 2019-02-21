<?php
COMMON('sdkCore','alipay_secure/alipay_config','alipay_secure/alipay_function','RNCryptor/RNEncryptor');
DAO('android_pay_dao','user_dao');

class android_pay extends sdkCore{

    public $DAO;
    public $qa_user_id;

    public function __construct(){
        parent::__construct();
        $this->DAO = new android_pay_dao();
        $this->qa_user_id = array('71','164626');
    }

    public function android_pay_order(){
        $USR_HEADER = $this->get_usr_session('USR_HEADER');
        $app_id = $USR_HEADER['app_id'];
        $app_info = $this->DAO->get_app_info($app_id);
        if($USR_HEADER['md5'] !== md5($app_id.$USR_HEADER['player_id'].$USR_HEADER['serv_id'].$USR_HEADER['usr_id'].$USR_HEADER['usertoken'].$app_info['app_key'])){
            $result = array("errcode"=>1,"message"=>"参数不正确");
        }else{
            $time = strtotime("now");
            $this->cp_order_verify($app_info,$USR_HEADER);
            $money = $this->DAO->get_money_info($app_id,$USR_HEADER['money_id']);
            $money['unit_price'] = $money['good_price'];
            $SYS_order_id = $this->orderid($USR_HEADER['app_id']);
            $app_order_id = $this->get_app_order_id($app_info, $USR_HEADER, $SYS_order_id, $money);
            $money = $this->get_discount($USR_HEADER,$money);
            if(!empty($USR_HEADER['goodmultiple'])){
                $money['pay_price'] = $money['pay_price'] * $USR_HEADER['goodmultiple'];
                $money['good_price'] = $money['good_price'] * $USR_HEADER['goodmultiple'];
            }
            $order_info = $this->create_order($money, $USR_HEADER, $SYS_order_id, $app_order_id, $time);
            $money['good_price'] = $money['pay_price'];

            if(in_array($USR_HEADER['usr_id'], $this->qa_user_id)){
                $money['good_price'] = 0.01;
            }
            $result = $this->get_pay_result($order_info,$money,$USR_HEADER,$app_info,$SYS_order_id,$app_order_id,ALI_SECURE_notify_url);
            $this->err_log(var_export($result,1),'cp_order');
        }
        die("0".base64_encode(json_encode($result)));
    }

    public function get_wx_result($order_info,$money,$USR_HEADER,$app_info,$SYS_order_id,$app_order_id){
        $wx_data = $this->make_wx_data($money,$order_info['order_id'],WX_SECURE_notify_url);
        $xml_data = $this->array_to_xml($wx_data);
        $request = $this->request(WX_API_url,$xml_data);
        $request_data = json_decode(json_encode(simplexml_load_string($request, 'SimpleXMLElement', LIBXML_NOCDATA)), true);
        if($request_data['return_code']=='FAIL'){
            $result = array("errcode"=>1,"message"=>$request_data['return_msg']);
            die("0".base64_encode(json_encode($result)));
        }elseif($request_data['return_code']=='SUCCESS' && $request_data['result_code']=='SUCCESS'){
            $wx_order_id = $request_data['prepay_id'];
            $wx_nonce = $request_data['nonce_str'];
//                $this->DAO->update_wx_order();
        }elseif($request_data['return_code']=='SUCCESS' && $request_data['result_code']=='FAIL'){
            $result = array("errcode"=>1,"message"=>$request_data['err_code_des'].$request_data['err_code']);
            die("0".base64_encode(json_encode($result)));
        }else{
            $result = array("errcode"=>1,"message"=>'请求订单异常.'.$request_data);
            die("0".base64_encode(json_encode($result)));
        }
        $result = array(
            "errcode"=>0,
            "appid"=>WX_APPID,
            "partnerid"=>MCH_ID,
            "prepayid"=>$wx_order_id,
            "noncestr"=>$wx_nonce,
            "sign"=>$wx_data['sign'],
            "message"=>"预支付订单发送成功");
        $this->err_log(var_export($request_data,1),'wx_pay_order');
        return $result;
    }

    public function nnb_wx_result($order_info,$money){
        $wx_data = $this->make_wx_data($money,$order_info['order_id'],NNB_WX_notify_url);
        $xml_data = $this->array_to_xml($wx_data);
        $request = $this->request(WX_API_url,$xml_data);
        $request_data = json_decode(json_encode(simplexml_load_string($request, 'SimpleXMLElement', LIBXML_NOCDATA)), true);
        if($request_data['return_code']=='FAIL'){
            $result = array("errcode"=>1,"message"=>$request_data['return_msg']);
            die("0".base64_encode(json_encode($result)));
        }elseif($request_data['return_code']=='SUCCESS' && $request_data['result_code']=='SUCCESS'){
            $wx_order_id = $request_data['prepay_id'];
            $wx_nonce = $request_data['nonce_str'];
//                $this->DAO->update_wx_order();
        }elseif($request_data['return_code']=='SUCCESS' && $request_data['result_code']=='FAIL'){
            $result = array("errcode"=>1,"message"=>$request_data['err_code_des'].$request_data['err_code']);
            die("0".base64_encode(json_encode($result)));
        }else{
            $result = array("errcode"=>1,"message"=>'请求订单异常.'.$request_data);
            die("0".base64_encode(json_encode($result)));
        }
        $result = array(
            "errcode"=>0,
            "appid"=>WX_APPID,
            "partnerid"=>MCH_ID,
            "prepayid"=>$wx_order_id,
            "noncestr"=>$wx_nonce,
            "sign"=>$wx_data['sign'],
            "message"=>"预支付订单发送成功");
        $this->err_log(var_export($request_data,1),'nnb_wx_pay_order');
        return $result;
    }


    public function make_sdk_wx_data($money,$out_trade_no,$notify_url){
        $body = $money['app_name']."[".$money['good_name'].$money['good_unit']."]";
        $attach='`store_appid='.SDK_XY_STORE_APPID.'#store_name='.SDK_XY_STORE_NAME.'#op_user=';
        $data = array(
            'appid' => SDK_XY_APPID,//兴业APPID
            'attach' => $attach,
            'body' => $body,
            'mch_id' => SDK_XY_MCD_ID,//兴业MCH_ID
            'nonce_str' => $this->create_guids(),
            'notify_url' => $notify_url,
            'out_trade_no' => $out_trade_no,
            'spbill_create_ip' => $this->client_ip(),
            'total_fee' => $money['good_price']*100,
            'trade_type' => 'APP',
            'version' => '1.0.4',//兴业APPID
            'wx_appid' => SDK_WX_APPID,
        );
        $str = '';
        $new_data=array();
        foreach($data as $key => $val ){
            if(!empty($val)){
                $new_data[$key]=$val;
                $str.=$key."=".$val."&";
            }
        }
        $str = $str."key=".SDK_XY_APPKEY;
        $new_data['sign']=strtoupper(md5($str));
        return $new_data;
    }

    public function make_h5_wx_data($money,$out_trade_no,$notify_url){
        $body = $money['app_name']."[".$money['good_name'].$money['good_unit']."]";
        $attach='`store_appid=s20170701000003555#store_name=福建牛牛SDK#op_user=';
        $data = array(
            'appid' => SDK_XY_H5_APPID,//兴业APPID
            'mch_id' => SDK_XY_H5_MCD_ID,//兴业MCH_ID
            'nonce_str' => $this->create_guids(),
            'body' => $body,
            'attach' => $attach,
            'out_trade_no' => $out_trade_no,
            'total_fee' => $money['good_price']*100,
            'notify_url' => $notify_url,
            'trade_type' => 'MWEB',
            'spbill_create_ip' => $this->client_ip()
        );
        ksort($data);
        $str = '';
        $new_data=array();
        foreach($data as $key => $val ){
            if(!empty($val)){
                $new_data[$key]=$val;
                $str.=$key."=".$val."&";
            }
        }
        $str = $str."key=".SDK_XY_H5_APPKEY;
        $new_data['sign']=strtoupper(md5($str));
        return $new_data;
    }


    public function make_sdk_qpay_data($money,$out_trade_no,$notify_url){
        $body = $money['app_name']."[".$money['good_name'].$money['good_unit']."]";
        $data = array(
            'appid' => QPAY_APP_ID,//QQ钱包商户号
            'mch_id' => QPAY_MCH_ID,//QQ钱包商户号
            'nonce_str' => $this->create_guids(),
            'body' => $body,
            'out_trade_no' => $out_trade_no,
            'fee_type' => 'CNY',
            'total_fee' => $money['good_price']*100,
            'trade_type' => 'APP',
            'spbill_create_ip' => $this->client_ip(),
            'notify_url' => $notify_url,
        );
        ksort($data);
        $str = '';
        $new_data=array();
        foreach($data as $key => $val ){
            if(!empty($val)){
                $new_data[$key]=$val;
                $str.=$key."=".$val."&";
            }
        }
        $str = $str."key=".QPAY_APPKEY;
        $new_data['sign']=strtoupper(md5($str));
        return $new_data;
    }

    public function sdk_qpay_result($order_info,$money,$USR_HEADER,$app_info,$SYS_order_id,$app_order_id,$notify_url){
        $qpay_data = $this->make_sdk_qpay_data($money,$order_info['order_id'],$notify_url);
        $xml_data = $this->array_to_xml($qpay_data);
        $request = $this->request(QPAY_API_url,$xml_data,array('Content-type: text/xml'));
        $request_data = json_decode(json_encode(simplexml_load_string($request, 'SimpleXMLElement', LIBXML_NOCDATA)), true);
        if($request_data['return_code']=='FAIL'){
            $result = array("errcode"=>1,"message"=>$request_data['return_msg']);
            die("0".base64_encode(json_encode($result)));
        }elseif($request_data['return_code']=='SUCCESS' && $request_data['result_code']=='SUCCESS'){
            $prepay_id = $request_data['prepay_id'];
            $qpay_nonce = $request_data['nonce_str'];
        }elseif($request_data['return_code']=='SUCCESS' && $request_data['result_code']=='FAIL'){
            $result = array("errcode"=>1,"message"=>$request_data['err_code_desc'].$request_data['err_code']);
            die("0".base64_encode(json_encode($result)));
        }else{
            $result = array("errcode"=>1,"message"=>'请求订单异常.'.$request_data);
            die("0".base64_encode(json_encode($result)));
        }

        $result = array(
            "errcode"=>0,
            "prepayid"=>$prepay_id,
            "order_id"=>$qpay_data['out_trade_no'],
            "sign"=>md5($prepay_id.$app_info['app_key']),
            "message"=>"预支付订单发送成功");
        $this->err_log(var_export($request_data,1),'sdk_qq_pay_order');
        return $result;
    }


    public function sdk_wx_result($order_info,$money,$USR_HEADER,$app_info,$SYS_order_id,$app_order_id,$notify_url){
        $wx_data = $this->make_sdk_wx_data($money,$order_info['order_id'],$notify_url);
        $xml_data = $this->array_to_xml($wx_data);
        $request = $this->request(SDK_XY_API_url,$xml_data,array('Content-type: text/xml'));
        $request_data = json_decode(json_encode(simplexml_load_string($request, 'SimpleXMLElement', LIBXML_NOCDATA)), true);
        if($request_data['return_code']=='FAIL'){
            $result = array("errcode"=>1,"message"=>$request_data['return_msg']);
            die("0".base64_encode(json_encode($result)));
        }elseif($request_data['return_code']=='SUCCESS' && $request_data['result_code']=='SUCCESS' && $request_data['trade_type']=='APP'){
            $req_appid = $request_data['req_appid'];
            $req_partnerid = $request_data['req_partnerid'];
            $req_prepayid = $request_data['req_prepayid'];
            $req_package = $request_data['req_package'];
            $req_noncestr = $request_data['req_noncestr'];
            $req_timestamp = $request_data['req_timestamp'];
            $req_sign = $request_data['req_sign'];

        }elseif($request_data['return_code']=='SUCCESS' && $request_data['result_code']=='FAIL'){
            $result = array("errcode"=>1,"message"=>$request_data['err_code_desc'].$request_data['err_code']);
            die("0".base64_encode(json_encode($result)));
        }else{
            $result = array("errcode"=>1,"message"=>'请求订单异常.'.$request_data);
            die("0".base64_encode(json_encode($result)));
        }
        $result = array(
            "errcode"=>0,
            "req_appid"=>$req_appid,
            "req_partnerid"=>$req_partnerid,
            "req_prepayid"=>$req_prepayid,
            "req_package"=>$req_package,
            "req_noncestr"=>$req_noncestr,
            "req_timestamp"=>$req_timestamp,
            "req_sign"=>$req_sign,
            "message"=>"预支付订单发送成功");
        $this->err_log(var_export($request_data,1),'sdk_wx_pay_order');
        return $result;
    }

    public function h5_wx_result($order_info,$money,$USR_HEADER,$app_info,$SYS_order_id,$app_order_id,$notify_url){
        $wx_data = $this->make_h5_wx_data($money,$order_info['order_id'],$notify_url);
        $xml_data = $this->array_to_xml($wx_data);
        $request = $this->request(SDK_XY_API_url,$xml_data,array('Content-type: text/xml'));
        $request_data = json_decode(json_encode(simplexml_load_string($request, 'SimpleXMLElement', LIBXML_NOCDATA)), true);
        if($request_data['return_code']=='FAIL'){
            $result = array("errcode"=>1,"message"=>$request_data['return_msg']);
            die("0".base64_encode(json_encode($result)));
        }elseif($request_data['return_code']=='SUCCESS' && $request_data['result_code']=='SUCCESS'){
            $prepay_id = $request_data['prepay_id'];
            $req_nonce = $request_data['nonce_str'];
            $req_sign = $request_data['sign'];
        }elseif($request_data['return_code']=='SUCCESS' && $request_data['result_code']=='FAIL'){
            $result = array("errcode"=>1,"message"=>$request_data['err_code_desc'].$request_data['err_code']);
            die("0".base64_encode(json_encode($result)));
        }else{
            $result = array("errcode"=>1,"message"=>'请求订单异常.'.$request_data);
            die("0".base64_encode(json_encode($result)));
        }

        $result = array(
            "errcode"=>0,
            "req_prepayid"=>$prepay_id,
            "order_id"=>$wx_data['out_trade_no'],
            "sign"=>md5($prepay_id.$app_info['app_key']),
            "req_sign"=>$req_sign,
            "req_noncestr"=>$req_nonce,
            "message"=>"预支付订单发送成功");
        $this->err_log(var_export($request_data,1),'sdk_h5_wx_pay_order');
        return $result;
    }


    public function sdk_wx_pay(){
        $USR_HEADER = $this->get_usr_session('USR_HEADER');
        $app_id = $USR_HEADER['app_id'];
        $app_info = $this->DAO->get_app_info($app_id);
        if($USR_HEADER['md5'] !== md5($app_id.$USR_HEADER['player_id'].$USR_HEADER['serv_id'].$USR_HEADER['usr_id'].$USR_HEADER['usertoken'].$app_info['app_key'])){
            $result = array("errcode"=>1,"message"=>"参数不正确");
        }else{
            $time = strtotime("now");
            $this->cp_order_verify($app_info,$USR_HEADER);
            $money = $this->DAO->get_money_info($app_id,$USR_HEADER['money_id']);
            $money['unit_price'] = $money['good_price'];
            $SYS_order_id = $this->orderid($USR_HEADER['app_id']);
            $app_order_id = $this->get_app_order_id($app_info, $USR_HEADER, $SYS_order_id, $money);
            $money = $this->get_discount($USR_HEADER,$money);
            if(!empty($USR_HEADER['goodmultiple'])){
                $money['pay_price'] = $money['pay_price'] * $USR_HEADER['goodmultiple'];
                $money['good_price'] = $money['good_price'] * $USR_HEADER['goodmultiple'];
            }
            $order_info = $this->create_order($money, $USR_HEADER, $SYS_order_id, $app_order_id, $time,2);
            $money['good_price'] = $money['pay_price'];

            if(in_array($USR_HEADER['usr_id'], $this->qa_user_id)){
                $money['good_price'] = 0.01;
            }
            $result = $this->sdk_wx_result($order_info,$money,$USR_HEADER,$app_info,$SYS_order_id,$app_order_id,SDK_WX_notify_url);
            $this->err_log(var_export($result,1),'sdk_wx_order');
        }
        die("0".base64_encode(json_encode($result)));
    }

    public function h5_wx_pay(){
        $USR_HEADER = $this->get_usr_session('USR_HEADER');
        $app_id = $USR_HEADER['app_id'];
        $app_info = $this->DAO->get_app_info($app_id);
        if($USR_HEADER['md5'] !== md5($app_id.$USR_HEADER['player_id'].$USR_HEADER['serv_id'].$USR_HEADER['usr_id'].$USR_HEADER['usertoken'].$app_info['app_key'])){
            $result = array("errcode"=>1,"message"=>"参数不正确");
        }else{
            $time = strtotime("now");
            $this->cp_order_verify($app_info,$USR_HEADER);
            $money = $this->DAO->get_money_info($app_id,$USR_HEADER['money_id']);
            $money['unit_price'] = $money['good_price'];
            $SYS_order_id = $this->orderid($USR_HEADER['app_id']);
            $app_order_id = $this->get_app_order_id($app_info, $USR_HEADER, $SYS_order_id, $money);
            $money = $this->get_discount($USR_HEADER,$money);
            if(!empty($USR_HEADER['goodmultiple'])){
                $money['pay_price'] = $money['pay_price'] * $USR_HEADER['goodmultiple'];
                $money['good_price'] = $money['good_price'] * $USR_HEADER['goodmultiple'];
            }
            $order_info = $this->create_order($money, $USR_HEADER, $SYS_order_id, $app_order_id, $time,2);
            $money['good_price'] = $money['pay_price'];

            if(in_array($USR_HEADER['usr_id'], $this->qa_user_id)){
                $money['good_price'] = 0.01;
            }
            $result = $this->h5_wx_result($order_info,$money,$USR_HEADER,$app_info,$SYS_order_id,$app_order_id,SDK_WX_H5_notify);
            $this->err_log(var_export($result,1),'sdk_wx_order');
        }
        die("0".base64_encode(json_encode($result)));
    }

    public function xz_wx_order(){
        $USR_HEADER = $this->get_usr_session('USR_HEADER');
        $app_id = $USR_HEADER['app_id'];
        $app_info = $this->DAO->get_app_info($app_id);
        if($USR_HEADER['md5'] !== md5($app_id.$USR_HEADER['player_id'].$USR_HEADER['serv_id'].$USR_HEADER['usr_id'].$USR_HEADER['usertoken'].$app_info['app_key'])){
            $result = array("errcode"=>1,"message"=>"参数不正确");
        }else{
            $time = strtotime("now");
            $this->cp_order_verify($app_info,$USR_HEADER);
            $money = $this->DAO->get_money_info($app_id,$USR_HEADER['money_id']);
            $money['unit_price'] = $money['good_price'];
            $SYS_order_id = $this->orderid($USR_HEADER['app_id']);
            $app_order_id = $this->get_app_order_id($app_info, $USR_HEADER, $SYS_order_id, $money);
            $money = $this->get_discount($USR_HEADER,$money);
            if(!empty($USR_HEADER['goodmultiple'])){
                $money['pay_price'] = $money['pay_price'] * $USR_HEADER['goodmultiple'];
                $money['good_price'] = $money['good_price'] * $USR_HEADER['goodmultiple'];
            }
            $order_info = $this->create_order($money, $USR_HEADER, $SYS_order_id, $app_order_id, $time,2);
            $money['good_price'] = $money['pay_price'];

            if(in_array($USR_HEADER['usr_id'], $this->qa_user_id)){
                $money['good_price'] = 0.01;
            }
            if($app_info['payee_ch'] == '4'){
                $result = array(
                    "errcode" => 0,
                    "appId" => XZ_HNYQ_SDK_APPID,
                    "mhtOrderNo" => $SYS_order_id,
                    "mhtOrderName" => "海南盈趣充值消费",
                    "mhtOrderType" => "01",
                    "mhtCurrencyType" => "156",
                    "mhtOrderAmt" => $money['good_price']*100,
                    "mhtOrderDetail" => $money['app_name']."[".$money['good_name']."]",
                    "notifyUrl" => XZ_HNYQ_SDK_notify_url,
                    "mhtCharset" => 'UTF-8',
                    "mhtSignType" => 'MD5');
            }else if($app_info['payee_ch'] == '2'){
                if($USR_HEADER['channel']=='xianzaizhifu'){
                    $result = array(
                    "errcode" => 0,
                    "appId" => XZ_SDK_APPID,
                    "mhtOrderNo" => $SYS_order_id,
                    "mhtOrderName" => "牛牛充值消费",
                    "mhtOrderType" => "01",
                    "mhtCurrencyType" => "156",
                    "mhtOrderAmt" => $money['good_price']*100,
                    "mhtOrderDetail" => $money['app_name']."[".$money['good_name']."]",
                    "notifyUrl" => XZ_SDK_notify_url,
                    "mhtCharset" => 'UTF-8',
                    "mhtSignType" => 'MD5');
                }else{
                    $result = array(
                        "errcode" => 0,
                        "appId" => XZ_HN_SDK_APPID,
                        "mhtOrderNo" => $SYS_order_id,
                        "mhtOrderName" => "海南牛牛充值消费",
                        "mhtOrderType" => "01",
                        "mhtCurrencyType" => "156",
                        "mhtOrderAmt" => $money['good_price']*100,
                        "mhtOrderDetail" => $money['app_name']."[".$money['good_name']."]",
                        "notifyUrl" => XZ_HN_SDK_notify_url,
                        "mhtCharset" => 'UTF-8',
                        "mhtSignType" => 'MD5');
                }

            }else{
                $result = array(
                    "errcode" => 0,
                    "appId" => XZ_HN_SDK_APPID,
                    "mhtOrderNo" => $SYS_order_id,
                    "mhtOrderName" => "海南牛牛充值消费",
                    "mhtOrderType" => "01",
                    "mhtCurrencyType" => "156",
                    "mhtOrderAmt" => $money['good_price']*100,
                    "mhtOrderDetail" => $money['app_name']."[".$money['good_name']."]",
                    "notifyUrl" => XZ_HN_SDK_notify_url,
                    "mhtCharset" => 'UTF-8',
                    "mhtSignType" => 'MD5');
//                $result = array(
//                    "errcode" => 0,
//                    "appId" => XZ_SDK_APPID,
//                    "mhtOrderNo" => $SYS_order_id,
//                    "mhtOrderName" => "牛牛充值消费",
//                    "mhtOrderType" => "01",
//                    "mhtCurrencyType" => "156",
//                    "mhtOrderAmt" => $money['good_price']*100,
//                    "mhtOrderDetail" => $money['app_name']."[".$money['good_name']."]",
//                    "notifyUrl" => XZ_SDK_notify_url,
//                    "mhtCharset" => 'UTF-8',
//                    "mhtSignType" => 'MD5');
            }

            $this->err_log(var_export($result,1),'xz_wx_pre_order');
        }
        die("0".base64_encode(json_encode($result)));
    }

    public function xz_qpay_order(){
        $USR_HEADER = $this->get_usr_session('USR_HEADER');
        $app_id = $USR_HEADER['app_id'];
        $app_info = $this->DAO->get_app_info($app_id);
        if($USR_HEADER['md5'] !== md5($app_id.$USR_HEADER['player_id'].$USR_HEADER['serv_id'].$USR_HEADER['usr_id'].$USR_HEADER['usertoken'].$app_info['app_key'])){
            $result = array("errcode"=>1,"message"=>"参数不正确");
        }else{
            $time = strtotime("now");
            $this->cp_order_verify($app_info,$USR_HEADER);
            $money = $this->DAO->get_money_info($app_id,$USR_HEADER['money_id']);
            $money['unit_price'] = $money['good_price'];
            $SYS_order_id = $this->orderid($USR_HEADER['app_id']);
            $app_order_id = $this->get_app_order_id($app_info, $USR_HEADER, $SYS_order_id, $money);
            $money = $this->get_discount($USR_HEADER,$money);
            if(!empty($USR_HEADER['goodmultiple'])){
                $money['pay_price'] = $money['pay_price'] * $USR_HEADER['goodmultiple'];
                $money['good_price'] = $money['good_price'] * $USR_HEADER['goodmultiple'];
            }
            $order_info = $this->create_order($money, $USR_HEADER, $SYS_order_id, $app_order_id, $time,5);
            $money['good_price'] = $money['pay_price'];

            if(in_array($USR_HEADER['usr_id'], $this->qa_user_id)){
                $money['good_price'] = 0.01;
            }
            $result = array(
                "errcode" => 0,
                "appId" => XZ_SDK_APPID,
                "mhtOrderNo" => $SYS_order_id,
                "mhtOrderName" => "牛牛充值消费",
                "mhtOrderType" => "01",
                "mhtCurrencyType" => "156",
                "mhtOrderAmt" => $money['good_price']*100,
                "mhtOrderDetail" => $money['app_name']."[".$money['good_name']."]",
                "notifyUrl" => XZ_SDK_notify_url,
                "mhtCharset" => 'UTF-8',
                "mhtSignType" => 'MD5');
            $this->err_log(var_export($result,1),'xz_qpay_pre_order');
        }
        die("0".base64_encode(json_encode($result)));
    }


    public function sdk_qq_pay(){
        $USR_HEADER = $this->get_usr_session('USR_HEADER');
        $app_id = $USR_HEADER['app_id'];
        $app_info = $this->DAO->get_app_info($app_id);
        if($USR_HEADER['md5'] !== md5($app_id.$USR_HEADER['player_id'].$USR_HEADER['serv_id'].$USR_HEADER['usr_id'].$USR_HEADER['usertoken'].$app_info['app_key'])){
            $result = array("errcode"=>1,"message"=>"参数不正确");
        }else{
            $time = strtotime("now");
            $this->cp_order_verify($app_info,$USR_HEADER);
            $money = $this->DAO->get_money_info($app_id,$USR_HEADER['money_id']);
            $money['unit_price'] = $money['good_price'];
            $SYS_order_id = $this->orderid($USR_HEADER['app_id']);
            $app_order_id = $this->get_app_order_id($app_info, $USR_HEADER, $SYS_order_id, $money);
            $money = $this->get_discount($USR_HEADER,$money);
            if(!empty($USR_HEADER['goodmultiple'])){
                $money['pay_price'] = $money['pay_price'] * $USR_HEADER['goodmultiple'];
                $money['good_price'] = $money['good_price'] * $USR_HEADER['goodmultiple'];
            }
            $order_info = $this->create_order($money, $USR_HEADER, $SYS_order_id, $app_order_id, $time,5);
            $money['good_price'] = $money['pay_price'];

            if(in_array($USR_HEADER['usr_id'], $this->qa_user_id)){
                $money['good_price'] = 0.01;
            }
            $result = $this->sdk_qpay_result($order_info,$money,$USR_HEADER,$app_info,$SYS_order_id,$app_order_id,SDK_QPAY_notify_url);
            $this->err_log(var_export($result,1),'sdk_qpay_order');
        }
        die("0".base64_encode(json_encode($result)));
    }

    public function wx_pay_order(){
        $USR_HEADER = $this->get_usr_session('USR_HEADER');
        $app_id = $USR_HEADER['app_id'];
        $app_info = $this->DAO->get_app_info($app_id);
        if($USR_HEADER['md5'] !== md5($app_id.$USR_HEADER['player_id'].$USR_HEADER['serv_id'].$USR_HEADER['usr_id'].$USR_HEADER['usertoken'].$app_info['app_key'])){
            $result = array("errcode"=>1,"message"=>"参数不正确");
        }else{
            $time = strtotime("now");
            $this->cp_order_verify($app_info,$USR_HEADER);
            $money = $this->DAO->get_money_info($app_id,$USR_HEADER['money_id']);
            $money['unit_price'] = $money['good_price'];
            $SYS_order_id = $this->orderid($USR_HEADER['app_id']);
            $app_order_id = $this->get_app_order_id($app_info, $USR_HEADER, $SYS_order_id, $money);
            $money = $this->get_discount($USR_HEADER,$money);
            if(!empty($USR_HEADER['goodmultiple'])){
                $money['pay_price'] = $money['pay_price'] * $USR_HEADER['goodmultiple'];
                $money['good_price'] = $money['good_price'] * $USR_HEADER['goodmultiple'];
            }
            $order_info = $this->create_order($money, $USR_HEADER, $SYS_order_id, $app_order_id, $time,2);
            $money['good_price'] = $money['pay_price'];
            if(in_array($USR_HEADER['usr_id'], $this->qa_user_id)){
                $money['good_price'] = 0.01;
            }
            $result = $this->get_wx_result($order_info,$money,$USR_HEADER,$app_info,$SYS_order_id,$app_order_id);
        }
        die("0".base64_encode(json_encode($result)));
    }

    public function super_pay_order(){
        $USR_HEADER = $this->get_usr_session('USR_HEADER');
        $app_id = $USR_HEADER['app_id'];
        $app_info = $this->DAO->get_super_app_info($app_id);
        if($USR_HEADER['md5'] !== md5($app_id.$USR_HEADER['player_id'].$USR_HEADER['serv_id'].$USR_HEADER['usr_id'].$USR_HEADER['usertoken'].$app_info['app_key'])){
            $result = array("errcode"=>1,"message"=>"参数不正确");
        }else{
            $time = strtotime("now");
            $this->cp_order_verify($app_info,$USR_HEADER);
            $money = $this->DAO->get_super_money_info($app_id,$USR_HEADER['money_id']);
            $SYS_order_id = $this->super_orderid($USR_HEADER['app_id']);
            $app_order_id = $this->get_app_order_id($app_info, $USR_HEADER, $SYS_order_id, $money);
            $money['unit_price'] = $money['good_price'];
            if(!empty($USR_HEADER['goodmultiple'])){
                $money['good_price'] = $money['unit_price'] * $USR_HEADER['goodmultiple'];
            }
            $order_info = $this->create_super_order($money, $USR_HEADER, $SYS_order_id, $app_order_id, $time);
            if(in_array($USR_HEADER['usr_id'], $this->qa_user_id)){
                $money['good_price'] = 0.01;
            }
            $result = $this->get_pay_result($order_info,$money,$USR_HEADER,$app_info,$SYS_order_id,$app_order_id,ALI_SUPER_notify_url);
            $this->err_log(var_export($result,1),'super_cp_order');
        }
        die("0".base64_encode(json_encode($result)));
    }

    public function create_pay_order(){
        $USR_HEADER = $this->get_usr_session('USR_HEADER');
        $app_id = $USR_HEADER['app_id'];
        $app_info = $this->DAO->get_super_app_info($app_id);
        if($USR_HEADER['player_id'] =='1024803'){
            $result = array("errcode" => 1, "msg" => "你的账号存在非法操作已被停止充值,请联系客服解封");
            die("0".base64_encode(json_encode($result)));
        }
        if($USR_HEADER['md5'] !== md5($app_id.$USR_HEADER['player_id'].$USR_HEADER['serv_id'].$USR_HEADER['usr_id'].$USR_HEADER['usertoken'].$app_info['app_key']) &&
            $USR_HEADER['md5'] !== md5($app_id.$USR_HEADER['player_id'].$USR_HEADER['serv_id'].$USR_HEADER['usr_id'].$USR_HEADER['usertoken']."=".$app_info['app_key']) &&
            $USR_HEADER['md5'] !== md5($app_id.$USR_HEADER['player_id'].$USR_HEADER['serv_id'].$USR_HEADER['usr_id'].$USR_HEADER['usertoken']."==".$app_info['app_key']) &&
            $USR_HEADER['md5'] !== md5($app_id.$USR_HEADER['player_id'].$USR_HEADER['serv_id'].$USR_HEADER['usr_id'].urldecode($USR_HEADER['usertoken']).$app_info['app_key'])
        ){
            $this->err_log(var_export($USR_HEADER,1),"super_pay_params_md5");
            $this->err_log(var_export($USR_HEADER['md5'],1),"super_pay_params_md5");
            $this->err_log(var_export($app_id.$USR_HEADER['player_id'].$USR_HEADER['serv_id'].$USR_HEADER['usr_id'].$USR_HEADER['usertoken'].$app_info['app_key'],1),"super_pay_params_md5");
            $result = array("errcode" => 1, "msg" => "参数不正确");
            die("0".base64_encode(json_encode($result)));
//            $result = array("errcode" => 1, "msg" => "参数不正确 md5=".$USR_HEADER['md5']."&sign=".$app_id.$USR_HEADER['player_id'].$USR_HEADER['serv_id'].$USR_HEADER['usr_id'].$USR_HEADER['usertoken'].$app_info['app_key']);
        }else{
            $ch_info = $this->DAO->get_ch_by_appid($app_id,$USR_HEADER['channel']);
            if($ch_info['status']=='4'){
                $result = array("errcode" => 1, "msg" => "充值已关闭请联系客服");
                die("0".base64_encode(json_encode($result)));
            }
//            if($USR_HEADER['usr_id']=='71'){
//                $channel_money = $this->DAO->get_channel_sum_moeny($app_id,$USR_HEADER['channel']);
//                if(($channel_money['sum']+$ch_info['warn_money']) > $ch_info['recharge_count']){
//                    $balance = $ch_info['recharge_count'] - $channel_money['sum'];
//                    if($balance < 0){
//                        $balance = 0;
//                    }
//                    $notice_mmc = $this->DAO->get_mmc_warn_money($ch_info['id']);
//                    if(empty($notice_mmc)){
//                        $send_result = $this->send_sms($ch_info['mobile'],array($ch_info['channel'],$balance),"234120");
//                        $this->err_log(var_export($send_result,1),'warn_send');
//                        if($send_result==0){
//                            $this->DAO->set_mmc_warn_money($ch_info['id'],$ch_info);
//                        }
//                    }
//                }
//                if($channel_money['sum'] >= $ch_info['recharge_count']){
//                    $result = array("errcode" => 1, "msg" => "充值限额不足,请及时联系客服。");
//                    die("0".base64_encode(json_encode($result)));
//                }
//                $result = array("errcode" => 1, "msg" => "充值限额不足。");
//                die("0".base64_encode(json_encode($result)));
//            }
            if($ch_info['is_open']=='1'){
                $channel_money = $this->DAO->get_channel_sum_moeny($app_id,$USR_HEADER['channel']);
                if(($channel_money['sum']+$ch_info['warn_money']) > $ch_info['recharge_count']){
                    $balance = $ch_info['recharge_count'] - $channel_money['sum'];
                    if($balance < 0){
                        $balance = 0;
                    }
                    $notice_mmc = $this->DAO->get_mmc_warn_money($ch_info['id']);
                    if(empty($notice_mmc)){
                        $send_result = $this->send_sms($ch_info['mobile'],array($ch_info['channel'],$balance),"234120");
                        $this->err_log(var_export($ch_info,1),'warn_send');
                        $this->err_log(var_export($balance,1),'warn_send');
                        $this->err_log(var_export($send_result,1),'warn_send');
                        if($send_result==0){
                            $this->DAO->set_mmc_warn_money($ch_info['id'],$ch_info);
                        }
                    }
                }
                if($channel_money['sum'] >= $ch_info['recharge_count']){
                    $result = array("errcode" => 1, "msg" => "充值已关闭,请联系渠道管理员开启。");
                    die("0".base64_encode(json_encode($result)));
                }
            }
            $time = strtotime("now");
            if(!$USR_HEADER['money_id']){
                $result = array("errcode" => 1, "msg" => "商品ID未传递。");
                die("0".base64_encode(json_encode($result)));
            }
            $money = $this->DAO->get_super_money_info($app_id,$USR_HEADER['money_id']);
            $money['unit_price'] = $money['good_price'];
            if(!empty($USR_HEADER['goodmultiple'])){
                $money['good_price'] = $money['unit_price'] * $USR_HEADER['goodmultiple'];
            }
            if($ch_info['is_open']=='1'){
                if(($channel_money['sum'] + $money['good_price']) > $ch_info['recharge_count']){
                    $surplus =  $ch_info['recharge_count'] - $channel_money['sum'];
                    $result = array("errcode" => 1, "msg" => "平台余额为".$surplus."元, 请联系客服。");
                    die("0".base64_encode(json_encode($result)));
                }
            }
            $super_order_id = $this->super_orderid($USR_HEADER['app_id']);
            $app_order_id = $this->get_app_order_id($app_info, $USR_HEADER, $super_order_id, $money);
            $order_info = $this->create_super_order($money, $USR_HEADER, $super_order_id, $app_order_id, $time);
            $result = array("errcode" => 0, "orderid" => $order_info['order_id'],"sign" => md5($USR_HEADER['money_id'].$order_info['order_id'].$app_info['app_key']));
        }
        die("0".base64_encode(json_encode($result)));
    }

    public function send_sms($mobile, $data, $template_id) {
        COMMON('CCPRestSDK');
        $rest = new REST();
        $result = $rest->sendTemplateSMS($mobile, $data, $template_id);
        unset($rest);
        file_put_contents(PREFIX.DS.'logs/'.date('Ymd').'_sms.log','手机号:'.$mobile.',数据:'.
            json_encode($data).',结果:'.json_encode($result)."\r\n",FILE_APPEND);
        if(!$result) {
            return 1;
        }
        if($result->statusCode != 0){
            return $result;
        }
        return 0;
    }

    public function nnb_pay(){
        $USR_HEADER = $this->get_usr_session('USR_HEADER');
        $app_id = $USR_HEADER['app_id'];
        $app_info = $this->DAO->get_app_info($app_id);
        if($USR_HEADER['md5'] !== md5($app_id.$USR_HEADER['player_id'].$USR_HEADER['serv_id'].$USR_HEADER['usr_id'].$USR_HEADER['usertoken'].$app_info['app_key'])){
            $result = array("errcode"=>1,"message"=>"参数不正确");
        }else{
            $time = strtotime("now");
            $this->cp_order_verify($app_info,$USR_HEADER);
            $money = $this->DAO->get_money_info($app_id,$USR_HEADER['money_id']);
            $money['unit_price'] = $money['good_price'];
            $SYS_order_id = $this->orderid($USR_HEADER['app_id']);
            $app_order_id = $this->get_app_order_id($app_info, $USR_HEADER, $SYS_order_id, $money);
            $money = $this->get_discount($USR_HEADER,$money);
            if(!empty($USR_HEADER['goodmultiple'])){
                $money['pay_price'] = $money['pay_price'] * $USR_HEADER['goodmultiple'];
                $money['good_price'] = $money['good_price'] * $USR_HEADER['goodmultiple'];
            }
            $order_info = $this->create_order($money, $USR_HEADER, $SYS_order_id, $app_order_id, $time,3);
            $result = $this->do_nnb_pay($order_info,$app_order_id,$money);
        }
        die("0".base64_encode(json_encode($result)));
    }

    public function nd_pay(){
        $USR_HEADER = $this->get_usr_session('USR_HEADER');
        $app_id = $USR_HEADER['app_id'];
        $app_info = $this->DAO->get_app_info($app_id);
        if($USR_HEADER['md5'] !== md5($app_id.$USR_HEADER['player_id'].$USR_HEADER['serv_id'].$USR_HEADER['usr_id'].$USR_HEADER['usertoken'].$app_info['app_key'])){
            $result = array("errcode"=>1,"message"=>"参数不正确");
        }else{
            $time = strtotime("now");
            $this->cp_order_verify($app_info,$USR_HEADER);
            $money = $this->DAO->get_money_info($app_id,$USR_HEADER['money_id']);
            $money['unit_price'] = $money['good_price'];
            $SYS_order_id = $this->orderid($USR_HEADER['app_id']);
            $app_order_id = $this->get_app_order_id($app_info, $USR_HEADER, $SYS_order_id, $money);
            $money = $this->get_discount($USR_HEADER,$money);
            if(!empty($USR_HEADER['goodmultiple'])){
                $money['pay_price'] = $money['pay_price'] * $USR_HEADER['goodmultiple'];
                $money['good_price'] = $money['good_price'] * $USR_HEADER['goodmultiple'];
            }
            $order_info = $this->create_order($money, $USR_HEADER, $SYS_order_id, $app_order_id, $time,6);
            $result = $this->do_nd_pay($order_info,$app_order_id,$money);
        }
        die("0".base64_encode(json_encode($result)));
    }


    protected function do_nnb_pay($order,$app_order_id,$money){
        $data = array("errcode"=>0,'message' => '请求出错','success'=>0);
        $userDao = new user_dao();
        $user_info = $userDao->get_user_info($order['buyer_id']);
        if(!$user_info){
            $data['message']='用户信息异常.';
            $this->err_log(var_export($order,1),'nnb_pay_log');
            die("0".base64_encode(json_encode($data)));
        }
        if($user_info['nnb_lock']!='0'){
            $data['message']='牛币状态异常,状态码:'.$user_info['nnb_lock'];
            $order['user_nnb'] = $user_info['nnb'];
            $order['nnb_lock'] = $user_info['nnb_lock'];
            $this->err_log(var_export($order,1),'nnb_pay_log');
            die("0".base64_encode(json_encode($data)));
        }
        if(empty($money['good_price'])){
            $data['message']='支付金额异常.';
            $this->err_log(var_export($order,1),'nnb_pay_log');
            die("0".base64_encode(json_encode($data)));
        }
        if(($money['good_price'] > $user_info['nnb']) || empty($user_info['nnb'])){
            $data['message']='牛币不足';
            $order['user_nnb'] = $user_info['nnb'];
            $this->err_log(var_export($order,1),'nnb_pay_log');
            die("0".base64_encode(json_encode($data)));
        }
        $userDao->user_lock_nnb(1, $user_info['user_id']);
        $this->DAO->update_nnb_pay_info($order['order_id']);
        $order['nnb_num'] = $money['good_price'];

        $userDao->update_user_nnb($order);
        $userDao->nnb_log($this->create_guid(),$order,2);
        $data = array("errcode"=>0,'message' => $app_order_id,'success'=>1);

        $order['cp_order_id'] = $app_order_id;
        $order['now_nnb'] = $user_info['nnb'] - $money['good_price'];
        $order['success_desc'] = $order['buyer_id'].'用户已成功消费'.$order['nnb_num'].'牛币';
        $this->err_log(var_export($order,1),'nnb_pay_log');
        return $data ;
    }

    protected function do_nd_pay($order,$app_order_id,$money){
        $data = array("errcode"=>1,'message' => '请求出错','success'=>0);
        $userDao = new user_dao();
        $user_info = $userDao->get_user_info($order['buyer_id']);
        if(!$user_info){
            $data['message']='用户信息异常.';
            $this->err_log(var_export($order,1),'nd_pay_log');
            die("0".base64_encode(json_encode($data)));
        }
        $nd_user_info = $this->DAO->get_nd_user_info($order['buyer_id'],$order['app_id']);
        if($nd_user_info['nd_lock']!='0'){
            $data['message']='牛点状态异常,状态码:'.$nd_user_info['nd_lock'];
            $order['user_nd'] = $nd_user_info['nd_num'];
            $order['nd_lock'] = $nd_user_info['nd_lock'];
            $this->err_log(var_export($order,1),'nd_pay_log');
            die("0".base64_encode(json_encode($data)));
        }
        if(empty($money['good_price'])){
            $data['message']='支付金额异常.';
            $this->err_log(var_export($order,1),'nd_pay_log');
            die("0".base64_encode(json_encode($data)));
        }
        if(($money['good_price'] > $nd_user_info['nd_num']) || empty($nd_user_info['nd_num'])){
            $data['message']='牛点不足';
            $order['user_nd'] = $nd_user_info['nd_num'];
            $this->err_log(var_export($order,1),'nd_pay_log');
            die("0".base64_encode(json_encode($data)));
        }
        $this->DAO->nd_user_lock(1,$order['app_id'],$order['buyer_id']);
        $this->DAO->update_nnb_pay_info($order['order_id']);
        $order['nd_num'] = $money['good_price'];
        $this->DAO->update_user_nd($order);
        $this->DAO->insert_nd_user_revoke_log($order,5,"游戏内充值");
        $data = array("errcode"=>0,'message' => $app_order_id,'success'=>1);

        $order['cp_order_id'] = $app_order_id;
        $order['now_nd'] = $nd_user_info['nd_num'] - $money['good_price'];
        $order['success_desc'] = $order['buyer_id'].'用户已成功消费'.$order['nd_num'].'牛币';
        $this->err_log(var_export($order,1),'nd_pay_log');
        return $data ;
    }


    public function cp_order_verify($app_info,$USR_HEADER){
        if(!$app_info['sdk_order_url'] && !$USR_HEADER['cp_order_id']){
            $result = array("errcode"=>1,"message"=>"请求订单错误。");
            die("0".base64_encode(json_encode($result)));
        }
        if(!$USR_HEADER['money_id']){
            $result = array("errcode"=>1,"message"=>"商品ID未传递。");
            die("0".base64_encode(json_encode($result)));
        }
    }

    public function get_discount($USR_HEADER,$money){
        $money['discount'] = 10;
        $money['pay_price'] = $money['good_price'];
        $good_discount = $this->DAO->get_good_discount($USR_HEADER['app_id'],$USR_HEADER['channel']);
        if(!empty($good_discount)){
            $money['discount'] = !empty($good_discount['discount'])?$good_discount['discount']:10;
            $money['pay_price'] = number_format($money['good_price']*$money['discount']/10,1,'.','');
        }
        return $money;
    }

    public function get_app_order_id($app_info, $USR_HEADER, $SYS_order_id, $money){
        if($USR_HEADER['cp_order_id']){
            $app_order_id = $USR_HEADER['cp_order_id'];
        }else{
            $app_order_id = $this->get_game_orderid($app_info, $USR_HEADER, $SYS_order_id, $money);
        }
        if(!$app_order_id){
            $result = array("errcode"=>1,"message"=>"游戏订单号创建失败。");
            die("0".base64_encode(json_encode($result)));
        }
        return $app_order_id;
    }

    public function get_pay_result($order_info,$money,$USR_HEADER,$app_info,$SYS_order_id,$app_order_id,$notify_url){
        $sign = $this->make_pay_sign($order_info,$money,$USR_HEADER,$app_info);
        $result = array("errcode"=>0,
            "orderid"=>$SYS_order_id,
            "goodsname"=>$money['app_name']."[".$money['good_name'].$money['good_unit']."]",
            "goodsdesc"=>$money['good_intro']."[".$money['good_name'].$money['good_unit'],
            "goodsfee"=>$money['good_price'],
            "notifyurl"=>$notify_url,
            "cporderid" => $app_order_id,
            "paytype" => 'FJ',
            "sign"=>$sign);
        if($USR_HEADER['sdkver'] > '3.0.0.6' && $app_info['payee_ch'] == '2'){
            $result['paytype']='HN';
            $result['notifyurl']='http://ins.66173yx.com/secure_notify.php';
        }else if($USR_HEADER['sdkver'] > '3.0.0.6' && $app_info['payee_ch'] == '4'){
            $result['paytype']='HNYQ';
            $result['notifyurl']='http://ins.66173.cn/hnyq_secure_notify.php';
        }
        return $result;
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


    protected function make_pay_sign($order,$money,$USR_HEADER,$app_info){
        if(!$order || !$money || !$USR_HEADER || !$app_info) {
            return '';
        }
        $sign = md5($order['app_id'].$order['order_id'].$money['good_price'].$order['buyer_id'].$USR_HEADER['usertoken'].$app_info['app_key']);
        return $sign;
    }

    public function make_wx_data($money,$out_trade_no,$notify_url){
        $data = array(
            'appid' => WX_APPID,
            'mch_id' => MCH_ID,
            'nonce_str' => $this->create_guids(),
            'body' => $money['good_name'],
            'out_trade_no' => $out_trade_no,
            'total_fee' => $money['good_price']*100,
            'spbill_create_ip' => $this->client_ip(),
            'notify_url' => $notify_url,
            'trade_type' => 'APP'
        );
        ksort($data);
        $str = '';
        $new_data=array();
        foreach($data as $key => $val ){
            if(!empty($val)){
                $new_data[$key]=$val;
                $str.=$key."=".$val."&";
            }
        }
        $str = $str."key=".WX_APPKEY;
        $new_data['sign']=strtoupper(md5($str));
        return $new_data;
    }

    public function create_guids() {
        $charid = strtolower(md5(uniqid(mt_rand(), true)));
        $uuid = substr($charid, 0, 32);
        return $uuid;
    }

    public function nnb_wx_order(){
        $USR_HEADER = $this->get_usr_session('USR_HEADER');
        $app_id = $USR_HEADER['app_id'];
        $app_info = $this->DAO->get_app_info($app_id);

        if($USR_HEADER['md5'] !== md5($app_id.$USR_HEADER['price'].$USR_HEADER['rate'].$USR_HEADER['usr_id'].$USR_HEADER['usertoken'].$app_info['app_key'])){
            $result = array("errcode"=>1,"message"=>"参数不正确");
        }else{

            $time = strtotime("now");
            if(!$USR_HEADER['price']){
                $result = array("errcode"=>1,"message"=>"充值金额未传递。");
                die("0".base64_encode(json_encode($result)));
            }
            $money=array(
                'pay_money' => $USR_HEADER['price'],
                'rate' => $USR_HEADER['rate'],
                'nnb_num'=>$USR_HEADER['price']*$USR_HEADER['rate'],
                'title'=>'充'.$USR_HEADER['price'].'元得'.$USR_HEADER['price']*$USR_HEADER['rate'].'牛币',
                'name'=>'充值'.$USR_HEADER['price'].'元获得'.$USR_HEADER['price']*$USR_HEADER['rate'].'牛币',
                'good_price' => $USR_HEADER['price'],
                'good_name'=>'充值'.$USR_HEADER['price'].'元获得'.$USR_HEADER['price']*$USR_HEADER['rate'].'牛币',
            );
            $niuniu_order_id = $this->niu_orderid($USR_HEADER['app_id']);
            if(!$niuniu_order_id){
                $result = array("errcode"=>1,"message"=>"66订单号创建失败。");
                die("0".base64_encode(json_encode($result)));
            }
            $niu_order_info = $this->create_niu_order($money,$USR_HEADER,$niuniu_order_id, $time,2);
            if(in_array($USR_HEADER['usr_id'], $this->qa_user_id)){
                $money['good_price'] = 0.01;
            }
            $result = $this->nnb_wx_result($niu_order_info,$money);
        }
        die("0".base64_encode(json_encode($result)));
    }

    public function nnb_pay_order(){
        $USR_HEADER = $this->get_usr_session('USR_HEADER');
        $app_id = $USR_HEADER['app_id'];
        $app_info = $this->DAO->get_app_info($app_id);

        if($USR_HEADER['md5'] !== md5($app_id.$USR_HEADER['price'].$USR_HEADER['rate'].$USR_HEADER['usr_id'].$USR_HEADER['usertoken'].$app_info['app_key'])){
            $result = array("errcode"=>1,"message"=>"参数不正确");
        }else{

            $time = strtotime("now");
            if(!$USR_HEADER['price']){
                $result = array("errcode"=>1,"message"=>"充值金额未传递。");
                die("0".base64_encode(json_encode($result)));
            }
            $money=array(
                'pay_money' => $USR_HEADER['price'],
                'rate' => $USR_HEADER['rate'],
                'nnb_num'=>$USR_HEADER['price']*$USR_HEADER['rate'],
                'title'=>'充'.$USR_HEADER['price'].'元得'.$USR_HEADER['price']*$USR_HEADER['rate'].'牛币',
                'name'=>'充值'.$USR_HEADER['price'].'元获得'.$USR_HEADER['price']*$USR_HEADER['rate'].'牛币',
            );
            $niuniu_order_id = $this->niu_orderid($USR_HEADER['app_id']);
            if(!$niuniu_order_id){
                $result = array("errcode"=>1,"message"=>"66订单号创建失败。");
                die("0".base64_encode(json_encode($result)));
            }
            $niu_order_info = $this->create_niu_order($money,$USR_HEADER,$niuniu_order_id, $time,1);
            if(in_array($USR_HEADER['usr_id'], $this->qa_user_id)){
                $niu_order_info['pay_money'] = 0.01;
            }
            $sign = $this->make_nnb_sign($niu_order_info,$app_info,$USR_HEADER);
            $result = array("errcode"=>0, "orderid"=>$niuniu_order_id, "goodsname"=>$money['name'],
                "goodsdesc"=>$money['title'],"goodsfee"=>$niu_order_info['pay_money'],
                "notifyurl"=>ALI_NIUCOIN_notify_url,'cporderid' => '','sign'=>$sign);
            $this->err_log(var_export($result,1),'nnb_order');
        }
        die("0".base64_encode(json_encode($result)));
    }

    protected function make_nnb_sign($order,$app_info,$USR_HEADER){
        if(!$order || !$app_info || !$USR_HEADER){
            return '';
        }
        $sign = md5($order['app_id'].$order['order_id'].$order['pay_money'].$order['rate'].$order['buyer_id'].$USR_HEADER['usertoken'].$app_info['app_key']);
        return $sign;
    }

    protected function create_niu_order($money, $header, $order_id, $time,$pay_ch){
        $order['app_id']        = $header['app_id'];
        $order['order_id']       = $order_id;
        $order['pay_channel']   = $pay_ch;
        $order['buyer_id']    = $header['usr_id'];
        $order['title']       = $money['title'];
        $order['pay_money']   = $money['pay_money'];
        $order['nnb_num']   = $money['nnb_num'];
        $order['rate']    = $money['rate'];
        $order['status']      = 0;
        $order['buy_time']    = $time;
        $order['ip']     = $this->client_ip();
        $order['channel']   = $header['channel'];
        if(!$this->DAO->create_nnb_order($order)){
            $result = array("errcode"=>1,"message"=>"牛币订单创建失败!");
            die("0".base64_encode(json_encode($result)));
        }
        return $order;
    }

    protected function create_order($money, $header, $YD_order_id, $app_order_id, $time,$pay_channel=1){
        $order['app_id']        = $header['app_id'];
        $order['order_id']       = $YD_order_id;
        $order['app_order_id']  = $app_order_id;
        $order['pay_channel']   = $pay_channel;
        $order['buyer_id']    = $header['usr_id'];
        $order['role_id']     = $header['player_id'];
        $order['product_id']  = $money['id'];
        $order['unit_price']  = $money['unit_price'];
        $order['title']       = $money['good_name'];
        $order['role_name']   = $header['player_name'];
        $order['amount']      = $money['good_amount'];
        $order['pay_money']   = $money['good_price'];
        $order['discount']    = isset($money['discount'])?$money['discount']:10;
        $order['pay_price']   = isset($money['pay_price'])?$money['pay_price']:$money['good_price'];
        $order['status']      = 0;
        $order['buy_time']    = $time;
        $order['ip']     = $this->client_ip();
        $order['serv_id']   = $header['serv_id'];
        $order['channel']   = $header['channel'];
        $order['payExpandData'] = isset($header['payexpanddata'])?$header['payexpanddata']:'';
        $order['serv_name']   =  isset($header['serv_name'])?$header['serv_name']:'';
        $user_info = $this->DAO->get_userapp_channel($header['usr_id'],$header['app_id']);
        if($user_info['channel']){
            $order['channel']   = $user_info['channel'];
        }
        if(!$this->DAO->create_order($order)){
            $result = array("errcode"=>1,"message"=>"订单创建失败!");
            die("0".base64_encode(json_encode($result)));
        }
        return $order;
    }

    protected function create_super_order($money, $header, $order_id, $app_order_id, $time){
        $order['app_id']        = $header['app_id'];
        $order['order_id']       = $order_id;
        $order['app_order_id']  = $app_order_id;
        $order['pay_channel']   = 1;
        $order['buyer_id']    = $header['usr_id'];
        $order['role_id']     = $header['player_id'];
        $order['product_id']  = $money['id'];
        $order['unit_price']  = $money['unit_price'];
        $order['title']       = $money['good_name'];
        $order['role_name']   = $header['player_name'];
        $order['amount']      = $money['good_amount'];
        $order['pay_money']   = $money['good_price'];
        $order['discount']    = 10;
        $order['pay_price']   = $money['good_price'];
        $order['status']      = 0;
        $order['buy_time']    = $time;
        $order['ip']     = $this->client_ip();
        $order['serv_id']   = $header['serv_id'];
        $order['channel']   = $header['channel'];
        $order['payExpandData'] = isset($header['payexpanddata'])?$header['payexpanddata']:'';
        $order['serv_name'] = $header['serv_name'];
        if(!$this->DAO->create_super_order($order)){
            $result = array("errcode"=>1,"message"=>"订单创建失败!");
            die("0".base64_encode(json_encode($result)));
        }
        return $order;
    }

    public function sdk_qpay_notify($data){
        try{
            if ($data['out_trade_no'] && $data['transaction_id'] && $data['openid']) {
                $order = $this->DAO->get_order_info($data['out_trade_no']);
                if($order['status']==2){
                    //订单已完成，更新用户email
                    $this->DAO->update_order_success($data['out_trade_no'], $data['transaction_id'], $data['openid'], 2);
                }else{
                    //更新订单为已支付状态
                    $this->DAO->update_order_success($data['out_trade_no'], $data['transaction_id'], $data['openid'], 1);
                }
                echo 'success';
            } else {
                echo "fail";
            }
        }catch (Exception $e){
            $this->err_log($e->getMessage(), "sdk_qpay_notify");
        }

    }

    public function sdk_wx_notify($data){
        try{
            if ($data['out_trade_no'] && $data['transaction_id'] && $data['openid']) {
                 $order = $this->DAO->get_order_info($data['out_trade_no']);
                    if($order['status']==2){
                        //订单已完成，更新用户email
                        $this->DAO->update_order_success($data['out_trade_no'], $data['transaction_id'], $data['openid'], 2);
                    }else{
                        //更新订单为已支付状态
                        $this->DAO->update_order_success($data['out_trade_no'], $data['transaction_id'], $data['openid'], 1);
                    }
                    echo 'success';
                } else {
                    echo "fail";
                }
        }catch (Exception $e){
            $this->err_log($e->getMessage(), "sdk_wx_notify");
        }

    }

    public function xz_sdk_notify($data,$collect_company = 1){
        try{
            if ($data['mhtOrderNo'] && $data['nowPayOrderNo']) {
                $order = $this->DAO->get_order_info($data['mhtOrderNo']);
                if($order['status']==2){
                    //订单已完成，更新用户email
                    $this->DAO->update_wap_wx_order_success($data['mhtOrderNo'], $data['nowPayOrderNo'], $data['payConsumerId'], 2,$collect_company);
                }else{
                    //更新订单为已支付状态
                    $this->DAO->update_wap_wx_order_success($data['mhtOrderNo'], $data['nowPayOrderNo'], $data['payConsumerId'], 1,$collect_company);
                }
                echo 'success=Y';
            } else {
                echo "fail";
            }
        }catch (Exception $e){
            $this->err_log($e->getMessage(), "xz_sdk_notify");
        }

    }


    public function wx_notify($data){
        try{
            if ($data['out_trade_no'] && $data['transaction_id'] && $data['openid']) {
                $order = $this->DAO->get_nnb_order_info($data['out_trade_no']);
                if($order['status']==1){
                    $this->DAO->update_order_success($data['out_trade_no'], $data['transaction_id'], $data['openid'], 1);
                }else{
                    $this->DAO->update_order_success($data['out_trade_no'], $data['transaction_id'], $data['openid'], 1);
                }
                echo "success";
            } else {
                echo "fail";
            }

        }catch (Exception $e){
            $this->err_log($e->getMessage(), "wxsdk_notify");
        }
    }

    public function wx_notify_callback($data,$collect_company = 1){
        try{
            if ($data['out_trade_no'] && $data['transaction_id'] && $data['openid']) {
                $order = $this->DAO->get_nnb_order_info($data['out_trade_no']);
                if($order['status']==1){
                    $this->DAO->update_wap_wx_order_success($data['out_trade_no'], $data['transaction_id'], $data['openid'], 1,$collect_company);
                }else{
                    $this->DAO->update_wap_wx_order_success($data['out_trade_no'], $data['transaction_id'], $data['openid'], 1,$collect_company);
                }
                echo "success";
            } else {
                echo "fail";
            }

        }catch (Exception $e){
            $this->err_log($e->getMessage(), "bj_wxsdk_notify");
        }
    }

    public function wx_wap_notify($data,$collect_company = 1){
        try{
            if ($data['mhtOrderNo'] && $data['nowPayOrderNo']) {
                $order = $this->DAO->get_order_info($data['mhtOrderNo']);
                if($order['status']==2){
                    //订单已完成，更新用户email
                    $this->DAO->update_wap_wx_order_success($data['mhtOrderNo'], $data['nowPayOrderNo'], $data['payConsumerId'], 2,$collect_company);
                }else{
                    //更新订单为已支付状态
                    $this->DAO->update_wap_wx_order_success($data['mhtOrderNo'], $data['nowPayOrderNo'], $data['payConsumerId'], 1,$collect_company);
                }
                echo 'success=Y';
            } else {
                echo "fail";
            }
        }catch (Exception $e){
            $this->err_log($e->getMessage(), "xz_sdk_notify");
        }
    }


    public function app_wx_notify($data){
        try{
            if ($data['out_trade_no'] && $data['transaction_id'] && $data['openid']) {
                $order = $this->DAO->get_app_order_info($data['out_trade_no']);
                if(!$order){
                    $this->err_log('失败','appwx_notify');
                    echo "fail";
                }else{
                    if($order['status']!=2){
                        $this->DAO->update_app_order_success($data['out_trade_no'], $data['transaction_id'],  $data['openid'], 1);
                        $this->DAO->update_app_user_info(1,$order);
                        $bo = new baseCore();
                        $bo->send_mail("2874759177@qq.com;3311363532@qq.com;252432349@qq.com;270772735@qq.com;5992025@qq.com",'单号'.$data['out_trade_no']."付款",'网站有人付款，单号'.$data['out_trade_no']."，通道支付宝");
                    }
                    //更新订单为已支付状态
                    echo 'success';
                }
            } else {
                echo "fail";
            }

        }catch (Exception $e){
            $this->err_log($e->getMessage(), "appwx_notify");
        }

    }

    public function nnb_xs_wx_notify($data){
        try{
            if ($data['out_trade_no'] && $data['transaction_id'] && $data['openid']) {
                $order = $this->DAO->get_nnb_order_info($data['out_trade_no']);
                $order['error_desc'] = $order['status'];
                if($order['status']==1){
                    //订单已完成，更新用户email
                    $this->DAO->update_nnb_order_success($data['out_trade_no'], $data['transaction_id'], $data['openid'], 1);
                }elseif($order['status']==0){
                    //更新订单为已支付状态
                    $this->DAO->update_nnb_order_success($data['out_trade_no'], $data['transaction_id'], $data['openid'], 1);
                    $this->add_nnb($order);
                }
                //更新赠送积分
                if($order['integral']){
                    $user_info = $this->DAO->get_user_message($order['buyer_id']);
                    if($user_info){
                        $integral = $user_info['integral']+$order['integral'];
                        $exp = $user_info['exp']+$order['integral'];
                        $this->DAO->update_user_message($order,$integral,$exp);
                    }else{
                        $this->DAO->insert_user_message($order);
                    }
                    $this->DAO->insert_integral_log($order);
                }
                echo 'success';
            } else {
                echo "fail";
            }
        }catch (Exception $e){
            $this->err_log($e->getMessage(), "nnb_xs_wxsdk_notify");
        }
    }

    public function nnb_wx_notify($data){
        try{
            if ($data['out_trade_no'] && $data['transaction_id'] && $data['openid']) {
                $order = $this->DAO->get_nnb_order_info($data['out_trade_no']);
                $order['error_desc'] = $order['status'];
//                $this->err_log(var_export($order,1),'yyq_test');
                if($order['status']==1){
                    //订单已完成，更新用户email
                    $this->DAO->update_nnb_order_success($data['out_trade_no'], $data['transaction_id'], $data['openid'], 1);
                }elseif($order['status']==0){
                    //更新订单为已支付状态
                    $this->DAO->update_nnb_order_success($data['out_trade_no'], $data['transaction_id'], $data['openid'], 1);
                    $this->add_nnb($order);
                }
                echo 'success';
            } else {
                echo "fail";
            }

        }catch (Exception $e){
            $this->err_log($e->getMessage(), "nnb_wxsdk_notify");
        }
    }

    public function hn_ali_notify($status, $order_id, $ali_order_id, $buyer_email){
        try{
            if ($status == 'TRADE_FINISHED' || $status== 'TRADE_SUCCESS') {
                $order = $this->DAO->get_order_info($order_id);
                if($order['status']==2){
                    //订单已完成，更新用户email
                    $this->DAO->update_order_success($order_id, $ali_order_id, $buyer_email, 2);
                }else{
                    //更新订单为已支付状态
                    $this->DAO->update_order_success($order_id, $ali_order_id, $buyer_email, 1);
                }
                echo 'success';
            } else {
                echo "fail";
            }

        }catch (Exception $e){
            $this->err_log($e->getMessage(), "hn_alisdk_notify");
        }
    }

    public function ali_notify($status, $order_id, $ali_order_id, $buyer_email){
        try{
            if ($status == 'TRADE_FINISHED' || $status== 'TRADE_SUCCESS') {
                $order = $this->DAO->get_order_info($order_id);
                if($order['status']==2){
                    //订单已完成，更新用户email
                    $this->DAO->update_order_success($order_id, $ali_order_id, $buyer_email, 2);
                }else{
                    //更新订单为已支付状态
                    $this->DAO->update_order_success($order_id, $ali_order_id, $buyer_email, 1);
                }
                echo 'success';
            } else {
                echo "fail";
            }

        }catch (Exception $e){
            $this->err_log($e->getMessage(), "alisdk_notify");
        }
    }

    public function sdk_ali_notify($status, $order_id, $ali_order_id, $buyer_email,$collect_company = 1){
        try{
            if ($status == 'TRADE_FINISHED' || $status== 'TRADE_SUCCESS') {
                $order = $this->DAO->get_order_info($order_id);
                if($order['status']==2){
                    //订单已完成，更新用户email
                    $this->DAO->update_ali_order_success($order_id, $ali_order_id, $buyer_email, 2,$collect_company);
                }else{
                    //更新订单为已支付状态
                    $this->DAO->update_ali_order_success($order_id, $ali_order_id, $buyer_email, 1,$collect_company);
                }
                echo 'success';
            } else {
                echo "fail";
            }

        }catch (Exception $e){
            $this->err_log($e->getMessage(), "alisdk_notify");
        }
    }


    public function aliapp_notify($status, $order_id, $ali_order_id, $buyer_email){
        try{
            if ($status == 'TRADE_FINISHED' || $status== 'TRADE_SUCCESS') {
                $order = $this->DAO->get_app_order_info($order_id);
                if(!$order){
                    $this->err_log('失败','aliapp_notify');
                    echo "fail";
                }else{
                    if($order['status']!=2){
                        $this->DAO->update_app_order_success($order_id, $ali_order_id, $buyer_email, 1);
                        $this->DAO->update_app_user_info(1,$order);
                        $bo = new baseCore();
                        $bo->send_mail("2874759177@qq.com;3311363532@qq.com;252432349@qq.com;270772735@qq.com;5992025@qq.com",'单号'.$order_id."付款",'网站有人付款，单号'.$order_id."，通道支付宝");
                    }
                    //更新订单为已支付状态
                    echo 'success';
                }
            } else {
                echo "fail";
            }

        }catch (Exception $e){
            $this->err_log($e->getMessage(), "alisdk_notify");
        }
    }

    public function super_ali_notify($status, $order_id, $ali_order_id, $buyer_email){
        try{
            if ($status == 'TRADE_FINISHED' || $status== 'TRADE_SUCCESS') {
                $order = $this->DAO->get_super_order_info($order_id);
                if($order['status']==2){
                    //订单已完成，更新用户email
                    $this->DAO->update_super_order_success($order_id, $ali_order_id, $buyer_email, 2);
                }else{
                    //更新订单为已支付状态
                    $this->DAO->update_super_order_success($order_id, $ali_order_id, $buyer_email, 1);
                }
                echo 'success';
            } else {
                echo "fail";
            }

        }catch (Exception $e){
            $this->err_log($e->getMessage(), "alisdk_notify");
        }
    }

    public function nnb_ali_notify($status, $order_id, $ali_order_id, $buyer_email){
        try{
            if ($status == 'TRADE_FINISHED' || $status== 'TRADE_SUCCESS') {
                $order = $this->DAO->get_nnb_order_info($order_id);
                $order['error_desc'] = $order['status'];
//                $this->err_log(var_export($order,1),'yyq_test');
                if($order['status']==1){
                    //订单已完成，更新用户email
                    $this->DAO->update_nnb_order_success($order_id, $ali_order_id, $buyer_email, 1);
                }elseif($order['status']==0){
                    //更新订单为已支付状态
                    $this->DAO->update_nnb_order_success($order_id, $ali_order_id, $buyer_email, 1);
                    $this->add_nnb($order);
                }
                //更新赠送积分
                if($order['integral']){
                    $user_info = $this->DAO->get_user_message($order['buyer_id']);
                    if($user_info){
                        $integral = $user_info['integral']+$order['integral'];
                        $exp = $user_info['exp']+$order['integral'];
                        $this->DAO->update_user_message($order,$integral,$exp);
                    }else{
                        $this->DAO->insert_user_message($order);
                    }
                    $this->DAO->insert_integral_log($order);
                }
                echo 'success';
            } else {
                echo "fail";
            }

        }catch (Exception $e){
            $this->err_log($e->getMessage(), "nnb_alisdk_notify");
        }
    }

    private function add_nnb($order){
        $order['error_desc']="";
        if(!$order['buyer_id']){
            $order['error_desc']="buyer_id为空";
            $this->err_log(var_export($order,1),'nnb_alisdk_notify');
            die("success");
        }
        if(empty($order['nnb_num']) || $order['nnb_num']< 0){
            $order['error_desc']="牛币数量异常";
            $this->err_log(var_export($order,1),'nnb_alisdk_notify');
            die("success");
        }
        $userDao = new user_dao();
        $user_info = $userDao->get_user_info($order['buyer_id']);
        if(!$user_info && !$user_info['user_id']){
            $order['error_desc']="未查询到用户信息";
            $this->err_log(var_export($order,1),'nnb_alisdk_notify');
            die("success");
        }
        if($user_info['nnb_lock']!='0'){
            $order['error_desc']='牛币状态异常,状态码:'.$user_info['nnb_lock'];
            $this->err_log(var_export($order,1),'nnb_alisdk_notify');
            die("success");
        }
        $userDao->user_lock_nnb(1, $user_info['user_id']);
        $this->DAO->update_nnb_order_info($order);
        $userDao->add_user_nnb($order);
        $userDao->nnb_log($this->create_guid(),$order,1);
        $order['error_desc']='已为'.$order['buyer_id'].'成功添加'.$order['nnb_num'].'牛币';
        $this->err_log(var_export($order,1),'nnb_alisdk_notify');
    }

    //获取厂商订单号
    public function get_game_orderid($app, $header, $order_id, $money){
        $payExpandData = isset($header['payexpanddata'])?$header['payexpanddata']:'';
        $time = strtotime("now");

        $sign_str = $app['app_id']. $header['serv_id']. $header['player_id']. $header['usr_id'].$time. $app['app_key'];
        $sign = md5($sign_str);

        $post_string = "app_id=".$app['app_id']."&serv_id=".$header['serv_id']."&player_id=".$header['player_id']."&usr_id=".$header['usr_id']."&coin=".$money['good_amount'].
            "&money=".$money['good_price']."&good_code=".$money['good_code']."&payExpandData=".$payExpandData."&add_time=".$time."&sign=".urlencode($sign);
        $order_url = $app['sdk_order_url'];
        if(strpos($order_url,"?")){
            $order_url = $order_url."&".$post_string;
        }else{
            $order_url = $order_url."?".$post_string;
        }

        $result = $this->request($order_url,1, array(),5);
        $result = json_decode($result);

        if($result->success==1 || $result->success=="1"){
            return $result->desc;
        }else{
            //$_SESSION['success'] = "[订单号：".$order_id."]<br />订单号创建失败，游戏服务器没有响应。<br /><span class=\"red\">[错误代码：10995]</span>";
            $this->DAO->charge_log($app['id'], $order_id, "单号创建失败",date("Y-m-d H:i:s"),0, $order_url, $sign_str);
            die("0".base64_encode(json_encode("游戏服务器未响应。")));
        }
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

    public function query_orders($order_id){
        $data = array('error' => 1,'status'=>0, "desc" => '参数异常');
        if ($order_id){
            $data['error'] = 0;
            $data['desc']='订单未支付';
            sleep(3);
            $order_info = $this->DAO->get_order_info($order_id);
            if($order_info && $order_info['status'] >=2 ){
                $data['status'] = 1;
                $data['desc']='订单支付完成';
            }
        }
        die("0".base64_encode(json_encode($data)));
    }

}