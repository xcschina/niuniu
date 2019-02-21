<?php
COMMON('adminBaseCore', 'uploadHelper', 'pageCore');
DAO('products_info_dao');

class console_products_info_web extends adminBaseCore{

    public $DAO;
    public $id;
    public $p_type;

    public function __construct(){
        parent::__construct();
        $this->DAO    = new products_info_dao();
        $this->p_type = array("首充号", "首充号续充", "代充");
    }

    public function get_products_list(){
        $params        = $this->get_params($_POST, $_GET);
        $game_list     = $this->DAO->get_game_list();
        $channels_list = $this->DAO->get_channels_list();
        $dataList      = $this->DAO->get_products_list($params, $this->pageCurrent);
        $page          = $this->pageshow($this->page, "console_products_info.php?act=orders_list&");
        $this->assign("page_bar", $page->show());
        $this->assign("game_list", $game_list);
        $this->assign("servs_list", "");
        $this->assign("channels_list", $channels_list);
        $this->assign("dataList", $dataList);
        $this->assign("params", $params);
        $this->display("console_products_info_list.html");
    }

    public function products_audit($id){
        $this->assign("id", $id);
        $this->display("console_products_audit_view.html");
    }

    public function do_products_audit(){
        $params           = $this->get_params($_POST, $_GET);
        $params['status'] = 1;
        $this->DAO->update_products_status($params);
        $this->succeed_msg();
    }

    public function refuse($id){
        $this->assign("id", $id);
        $this->display("console_products_refuse_view.html");
    }

    public function do_refuse(){
        $params = $this->get_params($_POST, $_GET);
        $params['status'] = 3;
        $this->DAO->update_products_status($params);
        $this->succeed_msg();
    }

    public function export(){
        $params = $this->get_params($_POST, $_GET);
        $dataList = $this->DAO->get_all_data($params);
        if($dataList){
            $this->excel_out($dataList);
        } else{
            echo "没有数据需要导出！";
        }
    }

    private function excel_out($data){
        set_time_limit(0);
        $str_now  = date("Y-m-d H:i:s", strtotime("now"));
        $objExcel = new PHPExcel();
        $objExcel->setActiveSheetIndex(0);
        $objActSheet = $objExcel->getActiveSheet();
        $objActSheet->setTitle("商品列表");
        $objActSheet->setCellValue("A1", "ID");
        $objActSheet->setCellValue("B1", "游戏");
        $objActSheet->setCellValue("C1", "渠道");
        $objActSheet->setCellValue("D1", "游戏区服");
        $objActSheet->setCellValue("E1", "标题");
        $objActSheet->setCellValue("F1", "类型");
        $objActSheet->setCellValue("G1", "库存");
        $objActSheet->setCellValue("H1", "价格");
        $n = 2;
        foreach($data as $info){
            $objActSheet->setCellValue("A" . $n, $info['id']);
            $objActSheet->setCellValue("B" . $n, $info['game_name']);
            $objActSheet->setCellValue("C" . $n, $info['channel_name']);
            if($info['server_name']){
                $objActSheet->setCellValue("D" . $n, $info['server_name']);
            } else{
                $objActSheet->setCellValue("D" . $n, '全区全服');
            }

            $objActSheet->setCellValue("E" . $n, $info['title']);
            if($info['type'] == 4){
                $objActSheet->setCellValue("F" . $n, '账号');
            } elseif($info['type'] == 5){
                $objActSheet->setCellValue("F" . $n, '游戏币');
            } elseif($info['type'] == 6){
                $objActSheet->setCellValue("F" . $n, '道具');
            }

            $objActSheet->setCellValue("G" . $n, $info['stock']);
            $objActSheet->setCellValue("H" . $n, $info['price']);
            $n++;
        }
        $title = iconv("UTF-8", "GB2312//IGNORE", "商品列表-" . $str_now . '.xls');
        header("Content-type: text/html;charset=utf-8");
        header("Content-Type: application/force-download");
        header("Content-Type: application/octet-stream");
        header("Content-Type: application/download");
        header('Content-Disposition:inline;filename="' . $title . '"');
        header("Content-Transfer-Encoding: binary");
        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
        header("Pragma: no-cache");
        $objWriter = PHPExcel_IOFactory::createWriter($objExcel, 'Excel5');
        $objWriter->save('php://output');
    }


}