<?php
/*接口版本：v3*/
header('Content-Type: application/json; charset=utf-8');
require_once 'config.php';
COMMON('paramUtils');
BO('android_pay');
//ini_set("display_errors","On");
//error_reporting(E_ALL);
$para = array('tokenkey' => '');
$result = array('result' => 0, 'desc' => '网络请求出错。');
$api = new android_pay();
if (isset($_SERVER['HTTP_USER_AGENT1'])) {
    $header = base64_decode(substr($_SERVER['HTTP_USER_AGENT1'], 1));
    $header = explode("&", $header);
    foreach ($header as $k => $params) {
        $params = explode("=", $params);
        if ($params[0] == 'app_id') {
            $appid = $params[1];
        }
    }
}
if ($appid == '7021') {
    $ch_id = 68;
}
$UA = $api->super_check_user_agent(paramUtils::strByPOST('x7_token'), $appid);
$ch = '66173';
foreach ($UA as $k => $param) {
    $param = explode("=", $param);
    switch ($param[0]) {
        case "tokenkey":
            $para['tokenkey'] = $param[1];
            break;
    }
}
$api->err_log(var_export($UA, 1), 'x7sy_token');

$para['tokenkey'] = $param[0];
if (empty($para['tokenkey']) || empty($ch_id)) {
    $result['desc'] = '缺少必要参数';
    die("0" . base64_encode(json_encode($result)));
}
$apiDao = new android_pay_dao();
$ch_info = $apiDao->get_super_ch_info($ch_id);
$para['sign'] = md5($ch_info['app_key'].$para['tokenkey']);
$url = 'https://api.x7sy.com/user/check_login?tokenkey='.$para['tokenkey'].'&sign='.$para['sgin'];

$post_data = array(
    'tokenkey' => $para['tokenkey'],
    'sign' => $para['sign']
);
$data = $api->request($url, $post_data);
$data = json_decode($data);
if ($data->errorno == 0) {
    $result['result'] = 1;
    $result['desc'] = '请求成功';
    $result['guid'] = $data->data->guid;
    $result['username'] = $data->data->username;
    die("0" . base64_encode(json_encode($result)));
} else {
    $result['desc'] = $data->errormsg;
}
die("0" . base64_encode(json_encode($result)));