<?php
COMMON('dao');

class moyu_product_dao extends Dao{

    public function __construct(){
        parent::__construct();
        $this->mmc = new Memcache();
        $this->mmc->connect(MMCHOST, MMCPORT);
        $this->price_type = array(
            0 => array("", "不限"),
            1 => array(" and price<30", "30元以下"),
            2 => array(" and price<100 and price>=30", "30--100元"),
            3 => array(' and price<300 and price>=100', "100-300元"),
            4 => array(" and price>=300", "300元以上")
        );
    }

    public function get_channels(){
        $this->sql    = "select * from channels";
        $this->params = array();
        $this->doResultList();
        return $this->result;
    }

    public function get_game_name($id){
        $this->sql = "select game_name from game where id=?";
        $this->params    = array($id);
        $this->doResult();
        return $this->result;
    }


    // 指定条件 - 魔域商品列表
    public function get_product_list($game_id,$user_id){
        $this->sql = 'select g.id as server_id,g.serv_id as sid,g.serv_name as sname,c.channel_name,c.platform,c.icon,p.* ,ga.game_name,ga.game_icon from products as p left join game as ga on ga.id=p.game_id left join channels as c on c.id=p.channel_id left join game_servs as g on g.id=p.serv_id where p.is_pub=1 and p.type=5 
        and p.game_id=? and p.user_id!=?';
        $this->params    = array($game_id,$user_id);
        $this->sql .= "order by p.add_time desc";
        $this->doResultList();
        return $this->result;
    }

    //获取产品的具体信息
    public function get_product_info($product_id){
        $this->sql    = "select c.channel_name,c.platform,p.*,se.serv_name as sname,g.game_name,g.tags,g.product_img,pd.*,i.img_url from products p
        inner join game g on p.game_id=g.id left join product_discounts as pd on p.id=pd.product_id left join game_servs as se on se.id=p.serv_id left join channels as c on c.id=p.channel_id left join product_imgs i on p.id=i.product_id where p.id=?";
        $this->params = array($product_id);
        $this->doResult();
        return $this->result;
    }

    //更新收藏用户id
    public function update_product_collect($collect, $id){
        $this->sql    = "update products set collect=? where id=?";
        $this->params = array($collect, $id);
        $this->doExecute();
    }

    //获取用户收藏的id
    public function get_product_collect($product_id){
        $this->sql    = "select collect from products where id=? ";
        $this->params = array($product_id);
        $this->doResult();
        return $this->result;
    }

    //获取渠道
    public function get_channel($platform=''){
        $this->sql = "select * from channels where apply=1";

        if($platform){
            $this->sql .= " and platform = ".$platform;
        }
        $this->doResultList();
        return $this->result;
    }


    //获取指定
    public function get_specified_ch_servs($ch_id,$serv_name,$game_id){
        $this->sql    = "select * from game_servs where ch_".$ch_id."=1 and game_id=?";
        if($serv_name){
            $this->sql    .= " and serv_name like '%" . $serv_name . "%'";
        }
        $this->sql    .= " order by id desc";
        $this->params = array($game_id);
        $this->doResultList();
        $data = $this->result;
        return $data;

    }

    //获取服务器
    public function get_ch_game_servs($ch_id,$game_id,$serv_name){
        $this->sql    = "select * from game_servs where ch_".$ch_id."=1 and game_id=? order by id desc";
        if($serv_name){
            $this->sql    .= " and serv_name like '%" . $serv_name . "%'";
        }
        $this->params = array($game_id);
        $this->doResultList();
        $data = $this->result;
        return $data;

    }

