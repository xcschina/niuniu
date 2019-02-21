<?php

class tools{

    static public function set_sign($params){
        ksort($params);
        return md5(str_replace(' ','',urldecode(http_build_query($params))).config::PRIVATEKEY);
    }
}