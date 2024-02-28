<?php
	
	function Get_Token(){
		$KBstyle = Get_Settings_KB_Style();
		$token = ($KBstyle==0) ? mt_rand(0,0x40000000)/0x40000000 : 0;
		$_SESSION[PREFIX.'token'] = $token;
		return $token;
	}
	
	function Encode_Password($pass){
		//Remove the repeat words and add shift
		if(!strcon(strtolower(ALL_KEYS), substr($pass, 0, 1)) && !strcon(ALL_SKEYS, substr($pass, 0, 1))){
			return false;
		}
		if(strcon(ALL_SKEYS, substr($pass, 0, 1))){
			$password = 's';
		}else{
			$password = '';
		}
		$password .= strtoupper(substr($pass, 0, 1));
		$shift = false;
		for($i=0; $i<strlen($pass)-1; $i++){
			if(substr($pass, $i, 1) != substr($pass, $i + 1, 1)){
				if(!strcon(strtolower(ALL_KEYS), substr($pass, $i + 1, 1)) && !strcon(ALL_SKEYS, substr($pass, $i + 1, 1))){
					return false;
				}
				if(strcon(ALL_SKEYS, substr($pass, $i, 1)) != strcon(ALL_SKEYS, substr($pass, $i + 1, 1))){
					$password .= 's';
				}
				$password .= strtoupper(substr($pass, $i + 1, 1));
			}
		}
		
		return $password;
	}
	
	function Generate_Keyboard(){
		$seed = Get_Token();
		$key_order = Generate_Key_Order($seed);
		$keys = Render_Keyboard($key_order);
		return $keys;
	}
	
	function Generate_Key_Order($seed){
		
		if($seed == 0){
				$key_order = ALL_KEYS;
		}else{
			$key_used = Array();
			$key_order = '';
			$key_num = strlen(ALL_KEYS);
			for($i=0; $i<$key_num; $i++){
				do{
					$ckey = intval(randomize($seed)*$key_num);
					$seed++;
				}while(isset($key_used[$ckey]));
				$key_used[$ckey] = true;
				$key_order .= substr(ALL_KEYS, $ckey, 1);
			}
		}
		unset($key_used);
		
		return $key_order;
	}
	
	function Get_Shifted_Keys($key_order){
		if(Get_Settings_Shift_Transform()){
			$skey_order = '';
			for($i=0; $i<strlen($key_order); $i++){
				$skey_order .= substr(ALL_SKEYS, strpos(ALL_KEYS, substr($key_order, $i, 1)), 1);
			}
		}else{
			$skey_order = ALL_KEYS;
		}
		return $skey_order;
	}
	
	function Check_Passcode($pass, $passcode, $seed){
		$key_order = Generate_Key_Order($seed);
		
		//Calculate Location
		$ckey = 0;
		$key_row = Array();
		$key_col = Array();
		
		for($col=0; $col<LINE0_LEN; $col++){
			$key_row[substr($key_order, $ckey, 1)] = 0;
			$key_col[substr($key_order, $ckey, 1)] = $col;
			$ckey++;
		}
		
		for($col=0; $col<LINE1_LEN; $col++){
			$key_row[substr($key_order, $ckey, 1)] = 1;
			$key_col[substr($key_order, $ckey, 1)] = $col;
			$ckey++;
		}
		
		for($col=0; $col<LINE2_LEN; $col++){
			$key_row[substr($key_order, $ckey, 1)] = 2;
			$key_col[substr($key_order, $ckey, 1)] = $col;
			$ckey++;
		}
		
		for($col=0; $col<LINE3_LEN; $col++){
			$key_row[substr($key_order, $ckey, 1)] = 3;
			$key_col[substr($key_order, $ckey, 1)] = $col;
			$ckey++;
		}
		
		$ck = '';	//Current Key
		$tk = '';	//Target Key
		$pk = '';	//Previous Key
		$len = 0;	//Finished Words
		$wrong_token = 0;
		$caps_locked = false;
		$caps_should_locked = false;
		for($i=0; $i<=strlen($pass)-1; $i++){
			if($ck == ''){
				
			//First Key
				if(substr($pass, $i, 1) == 's'){
					$caps_locked = !$caps_locked;
					continue;
				}
				$ck = substr($pass, $i, 1);
				for($caps_should_locked = !$caps_should_locked, $tk = 's'; $tk == 's'; $len++, $caps_should_locked = !$caps_should_locked){
					if($len > strlen($passcode) - 1){
						return false;
					}else{
						$tk = substr($passcode, $len, 1);
					}
				}
				if($ck == $tk && $caps_locked == $caps_should_locked){
					//First key is correct.
					for($caps_should_locked = !$caps_should_locked, $tk = 's'; $tk == 's'; $len++, $caps_should_locked = !$caps_should_locked){
						if($len > strlen($passcode) - 1){
							return false;
						}else{
							$tk = substr($passcode, $len, 1);
						}
					}
					continue;
				}else{
					//First key is incorrect;
					return false;
				}
				
			}else if($i == strlen($pass) - 1){
				
			//Last Key
				if($len == strlen($passcode) || strlen($passcode) == 1){
					$ck = substr($pass, $i, 1);
					$tk = substr($passcode, -1, 1);
					if($tk == $ck && $caps_locked == $caps_should_locked){
						//All right
						return true;
					}else{
						//Last key is incorrect
						return false;
					}
				}else{
					//Length wrong
					return false;
				}
			}else{
			
			//Other Key
				//Judge if the mistake is too big
				if($wrong_token >= 2){
					return false;
				}
				//Deal with shift
				if(substr($pass, $i, 1) == 's'){
					$caps_locked = !$caps_locked;
					continue;
				}
				
				$pk = $ck;
				$ck = substr($pass, $i, 1);
				
				if($ck == $tk && $caps_locked == $caps_should_locked){
					//Arrive the target
					for($caps_should_locked = !$caps_should_locked, $tk = 's'; $tk == 's'; $len++, $caps_should_locked = !$caps_should_locked){
						if($len > strlen($passcode) - 1){
							return false;
						}else{
							$tk = substr($passcode, $len, 1);
						}
					}
					continue;
				}else	if($key_row[$pk] == $key_row[$tk]){
					//Same Line
					if($key_row[$pk] == $key_row[$ck]){
						//Line is correct
						if(($key_col[$pk] > $key_col[$ck]) == ($key_col[$pk] > $key_col[$tk])){
							//Direction is correct
							if($wrong_token > 0){
								$wrong_token --;
							}
							continue;
						}else{
							//Direction is incorrect
							$wrong_token += 2;
							continue;
						}
					}else{
						//Line is incorrect
						$wrong_token += 3;
						continue;
					}
				}else if($key_row[$pk] > $key_row[$tk]){
					//Goes Up
					if($key_row[$pk] >= $key_row[$ck]){
						//Line is correct
						if(($key_col[$pk] < $key_col[$ck]) == ($key_col[$pk] < $key_col[$tk])){
							//Direction is correct
							if($wrong_token > 0){
								$wrong_token --;
							}
							continue;
						}else{
							//Direction is incorrect
							$wrong_token ++;
							continue;
						}
					}else{
						//Line is incorrect
						$wrong_token += 3;
						continue;
					}
				}else if($key_row[$pk] < $key_row[$tk]){
					//Goes Down
					if($key_row[$pk] <= $key_row[$ck]){
						//Line is correct
						if(($key_col[$pk] <= $key_col[$ck]) == ($key_col[$pk] <= $key_col[$tk])){
							//Direction is correct
							if($wrong_token > 0){
								$wrong_token --;
							}
							continue;
						}else{
							//Direction is incorrect
							$wrong_token ++;
							continue;
						}
					}else{
						//Line is incorrect
						$wrong_token += 3;
						continue;
					}
				}
			}
			
		}
	}
	
	function Render_Keyboard($key_order){
		$keys = '';
		$ckey = 0;
		$skey_order = Get_Shifted_Keys($key_order);
		
		$keys .= '<div class="l0">';
		for($i=0; $i<LINE0_LEN; $i++){
			$keys .= Render_Key(substr($key_order, $ckey, 1), substr($skey_order, $ckey, 1));
			$ckey++;
		}
		$keys .= '</div>';
		
		$keys .= '<div class="l1">';
		for($i=0; $i<LINE1_LEN; $i++){
			$keys .= Render_Key(substr($key_order, $ckey, 1), substr($skey_order, $ckey, 1));
			$ckey++;
		}
		$keys .= '</div>';
		
		$keys .= '<div class="l2">';
		for($i=0; $i<LINE2_LEN; $i++){
			$keys .= Render_Key(substr($key_order, $ckey, 1), substr($skey_order, $ckey, 1));
			$ckey++;
		}
		$keys .= '</div>';
		
		$keys .= '<div class="l3">';
		for($i=0; $i<LINE3_LEN; $i++){
			$keys .= Render_Key(substr($key_order, $ckey, 1), substr($skey_order, $ckey, 1));
			$ckey++;
		}
		$keys .= '</div>';
		return $keys;
	}
	
	function Render_Key($key_content, $skey_content){
		if($skey_content == '"'){
			$skey_content = "'".$skey_content."'";
		}else{
			$skey_content = '"'.$skey_content.'"';
		}
		return '<div class="key" key="'.$key_content.'" skey='.$skey_content.' unselectable="on" onselectstart="return false;">'.$key_content.'</div>';
	}
	
	/*
	 *随机数生成
	 *0x1000000容量的随机数生成，平均值约为0.4655
	 *param $seed 随机数种子
	 *return double
	*/
	function randomize($seed){
		return intval((RND_A*$seed + RND_B) % 0x1000000) / 0x1000000;
	}
	
	function strcon($string, $find, $start = 0){
		return strpos($string, $find, $start) !== false;
	}
?>