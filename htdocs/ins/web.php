<?php
/*接口版本：v3*/
header("Content-Type: text/html; charset=utf-8");
require_once 'config.php';
COMMON('paramUtils');
BO('web_admin');

$bo = new web_admin();
$act = paramUtils::strByGET("act");
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
        //系统固件版本
        if($param[0] == 'osver'){
            $params['osver'] = $param[1];
        }
        //游戏版本
        if($param[0] == 'ver'){
            $params['gamever'] = $param[1];
        }
        //游戏网络
        if($param[0] == 'net'){
            $params['net'] = $param[1];
        }
        //所用设备
        if($param[0] == 'mtype'){
            $params['mtype'] = $param[1];
        }
        //登录类型
        if($param[0] == 'mode'){
            $params['logintype'] = $param[1];
        }

        if($param[0] == 'sdktype'){
            $params['sdktype'] = $param[1];
        }
        //系统名
        if($param[0] == 'dt'){
            $params['osname'] = $param[1];
        }
        //设备标识
        if($param[0] == 'sid'){
            $params['uuid'] = $param[1];
        }
        //渠道ID
        if($param[0] == 'channel'){
            $params['channel'] = $param[1];
        }
        if($param[0] == 'orientation'){
            $params['orientation'] = $param[1];
        }
    }
    $bo->set_usr_session("USR_NEWS_HEADER", $params);
}
switch ($act) {
    case 'message_center':
        $bo->message_center($params['appid'],$params['user_id'],$params['channel']);
        break;
    case 'more':
        $bo->message_more();
        break;
    case 'message_detail':
        $id = paramUtils::strByGET("id",false);
        $bo->message_detail($params['appid'],$params['user_id'],$id);
        break;
    case 'account_find':
        $bo->service_account_find($params['osname'], $params['uuid'], $params['appid']);
        break;
    case 'account_submit':
        $bo->account_submit();
        break;
    case 'service_center':
        if(!$params['user_id']){
            $data = $bo->get_usr_session('USR_NEWS_HEADER');
            if(!$data){
                $bo->show_error("无法获取用户信息,请重新登录");
            }else{
                $bo->service_center($data['appid'], $data['user_id']);
            }
        }else{
            $bo->service_center($params['appid'], $params['user_id']);
        }
        break;
    case 'load_more_record':
        $bo->load_more_record();
        break;
    case 'service_detail':
        $bo->service_detail(paramUtils::intByGET('id'), paramUtils::intByGET('read_status'));
        break;
    case 'work_order':
        $bo->service_work_order($params['osname']);
        break;
    case'problem_submit':
        $bo->problem_submit();
        break;

    //SDK客服系统改版
    case'reply_question':
        $bo->reply_question();
        break;
    case"common_list":
        $bo->common_list();
        break;
    case"common_more":
        $bo->common_more();
        break;
    case"common_detail":
        $id = paramUtils::strByGET("id",false);
        $bo->common_detail($id);
        break;
    case"record_list":
        $bo->service_record_list();
        break;
}