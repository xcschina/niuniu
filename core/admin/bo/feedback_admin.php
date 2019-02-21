<?php
COMMON('adminBaseCore','pageCore','uploadHelper','baseCore');
DAO('feedback_admin_dao');

class feedback_admin extends adminBaseCore{
    public $DAO;

    public function __construct(){
        parent::__construct();
        $this->DAO = new feedback_admin_dao();
    }

    public function list_view(){
        $params = $this->get_params($_POST,$_GET);
        $feedbacks_name = $this->DAO->get_feedback_name();
        $feedbacks = $this->DAO->get_list($this->page,$params);
        $page = $this->pageshow($this->page, "feedback.php?act=list&");
        $this->assign("params",$params);
        $this->assign("feedbacks",$feedbacks_name);
        $this->assign("datalist", $feedbacks);
        $this->assign("page_bar", $page->show());
        $this->display("feedback_list.html");
    }
    public function account_retrieve(){
        $params = $_GET;
        $accout_list = $this->DAO->get_account_retrieve($this->page,$params);
        $page = $this->pageshow($this->page, "feedback.php?act=account_retrieve&mobile=".$params['mobile']."&qq=".$params['qq']."&");
        $this->assign("params",$params);
        $this->V->assign("page_bar", $page->show());
        $this->V->assign('datalist', $accout_list);
        $this->V->display("account_retrieve_list.html");
    }
    public function account_edit($id) {
        $info = $this->DAO->get_account_info($id);
        if($info['img_path']) {
            $info['img_path'] = explode("|",$info['img_path']);
        }
        $this->V->assign("info",$info);
        $this->V->display("account_retrieve_edit.html");
    }

    public function do_account_edit($id) {
        $feedback = $_POST;
        if($feedback['status'] == 2 && !$feedback['reply']){
            $this->error_msg("请填写回复内容");
        } else {
            $feedback['operator_id'] = $_SESSION['usr_id'];
            $feedback['operator_time'] = time();
            $this->DAO->update_account($feedback,$id);
            $this->succeed_msg("信息提交成功");
        }
    }

    public function edit_view($id) {
        $info = $this->DAO->get_feedback($id);
        if($info['img_path']) {
            $info['img_path'] = explode("|",$info['img_path']);
        }
        $this->V->assign("info", $info);
        $this->V->display("feedback_edit.html");
    }

    public function do_edit($id){
        if (!$_POST['feedback']) {
            $this->error_msg("请填写回复信息");
        } else {
                $data=array(
                    'feedback'=>$_POST['feedback'],
                    'feedback_usr'=>$_SESSION['usr_id'],
                    'feedback_time'=>date('Y-m-d H:i:s',time())
                );
            $this->DAO->update_feedback($data,$id);
            $this->succeed_msg();
        }
    }

    public function mobile_change() {
        if($_SESSION['usr_id']!='361'){
            die("你没有该权限");
        }
        $datalist = $this->DAO->get_change_mobile_log($this->page);
        $page = $this->pageshow($this->page, "feedback.php?act=mobile_change&");
        $this->V->assign("page_bar", $page->show());
        $this->V->assign('datalist', $datalist);
        $this->V->display("mobile_change_view.html");
    }
    //增加身份证换绑
    public function idCard_change(){
        if($_SESSION['usr_id']!='84'){
            die("你没有该权限");
        }
        $this->page_hash();
        $this->V->display("idCard_change_view.html");
    }

    //修改密码
    public function change_pwd(){
        if($_SESSION['usr_id']!='136'){
            die("你没有该权限");
        }
        $this->page_hash();
        $this->V->display("change_pwd_view.html");
    }
    public function change_user_pwd(){
        if($_SESSION['usr_id']!='136'){
            die("你没有该权限");
        }
        $this->page_hash();
        $this->V->display("change_user_pwd.html");
    }
    public function add_change() {
        if($_SESSION['usr_id']!='361'){
            die("你没有该权限");
        }
        $this->page_hash();
        $this->V->display("mobile_change_add.html");
    }

