<?php
COMMON('baseCore','uploadHelper');
DAO('products_info_dao');

class products_info_web extends baseCore{

    public $DAO;
    public $id;
    public $p_type;

    public function __construct(){
        parent::__construct();
        $this->DAO = new products_info_dao();
        $this->p_type = array("首充号","首充号续充","代充");
    }

    public function get_products_list(){
        $this->V->force_compile = true;
        $params=$_POST;
        $game_list= $this->DAO->get_game_list();
        //$servs_list=$this->DAO->get_game_servs_list();
        $channels_list=$this->DAO->get_channels_list();
        $dataList=$this->DAO->get_products_list($params,$this->pageCurrent);
        $this->pageInfo($this->pageCurrent);
        $this->assign("game_list",$game_list);
        $this->assign("servs_list","");
        $this->assign("channels_list",$channels_list);
        $this->assign("dataList", $dataList);
        $this->assign("params",$params);
        $this->display("products_info_list.html");
    }

    public function products_add_view(){
        $game_list= $this->DAO->get_game_list();
        //$servs_list=$this->DAO->get_game_servs_list();
        $channels_list=$this->DAO->get_channels_list();
        $this->assign("game_list",$game_list);
        //$this->assign("servs_list",$servs_list);
        $this->assign("channels_list",$channels_list);
        $this->display("products_info_add.html");
    }

    public function  do_products_add(){
        $params=$_POST;
        if(!$params['type']){
            echo json_encode($this->error_msg("请选择商品类型"));
            exit();
        }
        if(!$params['title']){
            echo json_encode($this->error_msg("请输入商品名称"));
            exit();
        }
//        if(!$_FILES['product_img']['tmp_name']){
//            echo json_encode($this->error_msg("请上传图片"));
//            exit();
//        }
        if(!$params['game_id']){
            echo json_encode($this->error_msg("请选择游戏"));
            exit();
        }
//        if(!$params['stock'] || !is_numeric($params['stock'])){
//            echo json_encode($this->error_msg("请输入库存"));
//            exit();
//        }
        if(!$params['price']){
            echo json_encode($this->error_msg("请输入价格"));
            exit;
        }
        if(!$params['intro']){
            echo json_encode($this->error_msg("请输入商品介绍"));
            exit;
        }
        if(!$params['end_time']){
            echo json_encode($this->error_msg("请输入商品到期时间！"));
            exit;
        }
        $params['stock']=66173;
//        $params['img']=$this->up_img('product_img',PRODUCT_IMG);
//        $params['discount']=floatval($params['discount']);
        $params['price']=floatval($params['price']);
        $params['end_time']=strtotime($params['end_time']);
        $product_id=$this->DAO->add_product($params);
        $this->update_game_service($params);
        $this->up_product_img($product_id);
        echo json_encode($this->succeed_msg("商品添加成功","products_list"));
    }

    public function products_edit_view($id){
        $info = $this->DAO->get_product_info($id);
        $game_list= $this->DAO->get_game_list();
        if($info['channel_id']){
            $servs_list=$this->DAO->get_game_ch_servs($info['game_id'], $info['channel_id']);
        }else{
            $servs_list = array();
        }
        $channels_list=$this->DAO->get_channels_list();
        $imgs=$this->DAO->get_product_imgs($id);
        $gifts = $this->DAO->get_attach_gifts($info['game_id']);

        $this->assign("game_list",$game_list);
        $this->assign("servs_list",$servs_list);
        $this->assign("channels_list",$channels_list);
        $this->assign("product",$info);
        $this->assign("imgs",$imgs);
        $this->assign("gifts",$gifts);
        $this->display("products_info_edit.html");
    }

    public function do_products_edit(){
        $params=$_POST;
        if(!$params['type']){
            die(json_encode($this->error_msg("请选择商品类型")));
        }
        if(!$params['title']){
            die(json_encode($this->error_msg("请输入商品名称")));
        }
        if(!$params['game_id']){
            die( json_encode($this->error_msg("请选择游戏")));
        }
//        if(!$params['stock'] || !is_numeric($params['stock'])){
//            die( json_encode($this->error_msg("请输入库存")));
//        }
        if(!$params['price']){
            die( json_encode($this->error_msg("请输入价格")));
        }
        if(!$params['intro']){
            die( json_encode($this->error_msg("请输入商品介绍")));
        }

        if(!$params['end_time']){
            die( json_encode($this->error_msg("请输入商品到期时间！")));
        }

//        if(!$_FILES['product_img']['tmp_name']){
//            $params['img']=$params['old_img'];
//        }else{
//            $params['img']=$this->up_img('product_img',PRODUCT_IMG);
//        }

        $params['discount']=floatval($params['discount']);
        $params['price']=floatval($params['price']);
        $params['end_time']=strtotime($params['end_time']);
        $this->DAO->edit_product($params);
        if($_FILES['imgs']['tmp_name'] && $_FILES['imgs']['tmp_name'][0]){
            $this->DAO->del_product_imgs($params['id']);
            $this->up_product_img($params['id']);
        }
        echo json_encode($this->succeed_msg("商品编辑成功","products_list"));
    }

