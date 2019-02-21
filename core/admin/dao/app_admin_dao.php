<?php
COMMON('niuniuDao');
class app_admin_dao extends niuniuDao {

    public function __construct() {
        parent::__construct();
        $this->mmc = new Memcache();
        $this->mmc->connect(MMCHOST, MMCPORT);
        $this->TB_NAME = "apps";
    }

    public function get_channel(){
        $this->sql = "select * from channel_tb";
        $this->doResultList();
        return $this->result;
    }

    public function get_app_list($page,$params){
        $this->limit_sql="select a.*,c.name as ch_name from apps as a left join channel_tb as c on a.ch_id=c.id where 1=1";
        if($params['app_id']){
            $this->limit_sql = $this->limit_sql." and a.app_id = ".$params['app_id'];
        }
        if($params['ch_id']){
            $this->limit_sql = $this->limit_sql." and a.ch_id = ".$params['ch_id'];
        }
        if($params['access_type'] && is_numeric($params['access_type']) || $params['access_type'] === "0"){
            $this->limit_sql = $this->limit_sql." and a.access_type = ".$params['access_type'];
        }
        if($params['role_type'] && is_numeric($params['role_type']) || $params['role_type'] === "0"){
            $this->limit_sql = $this->limit_sql." and a.role_type = ".$params['role_type'];
        }
        $this->limit_sql = $this->limit_sql." order by a.id desc";
        $this->doLimitResultList($page);
        return $this->result;
    }

    public function get_app_name(){
        $this->sql="select * from apps where app_id <>'5001'";
        $this->doResultList();
        return $this->result;
    }

    public function get_guild_app_name(){
        $this->sql="select * from apps where app_id <>'5001' and app_id <>'1000' and app_id <>'1001' and access_type = 4 order by id desc";
        $this->doResultList();
        return $this->result;
    }

    public function get_app_name_by_id($app_id){
        $this->sql="select * from apps where app_id= ?";
        $this->params=array($app_id);
        $this->doResultList();
        return $this->result;
    }

    public function get_app_name_by_ids($app_id,$app_id2){
        $this->sql="select * from apps where app_id= ? or app_id=? ";
        $this->params=array($app_id,$app_id2);
        $this->doResultList();
        return $this->result;
    }

    public function get_online_game_list(){
        $this->sql="select * from apps where access_type > 2 ";
        $this->doResultList();
        return $this->result;
    }

    public function get_ch_info(){
        $this->sql="select * from channel_tb order by id desc";
        $this->doResultList();
        return $this->result;
    }

    public function get_ch_apps($ch_id){
        $this->sql="select * from apps where ch_id=? order by id desc";
        $this->params=array($ch_id);
        $this->doResultList();
        return $this->result;
    }

    public function get_apps_list($page,$params){
        $this->limit_sql = "select * from apps where status=1 and (access_type = 4 or access_type = 3) and app_id <> '5001' and app_id <> '6017' and app_type = 1";
        if($params['app_id']){
            $this->limit_sql = $this->limit_sql." and app_id = ".$params['app_id'];
        }
        if($params['access_type']){
            $this->limit_sql = $this->limit_sql." and access_type = ".$params['access_type'];
        }
        $this->limit_sql = $this->limit_sql." order by id desc";
        $this->doLimitResultList($page);
        return $this->result;
    }

    public function get_moyu_list($page){
        $this->limit_sql = "select * from apps where status=1 and (access_type = 4 or access_type = 3) and app_id = '5001' and app_type = 1";
        $this->limit_sql = $this->limit_sql." order by id desc";
        $this->doLimitResultList($page);
        return $this->result;
    }

    public function get_moyu_lists($page){
        $this->limit_sql = "select * from apps where status=1 and (access_type = 4 or access_type = 3) and (app_id = '5001' or app_id = '6024' or app_id = '6017') and app_type = 1";
        $this->limit_sql = $this->limit_sql." order by id desc";
        $this->doLimitResultList($page);
        return $this->result;
    }

    public function get_user_info($user_id){
        $this->sql="select * from admins where id=?  ";
        $this->params = array($user_id);
        $this->doResult();
        return $this->result;
    }

    public function get_pack_record($appid,$user_code){
        $this->sql="select * from guild_app_tb where guild_code=? and app_id=?  ";
        $this->params = array($user_code,$appid);
        $this->doResult();
        return $this->result;
    }

