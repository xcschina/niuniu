/**
 * Created by ong on 15/7/24.
 */
$(function (){
    ini_search();
    $("ul.index-list a").click(function(e){nav_game_tab(this);});
});
function ini_search(){

}
function nav_game_tab(obj){
    rel = $(obj).attr("rel");
    cate_type = $("input[name='cate_type']").val();
    if(rel=="hot"){
        $("ul.hotgame").show();
        $("ul.othergame").hide();
    }else{
        $("ul.hotgame").hide();
        games = $.ajax({url:"index.php?act=get_games&type="+cate_type+"&letter="+rel,async:false});
        games= jQuery.parseJSON(games.responseText);//转换为json对象
        $("ul.othergame").html("").show();
        $.each(games,function(idx,item){
            if(item.first_letter == rel){
                $("ul.othergame").append('<li><a href="/game'+item.id+'"><img src="http://static.66173.cn/'+item.game_icon+'"/><a title=\"'+item.game_name+'\" href=\"/game'+item.id+'\"><span>'+item.game_name+'</span><\/a><\/li>');
            }
        })
    }
    $("ul.index-list a").removeClass("on");
    $(obj).addClass("on");
}