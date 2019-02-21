<?php
COMMON('baseCore', 'pageCore','uploadHelper');
DAO('sell_mobile_dao');
VALIDATOR("sell_validator");

class sell_mobile extends baseCore{

    public $DAO;
    public $id;
    public $sell_type;
    public $type;

    public function __construct(){
        parent::__construct();
        $this->DAO = new sell_mobile_dao();
        $this->V->template_dir  = VIEW."/v2/sell";
        $this->sell_type = array("account"=>4,"props"=>6,"coin"=>5);
        $this->type = array(
            1=>"首充号",
            2=>"首充号续充",
            3=>"代充",
            4=>"账号",
            5=>"游戏币",
            6=>"道具",
            7=>"礼包"
        );
    }

    public function sell_account_view($game_id){
        $this->page_hash();
        $game = $this->DAO->get_game_info($game_id);
        $chs = $this->DAO->get_channels();

        $this->assign("info", $game);
        $this->assign("chs", $chs);
        $this->display("sell_account.html");
        $_SESSION['sell_error']='';
    }

    public function sell_props_view($game_id){
        $this->page_hash();
        $game = $this->DAO->get_game_info($game_id);
        $chs = $this->DAO->get_channels();
        $types = explode("|", $game['game_storages']);

        $this->assign("info", $game);
        $this->assign("chs", $chs);
        $this->assign("types", $types);
        $this->display("sell_props.html");
        $_SESSION['sell_error']='';
    }

    public function sell_coin_view($game_id){
        $this->page_hash();
        $game = $this->DAO->get_game_info($game_id);
        $chs = $this->DAO->get_channels();
        $units = explode("|", $game['game_units']);

        $this->assign("info", $game);
        $this->assign("chs", $chs);
        $this->assign("units", $units);
        $this->display("sell_coin.html");
        $_SESSION['sell_error']='';
    }

    public function sell_publish($game_id){
        if(!$_SESSION['sell_info']){
            $this->redirect("");
        }
        $this->page_hash();
        $game = $this->DAO->get_game_info($game_id);

        $this->assign("info", $game);
        $this->assign("sell", $_SESSION['sell_info']);
        $this->display("sell_publish.html");
    }

    public function do_account(){
        $sell_validator = new sellValidator($_POST);
        $sell_validator->check_account_one("http://".SITEURL."/game".$_POST['game_id']."/sell");
        $_SESSION['sell_info'] = $_POST;
        $this->redirect("game".$_POST['game_id']."/sell?act=publish");
    }

    public function do_publish(){
        $imgs = $this->up_sell_img();
        $sell_validator = new sellValidator($_POST);
        $sell_validator->check_publish("http://".SITEURL."/game".$_POST['game_id']."/sell?act=publish");
        $sell_id = $this->DAO->insert_sell($_SESSION['sell_info'], $_POST, $this->sell_type[$_POST['do']]);
        $this->DAO->insert_sell_extra($_POST, $sell_id);
        if($imgs && $imgs!="error"){
            $imgs = explode("|",$imgs);
            foreach($imgs as $img){
                if($img)$this->DAO->insert_sell_imgs($img, $sell_id);
            }
        }
        unset($_SESSION['sell_info']);
        $this->page_hash();
        $_SESSION['sell_error']='';

        $info = $this->DAO->get_product($sell_id);

        $this->assign("info", $info);
        $this->assign("sell_type", $this->type[$info['type']]);
        $this->display("pub_success.html");
    }

    protected function up_sell_img(){
        if(isset($_FILES['imgs'])&&$_FILES['imgs']['tmp_name'] && $_FILES['imgs']['tmp_name'][0]){
            $project_folder = PREFIX."/htdocs/static/images/sell/".date('Ym')."/";
            $imgs = $this->batch_up_img('imgs', 'images/sell', array(640,640));
            if($imgs){
                return implode('|', $imgs);
            }else{
                return 'error';
            }
        }
    }
}