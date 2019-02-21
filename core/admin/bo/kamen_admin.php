<?php
COMMON('adminBaseCore','pageCore');
DAO('kamen_admin_dao');

class kamen_admin extends adminBaseCore{

    public $DAO;
    public $id;

    public function __construct(){
        parent::__construct();
        $this->DAO = new kamen_admin_dao();
    }

    public function kamen_list_view(){
        if($_POST){
            $_SESSION['kamen_list'] = $params = $_POST;
        }elseif(!$_GET['page']){
            unset($_SESSION['kamen_list']);
        }else{
            $params = $_SESSION['kamen_list'];
        }
        $list = $this->DAO->list_data($this->page,$params);
        $page = $this->pageshow($this->page, "kamen.php?act=list&");
        $this->assign("params",$params);
        $this->assign("datalist", $list);
        $this->assign("page_bar", $page->show());
        $this->display("text.html");
    }

    public function kamen_add_view(){
        $this->page_hash();
        $this->display("kamen_add.html");
    }

    public function do_add(){
        if($_POST['pagehash']!= $_SESSION['page-hash'] || !$_POST['qq']){
            $this->error_msg("缺少必要参数");
        }
        if($_POST['qq']!=$_POST['qq_again']){
            $this->error_msg("俩次qq号不一致");
        }
        if($_POST['amount']>5000){
            $this->error_msg("金额超标了");
        }

        $url = "http://ccapi.kamenwang.com/Interface/Method";
        $order_id = $this->orderid(1000);
//        $product_id = 702349; //旧的
        $product_id = 961331;
        $amount = $_POST['amount'];
        $qq = $_POST['qq'];
        $notify_url = pKAMEN_NOTIFY_URL;
        //yyyy-MM-dd HH:mm:ss
        $kamen = array("method"=>urlencode("kamenwang.order.add"), "timestamp"=>date("Y-m-d H:i:s"), "format"=>"json", "v"=>"1.0",
                        "customerid"=> pKAMEN_ID, "customerorderno"=>$order_id, "productid"=> $product_id, "buynum"=>$amount, "chargeaccount"=> $qq, "notifyurl"=> $notify_url);
        ksort($kamen);
        $sign_str = urldecode(http_build_query($kamen));
        $sign = md5($sign_str.pKAMEN_KEY);
        $kamen['sign'] = $sign;

        $result = $this->request($url, http_build_query($kamen));
        //die(print_r($result));
        $result = json_decode($result);
        if(!$result->Status || !$result->OrderId){
            $this->error_msg("请求供应商出错【".$result->MessageCode."==>".$result->MessageInfo."】");
        }else{
            $kamen_order_id = $result->OrderId;
            $purchase_price = $result->PurchasePrice;
            $info_id = $this->DAO->insert_order($order_id, $product_id, $amount, $purchase_price, $qq, $kamen_order_id);
            if(!$info_id){
                $this->error_msg("插入数据库错误,请报告技术");
            }
            $this->succeed_msg();
        }
    }

    public function edit_view($id){
        $info = $this->DAO->get($id);
        $sys_games = $this->DAO->get_sys_game_info($id);

        $this->assign("info", $info);
        $this->assign("sys_games", $sys_games);
        $this->display("7881_edit.html");
    }

    public function do_edit($id){
        $sys_game_id = $_POST['sys_game_id'];
//        if($sys_game_id){
//            $this->DAO->update_game_bind($id, $sys_game_id);
//        }
        $this->DAO->update_game_bind($id, $sys_game_id);
        echo json_encode($this->succeed_msg("编辑成功".$sys_game_id,"p7881"));
    }
}