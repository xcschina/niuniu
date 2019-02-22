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
    if ($redis->exists("ios_stats_device")) {
        $ios_stats_device_id = $redis->lGet("ios_stats_device", -1);
        if ($ios_stats_device_id) {
            $ios_stats_device = $redis->hGetAll($ios_stats_device_id);
//            do_cpa_callback($ios_stats_device);
            if (!empty($ios_stats_device)) {
//                cpa回调
                if(in_array($ios_stats_device['AppID'],array('1000','1001','1076'))){
                    $is_instatll = $test_dao->get_install_adfa($ios_stats_device['Adtid']);
                    if(empty($is_instatll)){
                        $redis->select(5);
                        //redis
                        $key = '5'.'_'.$ios_stats_device['Adtid'];
                        if($redis->exists($key)){
                            $cpa_log = $redis->get($key);
                            $cpa_log = json_decode($cpa_log);
                            if($cpa_log){
                                $install_time = $ios_stats_device['RegTime'] ? $ios_stats_device['ActTime'] : $ios_stats_device['ActTime'];
                                $test_dao->add_cpa_install_log($cpa_log,$install_time,1);
                            }
                        }
                    }
                    $redis->select(2);
                }
                $ios_stats_device_res = $test_dao->get_ios_stats_device_by_id($ios_stats_device);
                if (empty($ios_stats_device_res)) {
                    //插入
                    $test_dao->insert_ios_stats_device($ios_stats_device);
                }else{
                    //更新
                    $test_dao->update_ios_stats_device($ios_stats_device);
                }
            }

            $redis->rPop("ios_stats_device");
        }else{
            //echo "ios_stats_device list is empty \n";
        }
    }else{
        //echo "no ios_stats_device list \n";
    }
    usleep(10000);
}

function  do_cpa_callback($device){
    $test_dao = new script_dao();
    if(in_array($device['AppID'],array('1000','1001','1076'))){
        //是否在投放
        $cpa_log = $test_dao->get_cpa_log($device);
        if($cpa_log){
            $cpa_info = $test_dao->get_cpa_info($cpa_log['adid']);
            $install_time = $device['RegTime'] ? $device['ActTime'] : $device['ActTime'];
            $test_dao->update_cpa_log($cpa_log,$install_time,1);
        }

    }

}