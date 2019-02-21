<?php
header("Content-Type: text/html; charset=utf-8");
require_once 'config.php';
COMMON('paramUtils');
BO("game_pay_web");
$game_id = paramUtils::intByGET("game_id");
$ch = paramUtils::strByGET("ch");
$type = paramUtils::strByGET("t");

if($_GET['out_trade_no']){
    $family = $_GET['family'];
    $cpext = $_GET['cpext'];
    $url = "http://".$_SERVER['HTTP_HOST']."/h5_game.php?game_id=".$game_id;
    if($ch){
        $url = $url."&ch=".$ch;
    }
    if($family){
        $url = $url."&family=".$family;
    }
    if($cpext){
        $url = $url."&cpext=".$cpext;
    }
    header("Location: $url");
}
$bo = new game_pay_web();

if(strpos($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger') === false){
    $bo->games_view($game_id,$ch);
}else{
    if($_SESSION['weixin_code']){
        if(!$_SESSION['wx_openid']){
            $bo->get_auth_user_token($game_id);
            exit();
        }
        $bo->games_view($game_id,$ch);
    }else{
        $bo->do_weixin_login($game_id,$_GET);
    }
}

