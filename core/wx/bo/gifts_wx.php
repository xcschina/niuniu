<?php
COMMON('weixinCore', 'pageCore');
BO("account_wx");
DAO('gifts_dao','weixin_dao');
class gifts_wx extends weixinCore{

    protected $DAO;

    public function __construct(){
        parent::__construct();
        $this->DAO = new gifts_dao();
        if(strpos($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger') !== false ) {
            if(isset($_GET['code'])){
                $_SESSION['weixin_code'] = $_GET['code'];
                if(!$_SESSION['wx_openid']){
                    $this->get_auth_usr_token();
                }
                if(!$_SESSION['usr_info']['user_id']){
                    $account = new account_wx();
                    $_SESSION['usr_info'] = $account->DAO->get_wx_usr_info($_SESSION['wx_openid']);
                    $account->build_wx_user_info();
                }
            }
            $this->assign("is_weixin","1");
        }
    }

    public function check_login(){
        if(!$_SESSION['usr_info']){
            $this->redirect("my.php");
            exit;
        }
    }

    public function do_login($id){
        $_SESSION['login_return'] = '/gift'.$id;
        $this->redirect("my.php");
    }

    public function list_view(){
        $gifts = $this->DAO->get_gifts();
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
        $this->assign("gifts", $gifts);
        $this->display("gifts.html");
    }

    public function item_view($id){
        $_SESSION['login_return'] = '/gift'.$id;
        $info = $this->DAO->get_gift_info($id);
        $gifts = $this->DAO->get_game_gifts($info['game_id']);
        $game_info = $this->DAO->get_game_info($info['game_id']);
        $downs = $this->DAO->get_game_downs($info['game_id']);
        $is_get = 0;
        if($_SESSION['usr_info']['user_id']){
            $_SESSION['login_return'] = '';
            $wx_dao = new weixin_dao();
            $usr_gifts = $wx_dao->get_user_gifts($_SESSION['usr_info']['user_id']);
            foreach($usr_gifts as $gift){
                if($gift['batch_id']==$info['id']){
                    $is_get = 1;
                }
            }
        }

        foreach($gifts as $k=>$v){
            if($v['id']==$id){
                unset($gifts[$k]);
            }
        }
        $this->page_hash();
        $this->assign("info", $info);
        $this->assign("game_info", $game_info);
        $this->assign("gifts", $gifts);
        $this->assign("downs", $downs);
        $this->assign("is_get", $is_get);
        $this->display("gift.html");
    }

    public function game_view($id){
        $info = $this->DAO->get_game_info($id);
        $gifts = $this->DAO->get_game_gifts($id);

        $this->assign("info", $info);
        $this->assign("gifts", $gifts);
        $this->display("game.html");
    }

    public function get_code($id, $csrf){
        $_SESSION['login_return'] = '/gift'.$id;
        $this->check_login();
        $this->open_debug();
        if($csrf!=$_SESSION['page-hash']){
            $this->err_log($csrf."====".$_SESSION['page-hash'], "gift");
            die(json_encode(array("res"=>0,"msg"=>"非法操作")));
        }
        if(!$_SESSION['usr_info']['user_id']){
            die(json_encode(array("res"=>0,"msg"=>"请先登入")));
        }

        $wx_dao = new weixin_dao();
        $usr_gifts = $wx_dao->get_user_gifts($_SESSION['usr_info']['user_id']);
        foreach($usr_gifts as $gift){
            if($gift['batch_id']==$id){
                die(json_encode(array("res"=>0,"msg"=>"您已领取过了")));
            }
        }
        $this->get_gift($id);
    }

    public function get_gift($id){
        $_SESSION['login_return'] = '/gift'.$id;
        $this->check_login();
        $fp = fopen(PREFIX."/htdocs/gift.txt","w+");//增加排它锁
        if(!flock($fp,LOCK_EX|LOCK_NB)) {
            die(json_encode(array("res"=>0,"msg"=>"排队失败，请重新尝试")));
        }

        $gift_batch = $this->DAO->get_gift_info($id);

        if($gift_batch['end_time'] < strtotime("now")){
            flock($fp,LOCK_UN);
            die(json_encode(array("res"=>0,"msg"=>"礼包已过期")));
        }

        //抢光了
        if(!$gift_batch || $gift_batch['remain'] <1){
            flock($fp,LOCK_UN);
            die(json_encode(array("res"=>0,"msg"=>"靠，被抢光了！吊打GM！")));
        }

        $gift_code = $this->DAO->get_gift($id);

        if(!$gift_code){
            flock($fp,LOCK_UN);
            die(json_encode(array("res"=>0,"msg"=>"靠，被抢光了！吊打GM！")));
        }else{
            $this->DAO->update_code_status($gift_code, $_SESSION['usr_info']['user_id'], $id, $_SESSION['wx_openid']);
            flock($fp,LOCK_UN);
            die(json_encode(array("res"=>1,"msg"=>"领取成功，已放入[我的礼包]","code"=>$gift_code['code'])));
        }
    }
}