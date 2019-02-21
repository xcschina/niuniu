<?php
header("Content-Type: text/html; charset=utf-8");
require_once 'config.php';
COMMON('paramUtils');
BO('play_ground');
ini_set("display_errors","On");

$bo = new play_ground();
$act = paramUtils::strByGET('act');
if(isset($_SERVER['HTTP_USER_AGENT1'])){
    $header = base64_decode(substr($_SERVER['HTTP_USER_AGENT1'],1));
    $header = explode("&",$header);
    foreach($header as $k=>$param){
        $param = explode("=",$param);
        //用户id
        if($param[0] == 'user_id' || $param[0] == 'userId'){
            $params['user_id'] = $user_id = $param[1];
        }
        //系统固件版本
        if($param[0] == 'osver'){
            $params['osver'] = $osver = $param[1];
        }
        //游戏版本
        if($param[0] == 'ver'){
            $params['gamever'] = $gamever = $param[1];
        }
        //系统名
        if($param[0] == 'dt'){
            $params['osname'] = $osname = $param[1];
        }
        //设备标识
        if($param[0] == 'sid'){
            $params['sid'] = $uuid = $param[1];
        }
        //登录类型
        if($param[0] == 'mode'){
            $params['logintype'] = $logintype = $param[1];
        }
        //游戏ID
        if($param[0] == 'appId'){
            $params['app_id'] = $app_id = $param[1];
        }
        $bo->set_usr_session("user",$params);
    }
}
switch ($act){
    case 'shop_list':
        $bo->shop_list();
        break;
    case 'server_list':
        $bo->server_list();
        break;
    case 'exchange':
        $bo->exchange();
        break;
    case 'draw_log':
        $bo->draw_log();
        break;
    //cp积分获得消耗接口
    case 'cp_integral':
        $bo->cp_integral();
        break;
    case 'get_money_integral':
        $bo->get_money_integral();
        break;
    default:
        $app_id = paramUtils::intByGET('appId');
        $bo->index($app_id);
        break;
}