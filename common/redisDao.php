<?php
abstract class redisDao{

    public $key_prefix;
	protected $redis;
	protected $host;
	protected $port;
	protected $auth;
	protected $timeout;
	private $auto_close_conn;
	protected $total;
	protected $start;
	protected $end;

    public function  __construct() {
        $this->host = REDIS_HOST;
        $this->port = REDIS_PORT;
        $this->auth = '';
        $this->redis = new Redis();
    }

    public function set_connection($host = REDIS_HOST, $port = REDIS_PORT, $auth = REDISPWD){
        $this->host = $host;
        $this->port = $port;
        $this->auth = $auth;
    }

    //开启连接
    //$auto_close_conn 默认短连接
    //序列化格式  默认不序列化   Redis::SERIALIZER_PHP php内置序列化方式  SERIALIZER_IGBINARY 使用igBinary库进行序列化
    public function connection($auto_close_conn = TRUE, $serializer_type= Redis::SERIALIZER_NONE){
    	try{
    		if($this->redis = new Redis()){

				if($this->auth){
					$this->redis->auth($this->auth);
				}

	    		if($this->auto_close_conn = $auto_close_conn){
	    			$this->redis->connect($this->host, $this->port);
	    		}else{
	    			$this->redis->pconnect($this->host, $this->port);
	    		}

	    		if($this->key_prefix){
    				$this->redis->setOption(Redis::OPT_PREFIX, $this->key_prefix);
    			}

    			$this->redis->setOption(Redis::OPT_SERIALIZER, $serializer_type);
    		}else{
                $this->err_log(var_export($this->redis->getLastError(),1));
    			die("错误：<strong style=\"color:orange\">$this->redis->getLastError()</strong>");
    		}
    	}catch (Exception $e){
    		print "error003:".$e->getMessage();
            $this->err_log(var_export($e,1));
    		exit();
    	}
    }

    //切换数据库 默认数据库0 成功返回true失败返回false
    public function switchDB($index = 0){
        return $this->redis->select($index);
    }

    //关闭连接
    public function close(){
        if($this->auto_close_conn){
            $this->redis->close();
        }
    }

    //获取当前连接信息
    public function getConnectionInfo(){
        try{
           if($this->redis->ping() == '+PONG'){
               return 1;
           }else{
               return 0;
           }
        }catch (Exception $e){
            print "error003:".$e->getMessage();
            $this->err_log(var_export($e,1));
            exit();
        }
    }

    //获取数据key的数量
    public function getKeySize(){
        return $this->redis->dbSize();
    }

    //获取redis信息used_memory_human total_commands_processed rejected_connections expired_keys  "# Keyspace db0"  未测试
    public function getInfo($info_name = ''){
        $info = $this->redis->info();
		
		if($info_name){
            echo $info_name;
			return $info[''.$info_name];
		}else{
			return $info;
		}
    }

    //获取最新的错误信息
    public function getErrorInfo(){
        return $this->redis->getLastError();
    }

    //设置redis参数
    public function setOption($key, $value){
        return $this->redis->setOption($key, $value);
    }

    //异步持久化 未测试
    public function syncSave(){
    	return $this->redis->bgSave();
    }

    //清空当前数据库
    public function clear(){
        $this->redis->flushDB();
    }

    //清空所有数据库 未测试
    public function clearAll(){
        $this->redis->flushAll();
    }

    //写错误日志到文件中
    public function err_log($word) {
        $fp = fopen(PREFIX."/htdocs/redis.txt","a");
        flock($fp, LOCK_EX) ;
        fwrite($fp,"执行日期：".strftime("%Y%m%d%H%M%S",time())."\n".$word."\n");
        flock($fp, LOCK_UN);
        fclose($fp);
    }

	//设置分页参数
    private function _setLimit($page = 1, $pageSize = PERPAGE){

    	$this->start = ($page - 1) * $pageSize;

    	$this->end = $this->start + $pageSize - 1;

    	if($this->end > $this->total){
    		$this->end = -1;
    	}
    }

    //获取自增id
    public function getId($key = ''){
    	if($key){
    		return $this->redis->incr('id_'.$key);
    	}else{
    		return $this->redis->incr('id');
    	}
    }

    //获取最新的id
    public function getLastId($key = ''){
    	if($key){
    		return $this->redis->get('id_'.$key);
    	}else{
    		return $this->redis->get('id');
    	}
    }

    //插入/更新
    //timeout参数指定则用于缓存  xx秒 成功返回true
    public function insert($key, $value, $timeout=0){
    	if($timeout){
    		return $this->redis->set($key, $value,$timeout);
    	}else{
    		return $this->redis->set($key, $value);
    	}
    }

    //查询单条记录
	public function get($key){
		if($this->exist($key)){
			return $this->redis->get($key);
		}else{
			return 0;
		}
    }

    //获取与pattern匹配的所有key
    public function gets($pattern = '*'){
        return $this->redis->keys($pattern);
    }

    //删除记录 失败返回0， 成功返回！=0
    public function delete($key){
    	return $this->redis->delete($key);
    }

    //判断记录是否存在
    public function exist($key){
    	return $this->redis->exists($key);
    }

