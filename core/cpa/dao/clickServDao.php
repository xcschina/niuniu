 <?php
COMMON('niuniuDao','baseRedis');
class clickServDao extends niuniuDao{

    public $redisDao;

    public function __construct() {
        parent::__construct();
        $this->DB_NAME = "cps_mac";
        $this->redisDao = new baseRedis();
        $this->redisDao->switchDB(5);
    }

    public function add_click_log($adid,$cid,$imei,$mac,$androidid,$timestamp,$callback_url){
        $this->sql = "insert into cps_mac(mac,adid,cid,imei,android_id,`timestamp`,callback_url,add_time)
                      VALUES(?,?,?,?,?,?,?,?)";
        $this->params=array($mac,$adid,$cid,$imei,$androidid,$timestamp,$callback_url,time());
        $this->doInsert();
    }

    public function add_jrtt_click_log($app_id,$data){
        $this->sql = "insert into cpa_mac_tb(app_id,mac,adid,aid,cid,callback_url,imei,android_id,add_time,`timestamp`,idfa)
                      VALUES(?,?,?,?,?,?,?,?,?,?,?)";
        $this->params=array($app_id,$data['mac'],$data['adid'],$data['aid'],$data['cid'],$data['callback_url'],
                            $data['imei'],$data['androidid'],time(),$data['timestamp'],$data['idfa']);
        $this->doInsert();
    }

    public function get_jrtt_click_log($app_id,$data){
        $this->sql = "select * from cpa_mac_tb where app_id=? and adid=? and mac = ? and imei =? and android_id=?";
        $this->params=array($app_id,$data['adid'],$data['mac'],$data['imei'],$data['androidid']);
        $this->doResult();
        $data = $this->result;
        return $data;
    }

    public function get_cpa_log($cpa_info, $adtid){
        $this->sql = "select id from cps_mac where app_id=? and idfa=?";
        $this->params = array($cpa_info['app_id'],$adtid);
        $this->doResult();
        return $this->result;
    }

    public function  get_cpa_info($id){
        $data = $this->redisDao->getHash("cpa_info_".$id);
        if(!$data){
            $this->sql = "select * from cpa_app where id=? and `status` > '0'";
            $this->params = array($id);
            $this->doResult();
            $data = $this->result;
            $this->redisDao->insertHash("cpa_info_".$id, $data);
        }
        return $data;
    }

    public function  get_idfa_info($idfa){
        $data = $this->redisDao->getHash("idfa_info_".$idfa);
        if(!$data){
            $this->sql = "select id from ios_stats_device where Adtid=? ";
            $this->params = array($idfa);
            $this->doResult();
            $data = $this->result;
            $this->redisDao->insertHash("idfa_info_".$idfa, $data);
        }
        return $data;
    }

    public function get_android_info($android){
        $data = $this->redisDao->getHash("android_info_".$android);
        if(!$data) {
            $this->sql = "select id from stats_device where Android_id=? ";
            $this->params = array($android);
            $this->doResult();
            $data = $this->result;
            $this->redisDao->insertHash("android_info_".$android, $data);
        }
        return $data;
    }

    public function get_imei_info($imei){
        $data = $this->redisDao->getHash("imei_info_".$imei);
        if(!$data) {
            $this->sql = "select id from stats_device where Imei=? ";
            $this->params = array($imei);
            $this->doResult();
            $data = $this->result;
            $this->redisDao->insertHash("imei_info_".$imei, $data);
        }
        return $data;
    }

    public function insert_ios_cpa_log($cpa_info,$mac, $adtid, $callback){
        $this->sql = "insert into cps_mac(app_id,adid,aid,cid,mac,idfa,callback_url,add_time)VALUES(?,?,?,?,?,?,?,?)";
        $this->params=array($cpa_info['app_id'],$cpa_info['id'],$cpa_info['aid'],$cpa_info['cid'],$mac,$adtid,$callback,time());
        $this->doInsert();
        return $this->LAST_INSERT_ID;
    }

