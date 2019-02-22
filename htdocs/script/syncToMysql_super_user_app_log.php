<?php
/*
 * 延迟入库日志(融合SDK角色)
 */
require_once "config.php";
DAO("script_dao");
$test_dao = new script_dao();
$redis = new Redis();
$redis->pconnect(REDIS_HOST,REDIS_PORT) or die("redis connect fail");
$redis->select(6);
while (true){
    //super_user_app
    if ($redis->exists("super_user_app")) {
        $super_user_app_id = $redis->rPop("super_user_app");
        if ($super_user_app_id) {
            $super_user_app = $redis->hGetAll($super_user_app_id);
            if (!empty($super_user_app)) {
                $super_user_app_res = $test_dao->get_super_user_app_by_id_new($super_user_app);
                if (empty($super_user_app_res)) {
                    //插入
                    $test_dao->insert_super_user_app($super_user_app);
                }else{
                    if ($super_user_app['ActIP'] == '0' || $super_user_app['ActIP'] == '1'){
                        continue ;
                    }
                    $test_dao->update_super_user_app_new(array(
                        "ActIP"=>$super_user_app['ActIP'],
                        "ActTime"=>$super_user_app['ActTime'],
                        "RoleLevel"=>$super_user_app['RoleLevel'],
                        "RoleName"=>$super_user_app['RoleName'],
                        "ID"=>$super_user_app_res['ID']
                    ));
                }
            }
        }else{
            //echo "super_user_app list is empty \n";
        }
    }else{
        //echo "no super_user_app list \n";
    }
    usleep(10000);
}
