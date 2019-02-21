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

    public function get_zhijian_info($super_id,$channel,$cpgameid){
        $data = $this->mmc->get("zhijian_info_".$super_id.'_'.$channel);
        if(!$data){
            $this->sql = "select * from `niuniu`.channel_apps where super_id =? and ch_code = ? and param1 = ?";
            $this->params = array($super_id,$channel,$cpgameid);
            $this->doResult();
            $data = $this->result;
            $this->mmc->set("zhijian_info_".$super_id.'_'.$channel,$data,1,120);
        }
        return $data;
    }

    public function get_super_order($order_id){
        $this->sql = "select * from `niuniu`.super_orders where order_id = ?";
        $this->params = array($order_id);
        $this->doResult();
        $data = $this->result;
        return $data;
    }

    public function update_super_order_info($order_id,$pay_time,$ch_order){
        $this->sql = "update `niuniu`.super_orders set `status`=?,pay_time=?,charge_time=?,ch_order=? where order_id = ? ";
        $this->params = array(1,$pay_time, time(), $ch_order,$order_id);
        $this->doExecute();
    }
}