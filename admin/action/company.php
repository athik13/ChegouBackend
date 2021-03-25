<?php
include_once('../../common.php');

if (!isset($generalobjCompany)) {
    require_once(TPATH_CLASS . "class.general_admin.php");
    $generalobjCompany = new General_admin();
}
$date = Date('Y-m-d');
$ip = $_SERVER['REMOTE_ADDR'] ? $_SERVER['REMOTE_ADDR'] : '';

$reload = $_SERVER['REQUEST_URI']; 
$urlparts = explode('?',$reload);
$parameters = $urlparts[1];
$id = isset($_REQUEST['id']) ? $_REQUEST['id'] : '';
$iCompanyId = isset($_REQUEST['iCompanyId']) ? $_REQUEST['iCompanyId'] : '';
$status = isset($_REQUEST['status']) ? $_REQUEST['status'] : '';
$statusVal = isset($_REQUEST['statusVal']) ? $_REQUEST['statusVal'] : '';
$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : 'view';
$hdn_del_id = isset($_REQUEST['hdn_del_id']) ? $_REQUEST['hdn_del_id'] : '';
$checkbox = isset($_REQUEST['checkbox']) ? implode(',',$_REQUEST['checkbox']) : '';
$method = isset($_REQUEST['method']) ? $_REQUEST['method'] : '';
//echo "<pre>"; print_r($_REQUEST); die;
//Start make deleted
if ($method == 'delete' && $iCompanyId != '') {
    if(!$userObj->hasPermission('delete-company')) {
        $_SESSION['success'] = 3;
        $_SESSION['var_msg'] = 'You do not have permission to delete company';   
    }else{
	   if(SITE_TYPE !='Demo'){
		
             $qur1 = "UPDATE register_driver SET register_driver.iCompanyId=1 WHERE register_driver.iCompanyId=$iCompanyId ";
	         $res1 = $obj->sql_query($qur1);
     
             $qur3 = "UPDATE driver_vehicle SET driver_vehicle.iCompanyId=1 WHERE driver_vehicle.iCompanyId=$iCompanyId ";
	         $res3 = $obj->sql_query($qur3);
      
             if($res1==1)
             {
              $qur2 = "UPDATE company SET eStatus = 'Deleted' WHERE iCompanyId = '" . $iCompanyId . "'";
              $res2 = $obj->sql_query($qur2);

                /* Insert status log on user_log table*/
                $queryIn = "INSERT INTO user_status_logs SET iUserId = ".$iCompanyId.", eUserType = 'company', dDate = '".$date."', eStatus = 'Deleted', iUpdatedBy = ".$_SESSION['sess_iAdminUserId'].", vIP = '".$ip."'";
                $obj->sql_query($queryIn);
		     }
            
            $_SESSION['success'] = '1';
            $_SESSION['var_msg'] = $langage_lbl_admin['LBL_RECORD_DELETE_MSG'];   
    	} else {
            $_SESSION['success'] = '2';
        }
    }
	header("Location:".$tconfig["tsite_url_main_admin"]."company.php?".$parameters); exit;
}
//End make deleted

//Start Change single Status
if ($iCompanyId != '' && $status == 'Active') {
    if(!$userObj->hasPermission('update-status-company')) {
        $_SESSION['success'] = 3;
        $_SESSION['var_msg'] = 'You do not have permission to change status of company';     
    }else{
    	if(SITE_TYPE !='Demo'){
            $query = "UPDATE company SET eStatus = '" . $status . "' WHERE iCompanyId = '" . $iCompanyId . "'";
            $obj->sql_query($query);

            /* Insert status log on user_log table*/
            $queryIn = "INSERT INTO user_status_logs SET iUserId = ".$iCompanyId.", eUserType = 'company', dDate = '".$date."', eStatus = '".$status."', iUpdatedBy = ".$_SESSION['sess_iAdminUserId'].", vIP = '".$ip."'";
            $obj->sql_query($queryIn);
                        
            $_SESSION['success'] = '1';
            $_SESSION['var_msg'] = $langage_lbl_admin['LBL_RECORD_ACTIVATE_MSG'];
    	}
    	else{
                $_SESSION['success']=2;
    	}
    }
    header("Location:".$tconfig["tsite_url_main_admin"]."company.php?".$parameters);
    exit;
}
//End Change single Status

//Start Change single Status
if ($iCompanyId != '' && $status == 'Inactive') {
    if(!$userObj->hasPermission('update-status-company')) {
        $_SESSION['success'] = 3;
        $_SESSION['var_msg'] = 'You do not have permission to change status of company';   
    }else{
        if(SITE_TYPE !='Demo'){
            $qur1 = "UPDATE register_driver SET register_driver.iCompanyId=1 WHERE register_driver.iCompanyId=$iCompanyId ";
            $res1 = $obj->sql_query($qur1);
     
            $qur3 = "UPDATE driver_vehicle SET driver_vehicle.iCompanyId=1 WHERE driver_vehicle.iCompanyId=$iCompanyId ";
            $res3 = $obj->sql_query($qur3);
      
            if($res1==1) {
                $query = "UPDATE company SET eStatus = '" . $status . "' WHERE iCompanyId = '" . $iCompanyId . "'";
                $obj->sql_query($query);

                /* Insert status log on user_log table*/
                $queryIn = "INSERT INTO user_status_logs SET iUserId = ".$iCompanyId.", eUserType = 'company', dDate = '".$date."', eStatus = '".$status."', iUpdatedBy = ".$_SESSION['sess_iAdminUserId'].", vIP = '".$ip."'";
                $obj->sql_query($queryIn);
                                
            }
            
            $_SESSION['success'] = '1';
            $_SESSION['var_msg'] = $langage_lbl_admin['LBL_RECORD_INACTIVATE_MSG'];
            
        }
        else{
                $_SESSION['success']=2;
        }
    }
    header("Location:".$tconfig["tsite_url_main_admin"]."company.php?".$parameters);
    exit;
}
//End Change single Status

//Start Change All Selected Status
if($checkbox != "" && $statusVal != "") {
    if(!$userObj->hasPermission(['update-status-company', 'delete-company'])) {
        $_SESSION['success'] = 3;
        $_SESSION['var_msg'] = 'You do not have permission to change status of company';   
    }else{
    	if(SITE_TYPE !='Demo'){
    		 $query = "UPDATE company SET eStatus = '" . $statusVal . "' WHERE iCompanyId IN (" . $checkbox . ")";
             $obj->sql_query($query);
             
             $checkbox = explode(",", $checkbox);
             for ($i = 0; $i < count($checkbox); $i++) {
 
                 /* Insert status log on user_log table*/
                 $queryIn = "INSERT INTO user_status_logs SET iUserId = ".$checkbox[$i].", eUserType = 'company', dDate = '".$date."', eStatus = '".$statusVal."', iUpdatedBy = ".$_SESSION['sess_iAdminUserId'].", vIP = '".$ip."'";
                 $obj->sql_query($queryIn);
             }

    		 $_SESSION['success'] = '1';
    		 $_SESSION['var_msg'] = $langage_lbl_admin['LBL_Record_Updated_successfully'];
    	}
    	else{
    		$_SESSION['success']=2;
        }
	}
    header("Location:".$tconfig["tsite_url_main_admin"]."company.php?".$parameters);
    exit;
}
//End Change All Selected Status

?>