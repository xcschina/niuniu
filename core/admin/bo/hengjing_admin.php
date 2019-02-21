<?php
COMMON('adminBaseCore','pageCore');
DAO('hengjing_admin_dao');

class hengjing_admin extends adminBaseCore{

    public $DAO;
    public $id;
    public $product_list;

    public function __construct(){
        parent::__construct();
        $this->DAO = new hengjing_admin_dao();
//        $this->open_debug();
        $this->product_list = array(
            '10000'=>'浙江',
            '10097'=>'浙江',
            '10062'=>'全国',
        );
    }

    public function hengjing_list_view(){
        if($_POST){
            $_SESSION['hengjing_list'] = $params = $_POST;
        }elseif(!$_GET['page']){
            unset($_SESSION['hengjing_list']);
        }else{
            $params = $_SESSION['hengjing_list'];
        }
        $operation_list = $this->DAO->get_operation_name();
        $list = $this->DAO->list_data($this->page,$params);
        $page = $this->pageshow($this->page, "hengjing.php?act=list&");
        $this->assign("params",$params);
        $this->assign("operation_list",$operation_list);
        $this->assign("datalist", $list);
        $this->assign("page_bar", $page->show());
        $this->display("hengjing_list.html");
//        $list = $this->DAO->list_data($this->page);
//        $page = $this->pageshow($this->page, "hengjing.php?act=list&");
//
//        $this->assign("datalist", $list);
//        $this->assign("page_bar", $page->show());
//        $this->display("hengjing_list.html");
    }

    public function hengjing_add_view(){

        $products = $this->request("http://www.pvm.com.cn/Api/QueryAllProduct");
        $products = (array)simplexml_load_string($products, 'SimpleXMLElement', LIBXML_NOCDATA);
        $QBs = array();
        foreach($products['product'] as $k => $v){
            if($v->id == '10000'||$v->id == '10062'||$v->id == '10097'){
                $qb = $this->object_to_array($v);
                $QBs[$qb['id']] = $qb;
            }
        }
        $qbs = array();
        foreach ($QBs as $qb) {
            $qbs[] = $qb['price'] ;
        }
        $_SESSION['QBS'] = $QBs;
        array_multisort($qbs, SORT_ASC, $QBs);

        $this->page_hash();
        $this->assign("qbs", $QBs);
        $this->display("hengjing_add.html");
    }

    public function do_add(){
        $merchant_id = 10002;
//        $merchant_id = 10007;
        if($_POST['pagehash']!= $_SESSION['page-hash'] || !$_POST['qq']){
            $this->error_msg("缺少必要参数");
        }
        if (!isset($_POST['game_id']) || $_POST['game_id']===""){
            $this->error_msg("必须选择游戏");
        }
        if($_POST['qq']!=$_POST['qq_again']){
            $this->error_msg("俩次qq号不一致");
        }
        if(strlen($_POST['qq']) < 5){
            $this->error_msg("QQ号位数不得小于五位数");
        }
        if($_POST['amount']>5000){
            $this->error_msg("金额超标了");
        }
        if(!isset($_POST['pay_pwd'])){
            $this->error_msg("支付密码不能为空");
        }else{
            $pay_pwd = md5($_POST['pay_pwd']);
            $user_info = $this->DAO->get_user_info($_SESSION['usr_id']);
            if($pay_pwd != $user_info['pay_pwd']){
                $this->error_msg("支付密码错误，请重新输入");
            }
//            if(empty($_POST['code']) || strtoupper($_POST['code']) != $_SESSION['c']){
//                $this->error_msg("图形验证码错误");
//            }
        }
        if(!preg_match('/^[1-9][0-9]*$/',$_POST['num'])){
            $this->error_msg("采购次数只能是整数");
        }
        $product_id = $_POST['product_id'];
        $qb = $_SESSION['QBS'][$product_id];
        if(strpos($qb['name'],'全国') === false){
            $ip = $this->client_ip();
            $result = $this->DAO->get_ip_session($ip);
            if(!$result){
                $result = $this->decide_ip($ip);
                $this->DAO->set_ip_session($ip,$result);
            }
            if(strpos($qb['name'],$result['region']) === false){
                $this->error_msg('你的当前ip不在允许范围');
            }
        }
        //今日订单总数
        $start_time = strtotime(date('Y-m-d 00:00:00',time()));
        $order = $this->DAO->get_order_amount($_POST['qq'],$start_time);
        if($order['amount'] >= 10000){
            $this->error_msg('该QQ号今日购买数量已达到上限10000个');
        }elseif(($_POST['amount']*$_POST['num']+$order['amount'])>10000){
            $this->error_msg('您提交订单的购买数量已超过上限10000个，请重新提交');
        }
//
//        if($this->product_list[$_POST['product_id']] != '全国'){
//            $province_list = $this->DAO->get_province_all();
//            $province = '';
//            foreach($province_list as $key=>$data){
//                $province .= $data['province'].',';
//            }
//            $province .= '全国';
//            $ip = $this->client_ip();
//            $result = $this->DAO->get_ip_session($ip);
//            if(!$result){
//                $result = $this->decide_ip($ip);
//                $this->DAO->set_ip_session($ip,$result);
//            }
//            if(!in_array($result['region'],explode(',',$province)) || !in_array($result['region'].'省',explode(',',$province))){
//                $this->error_msg('你的当前ip不在允许范围');
//            }
//        }
        if($_POST['num'] == 1){
            $result = $this->insert_order($_POST,$merchant_id,$product_id,$qb);
        }else{
            for($i=1;$i<=$_POST['num'];$i++){
                $result = $this->insert_order($_POST,$merchant_id,$product_id,$qb);
            }
        }
        $this->succeed_msg("操作成功[".$result['state-info']."]");
//        }

    }

