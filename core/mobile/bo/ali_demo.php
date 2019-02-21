<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/5/17
 * Time: 14:23
 */
COMMON('baseCore','ali_demo/aop/AopClient','ali_demo/aop/request/AlipayTradeWapPayRequest');
class ali_demo extends baseCore{
    private $config;
    public function __construct(){
        parent::__construct();
        $this->config = array(
            "CorpId"=>'dingc426142ca9c5a652',
            "SSOsecret"=>'pRJz_Mk6IfjmUsAsU_RC-3Lx1TAm1zJX9SPyC2lbnIbQhkNAIS-WpbbQkf93PXca',
            "AppKey"=>'dingpsvbrqyklbpgiyec',
            "AppSecret"=>'8hKcYajPUphgg_a2-NfB2bx0032lx6MMxPxkYg99_Cmt8fAedHdHaR1Ec_hV-x38',
            "AgentId"=>'199610269',
        );
    }

    public function index(){
        $access_token = $this->get_access_token();
//        $this->get_scopes($access_token);
//        $this->open_debug();
        $data_list = $this->get_employee_queryonjob($access_token);
        $this->get_employee_list($access_token,implode(',',$data_list));
        var_dump($access_token);
//        $this->display('ali_demo.html');
    }

    //获取钉钉access_token
    public function get_access_token(){
        $url = "https://oapi.dingtalk.com/gettoken?appkey=".$this->config['AppKey']."&appsecret=".$this->config['AppSecret'];
        $data = $this->request($url);
        $result = json_decode(json_encode(json_decode(html_entity_decode($data))),TRUE);
        return $result['access_token'];
    }

    //获取通讯录权限范围
    public function get_scopes($access_token){
        $url = "https://oapi.dingtalk.com/auth/scopes?access_token=".$access_token;
        $data = $this->request($url);
        $result = json_decode(json_encode(json_decode(html_entity_decode($data))),TRUE);
        return $result;
    }

    //获取企业角色列表
    public function get_role_list($access_token){
        $url = "https://oapi.dingtalk.com/topapi/role/list?access_token=".$access_token;
        $data = $this->request($url);
        $result = json_decode(json_encode(json_decode(html_entity_decode($data))),TRUE);
        return $result;
    }

    //获取企业角色下的员工列表
    public function get_role_simplelist($access_token){
        $url = "https://oapi.dingtalk.com/topapi/role/simplelist?access_token=".$access_token;
        $arr = array('role_id'=>103879825);
        $data = $this->request($url,$arr);
        $result = json_decode(json_encode(json_decode(html_entity_decode($data))),TRUE);
        return $result;
    }

    //获取企业在职员工列表
    public function get_employee_queryonjob($access_token){
        $url = "https://oapi.dingtalk.com/topapi/smartwork/hrm/employee/queryonjob?access_token=".$access_token;
        $arr = array(
            'status_list'=>3,
            'offset'=>0,
            'size'=>50,
            );
        $data = $this->request($url,$arr);
        $result = json_decode(json_encode(json_decode(html_entity_decode($data))),TRUE);
        var_dump($result);
        return $result['result']['data_list'];
    }

    //获取企业员工花名册列表
    public function get_employee_list($access_token,$data_list){
        $url = "https://oapi.dingtalk.com/topapi/smartwork/hrm/employee/list?access_token=".$access_token;
        $arr = array(
            'userid_list'=>'01106203039358'
            );
        $data = $this->request($url,$arr);
        $result = json_decode(json_encode(json_decode(html_entity_decode($data))),TRUE);
        var_dump($result);
        return $result;
    }



