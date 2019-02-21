<?php
COMMON('baseCore', 'pageCore');
DAO('activity_dao');
class activity_web extends baseCore{
    public $DAO;
    public $COMDAO;

    public function __construct(){
        parent::__construct();
        $this->DAO = new activity_dao();
        $this->user_id=$_SESSION['user_id'];
    }

    public function activity_view($id){
        $_SESSION['login_back_url'] = ltrim($_SERVER['REQUEST_URI'],'/');
        $activity = $this->DAO->get_activity_info($id);
        $receive = $this->DAO->get_receive_id($this->user_id,$id);
        $pc_img = explode(",",$activity['pc_img']);
        $box_img = explode(",",$activity['box_img']);
        $this->assign("pc_img",$pc_img);
        $this->assign("box_img",$box_img);
        $this->assign("receive",$receive);
        $this->assign("activity",$activity);
        $this->display("activity.html");
    }

    public function activity_ajax(){
        $result = array('code' => 0, 'desc' => '网络丢了,请刷新后重新领取.');
        $params = $_POST;
        if(empty($params['user_id'])){
            die(json_encode($result));
        }elseif(empty($params['id'])){
            $result['desc'] = "请求出错";
            die(json_encode($result));
        }else{
            $receive = $this->DAO->get_receive_id($params['user_id'],$params['id']);
            if($receive){
                $result['desc'] = "您已领取过,不能再领取了";
                die(json_encode($result));
            }
        }
        $activity = $this->DAO->get_activity_info($params['id']);
        if($activity['end_time']<time()){
            $result['desc'] = "活动已结束，请参与其它精彩活动！";
            die(json_encode($result));
        }
        $gifts = explode(",",$activity['gift_id']);
        $gift_id = $this->get_gift_info($gifts);
        $fulls = explode(",",$activity['full_id']);
        $full_id = $this->get_coupon_info($fulls);
        $coupons = explode(",",$activity['coupon_id']);
        $coupon_id = $this->get_coupon_info($coupons);
        $prize_arr = array();
        if($coupon_id){
            $prize_id = "1";
            array_push($prize_arr,$prize_id);
        }
        if($full_id){
            $prize_id = "2";
            array_push($prize_arr,$prize_id);
        }
        if($gift_id){
            $prize_id = "3";
            array_push($prize_arr,$prize_id);
        }
        if(!$prize_arr){
            $result["desc"] = "来晚一步，宝物都被人抢光了。请参与其它精彩活动！";
            die(json_encode($result));
        }
        $prize = $prize_arr[array_rand($prize_arr,1)];
        // 1 专属优惠券 2 全场通用券 3 游戏礼包
        if($prize == 1){
            $coupon['discount_amount'] = '';
            foreach($coupon_id as $key => $value){
                $coupon['discount_amount'] += $this ->get_coupon_date($value,$params['user_id']);
            }
            $coupon['num'] = count($coupon_id);
            $this->DAO->insert_activity_info($params['user_id'],$params['id']);
            die(json_encode(array("code" => 1,"type" => "coupon","info" => $coupon)));
        }else if($prize == 2){
            $full = array_rand($full_id,1);
            $coupon['discount_amount'] = $this->get_coupon_date($full_id[$full],$params['user_id']);
            $coupon['num'] = 1;
            $this->DAO->insert_activity_info($params['user_id'],$params['id']);
            die(json_encode(array("code" => 1,"type" => "common","info" => $coupon)));
        }else if($prize == 3){
            $gift = array_rand($gift_id,1);
            $gift_code = $this->DAO->get_gift($gift_id[$gift]);
            $this->DAO->update_code_status($gift_code,$params['id'], $params['user_id'], $gift_id[$gift]);
            die(json_encode(array("code" => 1,"type" => "gift","info" => $gift_code)));
        }
    }

    public  function get_coupon_date($coupon_id,$user_id){
        $coupon = '';
        $coupon_info = $this->DAO->get_coupon_info($coupon_id,$user_id);
        if(!$coupon_info){
            $last_coupon = $this->DAO->get_coupon_last_log($coupon_id);
            $valid_day = $this->DAO->get_type_coupon($coupon_id);
            if($valid_day['valid_type'] == 1){
                $endtime = time()+($valid_day['valid_days']*3600);
                $this->DAO->update_coupon_log_date($last_coupon['id'],$endtime,$user_id);
            }else{
                $this->DAO->update_coupon_log($last_coupon['id'],$valid_day['start_time'],$valid_day['end_time'],$user_id);
            }
            $coupon = $valid_day['discount_amount'];
        }
        return $coupon;
    }

    public function get_coupon_info($coupons){
        if(!empty($coupons)){
            foreach($coupons as $key => $data){
                $coupon = $this->DAO->get_coupon_last_log($data);
                if(!$coupon){
                    unset($coupons[$key]);
                }
            }
            return $coupons;
        }else{
            return "";
        }
    }

    public function get_gift_info($gifts){
        if(!empty($gifts)){
            foreach($gifts as $key => $data){
                $gift = $this->DAO->get_gift($data);
                if(!$gift){
                    unset($gifts[$key]);
                }
            }
            return $gifts;
        }else{
            return "";
        }
    }

}
?>