    //更新游戏服务
    protected function update_game_service($product){
        $field = array(
            1=>"is_character",
            2=>"is_recharge",
            3=>"is_top_up",
            4=>"is_account",
            5=>"is_coin",
            6=>"is_props",
            7=>"is_gift_bag"
        );
        $this->DAO->update_game_service($field[$product['type']], $product['game_id']);
    }

    protected function up_product_img($product_id){
        if($_FILES['imgs']['tmp_name'] && $_FILES['imgs']['tmp_name'][0]){
            $imgs = $this->batch_up_img('imgs', PRODUCT_IMG);
            foreach($imgs as $img){
                if($img){
                    $this->DAO->add_product_img($product_id, $img);
                }
            }
        }
    }

    public function import_view(){
        $game_list= $this->DAO->get_game_list();
        $this->assign("game_list",$game_list);
        $this->display("products_import.html");
    }


    public function do_import(){
        ini_set('display_errors', 'Off');
        header("Content-Type:text/html;charset=utf-8");
        $game_id=$_POST['game_id'];
        if(!$game_id){
            echo json_encode($this->error_msg("请选择游戏"));
            exit;
        }
        $type=$_POST['type'];
        if(!$type){
            echo json_encode($this->error_msg("请选择商品类型"));
            exit;
        }
        $fileType = $_FILES['products']['type'];
        if($fileType != 'application/vnd ms-excel' &&
            $fileType != 'application/kset' &&
            $fileType != 'application/vnd.ms-excel' &&
            $fileType != 'application/octet-stream' &&
            $fileType != 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'){
            echo json_encode($this->error_msg("请选择xls,xlsx文件"));
            exit;
        }
        $file_name = $_FILES['products']["tmp_name"];
        $ext_name = $_FILES['products']["name"];
        $ext_name = explode(".", $ext_name);
        $ext_name = $ext_name[1];
        if($ext_name == 'xlsx'){
            $PHPReader = new PHPExcel_Reader_Excel2007();
        }else{
            $PHPReader = new PHPExcel_Reader_Excel5();
        }

        $PHPExcel = $PHPReader->load($file_name);
        $currentSheet = $PHPExcel->getSheet(0);
        $allRow = $currentSheet->getHighestRow();

        for( $currentRow = 2 ; $currentRow <= $allRow ; $currentRow++){
            $title = $currentSheet->getCellByColumnAndRow("0", $currentRow)->getValue();
            $stock =$currentSheet->getCellByColumnAndRow("1", $currentRow)->getValue();
            $sub_title =$currentSheet->getCellByColumnAndRow("2", $currentRow)->getValue();
            $price =$currentSheet->getCellByColumnAndRow("3", $currentRow)->getValue();
            $intro =$currentSheet->getCellByColumnAndRow("4", $currentRow)->getValue();
            $is_pub =$currentSheet->getCellByColumnAndRow("5", $currentRow)->getValue();
            if(strlen(trim($title)) >= 1 && strlen(trim($stock)) >= 1 && strlen(trim($sub_title)) >= 1
                && strlen(trim($stock)) >= 1 && strlen(trim($price)) >= 1
                && strlen(trim($intro)) >= 1 && strlen(trim($is_pub))){
                $params = array('type'=>$type,
                    'title'=>$title,
                    'sub_title'=>$sub_title,
                    'img'=>'',
                    'game_id' => $game_id,
                    'channel_id' => '0',
                    'serv_id' => '0',
                    'serv_name' => '全区全服',
                    'stock' => $stock,
                    'price'=>$price,
                    'discount'=>'0',
                    'intro'=>$intro,
                    'end_time'=>strtotime("+1 year"),
                    'is_pub'=>$is_pub);
                $this->DAO->add_product($params);
                $this->update_game_service($params);
            }
        }
        unlink($file_name);
        echo json_encode($this->succeed_msg("商品导入成功！","products_list"));
    }

    public function product_ch_view($id){
        $info = $this->DAO->get_product_info($id);
        $product_channels = $this->DAO->get_product_channels($id);
        $channels = $this->DAO->get_channels_list();

        unset($product_channels['product_id']);
        $new_dis = array();
        foreach($product_channels as $k=>$v){
            $ch_id = explode("_", $k);
            if($ch_id[0]=='ch'){
                $ch_info = $this->DAO->get_channel_info($ch_id[1]);
                $new_dis[$k] = array("name"=>$ch_info['channel_name'], "discount"=>$v);
            }
        }

        $this->assign("info", $info);
        $this->assign("discounts", $new_dis);
        $this->assign("types", $this->p_type);
        $this->display("products_ch_edit.html");
    }

    public function do_save_ch(){
        //TODO
        //错误 echo json_encode($this->error_msg("错误信息"));
        //成功关闭当前页 echo json_encode($this->succeed_msg("正确信息","刷新tabid"));
        $product_id = $_POST['product_id'];
        unset($_POST['product_id']);
        $this->DAO->update_product_ch($product_id, $_POST);

        echo json_encode($this->unclose_succeed_msg("修改成功"));
    }


