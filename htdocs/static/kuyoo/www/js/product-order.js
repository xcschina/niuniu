$(document).ready(function(){
    $("#pay-way-tab li").click(function(){pay_way(this)});
    $("#ebank-content a").click(function(){bank_way(this)});
})
function pay_way(obj){
    way = $(obj).attr('data-value');
    ch = $(obj).attr('rel');
    $("#pay-way-tab li").removeClass("active");
    if(way=='ebank'){
        $("#pay-way-content").show();
    }else{
        $("#pay-way-content").hide();
    }
    $(obj).addClass("active");
    $("input[name='pay-channel']").val(ch);
}
function bank_way(obj){
    $("#ebank-content a").removeClass("active");
    $(obj).addClass("active");
    bank = $(obj).attr('rel');
    $("input[name='bank']").val(bank);
}
function go_pay(){
    pay_ch = $("input[name='pay-channel']").val();
    bank = $("input[name='bank']").val();
    if(pay_ch==2 && bank==''){
        var t = $("div.error").html("请选择网银").show(500);
        setTimeout('$("div.error").hide();', 3000);
        return false;
    }
    $("form[name='payform']").submit();
}