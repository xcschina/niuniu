<?php
COMMON('baseCore');
BO('index_admin');
DAO("feedback_dao");
class feedback_admin extends baseCore {
    public $DAO;
    public function __construct(){
        parent::__construct();
        $this->DAO = new feedback_dao();
    }

    public function feedback_view() {
        $game_list = $this->DAO->get_app_name();
        $_SESSION['login_back_url'] = ltrim($_SERVER['REQUEST_URI'],'/');
        unset($_SESSION['act_last_time']);
        $bo = new index_admin();
        $this->page_hash();
        $wx = $bo->wx_share();
        $this->assign("noncestr", $wx['noncestr']);
        $this->assign("timestamp", $wx['timestamp']);
        $this->assign("signature", $wx['signature']);
        $this->assign("game_list",$game_list);
        $this->display("feedback.html");
    }

    public function add_feedback(){
        $result = array("code"=>0,"msg"=>"网络出错啦");
        if($_SESSION["act_last_time"] != ''){
            if((time()-$_SESSION["act_last_time"]) <10 ){
                $result['msg'] ="对不起，不能频繁提交！";
                die(json_encode($result));
            }
        }else{
            $_SESSION["act_last_time"] = time();
        }
        $params = $_POST;
        if($params['pagehash'] != $_SESSION['page-hash']){
            $result['msg'] = "参数异常!  001";
            die(json_encode($result));
        }
        if(!$params['app_id'] || !$params['login_name'] || !$params['service_name'] || !$params['role_name'] || !$params['mode'] || !$params['desc'] || !$params['verifyCode']){
            $result['msg'] = "缺少必填项";
            die(json_encode($result));
        }
        if(!$this->is_mobile($params['mode'])){
            $result['msg']='手机格式错误。';
            die(json_encode($result));
        }
        if(empty($params['verifyCode']) || strtoupper($params['verifyCode'])!= $_SESSION['c']){
            $result['msg'] = "图形验证码不正确";
            die(json_encode($result));
        }
        if($_SESSION['user_id']){
            $params['user_id'] = $_SESSION['user_id'];
        }
        $this->DAO->insert_feedback($params);
        $result['code'] = 1;
        $result['msg'] = "恭喜反馈成功！客服人员会第一时间给你回复~";
        die(json_encode($result));
    }

    public function is_mobile($str){
        return preg_match('/^13[0-9]{1}[0-9]{8}$|15[012356789]{1}[0-9]{8}$|18[0-9]{9}$|14[57]{1}[0-9]{8}$|17[0678]{1}[0-9]{8}$/', $str) == 1 ? true : false;
    }
}