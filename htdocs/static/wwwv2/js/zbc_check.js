// ------------------------
// 数据校验 <zbc>
// ------------------------
$.extend({
	z_add_msg : function($obj,$msg){
		$obj.parent().find('.z_warning').remove();
		$obj.after('<span class="z_warning">'+$msg+'</span>');
	},
	z_del_msg : function($obj){
		$obj.parent().find('.z_warning').remove();
	},
	z_clear_msg : function(){
		$('.z_warning').remove();
	},
	z_is_empty : function($obj,$err){
		$obj.parent().find('.z_warning').remove();
		if($.trim($obj.val())==''){
			$obj.after('<span class="z_warning">'+$err+'</span>');
			return false;
		}else{
			return true;
		}
	},
	z_is_eq : function($o1,$o2,$err){
		$o2.parent().find('.z_warning').remove();
		if($o1.val() == $o2.val()){
			$o2.after('<span class="z_warning">'+$err+'</span>');
			return false;
		}else{
			return true;
		}
	},
	z_is_tel:function($phone,$obj){
		$obj.parent().find('.z_warning').remove();
		if(!RegExp(/^13[0-9]{9}$|14[0-9]{9}|15[0-9]{9}$|18[0-9]{9}$/).test($phone)){
			$obj.after('<span class="z_warning">请输入正确的手机号</span>');
			return false;
		}else{
			return true;
		}
	},
	z_is_qq:function($qq,$obj){
		$obj.parent().find('.z_warning').remove();
		if(!RegExp(/^[1-9][0-9]{4,15}$/).test($qq)){
			$obj.after('<span class="z_warning">请输入正确的QQ号</span>');
			return false;
		}else{
			return true;
		}
	},
	z_is_int:function($str){
		var isInteger = RegExp(/^[0-9]+$/);  
		return (isInteger.test($str));  
	},
	z_is_idcard:function($idcard,$obj){
		$obj.parent().find('.z_warning').remove();
		if(!RegExp(/^[1-9]\d{7}((0\d)|(1[0-2]))(([0|1|2]\d)|3[0-1])\d{3}$|^[1-9]\d{5}[1-9]\d{3}((0\d)|(1[0-2]))(([0|1|2]\d)|3[0-1])\d{3}([0-9]|X)$/).test($idcard)){
			$obj.after('<span class="z_warning">请输入正确的身份证号码</span>');
			return false;
		}else{
			return true;
		}
	}
});