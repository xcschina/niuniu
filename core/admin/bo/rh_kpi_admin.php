<?php
COMMON('adminBaseCore','pageCore','uploadHelper');
DAO('rh_kpi_admin_dao','menu_admin_dao','app_admin_dao');

class rh_kpi_admin extends adminBaseCore{
    public $DAO;
    public $ES;
    public $ES_PARAMS;

    public function __construct() {
        parent::__construct();

        $hosts = [ ES_HOST.":".ES_PORT];
        $this->ES = Elasticsearch\ClientBuilder::create()->setHosts($hosts)->build();
        $this->ES_PARAMS = array();
        $this->ES_PARAMS['index'] = 'r_sdk_log';
        $this->DAO = new rh_kpi_admin_dao();
    }

    public function index_view(){
        $rh_app_dao = new rh_app_admin_dao();
        $rh_apps = $rh_app_dao->get_all_rh_apps();
        $ch_list = $rh_app_dao->get_all_ch();
        if($_SESSION['usr_id'] == '727'){
            $user_app = array('7032','7033');
            foreach($rh_apps as $k=>$item){
               if(!in_array($item['app_id'],$user_app)){
                   unset($rh_apps[$k]);
               }
            }
        }
        if($_SESSION['usr_id'] == '712'){
            $user_app = array('7026','7033');
            foreach($rh_apps as $k=>$item){
                if(!in_array($item['app_id'],$user_app)){
                    unset($rh_apps[$k]);
                }
            }
        }
        if($_SESSION['usr_id'] == '762'){
            $user_app = array('7033');
            foreach($rh_apps as $k=>$item){
                if(!in_array($item['app_id'],$user_app)){
                    unset($rh_apps[$k]);
                }
            }
            $ch_app = array('quick');
            foreach($ch_list as $k=>$item){
                if(!in_array($item['ch_code'],$ch_app)){
                    unset($ch_list[$k]);
                }
            }
        }
        $this->assign("apps", $rh_apps);
        $this->assign("ch_list", $ch_list);
        $this->display("rh_kpi/index.html");
    }

    public function idx_group_data(){
        $app_id = $this->get_rh_app_id($_GET['appids']);
        $apps = explode(",", $app_id);
        $channels = $this->get_rh_channel($_GET['channels']);
        $day_diff = $_POST['day_diff'];
        $latest_seven_days_data = $this->get_latest_seven_days_data_kpi($day_diff,$apps,$channels);
        $all_sum_new_user = 0;
        $all_sum_new_device = 0;
        $all_sum_act_user = 0;
        $all_sum_act_device = 0;
        $all_sum_pay = 0;
        $all_sum_pay_count = 0;
        $all_sum_order_count = 0;
        if ($latest_seven_days_data) {
            foreach ($latest_seven_days_data as $key => $days_data) {
                $all_sum_new_user = $all_sum_new_user + $days_data['sum_new_user'];
                $all_sum_new_device = $all_sum_new_device + $days_data['sum_new_device'];
                $all_sum_act_user = $all_sum_act_user + $days_data['sum_act_user'];
                $all_sum_act_device = $all_sum_act_device + $days_data['sum_act_device'];
                $all_sum_pay = $all_sum_pay + $days_data['sum_pay'];
                $all_sum_pay_count = $all_sum_pay_count + $days_data['sum_pay_count'];
                $all_sum_order_count = $all_sum_order_count + $days_data['sum_order_count'];
            }
        }
        $day1_num = -(6 + $day_diff)." day";
        $day7_num = -(0 + $day_diff)." day";
        $day1 = strtotime(date('Y-m-d',time()).$day1_num);
        $day7 = strtotime(date('Y-m-d',time()).$day7_num);
        $this->assign("app_id", $_GET['appids']);
        $this->assign("channel", $_GET['channels']);
        $this->assign("day1", $day1);
        $this->assign("day7", $day7);
        $this->assign("day_diff", empty($day_diff)?'0':$day_diff);
        $this->assign("all_sum_new_user", $all_sum_new_user);
        $this->assign("all_sum_new_device", $all_sum_new_device);
        $this->assign("all_sum_act_user", $all_sum_act_user);
        $this->assign("all_sum_act_device", $all_sum_act_device);
        $this->assign("all_sum_pay", $all_sum_pay);
        $this->assign("all_sum_pay_count", $all_sum_pay_count);
        $this->assign("all_sum_order_count", $all_sum_order_count);
        $this->assign("latest_seven_days_data", $latest_seven_days_data);
        $this->display("rh_kpi/idx_group_view.html");
    }

    public function idx_trend_data(){
        $app_id = $this->get_rh_app_id($_GET['appids']);
        $apps = explode(",", $app_id);
        $channels = $this->get_rh_channel($_GET['channels']);
        $params = $_POST;
        $data_type = $params['data_type'];
        $date_type = $params['date_type'];
        switch ($date_type) {
            //时间查询
            case"0":
                $start_date = $params['start'];
                $end_date = $params['end'];
                break;
            //本周
            case"1":
                $start_date = date("Y-m-d", mktime(0,0,0,date("m"),date("d")-date("w")-5,date("Y")));
                $end_date = date('Y-m-d', time()+86400);
                break;
            //上周
            case"2":
                $start_date = date("Y-m-d",mktime(0,0,0,date("m"),date("d")-date("w")+1-7,date("Y")));
                $end_date = date("Y-m-d",mktime(23,59,59,date("m"),date("d")-date("w")+7-7,date("Y"))+86400);
                break;
            //本月
            case"3":
                $start_date = date("Y-m-d",mktime(0,0,0,date('m'),1,date('Y')));
                $end_date = date('Y-m-d',time()+86400);
                break;
            //上个月
            case"4":
                $start_date = date("Y-m-d",mktime(0,0,0,date("m")-1,1,date("Y")));
                $end_date = date("Y-m-d",mktime(23,59,59,date("m"),0,date("Y"))+86400);
                break;
            //前三个月
            case"5":
                $start_date = date("Y-m-d",mktime(0,0,0,date("m")-3,1,date("Y")));
                $end_date = date("Y-m-d",mktime(23,59,59,date("m"),0,date("Y"))+86400);
                break;
            default:
                $start_date = $params['start']?$params['start']:date("Y-m-d", mktime(0,0,0,date("m"),date("d")-date("w")-5,date("Y")));
                $end_date = date('Y-m-d', time()+86400);
                break;
        }

        switch ($data_type) {
            case"1":
                $source = "new_user";
                $title = "新增用户";
                break;
            case"2":
                $source = "act_user";
                $title = "活跃用户";
                break;
            case"3":
                $source = "pay_money";
                $title = "充值金额";
                break;
            default:
                $source = "new_user";
                $title = "新增用户";
                break;
        }
        $data = $this->get_apps_trend_data($apps,$start_date,$end_date,$source,$channels);

        $this->assign("app_id", $app_id);
        $this->assign("channel", $_GET['channels']);
        $this->assign("data_list", "'".implode("','",$data['data_list'])."'");
        $this->assign("date_list", "'".implode("','",$data['date_list'])."'");
        $this->assign("data_total", $data['total']);
        $this->assign("title", $title);
        $this->assign("date_type", empty($date_type)?'0':$date_type);
        $this->assign("data_type", empty($data_type)?'0':$data_type);
        $this->assign("date_start", $start_date);
        $this->assign("date_end", $end_date);
        $this->display("rh_kpi/idx_trend_view.html");
    }

