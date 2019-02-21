<?php
COMMON('baseCore','uploadHelper');
DAO('disc_theme_dao','common_dao');

class disc_theme_admin extends baseCore
{

    public $DAO;
    public $COMMON;

    public function __construct()
    {
        parent::__construct();
        $this->DAO = new disc_theme_dao();
        $this->common = new common_dao();
    }

    public function theme_list(){
        $params = $_POST;
        $data_list = $this->DAO->get_theme_list($params,$this->pageCurrent);
        $game_list = $this->DAO->get_game_list();
        $this->pageInfo($this->pageCurrent);
        $this->assign("params",$params);
        $this->assign("game_list",$game_list);
        $this->assign("data_list",$data_list);
        $this->display("disc_theme_list.html");
    }

    public function add_view(){
        $game_list = $this->DAO->get_game_list();
        $this->assign("game_list",$game_list);
        $this->display("disc_theme_add.html");
    }

    public function add_save(){
        $params = $_POST;
        if(!$params['m_title'] || !$params['sub_title'] || !$params['introduce']){
            die(json_encode($this->error_msg("缺少必填项")));
        }
        if(!$params['re_game']){
            die(json_encode($this->error_msg("关联游戏不能为空")));
        }else{
            $params['re_game'] = implode(",",$params['re_game']);
        }
        if(!$_FILES['img']['tmp_name']){
            die(json_encode($this->error_msg("主题图片不能为空")));
        }else{
            $params['img'] = $this->up_img("img","images/banner_img");
        }
        if(!$_FILES['img1']['tmp_name']){
            die(json_encode($this->error_msg("主题详情顶部图片不能为空")));
        }else{
            $params['img1'] = $this->up_img("img1","images/banner_img");
        }
        $this->DAO->insert_theme($params);
        die(json_encode($this->succeed_msg("添加成功","theme_list")));
    }

    public function edit_view($id){
        $info = $this->DAO->get_theme_info($id);
        $game_list = $this->DAO->get_game_list();
        $re_game = explode(",",$info['re_game']);
        $this->assign("re_game",$re_game);
        $this->assign("game_list",$game_list);
        $this->assign("info",$info);
        $this->display("disc_theme_edit.html");
    }

    public function edit_save(){
        $params = $_POST;
        if(!$params['m_title'] || !$params['sub_title'] || !$params['introduce']){
            die(json_encode($this->error_msg("缺少必填项")));
        }
        if(!$params['re_game']){
            die(json_encode($this->error_msg("关联游戏不能为空")));
        }else{
            $params['re_game'] = implode(",",$params['re_game']);
        }
        if(!$_FILES['img']['tmp_name']){
            $params['img'] = $params['old_img'];
        }else{
            $params['img'] = $this->up_img("img","images/banner_img");
        }
        if(!$_FILES['img1']['tmp_name']){
            $params['img1'] = $params['old_img1'];
        }else{
            $params['img1'] = $this->up_img("img1","images/banner_img");
        }
        $this->DAO->update_theme($params);
        die(json_encode($this->succeed_msg("修改成功","theme_list")));
    }

    public function del_theme($id){
        $info = $this->DAO->get_theme_info($id);
        if(!$info){
            die(json_encode($this->error_msg("未查询到主题信息")));
        }else{
            $this->DAO->del_theme($id);
            die(json_encode($this->succeed_del("主题删除成功", "theme_list")));
        }
    }
}