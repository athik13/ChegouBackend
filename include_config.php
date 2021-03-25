<?php
/*ini_set('display_errors', 1);
  ini_set('display_startup_errors', 1);
  error_reporting(E_ALL);*/

include_once("assets/libraries/configuration_variables.php");
include_once("assets/libraries/system_global_functions.php");

$DOCUMENT_ROOT = $_SERVER['DOCUMENT_ROOT'];
define('TPATH_CLASS', $DOCUMENT_ROOT . $tconfig["tsite_folder"] . 'assets/libraries/');

$IS_CONTINUE_DELETE_PROCESS = empty($IS_INHOUSE_DOMAINS) || $IS_INHOUSE_DOMAINS == false ? true : false;

include_once("assets/libraries/db_info.php");
include_once("include_config_inc.php");
include_once ('assets/libraries/site_variables.php');
include_once("assets/libraries/modules_availibility.php");

$IS_RIDE_MODULE_AVAIL = isRideModuleAvailable();
$IS_DELIVERY_MODULE_AVAIL = isDeliveryModuleAvailable();
$IS_UFX_MODULE_AVAIL= isUberXModuleAvailable();
$IS_DELIVERALL_MODULE_AVAIL = isDeliverAllModuleAvailable();
$IS_FLY_MODULE_AVAIL = checkFlyStationsModule();

$isUfxAvailable = $generalobj->CheckUfxServiceAvailable(); // Added By HJ On 04-06-2020 For Optimized Query
$deleteAppFilesArr = array();
$addOnsDataArr = $obj->MySQLSelect("SELECT * FROM setup_info LIMIT 0,1");
$addOnsDataArr_orig = $addOnsDataArr;
$addOnData =$addOnsJSONObj= json_decode($addOnsDataArr[0]['lAddOnConfiguration'], true);
$eCubeX = $eCubejekX =$eRideX=$eDeliverallX= "No";
if (isset($addOnsDataArr[0]['eCubeX']) && $addOnsDataArr[0]['eCubeX'] != "") {
    $eCubeX = $addOnsDataArr[0]['eCubeX'];
}
if (isset($addOnsDataArr[0]['eCubejekX']) && $addOnsDataArr[0]['eCubejekX'] != "") {
    $eCubejekX = $addOnsDataArr[0]['eCubejekX'];
}
if (isset($addOnsDataArr[0]['eRideX']) && $addOnsDataArr[0]['eRideX'] != "") {
    $eRideX = $addOnsDataArr[0]['eRideX'];
}
if (isset($addOnsDataArr[0]['eDeliverallX']) && $addOnsDataArr[0]['eDeliverallX'] != "") {
    $eDeliverallX = $addOnsDataArr[0]['eDeliverallX'];
}
//echo "<pre>";print_r($addOnsJSONObj);die;
$Deliverall = $Fly = $UberX =$Delivery=$Ride= "";
if (strtoupper($eCubejekX) == "YES" || strtoupper($eCubeX) == "YES" || strtoupper($eDeliverallX) == "YES") {
    foreach ($addOnData as $addOnKey => $addOnVal) {
        $$addOnKey = $addOnVal;
    }
}
//echo "<pre>";print_r($Deliverall);die;
//$IS_UFX_SERVICE_AVAIL = $generalobj->CheckUfxServiceAvailable(); // Commented By HJ On 04-06-2020 For Optimized Query Below Line
$IS_UFX_SERVICE_AVAIL = $isUfxAvailable; // Added By HJ On 04-06-2020 For Optimized Query


################################# Check Files Of Android Applications ##########################################

################################ Module Delete ############################################
if($IS_RIDE_MODULE_AVAIL == false){
    addRentalFeatureToDeliteList();
    addPoolFeatureToDeliteList();
    addBusinessProfileFeatureToDeliteList();
    addBookForElseToDeliteList();
    addEndOfDayTripToDeliteList();
    addMultiStopOverPointsToDeliteList();
}
if($IS_FLY_MODULE_AVAIL == fasle){
    addFlyFeatureToDeliteList();
}
if($IS_DELIVERY_MODULE_AVAIL == false){
    addMultiDeliveryToDeliteList();
    addSingleDeliveryToDeliteList();
    
    if($APP_TYPE == "Ride-Delivery-UberX"){
        /** Custom changes for common delivery type section files to avoid deleting Open cat type file * */
        $COMMON_DELIVERY_TYPE_SECTION_FILES_ARR = array();
        $COMMON_DELIVERY_TYPE_SECTION_FILES_ARR_tmp = explode(",", $_REQUEST['COMMON_DELIVERY_TYPE_SECTION_FILES']);
        $_REQUEST['COMMON_DELIVERY_TYPE_SECTION'] = "No";
        for ($i = 0; $i < count($COMMON_DELIVERY_TYPE_SECTION_FILES_ARR_tmp); $i++) {
            $item_tmp_file = $COMMON_DELIVERY_TYPE_SECTION_FILES_ARR_tmp[$i];

            if ($item_tmp_file != "" && $item_tmp_file != "GeneralFiles/OpenCatType.swift" && $item_tmp_file != "com.general.files.OpenCatType" && endsWithSGF($item_tmp_file,"CommonDeliveryTypeSelectionActivity") && endsWithSGF($item_tmp_file,"activity_multi_type_selection")) {
                $COMMON_DELIVERY_TYPE_SECTION_FILES_ARR[] = $item_tmp_file;
                $_REQUEST['COMMON_DELIVERY_TYPE_SECTION'] = "Yes";
            }
        }
        $_REQUEST['COMMON_DELIVERY_TYPE_SECTION_FILES'] = implode(",", $COMMON_DELIVERY_TYPE_SECTION_FILES_ARR);
        /** Custom changes for common delivery type section files to avoid deleting Open cat type file * */
    }
    
    addCommonDeliveryTypesToDeliteList();
}

