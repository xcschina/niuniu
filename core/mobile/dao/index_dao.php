<?php
COMMON('dao');

class index_dao extends Dao {

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

    public function get_character_games(){
        $games = memcache_get($this->mmc, 'h5_character_games');
        if(!$games){
            $this->sql = "select * from game where status=1 and is_character=1 and is_del=0";
            $this->doResultList();
            $games = $this->result;
            memcache_set($this->mmc, 'h5_character_games', $games, 1, 600);
        }
        return $games;
    }

    public function get_cate_hot_games($type=9999,$str){
        $games = memcache_delete($this->mmc, 'cate_hot_games'.$type);
        $games = memcache_get($this->mmc, 'cate_hot_games'.$type);
        if(!$games){
            if($str){
                $this->sql = "select a.type,b.* from hot_games as a inner join game as b on a.game_id=b.id where b.status=1 and b.is_del=0 and a.is_del=0 and a.`type`=? and b.channel_id in (?) limit 12";
                $this->params = array($type,$str);
                $this->doResultList();
            }else {
                $this->sql = "select a.type,b.* from hot_games as a inner join game as b on a.game_id=b.id where b.status=1 and b.is_del=0 and a.is_del=0 and a.`type`=? limit 12";
                $this->params = array($type);
                $this->doResultList();
            }
            $games = $this->result;
            memcache_set($this->mmc, 'cate_hot_games'.$type, $games, 1, 600);
        }
        return $games;
    }

    public function get_banners(){
        $banners = memcache_get($this->mmc, 'mod_articles11');
        if(!$banners){
            $this->sql = "select * from articles where is_pub=1 and part_id=11 order by lastupdate desc,id desc limit 5";
            $this->doResultList();
            $banners = $this->result;
            memcache_set($this->mmc, 'mod_articles11', $banners, 1, 600);
        }
        return $banners;
    }

    public function get_hot_item(){
        memcache_delete($this->mmc,'h5_hot_item');
        $items = memcache_get($this->mmc, 'h5_hot_item');
        if(!$items){
            $this->sql = "select a.*,b.game_name from products as a inner join game as b on a.game_id=b.id where a.is_pub=1 order by a.id desc limit 10";
            $this->doResultList();
            $items = $this->result;
            memcache_set($this->mmc, 'h5_hot_item', $items, 1, 600);
        }
        return $items;
    }

    public function get_letter_games($letter){
        $games = memcache_get($this->mmc, 'letter_games'.$letter);
        if(!$games){
            $this->sql = "select * from game where first_letter=? and status=1 and is_del=0";
            $this->params = array($letter);
            $this->doResultList();
            $games = $this->result;
            memcache_set($this->mmc, 'letter_games'.$letter, $games, 1, 600);
        }
        return $games;
    }

    public function get_channels_id($platform){
        $this->sql = "select id from channels where platform=?";
        $this->params = array($platform);
        $this->doResultList();
        return $this->result;
    }

    public function get_platform_id($id,$platform){
        $this->sql = "select DISTINCT(g.id),g.* from game as g left join game_downloads as d on g.id=d.game_id where g.is_del=0 and d.is_del=0 and g.`status`= 1 and g.id=".$id." and d.channel_id in (".$platform.")";
        $this->doResult();
        return $this->result;
    }

    public function get_games($type,$str,$letter){
        $games = memcache_get($this->mmc, 'get_letter_games_'.$letter."_".$str);
        if (!$games) {
            if ($letter == "hot") {
                if ($str == "") {
                    $this->sql = "select a.type,b.* from hot_games as a inner join game as b on a.game_id=b.id where b.is_del=0 and b.`status`= 1 and a.is_del=0 and a.`type`=?";
                    $this->params = array($type);
                    $this->doResultList();
                } else {
                    $this->sql = "select DISTINCT(b.id),a.type,b.* from hot_games as a left join game as b on a.game_id=b.id left join game_downloads as d on d.game_id=b.id
                                  where b.`status`= 1 and b.is_del=0 and a.is_del=0 and d.is_del=0 and a.`type`='".$type."' and d.channel_id in ( ".$str." )";
                    $this->doResultList();
                }
            } elseif ($str == "") {
                $this->sql = "select * from game where is_del=0 and `status`= 1 and first_letter=?";
                $this->params = array($letter);
                $this->doResultList();
            } else {
                $this->sql = "select DISTINCT(g.id),g.* from game as g left join game_downloads as d on g.id=d.game_id where g.is_del=0 and d.is_del=0 and g.`status`= 1 and g.first_letter='".$letter."' and d.channel_id in (".$str.")";
                $this->doResultList();
            }
            $games = $this->result;
            memcache_set($this->mmc, 'get_letter_games_'.$letter."_".$str, $games, 1, 600);
        }
        return $games;
    }

    public function get_iap_letter_games($letter){
        $games = memcache_get($this->mmc, 'iap_letter_games'.$letter);
        if(!$games){
            $this->sql = "select a.* from game as a inner join 7881_games as b on a.iap_game_id=b.id where b.products=1 and a.first_letter=? and a.status=1";
            $this->params = array($letter);
            $this->doResultList();
            $games = $this->result;
            memcache_set($this->mmc, 'iap_letter_games'.$letter, $games, 1, 600);
        }
        return $games;
    }