    public function get_pack_log($appid,$user_code){
        $this->sql="select * from guild_app_tb where guild_code=? and app_id=?  ";
        $this->params = array($user_code,$appid);
        $this->doResult();
        return $this->result;
    }

    public function query_pack_log($appid,$user_code,$status){
        $this->sql="select * from apk_pack_log where guild_code=? and app_id=? and status=? ";
        $this->params = array($user_code,$appid,$status);
        $this->doResult();
        return $this->result;
    }

    public function insert_pack_log($user_code,$params){
        $this->sql = "insert into apk_pack_log(guild_code,app_id,down_url,add_time,status)values(?,?,?,?,?)";
        $this->params = array($user_code,$params['app_id'],$params['apk_url'],time(),1);
        $this->doInsert();
        return $this->LAST_INSERT_ID;
    }

    public function get_ready_apk_num(){
        $this->sql = "select count(*) as num from apk_pack_log where status=1";
        $this->params = array();
        $this->doResult();
        return $this->result;
    }


    public function get_guild_app_url($user_code,$app_id){
        $this->sql="select * from guild_app_tb where guild_code=? and app_id=?  ";
        $this->params = array($user_code,$app_id);
        $this->doResult();
        return $this->result;
    }

    public function get_guild_by_ch($ch){
        $data = $this->mmc->get("guild_code_".$ch);
        if(!$data) {
            $this->sql = "select * from admins where user_code= ? ";
            $this->params = array($ch);
            $this->doResult();
            $data = $this->result;
            $this->mmc->set("guild_code_".$ch, $data, 1, 3600);
        }
        return $data;
    }

    public function get_all_guild_ch($user_id){
        $this->sql = "select * from admins where (id =? or p1=? or p2=?)";
        $this->params = array($user_id,$user_id,$user_id);
        $this->doResultList();
        return $this->result;
    }

    public function get_user_apps(){
        $data = $this->mmc->get("admin_apps".$_SESSION['usr_id']);
        if(!$data){
            $this->sql="select * from apps where 1=1 ";
            if($_SESSION['group_id']<>1){
                if ($_SESSION['group_id'] <> 6) {

                    $this->sql .= " and (";
                    $apps = explode(",", $_SESSION['apps']);
                    foreach ($apps as $k => $v) {
                        if ($k == 0) {
                            $this->sql .= " app_id=" . $v;
                        } else {
                            $this->sql .= " or app_id=" . $v;
                        }
                    }
                    $this->sql .= ")";
                }
            }
            $this->sql.=" order by id desc";
            $this->doResultList();
            $data = $this->result;
            $this->mmc->set("admin_apps".$_SESSION['usr_id'], $data, 1, 300);
        }
        return $data;
    }

    public function get_guild_list(){
        $data = $this->mmc->get("guild_list".$_SESSION['usr_id']);
        if(!$data){
            $this->sql = "select * from admins where is_del = 0 and group_id = 10";
            $this->doResultList();
            $data = $this->result;
            $this->mmc->set("guild_list".$_SESSION['usr_id'],$data,1,6000);
        }
        return $data;
    }

    public function get_super_user_apps(){
        $data = $this->mmc->get("admin_super_app_".$_SESSION['usr_id']);
        if(!$data){
            $this->sql = "select * from super_apps where 1=1";
            if($_SESSION['group_id']<>1){
                if($_SESSION['group_id'] <> 6){
                    $this->sql .= " and (";
                    $apps = explode(",", $_SESSION['rh_apps']);
                    foreach($apps as $k => $v){
                        if($k == 0){
                            $this->sql .= " app_id=" . $v;
                        }else{
                            $this->sql .= " or app_id=" . $v;
                        }
                    }
                    $this->sql .= ")";
                }
            }
            $this->sql .= " order by app_id desc";
            $this->doResultList();
            $data = $this->result;
            $this->mmc->set("admin_super_app_".$_SESSION['usr_id'], $data, 1, 300);
        }
        return $data;
    }

    public function get_all_app(){
        $data = $this->mmc->get("admin_all_apps");
        if(!$data){
            $this->sql = "select * from apps order by id desc";
            $this->doResultList();
            $data = $this->result;
            $this->mmc->set("admin_all_apps", $data, 1, 7200);
        }
        return $data;
    }

