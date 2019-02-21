<?php

class luoke_tools{

    static public function set_sign($params){
        ksort($params);
        return md5(str_replace(' ','',urldecode(http_build_query($params))).config::B_PRIVATEKEY);
    }
}