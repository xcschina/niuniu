<?php
COMMON('adminBaseCore','pageCore');
DAO('ry_dao');

class ry_admin extends adminBaseCore{

    public $DAO;
    public $COMMON;

    public function __construct(){
        parent::__construct();
        $this->DAO = new ry_dao();
    }

    public function ry_list_view(){
        $params = $_POST;
        $ry_list = $this->DAO->get_ry_list($this->page,$params);
        $page = $this->pageshow($this->page, "ry_info.php?act=list&");
        $this->assign("page_bar", $page->show());
        $this->assign("datalist",$ry_list);
        $this->display("ry_list.html");
    }

    public function add_ry_view(){
        $app_list = $this->DAO->get_all_app();
        $this->assign("app_list", $app_list);
        $this->display("ry_add_view.html");
    }

    public function ry_do_add(){
        if(!$_POST || empty($_POST)){
            $this->error_msg("缺少必填项");
        }
        if(!$_POST['app_id'] || !$_POST['ry_appid'] || !$_POST['channel']|| !$_POST['game_name'] ){
            $this->error_msg("缺少必填项");
        }

        $id = $this->DAO->insert_ry_info($_POST);
        if(!$id){
            $this->error_msg("保存失败,请刷新后重新操作");
        }
        $this->succeed_msg();
    }

    public function edit_ry_view($id){
        $ry_info = $this->DAO->get_ry_info($id);
        $app_list = $this->DAO->get_all_app();
        $this->assign("app_list", $app_list);
        $this->assign("info", $ry_info);
        $this->display("ry_edit_view.html");
    }


    public function ry_do_edit($id){
        if(!$_POST || empty($_POST)){
            $this->error_msg("缺少必填项");
        }

        if(!$_POST['app_id'] || !$_POST['ry_appid'] || !$_POST['channel']|| !$_POST['game_name'] ){
            $this->error_msg("缺少必填项");
        }
        $this->DAO->update_ry_info($_POST, $id);
        $this->succeed_msg();
    }

    public function ext_view(){
        $params = $_POST;
        $data = $this->DAO->get_ry_ext_list($this->page,$params);
        $page = $this->pageshow($this->page, "ry_info.php?act=ext_view&");
        $this->assign("page_bar", $page->show());
        $this->assign("datalist",$data);
        $this->display("ry_ext_list.html");
    }

    public function ext_add_view(){
        $this->display("ry_ext_add_view.html");
    }

    public function ext_do_add(){
        if(!$_POST || empty($_POST)){
            $this->error_msg("缺少必填项!");
        }
        if(!$_POST['channel_name'] || !$_POST['activity_name'] || !$_POST['apple_id']|| !$_POST['act_id']){
            $this->error_msg("缺少必填项!!");
        }
        $ext_info = $this->DAO->get_ry_ext_act_id($_POST['act_id']);
        if($ext_info){
            $this->error_msg("该活动ID已经添加过了!!");
        }
        $id = $this->DAO->insert_ext_ry_info($_POST);
        if(!$id){
            $this->error_msg("保存失败,请刷新后重新操作");
        }
        $this->succeed_msg();
    }

    public function ext_edit_view($id){
        $info = $this->DAO->get_ry_ext_info($id);
        $this->assign("data", $info);
        $this->display("ry_ext_edit_view.html");
    }


    public function ext_do_edit(){
        if(!$_POST || empty($_POST)){
            $this->error_msg("缺少必填项");
        }

        if(!$_POST['channel_name'] || !$_POST['activity_name'] || !$_POST['apple_id']|| !$_POST['act_id']||!$_POST['id']){
            $this->error_msg("缺少必填项!!");
        }
        $id = $_POST['id'];
        $ext_info = $this->DAO->get_ry_ext_act_id($_POST['act_id'],$id);
        if($ext_info){
            $this->error_msg("该活动ID已经添加过了!!");
        }
        $this->DAO->update_ry_ext_info($_POST, $id);
        $this->succeed_msg();
    }
}