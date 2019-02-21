<?php
COMMON('adminBaseCore','pageCore','uploadHelper','QQMailer');
BO('business_inside_admin');
DAO('business_inside_dao');

class business_inside_second extends adminBaseCore{
    public $DAO;
    public $vip;
    public $channel;

    public function __construct(){
        parent::__construct();
        $this->DAO = new business_inside_dao();
        $this->vip = array(
            'V5' => 'vip5',
            'V10' => 'vip10',
            'V12' => 'vip12',
        );
        $this->channel = array(
            '00008' => '应用宝',
            '00009' => '魔域混服',
            '00010' => '官服'
        );
    }

    public function order_collect(){
        $params = $_POST;
        $user_info = $this->DAO->get_user_info($_SESSION['usr_id']);
        $app_list = $this->DAO->get_app_list();
        if($_SESSION['group_id'] == '1' ){
            $order_list = $this->DAO->get_business_order_collect_list($params,$this->page);
        }elseif($_SESSION['group_id'] == '15' && !$user_info['p1'] && !$user_info['p2']){
            $group_list = $this->DAO->get_group_list($_SESSION['usr_id']);
            $order_list = $this->DAO->get_business_order_collect_list($params,$this->page,$_SESSION['usr_id']);
        }else{
            die("您没有该目录的权限，请联系管理员");
        }
        foreach($order_list as $key=>$data){
            $order_list[$key]['channel_name'] = $this->channel[$data['channel']];
        }
        if($params['app_id']){
            $service_list = $this->DAO->get_service_list($params['app_id']);
        }
        $page = $this->pageshow($this->page, "business_inside_second.php?act=order_collect&");
        $this->assign("page_bar", $page->show());
        $this->assign("group_list",$group_list);
        $this->assign("service_list",$service_list);
        $this->assign("user_info",$user_info);
        $this->assign("params",$params);
        $this->assign("order_list",$order_list);
        $this->assign("app_list",$app_list);
        $this->assign("channel_list",$this->channel);
        $this->display('chamber/inside_business_order_collect.html');
    }

    public function money_collect(){
        $params = $this->get_params($_POST,$_GET);
        if($params['start_time']){
            if($params['collect_type'] == '1'){
                $params['start'] = date('Ym',strtotime($params['start_time']));
            }else{
                $params['start'] = date('Ymd',strtotime($params['start_time']));
            }
        }
        if($params['end_time']){
            if($params['collect_type'] == '1'){
                $params['end'] = date('Ym',strtotime($params['end_time']));
            }else{
                $params['end'] = date('Ymd',strtotime($params['end_time']));
            }
        }
        $user_info = $this->DAO->get_user_info($_SESSION['usr_id']);
        $app_list = $this->DAO->get_app_list();
        if($_SESSION['group_id'] == '1'){
            $user_list = $this->DAO->get_business_list();
            $list = '';
            if($user_list){
                foreach($user_list as $key=>$data){
                    $list .= $data['id'].',';
                }
            }
            $order_list = $this->DAO->get_business_money_collect_list($params,$this->page,trim($list,','));
        }elseif($_SESSION['group_id'] == '15' && (!$user_info['p1'] || !$user_info['p2'])){
            $order_list = $this->DAO->get_business_money_collect_list($params,$this->page,$_SESSION['usr_id']);
        }else{
            die("您没有该目录的权限，请联系管理员");
        }
        foreach($order_list as $key=>$data){
            $order_list[$key]['channel_name'] = $this->channel[$data['channel']];
        }
        if($params['app_id']){
            $service_list = $this->DAO->get_service_list($params['app_id']);
        }
        $page = $this->pageshow($this->page, "business_inside.php?act=group_money_collect&");
        $this->assign("page_bar", $page->show());
        $this->assign("user_info",$user_info);
        $this->assign("params",$params);
        $this->assign("order_list",$order_list);
        $this->assign("service_list",$service_list);
        $this->assign("app_list",$app_list);
        $this->assign("channel_list",$this->channel);
        $this->display("chamber/inside_business_money_collect.html");
    }

    public function del_order($id){
        $this->assign('id',$id);
        $this->display("chamber/inside_del_order.html");
    }

    public function do_del($id){
        $info = $this->DAO->get_order_info($id);
        $user_info = $this->DAO->get_user_info($info['user_id']);
        if($user_info['p2']){
            $info['business_id'] = $user_info['p2'];
            $info['group_id'] = $user_info['p1'];
        }elseif($user_info['p1']){
            $info['group_id'] = $user_info['id'];
            $info['business_id'] = $user_info['p1'];
        }else{
            $info['group_id'] = $_SESSION['usr_id'];
            $info['business_id'] = $_SESSION['usr_id'];
        }
        $info['order_date'] = date('Ymd',$info['add_time']);
        //个人日订单、日汇总
        $personal_day_order = $this->DAO->get_personal_day_order($info,0,$info['user_id']);
        $personal_day_money = $this->DAO->get_personal_day_money($info,0,$info['user_id']);
        //本组日订单、日汇总
        $group_day_order = $this->DAO->get_group_day_order($info,1,$info['group_id']);
        $group_day_money = $this->DAO->get_group_day_money($info,1,$info['group_id']);
        //商会日订单、日汇总
        $business_day_order = $this->DAO->get_business_order($info,2,$info['business_id']);
        $business_day_money = $this->DAO->get_business_money($info,2,$info['business_id']);
        //个人日订单、日汇总删除
        $this->delete_order($info,$personal_day_order,$personal_day_money);
        //本组日订单、日汇总删除
        $this->delete_order($info,$group_day_order,$group_day_money);
        //商会日订单、日汇总删除
        $this->delete_order($info,$business_day_order,$business_day_money);
        if($info['status'] == '1'){
            //个人月订单、月汇总
            $info['order_date'] = date('Ym',$info['add_time']);
            //个人月订单、月汇总
            $personal_month_order = $this->DAO->get_personal_day_order($info,0,$info['user_id']);
            $personal_month_money = $this->DAO->get_personal_day_money($info,0,$info['user_id']);
            //本组月订单、月汇总
            $group_month_order = $this->DAO->get_group_day_order($info,1,$info['group_id']);
            $group_month_money = $this->DAO->get_group_day_money($info,1,$info['group_id']);
            //商会月订单、月汇总
            $business_month_order = $this->DAO->get_business_order($info,2,$info['business_id']);
            $business_month_money = $this->DAO->get_business_money($info,2,$info['business_id']);
            //个人月订单、月汇总删除
            $this->delete_order($info,$personal_month_order,$personal_month_money);
            //本组月订单、月汇总删除
            $this->delete_order($info,$group_month_order,$group_month_money);
            //商会月订单、月汇总删除
            $this->delete_order($info,$business_month_order,$business_month_money);
        }
        $stock_info = $this->DAO->get_stock_info($info,$info['group_id']);
        if($user_info['p2']){
            $lead_stock_info = $this->DAO->get_stock_info($info,$user_info['p2']);
        }
        //更新自己的库存代币
        $this->delete_stock($info,$stock_info);
        if($lead_stock_info){
            //更新商会长的库存代币
            $this->delete_stock($info,$lead_stock_info,1);
        }
        //插入库存记录
        if($user_info['p1'] && $user_info['p2']){
            $info['new_stock_balance'] = $stock_info['stock_balance']+$info['exit_depot'];
            $info['desc'] = '删除订单ID为'.$info['id'];
            $info['new_stock_collect'] = $stock_info['stock_collect'];
            $info['stock_num'] = -$info['exit_depot'];
            $stock_info['user_id'] = $info['user_id'];
            $this->DAO->insert_stock_record($info,$stock_info,$_SESSION['usr_id']);
        }
        $this->DAO->del_order($info,$_SESSION['usr_id']);
        $this->succeed_msg();
    }

