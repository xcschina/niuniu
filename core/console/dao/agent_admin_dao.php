<?php
COMMON('dao');
class agent_admin_dao extends Dao {

    public function __construct() {
        parent::__construct();
        $this->mmc = new Memcache();
        $this->mmc->connect(MMCHOST, MMCPORT);
    }

    public function get_agents($page){
        $this->limit_sql = "select a.*,b.is_agent,b.user_name,b.mobile,c.game_name from agents as a
                          left join user_info as b on a.user_id=b.user_id
                          left join game as c on a.game_id=c.id order by a.lastupdate desc";
        $this->doLimitResultList($page);
        return $this->result;
    }

    public function get_agent($id){
        $this->sql = "select a.*,b.is_agent,b.user_name,b.mobile,c.game_name from agents as a
                          left join user_info as b on a.user_id=b.user_id
                          left join game as c on a.game_id=c.id where a.id=?";
        $this->params = array($id);
        $this->doResult();
        return $this->result;
    }

    public function update_agent($id, $agent){
        $this->sql = "update agents set discount_1=?,discount_2=?,discount_3=?,lastupdate=? where id=?";
        $this->params = array($agent['discount_1'], $agent['discount_2'], $agent['discount_3'], strtotime("now"), $id);
        $this->doExecute();

        $this->sql = "update user_info set is_agent=? where user_id=?";
        $this->params = array($agent['is_agent'], $agent['user_id']);
        $this->doExecute();
        $this->mmc->delete("user_agent_games".$agent['user_id']."-".$agent['game_id']);
    }

    public function get_user_agent($agent){
        $this->sql = "select * from agents where user_id=? and game_id=?";
        $this->params = array($agent['user_id'], $agent['game_id']);
        $this->doResult();
        return $this->result;
    }

    public function insert_agent($agent){
        $this->sql = "insert into agents(user_id,game_id,discount_1,discount_2,discount_3,lastupdate)values(?,?,?,?,?,?)";
        $this->params = array($agent['user_id'], $agent['game_id'], $agent['discount_1'], $agent['discount_2'], $agent['discount_3'], strtotime("now"));
        $this->doExecute();

        $this->sql = "update user_info set is_agent=? where user_id=?";
        $this->params = array($agent['is_agent'], $agent['user_id']);
        $this->doExecute();
        $this->mmc->delete("user_agent_games".$agent['user_id']."-".$agent['game_id']);
    }
}