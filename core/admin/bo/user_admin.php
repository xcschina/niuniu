<?php
COMMON('adminBaseCore','pageCore','uploadHelper');
DAO('user_dao');

class user_admin extends adminBaseCore{
    public $DAO;
    public $mobile;

    public function __construct(){
        parent::__construct();
        $this->DAO = new user_dao();
        $this->mobile = array('13003825038','18120795712','18649800250','15260949551','13799936559','18105022586');
    }

    public function list_view(){
        if($_SESSION['usr_id']!='84'){
            die("你没有该权限");
        }
        if($_POST){
            $_SESSION['log_list'] = $params = $_POST;
        }elseif(!$_GET['page']){
            unset($_SESSION['log_list']);
        }else{
            $params = $_SESSION['log_list'];
        }
        $log_list = $this->DAO->get_log_list($this->page,$params);
        $page = $this->pageshow($this->page,"user.php?act=list&");
        $this->assign("page_bar",$page->show());
        $this->assign("params",$params);
        $this->assign("log_list",$log_list);
        $this->display("operator_log_list.html");
    }

    public function change_pwd(){
        if($_SESSION['usr_id']!='84'){
            die("你没有该权限");
        }
        $this->page_hash();
        $this->display("operator_change.html");
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
    public function reg_mobile(){
        $data = array('error'=>1,'msg'=>'');
        if($_POST['pagehash'] != $_SESSION['page-hash']){
            $data['msg'] = "hash错误，请刷新后重新登录！";
            die(json_encode($data));
        }
        $mobile = $_POST['mobile'];
        if(!$mobile){
            $data['msg'] = "用户手机号码不能为空！";
            die(json_encode($data));
        }
        $res = $this->DAO->get_user_mobile($mobile);
        if(!$res){
            $data['msg'] = '未匹配到该用户！';
            die(json_encode($data));
        }
        $data['error'] = 0;
        $data['msg'] = '查询成功！';
        $data['infos'] = $res;
        die(json_encode($data));
    }

    public function do_change_pwd(){
        if($_SESSION['usr_id']!='84'){
            $this->error_msg("你没有操作的权限，请联系管理员");
        }
        if($_POST['pagehash'] != $_SESSION['page-hash']){
            $this->error_msg("hash错误，请刷新后重新登录。");
        }
        $user_id = $_POST['user_id_h'];
        $password = $_POST['password'];
        $again_password = $_POST['again_password'];
        if($_POST['type'] == '1' && !$user_id){
            $this->error_msg("请先验证ID！");
        }
        if($_POST['type'] == '2' && !$_POST['mobile']){
            $this->error_msg("请先验证手机！");
        }
        $info = $this->DAO->get_user_info($user_id);
        if(!$info){
            $this->error_msg("未匹配到该用户！");
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
        $_POST['old_password'] = $info['password'];
        $this->DAO->update_user_pwd(md5($password),$user_id);
        $this->DAO->insert_operation_log($_SESSION['usr_id'],$_POST,$user_id,$this->client_ip());
        $this->succeed_msg();
    }

    public function clear_mobile(){
        if($_SESSION['usr_id']!='84'){
            die("你没有该权限");
        }
        $this->page_hash();
        $this->display("operator_clear_mobile.html");
    }
    public function qa_clear(){
        if($_SESSION['usr_id']!='84'){
            die("你没有该权限");
        }
        $this->page_hash();
        $this->assign("mobile_list",$this->mobile);
        $this->display("operator_qa_clear.html");
    }

    public function do_clear_mobile(){
        $params = $_POST;
        if($params['pagehash'] != $_SESSION['page-hash']){
            $this->error_msg("hash错误，请刷新后重新登录。");
        }
        if($params['type'] == '1' && !$params['user_id']){
            $this->error_msg("请先验证ID！");
        }
        if($params['type'] == '2' && !$params['mobile']){
            $this->error_msg("请先验证手机！");
        }
        $info = $this->DAO->get_user_info($params['user_id']);
        if(!$info){
            $this->error_msg("未匹配到该用户！");
        }
        if(!$info['mobile']){
            $this->error_msg("用户电话号码已为空,无需再次清空");
        }
        $this->DAO->update_user_mobile($params['user_id']);
        $this->DAO->insert_operation($params,$_SESSION['usr_id'],$this->client_ip());
        $this->succeed_msg();
    }

    public function do_qa_clear(){
        $params = $_POST;
        if($params['pagehash'] != $_SESSION['page-hash']){
            $this->error_msg("hash错误，请刷新后重新登录。");
        }
        if(!$params['mobile']){
            $this->error_msg("请先验证手机！");
        }
        if(!$params['user_id']){
            $this->error_msg("缺少必要！");
        }
        if(!in_array($params['mobile'],$this->mobile)){
            $this->error_msg("该手机号码不在名单内！");
        }
        $info = $this->DAO->get_user_info($params['user_id']);
        if(!$info){
            $this->error_msg("未匹配到该用户！");
        }
        if(!$info['mobile']){
            $this->error_msg("用户电话号码已为空,无需再次清空");
        }
        $this->DAO->update_user_mobile($params['user_id']);
        $this->DAO->insert_operation($params,$_SESSION['usr_id'],$this->client_ip());
        $this->succeed_msg();
    }

    public function suspend(){
        if($_POST){
            $_SESSION['user_suspend_list'] = $params = $_POST;
        }elseif(!$_GET['page']){
            unset($_SESSION['user_suspend_list']);
            $params = array();
        }else{
            $params = $_SESSION['user_suspend_list'];
        }
        $user_list = $this->DAO->get_user_suspend_list($this->page,$params);
        $page = $this->pageshow($this->page,"user.php?act=suspend&");
        $this->assign("user_id",$params['user_id']);
        $this->assign("page_bar",$page->show());
        $this->assign("dataList",$user_list);
        $this->display("user_suspend_list_view.html");
    }

    public function do_suspend(){
//        if($_SESSION['usr_id']!='84'){
//            die("你没有该权限");
//        }
        $this->page_hash();
        $this->display("user_do_suspend_view.html");
    }

    public function reg_suspend(){
//        if($_SESSION['usr_id']!='84'){
//            $this->error_msg("你没有该权限");
//        }
        $params = $_REQUEST;
        if($params['pagehash'] != $_SESSION['page-hash']){
            $this->error_msg("hash错误，请刷新后重新登录。");
        }
        if ($params['type']=='1'){
            if (!$params['user_id']){
                $this->error_msg("请先验证ID！");
            }
            $user_res = $this->DAO->get_user_suspend_by_id($params['user_id']);
            if ($user_res['statu']==2){
                $this->error_msg("该账号已经停封，请先解封！");
            }
        }elseif($params['type']=='2'){
            if (!$params['mobile']){
                $this->error_msg("请先验证手机！");
            }
            $user_res = $this->DAO->get_user_suspend_by_mobile($params['mobile']);
            if ($user_res['statu']==2){
                $this->error_msg("该账号已经停封，请先解封！");
            }
        }else{
            $this->error_msg("验证出错！");
        }
        if (!$user_res){
            $this->error_msg("不存在该用户！");
        }
        $this->succeed_msg($user_res);
    }

    public function do_suspend_save(){
//        if($_SESSION['usr_id']!='84'){
//            $this->error_msg("你没有该权限");
//        }
        $params = $_POST;
        if($params['pagehash'] != $_SESSION['page-hash']){
            $this->error_msg("hash错误，请刷新后重新登录。");
        }
        if (!$params['password_suspend']){
            $this->error_msg("没有设置新密码！");
        }
        $params['password_suspend'] = md5($params['password_suspend']);
        //处理没有密码
        if (!$params['password_save']){
            $params['password_save'] = '';
        }
        if ($params['mobile_save']){
            $params['mobile_save_new'] = substr_replace($params['mobile_save'],"2",0,1);
        }
        $user_info = $this->DAO->get_user_mobile($params['mobile_save_new']);
        if ($user_info){
            $this->error_msg("该手机号码异常，不能封停！");
        }
        $suspend_res = $this->DAO->get_suspend_by_id($params['u_id_save']);
        if ($suspend_res['statu']==2){
            $this->error_msg("该账号已经停封，请先解封！");
        }elseif ($suspend_res['statu']==1){
            //更新
            $old_res = array("mobile"=>$params['mobile_save_new'],"password"=>$params['password_suspend'],"user_id"=>$params['u_id_save']);
            $res = array("password_old"=>$params['password_save'],"mobile_old"=>$params['mobile_save'],"password_new"=>$params['password_suspend'],"mobile_new"=>$params['mobile_save_new'],"statu"=>2,"user_id"=>$params['u_id_save']);
            $res_log = array("id"=>$suspend_res['id'],"type"=>1);
            $this->DAO->update_user_suspend($old_res);
            $this->DAO->update_suspend_info($res);
            $this->DAO->insert_suspend_info_log($res_log);
        }else{
            //插入
            $old_res = array("mobile"=>$params['mobile_save_new'],"password"=>$params['password_suspend'],"user_id"=>$params['u_id_save']);
            $res = array("password_old"=>$params['password_save'],"mobile_old"=>$params['mobile_save'],"password_new"=>$params['password_suspend'],"mobile_new"=>$params['mobile_save_new'],"statu"=>2,"user_id"=>$params['u_id_save']);
            $this->DAO->update_user_suspend($old_res);
            $suspend_id = $this->DAO->insert_suspend_info($res);
            $res_log = array("id"=>$suspend_id,"type"=>1);
            $this->DAO->insert_suspend_info_log($res_log);
        }
        $usr_info = $this->DAO->get_user_info($params['u_id_save']);
        $this->DAO->del_account_mmc($usr_info['login_name']);
        $this->succeed_msg("停封成功");
    }

    public function relieve_suspend(){
//        if($_SESSION['usr_id']!='84'){
//            $this->error_msg("你没有该权限");
//        }
        $user_id = $_GET['user_id'];
        if (!$user_id){
            $this->error_msg("用户信息错误！");
        }
        $this->assign("user_id",$user_id);
        $this->display("user_relieve_suspend_view.html");
    }

    public function relieve_suspend_save(){
//        if($_SESSION['usr_id']!='84'){
//            $this->error_msg("你没有该权限");
//        }
        $user_id = $_POST['u_id'];
        if (!$user_id){
            $this->error_msg("用户信息错误！");
        }
        $suspend_res = $this->DAO->get_suspend_by_id($user_id);
        if ($suspend_res['statu']==1){
            $this->error_msg("该账号正常，不需要解除！");
        }elseif($suspend_res['statu']==2){
            if ($suspend_res['mobile_old']){
                $user_info = $this->DAO->get_user_mobile($suspend_res['mobile_old']);
                if ($user_info){
                    $this->error_msg("该手机号码已经被注册，不能正常解封！");
                }
            }
            $params = array("mobile"=>$suspend_res['mobile_old'],"password"=>$suspend_res['password_old'],"user_id"=>$user_id);
            $res = array("password_old"=>$suspend_res['password_old'],"mobile_old"=>$suspend_res['mobile_old'],"password_new"=>"","mobile_new"=>"","statu"=>1,"user_id"=>$params['user_id']);
            $res_log = array("id"=>$suspend_res['id'],"type"=>2);
            $this->DAO->update_user_suspend($params);
            $this->DAO->update_suspend_info($res);
            $this->DAO->insert_suspend_info_log($res_log);
        }else{
            $this->error_msg("没有该账号信息！");
        }
        $usr_info = $this->DAO->get_user_info($user_id);
        $this->DAO->del_account_mmc($usr_info['login_name']);
        $this->succeed_msg("解除成功！");
    }

    public function relieve_suspend_bind(){
        $user_id = $_GET['user_id'];
        if (!$user_id){
            $this->error_msg("用户信息错误！");
        }
        $this->assign("user_id",$user_id);
        $this->display("user_relieve_suspend_bind_view.html");
    }

    public function relieve_suspend_bind_save(){
        $user_id = $_POST['u_id'];
        $mobile_bind = $_POST['mobile_bind'];
        if (!$user_id){
            $this->error_msg("用户信息错误！");
        }
        if (!$mobile_bind){
            $this->error_msg("手机不能为空！");
        }
        $suspend_res = $this->DAO->get_suspend_by_id($user_id);
        if ($suspend_res['statu']==1){
            $this->error_msg("该账号正常，不需要解除！");
        }elseif($suspend_res['statu']==2){
            if ($suspend_res['mobile_old']){
                $user_info = $this->DAO->get_user_mobile($mobile_bind);
                if ($user_info){
                    $this->error_msg("该手机号码已经被注册，不能正常解封！");
                }
            }
            $params = array("mobile"=>$mobile_bind,"password"=>$suspend_res['password_old'],"user_id"=>$user_id);
            $res = array("password_old"=>$suspend_res['password_old'],"mobile_old"=>$mobile_bind,"password_new"=>"","mobile_new"=>"","statu"=>1,"user_id"=>$params['user_id']);
            $res_log = array("id"=>$suspend_res['id'],"type"=>3);
            $this->DAO->update_user_suspend($params);
            $this->DAO->update_suspend_info($res);
            $this->DAO->insert_suspend_info_log($res_log);
        }else{
            $this->error_msg("没有该账号信息！");
        }
        $this->succeed_msg("解除并换绑成功！");
    }

    public function search_user_info(){
        $user_id = $_GET['user_id'];
        $usr_info = $this->DAO->get_user_info($user_id);
        $account_mmc = $this->DAO->get_account_mmc($usr_info);
        print_r('account_mmc');
        var_dump($account_mmc);
    }

}