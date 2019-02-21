<?php
class productValidator{
    protected $product;
    public function __construct($product){
        $this->product = $product;
    }
    function check_verify_code(){
        if(!$this->product['pagehash'] || $this->product['pagehash']!=$_SESSION['page-hash']){
            die("发生错误");
        }
    }

    function check_character($id){
        $this->check_verify_code();
        $this->base_check($id);

        if(!$this->product['role_back_name'] ||
            strlen($this->product['role_back_name']) ==0  ||
            strlen($this->product['role_back_name'])>50){
            header("Location:http://".SITEURL."/item".$id);
            exit;
        }
    }

    function check_recharge($id, $dao){
        $this->check_topup($id);
        if(!$this->product['order_id'] || !$dao->check_usr_order($_SESSION['user_id'], $this->product['order_id'])){
            header("Location:http://".SITEURL."/item".$id);
            exit;
        }
    }

    function check_topup($id){
        $this->base_check($id);
        if(!$this->product['game_user'] ||
            strlen($this->product['game_user']) ==0  ||
            strlen($this->product['game_user'])>50){
            header("Location:http://".SITEURL."/item".$id);
            exit;
        }

        if(!$this->product['game_pwd'] ||
            strlen($this->product['game_pwd']) ==0  ||
            strlen($this->product['game_pwd'])>30){
            header("Location:http://".SITEURL."/item".$id);
            exit;
        }
    }

    function base_check($id){

        if(!$this->product['channel_id'] || !is_numeric($this->product['channel_id'])){
            header("Location:http://".SITEURL."/item".$id);
            exit;
        }

        if(!$this->product['serv_id'] || !is_numeric($this->product['serv_id'])){
            header("Location:http://".SITEURL."/item".$id);
            exit;
        }

        if(!$this->product['role_name'] || strlen($this->product['role_name']) ==0  || strlen($this->product['role_name'])>50){
            header("Location:http://".SITEURL."/item".$id);
            exit;
        }

        if(!$this->product['tel'] || strlen($this->product['tel']) ==0  || strlen($this->product['tel'])>15 || !is_numeric($this->product['tel'])){
            header("Location:http://".SITEURL."/item".$id);
            exit;
        }

        if(!$this->product['qq'] || strlen($this->product['qq']) ==0  || strlen($this->product['qq'])>13 || !is_numeric($this->product['qq'])){
            header("Location:http://".SITEURL."/item".$id);
            exit;
        }
    }

    function check_session_order(){
        if(!$_POST['pay-channel'] || !$_POST['id']){
            die("发生错误");
        }

        if(!$_SESSION['order']['serv_id'] || !$_SESSION['order']['role_name'] || !$_SESSION['order']['do']||
            !$_SESSION['order']['tel'] || !$_SESSION['order']['qq'] || !$_SESSION['order']['channel_id'] ||
            !$_SESSION['order']['service_id'] || !$_SESSION['order']['price'] || !$_SESSION['order']['serv_name']){
            header("Location:http://".SITEURL."/item".$_POST['id']);
            exit;
        }

        if($_SESSION['order']['do'] == 'character' && !$_SESSION['order']['role_back_name']){
            header("Location:http://".SITEURL."/item".$_POST['id']);
            exit;
        }

        if($_SESSION['order']['do'] == 'topup' || $_SESSION['order']['do']=='recharge'){
            if(!$_SESSION['order']['game_user'] || !$_SESSION['order']['game_pwd']){
                header("Location:http://".SITEURL."/item".$_POST['id']);
                exit;
            }
        }
    }

    // ------------------
    //  zbc 验证
    // ------------------
    private $url = '';

    // ------ 验证 ------
    public function v2_base_check(){
        $this->v2_is_empty($this->product['channel_id'], '来源渠道不存在！');
        $this->v2_is_numeric($this->product['channel_id'], '来源渠道不正确！');
        $this->v2_is_empty($this->product['serv_id'],'游戏区服不存在！');
        $this->v2_is_numeric($this->product['serv_id'],'游戏区服必须为数字！');
        $this->v2_is_empty($this->product['product_id'],'商品不存在！');
        $this->v2_is_numeric($this->product['product_id'],'商品编号必须为数字！');
        $this->v2_is_empty($this->product['game_id'],'游戏不存在！');
        $this->v2_is_numeric($this->product['game_id'],'游戏编号必须为数字！');
        $this->v2_is_match(
            "/^13[0-9]{9}$|14[0-9]{9}|15[0-9]{9}$|18[0-9]{9}$|17[0-9]{9}$/",
            $this->product['tel'],
            '请输入正确的手机号！'
            );

        $this->v2_is_match(
            "/^[1-9][0-9]{4,15}$/",
            $this->product['qq'],
            '请输入正确的QQ号码！'
            );
    }

    // ------ 首充号
    public function v2_check_firstpay(){
        $this->v2_check_verify_code();
        $this->url = $this->v2_set_url($this->product['game_id']);
        $is_random = $this->product['is_random_nickname'];
        if(!$is_random){
            $this->v2_is_empty($this->product['role_name'], '请输入您的角色名！');
            $this->v2_is_empty($this->product['role_back_name'], '请输入您的备用角色名！');
            if($this->product['role_name'] == $this->product['role_back_name']){
                die('角色名和备用角色名不能相同！'.$this->v2_get_back_url());
            }
        }
        $this->v2_base_check();
        return true;
    }

