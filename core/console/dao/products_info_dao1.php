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

    public function get_attach_gifts($game_id){
        $this->sql = "select * from game_gift_info where game_id=? and is_attach=1";
        $this->params = array($game_id);
        $this->doResultList();
        return $this->result;
    }

    public function get_products_list($params,$page){
        $this->limit_sql="select g.game_name,p.* from products as p inner join game as g on g.id=p.game_id  where 1=1";
        if($params['game_id'] && is_numeric($params['game_id'])){
            $this->limit_sql=$this->limit_sql." and p.game_id =".$params['game_id'];
        }
        if($params['type'] && is_numeric($params['type'])){
            $this->limit_sql=$this->limit_sql." and p.type =".$params['type'];
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
        if($params['sort']){
            $this->limit_sql=$this->limit_sql." and p.is_pub =".($params['sort']-1);
        }
        $this->limit_sql=$this->limit_sql." order by p.id desc";
        $this->doLimitResultList($page);
        return $this->result;
    }

    public function add_product($params){
        $this->sql="insert into products(`type`,title,sub_title,game_id,channel_id,serv_id,serv_name,stock,price,intro,user_id,add_time,end_time,is_pub)values(?,?,?,?,?,?,?,?,?,?,?,?,?,?)";
        $this->params=array($params['type'],$params['title'],$params['sub_title'],$params['game_id'],$params['channel_id'],$params['serv_id'],$params['serv_name'],$params['stock'],$params['price'],
            $params['intro'],"1",strtotime("now"),$params['end_time'],$params['is_pub']);
        $this->doInsert();
        $product_id = $this->LAST_INSERT_ID;

        $this->sql = "insert into product_discounts(product_id)values(?)";
        $this->params = array($product_id);
        $this->doExecute();
        return $product_id;
    }

    public function edit_product($params){
        $this->sql="update products set `type`=?,title=?,game_id=?,channel_id=?,serv_id=?,serv_name=?,price=?,intro=?,end_time=?,is_pub=?,
              attach_gift=?,sub_title=?,stock=? where id=?";
        $this->params=array($params['type'],$params['title'],$params['game_id'],$params['channel_id'],$params['serv_id'],$params['serv_name'],
            $params['price'],$params['intro'],$params['end_time'],$params['is_pub'],$params['attach_gift'],$params['sub_title'],$params['stock'],$params['id']);
        $this->doExecute();
    }

    public function update_game_service($field, $game_id){
        $this->sql = "update game set ".$field."=1 where id=?";
        $this->params = array($game_id);
        $this->doExecute();
    }

    public function add_product_img($product_id,$img_url){
        $this->sql="insert into product_imgs(product_id,img_url) value(?,?)";
        $this->params=array($product_id,$img_url);
        $this->doInsert();
    }

    public function get_product_imgs($product_id){
        $this->sql="select * from product_imgs where product_id=?";
        $this->params=array($product_id);
        $this->doResultList();
        return $this->result;
    }

    public function del_product_imgs($product_id){
        $this->sql="delete from product_imgs where product_id=?";
        $this->params=array($product_id);
        $this->doExecute();
    }

    public function get_product_channels($id){
        $this->sql = "select * from product_discounts where product_id=?";
        $this->params = array($id);
        $this->doResult();
        return $this->result;
    }

    public function get_channel_info($ch_id){
        $this->sql = "select * from channels where id=?";
        $this->params = array($ch_id);
        $this->doResult();
        return $this->result;
    }

    public function update_product_ch($product_id, $chs){
        $this->sql = "update product_discounts set product_id=?";
        $val = array();
        foreach($chs as $k=>$v){
            $this->sql.=",".$k."=?";
            $val[] = $v;
        }
        $this->sql.= " where product_id=?";
        $val[] = $product_id;
        array_unshift($val, $product_id);
        $this->params = $val;
        $this->doExecute();
    }

    public function get_special_products_list($params,$page){
        $this->limit_sql="select t.*,g.game_name from game g inner join (select s.*,p.title as ptitle from special_sells s inner join products p on s.product_id=p.id)t on g.id=t.game_id  where 1=1 ";
        if($params['game_id'] && is_numeric($params['game_id'])){
            $this->limit_sql=$this->limit_sql." and t.game_id =".$params['game_id'];
        }

        if($params['title']){
            $this->limit_sql=$this->limit_sql." and t.title like '%".$params['title']."%'";
        }

        if($params['product_id'] && is_numeric($params['product_id'])){
            $this->limit_sql=$this->limit_sql." and t.product_id =".$params['product_id'];
        }

        if($params['game_name']){
            $this->limit_sql=$this->limit_sql." and g.game_name like '%".$params['game_name']."%'";
        }

        $this->limit_sql=$this->limit_sql." order by t.id desc";
        $this->doLimitResultList($page);
        return $this->result;

    }

    public function get_products_bygame_id($game_id){
        $this->sql="select * from products where is_pub=1 and game_id=? order by id desc";
        $this->params=array($game_id);
        $this->doResultList();
        return $this->result;
    }

    public function do_special_products_save($params){
        $this->sql="insert into special_sells(product_id,game_id,end_time,is_del,img,title)values(?,?,?,?,?,?)";
        $this->params=array($params['product_id'],$params['game_id'],$params['end_time'],$params['is_del'],$params['img'],$params['title']);
        $this->doInsert();
    }

    public function get_special_products($id){
        $this->sql="select a.*,b.title as ptitle,c.game_name from special_sells as a inner join products as b on a.product_id=b.id
                inner join game as c on b.game_id=c.id where a.id=?";
        $this->params=array($id);
        $this->doResult();
        return $this->result;
    }

    public function do_special_products_edit($params){
        $this->sql="update special_sells set end_time=?,is_del=?,img=?,title=? where id=?";
        $this->params=array($params['end_time'],$params['is_del'],$params['img'],$params['title'],$params['id']);
        $this->doExecute();
    }

    public function batch_pub($is_pub="1",$ids){
        $this->sql="update products set is_pub=? where id in($ids)";
        $this->params=array($is_pub);
        $this->doExecute();
    }

    public function add_product_intro_img($params){
        $this->sql="insert into product_intro_imgs(game_id,`type`,img_url) value(?,?,?)";
        $this->params=array($params['game_id'],$params['type'],$params['img_url']);
        $this->doInsert();
    }

    public function get_product_intro_img($game_id,$params){
        $this->sql="select * from product_intro_imgs where game_id=?";
        if($params['type'] && is_numeric($params['type'])){
            $this->sql=$this->sql." and `type` =".$params['type'];
        }
        $this->sql=$this->sql." order by `type` desc";
        $this->params=array($game_id);
        $this->doResultList();
        return $this->result;
    }

    public function intro_img_count($game_id,$type){
        $this->sql="select count(1) as count from product_intro_imgs where game_id=? and `type`=?";
        $this->params=array($game_id,$type);
        $this->doResult();
        return $this->result['count'];
    }

    public function del_product_intro_img($id){
        $this->sql="delete from product_intro_imgs where id=?";
        $this->params=array($id);
        $this->doExecute();
    }

    public function get_game_ch_servs($game_id, $ch_id){
        $this->sql = "select * from game_servs where game_id=? and ch_".$ch_id."=1 order by id desc";
        $this->params = array($game_id);
        $this->doResultList();
        return $this->result;
    }
}