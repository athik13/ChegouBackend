<?php

############################################ Add Functions ############################################

function TripCollectTip($iMemberId, $iTripId, $fAmount) {
    global $generalobj, $obj, $APP_PAYMENT_METHOD;
    $tbl_name = "register_user";
    $currencycode = "vCurrencyPassenger";
    $iUserId = "iUserId";
    $eUserType = "Rider";
    if ($iMemberId == "") {
        $iMemberId = get_value('trips', 'iUserId', 'iTripId', $iTripId, '', 'true');
    }
 
    $vStripeCusId = $vStripeToken = $vBrainTreeToken = $userCurrencyCode = $vPaymayaCustId = $vPaymayaToken = $vStripeMethod = "";
    $getUserData = $obj->MySQLSelect("SELECT vStripeCusId,vStripeToken,vBrainTreeToken,$currencycode,vPaymayaCustId,vPaymayaToken FROM " . $tbl_name . " WHERE $iUserId='" . $iMemberId . "'");
    if (count($getUserData) > 0) {
        $vStripeCusId = $getUserData[0]['vStripeCusId'];
        $vStripeToken = $getUserData[0]['vStripeToken'];
        $vBrainTreeToken = $getUserData[0]['vBrainTreeToken'];
        $userCurrencyCode = $getUserData[0][$currencycode];
        $vPaymayaCustId = $getUserData[0]['vPaymayaCustId'];
        $vPaymayaToken = $getUserData[0]['vPaymayaToken'];
    }
    //$vStripeCusId = get_value($tbl_name, 'vStripeCusId', $iUserId, $iMemberId, '', 'true');
    //$vStripeToken = get_value($tbl_name, 'vStripeToken', $iUserId, $iMemberId, '', 'true');
    //$vBrainTreeToken = get_value($tbl_name, 'vBrainTreeToken', $iUserId, $iMemberId, '', 'true');
    //$userCurrencyCode = get_value($tbl_name, $currencycode, $iUserId, $iMemberId, '', 'true');
    $currencyCode = get_value('currency', 'vName', 'eDefault', 'Yes', '', 'true');
    $currencyratio = get_value('currency', 'Ratio', 'vName', $userCurrencyCode, '', 'true');
    //$UserCardData = get_value($tbl_name, 'vPaymayaCustId,vPaymayaToken', $iUserId, $iMemberId);
    //$vPaymayaCustId = $UserCardData[0]['vPaymayaCustId'];
    //$vPaymayaToken = $UserCardData[0]['vPaymayaToken'];
    //$price = $fAmount*$currencyratio;
    $price = round($fAmount / $currencyratio, 2);
    $price_new = $price * 100;
    $price_new = round($price_new);
    if ((($vStripeCusId == "" || $vStripeToken == "") && $APP_PAYMENT_METHOD == "Stripe")) {
        $returnArr["Action"] = "0";
        $returnArr['message'] = "LBL_NO_CARD_AVAIL_NOTE";
        setDataResponse($returnArr);
    }
    if ($vBrainTreeToken == "" && $APP_PAYMENT_METHOD == "Braintree") {
        $returnArr["Action"] = "0";
        $returnArr['message'] = "LBL_NO_CARD_AVAIL_NOTE";
        setDataResponse($returnArr);
    }
    if ((($vPaymayaCustId == "" || $vPaymayaToken == "") && $APP_PAYMENT_METHOD == "Paymaya")) {
        $returnArr["Action"] = "0";
        $returnArr['message'] = "LBL_NO_CARD_AVAIL_NOTE";
        setDataResponse($returnArr);
    }
    $dDate = Date('Y-m-d H:i:s');
    $eFor = 'Deposit';
    $eType = 'Credit';
    $tDescription_stripe = "Amount debited";
    $tDescription = "#LBL_AMOUNT_DEBIT#";
    $ePaymentStatus = 'Unsettelled';
    $userAvailableBalance = $generalobj->get_user_available_balance($iMemberId, $eUserType);
    if ($fAmount == 0 || $fAmount == 0.0 || $price_new < 0.51) {
        $returnArr["Action"] = "0";
        $returnArr['message'] = "LBL_REQUIRED_MINIMUM_AMOUT";
        $returnArr['minValue'] = strval(round(0.51 * $currencyratio, 2));
        setDataResponse($returnArr);
    }
	
    if ($userAvailableBalance > $price) {
        $where = " iTripId = '$iTripId'";
        $data['fTipPrice'] = $price;
        $id = $obj->MySQLQueryPerform("trips", $data, 'update', $where);
        $vRideNo = get_value('trips', 'vRideNo', 'iTripId', $iTripId, '', 'true');
        $data_wallet['iUserId'] = $iMemberId;
        $data_wallet['eUserType'] = "Rider";
        $data_wallet['iBalance'] = $price;
        $data_wallet['eType'] = "Debit";
        $data_wallet['dDate'] = date("Y-m-d H:i:s");
        $data_wallet['iTripId'] = $iTripId;
        $data_wallet['eFor'] = "Booking";
        $data_wallet['ePaymentStatus'] = "Unsettelled";
        $data_wallet['tDescription'] = '#LBL_DEBITED_TIP_AMOUNT_TXT#' . " - " . $vRideNo; //Debited for Tip of Trip
        //$data_wallet['tDescription'] = "#LBL_DEBITED_BOOKING# " . $vRideNo;
        $generalobj->InsertIntoUserWallet($data_wallet['iUserId'], $data_wallet['eUserType'], $data_wallet['iBalance'], $data_wallet['eType'], $data_wallet['iTripId'], $data_wallet['eFor'], $data_wallet['tDescription'], $data_wallet['ePaymentStatus'], $data_wallet['dDate']);
       
		/* Added By PM On 25-01-2020 For wallet credit to driver Start */			
		if (checkAutoCreditDriverModule()) {
				$Data = array();
				$Data['price']=$price;
				$Data['iUserId']=$iUserId;
				$Data['iTripId']=$iTripId;
				AutoCreditWalletDriver($Data,"TripCollectTip",0);
		}
		/* Added By PM On 25-01-2020 For wallet credit to driver End */
    } else if ($price > 0.51) {
        $Charge_Array = array(
            "iFare" => $price,
            "price_new" => $price_new,
            "currency" => $currencyCode,
            "vStripeCusId" => $vStripeCusId,
            "description" => $tDescription_stripe,
            "iTripId" => $iTripId,
            "eCancelChargeFailed" => "No",
            "vBrainTreeToken" => $vBrainTreeToken,
            "vRideNo" => $vRideNo,
            "iMemberId" => $iMemberId,
            "UserType" => "Passenger"
        );
        $ChargeidArr = ChargeCustomer($Charge_Array, "submitRating"); // function for charge customer
        $ChargeidArrId = $ChargeidArr['id'];
        $status = $ChargeidArr['status'];
        if ($status == "success") {
            $where = " iTripId = '$iTripId'";
            $data['fTipPrice'] = $price;
            $id = $obj->MySQLQueryPerform("trips", $data, 'update', $where);
            $where_payments = " iPaymentId = '" . $ChargeidArrId . "'";
            $data_payments['iTripId'] = $iTripId;
            $data_payments['eEvent'] = "TripTip";
            $obj->MySQLQueryPerform("payments", $data_payments, 'update', $where_payments);
			/* Added By PM On 25-01-2020 For wallet credit to driver Start */
			if (checkAutoCreditDriverModule()) {
						$Data = array();
						$Data['price']=$price;
						$Data['iUserId']=$iUserId;
						$Data['iTripId']=$iTripId;
						AutoCreditWalletDriver($Data,"TripCollectTip",0);
				}
			/* Added By PM On 25-01-2020 For wallet credit to driver End */
        } else {
            $returnArr['Action'] = "0";
            $returnArr['message'] = "LBL_TRANS_FAILED";
            setDataResponse($returnArr);
        }
    } else {
        $returnArr["Action"] = "0";
        $returnArr['message'] = "LBL_REQUIRED_MINIMUM_AMOUT";
        $returnArr['minValue'] = strval(round(0.51 * $currencyratio, 2));
        setDataResponse($returnArr);
    }
    return $iTripId;
}

