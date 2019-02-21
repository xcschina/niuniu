<?php
COMMON('baseCore');
DAO('clickServDao');
class clickServ extends baseCore{
    public $callback;
    public $DAO;

    public function __construct(){
        parent::__construct();
        $this->DAO = new clickServDao();
        $this->callback = array('result'=>0,'messages'=>'收到请求。');
    }

    public function add_click($adid,$cid,$imei,$mac,$androidid,$timestamp,$callback_url){
        $this->DAO->add_click_log($adid,$cid,$imei,$mac,$androidid,$timestamp,$callback_url);
    }

    public function jrtt_click($data){
        $this->jrtt_verfiy($data);
        $cpa_info = $this->DAO->get_cpa_info($data['adid']);
        if(empty($cpa_info) || empty($cpa_info['app_id'])){
            $data['msg'] = '推广渠道异常';
            $this->jrtt_error_log($data,$data['msg']);
        }
        $app_id = $cpa_info['app_id'];
        $click_info =  $this->DAO->get_jrtt_click_log($app_id,$data);
        if($click_info){
            $data['msg'] = '重复点击';
            $this->jrtt_error_log($data,$data['msg']);
        }else{
            $this->DAO->add_jrtt_click_log($app_id,$data);
        }
    }

    public function jrtt_verfiy($data){
        if(!$data['adid']){
            $data['msg'] = '缺少cpa_id';
            $this->jrtt_error_log($data,$data['msg']);
        }
        if(!$data['imei'] && !$data['mac'] && !$data['androidid']){
            $data['msg'] = '缺少必要参数';
            $this->jrtt_error_log($data,$data['msg']);
        }
        if(!$data['callback_url'] && !$data['callback']){
            $data['msg'] = '缺少回调信息';
            $this->jrtt_error_log($data,$data['msg']);
        }
    }

    public function jrtt_error_log($data,$msg,$status = 1){
        $this->err_log(var_export($data,1),'cpa_jrtt_click_error');
        die(json_encode(array('status' => $status,'msg'=>$msg)));
    }

    public function uc_click($data){
        $this->jrtt_verfiy($data);
        $cpa_info = $this->DAO->get_cpa_info($data['adid']);
        if(empty($cpa_info) || empty($cpa_info['app_id'])){
            $data['msg'] = '推广渠道异常';
            $this->uc_error_log($data,$data['msg']);
        }
        $app_id = $cpa_info['app_id'];
        $click_info =  $this->DAO->get_jrtt_click_log($app_id,$data);
        if($click_info){
            $data['msg'] = '重复点击';
            $this->uc_error_log($data,$data['msg']);
        }else{
            $this->DAO->add_jrtt_click_log($app_id,$data);
        }
    }

    public function uc_verfiy($data){
        if(!$data['adid']){
            $data['msg'] = '缺少cpa_id';
            $this->uc_error_log($data,$data['msg']);
        }
        if(!$data['imei'] && !$data['mac'] && !$data['androidid']){
            $data['msg'] = '缺少必要参数';
            $this->uc_error_log($data,$data['msg']);
        }
        if(!$data['callback_url'] && !$data['callback']){
            $data['msg'] = '缺少回调信息';
            $this->uc_error_log($data,$data['msg']);
        }
    }

    public function uc_error_log($data,$msg,$status = 1){
        $this->err_log(var_export($data,1),'cpa_uc_click_error');
        die(json_encode(array('status' => $status,'msg'=>$msg)));
    }

    public function param_verify($cpa_id,$mac,$adtid){
        $this->err_log(var_export($_GET,1),'cpa_click_log');
        if(empty($cpa_id)){
            $this->callback['messages'] = '缺少广告主ID';
            die(json_encode($this->callback));
        }
        if($mac && !stristr($mac, ':')){
            $mac = implode(":", str_split($mac,2));
        }

        if($mac == '02:00:00:00:00:00'){
            $mac = '';
        }
        if(empty($adtid) || $adtid == 'null' || $adtid == 'NULL' || $adtid == '00000000-0000-0000-0000-000000000000'){
            $adtid = '';
        }
        if(!$mac && !$adtid){
            $this->callback['messages'] = '无效的点击';
            die(json_encode($this->callback));
        }
    }

