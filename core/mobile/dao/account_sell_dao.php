<?php
COMMON('dao','randomUtils');
class account_sell_dao extends Dao{

    public function __construct(){
        parent::__construct();
        $this->mmc = new Memcache();
        $this->mmc->connect(MMCHOST, MMCPORT);
    }

    public function get_channels(){
        $data = $this->mmc->get("channels");
        if(!$data){
            $this->sql = "select * from channels";
            $this->params = array();
            $this->doResultList();
            $data = $this->result;
            $this->mmc->set("channels", $data, 1 ,600);
        }
        return $data;
    }

    public function get_stock_products($user_id){
        $this->sql = "select a.*,b.game_name,c.serv_name,d.channel_name from products as a inner join game as b
                    on a.game_id=b.id left JOIN game_servs as c on a.serv_id=c.id left join channels as d
                    on a.channel_id=d.id where a.user_id=? and a.is_pub<>1 and a.is_pub<>4 order by lastupdate desc";
        $this->params = array($user_id);
        $this->doResultList();
        return $this->result;
    }

    public function get_audit_products($user_id){
        $this->sql = "select a.*,b.game_name,c.serv_name,d.channel_name from products as a inner join game as b
                    on a.game_id=b.id left JOIN game_servs as c on a.serv_id=c.id left join channels as d
                    on a.channel_id=d.id where a.user_id=? and a.is_pub=2 order by lastupdate desc";
        $this->params = array($user_id);
        $this->doResultList();
        return $this->result;
    }

    public function get_unput_products($user_id){
        $this->sql = "select a.*,b.game_name,c.serv_name,d.channel_name from products as a inner join game as b
                    on a.game_id=b.id left JOIN game_servs as c on a.serv_id=c.id left join channels as d
                    on a.channel_id=d.id where a.user_id=? and a.is_pub=0 order by lastupdate desc";
        $this->params = array($user_id);
        $this->doResultList();
        return $this->result;
    }

    public function get_pub_products($user_id){
        $this->sql = "select a.*,b.game_name,c.serv_name,d.channel_name from products as a inner join game as b
                    on a.game_id=b.id left JOIN game_servs as c on a.serv_id=c.id left join channels as d
                    on a.channel_id=d.id where a.user_id=? and a.is_pub=1 order by lastupdate desc";
        $this->params = array($user_id);
        $this->doResultList();
        return $this->result;
    }

    public function get_sell_orders($user_id,$page){
        $this->limit_sql = "select o.*,b.game_name,d.channel_name,c.serv_name from orders as o inner join products as p on o.product_id=p.id left join game as b on o.game_id=b.id
                            left JOIN game_servs as c on o.serv_id=c.id left join channels as d on o.game_channel=d.id where p.user_id=? and o.status>0";
        $this->limit_sql = $this->limit_sql." order by o.id desc";
        $this->params = array($user_id);
        $this->doLimitResultList($page);
        return $this->result;
    }

    public function get_order_info($id, $user_id){
        $this->sql = "select o.*,p.intro,b.game_name,d.channel_name,c.serv_name from orders as o inner join products as p on o.product_id=p.id left join game as b on o.game_id=b.id
                      left JOIN game_servs as c on o.serv_id=c.id left join channels as d on o.game_channel=d.id where o.id=? and p.user_id=? and o.status>0";
        $this->params = array($id, $user_id);
        $this->doResult();
        return $this->result;
    }

    public function get_sell_info($user_id, $id){
        $this->sql = "select a.*,b.game_name,b.game_units,b.game_storages,c.serv_name,d.channel_name,f.game_user,f.game_pwd,f.role_name,f.role_level,
                    f.game_user_lock,f.qq,f.tel from products as a inner join game as b
                    on a.game_id=b.id left JOIN game_servs as c on a.serv_id=c.id left join channels as d
                    on a.channel_id=d.id left join sell_extra as f on a.id=f.product_id where a.user_id=? and a.id=?";
        $this->params = array($user_id, $id);
        $this->doResult();
        return $this->result;
    }

    public function get_product_imgs($product_id){
        $this->sql = "select * from product_imgs where product_id=?";
        $this->params = array($product_id);
        $this->doResultList();
        return $this->result;
    }

    public function update_sell_info($id, $sell_info, $sell){
        $time = strtotime("now");
        $this->sql = "update products set title=?,game_id=?,channel_id=?,serv_id=?,stock=?,price=?,intro=?,game_storage=?,unit=?,is_pub=?,end_time=?,overdue=?,pernum=? where id=?";
        $this->params = array($sell_info['title'], $sell_info['game_id'], $sell_info['ch'], $sell['serv_id'],
            $sell_info['stock'], $sell_info['price'], $sell_info['desc'], $sell_info['game_storage'],
            $sell_info['unit'], 2,$time+$sell['overdue']*24*3600, $sell['overdue'], $sell_info['pernum'], $id);
        $this->doExecute();
    }

    public function update_sell_extra($id, $sell){
        $this->sql = "update sell_extra set game_user=?, game_pwd=?, role_name=?, role_level=?, game_user_lock=?, tel=?, qq=? where product_id=?";
        $this->params = array($sell['game_user'], $sell['game_pwd'], $sell['role_name'], $sell['role_level'], $sell['game_user_lock'],
            $sell['tel'], $sell['qq'], $id);
        $this->doExecute();
    }

    public function delete_sell_imgs($id){
        $this->sql = "delete from product_imgs where product_id=?";
        $this->params = array($id);
        $this->doExecute();
    }

    public function insert_sell_imgs($img, $sell_id){
        $this->sql = "insert into product_imgs(product_id, img_url)values(?,?)";
        $this->params = array($sell_id, $img);
        $this->doExecute();
    }

    public function update_undo_audit($id){
        $this->sql = "update products set is_pub=0 where id=?";
        $this->params = array($id);
        $this->doExecute();
    }

    public function delete_sell($id){
        $this->sql = "update products set is_pub=4 where id=?";
        $this->params = array($id);
        $this->doExecute();
    }

    //审核记录
    public function get_sell_audit($prodct_id){
        $this->sql = "SELECT * FROM `sell_audit_logs` where product_id=?";
        $this->params = array($prodct_id);
        $this->doResult();
        return $this->result;
    }
}
