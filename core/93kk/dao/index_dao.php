<?php
COMMON('niuniuDao','randomUtils');
class index_dao extends niuniuDao
{

    public function __construct()
    {
        parent::__construct();
        $this->mmc = new Memcache();
        $this->mmc->connect(MMCHOST, MMCPORT);
    }

    public function get_new_game(){
        $data = $this->mmc->get("new_game");
        if(!$data){
            $this->sql = "select a.*,b.app_name from apps_info as a left join apps as b on a.app_id = b.app_id where a.is_new = '0' and a.is_del ='0' order by a.add_time desc limit 9";
            $this->doResultList();
            $data = $this->result;
            $this->mmc->set("new_game",$data,1,120);
        }
        return $data;
    }
    public function get_new_game_count(){
        $this->sql = "select a.*,b.app_name from apps_info as a left join apps as b on a.app_id = b.app_id where a.is_new = '0' and a.is_del ='0' order by a.add_time desc";
        $this->doResultList();
        $data = $this->result;
        return $data;
    }

    // 指定条件 - 游戏列表
    public function get_more_game_list($params){
        $this->sql = 'select a.*,b.app_name from apps_info as a left join apps as b on a.app_id = b.app_id  where a.is_del =0 ';
        if($params['tags']){
            $this->sql .= " and a.tags = ".$params['tags'];
        }
        if($params['is_new']!=null){
            $this->sql .= " and a.is_new = ".$params['is_new'];
        }
        $this->sql .= " order by a.add_time desc";
        $this->sql .= " limit " . $params['start_num'] . "," . $params['page_size'];
        $this->doResultList();
        return $this->result;

    }
    //游戏详情
    public function get_game_info($id){
//        $data = $this->mmc->get("game_info"."_".$id);
//        if(!$data){
        $this->sql = "select a.*,b.app_name,b.app_icon from apps_info as a left join apps as b on a.app_id = b.app_id where a.id =?";
        $this->params = array($id);
        $this->doResult();
        $data = $this->result;
//            $this->mmc->set("game_info"."_".$id,$data,1,120);
//        }
        return $data;
    }

    public function get_game_list(){
        $this->sql = 'select a.*,b.app_name from apps_info as a left join apps as b on a.app_id = b.app_id  where a.is_del =0 order by a.add_time desc limit 5';
        $this->doResultList();
        return $this->result;

    }
    public function get_game_count(){
        $this->sql = 'select a.*,b.app_name from apps_info as a left join apps as b on a.app_id = b.app_id  where a.is_del =0';
        $this->sql .= " order by a.add_time desc";
        $this->doResultList();
        return $this->result;
    }

    public function get_banner_list(){
        $this->sql = "select * from apps_banner where is_del = 0 order by id desc limit 6";
        $this->doResultList();
        return $this->result;
    }

    public function get_wx_access_token(){
        $data = $this->mmc->get('wx_access_token');
        return $data;
    }

    public function set_wx_access_token($data){
        $this->mmc->set( "wx_access_token", $data, 1, 7200);
    }

    public function get_wx_access_jsapi_data($token){
        $data = $this->mmc->get( 'jsapi_data_'.$token);
        return $data;
    }

    public function set_wx_access_jsapi_data($token,$data){
        $this->mmc->set( 'jsapi_data_'.$token, $data, 1, 7200);
    }

}