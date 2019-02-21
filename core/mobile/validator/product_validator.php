<?php
class productValidator{
    protected $product;
    public function __construct($product){
        $this->product = $product;
    }
    function check_verify_code(){
        if(!$this->product['pagehash'] || $this->product['pagehash']!=$_SESSION['page-hash']){
            $_SESSION['pay_error']='发生错误，错误代码E001';
            die("发生错误");
        }
    }

    function check_character($id){
        $this->check_verify_code();
        $this->base_check($id);

        if($this->product['is_rand_user']==0){
            if(!$this->product['role_back_name'] ||
                strlen($this->product['role_back_name']) ==0  ||
                strlen($this->product['role_back_name'])>50){
                $_SESSION['pay_error']='发生错误，错误代码E002';
                header("Location:http://".SITEURL."/shop".$id);
                exit;
            }
        }
    }

    function check_recharge($id, $dao){
        $this->check_topup($id);
        //if(!$this->product['order_id'] || !$dao->check_usr_order($_SESSION['user_id'], $this->product['order_id'])){
        if(!$this->product['order_id']){
            $_SESSION['pay_error']='发生错误，错误代码E003';
            header("Location:http://".SITEURL."/shop".$id);
            exit;
        }
    }

    function check_topup($id){
        $this->base_check($id);
        if(!$this->product['game_user'] ||
            strlen($this->product['game_user']) ==0  ||
            strlen($this->product['game_user'])>50){
            $_SESSION['pay_error']='发生错误，错误代码E004';
            header("Location:http://".SITEURL."/shop".$id);
            exit;
        }

        if(!$this->product['game_pwd'] ||
            strlen($this->product['game_pwd']) ==0  ||
            strlen($this->product['game_pwd'])>30){
            $_SESSION['pay_error']='发生错误，错误代码E005';
            header("Location:http://".SITEURL."/shop".$id);
            exit;
        }
    }

    function check_iap($id){
        $this->base_check($id);
        if(!$this->product['game_user'] ||
            strlen($this->product['game_user']) ==0  ||
            strlen($this->product['game_user'])>50){
            $_SESSION['pay_error']='发生错误，错误代码E004';
            header("Location:http://".SITEURL."/shop".$id);
            exit;
        }

        if(!$this->product['game_pwd'] ||
            strlen($this->product['game_pwd']) ==0  ||
            strlen($this->product['game_pwd'])>30){
            $_SESSION['pay_error']='发生错误，错误代码E005';
            header("Location:http://".SITEURL."/shop".$id);
            exit;
        }

        if(!$this->product['role_level'] ||
            strlen($this->product['role_level']) ==0  ||
            strlen($this->product['role_level'])>10){
            $_SESSION['pay_error']='发生错误，错误代码E-ROLE-LEVEL';
            header("Location:http://".SITEURL."/shop".$id);
            exit;
        }
    }

    function base_check($id){

        if(!$this->product['channel_id'] || !is_numeric($this->product['channel_id'])){
            $_SESSION['pay_error']='发生错误，错误代码E006';
            header("Location:http://".SITEURL."/shop".$id);
            exit;
        }

        if(!$this->product['serv_id'] || !is_numeric($this->product['serv_id'])){
            $_SESSION['pay_error']='发生错误，错误代码E007';
            header("Location:http://".SITEURL."/shop".$id);
            exit;
        }

        if(!$this->product['role_name'] || strlen($this->product['role_name']) ==0  || strlen($this->product['role_name'])>50){
            $_SESSION['pay_error']='发生错误，错误代码E008';
            header("Location:http://".SITEURL."/shop".$id);
            exit;
        }

        if(!$this->product['tel'] || strlen($this->product['tel']) ==0  || strlen($this->product['tel'])>15 || !is_numeric($this->product['tel'])){
            $_SESSION['pay_error']='发生错误，错误代码E009';
            header("Location:http://".SITEURL."/shop".$id);
            exit;
        }

        if(!$this->product['qq'] || strlen($this->product['qq']) ==0  || strlen($this->product['qq'])>13 || !is_numeric($this->product['qq'])){
            $_SESSION['pay_error']='发生错误，错误代码E010';
            header("Location:http://".SITEURL."/shop".$id);
            exit;
        }
    }

    function check_session_order(){
        if(!$_POST['pay-channel'] || !$_POST['id']){
            $_SESSION['pay_error']='发生错误，错误代码E011';
            die("发生错误");
        }

        if(!$_SESSION['order']['serv_id'] || !$_SESSION['order']['do']||
            !$_SESSION['order']['tel'] || !$_SESSION['order']['qq'] || !$_SESSION['order']['channel_id'] ||
            !$_SESSION['order']['service_id'] || !$_SESSION['order']['price'] || !$_SESSION['order']['serv_name']){
            $_SESSION['pay_error']='发生错误，错误代码E012';
            header("Location:http://".SITEURL."/shop".$_POST['id']);
            exit;
        }

        if($_SESSION['order']['do'] == 'topup' || $_SESSION['order']['do']=='recharge'){
            if(!$_SESSION['order']['game_user'] || !$_SESSION['order']['game_pwd']){
                $_SESSION['pay_error']='发生错误，错误代码E013';
                header("Location:http://".SITEURL."/shop".$_POST['id']);
                exit;
            }
        }
    }
}