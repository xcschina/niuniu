<?php
/*
 * 延迟入库日志
 */
require_once "config.php";
DAO("script_dao");
ini_set("display_errors","On");
error_reporting(E_ALL);
$test_dao = new script_dao();
$redis = new Redis();
$redis->pconnect(REDIS_HOST,REDIS_PORT) or die("redis connect fail");
$redis->select(5);

//redis
$key = 'vido_1_b2f1beed5a72db63';
if($redis->exists($key)) {
    $cpa_log = $redis->get($key);
    echo $cpa_log;
    $cpa_log = json_decode($cpa_log);
    if ($cpa_log) {
        $install_time = time();
        $test_dao->add_cpa_install_android_log($cpa_log, $install_time, 1);
    }
}