<?php
COMMON('baseCore', 'pageCore');
DAO('gift_dao');
class gift_web extends baseCore{

    protected $DAO;

    public function __construct(){
        parent::__construct();
        $this->DAO = new gift_dao();
        $this->user_id=$_SESSION['user_id'];
        if(isset($_SERVER['HTTP_USER_AGENT1'])){
            $header = base64_decode(substr($_SERVER['HTTP_USER_AGENT1'],1));
            $header = explode("&",$header);
            foreach($header as $k=>$param){
                $param = explode("=",$param);
                if($param[0] == 'user_id'){
                    $this->user_id = $param[1];
                }
            }
        }
    }

    public function list_view(){
        if($this->user_id){
            $_SESSION['user_id'] = $this->user_id;
        }
        $gift_list = $this->DAO->get_gift_list();
        if($this->user_id){
            foreach($gift_list as $k=>$gift){
                $my_gifts = $this->DAO->get_user_gift_batch($this->user_id,$gift['id']);
                if($my_gifts){
                    $gift_list[$k]['is_get'] = 1;
                }
            }
        }
        $this->assign("gifts", $gift_list);
        $this->display("gift_view.html");
    }

    public function item_view($id){
        $info = $this->DAO->get_gift_info($id);
        $gifts = $this->DAO->get_game_gifts($info['game_id']);
        $game_info = $this->DAO->get_game_info($info['game_id']);
        $downs = $this->DAO->get_game_downs($info['game_id']);
        $is_get = 0;
        if($this->user_id){
            $usr_gifts = $this->DAO->get_user_gifts($this->user_id);
            foreach($usr_gifts as $gift){
                if($gift['batch_id']==$info['id']){
                    $is_get = 1;
                }
            }
        }
        if(!empty($gifts)){
            foreach($gifts as $k=>$v){
                if($v['id']==$id){
                    unset($gifts[$k]);
                }
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

    public function get_code($id, $csrf){
        if($csrf!=$_SESSION['page-hash']){
            $this->err_log($csrf."====".$_SESSION['page-hash'], "gift");
            die(json_encode(array("res"=>0,"msg"=>"非法操作")));
        }
        if(!$_SESSION['user_id']){
            die(json_encode(array("res"=>2,"msg"=>"请先登入")));
        }
        $usr_gifts = $this->DAO->get_user_gifts($_SESSION['user_id']);
        foreach($usr_gifts as $gift){
            if($gift['batch_id']==$id){
                die(json_encode(array("res"=>0,"msg"=>"您已领取过了")));
            }
        }
        $this->get_gift($id);
    }

    public function get_gift($id){
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
            die(json_encode(array("res"=>0,"msg"=>"靠，被抢光了！吊打GM！")));
        }else{
            $this->DAO->update_code_status($gift_code, $_SESSION['user_id'], $id, 'app_gift_'.$_SESSION['user_id']);
            flock($fp,LOCK_UN);
            die(json_encode(array("res"=>1,"msg"=>"领取成功","code"=>$gift_code['code'])));
        }
    }
}