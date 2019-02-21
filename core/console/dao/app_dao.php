<?php
COMMON('dao');
class app_dao extends Dao {
    public function __construct() {
        parent::__construct();
        $this->mmc = new Memcache();
        $this->mmc->connect(MMCHOST, MMCPORT);
    }
    public function banner_gain($page){
        $this->limit_sql = "select * from app_banner_tb where is_del=0 order by id desc";
        $this->params=array();
        $this->doLimitResultList($page);
        return $this->result;
    }
    public function get_banner($id){
        $this->sql="select * from app_banner_tb where id=?";
        $this->params=array($id);
        $this->doResult();
        return $this->result;
    }
    //删除图片
    public function del_banner($id){
        $this->sql="update app_banner_tb set is_del =1 where id=?";
        $this->params=array($id);
        $this->doExecute();
    }
    public function insert_banner($params){
        $this->sql = "insert into app_banner_tb(remark,url,add_time,is_del,img,`type`,game_id)
                      VALUES(?,?,?,?,?,?,?)";
        $this->params=array($params['remark'],$params['url'],time(),'0',$params['img'],$params['type'],$params['game_id']);
        $this->doInsert();
        return $this->LAST_INSERT_ID;
    }
    public function relation_play(){
        $this->sql="select id,game_name from game where is_del=0 and `status`=1 order by id desc";
        $this->doResultList();
        return $this->result;
    }
    public function play_name(){
        $this->sql="select id,game_name from app_game_tb ";
        $this->doResultList();
        return $this->result;
    }
    public function play_list($params,$page){
        $this->limit_sql="select * from app_game_tb where is_del=0 ";
        if($params['game_name']){
            $this->limit_sql=$this->limit_sql." and game_name like '%".$params['game_name']."%'";
        }
        if($params['is_state']){
            $this->limit_sql=$this->limit_sql." and ".$params['is_state']."=1";
        }
        $this->limit_sql=$this->limit_sql." order by id desc";
        $this->doLimitResultList($page);
        return $this->result;
    }
    //获取游戏信息
    public function get_play_info($params){
        $this->sql="select * from app_game_tb where game_name=? and is_del=0";
        $this->params=array($params['play_name']);
        $this->doResult();
        return $this->result;
    }
    //添加游戏信息
    public function add_play($params){
        $this->sql="insert into app_game_tb(game_name,game_id,game_icon,banner_url,rate,down_url,tags,`desc`,is_hot,is_new,is_rate,is_top,is_del,add_time,game_packname,game_size,hot_search,download,score,introduce) values(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";
        $this->params=array($params['play_name'],$params['game_id'],$params['game_icon'],$params['banner_url'],$params['rate'],$params['down_url'],implode(',',$params['tags']),$params['desc'],$params['is_hot'],$params['is_new'],$params['is_rate'],$params['is_top'],'0',time(),$params['game_packname'],$params['game_size'],$params['hot_search'],$params['download'],$params['score'],$params['introduce']);
        $this->doInsert();
        $game_id = $this->LAST_INSERT_ID;
        return $game_id;
    }
    //修改游戏信息
    public function update_play($params){
        $this->sql="update app_game_tb set game_name=?,game_id=?,game_icon=?,banner_url=?,rate=?,down_url=?,tags=?,`desc`=?,is_hot=?,is_new=?,is_rate=?,is_top=?,add_time=?,game_packname=?,game_size=?,hot_search=?,download=?,score=?,introduce=? where id=?";
        $this->params=array($params['play_name'],$params['game_id'],$params['game_icon'],$params['banner_url'],$params['rate'],$params['down_url'],implode(',',$params['tags']),$params['desc'],$params['is_hot'],$params['is_new'],$params['is_rate'],$params['is_top'],time(),$params['game_packname'],$params['game_size'],$params['hot_search'],$params['download'],$params['score'],$params['introduce'],$params['id']);
        $this->doExecute();
        memcache_delete($this->mmc,'play_info'.$params['id']);
    }
    public function get_play($id){
        $this->sql="select * from app_game_tb where id=?";
        $this->params=array($id);
        $this->doResult();
        return $this->result;
    }
    //删除游戏
    public function del_play($id){
        $this->sql="update app_game_tb set is_del =1 where id=?";
        $this->params=array($id);
        $this->doExecute();
    }
    public function gain_info($id){
        $this->sql="select * from app_game_tb where is_del=0 and id=?";
        $this->params=array($id);
        $this->doResult();
        return $this->result;
    }


}