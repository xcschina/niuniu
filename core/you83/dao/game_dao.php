<?php
COMMON('niuniuDao','randomUtils');
class game_dao extends niuniuDao
{

    public function __construct()
    {
        parent::__construct();
        $this->mmc = new Memcache();
        $this->mmc->connect(MMCHOST, MMCPORT);
    }

    public function get_game_list($page,$params){
        $data = $this->mmc->get("game_list"."_".$page."_".$params['tags']);
        if(!$data){
            $this->limit_sql = "select a.id,a.banner,a.subtitle,b.app_name from apps_info as a left join apps as b on a.app_id = b.app_id where a.is_del = '0' ";
            if($params['tags']){
                $this->limit_sql .= " and a.tags = ".$params['tags'];
            }
            $this->limit_sql .= " order by a.add_time desc";
            $this->doLimitResultList($page);
            $data = $this->result;
            $this->mmc->set("game_list"."_".$page."_".$params['tags'],$data,1,120);
        }
        return $data;
    }

    public function get_game_count($params){
        $data = $this->mmc->get("game_count"."_".$params['tags']);
        if(!$data) {
            $this->sql = "select count(*) as num from apps_info where is_del = '0'";
            if ($params['tags']) {
                $this->sql .= " and tags = " . $params['tags'];
            }
            $this->doResult();
            $data = $this->result;
            $this->mmc->set("game_count"."_".$params['tags'],$data,1,120);
        }
        return $data;
    }

    public function get_game_info($id){
        $data = $this->mmc->get("game_info"."_".$id);
        if(!$data){
            $this->sql = "select a.*,b.app_name,b.app_icon from apps_info as a left join apps as b on a.app_id = b.app_id where a.id =?";
            $this->params = array($id);
            $this->doResult();
            $data = $this->result;
            $this->mmc->set("game_info"."_".$id,$data,1,120);
        }
        return $data;
    }

}