function getHotelDetailInfo($passengerID, $cityName) {
    global $generalobj, $obj, $demo_site_msg, $PHOTO_UPLOAD_SERVICE_ENABLE, $parent_ufx_catid, $generalSystemConfigDataArr, $tconfig, $vTimeZone, $vUserDeviceCountry, $_REQUEST, $intervalmins, $generalConfigPaymentArr, $ENABLE_RESTAURANTS_ADVERTISEMENT_BANNER, $ADVERTISEMENT_TYPE;
    $where = " iHotelId = '" . $passengerID . "'";
    $data_version['iAppVersion'] = "2";
    $data_version['eLogout'] = 'No';
    $IS_DEBUG_MODE = isset($_REQUEST["IS_DEBUG_MODE"]) ? $_REQUEST["IS_DEBUG_MODE"] : '';
    if ($IS_DEBUG_MODE != "") {
        $data_version['eDebugMode'] = $_REQUEST["IS_DEBUG_MODE"];
    }
    $obj->MySQLQueryPerform("hotel", $data_version, 'update', $where);
    // kiosk changes
    $sql = "SELECT h.*,a.* FROM `hotel` as h LEFT JOIN administrators as a on a.iAdminId=h.iAdminId WHERE h.iHotelId='$passengerID'";
    $row = $obj->MySQLSelect($sql);

    if (count($row) > 0) {
        ### Update Tripid - 0 and TripStatus - None For UberX Trip ###
        $currenttrip = $row[0]['iTripId'];
        if ($currenttrip > 0) {
            $sql = "SELECT eType FROM `trips` WHERE iTripId = '" . $currenttrip . "'";
            $db_currenttrip = $obj->MySQLSelect($sql);
        }
        ### Update Tripid - 0 and TripStatus - None For UberX Trip ###
        $row[0]['LOGO_IMAGE'] = "";
        if ($row[0]['vImgName'] != "" && $row[0]['vImgName'] != "NONE") {
            $row[0]['vImgName'] = $row[0]['vImgName'];
            $row[0]['LOGO_IMAGE'] = $tconfig["tsite_upload_images_hotel_passenger"] . '/' . $passengerID . "/3_" . $row[0]['vImgName'];
        }

        // kiosk changes
        $row[0]['BANNER_IMAGE'] = "";
        if ($row[0]['vVehicleTypeImg'] != "" && $row[0]['vVehicleTypeImg'] != "NONE") {
            $row[0]['vVehicleTypeImg'] = $row[0]['vVehicleTypeImg'];
            $row[0]['BANNER_IMAGE'] = $tconfig["tsite_upload_images_hotel_passenger"] . '/' . $passengerID . '/' . $row[0]['vVehicleTypeImg'];
        }

        $row[0]['vName'] = $row[0]['vFirstName'];
        $row[0]['vAddress'] = $row[0]['vAddress'];
        $page_link = $tconfig['tsite_url'] . "sign-up_rider.php?UserType=Rider";
        $link = get_tiny_url($page_link);
        //$activation_text = '<a href="'.$link.'" target="_blank"> '.$link.' </a>';
        $activation_text = "<a href='" . $link . "' target='_blank'> '" . $link . "' </a>";
        $vLanguage = $row[0]['vLang'];
        if ($vLanguage == "" || $vLanguage == NULL) {
            $vLanguage = get_value('language_master', 'vCode', 'eDefault', 'Yes', '', 'true');
        }
        $sql = "SELECT * FROM `language_label` WHERE vLabel = 'LBL_SHARE_CONTENT_PASSENGER' AND vCode = '" . $vLanguage . "'";
        $db_label = $obj->MySQLSelect($sql);
        $LBL_SHARE_CONTENT_PASSENGER = $db_label[0]['vValue'];
        $row[0]['INVITE_SHARE_CONTENT'] = $LBL_SHARE_CONTENT_PASSENGER . " " . $link;
        $row[0] = array_merge($row[0],$generalSystemConfigDataArr); // Added By HJ On 18-03-2020 For Optimized Function
        $row[0]['GOOGLE_ANALYTICS'] = "";
        $row[0]['SERVER_MAINTENANCE_ENABLE'] = $row[0]['MAINTENANCE_APPS'];
        $RIDER_EMAIL_VERIFICATION = $row[0]["RIDER_EMAIL_VERIFICATION"];
        $RIDER_PHONE_VERIFICATION = $row[0]["RIDER_PHONE_VERIFICATION"];
        $REFERRAL_AMOUNT = $row[0]["REFERRAL_AMOUNT"];
        $REFERRAL_AMOUNT_ARR = getPriceUserCurrency($passengerID, "Hotel", $REFERRAL_AMOUNT);
        $REFERRAL_AMOUNT_USER = $REFERRAL_AMOUNT_ARR['fPricewithsymbol'];
        $LBL_INVITE_FRIEND_SHARE_PREFIX_TXT = get_value('language_label', 'vValue', 'vLabel', 'LBL_INVITE_FRIEND_SHARE_PREFIX_TXT', " and vCode='" . $vLanguage . "'", 'true');
        $LBL_INVITE_FRIEND_SHARE_POSTFIX_TXT = get_value('language_label', 'vValue', 'vLabel', 'LBL_INVITE_FRIEND_SHARE_POSTFIX_TXT', " and vCode='" . $vLanguage . "'", 'true');
        $row[0]['INVITE_DESCRIPTION_CONTENT'] = $LBL_INVITE_FRIEND_SHARE_PREFIX_TXT . " " . $REFERRAL_AMOUNT_USER . " " . $LBL_INVITE_FRIEND_SHARE_POSTFIX_TXT;
        if ($RIDER_EMAIL_VERIFICATION == 'No') {
            $row[0]['eEmailVerified'] = "Yes";
        }
        if ($RIDER_PHONE_VERIFICATION == 'No') {
            $row[0]['ePhoneVerified'] = "Yes";
        }
        $lang_usr = $row[0]['vLang'];
        $sql = "select vBody_$lang_usr as Message from send_message_templates where vEmail_Code = 'VERIFICATION_CODE_MESSAGE'";
        $data_SMS = $obj->MySQLSelect($sql);
        $row[0]['SMS_BODY'] = $data_SMS[0]['Message'];
        ## Display Braintree Charge Message ##
        $sql = "SELECT * FROM `language_label` WHERE vLabel = 'LBL_BRAINTREE_CHARGE_MSG_TXT' AND vCode = '" . $vLanguage . "'";
        $db_label_braintree = $obj->MySQLSelect($sql);
        $LBL_BRAINTREE_CHARGE_MSG_TXT = $db_label_braintree[0]['vValue'];
        $BRAINTREE_CHARGE_AMOUNT = $row[0]['BRAINTREE_CHARGE_AMOUNT'];
        $BRAINTREE_CHARGE_AMOUNT_USER_ARR = getPriceUserCurrency($passengerID, "Passenger", $BRAINTREE_CHARGE_AMOUNT);
        $BRAINTREE_CHARGE_AMOUNT_USER = $BRAINTREE_CHARGE_AMOUNT_USER_ARR['fPricewithsymbol'];
        //$msg = str_replace('##AMOUNT##', $BRAINTREE_CHARGE_AMOUNT_USER, $LBL_BRAINTREE_CHARGE_MSG_TXT);
        $msg = str_replace('####', $BRAINTREE_CHARGE_AMOUNT_USER, $LBL_BRAINTREE_CHARGE_MSG_TXT);
        $row[0]['BRAINTREE_CHARGE_MESSAGE'] = $msg;
        ## Display Braintree Charge Message ##
        ## Display Adyen Charge Message ##
        $sql = "SELECT * FROM `language_label` WHERE vLabel = 'LBL_ADYEN_CHARGE_MSG_TXT' AND vCode = '" . $vLanguage . "'";
        $db_label_adyen = $obj->MySQLSelect($sql);
        $LBL_ADYEN_CHARGE_MSG_TXT = $db_label_adyen[0]['vValue'];
        $ADEYN_CHARGE_AMOUNT = $row[0]['ADYEN_CHARGE_AMOUNT'];
        $ADEYN_CHARGE_AMOUNT_USER_ARR = getPriceUserCurrency($passengerID, "Passenger", $ADEYN_CHARGE_AMOUNT);
        $ADEYN_CHARGE_AMOUNT_USER = $ADEYN_CHARGE_AMOUNT_USER_ARR['fPricewithsymbol'];
        $msg = str_replace('####', $ADEYN_CHARGE_AMOUNT_USER, $LBL_ADYEN_CHARGE_MSG_TXT);
        $row[0]['ADEYN_CHARGE_MESSAGE'] = $msg;
        ## Display Adyen Charge Message ##
        ## Check and update Device Session ID ##
        if ($row[0]['tDeviceSessionId'] == "") {
            $random = substr(md5(rand()), 0, 7);
            $Update_Device_Session['tDeviceSessionId'] = session_id() . time() . $random;
            $Update_Device_Session_id = $obj->MySQLQueryPerform("register_user", $Update_Device_Session, 'update', $where);
            $row[0]['tDeviceSessionId'] = $Update_Device_Session['tDeviceSessionId'];
        }
        ## Check and update Device Session ID ##
        ## Check and update Session ID ##
        if ($row[0]['tSessionId'] == "") {
            $Update_Session['tSessionId'] = session_id() . time();
            $Update_Session_id = $obj->MySQLQueryPerform("register_user", $Update_Session, 'update', $where);
            $row[0]['tSessionId'] = $Update_Session['tSessionId'];
        }
        ## Check and update Session ID ##
        if ($row[0]['vImgName'] != "" && $row[0]['vImgName'] != "NONE") {
            $row[0]['vImgName'] = "3_" . $row[0]['vImgName'];
        }
        //$row[0]['Passenger_Password_decrypt']= $generalobj->decrypt($row[0]['vPassword']);
        $row[0]['Passenger_Password_decrypt'] = "";
        if ($row[0]['eStatus'] != "Active") {
            $returnArr['Action'] = "0";
            if ($row[0]['eStatus'] != "Deleted") {
                $returnArr['message'] = "LBL_CONTACT_US_STATUS_NOTACTIVE_PASSENGER";
            } else {
                $returnArr['message'] = "LBL_ACC_DELETE_TXT";
            }
            setDataResponse($returnArr);
        }
        $TripStatus = $row[0]['vTripStatus'];
        $TripID = $row[0]['iTripId'];
        if ($TripStatus != "NONE") {
            $TripID = $row[0]['iTripId'];
            $row_result_trips = getTripPriceDetails($TripID, $passengerID, "Passenger");
            $row[0]['TripDetails'] = $row_result_trips;
            $row[0]['DriverDetails'] = $row_result_trips['DriverDetails'];
            $row_result_trips['DriverCarDetails']['make_title'] = $row_result_trips['DriverCarDetails']['vMake'];
            $row_result_trips['DriverCarDetails']['model_title'] = $row_result_trips['DriverCarDetails']['vTitle'];
            $row[0]['DriverCarDetails'] = $row_result_trips['DriverCarDetails'];
            $sql = "SELECT vPaymentUserStatus FROM `payments` WHERE iTripId='$TripID'";
            $row_result_payments = $obj->MySQLSelect($sql);
            $row[0]['PaymentStatus_From_Passenger'] = "No Entry";
            if (count($row_result_payments) > 0) {
                $row[0]['PaymentStatus_From_Passenger'] = "Approved";
                if ($row_result_payments[0]['vPaymentUserStatus'] != 'approved') {
                    $row[0]['PaymentStatus_From_Passenger'] = "Not Approved";
                }
            }
            $sql = "SELECT iTripId,eUserType FROM `ratings_user_driver` WHERE iTripId='$TripID'";
            $row_result_ratings = $obj->MySQLSelect($sql);
            $row[0]['Ratings_From_Passenger'] = "No Entry";
            if (count($row_result_ratings) > 0) {
                $count_row_rating = 0;
                $ContentWritten = "false";
                while (count($row_result_ratings) > $count_row_rating) {
                    $UserType = $row_result_ratings[$count_row_rating]['eUserType'];
                    $row[0]['Ratings_From_Passenger'] = "Not Done";
                    if ($UserType == "Passenger") {
                        $ContentWritten = "true";
                        $row[0]['Ratings_From_Passenger'] = "Done";
                    }
                    $count_row_rating++;
                }
            }
        }
        // $row[0]['PayPalConfiguration']=$generalobj->getConfigurations("configurations","PAYMENT_ENABLED");
        $row[0]['DefaultCurrencySign'] = $row[0]["DEFAULT_CURRENCY_SIGN"];
        $row[0]['DefaultCurrencyCode'] = $row[0]["DEFAULT_CURRENCY_CODE"];
        $row[0]['FETCH_TRIP_STATUS_TIME_INTERVAL'] = fetchtripstatustimeinterval();
        $row[0]['ENABLE_TOLL_COST'] = $row[0]['APP_TYPE'] != "UberX" ? $row[0]['ENABLE_TOLL_COST'] : "No";
        $usercountrycode = $row[0]['vCountry'];
        if ($usercountrycode != "") {
            $sqlc = "SELECT eEnableToll from country WHERE vCountryCode = '" . $usercountrycode . "'";
            $user_country_toll = $obj->MySQLSelect($sqlc);
            $eEnableToll = $user_country_toll[0]['eEnableToll'];
            if ($eEnableToll != "") {
                $row[0]['ENABLE_TOLL_COST'] = ($eEnableToll == "Yes" && $row[0]['ENABLE_TOLL_COST'] == "Yes") ? "Yes" : "No";
            }
        }
        if ($row[0]['APP_TYPE'] == "Ride" || $row[0]['APP_TYPE'] == "Ride-Delivery" || $row[0]['APP_TYPE'] == "Ride-Delivery-UberX") {
            $row[0]['FEMALE_RIDE_REQ_ENABLE'] = $row[0]['FEMALE_RIDE_REQ_ENABLE'];
            $row[0]['HANDICAP_ACCESSIBILITY_OPTION'] = $row[0]['HANDICAP_ACCESSIBILITY_OPTION'];
        } else {
            $row[0]['FEMALE_RIDE_REQ_ENABLE'] = "No";
            $row[0]['HANDICAP_ACCESSIBILITY_OPTION'] = "No";
            // $row[0]['ENABLE_TOLL_COST'] = "No";
        }
        if ($row[0]['APP_TYPE'] == "Ride" || $row[0]['APP_TYPE'] == "Ride-Delivery" || $row[0]['APP_TYPE'] == "Ride-Delivery-UberX") {
            $row[0]['ENABLE_HAIL_RIDES'] = $row[0]['ENABLE_HAIL_RIDES'];
        } else {
            $row[0]['ENABLE_HAIL_RIDES'] = "No";
        }
        if ($row[0]['APP_PAYMENT_MODE'] == "Card" || ONLYDELIVERALL == "Yes") {
            $row[0]['ENABLE_HAIL_RIDES'] = "No";
        }
        // $row[0]['PHOTO_UPLOAD_SERVICE_ENABLE']=$PHOTO_UPLOAD_SERVICE_ENABLE;
        $row[0]['PHOTO_UPLOAD_SERVICE_ENABLE'] = $row[0]['APP_TYPE'] == "UberX" ? $PHOTO_UPLOAD_SERVICE_ENABLE : "No";
        $row[0]['ENABLE_TIP_MODULE'] = $row[0]['ENABLE_TIP_MODULE'];
        //$row[0]['UBERX_PARENT_CAT_ID'] = 1;
        if ($row[0]['APP_TYPE'] == "UberX") {
            $row[0]['APP_DESTINATION_MODE'] = "None";
            $row[0]['ENABLE_TOLL_COST'] = "No";
            $row[0]['HANDICAP_ACCESSIBILITY_OPTION'] = "No";
            $row[0]['FEMALE_RIDE_REQ_ENABLE'] = "No";
            $row[0]['ENABLE_HAIL_RIDES'] = "No";
            $row[0]['ONLINE_DRIVER_LIST_UPDATE_TIME_INTERVAL'] = "5";
        }
        // $row[0]['ENABLE_DELIVERY_MODULE']=$generalobj->getConfigurations("configurations","ENABLE_DELIVERY_MODULE");
        $row[0]['ENABLE_DELIVERY_MODULE'] = SITE_TYPE == "Demo" ? $row[0]['eDeliverModule'] : $row[0]['ENABLE_DELIVERY_MODULE'];
        $row[0]['PayPalConfiguration'] = $row[0]['ENABLE_DELIVERY_MODULE'] == "Yes" ? "Yes" : $row[0]['PAYMENT_ENABLED'];
        // if($row[0]['ENABLE_DELIVERY_MODULE'] == "Yes"){
        // $row[0]['PayPalConfiguration'] = "Yes";
        // }
        $row[0]['CurrencyList'] = get_value('currency', '*', 'eStatus', 'Active');
        $row[0]['SITE_TYPE'] = SITE_TYPE;
        $row[0]['RIIDE_LATER'] = RIIDE_LATER;
        $row[0]['PROMO_CODE'] = PROMO_CODE;
        $row[0]['SITE_TYPE_DEMO_MSG'] = $demo_site_msg;
        $row[0]['CurrencySymbol'] = get_value('currency', 'vSymbol', 'vName', $row[0]['vCurrencyPassenger'], '', 'true');
        $eUnit = getMemberCountryUnit($passengerID, "Hotel");
        $row[0]['eUnit'] = $eUnit;
        $SITE_POLICE_CONTROL_NUMBER = getMemberCountryPoliceNumber($passengerID, "Passenger", $row[0]['vCountry']);
        $row[0]['SITE_POLICE_CONTROL_NUMBER'] = $SITE_POLICE_CONTROL_NUMBER;
        $row[0]['MAX_ALLOW_TIME_INTERVAL_MILLI'] = (fetchtripstatustimeMAXinterval() + $intervalmins) * 1000;
        $vCurrencyPassenger = $row[0]['vCurrencyPassenger'];
        if ($vCurrencyPassenger == "" || $vCurrencyPassenger == NULL) {
            $vCurrencyPassenger = get_value('currency', 'vName', 'eDefault', 'Yes', '', 'true');
        }
        $CurrencySymbol = get_value('currency', 'vSymbol', 'vName', $vCurrencyPassenger, '', 'true');
        $Ratio = get_value('currency', 'Ratio', 'vName', $vCurrencyPassenger, '', 'true');
        $row[0]['MONGO_DB'] = $tconfig['tmongodb_databse'];
        $row[0]['MONGO_DB_CONNECTION_PORT'] = $tconfig['tmongodb_port'];
        $row[0]['SERVER_DEFAULT_TIMEZONE'] = date_default_timezone_get();
        $row[0]['tsite_upload_docs_file_extensions'] = $tconfig['tsite_upload_docs_file_extensions'];
        $row[0]['SC_CONNECT_URL'] = getSocketURL();
        //$sql = "SELECT iVisitId,vSourceLatitude,vSourceLongitude,vDestLatitude,vDestLongitude,vSourceAddresss,tDestAddress,tDestLocationName FROM  visit_address WHERE `eStatus` = 'Active' ORDER BY iDisplayOrder ASC";
        //added by SP for kioskchange via hotelid
        $gethotelid = get_value('hotel', 'iAdminId', 'iHotelId', $passengerID, '', 'true');
        $sql = "SELECT iVisitId,vSourceLatitude,vSourceLongitude,vDestLatitude,vDestLongitude,vSourceAddresss,tDestAddress,tDestLocationName FROM  visit_address WHERE `eStatus` = 'Active' AND iHotelId = '" . $gethotelid . "' ORDER BY iDisplayOrder ASC";

        $Data_visitlocation = $obj->MySQLSelect($sql);
        $row[0]['Visit_Locations'] = $Data_visitlocation;
        //Added By Hasmukh On 25-12-2018 For Get Advertise Banner Data Start
        if (isset($ENABLE_RESTAURANTS_ADVERTISEMENT_BANNER) && $ENABLE_RESTAURANTS_ADVERTISEMENT_BANNER == "Yes" && $ADVERTISEMENT_TYPE != "Disable" && $row[0]['eStatus'] == "Active") {
            $adBannerData = getAdvertisementBanners($passengerID, "Store");
            $bannerData = array();
            if (isset($adBannerData['iAdvertBannerId']) && $adBannerData['iAdvertBannerId'] > 0) {
                $iAdvertBannerId = $adBannerData['iAdvertBannerId'];
                $pathOfAdvImage = $tconfig['tsite_upload_advertise_banner'] . '/' . $adBannerData['vBannerImage'];
                $imagedata = getimagesize($pathOfAdvImage);
                $bannerData['vImageWidth'] = strval($imagedata[0]);
                $bannerData['vImageHeight'] = strval($imagedata[1]);
                $bannerData['tRedirectUrl'] = $adBannerData['tRedirectUrl'];
                $bannerData['image_url'] = $tconfig['tsite_url'] . "webservice_shark.php?type=insertBannereImpressionCount&iAdvertBannerId=" . $iAdvertBannerId . "&iUserId=" . $passengerID . "&UserType=Store";
                $row[0]['advertise_banner_data'] = json_encode($bannerData);
            } else {
                $row[0]['advertise_banner_data'] = "";
            }
        }
        //Added By Hasmukh On 25-12-2018 For Get Advertise Banner Data End
        return $row[0];
    } else {
        $returnArr['Action'] = "0";
        $returnArr['message'] = "LBL_TRY_AGAIN_LATER_TXT";
        setDataResponse($returnArr);
    }
}

