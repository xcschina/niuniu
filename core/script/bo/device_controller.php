<?php
DAO('device_controller_dao');
/**
 * Created by PhpStorm.
 * User: Young
 * Date: 2018/11/29
 * Time: 11:40 AM
 */
class device_controller{

    public $DAO;
    public $redis_2;

    public function __construct(){
        $this->DAO = new device_controller_dao();

        $this->mmc = new Memcache();
        $this->mmc->connect(MMCHOST, MMCPORT);

        $this->redis_2 = new Redis();
        $this->redis_2->connect(REDIS_HOST,REDIS_PORT);
        $this->redis_2->select(2);


        $this->redis_3 = new Redis();
        $this->redis_3->connect(REDIS_HOST,REDIS_PORT);
        $this->redis_3->select(6);
//        $this->redis_5 = new Redis();
//        $this->redis_5->connect(REDIS_HOST,REDIS_PORT);
//        $this->redis_5->select(5);
    }

    public function web_log($word, $filename = 'web_log') {
        file_put_contents('/data/wwwroot/niuniu/logs/'.$filename.'_'.date('Ymd').'.log', strftime("%Y-%m-%d %H:%M:%S",time())."\r\n".$word."\r\n",FILE_APPEND);
    }
    public function show_status(){
        echo 'ios_stats_user_app:';
        echo $this->redis_2->lLen("ios_stats_user_app");
        echo "<br>";
        echo 'ios_stats_device:';
        echo $this->redis_2->lLen("ios_stats_device");
        echo "<br>";
        echo 'ios_stats_user_login_log:';
        echo $this->redis_2->lLen("ios_stats_user_login_log");
        echo "<br>";
        echo 'ios_user_op_log:';
        echo $this->redis_2->lLen("ios_user_op_log");
        echo "<br>";
        echo 'stats_user_login_log:';
        echo $this->redis_2->lLen("stats_user_login_log");
        echo "<br>";
        echo 'stats_user_logout_log:';
        echo $this->redis_2->lLen("stats_user_logout_log");
        echo "<br>";
        echo 'stats_user_op_log:';
        echo $this->redis_2->lLen("stats_user_op_log");
        echo "<br>";
        echo 'stats_device:';
        echo $this->redis_2->lLen("stats_device");
        echo "<br>";
        echo 'super_stats_device:';
        echo $this->redis_3->lLen("super_stats_device");
        echo "<br>";
        echo 'super_user_app:';
        echo $this->redis_3->lLen("super_user_app");
        echo "<br>";
        echo 'super_user_login_log:';
        echo $this->redis_3->lLen("super_user_login_log");
        echo "<br>";
        echo 'super_user_logout_log:';
        echo $this->redis_3->lLen("super_user_logout_log");
        echo "<br>";
        echo 'super_user_op_log:';
        echo $this->redis_3->lLen("super_user_op_log");
        echo "<br>";
    }