    public function idx_game_data(){
        $app_id = $this->get_rh_app_id($_GET['appids']);
        $apps = explode(",", $app_id);
        $channel = $this->get_rh_channel($_GET['channels']);
        $params = $_POST;
        switch ($params['date_type']) {
            //时间查询
            case"0":
                $start_date = $params['start'];
                $end_date = $params['end'];
                break;
            //今天
            case"1":
                $start_date = date('Y-m-d',time());
                $end_date = date('Y-m-d', time()+86400);
                break;
            //昨天
            case"2":
                $start_date = date('Y-m-d', time()-86400);
                $end_date = date('Y-m-d', time());
                break;
            //上周
            case"3":
                $start_date = date("Y-m-d",mktime(0,0,0,date("m"),date("d")-date("w")+1-7,date("Y")));
                $end_date = date("Y-m-d",mktime(23,59,59,date("m"),date("d")-date("w")+7-7,date("Y"))+86400);
                break;
            //本周
            case"4":
                $start_date = date("Y-m-d",mktime(0,0,0,date('m'),1,date('Y')));
                $end_date = date('Y-m-d',time()+86400);
                break;
            //上个月
            case"5":
                $start_date = date("Y-m-d",mktime(0,0,0,date("m")-1,1,date("Y")));
                $end_date = date("Y-m-d",mktime(23,59,59,date("m"),0,date("Y"))+86400);
                break;
            default:
                $start_date = date('Y-m-d',time());
                $end_date = date('Y-m-d', time()+86400);
                break;
        }
        $apps_data = $this->get_app_day_data($apps,$start_date,$end_date,$channel);
        $all_sum_new_user = 0;
        $all_sum_new_device = 0;
        $all_sum_act_user = 0;
        $all_sum_act_device = 0;
        $all_sum_new_role = 0;
        $all_sum_pay = 0;
        if ($apps_data) {
            foreach ($apps_data as $key => $days_data) {
                if(!($days_data['key'] ==1000 || $days_data['key'] == 1001)){
                    $app_info = $this->DAO->get_rh_app_info($days_data['key']);
                    if($app_info){
                        $apps_data[$key]['app_name']=$app_info['app_name'];
                    }else{
                        unset($apps_data[$key]);
                    }
                }else{
                    unset($apps_data[$key]);
                }
                $all_sum_new_user = $all_sum_new_user + $days_data['sum_new_user']["value"];
                $all_sum_new_device = $all_sum_new_device + $days_data['sum_new_device']["value"];
                $all_sum_act_user = $all_sum_act_user + $days_data['sum_act_user']["value"];
                $all_sum_act_device = $all_sum_act_device + $days_data['sum_act_device']["value"];
                $all_sum_new_role = $all_sum_new_role + $days_data['sum_new_role']["value"];
                $all_sum_pay = $all_sum_pay + $days_data['sum_pay']["value"];
            }
        }
        $this->assign("end_date", $end_date);
        $this->assign("start_date", $start_date);
        $this->assign("apps_data", $apps_data);
        $this->assign("all_sum_new_user", $all_sum_new_user);
        $this->assign("all_sum_new_device", $all_sum_new_device);
        $this->assign("all_sum_act_user", $all_sum_act_user);
        $this->assign("all_sum_new_role", $all_sum_new_role);
        $this->assign("all_sum_act_device", $all_sum_act_device);
        $this->assign("all_sum_pay", $all_sum_pay);
        $this->assign("date_type", $params['date_type']);
        $this->assign("app_id", $app_id);
        $this->assign("channel", $_GET['channels']);
        $this->assign("date_start", date('Y-m-d',time()));
        $this->assign("date_end", date('Y-m-d',time()));
        $this->display("rh_kpi/idx_game_view.html");
    }

    public function game_hour_data(){
        $app_id = $this->get_rh_app_id($_GET['appids']);
        $apps = explode(",", $app_id);
        $channels = $this->get_rh_channel($_GET['channels']);
        $date_time = $_GET['date'];
        $apps_data = $this->get_apps_hour_data(date('Y', $date_time),date('m', $date_time), date('d', $date_time),$apps,$channels);
        $date_1 = $date_time-86400;
        $apps_data_1 = $this->get_apps_hour_data(date('Y', $date_1),date('m', $date_1), date('d', $date_1),$apps,$channels);
        $date_2 = $date_time-(86400*2);
        $apps_data_2 = $this->get_apps_hour_data(date('Y', $date_2),date('m', $date_2), date('d', $date_2),$apps,$channels);
        $date_7 = $date_time-(86400*7);
        $apps_data_7 = $this->get_apps_hour_data(date('Y', $date_7),date('m', $date_7), date('d', $date_7),$apps,$channels);

        $time_list = array();
        $pay_list = array();
        $new_user_list = array();
        $act_user_list = array();
        $new_device_list = array();
        $act_device_list = array();

        foreach($apps_data as $key=> $app_data){
            array_push($time_list,$app_data['key'].":00");
            array_push($pay_list,$app_data['sum_pay']['value']);
            array_push($new_user_list,$app_data['sum_new_user']['value']);
            array_push($act_user_list,$app_data['sum_act_user']['value']);
            array_push($new_device_list,$app_data['sum_new_device']['value']);
            array_push($act_device_list,$app_data['sum_act_device']['value']);
        }

        $pay_list1 = array();
        $new_user_list1 = array();
        $act_user_list1 = array();
        $new_device_list1 = array();
        $act_device_list1 = array();
        foreach($apps_data_1 as $key=> $app_data1){
            array_push($pay_list1,$app_data1['sum_pay']['value']);
            array_push($new_user_list1,$app_data1['sum_new_user']['value']);
            array_push($act_user_list1,$app_data1['sum_act_user']['value']);
            array_push($new_device_list1,$app_data1['sum_new_device']['value']);
            array_push($act_device_list1,$app_data1['sum_act_device']['value']);
        }

        $pay_list2 = array();
        $new_user_list2 = array();
        $act_user_list2 = array();
        $new_device_list2 = array();
        $act_device_list2 = array();
        foreach($apps_data_2 as $key=> $app_data2){
            array_push($pay_list2,$app_data2['sum_pay']['value']);
            array_push($new_user_list2,$app_data2['sum_new_user']['value']);
            array_push($act_user_list2,$app_data2['sum_act_user']['value']);
            array_push($new_device_list2,$app_data2['sum_new_device']['value']);
            array_push($act_device_list2,$app_data2['sum_act_device']['value']);
        }

        $pay_list7 = array();
        $new_user_list7 = array();
        $act_user_list7 = array();
        $new_device_list7 = array();
        $act_device_list7 = array();
        foreach($apps_data_7 as $key=> $app_data7){
            array_push($pay_list7,$app_data7['sum_pay']['value']);
            array_push($new_user_list7,$app_data7['sum_new_user']['value']);
            array_push($act_user_list7,$app_data7['sum_act_user']['value']);
            array_push($new_device_list7,$app_data7['sum_new_device']['value']);
            array_push($act_device_list7,$app_data7['sum_act_device']['value']);
        }
        $day_data = $this->get_apps_day_data($apps, date('Y', $date_time),date('m', $date_time),date('d', $date_time), 1, $channels);
        $act_user = $day_data['sum_act_user'];
        $new_user = $day_data['sum_new_user'];
        $act_device = $day_data['sum_act_device'];
        $new_device = $day_data['sum_new_device'];
        $pay_ammount = $day_data['sum_pay'];
        $pay_count = $day_data['sum_pay_count']+$day_data['apple_user_no'];
        if($new_user > 0){
            $keep_next = $day_data['sum_keep_next'] / $new_user * 100;
        }else{
            $keep_next = 0;
        }

        if(!empty($pay_count)){
            $arppu = round(($pay_ammount)/$pay_count, 2);
        }else{
            $arppu = 0;
        }

        if(!empty($act_user)){
            $darpu = round(($pay_ammount)/ $act_user, 2);
            $pay_rate = round(($pay_count / $act_user) * 100, 2);
        }else{
            $darpu = 0;
            $pay_rate = 0;
        }
        $this->assign("channels", $channels);
        $this->assign("apps_data", $apps_data);
        $this->assign("date_time", date('m-d', $date_time));
        $this->assign("time_list", implode("','",$time_list));
        $this->assign("pay_list", implode("','",$pay_list));
        $this->assign("new_user_list", implode("','",$new_user_list));
        $this->assign("act_user_list", implode("','",$act_user_list));
        $this->assign("new_device_list", implode("','",$new_device_list));
        $this->assign("act_device_list", implode("','",$act_device_list));
        $this->assign("pay_list1", implode("','",$pay_list1));
        $this->assign("new_user_list1", implode("','",$new_user_list1));
        $this->assign("act_user_list1", implode("','",$act_user_list1));
        $this->assign("new_device_list1", implode("','",$new_device_list1));
        $this->assign("act_device_list1", implode("','",$act_device_list1));
        $this->assign("pay_list2", implode("','",$pay_list2));
        $this->assign("new_user_list2", implode("','",$new_user_list2));
        $this->assign("act_user_list2", implode("','",$act_user_list2));
        $this->assign("new_device_list2", implode("','",$new_device_list2));
        $this->assign("act_device_list2", implode("','",$act_device_list2));
        $this->assign("pay_list7", implode("','",$pay_list7));
        $this->assign("new_user_list7", implode("','",$new_user_list7));
        $this->assign("act_user_list7", implode("','",$act_user_list7));
        $this->assign("new_device_list7", implode("','",$new_device_list7));
        $this->assign("act_device_list7", implode("','",$act_device_list7));

        $this->assign("act_user", $act_user);
        $this->assign("new_user", $new_user);
        $this->assign("act_device", $act_device);
        $this->assign("new_device", $new_device);
        $this->assign("pay_ammount", $pay_ammount);
        $this->assign("pay_count", $pay_count);
        $this->assign("arppu", $arppu);
        $this->assign("darpu", $darpu);
        $this->assign("pay_rate", $pay_rate);
        $this->assign("keep_next", $keep_next);
        $this->display("rh_kpi/game_hour_view.html");
    }

