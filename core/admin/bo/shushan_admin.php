<?php
COMMON('adminBaseCore','pageCore');
DAO('shushan_admin_dao');

class shushan_admin extends adminBaseCore{

    public $DAO;
    public $id;

    public function __construct(){
        parent::__construct();
        $this->DAO = new shushan_admin_dao();
    }

    public function list_view(){
        $params = $this->get_params($_POST,$_GET);
        $list = $this->DAO->list_data($this->page,$params);
        $page = $this->pageshow($this->page, "shushan.php?act=list&");
        $this->assign("params",$params);
        $this->assign("datalist", $list);
        $this->assign("page_bar", $page->show());
        $this->display("shushan_list.html");
    }

    public function add_view(){
        $all_product = $this->get_all_product();
        foreach($all_product['product'] as $key=>$data){
            if($data['id'] != '10041'){
                unset($all_product['product'][$key]);
            }
        }
        $this->assign('items_list',$all_product['product']);
        $this->page_hash();
        $this->display("shushan_add.html");
    }

    public function do_add(){
        if($_POST['pagehash']!= $_SESSION['page-hash'] || !$_POST['qq']){
            $this->error_msg("缺少必要参数");
        }
        if (!isset($_POST['game_id']) || $_POST['game_id'] === ''){
            $this->error_msg("没有选择游戏");
        }
        if (!isset($_POST['product_id']) || $_POST['product_id'] === ''){
            $this->error_msg("没有选择商品");
        }
        if($_POST['qq']!=$_POST['qq_again']){
            $this->error_msg("俩次qq号不一致");
        }
        if(!preg_match('/^[1-9][0-9]*$/',$_POST['amount'])){
            $this->error_msg("商品数量只能是整数");
        }
        if(!preg_match('/^[1-9][0-9]*$/',$_POST['num'])){
            $this->error_msg("采购次数只能是整数");
        }
        $nowtime = time();
        if(isset($_SESSION['qb_last_send_time'])){
            if($nowtime-$_SESSION['qb_last_send_time']<6){
                $this->error_msg("请求太频繁，请稍后再试");
            }else{
                $_SESSION['qb_last_send_time'] = $nowtime;
            }
        }else{
            $_SESSION['qb_last_send_time'] = $nowtime;
        }
        $amount = $_POST['amount'];
        $num = $_POST['num'];
        $detail = $this->get_product_detail($_POST['product_id']);
        $price = $detail['price'];
        if(!$price){
            $this->error_msg("商品价格出错");
        }
        //今日订单总数
        $start_time = strtotime(date('Y-m-d 00:00:00',time()));
        $order = $this->DAO->get_order_amount($_POST['qq'],$start_time);
        if($order['amount'] >= 10000){
            $this->error_msg('该QQ号今日购买数量已达到上限10000个');
        }elseif(($_POST['amount']*$_POST['num']+$order['amount']) > 10000){
            $this->error_msg('您提交订单的购买数量已超过上限10000个，请重新提交');
        }
        $merchant = $this->get_shushan_merchant();
        if((int)$amount*(float)$price*(int)$num>$merchant['balance']){
            $this->error_msg("账户余额不足");
        }
        if($num == 1){
            $this->insert_order($_POST,$amount,$price);
        }else{
            for($i=1;$i<=$num;$i++){
                $this->insert_order($_POST,$amount,$price,$i);
            }
        }
        $this->succeed_msg();
    }

