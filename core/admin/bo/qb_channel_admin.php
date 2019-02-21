<?php
COMMON('adminBaseCore', 'pageCore', 'uploadHelper');
DAO('qb_channel_dao');

class qb_channel_admin extends adminBaseCore{
    public $DAO;
    public $channel;
    public $vip;
    public $exit_depot;

    public function __construct(){
        parent::__construct();
        $this->DAO = new qb_channel_dao();
    }

    public function add_view(){
        $this->display('qb/qb_channel_add.html');
    }

    public function do_add(){
        $params   = $_POST;
        if(!$_POST['channel_name']){
            $this->error_msg("缺少必要参数");
        }
        $this->DAO->insert_channel($params);
        $this->succeed_msg("添加成功");
    }


    public function list_view(){
        $params   = $this->get_params($_POST, $_GET);
        $dataList = $this->DAO->get_list($this->page, $params);
        $page     = $this->pageshow($this->page, "qb_channel.php?act=list&");
        $this->assign("page_bar", $page->show());
        $this->assign("datalist", $dataList);
        $this->assign("params", $params);
        $this->display("qb/qb_channel_list.html");
    }

    public function edit($id){
        $info = $this->DAO->get_qb_channel_info($id);
        $this->assign("info", $info);
        $this->display("qb/qb_channel_edit.html");
    }

    public function do_edit($id){
        if(!$_POST['channel_name'] ||!$_POST['type']){
            $this->error_msg("缺少必要参数");
        }
        $this->DAO->update_qb_channel($_POST,$id);
        $this->succeed_msg();
    }

}
