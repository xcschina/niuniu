<?php
COMMON('baseCore', 'pageCore');
DAO('index_dao','common_dao');

class index_web extends baseCore {

    public $DAO;
    public $COMDAO;
    public $game_type;
    public $game_status;

    public function __construct() {
        parent::__construct();
        $this->DAO = new index_dao();
        $this->COMDAO=new common_dao();
        if(!empty($_GET['promoter_id'])){
            $promoter_code = $this->COMDAO->get_promoter_code($_GET['promoter_id']);
            if($promoter_code){
                $_SESSION['promoter_id'] = $_GET['promoter_id'];
            }
        }
        if($this->is_mobile_client()){
            header("Location:http://m.66173.cn");
        }
        $notices=$this->COMDAO->get_mod_articles(14);
        $links=$this->COMDAO->get_friendly_links();
        $service_qq = $this->COMDAO->get_service_qq();
        $games = $this->COMDAO->get_hot_games();
        $rank_type = $this->game_type = array(
            1 => '动作',
            2 => '角色',
            3 => '射击',
            4 => '策略',
            5 => '即时',
            6 => '回合',
            7 => '休闲',
            8 => '冒险',
            9 => '模拟',
            10 => '竞技',
            11 => '卡牌',
            12 => '体育',
            13 => '格斗',
            14 => 'MOBA'
        );
        $rank_status = $this->game_status = array(
            1 => '公测',
            2 => '不删档内测',
            3 => '不删档测试',
            4 => '不删档封测',
            5 => '不删档测试',
            6 => '删档封测',
        );
        $this->assign("rank_type",$rank_type);
        $this->assign("rank_status",$rank_status);
        $this->assign("games",$games);
        $this->assign("serviceqq", $service_qq);
        $this->assign("notices", $notices);
        $this->assign("links", $links);
    }

    public function index_web() {

        $_SESSION['login_back_url'] = '';
        $result = $this->get_http_host();
        $domain_name = $result['domain_name'];
        $games = $this->DAO->get_hot_games();
        $cate_hot_games = $this->DAO->get_cate_hot_games();
        $banners = $this->DAO->get_banners();
        $acts = $this->DAO->get_mod_articles(8);
        $acts = array_slice($acts, 0, 4);
        $news = $this->DAO->get_mod_articles(16);
        $news_left = array_slice($news, 0, 5);
        $news_right= array_slice($news, 5, 10);
        $newgame = $this->DAO->get_mod_articles(13);
        $quetion = $this->DAO->get_mod_articles(6);
        $special = $this->DAO->get_special_sells();
        // $advert = $this->DAO->get_article_by_id(64);// 暂时不用
        $regpic = $this->DAO->get_article_by_id(6382);
        $reg_pic = $this->DAO->get_article_by_id(2556);
        $trades = $this->DAO->get_mix_trades();
        $publish = $this->DAO->get_mod_articles(18);
        $publish_left = array_slice($publish, 0, 5);
        $publish_right = array_slice($publish, 5, 10);
        $hot_rank = $this->DAO->get_hot_rank();
        $new_game_rank = $this->DAO->get_new_game_rank();
        foreach ($new_game_rank as $key => $val) {
            $new_game_rank[$key]['android_down'] = $this->DAO->get_game_download_count($val['game_id'],1);
            $new_game_rank[$key]['ios_down'] = $this->DAO->get_game_download_count($val['game_id'],2);
        }
        $this->assign("hot_rank", $hot_rank);
        $this->assign("new_game_rank", $new_game_rank);
        $this->assign("games", $games);
        $this->assign("cate_hot_games", $cate_hot_games);
        $this->assign("banners", $banners);
        $this->assign("acts", $acts);
        $this->assign("news", $news_left);
        $this->assign("news_right", $news_right);
        $this->assign("newgame", $newgame);
        $this->assign("quetion", $quetion);
        $this->assign("special", $special);
        // $this->assign("advert", $advert);
        $this->assign("regpic", $regpic);
        $this->assign("reg_pic", $reg_pic);
        $this->assign("trades", $trades);
        $this->assign("publish", $publish_left);
        $this->assign("domain_name", $domain_name);
        $this->assign("publish_right", $publish_right);
        $this->assign("games_json", json_encode($games));
        $this->display('index_v2.html');
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
        $result = $this->get_http_host();
        $domain_name = $result['domain_name'];
        $name = $result['name'];
        // old
        // $this->assign("games", $games);
        // $this->assign("games_json", json_encode($games));
        // $this->display("game_list.html");

        // v2
        $this->assign("letter_games", $group_game_list);
        $this->assign("all_games",$games);
        $games = array_slice($games,0,5);
        $this->assign("hot_games",$games);
        $regpic = $this->DAO->get_article_by_id(6382);
        $this->assign("regpic",$regpic);
        if(is_null($type)){
            $this->assign("web_des",$name."提供热门手机游戏排行榜、手游账号交易、手机游戏首充号购买、手机游戏充值，苹果IOS代充、手游退游、装备道具交易、游戏币交易等手游相关交易服务。(".$_SERVER['HTTP_HOST'].")是国内权威的游戏交易平台，安全快捷有保障的手游充值中心。");
            $this->assign('domain_name',$domain_name);
            $this->display("game_list.html");
        }elseif($type == 'download'){
            $this->assign("web_des",$name."手游下载列表提供热门手游免费下载渠道，包括手游安卓平台下载渠道、IOS平台下载渠道，(".$_SERVER['HTTP_HOST'].")是国内权威的手机游戏交易平台，安全快捷有保障的手游下载中心。");
            return true;
        }
    }

