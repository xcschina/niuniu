<?php
COMMON('dao','randomUtils');
class website_dao extends Dao{

    public function __construct(){
        parent::__construct();
        $this->mmc = new Memcache();
        $this->mmc->connect(MMCHOST, MMCPORT);
    }

    public function get_order_list($user_id,$app_id){
        $data = $this->mmc->get('get_order_list'.$user_id."_".$app_id);
        if(!$data) {
            $this->sql = "select sum(pay_money) as pay_money from niuniu.orders where  buyer_id = ? and app_id = ? and status = 2";
            $this->params = array($user_id, $app_id);
            $this->doResult();
            $data = $this->result;
            $this->mmc->set('get_order_list'.$user_id."_".$app_id, $data, 1, 300);
        }
        return $data;
    }

    public function get_gift_log($user_id,$game_id){
        $data = $this->mmc->get('get_gift_log'.$user_id."_".$game_id);
        if(!$data) {
            $this->sql = "select id,batch_id from website_gift_log where user_id = ? and game_id = ?";
            $this->params = array($user_id,$game_id);
            $this->doResultList();
            $data = $this->result;
            $this->mmc->set('get_gift_log'.$user_id."_".$game_id, $data, 1, 300);
        }
        return $data;
    }

    public function  get_user_by_mobile($mobile){
        $this->sql="select * from user_info where  mobile = ?";
        $this->params=array($mobile);
        $this->doResult();
        return $this->result;
    }

    public function insert_login_log($user_id='',$account='',$login_ip,$login_pwd='',$source='',$browser='',$desc='',$status){
        $this->sql = "insert into user_login_log(user_id,account,login_ip,login_pwd,source,browser,`desc`,status,create_time) values (?,?,?,?,?,?,?,?,?)";
        $this->params = array($user_id,$account,$login_ip,$login_pwd,$source,$browser,$desc,$status,strtotime("now"));
        $this->doInsert();
    }

    public function get_all_gift_info($id){
        $this->sql = "select a.*,b.game_name,b.game_icon from game_gift_info as a INNER JOIN game as b on a.game_id=b.id where a.id=?";
        $this->params = array($id);
        $this->doResult();
        return $this->result;
    }

    public function get_gift($batch_id){
        $this->sql = "select a.*,b.title from game_gifts as a left join game_gift_info as b on b.id = a.batch_id where a.is_use=0 and a.batch_id=?";
        $this->params = array($batch_id);
        $this->doResult();
        return $this->result;
    }

    public function update_code_status($gift,$game_info,$ip,$game_id, $user_id){
        $this->sql = "update game_gifts set is_use=1,buyer_id=?,buy_time=? where id=?";
        $this->params = array($user_id, strtotime("now"), $gift['id']);
        $this->doExecute();

        $this->sql = "update game_gift_info set remain=remain-1 where id=?";
        $this->params = array($gift['batch_id']);
        $this->doExecute();

        $this->sql = "insert into website_gift_log(user_id,ip,role_level,money,game_id,batch_id,add_time,service_id,service_name,role_name) values(?,?,?,?,?,?,?,?,?,?)";
        $this->params = array($user_id,$ip,$game_info['RoleLevel'],$game_info['pay_money'],$game_id,$gift['batch_id'],time(),$game_info['AreaServerID'],$game_info['AreaServerName'],$game_info['RoleName']);
        $this->doExecute();
        $this->mmc->delete($this->mmc,'get_my_gift'.$user_id);
        $this->mmc->delete($this->mmc,'get_gift_info'.$user_id."_".$gift['batch_id']);
        $this->mmc->delete($this->mmc,'get_gift_log'.$user_id."_".$game_id);
    }

    public function get_reserve_ip_sum($ip,$batch_id){
        $this->sql = "select count(id) as num from website_gift_log where ip = ? and batch_id = ?";
        $this->params = array($ip,$batch_id);
        $this->doResult();
        return $this->result;
    }

    public function get_reserve_ip_code($ip,$batch_id){
        $data = $this->mmc->get('wl_gift_'.$batch_id."_".$ip);
        if(!$data) {
            $this->sql = "select * from website_gift_log where ip = ? and batch_id = ? and code != '' order by id desc limit 2";
            $this->params = array($ip,$batch_id);
            $this->doResultList();
            $data= $this->result;
            if(count($data)>1){
                $this->mmc->set('wl_gift_'.$batch_id."_".$ip, $data, 1, 3600);
            }
        }

        return $data;
    }

    public function insert_log($url,$ip,$pid){
        $this->sql = "insert into general_visit_log(ip,relation_id,add_time,url) values(?,?,?,?)";
        $this->params = array($ip,$pid,time(),$url);
        $this->doInsert();
    }

    public function get_gift_info($batch_id,$user_id,$game_id){
        $data = $this->mmc->get('get_gift_info'.$user_id."_".$batch_id);
        if(!$data) {
            $this->sql = "select * from website_gift_log where game_id = ? and user_id = ? and batch_id = ? ";
            $this->params = array($game_id, $user_id, $batch_id);
            $this->doResult();
            $data = $this->result;
            $this->mmc->set('get_gift_info'.$user_id."_".$batch_id, $data, 1, 300);
        }
        return $data;
    }

