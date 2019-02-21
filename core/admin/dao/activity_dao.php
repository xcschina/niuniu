<?php
COMMON('niuniuDao');
class activity_dao extends niuniuDao
{

    public function __construct()
    {
        parent::__construct();
        $this->mmc = new Memcache();
        $this->mmc->connect(MMCHOST, MMCPORT);
        $this->TB_NAME = "admins";
    }

    public function get_activity_list($page,$params){
        $this->limit_sql = "select * from guild_reserve_tb where 1=1 ";
        if($params['is_del'] && is_numeric($params['is_del']) || $params['is_del'] ==="0"){
            $this->limit_sql = $this->limit_sql." and is_del = ".$params['is_del'];
        }
        if($params['act_id']){
            $this->limit_sql = $this->limit_sql." and id = ".$params['act_id'];
        }
        $this->limit_sql = $this->limit_sql." order by id desc";
        $this->doLimitResultList($page);
        return $this->result;
    }

    public function get_guild_activity($page,$params){
        $this->limit_sql = "select * from guild_reserve_tb where is_del = 0 ";
        if($params['act_id']){
            $this->limit_sql = $this->limit_sql." and id = ".$params['act_id'];
        }
        $this->limit_sql = $this->limit_sql." order by id desc";
        $this->doLimitResultList($page);
        return $this->result;
    }

    public function get_activity_name(){
        $this->sql = "select * from guild_reserve_tb ";
        $this->doResultList();
        return $this->result;
    }

    public function get_user_info($user_id){
        $this->sql = "select * from admins where id = ?";
        $this->params = array($user_id);
        $this->doResult();
        return $this->result;
    }

    public function get_list($user_id){
        $this->sql = "select * from admins where (p1 = ? or p2 = ?)";
        $this->params = array($user_id,$user_id);
        $this->doResultList();
        return $this->result;
    }

    public function get_game(){
        $this->sql = "select * from apps where status = 1";
        $this->doResultList();
        return $this->result;
    }

    public function get_gift_list(){
        $this->sql = "select * from `66173`.game_gift_info where is_del = 0";
        $this->doResultList();
        return $this->result;
    }

    public function get_related_guild($game_ac){
        $this->sql = "select * from guild_reserve_tb where game_ac = ? ";
        $this->params = array($game_ac);
        $this->doResult();
        return $this->result;
    }

    public function get_related_guild_info($game_ac,$id){
        $this->sql = "select * from guild_reserve_tb where game_ac = ? and id !=?";
        $this->params = array($game_ac,$id);
        $this->doResult();
        return $this->result;
    }

    public function get_guild(){
        $this->sql = "select * from admins where is_del = 0 and group_id = 10";
        $this->doResultList();
        return $this->result;
    }

    public function add_activity($params){
        $this->sql = "insert into guild_reserve_tb(title,game_id,num,rule,start_time,end_time,share_title,share_msg,share_img,share_desc,undo_time,gift_id,related_guild,game_ac,add_time,is_del) values(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";
        $this->params = array($params['title'],$params['game_id'],$params['num'],$params['rule'],strtotime($params['start_time']),strtotime($params['end_time']),$params['share_title'],$params['share_msg'],$params['share_img'],$params['share_desc'],strtotime($params['undo_time']),$params['gift_id'],$params['related_guild'],$params['game_ac'],time(),$params['is_del']);
        $this->doInsert();
        return $this->LAST_INSERT_ID;
    }

    public function add_rank($act_id,$params){
        $this->sql = "insert into guild_reserve_rank(act_id,guild_id,reserve_num,add_time,last_time) values(?,?,?,?,?)";
        $this->params = array($act_id,$params['related_guild'],$params['num'],time(),time());
        $this->doInsert();
    }

    public function get_activity_info($id){
        $this->sql = "select a.*,b.real_name from guild_reserve_tb as a left join admins as b on a.related_guild = b.id where a.id = ?";
        $this->params = array($id);
        $this->doResult();
        return $this->result;
    }

    public function get_reserve_rank($id,$guild_id){
        $this->sql = "select * from guild_reserve_rank where act_id = ? and guild_id = ?";
        $this->params = array($id,$guild_id);
        $this->doResult();
        return $this->result;
    }

