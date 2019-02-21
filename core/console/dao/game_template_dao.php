<?php
COMMON('dao');
class game_template_dao extends Dao {
    public function __construct() {
        parent::__construct();
        $this->mmc = new Memcache();
        $this->mmc->connect(MMCHOST, MMCPORT);
    }

    public function template_info($params,$page){
        $this->limit_sql = "select * from game_template where is_del=0";
        if($params['title']){
            $this->limit_sql = $this->limit_sql." and title like '%".$params['title']."%'";
        }
        $this->limit_sql = $this->limit_sql." order by id desc";
        $this->doLimitResultList($page);
        return $this->result;
    }

    public function add_info($params){
        $this->sql = "insert into game_template (title,top_img,middle_img,box_img,bottom_img,qr_code,game_img,is_del,down_url,img1,img2,img3,img4,game_id) values (?,?,?,?,?,?,?,?,?,?,?,?,?,?)";
        $this->params=array($params['title'],$params['top_img'],$params['middle_img'],$params['box_img'],$params['bottom_img'],$params['qr_code'],$params['game_img'],"0",$params['down_url'],$params['img1'],$params['img2'],$params['img3'],$params['img4'],$params['game_id']);
        $this->doExecute();
    }

    public function get_info($id){
        $this->sql = "select * from game_template where is_del=0 and id=?";
        $this->params=array($id);
        $this->doResult();
        return $this->result;
    }

    public function info_edit($params){
        $this->sql = "update game_template set title=?,top_img=?,middle_img=?,box_img=?,bottom_img=?,qr_code=?,game_img=?,down_url=?,img1=?,img2=?,img3=?,img4=?,game_id=? where id=?";
        $this->params = array($params['title'],$params['top_img'],$params['middle_img'],$params['box_img'],$params['bottom_img'],$params['qr_code'],$params['game_img'],$params['down_url'],$params['img1'],$params['img2'],$params['img3'],$params['img4'],$params['game_id'],$params['id']);
        $this->doExecute();
    }

    public function del_info($id){
        $this->sql = "update game_template set is_del = 1 where id=?";
        $this->params = array($id);
        $this->doExecute();
    }

    public function get_game_list(){
        $this->sql = "select * from game where status = 1 and is_del = 0 order by id desc";
        $this->doResultList();
        return $this->result;
    }





}