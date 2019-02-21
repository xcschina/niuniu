<?php
COMMON('baseCore','uploadHelper','pageCore');
DAO('api_dao');

class api_web extends baseCore{

    public $DAO;
    public $COMMON;

    public function __construct(){
        parent::__construct();
        $this->DAO = new api_dao();
        if(isset($_SERVER['HTTP_USER_AGENT1'])){
            $header = base64_decode(substr($_SERVER['HTTP_USER_AGENT1'],1));
            $header = explode("&",$header);
            foreach($header as $k=>$param){
                $param = explode("=",$param);
                if($param[0] == 'user_id'){
                    $this->user_id = $param[1];
                }
            }
        }
    }

    public function get_banner(){
        $result = array("result" => "0", "desc" => "网络请求出错");
        $banner = $this->DAO->get_banner();
        if(!empty($banner)) {
            $result = array("result" => "1", "desc" => "数据查询成功", "data" => $banner);
        }else{
            $result['desc'] = '未查询到banner';
        }
        die("0".base64_encode(json_encode($result)));
    }

    public function hot_game(){
        $result = array("result" => "0", "desc" => "网络请求出错");
        $hot = $this->DAO->hot_game();
        if($hot) {
            $result = array("result" => "1", "desc" => "热门游戏", "data" => $hot);
        }else{
            $result['desc'] = "未查询到热门游戏";
        }
        die("0".base64_encode(json_encode($result)));
    }

    public function hot_more(){
        $result=array("result"=>"0","desc"=>"网络请求出错");
        $more = $this->DAO->hot_more();
        if($more){
            $result = array("result" => "1","desc" => "更多热门游戏","data"=>$more);
        }else{
            $result["desc"] = "未查询到更多热门游戏";
        }
        die("0".base64_encode(json_encode($result)));
    }

    public function hot_search(){
        $data = $this->DAO->get_hot_search();
        if(!empty($data)) {
            $result = array("result" => "1", "desc" => "热门查询接口", "data" => $data);
        }else{
            $result['desc'] = '未参数到对应数据';
        }
        die("0".base64_encode(json_encode($result)));
    }

    public function search(){
        $result = array("result" => "0", "desc" => "网络请求出错");
        $params = $_POST;
        $search_list = $this->DAO->search($params['tag'], $params['page']);
        if(!empty($search_list)) {
            $result = array("result" => "1", "desc" => "数据查询成功", "data" => $search_list);
        }else{
            $result['desc'] = '未能查询到对应信息.';
            if($params['page']>'1'){
                $result['desc'] = '没有更多数据.';
            }
        }
        die("0".base64_encode(json_encode($result)));
    }

    public function go_search($game_name){
        $result = array("result" => "0", "desc" => "网络请求出错");
        if(empty($game_name)){
            $result['desc']='缺少必要参数';
            die("0".base64_encode(json_encode($result)));
        }
        $search_list = $this->DAO->get_search($game_name);
        if(!empty($search_list)) {
            $result = array("result" => "1", "desc" => "数据查询成功", "data" => $search_list);
        }else{
            $result['desc'] = '未能查询到对应信息.';
        }
        die("0".base64_encode(json_encode($result)));
    }

    public function game_detail($game_id){
        $result = array("result" => "0", "desc" => "网络请求出错");
        $game_detail = $this->DAO->get_game_detail($game_id);
        if(!empty($game_detail)) {
            $result = array("result" => "1", "desc" => "数据查询成功", "data" => $game_detail);
        }else{
            $result['desc'] = '未查询到游戏详情';
        }
        die("0".base64_encode(json_encode($result)));
    }

    public function detail_similar($game_id){
        $result = array("result" => "0", "desc" => "网络请求出错");
        $game_rmd = $this->DAO->get_detail_similar($game_id);
        if(!empty($game_rmd)) {
            $result = array("result" => "1", "desc" => "数据查询成功", "data" => $game_rmd);
        }else{
            $result['desc'] = '未查询到相关信息';
        }
        die("0".base64_encode(json_encode($result)));
    }

    public function detail_top($game_id){
        $result = array("result" => "0", "desc" => "网络请求出错");
        $game_rmd = $this->DAO->get_detail_top($game_id);
        if(!empty($game_rmd)) {
            $result = array("result" => "1", "desc" => "数据查询成功", "data" => $game_rmd);
        }else{
            $result['desc'] = '未查询到相关信息';
        }
        die("0".base64_encode(json_encode($result)));
    }