    //限时特卖商品
    public function get_special_products_list(){
        $params=$_POST;
        $dataList=$this->DAO->get_special_products_list($params,$this->pageCurrent);
        $this->pageInfo($this->pageCurrent);
        $this->assign("dataList", $dataList);
        $this->assign("params",$params);
        $this->display("special_products_list.html");
    }

    public function special_products_add_view(){
        $game_list= $this->DAO->get_game_list();
        $this->assign("game_list",$game_list);
        $this->display("special_products_add.html");
    }

    function get_products_bygame_id(){
        ini_set('display_errors', 'Off');
        $type = array(1 => '首充号', 2 => '首充号续充', 3 => '代充', 4 => '账号', 5 => '游戏币', 6 => '道具');
        $game_id=$_POST['game_id'];
        $data_list=$this->DAO->get_products_bygame_id($game_id);
        foreach($data_list as $key=>$value){
            $data_list[$key]['type_name'] = $type[$value['type']];
        }
        echo json_encode($data_list);
    }

    public function do_special_products_save(){
        $params=$_POST;
        if(!$params['game_id']){
            echo json_encode($this->error_msg("请选择游戏！"));
            exit;
        }
        if(!$params['product_id']){
            echo json_encode($this->error_msg("请选择游戏！"));
            exit;
        }
        if(!$params['end_time']){
            echo json_encode($this->error_msg("请输入截止时间！"));
            exit;
        }
        $params['img']="";
        if($_FILES['special_product_img']['tmp_name']){
            $params['img']=$this->up_img('special_product_img',PRODUCT_IMG);
        }
        $params['end_time']=strtotime($params['end_time']);
        $this->DAO->do_special_products_save($params);
        echo json_encode($this->succeed_msg("保存成功！","special_products_list"));
    }

    public function special_products_edit_view($id){
        $info=$this->DAO->get_special_products($id);
        $this->assign("info",$info);
        $this->display("special_products_edit.html");
    }

    public function do_special_products_edit(){
        $params=$_POST;
        if(!$params['end_time']){
            echo json_encode($this->error_msg("请输入截止时间！"));
            exit;
        }

        if($_FILES['special_product_img']['tmp_name']){
            $params['img']=$this->up_img('special_product_img',PRODUCT_IMG);
        }else{
            $params['img']=$params['old_img'];
        }

        $params['end_time']=strtotime($params['end_time']);
        $this->DAO->do_special_products_edit($params);
        echo json_encode($this->succeed_msg("编辑成功！","special_products_list"));
    }

    // 批量上架
    public function product_batch_pub(){
        $ids=paramUtils::strByGET('ids',false);
        $this->DAO->batch_pub("1",$ids);
        echo json_encode($this->unclose_succeed_msg("上架成功！","products_list"));
    }

    // 批量下架
    public function product_batch_unpub(){
        $ids=paramUtils::strByGET('ids',false);
        $this->DAO->batch_pub("0",$ids);
        echo json_encode($this->unclose_succeed_msg("下架成功！","products_list"));
    }


    public function intro_img_list($game_id){
        $params=$_POST;
        $img_list=$this->DAO->get_product_intro_img($game_id,$params);
        $this->assign("params",$params);
        $this->assign("game_id", $game_id);
        $this->assign("img_list", $img_list);
        $this->display("product_intro_img_list.html");
    }

    public function intro_img_add_view(){
        $game_id=paramUtils::intByGET("game_id",false);
        $this->assign("game_id", $game_id);
        $this->display("product_intro_img_add.html");
    }

    public function do_intro_img_add(){
        $params['game_id']=$_POST['game_id'];
        $params['type']=$_POST['type'];
        if(!$_FILES['imgs']['tmp_name']){
            echo json_encode($this->error_msg("请选择你要上传的图片"));
            exit;
        }

        $img_count=$this->DAO->intro_img_count($params['game_id'],$params['type']);
        if($img_count>=20){
            echo json_encode($this->error_msg("每种类型商品套图最多只能上传20张！"));
            exit;
        }
        if((count($_FILES['imgs']['tmp_name'])+$img_count)>20){
            echo json_encode($this->error_msg("每种类型商品套图最多只能上传20张！"));
            exit;
        }
        $img_path=$this->up_imgs();
        foreach($img_path as $img){
            $params['img_url']=$img['img'];
            $this->DAO->add_product_intro_img($params);
        }

        echo json_encode($this->succeed_msg("图片上传成功","intro_img_list"));
    }

    public function del_intro_img($id){
        $this->DAO->del_product_intro_img($id);
        echo json_encode($this->unclose_succeed_msg("删除成功","intro_img_list"));
    }

    protected function up_imgs(){
        if($_FILES['imgs']['tmp_name'] && $_FILES['imgs']['tmp_name'][0]){
            $imgs = $this->batch_up_img('imgs', PRODUCT_IMG);
            foreach($imgs as $key=>$img){
                if($img){
                    $img_path[]['img']=$img;
                }
            }
        }
        return $img_path;
    }

    public function game_ch_servs($game_id, $ch_id){
        $servs = $this->DAO->get_game_ch_servs($game_id, $ch_id);
        echo json_encode($servs);
    }
}