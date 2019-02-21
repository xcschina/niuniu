<?php
COMMON('baseCore', 'pageCore','imageCore','uploadHelper','class.phpmailer');
DAO('my_dao','common_dao');
BO('pay_mobile');
class my_mobile  extends baseCore{
    public $DAO;

    public function __construct(){
        ini_set("display_errors","Off");
        parent::__construct();
        $this->DAO=new my_dao();
        $this->user_id=$_SESSION['user_id'];
    }

    //我的订单
    public function my_orders(){
        $params=$_GET;
        if(!$this->user_id){
            $this->redirect("account.php?act=login");
            exit;
        }
        if(!$params['page']){
            $params['page']=$this->page;
        }
        $common=new common_dao();
        $all_games=$common->get_all_games();
        $serv_list="";
        if($params['game_id']){
            $serv_list=$common->get_game_servs($params['game_id']);
        }

        $order_list=$this->DAO->get_my_orders($params,$this->user_id,$params['page']);
        foreach($order_list as $key=>$data){
            $game_info=$common->get_game_info($data['game_id']);
            $get_serv_info=$common->get_serv_info($data['game_id'],$data['serv_id']);
            $order_list[$key]['game_name']=$game_info['game_name'];
            $order_list[$key]['serv_name']=$get_serv_info['serv_name'];
            $order_list[$key]['date']=date("m-d",$data['buy_time']);
            $order_list[$key]['time']=date("H:i",$data['buy_time']);
        }
        if($params['page']==1) {
            $this->assign("params", $params);
            $this->assign("all_games", $all_games);
            $this->assign("serv_list", $serv_list);
            $this->assign("order_list", $order_list);
            $this->display("member/orders.html");
        }else{
            echo json_encode($order_list);
        }
    }

    //Q币订单
    public function qb_orders(){
        $params=$_GET;
        if(!$this->user_id){
            $this->redirect("account.php?act=login");
            exit;
        }
        $params['page']=$this->page;
        $qb_order_list=$this->DAO->get_qb_orders($params,$this->user_id,$params['page']);
        foreach($qb_order_list as $key=>$data){
            $qb_order_list[$key]['date']=date("m-d",$data['buy_time']);
            $qb_order_list[$key]['time']=date("H:i",$data['buy_time']);
        }
        if($params['page']==1) {
            $this->assign("params", $params);
            $this->assign("qb_order_list", $qb_order_list);
            $this->display("member/qb_orders.html");
        }else{
            echo json_encode($qb_order_list);
        }
    }

    //订单详情
    public function order_detail($id){
        if(!isset($this->user_id)){
            $this->redirect("account.php?act=login");
            exit;
        }
        $detail=$this->DAO->get_order_detail($id,$this->user_id);
        $common=new common_dao();
        $game_info=$common->get_game_info($detail['game_id']);
        $detail['game_name']=$game_info['game_name'];
        $channel_info=$common->get_channel_info($detail['game_channel']);
        $detail['serv_name'] = $common->get_serv_name($detail['serv_id']);

        $detail['channel_name']=$channel_info['channel_name'];
        $this->assign("detail",$detail);
        $this->display("member/order_detail.html");
    }

    //Q币订单详情
    public function qb_order_detail($id){
        if(!isset($this->user_id)){
            $this->redirect("account.php?act=login");
            exit;
        }
        $qb_detail=$this->DAO->get_qb_order_detail($id,$this->user_id);
        $this->assign("qb_detail",$qb_detail);
        $this->display("member/qb_order_detail.html");

    }

    public function qb_order_pay($id){
        $this->open_debug();
        $order = $this->DAO->get_qb_order_detail($id, $_SESSION['user_id']);
        if(!$order){
            die("发生错误");
        }
        $pay = new pay_mobile();
        $pay->qb_ali_pay($order);
    }

    //个人取消Q币订单
    public function qb_order_cancel($id){
        $this->open_debug();
        $order = $this->DAO->get_qb_order_detail($id, $this->user_id);
        if(!$order){
            die("发生错误");
        }
        $this->DAO->update_qb_cancel($id, $this->user_id);
        $this->redirect("my.php?act=qb_orders");
    }

    public function order_pay($id){
        $this->open_debug();
        $order = $this->DAO->get_order_detail($id, $_SESSION['user_id']);
        if(!$order){
            die("发生错误");
        }

        $order = (object)$order;
        $pay = new pay_mobile();
        $pay->ali_pay($order);
    }

    //个人取消订单
    public function order_cancel($id){
        $this->open_debug();
        $order = $this->DAO->get_order_detail($id, $this->user_id);
        if(!$order){
            die("发生错误");
        }
        $this->DAO->update_order_cancel($id, $this->user_id, $order);
        $this->redirect("my.php?act=my_orders");
    }

    //我的消息
    public function my_msgs(){
        $params=$_GET;
        if(!isset($this->user_id)){
            $this->redirect("account.php?act=login");
            exit;
        }
        if(!$params['page']){
            $params['page']=$this->page;
        }
        $msg_list=$this->DAO->get_my_msgs($this->user_id,$params['page'],$params);
        foreach($msg_list as $key=>$data){
            $msg_list[$key]['add_time']= $this->fig_time(date("Y-m-d H:i:s",$data['add_time']));
        }
        if($params['page']==1){
            $unread_num=$this->DAO->get_unread_msg_count($this->user_id);
            $this->assign("unread_num",$unread_num);
            $this->assign("is_read",$params['is_read']);
            $this->assign("msg_list",$msg_list);
            $this->display("member/messages.html");
        }else{
            echo json_encode($msg_list);
        }
    }

    public function msg_detail($id){
        ini_set("display_errors","Off");
        if(!isset($this->user_id)){
            $this->redirect("account.php?act=login");
            exit;
        }
        $detail=$this->DAO->get_msgs_detail($id,$this->user_id);
        if($detail){
            $detail['beautiful_time']=$this->fig_time(date("Y-m-d H:i:s",$detail['add_time']));
        }
        $this->DAO->upd_msgs_read($this->user_id,$id);
        $this->assign("detail",$detail);
        $this->display("member/message_detail.html");
    }
    
    // 我的礼包列表
    public function my_gifts(){
        ini_set("display_errors","Off");
        $params=$_GET;
        if(!$this->user_id){
            $this->redirect("account.php?act=login");
            exit;
        }
        if(!$params['page']){
            $params['page']=$this->page;
        }
        $common=new common_dao();
        $all_games=$common->get_all_games();
        $gift_list=$this->DAO->get_my_gifts( $this->user_id,$params['page'],$params);
        if($gift_list){
            foreach ($gift_list as $key=>$data){
                $gift_list[$key]['gift_title'] = $this->DAO->get_gifts_name($data['batch_id']);
            }
        }
        if($params['page']==1) {
            $this->assign("gift_list", $gift_list);
            $this->assign("all_games", $all_games);
            $this->assign("params", $params);
            $this->display("member/gifts.html");
        }else{
            echo json_encode($gift_list);
        }
    }

    public function game_down_url(){
        $url = $_GET['url'];
        $re=$this->is_base64($url);
        if($re){
            $url = base64_decode($url);
        }
        if($this->is_weixin()){
            $this->display("guidance_down.html");
        }else{
            header("Location:".$url);
        }
    }
    public function is_weixin(){
        if (strpos($_SERVER['HTTP_USER_AGENT'],
                'MicroMessenger') !== false ) {
            return true;
        }
        return false;
    }

//判断字符串是否经过编码方法
    public function is_base64($str){
    if($str==base64_encode(base64_decode($str))){
        return true;
    }else{
        return false;
    }
}


}
?>