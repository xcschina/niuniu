<?php
/*
 * 延迟入库日志(联运SDK，登入登出)
 */
require_once "config.php";
DAO("script_dao");
$test_dao = new script_dao();
$redis = new Redis();
$redis->pconnect(REDIS_HOST,REDIS_PORT) or die("redis connect fail");
$redis->select(2);
while (true){
    //stats_user_login_log
    if ($redis->exists("stats_user_login_log")) {
        $stats_user_login_log_id = $redis->lGet("stats_user_login_log", -1);
        $stats_user_login_log_id = $redis->rPop("stats_user_login_log");
        if ($stats_user_login_log_id) {
            $stats_user_login_log = $redis->hGetAll($stats_user_login_log_id);
            if (!empty($stats_user_login_log)) {
                //插入记录
                $test_dao->insert_stats_user_login_log($stats_user_login_log);
                $redis->delete($stats_user_login_log_id);
            }
        }else{
            //echo "stats_user_login_log list is empty \n";
        }
    }else{
        //echo "no stats_user_login_log list \n";
    }
    //stats_user_logout_log
    if ($redis->exists("stats_user_logout_log")) {
        $stats_user_op_log_id = $redis->lGet("stats_user_logout_log", -1);
        $stats_user_logout_log_id =  $redis->rPop("stats_user_logout_log");
        if ($stats_user_logout_log_id) {
            $stats_user_logout_log = $redis->hGetAll($stats_user_logout_log_id);
            if (!empty($stats_user_logout_log)) {
                //插入记录
                $test_dao->insert_stats_user_logout_log($stats_user_logout_log);
                $redis->delete($stats_user_logout_log_id);
            }
        }else{
            //echo "stats_user_logout_log list is empty \n";
        }
    }else{
        //echo "no stats_user_logout_log list \n";
    }
    //stats_user_op_log
    if ($redis->exists("stats_user_op_log")) {
        $stats_user_op_log_id = $redis->lGet("stats_user_op_log", -1);
        $stats_user_op_log_id = $redis->rPop("stats_user_op_log");
        if ($stats_user_op_log_id) {
            $stats_user_op_log = $redis->hGetAll($stats_user_op_log_id);
            if (!empty($stats_user_op_log)) {
                //插入记录
                $test_dao->insert_stats_user_op_log($stats_user_op_log);
                $redis->delete($stats_user_op_log_id);
            }
        }else{
            //echo "stats_user_op_log list is empty \n";
        }
    }else{
        //echo "no stats_user_op_log list \n";
    }
    usleep(10000);
}
