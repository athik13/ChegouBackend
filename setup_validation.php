<?php
include_once('common.php');

$sql = "SELECT * FROM setup_info";
$db_setup_info = $obj->MySQLSelect($sql);
define('ADMIN_URL_CLIENT', 'admin');
$sql_vehicle_category_table_name = $generalobj->getVehicleCategoryTblName();

/* Give Below File Permission First
 * project/setup_info/uploads
 * project/webimages and it's all folder
 * Permission Folder For Auto Delete Files : Root,Admin,Admin/action folder
 * Changes In File
 * 1) project/assets/libraries/configuration_variables.php - Change Folder Name
 * 2) project/assets/libraries/db_info.php - Change Db Info
 */
$query = "SELECT * FROM service_categories WHERE eStatus='Active'";
$db_service_categories = $obj->MySQLSelect($query);

$array_column = array_column($db_service_categories, 'iServiceId');
$matchresult = array_diff($array_column, $service_categories_ids_arr);
$matchresult2 = array_diff($service_categories_ids_arr, $array_column);
$errorcountsystemvalidation = $fileCount = $manualOrderFiles = 0;
$DONATION = $DRIVER_DESTINATION = $FAVOURITE_DRIVER = $FAVOURITE_STORE = $DRIVER_SUBSCRIPTION = $GOJEK_GOPAY = $MULTI_STOPOVER_POINTS = $MANUAL_STORE_ORDER_WEBSITE = $MANUAL_STORE_ORDER_STORE_PANEL = $MANUAL_STORE_ORDER_ADMIN_PANEL = $Deliverall = $UberX = $Fly = "No"; // Added By HJ On 12-07-2019
?>
<style>
    ol.validation li {
        background: #cce5ff;
        margin: 5px;
        padding: 10px;
    }
