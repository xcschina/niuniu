<?php
COMMON('adminBaseCore','pageCore','uploadHelper','baseCore');
DAO('super_product_dao');

class super_product extends adminBaseCore{
    public $DAO;

    public function __construct(){
        parent::__construct();
        $this->DAO = new super_product_dao();
    }

    public function product_list_view(){
        $appid = $_GET['app_id'];
        $list = $this->DAO->get_product_list($this->page,$appid);
        $aplist = $this->DAO->get_app_list();
        if(!$appid){
            $page = $this->pageshow($this->page, "super_product.php?act=list&");
        }else{
            $page = $this->pageshow($this->page, "super_product.php?act=list&app_id=".$appid."&");
        }
        $this->assign("datalist", $list);
        $this->assign("applist",$aplist);
        $this->assign("app_id",$appid);
        $this->assign("page_bar", $page->show());
        $this->display("super_sdk/product_list.html");
    }

    public function product_add_view(){
        $app_list = $this->DAO->get_all_app();
        $this->assign("app_list", $app_list);
        $this->display("super_sdk/product_add.html");
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
        $this->display("super_sdk/product_edit.html");
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
        $app_id = $_GET['app_id'];
        $dataList =  $this->DAO->get_product_list_nolimit($app_id);
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

    public function import(){
        $this->display("super_sdk/product_import.html");
    }

    public function do_import(){
        if (isset($_FILES['goods_file'])){
            $goods_file = $_FILES['goods_file'];
            if (preg_match("/\.xls$/",$goods_file['name'])){
                $temp = dirname($_FILES['goods_file']['tmp_name']).'/temp_test.xls';
            }elseif(preg_match("/\.xlsx$/",$goods_file['name'])){
                $temp = dirname($_FILES['goods_file']['tmp_name']).'/temp_test.xlsx';
            }else{
                $this->error_msg("上传文件后缀必须是xls或者xlsx！");
            }
            if ($goods_file['size']>=1024*1024){
                $this->error_msg("上传文件大小不能超过1M！");
            }
            if (move_uploaded_file($goods_file['tmp_name'],$temp)){
                if (file_exists($temp)){
                    //执行数据解析
                    $channel_app_data = $this->excel_file_import($temp);
                    unlink($temp);
                    //导入mysql
                    foreach($channel_app_data as $data){
                        $id = $this->DAO->import_super_app_goods($data);
                        if (!$id) {
                            $this->error_msg("导入失败！");
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

    private function excel_file_import($file_name){
        $inputFileType = PHPExcel_IOFactory::identify($file_name);
        $objReader = PHPExcel_IOFactory::createReader($inputFileType);
        $excelObj = $objReader->load($file_name);
        $objActSheet = $excelObj->getSheet(0);
        $highestRow = $objActSheet->getHighestRow();
        $highestColumm = $objActSheet->getHighestColumn();
        if ($highestColumm!='I'){
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
                            if ($value != '应用ID')
                            {
                                unlink($file_name);
                                $this->error_msg("文件".$column_name.$row."必须是应用ID");
                            }
                            break;
                        case 1:
                            if ($value != '商品名称'){
                                unlink($file_name);
                                $this->error_msg("文件".$column_name.$row."必须是商品名称");
                            }
                            break;
                        case 2:
                            if ($value != '商品代码'){
                                unlink($file_name);
                                $this->error_msg("文件".$column_name.$row."必须是商品代码");
                            }
                            break;
                        case 3:
                            if ($value != '商品数量'){
                                unlink($file_name);
                                $this->error_msg("文件".$column_name.$row."必须是商品数量");
                            }
                            break;
                        case 4:
                            if ($value != '商品单位'){
                                unlink($file_name);
                                $this->error_msg("文件".$column_name.$row."必须是商品单位");
                            }
                            break;
                        case 5:
                            if ($value != '商品价格'){
                                unlink($file_name);
                                $this->error_msg("文件".$column_name.$row."必须是商品价格");
                            }
                            break;
                        case 6:
                            if ($value != '商品属性'){
                                unlink($file_name);
                                $this->error_msg("文件".$column_name.$row."必须是商品类型");
                            }
                            break;
                        case 7:
                            if ($value != '商品介绍'){
                                unlink($file_name);
                                $this->error_msg("文件".$column_name.$row."必须是商品介绍");
                            }
                            break;
                        case 8:
                            if ($value != '状态'){
                                unlink($file_name);
                                $this->error_msg("文件".$column_name.$row."必须是状态");
                            }
                            break;
                    }
                }else{
                    switch ($column){
                        case 0:
                            $good_array['app_id'] = (int)$value;

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
                            $good_array['good_name'] = $value;
                            break;
                        case 2:
                            $good_array['good_code'] = $value;
                            break;
                        case 3:
                            $good_array['good_amount'] = (int)$value;
                            if (!$good_array['good_amount']){
                                unlink($file_name);
                                $this->error_msg("文件".$column_name.$row."格式错误！");
                            }
                            break;
                        case 4:
                            $good_array['good_unit'] = $value;
                            break;
                        case 5:
                            $good_array['good_price'] = (float)$value;
                            if (!$good_array['good_price']){
                                unlink($file_name);
                                $this->error_msg("文件".$column_name.$row."格式错误！");
                            }
                            break;
                        case 6:
                            $good_array['good_type'] = $value;
                            if ($good_array['good_type'] == '代币'){
                                $good_array['good_type'] = 1;
                            }else if($good_array['good_type'] == '道具'){
                                $good_array['good_type'] = 2;
                            }else if($good_array['good_type'] == '未知'){
                                $good_array['good_type'] = 0;
                            }else{
                                unlink($file_name);
                                $this->error_msg("文件".$column_name.$row."格式错误！");
                            }
                            break;
                        case 7:
                            $good_array['good_intro'] = $value;
                            break;
                        case 8:
                            $good_array['status'] = $value;
                            if ($good_array['status'] == '上架'){
                                $good_array['status'] = 1;
                            }else if($good_array['status'] == '下架'){
                                $good_array['status'] = 0;
                            }else if($good_array['status'] == '未知'){
                                $good_array['status'] = 2;
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

    public function channel_list(){
        $params = $this->get_params($_POST,$_GET);
        $ch_code = $params['ch_code'];
        $data_list = $this->DAO->get_relation_list($this->page,$ch_code);
        $channel_list = $this->DAO->get_channel_all_list();
        $page = $this->pageshow($this->page, "super_product.php?act=channel_list&");
        $this->assign("page_bar", $page->show());
        $this->assign('ch_code',$ch_code);
        $this->assign('data_list',$data_list);
        $this->assign('channel_list',$channel_list);
        $this->display("super_sdk/product_channel_list.html");
    }

    public function add_channel(){
        $app_list = $this->DAO->get_channel_app_list();
        $this->assign('app_list',$app_list);
        $this->display("super_sdk/product_channel_add.html");
    }

    public function get_goods_list(){
        $result = array('code'=>0,'msg'=>'网络错误');
        $app_id = $_POST['app_id'];
        if(!$app_id){
            $result['mag'] = '缺少游戏ID';
            die(json_encode($result));
        }
        $goods_list = $this->DAO->get_goods_list($app_id);
        $channel_list = $this->DAO->get_channel_list($app_id);
        $result['code'] = 1;
        $result['msg'] = '查询成功';
        $result['goods_list'] = $goods_list;
        $result['channel_list'] = $channel_list;
        die(json_encode($result));
    }

    public function do_add_channel(){
        $params = $_POST;
        if(!$params['app_id'] || !$params['channel_id'] || !$params['goods_id'] || !$params['channel_goods']){
            $this->error_msg('缺少必填项');
        }
        $channel_info = $this->DAO->get_channel_info($params['channel_id']);
        if(!$channel_info){
            $this->error_msg('查无此渠道信息');
        }
        $relation_info = $this->DAO->get_relation($params);
        if($relation_info){
            $this->error_msg('该渠道已关联该商品，无需重新关联');
        }
        $params['ch_code'] = $channel_info['ch_code'];
        $this->DAO->insert_goods_channel($params);
        $this->succeed_msg();
    }

    public function edit_channel($id){
        $info = $this->DAO->get_relation_info($id);
        if(!$info){
            die('商品关联渠道ID不存在');
        }
        $goods_list = $this->DAO->get_goods_list($info['app_id']);
        $channel_list = $this->DAO->get_channel_list($info['app_id']);
        $app_list = $this->DAO->get_channel_app_list();
        $this->assign('app_list',$app_list);
        $this->assign('goods_list',$goods_list);
        $this->assign('channel_list',$channel_list);
        $this->assign('info',$info);
        $this->display('super_sdk/product_channel_edit.html');
    }

    public function do_edit_channel(){
        $params = $_POST;
        $info = $this->DAO->get_relation_info($params['id']);
        if(!$info){
            $this->error_msg('商品关联渠道ID不存在');
        }
        if(!$params['app_id'] || !$params['channel_id'] || !$params['goods_id']){
            $this->error_msg('缺少必填项');
        }
        $channel_info = $this->DAO->get_channel_info($params['channel_id']);
        if(!$channel_info){
            $this->error_msg('查无此渠道信息');
        }
        $params['ch_code'] = $channel_info['ch_code'];
        $relation_info = $this->DAO->get_relation($params);
        if($relation_info){
            $this->error_msg('该渠道已关联该商品，无需重新关联');
        }

        $this->DAO->update_goods_channel($params);
        $this->succeed_msg();
    }

    public function channel_export(){
        $ch_code = $_GET['ch_code'];
        $datalist = $this->DAO->get_relation_all_list($ch_code);
        if($datalist){
            $this->master_channel_excel_out($datalist);
        }else{
            echo "没有数据需要导出！";
        }
    }

    public function master_channel_excel_out($data){
        set_time_limit(0);
        $str_now=date("Y-m-d H:i:s",strtotime("now"));
        $objExcel = new PHPExcel();
        $objExcel->setActiveSheetIndex(0);
        $objActSheet = $objExcel->getActiveSheet();
        $objActSheet->setTitle("商品渠道列表");
        $objActSheet->setCellValue("A1", "ID");
        $objActSheet->setCellValue("B1", "应用");
        $objActSheet->setCellValue("C1", "商品名称");
        $objActSheet->setCellValue("D1", "关联渠道名称");
        $objActSheet->setCellValue("E1", "渠道计费点代码");
        $objActSheet->setCellValue("F1", "状态");
        $objActSheet->setCellValue("G1", "录入时间");
        $n = 2;
        foreach($data as $info){
            $objActSheet->setCellValue("A".$n, $info['id']);
            $objActSheet->setCellValue("B".$n, "[".$info['app_id']."]".$info['app_name']);
            $objActSheet->setCellValue("C".$n, $info['good_name']);
            $objActSheet->setCellValue("D".$n, $info['channel']);
            $objActSheet->setCellValue("E".$n, $info['channel_goods']);
            if($info['is_pub'] == 1){
                $objActSheet->setCellValue("F".$n, "下线");
            }else{
                $objActSheet->setCellValue("F".$n, "发布");
            }
            $objActSheet->setCellValue("G".$n, date('Y-m-d H:i:s',$info['add_time']));
            $n++;
        }
        $title = iconv("UTF-8", "GB2312//IGNORE","商品渠道列表-".$str_now.'.xls');
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