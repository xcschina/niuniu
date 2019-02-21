<?php
COMMON("alipay.config");
COMMON("alipay/alipay_submit.class");
class Alipay{

    /**************************请求参数**************************/
    protected $alipay_config;

    //支付类型
    protected $payment_type = "1";
    //必填，不能修改
    //服务器异步通知页面路径
    public $notify_url = ALI_notify_url;
    //需http://格式的完整路径，不能加?id=123这类自定义参数

    //页面跳转同步通知页面路径
    public $return_url;
    //需http://格式的完整路径，不能加?id=123这类自定义参数，不能写成http://localhost/

    //卖家支付宝帐户
    protected $seller_email = ALIPAY_EMAIL;
    //必填

    //商户订单号
    public $out_trade_no;
    //商户网站订单系统中唯一订单号，必填

    //订单名称
    public $subject;
    //必填

    //付款金额
    public $total_fee;

    public $defaultbank;
    //必填

    //订单描述

    public $body;
    //商品展示地址
    public $show_url;
    //需以http://开头的完整路径，例如：http://www.xxx.com/myorder.html

    //防钓鱼时间戳
    public $anti_phishing_key;
    //若要使用请调用类文件submit中的query_timestamp函数

    //客户端的IP地址
    public $exter_invoke_ip;
    //非局域网的外网IP地址，如：221.0.0.1
    function __construct($alipay_config){
        $this->alipay_config = $alipay_config;
    }

    /************************************************************/
    public function redirect_alipay(){

        //建立请求
        $alipaySubmit = new AlipaySubmit($this->alipay_config);

        //构造要请求的参数数组，无需改动
        $parameter = array(
            "service" => "create_direct_pay_by_user",
            "partner" => trim($this->alipay_config['partner']),
            "payment_type"	=> $this->payment_type,
            "notify_url"	=> $this->notify_url,
            "return_url"	=> $this->return_url,
            "seller_email"	=> $this->seller_email,
            "out_trade_no"	=> $this->out_trade_no,
            "subject"	=> $this->subject,
            "total_fee"	=> $this->total_fee,
            "body"	=> $this->body,
            "show_url"	=> $this->show_url,
            "anti_phishing_key"	=> $alipaySubmit->query_timestamp(),
            "exter_invoke_ip"	=> $this->exter_invoke_ip,
            "_input_charset"	=> trim(strtolower($this->alipay_config['input_charset']))
        );
        if($this->defaultbank){
            $parameter['defaultbank'] = $this->defaultbank;
        }
        $html_text = $alipaySubmit->buildRequestForm($parameter,"get", "&nbsp;确认支付&nbsp;");
        echo $html_text;
    }

    /************************************************************/
    public function demo_redirect_alipay($notify_url){

        //建立请求
        $alipaySubmit = new AlipaySubmit($this->alipay_config);

        //构造要请求的参数数组，无需改动
        $parameter = array(
            "service" => "create_direct_pay_by_user",
            "partner" => trim($this->alipay_config['partner']),
            "payment_type"	=> $this->payment_type,
            "notify_url"	=> $notify_url,
            "return_url"	=> $this->return_url,
            "seller_email"	=> $this->seller_email,
            "out_trade_no"	=> $this->out_trade_no,
            "subject"	=> $this->subject,
            "total_fee"	=> $this->total_fee,
            "body"	=> $this->body,
            "show_url"	=> $this->show_url,
            "anti_phishing_key"	=> $alipaySubmit->query_timestamp(),
            "exter_invoke_ip"	=> $this->exter_invoke_ip,
            "_input_charset"	=> trim(strtolower($this->alipay_config['input_charset']))
        );
        $html_text = $alipaySubmit->buildRequestForm($parameter,"get", "&nbsp;确认支付&nbsp;");
        echo $html_text;
    }


}