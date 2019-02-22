$(document).ready(function(){
    game_id = Number($("input[name='game_id']").val());
    buy_type = Number($("input[name='buy_type']").val());
    game_user_id = Number($("input[name='game_user_id']").val());
    $("ul.gver-list li").click(function(){
        $("ul.gver-list a").addClass("on");
    });
    $("#slt-product").click(function(){
        select_products(game_id,buy_type,game_user_id);
    });
    $("#is-rand-game-user a").click(function(){
        is_rand_game_user(this);
    });
})
function select_servs(){
    channel_id = Number($("input[name='channel_id']").val());
    game_id = Number($("input[name='game_id']").val());
    $("input[name='serv_id']").val("0");
    if(channel_id==0 || channel_id==""){
        alert("请先选择渠道");
        return false;
    }

    $("#servs").load("/ajax/servs?game_id="+game_id+"&ch_id="+channel_id,function(responseTxt,statusTxt,xhr){
        if(statusTxt=="error"){
            $("#servs").html("<h5>加载数据失败</h5>");
        }
    }).css("display","block");
}
function select_products(game_id, buy_type){
    $("#products").load("/ajax/products?game_id="+game_id+"&buy_type="+buy_type+"&game_user_id="+game_user_id,function(responseTxt,statusTxt,xhr){
        if(statusTxt=="error"){
            $("#servs").html("<h5>加载数据失败</h5>");
        }
    }).css("display","block");
}
function set_price(obj){
    discount = $(obj).attr("rel");
    price = $("input[name='stprice']").val();
    ch = $(obj).find("a").attr("rel");
    $("input[name='channel_id']").val(ch);
    $("#dprice").html("￥"+Math.round((discount*price)/10));
    $("input[name='price']").val(Math.round((discount*price)/10));
    $(obj).find("a").addClass("on");
    serv_id = Number($(obj).find("a").attr("data-serv-id"));
    $("input[name='serv_id']").val(0);
    if(serv_id>0){
        $("input[name='serv_id']").val(serv_id);
    }
    $("a.sel-sev").html("点击选择服务器");
}
function is_rand_game_user(obj){
    $("#is-rand-game-user a").removeClass("on");
    rel = $(obj).attr("rel");
    $(obj).addClass("on");
    $("input[name='is_rand_user']").val(rel);
    if(rel==1){
        $(".rand-user").hide();
    }else{
        $(".rand-user").show();
    }
}


function go_pay(){
    act = $("input[name='do']").val();
    channel_id = $("input[name='channel_id']").val();
    serv_id = $("input[name='serv_id']").val();
    tel = $("input[name='tel']").val();
    qq = $("input[name='qq']").val();
    $(".form-warning").removeClass("form-warning");

    role_name = $("input[name='role_name']").val();
    game_user = $("input[name='game_user']").val();
    game_pwd = $("input[name='game_pwd']").val();

    if($.trim(game_user)==''){
        $("h3.user").addClass("form-warning").focus();
        $("html,body").animate({scrollTop:$("h3.user").offset().top},1000);
        return false;
    }

    if($.trim(role_name)==''){
        $("h3.user").addClass("form-warning").focus();
        $("html,body").animate({scrollTop:$("h3.user").offset().top},1000);
        return false;
    }

    if($.trim(game_pwd)==''){
        $("input[name='game_pwd']").addClass("form-warning").focus();
        return false;
    }

    if($.trim(channel_id)==''){
        $("h3.user").addClass("form-warning").focus();
        $("html,body").animate({scrollTop:$("h3.user").offset().top},1000);
        return false;
    }

    if($.trim(serv_id)=='' || $.trim(serv_id)==0){
        $("h3.user").addClass("form-warning").focus();
        $("html,body").animate({scrollTop:$("h3.user").offset().top},1000);
        return false;
    }

    if($.trim(tel)==''){
        $("input[name='tel']").addClass("form-warning").focus();
        return false;
    }

    if($.trim(qq)==''){
        $("input[name='qq']").addClass("form-warning").focus();
        return false;
    }

    $("form[name='payform']").submit();
}