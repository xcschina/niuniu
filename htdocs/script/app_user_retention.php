<?php
require_once "config.php";
DAO("script_dao");
$test_dao = new script_dao();
$time = date('Y-m-d',time());
$reg_time = strtotime(date('Y-m-d',strtotime("$time-7 day")));
$data_list = $test_dao->get_data_list($reg_time);
foreach($data_list as $k=>$v){
    $info = $test_dao->get_user_app_info($v['user_id']);
    $day = intval(($info['ActTime']-$info['RegTime'])/(24*3600));
    if($day && $day<8){
        $test_dao->update_retention($info,$day+1);
    }
}
$start_time = strtotime($time.' 00:00:00');
$end_time = strtotime($time.' 11:00:00');
$new_data_list = $test_dao->get_reg_data($start_time,$end_time);
foreach($new_data_list as $key=>$data){
    $info = $test_dao->get_user_retention_info($data['UserID']);
    if(!$info){
        $test_dao->insert_retention($data);
    }
}