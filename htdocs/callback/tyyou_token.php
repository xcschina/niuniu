<?php
header("Content-Type: text/html; charset=utf-8");
require_once 'config.php';
COMMON('paramUtils','baseCore','QuickEncrypt');
DAO('callback_dao');
$bo = new baseCore();
$data['app_id'] = paramUtils::strByPOST("app_id");
$data['mem_id'] = paramUtils::strByPOST("mem_id");
$data['user_token'] = paramUtils::strByPOST("user_token");
$data['sign'] = paramUtils::strByPOST("sign");

$params = json_encode($data);
$url = "http://api.tianyuyou.com/notice/gamecp/checkusertoken.php";
$rdata = http_post_data($url, $params);
$rdata = stripslashes((string)$rdata);
die($rdata);


function http_post_data($url, $data_string) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json; charset=utf-8',
            'Content-Length: ' .strlen($data_string))
    );
    ob_start();
    curl_exec($ch);
    $return_content = ob_get_contents();
    ob_end_clean();
    return $return_content;
}