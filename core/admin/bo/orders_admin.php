<?php
COMMON('adminBaseCore','pageCore','uploadHelper');
DAO('orders_admin_dao');

class orders_admin extends adminBaseCore{
    public $DAO;

    public function __construct() {
        parent::__construct();
        $this->DAO = new orders_admin_dao();
    }

    public function ch_order_list(){
        if($_SESSION['group_id'] != '11' && $_SESSION['group_id'] != '1'){
            die("你的账号没有渠道权限.");
        }
        $account_dao = new account_admin_dao();
        $user_info = $account_dao->get_user_info($_SESSION['usr_id']);
        if($_SESSION['group_id'] == '1'){
            //靠谱
            $user_info['ch_id']=7;
        }
        if(!$user_info || !$user_info['ch_id']){
            die("渠道信息获取失败,请重新登录.");
        }
        $app_dao = new app_admin_dao();
        $ch_app = $app_dao->get_ch_apps($user_info['ch_id']);
        $params = $_GET;
        $money = $this->DAO->get_ch_sum_money($params,$user_info['ch_id']);
        $list = $this->DAO->get_ch_order_list($this->page,$params,$user_info['ch_id']);
        $url="orders.php?act=ch_list&app_id=".$_GET['app_id']."&buyer_id=".$_GET['buyer_id']."&ch_id=".$_GET['ch_id'];
        $url.="&start_time=".$_GET['start_time']."&end_time=".$_GET['end_time']."&";
        $page = $this->pageshow($this->page,$url);
        $this->assign("ch_id", $user_info['ch_id']);
        $this->assign("datalist", $list);
        $this->assign("params", $params);
        $this->assign("money", $money[0]['money']);
        $this->assign("apps", $ch_app);
        $this->assign("page_bar", $page->show());
        $this->display("ch_order_list.html");
    }

    public function order_list_view($app_id){
        $app_dao = new app_admin_dao();
        $params = $this->get_params($_POST,$_GET);
        if ($params['date_type']){
            if (1 == $params['date_type']){
                $params['end_time'] = date('Y-m-d',strtotime("+1 day"));
                $params['start_time'] = date('Y-m-d',strtotime(date('Y-m',time())));
            }elseif (2 == $params['date_type']){
                $params['end_time'] = date('Y-m-d',strtotime(date('Y-m',time())));
                $params['start_time'] = date('Y-m-d',strtotime(date('Y-m',strtotime("-1 month"))));
            }elseif (3 == $params['date_type']){
                $params['end_time'] = date('Y-m-d',strtotime(date('Y-m',time())));
                $params['start_time'] = date('Y-m-d',strtotime(date('Y-m',strtotime("-3 month"))));
            }
        }else{
            $params['date_type'] = 1;
            $params['end_time'] = date('Y-m-d',strtotime("+1 day"));
            $params['start_time'] = date('Y-m-d',strtotime(date('Y-m',time())));
        }
        if (!$params['start_time'] || !$params['end_time']){
            $params['end_time'] = date('Y-m-d',strtotime("+1 day"));
            $params['start_time'] = date('Y-m-d',strtotime(date('Y-m',time())));
        }else{
            if (strtotime($params['end_time'])-strtotime($params['start_time'])>365*24*3600){
                die("订单查询时间间隔只能在一年内！");
            }
        }
        $account_dao = new account_admin_dao();
        $channel = $this->DAO->get_ch_info();
        $user_info = $account_dao->get_user_info($_SESSION['usr_id']);
        if($params['ch_status']=='1' && !empty($params['ch'])){
            $guild_str="";
            $user_code=$this->DAO->get_user_info_by_code($params['ch']);
            $guild_code_list = $this->DAO->get_guild_code($user_code['id']);
            if($guild_code_list){
                foreach($guild_code_list as $item){
                    if(!empty($item['user_code'])){
                        $guild_str.="'".$item['user_code']."',";
                    }
                }
                $params['guild_code'] = substr($guild_str, 0, strlen($guild_str) - 1);
            }
        }
        if($user_info['group_id']=="1" || $user_info['group_id']=='6'){
            $guild_list = $this->DAO->get_guild_list();
        }elseif($user_info['group_id']=="10"){
            $guild_code="";
            $guild_list = $this->DAO->get_guild_code($_SESSION['usr_id']);
            if($guild_list){
                foreach($guild_list as $item){
                    if(!empty($item['user_code'])){
                        $guild_code.="'".$item['user_code']."',";
                    }
                }
                $_SESSION['guild_code'] = substr($guild_code, 0, strlen($guild_code) - 1);
            }
        }
        if($params['app_id']){
            if($params['apple_id'] != 1){
                $apple_info = $this->DAO->get_apple_info($params['apple_id']);
                if($apple_info){
                    $params['apple_channel'] = $apple_info['channel'];
                }
            }
            $app_info = $this->DAO->get_app_info($params['app_id']);
            if($app_info['app_type'] == 2){
                $apple_list = $this->DAO->get_apple_list($app_info['app_id']);
                if($apple_list){
                    $this->assign("apple_list", $apple_list);
                }
            }
        }
        $money = $this->DAO->get_sum_money($params);
        $list = $this->DAO->get_order_list($this->page,$params);
        $url="orders.php?act=list&";
        $page = $this->pageshow($this->page,$url);
        $this->assign("channel", $channel);
        $this->assign("datalist", $list);
        $this->assign("guild_list", $guild_list);
        $this->assign("params", $params);
        $this->assign("money", $money[0]['money']);
        $this->assign("apps", $app_dao->get_user_apps());
        $this->assign("page_bar", $page->show());
        $this->assign("app_id", $app_id);
        $this->display("order_list.html");
    }

