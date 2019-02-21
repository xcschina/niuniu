<?php
header("Content-Type: text/html; charset=utf-8");
require_once 'config.php';
COMMON('paramUtils');
BO("game_pay_web");
$game_id = paramUtils::intByGET("game_id");
$ch = paramUtils::strByGET("ch");
$type = paramUtils::strByGET("t");

if(strpos($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger') === false){
    // 非微信浏览器禁止浏览
    $bo = new game_pay_web();
    $bo->games_view($game_id,$ch);
}else{
    header('HTTP/1.1 301 Moved Permanently');
    // 微信浏览器，允许访问
    $url = "http://wx.yun273.com/games.php?game_id=".$game_id."&ch=".$ch."&t=".$type;
    if(!empty($type)){
        $url = "https://open.weixin.qq.com/connect/oauth2/authorize?appid=wx145b6dcbc2f651b5&redirect_uri=".urlencode($url)."&response_type=code&scope=snsapi_userinfo&state=STATE#wechat_redirect";
    }else{
        $url = "https://open.weixin.qq.com/connect/oauth2/authorize?appid=wx145b6dcbc2f651b5&redirect_uri=".urlencode($url)."&response_type=code&scope=snsapi_base&state=STATE#wechat_redirect";
    }
    header('Location: '.$url);
}
