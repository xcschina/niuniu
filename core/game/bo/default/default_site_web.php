<?php
COMMON('baseCore','pageCore');
DAO('default/default_site_dao');
class default_site_web extends baseCore{

    public $DAO;
    public $id;
    private $template_info;
    private $column = array(
        array('官网首页','/'),
        array('活动公告','/article_list.php?id=1'),
        array('图鉴攻略','/article_list.php?id=4'),
        array('游戏论坛','/article_list.php?id=5'),
    );

    public function __construct($template_info){
        parent::__construct();

        $this->DAO = new default_site_dao();
        $this->template_info = $template_info;
        $this->module = array(
        	1 => '公告',
        	2 => '资讯',
        	3 => '活动',
        	4 => '图鉴',
        	5 => '攻略',
        	6 => '经典视频',
        	7 => '常见问题'
        );

        $this->V->assign("is_ipad",$this->is_ipad());

        $_SESSION['login_redirect'] = 'http://' . $_SERVER['HTTP_HOST'];

        if($_SERVER['REQUEST_URI']){
            $_SESSION['login_redirect'] .= $_SERVER['REQUEST_URI'];
            if($this->is_ajax_request()){
                $_SESSION['login_redirect'] = $_SERVER['HTTP_REFERER'];
            }
        }
    }

    public function index_view(){
        $this->page_side();
        $banners = $this->DAO->get_banners(GAME_ID,2);
        $active_annouce = $this->DAO->get_article(GAME_ID, 18);
        $active_annouce = array_slice($active_annouce, 0, 7);
        $this->V->assign('webtitle','官网首页');
        $this->V->assign('banners', $banners);
        $this->V->assign('articles', $active_annouce);
        $this->V->display($this->template_info['template_name'].'/index.htm');
    }

    public function article_list_view($moudule_id){
        if(!in_array($moudule_id, array_keys($this->module))) {
            $this->index_view();
            exit();
        }
        $banners = $this->DAO->get_banners(GAME_ID,5);
        $this->V->assign('banners', $banners);

        //公共内容
        $this->page_side();
        switch($moudule_id){
            case 5:
                $this->V->assign('moudule_id', $moudule_id);
                $this->V->assign('webtitle', '游戏论坛');
                break;
            case 4:
                $active_annouce = $this->DAO->get_article(GAME_ID, 18);
                $active_annouce = array_slice($active_annouce, 0, 20);
                $this->V->assign('articles', $active_annouce);
                $this->V->assign('moudule_id', $moudule_id);
                $this->V->assign('webtitle', '图鉴攻略');
                break;
            default:
                $active_annouce = $this->DAO->get_article(GAME_ID, 16);
                $active_annouce = array_slice($active_annouce, 0, 20);
                $this->V->assign('articles', $active_annouce);
                $this->V->assign('moudule_id', $moudule_id);
                $this->V->assign('webtitle','活动公告');
        }
        $this->V->assign('module', $moudule_id);
        $this->V->display($this->template_info['template_name'].'/index_news.htm');
        $_SESSION['error'] = '';
    }

    public function article_view($id){
        //公共内容
        $this->page_side();
        //获得文章信息
        $article = $this->DAO->get_article_info($id);
        if(empty($_GET['type']) && !$article){
            $this->index_view();
            exit(0);
        }

        $article['intro'] = htmlspecialchars_decode($article['intro']);
        if($article['part_id'] == '18') {
            $this->V->assign('webtitle', '图鉴攻略');
        } else {
            $this->V->assign('webtitle', '活动公告');
        }
        $this->V->assign('article',$article);
        $this->V->assign('id', $id);
        $this->V->display($this->template_info['template_name'].'/detail.htm');
        $_SESSION['result'] = 0;
    }

//    //获取更多
//    public function get_more($id) {
//        $reply = $this->DAO->get_reply_list($id);
//        $reply = array_chunk($reply, 10);
//        $reply = $reply[$this->page - 1];
//        foreach($reply as $key => $val) {
//            $ot = time() - strtotime($reply[$key]['reply_time']);
//            if($ot < 18000) {
//                $ot = ($ot/3600)< 1 ? 1 : floor($ot/3600);
//                $reply[$key]['reply_time'] = $ot .'小时前';
//            }
//        }
//        echo json_encode($reply);
//        exit;
//    }

    public function wmw_view(){
        $banners = $this->DAO->get_banners(GAME_ID,5);
        $this->V->assign('banners', $banners);

        //公共内容
        $this->page_side();
        $this->V->display($this->template_info['template_name'].'/wmw.htm');
    }


    public function pay_view(){
        //公告部分

        $info = $this->page_side();
        $exchanges = $this->DAO->exchanges_data(GAME_ID);
        $servers = $this->get_game_server(GAME_ID, $info);
        $banks = $this->DAO->get_banks();

        $this->V->assign("info", $info);
        $this->V->assign("servers", $servers);
        $this->V->assign("exchanges", $exchanges);
        $this->V->assign("time", strtotime("now"));
        $this->V->assign("banks", $banks);
        $this->V->assign('webtitle', '官网首页');
        $this->V->assign('game_id', GAME_ID);
        $this->V->display($this->template_info['template_name'].'/buy.htm');
        $_SESSION['error'] = '';
        $_SESSION['order_id_ex'] = '';
        $_SESSION['order_success'] = '';
        $_SESSION['order_error'] = '';
    }


    private function get_game_server($game_id, $game_info){
        $servers="";
        $game_info['webpay_dict_url']=$_SERVER['HTTP_HOST']."/dict.txt";
        if(!$servers && $game_info['webpay_dict_url']){
            $servers = $this->request($game_info['webpay_dict_url']);
        }

        $servers = json_decode($servers);
        return $this->object_to_array($servers->serv);

    }

    public function object_to_array($obj){
        $_arr = is_object($obj) ? get_object_vars($obj) : $obj;
        foreach ($_arr as $key => $val) {
            $val = (is_array($val) || is_object($val)) ? $this->object_to_array($val) : $val;
            $arr[$key] = $val;
        }
        return $arr;
    }

    //侧边栏目
    private function page_side(){
        //游戏信息
        $info = $this->DAO->get_game_info(GAME_ID);
        $this->V->assign('info',$info);
        //常见问题
        $article_qa = $this->DAO->get_article(GAME_ID, 6);
        //下载地址
        $downs = $this->DAO->get_app_downs(GAME_ID);
        $links= $this->DAO->get_links();
        $links = array_slice($links, 0, 5);

        $this->V->assign('faq',$article_qa);
        $this->V->assign('down',$downs);
        $this->V->assign('links', $links);
        $this->V->assign('width',floor(970/count($this->column)));
        $this->V->assign('column',$this->column);

        return $info;
    }
}
?>