    public function account_verify() {
        $data = array('error'=>1,'msg'=>'网络请求异常。');
        if(!$_POST['mobile'] ||!$_POST['pagehash']){
            $data['msg']='缺少必要参数。';
            die(json_encode($data));
        }
        if($_POST['pagehash'] != $_SESSION['page-hash']){
            $data['msg']='hash错误，请刷新后重新登录。';
            die(json_encode($data));
        }
        if(!$this->is_mobile($_POST['mobile'])){
            $data['msg']='手机格式错误。';
            die(json_encode($data));
        }
        $mobile = $_POST['mobile'];

        $user_info = $this->DAO->get_user_by_mobile($mobile);
        if(!$user_info){
            $data['msg']='未匹配到该手机号。';
            die(json_encode($data));
        }
        $data['error'] = 0;
        $data['msg'] = '查询成功。';
        $data['infos'] = $user_info;
        die(json_encode($data));
    }

    public function sms_code() {
        $phone = $_POST['mobile'];
        if($_POST['pagehash'] != $_SESSION['page-hash']){
            $msg['res']="0";//1成功0失败
            $data['msg']='hash错误，请刷新后重新登录。';
            die(json_encode($data));
        }
        $nowtime = strtotime("now");
        if(!$phone){
            $msg['res']="0";//1成功0失败
            $msg['msg']="请输入您的手机号";
            die(json_encode($msg));
        }
        if(!$this->is_mobile($phone)){
            $msg['res']="0";//1成功0失败
            $msg['msg']="验证消息发送失败,请重试";
            die(json_encode($msg));
        }
        if(isset($_SESSION['last_send_time'])){
            if($nowtime-$_SESSION['last_send_time']<60){
                $msg['res']="0";//1成功0失败
                $msg['msg']="获取验证码太频繁，请稍后再试";
                die(json_encode($msg));
            }else{
                $_SESSION['last_send_time']=$nowtime;
            }
        }else{
            $_SESSION['last_send_time']=$nowtime;
        }
        $code = str_split($_SERVER["REQUEST_TIME"] .rand(1001, 9999));
        $code = substr(implode('', array_rand($code, 6)), 0, 6);
//        $send_result = $this->send_sms($phone,array($code),"181503");
        $base = new baseCore();
        $send_result = $base->bm_send_sms($phone,array($code),4);
        $this->err_log(var_export($send_result,1),'sms');
        if($send_result){
            $_SESSION['reg_core']=$phone."_".$code;
            $msg['res']="1";//1成功0失败
            $msg['msg']="验证消息发送成功！";
//            $msg['code']=$code;
            die(json_encode($msg));
        }else{
            $msg['res']="0";//1成功0失败
            $msg['msg']="验证消息发送失败,请重试2";
            echo json_encode($msg);
        }
    }

