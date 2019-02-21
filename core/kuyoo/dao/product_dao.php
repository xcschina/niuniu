<?php
COMMON('dao');

class product_dao extends Dao {

    public function __construct() {
        parent::__construct();
        $this->mmc = new Memcache();
        $this->mmc->connect(MMCHOST, MMCPORT);

        // $this->mmc->flush(); // ftest;

        $this->price_type = array(
            0=>array("","不限"),
            1=>array(" and price<30","30元以下"),
            2=>array(" and price<100 and price>=30","30--100元"),
            3=>array(' and price<300 and price>=100',"100-300元"),
            4=>array(" and price>=300","300元以上")
        );
    }

    public function get_channels_discount($game_id){
        $this->sql="select c.*,ch.discount from channels c inner join  channels_discount ch on c.id=ch.channel_id where ch.game_id=?";
        $this->params=array($game_id);
        $this->doResultList();
        return $this->result;
    }

    public function get_game_servs($game_id){
        $data = memcache_get($this->mmc,'game_servs'.$game_id);
        if(!$data){
            $this->sql = "SELECT * FROM game_servs WHERE game_id=? order by id desc";
            $this->params = array($game_id);
            $this->doResultList();
            $data = $this->result;
            $this->mmc->set('game_servs'.$game_id, $data, 1, 600);
        }
        return $data;
    }

    public function get_game_ch_servs($game_id,$ch_id){
        $data = memcache_get($this->mmc,'game_servs'.$game_id."_".$ch_id);
        if(!$data){
            $this->sql = "SELECT * FROM game_servs WHERE game_id=? and ch_".$ch_id."=1 order by id desc";
            $this->params = array($game_id);
            $this->doResultList();
            $data = $this->result;
            $this->mmc->set('game_servs'.$game_id."_".$ch_id, $data, 1, 600);
        }
        return $data;
    }

    public function get_product_info($product_id){
        $this->sql="SELECT p.*,g.game_name,g.tags,g.product_img,pd.* from products p
        inner join game g on p.game_id=g.id LEFT join product_discounts as pd on p.id=pd.product_id where p.id=? and p.is_pub=1";
        $this->params=array($product_id);
        $this->doResult();
        return $this->result;
    }

    public function get_user_agent_game($user_id, $game_id){
        $data = $this->mmc->get("user_agent_games".$user_id."-".$game_id);
        if(!$data){
            $this->sql = "select * from agents where game_id=? and user_id=?";
            $this->params = array($game_id, $user_id);
            $this->doResult();
            $data = $this->result;
            $this->mmc->set("user_agent_games".$user_id."-".$game_id, $data, 1,600);
        }
        return $data;
    }

    public function get_game_intro_imgs($game_id){
        $data = $this->mmc->get("game_intro_imgs".$game_id);
        if(!$data){
            $this->sql = "select * from product_intro_imgs where `type`=? and game_id=?";
            $this->params = array(99, $game_id);
            $this->doResultList();
            $data = $this->result;
            $this->mmc->set("game_intro_imgs".$game_id, $data, 1, 3600);
        }
        return $data;
    }

    public function check_usr_order($user_id, $order_id){
        $this->sql = "select * from orders where buyer_id=? and id=? and status=2";
        $this->params = array($user_id, $order_id);
        $this->doResult();
        return $this->result;
    }

    public function get_banks(){
        $data = memcache_get($this->mmc, "1bankcodes");
        if(!$data){
            $this->sql = "select * from bankcodes where seq!=15 and t=1 order by seq";
            $this->params = array();

            $this->doResultList();
            $data = $this->result;
            memcache_set($this->mmc, "1bankcodes", $data, 1, 3600);
        }
        return $data;
    }

    public function add_collections($user_id,$product_id){
        $this->sql="insert into collections(user_id,product_id,add_time)values(?,?,?)";
        $this->params=array($user_id,$product_id,strtotime("now"));
        $this->doInsert();
    }

