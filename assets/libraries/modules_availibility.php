<?php

//here put my functions only bc others are also included in generalfun some are in dl so i have not change in all places...
//added by SP for fly stations on 13-08-2019 start
function checkFlyStationsModule($admin = '') {
    global $ENABLE_FLY_VEHICLES, $tconfig, $APP_TYPE;
    //Added By HJ On 06-03-2020 For Disable Fly Module As Per App Type Discuss WIth KS Sir Start
    if (strtoupper($APP_TYPE) == "RIDE" || strtoupper($APP_TYPE) == "DELIVERY" || strtoupper($APP_TYPE) == "RIDE-DELIVERY" || strtoupper($APP_TYPE) == "UBERX" || strtoupper(ONLYDELIVERALL) == "YES") {
        return false;
    }
    //Added By HJ On 06-03-2020 For Disable Fly Module As Per App Type Discuss WIth KS Sir End
    $fly_stations_filepath = $tconfig["tpanel_path"] . "include/features/include_fly_stations.php";

    /* $fly_stations_filepath = "include/features/include_fly_stations.php";

      if ($admin == 1) {
      $fly_stations_filepath = "../include/features/include_fly_stations.php";
      } else if ($admin == 2) {
      $fly_stations_filepath = ROOT_PATH . "include/features/include_fly_stations.php";
      } */

    if (empty($ENABLE_FLY_VEHICLES)) {
        $ENABLE_FLY_VEHICLES = get_value('configurations', 'vValue', 'vName', 'ENABLE_FLY_VEHICLES', '', true);
    }

    if (file_exists($fly_stations_filepath) && strtoupper($ENABLE_FLY_VEHICLES) == 'YES') {
        return true;
    }
    return false;
}

//added by SP for fly stations on 13-08-2019 end

function checkGojekGopayModule() {
    global $obj, $APP_TYPE, $PACKAGE_TYPE, $generalSystemConfigDataArr, $tconfig;

    $gojek_gopay_filepath = $tconfig["tpanel_path"] . "include/features/include_gojek_gopay.php";

    if (!empty($generalSystemConfigDataArr['ENABLE_GOPAY'])) {
        $EnableGopay = $generalSystemConfigDataArr['ENABLE_GOPAY'];
    } else {
        $EnableGopay = get_value('configurations_payment', 'vValue', 'vName', 'ENABLE_GOPAY', '', true);
    }

    //if (file_exists($gojek_gopay_filepath) && strtoupper($EnableGopay) == 'YES' && ($PACKAGE_TYPE == "SHARK")) {
    if (file_exists($gojek_gopay_filepath) && strtoupper($EnableGopay) == 'YES') {
        return true;
    }
    return false;
}

/* For Gojek-gopay added by SP end */

/* For DriverSubscription added by SP start */

function checkDriverSubscriptionModule() {
    global $obj, $APP_TYPE, $PACKAGE_TYPE, $generalobj, $generalSystemConfigDataArr, $DRIVER_SUBSCRIPTION_ENABLE, $tconfig;

    $DriverSubscriptionFilepath = $tconfig["tpanel_path"] . "include/features/include_driver_subscription.php";

//    if (!empty($generalSystemConfigDataArr['DRIVER_SUBSCRIPTION_ENABLE'])) {
    //        $DRIVER_SUBSCRIPTION_ENABLE = $generalSystemConfigDataArr['DRIVER_SUBSCRIPTION_ENABLE'];
    //    }
    if (empty($DRIVER_SUBSCRIPTION_ENABLE)) {
        $DRIVER_SUBSCRIPTION_ENABLE = $generalobj->getConfigurations("configurations", "DRIVER_SUBSCRIPTION_ENABLE");
        $DRIVER_SUBSCRIPTION_ENABLE = $DRIVER_SUBSCRIPTION_ENABLE[0]['vValue'];
    }

    if (file_exists($DriverSubscriptionFilepath) && strtoupper($DRIVER_SUBSCRIPTION_ENABLE) == 'YES' && ONLYDELIVERALL != "Yes") {
        return true;
    }
    return false;
}

