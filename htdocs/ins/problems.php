<?php
/*接口版本：v3*/
header("Content-Type: text/html; charset=utf-8");
require_once 'config.php';
COMMON('paramUtils');
BO('validate_web');
//ini_set("display_errors","On");
//error_reporting(E_ALL);
$validate = new validate_web();

$data = array(
    'app_id' => '',
    'user_id' => '',
    'role_id' => '',
    'role_name' => '',
    'role_level' => '',
    'area_server_id' => '',
    'area_server_name' => '',
);

if(isset($_SERVER['HTTP_USER_AGENT1'])){
    $header = base64_decode(substr($_SERVER['HTTP_USER_AGENT1'],1));
    $header = explode("&",$header);
    foreach($header as $k=>$param){
        $param = explode("=",$param);
        if($param[0] == 'app_id'){
            $data['app_id'] = $param[1];
        }
        if($param[0] == 'user_id'){
            $data['user_id'] = $param[1];
        }
        if($param[0] == 'role_id'){
            $data['role_id'] = $param[1];
        }
        if($param[0] == 'role_name'){
            $data['role_name'] = $param[1];
        }
        if($param[0] == 'role_level'){
            $data['role_level'] = $param[1];
        }
        if($param[0] == 'area_server_id'){
            $data['area_server_id'] = $param[1];
        }
        if($param[0] == 'area_server_name'){
            $data['area_server_name'] = $param[1];
        }
        if($param[0] == 'timestamp'){
            $data['timestamp'] = $param[1];
        }
    }
}
$validate->err_log($validate->client_ip()."\r".var_export($data,1),'problems');

$act  = paramUtils::strByGET("act",false);
switch ($act){
    case 'problems':
        $validate->get_problems($data);
        break;
    case 'answer':
        $validate->get_answer($data);
        break;
}
