<?php
COMMON('baseCore','uploadHelper','pageCore');
DAO('apk_dao');

class apk_web extends baseCore{

    public $DAO;
    public $COMMON;

    public function __construct(){
        parent::__construct();
        $this->DAO = new apk_dao();
        $this->game_type = array(
            101 => '动作',
            102 => '角色',
            103 => '射击',
            104 => '策略',
            105 => '即时',
            106 => '回合',
            107 => '休闲',
            108 => '冒险',
            109 => '模拟',
            110 => '竞技',
            111 => '卡牌',
            112 => '体育',
            113 => '格斗',
            114 => 'MOBA');
        if (isset($_SERVER['HTTP_USER_AGENT1'])) {
            $header = base64_decode(substr($_SERVER['HTTP_USER_AGENT1'], 1));
            $header = explode("&", $header);
            foreach ($header as $k => $param) {
                $param = explode("=", $param);
                if ($param[0] == 'user_id') {
                    $this->user_id = $param[1];
                }
            }
        }
    }

    public function top_banner(){
        $result = array("result" => "0", "desc" => "网络请求出错");
        $banner = $this->DAO->get_top_banner();
        if($banner){
            foreach($banner as $key=>$data) {
                $banner[$key]['url']=str_replace("&amp;","&",$data['url']);
            }
            $result = array("result" => "1", "desc" => "查询成功", "data" => $banner);
        }else{
            $result['desc'] = "未能获取到数据";
        }
        die("0".base64_encode(json_encode($result)));
    }

    public function fine_game(){
        $result = array("result" => "0", "desc" => "网络请求出错");

        $fine_game = $this->DAO->get_fine_game();
        if($fine_game){
            $result = array("result" => "1", "desc" => "查询成功", "data" => $fine_game);
        }else{
            $result['desc'] = "未能获取到精品游戏数据";
        }
        die("0".base64_encode(json_encode($result)));
    }

    public function fine_list(){
        $result = array("result" => "0", "desc" => "网络请求出错");
        $fine_game = $this->DAO->get_fine_game_list($this->page);
        if($fine_game){
            $count = $this->DAO->get_game_count(1);
            $result = array("result" => "1", "desc" => "查询成功", "data" => $fine_game,"count"=>$count['num']);
        }else{
            $result['desc'] = "未能获取更多66精品游戏数据";
        }
        die("0".base64_encode(json_encode($result)));
    }

    public function tx_list(){
        $result = array("result" => "0", "desc" => "网络请求出错");
        $tx_game = $this->DAO->get_tx_game_list($this->page);
        if($tx_game){
            $count = $this->DAO->get_game_count(2);
            $result = array("result" => "1", "desc" => "查询成功", "data" => $tx_game,"count"=>$count['num']);
        }else{
            $result['desc'] = "未能获取更多腾讯精品游戏数据";
        }
        die("0".base64_encode(json_encode($result)));
    }

    public function tx_game(){
        $result = array("result" => "0", "desc" => "网络请求出错");
        $tx_game = $this->DAO->get_tx_game();
        if($tx_game){
            $result = array("result" => "1", "desc" => "查询成功", "data" => $tx_game);
        }else{
            $result['desc'] = "未能查询到腾讯精品数据";
        }
        die("0".base64_encode(json_encode($result)));
    }

    public function all_list(){
        $result = array("result" => "0", "desc" => "网络请求出错");
        $all_game = $this->DAO->get_game_list($this->page);

        if($all_game){
            $count = $this->DAO->get_all_game_count();
            $result = array("result" => "1", "desc" => "查询成功", "data" => $all_game,"count"=>$count['num']);
        }else{
            $result['desc'] = "未能获取所有游戏数据";
        }
        die("0".base64_encode(json_encode($result)));
    }

    public function new_game(){
        $result = array("result" => "0", "desc" => "网络请求出错");
        $new_game = $this->DAO->get_new_game();
        if($new_game){
            $result = array("result" => "1", "desc" => "查询成功", "data" => $new_game);
        }else{
            $result['desc'] = "未能查询到每月新游数据";
        }
        die("0".base64_encode(json_encode($result)));
    }

