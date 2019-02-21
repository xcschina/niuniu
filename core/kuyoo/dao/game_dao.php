<?php
COMMON('dao');

class game_dao extends Dao {

    public function __construct() {
        parent::__construct();
        $this->mmc = new Memcache();
        $this->mmc->connect(MMCHOST, MMCPORT);
    }

    public function get_game_list(){
        $this->sql="select * from game order by first_letter asc";
        $this->doResultList();
        return $this->result;
    }

    // used zbc
    public function get_game($id){
        $this->sql = "select * from game where status=1 and is_del=0 and id=?";
        $this->params = array($id);
        $this->doResult();
        $games = $this->result;
        return $games;
    }

    public function get_game_servs($id){
        $this->sql="select * from game_servs where game_id=?";
        $this->params=array($id);
        $this->doResultList();
        return $this->result;
    }

    public function get_products($game_id,$page,$params=array()){
        $this->limit_sql = "select * from products where is_pub=1 and game_id=?";

        if($params['sid'] && is_numeric($params['sid'])){
            $this->limit_sql=$this->limit_sql." and serv_id =".$params['sid'];
        }

        if($params['type'] && is_numeric($params['type'])){
            $this->limit_sql=$this->limit_sql." and type =".$params['type'];
        }

        if($params['channel_id'] && is_numeric($params['channel_id'])){
            $this->limit_sql=$this->limit_sql." and channel_id =".$params['channel_id'];
        }

        if($params['price']==30){
            $this->limit_sql.=" and price<=30";
        }elseif($params['price']==100){
            $this->limit_sql.=" and price>30 and price<=100";
        }elseif($params['price']==300){
            $this->limit_sql.=" and price>100 and price<=300";
        }elseif($params['price']==301){
            $this->limit_sql.=" and price>300";
        }

//        if($this->price_type[$params['price_type']][0]){
//            $this->limit_sql=$this->limit_sql.$this->price_type[$params['price_type']][0];
//        }

        if($params['sort'] == 'desc'){
            $this->limit_sql=$this->limit_sql." order by price desc";
        }elseif($params['sort'] == 'asc'){
            $this->limit_sql=$this->limit_sql." order by price asc";
        }else{
            $this->limit_sql=$this->limit_sql." order by price asc";
        }
        $this->params = array($game_id);
        $this->doLimitResultList($page);
        $product= $this->result;
        return $product;
    }

    public function get_product_discount($product_id){
        $this->sql = "select * from product_discounts where product_id=?";
        $this->params = array($product_id);
        $this->doResult();
        return $this->result;
    }

    // --------------------------
    // v2 <zbc>
    // --------------------------

    // 单个游戏的各个渠道客户端下载列表 百度多酷客户端置顶
    public function get_game_ch_download($gid){
        $this->mmc->flush(); // test
        $data = memcache_get($this->mmc,"game_downs".$gid);
        if(!$data){
            $this->sql = "select a.*,b.icon,b.platform,b.channel_name from game_downloads as a INNER  JOIN channels as b on a.channel_id=b.id where a.is_del=0 and a.game_id=? order by b.id=6 desc";
            $this->params = array($gid);
            $this->doResultList();
            $data = $this->result;
            memcache_set($this->mmc,"game_downs".$gid, $data,1, 600);
        }
        return $data;
    }

    // 指定游戏指定渠道的下载链接id 获得 下载链接记录信息
    public function get_game_down_info($down_id){
        $data = memcache_get($this->mmc, "game_down_".$down_id);
        if(!$data){
            $this->sql = "select * from game_downloads where id=?";
            $this->params = array($down_id);
            $this->doResultList();
            $data = $this->result;
            memcache_set($this->mmc,"game_down_".$down_id, $data, 1, 120);
        }
        return $data;
    }



}