<?php
header("Content-Type: text/html; charset=utf-8");
require_once 'config.php';
//ini_set("display_errors","Off");
BO("website_mobile");
COMMON('paramUtils');
$bo = new website_mobile();
$act = paramUtils::strByGET("act",false);
$act_id = paramUtils::strByGET("act_id");
$code = paramUtils::strByGET("code");
$game_id = 1818;
$batch_id = 423;
switch ($act) {
    case'dsgl':
        $bo->dsgl();
        break;
    case'my_gift':
        $bo->my_gift();
        break;
    case'do_login':
        $bo->do_login();
        break;
    //验证码
    case'sms_code':
        $bo->sms_code();
        break;
    case'receive_gift':
        $bo->receive_gift();
        break;
    case'do_logout':
        $bo->do_logout();
        break;
    case'login_control':
        $bo->login_control();
        break;
    case'activity':
        $bo->activity($act_id,$code,$game_id,$batch_id);
        break;
    case'draw_gift':
        $bo->draw_gift($game_id,$batch_id);
        break;
    case'user_gift':
        $bo->user_gift($game_id);
        break;
    case'login':
        $bo->login();
        break;
    case'logout':
        $bo->logout($game_id);
        break;
    case'send_code':
        $bo->send_code();
        break;
    case'yhwz':
        $bo->yhwz();
        break;
    case'reserve_gift':
        $bo->reserve_gift();
        break;
    case'article_detail':
        $id = paramUtils::intByGET("id",false);
        $bo->article_detail($id);
        break;
    case 'jyjy':
        $bo->jyjy($act_id,$code);
        break;
    case 'draw_gift_secret':
        $bo->draw_gift_secret();
        break;
    case 'login_secret':
        $bo->login_secret();
        break;
    case 'sms_code_secret':
        $bo->sms_code_secret();
        break;
    case 'app_promote':
        $bo->app_promote();
        break;
    case 'gift':
        $bo->get_gift();
        break;

}