    public function game_detail($game_id){
//        $this->open_debug();
        $result = array("result" => "0", "desc" => "网络请求出错");
        $game_detail = $this->DAO->get_game_detail($game_id);
        if($game_detail){
            $game_detail['tag']='';
            if(!empty($game_detail['tags'])){
                $game_detail['tag'] = $this->get_tags_str($game_detail['tags']);
            }
            $result = array("result" => "1", "desc" => "查询成功", "data" => $game_detail);
        }else{
            $result['desc'] = "未能查询到该游戏详情";
        }
        die("0".base64_encode(json_encode($result)));
    }

    public function game_gift($game_id){
        $result = array("result" => "0", "desc" => "网络请求出错");
        $game_gift = $this->DAO->get_game_gifts($game_id);
        if(empty($game_gift)){
            $result['desc'] = "当前游戏没有礼包";
            die("0".base64_encode(json_encode($result)));
        }else{
            foreach($game_gift as $k=>$gift){
                if($gift['type']==0){
                    $game_gift[$k]['gift_name']="新手礼包";
                }elseif ($gift['type']==1){
                    $game_gift[$k]['gift_name']="特权礼包";
                } elseif ($gift['type']==2){
                    $game_gift[$k]['gift_name']="白银礼包";
                }elseif ($gift['type']==3){
                    $game_gift[$k]['gift_name']="专属礼包";
                }
                $game_gift[$k]['gift_state'] = 0;
                if($this->user_id){
                    $my_gifts = $this->DAO->get_user_gift_batch($this->user_id,$gift['id']);
                    if($my_gifts){
                        $game_gift[$k]['gift_state'] = 1;
                    }
                }
                $result = array("result" => "1", "desc" => "查询成功" , "data" => $game_gift);
            }
        }

        die("0".base64_encode(json_encode($result)));
    }

    public function activity_center(){
        $result = array("result" => "0", "desc" => "网络请求出错");
        $center = $this->DAO->get_activity_center();
        if($center){
            $count = $this->DAO->get_activity_center_count();
            foreach($center as $key =>$data){
                $center[$key]['url']=str_replace("&amp;","&",$data['url']);
                $center[$key]['status'] = 0; //默认状态
                if($data['start_time'] > time()){
                    $center[$key]['status'] = 1; //即将开始
                }elseif($data['start_time'] < time() && $data['end_time'] > time()){
                    $center[$key]['status'] = 2; //进行中
                }else{
                    $center[$key]['status'] = 3;//已结束
                }
            }
            $result = array("result" => "1", "desc" => "查询成功", "count" => $count['num'], "data" => $center);
        }else{
            $result['desc'] = "活动还没上线";
        }
        die("0".base64_encode(json_encode($result)));
    }

    public function receive_gift($id){
        $result = array("result" => "0", "desc" => "网络请求出错");
        if(!$this->user_id){
            $result['desc']="用户未登陆";
            die("0".base64_encode(json_encode($result)));
        }
        $usr_gifts = $this->DAO->get_user_gifts($this->user_id,$id);
        if($usr_gifts){
            $result['desc']="您已领取过了";
            die("0".base64_encode(json_encode($result)));
        }
        $this->get_gift($id,$this->user_id);
    }

