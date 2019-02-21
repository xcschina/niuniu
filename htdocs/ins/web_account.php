<?php
/*接口版本：v3*/
header("Content-Type: text/html; charset=utf-8");
require_once 'config.php';
COMMON('paramUtils');
BO('web_account');

$bo = new web_account();
$act = paramUtils::strByGET("act");
$params = array('appid'=>0, 'user_id'=>'', 'channel'=>'');
if(isset($_SERVER['HTTP_USER_AGENT1'])){
    $header = base64_decode(substr($_SERVER['HTTP_USER_AGENT1'],1));
    $header = explode("&",$header);
    foreach($header as $k=>$param){
        $param = explode("=",$param);
        switch($param[0]) {
            case "user_id":
                $params['user_id'] = $user_id = $param[1] ;
                break;
            case "app_id":
                $params['appid'] = $param[1] ;
                break;
            case "orientation":
                $params['orientation'] = $param[1];
                break;
            case "sid":
                $params['sid'] = $param[1];
                break;
        }
    }
    $bo->set_usr_session("USR_NEWS_HEADER", $params);
}
switch ($act) {
    case 'bill':
        $bo->account_bill($params['user_id']);
        break;
    case 'bill_more':
        $bo->bill_more();
        break;
    case 'center':
        $bo->account_center($params['user_id']);
        break;
    case 'change_password':
        $bo->account_change_password();
        break;
    case 'niubi':
        $bo->account_niubi($params['user_id']);
        break;
    case 'niubi_more':
        $bo->niubi_more();
        break;
    case 'niudian':
        $bo->account_niudian($params['user_id']);
        break;
    case 'niudian_more':
        $bo->niudian_more();
        break;
    case 'phone_bind':
        $bo->account_phone_bind();
        break;
    case 'phone_bind_change':
        $bo->account_phone_bind_change();
        break;
    case 'real_verify':
        $bo->account_real_verify($params['user_id']);
        break;
    case'verify_submit':
        $bo->verify_submit();
        break;
    case'sms_code':
        $bo->sms_code();
        break;
    case'verify_old_idcard':
        $bo->verify_old_idcard();
        break;
    case'bind_new_idcard':
        $bo->bind_new_idcard();
        break;
    default:
        $bo->account_center($params['user_id']);
        break;
}