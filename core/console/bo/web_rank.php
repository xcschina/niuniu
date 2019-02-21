<?php
COMMON('baseCore');
DAO('web_rank_dao');

class web_rank extends baseCore{

    public $DAO;
    public $id;
    public $game_type;
    public $game_status;

    public function __construct(){
        parent::__construct();
        $this->DAO = new web_rank_dao();
        $this->game_type = array(
            1 => '动作',
            2 => '角色',
            3 => '射击',
            4 => '策略',
            5 => '即时',
            6 => '回合',
            7 => '休闲',
            8 => '冒险',
            9 => '模拟',
            10 => '竞技',
            11 => '卡牌',
            12 => '体育',
            13 => '格斗',
            14 => 'MOBA'

        );
        $this->game_status = array(
            1 => '公测',
            2 => '不删档内测',
            3 => '不删档测试',
            4 => '不删档封测',
            5 => '不删档测试',
            6 => '删档封测',
            6 => '删档内测',
        );
    }

    public function hot_rank_view() {
        $list = $this->DAO->get_hot_rank_list();
        $this->assign("game_status",$this->game_status);
        $this->assign("game_type",$this->game_type);
        $this->assign("dataList",$list);
        $this->display("hot_rank_list.html");
    }

    public function hot_edit($id) {
        $info = $this->DAO->get_hot_rank($id);
        $game_list = $this->DAO->get_game_list();
        $this->assign("info",$info);
        $this->assign("game_list",$game_list);
        $this->assign("id",$id);
        $this->assign("game_status",$this->game_status);
        $this->assign("game_type",$this->game_type);
        $this->display("hot_rank_edit.html");
    }

    public function new_game_rank_edit($id) {
        $info = $this->DAO->get_new_rank($id);
        $game_list = $this->DAO->get_game_list();
        $this->assign("info",$info);
        $this->assign("game_list",$game_list);
        $this->assign("id",$id);
        $this->assign("game_type",$this->game_type);
        $this->display("new_game_rank_edit.html");
    }

    public function hot_add() {
        $game_list = $this->DAO->get_game_list();
        $this->assign("game_list",$game_list);
        $this->assign("game_status",$this->game_status);
        $this->assign("game_type",$this->game_type);
        $this->display("hot_rank_add.html");
    }

    public function new_game_rank_add() {
        $game_list = $this->DAO->get_game_list();
        $this->assign("game_list",$game_list);
        $this->assign("game_type",$this->game_type);
        $this->display("new_game_rank_add.html");
    }

    public function hot_update($id) {
        $params = $_POST;
        if(!$params['game_id']){
            die(json_encode($this->error_msg("请选择游戏ID")));
        }
        if(!$params['type']){
            die(json_encode($this->error_msg("请选择分类")));
        }
        if(!$params['status']){
            die(json_encode($this->error_msg("请选择状态")));
        }
        if(!$params['order_num']){
            die(json_encode($this->error_msg("请输入订单数")));
        }
        $this->DAO->hot_update($params,$id);
        echo json_encode($this->succeed_msg("编辑成功","hot_rank"));
    }

    public function new_game_rank_update($id) {
        $params = $_POST;
        if(!$params['game_id']){
            die(json_encode($this->error_msg("请选择游戏ID")));
        }
        if(!$params['type']){
            die(json_encode($this->error_msg("请选择分类")));
        }
        if(!$params['down_url']){
            die(json_encode($this->error_msg("请输入下载地址")));
        }
        if(!$params['hot']){
            die(json_encode($this->error_msg("请输入热度")));
        }
        $this->DAO->new_update($params,$id);
        echo json_encode($this->succeed_msg("编辑成功","new_rank"));
    }

    public function hot_save() {
        $params = $_POST;
        if(!$params['game_id']){
            die(json_encode($this->error_msg("请选择游戏ID")));
        }
        if(!$params['type']){
            die(json_encode($this->error_msg("请选择分类")));
        }
        if(!$params['status']){
            die(json_encode($this->error_msg("请选择状态")));
        }
        if(!$params['order_num']){
            die(json_encode($this->error_msg("请输入订单数")));
        }
        $this->DAO->hot_insert($params);
        echo json_encode($this->succeed_msg("添加成功","hot_rank"));
    }

    public function new_game_rank_save() {
        $params = $_POST;
        if(!$params['game_id']){
            die(json_encode($this->error_msg("请选择游戏ID")));
        }
        if(!$params['type']){
            die(json_encode($this->error_msg("请选择分类")));
        }
        if(!$params['down_url']){
            die(json_encode($this->error_msg("请输入下载地址")));
        }
        if(!$params['hot']){
            die(json_encode($this->error_msg("请输入热度")));
        }
        $this->DAO->new_insert($params);
        echo json_encode($this->succeed_msg("添加成功","new_rank"));
    }

    public function new_rank_view() {
        $list = $this->DAO->get_new_rank_list();
        $this->assign("game_type",$this->game_type);
        $this->assign("dataList",$list);
        $this->display("new_game_rank_list.html");
    }
}
?>