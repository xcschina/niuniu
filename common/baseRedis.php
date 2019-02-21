<?php
COMMON('redisDao');
class baseRedis extends redisDao{
    public function  __construct() {
        parent::__construct();
        $this->connection();
    }
}