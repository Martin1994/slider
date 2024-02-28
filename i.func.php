<?php

	//获取用户密码
	function get_password(){
		global $user;
		return $user;
	}
	
	//获取用户键盘数据
	function Get_Settings_KB_Style(){
		return 1;
	}
	
	//获取用户Shift按下后是否改变键盘显示内容
	function Get_Settings_Shift_Transform(){
		return true;
	}
?>