<?php
COMMON('dao','randomUtils');
class game_activity_dao extends Dao
{

    public function __construct()
    {
        parent::__construct();
    }

    public function game_name(){
        $this->sql = "select id,game_name from game where is_del=0 and `status`=1 order by id desc";
        $this->doResultList();
        return $this->result;
    }

    public function activity_list($params,$page){
        $this->limit_sql = "select a.*,g.game_name from activity_tb as a left join game as g on a.game_id=g.id where a.is_del = 0 ";
        if($params['game_id']){
            $this->limit_sql = $this->limit_sql." and a.game_id=".$params['game_id'];
        }
        $this->limit_sql = $this->limit_sql." order by a.id desc";
        $this->doLimitResultList($page);
        return $this->result;
    }

    public function get_gift_info($gift_id){
        $this->sql = "select title from game_gift_info where id = ? ";
        $this->params = array($gift_id);
        $this->doResult();
        return $this->result;
    }

    public function get_coupon_info($coupon_id){
        $this->sql = "select id,`name` from coupon_tb where id = ? ";
        $this->params = array($coupon_id);
        $this->doResult();
        return $this->result;
    }

    public function get_gift_list($game_id){
        $this->sql = "select * from game_gift_info where game_id=?";
        $this->params = array($game_id);
        $this->doResultList();
        return $this->result;
    }

    public function get_coupon_list($game_id){
        $this->sql = "select a.*,b.name from coupon_apply_type as a left join coupon_tb as b on a.coupon_id = b.id where a.game_id=?";
        $this->params = array($game_id);
        $this->doResultList();
        return $this->result;
    }

    public function get_full_list(){
        $this->sql = "select a.*,b.name from coupon_apply_type as a left join coupon_tb as b on a.coupon_id = b.id where a.apply_type = 1";
        $this->doResultList();
        return $this->result;
    }

    public function get_gift_remin($gift){
        $this->sql = "select * from game_gift_info where id = ?";
        $this->params = array($gift);
        $this->doResult();
        return $this->result;
    }

    public function get_coupon_num($coupon_id){
        $this->sql = "select count(*) as num from coupon_user_log_tb where coupon_id =? and receive_time is NULL";
        $this->params = array($coupon_id);
        $this->doResult();
        return $this->result;
    }

    public function add_activity($params){
        $this->sql = "insert into activity_tb(activity_name,game_id,gift_id,coupon_id,pc_img,m_img,box_img,down_url,activity_rules,start_time,end_time,share_title,share_msg,share_desc,full_id) values (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?) ";
        $this->params = array($params['activity_name'],$params['game_id'],implode(",",$params['gift_id']),implode(",",$params['coupon_id']),$params['pc_img'],$params['m_img'],$params['box_img'],$params['down_url'],$params['activity_rules'],strtotime($params['start_time']),strtotime($params['end_time']),$params['share_title'],$params['share_msg'],$params['share_desc'],implode(",",$params['full_id']));
        $this->doInsert();
        return $this->LAST_INSERT_ID;
    }

    public function activity_info($id){
        $this->sql = "select a.*,g.game_name from activity_tb as a left join game as g on a.game_id = g.id where a.id = ?";
        $this->params = array($id);
        $this->doResult();
        return $this->result;
    }

    public function update_activity($params){
        $this->sql = "update activity_tb set activity_name=?,game_id=?,gift_id=?,coupon_id=?,pc_img=?,m_img=?,box_img=?,down_url=?,activity_rules=?,start_time=?,end_time=?,share_title=?,share_msg=?,share_desc=?,full_id=? where id = ? ";
        $this->params = array($params['activity_name'],$params['game_id'],implode(",",$params['gift_id']),implode(",",$params['coupon_id']),$params['pc_img'],$params['m_img'],$params['box_img'],$params['down_url'],$params['activity_rules'],strtotime($params['start_time']),strtotime($params['end_time']),$params['share_title'],$params['share_msg'],$params['share_desc'],implode(",",$params['full_id']),$params['id']);
        $this->doExecute();
        return $this->result;
    }

    public function del_activity($id){
        $this->sql = "update activity_tb set is_del=1 where id=?";
        $this->params = array($id);
        $this->doExecute();
        return $this->result;
    }
}