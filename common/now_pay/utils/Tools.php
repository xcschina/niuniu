<?php

//require_once '../conf/Config.php';
COMMON('now_pay/conf/Config');
    class Tools{
        public static function getTime($tag){
            list($usec,$sec)=explode(" ", microtime());
            $now_time=((float)$usec+(float)$sec);
            list($usec,$sec)=explode(".", $now_time);
            $date=date($tag,$usec);
            return str_replace('x', $sec, $date);
        }

        /**
         * ƴ�ӱ���
         *
         * @param Array $para
         * @param Boolean $sort
         * @param Boolean $encode
         * @return string
         */
        public static function createLinkString(Array $para,$sort,$encode) {
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