    public function do_cpa_verfiy($stats_device,$cpa_info){
        $sid_info = $this->DAO->get_sid_info($stats_device['SID']);
        if(empty($sid_info)){
            echo "—————————new device——————————";
            if(!empty($stats_device['Imei'])){
                $imei_md5 = md5($stats_device['Imei']);
                if($cpa_info['ch_name'] == 'mksucxxl'){
                    $imei_md5 = strtoupper(md5(strtoupper($stats_device['Imei'])));
                }
                $new_click = $this->DAO->get_cpa_click_by_imei($cpa_info['id'],$stats_device['Imei'],$imei_md5);
                if(!empty($new_click)){
                    $this->web_log(var_export(json_encode($stats_device),1),'device_channel_success');
                    $this->DAO->up_cpa_click($new_click['id'],$stats_device['SID']);
                }else if(!empty($stats_device['Android_id'])) {
                    $an_click = $this->DAO->get_cpa_click_by_android_id($cpa_info['id'], $stats_device['Android_id'], md5($stats_device['Android_id']));
                    if (!empty($an_click)) {
                        $this->web_log(var_export(json_encode($stats_device), 1), 'device_channel_success');
                        $this->DAO->up_cpa_click($an_click['id'], $stats_device['SID']);
                    }elseif(!empty($stats_device['Mac'])){
                        $mac = str_replace(":","",$stats_device['Mac']);
                        $mac_click = $this->DAO->get_cpa_click_by_mac($cpa_info['id'], $stats_device['Mac'], md5($mac));
                        if (!empty($mac_click)) {
                            $this->web_log(var_export(json_encode($stats_device), 1), 'device_channel_success');
                            $this->DAO->up_cpa_click($an_click['id'], $stats_device['SID']);
                        }
                    }
                }
            }elseif(!empty($stats_device['Android_id'])){
                $an_click = $this->DAO->get_cpa_click_by_android_id($cpa_info['id'], $stats_device['Android_id'], md5($stats_device['Android_id']));
                if (!empty($an_click)) {
                    $this->web_log(var_export(json_encode($stats_device), 1), 'device_channel_success');
                    $this->DAO->up_cpa_click($an_click['id'], $stats_device['SID']);
                }elseif(!empty($stats_device['Mac'])){
                    $mac = str_replace(":","",$stats_device['Mac']);
                    $mac_click = $this->DAO->get_cpa_click_by_mac($cpa_info['id'], $stats_device['Mac'], md5($mac));
                    if (!empty($mac_click)) {
                        $this->web_log(var_export(json_encode($stats_device), 1), 'device_channel_success');
                        $this->DAO->up_cpa_click($an_click['id'], $stats_device['SID']);
                    }
                }
            }
        }elseif($cpa_info['status']=='1' && ($stats_device['ActIP'] == $cpa_info['white_ip'])){
            echo "—————————whithe ip——————————";
            if(!empty($stats_device['Imei'])){
                $imei_md5 = md5($stats_device['Imei']);
                if($cpa_info['ch_name'] == 'mksucxxl'){
                    $imei_md5 = strtoupper(md5(strtoupper($stats_device['Imei'])));
                }
                $new_click = $this->DAO->get_cpa_click_by_imei($cpa_info['id'],$stats_device['Imei'],$imei_md5);
                if(!empty($new_click)){
                    $this->web_log(var_export(json_encode($stats_device),1),'device_channel_success');
                    $this->DAO->up_cpa_click($new_click['id'],$stats_device['SID']);
                }else if(!empty($stats_device['Android_id'])) {
                    $an_click = $this->DAO->get_cpa_click_by_android_id($cpa_info['id'], $stats_device['Android_id'], md5($stats_device['Android_id']));
                    if (!empty($an_click)) {
                        $this->web_log(var_export(json_encode($stats_device), 1), 'device_channel_success');
                        $this->DAO->up_cpa_click($an_click['id'], $stats_device['SID']);
                    }
                }
            }
        }
    }

    public function do_an_device_up(){
        while(true) {
            //获取当前需要上报的设备号
            if ($this->redis_2->exists("stats_device")) {
                //获取最旧的用户
                $stats_device_id = $this->redis_2->rPop("stats_device");
                if ($stats_device_id) {
                    //获取详细信息
                    $stats_device = $this->redis_2->hGetAll($stats_device_id);
                    if(!empty($stats_device)){
                        if($stats_device['AppID'] == 1051){
                            $this->web_log(var_export("AppID-1051:".json_encode($stats_device),1),'device_niuguo');
                            continue;
                        }
                        //投放回调
                        $cpa_info = $this->DAO->get_cpa_app_info($stats_device['AppID'],$stats_device['Channel']);
                        $stats_device_res = $this->DAO->get_stats_device_by_id($stats_device);
                        if(!empty($cpa_info)){
                            echo "——————————cpa info——————————";
                            if(($cpa_info && empty($stats_device_res))){
                                echo "——————————！！！！——————————";
                                $this->do_cpa_verfiy($stats_device,$cpa_info);
                            }elseif($cpa_info['status']=='1' && ($stats_device['ActIP'] == $cpa_info['white_ip'])){
                                echo "—————————test——————————";
                                $this->do_cpa_verfiy($stats_device,$cpa_info);
                            }
                        }

                        if(empty($stats_device_res)) {
                            $this->DAO->insert_stats_device($stats_device);
                            $this->web_log(var_export(json_encode($stats_device),1),'device_info_add');
                        }else{
                            $this->DAO->update_stats_device($stats_device,$stats_device_res['ID']);
                            $stats_device['db_id'] = $stats_device_res['ID'];
                            $this->web_log(var_export(json_encode($stats_device),1),'device_info_up');
                        }
                    }
                } else {
                    //休息3秒
                    usleep(3000000);
                }
            }
            usleep(10000);
        }
    }
}