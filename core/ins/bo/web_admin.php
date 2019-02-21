<?php
COMMON('baseCore', 'pageCore','sdkCore','uploadHelper','alipay_mobile/alipay_service','alipay_mobile/alipay_notify');
DAO('android_pay_dao');
class web_admin  extends sdkCore{
    public $DAO;
    public $qa_user_id;
    public $USR_HEADER;

    public function __construct(){
        parent::__construct();
        $this->DAO = new web_dao();
        $this->qa_user_id = array(2233916,1441047,1883479,57);
        if(isset($_SERVER['HTTP_USER_AGENT1'])){
            $header = base64_decode(substr($_SERVER['HTTP_USER_AGENT1'],1));
            $header = explode("&",$header);
            foreach($header as $k=>$param){
                $param = explode("=",$param);
                if($param[0] == 'sdkver'){
                    $this->sdkver = $param[1];
                }elseif($param[0] == 'channel'){
                    $this->guild_code = $param[1];
                }elseif($param[0] == 'ver'){
                    $this->gameVer = $param[1];
                }
            }
        }
        $this->USR_HEADER =  $this->get_usr_session('USR_NEWS_HEADER');
    }

    public function message_center($app_id,$user_id,$channel){
        if(!$app_id || !$user_id || !$channel){
            die("请先登录游戏");
        }
        $this->page = 1;
        $user_message = $this->get_user_message($user_id,$app_id,$channel,$this->page);
        $this->V->assign('is_read',$this->is_read());
        $this->V->assign('is_question_read',$this->is_question_read());
        $this->V->assign('info',$user_message);
        $this->V->assign('orientation',$this->USR_HEADER['orientation']);
        $this->display("web/message_center.html");
    }

    public function message_more(){
        $USR_HEADER = $this->get_usr_session('USR_NEWS_HEADER');
        $result = array(
            'result'=> 0,
            'desc'=> '网络异常。',
            'info'=> '',
            'num'=> 0
        );
        if($_POST['page'] > 1){
            $this->page = $_POST['page'];
        }else{
            $this->page = 2;
        }
        if(!$USR_HEADER['appid'] || !$USR_HEADER['user_id'] || !$USR_HEADER['channel']){
            $result['desc']='参数异常。';
            die('0'.base64_encode(json_encode($result)));
        }
        //推送用户的消息
        $user_message = $this->get_user_message($USR_HEADER['user_id'],$USR_HEADER['appid'],$USR_HEADER['channel'],$this->page);
        if($user_message){
            $result['result'] = 1;
            $result['desc'] = '查询成功。';
            $result['info'] = $user_message;
            $result['num'] = count($user_message);
        }else{
            $result['result'] = 1;
            $result['desc'] = '查询成功';
            $result['info'] = "";
            $result['num'] = 0;
        }
        die('0'.base64_encode(json_encode($result)));
    }

    public function message_detail($appid,$user_id,$id){
        $details = $this->DAO->get_message_details($id,$appid);
        //更新已读状态
        $message_log = $this->DAO->get_message_log($id,$user_id);
        if(empty($message_log)){
            $this->DAO->add_message_log($id,$user_id,$appid);
        }elseif($message_log && $message_log['is_read']=='0'){
            $this->DAO->update_message_log($message_log['id'],$user_id,$appid);
        }
        $this->V->assign('is_read',$this->is_read());
        $this->V->assign('is_question_read',$this->is_question_read());
        $this->V->assign('details',$details);
        $this->V->assign('orientation',$this->USR_HEADER['orientation']);
        $this->display("web/message_detail.html");
    }

    public function service_account_find($osname,$uuid,$appid){
        $info = $this->DAO->get_uuid_info($uuid);
        $this->V->assign('info',$info);
        $this->V->assign('is_read',$this->is_read());
        $this->V->assign('uuid',$uuid);
        $this->V->assign('system',$osname);
        $this->V->assign('appid',$appid);
        $this->V->assign('is_question_read',$this->is_question_read());
        $this->V->assign('orientation',$this->USR_HEADER['orientation']);
        $this->display("web/service_account_find.html");
    }