    public function delete_order($params,$order,$money){
        $per_day_order['payment'] = $order['payment'] - $params['in_money'];
        $per_day_order['actual_payment'] = $order['actual_payment'] - $params['in_money'] + $params['in_money']*0.001;
        $per_day_order['sell_num'] = $order['sell_num'] - $params['exit_depot'];
        $per_day_order['loss_num'] = $order['loss_num'] - $params['loss_num'];
        $this->DAO->update_orders_collect($per_day_order,$order['id']);
        if($params['pay_mode'] == '1'){
            $per_day_order['wx_money'] = $money['wx_money'] - $params['in_money'];
            $per_day_order['ali_money'] = $money['ali_money'];
        }elseif($params['pay_mode'] == '2'){
            $per_day_order['ali_money'] =  $money['ali_money'] - $params['in_money'];
            $per_day_order['wx_money'] = $money['wx_money'];
        }else{
            $per_day_order['wx_money'] = $money['wx_money'];
            $per_day_order['ali_money'] = $money['ali_money'];
        }
        $per_day_order['total_money'] = $per_day_order['wx_money'] + $per_day_order['ali_money'];
        $per_day_order['service_charge'] = $per_day_order['total_money']*0.001;
        $per_day_order['actual_arrive'] = $per_day_order['total_money']+$per_day_order['service_charge'];
        //判断是否审核通过并删除对应的金额与人数
//        $user_money = $this->DAO->get_personal_day_money($params,0,$params['user_id']);
//        if($user_money['status'] == '3'){
//            $per_day_order['enter_money'] = $money['enter_money'] - $params['in_money'] + $params['in_money']*0.001;
//            if($user_money['order_num'] == '0'){
//                $per_day_order['enter_num'] = $money['enter_num'] - 1;
//            }else{
//                $per_day_order['enter_num'] = $money['enter_num'];
//            }
//        }else{
//            $per_day_order['enter_num'] = $money['enter_num'];
//            $per_day_order['enter_money'] = $money['enter_money'];
//        }
//        if($params['user_id'] == $money['user_id']){
//            $per_day_order['order_num'] = $user_money['order_num'] - 1;
//        }
        $this->DAO->update_money_collect($per_day_order,$money['id']);
    }

    public function delete_stock($params,$stock_info,$type=''){
        $user_info = $this->DAO->get_user_info($params['user_id']);
        if($params['user_id'] == $_SESSION['usr_id']){
            $params['desc'] = '订单删除';
        }else{
            $params['desc'] = '来自"'.$user_info['real_name'].'"商会的订单删除';
        }
        if($type){
            $params['new_stock_balance'] = $stock_info['stock_balance'];
        }else{
            $params['new_stock_balance'] = $stock_info['stock_balance'] + $params['exit_depot'] + $params['loss_num'];
        }
        $params['new_stock_collect'] = $stock_info['stock_collect'];
        $params['stock_num'] = $params['exit_depot'] + $params['loss_num'];
        if($params['stock_num']){
            $this->DAO->update_stock_info($params,$stock_info['user_id']);
            $this->DAO->insert_stock_record($params,$stock_info,$_SESSION['usr_id']);
        }
    }

    public function del_list(){
        $params = $this->get_params($_POST,$_GET);
        $app_list = $this->DAO->get_app_list();
        $user_info = $this->DAO->get_user_info($_SESSION['usr_id']);
        if($_SESSION['group_id'] == '1'){
            $order_list = $this->DAO->get_del_list($params,$this->page);
        }elseif($_SESSION['group_id'] == '15' && (!$user_info['p1'] || !$user_info['p2'])){
            $user_list = $this->DAO->get_user_list($_SESSION['usr_id']);
            $group = '';
            if($user_list){
                foreach($user_list as $key=>$data){
                    $group .= $data['id'].',';
                }
            }
            $group .= $_SESSION['usr_id'];
            $order_list = $this->DAO->get_del_list($params,$this->page,$group);
        }elseif($_SESSION['group_id'] == '15' && $user_info['p2'] && $user_info['p1']){
            $order_list = $this->DAO->get_del_list($params,$this->page,$_SESSION['usr_id']);
        }
        if($params['app_id']){
            $service_list = $this->DAO->get_service_list($params['app_id']);
        }
        $page = $this->pageshow($this->page, "business_inside_second.php?act=del_list&");
        $this->assign("page_bar", $page->show());
        $this->assign("channel_list",$this->channel);
        $this->assign('app_list',$app_list);
        $this->assign("service_list",$service_list);
        $this->assign("params",$params);
        $this->assign("app_list",$app_list);
        $this->assign("user_info",$user_info);
        $this->assign("order_list",$order_list);
        $this->display('chamber/inside_del_list.html');
    }

    public function del_export(){
        $params = $_GET;
        $user_info = $this->DAO->get_user_info($_SESSION['usr_id']);
        if($_SESSION['group_id'] == '1' || ($_SESSION['group_id']=='15' && !$user_info['p1'] && !$user_info['p2'])){
            $datalist = $this->DAO->get_all_del_list($params);
        }elseif($_SESSION['group_id'] == '15' && !$user_info['p2']){
            $user_list = $this->DAO->get_user_list($_SESSION['usr_id']);
            $group = '';
            if($user_list){
                foreach($user_list as $key=>$data){
                    $group .= $data['id'].',';
                }
            }
            $group .= $_SESSION['usr_id'];
            $datalist = $this->DAO->get_all_del_list($params,$group);
        }elseif($_SESSION['group_id'] == '15' && $user_info['p2'] && $user_info['p1']){
            $datalist = $this->DAO->get_all_del_list($params,$_SESSION['usr_id']);
        }
        if($datalist){
            $this->master_excel_out($datalist);
        }else{
            echo "没有数据需要导出！";
        }
    }

