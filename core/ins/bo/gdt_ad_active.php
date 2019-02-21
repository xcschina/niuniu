<?php
COMMON('gdt_ad/services');
DAO('gdt_ad_active_dao');
class gdt_ad_active{

    public $DAO;

    public function __construct(){
        $this->DAO = new gdt_ad_active_dao();
    }

    public function ios_feedback(){
        $params = $_GET;
        $gdt_obj = new services();
        if (!$params['muid'] || !$params['click_time'] || !$params['click_id'] || !$params['appid'] || !$params['advertiser_id'] || !$params['app_type']){
            //参数缺失，请求失败
            $result = array("ret"=>-1,"msg"=>"请求参数缺失");
        }else{
            //参数OK，请求成功
            $result = array("ret"=>0,"msg"=>"请求成功");
            $this->DAO->insert_feedback_record($params);
        }
        $gdt_obj->feedback_respond($result);
    }

    public function android_feedback(){
        //后期处理
    }

}