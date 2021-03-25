<?php

include_once('common.php');
$iCompanyId = isset($_REQUEST['iCompanyId']) ? $_REQUEST['iCompanyId'] : '';
$iOrganizationId = isset($_REQUEST['iOrganizationId']) ? $_REQUEST['iOrganizationId'] : '';
$iDriverId = isset($_REQUEST['iDriverId']) ? $_REQUEST['iDriverId'] : '';
$iUserId = isset($_REQUEST['iUserId']) ? $_REQUEST['iUserId'] : '';
$usertype = isset($_REQUEST['usertype']) ? $_REQUEST['usertype'] : '';
$eSystem = "";

if ($iCompanyId != '') {
    $ssql = " AND iCompanyId !='" . $iCompanyId . "'";
} else if ($iOrganizationId != '') {
    $ssql = " AND iOrganizationId !='" . $iOrganizationId . "'";
} else if ($iDriverId != "") {
    $ssql = " AND iDriverId !='" . $iDriverId . "'";
} else {
    $ssql = " ";
}


if(!empty($_REQUEST['phone'])) {
    $_REQUEST['vPhone'] = $_REQUEST['phone'];
}

if ($usertype == 'company' && isset($_REQUEST['vPhone'])) {
    $vPhone = $_REQUEST['vPhone'];
    $vCountry = $_REQUEST['vCountry'];
    if($generalobj->checkXThemOn() == 'Yes') {
        $eSystem = "General";
    } else {
        $eSystem = "";    
    }
    
    $checEmailExist = $generalobj->checkMemberDataInfo($vPhone, "", 'COMPANY', $vCountry, $iCompanyId,$eSystem); //Added By HJ On 12-09-2019
    if ($checEmailExist['status'] == 2) {
        echo 'deleted';
        $messge = "LBL_MOBILE_EXIST";
    } else if ($checEmailExist['status'] == 0) {
        echo 'false';
        $messge = "LBL_INVALID_MEMBER_USER_COUNTRY_EMAIL_TXT";
    } else {
        echo 'true';
    }
    
    /*$ssql .= " AND eSystem = '".$eSystem."'";
    $sql1 = "SELECT count('vPhone') as Total,eStatus FROM company WHERE vPhone = '" . $vPhone . "'" . $ssql;
    $db_comp = $obj->MySQLSelect($sql1);
    if ($db_comp[0]['Total'] > 0) {
        if ((ucfirst($db_comp[0]['eStatus']) == 'Deleted') || (ucfirst($db_comp[0]['eStatus']) == 'Inactive')) {
            echo 'deleted';
        } else {
            echo 'false';
        }
        //echo 'false';
    } else {
        echo 'true';
    }*/
}
/* Use For Organization */
if ($usertype == 'organization' && isset($_REQUEST['vPhone'])) {
    $vPhone = $_REQUEST['vPhone'];
    $vCountry = $_REQUEST['vCountry'];
    $checEmailExist = $generalobj->checkMemberDataInfo($vPhone, "", 'ORGANIZATION', $vCountry, $iOrganizationId,$eSystem); //Added By HJ On 12-09-2019
    if ($checEmailExist['status'] == 2) {
        echo 'false';
        $messge = "LBL_MOBILE_EXIST";
    } else if ($checEmailExist['status'] == 0) {
        echo 'false';
        $messge = "LBL_INVALID_MEMBER_USER_COUNTRY_EMAIL_TXT";
    } else {
        echo 'true';
    }
    /* $sql1 = "SELECT count('vPhone') as Total,eStatus FROM organization WHERE vPhone = '" . $vPhone . "'" . $ssql;
      $db_comp = $obj->MySQLSelect($sql1);
      if ($db_comp[0]['Total'] > 0) {
      if ((ucfirst($db_comp[0]['eStatus']) == 'Deleted') || (ucfirst($db_comp[0]['eStatus']) == 'Inactive')) {
      echo 'deleted';
      } else {
      echo 'false';
      }
      //echo 'false'; 
      } else {
      echo 'true';
      } */
}

