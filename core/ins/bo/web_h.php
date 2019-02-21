<?php
/**
 * Created by PhpStorm.
 * User: may
 * Date: 17/7/28
 * Time: 19:06
 */
COMMON('baseCore', 'pageCore','imageCore','uploadHelper','class.phpmailer','oauth.qq');
class web_h  extends baseCore{
    public function __construct() {
        parent::__construct();
    }
    public function account_bill() {
        $this->display("web_h/account_bill.html");
    }
    public function account_center() {
        $this->display("web_h/account_center.html");
    }
    public function account_change_password() {
        $this->display("web_h/account_change_password.html");
    }
    public function account_niubi() {
        $this->display("web_h/account_niubi.html");
    }
    public function account_niudian() {
        $this->display("web_h/account_niudian.html");
    }
    public function account_phone_bind() {
        $this->display("web_h/account_phone_bind.html");
    }
    public function account_phone_bind_change() {
        $this->display("web_h/account_phone_bind_change.html");
    }
    public function account_real_verify() {
        $this->display("web_h/account_real_verify.html");
    }
    public function message_center() {
        $this->display("web_h/message_center.html");
    }
    public function message_detail() {
        $this->display("web_h/message_detail.html");
    }
    public function service_account_find() {
        $this->display("web_h/service_account_find.html");
    }
    public function service_center() {
        $this->display("web_h/service_center.html");
    }
    public function service_detail() {
        $this->display("web_h/service_detail.html");
    }
    public function service_work_order() {
        $this->display("web_h/service_work_order.html");
    }
}