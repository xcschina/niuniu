<?php
COMMON('adminBaseCore','pageCore');
DAO('jinglan_admin_dao');

class jinglan_admin extends adminBaseCore{
    public $DAO;
    public $province;
    public $price;

    public function __construct(){
        parent::__construct();
        $this->DAO = new jinglan_admin_dao();
        $this->province = '四川';
        $this->price = 0.94;
    }

    public function list_view(){
        $params = $this->get_params($_POST,$_GET);
        $list = $this->DAO->list_data($this->page,$params);
        $operation_list = $this->DAO->get_operation_name();
        $page = $this->pageshow($this->page, "jinglan.php?act=list&");
        $this->assign("operation_list",$operation_list);
        $this->assign("params",$params);
        $this->assign("datalist", $list);
        $this->assign("page_bar", $page->show());
        $this->display('jinglan_list.html');
    }

    public function add_view(){
        $this->page_hash();
        $this->display('jinglan_add.html');
    }

    public function do_add(){
        $params = $_POST;
        if($params['pagehash']!= $_SESSION['page-hash'] || !$params['qq'] || !$params['amount']){
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
//        $ip = '218.88.230.217';
//        $result = $this->DAO->get_ip_session($ip);
//        if(!$result){
//            $result = $this->decide_ip($ip);
//            $this->DAO->set_ip_session($ip,$result);
//        }
//        if(!in_array($result['region'],explode(',',$this->province)) || !in_array($result['region'].'省',explode(',',$this->province))){
//            $this->error_msg('你的当前ip不在允许范围');
//        }
        $price = $this->DAO->get_qb_price();
        if($price){
            $params['price'] = $price['price'];
        }else{
            $params['price'] = $this->price;
        }
        //今日订单总数
        $start_time = strtotime(date('Y-m-d 00:00:00',time()));
        $order = $this->DAO->get_order_amount($params['qq'],$start_time);
        if($order['amount'] >= 10000){
            $this->error_msg('该QQ号今日购买数量已达到上限10000个');
        }elseif(($params['amount']*$params['num']+$order['amount'])>10000){
            $this->error_msg('您提交订单的购买数量已超过上限10000个，请重新提交');
        }
        $merchant = $this->get_merchant();
        //账户余额单位是厘，因此需要乘以1000
        if((int)$params['amount']*$params['price']*(int)$params['num']*1000>$merchant['data']['balance']){
            $this->error_msg("账户余额不足");
        }
        if($params['num'] == 1){
            $this->insert_order($params,$ip);
        }else{
            for($i=1;$i<=$params['num'];$i++){
                $this->insert_order($params,$ip,$i);
            }
        }
        $this->succeed_msg();

    }

    //下单接口
    public function insert_order($params,$ip,$i=''){
        $order_id = $this->orderid('6060');
//        $url = 'http://123.56.242.212:25000/mch/placeOrder.do';//测试环境
        $url = 'http://123.56.243.180:25000/mch/placeOrder.do';//正式环境
        $data = array(
            'merAccount'=>JL_merchant_Account,
            'merUserAccount'=>'',
            'businessType'=>10,
            'merOrderNo'=>$order_id,
            'merOrderTime'=>date('Y-m-d H:i:s',time()),
            'rechargeAccount'=>$params['qq'],
            'rechargeValue'=>$params['amount'],
            'rechargeNum'=>1,
            'customerIP'=>$ip,
            'notifyUrl'=>jinglan_NOTIFY_URL
        );
        $jinglan['data'] = json_encode($data);
        $jinglan['sign'] = md5('data='.$jinglan['data'].'&key='.jinglan_key);
        $result = $this->request($url,$jinglan);
        $result = json_decode(json_encode((json_decode($result))),TRUE);
//        $result['data'] = json_decode(json_encode((json_decode($result['data']))),TRUE);
        //增加http状态
        $result['http_status'] = $this->curl_status;
        $this->err_log(var_export($result, 1), "jinglan_result");
        if($result['resultCode'] == '0' || $result['http_status'] == '200'){
            $order_query_res = $this->get_order($order_id,$result['data']['orderNo']);
            if($order_query_res['resultCode'] == '0' && $order_query_res['data']['merOrderNo'] == $order_id){
                if($order_query_res['data']['orderState'] == '0'){
                    $insert_status = 0;
                }elseif($order_query_res['data']['orderState'] == '20'){
                    $insert_status = 1;
                }elseif($order_query_res['data']['orderState'] == '21'){
                    $insert_status = 2;
                }elseif($order_query_res['data']['orderState'] == '24'){
                    $insert_status = 3;
                }elseif($order_query_res['data']['orderState'] == '23'){
                    $insert_status = 4;
                }elseif($order_query_res['data']['orderState'] == '-1'){
                    $insert_status = 5;
                }
                $info_id = $this->DAO->insert_order_by_query($order_id, $params, $order_query_res['data']['orderNo'],$insert_status);
                if(!$info_id){
                    if($i){
                        $this->error_msg("插入数据库错误,请报告技术,已成功插入".$i."条记录");
                    }else{
                        $this->error_msg("插入数据库错误,请报告技术");
                    }
                }
            }else{
                $order_query_res['order_id'] = $order_id;
                $this->err_log(var_export($order_query_res, 1), "jinglan_query_result");
                $this->DAO->insert_order($order_id, $params, $result['data']['orderNo']);
                if($i){
                    $this->error_msg("请求供应商出错【".$result['resultCode']."==>".$result['resultMsg']."】,已成功插入".$i."条记录");
                }else{
                    $this->error_msg("请求供应商出错【".$result['resultCode']."==>".$result['resultMsg']."】");
                }
            }
        }elseif(!isset($result['resultCode']) || !$result['data']['orderNo']){
            $this->err_log(var_export($result, 1), "jinglan_query_result");
            if($i){
                $this->error_msg("请求供应商出错【".$result['resultCode']."==>".$result['resultMsg']."】,已成功插入".$i."条记录");
            }else{
                $this->error_msg("请求供应商出错【".$result['resultCode']."==>".$result['resultMsg']."】");
            }
        }else{
            $info_id = $this->DAO->insert_order($order_id, $params, $result['data']['orderNo']);
            if(!$info_id){
                if($i){
                    $this->error_msg("插入数据库错误,请报告技术,已成功插入".$i."条记录");
                }else{
                    $this->error_msg("插入数据库错误,请报告技术");
                }
            }
        }
    }

    //订单查询接口
    public function get_order($order_id,$order_no=''){
        //返回结果中含金额的，都是以厘为单位，1元=1000厘
//        $url = 'http://123.56.242.212:25000/mch/queryOrder.do';//测试环境
        $url = 'http://123.56.243.180:25000/mch/queryOrder.do';//正式环境
        $data = array(
            'merAccount'=>JL_merchant_Account,
            'businessType'=>10,
            'merOrderNo'=>$order_id,
            'orderNo'=>$order_no,
            'merRequestTime'=>date('Y-m-d H:i:s',time())
        );
        $jinglan['data'] = json_encode($data);
        $jinglan['sign'] = md5('data='.$jinglan['data'].'&key='.jinglan_key);
        $result = $this->request($url,$jinglan);
        $result = json_decode(json_encode((json_decode($result))),TRUE);
//        $result['data'] = json_decode(json_encode((json_decode($result['data']))),TRUE);
        return $result;
    }

    //余额查询接口
    public function get_merchant(){
//        $url = 'http://123.56.242.212:25000/mch/queryBalance.do';//测试环境
        $url = 'http://123.56.243.180:25000/mch/queryBalance.do';//正式环境
        $data = array(
            "merAccount" => JL_merchant_Account,
            "merRequestTime"=>date('Y-m-d H:i:s',time())
        );
        $jinglan['data'] = json_encode($data);
        $jinglan['sign'] = md5('data='.$jinglan['data'].'&key='.jinglan_key);
        $result = $this->request($url,$jinglan);
        $result = json_decode(json_encode((json_decode($result))),TRUE);
//        $result['data'] = json_decode(json_encode((json_decode($result['data']))),TRUE);
        return $result;
    }

    public function export(){
        set_time_limit(0);
        $params = $_GET;
        $dataList  = $this->DAO->get_list_all($params);
        if($dataList){
            $this->jinglan_excel_out($dataList);
        }else{
            echo "没有数据需要导出！";
        }
    }

    private function jinglan_excel_out($data){
        set_time_limit(0);
        $str_now=date("Y-m-d H:i:s",strtotime("now"));
        $objExcel = new PHPExcel();
        $objExcel->setActiveSheetIndex(0);
        $objActSheet = $objExcel->getActiveSheet();
        $objActSheet->setTitle("净蓝Q币数据");
        $objActSheet->setCellValue("A1", "订单号");
        $objActSheet->setCellValue("B1", "购买数量");
        $objActSheet->setCellValue("C1", "价格");
        $objActSheet->setCellValue("D1", "qq号");
        $objActSheet->setCellValue("E1", "状态");
        $objActSheet->setCellValue("F1","游戏");
        $objActSheet->setCellValue("G1", "下单时间");
        $objActSheet->setCellValue("H1", "回单时间");
        $n = 2;
        foreach($data as $info){
            $objActSheet->setCellValueExplicit("A".$n,$info['order_id'],PHPExcel_Cell_DataType::TYPE_STRING);
            $objActSheet->setCellValueExplicit("B".$n,$info['amount'],PHPExcel_Cell_DataType::TYPE_STRING);
            $objActSheet->setCellValueExplicit("C".$n,$info['price'],PHPExcel_Cell_DataType::TYPE_STRING);
            $objActSheet->setCellValueExplicit("D".$n,$info['qq'],PHPExcel_Cell_DataType::TYPE_STRING);
            if($info['status']==0){
                $objActSheet->setCellValue("E".$n, "待处理");
            }elseif($info['status']==1){
                $objActSheet->setCellValue("E".$n, "充值中");
            }elseif($info['status']==2){
                $objActSheet->setCellValue("E".$n, "待确认");
            }elseif($info['status']==3){
                $objActSheet->setCellValue("E".$n, "已成功");
            }elseif($info['status']==4){
                $objActSheet->setCellValue("E".$n, "净蓝充值失败");
            }elseif($info['status']==5){
                $objActSheet->setCellValue("E".$n, "净蓝订单已取消");
            }
            if ($info['game_id']==0){
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
            $n++;
        }
        $title = iconv("UTF-8", "GB2312//IGNORE","净蓝Q币数据-".$str_now.'.xls');
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

    public function price_edit($id){
        if($id){
            $user_info = $this->DAO->get_user_info($_SESSION['usr_id']);
            //136对应李丽慧账号
            if($user_info['group_id']==1 || $user_info['id']==136){
                $info = $this->DAO->get_order_info($id);
                $this->assign("info",$info);
                $this->display("jinglan_price_edit.html");
            }else{
                die("你没有此权限，请联系管理员！");
            }
        }else{
            die("没有此订单！");
        }
    }

    public function price_save(){
        if($_POST){
            $params = $_POST;
            $user_info = $this->DAO->get_user_info($_SESSION['usr_id']);
            if($user_info['group_id']==1 || $user_info['id']==136){
                if(strtoupper($params['code'])!=$_SESSION['c']){
                    $this->error_msg("验证码填写错误！");
                }
                if(!preg_match("/(^(?:0\.\d{0,3}|[1-9][0-9]{0,9}|[1-9][0-9]{1,6}\.\d{1,3})$)/",$params['new_price'])){
                    $this->error_msg("更改价格非法！");
                }
                if($params['new_price']<0){
                    $this->error_msg("更改价格不能是负数");
                }
                $pay_pwd = md5($params['pay_pwd']);
                if($pay_pwd != $user_info['pay_pwd']){
                    $this->error_msg('支付密码错误！');
                }
                $this->DAO->update_jinglan_price($params);
                $result = $this->DAO->insert_qb_price_log($params,$user_info['id']);
                if($result){
                    $this->succeed_msg();
                }else{
                    $this->error_msg("净蓝QB价格更改保存失败！");
                }
            }else{
                $this->error_msg("你没有此权限，请联系管理员！");
            }
        }else{
            $this->error_msg("没有相关数据！");
        }
    }
}