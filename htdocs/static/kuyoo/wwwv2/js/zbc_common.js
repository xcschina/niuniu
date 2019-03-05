$(function(){
    var keyup_timer;
    $("#keyword").click(function(e){
        show_keyword_search(this, e);
    });
    // 每次输入的事件
    $("#keyword").on("input", function () {
        clearTimeout(keyup_timer);
        var keyword = $.trim($("#keyword").val());
        // 延时请求
        keyup_timer = setTimeout(function () {
            keyword_search(keyword);
        }, 300);
    });
    keyword_search = function (keyword, callback) {
        games = $.ajax({url:"http://shouyou.kuyoo.com/index.php?act=keyword&keyword="+encodeURIComponent(keyword),async:false});
        games= jQuery.parseJSON(games.responseText);//转换为json对象
        $("ul.keyword-inner").html("");
        $.each(games,function(idx,item){
            if(idx>9){
                return false;
            }
            $("ul.keyword-inner").append('<li><a href="\/games/'+item.id+'.html"><img src="http:\/\/static.kuyoo.com\/'+item.game_icon+'" \/>'+item.game_name+'<\/a><\/li>');
        });
        if(games.length > 0){
            $('#keyword_box').show();
        }else{
            $('#keyword_box').hide();
        }
    };
    function show_keyword_search(obj,e){
        $(document).click(function(e){
            if(!(e.target == $('#keyword_box') || $.contains($('#keyword_box'), e.target))) {
                $('#keyword_box').hide();
            }
        });
    }
    //------- 顶部搜索 结束

    // 返回顶部 开关
    $(window).scroll(function(){
        if($(window).scrollTop()>200){
            $('.goto_top').slideDown();
        }else{
            $('.goto_top').slideUp();
        }
    });

    //活动喇叭
    jQuery(".top_news").slide({ mainCell:"ul", effect:"topLoop",  autoPlay:true, autoPage:true, trigger:"click" });

    // 首充号
    $('#search_btn').on('click',function(){
        var $sch = $.trim($(this).prev().val());
        if($sch != ''){
            $.post("/index.php?act=check_sch_do",{sch:$sch},function(data){
                switch(data['ret']){
                    case 'often': $msg = '您的操作太频繁了，为了您的健康，休息一下下吧~~'; break;
                    case 'right': window.location.href = data['url']; break;
                    case 'wrong': 
                    default: $msg = '验证失败，该账号不是酷游首充号！如果输入错误请重新输入！';break;
                }
                $('#res_msg').html($msg);
            },'json');
        }else{ $('#res_msg').html('验证失败，该账号不是酷游首充号！如果输入错误请重新输入！'); }
    });

    // 购买页VIP图
    jQuery(".pro_img_big").slide({ mainCell:"ul",autoPlay:false, effect:"left", vis:4, scroll:4, autoPage:true, pnLoop:false });
});