############################################ Add Functions ############################################
################################## Get Hotel Banners #############################################################################
if ($type == "getHotelBanners") {
    global $generalobj;
    $iHotelId = isset($_REQUEST['iMemberId']) ? clean($_REQUEST['iMemberId']) : '';
    if ($iHotelId != "") {
        $vLanguage = get_value('hotel', 'vLang', 'iHotelId', $iHotelId, '', 'true');
        if ($vLanguage == "" || $vLanguage == NULL) {
            $vLanguage = get_value('language_master', 'vCode', 'eDefault', 'Yes', '', 'true');
        }
        //$banners = get_value('hotel_banners', 'vImage', 'vCode', $vLanguage, ' ORDER BY iDisplayOrder ASC');
        $banners = $obj->MySQLSelect("SELECT vImage FROM hotel_banners WHERE vCode = '" . $vLanguage . "' AND eStatus = 'Active' ORDER BY iDisplayOrder ASC");
        $data = array();
        $count = 0;
        for ($i = 0; $i < count($banners); $i++) {
            if ($banners[$i]['vImage'] != "" && file_exists($tconfig["tsite_upload_images_hotel_banner_path"] . '/' . $banners[$i]['vImage'])) {
                $data[$count]['vImage'] = $tconfig["tsite_upload_images_hotel_banner"] . '/' . $banners[$i]['vImage'];
                ;
                $count++;
            }
        }
        $returnArr['Action'] = "1";
        $returnArr['message'] = $data;
    } else {
        $returnArr['Action'] = "0";
        $returnArr['message'] = "LBL_TRY_AGAIN_LATER_TXT";
    }
    setDataResponse($returnArr);
}
################################## Get Hotel Banners #############################################################################
#################################################Sign Up Kiosk Passenger #########################################################
if ($type == "signup_kiosk_passanger") {
    global $tconfig;
    $hotelid = isset($_REQUEST["iHotelId"]) ? $_REQUEST["iHotelId"] : '';
    $name = isset($_REQUEST["vName"]) ? $_REQUEST["vName"] : 'User';
    $vLastName = isset($_REQUEST["vLastName"]) ? $_REQUEST["vLastName"] : '';
    $email = isset($_REQUEST["vEmail"]) ? $_REQUEST["vEmail"] : '';
    $phone_mobile = isset($_REQUEST["vPhone"]) ? $_REQUEST["vPhone"] : '';
    $password = isset($_REQUEST["vPassword"]) ? $_REQUEST["vPassword"] : '123456';
    $iGcmRegId = isset($_REQUEST["vDeviceToken"]) ? $_REQUEST["vDeviceToken"] : '';
    $CountryCode = isset($_REQUEST["CountryCode"]) ? $_REQUEST["CountryCode"] : '';
    $deviceType = isset($_REQUEST["vDeviceType"]) ? $_REQUEST["vDeviceType"] : 'Android';
    $vCurrencyPassenger = isset($_REQUEST["vCurrencyPassenger"]) ? $_REQUEST["vCurrencyPassenger"] : '';
    $eSignUpType = isset($_REQUEST["eSignUpType"]) ? $_REQUEST["eSignUpType"] : 'Normal';
    $vFirebaseDeviceToken = isset($_REQUEST["vFirebaseDeviceToken"]) ? $_REQUEST["vFirebaseDeviceToken"] : '';
    $vPhoneCode = isset($_REQUEST["PhoneCode"]) ? $_REQUEST["PhoneCode"] : '';
    $vLang = isset($_REQUEST["vLang"]) ? $_REQUEST["vLang"] : '';
    $vCountry = isset($_REQUEST["vUserDeviceCountry"]) ? $_REQUEST["vUserDeviceCountry"] : '';
    $date = date('Y-m-d');
    $Password_user = $generalobj->encrypt($password);
    //added by SP for kiosk change in vRecipientEmail insteadof vEmail
    if (empty($hotelid) && !empty($_REQUEST["GeneralMemberId"])) {
        $hotelid = $_REQUEST["GeneralMemberId"];
    }

    //Check Status Hotel User >> Hotel must be active
    $db_rec = $obj->MySQLSelect("SELECT h.*,a.* FROM hotel as h LEFT JOIN administrators as a on a.iAdminId=h.iAdminId WHERE h.iHotelId=" . $hotelid . " AND h.eStatus = 'Active' AND a.eStatus = 'Active'");

    if (empty($db_rec) || count($db_rec) == 0) {
        $returnArr = array();
        $returnArr['Action'] = "0";
        $returnArr['message'] = "LBL_HOTEL_DISABLED";
        setDataResponse($returnArr);
    }

    //if (empty($email) && empty($phone_mobile)) {
    if (empty($phone_mobile)) {

        if (empty($db_rec) || count($db_rec) == 0) {
            $sql = "SELECT h.*,a.* FROM hotel as h LEFT JOIN administrators as a on a.iAdminId=h.iAdminId WHERE h.iHotelId=" . $hotelid;
            $db_rec = $obj->MySQLSelect($sql);
        }

        $Data_passenger['vName'] = $db_rec[0]['vName'] . "-" . $name;
        $Data_passenger['vLastName'] = $db_rec[0]['vLastName'];
        //$Data_passenger['vEmail'] = $db_rec[0]['vEmail'];
        $Data_passenger['vRecipientEmail'] = $db_rec[0]['vEmail'];
        $Data_passenger['vPhone'] = $db_rec[0]['vPhone'];
        $Data_passenger['vPassword'] = $Password_user;
        $Data_passenger['iGcmRegId'] = $iGcmRegId;
        $Data_passenger['vLang'] = get_value('language_master', 'vCode', 'eDefault', 'Yes', '', 'true');
        $Data_passenger['eDeviceType'] = $deviceType;
        $Data_passenger['eIs_Kiosk'] = "Yes";
        $Data_passenger['iHotelId'] = $hotelid;
        $Data_passenger['vLang'] = $vLang;
        $Data_passenger['vCurrencyPassenger'] = $vCurrencyPassenger;
        $Data_passenger['vPhoneCode'] = $vPhoneCode;
        $Data_passenger['eEmailVerified'] = 'Yes';
        $Data_passenger['ePhoneVerified'] = 'Yes';
        $id = $obj->MySQLQueryPerform("register_user", $Data_passenger, 'insert');
        $src = $tconfig["tsite_upload_images_hotel_passenger_path"] . "/" . $hotelid;
        $dest = $tconfig['tsite_upload_images_passenger_path'] . "/" . $id;
        $Data_passenger_detail['vImgName'] = $db_rec[0]['vImgName'];
        $where_pass = " iUserId = '" . $id . "' ";
        $pass_id = $obj->MySQLQueryPerform("register_user", $Data_passenger_detail, 'update', $where_pass);
        shell_exec("cp -r $src $dest");
        if ($id > 0) {
            /* new added */
            $returnArr['Action'] = "1";
            $returnArr['message'] = getPassengerDetailInfo($id, "");
            setDataResponse($returnArr);
        } else {
            $returnArr['Action'] = "0";
            $returnArr['message'] = "LBL_TRY_AGAIN_LATER_TXT";
            setDataResponse($returnArr);
        }
    } else {

        //$sql = "SELECT * FROM `register_user` WHERE vEmail = '$email' OR vPhone = '$phone_mobile'";
        //IF('$email'!='',vEmail = '$email',0) OR
        $ssql_phone = "";
        if (!empty($phone_mobile)) {
            $ssql_phone = "vPhone= '" . $phone_mobile . "'";
        }

        $ssql_email = "";
        if (!empty($email)) {
            //$ssql_email = " OR vEmail= '".$email."'";
            $ssql_email = " OR vRecipientEmail = '" . $email . "'";
        }

        $sql = "SELECT * FROM register_user WHERE 1=1 AND IF('$phone_mobile'!='',vPhone = '$phone_mobile',0) ";

        // $sql = "SELECT * FROM register_user WHERE ".$ssql_phone. " ".$ssql_email;
        $check_passenger = $obj->MySQLSelect($sql);
        if (count($check_passenger) > 0) {
            $returnArr['Action'] = "0";
            $Data_passenger['iGcmRegId'] = $iGcmRegId;
            $Data_passenger['eDeviceType'] = $deviceType;
            $Data_passenger['vLang'] = $vLang;
            $Data_passenger['vCurrencyPassenger'] = $vCurrencyPassenger;
            $Data_passenger['vName'] = $name;
            $Data_passenger['vLastName'] = $vLastName;
            $Data_passenger['vRecipientEmail'] = $email;
            //$Data_passenger['vEmail'] = $email;
            $Data_passenger['vPhone'] = $phone_mobile;
            $Data_passenger['vPhoneCode'] = $vPhoneCode;
            $Data_passenger['iHotelId'] = $hotelid;
            $Data_passenger['vCountry'] = strtoupper($CountryCode);
            $Data_passenger['eEmailVerified'] = 'Yes';
            $Data_passenger['ePhoneVerified'] = 'Yes';
            $where = " iUserId = '" . $check_passenger[0]['iUserId'] . "' ";
            $res_id = $obj->MySQLQueryPerform("register_user", $Data_passenger, 'update', $where);
            //if ($email == $check_passenger[0]['vEmail']) {
            if ($email == $check_passenger[0]['vRecipientEmail']) {
                $returnArr['Action'] = "1";
                $returnArr['message'] = getPassengerDetailInfo($check_passenger[0]['iUserId']);
            } else {
                $returnArr['Action'] = "1";
                $returnArr['message'] = getPassengerDetailInfo($check_passenger[0]['iUserId']);
            }
            setDataResponse($returnArr);
        } else {
            $Data_passenger['vName'] = $name;
            $Data_passenger['vLastName'] = $vLastName;
            $Data_passenger['vCountry'] = strtoupper($CountryCode);
            //$Data_passenger['vEmail'] = $email;
            $Data_passenger['vRecipientEmail'] = $email;
            $Data_passenger['vPhone'] = $phone_mobile;
            $Data_passenger['iGcmRegId'] = $iGcmRegId;
            $Data_passenger['vLang'] = get_value('language_master', 'vCode', 'eDefault', 'Yes', '', 'true');
            $Data_passenger['eDeviceType'] = $deviceType;
            $Data_passenger['eIs_Kiosk'] = "Yes";
            $Data_passenger['iHotelId'] = $hotelid;
            $Data_passenger['vLang'] = $vLang;
            $Data_passenger['vCurrencyPassenger'] = $vCurrencyPassenger;
            $Data_passenger['vFirebaseDeviceToken'] = $vFirebaseDeviceToken;
            $Data_passenger['eSignUpType'] = $eSignUpType;
            $Data_passenger['vPhoneCode'] = $vPhoneCode;
            $Data_passenger['eEmailVerified'] = 'Yes';
            $Data_passenger['ePhoneVerified'] = 'Yes';
            $id = $obj->MySQLQueryPerform("register_user", $Data_passenger, 'insert');
            $src = $tconfig["tsite_upload_images_hotel_passenger_path"] . "/" . $hotelid;
            $dest = $tconfig['tsite_upload_images_passenger_path'] . "/" . $id;
            $Data_passenger_detail['vImgName'] = $db_rec[0]['vImgName'];
            $where_pass = " iUserId = '" . $id . "' ";
            $pass_id = $obj->MySQLQueryPerform("register_user", $Data_passenger_detail, 'update', $where_pass);
            shell_exec("cp -r $src $dest");
            if ($id > 0) {
                /* new added */
                $returnArr['Action'] = "1";
                $returnArr['message'] = getPassengerDetailInfo($id);

                $maildata['EMAIL'] = $email;
                $maildata['NAME'] = $name;
                $maildata['PASSWORD'] = $password;
                $generalobj->send_email_user("MEMBER_REGISTRATION_USER", $maildata);

                setDataResponse($returnArr);
            } else {
                $returnArr['Action'] = "0";
                $returnArr['message'] = "LBL_TRY_AGAIN_LATER_TXT";
                setDataResponse($returnArr);
            }
        }
    }
}
################################################################################
if ($type == "updateuserPref") {
    $iMemberId = isset($_REQUEST['iMemberId']) ? clean($_REQUEST['iMemberId']) : '';
    $userType = isset($_REQUEST['UserType']) ? clean($_REQUEST['UserType']) : 'Driver';
    $eFemaleOnly = isset($_REQUEST['eFemaleOnly']) ? clean($_REQUEST['eFemaleOnly']) : 'No';
    $where = " iDriverId = '$iMemberId'";
    $Data_update_User['eFemaleOnlyReqAccept'] = $eFemaleOnly;
    $id = $obj->MySQLQueryPerform("register_driver", $Data_update_User, 'update', $where);
    if ($id > 0) {
        $returnArr['Action'] = "1";
        $returnArr['message'] = getDriverDetailInfo($iMemberId);
    } else {
        $returnArr['Action'] = "0";
        $returnArr['message'] = "LBL_TRY_AGAIN_LATER_TXT";
    }
    setDataResponse($returnArr);
}
###########################################################################
if ($type == "updateUserGender") {
    $iMemberId = isset($_REQUEST['iMemberId']) ? clean($_REQUEST['iMemberId']) : '';
    $userType = isset($_REQUEST['UserType']) ? clean($_REQUEST['UserType']) : 'Driver';
    $eGender = isset($_REQUEST['eGender']) ? clean($_REQUEST['eGender']) : '';

    if ($userType == "Driver") {
        $where = " iDriverId = '$iMemberId'";
        $Data_update_User['eGender'] = $eGender;

        $id = $obj->MySQLQueryPerform("register_driver", $Data_update_User, 'update', $where);
    } else {
        $where = " iUserId = '$iMemberId'";
        $Data_update_User['eGender'] = $eGender;

        $id = $obj->MySQLQueryPerform("register_user", $Data_update_User, 'update', $where);
    }

    if ($id > 0) {
        $returnArr['Action'] = "1";
        if ($userType != "Driver") {
            $returnArr['message'] = getPassengerDetailInfo($iMemberId, "");
        } else {
            $returnArr['message'] = getDriverDetailInfo($iMemberId);
        }
    } else {
        $returnArr['Action'] = "0";
        $returnArr['message'] = "LBL_TRY_AGAIN_LATER_TXT";
    }

    setDataResponse($returnArr);
}
###########################################################################
if ($type == "addDestination") {
    //$userId     = isset($_REQUEST["UserId"]) ? $_REQUEST["UserId"] : '';
    $Latitude = isset($_REQUEST["Latitude"]) ? $_REQUEST["Latitude"] : '';
    $Longitude = isset($_REQUEST["Longitude"]) ? $_REQUEST["Longitude"] : '';
    $Address = isset($_REQUEST["Address"]) ? $_REQUEST["Address"] : '';
    $userType = isset($_REQUEST["UserType"]) ? $_REQUEST["UserType"] : '';
    //$iDriverId     = isset($_REQUEST["iDriverId"]) ? $_REQUEST["iDriverId"] : '';
    $iTripId = isset($_REQUEST["TripId"]) ? $_REQUEST["TripId"] : '';
    $eConfirmByUser = isset($_REQUEST['eConfirmByUser']) ? $_REQUEST['eConfirmByUser'] : 'No';
    $eTollConfirmByUser = isset($_REQUEST['eTollConfirmByUser']) ? $_REQUEST['eTollConfirmByUser'] : 'No';
    $iMemberId = isset($_REQUEST["iMemberId"]) ? $_REQUEST["iMemberId"] : '';
    $UserType = isset($_REQUEST["UserType"]) ? $_REQUEST["UserType"] : 'Passenger';
    $fTollPrice = isset($_REQUEST["fTollPrice"]) ? $_REQUEST["fTollPrice"] : '';
    $vTollPriceCurrencyCode = isset($_REQUEST["vTollPriceCurrencyCode"]) ? $_REQUEST["vTollPriceCurrencyCode"] : '';
    $eTollSkipped = isset($_REQUEST["eTollSkipped"]) ? $_REQUEST["eTollSkipped"] : 'Yes';
    // payment flow 2 changes
    $eWalletIgnore = isset($_REQUEST["eWalletIgnore"]) ? $_REQUEST["eWalletIgnore"] : 'No';
    $vDistance = isset($_REQUEST["vDistance"]) ? $_REQUEST["vDistance"] : '0';
    $vDuration = isset($_REQUEST["vDuration"]) ? $_REQUEST["vDuration"] : '0';

    $vDuration = empty($vDuration) ? 0 : $vDuration;
    $vDistance = empty($vDistance) ? 0 : $vDistance;

    $vDuration = round(($vDuration / 60), 2);
    $vDistance = round(($vDistance / 1000), 2);

    if ($eConfirmByUser == "" || $eConfirmByUser == NULL) {
        $eConfirmByUser = "No";
    }
    if ($eWalletIgnore == "" || $eWalletIgnore == NULL) {
        $eWalletIgnore = "No";
    }
    if ($eTollConfirmByUser == "" || $eTollConfirmByUser == NULL) {
        $eTollConfirmByUser = "No";
    }
    if ($UserType == "Passenger") {
        $tblname = "register_user";
        $iUserId = "iUserId";
        $vCurrency = "vCurrencyPassenger";
        $sqlp = "SELECT ru.vCurrencyPassenger,ru.vLang,cu.vSymbol,cu.Ratio FROM register_user as ru LEFT JOIN currency as cu ON ru.vCurrencyPassenger = cu.vName WHERE iUserId = '" . $iMemberId . "'";
        $passengerData = $obj->MySQLSelect($sqlp);
        $currencycode = $passengerData[0]['vCurrencyPassenger'];
        $currencySymbol = $passengerData[0]['vSymbol'];
        $priceRatio = $passengerData[0]['Ratio'];
        $vLangCode = $passengerData[0]['vLang'];
    } else {
        $tblname = "register_driver";
        $iUserId = "iDriverId";
        $vCurrency = "vCurrencyDriver";
        $sqld = "SELECT rd.vCurrencyDriver,rd.vLang,cu.vSymbol,cu.Ratio FROM register_driver as rd LEFT JOIN currency as cu ON rd.vCurrencyDriver = cu.vName WHERE iDriverId = '" . $iMemberId . "'";
        $driverData = $obj->MySQLSelect($sqld);
        $currencycode = $driverData[0]['vCurrencyDriver'];
        $currencySymbol = $driverData[0]['vSymbol'];
        $priceRatio = $driverData[0]['Ratio'];
        $vLangCode = $driverData[0]['vLang'];
    }
    if ($currencycode == "" || $currencycode == NULL) {
        $sql = "SELECT vName,vSymbol,Ratio from currency WHERE eDefault = 'Yes'";
        $currencyData = $obj->MySQLSelect($sql);
        $currencycode = $currencyData[0]['vName'];
        $currencySymbol = $currencyData[0]['vSymbol'];
        $priceRatio = $currencyData[0]['Ratio'];
    }

    if ($vLangCode == "" || $vLangCode == NULL) {
        $vLangCode = get_value('language_master', 'vCode', 'eDefault', 'Yes', '', 'true');
    }
    $userLanguageLabelsArr = getLanguageLabelsArr($vLangCode, "1", $iServiceId);

    /* $sql = "SELECT * FROM `language_label` WHERE vLabel = 'LBL_AT_TXT' AND vCode = '" . $vLangCode . "'";
      $db_label = $obj->MySQLSelect($sql); */
    $LBL_AT_TXT = $userLanguageLabelsArr['LBL_AT_TXT'];
    $dropofflocationarr = array(
        $Latitude,
        $Longitude
    );
    $ChangeAddress = "No";
    // changed for rental
    $sql_trip = "SELECT iUserId,iDriverId,tStartLat,tStartLong,tEndLat as TripEndLat,tEndLong as TripEndLong, fPickUpPrice, fNightPrice, iVehicleTypeId, iRentalPackageId, eType, vCouponCode, eWalletDebitAllow, ePayWallet, vTripPaymentMode from trips WHERE iTripId='" . $iTripId . "'";
    $data_trip = $obj->MySQLSelect($sql_trip);
    $userId = $data_trip[0]['iUserId'];
    $iDriverId = $data_trip[0]['iDriverId'];
    $TripEndLat = $data_trip[0]['TripEndLat'];
    $TripEndLong = $data_trip[0]['TripEndLong'];
    $tStartLat = $data_trip[0]['tStartLat'];
    $tStartLong = $data_trip[0]['tStartLong'];
    $fPickUpPrice = $data_trip[0]['fPickUpPrice'];
    $fNightPrice = $data_trip[0]['fNightPrice'];
    $iVehicleTypeId = $data_trip[0]['iVehicleTypeId'];
    $eType = $data_trip[0]['eType'];
    $promoCode = $data_trip[0]['vCouponCode'];
    $eWalletDebitAllow = $data_trip[0]['eWalletDebitAllow'];
    $ePayWallet = $data_trip[0]['ePayWallet'];
    $vTripPaymentMode = $data_trip[0]['vTripPaymentMode'];
    /* changed for rental */
    $iRentalPackageId = $data_trip[0]['iRentalPackageId'];
    if ($TripEndLat != "" && $TripEndLong != "") {
        $ChangeAddress = "Yes";
    }
    $allowed_ans = checkAllowedAreaNew($dropofflocationarr, "Yes");
    if ($allowed_ans == "No") {
        $returnArr['Action'] = "0";
        $returnArr['message'] = "LBL_DROP_LOCATION_NOT_ALLOW";
        setDataResponse($returnArr);
    }

    if ($userType != "Driver") {
        //$sql = "SELECT ru.iTripId,tr.iDriverId,rd.vTripStatus as driverStatus,rd.iGcmRegId as regId,rd.eDeviceType as deviceType FROM register_user as ru,trips as tr,register_driver as rd WHERE ru.iUserId='$userId' AND tr.iTripId=ru.iTripId AND rd.iDriverId=tr.iDriverId";
        $sql = "SELECT rd.vTripStatus as driverStatus,rd.iGcmRegId as regId,rd.eDeviceType as deviceType,rd.vLatitude as tDriverLatitude,rd.vLongitude as tDriverLongitude FROM register_driver as rd WHERE rd.iDriverId='" . $iDriverId . "'";
    } else {
        //$sql = "SELECT rd.iTripId,rd.vTripStatus as driverStatus,ru.iGcmRegId as regId,ru.eDeviceType as deviceType FROM trips as tr,register_driver as rd ,register_user as ru WHERE ru.iUserId='$userId' AND rd.iDriverId='$iDriverId'";
        $sql = "SELECT rd.vTripStatus as driverStatus,ru.iGcmRegId as regId,ru.eDeviceType as deviceType,rd.vLatitude as tDriverLatitude,rd.vLongitude as tDriverLongitude FROM register_driver as rd ,register_user as ru WHERE ru.iUserId='$userId' AND rd.iDriverId='$iDriverId'";
    }
    $data = $obj->MySQLSelect($sql);
    if (count($data) > 0) {
        $driverStatus = $data[0]['driverStatus'];
        ######### Checking For Flattrip #########
        $sourceLocationArr = array(
            $tStartLat,
            $tStartLong
        );
        $destinationLocationArr = array(
            $Latitude,
            $Longitude
        );
        $eFlatTrip = "No";
        $fFlatTripPrice = 0;

        if (strtoupper(PACKAGE_TYPE) != "STANDARD") {
            $data_flattrip = checkFlatTripnew($sourceLocationArr, $destinationLocationArr, $iVehicleTypeId, $iRentalPackageId);
            $eFlatTrip = $data_flattrip['eFlatTrip'];
            $fFlatTripPrice = $data_flattrip['Flatfare'];
        }

        if ($eFlatTrip == "Yes") {
            // Changed for rental
            $data_surgePrice = checkSurgePrice($iVehicleTypeId, "", $iRentalPackageId);
            $SurgePriceValue = 1;
            $SurgePrice = "";
            if ($data_surgePrice['Action'] == "0") {
                if ($data_surgePrice['message'] == "LBL_PICK_SURGE_NOTE") {
                    $fPickUpPrice = $data_surgePrice['SurgePriceValue'];
                } else {
                    $fNightPrice = $data_surgePrice['SurgePriceValue'];
                }
                $SurgePriceValue = $data_surgePrice['SurgePriceValue'];
                $SurgePrice = $data_surgePrice['SurgePrice'];
            }
            if ($APPLY_SURGE_ON_FLAT_FARE == "No" && $data_flattrip['eFlatTrip'] == "Yes") {
                $fPickUpPrice = 1;
                $fNightPrice = 1;
                $SurgePriceValue = 1;
                $SurgePrice = "";
            }
            if ($eConfirmByUser == "No" && $eFlatTrip == "Yes") {
                $TripPrice = round($fFlatTripPrice * $priceRatio, 2);
                $fSurgePriceDiff = round(($TripPrice * $SurgePriceValue) - $TripPrice, 2);
                $TripPrice = $TripPrice + $fSurgePriceDiff;
                $returnArr['Action'] = "0";
                $returnArr['message'] = "Yes";
                $returnArr['eFlatTrip'] = $eFlatTrip;
                $returnArr['SurgePrice'] = ""; // $SurgePrice
                $returnArr['SurgePriceValue'] = ""; // $SurgePriceValue
                $returnArr['fFlatTripPrice'] = $TripPrice;
                if ($SurgePriceValue > 1) {
                    $returnArr['fFlatTripPricewithsymbol'] = $currencySymbol . " " . $TripPrice . " (" . $LBL_AT_TXT . " " . $SurgePrice . ")";
                } else {
                    $returnArr['fFlatTripPricewithsymbol'] = $currencySymbol . " " . $TripPrice;
                }
                setDataResponse($returnArr);
            }

            $Data_trips['fTollPrice'] = "0";
            $Data_trips['vTollPriceCurrencyCode'] = "";
            $Data_trips['eTollSkipped'] = "No";
        } else {
            $eFlatTrip = "No";
            $fFlatTripPrice = 0;
            ######### Checking For TollPrice #########
            if ($eTollSkipped == 'No' || ($fTollPrice != "" && $fTollPrice > 0)) {
                $fTollPrice_Original = $fTollPrice;

                $vTollPriceCurrencyCode = strtoupper($vTollPriceCurrencyCode);

                $default_currency = get_value('currency', 'vName', 'eDefault', 'Yes', '', 'true');
                $sql = " SELECT round(($fTollPrice/(SELECT Ratio FROM currency where vName='" . $vTollPriceCurrencyCode . "'))*(SELECT Ratio FROM currency where vName='" . $default_currency . "' ) ,2)  as price FROM currency  limit 1";
                $result_toll = $obj->MySQLSelect($sql);

                $fTollPrice = $result_toll[0]['price'];
                if ($fTollPrice == 0) {
                    $fTollPrice = get_currency($vTollPriceCurrencyCode, $default_currency, $fTollPrice_Original);
                }

                $Data_trips['fTollPrice'] = $fTollPrice;
                $Data_trips['vTollPriceCurrencyCode'] = $vTollPriceCurrencyCode;
                $Data_trips['eTollSkipped'] = $eTollSkipped;
                if ($eTollConfirmByUser == "No" && $fTollPrice > 0) {
                    $returnArr['Action'] = "0";
                    $returnArr['eTollExist'] = "Yes";
                    setDataResponse($returnArr);
                }
            } else {
                $Data_trips['fTollPrice'] = "0";
                $Data_trips['vTollPriceCurrencyCode'] = "";
                $Data_trips['eTollSkipped'] = "No";
            }
            ######### Checking For TollPrice #########
        }
        ######### Checking For Flattrip #########

        /*         * ******* check wallet balance when System Payment flow method-2/method-3 ******** */
        if ($userType != "Driver" && ($SYSTEM_PAYMENT_FLOW == "Method-2" || $SYSTEM_PAYMENT_FLOW == 'Method-3') && $eWalletDebitAllow == "Yes" && $ePayWallet == "Yes" && $vTripPaymentMode == "Card" && $eWalletIgnore == "No") {
            $Fare_data_New = calculateFareEstimateAll($vDuration, $vDistance, $iVehicleTypeId, $iMemberId, 1, "", "", $promoCode, 1, 0, 0, 0, "DisplySingleVehicleFare", "Passenger", 1, "", "Yes", $data_flattrip['eFlatTrip'], $data_flattrip["Flatfare"], $sourceLocationArr, $destinationLocationArr, "Yes", $eType);

            $fareAmount = $Fare_data_New[0]['total_fare_amount'];

            if (!empty($Data_trips['fTollPrice']) && $Data_trips['fTollPrice'] > 0 && !empty($Data_trips['eTollSkipped']) && strtoupper($Data_trips['eTollSkipped']) == "NO") {
                $fareAmount = $fareAmount + $Data_trips['fTollPrice'];
            }


            $user_available_balance_wallet = $generalobj->get_user_available_balance($iMemberId, "Rider", true);

            $walletDataArr = array();

            if (is_array($user_available_balance_wallet)) {
                $walletDataArr = $user_available_balance_wallet;
                $user_available_balance_wallet = $walletDataArr['CurrentBalance'];
            }
            $user_available_balance_wallet = $user_available_balance_wallet * $priceRatio;

            if ($user_available_balance_wallet < $fareAmount) {
                $returnArr['Action'] = "0";
                $returnArr['message'] = "LOW_WALLET_AMOUNT";

                if (!empty($walletDataArr) && count($walletDataArr) > 0) {
                    $auth_wallet_amount = strval((isset($walletDataArr['TotalAuthorizedAmount']) ? $walletDataArr['TotalAuthorizedAmount'] : 0) * $ratio);
                    $returnArr['AUTH_AMOUNT'] = $auth_wallet_amount > 0 ? ($currency_vSymbol . ' ' . $auth_wallet_amount) : "";
                    $returnArr['AUTH_AMOUNT_VALUE'] = $auth_wallet_amount > 0 ? $auth_wallet_amount : "";
                    $returnArr['ORIGINAL_WALLET_BALANCE'] = $currency_vSymbol . ' ' . strval((isset($walletDataArr['WalletBalance']) ? $walletDataArr['WalletBalance'] : 0) * $ratio);
                    $returnArr['ORIGINAL_WALLET_BALANCE_VALUE'] = strval((isset($walletDataArr['WalletBalance']) ? $walletDataArr['WalletBalance'] : 0) * $ratio);
                }
                $returnArr['CURRENT_JOB_EST_CHARGE'] = $currency_vSymbol . ' ' . strval($fareAmount);
                $returnArr['CURRENT_JOB_EST_CHARGE_VALUE'] = strval($fareAmount);
                $returnArr['WALLET_AMOUNT_NEEDED'] = $currency_vSymbol . ' ' . strval($fareAmount - $user_available_balance_wallet);
                $returnArr['WALLET_AMOUNT_NEEDED_VALUE'] = strval($fareAmount - $user_available_balance_wallet);

                if (!empty($walletDataArr) && count($walletDataArr) > 0 && $auth_wallet_amount > 0) {
                    $content_msg_low_balance = $userLanguageLabelsArr['LBL_LOW_WALLET_BAL_NOTE_WITH_AUTH_AMT'];
                    $content_msg_low_balance = str_replace("#####", $returnArr['WALLET_AMOUNT_NEEDED'], $content_msg_low_balance);
                    if (!empty($returnArr['ORIGINAL_WALLET_BALANCE'])) {
                        $content_msg_low_balance = str_replace("####", $returnArr['ORIGINAL_WALLET_BALANCE'], $content_msg_low_balance);
                    }
                    if (!empty($returnArr['AUTH_AMOUNT'])) {
                        $content_msg_low_balance = str_replace("###", $returnArr['AUTH_AMOUNT'], $content_msg_low_balance);
                    }
                    $content_msg_low_balance = str_replace("##", "\n\n", $content_msg_low_balance);
                    $returnArr['low_balance_content_msg'] = $content_msg_low_balance;
                } else {
                    $content_msg_low_balance = $userLanguageLabelsArr['LBL_LOW_WALLET_BAL_NOTE_WITH_AMT'];
                    $content_msg_low_balance = str_replace("#####", $returnArr['WALLET_AMOUNT_NEEDED'], $content_msg_low_balance);
                    if (!empty($returnArr['ORIGINAL_WALLET_BALANCE'])) {
                        $content_msg_low_balance = str_replace("####", $returnArr['ORIGINAL_WALLET_BALANCE'], $content_msg_low_balance);
                    }
                    if (!empty($returnArr['CURRENT_JOB_EST_CHARGE'])) {
                        $content_msg_low_balance = str_replace("###", $returnArr['CURRENT_JOB_EST_CHARGE'], $content_msg_low_balance);
                    }

                    $content_msg_low_balance = str_replace("##", "\n\n", $content_msg_low_balance);
                    $returnArr['low_balance_content_msg'] = $content_msg_low_balance;
                }

                if ($SYSTEM_PAYMENT_FLOW == 'Method-3') {
                    $returnArr['IS_RESTRICT_TO_WALLET_AMOUNT'] = "Yes";
                } else {
                    $returnArr['IS_RESTRICT_TO_WALLET_AMOUNT'] = "No";
                }

                setDataResponse($returnArr);
            }
        }
        /*         * ******* check wallet balance when System Payment flow method-2/method-3 ******** */

        $where_trip = " iTripId = '" . $iTripId . "'";
        $Data_trips['tEndLat'] = $Latitude;
        $Data_trips['tEndLong'] = $Longitude;
        $Data_trips['tDaddress'] = $Address;
        $Data_trips['eFlatTrip'] = $eFlatTrip;
        $Data_trips['fFlatTripPrice'] = $fFlatTripPrice;
        $Data_trips['fPickUpPrice'] = $fPickUpPrice;
        $Data_trips['fNightPrice'] = $fNightPrice;
        $id = $obj->MySQLQueryPerform("trips", $Data_trips, 'update', $where_trip);
        ## Insert Into trip Destination ###
        $Data_trip_destination['iTripId'] = $iTripId;
        $Data_trip_destination['tDaddress'] = $Address;
        $Data_trip_destination['tEndLat'] = $Latitude;
        $Data_trip_destination['tEndLong'] = $Longitude;
        $Data_trip_destination['tDriverLatitude'] = $data[0]['tDriverLatitude'];
        $Data_trip_destination['tDriverLongitude'] = $data[0]['tDriverLongitude'];
        $Data_trip_destination['eUserType'] = $userType;
        $Data_trip_destination['dAddedDate'] = @date("Y-m-d H:i:s");
        $Data_trip_destination_id = $obj->MySQLQueryPerform('trip_destinations', $Data_trip_destination, 'insert');
        ## Insert Into trip Destination ###
        if ($driverStatus == "Active") {
            $where_passenger = " iUserId = '$userId'";
            $Data_passenger['tDestinationLatitude'] = $Latitude;
            $Data_passenger['tDestinationLongitude'] = $Longitude;
            $Data_passenger['tDestinationAddress'] = $Address;
            $id = $obj->MySQLQueryPerform("register_user", $Data_passenger, 'update', $where_passenger);
        } else {
            /* For PubNub Setting */
            $tableName = $userType != "Driver" ? "register_driver" : "register_user";
            $iMemberId_VALUE = $userType != "Driver" ? $iDriverId : $userId;
            $iMemberId_KEY = $userType != "Driver" ? "iDriverId" : "iUserId";
            /* $iAppVersion=get_value($tableName, 'iAppVersion', $iMemberId_KEY,$iMemberId_VALUE,'','true');
              $eDeviceType=get_value($tableName, 'eDeviceType', $iMemberId_KEY,$iMemberId_VALUE,'','true'); */
            $AppData = get_value($tableName, 'iAppVersion,eDeviceType,vLang,tSessionId', $iMemberId_KEY, $iMemberId_VALUE);
            $iAppVersion = $AppData[0]['iAppVersion'];
            $eDeviceType = $AppData[0]['eDeviceType'];
            $tSessionId = $AppData[0]['tSessionId'];
            /* For PubNub Setting Finished */
            //$vLangCode=get_value($tableName, 'vLang', $iMemberId_KEY,$iMemberId_VALUE,'','true');
            $vLangCode = $AppData[0]['vLang'];
            if ($vLangCode == "" || $vLangCode == NULL) {
                $vLangCode = get_value('language_master', 'vCode', 'eDefault', 'Yes', '', 'true');
            }
            $languageLabelsArr = getLanguageLabelsArr($vLangCode, "1", $iServiceId);
            if ($ChangeAddress == "No") {
                $lblValue = $userType == "Driver" ? "LBL_DEST_ADD_BY_DRIVER" : "LBL_DEST_ADD_BY_PASSENGER";
            } else {
                $lblValue = $userType == "Driver" ? "LBL_DEST_EDIT_BY_DRIVER" : "LBL_DEST_EDIT_BY_PASSENGER";
            }
            $alertMsg = $languageLabelsArr[$lblValue];
            $message = "DestinationAdded";
            $message_arr = array();
            $message_arr['Message'] = $message;
            $message_arr['DLatitude'] = $Latitude;
            $message_arr['DLongitude'] = $Longitude;
            $message_arr['DAddress'] = $Address;
            $message_arr['vTitle'] = $alertMsg;
            $message_arr['iTripId'] = $iTripId;
            $message_arr['iDriverId'] = $iDriverId;
            $message_arr['eType'] = $APP_TYPE;
            $message_arr['eFlatTrip'] = $eFlatTrip;
            $message_arr['time'] = strval(time());
            $message_arr['eSystem'] = "";
            $message = json_encode($message_arr);
            $alertSendAllowed = true;
            #####################Add Status Message#########################
            $DataTripMessages['tMessage'] = $message;
            $DataTripMessages['iDriverId'] = $iDriverId;
            $DataTripMessages['iTripId'] = $iTripId;
            $DataTripMessages['iUserId'] = $userId;
            if ($userType != "Driver") {
                $DataTripMessages['eFromUserType'] = "Passenger";
                $DataTripMessages['eToUserType'] = "Driver";
            } else {
                $DataTripMessages['eFromUserType'] = "Driver";
                $DataTripMessages['eToUserType'] = "Passenger";
            }
            $DataTripMessages['eReceived'] = "No";
            $DataTripMessages['dAddedDate'] = @date("Y-m-d H:i:s");
            $obj->MySQLQueryPerform("trip_status_messages", $DataTripMessages, 'insert');
            ################################################################
            $deviceTokens_arr_ios = array();
            $registation_ids_new = array();
            if ($alertSendAllowed == true) {
                if ($data[0]['deviceType'] == "Android") {
                    array_push($registation_ids_new, $data[0]['regId']);
                    $Rmessage = array(
                        "message" => $message
                    );
                    $result = send_notification($registation_ids_new, $Rmessage, 0);
                } else if ($data[0]['deviceType'] != "Android") {
                    array_push($deviceTokens_arr_ios, $data[0]['regId']);
                    if ($message != "") {
                        if ($userType == "Driver") {
                            sendApplePushNotification(0, $deviceTokens_arr_ios, $message, $alertMsg, 0);
                        } else {
                            sendApplePushNotification(1, $deviceTokens_arr_ios, $message, $alertMsg, 0);
                        }
                    }
                }
            }
            ##### Pubnub Notification ######
            $channelName = "PASSENGER_" . $userId;
            if ($userType != "Driver") {
                $channelName = "DRIVER_" . $iDriverId;
                //$tSessionId=get_value("register_driver", 'tSessionId', "iDriverId",$iDriverId,'','true');
            }
            $message_arr['tSessionId'] = $tSessionId;
            $message_pub = json_encode($message_arr, JSON_UNESCAPED_UNICODE);
            if (count($deviceTokens_arr_ios) > 0) {
                sleep(5);
            }

            publishEventMessage($channelName, $message_pub);
            //}
            ##### Pubnub Notification ######
        }
        $returnArr['Action'] = "1";
    } else {
        $returnArr['Action'] = "0";
        $returnArr['message'] = "LBL_TRY_AGAIN_LATER_TXT";
    }
    setDataResponse($returnArr);
}

