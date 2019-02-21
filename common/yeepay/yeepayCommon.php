<?php
class yeepay{
    public $p0_Cmd = "Buy";

    #	送货地址
    # 为"1": 需要用户将送货地址留在易宝支付系统;为"0": 不需要，默认为 "0".
    public $p9_SAF = "0";

    public $reqURL_onLine = "https://www.yeepay.com/app-merchant-proxy/node";
    #签名函数生成签名串
    function getReqHmacString($p2_Order,$p3_Amt,$p4_Cur,$p5_Pid,$p6_Pcat,$p7_Pdesc,$p8_Url,$pa_MP,$pd_FrpId,$pr_NeedResponse){

        #进行签名处理，一定按照文档中标明的签名顺序进行
        $sbOld = "";
        #加入业务类型
        $sbOld = $sbOld.$this->p0_Cmd;
        #加入商户编号
        $sbOld = $sbOld.YEEPAY_M_ID;
        #加入商户订单号
        $sbOld = $sbOld.$p2_Order;
        #加入支付金额
        $sbOld = $sbOld.$p3_Amt;
        #加入交易币种
        $sbOld = $sbOld.$p4_Cur;
        #加入商品名称
        $sbOld = $sbOld.$p5_Pid;
        #加入商品分类
        $sbOld = $sbOld.$p6_Pcat;
        #加入商品描述
        $sbOld = $sbOld.$p7_Pdesc;
        #加入商户接收支付成功数据的地址
        $sbOld = $sbOld.$p8_Url;
        #加入送货地址标识
        $sbOld = $sbOld.$this->p9_SAF;
        #加入商户扩展信息
        $sbOld = $sbOld.$pa_MP;
        #加入支付通道编码
        $sbOld = $sbOld.$pd_FrpId;
        #加入是否需要应答机制
        $sbOld = $sbOld.$pr_NeedResponse;
        //$this->logstr($p2_Order."|".YEEPAY_M_KEY,$sbOld, $this->HmacMd5($sbOld,YEEPAY_M_KEY));
        return $this->HmacMd5($sbOld,YEEPAY_M_KEY);

    }

    function getCallbackHmacString($r0_Cmd,$r1_Code,$r2_TrxId,$r3_Amt,$r4_Cur,$r5_Pid,$r6_Order,$r7_Uid,$r8_MP,$r9_BType){

        #取得加密前的字符串
        $sbOld = "";
        #加入商家ID
        $sbOld = $sbOld.YEEPAY_M_ID;
        #加入消息类型
        $sbOld = $sbOld.$r0_Cmd;
        #加入业务返回码
        $sbOld = $sbOld.$r1_Code;
        #加入交易ID
        $sbOld = $sbOld.$r2_TrxId;
        #加入交易金额
        $sbOld = $sbOld.$r3_Amt;
        #加入货币单位
        $sbOld = $sbOld.$r4_Cur;
        #加入产品Id
        $sbOld = $sbOld.iconv("GBK//TRANSLIT","UTF-8",$r5_Pid);
        #加入订单ID
        $sbOld = $sbOld.$r6_Order;
        #加入用户ID
        $sbOld = $sbOld.$r7_Uid;
        #加入商家扩展信息
        $sbOld = $sbOld.$r8_MP;
        #加入交易结果返回类型
        $sbOld = $sbOld.$r9_BType;

        //$this->logstr($r6_Order."|".YEEPAY_M_KEY,$sbOld, $this->HmacMd5($sbOld,YEEPAY_M_KEY));
        return $this->HmacMd5($sbOld,YEEPAY_M_KEY);

    }


#	取得返回串中的所有参数
    function getCallBackValue(&$r0_Cmd,&$r1_Code,&$r2_TrxId,&$r3_Amt,&$r4_Cur,&$r5_Pid,&$r6_Order,&$r7_Uid,&$r8_MP,&$r9_BType,&$hmac){
        $r0_Cmd		= $_REQUEST['r0_Cmd'];
        $r1_Code	= $_REQUEST['r1_Code'];
        $r2_TrxId	= $_REQUEST['r2_TrxId'];
        $r3_Amt		= $_REQUEST['r3_Amt'];
        $r4_Cur		= $_REQUEST['r4_Cur'];
        $r5_Pid		= $_REQUEST['r5_Pid'];
        $r6_Order	= $_REQUEST['r6_Order'];
        $r7_Uid		= $_REQUEST['r7_Uid'];
        $r8_MP		= $_REQUEST['r8_MP'];
        $r9_BType	= $_REQUEST['r9_BType'];
        $hmac			= $_REQUEST['hmac'];

        return null;
    }

