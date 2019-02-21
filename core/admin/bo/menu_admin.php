<?php
COMMON('adminBaseCore','pageCore','uploadHelper');
DAO('menu_admin_dao');

class menu_admin extends adminBaseCore{
    public $DAO;

    public function __construct() {
        parent::__construct();
        $this->DAO = new menu_admin_dao();
    }

    public function menu_list_view(){
        $top_list = $this->DAO->get_cate_menu(0);
        foreach ($top_list as $k=>$v){
            $sub_list = $this->DAO->get_cate_menu($v['id']);
            $top_list[$k]['sub_list'] = $sub_list;
            foreach ($sub_list as $kk=>$vv){
                $child_list = $this->DAO->get_cate_menu($vv['id']);
                $top_list[$k]['sub_list'][$kk]['child_list'] = $child_list;
            }
        }

        $this->assign("dataList", $top_list);
        $this->display("menu_list.html");
    }

    public function menu_add_view($pid){
        if(!$pid){
            $pmenu = array("id"=>0,"name"=>"顶级菜单");
        }else{
            $pmenu = $this->DAO->get($pid);
        }

        $this->assign("pmenu", $pmenu);
        $this->display("menu_add.html");
    }

    public function menu_edit_view($id){
        $info = $this->DAO->get($id);
        if($info['pid']==0){
            $info['pp_id'] = 0;
        }
        $parents = $this->DAO->get_cate_menu($info['pp_id']);
        if($info['pid']==0){
            $parents[] = array("id"=>0, "name"=>"=顶级菜单=");
        }
        $this->assign("info", $info);
        $this->assign("parents", $parents);
        $this->display("menu_edit.html");
    }

    public function do_menu_edit($id){
        if(!isset($_POST['pid']) || !$_POST['name'] || !isset($_POST['status']) || !isset($_POST['is_del'])){
            $this->error_msg("缺少必填项");
        }
        
        $this->DAO->update_menu($_POST, $id);
        $this->succeed_msg();
    }

    public function do_menu_add(){
        if(!isset($_POST['pid']) || !$_POST['name'] || !isset($_POST['status']) || !isset($_POST['is_del'])){
            $this->error_msg("缺少必填项");
        }

        $this->DAO->insert_menu($_POST);
        $this->succeed_msg();
    }
}