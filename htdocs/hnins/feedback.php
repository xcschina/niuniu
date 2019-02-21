<?php
header("Content-Type: text/html; charset=utf-8");
require_once 'config.php';
COMMON('paramUtils');
BO('feedback');
ini_set("display_errors","On");

$bo = new feedback();
$act = paramUtils::strByGET('act');

$appid = 1000;
$user_id = 0;
$language = 'en';

if(isset($_SERVER['HTTP_USER_AGENT1'])){
    $header = base64_decode(substr($_SERVER['HTTP_USER_AGENT1'],1));
    $header = explode("&",$header);
    foreach($header as $k=>$param){
        $param = explode("=",$param);

        //应用id
        if($param[0] == 'app_id'){
            $params['appid'] = $appid = $param[1];
        }
        //用户id
        if($param[0] == 'user_id'){
            $params['usr_id'] = $user_id = $param[1];
        }
        //系统固件版本
        if($param[0] == 'osver'){
            $params['osver'] = $osver = $param[1];
        }
        //游戏版本
        if($param[0] == 'ver'){
            $params['gamever'] = $gamever = $param[1];
        }

        //游戏网络
        if($param[0] == 'net'){
            $params['net'] = $net = $param[1];
        }
        //所用设备
        if($param[0] == 'mtype'){
            $params['mtype'] = $mtype = $param[1];
        }
        //系统名
        if($param[0] == 'dt'){
            $params['osname'] = $osname = $param[1];
        }
        //登录类型
        if($param[0] == 'mode'){
            $params['logintype'] = $logintype = $param[1];
        }

        if($param[0] == 'sdktype'){
            $params['sdktype'] = $sdktype = $param[1];
        }
        //设备标识
        if($param[0] == 'sid'){
            $params['sid'] = $uuid = $param[1];
        }
        //sdk版本
        $sdkver = '';                   //有的版本没有sdk头选项，防止出错
        if($param[0] == 'sdkver'){
            $params['sdkver'] = $sdkver = $param[1];
        }
        if($param[0] == 'ch-lang' && $param[1]){
            $params['language'] = $language = $param[1];
        }
        $bo->set_usr_session("user",$params);
    }
}
switch ($act){
    case 'problem_feedback':
        $bo->problem_feedback($osname);
        break;
    case 'account_retrieve':
        $bo->account_retrieve($osname, $uuid, $appid);
        break;
    case 'account_submit':
        $bo->account_submit();
        break;
    case 'problem_submit':
        $bo->problem_submit();
        break;
    case 'load_more_record':
        $bo->load_more_record(paramUtils::intByPOST('appid'), paramUtils::intByPOST('user_id'),paramUtils::intByPOST('page'), paramUtils::intByPOST('num') ? paramUtils::intByPOST('num') : 5);
        break;
    case 'question_detail':
        $bo->question_detail(paramUtils::intByGET('id'), paramUtils::intByGET('read_status'));
        break;

    //SDK客服系统改版
    case"more_record":
        $bo->more_record();
        break;
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
    default :
        if(!$user_id){
            $data = $bo->get_usr_session('user');
            if(!$data){
                $bo->show_error("无法获取用户信息,请重新登录");
            }else{
                $bo->view($data['appid'], $data['usr_id'], $data['osver'],$data['gamever'],$data['net'],$data['mtype'],$data['osname'],$data['logintype'],$data['sdkver']);
            }
        }else{
            $bo->view($appid, $user_id, $osver, $gamever, $net,$mtype, $osname, $logintype,$sdkver);
        }
        break;
}