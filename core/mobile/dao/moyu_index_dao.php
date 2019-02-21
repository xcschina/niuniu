<?php
COMMON('dao');

class moyu_index_dao extends Dao{

    public function __construct(){
        parent::__construct();
        $this->mmc = new Memcache();
        $this->mmc->connect(MMCHOST, MMCPORT);
    }

    //获取公告或者防骗
    public function get_moyu_articles_list(){
        $this->sql = "select a.* from articles a left join game g on a.game_id=g.id  where a.is_pub=1 and a.part_id=28 and g.apply=1 order by a.id desc limit 3";
        $this->doResultList();
        return $this->result;
    }

    //获取banner
    public function get_banners(){
        $this->sql = "select * from articles where is_pub=1 and part_id=28 order by lastupdate desc,id desc limit 5";
        $this->doResultList();
        $banners = $this->result;
        return $banners;
    }

    //友情链接
    public function get_friendly_links(){
        $friendly_links = memcache_get($this->mmc, 'friendly_links');
        if(!$friendly_links){
            $this->sql = "select * from friendly_links";
            $this->doResultList();
            $friendly_links = $this->result;
            memcache_set($this->mmc, 'friendly_links', $friendly_links, 1, 600);
        }
        return $friendly_links;
    }

    //获取游戏列表
    public function get_game_list(){
        $this->sql = "select * from game where apply=1 and is_del=0 order by is_hot desc , id desc";
        $this->doResultList();
        return $this->result;
    }

    public function get_wx_access_token(){
        $data = memcache_get($this->mmc, 'wx_moyu_access_token');
        return $data;
    }

    public function set_wx_access_token($data){
        memcache_set($this->mmc, "wx_moyu_access_token", $data, 1, 7200);
    }

    public function get_wx_access_jsapi_data($token){
        $data = memcache_get($this->mmc, 'jsapi_moyu_data_'.$token);
        return $data;
    }

    public function set_wx_access_jsapi_data($token,$data){
        memcache_set($this->mmc, 'jsapi_moyu_data_'.$token, $data, 1, 7200);
    }
    public function get_moyu_products($params){
        $this->sql = 'select g.id as server_id, g.serv_id as sid,g.serv_name as sname,c.channel_name,c.platform,c.icon,p.* ,ga.game_name,ga.game_icon from products as p left join game as ga on ga.id=p.game_id left join channels as c on c.id=p.channel_id left join game_servs as g on g.id=p.serv_id where p.is_pub=1 ';
        $this->sql .= ' and p.game_id=' . (int)$params['game_id'];

        if(intval($params['platform'])){
            $this->sql .= ' and c.platform=' . (int)$params['platform'];
        }
        if(intval($params['ch_id'])){
            $this->sql .= ' and p.channel_id=' . (int)$params['ch_id'];
        }
        if(intval($params['serv_id'])){
            $this->sql .= ' and p.serv_id=' . (int)$params['serv_id'];
        }

        if(intval($params['pro_id'])){
            $this->sql .= ' and p.id=' . (int)$params['pro_id'];
        }
        if(intval($params['type'])){
            $this->sql .= ' and p.type=' . (int)$params['type'];
        }
        if($params['title']){
            $this->sql .= " and p.num like '%" . $params['title'] . "%'";
        }
        if(intval($params['price_pre'])){
            $this->sql .= " and p.price >= " . intval($params['price_pre']);
        }
        if(intval($params['price_aft'])){
            $this->sql .= " and p.price <= " . intval($params['price_aft']);

        }
        if(trim($params['price_order'])!=null){
            $this->sql .= ' order by p.price ' . $params['price_order'].',p.add_time desc';
        }
        if($params['price_order'] ==null){
            $this->sql .= " order by p.add_time desc";
        }
        $this->doResultList();
        return $this->result;
    }


}