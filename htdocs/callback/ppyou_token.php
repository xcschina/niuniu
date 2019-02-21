<?php
header("Content-Type: text/html; charset=utf-8");
require_once 'config.php';
COMMON('paramUtils','baseCore','QuickEncrypt','Security.class');
DAO('callback_dao');
$bo = new baseCore();
$token = paramUtils::strByPOST("token");
$gameid = paramUtils::strByPOST("gameid");
$result = array('result'=>-1,'desc'=>'网络请求出错');
if(empty($gameid) || empty($token)){
    $result['desc']='缺少必要参数';
    die(json_encode($result));
}
$callback_dao = new callback_dao();
$channel_info = $callback_dao->get_channel_app_by_appid($gameid);
if(empty($channel_info)){
    $result['desc']='参数异常';
    die(json_encode($result));
}
$token_info = explode("[memberNo]",$token);
$key = $channel_info['app_key'];
$aeskey = $channel_info['param2'];
$md5key = $channel_info['param1'];
$userno = $token_info[1];
$token = $token_info[0];
$apikey = $channel_info['app_key'];
$platform = $channel_info['param3'];
$url = 'http://papau.cn:10040/sdkapi/';
$datasort = array("userno"=>$userno, "token"=>$token, "apikey"=>$apikey);
ksort($datasort);
$signSrc="";
foreach ($datasort as $k=>$v){
    $signSrc .= $v;
}

$signSrc=$signSrc.$md5key;
$sign = strtoupper(md5(urlencode($signSrc)));
$data = json_encode ( array (
    'userno' => $userno,
    'token' => $token,
    'apikey' => $apikey,
    'sign'=>$sign.""
) );
$Security = new Security();
$value = $Security->encrypt($data ,$aeskey);
$param="platform=".$platform.'&param='.urlencode($value);
$res =$Security->http_post_data( $url . 'cpApi/secondCheckMember?'.$param, '' );
if($res[0]==200){
    $request = array(json_decode($res[1]));
    $result['result'] = $request[0]->code;
    $result['desc'] = $request[0]->msg;
    die(json_encode($result));
}
die(json_encode($result));