    public function sec_sms_code() {
        $phone = $_POST['mobile'];
        $msg = array(
            'res' => "0",
            'msg' => "网络请求异常",
            'code' => ""
        );
        if($_POST['pagehash'] != $_SESSION['page-hash']){
            $data['msg']='hash错误，请刷新后重新登录。';
            die(json_encode($data));
        }
        $nowtime = strtotime("now");
        if(!$phone){
            $msg['msg']="请输入您的手机号";
            die(json_encode($msg));
        }
        if(!$this->is_mobile($phone)){
            $msg['msg']="手机格式错误";
            die(json_encode($msg));
        }
        $user_info = $this->DAO->get_user_by_mobile($phone);
        if($user_info){
            $msg['msg']="该手机已绑定有账号,无法进行改绑。";
            die(json_encode($msg));
        }
        if(isset($_SESSION['sec_last_send_time'])){
            if($nowtime-$_SESSION['sec_last_send_time']<60){
                $msg['msg']="获取验证码太频繁，请稍后再试";
                die(json_encode($msg));
            }else{
                $_SESSION['sec_last_send_time']=$nowtime;
            }
        }else{
            $_SESSION['sec_last_send_time']=$nowtime;
        }
        $code = str_split($_SERVER["REQUEST_TIME"] .rand(1001, 9999));
        $code = substr(implode('', array_rand($code, 6)), 0, 6);
//        $send_result = $this->send_sms($phone,array($code),"181503");
        $base = new baseCore();
        $send_result = $base->bm_send_sms($phone,array($code),4);
        $this->err_log(var_export($send_result,1),'sms');
        if($send_result){
            $_SESSION['sec_reg_core']=$phone."_".$code;
            $msg['res']="1";//1成功0失败
            $msg['msg']="验证消息发送成功！";
//            $msg['code']=$code;
            die(json_encode($msg));
        }else{
            $msg['msg']="验证消息发送失败,请重试2";
            echo json_encode($msg);
        }
    }
    //身份证验证
    public function reg_idCard(){
        $data = array('error'=>1,'msg'=>'');
        if($_POST['pagehash'] != $_SESSION['page-hash']){
            $data['msg'] = "hash错误，请刷新后重新登录！";
            die(json_encode($data));
        }
        $userId = $_POST['user_id'];
        $userName = $_POST['user_name'];
        $userIdCard = $_POST['user_idCard'];
        if (!$userId){
            $data['msg'] = "用户ID不能为空！";
            die(json_encode($data));
        }
        if (!$userName){
            $data['msg'] = "用户姓名不能为空！";
            die(json_encode($data));
        }
        if (!$userIdCard){
            $data['msg'] = "身份证不能为空！";
            die(json_encode($data));
        }
        if (!$this->is_id($userId)){
            $data['msg'] = "用户ID不合法！";
            die(json_encode($data));
        }
        if (!$this->is_idCard($userIdCard)){
            $data['msg'] = "身份证不合法！";
            die(json_encode($data));
        }
        $res = $this->DAO->get_user_idCard($userId,$userName,$userIdCard);
        if(!$res){
            $data['msg']='未匹配到该用户！';
            die(json_encode($data));
        }
        $data['error'] = 0;
        $data['msg'] = '查询成功！';
        $data['infos'] = $res;
        die(json_encode($data));
    }

