<?php
COMMON('dao');
class script_dao extends Dao {
    public function __construct() {
        parent::__construct();
    }
    //重写mysql连接方法
    public function connection_db(){
        if(!$this->DB){
            echo "1111111 \n";
            try{
                $DSN = "mysql:host=".$this->DBHOST.";port=".$this->DBPORT.";dbname=".$this->DBNAME;
                if($this->DB = new PDO($DSN, $this->DBUSER, $this->DBPWD, array(PDO::ATTR_PERSISTENT => true))){
                    $this->DB->query('SET NAMES utf8mb4');
                    $this->DB->query('set interactive_timeout=3600');
                    $this->DB->query("SET CHARACTER SET utf8mb4");
                    $this->DB->query("SET COLLATION_CONNECTION='utf8mb4_general_ci'");
                    $this->DB->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                }else{
                    die("error:DB001");
                }
            }catch(Exception $e) {
                echo 'error connect mysql';
                exit();
            }
        }
    }
    public function connect_read_db(){
        ini_set("display_errors","on");
        if(!$this->READ_DB){
            echo "222222\n";
            try{
                $DSN = "mysql:host=".$this->READ_DBHOST.";port=".$this->READ_DBPORT.";dbname=".$this->DBNAME;
                if($this->READ_DB = new PDO($DSN, $this->DBUSER, $this->DBPWD, array(PDO::ATTR_PERSISTENT => true))){
                    $this->READ_DB->query('SET NAMES utf8mb4');
                    $this->READ_DB->query("SET CHARACTER SET utf8mb4");
                    $this->READ_DB->query('set interactive_timeout=3600');
                    $this->READ_DB->query("SET COLLATION_CONNECTION='utf8_general_ci'");
                    $this->READ_DB->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                }else{
                    die("error:DB001");
                }
            }catch(Exception $e) {
                echo 'error connect mysql';
                exit();
            }
        }
    }
    public function get_user_info_by_id($userid){
        $this->sql = "SELECT user_id FROM `66173`.user_login WHERE user_id=?";
        $this->params = array($userid);
        $this->doResult();
        return $this->result;
    }

    public function insert_user_info($params_msg,$params_login,$user_id){
        //插入user_login
        if (!$user_id) return ;
        $keys_all = array_keys($params_login);
        $index = array_search("id", $keys_all);
        if($index !== FALSE){
            array_splice($params_login, $index, 1);
        }
        $keys = implode(",",array_keys($params_login));
        $values = array_values($params_login);
        $value_flag = "";
        for ($i=0;$i<count($params_login);$i++){
            $value_flag .= "?,";
        }
        $value_flag = trim($value_flag,",");
        $this->sql = "INSERT INTO `66173`.user_login(".$keys.")VALUES(".$value_flag.")";
        $this->params = $values;
        $this->doInsert();
        //判断user_info是否插入
        $this->sql = "SELECT user_id FROM `66173`.user_info WHERE user_id=?";
        $this->params = array($user_id);
        $this->doResult();
        $res = $this->result;
        if (empty($res)){
            //插入user_info
            $keys_all = array_keys($params_msg);
            $index = array_search("id", $keys_all);
            if($index !== FALSE){
                array_splice($params_msg, $index, 1);
            }
            $keys = implode(",",array_keys($params_msg));
            $values = array_values($params_msg);
            $value_flag = "";
            for ($i=0;$i<count($params_msg);$i++){
                $value_flag .= "?,";
            }
            $value_flag = trim($value_flag,",");
            $this->sql = "INSERT INTO `66173`.user_info(".$keys.")VALUES(".$value_flag.")";
            $this->params = $values;
            $this->doInsert();
        }else{
            //更新user_info
            $keys_all = array_keys($params_msg);
            $index = array_search("id", $keys_all);
            if($index !== FALSE){
                array_splice($params_msg, $index, 1);
            }
            $keys = array_keys($params_msg);
            $values = array_values($params_msg);
            array_push($values,$params_msg['user_id']);
            $update_str = "";
            foreach ($keys as $val){
                $update_str .= $val."=?,";
            }
            $update_str = trim($update_str,",");
            $this->sql = "UPDATE `66173`.user_info SET ".$update_str." WHERE user_id=?";
            $this->params = $values;
            $this->doExecute();
        }
    }

