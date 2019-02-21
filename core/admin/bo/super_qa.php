<?php
COMMON('adminBaseCore','pageCore','uploadHelper');
DAO('qa_dao');
class super_qa extends adminBaseCore{
    public $qa_user;
    public $ES;
    public $ES_PARAMS;
    public $DAO;
    public function __construct() {
        parent::__construct();
        //$this->qa_user = array(57,13478,15312);
        $this->DAO = new qa_dao();
        $hosts = [ ES_HOST.":".ES_PORT];
        $this->ES = Elasticsearch\ClientBuilder::create()->setHosts($hosts)->build();
        $this->ES_PARAMS = array();
        $this->ES_PARAMS['index'] = 'sdk_log';
    }

    public function qa_device_view(){
        if($_POST){
            $_SESSION['qa_device_view'] = $params = $_POST;
        }elseif(!$_GET['page']){
            unset($_SESSION['qa_device_view']);
        }else{
            $params = $_SESSION['qa_device_view'];
        }
        $from = $this->getFrom();
        $this->ES_PARAMS['type']  = 'super_stats_device';
//        $this->ES_PARAMS['type']  = 'stats_device';
        $must = '';
        $search_str = "";
        if($_SESSION['usr_id'] == '712' or $_SESSION['usr_id'] == '713'){
            $params['app_id'] = 7026;
        }
        if($_SESSION['group_id']==1 or $_SESSION['group_id']==7){
            if(!empty($params['app_id'])){
                $must = '{"term":{"AppID":"'.$params['app_id'].'"}},';
            }
            if(!empty($params['id'])){
                $must .= '{"term":{"ID":"'.$params['id'].'"}},';
            }
            if(!empty($params['act_ip'])){
                $must .= '{"term":{"ActIP":"'.$params['act_ip'].'"}},';
            }
            if(!empty($params['device_model'])){
                $must .= '{"term":{"DeviceModel":"'.$params['device_model'].'"}},';
            }
            if(!empty($params['os_ver'])){
                $must.='{"term":{"OSVer":"'.$params['os_ver'].'"}},';
            }
            if(!empty($params['ch_code'])){
                $must.='{"term":{"Channel":"'.$params['ch_code'].'"}},';
            }
            if(!empty($params['sid'])){
                $must.='{"term":{"SID":"'.trim($params['sid']).'"}},';
            }
            if((!empty($params['start_time'])) && (!empty($params['end_time']))){
                if ($params['time_type']==1){
                    $must.='{"range":{"ActTime":{
                    "gte":'.strtotime($params['start_time']).',
                    "lte":'.strtotime($params['end_time']).'
                    }}},';
                }elseif ($params['time_type']==2){
                    $must.='{"range":{"RegTime":{
                    "gte":'.strtotime($params['start_time']).',
                    "lte":'.strtotime($params['end_time']).'
                    }}},';
                }
            }
        }
        if($_SESSION['group_id'] != 1){
            if(empty($params['app_id'])){
                if(!$_SESSION['rh_apps']){
                    die("你还未分配融合产品，请联系管理员");
                }
                $apps = str_replace(",", '","', $_SESSION['rh_apps']);
                $must .= '{"terms":{"AppID":["' . $apps . '"]}},';
            }else{
                $must .= '{"term":{"AppID":"' . $params['app_id'] . '"}},';
            }
        }
        if(!empty($must)){
            $must = substr($must, 0, -1);
        }
        $json = '{"query":{"bool":{
                    "must":['.$must.'],
                    "must_not":[],
                    "should":['.$search_str.']
                }},
                "sort":[{"ActTime":{"order":"desc"}}],
                "from":'.$from.',
                "size":20,
                "aggs":{}
                }';

        $this->ES_PARAMS['body'] = $json;
        $results = $this->ES->search($this->ES_PARAMS);
        if($results['hits']['total'] > '9999'){
            $results['hits']['total']='10000';
        }
         $channel_list = $this->DAO->get_ch_all();
        $app_dao = new app_admin_dao();
        $page = $this->page_show($this->page, $results['hits']['total'], "super_qa.php?act=device&");
        $this->assign("page_bar", $page->show());
        $this->assign("apps", $app_dao->get_super_user_apps());
        $this->assign("channel_list", $channel_list);
        $this->assign("params", $params);
        $this->assign("total", $results['hits']['total']);
        $this->assign("datas", $results['hits']['hits']);
        $this->display("super_sdk/qa_device.html");
    }

    public function qa_role_view(){
//        $this->ES_PARAMS['type']  = 'stats_user_app';
        if($_POST){
            $_SESSION['qa_role_view'] = $params = $_POST;
        }elseif(!$_GET['page']){
            unset($_SESSION['qa_role_view']);
        }else{
            $params = $_SESSION['qa_role_view'];
        }
        $this->ES_PARAMS['type']  = 'super_user_app';
        $from = $this->getFrom();
        $must = '';
        $search_str = "";
        if($_SESSION['usr_id'] == '712' or $_SESSION['usr_id'] == '713'){
            $params['app_id'] = 7026;
        }
        if($_SESSION['group_id']==1 or $_SESSION['group_id']==7){
            if(!empty($params['app_id'])){
                $must = '{"term":{"AppID":"'.$params['app_id'].'"}},';
            }
            if(!empty($params['user_id'])){
                $must .= '{"term":{"UserID":"'.$params['user_id'].'"}},';
            }
            if(!empty($params['ip'])){
                $must .= '{"term":{"ActIP":"'.$params['ip'].'"}},';
            }
            if(!empty($params['role_id'])){
                $must .= '{"term":{"RoleID":"'.$params['role_id'].'"}},';
            }
            if(!empty($params['area_server_id'])){
                $must.='{"term":{"AreaServerID":"'.$params['area_server_id'].'"}},';
            }
            if(!empty($params['role_name'])){
                $must.='{"term":{"RoleName":"'.trim($params['role_name']).'"}},';
            }
            if(!empty($params['ch_code'])){
                $must.='{"term":{"Channel":"'.$params['ch_code'].'"}},';
            }
            if((!empty($params['start_time'])) && (!empty($params['end_time']))){
                if ($params['time_type']==1){
                    $must.='{"range":{"ActTime":{
                    "gte":'.strtotime($params['start_time']).',
                    "lte":'.strtotime($params['end_time']).'
                    }}},';
                }elseif ($params['time_type']==2){
                    $must.='{"range":{"RegTime":{
                    "gte":'.strtotime($params['start_time']).',
                    "lte":'.strtotime($params['end_time']).'
                    }}},';
                }
            }
        }
        if($_SESSION['group_id'] != 1){
            if(empty($params['app_id'])){
                if(!$_SESSION['rh_apps']){
                    die("你还未分配融合产品，请联系管理员");
                }
                $apps = str_replace(",", '","', $_SESSION['rh_apps']);
                $must .= '{"terms":{"AppID":["' . $apps . '"]}},';
            }else{
                $must .= '{"term":{"AppID":"' . $params['app_id'] . '"}},';
            }
        }
        if(!empty($must)){
            $must = substr($must, 0, -1);
        }
        $json = '{"query":{"bool":{
                "must":['.$must.'],
                "must_not":[],
                "should":['.$search_str.']
                }},
                "sort":[{"ActTime":{"order":"desc"}}],
                "from":'.$from.',
                "size":20,
                "aggs":{}
                }';
        $this->ES_PARAMS['body'] = $json;
        $results = $this->ES->search($this->ES_PARAMS);
        if($results['hits']['total'] > '9999'){
            $results['hits']['total']='10000';
        }
        $channel_list = $this->DAO->get_ch_all();
        $app_dao = new app_admin_dao();
        $page = $this->page_show($this->page, $results['hits']['total'], "super_qa.php?act=role&");
        $this->assign("page_bar", $page->show());
        $this->assign("apps", $app_dao->get_super_user_apps());
        $this->assign("channel_list", $channel_list);
        $this->assign("params", $params);
        $this->assign("total", $results['hits']['total']);
        $this->assign("datas", $results['hits']['hits']);
        $this->display("super_sdk/qa_role.html");
    }

    public function role_export(){
        $params = $_SESSION['qa_role_view'];
        $this->ES_PARAMS['type']  = 'super_user_app';
        $from = $this->getFrom();
        $must = '';
        $search_str = "";
        if($_SESSION['group_id']==1){
            if(!empty($params['app_id'])){
                $must = '{"term":{"AppID":"'.$params['app_id'].'"}},';
            }
            if(!empty($params['user_id'])){
                $must .= '{"term":{"UserID":"'.$params['user_id'].'"}},';
            }
            if(!empty($params['ip'])){
                $must .= '{"term":{"ActIP":"'.$params['ip'].'"}},';
            }
            if(!empty($params['role_id'])){
                $must .= '{"term":{"RoleID":"'.$params['role_id'].'"}},';
            }
            if(!empty($params['area_server_id'])){
                $must.='{"term":{"AreaServerID":"'.$params['area_server_id'].'"}},';
            }
            if(!empty($params['role_name'])){
                $must.='{"term":{"RoleName":"'.trim($params['role_name']).'"}},';
            }
            if(!empty($params['ch_code'])){
                $must.='{"term":{"Channel":"'.$params['ch_code'].'"}},';
            }
            if((!empty($params['start_time'])) && (!empty($params['end_time']))){
                if ($params['time_type']==1){
                    $must.='{"range":{"ActTime":{
                    "gte":'.strtotime($params['start_time']).',
                    "lte":'.strtotime($params['end_time']).'
                    }}},';
                }elseif ($params['time_type']==2){
                    $must.='{"range":{"RegTime":{
                    "gte":'.strtotime($params['start_time']).',
                    "lte":'.strtotime($params['end_time']).'
                    }}},';
                }
            }
        }
        if($_SESSION['group_id'] != 1){
            if(empty($params['app_id'])){
                if(!$_SESSION['rh_apps']){
                    die("你还未分配融合产品，请联系管理员");
                }
                $apps = str_replace(",", '","', $_SESSION['rh_apps']);
                $must .= '{"terms":{"AppID":["' . $apps . '"]}},';
            }else{
                $must .= '{"term":{"AppID":"' . $params['app_id'] . '"}},';
            }
        }
        if(!empty($must)){
            $must = substr($must, 0, -1);
        }
        $json = '{"query":{"bool":{
                "must":['.$must.'],
                "must_not":[],
                "should":['.$search_str.']
                }},
                "sort":[{"ActTime":{"order":"desc"}}],
                "from":'.$from.',
                "size":10000,
                "aggs":{}
                }';
        $this->ES_PARAMS['body'] = $json;
        $results = $this->ES->search($this->ES_PARAMS);
        if($results['hits']['total'] > '9999'){
            $results['hits']['total']='10000';
        }
        $this->excel_do($results['hits']['hits']);
    }

    function excel_do($data){
        set_time_limit(0);
        $str_now=date("Y-m-d H:i:s",strtotime("now"));
        $objExcel = new PHPExcel();
        $objExcel->setActiveSheetIndex(0);
        $objActSheet = $objExcel->getActiveSheet();
        $objActSheet->setTitle("融合角色数据");
        $objActSheet->getColumnDimension('D')->setWidth(20);
        $objActSheet->getColumnDimension('F')->setWidth(20);
        $objActSheet->getColumnDimension('H')->setWidth(20);
        $objActSheet->getColumnDimension('I')->setWidth(20);
        $objActSheet->getColumnDimension('J')->setWidth(20);
        $objActSheet->setCellValue("A1", "用户ID");
        $objActSheet->setCellValue("B1", "APPID");
        $objActSheet->setCellValue("C1", "角色ID");
        $objActSheet->setCellValue("D1", "角色名称");
        $objActSheet->setCellValue("E1", "角色等级");
        $objActSheet->setCellValue("F1", "区服名");
        $objActSheet->setCellValue("G1", "区服ID");
        $objActSheet->setCellValue("H1","注册时间");
        $objActSheet->setCellValue("I1", "活跃时间");
        $objActSheet->setCellValue("J1", "IP");
        $objActSheet->setCellValue("K1", "渠道代码");
        $n = 2;
        foreach($data as $info){
            $objActSheet->setCellValue("A".$n, $info['_source']['UserID']);
            $objActSheet->setCellValue("B".$n, $info['_source']['AppID']);
            $objActSheet->setCellValueExplicit("C".$n,$info['_source']['RoleID'],PHPExcel_Cell_DataType::TYPE_STRING);
            $objActSheet->setCellValue("D".$n, $info['_source']['RoleName']);
            $objActSheet->setCellValue("E".$n, $info['_source']['RoleLevel']);
            $objActSheet->setCellValue("F".$n, $info['_source']['AreaServerName']);
            $objActSheet->setCellValue("G".$n, $info['_source']['AreaServerID']);
            $objActSheet->setCellValue("H".$n, date("Y-m-d H:i:s",$info['_source']['RegTime']));
            $objActSheet->setCellValue("I".$n, date("Y-m-d H:i:s",$info['_source']['ActTime']));
            $objActSheet->setCellValue("J".$n, $info['_source']['ActIP']);
            $objActSheet->setCellValue("K".$n, $info['_source']['Channel']);
            $n++;
        }
//        $objWriter = PHPExcel_IOFactory::createWriter($objExcel, 'Excel2007');
        $title = iconv("UTF-8", "GB2312//IGNORE","角色数据-".$str_now.'.xls');
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

    public function qa_login_view(){
//        $this->ES_PARAMS['type']  = 'user_op_log';
        if($_POST){
            $_SESSION['qa_login_view'] = $params = $_POST;
        }elseif(!$_GET['page']){
            unset($_SESSION['qa_login_view']);
        }else{
            $params = $_SESSION['qa_login_view'];
        }
        if($_SESSION['usr_id'] == '712' or $_SESSION['usr_id'] == '713'){
            $params['app_id'] = 7026;
        }
        $this->ES_PARAMS['type']  = 'ch_user_op_log';
        $from = $this->getFrom();
        $must = "";
        if($_SESSION['group_id']==1 or $_SESSION['group_id']==7){
            if(!empty($params['app_id'])){
                $must.='{"term":{"appid":"'.$params['app_id'].'"}},';
            }
            if(!empty($params['user_id'])){
                $must.='{"match":{"userid":"'.trim($params['user_id']).'"}},';
            }
            if(!empty($params['idfa'])){
                $must.='{"match":{"idfa":"'.trim($params['idfa']).'"}},';
            }
            if(!empty($params['ip'])){
                $must.='{"term":{"ip":"'.$params['ip'].'"}},';
            }
            if(!empty($params['ch_code'])){
                $must.='{"term":{"channel":"'.$params['ch_code'].'"}},';
            }
            if ((!empty($params['start_time'])) && (!empty($params['end_time']))){
                $must.='{"range":{"addtime":{
                    "gte":'.strtotime($params['start_time']).',
                    "lte":'.strtotime($params['end_time']).'
                }}},';
            }
        }
        if($_SESSION['group_id'] != 1){
            if(empty($params['app_id'])){
                if(!$_SESSION['rh_apps']){
                    die("你还未分配融合产品，请联系管理员");
                }
                $apps = str_replace(",", '","', $_SESSION['rh_apps']);
                $must .= '{"terms":{"appid":["' . $apps . '"]}},';
            }else{
                $must .= '{"term":{"appid":"' . $params['app_id'] . '"}},';
            }
        }
        if(!empty($must)){
            $must = substr($must, 0, -1);
        }
        $json = '{"query":{"bool":{
                "must":['.$must.'],
                "must_not":[],
                "should":[]
                }},
                "sort":[{"addtime":{"order":"desc"}}],
                "from":'.$from.',
                "size":20,
                "aggs":{}
         }';
        $this->ES_PARAMS['body'] = $json;
        $results = $this->ES->search($this->ES_PARAMS);
        if($results['hits']['total'] > '9999'){
            $results['hits']['total']='10000';
        }
        $channel_list = $this->DAO->get_ch_all();
        $app_dao = new app_admin_dao();
        $page = $this->page_show($this->page, $results['hits']['total'], "super_qa.php?act=login&");
        $this->assign("page_bar", $page->show());
        $this->assign("apps", $app_dao->get_super_user_apps());
        $this->assign("channel_list",$channel_list);
        $this->assign("params",$params);
        $this->assign("total", $results['hits']['total']);
        $this->assign("datas", $results['hits']['hits']);
        $this->display("super_sdk/qa_login.html");
    }

    public function getFrom(){
        $from = 0;
        if($this->page > 1){
            $from = ($this->page - 1) * 20;
        }
        return $from;
    }

    function page_show($page,$count,$url){
        // 格式化页码
        if(!$page || intval($page) < 1) {
            $page = 1;
        }elseif($page>(ceil($count/PERPAGE))) {
            $page=ceil($count/PERPAGE);
        }
        $page = new pageCore(array('total'=>$count,'perpage'=>PERPAGE,'nowindex'=>$page,'url'=>$url));
        return $page;
    }

    public function qa_h5_device(){
        $params = $this->get_params($_POST,$_GET);
        $datas = $this->DAO->get_super_h5_device($this->page,$params);
        $app_dao = new app_admin_dao();
        $page = $this->pageshow($this->page,"super_qa.php?act=h5_device&");
        $this->assign("page_bar", $page->show());
        $this->assign("apps", $app_dao->get_super_user_apps());
        $this->assign("guild_list",$this->DAO->get_ch_all());
        $this->assign("params",$params);
        $this->assign('datas',$datas);
        $this->display("super_sdk/qa_h5_device.html");
    }

    public function qa_h5_role(){
        $params = $this->get_params($_POST,$_GET);
        $datas = $this->DAO->get_super_h5_role($this->page,$params);
        $app_dao = new app_admin_dao();
        $page = $this->pageshow($this->page,"super_qa.php?act=h5_role&");
        $this->assign("page_bar", $page->show());
        $this->assign("apps", $app_dao->get_super_user_apps());
        $this->assign("guild_list",$this->DAO->get_ch_all());
        $this->assign("params",$params);
        $this->assign('datas',$datas);
        $this->display("super_sdk/qa_h5_role.html");
    }

    public function qa_h5_login(){
        $params = $this->get_params($_POST,$_GET);
        $datas = $this->DAO->get_super_h5_login($this->page,$params);
        $app_dao = new app_admin_dao();
        $page = $this->pageshow($this->page,"super_qa.php?act=h5_login&");
        $this->assign("page_bar", $page->show());
        $this->assign("apps", $app_dao->get_super_user_apps());
        $this->assign("guild_list",$this->DAO->get_ch_all());
        $this->assign("params",$params);
        $this->assign('datas',$datas);
        $this->display("super_sdk/qa_h5_login.html");
    }

    public function index(){
        $rh_h5_apps = $this->DAO->get_rh_h5_apps();
        $ch_list = $this->DAO->get_ch_all();
        $this->assign("apps", $rh_h5_apps);
        $this->assign("ch_list", $ch_list);
        $this->display("rh_kpi/h5_index.html");
    }

    public function idx_game_data(){
        $params = $_POST;
        $app_id = $this->get_rh_app_id($_GET['appids']);
        if($app_id){
            $apps = explode(",", $app_id);
        }
        $channels = $this->get_rh_channel($_GET['channels']);
        switch($params['date_type']){
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
        if(is_array($apps)){
            $data = $this->get_app_day_data($apps,$start_date,$end_date,$channels);
        }else{
            $data = array();
        }
        $this->assign('params',$params);
        $this->assign('apps_data',$data['apps_data']);
        $this->assign("new_user_total", $data['new_user_total']);
        $this->assign("new_device_total", $data['new_device_total']);
        $this->assign("new_role_total", $data['new_role_total']);
        $this->assign("money_total", $data['money_total']);
        $this->assign('date_type',$params['date_type']);
        $this->assign("app_id", $app_id);
        $this->assign("channel", $_GET['channels']);
        $this->assign("end_date", $end_date);
        $this->assign("start_date", $start_date);
        $this->display("rh_kpi/h5_idx_game_view.html");
    }

    public function get_app_day_data($app_list,$start_date='',$end_date='',$channels=''){
        if(is_array($channels)){
            $channels = implode('","',$channels);
        }
        $app_data = array();
        $new_user_total = 0;
        $new_device_total = 0;
        $new_role_total = 0;
        $money_total = 0;
        foreach($app_list as $key=>$data){
            $user = $this->DAO->get_h5_user_num($data,$start_date,$end_date,$channels);
            $device = $this->DAO->get_h5_device_num($data,$start_date,$end_date,$channels);
            $role = $this->DAO->get_h5_role_num($data,$start_date,$end_date,$channels);
            $money = $this->DAO->get_h5_money_num($data,$start_date,$end_date,$channels);
            $app_info = $this->DAO->get_h5_app_info($data);

            $app_data[$data]['new_user'] = $user['num'];
            $app_data[$data]['new_device'] = $device['num'];
            $app_data[$data]['new_role'] = $role['num'];
            $app_data[$data]['money_total'] = $money['money'];
            $app_data[$data]['app_name'] = $app_info['app_name'];

            $new_user_total = $new_user_total+$user['num'];
            $new_device_total = $new_device_total+$device['num'];
            $new_role_total = $new_role_total+$role['num'];
            $money_total = $money_total+$money['money'];
        }
        $data = array(
            'new_user_total'=>$new_user_total,
            'new_device_total'=>$new_device_total,
            'new_role_total'=>$new_role_total,
            'money_total'=>$money_total,
            'apps_data'=>$app_data,
        );
        return $data;
    }

    public function get_rh_app_id($app_id=""){
        if(!empty($app_id)){
            return $app_id;
        }else{
            $apps = $this->DAO->get_rh_h5_apps();
            $apps_list = array();
            foreach($apps as $item){
                array_push($apps_list,$item['app_id']);
            }
            $app_id = implode(",", $apps_list);
            return $app_id;
        }
    }

    public function get_rh_channel($channel){
        if(empty($channel)){
            return "";
        }
        $channels = $this->DAO->get_channel_info($channel);
        if(!$channels){
            return "";
        }
        return $channel;
    }
    
    public function all_channel($app_id){
        $apps = $this->DAO->get_rh_h5_apps();
        $app_info = $this->DAO->get_h5_app_info($app_id);
        //获取所有渠道
        $ch_all = $this->DAO->get_ch_all($app_id);
        $start_date = date("Y-m-d",time());
        $end_date = date('Y-m-d', time()+86400);
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
        foreach($ch_all as $val){
            $user = $this->DAO->get_h5_user_num($app_id,$start_date,$end_date,$val['ch_code']);
            $device = $this->DAO->get_h5_device_num($app_id,$start_date,$end_date,$val['ch_code']);
            $role = $this->DAO->get_h5_role_num($app_id,$start_date,$end_date,$val['ch_code']);
            $money = $this->DAO->get_h5_money_num($app_id,$start_date,$end_date,$val['ch_code']);
            $person = $this->DAO->get_h5_pay_person_num($app_id,$start_date,$end_date,$val['ch_code']);
            $app_data_channel = array(
                "channel"=>$val['ch_code'],
                "sum_new_user"=>$user['num'],
                "sum_new_device"=>$device['num'],
                "sum_new_role"=>$role['num'],
                "sum_pay_count"=>$person['num'],
                "sum_pay"=>$money['money']
            );
            array_push($app_data_channel_all,$app_data_channel);
            $channel_all .= $val['ch_code']."','";
            $sum_new_user_all .= $user['num']."','";
            $sum_act_user_all .= $device['num']."','";
        }
        $channel_all = trim($channel_all,"','");
        $sum_new_user_all = trim($sum_new_user_all,"','");
        $sum_act_user_all = trim($sum_act_user_all,"','");
        $this->assign("app_data_all",$app_data_channel_all);
        $this->assign("channel_all",$channel_all);
        $this->assign("sum_new_user_all",$sum_new_user_all);
        $this->assign("sum_act_user_all",$sum_act_user_all);
        $this->assign("app_name", $app_info['app_name']);
        $this->assign("appid", $app_id);
        $this->assign("apps",$apps);
        $this->assign("start_date", $start_date);
        $this->assign("end_date", $end_date);
        $this->display("rh_kpi/h5_game_all_channel.html");
    }
}