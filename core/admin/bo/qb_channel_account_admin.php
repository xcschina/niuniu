<?php
COMMON('adminBaseCore', 'pageCore', 'uploadHelper');
DAO('qb_channel_dao');

class qb_channel_account_admin extends adminBaseCore{
    public $DAO;
    public $channel;
    public $vip;
    public $exit_depot;

    public function __construct(){
        parent::__construct();
        $this->DAO = new qb_channel_account_dao();
    }

    public function add_view(){
        $channel_list = $this->DAO->get_qb_channel_list();
        $this->assign("datalist", $channel_list);
        $this->display('qb/qb_channel_account_add.html');
    }

    public function do_add(){
        $params = $_POST;
        if(!$_POST['user_name'] || !$_POST['payment_account'] || !$_POST['type'] || !$_POST['pay_mode']){
            $this->error_msg("缺少必要参数");
        }
        $_POST['payment_account'] = preg_replace('# #','',$_POST['payment_account']);
        $payment_account = $this->DAO->get_qb_channel_account_info_by_account($_POST['payment_account'])['payment_account'];
        if(!empty($payment_account)){
            $this->error_msg("已经添加过账号");
        }
        if($_POST['type'] == 1){
            if(!$_POST['channel_id']){
                $this->error_msg("缺少渠道");
            }
            if(!$_POST['mode']){
                $this->error_msg("缺少转账方式");
            }
            if($_POST['mode'] == 1 && $_POST['credit_money'] == null){
                $this->error_msg("缺少授信金额");
            }

        }
        if($_POST['type'] == 2){
            $_POST['channel_id'] = 0;
            $_POST['mode'] = 0;
        }
        $this->DAO->insert_qb_channel_account($params);
        $this->succeed_msg("添加成功");
    }

    public function list_view(){
        $params   = $_POST;
        $dataList = $this->DAO->get_list($this->page, $params);
        $page     = $this->pageshow($this->page, "qb_channel_account.php?act=list&");
        $this->assign("page_bar", $page->show());
        $this->assign("datalist", $dataList);
        $this->assign("params", $params);
        $this->display("qb/qb_channel_account_list.html");
    }

    public function edit($id){
        $info         = $this->DAO->get_qb_channel_account_info($id);
        $channel_list = $this->DAO->get_qb_channel_list();
        $this->assign("datalist", $channel_list);
        $this->assign("info", $info);
        $this->display("qb/qb_channel_account_edit.html");
    }

    public function do_edit(){
        if(!$_POST['user_name'] || !$_POST['payment_account'] || !$_POST['type']){
            $this->error_msg("缺少必要参数");
        }
        $_POST['payment_account'] = preg_replace('# #','',$_POST['payment_account']);
        $al_payment_account         = $this->DAO->get_qb_channel_account_info($_POST['id'])['payment_account'];
        if($al_payment_account!=$_POST['payment_account']){
            $payment_account = $this->DAO->get_qb_channel_account_info_by_account($_POST['payment_account'])['payment_account'];
        }
        if(!empty($payment_account)){
            $this->error_msg("已经添加过账号");
        }

        if($_POST['type'] == 1){
            if(!$_POST['channel_id']){
                $this->error_msg("缺少渠道");
            }
            if(!$_POST['mode']){
                $this->error_msg("缺少转账方式");
            }
            if($_POST['mode'] == 1 && $_POST['credit_money']==null){
                $this->error_msg("缺少授信金额");
            }

        }
        if($_POST['type'] == 2){
            $_POST['channel_id'] = 0;
            $_POST['mode'] = 0;
        }
        $this->DAO->update_qb_channel_account($_POST, $_POST['id']);
        $this->succeed_msg();
    }

    public function delete($id){
        $this->assign("id", $id);
        $this->display("qb/qb_channel_account_delete.html");
    }
    public function do_delete(){
        $params = $this->get_params($_POST, $_GET);
        if(!$_POST['id'] ){
            $this->error_msg("缺少必要参数");
        }
        $this->DAO->update_qb_channel_account_delete($params);
        $this->succeed_msg();
    }



}
