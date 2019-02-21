<?php
COMMON('dao');
class products_info_dao extends Dao {

    public function __construct() {
        parent::__construct();
        $this->mmc = new Memcache();
        $this->mmc->connect(MMCHOST, MMCPORT);
    }

    //游戏列表
    public function get_game_list(){
        $data = $this->mmc->get("game_list");
        if(!$data){
            $this->sql="select * from game where is_del=0 and status=1";
            $this->doResultList();
            $data = $this->result;
            $this->mmc->set("game_list", $data, 1 ,60);
        }
        return $data;
    }
    //区服列表
    public function get_game_servs_list($game_id){
        $data = $this->mmc->get("game_servs".$game_id);
        if(!$data) {
            $this->sql = "select * from game_servs where game_id=?";
            $this->params = array($game_id);
            $this->doResultList();
            $data = $this->result;
            $this->mmc->set("game_servs".$game_id, $data, 1 ,60);
        }
        return $data;
    }
    //获取渠道列表
    public function get_channels_list(){
        $this->sql="select * from channels ";
        $this->doResultList();
        return $this->result;
    }

    //商品信息
    public function get_product_info($id){
        $this->sql="select * from products where id =?";
        $this->params=array($id);
        $this->doResult();
        return $this->result;
    }


    public function get_products_list($params,$page){
        $this->limit_sql="select g.game_name,p.*,ch.channel_name,s.serv_name as server_name from products as p inner join game as g on g.id=p.game_id left join channels ch on p.channel_id=ch.id left join game_servs s on p.serv_id=s.id where p.is_pub !=4";
        if($params['game_id'] && is_numeric($params['game_id'])){
            $this->limit_sql=$this->limit_sql." and p.game_id =".$params['game_id'];
        }
        if($params['type'] && is_numeric($params['type'])){
            $this->limit_sql=$this->limit_sql." and p.type =".$params['type'];
        }
        if($params['is_pub']!=null){
            $this->limit_sql=$this->limit_sql." and p.is_pub =".$params['is_pub'];
        }
        if($params['serv_id'] && is_numeric($params['serv_id'])){
            $this->limit_sql=$this->limit_sql." and p.serv_id =".$params['serv_id'];
        }
        if($params['channel_id'] && is_numeric($params['channel_id'])){
            $this->limit_sql=$this->limit_sql." and p.channel_id =".$params['channel_id'];
        }
        if($params['title']){
            $this->limit_sql=$this->limit_sql." and p.title like '%".$params['title']."%'";
        }

        $this->limit_sql=$this->limit_sql." order by p.id desc";
        $this->doLimitResultList($page,50);
        return $this->result;
    }


    public function edit_product($params){
        $this->sql="update products set proportion=?,num=?,role_level=?,role_job=?,role_sex=?,`type`=?,title=?,game_id=?,channel_id=?,serv_id=?,serv_name=?,price=?,intro=?,end_time=?,is_pub=?,
              attach_gift=?,sub_title=?,stock=? where id=?";
        $this->params=array($params['proportion'],$params['num'],$params['role_level'],$params['role_job'],$params['role_sex'],$params['type'],$params['title'],$params['game_id'],$params['channel_id'],$params['serv_id'],$params['serv_name'],
            $params['price'],$params['intro'],$params['end_time'],$params['is_pub'],$params['attach_gift'],$params['sub_title'],$params['stock'],$params['id']);
        $this->doExecute();
    }


    public function get_all_data($params){
        $this->sql="select g.game_name,p.*,ch.channel_name,s.serv_name as server_name from products as p inner join game as g on g.id=p.game_id left join channels ch on p.channel_id=ch.id left join game_servs s on p.serv_id=s.id where p.is_pub !=4";
        if($params['game_id'] && is_numeric($params['game_id'])){
            $this->sql=$this->sql." and p.game_id =".$params['game_id'];
        }
        if($params['type'] && is_numeric($params['type'])){
            $this->sql=$this->sql." and p.type =".$params['type'];
        }
        if($params['is_pub']!=null){
            $this->sql=$this->sql." and p.is_pub =".$params['is_pub'];
        }
        if($params['serv_id'] && is_numeric($params['serv_id'])){
            $this->sql=$this->sql." and p.serv_id =".$params['serv_id'];
        }
        if($params['channel_id'] && is_numeric($params['channel_id'])){
            $this->sql=$this->sql." and p.channel_id =".$params['channel_id'];
        }
        if($params['title']){
            $this->sql=$this->sql." and p.title like '%".$params['title']."%'";
        }

        $this->sql=$this->sql." order by p.id desc";
        $this->doResultList();
        return $this->result;
    }

    public function update_products_status($params){
        $this->sql = "update products set is_pub=?,`desc`=? where id = ?";
        $this->params = array($params['status'],$params['desc'],$params['id']);
        $this->doExecute();
    }
}