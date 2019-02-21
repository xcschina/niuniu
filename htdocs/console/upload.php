<?php
header('Content-Type: text/html; charset=UTF-8');
require_once 'config.php';
COMMON('uploadHelper');

var_dump(32131);
if(!isset($_SERVER['HTTP_REFERER'])){
	exit();
}else{
	$referer = $_SERVER['HTTP_REFERER'];
	if(!strpos($referer, SITEURL)){
		exit();
	}
}

function up_img($img_name) {
   $upFile = new uploadHelper($img_name,INTRO_IMG);
   $upFile->upload();

   if ($upFile->occur_err()) {
   		return '';
   	} else {
   		return $upFile->get_rel_file_path();
   	}
}

$err = '';
$filename = '';
$pypath = PREFIX.DS."htdocs/static/";
$realpath = INTRO_IMG.DS.date("Ym").DS;
	
if(isset($_SERVER['HTTP_CONTENT_DISPOSITION'])&&preg_match('/attachment;\s+name="(.+?)";\s+filename="(.+?)"/i',$_SERVER['HTTP_CONTENT_DISPOSITION'],$info)){//HTML5上传
	$content = file_get_contents('php://input');
	$filename = time().mt_rand(1, 1000).substr($info[2], strpos($info[2], '.'));
	if (!file_exists($pypath.$realpath)) {
		@mkdir($pypath.$realpath, 0777, true);
	}
	$file = fopen($pypath.$realpath.$filename,"w");
	fwrite($file,$content);//写入
	fclose($file);//关闭
}else{//标准表单式上传
	$upfile=@$_FILES['filedata'];
	if(!isset($upfile)){
		$err='文件域的name错误';
	}elseif(!empty($upfile['error'])){
		switch($upfile['error']){
			case '1':
				$err = '文件大小超过了php.ini定义的upload_max_filesize值';
				break;
			case '2':
				$err = '文件大小超过了HTML定义的MAX_FILE_SIZE值';
				break;
			case '3':
				$err = '文件上传不完全';
				break;
			case '4':
				$err = '无文件上传';
				break;
			case '6':
				$err = '缺少临时文件夹';
				break;
			case '7':
				$err = '写文件失败';
				break;
			case '8':
				$err = '上传被其它扩展中断';
				break;
			case '999':
			default:
				$err = '无有效错误代码';
		}
        alert($err);
	}elseif(empty($upfile['tmp_name']) || $upfile['tmp_name'] == 'none'){
		$err = '无文件上传';
        alert($err);
	}else{
		$filename = up_img('filedata');
		$realpath = 'image';
	}
}

//echo "{'err':'".$err."','msg':'".jsonString("http://".IMG_DOMAIN.'/'.$realpath.$filename)."'}";
header('Content-type: text/html; charset=UTF-8');
$json = new Services_JSON();
echo $json->encode(array('error' => 0, 'url' => 'http://'.IMG_DOMAIN.'/'.$realpath.$filename));
exit;

function jsonString($str)
{
	return preg_replace("/([\\\\\/'])/",'\\\$1',$str);
}
function formatBytes($bytes) {
	if($bytes >= 1073741824) {
		$bytes = round($bytes / 1073741824 * 100) / 100 . 'GB';
	} elseif($bytes >= 1048576) {
		$bytes = round($bytes / 1048576 * 100) / 100 . 'MB';
	} elseif($bytes >= 1024) {
		$bytes = round($bytes / 1024 * 100) / 100 . 'KB';
	} else {
		$bytes = $bytes . 'Bytes';
	}
	return $bytes;
}

function alert($msg) {
    header('Content-type: text/html; charset=UTF-8');
    $json = new Services_JSON();
    echo $json->encode(array('error' => 1, 'message' => $msg));
    exit;
}


?>