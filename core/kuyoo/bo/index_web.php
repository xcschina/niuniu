<?php
COMMON('baseCore', 'pageCore');
BO('baseKuyoo');
DAO('index_dao','common_dao');
class index_web extends baseKuyoo {

    public $DAO;
    public $COMDAO;

    public function __construct() {
        parent::__construct();
        // if($this->is_mobile_client()){
        //     header("Location:http://m.66173.cn");
        // }
        $this->DAO = new index_dao();
        $this->COMDAO=new common_dao();
        $links=$this->COMDAO->get_friendly_links();
        $service_qq = $this->COMDAO->get_service_qq();
        $games = $this->COMDAO->get_hot_games();
        $this->assign("games",$games);
        $this->assign("serviceqq", $service_qq);
        $notices=$this->COMDAO->get_mod_articles(21);
        foreach ($notices as $key => $value) {
            $kuyonotices[$key] = str_replace('www.66173.cn', 'shouyou.kuyoo.com', $value);
        }
        $this->assign("notices", $kuyonotices);
        $this->assign("links", $links);
    }

    public function activity_web(){
        $active = $this->DAO->show_active_all();
        $mission_1 = $this->DAO->get_my_gift($_SESSION['user_id'],257);
        $mission = array();
        if(!empty($mission_1)){
            $mission['gift'] = 1;
        }
        $mission_2 = $this->DAO->get_coupon_mission($_SESSION['user_id']);
        $count = count($mission_2);
        if($count == 10){
            $mission['coupon'] = 1;
        }
        $mission_3 = $this->DAO->get_prize_all($_SESSION['user_id']);
        if(!empty($mission_3)){
            $mission['prize'] = 1;
        }
        $share = $this->DAO->get_share_log($_SESSION['user_id']);
        if(empty($share)){
            $share_num = 1;
        }else{
            $share_num = $share['award_num'];
        }
        $num = count($mission);
        $this->assign('share_num',$share_num);
        $this->assign("num",$num);
        $this->assign('desc',$mission);
        $this->assign("active",$active);
        $this->display('tl_view.html');
    }

    public function index_web() {
        $_SESSION['login_back_url'] = '';
        $games = $this->DAO->get_hot_games();
        $cate_hot_games = $this->DAO->get_cate_hot_games();
        $banners = $this->DAO->get_banners();
        $acts = $this->DAO->get_mod_articles(8);
        $acts = array_slice($acts, 0, 4);
        $news = $this->DAO->get_mod_articles(16);
        $news = array_slice($news, 0, 5);
        $newgame = $this->DAO->get_mod_articles(13);
        $quetion = $this->DAO->get_mod_articles(21);
        $special = $this->DAO->get_special_sells();
        $advert = $this->DAO->get_article_by_id(64);
        $trades = $this->DAO->get_mix_trades();
        $publish = $this->DAO->get_mod_articles(18);
        $publish = array_slice($publish, 0, 5);

        $this->assign("games", $games);
        $this->assign("cate_hot_games", $cate_hot_games);
        $this->assign("banners", $banners);
        $this->assign("acts", $acts);
        $this->assign("news", $news);
        $this->assign("newgame", $newgame);
        $this->assign("quetion", $quetion);
        $this->assign("special", $special);
        $this->assign("advert", $advert);
        $this->assign("trades", $trades);
        $this->assign("publish", $publish);
        $this->assign("games_json", json_encode($games));
        $this->display('index.html');
    }

    public function get_games($letter){
        $letters = array("A","B","C","D","E","F","G","H","I","J","K","L","M","N","O","P","Q","R","S","T","Y","V","W","X","Y","Z");
        if(!in_array($letter,$letters)){
            die("error");
        }
        $games = $this->DAO->get_letter_games($letter);
        die(json_encode($games));
    }

    public function get_keyword_game($keyword){
        if($keyword){
            $games = $this->DAO->search_game($keyword);
        }else{
            $games = $this->DAO->get_hot_games();
        }
        die(json_encode($games));
    }

