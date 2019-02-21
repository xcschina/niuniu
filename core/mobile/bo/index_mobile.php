<?php
COMMON('baseCore', 'pageCore');
DAO('index_dao');

class index_mobile extends baseCore{

    public $DAO;
    public $id;
    public $game_type;

    public function __construct(){
        parent::__construct();
        $this->DAO = new index_dao();
        $this->game_type = array(
            101 => '动作',
            102 => '角色',
            103 => '射击',
            104 => '策略',
            105 => '即时',
            106 => '回合',
            107 => '休闲',
            108 => '冒险',
            109 => '模拟',
            110 => '竞技',
            111 => '卡牌',
            112 => '体育',
            113 => '格斗',
            114 => 'MOBA');

    }

    public function index_view(){
        $_SESSION['login_back_url'] = '/';
        $games = $this->DAO->get_hot_games();
        $banners = $this->DAO->get_banners();
        $hot_items = $this->DAO->get_hot_item();
        $notices = $this->DAO->get_mod_articles(15);
        $news = $this->DAO->get_mod_articles(16);
        $special = $this->DAO->get_special_sells();
        $regpic = $this->DAO->get_article_by_id(6406);

        $this->assign("ip", $this->client_ip());
        $this->assign("regpic", $regpic);
        $this->assign("games", $games);
        $this->assign("banners", $banners);
        $this->assign("hot_items", $hot_items);
        $this->assign("notices", $notices);
        $this->assign("news", $news);
        $this->assign("special", $special);
        $this->assign("games_json", json_encode($games));
        $this->display('index.html');
    }

    public function main_search(){
        $this->display('m_main_search.html');
    }

    public function password(){
//        $check=$this->request('https://ssl.ptlogin2.qq.com/check?regmaster=&pt_tea=2&pt_vcode=1&uin=3555055471&appid=710023101&js_ver=10231&js_type=1&login_sig=a9CO3rhY38fEcDroxCInQ*EvG6WdoqL*7YY9ql6JoHIcr2sM0wMP919xH6KWIvzL&u1=http%3A%2F%2Fop.open.qq.com%2Fgame_channel%2Flist&r=0.5841719949908268&pt_uistyle=40');
//        preg_match_all("/(?:\()(.*)(?:\))/i",$check, $result);
//        $list = explode("','",trim($result[1][0],"'"));
        $this->assign('password',$_GET['pwd']);
        $this->assign('salt',$_GET['salt']);
        $this->assign('verifyCode',$_GET['$verifycode']);
        $this->display('login.html');
    }

    public function ajax_list(){
        $url = 'https://ssl.ptlogin2.qq.com/login?u=3555055471&verifycode='.$_GET['code'].'&pt_vcode_v1=0&pt_verifysession_v1='.$_GET['pt'].'&p='.$_GET['p'].'&pt_randsalt=2&pt_jstoken=2761309594&u1=http%3A%2F%2Fop.open.qq.com%2Fgame_channel%2Flist&ptredirect=1&h=1&t=1&g=1&from_ui=1'.
            '&ptlang=2052&action=2-11-1509341981721&js_ver=10231&js_type=1&login_sig=a9CO3rhY38fEcDroxCInQ*EvG6WdoqL*7YY9ql6JoHIcr2sM0wMP919xH6KWIvzL&pt_uistyle=40&aid=710023101&&pt_guid_sig=6283F9AB308681D2D59954F4F34DD1C08FF6453E41022C682B6CB3A3FAA33A6583ADB23C3F086122';
        $data = $this->request($url);
        var_dump($data);
    }

    public function games_view(){
        $_SESSION['login_back_url'] = 'games.php';
        $hot_games = $this->DAO->get_hot_games();

        $this->assign("games", $hot_games);
        $this->assign("title","游戏列表页");
        $this->display('games.html');
    }

    public function letter_games($letter, $type){
        if($type==8){
            $games = $this->DAO->get_iap_letter_games($letter);
        }else{
            $games = $this->DAO->get_letter_games($letter);
        }
        die(json_encode($games));
    }

    public function get_now_gamelist($letter){
        $games = $this->DAO->get_letter_games($letter);
        $notices = $this->DAO->get_mod_articles(15);
        $regpic = $this->DAO->get_article_by_id(6406);
        $this->assign("notices", $notices);
        $this->assign("regpic",$regpic);
        $this->assign("games",$games);
        $this->display("all_game_list.html");
    }

