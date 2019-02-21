<?php
ini_set("display_errors", "on");
$param = array (
    'p1_MerId' => '10012462534',
    'r0_Cmd' => 'Buy',
    'r1_Code' => '1',
    'r2_TrxId' => '868588394432172I',
    'r3_Amt' => '300.0',
    'r4_Cur' => 'RMB',
    'r5_Pid' => '500Ԫ���5000Ԫ��',
    'r6_Order' => '820150807140235983392',
    'r7_Uid' => '',
    'r8_MP' => '820150807140235983392',
    'r9_BType' => '1',
    'ru_Trxtime' => '20150807140412',
    'ro_BankOrderId' => '2819854939150807',
    'rb_BankId' => 'CCB-NET',
    'rp_PayDate' => '20150807140402',
    'rq_CardNo' => '',
    'rq_SourceFee' => '0.0',
    'rq_TargetFee' => '1.5',
    'hmac' => 'e718cfd5f67c886721e6584179c0f225',
);

$param = urlencode(http_build_query($param));
$url = "http://charge.66173.cn/yeepay.php?".$param;
print_r(postData($param, $url));
function postData($data, $serverUrl)
{
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $serverUrl);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_POST, 0);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array());
    $result = curl_exec($ch);
    curl_close($ch);
    return $result;
}