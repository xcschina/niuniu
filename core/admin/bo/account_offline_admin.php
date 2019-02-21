<?php
COMMON('adminBaseCore','pageCore');
DAO('account_offline_admin_dao');

class account_offline_admin extends adminBaseCore {
    public $DAO;

    public function __construct(){
        parent::__construct();
        $this->DAO = new account_offline_admin_dao();
    }

    public function acount_offline_list(){
        if($_POST){
            $_SESSION['account_offline_list'] = $params = $_POST;
        }elseif(!$_GET['page']){
            unset($_SESSION['account_offline_list']);
        }else{
            $params = $_SESSION['account_offline_list'];
        }
        $game_list = $this->DAO->get_search_list("game");
        if ($params['game']){
            $game_area_list = $this->DAO->get_game_area($params['game']);
        }
        $channel_list = $this->DAO->get_search_list("channel");
        $channel_pay_list = $this->DAO->get_search_list("channel_pay");
        $input_id_list = $this->DAO->get_search_list("input_id");
        if ($input_id_list){
            $input_id = "";
            foreach ($input_id_list as $key=>$value){
                $input_id .=$value['input_id'].",";
            }
            $input_id = trim($input_id,",");
            $input_name_list = $this->DAO->get_input_name($input_id);
        }else{
            $input_name_list = array();
        }
        $datalist = $this->DAO->get_account_offline_list($this->page,$params);
        $page = $this->pageshow($this->page,"account_offline.php?act=list&");
        $this->assign("page_bar",$page->show());
        $this->assign("params",$params);
        $this->assign("datalist",$datalist);
        $this->assign("game_list",$game_list);
        $this->assign("game_area_list",$game_area_list);
        $this->assign("channel_list",$channel_list);
        $this->assign("channel_pay_list",$channel_pay_list);
        $this->assign("input_name_list",$input_name_list);
        $this->display("account_offline_list.html");
    }