    public function account_submit(){
        $data = $_POST;
        $data['creation_time']= strtotime($data['creation_time']);
        $data['last_time']= strtotime($data['last_time']);
        if(empty($data['other'])){
            $data['other']="";
        }
        $params = $this->usr_params;
        $data['appid'] = $params['pid'];
        $id = $this->DAO->insert_account_back($data);
        if (!$id) {
            die('0'.base64_encode(json_encode(array("result" => 1,"desc" => 'Network request error,Please resubmit！'))));
        }
        die('0'.base64_encode(json_encode(array("result" => 1,"desc" => 'success'))));
    }

    public function problem_submit(){
        $user = $this->get_usr_session('USR_NEWS_HEADER');
        if(!empty($user)){
            $params = array_merge($this->usr_params,$user);
            $data = array_merge($_POST,$params);
        }else{
            $params = $this->usr_params;
            $data['appid'] = $params['pid'];
            $data['gamever'] =$params['ver'];
            $data['mtype'] = $params['devicetype'];
            $data['osname'] = "";
            $data = array_merge($_POST,$this->usr_params,$data);
        }
        $msg="";
        if($data['type'] == '' || empty($data['type'])){
            $msg= "请选择问题类型!!";
        }
        if($data['server'] == '' || empty($data['server'])){
            $msg= "请选择服务器!!";
        }
        if($data['mobile'] == ''|| empty($data['mobile'])){
            $msg= "请填写手机号码!!";
        }
        if($data['content'] == ''|| empty($data['content'])){
            $msg= "请填写描述内容!!";
        }
        if(!$this->is_mobile($data['mobile'])){
            $msg= "请填写正确的手机号码!!";
        }
        if(!empty($msg)){
            die('0'.base64_encode(json_encode(array("result" => 1,"desc" => $msg))));
        }
        $id = $this->DAO->insert_feedback($data);
        if(!$id) {
            die('0'.base64_encode(json_encode(array("result" => 1,"desc" => 'Network request error,Please resubmit！'))));
        }
        die('0'.base64_encode(json_encode(array("result" => 1,"desc" => 'success'))));
    }

    public function service_center($appid, $user_id){
        $this->USR_HEADER =  $this->get_usr_session('USR_NEWS_HEADER');
        $qa = $this->DAO->get_fag($appid);
        $this->page = 1;
        $info = $this->DAO->get_service($appid, $user_id);
        foreach($info as $key=>$data){
            $time = $this->get_days($data['create_time']);
            if(date('Y-m-d H:i:s',time()) >$time[2]){
                $this->DAO->update_feedback_reply($data['id']);
                $info[$key]['question_status'] = 1;
            }
        }
        $question = $this->DAO->get_common_question();
        $question_list = $this->DAO->get_question_list();
        $this->V->assign('question_list', $question_list);
        $notice = $this->DAO->get_notice_by_appid($this->usr_params['pid']);
        $this->V->assign('appid', $appid);
        $this->V->assign('question', $question);
        $this->V->assign('user_id', $user_id);
        $this->V->assign('info', $info);
        $this->V->assign('qa', $qa);
        $this->V->assign('notice', $notice);
        $this->V->assign('is_read',$this->is_read());
        $this->V->assign('is_question_read',$this->is_question_read());
        $this->V->assign('user_id',$this->usr_params['usr_id']);
        $this->V->assign('appid',$this->usr_params['pid']);
        $this->V->assign('orientation',$this->USR_HEADER['orientation']);
        $this->display("web/service_center.html");
    }

    public function get_days($date=''){  //计算工作日
        $now = empty($date)?time():strtotime($date);
        $days = array();
        for($i=1;count($days)<3;$i++){
            $timer = $now +3600*24*$i;
            $num = date("N",$timer)-2;
            if($num>=-1 && $num<=3){
                $days[]=date('Y-m-d H:i:s',$now+3600*24*$i);
            }
        }
        return $days;
    }

    private function get_home_info($page) {
        $user = $this->get_usr_session('USR_NEWS_HEADER');
        $items = $this->DAO->get_my_problem($user['appid'], $user['user_id']);
        $items = array_chunk($items,10);
        return $items[$page-1];
    }

    public function load_more_record() {
        $USR_HEADER = $this->get_usr_session('USR_NEWS_HEADER');
        $result = array(
            'code'=> 0,
            'desc'=> '网络异常。',
            'list'=> '',
        );
        if($_POST['page'] > 1){
            $this->page = $_POST['page'];
        }else{
            $this->page = 2;
        }
        if(!$USR_HEADER['appid'] || !$USR_HEADER['user_id']){
            $result['desc']='参数异常。';
            die('0'.base64_encode(json_encode($result)));
        }
        $info = $this->get_home_info($this->page);
        if($info){
            $result['code'] = 1;
            $result['desc'] = '查询成功。';
            $result['list'] = $info;
        }else{
            $result['code'] = 1;
            $result['desc'] = '查询成功';
            $result['list'] = "";
        }
        die('0'.base64_encode(json_encode($result)));
    }

