<?php
COMMON('dao', 'randomUtils');

class trading_dao extends Dao{

    public function __construct(){
        parent::__construct();
        $this->mmc = new Memcache();
        $this->mmc->connect(MMCHOST, MMCPORT);
        $this->redis = new Redis();
        $this->redis->connect(REDIS_HOST, REDIS_PORT);
        $this->redis->select(2);
    }

    public function get_user_info($user_id){
        $data = $this->mmc->get('moyu_' . $user_id);
        if(!$data){
            $this->sql    = "select * from user_info where user_id = ?";
            $this->params = array($user_id);
            $this->doResult();
            $data = $this->result;
            $this->mmc->set('moyu_' . $user_id, $data, 1, 600);
        }
        return $data;
    }

    public function get_wx_user_openid($unionid, $wx_app_id){
        $this->sql    = "select * from `niuniu`.wx_app_login_tb where unionid=? and app_id=? ";
        $this->params = array($unionid, $wx_app_id);
        $this->doResult();
        $data = $this->result;
        return $data;
    }


    public function get_order($user_id, $status, $order_type){
        $this->sql    = "select count(*) as num from orders o left join orders_relation_tb r on o.id=r.order_id where o.buyer_id=? and o.status=? and r.order_type=? ";
        $this->params = array($user_id, $status, $order_type);
        $this->doResult();
        return $this->result;
    }
    public function get_sell_order($user_id, $status){
        $this->sql    = "select count(*) as num from orders o left join products r on o.product_id=r.id where r.user_id=? and o.status=?";
        $this->params = array($user_id, $status);
        $this->doResult();
        return $this->result;
    }
    public function get_sell_product($user_id, $status){
        $this->sql    = "select count(*) as num from  products where user_id=? and is_pub=?";
        $this->params = array($user_id, $status);
        $this->doResult();
        return $this->result;
    }
    public function get_order_list($user_id, $status = ''){
        $this->sql = "select a.*,g.game_name,v.serv_name,c.channel_name,p.proportion,p.num,p.type from orders a left join orders_relation_tb b on a.id = b.order_id left join game g on a.game_id=g.id left join game_servs v on a.game_id=v.game_id and a.serv_id=v.id left join channels c on b.channel_id=c.id left join products p on a.product_id=p.id where a.buyer_id = " . $user_id;
        if($status && is_numeric($status) || $status === '0'){
            $this->sql .= " and a.status = " . $status;
        }
        $this->sql .= " order by a.buy_time desc";
        $this->doResultList();
        return $this->result;
    }

