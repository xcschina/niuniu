<?php
COMMON('sdkCore','uploadHelper');
DAO('validate_dao');

class validate_web extends sdkCore{

    public $DAO;
    public $qa_user_id;

    public function __construct(){
        parent::__construct();
        $this->DAO = new validate_dao();
        if(isset($_SERVER['HTTP_USER_AGENT1'])){
            $header = base64_decode(substr($_SERVER['HTTP_USER_AGENT1'],1));
            $header = explode("&",$header);
            foreach($header as $k=>$param){
                $param = explode("=",$param);

                if($param[0] == 'sdkver'){
                    $this->sdkver = $param[1];
                }elseif($param[0] == 'channel'){
                    $this->guild_code = $param[1];
                }elseif($param[0] == 'ver'){
                    $this->gameVer = $param[1];
                }
            }
        }
    }

    public function verify_api($data){
        $result = array('error'=>1,'msg'=>'网络异常！');
        $this->verify_data($data);
        //查询游戏防刷设定
        $app_info = $this->DAO->get_app_info($data['app_id']);
        if(!$app_info || $app_info['verify_type']=='0'){
            $result['msg']='参数异常！';
            die("0".base64_encode(json_encode($result)));
        }elseif($app_info['verify_type']=='1' || $app_info['verify_type']=='2'){
            //等级验证
//            $result = array('error' => 0, 'msg' => '等级验证！', 'time' => $data['timestamp'] , 'status' => $app_info['verify_type'], 'number' => 3, 'last_level' => '1');
            //时间验证
//            $result = array('error' => 0, 'msg' => '时间验证！', 'time' => $data['timestamp'] ,'status' => $app_info['verify_type'],'in_time' => '1', 'start_time' => $data['timestamp']);
            $verify_content = $this->DAO->get_sdk_rules($app_info['verify_content']);
            $verify_content_arr = array();
            foreach ($verify_content as $value){
                array_push($verify_content_arr,$value['content']);
            }
            sort($verify_content_arr);
            $verify_content_str = implode(",",$verify_content_arr);
            if ($app_info['verify_type']=='1'){
                $result = array('error' => 0, 'msg' => '等级验证！', 'time' => $data['timestamp'] , 'status' => $app_info['verify_type'], 'number' => $verify_content_str);
            }else{
                $result = array('error' => 0, 'msg' => '时间验证！', 'time' => $data['timestamp'] , 'status' => $app_info['verify_type'],'in_time' => $verify_content_str);
            }
            die("0".base64_encode(json_encode($result)));
        }else{
            $result['msg']='异常类型！';
            die("0".base64_encode(json_encode($result)));
        }
    }

    public function get_problems($data){
        $result = array('error'=>1,'msg'=>'网络异常！');
        $this->verify_data($data);
        //查询游戏防刷设定
        $app_info = $this->DAO->get_app_info($data['app_id']);
        $sign = $data['app_id'].$data['timestamp'].$app_info['app_key'];
        if(!$app_info || $app_info['verify_type']=='0'){
            $result['msg']='参数异常！';
        }elseif($app_info['verify_type']=='1' || $app_info['verify_type']=='2'){
            $params = $this->create_problems();
            $id = $this->DAO->add_problems_log($data,$params);
            if(empty($id)){
                $result['msg']='问题创建失败！';
                die("0".base64_encode(json_encode($result)));
            }
            if ($app_info['verify_type']=='1'){
                $result = array('error' => 0, 'msg' => '等级验证！', 'time' => $data['timestamp'], 'problems' => $params['problems'], 'id' =>$id,'sign'=>md5($sign));
            }elseif ($app_info['verify_type']=='2'){
                $result = array('error' => 0, 'msg' => '时间验证！', 'time' => $data['timestamp'], 'problems' => $params['problems'], 'id' =>$id,'sign'=>md5($sign));
            }
        }else{
            $result['msg']='异常类型！';
        }
        die("0".base64_encode(json_encode($result)));
    }

    public function get_answer($data){
        $result = array('error'=>1,'msg'=>'网络异常！');
        $this->verify_data($data);
        //查询游戏防刷设定
        $app_info = $this->DAO->get_app_info($data['app_id']);
        $sign = $data['app_id'].$data['timestamp'].$app_info['app_key'];
        if(!$app_info || $app_info['verify_type']=='0'){
            $result['msg']='参数异常！';
        }elseif($app_info['verify_type']=='1' || $app_info['verify_type']=='2'){
            if (!empty($_POST['qid'])) {
                $problems_log = $this->DAO->get_problems_log($_POST['qid']);
                if ($_POST['qan'] == $problems_log['answer']) {
                    $result = array('error' => 0, 'msg' => '验证成功！', 'time' => $data['timestamp'],'sign'=>md5($sign));
                    die("0" . base64_encode(json_encode($result)));
                }else{
                    $result['error'] = 2;
                    $result['msg'] = '结果错误！';
                    die("0" . base64_encode(json_encode($result)));
                }
            }
            $result['error'] = 2;
            $result['msg'] = '没有查询到问题！';
        }else{
            $result['msg']='异常类型！';
        }
        die("0".base64_encode(json_encode($result)));
    }


    public function create_problems(){
        $first = rand(1,15);
        $second = rand(1,10);
        $tool = rand(1,3);
        if($first < $second){
            $tool = rand(1,2);
        }
        switch ($tool){
            case "1": //加
                $problems = $first." + ".$second." = ? ";
                $answer = $first + $second;
                break;
            case "2": //乘
                $problems = $first." x ".$second." = ? ";
                $answer = $first * $second;
                break;
            case "3": //减
                $problems = $first." - ".$second." = ? ";
                $answer = $first - $second;
                break;
        }
        $data = array(
            'problems' => $problems,
            'answer' => $answer
        );
        return $data;
    }


    public function verify_data($data){
        $result = array('error'=>1,'msg'=>'缺少必要参数！');
        if(empty($data['app_id'])){
            $result['msg']='app_id';
            die("0".base64_encode(json_encode($result)));
        }
        if(empty($data['user_id'])){
            $result['msg']='user_id';
            die("0".base64_encode(json_encode($result)));
        }
        if(empty($data['role_id'])){
            $result['msg']='role_id';

            die("0".base64_encode(json_encode($result)));
        }
        if(empty($data['role_name'])){
            $result['msg']='role_name';

            die("0".base64_encode(json_encode($result)));
        }
        if(empty($data['role_level'])){
            $result['msg']='role_level';

            die("0".base64_encode(json_encode($result)));
        }
        if(empty($data['area_server_id'])){
            $result['msg']='area_server_id';

            die("0".base64_encode(json_encode($result)));
        }
        if(empty($data['area_server_name'])){
            $result['msg']='area_server_name';

            die("0".base64_encode(json_encode($result)));
        }
    }

}
