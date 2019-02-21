<?php
header("Content-Type: text/html; charset=utf-8");
require_once 'config.php';
COMMON('paramUtils');
BO("game_pay_web");
$bo = new game_pay_web();
$pid = paramUtils::intByGET("game_id");
$ch = paramUtils::intByGET("ch");
if($_GET['out_trade_no']){
    $family = $_GET['family'];
    $cpext = $_GET['cpext'];
    $url = "http://".$_SERVER['HTTP_HOST']."/h5_game.php?game_id=".$pid;
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
if(empty($pid)){
    die("缺少必要参数");
}
$bo->games_view($pid,$ch);