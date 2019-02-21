<?php
COMMON('baseCore', 'pageCore');
DAO('article_dao','common_dao','index_dao');
class article_web extends baseCore{
    public $DAO;
    public $COMDAO;

    public function __construct(){
        parent::__construct();
        $this->DAO = new article_dao();
        $data['guiders']     = $this->DAO->get_mod_articles(1);
        $data['servicies']   = $this->DAO->get_mod_articles(2);
        $data['aboutus']     = $this->DAO->get_mod_articles(3);
        $data['vouchers']    = $this->DAO->get_mod_articles(4);
        $data['guarantees']  = $this->DAO->get_mod_articles(5);
        $data['questions']   = $this->DAO->get_mod_articles(6);
        $data['regpic'][0]   = $this->DAO->get_article_info(6387);

        $this->assign('data',$data);
    }

    public function get_articles_list($id){
        $params=$_GET;
        $this->assign("canonical", "http://".$_SERVER['HTTP_HOST']."/info/list".$id);
        $part_name     =  $this->get_part_name($id);
        $article_list  =  $this->DAO->get_articles_list($params,$this->page);
        $page  =  $this->pageshow($this->page, 'info/list'.$id.'?');
        $result = $this->get_http_host();
        $domain_name = $result['domain_name'];
        $name = $result['name'];
        if($id==13){
            $this->assign("web_des", $name.'('.$_SERVER['HTTP_HOST'].')是国内权威的游戏交易平台，安全快捷有保障的手游充值中心。'.$name.'提供最专业的游戏新闻资讯，最潮流的玩法，最完善的游戏攻略，最齐全的新游玩法推荐。');
        }

        $this->assign('domain_name',$domain_name);
        $this->assign("title",$part_name);
        $this->assign("page_show", $page->show(9));
        $this->assign("article_list", $article_list);
        $this->assign("pid",$params['part_id']);
        if($params['part_id']>7){
            $index_dao = new index_dao();
            $hot_games = $index_dao->get_hot_games();
            $new_game_news = $this->DAO->get_articles_list(array('part_id'=>13));

            $this->assign("hot_games", $hot_games);
            $this->assign("new_game_news", $new_game_news);
            $this->display("article_game_list.html");
        }else{
            $this->display("article_list.html");
        }
    }

    public function get_article($id){
        $result = $this->get_http_host();
        $domain_name = $result['domain_name'];
        $name = $result['name'];
        $this->assign('domain_name',$domain_name);
        if($this->is_mobile_client()){
            header("Location:http://m.66173.cn/info/".$id);
        }
        $this->assign("canonical", "http://www.66173.cn/info/".$id);
        $article = $this->DAO->get_article_info($id);
        if(!$article){
            die("鱼不可脱于渊，国之利器不可以示人");
        }
        $article['ot'] = $this->fig_time(date("Y-m-d H:i:s",$article['add_time']));
        $article['part_name'] = $this->get_part_name($article['part_id']);
        $game_info = array();
        $game_news = array();

        if($article['game_id']){
            $game_info = $this->DAO->get_game_info($article['game_id']);
            $web_key = $article['tags']?:$game_info['game_name']."，".$game_info['game_name']."最新资讯，".$game_info['game_name']."游戏资讯，".$name."手游新闻资讯";
            $web_des = $article['summary']?:$name."游戏新闻提供".$game_info['game_name']."游戏攻略、".$game_info['game_name']."游戏公告、".$game_info['game_name']."游戏测评、".$game_info['game_name']."游戏资讯，获取".$game_info['game_name']."相关资讯就上".$name."。(".$_SERVER['HTTP_HOST'].")是国内权威的游戏交易平台，安全快捷有保障的手游充值中心。";
        }else{
            $web_des = $article['title']."，".$name."手游新闻资讯";
            $web_key = "";
        }

        if($article['tags']){
            $article['tags'] = explode("，", $article['tags']);
        }

        if($game_info){
            $game_strategy = $this->DAO->get_game_strategy($article['game_id'],18);
            $game_news = $this->DAO->get_game_strategy($article['game_id'],16);
        }

        $this->assign("article",$article);
        $this->assign("web_key",$web_key);
        $this->assign("web_des",$web_des);
        $this->assign("game",$game_info);
        $this->assign("game_news",$game_news);
        $this->assign("game_strategys",$game_strategy);
        $this->assign("title",$article['title']);
        $this->assign("pid",$article['part_id']);
        $this->assign("sid",$article['id']);
        $this->assign("show_zx",array(16)); // 显示游戏资讯子栏目集合);

        if($article['game_id']){
            $prve_article = $this->DAO->get_prve_article_info($id,$article['part_id'],$article['game_id']);
            $next_article = $this->DAO->get_next_article_info($id,$article['part_id'],$article['game_id']);
            $this->assign("prve_article",$prve_article);
            $this->assign("next_article",$next_article);
            $this->display("article_game.html");
        }else{
            $this->display("article.html");
        }
    }

    private function get_part_name($part_id = 0){
        $part_name  = "";
        $parts_list = $this->DAO->get_parts_list();
        if($part_id){
            foreach($parts_list as $data){
                if($data['id'] == $part_id){
                    $part_name = $data['name'];
                    break;
                }
            }
        }
        return $part_name;
    }



}
?>
