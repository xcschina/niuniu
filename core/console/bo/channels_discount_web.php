<?php
COMMON('baseCore');
DAO('channels_discount_dao');

class channels_discount_web extends baseCore{

    public $DAO;
    public $id;

    public function __construct(){
        parent::__construct();
        $this->DAO = new channels_discount_dao();
    }

    public function get_channels_discount_list(){
        $params=$_POST;
        $game_list= $this->DAO->get_game_list();
        $channels_list=$this->DAO->get_channels_list();
        $dataList=$this->DAO->get_channels_discount_list($params,$this->pageCurrent);

        $this->pageInfo($this->pageCurrent);
        $this->assign("game_list",$game_list);
        $this->assign("dataList", $dataList);
        $this->assign("channels_list",$channels_list);
        $this->assign("params",$params);
        $this->display("channels_discount_list.html");
    }

    public function channels_discount_add_view(){
        $game_list= $this->DAO->get_game_list();
        $channels_list=$this->DAO->get_channels_list();
        $this->assign("game_list",$game_list);
        $this->assign("channels_list",$channels_list);
        $this->display("channels_discount_add.html");
    }

    public function  do_channels_discount_add(){
        $params=$_POST;
        if(!$params['game_id']){
            echo json_encode($this->error_msg("请选择游戏"));
            exit;
        }
        if(!$params['channel_id']){
            echo json_encode($this->error_msg("请选择渠道"));
            exit;
        }
        $info=$this->DAO->get_channels_discount_info($params);
        if($info){
            echo json_encode($this->error_msg("该游戏渠道折扣已设置"));
            exit;
        }
        $params['discount']=floatval($params['discount']);
        $this->DAO->add_channels_discount($params);
        echo json_encode($this->succeed_msg("渠道折扣已添加成功","channels_discount_list"));
    }

    public function channels_discount_edit_view($id){
        $channel_discount=$this->DAO->get_channels_discount_byid($id);
        $game_list= $this->DAO->get_game_list();
        $channels_list=$this->DAO->get_channels_list();
        $this->assign("channel_discount",$channel_discount);
        $this->assign("game_list",$game_list);
        $this->assign("channels_list",$channels_list);
        $this->display("channels_discount_edit.html");
    }

    public function  do_channels_discount_edit(){
        $params=$_POST;
        if(!$params['game_id']){
            echo json_encode($this->error_msg("请选择游戏"));
            exit;
        }
        if(!$params['channel_id']){
            echo json_encode($this->error_msg("请选择渠道"));
            exit;
        }
        $info=$this->DAO->get_channels_discount_info($params);
        if($info && $info['id']!=$params['id']){
            echo json_encode($this->error_msg("该区服已设置"));
            exit;
        }
        $this->DAO->update_channels_discount($params);
        echo json_encode($this->succeed_msg("渠道折扣编辑成功","channels_discount_list"));
    }
}
?>