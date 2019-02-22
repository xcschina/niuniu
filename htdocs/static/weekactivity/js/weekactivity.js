$(document).ready(function(){
    shop_id  = Number($("input[name='shop_id']").val());
    game_id  = Number($("input[name='game_id']").val());
    buy_type = Number($("input[name='buy_type']").val());
    $("#slt-product").click(function(){
        select_products(game_id,buy_type);
    });
    $("#is-rand-game-user a").click(function(){
        is_rand_game_user(this);
    });
    $('.card-close-1').click(function(){$(this).prev().val('');})

    $(".close_down").click(function(){
        $(".down_alert").hide();
        $(".alert_box").hide();
        $(".alert_box_2").hide();
        $(".black").hide();
    });
    $(".guanbi").click(function(){
        $('.tc_bg,.login_box').hide();
    });
    $(".desc_img").mouseover(function(){
        $(this).children(".desc_content").show();
    }).mouseout(function(){
        $(this).children(".desc_content").hide();
    });
    $('.ljgm').click(function(){
        $(".gver-list").html("");
        var old_price = $(this).attr("price");
        var new_price = $(this).attr("nprice");
        var game_id = $(this).attr("info");
        var user_id = $("input[name='user_id']").val();
        var id = $(this).attr("id");
        if(!user_id){
            $('.tc_bg,.login_box').show();
            return false;
        }
        $("input[name='game_id']").val(game_id);
        $("input[name='price']").val(old_price);
        $("input[name='stprice']").val(new_price);
        $.ajax({
            type:'post',
            url:'/weekactivity.php?act=ajax_buy',
            data:{
                game_id:game_id,
                id:id
            },
            dataType: 'json',
            success: function (json) {
                if(json.msg == 0){
                    alert(json.desc);
                    return false;
                }else if(json.msg == 1){
                    if(json.android_chs != null){
                        $.each(json.android_chs,function(key,value){
                            $(".and").append(
                                "<li rel='"+value.discount+"'>"+
                                "<a href='#;' rel='"+value.id+"'>"+ "<span><img src='http://cdn.66173.cn/"+value.icon+"'/><label>"+value.channel_name+"</label></span>"+
                                "<em></em>"+
                                "</a>"+
                                "</li>"
                            );
                        })
                    }else{
                        $(".and").append(
                            "<span style='padding:15px 80px;background:#F0F0F0;margin-left:10px;line-height:70px'>敬请期待！！！</span>"
                        );
                    }
                    if(json.ios_chs != null){
                        $.each(json.ios_chs,function(key,value){
                            $(".iosd").append(
                                "<li rel='"+value.discount+"'>"+
                                "<a href='#;' rel='"+value.id+"'>"+ "<span><img src='http://cdn.66173.cn/"+value.icon+"'/><label>"+value.channel_name+"</label></span>"+
                                "<em></em>"+
                                "</a>"+
                                "</li>"
                            );
                        });
                    }else{
                        $(".iosd").append(
                            "<span style='padding:15px 80px;background:#F0F0F0;margin-left:10px;line-height:70px'>敬请期待！！！</span>"
                        );
                    }
                    $(".ser_ids_num").remove();
                    $("ul.gver-list li").on("click",function(){
                        $("ul.gver-list a").removeClass("on");
                        set_price(this);
                        channel_id =  $("input[name='channel_id']").val();
                        $.get("/product.php",{act:'channel_servs',id:game_id,ch:channel_id},function(data){
                            $('#choose_server').empty();
                            if(data.length>0){
                                $options = '<option value="">点击选择游戏区服</option>';
                                for(var j in data){
                                    $options += '<option class="ser_ids_num" value="'+data[j]['id']+'">'+data[j]['serv_name']+'</option>';
                                }
                            }else{
                                $options = '<option value="">暂无可选服务器</option>';
                            }
                            $('#choose_server').append($options);
                        },'json');
                    });
                    $(".remove_box").remove();
                    $.each(json.attr,function(key,value){
                        $(".buy_box").append(
                            "<div class='card-info-box remove_box'>"+
                            "<label><i>*</i>"+key+"：</label>"+
                            "<select name='attr[]' id='"+key+"' class='attr_select'>"+
                            "</select>"+
                            "</div>"
                        );

                        $.each(value,function(k,v){
                            $("#"+key).append(
                                "<option value='"+k+":"+v+"'>"+v+"</option>+"
                            )
                        })
                    });

                    $("input[name='game_id']").val(json.product.game_id);
                    $("input[name='price']").val(json.product.new_price);
                    $("#payform").attr("action","/weekactivity.php?act=topay&id="+json.product.id);
                    $("#product_id").val(json.product.id);
                    $("#pid").val(json.product.id);
                    $(".alert_box .alert_nav span").html(json.product.game_name+"购买页");
                    var scrollTop=document.body.scrollTop - 500;
                    $(".alert_box").attr("style","margin-top:"+scrollTop+"px");
                    $(".black").show();
                    $(".alert_box").show();
                }

            },
            error:function(){
            }
        });
    });
    $('.yjxz').click(function(){
        var game_id = $(this).attr("info");
        $("input[name='game_id']").val(game_id);
        $(".down_alt").remove();
        $.ajax({
            type:'post',
            url:'/weekactivity.php?act=ajax_down',
            data:{
                game_id:game_id
            },
            dataType: 'json',
            success: function (json) {
                if(json.android == null){
                    $(".down_an").append("<a class='item down_alt'> <span >敬请期待...</span> </a>");
                }else{
                    $.each(json.android,function(key,value){
                        $(".down_an").append("<a class='item down_alt' id='games' href='"+value.url+"'>"+ value.channel_name+"<br/>点击下载"+
                            "<div class='smallcode'>"+
                            "<div class='code'>"+
                            "<i></i>"+
                            "<img src='/game"+value.game_id+"/qr"+value.id+"'/>扫一扫下载到手机"+
                            "</div>"+
                            "<img src='/game"+value.game_id+"/qr"+value.id+"' />"+
                            "</div>"+
                            "</a>");
                    });
                }
                if(json.ios == null){
                    $(".down_ios").append("<a class='item down_alt'> <span >敬请期待...</span> </a>");
                }else{
                    $.each(json.ios,function(key,value){
                        $(".down_ios").append("<a class='item down_alt' id='games' href='"+value.url+"'>"+ value.channel_name+"<br/>点击下载"+
                            "<div class='smallcode'>"+
                            "<div class='code'>"+
                            "<i></i>"+
                            "<img src='/game"+value.game_id+"/qr"+value.id+"'/>扫一扫下载到手机"+
                            "</div>"+
                            "<img src='/game"+value.game_id+"/qr"+value.id+"' />"+
                            "</div>"+
                            "</a>");

                    })
                }
                $(".down_alert .alert_nav span").html(json.game.game_name+"下载地址");
                $(".black").show();
                $(".down_alert").show();
            },
            error:function(){
            }
        });
    });


    $(".cksp").click(function(){
        var order_id = $(this).attr("info");
        $.ajax({
            type:'post',
            url:'/weekactivity.php?act=ajax_order_info',
            data:{
                order_id:order_id
            },
            dataType: 'json',
            success: function (json) {
                $(".dd_bh").html(json.order_id);
                if(json.status == 1){
                    $(".dd_zt").html("已付款");
                }else if(json.status == 2){
                    $(".dd_zt").html("交易成功");
                }
                $(".em1").html(json.title);
                $(".em2").html(json.unit_price);
                $(".em3").html(json.serv_name);
                $(".em4").html(json.channel_name);
                $(".em5").html(json.game_name);
                $(".em6").html(json.amount);
                $(".em7").html(json.role_name);
                $(".em8").html(json.role_back_name);
                $(".alert_box_2 .alert_nav span").html(json.game_name);
                $(".black").show();
                $(".alert_box_2").show();
            },
            error:function(){
            }
        });
    })

    // ------------------------
// 订单付款 <zbc>
// ------------------------
    $(function(){
        $(".form li .item").click(function(){
            $(this).parent().find(".item").removeClass("cur");
            $(this).addClass("cur");
        })

        $("#pay_other").click(function(){
            if($("input[name='pay_other']").attr("checked")=="checked"){
                $("#li_other").show();
            }else{
                $("#li_other").hide();
            }
        })

        $("#pay_balance").click(function(){
            if($("input[name='pay_balance']").attr("checked")=="checked"){
                $("#span_pay,#span_balance_pay").html($("#span_balance").html());
            }else{
                $("#span_pay,#span_balance_pay").html("0 元");
            }
        })

        // 支付方式
        $('.pay_method_tab li').on('click',function(){
            $pc = parseInt($(this).attr('data-pc'));
            $('#pay_channel').val($pc);
            if($pc!=2||$pc!=5){
                $.z_del_msg($(this));
            }
            $(this).addClass("cur").siblings('li').removeClass("cur");
            $(".pay_method_box").hide();
            $("#"+$(this).attr("lang")).show();
            $("#method_name").html($(this).html()+"：")
        });

        // 银行选择
        $('.banklist img').on('click',function(){
            $checkbox = $(this).parent().prev();
            $bank_code = $checkbox.prop('checked',true);// 强制选中
            $bank_code = $checkbox.attr('data-code');
            $('#bank').val($bank_code);
        });
    })
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
    serv_id = $("select[name='serv_id']").val();
    tel     = $("input[name='tel']").val();
    qq      = $("input[name='qq']").val();
    $(".form-warning").removeClass("form-warning");
    if(check_character() == false){
        return false;
    }
    if($.trim(ch_id)=='' || $.trim(ch_id)==0){
        var t = $("div.error").html("请选择游戏版本").show(500);
        alert("请选择游戏版本");
        setTimeout('$("div.error").hide();', 2000);
        return false;
    }
    if($.trim(serv_id)=='' || $.trim(serv_id)==0){
        $("a.sel-sev").addClass("form-warning").focus();
        alert("选择游戏区服");
        return false;
    }
    if($.trim(tel)==''){
        $("input[name='tel']").addClass("form-warning").focus();
        alert("请填写手机号码");
        return false;
    }
    if($.trim(qq)==''){
        $("input[name='qq']").addClass("form-warning").focus();
        alert("请填写QQ号码");
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
    $pay_ch = $("#pay_channel");
    $lastch = $('.pay_method_tab li:last');

    // 过滤网银支付代码
    if($pay_ch.val()==2){
        $selected_bank = $('input[name=radio_bank]:checked');
        if($selected_bank.length==0){
            $.z_add_msg($lastch,'请选择网银');
            return false;
        }else{
            // 兼容IE
            $bank_code = $selected_bank.attr('data-code');
            $('#bank').val($bank_code);
        }
    }
    $('#payform').submit();
}

//首充号
function check_character(){
    is_rand_user  = $("input[name='is_rand_user']").val();
    role_name     = $("input[name='role_name']").val();
    role_bak_name = $("input[name='role_back_name']").val();
    is_rand_user  = $("input[name='is_rand_user']").val();
    if(is_rand_user == 1){
        $("input[name='role_name']").val('');
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
