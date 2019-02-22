$(document).ready(function(){
    $("a[name='sub_search']").click(function(){check_usr_name();});
    $("ul.money_box>li").click(function(){check_money(this)});
    $("div.ch>a").click(function(){pay_channel(this)});
    $("div.ch_box>a").click(function(){set_bank(this)});
    if($("div.tips").html()!=''){
        $("div.tips").show(1000);
    }
    $("#user").keydown(function(e){
      if(e.keyCode==13){
          check_usr_name();
          return false;
      }
    });
});

function check_usr_name(){
    serv_id = $("select[name='serv_id']").val();
    usr_name = encodeURI($("input[name='user']").val());
    game_id = $("input[name='game_id']").val();

    if($.trim(serv_id)==''){
        $("div.usr_err").html("请选择服务器").show(200);
        return false;
    }
    if($.trim(usr_name)==''){
        $("div.usr_err").html("请填写角色信息").show(200);
        return false;
    }
    $.ajax({
        url: "gameserv.php",
        type: "GET",
        data: {serv_id:serv_id, usr_name:usr_name, game_id:game_id },
        dataType: "json",
        beforeSend: function(){
            $("div.usr_err").html("<div class='process'>查询中。。。。</div>").show();
        },success: function(result,textStatus){
            if(result == '' || result == null){
                $("div.usr_err").html("游戏服务器繁忙，请联络客服。").show();
                return;
            }
            result = eval(result);
            if(parseInt(result.err_code) != 0){
                $("div.usr_err").html("没有查到用户，请重新查询。").show();
            }else{
                $("div.usr_err").html("<label>角色：</label><span>"+result.usr_name+"</span><label>&nbsp;&nbsp;&nbsp;等级：</label><span>"+result.usr_rank	+"</span>").show();
                $("input[name='player_id']").val(result.player_id);
                $("input[name='usr_id']").val(result.usr_id);
                $("input[name='usr_name']").val(result.usr_name);
                if(result.app_id){
                    $("input[name='encrypt_id']").val(result.app_id);
                }
                if(result.appid){
                    $("input[name='encrypt_id']").val(result.appid);
                }
            }
        },complete:function(){}
    });
}
//selected
function check_money(obj){
	var o = $(obj).find("a");
    $("input[name='money_id']").val(o.attr("rel"));
    $("ul.money_box>li>a").removeClass("hover");
    $(o).addClass("hover");
    if($(o).attr("title")!=""){
        $("div.money_err").html($(o).attr("title")).show(500);
        return false;
    }else{
        $("div.money_err").html("").hide(500);
    }
}

function go_pay(){
    player_id = $("input[name='player_id']").val();
    money_id = $("input[name='money_id']").val();
    ch = $("input[name='ch']").val();
    bank = $("input[name='bank']").val();
    referrer = $("input[name='referrer']").val();
    if($.trim(player_id)==''){
        var t = $("div.usr_err").html("请先查询到您的账号").show(500).offset().top;
        $(window).scrollTop(t - 200);
        return false;
    }

    if($.trim(money_id)==''){
        var t = $("div.money_err").html("请点击您需要充值的金额").show(500).offset().top;
        $(window).scrollTop(t - 200);
        return false;
    }

    if($.trim(ch)==''){
        var t = $("div.ch_err").html("请选择支付渠道").show(500).offset().top;
        $(window).scrollTop(t - 200);
        return false;
    }

    if($.trim(ch)==2 && $.trim(bank)==''){
        var t = $("div.ch_err").html("请选择要支付的银行").show(500).offset().top;
        $(window).scrollTop(t - 200);
        return false;
    }

    if($.trim(ch) == 12){
        if($("input[name='remit_money']").val() =="" || $("input[name='remit_bank']").val() == "" || $("input[name='remit_usr_name']").val()=="" ||
            $("input[name='remit_time']").val()=="" || $("input[name='remit_tel']").val()==""){
            var t = $("div.ch_err").html("请填写汇款需要的信息").show(500).offset().top;
            $(window).scrollTop(t - 200);
            return false;
        }
        $("form[name='payform']").attr("target","_self");
        $("form[name='payform']").submit();
        return;
    }

    $("form[name='payform']").submit();
    var box1 = new Boxy($("div.alert").html(),{
        title: $("h2.con_title").html(), //对话框标题
        modal: true, //是否为模式窗口
        closeText: "X",   //关闭功能按钮的标题文字
        draggable: false //是否可以拖动
    });
    box1.resize(400, 200);  //设置对话框的大小
}

function pay_channel(obj){
    $("input[name='ch']").val($(obj).attr("rel"));
    $("div.ch>a").removeClass("pay_hover");
    $(obj).addClass("pay_hover");
    $("div.ch_err").html("").hide();
    if($(obj).attr("rel")==3){
        $(".ch_box").hide();
        $(".ch_box:first").show(500);
    }else if($(obj).attr("rel")==12){
        $(".ch_box").hide();
        $(".ch_box:last").show(500);
    }else if($(obj).attr("rel")==91 || $(obj).attr("rel")==92 || $(obj).attr("rel")==93){
        $("div.ch_err").html("充值卡面额大于充值金额时，请保留充值卡！用于余额充值。").show(500);
    }else{
        $(".ch_box").hide(500);
    }
}
function set_bank(obj){
    $("div.ch_box>a").removeClass("hover");
    $(obj).addClass("hover");
    $("input[name='bank']").val($(obj).attr("rel"));
}