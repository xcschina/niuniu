<?php
COMMON('dao');
class api_dao extends Dao{

    public function __construct(){
        parent::__construct();
        $this->mmc = new Memcache();
        $this->mmc->connect(MMCHOST, MMCPORT);
    }

    public function get_banner(){
        $data = memcache_get($this->mmc, "app_top_banner");
        if (!$data) {
            $this->sql = "select * from app_banner_tb where is_del=0 order by id desc limit 5";
            $this->doResultList();
            $data = $this->result;
            memcache_set($this->mmc, "app_top_banner",$data, 1, 3600);
        }
        return $data;
    }

    public function hot_game(){
        $data = memcache_get($this->mmc, "app_hot_game");
        if (!$data){
            $this->sql = "select app.game_icon,app.game_id,app.down_url,app.rate,app.game_packname,app.game_size,game.game_name
                          from app_game_tb as app left join game on game.id=app.game_id
                          where app.is_del=0 and app.is_hot=1 order by app.id desc limit 12";
            $this->doResultList();
            $data = $this->result;
            memcache_set($this->mmc, "app_hot_game", $data, 1, 3600);
        }
        return $data;

    }
    public function hot_more(){
        $data = memcache_get($this->mmc,"app_hot_game_list");
        if(!$data){
            $this->sql="select * from app_game_tb where is_del=0 and is_hot=1 order by id desc ";
            $this->doResultList();
            $data = $this->result;
            memcache_set($this->mmc,"app_hot_game_list",$data, 1, 3600);
        }
        return $data;
    }

    public function new_game(){
        $data = memcache_get($this->mmc, "new_game_list");
        if (!$data) {
            $this->sql = "select app.game_icon,app.game_id,app.down_url,app.game_packname,app.game_size,game.game_name
                          from app_game_tb as app left join game on game.id=app.game_id
                          where app.is_del=0 and app.is_new=1 order by app.id desc limit 3";
            $this->doResultList();
            $data = $this->result;
            memcache_set($this->mmc, "new_game_list", $data, 1, 3600);
        }
        return $data;
    }

    public function new_more(){
        $data=memcache_get($this->mmc,"new_more_list");
        if(!$data){
            $this->sql="select * from app_game_tb where is_del=0 and is_new=1 order by id desc ";
            $this->doResultList();
            $data = $this->result;
            memcache_set($this->mmc,"new_more_list",$data, 1, 3600);
        }
        return $data;
    }

    public function get_hot_search(){
        $data = memcache_get($this->mmc,"app_hot_search");
        if(!$data){
            $this->sql="select * from app_game_tb where is_del=0 and hot_search=1 order by id desc limit 20";
            $this->doResultList();
            $data = $this->result;
            memcache_set($this->mmc, "app_hot_search",$data, 1, 3600);
        }
        return $data;
    }

    public function search($tag,$page=1){
        $data = memcache_get($this->mmc, "search_list_".$tag."_".$page);
        if (!$data) {
            $this->limit_sql = "select * from app_game_tb where is_del=0 ";
            if(!empty($tag)){
                $this->limit_sql = $this->limit_sql." and tags like '%".$tag."%'";
            }
            $this->limit_sql .= " order by id desc";
            $this->doLimitResultList($page);
            $data = $this->result;
            memcache_set($this->mmc, "search_list_".$tag."_".$page, $data, 1, 3600);
        }
        $pre_page = $page - 1;
        $previous_page = memcache_get($this->mmc, "search_list_".$tag."_".$pre_page);
        if($previous_page==$data){
            $data='';
        }
        return $data;
    }

    public function get_search($game_name){
        $data = memcache_get($this->mmc, "search_name_".$game_name);
        if (!$data) {
            $this->sql = "select app.game_icon,app.game_id,app.down_url,app.rate,app.game_packname,app.tags,app.game_size,app.`desc`,game.game_name
                          from app_game_tb as app left join game on game.id=app.game_id
                          where app.is_del=0 and game.game_name like '%".$game_name."%' order by game.id desc limit 20 ";
            $this->doResultList();
            $data = $this->result;
            memcache_set($this->mmc, "search_name_".$game_name , $data, 1, 3600);
        }
        return $data;
    }

    public function get_app_game($status=''){
        $this->sql="select * from app_game_tb where is_del=0 ";
        if($status){
            $this->sql.=" and".$status."=1 ";
        }
        $this->sql.="  order by id desc limit 20";
        $this->doResultList();
        return $this->result;
    }

