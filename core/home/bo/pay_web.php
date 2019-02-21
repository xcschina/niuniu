<?php
COMMON('baseCore', 'pageCore','alipay','alipay/alipay_submit.class');
COMMON('yeepay/yeepayCommon');
COMMON('wxPay');
DAO('product_dao');

class pay_web extends baseCore {

    public $DAO;

    public function __construct() {
        parent::__construct();
        $this->DAO = new product_dao();
        $this->type = array(
            1=>"首充号",
            2=>"首充号续充",
            3=>"代充",
            4=>"账号",
            5=>"游戏币",
            6=>"道具",
            7=>"礼包"
        );
        $this->user_id=$_SESSION['user_id'];
    }

    public function yeepay($bank, $order){
        header("Content-Type: text/html; charset=GBK");
        $yeepay = new yeepay();

        $p2_Order= $order->order_id;
        $p3_Amt	= $order->pay_money;
        $p4_Cur	= "CNY";
        $p5_Pid	= $_SESSION['order']['title'];
        $p6_Pcat= "手机游戏";
        $p7_Pdesc= 'dNF';
        $p8_Url	= "http://charge.66173.cn/yeepay.php";
        $pa_MP	= $order->order_id;
        #支付通道编码
        $pd_FrpId= $bank;
        $pr_NeedResponse= 1;

        $form['p2_Order'] = $order->order_id;
        $form['p3_Amt'] = $p3_Amt;
        $form['p4_Cur'] = $p4_Cur;
        $form['p5_Pid'] = $p5_Pid;
        $form['p6_Pcat'] = $p6_Pcat;
        $form['p7_Pdesc'] = $p7_Pdesc;
        $form['p8_Url'] = $p8_Url;
        $form['pa_MP'] = $pa_MP;
        $form['pd_FrpId'] = $pd_FrpId;
        $form['pr_NeedResponse'] = 1;

        #调用签名函数生成签名串
        $hmac = $yeepay->getReqHmacString($p2_Order,$p3_Amt,$p4_Cur,$p5_Pid,$p6_Pcat,$p7_Pdesc,$p8_Url,$pa_MP,$pd_FrpId,$pr_NeedResponse);
        $form['hmac'] = $hmac;
        $yeepay->redirect_yeepay($form);
    }

    public function ali_pay($product){
        $alipay_config['partner']		= ALI_partner;
        $alipay_config['key']			= ALI_key;
        $alipay_config['sign_type']    = ALI_sign_type;
        $alipay_config['input_charset']= ALI_input_charset;
        $alipay_config['cacert']    = ALI_cacert;
        $alipay_config['transport']    = ALI_transport;
        $alipay = new Alipay($alipay_config);
        $alipay->return_url     = "http://www.66173.cn/ali_return.php";
        $alipay->out_trade_no   = $product->order_id;
        $alipay->show_url       = "http://www.66173.cn/";
        $alipay->subject        = $product->title;
        $alipay->total_fee      = $product->pay_money;
        //测试账号备注
        // 71 yyq
        // 57 ong
        // 13474  13478 张绿
        if($this->user_id==57||$this->user_id==71||$this->user_id==13474||$this->user_id==13478){
            $alipay->total_fee=0.01;
        }
        $alipay->body           = $alipay->subject."充值";
        $alipay->exter_invoke_ip = $this->client_ip();
        $alipay->redirect_alipay();
    }

    public function wx_pay($product){
        $pay = new wxPay();
        $pay->set_order((array)$product);
        $res = $pay->pay();
        if($res['status']){
            $url = $res['msg'];
            $this->assign('wx_pay_url',$url);
            if($product->serv_id==1){
                $order_info = $this->DAO->get_order_infos($product->order_id);
                $order_info['serv_name'] =$_SESSION['order']['serv_name'];
            }else{
                $order_info = $this->DAO->get_order_info($product->order_id);
            }
            if($order_info['product_id'] != 33875){
                $order_relation = $this->DAO->get_order_relation_info($order_info['product_id']);
            }else{
                $order_relation = $this->DAO->get_weekactivity_tb($order_info['activity_id']);
                $game = $this->DAO->get_game_tb($order_relation['game_id']);
                $order_relation['title'] = $order_relation['game_name'];
                $order_relation['game_name'] = $game['game_name'];
            }
            $this->assign("info", $order_info);
            $this->assign("order_relation", $order_relation);
            unset($_SESSION['order']);
            $this->display('pay_wx_qr.html');
        }else{
            $msg = '<br><a href="http://www.66173.cn/games/'.$product->game_id.'.html">点此返回</a>';
            die($res['msg'].$msg);
        }
    }


}