    // 游戏下载总列表 <zbc>
    public function games_download_list(){
        $result = $this->get_http_host();
        $domain_name = $result['domain_name'];
        $this->assign('domain_name',$domain_name);
        $this->game_list('download');
        $this->display("games_download_list.html");
    }
    
    // 注册引导页 <zbc>
    public function get_reg_guid(){
        $result = $this->get_http_host();
        $domain_name = $result['domain_name'];
        $this->assign('domain_name',$domain_name);
        $this->display('reg_guide.html');
    }

    // 首充号验证-首页 <zbc>
    public function check_game_user($game_user){
        $res = $this->DAO->check_game_user($game_user);
        if($res['game_id']){
            // 首充号续充页面
            header('Location:http://www.66173.cn/game'.$res['game_id'].'/recharge');
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

        $res = $this->DAO->check_game_user($game_user);
        if($res['id']){
            $bak['ret'] = 'right';
            $bak['url'] = 'http://www.66173.cn/game'.$res['game_id'].'/recharge';
        }else{
            $bak['ret'] = 'wrong';
        }
        die(json_encode($bak));

    }

    public function gift_view(){
        $result = $this->get_http_host();
        $domain_name = $result['domain_name'];
        $this->assign('domain_name',$domain_name);
        $this->display("gift-bag.html");
    }

    public function coupon_view(){
        $this->display("coupon_view.html");
    }

    public function get_coupon(){
        $id = $_POST['id'];
        if(!$id || empty($_SESSION['user_id'])){
            $msg['res']="0";//1成功0失败
            $msg['msg']="参数丢失,请登录后重新领取";
            die(json_encode($msg));
        }
        $user_id = $_SESSION['user_id'];
        $user_coupon_log = $this->DAO->get_user_coupon_log($user_id, $id);
        if(!$user_coupon_log){
            $msg['res']="0";//1成功0失败
            $msg['msg']="没有您要领取的优惠券";
            die(json_encode($msg));
        }
        if(!empty($user_coupon_log['receive_time'])){
            $msg['res']="0";//1成功0失败
            $msg['msg']="你已领取过该优惠券,无需重复领取.";
            die(json_encode($msg));
        }
        if($user_coupon_log['valid_type']==1){
            $end_time = $this->get_end_time($user_coupon_log['valid_days']);
            $this->DAO->update_coupon_time(time(),$end_time,$id);
        }elseif($user_coupon_log['valid_type']==2){
            $this->DAO->update_coupon_time($user_coupon_log['start_time'],$user_coupon_log['end_time'],$id);
        }
        $msg['res']="1";//1成功0失败
        $msg['msg']="领取成功";
        die(json_encode($msg));
    }

    private function get_end_time($days=0){
        if(!$days){
            return time();
        }
        $time = $days * 86400;
        $end_time = time() + $time;
        return $end_time;
    }

    public function game_down_url(){
        $url = "http://apk.66173.cn/1020/shol_66173.apk";
        if($this->is_weixin()){
            $this->display("guidance_down.html");
        }else{
            header("Location:".$url);
        }
    }

    public function is_weixin(){
        if (strpos($_SERVER['HTTP_USER_AGENT'],
                'MicroMessenger') !== false ) {
            return true;
        }
        return false;
    }


}