    public function android_param_verify($cpa_id,$android_id){
        $this->err_log(var_export($_GET,1),'cpa_android_click_log');
        if(empty($cpa_id)){
            $this->callback['messages'] = '缺少广告主ID';
            die(json_encode($this->callback));
        }
        if(empty($android_id) || $android_id == 'null' || $android_id == 'NULL'){
            $android_id = '';
        }
        if(!$android_id){
            $this->callback['messages'] = '无效的点击';
            die(json_encode($this->callback));
        }
    }

    public function android_param_ver($cpa_id,$data){
        $this->err_log(var_export($_GET,1),'cpa_android_click_log');
        if(empty($cpa_id)){
            $this->callback['messages'] = '缺少广告主ID';
            die(json_encode($this->callback));
        }
        if(empty($data['android_id']) && empty($data['android_id_md5']) && empty($data['imei'])){
            $this->callback['messages'] = '无效的点击';
            die(json_encode($this->callback));
        }
    }

    public function device_click($cpa_id, $mac, $adtid, $callback){
        try{
            $cpa_info = $this->DAO->get_cpa_info($cpa_id);
            if(!$cpa_info){
                $this->callback['messages'] = '无效的广告主ID!';
                die(json_encode($this->callback));
            }
            $cpa_log = $this->DAO->get_cpa_log($cpa_info,strtoupper($adtid));
            if($cpa_log){
                $this->callback['messages'] = '无效的点击! ERROR=3';
                die(json_encode($this->callback));
            }else{
                $ID = $this->DAO->insert_ios_cpa_log($cpa_info, strtoupper($mac),strtoupper($adtid), $callback);
            }
            if($ID){
                $this->callback['result'] = '1';
                $this->callback['messages'] = '点击成功!';
                die(json_encode($this->callback));
            }else{
                $this->callback['messages'] = '无效的点击! ERROR=0';
                die(json_encode($this->callback));
            }
        }catch (Exception $e){
            $this->err_log(var_export($e,1),'cpa_click_error_log');
            $this->callback['messages'] = '网络请求异常!';
            die(json_encode($this->callback));
        }
    }

    public function default_device_click($cpa_id, $mac, $adtid, $callback){
        try{
            $cpa_info = $this->DAO->get_cpa_info($cpa_id);
            if(!$cpa_info){
                $this->callback['messages'] = '无效的广告主ID!';
                die(json_encode($this->callback));
            }

            $ID = $this->DAO->insert_mac_data($cpa_info, strtoupper($mac), strtoupper($adtid), $callback);
            if($ID){
                $idfa = $this->DAO->get_idfa_info($adtid);
                if($idfa){
                    $this->callback['messages'] = '无效点击，设备已激活过。';
                    die(json_encode($this->callback));
                }
                $this->callback['result'] = '1';
                $this->callback['messages'] = '点击成功!';
                die(json_encode($this->callback));
            }else{
                $this->callback['messages'] = '无效的点击! ERROR=0';
                die(json_encode($this->callback));
            }
        }catch (Exception $e){
            $this->err_log(var_export($e,1),'cpa_click_error_log');
            $this->callback['messages'] = '网络请求异常!';
            die(json_encode($this->callback));
        }
    }

    public function android_device_click($data){
        try{
            $cpa_info = $this->DAO->get_cpa_info($data['cpa_id']);
            if(!$cpa_info){
                $this->callback['messages'] = '无效的广告主ID!';
                die(json_encode($this->callback));
            }
            $ID = $this->DAO->insert_android_info($cpa_info, strtoupper($data['click_id']), strtoupper($data['android_id']),$data);
            if($ID){
                if($data['android_id']){
                    $info = $this->DAO->get_android_info($data['android_id']);
                }elseif($data['imei']){
                    $info = $this->DAO->get_imei_info($data['imei']);
                }
                if($info){
                    $this->callback['messages'] = '无效点击，设备已激活过。';
                    die(json_encode($this->callback));
                }
                $this->callback['result'] = '1';
                $this->callback['messages'] = '点击成功!';
                $this->err_log(var_export($data,1),'android_device_click_log');
                die(json_encode($this->callback));
            }else{
                $this->callback['messages'] = '无效的点击! ERROR=0';
                die(json_encode($this->callback));
            }
        }catch (Exception $e) {
            $this->err_log(var_export($e,1),'cpa_click_error_log');
            $this->callback['messages'] = '网络请求异常!';
            die(json_encode($this->callback));
        }
    }

