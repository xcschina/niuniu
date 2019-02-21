<?php
COMMON('baseCore','uploadHelper');
DAO('coupon_dao');
class coupon_web extends baseCore{

    public $DAO;
    public $id;

    public function __construct(){
        parent::__construct();
        $this->DAO = new coupon_dao();
    }

    public function coupon_list(){
        $params = $_POST;
        $coupon_list = $this->DAO->get_coupon_list($params,$this->pageCurrent);
        foreach($coupon_list as $key=>$value){
            $coupon_list[$key]['count_total'] = $this->DAO->get_count_total($value['id']);
            $coupon_list[$key]['count_receive'] = $this->DAO->get_count_receive($value['id']);
        }
        $this->assign("params", $params);
        $this->assign("coupon_list", $coupon_list);
        $this->pageInfo($this->pageCurrent);
        $this->display("coupon_list.html");
    }

    public function coupon_add(){
        $game_list = $this->DAO->get_game_list();
        $channels = $this->DAO->get_channel_list();
        $this->assign("channel_list", $channels);
        $this->assign("game_list", $game_list);
        $this->display("coupon_add.html");
    }

    public function coupon_edit($id){
        $this->coupon_info($id);
        $this->display("coupon_edit.html");
    }

    public function details($id){
        $this->coupon_info($id);
        $this->display("coupon_details.html");
    }

    public function coupon_review($id){
        $this->coupon_info($id);
        $this->display("coupon_review.html");
    }

    public function coupon_show($id){
        $this->assign("id", $id);
        $this->display("coupon_show.html");
    }

    public function coupon_info($id){
        $coupon_info = $this->DAO->get_coupon_info($id);
        if($coupon_info['channel_id']){
            $this->V->assign("channel_id", json_decode($coupon_info['channel_id']));
        }
        if($coupon_info['apply_type']==3 && $coupon_info['game_id']){
            $this->V->assign("game_id", json_decode($coupon_info['game_id']));
        }
        if($coupon_info['apply_type']==2 && $coupon_info['game_id']){
            $goods_list=$this->DAO->get_goods_list($coupon_info['game_id']);
            $this->V->assign("goods_list", $goods_list);
        }
        $game_list = $this->DAO->get_game_list();
        $channels = $this->DAO->get_channel_list();
        $this->assign("channel_list", $channels);
        $this->assign("game_list", $game_list);
        $this->assign("info", $coupon_info);
    }

    public function coupon_save(){
        if(!$_POST){
            die(json_encode($this->error_msg("网络异常,请刷新后重新提交.")));
        }
        $params = $_POST;
        $params = $this->coupon_save_verify($params);
        if($params['start_time']) {
            $params['start_time'] = strtotime($params['start_time']);
        }
        if($params['end_time']) {
            $params['end_time'] = strtotime($params['end_time']);
        }
        $coupon_id = $this->DAO->save_coupon($params);
        $this->insert_coupon_apply_type($coupon_id,$params);
        echo json_encode($this->succeed_msg("优惠券添加成功", "coupon_list"));
    }

    public function coupon_update(){
        $params = $_POST;
        if (!$params) {
            die(json_encode($this->error_msg("网络异常,请刷新后重新提交.")));
        }
        $params = $this->coupon_save_verify($params);
        if ($params['start_time']) {
            $params['start_time'] = strtotime($params['start_time']);
        }
        if ($params['end_time']) {
            $params['end_time'] = strtotime($params['end_time']);
        }
        $this->DAO->update_coupon($params);
        $this->update_coupon_apply_type($params);
        echo json_encode($this->succeed_msg("优惠券修改成功", "coupon_list"));
    }

    public function review_status(){
        $params = $_POST;
        if (!$params || !$params['id']) {
            die(json_encode($this->error_msg("网络异常,请刷新后重新提交.")));
        }
        if ($params['review_status'] == 3 && !$params['reason']) {
            die(json_encode($this->error_msg("请填写不通过理由.")));
        }
        $this->DAO->update_review_status($params);
        if($params['review_status'] == 3){
            $this->DAO->save_coupon_operation_log($params['id'],$_SESSION["usr_id"],'优惠券审核不通过');
        }elseif($params['review_status'] == 2){
            $this->DAO->save_coupon_operation_log($params['id'],$_SESSION["usr_id"],'优惠券审核通过');
        }
        echo json_encode($this->succeed_msg("审核状态已更新", "coupon_list"));
    }

