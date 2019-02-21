<?php
COMMON('dao');
class announcement_dao extends Dao{

    public function __construct(){
        parent::__construct();
        $this->mmc = new Memcache();
        $this->mmc->connect(MMCHOST, MMCPORT);
    }

    // 指定条件 - 魔域商品列表
    public function get_moyu_products($game_id, $page=1, $pagesize=6, $params=array('platform'=>1)){
        $this->limit_sql = 'select g.serv_id as sid,g.serv_name as sname,c.channel_name,c.platform,c.icon,p.* ,ga.game_name from products as p left join game as ga on ga.id=p.game_id left join channels as c on c.id=p.channel_id left join game_servs as g on g.id=p.serv_id where 1=1 and p.is_pub=1 and p.type!=2 
 and p.type!=3 and p.type!=7 and p.type!=8 and p.game_id=?';
        if(count($params)>1){
            if(intval($params['platform'])){
                $this->limit_sql .= ' and c.platform='.(int)$params['platform'];
            }
            if(intval($params['ch_id'])){
                $this->limit_sql .= ' and p.channel_id='.(int)$params['ch_id'];
            }
            if(intval($params['serv_id'])){
                $this->limit_sql .= ' and p.serv_id='.(int)$params['serv_id'];
            }
            if(intval($params['pro_id'])){
                $this->limit_sql .= ' and p.id='.(int)$params['pro_id'];
            }
            if(intval($params['type'])){
                $this->limit_sql .= ' and p.type='.(int)$params['type'];
            }
            if(in_array(trim($params['price_order']), array('asc','desc'))){
                $this->limit_sql .= ' order by p.price '.$params['price_order'];
            }else{
                $this->limit_sql .= ' order by p.price desc';
            }
            if($params['game_name']){
                $this->limit_sql .= " and ga.game_game like '%".$params['game_game']."%'";
            }
            if($params['title']){
                $this->limit_sql .= " and p.title like '%".$params['title']."%'";
            }
        }
        $this->params = array($game_id);
        $this->doLimitResultList($page,$pagesize);
        return $this->result;
    }



    //获取模块列表
    public function get_parts_list(){
        $this->sql="select * from parts  order by id desc";
        $this->doResultList();
        return $this->result;
    }

    //获取公告或者防骗
    public function get_moyu_articles_list(){
        $this->sql="select * from articles  where is_pub=1 and part_id=25 or part_id=26 order by id desc limit 3 ";
        $this->doResultList();
        return $this->result;
    }
    //获取banner
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
    //获取公告详情页
    public function get_article_info($id){
        $this->sql="select * from articles where is_pub=1 AND id=?";
        $this->params=array($id);
        $this->doResult();
        return $this->result;
    }


    public function get_game($id){
        $this->sql = "select * from game where status=1 and is_del=0 and id=?";
        $this->params = array($id);
        $this->doResult();
        $games = $this->result;
        return $games;
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




}
?>