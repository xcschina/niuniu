<?php
/*
 * 延迟入库日志(融合SDK设备)
 */
require_once "config.php";
DAO("script_dao");
$test_dao = new script_dao();
$redis = new Redis();
$redis->pconnect(REDIS_HOST,REDIS_PORT) or die("redis connect fail");
$redis->select(6);
while (true){
    //super_stats_device
    if ($redis->exists("super_stats_device")) {
        $super_device_id = $redis->rPop("super_stats_device");
        if ($super_device_id) {
            $super_device = $redis->hGetAll($super_device_id);
            if (!empty($super_device)) {
                $super_device_res = $test_dao->get_super_device_by_id($super_device);
                if (empty($super_device_res)) {
                    //插入
                    $test_dao->insert_super_device($super_device);
//                    echo "insert ok\n";
                }else{
                    $test_dao->update_super_device($super_device);
//                    echo "update ok\n";
                }
            }
        }else{
//            echo "stats_device list is empty \n";
        }
    }else{
//        echo "no stats_device list \n";
    }
    usleep(10000);
}