    public function game_list($type = null){
        $games = $this->DAO->get_hot_games();
        $game_list=$this->DAO->get_all_games();
        $group_game_list = array();
        foreach($game_list as $v){
            $group_game_list[$v['first_letter']]['first_letter'] = $v['first_letter'];
            $group_game_list[$v['first_letter']]['list'][]=$v;
        }
        ksort($group_game_list);

        // old
        // $this->assign("games", $games);
        // $this->assign("games_json", json_encode($games));
        // $this->display("game_list.html");

        // v2
        $this->assign("letter_games", $group_game_list);
        $this->assign("all_games",$games);
        $games = array_slice($games,0,5);
        $this->assign("hot_games",$games);
        if(is_null($type)){
            $this->assign("web_des","酷游提供热门手机游戏排行榜、手游账号交易、手机游戏首充号购买、手机游戏充值，苹果IOS代充、手游退游、装备道具交易、游戏币交易等手游相关交易服务。(shouyou.kuyoo.com)是国内权威的游戏交易平台，安全快捷有保障的手游充值中心。");
            $this->display("game_list.html");
        }elseif($type == 'download'){
            $this->assign("web_des","酷游手游下载列表提供热门手游免费下载渠道，包括手游安卓平台下载渠道、IOS平台下载渠道，(shouyou.kuyoo.com)是国内权威的手机游戏交易平台，安全快捷有保障的手游下载中心。");
            return true;
        }
    }

    // 游戏下载总列表 <zbc>
    public function games_download_list(){
        $this->game_list('download');
        $this->display("games_download_list.html");
    }

    // 首充号验证-首页 <zbc>
    public function check_game_user($game_user){
        $res = $this->DAO->check_game_user($game_user);
        if($res['game_id']){
            // 首充号续充页面
            header('Location:http://shouyou.kuyoo.com/game'.$res['game_id'].'/recharge');
        }else{
            // 首充号验证专题页
            $this->display("index_check_firstpay.html"); die;
        }
    }

    // 首充号验证-专页 <zbc>
    public function do_check_game_user($game_user){

        // 简单频率校验
        if(!empty($_COOKIE['cfpt'])){
            $times = intval($_COOKIE['cfpt']);
            if($times>30){
                $bak['ret'] = 'often';
                die(json_encode($bak));
            }else{
                $times++;
            }
        }else{
            $times = 1;
        }
        setCookie('cfpt',$times,time()+60); // 1分钟30次

        // 开始查询
        $res = $this->DAO->check_game_user($game_user);
        if($res['id']){
            $bak['ret'] = 'right';
            $bak['url'] = 'http://shouyou.kuyoo.com/game'.$res['game_id'].'/recharge';
        }else{
            $bak['ret'] = 'wrong';
        }
        die(json_encode($bak));

    }

    public function gift_view(){
        $this->display("gift-bag.html");
    }

    public function ajax_get_coupon(){
        $params = $_POST;
        $data = array('msg' => 0, 'desc' => '网络丢了,请刷新后重新领取.');
        if(empty($params['user_id'])||!$params['btn_id']){
            die(json_encode($data));
        }
        if($params['btn_id'] == 1){
            $coupon_arr = array(64,65,66,67,68,69);
        }else if($params['btn_id'] == 2){
            $coupon_arr = array(70,71,72);
        }else{
            $coupon_arr = array(75);
        }
        //获取活动配置表
        $active_info = $this->DAO->get_active_info($params['user_id'],$params['btn_id']);
        $active_Total = $this->DAO->get_active_total($params['btn_id']);
        if($active_Total >=1000){
            $data['desc']='亲,礼包领完了.';
            die(json_encode($data));
        }
        if(!empty($active_info)){
            $data['desc']='亲,您已领取过了.';
            die(json_encode($data));
        }else{
            foreach($coupon_arr as $key=>$value){
                $coupon_info = $this->DAO->get_coupon_info($value,$params['user_id']);
                if(!$coupon_info){
                    $last_coupon = $this->DAO->get_coupon_last_log($value);
                    $valid_day = $this->DAO->get_type_coupon($value);
                    if($valid_day['valid_days'] > 0){
                        $endtime = time()+($valid_day['valid_days']*3600);
                        $this->DAO->update_coupon_log_date($last_coupon['id'],$endtime,$params['user_id']);
                    }else{
                        $this->DAO->update_coupon_log($last_coupon['id'],$params['user_id']);
                    }
                }
            }
            $this->DAO->insert_active_info($params['user_id'],$params['btn_id']);
            $data['msg']=1;
            $data['desc']='领取成功';
            die(json_encode($data));
        }
    }

