<?php
/*
 * 延迟入库日志(联运SDK设备，角色)
 */
require_once "config.php";
DAO("script_dao");
$test_dao = new script_dao();
$redis = new Redis();
$redis->pconnect(REDIS_HOST,REDIS_PORT) or die("redis connect fail");
$redis->select(2);
while (true){
    //stats_user_app
    if ($redis->exists("stats_user_app")) {
        $stats_user_app_id = $redis->lGet("stats_user_app", -1);
        if ($stats_user_app_id) {
            print_r($stats_user_app_id);
            echo "\n";
            $stats_user_app = $redis->hGetAll($stats_user_app_id);
            if (!empty($stats_user_app)) {
                print_r($stats_user_app);
                echo "\n";
                $stats_user_app_res = $test_dao->get_stats_user_app_by_id($stats_user_app);
                if (empty($stats_user_app_res)) {
                    //插入
                    $test_dao->insert_stats_user_app($stats_user_app);
                    print_r('insert ok');
                    echo "\n";
                }else{
                    //更新
                    $test_dao->update_stats_user_app($stats_user_app);
                    print_r('update ok');
                    echo "\n";
                }
            }
            $redis->rPop("stats_user_app");
        }else{
            echo "stats_user_app list is empty \n";
        }
    }else{
        echo "no stats_user_app list \n";
    }
    usleep(10000);
}
