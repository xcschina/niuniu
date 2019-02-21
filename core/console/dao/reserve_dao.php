<?php
COMMON('dao');
class reserve_dao extends Dao
{
    public function __construct()
    {
        parent::__construct();
        $this->mmc = new Memcache();
        $this->mmc->connect(MMCHOST, MMCPORT);
    }

    public function get_game(){
        $this->sql = "select game_name,id from game where is_del = 0 and `status`=1 order by id desc";
        $this->doResultList();
        return $this->result;
    }

    public function get_reserve($params,$page){
        $this->limit_sql = "select a.*,b.game_name from reserve_act_tb as a left join game as b on a.game_id = b.id where a.is_del = 0 ";
        if($params['game_id']){
            $this->limit_sql = $this->limit_sql." and a.game_id = " .$params['game_id'];
        }
        if($params['act_id']){
            $this->limit_sql = $this->limit_sql. " and a.id = ".$params['act_id'];
        }
        $this->limit_sql = $this->limit_sql." order by a.id desc";
        $this->doLimitResultList($page);
        return $this->result;
    }

    public function get_reserve_name(){
        $this->sql = "select `name`,id from reserve_act_tb where is_del = 0 order by id desc";
        $this->doResultList();
        return $this->result;
    }

    public function get_reserve_prize($act_id){
        $this->sql = "select * from reserve_act_draw where act_id = ?";
        $this->params = array($act_id);
        $this->doResult();
        return $this->result;
    }

    public function add_reserve($params){
        $this->sql = "insert into reserve_act_tb(`name`,game_id,virtual_num,down_url,activity_rules,start_time,end_time,share_title,share_msg,share_img,share_desc,undo_time,gifts_id) values(?,?,?,?,?,?,?,?,?,?,?,?,?)";
        $this->params = array($params['name'],$params['game_id'],$params['virtual_num'],$params['down_url'],$params['activity_rules'],strtotime($params['start_time']),strtotime($params['end_time']),$params['share_title'],$params['share_msg'],$params['share_img'],$params['share_desc'],strtotime($params['undo_time']),implode(",",$params['gift_id']));
        $this->doInsert();
        return $this->LAST_INSERT_ID;
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

    public function update_reserve($params){
        $this->sql = "update reserve_act_tb set `name`=?,game_id=?,virtual_num=?,down_url=?,activity_rules=?,start_time=?,end_time=?,share_title=?,share_msg=?,share_img=?,share_desc=?,undo_time=?,gifts_id=? where id = ?";
        $this->params = array($params['name'],$params['game_id'],$params['virtual_num'],$params['down_url'],$params['activity_rules'],strtotime($params['start_time']),strtotime($params['end_time']),$params['share_title'],$params['share_msg'],$params['share_img'],$params['share_desc'],strtotime($params['undo_time']),implode(",",$params['gift_id']),$params['id']);
        $this->doExecute();
    }

    public function get_reserve_info($id){
        $this->sql = "select * from reserve_act_tb where id = ? ";
        $this->params = array($id);
        $this->doResult();
        return $this->result;
    }

    public function get_prize($id){
        $this->sql = "select * from reserve_act_draw where act_id = ?";
        $this->params = array($id);
        $this->doResultList();
        return $this->result;
    }

    public function update_prize_channel($act_id, $params,$sort_id){
        $this->sql = "update reserve_act_draw set title=?,chance=?,`type`=?,prize_id=? where act_id=? and sort_id=?";
        $this->params = array($params['title'],$params['chance'],$params['type'],$params['prize_id'],$act_id,$sort_id);
        $this->doExecute();
    }

    public function prize_channel_add($act_id, $params, $sort_id){
        $this->sql = "insert into reserve_act_draw(act_id,sort_id,title,chance,`type`,prize_id) values(?,?,?,?,?,?) ";
        $this->params = array($act_id,$sort_id,$params['title'],$params['chance'],$params['type'],$params['prize_id']);
        $this->doInsert();
    }

    public function get_reserve_draw_log($id,$params,$page){
        $this->limit_sql = "select a.*,u.nick_name,u.mobile,d.title from reserve_draw_log as a left join user_info as u on a.user_id = u.user_id left join reserve_act_draw as d on a.draw_id = d.id where a.act_id = ?";
        if($params['mobile']){
            $this->limit_sql = $this->limit_sql." and u.mobile = ".$params['mobile'];
        }
        if($params['draw_type']){
            $this->limit_sql = $this->limit_sql." and a.draw_type = ".$params['draw_type'];
        }
        if($params['user_id']){
            $this->limit_sql = $this->limit_sql." and a.user_id = ".$params['user_id'];
        }
        $this->limit_sql = $this->limit_sql." order by a.id desc";
        $this->params = array($id);
        $this->doLimitResultList($page);
        return $this->result;
    }

    public function get_reserve_log($id,$params,$page){
        $this->limit_sql = "select a.*,u.nick_name,u.mobile from reserve_log as a left join user_info as u on a.user_id = u.user_id where a.act_id  = ?";
        if($params['mobile']){
           $this->limit_sql = $this->limit_sql." and u.mobile = ".$params['mobile'];
        }
        if($params['code']){
            $this->limit_sql = $this->limit_sql." and a.code = '".$params['code']."'";
        }
        if($params['user_id']){
            $this->limit_sql = $this->limit_sql." and a.user_id = ".$params['user_id'];
        }
        $this->limit_sql = $this->limit_sql." order by a.id desc";
        $this->params = array($id);
        $this->doLimitResultList($page);
        return $this->result;
    }

    public function del_reserve($id){
        $this->sql = "update reserve_act_tb set is_del = 1 where id = ?";
        $this->params = array($id);
        $this->doExecute();
    }

}