<?php
COMMON('adminBaseCore','pageCore','luoke_qb/services','luoke_qb/luoke_services');
DAO('luoke_admin_dao');

class luoke_admin extends adminBaseCore{

    public $DAO;
    public $id;

    public function __construct(){
        parent::__construct();
        $this->DAO = new luoke_admin_dao();
    }

    public function luoke_list_view(){
        if($_POST){
            $_SESSION['luoke_list'] = $params = $_POST;
        }elseif(!$_GET['page']){
            unset($_SESSION['luoke_list']);
        }else{
            $params = $_SESSION['luoke_list'];
        }
        $list = $this->DAO->list_data($this->page,$params);
        $page = $this->pageshow($this->page, "luoke.php?act=list&");
        $this->assign("params",$params);
        $this->assign("datalist", $list);
        $this->assign("page_bar", $page->show());
        $this->display("luoke_list.html");
    }

    public function luoke_b_list_view(){
        if($_POST){
            $_SESSION['luoke_b_list'] = $params = $_POST;
        }elseif(!$_GET['page']){
            unset($_SESSION['luoke_b_list']);
        }else{
            $params = $_SESSION['luoke_b_list'];
        }
        $list = $this->DAO->list_data($this->page,$params);
        $page = $this->pageshow($this->page, "luoke.php?act=b_list&");
        $this->assign("params",$params);
        $this->assign("datalist", $list);
        $this->assign("page_bar", $page->show());
        $this->display("luoke_b_list.html");
    }


    public function luoke_add_view(){
        $luoke_res = $this->get_luoke_items();
        if ($luoke_res->code === 0){
            $items_list = json_decode($luoke_res->items,true);
            if (!empty($items_list)){
                foreach ($items_list as $value){
                    $_SESSION['luoke'][$value['item_id']] = $value['item_price'];
                }
                $this->assign('items_list',$items_list);
            }
        }
        $this->page_hash();
        $this->display("luoke_add.html");
    }

    public function b_add_view(){
        $luoke_res = $this->get_b_luoke_items();
        if ($luoke_res->code === 0){
            $items_list = json_decode($luoke_res->items,true);
            if (!empty($items_list)){
                foreach ($items_list as $value){
                    $_SESSION['luoke_b'][$value['item_id']] = $value['item_price'];
                }
                $this->assign('items_list',$items_list);
            }
        }
        $this->page_hash();
        $this->display("luoke_b_add.html");
    }

    public function do_add(){
        if($_POST['pagehash']!= $_SESSION['page-hash'] || !$_POST['qq']){
            $this->error_msg("缺少必要参数");
        }
        if (!isset($_POST['game_id']) || $_POST['game_id'] === ''){
            $this->error_msg("没有选择游戏");
        }
        if (!isset($_POST['items_id']) || $_POST['items_id'] === ''){
            $this->error_msg("没有选择商品");
        }
        if($_POST['qq']!=$_POST['qq_again']){
            $this->error_msg("俩次qq号不一致");
        }
        if (!preg_match('/^[1-9][0-9]*$/',$_POST['amount'])){
            $this->error_msg("商品数量只能是整数");
        }
        $order_id = $this->orderid('8080');
        $item_id = $_POST['items_id'];
        $game_id = $_POST['game_id'];
        $amount = $_POST['amount'];
        $price = $_SESSION['luoke'][$item_id];
        if (!$price){
            $this->error_msg("商品价格出错");
        }
        if((int)$amount*(float)$price>2000){
            $this->error_msg("金额超标了");
        }
        $qq = $_POST['qq'];
        //yyyy-MM-dd HH:mm:ss
        $url = "http://chong.loke123.com/merchantgame/chargeQbi";
        $luoke = array(
            "timestamp" => date("YmdHis",time()),
            "format" => 'Json',
            "version" => '1.0',
            "game_name" => 'Q币(票)',
            "client_ip"=>$this->client_ip(),
            "merchant_id" => luoke_merchant_id,
            "item_id" => $item_id,
            "merchant_order_no" => $order_id,
            "buy_num" => $amount,
            "game_account" => $qq,
            "notify_url" => luoke_NOTIFY_URL,
        );
        ksort($luoke);
        $sign_str = urldecode(http_build_query($luoke));
        $sign = md5($sign_str.luoke_key);
        $luoke['sign'] = $sign;

        $result = $this->request($url."?".http_build_query($luoke));
        $result = json_decode($result);
        //增加http状态
        $result->http_status = $this->curl_status;
        $this->err_log(var_export($result, 1), "luoke_result");
        if (1000 == $result->code || '200' != $result->http_status){
            //查询订单接口
            $luoke_obj = new services();
            $order_query_res = $luoke_obj->queryOrder($order_id);
            if (0 === $order_query_res['code'] && $order_query_res['merchant_order_no'] == $order_id && $order_query_res['order_status']){
                if (1 == $order_query_res['order_status']){
                    $insert_status = 0;
                }elseif (3 == $order_query_res['order_status']){
                    $insert_status = 1;
                }else{
                    $insert_status = 4;
                }
                $info_id = $this->DAO->insert_order_by_query($order_id, $item_id, $amount, $price, $qq, $order_query_res['order_id'],$game_id,$insert_status);
                if(!$info_id){
                    $this->error_msg("插入数据库错误,请报告技术");
                }
            }else{
                $this->err_log(var_export($order_query_res, 1), "luoke_query_result");
                $this->error_msg("请求供应商出错【".$result->code."==>".$result->msg."】");
            }
        }elseif (!$result->order_status || !$result->order_id){
            $this->error_msg("请求供应商出错【".$result->code."==>".$result->msg."】");
        }else{
            $luoke_order_id = $result->order_id;
            $info_id = $this->DAO->insert_order($order_id, $item_id, $amount, $price, $qq, $luoke_order_id,$game_id);
            if(!$info_id){
                $this->error_msg("插入数据库错误,请报告技术");
            }
        }
        $this->succeed_msg();
    }

