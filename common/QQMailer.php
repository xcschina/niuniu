<?php
COMMON('class.phpmailer');
class QQMailer
{
    public static $HOST = 'smtp.163.com'; // QQ 邮箱的服务器地址
    public static $PORT = 465; // smtp 服务器的远程服务器端口号
    public static $SMTP = 'ssl'; // 使用 ssl 加密方式登录
    public static $CHARSET = 'UTF-8'; // 设置发送的邮件的编码
//    private static $USERNAME =  'm17125684552@163.com'; // 授权登录的账号
    private static $PASSWORD = 'a123456'; // 授权登录的密码
    private static $NICKNAME = '外部商会'; // 发件人的昵称
    private static  $user_list = array("m17125684552@163.com","m17125684553@163.com","m17125684419@163.com","m17125684645@163.com","m17125689341@163.com","m17125686435@163.com","m17125684356@163.com");
    /**
     * QQMailer constructor.
     * @param bool $debug [调试模式]
     */

    public function rand_username(){
        $key = rand(0,6);
        $username = self::$user_list[$key];
        return $username;
    }

    public function __construct($debug = false)
    {
        $this->mailer = new PHPMailer();
        $this->mailer->SMTPDebug = $debug ? 1 : 0;
        $this->mailer->isSMTP(); // 使用 SMTP 方式发送邮件
    }

    /**
     * @return PHPMailer
     */
    public function getMailer()
    {
        return $this->mailer;
    }

    private function loadConfig($name)
    {
        $USERNAME = $this->rand_username();
        /* Server Settings  */
        $this->mailer->SMTPAuth = true; // 开启 SMTP 认证
        $this->mailer->Host = self::$HOST; // SMTP 服务器地址
        $this->mailer->Port = self::$PORT; // 远程服务器端口号
        $this->mailer->SMTPSecure = self::$SMTP; // 登录认证方式
        /* Account Settings */
        $this->mailer->Username = $USERNAME; // SMTP 登录账号
        $this->mailer->Password = self::$PASSWORD; // SMTP 登录密码
        $this->mailer->From = $USERNAME; // 发件人邮箱地址
        $this->mailer->FromName = $name?$name:self::$NICKNAME; // 发件人昵称（任意内容）
        /* Content Setting  */
        $this->mailer->isHTML(true); // 邮件正文是否为 HTML
        $this->mailer->CharSet = self::$CHARSET; // 发送的邮件的编码
    }

    /**
     * Add attachment
     * @param $path [附件路径]
     */
    public function addFile($path)
    {
        $this->mailer->addAttachment($path);
    }


    /**
     * Send Email
     * @param $email [收件人]
     * @param $title [主题]
     * @param $content [正文]
     * @return bool [发送状态]
     */
    public function send($title, $content,$email,$name='')
    {
        $this->loadConfig($name);
//        foreach(self::$email_list as $key=>$email){
//            $this->mailer->addAddress($email); // 收件人邮箱
//        }
        $this->mailer->addAddress($email); // 收件人邮箱
        $this->mailer->Subject = $title; // 邮件主题
        $this->mailer->Body = $content; // 邮件信息
        return (bool)$this->mailer->send(); // 发送邮件
    }
}