    public function update_user_info($params_msg,$params_login){
        //更新user_login
        $keys_all = array_keys($params_login);
        $index = array_search("id", $keys_all);
        if($index !== FALSE){
            array_splice($params_login, $index, 1);
        }
        $keys = array_keys($params_login);
        $values = array_values($params_login);
        array_push($values,$params_login['user_id']);
        $update_str = "";
        foreach ($keys as $val){
            $update_str .= $val."=?,";
        }
        $update_str = trim($update_str,",");
        $this->sql = "UPDATE `66173`.user_login SET ".$update_str." WHERE user_id=?";
        $this->params = $values;
        $this->doExecute();
        //更新user_info
        $keys_all = array_keys($params_msg);
        $index = array_search("id", $keys_all);
        if($index !== FALSE){
            array_splice($params_msg, $index, 1);
        }
        $keys = array_keys($params_msg);
        $values = array_values($params_msg);
        array_push($values,$params_msg['user_id']);
        $update_str = "";
        foreach ($keys as $val){
            $update_str .= $val."=?,";
        }
        $update_str = trim($update_str,",");
        $this->sql = "UPDATE `66173`.user_info SET ".$update_str." WHERE user_id=?";
        $this->params = $values;
        $this->doExecute();
    }

    public function get_user_app_by_id($params){
        $this->sql = "SELECT userid FROM user_apptb WHERE app_id=? AND userid=?";
        $this->params = array($params['app_id'],$params['userid']);
        $this->doResult();
        return $this->result;
    }

    public function insert_user_app($params){
        $keys_all = array_keys($params);
        $index = array_search("id", $keys_all);
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
        $this->sql = "INSERT INTO user_apptb(".$keys.")VALUES(".$value_flag.")";
        $this->params = $values;
        $this->doInsert();
    }

    public function update_user_app($params){
        $keys_all = array_keys($params);
        $index = array_search("id", $keys_all);
        if($index !== FALSE){
            array_splice($params, $index, 1);
        }
        $keys = array_keys($params);
        $values = array_values($params);
        array_push($values,$params['app_id'],$params['userid']);
        $update_str = "";
        foreach ($keys as $val){
            $update_str .= $val."=?,";
        }
        $update_str = trim($update_str,",");
        $this->sql = "UPDATE user_apptb SET ".$update_str." WHERE app_id=? AND userid=?";
        $this->params = $values;
        $this->doExecute();
    }

    public function get_stats_device_by_id($params){
        $this->sql = "SELECT ID FROM stats_device WHERE AppID=? AND SID=? AND Channel=?";
        $this->params = array($params['AppID'],$params['SID'],$params['Channel']);
        $this->doResult();
        return $this->result;
    }

