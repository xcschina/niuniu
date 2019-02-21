<?php
COMMON('dao','randomUtils');
class article_dao extends Dao{

    public function __construct(){
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

    public function get_game_news($game_id){
        $data = memcache_get($this->mmc, 'news_game_news'.$game_id);
        if(!$data){
            $this->sql ="select * from articles where game_id=? order by id desc limit 10";
            $this->params = array($game_id);
            $this->doResultList();
            $data = $this->result;
            memcache_set($this->mmc, 'news_game_news'.$game_id, $data, 1, 600);
        }
        return $data;
    }

    public function get_game_strategy($game_id, $mod_id){
        $data = memcache_get($this->mmc, 'game_strategy'.$game_id."-".$mod_id);
        if(!$data){
            $this->sql ="select * from articles where game_id=? and part_id=? order by id desc limit 10";
            $this->params = array($game_id, $mod_id);
            $this->doResultList();
            $data = $this->result;
            memcache_set($this->mmc, 'game_strategy'.$game_id."-".$mod_id, $data, 1, 600);
        }
        return $data;
    }

   //获取模块列表
    public function get_parts_list(){
        $this->sql="select * from parts  order by id desc";
        $this->doResultList();
        return $this->result;
    }

    public function get_articles_list($params,$page=1){
        $this->limit_sql="select * from articles  where is_pub=1";
        if($params['part_id'] && is_numeric($params['part_id'])){
            $this->limit_sql=$this->limit_sql." and part_id =".$params['part_id'];
        }
        $this->limit_sql=$this->limit_sql." order by id desc";
        $this->doLimitResultList($page);
        return $this->result;
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

    // ---------------
    // v2 zbc
    // ---------------

    public function get_mod_articles($part_id,$params=array()){
        $articles = memcache_get($this->mmc, 'mod_articles_limit_'.$part_id);
        if(!$articles){
            $this->sql = "select * from articles where is_pub=1 and part_id=?";
            if((int)$params['limit']){
                $this->sql .= ' limit '.$params['limit'];
            }
            $this->params = array($part_id);
            $this->doResultList();
            $articles = $this->result;
            memcache_set($this->mmc, 'mod_articles_limit_'.$part_id, $articles, 1, 600);
        }
        return $articles;
    }



}
?>
