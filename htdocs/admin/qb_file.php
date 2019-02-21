<?php
COMMON('adminBaseCore','pageCore','uploadHelper','QQMailer','PHPExcel');
BO('business_inside_admin');
DAO('business_inside_dao');
$filePath = "qb/test1.xlsx";
//建立reader对象
$PHPReader = new PHPExcel_Reader_Excel2007();
if(!$PHPReader->canRead($filePath)){
    $PHPReader = new PHPExcel_Reader_Excel5();
    if(!$PHPReader->canRead($filePath)){
        echo 'no Excel';
        return ;
    }}