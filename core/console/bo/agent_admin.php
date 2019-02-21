<?php
COMMON('baseCore');
DAO('agent_admin_dao','common_dao');

class agent_admin extends baseCore{

    public $DAO;
    public $COMMON;
    public $type;

    public function __construct(){
        parent::__construct();
        $this->DAO = new agent_admin_dao();
        $this->common = new common_dao();
    }

    public function list_view(){
        $datas = $this->DAO->get_agents($this->pageCurrent);
        $this->pageInfo($this->pageCurrent);

        $this->assign("datas", $datas);
        $this->display("agent_list.html");
    }

    public function item_view($id){
        $info = $this->DAO->get_agent($id);

        $this->assign("info", $info);
        $this->display("agent_edit.html");
    }

    public function add_view(){
        $this->open_debug();
        $games = $this->common->get_game_list();

        $this->assign("games", $games);
        $this->display("agent_add.html");
    }

    public function do_edit($id){
        $agent = $_POST;
        $this->DAO->update_agent($id, $agent);
        echo json_encode($this->succeed_msg("配置成功","agents"));
    }

    public function do_save(){
        $this->open_debug();
        $agent = $_POST;
        $user = $this->common->get_user_info($agent['user_id']);
        if(!$user){
            die(json_encode($this->error_msg("添加失败，用户不存在","agents")));
        }

        $user_agent = $this->DAO->get_user_agent($agent);
        if($user_agent){
            die(json_encode($this->error_msg("该配置已经存在","agents")));
        }
        $this->DAO->insert_agent($agent);
        die(json_encode($this->succeed_msg("配置成功","agents")));
    }
}