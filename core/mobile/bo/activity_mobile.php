<?php
COMMON('baseCore', 'pageCore');
DAO('activity_dao');

class activity_mobile extends baseCore{

    public $DAO;
    public $id;

    public function __construct(){
        parent::__construct();
        $this->DAO = new activity_dao();
    }

    public function activity_view(){
        $_SESSION['login_back_url'] = "/activity.php?act=index";
        $my_gift_1 = $this->DAO->get_my_gift($_SESSION['user_id'],271);
        $my_gift_2 = $this->DAO->get_my_gift($_SESSION['user_id'],277);
        $my_gift_3 = $this->DAO->get_my_gift($_SESSION['user_id'],273);
        $my_gift_4 = $this->DAO->get_my_gift($_SESSION['user_id'],270);
        $my_gift_5 = $this->DAO->get_my_gift($_SESSION['user_id'],276);

        $my_coupon_1 = $this->DAO->get_coupon_isdraw($_SESSION['user_id'],114);
        $my_coupon_2 = $this->DAO->get_coupon_isdraw($_SESSION['user_id'],132);
        $my_coupon_3 = $this->DAO->get_coupon_isdraw($_SESSION['user_id'],118);
        $my_coupon_4 = $this->DAO->get_coupon_isdraw($_SESSION['user_id'],119);
        $my_coupon_5 = $this->DAO->get_coupon_isdraw($_SESSION['user_id'],133);

        $rod_gift_1 = $this->DAO->get_my_gift_rod(271);
        $rod_gift_2 = $this->DAO->get_my_gift_rod(277);
        $rod_gift_3 = $this->DAO->get_my_gift_rod(273);
        $rod_gift_4 = $this->DAO->get_my_gift_rod(270);
        $rod_gift_5 = $this->DAO->get_my_gift_rod(276);

        $rod_coupon_1 = $this->DAO->get_coupon_last_log(114);
        $rod_coupon_2 = $this->DAO->get_coupon_last_log(132);
        $rod_coupon_3 = $this->DAO->get_coupon_last_log(118);
        $rod_coupon_4 = $this->DAO->get_coupon_last_log(119);
        $rod_coupon_5 = $this->DAO->get_coupon_last_log(133);

        $this->assign("game_1",$my_gift_1);
        $this->assign("game_2",$my_gift_2);
        $this->assign("game_3",$my_gift_3);
        $this->assign("game_4",$my_gift_4);
        $this->assign("game_5",$my_gift_5);

        $this->assign("coupon_1",$my_coupon_1);
        $this->assign("coupon_2",$my_coupon_2);
        $this->assign("coupon_3",$my_coupon_3);
        $this->assign("coupon_4",$my_coupon_4);
        $this->assign("coupon_5",$my_coupon_5);

        $this->assign("g_rod_1",$rod_gift_1);
        $this->assign("g_rod_2",$rod_gift_2);
        $this->assign("g_rod_3",$rod_gift_3);
        $this->assign("g_rod_4",$rod_gift_4);
        $this->assign("g_rod_5",$rod_gift_5);

        $this->assign("c_rod_1",$rod_coupon_1);
        $this->assign("c_rod_2",$rod_coupon_2);
        $this->assign("c_rod_3",$rod_coupon_3);
        $this->assign("c_rod_4",$rod_coupon_4);
        $this->assign("c_rod_5",$rod_coupon_5);
        $this->assign("user_id",$_SESSION['user_id']);
        $this->display("activity.html");
    }

