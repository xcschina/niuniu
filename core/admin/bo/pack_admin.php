<?php
COMMON('adminBaseCore','pageCore','uploadHelper');
DAO('account_admin_dao','menu_admin_dao','app_admin_dao');

class pack_admin extends adminBaseCore{
    public $DAO;

    public function __construct() {
        parent::__construct();
        $this->DAO = new pack_admin_dao();
    }

    public function do_pack_app($app_id){
        $data = array("error"=>'1','msg'=>'网络请求错误,请重新登录.');
        if(!$_SESSION['usr_id'] || !$app_id){
            $data['msg']='未能获取用户信息,请重新登录.';
            die(json_encode($data));
        }
        $user_info = $this->DAO->get_user_info($_SESSION['usr_id']);
        if($user_info['group_id']=='10' && empty($user_info['user_code'])){
            $data['msg']='未能获取公会代码,请联系管理员.';
            die(json_encode($data));
        }elseif($user_info['group_id']=='1'){
            $user_info['user_code']='nnwl';
        }elseif($user_info['group_id']!='10'){
            $data['msg']='你没有自助打包的权限.请联系管理员';
            die(json_encode($data));
        }
        $pack_record = $this->DAO->get_pack_record($app_id,$_SESSION['usr_id']);
        if($pack_record){
            $data['msg']='该包你已申请过，无需重复打包。';
            die(json_encode($data));
        }
        $app_info = $this->DAO->get_app_info($app_id);
        if(!$app_info || empty($app_info['apk_url'])){
            $data['msg']='该包还未上传.';
            die(json_encode($data));
        }
        $id = $this->DAO->insert_pack_log($user_info['user_code'],$app_id,$app_info['apk_url'],$_SESSION['usr_id'],1);
        if($id){
            $data['msg']='打包申请提交成功,耐心等待。';
            die(json_encode($data));
        }else{
            $data['msg']='打包出错,请联系上级公会。';
            die(json_encode($data));
        }

    }
}