    public function reg_user_id(){
        $data = array('error'=>1,'msg'=>'');
        if($_POST['pagehash'] != $_SESSION['page-hash']){
            $data['msg'] = "hash错误，请刷新后重新登录！";
            die(json_encode($data));
        }
        $user_id = $_POST['user_id'];
        if (!$user_id){
            $data['msg'] = "用户ID不能为空！";
            die(json_encode($data));
        }
        $res = $this->DAO->get_user_info($user_id);
        if(!$res){
            $data['msg']='未匹配到该用户！';
            die(json_encode($data));
        }
        $data['error'] = 0;
        $data['msg'] = '查询成功！';
        $data['infos'] = $res;
        die(json_encode($data));
    }
    //身份证验证规则
    public function is_idCard($str){
        return preg_match('/^[1-9]\d{5}[1-9]\d{3}((0\d)|(1[0-2]))(([0|1|2]\d)|3[0-1])\d{3}([0-9]|X)$/', $str) == 1 ? true : false;
    }
    //用户id验证规则
    public function is_id($str){
        return preg_match('/^[1-9][0-9]*$/', $str) == 1 ? true : false;
    }
    //保存用户身份证图片
    public function do_idCard(){
        if($_SESSION['usr_id']!='84'){
            $this->error_msg("你没有操作的权限，请联系管理员");
        }
        if($_POST['pagehash'] != $_SESSION['page-hash']){
            $this->error_msg("hash错误，请刷新后重新登录。");
        }
        $userId = $_POST['user_id_h'];
        $userName = $_POST['user_name_h'];
        $userIdCard = $_POST['user_idCard_h'];
        $newPhone = $_POST['new_phone'];
        $sec_sms_code = $_POST['new_phone_id'];
        if (!$userId){
            $this->error_msg("用户ID不能为空！");
        }
        if (!$userName){
            $this->error_msg("用户姓名不能为空！");
        }
        if (!$userIdCard){
            $this->error_msg("身份证不能为空！");
        }
        if (!$newPhone){
            $this->error_msg("改绑手机号不能为空！");
        }
        if (!$this->is_id($userId)){
            $this->error_msg("用户ID不合法！");
        }
        if (!$this->is_idCard($userIdCard)){
            $this->error_msg("身份证不合法！");
        }
        if (!$this->is_mobile($newPhone)){
            $this->error_msg("改绑手机号不合法！");
        }
        if($_SESSION['sec_reg_core']!=$newPhone."_".$sec_sms_code){
            $this->error_msg("改绑的手机验证码错误。");
        }
        if(strtotime("now") - $_SESSION['sec_last_send_time']>300){
            $this->error_msg("改绑的手机验证码超时。".$_SESSION['sec_last_send_time']);
        }
        if($_FILES['user_idImg']['tmp_name']){
            //判断图片类型和大小
            if (!(strpos($_FILES['user_idImg']['type'],"jpeg")||strpos($_FILES['user_idImg']['type'],"jpg")||strpos($_FILES['user_idImg']['type'],"png"))){
                $this->error_msg("身份证图片格式只能是jpeg,jpg,png！");
            }
            if($_FILES['user_idImg']['size']/(1024*1024)>2){
                $this->error_msg("身份证图片大小必须小于2M！");
            }
            $img_path = $this->up_img('user_idImg','images/user_idCard');
            $this->DAO->update_user_idCard($userId,$userName,$userIdCard,$newPhone);
            $desc = '身份证改绑成功。';
            $this->DAO->add_operation_id_log($_SESSION['usr_id'],$userId,$userIdCard,$newPhone,$desc,$img_path,2);
            $this->succeed_msg();
        }else{
            $this->error_msg("请上传身份证图片！");
        }
    }
    public function do_change() {
        if($_SESSION['usr_id']!='361'){
            $this->error_msg("你没有操作的权限，请联系管理员");
        }
        $mobile = $_POST['mobile'];
        $new_mobile = $_POST['new_mobile'];
        $sms_code = $_POST['old_code'];
        $sec_sms_code = $_POST['new_code'];
        if($_POST['pagehash'] != $_SESSION['page-hash']){
            $this->error_msg("hash错误，请刷新后重新登录。");
        }
        if(!$sms_code){
            $this->error_msg("原手机验证码不能为空。");
        }
        if(!$sec_sms_code){
            $this->error_msg("改绑手机验证码不能为空。");
        }
        if(!$mobile){
            $this->error_msg("原手机号不能为空。");
        }
        if(!$this->is_mobile($mobile)){
            $this->error_msg("原手机号格式错误。");
        }
        if(!$new_mobile){
            $this->error_msg("改绑手机号不能为空。");
        }
        if(!$this->is_mobile($new_mobile)){
            $this->error_msg("改绑手机格式错误。");
        }
        if($new_mobile==$mobile){
            $this->error_msg("改绑的手机和原手机不能相同。");
        }
        if(!isset($_SESSION['reg_core']) || $_SESSION['reg_core']!=$mobile."_".$sms_code ){
            $this->error_msg("原手机验证码错误。");
        }
        if(strtotime("now") - $_SESSION['last_send_time']>300){
            $this->error_msg("原手机验证码超时。".$_SESSION['last_send_time']);
        }
        if(!isset($_SESSION['reg_core']) || $_SESSION['sec_reg_core']!=$new_mobile."_".$sec_sms_code){
            $this->error_msg("改绑的手机验证码错误。");
        }
        if(strtotime("now") - $_SESSION['sec_last_send_time']>300){
            $this->error_msg("改绑的手机验证码超时。".$_SESSION['sec_last_send_time']);
        }
        $this->err_log(var_export($_POST,1),'change_mobie');
        $user_info = $this->DAO->get_user_by_mobile($mobile);
        $new_mobile_info = $this->DAO->get_user_by_mobile($new_mobile);
        if($new_mobile_info){
            $this->error_msg("改绑的手机已绑定过。");
        }
        $desc = '手机改绑成功。';
        $this->DAO->add_operation_log($_SESSION['usr_id'],$user_info['user_id'],$mobile,$new_mobile,$desc,1);
        $this->DAO->update_user_mobile($user_info['user_id'],$mobile,$new_mobile);
        $this->succeed_msg();

    }

