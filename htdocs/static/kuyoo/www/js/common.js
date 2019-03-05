
/**** 图片浏览JS ****/
var _index = 0;

$(".pro_img_small li").click(function(){
	$(".pro_img_small li").removeClass("cur");
	$(this).addClass("cur");
	$("#bigimg").attr("src",$(this).find("img").attr("src"))
	imgStatus();
})
		
$(".icon_arrow_left").click(function(){
	if(_index != 0){
		$(".pro_img_small li:eq("+(_index-1)+")").click();
		imgStatus();
	}
})
		
$(".icon_arrow_right").click(function(){
	if(_index != $(".pro_img_small li").length-1){
		$(".pro_img_small li:eq("+(_index+1)+")").click();
		imgStatus();
	}
})
		
function imgStatus(){
	$(".icon_arrow_left,.icon_arrow_right").removeClass("disable");
	$(".pro_img_small li").each(function(i){
		if($(this).attr("class")=="cur"){
			if(i==0){ $(".icon_arrow_left").addClass("disable"); }
			if(i==$(".pro_img_small li").length-1){
				$(".icon_arrow_right").addClass("disable");
			}
			_index = i;
		}
	})
}
imgStatus();
/**** 图片浏览JS 结束 ****/

//公告轮播
jQuery(".top_news").slide({ mainCell:"ul", effect:"topLoop",  autoPlay:true, autoPage:true, trigger:"click" });	


$(".game_recharge .item").click(function(){
	$(this).parent().find(".item").removeClass("cur");
	$(this).addClass("cur")
})


function login(){
	$('#do_login').submit();
}

function register(){
	$('#register').submit();
}