    function CheckHmac($r0_Cmd,$r1_Code,$r2_TrxId,$r3_Amt,$r4_Cur,$r5_Pid,$r6_Order,$r7_Uid,$r8_MP,$r9_BType,$hmac){
        if($hmac==$this->getCallbackHmacString($r0_Cmd,$r1_Code,$r2_TrxId,$r3_Amt,$r4_Cur,$r5_Pid,$r6_Order,$r7_Uid,$r8_MP,$r9_BType))
            return true;
        else
            return false;
    }

    function HmacMd5($data,$key){
// RFC 2104 HMAC implementation for php.
// Creates an md5 HMAC.
// Eliminates the need to install mhash to compute a HMAC
// Hacked by Lance Rushing(NOTE: Hacked means written)

//需要配置环境支持iconv，否则中文参数不能正常处理
//        $key = iconv("GBK//IGNORE","UTF-8",$key);
//        $data = iconv("GBK//IGNORE","UTF-8",$data);

        $b = 64; // byte length for md5
        if (strlen($key) > $b) {
            $key = pack("H*",md5($key));
        }
        $key = str_pad($key, $b, chr(0x00));
        $ipad = str_pad('', $b, chr(0x36));
        $opad = str_pad('', $b, chr(0x5c));
        $k_ipad = $key ^ $ipad ;
        $k_opad = $key ^ $opad;

        return md5($k_opad . pack("H*",md5($k_ipad . $data)));
    }

    function logstr($orderid,$str,$hmac){
        $path=PREFIX."/logs/yeepay".date("Y-m-d").".log";
        error_log(date('Y-m-d H:i:s').': '."|orderid[".$orderid."]|str[".$str."]|hmac[".$hmac."]".PHP_EOL, 3, $path);
    }
    function redirect_yeepay($params){
        $html_text = $this->buildRequestForm($params, "post", iconv("UTF-8","GBK","&nbsp;确认支付&nbsp;"));
        echo $html_text;
    }

    function buildRequestForm($para_temp, $method, $button_name) {
        //global $reqURL_onLine;
        //待请求参数数组
        //$para = $this->buildRequestPara($para_temp);

        $sHtml = "<form id='yeepay' name='yeepay' action='".$this->reqURL_onLine."' method='".$method."'>";
        $sHtml.= "<input type='hidden' name='p0_Cmd' value='Buy' />";
        $sHtml.= "<input type='hidden' name='p1_MerId' value='".YEEPAY_M_ID."'/>";
        $sHtml.= "<input type='hidden' name='p9_SAF' value='".$this->p9_SAF."'/>";
        foreach($para_temp as $key=>$val){
            $sHtml.= "<input type='hidden' name='".$key."' value='".iconv("UTF-8","GBK",$val)."'/>";
        }
//    while (list ($key, $val) = each ($para)) {
//        $sHtml.= "<input type='hidden' name='".$key."' value='".$val."'/>";
//    }

        //submit按钮控件请不要含有name属性
        $sHtml = $sHtml."<input type='submit' value='".$button_name."'></form>";

        $sHtml = $sHtml."<script>document.forms['yeepay'].submit();</script>";

        return $sHtml;
    }
}