function isCorporateProfileEnable() {
    global $tab, $ENABLE_CORPORATE_PROFILE;

    if (PACKAGE_TYPE == 'SHARK' && ($tab == "true") && $ENABLE_CORPORATE_PROFILE == 'Yes' && strtoupper(APP_TYPE) != "DELIVERY" && strtoupper(APP_TYPE) != "UBERX" && strtoupper(ONLYDELIVERALL) != "YES") {
        $IS_CORPORATE_PROFILE_ENABLED = strtoupper($ENABLE_CORPORATE_PROFILE) == "YES" ? true : false;
    } else {
        $IS_CORPORATE_PROFILE_ENABLED = false;
    }

    return $IS_CORPORATE_PROFILE_ENABLED;
}

function isInsuranceReportEnable() {
    global $ENABLE_INSURANCE_IDLE_REPORT, $ENABLE_INSURANCE_ACCEPT_REPORT, $ENABLE_INSURANCE_TRIP_REPORT;
    if (PACKAGE_TYPE == 'SHARK' && strtoupper(APP_TYPE) != 'UBERX' && strtoupper(ONLYDELIVERALL) != "YES" && ($ENABLE_INSURANCE_IDLE_REPORT == 'Yes' || $ENABLE_INSURANCE_ACCEPT_REPORT == 'Yes' || $ENABLE_INSURANCE_TRIP_REPORT == 'Yes')) {
        return true;
    } else {
        return false;
    }
}

function manual_booking_extended_version() {
    global $generalobj;
    if ((strtoupper($generalobj->checkXThemOn()) == 'YES') && strtoupper(ENABLE_EXTENDED_VERSION_MANUAL_BOOKING) == 'YES') {
        return true;
    } else {
        return false;
    }
}

function isMongoDBAvailable() {
    global $obj;
    if (strtoupper(ENABLE_MONGO_CONNECTION) == "YES" && $obj->isMongoDBConnected()) {
        return true;
    }
    return false;
}

function isMemcachedAvailable() {
    if (strtoupper(ENABLE_MEMCACHED) == "YES") {
        return true;
    }
    return false;
}

function mapAPIreplacementAvailable() {
    global $tconfig, $addOnsDataArr_orig, $obj;
    $isMapiReplacementAvail = false;
    if (isMongoDBAvailable() && (file_exists($tconfig["tpanel_path"] . "admin/map_api_setting.php") && file_exists($tconfig["tpanel_path"] . "admin/map_api_mongo_auth_places_action.php") && file_exists($tconfig["tpanel_path"] . "admin/map_api_mongo_auth_places.php"))) {
        if (empty($addOnsDataArr_orig)) {
            $addOnsDataArr_orig = $obj->MySQLSelect("SELECT lAddOnConfiguration,eCubejekX,eCubeX FROM setup_info LIMIT 0,1");
        }
        $addOnsJSONObj = json_decode($addOnsDataArr_orig[0]['lAddOnConfiguration'], true);
        $GOOGLE_PLAN_VAL = intVal($addOnsJSONObj['GOOGLE_PLAN']);
        $GOOGLE_PLAN = empty($addOnsJSONObj['GOOGLE_PLAN']) ? "No" : (($GOOGLE_PLAN_VAL == 1 || $GOOGLE_PLAN_VAL == 2 || $GOOGLE_PLAN_VAL == 3) ? "Yes" : "No");
        if ($GOOGLE_PLAN == "Yes") {
            $isMapiReplacementAvail = true;
        }
    }
    return $isMapiReplacementAvail;
}

//Added By HJ On 08-02-2020 For Config Expired Document Feature As Per Discuss With KS Sir Start
function isExpiredDocumentEnable() {
    return strtoupper(ENABLE_EXPIRE_DOCUMENT) == "YES" ? true : false;
}

//Added By HJ On 08-02-2020 For Config Expired Document Feature As Per Discuss With KS Sir End

/* added by PM for Auto credit wallet driver on 25-01-2020 start */
function checkAutoCreditDriverModule() {
    global $obj, $generalSystemConfigDataArr, $tconfig;
    $auto_credit_driver_filepath = $tconfig["tpanel_path"] . "include/features/include_auto_credit_driver.php";
    if (!empty($generalSystemConfigDataArr['CREDIT_TO_WALLET_ENABLE'])) {
        $EnableCreditWalletDriver = $generalSystemConfigDataArr['CREDIT_TO_WALLET_ENABLE'];
    } else {
        $EnableCreditWalletDriver = get_value('configurations_payment', 'vValue', 'vName', 'CREDIT_TO_WALLET_ENABLE', '', true);
    }

    if (file_exists($auto_credit_driver_filepath) && strtoupper($EnableCreditWalletDriver) == 'YES') {
        return true;
    }
    return false;
}

