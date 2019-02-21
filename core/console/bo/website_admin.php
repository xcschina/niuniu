<?php
COMMON('baseCore','uploadHelper');
DAO('website_admin_dao','common_dao');

class website_admin extends baseCore{

    public $DAO;
    public $COMMON;
    public $game_info;

    public function __construct(){
        parent::__construct();
        $this->DAO = new website_admin_dao();
        $this->common = new common_dao();
    }

    public function promoter_list(){
        $params = $_POST;
        $promoter_list = $this->DAO->get_promoter_list($params,$this->pageCurrent);
        if($promoter_list){
            foreach($promoter_list as $k=>$v){
                $user_list = $this->DAO->get_user_list($v['code']);
                $promoter_list[$k]['count']=count($user_list);
            }
        }
        $this->assign("params",$params);
        $this->pageInfo($this->pageCurrent);
        $this->assign("info",$promoter_list);
        $this->display("promoter_list.html");
    }

    public function promoter_add(){
        $this->display("promoter_add_view.html");
    }

    public function promoter_save(){
        $params = $_POST;
        if(!$params['name']||!$params['phone_num']||!$params['code']){
            die(json_encode($this->error_msg("请填写所以参数.")));
        }
        if(!$this->is_mobile($_POST['phone_num'])){
            die(json_encode($this->error_msg("手机格式不对")));
        }
        $is_use = $this->DAO->get_promoter_code($params['code']);
        if(!empty($is_use)){
            die(json_encode($this->error_msg("代码已被使用，请使用新代码")));
        }
        $url = "http://www.66173.cn/?promoter_id=".$params['code'];
        $this->DAO->insert_promoter_info($params,$url);
        die(json_encode($this->succeed_msg("推广人添加成功！","promoter_list")));
    }


    public function list_view(){
        $datas = $this->DAO->get_promotion($this->pageCurrent);
        $this->pageInfo($this->pageCurrent);
        $this->assign("datas", $datas);
        $this->display("website_list.html");
    }

    public function add_view(){
        $game_list = $this->DAO->get_promotion_game();
        $this->assign("game_list", $game_list);
        $this->display("website_add.html");
    }

    public function do_save(){
        $params = $_POST;
        if(!$params['ch_name']){
            die(json_encode($this->error_msg("请输入渠道名称")));
        }
        if(!$params['game_id']){
            die(json_encode($this->error_msg("请选择游戏")));
        }
        if(!$_FILES['game_icon']['tmp_name']){
            die(json_encode($this->error_msg("请上传二维码")));
        }
        if(!$params['down_url']){
            die(json_encode($this->error_msg("请填写安卓下载地址")));
        }
        $game_icon = $this->up_game_icon("game_icon","",time(),0);
        $params['qr_url'] = $game_icon['path'];
        if(!$params['qr_url']){
            die(json_encode($this->error_msg("二维码上传失败,请联系管理员.")));
        }
        $this->DAO->add_promotion($params);
        die(json_encode($this->succeed_msg("推广渠道信息添加成功","website_list")));
    }

    protected function up_game_icon($img_name, $is_point_color='', $file_name = '', $is_record = 1) {
        $upFile = new uploadHelper($img_name, GAME_ICON, $is_record);
        $upFile->point_color = $is_point_color;
        $upFile->upload($file_name);

        if ($upFile->occur_err()) {
            $error = $upFile->get_err_msgs();
            $_SESSION['msg'] = $error[0];
            die(json_encode($this->error_msg($img_name."上传失败,失败原因：".$error[0])));
        } else {
            return array(
                'phy_path'=>$upFile->phy_files_path,
                "path"=>$upFile->get_rel_file_path(),
                "width"=>$upFile->img_width,
                "height"=>$upFile->img_height,
                "color"=>$upFile->point_color
            );
        }
    }

    public function website_info(){
        $game = $this->DAO->get_game_info($this->pageCurrent);
        $this->assign("game",$game);
        $this->pageInfo($this->pageCurrent);
        $this->display("website_info.html");
    }

    public function add_info(){
        $game_list = $this->DAO->get_game_page();
        $this->assign("game_list", $game_list);
        $this->display("website_add_info.html");
    }

    public function add_save(){
        $params = $_POST;
        if(!$params['ch_name']){
            die(json_encode($this->error_msg("请输入渠道名称")));
        }
        if(!$params['page_id']){
            die(json_encode($this->error_msg("请选择游戏")));
        }
        if(!$_FILES['game_icon']['tmp_name']){
            die(json_encode($this->error_msg("请上传二维码")));
        }
        if(!$params['down_url']){
            die(json_encode($this->error_msg("请填写安卓下载地址")));
        }
        $game_icon = $this->up_game_icon("game_icon","",time(),0);
        $params['qr_url'] = $game_icon['path'];
        if(!$params['qr_url']){
            die(json_encode($this->error_msg("二维码上传失败,请联系管理员.")));
        }
        $this->DAO->add_promotion_info($params);
        die(json_encode($this->succeed_msg("推广信息添加成功","website_info")));
    }


}