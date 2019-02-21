<?php
COMMON('baseCore');
DAO('user_info_dao');
class user_info_web extends baseCore{

    public $DAO;
    public $id;

    public $p_type=array(
        "1"=>'首充号',
        "2"=>'首充号续充',
        "3"=>'代充',
        "4"=>'账号',
        "5"=>'游戏币',
        "6"=>'道具',
        "8"=>"苹果内购"
    );

    public function __construct(){
        parent::__construct();
        $this->DAO = new user_info_dao();
    }

    public function get_user_info_list(){
        $params=$_POST;
        if(!empty($params['time'])){
            $params['reg_time']=strtotime($params['time']);
        }
        if(!empty($params['time2'])){
            $params['reg_time2']=strtotime($params['time2']);
        }
        $groups = $this->DAO->get_user_groups();
        $this->assign('groups',$groups);
        $dataList=$this->DAO->get_user_info_list($params,$this->pageCurrent);
        $this->pageInfo($this->pageCurrent);
        $this->assign("dataList", $dataList);
        $this->assign("params",$params);
        $this->display("user_info_list.html");
    }

    public function search_user_info_list($code){
        if(!$code){
            echo json_encode($this->error_msg("代码参数丢失"));
            exit();
        }
        $params=$_POST;
        if(!empty($params['time'])){
            $params['reg_time']=strtotime($params['time']);
        }
        if(!empty($params['time2'])){
            $params['reg_time2']=strtotime($params['time2']);
        }
        $groups = $this->DAO->get_user_groups();
        $this->assign('groups',$groups);
        $dataList=$this->DAO->get_user_info_list($params,$this->pageCurrent,$code);
        $this->pageInfo($this->pageCurrent);
        $this->assign("dataList", $dataList);
        $this->assign("code", $code);
        $this->assign("params",$params);
        $this->display("search_user_info_list.html");
    }

    public function get_pay_user_list(){
        $params = $_POST;
        $dataList=$this->DAO->get_pay_user_list($params,$this->pageCurrent);
        foreach($dataList as $key => $data){
            $dataList[$key]['unit_price'] = number_format($data['pay'] / $data['count'],2);
            $game_info = $this->DAO->get_user_pay_games_info($data['user_id']);
            $dataList[$key]['game_name']="";
            foreach ($game_info as $k => $v) {
                $dataList[$key]['game_name'].=$v['game_name']."[".$v['channel_name']."]</br>";
            }
        }
        $this->pageInfo($this->pageCurrent);
        $this->assign("dataList", $dataList);
        $this->assign("params",$params);
        $this->display("pay_user_list.html");
    }

    // zbc
    public function user_info_detail($user_id=0){
        // $user_id = 4891; // test
        $groups = $this->DAO->get_user_groups();
        $this->assign('groups',$groups);
        $user = $this->DAO->get_user_info($user_id);
        $user['last_login_time'] = $this->DAO->get_user_last_login_time($user_id);
        $user['total_pay'] = $this->DAO->count_user_total_pay($user_id);
        if($user['total_pay']){
            $last_pay_order         = $this->DAO->get_user_last_pay_order($user_id);
            $user['last_pay']       = $last_pay_order['pay_money'];
            $user['last_buy_time']  = $last_pay_order['pay_time'];
        }else{
            $user['last_pay']       = 0;
            $user['last_buy_time']  = 0;
        }
        $user['games'] = $this->DAO->get_user_pay_games($user_id);
        $this->assign("user", $user);
        $this->display("user_info_detail.html");
    }

    public function game_info_detail($user_id=0){
        $user_attr_list=$this->DAO->get_user_attr_list($user_id,$this->pageCurrent);
        foreach($user_attr_list as $key=>$value){
            $attr = json_decode($value['attr']);
            $user_attr_list[$key]['attr']= implode(';',$attr);
            $count = $this->DAO->get_game_ch_pay($value, $user_id);
            $user_attr_list[$key]['sum']=$count['sum'];
            $user_attr_list[$key]['day']=rand(0,9).".".rand(0,9);
            $user_attr_list[$key]['in_time']=rand(0,23).".".rand(0,9);
            $user_attr_list[$key]['mon']=rand(0,30);
        }
        $this->pageInfo($this->pageCurrent);
        $this->assign("user_attr_list", $user_attr_list);
        $this->display("game_info_detail.html");
    }

    //zbc
    public function user_info_detail_do($user_id=0){
        if(!(int)$user_id){
            die(json_encode($this->succeed_msg("无效用户","get_user_info_list")));
        }
        $params = $_POST;
        $this->DAO->set_user_group($user_id, $params['user_group']);
        die(json_encode($this->succeed_msg("修改成功","get_user_info_list")));
    }

    //zbc
    public function user_pay_list($user_id=0){
        $params = $_POST;
        $channel_list=$this->DAO->get_channel_list();
        $params['user_id'] = $user_id;
        if(!empty($params['time'])){
            $params['pay_time'] = strtotime($params['time']);
        }
        if(!empty($params['time2'])){
            $params['pay_time2'] = strtotime($params['time2']);
        }
        if($_SESSION['group'] == 'admin'){
            $usr = 1;
        }
        $pays = $this->DAO->get_user_pay_list($params, $this->pageCurrent);
        $this->pageInfo($this->pageCurrent);
        $this->assign("channel_list", $channel_list);
        $this->assign("pays", $pays);
        $this->assign('usr',$usr);
        $this->assign("params", $params);
        $this->assign("p_type", $this->p_type);
        $this->display("user_pay_list.html");
    }

