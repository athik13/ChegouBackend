<?php
	include_once("common.php");
	unset($_SESSION['sess_iUserId']);
	unset($_SESSION["sess_iOrganizationId"]);
	unset($_SESSION["sess_vName"]);
	unset($_SESSION["sess_vEmail"]);
	unset($_SESSION["sess_user"]);
	
	unset($_SESSION['sess_iMemberId']);
	unset($_SESSION['sess_eGender']);
	unset($_SESSION['sess_vImage']);
	unset($_SESSION['fb_user']);

	if (isset($_SERVER['HTTP_COOKIE'])) {
		$cookies = explode(';', $_SERVER['HTTP_COOKIE']);
		foreach($cookies as $cookie) {
			$parts = explode('=', $cookie);
			$name = trim($parts[0]);
			setcookie($name, '', time()-1000);
			setcookie($name, '', time()-1000, '/');
		}
	}
	session_destroy();
	setcookie('login_redirect_url_user', $_SERVER['HTTP_REFERER'], time()+2*24*60*60);

	header("Location:organization_login.php?action=organization");
	exit;
?>
