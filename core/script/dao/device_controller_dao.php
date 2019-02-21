<?php
COMMON('dao');
/**
 * Created by PhpStorm.
 * User: Young
 * Date: 2018/11/29
 * Time: 11:55 AM
 */
class device_controller_dao extends Dao{

    public $mmc;
    public $redis_5;
    public $redis_2;

    public function __construct(){
        parent::__construct();
        $this->mmc = new Memcache();
        $this->mmc->connect(MMCHOST, MMCPORT);
    }

    public function get_cpa_app_info($app_id,$channel){
        $data = $this->mmc->get("do_cpa_app_".$app_id."_".$channel);
        if(!$data){
            $this->sql = "SELECT * from cpa_app  WHERE `status` > 0 AND app_id=? AND ch_code=? AND start_time < ? AND end_time > ? ";
            $this->params = array($app_id,$channel,time(),time());
            $this->doResult();
            $data = $this->result;
            if(empty($data)){
                $data = array();
            }
            $this->mmc->set("do_cpa_app_".$app_id."_".$channel,$data,1,3600);
        }
        return $data;
    }


    public function get_stats_device_by_id($params){
        $data = $this->mmc->get("old_device_".$params['AppID']."_".$params['Channel']."_".$params['SID']);
        if(!$data){
            $this->sql = "SELECT ID FROM stats_device WHERE AppID=? AND SID=? AND Channel=?";
            $this->params = array($params['AppID'],$params['SID'],$params['Channel']);
            $this->doResult();
            $data = $this->result;
            if($data){
                $this->mmc->set("old_device_".$params['AppID']."_".$params['Channel']."_".$params['SID'],$data,1,3600);
            }
        }
        return $data;
    }

    public function insert_stats_device($params){
        $keys_all = array_keys($params);
        $index = array_search("ID", $keys_all);
        if($index !== FALSE){
            array_splice($params, $index, 1);
        }
        $keys = implode(",",array_keys($params));
        $values = array_values($params);
        $value_flag = "";
        for ($i=0;$i<count($params);$i++){
            $value_flag .= "?,";
        }
        $value_flag = trim($value_flag,",");
        $this->sql = "INSERT INTO stats_device(".$keys.")VALUES(".$value_flag.")";
        $this->params = $values;
        $this->doInsert();
    }

    public function update_stats_device($params,$id){
        $this->sql = "UPDATE stats_device SET ActIP=?,ActTime=? WHERE ID=? ";
        $this->params = array($params['ActIP'],$params['ActTime'],$id);
        $this->doExecute();
    }


    public function get_sid_info($sid){
        $data = $this->mmc->get("old_device_sid_".$sid);
        if(!$data){
            $this->sql = "SELECT ID FROM stats_device WHERE SID=? ";
            $this->params = array($sid);
            $this->doResult();
            $data = $this->result;
            if($data){
                $this->mmc->set("old_device_sid_".$sid,$data,1,3600);
            }
        }
        return $data;
    }

    public function get_cpa_click_by_imei($adid,$imei,$md5_imei){
        $this->sql = "SELECT * FROM cpa_mac_tb WHERE adid=? AND (imei=? or imei=?) and install_check= 0";
        $this->params = array($adid,$imei,$md5_imei);
        $this->doResult();
        return $this->result;
    }

    public function get_cpa_click_by_android_id($adid,$android_id,$md5_android_id){
        $this->sql = "SELECT * FROM cpa_mac_tb WHERE adid=? AND (android_id=? or android_id=?) and install_check= 0";
        $this->params = array($adid,$android_id,$md5_android_id);
        $this->doResult();
        return $this->result;
    }

    public function get_cpa_click_by_mac($adid,$mac,$md5_mac){
        $this->sql = "SELECT * FROM cpa_mac_tb WHERE adid=? AND (mac=? or mac=?) and install_check= 0";
        $this->params = array($adid,$mac,$md5_mac);
        $this->doResult();
        return $this->result;
    }

    public function up_cpa_click($id,$sid){
        $this->sql = "UPDATE cpa_mac_tb SET install_check=?,is_install=?,sid=?,install_time=? WHERE id=? ";
        $this->params = array(1,1,$sid,time(),$id);
        $this->doExecute();
    }

}