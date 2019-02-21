<?php
COMMON('dao','niuniuDao');
class ios_pay_dao extends niuniuDao {

	public function __construct() {
		parent::__construct();
        $this->mmc = new Memcache();
        $this->mmc->connect(MMCHOST, MMCPORT);
	}

    public function get_app_info($app_id){
        $data = $this->mmc->get("app_info".$app_id);
        if (!$data) {
            $this->sql = "select * from apps where app_id=?";
            $this->params = array($app_id);
            $this->doResult();
            $data = $this->result;
            $this->mmc->set("app_info".$app_id, $data, 1,3600);
        }
        return $data;
    }

    public function get_money_info($app_id,$money_id){
        $data = $this->mmc->get("money_info_".$app_id."_".$money_id);
        if (!$data) {
            #$this->sql = "select b.app_name,a.* from app_goods as a inner join apps as b on a.app_id=b.app_id where a.id=? and a.status=1 order by good_price";
            $this->sql = "select b.app_name,a.* from app_goods as a inner join apps as b on a.app_id=b.app_id where a.app_id=? and a.good_code=? and a.status=1 order by good_price";
            $this->params = array($app_id, $money_id);
            $this->doResult();
            $data = $this->result;
            $this->mmc->set("money_info_".$app_id."_".$money_id, $data, 1,3600);
        }
        return $data;
    }

    public function get_apple_info($app_id,$apple_id){
        $data = $this->mmc->get("app_info_".$apple_id);
        if(!$data){
            $this->sql = "select a.*,a.game_name as app_name,b.web_url from app_ios_pack a left join apps b on a.app_id = b.app_id where a.app_id =? and a.channel=? ";
            $this->params = array($app_id,$apple_id);
            $this->doResult();
            $data = $this->result;
            $this->mmc->set("app_info_".$apple_id,$data,1,3600);
        }
        return $data;
    }

    public function get_apple_id_info($app_id,$apple_id){
        $data = $this->mmc->get("apple_id_info_".$apple_id);
        if(!$data){
            $this->sql = "select * from app_ios_pack  where app_id =? and apple_id=? ";
            $this->params = array($app_id,$apple_id);
            $this->doResult();
            $data = $this->result;
            $this->mmc->set("apple_id_info_".$apple_id,$data,1,3600);
        }
        return $data;
    }

    public function get_money_by_goodid($goods_id){
        $data = $this->mmc->get("money_goods_id_".$goods_id);
        if (!$data) {
            $this->sql = "select * from app_goods WHERE status = '1' and id= ?";
            $this->params = array($goods_id);
            $this->doResult();
            $data = $this->result;
            $this->mmc->set("money_goods_id_".$goods_id, $data, 1,3600);
        }
        return $data;
    }


    public function get_appstore_orders(){
        $time = time()-5;
        $this->sql = "select * from apple_order where status = 1 and pay_time < ? order by pay_time asc";
        $this->params = array($time);
        $this->doResultList();
        $data = $this->result;
        return $data;
    }

    public function get_appstore_order($id){
        $this->sql = "select * from apple_order where status = 1 and id=? ";
        $this->params = array($id);
        $this->doResult();
        $data = $this->result;
        return $data;
    }

    public function up_order_status($order_id, $status){
        $this->sql = "update orders set status=? where order_id=?";
        $this->params = array($status, $order_id);
        $this->doExecute();
    }

    public function get_apple_payed_orders(){
        $time = time()-10;
        $this->sql = "SELECT b.sdk_charge_url,b.app_key,a.*,c.good_code FROM `apple_order` as a inner join apps as b on a.app_id=b.app_id 
                      inner join app_goods as c on a.goods_id=c.id where a.status=2 and a.charge_time < ? order by a.charge_time asc";
        $this->params = array($time);
        $this->doResultList();
        return $this->result;
    }

    public function get_apple_payed_order($id){
        $this->sql = "SELECT b.sdk_charge_url,b.app_key,a.*,c.good_code FROM `apple_order` as a inner join apps as b on a.app_id=b.app_id 
                      inner join app_goods as c on a.goods_id=c.id where a.status=2 and a.id=? ";
        $this->params = array($id);
        $this->doResult();
        return $this->result;
    }


    public function update_apple_order_charge($id){
        $this->sql = "update apple_order set charge_time=? where id=?";
        $this->params = array(strtotime("now"), $id);
        $this->doExecute();
    }