/* added by PM for Auto credit wallet driver on 25-01-2020 end */

//Added By HJ On 18-03-2020 For Configure Hotel Panel Configuration Start
function isHotelPanelEnable() {
    if (strtoupper(APP_TYPE) == "UBERX" || strtoupper(APP_TYPE) == "DELIVERY" || strtoupper(ONLYDELIVERALL) == "YES") {
        return false;
    }
    return strtoupper(ENABLEHOTELPANEL) == "YES" ? true : false;
}

//Added By HJ On 18-03-2020 For Configure Hotel Panel Configuration End
//Added By HJ On 18-03-2020 For Configure Kiosk Panel Configuration Start
function isKioskPanelEnable() {
    if (strtoupper(APP_TYPE) == "UBERX" || strtoupper(APP_TYPE) == "DELIVERY" || strtoupper(ONLYDELIVERALL) == "YES") {
        return false;
    }
    return strtoupper(ENABLEKIOSKPANEL) == "YES" ? true : false;
}

//Added By HJ On 18-03-2020 For Configure Kiosk Panel Configuration End

function isStoreCategoriesEnable() {
    if (!empty(ENABLE_STORE_CATEGORIES_MODULE) && strtoupper(ENABLE_STORE_CATEGORIES_MODULE) == 'YES') {
        return true;
    } else {
        return false;
    }
}

function checkFavStoreModule() {
    global $ENABLE_FAVORITE_STORE_MODULE, $tconfig;

    $fav_store_file_path = $tconfig["tpanel_path"] . "include/features/include_fav_store.php";
    if (file_exists($fav_store_file_path) && strtoupper($ENABLE_FAVORITE_STORE_MODULE) == 'YES' && strtoupper(DELIVERALL) == "YES") {
        return true;
    }
    return false;
}


function isDeliveryPreferenceEnable() {
    return strtoupper(ENABLE_DELIVERY_PREFERENCE) == "YES" ? true : false;
}

function isTakeAwayEnable() {
    global $APP_PAYMENT_MODE;
    if(ENABLE_TAKE_AWAY=='Yes' && strtoupper($APP_PAYMENT_MODE) != "CASH") {
        return true;
    } else {
        return false;
    }
}

function checkSystemStoreSelection() {
    if(IS_SINGLE_STORE_SELECTION=='Yes') {
        return true;
    } else {
        return false;
    }
}

// Added By HJ On 20-03-2020 For Check Manage Driver By Store Enable In Store Panel Left Menu Link Start
function isStoreDriverAvailable() {
    //global $ENABLE_ADD_PROVIDER_FROM_STORE;
    //if (strtoupper($ENABLE_ADD_PROVIDER_FROM_STORE) == "YES") {
    if(ENABLE_ADD_PROVIDER_FROM_STORE=='Yes') {
        return true;
    }
    return false;
}

// Added By HJ On 20-03-2020 For Check Manage Driver By Store Enable In Store Panel Left Menu Link End
function checkSafetyPractice() {
    if(ENABLE_SAFETY_PRACTICE=='Yes') {
        return true;
    } else {
        return false;
    } 
}

function isRideModuleAvailable(){
    if((!empty(ONLYDELIVERALL) && strtoupper(ONLYDELIVERALL) == "YES") || strtoupper(APP_TYPE) == "DELIVERY" || strtoupper(APP_TYPE) == "UBERX"){
        return false;
    }
    return (!empty(RIDE_MODULE_AVAILABLE) && strtoupper(RIDE_MODULE_AVAILABLE) == "NO") ? false : true;
}

