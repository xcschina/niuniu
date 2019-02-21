<?php
class tools{

    public static function data_change($params){
        /*
         * 组合参数生成query_string
         * click_id string  广点通点击跟踪 id
         * muid   string  设备id
         * conv_time string  转化发生时间
         * client_ip  string    选填
         * value  int    选填
         */
        $query_string = 'muid='.$params['muid'].'&conv_time='.$params['conv_time'].'&click_id='.$params['click_id'];
        /*
         *利用query_string构造page
         * appid  int
         * query_string  string
         */
        $page = 'http://t.gdt.qq.com/conv/app/'.$params['appid'].'/conv?'.$query_string;
        /*
         * 将page进行urlencode得到encode_page
         * page  string
         */
        $encode_page = urlencode($page);
        /*
         * 将encode_page组装成property
         * encode_page   string
         * sign_key  string   签名密钥
         */
        $property = config::SIGNKEY.'&GET&'.$encode_page;
        /*
         * 利用property生成signature
         * property  string
         */
        $signature = md5($property);
        /*
         * 利用signature和query_string生成base_data
         * signature string
         * query_string  string
         */
        $base_data=$query_string.'&sign='.urlencode($signature);
        /*
         * 利用base_data和和encrypt_key生成data
         * base_data string
         * encrypt_key string 加密密钥
         */
        $data = urlencode(str_replace(' ','',base64_encode(self::xor_enc($base_data,config::ENCRYPTKEY))));
        return $data;
    }

    private static function xor_enc($str,$key){
        $crystr = '';
        for($i=0;$i<strlen($str);$i++){
            $crystr .= $str[$i]^$key;
        }
        return $crystr;
    }
}