    private function get_tag_name($tag_ids){
        $tags_array = explode(',', $tag_ids);
        $new_tags = array();
        foreach ($tags_array as $key=>$val){
            if(!empty($val)){
                array_push($new_tags,$this->game_type[$val]);
            }
        }
        return $new_tags;
    }

    private function get_platform($platform){
        $arr = "";
        foreach ($platform as $key =>$data){
            $arr .=$data['id'].",";
        }
        $str = trim($arr,',');
        return $str;
    }

    public function up_char_rate(){
        $char_list = $this->DAO->get_up_char_list();
        foreach($char_list as $key => $game){
            $game_chs = $this->DAO->get_game_channels($game['id']);
            unset($game_chs['game_id']);
            //最低折扣
            $min_dis = 10;
            foreach($game_chs as $k=>$v){
                if(!empty($v)){
                    $ch_id = explode("_",$k);
                    if($ch_id[2]=='1' && $min_dis > $v){
                        $min_dis = $v;
                    }
                }
            }
            $this->DAO->up_game_char_rate($game['id'],$min_dis);
        }
        print("更新完成".date("Y/m/d h:i:sa",time()));
    }

    public function up_refill_rate(){
        $char_list = $this->DAO->get_up_refill_list();
        foreach($char_list as $key => $game){
            $game_chs = $this->DAO->get_game_channels($game['id']);
            unset($game_chs['game_id']);
            //最低折扣
            $min_dis = 10;
            foreach($game_chs as $k=>$v){
                if(!empty($v)){
                    $ch_id = explode("_",$k);
                    if($ch_id[2]=='2' && $min_dis > $v){
                        $min_dis = $v;
                    }
                }
            }
            $this->DAO->up_game_refill_rate($game['id'],$min_dis);
        }
        print("更新完成".date("Y/m/d h:i:sa",time()));
    }

    public function get_ch_status($params){
        $android = $this->DAO->get_channels_id(1);
        $iOS = $this->DAO->get_channels_id(2);
        foreach($params as $key=>$data){
            $android_status = $this->DAO->get_platform_id($data['id'],$this->get_platform($android));
            $ios_status = $this->DAO->get_platform_id($data['id'],$this->get_platform($iOS));
            $params[$key]['android'] = "";
            $params[$key]['iOS'] = "";
            if($android_status){
                $params[$key]['android'] = 1;
            }
            if($ios_status){
                $params[$key]['iOS'] = 1;
            }
        }
        return $params;
    }

    public function character_view(){
        $_SESSION['login_back_url'] = 'character.php';
        //大家都在买
        $special_sells = $this->DAO->get_all_buy();
        $special_sells = $this->get_ch_status($special_sells);
        //最新上架
        $new_game_list = $this->DAO->get_new_game_list();
        $new_game_list = $this->get_ch_status($new_game_list);
        //全站特价
        $total_special = $this->DAO->get_special_list();
        $total_special = $this->get_ch_status($total_special);

        $this->assign("newest",$new_game_list);
        $this->assign("special_sells",$special_sells);
        $this->assign("total_special",$total_special);
        $this->display('character.html');
    }

    public function get_games($platform,$letter){
        $channels_id = $this->DAO->get_channels_id($platform);
        $arr ="";
        foreach ($channels_id as $key =>$data){
            $arr .= $data['id'].",";
        }
        $str=trim($arr,',');
        $games = $this->DAO->get_games(1,$str,$letter);
        if($games){
            $return = array("code" => "1","games" => $games);
        }else{
            $return = array("code" => "0","msg" => "未查询到游戏信息");
        }
        die(json_encode($return));
    }

    public function recharge_view(){
        $_SESSION['login_back_url'] = 'recharge.php';
        //$games = $this->DAO->get_character_games();
        $hot_games = $this->DAO->get_cate_hot_games(2);
        $notices = $this->DAO->get_mod_articles(15);
        $regpic = $this->DAO->get_article_by_id(6406);
        $this->assign("regpic", $regpic);
        $this->assign("games", $hot_games);
        $this->assign("notices", $notices);
        $this->display('recharge.html');
    }

