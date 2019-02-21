<?php
COMMON('adminBaseCore','pageCore','uploadHelper');
DAO('kpi_admin_dao','menu_admin_dao','app_admin_dao');

class kpi_admin extends adminBaseCore{
    public $DAO;
    public $ES;
    public $ES_PARAMS;

    public function __construct() {
        parent::__construct();

        $hosts = [ ES_HOST.":".ES_PORT];
        $this->ES = Elasticsearch\ClientBuilder::create()->setHosts($hosts)->build();
        $this->ES_PARAMS = array();
        $this->ES_PARAMS['index'] = 'sdk_log';
        $this->DAO = new kpi_admin_dao();
    }

    public function index_view(){
        $app_dao = new app_admin_dao();
        $apps = $app_dao->get_all_app();
        if($_SESSION['group_id'] == 1 || $_SESSION['group_id'] == 12 ){
            $guild_list = $this->DAO->get_guild_list();
            $this->assign("apps", $apps);
        }elseif($_SESSION['group_id'] == 10){
            $app_list = array();
            $guild_info['apps'] = explode(",", $_SESSION['apps']);
            foreach ($apps as $key => $data) {
                if (in_array($data['app_id'], $guild_info['apps'])) {
                    array_push($app_list, $data);
                }
            }
            $guild_list = $this->DAO->get_guild_code($_SESSION['usr_id']);
            $this->assign("apps", $app_list);
        }elseif($_SESSION['group_id'] == 7 || $_SESSION['group_id'] == 6){
            $app_list = array();
            $guild_info['apps'] = explode(",", $_SESSION['apps']);
            foreach ($apps as $key => $data) {
                if (in_array($data['app_id'], $guild_info['apps'])) {
                    array_push($app_list, $data);
                }
            }
            $guild_list = $this->DAO->get_guild_list();
            $this->assign("apps", $app_list);
        }
        $this->assign("guild_list", $guild_list);
        $this->display("kpi/index.html");
    }

    public function information_view(){
        if($_SESSION['group_id']==1 || $_SESSION['group_id']==12){
            $apps = $this->DAO->get_apps_list($this->page,$_POST['app_id']);
            $app_list = $this->DAO->get_apps();
            if($_SESSION['group_id']==12){
                $app_id = $_SESSION['apps'];
                $app_list = $this->DAO->get_apps($app_id);
                $apps = $this->DAO->get_list($this->page,$app_id,$_POST['app_id']);
            }
            $page = $this->pageshow($this->page,"kpi.php?act=information&");
            $this->assign("page_bar", $page->show());
            $this->assign("app_id",$_POST['app_id']);
            $this->assign("app_list", $app_list);
            $this->assign("apps", $apps);
            $this->display("kpi/information_view.html");
        }else{
            die("对不起你没有该权限");
        }
    }

    public function get_app_id($app_id=""){
        if(!empty($app_id)){
            return $app_id;
        }else{
            if($_SESSION['group_id']==1){
                $apps = $this->DAO->get_apps();
                $apps_list=array();
                foreach($apps as $item){
                    array_push($apps_list,$item['app_id']);
                }
                $app_id = implode(",", $apps_list);
            }else{
                $app_id = $_SESSION['apps'];
            }
            return $app_id;
        }
    }

    public function get_channel($channels=""){
        if(!empty($channels)){
            return $channels;
        }else{
            if($_SESSION['group_id']==1 || $_SESSION['group_id']==7 || $_SESSION['group_id']==6){
                $channel = "";
            }else{
                $user_info = $this->DAO->get_user_apps($_SESSION['usr_id']);
                $channel = $user_info['user_code'];
            }
            return $channel;
        }
    }

    public function idx_group_data(){
        $app_id = $this->get_app_id($_GET['appids']);
        $channel = $this->get_channel($_GET['channels']);
        $apps = explode(",", $app_id);
        $channels = $this->get_guild_channel($channel);
        $day_diff = $_POST['day_diff'];
        $latest_seven_days_data = $this->get_latest_seven_days_data_kpi($day_diff,$apps,$channels);
        $all_sum_new_user = 0;
        $all_sum_new_device = 0;
        $all_sum_act_user = 0;
        $all_sum_act_device = 0;
        $all_sum_pay = 0;
        $all_sum_pay_count = 0;
        $all_sum_order_count = 0;
        $all_apple_user_no = 0;
        $all_apple_order_count = 0;
        $all_apple_pay_count = 0;
        if ($latest_seven_days_data) {
            foreach ($latest_seven_days_data as $key => $days_data) {
                $all_sum_new_user = $all_sum_new_user + $days_data['sum_new_user'];
                $all_sum_new_device = $all_sum_new_device + $days_data['sum_new_device'];
                $all_sum_act_user = $all_sum_act_user + $days_data['sum_act_user'];
                $all_sum_act_device = $all_sum_act_device + $days_data['sum_act_device'];
                $all_sum_pay = $all_sum_pay + $days_data['sum_pay'];
                $all_sum_pay_count = $all_sum_pay_count + $days_data['sum_pay_count'];
                $all_sum_order_count = $all_sum_order_count + $days_data['sum_order_count'];
                $all_apple_user_no += $days_data['apple_user_no'];
                $all_apple_order_count += $days_data['apple_order_count'];
                $all_apple_pay_count += $days_data['apple_pay_count'];
            }
        }
        $day1_num = -(6 + $day_diff)." day";
        $day7_num = -(0 + $day_diff)." day";
        $day1=strtotime(date('Y-m-d',time()).$day1_num);
        $day7=strtotime(date('Y-m-d',time()).$day7_num);
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
        $this->assign("all_apple_user_no",$all_apple_user_no);
        $this->assign("all_apple_order_count",$all_apple_order_count);
        $this->assign("all_apple_pay_count",$all_apple_pay_count);
        $this->assign("latest_seven_days_data", $latest_seven_days_data);
        $this->display("kpi/idx_group_view.html");
    }

    public function idx_trend_data(){
        $app_id = $this->get_app_id($_GET['appids']);
        $channel = $this->get_channel($_GET['channels']);
        $apps = explode(",", $app_id);
        $channels = $this->get_guild_channel($channel);
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
                $end_date = date("Y-m-d",mktime(23,59,59,date("m"),date("d")-date("w")+8-7,date("Y")));
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
        $this->display("kpi/idx_trend_view.html");
    }

