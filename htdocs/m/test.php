<?php
header("Content-Type: text/html; charset=utf-8");
require_once 'config.php';
ini_set("display_errors","On");
error_reporting(E_ALL);
BO("account_mobile");
COMMON('paramUtils');
die();
$bo = new account_mobile();
$url = 'http://sms.bamikeji.com:8890/mtPort/mt/bindip/send';
$uid = '2257';
$passwd = '13075868963';
$phonelist = '18558789300';//多个号码用英文半角逗号分隔
$content = '您的验证码是:66173822'.time().' 【精品游戏】';// 内容+【签名】
$url = $url."?uid=".$uid."&passwd=".md5($passwd)."&phonelist=".$phonelist."&content=".urlencode($content);
//$html = file_get_contents($url);
//echo $html;

$request = $bo->request($url);
var_dump($request);