    public function service_detail($id,$read_status){
        if($read_status == 0) {
            $this->DAO->update_question_status($id, $this->usr_params['usr_id'], $this->usr_params['pid']);
        }
        $info = $this->DAO->get_feedback_info($id);
        $reply_list = $this->DAO->get_reply_list($id);
        if(!$info['app_name']){
            $info['app_name'] = $this->DAO->get_app_name($info['appid']);
        }
        if($info['img_path']) {
            $info['imgs'] = explode('|', $info['img_path']);
        }
        unset($info['img_path']);
        $info['type_name'] = $this->translate_type($info['type']);
        $this->V->assign('is_read',$this->is_read());
        $this->V->assign('is_question_read',$this->is_question_read());
        $this->V->assign('reply_list',$reply_list);
        $this->V->assign('info',$info);
        $this->V->assign('orientation',$this->USR_HEADER['orientation']);
        $this->display("web/service_detail.html");
    }

    public function translate_type($type){
        switch($type){
            case 1:
                return "其他问题";
            case 2:
                return "账号问题";
            case 3:
                return "充值问题";
            case 4:
                return "游戏问题";
            default:
                return "其他问题";
        }
    }

    public function service_work_order($osname){
        if(empty($this->usr_params['usr_id']) || empty($this->usr_params['pid'])){
            $this->show_error("无法获取用户信息,请重新登录");
            exit();
        }
        $problem_type = array(
            1=>'其他问题',
            2=>'账号问题',
            3=>'充值问题',
            4=>'游戏问题'
        );
        $user_game_info = $this->get_my_games($this->usr_params['usr_id'], $this->usr_params['pid']);
        $this->V->assign('system',$osname);
        $this->V->assign('services',$user_game_info);
        $this->V->assign('type', $problem_type);
        $this->V->assign('is_read',$this->is_read());
        $this->V->assign('is_question_read',$this->is_question_read());
        $this->V->assign('orientation',$this->USR_HEADER['orientation']);
        $this->display("web/service_work_order.html");
    }