    public function new_game(){
        $result = array("result" => "0", "desc" => "网络请求出错");
        $new = $this->DAO->new_game();
        if($new){
            $result = array("result" => "1","desc" => "新游推荐","data" =>$new);
        }else{
            $result['desc'] = "未查询到新游推荐";
        }
        die("0".base64_encode(json_encode($result)));
    }

    public function new_more(){
        $result = array("result" => "0", "desc" => "网络请求出错");
        $new = $this->DAO->new_more();
        if($new){
            $result = array("result" => "1","desc" => "更多新游推荐","data" =>$new);
        }else{
            $result['desc'] = "未查询到更多新游推荐";
        }
        die("0".base64_encode(json_encode($result)));
    }

    public function rate_game(){
        $result = array("result" => "0", "desc" => "网络请求出错");
        $rate = $this->DAO->rate_game();
        if($rate){
            $result = array("result" => "1","desc" => "超低折扣游戏","data" =>$rate);
        }else{
            $result['desc'] = "未查询到超低折扣游戏";
        }
        die("0".base64_encode(json_encode($result)));
    }

    public function rate_more(){
        $result = array("result" => "0", "desc" => "网络请求出错");
        $rate = $this->DAO->rate_more();
        if($rate){
            $result = array("result" => "1","desc" => "更多超低折扣游戏","data" =>$rate);
        }else{
            $result['desc'] = "未查询到更多超低折扣游戏";
        }
        die("0".base64_encode(json_encode($result)));
    }

    public function top_game(){
        $result = array("result" => "0", "desc" => "网络请求出错");
        $top = $this->DAO->top_game();
        if($top){
            $result = array("result" => "1","desc" => "66推荐游戏","data" =>$top);
        }else{
            $result['desc'] = "未查询到更多66推荐游戏";
        }
       die("0".base64_encode(json_encode($result)));
    }

    public function top_more(){
        $result = array("result" => "0", "desc" => "网络请求出错");
        $top = $this->DAO->top_more();
        if($top){
            $result = array("result" => "1","desc" => "更多66推荐游戏","data" =>$top);
        }else{
            $result['desc'] = "未查询到更多66推荐游戏";
        }
        die("0".base64_encode(json_encode($result)));
    }

    public function rank_list(){
        $result = array("result" => "0","desc" => "网络请求出错");
        $rank = $this->DAO->rank_list();
        if($rank){
            $result = array("result" => "1","desc" => "排行榜","data" =>$rank);
        }else{
            $result['desc'] = "未查询到排行榜数据";
        }
        die("0".base64_encode(json_encode($result)));
    }

    public function get_sign(){
        $result = array("result" => "0","desc" => "网络请求出错");
        if(empty($this->user_id)){
            $result['desc'] = "发生错误，用户未登录";//缺少用户id
            die("0".base64_encode(json_encode($result)));
        }
        $this_day = date('d',time());
        $yesterday = date('d',time()-86400);
        $sign = $this->DAO->get_sign_data($this->user_id,$this_day);
        if($sign){
            $result['desc'] = "今日你已签到过.";
        }else{
            $yesterday_log = $this->DAO->get_sign_data($this->user_id,$yesterday);
            if($yesterday_log){
                $sign_day = $yesterday_log['num'] + 1;
                $this->DAO->set_sign_data($this->user_id,$this_day,$sign_day);
            }else{
                $sign_day = 1;
                $this->DAO->set_sign_data($this->user_id,$this_day,$sign_day);
            }
            $result = array("result" => "1","desc" => "签到成功","day"=>$sign_day);
        }
        die("0".base64_encode(json_encode($result)));
    }