    public function do_b_add(){
        if($_POST['pagehash']!= $_SESSION['page-hash'] || !$_POST['qq']){
            $this->error_msg("缺少必要参数");
        }
        if (!isset($_POST['game_id']) || $_POST['game_id'] === ''){
            $this->error_msg("没有选择游戏");
        }
        if (!isset($_POST['items_id']) || $_POST['items_id'] === ''){
            $this->error_msg("没有选择商品");
        }
        if($_POST['qq']!=$_POST['qq_again']){
            $this->error_msg("俩次qq号不一致");
        }
        if (!preg_match('/^[1-9][0-9]*$/',$_POST['amount'])){
            $this->error_msg("商品数量只能是整数");
        }
        $order_id = $this->orderid('8080');
        $item_id = $_POST['items_id'];
        $game_id = $_POST['game_id'];
        $amount = $_POST['amount'];
        $price = $_SESSION['luoke_b'][$item_id];
        if (!$price){
            $this->error_msg("商品价格出错");
        }
        if((int)$amount*(float)$price>2000){
            $this->error_msg("金额超标了");
        }
        $qq = $_POST['qq'];
        //yyyy-MM-dd HH:mm:ss
        $url = "http://chong.loke123.com/merchantgame/chargeQbi";
        $luoke = array(
            "timestamp" => date("YmdHis",time()),
            "format" => 'Json',
            "version" => '1.0',
            "game_name" => 'Q币(票)',
            "client_ip"=>$this->client_ip(),
            "merchant_id" => b_luoke_merchant_id,
            "item_id" => $item_id,
            "merchant_order_no" => $order_id,
            "buy_num" => $amount,
            "game_account" => $qq,
            "notify_url" => b_luoke_NOTIFY_URL,
        );
        ksort($luoke);
        $sign_str = urldecode(http_build_query($luoke));
        $sign = md5($sign_str.b_luoke_key);
        $luoke['sign'] = $sign;

        $result = $this->request($url."?".http_build_query($luoke));
        $result = json_decode($result);
        //增加http状态
        $result->http_status = $this->curl_status;
        $this->err_log(var_export($result, 1), "luoke_b_result");
        if (1000 == $result->code || '200' != $result->http_status){
            //查询订单接口
            $luoke_obj = new luoke_services();
            $order_query_res = $luoke_obj->queryOrder($order_id);
            if (0 === $order_query_res['code'] && $order_query_res['merchant_order_no'] == $order_id && $order_query_res['order_status']){
                if (1 == $order_query_res['order_status']){
                    $insert_status = 0;
                }elseif (3 == $order_query_res['order_status']){
                    $insert_status = 1;
                }else{
                    $insert_status = 4;
                }
                $info_id = $this->DAO->insert_order_by_luokeB($order_id, $item_id, $amount, $price, $qq, $order_query_res['order_id'],$game_id,$insert_status);
                if(!$info_id){
                    $this->error_msg("插入数据库错误,请报告技术");
                }
            }else{
                $this->err_log(var_export($order_query_res, 1), "luoke_b_query_result");
                $this->error_msg("请求供应商出错【".$result->code."==>".$result->msg."】");
            }
        }elseif (!$result->order_status || !$result->order_id){
            $this->error_msg("请求供应商出错【".$result->code."==>".$result->msg."】");
        }else{
            $luoke_order_id = $result->order_id;
            $info_id = $this->DAO->insert_luokeB_order($order_id, $item_id, $amount, $price, $qq, $luoke_order_id,$game_id);
            if(!$info_id){
                $this->error_msg("插入数据库错误,请报告技术");
            }
        }
        $this->succeed_msg();
    }