/* Use For Organization */
if ($usertype == 'driver' && isset($_REQUEST['vPhone'])) {
    $vPhone = $_REQUEST['vPhone'];
    $vCountry = $_REQUEST['vCountry'];
    $eSystem = "";

    
    $checEmailExist = $generalobj->checkMemberDataInfo($vPhone, "", 'DRIVER', $vCountry, $iDriverId,$eSystem); //Added By HJ On 12-09-2019


    if ($checEmailExist['status'] == 2) {
        echo 'deleted';
        $messge = "LBL_MOBILE_EXIST";
    } else if ($checEmailExist['status'] == 0) {
        echo 'false';
        $messge = "LBL_INVALID_MEMBER_USER_COUNTRY_EMAIL_TXT";
    } else {
        echo 'true';
    }
    /*$vCountry = $_REQUEST['vCountry'];
    $sql2 = "SELECT count('vPhone') as Total,eStatus FROM register_driver WHERE vPhone = '" . $vPhone . "'" . $ssql;
    $db_driver = $obj->MySQLSelect($sql2);
    if ($db_driver[0]['Total'] > 0) {
        if ((ucfirst($db_driver[0]['eStatus']) == 'Deleted') || (ucfirst($db_driver[0]['eStatus']) == 'Inactive')) {
            echo 'deleted';
        } else {
            echo 'false';
        }
        //echo 'false';
    } else {
        echo 'true';
    }*/
}
// for profile edit 
/*if ($usertype == 'company' && isset($_REQUEST['phone'])) {
    $phone = $_REQUEST['phone'];
    $vCountry = $_REQUEST['vCountry'];
    $eSystem = "";    
    
    $checEmailExist = $generalobj->checkMemberDataInfo($phone, "", 'COMPANY', $vCountry, $iCompanyId,$eSystem); //Added By HJ On 12-09-2019
    if ($checEmailExist['status'] == 0) {
        echo 'deleted';
        $messge = "LBL_MOBILE_EXIST";
    } else if ($checEmailExist['status'] == 2) {
        echo 'false';
        $messge = "LBL_INVALID_MEMBER_USER_COUNTRY_EMAIL_TXT";
    } else {
        echo 'true';
    }
    $sql1 = "SELECT count('vPhone') as Total,eStatus FROM company WHERE vPhone = '" . $phone . "'" . $ssql;
    $db_comp = $obj->MySQLSelect($sql1);
    if ($db_comp[0]['Total'] > 0) {
        if ((ucfirst($db_comp[0]['eStatus']) == 'Deleted') || (ucfirst($db_comp[0]['eStatus']) == 'Inactive')) {
            echo 'deleted';
        } else {
            echo 'false';
        }
    } else {
        echo 'true';
    }
}*/
// for profile edit 
/*if ($usertype == 'driver' && isset($_REQUEST['phone'])) {
    $phone = $_REQUEST['phone'];
    $vCountry = $_REQUEST['vCountry'];
    $eSystem = "";
    
    $checEmailExist = $generalobj->checkMemberDataInfo($vPhone, "", 'DRIVER', $vCountry, '',$eSystem); //Added By HJ On 12-09-2019
    if ($checEmailExist['status'] == 2) {
        echo 'deleted';
        $messge = "LBL_MOBILE_EXIST";
    } else if ($checEmailExist['status'] == 0) {
        echo 'false';
        $messge = "LBL_INVALID_MEMBER_USER_COUNTRY_EMAIL_TXT";
    } else {
        echo 'true';
    }*/
    
    /*$sql2 = "SELECT count('vPhone') as Total,eStatus FROM register_driver WHERE vPhone = '" . $phone . "'" . $ssql;
    $db_driver = $obj->MySQLSelect($sql2);
    if ($db_driver[0]['Total'] > 0) {
        if ((ucfirst($db_driver[0]['eStatus']) == 'Deleted') || (ucfirst($db_driver[0]['eStatus']) == 'Inactive')) {
            echo 'deleted';
        } else {
            echo 'false';
        }
        //echo 'false';
    } else {
        echo 'true';
    }*/
//}
if($usertype=='user' && isset($_REQUEST['vPhone'])){
    $vPhone=$_REQUEST['vPhone'];
    $vCountry = $_REQUEST['vCountry'];
    $eSystem = "";
    
    $checEmailExist = $generalobj->checkMemberDataInfo($vPhone, "", 'RIDER', $vCountry, '',$eSystem); //Added By HJ On 12-09-2019
    if ($checEmailExist['status'] == 2) {
        echo 'deleted';
        $messge = "LBL_MOBILE_EXIST";
    } else if ($checEmailExist['status'] == 0) {
        echo 'false';
        $messge = "LBL_INVALID_MEMBER_USER_COUNTRY_EMAIL_TXT";
    } else {
        echo 'true';
    }
    /*$sql2 = "SELECT count('vPhone') as Total,eStatus FROM register_user WHERE vPhone = '".$phone."'".$ssql;
    $db_driver = $obj->MySQLSelect($sql2);

    if($db_driver[0]['Total'] > 0) {
        if((ucfirst($db_driver[0]['eStatus'])=='Deleted') || (ucfirst($db_driver[0]['eStatus'])=='Inactive') ){ 
            echo 'deleted';
        } else {
            echo 'false';
        }
        //echo 'false';
    } else {
        echo 'true';
    }*/
}
if($usertype=='store' && isset($_REQUEST['vPhone'])) {
		$vPhone=$_REQUEST['vPhone'];
        $vCountry = $_REQUEST['vCountry'];
        $eSystem = "DeliverAll";
        
        $checEmailExist = $generalobj->checkMemberDataInfo($vPhone, "", 'COMPANY', $vCountry, $iCompanyId,$eSystem); //Added By HJ On 12-09-2019
        if ($checEmailExist['status'] == 2) {
            echo 'deleted';
            $messge = "LBL_MOBILE_EXIST";
        } else if ($checEmailExist['status'] == 0) {
            echo 'false';
            $messge = "LBL_INVALID_MEMBER_USER_COUNTRY_EMAIL_TXT";
        } else {
            echo 'true';
        }
		
		/*$sql2 = "SELECT count('vPhone') as Total,eStatus FROM company WHERE eSystem = 'DeliverAll' AND vPhone = '".$phone."'".$ssql;
		$db_driver = $obj->MySQLSelect($sql2);

		if($db_driver[0]['Total'] > 0) {
			if((ucfirst($db_driver[0]['eStatus'])=='Deleted') || (ucfirst($db_driver[0]['eStatus'])=='Inactive') ){ 
				echo 'deleted';
			} else {
				echo 'false';
			}
			//echo 'false';
		} else {
			echo 'true';
		}*/
}
?>