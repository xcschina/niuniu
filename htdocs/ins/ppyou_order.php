<?php
/*接口版本：v3*/
header("Content-Type: text/html; charset=utf-8");
require_once 'config.php';
COMMON('paramUtils','baseCore','Security.class');
BO('android_pay');
DAO('android_pay_dao');
$api = new android_pay();
$api->err_log(var_export($_POST,1),'ppyou_order');
$result = array('result' => 0, 'desc' => '网络请求出错。');

$url = 'http://papau.cn:10040/sdkapi/';
$time = date("YmdHis",time());
$extime = date("YmdHis",time()+60*10);
$timestamp = time();
if(empty($_POST)||empty($_POST['game_id'])||empty($_POST['niuorderid'])){
    $result['desc']='缺少必要参数';
    die("0".base64_encode(json_encode($result)));
}
$android_pay_dao = new android_pay_dao();
$channel_info = $android_pay_dao->get_channel_app_by_appid($_POST['game_id']);
if(empty($channel_info)){
    $result['desc']='配置参数异常';
    die("0".base64_encode(json_encode($result)));
}
$order_info = $android_pay_dao->get_super_order_info($_POST['niuorderid']);
if(empty($order_info)){
    $result['desc']='订单信息异常';
    die("0".base64_encode(json_encode($result)));
}
$key = $channel_info['app_key'];
$aeskey = $channel_info['param2'];
$md5key = $channel_info['param1'];
$apikey = $channel_info['app_key'];
$platform = $channel_info['param3'];
$notifyurl="http://callback.66173.cn/ppyou.php?id=70";
$expiretime = $extime;
$submittime = $time;
$subject= $order_info['title'];

$playerid = $order_info['buyer_id'];
$dealprice = $order_info['pay_money'];
$delivertype = "1";
$outorderno = $_POST['niuorderid'];
$totalfee = $order_info['pay_money'];
$gameid = $_POST['game_id'];
$sdktype="1";
$sleve = '1';
$sgameplay = $order_info['serv_name'];
$srole = $order_info['role_name'];
$roleid = $order_info['role_id'];
$serviceid = $order_info['serv_id'];
$levename = "等级";
$sex = "2";
$datasort = array("totalfee"=>$totalfee, "outorderno"=>$outorderno, "submittime"=>$submittime,
    "expiretime"=>$expiretime, "delivertype"=>$delivertype, "dealprice"=>$dealprice,
    "playerid"=>$playerid,"sdktype"=>$sdktype,
    "notifyurl"=>$notifyurl, "subject"=>$subject, "apikey"=>$apikey, "gameid"=>$gameid,
    "sleve"=>$sleve, "sgameplay"=>$sgameplay, "srole"=>$srole, "roleid"=>$roleid,
    "serviceid"=>$serviceid, "levename"=>$levename, "sex"=>$sex);
ksort($datasort);
$signSrc="";
foreach ($datasort as $k=>$v){
    $signSrc .= $v;
}
$signSrc=$signSrc.$md5key;
$sign = strtoupper(md5(urlencode($signSrc)));
$data = json_encode ( array (
    'playerid' => $playerid,
    'apikey' => $apikey,
    'dealprice' => $dealprice,
    'delivertype' => $delivertype,
    'expiretime' => $expiretime,
    'notifyurl' => $notifyurl,
    'outorderno' => $outorderno,
    'subject' =>$subject,
    'submittime' => $submittime,
    'totalfee' => $totalfee,
    'gameid' => $gameid,
    'sdktype'=>$sdktype,
    "sleve"=>$sleve,
    "sgameplay"=>$sgameplay,
    "srole"=>$srole,
    "roleid"=>$roleid,
    "serviceid"=>$serviceid,
    "levename"=>$levename,
    "sex"=>$sex,
    'sign'=>$sign.""
) );
$Security = new Security();
$value = $Security->encrypt($data ,$aeskey);
$param = "platform=".$platform.'&param='.urlencode($value);
$res = $Security->http_post_data( $url.'cpApi/createOrder?'.$param, '' );
if($res[0]==200){
    $request = array(json_decode($res[1]));
    if($request[0]->code == "0"){
        $result['result'] = 1;
        $result['submittime'] = $request[0]->data->submittime;
        $result['orderno'] = $request[0]->data->orderno;
        $result['outorderno'] = $request[0]->data->outorderno;
        $result['amt'] = $request[0]->data->amt;
        $result['productName'] = $request[0]->data->productName;
        $result['desc'] = $request[0]->msg;
    }else{
        $result['desc'] = $request[0]->msg.$request[0]->code;
    }
    die("0".base64_encode(json_encode($result)));
}
die("0".base64_encode(json_encode($result)));