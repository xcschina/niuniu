$(document).ready(function(){
    shop_id  = Number($("input[name='shop_id']").val());
    game_id  = Number($("input[name='game_id']").val());
    buy_type = Number($("input[name='buy_type']").val());
    $("ul.gver-list li").click(function(){
        $("ul.gver-list a").removeClass("on");
        set_price(this);
    });
    $("#slt-product").click(function(){
        select_products(game_id,buy_type);
    });
    $("#is-rand-game-user a").click(function(){
        is_rand_game_user(this);
    });
    $('.card-close-1').click(function(){$(this).prev().val('');})
})

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
    act     = $("input[name='do']").val();
    ch_id   = $("input[name='channel_id']").val();
    serv_id = $("input[name='serv_id']").val();
    tel     = $("input[name='tel']").val();
    qq      = $("input[name='qq']").val();
    $(".form-warning").removeClass("form-warning");
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
    if($.trim(ch_id)==''){
        var t = $("div.error").html("请选择游戏版本").show(500);
        setTimeout('$("div.error").hide();', 2000);
        return false;
    }
    if($.trim(serv_id)=='' || $.trim(serv_id)==0){
        $("a.sel-sev").addClass("form-warning").focus();
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
    if(!RegExp(/^13[0-9]{9}$|14[0-9]{9}|15[0-9]{9}$|18[0-9]{9}$|17[0-9]{9}$/).test(tel)){
        $("input[name='tel']").addClass("form-warning").focus();
        alert('请输入正确的手机号');
        return false;
    }
    if(!RegExp(/^[1-9][0-9]{4,15}$/).test(qq)){
        $("input[name='qq']").addClass("form-warning").focus();
        alert('请输入正确的QQ号');
        return false;
    }

    if(act=='character'){
        game_ch_serv_characters();// 重复购买首充号
    }else{
        $("form[name='payform']").submit();
    }
}

//首充号
function check_character(){
    is_rand_user  = $("input[name='is_rand_user']").val();
    role_name     = $("input[name='role_name']").val();
    role_bak_name = $("input[name='role_back_name']").val();
    is_rand_user  = $("input[name='is_rand_user']").val();
    if(is_rand_user == 1){
        $("input[name='role_name']").val('随机角色');
        return true;
    }
    if($.trim(role_name)==''){
        $("input[name='role_name']").addClass("form-warning").focus();
        return false;
    }
    if($.trim(role_bak_name)==''){
        $("input[name='role_back_name']").addClass("form-warning").focus();
        return false;
    }
    if($.trim(role_bak_name)==$.trim(role_name)){
        $("p.card3-con-txt").show();
        $("input[name='role_back_name']").addClass("form-warning").focus();
        return false;
    }
    return true;
}

//首充号续充
function check_topup(){
    role_name = $("input[name='role_name']").val();
    game_user = $("input[name='game_user']").val();
    game_pwd = $("input[name='game_pwd']").val();
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
    return true;
}



// zbc


/**
 * 首充号
 */

// 商品 ok
function select_products(game_id, buy_type){
    var $url = "/index.php?act=ajax_products&shop_id="+shop_id+"&game_id="+game_id+"&buy_type="+buy_type;
    if(buy_type == '2'){
        order = Number($("input[name='order']").val());
        $url += "&order="+order;
    }
    $("#products").load($url,function(responseTxt,statusTxt,xhr){
        if(statusTxt=="error"){
            $("#servs").html("<h5>加载数据失败</h5>");
        }
    }).css("display","block");
}

// 区服 ok
function select_servs(){
    channel_id = Number($("input[name='channel_id']").val());
    // game_id    = Number($("input[name='game_id']").val());
    $("input[name='serv_id']").val("0");
    if(channel_id==0 || channel_id==""){
        alert("请先选择渠道");
        return false;
    }
    $("#servs").load("/index.php?act=ajax_servs&shop_id="+shop_id+"&game_id="+game_id+"&ch_id="+channel_id,function(responseTxt,statusTxt,xhr){
        if(statusTxt=="error"){
            $("#servs").html("<h5>加载数据失败</h5>");
        }
    }).css("display","block");
}

// 同游戏同渠道同区服已购买过的首充号列表
function game_ch_serv_characters(){
    serv_id = Number($("input[name='serv_id']").val());
    channel_id = Number($("input[name='channel_id']").val());
    $("#game_ch_serv").load("/index.php?act=ajax_game_ch_serv_characters&shop_id="+shop_id+"&game_id="+game_id+"&ch_id="+channel_id+'&serv_id='+serv_id,function(responseTxt,statusTxt,xhr){
        if(statusTxt=="error"){
            $("#game_ch_serv").html("<h5>加载数据失败</h5>");
        }
    }).css("display","block");
}

function set_price(obj){
    discount = $(obj).attr("rel");
    price = $("input[name='stprice']").val();
    ch = $(obj).find("a").attr("rel");
    ch_name = $(obj).find("a label").text();
    $("input[name='channel_id']").val(ch);
    $("input[name='channel_name']").val(ch_name);
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

/**
 * 首充号续充
 */
