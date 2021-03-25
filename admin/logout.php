<?php

	include_once("../common.php");
	$_SESSION['sess_iAdminUserId'] = "";
	$_SESSION["sess_vAdminFirstName"] = "";
	$_SESSION["sess_vAdminLastName"] = "";
	$_SESSION["sess_vAdminEmail"] = "";
	$_SESSION["current_link"] = "";
	unset($_SESSION['OrderDetails']);
    unset($_SESSION['sess_iServiceId_mr']);
    unset($_SESSION['sess_iUserId_mr']);
    unset($_SESSION["sess_iUserAddressId_mr"]);
    unset($_SESSION["sess_promoCode"]);

    unset($_SESSION["sess_vCurrency_mr"]);
    unset($_SESSION['sess_currentpage_url_mr']);
    unset($_SESSION['sess_vLatitude_mr']);
    unset($_SESSION['sess_vLongitude_mr']);
    unset($_SESSION['sess_vServiceAddress_mr']);


    unset($_SESSION["sess_vName_mr"]);
    unset($_SESSION["sess_company_mr"]);
    unset($_SESSION["sess_vEmail_mr"]);

    unset($_SESSION["sess_user_mr"]);
    unset($_SESSION['sess_userby_mr']);
    unset($_SESSION["sess_userby_id"]);
    unset($_SESSION["server_requirements_modal"]);
	
	if($_SESSION["SessionUserType"]=='hotel')
	{
		$_SESSION["SessionUserType"] = "";
		if($_SESSION["SessionRedirectUserPanel"]=="Yes") {
			$_SESSION["SessionRedirectUserPanel"] = '';
			header("location:../sign-in?type=hotel");
		} else {
			header("location:../hotel");
		}
	}else{
		//print_r($_SESSION);print_r($_SERVER); exit;
		$_SESSION['login_redirect_url'] = $_SERVER['HTTP_REFERER'];
		header("location:index.php");
	}
	exit;
?>
