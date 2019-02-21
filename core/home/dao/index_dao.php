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

    public function get_hot_rank(){
        $rank = memcache_get($this->mmc, 'hot_rank');
        if(!$rank){
            $this->sql = "select * from hot_rank_tb order by order_num desc limit 10";
            $this->params = array();
            $this->doResultList();
            $rank = $this->result;
            memcache_set($this->mmc, 'hot_rank', $rank, 1, 600);
        }
        return $rank;
    }

    public function get_new_game_rank(){
        $rank = memcache_get($this->mmc, 'new_game_rank');
        if(!$rank){
            $this->sql = "select * from new_game_hot_rank order by `hot` desc limit 10";
            $this->params = array();
            $this->doResultList();
            $rank = $this->result;
            memcache_set($this->mmc, 'new_game_rank', $rank, 1, 600);
        }
        return $rank;
    }

    public function get_game_download_count($gid,$platform){
        $data = memcache_get($this->mmc,"download_count_".$gid."_".$platform);
        if(!$data){
            $this->sql = "select COUNT(*) as sum from game_downloads as a inner join channels as b on a.channel_id=b.id where a.is_del=0 and a.game_id=? and b.platform=?";
            $this->params = array($gid,$platform);
            $this->doResult();
            $data = $this->result['sum'];
            memcache_set($this->mmc,"download_count_".$gid."_".$platform, $data,1, 600);
        }
        return $data;
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
        $this->sql = "select a.`id`, a.`game_user`, a.`role_name`, a.`serv_id`, a.`game_channel` as ch_id, a.`game_id` from orders as a inner join products as b on a.`product_id`=b.`id` where a.`game_user`=? and a.`status`=2 and b.`type`=1";
        $this->params = array($game_user);
        $this->doResult();
        return $this->result;
    }

    // 获取指定id的文章内容 <zbc>
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

    // 实时交易 <zbc>
    public function get_last_trades($num=1){
        $this->sql = "select b.`game_name`, a.`title`, a.`pay_money` from orders as a left join game as b on b.`id` = a.`game_id` where a.`platform`=0 and a.`is_del`=0 and a.`status`=2 order by a.`ship_time` desc limit ".intval($num);
        $this->doResultList();
        $trades = $this->result;
        return $trades;
    }

    // 混合 实时交易 <zbc>
    public function get_mix_trades(){
        $trades = memcache_get($this->mmc, 'mix_trades');
        if(!$trades){
            $real = array();
//            $real = $this->get_last_trades(5);
            $this->sql = "select trades from setting where id=1";
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


    public function get_goods_info($id){
        $data = $this->mmc->get('goods_info_'.$id);
        if(!$data){
            $this->sql = "select a.*,g.game_name,v.serv_name,c.channel_name,c.platform from products a left join game g on a.game_id=g.id left join game_servs v on a.game_id=v.game_id and a.serv_id=v.id left join channels c on a.channel_id=c.id where a.id =?";
            $this->params = array($id);
            $this->doResult();
            $data = $this->result;
            $this->mmc->set('goods_info_'.$id,$data,1,600);
        }
        return $data;
    }

    public function update_goods($info){
        $this->sql = "update products set stock=? where id=?";
        $this->params = array($info['new_stock'],$info['id']);
        $this->doExecute();
        $this->mmc->delete('goods_info_'.$info['id']);
    }


    public function update_pub($goods_id){
        $this->sql = "update products set is_pub=0 where id=?";
        $this->params = array($goods_id);
        $this->doExecute();
        $this->mmc->delete('goods_info_'.$goods_id);
    }

}