    public function del_collections($user_id,$product_id){
        $this->sql="delete from collections where user_id=? and product_id=?";
        $this->params=array($user_id,$product_id);
        $this->doExecute();
    }

    public function get_collections($user_id,$product_id){
        $this->sql="select * from collections where user_id=? and product_id=?";
        $this->params=array($user_id,$product_id);;
        $this->doResult();
        return $this->result;
    }

    public function get_channels(){
        $this->sql = "select * from channels";
        $this->params = array();
        $this->doResultList();
        return $this->result;
    }

    public function get_service(){
        $this->sql = "SELECT * FROM `admins` WHERE `group`='vip' AND is_del=0 AND last_service_time>0 and id <>'114' order by last_service_time limit 1";
        $this->doResult();
        return $this->result;
    }

    public function get_serv_info($serv_id){
        $info = memcache_get($this->mmc, 'serv-info'.$serv_id);
        if(!$info){
            $this->sql = "SELECT * FROM game_servs WHERE id=?";
            $this->params = array($serv_id);
            $this->doResult();
            $info = $this->result;
            memcache_set($this->mmc, 'serv-info'.$serv_id, $info);
        }
        return $info;
    }

//    public function insert_order($order){
//        $this->sql = "insert into orders(order_id,title,buyer_id,product_id,amount,money,unit_price,pay_money,game_id,serv_id,
//            game_channel,seller_id,status,buy_time,pay_channel,qq,tel,discount,discount_in,is_rand_user,role_name,
//            role_back_name,game_user,game_pwd,attr)values(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";
//        $this->params = array_values($order);
//        $this->doExecute();
//
//        $this->sql = "update user_info set buy_mobile=? where user_id=?";
//        $this->params = array($order['tel'], $order['buyer_id']);
//        $this->doExecute();
//        $_SESSION['buy_mobile'] = $order['tel'];
//    }

    public function get_game_user($game_id, $user_id){
        $this->sql = "select a.id,a.game_user,a.role_name,a.serv_id,c.serv_name from orders as a inner join products as b on a.product_id=b.id
        inner join game_servs as c on a.serv_id=c.id where a.buyer_id=? and b.type=1 and a.status=2 and a.game_id=?";
        $this->params = array($user_id, $game_id);
        $this->doResultList();
        return $this->result;
    }



    // -------------------
    // v2 zbc
    // -------------------

    public function get_recommends($game_id,$type){
        $info = memcache_get($this->mmc, 'recommends_'.$game_id.'_'.$type);
        if(!$info){
            $this->sql = "select img,price,title from products where game_id=? and type=? and is_pub=1 order by id desc limit 4";
            $this->params = array($game_id,$type);
            $this->doResultList();
            $info = $this->result;
            memcache_set($this->mmc, 'recommends_'.$game_id.'_'.$type, $info,1,600);
        }
        return $info;
    }

    public function get_products_by_gameid($game_id,$type){
        $this->sql =  "select * from products where 1=1 and is_pub=1 and game_id=? and `type`=?  order by add_time desc,id desc";//and end_time>=? or 0
        $this->params = array($game_id,$type);
        $this->doResultList();
        return $this->result;
    }

    public function get_order_relation_info($product_id){
        $this->sql = "select a.game_name,b.title from game a,products b where b.id=? and b.game_id=a.id";
        $this->params = array($product_id);
        $this->doResult();
        return $this->result;
    }

    public function get_order_info_by_order_id($order_id){
        $this->sql = "select a.*,b.qq as service_qq,c.serv_name from orders as a inner join admins as b on a.service_id=b.id inner join game_servs as c on c.id=a.serv_id where a.order_id=?";
        $this->params = array($order_id);
        $this->doResult();
        return $this->result;
    }

