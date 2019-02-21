<?php
COMMON('dao','randomUtils');
class website_dao extends Dao{

    public function __construct(){
        parent::__construct();
        $this->mmc = new Memcache();
        $this->mmc->connect(MMCHOST, MMCPORT);
    }

    public function get_gift($batch_id){
        $this->sql = "select a.code,a.batch_id,a.id,b.title from game_gifts as a left join game_gift_info as b on b.id = a.batch_id where a.is_use=0 and a.batch_id=?";
        $this->params = array($batch_id);
        $this->doResult();
        return $this->result;
    }

    public function get_reserve_num(){
        $this->sql = "select count(*) as num from activity_reserve_log";
        $this->doResult();
        return $this->result;
    }

    public function get_new_article($game_id){
        $this->sql = " select a.title,a.lastupdate,a.id,b.name from articles a left join parts b on a.part_id = b.id where a.game_id = ? order by a.lastupdate desc limit 4";
        $this->params = array($game_id);
        $this->doResultList();
        return $this->result;
    }

    public function get_article_list($game_id,$part_id){
        $this->sql = "select a.title,a.lastupdate,a.id,b.name from articles a left join parts b on a.part_id = b.id where a.game_id = ? and a.part_id = ? order by a.lastupdate desc limit 4";
        $this->params = array($game_id,$part_id);
        $this->doResultList();
        return $this->result;
    }

    public function get_reserve_ip_log($ip,$start,$end){
        $this->sql = "select count(id) as num from activity_reserve_log where ip = ? and add_time >= ? and add_time <= ?";
        $this->params = array($ip,$start,$end);
        $this->doResult();
        return $this->result;
    }

    public function get_reserve_gift_info($params){
        $this->sql = "select a.*,b.code from activity_reserve_log a left join game_gifts b on a.gift_id = b.id where a.system = ? and a.mobile = ?";
        $this->params = array($params['system'],$params['mobile']);
        $this->doResult();
        return $this->result;
    }

    public function update_gift_code_status($gift){
        $this->sql = "update game_gifts set is_use=1,buyer_id=?,buy_time=? where id=?";
        $this->params = array(-1,strtotime("now"), $gift['id']);
        $this->doExecute();

        $this->sql = "update game_gift_info set remain=remain-1 where id=?";
        $this->params = array($gift['batch_id']);
        $this->doExecute();
    }

    public function insert_reserve_log($params,$gift,$ip,$app_id){
        $this->sql = "insert into activity_reserve_log(mobile,add_time,system,`from`,ip,gift_id,app_id) values(?,?,?,?,?,?,?)";
        $this->params = array($params['mobile'],time(),$params['system'],2,$ip,$gift['id'],$app_id);
        $this->doInsert();
    }

    public function get_article_info($id){
        $this->sql = "select a.*,b.game_name from articles a left join game b on a.game_id=b.id where a.id = ?";
        $this->params = array($id);
        $this->doResult();
        return $this->result;
    }
}