if (strtoupper($IS_UFX_SERVICE_AVAIL) != "YES" || $IS_UFX_MODULE_AVAIL == false) {
    $IS_UFX_SERVICE_AVAIL = "No";
    addUberXServicesToDeliteList();
}

if ((!empty($Deliverall) && strtoupper($Deliverall) == "NO") || $IS_DELIVERALL_MODULE_AVAIL == false) {
    addDeliverAllToDeliteList();
}

if($IS_DELIVERY_MODULE_AVAIL == false && $IS_UFX_MODULE_AVAIL == false){
    addOnGoingJobsToDeliteList();
}

if($IS_RIDE_MODULE_AVAIL == false && $IS_DELIVERY_MODULE_AVAIL == false && $IS_UFX_MODULE_AVAIL == false){
    addRideSectionToDeliteList();
    addWayBillToDeliteList();
    addRDUToDeliteList();
    addFavDriverToDeliteList();
    addDriverSubscriptionToDeliteList();
}
################################ Module Delete ############################################

function addWayBillToDeliteList() {
    global $_REQUEST, $deleteAppFilesArr;
    if (!empty($_REQUEST['WAYBILL_MODULE']) && ($_REQUEST['WAYBILL_MODULE'] == "Yes" || $_REQUEST['WAYBILL_MODULE'] == "YES")) {
        $deleteAppFilesArr['WayBill Feature'] = $_REQUEST['WAYBILL_MODULE_FILES'];
    }
}

function addDeliverAllToDeliteList() {
    global $_REQUEST, $deleteAppFilesArr;
    if (!empty($_REQUEST['DELIVER_ALL']) && ($_REQUEST['DELIVER_ALL'] == "Yes" || $_REQUEST['DELIVER_ALL'] == "YES")) {
        $deleteAppFilesArr['DeliverAll Feature (Food/Grocery/DeliverAll etc.)'] = $_REQUEST['DELIVER_ALL_FILES'];
    }
}

function addMultiDeliveryToDeliteList() {
    global $_REQUEST, $deleteAppFilesArr;

    if (!empty($_REQUEST['MULTI_DELIVERY']) && ($_REQUEST['MULTI_DELIVERY'] == "Yes" || $_REQUEST['MULTI_DELIVERY'] == "YES")) {
        $deleteAppFilesArr['Multi Delivery Feature'] = $_REQUEST['MULTI_DELIVERY_FILES'];
    }
}

function addSingleDeliveryToDeliteList() {
    global $_REQUEST, $deleteAppFilesArr;

    if (!empty($_REQUEST['DELIVERY_MODULE']) && ($_REQUEST['DELIVERY_MODULE'] == "Yes" || $_REQUEST['DELIVERY_MODULE'] == "YES")) {
        $deleteAppFilesArr['Single Delivery Feature'] = $_REQUEST['DELIVERY_MODULE_FILES'];
    }
}

function addUberXServicesToDeliteList() {
    global $_REQUEST, $deleteAppFilesArr;

    if (!empty($_REQUEST['UBERX_SERVICE']) && ($_REQUEST['UBERX_SERVICE'] == "Yes" || $_REQUEST['UBERX_SERVICE'] == "YES")) {
        $deleteAppFilesArr['UberX (Other Services like - Carwash etc) Feature'] = $_REQUEST['UBERX_FILES'];
    }
}

function addOnGoingJobsToDeliteList() {
    global $_REQUEST, $deleteAppFilesArr;

    if (!empty($_REQUEST['ON_GOING_JOB_SECTION']) && ($_REQUEST['ON_GOING_JOB_SECTION'] == "Yes" || $_REQUEST['ON_GOING_JOB_SECTION'] == "YES")) {
        $deleteAppFilesArr['OnGoing Job Section (UberX/Multi)'] = $_REQUEST['ON_GOING_JOB_SECTION_FILES'];
    }
}

function addCommonDeliveryTypesToDeliteList() {
    global $_REQUEST, $deleteAppFilesArr;

    if (!empty($_REQUEST['COMMON_DELIVERY_TYPE_SECTION']) && ($_REQUEST['COMMON_DELIVERY_TYPE_SECTION'] == "Yes" || $_REQUEST['COMMON_DELIVERY_TYPE_SECTION'] == "YES")) {
        $deleteAppFilesArr['Common Delivery Type Section (For Ride-Delivery/Delivery/MultiDelivery/Cubejek)'] = $_REQUEST['COMMON_DELIVERY_TYPE_SECTION_FILES'];
    }
}

