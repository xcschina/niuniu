$(document).ready(function(){
    $("#money_id>a").click(function(){
        $("#money_id>a").removeClass("hover");
        $("input[name='money_id']").val($(this).attr("rel"));
        $(this).addClass("hover");
        if($(this).attr("data-goodname")!=''){
            $("div.tips").html($(this).attr("data-goodname")).show();
        }
    });
    $("section.pay>a").click(function(){
        $("section.pay>a").removeClass("select");
        $("input[name='mode']").val($(this).attr("rel"));
        $(this).addClass("select");
    });

    if($("input[name='pay_result']").val()>0){
        pay_result($("input[name='pay_result']"));
    }

    $("#money_id>a").each(function(){
        if($(this).attr("class") == "hover"){
            $("input[name='money_id']").val($(this).attr("rel"));
            if($(this).attr("data-goodname")!=''){
                $("div.tips").html($(this).attr("data-goodname")).show();
            }
        }
    });
});

function android_pay(){
    var goodid = $("input[name='money_id']").val();
    var mode = $("input[name='mode']").val();
    if(goodid == "" || goodid == "0"){
        alert('请选择需要充值的金额');
        return;
    }
    //window.local_obj.onpay(goodid);

    if(mode==2){
        if($("#alipay_short") == undefined || $("#alipay_short").length == 0){
            alert("请选择充值方式");
            return;
        }
        window.nsdk_obj.onpay(String(goodid));
        return;
    }else if(mode==1){
        $("form[name='pay_form']").submit();
        return;
    }
}

function ios_pay(){
    goodid = $("input[name='money_id']").val();
    if(goodid == "" || goodid == 0){
        alert('请选择需要充值的金额');
        return false;
    }

    mode = $("input[name='mode']").val();
    if(mode==2){
        $("a.sub_pay").attr("href","http://location?goodsid="+goodid);
        return true;
    }else{
        $("form[name='pay_form']").submit();
        return false;
    }
    return false;
}

function pay_result(obj){
    window.nsdk_obj.onpaywapcomplete($(obj).val());
}
