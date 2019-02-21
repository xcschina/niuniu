<?php
header("Content-Type: text/html; charset=utf-8");
ob_start(); //打开输出缓冲区 
ob_end_flush();
ob_implicit_flush(1); //立即输出

$cmd = "java -jar /data/webroot/androidpack/AndroidPack.jar "
    ."/data/webroot/66173.com/htdocs/apk "
    ."/data/webroot/66173.com/htdocs/apk/fknsg.apk "
    .'mengxiang'." "
    ."/data/webroot/66173.com/htdocs/wjx.keystore "
    ."12152205 ";

echo $cmd;
echo "<br/>";
try {
    system($cmd,$err);
    //echo $err;
} catch (Exception $e) {
    echo "发生错误:";
    print $e->getMessage();
}
?>