    public function app_edit_view($id){
        $info = $this->DAO->get($id);
        $this->assign("info", $info);
        $this->display("app_edit.html");
    }

    public function niu_coin_list(){
        if($_POST){
            $_SESSION['niu_coin_list'.$_SESSION['usr_id']] = $params = $_POST;
        }elseif(!$_GET['page']){
            unset($_SESSION['niu_coin_list'.$_SESSION['usr_id']]);
        }else{
            $params = $_SESSION['niu_coin_list'.$_SESSION['usr_id']];
        }
        $niu_log = $this->DAO->get_niu_log($this->page,$params);
        $page = $this->pageshow($this->page, "orders.php?act=niu_coin_list&");
        $this->assign("page_bar", $page->show());
        $this->assign("params",$params);
        $this->assign("datalist", $niu_log);
        $this->display("niu_order_list.html");
    }
    public function nnb_order(){
        $app_dao = new app_admin_dao();
        if($_POST){
            $_SESSION['nnb_order'.$_SESSION['usr_id']] = $params = $_POST;
        }elseif(!$_GET['page']){
            unset($_SESSION['nnb_order'.$_SESSION['usr_id']]);
        }else{
            $params = $_SESSION['nnb_order'.$_SESSION['usr_id']];
        }
        $nnb_order = $this->DAO->get_nnb_order($params,$this->page);
        $page = $this->pageshow($this->page,"orders.php?act=nnb_order&");
        $this->assign("guild_list",$this->DAO->get_guild_list());
        $this->assign("app_list", $app_dao->get_user_apps());
        $this->assign("params",$params);
        $this->assign("page_bar", $page->show());
        $this->assign("datalist", $nnb_order);
        $this->display("nnb_order_list.html");
    }

    public function do_app_edit($id){
        if(!$_POST['app_id'] || !$_POST['app_key'] || !$_POST['app_name']){
            $this->error_msg("缺少必填项");
        }
        if(!$_FILES['app_icon']['tmp_name']){
            $_POST['app_icon'] = $_POST['old_img'];
        }else{
            $_POST['app_icon']=$this->up_img('app_icon',GAME_ICON,array(),1,1,$id,0);
        }
        $this->DAO->update_app($_POST, $id);
        $this->succeed_msg();
    }

