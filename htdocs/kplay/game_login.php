<?php
header("Content-Type: text/html; charset=utf-8");
require_once 'config.php';
COMMON('paramUtils');
BO("game_admin");
try{
    $bo = new game_admin();
    $bo->h5_game_view();
}catch (Exception $e){
    $bo->err_log(var_export($e,1),'h5_r_game_error');
    print $e;
}
