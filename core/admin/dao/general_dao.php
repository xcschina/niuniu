<?php
COMMON('dao');
class general_dao extends Dao
{

    public function __construct()
    {
        parent::__construct();
    }

    public function get_general_name(){
        $this->sql = "select * from general_tb where pid = 0 ";
        $this->doResultList();
        return $this->result;
    }

    public function get_general_list($params,$page){
        $this->limit_sql = "select a.*,b.app_name,b.app_id from general_tb as a left join niuniu.apps as b on a.game_id = b.app_id where a.pid = 0";
        if($params['game_id']){
            $this->limit_sql = $this->limit_sql." and a.game_id = ".$params['game_id'];
        }
        if($params['general_id']){
            $this->limit_sql = $this->limit_sql." and a.id = ".$params['general_id'];
        }
        $this->limit_sql = $this->limit_sql." order by a.id desc";
        $this->doLimitResultList($page);
        return $this->result;
    }

    public function get_general_max($code_name){
        $this->sql = "select * from general_tb where code_name = ? order by id desc";
        $this->params = array($code_name);
        $this->doResult();
        return $this->result;
    }

    public function get_game(){
        $this->sql = "select * from niuniu.apps where apk_url != '' order by id desc";
        $this->doResultList();
        return $this->result;
    }

    public function get_game_info($id){
        $this->sql = "select * from niuniu.apps where app_id = ?";
        $this->params = array($id);
        $this->doResult();
        return $this->result;
    }

    public function insert_activity($params){
        $this->sql = "insert into general_tb(title,down_url,banner,bg_img,`type`,add_time,game_id,batch_name,top_img,middle_img,bottom_img,`module`,page_info,is_video,video_url,video_img,is_footer) values(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";
        $this->params = array($params['title'],$params['down_url'],$params['banner'],$params['bg_img'],$params['type'],time(),$params['game_id'],$params['batch_name'],$params['top_img'],$params['middle_img'],$params['bottom_img'],$params['module'],$params['page_info'],$params['is_video'],$params['video_url'],$params['video_img'],$params['is_footer']);
        $this->doInsert();
    }

    public function get_general($id){
        $this->sql = "select * from general_tb where pid = ?";
        $this->params = array($id);
        $this->doResultList();
        return $this->result;
    }

    public function get_general_info($id){
        $this->sql = "select * from general_tb where id = ?";
        $this->params = array($id);
        $this->doResult();
        return $this->result;
    }

    public function insert_general($params,$info,$data,$id){
        $this->sql = "insert into general_tb(title,game_id,down_url,add_time,code,pid,code_name) values(?,?,?,?,?,?,?)";
        $this->params = array($info['title'],$info['game_id'],$params['down_url'],time(),$data,$id,$params['code_name']);
        $this->doInsert();
    }

    public function get_down_num($id){
        $this->sql = "select count(*) as num from general_down_log where relation_id = ?";
        $this->params = array($id);
        $this->doResult();
        return $this->result;
    }

    public function get_visit_num($id){
        $this->sql = " select count(*) as num from general_visit_log where relation_id = ?";
        $this->params = array($id);
        $this->doResult();
        return $this->result;
    }

    public function get_general_code($code,$id){
        $this->sql = "select * from general_tb where code = ? and pid = ?";
        $this->params = array($code,$id);
        $this->doResult();
        return $this->result;
    }

    public function update_general($id){
        $this->sql = "update general_tb set is_del = 1 where id = ?";
        $this->params = array($id);
        $this->doExecute();
    }

    public function update_general_info($id,$params){
        $this->sql = "update general_tb set title=?,down_url=?,banner=?,bg_img=?,`type`=?,game_id=?,batch_name=?,top_img=?,middle_img=?,bottom_img=?,`module`=?,page_info=?,is_video=?,video_url=?,video_img=?,is_footer=?  where id = ? ";
        $this->params = array($params['title'],$params['down_url'],$params['banner'],$params['bg_img'],$params['type'],$params['game_id'],$params['batch_name'],$params['top_img'],$params['middle_img'],$params['bottom_img'],$params['module'],$params['page_info'],$params['is_video'],$params['video_url'],$params['video_img'],$params['is_footer'],$id);
        $this->doExecute();
    }

