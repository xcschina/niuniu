// ------------------------
// 购买页 和 订单页 公用 <zbc> 
// ------------------------

var $IMAGES_PATH = 'http://cdn.66173.cn/';

var buy_left_show = function($type){
	if($type=='show'){
		$('#weixin').slideDown('slow');
		$('#recommend').slideDown('slow');
	}else{
		$('#weixin').slideUp('slow');
		$('#recommend').slideUp('slow');
	}
}

var init_choose_platform = function(){
	$('#choose_platform div').remove('.item');
	$('#android_platform').slideUp();
	$('#ios_platform').slideUp();
	$('#discount_price').html('');
}

$(function(){
	f_hide_login_box();
	$(".title_tab span").click(function(){
		var id = $(this).attr("id");
		$(".title_tab span").removeClass("cur");
		$(this).addClass("cur");
		$("#tab1_box,#tab2_box,#tab3_box").hide();
		$("#"+id+"_box").show();
	})

	/**** 图片浏览 ****/
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

});