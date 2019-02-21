<?php
COMMON('baseCore','uploadHelper');
DAO('orders_info_dao');

class orders_info_web extends baseCore{

    public $DAO;
    public $id;
    public $p_type;
    public $pay_channel;

    public function __construct(){
        parent::__construct();
        $this->DAO = new orders_info_dao();
        $this->p_type=array(
            "1"=>'首充号',
            "2"=>'首充号续充',
            "3"=>'代充',
            "4"=>'账号',
            "5"=>'游戏币',
            "6"=>'道具',
            "8"=>"苹果内购"
        );
        $this->pay_channel=array(
            "1"=>'支付宝',
            "2"=>'网银',
            "3"=>'财付通'
        );
    }

    public function get_orders_list(){
        $params=$_POST;
        if(!empty($params['time'])){
            $params['buy_time']=strtotime($params['time']);
        }
        if(!empty($params['time2'])){
            $params['buy_time2']=strtotime($params['time2']);
        }

        $shop_list = $this->DAO->get_shop_list();
        $game_list= $this->DAO->get_game_list();
        //$servs_list=$this->DAO->get_game_servs_list();
        $channels_list=$this->DAO->get_channels_list();
        $dataList=$this->DAO->get_orders_list($params,$this->pageCurrent);
        $this->pageInfo($this->pageCurrent);
        $this->assign("shop_list",$shop_list);
        $this->assign("game_list",$game_list);
        $this->assign("servs_list","");
        $this->assign("channels_list",$channels_list);
        $this->assign("dataList", $dataList);
        $this->assign("params",$params);
        $this->assign("p_type",$this->p_type);
        $this->assign("pay_channel",  $this->pay_channel);
        $this->display("orders_info_list.html");
    }

    public function order_edit_view($id){
        $this->assign("id", $id);
        $this->display("orders_info_edit.html");
    }

    public function order_do_edit(){
        $params=$_POST;
        if(!$params['game_user']){
            echo json_encode($this->error_msg("请输入首充账号"));
            exit;
        }
        if(!$params['game_pwd']){
            echo json_encode($this->error_msg("请输入密码"));
            exit;
        }
        $info=$this->DAO->isexist_game_user($params['game_user']);
        if($info){
            echo json_encode($this->error_msg("该首充账号已售出"));
            exit;
        }
        if(!$_FILES['pay_img']['tmp_name']){
            echo json_encode($this->error_msg("请上传支付截图"));
            exit;
        }
        $params['pay_img']=$this->up_img('pay_img',ORDER_IMG);
        $this->DAO->do_order_edit($params);
        $this->add_oredr_logs($params['id']);
        $detail= $this->DAO->get_order_detail($params['id']);
        $active = $this->DAO->get_active_info($detail);
        if($active){
            $this->DAO->upd_active_info($detail);
        }
        echo json_encode($this->succeed_msg("首充号录入成功","orders_list"));
    }

    public function finish_order_view($id){
        $this->assign("id", $id);
        $this->display("finish_order_info.html");
    }


    public function do_order_finish(){
        $params=$_POST;
        if(!$_FILES['pay_img']['tmp_name']){
            echo json_encode($this->error_msg ("请上传支付截图"));
            exit;
        }
        $params['pay_img']=$this->up_img('pay_img',ORDER_IMG);
        $this->DAO->do_order_finish($params);
        //添加余额明细
        $user_id = $this->DAO->get_order_user_id($params['id'])['user_id'];
        $money = $this->DAO->get_order_user_id($params['id'])['pay_money'];
        if($user_id>1){
            $params['user_id'] = $user_id;
            $params['money']   = $money;
            $this->DAO->add_user_balance_detail($params);
            //更新账户余额
            $balance = $this->DAO->get_user_balance($user_id)['balance'];
            $balance = $balance + $money;
            $this->DAO->update_user_balance($balance,$user_id);
        }
        $this->add_oredr_logs($params['id']);
        echo json_encode($this->succeed_msg("结单成功","orders_list"));
    }
    public function audit_order_view($id){
        $img_list = $this->DAO->get_order_imgs($id);
        $this->assign("id", $id);
        $this->display("finish_order_info.html");
    }


