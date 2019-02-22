<?php
/*
 * 延迟入库user_info && user_apptb
 */
require_once "config.php";
DAO("script_dao");
$test_dao = new script_dao();
$redis = new Redis();
$redis->pconnect(REDIS_HOST,REDIS_PORT) or die("redis connect fail");
$redis->select(2);
while (true){
    if ($redis->exists("user_login")){
        $user_login_id = $redis->lGet("user_login",-1);
        if ($user_login_id){
            $user_login = $redis->hGetAll($user_login_id);
            if (!empty($user_login)) {
                $user_info_id = md5((string)$user_login['user_id']."user_info");
                $user_info = $redis->hGetAll($user_info_id);
                $user_msg = array_merge($user_login,$user_info);
                $user_res = $test_dao->get_user_info_by_id($user_login['user_id']);
                if (empty($user_res)) {
                    //插入
                    $test_dao->insert_user_info($user_msg,$user_login,$user_login['user_id']);
                    //echo "user_msg into mysql ok! \n";
                } else {
                    //更新
                    $test_dao->update_user_info($user_msg,$user_login);
                    //echo "user_msg update mysql ok! \n";
                }
            }
            $redis->rPop("user_login");
        }else{
            //echo "user_login list is empty \n";
        }
    }else{
        //echo "no user_login list \n";
    }
    if ($redis->exists("user_app")){
        $user_app_id = $redis->lGet("user_app",-1);
        if ($user_app_id){
            $user_app_info = $redis->hGetAll($user_app_id);
            if (!empty($user_app_info)) {
                $user_app_res = $test_dao->get_user_app_by_id($user_app_info);
                if (empty($user_app_res)) {
                    //插入
                    $test_dao->insert_user_app($user_app_info);
                    //echo "user_app into mysql ok! \n";
                } else {
                    //更新
                    $test_dao->update_user_app($user_app_info);
                    //echo "user_app update mysql ok! \n";
                }
            }
            $redis->rPop("user_app");
        }else{
            //echo "user_app list is empty \n";
        }
    }else{
        //echo "no user_app list \n";
    }
    usleep(2000);
}
