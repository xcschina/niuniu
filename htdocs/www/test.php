<?php
ini_set("display_errors","on");
//header("Content-Type: text/html; charset=utf-8");
require_once 'config.php';
COMMON("baseCore");
$time = strtotime("2015-10-10");
$time2 = $time+7*24*3600;
echo $time;
echo "<br />".date("Y-m-d H:i:s", $time2);
//
//$data = array(
//    'outerCustomer'=> '278747243',
//    'extendJsonParams'=> '{"buyerAccount":"0","buyerPassword":"123123","gameVersion":""}',
//    'gameId'=> 'A1575',
//     'buyerQQ'=> '79713910',
//     'outerOrderId'=> '820151026124500373079',
//     'userId'=> '117199584',
//     'buyerPhone'=> '18567911801',
//     'platform'=> '1771',
//     'goodsId'=> '126002869',
//     'goodsStock'=> '60',
//     'roleLevel'=> '123',
//     'roleName'=> 'abcdef',
//     'serverId'=> 'A1575P001182',
//     'goodsPrice'=> '6.0',
//     'groupId'=> 'A1575P001',
//     'sign'=> '27f556fefc7a26da98fe379e9279782d'
//);
//$bo = new baseCore();
//$bo->is_request_debug = 1;
//$url = "http://open.1771.com/liebaob2bcreate.action";
//$result = $bo->request($url, $data);
//print_r($result);