    public function master_excel_out($data){
        set_time_limit(0);
        $str_now = date("Y-m-d H:i:s",strtotime("now"));
        $objExcel = new PHPExcel();
        $objExcel->setActiveSheetIndex(0);
        $objActSheet = $objExcel->getActiveSheet();
        $objActSheet->setTitle("内部商会订单删除列表");
        $objActSheet->getColumnDimension('B')->setWidth(30);
        $objActSheet->getColumnDimension('D')->setWidth(20);
        $objActSheet->getColumnDimension('E')->setWidth(20);
        $objActSheet->setCellValue("A1", "删除日期");
        $objActSheet->setCellValue("B1", "订单号");
        $objActSheet->setCellValue("C1", "渠道");
        $objActSheet->setCellValue("D1", "游戏名称");
        $objActSheet->setCellValue("E1", "区服名称");
        $objActSheet->setCellValue("F1", "出仓代币");
        $objActSheet->setCellValue("G1", "代币比例");
        $objActSheet->setCellValue("H1", "购买人");
        $objActSheet->setCellValue("I1", "损耗代币");
        $objActSheet->setCellValue("J1", "收入金额");
        $objActSheet->setCellValue("K1", "收款方式");
        $objActSheet->setCellValue("L1", "删除操作人");
        $n = 2;
        foreach($data as $info){
            $objActSheet->setCellValue("A".$n, date("Ymd",$info['del_time']));
            $objActSheet->setCellValueExplicit("B".$n, $info['order_id'],PHPExcel_Cell_DataType::TYPE_STRING);
            $objActSheet->setCellValue("C".$n, $this->channel[$info['channel']]);
            $objActSheet->setCellValue("D".$n, $info['app_name']);
            $objActSheet->setCellValue("E".$n, $info['service_name']);
            $objActSheet->setCellValue("F".$n, $info["exit_depot"]);
            $objActSheet->setCellValue("G".$n, $info["token_scale"]);
            $objActSheet->setCellValue("H".$n, $info["buy_name"]);
            $objActSheet->setCellValue("I".$n, $info["loss_num"]);
            $objActSheet->setCellValue("J".$n, $info["in_money"]);
            if($info['pay_mode'] == 1){
                $objActSheet->setCellValue("K".$n, "微信");
            }elseif($info['pay_mode'] == 2){
                $objActSheet->setCellValue("K".$n, "支付宝");
            }else{
                $objActSheet->setCellValue("K".$n, "无");
            }
            $objActSheet->setCellValue("L".$n, $info['real_name']);
            $n++;
        }
        $title = iconv("UTF-8", "GB2312//IGNORE","内部商会订单删除列表-".$str_now.'.xls');
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

    public function money_detail(){
        $params = $this->get_params($_POST,$_GET);
        if($params['start_time'] || $params['end_time']){
            $params['start_time'] = strtotime($params['start_time']);
            $params['end_time'] = strtotime($params['end_time']);
        }else{
            $params['start_time'] = strtotime(date('Y-m-01 00:00:00',time()));
            $params['end_time'] = strtotime(date('Y-m-d H:i:s',time()));
        }
        $user_info = $this->DAO->get_user_info($_SESSION['usr_id']);
        if($_SESSION['group_id'] == '1'){
            $list = $this->DAO->get_money_detail($this->page,$params);
            $wx_money = $this->DAO->get_wx_money($params);
            $ali_money = $this->DAO->get_ali_money($params);
            $group_list = $this->DAO->get_group_all_list();
        }elseif($_SESSION['group_id'] == '15' && (!$user_info['p1'] || !$user_info['p2'])){
            $user_list = $this->DAO->get_user_list($_SESSION['usr_id']);
            $group = '';
            if($user_list){
                foreach($user_list as $key=>$data){
                    $group .= $data['id'].',';
                }
            }
            $group .= $_SESSION['usr_id'];
            $list = $this->DAO->get_money_detail($this->page,$params,$group);
            $wx_money = $this->DAO->get_wx_money($params,$group);
            $ali_money = $this->DAO->get_ali_money($params,$group);
            $group_list = $this->DAO->get_personal_list($_SESSION['usr_id']);
        }elseif($_SESSION['group_id'] == '15' && $user_info['p2'] && $user_info['p1']){
            $list = $this->DAO->get_money_detail($this->page,$params,$_SESSION['usr_id']);
            $wx_money = $this->DAO->get_wx_money($params,$_SESSION['usr_id']);
            $ali_money = $this->DAO->get_ali_money($params,$_SESSION['usr_id']);
        }else{
            die("您没有该目录的权限，请联系管理员。");
        }
        $total_money = $wx_money['num']+$ali_money['num'];
        foreach($list as $key=>$data){
            $user = $this->DAO->get_user_info($data['user_id']);
            if($user['p2'] || $user['p1']){
                $group_id = $user['p1'];
            }else{
                $group_id = $user['id'];
            }
            $group_info = $this->DAO->get_user_info($group_id);
            $list[$key]['group_name'] = $group_info['real_name'];
            $list[$key]['channel_name'] = $this->channel[$data['channel']];
        }
        $app_list = $this->DAO->get_app_list();
        if($params['app_id']){
            $service_list = $this->DAO->get_service_list($params['app_id']);
        }
        $page = $this->pageshow($this->page, "business_inside_second.php?act=money_detail&");
        $this->assign("page_bar", $page->show());
        $this->assign('wx_money',$wx_money);
        $this->assign('ali_money',$ali_money);
        $this->assign('total_money',$total_money);
        $this->assign('channel_list',$this->channel);
        $this->assign('app_list',$app_list);
        $this->assign('service_list',$service_list);
        $this->assign('group_list',$group_list);
        $this->assign('user_info',$user_info);
        $this->assign('params',$params);
        $this->assign('dataList',$list);
        $this->display('chamber/inside_money_detail.html');
    }

    public function detail_export(){
        $params = $_GET;
        $user_info = $this->DAO->get_user_info($_SESSION['usr_id']);
        if($_SESSION['group_id'] == '1'){
            $datalist = $this->DAO->get_all_money_detail($params);
        }elseif($_SESSION['group_id'] == '15' && (!$user_info['p1'] || !$user_info['p2'])){
            $user_list = $this->DAO->get_user_list($_SESSION['usr_id']);
            $group = '';
            if($user_list){
                foreach($user_list as $key=>$data){
                    $group .= $data['id'].',';
                }
            }
            $group .= $_SESSION['usr_id'];
            $datalist = $this->DAO->get_all_money_detail($params,$group);
        }elseif($_SESSION['group_id'] == '15' && $user_info['p2'] && $user_info['p1']){
            $datalist = $this->DAO->get_all_money_detail($params,$_SESSION['usr_id']);
        }
        if($datalist){
            $this->master_excel_out_money($datalist);
        }else{
            echo "没有数据需要导出！";
        }
    }

    public function master_excel_out_money($data){
        set_time_limit(0);
        $str_now = date("Y-m-d H:i:s",strtotime("now"));
        $objExcel = new PHPExcel();
        $objExcel->setActiveSheetIndex(0);
        $objActSheet = $objExcel->getActiveSheet();
        $objActSheet->setTitle("内部商会现金明细列表");
        $objActSheet->getColumnDimension('I')->setWidth(30);
        $objActSheet->getColumnDimension('D')->setWidth(20);
        $objActSheet->getColumnDimension('E')->setWidth(20);
        $objActSheet->setCellValue("A1", "日期");
        $objActSheet->setCellValue("B1", "执行员");
        $objActSheet->setCellValue("C1", "渠道");
        $objActSheet->setCellValue("D1", "游戏名称");
        $objActSheet->setCellValue("E1", "区服名称");
        $objActSheet->setCellValue("F1", "类型");
        $objActSheet->setCellValue("G1", "支付宝收款");
        $objActSheet->setCellValue("H1", "微信收款");
        $objActSheet->setCellValue("I1", "对应单号");
        $n = 2;
        foreach($data as $info){
            $objActSheet->setCellValue("A".$n, date("Ymd",$info['sell_time']));
            if($info['p1'] && !$info['p2']){
                $objActSheet->setCellValue("B".$n, $info['account']);
            }else{
                $objActSheet->setCellValue("B".$n, $info['real_name']);
            }
            $objActSheet->setCellValue("C".$n, $this->channel[$info['channel']]);
            $objActSheet->setCellValue("D".$n, $info['app_name']);
            $objActSheet->setCellValue("E".$n, $info['service_name']);
            if($info['type'] == 0){
                $objActSheet->setCellValue('F'.$n, '出代币');
            }elseif($info['type'] == 1){
                $objActSheet->setCellValue('F'.$n, '出空号');
            }elseif($info['type'] == 2){
                $objActSheet->setCellValue('F'.$n, '出整号');
            }
            $objActSheet->setCellValue("G".$n, $info["ali_money"]);
            $objActSheet->setCellValue("H".$n, $info["wx_money"]);
            $objActSheet->setCellValueExplicit("I".$n, $info['order_id'],PHPExcel_Cell_DataType::TYPE_STRING);
            $n++;
        }
        $title = iconv("UTF-8", "GB2312//IGNORE","内部商会现金明细列表-".$str_now.'.xls');
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

    public function game_sell(){
        $params = $this->get_params($_POST,$_GET);
        $user_info = $this->DAO->get_user_info($_SESSION['usr_id']);
        if($_SESSION['group_id']=='15' && !$user_info['p1'] && !$user_info['p2']){
            $user_id = $_SESSION['usr_id'];
        }elseif($_SESSION['group_id']=='15' && $user_info['p1'] && !$user_info['p2']){
            $user_id = $user_info['p1'];
        }elseif($_SESSION['group_id']=='15' && $user_info['p1'] && $user_info['p2']){
            $user_id = $user_info['p2'];
        }
        if($_SESSION['group_id']=='1'){
            $order_list = $this->DAO->get_sell_list($params,$this->page);
        }elseif($_SESSION['group_id']=='15'){
            $order_list = $this->DAO->get_sell_list($params,$this->page,$user_id);
        }else{
            die("您没有该目录的权限，请联系管理员");
        }
        $app_list = $this->DAO->get_app_list();
        if($params['app_id']){
            $service_list = $this->DAO->get_service_list($params['app_id']);
        }
        $page = $this->pageshow($this->page, "business_inside_second.php?act=game_sell&");
        $this->assign("page_bar", $page->show());
        $this->assign('channel_list',$this->channel);
        $this->assign('order_list',$order_list);
        $this->assign('app_list',$app_list);
        $this->assign('service_list',$service_list);
        $this->assign('user_info',$user_info);
        $this->assign('params',$params);
        $this->display('chamber/inside_sell_list.html');
    }

    public function add_sell(){
        $app_list = $this->DAO->get_app_list();
        $this->assign('app_list',$app_list);
        $this->assign('vip_list',$this->vip);
        $this->display('chamber/inside_sell_add.html');
    }

    public function do_add_sell(){
        $params = $_POST;
        $stock_info = $this->DAO->get_stock_info($params,$_SESSION['usr_id']);
        $msg = $this->validate_msg($params);
        if($msg['error']){
            $this->error_msg($msg['error']);
        }
        if(!$stock_info){
            $this->error_msg('库存信息不存在，无法导入');
        }
        $params = $msg['data'];
        $config = $this->DAO->get_configure();
        $this->insert_sell($params,$config,$stock_info,$_SESSION['usr_id']);
        $this->succeed_msg();
    }

    public function validate_msg($params){
        $array = array();
        if(!$params['app_id'] || !$params['service_id'] || !$params['role_job'] || !$params['vip_grade'] || !$params['type']
            || is_null($params['status']) || !$params['role_name'] || !$params['login_account'] || !$params['login_pwd'] || !$params['re_money']){
            $array['error'] = '缺少必填项';
            return $array;
        }
        if($params['status'] == '0'){
            if($params['pay_mode'] || $params['in_money'] || $params['buy_name'] || $params['sell_time']){
                $array['error'] = '状态为未出售时，收款方式、收入金额、购买人、卖号时间必须为空';
                return $array;
            }
        }else{
            if(!$params['pay_mode'] || !$params['in_money'] || !$params['buy_name'] || !$params['sell_time']){
                $array['error'] = '状态为已出售时，收款方式、收入金额、购买人、卖号时间不能为空';
                return $array;
            }
            $params['operation_id'] = $_SESSION['usr_id'];
        }
        if($params['type'] == '1'){
            if($params['exit_depot']){
                $array['error'] = '账号类型为空号时，出仓代币必须为空';
                return $array;
            }
            if(!$params['enter_depot']){
                $array['error'] = '账号类型为空号时，进仓代币不能为空';
                return $array;
            }
        }elseif($params['type'] == '2'){
            if($params['status'] != '1'){
                $array['error'] = '账号类型为整号时，状态必须是已出售';
                return $array;
            }
            if(!$params['exit_depot'] || !$params['enter_depot']){
                $array['error'] = '账号类型为整号时，进、出仓代币不能为空';
                return $array;
            }
        }
        $time = time();
        if($params['do_time']){
            $params['do_time'] = strtotime($params['do_time']);
        }else{
            $params['do_time'] = $time;
        }
        if($params['sell_time']){
            $params['sell_time'] = strtotime($params['sell_time']);
        }else{
            if($params['status'] == '1'){
                $params['sell_time'] = time();
            }else{
                $params['sell_time'] = '';
            }
        }
        if(date('Y',$params['do_time']) == '1970' || date('Y',$params['sell_time']) == '1970'){
            $array['time'] = '时间格式出错啦';
            return $array;
        }
        if($params['do_time']>$time){
            $array['error'] = '做号时间不能超过当前时间';
            return $array;
        }
        if($params['sell_time']>$time){
            $array['error'] = '卖号时间不能超过当前时间';
            return $array;
        }
        if($params['sell_time'] && ($params['do_time']>$params['sell_time'])){
            $array['error'] = '卖号时间不能小于做号时间';
            return $array;
        }
        $array['data'] = $params;
        return $array;
    }

    public function insert_sell($params,$config,$stock_info,$user_id=''){
        if($config[$this->vip[$params['vip_grade']]] != $params['enter_depot']){
            $params['order_status'] = 1;
        }else{
            $params['order_status'] = 0;
        }
        $str = '';
        for ($i = 1; $i <= 4; $i++) {
            $str .= chr(rand(65, 90));
        }
        $params['order_id'] = $str.date('Ymd').time().rand(10,99);
        //日现金明细
        if($params['pay_mode'] == '1'){
            $params['wx_money'] = $params['in_money'];
            $params['ali_money'] = 0;
        }elseif($params['pay_mode'] == '2'){
            $params['ali_money'] = $params['in_money'];
            $params['wx_money'] = 0;
        }else{
            $params['ali_money'] = 0;
            $params['wx_money'] = 0;
        }
        if($user_id){
            $operation_id = $user_id;
        }else{
            $operation_id = $_SESSION['usr_id'];
        }
        $app_info = $this->DAO->get_app_info($params['app_id']);
        $params['channel'] = $app_info['channel'];
        if($params['status'] == 1){
            $this->update_money($params);
        }
        if($params['type'] == 1){
            $params['new_stock_balance'] = $stock_info['stock_balance'] + $params['enter_depot'];
            while($params['new_stock_balance'] == $stock_info['stock_balance']){
                $params['new_stock_collect'] = $stock_info['stock_collect'] + $params['enter_depot'];
            }
        }elseif($params['type'] == 2){
            $params['new_stock_balance'] = $stock_info['stock_balance'];
        }
        $params['new_stock_collect'] = $stock_info['stock_collect'] + $params['enter_depot'];
        while($params['new_stock_collect'] == $stock_info['stock_collect']){
            $params['new_stock_collect'] = $stock_info['stock_collect'] + $params['enter_depot'];
        }
        $params['stock_num'] = $params['enter_depot'];
        $this->DAO->insert_game_sell($params,$_SESSION['usr_id']);
        $this->DAO->insert_money_detail($params,$operation_id);
        $this->DAO->update_stock_info($params,$_SESSION['usr_id']);
        $params['desc'] = "新建游戏账号";
        $this->DAO->insert_stock_record($params,$stock_info,$_SESSION['usr_id']);
    }

    public function update_money($params){
        if($params['sell_time']){
            $params['order_time'] = $params['order_date'] = date('Ymd',$params['sell_time']);
        }else{
            $params['order_time'] = $params['order_date'] = date('Ymd',time());
        }
        $user_info = $this->DAO->get_user_info($_SESSION['usr_id']);
        $user_id = $_SESSION['usr_id'];
        if($user_info['p2']){
            $params['business_id'] = $user_info['p2'];
            $params['group_id'] = $user_info['p1'];
        }elseif($user_info['p1']){
            $params['business_id'] = $user_info['p1'];
            $params['group_id'] = $user_info['id'];
        }else{
            $params['group_id'] = $_SESSION['usr_id'];
            $params['business_id'] = $_SESSION['usr_id'];
        }
        $per_money = $this->DAO->get_personal_day_money($params,0,$user_id);
        $group_money = $this->DAO->get_group_day_money($params,1,$params['group_id']);
        $business_money = $this->DAO->get_business_money($params,2,$params['business_id']);
        if(!$per_money){
            $this->insert_money_collect($params,$user_id,0,0);
        }else{
            $this->update_money_collect($params,$per_money);
        }
        if(!$group_money){
            $this->insert_money_collect($params,$user_id,1,0);
        }else{
            $this->update_money_collect($params,$group_money);
        }
        if(!$business_money){
            $this->insert_money_collect($params,$user_id,2,0);
        }else{
            $this->update_money_collect($params,$business_money);
        }
    }

    public function insert_money_collect($params,$user_id,$status,$collect_type){
        $per_day_order['order_time'] = $params['order_time'];
        if($params['pay_mode'] == '1'){
            $per_day_order['wx_money'] = $params['in_money'];
            $per_day_order['ali_money'] = 0;
        }elseif($params['pay_mode'] == '2'){
            $per_day_order['ali_money'] = $params['in_money'];
            $per_day_order['wx_money'] = 0;
        }else{
            $per_day_order['ali_money'] = 0;
            $per_day_order['wx_money'] = 0;
        }
        $per_day_order['collect_type'] = $collect_type;
        $per_day_order['total_money'] = $per_day_order['wx_money'] + $per_day_order['ali_money'];
        $per_day_order['service_charge'] = $per_day_order['total_money']*0.001;
        $per_day_order['actual_arrive'] = $per_day_order['total_money']-$per_day_order['service_charge'];
        $per_day_order['status'] = 3;
        $per_day_order['enter_num'] = 1;
        $per_day_order['enter_money'] = $per_day_order['actual_arrive'];
        $this->DAO->insert_money_collect($per_day_order,$params,$user_id,$status);
    }

    public function update_money_collect($params,$day_money){
        if($params['pay_mode'] == '1'){
            $per_day_order['wx_money'] = $params['in_money'] + $day_money['wx_money'];
            $per_day_order['ali_money'] = $day_money['ali_money'];
        }elseif($params['pay_mode'] == '2'){
            $per_day_order['ali_money'] = $params['in_money'] + $day_money['ali_money'];
            $per_day_order['wx_money'] = $day_money['wx_money'];
        }else{
            $per_day_order['wx_money'] = $day_money['wx_money'];
            $per_day_order['ali_money'] = $day_money['ali_money'];
        }
        $per_day_order['total_money'] = $per_day_order['wx_money'] + $per_day_order['ali_money'];
        $per_day_order['service_charge'] = $per_day_order['total_money']*0.001;
        $per_day_order['actual_arrive'] = $per_day_order['total_money']-$per_day_order['service_charge'];
        $per_day_order['enter_money'] = $day_money['enter_money'] + $params['in_money'] - $params['in_money']*0.001;
        $per_day_order['enter_num'] = $day_money['enter_num'];
        $this->DAO->update_money_collect($per_day_order,$day_money['id']);
    }

    public function edit_sell($id){
        $info = $this->DAO->get_sell_info($id);
        $info['channel_name'] = $this->channel[$info['channel']];
        $service_list = $this->DAO->get_service_list($info['app_id']);
        $app_list = $this->DAO->get_app_list();
        $this->assign("vip_list",$this->vip);
        $this->assign("service_list",$service_list);
        $this->assign("app_list",$app_list);
        $this->assign("info",$info);
        $this->display('chamber/inside_sell_edit.html');
    }

    public function do_edit_sell(){
        $params = $_POST;
        if(!$params['pay_mode'] || !$params['buy_name'] || !$params['in_money']){
            $this->error_msg("缺少必填项");
        }
        if($params['in_money']<0){
            $this->error_msg("收入金额不能小于0");
        }
        $info = $this->DAO->get_sell_info($params['id']);
        if(!$info){
            $this->error_msg("订单ID出错啦");
        }
        if($params['sell_time']){
            $params['sell_time'] = strtotime($params['sell_time']);
        }else{
            $params['sell_time'] = time();
        }
        if(intval($info['do_time'])>$params['sell_time']){
            $this->error_msg("卖号时间不能比出号时间早");
        }
        if(intval($params['sell_time'])>time()){
            $this->error_msg("卖号时间不能超过当前时间");
        }
        $str = array_merge($info,$params);
        $this->update_money($str);
        if($params['pay_mode'] == '1'){
            $params['wx_money'] = $params['in_money'];
            $params['ali_money'] = 0;
        }elseif($params['pay_mode'] == '2'){
            $params['ali_money'] = $params['in_money'];
            $params['wx_money'] = 0;
        }else{
            $params['ali_money'] = 0;
            $params['wx_money'] = 0;
        }
        $this->DAO->update_sell_info($params,$_SESSION['usr_id']);
        $this->DAO->update_money_detail($params,$info,$_SESSION['usr_id']);
        $this->succeed_msg();
    }

    public function orders_import_view(){
        $this->display("chamber/inside_sell_import.html");
    }

    public function import_do(){
        if(isset($_FILES['order_file'])){
            $service_file = $_FILES['order_file'];
            if(preg_match("/\.xls$/",$service_file['name'])){
                $type = 1;
                $temp = dirname($service_file['tmp_name']).'/temp_test.xls';
            }elseif(preg_match("/\.xlsx$/",$service_file['name'])){
                $type = 2;
                $temp = dirname($service_file['tmp_name']).'/temp_test.xlsx';
            }else{
                $this->error_msg("上传文件后缀必须是xls或者xlsx！");
            }
            if($service_file['size']>=1024*1024){
                $this->error_msg("上传文件大小不能超过1M！");
            }
            if(move_uploaded_file($service_file['tmp_name'],$temp)){
                if(file_exists($temp)){
                    //执行数据解析
                    $data_arr = array(
                        array("title_name"=>"做号日期","title_field"=>"do_time","title_type"=>"string"),
                        array("title_name"=>"游戏名称","title_field"=>"app_name","title_type"=>"string"),
                        array("title_name"=>"区服名称","title_field"=>"service_name","title_type"=>"string"),
                        array("title_name"=>"账号","title_field"=>"login_account","title_type"=>"string"),
                        array("title_name"=>"密码","title_field"=>"login_pwd","title_type"=>"string"),
                        array("title_name"=>"职业","title_field"=>"role_job","title_type"=>"string"),
                        array("title_name"=>"性别","title_field"=>"role_sex"),
                        array("title_name"=>"角色名","title_field"=>"role_name","title_type"=>"string"),
                        array("title_name"=>"充值金额","title_field"=>"re_money","title_type"=>"float"),
                        array("title_name"=>"进仓代币","title_field"=>"enter_depot","title_type"=>"int"),
                        array("title_name"=>"出仓代币","title_field"=>"exit_depot"),
                        array("title_name"=>"VIP","title_field"=>"vip_grade","title_type"=>"string"),
                        array("title_name"=>"类型","title_field"=>"type","title_type"=>"string"),
                        array("title_name"=>"状态","title_field"=>"status","title_type"=>"string"),
                        array("title_name"=>"收入金额","title_field"=>"in_money"),
                        array("title_name"=>"付款方式","title_field"=>"pay_mode","title_type"=>"string"),
                        array("title_name"=>"购买日期","title_field"=>"sell_time"),
                        array("title_name"=>"购买人","title_field"=>"buy_name"),
                        array("title_name"=>"出号人","title_field"=>"operation_name"),
                        array("title_name"=>"账号备注信息","title_field"=>"account_desc"),
                    );
                    $order_data = $this->excel_import_data($temp,$data_arr,$type);
                    unlink($temp);
                    if(!$order_data){
                        $this->error_msg('导入数据格式出错或没有数据');
                    }
                    $app_list = $this->DAO->get_app_list();
                    if(empty($app_list)){
                        $this->error_msg("没有维护对应游戏");
                    }
                    $game_list = array();
                    foreach($app_list as $app_value){
                        if(!in_array($app_value['app_name'],array_values($game_list))){
                            $game_list[$app_value['app_id']] = $app_value['app_name'];
                        }
                    }
                    $error = '';
                    $time = '';
                    $stock_error = '';
                    foreach($order_data as $key=>$value){
                        if(!in_array($value['app_name'],array_values($game_list))){
                            $error .= ($key+2).',';
                            continue;
                        }
                        $order_data[$key]['app_id'] = $value['app_id'] = array_search($value['app_name'], $game_list);
                        $service_list = $this->DAO->get_service_list($order_data[$key]['app_id']);
                        foreach($service_list as $service_value){
                            if(!in_array($service_value['service_name'],array_values($service_list))){
                                $game_service[$service_value['service_id']] = $service_value['service_name'];
                            }
                        }
                        $order_data[$key]['service_id'] = $value['service_id'] = array_search($value['service_name'], $game_service);

                        if(strstr($value['type'],'整号')){
                            $order_data[$key]['type'] = $value['type'] = 2;
                        }elseif(strstr($value['type'],'空号')){
                            $order_data[$key]['type'] = $value['type'] = 1;
                        }
                        if(strstr($value['status'],'已售')){
                            $order_data[$key]['status'] = $value['status'] = 1;
                        }elseif(strstr($value['status'],'未出售')){
                            $order_data[$key]['status'] = $value['status'] = 0;
                        }
                        if(strstr($value['pay_mode'],'支付宝')){
                            $order_data[$key]['pay_mode'] = $value['pay_mode'] = 2;
                        }elseif(strstr($value['pay_mode'],'微信')){
                            $order_data[$key]['pay_mode'] = $value['pay_mode'] = 1;
                        }else{
                            $order_data[$key]['pay_mode'] = $value['pay_mode'] = 0;
                        }
                        if($value['role_sex']=="男"){
                            $order_data[$key]['role_sex'] = $value['role_sex'] = 1;
                        }elseif($value['role_sex']=='女'){
                            $order_data[$key]['role_sex'] = $value['role_sex'] = 2;
                        }
                        $stock_info = $this->DAO->get_stock_info($value,$_SESSION['usr_id']);
                        if(!$stock_info){
                            $stock_error .= ($key+2).',';
                            continue;
                        }
                        $msg = $this->validate_msg($value);
                        if($msg['error']){
                            $error .= ($key+2).',';
                            continue;
                        }
                        if($msg['time']){
                            $time .= ($key+2).',';
                            continue;
                        }
                        $value = $msg['data'];
                        $order_data[$key]['operation_id'] = $value['operation_id'];
                        $order_data[$key]['do_time'] = $value['do_time'];
                        $order_data[$key]['sell_time'] = $value['sell_time'];
                    }
                   if($error){
                        $this->error_msg($error.'导入订单中这些订单出错啦');
                    }elseif($stock_error){
                       $this->error_msg($stock_error.'导入订单中这些订单的库存信息不存在，无法导入');
                   }elseif($time){
                       $this->error_msg($time.'导入订单中这些订单的时间格式出错啦');
                   }else{
                        //导入mysql
                       $config = $this->DAO->get_configure();
                        foreach($order_data as $key =>$data){
                            if($data['operation_name']){
                                $user_info = $this->DAO->get_user_id($data['operation_name']);
                                $data['operation_id'] = $user_info['id'];
                            }
                            $stock_info = $this->DAO->get_stock_info($data,$_SESSION['usr_id']);
                            $this->insert_sell($data,$config,$stock_info,$data['operation_id']);
                        }
                    }

                    $this->succeed_msg("导入成功！");
                }else{
                    $this->error_msg("Excel文件不存在！");
                }
            }else{
                $this->error_msg("Excel文件复制失败！");
            }
        }else{
            $this->error_msg("Excel文件上传失败！");
        }
    }

    public function sell_export(){
        $params = $_GET;
        $user_info = $this->DAO->get_user_info($_SESSION['usr_id']);
        if($_SESSION['group_id']=='15' && !$user_info['p1'] && !$user_info['p2']){
            $user_id = $_SESSION['usr_id'];
        }elseif($_SESSION['group_id']=='15' && $user_info['p1'] && !$user_info['p2']){
            $user_id = $user_info['p1'];
        }elseif($_SESSION['group_id']=='15' && $user_info['p1'] && $user_info['p2']){
            $user_id = $user_info['p2'];
        }
        if($_SESSION['group_id']=='1'){
            $order_list = $this->DAO->get_all_sell_list($params);
        }elseif($_SESSION['group_id']=='15'){
            $order_list = $this->DAO->get_all_sell_list($params,$user_id);
        }
        if($order_list){
            $this->master_excel_out_sell($order_list);
        }else{
            echo "没有数据需要导出！";
        }
    }

    public function master_excel_out_sell($data){
        set_time_limit(0);
        $str_now = date("Y-m-d H:i:s",strtotime("now"));
        $objExcel = new PHPExcel();
        $objExcel->setActiveSheetIndex(0);
        $objActSheet = $objExcel->getActiveSheet();
        $objActSheet->setTitle("游戏帐号出售列表");
        $objActSheet->getColumnDimension('B')->setWidth(30);
        $objActSheet->getColumnDimension('D')->setWidth(20);
        $objActSheet->getColumnDimension('E')->setWidth(20);
        $objActSheet->setCellValue("A1", "做号日期");
        $objActSheet->setCellValue("B1", "订单号");
        $objActSheet->setCellValue("C1", "渠道");
        $objActSheet->setCellValue("D1", "游戏名称");
        $objActSheet->setCellValue("E1", "区服名称");
        $objActSheet->setCellValue("F1", "出仓代币");
        $objActSheet->setCellValue("G1", "进仓代币");
        $objActSheet->setCellValue("H1", "购买人");
        $objActSheet->setCellValue("I1", "角色名");
        $objActSheet->setCellValue("J1", "收入金额");
        $objActSheet->setCellValue("K1", "收款方式");
        $objActSheet->setCellValue("L1", "登录帐号");
        $objActSheet->setCellValue("M1", "登录密码");
        $objActSheet->setCellValue("N1", "帐号类型");
        $objActSheet->setCellValue("O1", "出售状态");
        $objActSheet->setCellValue("P1", "出号人员");
        $objActSheet->setCellValue("Q1", "出号时间");
        $n = 2;
        foreach($data as $info){
            $objActSheet->setCellValue("A".$n, date("Y/m/d",$info['do_time']));
            $objActSheet->setCellValueExplicit("B".$n, $info['order_id'],PHPExcel_Cell_DataType::TYPE_STRING);
            $objActSheet->setCellValue("C".$n, $this->channel[$info['channel']]);
            $objActSheet->setCellValue("D".$n, $info['app_name']);
            $objActSheet->setCellValue("E".$n, $info['service_name']);
            $objActSheet->setCellValue("F".$n, $info["exit_depot"]);
            $objActSheet->setCellValue("G".$n, $info["enter_depot"]);
            $objActSheet->setCellValue("H".$n, $info["buy_name"]);
            $objActSheet->setCellValue("I".$n, $info["role_name"]);
            $objActSheet->setCellValue("J".$n, $info["in_money"]);
            if($info['pay_mode'] == 1){
                $objActSheet->setCellValue("K".$n, "微信");
            }elseif($info['pay_mode'] == 2){
                $objActSheet->setCellValue("K".$n, "支付宝");
            }else{
                $objActSheet->setCellValue("K".$n, "无");
            }
            $objActSheet->setCellValueExplicit("L".$n, $info['login_account'],PHPExcel_Cell_DataType::TYPE_STRING);
            $objActSheet->setCellValueExplicit("M".$n, $info['login_pwd'],PHPExcel_Cell_DataType::TYPE_STRING);
            if($info['type'] == 1){
                $objActSheet->setCellValue("N".$n, "空号");
            }elseif($info['type'] == 2){
                $objActSheet->setCellValue("N".$n, "整号");
            }
            if($info['status'] == 0){
                $objActSheet->setCellValue("O".$n, "待出售");
            }elseif($info['status'] == 1){
                $objActSheet->setCellValue("O".$n, "已出售");
            }
            $objActSheet->setCellValue("P".$n, $info['account']);
            $objActSheet->setCellValue("Q".$n, date("Y/m/d",$info['sell_time']));
            $n++;
        }
        $title = iconv("UTF-8", "GB2312//IGNORE","游戏帐号出售列表-".$str_now.'.xls');
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

    public function tpl_down(){
        set_time_limit(0);
        $str_now = date("Y-m-d H:i:s",strtotime("now"));
        $objExcel = new PHPExcel();
        $objExcel->setActiveSheetIndex(0);
        $objActSheet = $objExcel->getActiveSheet();
        $objActSheet->setTitle("游戏帐号出售");
        $objActSheet->setCellValue("A1", "做号日期");
        $objActSheet->setCellValue("B1", "游戏名称");
        $objActSheet->setCellValue("C1", "区服名称");
        $objActSheet->setCellValue("D1", "账号");
        $objActSheet->setCellValue("E1", "密码");
        $objActSheet->setCellValue("F1", "职业");
        $objActSheet->setCellValue("G1", "性别");
        $objActSheet->setCellValue("H1", "角色名");
        $objActSheet->setCellValue("I1", "充值金额");
        $objActSheet->setCellValue("J1", "进仓代币");
        $objActSheet->setCellValue("K1", "出仓代币");
        $objActSheet->setCellValue("L1", "VIP");
        $objActSheet->setCellValue("M1", "类型");
        $objActSheet->setCellValue("N1", "状态");
        $objActSheet->setCellValue("O1", "收入金额");
        $objActSheet->setCellValue("P1", "付款方式");
        $objActSheet->setCellValue("Q1", "购买日期");
        $objActSheet->setCellValue("R1", "购买人");
        $objActSheet->setCellValue("S1", "出号人");
        $objActSheet->setCellValue("T1", "账号备注信息");
        $objActSheet->setCellValueExplicit("A2", "20180607",PHPExcel_Cell_DataType::TYPE_STRING);
        $objActSheet->setCellValue("B2", "");
        $objActSheet->setCellValue("C2", "");
        $objActSheet->setCellValue("D2", "");
        $objActSheet->setCellValue("E2", "");
        $objActSheet->setCellValue("F2", "");
        $objActSheet->setCellValue("G2", "");
        $objActSheet->setCellValue("H2", "");
        $objActSheet->setCellValue("I2", "");
        $objActSheet->setCellValue("J2", "");
        $objActSheet->setCellValue("K2", "");
        $objActSheet->setCellValue("L2", "");
        $objActSheet->setCellValue("M2", "");
        $objActSheet->setCellValue("N2", "");
        $objActSheet->setCellValue("I2", "");
        $objActSheet->setCellValue("O2", "");
        $objActSheet->setCellValue("P2", "");
        $objActSheet->setCellValueExplicit("Q2", "20180607",PHPExcel_Cell_DataType::TYPE_STRING);
        $objActSheet->setCellValue("R2", "");
        $objActSheet->setCellValue("S2", "");
        $objActSheet->setCellValue("T2", "");
        $title = iconv("UTF-8", "GB2312//IGNORE","游戏帐号出售导入模版-".$str_now.".xls");
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

    public function business_order_export(){
        $params = $_GET;
        $user_info = $this->DAO->get_user_info($_SESSION['usr_id']);
        if($_SESSION['group_id'] == '1' ){
            $order_list = $this->DAO->get_all_business_order_list($params);
        }elseif($_SESSION['group_id'] == '15' && !$user_info['p1'] && !$user_info['p2']){
            $order_list = $this->DAO->get_all_business_order_list($params,$_SESSION['usr_id']);
        }else{
            die("您没有该目录的权限，请联系管理员");
        }
        if($order_list){
            $business_inside = new business_inside_admin();
            $business_inside->group_master_excel_out($order_list,1);
        }else{
            echo "没有数据需要导出！";
        }
    }

    public function business_money_export(){
        $params = $_GET;
        if($params['start_time']){
            if($params['collect_type'] == '1'){
                $params['start'] = date('Ym',strtotime($params['start_time']));
            }else{
                $params['start'] = date('Ymd',strtotime($params['start_time']));
            }
        }
        if($params['end_time']){
            if($params['collect_type'] == '1'){
                $params['end'] = date('Ym',strtotime($params['end_time']));
            }else{
                $params['end'] = date('Ymd',strtotime($params['end_time']));
            }
        }
        $user_info = $this->DAO->get_user_info($_SESSION['usr_id']);
        if($_SESSION['group_id'] == '1'){
            $user_list = $this->DAO->get_business_list();
            $list = '';
            if($user_list){
                foreach($user_list as $key=>$data){
                    $list .= $data['id'].',';
                }
            }
            $order_list = $this->DAO->get_all_business_money_list($params,trim($list,','));
        }elseif($_SESSION['group_id'] == '15' && (!$user_info['p1'] || !$user_info['p2'])){
            $order_list = $this->DAO->get_all_business_money_list($params,$_SESSION['usr_id']);
        }else{
            die("您没有该目录的权限，请联系管理员");
        }
        if($order_list){
            $business_inside = new business_inside_admin();
            $business_inside->group_money_excel_out($order_list,1);
        }else{
            echo "没有数据需要导出！";
        }
    }

    public function repair_log(){
        if($_SESSION['usr_id'] != '84'){
            die('您没有该目录的权限，请联系管理员');
        }
        $params = $this->get_params($_POST,$_GET);
        $apps = $this->DAO->get_app_list();
        if($params['app_id']){
            $service_list = $this->DAO->get_service_list($params['app_id']);
        }
        $personal_list = $this->DAO->get_personal_all_list();
        $repair_list = $this->DAO->get_repair_list($this->page,$params);
        $page = $this->pageshow($this->page, "business_inside_second.php?act=repair_log&");
        $this->assign("page_bar", $page->show());
        $this->assign('apps',$apps);
        $this->assign('params',$params);
        $this->assign('personal_list',$personal_list);
        $this->assign('service_list',$service_list);
        $this->assign('repair_list',$repair_list);
        $this->display('chamber/business_repair_log.html');
    }

    public function add_repair(){
        if($_SESSION['usr_id'] != '84'){
            die('您没有该目录的权限，请联系管理员');
        }
        $apps = $this->DAO->get_app_list();
        $personal_list = $this->DAO->get_personal_all_list();
        $this->assign('apps',$apps);
        $this->assign('personal_list',$personal_list);
        $this->display('chamber/business_repair_add.html');
    }

    public function do_add_repair(){
        if($_SESSION['usr_id'] != '84'){
            $this->error_msg('您没有该目录的权限，请联系管理员');
        }
        $params = $_POST;

        if(!$params['app_id'] || !$params['service_id'] || !$params['user_id'] || !$params['type'] || !$params['desc'] || !$params['stock_num']){
            $this->error_msg('缺少必填项');
        }
        if(strlen($params['desc'])<5){
            $this->error_msg('备注信息不能少于5个字');
        }
        $stock_info = $this->DAO->get_stock_info($params,$params['user_id']);
        if($params['type'] == '1'){
            $params['new_stock_balance'] = $stock_info['stock_balance'] + $params['stock_num'];
            $params['new_stock_collect'] = $stock_info['stock_collect'];
        }elseif($params['type'] == '2'){
            $params['new_stock_collect'] = $stock_info['stock_collect'] + $params['stock_num'];
            $params['new_stock_balance'] = $stock_info['stock_balance'];
        }
        $this->DAO->insert_stock_record($params,$stock_info,$_SESSION['usr_id']);
        $this->DAO->insert_repair_log($params,$stock_info,$_SESSION['usr_id']);
        $this->DAO->update_stock_info($params,$params['user_id']);
        $this->succeed_msg();
    }
}