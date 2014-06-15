<?php 


/* return attribute of current login user */
function user($attr=null, $value=null){
	if(!isset($_SESSION['login_user'])){
		return false;
	}
	if($attr === null){
		return true;
	}
	if($value === null){	// read user info
		return $_SESSION['login_user'][$attr];
	}
	else{					//write user info
		$_SESSION['login_user'][$attr] = $value;
	}
}