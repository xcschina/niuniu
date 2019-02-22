$(function(){
	f_show_login_box();

	// 验证表单
	$qq   = $("input[name='qq']");
	$tel  = $("input[name='tel']");
	$role = $("input[name='role_name']");
	$serv = $("#choose_serv");

	$tel.on('blur',function(){
		return $.z_is_empty($(this),'请输入手机号！');
	})
	$qq.on('blur',function(){
		return $.z_is_empty($(this),'请输入QQ号！');
	})
	$role.on('blur',function(){
		return $.z_is_empty($(this),'请输入角色名！');
	})
	$serv.on('change',function(){
		return $.z_is_empty($(this),'请选择区服！');
	})

	function check_gamecoin(){
		$flag = true;
		if(!$role.triggerHandler('blur')){
			$flag = false;
		}
		if(!$tel.triggerHandler('blur') || !$.z_is_tel($tel.val(),$tel)){
			$flag = false;
		}
		if(!$qq.triggerHandler('blur') || !$.z_is_qq($qq.val(),$qq)){
			$flag = false;
		}
		if(!$.z_is_empty($serv,'请选择区服！')){
			$flag = false;
		}
		return $flag;
	}

	// 提交表单
	$('#gamecoin').on('click',function(){
		if(f_show_login_box()){ return false; }
		if(check_gamecoin()){
			$('#buyfrm').attr('action','/product.php?act=order2&id=5').submit();
		}
		return false;
	});

	$('#choose_serv').on('change',function(){
		if(f_show_login_box()){ $(this).val(''); return false; }
	});
})