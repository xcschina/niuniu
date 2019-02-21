<?php
COMMON('baseCore', 'pageCore');
DAO('game_dao');
BO('index_admin');
class game_admin extends baseCore {
    public $DAO;
    public $tags;
    public  $bo;

    public function __construct(){
        parent::__construct();
        $this->DAO = new game_dao();
        $this->tags = array(
            '101' => "角色",
            '102' => "策略",
            '103' => "卡牌",
            '104' => "其他"
        );
        $this->bo = new index_admin();
    }

    public function list_view() {
        $_SESSION['login_back_url'] = ltrim($_SERVER['REQUEST_URI'],'/');
        $params = $_GET;
        if($params['page']){
            $this->page = $params['page'];
        }
        $game_list = $this->DAO->get_game_list($this->page,$params);
        $game_count = $this->DAO->get_game_count($params);
        $page_num = $game_count['num']%9;
        if($game_count['num']){
            if($page_num){
                $num = intval($game_count['num']/9)+1;
            }else{
                $num = intval($game_count['num']/9);
            }
        }else{
            $num = 1;
        }
        $this->page_hash();
        $wx = $this->bo->wx_share();
        $this->assign("noncestr", $wx['noncestr']);
        $this->assign("timestamp", $wx['timestamp']);
        $this->assign("signature", $wx['signature']);
        $this->assign("num",$num);
        $this->assign("currentTag",$params);
        $this->assign("tags_list",$this->tags);
        $this->assign("game_list",$game_list);
        $this->display("game_center.html");
    }

    public function detail_view($id) {
        $_SESSION['login_back_url'] = ltrim($_SERVER['REQUEST_URI'],'/');
        $game_info = $this->DAO->get_game_info($id);
        $wx = $this->bo->wx_share();
        $this->assign("noncestr", $wx['noncestr']);
        $this->assign("timestamp", $wx['timestamp']);
        $this->assign("signature", $wx['signature']);
        $this->assign("game_info",$game_info);
        $this->display("game_detail.html");
    }

    public function ajax(){
        $result = array("code"=>0,"msg"=>"网络出错");
        $params = $_POST;
        if($params['pagehash'] != $_SESSION['page-hash']){
            $result['msg'] = "参数异常!  001";
            die(json_encode($result));
        }
        $game_list = $this->DAO->get_game_list($params['page'],$params);
        $game_count = $this->DAO->get_game_count($params);
        $page_num = $game_count['num']%9;
        if($game_count['num']){
            if($page_num){
                $num = intval($game_count['num']/9)+1;
            }else{
                $num = intval($game_count['num']/9);
            }
        }else{
            $num = 1;
        }
        $result['code'] = 1;
        $result['msg'] = "查询成功";
        $result['data'] = $game_list;
        $result['currentPage'] = $params['page'];
        $result['totalPage'] = $num;
        die(json_encode($result));
    }


}