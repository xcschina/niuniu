<?php
COMMON('nowpay_check/Services');
DAO('now_pay_id_check_dao');
class now_pay_id_check{

    public $DAO;

    public function __construct(){
        $this->DAO = new now_pay_id_check_dao();
    }

    public function check_id(){
        $id_card = "350121199012143710";
        $real_name = "陈静康";
        $res = $this->DAO->get_user_by_id_card($id_card);
        if ($res){
            if ($res['real_name'] == $real_name){
                //验证成功

            }else{
                //验证失败

            }
        }else{
            $req=array();
            $req["funcode"]=Config::CHECK_FUNCODE;
            $req["mhtOrderNo"]=date("YmdHis");
            $req["appId"]=Config::$app_id;
            $req["cardName"]=$real_name;
            $req["idcard"]=$id_card;
            $now_pay = new Services();
            $req_content = $now_pay->buildAndSendCheckReq($req);
            //判断签名是否正确
            if ('CHECK_SIGN_FAILS' == $req_content){
                //签名失败提示

            }else{
                //判断是否调用正常
                $res = str_replace(array('=','&'), array('"=>"','","'),'array("'.$req_content.'")');
                eval("\$res"." = $res;");
                if ('0000' != $res['responseCode'] || '02' == $res['status'] || '03' == $res['status']){
                    //调用接口错误

                }else{
                    if ('00' == $res['status']){
                        //匹配成功
                        $this->DAO->insert_user_id_card($id_card,$real_name,1);
                    }elseif ('01' == $res['status']){
                        //匹配失败
                        $this->DAO->insert_user_id_card($id_card,$real_name,2);
                    }
                }
            }
        }
    }
}