    public function do_audit_order_view(){
        $params=$_POST;
        if(!$_FILES['pay_img']['tmp_name']){
            echo json_encode($this->error_msg("请上传支付截图"));
            exit;
        }
        $params['pay_img']=$this->up_img('pay_img',ORDER_IMG);
        $this->DAO->do_order_finish($params);
        $this->add_oredr_logs($params['id']);
        echo json_encode($this->succeed_msg("结单成功","orders_list"));
    }


    public function get_order_detail($id){
//        $this->open_debug();
        $order_detail= $this->DAO->get_order_detail($id);
        if($order_detail['type']==1){
            $tags = $this->DAO->get_tags($order_detail["game_id"]);
            $this->assign("tags", $tags['tags']);
//            $tags = $this->set_product_tag($tags["tags"]);
        }
        if($order_detail['attr']!="null"){
            $order_detail["attr"]=implode(",",json_decode($order_detail['attr']));
        }
        $servs = $this->DAO->get_game_ch_servs($order_detail['game_id'], $order_detail['game_channel']);
        $services = $this->DAO->get_services();
        
        $this->assign("order_detail", $order_detail);
        $this->assign("servs", $servs);
        $this->assign("services", $services);
        $this->display("order_detail.html");
    }

    protected function set_product_tag($tags){
        $atts = array();
        if(!$tags){
            $this->assign("tags",array());
            return false;
        }
        $tags = explode("\n", $tags);
        if(!is_array($tags))return false;

        foreach($tags as $k=>$v){
            if($v){
                $tag = explode("：",$v);
                var_dump($tag);
                $atts[$tag[0]] = explode("|",$tag[1]);
            }
        }
        $this->assign("tags", $atts);
    }

    public function get_artificial_orders(){
        $params=$_POST;
        $game_list= $this->DAO->get_game_list();
        //$servs_list=$this->DAO->get_game_servs_list();
        $channels_list=$this->DAO->get_channels_list();
        $dataList=$this->DAO->get_artificial_orders_list($params,$this->pageCurrent);
        $this->assign("game_list",$game_list);
        $this->assign("servs_list","");
        $this->assign("channels_list",$channels_list);
        $this->assign("dataList", $dataList);
        $this->assign("params",$params);
        $this->display("artificial_orders_list.html");
    }

    public function get_artificia_orders_info($id){
        $order_detail= $this->DAO->get_artificia_orders_info($id);
        $this->assign("order_info", $order_detail);
        $this->display("artificial_order_examine.html");
    }

    public function do_artificia_orders_examine(){
        $params=$_POST;
        $this->DAO->upd_artificia_orders_info($params);
        echo json_encode($this->succeed_msg("审核状态已修改","artificial_orders"));
    }

    public function order_gift_code_view($id){
        $this->assign("id", $id);
        $this->display("orders_gift_code.html");
    }

    public function do_gift_code_save(){
        $params=$_POST;
        if(!$params['gift_code']){
            echo json_encode($this->error_msg("请输入礼包码"));
            exit;
        }
        $this->DAO->do_gift_code_save($params);
        $this->add_oredr_logs($params['id']);
        echo json_encode($this->succeed_msg("礼包码赠送成功","orders_list"));
    }

    public function get_order_imgs($order_id){
        $img_list=$this->DAO->get_order_imgs($order_id,0);
        $this->assign("order_id", $order_id);
        $this->assign("img_list", $img_list);
        $this->display("orders_img_list.html");
    }
    public function get_sell_order_imgs($order_id){
        $img_list=$this->DAO->get_order_imgs($order_id,1)['0']['img'];
        $img_list = explode(',',$img_list);
        $this->assign("order_id", $order_id);
        $this->assign("img_list", $img_list);
        $this->display("sell_orders_img_list.html");
    }

    public function order_imgs_add_view(){
        $order_id=paramUtils::intByGET("order_id",false);
        $this->assign("order_id", $order_id);
        $this->display("orders_img_add.html");
    }

    public function do_order_imgs_add(){
        $params['order_id']=$_POST['order_id'];
        $params['admin_id']=$_SESSION["usr_id"];
        if(!$_FILES['imgs']['tmp_name']){
            echo json_encode($this->error_msg("请选择你要上传的图片"));
            exit;
        }
        $img_path=$this->up_imgs();
        foreach($img_path as $img){
            $params['img']=$img['img'];
            $this->DAO->add_order_img($params);
        }

        echo json_encode($this->succeed_msg("图片上传成功","get_order_imgs"));
    }

