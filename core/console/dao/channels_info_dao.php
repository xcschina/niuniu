<?php
COMMON('dao');
class channels_info_dao extends Dao {

    public function __construct() {
        parent::__construct();
    }

    //获取游戏列表
    public function get_channels_info_list($params,$page){
        $this->limit_sql="select * from channels where 1=1";
        if($params['channel_name']){
            $this->limit_sql=$this->limit_sql." and channel_name like '%".$params['channel_name']."%'";
        }
        $this->limit_sql=$this->limit_sql." order by id desc";
        $this->doLimitResultList($page);
        return $this->result;
    }

    //获取游戏信息
    public  function  get_channel_info($params){
        $this->sql="select * from channels where channel_name=?";
        $this->params=array($params['channel_name']);
        $this->doResult();
        return $this->result;
    }

    public function get_channel_info_byid($id){
        $this->sql="select * from channels where id=?";
        $this->params=array($id);
        $this->doResult();
        return $this->result;
    }

    public function get_game_list(){
        $this->sql = "select * from game where is_del = 0 order by id desc";
        $this->doResultList();
        return $this->result;
    }

    //添加渠道信息
    public function add_channel($params){
        $this->sql="insert into channels(channel_name,icon,apply,game_id) values(?,?,?,?)";
        $this->params=array($params['channel_name'],$params['icon'],$params['apply'],$params['game_id']);
        $this->doInsert();
        //商品折扣表
        $ch_id = $this->LAST_INSERT_ID;
        $this->sql = "ALTER TABLE `product_discounts` ADD `ch_".$ch_id."` FLOAT(3) NOT NULL DEFAULT '0', ADD `chd_".$ch_id."` FLOAT(3) NOT NULL DEFAULT '0'";
        $this->doExecute();
        //游戏折扣表
        $this->sql = "ALTER TABLE `channel_discount` ADD `ch_".$ch_id."_1` FLOAT NOT NULL DEFAULT '0', ADD `ch_".$ch_id."_2` FLOAT NOT NULL DEFAULT '0', ";
        $this->sql.="ADD `ch_".$ch_id."_3` FLOAT NOT NULL DEFAULT '0';";
        $this->doExecute();
        //渠道区服
        $this->sql = "ALTER TABLE `game_servs` ADD `ch_".$ch_id."` SMALLINT(1) NULL DEFAULT '0'";
        $this->doExecute();
        //店铺游戏
        $this->sql = "ALTER TABLE `shop_game` ADD `ch_".$ch_id."_1` tinyint(1) unsigned NOT NULL DEFAULT '0', ADD `ch_".$ch_id."_2` tinyint(1) unsigned NOT NULL DEFAULT '0'";
        $this->doExecute();
        //店铺商品
        $this->sql = "ALTER TABLE `shop_product` ADD `ch_".$ch_id."` decimal(3,1) NOT NULL DEFAULT '0.0'";
        $this->doExecute();
    }

    //修改渠道信息
    public function update_channel($params){
        $this->sql="update channels set channel_name=?,icon=?,platform=?,apply=?,game_id=? where id=?";
        $this->params=array($params['channel_name'],$params['icon'],$params['platform'],$params['apply'],$params['game_id'],$params['id']);
        $this->doExecute();
    }
}