    // ----- 续充
    public function v2_check_recharge(){
        $this->url = $this->v2_set_url($this->product['game_id']);
        $this->v2_base_check();
        return true;
    }

    // ------ 代充[包含：苹果代充] 
    public function v2_check_helppay(){
        $this->url = $this->v2_set_url($this->product['game_id']);
        $this->v2_base_check();
        $this->v2_is_empty($this->product['role_name'], '请输入您的角色名！');
        $this->v2_is_empty($this->product['game_user'], '请输入您要充值的帐号！');
        $this->v2_is_empty($this->product['game_pwd'], '请输入您的密码！');
        return true;
    }

    // ------ 游戏币
    public function v2_check_gamecoin(){
        $this->url = $this->v2_set_url($this->product['game_id']);
        $this->v2_base_check();
        $this->v2_is_empty($this->product['role_name'], '请输入您的角色名！');
        return true;
    }

    // ------ 账号
    public function v2_check_account(){
        $this->url = $this->v2_set_url($this->product['game_id']);
        $this->v2_base_check();
        return true;
    }


    // ----- 支付 --------
    public function v2_do_pay_check($product_id){
        $pay_channel = $this->product['pay-channel'];
        $bank        = $this->product['bank'];
        $game_id     = $this->product['game_id'];
        $this->url   = $this->v2_set_url($game_id);
        $this->v2_is_empty($_SESSION['order'],'订单已失效，请重新购买！');
        $this->v2_is_eq($_SESSION['order']['product_id'],$product_id,'订单有误，请重新购买！'); // 苹果代充 - 开启调试3
        $this->v2_is_empty($this->product,'购买页面已过期！');
        $this->v2_is_empty($pay_channel,'支付渠道不存在！');
        if($pay_channel == 2){
            $this->v2_is_empty($bank,'您选择了网银支付，请选择银行！~');
        }
        return true;
    }

    // 支付前的订单最终验证
    public function v2_check_session_order(){
        $order     = $_SESSION['order'];
        $this->url = $this->v2_set_url($_POST['game_id']);
        $this->v2_is_empty($_POST['pay-channel'],'订单充值渠道不能为空！');
        $this->v2_is_empty($_POST['id'],'订单商品不能为空！');
        $this->v2_is_empty($order['serv_id'],'订单游戏区服不能为空！');
        $this->v2_is_empty($order['do'],'订单操作选择有误！');
        $this->v2_is_empty($order['tel'],'订单手机号不能为空！');
        $this->v2_is_empty($order['qq'],'订单QQ号不能为空！');
        $this->v2_is_empty($order['channel_id'],'订单渠道不能为空');

        // 角色名
        switch ($order['do']) {
            case 'character': 
            if(!$order['is_random_nickname']){
                $this->v2_is_empty($order['role_name'],'请输入角色名！');
                if(!$order['role_back_name']){
                    die('备用角色名不能为空'.$this->v2_get_back_url());
                }
                if($order['role_name'] == $order['role_back_name']){
                    die('角色名和备用角色名不能相同！'.$this->v2_get_back_url());
                }
            }
            break;
            case 'helppay':
            case 'applepay':
                $this->v2_is_empty($order['game_user'],'代充账号不能为空');
                $this->v2_is_empty($order['game_pwd'],'代充密码不能为空');
            break;
            case 'recharge': break;
            case 'gamecoin': break;
            case 'account': break;
            default: break;
        }
    }

    public function v2_check_verify_code(){
        if(!$this->product['pagehash'] || $this->product['pagehash']!=$_SESSION['page-hash']){
            $this->url = 'http://shouyou.kuyoo.com/games.php';
            die("页面已过期".$this->v2_get_back_url());
        }
    }

    private function v2_is_match($preg, $target, $err='err',$url=''){
        $info = $err.$this->v2_get_back_url($url);
        if(!preg_match($preg, $target)) die($info);
        return true;
    }

    private function v2_is_eq($str1, $str2, $err='err',$url=''){
        $info = $err.$this->v2_get_back_url($url);
        if($str1 != $str2) die($info);
        return true;
    }

    private function v2_is_empty($id, $err='err', $url=''){
        $info = $err.$this->v2_get_back_url($url);
        if(!$id) die($info);
        return true;
    }

    private function v2_is_numeric($id, $err='err',$url=''){
        $info = $err.$this->v2_get_back_url($url);
        if(!is_numeric($id)) die($info);
        return true;
    }

    private function v2_get_back_url($url=''){
        $url = $url?:($this->url?:'http://shouyou.kuyoo.com');
        return '<br>3秒后返回....<meta http-equiv="refresh" content="3;url='.$url.'" />';
    }

    private function v2_set_url($game_id=null){
        return 'http://'.SITEURL.(is_int($game_id)?('/buy'.$game_id):'/games.php');
    }


}
