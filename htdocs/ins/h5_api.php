<?php
header("Content-Type: text/html; charset=utf-8");
require_once 'config.php';
COMMON('paramUtils');
BO("game_pay_web");
$bo = new game_pay_web();

$act = paramUtils::strByGET("act",false);
switch ($act) {
    case "role":
        $bo->add_role();
        break;
    case "device":
        $bo->add_device();
        break;
    case "login":
        $bo->add_login();
        break;
    case 'bind':
        $bo->bind_mobile();
        break;
    case 'check'://手机验证
        $bo->check_mobile();
        break;
    default;
        die('0'.base64_encode(json_encode(array("result"=>0,"desc"=>"找不到服务器"))));
        break;
}
