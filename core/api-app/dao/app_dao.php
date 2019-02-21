<?php
COMMON('dao');
class app_dao extends Dao {

	public function __construct() {
		parent::__construct();
		$this->mmc = new Memcache();
		$this->mmc->connect(MMCHOST, MMCPORT);
	}

	public function get_game($id){
		$this->sql = "select * from game where status=1 and is_del=0 and id=?";
		$this->params = array($id);
		$this->doResult();
		$games = $this->result;
		return $games;
	}

	//获取渠道列表
	public function get_channels_list(){
		$this->sql="select * from channels ";
		$this->doResultList();
		return $this->result;
	}

	public function get_game_channels($game_id){
		$this->sql = "select * from channel_discount where game_id=?";
		$this->params = array($game_id);
		$this->doResult();
		return $this->result;
	}

	public function get_channel_info($ch_id){
		$this->sql = "select * from channels where id=?";
		$this->params = array($ch_id);
		$this->doResult();
		return $this->result;
	}

	public function get_channel_discount($game_id){
		$this->sql = "select * from channel_discount where game_id=?";
		$this->params = array($game_id);
		$this->doResult();
		return $this->result;
	}

	public function game_products_list($game_id,$type){
		$this->sql = "select * from products where game_id=? and `type`=? and is_pub=1 order by price ";
		$this->params = array($game_id,$type);
		$this->doResultList();
		return $this->result;
	}

	public function app_game_info($game_id){
		$this->sql = "select * from app_game_tb where game_id=? and is_del=0 ";
		$this->params = array($game_id);
		$this->doResult();
		return $this->result;
	}

	public function get_products_info($game_id,$id,$type){
		$this->sql = "select * from products where game_id=? and id=? and `type`=? and is_pub=1";
		$this->params = array($game_id,$id,$type);
		$this->doResult();
		return $this->result;
	}

	public function get_game_introduce_byid($id){
		$this->sql="select * from game_introduce_tb where game_id=?";
		$this->params=array($id);
		$this->doResult();
		return $this->result;
	}

	public function get_product_info($product_id){
		$this->sql="select p.*,g.game_name,g.tags,pd.* from products p
                    inner join game g on p.game_id=g.id left join product_discounts as pd on p.id=pd.product_id where p.id=?";
		$this->params=array($product_id);
		$this->doResult();
		return $this->result;
	}

	public function insert_order($order){
		$this->sql = "insert into orders(order_id,title,buyer_id,product_id,amount,money,unit_price,pay_money,game_id,serv_id,
                                    game_channel,seller_id,status,buy_time,pay_channel,qq,tel,discount,discount_in,role_name,
                                    role_back_name,service_id,game_user,game_pwd,platform,is_rand_user,attr,is_agent,role_level,reduce_product,coupon_id)
                                    values(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";
		$this->params = array_values($order);
		$this->err_log(var_export($this->params,1),'66app_order');
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

	public function get_service(){
		$this->sql = "SELECT * FROM `admins` WHERE `group`='vip' AND is_del=0 AND last_service_time>0 order by last_service_time limit 1";
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

	public function get_ser_list($game_id,$ch_id){
		$data = $this->mmc->get("get_master_game_ch_servs".$game_id."-".$ch_id);
		if(!$data){
			$this->sql = "select id,serv_name from game_servs where game_id=? and ch_".$ch_id."=1 order by id desc";
			$this->params = array($game_id);
			$this->doResultList();
			$data = $this->result;
			$this->mmc->set("get_master_game_ch_servs".$game_id."-".$ch_id, $data, 1 ,600);
		}
		return $data;
	}

	public function check_game_user($game_user){
		$this->sql = "select a.game_id,a.tel,a.qq,a.game_user,a.role_name,a.serv_id,a.game_channel as ch_id,c.serv_name from orders as a
						inner join products as b on a.product_id=b.id inner join game_servs as c on a.serv_id=c.id
						where a.game_user=? and b.type=1 and a.status=2 ";
		$this->params = array($game_user);
		$this->doResult();
		$data = $this->result;
		return $data;

		$data = memcache_get($this->mmc, 'game_user_'.$game_user);
		if(!$data){
			$this->sql = "select a.game_id,a.tel,a.qq,a.game_user,a.role_name,a.serv_id,a.game_channel as ch_id,c.serv_name from orders as a
						inner join products as b on a.product_id=b.id inner join game_servs as c on a.serv_id=c.id
						where a.game_user=? and b.type=1 and a.status=2 ";
			$this->params = array($game_user);
			$this->doResult();
			$data = $this->result;
			$this->mmc->set("game_user_".$game_user, $data, 1 ,600);
		}
		return $data;

	}
}
?>