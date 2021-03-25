<?php 
//echo $tconfig["tsite_url"];
if(!empty($_SESSION['sess_user']) && $_SESSION['sess_user']=='rider' && !empty($_SESSION['sess_iUserId'])){

    $sess_iUserId = $_SESSION['sess_iUserId'];
    $sql = "SELECT COUNT(iUserId) AS Total,eStatus FROM register_user WHERE eStatus != 'Deleted' AND eStatus !=  'Inactive' AND
    iUserId=".$sess_iUserId; 
    $data = $obj->MySQLSelect($sql);    	
    $checkuser = $data[0]['Total'];
    $eStatusUser = $data[0]['eStatus'];
    if($checkuser <=0){

    session_start();
    if($eStatusUser=='Deleted'){
     echo $_SESSION['checkadminmsg'] = $langage_lbl['LBL_ACCOUNT_NOT_ACTIVE_ERROR_MSG'];  
    }else{
        echo $_SESSION['checkadminmsg'] = $langage_lbl['LBL_ACCOUNT_NOT_ACTIVE_ERROR_MSG'];  
    }

    unset($_SESSION['sess_iUserId']);

    unset($_SESSION["sess_iCompanyId"]);
    unset($_SESSION["sess_vName"]);
    unset($_SESSION["sess_vEmail"]);
    unset($_SESSION["sess_user"]);
    unset($_SESSION['sess_iMemberId']);
    unset($_SESSION['sess_eGender']);
    unset($_SESSION['sess_vImage']);
    unset($_SESSION['fb_user']);

    unset($_SESSION['linkedin_user']);
    unset($_SESSION['oauth_access_token']);
    unset($_SESSION['oauth_verifier']);
    unset($_SESSION['requestToken']);
    unset($_SESSION['sess_currentpage_url_ub']);
    $_SESSION['sess_currentpage_url_ub'] = "";
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
    if (isset($_SERVER['HTTP_COOKIE'])) {
        $cookies = explode(';', $_SERVER['HTTP_COOKIE']);
        foreach($cookies as $cookie) {
            $parts = explode('=', $cookie);
            $name = trim($parts[0]);
            setcookie($name, '', time()-1000);
            setcookie($name, '', time()-1000, '/');
        }
    }

    
    if(isset($_REQUEST['depart']) && $_REQUEST['depart'] == 'mobi') {
    	
        header("Location:mobi");
    }else {
        header("Location:$url");
        exit;
    }
    exit;
   } 

}else if(!empty($_SESSION['sess_user']) && $_SESSION['sess_user']=='driver' && !empty($_SESSION['sess_iUserId'])){

    $sess_iDriverId = $_SESSION['sess_iUserId'];
    $sql = "SELECT COUNT(iDriverId) AS Total,eStatus FROM register_driver WHERE eStatus != 'Deleted' AND eStatus !=  'inactive' AND
    eStatus !=  'Suspend' AND iDriverId =".$sess_iDriverId; 
    $data = $obj->MySQLSelect($sql);
    $checkdriver = $data[0]['Total'];
    $eStatusdriver = $data[0]['eStatus'];
    if($checkdriver <=0){
    session_start();
    unset($_SESSION['sess_iUserId']);
    unset($_SESSION["sess_iCompanyId"]);
    unset($_SESSION["sess_vName"]);
    unset($_SESSION["sess_vEmail"]);
    unset($_SESSION["sess_user"]);
    unset($_SESSION['sess_iMemberId']);
    unset($_SESSION['sess_eGender']);
    unset($_SESSION['sess_vImage']);
    unset($_SESSION['fb_user']);
   	unset($_SESSION['linkedin_user']);
   	unset($_SESSION['oauth_access_token']);
   	unset($_SESSION['oauth_verifier']);
   	unset($_SESSION['requestToken']);
   	unset($_SESSION['sess_currentpage_url_ub']);
   	$_SESSION['sess_currentpage_url_ub'] = "";
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

     if($eStatusdriver=='Deleted'){
     	$_SESSION['checkadminmsg'] = $langage_lbl['LBL_ACCOUNT_NOT_ACTIVE_ERROR_MSG'];  
    }else if($eStatusdriver=='Suspend'){
     	$_SESSION['checkadminmsg'] = $langage_lbl['LBL_ACCOUNT_NOT_ACTIVE_ERROR_MSG'];  
    }else{
     	$_SESSION['checkadminmsg'] = $langage_lbl['LBL_ACCOUNT_NOT_ACTIVE_ERROR_MSG'];   
    }
    if(isset($_REQUEST['depart']) && $_REQUEST['depart'] == 'mobi') {
        header("Location:mobi");
    }else {
        header("Location:provider-login");
    }
    exit;
   } 
}else if(!empty($_SESSION['sess_user']) && $_SESSION['sess_user']=='company' && !empty($_SESSION['sess_iCompanyId'])){

    $sess_iCompanyId = $_SESSION['sess_iCompanyId'];
    $sql = "SELECT COUNT(iCompanyId) AS Total,eStatus FROM company WHERE eStatus != 'Deleted' AND eStatus != 'Inactive' AND
    iCompanyId=".$sess_iCompanyId; 
    $data = $obj->MySQLSelect($sql);
    $checkcompany = $data[0]['Total'];
    $eStatuscompany = $data[0]['eStatus'];
    if($checkcompany <=0){
    session_start();
    unset($_SESSION['sess_iUserId']);
    unset($_SESSION["sess_iCompanyId"]);
    unset($_SESSION["sess_vName"]);
    unset($_SESSION["sess_vEmail"]);
    unset($_SESSION["sess_user"]);
    unset($_SESSION['sess_iMemberId']);
    unset($_SESSION['sess_eGender']);
    unset($_SESSION['sess_vImage']);
    unset($_SESSION['fb_user']);
    unset($_SESSION['linkedin_user']);
    unset($_SESSION['oauth_access_token']);
    unset($_SESSION['oauth_verifier']);
    unset($_SESSION['requestToken']);
    unset($_SESSION['sess_currentpage_url_ub']);
    $_SESSION['sess_currentpage_url_ub'] = "";
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

     if($eStatusdriver=='Deleted'){
     	$_SESSION['checkadminmsg'] = $langage_lbl['LBL_ACCOUNT_NOT_ACTIVE_ERROR_MSG'];  
    }else{
     	$_SESSION['checkadminmsg'] = $langage_lbl['LBL_ACCOUNT_NOT_ACTIVE_ERROR_MSG'];   
    }
    if(isset($_REQUEST['depart']) && $_REQUEST['depart'] == 'mobi') {
        header("Location:mobi");
    }else {
        header("Location:company-login");
    }
    exit;
   } 
}else if(!empty($_SESSION['sess_user']) && $_SESSION['sess_user']=='organization' && !empty($_SESSION['sess_iOrganizationId'])){

    $sess_iOrganizationId = $_SESSION['sess_iOrganizationId'];
    $sql = "SELECT COUNT(iOrganizationId) AS Total,eStatus FROM organization WHERE eStatus != 'Deleted' AND eStatus != 'Inactive' AND
    iOrganizationId=".$sess_iOrganizationId; 
    $data = $obj->MySQLSelect($sql);
    $checkorganization = $data[0]['Total'];
    $eStatusorganization = $data[0]['eStatus'];
if($checkorganization <=0){
    session_start();
    unset($_SESSION['sess_iUserId']);
    unset($_SESSION["sess_iCompanyId"]);
    unset($_SESSION["sess_vName"]);
    unset($_SESSION["sess_vEmail"]);
    unset($_SESSION["sess_user"]);
    unset($_SESSION['sess_iMemberId']);
    unset($_SESSION['sess_eGender']);
    unset($_SESSION['sess_vImage']);
    unset($_SESSION['fb_user']);
   	unset($_SESSION['linkedin_user']);
   	unset($_SESSION['oauth_access_token']);
   	unset($_SESSION['oauth_verifier']);
   	unset($_SESSION['requestToken']);
   	unset($_SESSION['sess_currentpage_url_ub']);
   	$_SESSION['sess_currentpage_url_ub'] = "";
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

    if($eStatusdriver=='Deleted'){
     	$_SESSION['checkadminmsg'] = $langage_lbl['LBL_ACCOUNT_NOT_ACTIVE_ERROR_MSG'];  
    }else{
     	$_SESSION['checkadminmsg'] = $langage_lbl['LBL_ACCOUNT_NOT_ACTIVE_ERROR_MSG'];   
    }
    
    if(isset($_REQUEST['depart']) && $_REQUEST['depart'] == 'mobi') {
        header("Location:mobi");
    }else {
        header("Location:organization-login");
    }
    exit;
   } 
}

if(isset($_SESSION['sess_iAdminUserId']) && !empty($_SESSION['sess_iAdminUserId'])){
    $iAdminUserId = $_SESSION['sess_iAdminUserId'];
    $sql = "";
    $sql = "SELECT COUNT(iAdminId) AS Total,eStatus FROM administrators WHERE eStatus != 'Deleted' AND eStatus !=  'Inactive' AND iAdminId=".$iAdminUserId;
    $data = $obj->MySQLSelect($sql);    
    $checkadmin = $data[0]['Total'];
    $eStatus = $data[0]['eStatus'];
    if($checkadmin <=0){
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
        if($eStatus=='Deleted'){
         $_SESSION['checkadminmsg'] = 'Your account has been deleted.Please contact administrator to activate your account.';  
        }else{
            $_SESSION['checkadminmsg'] = 'Your account has been disabled.Please contact administrator to activate your account.';   
        }
        if($_SESSION["SessionUserType"]=='hotel')
        {
            $_SESSION["SessionUserType"] = "";   
            header("location:../hotel");
        }else{   
            header("location:index.php");
        }
     }   
}
?>