    public function tpl_down(){
        set_time_limit(0);
        $objExcel = new PHPExcel();
        $objExcel->setActiveSheetIndex(0);
        $objActSheet = $objExcel->getActiveSheet();
        $objActSheet->setTitle("线下账单");
        $objActSheet->setCellValue("A1", "日期");
        $objActSheet->setCellValue("B1", "游戏");
        $objActSheet->setCellValue("C1", "渠道");
        $objActSheet->setCellValue("D1", "游戏区服");
        $objActSheet->setCellValue("E1", "账号");
        $objActSheet->setCellValue("F1", "支付渠道");
        $objActSheet->setCellValue("G1", "金额");
        $objActSheet->setCellValue("H1", "回款折扣");
        $objActSheet->setCellValue("I1", "回款");
        $objActSheet->setCellValue("J1", "支出金额");
        $objActSheet->setCellValue("K1", "收款渠道");
        $objActSheet->getStyle('A1:K1')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
        $objActSheet->getStyle( 'A1:K1')->getFill()->getStartColor()->setARGB('FF00B0F0');
        $objActSheet->setCellValue("A2", " ");
        $objActSheet->setCellValue("B2", " ");
        $objActSheet->setCellValue("C2", " ");
        $objActSheet->setCellValue("D2", " ");
        $objActSheet->setCellValue("E2", " ");
        $objActSheet->setCellValue("F2", " ");
        $objActSheet->setCellValue("G2", " ");
        $objActSheet->setCellValue("H2", " ");
        $objActSheet->setCellValue("I2", " ");
        $objActSheet->setCellValue("J2", " ");
        $objActSheet->setCellValue("K2", " ");
        $title = iconv("UTF-8", "GB2312//IGNORE","线下账单模版.xls");
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

    public function export(){
        $params = $_GET;
        $dataList = $this->DAO->get_account_offline_all($params);
        if($dataList){
            $this->excel_out($dataList);
        }else{
            echo "没有数据需要导出！";
        }
    }

    private function excel_out($data){
        set_time_limit(0);
        $str_now=date("Y-m-d H:i:s",strtotime("now"));
        $objExcel = new PHPExcel();
        $objExcel->setActiveSheetIndex(0);
        $objActSheet = $objExcel->getActiveSheet();
        $objActSheet->setTitle("线下账单");
        $objActSheet->setCellValue("A1", "日期");
        $objActSheet->setCellValue("B1", "游戏");
        $objActSheet->setCellValue("C1", "渠道");
        $objActSheet->setCellValue("D1", "游戏区服");
        $objActSheet->setCellValue("E1", "账号");
        $objActSheet->setCellValue("F1", "支付渠道");
        $objActSheet->setCellValue("G1", "金额");
        $objActSheet->setCellValue("H1", "回款折扣");
        $objActSheet->setCellValue("I1", "回款");
        $objActSheet->setCellValue("J1", "支出金额");
        $objActSheet->setCellValue("K1", "收款渠道");
        $objActSheet->getStyle('A1:K1')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
        $objActSheet->getStyle( 'A1:K1')->getFill()->getStartColor()->setARGB('FF00B0F0');
        $n = 2;
        foreach($data as $info){
            $objActSheet->setCellValue("A".$n, date("Y/m/d",$info['time']));
            $objActSheet->setCellValue("B".$n, $info['game']);
            $objActSheet->setCellValue("C".$n, $info['channel']);
            $objActSheet->setCellValue("D".$n, $info['game_area']);
            $objActSheet->setCellValue("E".$n, $info['game_account']);
            $objActSheet->setCellValue("F".$n, $info['channel_pay']);
            $objActSheet->setCellValue("G".$n, $info['money']);
            $objActSheet->setCellValue("H".$n, $info['payment_discount']);
            $objActSheet->setCellValue("I".$n, $info['payment']);
            $objActSheet->setCellValue("J".$n, $info['money_pay']);
            $objActSheet->setCellValue("K".$n, $info['channel_receivmoney']);
            $n++;
        }
        $title = iconv("UTF-8", "GB2312//IGNORE","线下账单-".$str_now.'.xls');
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

    public function import_view(){
        $this->display("account_offline_import_view.html");
    }

    public function import(){
        if (isset($_FILES['account_offline_file'])){
            $account_offline_file = $_FILES['account_offline_file'];
            if (preg_match("/\.xls$/",$account_offline_file['name'])){
                $type = "excel2003";
                $temp = dirname($_FILES['account_offline_file']['tmp_name']).'/temp_test.xls';
            }elseif(preg_match("/\.xlsx$/",$account_offline_file['name'])){
                $type = "excel2007";
                $temp = dirname($_FILES['account_offline_file']['tmp_name']).'/temp_test.xlsx';
            }else{
                $this->error_msg("上传文件后缀必须是xls或者xlsx！");
            }
            if ($account_offline_file['size']>=1024*1024){
                $this->error_msg("上传文件大小不能超过1M！");
            }
            if (move_uploaded_file($account_offline_file['tmp_name'],$temp)){
                if (file_exists($temp)){
                    //执行数据解析
                    $account_offline_data = $this->excel_file_import($temp,$type);
                    if (!$account_offline_data){
                        $this->error_msg("导入数据不能为空");
                    }
                    unlink($temp);
                    //导入mysql
                    $id = $this->DAO->import_account_offline($account_offline_data);
                    if (!$id){
                        $this->error_msg("导入失败！");
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

    private function excel_file_import($file_name,$type){
        if ($type=="excel2003"){
            $reader = PHPExcel_IOFactory::createReader('Excel5');
        }else{
            $reader = PHPExcel_IOFactory::createReader('Excel2007');
        }
        $excelObj = $reader->load($file_name);
        $objActSheet = $excelObj->getSheet(0);
        $highestRow = $objActSheet->getHighestRow();
        $highestColumm = $objActSheet->getHighestColumn();
        if ($highestColumm!='K'){
            unlink($file_name);
            $this->error_msg("Excel文件内容格式错误！");
        }
        //将字母转成数字
        $highestColumm = PHPExcel_Cell::columnIndexFromString($highestColumm);
        $account_offline_array = array();
        for($row=1;$row<=$highestRow;$row++){
            if ($objActSheet->getCellByColumnAndRow(0,$row)->getValue()=="")
                continue;
            if ($row>1) $good_array = array();
            for($column=0;$column<$highestColumm;$column++){
                if ($column==6 || $column==7 || $column==8 || $column==9){
                    $value = $objActSheet->getCellByColumnAndRow($column,$row)->getFormattedValue();
                }else{
                    $value = $objActSheet->getCellByColumnAndRow($column,$row)->getValue();
                }
                $column_name = PHPExcel_Cell::stringFromColumnIndex($column);
                if ($row==1){
                    switch ($column){
                        case 0:
                            if ($value!='日期')
                            {
                                unlink($file_name);
                                $this->error_msg("文件".$column_name.$row."必须是游戏名称");
                            }
                            break;
                        case 1:
                            if ($value!='游戏'){
                                unlink($file_name);
                                $this->error_msg("文件".$column_name.$row."必须是游戏区服");
                            }
                            break;
                        case 2:
                            if ($value!='渠道'){
                                unlink($file_name);
                                $this->error_msg("文件".$column_name.$row."必须是游戏账号");
                            }
                            break;
                        case 3:
                            if ($value!='游戏区服'){
                                unlink($file_name);
                                $this->error_msg("文件".$column_name.$row."必须是渠道");
                            }
                            break;
                        case 4:
                            if ($value!='账号'){
                                unlink($file_name);
                                $this->error_msg("文件".$column_name.$row."必须是支付渠道");
                            }
                            break;
                        case 5:
                            if ($value!='支付渠道'){
                                unlink($file_name);
                                $this->error_msg("文件".$column_name.$row."必须是金额");
                            }
                            break;
                        case 6:
                            if ($value!='金额'){
                                unlink($file_name);
                                $this->error_msg("文件".$column_name.$row."必须是回款折扣");
                            }
                            break;
                        case 7:
                            if ($value!='回款折扣'){
                                unlink($file_name);
                                $this->error_msg("文件".$column_name.$row."必须是回款");
                            }
                            break;
                        case 8:
                            if ($value!='回款'){
                                unlink($file_name);
                                $this->error_msg("文件".$column_name.$row."必须是支出金额");
                            }
                            break;
                        case 9:
                            if ($value!='支出金额'){
                                unlink($file_name);
                                $this->error_msg("文件".$column_name.$row."必须是收款渠道");
                            }
                            break;
                        case 10:
                            if ($value!='收款渠道'){
                                unlink($file_name);
                                $this->error_msg("文件".$column_name.$row."必须是录入时间");
                            }
                            break;
                    }
                }else{
                    switch ($column){
                        case 0:
                            $value = date("Y/m/d",PHPExcel_Shared_Date::ExcelToPHP($value));
                            $good_array['time']=strtotime($value);
                            break;
                        case 1:
                            $good_array['game']=$value;
                            if (!$good_array['game']){
                                unlink($file_name);
                                $this->error_msg("文件".$column_name.$row."游戏不能为空！");
                            }
                            break;
                        case 2:
                            $good_array['channel']=$value;
                            if (!$good_array['channel']){
                                unlink($file_name);
                                $this->error_msg("文件".$column_name.$row."渠道不能为空！");
                            }
                            break;
                        case 3:
                            $good_array['game_area']=$value;
                            if (!$good_array['game_area']){
                                unlink($file_name);
                                $this->error_msg("文件".$column_name.$row."游戏区服不能为空！");
                            }
                            break;
                        case 4:
                            $good_array['game_account']=$value;
                            if (!$good_array['game_account']){
                                unlink($file_name);
                                $this->error_msg("文件".$column_name.$row."游戏账号不能为空！");
                            }
                            break;
                        case 5:
                            $good_array['channel_pay']=$value;
                            if (!$good_array['channel_pay']){
                                unlink($file_name);
                                $this->error_msg("文件".$column_name.$row."支付渠道不能为空！");
                            }
                            break;
                        case 6:
                            $good_array['money']=(float)$value;
                            if (!$good_array['money']){
                                unlink($file_name);
                                $this->error_msg("文件".$column_name.$row."格式错误！");
                            }
                            break;
                        case 7:
                            $good_array['payment_discount']=$value;
                        case 8:
                            $good_array['payment']=(float)$value;
                        case 9:
                            $good_array['money_pay']=(float)$value;
                        case 10:
                            $good_array['channel_receivmoney']=$value;
                    }
                }
            }
            if ($row>1) array_push($account_offline_array,$good_array);
        }
        return $account_offline_array;
    }

    public function  get_game_area(){
        if ($_POST['game_name']){
            $game_area_list = $this->DAO->get_game_area($_POST['game_name']);
            if ($game_area_list){
                $this->succeed_msg($game_area_list);
            }else{
                $this->error_msg("该游戏没有区服");
            }
        }else{
            $this->error_msg("没有对应游戏");
        }
    }
}