    public function ajax_get_gift(){
        $params = $_POST;
        $data = array('msg' => 0, 'desc' => '网络丢了,请刷新后重新领取.');
        if(!$params['user_id']||!$params['gift_id']){
            die(json_encode($data));
        }
        $my_gift = $this->DAO->get_my_gift($params['user_id'],$params['gift_id']);
        if(!empty($my_gift)){
            $data['desc']='您已经领取过了';
            die(json_encode($data));
        }else{
            $last_gift = $this->DAO->query_last_gift($params['gift_id']);
            if(!$last_gift){
                $data['desc']='礼包领完了.';
                die(json_encode($data));
            }
            $this->DAO->update_game_gifts($last_gift['id'],$params['user_id']);
            $data['msg']=1;
            $data['desc']='领取成功';
            die(json_encode($data));
        }
    }

    public function get_my_gift(){
        $params = $_POST;
        $data = array('msg' => 0, 'desc' => '网络丢了,请刷新后重新领取.');
        if(!$params['user_id']||!$params['gift_id']){
            die(json_encode($data));
        }
        $my_gift = $this->DAO->get_my_gift($params['user_id'],$params['gift_id']);
        $effi_time =$this->DAO->get_my_effi_time($params['gift_id']);
        if(!$my_gift){
            $data['desc']='您还没领取过礼包.';
            die(json_encode($data));
        }else{
            $data['msg']=1;
            $data['desc']='礼包码:'.$my_gift['code'];
            $data['start'] = date("Y-m-d",$effi_time['start_time']);
            $data['end'] = date("Y-m-d",$effi_time['end_time']);
            die(json_encode($data));
        }
    }

