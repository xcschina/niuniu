<?php
COMMON('dao');
class web_rank_dao extends Dao {

    public function __construct() {
        parent::__construct();
        $this->mmc = new Memcache();
        $this->mmc->connect(MMCHOST, MMCPORT);
    }

    public function get_hot_rank_list(){
        $this->sql = "select * from hot_rank_tb order by order_num desc limit 10";
        $this->params = array();
        $this->doResultList();
        return $this->result;
    }

    public function get_new_rank_list(){
        $this->sql = "select * from new_game_hot_rank order by hot desc limit 10";
        $this->params = array();
        $this->doResultList();
        return $this->result;
    }

    public function get_hot_rank($id){
        $this->sql = "select * from hot_rank_tb WHERE id= ?";
        $this->params = array($id);
        $this->doResult();
        return $this->result;
    }

    public function get_new_rank($id){
        $this->sql = "select * from new_game_hot_rank WHERE id= ?";
        $this->params = array($id);
        $this->doResult();
        return $this->result;
    }

    public function hot_update($data,$id){
        $this->sql = "update hot_rank_tb set game_id=?,game_name=?,`type`=?,status=?,order_num=?,`time`=? where id=?";
        $this->params = array($data['game_id'],$data['game_name'],$data['type'],$data['status'],$data['order_num'],time(),$id);
        $this->doExecute();
    }

    public function new_update($data,$id){
        $this->sql = "update new_game_hot_rank set game_id=?,game_name=?,`type`=?,down_url=?,hot=?,`time`=? where id=?";
        $this->params = array($data['game_id'],$data['game_name'],$data['type'],$data['down_url'],$data['hot'],time(),$id);
        $this->doExecute();
    }

    public function hot_insert($data){
        $this->sql="insert into hot_rank_tb(game_id ,game_name ,`type` ,status ,order_num ,`time`)values(?,?,?,?,?,?)";
        $this->params = array($data['game_id'], $data['game_name'], $data['type'], $data['status'], $data['order_num'], time());
        $this->doExecute();
    }

    public function new_insert($data){
        $this->sql="insert into new_game_hot_rank(game_id ,game_name ,`type` ,down_url ,hot ,`time`)values(?,?,?,?,?,?)";
        $this->params = array($data['game_id'], $data['game_name'], $data['type'], $data['down_url'], $data['hot'], time());
        $this->doExecute();
    }

    //游戏列表
    public function get_game_list(){
        $data = $this->mmc->get("game_list");
        if(!$data){
            $this->sql="select * from game where is_del=0 and status=1";
            $this->doResultList();
            $data = $this->result;
            $this->mmc->set("game_list", $data, 1 ,60);
        }
        return $data;
    }

}