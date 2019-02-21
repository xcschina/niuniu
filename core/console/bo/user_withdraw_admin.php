<?php
COMMON('baseCore', 'uploadHelper');
DAO('user_withdraw_dao');

class user_withdraw_admin extends baseCore{

    public $DAO;


    public function __construct(){
        parent::__construct();
        $this->DAO = new user_withdraw_dao();
    }

    public function user_withdraw_list() {
        $params=$_POST;
        $dataList = $this->DAO->get_user_withdraw_list($params,$this->pageCurrent);
        $this->pageInfo($this->pageCurrent);
        $this->assign("dataList", $dataList);
        $this->assign("params",$params);
        $this->display("user_withdraw_list.html");
    }

    public function upload_view($id){
        $this->assign("id",$id);
        $this->display("user_withdraw_upload.html");
    }



    public function do_upload_view(){
        $params = $_POST;
        $params['img'] = "";
        if($_FILES['special_product_img']['tmp_name']){
            $params['img'] = $this->up_img('special_product_img', WithDraw_ICON);
        }
        $this->DAO->update_user_withdraw_img($params);
        $info = $this->DAO->get_user_withdraw_detail($params['id']);
        $balance_params['user_id'] = $info['user_id'];
        $balance_params['money'] = $info['money'];
        $balance_params['type'] = 3;
        $balance_params['pay_mode'] = 2;
        $this->DAO->add_user_balance_detail($balance_params);//添加明细
        echo json_encode($this->succeed_msg("保存成功！", "user_withdraw_list"));
    }
    public function refuse($id){
        $this->assign("id", $id);
        $this->display("with_draw_refuse_view.html");
    }

    public function do_refuse(){
        $params = $_POST;
        $info = $this->DAO->get_user_withdraw_detail($params['id']);
        $params['status'] = 3;
        $this->DAO->update_user_withdraw_status($params);
        $this->DAO->update_user_balance($info['money'],$info['user_id']);
        echo json_encode($this->succeed_msg("拒绝成功", "user_withdraw_list"));
    }
}
?>