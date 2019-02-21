<?php
COMMON('dao');
class channels_discount_dao extends Dao {

    public function __construct() {
        parent::__construct();
    }

    //游戏列表
    public function get_game_list(){
        $this->sql="select * from game where is_del=0 and status=1";
        $this->doResultList();
        return $this->result;
    }

    //获取渠道列表
    public function get_channels_list(){
        $this->sql="select * from channels ";
        $this->doResultList();
        return $this->result;
    }


    public function get_channels_discount_list($params,$page){
        $this->limit_sql="select c.channel_name,cd.* from channels as c inner join (select g.game_name,ch.* from channels_discount ch inner join game as g on g.id=ch.game_id)cd on c.id=cd.channel_id  where 1=1";
        if($params['game_id'] && is_numeric($params['game_id'])){
            $this->limit_sql=$this->limit_sql." and cd.game_id =".$params['game_id'];
        }
        if($params['channel'] && is_numeric($params['channel'])){
            $this->limit_sql=$this->limit_sql." and cd.channel =".$params['channel'];
        }
        $this->limit_sql=$this->limit_sql." order by cd.id desc";
        $this->doLimitResultList($page);
        return $this->result;
    }

    public  function  get_channels_discount_info($params){
        $this->sql="select * from channels_discount where game_id=?  and channel_id=?";
        $this->params=array($params['game_id'],$params['channel_id']);
        $this->doResult();
        return $this->result;
    }

    public function get_channels_discount_byid($id){
        $this->sql="select * from channels_discount where id=?";
        $this->params=array($id);
        $this->doResult();
        return $this->result;
    }

    public function add_channels_discount($params){
        $this->sql="insert into channels_discount(game_id,channel_id,discount) values(?,?,?)";
        $this->params=array($params['game_id'],$params['channel_id'],$params['discount']);
        $this->doInsert();
    }

    //修改渠道信息
    public function update_channels_discount($params){
        $this->sql="update channels_discount set game_id=?,channel_id=?,discount=? where id=?";
        $this->params=array($params['game_id'],$params['channel_id'],$params['discount'],$params['id']);
        $this->doExecute();
    }


    public function test_channels_discount_list($page){
        $this->limit_sql="select * from channel_discount  where 1=1" ;
        $this->limit_sql=$this->limit_sql." order by id desc";
        $this->doLimitResultList($page);
        return $this->result;
    }
}