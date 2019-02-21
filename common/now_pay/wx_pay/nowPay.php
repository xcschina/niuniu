<?php
	require_once './config.php';
	require_once './signaTure.php';
	require_once './notify/postRequest.php';
/**
 * 
 * 消费接口
 * 用于对支付信息进行重组和签名，并将请求发往现在支付
 * 
 */	
	if(!empty($_POST['payType'][0])) {
		$payType = $_POST['payType'][0];
	} else {
		die('请选择支付方式');
	}
	//判断支付类型
	switch($payType) {
		case 'weixin':
			$appid = Config::$wxAppId;
			$key = Config::$wxKey;
			$payChannelType = 13;
			break;
		case 'zhifubao':
			$appid = Config::$zfbAppId;
			$key = Config::$zfbKey;
			$payChannelType = 12;
			break;
		case 'yinlian':
			$appid = Config::$ylAppId;
			$key = Config::$ylKey;
			$payChannelType = 20;
			break;			
	}
	//$appid = Config::$zfbAppId;
	//$key = Config::$zfbKey;
	$outputType = '0';
	$consumerCreateIp = '';
	if($_POST['payType'][0] == 'weixin' && $_POST['outputType'][0] == '0') {
		$outputType = '0';
		$consumerCreateIp = getUserIp(); //需上传用户真实ip
	} else if($_POST['payType'][0] == 'weixin' && $_POST['outputType'][0] == '1') {
		$outputType = '2';
	} else if($_POST['payType'][0] == 'zhifubao' && $_POST['outputType'][0] == '0') {
		$outputType = '0';
	} else if($_POST['payType'][0] == 'zhifubao' && $_POST['outputType'][0] == '1') {
		$outputType = '1';
	}

	//获取真实IP，可以根据实际情况自行获取
	function getUserIp() {
		$info = file_get_contents('http://myip.ipip.net');
	 	$ipstr = explode('：',$info);
	 	$ip = explode(' ', $ipstr[1]);
	 	return $ip[0];
	}

	$req=array();
	$req["appId"]             = $appid;
	$req["deviceType"]        = Config::TRADE_DEVICE_TYPE;
	$req["frontNotifyUrl"]    = Config::$front_notify_url;
	$req["funcode"]           = Config::TRADE_FUNCODE;
	$req["mhtCharset"]        = Config::TRADE_CHARSET;
	$req["mhtCurrencyType"]   = Config::TRADE_CURRENCYTYPE;
	$req["mhtOrderAmt"]       = $_POST["mhtOrderAmt"];
	$req["mhtOrderDetail"]    = $_POST["mhtOrderDetail"];
	$req["mhtOrderName"]      = $_POST["mhtOrderName"];
	$req["mhtOrderNo"]        = date("YmdHis").rand(10000,99999);
	$req["mhtOrderStartTime"] = date("YmdHis");
    $req["mhtOrderTimeOut"]   = Config::$trade_time_out;
    $req["mhtOrderType"]      = Config::TRADE_TYPE;
	$req["mhtReserved"]       = "test";
    $req["mhtSignType"]       = Config::TRADE_SIGN_TYPE;
	$req["notifyUrl"]         = Config::$back_notify_url;
	$req["outputType"]        = $outputType;//   0 默认值    // 2  微信deeplink模式
	$req["payChannelType"]    = $payChannelType; //12 支付宝  //13 微信 //20 银联  //25  手Q
	$req["version"]           = "1.0.0";
    $req["consumerCreateIp"]  = $consumerCreateIp; //微信必填// outputType=2时 无须上送该值

	$info = new SignaTure;
	$req_str = $info -> getToStr($req, $key);

	if($_POST['payType'][0] == 'weixin' && $_POST['outputType'][0] == '0') {
		header("location:".Config::TRADE_URL."?".$req_str);
		die();
	}
	$post = new PostRequest;
	$res = $post -> post(Config::TRADE_URL, $req_str);

	$code = (bool)stripos($res, '&tn=');
	if($code) {
		$arr = explode('&', $res);
		$gettn = '';
		foreach($arr as $v) {
			$tn = explode('=', $v);
			if($tn[0] == 'tn'){
				$gettn = $tn[1];
			} 
		}
		echo "请点击链接进行支付：<a href='". urldecode($gettn) ."'>点我支付</a>";
	} else {
		echo $res;
	}