    public function ali(){
        $aop = new AopClient();
        $aop->gatewayUrl = 'https://openapi.alipaydev.com/gateway.do';
        $aop->appId = '2016091500519413';
        $aop->rsaPrivateKey = 'MIIEpAIBAAKCAQEA1RS7iYUFNLMWsJu2MRbw74l4A73o4ReFzAwa2bCh5etPo3gOnXf0PKjYFrIAy+vGyPFxcvpjWsq8VC7Kww05i0f9gJx81/KsBlDikfm3WMUup7lMUDdjZUbaQw1GiVW3CQdMX1mUps/BGNvkHfQkzYFMaVBtxF3xXkNbdVmo1nItkWAa4kWXKvC2UnzA/0OuVuG7sjFzyAaJeICCahTAGyOiSSJ0gkM41NVPz7DPTSElquuJsfIbEj9oAqmWlkg4x84Dr9ubFJ2vuh0jgepg9Z+DwYZSDLLlhIeuLn6u0AWqpyEOpTv/gh6X5UYIbnh6cz3YLrTll7mjPziig1PlFwIDAQABAoIBAQCAAduECwiMl0X7RSAXr4uAq5oQzWb4ypPTeDYtLwKjyFBoiPhmuyhbxKdEQx1dZEBgeUPJagiEA6VOAWpH8UrvlIkDWX9aj+uuQ6tJPLZbuuGDWzswOYYZ0o3OmEwWtwqlft/vrDY0XDzJvVf2crQlTRblca7vt+wHz/lCr2FRqd9l0UR8qs7piEm+jT+CbPCkzVvu/awZwF3OkZA5A6QS5psNCl29TxEzIGVqO9zwTeGUYFEEf/l220wnA50cd2uQwUKIEhnh2KaZmFfvYjWjspOmdXRoDBNQh50OoAW0YdBfAhqjKGPDezcRziLPLBK6wYz844O37LkhiOJSbV/hAoGBAO+d6omLDlJmjF+pHSQxtMxleCAPK0mUnYk4fI6U2J6efsimijrEWu1TUFoQLEcQbmMve6DRSJgNlWh0Y9bSg633ymuP6pqajDrzPqTU5VIB5tBcsKHFJjnSDHylEGbReNp07i+TjVl4eyie72xvHa21H+RAkwSZ3Prc4NCbxOeHAoGBAOOmWey5RJMwRPn0H5WgaPyuk26gQAwZYpe2vnvbMRviivIL//a+qbUe5VotgFz1AXNeHoMRC6GFd/hMFmzGqGTP8ZMcAOFyzTz5Esak/rtIHoe7JsDEE9FLtrLBJDtCo2JZKDdrf2Jenc6AAw0uG0r/dng42lf8hq9GXSBvyFnxAoGARuEVkZ4anHFNMLbbsesqKhQR1pnmGhvmBcM5xQtukG0d38iztvKCWxV7/UgvcM4BeUGAdm+x45iRC5byOYeIABv+OpcVtKnmtUFi1GtNclaxqgzhCtlAl3X0z6IcGe34q9ZO5q/k7gRfmgJ5nD+6LboYNvKLCp9g6KwRrGOHeV0CgYEApn84LcaivPQEvrspjomvv37/HV3e+fpm/YcOcg+yLy7VaIoWPyTbgjiCjh3RUCtxUCt6LWn+E7hGMjeT/yKcbn8Xs/w7OKh6KyoP7XEnTVpF2gerJDlENUMm8D4Kfb0TOg7zNvVMSWniCPHfSXh7RbLptqv+JXPwHalc2yhmZEECgYAYylOK5EQEba820Faj2fUnXsFl3/oxSq9ANHxG1IW9/ctCfv6tpGlTl//8YjXo67Wp9dEIqP+yU+QGYWd4te3CzXrxMSK6LfSPeXX5vzZs6YPFL9eyq3cM0d2Q7t58P/6k+ALxr/E/+AxMUfMQyDK5eTamyVN/a4Jx/G3fbTZR6g==';
        $aop->alipayrsaPublicKey='MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEA1RS7iYUFNLMWsJu2MRbw74l4A73o4ReFzAwa2bCh5etPo3gOnXf0PKjYFrIAy+vGyPFxcvpjWsq8VC7Kww05i0f9gJx81/KsBlDikfm3WMUup7lMUDdjZUbaQw1GiVW3CQdMX1mUps/BGNvkHfQkzYFMaVBtxF3xXkNbdVmo1nItkWAa4kWXKvC2UnzA/0OuVuG7sjFzyAaJeICCahTAGyOiSSJ0gkM41NVPz7DPTSElquuJsfIbEj9oAqmWlkg4x84Dr9ubFJ2vuh0jgepg9Z+DwYZSDLLlhIeuLn6u0AWqpyEOpTv/gh6X5UYIbnh6cz3YLrTll7mjPziig1PlFwIDAQAB';
        $aop->apiVersion = '1.0';
        $aop->postCharset='UTF-8';
        $aop->format='json';
        $aop->signType='RSA2';
        $aop->notifyUrl="http://m.niuniu.com/notifyUrl.php";
        $request = new AlipayTradeWapPayRequest();
        $request->setBizContent("{" .
            "    \"body\":\"对一笔交易的具体描述信息。如果是多种商品，请将商品描述字符串累加传给body。\"," .
            "    \"subject\":\"大乐透\"," .
            "    \"out_trade_no\":\"70501113121S101211116\"," .
            "    \"timeout_express\":\"90m\"," .
            "    \"total_amount\":9.00," .
            "    \"product_code\":\"QUICK_WAP_WAY\"" .
            "  }");
        $result = $aop->pageExecute($request);
        echo $result;exit;
    }



}