    public function insert_order($params,$merchant_id,$product_id,$qb){
        $game_id = $params['game_id'];
        $url = "http://www.pvm.com.cn/Api/Pay";
        $order_id = $this->orderid(1000);
        $amount = $params['amount'];
        $qq = $params['qq'];

        $sign = md5($merchant_id.$order_id.$product_id.$amount.$qq.pHENGJING_KEY);

        $hengjing = array("MerchantID"=>$merchant_id, "MerchantOrderID"=>$order_id, "ProductID"=>$product_id,
            "BuyAmount"=>$amount, "TargetAccount"=>$qq, "ResponseUrl"=>pHENGJING_NOTIFY_URL, "Sign"=>strtolower($sign));
        $hengjing['CustomerIP'] = $this->client_ip();
        $hengjing['CustomerRegion'] = $this->product_list[$params['product_id']];
        $result = $this->request($url, http_build_query($hengjing));
        $result = (array)simplexml_load_string($result, 'SimpleXMLElement', LIBXML_NOCDATA);
//        if($result['state']<>101 && $result['state']<>102){
//            $this->error_msg("接口发生错误【".$result['state-info']."】");
//        }else{
        $status_info = $result['state-info'];
        $hj_order_id = $result['order-id'];
        $order_id = $result['merchant-order-id'];

//            $this->DAO->insert_order($order_id, $product_id, $qb['price'], $amount, $qb['par-value'], $qb['name'], $qq, $hj_order_id);
        $this->DAO->insert_order($order_id, $product_id, $qb['price'], $amount, $qb['par-value'], $qb['name'], $qq, $hj_order_id,$status_info,$game_id);
        return $result;
    }

    public function export(){
        set_time_limit(0);
        $params = $_GET;
        $dataList  = $this->DAO->get_all_list($params);
        if($dataList){
            $this->hengjing_excel_out($dataList);
        }else{
            echo "没有数据需要导出！";
        }
    }
    private function hengjing_excel_out($data){
        set_time_limit(0);
        $str_now=date("Y-m-d H:i:s",strtotime("now"));
        $objExcel = new PHPExcel();
        $objExcel->setActiveSheetIndex(0);
        $objActSheet = $objExcel->getActiveSheet();
        $objActSheet->setTitle("恒鲸数据");
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
            $objActSheet->setCellValueExplicit("A".$n,$info['order_id'],PHPExcel_Cell_DataType::TYPE_STRING);
            //$objActSheet->setCellValue("A".$n, $info['order_id']);
            $objActSheet->setCellValueExplicit("B".$n,$info['product_id'],PHPExcel_Cell_DataType::TYPE_STRING);
            //$objActSheet->setCellValue("B".$n, $info['product_id']);
            $objActSheet->setCellValueExplicit("C".$n,$info['amount'],PHPExcel_Cell_DataType::TYPE_STRING);
            //$objActSheet->setCellValue("C".$n, $info['amount']);
            $objActSheet->setCellValueExplicit("D".$n,$info['price'],PHPExcel_Cell_DataType::TYPE_STRING);
            //$objActSheet->setCellValue("D".$n, $info['price']);
            $objActSheet->setCellValueExplicit("E".$n,$info['qq'],PHPExcel_Cell_DataType::TYPE_STRING);
            //$objActSheet->setCellValue("E".$n, $info['qq']);
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
            }elseif ($info['game_id']==3){
                $objActSheet->setCellValue("H".$n, "欢乐爱捕鱼");
            }
            $objActSheet->setCellValue("I".$n, date("Y-m-d H:i:s",$info['addtime']));
            if ($info['callback_time']){
                $objActSheet->setCellValue("J".$n, date("Y-m-d H:i:s",$info['callback_time']));
            }else{
                $objActSheet->setCellValue("J".$n, '');
            }
            $n++;
        }
        $title = iconv("UTF-8", "GB2312//IGNORE","恒鲸数据-".$str_now.'.xls');
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

    public function province_list(){
        $province_list = $this->DAO->get_province_list($this->page);
        $this->assign('province_list',$province_list);
        $this->display('province_list.html');
    }

    public function add_province(){
        $this->display('province_add.html');
    }

    public function do_add_province(){
        if(!$_POST['province']){
            $this->error_msg('省份名不能为空');
        }
        $info = $this->DAO->get_province_info($_POST['province']);
        if($info){
            $this->error_msg('该省份已添加，无需再次添加');
        }
        $this->DAO->insert_province($_POST['province']);
        $this->succeed_msg();
    }

    public function province_edit($id){
        $info = $this->DAO->get_province($id);
        $this->assign('info',$info);
        $this->display('province_edit.html');
    }

    public function do_edit_province(){
        if(!$_POST['province']){
            $this->error_msg('省份名不能为空');
        }
        $info = $this->DAO->get_province_info($_POST['province'],$_POST['id']);
        if($info){
            $this->error_msg('该省份已添加，无需再次添加');
        }
        $this->DAO->update_province($_POST);
        $this->succeed_msg();
    }
}