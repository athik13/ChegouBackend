<?php include_once("common.php");
include('assets/libraries/configuration.php');
if(isset($serviceCategoriesTmp) && !empty($serviceCategoriesTmp)){
$service_categories = $serviceCategoriesTmp;
}else{
$service_categories = array();
}
	
if($_POST)
{

	if(isset($_POST['from_long']) && !empty($_POST['from_long'])){

		if(isset($_SESSION['sess_iUserId']) && !empty($_SESSION['sess_iUserId']) && $_SESSION['sess_user']=='rider'){
		$_SESSION['sess_iUserId_mr'] = $_SESSION['sess_iUserId'];
		$_SESSION["sess_vName_mr"] = $_SESSION['sess_vName'];
		$_SESSION["sess_company_mr"] = "";
		$_SESSION["sess_vEmail_mr"] = $_SESSION['sess_vEmail'];
		$_SESSION["sess_iUserAddressId_mr"] = "";
		$_SESSION["sess_user_mr"] = "rider";
		//$_SESSION["sess_vCurrency_mr"] = $Data['vCurrencyPassenger'];
		$maildata['EMAIL_mr'] = $_SESSION["sess_vEmail_mr"];
		$maildata['NAME_mr'] = $_SESSION["sess_vName_mr"];
		$_SESSION["sess_vLongitude_mr"] = $_POST['from_long'];
		$_SESSION["sess_vLatitude_mr"] = $_POST['from_lat'];
		}else{

		$_SESSION['sess_iUserId_mr'] = '';
		$_SESSION["sess_iUserAddressId_mr"] = '';
		$_SESSION["sess_vLongitude_mr"] = $_POST['from_long'];
		$_SESSION["sess_vLatitude_mr"] = $_POST['from_lat'];
		$_SESSION['sess_iServiceName_mr'] =  $service_categories[($_SESSION['sess_iServiceId_mr']-1)]['vServiceName'];
		}
		$_SESSION["sess_vServiceAddress_mr"] = $_POST['vServiceAddress'];
		if(isset($_SESSION['sess_iServiceId_mr']) && !empty($_SESSION['sess_iServiceId_mr'] &&  $_SESSION['sess_page_name']!='service_listing')){
			header("Location:restaurant_listing.php");	
		}else{
		header("Location:user_info.php");
		}
		
	}else{
		header("Location:user_info.php");
		exit;
	}		
					
}
?>
