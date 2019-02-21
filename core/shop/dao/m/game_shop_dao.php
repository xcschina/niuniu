<?php
// --------------------------------------
//  店铺系统 <zbc> < 2016/4/14 >
// --------------------------------------

COMMON('dao');
class game_shop_dao extends Dao {

    public function __construct() {
        parent::__construct();
        $this->mmc = new Memcache();
        $this->mmc->connect(MMCHOST, MMCPORT);
    }

    // 店铺游戏列表
    public function get_shop_game_list($params=array()){
        $select = 'g.id, g.game_name, g.game_icon, g.first_letter, sg.*';
        $sql = 'select '.$select.' FROM shop_game AS sg LEFT JOIN game AS g ON sg.game_id=g.id WHERE 1=1 and sg.sg_status=1 AND sg.s_id=? AND g.status=1 AND g.is_del=0';
        isset($params['is_hot']) && $sql .= ' and sg.is_hot=1 order by sg.sg_sort desc';
        isset($params['limit'])  && $sql .= ' limit '.$params['limit']; // '2,1' 或者 '1' 
        $this->sql = $sql;
        $this->params = array($params['shop_id']);
        $this->doResultList();
        return $this->result;
    }

    // 店铺某款游戏信息 [$moreinfo=1 详细信息]
    public function get_shop_game_info($params=array(), $moreinfo=0){
        if($moreinfo){
            $select = 'g.id, g.game_name, g.game_icon, g.first_letter, g.tags,sg.*';
            $this->sql = "select ".$select." from shop_game as sg left join game as g on g.id=sg.game_id where sg.s_id=? and sg.game_id=? and sg.sg_status=1 and g.status=1 and g.is_del=0";
        }else{
            $this->sql = 'select * from shop_game where 1=1 and sg_status=1 and s_id=? and game_id=?';
        }
        $this->params = array($params['shop_id'], $params['game_id']);
        $this->doResult();
        return $this->result;
    }

    // 店铺游戏查询 - 首字母查询
    public function shop_letter_games($params=array()){
        $mmc_name = 'shop_letter_games_'.$params['shop_id'].'_'.$params['letter'];
        $games = memcache_get($this->mmc, $mmc_name);
        if(!$games){
            $select = 'sg.s_id as shop_id, sg.game_id, g.game_icon, g.game_name';
            $this->sql = 'select '.$select.' from shop_game as sg inner join game as g on sg.game_id=g.id where 1=1 and sg.s_id=? and sg.sg_status=1 and g.first_letter =? and g.status=1 and g.is_del=0 order by sg.sg_sort desc,g.create_time desc';
            $this->params = array($params['shop_id'], strtoupper($params['letter']));
            $this->doResultList();
            $games = $this->result;
            memcache_set($this->mmc, $mmc_name, $games, 1, 600);
        }
        return $games;
    }

    // 店铺游戏查询 - 模糊搜索
    public function shop_keyword_games($params=array()){
        $mmc_name = 'shop_keyword_games_'.$params['shop_id'].'_'.md5($params['keyword']);
        $games = memcache_get($this->mmc, $mmc_name);
        if(!$games){
            $this->sql = "select sg.s_id as shop_id, sg.game_id, g.game_name from shop_game as sg left join game as g on g.id=sg.game_id where 1=1 and sg.s_id=? and sg.sg_status=1 and (g.game_name like ? or g.en_name like ?) and g.status=1 and g.is_del=0 order by sg.sg_sort desc,g.create_time desc";
            $this->params = array($params['shop_id'],$params['keyword'].'%',$params['keyword'].'%');
            $this->doResultList();
            $games = $this->result;
            memcache_set($this->mmc, $mmc_name, $games, 1, 600);
        }
        return $games;
    }


    // 首充号查询 // test - 13403328856
    public function shop_check_character($params=array()){
        $data = memcache_get($this->mmc,'shop_check_character_'.$params['character']);
        if(!$data){
            $this->sql = "select o.shop_id, o.id, o.game_user, o.role_name, o.serv_id, o.game_channel AS ch_id, o.game_id, g.serv_name FROM orders AS o INNER JOIN products AS p ON o.product_id=p.id left join game_servs as g on g.id=o.serv_id WHERE 1=1 and o.game_user=? AND o.status=2 AND p.type=1";
            $this->params = array($params['character']);
            $this->doResult();
            $data = $this->result;
            $this->mmc->set('shop_check_character_'.$params['params'], $data, 1, 600);
        }
        return $data;
    }


    // --------------------
    // 主站66173信息区
    // --------------------

    // 66用户已购买的首充号列表
    public function get_master_user_characters($params=array()){
        $select = 'o.id,o.game_user,o.role_name,o.serv_id,o.game_channel as ch_id,c.serv_name,o.game_id,o.shop_id';
        $this->sql = "select ".$select." from orders as o inner join products as p on o.product_id=p.id inner join game_servs as c on o.serv_id=c.id where 1=1 and o.buyer_id=? and p.type=1 and o.status=2 and o.game_id=?";
        if($params['shop_id']){
            $this->sql .= ' and o.shop_id in(0,'.$params['shop_id'].')';
        }
        if($params['serv_id']){
            $this->sql .= " and o.serv_id='".$params['serv_id']."'";
        }
        if($params['ch_id']){
            $this->sql .= " and o.game_channel='".$params['ch_id']."'";
        }
        $this->params = array($params['user_id'], $params['game_id']);
        $this->doResultList();
        return $this->result;
    }

    // 主站游戏渠道区服列表
    public function get_master_game_ch_servs($params=array()){
        $data = $this->mmc->get("get_master_game_ch_servs".$params['game_id']."-".$params['ch_id']);
        if(!$data){
            if($params['ch_id']){
                $this->sql = "select id,serv_name from game_servs where game_id=? and ch_".$params['ch_id']."=1 order by id desc";
            }else{
                $this->sql = "select id,serv_name from game_servs where game_id=? order by id desc";
            }
            $this->params = array($params['game_id']);
            $this->doResultList();
            $data = $this->result;
            $this->mmc->set("get_master_game_ch_servs".$params['game_id']."-".$params['ch_id'], $data, 1 ,600);
        }
        return $data;
    }

    // 获取主站指定区服的信息
    public function get_serv_info($id){
        $this->sql="select * from game_servs where id=?";
        $this->params=array($id);
        $this->doResult();
        return $this->result;
    }

}