    public function update_rank($id,$params){
        $this->sql = "update guild_reserve_rank set reserve_num = ?,last_time = ? where act_id = ? and guild_id = ?";
        $this->params = array($params['num'],time(),$id,$params['related_guild']);
        $this->doExecute();
    }

    public function update_activity($id,$params){
        $this->sql = "update guild_reserve_tb set title=?,game_id=?,num=?,rule=?,start_time=?,end_time=?,share_title=?,share_msg=?,share_img=?,share_desc=?,undo_time=?,gift_id=?,related_guild=?,game_ac=?,is_del=? where id =?";
        $this->params = array($params['title'],$params['game_id'],$params['num'],$params['rule'],strtotime($params['start_time']),strtotime($params['end_time']),$params['share_title'],$params['share_msg'],$params['share_img'],$params['share_desc'],strtotime($params['undo_time']),$params['gift_id'],$params['related_guild'],$params['game_ac'],$params['is_del'],$id);
        $this->doExecute();
    }

    public function get_reserve_log($guild_id,$page,$params){
        $this->limit_sql = "select a.*,b.nick_name,b.mobile,c.title,c.game_ac from guild_reserve_log as a left join `66173`.user_info as b on a.user_id = b.user_id left join guild_reserve_tb as c on a.parent_id = c.id where 1=1";
        if($guild_id){
            $this->limit_sql = $this->limit_sql." and a.guild_id in (".$guild_id.")";
        }
        if($params['activity_id']){
            $this->limit_sql = $this->limit_sql. " and a.parent_id = ".$params['activity_id'];
        }
        if($params['ip']){
            $this->limit_sql = $this->limit_sql." and a.ip = '".$params['ip']."'";
        }
        if($params['start'] && $params['end']){
            $this->limit_sql = $this->limit_sql ." and a.add_time >= ".strtotime($params['start'])." and a.add_time <= ".strtotime($params['end']);
        }else if($params['start'] && !$params['end']) {
            $this->limit_sql = $this->limit_sql ." and a.add_time >= ".strtotime($params['start']);
        } else if(!$params['start'] && $params['end']) {
            $this->limit_sql = $this->limit_sql ." and a.add_time <= ".strtotime($params['end']);
        }
        $this->limit_sql = $this->limit_sql." order by a.id desc";
        $this->doLimitResultList($page);
        return $this->result;
    }

    public function get_reserve_info($id){
        $this->sql = "select a.num,b.guild_id,b.activity_id,c.user_code from guild_reserve_tb as a left join guild_reserve_review as b on a.id = b.activity_id left join admins as c on c.id = b.guild_id where b.id = ?";
        $this->params = array($id);
        $this->doResult();
        return $this->result;
    }

    public function get_reserve_review($id,$guild_id){
        $this->sql = "select * from guild_reserve_review where activity_id = ? and guild_id = ? order by id desc";
        $this->params = array($id,$guild_id);
        $this->doResult();
        return $this->result;
    }

    public function insert_reserve_review($act,$guild){
        $this->sql = "insert into guild_reserve_review(guild_id,activity_id,status,add_time) value(?,?,?,?) ";
        $this->params = array($guild['id'],$act['id'],1,time());
        $this->doInsert();

    }

    public function insert_reserve_list($info){
        $this->sql = "insert into guild_reserve_list(activity_id,guild_id,guild_code,num,add_time,is_del) value(?,?,?,?,?,?) ";
        $this->params = array($info['activity_id'],$info['guild_id'],$info['user_code'],$info['num'],time(),0);
        $this->doInsert();

        $this->sql = "insert into guild_reserve_rank(act_id,guild_id,reserve_num,add_time,last_time) value(?,?,?,?,?)";
        $this->params = array($info['activity_id'],$info['guild_id'],0,time(),time());
        $this->doInsert();
    }

    public function get_reserve_list($page,$params){
        $this->limit_sql = "select a.*,b.title,c.real_name,c.img,c.guild_name from guild_reserve_review as a left join guild_reserve_tb as b on a.activity_id = b.id left join admins as c on a.guild_id = c.id where 1=1";
        if($params['status']){
            $this->limit_sql = $this->limit_sql." and a.`status` = ".$params['status'];
        }
        if($params['activity_id']){
            $this->limit_sql = $this->limit_sql." and a.activity_id = ".$params['activity_id'];
        }
        if($params['guild_id']){
            $this->limit_sql = $this->limit_sql." and a.guild_id = ".$params['guild_id'];
        }
        $this->limit_sql = $this->limit_sql." order by a.id desc";
        $this->doLimitResultList($page);
        return $this->result;
    }