    public function detail($appid){
        if($_SESSION['usr_id'] == '762'){
            $_GET['appids'] = '7033';
            $appid = 7033;
        }
        $app_info = $this->DAO->get_rh_app_info($_GET['appids']);
        if($_POST['channels']){
            $channels = $_POST['channels'];
        }else{
            $channels = "";
        }
        if($_SESSION['usr_id'] == '762'){
            $channels = 'quick';
        }
        if($_POST['start_date']){
            $start_date = $_POST['start_date'];
        }else{
            $start_date = date("Y-m-d",mktime(0,0,0,date("m"),date("d")-date("w")-20,date("Y")));
        }
        if($_POST['end_date']){
            $end_date = $_POST['end_date'];
        }else{
            $end_date = date('Y-m-d', time()+86400);
        }
        $channel = $channels;
        $rh_app_dao = new rh_app_admin_dao();
        $apps = $rh_app_dao ->get_all_rh_apps();
        $channel_list = $rh_app_dao->get_all_ch();

        //查询苹果充值明细
        $app_data = $this->get_single_app_day_data($appid,$start_date,$end_date,$channel,$apple_res_date);
        $sum_new_user = array_sum($app_data['new_user_list']);
        $sum_act_user = array_sum($app_data['act_user_list']);
        $sum_new_device = array_sum($app_data['new_device_list']);
        $sum_act_device = array_sum($app_data['act_device_list']);
        $sum_pay = array_sum($app_data['pay_list']);
        $sum_pay_count = array_sum($app_data['pay_count_list']);
        $sum_next = array_sum($app_data['next_list']);
        $sum_two = array_sum($app_data['two_list']);
        $sum_third = array_sum($app_data['third_list']);
        $sum_four = array_sum($app_data['four_list']);
        $sum_five = array_sum($app_data['five_list']);
        $sum_six = array_sum($app_data['six_list']);
        $sum_seven = array_sum($app_data['seven_list']);
        $sum_eight = array_sum($app_data['eight_list']);
        $sum_ten = array_sum($app_data['ten_list']);
        $sum_twelve = array_sum($app_data['twelve_list']);
        $sum_fourteen = array_sum($app_data['fourteen_list']);
        $sum_sixteen = array_sum($app_data['sixteen_list']);
        $sum_twenty = array_sum($app_data['twenty_list']);
        $sum_thirty = array_sum($app_data['thirty_list']);
        $this->assign("app_name", $app_info['app_name']);
        $this->assign("sum_new_user", $sum_new_user);
        $this->assign("sum_act_user", $sum_act_user);
        $this->assign("sum_new_device", $sum_new_device);
        $this->assign("sum_act_device", $sum_act_device);
        $this->assign("sum_pay", $sum_pay);
        $this->assign("sum_pay_count", $sum_pay_count);
        $this->assign("sum_next", $sum_next);
        $this->assign("sum_two", $sum_two);
        $this->assign("sum_third", $sum_third);
        $this->assign("sum_four", $sum_four);
        $this->assign("sum_five", $sum_five);
        $this->assign("sum_six", $sum_six);
        $this->assign("sum_seven", $sum_seven);
        $this->assign("sum_eight", $sum_eight);
        $this->assign("sum_ten", $sum_ten);
        $this->assign("sum_twelve", $sum_twelve);
        $this->assign("sum_fourteen", $sum_fourteen);
        $this->assign("sum_sixteen", $sum_sixteen);
        $this->assign("sum_twenty", $sum_twenty);
        $this->assign("sum_thirty", $sum_thirty);
        $this->assign("app_data", $app_data);
        $this->assign("channels", $channels);
        $this->assign("start_date", $start_date);
        $this->assign("end_date", $end_date);
        $this->assign("appid", $appid);
        $this->assign("channel_list", $channel_list);
        $this->assign("apps", $apps);
        $this->display("rh_kpi/game_detail.html");
    }

