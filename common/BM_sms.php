<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/10/16
 * Time: 15:03
 */
COMMON('adminBaseCore');
class BaMi extends adminBaseCore{
    private $Uid;
    private $Password;
    private $Url;
    private $Filename="../bm_sms_log.txt"; //日志文件
    private $Handle;
    function __construct() {
        //主帐号ID
        $this->Uid = '2257';
        //主帐号密钥
        $this->Password = md5('13075868963');
        //请求地址，格式如下
        $this->Url = 'http://sms.bamikeji.com:8890/mtPort/mt/bindip/send';
        $this->Handle = fopen($this->Filename, 'a');
    }
    //短信下发接口
    public function toIndustry($mobile,$data,$type=''){
        $ret = array();
        $ret['uid'] = $this->Uid;
        $ret['passwd'] = $this->Password;
        $ret['phonelist'] = $mobile;
        switch($type){
            //对应云通讯模板ID：23267
            case'1':
                $ret['content'] = "【牛牛网络】您的验证码是".$data[0]."，5分钟之内有效！";
                break;
            //对应云通讯模板ID：201723、201718
            case'2':
                $ret['content'] = "【牛果游戏】您的验证码是".$data[0]."，5分钟之内有效！";
                break;
            //对应云通讯模板ID：240478
            case'3':
                $ret['content'] = "【精品游戏】您的验证码是".$data[0]."，5分钟之内有效！";
                break;
            //对应云通讯模板ID：181503
            case'4':
                $ret['content'] = "【66游戏】您的验证码是".$data[0]."，5分钟之内有效！";
                break;
            //对应云通讯模板ID：234120
            case'5':
                $ret['content'] = '【牛牛网络】亲爱的'.$data[0].'，你的后台余额已不足'.$data[1].'，请尽快联系工作人员安排打款，避免因余额不足造成无法正常充值。退订回N';
                break;
            //对应云通讯模板ID：124312
            case'6':
                $ret['content'] = '【酷游网络】验证码：'.$data[0].'，打死都不要告诉别人哦！';
                break;
            //对应云通讯模板ID：228851
            case'7':
                $ret['content'] = '【一起上捕鱼】您游戏绑定/解绑手机的验证码为：'.$data[0].'，有效期'.$data[1].'分钟，为保证账号安全，勿将验证码转告他人。';
                break;
            //对应云通讯模板ID：232268
            case'8':
                $ret['content'] = '【牛果小市】您的验证码是：'.$data[0].'，10分钟内有效。请不要把验证码泄露给他人，如非本人操作，可不用理会。';
                break;
            //对应云通讯模板ID：226704
            case'9':
                $ret['content'] = '【66游戏】《英魂外传》官方预约验证码是'.$data[0].'，'.$data[1].'分钟内使用，有效提示：请勿泄露短信验证码';
                break;
            //对应云通讯模板ID：200875
            case'10':
                $ret['content'] = '【牛果游戏】恭喜您注册成功，您的初始密码为'.$data[0];
                break;
            //对应云通讯模板ID：172456
            case'11':
                $ret['content'] = '【牛牛网络】恭喜您注册成功，您的初始密码为'.$data[0];
                break;
            //对应云通讯模板ID：23267
            default:
                $ret['content'] = "【牛牛网络】您的验证码是".$data[0]."，5分钟之内有效！";
                break;
        }
        $url = "http://sms.bamikeji.com:8890/mtPort/mt/bindip/send";
        $html = $this->request($url,$ret);
        return $html;
    }

    //余额查询
    public function getAccountInfo(){
        $url = "http://sms.bamikeji.com:8890/mtPort/account/info";
        $ret = array();
        $ret['uid'] = $this->Uid;
        $ret['passwd'] = $this->Password;
        $html =  $this->request($url,$ret);
        return $html;
    }

}