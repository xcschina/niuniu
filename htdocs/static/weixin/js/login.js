/**
 * Created by ong on 15/6/26.
 */
$(document).ready(function(){
    if($("p.error").html()!=""){
        $("p.error").show();
    }
})
function get_code(){
    $("input[name='verify']").val(0);
    mobile = $("input[name='tel']").val();
    code = $("input[name='page-hash']").val();
    if(mobile.length!=11){
        $("p.error").html("请输入手机号").show();
        return false;
    }
    $("p.error").html("获取中").show();

    $.ajax({
        type:'get',
        url:'my.php?act=register_sms_wx',
        data:{mobile:mobile,code:code},
        dataType: 'json',
        success: function (json) {
            if(json.res==0){
                $("p.error").html(json.msg).show();
                return false;
            }else{
                $("input[name='verify']").val("1");
                $("p.error").html(json.msg).show();
                $("a.get-code").html("已发送").css("background","#999");
                return true;
            }
        }
    });
    return false;
}
function doMobileVerify(){
    verify = $("input[name='verify']").val();
    tel = $("input[name='tel']").val();
    vcode = $("input[name='v-code']").val();
    pwd = $("input[name='pwd']").val();
    if(tel.length!=11){
        $("p.error").html("请先验证手机号").show();
        return false;
    }
    if(vcode.length!=6){
        $("p.error").html("请填写验证码").show();
        return false;
    }
    if(pwd.length<6 || pwd.length>30){
        $("p.error").html("请填写密码").show();
        return false;
    }
    $("form[name='loginForm']").submit();
}