    public function get_all_rh_app(){
        $data = $this->mmc->get("admin_all_rh_apps");
        if(!$data){
            $this->sql = "select * from super_apps order by app_id desc";
            $this->doResultList();
            $data = $this->result;
            $this->mmc->set("admin_all_rh_apps", $data, 1, 7200);
        }
        return $data;
    }

    public function update_app($app, $id){
        $this->sql = "update apps set app_name=?,app_type=?,channel=?,ch_id=?,status=?,lastupdate=?,app_icon=?,sdk_order_url=?,sdk_charge_url=?,
                        web_serv_url=?,web_user_url=?,web_order_url=?,web_charge_url=?,apk_url=?,version=?,version_time=?,
                        version_url=?,payee_ch=?,access_type=?,role_type=?,web_url=?,register_img=?,register_num=?,offline_time=?,wx_app_id=?,wx_app_secret=?,mch_id=? where id=?";
        $this->params = array($app['app_name'], $app['app_type'],$app['channel'],$app['ch_id'],$app['status'], strtotime("now"), $app['app_icon'], $app['sdk_order_url'],
            $app['sdk_charge_url'], $app['web_serv_url'], $app['web_user_url'], $app['web_order_url'], $app['web_charge_url'],
            $app['apk_url'], $app['version'], strtotime($app['version_time']), $app['version_url'], $app['payee_ch'], $app['access_type'],$app['role_type'],$app['web_url'],$app['register_img'],$app['register_num'],strtotime($app['offline_time']), $app['wx_app_id'],$app['wx_app_secret'],$app['mch_id'],$id);
        $this->doExecute();
        $this->mmc->delete("app_info".$app['app_id']);
        $this->mmc->delete("h5_app_info".$app['app_id']);
    }

    public function update_app_notice($app, $id){
        $this->sql = "update apps set notice=?,notice_status=? where id=?";
        $this->params = array($app['notice'], $app['notice_status'], $id);
        $this->doExecute();
        $this->mmc->delete("app_info".$app['app_id']);
    }

    public function version_update($app, $id){
        $this->sql = "update apps set version=?,up_title=?,up_desc=?,up_status=? where id=?";
        $this->params = array($app['version'],$app['up_title'],$app['up_desc'],$app['up_status'], $id);
        $this->doExecute();
        $this->mmc->delete("app_info".$app['app_id']);
    }

    public function insert_app($app, $app_key){
        $this->sql = "insert into apps(app_id, app_key, app_name, app_type, status, add_time, sdk_order_url, sdk_charge_url)values(?,?,?,?,?,?,?,?)";
        $this->params = array($app['app_id'], $app_key, $app['app_name'], $app['app_type'], $app['status'], strtotime("now"), $app['sdk_order_url'], $app['sdk_charge_url']);
        $this->doInsert();
        $this->mmc->delete("all_apps");
        return $this->LAST_INSERT_ID;
    }

    public function update_app_icon($img, $id, $app_key){
        $this->sql = "update apps set app_icon=?,app_key=? where id=?";
        $this->params = array($img, $app_key, $id);
        $this->doExecute();
    }

    public function check_app_id($app_id){
        $this->sql = "select * from apps where app_id=?";
        $this->params = array($app_id);
        $this->doResultList();
        return $this->result;
    }

    public function get_pack_ch_list($app_id){
        $this->sql = "SELECT * from admins where user_code is not NUll and apps LIKE '%".$app_id."%'";
        $this->params = array();
        $this->doResultList();
        return $this->result;
    }

    public function get_pack_info($app_id){
        $this->sql = "SELECT * from apk_pack_tb where appid =?";
        $this->params = array($app_id);
        $this->doResult();
        return $this->result;
    }

    public function get_app_info($app_id){
        $this->sql = "SELECT * from apps where app_id =?";
        $this->params = array($app_id);
        $this->doResult();
        return $this->result;
    }

    public function get_app_web($id){
        $this->sql = "select * from apps where id = ?";
        $this->params = array($id);
        $this->doResult();
        return $this->result;
    }

    public function update_app_web($id,$is_web){
        $this->sql = "update apps set is_web = ? where id = ?";
        $this->params = array($is_web,$id);
        $this->doExecute();
    }

    public function insert_pack_info($data){
        $this->sql = "insert into apk_pack_tb(apk_url,appid,channels,add_time,update_time)values(?,?,?,?,?)";
        $this->params = array($data['apk_url'],$data['app_id'],$data['new_pack_ch'],time(),time());
        $this->doInsert();
        return $this->LAST_INSERT_ID;
    }

