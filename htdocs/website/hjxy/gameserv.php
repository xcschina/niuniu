<?php
header("Content-Type: application/json; charset=utf-8");
require_once 'config.php';
COMMON('paramUtils');
$serv_id = paramUtils::strByGET("serv_id",false);
$usr_name = paramUtils::strByGET("usr_name",false);
BO("game_index_web");
$bo = new game_index_web();
$bo->request_usr_name($serv_id, $usr_name);