    public function vido_device_click($data,$url=''){
        try{
            $cpa_info = $this->DAO->get_cpa_info($data['cpa_id']);
            if(!$cpa_info){
                $this->callback['messages'] = '无效的广告主ID!';
                die(json_encode($this->callback));
            }
            $ID = $this->DAO->insert_android_info($cpa_info, strtoupper($data['click_id']), strtoupper($data['android_id']),$data);
            if($ID){
                die(header("location:".$url));
            }else{
                $this->callback['messages'] = '无效的点击! ERROR=0';
                die(json_encode($this->callback));
            }
        }catch (Exception $e) {
            $this->err_log(var_export($e,1),'cpa_click_error_log');
            $this->callback['messages'] = '网络请求异常!';
            die(json_encode($this->callback));
        }
    }

    public function no_vido_device_click($data,$url=''){
        try{
            $cpa_info = $this->DAO->get_cpa_info($data['cpa_id']);
            if(!$cpa_info){
                $this->callback['messages'] = '无效的广告主ID!';
                die(json_encode($this->callback));
            }
            $ID = $this->DAO->insert_android_info($cpa_info, strtoupper($data['click_id']), strtoupper($data['android_id']),$data);
            if($ID){
                if($ID == 1){
                    $this->callback['result'] = '1';
                    $this->callback['messages'] = '点击成功!';
                    $this->err_log(var_export($data,1),'vido_success_android_click_log');
                    die(json_encode($this->callback));
                }else{
                    $this->callback['result'] = '1';
                    $this->callback['messages'] = '重复点击!';
                    $this->err_log(var_export($data,1),'vido_error_android_click_log');
                    die(json_encode($this->callback));
                }
//                die(header("location:".$url));
            }else{
                $this->callback['messages'] = '无效的点击! ERROR=0';
                die(json_encode($this->callback));
            }
        }catch (Exception $e) {
            $this->err_log(var_export($e,1),'cpa_click_error_log');
            $this->callback['messages'] = '网络请求异常!';
            die(json_encode($this->callback));
        }
    }

    public function callback(){
//        $logs = $this->DAO->get_is_open_log();
        $logs = $this->DAO->get_is_install_log();
        foreach($logs as $key =>$item){
            $error='';
            $cpa_info = $this->DAO->get_cpa_info($item['adid']);
            if(empty($cpa_info)){
                $this->DAO->update_cpa_log($item['id'],9,'推广信息未配置');
                $error = 1;
            }
            if($cpa_info['start_time'] && $item['add_time'] < $cpa_info['start_time']){
               $this->DAO->update_cpa_log($item['id'],10,'点击时间不能在提前于推广时间');
               $error = 1;
            }
            if($cpa_info['end_time'] && $item['add_time'] > $cpa_info['end_time']){
                $this->DAO->update_cpa_log($item['id'],11,'点击时间不能在于推广时间之后');
                $error = 1;
            }
            if($item['install_time'] - $item['add_time'] < 8){
                $this->DAO->update_cpa_log($item['id'],12,'安装时间过快.');
                $error = 1;
            }
            if(empty($error)){
                switch ($item['ch_name']){
                    case 'niwei':
                        return $this->niwei_callback($item);
                        break;
                    case 'nnwl':
                        return $this->default_callback($item);
                        break;
                    case 'pingme':
                        return $this->pingme_callback($item);
                        break;
                    case 'vido':
                        return $this->vido_callback($item);
                        break;
                }
            }
        }
    }

    public function callback_v2(){
        $install_list = $this->DAO->get_cpa_is_install();
        foreach($install_list as $key =>$item){
            $error = '';
            $cpa_info = $this->DAO->get_cpa_info($item['adid']);
            if(empty($cpa_info)){
                $this->DAO->up_cpa_install($item['id'],9,'推广信息未配置');
                $error = 1;
            }elseif($cpa_info['start_time'] && $item['add_time'] < $cpa_info['start_time']){
                $this->DAO->up_cpa_install($item['id'],10,'点击时间不能在提前于推广时间');
                $error = 1;
            }elseif($cpa_info['end_time'] && $item['add_time'] > $cpa_info['end_time']){
                $this->DAO->up_cpa_install($item['id'],11,'点击时间不能在于推广时间之后');
                $error = 1;
            }elseif($item['install_time'] - $item['add_time'] < 8){
                $this->DAO->up_cpa_install($item['id'],12,'安装时间过快.');
                $error = 1;
            }
            if(empty($error)){
                switch ($item['ch_name']){
                    case 'bltjrtt':
                        return $this->jrtt_callback($item);
                        break;
                    case 'bjssjrtt':
                        return $this->jrtt_callback($item);
                        break;
                    case 'mksucxxl':
                        return $this->uc_callback($item);
                        break;
                }
            }
        }
    }

