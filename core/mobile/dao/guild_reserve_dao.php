<?php
COMMON('niuniuDao','randomUtils');
class guild_reserve_dao extends niuniuDao{

    public function __construct(){
        parent::__construct();
        $this->mmc = new Memcache();
        $this->mmc->connect(MMCHOST, MMCPORT);
    }

    public function get_guild_act_info($act_id){
//        $data = memcache_get($this->mmc,'guild_activity_info'.$act_id);
        if(!$data){
            $this->sql = "select act.*,re.guild_id,re.guild_code from guild_reserve_tb as act left join guild_reserve_list as re on act.id=re.activity_id  
                          where re.id = ? and re.is_del=0 and act.is_del=0";
            $this->params = array($act_id);
            $this->doResult();
            $data = $this->result;
            $this->mmc->set('guild_activity_info'.$act_id, $data, 1, 600);
        }
        return $data;
    }

    public function get_guild_info($guild_id){
//        $data = memcache_get($this->mmc,'guild_info'.$guild_id);
        if(!$data){
            $this->sql = "select * from admins where id= ? ";
            $this->params = array($guild_id);
            $this->doResult();
            $data = $this->result;
            $this->mmc->set('guild_info'.$guild_id, $data, 1, 600);
        }
        return $data;
    }

    public function get_guild_reserve_info($activity_id){
        $this->sql = "select * from guild_reserve_list where id= ? and is_del = 0";
        $this->params = array($activity_id);
        $this->doResult();
        $data = $this->result;
        return $data;
    }

    public function get_guild_reserve_count($act_id,$guild_id){
//        $data = memcache_get($this->mmc,'guild_reserve_count'.$act_id."guild_".$guild_id);
        if(!$data){
            $this->sql = "select reserve_num as num  from guild_reserve_rank where act_id= ? and guild_id= ?";
            $this->params = array($act_id,$guild_id);
            $this->doResult();
            $data = $this->result['num'];
            $this->mmc->set('guild_reserve_count'.$act_id."guild_".$guild_id, $data, 1, 600);
        }
        return $data;
    }

    public function get_guild_rank($params){
//        $data = memcache_get($this->mmc,'guild_rank_list'.$params['id']);
        if(!$data){
            $this->sql = "select a.*,b.guild_name,b.real_name,b.img from guild_reserve_rank as a left join admins as b on a.guild_id = b.id where a.act_id= ? order by a.reserve_num desc limit 10";
            $this->params = array($params['id']);
            $this->doResultList();
            $data = $this->result;
            $this->mmc->set('guild_rank_list'.$params['id'], $data, 1, 600);
        }
        return $data;
    }

    public function get_app_info($game_id){
//        $data = memcache_get($this->mmc,'game_app_info'.$game_id);
        if(!$data){
            $this->sql = "select * from apps where app_id=? ";
            $this->params = array($game_id);
            $this->doResult();
            $data = $this->result;
            $this->mmc->set('game_app_info'.$game_id, $data, 1, 600);
        }
        return $data;
    }

    public function get_my_guild_rank($params){
//        $data = memcache_get($this->mmc,'my_guild_rank'.$params['id']."user_".$params['guild_id']);
        if(!$data){
            $this->sql = "SELECT *  from (SELECT *,(@rowno:=@rowno+1) as rowno from guild_reserve_rank,(select (@rowno:=0)) a where act_id= ? ORDER BY reserve_num desc) b WHERE guild_id= ?";
            $this->params = array($params['id'],$params['guild_id']);
            $this->doResult();
            $data = $this->result;
            $this->mmc->set('my_guild_rank'.$params['id']."user_".$params['guild_id'], $data, 1, 600);
        }
        return $data;
    }


    public function get_all_reserve_count($parent_id){
//        $data = memcache_get($this->mmc,'all_reserve_count'.$parent_id);
        if(!$data){
            $this->sql = "select sum(reserve_num) as num from guild_reserve_rank where act_id= ?";
            $this->params = array($parent_id);
            $this->doResult();
            $data = $this->result['num'];
            $this->mmc->set('all_reserve_count'.$parent_id, $data, 1, 600);
        }
        return $data;
    }

