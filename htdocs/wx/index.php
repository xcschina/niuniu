<?php
//header("Content-Type: text/html; charset=utf-8");
require_once 'config.php';
ini_set("display_errors","on");
COMMON('paramUtils','wechat.class');
BO("article_wx");
$bo = new article_wx();
$wechat = new Weixin(WX_TOKEN);
$wechat->getMsg();
$type = $wechat->msgtype; //消息类型
$openid = $wechat->msg['FromUserName'];
$key = $wechat->msg['EventKey'];
$content = $wechat->msg['Content'];
$event = $wechat->msg['Event'];
try{
    if($type=='text'){
        if($content=='yyq'){
            $msg = $wechat->makeText('您好，点击<a href="https://open.weixin.qq.com/connect/oauth2/authorize?appid=wxbaed68c7f2f3a62c&redirect_uri=http%3a%2f%2fwx.66173.cn%2fgames.php%3fgame_id%3d1234567%26ch%3d2&response_type=code&scope=snsapi_userinfo&state=STATE#wechat_redirect">【福建公众号】</a>领取最新游戏礼包！ 点击<a href="https://open.weixin.qq.com/connect/oauth2/authorize?appid=wxbaed68c7f2f3a62c&redirect_uri=http%3a%2f%2fwx.66173.cn%2fgame.php%3fgame_id%3d1094001%26t%3d1&response_type=code&scope=snsapi_userinfo&state=STATE#wechat_redirect">【海南公众号】</a>购买热门游戏商品，更多功能尽情期待！');
            die($msg);
        }
        $content = str_replace("礼包","", $content);
        if($content){
            $games = $bo->search_game($content);
            if(!$games){
                //weixin_reply
                $data = $bo->get_setting();
                $msg = $wechat->makeText(htmlspecialchars_decode($data['weixin_reply']));
            }else{
                $msg = $wechat->makeText($games);
            }
        }else{
            $msg = $wechat->makeText('您好，点击<a href="https://open.weixin.qq.com/connect/oauth2/authorize?appid=wxbaed68c7f2f3a62c&redirect_uri=http%3A%2F%2Fwx.66173.cn%2Fgifts.php&response_type=code&scope=snsapi_userinfo&state=STATE#wechat_redirect">【游戏礼包】</a>领取最新游戏礼包！ 点击<a href="http://m.66173.cn/">【我要买】</a>购买热门游戏商品，更多功能尽情期待！');
        }
    }elseif($type=='event'){
        if($event=='subscribe'){
            $data = $bo->get_setting();
            $msg = $wechat->makeText(htmlspecialchars_decode($data['weixin_welcome']));
        }elseif($key=='service'){
            $msg = $wechat->makeText("您好！\r\n客服热线：0591-87766173\r\n客服QQ：3311363532\r\n客服QQ：270772735\r\n客服QQ：3141424712");
        }else{
            $msg = $wechat->makeText("感谢您的关注，后会有期");
        }
    }
    die($msg);
}catch (Exception $e){
    $bo->err_log("接口".var_export($e,1)."<hr />",'wx-error');
}