    public function get_is_open_log(){
        $this->sql = "select mac.*,cpa.ch_name from cps_mac as mac left join cpa_app as cpa on mac.adid=cpa.id where mac.install_check='1' order by mac.add_time desc limit 10";
        $this->params = array();
        $this->doResultList();
        return $this->result;
    }

    public function get_is_install_log(){
        $this->sql = "select mac.*,cpa.ch_name from cpa_install as mac left join cpa_app as cpa on mac.adid=cpa.id where mac.install_check='1' order by mac.add_time desc limit 10";
        $this->params = array();
        $this->doResultList();
        return $this->result;
    }

    public function get_cpa_is_install(){
        $this->sql = "select mac.*,cpa.ch_name from cpa_mac_tb as mac left join cpa_app as cpa on mac.adid=cpa.id where mac.install_check='1' order by mac.add_time desc limit 10 ";
        $this->params = array();
        $this->doResultList();
        return $this->result;
    }


    public function get_is_install_info(){
        $this->sql = "select * from cps_mac where install_check >'0' and is_install='0' order by add_time desc limit 200";
        $this->params = array();
        $this->doResultList();
        return $this->result;
    }


    public function get_install_info($idfa){
        $this->sql = "select * from cpa_install where idfa=?";
        $this->params = array($idfa);
        $this->doResult();
        return $this->result;
    }

    public function update_install_status($id,$status){
        $this->sql = "update cps_mac set is_install=? where id=?";
        $this->params=array($status,$id);
        $this->doExecute();
    }


    public function add_install_log($info){
        $this->sql = "insert into cpa_install(app_id,adid,aid,cid,callback_url,add_time,install_time,callback_time,install_check,idfa,`desc`)values(?,?,?,?,?,?,?,?,?,?,?)";
        $this->params = array($info['app_id'],$info['adid'],$info['aid'],$info['cid'],$info['callback_url'],$info['add_time'],$info['install_time'],$info['callback_time'],$info['install_check'],$info['idfa'],$info['desc']);
        $this->doInsert();
    }

    public function add_activate_log($info){
        $this->sql = "insert into cpa_install(app_id,adid,aid,cid,callback_url,add_time,install_time,callback_time,install_check,idfa,`desc`)values(?,?,?,?,?,?,?,?,?,?,?)";
        $this->params = array($info['app_id'],$info['adid'],$info['aid'],$info['cid'],$info['callback_url'],$info['add_time'],$info['install_time'],$info['callback_time'],$info['install_check'],$info['idfa'],$info['desc']);
        $this->doInsert();
    }

    public function insert_mac_data($cpa_info, $mac = '', $adtid = '', $callback_url = ''){
        $key = $cpa_info['id'].'_';
        if($adtid){
            $key .= $adtid;
        }elseif($mac){
            $key .= $mac;
        }else{
            return '';
        }
        $this->redisDao->switchDB(5);
        if(!$this->redisDao->exist($key)){
            $click_data = array(
                'app_id' =>  $cpa_info['app_id'],
                'mac' => $mac,
                'adtid' => $adtid,
                'cpa_id' => $cpa_info['id'],
                'ch_name' => $cpa_info['ch_name'],
                'ch_code' => $cpa_info['ch_code'],
                'aid' => $cpa_info['aid'],
                'cid' => $cpa_info['cid'],
                'callback_url' => $callback_url,
                'cpa_type' => $cpa_info['type'],
                'add_time' => time()
            );
            $this->redisDao->insert($key, json_encode($click_data), 259200);
            $this->redisDao->listTailAdd('cpa-redis-key', $key);
            $click_key = $cpa_info['id'].'_'.strtotime(date('Y-m-d',time()));
//            $this->redisDao->increHashField($click_key,$click_key);
            $getID = $this->redisDao->getId($click_key);
            return 1;
        }else{
            return '';
        }
    }

