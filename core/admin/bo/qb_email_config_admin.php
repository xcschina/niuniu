<?php
COMMON('adminBaseCore', 'pageCore', 'uploadHelper', 'QQMailer');
DAO('qb_channel_dao');

class qb_email_config_admin extends adminBaseCore{
    public $DAO;
    public $type;


    public function __construct(){
        parent::__construct();
        $this->DAO  = new qb_email_config_dao();
        $this->type = array(
            '1' => 'qb渠道',
            '2' => 'vip客服',
            '3' => '财务对私',
            '4' => '财务对公',
            '5' => '财务主管',
            '6' => '客服',
            '7' => '客服经理',
            '8' => '财务'

        );
    }

    public function add_view(){
        $type_list = $this->type;
        $this->assign("type_list", $type_list);
        $this->display('qb/qb_email_add.html');
    }

    public function do_add(){
        $params = $_POST;
        if(!$_POST['type'] || !$_POST['email']){
            $this->error_msg("缺少必要参数");
        }
        if($_POST['type']==4 && !$_POST['financial_type']){
            $this->error_msg("缺少必要参数");
        }
        $chars = "/^[0-9a-zA-Z]+(?:[\_\.\-][a-z0-9\-]+)*@[a-zA-Z0-9]+(?:[-.][a-zA-Z0-9]+)*\.[a-zA-Z]+$/i";
        if(!preg_match($chars, $_POST['email'])){
            $this->error_msg("收款账号格式出错");
        }
        $this->DAO->insert_email($params);
        $this->succeed_msg("添加成功");
    }


    public function list_view(){
        $params = $_POST;
        $type_list = $this->type;
        $dataList = $this->DAO->get_list($this->page, $params);
        $page     = $this->pageshow($this->page, "qb_email_config.php?act=list&");
        $this->assign("page_bar", $page->show());
        $this->assign("datalist", $dataList);
        $this->assign("type_list", $type_list);
        $this->assign("params", $params);
        $this->display("qb/qb_email_list.html");
    }

    public function edit($id){
        $info        = $this->DAO->get_email_info($id);
        $type_list = $this->type;
        $this->assign("type_list", $type_list);
        $this->assign("info", $info);
        $this->assign("id", $id);
        $this->display("qb/qb_email_edit.html");
    }

    public function do_edit(){
        $params = $this->get_params($_POST, $_GET);
        if(!$_POST['type'] || !$_POST['email']){
            $this->error_msg("缺少必要参数");
        }
        if($_POST['type']==4 && !$_POST['financial_type']){
            $this->error_msg("缺少必要参数");
        }
        $chars = "/^[0-9a-zA-Z]+(?:[\_\.\-][a-z0-9\-]+)*@[a-zA-Z0-9]+(?:[-.][a-zA-Z0-9]+)*\.[a-zA-Z]+$/i";
        if(!preg_match($chars, $_POST['email'])){
            $this->error_msg("收款账号格式出错");
        }
        $this->DAO->update_email_info($params);
        $this->succeed_msg();
    }
    public function delete($id){
        $this->assign("id", $id);
        $this->display("qb/qb_email_delete.html");
    }
    public function do_delete(){
        $params = $this->get_params($_POST, $_GET);
        if(!$_POST['id'] ){
            $this->error_msg("缺少必要参数");
        }
        $this->DAO->update_email_info_delete($params);
        $this->succeed_msg();
    }



}