    public function update_pack_info($data,$id){
        $this->sql = "update apk_pack_tb set apk_url=? ,channels=? ,update_time=? where id=? ";
        $this->params = array($data['apk_url'],$data['new_pack_ch'],time(), $id);
        $this->doExecute();
    }

    public function get_guild_pack_info($app_id,$guild){
        $this->sql = "select * from guild_app_tb where app_id =? and guild_code=? ";
        $this->params = array($app_id,$guild);
        $this->doResult();
        return $this->result;
    }

    public function insert_guild_pack_info($app_id, $guild_code,$apk_url){
        $this->sql = "insert into guild_app_tb(app_id,guild_code,down_url,`time`)values(?,?,?,?)";
        $this->params = array($app_id, $guild_code,$apk_url,time());
        $this->doInsert();
        return $this->LAST_INSERT_ID;
    }

    public function update_guild_pack_info($app_id, $guild_code,$apk_url){
        $this->sql = "update guild_app_tb set down_url=?,`time`=? where app_id=? and guild_code =?";
        $this->params = array($apk_url,time(),$app_id,$guild_code);
        $this->doExecute();
    }

    public function update_guild_pack_record($app_id, $guild_code,$apk_url,$status){
        $this->sql = "update guild_app_tb set down_url=?,`time`=?,status=? where app_id=? and guild_code =?";
        $this->params = array($apk_url,time(),$status,$app_id,$guild_code);
        $this->doExecute();
        return $this->LAST_INSERT_ID;
    }
    //获取游戏对应实名验证是否开启
    public function get_app_real_validate($id){
        $this->sql = "SELECT autonym FROM apps WHERE id=?";
        $this->params = array($id);
        $this->doResult();
        return $this->result;
    }
    //修改实名验证是否开启
    public function update_app_real_validate($id,$real_validate){
        $this->sql = "UPDATE apps SET autonym = ? WHERE id=?";
        $this->params = array($real_validate,$id);
        $this->doExecute();
    }
    //修改游戏牛点折扣
    public function update_app_nd_discount($nd_discount,$app_id){
        $this->sql = "UPDATE apps SET nd_discount = ? WHERE app_id=?";
        $this->params = array($nd_discount,$app_id);
        $this->doExecute();
    }
    //增加编辑牛点折扣记录
    public function insert_nd_app_discount_log($appid,$dd_id,$discount,$operator_id){
        $this->sql = "INSERT INTO nd_app_discount_log(appid,dd_id,discount,operator_id,add_time)VALUES(?,?,?,?,?)";
        $this->params = array($appid,$dd_id,$discount,$operator_id,time());
        $this->doInsert();
        return $this->LAST_INSERT_ID;
    }

    public function get_sdk_verify_rules($page){
        $this->limit_sql = "SELECT * FROM sdk_verify_rules ORDER BY type,content DESC";
        $this->doLimitResultList($page);
        return $this->result;
    }

    public function insert_sdk_verify_rules($type,$content){
        $this->sql = "INSERT INTO sdk_verify_rules(type,content,addtime)VALUES(?,?,?)";
        $this->params = array($type,$content,time());
        $this->doInsert();
        return $this->LAST_INSERT_ID;
    }

    public function get_sdk_rules($type){
        $this->sql = "SELECT * FROM sdk_verify_rules WHERE type=? ORDER BY content";
        $this->params = array($type);
        $this->doResultList();
        return $this->result;
    }

    public function update_app_rules($verify_type,$verify_content,$app_id){
        $this->sql = "UPDATE apps SET verify_type=?,verify_content=? WHERE app_id=?";
        $this->params = array($verify_type,$verify_content,$app_id);
        $this->doExecute();
    }

    public function update_app_verifycode_type($verifycode_type,$verifycode_time,$app_id){
        if ($verifycode_type==1){
            $this->sql = "UPDATE apps SET verifycode_type=?,verifycode_time=?  WHERE app_id=?";
            $this->params = array($verifycode_type,$verifycode_time,$app_id);
        }else{
            $this->sql = "UPDATE apps SET verifycode_type=? WHERE app_id=?";
            $this->params = array($verifycode_type,$app_id);
        }
        $this->doExecute();
        memcache_delete($this->mmc,"app_info".$app_id);
    }

