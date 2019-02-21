<?php
abstract class Dao {

    protected $DB;
    protected $READ_DB;
    protected $DBHOST;
    protected $DBPORT;
    protected $DBUSER;
    protected $DBPWD;
    protected $READ_DBHOST;
    protected $READ_DBPORT;
    protected $READ_DBUSER;
    protected $READ_DBPWD;
    protected $DBNAME;
    protected $TB_NAME;

    public $sql;
    public $params;
    public $result;
    public $LAST_INSERT_ID;
    public $rows;
    protected $limit_sql;
    protected $limit;
    public $total;
    public $mmc;
    public $redis;

    // 构造函数
    public function  __construct() {
        $this->DBHOST   =   DBHOST;
        $this->DBPORT   =   DBPORT;
        $this->DBUSER   =   DBUSER;
        $this->DBPWD    =   DBPWD;
        $this->READ_DBHOST   =   READ_DBHOST;
        $this->READ_DBPORT   =   READ_DBPORT;
        $this->READ_DBUSER   =   DBUSER;
        $this->READ_DBPWD    =   DBPWD;
        $this->DBNAME = DBNAME;
    }

    public function connection_db(){
        if(!$this->DB){
            try{
                $DSN = "mysql:host=".$this->DBHOST.";port=".$this->DBPORT.";dbname=".$this->DBNAME;
                if($this->DB = new PDO($DSN, $this->DBUSER, $this->DBPWD, array(PDO::ATTR_PERSISTENT => false))){
                    $this->DB->query('SET NAMES utf8mb4');
                    $this->DB->query('set interactive_timeout=3600');
                    $this->DB->query("SET CHARACTER SET utf8mb4");
                    $this->DB->query("SET COLLATION_CONNECTION='utf8mb4_general_ci'");
                    $this->DB->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                }else{
                    die("错误：<strong style=\"color:orange\">DB001</strong>");
                }
            }catch(Exception $e) {
                $this->err_log(var_export($e,1));
                header('HTTP/1.1 503 Service Temporarily Unavailable');
                header('Status: 503 Service Temporarily Unavailable');
                header('Retry-After: 3600');
                header('X-Powered-By:66173.cn');
                echo '服务器恢复未满血，谢谢您的耐心等待。';
                exit();
            }
        }
    }

    public function connect_read_db(){
        ini_set("display_errors","on");
        if(!$this->READ_DB){
            try{
                $DSN = "mysql:host=".$this->READ_DBHOST.";port=".$this->READ_DBPORT.";dbname=".$this->DBNAME;
                if($this->READ_DB = new PDO($DSN, $this->DBUSER, $this->DBPWD, array(PDO::ATTR_PERSISTENT => false))){
                    $this->READ_DB->query('SET NAMES utf8mb4');
                    $this->READ_DB->query("SET CHARACTER SET utf8mb4");
                    $this->READ_DB->query('set interactive_timeout=3600');
                    $this->READ_DB->query("SET COLLATION_CONNECTION='utf8_general_ci'");
                    $this->READ_DB->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                }else{
                    die("错误：<strong style=\"color:orange\">DB001</strong>");
                }
            }catch(Exception $e) {
                $this->err_log(var_export($e,1));
                header('HTTP/1.1 503 Service Temporarily Unavailable');
                header('Status: 503 Service Temporarily Unavailable');
                header('Retry-After: 3600');
                header('X-Powered-By:66173.cn');
                echo '服务器恢复未满血，谢谢您的耐心等待。';
                exit();
            }
        }
    }

    // 析构函数
    public function  __destruct() {
        @$this->DB = null;
    }

    // 插入，记录LAST_INSERT_ID
    public function doInsert(){
        $this->connection_db();
        $this->rows = 0;
        $sth = $this->DB->prepare($this->sql);
        $this->rows = $sth->execute($this->params);
        if($sth->errorCode()!='00000'){
            $this->err_log($this->sql."\n".$this->params."\n".$sth->errorInfo());
        }else{
            $this->LAST_INSERT_ID = $this->DB->lastInsertId();
        }
    }

    // 插入，更新，删除，不记录LAST_INSERT_ID
    public function doExecute(){
        $this->connection_db();
        $this->rows = 0;
        $sth = $this->DB->prepare($this->sql);
        $this->rows = $sth->execute($this->params);
        if($sth->errorCode()!='00000'){
            $this->err_log($this->sql."\n".$this->params."\n".$sth->errorInfo());
            exit;
        }
    }