    public function del_order_img($id){
        $this->DAO->del_order_img($id);
        echo json_encode($this->unclose_succeed_msg("删除成功","get_order_imgs"));
    }

    //通过卖家发货
    public function orders_audit($id){
        $this->assign("id", $id);
        $this->display("orders_audit_view.html");
    }

    public function do_orders_audit(){
        $params           = $_POST;
        $params['status'] = 2;
        $this->DAO->update_orders_status($params);
        echo json_encode($this->succeed_msg("卖家发货审核成功", "products_list"));
    }

    public function refuse($id){
        $this->assign("id", $id);
        $this->display("orders_refuse_view.html");
    }

    public function do_refuse(){
        $params = $_POST;
        $params['status'] = 11;
        $this->DAO->update_orders_status($params);
        echo json_encode($this->succeed_msg("卖家发货拒绝成功", "products_list"));
    }
    //卖家发货详情
    public function seller_delivery_detail($id){
        $img_info = $this->DAO->get_order_imgs($id);
        $img_list = array();
        foreach ($img_info as $value) {
            $img_list[] = $value['img'];
        }
        $this->assign("img_list", $img_list);
        $this->display("seller_delivery_detail.html");
    }




    //订单操作日志
    public function add_oredr_logs($id){
        $detail= $this->DAO->get_order_detail($id);
        $params['admin_id']=$_SESSION["usr_id"];
        $detail_arr=array(
            'id'=>$detail['id'],
            'order_id'=>$detail['order_id'],
            'buyer_id'=>$detail['buyer_id'],
            'product_id'=>$detail['product_id'],
            'amount'=>$detail['id'],
            'unit_price'=>$detail['unit_price'],
            'money'=>$detail['money'],
            'pay_money'=>$detail['pay_money'],
            'game_id'=>$detail['game_id'],
            'serv_id'=>$detail['serv_id'],
            'game_channel'=>$detail['game_channel'],
            'seller_id'=>$detail['seller_id'],
            'status'=>$detail['status'],
            'buy_time'=>$detail['buy_time'],
            'pay_time'=>$detail['pay_time'],
            'ship_time'=>$detail['ship_time'],
            'pay_channel'=>$detail['pay_channel'],
            'channel_order_id'=>$detail['channel_order_id'],
            'bank_order_id'=>$detail['bank_order_id'],
            'payer'=>$detail['payer'],
            'ship_memo'=>$detail['ship_memo'],
            'pay_memo'=>$detail['pay_memo'],
            'qq'=>$detail['qq'],
            'tel'=>$detail['tel'],
            'discount'=>$detail['discount'],
            'discount_in'=>$detail['discount_in'],
            'role_name'=>$detail['role_name'],
            'role_back_name'=>$detail['role_back_name'],
            'service_id'=>$detail['service_id'],
            'game_user'=>$detail['game_user'],
            'game_pwd'=>$detail['game_pwd'],
            'attr'=>$detail['attr'],
            'gift_code'=>$detail['gift_code']
        );
        $params['do']=json_encode($detail_arr);
        $this->DAO->add_order_logs($params);
    }

    protected function up_imgs(){
        if($_FILES['imgs']['tmp_name'] && $_FILES['imgs']['tmp_name'][0]){
            $imgs = $this->batch_up_img('imgs', ORDER_IMG);
            foreach($imgs as $key=>$img){
                if($img){
                    $img_path[]['img']=$img;
                }
            }
        }
        return $img_path;
    }

    public function order_refund_view($id){
        $this->assign("id", $id);
        $this->display("refund_order_view.html");
    }

    public function do_order_refund(){
        $params=$_POST;
        if(!$_FILES['refund_img']['tmp_name']){
            echo json_encode($this->error_msg("请上传退款截图"));
            exit;
        }
        $params['refund_img']=$this->up_img('refund_img',ORDER_IMG);
        $this->DAO->do_order_refund($params);
        $info = $this->DAO->get_order_relation($params['id']);
        if($info){
            $this->DAO->update_order_time($params['id']);
        }
        $this->add_oredr_logs($params['id']);
        echo json_encode($this->succeed_msg("退款成功","orders_list"));
    }

