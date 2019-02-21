<?php
COMMON('baseCore', 'pageCore','imageCore','uploadHelper','class.phpmailer');
DAO('my_dao','common_dao');
BO('pay_web');
class my_web  extends baseCore{
    public $DAO;
    public $COMDAO;

    public function __construct(){
        parent::__construct();
        $this->DAO = new my_dao();
        $this->user_id = $_SESSION['user_id'];
        $this->COMDAO = new common_dao();
        $notices = $this->COMDAO->get_mod_articles(14);
        $links = $this->COMDAO->get_friendly_links();
        $this->assign("notices", $notices);
        $this->assign("links", $links);
        $buy_type = array(1=>"character", 2=>"recharge", 8=>"appstore", 5=>"coin", 4=>"account", 3=>"topup", 6=>"topup");
        $this->assign("buy_types", $buy_type);
    }

    //我的订单
    public function my_orders(){
        $params=$_GET;
        if(!$this->user_id){
            $this->redirect("account.php?act=login");
            exit;
        }
        $common=new common_dao();
        $all_games=$common->get_all_games();
        $serv_list="";
        if($params['game_id']){
            $serv_list=$common->get_game_servs($params['game_id']);
        }

        $order_list=$this->DAO->get_my_orders($params,$this->user_id,$this->page);
        foreach($order_list as $key=>$data){
            $game_info=$common->get_game_info($data['game_id']);
            $get_serv_info=$common->get_serv_info($data['game_id'],$data['serv_id']);
            $order_list[$key]['game_name']=$game_info['game_name'];
            if(isset($get_serv_info['serv_name']))$order_list[$key]['serv_name']=$get_serv_info['serv_name'];
        }
        $page = $this->pageshow($this->page, "my.php?act=my_orders&game_id=".$params['game_id']."&serv_id=".$params['serv_id']. "&status=".$params['status']."&");
        $result = $this->get_http_host();
        $domain_name = $result['domain_name'];
        $this->assign('domain_name',$domain_name);
        $this->assign("params",$params);
        $this->assign("pageBar", $page->show());
        $this->assign("all_games",$all_games);
        $this->assign("serv_list",$serv_list);
        $this->assign("order_list",$order_list);
        $this->display("account/orders.html");
    }

    //订单详情
    public function order_detail($id){
        if(!isset($this->user_id)){
            $this->redirect("account.php?act=login");
            exit;
        }
        //$this->open_debug();
        $detail=$this->DAO->get_order_detail($id,$this->user_id);
        $game_info=$this->COMDAO->get_game_info($detail['game_id']);
        $detail['game_name']=$game_info['game_name'];
        $channel_info=$this->COMDAO->get_channel_info($detail['game_channel']);
        $detail['channel_name']=$channel_info['channel_name'];
        if($detail['serv_id']==1){
            $detail['serv_name'] = $detail['other_ser'];
        }else{
            $serv_info = $this->COMDAO->get_serv_info_by_id($detail['serv_id']);
            $detail['serv_name']=$serv_info['serv_name'];
        }
        $result = $this->get_http_host();
        $domain_name = $result['domain_name'];
        $this->assign('domain_name',$domain_name);
        $this->assign("detail",$detail);
        $this->display("account/order_detail.html");
    }

    //我的消息
    public function my_msgs(){
        $params=$_GET;
        if(!isset($this->user_id)){
            $this->redirect("account.php?act=login");
            exit;
        }
        $msg_list = $this->DAO->get_my_msgs($this->user_id,$page=$this->page,$params);
        foreach($msg_list as $key=>$data){
            $msg_list[$key]['add_time'] = $this->fig_time(date("Y-m-d H:i:s",$data['add_time']));
        }
        $unread_num = $this->DAO->get_unread_msg_count($this->user_id);
        $isread_num = $this->DAO->get_isread_msg_count($this->user_id);
        $read_all = $unread_num + $isread_num;
        $page = $this->pageshow($this->page, "my.php?act=my_msgs&is_read=".$params['is_read']."&");
        $result = $this->get_http_host();
        $domain_name = $result['domain_name'];
        $this->assign('domain_name',$domain_name);
        $this->assign("pageBar", $page->show());
        $this->assign("is_read", $params["is_read"]);
        $this->assign("unread_num",$unread_num);
        $this->assign("isread_num",$isread_num);
        $this->assign("read_all",$read_all);
        $this->assign("msg_list",$msg_list);
        $this->display("account/messages.html");
    }

    public function msg_detail($id){
        if(!isset($this->user_id)){
            $this->redirect("account.php?act=login");
            exit;
        }
        $detail=$this->DAO->get_msgs_detail($id,$this->user_id);
        if($detail){
            $detail['beautiful_time']=$this->fig_time(date("Y-m-d H:i:s",$detail['add_time']));
        }
        $this->DAO->upd_msgs_read($this->user_id,$id);
        $result = $this->get_http_host();
        $domain_name = $result['domain_name'];
        $this->assign('domain_name',$domain_name);
        $this->assign("detail",$detail);
        $this->display("account/messages_detail.html");
    }

    // 我的礼包列表
    public function my_gifts(){
        $params = $_GET;
        if(!$this->user_id){
            $this->redirect("account.php?act=login");
            exit;
        }
        $common = new common_dao();
        $all_games=$common->get_all_games();
        $gift_list=$this->DAO->get_my_gifts( $this->user_id,$this->page,$params);
        $this->assign("gift_list",$gift_list);
        $this->assign("all_games",$all_games);
        $page = $this->pageshow($this->page, "my.php?act=my_gifts&game_id=".$params['game_id']."&");
        $result = $this->get_http_host();
        $domain_name = $result['domain_name'];
        $this->assign('domain_name',$domain_name);
        $this->assign("params",$params);
        $this->assign("pageBar", $page->show());
        $this->display("account/gifts.html");
    }

    //我的优惠卷
    public function my_coupon(){
        $params = $_GET;
        if(!$this->user_id || !$_SESSION['user_id']){
            $this->redirect("account.php?act=login");
            exit;
        }
        $all_coupon=$this->DAO->get_my_coupon($_SESSION['user_id'],$this->page,$params['coupon_status']);
        $page = $this->pageshow($this->page, "my.php?act=my_coupon&coupon_status=".$params['coupon_status']."&");
        foreach($all_coupon as $k=>$v){
            if($v['end_time'] > time() && empty($v['use_time'])){
                $all_coupon[$k]['gift_state']=1;
            }else if($v['end_time'] < time() && empty($v['use_time'])){
                $all_coupon[$k]['gift_state']=2;
            }else if(!empty($v['use_time'])){
                $all_coupon[$k]['gift_state']=3;
            }
        }
        $result = $this->get_http_host();
        $domain_name = $result['domain_name'];
        $this->assign('domain_name',$domain_name);
        $this->assign("status",$params['coupon_status']);
        $this->assign("data",$all_coupon);
        $this->assign("pageBar", $page->show());
        $this->display("account/coupon.html");
    }

    // 个人订单付款
    public function order_pay($id){
        $this->open_debug();
        $order = $this->DAO->get_order_detail($id, $_SESSION['user_id']);
        if(!$order){
            die("发生错误");
        }
        $order = (object)$order;
        $pay = new pay_web();
        if($order->pay_channel == '5'){
            $pay->wx_pay($order);
        }else{
            $pay->ali_pay($order);
        }
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
}