<?php
class Tools{
    public static function createLinkString($para,$sort,$encode) {
        if($sort){
            $para= self::argSort($para);
        }
        $linkString="";
        foreach($para as $key => $value){
            if($encode){
                $value=  urlencode($value);
            }
            $linkString.=$key.Config::QSTRING_EQUAL.$value.Config::QSTRING_SPLIT;
        }
        $linkString=  substr($linkString, 0, count($linkString)-2);
        return $linkString;
    }

    private static function argSort($para){
        ksort($para);
        reset($para);
        return $para;
    }
}