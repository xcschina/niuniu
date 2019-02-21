<?php
COMMON('baseCore','uploadHelper');
DAO('weekactivity_admin_dao','common_dao');

class weekactivity_admin extends baseCore{

    public $DAO;
    public $COMMON;

    public function __construct(){
        parent::__construct();
        $this->DAO = new weekactivity_admin_dao();
        $this->common = new common_dao();
    }

    public function list_view(){
        $list = $this->DAO->get_weekactivity_list($this->pageCurrent);
        $nouse_list = $this->DAO->get_weekactivity_nouse();
        $batchs = $this->DAO->activity_batch_list();
        foreach ($list as $k=>$v){
            if(empty($v['batch_id'])){
                $this->DAO->update_weekactivity_status(4,$v['id']);
            }else{
               $batch = $this->DAO->get_weekactivity_batch($v['batch_id']);
               $this->DAO->update_weekactivity_status($batch['status'],$v['id']);
            }
        }
        $list = $this->DAO->get_weekactivity_list($this->pageCurrent);
        foreach($list as $k=>$v){
            $game_icon =  $this->DAO->get_game_icon($v['game_id']);
            $list[$k]['game_icon'] = $game_icon['game_icon'];
            $batch = $this->DAO->get_weekactivity_batch($v['batch_id']);
            $list[$k]['time'] = date("Y-m-d H:i:s",$batch['start_time'])."-". date("Y-m-d H:i:s",$batch['end_time']);
            $list[$k]['batch_id'] = $batch['id'];
        }
        $this->pageInfo($this->pageCurrent);
        $this->assign("datas",$list);
        $this->assign("lists",$nouse_list);
        $this->assign("batchs",$batchs);
        $this->display("weekactivity.html");
    }

    public function add_view(){
        $list = $this->DAO->get_game_list();
        $this->assign("game_list",$list);
        $this->display("weekactivity_add.html");
    }

    public function do_save(){
        $params = $_POST;
        if($_FILES['game_pic']['tmp_name']){
            $params['game_img']=$this->up_img('game_pic','images/activity_img');
        }
        if(!$params['game_id']){
            echo json_encode($this->error_msg("请选择游戏"));
            exit();
        }
        if(!$params['game_name']){
            echo json_encode($this->error_msg("请选择商品名称"));
            exit();
        }
        if(!$params['old_price']){
            echo json_encode($this->error_msg("请填写商品原价"));
            exit();
        }
        if(!$params['new_price']){
            echo json_encode($this->error_msg("请填写商品折扣价"));
            exit();
        }
        if(!$params['repertory']){
            echo json_encode($this->error_msg("请填写商品库存量"));
            exit();
        }
        if(!$params['false_reper']){
            echo json_encode($this->error_msg("请填写商品剩余库存"));
            exit();
        }
        if(!$params['game_desc']){
            echo json_encode($this->error_msg("请填写游戏简介"));
            exit();
        }
        $data =  $this->DAO->insert_activity_tb($params);
        if(!empty($data)){
            echo json_encode($this->succeed_msg("保存成功！","list_view"));
        }else{
            echo json_encode($this->succeed_msg("保存失败！","list_view"));
        }
    }

    public function activity_batch_save(){
        $params = $_POST;
        $params['start_time'] = strtotime($params['start_time']);
        $params['end_time'] = strtotime($params['end_time']);
        if(!$params['start_time']){
            echo json_encode($this->error_msg("请选择开始时间"));
            exit();
        }
        if(!$params['end_time']){
            echo json_encode($this->error_msg("请选择结束时间"));
            exit();
        }
        if($_FILES['pc_pic']['tmp_name']){
            $params['pc_img']=$this->up_img('pc_pic','images/activity_img');
        }
        if($_FILES['m_pic']['tmp_name']){
            $params['m_img']=$this->up_img('m_pic','images/activity_img');
        }
        $data =  $this->DAO->insert_activity_batch($params);
        if(!empty($data)){
            echo json_encode($this->succeed_msg("保存成功！","batch_view"));
        }else{
            echo json_encode($this->succeed_msg("保存失败！","batch_view"));
        }
    }

    public function edit_view(){
        $id = $_GET['id'];
        $list = $this->DAO->get_game_list();
        $data = $this->DAO->get_activity_list($id);
        $this->assign("data",$data);
        $this->assign("game_list",$list);
        $this->display("weekactivity_edit.html");
    }

    public function batch_edit_view(){
        $id = $_GET['id'];
        $datas = $this->DAO->activity_batch_list($id);
        $this->assign("data",$datas[0]);
        $this->display("weekactivity_batch_edit.html");
    }

    public function do_batch_edit(){
//        ini_set("display_errors","On");
//        error_reporting(E_ALL);
        $params = $_POST;
        $params['start_time'] = strtotime($params['start_time']);
        $params['end_time'] = strtotime($params['end_time']);
        if($_FILES['pc_pic']['tmp_name']){
            $params['pc_img']=$this->up_img('pc_pic','images/activity_img');
        }else{
            $params['pc_img']=$params['v_pc_img'];
        }
        if($_FILES['m_pic']['tmp_name']){
            $params['m_img']=$this->up_img('m_pic','images/activity_img');
        }else{
            $params['m_img']=$params['v_m_img'];
        }
        $this->DAO->update_weekactivity_batch($params);
        echo json_encode($this->succeed_msg("修改成功","batch_view"));
    }

    public function do_edit(){
        $params = $_POST;
        if($_FILES['game_pic']['tmp_name']){
            $params['game_img']=$this->up_img('game_pic','images/activity_img');
        }else{
            $params['game_img']=$params['v_game_img'];
        }
        $this->DAO->update_weekactivity_tb($params);
        echo json_encode($this->succeed_msg("修改成功","list_view"));
    }

    public function batch_view(){
        $datas = $this->DAO->activity_batch_all($this->pageCurrent);
        foreach($datas as $k=>$v){
            if($v['is_use'] == 1 and $v['start_time']<time() and time() < $v['end_time']){
                $status = 3;
            }elseif($v['is_use'] == 1 and $v['end_time'] < time()){
                $status = 1;
            }else{
                $status = 2;
            }
            $this->DAO->update_batch_status($status,$v['id']);
        }
        $datas = $this->DAO->activity_batch_all($this->pageCurrent);
        $this->pageInfo($this->pageCurrent);
        $this->assign("datas",$datas);
        $this->display("weekactivity_batch_list.html");
    }

    public function add_batch_activity(){
        $this->display("weekactivity_batch_add.html");
    }

    public function update_weekactivity(){
        $params = $_POST;
        if(!$params['batch_id']){
            echo json_encode($this->error_msg("请选择批次"));
            exit();
        }
        if(!$params['activity']){
            echo json_encode($this->error_msg("请添加游戏"));
            exit();
        }
        $batch_id = $params['batch_id'];
        foreach($params['activity'] as $k=>$v){
            $this->DAO->update_activity_message($params['batch_id'],$v);
        }
        $this->DAO->update_activity_batch($batch_id);
        echo json_encode($this->unclose_succeed_msg("上线成功"));
    }
}