function isDeliverAllModuleAvailable(){
    if((!empty(DELIVERALL) && strtoupper(DELIVERALL) == "NO") || strtoupper(APP_TYPE) == "RIDE" || strtoupper(APP_TYPE) == "DELIVERY" || strtoupper(APP_TYPE) == "RIDE-DELIVERY" || strtoupper(APP_TYPE) == "UBERX"){
        return false;
    }
    return (!empty(DELIVERALL_MODULE_AVAILABLE) && strtoupper(DELIVERALL_MODULE_AVAILABLE) == "NO") ? false : true;
}

function isDeliveryModuleAvailable(){
    if((!empty(ONLYDELIVERALL) && strtoupper(ONLYDELIVERALL) == "YES") || strtoupper(APP_TYPE) == "RIDE" || strtoupper(APP_TYPE) == "UBERX"){
        return false;
    }
    
    return (!empty(DELIVERY_MODULE_AVAILABLE) && strtoupper(DELIVERY_MODULE_AVAILABLE) == "NO") ? false : true;
}
function isUberXModuleAvailable(){
    global $obj,$generalobj;
		
    if (strtoupper(ENABEL_SERVICE_PROVIDER_MODULE) == 'NO') {
        return false;
    }
    
    if (strtoupper(ONLYDELIVERALL) == 'YES') {
        return false;
    }
    
    if (!empty(IS_CUBE_X_THEME) && IS_CUBE_X_THEME == "Yes") {
        return false;
    }
    
    if (strtoupper(APP_TYPE) == "RIDE-DELIVERY-UBERX" || strtoupper(APP_TYPE) == "UBERX") {
        $ufx_data = $obj->MySQLSelect("SELECT COUNT(iVehicleCategoryId) AS Total FROM " . $generalobj->getVehicleCategoryTblName() . " WHERE 1 = 1 AND eCatType = 'ServiceProvider'");
        if (!empty($ufx_data[0]['Total']) && $ufx_data[0]['Total'] > 0) {
            return true;
        } else {
            return false;
        }
    }
    
    return false;
}
function isRentalFeatureAvailable() {
    if((!empty(ONLYDELIVERALL) && strtoupper(ONLYDELIVERALL) == "YES") || strtoupper(APP_TYPE) == "DELIVERY" || strtoupper(APP_TYPE) == "UBERX"){
        return false;
    }
    
    return (!empty(ENABLE_RENTAL_OPTION) && strtoupper(ENABLE_RENTAL_OPTION) == "YES") ? true : false;
}

function isEnableServerRequirementValidation() {
    if(ENABLE_SERVER_REQUIREMENT_VALIDATION=='Yes') {
        return true;
    } else {
        return false;
    } 
}

//Added By HV On 12-02-2020 For ENABLE_TERMS_SERVICE_CATEGORIES Feature As Per Discuss With KS Sir Start
function isEnableTermsServiceCategories() {
    global $ENABLE_TERMS_SERVICE_CATEGORIES;
    return strtoupper($ENABLE_TERMS_SERVICE_CATEGORIES) == "YES" ? true : false;
}
//Added By HV On 12-02-2020 For ENABLE_TERMS_SERVICE_CATEGORIES As Per Discuss With KS Sir End

//Added By HV On 12-02-2020 For ENABLE_TERMS_SERVICE_CATEGORIES Feature As Per Discuss With KS Sir Start
function isEnableProofUploadServiceCategories() {
    global $ENABLE_PROOF_UPLOAD_SERVICE_CATEGORIES;
    return strtoupper($ENABLE_PROOF_UPLOAD_SERVICE_CATEGORIES) == "YES" ? true : false;
}
//Added By HV On 12-02-2020 For ENABLE_TERMS_SERVICE_CATEGORIES As Per Discuss With KS Sir End
//Added By HJ On 16-06-2020 For Customer App Type CubejekX Deliverall Start As Per Discuss With KS Sir
function isDeliverAllOnlySystem(){
    if(isRideModuleAvailable() == false && isDeliveryModuleAvailable() == false && isUberXModuleAvailable() == false && isDeliverAllModuleAvailable() == true){
        return true;
    }
    return false;
}
//Added By HJ On 16-06-2020 For Customer App Type CubejekX Deliverall End As Per Discuss With KS Sir

function isEnableNewWalletWithdrawalFlowForDriver(){
    return strtoupper(ENABLE_NEW_WALLET_WITHDRAWAL_FLOW_DRIVER) == "YES" ? true : false;
}
?>