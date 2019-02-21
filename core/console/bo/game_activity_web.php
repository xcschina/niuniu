<?php
COMMON('baseCore','uploadHelper');
DAO('game_activity_dao','common_dao');

class game_activity_web extends baseCore
{

    public $DAO;
    public $COMMON;

    public function __construct()
    {
        parent::__construct();
        $this->DAO = new game_activity_dao();
    }

    public function activity_info(){
        $params = $_POST;
        $game_list = $this->DAO->game_name();
        $activity_list = $this->DAO->activity_list($params,$this->pageCurrent);
        foreach($activity_list as $key=>$activity){
            $gifts = explode(",",$activity['gift_id']);
            $coupon_id = explode(",",$activity['coupon_id']);
            foreach($gifts as $k =>$data){
                $gift = $this->DAO->get_gift_info($data);
                $activity_list[$key]['gift'] .=$gift['title'].",";
            }
            foreach($coupon_id as $k => $info){
                $coupon = $this->DAO->get_coupon_info($info);
                $activity_list[$key]['coupon'] .=$coupon['name'].",";
            }
            $activity_list[$key]['coupon'] = rtrim($activity_list[$key]['coupon'],",");
            $activity_list[$key]['gift'] = rtrim($activity_list[$key]['gift'],",");
        }
        $this->pageInfo($this->pageCurrent);
        $this->assign("params",$params);
        $this->assign("game_list",$game_list);
        $this->assign("activity_list",$activity_list);
        $this->display("game_activity_info.html");
    }

    public function add_activity_view(){
        $game_list = $this->DAO->game_name();
        $full_list = $this->DAO->get_full_list();
        $this->assign("full_list",$full_list);
        $this->assign("game_list",$game_list);
        $this->display("game_activity_add.html");
    }

    public function gift_info($game_id){
        $gift_list = $this->DAO->get_gift_list($game_id);
        $coupon_list = $this->DAO->get_coupon_list($game_id);
        $result = array("gift" => $gift_list,"coupon" => $coupon_list);
        die(json_encode($result));
    }

    public function add_activity(){
        $params = $_POST;
        $params["pc_img"] = $this->up_imgs("pc_img");
        $params['m_img'] = $this->up_imgs("m_img");
        $params['box_img'] = $this->up_imgs("box_img");
        if(!$params['pc_img']){
            die(json_encode($this->error_msg("pc背景图不能为空")));
        }elseif (count(explode(",",$params['pc_img'])) != 4){
            die(json_encode($this->error_msg("pc背景图不能少于4张")));
        }
        if(!$params['m_img']){
            die(json_encode($this->error_msg("M站背景图不能为空")));
        }elseif (count(explode(",",$params['m_img'])) != 2){
            die(json_encode($this->error_msg("M站背景图不能少于2张")));
        }
        if(!$params['box_img']){
            die(json_encode($this->error_msg("宝箱产品展示图不能为空")));
        }
        $array = get_headers($params['down_url'],1);
        if(!preg_match('/200/',$array[0])){
            die(json_encode($this->error_msg("请输入正确的游戏下载地址")));
        }
        if(!preg_match("/(http:\/\/)|(https:\/\/)/i",$params['down_url'])){
            die(json_encode($this->error_msg("请输入以http://开头的下载地址")));
        }
        if( $_SERVER['HTTP_REFERER'] == "" )
        {
            header("Location:".$params['down_url']); exit;
        }
        foreach($params['gift_id'] as $key=>$gift){
            $remain = $this->DAO->get_gift_remin($gift);
            $params['gift_remain'] .=$remain['remain'].",";
        }
        foreach($params['full_id'] as $key=>$full){
            $num = $this->DAO->get_coupon_num($full);
            $params['full_remain'] .=$num['num'].",";

        }
        foreach($params['coupon_id'] as $key=>$coupon){
            $num = $this->DAO->get_coupon_num($coupon);
            $params['coupon_remain'] .=$num['num'].",";

        }
        $this->DAO->add_activity($params);
        die(json_encode($this->succeed_msg("活动信息添加成功","activity_info")));
    }

    protected function up_imgs($pic){
        $img_path = "";
        if($_FILES[$pic]['tmp_name'] && $_FILES[$pic]['tmp_name'][0]){
            $imgs = $this->batch_up_img($pic, PRODUCT_IMG);
            foreach($imgs as $key=>$img){
                if($img){
                    $img_path .=$img .",";
                }
            }
            $img_path = rtrim($img_path,",");
        }
        return $img_path;
    }

    public function game_activity_edit($id){
        $activity_info = $this->DAO->activity_info($id);
        $game_list = $this->DAO->game_name();
        $gifts = $this->DAO->get_gift_list($activity_info['game_id']);
        $coupons = $this->DAO->get_coupon_list($activity_info['game_id']);
        $full_list = $this->DAO->get_full_list();
        $gift_id = explode(",",$activity_info['gift_id']);
        $coupon_id = explode(",",$activity_info['coupon_id']);
        $full_id = explode(",",$activity_info['full_id']);
        $m_img = explode(",",$activity_info['m_img']);
        $pc_img = explode(",",$activity_info['pc_img']);
        $box_img = explode(",",$activity_info['box_img']);
        $this->assign("m_img",$m_img);
        $this->assign("pc_img",$pc_img);
        $this->assign("box_img",$box_img);
        $this->assign("full_list",$full_list);
        $this->assign("full_id",$full_id);
        $this->assign("gifts",$gifts);
        $this->assign("coupons",$coupons);
        $this->assign("gift_id",$gift_id);
        $this->assign("coupon_id",$coupon_id);
        $this->assign("game_list",$game_list);
        $this->assign("info",$activity_info);
        $this->display("game_activity_edit.html");
    }

    public function activity_edit(){
        $params = $_POST;
        if(!$params['activity_name']){
            die(json_encode($this->error_msg("请输入活动名")));
        }
        $array = get_headers($params['down_url'],1);
        if(!preg_match('/200/',$array[0])){
            die(json_encode($this->error_msg("请输入正确的游戏下载地址")));
        }
        if( $_SERVER['HTTP_REFERER'] == "" )
        {
            header("Location:".$params['down_url']); exit;
        }
        if(!preg_match("/(http:\/\/)|(https:\/\/)/i",$params['down_url'])){
            die(json_encode($this->error_msg("请输入以http://开头的地址")));
        }
        if(!$_FILES['pc_img']['tmp_name'][0]){
            $params['pc_img'] = $params['old_pc_img'];
        }else{
            $params['pc_img'] = $this->up_imgs("pc_img");
        }
        if(!$_FILES['m_img']['tmp_name'][0]){
            $params['m_img'] = $params['old_m_img'];
        }else{
            $params['m_img'] = $this->up_imgs("m_img");
        }
        if(!$_FILES['box_img']['tmp_name'][0]){
            $params['box_img'] = $params['old_box_img'];
        }else{
            $params['box_img'] = $this->up_imgs("box_img");
        }
        $this->DAO->update_activity($params);
        die(json_encode($this->succeed_msg("活动信息修改成功","activity_info")));
    }

    public  function del_activity($id){
        $this->DAO->del_activity($id);
        die(json_encode($this->succeed_del("活动信息删除成功","activity_info")));
    }

}