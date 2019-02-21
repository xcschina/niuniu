<?php
COMMON('dao');
class rank_dao extends Dao {
    public function __construct() {
        parent::__construct();
        $this->mmc = new Memcache();
        $this->mmc->connect(MMCHOST, MMCPORT);
    }

    public function rank_gain(){
        $this->sql = "select app.*,rank.* from app_game_tb as app join app_rank_tb as rank on app.id=rank.app_gid where app.is_del=0 and rank.hot>0 order by hot desc limit 10";
        $this->doResultList();
        return $this->result;
    }

    public function insert_rank($params){
        $this->sql = "insert into app_rank_tb (app_gid,hot,`time`) values (?,?,?)";
        $this->params = array($params['game_id'],$params['hot'],time());
        $this->doExecute();
    }

    public function game_name(){
        $this->sql = "select id,game_name from app_game_tb where is_del=0 order by id desc";
        $this->doResultList();
        return $this->result;
    }

    public function get_rank($id){
        $this->sql = "select * from app_rank_tb where id=?";
        $this->params = array($id);
        $this->doResultList();
        return $this->result;
    }

    public function get_game($app_gid){
        $this->sql = "select  app.*,rank.* from app_game_tb as app join app_rank_tb as rank on app.id=rank.app_gid where app.id= ?";
        $this->params = array($app_gid['app_gid']);
        $this->doResult();
        return $this->result;
    }
    public function update_rank($params){
        $this->sql = "update app_rank_tb set app_gid=?,hot=?,`time`=? where id=?";
        $this->params = array($params['game_id'],$params['hot'],time(),$params['id']);
        $this->doExecute();
    }

}