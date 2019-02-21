<?php
COMMON('dao');
class article_dao extends Dao{

    public function __construct(){
        parent::__construct();
        $this->mmc = new Memcache();
        $this->mmc->connect(MMCHOST, MMCPORT);
    }


    //获取模块列表
    public function get_parts_list(){
        $this->sql = "select * from parts  order by id desc";
        $this->doResultList();
        return $this->result;
    }

    public function get_articles_list($params, $page){
        $this->limit_sql = "select * from articles  where is_pub=1";
        if($params['part_id'] && is_numeric($params['part_id'])){
            $this->limit_sql = $this->limit_sql . " and part_id =" . $params['part_id'];
        }
        $this->limit_sql = $this->limit_sql . " order by id desc";
        $this->doLimitResultList($page);
        return $this->result;
    }

    public function get_article_info($id){
        $this->sql    = "select * from articles where is_pub=1 AND id=?";
        $this->params = array($id);
        $this->doResult();
        return $this->result;
    }

    public function get_game($id){
        $this->sql    = "select * from game where status=1 and is_del=0 and id=?";
        $this->params = array($id);
        $this->doResult();
        $games = $this->result;
        return $games;
    }

    public function get_channels(){
        $data = $this->mmc->get("channels");
        if(!$data){
            $this->sql    = "select * from channels";
            $this->params = array();
            $this->doResultList();
            $data = $this->result;
            $this->mmc->set("channels", $data, 1, 600);
        }
        return $data;
    }

    public function get_game_last_product($game_id, $type = 1){
        $data = $this->mmc->get("game_last_product" . $game_id . "-" . $type);
        if(!$data){
            $this->sql    = "select p.*,pd.* from products p
                    inner join product_discounts as pd on p.id=pd.product_id where p.is_pub=1 and p.game_id=? and p.type=? order by price asc limit 1";
            $this->params = array($game_id, $type);
            $this->doResult();
            $data = $this->result;
            $this->mmc->set("game_last_product" . $game_id . "-" . $type, $data, 1, 600);
        }
        return $data;
    }

    public function get_articles_by_type($type){
        $this->sql    = "select a.*,b.name from articles a left join parts b on a.part_id=b.id where a.is_pub=1 and a.part_id=? order by a.id desc";
        $this->params = array($type);
        $this->doResultList();
        return $this->result;
    }
    public function get_part_name($type){
        $this->sql    = "select `name` from parts where id=?";
        $this->params = array($type);
        $this->doResult();
        return $this->result;
    }

    public function get_more_articles_list($params){
        $this->sql = 'select * from articles  where is_pub=1';
        if($params['type']){
            $this->sql .= " and part_id = " . $params['type'];
        }else{
            $this->sql .= " and part_id in " .'(29,30,31,32,33,34)';
        }
        if($params['title']){
            $this->sql .= " and title like '%".$params['title']."%'";
        }
        $this->sql .= " order by id desc";
        $this->doResultList();
        return $this->result;
    }
}
?>