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
        $games = memcache_get($this->mmc, 'pc_hot_games');
        if(!$games){
            $this->sql = "select a.type,b.* from hot_games as a inner join game as b on a.game_id=b.id
              where a.type=9999 and b.status=1 and b.is_del=0 and a.is_del=0 order by a.id asc limit 21";
            $this->doResultList();
            $games = $this->result;
            memcache_set($this->mmc, 'pc_hot_games', $games, 1, 600);
        }
        return $games;
    }

    public function get_cate_hot_games(){
        $games = memcache_get($this->mmc, 'cate_hot_games');
        if(!$games){
            $this->sql = "select a.type,b.* from hot_games as a inner join game as b on a.game_id=b.id where b.status=1 and b.is_del=0 and a.is_del=0";
            $this->doResultList();
            $games = $this->result;
            memcache_set($this->mmc, 'cate_hot_games', $games, 1, 600);
        }
        return $games;
    }


    public function get_banners(){
        $banners = memcache_get($this->mmc, 'mod_articles7');
        if(!$banners){
            $this->sql = "select * from articles where is_pub=1 and part_id=7 order by lastupdate desc,id desc limit 5";
            $this->doResultList();
            $banners = $this->result;
            memcache_set($this->mmc, 'mod_articles7', $banners, 1, 600);
        }
        return $banners;
    }

    public function get_mod_articles($part_id){
        $articles = memcache_get($this->mmc, 'mod_articles'.$part_id);
        if(!$articles){
            $this->sql = "select * from articles where is_pub=1 and part_id=? order by lastupdate desc limit 10";
            $this->params = array($part_id);
            $this->doResultList();
            $articles = $this->result;
            memcache_set($this->mmc, 'mod_articles'.$part_id, $articles, 1, 600);
        }
        return $articles;
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

    public function search_game($keyword){
        $games = memcache_get($this->mmc, 'search_game'.md5($keyword));
        if(!$games){
            $this->sql = "select * from game where (game_name like ? or en_name like ?) and status=1 and is_del=0 order by is_hot desc";
            $this->params = array($keyword.'%',$keyword.'%');
            $this->doResultList();
            $games = $this->result;
            memcache_set($this->mmc, 'search_game'.md5($keyword), $games, 1, 600);
        }
        return $games;
    }

    public function get_special_sells(){
        $data = memcache_get($this->mmc, 'special_sells');
        if(!$data){
            $this->sql = "select a.*,b.game_name from special_sells as a inner join game as b on a.game_id=b.id
                  where a.is_del=0 order by a.id desc limit 5";
            $this->doResultList();
            $data = $this->result;
            memcache_set($this->mmc, 'special_sells', $data, 1, 3600);
        }
        return $data;
    }

    // 验证首充号 <zbc>
    public function check_game_user($game_user){
        $this->sql = "SELECT a.`id`, a.`game_user`, a.`role_name`, a.`serv_id`, a.`game_channel` AS ch_id, a.`game_id` FROM orders AS a INNER JOIN products AS b ON a.`product_id`=b.`id` WHERE a.`game_user`=? AND a.`status`=2 AND b.`type`=1";
        $this->params = array($game_user);
        $this->doResult();
        return $this->result;
    }

    // 获取指定id的文章内容 <zbc>
    public function get_article_by_id($id){
        $article = memcache_get($this->mmc, 'article_by_id'.$id);
        if(!$article){
            $this->sql = "SELECT * FROM articles WHERE is_pub=1 AND id=? ORDER BY lastupdate DESC LIMIT 1";
            $this->params = array($id);
            $this->doResultList();
            $article = $this->result;
            memcache_set($this->mmc, 'article_by_id'.$id, $article, 1, 600);
        }
        return $article;
    }

    // 实时交易 <zbc>
    public function get_last_trades($num=1){
        $this->sql = "SELECT b.`game_name`, a.`title`, a.`pay_money` FROM orders AS a LEFT JOIN game AS b ON b.`id` = a.`game_id` WHERE a.`platform`=0 AND a.`is_del`=0 AND a.`status`=2 ORDER BY a.`ship_time` DESC LIMIT ".intval($num);
        $this->doResultList();
        $trades = $this->result;
        return $trades;
    }

    // 混合 实时交易 <zbc>
    public function get_mix_trades(){
        $trades = memcache_get($this->mmc, 'mix_trades');
        if(!$trades){
            $real = $this->get_last_trades(5);
            $this->sql = "SELECT trades FROM setting WHERE id=1";
            $this->doResultList();
            $trade = $this->result[0]['trades'];
            if(!empty($trade)){
                $temp = explode(',', trim($trade,','));
                $virtual = array();
                foreach ($temp as $key => $val) {
                    list(
                        $virtual[$key]['game_name'],
                        $virtual[$key]['title'],
                        $virtual[$key]['pay_money']
                        ) = explode('|', $val);
                }
                $trades  = array_merge($real, $virtual);
                shuffle($trades);
            }else{
                $trades = $real;
            }
            memcache_set($this->mmc, 'mix_trades', $trades, 1, 600);
        }
        return $trades;
    }

    public function get_my_gift($user_id,$batch_id){
        $this->sql = "select * from game_gifts where buyer_id = ? and batch_id = ?";
        $this->params=array($user_id,$batch_id);
        $this->doResult();
        return $this->result;
    }

    public function get_mission_gift($user_id,$batch_id){
        $this->sql = "select * from game_gifts where buyer_id = ? and batch_id = ?";
        $this->params=array($user_id,$batch_id);
        $this->doResultList();
        return $this->result;
    }

    public function insert_my_gift($openid,$user_id,$gift_id,$game_id,$batch_id){
        $this->sql = "insert into weixin_gifts(openid,user_id,gift_id,game_id,batch_id,add_time) VALUES(?,?,?,?,?,?)";
        $this->params=array($openid,$user_id,$gift_id,$game_id,$batch_id,time());
        $this->doInsert();
        return $this->LAST_INSERT_ID;
    }

    public function get_gift_info($gift_id){
        $this->sql = "select * from game_gift_info where id = ?";
        $this->params=array($gift_id);
        $this->doResult();
        return $this->result;
    }

    public function query_last_gift($batch_id){
        $this->sql = "select * from game_gifts where batch_id = ? and is_use = 0";
        $this->params=array($batch_id);
        $this->doResult();
        return $this->result;
    }

    public function update_game_gifts($id,$user_id){
        $this->sql = "update game_gifts set is_use=1,buyer_id=?,buy_time=? where id=?";
        $this->params=array($user_id,time(),$id);
        $this->doExecute();
    }

    public function get_my_effi_time($params){
        $this->sql = "select * from game_gift_info where id = ?";
        $this->params=array($params);
        $this->doResult();
        return $this->result;
    }

    public function get_active_info($user_id,$active_id){
        $this->sql = "select * from active_tb where user_id = ? and active_id = ?";
        $this->params=array($user_id,$active_id);
        $this->doResult();
        return $this->result;
    }

    public function show_active_all(){
        $this->sql = "select a.prize_name,a.add_time,b.mobile from prize_log_tb a,user_info b where a.user_id = b.user_id ORDER BY a.add_time DESC";
        $this->params=array();
        $this->doResultList();
        return $this->result;
    }

    public function insert_active_info($user_id,$active_id){
        $this->sql = "insert into active_tb(user_id,active_id,add_time) VALUES(?,?,?)";
        $this->params=array($user_id,$active_id,time());
        $this->doInsert();
//        return $this->LAST_INSERT_ID;
    }

    public function add_prize_log($user_id,$params){
        $this->sql = "insert into prize_log_tb(user_id,prize_name,prize_id,add_time,type,coupon_id,gift_id) VALUES(?,?,?,?,?,?,?)";
        $this->params=array($user_id,$params['prize_name'],$params['prize_id'],time(),$params['type'],$params['coupon_id'],$params['code_id']);
        $this->doInsert();
    }

    public function get_active_total($active_id){
        $this->sql = "select count(1) as sum from active_tb where active_id = ?";
        $this->params=array($active_id);
        $this->doResult();
        return $this->result['sum'];
    }

    public function get_coupon_info($coupon_id,$user_id){
        $this->sql = "select * from coupon_user_log_tb where coupon_id = ? and user_id = ? and receive_time is NOT NULL";
        $this->params=array($coupon_id,$user_id);
        $this->doResult();
        return $this->result;
    }

    public function get_coupon_last_id($coupon_id){
        $this->sql = "select * from coupon_user_log_tb where coupon_id = ? and receive_time is NOT NULL";
        $this->params=array($coupon_id);
        $this->doResult();
        return $this->result;
    }

    public function get_coupon_last_log($coupon_id){
        $this->sql = "select * from coupon_user_log_tb where coupon_id = ? and receive_time is NULL";
        $this->params=array($coupon_id);
        $this->doResult();
        return $this->result;
    }

    public function update_coupon_log_date($id,$endtime,$user_id){
        $this->sql = "update coupon_user_log_tb set user_id=?,receive_time=?,start_time=?,end_time=? where id = ? ";
        $this->params=array($user_id,time(),time(),$endtime,$id);
        $this->doExecute();
        return $this->result;
    }

    public function update_coupon_log($id,$user_id){
        $this->sql = "update coupon_user_log_tb set user_id=? where id = ? ";
        $this->params=array($user_id,$id);
        $this->doExecute();
        return $this->result;
    }


    public function get_type_coupon($value){
        $this->sql = "select * from coupon_tb where id = ?";
        $this->params=array($value);
        $this->doResult();
        return $this->result;
    }

    public function get_coupon_all($params){
        $this->sql = "select * from active_tb where user_id = ?";
        $this->params=array($params);
        $this->doResultList();
        return $this->result;
    }

    public function get_coupon_1(){
        $this->sql = "select * from coupon_tb where id in(64,65,66,67,68,69)";
        $this->params=array();
        $this->doResultList();
        return $this->result;
    }

    public function get_coupon_2(){
        $this->sql = "select * from coupon_tb where id in(70,71,72)";
        $this->params=array();
        $this->doResultList();
        return $this->result;
    }

    public function get_coupon_3(){
        $this->sql = "select * from coupon_tb where id = 75";
        $this->params=array();
        $this->doResult();
        return $this->result;
    }

    public function get_prize_all($user_id){
        $this->sql = "select * from prize_log_tb where user_id = ?";
        $this->params=array($user_id);
        $this->doResultList();
        return $this->result;
    }

    public function get_coupon($id){
        $this->sql = "select * from coupon_tb where id = ?";
        $this->params=array($id);
        $this->doResultList();
        return $this->result;
    }

    public function insert_my_coupon($parmas,$user_id){
        $this->sql = "insert into coupon_user_log_tb(coupon_id,user_id,add_time,receive_time,start_time,end_time) VALUES(?,?,?,?,?,?)";
        $this->params=array($parmas['id'],$user_id,time(),time(),$parmas['start_time'],$parmas['end_time']);
        $this->doInsert();
    }

    public function get_code_gift($id){
        $this->sql = "select * from game_gifts where id = ?";
        $this->params=array($id);
        $this->doResult();
        return $this->result;
    }
    public function get_coupon_mission($user_id){
        $this->sql = "select * from coupon_user_log_tb where user_id=? and coupon_id in(75,72,71,70,69,68,67,66,65,64)";
        $this->params=array($user_id);
        $this->doResultList();
        return $this->result;
    }

    public function get_share_log($user_id){
        $this->sql = "select * from share_log where user_id = ?";
        $this->params=array($user_id);
        $this->doResult();
        return $this->result;
    }

    public function insert_share_log($user_id){
        $this->sql = "insert into share_log(user_id,award_num) VALUES(?,?)";
        $this->params=array($user_id,0);
        $this->doInsert();
    }

    public function update_share_log($new_num,$user_id){
        $this->sql = "update share_log set award_num = ? where user_id = ? ";
        $this->params=array($new_num,$user_id);
        $this->doExecute();
    }

}