    public function get_gift($id,$user_id){
        $result = array("result" => 0);
        $fp = fopen(PREFIX."/htdocs/gifts.txt","w+");//增加排它锁
        if(!flock($fp,LOCK_EX|LOCK_NB)) {
            $result['desc']="排队失败，请重新尝试";
            die("0".base64_encode(json_encode($result)));
        }
        $gift_batch = $this->DAO->get_gift_info($id);
        if($gift_batch['end_time'] < strtotime("now")){
            flock($fp,LOCK_UN);
            $result['desc']="礼包已过期";
            die("0".base64_encode(json_encode($result)));
        }

        //抢光了
        if(!$gift_batch || $gift_batch['remain'] <1){
            flock($fp,LOCK_UN);
            $result["desc"]="靠，被抢光了！吊打GM！";
            die("0".base64_encode(json_encode($result)));
        }
        $gift_code = $this->DAO->get_gift($id);
        if(!$gift_code){
            $result["desc"]="靠，被抢光了！吊打GM！";
            die("0".base64_encode(json_encode($result)));
        }else{
            $this->DAO->update_code_status($gift_code, $user_id, $id);
            flock($fp,LOCK_UN);
            $result=array("result"=>1,"desc"=>"领取成功","gift_number"=>$gift_code['code']);
            die("0".base64_encode(json_encode($result)));
        }
    }

    public function get_tags_str($tags){
        $tag_array = explode(',', $tags);
        $new_tags = array();
        foreach($tag_array as $k=>$data){
            array_push($new_tags,$this->game_type[$data]);
        }
        return  implode(',', $new_tags);
    }

    public function nn_agreement(){
        $this->display("nn_agreement.html");
    }

    public function usage_rules(){
        $this->display("usage_rules.html");
    }

    public function qb_rate(){
        $result = array("result" => 0, "desc" => "网络请求出错");
        $rate = $this->DAO->get_qq_rate(1);
        if(empty($rate)) {
            $rate = 10;
        }
        $result = array("result" => 1, "rate" => $rate);
        die("0".base64_encode(json_encode($result)));
    }

    public function my_gifts(){
        $result = array("result" => 0, "desc" => "网络请求出错");
        if(empty($this->user_id)){
            $result['desc'] = "发生错误，用户未登录";
            die("0".base64_encode(json_encode($result)));
        }
        $count = $this->DAO->get_gifts_count($this->user_id);
        $my_gifts = $this->DAO->get_my_gifts($this->user_id,$this->page);
        if($my_gifts) {
            $result = array("result" => 1, "desc" => "查询成功", "count" => $count['num'], "data" => $my_gifts);
        }else{
            $result['desc'] = "未能查询到礼包数据";
        }
        die("0".base64_encode(json_encode($result)));
    }


    public function nnb_num(){
        $result = array("result" => "0", "desc" => "网络请求出错");
        if(!$this->user_id){
            $result['desc']='缺少用户信息';
            die("0".base64_encode(json_encode($result)));
        }
        $nnb_num = $this->DAO->get_user_nnb_num($this->user_id);
        if($nnb_num){
            $result = array("result" => "1", "desc" => "查询成功",'count'=>$nnb_num['nnb']);
        }else{
            $result = array("result" => "1", "desc" => "查询成功",'count'=>0);
        }
        die("0".base64_encode(json_encode($result)));
    }

    public function pop_list(){
        $result = array('result'=>0,'desc'=>'网络请求出错');
        $data_list = $this->DAO->get_pop_list();
        $result['result'] = 1;
        $result['desc'] = '查询成功';
        $result['data'] = $data_list;
        die("0".base64_encode(json_encode($result)));
    }

    public function hot_search(){
        $result = array('result'=>0,'desc'=>'网络请求出错');
        $hot_search = $this->DAO->get_hot_search();
        $result['result'] = 1;
        $result['desc'] = '查询成功';
        $result['data'] = $hot_search;
        die("0".base64_encode(json_encode($result)));
    }

    public function search_list(){
        $result = array('result'=>0,'desc'=>'网络请求出错');
        $keyword = $_GET['keyword'];
        $page = $_GET['page'];
        if(!$page){
            $page = $this->page;
        }
        if($keyword){
            $count = $this->DAO->get_search_count($keyword);
            $search_list = $this->DAO->get_search_list($keyword,$page);
            $result['desc'] = "查询成功";
            $result['count'] = $count['count'];
            $result['data'] = $search_list;
        }else{
            $result['desc'] = "查询成功";
            $result['count'] = 0;
            $result['data'] = array();
        }
        $result['result'] = 1;
        die("0".base64_encode(json_encode($result)));
    }
}