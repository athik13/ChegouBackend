<?php
include_once('../../common.php');
if (!isset($generalobjAdmin)) {
    require_once(TPATH_CLASS . "class.general_admin.php");
    $generalobjAdmin = new General_admin();
}
$reload = $_SERVER['REQUEST_URI'];
$urlparts = explode('?', $reload);
$parameters = $urlparts[1];
$id = isset($_REQUEST['id']) ? $_REQUEST['id'] : '';
$iDonationId = isset($_REQUEST['iDonationId']) ? $_REQUEST['iDonationId'] : '';
$status = isset($_REQUEST['status']) ? $_REQUEST['status'] : '';
$statusVal = isset($_REQUEST['statusVal']) ? $_REQUEST['statusVal'] : '';
$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : 'view';
$hdn_del_id = isset($_REQUEST['hdn_del_id']) ? $_REQUEST['hdn_del_id'] : '';
$checkbox = isset($_REQUEST['checkbox']) ? implode(',', $_REQUEST['checkbox']) : '';
$method = isset($_REQUEST['method']) ? $_REQUEST['method'] : '';
// echo "<pre>"; print_r($_REQUEST);
//Start city deleted
if (($statusVal == 'Deleted' || $method == 'delete') && ($iDonationId != '' || $checkbox != "")) {
	
	      if (!$userObj->hasPermission('delete-donation')) {
        $_SESSION['success'] = 3;
        $_SESSION['var_msg'] = 'You do not have permission to delete donation';
    } else  
		{
        //Added By Hasmukh On 05-10-2018 For Solved Bug Start
        if ($iDonationId != "") {
            $iDonationIds = $iDonationId;
        } else {
            $iDonationIds = $checkbox;
        }


        //Added By Hasmukh On 05-10-2018 For Solved Bug End
        if (SITE_TYPE != 'Demo') {
             $sql = "SELECT * FROM donation WHERE iDonationId = '" . $iDonationIds . "'";
            $db_data = $obj->MySQLSelect($sql);
            $temp_order = $db_data[0]['iDisplayOrder'] ;
            $sql1 = "SELECT * FROM donation";
            $db_data1 = $obj->MySQLSelect($sql1);
            $iDisplayOrder = count($db_data1);
            if($temp_order > $iDisplayOrder) { 
                for($i = $temp_order-1; $i >= $iDisplayOrder; $i--) { 
                   $sql="UPDATE donation SET iDisplayOrder = '".($i+1)."' WHERE iDisplayOrder = '".$i."'";
                    $obj->sql_query($sql);
                }
            } else if($temp_order < $iDisplayOrder) {
                for($i = $temp_order+1; $i <= $iDisplayOrder; $i++) {
                    $sql="UPDATE donation SET iDisplayOrder = '".($i-1)."' WHERE iDisplayOrder = '".$i."'";
                    $obj->sql_query($sql);
                }
            }
            $img_path = $tconfig["tsite_upload_images_donation_path"];
            $oldImage =  $db_data[0]['vDonationImage'];
            $check_file = $img_path . '/' . $oldImage;
            if ($oldImage != '' && file_exists($check_file)) {
                @unlink($img_path . '/' . $oldImage);
            }

            // $query = "UPDATE donation SET eStatus = 'Deleted',iDisplayOrder= ".$iDisplayOrder." WHERE iDonationId IN (" . $iDonationIds . ")";
            // $obj->sql_query($query);

            $query = "Delete from donation WHERE iDonationId IN (" . $iDonationIds . ")";
            $obj->sql_query($query);
            $_SESSION['success'] = '1';
            $_SESSION['var_msg'] = $langage_lbl_admin['LBL_RECORD_DELETE_MSG'];
        } else {
            $_SESSION['success'] = '2';
        }
    }
    header("Location:" . $tconfig["tsite_url_main_admin"] . "donation.php?" . $parameters);
    exit;
}
//End Donation deleted
//Start Change single Status
if ($iDonationId != '' && $status != '') {
     if (!$userObj->hasPermission('update-status-donation')) {
        $_SESSION['success'] = 3;
        $_SESSION['var_msg'] = 'You do not have permission to change status of donation';
    } else   
		{
        if (SITE_TYPE != 'Demo') {
            $query = "UPDATE donation SET eStatus = '" . $status . "' WHERE iDonationId = '" . $iDonationId . "'";
            $obj->sql_query($query);
            $_SESSION['success'] = '1';
            if ($status == 'Active') {
                $_SESSION['var_msg'] = $langage_lbl_admin['LBL_RECORD_ACTIVATE_MSG'];
            } else {
                $_SESSION['var_msg'] = $langage_lbl_admin['LBL_RECORD_INACTIVATE_MSG'];;
            }
        } else {
            $_SESSION['success'] = 2;
        }
    }
    header("Location:" . $tconfig["tsite_url_main_admin"] . "donation.php?" . $parameters);
    exit;
}
//End Change single Status
//Start Change All Selected Status
if ($checkbox != "" && $statusVal != "") {
    if (!$userObj->hasPermission('update-status-donation')) {
        $_SESSION['success'] = 3;
        $_SESSION['var_msg'] = 'You do not have permission to change status of donation';
    } else  
	{
        if (SITE_TYPE != 'Demo') {
            $query = "UPDATE donation SET eStatus = '" . $statusVal . "' WHERE iDonationId IN (" . $checkbox . ")";
            $obj->sql_query($query);
            $_SESSION['success'] = '1';
            $_SESSION['var_msg'] = $langage_lbl_admin['LBL_Record_Updated_successfully'];
        } else {
            $_SESSION['success'] = 2;
        }
    }
    header("Location:" . $tconfig["tsite_url_main_admin"] . "donation.php?" . $parameters);
    exit;
}
//End Change All Selected Status
?>