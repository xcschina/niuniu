<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept,User-Agent1');
header("Content-Type: text/html; charset=utf-8");
require_once 'config.php';
COMMON('paramUtils');
BO("game_super_web");
$bo = new game_super_web();
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
//    case 'logout'://退出
//        $bo->log_out();
//        break;
    default;
        die(json_encode(array("result"=>0,"desc"=>"接口异常")));
        break;
}
