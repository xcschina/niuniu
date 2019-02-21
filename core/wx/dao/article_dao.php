<?php
COMMON('dao');
class article_dao extends Dao {

    public function __construct() {
        parent::__construct();
        $this->mmc = new Memcache();
        $this->mmc->connect(MMCHOST, MMCPORT);
    }

    public function get_game_info($game_id){
        $data = memcache_get($this->mmc,'game_info'.$game_id);
        if(empty($data)){
            $this->sql = "select * from game where status=1 and is_del=0 and id=?";
            $this->params = array($game_id);
            $this->doResult();
            $data = $this->result;
            memcache_set($this->mmc, 'game_info'.$game_id, $data);
        }
        return $data;
    }

    public function get_usr_gift($open_id, $game_id){
        $data = memcache_get($this->mmc,'usr_gift'.$game_id."_".$open_id);
        if(empty($data)){
            $this->sql = "select * from weixin_gifts where game_id=? and openid=?";
            $this->params = array($game_id, $open_id);
            $this->doResult();
            $data = $this->result;
            memcache_set($this->mmc, 'usr_gift'.$game_id."_".$open_id, $data);
        }
        if(!empty($data)){
            return true;
        }
        return false;
    }

    public function get_game_gift($game_id){
        $this->sql = "select * from game_gifts where game_id=? and is_use=0 limit 1";
        $this->params = array($game_id);
        $this->doResult();
        return $this->result;
    }

    public function user_gift_get($open_id, $gift_id, $game_id){
        $this->sql = "update game_gifts set is_use=1 where id=?";
        $this->params = array($gift_id);
        $this->doExecute();
        $this->sql = "insert into weixin_gifts(openid, gift_id, game_id, add_time)values(?,?,?,?)";
        $this->params = array($open_id, $gift_id, $game_id, strtotime("now"));
        $this->doExecute();
    }

    public function get_setting(){
        $data = memcache_get($this->mmc,'setting');
        if(empty($data)){
            $this->sql = "select * from setting where id=1";
            $this->doResult();
            $data = $this->result;
            memcache_set($this->mmc, 'setting', $data, 1, 600);
        }
        return $data;
    }

    public function get_setting_by_id($id){
        $data = memcache_get($this->mmc,'setting_'.$id);
        if(empty($data)){
            $this->sql = "select * from setting where id=?";
            $this->params = array($id);
            $this->doResult();
            $data = $this->result;
            memcache_set($this->mmc, 'setting_'.$id, $data, 1, 600);
        }
        return $data;
    }

    public function search_game($game_name){
        $data = memcache_get($this->mmc,'search_game'.md5($game_name));
        if(empty($data)){
            $this->sql = "select * from game where game_name like ? order by id desc limit 5";
            $this->params = array($game_name."%");
            $this->doResultList();
            $data = $this->result;
            memcache_set($this->mmc, 'search_game'.md5($game_name), $data, 1, 3600);
        }
        return $data;
    }

    public function get_articles_list($part_id,$page){
        //$data = memcache_delete($this->mmc,'wx_artilce_list'.md5($part_id.$page));
        $data = memcache_get($this->mmc,'wx_artilce_list'.md5($part_id.$page));
        if(!$data){
            $this->limit_sql="select * from articles where is_pub=1";
            if($part_id && is_numeric($part_id)){
                $this->limit_sql=$this->limit_sql." and part_id =".$part_id;
            }
            $this->limit_sql=$this->limit_sql." order by id desc";
            $this->doLimitResultList($page);
            $data = $this->result;
            memcache_set($this->mmc, 'wx_artilce_list'.md5($part_id.$page), $data, 1, 600);
        }
        return $data;
    }

    public function get_article_info($id){
        $data = memcache_get($this->mmc,'wx_artilce_info'.$id);
        if(!$data){
            $this->sql="select * from articles where id=?";
            $this->params=array($id);
            $this->doResult();
            $data = $this->result;
            memcache_set($this->mmc, 'wx_artilce_info'.$id, $data, 1, 3600);
        }
        return $data;
    }
}