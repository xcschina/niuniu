<?php
COMMON('baseCore','now_pay/Signature','now_pay/conf/Config');
class wx_demo extends baseCore{
    public function __construct(){
        parent::__construct();
    }

    public function index(){
        $this->wx_pay($_REQUEST);

    }

    public function wx_pay($params){
        $req = array();
        $req["appId"]             = Config::$wxAppId;
        $req["deviceType"]        = Config::TRADE_DEVICE_TYPE;
        $req["frontNotifyUrl"]    = Config::$front_notify_url;
        $req["funcode"]           = Config::TRADE_FUNCODE;
        $req["mhtCharset"]        = Config::CHARSET;
        $req["mhtCurrencyType"]   = Config::TRADE_CURRENCYTYPE;
        $req["mhtOrderAmt"]       = $params['mhtOrderAmt'];
        $req["mhtOrderDetail"]    = $params['mhtOrderDetail'];
        $req["mhtOrderName"]      = $params['mhtOrderName'];
        $req["mhtOrderNo"]        = date("YmdHis").rand(10000,99999);
        $req["mhtOrderStartTime"] = date("YmdHis");
        $req["mhtOrderTimeOut"]   = Config::$trade_time_out;
        $req["mhtOrderType"]      = Config::TRADE_TYPE;
        $req["mhtSignType"]       = Config::TRADE_SIGN_TYPE;
        $req["notifyUrl"]         = Config::$back_notify_url;
        $req["outputType"]        = '2';//   0 默认值    // 2  微信deeplink模式
        $req["payChannelType"]    = 13; //12 支付宝  //13 微信 //20 银联  //25  手Q
        $req["version"]           = "1.0.0";
//        $req["consumerCreateIp"]  = $this->client_ip(); //微信必填// outputType=2时 无须上送该值
        $sign = new Signature();
        $req_str = $sign -> getToStr($req, Config::$wxKey);
        $res = $this -> request(Config::TRADE_URL, $req_str);
        $code = (bool)stripos($res, '&tn=');
        if($code){
            $arr = explode('&', $res);
            $get_tn = '';
            foreach($arr as $v) {
                $tn = explode('=', $v);
                if($tn[0] == 'tn'){
                    $get_tn = $tn[1];
                }
            }
//            header('location:'.urldecode($get_tn));
            echo "请点击链接进行支付：<a href='". urldecode($get_tn) ."'>点我支付</a>";
        }else{
            echo $res;
        }
    }
}