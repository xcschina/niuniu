$(document).ready(function(){
    game_id = Number($("input[name='game_id']").val());
    buy_type = Number($("input[name='buy_type']").val());
    $("ul.gver-list li").click(function(){
        $("ul.gver-list a").removeClass("on");
        select_group(this);
    });
    $("#slt-product").click(function(){
        select_products(game_id,buy_type);
    });
    //$("#is-rand-game-user a").click(function(){
    //    is_rand_game_user(this);
    //});
    select_group($("ul.group-list li a:eq(0)"));
    $("ul.group-list li a").click(function(){
        $("ul.group-list li a").removeClass("on");
        select_group(this);
    });
})

function select_group(obj){
    $(obj).addClass("on");
    group_id = $(obj).attr("rel");
    $("input[name='group_id']").val(group_id);

    $("input[name='serv_id']").val(0);
    $("a.sel-sev").html("点击选择服务器");
}
function select_servs(){
    game_id = Number($("input[name='game_id']").val());
    group_id = $("input[name='group_id']").val();
    $("input[name='serv_id']").val("0");

    $("#servs").load("/ajax/iapservs?game_id="+game_id+"&group_id="+group_id,function(responseTxt,statusTxt,xhr){
        if(statusTxt=="error"){
            $("#servs").html("<h5>加载数据失败</h5>");
        }
    }).css("display","block");
}
function select_products(game_id, buy_type){
    $("#products").load("/ajax/products?game_id="+game_id+"&buy_type="+buy_type,function(responseTxt,statusTxt,xhr){
        if(statusTxt=="error"){
            $("#servs").html("<h5>加载数据失败</h5>");
        }
    }).css("display","block");
}

function go_pay(){
    act = $("input[name='do']").val();
    channel_id = $("input[name='channel_id']").val();
    serv_id = $("input[name='serv_id']").val();
    tel = $("input[name='tel']").val();
    qq = $("input[name='qq']").val();
    $(".form-warning").removeClass("form-warning");
    role_name = $("input[name='role_name']").val();
    role_level = $("input[name='role_level']").val();
    game_user = $("input[name='game_user']").val();
    game_pwd = $("input[name='game_pwd']").val();
    group_id = $("input[name='group_id']").val();

    if($.trim(group_id)==''){
        alert("请选择版本");
        return false;
    }

    if($.trim(serv_id)=='' || $.trim(serv_id)==0){
        $("a.sel-sev").addClass("form-warning").focus();
        return false;
    }

    if($.trim(game_user)==''){
        $("input[name='game_user']").addClass("form-warning").focus();
        return false;
    }

    if($.trim(game_pwd)==''){
        $("input[name='game_pwd']").addClass("form-warning").focus();
        return false;
    }

    if($.trim(role_name)==''){
        $("input[name='role_name']").addClass("form-warning").focus();
        return false;
    }

    if($.trim(role_level)==''){
        $("input[name='role_level']").addClass("form-warning").focus();
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