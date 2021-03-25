<?php

include_once('../../common.php');

if (!isset($generalobjDriver)) {
    require_once(TPATH_CLASS . "class.general_admin.php");
    $generalobjDriver = new General_admin();
}
$generalobjDriver->check_member_login();

$reload = $_SERVER['REQUEST_URI'];
$urlparts = explode('?', $reload);
$parameters = $urlparts[1];

$id = isset($_REQUEST['id']) ? $_REQUEST['id'] : '';
$iDriverVehicleId = isset($_REQUEST['iDriverVehicleId']) ? $_REQUEST['iDriverVehicleId'] : '';
$iDriverId = isset($_REQUEST['iDriverId']) ? $_REQUEST['iDriverId'] : '';
$status = isset($_REQUEST['status']) ? $_REQUEST['status'] : '';
$statusVal = isset($_REQUEST['statusVal']) ? $_REQUEST['statusVal'] : '';
$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : 'view';
$hdn_del_id = isset($_REQUEST['hdn_del_id']) ? $_REQUEST['hdn_del_id'] : '';
$checkbox = isset($_REQUEST['checkbox']) ? implode(',', $_REQUEST['checkbox']) : '';
$method = isset($_REQUEST['method']) ? $_REQUEST['method'] : '';

$getUberXVehicles = $obj->MySQLSelect("SELECT * FROM driver_vehicle WHERE eType='UberX'");
$driverVehicleIdArr = array();
for ($d = 0; $d < Count($getUberXVehicles); $d++) {
    $driverVehicleIdArr[] = $getUberXVehicles[$d]['iDriverVehicleId'];
}

