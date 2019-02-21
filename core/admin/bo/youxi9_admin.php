<?php
COMMON('adminBaseCore','pageCore','uploadHelper');
DAO('youxi9_dao');

class youxi9_admin extends adminBaseCore{
    public $DAO;
    public $goods_id;

    public function __construct(){
        parent::__construct();
        $this->DAO = new youxi9_dao();
        $this->goods_id = 30;
    }

    public function list_view(){
        $params = $this->get_params($_POST,$_GET);
        $data_list = $this->DAO->get_youxi9_list($params,$this->page,2);
        $operation_list = $this->DAO->get_operation_name();
        $page = $this->pageshow($this->page, "youxi9.php?act=list&");
        $this->assign("operation_list",$operation_list);
        $this->assign("params",$params);
        $this->assign("page_bar",$page->show());
        $this->assign('data_list',$data_list);
        $this->display("youxi9_list.html");
    }

    public function add_view($pay_way){
        $this->page_hash();
        $this->assign("pay_way",$pay_way);
        $this->display("youxi9_add.html");
    }

    public function do_add(){
        $params = $_POST;
        if($params['pagehash']!= $_SESSION['page-hash']){
            $this->error_msg("参数异常!  001");
        }
        if(!$params['qq'] || !$params['amount']){
            $this->error_msg("缺少必要参数");
        }
        if (!isset($params['game_id']) || $params['game_id'] === ''){
            $this->error_msg("没有选择游戏");
        }
        if($params['qq']!=$params['qq_again']){
            $this->error_msg("俩次qq号不一致");
        }
        if(!preg_match('/^[1-9][0-9]*$/',$params['amount'])){
            $this->error_msg("商品数量只能是整数");
        }
        if(!preg_match('/^[1-9][0-9]*$/',$params['num'])){
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
        $params['pay_way'] = $params['pay_way']?$params['pay_way']:1;
        //今日订单总数
        $start_time = strtotime(date('Y-m-d 00:00:00',time()));
        $order = $this->DAO->get_order_amount($params,$start_time);
        if($order['amount'] >= 10000){
            $this->error_msg('该QQ号今日购买数量已达到上限10000个');
        }elseif(($params['amount']*$params['num']+$order['amount'])>10000){
            $this->error_msg('您提交订单的购买数量已超过上限10000个，请重新提交');
        }
        $price = $this->get_price($params['pay_way']);
        $merchant = $this->get_merchant($params['pay_way']);
        $balance = $params['pay_way']==1?$merchant['balanceAccount']:$merchant['balance'];
        if((int)$params['amount']*$price['rate']*(int)$params['num']>$balance){
            $this->error_msg("账户余额不足");
        }
        $params['price'] = $price['rate'];
        if($params['num'] == 1){
            $this->insert_order($params);
        }else{
            for($i=1;$i<=$params['num'];$i++){
                $this->insert_order($params,$i);
            }
        }
        $this->succeed_msg();
    }

    public function insert_order($params,$i=''){
        $order_id = $this->orderid('9090');
        $url = "http://down2.utoozs.com/Card/QB";
        //1对私 2对公 默认对公
        if($params['pay_way'] == 1){
            $merchant_id = youxi9_merchant_id_P;
            $key = youxi9_key_P;
        }else{
            $merchant_id = youxi9_merchant_id;
            $key = youxi9_key;
        }
        $youxi9 = array(
            'MerchantID' => $merchant_id,
            'orderId' => $order_id,
            'goodsId' => $this->goods_id,
            'num' => $params['amount'],
            'account' => $params['qq'],
            'payway' => $params['pay_way'],
        );
        $youxi9['Sign'] = strtolower(md5($merchant_id.$key.$this->goods_id.$order_id.$params['amount'].$params['qq']));
        $data = json_encode($youxi9);
        $result = $this->request($url,$data);
        $result = json_decode(trim($result,chr(239).chr(187).chr(191)),true);
        $result['http_status'] = $this->curl_status;
        $this->err_log(var_export($result, 1), "youxi9_result");
        if($result['status'] == '2' || $result['http_status'] == '200'){
            $order_query_res = $this->get_order($order_id,$params);
            if($order_query_res['status'] == '3' || $order_query_res['status'] == '4'){
                $order_query_res['status'] = 3;
            }
            if($order_query_res['status'] == '2' && $order_query_res['orderId'] == $order_id){
                $info_id = $this->DAO->insert_order_by_query($order_id, $params, $order_query_res,$this->goods_id);
                if(!$info_id){
                    if($i){
                        $this->error_msg("插入数据库错误,请报告技术,已成功插入".$i."条记录");
                    }else{
                        $this->error_msg("插入数据库错误,请报告技术");
                    }
                }
            }else{
                $this->err_log(var_export($order_query_res, 1), "youxi9_query_result");
                if($order_query_res['status'] == '3'){
                    $this->DAO->insert_order_by_query($order_id, $params, $order_query_res,$this->goods_id);
                    if($i){
                        $this->error_msg("请求供应商出错【".$result['status']."==>".$result['msg']."】,已成功插入".$i."条记录");
                    }else{
                        $this->error_msg("请求供应商出错【".$result['status']."==>".$result['msg']."】");
                    }
                }else{
                    $order_query_res['msg'] = '';
                    $this->DAO->insert_order_by_query($order_id, $params, $order_query_res,$this->goods_id);
                }
            }
        }elseif(!isset($result['status']) || !$result['productId']){
            if($result['http_status'] == 0){
                $this->DAO->insert_order($order_id, $params, $result['productId'],$this->goods_id);
            }
            $this->err_log(var_export($result, 1), "youxi9_query_result");
            if($i){
                $this->error_msg("请求供应商出错【".$result['status']."==>".$result['msg']."】,已成功插入".$i."条记录");
            }else{
                $this->error_msg("请求供应商出错【".$result['status']."==>".$result['msg']."】");
            }
        }else{
            $info_id = $this->DAO->insert_order($order_id, $params, $result['productId'],$this->goods_id);
            if(!$info_id) {
                if($i){
                    $this->error_msg("插入数据库错误,请报告技术,已成功插入".$i."条记录");
                }else{
                    $this->error_msg("插入数据库错误,请报告技术");
                }
            }
        }
    }

    public function get_merchant($pay_way){
        //1对私 2对公 默认对公
        if($pay_way == 1){
            $merchant_id = youxi9_merchant_id_P;
            $key = youxi9_key_P;
        }else{
            $merchant_id = youxi9_merchant_id;
            $key = youxi9_key;
        }
        $url = "http://down2.utoozs.com/Card/";
        $youxi9['MerchantID'] = $merchant_id;
        $youxi9['Sign'] = strtolower(md5($merchant_id.$key));
        $data = json_encode($youxi9);
        $result = $this->request($url,$data);
        $this->err_log(var_export($result, 1), "youxi9_merchant");
        $result = json_decode(trim($result,chr(239).chr(187).chr(191)),true);
        return $result;
    }

    public function get_order($order_id,$params){
        $url = "http://down2.utoozs.com/Card/order.php/QB";
        //1对私 2对公 默认对公
        if($params['pay_way'] == 1){
            $merchant_id = youxi9_merchant_id_P;
            $key = youxi9_key_P;
        }else{
            $merchant_id = youxi9_merchant_id;
            $key = youxi9_key;
        }
        $youxi9 = array(
            'orderId'=>$order_id,
            'MerchantID'=>$merchant_id
        );
        $youxi9['Sign'] = strtolower(md5($order_id.$key));
        $data = json_encode($youxi9);
        $result = $this->request($url,$data);
        $this->err_log(var_export($result, 1), "youxi9_order");
        $result = json_decode(trim($result,chr(239).chr(187).chr(191)),true);
        return $result;
    }

    public function get_price($pay_way){
        $url = "http://down2.utoozs.com/Card/Goods/QueryProduct";
        //1对私 2对公 默认对公
        if($pay_way == 1){
            $merchant_id = youxi9_merchant_id_P;
            $key = youxi9_key_P;
        }else{
            $merchant_id = youxi9_merchant_id;
            $key = youxi9_key;
        }
        $youxi9 = array(
            'MerchantID' => $merchant_id,
            'goodsId' => $this->goods_id,
            'payway' => $pay_way,
        );
        $youxi9['Sign'] = strtolower(md5($merchant_id.$key));
        $data = base64_encode(json_encode($youxi9));
        $result = $this->request($url,$data);
        $this->err_log(var_export($result, 1), "youxi9_price");
        $result = json_decode(trim($result,chr(239).chr(187).chr(191)),true);
        return $result;
    }

    public function export(){
        set_time_limit(0);
        $params = $_GET;
        $dataList  = $this->DAO->get_list_all($params);
        if($dataList){
            $this->youxi9_excel_out($dataList,$params['pay_way']);
        }else{
            echo "没有数据需要导出！";
        }
    }

    private function youxi9_excel_out($data,$pay_way){
        set_time_limit(0);
        $str_now=date("Y-m-d H:i:s",strtotime("now"));
        $objExcel = new PHPExcel();
        $objExcel->setActiveSheetIndex(0);
        $objActSheet = $objExcel->getActiveSheet();
        $objActSheet->getColumnDimension('A')->setWidth(26);
        $objActSheet->getColumnDimension('G')->setWidth(20);
        $objActSheet->getColumnDimension('H')->setWidth(20);
        if($pay_way == 2){
            $title = "游戏久Q币数据-对公";
        }else{
            $title = "游戏久Q币数据";
        }
        $objActSheet->setTitle($title);
        $objActSheet->setCellValue("A1", "订单号");
        $objActSheet->setCellValue("B1", "购买数量");
        $objActSheet->setCellValue("C1", "价格");
        $objActSheet->setCellValue("D1", "qq号");
        $objActSheet->setCellValue("E1", "状态");
        $objActSheet->setCellValue("F1","游戏");
        $objActSheet->setCellValue("G1", "下单时间");
        $objActSheet->setCellValue("H1", "回单时间");
        $objActSheet->setCellValue("I1", "平台订单号");
        $n = 2;
        foreach($data as $info){
            $objActSheet->setCellValueExplicit("A".$n,$info['order_id'],PHPExcel_Cell_DataType::TYPE_STRING);
            $objActSheet->setCellValueExplicit("B".$n,$info['amount'],PHPExcel_Cell_DataType::TYPE_STRING);
            $objActSheet->setCellValueExplicit("C".$n,$info['price'],PHPExcel_Cell_DataType::TYPE_STRING);
            $objActSheet->setCellValueExplicit("D".$n,$info['qq'],PHPExcel_Cell_DataType::TYPE_STRING);
            if($info['status']==1){
                $objActSheet->setCellValue("E".$n, "充值中");
            }elseif($info['status']==2){
                $objActSheet->setCellValue("E".$n, "已成功");
            }elseif($info['status']==3){
                $objActSheet->setCellValue("E".$n, "净蓝充值失败");
            }
            if($info['game_id']==0){
                $objActSheet->setCellValue("F".$n, "魔域口袋版（网龙）");
            }elseif($info['game_id']==1){
                $objActSheet->setCellValue("F".$n, "问道");
            }elseif ($info['game_id']==2){
                $objActSheet->setCellValue("F".$n, "魔域手游（西山居）");
            }
            $objActSheet->setCellValue("G".$n, date("Y-m-d H:i:s",$info['add_time']));
            if ($info['callback_time']){
                $objActSheet->setCellValue("H".$n, date("Y-m-d H:i:s",$info['callback_time']));
            }else{
                $objActSheet->setCellValue("H".$n, '');
            }
            $objActSheet->setCellValueExplicit("I".$n,$info['merchant_order_id'],PHPExcel_Cell_DataType::TYPE_STRING);
            $n++;
        }
        $title = iconv("UTF-8", "GB2312//IGNORE",$title."-".$str_now.'.xls');
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

    public function private_list(){
        $params = $this->get_params($_POST,$_GET);
        $data_list = $this->DAO->get_youxi9_list($params,$this->page,1);
        $operation_list = $this->DAO->get_operation_name();
        $page = $this->pageshow($this->page, "youxi9.php?act=private_list&");
        $this->assign("operation_list",$operation_list);
        $this->assign("params",$params);
        $this->assign("page_bar",$page->show());
        $this->assign('data_list',$data_list);
        $this->display("youxi9_private_list.html");
    }

    public function status_edit($id,$ch){
        $info = $this->DAO->get_qb_info($id,$ch);
        if(!$info){
            die("订单出错啦");
        }
        $this->page_hash();
        $this->assign('info',$info);
        $this->assign('ch',$ch);
        $this->display("qb_status_edit.html");
    }

    public function do_status(){
        $params = $_POST;
        if($params['pagehash']!= $_SESSION['page-hash']){
            $this->error_msg("参数异常!  001");
        }
        $info = $this->DAO->get_qb_info($params['id'],$params['ch']);
        if($info['status'] != 1){
            $this->error_msg("该状态不可重新修改");
        }
        if(!$params['status_after'] || !$params['pay_pwd'] || !$params['merchant_order_id']){
            $this->error_msg("缺少必填项");
        }
        $user_info = $this->DAO->get_user_info($_SESSION['usr_id']);
        if(md5($params['pay_pwd']) != $user_info['pay_pwd']){
            $this->error_msg("支付密码错误");
        }
        $order_info = $this->DAO->get_order_info($params['merchant_order_id']);
        if($order_info && $order_info['id'] != $params['id']){
            $this->error_msg("平台交易订单重复，请检查仔细再修改");
        }
        $this->DAO->update_qb_status($params);
        $this->DAO->insert_operation_log($params,$_SESSION['usr_id']);
        $this->succeed_msg();
    }

    public function import_view(){
        $this->display("youxi9_import_view.html");
    }

    public function do_import(){
        if(isset($_FILES['order_file'])){
            $service_file = $_FILES['order_file'];
            if(preg_match("/\.xls$/", $service_file['name'])){
                $type = 1;
                $temp = dirname($service_file['tmp_name']) . '/temp_test.xls';
            }elseif (preg_match("/\.xlsx$/", $service_file['name'])){
                $type = 2;
                $temp = dirname($service_file['tmp_name']) . '/temp_test.xlsx';
            }else{
                $this->error_msg("上传文件后缀必须是xls或者xlsx！");
            }
            if(move_uploaded_file($service_file['tmp_name'],$temp)){
                if(file_exists($temp)){
                    //执行数据解析
                    $data_arr = array(
                        array("title_name" => "订单号", "title_field" => "merchant_order_id"),
                        array("title_name" => "充值账号", "title_field" => "qq"),
                        array("title_name" => "时间", "title_field" => "add_time"),
                        array("title_name" => "面额", "title_field" => "amount"),
                        array("title_name" => "变动前金额", "title_field" => "money_before"),
                        array("title_name" => "变动金额", "title_field" => "money"),
                        array("title_name" => "变动后金额", "title_field" => "money_after"),
                        array("title_name" => "支付方式", "title_field" => "pay_way"),
                        array("title_name" => "端", "title_field" => "from"),
                        array("title_name" => "收入/支出", "title_field" => "money_status"),
                        array("title_name" => "说明", "title_field" => "msg"),
                        array("title_name" => "状态", "title_field" => "status"),
                    );
                }
            }
            $order_data = $this->excel_import_data($temp,$data_arr,$type);
            unlink($temp);
            foreach($order_data as $key=>$value){
                $value['merchant_order_id'] = rtrim(ltrim($value['merchant_order_id'],'="'),'"');
                if($value['pay_way'] == '久币'){
                    $value['pay_way'] = 1;
                }elseif($value['pay_way'] == '商务币'){
                    $value['pay_way'] = 2;
                }
                if($value['from'] == 'PC'){
                    $value['from'] = 1;
                }elseif($value['from'] == 'API'){
                    $value['from'] = 2;
                }
                if($value['money_status'] == '支出'){
                    $value['money_status'] = 1;
                }elseif($value['money_status'] == '收入'){
                    $value['money_status'] = 2;
                }
                if($value['status'] == '处理中'){
                    $value['status'] = 1;
                }elseif($value['status'] == '处理成功'){
                    $value['status'] = 2;
                }elseif($value['status'] == '处理失败'){
                    $value['status'] = 3;
                }
                $value['price'] = $value['money']/$value['amount'];
                $value['order_id'] = $this->orderid('9090');
                $info = $this->DAO->get_order_info($value['merchant_order_id']);
                if(!$info){
                    $this->DAO->insert_youxi9_order($value,$_SESSION['usr_id']);
                }elseif($info['status'] != $value['status']){
                    $this->DAO->update_youxi9_status($value);
                }
            }
        }
        $this->succeed_msg();
    }
}