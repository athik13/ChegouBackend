<?php
include_once("common.php");

    $getDriverData = $obj->MySQLSelect("SELECT vImage,eStatus,vName,vLastName FROM register_driver WHERE iDriverId = '" . $_SESSION['sess_iUserId'] . "'");

    $OldImageName = $getDriverData[0]['vImage'];
    $checkEditProfileStatus = $generalobj->getEditDriverProfileStatus($getDriverData[0]['eStatus']);
   
    if($OldImageName != "" && $checkEditProfileStatus == "No") {
        //$var_msg = $langage_lbl['LBL_EDIT_PROFILE_DISABLED'];
        /* header("location:profile.php?success=0"."&var_msg=" . $var_msg);
        exit; */
        echo $var_msg = '0';
        return $var_msg;
    }
    
?>