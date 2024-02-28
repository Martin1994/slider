<?php
	header("Content-type: text/html; charset=utf-8");
	include './include/conf.spwd.php';
	include './include/func.spwd.php';
	include './i.func.php';
	session_start();
	
	Get_Shifted_Keys(ALL_KEYS);
	$keys = Generate_Keyboard();
		
	include './template/spwd/index.HTML';
?>
</body>
