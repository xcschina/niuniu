<?php
COMMON('adminBaseCore', 'pageCore');
DAO('deal_admin_dao');

class deal_admin extends adminBaseCore
{

    public $DAO;
    public $id;

    public function __construct()
    {
        parent::__construct();
        $this->DAO = new deal_admin_dao();
    }

    public function deal_list_view()
    {
        $list = $this->DAO->list_data($this->page);
        $page = $this->pageshow($this->page, "deal.php?act=list&");
        $this->assign("datalist", $list);
        $this->assign("page_bar", $page->show());
        $this->display("deal_list.html");
    }

    public function deal_add_view()
    {

        $products = $this->request("http://www.pvm.com.cn/Api/QueryAllProduct");
        $products = (array)simplexml_load_string($products, 'SimpleXMLElement', LIBXML_NOCDATA);
        $products_list = array();
        foreach ($products['product'] as $k => $v) {
            if (strrpos($v->name, '骏网') !== FALSE) {
                $goods = $this->object_to_array($v);
                $products_list[$goods['id']] = $goods;
            }
        }
        $params = array();
        foreach ($products_list as $data) {
            $params[] = $data['price'];
        }
        $_SESSION['products_list'] = $products_list;
        array_multisort($params, SORT_ASC, $products_list);

        $this->page_hash();
        $this->assign("products_list", $products_list);
        $this->display("deal_add.html");
    }

    public function do_add()
    {
//        $merchant_id = 10002;
        $merchant_id = 10007;
        if ($_POST['pagehash'] != $_SESSION['page-hash'] || !$_POST['account']) {
            $this->error_msg("缺少必要参数");
        }
        if ($_POST['account'] != $_POST['account_again']) {
            $this->error_msg("俩次账号不一致");
        }
        if ($_POST['amount'] > 5000) {
            $this->error_msg("金额超标了");
        }

        $url = "http://www.pvm.com.cn/Api/Pay";
        $order_id = $this->orderid(1000);
        $amount = $_POST['amount'];
        $account = $_POST['account'];
        $product_id = $_POST['product_id'];

        $sign = md5($merchant_id . $order_id . $product_id . $amount . $account . pHENGJING_KEY);

        $hengjing = array("MerchantID" => $merchant_id, "MerchantOrderID" => $order_id, "ProductID" => $product_id,
            "BuyAmount" => $amount, "TargetAccount" => $account, "ResponseUrl" => pHENGJING_NOTIFY_URL, "Sign" => strtolower($sign));

        $result = $this->request($url, http_build_query($hengjing));
        $this->err_log(var_export($result, 1), "junwaka");
        $result = $this->xmlToArray($result);
        $this->err_log(var_export($result, 1), "junwaka");
        if ($result['state'] <> 101) {
            $this->error_msg("接口发生错误【" . $result['state-info'] . "】");
        } else {
            $hj_order_id = $result['order-id'];
            $order_id = $result['merchant-order-id'];
            $product = $_SESSION['products_list'][$product_id];
            $card_id = '';
            $card_password = '';
            if($result['cards']['card']['id']){
                $card_id = $result['cards']['card']['id'];
            }
            if($result['cards']['card']['id']){
                $card_password = $result['cards']['card']['password'];
            }
            $this->DAO->insert_order($order_id, $product_id, $product['price'], $amount, $product['par-value'], $product['name'], $account, $hj_order_id,$card_id,$card_password);
            $this->succeed_msg("购买成功[" . $result['state-info'] . "]");
        }

    }

    function xmlToArray($xml){
        //禁止引用外部xml实体
        libxml_disable_entity_loader(true);
        $xmlstring = simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA);
        $val = json_decode(json_encode($xmlstring),true);
        return $val;

    }
}