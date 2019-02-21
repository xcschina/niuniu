<?php
COMMON('dao');
class game_dao extends Dao {

    public function __construct() {
        parent::__construct();
        $this->mmc = new Memcache();
        $this->mmc->connect(MMCHOST, MMCPORT);
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

    // 游戏资讯列表
    public function get_articles_list($game_id){
        $data = memcache_get($this->mmc,'game_link_articles'.$game_id);
        if(!$data){
            $this->sql="select * from articles where is_pub=1 and game_id=? order by lastupdate desc limit 10";
            $this->params = array($game_id);
            $this->doResultList();
            $data = $this->result;
            $this->mmc->set('game_link_articles'.$game_id, $data, 1, 600);
        }
        return $data;
    }

    public function get_game_gifts($game_id){
        $data = memcache_get($this->mmc,"game_gifts".$game_id);
        if(!$data){
            $this->sql = "select a.*,b.game_name,b.game_icon from game_gift_info as a INNER JOIN game as b on a.game_id=b.id
                where a.game_id=? and a.is_del=0 and a.remain>0 and a.end_time>unix_timestamp(now()) order by a.id desc limit 10";
            $this->params = array($game_id);
            $this->doResultList();
            $data = $this->result;
            memcache_set($this->mmc, "game_gifts".$game_id, $data, 1, 600);
        }
        return $data;
    }

    public function get_game_list($params){
        $this->sql="select * from game where 1=1";
        if($params['first_letter']){
            $this->sql=$this->sql." and first_letter='".$params['first_letter']."'";
        }else{
            $this->sql=$this->sql." and is_hot=1";
        }

        $this->doResultList();
        return $this->result;
    }

    public function get_game($id){
        $this->sql = "select * from game where status=1 and is_del=0 and id=?";
        $this->params = array($id);
        $this->doResult();
        $games = $this->result;
        return $games;
    }

    public function get_game_downs($id){
        $data = memcache_get($this->mmc,"game_downs".$id);
        if(!$data){
            $this->sql = "select a.*,b.icon from game_downloads as a INNER  JOIN channels as b on a.channel_id=b.id
                where a.is_del=0 and a.game_id=? order by b.id=6 desc";
            $this->params = array($id);
            $this->doResultList();
            $data = $this->result;
            memcache_set($this->mmc,"game_downs".$id, $data,1, 600);
        }
        return $data;
    }

    public function get_game_servs($id){
        $this->sql="select * from game_servs where game_id=?";
        $this->params=array($id);
        $this->doResultList();
        return $this->result;
    }

    public function get_game_discount($game_id){
        $this->sql="select * from channel_discount where game_id=?";
        $this->params=array($game_id);
        $this->doResult();
        return $this->result;
    }

    public function get_products($game_id,$page,$params=array()){
        $this->limit_sql = "select * from products where is_pub=1 and game_id=?";
        if($params['type'] && is_numeric($params['type'])){
            $this->limit_sql=$this->limit_sql." and type =".$params['type'];
        }
        $this->limit_sql .= " order by price asc";
        $this->params = array($game_id);

        $this->_setLimit($page);
        $this->sql = $this->limit_sql . " limit " . $this->limit . "," . PERPAGE;
        $this->doResultList();
        $product= $this->result;
        return $product;
    }

    public function get_other_products($game_id, $page, $params = array()){
        $this->limit_sql = "select a.*,b.serv_name,c.channel_name,c.icon from products as a left join game_servs as b on a.serv_id=b.id
              left join channels as c on a.channel_id=c.id where a.is_pub=1 and a.game_id=?";
        if($params['type'] && is_numeric($params['type'])){
            $this->limit_sql = $this->limit_sql." and a.type =".$params['type'];
        }
        if($params['serv_id'] && is_numeric($params['serv_id'])){
            $this->limit_sql = $this->limit_sql." and (a.serv_id =".$params['serv_id']." or a.serv_id=0)";
        }
        if($params['ch_id'] && is_numeric($params['ch_id'])){
            $this->limit_sql = $this->limit_sql." and a.channel_id =".$params['ch_id'];
        }

        $this->limit_sql .= " order by a.user_id asc";
        if($params['price']=='desc'){
            $this->limit_sql .= ",a.price desc";
        }else{
            $this->limit_sql .=",a.price asc";
        }
        $this->params = array($game_id);

        $this->_setLimit($page);
        $this->sql = $this->limit_sql . " limit " . $this->limit . "," . PERPAGE;
        $this->doResultList();
        $product = $this->result;
        return $product;
    }

    public function get_product_discount($product_id){
        $this->sql = "select * from product_discounts where product_id=?";
        $this->params = array($product_id);
        $this->doResult();
        return $this->result;
    }

    public function get_user_gift_batch($user_id){
        $this->sql = "select batch_id from weixin_gifts where user_id=? group by batch_id";
        $this->params = array($user_id);
        $this->doResultList();
        return $this->result;
    }

    public function get_game_last_product($game_id, $type=1){
        $data = $this->mmc->get("game_last_product".$game_id."-".$type);
        if(!$data){
            $this->sql = "select p.*,pd.* from products p
                    inner join product_discounts as pd on p.id=pd.product_id where p.is_pub=1 and p.game_id=? and p.type=? order by price asc limit 1";
            $this->params = array($game_id, $type);
            $this->doResult();
            $data = $this->result;
            $this->mmc->set("game_last_product".$game_id."-".$type, $data, 1 ,600);
        }
        return $data;
    }

    public function get_game_iap_last_products($game_id){
        $data = $this->mmc->get("game_iap_last_product".$game_id);
        if(!$data){
            $this->sql = "select p.* from products as p inner join 7881_games as b on p.coop_game_id=b.id
                          where p.is_pub=1 and b.sys_game_id=? order by price asc limit 1";
            $this->params = array($game_id);
            $this->doResult();
            $data = $this->result;
            $this->mmc->set("game_iap_last_product".$game_id, $data, 1 ,600);
        }
        return $data;
    }

    public function get_channels(){
        $data = $this->mmc->get("channels");
        if(!$data){
            $this->sql = "select * from channels";
            $this->params = array();
            $this->doResultList();
            $data = $this->result;
            $this->mmc->set("channels", $data, 1 ,600);
        }
        return $data;
    }

    public function get_game_ch_servs($game_id, $ch_id){
        $data = $this->mmc->get("game_ch_servs".$game_id."-".$ch_id);
        if(!$data){
            if(!empty($ch_id)){
                $this->sql = "select id,serv_name from game_servs where game_id=? and ch_".$ch_id."=1 order by id asc";
            }else{
                $this->sql = "select id,serv_name from game_servs where game_id=? order by id asc";
            }
            $this->params = array($game_id);
            $this->doResultList();
            $data = $this->result;
            $this->mmc->set("game_ch_servs".$game_id."-".$ch_id, $data, 1 ,600);
        }
        return $data;
    }

    public function get_game_iap_servs($game_id, $group_id){
        $data = $this->mmc->get("game_iap_servs".$game_id."_".$group_id);
        if(!$data){
            $this->sql = "select a.* from 7881_game_servs as a inner join 7881_games as b on a.game_id=b.game_id
                      where b.sys_game_id=? and a.group_id=? order by a.serv_id desc";
            $this->params = array($game_id, $group_id);
            $this->doResultList();
            $data = $this->result;
            $this->mmc->set("game_iap_servs".$game_id."_".$group_id, $data, 1 ,600);
        }
        return $data;
    }

    public function get_all_products($game_id, $type=1){
        $data = $this->mmc->get("game_product_all".$game_id."-".$type);
        if(!$data){
            $this->sql = "select * from products where is_pub=1 and game_id=? and type=? order by price asc";
            $this->params = array($game_id, $type);
            $this->doResultList();
            $data = $this->result;
            $this->mmc->set("game_product_all".$game_id."-".$type, $data, 1, 600);
        }
        return $data;
    }

    public function get_game_user($game_id, $user_id){
        $this->sql = "select a.id,a.game_user,a.role_name,a.serv_id,a.game_channel as ch_id,c.serv_name from orders as a inner join products as b on a.product_id=b.id
                  inner join game_servs as c on a.serv_id=c.id where a.buyer_id=? and b.type=1 and a.status=2 and a.game_id=?";
        $this->params = array($user_id, $game_id);
        $this->doResultList();
        return $this->result;
    }

    public function check_game_user($game_id, $game_user){
        $this->sql = "select a.id,a.game_user,a.role_name,a.serv_id,a.game_channel as ch_id,c.serv_name from orders as a
                    inner join products as b on a.product_id=b.id inner join game_servs as c on a.serv_id=c.id
                    where a.game_user=? and b.type=1 and a.status=2 and a.game_id=?";
        $this->params = array($game_user, $game_id);
        $this->doResult();
        return $this->result;
    }

    public function get_channel_info($id){
        $data = $this->mmc->get("channelinfo".$id);
        if(!$data){
            $this->sql = "select * from channels where id=?";
            $this->params = array($id);
            $this->doResult();
            $data = $this->result;
            $this->mmc->set("channelinfo".$id, $data, 1 ,600);
        }
        return $data;
    }

    public function get_game_user_by_id($id){
        $this->sql = "select a.id,a.game_user,a.role_name,a.serv_id,a.game_channel as ch_id,c.serv_name from orders as a inner join products as b on a.product_id=b.id
                  inner join game_servs as c on a.serv_id=c.id where b.type=1 and a.status=2 and a.id=?";
        $this->params = array($id);
        $this->doResult();
        return $this->result;
    }

    public function get_serv_info($id){
        $data = $this->mmc->get("servinfo".$id);
        if(!$data){
            $this->sql = "select * from game_servs where id=?";
            $this->params = array($id);
            $this->doResult();
            $data = $this->result;
            $this->mmc->set("servinfo".$id, $data, 1 ,600);
        }
        return $data;
    }

    public function get_game_thumbs($game_id){
        $data = $this->mmc->delete("game_thumbs".$game_id);
        $data = $this->mmc->get("game_thumbs".$game_id);
        if(!$data){
            $this->sql = "select * from product_intro_imgs where game_id=? and type=99";
            $this->params = array($game_id);
            $this->doResultList();
            $data = $this->result;
            $this->mmc->set("game_thumbs".$game_id, $data, 1 ,600);
        }
        return $data;
    }

    public function get_user_info($user_id){
        $data = $this->mmc->get("seller_info".$user_id);
        if(!$data){
            $this->sql = "select * from user_info where user_id=?";
            $this->params = array($user_id);
            $this->doResult();
            $data = $this->result;
            $this->mmc->set("seller_info".$user_id, $data, 1 ,600);
        }
        return $data;
    }

    public function get_iap_game($game_id){
        $data = $this->mmc->get("iap_game_".$game_id);
        if(!$data){
            $this->sql = "select * from 7881_games where id=?";
            $this->params = array($game_id);
            $this->doResult();
            $data = $this->result;
            $this->mmc->set("iap_game_".$game_id, $data, 1,3600);
        }
        return $data;
    }

    public function get_iap_game_groups($game_id){
        $data = $this->mmc->get("iap_game_groups".$game_id);
        if(!$data){
            $this->sql = "select a.* from 7881_game_groups as a inner join 7881_games as b on a.game_id=b.game_id where b.sys_game_id=? order by a.group_id";
            $this->params = array($game_id);
            $this->doResultList();
            $data = $this->result;
            $this->mmc->set("iap_game_groups".$game_id, $data, 1,3600);
        }
        return $data;
    }

    public function update_iap_game($iap_game_id){
        $this->sql = "update 7881_games set serv_fresh_time=? where id=?";
        $this->params = array(strtotime("now"), $iap_game_id);
        $this->doExecute();
    }

    public function check_iap_game_servs($serv_id, $group_id){
        $data = $this->mmc->get(md5("iap_game_servs".$serv_id."_".$group_id));
        if(!$data){
            $this->sql = "select * from 7881_game_servs where serv_id=? and group_id=?";
            $this->params = array($serv_id, $group_id);
            $this->doResult();
            $data = $this->result;
            $this->mmc->set(md5("iap_game_servs".$serv_id."_".$group_id), $data, 1,3600);
        }
        return $data;
    }

    public function update_iap_game_serv($id, $serv_id, $serv_name){
        $this->sql = "update 7881_game_servs set serv_id=?,serv_name=? where id=?";
        $this->params = array($serv_id, $serv_name, $id);
        $this->doExecute();
    }

    public function insert_iap_game_serv($serv_id, $serv_name, $game_id, $group_id){
        $this->sql = "insert into 7881_game_servs(serv_id, serv_name, game_id, group_id)values(?,?,?,?)";
        $this->params = array($serv_id, $serv_name, $game_id, $group_id);
        $this->doExecute();
    }

    public function get_user_agent_game($user_id, $game_id){
        $data = $this->mmc->get("user_agent_games".$user_id."-".$game_id);
        if(!$data){
            $this->sql = "select * from agents where game_id=? and user_id=?";
            $this->params = array($game_id, $user_id);
            $this->doResult();
            $data = $this->result;
            $this->mmc->set("user_agent_games".$user_id."-".$game_id, $data, 1,600);
        }
        return $data;
    }

    public function get_game_articles($game_id){
        $data = $this->mmc->get("game_articles".$game_id);
        if(!$data){
            $this->sql = "select * from articles where game_id=? order by id desc limit 20";
            $this->params = array($game_id);
            $this->doResultList();
            $data = $this->result;
            $this->mmc->set("game_articles".$game_id, $data, 1,600);
        }
        return $data;
    }

    public function get_channel_app_info($app_id){
        $data = $this->mmc->get("channel_app_info_".$app_id);
        if(!$data){
            $this->sql = "select * from  channel_apps where app_id=?";
            $this->params = array($app_id);
            $this->doResult();
            $data = $this->result;
            $this->mmc->set("channel_app_info_".$app_id, $data, 1 ,600);
        }
        return $data;
    }
}