</style>
<?php if (isset($filePanel) && $filePanel == "Admin") { ?>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>
    <?php
}
$deleteFileArr = $mianWebserviceArr = array();
if (count($db_setup_info) > 0) {
    $ePackageType = $db_setup_info[0]['ePackageType'];
    $eProductType = $db_setup_info[0]['eProductType'];
    $eDeliveryType = $db_setup_info[0]['eDeliveryType'];
    $eEnableKiosk = $eEnableHotel = $eCubeX = $eCubejekX = $eRideX = $eDeliverallX = $eFoodX = "No";
    $eConfigurationApplied = "Yes";
    if (isset($db_setup_info[0]['eEnableKiosk'])) {
        $eEnableKiosk = $db_setup_info[0]['eEnableKiosk'];
    }
    if (isset($db_setup_info[0]['eEnableHotel'])) {
        $eEnableHotel = $db_setup_info[0]['eEnableHotel'];
    }
    if (isset($db_setup_info[0]['eConfigurationApplied'])) {
        $eConfigurationApplied = $db_setup_info[0]['eConfigurationApplied'];
    }
    if (isset($db_setup_info[0]['eCubeX'])) {
        $eCubeX = $db_setup_info[0]['eCubeX'];
    }
    if (isset($db_setup_info[0]['eCubejekX'])) {
        $eCubejekX = $db_setup_info[0]['eCubejekX'];
    }
    if (isset($db_setup_info[0]['eRideX'])) {
        $eRideX = $db_setup_info[0]['eRideX'];
    }
    if (isset($db_setup_info[0]['eDeliverallX'])) {
        $eDeliverallX = $db_setup_info[0]['eDeliverallX'];
    }
    if (isset($db_setup_info[0]['eFoodX'])) {
        $eFoodX = $db_setup_info[0]['eFoodX'];
    }
    //Added By HJ On 12-07-2019 For Get New Addon Configuration Start
    $addOnData = json_decode($db_setup_info[0]['lAddOnConfiguration'], true);
    foreach ($addOnData as $addOnKey => $addOnVal) {
        $$addOnKey = $addOnVal;
    }
    //Added By HJ On 12-07-2019 For Get New Addon Configuration End
    $applyConfiguration = 0; //0-Not Run Configuration Setting,1-Set Default Configuration Setting As Per Product and Package
    if ($eConfigurationApplied == "No") {
        $applyConfiguration = 1; //0-Not Run Configuration Setting,1-Set Default Configuration Setting As Per Product and Package
    }
    $setupShFile = dirname(__FILE__) . "/setup_info/setup.sh";
    $shFileCommand = "sh " . $setupShFile;
    $permissionTxt = "";
    if (!is_writable($setupShFile)) {
        $permissionTxt = "Note: Please assign 777 Permission to " . $setupShFile . " File";
    }
    //Added By HJ On 17-12-2019 For Google Setting Update Start
    $tsite_url = $tconfig['tsite_url'];
    $obj->sql_query("UPDATE `configurations` SET `vValue` = '$tsite_url' WHERE vName = 'GOOGLE_PLUS_SITE_NAME'");
    $obj->sql_query("UPDATE `configurations` SET `vValue` = '" . $tsite_url . "gpconnect.php' WHERE vName = 'GOOGLE_PLUS_OAUTH_REDIRECT_URI'");
    //Added By HJ On 17-12-2019 For Google Setting Update End
    //echo $permissionTxt;die;
    echo "<div style='background:#ff0000;padding:20px;color:#ffffff;font-size:25px;text-align:center;display:none;' id='permissionmsg'>Please tell Chirag sir or Anurag sir to run below command from command prompt to delete below files.<br><b><u>" . $shFileCommand . "</u></b><br>" . $permissionTxt . "</div>";
    echo '<ol class="validation">';
    /* if(ENABLEHOTELPANEL == 'Yes'){ 
      $errorcountsystemvalidation +=1;
      echo "<li>Please Update ENABLEHOTELPANEL value as 'No' in configuration_variables file.</li>";
      } */
    if (strtoupper($ePackageType) != strtoupper($PACKAGE_TYPE)) {
        $errorcountsystemvalidation += 1;
        echo "<li>Please Set Project Package Type : " . $ePackageType . "</li>";
    }
    $configAppType = "";
    $getAppType = $obj->MySQLSelect("SELECT vValue FROM configurations WHERE vName='APP_TYPE'");
    if (count($getAppType) > 0) {
        $configAppType = $getAppType[0]['vValue'];
    }
    $eProductTypeCheck = $eProductType;
    if ($eProductType == "Deliverall") {
        $eProductTypeCheck = "Ride-Delivery-UberX";
    }
    // Added BY HJ On 14-10-2019 As Per Discuss With KS For Solved Foodonly Setup Isssue Start
    if ($eProductTypeCheck == "Foodonly" && $APP_TYPE == "Ride-Delivery-UberX") {
        //Foodonly's App Type Always Ride-Delivery-UberX
    } else if (strtoupper($eProductTypeCheck) != strtoupper($APP_TYPE)) {
        $errorcountsystemvalidation += 1;
        echo "<li>Please Set Project App Type : " . $eProductType . "</li>";
    }
    // Added BY HJ On 14-10-2019 As Per Discuss With KS For Solved Foodonly Setup Isssue End
    // Commented BY HJ On 14-10-2019 As Per Discuss With KS For Solved Foodonly Setup Isssue Start
    /* if (strtoupper($eProductTypeCheck) != strtoupper($APP_TYPE)) {
      $errorcountsystemvalidation += 1;
      echo "<li>Please Set Project App Type : " . $eProductType . "</li>";
      } */
    // Commented BY HJ On 14-10-2019 As Per Discuss With KS For Solved Foodonly Setup Isssue End
    if (strtoupper($configAppType) != strtoupper($APP_TYPE)) {
        $errorcountsystemvalidation += 1;
        echo "<li>Please Set Project App Type In Configuration Table : " . $eProductType . "</li>";
    }
    //Added By HJ On 02-03-2019 For Remove Main Webservice File Start
    if ($eProductType == "Ride" || $eProductType == "Delivery" || $eProductType == "UberX" || $eProductType == "Ride-Delivery") {
        $mianWebserviceArr = array("webservice_dl_shark.php", "generalFunctions_dl_shark.php");
    } else if ($eProductType == "Foodonly" || $eProductType == "Deliverall" || ONLYDELIVERALL == "Yes") {
        $mianWebserviceArr = array("include_webservice_shark.php", "include_generalFunctions_shark.php");
    }
    $unUsefulFiles = array("test_socket.php", "chkR.php", "test_socket.php", "resizeImg - Copy.php", "trip_tracking - Copy.php", "expired_documents.php", "dummy_data_insert.php", "dummy_data_insert-14052019.php", "dummy_data_insert_gojek.php", "dummy_data_insert_taxi.php", "set_demo_store_img.php", "profile_190718.php", "cron_notification_email07082018.php", "=index.php", "=sign-in.php", "1.php", "test.php", "test_socket_orderdata.php", "test_socket_publish.php", "test_socket_watch.php", "test_socket1.php", "testa.php", "trip_tracking_test.php", "knowledgebase.php", "knowledgebase_design.php", "knowledgebase_detail.php", "knowledgebase_search.php", "knowledgebase_search_list.php", "knowledgebase2.php", "labelsDemo.php", "labelsDemo1.php", "labelsupdate.php", "language_popup.php", "language_save.php", "mobiconfiguration.php", "driver_action_new.php", "dummy_data_insert_04112019.php", "dummy_data_insert_bk_121219.php", "dummy_data_insert_final.php", "faq_new.php", "flag_files.php", "index-new.php", "captcha_code_news_file.php", "1.html", "2.php", "admin/map_tracking_old.php", "admin/home_content_cubejekx_action.php-14-12", "admin/invoicekp1.php", "settings_orig.php", "faq_categories_action_cd943.php", "cx-fareestimate1.php", ADMIN_URL_CLIENT . "help_detail_categories_action_cd943.php", ADMIN_URL_CLIENT . "faq_categories_action_cd943.php");
    $finalRootFileArr = array_merge($mianWebserviceArr, $unUsefulFiles);
    foreach ($finalRootFileArr as $key => $filename) {
        if (file_exists(dirname(__FILE__) . "/" . $filename)) {
            $errorcountsystemvalidation += 1;
            echo "<li>Please Delete File From root folder : " . $filename . "</li>";
            $deleteFileArr[] = dirname(__FILE__) . "/" . $filename;
        }
    }
    //Added By HJ On 02-03-2019 For Remove Main Webservice File End
    if ($eProductType == 'Ride') {
        $ridewebservicefilearray = array("include/uberx/include_webservice_uberx.php", "include/uberx/action_booking_admin.php", "include/delivery/include_webservice_delivery.php", "include/delivery/add_booking_admin4.php", "include/ride-delivery/add_booking_admin1.php", "include/ride-delivery/add_booking_admin3.php", "include/ride-delivery/add_booking_admin7.php", "include/ride-delivery/ajax_get_vehicletype_airportsurcharge_admin1.php", "include/ride-delivery/ajax_get_vehicletype_fixfare_admin1.php", "include/ride-delivery-uberx/add_booking_admin2.php", "include/ride-delivery-uberx/add_booking_admin5.php", "include/ride-delivery-uberx/add_booking_admin8.php", "include/ride-delivery-uberx/ajax_get_vehicletype_airportsurcharge_admin2.php", "include/ride-delivery-uberx/ajax_get_vehicletype_fixfare_admin2.php");
        foreach ($ridewebservicefilearray as $key => $filename) {
            if (file_exists(dirname(__FILE__) . "/" . $filename)) {
                $errorcountsystemvalidation += 1;
                echo "<li>Please Delete File From include folder from root " . $filename . "</li>";
                $deleteFileArr[] = dirname(__FILE__) . "/" . $filename;
            }
        }
        $obj->sql_query("DELETE FROM vehicle_type WHERE eIconType!='Car'");
    } else if ($eProductType == 'Delivery') {
        $deliverywebservicefilearray = array("include/uberx/include_webservice_uberx.php", "include/uberx/action_booking_admin.php", "include/ride/include_webservice_ride.php", "include/ride/add_booking_admin6.php", "include/ride-delivery/add_booking_admin1.php", "include/ride-delivery/add_booking_admin3.php", "include/ride-delivery/add_booking_admin7.php", "include/ride-delivery/ajax_get_vehicletype_airportsurcharge_admin1.php", "include/ride-delivery/ajax_get_vehicletype_fixfare_admin1.php", "include/ride-delivery-uberx/add_booking_admin2.php", "include/ride-delivery-uberx/add_booking_admin5.php", "include/ride-delivery-uberx/add_booking_admin8.php", "include/ride-delivery-uberx/ajax_get_vehicletype_airportsurcharge_admin2.php", "include/ride-delivery-uberx/ajax_get_vehicletype_fixfare_admin2.php");
        foreach ($deliverywebservicefilearray as $key => $filename) {
            if (file_exists(dirname(__FILE__) . "/" . $filename)) {
                $errorcountsystemvalidation += 1;
                echo "<li>Please Delete File From include folder from root " . $filename . "</li>";
                $deleteFileArr[] = dirname(__FILE__) . "/" . $filename;
            }
        }
        $obj->sql_query("DELETE FROM admin_groups WHERE iGroupId ='4'");
    } else if ($eProductType == 'UberX') {
        $ufxwebservicefilearray = array("include/delivery/include_webservice_delivery.php", "include/delivery/add_booking_admin4.php", "include/ride/include_webservice_ride.php", "include/ride/add_booking_admin6.php", "include/ride-delivery/add_booking_admin1.php", "include/ride-delivery/add_booking_admin3.php", "include/ride-delivery/add_booking_admin7.php", "include/ride-delivery/ajax_get_vehicletype_airportsurcharge_admin1.php", "include/ride-delivery/ajax_get_vehicletype_fixfare_admin1.php", "include/ride-delivery-uberx/add_booking_admin2.php", "include/ride-delivery-uberx/add_booking_admin5.php", "include/ride-delivery-uberx/add_booking_admin8.php", "include/ride-delivery-uberx/ajax_get_vehicletype_airportsurcharge_admin2.php", "include/ride-delivery-uberx/ajax_get_vehicletype_fixfare_admin2.php");
        foreach ($ufxwebservicefilearray as $key => $filename) {
            if (file_exists(dirname(__FILE__) . "/" . $filename)) {
                $errorcountsystemvalidation += 1;
                echo "<li>Please Delete File From include folder from root " . $filename . "</li>";
                $deleteFileArr[] = dirname(__FILE__) . "/" . $filename;
            }
        }
    } else if ($eProductType == 'Ride-Delivery') {
        $ridedeliverywebservicefilearray = array("include/ride-delivery-uberx/add_booking_admin2.php", "include/ride-delivery-uberx/add_booking_admin5.php", "include/ride-delivery-uberx/add_booking_admin8.php", "include/ride-delivery-uberx/ajax_get_vehicletype_airportsurcharge_admin2.php", "include/ride-delivery-uberx/add_booking_admin2.php", "include/uberx/action_booking_admin.php", "include/uberx/include_webservice_uberx.php");
        foreach ($ridedeliverywebservicefilearray as $key => $filename) {
            if (file_exists(dirname(__FILE__) . "/" . $filename)) {
                $errorcountsystemvalidation += 1;
                echo "<li>Please Delete File From include folder from root " . $filename . "</li>";
                $deleteFileArr[] = dirname(__FILE__) . "/" . $filename;
            }
        }
    }

    if ($eProductType != 'Delivery' && $eProductType != 'Ride-Delivery-UberX' && $eProductType != 'Ride-Delivery' && $eProductType != 'Ride-Delivery-UberX-Shark') {
        if (ENABLE_MULTI_DELIVERY == 'Yes') {
            $errorcountsystemvalidation += 1;
            echo "<li>Please Update ENABLE_MULTI_DELIVERY value as 'No' in configuration_variables file.</li>";
        }
    }
    //Added By HJ On 14-10-2019 For Remove SP App Files Start
    if ($eProductType != "Ride-Delivery-UberX" && $eProductType != "UberX" && $eProductType == 'Ride-Delivery-UberX-Shark') {
        $uberxAdmnFilesArr = array(ADMIN_URL_CLIENT . "/action_driver_service_request.php", ADMIN_URL_CLIENT . "/driver_service_request.php");
        foreach ($uberxAdmnFilesArr as $key => $filename) {
            if (file_exists(dirname(__FILE__) . "/" . $filename)) {
                $errorcountsystemvalidation += 1;
                echo "<li>Please Delete File From admin folder " . $filename . "</li>";
                $deleteFileArr[] = dirname(__FILE__) . "/" . $filename;
            }
        }
    }
    //Added By HJ On 14-10-2019 For Remove SP App Files End
    if ($eProductType == 'Delivery' || $eProductType == 'Ride-Delivery-UberX' || $eProductType == 'Ride-Delivery-UberX-Shark' || $eProductType == 'Ride-Delivery') {
        if ($eDeliveryType == 'Multi') {
            if (ENABLE_MULTI_DELIVERY == 'No') {
                $errorcountsystemvalidation += 1;
                echo "<li>Please Update ENABLE_MULTI_DELIVERY value as 'Yes' in configuration_variables file.</li>";
            }
        }
    }

    if ($eProductType == 'UberX') {
        //$sql1 = "UPDATE configurations SET `eAdminDisplay` = 'No' WHERE (vName = 'DRIVER_REQUEST_METHOD' || vName = 'ENABLE_HAIL_RIDES' || vName='ENABLE_ROUTE_CALCULATION_MULTI' || vName='DELIVERY_VERIFICATION_METHOD' || vName='ENABLE_ROUTE_OPTIMIZE_MULTI' || vName='MAX_ALLOW_NUM_DESTINATION_MULTI')";
        //$obj->sql_query($sql1); // By HJ On 07-03-2019
    }

    //if ($eProductType != 'UberX' && $eProductType != 'Ride-Delivery-UberX' && $eProductType != 'Ride-Delivery-UberX-Shark') { // Commented By HJ On 14-03-2019 As Per Discuss With KS Sir
    if ($eProductType == 'Ride' || ONLYDELIVERALL == "Yes") { // Added By HJ On 14-03-2019 As Per Discuss With KS Sir
        $uberxfilearray = array(ADMIN_URL_CLIENT . "/service_type.php", ADMIN_URL_CLIENT . "/vehicle_category.php", ADMIN_URL_CLIENT . "/left_menu_ufx_array.php", ADMIN_URL_CLIENT . "/left_menu_ufx.php", ADMIN_URL_CLIENT . "/left_menu_ufx_n.php", ADMIN_URL_CLIENT . "/vehicle_sub_category.php", ADMIN_URL_CLIENT . "/vehicle_category_action.php", ADMIN_URL_CLIENT . "/service_type_action.php", ADMIN_URL_CLIENT . "/action/service_type.php", ADMIN_URL_CLIENT . "/add_availability.php", ADMIN_URL_CLIENT . "/manage_service_type.php", ADMIN_URL_CLIENT . "/action/vehicle_category.php", ADMIN_URL_CLIENT . "/action/vehicle_sub_category.php");
        foreach ($uberxfilearray as $key => $filename) {
            if (file_exists(dirname(__FILE__) . "/" . $filename)) {
                $errorcountsystemvalidation += 1;
                echo "<li>Please Delete File From Admin Panel " . $filename . "</li>";
                $deleteFileArr[] = dirname(__FILE__) . "/" . $filename;
            }
        }
    }
    if ($eProductType != 'Delivery' && $eProductType != 'Ride-Delivery-UberX' && $eProductType != 'Ride-Delivery' && $eProductType != 'Ride-Delivery-UberX-Shark') {
        if ($eDeliveryType != 'Multi') {
            $multifilearray = array(ADMIN_URL_CLIENT . "/invoice_multi_delivery.php");
            foreach ($multifilearray as $key => $filename) {
                if (file_exists(dirname(__FILE__) . "/" . $filename)) {
                    $errorcountsystemvalidation += 1;
                    echo "<li>Please Delete File From Admin Panel " . $filename . "</li>";
                    $deleteFileArr[] = dirname(__FILE__) . "/" . $filename;
                }
            }
        }
        $deliveryfilearray = array(ADMIN_URL_CLIENT . "/package_type.php", ADMIN_URL_CLIENT . "/package_type_action.php", ADMIN_URL_CLIENT . "/action/package_type.php");
        foreach ($deliveryfilearray as $key => $filename) {
            if (file_exists(dirname(__FILE__) . "/" . $filename)) {
                $errorcountsystemvalidation += 1;
                echo "<li>Please Delete File From Admin Panel " . $filename . "</li>";
                $deleteFileArr[] = dirname(__FILE__) . "/" . $filename;
            }
        }
    }
    if ($ePackageType != "shark") {
        $blockDriverFiled = array(ADMIN_URL_CLIENT . "/blocked_driver.php", ADMIN_URL_CLIENT . "/blocked_rider.php", ADMIN_URL_CLIENT . "/action/blocked_driver.php", ADMIN_URL_CLIENT . "/action/blocked_rider.php");
        foreach ($blockDriverFiled as $key => $filename) {
            if (file_exists(dirname(__FILE__) . "/" . $filename)) {
                $errorcountsystemvalidation += 1;
                echo "<li>Please Delete File From Admin Panel " . $filename . "</li>";
                $deleteFileArr[] = dirname(__FILE__) . "/" . $filename;
            }
        }
    }
    /* if (($eProductType == "Ride" || $eProductType == "Ride-Delivery" || $eProductType == "Ride-Delivery-UberX") && ENABLEKIOSKPANEL == "Yes") {
      $errorcountsystemvalidation += 1;
      echo "<li>Please Update ENABLEKIOSKPANEL value as 'No' in configuration_variables file.</li>";
      } */
    $hotelPanel = isHotelPanelEnable();
    $kioskPanel = isKioskPanelEnable();
    $hotelPanelEnable = $kioskPanelEnable = "No";
    if ($hotelPanel > 0) {
        $hotelPanelEnable = "Yes";
    }
    if ($kioskPanel > 0) {
        $kioskPanelEnable = "Yes";
    }
    if ($kioskPanelEnable != $eEnableKiosk) {
        $errorcountsystemvalidation += 1;
        echo "<li>Please Update ENABLEKIOSKPANEL value as '$eEnableKiosk' in configuration_variables file.</li>";
    }
    if ($hotelPanelEnable != $eEnableHotel) {
        $errorcountsystemvalidation += 1;
        echo "<li>Please Update ENABLEHOTELPANEL value as '$eEnableHotel' in configuration_variables file.</li>";
    }
    if ($hotelPanelEnable == 'No') {
        $hotelfilearray = array(ADMIN_URL_CLIENT . "/hotel_rider.php", ADMIN_URL_CLIENT . "/hotel_rider_action.php", ADMIN_URL_CLIENT . "/hotel_payment_report.php", ADMIN_URL_CLIENT . "/hotel_index.php", ADMIN_URL_CLIENT . "/hotel_booking.php", ADMIN_URL_CLIENT . "/hotel_banner_action.php", ADMIN_URL_CLIENT . "/hotel_banner.php", ADMIN_URL_CLIENT . "/export_hotel_pay_details.php", ADMIN_URL_CLIENT . "/create_request.php", ADMIN_URL_CLIENT . "/action/hotel_payment_report.php", ADMIN_URL_CLIENT . "/action/hotel_rider.php");
        foreach ($hotelfilearray as $key => $filename) {
            if (file_exists(dirname(__FILE__) . "/" . $filename)) {
                $errorcountsystemvalidation += 1;
                echo "<li>Please Delete File From Admin Panel " . $filename . "</li>";
                $deleteFileArr[] = dirname(__FILE__) . "/" . $filename;
            }
        }
    }
    if ($kioskPanelEnable == "No") {
        $kioskFileArr = array(ADMIN_URL_CLIENT . "/visit.php", "/visit_address_action.php", "/action/visit.php");
        foreach ($kioskFileArr as $key => $filename) {
            if (file_exists(dirname(__FILE__) . "/" . $filename)) {
                $errorcountsystemvalidation += 1;
                echo "<li>Please Delete File From Admin Panel " . $filename . "</li>";
                $deleteFileArr[] = dirname(__FILE__) . "/" . $filename;
            }
        }
    }
    if ($eProductType != 'Ride-Delivery-UberX' && $eProductType != 'Ride-Delivery-UberX-Shark') {
        $cubejekfilearray = array(ADMIN_URL_CLIENT . "/home_content_new.php", ADMIN_URL_CLIENT . "/home_content_action_new.php", ADMIN_URL_CLIENT . "/app_home_settings.php", ADMIN_URL_CLIENT . "/app_home_settings_action.php");
        foreach ($cubejekfilearray as $key => $filename) {
            if (file_exists(dirname(__FILE__) . "/" . $filename)) {
                $errorcountsystemvalidation += 1;
                echo "<li>Please Delete File From Admin Panel " . $filename . "</li>";
                $deleteFileArr[] = dirname(__FILE__) . "/" . $filename;
            }
        }
    }

    if ($eProductType != 'Ride' && $eProductType != 'Ride-Delivery' && $eProductType != 'Ride-Delivery-UberX' && $eProductType != 'Ride-Delivery-UberX-Shark') {
        $ridefilearray = array(ADMIN_URL_CLIENT . "/user_profile_master_action.php", ADMIN_URL_CLIENT . "/user_profile_master.php", ADMIN_URL_CLIENT . "/trip_reason.php", ADMIN_URL_CLIENT . "/trip_reason_action.php", ADMIN_URL_CLIENT . "/profile.php", ADMIN_URL_CLIENT . "/organization_document_fetch.php", ADMIN_URL_CLIENT . "/organization.php", ADMIN_URL_CLIENT . "/organization_action.php", ADMIN_URL_CLIENT . "/org_payment_report.php", ADMIN_URL_CLIENT . "/org_cancellation_payment_report.php", ADMIN_URL_CLIENT . "/location-airport.php", ADMIN_URL_CLIENT . "/location_action_airport.php", ADMIN_URL_CLIENT . "/action/location-airport.php", ADMIN_URL_CLIENT . "/action/trip_reason.php", ADMIN_URL_CLIENT . "/action/user_profile_master.php");
        foreach ($ridefilearray as $key => $filename) {
            if (file_exists(dirname(__FILE__) . "/" . $filename)) {
                $errorcountsystemvalidation += 1;
                echo "<li>Please Delete File From Admin Panel " . $filename . "</li>";
                $deleteFileArr[] = dirname(__FILE__) . "/" . $filename;
            }
        }
    }
    if ($eProductType == 'Foodonly') {
        /* $sql2 = "UPDATE configurations_cubejek SET `eStatus` = 'Inactive' WHERE (vName = 'GROCERY_APP_SHOW_SELECTION' || vName = 'GROCERY_APP_GRID_ICON_NAME' || vName = 'GROCERY_APP_BANNER_IMG_NAME' || vName = 'GROCERY_APP_DETAIL_BANNER_IMG_NAME' || vName = 'GROCERY_APP_DETAIL_GRID_ICON_NAME' || vName = 'GROCERY_APP_PACKAGE_NAME' || vName = 'GROCERY_APP_IOS_APP_ID' || vName = 'GROCERY_APP_IOS_PACKAGE_NAME' || vName = 'GROCERY_APP_SERVICE_ID' || vName = 'DELIVER_ALL_APP_IOS_PACKAGE_NAME' || vName = 'DELIVER_ALL_APP_IOS_APP_ID' || vName = 'DELIVER_ALL_APP_PACKAGE_NAME' || vName = 'DELIVER_ALL_APP_DETAIL_GRID_ICON_NAME' || vName = 'DELIVER_ALL_APP_DETAIL_BANNER_IMG_NAME' || vName = 'DELIVER_ALL_APP_BANNER_IMG_NAME' || vName='DELIVER_ALL_APP_GRID_ICON_NAME' || vName = 'DELIVER_ALL_APP_SHOW_SELECTION' )";
          $obj->sql_query($sql2); */
    }

    if ($eProductType != 'Foodonly' && $eProductType != 'Deliverall') {
        if ($ePackageType == 'standard' || $ePackageType == 'enterprise') {
            if ($eProductType != 'Ride-Delivery-UberX-Shark' && $eProductType != 'Ride-Delivery-UberX') {
                /* $sql2 = "UPDATE configurations_cubejek SET `eStatus` = 'Inactive' WHERE (vName = 'FOOD_APP_SHOW_SELECTION' || vName = 'FOOD_APP_GRID_ICON_NAME' || vName = 'FOOD_APP_BANNER_IMG_NAME' || vName = 'FOOD_APP_DETAIL_BANNER_IMG_NAME' || vName = 'FOOD_APP_DETAIL_GRID_ICON_NAME' || vName = 'FOOD_APP_PACKAGE_NAME' || vName = 'FOOD_APP_IOS_APP_ID' || vName = 'FOOD_APP_IOS_PACKAGE_NAME'  || vName = 'FOOD_APP_SERVICE_ID' || vName = 'GROCERY_APP_SHOW_SELECTION' || vName = 'GROCERY_APP_GRID_ICON_NAME' || vName = 'GROCERY_APP_BANNER_IMG_NAME' || vName = 'GROCERY_APP_DETAIL_BANNER_IMG_NAME' || vName = 'GROCERY_APP_DETAIL_GRID_ICON_NAME' || vName = 'GROCERY_APP_PACKAGE_NAME' || vName = 'GROCERY_APP_IOS_APP_ID' || vName = 'GROCERY_APP_IOS_PACKAGE_NAME' || vName = 'GROCERY_APP_SERVICE_ID' || vName = 'DELIVER_ALL_APP_IOS_PACKAGE_NAME' || vName = 'DELIVER_ALL_APP_IOS_APP_ID' || vName = 'DELIVER_ALL_APP_PACKAGE_NAME' || vName = 'DELIVER_ALL_APP_DETAIL_GRID_ICON_NAME' || vName = 'DELIVER_ALL_APP_DETAIL_BANNER_IMG_NAME' || vName = 'DELIVER_ALL_APP_BANNER_IMG_NAME' || vName='DELIVER_ALL_APP_GRID_ICON_NAME' || vName = 'DELIVER_ALL_APP_SHOW_SELECTION' )";
                  $obj->sql_query($sql2);
                 */
                $s1 = "DROP TABLE IF EXISTS `cuisine`, `food_menu`, `service_categories`, `food_menu_images`, `orders`, `order_details`, `order_later`, `order_status`, `order_status_logs`, `menuitem_options`, `menu_items`, `language_label_6`, `language_label_5`";
                $obj->sql_query($s1);
            }

            $foodfilearray = array(ADMIN_URL_CLIENT . "/cuisine.php", ADMIN_URL_CLIENT . "/cuisine_action.php", ADMIN_URL_CLIENT . "/delivery_charges_action.php", ADMIN_URL_CLIENT . "/delivery_charges.php", ADMIN_URL_CLIENT . "/restaurants_pay_report.php", ADMIN_URL_CLIENT . "/food_menu.php", ADMIN_URL_CLIENT . "/food_menu_action.php", ADMIN_URL_CLIENT . "/store.php", ADMIN_URL_CLIENT . "/store_action.php", ADMIN_URL_CLIENT . "/store_banner_action.php", ADMIN_URL_CLIENT . "/store_banner.php", ADMIN_URL_CLIENT . "/store_cancel_reason.php", ADMIN_URL_CLIENT . "/store_cancel_reason_action.php", ADMIN_URL_CLIENT . "/store_dashboard.php", ADMIN_URL_CLIENT . "/store_document_action.php", ADMIN_URL_CLIENT . "/store_document_fetch.php", ADMIN_URL_CLIENT . "/store_driver_pay_report.php", ADMIN_URL_CLIENT . "/store_payment_report.php", ADMIN_URL_CLIENT . "/store_review.php", ADMIN_URL_CLIENT . "/store_vehicle_type.php", ADMIN_URL_CLIENT . "/store_vehicle_type_action.php", ADMIN_URL_CLIENT . "/store-dashboard.php", ADMIN_URL_CLIENT . "/order_invoice.php", ADMIN_URL_CLIENT . "/order_status.php", ADMIN_URL_CLIENT . "/menu_item_action.php", ADMIN_URL_CLIENT . "/order_status_action.php", ADMIN_URL_CLIENT . "/menu_item.php", ADMIN_URL_CLIENT . "/action/food_menu.php", ADMIN_URL_CLIENT . "/action/ordar_status_type.php", ADMIN_URL_CLIENT . "/action/restaurants_pay_report.php", ADMIN_URL_CLIENT . "/action/store.php", ADMIN_URL_CLIENT . "/action/store_cancel_reason.php", ADMIN_URL_CLIENT . "/action/store_driver_pay_report.php", ADMIN_URL_CLIENT . "/action/store_payment_report.php", ADMIN_URL_CLIENT . "/action/store_review.php", ADMIN_URL_CLIENT . "/action/store_vehicle_type.php", ADMIN_URL_CLIENT . "/action/menu_item.php", ADMIN_URL_CLIENT . "/action/cuisine.php", ADMIN_URL_CLIENT . "/action/delivery_charges.php", ADMIN_URL_CLIENT . "/service_provider.php", ADMIN_URL_CLIENT . "/service_provider_action.php", ADMIN_URL_CLIENT . "/service_category.php", ADMIN_URL_CLIENT . "/service_category_action.php", ADMIN_URL_CLIENT . "/left_menu_deliverall_array.php", ADMIN_URL_CLIENT . "/homecontent.php", ADMIN_URL_CLIENT . "/homecontent_action.php", ADMIN_URL_CLIENT . "/cancelled_orders.php", ADMIN_URL_CLIENT . "/allorders.php", ADMIN_URL_CLIENT . "/ajax_get_cuisine.php", ADMIN_URL_CLIENT . "/ajax_get_food_category.php", ADMIN_URL_CLIENT . "/ajax_get_restorantcat_filter.php", ADMIN_URL_CLIENT . "/ajax_check_deliverycharge_area.php", ADMIN_URL_CLIENT . "/ajax_store_details.php", ADMIN_URL_CLIENT . "/advertise_banners.php", ADMIN_URL_CLIENT . "/advertise_banner_action.php", ADMIN_URL_CLIENT . "/groups.php", ADMIN_URL_CLIENT . "/group_action.php", ADMIN_URL_CLIENT . "/permissions.php", ADMIN_URL_CLIENT . "/permission_action.php", ADMIN_URL_CLIENT . "/action/permissions.php", ADMIN_URL_CLIENT . "/action/groups.php", ADMIN_URL_CLIENT . "/action/banner_impression.php", ADMIN_URL_CLIENT . "/action/advertise_banners.php", ADMIN_URL_CLIENT . "/action/news.php", ADMIN_URL_CLIENT . "/action/newsletter.php", ADMIN_URL_CLIENT . "/action/organization.php", ADMIN_URL_CLIENT . "/action/airport_surcharge.php", ADMIN_URL_CLIENT . "/airport_surcharge_action.php", ADMIN_URL_CLIENT . "/airport_surcharge.php", ADMIN_URL_CLIENT . "/news.php", ADMIN_URL_CLIENT . "/news_action.php", ADMIN_URL_CLIENT . "/newsletter.php", ADMIN_URL_CLIENT . "/newsletter---.php", ADMIN_URL_CLIENT . "/action/service_provider.php");
            foreach ($foodfilearray as $key => $filename) {
                if (file_exists(dirname(__FILE__) . "/" . $filename)) {
                    $errorcountsystemvalidation += 1;
                    echo "<li>Please Delete File From Admin Panel " . $filename . "</li>";
                    $deleteFileArr[] = dirname(__FILE__) . "/" . $filename;
                }
            }
            //$obj->sql_query("UPDATE configurations SET `vValue` = 'Normal' WHERE vName='RIDE_DRIVER_CALLING_METHOD'"); // By HJ On 07-03-2019
            //$obj->sql_query("UPDATE configurations SET `eAdminDisplay` = 'No' WHERE (vName='LIST_RESTAURANT_LIMIT_BY_DISTANCE' || vName = 'ADMIN_COMMISSION' || vName = 'MIN_ORDER_CANCELLATION_CHARGES' || vName = 'COMPANY_EMAIL_VERIFICATION' || vName = 'COMPANY_PHONE_VERIFICATION')"); // By HJ On 07-03-2019
            //Added By HJ On 28-02-2019 For Disable New Features Start
            //$obj->sql_query("UPDATE configurations SET `vValue` = 'No',`eAdminDisplay` = 'No' WHERE (vName = 'ENABLE_INSURANCE_TRIP_REPORT'||  vName = 'ENABLE_INSURANCE_ACCEPT_REPORT' || vName = 'ENABLE_INSURANCE_IDLE_REPORT' ||  vName = 'ENABLE_NEWSLETTERS_SUBSCRIPTION_SECTION' || vName = 'ENABLE_INTRANSIT_SHOPPING_SYSTEM' || vName = 'ENABLE_AIRPORT_SURCHARGE_SECTION' || vName = 'ENABLE_NEWS_SECTION' || vName = 'ENABLE_LIVE_CHAT' || vName = 'ENABLE_RESTAURANTS_ADVERTISEMENT_BANNER' || vName = 'ENABLE_DRIVER_ADVERTISEMENT_BANNER' || vName = 'ENABLE_RIDER_ADVERTISEMENT_BANNER' || vName = 'BOOK_FOR_ELSE_ENABLE' || vName = 'CHILD_SEAT_ACCESSIBILITY_OPTION' || vName = 'POOL_ENABLE' || vName = 'ENABLE_CORPORATE_PROFILE' || vName = 'PASSENGER_LINKEDIN_LOGIN' || vName = 'DRIVER_LINKEDIN_LOGIN' || vName = 'PASSENGER_LINKEDIN_LOGIN')"); // By HJ On 07-03-2019
            //$obj->sql_query("UPDATE configurations SET `vValue` = 'Disable',`eAdminDisplay` = 'No' WHERE vName='ADVERTISEMENT_TYPE'"); // By HJ On 07-03-2019
            //$obj->sql_query("UPDATE configurations SET `vValue` = '',`eAdminDisplay` = 'No' WHERE (vName='LINKEDIN_APP_SECRET_KEY' || vName='LINKEDIN_APP_ID')"); // By HJ On 07-03-2019
            //Added By HJ On 28-02-2019 For Disable New Features End
            $frontfoodfilesarray = array("food_menu.php", "food_menu_action.php", "invoice_deliverall.php", "menu_item_action.php", "menuitems.php", "myorder.php", "order_invoice.php", "settings.php", "orderdetails_mail_format.php", "processing_orders.php", "sign-up-restaurant.php", "organization_login.php", "organization_trip.php", "organization_users_trip.php", "organization_profile_action.php", "organization-profile.php", "my_users.php", "ajax_organization_login_action.php", "signup_action_organization.php", "organization-logout.php", "sign-up-organization.php");
            if ($eProductType != 'Ride-Delivery-UberX' && $eProductType != 'UberX') {
                $frontfoodfilesarray[] = "provider_images.php";
            }
            foreach ($frontfoodfilesarray as $key => $filesname) {
                if (file_exists((dirname(__FILE__) . "/" . $filesname))) {
                    $errorcountsystemvalidation += 1;
                    echo "<li>Please Delete File From root folder " . $filesname . "</li>";
                    $deleteFileArr[] = dirname(__FILE__) . "/" . $filesname;
                }
            }
            $webserviceIncludeFilesArr = array("include/include_webservice_sharkfeatures.php", "include/livechat.php");
            foreach ($webserviceIncludeFilesArr as $key => $filesname) {
                if (file_exists((dirname(__FILE__) . "/" . $filesname))) {
                    $errorcountsystemvalidation += 1;
                    echo "<li>Please Delete File From include folder " . $filesname . "</li>";
                    $deleteFileArr[] = dirname(__FILE__) . "/" . $filesname;
                }
            }
            if ($eProductType != 'Ride-Delivery-UberX' && $eProductType != 'Ride-Delivery-UberX-Shark') {
                /* $que1 = 'TRUNCATE TABLE configurations_cubejek';
                  $obj->sql_query($que1); */
            }
        } else {
            if ($eProductType != 'Ride-Delivery-UberX-Shark' && ($eProductType != 'Ride-Delivery-UberX' && $ePackageType != 'shark')) {
                $foodfilearray = array(ADMIN_URL_CLIENT . "/cuisine.php", ADMIN_URL_CLIENT . "/cuisine_action.php", ADMIN_URL_CLIENT . "/delivery_charges_action.php", ADMIN_URL_CLIENT . "/delivery_charges.php", ADMIN_URL_CLIENT . "/restaurants_pay_report.php", ADMIN_URL_CLIENT . "/food_menu.php", ADMIN_URL_CLIENT . "/food_menu_action.php", ADMIN_URL_CLIENT . "/store.php", ADMIN_URL_CLIENT . "/store_action.php", ADMIN_URL_CLIENT . "/store_banner_action.php", ADMIN_URL_CLIENT . "/store_banner.php", ADMIN_URL_CLIENT . "/store_cancel_reason.php", ADMIN_URL_CLIENT . "/store_cancel_reason_action.php", ADMIN_URL_CLIENT . "/store_dashboard.php", ADMIN_URL_CLIENT . "/store_document_action.php", ADMIN_URL_CLIENT . "/store_document_fetch.php", ADMIN_URL_CLIENT . "/store_driver_pay_report.php", ADMIN_URL_CLIENT . "/store_payment_report.php", ADMIN_URL_CLIENT . "/store_review.php", ADMIN_URL_CLIENT . "/store_vehicle_type.php", ADMIN_URL_CLIENT . "/store_vehicle_type_action.php", ADMIN_URL_CLIENT . "/store-dashboard.php", ADMIN_URL_CLIENT . "/order_invoice.php", ADMIN_URL_CLIENT . "/order_status.php", ADMIN_URL_CLIENT . "/menu_item_action.php", ADMIN_URL_CLIENT . "/order_status_action.php", ADMIN_URL_CLIENT . "/menu_item.php", ADMIN_URL_CLIENT . "/action/food_menu.php", ADMIN_URL_CLIENT . "/action/ordar_status_type.php", ADMIN_URL_CLIENT . "/action/restaurants_pay_report.php", ADMIN_URL_CLIENT . "/action/store.php", ADMIN_URL_CLIENT . "/action/store_cancel_reason.php", ADMIN_URL_CLIENT . "/action/store_driver_pay_report.php", ADMIN_URL_CLIENT . "/action/store_payment_report.php", ADMIN_URL_CLIENT . "/action/store_review.php", ADMIN_URL_CLIENT . "/action/store_vehicle_type.php", ADMIN_URL_CLIENT . "/action/menu_item.php", ADMIN_URL_CLIENT . "/action/cuisine.php", ADMIN_URL_CLIENT . "/action/delivery_charges.php", ADMIN_URL_CLIENT . "/service_provider.php", ADMIN_URL_CLIENT . "/service_provider_action.php", ADMIN_URL_CLIENT . "/service_category.php", ADMIN_URL_CLIENT . "/service_category_action.php", ADMIN_URL_CLIENT . "/left_menu_deliverall_array.php", ADMIN_URL_CLIENT . "/homecontent.php", ADMIN_URL_CLIENT . "/homecontent_action.php", ADMIN_URL_CLIENT . "/cancelled_orders.php", ADMIN_URL_CLIENT . "/allorders.php", ADMIN_URL_CLIENT . "/ajax_get_cuisine.php", ADMIN_URL_CLIENT . "/ajax_get_food_category.php", ADMIN_URL_CLIENT . "/ajax_get_restorantcat_filter.php", ADMIN_URL_CLIENT . "/ajax_check_deliverycharge_area.php", ADMIN_URL_CLIENT . "/ajax_store_details.php");
                foreach ($foodfilearray as $key => $filename) {
                    if (file_exists(dirname(__FILE__) . "/" . $filename)) {
                        $errorcountsystemvalidation += 1;
                        echo "<li>Please Delete File From Admin Panel " . $filename . "</li>";
                        $deleteFileArr[] = dirname(__FILE__) . "/" . $filename;
                    }
                }
            }
        }
    }
    //Added By HJ On 24-01-2020 For Removed Unwanted Filed of Cubex and CubejekX Setup Start
    $cubejekXCubeXFileArr = array();
    if (strtoupper($eCubeX) == "YES" || strtoupper($eCubejekX) == "YES" || strtoupper($eDeliverallX) == "YES") {
        $delAllFileArr = $flyFileArr = $ufxFileArr = $cubejekXCubeXFileArr = $rideFileArr = $deliveryFileArr = array();
        //echo $Deliverall."===".$Fly."===".$UberX."===".$Ride."===".$Delivery."==".ONLYDELIVERALL."<br>";
        if (strtoupper($Deliverall) == "NO") {
            //echo "Deliverall<br>";
            $manualOrderFiles = 1;
            $delAllFileArr = array(ADMIN_URL_CLIENT . "/cuisine.php", ADMIN_URL_CLIENT . "/cuisine_action.php", ADMIN_URL_CLIENT . "/delivery_charges_action.php", ADMIN_URL_CLIENT . "/delivery_charges.php", ADMIN_URL_CLIENT . "/restaurants_pay_report.php", ADMIN_URL_CLIENT . "/food_menu.php", ADMIN_URL_CLIENT . "/food_menu_action.php", ADMIN_URL_CLIENT . "/store.php", ADMIN_URL_CLIENT . "/store_action.php", ADMIN_URL_CLIENT . "/store_banner_action.php", ADMIN_URL_CLIENT . "/store_banner.php", ADMIN_URL_CLIENT . "/store_cancel_reason.php", ADMIN_URL_CLIENT . "/store_cancel_reason_action.php", ADMIN_URL_CLIENT . "/store_dashboard.php", ADMIN_URL_CLIENT . "/store_document_action.php", ADMIN_URL_CLIENT . "/store_document_fetch.php", ADMIN_URL_CLIENT . "/store_driver_pay_report.php", ADMIN_URL_CLIENT . "/store_payment_report.php", ADMIN_URL_CLIENT . "/store_review.php", ADMIN_URL_CLIENT . "/store_vehicle_type.php", ADMIN_URL_CLIENT . "/store_vehicle_type_action.php", ADMIN_URL_CLIENT . "/store-dashboard.php", ADMIN_URL_CLIENT . "/order_invoice.php", ADMIN_URL_CLIENT . "/order_status.php", ADMIN_URL_CLIENT . "/menu_item_action.php", ADMIN_URL_CLIENT . "/order_status_action.php", ADMIN_URL_CLIENT . "/menu_item.php", ADMIN_URL_CLIENT . "/action/food_menu.php", ADMIN_URL_CLIENT . "/action/ordar_status_type.php", ADMIN_URL_CLIENT . "/action/restaurants_pay_report.php", ADMIN_URL_CLIENT . "/action/store.php", ADMIN_URL_CLIENT . "/action/store_cancel_reason.php", ADMIN_URL_CLIENT . "/action/store_driver_pay_report.php", ADMIN_URL_CLIENT . "/action/store_payment_report.php", ADMIN_URL_CLIENT . "/action/store_review.php", ADMIN_URL_CLIENT . "/action/store_vehicle_type.php", ADMIN_URL_CLIENT . "/action/menu_item.php", ADMIN_URL_CLIENT . "/action/cuisine.php", ADMIN_URL_CLIENT . "/action/delivery_charges.php", ADMIN_URL_CLIENT . "/service_provider.php", ADMIN_URL_CLIENT . "/service_provider_action.php", ADMIN_URL_CLIENT . "/service_category.php", ADMIN_URL_CLIENT . "/service_category_action.php", ADMIN_URL_CLIENT . "/left_menu_deliverall_array.php", ADMIN_URL_CLIENT . "/homecontent.php", ADMIN_URL_CLIENT . "/homecontent_action.php", ADMIN_URL_CLIENT . "/cancelled_orders.php", ADMIN_URL_CLIENT . "/allorders.php", ADMIN_URL_CLIENT . "/ajax_get_cuisine.php", ADMIN_URL_CLIENT . "/ajax_get_food_category.php", ADMIN_URL_CLIENT . "/ajax_get_restorantcat_filter.php", ADMIN_URL_CLIENT . "/ajax_check_deliverycharge_area.php", ADMIN_URL_CLIENT . "/ajax_store_details.php", "food_menu.php", "cx-food_menu.php", "food_menu_action.php", "cx-food_menu_action.php", "invoice_deliverall.php", "cx-invoice_deliverall.php", "menu_item_action.php", "cx-menu_item_action.php", "menuitems.php", "cx-menuitems.php", "myorder.php", "cx-myorder.php", "order_invoice.php", "settings.php", "cx-settings.php", "orderdetails_mail_format.php", "processing_orders.php", "cx-processing_orders.php", "sign-up-restaurant.php", "organization_login.php", "organization_trip.php", "cx-organization_trip.php", "organization_users_trip.php", "cx-organization_users_trip.php", "organization_profile_action.php", "organization-profile.php", "cx-profile_organization.php", "my_users.php", "cx-my_users.php", "ajax_organization_login_action.php", "signup_action_organization.php", "organization-logout.php", "sign-up-organization.php", "cx-grocery.php", "cx-food.php", ADMIN_URL_CLIENT . "home_content_grocery_action.php", ADMIN_URL_CLIENT . "home_content_grocery.php", ADMIN_URL_CLIENT . "home_content_food_action.php", ADMIN_URL_CLIENT . "home_content_food.php", ADMIN_URL_CLIENT . "home_content_deliverallx_action.php", ADMIN_URL_CLIENT . "home_content_deliverallx.php");
        }
        if (strtoupper($Fly) == "NO") {
            //echo "Fly<br>";
            $flyFileArr = array("home_content_fly.php", ADMIN_URL_CLIENT . "/fly_stations.php", ADMIN_URL_CLIENT . "/fly_stations_action.php", ADMIN_URL_CLIENT . "/fly_vehicle_type.php", ADMIN_URL_CLIENT . "/action/fly_vehicle_type_action.php", ADMIN_URL_CLIENT . "/fly_locationwise_fare.php", ADMIN_URL_CLIENT . "/fly_location_wise_fare_action.php", ADMIN_URL_CLIENT . "/action/fly_locationwise_fare.php", ADMIN_URL_CLIENT . "/action/fly_stations.php", ADMIN_URL_CLIENT . "/action/fly_vehicle_type.php", ADMIN_URL_CLIENT . "home_content_fly_action.php", ADMIN_URL_CLIENT . "home_content_fly.php");
        }
        $cubejekXCubeXFileArr = array_merge($delAllFileArr, $flyFileArr);
        if (strtoupper($UberX) == "NO") {
            //echo "Uberx<br>";
            $ufxFileArr = array(ADMIN_URL_CLIENT . "/service_type.php", ADMIN_URL_CLIENT . "/left_menu_ufx_array.php", ADMIN_URL_CLIENT . "/left_menu_ufx.php", ADMIN_URL_CLIENT . "/left_menu_ufx_n.php", ADMIN_URL_CLIENT . "/add_availability.php", ADMIN_URL_CLIENT . "/service_type_action.php", ADMIN_URL_CLIENT . "/action/service_type.php", ADMIN_URL_CLIENT . "/action_driver_service_request.php", ADMIN_URL_CLIENT . "/driver_service_request.php", ADMIN_URL_CLIENT . "/manage_service_type.php", "include/uberx/action_booking_admin.php", "include/uberx/include_webservice_uberx.php");
            $cubejekXCubeXFileArr = array_merge($cubejekXCubeXFileArr, $ufxFileArr);
        }
        if (strtoupper($Ride) == "NO") {
            //echo "Ride<br>";
            $ridefilearray = array("include/ride/include_webservice_ride.php", "include/ride/add_booking_admin6.php", ADMIN_URL_CLIENT . "/user_profile_master_action.php", ADMIN_URL_CLIENT . "/user_profile_master.php", ADMIN_URL_CLIENT . "/trip_reason.php", ADMIN_URL_CLIENT . "/trip_reason_action.php", ADMIN_URL_CLIENT . "/profile.php", ADMIN_URL_CLIENT . "/organization_document_fetch.php", ADMIN_URL_CLIENT . "/organization.php", ADMIN_URL_CLIENT . "/organization_action.php", ADMIN_URL_CLIENT . "/org_payment_report.php", ADMIN_URL_CLIENT . "/org_cancellation_payment_report.php", ADMIN_URL_CLIENT . "/location-airport.php", ADMIN_URL_CLIENT . "/location_action_airport.php", ADMIN_URL_CLIENT . "/action/location-airport.php", ADMIN_URL_CLIENT . "/action/trip_reason.php", ADMIN_URL_CLIENT . "/action/user_profile_master.php", ADMIN_URL_CLIENT . "home_content_taxi_action.php", ADMIN_URL_CLIENT . "home_content_taxi.php", ADMIN_URL_CLIENT . "home_content_ridecx_action.php", ADMIN_URL_CLIENT . "home_content_ridecx.php", ADMIN_URL_CLIENT . "home_content_action_ride.php");
            $cubejekXCubeXFileArr = array_merge($cubejekXCubeXFileArr, $ridefilearray);
        }
        if ($eDeliveryType != 'Multi') {
            $cubejekXCubeXFileArr[] = ADMIN_URL_CLIENT . "/invoice_multi_delivery.php";
            $cubejekXCubeXFileArr[] = "invoice_multi_delivery.php";
            $cubejekXCubeXFileArr[] = "cx-invoice_multi_delivery.php";
        }
        if (strtoupper($eDeliveryType) == 'NONE' || trim($eDeliveryType) == "") {
            $cubejekXCubeXFileArr[] = ADMIN_URL_CLIENT . "/package_type.php";
            $cubejekXCubeXFileArr[] = ADMIN_URL_CLIENT . "/package_type_action.php";
            $cubejekXCubeXFileArr[] = ADMIN_URL_CLIENT . "/action/package_type.php";
        }
        if (strtoupper($Delivery) == "NO") {
            //echo "Delivery<br>";
            $deliveryFileArr = array("include/delivery/include_webservice_delivery.php", "include/delivery/add_booking_admin4.php", ADMIN_URL_CLIENT . "home_content_deliveryx_action.php", ADMIN_URL_CLIENT . "home_content_deliveryx.php", ADMIN_URL_CLIENT . "home_content_delivery_action.php", ADMIN_URL_CLIENT . "home_content_delivery.php");
            $cubejekXCubeXFileArr = array_merge($cubejekXCubeXFileArr, $deliveryFileArr);
        }
        if (strtoupper(ONLYDELIVERALL) == "YES") {
            //echo "ONLYDELIVERALL<br>";
            $cubejekXCubeXFileArr[] = ADMIN_URL_CLIENT . "/left_menu_ufx_array.php";
            $cubejekXCubeXFileArr[] = ADMIN_URL_CLIENT . "/left_menu_uberapp_array.php";
            $cubejekXCubeXFileArr[] = ADMIN_URL_CLIENT . "/home_content_ridedeliveryx.php";
            $cubejekXCubeXFileArr[] = ADMIN_URL_CLIENT . "/home_content_taxi_action.php";
            $cubejekXCubeXFileArr[] = ADMIN_URL_CLIENT . "/home_content_taxi.php";
            $cubejekXCubeXFileArr[] = ADMIN_URL_CLIENT . "/home_content_ridedeliveryx_action.php";
            $cubejekXCubeXFileArr[] = ADMIN_URL_CLIENT . "/home_content_ridedeliveryx.php";
            $cubejekXCubeXFileArr[] = ADMIN_URL_CLIENT . "/home_content_ridecx.php";
            $cubejekXCubeXFileArr[] = ADMIN_URL_CLIENT . "/home_content_deliveryx_action.php";
            $cubejekXCubeXFileArr[] = ADMIN_URL_CLIENT . "/home_content_deliveryx.php";
            $cubejekXCubeXFileArr[] = ADMIN_URL_CLIENT . "/home_content_delivery_action.php";
            $cubejekXCubeXFileArr[] = ADMIN_URL_CLIENT . "/home_content_delivery.php";
        }
        if (strtoupper($eCubeX) == "NO") {
            $cubejekXCubeXFileArr[] = ADMIN_URL_CLIENT . "/home_content_cubex_action.php";
            $cubejekXCubeXFileArr[] = ADMIN_URL_CLIENT . "/home_content_cubex.php";
        }
        if (strtoupper($eCubejekX) == "NO") {
            $cubejekXCubeXFileArr[] = ADMIN_URL_CLIENT . "/home_content_cubejekx_action.php";
            $cubejekXCubeXFileArr[] = ADMIN_URL_CLIENT . "/home_content_cubejekx.php";
        }
    }
    //echo "<pre>";print_r($cubejekXCubeXFileArr);die;
    foreach ($cubejekXCubeXFileArr as $key => $filename) {
        if (file_exists(dirname(__FILE__) . "/" . $filename)) {
            $errorcountsystemvalidation += 1;
            echo "<li>Please Delete File From Admin Panel " . $filename . "</li>";
            $deleteFileArr[] = dirname(__FILE__) . "/" . $filename;
        }
    }
    //Added By HJ On 24-01-2020 For Removed Unwanted Filed of Cubex and CubejekX Setup End
    //Added By HJ On 12-02-2020 For Check Sample Image Water Mark Status Start
    //ini_set('display_errors', 1);
    //error_reporting(E_ALL);
    $sampleImgTableName = "sample_image_master";
    $dbAllTablesArr = $generalobj->getAllTableArray(); // For Get Current Db's All Table Arr
    $sample_image_master = $generalobj->checkTableExists($sampleImgTableName, $dbAllTablesArr);
    //echo "<pre>";print_r($sample_image_master);die;
    if ($sample_image_master > 0 && ($eCubeX == "Yes" || $eCubejekX == "Yes")) {
        if ($eCubeX == "Yes") {
            $eTypeApp = "Cubex";
        } else if ($eCubejekX == "Yes") {
            $eTypeApp = "CubeJekX";
        } else if (strtoupper($eDeliverallX) == "YES") {
            $eTypeApp = "DeliverallX";
        } else {
            $eTypeApp = "";
            $obj->sql_query("TRUNCATE " . $sampleImgTableName);
        }
        if ($eTypeApp != "") {
            $sampleImages = $obj->MysqlSelect("SELECT iSamleId,vImagePath,vImageName FROM " . $sampleImgTableName . " WHERE eIsSample='Yes' AND eType='" . $eTypeApp . "' AND eStatus='Active'");
            if (count($sampleImages) > 0) {
                $errorcountsystemvalidation += 1;
                echo "<li>Please Convert Sample Image Into Water Mark</li>";
            }
            //echo "<pre>";print_r($sampleImages);die;
        }
    }
    //Added By HJ On 12-02-2020 For Check Sample Image Water Mark Status End
    if (DELIVERALL == "Yes" && ($eProductType == "Ride" || $eProductType == "Ride-Delivery" || $eProductType == "Delivery" || $eProductType == "UberX")) {
        $errorcountsystemvalidation += 1;
        echo "<li>Please Update DELIVERALL value as 'No' in configuration_variables file.</li>";
    }
    if ($eProductType == 'Foodonly' || $eProductType == 'Deliverall') {

        if (DELIVERALL == 'No') {
            $errorcountsystemvalidation += 1;
            echo "<li>Please Update DELIVERALL value as 'Yes' in configuration_variables file.</li>";
        }

        if (ONLYDELIVERALL == 'No') {
            $errorcountsystemvalidation += 1;
            echo "<li>Please Update ONLYDELIVERALL value as 'Yes' in configuration_variables file.</li>";
        }

        if (ENABLE_RENTAL_OPTION == 'Yes') {
            $errorcountsystemvalidation += 1;
            echo "<li>Please Update ENABLE_RENTAL_OPTION value as 'No' in configuration_variables file.</li>";
        }

        if ($eDeliveryType != 'Multi') {
            if (ENABLE_MULTI_DELIVERY == 'Yes') {
                $errorcountsystemvalidation += 1;
                echo "<li>Please Update ENABLE_MULTI_DELIVERY value as 'No' in configuration_variables file.</li>";
            }
        }

        foreach ($db_service_categories as $key => $value) {
            $iServiceId = $value['iServiceId'];
            if (!empty($iServiceId)) {
                $q1 = "show tables like 'language_label_" . $iServiceId . "'";
                $dbchecktabel = $obj->MySQLSelect($q1);
                if (count($dbchecktabel) <= 0) {
                    $errorcountsystemvalidation += 1;
                    echo "<li>Please Create 'language_label_" . $iServiceId . "' tabel using language_label_2 tabel.</li>";
                }
            }
        }

        //if (!empty($matchresult) || !empty($matchresult2)) { // Commented By HJ On 08-02-2020 As Per Discuss With KS Sir
        if (!empty($matchresult2)) { // Added By HJ On 08-02-2020 As Per Discuss With KS Sir
            $errorcountsystemvalidation += 1;
            echo "<li>Please Add or Remove 'Service Ids' in configuration file in array 'service_categories_ids_arr'.</li>";
        }

        //$sql3 = "UPDATE configurations SET `eAdminDisplay` = 'No' WHERE (vName='DRIVER_REQUEST_METHOD' || vName = 'ENABLE_TOLL_COST' || vName = 'TOLL_COST_APP_ID' || vName = 'TOLL_COST_APP_CODE' || vName = 'ENABLE_HAIL_RIDES' ||  vName = 'ENABLE_SURGE_CHARGE_RENTAL' || vName = 'ENABLE_WAITING_CHARGE_RENTAL')";
        //$obj->sql_query($sql3); // By HJ On 07-03-2019
        //$sql3 = "UPDATE configurations SET `eAdminDisplay` = 'Yes' WHERE (vName='LIST_RESTAURANT_LIMIT_BY_DISTANCE' || vName = 'ADMIN_COMMISSION' || vName = 'MIN_ORDER_CANCELLATION_CHARGES' || vName = 'COMPANY_EMAIL_VERIFICATION' || vName = 'COMPANY_PHONE_VERIFICATION')";
        //$obj->sql_query($sql3); // By HJ On 07-03-2019

        /* $query2 = 'TRUNCATE TABLE configurations_cubejek';
          $obj->sql_query($query2); */

        //$sql5 = "UPDATE configurations SET `eAdminDisplay` = 'No' WHERE ( vName = 'ENABLE_SURGE_CHARGE_RENTAL' || vName = 'ENABLE_WAITING_CHARGE_RENTAL'|| vName = 'ENABLE_HAIL_RIDES' || vName = 'FEMALE_RIDE_REQ_ENABLE' || vName = 'HANDICAP_ACCESSIBILITY_OPTION' ||  vName =  'ENABLE_WAITING_CHARGE_FLAT_TRIP' ||  vName =  'APPLY_SURGE_ON_FLAT_FARE' || vName =  'BOOKING_LATER_ACCEPT_BEFORE_INTERVAL' || vName =  'BOOKING_LATER_ACCEPT_AFTER_INTERVAL')";
        //$obj->sql_query($sql5); // By HJ On 07-03-2019
        //$sql4 = "UPDATE configurations SET `eAdminDisplay` = 'No' WHERE ( vName = 'DELIVERY_VERIFICATION_METHOD')";
        //$obj->sql_query($sql4); // By HJ On 07-03-2019
    } else {
        if ($ePackageType == 'standard') {
            if (ENABLE_RENTAL_OPTION == 'Yes') {
                $errorcountsystemvalidation += 1;
                echo "<li>Please Update ENABLE_RENTAL_OPTION value as 'No' in configuration_variables file.</li>";
            }
            /* if($eDeliveryType != 'Multi'){
              if(ENABLE_MULTI_DELIVERY == 'Yes'){
              $errorcountsystemvalidation +=1;
              echo "<li>Please Update ENABLE_MULTI_DELIVERY value as 'No' in configuration_variables file.</li>";
              }
              } */
            if (DELIVERALL == 'Yes') {
                $errorcountsystemvalidation += 1;
                echo "<li>Please Update DELIVERALL value as 'No' in configuration_variables file.</li>";
            }
            if (ONLYDELIVERALL == 'Yes') {
                $errorcountsystemvalidation += 1;
                echo "<li>Please Update ONLYDELIVERALL value as 'No' in configuration_variables file.</li>";
            }
            //$sql4 = "UPDATE configurations SET `vValue` = 'No' WHERE (vName = 'ENABLE_TOLL_COST'||  vName = 'CALLMASKING_ENABLED' || vName = 'ENABLE_HAIL_RIDES' ||  vName = 'ENABLE_SURGE_CHARGE_RENTAL' || vName = 'ENABLE_WAITING_CHARGE_RENTAL' || vName = 'WAYBILL_ENABLE')";
            //$obj->sql_query($sql4); // By HJ On 07-03-2019
            //$obj->sql_query("UPDATE configurations SET `eAdminDisplay` = 'No' WHERE ( vName = 'WAYBILL_ENABLE')"); // By HJ On 07-03-2019
            if ($eProductType == 'UberX' || $eProductType == 'Delivery' || $eProductType == 'Foodonly' || $eProductType == 'Deliverall') {
                //$sql8 = "UPDATE configurations SET `eAdminDisplay` = 'No' WHERE (vName = 'FEMALE_RIDE_REQ_ENABLE' || vName = 'HANDICAP_ACCESSIBILITY_OPTION' ||  vName =  'ENABLE_WAITING_CHARGE_FLAT_TRIP' ||  vName =  'APPLY_SURGE_ON_FLAT_FARE')";
                //$obj->sql_query($sql8); // By HJ On 07-03-2019
            }
            //$sql3 = "UPDATE configurations SET `eAdminDisplay` = 'No' WHERE (vName = 'ENABLE_TOLL_COST' || vName = 'TOLL_COST_APP_ID' || vName = 'TOLL_COST_APP_CODE' || vName = 'CALLMASKING_ENABLED' || vName = 'ENABLE_HAIL_RIDES' ||  vName = 'ENABLE_SURGE_CHARGE_RENTAL' || vName = 'ENABLE_WAITING_CHARGE_RENTAL')";
            //$obj->sql_query($sql3); // By HJ On 07-03-2019
            $standardfilearray = array(ADMIN_URL_CLIENT . "/masking_numbers.php", ADMIN_URL_CLIENT . "/masking_numbers_action.php", ADMIN_URL_CLIENT . "/location_wise_fare.php", ADMIN_URL_CLIENT . "/location_wise_fare_action.php", ADMIN_URL_CLIENT . "/locationwise_fare.php", ADMIN_URL_CLIENT . "/rental_package.php", ADMIN_URL_CLIENT . "/action/masking_numbers.php", ADMIN_URL_CLIENT . "/action/locationwise_fare.php", ADMIN_URL_CLIENT . "/action/rental_package.php");
            foreach ($standardfilearray as $key => $filename) {
                if (file_exists(dirname(__FILE__) . "/" . $filename)) {
                    $errorcountsystemvalidation += 1;
                    echo "<li>Please Delete File From Admin Panel " . $filename . "</li>";
                    $deleteFileArr[] = dirname(__FILE__) . "/" . $filename;
                }
            }
            $frontFilesarray = array("call.php", "callmask.php", "tollroute.php");
            foreach ($frontFilesarray as $filenamefront) {
                if (file_exists(dirname(__FILE__) . "/" . $filenamefront)) {
                    $errorcountsystemvalidation += 1;
                    echo "<li>Please Delete File From Root " . $filenamefront . "</li>";
                    $deleteFileArr[] = dirname(__FILE__) . "/" . $filenamefront;
                }
            }
            $foldersArray = array("assets/libraries/adyen", "assets/libraries/MPesa", "assets/libraries/omise", "assets/libraries/paymaya", "assets/libraries/xendit", "assets/libraries/webview/flutterwave", "assets/libraries/webview/flutterwave_old", "assets/libraries/webview/hyper_pay", "assets/libraries/webview/hyperpay", "assets/libraries/webview/iugu", "assets/libraries/webview/payu_ro", "assets/libraries/webview/payubiz", "assets/libraries/webview/payulatam", "assets/libraries/webview/payzen", "assets/libraries/webview/mpesa", "assets/libraries/webview/final_payu_live_update", "assets/libraries/webview/alu-client-php-master", "assets/libraries/webview/static_google_map", "assets/libraries/webview/mcp");
            foreach ($foldersArray as $k => $val) {
                if (is_dir(dirname(__FILE__) . "/" . $val)) {
                    $errorcountsystemvalidation += 1;
                    echo "<li>Please Delete Folders From assets/libraries : " . $val . "</li>";
                    $deleteFileArr[] = dirname(__FILE__) . "/" . $val;
                }
            }
            $webserviceIncludeFilesArr = array("include/include_webservice_dl_enterprisefeatures.php", "include/include_webservice_enterprisefeatures.php");
            foreach ($webserviceIncludeFilesArr as $key => $filesname) {
                if (file_exists((dirname(__FILE__) . "/" . $filesname))) {
                    $errorcountsystemvalidation += 1;
                    echo "<li>Please Delete File From include folder " . $filesname . "</li>";
                    $deleteFileArr[] = dirname(__FILE__) . "/" . $filesname;
                }
            }
            if ($eProductType != 'Foodonly' || $eProductType != 'Deliverall') {
                //$sql3 = "UPDATE configurations SET `eAdminDisplay` = 'No' WHERE (vName='LIST_RESTAURANT_LIMIT_BY_DISTANCE' || vName = 'ADMIN_COMMISSION' || vName = 'MIN_ORDER_CANCELLATION_CHARGES' || vName = 'COMPANY_EMAIL_VERIFICATION' || vName = 'COMPANY_PHONE_VERIFICATION')";
                //$obj->sql_query($sql3); // By HJ On 07-03-2019
            }

            if ($eDeliveryType != 'Multi') {
                //$sql5 = "UPDATE configurations SET `eAdminDisplay` = 'No' WHERE ( vName =  'DELIVERY_VERIFICATION_METHOD')";
                //$obj->sql_query($sql5); // By HJ On 07-03-2019
            }
        } else if ($ePackageType == 'enterprise') {
            if (ENABLE_MULTI_DELIVERY == 'No') {
                /* $sql3 = "UPDATE configurations_cubejek SET `eStatus` = 'Inactive' WHERE (vName = 'MULTI_DELIVERY_SHOW_SELECTION' || vName = 'MULTI_DELIVERY_GRID_ICON_NAME' || vName = 'MULTI_DELIVERY_BANNER_IMG_NAME')";
                  $obj->sql_query($sql3); */
            }
            if ($eProductType != 'Foodonly' || $eProductType != 'Deliverall') {
                $enterpriseOrderFilesarray = array("include/include_webservice_dl_enterprisefeatures.php");
                foreach ($enterpriseOrderFilesarray as $filenameEnterprise) {
                    if (file_exists(dirname(__FILE__) . "/" . $filenameEnterprise)) {
                        $errorcountsystemvalidation += 1;
                        echo "<li>Please Delete File From Include " . $filenameEnterprise . "</li>";
                        $deleteFileArr[] = dirname(__FILE__) . "/" . $filenameEnterprise;
                    }
                }
            }

            if ($eProductType != 'Delivery' && $eProductType != 'UberX') {
                if (ENABLE_RENTAL_OPTION == 'No') {
                    $errorcountsystemvalidation += 1;
                    echo "<li>Please Update ENABLE_RENTAL_OPTION value as 'Yes' in configuration_variables file.</li>";
                }
            } else {
                if (ENABLE_RENTAL_OPTION == 'Yes') {
                    $errorcountsystemvalidation += 1;
                    echo "<li>Please Update ENABLE_RENTAL_OPTION value as 'No' in configuration_variables file.</li>";
                }
            }

            if ($eDeliveryType != 'Multi') {
                if (ENABLE_MULTI_DELIVERY == 'Yes') {
                    $errorcountsystemvalidation += 1;
                    echo "<li>Please Update ENABLE_MULTI_DELIVERY value as 'No' in configuration_variables file.</li>";
                }
            }

            if (DELIVERALL == 'Yes') {
                $errorcountsystemvalidation += 1;
                echo "<li>Please Update DELIVERALL value as 'No' in configuration_variables file.</li>";
            }

            if (ONLYDELIVERALL == 'Yes') {
                $errorcountsystemvalidation += 1;
                echo "<li>Please Update ONLYDELIVERALL value as 'No' in configuration_variables file.</li>";
            }
            //$sql3 = "UPDATE configurations SET `eAdminDisplay` = 'Yes' WHERE (vName='CALLMASKING_ENABLED' || vName = 'ENABLE_TOLL_COST' || vName ='TOLL_COST_APP_ID' ||  vName ='TOLL_COST_APP_CODE')";
            //$obj->sql_query($sql3); // By HJ On 07-03-2019
            //$obj->sql_query("UPDATE configurations SET `eAdminDisplay` = 'No',`vValue`='Yes' WHERE vName =  'WAYBILL_ENABLE'"); // Added By HJ On 02-03-2019
            if ($eProductType == 'Ride' || $eProductType == 'Ride-Delivery-UberX' || $eProductType == 'Ride-Delivery') {
                //$sql5 = "UPDATE configurations SET `eAdminDisplay` = 'Yes' WHERE ( vName = 'ENABLE_SURGE_CHARGE_RENTAL' || vName = 'ENABLE_WAITING_CHARGE_RENTAL'|| vName = 'ENABLE_HAIL_RIDES' || vName = 'FEMALE_RIDE_REQ_ENABLE' || vName = 'HANDICAP_ACCESSIBILITY_OPTION' ||  vName =  'ENABLE_WAITING_CHARGE_FLAT_TRIP' ||  vName =  'APPLY_SURGE_ON_FLAT_FARE')";
                //$obj->sql_query($sql5);
            } else {
                //$sql5 = "UPDATE configurations SET `eAdminDisplay` = 'No' WHERE ( vName = 'ENABLE_SURGE_CHARGE_RENTAL' || vName = 'ENABLE_WAITING_CHARGE_RENTAL'|| vName = 'ENABLE_HAIL_RIDES' || vName = 'FEMALE_RIDE_REQ_ENABLE' || vName = 'HANDICAP_ACCESSIBILITY_OPTION' ||  vName =  'ENABLE_WAITING_CHARGE_FLAT_TRIP' ||  vName =  'APPLY_SURGE_ON_FLAT_FARE')";
                //$obj->sql_query($sql5);
                $enterpriseFilesarray = array(ADMIN_URL_CLIENT . "/rental_package.php", ADMIN_URL_CLIENT . "/rental_vehicle_list.php", ADMIN_URL_CLIENT . "/action/rental_package.php");
                foreach ($enterpriseFilesarray as $filenameEnterprise) {
                    if (file_exists(dirname(__FILE__) . "/" . $filenameEnterprise)) {
                        $errorcountsystemvalidation += 1;
                        echo "<li>Please Delete File From Admin " . $filenameEnterprise . "</li>";
                        $deleteFileArr[] = dirname(__FILE__) . "/" . $filenameEnterprise;
                    }
                }
            }
            ?>
        <?php } else { ?>
            <?php
            if (DELIVERALL == 'No' && ($eProductType == "Ride-Delivery-UberX" || $eProductType == 'Foodonly' || $eProductType == 'Deliverall')) {
                $deliverAllFlag = 1;
                if ($eCubejekX == "Yes" && $Deliverall == "No") {
                    $deliverAllFlag = 0;
                }
                if ($deliverAllFlag > 0) {
                    $errorcountsystemvalidation += 1;
                    echo "<li>Please Update DELIVERALL value as 'Yes' in configuration_variables file.</li>";
                }
            }
            //$obj->sql_query("UPDATE configurations SET `vValue` = 'Voip' WHERE vName='RIDE_DRIVER_CALLING_METHOD'");
            //$obj->sql_query("UPDATE configurations SET `eAdminDisplay` = 'No',`vValue`='Yes' WHERE vName =  'WAYBILL_ENABLE'"); // Added By HJ On 02-03-2019

            if ($eDeliveryType != 'Multi') {
                if (ENABLE_MULTI_DELIVERY == 'Yes') {
                    $errorcountsystemvalidation += 1;
                    echo "<li>Please Update ENABLE_MULTI_DELIVERY value as 'No' in configuration_variables file.</li>";
                }
            }
            /* $sql2 = "UPDATE configurations_cubejek SET `eStatus` = 'Active' WHERE (vName = 'FOOD_APP_SHOW_SELECTION' || vName = 'FOOD_APP_GRID_ICON_NAME' || vName = 'FOOD_APP_BANNER_IMG_NAME' || vName = 'FOOD_APP_DETAIL_BANNER_IMG_NAME' || vName = 'FOOD_APP_DETAIL_GRID_ICON_NAME' || vName = 'FOOD_APP_PACKAGE_NAME' || vName = 'FOOD_APP_IOS_APP_ID' || vName = 'FOOD_APP_IOS_PACKAGE_NAME'  || vName = 'FOOD_APP_SERVICE_ID' || vName = 'GROCERY_APP_SHOW_SELECTION' || vName = 'GROCERY_APP_GRID_ICON_NAME' || vName = 'GROCERY_APP_BANNER_IMG_NAME' || vName = 'GROCERY_APP_DETAIL_BANNER_IMG_NAME' || vName = 'GROCERY_APP_DETAIL_GRID_ICON_NAME' || vName = 'GROCERY_APP_PACKAGE_NAME' || vName = 'GROCERY_APP_IOS_APP_ID' || vName = 'GROCERY_APP_IOS_PACKAGE_NAME' || vName = 'GROCERY_APP_SERVICE_ID' || vName = 'DELIVER_ALL_APP_IOS_PACKAGE_NAME' || vName = 'DELIVER_ALL_APP_IOS_APP_ID' || vName = 'DELIVER_ALL_APP_PACKAGE_NAME' || vName = 'DELIVER_ALL_APP_DETAIL_GRID_ICON_NAME' || vName = 'DELIVER_ALL_APP_DETAIL_BANNER_IMG_NAME' || vName = 'DELIVER_ALL_APP_BANNER_IMG_NAME' || vName='DELIVER_ALL_APP_GRID_ICON_NAME' || vName = 'DELIVER_ALL_APP_SHOW_SELECTION' )";
              $obj->sql_query($sql2); */
            foreach ($db_service_categories as $key => $value) {
                $iServiceId = $value['iServiceId'];
                if (!empty($iServiceId)) {
                    $q1 = "show tables like 'language_label_" . $iServiceId . "'";
                    $dbchecktabel = $obj->MySQLSelect($q1);
                    if (count($dbchecktabel) <= 0) {
                        $errorcountsystemvalidation += 1;
                        echo "<li>Please Create 'language_label_" . $iServiceId . "' tabel using language_label_2 tabel.</li>";
                    }
                }
            }
            //if (!empty($matchresult) || !empty($matchresult2)) { // Commented By HJ On 08-02-2020 As Per Discuss With KS Sir
            if (!empty($matchresult2)) { // Added By HJ On 08-02-2020 As Per Discuss With KS Sir
                $errorcountsystemvalidation += 1;
                echo "<li>Please Add 'Service Ids' in configuration file in array 'service_categories_ids_arr'.</li>";
            }
        }
    }
    //Added By HJ On 12-07-2019 For Removed New Addon Files As Per Selection Start
    $addonFilesArr = $permissionArr = $emailTemplateArr = $smsTemplateArr = array();
    $permissionArr[] = "expired-documents"; //Added By HJ On 08-02-2020 For Removed Expired Document Feature As Per Discuss With KS Sir
    if ($DONATION == "No" || $DONATION != "Yes") {
        $addonFilesArr[] = ADMIN_URL_CLIENT . "/donation.php";
        $addonFilesArr[] = ADMIN_URL_CLIENT . "/action/donation.php";
        $addonFilesArr[] = ADMIN_URL_CLIENT . "/donation_action.php";
        $addonFilesArr[] = "include/features/include_donation.php";
        $permissionArr[] = "create-donation";
        $permissionArr[] = "delete-donation";
        $permissionArr[] = "edit-donation";
        $permissionArr[] = "update-status-donation";
        $permissionArr[] = "view-donation";
        $obj->sql_query("UPDATE configurations SET `vValue` = 'No',eAdminDisplay='No',eStatus='Inactive' WHERE vName='DONATION_ENABLE'");
        $obj->sql_query("UPDATE " . $sql_vehicle_category_table_name . " SET eStatus='Deleted' WHERE eCatType='Donation'");
    }
    if (strtoupper($eCubeX) == "YES" || strtoupper($eCubejekX) == "YES" || strtoupper($eDeliverallX) == "YES") {
        $getAllPermission = $obj->MySQLSelect("SELECT * FROM admin_permissions WHERE eFor != 'General'");
        $adminPermissionArr = array();
        for($p=0;$p<count($getAllPermission);$p++){
            $adminPermissionArr[$getAllPermission[$p]['eFor']][] = $getAllPermission[$p]['permission_name'];
        }
        //echo "<pre>";print_r($adminPermissionArr);die;
        if (strtoupper($kioskPanelEnable) == "NO") {
            if(isset($adminPermissionArr['Kiosk'])){
                $kioskPermission = $adminPermissionArr['Kiosk'];
                foreach($kioskPermission as $key=>$val){
                    $permissionArr[] = $val;
                }
            }
        }
        if(strtoupper(DELIVERALL) == 'NO'){
            if(isset($adminPermissionArr['DeliverAll'])){
                $deliverallPermission = $adminPermissionArr['DeliverAll'];
                foreach($deliverallPermission as $key=>$val){
                    $permissionArr[] = $val;
                }
            }
        }
        if (strtoupper($Fly) == "NO") {
            if(isset($adminPermissionArr['Fly'])){
                $flyPermission = $adminPermissionArr['Fly'];
                foreach($flyPermission as $key=>$val){
                    $permissionArr[] = $val;
                }
            }
            /*$permissionArr[] = "manage-fly-vehicles";
            $permissionArr[] = "view-fly-stations";
            $permissionArr[] = "view-fly-vehicle-type";
            $permissionArr[] = "view-fly-fare";
            $permissionArr[] = "create-fly-fare";
            $permissionArr[] = "create-fly-stations";
            $permissionArr[] = "delete-fly-fare";
            $permissionArr[] = "delete-fly-stations";
            $permissionArr[] = "edit-fly-fare";
            $permissionArr[] = "edit-fly-stations";
            $permissionArr[] = "update-status-fly-fare";
            $permissionArr[] = "update-status-fly-stations";
            $permissionArr[] = "create-fly-vehicle-type";
            $permissionArr[] = "view-fly-vehicle-type";
            $permissionArr[] = "edit-fly-vehicle-type";
            $permissionArr[] = "update-status-fly-vehicle-type";
            $permissionArr[] = "delete-fly-vehicle-type";*/
        }
        if (strtoupper($UberX) == "NO") {
            $obj->sql_query("UPDATE configurations SET `vValue` = 'No',eAdminDisplay='No',eStatus='Inactive' WHERE vName='ENABLE_DRIVER_SERVICE_REQUEST_MODULE'");
            if(isset($adminPermissionArr['UberX'])){
                $uberXPermission = $adminPermissionArr['UberX'];
                foreach($uberXPermission as $key=>$val){
                    $permissionArr[] = $val;
                }
            }
            //$permissionArr[] = "create-service-type";
            //$permissionArr[] = "delete-service-type";
            //$permissionArr[] = "edit-service-type";
            //$permissionArr[] = "update-status-service-type";
            //$permissionArr[] = "view-driver-service-request";
            //$permissionArr[] = "view-service-type";
        }
        if (strtoupper($Ride) == "NO" && strtoupper($Delivery) == "NO") {
            if(isset($adminPermissionArr['Ride,Delivery'])){
                $rideDeliveryPermission = $adminPermissionArr['Ride,Delivery'];
                foreach($rideDeliveryPermission as $key=>$val){
                    $permissionArr[] = $val;
                }
            }
        }
        if (strtoupper($Ride) == "NO" && strtoupper($Delivery) == "NO" && strtoupper($UberX) == "NO") {
            if(isset($adminPermissionArr['Ride,Delivery,UberX'])){
                $rideDeliveryUfxPermission = $adminPermissionArr['Ride,Delivery,UberX'];
                foreach($rideDeliveryUfxPermission as $key=>$val){
                    if(strtoupper($CUSTOME_APP_TYPE) == "CUBEDELIVERALLX" && ($val == "view-vehicle-category" || $val == "delete-vehicle-category" || $val == "update-status-vehicle-category" || $val == "edit-vehicle-category")){
                        // Service Category Here As Per Discuss with KS For Custome Req. CubejekX-Deliverall Setup
                    }else{
                        $permissionArr[] = $val;
                    }
                }
            }
        }
        if (strtoupper($Ride) == "NO"){
            if(isset($adminPermissionArr['Ride'])){
                $ridePermission = $adminPermissionArr['Ride'];
                foreach($ridePermission as $key=>$val){
                    $permissionArr[] = $val;
                }
            }
        }
        if(strtoupper($Delivery) == "NO"){
            if(isset($adminPermissionArr['Delivery'])){
                $deliveryPermission = $adminPermissionArr['Delivery'];
                foreach($deliveryPermission as $key=>$val){
                    $permissionArr[] = $val;
                }
            }
            if(isset($adminPermissionArr['Multi-Delivery'])){
                $mulDeliveryPermission = $adminPermissionArr['Multi-Delivery'];
                foreach($mulDeliveryPermission as $key=>$val){
                    $permissionArr[] = $val;
                }
            }
        }   
    }
    if ($DRIVER_SUBSCRIPTION == "No" || $DRIVER_SUBSCRIPTION != "Yes") {
        $addonFilesArr[] = ADMIN_URL_CLIENT . "/driver_subscription.php";
        $addonFilesArr[] = ADMIN_URL_CLIENT . "/driver_subscription_action.php";
        $addonFilesArr[] = ADMIN_URL_CLIENT . "/driver_subscription_report.php";
        $addonFilesArr[] = ADMIN_URL_CLIENT . "/action/driver_subscription.php";
        $addonFilesArr[] = "cron_driver_subscription.php";
        $addonFilesArr[] = ADMIN_URL_CLIENT . "/ajax_driver_subscription.php";
        $addonFilesArr[] = "booking/ajax_driver_subscription.php";
        $addonFilesArr[] = "include/features/include_driver_subscription.php";
        $permissionArr[] = "create-driver-subscription";
        $permissionArr[] = "delete-driver-subscription";
        $permissionArr[] = "edit-driver-subscription";
        $permissionArr[] = "manage-driver-subscription";
        $permissionArr[] = "manage-driver-subscription-report";
        $permissionArr[] = "update-status-driver-subscription";
        $permissionArr[] = "view-driver-subscription";
        $obj->sql_query("DELETE FROM email_templates WHERE vEmail_Code IN ('DRIVER_SUBSCRIPTION_CANCEL','DRIVER_SUBSCRIPTION_SUCCESS')");
        $emailTemplateArr[] = "DRIVER_SUBSCRIPTION_CANCEL";
        $emailTemplateArr[] = "DRIVER_SUBSCRIPTION_SUCCESS";
        $emailTemplateArr[] = "CRON_SUBSCRIBE_REMAIN_DAYS";
    }
    if ($FAVOURITE_DRIVER == "No" || $FAVOURITE_DRIVER != "Yes") {
        $addonFilesArr[] = "include/features/include_fav_driver.php";
    }
    if ($FAVOURITE_STORE == "No" || $FAVOURITE_STORE != "Yes") {
        $addonFilesArr[] = "include/features/include_fav_store.php";
    }
    if ($GOJEK_GOPAY == "No" || $GOJEK_GOPAY != "Yes") {
        $addonFilesArr[] = "include/features/include_gojek_gopay.php";
        $emailTemplateArr[] = "WALLET_AMOUNT_TRANSFER";
        $emailTemplateArr[] = "OTP_TRANSFER_MONEY";
        $smsTemplateArr[] = "WALLET_AMOUNT_TRANSFER";
        $smsTemplateArr[] = "OTP_TRANSFER_MONEY";
    }
    if ($DRIVER_DESTINATION == "No" || $DRIVER_DESTINATION != "Yes") {
        $addonFilesArr[] = "include/features/include_destinations_driver.php";
    }
    if ($MULTI_STOPOVER_POINTS == "No" || $MULTI_STOPOVER_POINTS != "Yes") {
        $addonFilesArr[] = "include/features/include_stop_over_point.php";
    }
    if (($MANUAL_STORE_ORDER_WEBSITE == "No" && $MANUAL_STORE_ORDER_STORE_PANEL == "No" && $MANUAL_STORE_ORDER_ADMIN_PANEL == "No") || $manualOrderFiles > 0) {
        $addonFilesArr[] = "customer_info.php";
        $addonFilesArr[] = "include_generalFunctions_dl.php";
        //$addonFilesArr[] = "generalFunctions.php"; // Removed
        $addonFilesArr[] = "customer_info_action.php";
        $addonFilesArr[] = "ajax_find_rider_by_number.php";
        $addonFilesArr[] = "restaurant_listing.php";
        $addonFilesArr[] = "include/features/include_fav_store.php";
        $addonFilesArr[] = "ajax_load_store.php";
        $addonFilesArr[] = "ajax_load_fav_store.php";
        $addonFilesArr[] = "ajax_fav_manual_store.php";
        $addonFilesArr[] = "ajax_get_values_cart_to_restaurant.php";
        $addonFilesArr[] = "update_qty_item_cart_restaurant.php";
        $addonFilesArr[] = "add_cart_to_restaurant.php";
        $addonFilesArr[] = "ajax_view_cart_to_restaurant.php";
        $addonFilesArr[] = "update_item_cart_restaurant.php";
        $addonFilesArr[] = "ajax_filter_restaurant_menu_item.php";
        $addonFilesArr[] = "ajax_load_model_cart.php";
        $addonFilesArr[] = "ajax_checkout_order_details.php";
        $addonFilesArr[] = "ajax_checkout_cart_to_restaurant.php";
        $addonFilesArr[] = "ajax_check_promocode_cart_to_restaurant.php";
        $addonFilesArr[] = "remove_item_cart_to_restaurant.php";
        $addonFilesArr[] = "ajax_check_address_store.php";
        $addonFilesArr[] = "ajax_add_delivery_address.php";
        //$addonFilesArr[] = "ajax_fpass_action.php";
        $addonFilesArr[] = "restaurant_menu.php";
        $addonFilesArr[] = "restaurant_place-order.php";
        $addonFilesArr[] = "thanks.php";
        $addonFilesArr[] = "change_check_code.php";
        $addonFilesArr[] = "user_info.php";
        $addonFilesArr[] = "user_info_action.php";
        $addonFilesArr[] = "user_info_action_all.php";
        $permissionArr[] = "manage-restaurant-order";
    }
    if (($MANUAL_STORE_ORDER_WEBSITE == "No") || $manualOrderFiles > 0) {
        $addonFilesArr[] = "user_info.php";
        $addonFilesArr[] = "user_info_action.php";
        $addonFilesArr[] = "user_info_action_all.php";
    }
    if (($MANUAL_STORE_ORDER_ADMIN_PANEL == "No" && $MANUAL_STORE_ORDER_STORE_PANEL == "No") || $manualOrderFiles > 0) {
        $addonFilesArr[] = "customer_info.php";
        $addonFilesArr[] = "customer_info_action.php";
        $permissionArr[] = "manage-restaurant-order";
    }
    if (count($permissionArr) > 0) {
        $delPermission = "'" . implode("','", $permissionArr) . "'";
        $obj->sql_query("DELETE FROM admin_permissions WHERE permission_name IN ($delPermission)");
    }
    foreach ($addonFilesArr as $key => $addOnFile) {
        if (file_exists(dirname(__FILE__) . "/" . $addOnFile)) {
            $errorcountsystemvalidation += 1;
            echo "<li>Please Delete File Of New Addon " . $addOnFile . "</li>";
            $deleteFileArr[] = dirname(__FILE__) . "/" . $addOnFile;
        }
    }
    //Added By HJ On 12-07-2019 For Removed New Addon Files As Per Selection End
    //Added By HJ On 30-07-2019 For Removed New Addon's SMS and Email Template Start
    for ($e = 0; $e < count($emailTemplateArr); $e++) {
        $obj->sql_query("DELETE FROM email_templates WHERE vEmail_Code='" . $emailTemplateArr[$e] . "'"); // By HJ On 20-03-2019 For Solved Bug - 6403
    }
    for ($s = 0; $s < count($emailTemplateArr); $s++) {
        $obj->sql_query("DELETE FROM send_message_templates WHERE vEmail_Code='" . $emailTemplateArr[$s] . "'"); // By HJ On 20-03-2019 For Solved Bug - 6403
    }
    //Added By HJ On 30-07-2019 For Removed New Addon's SMS and Email Template End
    //Added By HJ On 20-03-2019 For Remove SMS and Email Templates Start Bug - 6406
    if (strtoupper($ePackageType) != "SHARK") {
        $obj->sql_query("DELETE FROM email_templates WHERE vEmail_Code IN ('USER_REGISTRATION_ORGANIZATION','ORGANIZATION_UPDATE_USERPROFILESTATUS_TO_USER','ORGANIZATION_REGISTRATION_ADMIN','ORGANIZATION_REGISTRATION_USER','ADMIN_UPDATE_USERPROFILESTATUS_TO_ORGANIZATION','STORE_REGISTRATION_USER','STORE_REGISTRATION_ADMIN','MEMBER_BLOCKED_INACTIVE_DRIVER','MEMBER_BLOCKED_ACTIVE_DRIVER','MEMBER_BLOCKED_INACTIVE_USER','MEMBER_BLOCKED_ACTIVE_USER','MEMBER_NEWS_SUBSCRIBE_USER','MEMBER_NEWS_UNSUBSCRIBE_USER')");
    }
    //Added By HJ On 01-07-2019 As Per Discuss With KS Sir Start
    if ($kioskPanelEnable == "No") {
        $obj->sql_query("DELETE FROM administrators WHERE iGroupId='4'"); // By HJ On 20-03-2019 For Solved Bug - 6403
        $obj->sql_query("DELETE FROM admin_groups WHERE iGroupId='4'"); // By HJ On 20-03-2019 For Solved Bug - 6403
        $obj->sql_query("DELETE FROM admin_group_permission WHERE group_id='4'"); // By HJ On 20-03-2019 For Solved Bug - 6403
        $obj->sql_query("DELETE FROM admin_permissions WHERE `permission_name` LIKE 'manage-hotel-payment-report'"); // By HJ On 20-03-2019 For Solved Bug - 6403
    }
    //Added By HJ On 01-07-2019 As Per Discuss With KS Sir End
    if ($eProductType == "Delivery" || $eProductType == "UberX" || ONLYDELIVERALL == "Yes") {
        $obj->sql_query("DELETE FROM email_templates WHERE vEmail_Code IN ('USER_REGISTRATION_ORGANIZATION','ORGANIZATION_UPDATE_USERPROFILESTATUS_TO_USER','ORGANIZATION_REGISTRATION_ADMIN','ORGANIZATION_REGISTRATION_USER','ADMIN_UPDATE_USERPROFILESTATUS_TO_ORGANIZATION')");
        $obj->sql_query("DELETE FROM send_message_templates WHERE vEmail_Code IN ('EMERGENCY_SMS_FOR_USER_RIDE','EMERGENCY_SMS_FOR_DRIVER_RIDE')");
    }
    if ($eProductType == "Ride" || $eProductType == "Delivery" || $eProductType == "Ride-Delivery" || ONLYDELIVERALL == "Yes") {
        $obj->sql_query("DELETE FROM email_templates WHERE vEmail_Code IN ('MANUAL_TAXI_DISPATCH_DRIVER_APP_SP','MANUAL_BOOKING_ACCEPT_BYDRIVER_SP','MANUAL_BOOKING_DECLINED_BYDRIVER_SP','MANUAL_BOOKING_CANCEL_BYDRIVER_SP','MANUAL_BOOKING_CANCEL_BYRIDER_SP')");
        $obj->sql_query("DELETE FROM send_message_templates WHERE vEmail_Code IN ('EMERGENCY_SMS_FOR_USER_SP','EMERGENCY_SMS_FOR_DRIVER_SP','DRIVER_SEND_MESSAGE_SP','BOOKING_ACCEPT_BYDRIVER_MESSAGE_SP','BOOKING_DECLINED_BYDRIVER_MESSAGE_SP','BOOKING_CANCEL_BYRIDER_MESSAGE_SP','BOOKING_CANCEL_BYDRIVER_MESSAGE_SP')");
    }
    if (strtoupper($ePackageType) != "SHARK" || $eProductType == "Delivery" || $eProductType == "UberX" || ONLYDELIVERALL == "Yes") {
        $obj->sql_query("DELETE FROM send_message_templates WHERE vEmail_Code IN ('BOOK_FOR_SOMEONE_ELSE_SMS')");
    }
    if ($kioskPanelEnable != "Yes") {
        $obj->sql_query("DELETE FROM send_message_templates WHERE vEmail_Code IN ('BOOKING_IN_KIOSK')");
    }
    if ($eProductType == "Ride" || $eProductType == "UberX" || ONLYDELIVERALL == "Yes") {
        $obj->sql_query("DELETE FROM send_message_templates WHERE vEmail_Code IN ('EMERGENCY_SMS_FOR_USER_DELIVERY','EMERGENCY_SMS_FOR_DRIVER_DELIVERY')");
    }
    if (strtoupper($ePackageType) == "STANDARD" && ($eProductType == "Delivery" || $eProductType == "Ride-Delivery")) {
        $obj->sql_query("DELETE FROM " . $sql_vehicle_category_table_name . " WHERE eDeliveryType='Multi'");
        $obj->sql_query("DELETE FROM " . $sql_vehicle_category_table_name . " WHERE eFor='DeliveryCategory' AND eCatType='MultipleDelivery'");
        if (ENABLE_MULTI_DELIVERY == 'Yes') {
            $errorcountsystemvalidation += 1;
            echo "<li>Please Update ENABLE_MULTI_DELIVERY value as 'No' in configuration_variables file.</li>";
        }
    }
    if ($eProductType == "Ride-Delivery-UberX" && strtoupper($ePackageType) != "SHARK") {
        $getCatId = $obj->MySQLSelect("SELECT VC.iVehicleCategoryId FROM " . $sql_vehicle_category_table_name . " AS VC WHERE VC.eCatType='MoreDelivery' AND VC.eFor='DeliveryCategory'");
        $catIds = "";
        for ($fr = 0; $fr < count($getCatId); $fr++) {
            $catIds .= ",'" . $getCatId[$fr]['iVehicleCategoryId'] . "'";
        }
        if ($catIds != "") {
            $obj->sql_query("DELETE FROM " . $sql_vehicle_category_table_name . " WHERE iParentId=" . trim($catIds, ","));
        }
        $obj->sql_query("DELETE FROM " . $sql_vehicle_category_table_name . " WHERE (((eFor='DeliveryCategory' OR eFor='DeliverAllCategory') AND (eCatType='MoreDelivery' OR eCatType='MultipleDelivery')) OR (eCatType='DeliverAll') OR eDeliveryType='Multi')");
        $obj->sql_query("UPDATE " . $sql_vehicle_category_table_name . " SET iParentId='0',eShowType='Icon' WHERE eCatType='Delivery' OR eCatType='MotoDelivery'");
        $obj->sql_query("DELETE FROM cancel_reason WHERE eType='Deliverall'"); // For Solved Bug - 6467
        $obj->sql_query("DELETE FROM document_master WHERE doc_usertype='store'"); // For Solved Bug - 6465
        $obj->sql_query("DELETE FROM help_detail WHERE eSystem='DeliverAll'"); // For Solved Bug - 6466
        $obj->sql_query("DELETE FROM help_detail_categories WHERE eSystem='DeliverAll'"); // For Solved Bug - 6466
    }
    if (strtoupper($ePackageType) == "STANDARD") {
        $obj->sql_query("DELETE FROM " . $sql_vehicle_category_table_name . " WHERE eCatType='Rental' OR eCatType='MotoRental'");
    }
    //Added By HJ On 20-03-2019 For Remove SMS and Email Templates End Bug - 6406
    //Added By HJ On 11-03-2019 For Set Default configurations As Per KS Sir Start
    if ($applyConfiguration == 1) {
        //Added By HJ On 12-07-2019 For Set New Addon Configuration Based On Selection Start
        if ($DONATION == "No" || $DONATION != "Yes") { // Common
            $obj->sql_query("UPDATE configurations SET `vValue` = 'No',eAdminDisplay='No',eStatus='Inactive' WHERE vName='DONATION_ENABLE'");
        } else {
            $obj->sql_query("UPDATE configurations SET `vValue` = 'Yes',eAdminDisplay='Yes',eStatus='Active' WHERE vName='DONATION_ENABLE'");
        }
        $obj->sql_query("UPDATE configurations SET `vValue` = 'No',eAdminDisplay='Yes',eStatus='Active' WHERE vName='DRIVER_SUBSCRIPTION_ENABLE'"); // Added By HJ ON 19-01-2020 FOr Default Set No As Per Discuss WIth KS and CD sir
        //Added By HJ On 19-02-2020 For Set CubeX and CubeJekX Configuration Setting Start 
        $obj->sql_query("UPDATE configurations SET `vValue` = 'No',eAdminDisplay='No',eStatus='Active' WHERE vName='ENABLE_MAPS_API_REPLACEMENT'");
        $obj->sql_query("UPDATE configurations_payment SET `vValue` = 'No',eAdminDisplay='No',eStatus='Active' WHERE vName='CREDIT_TO_WALLET_ENABLE'");
        $obj->sql_query("UPDATE configurations SET `vValue` = 'Twilio',eAdminDisplay='No',eStatus='Active' WHERE vName='MOBILE_NO_VERIFICATION_METHOD'");
        if ($GOOGLE_PLAN > 0 || !empty($GOOGLE_PLAN)) { // Common
            $obj->sql_query("UPDATE configurations SET `vValue` = 'Advance',eAdminDisplay='No',eStatus='Active' WHERE vName='MAPS_API_REPLACEMENT_STRATEGY'");
        } else {
            $obj->sql_query("UPDATE configurations SET `vValue` = 'None',eAdminDisplay='No',eStatus='Active' WHERE vName='MAPS_API_REPLACEMENT_STRATEGY'");
        }
        //Added By HJ On 19-02-2020 For Set CubeX and CubeJekX Configuration Setting End
        if ($DRIVER_SUBSCRIPTION == "No" || $DRIVER_SUBSCRIPTION != "Yes") { // Common
            $obj->sql_query("UPDATE configurations SET eAdminDisplay='No',eStatus='Inactive' WHERE vName='DRIVER_SUBSCRIPTION_REMINDER_NOTIFICATION_DAYS'");
        } else {
            //$obj->sql_query("UPDATE configurations SET `vValue` = 'Yes',eAdminDisplay='Yes',eStatus='Active' WHERE vName='DRIVER_SUBSCRIPTION_ENABLE'"); // Commented By HJ ON 19-01-2020 FOr Default Set No As Per Discuss WIth KS and CD sir
            $obj->sql_query("UPDATE configurations SET `vValue` = '3',eAdminDisplay='Yes',eStatus='Active' WHERE vName='DRIVER_SUBSCRIPTION_REMINDER_NOTIFICATION_DAYS'");
        }
        if ($FAVOURITE_DRIVER == "No" || $FAVOURITE_DRIVER != "Yes") { // Common
            $obj->sql_query("UPDATE configurations SET `vValue` = 'No',eAdminDisplay='No',eStatus='Inactive' WHERE vName='ENABLE_FAVORITE_DRIVER_MODULE'");
        } else {
            $obj->sql_query("UPDATE configurations SET `vValue` = 'Yes',eAdminDisplay='Yes',eStatus='Active' WHERE vName='ENABLE_FAVORITE_DRIVER_MODULE'");
        }
        if (($FAVOURITE_STORE == "No" || $FAVOURITE_STORE != "Yes") || $manualOrderFiles > 0) { // Common
            $obj->sql_query("UPDATE configurations SET `vValue` = 'No',eAdminDisplay='No',eStatus='Inactive' WHERE vName='ENABLE_FAVORITE_STORE_MODULE'");
        } else {
            $obj->sql_query("UPDATE configurations SET `vValue` = 'Yes',eAdminDisplay='Yes',eStatus='Active' WHERE vName='ENABLE_FAVORITE_STORE_MODULE'");
        }
        if ($GOJEK_GOPAY == "No" || $GOJEK_GOPAY != "Yes") { // Common
            $obj->sql_query("UPDATE configurations_payment SET `vValue` = 'No',eAdminDisplay='No',eStatus='Inactive' WHERE vName='ENABLE_GOPAY'");
            $obj->sql_query("UPDATE configurations_payment SET eAdminDisplay='No',eStatus='Inactive' WHERE (vName='WALLET_MINIMUM_BALANCE_GOPAY' || vName='GOPAY_MAXIMUM_LIMIT_PER_TRANSACTION' || vName='GOPAY_MAXIMUM_LIMIT_PER_DAY')");
        } else {
            $obj->sql_query("UPDATE configurations_payment SET `vValue` = 'Yes',eAdminDisplay='Yes',eStatus='Active' WHERE vName='ENABLE_GOPAY'");
            $obj->sql_query("UPDATE configurations_payment SET `vValue` = '15',eAdminDisplay='Yes',eStatus='Active' WHERE vName='WALLET_MINIMUM_BALANCE_GOPAY'");
            $obj->sql_query("UPDATE configurations_payment SET `vValue` = '100',eAdminDisplay='Yes',eStatus='Active' WHERE (vName='GOPAY_MAXIMUM_LIMIT_PER_TRANSACTION' || vName='GOPAY_MAXIMUM_LIMIT_PER_DAY')");
        }
        if ($DRIVER_DESTINATION == "No" || $DRIVER_DESTINATION != "Yes") { // Common
            $obj->sql_query("UPDATE configurations SET `vValue` = 'No',eAdminDisplay='No',eStatus='Inactive' WHERE vName='ENABLE_DRIVER_DESTINATIONS'");
            $obj->sql_query("UPDATE configurations SET eAdminDisplay='No',eStatus='Inactive' WHERE (vName='MAX_DRIVER_DESTINATIONS' || vName='DRIVER_DESTINATIONS_RESET_TIME' || vName='RESTRICTION_KM_NEAREST_DESTINATION_DRIVER')");
        } else {
            $obj->sql_query("UPDATE configurations SET `vValue` = 'Yes',eAdminDisplay='Yes',eStatus='Active' WHERE vName='ENABLE_DRIVER_DESTINATIONS'");
            $obj->sql_query("UPDATE configurations SET `vValue` = '3',eAdminDisplay='Yes',eStatus='Active' WHERE vName='MAX_DRIVER_DESTINATIONS'");
            $obj->sql_query("UPDATE configurations SET `vValue` = '13:16',eAdminDisplay='Yes',eStatus='Active' WHERE vName='DRIVER_DESTINATIONS_RESET_TIME'");
            $obj->sql_query("UPDATE configurations SET `vValue` = '1',eAdminDisplay='Yes',eStatus='Active' WHERE vName='RESTRICTION_KM_NEAREST_DESTINATION_DRIVER'");
        }
        if ($MULTI_STOPOVER_POINTS == "No" || $MULTI_STOPOVER_POINTS != "Yes") { // Common
            $obj->sql_query("UPDATE configurations SET `vValue` = 'No',eAdminDisplay='No',eStatus='Inactive' WHERE vName='ENABLE_STOPOVER_POINT'");
            $obj->sql_query("UPDATE configurations SET eAdminDisplay='No',eStatus='Inactive' WHERE vName='MAX_NUMBER_STOP_OVER_POINTS'");
        } else {
            $obj->sql_query("UPDATE configurations SET `vValue` = 'Yes',eAdminDisplay='Yes',eStatus='Active' WHERE vName='ENABLE_STOPOVER_POINT'");
            $obj->sql_query("UPDATE configurations SET `vValue` = '3',eAdminDisplay='Yes',eStatus='Active' WHERE vName='MAX_NUMBER_STOP_OVER_POINTS'");
        }
        //Added By HJ On 12-07-2019 For Set New Addon Configuration Based On Selection End
        if ($ePackageType == 'standard') { // Common
            $obj->sql_query("UPDATE configurations SET `vValue` = 'No',eAdminDisplay='No',eStatus='Inactive' WHERE vName='WAYBILL_ENABLE'");
            $obj->sql_query("UPDATE configurations SET `vValue` = 'Normal',eAdminDisplay='No',eStatus='Inactive' WHERE vName='RIDE_DRIVER_CALLING_METHOD'");
            $obj->sql_query("UPDATE configurations SET `vValue` = '',eAdminDisplay='No',eStatus='Inactive' WHERE (vName='SINCH_APP_ENVIRONMENT_HOST' || vName='SINCH_APP_SECRET_KEY' || vName='SINCH_APP_KEY')");
        } else {
            $obj->sql_query("UPDATE configurations SET `vValue` = 'Yes',eAdminDisplay='Yes',eStatus='Active' WHERE vName='WAYBILL_ENABLE'");
            $obj->sql_query("UPDATE configurations SET `vValue` = 'Voip',eAdminDisplay='Yes',eStatus='Active' WHERE vName='RIDE_DRIVER_CALLING_METHOD'");
            $obj->sql_query("UPDATE configurations SET `vValue` = 'sandbox.sinch.com',eAdminDisplay='Yes',eStatus='Active' WHERE vName='SINCH_APP_ENVIRONMENT_HOST'");
            $obj->sql_query("UPDATE configurations SET `vValue` = '',eAdminDisplay='Yes',eStatus='Active' WHERE (vName='SINCH_APP_SECRET_KEY' || vName='SINCH_APP_KEY')");
        }
        $obj->sql_query("UPDATE configurations_payment SET `vValue` = 'Method-1',eAdminDisplay='No',eStatus='Inactive' WHERE vName='SYSTEM_PAYMENT_FLOW'");
        $obj->sql_query("UPDATE configurations SET `vValue` = 'NonStrict',eAdminDisplay='No',eStatus='Inactive' WHERE vName='APP_DESTINATION_MODE'");
        $obj->sql_query("UPDATE configurations SET `vValue` = 'Yes',eAdminDisplay='No',eStatus='Inactive' WHERE (vName='PUBNUB_DISABLED' || vName='ENABLE_PUBNUB' || vName='ENABLE_DELIVERY_MODULE')");
        if (strtoupper($eDeliveryType) == 'NONE' || trim($eDeliveryType) == "") { // Common
            $obj->sql_query("UPDATE configurations SET `vValue` = 'No',eAdminDisplay='No',eStatus='Inactive' WHERE vName='ENABLE_DELIVERY_MODULE'");
            $obj->sql_query("UPDATE `configurations` SET `vValue`='NONE' WHERE vName = 'APP_DELIVERY_MODE'");
            $obj->sql_query("UPDATE configurations SET eAdminDisplay='No',eStatus='Inactive' WHERE eFor='Delivery' || eFor='Multi-Delivery'");
        }
        $obj->sql_query("UPDATE configurations SET `vValue` = 'No',eAdminDisplay='Yes',eStatus='Active' WHERE vName='MAILGUN_ENABLE'");
        $obj->sql_query("UPDATE configurations SET `vValue` = '',eAdminDisplay='Yes',eStatus='Active' WHERE (vName='MAILGUN_USER' || vName='MAILGUN_KEY')");
        $obj->sql_query("UPDATE configurations SET `vValue` = 'Provider',eAdminDisplay='No',eStatus='Inactive' WHERE vName='SERVICE_PROVIDER_FLOW'");
        $obj->sql_query("UPDATE configurations SET `vValue` = 'Yes',eAdminDisplay='No',eStatus='Inactive' WHERE vName='CALLMASKING_ENABLED'"); // Added By HJ On 29-07-2019 As Per Discuss With KS Sir
        if ($ePackageType == 'standard' || $ePackageType == 'enterprise') { // Common
            $obj->sql_query("UPDATE configurations SET `vValue` = '5',eAdminDisplay='No',eStatus='Inactive' WHERE vName='SHOW_ADVERTISE_AFTER_MINUTES'");
            $obj->sql_query("UPDATE configurations SET `vValue` = '24',eAdminDisplay='No',eStatus='Inactive' WHERE vName='CANCEL_DECLINE_TRIPS_IN_HOURS'");
            $obj->sql_query("UPDATE configurations SET `vValue` = '',eAdminDisplay='No',eStatus='Inactive' WHERE (vName='LINKEDIN_APP_SECRET_KEY' || vName='LINKEDIN_APP_ID')");
            $obj->sql_query("UPDATE configurations SET `vValue` = 'Disable',eAdminDisplay='No',eStatus='Inactive' WHERE vName='ADVERTISEMENT_TYPE'");
            $obj->sql_query("UPDATE configurations SET `vValue` = 'No',eAdminDisplay='No',eStatus='Inactive' WHERE (vName='ENABLE_NEWSLETTERS_SUBSCRIPTION_SECTION' || vName='ENABLE_NEWS_SECTION' || vName='ENABLE_LIVE_CHAT' || vName='ENABLE_DRIVER_ADVERTISEMENT_BANNER' || vName='ENABLE_RIDER_ADVERTISEMENT_BANNER' || vName='LIVE_CHAT_LICENCE_NUMBER' || vName='PASSENGER_LINKEDIN_LOGIN' || vName='DRIVER_LINKEDIN_LOGIN')");
        } else {
            $obj->sql_query("UPDATE configurations SET `vValue` = 'Yes',eAdminDisplay='Yes',eStatus='Active' WHERE (vName='ENABLE_NEWSLETTERS_SUBSCRIPTION_SECTION' || vName='ENABLE_NEWS_SECTION' || vName='ENABLE_LIVE_CHAT' || vName='ENABLE_DRIVER_ADVERTISEMENT_BANNER' || vName='ENABLE_RIDER_ADVERTISEMENT_BANNER' || vName='PASSENGER_LINKEDIN_LOGIN' || vName='DRIVER_LINKEDIN_LOGIN')");
            //$obj->sql_query("UPDATE configurations SET `vValue` = 'sandbox.sinch.com',eAdminDisplay='Yes',eStatus='Active' WHERE vName='SINCH_APP_ENVIRONMENT_HOST'");
            $obj->sql_query("UPDATE configurations SET `vValue` = '5',eAdminDisplay='Yes',eStatus='Active' WHERE vName='SHOW_ADVERTISE_AFTER_MINUTES'");
            $obj->sql_query("UPDATE configurations SET `vValue` = '24',eAdminDisplay='Yes',eStatus='Active' WHERE vName='CANCEL_DECLINE_TRIPS_IN_HOURS'");
            //$obj->sql_query("UPDATE configurations SET `vValue` = '',eAdminDisplay='Yes',eStatus='Active' WHERE (vName='SINCH_APP_SECRET_KEY' || vName='SINCH_APP_KEY')");
            $obj->sql_query("UPDATE configurations SET `vValue` = 'Sequential',eAdminDisplay='No',eStatus='Inactive' WHERE vName='ADVERTISEMENT_TYPE'");
        }
        if (DELIVERALL == "Yes" && strtoupper($ePackageType) == 'SHARK') { // Common
            $obj->sql_query("UPDATE configurations SET `vValue` = 'Yes',eAdminDisplay='Yes',eStatus='Active' WHERE (vName='ENABLE_RESTAURANTS_ADVERTISEMENT_BANNER')");
        } else {
            $obj->sql_query("UPDATE configurations SET `vValue` = 'No',eAdminDisplay='No',eStatus='Inactive' WHERE (vName='ENABLE_RESTAURANTS_ADVERTISEMENT_BANNER')");
        }
        $obj->sql_query("UPDATE configurations SET `vValue` = 'No',eAdminDisplay='No',eStatus='Inactive' WHERE (vName='ENABLE_INSURANCE_ACCEPT_REPORT' || vName='ENABLE_INSURANCE_TRIP_REPORT' || vName='ENABLE_INSURANCE_IDLE_REPORT' || vName='WHEEL_CHAIR_ACCESSIBILITY_OPTION' || vName='PASSENGER_TWITTER_LOGIN' || vName='DRIVER_TWITTER_LOGIN')");
        if (($eProductType == 'Delivery' || $eProductType == 'UberX') || ($ePackageType == 'standard' || $ePackageType == 'enterprise') || ONLYDELIVERALL == "Yes") { // Done
            $obj->sql_query("UPDATE configurations SET `vValue` = 'No',eAdminDisplay='No',eStatus='Inactive' WHERE (vName='ENABLE_INTRANSIT_SHOPPING_SYSTEM' || vName='ENABLE_AIRPORT_SURCHARGE_SECTION' || vName='BOOK_FOR_ELSE_ENABLE' || vName='CHILD_SEAT_ACCESSIBILITY_OPTION' || vName='POOL_ENABLE' || vName='ENABLE_CORPORATE_PROFILE' || vName='FEMALE_RIDE_REQ_ENABLE')");
            $obj->sql_query("UPDATE configurations SET `vValue` = '5',eAdminDisplay='No',eStatus='Inactive' WHERE (vName='RESTRICTION_KM_NEAREST_DESTINATION_POOL' || vName='RESTRICTION_KM_NEAREST_TAXI_POOL' || vName='BOOK_FOR_ELSE_SHOW_NO_CONTACT')");
        } else {
            $obj->sql_query("UPDATE configurations SET `vValue` = 'Yes',eAdminDisplay='Yes',eStatus='Active' WHERE (vName='ENABLE_INTRANSIT_SHOPPING_SYSTEM' || vName='ENABLE_AIRPORT_SURCHARGE_SECTION' || vName='BOOK_FOR_ELSE_ENABLE' || vName='CHILD_SEAT_ACCESSIBILITY_OPTION' || vName='POOL_ENABLE' || vName='ENABLE_CORPORATE_PROFILE')");
            $obj->sql_query("UPDATE configurations SET `vValue` = '5',eAdminDisplay='Yes',eStatus='Active' WHERE (vName='RESTRICTION_KM_NEAREST_DESTINATION_POOL' || vName='RESTRICTION_KM_NEAREST_TAXI_POOL' || vName='BOOK_FOR_ELSE_SHOW_NO_CONTACT')");
        }
        if (($eProductType == "Ride-Delivery-UberX" || $eProductType == "UberX") && ONLYDELIVERALL != "Yes") { // Done
            $obj->sql_query("UPDATE configurations SET eAdminDisplay='Yes',eStatus='Active' WHERE vName='GRID_TILES_MAX_COUNT'");
        } else {
            $obj->sql_query("UPDATE configurations SET `vValue` = '9',eAdminDisplay='No',eStatus='Inactive' WHERE vName='GRID_TILES_MAX_COUNT'");
        }
        $obj->sql_query("UPDATE configurations SET `vValue` = 'Single',eAdminDisplay='No',eStatus='Inactive' WHERE vName='DELIVERY_MODULE_MODE'");
        $obj->sql_query("UPDATE configurations SET `vValue` = '',eAdminDisplay='No',eStatus='Inactive' WHERE vName='YALGAAR_CLIENT_KEY'");
        if (strtoupper($ePackageType) != 'SHARK') { // Common
            $obj->sql_query("UPDATE configurations SET `vValue` = '',eAdminDisplay='No',eStatus='Inactive' WHERE vName='EXCHANGE_CURRENCY_RATES_APP_ID'");
        } else {
            $obj->sql_query("UPDATE configurations SET `vValue` = '',eAdminDisplay='Yes',eStatus='Inactive' WHERE vName='EXCHANGE_CURRENCY_RATES_APP_ID'");
        }
        $obj->sql_query("UPDATE configurations SET `vValue` = 'SocketCluster',eAdminDisplay='No',eStatus='Inactive' WHERE vName='PUBSUB_TECHNIQUE'");
        $obj->sql_query("UPDATE configurations SET `vValue` = '2',eAdminDisplay='Yes',eStatus='Active' WHERE vName='MIN_CHAR_REQ_GOOGLE_AUTO_COMPLETE'");
        $obj->sql_query("UPDATE configurations SET `vValue` = '30-180',eAdminDisplay='No',eStatus='Inactive' WHERE vName='FETCH_TRIP_STATUS_TIME_INTERVAL'");
        $obj->sql_query("UPDATE configurations SET `vValue` = '100',eAdminDisplay='No',eStatus='Inactive' WHERE vName='SITE_POLICE_CONTROL_NUMBER'");
        $obj->sql_query("UPDATE configurations SET `vValue` = '1',eAdminDisplay='Yes',eStatus='Inactive' WHERE vName='DESTINATION_UPDATE_TIME_INTERVAL'");
        $obj->sql_query("UPDATE configurations SET `vValue` = '1',eAdminDisplay='No',eStatus='Inactive' WHERE vName='ONLINE_DRIVER_LIST_UPDATE_TIME_INTERVAL'");
        $obj->sql_query("UPDATE configurations SET `vValue` = 'sec-c-KoPMtUgEL2QPdViKFr88UiKlOlReQWSyRGE6IJFROvgbLbKY',eAdminDisplay='No',eStatus='Inactive' WHERE vName='PUBNUB_SECRET_KEY'");
        $obj->sql_query("UPDATE configurations SET `vValue` = 'sub-c-9r3u6k8c-h9kl-66s9-b85h-d8e695euy20k',eAdminDisplay='No',eStatus='Inactive' WHERE vName='PUBNUB_SUBSCRIBE_KEY'");
        $obj->sql_query("UPDATE configurations SET `vValue` = 'pub-c-49394564-gr96-95g7-8530-96f5f2dv9w53',eAdminDisplay='No',eStatus='Inactive' WHERE vName='PUBNUB_PUBLISH_KEY'");
        $obj->sql_query("UPDATE configurations SET `vValue` = 'fg5k3i7i7l5ghgk1jcv43w0j41',eAdminDisplay='No',eStatus='Inactive' WHERE vName='PUBNUB_UUID'");
        if ($ePackageType != 'standard' && $eProductType != "UberX" && $eProductType != "Ride" && ONLYDELIVERALL == "No") { // Done
            $obj->sql_query("UPDATE configurations SET `vValue` = '5',eAdminDisplay='Yes',eStatus='Active' WHERE vName='MAX_ALLOW_NUM_DESTINATION_MULTI'");
            $obj->sql_query("UPDATE configurations SET `vValue` = 'Yes',eAdminDisplay='Yes',eStatus='Active' WHERE (vName='ENABLE_ROUTE_CALCULATION_MULTI' || vName='ENABLE_ROUTE_OPTIMIZE_MULTI')");
        } else {
            $obj->sql_query("UPDATE configurations SET `vValue` = '1',eAdminDisplay='No',eStatus='Inactive' WHERE vName='MAX_ALLOW_NUM_DESTINATION_MULTI'");
            $obj->sql_query("UPDATE configurations SET `vValue` = 'No',eAdminDisplay='No',eStatus='Inactive' WHERE (vName='ENABLE_ROUTE_CALCULATION_MULTI' || vName='ENABLE_ROUTE_OPTIMIZE_MULTI')");
        }
        if ($kioskPanelEnable == "Yes") { // Common
            $obj->sql_query("UPDATE configurations SET `vValue` = '45',eAdminDisplay='Yes',eStatus='Active' WHERE vName='KIOSK_BOOKING_CONFIRM_TIME_IN_SECONDS'");
            $obj->sql_query("UPDATE configurations SET eAdminDisplay='Yes',eStatus='Active' WHERE (vName='KIOSK_IOS_APP_VERSION' || vName='KIOSK_ANDROID_APP_VERSION')");
        } else {
            $obj->sql_query("UPDATE configurations SET `vValue` = '45',eAdminDisplay='No',eStatus='Inactive' WHERE vName='KIOSK_BOOKING_CONFIRM_TIME_IN_SECONDS'");
            $obj->sql_query("UPDATE configurations SET eAdminDisplay='No',eStatus='Inactive' WHERE (vName='KIOSK_IOS_APP_VERSION' || vName='KIOSK_ANDROID_APP_VERSION')");
        }
        if (ONLYDELIVERALL == "Yes" || DELIVERALL == "Yes") { // Common
            $obj->sql_query("UPDATE configurations SET `vValue` = 'Yes',eAdminDisplay='Yes',eStatus='Active' WHERE (vName='COMPANY_PHONE_VERIFICATION' || vName='COMPANY_EMAIL_VERIFICATION')");
            $obj->sql_query("UPDATE configurations SET `vValue` = '10',eAdminDisplay='Yes',eStatus='Active' WHERE (vName='MIN_ORDER_CANCELLATION_CHARGES' || vName='ADMIN_COMMISSION')");
            $obj->sql_query("UPDATE configurations SET `vValue` = '5',eAdminDisplay='Yes',eStatus='Active' WHERE vName='LIST_RESTAURANT_LIMIT_BY_DISTANCE'");
        } else {
            $obj->sql_query("UPDATE configurations SET `vValue` = 'No',eAdminDisplay='No',eStatus='Inactive' WHERE (vName='COMPANY_PHONE_VERIFICATION' || vName='COMPANY_EMAIL_VERIFICATION')");
            $obj->sql_query("UPDATE configurations SET `vValue` = '10',eAdminDisplay='No',eStatus='Inactive' WHERE (vName='MIN_ORDER_CANCELLATION_CHARGES' || vName='ADMIN_COMMISSION')");
            $obj->sql_query("UPDATE configurations SET `vValue` = '5',eAdminDisplay='No',eStatus='Inactive' WHERE vName='LIST_RESTAURANT_LIMIT_BY_DISTANCE'");
        }
        $obj->sql_query("UPDATE configurations SET `vValue` = 'Yes',eAdminDisplay='No',eStatus='Inactive' WHERE vName='ENABLE_SOCKET_CLUSTER'");
        $obj->sql_query("UPDATE configurations SET `vValue` = '5',eAdminDisplay='No',eStatus='Inactive' WHERE vName='PUBSUB_PUBLISH_DRIVER_LOC_DISTANCE_LIMIT'");
        $obj->sql_query("UPDATE configurations SET `vValue` = '15',eAdminDisplay='Yes',eStatus='Active' WHERE vName='VERIFICATION_CODE_RESEND_TIME_IN_SECONDS_EMERGENCY'");
        $obj->sql_query("UPDATE configurations SET `vValue` = '30',eAdminDisplay='Yes',eStatus='Active' WHERE vName='VERIFICATION_CODE_RESEND_TIME_IN_SECONDS'");
        $obj->sql_query("UPDATE configurations SET `vValue` = '5',eAdminDisplay='Yes',eStatus='Active' WHERE (vName='VERIFICATION_CODE_RESEND_COUNT_EMERGENCY' || vName='VERIFICATION_CODE_RESEND_COUNT_RESTRICTION_EMERGENCY' || vName='VERIFICATION_CODE_RESEND_COUNT_RESTRICTION' || vName='VERIFICATION_CODE_RESEND_COUNT')");
        $obj->sql_query("UPDATE configurations SET `vValue` = '125-300',eAdminDisplay='No',eStatus='Inactive' WHERE vName='AIRPORT_TIME_INTERVAL'");
        if ($ePackageType != 'standard' && $eProductType != 'Delivery' && $eProductType != 'UberX' && ONLYDELIVERALL != "Yes") { // Done
            $obj->sql_query("UPDATE configurations SET `vValue` = 'Yes',eAdminDisplay='Yes',eStatus='Active' WHERE (vName='ENABLE_WAITING_CHARGE_FLAT_TRIP' || vName='ENABLE_WAITING_CHARGE_RENTAL' || vName='ENABLE_SURGE_CHARGE_RENTAL')");
        } else {
            $obj->sql_query("UPDATE configurations SET `vValue` = 'No',eAdminDisplay='No',eStatus='Inactive' WHERE (vName='ENABLE_WAITING_CHARGE_FLAT_TRIP' || vName='ENABLE_WAITING_CHARGE_RENTAL' || vName='ENABLE_SURGE_CHARGE_RENTAL')");
        }
        if (($eProductType == "UberX" || $eProductType == "Ride-Delivery-UberX") && ONLYDELIVERALL == "No") { // Done
            $obj->sql_query("UPDATE configurations SET `vValue` = 'Yes',eAdminDisplay='Yes',eStatus='Active' WHERE (vName='PROVIDER_AVAIL_LOC_CUSTOMIZE')");
            $obj->sql_query("UPDATE configurations SET `vValue` = '30',eAdminDisplay='Yes',eStatus='Active' WHERE vName='BOOKING_LATER_ACCEPT_AFTER_INTERVAL'");
            $obj->sql_query("UPDATE configurations SET `vValue` = '120',eAdminDisplay='Yes',eStatus='Active' WHERE vName='BOOKING_LATER_ACCEPT_BEFORE_INTERVAL'");
        } else {
            $obj->sql_query("UPDATE configurations SET `vValue` = 'No',eAdminDisplay='No',eStatus='Inactive' WHERE (vName='PROVIDER_AVAIL_LOC_CUSTOMIZE')");
            $obj->sql_query("UPDATE configurations SET `vValue` = '30',eAdminDisplay='No',eStatus='Inactive' WHERE vName='BOOKING_LATER_ACCEPT_AFTER_INTERVAL'");
            $obj->sql_query("UPDATE configurations SET `vValue` = '120',eAdminDisplay='No',eStatus='Inactive' WHERE vName='BOOKING_LATER_ACCEPT_BEFORE_INTERVAL'");
        }
        $obj->sql_query("UPDATE configurations SET eAdminDisplay='No',eStatus='Inactive' WHERE vName='ALLOW_SERVICE_PROVIDER_AMOUNT'");
        if ($eProductType != 'Delivery' && $eProductType != 'UberX' && ONLYDELIVERALL == "No" && $ePackageType != "standard") { //Done
            $obj->sql_query("UPDATE configurations SET `vValue` = 'Yes',eAdminDisplay='Yes',eStatus='Active' WHERE (vName='APPLY_SURGE_ON_FLAT_FARE' || vName='ENABLE_HAIL_RIDES')");
            $obj->sql_query("UPDATE configurations SET `vValue` = 'No',eAdminDisplay='Yes',eStatus='Active' WHERE  vName='ENABLE_TOLL_COST'");
        } else {
            $obj->sql_query("UPDATE configurations SET `vValue` = 'No',eAdminDisplay='No',eStatus='Inactive' WHERE (vName='APPLY_SURGE_ON_FLAT_FARE' || vName='ENABLE_HAIL_RIDES' || vName='ENABLE_TOLL_COST')");
        }
        $obj->sql_query("UPDATE configurations SET `vValue` = '#',eAdminDisplay='Yes',eStatus='Active' WHERE (vName='TOLL_COST_APP_CODE' || vName='TOLL_COST_APP_ID')");
        if ($eProductType == 'UberX' || $eProductType == 'Delivery' || $eProductType == 'Foodonly' || $eProductType == 'Deliverall') { // Done
            $sql8 = $obj->sql_query("UPDATE configurations SET `vValue` = 'No',`eAdminDisplay` = 'No',eStatus='Inactive' WHERE (vName = 'FEMALE_RIDE_REQ_ENABLE' || vName = 'HANDICAP_ACCESSIBILITY_OPTION' ||  vName =  'ENABLE_WAITING_CHARGE_FLAT_TRIP' ||  vName =  'APPLY_SURGE_ON_FLAT_FARE')");
        }
        if ($eProductType == "UberX") { // Done
            $obj->sql_query("UPDATE configurations SET `vValue` = 'Yes',eAdminDisplay='No',eStatus='Inactive' WHERE vName='ENABLE_EDIT_DRIVER_VEHICLE'");
        } else {
            $obj->sql_query("UPDATE configurations SET `vValue` = 'Yes',eAdminDisplay='Yes',eStatus='Active' WHERE vName='ENABLE_EDIT_DRIVER_VEHICLE'");
        }
        if ($eProductType == "UberX" || ONLYDELIVERALL == "Yes") { // Done
            $obj->sql_query("UPDATE configurations SET `vValue` = 'All',eAdminDisplay='No',eStatus='Inactive' WHERE vName='DRIVER_REQUEST_METHOD'");
        } else {
            $obj->sql_query("UPDATE configurations SET `vValue` = 'All',eAdminDisplay='Yes',eStatus='Active' WHERE vName='DRIVER_REQUEST_METHOD'");
        }
        if ($eProductType != 'Delivery' && $eProductType != 'UberX' && ONLYDELIVERALL == "No") { // Done
            $obj->sql_query("UPDATE configurations SET `vValue` = 'Yes',eAdminDisplay='Yes',eStatus='Active' WHERE (vName='IS_DEST_ANYTIME_CHANGE' || vName='ENABLE_TIP_MODULE')");
        } else {
            $obj->sql_query("UPDATE configurations SET `vValue` = 'No',eAdminDisplay='No',eStatus='Inactive' WHERE (vName='IS_DEST_ANYTIME_CHANGE' || vName='ENABLE_TIP_MODULE')");
        }
        if ($eDeliveryType == 'Multi' && $eProductType != 'Ride' && $eProductType != 'UberX' && ONLYDELIVERALL == "No") { // Done
            $obj->sql_query("UPDATE configurations SET `vValue` = 'Code',eAdminDisplay='Yes',eStatus='Active' WHERE vName='DELIVERY_VERIFICATION_METHOD'");
        } else {
            $obj->sql_query("UPDATE configurations SET `vValue` = 'None',eAdminDisplay='No',eStatus='Inactive' WHERE vName='DELIVERY_VERIFICATION_METHOD'");
        }
        if ($eProductType == "Delivery") {
            $obj->sql_query("UPDATE configurations SET `vValue` = 'No',eAdminDisplay='Yes',eStatus='Active' WHERE (vName='ENABLE_ROUTE_CALCULATION_MULTI' || vName='ENABLE_ROUTE_OPTIMIZE_MULTI')");
            $obj->sql_query("UPDATE configurations SET eAdminDisplay='No',eStatus='Inactive' WHERE (vName='ENABLE_SURGE_CHARGE_RENTAL' || vName='ENABLE_WAITING_CHARGE_RENTAL' || vName='ENABLE_WAITING_CHARGE_FLAT_TRIP' || vName='KIOSK_BOOKING_CONFIRM_TIME_IN_SECONDS' || vName='PROVIDER_BOOKING_ACCEPT_TIME_INTERVAL')");
        }
        if ($eProductType == "Ride-Delivery-UberX" && strtoupper($ePackageType) != "SHARK") { //Common
            $obj->sql_query("UPDATE configurations SET eAdminDisplay='No',eStatus='Inactive' WHERE (vName='DELIVERY_VERIFICATION_METHOD' || vName='GRID_TILES_MAX_COUNT' || vName='ENABLE_ROUTE_CALCULATION_MULTI' || vName='ENABLE_ROUTE_OPTIMIZE_MULTI' || vName='MAX_ALLOW_NUM_DESTINATION_MULTI')");
        }
        if (ONLYDELIVERALL == "Yes") { //Common
            $obj->sql_query("UPDATE configurations_payment SET `vValue` = 'Yes',eAdminDisplay='No',eStatus='Inactive' WHERE vName='COMMISION_DEDUCT_ENABLE'");
            $obj->sql_query("UPDATE configurations SET eAdminDisplay='No',eStatus='Inactive' WHERE (vName='RIDE_LATER_BOOKING_ENABLED' || vName='DRIVER_REQUEST_METHOD' || vName='VERIFICATION_CODE_RESEND_COUNT_RESTRICTION_EMERGENCY' || vName='VERIFICATION_CODE_RESEND_COUNT_EMERGENCY' || vName='VERIFICATION_CODE_RESEND_TIME_IN_SECONDS_EMERGENCY' || vName='WAYBILL_ENABLE' || vName='PROVIDER_BOOKING_ACCEPT_TIME_INTERVAL' || vName='CANCEL_DECLINE_TRIPS_IN_HOURS')");
        }
        //Added By HJ On 14-10-2019 For Remove SP App Files Start
        if (($eProductType == "UberX" || $eProductType == "Ride-Delivery-UberX" || $eProductType == "Ride-Delivery-UberX-Shark") && $eCubeX != "Yes") { // Done
            $obj->sql_query("UPDATE configurations SET `vValue` = 'Yes',eAdminDisplay='Yes',eStatus='Active' WHERE vName='ENABLE_DRIVER_SERVICE_REQUEST_MODULE'");
        } else {
            $obj->sql_query("UPDATE configurations SET `vValue` = 'No',eAdminDisplay='No',eStatus='Inactive' WHERE (vName='ENABLE_DRIVER_SERVICE_REQUEST_MODULE' || vName='ENABLE_EDIT_DRIVER_SERVICE')");
        }
        //Added By HJ On 14-10-2019 For Remove SP App Files End
        if ($eProductType == "UberX") { // Done
            $obj->sql_query("UPDATE configurations SET `vValue` = 'No',eAdminDisplay='No',eStatus='Inactive' WHERE vName='WAYBILL_ENABLE'");
        }
        if ($eProductType == "Foodonly") { // Done
            $obj->sql_query("UPDATE configurations SET `vValue` = 'No',eAdminDisplay='No',eStatus='Inactive' WHERE vName='ENABLE_EDIT_DRIVER_SERVICE'");
        }
        $cubejekXConfigArr= array();
        if (strtoupper($eCubeX) == "YES" || strtoupper($eCubejekX) == "YES" || strtoupper($eDeliverallX) == "YES") {
            $cubejekXConfigArr["CREDIT_TO_WALLET_ENABLE"] = array("vValue" => "No", "eAdminDisplay" => "Yes", "eStatus" => "Active");
            if (strtoupper($Ride) == "NO") {
                $cubejekXConfigArr["ENABLE_INTRANSIT_SHOPPING_SYSTEM"] = array("vValue" => "No", "eAdminDisplay" => "No", "eStatus" => "Inactive");
                $cubejekXConfigArr["ENABLE_AIRPORT_SURCHARGE_SECTION"] = array("vValue" => "No", "eAdminDisplay" => "No", "eStatus" => "Inactive");
                $cubejekXConfigArr["BOOK_FOR_ELSE_ENABLE"] = array("vValue" => "No", "eAdminDisplay" => "No", "eStatus" => "Inactive");
                $cubejekXConfigArr["CHILD_SEAT_ACCESSIBILITY_OPTION"] = array("vValue" => "No", "eAdminDisplay" => "No", "eStatus" => "Inactive");
                $cubejekXConfigArr["POOL_ENABLE"] = array("vValue" => "No", "eAdminDisplay" => "No", "eStatus" => "Inactive");
                $cubejekXConfigArr["ENABLE_CORPORATE_PROFILE"] = array("vValue" => "No", "eAdminDisplay" => "No", "eStatus" => "Inactive");
                $cubejekXConfigArr["FEMALE_RIDE_REQ_ENABLE"] = array("vValue" => "No", "eAdminDisplay" => "No", "eStatus" => "Inactive");
                $cubejekXConfigArr["ENABLE_WAITING_CHARGE_FLAT_TRIP"] = array("vValue" => "No", "eAdminDisplay" => "No", "eStatus" => "Inactive");
                $cubejekXConfigArr["ENABLE_WAITING_CHARGE_RENTAL"] = array("vValue" => "No", "eAdminDisplay" => "No", "eStatus" => "Inactive");
                $cubejekXConfigArr["ENABLE_SURGE_CHARGE_RENTAL"] = array("vValue" => "No", "eAdminDisplay" => "No", "eStatus" => "Inactive");
                $cubejekXConfigArr["RESTRICTION_KM_NEAREST_DESTINATION_POOL"] = array("vValue" => "5", "eAdminDisplay" => "No", "eStatus" => "Inactive");
                $cubejekXConfigArr["RESTRICTION_KM_NEAREST_TAXI_POOL"] = array("vValue" => "5", "eAdminDisplay" => "No", "eStatus" => "Inactive");
                $cubejekXConfigArr["BOOK_FOR_ELSE_SHOW_NO_CONTACT"] = array("vValue" => "5", "eAdminDisplay" => "No", "eStatus" => "Inactive");
                $cubejekXConfigArr["APPLY_SURGE_ON_FLAT_FARE"] = array("vValue" => "No", "eAdminDisplay" => "No", "eStatus" => "Inactive");
                $cubejekXConfigArr["ENABLE_HAIL_RIDES"] = array("vValue" => "No", "eAdminDisplay" => "No", "eStatus" => "Inactive");
                $cubejekXConfigArr["ENABLE_TOLL_COST"] = array("vValue" => "No", "eAdminDisplay" => "No", "eStatus" => "Inactive");
                $cubejekXConfigArr["HANDICAP_ACCESSIBILITY_OPTION"] = array("vValue" => "No", "eAdminDisplay" => "No", "eStatus" => "Inactive");
                $cubejekXConfigArr["IS_DEST_ANYTIME_CHANGE"] = array("vValue" => "No", "eAdminDisplay" => "No", "eStatus" => "Inactive");
                $cubejekXConfigArr["ENABLE_TIP_MODULE"] = array("vValue" => "No", "eAdminDisplay" => "No", "eStatus" => "Inactive");
            } else {
                $cubejekXConfigArr["ENABLE_INTRANSIT_SHOPPING_SYSTEM"] = array("vValue" => "Yes", "eAdminDisplay" => "Yes", "eStatus" => "Active");
                $cubejekXConfigArr["ENABLE_AIRPORT_SURCHARGE_SECTION"] = array("vValue" => "Yes", "eAdminDisplay" => "Yes", "eStatus" => "Active");
                $cubejekXConfigArr["BOOK_FOR_ELSE_ENABLE"] = array("vValue" => "Yes", "eAdminDisplay" => "Yes", "eStatus" => "Active");
                $cubejekXConfigArr["CHILD_SEAT_ACCESSIBILITY_OPTION"] = array("vValue" => "Yes", "eAdminDisplay" => "Yes", "eStatus" => "Active");
                $cubejekXConfigArr["POOL_ENABLE"] = array("vValue" => "Yes", "eAdminDisplay" => "Yes", "eStatus" => "Active");
                $cubejekXConfigArr["ENABLE_CORPORATE_PROFILE"] = array("vValue" => "Yes", "eAdminDisplay" => "Yes", "eStatus" => "Active");
                $cubejekXConfigArr["FEMALE_RIDE_REQ_ENABLE"] = array("vValue" => "Yes", "eAdminDisplay" => "Yes", "eStatus" => "Active");
                $cubejekXConfigArr["ENABLE_WAITING_CHARGE_FLAT_TRIP"] = array("vValue" => "Yes", "eAdminDisplay" => "Yes", "eStatus" => "Active");
                $cubejekXConfigArr["ENABLE_WAITING_CHARGE_RENTAL"] = array("vValue" => "Yes", "eAdminDisplay" => "Yes", "eStatus" => "Active");
                $cubejekXConfigArr["ENABLE_SURGE_CHARGE_RENTAL"] = array("vValue" => "Yes", "eAdminDisplay" => "Yes", "eStatus" => "Active");
                $cubejekXConfigArr["RESTRICTION_KM_NEAREST_DESTINATION_POOL"] = array("vValue" => "5", "eAdminDisplay" => "Yes", "eStatus" => "Active");
                $cubejekXConfigArr["RESTRICTION_KM_NEAREST_TAXI_POOL"] = array("vValue" => "5", "eAdminDisplay" => "Yes", "eStatus" => "Active");
                $cubejekXConfigArr["BOOK_FOR_ELSE_SHOW_NO_CONTACT"] = array("vValue" => "5", "eAdminDisplay" => "Yes", "eStatus" => "Active");
                $cubejekXConfigArr["APPLY_SURGE_ON_FLAT_FARE"] = array("vValue" => "Yes", "eAdminDisplay" => "Yes", "eStatus" => "Active");
                $cubejekXConfigArr["ENABLE_HAIL_RIDES"] = array("vValue" => "Yes", "eAdminDisplay" => "Yes", "eStatus" => "Active");
                $cubejekXConfigArr["ENABLE_TOLL_COST"] = array("vValue" => "No", "eAdminDisplay" => "Yes", "eStatus" => "Active");
                $cubejekXConfigArr["HANDICAP_ACCESSIBILITY_OPTION"] = array("vValue" => "Yes", "eAdminDisplay" => "Yes", "eStatus" => "Active");
                $cubejekXConfigArr["IS_DEST_ANYTIME_CHANGE"] = array("vValue" => "Yes", "eAdminDisplay" => "Yes", "eStatus" => "Active");
                $cubejekXConfigArr["ENABLE_TIP_MODULE"] = array("vValue" => "Yes", "eAdminDisplay" => "Yes", "eStatus" => "Active");
            }
            if (strtoupper($Fly) == "NO") {
                $cubejekXConfigArr["FLY_RADIUS"] = array("vValue" => "500", "eAdminDisplay" => "No", "eStatus" => "Inactive");
                $cubejekXConfigArr["ENABLE_FLY_VEHICLES"] = array("vValue" => "No", "eAdminDisplay" => "No", "eStatus" => "Inactive");
            }
            if (strtoupper($UberX) == "NO" && strtoupper($Ride) == "NO" && strtoupper($Delivery) == "YES") {
                $cubejekXConfigArr["MAX_ALLOW_NUM_DESTINATION_MULTI"] = array("vValue" => "5", "eAdminDisplay" => "Yes", "eStatus" => "Active");
                $cubejekXConfigArr["ENABLE_ROUTE_CALCULATION_MULTI"] = array("vValue" => "Yes", "eAdminDisplay" => "Yes", "eStatus" => "Active");
                $cubejekXConfigArr["ENABLE_ROUTE_OPTIMIZE_MULTI"] = array("vValue" => "Yes", "eAdminDisplay" => "Yes", "eStatus" => "Active");
            }
            if (strtoupper($Delivery) == "NO") {
                $cubejekXConfigArr["MAX_ALLOW_NUM_DESTINATION_MULTI"] = array("vValue" => "1", "eAdminDisplay" => "No", "eStatus" => "Inactive");
                $cubejekXConfigArr["ENABLE_ROUTE_CALCULATION_MULTI"] = array("vValue" => "No", "eAdminDisplay" => "No", "eStatus" => "Inactive");
                $cubejekXConfigArr["ENABLE_ROUTE_OPTIMIZE_MULTI"] = array("vValue" => "No", "eAdminDisplay" => "No", "eStatus" => "Inactive");
                $cubejekXConfigArr["DELIVERY_VERIFICATION_METHOD"] = array("vValue" => "None", "eAdminDisplay" => "No", "eStatus" => "Inactive");
            } else {
                $cubejekXConfigArr["DELIVERY_VERIFICATION_METHOD"] = array("vValue" => "Code", "eAdminDisplay" => "Yes", "eStatus" => "Active");
            }
            if (strtoupper($UberX) == "NO") {
                $cubejekXConfigArr["ENABLE_DRIVER_SERVICE_REQUEST_MODULE"] = array("vValue" => "No", "eAdminDisplay" => "No", "eStatus" => "Inactive");
                $cubejekXConfigArr["ENABLE_EDIT_DRIVER_SERVICE"] = array("vValue" => "No", "eAdminDisplay" => "No", "eStatus" => "Inactive");
                $cubejekXConfigArr["GRID_TILES_MAX_COUNT"] = array("vValue" => "9", "eAdminDisplay" => "No", "eStatus" => "Inactive");

                $cubejekXConfigArr["PROVIDER_AVAIL_LOC_CUSTOMIZE"] = array("vValue" => "No", "eAdminDisplay" => "No", "eStatus" => "Inactive");
                $cubejekXConfigArr["BOOKING_LATER_ACCEPT_AFTER_INTERVAL"] = array("vValue" => "30", "eAdminDisplay" => "No", "eStatus" => "Inactive");
                $cubejekXConfigArr["BOOKING_LATER_ACCEPT_BEFORE_INTERVAL"] = array("vValue" => "120", "eAdminDisplay" => "No", "eStatus" => "Inactive");
            } else {
                $cubejekXConfigArr["ENABLE_DRIVER_SERVICE_REQUEST_MODULE"] = array("vValue" => "Yes", "eAdminDisplay" => "Yes", "eStatus" => "Active");
                $cubejekXConfigArr["GRID_TILES_MAX_COUNT"] = array("vValue" => "9", "eAdminDisplay" => "Yes", "eStatus" => "Active");
                $cubejekXConfigArr["PROVIDER_AVAIL_LOC_CUSTOMIZE"] = array("vValue" => "Yes", "eAdminDisplay" => "Yes", "eStatus" => "Active");
                $cubejekXConfigArr["BOOKING_LATER_ACCEPT_AFTER_INTERVAL"] = array("vValue" => "30", "eAdminDisplay" => "Yes", "eStatus" => "Active");
                $cubejekXConfigArr["BOOKING_LATER_ACCEPT_BEFORE_INTERVAL"] = array("vValue" => "120", "eAdminDisplay" => "Yes", "eStatus" => "Active");
            }
            if (strtoupper($UberX) == "YES" && strtoupper($Ride) == "NO" && strtoupper($Delivery) == "NO") {
                $cubejekXConfigArr["WAYBILL_ENABLE"] = array("vValue" => "No", "eAdminDisplay" => "No", "eStatus" => "Inactive");
            } else {
                $cubejekXConfigArr["WAYBILL_ENABLE"] = array("vValue" => "Yes", "eAdminDisplay" => "Yes", "eStatus" => "Active");
            }
            if (strtoupper($Ride) == "YES" || strtoupper($Delivery) == "YES") {
                $cubejekXConfigArr["ENABLE_EDIT_DRIVER_VEHICLE"] = array("vValue" => "Yes", "eAdminDisplay" => "Yes", "eStatus" => "Active");
                $cubejekXConfigArr["DRIVER_REQUEST_METHOD"] = array("vValue" => "All", "eAdminDisplay" => "Yes", "eStatus" => "Active");
            }
            if (strtoupper($Ride) == "NO" && strtoupper($Delivery) == "NO") {
                $cubejekXConfigArr["ENABLE_EDIT_DRIVER_VEHICLE"] = array("vValue" => "Yes", "eAdminDisplay" => "No", "eStatus" => "Inactive");
                $cubejekXConfigArr["DRIVER_REQUEST_METHOD"] = array("vValue" => "All", "eAdminDisplay" => "No", "eStatus" => "Inactive");
            }
        }
        foreach($cubejekXConfigArr as $vname=>$values){
            $tableName = "configurations";
            if($vname == "CREDIT_TO_WALLET_ENABLE"){
                $tableName = "configurations_payment";
            }
            $vValue = $values['vValue'];
            $eAdminDisplay = $values['eAdminDisplay'];
            $eStatus = $values['eStatus'];
            $obj->sql_query("UPDATE ".$tableName." SET `vValue` = '".$vValue."',eAdminDisplay='".$eAdminDisplay."',eStatus='".$eStatus."' WHERE vName='".$vname."'");
        }
        $obj->sql_query("UPDATE setup_info SET `eConfigurationApplied` = 'Yes'");
    }
    //Added By HJ On 11-03-2019 For Set Default configurations As Per KS Sir End
    //Added By HJ On 20-03-2019 For Solved Bug - 6403 As Per Discuss with BM Mam QA. Start
    if (strtoupper($ePackageType) != "SHARK" || $eProductType == "Delivery" || $eProductType == "UberX" || ONLYDELIVERALL == "Yes") {
        $obj->sql_query("DELETE FROM vehicle_type WHERE ePoolStatus='Yes'");
    }
    $obj->sql_query("UPDATE `language_label` SET `vValue` = 'Tax' WHERE `vLabel` = 'LBL_TAX1_TXT' || `vLabel` = 'LBL_TAX2_TXT'");
    //Added By HJ On 20-03-2019 For Solved Bug - 6403 As Per Discuss with BM Mam QA. End
    //echo "<pre>";print_r($deleteFileArr);die;
    //Added BY HJ On 11-03-2019 For Auto Remove File Start
    $fileCount = count($deleteFileArr);
    if (count($deleteFileArr) > 0) {
        $shFilePath = dirname(__FILE__) . "/setup_info/setup.sh";
        $fp = fopen($shFilePath, "w");
        fwrite($fp, "#!/bin/bash");
        fwrite($fp, "\n");
        for ($g = 0; $g < count($deleteFileArr); $g++) {
            $filename = $deleteFileArr[$g] . "\n";
            //fwrite($fp, "rm -f " . $filename);
            fwrite($fp, "rm -R -f " . $filename);
            //unlink($filename);
            /* if (is_writable($filename)) {
              //unlink($filename);
              echo "Writable - " . $filename . "<br>";
              } else {
              echo "Not Writable - " . $filename . "<br>";
              } */
        }
    }
    //Added BY HJ On 11-03-2019 For Auto Remove File End
    //Added BY HJ On 12-03-2019 For Check webimages Folder Permission for Upload Image Start
    $webImagePermission = dirname(__FILE__) . "/webimages";
    if (!is_writable($webImagePermission)) {
        echo "<li>Please Set write permission to folder " . $webImagePermission . "</li>";
        $errorcountsystemvalidation += 1;
    }
    //Added BY HJ On 12-03-2019 For Check webimages Folder Permission for Upload Image Start
    //Added BY HJ On 12-03-2019 For Set Default Configuration in php.ini file Start
    $memory_limit = trim(ini_get('memory_limit'), "M"); // Must Be -1
    if ($memory_limit != "-1") {
        echo "<li>Please Set memory_limit '-1' in php.ini configuration file.</li>";
        $errorcountsystemvalidation += 1;
    }
    $upload_max_filesize = getUploadSizeInMB(ini_get('upload_max_filesize')); // Must Be Min 100
    if ($upload_max_filesize < 100) {
        echo "<li>Please Set upload_max_filesize minimum 100 in php.ini configuration file.</li>";
        $errorcountsystemvalidation += 1;
    }
    $post_max_size = getUploadSizeInMB(ini_get('post_max_size')); // Must Be Min 100
    if ($post_max_size < 100) {
        echo "<li>Please Set post_max_size minimum 100 in php.ini configuration file.</li>";
        $errorcountsystemvalidation += 1;
    }
    $post_max_execution_time = getUploadSizeInMB(ini_get('max_execution_time')); // Must Be 0
    if ($post_max_execution_time != 0) {
        echo "<li>Please Set max_execution_time 0 in php.ini configuration file.</li>";
        $errorcountsystemvalidation += 1;
    }
    //Added BY HJ On 12-03-2019 For Set Default Configuration in php.ini file End
    echo '</ol>';
    if ($errorcountsystemvalidation > 0) {
        echo '<ol class="validation">';
        $notUsedFolder = dirname(__FILE__) . "/" . ADMIN_URL_CLIENT . "/NOTUSED";
        $translateFolder = dirname(__FILE__) . "/translate";
        if (file_exists($notUsedFolder) || file_exists($translateFolder)) {
            echo "<li>[Note :Please Remove Below Folder in all APP Type.] <br/> ";
        }
        $number = 0;
        if (file_exists($notUsedFolder)) {
            $number += 1;
            echo $number . ". Remove admin/NOTUSED folder in admin panel. <br/>";
        }
        if (file_exists($translateFolder)) {
            $number += 1;
            echo $number . ". Remove 'translate' folder in root.";
        }
        if ($eProductType != 'Foodonly' && $eProductType != 'Deliverall') {
            if ($ePackageType == 'standard') {
                ?>
                [Note : Please Remove location wise fare menu From admin left menu and geo location files.]
                <?php
            }
        }
        echo "<br>Set Cron File.";
        echo "<li>Set Cron for Later Ride Booking File Name in root folder : cron_schedule_ride_new_parent.php</li>";
        echo "<li>Set Cron for Send Notification File Name in root folder: cron_notification_email_parent.php</li>";
        if ($ePackageType == "shark") {
            echo "<li>Set Cron for Auto Update Currency rate File Name in root folder: cron_update_currency.php</li>";
        }
        echo '</ol>';
    }
}

function getUploadSizeInMB($sSize) {
    $sSuffix = strtoupper(substr($sSize, -1));
    if (!in_array($sSuffix, array('P', 'T', 'G', 'M', 'K'))) {
        return (int) $sSize;
    }
    $iValue = substr($sSize, 0, -1);
    switch ($sSuffix) {
        case 'P':
            $iValue = $iValue * 1024 * 1024 * 1024;
            break;
        case 'T':
            $iValue = $iValue * 1024 * 1024;
            break;
        case 'G':
            $iValue = $iValue * 1024;
            break;
        case 'M':
            $iValue = $iValue;
            break;
        case 'K':
            $iValue = $iValue / 1024;
            break;
    }
    return (int) $iValue;
}
?>
<script>
    var fileCount = '<?= $fileCount; ?>';
    $(document).ready(function () {
        if (fileCount > 0) {
            $("#permissionmsg").show();
        } else {
            $("#permissionmsg").hide();
        }
    });
</script>
