<?php
COMMON('dao','randomUtils');
class common_dao extends Dao{

    public function __construct() {
        parent::__construct();
        $this->mmc = new Memcache();
        $this->mmc->connect(MMCHOST, MMCPORT);
    }

    public function get_all_games(){
        $games = memcache_get($this->mmc, 'all_games');
        if(!$games){
            $this->sql = "select * from game where status=1 and is_del=0";
            $this->doResultList();
            $games = $this->result;
            memcache_set($this->mmc, 'all_games', $games, 1, 600);
        }
        return $games;
    }

    public function get_game_servs($game_id){
        $this->sql="select * from game_servs where game_id=?";
        $this->params=array($game_id);
        $this->doResultList();
        return $this->result;
    }

    public function get_game_info($id){
        $this->sql = "select * from game where status=1 and is_del=0 and id=?";
        $this->params=array($id);
        $this->doResult();
        return $this->result;
    }

    public function get_serv_info($game_id,$serv_id){
        $this->sql="select * from game_servs where game_id=? and serv_id=?";
        $this->params=array($game_id,$serv_id);
        $this->doResult();
        return $this->result;
    }

    public function get_serv_name($id){
        $this->sql="select * from game_servs where id=?";
        $this->params=array($id);
        $this->doResult();
        return $this->result['serv_name'];
    }

    public function get_channel_info($channel_id){
        $this->sql="select * from channels where id=?";
        $this->params=array($channel_id);
        $this->doResult();
        return $this->result;
    }
}
?>