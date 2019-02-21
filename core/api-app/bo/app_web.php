<?php
COMMON('baseCore','pageCore');
DAO('app_dao');
BEAN('product_bean');
class app_web extends baseCore{

    public $DAO;

    public function __construct(){
        parent::__construct();
        $this->DAO = new app_dao();
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

    public function game_ch_view($game_id){
        //游戏明细
        $gameinfo = $this->DAO->get_game($game_id);
        if (!$gameinfo) {
            die("该游戏未上架.");
        }
        //渠道列表
        $channels = $this->DAO->get_channels_list();
        $gamechannels = $this->DAO->get_game_channels($game_id);
        unset($gamechannels['game_id']);
        $new_game_ch = array();
        //最低折扣
        $min_dis = 10;
        foreach($gamechannels as $k=>$v){
            if(!empty($v)){
                $ch_id = explode("_",$k);
                if($ch_id[2]=='1'){
                    $ch_info = $this->DAO->get_channel_info($ch_id[1]);
                    $ch_array = array("name"=>$ch_info['channel_name'], "discount"=>$v, "ch_id"=>$ch_id[1], "d_type"=>$k,"icon"=>$ch_info['icon']);
                    if($v < $min_dis){
                        $min_dis = $v;
                    }
                    array_push($new_game_ch,$ch_array);
                }
            }
        }
        $this->assign("game_id", $game_id);
        $this->assign("info", $gameinfo);
        $this->assign("min_dis", $min_dis);
        $this->assign("channels", $channels);
        $this->assign("ch_list", $new_game_ch);
        $this->display("sel_ch_view.html");
    }

    public function sel_goods_view($ch_id,$game_id){
        $app_gamne=$this->DAO->app_game_info($game_id);
        $products_list=$this->DAO->game_products_list($game_id,1);
        $game_introduce=$this->DAO->get_game_introduce_byid($game_id);
        $gameinfo = $this->DAO->get_game($game_id);
        $channel_discount = $this->DAO->get_channel_discount($game_id);
        $ch = explode("_",$ch_id);
        $ch_info = $this->DAO->get_channel_info($ch[1]);
        if(!empty($channel_discount[$ch_id])){
            $discount = $channel_discount[$ch_id];
        }else{
            die("渠道信息有误!");
        }
        $pay_money = round($discount * $products_list[0]['price']/10);
        $this->assign("discount", $discount);
        $this->assign("info", $gameinfo);
        $this->assign("pay_money", $pay_money);
        $this->assign("ch_id", $ch_id);
        $this->assign("ch_info", $ch_info);
        $this->assign("app_gamne", $app_gamne);
        $this->assign("goods", $products_list);
        $this->assign("game_introduce", $game_introduce);
        $this->display("sel_goods_view.html");
    }

    public function pay_view(){
        $param = $_POST;
        if(empty($param)){
            die("缺少必要参数!!!");
        }
        if(!$param['game_id'] || !$param['ch_id'] || !$param['ch_key'] ||!$param['good_id']){
            die("参数丢失,检查网络是否正常.");
        }
        $game = $this->DAO->get_game($param['game_id']);
        $products_info=$this->DAO->get_products_info($param['game_id'],$param['good_id'],1);
//        $game_introduce=$this->DAO->get_game_introduce_byid($param['game_id']);
        $ch_info = $this->DAO->get_channel_info($param['ch_id']);
        $channel_discount = $this->DAO->get_channel_discount($param['game_id']);
        if(!empty($channel_discount[$param['ch_key']])){
            $discount = $channel_discount[$param['ch_key']];
        }else{
            die("渠道信息有误!");
        }
        $pay_money = round($discount * $products_info['price']/10);
        $this->set_product_tag($game['tags']);
        $this->page_hash();
        $this->assign("game_id", $param['game_id']);
        $this->assign("pay_money", $pay_money);
        $this->assign("discount", $discount);
        $this->assign("ch_info", $ch_info);
        $this->assign("products_info", $products_info);
        $this->display("pay_view.html");
    }

    public function recharge_view($game_user){
        $user = $this->DAO->check_game_user($game_user);
        if(!$user){
            die("未查询到该账号记录");
        }

        $products_list=$this->DAO->game_products_list($user['game_id'],2);
        $game_introduce=$this->DAO->get_game_introduce_byid($user['game_id']);
        $game = $this->DAO->get_game($user['game_id']);
        $channel_discount = $this->DAO->get_channel_discount($user['game_id']);
        $discount=10;
        $ch_info = $this->DAO->get_channel_info($user['ch_id']);
        if(!empty($channel_discount['ch_'.$user['ch_id'].'_2'])){
            $discount = $channel_discount['ch_'.$user['ch_id'].'_2'];
        }
        $pay_money = round($discount * $products_list[0]['price']/10);
        $this->page_hash();
        $this->assign("goods", $products_list);
        $this->assign("user", $user);
        $this->assign("pay_money", $pay_money);
        $this->assign("game_introduce", $game_introduce);
        $this->assign("discount", $discount);
        $this->assign("game", $game);
        $this->assign("ch_info", $ch_info);
        $this->display("recharge_view.html");
    }

    public function search_user($game_user){
        $url_head='http://api-app.66173.cn/';
        $result = array('error'=>'1','msg'=>'网络异常','game_user'=>'','url'=>'');
        $user = $this->DAO->check_game_user($game_user);
        if(!$user || !$user['game_user']){
            $result['msg']='未查询到角色信息';
            die('0'.base64_encode(json_encode($result)));
        }else{
            $result = array('error'=>'0','msg'=>'角色查询成功','game_user'=>$game_user,'url'=>$url_head.'app.php?act=recharge&game_user='.$game_user);
        }
        die('0'.base64_encode(json_encode($result)));
    }

    public function go_pay(){
        $result = array('error' => '1', 'msg' => '网络请求错误.');
        if(empty($this->user_id)){
            $result['msg'] = "发生错误，错误代码E100";//缺少用户id
            die("0".base64_encode(json_encode($result)));
        }
        $params = $this->url_validator($_POST);
        $request = $this->productValidator($params);
        if(!empty($request)){
            $result['msg'] = $request;
            die("0".base64_encode(json_encode($result)));
        }
        $order_id = $this->orderid($params['game_id']);
        $product = $this->DAO->get_product_info($params['product_id']);
        $discount = 10;
        if($product['ch_'.$params['channel_id']]!=0){
            $discount = $product['ch_'.$params['channel_id']];
        }elseif($product['chd_'.$params['channel_id']]!=0){
            $discount = $product['chd_'.$params['channel_id']];
        }
        $order_info = $this->insert_order($order_id,$product,$params,$discount);
        //返回客户端进行支付
//        $this->open_debug();
        if($params['pay_mode']=='zfb'){
            $request = $this->create_zfb_pay($order_info,$product,ALI_APP_notify_url);
            $result = array('error' => '0', 'data' => $request,'pay_mode' => 'zfb','msg' => '请求成功');

        }elseif($params['pay_mode']=='wx'){
            $request = $this->create_wx_pay($order_info,$product,WX_APP_notify_url);
            $result = array('error' => '0', 'data' => $request,'pay_mode' => 'wx','msg' => '请求成功');
        }
        die("0".base64_encode(json_encode($result)));
    }

    public function go_recharge(){
        $result = array('error' => '1', 'msg' => '网络请求错误.');
        if(empty($this->user_id)){
            $result['msg'] = "发生错误，错误代码E100";//缺少用户id
            die("0".base64_encode(json_encode($result)));
        }

        $params = $_POST;
        if(!$params['game_user']){
            $result['msg'] = "发生错误，错误代码E101";//缺少查询角色信息
            die("0".base64_encode(json_encode($result)));
        }
        $user = $this->DAO->check_game_user($params['game_user']);
        if(!$user){
            $result['msg'] = "发生错误，错误代码E102";//未查询到角色信息
            die("0".base64_encode(json_encode($result)));
        }
        $request = $this->productValidator($params);
        if(!empty($request)){
            $result['msg'] = $request;
            die("0".base64_encode(json_encode($result)));
        }
        $order_id = $this->orderid($params['game_id']);
        $product = $this->DAO->get_product_info($params['product_id']);
        $discount = 10;
        if($product['ch_'.$params['channel_id']]!=0){
            $discount = $product['ch_'.$params['channel_id']];
        }elseif($product['chd_'.$params['channel_id']]!=0){
            $discount = $product['chd_'.$params['channel_id']];
        }
        $order_info = $this->insert_order($order_id,$product,$params,$discount);
        //返回客户端进行支付
        if($params['pay_mode']=='zfb'){
            $request = $this->create_zfb_pay($order_info,$product,ALI_APP_notify_url);
            $result = array('error' => '0', 'data' => $request,'pay_mode' => 'zfb','msg' => '请求成功');

        }elseif($params['pay_mode']=='wx'){
            $request = $this->create_wx_pay($order_info,$product,WX_APP_notify_url);
            $result = array('error' => '0', 'data' => $request,'pay_mode' => 'wx','msg' => '请求成功');
        }
        die("0".base64_encode(json_encode($result)));
    }

    private function create_zfb_pay($order,$product,$notify_url){
        if(!$order || !$product ||!$notify_url) {
            return '';
        }
        $order->pay_money=0.01;
        $product['title']=$product['title']."[app充值测试]";
        $sign = md5($order->game_id.$order->order_id.$order->pay_money.$order->buyer_id.'66apk');
        $result = array("errcode"=>0,
            "orderid"=>$order->order_id,
            "goodsname"=>$product['title'],
            "goodsdesc"=>$product['title'],
            "goodsfee"=>$order->pay_money,
            "notifyurl"=>$notify_url,
            "sign"=>$sign);
//        $this->err_log(var_export($result,1),'66app_order');
        return $result;
    }

    public function create_guids() {
        $charid = strtolower(md5(uniqid(mt_rand(), true)));
        $uuid = substr($charid, 0, 32);
        return $uuid;
    }

    public function make_wx_data($money,$product,$out_trade_no,$notify_url){
        $product['title']=$product['title']."appwx测试";
        $attach='`store_appid='.XY_STORE_APPID.'#store_name='.XY_STORE_NAME.'#op_user=';
        $data = array(
            'version' => '1.0.4',//兴业APPID
            'appid' => XY_APPID,//兴业APPID
            'mch_id' => XY_MCD_ID,//兴业MCH_ID
            'wx_appid' => WX_APPID,
            'nonce_str' => $this->create_guids(),
            'body' => $product['title'],
            'attach' => $attach,
            'out_trade_no' => $out_trade_no,
            'total_fee' => $money*100,
            'spbill_create_ip' => $this->client_ip(),
            'notify_url' => $notify_url,
            'trade_type' => 'APP'
        );
        ksort($data);
        $str = '';
        $new_data=array();
        foreach($data as $key => $val ){
            if(!empty($val)){
                $new_data[$key]=$val;
                $str.=$key."=".$val."&";
            }
        }
        $str = $str."key=".XY_APPKEY;
        $new_data['sign']=strtoupper(md5($str));
        return $new_data;
    }
    public function array_to_xml($arr=array()){
        $xml = '<xml>';
        foreach ($arr as $key => $val){
            if(is_array($val)){
                $xml .= "<".$key.">".$this->array_to_xml($val)."</".$key.">";
            }else{
                $xml .= "<".$key.">".$val."</".$key.">";
            }
        }
        $xml .= "</xml>";
        return $xml;
    }
    private function create_wx_pay($order,$product,$notify_url){
        $order->pay_money=0.01;
        $wx_data = $this->make_wx_data($order->pay_money,$product,$order->order_id,$notify_url);
        $xml_data = $this->array_to_xml($wx_data);
        $request = $this->request(XY_API_url,$xml_data,array('Content-type: text/xml'));
        $request_data = json_decode(json_encode(simplexml_load_string($request, 'SimpleXMLElement', LIBXML_NOCDATA)), true);
        $this->err_log(var_export($request_data,1),'66app_wx_order_test');
        if($request_data['return_code']=='FAIL'){
            $result = array("errcode"=>1,"message"=>$request_data['return_msg']);
            die("0".base64_encode(json_encode($result)));
        }elseif($request_data['return_code']=='SUCCESS' && $request_data['result_code']=='SUCCESS' && $request_data['trade_type']=='APP'){
            $req_appid = $request_data['req_appid'];
            $req_partnerid = $request_data['req_partnerid'];
            $req_prepayid = $request_data['req_prepayid'];
            $req_package = $request_data['req_package'];
            $req_noncestr = $request_data['req_noncestr'];
            $req_timestamp = $request_data['req_timestamp'];
            $req_sign = $request_data['req_sign'];

        }elseif($request_data['return_code']=='SUCCESS' && $request_data['result_code']=='FAIL'){
            $result = array("errcode"=>1,"message"=>$request_data['err_code_des'].$request_data['err_code']);
            die("0".base64_encode(json_encode($result)));
        }else{
            $result = array("errcode"=>1,"message"=>'请求订单异常.'.$request_data);
            die("0".base64_encode(json_encode($result)));
        }
        $result = array(
            "errcode"=>0,
            "req_appid"=>$req_appid,
            "req_partnerid"=>$req_partnerid,
            "req_prepayid"=>$req_prepayid,
            "req_package"=>$req_package,
            "req_noncestr"=>$req_noncestr,
            "req_timestamp"=>$req_timestamp,
            "req_sign"=>$req_sign,
            "message"=>"预支付订单发送成功");
        $this->err_log(var_export($request_data,1),'66app_wx_order');
        return $result;
    }

    private function insert_order($order_id,$product,$params,$discount){
        //客服环节加入
        $service = $this->DAO->get_service();
        if(empty($service)){
            $service['id']='114';
        }
        $bean = new productBean();
        $bean->order_id = $order_id;
        $bean->title = $product['title'];
        $bean->buyer_id = $this->user_id;
        $bean->product_id = $params['product_id'];
        $bean->amount = 1;
        $bean->money = $product['price'];
        $bean->unit_price = $product['price'];
        $bean->pay_money = round(($product['price']*$discount)/10);
        $bean->game_id = $product['game_id'];
        $bean->serv_id = $params['serv_id'];
        $bean->game_channel = $params['channel_id'];
        $bean->seller_id = $product['user_id'];
        $bean->status = 0;
        $bean->buy_time = strtotime("now");
        if ($params['pay_mode'] == 'wx') {
            $bean->pay_channel = 2;
        } else{
            $bean->pay_channel = 1;
        }

        $bean->qq = $params['qq'];
        $bean->tel = $params['tel'];
        $bean->discount = $discount;
        $bean->discount_in = 0;
        $bean->role_name = $params['role_name'];
        $bean->role_back_name = $params['role_back_name']?$params['role_back_name']:"";
        $bean->service_id = $service['id'];
        $bean->game_user = "";
        $bean->game_pwd = "";
        $bean->platform = 8;//66市场app
        $bean->is_rand_user = $params['is_rand_user'];

        $bean->attr = json_encode($params['attr'],JSON_UNESCAPED_UNICODE);
        $bean->is_agent = 0;
        $bean->role_level = "";
        $bean->reduce_product = 0;
        $bean->coupon_id = 0;
        unset($bean->id);
        $this->err_log(var_export($bean,1),'66app');
        $this->err_log(var_export((array)$bean,1),'66app');
        $this->DAO->insert_order((array)$bean);
        return $bean;
    }

    public function url_validator($param){
        if(empty($param)){
            return "";
        }
        $new_param=array();
        foreach($param as $key=>$data){
            $new_param[urldecode($key)]=urldecode($data);

        }
        $new_param['attr']=array();
        if($new_param['attr[0]']){
            array_push($new_param['attr'],$new_param['attr[0]']);
        }
        if($new_param['attr[1]']){
            array_push($new_param['attr'],$new_param['attr[1]']);
        }
        if($new_param['attr[2]']){
            array_push($new_param['attr'],$new_param['attr[2]']);
        }
        return $new_param;
    }

    public function productValidator($param){
        $this->base_check($param);
        if($param['is_rand_user']==0){
            if(!$param['role_back_name'] ||
                strlen($param['role_back_name']) ==0  ||
                strlen($param['role_back_name'])>50){
                $data = "发生错误，错误代码E002";//备选角色名异常
                return $data;
            }
        }
    }

    public function base_check($param){
        if(!$param['pagehash'] || $param['pagehash']!=$_SESSION['page-hash']){
            $data = "发生错误，错误代码E001";//pagehash异常
            return $data;
        }
        if (!$param['channel_id'] || !is_numeric($param['channel_id'])) {
            $data = "发生错误，错误代码E006";//缺少渠道id
            return $data;
        }
        if (!$param['serv_id'] || !is_numeric($param['serv_id'])) {
            $data = "发生错误，错误代码E007";//缺少服务器id
            return $data;
        }
        if (!$param['role_name'] || strlen($param['role_name']) == 0 || strlen($param['role_name']) > 50) {
            $data = "发生错误，错误代码E008";//角色名异常
            return $data;
        }
        if (!$param['tel'] || strlen($param['tel']) == 0 || strlen($param['tel']) > 15 || !is_numeric($param['tel'])) {
            $data = "发生错误，错误代码E009";//手机号格式错误
            return $data;
        }
        if (!$param['qq'] || strlen($param['qq']) == 0 || strlen($param['qq']) > 13 || !is_numeric($param['qq'])) {
            $data = "发生错误，错误代码E010";//qq账号异常
            return $data;
        }
        if (!$param['pay_mode'] || !($param['pay_mode']=='zfb' || $param['pay_mode']=='wx')) {
            $data = "发生错误，错误代码E099";//支付方式异常
            return $data;
        }
    }

    public function search_ser($game_id,$ch_id){
        $servs = $this->DAO->get_ser_list($game_id,$ch_id);
        $servs = array_chunk($servs, 50);
        $this->assign("servs", $servs);
        $this->display("server_view.html");
    }

    protected function set_product_tag($tags){
        $atts = array();
        if(!$tags){
            $this->assign("tags",array());
            return false;
        }
        $tags = explode("\n", $tags);
        if(!is_array($tags))return false;

        foreach($tags as $k=>$v){
            if($v){
                $tag = explode("：",$v);
                $atts[$tag[0]] = explode("|",$tag[1]);
            }
        }
        $this->assign("tags", $atts);
    }

}
?>