function addRentalFeatureToDeliteList() {
    global $_REQUEST, $deleteAppFilesArr;

    if (!empty($_REQUEST['RENTAL_FEATURE']) && ($_REQUEST['RENTAL_FEATURE'] == "Yes" || $_REQUEST['RENTAL_FEATURE'] == "YES")) {
        $deleteAppFilesArr['Rental Feature'] = $_REQUEST['RENTAL_SERVICE_FILES'];
    }
}

function addRideSectionToDeliteList() {
    global $_REQUEST, $deleteAppFilesArr;

    if (!empty($_REQUEST['RIDE_SECTION']) && ($_REQUEST['RIDE_SECTION'] == "Yes" || $_REQUEST['RIDE_SECTION'] == "YES")) {
        $deleteAppFilesArr['Ride Section'] = $_REQUEST['RIDE_SECTION_FILES'];
    }
}

function addPoolFeatureToDeliteList() {
    global $_REQUEST, $deleteAppFilesArr;

    if (!empty($_REQUEST['POOL_MODULE']) && ($_REQUEST['POOL_MODULE'] == "Yes" || $_REQUEST['POOL_MODULE'] == "YES")) {
        $deleteAppFilesArr['Pool Feature'] = $_REQUEST['POOL_MODULE_FILES'];
    }
}
function addFlyFeatureToDeliteList() {
    global $_REQUEST, $deleteAppFilesArr;

    if (!empty($_REQUEST['FLY_MODULE']) && ($_REQUEST['FLY_MODULE'] == "Yes" || $_REQUEST['FLY_MODULE'] == "YES")) {
        $deleteAppFilesArr['Fly Feature'] = $_REQUEST['MODULE_FILES'];
    }
}

function addBusinessProfileFeatureToDeliteList() {
    global $_REQUEST, $deleteAppFilesArr;

    if (!empty($_REQUEST['BUSINESS_PROFILE_FEATURE']) && ($_REQUEST['BUSINESS_PROFILE_FEATURE'] == "Yes" || $_REQUEST['BUSINESS_PROFILE_FEATURE'] == "YES")) {
        $deleteAppFilesArr['Business Profile Feature'] = $_REQUEST['BUSINESS_PROFILE_FILES'];
    }
}

function addRDUToDeliteList() {
    global $_REQUEST, $deleteAppFilesArr;

    if (!empty($_REQUEST['RDU_SECTION']) && ($_REQUEST['RDU_SECTION'] == "Yes" || $_REQUEST['RDU_SECTION'] == "YES")) {
        $deleteAppFilesArr['RDU Section Files'] = $_REQUEST['RDU_SECTION_FILES'];
    }
}

function addVOIPToDeliteList() {
    global $_REQUEST, $deleteAppFilesArr;

    if (!empty($_REQUEST['VOIP_SERVICE']) && ($_REQUEST['VOIP_SERVICE'] == "Yes" || $_REQUEST['VOIP_SERVICE'] == "YES")) {
        $deleteAppFilesArr['VOIP Feature'] = $_REQUEST['VOIP_SERVICE_FILES'];
    }
}

function addFavDriverToDeliteList() {
    global $_REQUEST, $deleteAppFilesArr;

    if (!empty($_REQUEST['FAV_DRIVER_SECTION']) && ($_REQUEST['FAV_DRIVER_SECTION'] == "Yes" || $_REQUEST['FAV_DRIVER_SECTION'] == "YES")) {
        $deleteAppFilesArr['Favourite Driver Feature'] = $_REQUEST['FAV_DRIVER_SECTION_FILES'];
    }
}

function addBookForElseToDeliteList() {
    global $_REQUEST, $deleteAppFilesArr;

    if (!empty($_REQUEST['BOOK_FOR_ELSE_SECTION']) && ($_REQUEST['BOOK_FOR_ELSE_SECTION'] == "Yes" || $_REQUEST['BOOK_FOR_ELSE_SECTION'] == "YES")) {
        $deleteAppFilesArr['Book for someone else Feature'] = $_REQUEST['BOOK_FOR_ELSE_SECTION_FILES'];
    }
}

function addEndOfDayTripToDeliteList() {
    global $_REQUEST, $deleteAppFilesArr;

    if (!empty($_REQUEST['END_OF_DAY_TRIP_SECTION']) && ($_REQUEST['END_OF_DAY_TRIP_SECTION'] == "Yes" || $_REQUEST['END_OF_DAY_TRIP_SECTION'] == "YES")) {
        $deleteAppFilesArr['EndOfDayTrip Feature'] = $_REQUEST['END_OF_DAY_TRIP_SECTION_FILES'];
    }
}

