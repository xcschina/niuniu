<?php
COMMON('baseCore');
DAO('mobile_index_dao');
class mobile_index_admin extends baseCore {
    public $DAO;

    public function __construct(){
        parent::__construct();
        $this->DAO = new mobile_index_dao();
        $this->tags = array(
            '101' => "角色",
            '102' => "策略",
            '103' => "卡牌",
            '104' => "其他"
        );
    }

    function index_view() {
        $_SESSION['login_back_url'] = ltrim($_SERVER['REQUEST_URI'],'/');
        $new_game = $this->DAO->get_new_game();
        $banner_list = $this->DAO->get_banner_list();
        foreach($banner_list as $key=>$data){
            $str = substr($data['url'],-5,strpos($data['url'], '='));
            if(preg_match('/\d+/',$str,$arr)){
                $banner_list[$key]['h5_url'] = "/gameDetail/".$arr[0];
            }
        }
        $new_game_count = $this->DAO->get_new_game_count()['num'];//新品
        $game_list = $this->DAO->get_game_list();
        $count = $this->DAO->get_game_count()['num'];//精品
        $first_banner = $banner_list[0]['banner'];
        $last_banner = $banner_list[count($banner_list) -1]['banner'];
        $this->assign("banner_list", $banner_list);
        $this->assign("game_list", $game_list);
        $this->assign("count", $count);
        $this->assign("new_game",$new_game);
        $this->assign("first_banner",$first_banner);
        $this->assign("last_banner",$last_banner);
        $this->assign("tags_list",$this->tags);
        $this->assign("new_game_count",$new_game_count);
        if($this->isMobile()){
            $this->display("h5/index.html");
        }else{
            $this->redirect("");
        }
    }

    public function get_more_game_list(){
        $result   = array();
        $_POST['start_num'] = ($_POST['page']-1)*10;
        $_POST['page_size'] = $_POST['pagesize']?$_POST['pagesize']:10;
        $flag = 1;
        $new_game_count = '';
        $count = '';
        if($_POST['tags']==""){
            $_POST['is_new'] = 0;
            $new_game_count = $this->DAO->get_new_game_count()['num'];//新品
        }else{
            $_POST['is_new'] = null;
            $count = $this->DAO->get_game_count($_POST['tags'])['num'];//精品
        }
        $products = $this->DAO->get_more_game_list($_POST);
        $now_count = ($_POST['page']-1)*10 + count($products);
        if($new_game_count > $now_count || $count > $now_count){
            $flag = 1;
        }else{
            $flag = 0;
        }
        if($products){
            $result['msg']  = '搜索成功';
            $result['code'] = 1;
            $result['data'] = $products;
            $result['flag'] = $flag;
            die(json_encode($result));
        } else{
            $result['msg']  = '暂无数据';
            $result['code'] = 0;
            $result['data'] = '';
            $result['flag'] = 0;
            die(json_encode($result));
        }
    }

    public function detail_view($id) {
        $_SESSION['login_back_url'] = ltrim($_SERVER['REQUEST_URI'],'/');
        $game_info = $this->DAO->get_game_info($id);
        $this->assign("game_info",$game_info);
        $this->display("h5/detail.html");
    }
    public function enter_view() {
        $this->display("h5/start.html");
    }
}