    public function ajax_get_gift(){
        $params = $_POST;
        $data = array('msg' => 0, 'desc' => '网络丢了,请刷新后重新领取.');
        if(!$params['user_id']||!$params['gift_id']){
            die(json_encode($data));
        }
        $my_gift = $this->DAO->get_my_gift($params['user_id'],$params['gift_id']);
        if(!empty($my_gift)){
            $game_name  = $this->DAO->get_game_id($my_gift['game_id']);
            $data['game_name'] = $game_name['game_name'];
            $data['code'] = $my_gift['code'];
            $data['desc']='您已经领取过了，请到个人中心的我的兑换码处查看';
            die(json_encode($data));
        }else{
            $last_gift = $this->DAO->query_last_gift($params['gift_id']);
            if(!$last_gift){
                $data['desc']='礼包领完了.';
                die(json_encode($data));
            }
            $this->DAO->update_game_gifts($last_gift['id'],$params['user_id']);;
            $this->DAO->update_game_info_remain($last_gift['batch_id']);;
            $my_gift_2 = $this->DAO->get_gift($last_gift['id']);
            $data['code'] = $my_gift_2['code'];
            $data['msg']=1;
            $data['desc']='领取成功，请到个人中心的我的兑换码处查看';
            die(json_encode($data));
        }
    }


    //获取优惠卷
    public function ajax_get_coupon(){
        $params = $_POST;
        $data = array('msg' => 0, 'desc' => '网络丢了,请刷新后重新领取.');
        if(empty($params['user_id'])||!$params['coupon_id']){
            die(json_encode($data));
        }
        $is_not = $this->DAO->get_coupon_isnot($params['coupon_id']);
        if(empty($is_not)){
            $data['desc']='亲,优惠卷领完了.';
            die(json_encode($data));
        }
        $is_draw = $this->DAO->get_coupon_isdraw($params['user_id'],$params['coupon_id']);
        if(!empty($is_draw)){
            $data['desc']='亲,您已领取过该优惠卷了.';
            die(json_encode($data));
        }else{
            $last_coupon = $this->DAO->get_coupon_last_log($params['coupon_id']);
            $coupon_time = $this->DAO->get_coupon_time($params['coupon_id']);
            switch($params['coupon_id']){
                case '114':
                    $data['url'] = "http://m.66173.cn/game1600/character";
                    break;
                case '117':
                    $data['url'] = "http://m.66173.cn/game1611/character";
                    break;
                case '118':
                    $data['url'] = "http://m.66173.cn/game1566/character";
                    break;
                case '119':
                    $data['url'] = "http://m.66173.cn/game8/character";
                    break;
                case '115':
                    $data['url'] = "http://m.66173.cn/game1612/character";
                    break;
                case '133':
                    $data['url'] = "http://m.66173.cn/game1519/character";
                    break;
                case '132':
                    $data['url'] = "http://m.66173.cn/game84/character";
                    break;

            }
            if($coupon_time['valid_days'] > 0){
               $endtime = time()+($coupon_time['valid_days']*86400);
            }
            if($coupon_time['id'] == 119 or $coupon_time['id'] == 133 or $coupon_time['id'] == 132){
                $data['title'] = "购买任意金额首充均享受4.5折，仅限乐8";
            }else{
                $data['title'] = "购买任意金额首充均享受5.5折";
            }
            $data['coupon_name'] = "恭喜您领取".$coupon_time['name']."!";
            $this->DAO->update_coupon_log_date($last_coupon['id'],$endtime,$params['user_id'],$coupon_time['start_time'],$coupon_time['end_time']);
            $data['msg']=1;
            $data['desc']='领取成功';
            die(json_encode($data));
        }
    }

