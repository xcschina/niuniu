<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/5/31
 * Time: 14:06
 */
require_once 'config.php';
//DAO('redisDemo');
$redis = new Redis();
$redis->pconnect('192.168.0.168','6379') or die("redis connect fail");

$redis->select(2);
//string 字符串
$redis->set('name','hello',60);
$data = $redis->get('name');
//list 列表
$redis->lpush('list','hello');
$redis->lpush('list','word');
$redis->lpush('list','!');
$redis->lrange('list',0,10);
//hash 哈希
$redis->hSet('hash','key','value');
$redis->hSet('hash','key1','value1');
$redis->hMGet('hash',array('key','key1'));
//set 集合
$redis->sAdd('runoob','mongodb','rabitmq','rabitmq');
$redis->sMembers('runoob');
//zset 有序集合
$redis->zAdd('runoob1',3,'redis');
$redis->zAdd('runoob1',3,'mongodb');
$redis->zAdd('runoob1',5,'rabitmq');
$redis->zRangeByScore('runoob1', 0, 4);


