<?php
header("Content-Type: text/html; charset=utf-8");
require_once '../../../config.php';
COMMON('paramUtils');

$serv_id = paramUtils::strByGET("serv_id",false);
$usr_name = paramUtils::strByGET("usr_name",false);

$play_data = array(
    'err_code'=>0,
    'usr_id'=>71,
    'usr_name'=>$usr_name,
    'player_id'=>71,
    'usr_rank'=>20,
    'app_id'=>1001,
);


echo json_encode($play_data);