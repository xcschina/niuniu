<?php
COMMON('adminBaseCore','pageCore','uploadHelper','baseCore');
DAO('product_admin_dao');

class product_admin extends adminBaseCore{
    public $DAO;

    public function __construct(){
        parent::__construct();

        $this->DAO = new product_admin_dao();
    }

    public function product_list_view(){
        $params = $this->get_params($_POST,$_GET);
        $list = $this->DAO->get_product_list($this->page,$params);
        $aplist = $this->DAO->get_app_list();
        $page = $this->pageshow($this->page, "product.php?act=list&");
        $this->assign("datalist", $list);
        $this->assign("applist",$aplist);
        $this->assign("params",$params);
        $this->assign("page_bar", $page->show());
        $this->display("product_list.html");
    }

    public function product_add_view(){
        $app_list = $this->DAO->get_all_app();
        $this->assign("app_list", $app_list);
        $this->display("product_add.html");
    }

    public function product_do_add(){
        if(!$_POST || empty($_POST)){
            $this->error_msg("缺少必填项");
        }
        if(!$_POST['app_id'] || !$_POST['good_unit'] || !$_POST['good_price']){
            $this->error_msg("缺少必填项");
        }
        
        $id = $this->DAO->insert_product($_POST);
        if(!$id){
            $this->error_msg("保存失败,请刷新后重新操作");
        }
        $this->succeed_msg();
    }

    public function product_edit_view($id){
        $product_info = $this->DAO->get_product_info($id);
        $app_list = $this->DAO->get_all_app();
        $this->assign("app_list", $app_list);
        $this->assign("info", $product_info);
        $this->display("product_edit.html");
    }

    public function product_do_edit($id){
        if(!$_POST || empty($_POST)){
            $this->error_msg("缺少必填项");
        }
        if(!$_POST['app_id'] || !$_POST['good_unit'] || !$_POST['good_price']){
            $this->error_msg("缺少必填项");
        }
        
        $this->DAO->update_product($_POST, $id);
        $this->succeed_msg();
    }

