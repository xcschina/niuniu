<?php
COMMON('dao','randomUtils');
class common_dao extends Dao{

    public function __construct() {
        parent::__construct();
        $this->mmc = new Memcache();
        $this->mmc->connect(MMCHOST, MMCPORT);
    }

    public function get_hot_games(){
        $games = memcache_get($this->mmc, 'h5_hot_games');
        if(!$games){
            $this->sql = "select a.type,b.* from hot_games as a inner join game as b on a.game_id=b.id
              where a.type=9999 and b.status=1 and b.is_del=0 and a.is_del=0 order by a.id asc limit 20";
            $this->doResultList();
            $games = $this->result;
            memcache_set($this->mmc, 'h5_hot_games', $games, 1, 600);
        }
        return $games;
    }

    public function get_all_games(){
        // memcache_delete($this->mmc, 'all_games');
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

    public function get_serv_info_by_id($id){
        $this->sql="select * from game_servs where id=?";
        $this->params=array($id);
        $this->doResult();
        return $this->result;
    }

    public function get_mod_articles($part_id){
        $articles = memcache_get($this->mmc, 'mod_articles'.$part_id);
        if(!$articles){
            $this->sql = "select * from articles where is_pub=1 and part_id=?";
            $this->params = array($part_id);
            $this->doResultList();
            $articles = $this->result;
            memcache_set($this->mmc, 'mod_articles'.$part_id, $articles, 1, 600);
        }
        return $articles;
    }

    public function get_friendly_links(){
        $friendly_links = memcache_get($this->mmc, 'friendly_links');
        if(!$friendly_links){
            $this->sql="select * from friendly_links";
            $this->doResultList();
            $friendly_links= $this->result;
            memcache_set($this->mmc, 'friendly_links', $friendly_links, 1, 600);
        }
        return $friendly_links;
    }

    public function get_service_qq(){
        $data = $this->mmc->get("serviceqq");
        if(!$data){
            $this->sql = "select * from admins where `group`='vip' and is_del=0 and qq<>''";
            $this->doResultList();
            $data = $this->result;
            $this->mmc->set("serviceqq", $data, 1, 3600);
        }
        return $data;
    }

    public function get_channel_info($channel_id){
        $this->sql="select * from channels where id=?";
        $this->params=array($channel_id);
        $this->doResult();
        return $this->result;
    }
}