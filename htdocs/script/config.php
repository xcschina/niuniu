<?php
//配置文件
define("DBHOST", "192.168.91.168");
define("DBPORT", "3306");
define("READ_DBHOST", "192.168.91.168");
define("READ_DBPORT", "3306");
define("DBUSER", "root");
define("DBPWD", "123456");
define("DBNAME", "niuniu");
define('REDIS_HOST', '127.0.0.1');
define('REDIS_PORT', '6379');
define("MMCHOST", "10.10.203.115");
define("MMCPORT", "11211");
define("DBNAME", "niuniu");
define("APP","script");


//路径符号，win和linux会有区别
define('PREFIX', realpath(dirname(__FILE__)));
define("DS", DIRECTORY_SEPARATOR);
$cur_dir = dirname(__FILE__);
chdir($cur_dir);

//常用类文件夹
define("COMMON", "..".DS."..".DS."common".DS);
//数据操作类文件夹
define("DAO", "..".DS."..".DS."core".DS."script".DS."dao".DS);
define("BO", "..".DS."..".DS."core".DS."script".DS."bo".DS);
require '../../vendor/autoload.php';
spl_autoload_register('core_autoloader', false, true);