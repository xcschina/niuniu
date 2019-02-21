<?php
COMMON('baseCore', 'pageCore');
DAO('common_dao');
class website_web extends baseCore{
    public $DAO;
    public $COMDAO;
    public function __construct(){
        parent::__construct();
        $this->COMDAO = new common_dao();
        $links = $this->COMDAO->get_friendly_links();
        $this->assign("links", $links);
    }

    public function zxj_view($id=''){
        if(!empty($id)){
           $down_info = $this->COMDAO->get_promotion_info($id);
        }
        $this->assign("info", $down_info);
        $this->display("website/zxj.html");
    }

    public function blr_view($id=''){
        if(!empty($id)){
            $down_info = $this->COMDAO->get_promotion_info($id);
        }
        $this->assign("info", $down_info);
        $this->display("website/blr.html");
    }

    public function zt3_view($id=''){
        if(!empty($id)){
            $down_info = $this->COMDAO->get_promotion_info($id);
        }
        $this->assign("info", $down_info);
        $this->display("website/zt3.html");
    }

    public function fishing_view(){
        $this->display("website/fishing.html");
    }

    public function blhdx_view(){
        $this->display("website/blhdx.html");
    }

    public function game_template($id){
        $key = array("text","img");
        if(!empty($id)){
            $down_info = $this->COMDAO->get_promotion_info($id);
        }
        $game_info = $this->COMDAO->game_template($id);
        if(!$game_info){
            die( "未查询到该ID所对应的信息");
        }
        $game['middle_img'] = explode(",", $game_info['middle_img']);
        $game['top_img'] = explode(",", $game_info['top_img']);
        $game['bottom_img'] = explode(',', $game_info['bottom_img']);
        $game_page['img1'] = explode(",", $game_info['img1']);
        $game_page['img2'] = explode(",", $game_info['img2']);
        $game_page['img3'] = explode(",", $game_info['img3']);
        $game_page['img4'] = explode(",", $game_info['img4']);
        $game['title'] = $game_info['title'];
        $game['qr_code'] = $game_info['qr_code'];
        $game['down_url'] = $game_info['down_url'];
        $game['box_img'] = $game_info['box_img'];
        $game['game_img'] = explode(',', $game_info['game_img']);
        $game['tabList'] = array(array_combine($key, $game_page['img1']), array_combine($key, $game_page['img2']), array_combine($key, $game_page['img3']), array_combine($key, $game_page['img4']));
        $this->assign("info", $down_info);
        $this->assign("game",$game);
        $this->display("website/activity.html");
    }

    public function general_view($id){
//        $url="http://www.66173yx.com".$_SERVER["REQUEST_URI"];
//        header("HTTP/1.1 301 Moved Permanently");
//        header ("Location:$url");
//        exit();
        $pid = $this->COMDAO->get_info($id);
        if(!$pid['pid']){
            die("此链接出错啦");
        }
        $info = $this->COMDAO->get_info($pid['pid']);
        $info['ios_link'] = $pid['ios_link'];
        $info['id'] = $id;
        $url = $_SERVER['REQUEST_URI'];
        $ip =  $this->client_ip();
        $this->COMDAO->insert_log($url,$ip,$pid);
        $this->assign("bg_img",explode(",",$info['bg_img']));
        $this->assign("info",$info);
        $this->assign("id",$id);
        if($id=='1148'){
            $this->display("website/general4.html");
            exit();
        }
        if($id=='1219'){
            $this->display("website/shenma.html");
            exit();
        }
        if($id=='1248'){
            $this->display("website/shenma.html");
            exit();
        }
        if($info['module'] == 1){
            $this->display("website/general.html");
        }elseif($info['module'] == 2){
            $this->display("website/general2.html");
        }elseif($info['module'] == 3){
            $this->display("website/general3.html");
        }

    }

    public function open_apple_url($apple_id){
        $apple_info = $this->COMDAO->get_apple_info($apple_id);
        if($apple_info['version_url']){
            header("HTTP/1.1 301 Moved Permanently");
            header ("Location:".$apple_info['version_url']);
        }else{
            die("404");
        }
    }

    public function general_down($id){
        $info = $this->COMDAO->get_info($id);
        if(!$info['down_url']){
            die("下载地址出错啦");
        }
        $this->COMDAO->insert_down_log($this->client_ip(),$info,$id);
        header("Location:".$info['down_url']);
    }
}