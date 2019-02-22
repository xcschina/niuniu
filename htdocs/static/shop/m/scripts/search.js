/**
 * Created by zbc on 16/5/11.
 * shop_index.html
 */
 $(function(){
    // 首字母游戏
     $("ul.index-list a").on('click',function(){
         $("ul.index-list a").removeClass("on");
         $(this).addClass("on");
         shop_search_letter_games(this);
     });

    // 游戏模糊查找
     var keyup_timer;
     $("#search a.cancel").on('click',function(){cancel_search();});
     $("#keyword").on('focus',function(){ $("#search").show();});
     $("#keyword").on("input", function (e) {
         clearTimeout(keyup_timer);
         var keyword = $.trim($("#keyword").val());
         keyup_timer = setTimeout(function () {
             shop_search_name_games(keyword);
         }, 500);
     });

     // 查询按钮
     $('.shop-search-icon').on('click',function(){
        var key = $.trim($("#keyword").val());
        shop_search_name_games(key);
     });
 })

function cancel_search(){
    $("#search").hide();
    return false;
}

// 游戏模糊查找
var shop_search_name_games = function (keyword){
    $shop = $("input[name='shop']").val();
    $searchbox = $('#search-box');
    $.get("/index.php",{'act':'ajax_shop_search_games','keyword':keyword,'shop':$shop},function(data){
        $searchbox.empty();
        if(data.status==1){
            var $searchbox_html = '';
            $.each(data.shop_games, function(idx,shop_game){
                $searchbox_html += '<a style="width:98%;" href="http://shop.66173.cn/'+shop_game.shop_id+'-'+shop_game.game_id+'.html" class="default">'+shop_game.game_name+'</a>';
            });
            $searchbox.append($searchbox_html);
        }else{
            $searchbox.append("<a href='javascript:;'' class='default' style='width:98%;'>暂无匹配结果</a>");
        }
        $("#search").show();
    },'json');
}

// 首字母游戏
var shop_search_letter_games = function(obj){
    $rel = $(obj).attr("rel");
    $shop = $("input[name='shop']").val();
    $othergame = $("ul.othergame");
    if($rel=="hot"){
        $("ul.hotgame").show();
        $("ul.othergame").hide();
    }else{
        $("ul.hotgame").hide();
        $.get('/index.php',{'act':'ajax_shop_search_games','letter':$rel,'shop':$shop},function(data){
            $othergame.empty();
            if(data.status==1){
                var $othergame_html = '';
                $.each(data.shop_games, function(idx,shop_game){
                    $othergame_html += '<li><a href="/'+shop_game.shop_id+'-'+shop_game.game_id+'.html"><img src="http://static.66173.cn/'+shop_game.game_icon+'" title="'+shop_game.game_name+'"/><span>'+shop_game.game_name+'</span></a></li>';
                });
                $othergame.append($othergame_html);
            }else{
                $othergame.append("<div style='text-align:center;width: 100%;'>本店暂未上架首字母为 [ <span style='color:red;'>"+$rel+"</span> ] 的游戏</div>");
            }
            $othergame.show();
        },'json')
    }
}