    public function detail_edit(){
        $params=$_POST;
        if(empty($params['qq'])||!$params['qq']){
            die(json_encode($this->error_msg("QQ不能为空")));
        }
        if(empty($params['tel'])||!$params['tel']){
            die(json_encode($this->error_msg("电话不能为空")));
        }
        if($params['type']==1){
            if(empty($params['role_name'])||!$params['role_name']){
                die(json_encode($this->error_msg("角色名不能为空")));
            }
            if(!empty($params['attr'])||$params['attr']){
                $params["attr"]=json_encode(explode(",",$params['attr']),JSON_UNESCAPED_UNICODE);
            }
        }elseif ($params['type']==2 ||$params['type']==3){
            if(empty($params['game_user'])||!$params['game_user']){
                die(json_encode($this->error_msg("代充账号不能为空")));
            }
            if(empty($params['game_pwd'])||!$params['game_pwd']){
                die(json_encode($this->error_msg("代充密码不能为空")));
            }
            if(empty($params['role_name'])||!$params['role_name']) {
                die(json_encode($this->error_msg("代充角色名不能为空")));
            }
        }
        $this->DAO->upd_order_info($params);
        die(json_encode($this->succeed_msg("数据更新成功","orders_list")));
    }

    //导出
    public function export(){
        set_time_limit(0);
        $params=$_GET;
        if(!empty($params['time'])){
            $params['buy_time']=strtotime($params['time']);
        }
        if(!empty($params['time2'])){
            $params['buy_time2']=strtotime($params['time2']);
        }
        $channel_list=$this->DAO->get_channels_list();
        $channels_list=array();
        foreach($channel_list as $key=>$value){
            $channels_list[$value['id']]=$value['channel_name'];
        }
        $dataList=$this->DAO->all_orders_list($params);
        if($dataList){
            // $this->excel_out($dataList);
            $this->master_excel_out($dataList,$channels_list);
            echo json_encode($this->succeed_msg("导出成功","orders_list"));
        }else{
            echo json_encode($this->error_msg("没有要导出的数据","orders_list"));
        }
    }