    public function get_my_games($user_id, $appid){
        $hosts = [ ES_HOST.":".ES_PORT];
        $client = Elasticsearch\ClientBuilder::create()->setHosts($hosts)->build();
        $params = array();
        $params['index'] = 'sdk_log';
        $params['type']  = 'stats_user_app';
        $json = '{"query":{"bool":{"must":[{"term":{"AppID":'.$appid.'}},{"term":{"UserID":'.$user_id.'}}],"must_not":[{"match":{"AreaServerID":""}}]}},"sort":[{"ActTime":{"order":"asc"}}],
        "aggregations":{"AreaServerID":{"terms":{"field":"AreaServerID"},"aggregations":{"AreaServerName":{"terms":{"field":"AreaServerName"},
        "aggregations":{"RoleID":{"terms":{"field":"RoleID"},"aggregations":{"RoleName":{"terms":{"field":"RoleName"}}}}}}}}}}';
        $params['body'] = $json;
        $results = $client->search($params);
        $es_data = $results['aggregations']['AreaServerID'];
        $data = array();
        foreach ($es_data['buckets'] as $key => $val) {
            $data[$key]["AreaServerID"]=$val['key'];
            $data[$key]["AreaServerName"]=$val['AreaServerName']['buckets'][0]['key'];
            if(!$val['AreaServerName']['buckets'][0]['RoleID']['buckets'][0]['key']){
                $data[$key]["RoleID"]=$val['AreaServerName']['buckets'][0]['RoleID']['buckets'][1]['key'];
                $data[$key]["RoleName"]=$val['AreaServerName']['buckets'][0]['RoleID']['buckets'][1]['RoleName']['buckets'][0]['key'];
            }else{
                $data[$key]["RoleID"]=$val['AreaServerName']['buckets'][0]['RoleID']['buckets'][0]['key'];
                $data[$key]["RoleName"]=$val['AreaServerName']['buckets'][0]['RoleID']['buckets'][0]['RoleName']['buckets'][0]['key'];
            }
        }
        return $data;
    }

    public function get_user_message($user_id,$app_id,$channel,$page='1'){
//        $data = $this->DAO->get_mmc_data('user_msg'.$user_id."_".$app_id."_".$channel);
        if(!$data){
            $user_meassge = $this->DAO->get_user_message_list($user_id, $app_id, $channel);
            $app_meassge = $this->DAO->get_app_message_list( $app_id, $channel);
            if($app_meassge){
                foreach($app_meassge as $key => $data){
                    $msg_log = $this->DAO->get_user_msg_log($data['id'], $user_id);
                    if ($msg_log) {
                        $app_meassge[$key]['is_read'] = $msg_log['is_read'];
                    }else{
                        $app_meassge[$key]['is_read'] = 0;
                    }
                }
            }
            $user_msg = array_merge($user_meassge,$app_meassge);
            $data = $this->array_sort($user_msg,'push_time','desc');
            $this->DAO->set_mmc_data('user_msg'.$user_id."_".$app_id."_".$channel,$data);
        }
        $new_data = array_chunk($data,10);
        return $new_data[$page-1];
    }

    //多维数组通过关键字排序
    public function array_sort($array,$keys,$type='asc'){
        $keysvalue = $new_array = array();
        foreach ($array as $k => $v) {
            $keysvalue[$k] = $v[$keys];
        }
        if($type == 'asc') {
            asort($keysvalue);
        }else{
            arsort($keysvalue);
        }
        reset($keysvalue);
        foreach ($keysvalue as $k => $v) {
            $new_array[$k] = $array[$k];
        }
        return $new_array;
    }

    public function set_usr_session($key, $data){
        $this->DAO->set_usr_session($key, $data);
    }

    public function get_usr_session($key=''){
        return $this->DAO->get_usr_session($key);
    }

    public function show_error($msg){
        $this->V->assign("msg", $msg);
        $this->V->display("error.html");
    }

    public function is_read(){
        $USR_HEADER = $this->get_usr_session('USR_NEWS_HEADER');
        $is_read = $this->DAO->get_mmc_data("is_read_".$USR_HEADER['user_id'].'_'.$USR_HEADER['appid']);
        if(!$is_read){
            $user_meassge = $this->DAO->get_user_message($USR_HEADER['user_id'], $USR_HEADER['appid'], $USR_HEADER['channel']);
            $app_meassge = $this->DAO->get_app_message_list($USR_HEADER['appid'], $USR_HEADER['channel']);

            if($app_meassge){
                foreach($app_meassge as $key => $data){
                    $msg_log = $this->DAO->get_user_msg_log($data['id'], $USR_HEADER['user_id']);
                    if($msg_log) {
                        if($msg_log['is_read'] == 0){
                            $is_app = 1;
                        }
                    }else{
                        $is_app = 1;
                    }
                }
            }
            if($user_meassge){
                $is_user = 1;
            }
            if($is_app || $is_user){
                $is_read = 1;
            }
            $this->DAO->set_mmc_data("is_read_".$USR_HEADER['user_id'].'_'.$USR_HEADER['appid'],$is_read);
        }
        return $is_read;
    }

    public function service_record_list(){
        $USR_HEADER = $this->get_usr_session('USR_NEWS_HEADER');
        if(!$USR_HEADER['appid'] || !$USR_HEADER['user_id']){
            $result['desc'] = "请先登录游戏";
            die('0'.base64_encode(json_encode($result)));
        }
        $info = $this->get_home_info($this->page);
        foreach($info as $key=>$data){
            $time = $this->get_days($data['create_time']);
            if(date('Y-m-d H:i:s',time()) >$time[2]){
                $this->DAO->update_feedback_reply($data['id']);
                $info[$key]['question_status'] = 1;
            }
        }
        $this->assign('info',$info);
        $this->assign('is_read',$this->is_read());
        $this->assign('is_question_read',$this->is_question_read());
        $this->V->assign('orientation',$this->USR_HEADER['orientation']);
        $this->display("web/service_record_list.html");
    }

    public function reply_question(){
        $USR_HEADER = $this->get_usr_session('USR_NEWS_HEADER');
        $result = array("code" => 0,"desc" => "网络请求出错");
        if(!$USR_HEADER['appid'] || !$USR_HEADER['user_id']){
            $result['desc'] = "请先登录游戏";
            die('0'.base64_encode(json_encode($result)));
        }
        $params = $_POST;
        $info = $this->DAO->get_feedback_info($params['pid']);
        if($info['question_status'] == 1){
            $result['desc'] = "问题已关闭，不能再提交咯";
            die('0'.base64_encode(json_encode($result)));
        }
        if(!$params['desc']){
            $result['desc'] = "请填写回复内容";
            die('0'.base64_encode(json_encode($result)));
        }else if(strlen($params['desc']) < 10){
            $result['desc'] = "回复内容不能少于10个字符";
            die('0'.base64_encode(json_encode($result)));
        }
        $params = array_merge($params,$USR_HEADER);
        $reply_list = $this->DAO->get_question($params);
        $num = 0 ;
        foreach($reply_list as $key=>$data){
            if($data['user_id']){
                $num = $num + 1 ;
            }else{
                break ;
            }
        }
        if($num >= 3){
            $result['desc'] = '客服MM正在加紧回复，请耐心等待哟';
            die('0'.base64_encode(json_encode($result)));
        }
        $this->DAO->insert_reply_feedback($params);
        $result['code'] = 1;
        $result['desc'] = "回复成功";
        die('0'.base64_encode(json_encode($result)));
    }

    public function common_list(){
        $USR_HEADER = $this->get_usr_session('USR_NEWS_HEADER');
        if(!$USR_HEADER['appid'] || !$USR_HEADER['user_id']){
            die("请先登录游戏");
        }
        $question = $this->get_question($this->page);
        $this->V->assign('is_read',$this->is_read());
        $this->V->assign('is_question_read',$this->is_question_read());
        $this->assign('question',$question);
        $this->V->assign('orientation',$this->USR_HEADER['orientation']);
        $this->display("web/service_common_list.html");
    }

    public function get_question($page){
        $items = $this->DAO->get_question_list();
        $items = array_chunk($items,10);
        return $items[$page-1];
    }

    public function common_more(){
        $USR_HEADER = $this->get_usr_session('USR_NEWS_HEADER');
        $result = array(
            'result'=> 0,
            'desc'=> '网络异常。',
            'info'=> '',
            'num'=> 0
        );
        if($_POST['page'] > 1){
            $this->page = $_POST['page'];
        }else{
            $this->page = 2;
        }
        if(!$USR_HEADER['appid'] || !$USR_HEADER['user_id']){
            $result['desc']='参数异常。';
            die('0'.base64_encode(json_encode($result)));
        }
        //常见问题
        $question = $this->get_question($this->page);
        if($question){
            $result['result'] = 1;
            $result['desc'] = '查询成功。';
            $result['info'] = $question;
            $result['num'] = count($question);
        }else{
            $result['result'] = 1;
            $result['desc'] = '查询成功';
            $result['info'] = "";
            $result['num'] = 0;
        }
        die('0'.base64_encode(json_encode($result)));
    }

    public function common_detail($id){
        if(!$id){
            die("链接出错啦");
        }
        $info = $this->DAO->get_question_info($id);
        if(!$info){
            die("查无此常见问题");
        }
        $this->V->assign('is_read',$this->is_read());
        $this->V->assign('is_question_read',$this->is_question_read());
        $this->assign('info',$info);
        $this->V->assign('orientation',$this->USR_HEADER['orientation']);
        $this->display("web/service_common_detail.html");
    }

    public function is_question_read(){
        $USR_HEADER = $this->get_usr_session('USR_NEWS_HEADER');
        $is_read = $this->DAO->get_mmc_data("is_server_read_".$USR_HEADER['user_id'].'_'.$USR_HEADER['appid']);
        if(!$is_read){
            $user_meassge = $this->DAO->get_user_question($USR_HEADER['user_id'], $USR_HEADER['appid']);
            if($user_meassge){
                $is_user = 1;
            }
            if($is_user){
                $is_read = 1;
            }
            $this->DAO->set_mmc_data("is_server_read_".$USR_HEADER['user_id'].'_'.$USR_HEADER['appid'],$is_read);
        }
        return $is_read;
    }

    public function is_mobile($mobile){
        return preg_match('/^13[0-9]{1}[0-9]{8}$|15[012356789]{1}[0-9]{8}$|18[0-9]{9}$|14[57]{1}[0-9]{8}$|17[0678]{1}[0-9]{8}$/', $mobile) == 1 ? true : false;
    }

}