    public function get_b_luoke_items(){
        $url = "http://chong.loke123.com/merchantgame/queryItems";
        $luoke = array(
            "timestamp" => date("YmdHis",time()),
            "format" => 'Json',
            "version" => '1.0',
            "game_name" => 'Q币',
            "merchant_id" => b_luoke_merchant_id,
        );
        ksort($luoke);
        $sign_str = urldecode(http_build_query($luoke));
        $sign = md5($sign_str.b_luoke_key);
        $luoke['sign'] = $sign;
        $result = $this->request($url."?".http_build_query($luoke));
        $result = json_decode($result);
        return $result;
    }

    public function get_luoke_items(){
        $url = "http://chong.loke123.com/merchantgame/queryItems";
        $luoke = array(
            "timestamp" => date("YmdHis",time()),
            "format" => 'Json',
            "version" => '1.0',
            "game_name" => 'Q币(票)',
            "merchant_id" => luoke_merchant_id,
        );
        ksort($luoke);
        $sign_str = urldecode(http_build_query($luoke));
        $sign = md5($sign_str.luoke_key);
        $luoke['sign'] = $sign;
        $result = $this->request($url."?".http_build_query($luoke));
        $result = json_decode($result);
        return $result;
    }

    public function query_balance(){
        $luoke_obj = new services();
        $balance = $luoke_obj->queryBalance();
        if ($balance['code'] === 0){
            $this->assign('balance',$balance);
        }else{
            //接口异常
            die("余额接口异常：".$balance['msg']);
        }
        $start_time = date('Y-m-d',strtotime('-1 day'));
        $this->assign('start_time',$start_time);
        $this->display('query_balance_luoke.html');
    }

    public function query_recharge(){
        if ($_POST['start_time']){
            $luoke_obj = new services();
            $recharge_list = $luoke_obj->queryRechargeList($_POST['start_time'],date('Y-m-d',strtotime($_POST['start_time'])+24*3600));
            if ($recharge_list['code'] !== 0){
                $this->error_msg("获取充值列表接口失败：".$recharge_list['msg']);
            }
            $daily_trade = $luoke_obj->queryDailyTrade($_POST['start_time']);
            if ($daily_trade['code'] === 0){
                $recharge_list['total_price'] = $daily_trade['total_price'];
                $recharge_list['order_num'] = $daily_trade['order_num'];
            }else{
                $recharge_list['total_price'] = 0;
                $recharge_list['order_num'] = 0;
            }
            $this->succeed_msg($recharge_list);
        }else{
            $this->error_msg("查询日期不能为空");
        }
    }

    public function luoke_export(){
        set_time_limit(0);
        $params = $_GET;
        $dataList  = $this->DAO->get_luokeB_list_all($params);
        if($dataList){
            $this->luoke_excel_out($dataList);
        }else{
            echo "没有数据需要导出！";
        }
    }

    public function luoke_b_export(){
        set_time_limit(0);
        $params = $_GET;
        $dataList  = $this->DAO->get_list_all($params);
        if($dataList){
            $this->luoke_excel_out($dataList);
        }else{
            echo "没有数据需要导出！";
        }
    }

    private function luoke_excel_out($data){
        set_time_limit(0);
        $str_now=date("Y-m-d H:i:s",strtotime("now"));
        $objExcel = new PHPExcel();
        $objExcel->setActiveSheetIndex(0);
        $objActSheet = $objExcel->getActiveSheet();
        $objActSheet->setTitle("洛克Q币数据");
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
            $objActSheet->setCellValueExplicit("D".$n,$info['purchase_price'],PHPExcel_Cell_DataType::TYPE_STRING);
            $objActSheet->setCellValueExplicit("E".$n,$info['qq'],PHPExcel_Cell_DataType::TYPE_STRING);
            if ($info['status']==0){
                $objActSheet->setCellValue("F".$n, "未完成");
            }elseif($info['status']==1){
                $objActSheet->setCellValue("F".$n, "已完成");
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
        $title = iconv("UTF-8", "GB2312//IGNORE","洛克Q币数据-".$str_now.'.xls');
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