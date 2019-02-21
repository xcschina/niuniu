<?php
COMMON('adminBaseCore', 'pageCore', 'uploadHelper');
DAO('orders_info_dao');

class console_orders_info_web extends adminBaseCore{

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
        $params   = $this->get_params($_POST, $_GET);
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
        $dataList=$this->DAO->get_orders_list($this->page, $params);
        $page     = $this->pageshow($this->page, "console_orders_info.php?act=orders_list&");
        $this->assign("shop_list",$shop_list);
        $this->assign("game_list",$game_list);
        $this->assign("servs_list","");
        $this->assign("channels_list",$channels_list);
        $this->assign("dataList", $dataList);
        $this->assign("params",$params);
        $this->assign("p_type",$this->p_type);
        $this->assign("pay_channel",  $this->pay_channel);
        $this->assign("page_bar", $page->show());
        $this->display("console_orders_info_list.html");
    }


    public function order_refund_view($id){
        $this->assign("id", $id);
        $this->display("console_refunud_order_view.html");
    }

    public function do_order_refund(){
        $params=$_POST;
        if(!$_FILES['refund_img']['tmp_name']){
            $this->error_msg("请上传退款截图");
            exit;
        }
        $params['refund_img']=$this->up_img('refund_img',ORDER_IMG);
        $this->DAO->do_order_refund($params);
        $this->succeed_msg();
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
            $this->master_excel_out($dataList,$channels_list);

        }else{
            echo "没有数据需要导出！";
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
}
?>