    public function get_guild_reserve_list($act_id){
//        $data = memcache_get($this->mmc,'guild_reserve_list'.$act_id);
        if(!$data){
            $this->sql = "SELECT * FROM guild_reserve_log WHERE  act_id=? GROUP BY  guild_id ";
            $this->params = array($act_id);
            $this->doResultList();
            $data = $this->result;
            $this->mmc->set('guild_reserve_list'.$act_id, $data, 1, 600);
        }
        return $data;
    }

    public function get_act_guild_count($act_id,$guild_id){
        $this->sql = "SELECT count(1) as num FROM guild_reserve_log WHERE  act_id=? AND guild_id= ? ";
        $this->params = array($act_id,$guild_id);
        $this->doResult();
        $data = $this->result['num'];
        return $data;
    }

    public function get_reserve_ip_sum($ip){
        $this->sql = "select count(id) as num from guild_reserve_log where ip = ?";
        $this->params = array($ip);
        $this->doResult();
        return $this->result;
    }

    public function insert_reserve_log($act_id,$user_id,$ip,$reserve_info){
        $this->sql = "insert into guild_reserve_log(user_id,act_id,ip,parent_id,guild_id,add_time) VALUES(?,?,?,?,?,?)";
        $this->params = array($user_id,$act_id,$ip,$reserve_info['activity_id'],$reserve_info['guild_id'],time());
        $this->doInsert();
    }

    public function get_reserve_log($act_id,$user_id){
        $this->sql = "select * from guild_reserve_log  where act_id= ? and user_id= ? ";
        $this->params = array($act_id,$user_id);
        $this->doResult();
        return $this->result;
    }

    public function get_my_reserve_log($parent_id,$user_id){
        $this->sql = "select * from guild_reserve_log  where parent_id= ? and user_id= ? ";
        $this->params = array($parent_id,$user_id);
        $this->doResult();
        return $this->result;
    }

    public function get_gift_code_by_userid($gift_id,$user_id){
        $this->sql = "select a.*,b.title from `66173`.game_gifts as a left join `66173`.game_gift_info as b on a.batch_id = b.id where a.buyer_id = ? and a.batch_id= ? ";
        $this->params = array($user_id,$gift_id);
        $this->doResultList();
        return $this->result;
    }

    public function query_last_gift($batch_id){
        $this->sql = "select * from `66173`.game_gifts where batch_id = ? and is_use = 0";
        $this->params = array($batch_id);
        $this->doResult();
        return $this->result;
    }

    public function update_game_gifts($id,$user_id){
        $this->sql = "update `66173`.game_gifts set is_use=1,buyer_id=?,buy_time=? where id=?";
        $this->params = array($user_id,time(),$id);
        $this->doExecute();

    }

    public function get_act_guild_rank($params){
        $this->sql = "select * from guild_reserve_rank where act_id= ? and guild_id= ?";
        $this->params = array($params['activity_id'],$params['guild_id']);
        $this->doResult();
        return $this->result;
    }

    public function insert_act_guild_rank($params,$unm=1){
        $this->sql = "insert into guild_reserve_rank(act_id,guild_id,reserve_num,add_time,last_time) VALUES(?,?,?,?,?)";
        $this->params = array($params['activity_id'],$params['guild_id'],$unm,time(),time());
        $this->doInsert();
    }

    public function update_act_guild_rank($params,$unm=1){
        $this->sql = "update guild_reserve_rank set reserve_num=?,last_time=? where act_id= ? and guild_id= ? ";
        $this->params = array($unm,time(),$params['activity_id'],$params['guild_id']);
        $this->doExecute();
    }

    public function get_wx_access_token(){
        $data = memcache_get($this->mmc, 'wx_access_token');
        return $data;
    }

    public function set_wx_access_token($data){
        memcache_set($this->mmc, "wx_access_token", $data, 1, 7200);
    }

    public function get_wx_access_jsapi_data($token){
        $data = memcache_get($this->mmc, 'jsapi_data_'.$token);
        return $data;
    }

    public function set_wx_access_jsapi_data($token,$data){
        memcache_set($this->mmc, 'jsapi_data_'.$token, $data, 1, 7200);
    }
}
?>
