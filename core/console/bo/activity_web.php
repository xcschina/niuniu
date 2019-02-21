<?php
COMMON('baseCore','uploadHelper');
DAO('activity_dao');

class activity_web extends baseCore{

    public $DAO;

    public function __construct(){
        parent::__construct();
        $this->DAO = new activity_dao();
    }

    public function activity_list(){
        $params=$_POST;
        $activity_name = $this->DAO->activity_name();
        $data_list = $this->DAO->activity_list($params,$this->pageCurrent);
        $this->pageInfo($this->pageCurrent);
        $this->assign("params",$params);
        $this->assign("activity_name",$activity_name);
        $this->assign("data_list",$data_list);
        $this->display("activity_list.html");
    }

    public function activity_add_view(){
        $this->display("activity_add_view.html");
    }

    public function add_activity(){
        $params=$_POST;
        if($_FILES['banner_url']['tmp_name']){
            $params['banner']=$this->up_img('banner_url','images/activity_img');
        }
        $this->DAO->add_activity($params);
        die(json_encode($this->succeed_msg("活动添加成功","activity_list")));
    }

    public function activity_edit_view($id){
        $info=$this->DAO->get_activity($id);
        if($info){
            $this->assign("activity",$info);
            $this->display("activity_edit_view.html");
        }else{
            die(json_encode($this->error_msg("未查到该活动信息")));
        }
    }

    public function edit_activity(){
        $params=$_POST;
        if(!$_FILES['banner_url']['tmp_name']){
            $params['banner']=$params['old_activity_banner'];
        }else{
            $params['banner']=$this->up_img('banner_url','images/activity_img');
        }
        $this->DAO->edit_activity($params);
        die(json_encode($this->succeed_msg("活动编辑成功","activity_list")));
    }

    public function del_activity($id){
        $info=$this->DAO->get_activity($id);
        if(!$info){
            die(json_encode($this->error_msg("未查到该活动信息")));
        }else {
            $this->DAO->del_activity($id);
            die(json_encode($this->succeed_del("活动删除成功", "activity_list")));
        }
    }

    public function pop_list(){
        $params = $_POST;
        $list = $this->DAO->get_pop_list($params,$this->pageCurrent);
        $game = $this->DAO->get_name_list();
        $theme = $this->DAO->get_theme_list();
        $this->pageInfo($this->pageCurrent);
        $this->assign('params',$params);
        $this->assign('game',$game);
        $this->assign('theme_list',$theme);
        $this->assign('data_list',$list);
        $this->display("activity_pop_list.html");
    }

    public function pop_add(){
        $game = $this->DAO->get_name_list();
        $theme = $this->DAO->get_theme_list();
        $rec_list = $this->DAO->get_rec_list();
        $this->assign('game',$game);
        $this->assign('theme_list',$theme);
        $this->assign('rec_list',$rec_list);
        $this->display("activity_pop_add.html");
    }

    public function pop_save(){
        $params = $_POST;
        if(!$params['title'] || !$params['type'] || !$params['start_time'] || !$params['end_time']){
            die(json_encode($this->error_msg("缺少必填项")));
        }
        if($params['type'] == 1){
            if(!$params['game_id']){
                die(json_encode($this->error_msg("请选择关联游戏")));
            }else{
                $params['url'] = '';
                $params['theme_id'] = '';
                $params['rec_id'] = '';
            }
        }else if($params['type'] == 2){
            if(!$params['url']){
                die(json_encode($this->error_msg("请填写图片链接")));
            }else{
                $params['game_id'] = '';
                $params['theme_id'] = '';
                $params['rec_id'] = '';
            }
            $params['url'] = trim($params['url']);
            $array = get_headers($params['url'],1);
            if(!(preg_match('/200/',$array[0]) || preg_match('/302/',$array[0]))){
                die(json_encode($this->error_msg("请输入正确的图片链接")));
            }
            if( $_SERVER['HTTP_REFERER'] == "" ){
                header("Location:".$params['url']); exit;
            }
        }else if($params['type'] == 3){
            if(!$params['theme_id']){
                die(json_encode($this->error_msg("请选择关联主题")));
            }else{
                $params['url'] = '';
                $params['game_id'] = '';
                $params['rec_id'] = '';
            }
        }else if($params['type'] == 4){
            if(!$params['rec_id']){
                die(json_encode($this->error_msg("请选择关联推荐游戏")));
            }else{
                $params['url'] = '';
                $params['theme_id'] = '';
                $params['game_id'] = $params['rec_id'];
            }
        }else if($params['type'] == 5 || $params['type'] == 6 || $params['type'] == 7){
            $params['url'] = '';
            $params['theme_id'] = '';
            $params['game_id'] = '';
        }
        if($_FILES['img']['tmp_name']){
            $params['img'] = $this->up_img('img','images/banner_img');
        }else{
            die(json_encode($this->error_msg("请选择上传图片")));
        }
        $data =  $this->DAO->insert_activity_pop($params);
        if(!empty($data)){
            die(json_encode($this->succeed_msg("保存成功！","pop_list")));
        }else{
            die(json_encode($this->error_msg("未能保存成功.")));
        }
    }

