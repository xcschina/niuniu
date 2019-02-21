<?php
COMMON('dao');
class partner78871_admin_dao extends Dao {

    public function __construct() {
        parent::__construct();
    }

    public function list_data(){
        $this->sql = "select a.*,b.game_name as sys_name from 7881_games as a left join game as b on a.sys_game_id=b.id order by a.sys_game_id asc";
        $this->doResultList();
        return $this->result;
    }

    public function check_game_by_game_id($game_id){
        $this->sql = "select * from 7881_games where game_id=?";
        $this->params = array($game_id);
        $this->doResult();
        return $this->result;
    }

    public function check_game_group($game_id, $group_id){
        $this->sql = "select * from 7881_game_groups where game_id=? and group_id=?";
        $this->params = array($game_id, $group_id);
        $this->doResult();
        return $this->result;
    }

    public function insert_game($game_id, $game_name){
        $this->sql = "insert into 7881_games(game_name, game_id)values(?,?)";
        $this->params = array($game_name, $game_id);
        $this->doInsert();
    }

    public function isnert_group($game_id, $group_id, $group_name){
        $this->sql = "insert into 7881_game_groups(game_id, group_id, group_name)values(?,?,?)";
        $this->params = array($game_id, $group_id, $group_name);
        $this->doInsert();
    }

    public function get_game_info($id){
        $this->sql = "select * from 7881_games where id=?";
        $this->params = array($id);
        $this->doResult();
        return $this->result;
    }

    public function get_sys_game_info($id){
        $this->sql = "select * from game order by id desc";
        $this->doResultList();
        return $this->result;
    }

    public function update_game_bind($id, $sys_game_id){
        $this->sql = "update 7881_games set sys_game_id=? where id=?";
        $this->params = array($sys_game_id, $id);
        $this->doExecute();
        $this->sql = "update game set iap_game_id=? where id=?";
        $this->params = array($id, $sys_game_id);
        $this->doExecute();
        $this->sql = "update products set game_id=? where coop_game_id=?";
        $this->params = array($sys_game_id, $id);
        $this->doExecute();
    }
}