    public function export(){
        set_time_limit(0);
        $params = $_GET;
        if($params['ch_status']=='1' && !empty($params['ch'])){
            $guild_str="";
            $user_code=$this->DAO->get_user_info_by_code($params['ch']);
            $guild_code_list = $this->DAO->get_guild_code($user_code['id']);
            if($guild_code_list){
                foreach($guild_code_list as $item){
                    if(!empty($item['user_code'])){
                        $guild_str.="'".$item['user_code']."',";
                    }
                }
                $params['guild_code'] = substr($guild_str, 0, strlen($guild_str) - 1);
            }
        }
        if($params['apple_id'] != 1){
            $apple_info = $this->DAO->get_apple_info($params['apple_id']);
            if($apple_info){
                $params['apple_channel'] = $apple_info['channel'];
            }
        }
        $dataList  = $this->DAO->get_order_list_nolimit($params);
        if($dataList){
            foreach($dataList as $key=>$data){
                $code = $this->DAO->get_guild_code_info($data['ch']);
                //超级工会
                if($code['p2']==0 && $code['p1']==0){
                    $dataList[$key]['user_code3'] = $code['user_code'];
                }
                //高级工会
                elseif($code['p2']==0 && $code['p1']<>0){
                    $dataList[$key]['user_code2'] = $code['user_code'];
                    $guild1 = $this->DAO->get_code_info($code['p1']);
                    $dataList[$key]['user_code3'] =$guild1['user_code'];
                 }
                //普通工会
                elseif($code['p2']<>0 && $code['p1']<>0){
                    $dataList[$key]['user_code1'] = $code['user_code'];
                    $guild1 = $this->DAO->get_code_info($code['p1']);
                    $dataList[$key]['user_code2'] = $guild1['user_code'];
                    $guild2 = $this->DAO->get_code_info($code['p2']);
                    $dataList[$key]['user_code3'] = $guild2['user_code'];
                }else{
                    echo "数据异常！";
                }
            }
            $this->master_excel_out($dataList);
        }else{
            echo "没有数据需要导出！";
        }
    }

    public function ch_export(){
        set_time_limit(0);
        $params = $_GET;
        $dataList  = $this->DAO->get_ch_all_order($params);
        if($dataList){
            $this->master_excel_out($dataList);
        }else{
            echo "没有数据需要导出！";
        }
    }

