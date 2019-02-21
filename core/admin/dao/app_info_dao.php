<?php
COMMON('niuniuDao');
class app_info_dao extends niuniuDao {

    public function __construct() {
        parent::__construct();
        $this->mmc = new Memcache();
        $this->mmc->connect(MMCHOST, MMCPORT);
        $this->TB_NAME = "apps";
    }

    public function get_info_list($page,$params){
        $this->limit_sql = "select a.*,b.app_name,b.app_icon from apps_info as a left join apps as b on a.app_id = b.app_id where 1=1 ";
        if($params['app_id']){
            $this->limit_sql .= " and a.app_id =".$params['app_id'];
        }
        if($params['is_new'] && is_numeric($params['is_new']) || $params['is_new'] === '0'){
            $this->limit_sql .= " and a.is_new = ".$params['is_new'];
        }
        if($params['is_del'] && is_numeric($params['is_del']) || $params['is_del'] === '0'){
            $this->limit_sql .= " and a.is_del = ".$params['is_del'];
        }
        if($params['tags']){
            $this->limit_sql .= " and a.tags = ".$params['tags'];
        }
        $this->limit_sql .= " order by a.id desc";
        $this->doLimitResultList($page);
        return $this->result;
    }

    public function get_app_list(){
        $this->sql = "select * from apps ";
        $this->doResultList();
        return $this->result;
    }

    public function insert_apps_into($params){
        $this->sql = "insert into apps_info (game_id,title,subtitle,banner,app_id,down_url,app_size,tags,system,`desc`,add_time,is_new) values (?,?,?,?,?,?,?,?,?,?,?,?)";
        $this->params = array($params['game_id'],$params['title'],$params['subtitle'],$params['banner'],$params['app_id'],$params['down_url'],$params['app_size'],$params['tags'],$params['system'],$params['desc'],time(),$params['is_new']);
        $this->doExecute();
    }

    public function get_app_info($id){
        $this->sql = "select * from apps_info where id = ?";
        $this->params = array($id);
        $this->doResult();
        return $this->result;
    }

    public function update_apps_into($params){
        $this->sql = "update apps_info set game_id=?,title=?,subtitle=?,banner=?,app_id=?,down_url=?,app_size=?,tags=?,system=?,`desc`=?,is_new=? where id=?";
        $this->params = array($params['game_id'],$params['title'],$params['subtitle'],$params['banner'],$params['app_id'],$params['down_url'],$params['app_size'],$params['tags'],$params['system'],$params['desc'],$params['is_new'],$params['id']);
        $this->doExecute();
    }

    public function update_info($params){
        $this->sql = "update apps_info set is_del = ? where id = ?";
        $this->params = array($params['status'],$params['id']);
        $this->doExecute();
    }

    public function get_banner_list($page){
        $this->limit_sql = "select * from apps_banner where is_del = 0 order by id desc";
        $this->doLimitResultList($page);
        return $this->result;
    }

    public function insert_app_banner($params){
        $this->sql = "insert into apps_banner (banner,add_time,remark,url) values(?,?,?,?)";
        $this->params = array($params['banner'],time(),$params['remark'],$params['url']);
        $this->doExecute();
    }

    public function get_banner_info($id){
        $this->sql = "select * from apps_banner where id = ?";
        $this->params = array($id);
        $this->doResult();
        return $this->result;
    }

    public function update_app_banner($id){
        $this->sql = "update apps_banner set is_del = 1  where id = ?";
        $this->params = array($id);
        $this->doExecute();
    }
    //获取66173的游戏信息
    public function get_66173_game(){
        $this->sql = "select id,game_name from `66173`.`66app_game_tb` where is_del=0 ";
        $this->doResultList();
        return $this->result;
    }


}