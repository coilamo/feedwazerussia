<?php

function filter(&$value) {
  $value = htmlspecialchars($value, ENT_COMPAT, 'UTF-8');
}

function getUserParams() {
	if (isset($_COOKIE['user_id']) and isset($_COOKIE['user_hash'])) {   

		$query = "SELECT *,INET_NTOA(user_ip) FROM users WHERE user_id = '".intval($_COOKIE['user_id'])."' LIMIT 1";
		$userdata = mysql_getrow($query);

		if(($userdata['user_hash'] !== $_COOKIE['user_hash']) or ($userdata['user_id'] !== $_COOKIE['user_id']) ) {

			setcookie("user_id", "", time() - 3600*100*24*30*12, "/");
			setcookie("wmeusername", "", time() - 3600*100*24*30*12, "/");
			setcookie("user_hash", "", time() - 3600*100*24*30*12, "/");
		
			return 0; //Доступа нет, необходимо авторизоваться
		}else{
			return $userdata;

		}

	}else{
		return 0; //Доступа нет, необходимо авторизоваться
	}
}

?>