    public function realtime($appid){
        $app_info = $this->DAO->get_rh_app_info($appid);
        $channels = $this->get_rh_channel($_GET['channels']);
        //根据渠道获取子渠道信息
        $channel = $_GET['channels'];
        $rh_app_dao = new rh_app_admin_dao();
        $channel_list = $rh_app_dao->get_all_ch();
        if($_GET['date']){
            $start_time = strtotime($_GET['date']);
            $apps_data = $this->get_apps_hour_data(date('Y',$start_time),date('m',$start_time),date('d',$start_time),$appid,$channel);
            $today_data = $this->get_single_app_day_data($appid,date("Y-m-d",$start_time),date("Y-m-d",$start_time+86400),$channel);
        }else{
            $apps_data = $this->get_apps_hour_data(date('Y',time()),date('m',time()),date('d',time()),$appid,$channel);
            $today_data = $this->get_single_app_day_data($appid,date("Y-m-d",time()),date("Y-m-d",time()+86400),$channel);
        }

        if ($today_apple_data['pay_count']){
            $today_apple_pay = $today_apple_data['pay_count'];
        }else{
            $today_apple_pay = 0;
        }
        $time_list = array();
        $pay_list = array();
        $new_user_list = array();
        $act_user_list = array();
        $new_device_list = array();
        $act_device_list = array();
        $pay_count_list = array();
        foreach($apps_data as $key=> $app_data){
            array_push($time_list,$app_data['key'].":00");
            array_push($pay_list,$app_data['sum_pay']['value']);
            array_push($new_user_list,$app_data['sum_new_user']['value']);
            array_push($act_user_list,$app_data['sum_act_user']['value']);
            array_push($new_device_list,$app_data['sum_new_device']['value']);
            array_push($act_device_list,$app_data['sum_act_device']['value']);
            array_push($pay_count_list,$app_data['sum_pay_count']['value']);
        }
        $sum_pay = $today_data['pay_list'][0];
        $sum_new_user = $today_data['new_user_list'][0];
        $sum_act_user = $today_data['act_user_list'][0];
        $sum_new_device =$today_data['new_device_list'][0];
        $sum_act_device = $today_data['act_device_list'][0];
        $sum_pay_count = $today_data['pay_count_list'][0]+$today_apple_data['user_no'];
        $start_date = mktime(0,0,0);
        $end_date = mktime(0,0,0)+86400;

        $sixty_end = time();
        $sixty_start = $sixty_end - 3600;
        $act_user_60_min = $this->get_act_user($appid,$sixty_start, $sixty_end, $channels);
//        $act_user = $this->get_act_user($appid,$start_date, $end_date, $channels);
        $new_user = $this->get_new_user($appid,$start_date, $end_date, $channels);
        $act_device = $this->get_act_device($appid,$start_date, $end_date, $channels);
        $new_device = $this->get_new_device($appid,$start_date, $end_date, $channels);
        $pay_ammount = $this->DAO->get_pay_ammount($appid,$start_date, $end_date, $channels);
        $pay_count = $this->DAO->get_pay_count($appid,$start_date, $end_date, $channels);
        if(empty($pay_ammount)){
            $pay_ammount=0;
        }
        if(empty($pay_count)){
            $pay_count=0;
        }
        $arppu = 0;
        $darpu = 0;
        $pay_rate = 0;
        if($sum_pay_count){
            $arppu = round(($sum_pay)/$sum_pay_count, 2);
        }
        if($sum_act_user){
            $darpu = round(($sum_pay)/$sum_act_user, 2);
            $pay_rate = round(($sum_pay_count/$sum_act_user)*100,2);
        }
        $date = $_GET['date']?$_GET['date']:date("Y-m-d",time());
        $this->assign("date", $date);
        $this->assign("selected_channel", $_GET['channels']);
        $this->assign("apps_data", $apps_data);
        $this->assign("time_list", implode("', '",$time_list));
        $this->assign("pay_list", implode("', '",$pay_list));
        $this->assign("new_user_list", implode("', '",$new_user_list));
        $this->assign("act_user_list", implode("', '",$act_user_list));
        $this->assign("new_device_list", implode("', '",$new_device_list));
        $this->assign("act_device_list",implode("', '",$act_device_list));
        $this->assign("app_type",$app_info['app_type']);
        $this->assign("app_name", $app_info['app_name']);
        $this->assign("appid", $appid);
        $this->assign("act_user_60_min", $act_user_60_min);
//        $this->assign("act_user", $act_user);
        $this->assign("new_user", $new_user);
        $this->assign("act_device", $act_device);
        $this->assign("new_device", $new_device);
        $this->assign("arppu", $arppu);
        $this->assign("darpu", $darpu);
        $this->assign("pay_rate", $pay_rate);
        $this->assign("sum_pay", $sum_pay);
        $this->assign("sum_new_user", $sum_new_user);
        $this->assign("sum_act_user", $sum_act_user);
        $this->assign("sum_new_device", $sum_new_device);
        $this->assign("sum_act_device", $sum_act_device);
        $this->assign("sum_pay_count", $sum_pay_count);
        $this->assign("today_apple_pay",$today_apple_pay);
        $this->assign("channel_list",$channel_list);
        $this->display("rh_kpi/game_realtime.html");
    }

