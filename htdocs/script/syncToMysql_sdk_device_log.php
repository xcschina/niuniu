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
    //stats_device
    if ($redis->exists("stats_device")) {
        $stats_device_id = $redis->rPop("stats_device");
        if ($stats_device_id) {
            $stats_device = $redis->hGetAll($stats_device_id);
            if (!empty($stats_device)) {
                //过滤牛果APP
                if($stats_device['AppID'] == 1051){
                    echo "AppID-1051:".json_encode($stats_device)."\n";
                    continue;
                }
                $redis->select(5);
                //查询cpa渠道
                $app_list = json_decode($redis->get("cpa_app_list"));
                if(!$app_list){
                    $cpa_list = $test_dao->get_cpa_list();
                    $app_list = array('nnwl');
                    foreach($cpa_list as $data){
                        array_push($app_list,$data['app_id']);
                    }
                    $redis->set("cpa_app_list",json_encode($app_list),6000);
                }
                //cpa回调
                if(in_array($stats_device['AppID'],$app_list)){
                    if($stats_device['Channel'] == 'vido' || $stats_device['Channel'] == 'pingme'){
                        if($stats_device['Android_id']){
                            $android_id_md5 = strtolower(md5($stats_device['Android_id']));
                            $key = $stats_device['Channel'].'_1_'.$android_id_md5;
                            if($redis->exists($key)){
                                $is_install = $test_dao->get_install_app_android_md5(strtolower(md5($stats_device['Android_id'])),$stats_device['Android_id']);
//                                $is_install = $test_dao->get_install_android_md5(strtolower(md5($stats_device['Android_id'])));
                            }else{
                                $key = $stats_device['Channel'].'_1_'.$stats_device['Imei'];
                                $is_install = $test_dao->get_install_app_imei($stats_device['Imei'],$stats_device['Android_id']);
//                                $is_install = $test_dao->get_install_imei($stats_device['Imei']);
                            }
                        }elseif($stats_device['Imei']){
                            $key = $stats_device['Channel'].'_1_'.$stats_device['Imei'];
                            $is_install = $test_dao->get_install_app_imei($stats_device['Imei'],$stats_device['Android_id']);
//                            $is_install = $test_dao->get_install_imei($stats_device['Imei']);
                        }else{
                            $is_install = '1';
                        }
                        if(empty($is_install)){
                            //redis
                            if($redis->exists($key)){
                                $cpa_log = $redis->get($key);
                                $cpa_log = json_decode($cpa_log);
                                if($cpa_log){
                                    $install_time = $stats_device['RegTime'] ? $stats_device['ActTime'] : $stats_device['ActTime'];
                                    $test_dao->add_cpa_install_android_log($cpa_log,$install_time,1);
                                }
                            }
                        }
                    }

                }
                $redis->select(2);
                $stats_device_res = $test_dao->get_stats_device_by_id($stats_device);
                if(empty($stats_device_res)) {
                    //插入
                    $test_dao->insert_stats_device($stats_device);
                }else{
                    $test_dao->update_stats_device($stats_device);

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