    public function get_collect_list($user_id, $type = ''){
        $data = $this->mmc->get('my_collect_' . $user_id . '_' . $type);
        if(!$data){
            $this->sql = "select a.*,p.type,p.is_pub,p.title,p.game_id,p.serv_id,p.channel_id,p.price,p.proportion,p.num from product_user_collect a left join products p on a.product_id=p.id where a.is_deleted=2 and a.user_id=" . $user_id;
            if($type){
                $this->sql .= " and (p.is_pub = 0 or p.is_pub = 4)";
            } else{
                $this->sql .= " and (p.is_pub = 1 or p.is_pub = 2)";
            }
            $this->sql .= " order by a.id desc";
            $this->doResultList();
            $data = $this->result;
            $this->mmc->set('my_collect_' . $user_id . '_' . $type, $data, 1, 600);
        }
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

    public function get_channel_info($ch_id){
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

    public function get_goods_info($id){
        $data = $this->mmc->get('goods_info_' . $id);
        if(!$data){
            $this->sql    = "select a.*,g.game_name,v.serv_name,c.channel_name,c.platform from products a left join game g on a.game_id=g.id left join game_servs v on a.game_id=v.game_id and a.serv_id=v.id left join channels c on a.channel_id=c.id where a.id =?";
            $this->params = array($id);
            $this->doResult();
            $data = $this->result;
            $this->mmc->set('goods_info_' . $id, $data, 1, 600);
        }
        return $data;
    }

    public function update_goods($info){
        $this->sql    = "update products set stock=? where id=?";
        $this->params = array($info['new_stock'], $info['id']);
        $this->doExecute();
        $this->mmc->delete('goods_info_' . $info['id']);
    }

    public function update_pub($goods_id){
        $this->sql    = "update products set is_pub=0 where id=?";
        $this->params = array($goods_id);
        $this->doExecute();
        $this->mmc->delete('goods_info_' . $goods_id);
    }

    public function get_service_list($params){
        $data = $this->mmc->get('service_list_' . $params['game_id'] . '_' . $params['channel_id']);
        if(!$data){
            $this->sql    = "select * from game_servs where game_id=? and ch_" . $params['channel_id'] . "=1 order by id desc";
            $this->params = array($params['game_id']);
            $this->doResultList();
            $data = $this->result;
            $this->mmc->set('service_list_' . $params['game_id'] . '_' . $params['channel_id'], $data, 1, 600);
        }
        return $data;
    }

    public function update_collect($id, $user_id){
        $this->sql    = "update product_user_collect set is_deleted = 1 where id = ? and user_id=?";
        $this->params = array($id, $user_id);
        $this->doExecute();
        $this->mmc->delete('goods_info_' . $id);
    }

    public function del_session($name, $user_id, $type = ''){
        $this->mmc->delete($name . $user_id . '_' . $type);
    }

    public function update_user_info($params, $user_id){
        $user_id_md5 = md5((string)$user_id . "user_login");
        $user_info   = $this->redis->hGetAll($user_id_md5);
        if(!$user_info){
            $this->sql    = "SELECT * FROM user_login WHERE user_id=?";
            $this->params = array($user_id);
            $this->doResult();
            $user_info = $this->result;
            if(!$user_info){
                $this->sql    = "SELECT user_id,nick_name,login_name,user_name,id_number,password,mobile,age,login_type,token FROM user_info WHERE user_id=?";
                $this->params = array($user_id);
                $this->doResult();
                $user_info = $this->result;
            }
        }
        $user_info = array_merge($user_info, array("id_number" => $params['id_number'], "user_name" => $params['user_name']));
        $this->redis->hMset($user_id_md5, $user_info);
        $this->redis->lPush("user_login", $user_id_md5);
        $this->mmc->delete('moyu_' . $user_id);
    }

    public function get_user_by_mobile($mobile){
        $this->sql    = "select user_id,nick_name,login_name,mobile from user_info where mobile= ?";
        $this->params = array($mobile);
        $this->doResult();
        return $this->result;
    }

    public function update_user_mobile($user_id, $mobile){
        $user_id_md5 = md5((string)$user_id . "user_login");
        $user_info   = $this->redis->hGetAll($user_id_md5);
        if(!$user_info){
            $this->sql    = "SELECT * FROM user_login WHERE user_id=?";
            $this->params = array($user_id);
            $this->doResult();
            $user_info = $this->result;
            if(!$user_info){
                $this->sql    = "SELECT user_id,nick_name,login_name,user_name,id_number,password,mobile,age,login_type,token FROM user_info WHERE user_id=?";
                $this->params = array($user_id);
                $this->doResult();
                $user_info = $this->result;
            }
        }
        $user_info = array_merge($user_info, array("mobile" => $mobile));
        $this->redis->hMset($user_id_md5, $user_info);
        $this->redis->lPush("user_login", $user_id_md5);
        $this->mmc->delete('moyu_' . $user_id);
    }

    public function get_my_messages($user_id){
        $this->sql    = "select * from messages where receiver_id=? ";
        $this->sql    .= " order by id desc";
        $this->params = array($user_id);
        $this->doResultList();
        return $this->result;
    }

    public function get_unread_messages($user_id){
        $this->sql    = "select count(*) as num from messages where receiver_id=? and is_read=0";
        $this->params = array($user_id);
        $this->doResult();
        return $this->result;
    }

    public function get_unread_msg_count($user_id){
        $this->sql    = "select count(1) as undread_num from messages where is_read=0 and receiver_id=?";
        $this->params = array($user_id);
        $this->doResult();
        return $this->result['undread_num'];
    }

    public function get_message_detail($id){
        $this->sql    = "select * from messages where id = ?";
        $this->params = array($id);
        $this->doResult();
        return $this->result;
    }

    public function get_order_info($id){
        $this->sql    = "select a.*,b.collect_time,b.finish_time,b.sex,b.job,b.order_type,c.real_name,g.game_name,v.serv_name,p.user_id from orders a left join orders_relation_tb b on a.id=b.order_id left join admins c on a.service_id=c.id left join game g on a.game_id=g.id left join game_servs v on a.serv_id=v.id and a.game_id=v.game_id left join products p on a.product_id=p.id where a.id=?";
        $this->params = array($id);
        $this->doResult();
        return $this->result;
    }

    public function get_product_imgs($product_id){
        $this->sql    = "select * from product_imgs where product_id=?";
        $this->params = array($product_id);
        $this->doResultList();
        return $this->result;
    }

    public function update_order_type($order_id, $type){
        $this->sql    = "update orders_relation_tb set close_type=?,finish_time=? where order_id=?";
        $this->params = array($type, time(), $order_id);
        $this->doExecute();
    }

    public function update_order_status($id,$status){
        $this->sql    = "update orders set `status`=? where id=?";
        $this->params = array($status,$id);
        $this->doExecute();
    }

    public function update_order_time($id){
        $this->sql    = "update orders_relation_tb set collect_time=?,finish_time=? where order_id=?";
        $this->params = array(time(), time(), $id);
        $this->doExecute();
    }

    public function get_articles_list($part_id, $num = ''){
        $this->sql = "select * from articles where is_pub=1 and part_id = " . $part_id;
        if($num){
            $this->sql .= " limit " . $num;
        }
        $this->doResultList();
        return $this->result;
    }

    public function get_articles_info($id){
        $this->sql    = "select * from articles where id = ? and is_pub = 1";
        $this->params = array($id);
        $this->doResult();
        return $this->result;
    }

    public function insert_order($params, $info, $user_id){
        $this->sql    = "insert into orders(order_id,title,buyer_id,product_id,amount,unit_price,money,pay_money,game_id,serv_id,status,buy_time,pay_channel,
                        qq,tel,role_name,platform,game_channel,role_sex)values(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";
        $this->params = array($params['order_id'], $info['title'], $user_id, $info['id'], $params['num'], $params['price'], $params['money'], $params['pay_money'],
            $info['game_id'], $params['service_id'], 0, time(), $params['pay_mode'], $params['qq'], $params['mobile'], $params['role_name'], 1, $info['channel_id'], $params['sex']);
        $this->doInsert();
        $order_id = $this->LAST_INSERT_ID;

        $this->sql    = "insert into orders_relation_tb(order_id,sex,job,channel_id) values(?,?,?,?)";
        $this->params = array($order_id, $params['sex'], $params['job'], $info['channel_id']);
        $this->doInsert();

        return $order_id;
    }

    public function get_order_id($order_id){
        $this->sql    = "select id,product_id,amount from orders where order_id=?";
        $this->params = array($order_id);
        $this->doResult();
        return $this->result;
    }

    //卖家订单
    public function get_sell_order_list($user_id, $status = ''){
        $this->sql = "select a.*,g.game_name,v.serv_name,c.channel_name,p.proportion,p.num,p.type from orders a left join orders_relation_tb b on a.id = b.order_id left join game g on a.game_id=g.id left join game_servs v on a.game_id=v.game_id and a.serv_id=v.id left join channels c on b.channel_id=c.id left join products p on a.product_id=p.id where p.user_id = " . $user_id;
        if($status && is_numeric($status) || $status === '0'){
            $this->sql .= " and a.status = " . $status;
        }
        $this->sql .= " order by a.buy_time desc";
        $this->doResultList();
        return $this->result;
    }

    //上传订单相关图片
    public function add_order_imgs($order_img_id, $img_url, $user_id){
        $this->sql    = "insert into order_imgs(order_id,img_url,admin_id,add_time) value(?,?,?,?)";
        $this->params = array($order_img_id, $img_url, $user_id,time());
        $this->doInsert();
        $order_imgs_id = $this->LAST_INSERT_ID;
        return $order_imgs_id;
    }

    public function add_user_balance_detail($params){
        $this->sql    = "insert into user_balance_detail(order_id,pay_mode,user_id,`type`,money,add_time,status)values(?,?,?,?,?,?,?)";
        $this->params = array($params['order_id'],$params['pay_mode'], $params['user_id'], $params['type'], $params['money'], time(), $params['status']);
        $this->doInsert();
    }

    public function get_user_balance($user_id){
        $this->sql    = "SELECT balance FROM user_detail WHERE user_id=?";
        $this->params = array($user_id);
        $this->doResult();
        return $this->result;
    }

    public function update_user_balance($balance, $user_id){
        $this->sql    = "update user_detail set balance=balance-? where user_id = ?";
        $this->params = array($balance, $user_id);
        $this->doExecute();
    }
    public function update_seller_balance($balance, $user_id){
        $this->sql    = "update user_detail set balance=balance+? where user_id = ?";
        $this->params = array($balance, $user_id);
        $this->doExecute();
    }
    public function update_user_lock($status, $user_id){
        $this->sql    = "update user_detail set pay_lock=? where user_id=?";
        $this->params = array($status, $user_id);
        $this->doExecute();
    }

    //实名认证
    public function get_user_certification_count($user_id){
        $this->sql    = "select count(1) as num from user_certification where user_id=?";
        $this->params = array($user_id);
        $this->doResult();
        return $this->result;
    }

    public function get_account_info($user_id){
        $this->sql    = "select c.mobile,a.pay_password,a.security_mobile,a.pay_account,a.pay_name,a.balance,b.real_name,b.id_card from user_detail a left join user_certification b on a.user_id=b.user_id left join user_info c on a.user_id=c.user_id where a.user_id=?";
        $this->params = array($user_id);
        $this->doResult();
        return $this->result;
    }

    public function insert_user_certification($params){
        $this->sql    = "insert into user_certification(user_id,real_name,id_card,add_time) values (?,?,?,?)";
        $this->params = array($params['user_id'], $params['real_name'], $params['id_card'], time());
        $this->doInsert();
        $id = $this->LAST_INSERT_ID;
        return $id;
    }

    public function update_user_security_mobile($mobile, $user_id){
        $this->sql    = "update user_detail set security_mobile=? where user_id=?";
        $this->params = array($mobile, $user_id);
        $this->doExecute();
    }

    public function update_user_pay_passward($pay_passward, $user_id){
        $this->sql    = "update user_detail set pay_passward=? where user_id=?";
        $this->params = array($pay_passward, $user_id);
        $this->doExecute();
    }

    public function update_user_pay_account_and_name($pay_account, $pay_name, $user_id){
        $this->sql    = "update user_detail set pay_account=?,pay_name=? where user_id=?";
        $this->params = array($pay_account, $pay_name, $user_id);
        $this->doExecute();
    }

    //卖家订单
    public function get_sell_product_list($user_id, $status = ''){
        $this->sql = "select a.*,g.game_name,v.serv_name,c.channel_name from products a left join game g on a.game_id=g.id left join game_servs v on a.game_id=v.game_id and a.serv_id=v.serv_id left join channels c on a.channel_id=c.id  where a.is_pub!=4 and a.user_id = " . $user_id;
        if($status && is_numeric($status) || $status === '0'){
            $this->sql .= " and a.is_pub = " . $status;
        }
        $this->sql .= " order by a.add_time desc";
        $this->doResultList();
        return $this->result;
    }

    public function get_product_collect_num($product_id){
        $this->sql    = "select count(1) as num from product_user_collect where product_id=? and is_deleted=2";
        $this->params = array($product_id);
        $this->doResult();
        return $this->result;
    }

    //销售数量
    public function get_sell_count_by_user_id($user_id){
        $this->sql    = "select count(1) as num from orders o left join products p on o.product_id=p.id where p.user_id=? and o.status=3";
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

    //获取产品的具体信息
    public function get_product_info($product_id){
        $this->sql    = "select c.channel_name,c.platform,p.*,se.serv_name as sname,g.game_name,g.tags,g.product_img,pd.* from products p
        inner join game g on p.game_id=g.id left join product_discounts as pd on p.id=pd.product_id left join game_servs as se on se.id=p.serv_id left join channels as c on c.id=p.channel_id where p.id=?";
        $this->params = array($product_id);
        $this->doResult();
        return $this->result;
    }
    public function get_user_lock($user_id){
        $this->sql    = "select pay_lock from user_detail where user_id=?";
        $this->params = array($user_id);
        $this->doResult();
        return $this->result;
    }
    public function get_user_balance_detail($user_id,$type){
        $this->sql    = "select * from user_balance_detail where user_id=? and `type`=?";
        $this->params = array($user_id,$type);
        $this->doResultList();
        return $this->result;
    }

}