    private function master_excel_out($data){
        set_time_limit(0);
        $str_now=date("Y-m-d H:i:s",strtotime("now"));
        $objExcel = new PHPExcel();
        $objExcel->setActiveSheetIndex(0);
        $objActSheet = $objExcel->getActiveSheet();
        $objActSheet->setTitle("充值数据");
        $objActSheet->setCellValue("A1", "游戏ID");
        $objActSheet->setCellValue("B1", "游戏名");
        $objActSheet->setCellValue("C1", "当前工会");
        $objActSheet->setCellValue("D1", "普通工会");
        $objActSheet->setCellValue("E1", "高级工会");
        $objActSheet->setCellValue("F1", "超级工会");
        $objActSheet->setCellValue("G1", "游戏区服ID");
        $objActSheet->setCellValue("H1", "充值ID");
        $objActSheet->setCellValue("I1", "账号");
        $objActSheet->setCellValue("J1", "66订单号");
        $objActSheet->setCellValue("K1", "cp订单号");
        $objActSheet->setCellValue("L1", "商品");
        $objActSheet->setCellValue("M1", "金额");
        $objActSheet->setCellValue("N1", "购买人ID");
        $objActSheet->setCellValue("O1", "渠道");
        $objActSheet->setCellValue("P1", "订单状态");
        $objActSheet->setCellValue("Q1", "下单日期");
        $objActSheet->setCellValue("R1", "时间");
        $objActSheet->setCellValue("S1", "支付日期");
        $objActSheet->setCellValue("T1", "时间");
        $objActSheet->setCellValue("U1", "方式");
        $objActSheet->setCellValue("V1", "区服");
        $n = 2;
        foreach($data as $info){
            $objActSheet->setCellValue("A".$n, $info['app_id']);
            $objActSheet->setCellValue("B".$n, $info['app_name']);
            $objActSheet->setCellValue("C".$n, $info['ch']);
            $objActSheet->setCellValue("D".$n, $info['user_code1']);
            $objActSheet->setCellValue("E".$n, $info['user_code2']);
            $objActSheet->setCellValue("F".$n, $info['user_code3']);
            $objActSheet->setCellValue("G".$n, $info['serv_id']);
            $objActSheet->setCellValue("H".$n, "'".$info['role_id']);
            $objActSheet->setCellValue("I".$n, "'".$info['payer']);
            $objActSheet->setCellValue("J".$n, "'".$info['order_id']);
            $objActSheet->setCellValue("K".$n, "'".$info['app_order_id']);
            $objActSheet->setCellValue("L".$n, $info['title']);
            $objActSheet->setCellValue("M".$n, $info['pay_money']);
            $objActSheet->setCellValue("N".$n, $info['buyer_id']);
            $objActSheet->setCellValue("O".$n, $info['pay_channel']);
            if($info['status'] == 0){
                $objActSheet->setCellValue("P".$n, "未付款");
            }elseif($info['status'] == 1){
                $objActSheet->setCellValue("P".$n, "已付款");
            }elseif($info['status'] == 2){
                $objActSheet->setCellValue("P".$n, "完成订单");
            }else{
                $objActSheet->setCellValue("P".$n, "取消订单");
            }
            $objActSheet->setCellValue("Q".$n, date("Y-m-d",$info['buy_time']));
            $objActSheet->setCellValue("R".$n, date("H:i:s",$info['buy_time']));
            $objActSheet->setCellValue("S".$n, date("Y-m-d",$info['pay_time']));
            $objActSheet->setCellValue("T".$n, date("H:i:s",$info['pay_time']));
            if($info['collect_company'] == 2){
                $objActSheet->setCellValue("U".$n, "hn");
            }elseif($info['collect_company'] == 3){
                $objActSheet->setCellValue("U".$n, "bj");
            }elseif($info['collect_company'] == 4){
                $objActSheet->setCellValue("U".$n, "hnyq");
            }else{
                $objActSheet->setCellValue("U".$n, "fj");
            }
            $objActSheet->setCellValue("V".$n,$info['serv_name']);

            $n++;
        }
        $title = iconv("UTF-8", "GB2312//IGNORE","充值数据-".$str_now.'.xls');
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

    public function error_list(){
        $app_dao = new app_admin_dao();
        if($_POST){
            $_SESSION['error_list'] = $params = $_POST;
        }elseif($_GET['page']){
            $params = $_SESSION['error_list'];
        }
        $account_dao = new account_admin_dao();
        $channel = $this->DAO->get_ch_info();
        $user_info = $account_dao->get_user_info($_SESSION['usr_id']);
        if($user_info['group_id']=="1"){
            $guild_list = $this->DAO->get_guild_list();
        }elseif($user_info['group_id']=="10"){
            $guild_code="";
            $guild_list = $this->DAO->get_guild_code($_SESSION['usr_id']);
            if($guild_list){
                foreach($guild_list as $item){
                    if(!empty($item['user_code'])){
                        $guild_code.="'".$item['user_code']."',";
                    }
                }
                $_SESSION['guild_code'] = substr($guild_code, 0, strlen($guild_code) - 1);
            }
        }
        $list = $this->DAO->get_error_list($this->page,$params);
        $page = $this->pageshow($this->page,"orders.php?act=error_list&");
        $this->assign("page_bar",$page->show());
        $this->assign("channel",$channel);
        $this->assign("apps", $app_dao->get_user_apps());
        $this->assign("guild_list",$guild_list);
        $this->assign("datalist", $list);
        $this->assign("params",$params);
        $this->display("order_error_list.html");
    }

    public function edit_view($id){
        $this->page_hash();
        $this->assign("id",$id);
        $this->display("order_edit.html");
    }

    public function edit_save(){
        $params = $_POST;
        if($params['pagehash'] != $_SESSION['page-hash']){
            $this->error_msg("参数异常!  001");
        }
        if(!$_SESSION['usr_id']){
            $this->error_msg("请先登录");
        }
        if(!$params['id']){
            $this->error_msg("请选择需要修改的订单");
        }
        $this->DAO->update_order($params['id']);
        $this->DAO->insert_operator($_SESSION['usr_id'],$params['id'],$this->client_ip());
        $this->succeed_msg();
    }

    public function apple_list(){
        $app_dao = new app_admin_dao();
        $params = $this->get_params($_POST,$_GET);
        if($params['app_id']){
            if($params['apple_id'] != 1){
                $apple_info = $this->DAO->get_apple_info($params['apple_id']);
                if($apple_info){
                    $params['apple_channel'] = $apple_info['channel'];
                }
            }
            $app_info = $this->DAO->get_app_info($params['app_id']);
            if($app_info['app_type'] == 2){
                $apple_list = $this->DAO->get_apple_list($app_info['app_id']);
                if($apple_list){
                    $this->assign("apple_list", $apple_list);
                }
            }
        }
        $channel = $this->DAO->get_ch_info();
        $money = $this->DAO->get_sum_apple_money($params);
        $list = $this->DAO->get_apple_order_list($this->page,$params);
        $url = "orders.php?act=apple_list&";
        $page = $this->pageshow($this->page,$url);
        $this->assign("channel", $channel);
        $this->assign("datalist", $list);
        $this->assign("params", $params);
        $this->assign("money", $money[0]['money']);
        $this->assign("apps", $app_dao->get_user_apps());
        $this->assign("page_bar", $page->show());
        $this->display("apple_order_list.html");
    }

    public function apple_export(){
        set_time_limit(0);
        $params = $_GET;
        if($params['apple_id'] != 1){
            $apple_info = $this->DAO->get_apple_info($params['apple_id']);
            if($apple_info){
                $params['apple_channel'] = $apple_info['channel'];
            }
        }
        $dataList  = $this->DAO->get_apple_order_list_nolimit($params);
        if($dataList){
            $this->apple_master_excel_out($dataList);
        }else{
            echo "没有数据需要导出！";
        }
    }

    private function apple_master_excel_out($data){
        set_time_limit(0);
        $str_now=date("Y-m-d H:i:s",strtotime("now"));
        $objExcel = new PHPExcel();
        $objExcel->setActiveSheetIndex(0);
        $objActSheet = $objExcel->getActiveSheet();
        $objActSheet->setTitle("苹果充值数据");
        $objActSheet->getColumnDimension('D')->setWidth(20);
        $objActSheet->getColumnDimension('E')->setWidth(35);
        $objActSheet->getColumnDimension('F')->setWidth(35);
        $objActSheet->getColumnDimension('G')->setWidth(20);
        $objActSheet->getColumnDimension('L')->setWidth(10);
        $objActSheet->getColumnDimension('N')->setWidth(10);
        $objActSheet->setCellValue("A1", "游戏ID");
        $objActSheet->setCellValue("B1", "游戏名");
        $objActSheet->setCellValue("C1", "游戏区服ID");
        $objActSheet->setCellValue("D1", "充值ID");
        $objActSheet->setCellValue("E1", "66订单号");
        $objActSheet->setCellValue("F1", "cp订单号");
        $objActSheet->setCellValue("G1", "apple订单号");
        $objActSheet->setCellValue("H1", "商品");
        $objActSheet->setCellValue("I1", "金额");
        $objActSheet->setCellValue("J1", "购买人ID");
        $objActSheet->setCellValue("K1", "订单状态");
        $objActSheet->setCellValue("L1", "下单日期");
        $objActSheet->setCellValue("M1", "下单时间");
        $objActSheet->setCellValue("N1", "支付日期");
        $objActSheet->setCellValue("O1", "支付时间");
        $n = 2;
        foreach($data as $info){
            $objActSheet->setCellValue("A".$n, $info['app_id']);
            $objActSheet->setCellValue("B".$n, $info['app_name']);
            $objActSheet->setCellValue("C".$n, $info['serv_id']);
            $objActSheet ->setCellValueExplicit("D".$n, $info['role_id'],PHPExcel_Cell_DataType::TYPE_STRING);
            $objActSheet ->setCellValueExplicit("E".$n, $info['order_id'],PHPExcel_Cell_DataType::TYPE_STRING);
            $objActSheet ->setCellValueExplicit("F".$n, $info['cp_order_id'],PHPExcel_Cell_DataType::TYPE_STRING);
            $objActSheet ->setCellValueExplicit("G".$n, $info['apple_order_id'],PHPExcel_Cell_DataType::TYPE_STRING);
            $objActSheet->setCellValue("H".$n, $info['title']);
            $objActSheet->setCellValue("I".$n, $info['pay_money']);
            $objActSheet->setCellValue("J".$n, $info['buyer_id']);
            if($info['status'] == 0){
                $objActSheet->setCellValue("K".$n, "未付款");
            }elseif($info['status'] == 1){
                $objActSheet->setCellValue("K".$n, "已付款");
            }elseif($info['status'] == 2){
                $objActSheet->setCellValue("K".$n, "验证通过");
            }elseif($info['status'] == 3){
                $objActSheet->setCellValue("K".$n, "完成订单");
            }elseif($info['status'] == 4){
                $objActSheet->setCellValue("K".$n, "验证失败");
            }else{
                $objActSheet->setCellValue("K".$n, "取消订单");
            }
            $objActSheet->setCellValue("L".$n, date("Y-m-d",$info['buy_time']));
            $objActSheet->setCellValue("M".$n, date("H:i:s",$info['buy_time']));
            $objActSheet->setCellValue("N".$n, date("Y-m-d",$info['pay_time']));
            $objActSheet->setCellValue("O".$n, date("H:i:s",$info['pay_time']));
            $n++;
        }
        $title = iconv("UTF-8", "GB2312//IGNORE","苹果充值数据-".$str_now.'.xls');
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

    public function app_info(){
        $result = array('code'=>0);
        $app_info = $this->DAO->get_app_info($_POST['app_id']);
        if($app_info['app_type'] == 1){
            die(json_encode($result));
        }else{
            $apple_list = $this->DAO->get_apple_list($app_info['app_id']);
            if(!$apple_list){
                die(json_encode($result));
            }else{
                $result['code'] = 1;
                $result['info'] = $apple_list;
                die(json_encode($result));
            }
        }
    }

    public function qq_member(){
        $params = $this->get_params($_POST,$_GET);
        $app_list = $this->DAO->get_app_list();
        $member_list = $this->DAO->get_qq_member_list($this->page,$params);
        $page = $this->pageshow($this->page,'orders.php?act=qq_member&');
        $this->assign("page_bar", $page->show());
        $this->assign('app_list',$app_list);
        $this->assign('params',$params);
        $this->assign('member_list',$member_list);
        $this->display('qq_member_list.html');
    }

    public function apple_fail_list(){
        $params = $this->get_params($_POST,$_GET);
        $list = $this->DAO->get_apple_orders_correct_list($this->page,$params);
        $page = $this->pageshow($this->page,'orders.php?act=apple_fail_list&');
        $this->assign("page_bar", $page->show());
        $this->assign('params',$params);
        $this->assign('list',$list);
        $this->display('apple_orders_correct_list.html');
    }

    public function apple_orders_update(){
        $this->page_hash();
        $this->display('apple_orders_update_view.html');
    }

    public function apple_orders_reg(){
        if(!$_POST['pagehash'] || $_POST['pagehash'] != $_SESSION['page-hash']){
            $this->error_msg("hash错误，请刷新后重新登录。");
        }
        if ($_POST['id'] or $_POST['order_id']){
            $reg_res = $this->DAO->get_apple_order_fail($_POST);
            if (empty($reg_res)){
                $this->error_msg("没有相关订单");
            }
            $this->succeed_msg($reg_res);
        }else{
            $this->error_msg("缺少必要参数");
        }
    }

    public function do_apple_orders_update(){
        if(!$_POST['pagehash'] || $_POST['pagehash'] != $_SESSION['page-hash']){
            $this->error_msg("hash错误，请刷新后重新登录。");
        }
        if ($_POST['apple_list_id'] && $_POST['user_charge_pwd']){
            //验证支付密码是否正确
            $user_info = $this->DAO->get_admins_info_by_id($_SESSION['usr_id']);
            if (!$user_info['pay_pwd'] || md5($_POST['user_charge_pwd'])!=$user_info['pay_pwd']){
                $this->error_msg('支付密码错误！');
            }
            //更改苹果订单的环境,状态,支付时间，回调时间
            $apple_res = $this->DAO->get_apple_order_fail(array(
                "id"=>$_POST['apple_list_id']
            ));
            if (empty($apple_res)){
                $this->error_msg("没有相关订单，更改失败！");
            }
            $this->DAO->update_apple_order_fail($apple_res['buy_time'],time()-300,$apple_res['id']);
            //插入操作记录
            $this->DAO->insert_apple_orders_correct($apple_res['id'],1);
            $this->succeed_msg("修改成功");
        }else{
            $this->error_msg("缺少必要参数");
        }
    }

}