###########################################################################
if ($type == "checkFlatTrip") {
    $Source_point_Address = isset($_REQUEST["sAddress"]) ? $_REQUEST["sAddress"] : '';
    $Dest_point_Address = isset($_REQUEST["dAddress"]) ? $_REQUEST["dAddress"] : '';
    $iUserId = isset($_REQUEST["iUserId"]) ? $_REQUEST["iUserId"] : '';
    $iVehicleTypeId = isset($_REQUEST["iVehicleTypeId"]) ? $_REQUEST["iVehicleTypeId"] : '';

    $vCurrencyPassenger = get_value('register_user', 'vCurrencyPassenger', 'iUserId', $iUserId, '', 'true');
    $priceRatio = get_value('currency', 'Ratio', 'vName', $vCurrencyPassenger, '', 'true');
    $currencySymbol = get_value('currency', 'vSymbol', 'vName', $vCurrencyPassenger, '', 'true');
    $sourceLocation = isset($_REQUEST["sourceLocation"]) ? $_REQUEST["sourceLocation"] : '';
    $destinationLocation = isset($_REQUEST["destinationLocation"]) ? $_REQUEST["destinationLocation"] : '';
    $Source_point_AddressArr = explode(",", $sourceLocation);
    $Dest_point_AddressArr = explode(",", $destinationLocation);

    $returnArr['eFlatTrip'] = "No";
    $returnArr['Flatfare'] = 0;

    $eFlatTrip = "No";
    $fFlatTripPrice = 0;

    if (strtoupper(PACKAGE_TYPE) != "STANDARD") {
        $data_flattrip = checkFlatTripnew($Source_point_AddressArr, $Dest_point_AddressArr, $iVehicleTypeId);
        $eFlatTrip = $data_flattrip['eFlatTrip'];
        $fFlatTripPrice = $data_flattrip['Flatfare'];
    }

    $returnArr['eFlatTrip'] = $eFlatTrip;
    $returnArr['Flatfare'] = $fFlatTripPrice;
    $returnArr['passenger_price'] = $currencySymbol . " " . number_format(($fFlatTripPrice * $priceRatio), 2);

    setDataResponse($returnArr);
}
?>