    public function get_mod_articles($part_id){
        $articles = $this->mmc->get('mod_articles'.$part_id);
        if(!$articles){
            $this->sql = "select * from articles where is_pub=1 and part_id=? order by lastupdate desc limit 10";
            $this->params = array($part_id);
            $this->doResultList();
            $articles = $this->result;
            $this->mmc->set('mod_articles'.$part_id, $articles, 1, 600);
        }
        return $articles;
    }

    public function get_all_buy(){
        $data = memcache_get($this->mmc, 'all_buy');
        if(!$data){
            $this->sql = "select g.id as game_id,g.* from hot_rank_tb as h left join game as g on h.game_id=g.id  where g.`status`=1 and g.is_del=0  order by h.order_num desc limit 5";
            $this->doResultList();
            $data = $this->result;
            memcache_set($this->mmc, 'all_buy', $data, 1, 3600);
        }
        return $data;
    }

    public function get_special_sells(){
        $data = memcache_get($this->mmc, 'special_sells');
        if(!$data){
            $this->sql = "select a.*,b.game_name,b.game_icon,b.game_intr,b.game_size,b.game_tags,b.char_min_rate from special_sells as a inner join game as b on a.game_id=b.id
                  where a.is_del=0 order by a.id desc limit 5";
            $this->doResultList();
            $data = $this->result;
            memcache_set($this->mmc, 'special_sells', $data, 1, 3600);
        }
        return $data;
    }

    public function get_up_char_list(){
        $this->sql = "select * from game where char_min_rate = 0 ";
        $this->params = array();
        $this->doResultList();
        return $this->result;
    }

    public function get_up_refill_list(){
        $this->sql = "select * from game where refill_min_rate is NULL limit 100";
        $this->params = array();
        $this->doResultList();
        return $this->result;
    }

    public function get_game_channels($game_id){
        $this->sql = "select * from channel_discount where game_id=?";
        $this->params = array($game_id);
        $this->doResult();
        return $this->result;
    }

    public function up_game_char_rate($game_id,$min){
        $this->sql = "update game set char_min_rate=? where id=?";
        $this->params = array($min,$game_id);
        $this->doExecute();
    }

    public function up_game_refill_rate($game_id,$min){
        $this->sql = "update game set refill_min_rate=? where id=?";
        $this->params = array($min,$game_id);
        $this->doExecute();
    }

    public function get_total_special(){
        $this->sql = "select * from game where is_del=0 order by char_min_rate asc limit 5";
        $this->doResultList();
        return $this->result;
    }

    public function get_channels_list(){
        $this->sql="select * from channels ";
        $this->doResultList();
        return $this->result;
    }

    public function search_game($keyword,$page){
        $this->limit_sql = "select * from game where game_name like ?  and status=1 and is_del=0 ";
        $this->limit_sql .= " order by is_hot desc";
        $this->params = array('%'.$keyword.'%');
        $this->doLimitResultList($page);
        return $this->result;
    }

    public function search_game_num($keyword){
        $games = memcache_get($this->mmc, 'search_game_num'.md5($keyword));
        if(!$games){
            $this->sql = "select count(*) as num from game where game_name like ?  and status=1 and is_del=0 ";
            $this->params = array('%'.$keyword.'%');
            $this->doResult();
            $games = $this->result;
            memcache_set($this->mmc, 'search_game_num'.md5($keyword), $games, 1, 600);
        }
        return $games;
    }

    // zbc
    public function get_article_by_id($id){
        $article = memcache_get($this->mmc, 'article_by_id'.$id);
        if(!$article){
            $this->sql = "select * from articles where is_pub=1 and id=?";
            $this->params = array($id);
            $this->doResultList();
            $article = $this->result;
            memcache_set($this->mmc, 'article_by_id'.$id, $article, 1, 600);
        }
        return $article;
    }
    public function get_special_list(){
        $this->sql = "select * from game where is_del=0 and `status`= 1 order by char_min_rate asc limit 5";
        $this->doResultList();
        return $this->result;
    }
    public function get_new_game_list(){
        $this->sql = "select * from game where is_del=0 order by id desc limit 4";
        $this->doResultList();
        return $this->result;
    }

    public function get_user_coupon_log($user_id,$id){
        $this->sql = "SELECT cou.*,user_log.receive_time from coupon_user_log_tb as user_log left join coupon_tb as cou on user_log.coupon_id=cou.id where user_log.user_id=? and user_log.id=?";
        $this->params = array($user_id,$id);
        $this->doResult();
        return $this->result;
    }

    public function update_coupon_time($start_time,$end_time,$id){
        $this->sql = "update coupon_user_log_tb set receive_time=?,start_time=?,end_time=? where id=?";
        $this->params = array(time(),$start_time,$end_time,$id);
        $this->doExecute();
    }

    public function get_hot_game(){
        $this->sql="select * from hot_games a,game b where a.game_id=b.id";
        $this->params = array();
        $this->doResultList();
        return $this->result;

    }

    public function get_my_coupon($user_id,$page,$params){
        $this->limit_sql = "select b.name,b.type,b.discount,b.total_amount,b.discount_amount,a.start_time,a.end_time,a.use_time
                            from coupon_user_log_tb  as a inner join coupon_tb as b on a.coupon_id =b.id
                            where a.user_id = ? and  a.end_time is not NULL ";
        if($params == "1"){
            $this->limit_sql .=" and a.use_time > 0";
        }else if($params == "2"){
            $this->limit_sql .=" and a.end_time <".time()." and a.use_time is null";
        }
        $this->limit_sql .=" order by a.use_time,end_time desc" ;
        $this->params = array($user_id);
        $this->doLimitResultList($page,6);
        return $this->result;
    }

}