    // zbc
    public function user_login_list($user_id=0){
        $params = $_POST;
        $params['user_id'] = $user_id;
        if(!empty($params['time'])){
            $params['login_time'] = strtotime($params['time']);
        }
        if(!empty($params['time2'])){
            $params['login_time2'] = strtotime($params['time2']);
        }
        $logs = $this->DAO->get_user_login_list($params,$this->pageCurrent);
        $this->pageInfo($this->pageCurrent);
        $this->assign("logs", $logs);
        $this->assign("params",$params);
        $this->display("user_login_list.html");
    }

    public function get_login_log_list(){
       $params=$_POST;
       if(!empty($params['time'])){
           $params['login_time'] = strtotime($params['time']);
       }
       if(!empty($params['time2'])){
           $params['login_time2'] = strtotime($params['time2']);
       }
       $dataList=$this->DAO->get_login_log_list($params,$this->pageCurrent);
       $this->pageInfo($this->pageCurrent);
       $this->assign("dataList", $dataList);
       $this->assign("params",$params);
       $this->display("login_log_list.html");
    }

    public function get_operation_log_list(){
        $params=$_POST;
        $dataList=$this->DAO->get_operation_log_list($params,$this->pageCurrent);
        $this->pageInfo($this->pageCurrent);
        $this->assign("dataList", $dataList);
        $this->assign("params",$params);
        $this->display("operation_log_list.html");
    }

    public function change_my_channel(){
        $params=$_POST;
        $this->DAO->update_my_channel($params);
        die(json_encode(array("desc"=>"修改成功!")));
    }

//    public function user_age(){
//        echo "开始时间".time();
//        $user = $this->DAO->get_user_list();
//        foreach($user as $key=>$data){
//            $sub_str = substr($data['id_number'],6,4);
//            $now = date("Y",time());
//            $this->DAO->update_user_age($now-$sub_str,$data['user_id']);
//        }
//      echo "结束时间".time();
//    }

    /**
     * 更新付费用户vip等级,年龄,所属地区
     */
//    public function pay_user_update(){
//        $pay_user_count=$this->DAO->get_all_pay_count();
//        for($i=1; $i<=ceil($pay_user_count['sum']/20);$i++){
//            $pay_user_list=$this->DAO->get_all_pay_list($i);
//            foreach($pay_user_list as $key=>$data){
//                if($data['pay']<"499"){
//                    $data['vip_level']=0;
//                }else if($data['pay']>"2999"){
//                    $data['vip_level']=3;
//                }elseif($data['pay']>"1499"){
//                    $data['vip_level']=2;
//                }elseif($data['pay']>"499"){
//                    $data['vip_level']=1;
//                }
//                $data['age']=0;
//                if($data['id_number']){
//                    $data['age'] = $this->getIDCardInfo($data['id_number']); //身份证计算年龄
//                }
//                $data['area']="";
//                if($data['reg_ip']){
//                    $ipInfos = $this->get_ip_info($data['reg_ip']); // baidu.com IP地址
//                    $data['area'] = $ipInfos['country'] . "-" . $ipInfos['province'] . "-" . $ipInfos['city'];
//                }
//                $this->DAO->update_pay_user_info($data);
//            }
//            sleep(1);
//        }
//        echo "更改成功";
//    }

    /**
     * 根据IP获取地址详情
     */
    protected function get_ip_info($ip = ''){
        $res = @file_get_contents('http://int.dpool.sina.com.cn/iplookup/iplookup.php?format=js&ip=' . $ip);
        if (empty($res)) {
            return false;
        }
        $jsonMatches = array();
        preg_match('#\{.+?\}#', $res, $jsonMatches);
        if (!isset($jsonMatches[0])){
            return false;
        }
        $json = json_decode($jsonMatches[0], true);
        if (isset($json['ret']) && $json['ret'] == 1){
            $json['ip'] = $ip;
            unset($json['ret']);
        }else{
            return false;
        }
        return $json;
    }

    /**
     * 根据身份证号获取生日,年龄
     */
    private function getIDCardInfo($IDCard){
        if (strlen($IDCard) == 18) {
            $tyear = intval(substr($IDCard, 6, 4));
            $tmonth = intval(substr($IDCard, 10, 2));
            $tday = intval(substr($IDCard, 12, 2));
            if ($tyear > date("Y") || $tyear < (date("Y") - 100)) {
                return "";
            } elseif ($tmonth < 0 || $tmonth > 12) {
                return "";
            } elseif ($tday < 0 || $tday > 31) {
                return "";
            } else {
                $tdate = $tyear . "-" . $tmonth . "-" . $tday;
            }
        } elseif (strlen($IDCard) == 15) {
            $tyear = intval("19" . substr($IDCard, 6, 2));
            $tmonth = intval(substr($IDCard, 8, 2));
            $tday = intval(substr($IDCard, 10, 2));
            if ($tyear > date("Y") || $tyear < (date("Y") - 100)) {
                return "";
            } elseif ($tmonth < 0 || $tmonth > 12) {
                return "";
            } elseif ($tday < 0 || $tday > 31) {
                return "";
            } else {
                $tdate = $tyear . "-" . $tmonth . "-" . $tday;
            }
        }
        if ($tdate) {
            $age = date('Y', time()) - date('Y', strtotime($tdate)) - 1;
            if (date('m', time()) == date('m', strtotime($tdate))) {
                if (date('d', time()) > date('d', strtotime($tdate))) {
                    $age++;
                }
            } elseif (date('m', time()) > date('m', strtotime($tdate))) {
                $age++;
            }
        }

        return $age;
    }
}
?>