    public function update_order_status($id, $status){
        $this->sql = "update apple_order set status=?,charge_time=? where id=?";
        $this->params = array($status,strtotime("now"), $id);
        $this->doExecute();
    }


    public function update_apple_order_status($status,$app_status,$sandbox,$id){
        $this->sql = "update apple_order set status=?,apple_status=?,charge_time=?,sandbox=? where id=? ";
        $this->params = array($status,$app_status,time(),$sandbox, $id);
        $this->doExecute();
    }

    public function get_apple_orders_info($params){
        $this->sql = "select * from apple_order where cp_order_id=? and order_id=?";
        $this->params = array($params['cp_order_id'],$params['niu_order_id']);
        $this->doResult();
        $data = $this->result;
        return $data;
    }

    public function get_apple_orders_id($params){
        $this->sql = "select * from apple_order where apple_order_id=?";
        $this->params = array($params['apple_order_id']);
        $this->doResult();
        $data = $this->result;
        return $data;
    }

    public function get_apple_receipt_md5($receipt_md5){
        $data = $this->mmc->get("receipt_md5_".$receipt_md5);
        if (!$data) {
            $this->sql = "select * from apple_order where receipt_md5=?";
            $this->params = array($receipt_md5);
            $this->doResult();
            $data = $this->result;
            $this->mmc->set("receipt_md5_".$receipt_md5, $data, 1, 3600);
        }
        return $data;
    }

    public function get_apple_order_id($apple_order_id){
        $this->sql = "select * from apple_order where apple_order_id=? order by `status` desc ";
        $this->params = array($apple_order_id);
        $this->doResult();
        $data = $this->result;
        return $data;
    }

    public function update_apple_order($id,$params,$status){
        $this->sql = "update apple_order set apple_order_id=?,`status`=?,`receipt`=?,`receipt_md5`=?,pay_time=? where id=?";
        $this->params = array($params['apple_order_id'],$status,$params['receipt'],$params['receipt_md5'],time(), $id);
        $this->doExecute();
    }


    public function create_apple_order($order){
        $this->sql = "insert into apple_order(app_id,order_id,cp_order_id,buyer_id,serv_id,role_id,role_name,title,goods_id,pay_money,ip,channel,buy_time,`status`,payExpandData,apple_id,sid,sid_md5,idfa)values(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";
        $this->params = array_values($order);
        $this->doInsert();
        return $this->LAST_INSERT_ID;
    }

    public function set_usr_session($key, $data){
        $session_data = $this->mmc->get("mpay-session-".session_id());
        $session_data[$key] = $data;
        $this->mmc->set("mpay-session-".session_id(), $session_data, 1, 300);
    }

    public function get_usr_session($key){
        $session_data = $this->mmc->get("mpay-session-".session_id());
        if($key){
            return isset($session_data[$key])?$session_data[$key]:'';
        }else{
            return $session_data;
        }
    }

    public function get_order_debug_info(){
        $data = $this->mmc->get("order_debug_info");
        return $data;
    }

    public function get_black_sid($sid){
        $data = $this->mmc->get("black_sid_".md5($sid));
        if(!$data){
            $this->sql = "select * from black_device where sid_md5=?";
            $this->params = array(md5($sid));
            $this->doResult();
            $data = $this->result;
            $this->mmc->set("black_sid_".md5($sid), $data, 1, 86400);
        }
        return $data;
    }

    public function verify_sid_count($sid){
        $t = time();
        $start = mktime(0,0,0,date("m",$t),date("d",$t),date("Y",$t));
        $data = $this->mmc->get("sid_verify_".md5($sid).'_'.$start);
        if(!$data){
            $this->sql = "select count(distinct(buyer_id)) as count from apple_order where sid_md5=? and `status` > 1 and buy_time > ? and buy_time <= ?";
            $this->params = array(md5($sid),$start,$t);
            $this->doResult();
            $data = $this->result;
            if($data['count'] > 5){
                $this->mmc->set("sid_verify_".md5($sid).'_'.$start, $data, 1, 86400);
            }
        }
        return $data;
    }

    public function add_black_sid($sid,$idfa){
        $this->sql = "insert into black_device(sid,sid_md5,add_time,idfa)values(?,?,?,?)";
        $this->params = array($sid,md5($sid),time(),$idfa);
        $this->doInsert();
        return $this->LAST_INSERT_ID;
    }
}