    public function activity_ajax(){
        if(!$_POST['user_id']){
            $data = array('msg' => 0, 'desc' => '网络丢了,请刷新后重新领取.');
            die(json_encode($data));
        }

        $share = $this->DAO->get_share_log($_POST['user_id']);
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
                $new_num = $share['award_num'] - 1;
                $this->DAO->update_share_log($_POST['user_id'],$new_num);
                $data['num'] = $share['award_num'];
            }
        }

        //添加iphone7和mi5判断
        //prize表示奖项内容，v表示中奖几率(若数组中七个奖项的v的总和为100，如果v的值为1，则代表中奖几率为1%，依此类推)
        $prize_arr = array(
            '0' => array('id' => 1, 'prize' => '3元现金券', 'v' => 0 ,'coupon_id' => 75,'type' => 1),
            '1' => array('id' => 2, 'prize' => 'Iphone7',  'v' => 0),
            '2' => array('id' => 3, 'prize' => '普发礼包',  'v' => 0, 'gift_id'=> 240 ,'type' => 2),
            '3' => array('id' => 4, 'prize' => '10元现金券','v' => 50, 'coupon_id' => 72,'type' => 1),
            '4' => array('id' => 5, 'prize' => '小米5',    'v' => 50),
            '5' => array('id' => 6, 'prize' => 'v12礼包',  'v' => 0, 'gift_id' => 240,'type' => 2),
            '6' => array('id' => 7, 'prize' => '20元现金卷','v' => 0, 'coupon_id' => 70,'type' => 1),
            '7' => array('id' => 8, 'prize' => '顶级礼包',  'v' => 0,'gift_id' => 240,'type' => 2)
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
        $desc = $all[$prize_id - 1];//中奖项
        $data['msg'] = 1;
        $data['prize_name'] = $desc['prize'];
        $data['prize_site'] = $prize_site;//前端奖项从-1开始
        $data['prize_id'] = $prize_id;
        $data['type'] = $desc['type'];
        $data['gift_id'] = $desc['gift_id'];
        $data['coupon_id'] = $desc['coupon_id'];
        switch($data['prize_id']){
            case '1':
                $this->update_coupon_log(74,$_POST['user_id']);
                break;
            case '4':
                $this->update_coupon_log(74,$_POST['user_id']);
                break;
            case '7':
                $this->update_coupon_log(74,$_POST['user_id']);
                break;
            case '3':
                $gift_code = $this->update_game_gifts(257,$_POST['user_id']);
                $data['gift_code'] = $gift_code['code'];
                $data['code_id']   = $gift_code['id'];
                break;
            case '6':
                $gift_code = $this->update_game_gifts(257,$_POST['user_id']);
                $data['gift_code'] = $gift_code['code'];
                $data['code_id']   = $gift_code['id'];
                break;
            case '8':
                $gift_code = $this->update_game_gifts(257,$_POST['user_id']);
                $data['gift_code'] = $gift_code['code'];
                $data['code_id']   = $gift_code['id'];
                break;
            case '2':
            case '5'://手机
                break;
        }
        $this->DAO->add_prize_log($_POST['user_id'],$data);
        die(json_encode($data));
    }

    public function update_coupon_log($coupon_id,$user_id){
        $coupon = $this->DAO->get_coupon_last_log($coupon_id);
        if(!empty($coupon)){
            $coupon_type = $this->DAO->get_type_coupon($coupon_id);
            if($coupon_type['valid_days']>0){
                $endtime = time() + ($coupon_type['valid_days']*3600);
                $this->DAO->update_coupon_log_date($coupon['id'],$endtime,$user_id);
            }
            $this->DAO->update_coupon_log($coupon['id'],$user_id);
        }else{
            $data = array('msg' => 0, 'desc' => '该奖品已领完.');
            die(json_encode($data));
        }
    }

    public function update_game_gifts($batch_id,$user_id){

        $last_gift = $this->DAO->query_last_gift($batch_id);

        if(!$last_gift){
            $data = array('msg' => 0, 'desc' => '该奖品已领完.');
            die(json_encode($data));
        }else{
            $this->DAO->update_game_gifts($last_gift['id'],$user_id);
        }
        return $last_gift;
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

    public function show_my_coupon(){
        $params = $_POST;
        $coupon = $this->DAO->get_coupon_all($params['user_id']);
        if(empty($coupon)){
            $data = array('msg' => 0, 'desc' => '目前没有优惠卷！');
            die(json_encode($data));
        }else{
            foreach($coupon as $k=>$v){
                if($v['active_id'] == 1){
                    $all_1 = $this->DAO->get_coupon_1();
                    foreach($all_1 as $key=>$value){
                        $all_co[] = $value['name'];
                    }
                }elseif($v['active_id'] == 2){
                    $all_2 = $this->DAO->get_coupon_2();
                    foreach($all_2 as $key=>$value){
                        $all_co[] = $value['name'];
                    }
                }else{
                    $all_3 = $this->DAO->get_coupon_3();
                    $all_co[] = $all_3['name'];
                }
            }
            die(json_encode($all_co));
        }
    }

    public function show_my_prize(){
        $params = $_POST;
        $prize = $this->DAO->get_prize_all($params['user_id']);
        if(empty($prize)){
            $data = array('msg' => 0, 'desc' => '目前没有奖品！');
            die(json_encode($data));
        }else{
            foreach($prize as $k => $v){
                    $data[$k]['add_time'] = date("Y-m-d H:i:s",$v['add_time']);
                    $code['code'] = $this->DAO->get_code_gift($v['gift_id']);
                    $data[$k]['code'] = $code['code']['code'];
                    $data[$k]['prize_name'] = $v['prize_name'];
            }
            die(json_encode($data));
        }
    }

    public function check_num(){
        $params = $_POST;
        $share = $this->DAO->get_share_log($params['user_id']);
        if(empty($share)){
            $this->DAO->insert_share_log($params['user_id']);
            die();
        }else{
            if($share['award_num'] == 0){
                $data['msg'] = "0";
                $data['desc'] = "抽奖机会已用完！！";
                die(json_encode($data));
            }
        }
    }
}

