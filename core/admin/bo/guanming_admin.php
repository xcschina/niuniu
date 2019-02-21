<?php
COMMON('adminBaseCore','pageCore');
DAO('guanming_admin_dao');

class guanming_admin extends adminBaseCore{

    public $DAO;
    public $id;

    public function __construct(){
        parent::__construct();
        $this->DAO = new guanming_admin_dao();
    }

    public function guanming_list_view(){
        if($_POST){
            $_SESSION['guanming_list'] = $params = $_POST;
        }elseif(!$_GET['page']){
            unset($_SESSION['guanming_list']);
        }else{
            $params = $_SESSION['guanming_list'];
        }
        $list = $this->DAO->list_data($this->page,$params);
        $page = $this->pageshow($this->page, "guanming.php?act=list&");
        $this->assign("params",$params);
        $this->assign("datalist", $list);
        $this->assign("page_bar", $page->show());
        $this->display("guanming_list.html");
    }

    public function guanming_add_view(){
        $this->page_hash();
        $this->display("guanming_add.html");
    }

    public function do_add(){
        if($_POST['pagehash']!= $_SESSION['page-hash'] || !$_POST['qq']){
            $this->error_msg("缺少必要参数");
        }
        if($_POST['qq']!=$_POST['qq_again']){
            $this->error_msg("俩次qq号不一致");
        }
        if($_POST['amount']>2000){
            $this->error_msg("金额不能超过2000");
        }


        $recharge_url = "http://api.gm193.com/post/gaorder.asp";//获取地址

        $order_id = $this->orderid(999);
        $gameapi = 'ypesqb'; //戏接口编码,全国Q币为ypesqb
        $account = $_POST['qq'];//游戏帐号,通行证,或QQ号等
        $buynum = $_POST['amount'];//购买数量,当数量单位(buyunit)为元时,也就是金额,Q币单笔最高2000
        $sporderid = $order_id;//SP订单号,商户平台的订单号
        $clientip = $this->client_ip();
        $sign_str = 'username='.GUANMING_ID.'&gameapi='.$gameapi.'&sporderid='.$sporderid.'||'.GUANMING_KEY;
        $sign = md5($sign_str);

        $url = $recharge_url.'?username='.GUANMING_ID.'&gameapi='.$gameapi.
            '&account='.$account.'&buynum='.$buynum.'&sporderid='.$sporderid.'&clientip='.$clientip.
            '&returl='.GUANMING_NOTIFY_URL.'&sign='.$sign;
        $this->err_log(var_export($url,1),'guanming_log');
        $result = $this->request($url);
        $result = (array)simplexml_load_string($result, 'SimpleXMLElement', LIBXML_NOCDATA);

        if($result['info']->ret!='0'){
            $this->DAO->insert_order(GUANMING_ID,$sporderid, '1', $buynum, $account,'冠铭腾讯QB按元', '',0,$result['info']->ret);
            $this->error_msg("请求供应商出错【错误代码：".$result['info']->ret."==>错误描述：".$result['info']->ret_msg."】");

        }else{
            $price = (float)$result['gaorder']->cost/(float)$result['gaorder']->buynum;
            $number = (string)$result['gaorder']->buynum;
            $gm_order_id = (string)$result['gaorder']->orderid;
            $status = (string)$result['gaorder']->zt;
            $gm_data['price'] = $price;
            $gm_data['number'] = $number;
            $gm_data['gm_order_id'] = $gm_order_id;
            $gm_data['status'] = $status;
            $gm_data['order_id'] = $sporderid;
            $gm_data['qq'] = $account;
            $this->err_log(var_export($gm_data,1),'guanming_log');
            $this->DAO->insert_order(GUANMING_ID,$sporderid, $price, $number, $account,'冠铭腾讯QB按元', $gm_order_id,$status,$result['info']->ret);
            $this->succeed_msg("操作成功");
        }
    }

    public function orderid($app_id){
        $order_id = $app_id.date('YmdHis').time();
        return $order_id;
    }
}