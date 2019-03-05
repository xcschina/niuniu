// ------------------------
// 代充<zbc> 
// ------------------------

$(function(){

	// 苹果代充 - 提交表单
	$('#applepay').on('click',function(){
		if(check_character()){
			$('#buyfrm').attr('action','/product.php?act=order2&id=8').submit();
		}
		return false;
	});

	// 代充 - 提交表单
	$('#helppay').on('click',function(){
		if(check_character()){
			$('#buyfrm').attr('action','/product.php?act=order2&id=3').submit();
		}
		return false;
	});

	// 验证表单
	$role_name     = $("input[name=role_name]");
	$choose_server = $('#choose_server');
	$tel = $("input[name=tel]");
	$qq  = $("input[name=qq]");

	$role_name.on('blur',function(){
		return $.z_is_empty($(this),'请填写角色名！');
	});

	$tel.on('blur',function(){
		return $.z_is_empty($(this),'请输入手机号！');
	});
	$qq.on('blur',function(){
		return $.z_is_empty($(this),'请输入QQ号！');
	});
	$choose_server.on('change',function(){
		return $.z_is_empty($(this),'请选择服务器！');
	});

	$user = $('input[name=game_user]');
	$pwd  = $('input[name=game_pwd]');
	$user.on('blur',function(){
		return $.z_is_empty($(this),'请输入账号！');
	})
	$pwd.on('blur',function(){
		return $.z_is_empty($(this),'请输入密码！');
	})


	function check_character(){
		$flag = true;
		if(!$role_name.triggerHandler('blur')){
			$flag = false;
		}
		if(!$user.triggerHandler('blur')){
			$flag = false;
		}
		if(!$pwd.triggerHandler('blur')){
			$flag = false;
		}
		if(!$tel.triggerHandler('blur')){
			$flag = false;
		}
		if(!$qq.triggerHandler('blur')){
			$flag = false;
		}
		if(!$choose_server.triggerHandler('change')){
			$flag = false;
		}
		if(!$.z_is_tel($tel.val(),$tel)){
			$flag = false;
		}
		if(!$.z_is_qq($qq.val(),$qq)){
			$flag = false;
		}
		return $flag;
	}

	// ----- 同首充号
	// 平台选择
	$('#choose_product').on('change',function(){
		if(f_show_login_box($uu1)){ return false; }
		init_choose_platform();
		buy_left_show('hide');
		$('#account_info').slideUp('slow');
		$('#contact').slideUp('slow');
		// 赠
		$subtitle = $(this).find('option:selected').attr('data-subtitle');
		if($subtitle){
			$('#product_sub_title').html('<span class="flag">赠</span><span>'+$subtitle+'</span>');
		}else{
			$('#product_sub_title').html('');
			$('#account_info').slideUp();
			$('#contact').slideUp();
		}

		// 商品渠道
		var $pid = $(this).val();
		if($pid > 0){
			$.get("/product.php",{act:'valid_discount',id:$pid},function(data){
				// 渠道
				if(data.channels){
					for(var i in data.channels){
						$datai = data['channels'][i];
						if($datai['platform']==1){
							$('#android_platform').append('<div class="item" data-discount='+$datai['discount']+' data-price='+data['product']['price']+' data-ch='+$datai['id']+'> <div class="img"><img src="'+$IMAGES_PATH+$datai['icon']+'" /><span>'+$datai['channel_name']+'</span></div> <span class="num">'+$datai['discount']+'折</span> </div>');
						}else if($datai['platform']==2){
							$('#ios_platform').append('<div class="item" data-discount='+$datai['discount']+' data-price='+data['product']['price']+' data-ch='+$datai['id']+'> <div class="img"><img src="'+$IMAGES_PATH+$datai['icon']+'" /><span>'+$datai['channel_name']+'</span></div> <span class="num">'+$datai['discount']+'折</span> </div>');
						}
					}
					if($('#android_platform .item').length>0){
						$('#android_platform').slideDown();
					}
					if($('#ios_platform .item').length>0){
						$('#ios_platform').slideDown();
					}
					$('#choose_platform .item').on('click',function(){
						$('#choose_platform .item').removeClass('cur');
						$(this).addClass('cur');
						$ch = $(this).attr('data-ch');
						selected_platform_fn($('#gid').val(),$ch);
						$('#channel_id').val($ch);
					});
				}
			},'json');
		}
	});


	// 选中渠道 [对应服务器列表]
	var selected_platform_fn = function($gid,$ch){
		buy_left_show('show');
		$('.z_warning').remove();

		// 折扣价
		$discount = $('#choose_platform .cur').attr('data-discount');
		$price    = $('#choose_platform .cur').attr('data-price');
		$disprice = Math.round($price*$discount/10);
		$('#discount_price').html('折扣价：<strong class="color_red">'+$disprice+'元</strong>　原价：<s class="color_red">'+$price+'元</s>');
		$('#price').val($price);
		$('#stprice').val($price);

		// 服务器列表
		$('#account_info').slideDown('slow');
		$('#contact').slideDown('slow');
		$.get("/product.php",{act:'channel_servs',id:$gid,ch:$ch},function(data){
			$('#choose_server').empty();
			if(data.length>0){
				$options = '<option value="">点击选择服务器</option>';
				for(var j in data){
					$options += '<option value="'+data[j]['id']+'">'+data[j]['serv_name']+'</option>';
				}
			}else{
				$options = '<option value="">暂无可选服务器</option>';
			}
			$('#choose_server').append($options);
		},'json');
	}





})