function addMultiStopOverPointsToDeliteList() {
    global $_REQUEST, $deleteAppFilesArr;

    if (!empty($_REQUEST['STOP_OVER_POINT_SECTION']) && ($_REQUEST['STOP_OVER_POINT_SECTION'] == "Yes" || $_REQUEST['STOP_OVER_POINT_SECTION'] == "YES")) {
        $deleteAppFilesArr['Multi StopOverPoint Feature'] = $_REQUEST['STOP_OVER_POINT_SECTION_FILES'];
    }
}

function addDriverSubscriptionToDeliteList() {
    global $_REQUEST, $deleteAppFilesArr;

    if (!empty($_REQUEST['DRIVER_SUBSCRIPTION_SECTION']) && ($_REQUEST['DRIVER_SUBSCRIPTION_SECTION'] == "Yes" || $_REQUEST['DRIVER_SUBSCRIPTION_SECTION'] == "YES")) {
        $deleteAppFilesArr['Driver Subscription Feature'] = $_REQUEST['DRIVER_SUBSCRIPTION_SECTION_FILES'];
    }
}

function addDonationToDeliteList() {
    global $_REQUEST, $deleteAppFilesArr;

    if (!empty($_REQUEST['DONATION_SECTION']) && ($_REQUEST['DONATION_SECTION'] == "Yes" || $_REQUEST['DONATION_SECTION'] == "YES")) {
        $deleteAppFilesArr['Donation Feature'] = $_REQUEST['DONATION_SECTION_FILES'];
    }
}

function addGoJekGoPayToDeliteList() {
    global $_REQUEST, $deleteAppFilesArr;

    if (!empty($_REQUEST['GO_PAY_SECTION']) && ($_REQUEST['GO_PAY_SECTION'] == "Yes" || $_REQUEST['GO_PAY_SECTION'] == "YES")) {
        $deleteAppFilesArr['Wallet to Wallet money Feature'] = $_REQUEST['GO_PAY_SECTION_FILES'];
    }
}

//Added By HJ On 01-10-2019 For Removed File of Thermal Print Start
function addThermalPrintToDeliteList() {
    global $_REQUEST, $deleteAppFilesArr;

    if (!empty($_REQUEST['THERMAL_PRINT_MODULE']) && ($_REQUEST['THERMAL_PRINT_MODULE'] == "Yes" || $_REQUEST['THERMAL_PRINT_MODULE'] == "YES")) {
        $deleteAppFilesArr['Thermal Print Feature'] = $_REQUEST['THERMAL_PRINT_MODULE_FILES'];
    }
}

if (strtoupper(PACKAGE_TYPE) == "SHARK") {
    unset($_REQUEST['THERMAL_PRINT_MODULE']);
    unset($_REQUEST['THERMAL_PRINT_MODULE_FILES']);
} else {
    addThermalPrintToDeliteList();
}

//Added By HJ On 01-10-2019 For Removed File of Thermal Print End
############################################################ Dynamic Features ############################################################

if (empty($addOnsJSONObj['DONATION']) || strtoupper($addOnsJSONObj['DONATION']) != "YES") {
    addDonationToDeliteList();
} else {
    unset($_REQUEST['DONATION_SECTION']);
    unset($_REQUEST['DONATION_SECTION_FILES']);
}

if (empty($addOnsJSONObj['DRIVER_DESTINATION']) || strtoupper($addOnsJSONObj['DRIVER_DESTINATION']) != "YES") {
    addEndOfDayTripToDeliteList();
} else {
    unset($_REQUEST['END_OF_DAY_TRIP_SECTION']);
    unset($_REQUEST['END_OF_DAY_TRIP_SECTION_FILES']);
}

if (empty($addOnsJSONObj['FAVOURITE_DRIVER']) || strtoupper($addOnsJSONObj['FAVOURITE_DRIVER']) != "YES") {
    addFavDriverToDeliteList();
} else {
    unset($_REQUEST['FAV_DRIVER_SECTION']);
    unset($_REQUEST['FAV_DRIVER_SECTION_FILES']);
}

if (empty($addOnsJSONObj['DRIVER_SUBSCRIPTION']) || strtoupper($addOnsJSONObj['DRIVER_SUBSCRIPTION']) != "YES") {
    addDriverSubscriptionToDeliteList();
} else {
    unset($_REQUEST['DRIVER_SUBSCRIPTION_SECTION']);
    unset($_REQUEST['DRIVER_SUBSCRIPTION_SECTION_FILES']);
}

if (empty($addOnsJSONObj['MULTI_STOPOVER_POINTS']) || strtoupper($addOnsJSONObj['MULTI_STOPOVER_POINTS']) != "YES") {
    addMultiStopOverPointsToDeliteList();
} else {
    unset($_REQUEST['STOP_OVER_POINT_SECTION']);
    unset($_REQUEST['STOP_OVER_POINT_SECTION_FILES']);
}

if (empty($addOnsJSONObj['GOJEK_GOPAY']) || strtoupper($addOnsJSONObj['GOJEK_GOPAY']) != "YES") {
    addGoJekGoPayToDeliteList();
} else {
    unset($_REQUEST['GO_PAY_SECTION']);
    unset($_REQUEST['GO_PAY_SECTION_FILES']);
}