    public function do_change_pwd(){
        if($_SESSION['usr_id']!='136'){
            $this->error_msg("你没有操作的权限，请联系管理员");
        }
        if($_POST['pagehash'] != $_SESSION['page-hash']){
            $this->error_msg("hash错误，请刷新后重新登录。");
        }
        $user_id = $_POST['user_id_h'];
        $password = $_POST['password'];
        $again_password = $_POST['again_password'];
        if (!$user_id){
            $this->error_msg("用户ID不能为空！");
        }
        $user_array=array(
            '189160','189161','189163','189164','189165','189166','189167','189168','189170','189173','189174','189176','189177','189178','189179','189180',
            '189182','189184','189186','189187','189188','189190','189191','189192','189194','189195','189197','189198','189199','189201','189203','189205',
            '189208','189209','189210','189212','189216','189217','189219','189220','189221','189222','189223','189224', '189225','189226','189227','189228',
            '189229','189230','189231','189232','189233','189234','189236','189239','189240','189242','189243','189244','189245','189246','71'
        );
        if(!in_array($user_id,$user_array)){
            $this->error_msg("该用户不在名单内！");
        }
        if (!$password){
            $this->error_msg("密码不能为空！");
        }
        if (strlen($password) < '6' || strlen($password) > '12'){
            $this->error_msg("密码格式错误！");
        }
        if (!$again_password){
            $this->error_msg("再次输入密码不能为空！");
        }
        if ($password != $again_password){
            $this->error_msg("两次的密码必须相同！");
        }

        $this->DAO->update_user_pwd(md5($password),$user_id);
        $desc = '修改密码成功。';
        $remark = "后台使用者：".$_SESSION['usr_id']."对用户id：".$user_id."进行修改密码操作, 密码为:".$password;
        $this->DAO->add_admin_operation_log($_SESSION['usr_id'],$desc,$remark);
        $this->succeed_msg();
    }

    public function is_mobile($str){
        return preg_match('/^13[0-9]{1}[0-9]{8}$|15[012356789]{1}[0-9]{8}$|18[0-9]{9}$|14[57]{1}[0-9]{8}$|17[03678]{1}[0-9]{8}$/', $str) == 1 ? true : false;
    }

    public function update_status(){
        $feedbacks = $this->DAO->get_feedback_list();
        foreach($feedbacks as $key => $data){
            $time = $this->get_days($data['create_time']);
            if(date('Y-m-d H:i:s',time()) >$time[2]){
                $this->DAO->update_feedback_reply($data['id']);
            }
        }
        die("更新成功");
    }

    public function new_list(){
        $params = $this->get_params($_POST,$_GET);
        $feedbacks_name = $this->DAO->get_feedback_name();
        $feedbacks = $this->DAO->get_list($this->page,$params);
        foreach($feedbacks as $key => $data){
            $time = $this->get_days($data['create_time']);
            if(date('Y-m-d H:i:s',time()) >$time[2]){
                $this->DAO->update_feedback_reply($data['id']);
                $feedbacks[$key]['question_status'] = 1;
            }
        }
        $page = $this->pageshow($this->page, "feedback.php?act=new_list&");
        $this->assign("params",$params);
        $this->assign("feedbacks",$feedbacks_name);
        $this->assign("datalist", $feedbacks);
        $this->assign("page_bar", $page->show());
        $this->display("feedback_new_list.html");
    }

    public function new_edit($id){
        $info = $this->DAO->get_feedback($id);
        if($info['img_path']) {
            $info['img_path'] = explode("|",$info['img_path']);
        }
        $time = $this->get_days($info['create_time']);
        if(date('Y-m-d H:i:s',time()) >$time[2]){
            $this->DAO->update_feedback_reply($id);
            $info['question_status'] = 1;
        }
        $reply_list = $this->DAO->get_reply_list($id);
        $this->V->assign('reply_list',$reply_list);
        $this->V->assign("info", $info);
        $this->V->display("feedback_new_edit.html");
    }

    public function do_new_edit($id){
        $info = $this->DAO->get_feedback($id);
        if($info['question_status'] == 1){
            $this->error_msg("问题已关闭，不能回复信息");
        }
        if (!$_POST['feedback']){
            $this->error_msg("请填写回复信息");
        }else{
            $data=array(
                'desc'=>$_POST['feedback'],
                'operator_id'=>$_SESSION['usr_id'],
                'add_time'=>date('Y-m-d H:i:s',time())
            );
            $this->DAO->insert_reply_feedback($data,$id);
            $this->succeed_msg();
        }
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
}