    public function get_my_gift($user_id,$game_id){
        $data = $this->mmc->get('get_my_gift'.$user_id);
        if(!$data){
            $this->sql = "select a.code as gift_code,b.title as draw_desc from `66173`.game_gifts as a left join `66173`.game_gift_info as b on a.batch_id = b.id left join website_gift_log as c on c.batch_id = a.batch_id where a.buyer_id = ? and c.user_id = ? and c.game_id = ?";
            $this->params = array($user_id,$user_id,$game_id);
            $this->doResultList();
            $data = $this->result;
            $this->mmc->set('get_my_gift'.$user_id, $data, 1, 300);
        }
        return $data;
    }

    public function get_memcache($user_id,$appid){
        return $this->mmc->get("get_my_games".$user_id."_".$appid);
    }

    public function set_memcache($data,$user_id,$appid){
        $this->mmc->set("get_my_games".$user_id."_".$appid,$data,1,300);
    }

    public function del_memcache($user_id,$appid){
        $this->mmc->delete("get_my_gift".$user_id);
        $this->mmc->delete("get_my_games".$user_id."_".$appid);
        $this->mmc->delete("get_gift_log".$user_id."_".$appid);
        $this->mmc->delete("get_order_list".$user_id."_".$appid);
    }

    public function get_game_info($id){
        $this->sql = "select * from game where status=1 and is_del=0 and id=?";
        $this->params=array($id);
        $this->doResult();
        return $this->result;
    }

    public function insert_open_log($act_id,$url,$code){
        $this->sql = "insert into reserve_url_log(act_id,code,url,`time`) VALUES(?,?,?,?)";
        $this->params = array($act_id,$code,$url,time());
        $this->doInsert();
    }

    public function get_user_visit($act_id,$code){
        $this->sql = "select * from reserve_url_log where act_id = ? and code = ?";
        $this->params = array($act_id,$code);
        $this->doResult();
        return $this->result;
    }

    public function get_game_articles($game_id){
        $data = $this->mmc->get("game_articles_".$game_id);
        if(!$data) {
            $this->sql = "select * from articles where part_id = ? and game_id = ? order by add_time desc limit 3";
            $this->params = array(18, $game_id);
            $this->doResultList();
            $data = $this->result;
            $this->mmc->set('game_articles_'.$game_id,$data,1,600);
        }
        return $data;
    }

    public function get_user_info($user_id){
        $this->sql = "select * from user_info where user_id = ?";
        $this->params = array($user_id);
        $this->doResult();
        return $this->result;
    }

    public function update_gift_status($gift,$ip,$game_id, $user_id){
        $this->sql = "update game_gifts set is_use=1,buyer_id=?,buy_time=? where id=?";
        $this->params = array($user_id, strtotime("now"), $gift['id']);
        $this->doExecute();

        $this->sql = "update game_gift_info set remain=remain-1 where id=?";
        $this->params = array($gift['batch_id']);
        $this->doExecute();

        $this->sql = "insert into website_gift_log(user_id,ip,game_id,batch_id,add_time,`code`) values(?,?,?,?,?,?)";
        $this->params = array($user_id,$ip,$game_id,$gift['batch_id'],time(),$gift['gift_code']);
        $this->doExecute();
        $this->mmc->delete($this->mmc,'get_my_gift'.$user_id);
        $this->mmc->delete($this->mmc,'get_gift_info'.$user_id."_".$gift['batch_id']);
    }

    public function get_gifts($batch_id,$user_id){
        $this->sql = "select * from game_gifts where buyer_id = ? and batch_id = ?";
        $this->params = array($user_id,$batch_id);
        $this->doResult();
        return $this->result;
    }

    public function get_gift_list($batch_id){
        $this->sql = "select a.code as gift_code,a.batch_id,a.id,b.title as draw_desc from game_gifts as a left join game_gift_info as b on b.id = a.batch_id where a.is_use=0 and a.batch_id=? ";
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
        $this->sql = " select a.title,a.lastupdate,a.id,b.name from articles a left join parts b on a.part_id = b.id where a.is_pub = 1 and a.game_id = ? order by a.lastupdate desc limit 4";
        $this->params = array($game_id);
        $this->doResultList();
        return $this->result;
    }

    public function get_article_list($game_id,$part_id){
        $this->sql = "select a.title,a.lastupdate,a.id,b.name from articles a left join parts b on a.part_id = b.id where a.is_pub = 1 and a.game_id = ? and a.part_id = ? order by a.lastupdate desc limit 4";
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

    public function insert_reserve_log($params,$gift,$ip,$app_id,$from){
        $this->sql = "insert into activity_reserve_log(mobile,add_time,system,`from`,ip,gift_id,app_id) values(?,?,?,?,?,?,?)";
        $this->params = array($params['mobile'],time(),$params['system'],$from,$ip,$gift['id'],$app_id);
        $this->doInsert();
    }

    public function get_article_info($id){
        $this->sql = "select a.*,b.game_name from articles a left join game b on a.game_id=b.id where a.id = ?";
        $this->params = array($id);
        $this->doResult();
        return $this->result;
    }

    public function get_share_num($act_id,$code){
        $this->sql = "select count(code) as num from reserve_url_log where act_id = ? and code = ?";
        $this->params = array($act_id,$code);
        $this->doResult();
        return $this->result;
    }
}