<?php
COMMON('baseCore','uploadHelper');
DAO('reserve_dao');

class reserve_admin extends baseCore
{

    public $DAO;
    public $prize_channel;
    public function __construct()
    {
        parent::__construct();
        $this->DAO = new reserve_dao();

    }

    public function reserve_list(){
        $params = $_POST;
        $game_list = $this->DAO->get_game();
        $reserve_name = $this->DAO->get_reserve_name();
        $reserve = $this->DAO->get_reserve($params,$this->pageCurrent);
        $this->pageInfo($this->pageCurrent);
        $this->assign("params",$params);
        $this->assign("reserve_name",$reserve_name);
        $this->assign("reserve",$reserve);
        $this->assign("game_list",$game_list);
        $this->display("reserve_list.html");
    }

    public function reserve_add(){
        $game_list = $this->DAO->get_game();
        $this->assign("game_list",$game_list);
        $this->display("reserve_add.html");
    }

    public function reserve_save(){
        $params = $_POST;
        if(count($params['gift_id'])>1){
            die(json_encode($this->error_msg("只能选择一个预约礼包")));
        }
        if(strtotime($params['end_time']) < strtotime($params['start_time'])){
            die(json_encode($this->error_msg("活动结束时间不能比活动开始时间早")));
        }
        if(!preg_match("/(http:\/\/)|(https:\/\/)/i",$params['down_url'])){
            die(json_encode($this->error_msg("请输入以http://开头的下载地址")));
        }
        $array = get_headers($params['down_url'],1);
        if(!preg_match('/200/',$array[0])){
            die(json_encode($this->error_msg("请输入正确的游戏下载地址")));
        }
        if($_SERVER['HTTP_REFERER'] == "" ){
            header("Location:".$params['down_url']); exit;
        }
        if($_FILES['share_img']['tmp_name']){
            $params['share_img'] = $this->up_img('share_img','images/activity_img');
        }
        $this->DAO->add_reserve($params);
        die(json_encode($this->succeed_msg("活动添加成功","reserve_list")));
    }

    public function reserve_edit_view($id){
        $game_list = $this->DAO->get_game();
        $reserve = $this->DAO->get_reserve_info($id);
        $gifts = $this->DAO->get_gift_list($reserve['game_id']);
        $gifts_id = explode(",",$reserve['gifts_id']);
        $this->assign("gifts_id",$gifts_id);
        $this->assign("gifts",$gifts);
        $this->assign("reserve",$reserve);
        $this->assign("game_list",$game_list);
        $this->display("reserve_edit.html");
    }

    public function gift_info($game_id){
        $gift_list = $this->DAO->get_gift_list($game_id);
        $coupon_list = $this->DAO->get_coupon_list($game_id);
        $result = array("gift" => $gift_list,"coupon" => $coupon_list);
        die(json_encode($result));
    }

    public function reserve_edit(){
        $params = $_POST;
        $reserve = $this->DAO->get_reserve_info($params['id']);
        if(count($params['gift_id']) > 1){
            die(json_encode($this->error_msg("只能选择一个预约礼包")));
        }
        if($params['virtual_num'] < $reserve['virtual_num']){
            die(json_encode($this->error_msg("虚拟人数不能比开始设置的值小")));
        }
        if(!preg_match("/(http:\/\/)|(https:\/\/)/i",$params['down_url'])){
            die(json_encode($this->error_msg("请输入以http://开头的下载地址")));
        }
        $array = get_headers($params['down_url'],1);
        if(!preg_match('/200/',$array[0])){
            die(json_encode($this->error_msg("请输入正确的游戏下载地址")));
        }
        if( $_SERVER['HTTP_REFERER'] == "" ){
            header("Location:".$params['down_url']); exit;
        }
        if(strtotime($params['end_time']) < strtotime($params['start_time'])){
            die(json_encode($this->error_msg("活动结束时间不能比活动开始时间早")));
        }
        if(!$_FILES['share_img']['tmp_name']){
            $params['share_img'] = $params['old_share_img'];
        }else{
            $params['share_img'] = $this->up_img('share_img','images/activity_img');
        }
        $this->DAO->update_reserve($params);
        die(json_encode($this->succeed_msg("活动修改成功","reserve_list")));
    }

    public function prize_name_edit($id){
        $info = $this->DAO->get_reserve_info($id);
        $prize_list = $this->DAO->get_prize($id);
        foreach($prize_list as $key => $data){
            if($data){
                $prize_list[$key]['sort_name'] = "奖品".$data['sort_id'];
            }
        }
        $this->assign("prize_list",$prize_list);
        $this->assign("info",$info);
        $this->display("prize_name_edit.html");
    }

    public function do_save_prize(){
        $act_id = $_POST['act_id'];
        unset($_POST['act_id']);
        $reserve = $this->DAO->get_reserve_prize($act_id);
        for($i = 1;$i < 9; $i++){
            $params = $_POST['prize'.$i];
            $this->params_valida($_POST);
            if($reserve){
                $this->DAO->update_prize_channel($act_id, $params, $i);
            }else{
                $this->DAO->prize_channel_add($act_id, $params, $i);
            }
        }
        echo json_encode($this->unclose_succeed_msg("修改成功"));
    }

    public function reserve_draw_log($id){
        $params = $_POST;
        $draw_log = $this->DAO->get_reserve_draw_log($id,$params,$this->pageCurrent);
        $this->pageInfo($this->pageCurrent);
        $this->assign("act_id",$id);
        $this->assign("params",$params);
        $this->assign("draw_log",$draw_log);
        $this->display("reserve_draw_log.html");
    }

    public function reserve_log($id){
        $params = $_POST;
        $reserve_log = $this->DAO->get_reserve_log($id,$params,$this->pageCurrent);
        $this->pageInfo($this->pageCurrent);
        $this->assign("act_id",$id);
        $this->assign("params",$params);
        $this->assign("reserve_log",$reserve_log);
        $this->display("reserve_log.html");
    }

    function params_valida($params){
        $chance = "";
        foreach($params as $key=>$data){
            $chance += $data['chance'];
            if(!$data['title']){
                die(json_encode($this->error_msg("奖品名称不能为空")));
            }
            if($data['type'] == 2 || $data['type'] == 3 ){
                if(!$data['prize_id']){
                    die(json_encode($this->error_msg("类型非实物时，必须填写关联")));
                }
            }
        }
        if($chance > 100){
            die(json_encode($this->error_msg("奖品概率总和不能超过100")));
        }
    }

    public function del_reserve($id){
        $this->DAO->del_reserve($id);
        die(json_encode($this->succeed_del("成功删除预约活动","reserve_list")));
    }

    public function image_list(){
        $this->display("image_list.html");
    }

    public function upload_image(){
        $this->display("image_upload.html");
    }
}