<?php
COMMON('baseCore');
DAO('article_dao');
class article_wx  extends baseCore{
    public $DAO;

    public function __construct(){
        parent::__construct();
        $this->DAO=new article_dao();
    }

    public function get_game_gift($open_id, $game_id){
        $game_info = $this->DAO->get_game_info($game_id);
        if(empty($game_info)){
            return '暂时没有这款游戏，随后杀到～';
        }
        $is_gift = $this->DAO->get_usr_gift($open_id, $game_id);
        if($is_gift){
            return '你已经领过啦';
        }
        $gift = $this->DAO->get_game_gift($game_id);
        if(!$gift){
            return "【".$game_info['game_name'].'】暂时没有兑换码，请对66173保持关注！';
        }
        $this->DAO->user_gift_get($open_id, $gift['id'], $game_id);
        return "恭喜您，兑换码是：\r\n【".$gift['code']."】\r\n".$game_info['gift_guide'];
    }

    public function get_setting(){
        $info = $this->DAO->get_setting();
        return $info;
    }
    public function get_setting_by_id($id){
        $info = $this->DAO->get_setting_by_id($id);
        return $info;
    }

    public function search_game($keyword){
        $games = $this->DAO->search_game($keyword);
        $str = "";
        $url = "https://open.weixin.qq.com/connect/oauth2/authorize?appid=wxbaed68c7f2f3a62c&redirect_uri=";
        foreach($games as $k=>$game){
            $str.='<a href="'.$url.urlencode('http://wx.66173.cn/gifts.php?act=game&id='.$game['id']).'&response_type=code&scope=snsapi_userinfo&state=STATE#wechat_redirect">【'.$game['game_name'].'礼包】</a>';
//            if($game['game_name']=='天龙八部3D'){
//                $str.="\r\n";
//                $str.='参与'.$game['game_name'].'活动,请戳→<a href="http://mp.weixin.qq.com/s?__biz=MzA4NDk5NjM4Mg==&mid=403438483&idx=1&sn=9e0cc542a020a0f346318512555f4955&scene=23&srcid=0415L7zOST4aSWMTtmiuWsIn#rd">这里</a>';
//            }
            $str.="\r\n";
        }
        return $str;
    }

    public function article_list($part_id){
        $article_list = $this->DAO->get_articles_list($part_id, $this->page);

        if($this->page<2){
            $this->assign("part_id",$part_id);
            $this->assign("list", $article_list);
            $this->display("article_list.html");
        }else{
            foreach($article_list as $k=>$v){
                $article_list[$k]['intro'] = strip_tags(htmlspecialchars_decode($v['intro']));
            }
            die(json_encode($article_list, true));
        }
    }

    public function article_item($id){
        $info = $this->DAO->get_article_info($id);
        $this->assign("info",$info);
        $this->display("article_item.html");
    }
}