<?php
COMMON('adminBaseCore','pageCore');
DAO('yzm_admin_dao');

class yzm_admin extends adminBaseCore{
    public $DAO;
    public $id;

    public function __construct(){
        parent::__construct();
        $this->DAO = new yzm_admin_dao();
    }

    public function list_view(){
        $params = $this->get_params($_POST,$_GET);
        $list = $this->DAO->list_data($this->page,$params,1);
        $page = $this->pageshow($this->page, "yunzhimeng.php?act=list&");
        $this->assign("params",$params);
        $this->assign("datalist", $list);
        $this->assign("page_bar", $page->show());
        $this->display('yzm_list.html');
    }

    public function add_view($type=''){
//        $products = $this->request("http://www.pvm.com.cn/Api/QueryAllProduct");
//        $products = (array)simplexml_load_string($products, 'SimpleXMLElement', LIBXML_NOCDATA);
//        $QBs = array();
//        foreach($products['product'] as $k => $v){
//            if($v->id == '10106'){
//                $qb = $this->object_to_array($v);
//                $QBs[$qb['id']] = $qb;
//            }
//        }
//        $_SESSION['QBS'] = $QBs;
        $this->page_hash();
        $this->assign("type", $type);
        $this->display('yzm_add.html');
    }

    public function do_add(){
        $params = $_POST;
        if($params['pagehash']!= $_SESSION['page-hash'] || !$params['qq'] || !$params['code']){
            $this->error_msg("缺少必要参数");
        }
        if(!isset($params['game_id']) || $params['game_id']===""){
            $this->error_msg("必须选择游戏");
        }
        if($params['qq']!=$params['qq_again']){
            $this->error_msg("俩次qq号不一致");
        }
        if(strlen($params['qq']) < 5){
            $this->error_msg("QQ号位数不得小于五位数");
        }
        if($params['amount']>5000){
            $this->error_msg("金额超标了");
        }
        if(!preg_match('/^[1-9][0-9]*$/',$params['num'])){
            $this->error_msg("采购次数只能是整数");
        }
//        if(empty($params['code']) || strtoupper($params['code']) != $_SESSION['c']){
//            $this->error_msg("图形验证码错误");
//        }
        if(!isset($params['pay_pwd'])){
            $this->error_msg("支付密码不能为空");
        }else{
            $pay_pwd = md5($params['pay_pwd']);
            $user_info = $this->DAO->get_user_info($_SESSION['usr_id']);
            if($pay_pwd != $user_info['pay_pwd']){
                $this->error_msg("支付密码错误，请重新输入");
            }
        }
        $qb = $_SESSION['QBS'][$params['product_id']];
        //今日订单总数
        $start_time = strtotime(date('Y-m-d',time()));
        $order = $this->DAO->get_order_amount($params,$start_time);
        if($order['amount'] >= 10000){
            $this->error_msg('该QQ号今日购买数量已达到上限10000个');
        }elseif(($params['amount']*$params['num']+$order['amount'])>10000){
            $this->error_msg('您提交订单的购买数量已超过上限10000个，请重新提交');
        }
        $merchant = $this->get_merchant();
        if((int)$params['amount']*(float)$qb['price']*(int)$params['num']>$merchant['balance']){
            $this->error_msg("账户余额不足");
        }
        if($params['num'] == 1){
            $this->insert_order($params,$qb['price']);
        }else{
            for($i=1;$i<=$params['num'];$i++){
                $this->insert_order($params,$qb['price'],$i);
            }
        }
        $this->succeed_msg();
    }

