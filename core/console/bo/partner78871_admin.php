<?php
COMMON('baseCore');
DAO('partner78871_admin_dao');

class partner78871_admin extends baseCore{

    public $DAO;
    public $id;

    public function __construct(){
        parent::__construct();
        $this->DAO = new partner78871_admin_dao();
    }

    public function list_view(){
        $list = $this->DAO->list_data();
        $this->assign("list", $list);
        $this->display("7881_list.html");
    }

    public function refresh(){
        $this->open_debug();
        $url = "http://open.1771.com/liebaogamelist_.action?type=mobile";
        $data = $this->request($url);
        $data = str_replace("'",'"', $data);
        $data = json_decode($data);
        foreach($data->msg as $k=>$game){
            $game_id = $game->gameId;
            $game_name = $game->gameName;
            $game_info = $this->DAO->check_game_by_game_id($game_id);
            if(!$game_info){
                $this->DAO->insert_game($game_id, $game_name);
                $url = "http://open.1771.com/liebaogamelist_.action?type=mobile&gameid=".$game_id;
                $data = $this->request($url);
                $data = str_replace("'",'"', $data);
                $data = json_decode($data);
                $groups  = $data->msg[0]->groupList;
                foreach($groups as $kk=>$group){
                    $group_id = $group->groupid;
                    $group_name = $group->groupName;
                    $group_info = $this->DAO->check_game_group($game_id, $group_id);
                    if(!$group_info){
                        $this->DAO->isnert_group($game_id, $group_id, $group_name);
                    }
                }
            }
        }
        $this->redirect("7881.php?act=list");
    }

    public function edit_view($id){
        //$this->open_debug();
        $info = $this->DAO->get_game_info($id);
        $sys_games = $this->DAO->get_sys_game_info($id);

        $this->assign("info", $info);
        $this->assign("sys_games", $sys_games);
        $this->display("7881_edit.html");
    }

    public function do_edit($id){
        $sys_game_id = $_POST['sys_game_id'];
//        if($sys_game_id){
//            $this->DAO->update_game_bind($id, $sys_game_id);
//        }
        $this->DAO->update_game_bind($id, $sys_game_id);
        echo json_encode($this->succeed_msg("编辑成功".$sys_game_id,"p7881"));
    }
}