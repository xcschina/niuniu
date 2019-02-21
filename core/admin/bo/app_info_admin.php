<?php
COMMON('adminBaseCore','pageCore','uploadHelper');
DAO('app_info_dao');

class app_info_admin extends adminBaseCore{
    public $DAO;
    public $tags;

    public function __construct() {
        parent::__construct();
        $this->DAO = new app_info_dao();
        $this->tags = array(
            '101' => "角色",
            '102' => "策略",
            '103' => "卡牌",
            '104' => "其他"
        );
    }

    public function info_list(){
        $params = $this->get_params($_POST,$_GET);
        $list = $this->DAO->get_info_list($this->page,$params);
        $app_list = $this->DAO->get_app_list();
        foreach($list as $key=>$data){
            if(empty($data['tags'])){
                $list[$key]['new_tags']="";
            }else{
                $tags_array = explode(',',$data['tags']);
                $new_array = array();
                foreach ($tags_array as $k=>$v){
                    if($v){
                        array_push($new_array,$this->tags[$v]);
                    }
                    $list[$key]['new_tags'] = implode(',',$new_array);
                }
            }
        }
        $page = $this->pageshow($this->page,"app_info.php?act=list&");
        $this->assign("page_bar",$page->show());
        $this->assign("app_list",$app_list);
        $this->assign("params",$params);
        $this->assign("list",$list);
        $this->display("app_info_list.html");
    }

    public function add_view(){
        $app_list = $this->DAO->get_app_list();
        $game_list = $this->DAO->get_66173_game();
        $this->assign("game_list",$game_list);
        $this->page_hash();
        $this->assign("tags_list",$this->tags);
        $this->assign("app_list",$app_list);
        $this->display("app_info_add.html");
    }

    public function do_add(){
        $params = $_POST;
        if(!$_SESSION['usr_id']){
            $this->error_msg("请先登录");
        }
        if($params['pagehash'] != $_SESSION['page-hash']){
            $this->error_msg("参数异常!  001");
        }
        if(!$params['title'] || !$params['subtitle'] || !$params['app_id'] || !$params['down_url'] || !$params['app_size'] || !$params['system'] || !$params['desc']){
            $this->error_msg("缺少必填项");
        }
        if(!$_FILES['banner']['tmp_name']){
            $this->error_msg("游戏banner图不能为空");
        }else{
            $params['banner']=$this->up_img('banner',INTRO_IMG);
        }
        if(preg_match("/[\x7f-\xff]/", $params['down_url'])){
            $this->error_msg("下载地址不能含中文");
        }
        $array = get_headers($params['down_url'],1);
        if(!(preg_match('/200/',$array[0]) || preg_match('/302/',$array[0]))){
            $this->error_msg("请输入正确的游戏下载地址");
        }
        if( $_SERVER['HTTP_REFERER'] == "" ){
            header("Location:".$params['down_url']); exit;
        }
        $this->DAO->insert_apps_into($params);
        $this->succeed_msg();
    }

    public function edit_view($id){
        $info = $this->DAO->get_app_info($id);
        $app_list = $this->DAO->get_app_list();
        $tags = explode(",",$info['tags']);
        $this->page_hash();
        $game_list = $this->DAO->get_66173_game();
        $this->assign("game_list",$game_list);
        $this->assign("tags",$tags);
        $this->assign("app_list",$app_list);
        $this->assign("info",$info);
        $this->display("app_info_edit.html");
    }

    public function do_edit(){
        $params = $_POST;
        if(!$_SESSION['usr_id']){
            $this->error_msg("请先登录");
        }
        if($params['pagehash'] != $_SESSION['page-hash']){
            $this->error_msg("参数异常!  001");
        }
        if(!$params['title'] || !$params['subtitle'] || !$params['app_id'] || !$params['down_url'] || !$params['app_size'] || !$params['system'] || !$params['desc']){
            $this->error_msg("缺少必填项");
        }
        if(!$_FILES['banner']['tmp_name']){
            $params['banner'] = $params['old_banner'];
        }else{
            $params['banner'] = $this->up_img('banner',INTRO_IMG);
        }
        if(preg_match("/[\x7f-\xff]/", $params['down_url'])){
            $this->error_msg("下载地址不能含中文");
        }
        $array = get_headers($params['down_url'],1);
        if(!(preg_match('/200/',$array[0]) || preg_match('/302/',$array[0]))){
            $this->error_msg("请输入正确的游戏下载地址");
        }
        if( $_SERVER['HTTP_REFERER'] == "" )
        {
            header("Location:".$params['down_url']); exit;
        }
        $this->DAO->update_apps_into($params);
        $this->succeed_msg();
    }

    public function offline_view($id){
        $this->page_hash();
        $this->assign("id",$id);
        $this->display("app_info_offline.html");
    }

    public function do_offline(){
        $params = $_POST;
        if(!$_SESSION['usr_id']){
            $this->error_msg("请先登录");
        }
        if($params['pagehash'] != $_SESSION['page-hash']){
            $this->error_msg("参数异常!  001");
        }
        $this->DAO->update_info($params);
        $this->succeed_msg();
    }

    public function banner_list(){
        $list = $this->DAO->get_banner_list($this->page);
        $page = $this->pageshow($this->page,"app_info.php?act=banner_list&");
        $this->assign("page_bar",$page->show());
        $this->assign("list",$list);
        $this->display("app_banner_list.html");
    }

    public function add_banner(){
        $this->display("app_banner_add.html");
    }

    public function do_add_banner(){
        if($_FILES['banner']['tmp_name']){
            list($width,$height) = getimagesize($_FILES['banner']['tmp_name']);
            if($width == 1920 && $height == 588){
                $_POST['banner'] = $this->up_img("banner","niuguo/img");
            }else{
                $this->error_msg("请上传1920*588的图片");
            }
        }else{
            $this->error_msg("banner不能为空");
        }
        if($_POST['url']){
            $array = get_headers($_POST['url'],1);
            if(!(preg_match('/200/',$array[0]) || preg_match('/302/',$array[0]))){
                $this->error_msg("图片链接错误！");
            }
        }
        if( $_SERVER['HTTP_REFERER'] == "" )
        {
            header("Location:".$_POST['url']); exit;
        }
        $this->DAO->insert_app_banner($_POST);
        $this->succeed_msg("添加成功");
    }

    public function is_del($id){
        $this->assign("id",$id);
        $this->display("app_banner_del.html");
    }

    public function do_del($id){
        $info = $this->DAO->get_banner_info($id);
        if(!$info){
            $this->error_msg("未查询到该轮播图");
        }
        $this->DAO->update_app_banner($id);
        $this->succeed_msg("下架成功");
    }

}