    public function idx_game_data(){
        $app_id = $this->get_app_id($_GET['appids']);
        $channel = $this->get_channel($_GET['channels']);
        $apps = explode(",", $app_id);
        $channel = $this->get_guild_channel($channel);
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
                $end_date = date("Y-m-d",mktime(23,59,59,date("m"),date("d")-date("w")+8-7,date("Y")));
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
        $all_apple_pay = 0;
        $all_apple_orders = 0;
        if ($apps_data) {
            foreach ($apps_data as $key => $days_data) {
                if(!($days_data['key'] ==1000 || $days_data['key'] == 1001)){
                    $app_info = $this->DAO->get_app_info($days_data['key']);
                    if($app_info){
                        $apple_pay_data = $this->DAO->get_apple_pay_order_by_app_id(array("start_time"=>strtotime($start_date),
                            "end_time"=>(strtotime($end_date)+24*3600),"app_id"=>$days_data['key']));
                        if ($apple_pay_data['app_pay_money']){
                            $apps_data[$key]['apple_pay'] = $apple_pay_data['app_pay_money'];
                        }else{
                            $apps_data[$key]['apple_pay'] = 0;
                        }
                        $apps_data[$key]['apple_orders'] = $apple_pay_data['app_count'];
                        $all_apple_pay += $apps_data[$key]['apple_pay'];
                        $all_apple_orders += $apps_data[$key]['apple_orders'];
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
        $this->assign("all_apple_pay",$all_apple_pay);
        $this->assign("all_apple_orders",$all_apple_orders);
        $this->assign("date_type", $params['date_type']);
        $this->assign("app_id", $app_id);
        $this->assign("channel", $_GET['channels']);
        $this->assign("date_start", date('Y-m-d',time()));
        $this->assign("date_end", date('Y-m-d',time()));
        $this->display("kpi/idx_game_view.html");
    }

    public function detail($appid){
        $app_info = $this->DAO->get_app_info($appid);
        if($_POST['channels']){
            $channels = $_POST['channels'];
        }else{
            if ($app_info['app_type']==1){
                $channels = $this->get_channel($_GET['channels']);
            }elseif ($app_info['app_type']==2){
                $channels = "";
            }
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
        if ($app_info['app_type']==1){
            $channel = $this->get_guild_channel($channels);
        }elseif ($app_info['app_type']==2){
            $channel = $channels;
        }
        $app_dao = new app_admin_dao();
        $apps = $app_dao->get_all_app();
        if($_SESSION['group_id'] == 1){
            $guild_list = $this->DAO->get_guild_list();
            $this->assign("apps", $apps);
        }elseif($_SESSION['group_id'] == 10){
            $app_list = array();
            $guild_info['apps'] = explode(",", $_SESSION['apps']);
            foreach ($apps as $key => $data) {
                if (in_array($data['app_id'], $guild_info['apps'])) {
                    array_push($app_list, $data);
                }
            }
            $guild_list = $this->DAO->get_guild_code($_SESSION['usr_id']);
            $this->assign("apps", $app_list);
        }elseif($_SESSION['group_id'] == 7){
            $app_list = array();
            $guild_info['apps'] = explode(",", $_SESSION['apps']);
            foreach ($apps as $key => $data) {
                if (in_array($data['app_id'], $guild_info['apps'])) {
                    array_push($app_list, $data);
                }
            }
            $guild_list = $this->DAO->get_guild_list();
            $this->assign("apps", $app_list);
        }
        //查询苹果充值明细
        $apple_res_date = $this->DAO->get_apple_pay_by_date(array(
            "app_id"=>$appid,
            "start_time"=>strtotime($start_date),
            "end_time"=>(strtotime($end_date)+24*3600)
        ));
        $app_data = $this->get_single_app_day_data($appid,$start_date,$end_date,$channel,$_POST,$apple_res_date);
        $extension_list = $this->DAO->get_extension_list();
        foreach($extension_list as $key=>$data){
            if(preg_match('/[a-zA-Z]/', $data['user_code']) && preg_match('/[0-9]/', $data['user_code'])){
                unset($extension_list[$key]);
            }
        }
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
        if ($app_info['app_type']==2){
            $apps_ios = $this->DAO->get_app_channels($appid);
            $this->assign("apppackage_list",$apps_ios);
        }
        $this->assign("app_type",$app_info['app_type']);
        $this->assign("app_name", $app_info['app_name']);
        $this->assign("extension_list", $extension_list);
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
        $this->assign("params", $_POST);
        $this->assign("start_date", $start_date);
        $this->assign("end_date", $end_date);
        $this->assign("appid", $appid);
        $this->assign("guild_list", $guild_list);
        $this->display("kpi/game_detail.html");
    }

    public function realtime($appid){
        $app_info = $this->DAO->get_app_info($appid);
        if ($app_info['app_type']==1){
            $channels = $this->get_channel($_GET['channels']);
            //根据渠道获取子渠道信息
            if($_SESSION['group_id'] == 1){
                $guild_list = $this->DAO->get_guild_list();
            }elseif($_SESSION['group_id'] == 10){
                $guild_list = $this->DAO->get_guild_code($_SESSION['usr_id']);
            }
            $channel = $this->get_guild_channel($channels);
        }elseif ($app_info['app_type']==2){
            if ($_GET['channels']){
                $channels = $_GET['channels'];
            }else{
                $channels = "";
            }
            $channel = $channels;
        }
        $apps_data = $this->get_apps_hour_data(date('Y',time()),date('m',time()),date('d',time()),$appid,$channel);
        $today_data = $this->get_single_app_day_data($appid,date("Y-m-d",time()),date("Y-m-d",time()+86400),$channel,$_POST);
//        $today_apple_data = $this->DAO->get_apple_pay_order_by_app_id(array(
//            "start_time"=>strtotime(date('Y-m-d',time())),"end_time"=>strtotime(date('Y-m-d',strtotime("+1 day"))),"app_id"=>$appid));
        $today_apple_data = $this->DAO->get_apple_pay_order_user_count(array(
                "start_time"=>strtotime(date('Y-m-d',time())),"end_time"=>strtotime(date('Y-m-d',strtotime("+1 day"))),"app_list"=>$appid
        ));
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
        $act_user = $this->get_act_user($appid,$start_date, $end_date, $channels);
        $new_user = $this->get_new_user($appid,$start_date, $end_date, $channels);
        $act_device = $this->get_act_device($appid,$start_date, $end_date, $channels);
        $new_device = $this->get_new_device($appid,$start_date, $end_date, $channels);
        $pay_ammount = $this->DAO->get_pay_ammount($appid,$start_date, $end_date, $channels);
        $pay_count = $this->DAO->get_pay_count($appid,$start_date, $end_date, $channels,6);
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
            $arppu = round(($sum_pay+$today_apple_pay)/$sum_pay_count, 2);
        }
        if($sum_act_user){
            $darpu = round(($sum_pay+$today_apple_pay)/$sum_act_user, 2);
            $pay_rate = round(($sum_pay_count/$sum_act_user)*100,2);
        }
        if ($app_info['app_type']==2){
            $apps_ios = $this->DAO->get_app_channels($appid);
            $this->assign("apppackage_list",$apps_ios);
        }
        $game_channel = $this->DAO->get_game_channel($appid,strtotime(date('Y-m-d',time())));
        $ch_name = array();
        $ch_num = array();
        foreach ($game_channel as $key=> $game_ch){
            array_push($ch_name,$game_ch['channel']);
            array_push($ch_num,$game_ch['ch_count']);
        }
        $old_role = $this->DAO->get_old_role($appid,strtotime(date('Y-m-d',time())));
        $this->assign("app_info", $app_info);
        $this->assign("old_role", $old_role);
        $this->assign("ch_name", implode("', '",$ch_name));
        $this->assign("ch_num", implode("', '",$ch_num));
        $this->assign("selected_channel", $_GET['channels']);
        $this->assign("apps_data", $apps_data);
        $this->assign("pay_count", $pay_count);
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
        $this->assign("act_user", $act_user);
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
        $this->assign("guild_list", $guild_list);
        $this->display("kpi/game_realtime.html");
    }

    public function ios_channel($appid){
        if(!($_SESSION['group_id'] == 1 || $_SESSION['group_id'] == 12)){
            die("无权限");
        }
        if($_SESSION['group_id'] == 12){
            $app_id = $_SESSION['apps'];
            $user_apps = explode(",", $app_id);
            if(!in_array($appid,$user_apps)){
                die("你没有该游戏的权限，请联系管理员");
            }
        }
        //根据渠道获取子渠道信息
        $user_info = $this->DAO->get_user_apps($_SESSION['usr_id']);
        if(empty($user_info) || empty($user_info['user_code'])){
            die("账号异常请联系管理员");
        }
        //查询对应的包名
        $apps_info = $this->DAO->get_app_channels($appid);
        if ($apps_info){
            $channels = "";
            foreach ($apps_info as $apps_value){
                if ($apps_value['channel']!=$apps_value['apple_id']){
                    $channels .= '"'.$apps_value['channel'].'","'.$apps_value['apple_id'].'",';
                }else{
                    $channels .= '"'.$apps_value['apple_id'].'",';
                }
            }
            $channels .= '"appstore"';
        }else{
            $channels = '"appstore"';
        }
        $start_date = date("Y-m-d",strtotime("-6 day"));
        $end_date = date('Y-m-d', time());
        //6天前的日期    2016-01-06
        if($_POST['start_date']){
            $start_date = $_POST['start_date'];
        }
        if($_POST['end_date']){
            $end_date = $_POST['end_date'];
        }
//        if ($_SESSION['group_id']==1){
//            $user_info['user_code'] = "*";
//        }
//        if ($_GET['user_code']){
//            $user_info['user_code'] = $_GET['user_code'];
//        }

        $app_data = $this->get_app_channel_ios_data($appid,$start_date,$end_date,$channels);

        #当日的留存扣量，次日扣两天，七日扣八天, 依次类推
        $date_e_o = strtotime($end_date);		//输出“1279115455”
        $end_diff_to_now = $date_e_o - time();
        $day = date("d",abs($end_diff_to_now));
        if($end_diff_to_now >=0){
            $keep_next_start = date("Y-m-d",strtotime("-2 day"));
            $keep_next_end = date('Y-m-d', time());
            $keep_seven_start = date("Y-m-d",strtotime("-8 day"));
            $keep_seven_end = date('Y-m-d', time());
            $next_sub = $this->get_app_channel_data_limited($appid,$keep_next_start,$keep_next_end,'keep_next_end');
            $seven_sub = $this->get_app_channel_data_limited($appid,$keep_seven_start,$keep_seven_end,'keep_seven');
        }elseif($day == '01'){
            $keep_next_start = date("Y-m-d",strtotime("-1 day"));
            $keep_next_end = date('Y-m-d', time());
            $keep_seven_start = date("Y-m-d",strtotime("-7 day"));
            $keep_seven_end = date('Y-m-d', time());
            $next_sub = $this->get_app_channel_data_limited($appid,$keep_next_start,$keep_next_end,'keep_next_end');
            $seven_sub = $this->get_app_channel_data_limited($appid,$keep_seven_start,$keep_seven_end,'keep_seven');
        }elseif($day == '02'){
            $keep_next_start = date("Y-m-d",strtotime("-6 day"));
            $keep_next_end = date('Y-m-d', time());
            $next_sub = array();
            $seven_sub = $this->get_app_channel_data_limited($appid,$keep_next_start,$keep_next_end,'keep_seven');
        }elseif($day == '03'){
            $keep_next_start = date("Y-m-d",strtotime("-5 day"));
            $keep_next_end = date('Y-m-d', time());
            $next_sub = array();
            $seven_sub = $this->get_app_channel_data_limited($appid,$keep_next_start,$keep_next_end,'keep_seven');
        }elseif($day == '04'){
            $keep_next_start = date("Y-m-d",strtotime("-4 day"));
            $keep_next_end = date('Y-m-d', time());
            $next_sub = array();
            $seven_sub = $this->get_app_channel_data_limited($appid,$keep_next_start,$keep_next_end,'keep_seven');
        }elseif($day == '05'){
            $keep_next_start = date("Y-m-d",strtotime("-3 day"));
            $keep_next_end = date('Y-m-d', time());
            $next_sub = array();
            $seven_sub = $this->get_app_channel_data_limited($appid,$keep_next_start,$keep_next_end,'keep_seven');
        }elseif($day == '06'){
            $keep_next_start = date("Y-m-d",strtotime("-2 day"));
            $keep_next_end = date('Y-m-d', time());
            $next_sub = array();
            $seven_sub = $this->get_app_channel_data_limited($appid,$keep_next_start,$keep_next_end,'keep_seven');
        }elseif($day == '07'){
            $keep_next_start = date("Y-m-d",strtotime("-1 day"));
            $keep_next_end = date('Y-m-d', time());
            $next_sub = array();
            $seven_sub = $this->get_app_channel_data_limited($appid,$keep_next_start,$keep_next_end,'keep_seven');
        }else{
            $next_sub = array();
            $seven_sub = array();
        }
        $sub_new_user_next =  array();
        $sub_sum_keep_next =  array();
        $sub_new_user_seven =  array();
        $sub_sum_keep_seven =  array();
        foreach($next_sub as $key=>$sub){
            $sum_new_user = $sub['sum_new_user']['value'];
            $sum_seven_user = $sub['sum_keep']['value'];
            if($sub['key']==''){
                $sub_new_user_next["_null_"] = $sum_new_user;
                $sub_sum_keep_next["_null_"] = $sum_seven_user;
            }else{
                $sub_new_user_next[$sub['key']] = $sum_new_user;
                $sub_sum_keep_next[$sub['key']] = $sum_seven_user;
            }
        }
        foreach($seven_sub as $key=>$sub){
            $sum_new_user = $sub['sum_new_user']['value'];
            $sum_seven_user = $sub['sum_keep']['value'];
            if($sub['key']==''){
                $sub_new_user_seven["_null_"] = $sum_new_user;
                $sub_sum_keep_seven["_null_"] = $sum_seven_user;
            }else{
                $sub_new_user_seven[$sub['key']] = $sum_new_user;
                $sub_sum_keep_seven[$sub['key']] = $sum_seven_user;
            }
        }
        $app_data_keep =array();
        foreach($app_data['result'] as $key=>$app_d){

            if($app_d['key']==''){
                #次日
                if(in_array($app_d['key'],$sub_new_user_next)){
                    $app_d['sub_new_user_next'] = $sub_new_user_next['_null_'];
                }else{
                    $app_d['sub_new_user_next'] = 0;
                }
                if(in_array($app_d['key'],$sub_sum_keep_next)){
                    $app_d['sub_keep_next'] = $sub_sum_keep_next['_null_'];
                }else{
                    $app_d['sub_keep_next'] = 0;
                }
                #七日
                if(in_array($app_d['key'],$sub_new_user_seven)){
                    $app_d['sub_new_user_seven'] = $sub_new_user_seven['_null_'];
                }else{
                    $app_d['sub_new_user_seven'] = 0;
                }
                if(in_array($app_d['key'],$sub_sum_keep_seven)){
                    $app_d['sub_keep_seven'] = $sub_sum_keep_seven['_null_'];
                }else{
                    $app_d['sub_keep_seven'] = 0;
                }
            }else{
                #次日
                if(in_array($app_d['key'],$sub_new_user_next)){
                    $app_d['sub_new_user_next'] = $sub_new_user_next[$app_d['key']];
                }else{
                    $app_d['sub_new_user_next'] = 0;
                }
                if(in_array($app_d['key'],$sub_sum_keep_next)){
                    $app_d['sub_keep_next'] = $sub_sum_keep_next[$app_d['key']];
                }else{
                    $app_d['sub_keep_next'] = 0;
                }
                #七日
                if(in_array($app_d['key'],$sub_new_user_seven)){
                    $app_d['sub_new_user_seven'] = $sub_new_user_seven[$app_d['key']];
                }else{
                    $app_d['sub_new_user_seven'] = 0;
                }
                if(in_array($app_d['key'],$sub_sum_keep_seven)){
                    $app_d['sub_keep_seven'] = $sub_sum_keep_seven[$app_d['key']];
                }else{
                    $app_d['sub_keep_seven'] = 0;
                }
            }
            array_push($app_data_keep,$app_d);
        }
        $app_data['result'] = $app_data_keep;
        $sum_new_user = 0;
        $sum_new_device = 0;
        $sum_new_role = 0;
        $sum_pay_count = 0;
        $sum_pay = 0;
        $down_total = 0;
        $visit_total = 0;
        $diff_total = 0;


        foreach ($app_data['result'] as $key =>$data){
            foreach ($apps_info as $app_name){
                if ($app_name['apple_id'] == $data['key'] || $app_name['channel'] == $data['key']){
                    $app_data['result'][$key]['app_name'] = $app_name['game_name'];
                    break;
                }
            }
            if(($data['sum_new_user']['value'] - $data['sub_new_user_next']) <= 0){
                $app_data['result'][$key]['k1']= 0;
            }else{
                $k1 = ((($data['sum_keep_next']['value']- abs($data['sub_keep_next'])) / ($data['sum_new_user']['value'] - abs($data['sub_new_user_next'])))*100);
                $app_data['result'][$key]['k1']= round($k1, 1);
            }
            if(($data['sum_new_user']['value']-$data['sub_new_user_seven']) == 0){
                $app_data['result'][$key]['k7']= 0;
            }else{
                $k7 = (($data['sum_keep_seven']['value'] - abs($data['sub_keep_seven']))/($data['sum_new_user']['value'] - abs($data['sub_new_user_seven'])))*100;
                $app_data['result'][$key]['k7']= round($k7, 1);
            }
//            $down_num = $this->DAO->get_down_log_num($app_data['result'][$key]['key'],strtotime($start_date),strtotime($end_date));
//            $visit_num = $this->DAO->get_visit_log_num($app_data['result'][$key]['key'],strtotime($start_date),strtotime($end_date));
//            $diff_num = $this->DAO->get_diff_down_log_num($app_data['result'][$key]['key'],strtotime($start_date),strtotime($end_date));
            $app_data['result'][$key]['down_num'] = 0;
            $app_data['result'][$key]['visit_num'] = 0;
            $app_data['result'][$key]['diff_num'] = 0;
//            $down_total += $down_num['num'];
//            $visit_total += $visit_num['num'];
//            $diff_total += $diff_num['num'];
            $sum_new_user += $app_data['result'][$key]['sum_new_user']['value'];
            $sum_new_device += $app_data['result'][$key]['sum_new_device']['value'];
            $sum_new_role += $app_data['result'][$key]['sum_new_role']['value'];
            $sum_pay_count += $app_data['result'][$key]['sum_pay_count']['value'];
            $sum_pay += $app_data['result'][$key]['sum_pay']['value'];
            if($data['sum_pay_count']['value']== 0){
                $app_data['result'][$key]['pu1']= 0;
            }else{
                $pu1 = $data['sum_pay']['value'] / $data['sum_pay_count']['value'];
                $app_data['result'][$key]['pu1']= round($pu1, 1);
            }
            if($data['sum_act_user']['value']== 0){
                $app_data['result'][$key]['pu2']= 0;
            }else{
                $pu2 = $data['sum_pay']['value'] / $data['sum_act_user']['value'];
                $app_data['result'][$key]['pu2']= round($pu2, 1);
            }
            if($data['sum_act_user']['value']== 0){
                $app_data['result'][$key]['pr']= 0;
            }else{
                $pu1 = ($data['sum_pay_count']['value'] / $data['sum_act_user']['value'])*100;
                $app_data['result'][$key]['pr']= round($pu1, 1);
            }

        }
        $app_data['sum_user_channel'] = implode("', '",$app_data['sum_user_channel']);
        $app_data['sum_new_user'] = implode("', '",$app_data['sum_new_user']);
        $app_data['sum_act_user'] = implode("', '",$app_data['sum_act_user']);

        $this->assign("sum_new_user", $sum_new_user);
        $this->assign("sum_new_device", $sum_new_device);
        $this->assign("sum_new_role", $sum_new_role);
        $this->assign("sum_pay_count", $sum_pay_count);
        $this->assign("sum_pay", $sum_pay);
        $this->assign("down_total", $down_total);
        $this->assign("visit_total", $visit_total);
        $this->assign("diff_total", $diff_total);
        $this->assign("app_data", $app_data);
        $this->assign("appid", $appid);
        $this->assign("start_date", $start_date);
        $this->assign("end_date", $end_date);
        $this->assign("user_code",$user_info['user_code']);
        $this->display("kpi/game_ios_channel.html");
    }

    public function ios_export($appid){
        set_time_limit(0);
        if(!($_SESSION['group_id'] == 1 || $_SESSION['group_id'] == 12)){
            die("无权限");
        }
        if($_SESSION['group_id'] == 12){
            $app_id = $_SESSION['apps'];
            $user_apps = explode(",", $app_id);
            if(!in_array($appid,$user_apps)){
                die("你没有该游戏的权限，请联系管理员");
            }
        }
        //根据渠道获取子渠道信息
        $user_info = $this->DAO->get_user_apps($_SESSION['usr_id']);
        if(empty($user_info) || empty($user_info['user_code'])){
            die("账号异常请联系管理员");
        }
        $start_date = date("Y-m-d",strtotime("-6 day"));
        $end_date = date('Y-m-d', time());
        //6天前的日期    2016-01-06
        if($_POST['start_date']){
            $start_date = $_POST['start_date'];
        }
        if($_POST['end_date']){
            $end_date = $_POST['end_date'];
        }
        $app_data = $this->get_app_channel_ios_data($appid,$start_date,$end_date);
        $sum_new_user = 0;
        $sum_new_device = 0;
        $sum_new_role = 0;
        $sum_pay_count = 0;
        $sum_pay = 0;
        $down_total = 0;
        $visit_total = 0;
        $diff_total = 0;
        foreach ($app_data['result'] as $key =>$data){
            $app_data['result'][$key]['down_num'] = 0;
            $app_data['result'][$key]['visit_num'] = 0;
            $app_data['result'][$key]['diff_num'] = 0;
            $sum_new_user += $app_data['result'][$key]['sum_new_user']['value'];
            $sum_new_device += $app_data['result'][$key]['sum_new_device']['value'];
            $sum_new_role += $app_data['result'][$key]['sum_new_role']['value'];
            $sum_pay_count += $app_data['result'][$key]['sum_pay_count']['value'];
            $sum_pay += $app_data['result'][$key]['sum_pay']['value'];
        }
        $app_data['sum_user_channel'] = implode("', '",$app_data['sum_user_channel']);
        $app_data['sum_new_user'] = implode("', '",$app_data['sum_new_user']);
        $app_data['sum_act_user'] = implode("', '",$app_data['sum_act_user']);
        $data=array(
            'sum_new_user'=> $sum_new_user,
            'sum_new_device'=> $sum_new_device,
            'sum_new_role'=> $sum_new_role,
            'down_total'=> $down_total,
            'visit_total'=> $visit_total,
            'sum_pay_count'=> $sum_pay_count,
            'sum_pay'=> $sum_pay,
            'diff_total'=> $diff_total
        );
        if($app_data){
            $this->master_excel_out_ios($app_data['result'],$data);
        }else{
            echo "没有数据需要导出！";
        }
    }

    public function get_app_channel_ios_data($app_list='',$start_date='',$end_date='',$channels){
        $this->ES_PARAMS['type']  = 'analyze_kpi';
        $must = '{"terms":{"app_id":["'.$app_list.'"]}},
        {"term":{"stats_type": 1 }},
        {"range":{"time":{
            "gte": "'.$start_date.'",
            "lt": "'.$end_date.'"
        }}}';
        $must.=',{"terms":{"channel":['.$channels.']}}';
        //$must.=',{"wildcard":{"channel":"'.$channels.'"}}';
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
        $sum_act_user =  array();
        $sum_new_user =  array();
        $sum_user_channel = array();
        $sum_act_others = 0;
        $sum_new_others = 0;
        foreach($results['aggregations']['channel']['buckets'] as $key=>$data){
            if(count($sum_act_user) <= 20){
                array_push($sum_user_channel,$data['key']);
                array_push($sum_act_user,$data['sum_act_user']['value']);
                array_push($sum_new_user,$data['sum_new_user']['value']);
            }else{
                $sum_act_others += $data['sum_act_user']['value'];
                $sum_new_others += $data['sum_new_user']['value'];
            }
        }

        if(count($sum_act_user) > 20){
            array_push($sum_user_channel,'others');
            array_push($sum_act_user,$sum_act_others);
            array_push($sum_new_user,$sum_new_others);
        }
        $rquest = array(
            "result" =>$results['aggregations']['channel']['buckets'],
            "sum_user_channel" =>$sum_user_channel,
            "sum_act_user" =>$sum_act_user,
            "sum_new_user" =>$sum_new_user
        );
        return $rquest;
    }

    public function get_app_channel_data_limited($appid=0,$start_date='',$end_date='',$keep="keep_next"){
        $this->ES_PARAMS['type']  = 'analyze_kpi';
        $body = '{
            "size": 0,
            "query": {
                "constant_score": {
                    "filter": {
                        "bool": {
                            "must": [
                                {"term": {"app_id": '.$appid.'}},
                                {"term": {"stats_type": 1}},
                                {"range": {
                                    "time": {
                                        "gte": "'.$start_date.'",
                                        "lt": "'.$end_date.'" }
                                   }
                                }
                            ]
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
                        "sum_new_user": {
                            "sum": {
                                "field": "new_user"
                            }
                        },
                        "sum_keep": {
                            "sum": {
                                "field": "'.$keep.'"
                            }
                        }
                    }
                }
            }
        }';
        $this->ES_PARAMS['body'] = $body;
        $results = $this->ES->search($this->ES_PARAMS);
        return $results['aggregations']['channel']['buckets'];
    }



    public function channel($appid){
        $get_appid= $appid;
        $wite_list = array('523','712');
        if(!in_array($_SESSION['usr_id'],$wite_list)){
            if(!($_SESSION['group_id'] == 1 || $_SESSION['group_id'] == 12)){
                die("无权限");
            }
        }
        if($_SESSION['group_id'] == 12){
            $app_id = $_SESSION['apps'];
//            $app_arr = array();
            $user_apps = explode(",", $app_id);
            if(!in_array($appid,$user_apps)){
                die("你没有该游戏的权限，请联系管理员");
            }
        }
//        $app_info = $this->DAO->get_app_info($appid);
        //根据渠道获取子渠道信息
        $user_info = $this->DAO->get_user_apps($_SESSION['usr_id']);
        if(empty($user_info) || empty($user_info['user_code'])){
            die("账号异常请联系管理员");
        }
        $start_date = date("Y-m-d",strtotime("-1 day"));
        $end_date = date('Y-m-d', time());
        //6天前的日期    2016-01-06
        if($_POST['start_date']){
            $start_date = $_POST['start_date'];
        }
        if($_POST['end_date']){
            $end_date = $_POST['end_date'];
        }
//        $start_num = 1;
//        $end_num = 10;
//        if(($_POST['end_num'] - $_POST['start_num'])>100){
//          die("包区间的差额不能大于100");
//        }
//        if($_POST['start_num']){
//            $start_num = $_POST['start_num'];
//        }
//        if($_POST['end_num']){
//            $end_num = $_POST['end_num'];
//        }

        if($_SESSION['group_id']==1 && $_SESSION['usr_id'] == 84){
            $user_info['user_code']='nnfb';
        }elseif ($_SESSION['group_id']==1 && $_SESSION['usr_id'] == 380){
            $user_info['user_code']='daweiba';
        }elseif($_SESSION['group_id']==1 && $_SESSION['usr_id'] == 312){
            $user_info['user_code']='nnfb';
        }
//        if($_SESSION['usr_id'] ==  406){
//            $user_info['user_code'] = 'nnfb';
////            $start_num = 301;
////            $end_num = 350;
//        }
        if ($_GET['user_code']){
            $user_info['user_code'] = $_GET['user_code'];
        }

        $app_data = $this->get_app_channel_data($appid,$start_date,$end_date,$user_info['user_code']);
        //$app_data = $this->get_app_channel_data($appid,$start_date,$end_date,$user_info['user_code'],$start_num,$end_num);

        #当日的留存扣量，次日扣两天，七日扣八天, 依次类推
        $date_e_o = strtotime($end_date);		//输出“1279115455”
        $end_diff_to_now = $date_e_o - time();
        $end_diff_to_now = $date_e_o - time();
        $day = date("d",abs($end_diff_to_now));
        if($end_diff_to_now >=0){
            $keep_next_start = date("Y-m-d",strtotime("-2 day"));
            $keep_next_end = date('Y-m-d', time());
            $keep_seven_start = date("Y-m-d",strtotime("-8 day"));
            $keep_seven_end = date('Y-m-d', time());
            $next_sub = $this->get_app_channel_data_limited($appid,$keep_next_start,$keep_next_end,'keep_next_end');
            $seven_sub = $this->get_app_channel_data_limited($appid,$keep_seven_start,$keep_seven_end,'keep_seven');
        }elseif($day == '01'){
            $keep_next_start = date("Y-m-d",strtotime("-1 day"));
            $keep_next_end = date('Y-m-d', time());
            $keep_seven_start = date("Y-m-d",strtotime("-7 day"));
            $keep_seven_end = date('Y-m-d', time());
            $next_sub = $this->get_app_channel_data_limited($appid,$keep_next_start,$keep_next_end,'keep_next_end');
            $seven_sub = $this->get_app_channel_data_limited($appid,$keep_seven_start,$keep_seven_end,'keep_seven');
        }elseif($day == '02'){
            $keep_next_start = date("Y-m-d",strtotime("-6 day"));
            $keep_next_end = date('Y-m-d', time());
            $next_sub = array();
            $seven_sub = $this->get_app_channel_data_limited($appid,$keep_next_start,$keep_next_end,'keep_seven');
        }elseif($day == '03'){
            $keep_next_start = date("Y-m-d",strtotime("-5 day"));
            $keep_next_end = date('Y-m-d', time());
            $next_sub = array();
            $seven_sub = $this->get_app_channel_data_limited($appid,$keep_next_start,$keep_next_end,'keep_seven');
        }elseif($day == '04'){
            $keep_next_start = date("Y-m-d",strtotime("-4 day"));
            $keep_next_end = date('Y-m-d', time());
            $next_sub = array();
            $seven_sub = $this->get_app_channel_data_limited($appid,$keep_next_start,$keep_next_end,'keep_seven');
        }elseif($day == '05'){
            $keep_next_start = date("Y-m-d",strtotime("-3 day"));
            $keep_next_end = date('Y-m-d', time());
            $next_sub = array();
            $seven_sub = $this->get_app_channel_data_limited($appid,$keep_next_start,$keep_next_end,'keep_seven');
        }elseif($day == '06'){
            $keep_next_start = date("Y-m-d",strtotime("-2 day"));
            $keep_next_end = date('Y-m-d', time());
            $next_sub = array();
            $seven_sub = $this->get_app_channel_data_limited($appid,$keep_next_start,$keep_next_end,'keep_seven');
        }elseif($day == '07'){
            $keep_next_start = date("Y-m-d",strtotime("-1 day"));
            $keep_next_end = date('Y-m-d', time());
            $next_sub = array();
            $seven_sub = $this->get_app_channel_data_limited($appid,$keep_next_start,$keep_next_end,'keep_seven');
        }else{
            $next_sub = array();
            $seven_sub = array();
        }
        $sub_new_user_next =  array();
        $sub_sum_keep_next =  array();
        $sub_new_user_seven =  array();
        $sub_sum_keep_seven =  array();
        foreach($next_sub as $key=>$sub){
            $sum_new_user = $sub['sum_new_user']['value'];
            $sum_seven_user = $sub['sum_keep']['value'];
            if($sub['key']==''){
                $sub_new_user_next["_null_"] = $sum_new_user;
                $sub_sum_keep_next["_null_"] = $sum_seven_user;
            }else{
                $sub_new_user_next[$sub['key']] = $sum_new_user;
                $sub_sum_keep_next[$sub['key']] = $sum_seven_user;
            }
        }
        foreach($seven_sub as $key=>$sub){
            $sum_new_user = $sub['sum_new_user']['value'];
            $sum_seven_user = $sub['sum_keep']['value'];
            if($sub['key']==''){
                $sub_new_user_seven["_null_"] = $sum_new_user;
                $sub_sum_keep_seven["_null_"] = $sum_seven_user;
            }else{
                $sub_new_user_seven[$sub['key']] = $sum_new_user;
                $sub_sum_keep_seven[$sub['key']] = $sum_seven_user;
            }
        }
        $app_data_keep =array();
        foreach($app_data['result'] as $key=>$app_d){

            if($app_d['key']==''){
                #次日
                if(in_array($app_d['key'],$sub_new_user_next)){
                    $app_d['sub_new_user_next'] = $sub_new_user_next['_null_'];
                }else{
                    $app_d['sub_new_user_next'] = 0;
                }
                if(in_array($app_d['key'],$sub_sum_keep_next)){
                    $app_d['sub_keep_next'] = $sub_sum_keep_next['_null_'];
                }else{
                    $app_d['sub_keep_next'] = 0;
                }
                #七日
                if(in_array($app_d['key'],$sub_new_user_seven)){
                    $app_d['sub_new_user_seven'] = $sub_new_user_seven['_null_'];
                }else{
                    $app_d['sub_new_user_seven'] = 0;
                }
                if(in_array($app_d['key'],$sub_sum_keep_seven)){
                    $app_d['sub_keep_seven'] = $sub_sum_keep_seven['_null_'];
                }else{
                    $app_d['sub_keep_seven'] = 0;
                }
            }else{
                #次日
                if(in_array($app_d['key'],$sub_new_user_next)){
                    $app_d['sub_new_user_next'] = $sub_new_user_next[$app_d['key']];
                }else{
                    $app_d['sub_new_user_next'] = 0;
                }
                if(in_array($app_d['key'],$sub_sum_keep_next)){
                    $app_d['sub_keep_next'] = $sub_sum_keep_next[$app_d['key']];
                }else{
                    $app_d['sub_keep_next'] = 0;
                }
                #七日
                if(in_array($app_d['key'],$sub_new_user_seven)){
                    $app_d['sub_new_user_seven'] = $sub_new_user_seven[$app_d['key']];
                }else{
                    $app_d['sub_new_user_seven'] = 0;
                }
                if(in_array($app_d['key'],$sub_sum_keep_seven)){
                    $app_d['sub_keep_seven'] = $sub_sum_keep_seven[$app_d['key']];
                }else{
                    $app_d['sub_keep_seven'] = 0;
                }
            }
            array_push($app_data_keep,$app_d);
        }
        $app_data['result'] = $app_data_keep;
        $sum_new_user = 0;
        $sum_new_device = 0;
        $sum_new_role = 0;
        $sum_pay_count = 0;
        $sum_pay = 0;
        $down_total = 0;
        $visit_total = 0;
        $diff_total = 0;


        foreach ($app_data['result'] as $key =>$data){
            if(($data['sum_new_user']['value'] - $data['sub_new_user_next']) <= 0){
                $app_data['result'][$key]['k1']= 0;
            }else{
                $k1 = ((($data['sum_keep_next']['value']- abs($data['sub_keep_next'])) / ($data['sum_new_user']['value'] - abs($data['sub_new_user_next'])))*100);
                $app_data['result'][$key]['k1']= round($k1, 1);
            }
            if(($data['sum_new_user']['value']-$data['sub_new_user_seven']) == 0){
                $app_data['result'][$key]['k7']= 0;
            }else{
                $k7 = (($data['sum_keep_seven']['value'] - abs($data['sub_keep_seven']))/($data['sum_new_user']['value'] - abs($data['sub_new_user_seven'])))*100;
                $app_data['result'][$key]['k7']= round($k7, 1);
            }
            $user_role = $this->DAO->get_user_role($get_appid,$app_data['result'][$key]['key'],strtotime($start_date),strtotime($end_date));
            $down_num = $this->DAO->get_down_log_num($app_data['result'][$key]['key'],strtotime($start_date),strtotime($end_date));
            $visit_num = $this->DAO->get_visit_log_num($app_data['result'][$key]['key'],strtotime($start_date),strtotime($end_date));
            $diff_num = $this->DAO->get_diff_down_log_num($app_data['result'][$key]['key'],strtotime($start_date),strtotime($end_date));
            $app_data['result'][$key]['down_num'] = $down_num['num'];
            $app_data['result'][$key]['visit_num'] = $visit_num['num'];
            $app_data['result'][$key]['diff_num'] = $diff_num['num'];
            $app_data['result'][$key]['sum_new_user']['value']=$user_role['num'];

            $down_total += $down_num['num'];
            $visit_total += $visit_num['num'];
            $diff_total += $diff_num['num'];
            $sum_new_user += $app_data['result'][$key]['sum_new_user']['value'];
            $sum_new_device += $app_data['result'][$key]['sum_new_device']['value'];
            $sum_new_role += $app_data['result'][$key]['sum_new_role']['value'];
            $sum_pay_count += $app_data['result'][$key]['sum_pay_count']['value'];
            $sum_pay += $app_data['result'][$key]['sum_pay']['value'];
            if($data['sum_pay_count']['value']== 0){
                $app_data['result'][$key]['pu1']= 0;
            }else{
                $pu1 = $data['sum_pay']['value'] / $data['sum_pay_count']['value'];
                $app_data['result'][$key]['pu1']= round($pu1, 1);
            }
            if($data['sum_act_user']['value']== 0){
                $app_data['result'][$key]['pu2']= 0;
            }else{
                $pu2 = $data['sum_pay']['value'] / $data['sum_act_user']['value'];
                $app_data['result'][$key]['pu2']= round($pu2, 1);
            }
            if($data['sum_act_user']['value']== 0){
                $app_data['result'][$key]['pr']= 0;
            }else{
                $pu1 = ($data['sum_pay_count']['value'] / $data['sum_act_user']['value'])*100;
                $app_data['result'][$key]['pr']= round($pu1, 1);
            }

        }
        $app_data['sum_user_channel'] = implode("', '",$app_data['sum_user_channel']);
        $app_data['sum_new_user'] = implode("', '",$app_data['sum_new_user']);
        $app_data['sum_act_user'] = implode("', '",$app_data['sum_act_user']);
        $lock = 0;
//        $white_list = array('593','592','591','590');
        $white_list = array();
        if(in_array($_SESSION['usr_id'],$white_list)&& $this->client_ip() != '117.25.83.213') {
            $lock = 1;
        }
        $this->assign("ip", $this->client_ip());
        $this->assign("lock",$lock);
        $this->assign("sum_new_user", $sum_new_user);
        $this->assign("sum_new_device", $sum_new_device);
        $this->assign("sum_new_role", $sum_new_role);
        $this->assign("sum_pay_count", $sum_pay_count);
        $this->assign("sum_pay", $sum_pay);
        $this->assign("down_total", $down_total);
        $this->assign("visit_total", $visit_total);
        $this->assign("diff_total", $diff_total);
        $this->assign("app_data", $app_data);
        $this->assign("appid", $appid);
        $this->assign("start_date", $start_date);
        $this->assign("end_date", $end_date);
        $this->assign("user_code",$user_info['user_code']);
//        $this->assign("start_num", $start_num);
//        $this->assign("end_num", $end_num);
        $this->display("kpi/game_channel.html");
    }

    public function all_channel($appid){
        $get_appid = $appid;
        $wite_list = array('523','712');
        if(!in_array($_SESSION['usr_id'],$wite_list)){
            if(!($_SESSION['group_id'] == 1 || $_SESSION['group_id'] == 12)){
                die("无权限");
            }
        }
        if($_SESSION['group_id'] == 12){
            $app_id = $_SESSION['apps'];
            $user_apps = explode(",", $app_id);
            if(!in_array($appid,$user_apps)){
                die("你没有该游戏的权限，请联系管理员");
            }
        }
        //获取所有APPS
        $app_dao = new app_admin_dao();
        $apps = $app_dao->get_all_app();
        $app_info = $this->DAO->get_app_info($appid);
        //获取所有渠道
        $user_info = $this->DAO->get_all_channel();
        $start_date = date("Y-m-d",strtotime("-1 day"));
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
            $app_data = $this->get_app_channel_data($appid,$start_date,$end_date,$val['user_code']);
            if (empty($app_data['result'])) continue;
            $sum_new_user = 0;
            $sum_act_user = 0;
            $sum_new_device = 0;
            $sum_new_role = 0;
            $sum_pay_count = 0;
            $sum_pay = 0;
            $down_total = 0;
            $visit_total = 0;
            $diff_total = 0;
            foreach ($app_data['result'] as $key =>$data){
//                if($this->client_ip()=='220.249.163.54'){
                $user_role = $this->DAO->get_user_role($get_appid,$app_data['result'][$key]['key'],strtotime($start_date),strtotime($end_date));
//                }
                $down_num = $this->DAO->get_down_log_num($app_data['result'][$key]['key'],strtotime($start_date),strtotime($end_date));
                $visit_num = $this->DAO->get_visit_log_num($app_data['result'][$key]['key'],strtotime($start_date),strtotime($end_date));
                $diff_num = $this->DAO->get_diff_down_log_num($app_data['result'][$key]['key'],strtotime($start_date),strtotime($end_date));
                $app_data['result'][$key]['down_num'] = $down_num['num'];
                $app_data['result'][$key]['visit_num'] = $visit_num['num'];
                $app_data['result'][$key]['diff_num'] = $diff_num['num'];
                $down_total += $down_num['num'];
                $visit_total += $visit_num['num'];
                $diff_total += $diff_num['num'];
//                $sum_new_user += $app_data['result'][$key]['sum_new_user']['value'];
//                if($this->client_ip()=='220.249.163.54'){
                $sum_new_user += $user_role['num'];
//                }
                $sum_act_user += $app_data['result'][$key]['sum_act_user']['value'];
                $sum_new_device += $app_data['result'][$key]['sum_new_device']['value'];
                $sum_new_role += $app_data['result'][$key]['sum_new_role']['value'];
                $sum_pay_count += $app_data['result'][$key]['sum_pay_count']['value'];
                $sum_pay += $app_data['result'][$key]['sum_pay']['value'];
            }
//            $app_data['sum_user_channel'] = implode("', '",$app_data['sum_user_channel']);
//            $app_data['sum_new_user'] = implode("', '",$app_data['sum_new_user']);
//            $app_data['sum_act_user'] = implode("', '",$app_data['sum_act_user']);

            $app_data_channel = array("channel"=>$val['user_code'],"sum_new_user"=>$sum_new_user,"sum_new_device"=>$sum_new_device,"sum_new_role"=>$sum_new_role,"sum_pay_count"=>$sum_pay_count,
                                        "sum_pay"=>$sum_pay,"down_total"=>$down_total,"visit_total"=>$visit_total,"diff_total"=>$diff_total);
            array_push($app_data_channel_all,$app_data_channel);
            $channel_all .= $val['user_code']."','";
            $sum_new_user_all .= $sum_new_user."','";
            $sum_act_user_all .= $sum_act_user."','";
        }
        $channel_all = trim($channel_all,"','");
        $sum_new_user_all = trim($sum_new_user_all,"','");
        $sum_act_user_all = trim($sum_act_user_all,"','");
//        var_dump($app_data_channel_all);
//        exit();
//        $this->assign("sum_new_user", $sum_new_user);
//        $this->assign("sum_new_device", $sum_new_device);
//        $this->assign("sum_new_role", $sum_new_role);
//        $this->assign("sum_pay_count", $sum_pay_count);
//        $this->assign("sum_pay", $sum_pay);
//        $this->assign("down_total", $down_total);
//        $this->assign("visit_total", $visit_total);
//        $this->assign("diff_total", $diff_total);
//        $this->assign("app_data", $app_data);
        $this->assign("app_data_all",$app_data_channel_all);
        $this->assign("channel_all",$channel_all);
        $this->assign("sum_new_user_all",$sum_new_user_all);
        $this->assign("sum_act_user_all",$sum_act_user_all);
        $this->assign("app_name", $app_info['app_name']);
        $this->assign("appid", $appid);
        $this->assign("apps",$apps);
        $this->assign("start_date", $start_date);
        $this->assign("end_date", $end_date);
        $this->display("kpi/game_all_channel.html");
    }

    public function game_hour_data(){
        $app_id = $this->get_app_id($_GET['appids']);
        $channel = $this->get_channel($_GET['channels']);
        $apps = explode(",", $app_id);
        $channels = $this->get_guild_channel($channel);
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
            $arppu = round(($pay_ammount+$day_data['apple_pay_count'])/$pay_count, 2);
        }else{
            $arppu = 0;
        }

        if(!empty($act_user)){
            $darpu = round(($pay_ammount+$day_data['apple_pay_count']) / $act_user, 2);
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
        $this->assign("apple_pay_amount",$day_data['apple_pay_count']);
        $this->display("kpi/game_hour_view.html");
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
    //游戏24小时综合数据
    public function get_apps_hour_data($year='',$month='',$day='',$app_list='',$channels=''){
        if(is_array($app_list)) {
            $app_list = implode('","', $app_list);
        }
        if(is_array($channels)){
            $channels = implode('","',$channels);
        }
        $this->ES_PARAMS['type']  = 'analyze_kpi';
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

    public function get_app_channel_data($app_list='',$start_date='',$end_date='',$channels){
//        $channel_array=array();
//        for($i = $start_num; $i <= $end_num; $i++) {
//            array_push($channel_array,$channels.$i);
//        }
        $this->ES_PARAMS['type']  = 'analyze_kpi';
        $must = '{"terms":{"app_id":["'.$app_list.'"]}},
        {"term":{"stats_type": 1 }},
        {"range":{"time":{
            "gte": "'.$start_date.'",
            "lt": "'.$end_date.'"
        }}}';
//        $channel_str = implode('","',$channel_array);
        $must.=',{"wildcard":{"channel":"'.$channels.'*"}}';
//        $must.=',{"terms":{"channel":["'.$channel_str.'"]}}';

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
        $sum_act_user =  array();
        $sum_new_user =  array();
        $sum_user_channel = array();
        $sum_act_others = 0;
        $sum_new_others = 0;

        foreach($results['aggregations']['channel']['buckets'] as $key=>$data){
            if(strpos($data['key'],"nnfb")==0){
                if(count($sum_act_user) <= 30){
                    array_push($sum_user_channel,$data['key']);
                    array_push($sum_act_user,$data['sum_act_user']['value']);
                    array_push($sum_new_user,$data['sum_new_user']['value']);
                }else{
                    $sum_act_others += $data['sum_act_user']['value'];
                    $sum_new_others += $data['sum_new_user']['value'];
                }
            }
        }

        if(count($sum_act_user) > 30){
            array_push($sum_user_channel,'others');
            array_push($sum_act_user,$sum_act_others);
            array_push($sum_new_user,$sum_new_others);
        }

        $rquest = array(
            "result" =>$results['aggregations']['channel']['buckets'],
            "sum_user_channel" =>$sum_user_channel,
            "sum_act_user" =>$sum_act_user,
            "sum_new_user" =>$sum_new_user
        );
        return $rquest;

    }


    public function get_apps_trend_data($app_list='',$start_date='',$end_date='',$source='',$channels=''){
        $app_list = implode('","',$app_list);
        if(is_array($channels)){
            $channels = implode('","',$channels);
        }
        $this->ES_PARAMS['type']  = 'analyze_kpi';
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

    public function get_app_day_data($app_list='',$start_date='',$end_date='',$channels=''){
        $app_list = implode('","',$app_list);
        if(is_array($channels)){
            $channels = implode('","',$channels);
        }
        $this->ES_PARAMS['type']  = 'analyze_kpi';
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

    public function get_apps_day_data($app_list = '',$year = 0, $month = 0, $day = 0,  $stats_type = 1, $channels = ''){
        $start_day = strtotime(($year."-".$month."-".$day." 00:00:00" ));
        $end_day = strtotime(($year.'-'.$month."-".$day." 24:00:00" ));
        $app = implode(',',$app_list);
        $app_list = implode('","',$app_list);
        if(is_array($channels)){
            $channels = implode('","',$channels);
        }
        $this->ES_PARAMS['type']  = 'analyze_kpi';
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
        $apple_pay_res = $this->DAO->get_apple_pay_order_user_count(array("start_time"=>$start_day,"end_time"=>$end_day,
            "app_list"=>$app));
        $data['apple_user_no'] = $apple_pay_res['user_no'];
        if ($apple_pay_res['order_count']){
            $data['apple_order_count'] = $apple_pay_res['order_count'];
        }else{
            $data['apple_order_count'] = 0;
        }
        if ($apple_pay_res['pay_count']){
            $data['apple_pay_count'] = $apple_pay_res['pay_count'];
        }else{
            $data['apple_pay_count'] = 0;
        }
        return $data;
    }

    public function get_single_app_day_data($apps= 0,$start_date="",$end_date="",$channels="",$params,$apple_pay = []){
        if(is_array($channels)){
            $channels = implode('","',$channels);
        }
        $this->ES_PARAMS['type']  = 'analyze_kpi';
        $must = '{"term":{"app_id":"'.$apps.'"}},
                {"term":{"stats_type": 1 }},
                {"range":{
                    "time":{
                        "gte":"'.$start_date.'",
                        "lt":"'.$end_date.'"
                        }}
                }';
        if($params['channel_type'] == '2'){
            if(!empty($params['ex_channel'])){
                $must .=',{"regexp":{"channel":"'.$params['ex_channel'].'[0-9]{0,4}"}}';
            }
            if(!empty($params['flow_channel'])){
                $must.=',{"terms":{"channel":["'.trim($params['flow_channel']).'"]}}';
            }
        }else{
            if(!empty($channels)){
                $must.=',{"terms":{"channel":["'.$channels.'"]}}';
            }
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
            array_push($date_list,substr($source['key_as_string'],5,5));
            array_push($short_date_list,substr($source['key_as_string'],5,5));
            if (!empty($apple_pay)){
                //增加苹果充值
                foreach ($apple_pay as $apple_key=>$apple_value){
                    if (substr($apple_value['date_group'],5,5) == substr($source['key_as_string'],5,5)){
                        $source['sum_pay_count']['value'] += $apple_value['all_date_user_no'];
                        $source['sum_pay']['value'] += $apple_value['all_date_pay_money'];
                        unset($apple_pay[$apple_key]);
                        break;
                    }
                }
            }
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
    //统计实时登入用户数
    public function get_act_user($app_id=0,$start=0,$end=0,$channels=''){
        $this->ES_PARAMS['type']  = 'stats_user_login_log';
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
    //统计实时新增账号数
    public function get_new_user($app_id=0,$start=0,$end=0,$channels=''){
        $this->ES_PARAMS['type']  = 'stats_user_app';
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
    //统计实时活跃设备数
    public function get_act_device($app_id=0,$start=0,$end=0,$channels=''){
        $this->ES_PARAMS['type']  = 'stats_device';
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
        $this->ES_PARAMS['type']  = 'stats_device';
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

    public function get_guild_channel($channel){
        if(empty($channel)){
            return "";
        }
        $app_dao = new app_admin_dao();
        $guild = $app_dao->get_guild_by_ch($channel);
        if($guild){
            $all_guild = $app_dao->get_all_guild_ch($guild['id']);
            $channels = array();
            foreach($all_guild as $data){
                if(!empty($data['user_code'])){
                    array_push($channels,$data['user_code']);
                }
            }
        }else{
            $channels = $channel;
        }


        return $channels;
    }

    public function ch_export($appid){
        set_time_limit(0);
        if(!($_SESSION['group_id'] == 1 || $_SESSION['group_id'] == 12)){
            die("无权限");
        }
        if($_SESSION['group_id'] == 12){
            $app_id = $_SESSION['apps'];
            $app_arr = array();
            $user_apps = explode(",", $app_id);
            if(!in_array($appid,$user_apps)){
                die("你没有该游戏的权限，请联系管理员");
            }
        }
        $app_info = $this->DAO->get_app_info($appid);
        //根据渠道获取子渠道信息
        $user_info = $this->DAO->get_user_apps($_SESSION['usr_id']);
        if(empty($user_info) || empty($user_info['user_code'])){
            die("账号异常请联系管理员");
        }
        $start_date = date("Y-m-d",strtotime("-6 day"));
        $end_date = date('Y-m-d', time());
        //6天前的日期    2016-01-06
        if($_POST['start_date']){
            $start_date = $_POST['start_date'];
        }
        if($_POST['end_date']){
            $end_date = $_POST['end_date'];
        }
//        $start_num = 1;
//        $end_num = 10;
//        if(($_POST['end_num'] - $_POST['start_num'])>100){
//            die("包区间的差额不能大于100");
//        }
//        if($_POST['start_num']){
//            $start_num = $_POST['start_num'];
//        }
//        if($_POST['end_num']){
//            $end_num = $_POST['end_num'];
//        }

        if($_SESSION['group_id']==1 && $_SESSION['usr_id'] == 84){
            $user_info['user_code']='nnfb';
        }elseif ($_SESSION['group_id']==1 && $_SESSION['usr_id'] == 380){
            $user_info['user_code']='daweiba';
        }elseif($_SESSION['group_id']==1 && $_SESSION['usr_id'] == 312){
            $user_info['user_code']='nnfb';
        }

        if ($_GET['user_code']){
            $user_info['user_code'] = $_GET['user_code'];
        }
        $app_data = $this->get_app_channel_data($appid,$start_date,$end_date,$user_info['user_code']);
        //$app_data = $this->get_app_channel_data($appid,$start_date,$end_date,$user_info['user_code'],$start_num,$end_num);

        $sum_new_user = 0;
        $sum_new_device = 0;
        $sum_new_role = 0;
        $sum_pay_count = 0;
        $sum_pay = 0;
        $down_total = 0;
        $visit_total = 0;
        $diff_total = 0;


        foreach ($app_data['result'] as $key =>$data){
            $down_num = $this->DAO->get_down_log_num($app_data['result'][$key]['key'],strtotime($start_date),strtotime($end_date));
            $visit_num = $this->DAO->get_visit_log_num($app_data['result'][$key]['key'],strtotime($start_date),strtotime($end_date));
            $diff_num = $this->DAO->get_diff_down_log_num($app_data['result'][$key]['key'],strtotime($start_date),strtotime($end_date));
            $app_data['result'][$key]['down_num'] = $down_num['num'];
            $app_data['result'][$key]['visit_num'] = $visit_num['num'];
            $app_data['result'][$key]['diff_num'] = $diff_num['num'];
            $down_total += $down_num['num'];
            $visit_total += $visit_num['num'];
            $diff_total += $diff_num['num'];
            $sum_new_user += $app_data['result'][$key]['sum_new_user']['value'];
            $sum_new_device += $app_data['result'][$key]['sum_new_device']['value'];
            $sum_new_role += $app_data['result'][$key]['sum_new_role']['value'];
            $sum_pay_count += $app_data['result'][$key]['sum_pay_count']['value'];
            $sum_pay += $app_data['result'][$key]['sum_pay']['value'];
        }
        $app_data['sum_user_channel'] = implode("', '",$app_data['sum_user_channel']);
        $app_data['sum_new_user'] = implode("', '",$app_data['sum_new_user']);
        $app_data['sum_act_user'] = implode("', '",$app_data['sum_act_user']);
        $data=array(
            'sum_new_user'=> $sum_new_user,
            'sum_new_device'=> $sum_new_device,
            'sum_new_role'=> $sum_new_role,
            'down_total'=> $down_total,
            'visit_total'=> $visit_total,
            'sum_pay_count'=> $sum_pay_count,
            'sum_pay'=> $sum_pay,
            'diff_total'=> $diff_total
        );
        if($app_data){
            $this->master_excel_out($app_data['result'],$data);
        }else{
            echo "没有数据需要导出！";
        }

    }
    private function master_excel_out_ios($data,$all){
        set_time_limit(0);
        $str_now=date("Y-m-d H:i:s",strtotime("now"));
        $objExcel = new PHPExcel();
        $objExcel->setActiveSheetIndex(0);
        $objActSheet = $objExcel->getActiveSheet();
        $objActSheet->setTitle("渠道充值数据");
        $objActSheet->setCellValue("A1", "包名");
        $objActSheet->setCellValue("B1", "展现点击量");
        $objActSheet->setCellValue("C1", "下载点击量");
        $objActSheet->setCellValue("D1", "下载排重点击量");
        $objActSheet->setCellValue("E1", "激活率");
        $objActSheet->setCellValue("F1", "新增用户");
        $objActSheet->setCellValue("G1", "新增设备");
        $objActSheet->setCellValue("H1", "新增角色");
        $objActSheet->setCellValue("I1", "充值总人数");
        $objActSheet->setCellValue("J1", "充值总金额");

        $objActSheet->setCellValue("A2", "总计");
        $objActSheet->setCellValue("B2", $all['visit_total']);
        $objActSheet->setCellValue("C2", $all['down_total']);
        $objActSheet->setCellValue("D2", $all['diff_total']);
        $objActSheet->setCellValue("E2", round( $all['sum_new_device']/$all['diff_total']*100, 2)."%");
        $objActSheet->setCellValue("F2", $all['sum_new_user']);
        $objActSheet->setCellValue("G2", $all['sum_new_device']);
        $objActSheet->setCellValue("H2", $all['sum_new_role']);
        $objActSheet->setCellValue("I2", $all['sum_pay_count']);
        $objActSheet->setCellValue("J2", $all['sum_pay']);
        $n = 3;
        foreach($data as $info){
            $objActSheet->setCellValueExplicit("A".$n,$info['key'],PHPExcel_Cell_DataType::TYPE_STRING);
            $objActSheet->setCellValue("B".$n, $info['visit_num']);
            $objActSheet->setCellValue("C".$n, $info['down_num']);
            $objActSheet->setCellValue("D".$n, $info['diff_num']);
            $objActSheet->setCellValue("E".$n, round( $info['sum_new_device']['value']/$info['diff_num']*100, 2)."%");
            $objActSheet->setCellValue("F".$n, $info['sum_new_user']['value']);
            $objActSheet->setCellValue("G".$n, $info['sum_new_device']['value']);
            $objActSheet->setCellValue("H".$n, $info['sum_new_role']['value']);
            $objActSheet->setCellValue("I".$n, $info['sum_pay_count']['value']);
            $objActSheet->setCellValue("J".$n, $info['sum_pay']['value']);

            $n++;
        }
        $title = iconv("UTF-8", "GB2312//IGNORE","信息流数据-".$str_now.'.xls');
        header("Content-type: text/html;charset=utf-8");
        header("Content-Type: application/force-download");
        header("Content-Type: application/octet-stream");
        header("Content-Type: application/download");
        header('Content-Disposition:inline;filename="'.$title.'"');
        header("Content-Transfer-Encoding: binary");
        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
        header("Pragma: no-cache");
        $objWriter = PHPExcel_IOFactory::createWriter($objExcel, 'Excel5');
        $objWriter->save('php://output');
    }

    private function master_excel_out($data,$all){
        set_time_limit(0);
        $str_now=date("Y-m-d H:i:s",strtotime("now"));
        $objExcel = new PHPExcel();
        $objExcel->setActiveSheetIndex(0);
        $objActSheet = $objExcel->getActiveSheet();
        $objActSheet->setTitle("渠道充值数据");
        $objActSheet->setCellValue("A1", "渠道ID");
        $objActSheet->setCellValue("B1", "展现点击量");
        $objActSheet->setCellValue("C1", "下载点击量");
        $objActSheet->setCellValue("D1", "下载排重点击量");
        $objActSheet->setCellValue("E1", "激活率");
        $objActSheet->setCellValue("F1", "新增用户");
        $objActSheet->setCellValue("G1", "新增设备");
        $objActSheet->setCellValue("H1", "新增角色");
        $objActSheet->setCellValue("I1", "充值总人数");
        $objActSheet->setCellValue("J1", "充值总金额");

        $objActSheet->setCellValue("A2", "总计");
        $objActSheet->setCellValue("B2", $all['visit_total']);
        $objActSheet->setCellValue("C2", $all['down_total']);
        $objActSheet->setCellValue("D2", $all['diff_total']);
        $objActSheet->setCellValue("E2", round( $all['sum_new_device']/$all['diff_total']*100, 2)."%");
        $objActSheet->setCellValue("F2", $all['sum_new_user']);
        $objActSheet->setCellValue("G2", $all['sum_new_device']);
        $objActSheet->setCellValue("H2", $all['sum_new_role']);
        $objActSheet->setCellValue("I2", $all['sum_pay_count']);
        $objActSheet->setCellValue("J2", $all['sum_pay']);
        $n = 3;
        foreach($data as $info){
            $objActSheet->setCellValue("A".$n, $info['key']);
            $objActSheet->setCellValue("B".$n, $info['visit_num']);
            $objActSheet->setCellValue("C".$n, $info['down_num']);
            $objActSheet->setCellValue("D".$n, $info['diff_num']);
            $objActSheet->setCellValue("E".$n, round( $info['sum_new_device']['value']/$info['diff_num']*100, 2)."%");
            $objActSheet->setCellValue("F".$n, $info['sum_new_user']['value']);
            $objActSheet->setCellValue("G".$n, $info['sum_new_device']['value']);
            $objActSheet->setCellValue("H".$n, $info['sum_new_role']['value']);
            $objActSheet->setCellValue("I".$n, $info['sum_pay_count']['value']);
            $objActSheet->setCellValue("J".$n, $info['sum_pay']['value']);

            $n++;
        }
        $title = iconv("UTF-8", "GB2312//IGNORE","信息流数据-".$str_now.'.xls');
        header("Content-type: text/html;charset=utf-8");
        header("Content-Type: application/force-download");
        header("Content-Type: application/octet-stream");
        header("Content-Type: application/download");
        header('Content-Disposition:inline;filename="'.$title.'"');
        header("Content-Transfer-Encoding: binary");
        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
        header("Pragma: no-cache");
        $objWriter = PHPExcel_IOFactory::createWriter($objExcel, 'Excel5');
        $objWriter->save('php://output');
    }


    public function role_export(){
        $params = $_GET;
        $this->ES_PARAMS['index'] = 'ios_log';
        $start = strtotime('2018-04-10 00:00:00');
        $end = strtotime('2018-04-25 00:00:00');
        $this->ES_PARAMS['type'] = 'stats_user_app';
        $role_results = $this->get_result($params,$start,$end);
        $role_list = $role_results['hits']['hits'];
        $this->ES_PARAMS['type'] = 'stats_device';
        $device_results = $this->get_result($params,$start,$end);
        $device_list = $device_results['hits']['hits'];
        $role_sid = '';
        foreach($role_list as $k=>$d){
            $role_sid .= $d['_source']['SID'].',';
        }
        $role_sid_list = explode(',',trim($role_sid,','));
        $list = array();
        foreach($device_list as $item =>$vo){
            if(!in_array($vo['_source']['SID'],$role_sid_list)){
                array_push($list,$vo['_source']);
            }
        }
        if($list){
            $this->master_excel_out_role($list);
        }else{
            die('没有要导出的数据');
        }
    }

    public function get_result($params,$start,$end,$size=10000){
        $must = '{"term": {"AppID": "'.$params['app_id'].'"}},
                {"range":{
                    "RegTime":{
                        "gte":"'.$start.'",
                        "lt":"'.$end.'"
                        }}
                }';
        $json = '{"query":{"bool":{
                   "must":['.$must.'],
                   "must_not":[],
                   "should":[]
               }},
                "sort":[{"RegTime":{"order":"asc"}}],
                "size":'.$size.',
                "aggs":{}
               }';
        $this->ES_PARAMS['body'] = $json;
        $results = $this->ES->search($this->ES_PARAMS);
        if($results['hits']['total'] > '9999'){
            $results['hits']['total']='10000';
        }
        return $results;
    }

    private function master_excel_out_role($data){
        set_time_limit(0);
        $str_now=date("Y-m-d H:i:s",strtotime("now"));
        $objExcel = new PHPExcel();
        $objExcel->setActiveSheetIndex(0);
        $objActSheet = $objExcel->getActiveSheet();
        $objActSheet->setTitle("设备未注册数据");
        $objActSheet->setCellValue("A1", "ID");
        $objActSheet->setCellValue("B1", "APPID");
        $objActSheet->setCellValue("C1", "语言");
        $objActSheet->setCellValue("D1", "型号");
        $objActSheet->setCellValue("E1", "活跃IP");
        $objActSheet->setCellValue("F1", "注册IP");
        $objActSheet->setCellValue("G1", "设备号");
        $objActSheet->setCellValue("H1", "SDK版本");
        $objActSheet->setCellValue("I1", "安卓版本");
        $objActSheet->setCellValue("J1", "渠道");
        $objActSheet->setCellValue("K1", "注册时间");
        $objActSheet->setCellValue("L1", "活跃时间");
        $n = 2;
        foreach($data as $info){
            $objActSheet->setCellValue("A".$n, $info['_source']['ID']);
            $objActSheet->setCellValue("B".$n, $info['_source']['AppID']);
            $objActSheet->setCellValue("C".$n, $info['_source']['Lang']);
            $objActSheet->setCellValue("D".$n, $info['_source']['DeviceModel']);
            $objActSheet->setCellValue("E".$n, $info['_source']['ActIP']);
            $objActSheet->setCellValue("F".$n, $info['_source']['RegIP']);
            $objActSheet->setCellValue("G".$n, $info['_source']['SID']);
            $objActSheet->setCellValue("H".$n, $info['_source']['SDKVer']);
            $objActSheet->setCellValue("I".$n, $info['_source']['OSVer']);
            $objActSheet->setCellValue("J".$n, $info['_source']['Channel']);
            $objActSheet->setCellValue("K".$n, date('Y-m-d H:i:s',$info['_source']['RegTime']));
            $objActSheet->setCellValue("L".$n, date('Y-m-d H:i:s',$info['_source']['ActTime']));
            $n++;
        }
        $title = iconv("UTF-8", "GB2312//IGNORE","设备未注册数据-".$str_now.'.xls');
        header("Content-type: text/html;charset=utf-8");
        header("Content-Type: application/force-download");
        header("Content-Type: application/octet-stream");
        header("Content-Type: application/download");
        header('Content-Disposition:inline;filename="'.$title.'"');
        header("Content-Transfer-Encoding: binary");
        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
        header("Pragma: no-cache");
        $objWriter = PHPExcel_IOFactory::createWriter($objExcel, 'Excel5');
        $objWriter->save('php://output');
    }

}