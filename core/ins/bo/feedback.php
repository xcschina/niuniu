<?php
COMMON('sdkCore','class.phpmailer', 'class.smtp','uploadHelper');
DAO('feedback_dao');

class feedback extends sdkCore{
    public $DAO;

    public function __construct(){
        parent::__construct();
        $this->DAO = new feedback_dao();
    }

    public function view($appid, $user_id, $osver, $gamever, $net, $mtype, $osname, $logintype, $sdkver){
        //把用户id和游戏id写到memcached下
        $this->set_usr_session('user', array('appid' => $appid, 'usr_id' =>$user_id, 'gamever'=>$gamever, 'net'=>$net,
            'mtype'=>$mtype, 'osname'=>$osname, 'osver'=>$osver, 'logintype'=>$logintype,
            'sdkver'=>$sdkver, 'language' => 'cn'));

        $qa = $this->DAO->get_fag($appid);
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
        $this->V->assign('appid', $appid);
        $this->V->assign('user_id', $user_id);
        $this->V->assign('info', $info);
        $this->V->assign('question', $question);
        $notice = $this->DAO->get_notice_by_appid($this->usr_params['pid']);
        $this->V->assign('qa', $qa);
        $this->V->assign('notice', $notice);
        $this->V->assign('user_id',$this->usr_params['usr_id']);
        $this->V->assign('appid',$this->usr_params['pid']);
        $this->V->display('help_v2/service_center.html');

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

    public function problem_feedback($osname) {
        if(empty($this->usr_params['usr_id']) || empty($this->usr_params['pid'])){
            $this->show_error("无法获取用户信息,请重新登录");
            exit();
        }
        $problem_type=array(
            1=>'其他问题',
            2=>'账号问题',
            3=>'充值问题',
            4=>'游戏问题'

        );
        $user_game_info = $this->get_my_games($this->usr_params['usr_id'], $this->usr_params['pid']);
        $this->V->assign('system',$osname);
        $this->V->assign('services',$user_game_info);
        $this->V->assign('type', $problem_type);
        $this->V->display('help_v2/service_work_order.html');
    }

    public function account_retrieve($osname,$uuid,$appid) {
        $info = $this->DAO->get_uuid_info($uuid);
        $this->V->assign('info',$info);
        $this->V->assign('uuid',$uuid);
        $this->V->assign('system',$osname);
        $this->V->assign('appid',$appid);
        $this->V->display('help_v2/service_account_find.html');
    }

    public function account_submit(){
        $data = $_POST;
        $data['creation_time']= strtotime($data['creation_time']);
        $data['last_time']= strtotime($data['last_time']);
        if($data['creation_time']>$data['last_time']){
            die(json_encode(array("result" => 0,"desc" => "注册时间不能比最后登录时间晚")));
        }
        if(empty($data['other'])){
            $data['other']="";
        }
        $data['img_path'] = "";
        //图片处理
        $img_path = $this->upload_img();
        if (!empty($img_path) && $img_path) {
            $imgs = implode("|", $img_path);
            $data['img_path'] = $imgs;
        }
        $params = $this->usr_params;
        $data['appid'] = $params['pid'];

        $id = $this->DAO->insert_account_back($data);
        if (!$id) {
            die(json_encode(array("result" => 0,"desc" => 'Network request error,Please resubmit！')));
        }
        die(json_encode(array("result" => 1,"desc" => 'success')));
    }

    public function problem_submit(){
        $user = $this->get_usr_session('user');
        unset($this->usr_params['mobile']);
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
        $data['img_path']="";
        $img_path = $this->upload_img();
        if(!empty($img_path) && $img_path){
            $imgs = implode("|", $img_path);
            $data['img_path'] = $imgs;
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
            die(json_encode(array("result" => 0,"desc" => $msg)));
        }
        $id = $this->DAO->insert_feedback($data);
        if(!$id) {
            die(json_encode(array("result" => 0,"desc" => 'Network request error,Please resubmit！')));
        }
        echo json_encode(array(
            "result" => 1,
            "desc" => 'success'
        ));
    }

    public function question_detail($id, $read_status) {
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
        $this->V->assign('info',$info);
        $this->V->assign('reply_list',$reply_list);
        $this->V->display('help_v2/service_detail.html');
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

    private function upload_img(){
        $image_count = count($_FILES);
        if ($image_count > 5) {
            echo json_encode(array(
                "status" => 0,
                "msg" => 'Picture can not be more than 5'
            ));
            exit();
        }
        $img_path = array();
        for ($i = 0; $i < $image_count; $i++) {
            $img_name = "image" . $i;
            $type = trim($_FILES[$img_name]['type']);
            if (!in_array($type, array('image/jpeg', 'image/jpg', 'image/gif', 'image/png'))) {
                echo json_encode(array(
                    "result" => 0,
                    "desc" => "Image format error"
                ));
                exit();
            }
            if ($_FILES[$img_name]['tmp_name']) {
                $upload = new uploadHelper($img_name, 'images/service', 1, "image/gif|image/jpg|image/jpeg|image/png");
                $upload->upload();
            }

            if ($upload->has_err == 1) {
                echo json_encode(array(
                    "result" => 0,
                    "desc" => $upload->get_err_msgs()
                ));
                exit();
            }
            $img_path = array_merge($img_path, $upload->rel_files_path);
        }
        $ses_name = $this->usr_params['usr_id'] . "_up_imgs";
        $_SESSION[$ses_name] = $img_path;
        //存入成功
        return $img_path;
    }

    public function more_record(){
        $USR_HEADER = $this->get_usr_session('user');
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
        if(!$USR_HEADER['appid'] || !$USR_HEADER['usr_id']){
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

    public function load_more_record($appid,$user_id,$page, $num=5) {
        $info = $this->more_home_info($appid,$user_id,$page, $num);
        echo json_encode($info);
    }

    private function more_home_info($appid, $user_id,$page = 0, $num=5) {
        $items = $this->DAO->get_my_problem($appid , $user_id, $page, $num);
        $has_more = 1;
        if(count($items) < $num) {
            $has_more = 0;
        }
        return array(
            'has_more' => $has_more,
            'items' => $items
        );
    }


    private function get_home_info($page = 0, $num=10) {
//        $items = $this->DAO->get_my_problem($this->usr_params['pid'], $this->usr_params['usr_id'], $page, $num);
//        $has_more = 1;
//        if(count($items) < $num) {
//            $has_more = 0;
//        }
//        return array(
//            'has_more' => $has_more,
//            'items' => $items
//        );
        $user = $this->get_usr_session('user');
        $items = $this->DAO->get_my_problem($user['appid'], $user['usr_id']);
        $items = array_chunk($items,10);
        return $items[$page-1];
    }

    public function show_error($msg){
        $this->V->assign("msg", $msg);
        $this->V->display("error.html");
    }

    public function set_usr_session($key, $data){
        $this->DAO->set_usr_session($key, $data);
    }

    public function get_usr_session($key=''){
        return $this->DAO->get_usr_session($key);
    }

    public function is_mobile($mobile){
        return preg_match('/^13[0-9]{1}[0-9]{8}$|15[012356789]{1}[0-9]{8}$|18[0-9]{9}$|14[57]{1}[0-9]{8}$|17[0678]{1}[0-9]{8}$/', $mobile) == 1 ? true : false;
    }

    public function service_record_list(){
        $USR_HEADER = $this->get_usr_session('user');
        if(!$USR_HEADER['appid'] || !$USR_HEADER['usr_id']){
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
        $this->display("help_v2/service_record_list.html");

    }

    public function reply_question(){
        $USR_HEADER = $this->get_usr_session('user');
        $result = array("code" => 0,"desc" => "网络请求出错");
        if(!$USR_HEADER['appid'] || !$USR_HEADER['usr_id']){
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
        $USR_HEADER = $this->get_usr_session('user');
        if(!$USR_HEADER['appid'] || !$USR_HEADER['usr_id']){
            die("请先登录游戏");
        }
        $question = $this->get_question($this->page);
        $this->assign('question',$question);
        $this->display("help_v2/service_common_list.html");
    }

    public function get_question($page){
        $items = $this->DAO->get_question_list();
        $items = array_chunk($items,10);
        return $items[$page-1];
    }

    public function common_more(){
        $USR_HEADER = $this->get_usr_session('user');
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
        if(!$USR_HEADER['appid'] || !$USR_HEADER['usr_id']){
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
        $this->assign('info',$info);
        $this->display("help_v2/service_common_detail.html");
    }
}