    public function export(){
        set_time_limit(0);
        $dataList =  $this->DAO->get_product_list_nolimit($_GET);
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
        $objActSheet->setTitle("商品列表");
        $objActSheet->setCellValue("A1", "ID");
        $objActSheet->setCellValue("B1", "应用");
        $objActSheet->setCellValue("C1", "商品名称");
        $objActSheet->setCellValue("D1", "商品代码");
        $objActSheet->setCellValue("E1", "商品数量");
        $objActSheet->setCellValue("F1", "商品单位");
        $objActSheet->setCellValue("G1", "商品价格");
        $objActSheet->setCellValue("H1", "商品属性");
        $objActSheet->setCellValue("I1", "商品介绍");
        $objActSheet->setCellValue("J1", "商品状态");
        $objActSheet->setCellValue("K1", "充值类型");
        $n = 2;
        foreach($data as $info){
            $objActSheet->setCellValue("A".$n, $info['id']);
            $objActSheet->setCellValue("B".$n, "[".$info['app_id']."]".$info['app_name']);
            $objActSheet->setCellValue("C".$n, $info['good_name']);
            $objActSheet->setCellValue("D".$n, $info['good_code']);
            $objActSheet->setCellValue("E".$n, $info['good_amount']);
            $objActSheet->setCellValue("F".$n, $info['good_unit']);
            $objActSheet->setCellValue("G".$n, $info['good_price']);
            if($info['good_type'] == 1){
                $objActSheet->setCellValue("H".$n, "代币");
            }elseif($info['good_type'] == 2){
                $objActSheet->setCellValue("H".$n, "道具");
            }
            $objActSheet->setCellValue("I".$n, $info['good_intro']);
            if($info['status'] == 1){
                $objActSheet->setCellValue("J".$n, "上架");
            }else{
                $objActSheet->setCellValue("J".$n, "下架");
            }
            if($info['rec_type'] == 1){
                $objActSheet->setCellValue("K".$n, "牛牛");
            }elseif($info['rec_type'] == 2){
                $objActSheet->setCellValue("K".$n, "苹果");
            }

            $n++;
        }
        $title = iconv("UTF-8", "GB2312//IGNORE","商品列表-".$str_now.'.xls');
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
        $this->display('product_import_view.html');
    }
    public function import_file(){
        if (isset($_FILES['goods_file'])){
            $goods_file = $_FILES['goods_file'];
            if (preg_match("/\.xls$/",$goods_file['name'])){
                $type = "excel2003";
                $temp = dirname($_FILES['goods_file']['tmp_name']).'/temp_test.xls';
            }elseif(preg_match("/\.xlsx$/",$goods_file['name'])){
                $type = "excel2007";
                $temp = dirname($_FILES['goods_file']['tmp_name']).'/temp_test.xlsx';
            }else{
                $this->error_msg("上传文件后缀必须是xls或者xlsx！");
            }
//            if (!($goods_file['type']=='application/vnd.ms-excel' || $goods_file['type']=='application/octet-stream')){
//                $this->error_msg("上传文件必须是Excel！");
//            }
            if ($goods_file['size']>=1024*1024){
                $this->error_msg("上传文件大小不能超过1M！");
            }

            if (move_uploaded_file($goods_file['tmp_name'],$temp)){
                if (file_exists($temp)){
                    //执行数据解析
                    $app_goods_data = $this->excel_file_import($temp,$type);
                    unlink($temp);
                    //导入mysql
                    $id = $this->DAO->import_app_goods($app_goods_data);
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
        if ($highestColumm!='J'){
            unlink($file_name);
            $this->error_msg("Excel文件内容格式错误！");
        }
        //将字母转成数字
        $highestColumm = PHPExcel_Cell::columnIndexFromString($highestColumm);
        $app_goods_array = array();
        for($row=1;$row<=$highestRow;$row++){
            if ($row>1) $good_array = array();
            for($column=0;$column<$highestColumm;$column++){
                $value = $objActSheet->getCellByColumnAndRow($column,$row)->getValue();
                $column_name = PHPExcel_Cell::stringFromColumnIndex($column);
                if ($row==1){
                    switch ($column){
                        case 0:
                            if ($value!='应用ID')
                            {
                                unlink($file_name);
                                $this->error_msg("文件".$column_name.$row."必须是应用ID");
                            }
                            break;
                        case 1:
                            if ($value!='商品名称'){
                                unlink($file_name);
                                $this->error_msg("文件".$column_name.$row."必须是商品名称");
                            }
                            break;
                        case 2:
                            if ($value!='商品代码'){
                                unlink($file_name);
                                $this->error_msg("文件".$column_name.$row."必须是商品代码");
                            }
                            break;
                        case 3:
                            if ($value!='商品数量'){
                                unlink($file_name);
                                $this->error_msg("文件".$column_name.$row."必须是商品数量");
                            }
                            break;
                        case 4:
                            if ($value!='商品单位'){
                                unlink($file_name);
                                $this->error_msg("文件".$column_name.$row."必须是商品单位");
                            }
                            break;
                        case 5:
                            if ($value!='商品价格'){
                                unlink($file_name);
                                $this->error_msg("文件".$column_name.$row."必须是商品价格");
                            }
                            break;
                        case 6:
                            if ($value!='商品属性'){
                                unlink($file_name);
                                $this->error_msg("文件".$column_name.$row."必须是商品类型");
                            }
                            break;
                        case 7:
                            if ($value!='商品介绍'){
                                unlink($file_name);
                                $this->error_msg("文件".$column_name.$row."必须是商品介绍");
                            }
                            break;
                        case 8:
                            if ($value!='状态'){
                                unlink($file_name);
                                $this->error_msg("文件".$column_name.$row."必须是状态");
                            }
                            break;
                        case 9:
                            if ($value!='充值类型'){
                                unlink($file_name);
                                $this->error_msg("文件".$column_name.$row."必须是充值类型");
                            }
                            break;
                    }
                }else{
                    switch ($column){
                        case 0:
                            $good_array['app_id']=(int)$value;

                            if (!$good_array['app_id']){
                                unlink($file_name);
                                $this->error_msg("文件".$column_name.$row."格式错误！");
                            }
                            //查找是否有该游戏ID
                            $app_good_res = $this->DAO->check_apps($good_array['app_id']);
                            if (empty($app_good_res)){
                                unlink($file_name);
                                $this->error_msg("文件".$column_name.$row."该游戏ID不存在！");
                            }
                            break;
                        case 1:
                            $good_array['good_name']=$value;
                            break;
                        case 2:
                            $good_array['good_code']=$value;
                            break;
                        case 3:
                            $good_array['good_amount']=(int)$value;
                            if (!$good_array['good_amount']){
                                unlink($file_name);
                                $this->error_msg("文件".$column_name.$row."格式错误！");
                            }
                            break;
                        case 4:
                            $good_array['good_unit']=$value;
                            break;
                        case 5:
                            $good_array['good_price']=(float)$value;
                            if (!$good_array['good_price']){
                                unlink($file_name);
                                $this->error_msg("文件".$column_name.$row."格式错误！");
                            }
                            break;
                        case 6:
                            $good_array['good_type']=$value;
                            if ($good_array['good_type']=='代币'){
                                $good_array['good_type'] = 1;
                            }else if($good_array['good_type']=='道具'){
                                $good_array['good_type'] = 2;
                            }else{
                                unlink($file_name);
                                $this->error_msg("文件".$column_name.$row."格式错误！");
                            }
                            break;
                        case 7:
                            $good_array['good_intro']=$value;
                            break;
                        case 8:
                            $good_array['status']=$value;
                            if ($good_array['status']=='上架'){
                                $good_array['status'] = 1;
                            }else if($good_array['status']=='下架'){
                                $good_array['status'] = 0;
                            }else{
                                unlink($file_name);
                                $this->error_msg("文件".$column_name.$row."格式错误！");
                            }
                            break;
                        case 9:
                            $good_array['rec_type']=$value;
                            if ($good_array['rec_type']=='牛牛'){
                                $good_array['rec_type'] = 1;
                            }else if($good_array['rec_type']=='苹果'){
                                $good_array['rec_type'] = 2;
                            }else{
                                unlink($file_name);
                                $this->error_msg("文件".$column_name.$row."格式错误！");
                            }
                            break;
                    }
                }
            }
            if ($row>1) array_push($app_goods_array,$good_array);
        }
        return $app_goods_array;
    }

    public function tpl_down(){
        set_time_limit(0);
        $objExcel = new PHPExcel();
        $objExcel->setActiveSheetIndex(0);
        $objActSheet = $objExcel->getActiveSheet();
        $objActSheet->setTitle("游戏商品");
        $objActSheet->setCellValue("A1", "应用ID");
        $objActSheet->setCellValue("B1", "商品名称");
        $objActSheet->setCellValue("C1", "商品代码");
        $objActSheet->setCellValue("D1", "商品数量");
        $objActSheet->setCellValue("E1", "商品单位");
        $objActSheet->setCellValue("F1", "商品价格");
        $objActSheet->setCellValue("G1", "商品属性");
        $objActSheet->setCellValue("H1", "商品介绍");
        $objActSheet->setCellValue("I1", "状态");
        $objActSheet->setCellValue("J1", "充值类型");
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
        $title = iconv("UTF-8", "GB2312//IGNORE","商品导入模版.xls");
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