############################################################ Dynamic Features ############################################################

if (strtoupper(PACKAGE_TYPE) == "STANDARD") {
    addVOIPToDeliteList();
} else {
    unset($_REQUEST['VOIP_SERVICE']);
    unset($_REQUEST['VOIP_SERVICE_FILES']);
}


if (strtoupper(PACKAGE_TYPE) == "STANDARD" || strtoupper(PACKAGE_TYPE) == "ENTERPRISE") {
    /*     * ************* Remove Shark Features ************** */

    if (!empty($_REQUEST['ADVERTISEMENT_MODULE']) && ($_REQUEST['ADVERTISEMENT_MODULE'] == "Yes" || $_REQUEST['ADVERTISEMENT_MODULE'] == "YES")) {
        $deleteAppFilesArr['Advertisement Feature'] = $_REQUEST['ADVERTISEMENT_MODULE_FILES'];
    }

    if (!empty($_REQUEST['LINKEDIN_MODULE']) && ($_REQUEST['LINKEDIN_MODULE'] == "Yes" || $_REQUEST['LINKEDIN_MODULE'] == "YES")) {
        $deleteAppFilesArr['LinkedIn Feature'] = $_REQUEST['LINKEDIN_MODULE_FILES'];
    }

    addPoolFeatureToDeliteList();

    if (!empty($_REQUEST['CARD_IO']) && ($_REQUEST['CARD_IO'] == "Yes" || $_REQUEST['CARD_IO'] == "YES")) {
        $deleteAppFilesArr['CardIO Feature'] = $_REQUEST['CARD_IO_FILES'];
    }

    if (!empty($_REQUEST['LIVE_CHAT']) && ($_REQUEST['LIVE_CHAT'] == "Yes" || $_REQUEST['LIVE_CHAT'] == "YES")) {
        $deleteAppFilesArr['LiveChat Feature'] = $_REQUEST['LIVE_CHAT_FILES'];
    }

    addBusinessProfileFeatureToDeliteList();

    if (!empty($_REQUEST['NEWS_SECTION']) && ($_REQUEST['NEWS_SECTION'] == "Yes" || $_REQUEST['NEWS_SECTION'] == "YES")) {
        $deleteAppFilesArr['News Feature'] = $_REQUEST['NEWS_SERVICE_FILES'];
    }

    addFavDriverToDeliteList();
    addBookForElseToDeliteList();
    addEndOfDayTripToDeliteList();
    addMultiStopOverPointsToDeliteList();
} else {
// Unset common features

    unset($_REQUEST['ADVERTISEMENT_MODULE']);
    unset($_REQUEST['ADVERTISEMENT_MODULE_FILES']);

    unset($_REQUEST['LINKEDIN_MODULE']);
    unset($_REQUEST['LINKEDIN_MODULE_FILES']);

    unset($_REQUEST['CARD_IO']);
    unset($_REQUEST['CARD_IO_FILES']);

    unset($_REQUEST['LIVE_CHAT']);
    unset($_REQUEST['LIVE_CHAT_FILES']);

    unset($_REQUEST['NEWS_SECTION']);
    unset($_REQUEST['NEWS_SERVICE_FILES']);
}

