<?php
COMMON('adminBaseCore','pageCore','uploadHelper');
DAO('qa_dao');
class qa_admin extends adminBaseCore{
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

    public function qa_pay_view(){
        $hosts = [ ES_HOST.":".ES_PORT];
        $client = Elasticsearch\ClientBuilder::create()->setHosts($hosts)->build();
        $params['index'] = 'sdk_log';
        $params['type']  = 'stats_user_app';
        $json = '{"query":{"bool":{"must":[{"term":{"UserID":'.$this->qa_user.'}}]}},"sort":[{"ActTime":{"order":"asc"}}],
        "aggregations":{"AreaServerID":{"terms":{"field":"AreaServerID"},"aggregations":{"AreaServerName":{"terms":{"field":"AreaServerName"},
        "aggregations":{"RoleID":{"terms":{"field":"RoleID"},"aggregations":{"RoleName":{"terms":{"field":"RoleName"}}}}}}}}}}';
        $params['body'] = $json;
        $results = $client->search($params);
        $es_data = $results['aggregations']['AreaServerID'];
        $data = array();
        foreach ($es_data['buckets'] as $key => $val) {
            $data[$key]["AreaServerID"]=$val['key'];
            $data[$key]["AreaServerName"]=$val['AreaServerName']['buckets'][0]['key'];
            $data[$key]["RoleID"]=$val['AreaServerName']['buckets'][0]['RoleID']['buckets'][0]['key'];
            $data[$key]["RoleName"]=$val['AreaServerName']['buckets'][0]['RoleID']['buckets'][0]['RoleName']['buckets'][0]['key'];
        }
        return $data;
    }

    public function qa_device_view(){
        if($_POST){
            $_SESSION['qa_device_view_'.$_SESSION['usr_id']] = $params = $_POST;
        }elseif(!$_GET['page']){
            unset($_SESSION['qa_device_view_'.$_SESSION['usr_id']]);
        }else{
            $params = $_SESSION['qa_device_view_'.$_SESSION['usr_id']];
        }
        $app_id = $params['app_id'];
        $from = $this->getFrom();
        $this->ES_PARAMS['type']  = 'stats_device';
        $channel = trim($params['channel']);
        $start_time = $params['start_time_de'];
        $end_time = $params['end_time_de'];
        if (!$params['time_type']){
            $time_type = 1;
        }else{
            $time_type = $params['time_type'];
        }
        if($params['index_name']){
            $this->ES_PARAMS['index'] = $params['index_name'];
        }
        $must = '';
        if($_SESSION['group_id']== 1 || $_SESSION['group_id']== 7){
            if(!empty($app_id)){
                $must.='{"term":{"AppID":"'.$app_id.'"}},';
            }
            if(!empty($channel) && !empty($params['user_code'])){
                $total_channel = $channel.",".$params['user_code'];
                $channel_str = str_replace("，",',',$total_channel);
                $channels = str_replace(",",'","',$channel_str);
                $must.='{"terms":{"Channel":["'.$channels.'"]}},';
            }elseif(!empty($channel)){
                $channel = str_replace("，",',',$channel);
                $channels = str_replace(",",'","',$channel);
                $must.='{"terms":{"Channel":["'.$channels.'"]}},';
            }elseif(!empty($params['user_code'])){
                $user_code = str_replace(",",'","',$params['user_code']);
                $must.='{"terms":{"Channel":["'.$user_code.'"]}},';
            }
            if(!empty($params['sid'])){
                $must.='{"term":{"SID":"'.$params['sid'].'"}},';
            }
            if(!empty($params['ip'])){
                $must.='{"term":{"ActIP":"'.$params['ip'].'"}},';
            }
            if(!empty($params['idfa'])){
                $must.='{"term":{"Adtid":"'.$params['idfa'].'"}},';
            }
            if ((!empty($start_time)) && (!empty($end_time))){
                if ($time_type==1){
                    $must.='{"range":{"ActTime":{
                    "gte":'.strtotime($start_time).',
                    "lte":'.strtotime($end_time).'
                    }}},';
                }elseif ($time_type==2){
                    $must.='{"range":{"RegTime":{
                    "gte":'.strtotime($start_time).',
                    "lte":'.strtotime($end_time).'
                    }}},';
                }
            }
        }else{
            if($_SESSION['group_id'] == 10 ){
                $must = '{"term":{"Channel":"'.$_SESSION['user_code'].'"}},';
            }
        }
        if($_SESSION['group_id'] != 1){
            if(empty($app_id)){
                if(!$_SESSION['apps']){
                    die("你还未分配产品，请联系管理员");
                }
                $apps = str_replace(",",'","',$_SESSION['apps']);
                $must.='{"terms":{"AppID":["'.$apps.'"]}},';
            }else{
                $must.='{"term":{"AppID":"'.$app_id.'"}},';
            }
        }
        if(!empty($must)){
            $must = substr($must, 0, -1);
        }
        $results = $this->get_result($time_type,$must,$from);
        if(!$results['hits']['total']){
            if(!$params['index_name']){
                $this->ES_PARAMS['index'] = 'ios_log';
                $results = $this->get_result($time_type,$must,$from);
                $params['index_name'] = 'ios_log';
            }
        }
        $app_dao = new app_admin_dao();
        $page = $this->page_show($this->page, $results['hits']['total'], "qa.php?act=device&");
        $this->assign("page_bar", $page->show());
        $this->assign("apps", $app_dao->get_user_apps());
        $this->assign("guild_list",$app_dao->get_guild_list());
        $this->assign("user_code",$params['user_code']);
        $this->assign("sid",$params['sid']);
        $this->assign("idfa",$params['idfa']);
        $this->assign("app_id", $app_id);
        $this->assign("channel", $channel);
        $this->assign("params", $params);
        $this->assign("index_name", $params['index_name']);
        $this->assign("total", $results['hits']['total']);
        $this->assign("datas", $results['hits']['hits']);
        $this->assign("start_time",$start_time);
        $this->assign("end_time",$end_time);
        $this->assign("time_type",$time_type);
        $this->display("qa_device.html");
    }

    public function get_result($time_type,$must,$from,$size=''){
        if(empty($size)){
            $size=20;
        }
        if ($time_type==1){
            $json = '{"query":{"bool":{
                    "must":['.$must.'],
                    "must_not":[],
                    "should":[]
                }},
                "sort":[{"ActTime":{"order":"desc"}}],
                "from":'.$from.',
                "size":'.$size.',
                "aggs":{}
                }';
        }elseif ($time_type==2){
            $json = '{"query":{"bool":{
                    "must":['.$must.'],
                    "must_not":[],
                    "should":[]
                }},
                "sort":[{"RegTime":{"order":"desc"}}],
                "from":'.$from.',
                "size":'.$size.',
                "aggs":{}
                }';
        }
        $this->ES_PARAMS['body'] = $json;
        $results = $this->ES->search($this->ES_PARAMS);
        if($results['hits']['total'] > '9999'){
            $results['hits']['total']='10000';
        }
        return $results;
    }

    public function qa_role_view(){
        if($_POST){
            $_SESSION['qa_role_view_'.$_SESSION['usr_id']] = $params = $_POST;
        }elseif(!$_GET['page']){
            unset($_SESSION['qa_role_view_'.$_SESSION['usr_id']]);
        }else{
            $params = $_SESSION['qa_role_view_'.$_SESSION['usr_id']];
        }
        $app_id = $params['app_id'];
        $from = $this->getFrom();
        $this->ES_PARAMS['type']  = 'stats_user_app';
        $channel = trim($params['channel']);
        $user_id = $params['user_id'];
        $start_time = $params['start_time_ro'];
        $end_time = $params['end_time_ro'];
        if (!$params['time_type']){
            $time_type = 1;
        }else{
            $time_type = $params['time_type'];
        }
        if($params['index_name']){
            $this->ES_PARAMS['index'] = $params['index_name'];
        }
        $must = '';
        if($_SESSION['group_id']== 1 || $_SESSION['group_id']== 7){
            if(!empty($app_id)){
                $must.='{"term":{"AppID":"'.$app_id.'"}},';
            }
            if(!empty($channel) && !empty($params['user_code'])){
                $total_channel = $channel.",".$params['user_code'];
                $channel_str = str_replace("，",',',$total_channel);
                $channels = str_replace(",",'","',$channel_str);
                $must.='{"terms":{"Channel":["'.$channels.'"]}},';
            }elseif(!empty($channel)){
                $channel = str_replace("，",',',$channel);
                $channels = str_replace(",",'","',$channel);
                $must.='{"terms":{"Channel":["'.$channels.'"]}},';
            }elseif(!empty($params['user_code'])){
                $user_code = str_replace(",",'","',$params['user_code']);
                $must.='{"terms":{"Channel":["'.$user_code.'"]}},';
            }
            if(!empty($params['sid'])){
                $must.='{"term":{"SID":"'.trim($params['sid']).'"}},';
            }
            if(!empty($params['ip'])){
                $must.='{"term":{"ActIP":"'.trim($params['ip']).'"}},';
            }
            if(!empty($params['role_name'])){
                $must.='{"wildcard":{"RoleName":"*'.trim($params['role_name']).'*"}},';
            }
            if(!empty($params['server_name'])){
                $must.='{"wildcard":{"AreaServerName":"*'.trim($params['server_name']).'*"}},';
            }
            if (!empty($user_id)){
                $must.='{"term":{"UserID":"'.$user_id.'"}},';
            }
            if ((!empty($start_time)) && (!empty($end_time))){
                if ($time_type==1){
                    $must.='{"range":{"ActTime":{
                    "gte":'.strtotime($start_time).',
                    "lte":'.strtotime($end_time).'
                    }}},';
                }elseif ($time_type==2){
                    $must.='{"range":{"RegTime":{
                    "gte":'.strtotime($start_time).',
                    "lte":'.strtotime($end_time).'
                    }}},';
                }
            }
        }else{
            if($_SESSION['group_id'] == 10 ){
                $must = '{"term":{"Channel":"'.$_SESSION['user_code'].'"}},';
            }
        }
        if($_SESSION['group_id'] != 1){
            if(empty($app_id)){
                if(!$_SESSION['apps']){
                    die("你还未分配产品，请联系管理员");
                }
                $apps = str_replace(",",'","',$_SESSION['apps']);
                $must.='{"terms":{"AppID":["'.$apps.'"]}},';
            }else{
                $must.='{"term":{"AppID":"'.$app_id.'"}},';
            }
        }
        if(!empty($must)){
            $must = substr($must, 0, -1);
        }
        $results = $this->get_result($time_type,$must,$from);
        if(!$results['hits']['total']){
            if(!$params['index_name']){
                $this->ES_PARAMS['index'] = 'ios_log';
                $results = $this->get_result($time_type,$must,$from);
                $params['index_name'] = 'ios_log';
            }
        }
        $app_dao = new app_admin_dao();
        $page = $this->page_show($this->page, $results['hits']['total'], "qa.php?act=role&");
        $this->assign("page_bar", $page->show());
        $this->assign("apps", $app_dao->get_user_apps());
        $this->assign("guild_list",$app_dao->get_guild_list());
        $this->assign("user_code",$params['user_code']);
        $this->assign("params",$params);
        $this->assign("app_id", $app_id);
        $this->assign("channel", $channel);
        $this->assign("index_name", $params['index_name']);
        $this->assign("total", $results['hits']['total']);
        $this->assign("datas", $results['hits']['hits']);
        $this->assign("user_id",$user_id);
        $this->assign("start_time",$start_time);
        $this->assign("end_time",$end_time);
        $this->assign("time_type",$time_type);
        $this->display("qa_role.html");
    }

    public function role_export(){
        $params = $_SESSION['qa_role_view_'.$_SESSION['usr_id']];
        $app_id = $params['app_id'];
        $from = $this->getFrom();
        $this->ES_PARAMS['type']  = 'stats_user_app';
        $channel = trim($params['channel']);
        $user_id = $params['user_id'];
        $start_time = $params['start_time_ro'];
        $end_time = $params['end_time_ro'];
        if (!$params['time_type']){
            $time_type = 1;
        }else{
            $time_type = $params['time_type'];
        }
        if($params['index_name']){
            $this->ES_PARAMS['index'] = $params['index_name'];
        }
        $must = '';
        if($_SESSION['group_id']== 1 || $_SESSION['group_id']== 7){
            if(!empty($app_id)){
                $must.='{"term":{"AppID":"'.$app_id.'"}},';
            }
            if(!empty($channel) && !empty($params['user_code'])){
                $total_channel = $channel.",".$params['user_code'];
                $channel_str = str_replace("，",',',$total_channel);
                $channels = str_replace(",",'","',$channel_str);
                $must.='{"terms":{"Channel":["'.$channels.'"]}},';
            }elseif(!empty($channel)){
                $channel = str_replace("，",',',$channel);
                $channels = str_replace(",",'","',$channel);
                $must.='{"terms":{"Channel":["'.$channels.'"]}},';
            }elseif(!empty($params['user_code'])){
                $user_code = str_replace(",",'","',$params['user_code']);
                $must.='{"terms":{"Channel":["'.$user_code.'"]}},';
            }
            if(!empty($params['sid'])){
                $must.='{"term":{"SID":"'.trim($params['sid']).'"}},';
            }
            if(!empty($params['ip'])){
                $must.='{"term":{"ActIP":"'.trim($params['ip']).'"}},';
            }
            if(!empty($params['role_name'])){
                $must.='{"wildcard":{"RoleName":"*'.trim($params['role_name']).'*"}},';
            }
            if(!empty($params['server_name'])){
                $must.='{"wildcard":{"AreaServerName":"*'.trim($params['server_name']).'*"}},';
            }
            if (!empty($user_id)){
                $must.='{"term":{"UserID":"'.$user_id.'"}},';
            }
            if ((!empty($start_time)) && (!empty($end_time))){
                if ($time_type==1){
                    $must.='{"range":{"ActTime":{
                    "gte":'.strtotime($start_time).',
                    "lte":'.strtotime($end_time).'
                    }}},';
                }elseif ($time_type==2){
                    $must.='{"range":{"RegTime":{
                    "gte":'.strtotime($start_time).',
                    "lte":'.strtotime($end_time).'
                    }}},';
                }
            }
        }else{
            if($_SESSION['group_id'] == 10 ){
                $must = '{"term":{"Channel":"'.$_SESSION['user_code'].'"}},';
            }
        }
        if($_SESSION['group_id'] != 1){
            if(empty($app_id)){
                if(!$_SESSION['apps']){
                    die("你还未分配产品，请联系管理员");
                }
                $apps = str_replace(",",'","',$_SESSION['apps']);
                $must.='{"terms":{"AppID":["'.$apps.'"]}},';
            }else{
                $must.='{"term":{"AppID":"'.$app_id.'"}},';
            }
        }
        if(!empty($must)){
            $must = substr($must, 0, -1);
        }
        $results = $this->get_result($time_type,$must,$from,10000);
        if(!$results['hits']['total']){
            if(!$params['index_name']){
                $this->ES_PARAMS['index'] = 'ios_log';
                $results = $this->get_result($time_type,$must,$from,10000);
            }
        }
        $this->excel_do($results['hits']['hits']);
    }

    function excel_do($data){
        set_time_limit(0);
        $str_now=date("Y-m-d H:i:s",strtotime("now"));
        $objExcel = new PHPExcel();
        $objExcel->setActiveSheetIndex(0);
        $objActSheet = $objExcel->getActiveSheet();
        $objActSheet->setTitle("角色数据");
        $objActSheet->getColumnDimension('D')->setWidth(20);
        $objActSheet->getColumnDimension('F')->setWidth(20);
        $objActSheet->getColumnDimension('H')->setWidth(20);
        $objActSheet->getColumnDimension('I')->setWidth(20);
        $objActSheet->getColumnDimension('J')->setWidth(20);
        $objActSheet->getColumnDimension('L')->setWidth(35);
        $objActSheet->setCellValue("A1", "用户ID");
        $objActSheet->setCellValue("B1", "APPID");
        $objActSheet->setCellValue("C1", "角色ID");
        $objActSheet->setCellValue("D1", "角色名");
        $objActSheet->setCellValue("E1", "等级");
        $objActSheet->setCellValue("F1", "区服名");
        $objActSheet->setCellValue("G1", "区服ID");
        $objActSheet->setCellValue("H1","注册时间");
        $objActSheet->setCellValue("I1", "活跃时间");
        $objActSheet->setCellValue("J1", "IP");
        $objActSheet->setCellValue("K1", "渠道代码");
        $objActSheet->setCellValue("L1", "SID");
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
            $objActSheet->setCellValue("L".$n, $info['_source']['SID']);
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
        if($_POST){
            $_SESSION['qa_login_view_'.$_SESSION['usr_id']] = $params = $_POST;
        }elseif(!$_GET['page']){
            unset($_SESSION['qa_login_view_'.$_SESSION['usr_id']]);
        }else{
            $params = $_SESSION['qa_login_view_'.$_SESSION['usr_id']];
        }
        $app_id = $params['app_id'];
        $from = $this->getFrom();
        $this->ES_PARAMS['type']  = 'user_op_log';
        $channel = trim($params['channel']);
        $user_id = $params['user_id'];
        if($params['index_name']){
            $this->ES_PARAMS['index'] = $params['index_name'];
        }
        $must = '';
        if($_SESSION['group_id']== 1 || $_SESSION['group_id']== 7){
            if(!empty($app_id)){
                $must.='{"term":{"appid":"'.$app_id.'"}},';
            }
            if(!empty($channel) && !empty($params['user_code'])){
                $total_channel = $channel.",".$params['user_code'];
                $channel_str = str_replace("，",',',$total_channel);
                $channels = str_replace(",",'","',$channel_str);
                $must.='{"terms":{"channel":["'.$channels.'"]}},';
            }elseif(!empty($channel)){
                $channel = str_replace("，",',',$channel);
                $channels = str_replace(",",'","',$channel);
                $must.='{"terms":{"channel":["'.$channels.'"]}},';
            }elseif(!empty($params['user_code'])){
                $user_code = str_replace(",",'","',$params['user_code']);
                $must.='{"terms":{"channel":["'.$user_code.'"]}},';
            }
            if (!empty($user_id)){
                $must.='{"term":{"userid":"'.$user_id.'"}},';
            }
            if (!empty($params['ip'])){
                $must.='{"term":{"ip":"'.$params['ip'].'"}},';
            }
        }else{
            if($_SESSION['group_id'] == 10 ){
                $must = '{"term":{"channel":"'.$_SESSION['user_code'].'"}},';
            }
        }
        if($_SESSION['group_id'] != 1){
            if(empty($app_id)){
                if(!$_SESSION['apps']){
                    die("你还未分配产品，请联系管理员");
                }
                $apps = str_replace(",",'","',$_SESSION['apps']);
                $must.='{"terms":{"appid":["'.$apps.'"]}},';
            }else{
                $must.='{"term":{"appid":"'.$app_id.'"}},';
            }
        }
        if(!empty($must)){
            $must = substr($must, 0, -1);
        }
        $results = $this->get_login_results($must,$from);
        if(!$results['hits']['total']){
            if(!$params['index_name']){
                $this->ES_PARAMS['index'] = 'ios_log';
                $results = $this->get_login_results($must,$from);
                $params['index_name'] = 'ios_log';
            }
        }
        $app_dao = new app_admin_dao();
        $page = $this->page_show($this->page, $results['hits']['total'], "qa.php?act=login&");
        $this->assign("page_bar", $page->show());
        $this->assign("apps", $app_dao->get_user_apps());
        $this->assign("guild_list",$app_dao->get_guild_list());
        $this->assign("app_id", $app_id);
        $this->assign("channel", $channel);
        $this->assign("params", $params);
        $this->assign("user_code",$params['user_code']);
        $this->assign("index_name", $params['index_name']);
        $this->assign("total", $results['hits']['total']);
        $this->assign("datas", $results['hits']['hits']);
        $this->assign("user_id",$user_id);
        $this->display("qa_login.html");
    }

    public function get_login_results($must,$from){
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
        return $results;
    }

    public function ch_device_count($app_id,$channls){
        if (!$app_id || !$channls) {
            return 0;
        }
        $this->ES_PARAMS['type'] = 'stats_device';
        $must = '{"term": {"AppID": "' . $app_id . '"}},{"term": {"Channel": "' . $channls . '"}}';
        $json = '{
        "size":0,
        "query":{
            "constant_score": {
                "filter": {
                    "bool": {
                        "must":[' . $must . ']
                    }
                }
            }
        }
        }';
        $this->ES_PARAMS['body'] = $json;
        $results = $this->ES->search($this->ES_PARAMS);
        return $results['hits']['total'];
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

    function qa_h5_device(){
        if($_POST){
            $_SESSION['qa_h5_device'] = $params = $_POST;
        }elseif(!$_GET['page']){
            unset($_SESSION['qa_h5_device']);
        }else{
            $params = $_SESSION['qa_h5_device'];
        }
        $datas = $this->DAO->get_h5_device($this->page,$params);
        $app_dao = new app_admin_dao();
        $page = $this->pageshow($this->page,"qa.php?act=h5_device&");
        $this->assign("page_bar", $page->show());
        $this->assign("apps", $app_dao->get_user_apps());
        $this->assign("guild_list",$app_dao->get_guild_list());
        $this->assign("params",$params);
        $this->assign('datas',$datas);
        $this->display("qa_h5_device.html");
    }
}