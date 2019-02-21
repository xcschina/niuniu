<?php
COMMON('baseCore', 'pageCore','uploadHelper','class.phpmailer');
DAO('account_sell_dao','my_dao');
VALIDATOR("sell_validator");
class account_sell_mobile  extends baseCore{
    public $DAO;
    protected $type;

    public function __construct(){
        parent::__construct();
        $this->DAO=new account_sell_dao();
        $this->V->template_dir  = VIEW."/v2";
        if(!$_SESSION['user_id']){
            $this->redirect("account.php?act=login");
        }
        $this->type = array(
            1=>"首充号",
            2=>"首充号续充",
            3=>"代充",
            4=>"账号",
            5=>"游戏币",
            6=>"道具",
            7=>"礼包"
        );
        $this->open_debug();
    }

    public function sell_view($act = 'sell'){
        $products = $this->DAO->get_pub_products($_SESSION['user_id']);

        $this->assign("products", $products);
        $this->display("account/account_sells.html");
    }

    public function stock_view($act = 'stock'){
        if($act=='audit'){
            $products = $this->DAO->get_audit_products($_SESSION['user_id']);
        }elseif($act=='unput'){
            $products = $this->DAO->get_unput_products($_SESSION['user_id']);
        }else{
            $products = $this->DAO->get_stock_products($_SESSION['user_id']);
        }

        $this->assign("products", $products);
        $this->assign("act", $act);
        $this->display("account/account_sell_stock.html");
    }

    //已售页面
    public function selled_view(){
        $orders = $this->DAO->get_sell_orders($_SESSION['user_id'], $this->page);

        $this->assign("orders", $orders);
        $this->display("account/account_sell_orders.html");
    }

    //单品信息页面
    public function stock_item_view($id){
        $info = $this->DAO->get_sell_info($_SESSION['user_id'], $id);
        $imgs = $this->DAO->get_product_imgs($id);
        $this->page_hash();

        if($info['is_pub']==3){
            $audit_log = $this->DAO->get_sell_audit($id);
            $this->assign("audit", $audit_log);
        }

        $this->assign("info", $info);
        $this->assign("imgs", $imgs);
        $this->display("account/account_sell_stock_info.html");
    }

    //修改页面
    public function stock_item_edit_view($id){
        $this->page_hash();
        $info = $this->DAO->get_sell_info($_SESSION['user_id'], $id);
        if($info['is_pub']!=0 && $info['is_pub']!=3){
            $this->redirect("my_sell.php?act=stock-item&id=".$id);
            exit;
        }
        if($info['type']!=4 && $info['type']!=5 && $info['type']!=6){
            $this->redirect("my_sell.php?act=stock-item&id=".$id);
            exit;
        }
        $chs = $this->DAO->get_channels();
        $units = explode("|", $info['game_units']);
        $types = explode("|", $info['game_storages']);

        $this->assign("info", $info);
        $this->assign("sell_type", $this->type[$info['type']]);
        $this->assign("chs", $chs);
        $this->assign("units", $units);
        $this->assign("types", $types);
        $this->display("account/account_sell_stock_info_edit.html");
    }

    public function stock_item_edit_check($id){
        $sell_validator = new sellValidator($_POST);
        $sell_validator->check_account_one("http://".SITEURL."/my_sell.php?act=stock-item-edit&id=".$id);
        $_SESSION['sell_info'] = $_POST;
        $this->redirect("my_sell.php?act=stock-item-pub&id=".$id);
    }

    public function stock_item_edit_pub($id){
        if(!$_SESSION['sell_info']){
            $this->redirect("");
            exit;
        }
        $this->page_hash();
        $info = $this->DAO->get_sell_info($_SESSION['user_id'], $id);
        $imgs = $this->DAO->get_product_imgs($id);

        $this->assign("info", $info);
        $this->assign("imgs", $imgs);
        $this->assign("sell", $_SESSION['sell_info']);
        $this->display("account/account_sell_stock_info_pub.html");
    }

    public function do_stock_item_edit_pub($id){
        $imgs = $this->up_sell_img();
        $sell_validator = new sellValidator($_POST);
        $sell_validator->check_publish("http://".SITEURL."/my_sell.php?act=stock");

        $this->DAO->update_sell_info($id, $_SESSION['sell_info'], $_POST);
        $this->DAO->update_sell_extra($id, $_POST);
        if($imgs && $imgs!="error"){
            $this->DAO->delete_sell_imgs($id);
            $imgs = explode("|",$imgs);
            foreach($imgs as $img){
                if($img)$this->DAO->insert_sell_imgs($img, $id);
            }
        }
        unset($_SESSION['sell_info']);
        $this->page_hash();
        $_SESSION['sell_error']='';

        $this->display("account/account_sell_pub_success.html");
    }

    //撤销审核
    public function stock_item_undo_audit($id, $token){
        $info = $this->DAO->get_sell_info($_SESSION['user_id'], $id);
        if(!$info || $_SESSION['page-hash']!= $token || ($info['is_pub']!=2 && $info['is_pub']!=1)){
            die("你来到错误的地方");
        }

        $this->DAO->update_undo_audit($id);
        $this->display("account/account_sell_undo_audit.html");
    }

    //删除商品
    public function stock_item_delete($id, $token){
        $info = $this->DAO->get_sell_info($_SESSION['user_id'], $id);
        if(!$info || $_SESSION['page-hash']!= $token || ($info['is_pub']!=3 &&$info['is_pub']!=0)){
            die("你来到错误的地方");
        }

        $this->DAO->delete_sell($id);
        $this->display("account/account_sell_delete.html");
    }

    //已卖订单详情
    public function selled_item_info($id){
        $info = $this->DAO->get_order_info($id, $_SESSION['user_id']);
        $imgs = $this->DAO->get_product_imgs($info['product_id']);

        $this->assign("info", $info);
        $this->assign("imgs", $imgs);
        $this->display("account/account_sell_order_info.html");
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