    public function get_game_detail($game_id){
        $data = memcache_get($this->mmc,"app_game_detail_".$game_id);
        if(!$data){
            $this->sql = "select game.id as game_id,game.game_name,game.game_icon,game.img1,game.img2,game.img3,game.img4,app.tags,app.down_url,app.game_packname,app.game_size,app.introduce as `desc`
                          from game left join app_game_tb as app on game.id=app.game_id WHERE game.id= ?";
            $this->params=array($game_id);
            $this->doResult();
            $data = $this->result;
            memcache_set($this->mmc, "app_game_detail_".$game_id,$data, 1, 3600);
        }
        return $data;
    }

    public function get_detail_similar($game_id){
        $data = memcache_get($this->mmc,"app_detail_similar_".$game_id);
        if(!$data){
            $this->sql = "select game_name,game_icon,game_id,rate from app_game_tb where is_del=0 order by rand() limit 6";
            $this->params=array($game_id);
            $this->doResultList();
            $data = $this->result;
            memcache_set($this->mmc, "app_detail_similar_".$game_id,$data, 1, 3600);
        }
        return $data;
    }

    public function get_detail_top($game_id){
        $data = memcache_get($this->mmc,"app_detail_top_".$game_id);
        if(!$data){
            $this->sql = "select game_name,game_icon,game_id,rate from app_game_tb where is_del=0 and is_top=1 order by id desc limit 3";
            $this->params=array($game_id);
            $this->doResultList();
            $data = $this->result;
            memcache_set($this->mmc, "app_detail_top_".$game_id,$data, 1, 3600);
        }
        return $data;
    }

    public function rate_game(){
        $data = memcache_get($this->mmc, "rate_game_list");
        if (!$data) {
            $this->sql = "select app.game_icon,app.game_id,app.down_url,app.rate,app.game_packname,app.tags,app.game_size,app.`desc`,game.game_name
                          from app_game_tb as app left join game on game.id=app.game_id
                          where app.is_del=0 and app.is_rate=1 order by app.id desc limit 6";
            $this->doResultList();
            $data = $this->result;
            memcache_set($this->mmc,"rate_game_list",$data, 1, 3600);
        }
        return $data;
    }
    public function rate_more(){
        $data = memcache_get($this->mmc, "rate_more_list");
        if (!$data) {
            $this->sql = "select * from app_game_tb where is_del=0 and is_rate=1 order by id desc ";
            $this->doResultList();
            $data = $this->result;
            memcache_set($this->mmc,"rate_more_list",$data, 1, 3600);
        }
        return $data;
    }
    public function top_game(){
        $data = memcache_get($this->mmc, "top_game_list");
        if (!$data) {
            $this->sql = "select app.game_icon,app.game_id,app.banner_url,app.down_url,app.rate,app.game_packname,app.tags,app.game_size,app.`desc`,game.game_name
                          from app_game_tb as app left join game on game.id=app.game_id
                          where app.is_del=0 and app.is_top=1 order by app.id desc limit 8";
            $this->doResultList();
            $data = $this->result;
            memcache_set($this->mmc,"top_game_list",$data, 1, 3600);
        }
        return $data;
    }
    public function top_more(){
        $data = memcache_get($this->mmc, "top_more_list");
        if (!$data) {
            $this->sql = "select * from app_game_tb where is_del=0 and is_top=1 order by id desc ";
            $this->doResultList();
            $data = $this->result;
            memcache_set($this->mmc,"top_more_list",$data, 1, 3600);
        }
        return $data;
    }
    public function rank_list(){
//        $data = memcache_get($this->mmc, "rank_list");
        if (!$data) {
            $this->sql = "select app.game_name,app.game_icon,app.game_id,app.banner_url,app.down_url,app.rate,app.game_packname,app.tags,app.game_size,app.`desc`,rank.app_gid,rank.hot
                          from app_game_tb as app join app_rank_tb as rank on app.id=rank.app_gid
                          where app.is_del=0 and rank.hot>0 order by hot desc limit 10 ";
            $this->doResultList();
            $data = $this->result;
            memcache_set($this->mmc,"rank_list",$data, 1, 3600);
        }
        return $data;
    }

    public function get_sign_data($user_id,$day){
        return memcache_get($this->mmc, "this_day_sign_".$day."_".$user_id);
    }

    public function set_sign_data($user_id,$day,$num){
        $data=array("user_id"=>$user_id,"sign_date"=>$day,"num"=>$num);
        $this->mmc->set("this_day_sign_".$day."_".$user_id,$data, 1,86400);
    }

    public function get_integral(){
        $data = memcache_get($this->mmc,"get_sign_list");
        return $data;
    }
    public function game_list(){
        $data = memcache_get($this->mmc,"get_game_list");
        if(!$data){
            $this->sql = "select * from game where status=1 and is_del=0";
            $this -> doResultList();
            $data = $this->result;
            memcache_set($this->mmc,"get_game_list",$data,1,3600);
        }
        return $data;
    }



}