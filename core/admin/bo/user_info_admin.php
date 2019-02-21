<?php
COMMON('adminBaseCore','pageCore','uploadHelper');
DAO('user_info_dao');

class user_info_admin extends adminBaseCore{
    public $DAO;

    public function __construct(){
        parent::__construct();
        $this->DAO = new user_info_dao();
    }

    public function list_view(){
        $params = $this->get_params($_POST,$_GET);
        $groups = $this->DAO->get_user_groups();
        $dataList = $this->DAO->get_user_info_list($params,$this->page);
        $page = $this->pageshow($this->page,"user_info.php?act=list&");
        $this->assign('groups',$groups);
        $this->assign("page_bar", $page->show());
        $this->assign("dataList", $dataList);
        $this->assign("params",$params);
        $this->display("user_info_list.html");
    }

    public function user_info_detail($user_id){
        $groups = $this->DAO->get_user_groups();
        $user = $this->DAO->get_user_info($user_id);
        $user['games'] = $this->DAO->get_user_games($user_id);
        $this->assign('groups',$groups);
        $this->assign("user", $user);
        $this->display("user_info_detail.html");
    }

    public function do_user_info($user_id){
        if(!(int)$user_id){
           $this->error_msg("无效用户");
        }
        $params = $_POST;
        $this->DAO->set_user_group($user_id, $params['user_group']);
        $this->succeed_msg("修改成功");
    }
}