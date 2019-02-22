$(document).ready(function(){
    $("#sub_search").click(function(){check_usr_name();});
    $(".pay_money_sel>section>div>a").click(function(){check_money(this)});

    $(".pay_sub_sel>div").click(function(){pay_channel(this)});


    $("#user").keydown(function(e){
      if(e.keyCode==13){
          check_usr_name();
          return false;
      }
    });

    if($("div.money_err").html()!=""){
        $("div.money_err").show();
    }
});

function check_usr_name(){
    serv_id = $("select[name='serv_id']").val();
    usr_name = encodeURI($("input[name='user']").val());

    if($.trim(serv_id)==''){
        $("div.usr_err").html("请选择服务器").show(200);
        return false;
    }
    serv_name = $("select[name='serv_id'] option:selected").html();
    $("input[name='serv_name']").val(serv_name);
    $("input[name='serv_id']").val(serv_id);
    if($.trim(usr_name)==''){
        $("input[name='user']").focus();
        $("div.usr_err").html("请填写角色信息").show(200);
        return false;
    }
    $.ajax({
        url: "gameserv.php",
        type: "GET",
        data: {serv_id:serv_id, usr_name:usr_name },
        dataType: "json",
        beforeSend: function(){
            $("div.info_box").html("<div class='process'>查询中。。。。</div>").show();
        },success: function(result,textStatus){
            if(result == '' || result == null){
                $("div.info_box").html("游戏服务器繁忙，请联络客服。").show();
                return;
            }
            result = eval(result);
            if(parseInt(result.err_code) != 0){
                $("div.usr_err").html("没有查到用户，请重新查询。").show();
            }else{
                $("div.usr_err").html("<label>角色：</label><span>"+result.usr_name+"</span><label>&nbsp;&nbsp;&nbsp;等级：</label><span>"+result.usr_rank+"</span>").show();
                $("input[name='player_id']").val(result.player_id);
                $("input[name='usr_name']").val(result.usr_name);
                if(result.usr_id){
                    $("input[name='usr_id']").val(result.usr_id);
                }
            }
        },complete:function(){}
    });
}
//selected
function check_money(obj){
    $("input[name='money_id']").val($(obj).attr("rel"));
    $("div#money_id").each(function(i){
        $(this).removeClass("blue");
    });
    var str = $(obj).html();
    $(".pay-res>p").html(str).show(500);
    if($(obj).attr("title")!=""){
        $(".pay-res>p").html($(obj).attr("title")).show(500);
    }
    $(obj).parent().addClass("blue");
}

function pay_affirm(){
    serv_id = $("select[name='serv_id']").val();
    usr_name = encodeURI($("input[name='user']").val());
    money_id = $("input[name='money_id']").val();
    player_id =   $("input[name='player_id']").val();
    referrer =   $("input[name='referrer']").val();
    if($.trim(serv_id)==''){
        $("div.usr_err").html("请选择服务器").show(200).offset().top;
        $(window).scrollTop(t - 200);
        return false;
    }
    if($.trim(player_id)==''){
        $("div.usr_err").html("没有查到用户，请重新查询。").show(200).offset().top;
        $(window).scrollTop(t - 200);
        return false;
    }

    if($.trim(usr_name)==''){
        var t = $("div.usr_err").html("请填写角色信息").show(500).offset().top;
        $(window).scrollTop(t - 200);
        return false;
    }
    if($.trim(money_id)=='' || $.trim(money_id)==0){
        var t = $(".pay-res>p").html("请点击您需要充值的金额").show(500).offset().top;
        $(window).scrollTop(t - 200);
        return false;
    }
    $("form[name='pay_form_info']").submit();
}

function go_pay(){
    player_id = $("input[name='player_id']").val();
    money_id = $("input[name='money_id']").val();
    ch = $("input[name='ch']").val();
    bank = $("input[name='bank']").val();
    referrer =   $("input[name='referrer']").val();

    if($.trim(ch)==''){
        var t = $("div.ch_err").html("请选择支付渠道").show(500).offset().top;
        $(window).scrollTop(t - 200);
        return false;
    }

    $("form[name='pay_form_commit']").submit();

}

function pay_channel(obj){
    $("input[name='ch']").val($(obj).children('span').attr("rel"));
    //选择之后样式修改
    $(".pay_sub_sel>div").each(function(i){
        $(this).removeClass("select");
    })
    $(obj).addClass("select");
}