    public function ajax_draw_activity(){
        if(!$_POST['user_id']){
            $data = array('msg' => 0, 'desc' => '网络丢了,请刷新后重新领取.');
            die(json_encode($data));
        }

        $share = $this->DAO->get_share_log($_POST['user_id']);
        if($share['is_share'] == 1){
            $data['is_share'] = 1;
        }else{
            $data['is_share'] = 0;
        }
        if(empty($share)){
            $this->DAO->insert_share_log($_POST['user_id']);
            $data['num'] = 0;
        }else{
            if($share['award_num'] == 0){
                $data['msg'] = "0";
                $data['desc'] = "抽奖机会已用完！！";
                $data['num'] = $share['award_num'];
                die(json_encode($data));
            }else{
                $this->DAO->update_share_log(0,$_POST['user_id']);
                $data['share'] = 1;
            }
        }

        //添加iphone7和mi5判断
        //prize表示奖项内容，v表示中奖几率(若数组中七个奖项的v的总和为100，如果v的值为1，则代表中奖几率为1%，依此类推)
        $prize_arr = array(
            '0' => array('id' => 1, 'prize' => '198元代金券', 'v' => 0),
            '1' => array('id' => 2, 'prize' => '',  'v' => 20),
            '2' => array('id' => 3, 'prize' => '98元代金券',  'v' => 0),
            '3' => array('id' => 4, 'prize' => '648元代金券','v' => 0),
            '4' => array('id' => 5, 'prize' => '328元代金券',    'v' => 0),
            '5' => array('id' => 6, 'prize' => '',  'v' => 20),
            '6' => array('id' => 7, 'prize' => '30元代金券','v' => 0),
            '7' => array('id' => 8, 'prize' => '6元代金券',  'v' => 60)
        );

        foreach ($prize_arr as $k => $v) {
            $arr[$v['id']] = $v['v'];
        }
        $prize_id = $this->getRand($arr); //根据概率获取奖项id
        foreach ($prize_arr as $k => $v) { //获取前端奖项位置
            if ($v['id'] == $prize_id) {
                $prize_site = $k;
                break;
            }
        }
        $all = $prize_arr;
        $desc = $all[$prize_id -1];
        $count = $this->DAO->count_share_log();
        if($count['num']/100 == 1){
            $desc = $all[6];//中奖项
            $prize_site = 6;
        }
        switch($count['num']){
            case '500':
                $desc = $all[2];//中奖项
                $prize_site = 2;
                break;
            case '1000':
                $desc = $all[0];//中奖项
                $prize_site = 0;
                break;
            case '1500':
                $desc = $all[4];//中奖项
                $prize_site = 4;
                break;
            case '2500':
                $desc = $all[3];//中奖项
                $prize_site = 3;
                break;
        }
        $data['msg'] = 1;
        $data['prize_name'] = $desc['prize'];
        $data['prize_site'] = $prize_site;//前端奖项从-1开始
        $data['prize_id'] = $prize_id;
        switch($data['prize_id']){
            case '1':
                $this->update_coupon_log(129,$_POST['user_id']);
                break;
            case '4':
                $this->update_coupon_log(131,$_POST['user_id']);
                break;
            case '7':
                $this->update_coupon_log(127,$_POST['user_id']);
                break;
            case '3':
                $this->update_coupon_log(128,$_POST['user_id']);
                break;
            case '6':
                break;
            case '8':
                $this->update_coupon_log(126,$_POST['user_id']);
                break;
            case '2':
                break;
            case '5':
                $this->update_coupon_log(130,$_POST['user_id']);
                break;
        }
        die(json_encode($data));
    }

    private function getRand($proArr){
        $data = '';
        $proSum = array_sum($proArr); //概率数组的总概率精度
        foreach ($proArr as $k => $v) { //概率数组循环
            $randNum = mt_rand(1, $proSum);
            if ($randNum <= $v) {
                $data = $k;
                break;
            } else {
                $proSum -= $v;
            }
        }
        unset($proArr);
        return $data;
    }

    public function update_coupon_log($coupon_id,$user_id){
        $coupon = $this->DAO->get_coupon_last_log($coupon_id);
        if(!empty($coupon)){
            $coupon_type = $this->DAO->get_type_coupon($coupon_id);
            if($coupon_type['valid_days']>0){
                $endtime = time() + ($coupon_type['valid_days']*86400);
            }
            $this->DAO->update_coupon_log_date($coupon['id'],$endtime,$user_id);
        }else{
            $data = array('msg' => 0, 'desc' => '该奖品已领完.');
            die(json_encode($data));
        }
    }

    public function ajax_share_activity(){
        $params = $_POST;
        sleep(5);
        $this->DAO->update_share_isuse($params['user_id']);
        $data = array('msg' => 1, 'desc' => '分享成功！');
        die(json_encode($data));
    }

}