    public function get_app_list_nolimit($params){
        $this->sql = "select a.*,c.name as ch_name from apps as a left join channel_tb as c on a.ch_id=c.id where 1=1";
        if($params['app_id']){
            $this->sql = $this->sql." and a.app_id = ".$params['app_id'];
        }
        if($params['ch_id']){
            $this->sql = $this->sql." and a.ch_id = ".$params['ch_id'];
        }
        if($params['access_type'] && is_numeric($params['access_type']) || $params['access_type'] === "0"){
            $this->sql = $this->sql." and a.access_type = ".$params['access_type'];
        }
        $this->sql = $this->sql." order by a.id desc";
        $this->doResultList();
        return $this->result;
    }

    public function get_apple_list($apple_id){
        $this->sql = "select * from app_ios_pack where app_id = ?";
        $this->params = array($apple_id);
        $this->doResultList();
        return $this->result;
    }

    public function get_apple_data($app_id,$apple_id){
        $this->sql = 'select a.*,b.app_name from app_ios_pack as a left join apps as b on a.app_id = b.app_id where a.app_id = '.$app_id;
        if($apple_id){
            $this->sql .= ' and a.apple_id = "'.$apple_id.'"';
        }
        $this->sql .= ' order by time desc';
        $this->doResultList();
        return $this->result;
    }

    public function get_apple_info($apple_id){
        $this->sql = "select * from app_ios_pack where apple_id = ?";
        $this->params = array($apple_id);
        $this->doResult();
        return $this->result;
    }

    public function insert_apple($params){
        $this->sql = "insert into app_ios_pack(app_id,game_name,apple_id,`time`,channel,status,offline_time) values (?,?,?,?,?,?,?) ";
        $this->params = array(trim($params['app_id']),trim($params['game_name']),trim($params['apple_id']),time(),trim($params['channel']),$params['status'],strtotime($params['offline_time']));
        $this->doInsert();
    }

    public function get_apple($id){
        $this->sql = "select * from app_ios_pack where id = ?";
        $this->params = array($id);
        $this->doResult();
        return $this->result;
    }

    public function get_apple_id($params){
        $this->sql = "select * from app_ios_pack where apple_id = ? and id != ?";
        $this->params = array($params['apple_id'],$params['id']);
        $this->doResult();
        return $this->result;
    }

    public function update_apple($params){
        $this->sql = "update app_ios_pack set game_name = ?,apple_id = ?,channel = ?,status = ?,offline_time = ?,version = ?,version_time = ?,version_url = ?,app_icon=?,callback_url=?,web_status=? where id=?";
        $this->params = array(trim($params['game_name']),trim($params['apple_id']),trim($params['channel']),$params['status'],strtotime($params['offline_time']),$params['version'],strtotime($params['version_time']),$params['version_url'],$params['app_icon'],$params['callback_url'],$params['web_status'],$params['id']);
        $this->doExecute();
        $this->mmc->delete("app_info".$params['apple_id']);
        $this->mmc->delete("app_info_".$params['apple_id']);
    }

    public function update_apple_notice($params){
        $this->sql = "update app_ios_pack set notice_status=?,notice=? where id=?";
        $this->params = array($params['notice_status'],$params['notice'],$params['id']);
        $this->doExecute();
        $this->mmc->delete("app_info".$params['apple_id']);
    }

    public function get_order_debug_info(){
        $data = $this->mmc->get("order_debug_info");
        return $data;
    }

    public function set_order_debug_info($data){
        $this->mmc->set("order_debug_info", $data, 1, 7200);
    }

    public function get_web_app_list(){
        $this->sql = "select * from apps where status = 1 and web_serv_url != '' order by add_time desc";
        $this->doResultList();
        return $this->result;
    }

    public function get_app_message($app_id){
        $this->sql = 'select * from apps_relation_tb where app_id = ?';
        $this->params = array($app_id);
        $this->doResult();
        return $this->result;
    }

    public function update_app_message($params){
        $this->sql = 'update apps_relation_tb set nnb_scale=?,relation_id=? where app_id=?';
        $this->params = array($params['nnb_scale'],$params['relation_id'],$params['app_id']);
        $this->doExecute();
    }

    public function insert_app_message($params){
        $this->sql = 'insert into apps_relation_tb (app_id,nnb_scale,relation_id,add_time)values(?,?,?,?)';
        $this->params = array($params['app_id'],$params['nnb_scale'],$params['relation_id'],time());
        $this->doInsert();
    }
}