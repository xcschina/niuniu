function go_pay(){
    act = $("input[name='do']").val();
    service_id = $("input[name='service_id']").val();
    channel_id = $("input[name='channel_id']").val();
    serv_id = $("#serv_id").val();
    tel = $("input[name='tel']").val();
    qq = $("input[name='qq']").val();

    if(act=='character'){
        if(!check_character()){
            return false;
        };
    }
    if(act=='topup' || act=='recharge'){
        if(!check_topup()){
            return false;
        };
    }

    if($.trim(channel_id)==''){
        var t = $("div.error").html("请选择游戏版本").show(500);
        setTimeout('$("div.error").hide();', 2000);
        return false;
    }

    if($.trim(serv_id)=='' || $.trim(serv_id)==0){
        var t = $("div.error").html("请选择服务器").show(500);
        setTimeout('$("div.error").hide();', 2000);
        $("input[name='service_id']").focus();
        return false;
    }

    if($.trim(tel)==''){
        var t = $("div.error").html("请填写联系电话").show(500);
        setTimeout('$("div.error").hide();', 2000);
        $("input[name='tel']").focus();
        return false;
    }

    if($.trim(qq)==''){
        var t = $("div.error").html("请填写联系qq").show(500);
        setTimeout('$("div.error").hide();', 2000);
        $("input[name='qq']").focus();
        return false;
    }

    $("form[name='payform']").submit();
}

//首充号
function check_character(){
    role_name = $("input[name='role_name']").val();
    role_bak_name = $("input[name='role_back_name']").val();

    if($.trim(role_name)==''){
        var t = $("div.error").html("请填写角色名").show(500);
        setTimeout('$("div.error").hide();', 2000);
        $("input[name='role_name']").focus();
        return false;
    }

    if($.trim(role_bak_name)==''){
        var t = $("div.error").html("请填写备用角色名").show(500);
        setTimeout('$("div.error").hide();', 2000);
        $("input[name='role_back_name']").focus();
        return false;
    }

    if($.trim(role_bak_name)==$.trim(role_name)){
        var t = $("div.error").html("备用角色名不能相同").show(500);
        setTimeout('$("div.error").hide();', 2000);
        $("input[name='role_back_name']").focus();
        return false;
    }
    return true;
}

//代充、首充号代充
function check_topup(){
    role_name = $("input[name='role_name']").val();
    game_user = $("input[name='game_user']").val();
    game_pwd = $("input[name='game_pwd']").val();

    if($.trim(game_user)==''){
        var t = $("div.error").html("请填写登入账号").show(500);
        setTimeout('$("div.error").hide();', 2000);
        $("input[name='game_user']").focus();
        return false;
    }

    if($.trim(game_pwd)==''){
        var t = $("div.error").html("请填写登入密码").show(500);
        setTimeout('$("div.error").hide();', 2000);
        $("input[name='game_pwd']").focus();
        return false;
    }

    if($.trim(role_name)==''){
        var t = $("div.error").html("请填写角色名").show(500);
        setTimeout('$("div.error").hide();', 2000);
        $("input[name='role_name']").focus();
        return false;
    }
    return true;
}
function slt_game_user(obj){
    gameusers = $.parseJSON(game_users);
    order_id = $(obj).val();
    serv_id = $(obj).attr("rel");
    $("input[name='serv_id']").val(0);
    $(gameusers).each(
        function(){
            if(this.id==order_id){
                $("input[name='game_user']").val(this.game_user);
                $("input[name='role_name']").val(this.role_name);
                $("input[name='serv_id']").val(this.serv_id);
            }
        }
    );
    if(order_id==0){
        $("input[name='game_user']").val('');
        $("input[name='role_name']").val('');
        $("input[name='game_pwd']").val('');
    }
}