    // 指定条件 - 魔域商品列表
    public function get_moyu_products($params,$user_id){
        $this->sql = 'select g.id as server_id, g.serv_id as sid,g.serv_name as sname,c.channel_name,c.platform,c.icon,p.* ,ga.game_name,ga.game_icon from products as p left join game as ga on ga.id=p.game_id left join channels as c on c.id=p.channel_id left join game_servs as g on g.id=p.serv_id where p.is_pub=1 and p.type=5 and p.user_id!='.$user_id;
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
            $this->sql .= " and p.title like '%" . $params['title'] . "%'";
        }
//        if(intval($params['price_pre'])){
//            $this->sql .= " and p.price >= " . intval($params['price_pre']);
//        }
//        if(intval($params['price_aft'])){
//            $this->sql .= " and p.price <= " . intval($params['price_aft']);
//
//        }
//        if(trim($params['price_order'])!=null){
//            $this->sql .= ' order by p.price ' . $params['price_order'].',p.add_time desc';
//        }
        if($params['price_order']=='time'){
            $this->sql .= " order by p.add_time desc";
        }
        if($params['price_order']=='price_order_desc'){
            $this->sql .= " order by p.price desc";
        }
        if($params['price_order']=='price_order_asc'){
            $this->sql .= " order by p.price asc";
        }
        $this->doResultList();
        return $this->result;
    }

    public function get_product_imgs($product_id){
        $this->sql    = "select * from product_imgs where product_id=?";
        $this->params = array($product_id);
        $this->doResult();
        return $this->result;
    }

    //获取一个user_id的收藏次数
    public function get_collect_list($user_id, $type = ''){
        $this->sql = "select * from products where 1=1";
        if($user_id){
            $this->sql .= " and find_in_set('" . $user_id . "', collect)";
        }
        $this->doResultList();
        $data = $this->result;

        return $data;
    }
    //删除缓存
    public function del_session($name,$user_id,$type=''){
        $this->mmc->delete($name.$user_id.'_'.$type);
    }

    public function insert_product_collect($params){
        $this->sql = "insert into product_user_collect(user_id,product_id,add_time) values(?,?,?)";
        $this->params = array($params['user_id'],$params['product_id'],time());
        $this->doInsert();
        return $this->LAST_INSERT_ID;
    }
    public function get_user_colllect_by_product_id($product_id){
        $this->sql    = "select * from product_user_collect where product_id=?";
        $this->params = array($product_id);
        $this->doResultList();
        return $this->result;
    }

    public function get_product_colllect_count_by_user_id($user_id){
        $this->sql    = "select count(1) as num from product_user_collect where user_id=? and is_deleted=2";
        $this->params = array($user_id);
        $this->doResult();
        return $this->result;
    }
    public function delete_product_collect($params){
        $this->sql    = "update product_user_collect set is_deleted=? where product_id=? and user_id=? and is_deleted=2";
        $this->params = array(1, $params['product_id'],$params['user_id']);
        $this->doExecute();
    }
    public function get_user_colllectinfo_by_product_id($params){
        $this->sql    = "select * from product_user_collect where user_id=? and product_id=? and is_deleted=2";
        $this->params = array($params['user_id'],$params['product_id']);
        $this->doResult();
        return $this->result;
    }

    public function update_user_colllect_by_id($id){
        $this->sql    = "update product_user_collect set is_deleted=?,add_time=? where id=?";
        $this->params = array(2,time(),$id);
        $this->doExecute();
    }

    public function update_product($id){
        $this->sql = "update products set is_pub=0 where id = ?";
        $this->params = array($id);
        $this->doExecute();
    }

    public function get_channel_info($ch_id){
        $this->sql = "select * from channels where id = ?";
        $this->params = array($ch_id);
        $this->doResult();
        return $this->result;
    }
    //获取游戏列表
    public function get_game_list(){
        $this->sql = "select * from game where apply=1 and is_del=0 order by is_hot desc , id desc";
        $this->doResultList();
        return $this->result;
    }
    //发布商品
    public function add_product($params){
        $this->sql="insert into products(title,qq,mobile,proportion,num,`type`,intro,game_id,channel_id,serv_id,stock,price,user_id,add_time,is_pub)values(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";
        $this->params=array($params['title'],$params['qq'],$params['mobile'],$params['proportion'],$params['num'],5,$params['intro'],$params['game_id'],$params['channel_id'],
            $params['serv_id'],$params['stock'],$params['price'],$params['user_id'],time(),5);
        $this->doInsert();
        $product_id = $this->LAST_INSERT_ID;
        return $product_id;
    }
    public function add_product_img($product_id,$img_url){
        $this->sql="insert into product_imgs(product_id,img_url) value(?,?)";
        $this->params=array($product_id,$img_url);
        $this->doInsert();
        $product_img_id = $this->LAST_INSERT_ID;
        return $product_img_id;
    }
    public function update_products_status($params){
        $this->sql = "update products set is_pub=? where id = ?";
        $this->params = array($params['is_pub'],$params['id']);
        $this->doExecute();
    }
    public function update_products_end_time($params){
        $this->sql = "update products set end_time=? where id = ?";
        $this->params = array(time(),$params['id']);
        $this->doExecute();
    }
    public function get_products_status($id){
        $this->sql = "select is_pub from products where id = ?";
        $this->params = array($id);
        $this->doResult();
        return $this->result;
    }
    public function get_products_imgs($id){
        $this->sql = "select * from product_imgs where product_id = ? and is_del=1";
        $this->params = array($id);
        $this->doResultList();
        return $this->result;
    }
    public function delete_products_imgs($id){
        $this->sql = "update product_imgs set is_del=? where id = ?";
        $this->params = array(2,$id);
        $this->doExecute();
    }
    public function get_products_imgs_is_del($id){
        $this->sql = "select is_del from product_imgs where id = ?";
        $this->params = array($id);
        $this->doResult();
        return $this->result;
    }
    //修改商品
    public function edit_product($params){
        $this->sql="update products set title=?,proportion=?,num=?,price=?,is_pub=?,
            stock=?,add_time=? where id=?";
        $this->params=array($params['title'],$params['proportion'],$params['num'],
            $params['price'],5,$params['stock'],time(),$params['id']);
        $this->doExecute();
    }
    public function update_product_img($id,$img_url){
        $this->sql = "update product_imgs set img_url=? where product_id=?";
        $this->params = array($img_url,$id);
        $this->doExecute();
    }
    public function get_publish_product_info($id){
        $this->sql = "select num,stock,price,proportion from products where id = ?";
        $this->params = array($id);
        $this->doResult();
        return $this->result;
    }
    //销售数量
    public function get_sell_count_by_user_id($user_id){
        $this->sql    = "select count(1) as num from orders o left join products p on o.product_id=p.id where p.user_id=? and o.status=3 and p.game_id in (1697,1698,1700)";
        $this->params = array($user_id);
        $this->doResult();
        return $this->result;
    }
    public function get_user_certification($user_id){
        $this->sql    = "select count(1) as num from user_certification where user_id=?";
        $this->params = array($user_id);
        $this->doResult();
        return $this->result;
    }
    public function get_account_info($user_id){
        $this->sql = "select a.pay_password,a.security_mobile,a.pay_account,a.pay_name,a.balance,b.real_name,b.id_card from user_detail a left join user_certification b on a.user_id=b.user_id where a.user_id=?";
        $this->params=array($user_id);
        $this->doResult();
        return $this->result;
    }
    public function update_product_see_num($id){
        $this->sql    = "update products set see_num=see_num+1 where id=?";
        $this->params = array($id);
        $this->doExecute();
    }
    public function get_user_collect_list($user_id, $type = ''){
//        $data = $this->mmc->get('my_collect_' . $user_id . '_' . $type);
//        if(!$data){
            $this->sql = "select a.*,p.type,p.is_pub,p.title,p.game_id,p.serv_id,p.channel_id,p.price,p.proportion,p.num from product_user_collect a left join products p on a.product_id=p.id where a.is_deleted=2 and a.user_id=" . $user_id;
            if($type){
                $this->sql .= " and (p.is_pub = 0 or p.is_pub = 4)";
            } else{
                $this->sql .= " and (p.is_pub = 1 or p.is_pub = 2)";
            }
            $this->sql .= " order by a.id desc";
            $this->doResultList();
            $data = $this->result;
//            $this->mmc->set('my_collect_' . $user_id . '_' . $type, $data, 1, 600);
//        }
        return $data;
    }
    public function get_game_info($game_id){
        $data = $this->mmc->get('game_info_' . $game_id);
        if(!$data){
            $this->sql    = "select * from game where id=?";
            $this->params = array($game_id);
            $this->doResult();
            $data = $this->result;
            $this->mmc->set('game_info_' . $game_id, $data, 1, 600);
        }
        return $data;
    }

    public function get_service_info($serv_id){
        $data = $this->mmc->get('service_info_' . $serv_id);
        if(!$data){
            $this->sql    = "select * from game_servs where id=?";
            $this->params = array($serv_id);
            $this->doResult();
            $data = $this->result;
            $this->mmc->set('service_info_' . $serv_id, $data, 1, 600);
        }
        return $data;
    }

    public function get_channel_infomation($ch_id){
        $data = $this->mmc->get('channel_info_' . $ch_id);
        if(!$data){
            $this->sql    = "select * from channels where id=?";
            $this->params = array($ch_id);
            $this->doResult();
            $data = $this->result;
            $this->mmc->set('channel_info_' . $ch_id, $data, 1, 600);
        }
        return $data;
    }


}