    public function get_order_info($order_id){
        $this->sql = "select a.*,c.serv_name from orders as a inner join game_servs as c on c.id=a.serv_id where a.order_id=?";
        $this->params = array($order_id);
        $this->doResult();
        return $this->result;
    }


    public function insert_order($order){
        $this->sql = "insert into orders(order_id,title,buyer_id,product_id,amount,money,unit_price,pay_money,game_id,serv_id,
            game_channel,seller_id,status,buy_time,pay_channel,qq,tel,discount,discount_in,role_name,
            role_back_name,service_id,game_user,game_pwd,attr,is_agent,is_rand_user,reduce_product,platform)values(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";
        $this->params = array_values($order);
        $this->doExecute();
        $this->sql = "update user_info set buy_mobile=? where user_id=?";
        $this->params = array($order['tel'], $order['buyer_id']);
        $this->doExecute();
        $_SESSION['buy_mobile'] = $order['tel'];

        //非首冲号\续充\代充,需要减库存
        if($order['reduce_product']==1){
            $this->sql = "update products set stock=stock-? where id=?";
            $this->params = array($order['amount'],$order['product_id']);
            $this->doExecute();
        }
        //更新客服信息
        $this->sql = "update `admins` set last_service_time=? where id=?";
        $this->params = array(strtotime("now"), $order['service_id']);
        $this->doExecute();
    }

    public function get_characters_by_userid($user_id,$game_id){
        $data = memcache_get($this->mmc,'www_game_c_user'.$game_id."u".$user_id);
        if(!$data){
            $this->sql =  "select a.game_user,a.role_name,a.qq,a.tel,a.serv_id,c.serv_name,a.game_channel,d.channel_name FROM orders AS a
                        LEFT JOIN products AS b ON a.product_id=b.id LEFT JOIN game_servs AS c ON a.serv_id = c.id
                        LEFT JOIN channels AS d ON a.game_channel=d.id WHERE a.buyer_id=? AND a.game_id=? AND a.status=2 AND b.type=1 ORDER BY a.pay_time DESC";
            $this->params = array($user_id,$game_id);
            $this->doResultList();
            $data = $this->result;
            $this->mmc->set('www_game_c_user'.$game_id."u".$user_id, $data, 1, 600);
        }
        return $data;
    }

    public function check_game_user($game_id, $game_user){
        $data = memcache_get($this->mmc,'www_game_user'.$game_id."u".$game_user);
        if(!$data){
            $this->sql = "select a.id,a.game_user,a.role_name,a.serv_id,a.game_channel as ch_id,c.serv_name from orders as a
                          inner join products as b on a.product_id=b.id inner join game_servs as c on a.serv_id=c.id
                          where a.game_user=? and b.type=1 and a.status=2 and a.game_id=?";
            $this->params = array($game_user, $game_id);
            $this->doResult();
            $data = $this->result;
            $this->mmc->set('www_game_user'.$game_id."u".$game_user, $data, 1, 600);
        }
        return $data;
    }

    // type 产品类型 1首充号; 
    public function get_channels_by_gameid($game_id,$type,$platform){
        $this->sql = "select DISTINCT(p.channel_id) AS ch_id,c.channel_name as ch_name,c.platform FROM channels AS c LEFT JOIN products AS p ON p.channel_id=c.id WHERE p.game_id=? AND p.is_pub=1 AND p.type=? AND c.platform=? AND 1=1 ";
        $this->params = array($game_id,$type,$platform);
        $this->doResultList();
        return $this->result;
    }