    public function jrtt_callback($data){
        $callback = $this->request(urldecode($data['callback_url']));
        $this->err_log(var_export($callback,1),'jrtt_cpa_callback_log');
        $callback = json_decode($callback,true);
        if ($callback['msg']  =='success') {
            $this->DAO->cpa_success_callback($data['id'], 2, '回调成功.');
        } else {
            //回调失败
            $this->DAO->up_cpa_install($data['id'], 13, '回调失败.'.$callback);
        }
    }

    public function uc_callback($data){
        $data['callback_url'] = htmlspecialchars_decode($data['callback_url']);
        $this->err_log(var_export($data,1),'uc_cpa_callback_log');
        $callback = $this->request($data['callback_url']);
        $this->err_log(var_export($callback,1),'uc_cpa_callback_log');
        $this->err_log(var_export($this->curl_status,1),'uc_cpa_callback_log');
        $callback = json_decode($callback,true);
        if ($this->curl_status == 200) {
            $this->DAO->cpa_success_callback($data['id'], 2, '回调成功.');
        } else {
            //回调失败
            $this->DAO->up_cpa_install($data['id'], 13, '回调失败.'.$callback);
        }
    }

    public function lmsw_callback($cpa_id,$appid,$idfa){
        $cpa_info = $this->DAO->get_cpa_info($cpa_id);
        $data = array(
            'cpa_id'=>$cpa_id,
            'apple_id'=>$appid,
            'idfa'=>$idfa
        );
        if(empty($cpa_info)){
            $data['status']='9';
            $data['desc']='推广信息未配置';
            die(json_encode(array('status'=>0,'message'=>'推广信息未配置。')));
        }
        if($cpa_info['start_time'] && time() < $cpa_info['start_time']){
            $data['status']='10';
            $data['desc']='点击时间不能在提前于推广时间';
            die(json_encode(array('status'=>0,'message'=>'推广未开启。')));
        }
        if($cpa_info['end_time'] && time() > $cpa_info['end_time']){
            $data['status']='11';
            $data['desc']='点击时间不能在于推广时间之后';
            die(json_encode(array('status'=>0,'message'=>'推广已结束。')));
        }
        $old_idfa = $this->DAO->get_install_info($idfa);
        if(empty($old_idfa)){
//            $idfa_info = $this->DAO->get_idfa_info($idfa);
//            if(empty($idfa_info)){
//                die(json_encode(array('status'=>0,'message'=>'游戏未激活。')));
//            }
            $info = array(
                'app_id'=>$cpa_info['app_id'],
                'adid'=>$cpa_info['id'],
                'aid'=>$appid,
                'cid'=>$cpa_info['cid'],
                'callback_url'=>'',
                'add_time'=>time(),
                'install_time'=>time(),
                'callback_time'=>time(),
                'install_check'=>2,
                'idfa'=>$idfa,
                'desc'=>'激活成功',
            );
            $this->DAO->add_activate_log($info);
            die(json_encode(array('status'=>1,'message'=>'激活成功。')));
        }else{
            die(json_encode(array('status'=>0,'message'=>'设备已激活。')));
        }

    }




