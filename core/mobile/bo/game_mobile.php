<?php
COMMON('baseCore', 'paramUtils', 'pageCore');
DAO('game_dao','product_dao');

class game_mobile extends baseCore {

    public $DAO;
    public $P_DAO;
    public $id;
    protected $url;
    protected $base_url;
    protected $param;

    public function __construct() {
        parent::__construct();
        $this->DAO = new game_dao();
        $this->P_DAO = new product_dao();
        $this->set_param();
        $this->user_id=$_SESSION['user_id'];
        if(!$_SESSION['user_id']){
            $this->assign("is_login",'nologin');
        }
    }

    public function game_list(){
        $params=$_GET;
        if(!$params['first_letter'] && !$params['is_hot']){
            $params['is_hot']=1;
        }
        $game_list=$this->DAO->get_game_list($params);
        $this->assign("game_list", $game_list);
        $this->assign("params", $params);
        $this->display("game_list.html");
    }

    public function game_view($id) {
        $_SESSION['login_back_url'] = 'game'.$id;
        $game = $this->DAO->get_game($id);
        $downs = $this->DAO->get_game_downs($id);
//        $news = $this->DAO->get_mod_articles(16);
        $news = $this->DAO->get_articles_list($id);
        $gifts = $this->DAO->get_game_gifts($id);
        if($_SESSION['usr_info']['user_id']){
            $my_gifts = $this->DAO->get_user_gift_batch($_SESSION['usr_info']['user_id']);
            foreach($gifts as $k=>$gift){
                foreach($my_gifts as $kk=>$m){
                    if($m['batch_id']==$gift['id']){
                        $gifts[$k]['is_get'] = 1;
                    }
                }
            }
        }

        $this->assign("game", $game);
        $this->assign("downs", $downs);
        $this->assign("news", $news);
        $this->assign("gifts", $gifts);
        $this->display("game.html");
    }

    public function game_cate_view($id){
        $_SESSION['login_back_url'] = 'game'.$id;
        $game = $this->DAO->get_game($id);
        $page=$_POST['page'];
        if(!$_POST['page']){
            $page=$this->page;
        }
        $product_list = $this->DAO->get_products($id,$page, $this->param);
        foreach($product_list as $k=>$v){
            $discount = $this->DAO->get_product_discount($v['id']);
            $v["discount"] = 10;
            //独立折扣核算到渠道折扣
            foreach($discount as $kk=>$vv){
                if($kk!='product_id'){
                    $split = explode("_",$kk);
                    if($split[0]=='ch' && $vv>0){
                        $discount['chd_'.$split[1]] = $vv;
                    }
                }
            }

            foreach($discount as $kk=>$vv){
                $split = explode("_",$kk);
                if($kk!='product_id' && $vv>0 && $vv<$v["discount"] && $split[0]=='chd'){
                    $v["discount"] = $vv;
                }
            }
            $product_list[$k]['discount'] = $v['discount'];
            $price = round($v['discount']*$v['price']/10);
            if($price<1)$price=1;
            $product_list[$k]['dprice'] = $price;
        }
        if($page=='1'){
            $this->assign("game", $game);
            $this->assign("products", $product_list);
            $this->assign("param", $this->param);
            $this->assign("page", $this->page);
            $this->display("game.html");
        }else{
            echo json_encode($product_list);
        }
    }

    public function product_info($product_id){
        if(time() >= 1483199100 && time() <= 1483200900) {
            die("2016.12.31 23:45-2017.1.1 00:15充值系统升级，暂停充值。对此带来的不便深感抱歉。");
        }
        //$this->open_debug();
        $product = $this->P_DAO->get_product_info($product_id);
        if(!$product){
            die("出错拉，找不到该商品");
        }
        if($product['type']==1){
            $this->character_buy($product['game_id'], $product);
        }elseif($product['type']==2){
            $this->recharge_buy($product['game_id'], $product);
        }elseif($product['type']==3){
            $this->topup_buy($product['game_id'], $product);
        }elseif($product['type']==8){
            $this->iap_buy($product['game_id'], $product);
        }else{
            $this->other_buy($product);
        }
    }

    public function character_buy($game_id, $product = array()){
        if(time() >= 1483199100 && time() <= 1483200900) {
            die("2016.12.31 23:45-2017.1.1 00:15充值系统升级，暂停充值。对此带来的不便深感抱歉。");
        }
        $game = $this->DAO->get_game($game_id);
        if(!$product){
            $product = $this->DAO->get_game_last_product($game_id, 1);
        }
        $_SESSION['login_back_url'] = 'shop'.$product['id'];
        $this->set_product_tag($game['tags']);
        if($product['sub_title']){
            $sub_title = explode("|", $product['sub_title']);
        }

        $channels = $this->DAO->get_channels();
        $this->set_discount($product, $channels, 1);
        $this->page_hash();

        $android_chs = $ios_chs = array();
        foreach($channels as $k => $ch){
            if($ch['platform']==1){
                $android_chs[] = $ch;
            }
            if($ch['platform']==2){
                $ios_chs[] = $ch;
            }
        }

        $this->assign("game", $game);
        $this->assign("info", $product);
        $this->assign("sub_title", $sub_title);
        $this->assign("android_chs", $android_chs);
        $this->assign("ios_chs", $ios_chs);
        $this->display("character_buy.html");
    }

