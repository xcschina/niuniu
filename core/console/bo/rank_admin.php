<?php
COMMON('baseCore','uploadHelper');
DAO('rank_dao','common_dao');

class rank_admin extends baseCore{

    public $DAO;
    public $COMMON;

    public function __construct(){
        parent::__construct();
        $this->DAO = new rank_dao();
        $this->common = new common_dao();
    }

    public function rank_list(){
        $data_list = $this->DAO->rank_gain();
        $this->assign("data_list",$data_list);
        $this->display("rank_list.html");
    }

    public function rank_add_view(){
        $params = $_POST;
        $data = $this->DAO->game_name();
        $this->assign("params",$params);
        $this->assign("data",$data);
        $this->display("rank_add_view.html");
    }

    public function rank_save(){
        $params = $_POST;
        if(!$params['game_id']){
            die(json_encode($this->error_msg("请选择游戏ID")));
        }
        if(!$params['hot']){
            die(json_encode($this->error_msg("请填写热度")));
        }
        $data =  $this->DAO->insert_rank($params);
        if(!$data){
            die(json_encode($this->succeed_msg("保存成功！","rank_list")));
        }else{
            die(json_encode($this->error_msg("保存失败！")));
        }
    }

    public function rank_edit($id){
        $app_gid = $this->DAO->get_rank($id);
        $info=$this->DAO->get_game($app_gid);
        $game_list = $this->DAO->game_name();
        $this->assign("info",$info);
        $this->assign("game_list",$game_list);
        $this->assign("id",$id);
        $this->assign("game_type",$this->game_type);
        $this->display("rank_edit.html");
    }

    public function rank_update(){
        $params = $_POST;
        if(!$params['game_id']){
            die(json_encode($this->error_msg("请选择游戏ID")));
        }
        if(!$params['hot']){
            die(json_encode($this->error_msg("请填写热度")));
        }
        $data = $this->DAO->update_rank($params);
        if(!$data){
            die(json_encode($this->succeed_msg("编辑成功","rank_list")));
        }else{
            die(json_encode($this->error_msg("编辑失败")));
        }
    }

}