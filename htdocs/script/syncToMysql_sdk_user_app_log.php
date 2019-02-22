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
//        $stats_user_app_id = $redis->lGet("stats_user_app", -1);
        $stats_user_app_id = $redis->rPop("stats_user_app");
        if ($stats_user_app_id) {
//            $redis->rPop("stats_user_app");
            $stats_user_app = $redis->hGetAll($stats_user_app_id);
            if (!empty($stats_user_app)) {
                $stats_user_app_res = $test_dao->get_stats_user_app_by_id_new($stats_user_app);
                if (empty($stats_user_app_res)) {
                    //插入
                    $test_dao->insert_stats_user_app($stats_user_app);
                }else{
                    if ($stats_user_app['ActIP'] == '0' || $stats_user_app['ActIP'] == '1'){
                        continue ;
                    }
                    $test_dao->update_stats_user_app_new(array(
                        "ActIP"=>$stats_user_app['ActIP'],
                        "ActTime"=>$stats_user_app['ActTime'],
                        "RoleLevel"=>$stats_user_app['RoleLevel'],
                        "RoleName"=>$stats_user_app['RoleName'],
                        "ID"=>$stats_user_app_res['ID']
                    ));
                }
            }
        }else{
            //echo "stats_user_app list is empty \n";
        }
    }else{
        //echo "no stats_user_app list \n";
    }
    usleep(10000);
}
