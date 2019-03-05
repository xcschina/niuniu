$(function(){
    // - 图片轮播
    var k = $(".index_slideBox .bd li").length;
    for (var i = 0; i < k; i++) {
        $(".index_slideBox .hd ul").html($(".index_slideBox .hd ul").html() + "<li></li>");
    }
    $(".index_slideBox .hd").css({"left":($(".index_slideBox").width()-$(".index_slideBox .hd").width())/2})
    jQuery(".index_slideBox").slide({
        mainCell: ".bd ul",
        effect: "leftLoop",
        autoPlay: true,
        interTime: 5000
    });

    // 类目导航
    $(".index_left_meu li").hover(function(){
        $(".index_left_box").hide();
        var id = $(this).attr("class");
        $("#"+id+"_box").show();
    },function(){
        $(".index_left_box").hide();
    });

    // 中部 - 游戏列表
    $(".a_z dd,.a_z dt").click(function(){game_tab(this);});
    function game_tab(obj){
        rel = $(obj).html();
        var $first_game_icon = $(".index_game_list .game_ico:first");
        var $last_game_icon  = $(".index_game_list .game_ico:last");
        var $game_num = 0;
        if(rel=="HOT"){
            $first_game_icon.show();
            $last_game_icon.hide();
        }else{
            $first_game_icon.hide();
            games = $.ajax({url:"index.php?act=get_games&letter="+rel,async:false});
            games= jQuery.parseJSON(games.responseText);//转换为json对象
            $last_game_icon.html("");
            if(games.length>0){
                $.each(games,function(idx,item){
                    if(item.first_letter == rel && $game_num<21){
                        $game_num++;
                        $last_game_icon.append('<dl> <dt><a target="_blank" rel="noopener noreferrer" href="/games/'+item.id+'.html"><img src="http://static.kuyoo.com/'+item.game_icon+'"/></a></dt> <dd><a target="_blank" rel="noopener noreferrer" href="/games/'+item.id+'.html">'+item.game_name+'</a></dd> </dl>');
                    }
                })
            }else{
                $last_game_icon.append('<div class="nogame_div"><span class="nogame_span">该类目下暂无游戏哦~</span></div>');
            }
            $last_game_icon.show();
        }
        $(".a_z dd, .a_z dt").removeClass("current");
        $(obj).addClass("current");
    }

    // 实时交易
    jQuery(".index_first_screen_right").slide({mainCell:"dl",autoPlay:true,effect:"topMarquee",vis:5,interTime:50,trigger:"click"});

    // 手游特卖
    jQuery(".index_sale").slide({mainCell:"ul",autoPlay:true,effect:"leftMarquee",vis:5,interTime:50,trigger:"click"});

    // 首充号验证
    $('.search_btn').on('click',function(){
        sch = $('.txt').val();
        window.location.href='http://shouyou.kuyoo.com/index.php?act=check_sch&sch='+sch;
    });

})