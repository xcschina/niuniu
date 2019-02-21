<?php
COMMON('baseCore','uploadHelper');
DAO('qb_order_dao','common_dao');

class qb_order_admin extends baseCore{

    public $DAO;
    public $COMMON;

    public function __construct(){
        parent::__construct();
        $this->DAO = new qb_order_dao();
        $this->common = new common_dao();
    }

    public function order_list(){
        $params = $_POST;
        if(!empty($params['time'])){
            $params['buy_time'] = strtotime($params['time']);
        }
        if(!empty($params['time2'])){
            $params['buy_time2'] = strtotime($params['time2']);
        }
        $order = $this->DAO->get_order($params,$this->pageCurrent);
        $this->pageInfo($this->pageCurrent);
        $this->assign("params",$params);
        $this->assign("order",$order);
        $this->display('qb_order_list.html');
    }

    public function order_details($id){
        $details = $this->DAO->order_details($id);
        $this->assign("order_detail",$details);
        $this->display('qb_order_detail.html');
    }

    public function detail_edit(){
        $params = $_POST;
        if(!$params['id'] || !$params['order_status']){
            die(json_encode($this->error_msg("缺少必要参数,请重新登录.")));
        }
        if($params['order_status']==2){
            if(empty($params['qb_order']) || !$params['qb_order']){
                die(json_encode($this->error_msg("Q币订单不能为空.")));
            }
            $order_info = $this->DAO->get_qb_order($params['qb_order']);
            if(!$order_info || empty($order_info)){
                die(json_encode($this->error_msg("未匹配到该充值订单,请确认.")));
            }elseif($order_info['status']!='1'){
                die(json_encode($this->error_msg("qb充值订单状态异常,请确定该订单是否已完成.")));
            }

        }elseif($params['order_status']==6){
            if(empty($params['remarks']) || !$params['remarks']){
                die(json_encode($this->error_msg("请在备注填写退款理由.")));
            }
        }else{
            die(json_encode($this->error_msg("订单类型异常,请联系后台管理员")));
        }
        $this->DAO->update_order($params);
        die(json_encode($this->succeed_msg("数据更新成功","order_list")));
    }

}