    public function get_stats_device_by_id_new($params){
        $this->sql = "SELECT ID,ActIP,Imei,Android_id FROM stats_device WHERE AppID=? AND Mac=? AND SID=? AND Channel=?";
        $this->params = array($params['AppID'],$params['Mac'],$params['SID'],$params['Channel']);
        $this->doResult();
        return $this->result;
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


    public function update_stats_device($params){
        $keys_all = array_keys($params);
        $index = array_search("ID", $keys_all);
        if($index !== FALSE){
            array_splice($params, $index, 1);
        }
        $keys = array_keys($params);
        $values = array_values($params);
        array_push($values,$params['AppID'],$params['Mac'],$params['SID'],$params['Channel']);
        $update_str = "";
        foreach ($keys as $val){
            $update_str .= $val."=?,";
        }
        $update_str = trim($update_str,",");
        $this->sql = "UPDATE stats_device SET ".$update_str." WHERE AppID=? AND Mac=? AND SID=? AND Channel=?";
        $this->params = $values;
        $this->doExecute();
    }
    //测试更新安卓设备表
    public function update_stats_device_new($params){
        $this->sql = "UPDATE stats_device SET ActIP=? AND ActTime=? AND Imei=? AND Android_id=? WHERE ID=?";
        $this->params = array($params['ActIP'],$params['ActTime'],$params['Imei'],$params['Android_id'],$params['ID']);
        $this->doExecute();
    }
    public function get_stats_user_app_by_id($params){
        $this->sql = "SELECT ID FROM stats_user_app WHERE AppID=? AND UserID=? AND AreaServerID=? AND Channel=? AND RoleID=?";
        $this->params = array($params['AppID'],$params['UserID'],$params['AreaServerID'],$params['Channel'],$params['RoleID']);
        $this->doResult();
        return $this->result;
    }
    public function get_stats_user_app_by_id_new($params){
        $this->sql = "SELECT ID,ActIP,RoleLevel,RoleName FROM stats_user_app WHERE AppID=? AND UserID=? AND AreaServerID=? AND Channel=? AND RoleID=?";
        $this->params = array($params['AppID'],$params['UserID'],$params['AreaServerID'],$params['Channel'],$params['RoleID']);
        $this->doResult();
        return $this->result;
    }

    public function insert_stats_user_app($params){
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
        $this->sql = "INSERT INTO stats_user_app(".$keys.")VALUES(".$value_flag.")";
        $this->params = $values;
        $this->doInsert();
    }

    public function update_stats_user_app($params){
        $keys_all = array_keys($params);
        $index = array_search("ID", $keys_all);
        if($index !== FALSE){
            array_splice($params, $index, 1);
        }
        $keys = array_keys($params);
        $values = array_values($params);
        array_push($values,$params['AppID'],$params['UserID'],$params['AreaServerID'],$params['Channel'],$params['RoleID']);
        $update_str = "";
        foreach ($keys as $val){
            $update_str .= $val."=?,";
        }
        $update_str = trim($update_str,",");
        $this->sql = "UPDATE stats_user_app SET ".$update_str." WHERE AppID=? AND UserID=? AND AreaServerID=? AND Channel=? AND RoleID=?";
        $this->params = $values;
        $this->doExecute();
    }
    //测试安卓角色表
    public function update_stats_user_app_new($params){
        $this->sql = "UPDATE stats_user_app SET ActIP=?,ActTime=?,RoleLevel=?,RoleName=? WHERE ID=?";
        $this->params = array($params['ActIP'],$params['ActTime'],$params['RoleLevel'],$params['RoleName'],$params['ID']);
        $this->doExecute();
    }
    public function insert_stats_user_login_log($params){
        $keys = implode(",",array_keys($params));
        $values = array_values($params);
        $value_flag = "";
        for ($i=0;$i<count($params);$i++){
            $value_flag .= "?,";
        }
        $value_flag = trim($value_flag,",");
        $table_name = date("Ym",time());
        $this->sql = "INSERT INTO stats_user_login_log".$table_name."(".$keys.")VALUES(".$value_flag.")";
        $this->params = $values;
        $this->doInsert();
    }

    public function insert_stats_user_logout_log($params){
        $keys = implode(",",array_keys($params));
        $values = array_values($params);
        $value_flag = "";
        for ($i=0;$i<count($params);$i++){
            $value_flag .= "?,";
        }
        $value_flag = trim($value_flag,",");
        $table_name = date("Ym",time());
        $this->sql = "INSERT INTO stats_user_logout_log".$table_name."(".$keys.")VALUES(".$value_flag.")";
        $this->params = $values;
        $this->doInsert();
    }

    public function insert_stats_user_op_log($params){
        $keys = implode(",",array_keys($params));
        $values = array_values($params);
        $value_flag = "";
        for ($i=0;$i<count($params);$i++){
            $value_flag .= "?,";
        }
        $value_flag = trim($value_flag,",");
        $table_name = date("Ym",time());
        $this->sql = "INSERT INTO stats_user_op_log".$table_name."(".$keys.")VALUES(".$value_flag.")";
        $this->params = $values;
        $this->doInsert();
    }
    public function get_ios_stats_device_by_id($params){
        $this->sql = "SELECT ID FROM ios_stats_device WHERE AppID=? AND Mac=? AND SID=? AND Channel=?";
        $this->params = array($params['AppID'],$params['Mac'],$params['SID'],$params['Channel']);
        $this->doResult();
        return $this->result;
    }

    public function insert_ios_stats_device($params){
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
        $this->sql = "INSERT INTO ios_stats_device(".$keys.")VALUES(".$value_flag.")";
        $this->params = $values;
        $this->doInsert();
    }


    public function update_ios_stats_device($params){
        $keys_all = array_keys($params);
        $index = array_search("ID", $keys_all);
        if($index !== FALSE){
            array_splice($params, $index, 1);
        }
        $keys = array_keys($params);
        $values = array_values($params);
        array_push($values,$params['AppID'],$params['Mac'],$params['SID'],$params['Channel']);
        $update_str = "";
        foreach ($keys as $val){
            $update_str .= $val."=?,";
        }
        $update_str = trim($update_str,",");
        $this->sql = "UPDATE ios_stats_device SET ".$update_str." WHERE AppID=? AND Mac=? AND SID=? AND Channel=?";
        $this->params = $values;
        $this->doExecute();
    }
    public function get_ios_stats_user_app_by_id($params){
        $this->sql = "SELECT ID FROM ios_stats_user_app WHERE AppID=? AND UserID=? AND AreaServerID=? AND Channel=? AND RoleID=?";
        $this->params = array($params['AppID'],$params['UserID'],$params['AreaServerID'],$params['Channel'],$params['RoleID']);
        $this->doResult();
        return $this->result;
    }

    public function insert_ios_stats_user_app($params){
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
        $this->sql = "INSERT INTO ios_stats_user_app(".$keys.")VALUES(".$value_flag.")";
        $this->params = $values;
        $this->doInsert();
    }

    public function update_ios_stats_user_app($params){
        $keys_all = array_keys($params);
        $index = array_search("ID", $keys_all);
        if($index !== FALSE){
            array_splice($params, $index, 1);
        }
        $keys = array_keys($params);
        $values = array_values($params);
        array_push($values,$params['AppID'],$params['UserID'],$params['AreaServerID'],$params['Channel'],$params['RoleID']);
        $update_str = "";
        foreach ($keys as $val){
            $update_str .= $val."=?,";
        }
        $update_str = trim($update_str,",");
        $this->sql = "UPDATE ios_stats_user_app SET ".$update_str." WHERE AppID=? AND UserID=? AND AreaServerID=? AND Channel=? AND RoleID=?";
        $this->params = $values;
        $this->doExecute();
    }

    public function insert_ios_stats_user_login_log($params){
        $keys = implode(",",array_keys($params));
        $values = array_values($params);
        $value_flag = "";
        for ($i=0;$i<count($params);$i++){
            $value_flag .= "?,";
        }
        $value_flag = trim($value_flag,",");
        $table_name = date("Ym",time());
        $this->sql = "INSERT INTO ios_stats_user_login_log".$table_name."(".$keys.")VALUES(".$value_flag.")";
        $this->params = $values;
        $this->doInsert();
    }

    public function insert_ios_user_op_log($params){
        $keys = implode(",",array_keys($params));
        $values = array_values($params);
        $value_flag = "";
        for ($i=0;$i<count($params);$i++){
            $value_flag .= "?,";
        }
        $value_flag = trim($value_flag,",");
        $table_name = date("Ym",time());
        $this->sql = "INSERT INTO ios_user_op_log".$table_name."(".$keys.")VALUES(".$value_flag.")";
        $this->params = $values;
        $this->doInsert();
    }

    public function insert_super_user_login_log($params){
        $keys = implode(",",array_keys($params));
        $values = array_values($params);
        $value_flag = "";
        for ($i=0;$i<count($params);$i++){
            $value_flag .= "?,";
        }
        $value_flag = trim($value_flag,",");
        $table_name = date("Ym",time());
        $this->sql = "INSERT INTO super_user_login_log".$table_name."(".$keys.")VALUES(".$value_flag.")";
        $this->params = $values;
        $this->doInsert();
    }

    public function insert_super_user_logout_log($params){
        $keys = implode(",",array_keys($params));
        $values = array_values($params);
        $value_flag = "";
        for ($i=0;$i<count($params);$i++){
            $value_flag .= "?,";
        }
        $value_flag = trim($value_flag,",");
        $this->sql = "INSERT INTO super_user_logout_log(".$keys.")VALUES(".$value_flag.")";
        $this->params = $values;
        $this->doInsert();
    }

    public function insert_super_user_op_log($params){
        $keys = implode(",",array_keys($params));
        $values = array_values($params);
        $value_flag = "";
        for ($i=0;$i<count($params);$i++){
            $value_flag .= "?,";
        }
        $value_flag = trim($value_flag,",");
        $table_name = date("Ym",time());
        $this->sql = "INSERT INTO ch_user_op_log".$table_name."(".$keys.")VALUES(".$value_flag.")";
        $this->params = $values;
        $this->doInsert();
    }

    public function get_super_user_app_by_id_new($params){
        $this->sql = "SELECT ID FROM super_user_app WHERE AppID=? AND UserID=? AND AreaServerID=? AND Channel=? AND RoleID=?";
        $this->params = array($params['AppID'],$params['UserID'],$params['AreaServerID'],$params['Channel'],$params['RoleID']);
        $this->doResult();
        return $this->result;
    }

    public function insert_super_user_app($params){
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
        $this->sql = "INSERT INTO super_user_app(".$keys.")VALUES(".$value_flag.")";
        $this->params = $values;
        $this->doInsert();
    }

    public function update_super_user_app_new($params){
        $this->sql = "UPDATE super_user_app SET ActIP=?,ActTime=?,RoleLevel=?,RoleName=? WHERE ID=?";
        $this->params = array($params['ActIP'],$params['ActTime'],$params['RoleLevel'],$params['RoleName'],$params['ID']);
        $this->doExecute();
    }

    public function get_super_device_by_id($params){
        $this->sql = "SELECT ID FROM super_stats_device WHERE AppID=? AND Mac=? AND SID=? AND Channel=?";
        $this->params = array($params['AppID'],$params['Mac'],$params['SID'],$params['Channel']);
        $this->doResult();
        return $this->result;
    }

    public function insert_super_device($params){
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
        $this->sql = "INSERT INTO super_stats_device(".$keys.")VALUES(".$value_flag.")";
        $this->params = $values;
        $this->doInsert();
    }

    public function update_super_device($params){
        $keys_all = array_keys($params);
        $index = array_search("ID", $keys_all);
        if($index !== FALSE){
            array_splice($params, $index, 1);
        }
        $keys = array_keys($params);
        $values = array_values($params);
        array_push($values,$params['AppID'],$params['Mac'],$params['SID'],$params['Channel']);
        $update_str = "";
        foreach ($keys as $val){
            $update_str .= $val."=?,";
        }
        $update_str = trim($update_str,",");
        $this->sql = "UPDATE super_stats_device SET ".$update_str." WHERE AppID=? AND Mac=? AND SID=? AND Channel=?";
        $this->params = $values;
        $this->doExecute();
    }

    public function get_cpa_info($id){
        $this->sql = "SELECT *  FROM cpa_app where id=?  and `status`>'0'";
        $this->params = array($id);
        $this->doResult();
        $data = $this->result;
        return $data;
    }

    public function get_cpa_log($info){
        $this->sql = "SELECT id FROM cps_mac where app_id=? and idfa=? and install_check='0' order by id desc";
        $this->params = array($info['AppID'],$info['Adtid']);
        $this->doResult();
        return $this->result;
    }

    public function get_install_adfa($idfa){
        $this->sql = "SELECT id from cpa_install where idfa=?";
        $this->params = array($idfa);
        $this->doResult();
        return $this->result;
    }

    public function get_install_android($android){
        $this->sql = "SELECT id from cpa_install where android_id=?";
        $this->params = array($android);
        $this->doResult();
        return $this->result;
    }

    public function get_install_android_md5($android){
        $this->sql = "SELECT id from cpa_install where android_id_md5=?";
        $this->params = array($android);
        $this->doResult();
        return $this->result;
    }

    public function get_install_app_android_md5($android,$app_id){
        $this->sql = "SELECT id from cpa_install where android_id_md5=? and app_id=? ";
        $this->params = array($android,$app_id);
        $this->doResult();
        return $this->result;
    }

    public function get_install_imei($imei){
        $this->sql = "SELECT id from cpa_install where imei=?";
        $this->params = array($imei);
        $this->doResult();
        return $this->result;
    }

    public function get_install_app_imei($imei,$app_id){
        $this->sql = "SELECT id from cpa_install where imei=? and app_id=? ";
        $this->params = array($imei,$app_id);
        $this->doResult();
        return $this->result;
    }

    public function update_cpa_log($cpa_log,$install_time,$status){
        $this->sql = "update cps_mac set install_time=?,install_check=? WHERE id=?";
        $this->params = array($install_time,$status,$cpa_log['id']);
        $this->doExecute();
    }

    public function add_cpa_install_log($cpa_log,$install_time,$status){
        $this->sql = "insert into cpa_install(app_id,adid,aid,cid,mac,idfa,callback_url,add_time,install_time,install_check)VALUES(?,?,?,?,?,?,?,?,?,?)";
        $this->params = array($cpa_log->app_id,$cpa_log->cpa_id,$cpa_log->aid,$cpa_log->cid,$cpa_log->mac,$cpa_log->adtid,$cpa_log->callback_url,$cpa_log->add_time,$install_time,$status);
//        $this->params = array($cpa_log['app_id'],$cpa_log['cpa_id'],$cpa_log['aid'],$cpa_log['cid'],$cpa_log['mac'],$cpa_log['adtid'],$cpa_log['callback_url'],$cpa_log['add_time'],$install_time,$status);
        $this->doInsert();
    }

    public function add_cpa_install_android_log($cpa_log,$install_time,$status){
        $this->sql = "insert into cpa_install(android_id_md5,app_id,adid,aid,cid,imei,android_id,callback_url,add_time,install_time,install_check)VALUES(?,?,?,?,?,?,?,?,?,?,?)";
        $this->params = array($cpa_log->android_id_md5,$cpa_log->app_id,$cpa_log->cpa_id,$cpa_log->aid,$cpa_log->cid,$cpa_log->imei,$cpa_log->android_id,$cpa_log->callback_url,$cpa_log->add_time,$install_time,$status);
        $this->doInsert();
    }

    public function get_cpa_list(){
        $this->sql = "select app_id from cpa_app where status!=0";
        $this->doResultList();
        return $this->result;
    }

}