    public function insert_order($params,$price,$i=''){
        $url = "http://www.pvm.com.cn/Api/Pay";
        $order_id = $this->orderid(8888);
        $yzm = array(
            'MerchantID'=>YZM_merchant_id,
            'MerchantOrderID'=>$order_id,
            'ProductID'=>$params['product_id'],
            'BuyAmount'=>$params['amount'],
            'TargetAccount'=>$params['qq'],
            'ResponseUrl'=>YZM_notify_url,
        );
        $yzm['Sign'] = md5(YZM_merchant_id.$order_id.$params['product_id'].$params['amount'].$params['qq'].YZM_key);
        ksort($yzm);
        $result = $this->request($url,$yzm);
        $result = json_decode(json_encode(simplexml_load_string($result, 'SimpleXMLElement', LIBXML_NOCDATA)), true);
        //增加http状态
        $result['http_status'] = $this->curl_status;
        $this->err_log(var_export($result, 1), "yunzhimeng_result");
        if($result['state'] == 101 || $result['http_status'] != '200'){
            //查询订单接口
            $order_query_res = $this->get_order($order_id);
            if($order_query_res['state'] === 101 && $order_query_res['merchant-order-id'] == $order_id && $order_query_res['order_status']){
                if($order_query_res['state'] == 101){
                    $insert_status = 1;
                }elseif($order_query_res['state'] == 102){
                    $insert_status = 0;
                }elseif($order_query_res['state'] == 103){
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
                $this->err_log(var_export($order_query_res, 1), "yunzhimeng_query_result");
                if($i){
                    $this->error_msg("请求供应商出错【".$result['state']."==>".$result['state-info']."】,已成功插入".$i."条记录");
                }else{
                    $this->error_msg("请求供应商出错【".$result['state']."==>".$result['state-info']."】");
                }
            }
        }elseif(!$result['state'] || !$result['order-id']){
            if($i){
                $this->error_msg("请求供应商出错【".$result['state']."==>".$result['state-info']."】,已成功插入".$i."条记录");
            }else{
                $this->error_msg("请求供应商出错【".$result['state']."==>".$result['state-info']."】");
            }
        }else{
            $info_id = $this->DAO->insert_order($order_id, $params, $price,$result['order-id']);
            if(!$info_id){
                if($i){
                    $this->error_msg("插入数据库错误,请报告技术,已成功插入".$i."条记录");
                }else{
                    $this->error_msg("插入数据库错误,请报告技术");
                }
            }
        }
    }

    //查询商户信息
    public function get_merchant(){
        $url = "http://www.pvm.com.cn/Api/QueryMerchant";
        $yzm = array(
            "MerchantID" => YZM_merchant_id,
        );
        ksort($yzm);
        $yzm['Sign'] = md5(YZM_merchant_id.YZM_key);
        $result = $this->request($url,$yzm);
        //将返回的xml转换成数组
        $result = json_decode(json_encode(simplexml_load_string($result, 'SimpleXMLElement', LIBXML_NOCDATA)), true);
        return $result;
    }

    //查询订单
    public function get_order($order_id){
        $url = 'http://www.pvm.com.cn/Api/QueryOrder';
        $data = array(
            'MerchantID'=>YZM_merchant_id,
            'MerchantOrderID'=>$order_id
        );
        $data['Sign'] = md5(YZM_merchant_id.$order_id.YZM_key);
        $result = $this->request($url,$data);
        //将返回的xml转换成数组
        $result = json_decode(json_encode(simplexml_load_string($result, 'SimpleXMLElement', LIBXML_NOCDATA)), true);
        return $result;
    }

    public function export(){
        set_time_limit(0);
        $params = $_GET;
        $dataList  = $this->DAO->get_list_all($params);
        if($dataList){
            $this->master_excel_out($dataList,$params['type']);
        }else{
            echo "没有数据需要导出！";
        }
    }

    private function master_excel_out($data,$type=''){
        set_time_limit(0);
        $str_now=date("Y-m-d H:i:s",strtotime("now"));
        $objExcel = new PHPExcel();
        $objExcel->setActiveSheetIndex(0);
        $objActSheet = $objExcel->getActiveSheet();
        if($type == 2){
            $title = "云之盟Q币数据";
        }else{
            $title = "云之盟Q币数据-对公";
        }
        $objActSheet->setTitle($title);
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
            if($info['status']==0){
                $objActSheet->setCellValue("F".$n, "未完成");
            }elseif($info['status']==1){
                $objActSheet->setCellValue("F".$n, "已完成");
            }elseif($info['status']==2){
                $objActSheet->setCellValue("F".$n, "可疑订单");
            }elseif ($info['status']==4){
                $objActSheet->setCellValue("F".$n, "充值失败");
            }
            if($info['game_id']==0){
                $objActSheet->setCellValue("G".$n, "魔域口袋版（网龙）");
            }elseif($info['game_id']==1){
                $objActSheet->setCellValue("G".$n, "问道");
            }elseif ($info['game_id']==2){
                $objActSheet->setCellValue("G".$n, "魔域手游（西山居）");
            }
            $objActSheet->setCellValue("H".$n, date("Y-m-d H:i:s",$info['add_time']));
            if($info['callback_time']){
                $objActSheet->setCellValue("I".$n, date("Y-m-d H:i:s",$info['callback_time']));
            }else{
                $objActSheet->setCellValue("I".$n, '');
            }
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
        $list = $this->DAO->list_data($this->page,$params,2);
        $page = $this->pageshow($this->page, "yunzhimeng.php?act=private_list&");
        $this->assign("params",$params);
        $this->assign("datalist", $list);
        $this->assign("page_bar", $page->show());
        $this->display("yzm_private_list.html");
    }

    public function do_add_private(){
        $params = $_POST;
        if($params['pagehash']!= $_SESSION['page-hash'] || !$params['qq'] || !$params['code']){
            $this->error_msg("缺少必要参数");
        }
        if(!isset($params['game_id']) || $params['game_id']===""){
            $this->error_msg("必须选择游戏");
        }
        if($params['qq']!=$params['qq_again']){
            $this->error_msg("俩次qq号不一致");
        }
        if(strlen($params['qq']) < 5){
            $this->error_msg("QQ号位数不得小于五位数");
        }
        if($params['amount']>5000){
            $this->error_msg("金额超标了");
        }
        if(!preg_match('/^[1-9][0-9]*$/',$params['num'])){
            $this->error_msg("采购次数只能是整数");
        }
        if(!isset($params['pay_pwd'])){
            $this->error_msg("支付密码不能为空");
        }else{
            $pay_pwd = md5($params['pay_pwd']);
            $user_info = $this->DAO->get_user_info($_SESSION['usr_id']);
            if($pay_pwd != $user_info['pay_pwd']){
                $this->error_msg("支付密码错误，请重新输入");
            }
        }
        //商品ID，1对公：40000 2对私：40001
        $params['product_id'] = $params['type']?40001:40000;
        $params['type'] = $params['type']?$params['type']:1;
        $product = $this->get_product($params['product_id'],$params['type']);
        $price = $product['ResContent'][0]['price'];
//        $price = $params['type']==2?0.935:0.985;  //暂时写固定值
        //今日订单总数
        $start_time = strtotime(date('Y-m-d',time()));
        $order = $this->DAO->get_order_amount($params,$start_time);
        if($order['amount'] >= 10000){
            $this->error_msg('该QQ号今日购买数量已达到上限10000个');
        }elseif(($params['amount']*$params['num']+$order['amount'])>10000){
            $this->error_msg('您提交订单的购买数量已超过上限10000个，请重新提交');
        }
        //对方接口未完善，暂时不查余额
        $merchant = $this->get_private_merchant($params['type']);
        if((int)$params['amount']*(float)$price*(int)$params['num']>$merchant['ResContent']){
            $this->error_msg("账户余额不足");
        }
        if($params['num'] == 1){
            $this->insert_private_order($params,$price);
        }else{
            for($i=1;$i<=$params['num'];$i++){
                $this->insert_private_order($params,$price,$i);
            }
        }
        $this->succeed_msg();
    }

    public function insert_private_order($params,$price,$i=''){
        $url = "http://api.yunka1688.com.cn/GameOrder/Send";
        $order_id = $this->orderid(8888);
        if($params['type'] == 1){
            $merchant_id = YZM_merchant_id;
            $key = YZM_key;
        }else{
            $merchant_id = YZM_merchant_id_P;
            $key = YZM_key_P;
        }
        $yzm = array(
            'account'=>$params['qq'],
            'billno'=>$order_id,
            'number'=>$params['amount'],
            'productid'=>$params['product_id'],
            'time'=>time(),
            'userid'=>$merchant_id,
        );
        $str = '';
        foreach($yzm as $k=>$d){
            $str .= $k.'='.$d.'&';
        }
        $sign = strtoupper(md5($str.'userkey='.$key));
        $str .= 'sign='.$sign.'&version=2.0';
        $result = $this->request($url,$str);
        $result = json_decode($result,true);
        //增加http状态
        $result['http_status'] = $this->curl_status;
        $this->err_log(var_export($result, 1), "yunzhimeng_result");
        if($result['ResCode'] == 1 || $result['http_status'] == '200'){
            //查询订单接口
            $order_query_res = $this->get_private_order($order_id,$params['type']);
            if($order_query_res['ResCode'] === 1 && $result['ResContent']['orderSn']){
                $info_id = $this->DAO->insert_order_by_query($order_id, $params, $price,$result['ResContent']['orderSn'],$order_query_res['ResContent']['orderStatus']);
                if(!$info_id){
                    if($i){
                        $this->error_msg("插入数据库错误,请报告技术,已成功插入".$i."条记录");
                    }else{
                        $this->error_msg("插入数据库错误,请报告技术");
                    }
                }
            }else{
                $this->err_log(var_export($order_query_res, 1), "yunzhimeng_query_result");
                $this->DAO->insert_order($order_id, $params, $price,$result['ResContent']['orderSn']);
                if($i){
                    $this->error_msg("请求供应商出错【".$result['ResCode']."==>".$result['ResMes']."】,已成功插入".$i."条记录");
                }else{
                    $this->error_msg("请求供应商出错【".$result['ResCode']."==>".$result['ResMes']."】");
                }
            }
        }elseif(!$result['ResCode'] || !$result['ResContent']['orderSn']){
            $this->err_log(var_export($result, 1), "yunzhimeng_query_result");
            if($i){
                $this->error_msg("请求供应商出错【".$result['ResCode']."==>".$result['ResMes']."】,已成功插入".$i."条记录");
            }else{
                $this->error_msg("请求供应商出错【".$result['ResCode']."==>".$result['ResMes']."】");
            }
        }else{
            $info_id = $this->DAO->insert_order($order_id, $params, $price,$result['ResContent']['orderSn']);
            if(!$info_id){
                if($i){
                    $this->error_msg("插入数据库错误,请报告技术,已成功插入".$i."条记录");
                }else{
                    $this->error_msg("插入数据库错误,请报告技术");
                }
            }
        }
    }

    public function get_private_order($order_id,$type){
        $url = "http://api.yunka1688.com.cn/Gameorder/Query";
        if($type == 1){
            $merchant_id = YZM_merchant_id;
            $key = YZM_key;
        }else{
            $merchant_id = YZM_merchant_id_P;
            $key = YZM_key_P;
        }
        $yzm = array(
            'billno'=>$order_id,
            'time'=>time(),
            'userid'=>$merchant_id
        );
        $str = '';
        foreach($yzm as $k=>$d){
            $str .= $k.'='.$d.'&';
        }
        $sign = strtoupper(md5($str.'userkey='.$key));
        $str .= 'sign='.$sign;
        $result = $this->request($url,$str);
        $result = json_decode($result,true);
        return $result;
    }

    public function get_product($product_id,$type){
        // type 1对公  2对私
        $url = "http://api.yunka1688.com.cn/Gameorder/ProductQuery";
        if($type == 1){
            $merchant_id = YZM_merchant_id;
            $key = YZM_key;
        }else{
            $merchant_id = YZM_merchant_id_P;
            $key = YZM_key_P;
        }
        $yzm = array(
            'productid'=>$product_id,
            'service'=>'QueryProduct',
            'time'=>time(),
            'userid'=>$merchant_id
        );
        $str = '';
        foreach($yzm as $k=>$d){
            $str .= $k.'='.$d.'&';
        }
        $sign = strtoupper(md5($str.'userkey='.$key));
        $str .= 'sign='.$sign;
        $result = $this->request($url,$str);
        $result = json_decode($result,true);
        return $result;
    }

    public function get_private_merchant($type){
        $url = 'http://api.yunka1688.com.cn/Gameorder/BalanceQuery';
        if($type == 1){
            $merchant_id = YZM_merchant_id_P;
            $key = YZM_key_P;
        }else{
            $merchant_id = YZM_merchant_id;
            $key = YZM_key;
        }
        $yzm = array(
            'service'=>'QueryBalance',
            'time'=>time(),
            'userid'=>$merchant_id
        );
        $str = '';
        foreach($yzm as $k=>$d){
            $str .= $k.'='.$d.'&';
        }
        $sign = strtoupper(md5($str.'userkey='.$key));
        $str .= 'sign='.$sign;
        $result = $this->request($url,$str);
        $result = json_decode($result,true);
        return $result;
    }
}