    public function get_guild_info($id,$guild_id){
        $this->sql = "select * from guild_reserve_list where activity_id = ? and guild_id = ?";
        $this->params = array($id,$guild_id);
        $this->doResult();
        return $this->result;
    }

    public function get_review_list($page,$params){
        $this->limit_sql = "select a.*,b.title,c.real_name,c.img,c.guild_name from guild_reserve_list as a left join guild_reserve_tb as b on a.activity_id = b.id left join admins as c on a.guild_id = c.id where 1=1";
        if($params['is_del'] && is_numeric($params['is_del']) || $params['is_del'] === "0"){
            $this->limit_sql = $this->limit_sql." and a.is_del = ".$params['is_del'];
        }
        if($params['activity_id']){
            $this->limit_sql = $this->limit_sql." and a.activity_id = ".$params['activity_id'];
        }
        if($params['guild_id']){
            $this->limit_sql = $this->limit_sql." and a.guild_id = ".$params['guild_id'];
        }
        $this->limit_sql = $this->limit_sql." order by a.id desc";
        $this->doLimitResultList($page);
        return $this->result;
    }

    public function get_review($page,$params,$guild_id){
        $this->limit_sql = "select a.*,b.title,c.real_name,c.img,c.guild_name from guild_reserve_review as a left join guild_reserve_tb as b on a.activity_id = b.id left join admins as c on a.operator_id = c.id where a.guild_id in (".$guild_id.")";
        if($params['status']){
            $this->limit_sql = $this->limit_sql." and a.`status` = ".$params['status'];
        }
        if($params['activity_id']){
            $this->limit_sql = $this->limit_sql." and a.activity_id = ".$params['activity_id'];
        }
        $this->limit_sql = $this->limit_sql." order by a.id desc";
        $this->doLimitResultList($page);
        return $this->result;
    }

    public function update_reserve($id,$params,$guild_id){
        $this->sql = "update guild_reserve_review set status=?,operator_id=?,reason=?,operator_time=? where id = ? ";
        $this->params = array($params['status'],$guild_id,$params['reason'],time(),$id);
        $this->doExecute();
    }

    public function get_reserve($id){
        $this->sql = "select a.*,b.title,c.real_name from guild_reserve_review as a left join guild_reserve_tb as b on a.activity_id = b.id left join admins as c on a.operator_id = c.id where a.id = ?";
        $this->params = array($id);
        $this->doResult();
        return $this->result;
    }

    public function get_guild_reserve($id){
        $this->sql = "select * from guild_reserve_list where id = ?";
        $this->params = array($id);
        $this->doResult();
        return $this->result;
    }

    public function update_guild_reserve($id,$params){
        $this->sql = "update guild_reserve_list set is_del = ? where id = ?";
        $this->params = array($params['is_del'],$id);
        $this->doExecute();
    }

    public function get_log_list($page,$params){
        $this->limit_sql = "select * from `66173`.activity_reserve_log where 1=1";
        if($params['system']){
            $this->limit_sql .= " and system = ".$params['system'];
        }
        if($params['from']){
            $this->limit_sql .= " and `from` = ".$params['from'];
        }
        if($params['start_time']){
            $this->limit_sql .= " and add_time >= ".strtotime($params['start_time']);
        }
        if($params['end_time']){
            $this->limit_sql .= " and add_time <= ".strtotime($params['end_time']);
        }
        $this->limit_sql .= " order by add_time desc";
        $this->doLimitResultList($page);
        return $this->result;
    }
    public function get_log_list_nolimit($params){
        $this->sql = "select * from `66173`.activity_reserve_log where 1=1";
        if($params['system']){
            $this->sql .= " and system = ".$params['system'];
        }
        if($params['from']){
            $this->sql .= " and `from` = ".$params['from'];
        }
        if($params['start_time']){
            $this->sql .= " and add_time >= ".strtotime($params['start_time']);
        }
        if($params['end_time']){
            $this->sql .= " and add_time <= ".strtotime($params['end_time']);
        }
        $this->sql .= " order by add_time desc";
        $this->doResultList();
        return $this->result;
    }


}