    //插入订单
    public function insert_order($params,$amount,$price,$i=''){
        $order_id = $this->orderid('7070');
        $qq = $params['qq'];
        $url = "http://api.shushanzx.shucard.com/Api/Pay";
        $shushan = array(
            "MerchantID" => shushan_merchant_id,
            "MerchantOrderID" => $order_id,
            "ProductID" => $params['product_id'],
            "BuyAmount"=>$amount,
            "TargetAccount" => $qq,
            "ResponseUrl" => shushan_NOTIFY_URL,
        );
        ksort($shushan);
        $sign = md5(shushan_merchant_id.$order_id.$params['product_id'].$amount.$qq.shushan_key);
        $shushan['Sign'] = $sign;
        $result = $this->request($url,$shushan);
        $result = json_decode(json_encode(simplexml_load_string($result, 'SimpleXMLElement', LIBXML_NOCDATA)), true);
        //增加http状态
        $result['http_status'] = $this->curl_status;
        $this->err_log(var_export($result, 1), "shushan_result");
        if(101 == $result['state'] || '200' == $result['http_status']){
            //查询订单接口
            $order_query_res = $this->get_order($order_id);
            if($order_query_res['state'] && $order_query_res['merchant-order-id'] == $order_id){
                if(101 == $order_query_res['state']){
                    $insert_status = 1;
                }elseif(102 == $order_query_res['state']){
                    $insert_status = 0;
                }elseif(103 == $order_query_res['state']){
                    $insert_status = 2;
                }else{
                    $insert_status = 4;
                }
                $info_id = $this->DAO->insert_order_by_query($order_id, $params, $price,$order_query_res['order-id'],$insert_status);
                if(!$info_id){
                    if($i){
                        $this->error_msg("插入数据库错误,请报告技术,已成功插入".$i."条记录");
                    }else{
                        $this->error_msg("插入数据库错误,请报告技术");
                    }
                }
            }else{
                $order_query_res['order_id'] = $order_id;
                $this->err_log(var_export($order_query_res, 1), "shushan_query_result");
                $this->DAO->insert_order($order_id, $params, $price,$order_query_res['order-id']);
                if($i){
                    $this->error_msg("请求供应商出错【".$result['state']."==>".$result['state-info']."】,已成功插入".$i."条记录");
                }else{
                    $this->error_msg("请求供应商出错【".$result['state']."==>".$result['state-info']."】");
                }
            }
        }elseif(!$result['state'] || !$result['order-id']){
            $this->err_log(var_export($result, 1), "shushan_query_result");
            if($i){
                $this->error_msg("请求供应商出错【".$result['state']."==>".$result['state-info']."】,已成功插入".$i."条记录");
            }else{
                $this->error_msg("请求供应商出错【".$result['state']."==>".$result['state-info']."】");
            }
        }else{
            $shushan_order_id = $result['order-id'];
            $info_id = $this->DAO->insert_order($order_id, $params, $price,$shushan_order_id);
            if(!$info_id){
                if($i){
                    $this->error_msg("插入数据库错误,请报告技术,已成功插入".$i."条记录");
                }else{
                    $this->error_msg("插入数据库错误,请报告技术");
                }
            }
        }
    }

    //查询订单
    public function get_order($order_id){
        $url = 'http://api.shushanzx.shucard.com/Api/QueryOrder';
        $data = array(
            'MerchantID'=>shushan_merchant_id,
            'MerchantOrderID'=>$order_id
        );
        $data['Sign'] = md5(shushan_merchant_id.$order_id.shushan_key);
        $result = $this->request($url,$data);
        //将返回的xml转换成数组
        $result = json_decode(json_encode(simplexml_load_string($result, 'SimpleXMLElement', LIBXML_NOCDATA)), true);
        return $result;
    }

    //获取所有商品
    public function get_all_product(){
        $url = "http://api.shushanzx.shucard.com/Api/QueryAllProduct";
        $result = $this->request($url);
        //将返回的xml转换成数组
        $result = json_decode(json_encode(simplexml_load_string($result, 'SimpleXMLElement', LIBXML_NOCDATA)), true);
        return $result;
    }

    //查询商品详细信息
    public function get_product_detail($product_id){
        $url = 'http://api.shushanzx.shucard.com/Api/QueryProductDetails';
        $data['ProductID'] = $product_id;
        $result = $this->request($url,$data);
        //将返回的xml转换成数组
        $result = json_decode(json_encode(simplexml_load_string($result, 'SimpleXMLElement', LIBXML_NOCDATA)), true);
        return $result;
    }

