<?php

Class General_admin {

    public function __construct() {
        $_SESSION['sess_lang'] = "EN";
        //echo $_SESSION['sess_iAdminUserId'];


        if (isset($_SESSION['sess_iAdminUserId']) && !empty($_SESSION['sess_iAdminUserId'])) {

            global $obj;
            $iAdminUserId = $_SESSION['sess_iAdminUserId'];

            $sql = "select vCode from language_master where eDefault='Yes'";
            $db_lbl = $obj->MySQLSelect($sql);
            $_SESSION['sess_lang'] = $db_lbl[0]['vCode'];

            $cmp_ssql = "";
            $sql = "SELECT COUNT(iAdminId) AS Total,eStatus FROM administrators WHERE iAdminId=" . $iAdminUserId;
            $data = $obj->MySQLSelect($sql);
            $checkadmin = $data[0]['Total'];
            $eStatus = $data[0]['eStatus'];
            if ($eStatus == 'Deleted') {
                $checkadmin = 0;
            } else if ($eStatus == 'Inactive') {
                $checkadmin = 0;
            } else {
                $checkadmin = 1;
                $isAjax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) AND strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest'; //check file is from ajax then session is not set bc it is not redirect after login
                if (!$isAjax){
                    $_SESSION['login_redirect_url'] = $_SERVER['REQUEST_URI']; //added by SP for redirection on admin after login on 15-7-2019, here put bc when page open from admin side and logout then open same link
                }
            }
            if ($checkadmin <= 0) {

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
                if ($eStatus == 'Deleted') {
                    $_SESSION['checkadminmsg'] = 'Your account has been deleted.Please contact administrator to activate your account.';
                } else {
                    $_SESSION['checkadminmsg'] = 'Your account has been disabled.Please contact administrator to activate your account.';
                }
                if ($_SESSION["SessionUserType"] == 'hotel') {
                    $_SESSION["SessionUserType"] = "";
                    header("location:../hotel");
                } else {
                    header("location:index.php");
                }
            }
        }
    }

    public function getCompanyDetails() {
        global $obj;
        $cmp_ssql = "";
        // if(SITE_TYPE =='Demo'){
        // $cmp_ssql = " And tRegistrationDate > '".WEEK_DATE."'";
        // }
        $sql = "SELECT COUNT(iCompanyId) AS Total FROM company WHERE eStatus != 'Deleted' AND eSystem =  'General' $cmp_ssql";
        $data = $obj->MySQLSelect($sql);
        return $data[0]['Total'];
        $data[0]['Total'];
    }

    public function getStoreDetails() {
        global $obj;
        $eSystem = " AND eSystem = 'DeliverAll'";

        $cmp_ssql = "";
        // if(SITE_TYPE =='Demo'){
        // $cmp_ssql = " And tRegistrationDate > '".WEEK_DATE."'";
        // }
        $sql = "SELECT COUNT(iCompanyId) AS Total FROM company WHERE eStatus != 'Deleted' $eSystem $cmp_ssql";
        $data = $obj->MySQLSelect($sql);
        return $data[0]['Total'];
    }

    public function getCompanycount() {
        global $obj;
        $cmp_ssql = "";
        $eSystem = " AND eSystem = 'General'";
        // if(SITE_TYPE =='Demo'){
        // $cmp_ssql = " And tRegistrationDate > '".WEEK_DATE."'";
        // }
        $sql = "SELECT count(iCompanyId) tot_company FROM company WHERE eStatus != 'Deleted'  $cmp_ssql order by tRegistrationDate desc";
        $data = $obj->MySQLSelect($sql);
        return $data;
    }

    public function getDriverDetails($status) {
        $cmp_ssql = "";
        if (SITE_TYPE == 'Demo') {
            $cmp_ssql = " And rd.tRegistrationDate > '" . WEEK_DATE . "'";
        }
        global $obj;
        $ssl = "";
        if ($status != "" && $status == "active") {
            $ssl = " AND rd.eStatus = '" . $status . "'";
        } else if ($status != "" && $status == "inactive") {
            $ssl = " AND rd.eStatus = '" . $status . "'";
        }
        $sql = "SELECT rd.*, c.vCompany companyFirstName, c.vLastName companyLastName FROM register_driver rd LEFT JOIN company c ON rd.iCompanyId = c.iCompanyId and c.eStatus != 'Deleted' WHERE  rd.eStatus != 'Deleted'" . $ssl . $cmp_ssql;
        $data = $obj->MySQLSelect($sql);

        return $data;
    }

    public function getDrivercount($status) {
        $cmp_ssql = "";
        if (SITE_TYPE == 'Demo') {
            $cmp_ssql = " And rd.tRegistrationDate > '" . WEEK_DATE . "'";
        }
        global $obj;
        $ssl = "";
        if ($status != "" && $status == "active") {
            $ssl = " AND rd.eStatus = '" . $status . "'";
        } else if ($status != "" && $status == "inactive") {
            $ssl = " AND rd.eStatus = '" . $status . "'";
        }
        $sql = "SELECT count(rd.iDriverId) as tot_driver FROM register_driver rd LEFT JOIN company c ON rd.iCompanyId = c.iCompanyId and c.eStatus != 'Deleted' WHERE  rd.eStatus != 'Deleted'" . $ssl . $cmp_ssql;
        $data = $obj->MySQLSelect($sql);

        return $data;
    }

    public function getVehicleDetails() {
        $cmp_ssql = "";
        if (SITE_TYPE == 'Demo') {
            $cmp_ssql = " And rd.tRegistrationDate > '" . WEEK_DATE . "'";
        }
        global $obj;
        $sql = "SELECT dv.*, m.vMake, md.vTitle,rd.vEmail, rd.vName, rd.vLastName, c.vName as companyFirstName, c.vLastName as companyLastName
				FROM driver_vehicle dv, register_driver rd, make m, model md, company c
				WHERE
				  dv.eStatus != 'Deleted'
				  AND dv.iDriverId = rd.iDriverId
				  AND dv.iCompanyId = c.iCompanyId
				  AND dv.iModelId = md.iModelId
				  AND dv.iMakeId = m.iMakeId" . $cmp_ssql;
        $data = $obj->MySQLSelect($sql);

        return $data;
    }

    public function getRiderDetails($status = "") {
        $cmp_ssql = "";
        if (SITE_TYPE == 'Demo') {
            $cmp_ssql = " And tRegistrationDate > '" . WEEK_DATE . "'";
        }
        global $obj;
        if ($status == "all")
            $sql = "SELECT * FROM register_user WHERE 1 = 1 " . $cmp_ssql;
        else
            $sql = "SELECT * FROM register_user WHERE eStatus != 'Deleted'" . $cmp_ssql;
        $data = $obj->MySQLSelect($sql);

        return $data;
    }

    public function getRiderCount($status = "") {
        global $obj;
        $cmp_ssql = "";
        if (SITE_TYPE == 'Demo') {
            $cmp_ssql = " And tRegistrationDate > '" . WEEK_DATE . "'";
        }

        $ssql1 = "AND (vEmail != '' OR vPhone != '') AND eHail='No'";
        if ($status == "all")
            $sql = "SELECT count(iUserId) as tot_rider FROM register_user WHERE 1 = 1 " . $ssql1 . $cmp_ssql;
        else
            $sql = "SELECT count(iUserId) FROM register_user WHERE eStatus != 'Deleted'" . $ssql1 . $cmp_ssql;
        $data = $obj->MySQLSelect($sql);

        return $data;
    }

    public function getTripsDetails() {
        $cmp_ssql = "";
        if (SITE_TYPE == 'Demo') {
            $cmp_ssql = " And tEndDate > '" . WEEK_DATE . "'";
        }
        global $obj;
        $sql = "SELECT * FROM trips WHERE 1=1" . $cmp_ssql;
        $data = $obj->MySQLSelect($sql);

        return $data;
    }

    /* check admin is login or not */

    function check_member_login() {

        /* global $tconfig;
          $previosLink = $_SERVER['REQUEST_URI'];
          if ((strpos($previosLink, 'ajax') === false) && (strpos($previosLink, 'get') === false)) {
          $_SESSION['current_link'] = $previosLink;
          }
          $sess_iAdminUserId = isset($_SESSION['sess_iAdminUserId'])?$_SESSION['sess_iAdminUserId']:'';
          $sess_iGroupId = isset($_SESSION['sess_iGroupId'])?$_SESSION['sess_iGroupId']:'';
          if($sess_iAdminUserId == "" && basename($_SERVER['PHP_SELF']) != "index.php") {
          header("Location:".$tconfig["tsite_url_main_admin"]."index.php");
          exit;
          }
          //If GroupId == 2
          //echo basename($_SERVER['PHP_SELF']); die;
          if($sess_iGroupId == '2' && basename($_SERVER['PHP_SELF']) == "dashboard.php") {
          header("Location:".$tconfig["tsite_url_main_admin"]."add_booking.php");
          exit;
          } else if($sess_iGroupId == '2' && basename($_SERVER['PHP_SELF']) != "cab_booking.php" && basename($_SERVER['PHP_SELF']) != "add_booking.php" && basename($_SERVER['PHP_SELF']) != "action_booking.php" && basename($_SERVER['PHP_SELF']) != "get_available_driver_list.php" && basename($_SERVER['PHP_SELF']) != "get_map_drivers_list.php" && basename($_SERVER['PHP_SELF']) != "ajax_find_rider_by_number.php" && basename($_SERVER['PHP_SELF']) != "change_code.php" && basename($_SERVER['PHP_SELF']) != "get_driver_detail_popup.php" && basename($_SERVER['PHP_SELF']) != "ajax_checkBooking_email.php" && basename($_SERVER['PHP_SELF']) != "admin_action.php" && basename($_SERVER['PHP_SELF']) != "map.php" && basename($_SERVER['PHP_SELF']) != "get_available_driver_list_in_godsview.php" && basename($_SERVER['PHP_SELF']) != "invoice.php" && basename($_SERVER['PHP_SELF']) != "ajax_booking_details.php" && basename($_SERVER['PHP_SELF']) != "checkForRestriction.php" && basename($_SERVER['PHP_SELF']) != "ajax_estimate_by_vehicle_type.php" &&  basename($_SERVER['PHP_SELF']) != "ajax_get_user_balance.php") {
          header("Location:".$tconfig["tsite_url_main_admin"]."add_booking.php" );
          exit;
          }
          //If GroupId == 3
          if($sess_iGroupId == '3' && basename($_SERVER['PHP_SELF']) == "dashboard.php") {
          header("Location:".$tconfig["tsite_url_main_admin"]."trip.php");
          exit;
          }else if($sess_iGroupId == '3' && basename($_SERVER['PHP_SELF']) != "trip.php" && basename($_SERVER['PHP_SELF']) != "referrer.php" && strpos(basename($_SERVER['PHP_SELF']), 'report') == false && basename($_SERVER['PHP_SELF']) != "admin_action.php" && basename($_SERVER['PHP_SELF']) != "invoice.php" && basename($_SERVER['PHP_SELF']) != "referrer_action.php" && basename($_SERVER['PHP_SELF']) != "export_driver_details.php" && basename($_SERVER['PHP_SELF']) != "report_export.php" && basename($_SERVER['PHP_SELF']) != "export_driver_pay_details.php" && basename($_SERVER['PHP_SELF']) != "export_trip_pay_details.php" && basename($_SERVER['PHP_SELF']) != "payment_report.php" && basename($_SERVER['PHP_SELF']) != "wallet_report.php" && basename($_SERVER['PHP_SELF']) != "driver_pay_report.php" && basename($_SERVER['PHP_SELF']) != "driver_log_report.php" && basename($_SERVER['PHP_SELF']) != "cancelled_trip.php" && basename($_SERVER['PHP_SELF']) != "ride_acceptance_report.php" && basename($_SERVER['PHP_SELF']) != "driver_trip_detail.php" && basename($_SERVER['PHP_SELF']) != "ajax_find_driver_by_company.php" && basename($_SERVER['PHP_SELF']) != "cancellation_payment_report.php"   && basename($_SERVER['PHP_SELF']) != "allorders.php" && basename($_SERVER['PHP_SELF']) != "driver_payment_report.php" && basename($_SERVER['PHP_SELF']) != "cancelled_report.php" && basename($_SERVER['PHP_SELF']) != "cancelled_orders.php" && basename($_SERVER['PHP_SELF']) != "restaurants_pay_report.php" && basename($_SERVER['PHP_SELF']) != "driver_trip_detail.php" && basename($_SERVER['PHP_SELF']) != "ajax_find_driver_by_company.php" && basename($_SERVER['PHP_SELF']) != "admin_payment_report.php" && basename($_SERVER['PHP_SELF']) != "order_invoice.php") {
          header("Location:".$tconfig["tsite_url_main_admin"]."trip.php");
          exit;
          } */
    }

    function getPostForm($POST_Arr, $msg = "", $action = "") {
        $str = '
			<html>
			<form name="frm1" action="' . $action . '" method=post>';
        foreach ($POST_Arr as $key => $value) {
            if ($key != "mode") {
                if (is_array($value)) {
                    foreach ($value as $kk => $vv)
                        $str .= '<br><input type="Hidden" name="Data[' . $kk . ']" value="' . stripslashes($vv) . '">';
                    $str .= '<br><input type="Hidden" name="' . $key . '[]" value="' . stripslashes($value[$i]) . '">';
                } else {
                    $str .= '<br><input type="Hidden" name="' . $key . '" value="' . stripslashes($value) . '">';
                }
            }
        }
        $str .= '<input type="Hidden" name=var_msg value="' . $msg . '">
			</form>
			<script>
			document.frm1.submit();
			</script>
			</html>';
        echo $str;
        exit;
    }

    function clearEmail($email) {
        if (SITE_TYPE == "Demo") {
            //Added By HJ On 29-11-2019 For Mask 70% String Start
            $email = trim($email);
            $mail = explode('.', $email);
            $text = $mail[0];
            $wordCount = strlen($text);
            $char = floor(($wordCount * 70) / 100);
            $orgChar = $wordCount - $char;
            $output = substr($text, 0, $orgChar);
            return $output . str_repeat("*", $char) . "." . $mail[count($mail) - 1];
            //Added By HJ On 29-11-2019 For Mask 70% String End
        } else {
            return $email;
        }
    }

    function clearPhone($text) {
        if (SITE_TYPE == "Demo") {
            //Added By HJ On 29-11-2019 For Mask 70% String Start
            $text = trim($text);
            $wordCount = strlen($text);
            $char = floor(($wordCount * 70) / 100);
            $orgChar = $wordCount - $char;
            $output = substr($text, 0, $orgChar);
            return $output . str_repeat("*", $char);
            //Added By HJ On 29-11-2019 For Mask 70% String End
        } else {
            return $text;
        }
    }

    function clearName($text) {
        if (SITE_TYPE == "Demo") {
            //Added By HJ On 29-11-2019 For Mask 70% String Start 
            $text = trim($text);
            $wordCount = strlen($text);
            $char = floor(($wordCount * 70) / 100);
            $orgChar = $wordCount - $char;
            $output = substr($text, 0, $orgChar);
            return $output . str_repeat("*", $char);
            //Added By HJ On 29-11-2019 For Mask 70% String End
        } else {
            return $text;
        }
    }

    function clearCmpName($text) {
        if (SITE_TYPE == "Demo") {
            //Added By HJ On 29-11-2019 For Mask 70% String Start 
            $text = trim($text);
            $wordCount = strlen($text);
            $char = floor(($wordCount * 70) / 100);
            $orgChar = $wordCount - $char;
            $output = substr($text, 0, $orgChar);
            return $output . str_repeat("*", $char);
            //Added By HJ On 29-11-2019 For Mask 70% String End
        } else {
            return $text;
        }
    }

    function remove_unwanted($day = 7) {


        global $tconfig, $obj;
        $later_date = date('Y-m-d H:i:s', strtotime("-" . $day . " day", strtotime(date('Y-m-d H:i:s'))));

        /*         * *************** Delete Driver ************************** */

        $sql = "SELECT *
			FROM register_driver
			WHERE tRegistrationDate < '" . $later_date . "'";
        $data = $obj->MySQLSelect($sql);

        if (count($data) > 0) {
            $common_member = "SELECT iDriverId
				FROM register_driver
				WHERE tRegistrationDate < '" . $later_date . "'";

            $sql = "DELETE FROM driver_vehicle WHERE iDriverId IN (" . $common_member . ")";
            $db_sql = $obj->sql_query($sql);

            $sql = "DELETE FROM trips WHERE iDriverId IN (" . $common_member . ")";
            $db_sql = $obj->sql_query($sql);

            $sql = "DELETE FROM log_file WHERE iDriverId IN (" . $common_member . ")";
            $db_sql = $obj->sql_query($sql);

            $sql = "DELETE FROM register_driver WHERE tRegistrationDate < '" . $later_date . "'";
            $db_sql = $obj->sql_query($sql);
        }

        /*         * ********************************************Delete Rider ******************************************* */

        $sql = "SELECT *
			FROM register_user
			WHERE tRegistrationDate < '" . $later_date . "'";
        $data_user = $obj->MySQLSelect($sql);
        if (count($data_user) > 0) {
            $common_member = "SELECT iUserId
				FROM register_user
				WHERE tRegistrationDate < '" . $later_date . "'";

            $sql = "DELETE FROM trips WHERE iUserId IN (" . $common_member . ")";
            $db_sql = $obj->sql_query($sql);

            $sql = "DELETE FROM register_user WHERE tRegistrationDate < '" . $later_date . "'";
            $db_sql = $obj->sql_query($sql);
        }
    }

    public function getTripStates($tripStatus = NULL, $startDate = "", $endDate = "") {

        $cmp_ssql = "";
        $dsql = "";
        if (SITE_TYPE == 'Demo') {
            $cmp_ssql = " And tTripRequestDate > '" . WEEK_DATE . "'";
        }
        global $obj;
        $data = array();

        if ($startDate != "" && $endDate != "") {
            $dsql = " AND tTripRequestDate BETWEEN '" . $startDate . "' AND '" . $endDate . "'";
            //$dsql = " AND tTripRequestDate >= '".$startDate."' OR tTripRequestDate <= '".$endDate."' ";
        }

        global $userObj;
        $locations_where = "";
        if (count($userObj->locations) > 0) {
            $locations = implode(', ', $userObj->locations);
            $locations_where = " AND EXISTS(SELECT * FROM vehicle_type WHERE trips.iVehicleTypeId = vehicle_type.iVehicleTypeId AND vehicle_type.iLocationid IN(-1, {$locations}))";
        }

        if ($tripStatus != "") {
            if ($tripStatus == "on ride") {
                $ssl = " AND (iActive = 'On Going Trip' OR iActive = 'Active') AND eCancelled='No'";
            } else if ($tripStatus == "cancelled") {
                $ssl = " AND (iActive = 'Canceled' OR eCancelled='yes')";
            } else if ($tripStatus == "finished") {
                $ssl = " AND iActive = 'Finished' AND eCancelled='No'";
            } else {
                $ssl = "";
            }

            $sql = "SELECT COUNT(iTripId) as tot FROM trips WHERE 1 = 1 AND eSystem = 'General'" . $cmp_ssql . $ssl . $dsql . $locations_where;
            $data = $obj->MySQLSelect($sql);
        }
        return $data[0]['tot'];
    }

    public function getStoreTripStates($OrderStatus = "", $tOrderRequestDate = "", $dDeliveryDate = "") {
        $cmp_ssql = "";
        $dsql = "";
        if (SITE_TYPE == 'Demo') {
            $cmp_ssql = " And tOrderRequestDate > '" . WEEK_DATE . "'";
        }
        global $obj;
        $data = array();

        if ($tOrderRequestDate != "" && $dDeliveryDate != "") {
            $dsql = " AND tOrderRequestDate BETWEEN '" . $tOrderRequestDate . "' AND '" . $dDeliveryDate . "'";
            //$dsql = " AND tTripRequestDate >= '".$startDate."' OR tTripRequestDate <= '".$endDate."' ";
        }

        if ($OrderStatus != "") {
            $ssl = "";
            if ($OrderStatus == "on going order") {
                $ssl .= " Where o.iStatusCode IN ('1','2','4','5') AND IF(o.eTakeaway = 'Yes' && os.iStatusCode = 6, os.eTakeaway='Yes', os.eTakeaway != 'Yes')";
            } else if ($OrderStatus == "Cancelled") {
                $ssl .= " Where o.iStatusCode IN ('9','8','7') AND IF(o.eTakeaway = 'Yes' && os.iStatusCode = 6, os.eTakeaway='Yes', os.eTakeaway != 'Yes')";
            } else if ($OrderStatus == "Delivered") {
                $ssl .= " Where o.iStatusCode = '6' AND IF(o.eTakeaway = 'Yes' && os.iStatusCode = 6, os.eTakeaway='Yes', os.eTakeaway != 'Yes')";
            }
            else{
                $ssl .= " Where IF(o.eTakeaway = 'Yes' && os.iStatusCode = 6, os.eTakeaway='Yes', os.eTakeaway != 'Yes')";
            }
            $sql = "SELECT COUNT(o.iOrderId) as tot FROM orders o LEFT JOIN order_status os ON o.iStatusCode = os.iStatusCode" . $cmp_ssql . $ssl . $dsql;
            $data = $obj->MySQLSelect($sql);
        }
        return $data[0]['tot'];
    }

    public function getTripStatescount($tripStatus = NULL, $startDate = "", $endDate = "") {
        $cmp_ssql = "";
        if (SITE_TYPE == 'Demo') {
            $cmp_ssql = " And tTripRequestDate > '" . WEEK_DATE . "'";
        }
        global $obj;
        $data = array();

        if ($startDate != "" && $endDate != "") {
            $dsql = " AND tTripRequestDate BETWEEN '" . $startDate . "' AND '" . $endDate . "'";
        }


        global $userObj;
        $locations_where = "";
        if (count($userObj->locations) > 0) {
            $locations = implode(', ', $userObj->locations);
            $locations_where = " AND EXISTS(SELECT * FROM vehicle_type WHERE trips.iVehicleTypeId = vehicle_type.iVehicleTypeId AND vehicle_type.iLocationid IN(-1, {$locations}))";
        }

        if ($tripStatus != "") {
            if ($tripStatus == "on ride") {
                $ssl = " AND (iActive = 'On Going Trip' OR iActive = 'Active') AND eCancelled='No'";
            } else if ($tripStatus == "cancelled") {
                $ssl = " AND (iActive = 'Canceled' OR eCancelled='yes')";
            } else if ($tripStatus == "finished") {
                $ssl = " AND iActive = 'Finished' AND eCancelled='No'";
            } else {
                $ssl = "";
            }

            $sql = "SELECT iTripId FROM trips WHERE 1" . $cmp_ssql . $ssl . $dsql . $locations_where;
            $data = $obj->MySQLSelect($sql);
        }
        return $data;
    }

    public function getTotalEarns() {
        global $obj;

        global $userObj;
        $location_where = "";
        if (count($userObj->locations) > 0) {
            $locations = implode(', ', $userObj->locations);
            $location_where = " AND EXISTS(SELECT * FROM vehicle_type WHERE trips.iVehicleTypeId = vehicle_type.iVehicleTypeId AND vehicle_type.iLocationid IN(-1, {$locations}))";
        }

        $cmp_ssql = "";
        if (SITE_TYPE == 'Demo') {
            $cmp_ssql = " And tEndDate > '" . WEEK_DATE . "'";
        }
        
        $etypeSql = "";
        if(!isRideModuleAvailable()) {
            $etypeSql .= " AND eType != 'Ride'";
        }
        if(!isDeliveryModuleAvailable()) {
            $etypeSql .= " AND eType != 'Deliver' AND eType != 'Multi-Delivery'";
        }
        if(!isUberXModuleAvailable()) { 
            $etypeSql .= " AND eType != 'UberX'";     
        }
        
        $sql = "SELECT SUM( `fCommision` ) AS total FROM trips WHERE iActive = 'Finished' AND eSystem = 'General' AND eCancelled = 'No' {$location_where} " . $cmp_ssql . $etypeSql;
        $data = $obj->MySQLSelect($sql);
        $result = $data[0]['total'];
        return $result;
    }

    public function getStoreTotalEarns() {
        $cmp_ssql = "";
        if (SITE_TYPE == 'Demo') {
            $cmp_ssql = " And dDeliveryDate > '" . WEEK_DATE . "'";
        }
        global $obj;
        $sql = "SELECT SUM( `fCommision` ) AS total FROM orders WHERE 1 = 1 AND (iStatusCode = '6')" . $cmp_ssql;
        $data = $obj->MySQLSelect($sql);
        $result = $data[0]['total'];
        return $result;
    }

    public function getTripDateStates($time) {
        global $obj;
        $data = array();
        $cmp_ssql = "";
        if (SITE_TYPE == 'Demo') {
            $cmp_ssql = " And tEndDate > '" . WEEK_DATE . "'";
        }
        if ($time == "month") {
            $startDate = date('Y-m') . "-01 00:00:00";
            $endDate = date('Y-m') . "-31 23:59:59";
            $ssl = " AND tTripRequestDate BETWEEN '" . $startDate . "' AND '" . $endDate . "'";
        } else if ($time == "year") {
            $startDate1 = date('Y') . "-00-01 00:00:00";
            $endDate1 = date('Y') . "-12-31 23:59:59";
            $ssl = " AND tTripRequestDate BETWEEN '" . $startDate1 . "' AND '" . $endDate1 . "'";
        } else {
            $startDate2 = date('Y-m-d') . " 00:00:00";
            $endDate2 = date('Y-m-d') . " 23:59:59";
            $ssl = " AND tTripRequestDate BETWEEN '" . $startDate2 . "' AND '" . $endDate2 . "'";
        }

        global $userObj;
        $location_where = "";
        if (count($userObj->locations) > 0) {
            $locations = implode(', ', $userObj->locations);
            $location_where = " AND EXISTS(SELECT * FROM vehicle_type WHERE trips.iVehicleTypeId = vehicle_type.iVehicleTypeId AND vehicle_type.iLocationid IN(-1, {$locations}))";
        }

        $etypeSql = "";
        if(!isRideModuleAvailable()) {
            $etypeSql .= " AND eType != 'Ride'";
        }
        if(!isDeliveryModuleAvailable()) {
            $etypeSql .= " AND eType != 'Deliver' AND eType != 'Multi-Delivery'";
        }
        if(!isUberXModuleAvailable()) { 
            $etypeSql .= " AND eType != 'UberX'";     
        }
        
        $sql = "SELECT count(iTripId) as total FROM trips WHERE 1 = 1 AND eSystem = 'General' " . $ssl . $cmp_ssql . $etypeSql . $location_where;
        $data = $obj->MySQLSelect($sql);
        return $data[0]['total'];
    }

    public function getOrderDateStates($time) {
        global $obj;
        $data = array();
        $cmp_ssql = "";
        if (SITE_TYPE == 'Demo') {
            $cmp_ssql = " And dDeliveryDate > '" . WEEK_DATE . "'";
        }
        if ($time == "month") {
            $startDate = date('Y-m') . "-01 00:00:00";
            $endDate = date('Y-m') . "-31 23:59:59";
            $ssl = " AND tOrderRequestDate BETWEEN '" . $startDate . "' AND '" . $endDate . "'";
        } else if ($time == "year") {
            $startDate1 = date('Y') . "-00-01 00:00:00";
            $endDate1 = date('Y') . "-12-31 23:59:59";
            $ssl = " AND tOrderRequestDate BETWEEN '" . $startDate1 . "' AND '" . $endDate1 . "'";
        } else {
            $startDate2 = date('Y-m-d') . " 00:00:00";
            $endDate2 = date('Y-m-d') . " 23:59:59";
            $ssl = " AND tOrderRequestDate BETWEEN '" . $startDate2 . "' AND '" . $endDate2 . "'";
        }

        $sql = "SELECT count(iOrderId) as total FROM orders WHERE 1 = 1 " . $ssl . $cmp_ssql;
        $data = $obj->MySQLSelect($sql);
        return $data[0]['total'];
    }

    public function getDriverDateStatus($time) {
        $cmp_ssql = "";
        if (SITE_TYPE == 'Demo') {
            $cmp_ssql = " And rd.tRegistrationDate > '" . WEEK_DATE . "'";
        }
        global $obj;
        $data = array();
        if ($time == "month") {
            $startDate = date('Y-m') . "-00 00:00:00";
            $endDate = date('Y-m') . "-31 23:59:59";
            $ssl = " AND rd.tRegistrationDate BETWEEN '" . $startDate . "' AND '" . $endDate . "'";
        } else if ($time == "year") {
            $startDate1 = date('Y') . "-00-00 00:00:00";
            $endDate1 = date('Y') . "-12-31 23:59:59";
            $ssl = " AND rd.tRegistrationDate BETWEEN '" . $startDate1 . "' AND '" . $endDate1 . "'";
        } else {
            $startDate2 = date('Y-m-d') . " 00:00:00";
            $endDate2 = date('Y-m-d') . " 23:59:59";
            $ssl = " AND rd.tRegistrationDate BETWEEN '" . $startDate2 . "' AND '" . $endDate2 . "'";
        }

        $ssql1 = "AND (rd.vEmail != '' OR rd.vPhone != '')";

        $sql = "SELECT rd.*, c.vCompany companyFirstName, c.vLastName companyLastName FROM register_driver rd LEFT JOIN company c ON rd.iCompanyId = c.iCompanyId and c.eStatus != 'Deleted' WHERE  rd.eStatus != 'Deleted'" . $ssl . $ssql1 . $cmp_ssql;
        $data = $obj->MySQLSelect($sql);
        return $data;
    }

    public function getStoreDateStatus($time) {
        $cmp_ssql = "";
        if (SITE_TYPE == 'Demo') {
            $cmp_ssql = " And tRegistrationDate > '" . WEEK_DATE . "'";
        }
        global $obj;
        $data = array();
        if ($time == "month") {
            $startDate = date('Y-m') . "-00 00:00:00";
            $endDate = date('Y-m') . "-31 23:59:59";
            $ssl = " AND tRegistrationDate BETWEEN '" . $startDate . "' AND '" . $endDate . "'";
        } else if ($time == "year") {
            $startDate1 = date('Y') . "-00-00 00:00:00";
            $endDate1 = date('Y') . "-12-31 23:59:59";
            $ssl = " AND tRegistrationDate BETWEEN '" . $startDate1 . "' AND '" . $endDate1 . "'";
        } else {
            $startDate2 = date('Y-m-d') . " 00:00:00";
            $endDate2 = date('Y-m-d') . " 23:59:59";
            $ssl = " AND tRegistrationDate BETWEEN '" . $startDate2 . "' AND '" . $endDate2 . "'";
        }

        $ssql1 = "AND (vEmail != '' OR vPhone != '')";

        $sql = "SELECT iCompanyId FROM company WHERE eStatus != 'Deleted' AND eSystem = 'DeliverAll' " . $ssl . $ssql1 . $cmp_ssql;
        $data = $obj->MySQLSelect($sql);
        return $data;
    }

    public function getAllCashCountbyDriverId($id, $ssql) {
        $etypeSql = "";
        if(!isRideModuleAvailable()) {
            $etypeSql .= " AND eType != 'Ride'";
        }
        if(!isDeliveryModuleAvailable()) {
            $etypeSql .= " AND eType != 'Deliver' AND eType != 'Multi-Delivery'";
        }
        if(!isUberXModuleAvailable()) { 
            $etypeSql .= " AND eType != 'UberX'";     
        }
        
        $total = '0.00';
        if ($id != "") {
            global $obj;
            //$sql = "SELECT SUM(fTripGenerateFare) as totalAmount FROM trips AS tr WHERE vTripPaymentMode='Cash' AND iDriverId = '".$id."'".$ssql;
            $sql = "SELECT SUM(fCommision) as totalAmount FROM trips AS tr WHERE vTripPaymentMode='Cash' AND eDriverPaymentStatus = 'Unsettelled' AND eSystem = 'General' AND iDriverId = '" . $id . "'" . $ssql . $etypeSql;
            $data = $obj->MySQLSelect($sql);
            $total = ($data[0]['totalAmount'] != "") ? $data[0]['totalAmount'] : '0.00';
        }
        return number_format($total, 2);
    }

    public function getAllCardCountbyDriverId($id, $ssql) {
        $etypeSql = "";
        if(!isRideModuleAvailable()) {
            $etypeSql .= " AND eType != 'Ride'";
        }
        if(!isDeliveryModuleAvailable()) {
            $etypeSql .= " AND eType != 'Deliver' AND eType != 'Multi-Delivery'";
        }
        if(!isUberXModuleAvailable()) { 
            $etypeSql .= " AND eType != 'UberX'";     
        }
        
        $total = '0.00';
        if ($id != "") {
            global $obj;
            //$sql = "SELECT SUM(fTripGenerateFare) as totalAmount FROM trips AS tr WHERE eDriverPaymentStatus = 'Unsettelled' AND vTripPaymentMode='Card' AND iDriverId = '".$id."'".$ssql;
            $sql = "SELECT SUM(fTripGenerateFare) as totalTripAmount,SUM(fCommision) as totalCommissionAmount,SUM(fOutStandingAmount) as totalOutstandingAmount FROM trips as tr WHERE eDriverPaymentStatus = 'Unsettelled' AND vTripPaymentMode='Card' AND eSystem = 'General' AND iDriverId = '" . $id . "'" . $ssql . $etypeSql;
            $data = $obj->MySQLSelect($sql);
            //$total = ($data[0]['totalAmount'] != "")?$data[0]['totalAmount']:'0.00';
            $totalAmount = $data[0]['totalTripAmount'] - $data[0]['totalCommissionAmount'] - $data[0]['totalOutstandingAmount'];
            $total = ($totalAmount != "") ? $totalAmount : '0.00';
        }
        return number_format($total, 2);
    }

    public function getAllWalletCountbyDriverId($id, $ssql) {
        
        $etypeSql = "";
        if(!isRideModuleAvailable()) {
            $etypeSql .= " AND eType != 'Ride'";
        }
        if(!isDeliveryModuleAvailable()) {
            $etypeSql .= " AND eType != 'Deliver' AND eType != 'Multi-Delivery'";
        }
        if(!isUberXModuleAvailable()) { 
            $etypeSql .= " AND eType != 'UberX'";     
        }
        
        $total = '0.00';
        if ($id != "") {
            global $obj;
            $sql = "SELECT SUM(fWalletDebit) as totalAmount FROM trips AS tr WHERE vTripPaymentMode='Cash' AND eDriverPaymentStatus = 'Unsettelled' AND eSystem = 'General' AND iDriverId = '" . $id . "'" . $ssql . $etypeSql;
            $data = $obj->MySQLSelect($sql);
            $total = ($data[0]['totalAmount'] != "") ? $data[0]['totalAmount'] : '0.00';
        }
        return number_format($total, 2);
    }

    public function getAllPromocodeCountbyDriverId($id, $ssql) {
        
        $etypeSql = "";
        if(!isRideModuleAvailable()) {
            $etypeSql .= " AND eType != 'Ride'";
        }
        if(!isDeliveryModuleAvailable()) {
            $etypeSql .= " AND eType != 'Deliver' AND eType != 'Multi-Delivery'";
        }
        if(!isUberXModuleAvailable()) { 
            $etypeSql .= " AND eType != 'UberX'";     
        }
        
        $total = '0.00';
        if ($id != "") {
            global $obj;
            $sql = "SELECT SUM(fDiscount) as totalAmount FROM trips AS tr WHERE vTripPaymentMode='Cash' AND eDriverPaymentStatus = 'Unsettelled' AND eSystem = 'General' AND iDriverId = '" . $id . "'" . $ssql . $etypeSql;
            $data = $obj->MySQLSelect($sql);
            $total = ($data[0]['totalAmount'] != "") ? $data[0]['totalAmount'] : '0.00';
        }
        return number_format($total, 2);
    }

    public function getAllOutstandingAmountCountbyDriverId($id, $ssql) {
        
        $etypeSql = "";
        if(!isRideModuleAvailable()) {
            $etypeSql .= " AND eType != 'Ride'";
        }
        if(!isDeliveryModuleAvailable()) {
            $etypeSql .= " AND eType != 'Deliver' AND eType != 'Multi-Delivery'";
        }
        if(!isUberXModuleAvailable()) { 
            $etypeSql .= " AND eType != 'UberX'";     
        }
        
        $total = '0.00';
        if ($id != "") {
            global $obj;
            $sql = "SELECT SUM(fOutStandingAmount) as totalAmount FROM trips AS tr WHERE vTripPaymentMode='Cash' AND eDriverPaymentStatus = 'Unsettelled' AND eSystem = 'General' AND iDriverId = '" . $id . "'" . $ssql . $etypeSql;
            $data = $obj->MySQLSelect($sql);
            $total = ($data[0]['totalAmount'] != "") ? $data[0]['totalAmount'] : '0.00';
        }
        return number_format($total, 2);
    }

    public function getAllBookingAmountCountbyDriverId($id, $ssql) {
        $etypeSql = "";
        if(!isRideModuleAvailable()) {
            $etypeSql .= " AND eType != 'Ride'";
        }
        if(!isDeliveryModuleAvailable()) {
            $etypeSql .= " AND eType != 'Deliver' AND eType != 'Multi-Delivery'";
        }
        if(!isUberXModuleAvailable()) { 
            $etypeSql .= " AND eType != 'UberX'";     
        }
        
        $total = '0.00';
        if ($id != "") {
            global $obj;
            $sql = "SELECT SUM(tr.fHotelCommision) as totalHotelAmount FROM trips AS tr WHERE tr.vTripPaymentMode='Cash' AND tr.eDriverPaymentStatus = 'Unsettelled' AND tr.eSystem = 'General' AND tr.iDriverId = '" . $id . "'" . $ssql . $etypeSql;
            $data = $obj->MySQLSelect($sql);
            $total = ($data[0]['totalHotelAmount'] != "") ? $data[0]['totalHotelAmount'] : '0.00';
        }
        return number_format($total, 2);
    }

    public function getAllTipCountbyDriverId($id, $ssql) {
        $etypeSql = "";
        if(!isRideModuleAvailable()) {
            $etypeSql .= " AND eType != 'Ride'";
        }
        if(!isDeliveryModuleAvailable()) {
            $etypeSql .= " AND eType != 'Deliver' AND eType != 'Multi-Delivery'";
        }
        if(!isUberXModuleAvailable()) { 
            $etypeSql .= " AND eType != 'UberX'";     
        }
        
        $total = '0.00';
        if ($id != "") {
            global $obj;
            $sql = "SELECT SUM(fTipPrice) as totalAmount FROM trips AS tr WHERE eDriverPaymentStatus = 'Unsettelled' AND vTripPaymentMode='Card' AND tr.eSystem = 'General' AND iDriverId = '" . $id . "'" . $ssql . $etypeSql;
            $data = $obj->MySQLSelect($sql);
            $total = ($data[0]['totalAmount'] != "") ? $data[0]['totalAmount'] : '0.00';
        }
        return number_format($total, 2);
    }

    public function getTransforAmountbyDriverId($id, $ssql, $tip = '') {
        
        if(!isRideModuleAvailable()) {
            $ssql .= " AND eType != 'Ride'";
        }
        if(!isDeliveryModuleAvailable()) {
            $ssql .= " AND eType != 'Deliver' AND eType != 'Multi-Delivery'";
        }
        if(!isUberXModuleAvailable()) { 
            $ssql .= " AND eType != 'UberX'";     
        }
        
        $total = '0.00';
        if ($id != "") {
            global $obj;
            //get Cash commision
            $sql = "SELECT SUM(fCommision) AS totalAmount FROM trips AS tr WHERE eDriverPaymentStatus = 'Unsettelled' AND vTripPaymentMode='Cash' AND eSystem = 'General' AND iDriverId = '" . $id . "'" . $ssql;
            $data = $obj->MySQLSelect($sql);
            $cashCommision = ($data[0]['totalAmount'] != "") ? $data[0]['totalAmount'] : '0.00';

            //get OutstandingAmount from driver for cash  trips
            $sql = "SELECT SUM(fOutStandingAmount) AS totalAmount FROM trips AS tr WHERE eDriverPaymentStatus = 'Unsettelled' AND vTripPaymentMode='Cash' AND eSystem = 'General' AND iDriverId = '" . $id . "'" . $ssql;
            $data = $obj->MySQLSelect($sql);
            $OutstandingAmount = ($data[0]['totalAmount'] != "") ? $data[0]['totalAmount'] : '0.00';

            //get Booking commision
            $sql = "SELECT SUM(fHotelCommision) AS totalAmountBooking FROM trips AS tr WHERE eDriverPaymentStatus = 'Unsettelled' AND vTripPaymentMode='Cash' AND eSystem = 'General' AND iDriverId = '" . $id . "'" . $ssql;
            $data = $obj->MySQLSelect($sql);
            $hotelcashCommision = ($data[0]['totalAmountBooking'] != "") ? $data[0]['totalAmountBooking'] : '0.00';

            //get Card total with deduct commision and trip outstanding amount
            $sql = "SELECT IFNULL( SUM( IFNULL( fTripGenerateFare, 0 ) ) + SUM( IFNULL( fTipPrice, 0 ) ) , 0 ) - IFNULL( SUM( IFNULL( fCommision, 0 ) ) , 0 ) - IFNULL( SUM( IFNULL( fOutStandingAmount, 0 ) ) , 0 ) AS amounts FROM trips  AS tr WHERE eDriverPaymentStatus = 'Unsettelled' AND vTripPaymentMode='Card' AND eSystem = 'General' AND iDriverId = '" . $id . "'" . $ssql;
            $data = $obj->MySQLSelect($sql);
            $cardTotal = ($data[0]['amounts'] != "") ? $data[0]['amounts'] : '0.00';
            // if($tip != ''){
            // $cardTotal = str_replace(',','',$cardTotal)+str_replace(',','',$tip);
            // }
            //get Cash Trips Wallet and Promocode total  
            $sql = "SELECT IFNULL( SUM( IFNULL( fWalletDebit, 0 ) ) + SUM( IFNULL( fDiscount, 0 ) ) , 0 ) AS totalpromowalletamount FROM trips AS tr WHERE eDriverPaymentStatus = 'Unsettelled' AND vTripPaymentMode='Cash'  AND eSystem = 'General' AND iDriverId = '" . $id . "'" . $ssql;
            $data = $obj->MySQLSelect($sql);
            $walletpromocodeTotal = ($data[0]['totalpromowalletamount'] != "") ? $data[0]['totalpromowalletamount'] : '0.00';

            $total = number_format($cardTotal - $cashCommision - $OutstandingAmount + $walletpromocodeTotal - $hotelcashCommision, 2);
        }
        return $total;
    }

    public function getCompanyDetailsDashboard() {
        $cmp_ssql = "";
        if (SITE_TYPE == 'Demo') {
            $cmp_ssql = " And tRegistrationDate > '" . WEEK_DATE . "'";
        }
        global $obj;
        $sql = "SELECT count(iCompanyId) as Total FROM company WHERE eStatus != 'Deleted' $cmp_ssql order by tRegistrationDate desc";
        $data = $obj->MySQLSelect($sql);
        return $data[0]['Total'];
    }

    public function getDriverDetailsDashboard($status) {
        $cmp_ssql = "";
        if (SITE_TYPE == 'Demo') {
            $cmp_ssql = " And rd.tRegistrationDate > '" . WEEK_DATE . "'";
        }
        global $obj;
        $ssl = "";
        if (isset($status) && $status != "" && $status == "active") {
            $ssl = " AND rd.eStatus = '" . $status . "'";
        } else if (isset($status) && $status != "" && $status == "inactive") {
            $ssl = " AND rd.eStatus = '" . $status . "'";
        }

        $ssql1 = "AND (rd.vEmail != '' OR rd.vPhone != '')";

        $sql = "SELECT count(rd.iDriverId) as Total FROM register_driver rd LEFT JOIN company c ON rd.iCompanyId = c.iCompanyId and c.eStatus != 'Deleted' WHERE  rd.eStatus != 'Deleted'" . $ssl . $ssql1 . $cmp_ssql;
        $data = $obj->MySQLSelect($sql);

        return $data[0]['Total'];
    }

    public function getStoreDetailsDashboard($status) {
        global $obj;
        $cmp_ssql = "";
        if (SITE_TYPE == 'Demo') {
            $cmp_ssql = " And tRegistrationDate > '" . WEEK_DATE . "'";
        }
        $ssl = "";
        if (isset($status) && $status != "" && $status == "active") {
            $ssl = " AND eStatus = '" . $status . "'";
        } else if (isset($status) && $status != "" && $status == "inactive") {
            $ssl = " AND eStatus = '" . $status . "'";
        }

        $ssql1 = "AND (vEmail != '' OR vPhone != '')";

        $sql = "SELECT count(iCompanyId) as Total FROM company WHERE eStatus != 'Deleted' AND eSystem ='DeliverAll' " . $ssl . $ssql1 . $cmp_ssql;
        $data = $obj->MySQLSelect($sql);

        return $data[0]['Total'];
    }

    public function getVehicleDetailsDashboard() {
        $cmp_ssql = "";
        if (SITE_TYPE == 'Demo') {
            $cmp_ssql = " And rd.tRegistrationDate > '" . WEEK_DATE . "'";
        }
        global $obj;
        $sql = "SELECT count(dv.iDriverVehicleId) as Total
				FROM driver_vehicle dv, register_driver rd, make m, model md, company c
				WHERE
				  dv.eStatus != 'Deleted'
				  AND dv.iDriverId = rd.iDriverId
				  AND dv.iCompanyId = c.iCompanyId
				  AND dv.iModelId = md.iModelId
				  AND dv.iMakeId = m.iMakeId" . $cmp_ssql;
        $data = $obj->MySQLSelect($sql);

        return $data[0]['Total'];
    }

    public function getRiderDetailsDashboard() {
        $cmp_ssql = "";
        if (SITE_TYPE == 'Demo') {
            $cmp_ssql = " And tRegistrationDate > '" . WEEK_DATE . "'";
        }
        global $obj;
        $sql = "SELECT count(iUserId) as Total FROM register_user WHERE eStatus != 'Deleted'" . $cmp_ssql;
        $data = $obj->MySQLSelect($sql);

        return $data[0]['Total'];
    }

    public function getTripsDetailsDashboard() {
        $cmp_ssql = "";
        if (SITE_TYPE == 'Demo') {
            $cmp_ssql = " And tEndDate > '" . WEEK_DATE . "'";
        }
        
        $etypeSql = "";
        if(!isRideModuleAvailable()) {
            $etypeSql .= " AND eType != 'Ride'";
        }
        if(!isDeliveryModuleAvailable()) {
            $etypeSql .= " AND eType != 'Deliver' AND eType != 'Multi-Delivery'";
        }
        if(!isUberXModuleAvailable()) { 
            $etypeSql .= " AND eType != 'UberX'";     
        }
        
        global $obj;
        $sql = "SELECT count(iTripId) as Total FROM trips WHERE 1=1" . $cmp_ssql . $etypeSql;
        $data = $obj->MySQLSelect($sql);

        return $data[0]['Total'];
    }

    public function getTripStatesDashboard($tripStatus = NULL) {
        $cmp_ssql = "";
        if (SITE_TYPE == 'Demo') {
            $cmp_ssql = " And tStartDate > '" . WEEK_DATE . "'";
        }
        
        $etypeSql = "";
        if(!isRideModuleAvailable()) {
            $etypeSql .= " AND eType != 'Ride'";
        }
        if(!isDeliveryModuleAvailable()) {
            $etypeSql .= " AND eType != 'Deliver' AND eType != 'Multi-Delivery'";
        }
        if(!isUberXModuleAvailable()) { 
            $etypeSql .= " AND eType != 'UberX'";     
        }
        
        global $obj;
        $data = array();
        if ($tripStatus != "") {
            if ($tripStatus == "on ride") {
                $ssl = " AND (iActive = 'On Going Trip' OR iActive = 'Active') AND eCancelled='No'";
            } else if ($tripStatus == "cancelled") {
                $ssl = " AND (iActive = 'Canceled' OR eCancelled='yes')";
            } else if ($tripStatus == "finished") {
                $ssl = " AND iActive = 'Finished' AND eCancelled='No'";
            } else {
                $ssl = "";
            }

            $sql = "SELECT count(iTripId) as Total FROM trips WHERE 1" . $cmp_ssql . $ssl . $etypeSql;
            $data = $obj->MySQLSelect($sql);
        }
        return $data[0]['Total'];
    }

    public function getTripDateStatesDashboard($time) {
        global $obj;
        $data = array();
        $cmp_ssql = "";
        if (SITE_TYPE == 'Demo') {
            $cmp_ssql = " And tEndDate > '" . WEEK_DATE . "'";
        }
        if ($time == "month") {
            $startDate = date('Y-m') . "-00 00:00:00";
            $endDate = date('Y-m') . "-31 23:59:59";
            $ssl = " AND tTripRequestDate BETWEEN '" . $startDate . "' AND '" . $endDate . "'";
        } else if ($time == "year") {
            $startDate1 = date('Y') . "-00-00 00:00:00";
            $endDate1 = date('Y') . "-12-31 23:59:59";
            $ssl = " AND tTripRequestDate BETWEEN '" . $startDate1 . "' AND '" . $endDate1 . "'";
        } else {
            $startDate2 = date('Y-m-d') . " 00:00:00";
            $endDate2 = date('Y-m-d') . " 23:59:59";
            $ssl = " AND tTripRequestDate BETWEEN '" . $startDate2 . "' AND '" . $endDate2 . "'";
        }
        $etypeSql = "";
        if(!isRideModuleAvailable()) {
            $etypeSql .= " AND eType != 'Ride'";
        }
        if(!isDeliveryModuleAvailable()) {
            $etypeSql .= " AND eType != 'Deliver' AND eType != 'Multi-Delivery'";
        }
        if(!isUberXModuleAvailable()) { 
            $etypeSql .= " AND eType != 'UberX'";     
        }
        
        $sql = "SELECT count(iTripId) as Total FROM trips WHERE 1 " . $ssl . $cmp_ssql.$etypeSql;
        $data = $obj->MySQLSelect($sql);
        return $data[0]['Total'];
    }

    public function getDriverDateStatusDashboard($time) {
        $cmp_ssql = "";
        if (SITE_TYPE == 'Demo') {
            $cmp_ssql = " And rd.tRegistrationDate > '" . WEEK_DATE . "'";
        }
        global $obj;
        $data = array();
        if ($time == "month") {
            $startDate = date('Y-m') . "-00 00:00:00";
            $endDate = date('Y-m') . "-31 23:59:59";
            $ssl = " AND rd.tRegistrationDate BETWEEN '" . $startDate . "' AND '" . $endDate . "'";
        } else if ($time == "year") {
            $startDate1 = date('Y') . "-00-00 00:00:00";
            $endDate1 = date('Y') . "-12-31 23:59:59";
            $ssl = " AND rd.tRegistrationDate BETWEEN '" . $startDate1 . "' AND '" . $endDate1 . "'";
        } else {
            $startDate2 = date('Y-m-d') . " 00:00:00";
            $endDate2 = date('Y-m-d') . " 23:59:59";
            $ssl = " AND rd.tRegistrationDate BETWEEN '" . $startDate2 . "' AND '" . $endDate2 . "'";
        }
        $sql = "SELECT count(rd.iDriverId) as Total FROM register_driver rd LEFT JOIN company c ON rd.iCompanyId = c.iCompanyId and c.eStatus != 'Deleted' WHERE  rd.eStatus != 'Deleted'" . $ssl . $cmp_ssql;
        $data = $obj->MySQLSelect($sql);
        return $data[0]['Total'];
    }

    public function set_hour_min($times) {
        $hour = 0;
        $second = 0;
        $minute = floor($times / 60);
        if ($times < 60) {
            $minute = 0;
        }
        if ($minute > 60) {
            $hour = floor($minute / 60);
            $minute = floor($minute % 60);
        } else {
            $second = floor($times % 60);
        }
        $ansdata = Array("hour" => $hour, "minute" => $minute, "second" => $second);

        return $ansdata;
    }

    public function getLocationName($Name, $Id) {
        $cmp_ssql = "";
        if (SITE_TYPE == 'Demo') {
            $cmp_ssql = " And tRegistrationDate > '" . WEEK_DATE . "'";
        }
        global $obj;
        if ($Name == "country") {
            $sql = "SELECT vCountry FROM country WHERE iCountryId=" . $Id;
            $data = $obj->MySQLSelect($sql);
            if (count($data) > 0) {
                return $data[0]['vCountry'];
            } else {
                return "-";
            }
        } elseif ($Name == "state") {
            $sql = "SELECT vState FROM state WHERE iStateId=" . $Id;
            $data = $obj->MySQLSelect($sql);
            if (count($data) > 0) {
                return $data[0]['vState'];
            } else {
                return "-";
            }
        } else {
            $sql = "SELECT vCity FROM city WHERE iCityId=" . $Id;
            $data = $obj->MySQLSelect($sql);
            if (count($data) > 0) {
                return $data[0]['vCity'];
            } else {
                return "-";
            }
        }
    }

    public function get_left_days_jobsave($dend, $dstart) {
        $dayinpass = $dstart;
        $today = strtotime($dend);
        $dayinpass = strtotime($dayinpass);
        return round(abs($today - $dayinpass));
        // return round(abs($today-$dayinpass)/60/60);
    }

    public function mediaTimeDeFormater($seconds) {
        $ret = "";

        $hours = (string) floor($seconds / 3600);
        $secs = (string) $seconds % 60;
        $mins = (string) floor(($seconds - ($hours * 3600)) / 60);

        if (strlen($hours) == 1)
            $hours = "0" . $hours;
        if (strlen($secs) == 1)
            $secs = "0" . $secs;
        if (strlen($mins) == 1)
            $mins = "0" . $mins;

        if ($hours == 0) {
            if ($mins > 1) {
                $ret = "$mins mins";
            } else {
                $ret = "$mins min";
            }
        } else {
            $mint = "";
            if ($mins > 01) {
                $mint = "$mins mins";
            } else {
                $mint = "$mins min";
            }
            if ($hours > 1) {
                $ret = "$hours hrs $mint";
            } else {
                $ret = "$hours hr $mint";
            }
        }
        return $ret;
    }

    public function clean($str) {
        global $obj;
        $str = trim($str);
        // $str = mysqli_real_escape_string($str);
        $str = $obj->SqlEscapeString($str);
        $str = htmlspecialchars($str);
        $str = strip_tags($str);
        return($str);
    }

    public function DateTime($text, $time = 'yes') {

        if ($text == "" || $text == "0000-00-00 00:00:00" || $text == "0000-00-00")
            return "---";

        $date = @date('jS F, Y', @strtotime($text));
        if ($time == 'yes') {
            $date .= " " . @date('h:i a', @strtotime($text));
            ;
        }
        return $date;
    }

    /* if user is at login page */

    function go_to_home() {
        global $tconfig;

        $sess_iAdminUserId = isset($_SESSION['sess_iAdminUserId']) ? $_SESSION['sess_iAdminUserId'] : '';
        $sess_iGroupId = isset($_SESSION['sess_iGroupId']) ? $_SESSION['sess_iGroupId'] : '';

        if ($sess_iGroupId == '4') {
            if ($sess_iAdminUserId != "") {
                $url = $tconfig['tsite_url_main_admin'] . "create_request.php";
            }
        } else {
            if ($sess_iAdminUserId != "") {
                $url = $tconfig['tsite_url_main_admin'] . "dashboard.php";
            }
        }



        if (isset($url) && $url != '' && basename($_SERVER['PHP_SELF']) != $url) {
            // if user is at same page 
            echo'<script>window.location="' . $url . '";</script>';
            @header("Location:" . $url);
            exit;
        }
    }

    public function getTransforAmountbyRestaurant($id, $ssql) {
        $total = '0.00';
        if ($id != "") {
            global $obj;
            $sql = "SELECT IFNULL( SUM( IFNULL( fTotalGenerateFare, 0 ) ), 0 ) - IFNULL( SUM( IFNULL( fCommision, 0 ) ) , 0 ) - IFNULL( SUM( IFNULL( fDeliveryCharge, 0 ) ) , 0 ) - IFNULL( SUM( IFNULL( fOutStandingAmount, 0 ) ) , 0 )- IFNULL( SUM( IFNULL( fOffersDiscount, 0 ) ) , 0 ) AS amounts FROM orders  AS o WHERE eRestaurantPaymentStatus = 'Unsettelled' AND (o.iStatusCode = '6') AND iCompanyId = '" . $id . "'" . $ssql; // OR o.fRestaurantPayAmount > 0
            $data = $obj->MySQLSelect($sql);
            $amounts = ($data[0]['amounts'] != "") ? $data[0]['amounts'] : '0.00';

            $total = $amounts;
        }
        return $total;
    }

    public function getExpectedforAmountbyRestaurant($id, $ssql) {
        $total = '0.00';
        if ($id != "") {
            global $obj;
            $sql = "SELECT o.iOrderId,o.vOrderNo,o.iCompanyId,o.iDriverId,o.iUserId,o.tOrderRequestDate,o.fRestaurantPayAmount,o.fRestaurantPaidAmount,o.fTotalGenerateFare,o.fDeliveryCharge,o.fOffersDiscount,o.fCommision,o.eRestaurantPaymentStatus,o.ePaymentOption,o.iStatusCode FROM orders  AS o WHERE o.eRestaurantPaymentStatus = 'Unsettelled' AND (o.iStatusCode = '6' OR o.fRestaurantPayAmount > 0) AND o.iCompanyId = '" . $id . "'" . $ssql;
            $data = $obj->MySQLSelect($sql);
            foreach ($data as $key => $value) {
                $fCommision = $value['fCommision'];
                $fTotalGenerateFare = $value['fTotalGenerateFare'];
                $fDeliveryCharge = $value['fDeliveryCharge'];
                $fOffersDiscount = $value['fOffersDiscount'];
                $fRestaurantPayAmount = $value['fRestaurantPayAmount'];

                if ($value['iStatusCode'] == '7' || $value['iStatusCode'] == '8') {
                    $amounts = $fRestaurantPaidAmount;
                } else {
                    $amounts = $fTotalGenerateFare - $fCommision - $fDeliveryCharge - $fOffersDiscount;
                }
                $total += $amounts;
            }
        }
        return $total;
    }

    /* New added For Driver */

    public function getTransforAmountbyDeliveryDriverId($id, $ssql, $tip = '') {
        $etypeSql = "";
        if(!isRideModuleAvailable()) {
            $etypeSql .= " AND eType != 'Ride'";
        }
        if(!isDeliveryModuleAvailable()) {
            $etypeSql .= " AND eType != 'Deliver' AND eType != 'Multi-Delivery'";
        }
        if(!isUberXModuleAvailable()) { 
            $etypeSql .= " AND eType != 'UberX'";     
        }
        
        $total = '0.00';
        if ($id != "") {
            global $obj;
            //get Cash commision
            //$sql = "SELECT SUM(fDeliveryCharge) AS totalAmount FROM trips AS tr WHERE eDriverPaymentStatus = 'Unsettelled' AND iDriverId = '".$id."'".$ssql;
            $sql = "SELECT SUM(tr.fDeliveryCharge) AS totalAmount FROM trips AS tr LEFT JOIN orders as o on o.iOrderId=tr.iOrderId WHERE tr.eDriverPaymentStatus = 'Unsettelled' AND o.iStatusCode = 6 AND tr.iDriverId = '" . $id . "'" . $ssql . $etypeSql;
            $data = $obj->MySQLSelect($sql);
            $DelvieryCharges = ($data[0]['totalAmount'] != "") ? $data[0]['totalAmount'] : '0.00';

            $total = $DelvieryCharges;
        }
        return $total;
    }

    public function getPaymentToDriver($iOrderId) {
        global $obj;
        $sql = "SELECT trips.fDeliveryCharge FROM orders JOIN trips on orders.iOrderId=trips.iOrderId WHERE orders.iOrderId = $iOrderId";
        $data = $obj->MySQLSelect($sql);

        if (count($data) > 0) {
            $fDeliveryCharge = $data[0]['fDeliveryCharge'];
            return $fDeliveryCharge;
        } else {
            return "0";
        }
    }

    public function getPaymentToRestaurant($iOrderId) {
        global $obj;
        //include_once("../generalFunctions_dl_shark.php");
        include_once("../include_generalFunctions_dl.php");
        $eConfirm = checkOrderStatus($iOrderId, "2"); //it is done bc when store has not accepted ordr and admin cancel order at that time store payout is 0
        if($eConfirm=='No') {
            return "0";
        }
        $sql = "SELECT fTotalGenerateFare,fOffersDiscount,fDeliveryCharge,fCommision,fOutStandingAmount  FROM orders WHERE orders.iOrderId = $iOrderId and iStatusCode!=1";
        $data = $obj->MySQLSelect($sql);

        if (count($data) > 0) {

            $payment_to_restaurant = $data[0]['fTotalGenerateFare'] - $data[0]['fOffersDiscount'] - $data[0]['fDeliveryCharge'] - $data[0]['fCommision'] - $data[0]['fOutStandingAmount'];
            return $payment_to_restaurant;
        } else {
            return "0";
        }
    }

    public function getUnitToMiles($fPricePerKM, $eUnit) {
        if ($eUnit == "Miles") {
            $PricePerKM = $fPricePerKM * 0.621371;
        } else {
            $PricePerKM = $fPricePerKM;
        }
        return $PricePerKM;
    }

    public function TimeDifference($text1, $text2) {
        // $t1 = $text1;
        // $t2 = $text2;
        if ($text1 == "" || $text1 == "0000-00-00 00:00:00" || $text1 == "0000-00-00")
            return "---";
        else if ($text2 == "" || $text2 == "0000-00-00 00:00:00" || $text2 == "0000-00-00")
            return "---";

        // echo $text1." ".$text2;exit;
        $text1 = @strtotime($text1);
        $text2 = @strtotime($text2);
        $time_diff = $text2 - $text1;
        $diff = $this->secondsToTime($time_diff);
        return str_pad($diff['h'], 2, "0", STR_PAD_LEFT) . ":" . str_pad($diff['m'], 2, "0", STR_PAD_LEFT) . ":" . str_pad($diff['s'], 2, "0", STR_PAD_LEFT);
    }

    public function getaddress($lat, $lng) {
        $url = 'http://maps.googleapis.com/maps/api/geocode/json?latlng=' . trim($lat) . ',' . trim($lng) . '&sensor=false';
        $json = @file_get_contents($url);
        $data = json_decode($json);
        //echo "<pre>"; print_r($data); exit;
        //$status = $data->status;
        if (isset($data->status) && $data->status == "OK")
            return $data->results[0]->formatted_address;
        else
            return false;
    }

    public function secondsToTime($seconds) {
        // extract hours
        $hours = floor($seconds / (60 * 60));
        // extract minutes
        $divisor_for_minutes = $seconds % (60 * 60);
        $minutes = floor($divisor_for_minutes / 60);
        // extract the remaining seconds
        $divisor_for_seconds = $divisor_for_minutes % 60;
        $seconds = ceil($divisor_for_seconds);
        // return the final array
        $obj = array(
            "h" => (int) $hours,
            "m" => (int) $minutes,
            "s" => (int) $seconds,
        );
        return $obj;
    }

    //number Reverse formatting & Symbol reverse function  add 31-08-2019
    /*public function formateNumAsPerCurrency($amount, $code, $decimals = 2) {
        global $obj, $CURRENCY_DATA_ARR_FORMATTER;
        if (empty($CURRENCY_DATA_ARR_FORMATTER)) {
            $CURRENCY_DATA_ARR_FORMATTER_TMP = $obj->MySQLSelect("SELECT eReverseformattingEnable,vSymbol,vName,eReverseSymbolEnable,eDefault from  `currency`");

            foreach ($CURRENCY_DATA_ARR_FORMATTER_TMP as $CURRENCY_ITEM) {
                $CURRENCY_DATA_ARR_FORMATTER[$CURRENCY_ITEM['vName']] = $CURRENCY_ITEM;
            }
        }

        if (empty($code)) {
            foreach ($CURRENCY_DATA_ARR_FORMATTER as $CURRENCY_ITEM => $value) {
                if (strtoupper($value['eDefault']) == "YES") {
                    $code = $value['vName'];
                    break;
                }
            }
        }

        if (empty($CURRENCY_DATA_ARR_FORMATTER[$code])) {
            $currency_data = $obj->MySQLSelect("SELECT eReverseformattingEnable,vSymbol,vName,eReverseSymbolEnable,eDefault from `currency` WHERE  vName = '" . $code . "'");
            $CURRENCY_DATA_ARR_FORMATTER[$code] = $currency_data[0];
        }
        $db_sql_fn = $CURRENCY_DATA_ARR_FORMATTER[$code];

        if ($db_sql_fn['eReverseformattingEnable'] == "Yes") {
            $totalvalue = number_format($amount, $decimals, ',', '.');
        } else {
            $totalvalue = number_format($amount, $decimals, '.', ',');
        }

        if ($db_sql_fn['eReverseSymbolEnable'] == "Yes") {
            $symbolvalue = $totalvalue . " " . $db_sql_fn['vSymbol'];
        } else {
            $symbolvalue = $db_sql_fn['vSymbol'] . " " . $totalvalue;
        }

        return $symbolvalue;
    }*/

}

?>