    public function topup_view(){
        $_SESSION['login_back_url'] = 'topup.php';
        //$games = $this->DAO->get_character_games();
        $hot_games = $this->DAO->get_cate_hot_games(3);
        $notices = $this->DAO->get_mod_articles(15);
        $regpic = $this->DAO->get_article_by_id(6406);
        $this->assign("regpic", $regpic);
        $this->assign("games", $hot_games);
        $this->assign("notices", $notices);
        $this->display('topup.html');
    }

    public function game_account_view(){
        $_SESSION['login_back_url'] = 'game_account.php';
        //$games = $this->DAO->get_character_games();
        $hot_games = $this->DAO->get_cate_hot_games(4);
        $notices = $this->DAO->get_mod_articles(15);
        $regpic = $this->DAO->get_article_by_id(6406);
        $this->assign("regpic", $regpic);
        $this->assign("games", $hot_games);
        $this->assign("notices", $notices);
        $this->display('game_account.html');
    }

    public function coin_view(){
        $_SESSION['login_back_url'] = 'coin.php';
        //$games = $this->DAO->get_character_games();
        $hot_games = $this->DAO->get_cate_hot_games(5);
        $notices = $this->DAO->get_mod_articles(15);
        $regpic = $this->DAO->get_article_by_id(6406);
        $this->assign("regpic", $regpic);
        $this->assign("games", $hot_games);
        $this->assign("notices", $notices);
        $this->display('coin.html');
    }

    public function appstore_view(){
        $_SESSION['login_back_url'] = 'appstore.php';
        //$games = $this->DAO->get_character_games();
        $hot_games = $this->DAO->get_cate_hot_games(8);
        $notices = $this->DAO->get_mod_articles(15);
        $regpic = $this->DAO->get_article_by_id(6406);
        $this->assign("regpic", $regpic);
        $this->assign("games", $hot_games);
        $this->assign("notices", $notices);
        $this->display('appstore.html');
    }

    public function sell_view(){
        $this->open_debug();
        $_SESSION['login_back_url'] = 'sells.php';
        $hot_games = $this->DAO->get_cate_hot_games(8888);
        $notices = $this->DAO->get_mod_articles(15);
        $this->assign("games", $hot_games);
        $this->assign("notices", $notices);
        $this->display('sells.html');
    }

    public function get_keyword_game(){
        $params = $_GET;
        if(!$params['page']){
            $params['page'] = $this->page;
        }
        if($params['keyword']){
            $games = $this->DAO->search_game($params['keyword'],$params['page']);
            $num = $this->DAO->search_game_num($params['keyword']);
            $games_list = $this->get_ch_status($games);
            $result = array('num' => $num['num'] , 'games' => $games_list);
        }else{
            $games_list = $this->DAO->get_hot_games();
            $result = array('games' => $games_list);
        }
        die(json_encode($result));
    }

    public function get_reg_guid(){
        $this->display('reg_guide.html');
    }

    public function coupon_view(){
        $params = $_GET;
        if($this->user_id){
            $this->redirect("account.php?act=login");
            exit;
        }
        $all_coupon = $this->DAO->get_my_coupon($_SESSION['user_id'],$this->page,$params['coupon_status']);
        $page = $this->pageshow($this->page, "coupon.php?act=coupon_list&coupon_status=".$params['coupon_status']."&");
        foreach($all_coupon as $k=>$v){
            if($v['end_time'] > time() && empty($v['use_time'])){
                $all_coupon[$k]['gift_state']=1;
            }else if($v['end_time'] < time() && empty($v['use_time'])){
                $all_coupon[$k]['gift_state']=2;
            }else if(!empty($v['use_time'])){
                $all_coupon[$k]['gift_state']=3;
            }
        }
        $this->assign("status",$params['coupon_status']);
        $this->assign("data_list",$all_coupon);
        $this->assign("pageBar", $page->show());
        $this->display("member/coupon.html");
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

    public function hot_game_list(){
        $games = $this->DAO->get_hot_game();
        $notices = $this->DAO->get_mod_articles(15);
        $regpic = $this->DAO->get_article_by_id(6406);
        $this->assign("notices", $notices);
        $this->assign("regpic",$regpic);
        $this->assign("games",$games);
        $this->display("hot_game_list.html");
    }
}