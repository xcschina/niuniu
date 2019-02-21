<?php
COMMON('baseCore','uploadHelper');
DAO('message_info_dao');

class message_info_web extends baseCore{

    public $DAO;

    public function __construct(){
        parent::__construct();
        $this->DAO = new message_info_dao();
    }

    public function get_massages_list(){
        $params=$_POST;
        $dataList=$this->DAO->get_massages_list($params,$this->pageCurrent);
        $this->pageInfo($this->pageCurrent);
        $this->assign("dataList", $dataList);
        $this->assign("params",$params);
        $this->display("message_info_list.html");
    }

    public function massages_add_view(){
        $this->display("message_info_add.html");
    }

    public function  do_massages_add(){
        $params=$_POST;
        if(!$params['receiver_ids']){
            echo json_encode($this->error_msg("请输入接收人ID，多个英文逗号隔开"));
            exit();
        }
        if(!$params['title']){
            echo json_encode($this->error_msg("请输入标题"));
            exit();
        }
        if(!$params['content']){
            echo json_encode($this->error_msg("请输入消息内容"));
            exit();
        }
        $receivers = explode(',',$params['receiver_ids']);
        foreach($receivers as $receiver){
            if($receiver && is_numeric($receiver)){
                $params['receiver_id']=$receiver;
                $info=$this->DAO->get_massages_info($params);
                if(!$info){
                    $this->DAO->add_massages_info($params);
                }
            }
        }

        echo json_encode($this->succeed_msg("站内消息发成功","massages_list"));
    }

}
?>