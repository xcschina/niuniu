<?php
COMMON('baseCore','uploadHelper');
DAO('game_template_dao','common_dao');

class game_template_admin extends baseCore{

    public $DAO;
    public $COMMON;

    public function __construct(){
        parent::__construct();
        $this->DAO = new game_template_dao();
        $this->common = new common_dao();
    }

    public function template_list(){
        $params = $_POST;
        $template = $this->DAO->template_info($params,$this->pageCurrent);
        $this->pageInfo($this->pageCurrent);
        $this->assign("params",$params);
        $this->assign("template",$template);
        $this->display("game_template_list.html");
    }

    public function add_view(){
        $game_list = $this->DAO->get_game_list();
        $this->assign("game_list",$game_list);
        $this->display("game_template_add.html");
    }

    public function add_template(){
        $params = $_POST;
        if(!$params['game_id']){
            die(json_encode($this->error_msg("请选择关联游戏")));
        }
        $array = get_headers($params['down_url'],1);
        if(!(preg_match('/200/',$array[0]))){
            die(json_encode($this->error_msg("请输入正确的下载地址")));
        }
        if( $_SERVER['HTTP_REFERER'] == "" ){
            header("Location:".$params['down_url']); exit;
        }
        $this->DAO->add_info($params);
        die(json_encode($this->succeed_msg("游戏页面信息添加成功","template_list")));
    }

    public function edit_view($id){
        $info = $this->DAO->get_info($id);
        $game_list = $this->DAO->get_game_list();
        $this->assign("game_list",$game_list);
        $this->assign("info",$info);
        $this->display("game_template_edit.html");
    }

    public function template_edit(){
        $params = $_POST;
        if(!$params['game_id']){
            die(json_encode($this->error_msg("请选择关联游戏")));
        }
        $array = get_headers($params['down_url'],1);
        if(!(preg_match('/200/',$array[0]))){
            die(json_encode($this->error_msg("请输入正确的下载地址")));
        }
        if($_SERVER['HTTP_REFERER'] == "" ){
            header("Location:".$params['down_url']); exit;
        }
        $this->DAO->info_edit($params);
        die(json_encode($this->succeed_msg("游戏页面信息修改成功","template_list")));
    }

    public function del_template($id){
        $info = $this->DAO->get_info($id);
        if(!$info){
            die(json_encode($this->error_msg("未查到游戏页面信息")));
        }else{
            $this->DAO->del_info($id);
            die(json_encode($this->succeed_del("游戏页面信息删除成功","template_list")));
        }
    }





}