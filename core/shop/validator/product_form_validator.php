<?php
// -------------------------------------------------------
// 店铺系统 - 商品订单校验 <zbc> <2016-04-26>
// -------------------------------------------------------
class productFormValidator{

    private $form; 
    private $url = '';

    public function __construct($form=array()){
        $this->form = $form;
        $this->url  = $_SESSION['login_back_url'];
    }

    public function shop_order_form_validator(){
        $do = $this->form['do'];
        if($do == 'character'){
            $this->shop_order_form_character_validator();
        }elseif($do == 'recharge'){
            $this->shop_order_form_recharge_validator();
        }else{
            die('牛牛发飙,无人能挡! 请您重头再来一遍~~'.$this->shop_get_back_url());
        }
    }

    private function shop_order_form_character_validator(){
        $this->check_verify_code();
        $this->shop_order_form_base_validator();
        if($this->form['is_rand_user']==0){
            if(!$this->form['role_back_name'] ||
                strlen($this->form['role_back_name']) ==0  ||
                strlen($this->form['role_back_name'])>50){
                $_SESSION['pay_error']='发生错误，错误代码E002';
                die("角色名长度有误！".$this->shop_get_back_url());
            }
        }
    }

    private function shop_order_form_recharge_validator(){
        $this->check_verify_code();
        $this->shop_order_form_base_validator();
        if(!$this->form['game_user'] ||
            strlen($this->form['game_user']) ==0  ||
            strlen($this->form['game_user'])>50){
            $_SESSION['pay_error']='发生错误，错误代码E004';
            die("续充账号有误！".$this->shop_get_back_url());
        }
        if(!$this->form['game_pwd'] ||
            strlen($this->form['game_pwd']) ==0  ||
            strlen($this->form['game_pwd'])>30){
            $_SESSION['pay_error']='发生错误，错误代码E005';
            die("续充账号密码长度有误！".$this->shop_get_back_url());
        }
    }

    private function shop_order_form_base_validator(){
        $this->shop_is_empty($this->form['channel_id'], '来源渠道不存在！');
        $this->shop_is_numeric($this->form['channel_id'], '来源渠道不正确！');
        $this->shop_is_empty($this->form['serv_id'],'游戏区服不存在！');
        $this->shop_is_numeric($this->form['serv_id'],'游戏区服必须为数字！');
        $this->shop_is_empty($this->form['id'],'商品不存在！');
        $this->shop_is_numeric($this->form['id'],'商品编号必须为数字！');
        $this->shop_is_empty($this->form['game_id'],'游戏不存在！');
        $this->shop_is_numeric($this->form['game_id'],'游戏编号必须为数字！');
        $this->shop_is_match(
            "/^13[0-9]{9}$|14[0-9]{9}|15[0-9]{9}$|18[0-9]{9}$|17[0-9]{9}$/",
            $this->form['tel'],
            '请输入正确的手机号！'
            );
        $this->shop_is_match(
            "/^[1-9][0-9]{4,15}$/",
            $this->form['qq'],
            '请输入正确的QQ号码！'
            );
    }

    private function check_verify_code(){
        if(!$this->form['pagehash'] || $this->form['pagehash'] != $_SESSION['page-hash']){
            $_SESSION['pay_error'] = '发生错误，错误代码E001';
            die("页面已过期".$this->shop_get_back_url());
        }
    }

    private function shop_is_match($preg, $target, $err='err',$url=''){
        $info = $err.$this->shop_get_back_url($url);
        if(!preg_match($preg, $target)) die($info);
        return true;
    }
    private function shop_is_eq($str1, $str2, $err='err',$url=''){
        $info = $err.$this->shop_get_back_url($url);
        if($str1 != $str2) die($info);
        return true;
    }
    private function shop_is_empty($id, $err='err', $url=''){
        $info = $err.$this->shop_get_back_url($url);
        if(!$id) die($info);
        return true;
    }
    private function shop_is_numeric($id, $err='err',$url=''){
        $info = $err.$this->shop_get_back_url($url);
        if(!is_numeric($id)) die($info);
        return true;
    }
    private function shop_get_back_url($url=''){
        $url = $url?:($this->url?:'http://shop.66173.cn');
        return '<br>3秒后返回....<meta http-equiv="refresh" content="3;url='.$url.'" />';
    }
}
