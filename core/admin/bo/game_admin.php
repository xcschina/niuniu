<?php
COMMON('adminBaseCore','pageCore');
DAO('game_admin_dao');
class game_admin extends adminBaseCore{
    public $DAO;
    public $channel;
    public function __construct(){
        parent::__construct();
        $this->DAO = new game_admin_dao();
        $this->channel = array(
            '00008'=>'应用宝',
            '00009'=>'魔域混服',
            '00010'=>'官服'
        );
//        $this->channel = array(
//            array('channel_id'=>'00008','channel_name'=>'应用宝'),
//            array('channel_id'=>'00009','channel_name'=>'魔域混服'),
//        );
    }
    public function game_list_view(){
        $params=$_POST;
        $list = $this->DAO->get_app_list($this->page,$params);
        $applist = $this->DAO->get_all_app();
        $page = $this->pageshow($this->page, "ccm_game.php?act=list&");
        $this->assign("params",$params);
        $this->assign("app_list",$applist);
        $this->assign("datalist", $list);
        $this->assign("page_bar", $page->show());
        $this->display("chamber/ccm_game_list.html");
    }
    public function game_add_view(){
        $this->assign('channel_list',$this->channel);
        $this->display("chamber/ccm_game_add.html");
    }
    public function game_do_add(){
        if ($_POST['app_id_add'] && $_POST['app_name_add'] && $_POST['type_add']){
            $params = $_POST;
            //验证app_id重复
            if($params['chamber_type'] == '1'){
                if(!$params['payment_scale']){
                    $this->error_msg("内部商会使用的游戏，回款比例不能为空");
                }elseif($params['payment_scale']<0){
                    $this->error_msg('回款比例不能小于0');
                }elseif(!preg_match("/(^([0-9])*$)|(^[0-9]{1,3}\.([0-9]|0[0-9]|[1-9][0-9])*$)/",$params['payment_scale'])){
                    $this->error_msg('回款比例非法');
                }
                if(!$params['goods_scale']){
                    $this->error_msg("内部商会使用的游戏，魔石比例不能为空");
                }elseif($params['goods_scale']<0){
                    $this->error_msg('魔石比例不能小于0');
                }elseif(!preg_match("/(^([0-9])*$)|(^[0-9]{1,3}\.([0-9]|0[1-9]|[1-9][0-9])*$)/",$params['goods_scale'])){
                    $this->error_msg('魔石比例非法');
                }
            }
            $app_res = $this->DAO->get_app_by_appid($params['app_id_add']);
            if (empty($app_res)){
                $res = $this->DAO->insert_app($params);
                if ($res){
                    $this->succeed_msg("新增成功");
                }else{
                    $this->error_msg("新增失败");
                }
            }else{
                $this->error_msg("此APPID已经被使用");
            }
        }else{
            $this->error_msg("缺少必填项");
        }
    }
    public function game_edit_view($app_id){
        $app_info = $this->DAO->get_app_by_appid($app_id);
        $this->assign('channel_list',$this->channel);
        $this->assign("appinfo",$app_info);
        $this->display("chamber/ccm_game_edit.html");
    }
    public function game_do_edit(){
        if ($_POST['app_id_edit'] && $_POST['app_name_edit'] && $_POST['type_edit']){
            $params = $_POST;
            //验证app_id重复
            if($params['chamber_type'] == '1'){
                if(!$params['payment_scale']){
                    $this->error_msg("内部商会使用的游戏，回款比例不能为空");
                }elseif($params['payment_scale']<0){
                    $this->error_msg('回款比例不能小于0');
                }elseif(!preg_match("/(^([0-9])*$)|(^[0-9]{1,3}\.([0-9]|0[0-9]|[1-9][0-9])*$)/",$params['payment_scale'])){
                    $this->error_msg('回款比例非法');
                }
                if(!$params['goods_scale']){
                    $this->error_msg("内部商会使用的游戏，魔石比例不能为空");
                }elseif($params['goods_scale']<0){
                    $this->error_msg('魔石比例不能小于0');
                }elseif(!preg_match("/(^([0-9])*$)|(^[0-9]{1,3}\.([0-9]|0[1-9]|[1-9][0-9])*$)/",$params['goods_scale'])){
                    $this->error_msg('魔石比例非法');
                }
            }
            $app_res = $this->DAO->get_app_check($params['app_id_edit'],$params['id']);
            if (empty($app_res)){
                $this->DAO->update_app($params);
                $this->succeed_msg("修改成功");
            }else{
                $this->error_msg("此APPID已经被使用");
            }
        }else{
            $this->error_msg("缺少必填项");
        }
    }
}