    // 查询单条记录
    public function doResult(){
        $this->connect_read_db();
        $this->result='';
        $sth = $this->READ_DB->prepare($this->sql);
        $sth->execute($this->params);
        try {
            $this->result = $sth->fetchAll(PDO::FETCH_ASSOC);
            $this->result = $this->result?$this->result[0]:'';
        } catch (Exception $e) {
            $this->err_log($this->sql."\n".$this->params."\n".$e->getMessage());
        }
    }

    // 查询集合
    public function doResultList(){
        $this->connect_read_db();
        $this->result = '';
        $sth = $this->READ_DB->prepare($this->sql);
        $sth->execute($this->params);
        try {
            $this->result = $sth->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            $this->err_log($this->sql . "\n" . $this->params . "\n" . $e->getMessage());
        }
    }

    function _setLimit($page , $page_size = PERPAGE){
        $sql = explode("from", $this->limit_sql);
        $sql = explode("order by", $sql[1]);

        $this->sql = " select count(1) as sum from " . $sql[0];
        $this->doResult();
        $this->total = $this->result && isset($this->result['sum']) ? $this->result['sum']: 0;
        $this->limit = ($page-1 < 0 ? 0 : ($page - 1)) * $page_size;
    }

    //分页通用方法
    function doLimitResultList($page, $page_size=PERPAGE){
        $this->sql = "select count(1) as sum from (".$this->limit_sql.")tb";
        $this->doResult();
        $this->total = $this->result && isset($this->result['sum']) ? $this->result['sum']: 0;
        $page>(ceil($this->total/$page_size))?$page=ceil($this->total/$page_size):$page;
        $this->limit = ($page-1 < 0 ? 0 : ($page - 1))*$page_size;
        $this->sql=$this->limit_sql . " limit " . $this->limit . "," .$page_size;
        $this->doResultList();
    }
    //优化分页通用方法
    function doLimitResultList_OP($page,$count='',$page_size=PERPAGE){
        if ($count!=''){
            $this->total = $count;
        }else{
            $this->total = 0;
        }
        $page>(ceil($this->total/$page_size))?$page=ceil($this->total/$page_size):$page;
        $this->limit = ($page-1 < 0 ? 0 : ($page - 1))*$page_size;
        $this->sql=$this->limit_sql . " limit " . $this->limit . "," .$page_size;
        $this->doResultList();
    }
    //事务开始
    function doAffairsBegin(){
        $this->connection_db();
        $res = $this->DB->beginTransaction();
        if(!$res){
            $this->err_log("事务开启失败");
            exit();
        }
    }
    //事务提交
    function doAffairsCommit(){
        $this->connection_db();
        $res = $this->DB->commit();
        if(!$res){
            $this->err_log("事务提交失败");
            exit();
        }
    }
    //事务回滚
    function doAffairsRollback(){
        $this->connection_db();
        try {
            $res = $this->DB->rollBack();
            if(!$res){
                $this->err_log("事务回滚失败");
                exit();
            }
        } catch (Exception $e) {
            $this->err_log($e->getMessage());
            exit();
        }
    }

    // 加载
    public function get($id){
        $this->sql = "select * from ".$this->TB_NAME." where id=".$id;
        $this->doResult();
        return $this->result;
    }

    // 加载集合
    public function getAll($orderBy='id', $isAsc=false){
        $this->sql = "select * from ".$this->TB_NAME." order by ".$orderBy." ".($isAsc ? "asc" : "desc");
        $this->doResultList();
        return $this->result;
    }

    // 真删除
    public function realDelete($id){
        $this->sql = "delete from ".$this->TB_NAME." where id=".$id;
        $this->doExecute();
    }

    // 假删除
    public function fakeDelete($id){
        $this->sql = "update ".$this->TB_NAME." set is_del=1 where id=".$id;
        $this->doExecute();
    }

    public function err_log($word, $filename='db') {
//        file_put_contents(PREFIX.DS.'logs/'.$filename.'_'.date('Ymd').'.log', strftime("%Y-%m-%d %H:%M:%S",time())."\r\n".$word."\r\n",FILE_APPEND);
    }
}
?>