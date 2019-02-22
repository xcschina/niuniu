/**
 * Created by ong on 15/12/3.
 */
$(document).ready(
    function(){
        if($("div.error").find("li").length>0){
            $("div.error").show();
        }
        $("#overdue a").click(function(){
            set_overdue(this);
        });
        $("#storage a").click(function(){
            set_storage(this);
        });
        $("#unit a").click(function(){
            set_unit(this);
        });

        //代币销售
        $("input[name='pernum'],input[name='price'],input[name='stock']").blur(
            function(){set_coin_price();}
        );
    }
)
function select_servs(){
    game_id = Number($("input[name='game_id']").val());
    $("input[name='serv_id']").val("0");

    $("#servs").load("/ajax/servs?game_id="+game_id,function(responseTxt,statusTxt,xhr){
        if(statusTxt=="error"){
            $("#servs").html("<h5>加载数据失败</h5>");
        }
    }).css("display","block");
}
function do_account(){
    $('body,html').animate({scrollTop:0},1000);
    game_id = Number($("input[name='game_id']").val());
    title = $("input[name='title']").val();
    ch = Number($("select[name='ch']").val());
    desc = $("textarea[name='desc']").val();
    price = $("input[name='price']").val();

    if(game_id<1){
        $("div.error").html("请选择游戏").show().focus();
        return false;
    }

    if(ch<1){
        $("div.error").html("请选择渠道").show().focus();
        return false;
    }

    if(title.length<5 || title.length>50){
        $("div.error").html("请认真填写标题, 5 - 50字").show().focus();
        return false;
    }

    if(desc.length>300 || desc.length<5){
        $("div.error").html("请认真填写商品描述,5 - 300字").show().focus();
        return false;
    }

    if(isNaN(price) || price.length>5 || price==''){
        $("div.error").html("请认真填写价格,不支持小数,1-5位").show().focus();
        return false;
    }

    $("#form").submit();
}
function do_props(){
    $('body,html').animate({scrollTop:0},1000);
    game_id = Number($("input[name='game_id']").val());
    title = $("input[name='title']").val();
    ch = Number($("select[name='ch']").val());
    desc = $("textarea[name='desc']").val();
    price = $("input[name='price']").val();
    stock = $("input[name='stock']").val();
    game_storage = $("input[name='game_storage']").val();

    if(game_id<1){
        $("div.error").html("请选择游戏").show().focus();
        return false;
    }

    if(ch<1){
        $("div.error").html("请选择渠道").show().focus();
        return false;
    }

    if(title.length<5 || title.length>50){
        $("div.error").html("请认真填写标题, 5 - 50字").show().focus();
        return false;
    }

    if(desc.length>300 || desc.length<5){
        $("div.error").html("请认真填写商品描述,5 - 300字").show().focus();
        return false;
    }

    if(game_storage.length>100 || game_storage.length<1){
        $("div.error").html("请选择商品存放位置").show().focus();
        return false;
    }

    if(isNaN(price) || price.length>5 || price==''){
        $("div.error").html("请认真填写价格,不支持小数,1-5位").show().focus();
        return false;
    }

    if(isNaN(stock) || stock.length>5 || stock==''){
        $("div.error").html("请认真填写需要出售的件数").show().focus();
        return false;
    }
    var re_type="^[0-9]*[1-9][0-9]*$";
    var re = new RegExp(re_type);
    if(stock.match(re)==null){
        $("div.error").html("请认真填写需要出售的件数").show().focus();
        return false;
    }

    $("#form").submit();
}
function do_coin(){
    $('body,html').animate({scrollTop:0},1000);
    game_id = Number($("input[name='game_id']").val());
    title = $("input[name='title']").val();
    ch = Number($("select[name='ch']").val());
    desc = $("input[name='desc']").val();
    price = $("input[name='price']").val();
    pernum = $("input[name='pernum']").val();
    stock = $("input[name='stock']").val();
    unit = $("input[name='unit']").val();

    if(game_id<1){
        $("div.error").html("请选择游戏").show().focus();
        return false;
    }
    if(ch<1){
        $("div.error").html("请选择渠道").show().focus();
        return false;
    }

    if(unit<1){
        $("div.error").html("请选择游戏币类型").show().focus();
        return false;
    }
    if(title.length<5 || desc.length<5){
        $("div.error").html("请认真商品价格和数量").show().focus();
        return false;
    }
    if(isNaN(price) || price.length>5 || price==''){
        $("div.error").html("请认真填写价格,不支持小数,1-5位").show().focus();
        return false;
    }
    if(isNaN(stock) || stock.length>5 || stock==''){
        $("div.error").html("请认真填写需要出售的件数").show().focus();
        return false;
    }
    if(isNaN(pernum) || pernum.length>5 || pernum==''){
        $("div.error").html("请认真填写需要出售的件数").show().focus();
        return false;
    }
    var re_type="^[0-9]*[1-9][0-9]*$";
    var re = new RegExp(re_type);
    if(stock.match(re)==null||pernum.match(re)==null){
        $("div.error").html("请认真填写商品数量和总件数").show().focus();
        return false;
    }

    $("#form").submit();
}
function do_publish(){
    $("div.error").html("").hide();
    $('body,html').animate({scrollTop:0},1000);
    game_id = Number($("input[name='game_id']").val());
    serv_id = Number($("input[name='serv_id']").val());
    game_user = $("input[name='game_user']").val();
    game_pwd = $("input[name='game_pwd']").val();
    game_pwd_again = $("input[name='game_pwd_again']").val();
    role_name = $("input[name='role_name']").val();
    role_level = $("input[name='role_level']").val();
    game_user_lock = $("input[name='game_user_lock']").val();
    tel = $("input[name='tel']").val();
    qq = $("input[name='qq']").val();
    overdue = Number($("input[name='overdue']").val());

    if(game_id<1){
        $("div.error").html("请选择游戏").show().focus();
        return false;
    }

    if(serv_id<1){
        $("div.error").html("请选择区服").show();
        return false;
    }

    if(game_user.length<1 || game_user.length>50){
        $("div.error").html("请正确填写游戏账号").show().focus();
        return false;
    }

    if(game_pwd.length<1 || game_pwd.length>30){
        $("div.error").html("请正确填写游戏密码").show().focus();
        return false;
    }

    if(game_pwd_again!=game_pwd){
        $("div.error").html("游戏密码俩次填写不一致").show().focus();
        return false;
    }

    if(role_name<1 || role_name.length>30){
        $("div.error").html("请正确填写游戏角色名").show().focus();
        return false;
    }

    if(role_level.length<1 || role_level.length>5){
        $("div.error").html("请正确填写游戏角色等级").show().focus();
        return false;
    }

    if($.trim(tel)==''){
        $("div.error").html("请正确填写联系电话").show().focus();
        return false;
    }

    if($.trim(qq)==''){
        $("div.error").html("请正确填写联系QQ").show().focus();
        return false;
    }

    if(isNaN(overdue) || overdue.length>5 || overdue==0 || overdue==''){
        $("div.error").html("请选择发布有效期").show().focus();
        return false;
    }

    $("#form").submit();
}
function set_overdue(obj){
    $("#overdue a").removeClass("on");
    overdue = $(obj).attr("rel");
    $("input[name='overdue']").val(overdue);
    $(obj).addClass("on");
}
function set_storage(obj){
    $("#storage a").removeClass("on");
    storage = $(obj).attr("rel");
    $("input[name='game_storage']").val(storage);
    $(obj).addClass("on");
}
function set_unit(obj){
    $("#unit a").removeClass("on");
    unit = $(obj).attr("rel");
    $("input[name='unit']").val(unit);
    $(obj).addClass("on");
    set_coin_price();
}
function set_coin_price(){
    if($("input[name='do']").val()!='coin'){
        return;
    }
    pernum = Number($("input[name='pernum']").val());
    price = Number($("input[name='price']").val());
    stock = Number($("input[name='stock']").val());
    unit = $("input[name='unit']").val();
    $("#unitprice").html("1元="+1/price*pernum+" "+unit);
    $("#gross").html(stock*pernum);

    $("input[name='title'],input[name='desc']").val(price+"元="+pernum+unit+"("+pernum+unit+"="+price+"元)");
}