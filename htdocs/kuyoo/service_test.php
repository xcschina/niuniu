<?php
ini_set("display_errors","on");
header("Content-Type: text/html; charset=utf-8");
require_once 'config.php';
COMMON("paramUtils","baseCore");

$bo = new baseCore();
$url = "http://pay.agent.hgcgame.com:10089/?type=usercheck";

$data = array(
    "serv_id"=>57,
    "verify_name"=>base64_encode("sasaki8")
);
echo '开始执行：';
$result = $bo->request($url, $data);
print_r($result);
//$servs = $bo->request($url);
//
//$servs = json_decode($servs,true);
//
//foreach($servs['serv'] as $k=>$s){
//    print_r($s);
//    echo "<hr />";
//}

//5b23697b3cbc422441e2f45bfd224407
//$url = "http://180.150.179.130:10061/?type=recharge";
//$timestamp = strtotime("now");
//$sign = md5("69857".base64_encode("sasaki8")."10100".$timestamp."5b23697b3cbc422441e2f45bfd224407");
//echo "69857".base64_encode("sasaki8")."10100".$timestamp."5b23697b3cbc422441e2f45bfd224407<br />";
//echo $sign."<br />";
//$data = array(
//    "serv_id"=>"57",
//    "buy_user"=>base64_encode("sasaki8"),
//    "num"=>10,
//    "money"=>100,
//    "order_time"=>$timestamp,
//    "sign"=>$sign
//);
//$result = $bo->request($url, $data);
//print_r($result);
//echo "<br />".md5("69857c2FzYWtpOA==1010014393608785b23697b3cbc422441e2f45bfd224407");