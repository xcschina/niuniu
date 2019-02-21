<?php
class formCheck {
    //是否为空
    function is_empty($str){
        $qian=array(" ","　","\t","\n","\r");$hou=array("","","","","");
        $str=str_replace($qian,$hou,$str);
        if($str==null || $str=="") return true;
        return false;
    }
    //检验email
    function is_email($str){
        return preg_match("/^\w+([-+.]\w+)*@\w+([-.]\w+)*\.\w+([-.]\w+)*$/", $str)== 1 ? true : false;
    }
    //检验网址
    function is_url($str){
        return preg_match("/^http:\/\/[A-Za-z0-9]+\.[A-Za-z0-9]+[\/=\?%\-&_~`@[\]\':+!]*([^<>\"])*$/", $str)== 1 ? true : false;
    }

    //检验qq
    function is_qq($str){
        return preg_match("/^[1-9]\d{4,8}$/", $str)== 1 ? true : false;
    }

    //检验邮编
    function is_zip($str){
        return preg_match("/^[1-9]\d{5}$/", $str)== 1 ? true : false;
    }

    //检验身份证
    function  is_idcard($str){
        $city = array(
            '11','12','13','14','15','21','22',
            '23','31','32','33','34','35','36',
            '37','41','42','43','44','45','46',
            '50','51','52','53','54','61','62',
            '63','64','65','71','81','82','91'
        );

        if (!preg_match('/^([\d]{17}[xX\d]|[\d]{15})$/', $str)) return false;
        if (!in_array(substr($str, 0, 2), $city)) return false;
        $str = preg_replace('/[xX]$/i', 'a', $str);
        $length = strlen($str);
        if ($length == 18){
            $birthday = substr($str, 6, 4) . '-' . substr($str, 10, 2) . '-' . substr($str, 12, 2);
        } else {
            $birthday = '19' . substr($str, 6, 2) . '-' . substr($str, 8, 2) . '-' . substr($str, 10, 2);
        }

        if (date('Y-m-d', strtotime($birthday)) != $birthday) return false;
        if ($length == 18){
            $sum = 0;
            for ($i = 17 ; $i >= 0 ; $i--){
                $subStr = substr($str, 17 - $i, 1);
                $sum += (pow(2, $i) % 11) * (($subStr == 'a') ? 10 : intval($subStr , 11));
            }
            if($sum % 11 != 1) return false;
        }
        return true;
    }

    //检验是否是中文
    function is_chinese($str){
        //return ereg("^[".chr(0xa1)."-".chr(0xff)."]+$",$str);
        return preg_match("/^[\x{4e00}-\x{9fa5}]+$/u",$str)==1 ? true : false;
        //eregi("[^\x80-\xff]","$str");
       // return preg_match("/^[\x7f-\xff]+$/", $str)==1 ? true : false;
    }

    //检验是否是英文
    function is_english($str){
        return preg_match("/^[A-Za-z]+$/", $str)== 1 ? true : false;
    }

    public function is_mobile($str){
        return preg_match('/^13[0-9]{1}[0-9]{8}$|15[012356789]{1}[0-9]{8}$|18[0-9]{9}$|14[57]{1}[0-9]{8}$|17[03678]{1}[0-9]{8}$/', $str) == 1 ? true : false;
    }

    function is_phone($str){
        return preg_match("/^((\(\d{3}\))|(\d{3}\-))?(\(0\d{2,3}\)|0\d{2,3}-)?[1-9]\d{6,7}$/", $str)== 1 ? true : false;
    }

    function is_safe($str){
        return (preg_match("/^(([A-Z]*|[a-z]*|\d*|[-_\~!@#\$%\^&\*\.\(\)\[\]\{\}<>\?\\\/\'\"]*)|.{0,5})$|\s/", $str) != 0)== 1 ? true : false;
    }
}