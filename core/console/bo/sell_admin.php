<?php
COMMON('baseCore');
DAO('sell_dao','common_dao');

class sell_admin extends baseCore{

    public $DAO;

    public function __construct(){
        parent::__construct();
        $this->DAO = new sell_dao();
    }

    public function sell_list(){
        $params=$_POST;
        $game_list=$this->DAO->get_game_list();
        $dataList=$this->DAO->get_sell_list($params,$this->pageCurrent);
        $this->pageInfo($this->pageCurrent);
        $this->assign("game_list",$game_list);
        $this->assign("dataList", $dataList);
        $this->assign("params",$params);
        $this->display("sell_list.html");
    }

    public function goods_detail($id){
        $extra_info=$this->DAO->get_extra_info($id);
        $products_info=$this->DAO->get_products_info($id);
        $products_img=$this->DAO->get_products_img($id);
        $this->assign("products_info",$products_info);
        $this->assign("extra_info",$extra_info);
        $this->assign("products_img",$products_img);
        $this->display("goods_detail_view.html");
    }
    public function review_logs($id){
        $review_logs=$this->DAO->get_review_logs($id);
        $this->assign("info",$review_logs);
        $this->display("review_logs_view.html");
    }

    public function del_view($id){
        $this->assign("id",$id);
        $this->display("sell_del_view.html");
    }
    //审核不通过
    public function do_del($id){
        if (!$_POST['delete_reason']) {
            echo json_encode($this->error_msg("请填写拒绝理由"));
            exit();
        }
        $this->DAO->update_is_pus($id, 3);
        $products_info = $this->DAO->get_products_info($id);
        $operation_info = array(
            'status' => 2,
            'operation_id' => $_SESSION['usr_id'],
            'desc' => $_POST['delete_reason'],
            'end_time' => time(),
        );
        $this->DAO->audit_log($products_info, $operation_info);
        echo json_encode($this->succeed_msg("操作成功，审核不通过。","sells_list"));
    }
    //审核通过
    public function do_sure($id){
        $this->DAO->update_is_pus($id, 1);
        $products_info = $this->DAO->get_products_info($id);
        $operation_info = array(
            'status' => 1,
            'operation_id' => $_SESSION['usr_id'],
            'desc' => "",
            'end_time' => time(),
        );
        $this->DAO->audit_log($products_info, $operation_info);
        echo json_encode($this->unclose_succeed_msg("审核通过","sells_list"));
    }




}