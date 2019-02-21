<?php
COMMON('beijingDao');
class game_pay_dao extends beijingDao {

    public function __construct() {
        parent::__construct();
        $this->mmc = new Memcache();
        $this->mmc->connect(MMCHOST, MMCPORT);
    }

    public function get_user_platform_num($user_id){
        $this->sql = "select * from platform_white_list where is_del='0' and user_id = ?";
        $this->params = array($user_id);
        $this->doResult();
        return $this->result;
    }

    public function get_user_bjb($user_id){
        $this->sql = "select * from platform_white_list where  is_del='0' and user_id = ? ";
        $this->params = array($user_id);
        $this->doResult();
        $data = $this->result;
        return $data;
    }

    //平台币锁定
    public function user_bjb_lock($status, $user_id){
        $this->sql = "update platform_white_list set bjb_lock=? where user_id=?";
        $this->params = array($status, $user_id);
        $this->doExecute();
    }

    public function update_user_bjb($order){
        $this->sql = "update platform_white_list set bjb=bjb-? where user_id=?";
        $this->params = array($order['bjb_num'], $order['buyer_id']);
        $this->doExecute();
        $this->user_bjb_lock(0, $order['buyer_id']);
    }

    //牛币日志
    public function bjb_log($guid,$order,$status){
        $this->sql = "insert into bjb_log(guid,user_id,do,amount,order_id,`from`,ip,add_time)values(?,?,?,?,?,?,?,?)";
        $this->params = array($guid, $order['buyer_id'], $status, $order['bjb_num'], $order['order_id'], 3, $order['ip'], strtotime("now"));
        $this->doInsert();
    }
}
?>