    public function renew_view($game_id){
        $game = $this->DAO->get_game($game_id);
        $_SESSION['login_back_url'] = 'game'.$game_id.'/renew';

        $game_user = $this->DAO->get_game_user($game_id, $this->user_id);

        $this->assign("game", $game);
        $this->assign("game_users", $game_user);
        $this->display("renew_view.html");
    }

    public function recharge_buy($game_id, $product = array()){
        if(time() >= 1483199100 && time() <= 1483200900) {
            die("2016.12.31 23:45-2017.1.1 00:15充值系统升级，暂停充值。对此带来的不便深感抱歉。");
        }
        $game = $this->DAO->get_game($game_id);
        if(!$product){
            $product = $this->DAO->get_game_last_product($game_id, 2);
        }
        $_SESSION['login_back_url'] = 'shop'.$product['id'];
        if($product['sub_title']){
            $sub_title = explode("|", $product['sub_title']);
        }

        if(!isset($_GET['game_user'])){
            $this->redirect("/game".$game_id."/renew");
            exit;
        }

        $game_user_id = $_GET['game_user'];
        $game_user = $this->DAO->get_game_user_by_id($game_user_id);

        if(!$game_user){
            $this->redirect("/game".$game_id."/renew");
            exit;
        }

        $discount = $product['ch_'.$game_user['ch_id']];
        $g_discount = $product['chd_'.$game_user['ch_id']];
        $discount = ($discount!=$g_discount && $discount>0)?$discount:$g_discount;
        if($discount==0){
            $discount=10;
        }
        if($_SESSION['is_agent']>0){
            $user_agent_game = $this->DAO->get_user_agent_game($_SESSION['user_id'], $product['game_id']);
            if($user_agent_game['discount_2']>0){
                $discount = $user_agent_game['discount_2'];
            }
        }

        $dprice = round($discount*$product['price']/10);
        $this->page_hash();

        $this->assign("game", $game);
        $this->assign("info", $product);
        $this->assign("sub_title", $sub_title);
        $this->assign("dprice", $dprice);
        $this->assign("u", $game_user);
        $this->assign("game_user_id", $game_user_id);
        $this->display("recharge_buy.html");
        //续充其他账号
        //$_SESSION['other_game_user'] = '';
    }

    public function topup_buy($game_id, $product = array()){
        if(time() >= 1483199100 && time() <= 1483200900) {
            die("2016.12.31 23:45-2017.1.1 00:15充值系统升级，暂停充值。对此带来的不便深感抱歉。");
        }
        $game = $this->DAO->get_game($game_id);
        if(!$product){
            $product = $this->DAO->get_game_last_product($game_id, 3);
        }
        $_SESSION['login_back_url'] = 'shop'.$product['id'];
        if($product['sub_title']){
            $sub_title = explode("|", $product['sub_title']);
        }
        $channels = $this->DAO->get_channels();
        $this->set_discount($product, $channels, 3);
        $this->page_hash();
        $android_chs = $ios_chs = array();
        foreach($channels as $k => $ch){
            if($ch['platform']==1){
                $android_chs[] = $ch;
            }
            if($ch['platform']==2){
                $ios_chs[] = $ch;
            }
        }

        $this->assign("game", $game);
        $this->assign("info", $product);
        $this->assign("sub_title", $sub_title);
        $this->assign("android_chs", $android_chs);
        $this->assign("ios_chs", $ios_chs);
        $this->display("topup_buy.html");
    }

    //首充号验证
    public function game_check_user_view($game_id){
        $game = $this->DAO->get_game($game_id);
        $this->page_hash();
        $this->assign("game_id", $game_id);
        $this->assign("game", $game);
        $this->display("recharge_check_user.html");
    }
    
    //首冲号验证
    public function game_check_user($game_id, $game_user, $pagehash){
        $user = $this->DAO->check_game_user($game_id, $game_user);
        $game = $this->DAO->get_game($game_id);
        if($user){
            $_SESSION['recharge_other_user'] = $user;
        }

        $this->page_hash();
        $this->assign("game_id", $game_id);
        $this->assign("game", $game);
        if($user){
            $this->assign("result", $user);
        }else{
            $this->assign("noresult", 'yes');
        }
        $this->display("recharge_check_user.html");
    }

    //代币、账号、装备列表页
    public function other_buy_list($game_id, $act=''){
        if(time() >= 1483199100 && time() <= 1483200900) {
            die("2016.12.31 23:45-2017.1.1 00:15充值系统升级，暂停充值。对此带来的不便深感抱歉。");
        }
        $game = $this->DAO->get_game($game_id);
        $_SESSION['login_back_url'] = 'game'.$game_id."/".$act;
        $product_list = $this->DAO->get_other_products($game_id, $this->page, $this->param);

        $this->assign("game", $game);
        $this->assign("products", $product_list);
        $this->assign("params", $this->param);
        $this->assign("page", $this->page);

        if($this->page>1){
            die(json_encode($product_list));
        }

        if($act=='account'){
            $this->display("other_account.html");
        }elseif($act=='coin'){
            $this->display("other_coin.html");
        }
    }