    public function update_general_msg($id,$params){
        $this->sql = "update general_tb set title=?,down_url=?,game_id=?  where id = ?";
        $this->params = array($params['title'],$params['new_url'],$params['game_id'],$id);
        $this->doExecute();
    }

    public function get_log_list($params,$page){
        $this->limit_sql = "select a.*,b.app_name,b.app_id from general_tb as a left join niuniu.apps as b on a.game_id = b.app_id where a.is_del = 0 and a.down_url != ''";
        if($params['game_id']){
            $this->limit_sql = $this->limit_sql." and a.game_id = ".$params['game_id'];
        }
        if($params['code']){
            $this->limit_sql = $this->limit_sql." and a.code = '".$params['code']."'";
        }
        if($params['code_name']){
            $this->limit_sql = $this->limit_sql." and a.code_name = '".$params['code_name']."'";
        }
        $this->limit_sql = $this->limit_sql." order by a.id desc";
        $this->doLimitResultList($page);
        return $this->result;
    }

    public function get_down_log($params,$page){
        $this->limit_sql = "select * from general_down_log where 1=1";
        if($params['ip']){
            $this->limit_sql = $this->limit_sql." and ip = '".$params['ip']."'";
        }
        if($params['relation_id']){
            $this->limit_sql = $this->limit_sql." and relation_id = ".$params['relation_id'];
        }
        if($params['start_time'] && $params['end_time']){
            $this->limit_sql = $this->limit_sql .=  " and add_time >= ".strtotime($params['start_time'])." and add_time <= ".strtotime($params['end_time']);
        }else if($params['start_time'] && !$params['end_time']) {
            $this->limit_sql = $this->limit_sql .=  " and add_time >= ".strtotime($params['start_time']);
        } else if(!$params['start_time'] && $params['end_time']) {
            $this->limit_sql = $this->limit_sql .=  " and add_time <= ".strtotime($params['end_time']);
        }
        $this->limit_sql = $this->limit_sql .= " order by id desc";
        $this->doLimitResultList($page);
        return $this->result;
    }

    public function get_visit_log($params,$page){
        $this->limit_sql = "select * from general_visit_log where 1=1 " ;
        if($params['ip']){
            $this->limit_sql = $this->limit_sql." and ip = '".$params['ip']."'";
        }
        if($params['relation_id']){
            $this->limit_sql = $this->limit_sql." and relation_id = ".$params['relation_id'];
        }
        if($params['start_time'] && $params['end_time']){
            $this->limit_sql = $this->limit_sql .=  " and add_time >= ".strtotime($params['start_time'])." and add_time <= ".strtotime($params['end_time']);
        }else if($params['start_time'] && !$params['end_time']) {
            $this->limit_sql = $this->limit_sql .=  " and add_time >= ".strtotime($params['start_time']);
        } else if(!$params['start_time'] && $params['end_time']) {
            $this->limit_sql = $this->limit_sql .=  " and add_time <= ".strtotime($params['end_time']);
        }
        $this->limit_sql = $this->limit_sql .= " order by id desc";
        $this->doLimitResultList($page);
        return $this->result;
    }

    public function get_all_general_log_list($params){
        $this->sql = "select * from general_tb  where is_del = 0 and down_url != ''";
        if($params['game_id']){
            $this->sql = $this->sql." and game_id = ".$params['game_id'];
        }
        if($params['code']){
            $this->sql = $this->sql." and code = '".$params['code']."'";
        }
        if($params['code_name']){
            $this->sql = $this->sql." and code_name = '".$params['code_name']."'";
        }
        $this->sql = $this->sql." order by id desc";
        $this->doResultList();
        return $this->result;
    }

    public function update_general_link($id,$ios_link){
        $this->sql = "update general_tb set ios_link = ? where id = ?";
        $this->params = array($ios_link,$id);
        $this->doExecute();
    }
}