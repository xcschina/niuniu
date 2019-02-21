<?php
COMMON('adminBaseCore','pageCore');
DAO('merchant_admin_dao','game_admin_dao','account_admin_dao');

class merchant_admin extends adminBaseCore{
    public $DAO;

    public function __construct() {
        parent::__construct();
        $this->DAO = new merchant_admin_dao();
    }
    public function consume_log(){
        $params = $_POST;
        if($_SESSION['group_id'] == 14){
            $business_dao = new business_dao();
            $game_list = $business_dao->get_business_app_list($_SESSION['usr_id']);
            if($game_list['game_list']){
                $list = explode(',',$game_list['game_list']);
                foreach($list as $key=>$data){
                    $app_info = $business_dao->get_app_info($data);
                    $app_list[$key]['app_name'] = $app_info['app_name'];
                    $app_list[$key]['app_id'] = $app_info['app_id'];
                }
            }else{
                $app_list = array();
            }
        }else{
            $game_admin_dao = new game_admin_dao();
            $app_list = $game_admin_dao->get_app_all();
        }
        $account_admin_dao = new account_admin_dao();
        if($_SESSION['group_id'] == '1') {
            $merchant_list = $account_admin_dao->merchant_list();
        }elseif ($_SESSION['group_id'] == '14'){
            $merchant_list = array($account_admin_dao->get_user_info($_SESSION['usr_id']));
        }else{
            die("您没有该目录的权限，请联系管理员。");
        }
        $guild_id_list = array();
        foreach ($merchant_list as $value){
            array_push($guild_id_list,$value['id']);
        }
        $params['guild_id_list'] = implode(",",$guild_id_list);
        $data_list = $this->DAO->get_charge_log($this->page,$params);
        $page = $this->pageshow($this->page, "merchant.php?act=charge_log&");
        $this->assign("merchant_list",$merchant_list);
        $this->assign("app_list",$app_list);
        $this->assign("datalist", $data_list);
        $this->assign("params",$params);
        $this->assign("page_bar", $page->show());
        $this->display("chamber/merchant_log_view.html");
    }
    public function consume_detail($id){
        $this->display("merchant_log_detail_view.html");
    }
}