    public function issue_status(){
        if (!$_POST || !$_POST['id']) {
            die(json_encode($this->error_msg("网络异常,请刷新后重新提交.")));
        }
        $params = $_POST;
        if(!$params['issue_start_time']){
            die(json_encode($this->error_msg("请选择活动开始时间.")));
        }elseif(!$params['issue_end_time']){
            die(json_encode($this->error_msg("请选择活动结束时间.")));
        }elseif($params['issue_start_time'] > $params['issue_end_time']){
            die(json_encode($this->error_msg("活动开始时间必须大于活动结束时间.")));
        }
        $params['issue_start_time'] = strtotime($params['issue_start_time']);
        $params['issue_end_time'] = strtotime($params['issue_end_time']);
        $this->DAO->update_issue_status($params,1);
        $this->DAO->save_coupon_operation_log($params['id'],$_SESSION["usr_id"],'优惠券已上线,开始入库');
        $coupon_info = $this->DAO->get_coupon_info($params['id']);
        if($coupon_info){
            $coupon_logs = $this->DAO->get_coupon_logs($params['id']);
            if(!$coupon_logs){
                for ($i = 0; $i < $coupon_info['total']; $i++) {
                    $this->DAO->save_user_log($params['id'], 0, $coupon_info['type']);
                }
            }
            $this->DAO->save_coupon_operation_log($params['id'],$_SESSION["usr_id"],'优惠券入库成功');
        }else{
            die(json_encode($this->error_msg("未获取到活动配置,请刷新后重试.")));
        }
        echo json_encode($this->succeed_msg("上架状态已更新", "coupon_list"));
    }

    public function coupon_hide($id){
        if (!$id) {
            die(json_encode($this->error_msg("网络异常,请刷新后重新提交.")));
        }
        $this->DAO->update_coupon_status($id,2);
        $this->DAO->save_coupon_operation_log($id,$_SESSION["usr_id"],'优惠券手动下线成功');
        echo json_encode($this->unclose_succeed_msg("活动下线成功","coupon_list"));
    }

    public function coupon_log($id){
        if (!$id) {
            die(json_encode($this->error_msg("网络异常,请刷新后重新提交.")));
        }
        $params = $_POST;
        if($params['time']){
            $params['time']=strtotime($params['time']);
        }
        if($params['time2']){
            $params['time2']=strtotime($params['time2']);
        }
        $coupon_logs = $this->DAO->get_coupon_log($params,$id, $this->pageCurrent);
        $this->assign("params", $params);
        $this->assign("coupon_logs", $coupon_logs);
        $this->assign("id", $id);
        $this->pageInfo($this->pageCurrent);
        $this->display("coupon_log.html");
    }

    public function send_view($id){
        $this->assign("id", $id);
        $this->display("send_view.html");
    }

    public function send_coupon($id){
        if (!$id) {
            die(json_encode($this->error_msg("网络异常,请刷新后重新提交.")));
        }
        $params = $_POST;
        if(!$params['user_id']){
            die(json_encode($this->error_msg("请填写玩家ID.")));
        }
        $coupon_info = $this->DAO->get_coupon_info($id);
        $this->DAO->save_coupon_operation_log($id,$_SESSION["usr_id"],'开始发券');
        if($coupon_info){
            $coupon_info['send_content']=$coupon_info['content'];
            $user_arr = explode(",", $params['user_id']);
            $count = $this->DAO->count_coupon_logs($id);
            $num = count($user_arr);
            if(($count['count']+$num) > 1000){
                die(json_encode($this->error_msg("数量已超过限制,请新建优惠券重新发送！")));
            }
            foreach ($user_arr as $key => $val) {
                if ($val && is_numeric($val)) {
                    $user_log = $this->DAO->get_user_log($id, $val, $coupon_info['type']);
                    if (!$user_log) {
                        $log_id = $this->DAO->save_user_log($id, $val, $coupon_info['type']);
                        $coupon_info['content']=$coupon_info['send_content'].'<a href="#" onclick="receive_coupon('.$log_id.')">点击领取</a>';
                        $info = $this->DAO->get_massages_info($val, $coupon_info);
                        if(!$info){
                            $this->DAO->add_massages_info($val,$coupon_info);
                        }
                    }
                }
            }
            $this->DAO->save_coupon_operation_log($id,$_SESSION["usr_id"],'完成发券');
        }else{
            die(json_encode($this->error_msg("未获取到活动配置,请刷新后重试.")));
        }
        $this->DAO->update_coupon_status($id,3);
        echo json_encode($this->succeed_msg("站内信发送成功","coupon_list"));
    }

