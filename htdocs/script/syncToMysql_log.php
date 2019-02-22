<?php
/*
 * 延迟入库日志
 */
require_once "config.php";
DAO("script_dao");
$test_dao = new script_dao();
$redis = new Redis();
$redis->pconnect(REDIS_HOST,REDIS_PORT) or die("redis connect fail");
$redis->select(2);
while (true){
    //ios_stats_device
//    if ($redis->exists("ios_stats_device")) {
//        $ios_stats_device_id = $redis->lGet("ios_stats_device", -1);
//        if ($ios_stats_device_id) {
//            $ios_stats_device = $redis->hGetAll($ios_stats_device_id);
//            if (!empty($ios_stats_device)) {
//                $ios_stats_device_res = $test_dao->get_ios_stats_device_by_id($ios_stats_device);
//                if (empty($ios_stats_device_res)) {
//                    //插入
//                    $test_dao->insert_ios_stats_device($ios_stats_device);
//                }else{
//                    //更新
//                    $test_dao->update_ios_stats_device($ios_stats_device);
//                }
//            }
//            $redis->rPop("ios_stats_device");
//        }else{
//            //echo "ios_stats_device list is empty \n";
//        }
//    }else{
//        //echo "no ios_stats_device list \n";
//    }
    //ios_stats_user_app
    if ($redis->exists("ios_stats_user_app")) {
        $ios_stats_user_app_id = $redis->lGet("ios_stats_user_app", -1);
        if ($ios_stats_user_app_id) {
            $ios_stats_user_app = $redis->hGetAll($ios_stats_user_app_id);
            if (!empty($ios_stats_user_app)) {
                $ios_stats_user_app_res = $test_dao->get_ios_stats_user_app_by_id($ios_stats_user_app);
                if (empty($ios_stats_user_app_res)) {
                    //插入
                    $test_dao->insert_ios_stats_user_app($ios_stats_user_app);
                }else{
                    //更新
                    $test_dao->update_ios_stats_user_app($ios_stats_user_app);
                }
            }
            $redis->rPop("ios_stats_user_app");
        }else{
            //echo "ios_stats_user_app list is empty \n";
        }
    }else{
        //echo "no ios_stats_user_app list \n";
    }
    //ios_stats_user_login_log
    if ($redis->exists("ios_stats_user_login_log")) {
        $ios_stats_user_login_log_id = $redis->lGet("ios_stats_user_login_log", -1);
        if ($ios_stats_user_login_log_id) {
            $ios_stats_user_login_log = $redis->hGetAll($ios_stats_user_login_log_id);
            if (!empty($ios_stats_user_login_log)) {
                //插入记录
                $test_dao->insert_ios_stats_user_login_log($ios_stats_user_login_log);
                $redis->delete($ios_stats_user_login_log_id);
            }
            $redis->rPop("ios_stats_user_login_log");
        }else{
            //echo "ios_stats_user_login_log list is empty \n";
        }
    }else{
        //echo "no ios_stats_user_login_log list \n";
    }
    //ios_user_op_log
    if ($redis->exists("ios_user_op_log")) {
        $ios_user_op_log_id = $redis->lGet("ios_user_op_log", -1);
        if ($ios_user_op_log_id) {
            $ios_user_op_log = $redis->hGetAll($ios_user_op_log_id);
            if (!empty($ios_user_op_log)) {
                //插入记录
                $test_dao->insert_ios_user_op_log($ios_user_op_log);
                $redis->delete($ios_user_op_log_id);
            }
            $redis->rPop("ios_user_op_log");
        }else{
            //echo "ios_user_op_log list is empty \n";
        }
    }else{
        //echo "no ios_user_op_log list \n";
    }
    usleep(10000);
}
