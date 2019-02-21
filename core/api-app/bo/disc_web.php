<?php
COMMON('baseCore','uploadHelper','pageCore');
DAO('disc_web_dao');
class  disc_web extends baseCore {
    public $DAO;
    public $game_type;

    public function __construct(){
        parent::__construct();
        $this->DAO = new disc_web_dao();
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
    }
    public function top_banner(){
        $result = array("result" => "0", "desc" => "网络请求出错");
        $banner = $this->DAO->get_top_banner();
        if ($banner){
            $result = array("result" => "1", "desc" => "查询成功", "data" => $banner);
        }else{
            $result['desc'] = "未获取到banner数据";
        }
        die("0".base64_encode(json_encode($result)));
    }
    public function game_find(){
        $result = array("result" => "0", "desc" => "网络请求出错");
        $game_list = $this->DAO->get_game_find();
        if ($game_list){
            $result = array("result" => "1", "desc" => "查询成功", "data" => $game_list);
        }else{
            $result['desc'] = "未获取到发现游戏数据";
        }
        die("0".base64_encode(json_encode($result)));
    }
    public function theme_entry(){
        $result = array("result" => "0", "desc" => "网络请求出错");
        $theme_entry = $this->DAO->get_theme_entry();
        if ($theme_entry){
            foreach ($theme_entry as $key=>$value){
                $theme_entry[$key]['theme_title'] = htmlspecialchars_decode($theme_entry[$key]['theme_title']);
                $theme_entry[$key]['theme_subtitle'] = htmlspecialchars_decode($theme_entry[$key]['theme_subtitle']);
            }
            $result = array("result" => "1", "desc" => "查询成功", "data" => $theme_entry);
        }else{
            $result['desc'] = "未获取到热门主题数据";
        }
        die("0".base64_encode(json_encode($result)));
    }
    public function theme_list($page){
        $result = array("result" => "0", "desc" => "网络请求出错");
        $count = $this->DAO->get_theme_count();
        $theme_list = $this->DAO->get_theme_list($page);
        if ($theme_list){
            foreach ($theme_list as $key=>$value){
                $theme_list[$key]['theme_title'] = htmlspecialchars_decode($theme_list[$key]['theme_title']);
                $theme_list[$key]['theme_subtitle'] = htmlspecialchars_decode($theme_list[$key]['theme_subtitle']);
            }
            $result = array("result" => "1", "desc" => "查询成功", "data" => $theme_list,"count"=>$count['count']);
        }else{
            if ($page>1){
                $result['desc'] = "没有更多数据";
            }else{
                $result['desc'] = "未获取到主题列表数据";
            }
        }
        die("0".base64_encode(json_encode($result)));
    }
    public function get_tags($tags_str){
        $tags_arr = explode(",",$tags_str);
        $result = '';
        foreach ($this->game_type as $key=>$value){
            if (in_array($key,$tags_arr)){
                $result .= $value.",";
            }
        }
        return trim($result,",");
    }
    public function game_recommend_entry(){
        $result = array("result" => "0", "desc" => "网络请求出错");
        $game_recommend_entry = $this->DAO->get_game_recommend_entry();
        if ($game_recommend_entry){
//            foreach ($game_recommend_entry as $key=>$value){
//                 $game_recommend_entry[$key]['game_label'] = $this->get_tags($game_recommend_entry[$key]['game_label']);
//            }
            foreach ($game_recommend_entry as $key=>$value){
                    $game_img = explode(",",$value['images']);
                    unset($game_recommend_entry[$key]['images']);
                    $game_recommend_entry[$key]['game_img1'] = $game_img[0];
                    $game_recommend_entry[$key]['game_img2'] = $game_img[1];
                    $game_recommend_entry[$key]['game_img3'] = $game_img[2];
            }
            $result = array("result" => "1", "desc" => "查询成功", "data" => $game_recommend_entry);
        }else{
            $result['desc'] = "未获取到推荐手游数据";
        }
        die("0".base64_encode(json_encode($result)));
    }
    public function game_recommend_list($page){
        $result = array("result" => "0", "desc" => "网络请求出错");
        $count = $this->DAO->get_game_recommend_count();
        $game_recommend_list = $this->DAO->get_game_recommend_list($page);
        if ($game_recommend_list){
//            foreach ($game_recommend_list as $key=>$value){
//                $game_recommend_list[$key]['game_label'] = $this->get_tags($game_recommend_list[$key]['game_label']);
//            }
            foreach ($game_recommend_list as $key=>$value){
                    $game_img = explode(",",$value['images']);
                    unset($game_recommend_list[$key]['images']);
                    $game_recommend_list[$key]['game_img1'] = $game_img[0];
                    $game_recommend_list[$key]['game_img2'] = $game_img[1];
                    $game_recommend_list[$key]['game_img3'] = $game_img[2];
            }
            $result = array("result" => "1", "desc" => "查询成功", "data" => $game_recommend_list,"count"=>$count['count']);
        }else{
            if ($page>1){
                $result['desc'] = "没有更多数据";
            }else{
                $result['desc'] = "未获取到推荐手游列表数据";
            }
        }
        die("0".base64_encode(json_encode($result)));
    }
    public function game_theme_list($theme_id,$page){
        $result = array("result" => "0", "desc" => "网络请求出错");
        $theme = $this->DAO->get_theme($theme_id);
        if ($theme){
            $theme_game_arr = explode(",",$theme['re_game']);
            $theme_game_str = "";
            foreach ($theme_game_arr as $value){
                $theme_game_str .="'".$value."',";
            }
            $theme_game_str = trim($theme_game_str,",");
            $count = $this->DAO->get_game_theme_count($theme_game_str,$theme_id);
            $game_theme_list = $this->DAO->get_game_theme_list($theme_game_str,$theme_id,$page);
            foreach ($game_theme_list as $key=>$val){

                $game_theme_list[$key]['game_title'] = htmlspecialchars_decode($game_theme_list[$key]['game_title']);
                $game_theme_list[$key]['game_subtitle'] = htmlspecialchars_decode($game_theme_list[$key]['game_subtitle']);
            }
            if ($game_theme_list){
                $result = array("result" => "1", "desc" => "查询成功", "data" => $game_theme_list,"theme_picture"=>$theme['theme_picture'],
                    "theme_title"=>htmlspecialchars_decode($theme['theme_title']),"theme_desc"=>htmlspecialchars_decode($theme['theme_desc']),"count"=>$count['count']);
            }else{
                if ($page>1){
                    $result['desc'] = "没有更多数据";
                }else{
                    $result = array("result" => "1", "desc" => "未获取到主题游戏数据", "data" => $game_theme_list,"theme_picture"=>$theme['theme_picture'],
                        "theme_title"=>htmlspecialchars_decode($theme['theme_title']),"theme_desc"=>htmlspecialchars_decode($theme['theme_desc']),"count"=>$count['count']);
//                    $result['desc'] = "未获取到主题游戏数据";
                }
            }
        }else{
            $result['desc'] = "未获取到主题数据";
        }
        die("0".base64_encode(json_encode($result)));
    }
    public function game_article($game_id){
        $result = array("result" => "0", "desc" => "网络请求出错");
        $game_article = $this->DAO->get_game_article($game_id);
        if ($game_article){
            $article_url = "/66apk_disc.php?act=game_article_view&game_id=".$game_id;
            $game_article['article_url'] = $article_url;
            $result = array("result" => "1", "desc" => "查询成功","data"=>$game_article);
        }else{
            $result['desc'] = "未获取到游戏文章数据";
        }
        die("0".base64_encode(json_encode($result)));
    }
    public function game_article_view($game_id){
        if ($game_id){
            $game_article_details = $this->DAO->get_game_article_details($game_id);
            if ($game_article_details){
                $game_article_details['disc_img_text'] = htmlspecialchars_decode($game_article_details['disc_img_text']);
                $this->assign("info",$game_article_details);
                $this->display("disc_articles_info.html");
            }else{
                die("该游戏不存在");
            }
        }else{
            die("没有该游戏文章");
        }
    }
}

?>