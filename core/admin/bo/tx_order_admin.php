<?php
COMMON('adminBaseCore','pageCore','uploadHelper');
DAO('tx_order_dao');
class tx_order_admin extends adminBaseCore{
    public $DAO;

    public function __construct(){
        parent::__construct();
        $this->DAO = new tx_order_dao();
    }

    public function list_view(){
        $params = $this->get_params($_POST,$_GET);
        $order_list = $this->DAO->get_tx_order_list($params,$this->page);
        foreach($order_list as $key=>$data){
            $info = $this->DAO->get_super_order_info($data['niuorderid']);
            $order_list[$key]['money'] = $info['pay_money'];
        }
        $game_list = $this->DAO->get_super_list();
        $page = $this->pageshow($this->page, "tx_order.php?act=list&");
        $this->assign("page_bar", $page->show());
        $this->assign("params",$params);
        $this->assign("order_list",$order_list);
        $this->assign("game_list",$game_list);
        $this->display("tx_order_list.html");
    }

    public function edit_view($id){
        $this->page_hash();
        $this->assign("id",$id);
        $this->display("tx_order_edit.html");
    }

    public function do_edit(){
        $params = $_POST;
        if($params['pagehash'] != $_SESSION['page-hash']){
            $this->error_msg("参数异常!  001");
        }
        $info = $this->DAO->get_tx_order_info($params['id']);
        if(!$info){
            $this->error_msg("查无此需要补发的订单");
        }
        $data['order_id'] = $params['id'];
        $data['operation_id'] = $_SESSION['usr_id'];
        $data['desc'] = "补发订单";
        $this->err_log(var_export($data,1),"tx_order_operation_log");
        $this->DAO->update_tx_order($params['id']);
        $this->succeed_msg();
    }
}