    private function update_coupon_apply_type($params){
        if($params['applicable']=='1'){
            $this->DAO->update_coupon_apply_type($params['id'],$params['applicable'],0,0,0,0);
            $this->DAO->save_coupon_operation_log($params['id'],$_SESSION["usr_id"],'优惠券修改成功');
        }else if($params['applicable']=='2'){
            $channels=json_encode($params['channel']);
            $this->DAO->update_coupon_apply_type($params['id'],$params['applicable'],$channels,$params['game_id'],$params['pay_type'],$params['product_id']);
            $this->DAO->save_coupon_operation_log($params['id'],$_SESSION["usr_id"],'优惠券修改成功');
        }else if($params['applicable']=='3'){
            $channels=json_encode($params['channel']);
            $game_ids=json_encode($params['game_ids']);
            $this->DAO->update_coupon_apply_type($params['id'],$params['applicable'],$channels,$game_ids,$params['pay_type'],0);
            $this->DAO->save_coupon_operation_log($params['id'],$_SESSION["usr_id"],'优惠券修改成功');
        }
    }

    private function insert_coupon_apply_type($coupon_id,$params){
        if($params['applicable']=='1'){
            $this->DAO->save_coupon_apply_type($coupon_id,$params['applicable'],0,0,0,0);
            $this->DAO->save_coupon_operation_log($coupon_id,$_SESSION["usr_id"],'新建优惠券成功');
        }else if($params['applicable']=='2'){
            $channels=json_encode($params['channel']);
            $this->DAO->save_coupon_apply_type($coupon_id,$params['applicable'],$channels,$params['game_id'],$params['pay_type'],$params['product_id']);
            $this->DAO->save_coupon_operation_log($coupon_id,$_SESSION["usr_id"],'新建优惠券成功');
        }else if($params['applicable']=='3'){
            $channels=json_encode($params['channel']);
            $game_ids=json_encode($params['game_ids']);
            $this->DAO->save_coupon_apply_type($coupon_id,$params['applicable'],$channels,$game_ids,$params['pay_type'],0);
            $this->DAO->save_coupon_operation_log($coupon_id,$_SESSION["usr_id"],'新建优惠券成功');
        }
    }

    private function coupon_save_verify($params){
        if(!$params['coupon_name']){
            die(json_encode($this->error_msg("请填写优惠券名称!")));
        }
        if($params['coupon_type']=="1"){
            $params['total_amount'] = 0;
            $params['discount_amount'] = 0;
            if ($params['full_amount'] =='') {
                die(json_encode($this->error_msg("请填写打折劵满的额度!")));
            }elseif (empty($params['discount'])) {
                die(json_encode($this->error_msg("折扣不能为空!")));
            } elseif (!is_numeric($params['discount'])) {
                die(json_encode($this->error_msg("折扣只能填写数字!")));
            }
            $params['total_amount'] = $params['full_amount'];
        }elseif($params['coupon_type']=="2"){
            $params['discount'] = 0;
            if(!$params['total_amount']){
                die(json_encode($this->error_msg("请填写满减劵满的额度!")));
            }else if(!$params['discount_amount']){
                die(json_encode($this->error_msg("请填写满减劵减的额度!")));
            }else if($params['total_amount'] <= $params['discount_amount']) {
                die(json_encode($this->error_msg("请填写减额度大于等于满的额度!")));
            }
        }

        if($params['valid_type']=="1"){
            $params['start_time'] = 0;
            $params['end_time'] = 0;
            if(!$params['valid_days']){
                die(json_encode($this->error_msg("请填写有效日期!")));
            }
        }elseif($params['valid_type']=="2"){
            $params['valid_days'] = 0;
            if(!$params['start_time']||!$params['end_time']){
                die(json_encode($this->error_msg("请选择有效时间!")));
            }
        }

        if($params['applicable']=="2" && !$params['game_id']){
            die(json_encode($this->error_msg("请选择指定游戏!")));
        }
        if($params['send_type']=="1"&&!$params['total']){
            die(json_encode($this->error_msg("请填写发行数量!")));
        }elseif($params['send_type']=="2"){
            $params['total']=0;
        }
        if(!$params['content']){
            die(json_encode($this->error_msg("请填写领取提示!")));
        }
        return $params;
    }
}