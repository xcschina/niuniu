<?php
COMMON('adminBaseCore');
DAO('integral_dao');

class integral_admin extends adminBaseCore{
    public $DAO;
    public $money;
    public function __construct() {
        parent::__construct();
        $this->DAO = new integral_dao();
        $this->money = array('10','30','50','100','300','500');
    }

    public function list_view(){
        $list = $this->DAO->get_integral_list($this->page);
        $this->assign("list",$list);
        $this->display("integral_list.html");
    }

    public function add_view(){
        $this->assign("money_list",$this->money);
        $this->display('integral_add.html');
    }

    public function do_add(){
        if(!$_POST['money'] || !$_POST['integral']){
            $this->error_msg("缺少必填项");
        }
        $info = $this->DAO->get_money_info($_POST['money']);
        if($info){
            $this->error_msg("该牛币段位积分已存在，无需重新添加");
        }
        $this->DAO->insert_integral($_POST);
        $this->succeed_msg();
    }

    public function edit_view($id){
        $info = $this->DAO->get_integral_info($id);
        $this->assign("info",$info);
        $this->assign("money_list",$this->money);
        $this->display('integral_edit.html');
    }

    public function do_edit(){
        if(!$_POST['money'] || !$_POST['integral']){
            $this->error_msg("缺少必填项");
        }
        $info = $this->DAO->get_info($_POST);
        if($info){
            $this->error_msg("该牛币段位积分已存在，无需重新添加");
        }
        $this->DAO->update_integral($_POST);
        $this->succeed_msg();
    }

    public function del_view($id){
        $this->assign('id',$id);
        $this->display("integral_del.html");
    }

    public function do_del($id){
        $info = $this->DAO->get_integral_info($id);
        if(!$info){
            $this->error_msg('查无此数据');
        }
        $this->DAO->delete_integral($id);
        $this->succeed_msg();
    }
}