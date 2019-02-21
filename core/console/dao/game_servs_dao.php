<?php
COMMON('dao');
class game_servs_dao extends Dao {

    public function __construct() {
        parent::__construct();
        $this->mmc = new Memcache();
        $this->mmc->connect(MMCHOST, MMCPORT);
    }

    //获取游戏列表
    public function get_game_servs_list($params,$page){
        $this->limit_sql="select g.game_name,s.* from game_servs s inner join game as g on g.id=s.game_id   where 1=1";
        if($params['game_id'] && is_numeric($params['game_id'])){
            $this->limit_sql=$this->limit_sql." and s.game_id =".$params['game_id'];
        }
        if($params['serv_id'] && is_numeric($params['serv_id'])){
            $this->limit_sql=$this->limit_sql." and s.serv_id =".$params['serv_id'];
        }
        if($params['channel_name']){
            $this->limit_sql=$this->limit_sql." and s.serv_name like '%".$params['serv_name']."%'";
        }
        $this->limit_sql=$this->limit_sql." order by s.id desc";
        $this->doLimitResultList($page);
        return $this->result;
    }

    public function get_game_ch_servs($game_id){
        $this->sql = "select g.game_name,s.* from game_servs s inner join game as g on g.id=s.game_id where s.game_id=? order by s.id desc";
        $this->params = array($game_id);
        $this->doResultList();
        return $this->result;
    }

    public function get_game_channels(){
        $this->sql = "select * from channels order by id";
        $this->doResultList();
        return $this->result;
    }

    public function get_channel($ch_id){
        $this->sql = "select * from channels where id=?";
        $this->params = array($ch_id);
        $this->doResult();
        return $this->result;
    }

    public function clear_game_ch($game_id, $ch_id){
        $this->sql = "update game_servs set ch_".$ch_id."=0 where game_id=?";
        $this->params = array($game_id);
        $this->doExecute();
    }

    public function update_ch_servs($serv, $ch_id){
        $this->sql = "update game_servs set  ch_".$ch_id."=1 where id=?";
        $this->params = array($serv);
        $this->doExecute();
    }


    //获取区服信息
    public function get_serv_info($params){
        $this->sql="select * from game_servs where game_id=? and serv_id=?";
        $this->params=array($params['game_id'],$params['serv_id']);
        $this->doResult();
        return $this->result;
    }

    public function get_serv_info_byid($id){
        $this->sql="select * from game_servs where id=?";
        $this->params=array($id);
        $this->doResult();
        return $this->result;
    }

    //添加区服信息
    public function add_serv($params){
        $this->sql="insert into game_servs(game_id,serv_name,serv_id) values(?,?,?)";
        $this->params=array($params['game_id'],$params['serv_name'],$params['serv_id']);
        $this->doInsert();
        $this->mmc->delete("game_servs".$params['game_id']);
    }

    //修改渠道信息
    public function update_serv($params){
        $this->sql="update game_servs set game_id=?,serv_name=?,serv_id=? where id=?";
        $this->params=array($params['game_id'],$params['serv_name'],$params['serv_id'],$params['id']);
        $this->doExecute();
        $this->mmc->delete("game_servs".$params['game_id']);
    }
}