    //统计实时登入用户数
    public function get_act_user($app_id=0,$start=0,$end=0,$channels=''){
        $this->ES_PARAMS['index']  = 'sdk_log';
        $this->ES_PARAMS['type']  = 'super_user_login_log';
        $must = '{"term": {"AppID": "'.$app_id.'"}},
                {"range":{
                    "RecordTime":{
                        "gte":"'.$start.'",
                        "lt":"'.$end.'"
                        }}
                }';
        if(!empty($channels)){
            $must.=',{"terms":{"Channel":["'.$channels.'"]}}';
        }
        $json='{
        "size":0,
            "query":{
                "constant_score": {
                    "filter": {
                        "bool": {
                            "must":['.$must.']
                        }
                    }
                }
            },
            "aggs": {
                "distinct_user": {
                    "cardinality": {
                        "field": "UserID"
                    }
                }
            }
        }';
        $this->ES_PARAMS['body'] = $json;
        $results = $this->ES->search($this->ES_PARAMS);
        return $results["aggregations"]["distinct_user"]["value"];
    }
    //统计实时活跃设备数
    public function get_act_device($app_id=0,$start=0,$end=0,$channels=''){
        $this->ES_PARAMS['index']  = 'sdk_log';
        $this->ES_PARAMS['type']  = 'super_stats_device';
        $must = '{"term": {"AppID": "'.$app_id.'"}},
                {"range":{
                    "ActTime":{
                        "gte":"'.$start.'",
                        "lt":"'.$end.'"
                        }}
                }';
        if(!empty($channels)){
            $must.=',{"terms":{"Channel":["'.$channels.'"]}}';
        }
        $json='{
        "size":0,
        "query":{
            "constant_score": {
                "filter": {
                    "bool": {
                        "must":['.$must.']
                    }
                }
            }
        }
        }';
        $this->ES_PARAMS['body'] = $json;
        $results = $this->ES->search($this->ES_PARAMS);
        return $results['hits']['total'];
    }
    //统计实时新增设备数
    public function get_new_device($app_id=0,$start=0,$end=0,$channels=''){
        $this->ES_PARAMS['index']  = 'sdk_log';
        $this->ES_PARAMS['type']  = 'super_stats_device';
        $must = '{"term": {"AppID": "'.$app_id.'"}},
                {"range":{
                    "RegTime":{
                        "gte":"'.$start.'",
                        "lt":"'.$end.'"
                        }}
                }';
        if(!empty($channels)){
            $must.=',{"terms":{"Channel":["'.$channels.'"]}}';
        }
        $json='{
        "size":0,
        "query":{
            "constant_score": {
                "filter": {
                    "bool": {
                        "must":['.$must.']
                    }
                }
            }
        }
        }';
        $this->ES_PARAMS['body'] = $json;
        $results = $this->ES->search($this->ES_PARAMS);
        return $results['hits']['total'];
    }

    //统计实时新增账号数
    public function get_new_user($app_id=0,$start=0,$end=0,$channels=''){
        $this->ES_PARAMS['index']  = 'sdk_log';
        $this->ES_PARAMS['type']  = 'super_user_app';
        $must = '{"term": {"AppID": "'.$app_id.'"}},
                {"range":{
                    "RegTime":{
                        "gte":"'.$start.'",
                        "lt":"'.$end.'"
                        }}
                }';
        if(!empty($channels)){
            $must.=',{"terms":{"Channel":["'.$channels.'"]}}';
        }
        $json='{
        "size":0,
        "query":{
            "constant_score": {
                "filter": {
                    "bool": {
                        "must":['.$must.']
                    }
                }
            }
        },
        "aggs": {
            "distinct_user": {
                "cardinality": {
                    "field": "UserID"
                }
            }
        }
        }';
        $this->ES_PARAMS['body'] = $json;
        $results = $this->ES->search($this->ES_PARAMS);
        return $results["aggregations"]["distinct_user"]["value"];
    }

    public function get_single_app_day_data($apps= 0,$start_date="",$end_date="",$channels="",$apple_pay = []){
        if(is_array($channels)){
            $channels = implode('","',$channels);
        }
        $this->ES_PARAMS['type']  = 'rh_analyze_kpi';
        $must = '{"term":{"app_id":"'.$apps.'"}},
                {"term":{"stats_type": 1 }},
                {"range":{
                    "time":{
                        "gte":"'.$start_date.'",
                        "lt":"'.$end_date.'"
                        }}
                }';
        if(!empty($channels)){
            $must.=',{"terms":{"channel":["'.$channels.'"]}}';
        }
        $json='{
        "size":0,
        "query":{
            "constant_score": {
                "filter": {
                    "bool": {
                        "must":['.$must.']
                    }
                }
            }
        },
            "aggs": {
                "time": {
                    "terms": {
                        "field": "time",
                        "size": 0,
                        "order": {"_term": "asc"}
                    },
                    "aggs":{
                        "sum_pay": {
                            "sum": {
                                "field": "pay_money"
                            }
                        },
                        "sum_new_user": {
                            "sum": {
                                "field": "new_user"
                            }
                        },
                        "sum_new_device": {
                            "sum": {
                                "field": "new_device"
                            }
                        },
                        "sum_new_role": {
                            "sum": {
                                "field": "new_role"
                            }
                        },
                        "sum_act_user": {
                            "sum": {
                                "field": "act_user"
                            }
                        },
                        "sum_act_device": {
                            "sum": {
                                "field": "act_device"
                            }
                        },
                        "sum_pay_count": {
                            "sum": {
                                "field": "pay_count"
                            }
                        },
                        "sum_keep_next": {
                            "sum": {
                                "field": "keep_next"
                            }
                        },
                        "sum_keep_two": {
                            "sum": {
                                "field": "keep_two"
                            }
                        },
                        "sum_keep_third": {
                            "sum": {
                                "field": "keep_third"
                            }
                        },
                        "sum_keep_four": {
                            "sum": {
                                "field": "keep_four"
                            }
                        },
                        "sum_keep_five": {
                            "sum": {
                                "field": "keep_five"
                            }
                        },
                        "sum_keep_six": {
                            "sum": {
                                "field": "keep_six"
                            }
                        },
                        "sum_keep_seven": {
                            "sum": {
                                "field": "keep_seven"
                            }
                        },
                        "sum_keep_eight": {
                            "sum": {
                                "field": "keep_eight"
                            }
                        },
                        "sum_keep_ten": {
                            "sum": {
                                "field": "keep_ten"
                            }
                        },
                        "sum_keep_twelve": {
                            "sum": {
                                "field": "keep_twelve"
                            }
                        },
                        "sum_keep_fourteen": {
                            "sum": {
                                "field": "keep_fourteen"
                            }
                        },
                        "sum_keep_sixteen": {
                            "sum": {
                                "field": "keep_sixteen"
                            }
                        },
                        "sum_keep_twenty": {
                            "sum": {
                                "field": "keep_twenty"
                            }
                        },
                        "sum_keep_thirty": {
                            "sum": {
                                "field": "keep_thirty"
                            }
                        }
                    }
                }
            }
        }';
        $this->ES_PARAMS['body'] = $json;
        $results = $this->ES->search($this->ES_PARAMS);

        $total_pay = 0;
        $total_act_user = 0;
        $total_new_user = 0;
        $total_act_device = 0;
        $total_new_device = 0;
        $date_list = array();
        $short_date_list = array();
        $pay_list = array();
        $new_user_list = array();
        $act_user_list = array();
        $new_device_list = array();
        $act_device_list = array();
        $next_list = array();
        $two_list = array();
        $third_list = array();
        $four_list = array();
        $five_list = array();
        $six_list = array();
        $seven_list = array();
        $eight_list = array();
        $ten_list = array();
        $twelve_list = array();
        $fourteen_list = array();
        $sixteen_list = array();
        $twenty_list = array();
        $thirty_list = array();
        $pay_count_list = array();
        $pay_list_graph = array();
        $sum_new_user = array();
        $sum_act_user = array();
        $sum_keep_next = array();
        $sum_keep_two = array();
        $sum_keep_third = array();
        $sum_keep_four = array();
        $sum_keep_five = array();
        $sum_keep_six = array();
        $sum_keep_seven = array();
        $sum_keep_eight = array();
        $sum_keep_ten = array();
        $sum_keep_twelve = array();
        $sum_keep_fourteen = array();
        $sum_keep_sixteen = array();
        $sum_keep_twenty = array();
        $sum_keep_thirty = array();
        foreach($results['aggregations']['time']['buckets'] as $key=>$source){
            array_push($date_list,substr($source['key_as_string'],0,10));
            array_push($short_date_list,substr($source['key_as_string'],5,5));
            array_push($pay_count_list,$source['sum_pay_count']['value']);
            array_push($pay_list_graph,$source['sum_pay']['value']);
            array_push($pay_list,$source['sum_pay']['value']);
            $total_pay = $total_pay + $source['sum_pay']['value'];
            array_push($sum_new_user,$source['sum_new_user']['value']);
            array_push($sum_act_user,$source['sum_act_user']['value']);
            array_push($new_user_list,$source['sum_new_user']['value']);
            $total_new_user = $total_new_user + $source['sum_new_user']['value'];
            array_push($act_user_list,$source['sum_act_user']['value']);
            $total_act_user = $total_act_user + $source['sum_act_user']['value'];
            array_push($act_device_list,$source['sum_act_device']['value']);
            $total_act_device = $total_act_device + $source['sum_act_device']['value'];
            array_push($new_device_list,$source['sum_new_device']['value']);
            $total_new_device = $total_new_device + $source['sum_new_device']['value'];
            array_push($next_list,$source['sum_keep_next']['value']);
            array_push($two_list,$source['sum_keep_two']['value']);
            array_push($third_list,$source['sum_keep_third']['value']);
            array_push($four_list,$source['sum_keep_four']['value']);
            array_push($five_list,$source['sum_keep_five']['value']);
            array_push($six_list,$source['sum_keep_six']['value']);
            array_push($seven_list,$source['sum_keep_seven']['value']);
            array_push($eight_list,$source['sum_keep_eight']['value']);
            array_push($ten_list,$source['sum_keep_ten']['value']);
            array_push($twelve_list,$source['sum_keep_twelve']['value']);
            array_push($fourteen_list,$source['sum_keep_fourteen']['value']);
            array_push($sixteen_list,$source['sum_keep_sixteen']['value']);
            array_push($twenty_list,$source['sum_keep_twenty']['value']);
            array_push($thirty_list,$source['sum_keep_thirty']['value']);
            if($source['sum_new_user']['value'] == 0){
                $next_rate = 0;
                $two_rate = 0;
                $third_rate = 0;
                $four_rate = 0;
                $five_rate = 0;
                $six_rate = 0;
                $seven_rate = 0;
                $eight_rate = 0;
                $ten_rate = 0;
                $twelve_rate = 0;
                $fourteen_rate = 0;
                $sixteen_rate = 0;
                $twenty_rate = 0;
                $thirty_rate = 0;
            }else{
                $next_rate = $source['sum_keep_next']['value']/$source['sum_new_user']['value']*100;
                $two_rate = $source['sum_keep_two']['value']/$source['sum_new_user']['value']*100;
                $third_rate = $source['sum_keep_third']['value']/$source['sum_new_user']['value']*100;
                $four_rate = $source['sum_keep_four']['value']/$source['sum_new_user']['value']*100;
                $five_rate = $source['sum_keep_five']['value']/$source['sum_new_user']['value']*100;
                $six_rate = $source['sum_keep_six']['value']/$source['sum_new_user']['value']*100;
                $seven_rate = $source['sum_keep_seven']['value']/$source['sum_new_user']['value']*100;
                $eight_rate = $source['sum_keep_eight']['value']/$source['sum_new_user']['value']*100;
                $ten_rate = $source['sum_keep_ten']['value']/$source['sum_new_user']['value']*100;
                $twelve_rate = $source['sum_keep_twelve']['value']/$source['sum_new_user']['value']*100;
                $fourteen_rate = $source['sum_keep_fourteen']['value']/$source['sum_new_user']['value']*100;
                $sixteen_rate = $source['sum_keep_sixteen']['value']/$source['sum_new_user']['value']*100;
                $twenty_rate = $source['sum_keep_twenty']['value']/$source['sum_new_user']['value']*100;
                $thirty_rate = $source['sum_keep_thirty']['value']/$source['sum_new_user']['value']*100;
            }
            array_push($sum_keep_next,round($next_rate,2));
            array_push($sum_keep_two,round($two_rate,2));
            array_push($sum_keep_third,round($third_rate,2));
            array_push($sum_keep_four,round($four_rate,2));
            array_push($sum_keep_five,round($five_rate,2));
            array_push($sum_keep_six,round($six_rate,2));
            array_push($sum_keep_seven,round($seven_rate,2));
            array_push($sum_keep_eight,round($eight_rate,2));
            array_push($sum_keep_ten,round($ten_rate,2));
            array_push($sum_keep_twelve,round($twelve_rate,2));
            array_push($sum_keep_fourteen,round($fourteen_rate,2));
            array_push($sum_keep_sixteen,round($sixteen_rate,2));
            array_push($sum_keep_twenty,round($twenty_rate,2));
            array_push($sum_keep_thirty,round($thirty_rate,2));
        }
        //逆向排序
        array_reverse($date_list);
        array_reverse($pay_list);
        array_reverse($new_user_list);
        array_reverse($act_user_list);
        array_reverse($new_device_list);
        array_reverse($act_device_list);
        array_reverse($next_list);
        array_reverse($two_list);
        array_reverse($third_list);
        array_reverse($four_list);
        array_reverse($five_list);
        array_reverse($six_list);
        array_reverse($seven_list);
        array_reverse($eight_list);
        array_reverse($ten_list);
        array_reverse($twelve_list);
        array_reverse($fourteen_list);
        array_reverse($sixteen_list);
        array_reverse($twenty_list);
        array_reverse($thirty_list);
        array_reverse($pay_count_list);
        return array(
            "date_list" => $date_list,
            "short_date_list" => implode("', '",$short_date_list),
            "pay_list" => $pay_list,
            "new_user_list" => $new_user_list,
            "act_user_list" => $act_user_list,
            "new_device_list" => $new_device_list,
            "act_device_list" => $act_device_list,
            "pay_count_list" => $pay_count_list,
            "next_list" => $next_list,
            "third_list" => $third_list,
            "two_list" => $two_list,
            "four_list" => $four_list,
            "five_list" => $five_list,
            "six_list" => $six_list,
            "eight_list" => $eight_list,
            "ten_list" => $ten_list,
            "twelve_list" => $twelve_list,
            "sixteen_list" => $sixteen_list,
            "twenty_list" => $twenty_list,
            "seven_list" => $seven_list,
            "fourteen_list" => $fourteen_list,
            "thirty_list" => $thirty_list,
            "total_pay" => $total_pay,
            "total_new_user" => $total_new_user,
            "total_act_user" => $total_act_user,
            "total_act_device" => $total_act_device,
            "total_new_device" => $total_new_device,
            "sum_keep_next" => implode("', '",$sum_keep_next),
            "sum_keep_third" => implode("', '",$sum_keep_third),
            "sum_keep_seven" => implode("', '",$sum_keep_seven),
            "sum_keep_fourteen" => implode("', '",$sum_keep_fourteen),
            "sum_keep_thirty" => implode("', '",$sum_keep_thirty),
            "pay_list_graph" => implode("', '",$pay_list_graph),
            "js_sum_new_user" => implode("', '",$sum_new_user),
            "sum_new_user" => $sum_new_user,
            "js_sum_act_user" => implode("', '",$sum_act_user),
            "sum_act_user" => $sum_act_user
        );
    }

    //游戏24小时综合数据
    public function get_apps_hour_data($year='',$month='',$day='',$app_list='',$channels=''){
        if(is_array($app_list)) {
            $app_list = implode('","', $app_list);
        }
        if(is_array($channels)){
            $channels = implode('","',$channels);
        }
        $this->ES_PARAMS['type']  = 'rh_analyze_kpi';
        $must = '{"terms":{"app_id":["'.$app_list.'"]}},
                {"term":{"year":"'.$year.'"}},
                {"term":{"month":"'.$month.'"}},
                {"term":{"day":"'.$day.'"}},
                {"term":{"stats_type":"2"}}';
        if($channels){
            $must.=',{"terms":{"channel":["'.$channels.'"]}}';
        }
        $json='{
            "size":0,
            "query":{
                "constant_score": {
                    "filter": {
                        "bool": {
                            "must":['.$must.']
                        }
                    }
                }
            },
            "aggs": {
                "hour": {
                    "terms": {
                        "field": "hour",
                        "size": 0,
                        "order": {"_term": "asc"}
                    },
                    "aggs":{
                        "sum_pay": {
                            "sum": {
                                "field": "pay_money"
                            }
                        },
                        "sum_new_user": {
                            "sum": {
                                "field": "new_user"
                            }
                        },
                        "sum_new_device": {
                            "sum": {
                                "field": "new_device"
                            }
                        },
                        "sum_new_role": {
                            "sum": {
                                "field": "new_role"
                            }
                        },
                        "sum_act_user": {
                            "sum": {
                                "field": "act_user"
                            }
                        },
                        "sum_act_device": {
                            "sum": {
                                "field": "act_device"
                            }
                        },
                        "sum_pay_count": {
                            "sum": {
                                "field": "pay_count"
                            }
                        },
                        "sum_keep_next": {
                            "sum": {
                                "field": "keep_next"
                            }
                        },
                        "sum_keep_third": {
                            "sum": {
                                "field": "keep_third"
                            }
                        },
                        "sum_keep_seven": {
                            "sum": {
                                "field": "keep_seven"
                            }
                        },
                        "sum_keep_fourteen": {
                            "sum": {
                                "field": "keep_fourteen"
                            }
                        },
                        "sum_keep_thirty": {
                            "sum": {
                                "field": "keep_thirty"
                            }
                        }
                    }
                }
            }
        }';
        $this->ES_PARAMS['body'] = $json;
        $results = $this->ES->search($this->ES_PARAMS);
        return $results['aggregations']['hour']['buckets'];
    }

    public function get_app_day_data($app_list='',$start_date='',$end_date='',$channels=''){
        $app_list = implode('","',$app_list);
        if(is_array($channels)){
            $channels = implode('","',$channels);
        }
        $this->ES_PARAMS['type']  = 'rh_analyze_kpi';
        $must = '{"terms":{"app_id": ["'.$app_list.'"] }},
                {"term":{"stats_type": 1 }},
                {"range":{
                    "time":{
                        "gte":"'.$start_date.'",
                        "lt":"'.$end_date.'"
                        }}
                }';

        if(!empty($channels)){
            $must.=',{"terms":{"channel":["'.$channels.'"]}}';
        }
        $json='{
        "size":0,
        "query":{
            "constant_score": {
                "filter": {
                    "bool": {
                        "must":['.$must.']
                    }
                }
            }
        },
        "aggs": {
                "app_id": {
                    "terms": {
                        "field": "app_id",
                        "size": 0
                    },
                    "aggs":{
                        "sum_pay": {
                            "sum": {
                                "field": "pay_money"
                            }
                        },
                        "sum_new_user": {
                            "sum": {
                                "field": "new_user"
                            }
                        },
                        "sum_new_device": {
                            "sum": {
                                "field": "new_device"
                            }
                        },
                        "sum_new_role": {
                            "sum": {
                                "field": "new_role"
                            }
                        },
                        "sum_act_user": {
                            "sum": {
                                "field": "act_user"
                            }
                        },
                        "sum_act_device": {
                            "sum": {
                                "field": "act_device"
                            }
                        },
                        "sum_pay_count": {
                            "sum": {
                                "field": "pay_count"
                            }
                        },
                        "sum_keep_next": {
                            "sum": {
                                "field": "keep_next"
                            }
                        },
                        "sum_keep_third": {
                            "sum": {
                                "field": "keep_third"
                            }
                        },
                        "sum_keep_seven": {
                            "sum": {
                                "field": "keep_seven"
                            }
                        },
                        "sum_keep_fourteen": {
                            "sum": {
                                "field": "keep_fourteen"
                            }
                        },
                        "sum_keep_thirty": {
                            "sum": {
                                "field": "keep_thirty"
                            }
                        }
                    }
                }
            }
        }';
        $this->ES_PARAMS['body'] = $json;
        $results = $this->ES->search($this->ES_PARAMS);
        return $results['aggregations']['app_id']['buckets'];
    }


    public function get_apps_trend_data($app_list='',$start_date='',$end_date='',$source='',$channels=''){
        $app_list = implode('","',$app_list);
        if(is_array($channels)){
            $channels = implode('","',$channels);
        }
        $this->ES_PARAMS['type']  = 'rh_analyze_kpi';
        $must = '{"terms":{"app_id":["'.$app_list.'"]}},
                {"term": {"stats_type": 1}},
                {"range":{
                    "time":{
                        "gte":"'.$start_date.'",
                        "lt":"'.$end_date.'"
                        }}
                }';
        if(!empty($channels)){
            $must.=',{"terms":{"channel":["'.$channels.'"]}}';
        }
        $json='{
        "size":0,
        "query":{
            "constant_score": {
                "filter": {
                    "bool": {
                        "must":['.$must.']
                    }
                }
            }
        },
        "aggs": {
                "time": {
                    "terms": {
                        "field": "time",
                        "size": 0,
                        "order": {"_term": "asc"}
                    },
                    "aggs":{
                        "sum_source": {
                            "sum": {
                                "field": "'.$source.'"
                            }
                        }
                    }
                }
            }
        }';
        $this->ES_PARAMS['body'] = $json;
        $results = $this->ES->search($this->ES_PARAMS);
        $total = 0;
        $data_list = array();;
        $date_list = array();;
        $date_list_long = array();;

        foreach($results['aggregations']['time']['buckets'] as $key=>$item){
            array_push($data_list,$item['sum_source']['value']);
            array_push($date_list,substr($item['key_as_string'],5,5));
            array_push($date_list_long,substr($item['key_as_string'],0,10));
            $total = $total + $item['sum_source']['value'];
        }
        return array(
            "data_list"=>$data_list,
            "date_list"=>$date_list,
            "data_list_profit"=>$data_list,
            "data_list_cost"=>$data_list,
            "date_list_long"=>$data_list,
            "total"=>$total
        );
    }

    public function get_latest_seven_days_data_kpi($start = 0, $apps='', $channel_list=''){
        $day1 = time() - (86400 * $start);
        $day1_data = $this->get_apps_day_data($apps, date('Y', $day1), date('m', $day1), date('d', $day1), 1, $channel_list);
        $day2 = time() - (86400 * ($start + 1));
        $day2_data = $this->get_apps_day_data($apps, date('Y', $day2), date('m', $day2), date('d', $day2), 1, $channel_list);
        $day3 = time() - (86400 * ($start + 2));
        $day3_data = $this->get_apps_day_data($apps, date('Y', $day3), date('m', $day3), date('d', $day3), 1, $channel_list);
        $day4 = time() - (86400 * ($start + 3));
        $day4_data = $this->get_apps_day_data($apps, date('Y', $day4), date('m', $day4), date('d', $day4), 1, $channel_list);
        $day5 = time() - (86400 * ($start + 4));
        $day5_data = $this->get_apps_day_data($apps, date('Y', $day5), date('m', $day5), date('d', $day5), 1, $channel_list);
        $day6 = time() - (86400 * ($start + 5));
        $day6_data = $this->get_apps_day_data($apps, date('Y', $day6), date('m', $day6), date('d', $day6), 1, $channel_list);
        $day7 = time() - (86400 * ($start + 6));
        $day7_data = $this->get_apps_day_data($apps, date('Y', $day7), date('m', $day7), date('d', $day7), 1, $channel_list);

        return [$day1_data, $day2_data, $day3_data, $day4_data, $day5_data, $day6_data, $day7_data];
    }

    public function get_apps_day_data($app_list = '',$year = 0, $month = 0, $day = 0,  $stats_type = 1, $channels = ''){
        $start_day = strtotime(($year."-".$month."-".$day." 00:00:00" ));
        $end_day = strtotime(($year.'-'.$month."-".$day." 24:00:00" ));
        $app = implode(',',$app_list);
        $app_list = implode('","',$app_list);
        if(is_array($channels)){
            $channels = implode('","',$channels);
        }
        $this->ES_PARAMS['type']  = 'rh_analyze_kpi';
        $must = '{"terms":{"app_id":["'.$app_list.'"]}},
        {"term":{"year":"'.$year.'"}},
        {"term":{"month":"'.$month.'"}},
        {"term":{"day":"'.$day.'"}},
        {"term":{"stats_type":"'.$stats_type.'"}}';
        if(!empty($channels)){
            $must.=',{"terms":{"channel":["'.$channels.'"]}}';
        }
        $json='{
        "size":0,
        "query":{
            "constant_score": {
                "filter": {
                    "bool": {
                        "must":['.$must.']
                    }
                }
            }
        },
        "aggs": {
                "sum_pay": {
                    "sum": {
                        "field": "pay_money"
                    }
                },
                "sum_new_user": {
                    "sum": {
                        "field": "new_user"
                    }
                },
                "sum_new_device": {
                    "sum": {
                        "field": "new_device"
                    }
                },
                "sum_new_role": {
                    "sum": {
                        "field": "new_role"
                    }
                },
                "sum_act_user": {
                    "sum": {
                        "field": "act_user"
                    }
                },
                "sum_act_device": {
                    "sum": {
                        "field": "act_device"
                    }
                },
                "sum_pay_count": {
                    "sum": {
                        "field": "pay_count"
                    }
                },
                "sum_keep_next": {
                    "sum": {
                        "field": "keep_next"
                    }
                },
                "sum_keep_third": {
                    "sum": {
                        "field": "keep_third"
                    }
                },
                "sum_keep_seven": {
                    "sum": {
                        "field": "keep_seven"
                    }
                },
                "sum_keep_fourteen": {
                    "sum": {
                        "field": "keep_fourteen"
                    }
                },
                "sum_keep_thirty": {
                    "sum": {
                        "field": "keep_thirty"
                    }
                }
            }
        }';
        $this->ES_PARAMS['body'] = $json;
        $results = $this->ES->search($this->ES_PARAMS);
        $data = array();
        foreach ($results['aggregations'] as $key=>$val) {
            $data[$key] = $val['value'];
        }
        $order = $this->DAO->get_lastest_seven_days_order($start_day,$end_day,$app,$channels);
        $data['sum_order_count'] = $order['num'];
        return $data;
    }


    public function get_rh_app_id($app_id=""){
        if(!empty($app_id)){
            return $app_id;
        }else{
            $apps = $this->DAO->get_rh_apps();
            $apps_list = array();
            foreach($apps as $item){
                if($_SESSION['usr_id'] == '712' or $_SESSION['usr_id'] == '713'){
                    $user_app = array('7026','7033');
                    if(in_array($item['app_id'],$user_app)){
                        array_push($apps_list,$item['app_id']);
                    }
                }else if($_SESSION['usr_id'] == '727'){
                    $user_app = array('7032','7033');
                    if(in_array($item['app_id'],$user_app)){
                        array_push($apps_list,$item['app_id']);
                    }
                }elseif($_SESSION['usr_id'] == '762'){
                    $user_app = array('7033');
                    if(in_array($item['app_id'],$user_app)){
                        array_push($apps_list,$item['app_id']);
                    }
                }else{
                    array_push($apps_list,$item['app_id']);
                }
            }
            $app_id = implode(",", $apps_list);
            return $app_id;
        }
    }

    public function get_rh_channel($channel){
        if($_SESSION['usr_id'] == '762'){
            $channel = 'quick';
        }
        if(empty($channel)){
            return "";
        }
        $rh_app_dao = new rh_app_admin_dao();
        $channels = $rh_app_dao->get_channel_info($channel);
        if(!$channels){
            return "";
        }
        return $channel;
    }

    public function all_channel($appid){
        //获取所有APPS
        $rh_app_dao = new rh_app_admin_dao();
        $apps = $rh_app_dao->get_all_rh_apps();
        $app_info = $this->DAO->get_rh_app_info($appid);
        //获取所有渠道
        $user_info = $rh_app_dao->get_all_ch();
        $start_date = date("Y-m-d",strtotime("-6 day"));
        $end_date = date('Y-m-d', time());
        //6天前的日期    2016-01-06
        if($_POST['start_date']){
            $start_date = $_POST['start_date'];
        }
        if($_POST['end_date']){
            $end_date = $_POST['end_date'];
        }
        $app_data_channel_all = array();
        $channel_all = '';
        $sum_new_user_all = '';
        $sum_act_user_all = '';
        foreach ($user_info as $val){
            $app_data = $this->get_app_channel_data($appid,$start_date,$end_date,$val['ch_code']);
            if(empty($app_data['result'])) continue;
            $sum_new_user = 0;
            $sum_act_user = 0;
            $sum_new_device = 0;
            $sum_new_role = 0;
            $sum_pay_count = 0;
            $sum_pay = 0;
            foreach ($app_data['result'] as $key =>$data){
                $sum_new_user += $app_data['result'][$key]['sum_new_user']['value'];
                $sum_act_user += $app_data['result'][$key]['sum_act_user']['value'];
                $sum_new_device += $app_data['result'][$key]['sum_new_device']['value'];
                $sum_new_role += $app_data['result'][$key]['sum_new_role']['value'];
                $sum_pay_count += $app_data['result'][$key]['sum_pay_count']['value'];
                $sum_pay += $app_data['result'][$key]['sum_pay']['value'];
            }
            $app_data_channel = array("channel"=>$val['ch_code'],"sum_new_user"=>$sum_new_user,"sum_new_device"=>$sum_new_device,"sum_new_role"=>$sum_new_role,"sum_pay_count"=>$sum_pay_count,
                "sum_pay"=>$sum_pay);
            array_push($app_data_channel_all,$app_data_channel);
            $channel_all .= $val['ch_code']."','";
            $sum_new_user_all .= $sum_new_user."','";
            $sum_act_user_all .= $sum_act_user."','";
        }
        $channel_all = trim($channel_all,"','");
        $sum_new_user_all = trim($sum_new_user_all,"','");
        $sum_act_user_all = trim($sum_act_user_all,"','");
        $this->assign("app_data_all",$app_data_channel_all);
        $this->assign("channel_all",$channel_all);
        $this->assign("sum_new_user_all",$sum_new_user_all);
        $this->assign("sum_act_user_all",$sum_act_user_all);
        $this->assign("app_name", $app_info['app_name']);
        $this->assign("appid", $appid);
        $this->assign("apps",$apps);
        $this->assign("start_date", $start_date);
        $this->assign("end_date", $end_date);
        $this->display("rh_kpi/game_all_channel.html");
    }

    public function get_app_channel_data($app_list='',$start_date='',$end_date='',$channels){
        $this->ES_PARAMS['type']  = 'rh_analyze_kpi';
        $must = '{"terms":{"app_id":["'.$app_list.'"]}},
        {"term":{"stats_type": 1 }},
        {"range":{"time":{
            "gte": "'.$start_date.'",
            "lt": "'.$end_date.'"
        }}}';
        if($channels=='nnwl'){
            $must.=',{"wildcard":{"channel":"'.$channels.'"}}';
        }else{
            $must.=',{"wildcard":{"channel":"'.$channels.'*"}}';
        }

        $json='{"size": 0,
            "query": {
                "constant_score": {
                    "filter": {
                        "bool": {
                            "must":['.$must.']
                        }
                    }
                }
            },
            "aggs": {
                "channel": {
                    "terms": {
                        "field": "channel",
                        "size": 0,
                        "order": {"sum_new_user":"desc"}
                    },
                    "aggs":{
                        "sum_pay": {
                            "sum": {
                                "field": "pay_money"
                            }
                        },
                        "sum_new_user": {
                            "sum": {
                                "field": "new_user"
                            }
                        },
                        "sum_new_device": {
                            "sum": {
                                "field": "new_device"
                            }
                        },
                        "sum_new_role": {
                            "sum": {
                                "field": "new_role"
                            }
                        },
                        "sum_act_user": {
                            "sum": {
                                "field": "act_user"
                            }
                        },
                        "sum_act_device": {
                            "sum": {
                                "field": "act_device"
                            }
                        },
                        "sum_pay_count": {
                            "sum": {
                                "field": "pay_count"
                            }
                        },
                        "sum_keep_next": {
                            "sum": {
                                "field": "keep_next"
                            }
                        },
                        "sum_keep_third": {
                            "sum": {
                                "field": "keep_third"
                            }
                        },
                        "sum_keep_seven": {
                            "sum": {
                                "field": "keep_seven"
                            }
                        },
                        "sum_keep_fourteen": {
                            "sum": {
                                "field": "keep_fourteen"
                            }
                        },
                        "sum_keep_thirty": {
                            "sum": {
                                "field": "keep_thirty"
                            }
                        }
                    }
                }
            }
        }';
        $this->ES_PARAMS['body'] = $json;
        $results = $this->ES->search($this->ES_PARAMS);
//        $sum_act_user =  array();
//        $sum_new_user =  array();
//        $sum_user_channel = array();
//        $sum_act_others = 0;
//        $sum_new_others = 0;
//        foreach($results['aggregations']['channel']['buckets'] as $key=>$data){
//            if(strpos($data['key'],"nnfb")==0){
//                if(count($sum_act_user) <= 30){
//                    array_push($sum_user_channel,$data['key']);
//                    array_push($sum_act_user,$data['sum_act_user']['value']);
//                    array_push($sum_new_user,$data['sum_new_user']['value']);
//                }else{
//                    $sum_act_others += $data['sum_act_user']['value'];
//                    $sum_new_others += $data['sum_new_user']['value'];
//                }
//            }
//        }
//        if(count($sum_act_user) > 30){
//            array_push($sum_user_channel,'others');
//            array_push($sum_act_user,$sum_act_others);
//            array_push($sum_new_user,$sum_new_others);
//        }

        $rquest = array(
            "result" =>$results['aggregations']['channel']['buckets'],
//            "sum_user_channel" =>$sum_user_channel,
//            "sum_act_user" =>$sum_act_user,
//            "sum_new_user" =>$sum_new_user
        );
        return $rquest;

    }

}