    public function other_buy($product){
        $ch_info = $this->DAO->get_channel_info($product['channel_id']);
        $serv_info = $this->DAO->get_serv_info($product['serv_id']);
        $thumbs = $this->DAO->get_game_thumbs($product['game_id']);
        $seller = $this->DAO->get_user_info($product['user_id']);
        if($product['channel_id']>0 && $product['serv_id']==0){
            $servs = $this->DAO->get_game_ch_servs($product['game_id'], $product['channel_id']);
            $this->assign("servs", $servs);
        }

        $max_stock = 10;
        if($product['user_id']>1 && $product['stock']<10){
            $max_stock = $product['stock'];
        }

        $this->assign("ch_info", $ch_info);
        $this->assign("serv_info", $serv_info);
        $this->assign("thumbs", $thumbs);
        $this->assign("info", $product);
        $this->assign("seller", $seller);
        $this->assign("max_stock", $max_stock);
        $this->page_hash();
        $this->display("other_buy.html");
    }

    public function iap_buy($game_id, $product = array()){
        $_SESSION['login_back_url'] = 'game'.$game_id."/iap";
        $game = $this->DAO->get_game($game_id);

        if(!$game['iap_game_id']){
            die("暂未开放");
        }

        if(!$product){
            $product = $this->DAO->get_game_iap_last_products($game_id);
        }
        if(!$product){
            $this->assign("game", $game);
            $this->display("iap_no_product.html");
            exit;
        }

        $groups = $this->DAO->get_iap_game_groups($game_id);

        $_SESSION['login_back_url'] = 'shop'.$product['id'];
        if($product['sub_title']){
            $sub_title = explode("|", $product['sub_title']);
        }
        $this->page_hash();

        $this->assign("game", $game);
        $this->assign("info", $product);
        $this->assign("sub_title", $sub_title);
        $this->assign("groups", $groups);
        $this->display("iap_buy.html");
    }

    protected function set_product_tag($tags){
        $atts = array();
        if(!$tags){
            $this->assign("tags",array());
            return false;
        }
        $tags = explode("\n", $tags);
        if(!is_array($tags))return false;

        foreach($tags as $k=>$v){
            if($v){
                $tag = explode("：",$v);
                $atts[$tag[0]] = explode("|",$tag[1]);
            }
        }
        $this->assign("tags", $atts);
    }

    protected function set_discount($product, &$channels, $product_type=1){
        $agent_discount = 0;
        if($_SESSION['is_agent']>0){
            $user_agent_game = $this->DAO->get_user_agent_game($_SESSION['user_id'], $product['game_id']);
            if($user_agent_game['discount_'.$product_type]>0){
                $agent_discount = $user_agent_game['discount_'.$product_type];
            }
        }
        foreach($channels as $k=>$v){
            $discount = $product['ch_'.$v['id']];
            $g_discount = $product['chd_'.$v['id']];
            $discount = ($discount!=$g_discount && $discount>0)?$discount:$g_discount;

            if($discount==0){
                unset($channels[$k]);
                continue;
            }
            $channels[$k]['discount'] = $discount;
            //代理折扣
            if($agent_discount>0){
                $channels[$k]['discount'] = $agent_discount;
            }
        }
        usort($channels, function($a, $b) {
            $al = $a['discount'];
            $bl = $b['discount'];
            if ($al == $bl)
                return 0;
            return ($al < $bl) ? -1 : 1;
        });
    }

    protected function set_param(){
        $id = paramUtils::intByGET("id",false);
        $act = paramUtils::strByGET("act");
        $price = paramUtils::strByGET("price");
        $serv_id = paramUtils::intByGET("serv_id");
        $ch_id = paramUtils::intByGET("ch_id");
        $this->param = array(
            "id"=>$id, "act"=>$act, "price"=>$price, "ch_id"=>$ch_id, "serv_id"=>$serv_id
        );

        if($ch_id){
            $channel = $this->DAO->get_channel_info($ch_id);
            $this->assign("ch_name", $channel['channel_name']);
        }else{
            $this->assign("ch_name", '');
        }

        if($serv_id){
            $serv = $this->DAO->get_serv_info($serv_id);
            $this->assign("serv_name", $serv['serv_name']);
        }else{
            $this->assign("serv_name", '');
        }

        if($act=='character'||$act=='')$this->param['type']=1;
        if($act=='recharge')$this->param['type']=2;
        if($act=='topup')$this->param['type']=3;
        if($act=='account')$this->param['type']=4;
        if($act=='coin')$this->param['type']=5;
        if($act=='props')$this->param['type']=6;
        if($act=='gift')$this->param['type']=7;
    }
    
    public function game_articles($id){
        $game = $this->DAO->get_game($id);
        $news = $this->DAO->get_game_articles($id);
        $this->assign("info", $game);
        $this->assign("news", $news);
        $this->display("game_articles.html");
    }
}