    // 表格字段需求更新 <zbc><2016-7-13>
    private function master_excel_out($data,$ch_list){
        set_time_limit(0);
        $str_now=date("Y-m-d H:i:s",strtotime("now"));
        $objExcel = new PHPExcel();
        $objExcel->setActiveSheetIndex(0);
        $objActSheet = $objExcel->getActiveSheet();
        $objActSheet->setTitle("订单信息");
        $objActSheet->setCellValue("A1", "日期");
        $objActSheet->setCellValue("B1", "时间");
        $objActSheet->setCellValue("C1", "游戏名");
        $objActSheet->setCellValue("D1", "游戏区服");
        $objActSheet->setCellValue("E1", "账号");
        $objActSheet->setCellValue("F1", "线上66173订单号");
        $objActSheet->setCellValue("G1", "数量");
        $objActSheet->setCellValue("H1", "单价");
        $objActSheet->setCellValue("I1", "总价");
        $objActSheet->setCellValue("J1", "出售折扣");
        $objActSheet->setCellValue("K1", "实收");
        $objActSheet->setCellValue("L1", "店铺");
        $objActSheet->setCellValue("M1", "支付渠道");
        $objActSheet->setCellValue("N1", "购买人ID");
        $objActSheet->setCellValue("O1", "商品");
        $objActSheet->setCellValue("P1", "渠道");
        $objActSheet->setCellValue("Q1", "订单来源");
        $n = 2;
        foreach($data as $info){
            $objActSheet->setCellValue("A".$n, date("Y-m-d", $info['buy_time']));
            $objActSheet->setCellValue("B".$n, date("H:i:s", $info['buy_time']));
            $objActSheet->setCellValue("C".$n, $info['game_name']);
            $objActSheet->setCellValue("D".$n, $info['serv_name']);
            $objActSheet->setCellValueExplicit("E".$n, $info['game_user'],PHPExcel_Cell_DataType::TYPE_STRING);
            $objActSheet->setCellValueExplicit("F".$n, $info['order_id'],PHPExcel_Cell_DataType::TYPE_STRING);
            $objActSheet->setCellValue("G".$n, $info['amount']);
            $objActSheet->setCellValue("H".$n, $info['unit_price']);
            $objActSheet->setCellValue("I".$n, $info['money']);
            $objActSheet->setCellValue("J".$n, $info['discount']);
            $objActSheet->setCellValue("K".$n, $info['pay_money']);
            $objActSheet->setCellValue("L".$n, $info['shop_id']);
            switch ($info['pay_channel']) {
                case '1': $msg = '支付宝'; break;
                case '2': $msg = '网银'; break;
                case '5': $msg = '微信'; break;
                default: $msg = '其他'; break;
            }
            $objActSheet->setCellValue("M".$n, $msg);
            $objActSheet->setCellValue("N".$n, $info['buyer_id']);
            $objActSheet->setCellValue("O".$n, $this->p_type[$info['type']]);
            $objActSheet->setCellValue("P".$n, $ch_list[$info['game_channel']]);
            switch($info['platform']){
                case '0':
                    $objActSheet->setCellValue("Q".$n, "PC");
                    break;
                case '1':
                    $objActSheet->setCellValue("Q".$n, "M");
                    break;
                case '3':
                    $objActSheet->setCellValue("Q".$n, "店铺");
                    break;
                case '4':
                    $objActSheet->setCellValue("Q".$n, "APP");
                    break;
                case '7':
                    $objActSheet->setCellValue("Q".$n, "KY");
                    break;
            }
            $n++;

        }
        $title = iconv("UTF-8", "GB2312//IGNORE","订单信息-".$str_now.'.xls');
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

    //excel生成
//     private function excel_out($data){
// /*        ini_set('display_errors', 'On');*/
//         set_time_limit(0);
//         $str_now=date("Y-m-d H:i:s",strtotime("now"));
//         $objExcel = new PHPExcel();
//         //设置当前的sheet
//         $objExcel->setActiveSheetIndex(0);
//         $objActSheet = $objExcel->getActiveSheet();
//         $objActSheet->setTitle("订单信息");
//         $objActSheet->setCellValue("A1", "编号");
//         $objActSheet->setCellValue("B1", "商品");
//         $objActSheet->setCellValue("C1", "订单ID");
//         $objActSheet->setCellValue("D1", "购买人");
//         $objActSheet->setCellValue("E1", "商品ID");
//         $objActSheet->setCellValue("F1", "购买数量");
//         $objActSheet->setCellValue("G1", "单价");
//         $objActSheet->setCellValue("H1", "金额");
//         $objActSheet->setCellValue("I1", "应付金额");
//         $objActSheet->setCellValue("J1", "区服");
//         $objActSheet->setCellValue("K1", "渠道");
//         $objActSheet->setCellValue("L1", "销售人ID");
//         $objActSheet->setCellValue("M1", "订单状态");
//         $objActSheet->setCellValue("N1", "购买时间");
//         $objActSheet->setCellValue("O1", "支付时间");
//         $objActSheet->setCellValue("P1", "结单时间");
//         $objActSheet->setCellValue("Q1", "支付渠道");
//         $objActSheet->setCellValue("R1","支付渠道订单号");
//         $objActSheet->setCellValue("S1", "银行单号");
//         $objActSheet->setCellValue("T1", "支付人");
//         $objActSheet->setCellValue("U1", "发货备注");
//         $objActSheet->setCellValue("V1", "支付备注");
//         $objActSheet->setCellValue("W1","QQ");
//         $objActSheet->setCellValue("X1","电话");
//         $objActSheet->setCellValue("Y1", "折扣");
//         $objActSheet->setCellValue("Z1", "折扣依赖表ID");
//         $objActSheet->setCellValue("AA1", "首充号角色名");
//         $objActSheet->setCellValue("AB1", "首充号备用角色名");
//         $objActSheet->setCellValue("AC1", "客服");
//         $objActSheet->setCellValue("AD1", "代充账号");
//         $objActSheet->setCellValue("AE1", "代充账号密码");
//         $objActSheet->setCellValue("AF1", "首充号角色属性");
//         $objActSheet->setCellValue("AG1", "赠送礼包码");
//         $objActSheet->setCellValue("AH1", "店铺ID");
//         $objActSheet->setCellValue("AI1", "游戏名");
//         $objActSheet->setCellValue("AJ1", "游戏区服");

//         $n = 2;
//         foreach($data as $info){
//             if($info['status'] ==0){
//                 $info['status']="提交中";
//             }elseif($info['is_del']=='2'){
//                 $info['status']="已退款";
//             }else if($info['status'] ==1){
//                 $info['status']="已付款";
//             }else{
//                 $info['status']="已结单";
//             }
//             $objActSheet->setCellValue("A".$n, $info['id']);
//             $objActSheet->setCellValue("B".$n, $info['title']);
//             $objActSheet->setCellValueExplicit("C".$n, $info['order_id'],PHPExcel_Cell_DataType::TYPE_STRING);
//             $objActSheet->setCellValue("D".$n, $info['buyer_id']);
//             $objActSheet->setCellValue("B".$n, $info['product_id']);
//             $objActSheet->setCellValue("E".$n, $info['amount']);
//             $objActSheet->setCellValue("F".$n, $info['unit_price']);
//             $objActSheet->setCellValue("G".$n, $info['money']);
//             $objActSheet->setCellValue("H".$n, $info['pay_money']);
//             $objActSheet->setCellValue("I".$n, $info['game_id']);
//             $objActSheet->setCellValue("J".$n, $info['serv_id']);
//             $objActSheet->setCellValue("K".$n, $info['game_channel']);
//             $objActSheet->setCellValue("L".$n, $info['seller_id']);
//             $objActSheet->setCellValue("M".$n, $info['status']);
//             $objActSheet->setCellValue("N".$n, date("Y-m-d H:i:s", $info['buy_time']));
//             $objActSheet->setCellValue("O".$n, date("Y-m-d H:i:s", $info['pay_time'])=="1970-01-01 08:00:00"?"":date("Y-m-d H:i:s", $info['pay_time']));
//             $objActSheet->setCellValue("P".$n, date("Y-m-d H:i:s", $info['ship_time'])=="1970-01-01 08:00:00"?"":date("Y-m-d H:i:s", $info['ship_time']));
//             $objActSheet->setCellValue("Q".$n, $this->pay_channel[$info['pay_channel']]);
//             $objActSheet->setCellValueExplicit("R".$n, $info['channel_order_id'],PHPExcel_Cell_DataType::TYPE_STRING);
//             $objActSheet->setCellValue("S".$n, $info['bank_order_id']);
//             $objActSheet->setCellValue("T".$n, $info['payer']);
//             $objActSheet->setCellValue("U".$n, $info['ship_memo']);
//             $objActSheet->setCellValue("V".$n, $info['pay_memo']);
//             $objActSheet->setCellValueExplicit("W".$n, $info['qq'],PHPExcel_Cell_DataType::TYPE_STRING);
//             $objActSheet->setCellValue("X".$n, $info['tel']);
//             $objActSheet->setCellValue("Y".$n, $info['discount']);
//             $objActSheet->setCellValue("Z".$n, $info['discount_in']);
//             $objActSheet->setCellValue("AA".$n, $info['role_name']);
//             $objActSheet->setCellValue("AB".$n, $info['role_back_name']);
//             $objActSheet->setCellValue("AC".$n, $info['service_id']);
//             $objActSheet->setCellValue("AD".$n, $info['game_user']);
//             $objActSheet->setCellValue("AE".$n, $info['game_pwd']);
//             $objActSheet->setCellValue("AF".$n, $info['attr']);
//             $objActSheet->setCellValue("AG".$n, $info['gift_code']);
//             $objActSheet->setCellValue("AH".$n, $info['shop_id']);
//             $objActSheet->setCellValue("AI".$n, $info['game_name']);
//             $objActSheet->setCellValue("AJ".$n, $info['serv_name']);
//             $n++;
//         }
//         $title = iconv("UTF-8", "GB2312//IGNORE","订单信息-".$str_now.'.xls');
//         header("Content-type: text/html;charset=utf-8");
//         header("Content-Type: application/force-download");
//         header("Content-Type: application/octet-stream");
//         header("Content-Type: application/download");
//         header('Content-Disposition:inline;filename="'.$title.'"');
//         header("Content-Transfer-Encoding: binary");
//         header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
//         header("Pragma: no-cache");
//         $objWriter = PHPExcel_IOFactory::createWriter($objExcel, 'Excel5');
//         $objWriter->save('php://output');
//     }
}
?>