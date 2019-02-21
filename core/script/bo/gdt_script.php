<?php
COMMON('gdt_ad/services');
DAO('gdt_script_dao');

class gdt_script{
    public $DAO;
    public $gdt;

    public function __construct(){
        $this->DAO = new gdt_script_dao();
        $this->gdt = new services();
    }

    public function gdt_data_compare(){
        //获取appid,channel,time
        $app_id = 1095;
        $channel = '1330552759';
        $time_arr = explode('-',date('Y-m-d-H-i'));
        $end_time = mktime($time_arr[3],$time_arr[4],0,$time_arr[1],$time_arr[2],$time_arr[0]) -60;
        $begin_time = $end_time-60;
        //搜索相关的设备报道数据(前分钟的数据 注册时间)
        $ios_device_data = $this->DAO->get_ios_stats_device(array("app_id"=>$app_id,"channel"=>$channel,"begin_time"=>$begin_time,"end_time"=>$end_time));
        if (!empty($ios_device_data)){
            foreach ($ios_device_data as $val){
                //比对广点通表中数据，是否有点击
                $res = $this->DAO->get_data_gdt(array("app_id"=>$channel,"muid"=>md5(strtoupper($val['Adtid']))));
                if (!empty($res)){
                    //有点击，算激活给广点通上报，调接口
                    $report_res = $this->gdt->data_report_active(array(
                        "appid"=>$res['appid'],"app_type"=>"ios","muid"=>$res['muid'],
                        "conv_time"=>$val['RegTime'],"click_id"=>$res['click_id']
                    ));
                    //入库存记录
                    if ($report_res['ret'] === 0){
                        $this->DAO->insert_report_record(array(
                            "gdt_ad_id"=>$res['id'],"app_id"=>$app_id,
                            "ret"=>$report_res['ret'], "message"=>$report_res['msg']
                        ));
                        //更新设备id
                        $this->DAO->update_data_gdt(array("device_id"=>$val['Adtid'],"id"=>$res['id']));
                        echo date('Y-m-d H:i:s')."---get gdt change ok\n";
                    }else{
                        $this->DAO->insert_report_record(array(
                            "gdt_ad_id"=>$res['id'],"app_id"=>$app_id,
                            "ret"=>$report_res['ret'], "message"=>$report_res['msg']
                        ));
                        echo date('Y-m-d H:i:s')."---get gdt change fail\n";
                    }
                }else{
                    echo date('Y-m-d H:i:s')."---".$val['Adtid']." no gdt_change_data\n";
                }
            }
        }else{
            echo date('Y-m-d H:i:s')."---no ios_device_data\n";
        }
    }
}