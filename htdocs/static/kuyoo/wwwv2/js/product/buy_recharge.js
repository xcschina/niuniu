// ------------------------
// 首充号续充 <zbc> 
// ------------------------

// 左侧显示
var init_left_fn = function(){
	$clen = $('.r_btn').length;
	if($clen > 4){
		$('#weixin').show();
	}else if($clen<4 && $clen>1){
		$('#recommend').show();
	}
}

$(function(){
	init_left_fn();

	// 首充号列表 - 选中
	$('.r_btn:not(:last)').on('click',function(){

		$('#weixin').slideDown('slow');
		$('#recommend').slideDown('slow');

		// 赋值
		$inputs = $('#recharge_info').find('input');
		$chars  = $(this).siblings('input');
		for (var i = 0; i < $chars.length; i++) {
			if(i != 5) $inputs.eq(i).val($chars.eq(i).val());
		};
		// 更多赋值
		$('#role_name').val($chars.eq(2).val());
		$('#game_user').val($chars.eq(3).val());

		// 显示
		$('#recharge_info').slideDown('slow');
		
	});

	// 该首充号指定的渠道折扣计算
	$('#choose_product').on('change',function(){
		$('#discount_price').empty();
		$(this).siblings('.z_warning').remove();
		$pid = parseInt($(this).val());
		if($pid){
			$ch  = $('input[name=channel_id]').val();
			$.get("/product.php",{act:'valid_discount',id:$pid,ch:$ch},function(data){
					var $ffdisprice = data['disprice'] || data['price'] || 0;
					$('#discount_price').append('折扣价： <strong class="color_red">'+$ffdisprice+'元</strong>　 原价： <s class="color_red">'+data['price']+'元</s>');
					$('#price').val(data['price']);
			},'json');
		}else{
			return $.z_is_empty($(this),'请选择要购买的商品！');
		}
	});


	// 提交表单
	$('#recharge').on('click',function(){
		if(check_recharge()){
			$('#buyfrm').attr('action','/product.php?act=order2&id=2').submit();
		}
		return false;
	});

	// 验证表单
	$qq  = $("input[name='qq']");
	$tel = $("input[name='tel']");
	$pwd = $("input[name='game_pwd']");
	$role= $("input[name='role_name']");
	$tel.on('blur',function(){
		return $.z_is_empty($(this),'请输入手机号！');
	})
	$qq.on('blur',function(){
		return $.z_is_empty($(this),'请输入QQ号！');
	})
	$pwd.on('blur',function(){
		return $.z_is_empty($(this),'请输入密码！');
	})
	$role.on('blur',function(){
		return $.z_is_empty($(this),'请输入角色名！');
	})

	function check_recharge(){
		$flag = true;
		$flag = $.z_is_empty($("#choose_product"), '请选择要购买的商品！');
		if(!$pwd.triggerHandler('blur')){
			$flag = false;
		}
		if($("input[name=role_name]").is(":not(:hidden)") && !$role.triggerHandler('blur')){
			$flag = false;
		}
		if(!$tel.triggerHandler('blur') || !$.z_is_tel($tel.val(),$tel)){
			$flag = false;
		}
		if(!$qq.triggerHandler('blur') || !$.z_is_qq($qq.val(),$qq)){
			$flag = false;
		}
		return $flag;
	}


	// ----- recharge2 -------

	// 首充号验证
	$('#do_check').on('click',function(){
		if(f_show_login_box()){ return false; }
		$gid  = $('#gid').val();
		$fpay = $.trim($(this).prev().val());
		if($fpay){
			$.z_del_msg($(this));
			$.get("/product.php",{act:'check_firstpay',id:$gid,fpay:$fpay},function($data){
				if($data.ret == 'right'){
					$('#baktip').html('验证成功，该账号为酷游首充号！');

					$('input[name=channel_id]').val($data.firstpay.ch_id);
					$('input[name=role_name]').val($data.firstpay.role_name);
					$('input[name=game_user],#game_user').val($data.firstpay.game_user);
					$('input[name=serv_id]').val($data.firstpay.serv_id); 
					$('#serv_id').val($data.firstpay.serv_name);

					$('#firstpay_check_wrong').slideUp('slow');
					$('#firstpay_check_right').slideDown('slow');
					$('#weixin').slideDown('slow');
					$('#recommend').slideDown('slow');

				}else if($data.ret == 'wrong'){
					$('#baktip').html('验证失败，不是这款游戏的酷游首充号!');
					$('#firstpay_check_right').slideUp('slow');
					$('#firstpay_check_wrong').slideDown('slow');
					$('#weixin').slideUp('slow');
					$('#recommend').slideUp('slow');
				}else if($data.ret == 'often'){
					$('#baktip').html('您的操作太频繁了，为了您的健康，请休息，休息一会吧!');
					$('#firstpay_check_right').slideUp('slow');
					$('#firstpay_check_wrong').slideDown('slow');
					$('#weixin').slideUp('slow');
					$('#recommend').slideUp('slow');
					
				}else{
					window.location.reload(true);
				}
			},'json');
		}else{
			$.z_add_msg($(this),"请输入要查询的首充号");
		}

	});



})