    public function pop_edit($id){
        $info = $this->DAO->get_pop_info($id);
        if(!$info){
            die(json_encode($this->error_msg("查无此活动")));
        }
        $game = $this->DAO->get_name_list();
        $theme = $this->DAO->get_theme_list();
        $rec_list = $this->DAO->get_rec_list();
        $this->assign('game',$game);
        $this->assign('theme_list',$theme);
        $this->assign('rec_list',$rec_list);
        $this->assign('info',$info);
        $this->display("activity_pop_edit.html");
    }

    public function pop_edit_save(){
        $params = $_POST;
        if(!$params['title'] || !$params['type'] || !$params['start_time'] || !$params['end_time']){
            die(json_encode($this->error_msg("缺少必填项")));
        }
        if($params['type'] == 1){
            if(!$params['game_id']){
                die(json_encode($this->error_msg("请选择关联游戏")));
            }else{
                $params['url'] = '';
                $params['theme_id'] = '';
                $params['rec_id'] = '';
            }
        }else if($params['type'] == 2){
            if(!$params['url']){
                die(json_encode($this->error_msg("请填写图片链接")));
            }else{
                $params['game_id'] = '';
                $params['theme_id'] = '';
                $params['rec_id'] = '';
            }
            $params['url'] = trim($params['url']);
            $array = get_headers($params['url'],1);
            if(!(preg_match('/200/',$array[0]) || preg_match('/302/',$array[0]))){
                die(json_encode($this->error_msg("请输入正确的图片链接")));
            }
            if( $_SERVER['HTTP_REFERER'] == "" ){
                header("Location:".$params['url']); exit;
            }
        }else if($params['type'] == 3){
            if(!$params['theme_id']){
                die(json_encode($this->error_msg("请选择关联主题")));
            }else{
                $params['url'] = '';
                $params['game_id'] = '';
                $params['rec_id'] = '';
            }
        }else if($params['type'] == 4){
            if(!$params['rec_id']){
                die(json_encode($this->error_msg("请选择关联推荐游戏")));
            }else{
                $params['url'] = '';
                $params['theme_id'] = '';
                $params['game_id'] = $params['rec_id'];
            }
        }else if($params['type'] == 5 || $params['type'] == 6 || $params['type'] == 7){
            $params['url'] = '';
            $params['theme_id'] = '';
            $params['game_id'] = '';
        }
        if($_FILES['img']['tmp_name']){
            $params['img'] = $this->up_img('img','images/banner_img');
        }else{
            $params['img'] = $params['old_img'];
        }
        $this->DAO->update_activity_pop($params);
        die(json_encode($this->succeed_msg('修改成功','pop_list')));
    }

    public function del_pop($id){
        $info = $this->DAO->get_pop_info($id);
        if(!$info){
            die(json_encode($this->error_msg('查无此活动')));
        }
        $this->DAO->del_activity_pop($id);
        die(json_encode($this->succeed_del('删除成功','pop_list')));
    }

}
?>