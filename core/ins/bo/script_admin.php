<?php
COMMON('baseCore');
DAO('script_dao');

class script_admin extends baseCore{
    public $DAO;
    public $admin;
    public function __construct(){
        parent::__construct();
        $this->DAO = new script_dao();
        $this->admin = 'admin';
    }

    public function seal_off_user_device($admin,$app_id,$channel){
        if($admin != $this->admin){
            $data['url'] = 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
            $data['ip'] = $this->client_ip();
            $this->err_log($data,'seal_off_user_device');
            die('502');
        }else{
            $date = date('Ym',time());
            $data_list = $this->DAO->get_data($app_id,$channel,$date);
            foreach($data_list as $data){
                $redis_data = $this->DAO->get_redis_data($data['sid']);
                if($data['user_count']>10 && !$redis_data){
                    $device_res = $this->DAO->get_device_info($data['sid']);
                    if(empty($device_res)){
                        $device_id = $this->DAO->insert_device_black($data['sid']);
                        $this->DAO->insert_device_operation_log(array(
                            "device_black_id" => $device_id,
                            "operation_type" => 1,
                            "operation_id" => 0,//0代表脚本操作
                            "operation_time" => time()
                        ));
                    }else{
                        if($device_res['device_status'] == '0' ){
                            $this->DAO->update_device_black(array(
                                "device_status" => 1,
                                "add_time" => time(),
                                "id" => $device_res['id']
                            ));
                            $this->DAO->insert_device_operation_log(array(
                                "device_black_id" => $device_res['id'],
                                "operation_type" => 1,
                                "operation_id" => 0,//0代表脚本操作
                                "operation_time" => time()
                            ));
                        }else{
                            echo '设备号：'.$data['sid'] ."；此设备已经在黑名单中";
                        }
                        $this->DAO->set_redis_data($data['sid'], $data['sid']);
                    }
                }
            }
        }
    }

    public function app_user_retention($admin,$app_id,$channel){
        if($admin != $this->admin){
            $data['url'] = 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
            $data['ip'] = $this->client_ip();
            $this->err_log($data,'app_user_retention');
            die('502');
        }else{
            $time = date('Y-m-d', time());
            //七天前的时间戳
            $reg_time = strtotime("$time-7 day");
            //七天之内注册的用户
            $data_list = $this->DAO->get_data_list($reg_time);
            foreach($data_list as $k => $v){
                $info = $this->DAO->get_user_app_info($v);
                $day = intval(($info['ActTime'] - $info['RegTime']) / (24 * 3600));
                if ($day && $day < 8) {
                    $this->DAO->update_retention($info, $day + 1);
                }
            }
            $start_time = strtotime($time . ' 00:00:00');
            $end_time = strtotime($time . ' 23:00:00');
            $new_data_list = $this->DAO->get_reg_data($app_id,$channel,$start_time, $end_time);
            foreach($new_data_list as $key => $data){
                $info = $this->DAO->get_user_retention_info($data);
                if(!$info){
                    $this->DAO->insert_retention($data);
                }
            }
        }
    }

    public function sid_seal_up($user, $app_id, $channel){
        if ($user != 'yyq') {
            $data['url'] = 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
            $data['ip'] = $this->client_ip();
            $this->err_log($data, 'seal_off_user_device');
            die('404');
        }else{
            $date = date('Ym', time());
            $data_list = $this->DAO->get_data($app_id, $channel, $date);
            if ($data_list) {
                echo '开始时间:' . time();
                foreach ($data_list as $data) {
                    if ($data['user_count'] > 10) {
                        $device_res = $this->DAO->get_device_info($data['sid']);
                        if (empty($device_res)) {
                            $device_id = $this->DAO->insert_device_black($data['sid']);
                            echo '插入设备'.$data['sid'];
                            echo '</br>';
                        } else if ($device_res['device_status'] == '0') {
                            $this->DAO->update_device_black(array(
                                "device_status" => 1,
                                "add_time" => time(),
                                "id" => $device_res['id']
                            ));
                            echo '更新设备'.$data['sid'];
                            echo '</br>';
                        }
                    }
                }
                echo '结束时间:' . time();
            } else {
                die('无需要停封设备');
            }
        }
    }
}