    //key是否过期了 true过期
    public function isExpired($key){
       $time = $this->redis->ttl($key);

       if($time == -1 || $time == -2){
            return true;
       }else{
            return false;
       }
    }

    //插入/更新一行记录
    public function insertHash($key , $value){
    	return $this->redis->hMset($key, $value);
    }

    //获取一行记录
    public function getHash($key){
        $keys = $this->redis->hKeys($key);
        if($keys){
            return $this->redis->HMGet($key, $this->redis->hKeys($key));
        }else{
            return '';
        }
    }

    //为一条记录添加/更新一个字段
    public function setHashFiled($key, $field, $value){
    	$this->redis->HSet($key, $field, $value);
    }

	//获取一条记录一个字段值
    public function getHashFiled($key, $field){
    	return $this->redis->HGet($key, $field);
    }

    //检查field是否在hash中 存在返回true
    public function existedInHash($key, $field){
        return $this->redis->hExists($key, $field);
    }

    //删除hash里的一个field 成功返回true
    public function deleteFiledFromHash($key, $filed){
        return $this->redis->hDel($key, $filed);
    }

    //更新一个hash字段  默认对字段自增1  返回最新值
    public function increHashField($key, $field, $increments = 1){
    	return $this->redis->hIncrBy($key, $field, $increments);
    }

    //队列头添加一个新元素  成功返回长度失败返回false
    public function listAdd($key, $value){
    	return $this->redis->lPush($key, $value);
    }

	//队列尾添加一个新元素  成功返回长度失败返回false
    public function listTailAdd($key, $value){
    	return $this->redis->rPush($key, $value);
    }

    //队列中删除一个值 time参数是删除该值在队列中出现的次数，默认0则完全将该值从队列中删除
    //成功 返回删除的次数 否则false
    public function removeFromList($key, $value, $time = 0){
        return $this->redis->lRem($key, $value, $time);
    }

    //获取队列全部数据
    public function getAllList($key){
    	return $this->redis->lRange($key, 0, -1);
    }

    //获取队列头第一个元素 失败返回false
    public function getListHead($key){
        return $this->redis->lPop($key);
    }

    //获取队列尾第一个元素 失败返回false
    public function getListTail($key){
        return $this->redis->rPop($key);
    }

    //获取队列分页队列
    public function getList($key, $page = 1 , $pageSize = PERPAGE){
    	$this->_setListLimit($key, $page, $pageSize);
    	return $this->redis->lRange($key, $this->start, $this->end);
    }

    //设置队列分页参数
    private function _setListLimit($key, $page = 1, $pageSize = PERPAGE){
    	$this->total = $this->getListSize($key);

    	$this->_setLimit($page, $pageSize);
    }

    //获取队列长度
    public function getListSize($key){
    	return $this->redis->lLen($key);
    }

    //排序集合添加新元素
    public function sortedSetAdd($key, $value, $score = 0){
        return $this->redis->zAdd($key, $score, $value);
    }

    //排序集合修改元素排名值
    //如果元素不在排序集合中等同于zadd
    public function sortedSetUpdate($key ,$value, $score = 1){
    	return $this->redis->zIncrBy($key, $score, $value);
    }

    //排序集合特定对象的值
    public function getRankFromSortedSet($key, $value){
        return $this->redis->zScore($key, $value);
    }

    //获取排序集合
    //order desc按score从大到小
    //      aes 按score从小到大
    //withScore 是否在结果中返回score
    public function getSortedSet($key, $page = 1 , $pageSize = PERPAGE, $order = 'DESC', $withScore = FALSE){
    	$this->_setSortsetLimit($key, $page, $pageSize);

    	if($order == 'DESC'){
    		return $this->redis->zRevRange($key, $this->start, $this->end, $withScore);
    	}elseif($order == 'AES'){
    		return $this->redis->zRange($key, $this->start, $this->end, $withScore);
    	}
    }

    //设置排序集合分页参数
    private function _setSortsetLimit($key, $page = 1, $pageSize = PERPAGE){

    	$this->total = $this->getSortsetSize($key);

    	$this->_setLimit($page, $pageSize);
    }

    //获取排序集合的长度
    public function getSortsetSize($key){
		return $this->redis->zSize($key);
    }

    //从排序集合中删除一个值 返回1成功 0失败
    public function deleteValueFromSortSet($key, $val){
        return $this->redis->zDelete($key, $val);
    }

    //一般集合添加/更新数据  成功返回set长度,失败返回false
    public function setAdd($key, $value){
    	return $this->redis->sAdd($key, $value);
    }

    //获取set长度
    public function getSetSize($key){
        return $this->redis->sCard($key);
    }

    //获取set中所有的值 返回值 array对象
    public function getAllMembersInSet($key){
        return $this->redis->sMembers($key);
    }

    //从set里删除一个值  返回值 1完成 0失败
    public function deleteValueFromSet($key, $val){
        return $this->redis->sRem($key, $val);
    }


    //一般集合中是否存在某个值, true存在，false不存在
    public function hasExistInSet($key, $value){
    	return $this->redis->sIsMember($key, $value);
    }
}