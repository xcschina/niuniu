<?php
/*接口版本：v3*/
header("Content-Type: text/html; charset=utf-8");
require_once 'config.php';
ini_set("display_errors","on");
COMMON('paramUtils');
BO('android_pay_web');
$bo = new news_web();
$params = array('appid'=>0, 'user_id'=>'', 'channel'=>'');
if(isset($_SERVER['HTTP_USER_AGENT1'])){
    $header = base64_decode(substr($_SERVER['HTTP_USER_AGENT1'],1));
    $header = explode("&",$header);
    foreach($header as $k=>$param){
        $param = explode("=",$param);
        //游戏版本
        if($param[0] == 'ver'){
            $params['gamever'] = $param[1];
        }
        //应用id
        if($param[0] == 'app_id'){
            $params['appid'] = $param[1];
        }
        //用户id
        if($param[0] == 'user_id'){
            $params['user_id'] = $param[1];
        }
        //渠道ID
        if($param[0] == 'channel'){
            $params['channel'] = $param[1];
        }
    }
    $bo->set_usr_session("USR_NEWS_HEADER", $params);
}

$act = paramUtils::strByGET("act",false);
switch ($act) {
    case "status":
        $bo->news_status($params['appid'],$params['user_id'],$params['channel']);
        break;
    case 'list':
        $bo->news_list($params['appid'],$params['user_id'],$params['channel']);
        break;
    case 'more':
        $bo->news_more();
        break;
    case 'info':
        $id = paramUtils::strByGET("id",false);
        $bo->news_info($params['appid'],$params['user_id'],$params['channel'],$id);
        break;
}