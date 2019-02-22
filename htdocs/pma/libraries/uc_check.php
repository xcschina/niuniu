<?php
date_default_timezone_set('prc');
include('../sdk_config.php');
$FAIL_LOG_PATH = 'log/check_'.date('Y-m-d').'.log';
$LOG = true;
/**
*日志记录函数
*根据全局定义变量log 和 FAIL_LOG_PATH 来决定是否生成日志和日志生成位置
*/
function setLog($msg) {
	global $LOG;
	global $FAIL_LOG_PATH;
	if ($LOG) {
		error_log(date('Y-m-d H:i:s').':'.$msg.PHP_EOL, 3, $FAIL_LOG_PATH);
	}
}
//获取服务器的data和sign
$receipt = file_get_contents("php://input");
$receipt = json_decode($receipt,true);

setLog('someone 触发此程序数据'.var_export($receipt, 1));

if (empty($receipt['data']) && empty($receipt['sign'])) {
	setLog('不存在data和sign');
	echo 'FAILURE';
	exit();
}
//解析data
$cpId = UC_CP_ID;
$apiKey = UC_API_KEY;

//进行sign验证
$str = $cpId;
if(isset($receipt['data']['amount'])) {
  $str .= 'amount='.$receipt['data']['amount'];
}
if(isset($receipt['data']['callbackInfo'])) {
  $str .= 'callbackInfo='.$receipt['data']['callbackInfo'];
}
if(isset($receipt['data']['cpOrderId'])) {
  $str .= 'cpOrderId='.$receipt['data']['cpOrderId'];
}
if(isset($receipt['data']['failedDesc'])) {
  $str .= 'failedDesc='.$receipt['data']['failedDesc'];
}
if(isset($receipt['data']['gameId'])) {
  $str .= 'gameId='.$receipt['data']['gameId'];
}
if(isset($receipt['data']['orderId'])) {
  $str .= 'orderId='.$receipt['data']['orderId'];
}
if(isset($receipt['data']['orderStatus'])) {
  $str .= 'orderStatus='.$receipt['data']['orderStatus'];
}
if(isset($receipt['data']['payWay'])) {
    $str .= 'payWay='.$receipt['data']['payWay'];
}
if(isset($receipt['data']['serverId'])) {
    $str .= 'serverId='.$receipt['data']['serverId'];
}
if(isset($receipt['data']['ucid'])) {
    $str .= 'ucid='.$receipt['data']['ucid'];
}
$str = $str.$apiKey;
setLog('str=>'.$str);
$sign = md5($str);
//验证失败
if ($sign != $receipt['sign']) {
	setLog('sign 不对');
	echo 'FAILURE';
	exit();
}
//游戏逻辑开始
$dbconnect = include('../db_config_test.php');
$gm = new mysqli($dbconnect['gm']['host'], $dbconnect['gm']['user'], $dbconnect['gm']['pass'], $dbconnect['gm']['dbname']);
$gm->set_charset('utf8');	
//查找订单是否已经出来
$gm_query = $gm->query("SELECT `id` FROM `gm_uc` WHERE `order_id`='".$receipt['data']['orderId']."'");
$row = $gm_query->fetch_row();
setLog('订单情况：'.print_r($row, true));

if (empty($row)) {				
		//写入充值库	$orderId = server--$uid--chanal--order--game_type
		$serverMsg = explode('--', $receipt['data']['cpOrderId']);
		//serverMsg = $serverid_uid_channel_amout_订单_game
		//判断服务器是否已经配置过了
		if (empty($serverMsg[4])) {
			$game = 'ec_ucandroid';
		} else {
			$game = $serverMsg[4];
		}
		$sid = $serverMsg[1];
		if (empty($dbconnect[$game][$sid])) {
			setLog($game.'编号为'.$sid.'数据库信息没有配置', true);
			$gm_query->free();
			$gm->close();
			echo 'FAILURE';
			exit();	
		}
		//写入订单
		$sql ="INSERT INTO `gm_uc`VALUES(NULL,'{$receipt['data']['orderId']}','{$receipt['data']['gameId']}','".$receipt['data']['serverId']."',"
			."'{$receipt['data']['ucid']}','{$receipt['data']['payWay']}', '{$receipt['data']['amount']}', '{$receipt['data']['callbackInfo']}',"
			." '{$receipt['data']['cpOrderId']}', '".time()."')";
		setLog('执行sql:'.$sql);
		if(false === $gm->query($sql)){
            print_r($receipt['data']);
			setLog($receipt['data']['orderId'].'未记录到数据库中，数据库错误：'.$gm->error, true);
            print_r($sql);
            echo "<hr />";
            print_r($gm->error);
			$gm_query->free();
			$gm->close();
			echo 'FAILURE';
			exit();			
		}	
		$pay = new mysqli($dbconnect[$game][$sid]['host'], $dbconnect[$game][$sid]['user'], $dbconnect[$game][$sid]['pass'], $dbconnect[$game][$sid]['dbname']);
		$pay->set_charset('utf8');	
		//连接钱币配置表 static_pay_money
		$fee = intval($receipt['data']['amount']);
		$sql = "SELECT * FROM `static_pay_money` WHERE `sChannelID` = 'uc_android' and `nPay` = $fee";
		$money_query = $pay->query($sql);
		$money_row = $money_query->fetch_assoc();
		if (empty($money_row)) {
			$money_query->free();
			setLog('未查到充值金币'.$fee.'对应的id', true);
			$pay->close();
			$gm->close();
			echo 'SUCCESS';
			exit();
		} 
		$sql = "INSERT INTO `game_pay` VALUES(NULL, $serverMsg[1] , 0, ".$money_row['sAppleID'].", ".$money_row['nAward'].", $serverMsg[2], '{$receipt['data']['orderId']}', ".time().")";
		//订单写入充值表失败return failed
		if (false === $pay->query($sql)) {
			setLog($receipt['data']['orderId'].'记录失败,数据库报错：'.$pay->error, true);		
			$gm_query->free();
			$sql = "INSERT INTO `gm_pay_log` VALUES(NULL, '数据库操作错误".addslashes($pay->error)."', 0, '".json_encode($receipt)."',".time().", '".$game."')";
			$gm->query($sql);
		} else {
			$gm_query->free();
		    $sql = "INSERT INTO `gm_pay_log` VALUES(NULL, '订单".$receipt['data']['orderId']."操作成功', 1, '".json_encode($receipt)."',".time().", '".$game."')";
			$gm->query($sql);			
		}
		$gm->close();
		$pay->close();
	} else {
		setLog("平台订单".$CooperatorOrderSerial."已经处理过了", true);
	}
echo 'SUCCESS';
$pay->close();
$gm->close();  
unset($receipt_data);
exit();