    public function help_center(){
        $data=array(
            array("question"=>"如何注册66173平台帐号？","answer"=>"进入个人中心，点击登录按钮，点击注册，输入手机号码获取验证码，验证通过后设置密码，点击同意用户服务协议，立即注册即可成功注册。"),
            array("question"=>"手机收不到验证码短信怎么办？","answer"=>"首先请您检查一下手机号是否输入错误。另外由于各地手机网络原因不一致，可能短信到达会有十几秒到几分钟的延时，请耐心等候。或者查看手机的安全中心，检查是否屏蔽掉验证短信。
若排除以上情况，请在第一次发生一分钟后点击再次获取，再耐心等待短信的到达。
若出现较长时间多次获取不到验证短信，请联系我们的客服，技术人员会排查问题。"),
            array("question"=>"什么是首充号？","answer"=>"首充号即66173根据用户选择的游戏渠道，新注册账号及密码，且完成首次充值的账号；账号内包括服务器，职业，性别，角色名等基础信息由玩家自行选择，只需官网原价的2~5折即可购买，包含您购买的充值数及同等充值奖励。"),
            array("question"=>"支付失败怎么办？","answer"=>"在支付界面有支付宝支付和网银支付，如遇到支付出现问题时检查支付宝余额是否充足，银行卡是否开通网银，以上情况都会导致支付失败的情况，如若不是，请联系客服。"),
            array("question"=>"购买资料填写有误怎么办？","answer"=>"在付款前购买资料错误时，返回重新填写资料。若已经付款完成发现资料填写错误的话，联系客服修改订单。"),
            array("question"=>"忘记密码如何找回？","answer"=>"进入个人中心，点击登录按钮，点击忘记密码，通过绑定的手机号，填写手机号以及收到的验证码找回密码；"),
            array("question"=>"买的东西不想要了怎么取消？","answer"=>"如果你需要取消订单，请及时联系相应的发货的客服，在交易之前取消，如果物品和资料已经发出，交易已经完成，是不能取消的，请理解！"),
            array("question"=>"退款后一般多久能到账？","answer"=>"若订单交易失败后，您支付的资金将给您原路返回，使用支付宝或者银行卡支付，退款将在1-7个工作日到账，请注意查收。"),
            array("question"=>"礼包如何领取？如何使用？","answer"=>"在游戏中心首页，点击礼包中心，进去礼包中心详情页，选择你需要礼包的游戏，点击领取，领取礼包在您已经登录情况下，礼包领取后可以直接复制礼包码进入游戏相应的地方兑换即可。所领取礼包在个人中心我的礼包呈现。"),
            array("question"=>"如何修改密码？","answer"=>"进入个人中心，在登陆账号后，点击账号安全修改密码，输入原始密码，输入新密码，点击确认按钮就修改好了。"),
            array("question"=>"如何联系客服？","answer"=>"1.您可以在APP个人中心客服模块联系我们的客服。
2.请拨打0591-87766173联系客服，将有专业人员解决您的问题。"),
            array("question"=>"如何辨别真假客服？","answer"=>"当您收到短信，QQ等站外客服信息时，可以到APP或者网站的客服功能资讯一下该客服人员的联系方式是否存在，涉及到您的资金账号等安全信息时尤为注意。
电话形式的客服主要协助您解决一些问题，或者特殊情况的通知和协调。客服热线均无外拨功能，请注意提防骗子冒充。"),
            array("question"=>"怎么快捷续充？","answer"=>"打开APP，底部选择栏点击代充，进入充值页面，点击快捷续充，输入在66173应用市场购买的首充号验证，验证通过后直接续充。
"),
            array("question"=>"怎么下载游戏？","answer"=>"进入游戏中心，可以搜索栏搜索您想下载游戏，进入游戏详情页，点击底部下载按钮进行下载。也可以在游戏中心浏览游戏，点击游戏下方的下载按钮直接下载。下载完成后会提醒你删除安装包并安装游戏。"),
        );
        $this->assign("data",$data);
        $this->display("help_center.html");
    }

    public function user_agreement(){
        $this->display("user_agreement.html");
    }

    public function integral(){
        $result = array("result" => "0" , "desc" => "网络请求出错");
        if(empty($this->user_id)){
            $result['desc'] = "发生错误，用户未登录";//缺少用户id
            die("0".base64_encode(json_encode($result)));
        }
        $integral = $this->DAO->get_integral();
        if(!$integral){
            $result["desc"] = "您还没有签到";
        }else{
            $result = array("result" => "1","desc" => "您已签到".$integral."天" ,"day" => $integral);
        }
        die("0".base64_encode(json_encode($result)));
    }

    public function game_list(){
        $result = array("result" => "0" , "desc" => "网络请求出错");
        $game_list = $this->DAO->game_list();
        $group_game_list = array();
        foreach($game_list as $v){
            $group_game_list[$v['first_letter']]['first_letter'] = $v['first_letter'];
            $group_game_list[$v['first_letter']]['list'][]=$v;
        }
        ksort($group_game_list);
        if(!$game_list){
            $result['desc'] = "未请求到数据";
        }else{
            $result = array("result" => "1" ,"desc" => "","data" => $group_game_list);
        }
        die("0".base64_encode(json_encode($result)));
    }

}