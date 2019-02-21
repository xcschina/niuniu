<?php
COMMON('adminBaseCore','pageCore');
DAO('hengjingwendao_admin_dao');

class hengjingwendao_admin extends adminBaseCore{

    public $DAO;
    public $id;

    public function __construct(){
        parent::__construct();
        $this->DAO = new hengjingwendao_admin_dao();
        //$this->open_debug();
    }

    public function hengjingwendao_list_view(){
        if($_POST){
            $_SESSION['hengjingwendao_list'] = $params = $_POST;
        }elseif(!$_GET['page']){
            unset($_SESSION['hengjingwendao_list']);
        }else{
            $params = $_SESSION['hengjingwendao_list'];
        }
        $operation_list = $this->DAO->get_operation_name();
        $list = $this->DAO->list_data($this->page,$params);
        $page = $this->pageshow($this->page, "hengjingwendao.php?act=list&");
        $this->assign("params",$params);
        $this->assign("operation_list",$operation_list);
        $this->assign("datalist", $list);
        $this->assign("page_bar", $page->show());
        $this->display("hengjingwendao_list.html");
    }

    public function hengjingwendao_add_view(){

        $products = $this->request("http://www.pvm.com.cn/Api/QueryAllProduct");
        $products = (array)simplexml_load_string($products, 'SimpleXMLElement', LIBXML_NOCDATA);
        $QBs = array();
        foreach($products['product'] as $k => $v){
            if((strrpos($v->name, 'QB') !== FALSE) && ($v->price=="0.980")){
                $qb = $this->object_to_array($v);
                $QBs[$qb['id']] = $qb;
            }
        }
        $qbs = array();
        foreach ($QBs as $qb) {
            $qbs[] = $qb['price'];
        }
        $_SESSION['QBS'] = $QBs;
        array_multisort($qbs, SORT_ASC, $QBs);

        $this->page_hash();
        $this->assign("qbs", $QBs);
        $this->display("hengjingwendao_add.html");
    }

    public function do_add(){
        $merchant_id = 10008;
        if($_POST['pagehash']!= $_SESSION['page-hash'] || !$_POST['qq']){
            $this->error_msg("缺少必要参数");
        }
        if (!isset($_POST['game_id']) || $_POST['game_id']===""){
            $this->error_msg("必须选择游戏");
        }
        if($_POST['qq']!=$_POST['qq_again']){
            $this->error_msg("俩次qq号不一致");
        }
        if($_POST['amount']>5000){
            $this->error_msg("金额超标了");
        }
        $game_id = $_POST['game_id'];
        $url = "http://www.pvm.com.cn/Api/Pay";
        $order_id = $this->orderid(1000);
        $amount = $_POST['amount'];
        $qq = $_POST['qq'];
        $product_id = $_POST['product_id'];

        $sign = md5($merchant_id.$order_id.$product_id.$amount.$qq.pHENGJINGWENDAO_KEY);

        $hengjing = array("MerchantID"=>$merchant_id, "MerchantOrderID"=>$order_id, "ProductID"=>$product_id,
            "BuyAmount"=>$amount, "TargetAccount"=>$qq, "ResponseUrl"=>pHENGJINGWENDAO_NOTIFY_URL, "Sign"=>strtolower($sign));

        $result = $this->request($url, http_build_query($hengjing));
        $result = (array)simplexml_load_string($result, 'SimpleXMLElement', LIBXML_NOCDATA);
//        if($result['state']<>101 && $result['state']<>102){
//            $this->error_msg("接口发生错误【".$result['state-info']."】");
//        }else{
            $status_info = $result['state-info'];
            $hj_order_id = $result['order-id'];
            $order_id = $result['merchant-order-id'];
            $qb = $_SESSION['QBS'][$product_id];

            $this->DAO->insert_order($order_id, $product_id, $qb['price'], $amount, $qb['par-value'], $qb['name'], $qq, $hj_order_id,$status_info,$game_id);
            $this->succeed_msg("操作成功[".$result['state-info']."]");
//        }

    }

    public function export(){
        set_time_limit(0);
        $params = $_GET;
        $dataList  = $this->DAO->get_all_list($params);
        if($dataList){
            $this->hengjingwendao_excel_out($dataList);
        }else{
            echo "没有数据需要导出！";
        }
    }

    private function hengjingwendao_excel_out($data){
        set_time_limit(0);
        $str_now=date("Y-m-d H:i:s",strtotime("now"));
        $objExcel = new PHPExcel();
        $objExcel->setActiveSheetIndex(0);
        $objActSheet = $objExcel->getActiveSheet();
        $objActSheet->setTitle("恒鲸问道数据");
        $objActSheet->setCellValue("A1", "订单号");
        $objActSheet->setCellValue("B1", "商品编号");
        $objActSheet->setCellValue("C1", "购买数量");
        $objActSheet->setCellValue("D1", "价格");
        $objActSheet->setCellValue("E1", "qq号");
        $objActSheet->setCellValue("F1", "状态");
        $objActSheet->setCellValue("G1", "状态信息");
        $objActSheet->setCellValue("H1","游戏");
        $objActSheet->setCellValue("I1", "下单时间");
        $objActSheet->setCellValue("J1", "回单时间");
        $n = 2;
        foreach($data as $info){
            $objActSheet->setCellValue("A".$n, $info['order_id']);
            $objActSheet->setCellValue("B".$n, $info['product_id']);
            $objActSheet->setCellValue("C".$n, $info['amount']);
            $objActSheet->setCellValue("D".$n, $info['price']);
            $objActSheet->setCellValue("E".$n, $info['qq']);
            if ($info['status']==0){
                $objActSheet->setCellValue("F".$n, "未完成");
            }elseif($info['status']==1){
                $objActSheet->setCellValue("F".$n, "已完成");
            }
            $objActSheet->setCellValue("G".$n, $info['status_info']);
            if ($info['game_id']==0){
                $objActSheet->setCellValue("H".$n, "魔域口袋版（网龙）");
            }elseif($info['game_id']==1){
                $objActSheet->setCellValue("H".$n, "问道");
            }elseif ($info['game_id']==2){
                $objActSheet->setCellValue("H".$n, "魔域手游（西山居）");
            }
            $objActSheet->setCellValue("I".$n, date("Y-m-d H:i:s",$info['addtime']));
            if ($info['callback_time']){
                $objActSheet->setCellValue("J".$n, date("Y-m-d H:i:s",$info['callback_time']));
            }else{
                $objActSheet->setCellValue("J".$n, '');
            }
            $n++;
        }
        $title = iconv("UTF-8", "GB2312//IGNORE","恒鲸问道数据-".$str_now.'.xls');
        header("Content-type: text/html;charset=utf-8");
        header("Content-Type: application/force-download");
        header("Content-Type: application/octet-stream");
        header("Content-Type: application/download");
        header('Content-Disposition:inline;filename="'.$title.'"');
        header("Content-Transfer-Encoding: binary");
        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
        header("Pragma: no-cache");
        $objWriter = PHPExcel_IOFactory::createWriter($objExcel, 'Excel5');
        $objWriter->save('php://output');
    }
}