    public function insert_android_info($cpa_info, $click_id = '', $android = '',$data=''){
        //1安卓 无->苹果
        $key = $cpa_info['ch_code'].'_1_';
        $cid = $cpa_info['cid'];
//        if($click_id && $click_id != $cid){
//            return'';
//        }

        if($data['android_id_md5']){
            $key .= strtolower($data['android_id_md5']);
        }elseif($android){
            $key .= strtolower(md5(strtolower($android)));
        }elseif($data['imei']){
            $key .= strtolower($data['imei']);
        }else{
            return '';
        }
        $this->redisDao->switchDB(5);
        $click_data = array(
            'app_id' =>  $cpa_info['app_id'],
            'imei' => $data['imei'] ? $data['imei'] : '',
            'android_id' => $android ? $android : '',
            'android_id_md5' => $data['android_id_md5']? $data['android_id_md5'] : strtolower(md5(strtolower($android))),
            'cpa_id' => $cpa_info['id'],
            'ch_name' => $cpa_info['ch_name'],
            'ch_code' => $cpa_info['ch_code'],
            'aid' =>    $sub_pub_id?$sub_pub_id:'',//子渠道
            'cid' => $click_id ? $click_id:'',//tid
            'callback_url' => $data['callback'] ? $data['callback'] : '',
            'add_time' => $data['click_time'] ? $data['click_time'] : time(),
        );
        if(!$this->redisDao->exist($key)){
            $this->redisDao->insert($key, json_encode($click_data), 259200);
            $this->redisDao->listTailAdd('cpa-redis-android-key', $key);
            $click_key = $cpa_info['ch_code'].'_'.strtotime(date('Y-m-d',time()));
            $adid_key = $cpa_info['id'].'_'.strtotime(date('Y-m-d',time()));
            $this->redisDao->getId($adid_key);
            $this->redisDao->getId($click_key);
            return 1;
        }else{
            $this->redisDao->delete($key);
            $this->redisDao->insert($key, json_encode($click_data), 259200);
            return 2;
        }
    }
//
//    public function update_cpa_log($id,$status,$desc=''){
//        $this->sql = "update cps_mac set install_check=?,`desc`=? where id=?";
//        $this->params=array($status,$desc,$id);
//        $this->doExecute();
//    }

    public function update_cpa_log($id,$status,$desc=''){
        $timestamp = strtotime(date('Y-m-d',time()));
        $this->sql = "update cpa_install set install_check=?,`desc`=?,`timestamp`=? where id=?";
        $this->params=array($status,$desc,$timestamp,$id);
        $this->doExecute();
    }

     public function up_cpa_install($id,$status,$desc=''){
         $this->sql = "update cpa_mac_tb set install_check=?,`desc`=? where id=?";
         $this->params=array($status,$desc,$id);
         $this->doExecute();
     }

    public function success_callback($id,$status,$desc=''){
        $this->sql = "update cpa_install set install_check=?,`desc`=?,callback_time=? where id=?";
        $this->params=array($status,$desc,time(),$id);
        $this->doExecute();
    }

     public function cpa_success_callback($id,$status,$desc=''){
         $this->sql = "update cpa_mac_tb set install_check=?,`desc`=?,callback_time=? where id=?";
         $this->params=array($status,$desc,time(),$id);
         $this->doExecute();
     }

     public function get_cpa_click($cpa_id,$time){
         $key = 'id_'.$cpa_id.'_'.$time;
         $this->redisDao->switchDB(5);
         $getID = $this->redisDao->get($key);
         return $getID;
     }

     public function get_cpa_click_num($cpa_id,$time){
         $this->sql = "select * from cpa_check where ad_id=? and ad_batch=?";
         $this->params=array($cpa_id,$time);
         $this->doResult();
         return $this->result;
     }

     public function insert_cpa_click_num($cpa_id,$time,$cpa_click){
         $this->sql = "insert into cpa_check(ad_id,ad_batch,`num`,old_num,add_time)values(?,?,?,?,?)";
         $this->params = array($cpa_id,$time,$cpa_click,$cpa_click,time());
         $this->doInsert();
     }

     public function up_cpa_click_num($cpa_click,$data){
         $this->sql = "update cpa_check set `num`=?,up_time=? where id=?";
         $this->params = array($cpa_click,time(),$data['id']);
         $this->doExecute();
     }

     public function get_cpa_list(){
         $this->sql = "select * from cpa_app where status =2 ";
         $this->params = array();
         $this->doResultList();
         return $this->result;
     }
}