    //查询商户信息
    public function get_shushan_merchant(){
        $url = "http://api.shushanzx.shucard.com/Api/QueryMerchant";
        $shushan = array(
            "MerchantID" => shushan_merchant_id,
        );
        ksort($shushan);
        $shushan['Sign'] = md5(shushan_merchant_id.shushan_key);
        $result = $this->request($url,$shushan);
        //将返回的xml转换成数组
        $result = json_decode(json_encode(simplexml_load_string($result, 'SimpleXMLElement', LIBXML_NOCDATA)), true);
        return $result;
    }

    public function export(){
        set_time_limit(0);
        $params = $_GET;
        $dataList  = $this->DAO->get_list_all($params);
        if($dataList){
            $this->shushan_excel_out($dataList);
        }else{
            echo "没有数据需要导出！";
        }
    }

    private function shushan_excel_out($data){
        set_time_limit(0);
        $str_now=date("Y-m-d H:i:s",strtotime("now"));
        $objExcel = new PHPExcel();
        $objExcel->setActiveSheetIndex(0);
        $objActSheet = $objExcel->getActiveSheet();
        $objActSheet->setTitle("蜀山Q币数据");
        $objActSheet->setCellValue("A1", "订单号");
        $objActSheet->setCellValue("B1", "商品编号");
        $objActSheet->setCellValue("C1", "购买数量");
        $objActSheet->setCellValue("D1", "价格");
        $objActSheet->setCellValue("E1", "qq号");
        $objActSheet->setCellValue("F1", "状态");
        $objActSheet->setCellValue("G1","游戏");
        $objActSheet->setCellValue("H1", "下单时间");
        $objActSheet->setCellValue("I1", "回单时间");
        $n = 2;
        foreach($data as $info){
            $objActSheet->setCellValueExplicit("A".$n,$info['order_id'],PHPExcel_Cell_DataType::TYPE_STRING);
            $objActSheet->setCellValueExplicit("B".$n,$info['product_id'],PHPExcel_Cell_DataType::TYPE_STRING);
            $objActSheet->setCellValueExplicit("C".$n,$info['amount'],PHPExcel_Cell_DataType::TYPE_STRING);
            $objActSheet->setCellValueExplicit("D".$n,$info['price'],PHPExcel_Cell_DataType::TYPE_STRING);
            $objActSheet->setCellValueExplicit("E".$n,$info['qq'],PHPExcel_Cell_DataType::TYPE_STRING);
            if ($info['status']==0){
                $objActSheet->setCellValue("F".$n, "未完成");
            }elseif($info['status']==1){
                $objActSheet->setCellValue("F".$n, "已完成");
            }elseif($info['status']==2){
                $objActSheet->setCellValue("F".$n, "可疑订单");
            }elseif ($info['status']==4){
                $objActSheet->setCellValue("F".$n, "充值失败");
            }
            if ($info['game_id']==0){
                $objActSheet->setCellValue("G".$n, "魔域口袋版（网龙）");
            }elseif($info['game_id']==1){
                $objActSheet->setCellValue("G".$n, "问道");
            }elseif ($info['game_id']==2){
                $objActSheet->setCellValue("G".$n, "魔域手游（西山居）");
            }
            $objActSheet->setCellValue("H".$n, date("Y-m-d H:i:s",$info['add_time']));
            if ($info['callback_time']){
                $objActSheet->setCellValue("I".$n, date("Y-m-d H:i:s",$info['callback_time']));
            }else{
                $objActSheet->setCellValue("I".$n, '');
            }
            $n++;
        }
        $title = iconv("UTF-8", "GB2312//IGNORE","蜀山Q币数据-".$str_now.'.xls');
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