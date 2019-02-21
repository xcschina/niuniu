<?php
COMMON('sdkCore','gdt_ad/config','gdt_ad/tools');
class services extends sdkCore {

    public function feedback_respond($res){
        die(json_encode($res,JSON_UNESCAPED_UNICODE));
    }

    public function data_report_active($params){
        $data = tools::data_change($params);
        $url = 'http://t.gdt.qq.com/conv/app/'.$params['appid'].'/conv?v='.$data.'&conv_type=MOBILEAPP_ACTIVITE&
                app_type='.$params['app_type'].'&advertiser_id='.config::ADVERTISERID;
        //请求上报接口
        $result = json_decode($this->request($url));
        return $result;
    }
}