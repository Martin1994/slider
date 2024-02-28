<?php

	session_start();

	if(isset($_POST['method'])){
		$method = $_POST['method'];
	}else{
		$method = 'classic';
	}
	
	if($method == 'slide'){
		include './include/conf.spwd.php';
		include './i.func.php';
		if(!isset($_POST['user']) || !isset($_POST['pass']) || !isset($_SESSION[PREFIX.'token'])){
			exit('Deny.');
		}else{
			include './include/func.spwd.php';
			$user = $_POST['user'];
			$pass = $_POST['pass'];
			$password = get_password($user);
			$passcode = Encode_Password($password);
			
			$seed = $_SESSION[PREFIX.'token'];
			unset($_SESSION[PREFIX.'token']);
			$new_keys = Generate_Keyboard();
			
			$result = Check_Passcode($pass, $passcode, $seed);
			if($_POST['mode'] == 'ajax'){
				echo json_encode(Array('status' => $result?'success':'failure', 'new_keys' => $new_keys));
			}else{
				echo $result?'True':'False';
			}
		}
	}
?>