<?php
header("Content-Type: text/html; charset=utf-8");
require_once 'config.php';
COMMON("paramUtils");
BO('game_mobile');
$id = paramUtils::intByGET("id",false);
$bo = new game_mobile();
if(isset($_GET['act'])){
    $act = paramUtils::strByGET("act",false);
    //if($bo->client_ip()=='27.155.145.206' || $bo->client_ip()=='218.104.231.54' || $bo->client_ip()=='27.156.114.183'){
        //$bo->open_debug();
        //if($bo->client_ip()=='218.104.231.54'){
        if (strtoupper($_SERVER['REQUEST_METHOD']) !== 'POST') {
            switch ($act) {
                case'character':
                    $bo->character_buy($id);
                    break;
                case'iap':
                    $bo->iap_buy($id);
                    break;
                case'renew':
                    $bo->renew_view($id);
                    break;
                case'recharge':
                    $bo->recharge_buy($id);
                    break;
                case'topup':
                    $bo->topup_buy($id);
                    break;
                case'account':
                    $bo->other_buy_list($id,'account');
                    break;
                case'coin':
                    $bo->other_buy_list($id,'coin');
                    break;
                case'props':
                    $bo->other_buy_list($id,'props');
                    break;
                case'gameuser':
                    $bo->game_check_user_view($id);
                    break;
                case'info':
                    $bo->game_articles($id);
                    break;
                default:
                    $bo->game_cate_view($id);
                    break;
            }
        }else{
            switch ($act) {
                case'gameuser':
                    $game_user = paramUtils::strByPOST("game_user",false);
                    $pagehash = paramUtils::strByPOST("pagehash",false);
                    $bo->game_check_user($id, $game_user, $pagehash);
                    break;
                default:
                    $bo->game_cate_view($id);
                    break;
            }
        }
//    }else{
//        $bo->game_cate_view($id);
//    }
}else{
    $bo->game_view($id);
}