if (strtoupper(ONLYDELIVERALL) == "YES") {
    addMultiDeliveryToDeliteList();
    addSingleDeliveryToDeliteList();
    addUberXServicesToDeliteList();
    addOnGoingJobsToDeliteList();
    addCommonDeliveryTypesToDeliteList();
    addRentalFeatureToDeliteList();
    addPoolFeatureToDeliteList();
    addBusinessProfileFeatureToDeliteList();
    addRideSectionToDeliteList();
    addRDUToDeliteList();

    addFavDriverToDeliteList();
    addBookForElseToDeliteList();
    addEndOfDayTripToDeliteList();
    addMultiStopOverPointsToDeliteList();
    addDriverSubscriptionToDeliteList();

    if (strtoupper(PACKAGE_TYPE) == "STANDARD") {
        addWayBillToDeliteList();
    } else {
        unset($_REQUEST['WAYBILL_MODULE']);
        unset($_REQUEST['WAYBILL_MODULE_FILES']);
    }
    if ((!empty($Deliverall) && strtoupper($Deliverall) == "NO") == false) {
        unset($_REQUEST['DELIVER_ALL']);
        unset($_REQUEST['DELIVER_ALL_FILES']);
    }
} else {
    unset($_REQUEST['RDU_SECTION']);
    unset($_REQUEST['RDU_SECTION_FILES']);

    if ($APP_TYPE == "Ride") {
        addDeliverAllToDeliteList();
        addMultiDeliveryToDeliteList();
        addSingleDeliveryToDeliteList();
        addUberXServicesToDeliteList();
        addOnGoingJobsToDeliteList();
        addCommonDeliveryTypesToDeliteList();
        addEndOfDayTripToDeliteList();
        addMultiStopOverPointsToDeliteList();
        addFavDriverToDeliteList();
        addDriverSubscriptionToDeliteList();

        if (strtoupper(PACKAGE_TYPE) == "STANDARD") {
            addRentalFeatureToDeliteList();
            addWayBillToDeliteList();
        } else {
            unset($_REQUEST['RENTAL_FEATURE']);
            unset($_REQUEST['RENTAL_SERVICE_FILES']);

            unset($_REQUEST['WAYBILL_MODULE']);
            unset($_REQUEST['WAYBILL_MODULE_FILES']);
        }

        if (strtoupper(PACKAGE_TYPE) == "SHARK") {
            unset($_REQUEST['POOL_MODULE']);
            unset($_REQUEST['POOL_MODULE_FILES']);

            unset($_REQUEST['BUSINESS_PROFILE_FEATURE']);
            unset($_REQUEST['BUSINESS_PROFILE_FILES']);

            unset($_REQUEST['BOOK_FOR_ELSE_SECTION']);
            unset($_REQUEST['BOOK_FOR_ELSE_SECTION_FILES']);
        }
        unset($_REQUEST['RIDE_SECTION']);
        unset($_REQUEST['RIDE_SECTION_FILES']);
    }

    if ($APP_TYPE == "Delivery") {
        addDeliverAllToDeliteList();
        addUberXServicesToDeliteList();
        addRentalFeatureToDeliteList();
        addPoolFeatureToDeliteList();
        addBusinessProfileFeatureToDeliteList();
        addBookForElseToDeliteList();
        addEndOfDayTripToDeliteList();
        addMultiStopOverPointsToDeliteList();
        addFavDriverToDeliteList();
        addDriverSubscriptionToDeliteList();

        if (strtoupper(PACKAGE_TYPE) == "STANDARD") {
            addWayBillToDeliteList();
            addMultiDeliveryToDeliteList();
            addCommonDeliveryTypesToDeliteList();
            addOnGoingJobsToDeliteList();
        } else {

            unset($_REQUEST['WAYBILL_MODULE']);
            unset($_REQUEST['WAYBILL_MODULE_FILES']);

            unset($_REQUEST['MULTI_DELIVERY']);
            unset($_REQUEST['MULTI_DELIVERY_FILES']);

            unset($_REQUEST['COMMON_DELIVERY_TYPE_SECTION']);
            unset($_REQUEST['COMMON_DELIVERY_TYPE_SECTION_FILES']);

            unset($_REQUEST['ON_GOING_JOB_SECTION']);
            unset($_REQUEST['ON_GOING_JOB_SECTION_FILES']);
        }

        unset($_REQUEST['DELIVERY_MODULE']);
        unset($_REQUEST['DELIVERY_MODULE_FILES']);

        unset($_REQUEST['RIDE_SECTION']);
        unset($_REQUEST['RIDE_SECTION_FILES']);
    }

    if ($APP_TYPE == "UberX") {
        addDeliverAllToDeliteList();
        addRentalFeatureToDeliteList();
        addPoolFeatureToDeliteList();
        addBusinessProfileFeatureToDeliteList();
        addSingleDeliveryToDeliteList();
        addMultiDeliveryToDeliteList();
        addRideSectionToDeliteList();
        addCommonDeliveryTypesToDeliteList();
        addBookForElseToDeliteList();
        addEndOfDayTripToDeliteList();
        addMultiStopOverPointsToDeliteList();
        addFavDriverToDeliteList();
        addDriverSubscriptionToDeliteList();

        if (strtoupper(PACKAGE_TYPE) == "STANDARD") {
            addWayBillToDeliteList();
        } else {
            unset($_REQUEST['WAYBILL_MODULE']);
            unset($_REQUEST['WAYBILL_MODULE_FILES']);
        }

        if (strtoupper($IS_UFX_SERVICE_AVAIL) == "YES") {
            unset($_REQUEST['UBERX_SERVICE']);
            unset($_REQUEST['UBERX_FILES']);
        }

        unset($_REQUEST['ON_GOING_JOB_SECTION']);
        unset($_REQUEST['ON_GOING_JOB_SECTION_FILES']);
    }
    if ($APP_TYPE == "Ride-Delivery") {
        addDeliverAllToDeliteList();
        addUberXServicesToDeliteList();
        addEndOfDayTripToDeliteList();
        addMultiStopOverPointsToDeliteList();
        addFavDriverToDeliteList();
        addDriverSubscriptionToDeliteList();

        if (strtoupper(PACKAGE_TYPE) == "STANDARD") {
            addRentalFeatureToDeliteList();
            addWayBillToDeliteList();

            addMultiDeliveryToDeliteList();
            addOnGoingJobsToDeliteList();
        } else {
            unset($_REQUEST['WAYBILL_MODULE']);
            unset($_REQUEST['WAYBILL_MODULE_FILES']);

            unset($_REQUEST['RENTAL_FEATURE']);
            unset($_REQUEST['RENTAL_SERVICE_FILES']);

            unset($_REQUEST['MULTI_DELIVERY']);
            unset($_REQUEST['MULTI_DELIVERY_FILES']);

            unset($_REQUEST['ON_GOING_JOB_SECTION']);
            unset($_REQUEST['ON_GOING_JOB_SECTION_FILES']);
        }

        if (strtoupper(PACKAGE_TYPE) == "SHARK") {
            unset($_REQUEST['POOL_MODULE']);
            unset($_REQUEST['POOL_MODULE_FILES']);

            unset($_REQUEST['BUSINESS_PROFILE_FEATURE']);
            unset($_REQUEST['BUSINESS_PROFILE_FILES']);

            //unset($_REQUEST['BOOK_FOR_ELSE_SECTION']);
            //unset($_REQUEST['BOOK_FOR_ELSE_SECTION_FILES']);
        } else {
            addPoolFeatureToDeliteList();
            addBusinessProfileFeatureToDeliteList();

            addBookForElseToDeliteList();
            addEndOfDayTripToDeliteList();
            addMultiStopOverPointsToDeliteList();
        }

        unset($_REQUEST['COMMON_DELIVERY_TYPE_SECTION']);
        unset($_REQUEST['COMMON_DELIVERY_TYPE_SECTION_FILES']);

        unset($_REQUEST['DELIVERY_MODULE']);
        unset($_REQUEST['DELIVERY_MODULE_FILES']);

        unset($_REQUEST['RIDE_SECTION']);
        unset($_REQUEST['RIDE_SECTION_FILES']);
    }

    if ($APP_TYPE == "Ride-Delivery-UberX") {
        // addEndOfDayTripToDeliteList();
        // addMultiStopOverPointsToDeliteList();

        if (strtoupper(PACKAGE_TYPE) == "STANDARD") {
            addRentalFeatureToDeliteList();
            addWayBillToDeliteList();
        } else {
            if($IS_RIDE_MODULE_AVAIL == false && $IS_DELIVERY_MODULE_AVAIL == false && $IS_UFX_MODULE_AVAIL == false){
                unset($_REQUEST['WAYBILL_MODULE']);
                unset($_REQUEST['WAYBILL_MODULE_FILES']);
            }
            
            if($IS_RIDE_MODULE_AVAIL){
                unset($_REQUEST['RENTAL_FEATURE']);
                unset($_REQUEST['RENTAL_SERVICE_FILES']);   
            }
        }
        if (strtoupper(PACKAGE_TYPE) != "SHARK") {
            addDeliverAllToDeliteList();
            addPoolFeatureToDeliteList();
            addBusinessProfileFeatureToDeliteList();

            addMultiDeliveryToDeliteList();
            addBookForElseToDeliteList();
            addEndOfDayTripToDeliteList();
            addMultiStopOverPointsToDeliteList();

            addFavDriverToDeliteList();
            addDriverSubscriptionToDeliteList();
            addDonationToDeliteList();

            /** Custom changes for common delivery type section files to avoid deleting Open cat type file * */
            $COMMON_DELIVERY_TYPE_SECTION_FILES_ARR = array();
            $COMMON_DELIVERY_TYPE_SECTION_FILES_ARR_tmp = explode(",", $_REQUEST['COMMON_DELIVERY_TYPE_SECTION_FILES']);
            $_REQUEST['COMMON_DELIVERY_TYPE_SECTION'] = "No";
            for ($i = 0; $i < count($COMMON_DELIVERY_TYPE_SECTION_FILES_ARR_tmp); $i++) {
                $item_tmp_file = $COMMON_DELIVERY_TYPE_SECTION_FILES_ARR_tmp[$i];

                if ($item_tmp_file != "" && $item_tmp_file != "GeneralFiles/OpenCatType.swift" && $item_tmp_file != "com.general.files.OpenCatType") {
                    $COMMON_DELIVERY_TYPE_SECTION_FILES_ARR[] = $item_tmp_file;
                    $_REQUEST['COMMON_DELIVERY_TYPE_SECTION'] = "Yes";
                }
            }
            $_REQUEST['COMMON_DELIVERY_TYPE_SECTION_FILES'] = implode(",", $COMMON_DELIVERY_TYPE_SECTION_FILES_ARR);
            /** Custom changes for common delivery type section files to avoid deleting Open cat type file * */
            addCommonDeliveryTypesToDeliteList();
        } else {
            if ((!empty($Deliverall) && strtoupper($Deliverall) == "NO") == false) {
                unset($_REQUEST['DELIVER_ALL']);
                unset($_REQUEST['DELIVER_ALL_FILES']);
            }
            
            if($IS_RIDE_MODULE_AVAIL){
                    
                unset($_REQUEST['POOL_MODULE']);
                unset($_REQUEST['POOL_MODULE_FILES']);
    
                unset($_REQUEST['BUSINESS_PROFILE_FEATURE']);
                unset($_REQUEST['BUSINESS_PROFILE_FILES']);
                
                unset($_REQUEST['BOOK_FOR_ELSE_SECTION']);
                unset($_REQUEST['BOOK_FOR_ELSE_SECTION_FILES']);

            }
            
            if($IS_DELIVERY_MODULE_AVAIL){
                unset($_REQUEST['MULTI_DELIVERY']);
                unset($_REQUEST['MULTI_DELIVERY_FILES']);
                
                unset($_REQUEST['COMMON_DELIVERY_TYPE_SECTION']);
                unset($_REQUEST['COMMON_DELIVERY_TYPE_SECTION_FILES']);
            }
        }

        if (strtoupper($IS_UFX_SERVICE_AVAIL) == "YES") {
            unset($_REQUEST['UBERX_SERVICE']);
            unset($_REQUEST['UBERX_FILES']);
        }

        if($IS_DELIVERY_MODULE_AVAIL || $IS_UFX_MODULE_AVAIL){
            unset($_REQUEST['ON_GOING_JOB_SECTION']);
            unset($_REQUEST['ON_GOING_JOB_SECTION_FILES']);
        }

        if($IS_DELIVERY_MODULE_AVAIL){
            unset($_REQUEST['DELIVERY_MODULE']);
            unset($_REQUEST['DELIVERY_MODULE_FILES']);  
        }

        if($IS_RIDE_MODULE_AVAIL == false && $IS_DELIVERY_MODULE_AVAIL == false && $IS_UFX_MODULE_AVAIL == false){
            unset($_REQUEST['RIDE_SECTION']);
            unset($_REQUEST['RIDE_SECTION_FILES']);   
        }
    }
}
//echo "<pre>";print_r($deleteAppFilesArr);die;
if (!empty($deleteAppFilesArr) && $IS_CONTINUE_DELETE_PROCESS == true) {
    ?>
    <!DOCTYPE html>
    <html lang="en">
        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>
        <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
        <script>
            function openSvnModal() {
                $("#appdatamodal").modal('show');
            }
            function confirmPassword() {
                var svnUrl = $("#svnurl").val();
                var svnUsername = $("#svnUsername").val();
                var svnPassword = $("#svnPassword").val();
                if (svnUrl == "") {
                    alert("Please enter SVN URl");
                    return false;
                }
                if (svnUsername == "") {
                    alert("Please enter SVN Username");
                    return false;
                }
                if (svnPassword == "") {
                    alert("Please enter SVN Password");
                    return false;
                }
                var retVal = confirm("Do you want to continue ?");
                if (retVal == true) {
                    $('#appdatamodal').modal('hide');
                    document.getElementById("svnform").submit();
                    return true;
                } else {
                    return false;
                }
            }
        </script>
        <?php
        $svnData = "";
        if (isset($_REQUEST)) {
            $svnData = json_encode($_REQUEST);
            $svnData = urlencode($svnData);
        }
        ?>
        <div id="appdatamodal" class="modal fade">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                        <h4 class="modal-title">SVN Details</h4>
                    </div>
                    <form action='app_configuration_file_action.php' method='post' id="svnform">
                        <input type='hidden' value='<?= $svnData; ?>' name='APP_CONFIG_PARAMS_PACKAGE'>
                        <div class="modal-body">
                            <div class="row">
                                <div class="col-lg-12">
                                    <label>SVN Url :</label>
                                    <input type="text" required="" class="form-control" name="svnurl" placeholder="SVN URL">
                                </div><br><br><br><br>
                                <div class="col-lg-6">
                                    <label>SVN Username :</label>
                                    <input type="text" required="" class="form-control" name="svnUsername" id="svnUsername" placeholder="Username">
                                </div>
                                <div class="col-lg-6">
                                    <label>SVN Password :</label>
                                    <input type="text" required="" class="form-control" id="svnPassword" name="svnPassword" placeholder="Password">
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                            <button type="submit" formtarget="_blank" onclick="return confirmPassword();" class="btn btn-primary">Next >></button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </body>
    </html>
    <?php
    $headerPortionHtml_str_prefix = "<!DOCTYPE html><html><head><style> table {font-family: arial, sans-serif; border-collapse: collapse; width: 100%; padding: 15px;} td, th { border: 1px solid #dddddd; text-align: left; padding: 8px;} tr:nth-child(even) { background-color: #dddddd;} </style> </head> <body style=\"width:100%; padding: 15px;\"> <h2>Remove Files From App's Code<button type='button' onClick='openSvnModal();' class='btn btn-primary' data-toggle='modal' style=\"position: absolute; right: 15px;\" data-target='#myModal'>Delete Files From SVN</button></h2><table> <tr> <th>Feature Name</th> <th>List of files Or Libraries to Delete</th> </tr>";
    foreach ($deleteAppFilesArr as $key => $value) {
        $str_tr = "";
        // echo $key."<BR/>";
        $str_value = "<tr><td>" . $key . "</td><td>" . str_replace(",", "<BR/>", $value) . "</td></tr>";
        $headerPortionHtml_str_prefix = $headerPortionHtml_str_prefix . $str_value;
    }
    $headerPortionHtml_str_postfix = "</table></body></html>";
    $files_delete_str_html = $headerPortionHtml_str_prefix . $headerPortionHtml_str_postfix;
    echo $files_delete_str_html;
    exit;
}

################################# Check Files Of Android Applications ##########################################
?>
