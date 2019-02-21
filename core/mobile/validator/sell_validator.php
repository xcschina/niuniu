<?php
COMMON("validator");
class sellValidator extends ValidFluent{
    protected $sell;
    public function __construct($sell){
        parent::__construct($sell);
        $this->sell = $sell;
    }

    function check_login(){
        if(!$_SESSION['user_id']){
            die("请先登入");
        }
    }

    function check_verify_code(){
        if(!$this->sell['pagehash'] || $this->sell['pagehash']!=$_SESSION['page-hash']){
            $_SESSION['pay_error']='发生错误，错误代码E001';
            $this->redirect("");
            exit;
        }
    }

    function check_account_one($back_url){
        $this->check_verify_code();
        $this->name("ch")->numberInteger("请选择渠道");
        $this->name('title')->required("标题必须填写")->minSize(5,"标题最少5个字")->maxSize(50,"标题最长50个字");
        $this->name('desc')->required("描述必须填写")->minSize(5,"描述最少5个字")->maxSize(300,"描述最长300个字");
        $this->name("price")->numberRic("价格必须填写")->minSize(1,"价格必须填写")->maxSize(5,"最大五位数");
        if(!$this->isGroupValid()){
            $_SESSION['sell_error'] = $this->getMessage();
            header("Location:".$back_url);
            exit;
        }
    }

    function check_publish($back_url){
        $this->check_login();
        $this->check_verify_code();

        if(!$_SESSION['sell_info']){
            $this->redirect("");
            exit;
        }

        $this->name("game_id")->numberInteger("请选择游戏");
        $this->name("game_user")->required("请填写游戏账号")->maxSize(50,"游戏账号最长50个字");
        $this->name("game_pwd")->required("请填写游戏密码")->maxSize(30,"游戏密码最长30个字");
        $this->name("game_pwd_again")->sameValue($this->getValue("game_pwd"),"俩次密码不一致");
        $this->name("role_name")->required("请填写游戏角色")->maxSize(30,"游戏角色最长30个字");
        $this->name("role_level")->required("请填写游戏等级")->maxSize(5,"游戏等级请填写数字,最长5个字");
        $this->name("game_user_lock")->maxSize(30,"安全锁最长30个字");
        $this->name("overdue")->numberInteger("请选择发布有效期");
        $this->name("do")->required("请选择商品类型");
        if(!$this->isGroupValid()){
            $_SESSION['publishu_error'] = $this->getMessage();
            header("Location:".$back_url);
            exit;
        }
    }
}