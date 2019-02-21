<?php
COMMON('niuniuDao', 'randomUtils');

class mobile_index_dao extends niuniuDao{

    public function __construct(){
        parent::__construct();
        $this->mmc = new Memcache();
        $this->mmc->connect(MMCHOST, MMCPORT);
    }

    public function get_new_game(){
        $data = $this->mmc->get("new_game");
        if(!$data){
            $this->sql = "select a.*,b.app_name,b.app_icon,c.down_num,c.last_update,c.language from apps_info as a left join apps as b on a.app_id = b.app_id left join `66173`.`66app_game_tb` as c on a.game_id=c.id where a.is_new = '0' and a.is_del ='0' order by a.add_time desc limit 10";
            $this->doResultList();
            $data = $this->result;
            $this->mmc->set("new_game", $data, 1, 120);
        }
        return $data;
    }
    public function get_new_game_count(){
        $this->sql = "select count(*) as num from apps_info where is_new = '0' and is_del ='0' order by add_time desc";
        $this->doResult();
        $data = $this->result;
        return $data;
    }

    // 指定条件 - 游戏列表
    public function get_more_game_list($params){
        $this->sql = 'select a.*,b.app_name,b.app_icon,c.down_num,c.last_update,c.language from apps_info as a left join apps as b on a.app_id = b.app_id left join `66173`.`66app_game_tb` as c on a.game_id=c.id where a.is_del =0 ';
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
        $data = $this->mmc->get("game_info"."_".$id);
        if(!$data){
            $this->sql = "select a.*,b.app_name,b.app_icon,c.down_num,c.last_update,c.language,c.img1,c.img2,c.img3,c.img4 from apps_info as a left join apps as b on a.app_id = b.app_id left join `66173`.`66app_game_tb` as c on a.game_id=c.id where a.id =?";
            $this->params = array($id);
            $this->doResult();
            $data = $this->result;
            $this->mmc->set("game_info"."_".$id,$data,1,120);
        }
        return $data;
    }

    public function get_game_list(){
        $this->sql = 'select a.*,b.app_name,b.app_icon,c.down_num,c.last_update,c.language from apps_info as a left join apps as b on a.app_id = b.app_id left join `66173`.`66app_game_tb` as c on a.game_id=c.id  where a.is_del =0 order by a.add_time desc limit 10';
        $this->doResultList();
        return $this->result;

    }
    public function get_game_count($tags=null){
        $this->sql = "select count(*) as num from apps_info where is_del = '0'";
        if ($tags) {
            $this->sql .= " and tags = " . $tags;
        }
        $this->sql .= " order by add_time desc";
        $this->doResult();
        return $this->result;

    }


    public function get_banner_list(){
        $this->sql = "select * from apps_banner where is_del = 0 order by id desc limit 6";
        $this->doResultList();
        return $this->result;
    }

}