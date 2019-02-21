<?php
COMMON('baseCore','uploadHelper','pageCore');
DAO('my_dao');

class my_web extends baseCore{

    public $DAO;
    public $COMMON;

    public function __construct(){
        parent::__construct();
        $this->DAO = new my_dao();

        if(isset($_SERVER['HTTP_USER_AGENT1'])){
            $header = base64_decode(substr($_SERVER['HTTP_USER_AGENT1'],1));
            $header = explode("&",$header);
            foreach($header as $k=>$param){
                $param = explode("=",$param);
                if($param[0] == 'user_id'){
                    $this->user_id = $param[1];
                }
            }
        }
    }

    public function my_order(){
        if($this->user_id){
            $_SESSION['user_id']=$this->user_id;
        }
        $status='';
        if($_POST['status']||$_POST['status']=='0'){
            $status = $_POST['status'];
        }
        $order_list = $this->DAO->get_my_order($_SESSION['user_id'],$status,$this->page);
        $this->assign("status",$status);
        $this->assign("user_id",$_SESSION['user_id']);
        $this->assign("order_list",$order_list);
        $this->display("order_view.html");
    }

    public function more_order(){
        $result= array('error'=>'1','msg'=>'网络请求错误');
        $param = $_POST;
        $order_list = $this->DAO->get_my_order($param['user_id'],$param['status'],$this->page);
        if($order_list){
            $result['error']='0';
            $result['msg']='查询完成';
            $result['data']=$order_list;
            $result['count']=count($order_list);
        }else{
            $result['error']='1';
            $result['msg']='没有更多数据';
        }
        die(json_encode($result));
    }

    public function my_gift(){
        if($this->user_id){
            $_SESSION['user_id']=$this->user_id;
        }
        $gift_list=$this->DAO->get_my_gifts( $_SESSION['user_id'],$this->page);
        $this->assign("gift_list",$gift_list);
        $this->assign("user_id",$_SESSION['user_id']);
        $this->display("my_gift.html");
    }
    public function more_gift(){
        $result= array('error'=>'1','msg'=>'网络请求错误');
        $param = $_POST;
        $gift_list = $this->DAO->get_my_gifts($param['user_id'],$this->page);
        if($gift_list){
            foreach($gift_list as $key=>$data){
                $gift_list[$key]['buy_time']=date('Y/m/d',$data['buy_time']+2678400);

            }
        }

        if($gift_list){
            $result['error']='0';
            $result['msg']='查询完成';
            $result['data']=$gift_list;
            $result['count']=count($gift_list);
        }else{
            $result['error']='1';
            $result['msg']='没有更多数据';
        }
        die(json_encode($result));
    }

}