    // 指定条件 - 游戏币商品列表
    public function get_coin_products($type,$pagesize,$page=1){
        $post = $_POST;
        // $this->limit_sql = "SELECT * from products as p left join product_discounts as pd on p.id=pd.product_id where 1=1 and p.is_pub=1 and p.type=? and p.game_id=? and p.channel_id=? and p.serv_id in(0,?)";
        $this->limit_sql = "select * from products as p left join product_discounts as pd on p.id=pd.product_id where 1=1 and p.is_pub=1 and p.type=? and p.game_id=?";

        if($post['game_unit']) {
            $this->limit_sql .= " and p.unit='".$post['game_unit']."'";
        }

        if($post['ch_id']) {
            $this->limit_sql .= " and p.channel_id='".$post['ch_id']."'";
        }

        if($post['serv_id']) {
            $this->limit_sql .= " and p.serv_id in(0,'".$post['serv_id']."')";
        }else{
            $this->limit_sql .= " and p.serv_id=0";
        }

        if($post['pt_id']) {
            $this->limit_sql .= " and p.channel_id in (select id from channels where platform='".$post['pt_id']."')";
        }

        switch ($post['price_order']) {
            case 'asc':  $this->limit_sql .= ' order by price asc';  break;
            case 'desc':
            default: $this->limit_sql .= ' order by price desc'; break;
        }

        // $post['game_id'] = 698; // test
        // $this->params= array_values($post);
        // array_unshift($this->params, $type);

        $this->params = array($type, $post['game_id']);
        $this->doLimitResultList($page,$pagesize);
        return $this->result;
    }

    // 指定游戏 - 游戏币商品列表
    public function get_coin_products_by_gameid($game_id,$type,$pagesize,$page=1){
        $this->limit_sql =  "select * from products as p left join product_discounts as pd on p.id=pd.product_id where 1=1 and p.is_pub=1 and p.type=? and p.game_id=?";
        $this->params = array($type,$game_id);
        $this->doLimitResultList($page,$pagesize);
        return $this->result;
    }

    // 指定条件 - 账号列表。。。
    public function get_count_products($game_id, $page=1, $pagesize=6, $params=array('platform'=>1)){
        $this->limit_sql = 'select g.serv_id as sid,g.serv_name as sname,c.channel_name,c.platform,c.icon,p.* from products as p left join channels as c on c.id=p.channel_id left join game_servs as g on g.id=p.serv_id where 1=1 and p.is_pub=1 and p.type=4 and p.game_id=?';
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
            if(in_array(trim($params['price_order']), array('asc','desc'))){
                $this->limit_sql .= ' order by p.price '.$params['price_order'];
            }else{
                $this->limit_sql .= ' order by p.price desc';
            }
        }

        $this->params = array($game_id);
        $this->doLimitResultList($page,$pagesize);
        return $this->result;

    }

    // 游戏资讯列表...
    public function get_articles_list($game_id){
        $data = memcache_get($this->mmc,'game_link_articles'.$game_id);
        if(!$data){
            $this->sql="SELECT * FROM articles WHERE is_pub=1 AND game_id=? ORDER BY lastupdate DESC limit 10";
            $this->params = array($game_id);
            $this->doResultList();
            $data = $this->result;
            $this->mmc->set('game_link_articles'.$game_id, $data, 1, 600);
        }
        return $data;
    }

    public function get_order_status($order_id=0){
        $this->sql = "select status from orders where 1=1 and order_id=?";
        $this->params = array($order_id);
        $this->doResult();
        return $this->result;
    }

    public function update_order_after_wx_pay($params=array()){
        $this->sql = "update orders set channel_order_id=?,status=?,payer=?,bank_order_id=?,pay_time=? where order_id=?";
        $this->params = array($params['channel_order_id'], 1, '微信openid:'.$params['openid'], '微信支付|'.$params['bank_type'], strtotime($params['pay_time']),$params['order_id']);
        $this->doExecute();
    }

    public function set_user_group($user_id, $user_group=11){
        $this->sql = 'update user_info set user_group=? where user_id=?';
        $this->params = array($user_group, $user_id);
        $this->doExecute();
    }

    public function get_user_group($user_id){
        $this->sql = 'select user_group from user_info where user_id=?';
        $this->params = array($user_id);
        $this->doResult();
        return $this->result;
    }

    
}