    public function data_repair(){
//        die('接口未开放');
        $cpa_list = $this->DAO->get_cpa_list();
        if(empty($cpa_list)){
            die('无需更新');
        }
        echo "开始时间:".time();
        foreach($cpa_list as $key=>$item){
//            if($item['type'] == '1'){
//                $for_time = strtotime(date('Y-m-d',time()));
//                $cpa_click = $this->DAO->get_cpa_click($item['id'],$for_time);
//                if($cpa_click){
//                    $click_info = $this->DAO->get_cpa_click_num($item['id'],$for_time);
//                    if($click_info){
//                        $this->DAO->up_cpa_click_num($cpa_click,$click_info);
//                    }else{
//                        $this->DAO->insert_cpa_click_num($item['id'],$for_time,$cpa_click);
//                    }
//
//                }
//            }elseif($item['type'] == '0'){
//                $for_time = strtotime(date('Y-m-d',time()));
//                $cpa_click = $this->DAO->get_cpa_click($item['ch_code'],$for_time);
//                if($cpa_click){
//                    $click_info = $this->DAO->get_cpa_click_num($item['id'],$for_time);
//                    if($click_info){
//                        $this->DAO->up_cpa_click_num($cpa_click,$click_info);
//                    }else{
//                        $this->DAO->insert_cpa_click_num($item['id'],$for_time,$cpa_click);
//                    }
//
//                }
//            }
            if($item['type'] == '1'){
                $for_time = strtotime(date('Y-m-d',time()))-86400;
                $cpa_click = $this->DAO->get_cpa_click($item['id'],$for_time);
                if($cpa_click){
                    $click_info = $this->DAO->get_cpa_click_num($item['id'],$for_time);
                    if($click_info){
                        $this->DAO->up_cpa_click_num($cpa_click,$click_info);
                    }else{
                        $this->DAO->insert_cpa_click_num($item['id'],$for_time,$cpa_click);
                    }

                }
            }elseif($item['type'] == '0'){
                $for_time = strtotime(date('Y-m-d',time()))-86400;
                $cpa_click = $this->DAO->get_cpa_click($item['ch_code'].'_'.$item['id'],$for_time);
                if($cpa_click){
                    $click_info = $this->DAO->get_cpa_click_num($item['id'],$for_time);
                    if($click_info){
                        $this->DAO->up_cpa_click_num($cpa_click,$click_info);
                    }else{
                        $this->DAO->insert_cpa_click_num($item['id'],$for_time,$cpa_click);
                    }

                }
            }

        }
        echo "完成时间:".time();
//        $logs = $this->DAO->get_is_install_info();
//        if(empty($logs)){
//            die('数据修复完成');
//        }
//        foreach($logs as $key=>$item){
//            $install_info = $this->DAO->get_install_info($item['idfa']);
//            if($install_info){
//                $this->DAO->update_install_status($item['id'],3);
//            }else{
//                $this->DAO->update_install_status($item['id'],3);
//                unset($item['id']);
//                $this->DAO->add_install_log($item);
//            }
//        }
    }

    public function get_game_idfa($appid,$adtid){
        return $this->DAO->get_idfa_info($adtid);
    }

    public function niwei_callback($data){
        $callback = $this->request(urldecode($data['callback_url']));
        $this->err_log(var_export($callback,1),'cpa_callback_log');
        if ($callback=='success') {
            $this->DAO->success_callback($data['id'], 2, '回调成功.');
        } else {
            //回调失败
            $this->DAO->update_cpa_log($data['id'], 13, '回调失败.'.$callback);
        }
    }

    public function default_callback($data){
        $callback = $this->request(urldecode($data['callback_url']));
        $this->err_log(var_export($callback,1),'cpa_callback_log');
        if ($callback) {
            $this->DAO->update_cpa_log($data['id'], 2, '回调成功.');
        } else {
            //回调失败
            $this->DAO->update_cpa_log($data['id'], 13, '回调失败.');
        }
    }

    public function pingme_callback($data){
        $url = 'http://pingme.fuse-cloud.com/pb?tid=';
        $url .= $data['cid'];
        $callback = $this->request($url);
        $this->err_log(var_export($url,1),'pingme_callback_log');
        $this->err_log(var_export($callback,1),'pingme_callback_log');
        if(strstr($callback, 'Success') || strstr($callback, 'Duplicate Trans. ID')) {
            $this->DAO->success_callback($data['id'], 2, '回调成功.');
        } else {
            //回调失败
            $this->DAO->update_cpa_log($data['id'], 13, '回调失败.'.$callback);
        }
    }

    public function vido_callback($data){
        $url = 'http://www.vidoadsplus.xyz/install?ref_id=';
        $url .= strtolower($data['cid']);
        $callback = $this->request($url);
        $this->err_log(var_export($url,1),'vido_callback_log');
        $this->err_log(var_export($callback,1),'vido_callback_log');
        $callback = json_decode($callback,true);
        if($callback['status'] =='200' || $callback['message'] =='Duplicate ref_id') {
            $this->DAO->success_callback($data['id'], 2, '回调成功.');
        } else {
            //回调失败
            $this->DAO->update_cpa_log($data['id'], 13, '回调失败.'.$callback);
        }
    }

}
?>