//Start make deleted
if (($statusVal == 'Deleted' || $method == 'delete') && ($iDriverVehicleId != '' || $checkbox != "")) {
    if (!$userObj->hasPermission('delete-provider-taxis')) {
        $_SESSION['success'] = 3;
        $_SESSION['var_msg'] = 'You do not have permission to delete vehicle';
    } else {
        //Added By Hasmukh On 05-10-2018 For Solved Bug Start
        if ($iDriverVehicleId != "") {
            $vehicleIds = $iDriverVehicleId;
        } else {
            $vehicleIds = $checkbox;
        }
        $explodeIds = explode(",", $vehicleIds);
        //Added By Hasmukh On 05-10-2018 For Solved Bug End
        if (SITE_TYPE != 'Demo') {
            $sql1 = "SELECT * FROM trips WHERE iDriverVehicleId = '" . $iDriverVehicleId . "' AND iActive IN ('Active',  'On Going Trip')";
            $current_active_trip = $obj->MySQLSelect($sql1);

            if (empty($current_active_trip)) {
                $query = "UPDATE driver_vehicle SET eStatus = 'Deleted' WHERE iDriverVehicleId IN (" . $vehicleIds . ")";
                $obj->sql_query($query);

                //$sql = "SELECT * FROM register_driver WHERE iDriverId = '".$iDriverId."' AND vAvailability = 'Available' AND iDriverVehicleId = '" . $iDriverVehicleId . "'";
                //$sql = "SELECT * FROM register_driver WHERE iDriverId = '" . $iDriverId . "' AND iDriverVehicleId IN (" . $vehicleIds . ")";
                //$avail_driver = $obj->MySQLSelect($sql);
                for ($re = 0; $re < count($explodeIds); $re++) {
                    //if ($APP_TYPE != 'Ride-Delivery-UberX') {
                    if (!in_array($explodeIds[$re], $driverVehicleIdArr)) {
                        //if (!empty($avail_driver)) {
                        $sql_update = "UPDATE register_driver SET vAvailability = 'Not Available', `iDriverVehicleId`= '0' WHERE iDriverId = '" . $iDriverId . "' AND iDriverVehicleId IN (" . $vehicleIds . ") AND vAvailability = 'Available'";
                        $obj->sql_query($sql_update);
                        //}
                    }
                }
                $_SESSION['success'] = '1';
                $_SESSION['var_msg'] = $langage_lbl_admin['LBL_RECORD_DELETE_MSG'];
            } else {
                $_SESSION['success'] = '3';
                $_SESSION['var_msg'] = "Vehicle can't delete because of " . strtolower($langage_lbl_admin['LBL_DRIVER_TXT_ADMIN']) . " has on trip.";
            }
        } else {
            $_SESSION['success'] = '2';
        }
    }
    if ($method == 'delete') {
        $parameters = '';
        foreach ($_REQUEST as $key => $val) {
            if ($key == "iDriverId") {
                $parameters .= "&$key=";
            } else {
                $parameters .= "&$key=" . $val;
            }
        }
    }
    header("Location:" . $tconfig["tsite_url_main_admin"] . "vehicles.php?" . $parameters);
    exit;
}
//End make deleted
//Start Change single Status
// For active or inactive
//print_r($status);die;
if ($iDriverVehicleId != '' && $status != '') {
    if (!$userObj->hasPermission('update-status-provider-taxis')) {
        $_SESSION['success'] = 3;
        $_SESSION['var_msg'] = 'You do not have permission to update vehicle status';
    } else {
        if (SITE_TYPE != 'Demo') {
            if ($status == 'Inactive') {
                $sql1 = "SELECT * FROM trips WHERE iDriverVehicleId = '" . $iDriverVehicleId . "' AND iActive IN ('Active',  'On Going Trip')";
                $current_active_trip = $obj->MySQLSelect($sql1);
                if (empty($current_active_trip)) {
                    $query = "UPDATE driver_vehicle SET eStatus = '" . $status . "' WHERE iDriverVehicleId = '" . $iDriverVehicleId . "'";
                    $obj->sql_query($query);

                    //$sql = "SELECT * FROM register_driver WHERE iDriverVehicleId = '" . $iDriverVehicleId . "'";
                    //$avail_driver = $obj->MySQLSelect($sql);
                    //if ($APP_TYPE != 'Ride-Delivery-UberX') {
                    if (!in_array($iDriverVehicleId, $driverVehicleIdArr)) {
                        //if (!empty($avail_driver)) {
                        $sql_update = "UPDATE register_driver SET vAvailability = 'Not Available', `iDriverVehicleId`= '0' WHERE iDriverVehicleId = '" . $iDriverVehicleId . "' AND vAvailability = 'Available'";
                        $obj->sql_query($sql_update);
                        //}
                    }
                    if ($SEND_TAXI_EMAIL_ON_CHANGE == 'Yes') {
                        $sql23 = "SELECT m.vMake, md.vTitle,rd.vEmail, rd.vName, rd.vLastName, c.vCompany as companyFirstName
									FROM driver_vehicle dv, register_driver rd, make m, model md, company c
									WHERE dv.eStatus != 'Deleted' AND dv.iDriverId = rd.iDriverId AND dv.iCompanyId = c.iCompanyId  AND dv.iModelId = md.iModelId AND dv.iMakeId = m.iMakeId AND dv.iDriverVehicleId = '" . $iDriverVehicleId . "'";
                        $data_email_drv = $obj->MySQLSelect($sql23);
                        $maildata['EMAIL'] = $data_email_drv[0]['vEmail'];
                        $maildata['NAME'] = $data_email_drv[0]['vName'];
                        $maildata['DETAIL'] = "Your " . $langage_lbl_admin['LBL_TEXI_ADMIN'] . " " . $data_email_drv[0]['vTitle'] . " For COMPANY " . $data_email_drv[0]['companyFirstName'] . " is temporarly " . $status;
                        $generalobj->send_email_user("ACCOUNT_STATUS", $maildata);
                    }
                    $_SESSION['success'] = '1';
                    $_SESSION['var_msg'] = $langage_lbl_admin['LBL_RECORD_INACTIVATE_MSG'];
                } else {
                    $_SESSION['success'] = '3';
                    $_SESSION['var_msg'] = "Vehicle can't inactive because of " . strtolower($langage_lbl_admin['LBL_DRIVER_TXT_ADMIN']) . " has on trip.";
                }
            } else {
                $sqldr = "SELECT iDriverId,iCompanyId FROM driver_vehicle WHERE iDriverVehicleId = '" . $iDriverVehicleId . "'";
                $DriverDetails = $obj->MySQLSelect($sqldr);

                $querydr = "SELECT iDriverVehicleId FROM register_driver WHERE iDriverId = '" . $DriverDetails[0]['iDriverId'] . "' ";
                $DriverVehicleDetails = $obj->MySQLSelect($querydr);
                if ($DriverVehicleDetails[0]['iDriverVehicleId'] == '' || $DriverVehicleDetails[0]['iDriverVehicleId'] == '0') {
                    $query = "UPDATE register_driver SET iDriverVehicleId = '" . $iDriverVehicleId . "' WHERE iDriverId = '" . $DriverDetails[0]['iDriverId'] . "'";
                    $obj->sql_query($query);
                }

                $query = "UPDATE driver_vehicle SET eStatus = '" . $status . "' WHERE iDriverVehicleId = '" . $iDriverVehicleId . "'";
                $obj->sql_query($query);
                if ($SEND_TAXI_EMAIL_ON_CHANGE == 'Yes') {
                    $sql23 = "SELECT m.vMake, md.vTitle,rd.vEmail, rd.vName, rd.vLastName, c.vCompany as companyFirstName
								FROM driver_vehicle dv, register_driver rd, make m, model md, company c
								WHERE dv.eStatus != 'Deleted'  AND dv.iDriverId = rd.iDriverId AND dv.iCompanyId = c.iCompanyId AND dv.iModelId = md.iModelId AND dv.iMakeId = m.iMakeId AND dv.iDriverVehicleId = '" . $iDriverVehicleId . "'";
                    $data_email_drv = $obj->MySQLSelect($sql23);
                    $maildata['EMAIL'] = $data_email_drv[0]['vEmail'];
                    $maildata['NAME'] = $data_email_drv[0]['vName'];
                    $maildata['DETAIL'] = "Your " . $langage_lbl_admin['LBL_TEXI_ADMIN'] . " " . $data_email_drv[0]['vTitle'] . " For COMPANY " . $data_email_drv[0]['companyFirstName'] . " is temporarly " . $status;
                    $generalobj->send_email_user("ACCOUNT_STATUS", $maildata);
                }
                $_SESSION['success'] = '1';
                if ($status == 'Active') {
                    $_SESSION['var_msg'] = $langage_lbl_admin['LBL_RECORD_ACTIVATE_MSG'];
                } else {
                    $_SESSION['var_msg'] = $langage_lbl_admin['LBL_RECORD_INACTIVATE_MSG'];
                }
            }
        } else {
            $_SESSION['success'] = 2;
        }
    }
    header("Location:" . $tconfig["tsite_url_main_admin"] . "vehicles.php?" . $parameters);
    exit;
}
//End Change single Status
//Start Change All Selected Status
if (!empty($checkbox) && $statusVal != "") {
    if (!$userObj->hasPermission('update-status-provider-taxis', 'delete-provider-taxis')) {
        $_SESSION['success'] = 3;
        $_SESSION['var_msg'] = 'You do not have permission to update or delete vehicle status';
    } else {
        $checkbox_values = implode(',', $_REQUEST['checkbox']);
        //print_r($_REQUEST['checkbox']);die;
        if (SITE_TYPE != 'Demo') {
            $current_active_trip = "";
            if (($statusVal == "Deleted") || ($statusVal == "Inactive")) {
                $sql = "SELECT iDriverId,iDriverVehicleId FROM driver_vehicle WHERE iDriverVehicleId IN (" . $checkbox_values . ")";
                $driverids = $obj->MySQLSelect($sql);
                //echo "<pre>";print_r($driverids);die;
                $vehicleIdArr = $data = array();
                foreach ($driverids as $key => $value) {
                    $data[$value['iDriverId']] = $value['iDriverId'];
                    if (!in_array($value['iDriverVehicleId'], $driverVehicleIdArr)) {
                        $vehicleIdArr[$value['iDriverId']] = $value['iDriverId'];
                    }
                }
                $driverid = implode(",", $data);
                $driverIds = implode(",", $vehicleIdArr);
                $sql1 = "SELECT * FROM register_driver as d LEFT JOIN trips as t ON t.iDriverId = d.iDriverId WHERE t.iDriverId IN (" . $driverid . ") AND  t.iDriverVehicleId  IN (" . $checkbox_values . ") AND  t.iActive IN ('Active',  'On Going Trip') ";
                $current_active_trip = $obj->MySQLSelect($sql1);
            }
            if (empty($current_active_trip)) {
                $query = "UPDATE driver_vehicle SET eStatus = '" . $statusVal . "' WHERE iDriverVehicleId IN (" . $checkbox_values . ")";
                $obj->sql_query($query);

                //$sql = "SELECT * FROM register_driver WHERE iDriverId IN (" . $driverIds . ") AND vAvailability = 'Available' AND iDriverVehicleId IN (" . $checkbox_values . ")";
                //$avail_driver = $obj->MySQLSelect($sql);
                if ($statusVal == "Deleted") {
                    $sql_update = "UPDATE register_driver SET vAvailability = 'Not Available', `iDriverVehicleId`= '0' WHERE iDriverId IN (" . $driverIds . ") AND vAvailability = 'Available'";
                    $obj->sql_query($sql_update);
                }

                if ($SEND_TAXI_EMAIL_ON_CHANGE == 'Yes') {
                    foreach ($checkbox as $iDriverVehicleId) {
                        $sql23 = "SELECT m.vMake, md.vTitle,rd.vEmail, rd.vName, rd.vLastName, c.vCompany as companyFirstName
								FROM driver_vehicle dv, register_driver rd, make m, model md, company c
								WHERE dv.eStatus != 'Deleted' AND dv.iDriverId = rd.iDriverId AND dv.iCompanyId = c.iCompanyId AND dv.iModelId = md.iModelId AND dv.iMakeId = m.iMakeId AND dv.iDriverVehicleId = '" . $iDriverVehicleId . "'";
                        $data_email_drv = $obj->MySQLSelect($sql23);
                        $maildata['EMAIL'] = $data_email_drv[0]['vEmail'];
                        $maildata['NAME'] = $data_email_drv[0]['vName'];
                        $maildata['DETAIL'] = "Your " . $langage_lbl_admin['LBL_TEXI_ADMIN'] . " " . $data_email_drv[0]['vTitle'] . " For COMPANY " . $data_email_drv[0]['companyFirstName'] . " is temporarily " . $statusVal;
                        $generalobj->send_email_user("ACCOUNT_STATUS", $maildata);
                    }
                }

                $_SESSION['success'] = '1';
                $_SESSION['var_msg'] = $langage_lbl_admin['LBL_Record_Updated_successfully'];
            } else {
                $_SESSION['success'] = '3';
                $_SESSION['var_msg'] = "Record can't " . $statusVal . " because one of " . strtolower($langage_lbl_admin['LBL_DRIVER_TXT_ADMIN']) . " has on trip.";
            }
        } else {
            $_SESSION['success'] = 2;
        }
    }
    header("Location:" . $tconfig["tsite_url_main_admin"] . "vehicles.php?" . $parameters);
    exit;
}
//End Change All Selected Status
?>