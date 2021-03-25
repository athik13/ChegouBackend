<?php
###################### DriverSubscription start by SP ########################

if ($type == "getDriverSubscriptionHistory") { //returns all plans of the driver
    $iDriverId = isset($_REQUEST['iDriverId']) ? trim($_REQUEST['iDriverId']) : '';
    
    $tblPlan = 'driver_subscription_plan';
    $tblDetails = 'driver_subscription_details';
    
    $usercursym = get_value('register_driver', 'vSymbol', 'iDriverId', $iDriverId, '', 'true'); //get language code of driver
    if ($usercursym == "" || $usercursym == NULL) {
        $usercursym = get_value('language_master', 'vSymbol', 'eDefault', 'Yes', '', 'true');
    }
    $sqld = "SELECT rd.vCurrencyDriver,rd.vLang,cu.vSymbol FROM register_driver as rd LEFT JOIN currency as cu ON rd.vCurrencyDriver = cu.vName WHERE iDriverId = '" . $iDriverId . "'";
    $driverData = $obj->MySQLSelect($sqld);
    $userlangcode = $driverData[0]['vLang'];
    $currencySymbol = $driverData[0]['vSymbol'];
    $currencycode = $driverData[0]['vCurrencyDriver'];
    
    if ($userlangcode == "" || $userlangcode == NULL) {
        $userlangcode = get_value('language_master', 'vCode', 'eDefault', 'Yes', '', 'true');  
    }
    if ($currencySymbol == "" || $currencySymbol == NULL) {
        $currencySymbol = get_value('currency', 'vSymbol', 'eDefault', 'Yes', '', 'true');  
    }
    
    $userCurrencyRatio = get_value('currency', 'Ratio', 'vName', $currencycode, '', 'true');
    
    $languageLabelsArr = getLanguageLabelsArr($userlangcode, "1");
    $SubscriptionTxt = $languageLabelsArr['LBL_SUBSCRIPTION_TXT'];
    $DailySubscriptionTxt = $languageLabelsArr['LBL_DAILY_SUBSCRIPTION'];
    $WeeklySubscriptionTxt = $languageLabelsArr['LBL_WEEKLY_SUBSCRIPTION'];
    $MonthlySubscriptionTxt = $languageLabelsArr['LBL_MONTHLY_SUSCRIPTION'];
    $DailyDurTxt = $languageLabelsArr['LBL_DAYS'];
    $WeeklyDurTxt = $languageLabelsArr['LBL_SUB_WEEKS'];
    $MonthlyDurTxt = $languageLabelsArr['LBL_SUB_MONTH'];
    
    $getField = "d.eSubscriptionStatus, d.vPlanName, d.vPlanDescription,d.vPlanPeriod,d.ePlanValidity,CONCAT('$currencySymbol',FORMAT(d.fPrice * $userCurrencyRatio,2)) as fPlanPrice,
            CASE 
                WHEN d.ePlanValidity = 'Daily' THEN '$DailySubscriptionTxt'
                WHEN d.ePlanValidity = 'Weekly' THEN '$WeeklySubscriptionTxt'
                WHEN d.ePlanValidity = 'Monthly' THEN '$MonthlySubscriptionTxt'
                ELSE  '$SubscriptionTxt'
            END AS PlanTypeTitle,
            CASE
                WHEN d.ePlanValidity = 'Daily' THEN CONCAT(d.vPlanPeriod,' $DailyDurTxt')
                WHEN d.ePlanValidity = 'Weekly' THEN CONCAT(d.vPlanPeriod,' $WeeklyDurTxt')
                WHEN d.ePlanValidity = 'Monthly' THEN CONCAT(d.vPlanPeriod,' $MonthlyDurTxt')
                ELSE  CONCAT(p.vPlanPeriod,'$SubscriptionTxt')
            END AS PlanDuration,
            d.tSubscribeDate,d.tExpiryDate,IFNULL(DATEDIFF(d.tExpiryDate,CURDATE()),'0') AS planLeftDays";
    
    $sql = "SELECT $getField FROM $tblDetails d INNER JOIN $tblPlan p ON d.iDriverSubscriptionPlanId = p.iDriverSubscriptionPlanId AND d.iDriverId = $iDriverId ORDER BY d.tSubscribeDate DESC, d.tExpiryDate DESC";
    $getDriverSubscription = $obj->MySQLSelect($sql);
    if(empty($getDriverSubscription)) {
        $returnArr['Action'] = 0;
        $returnArr['message'] = "LBL_NO_SUBSCRIPTION_PLANS";
    } else {
        $returnArr['Action'] = 1;
        $returnArr['message'] = $getDriverSubscription;
    }
    setDataResponse($returnArr);
}
if ($type == "getSubscriptionPlans") { //returns all plans which are addd by admin and also shows the details of driver wheather he will be subscribed or not, if not used plans then it shows N/A
    
    $iDriverId = isset($_REQUEST['iDriverId']) ? trim($_REQUEST['iDriverId']) : '';
    
    $tblPlan = 'driver_subscription_plan';
    $tblDetails = 'driver_subscription_details';
    
    $usercursym = get_value('register_driver', 'vSymbol', 'iDriverId', $iDriverId, '', 'true'); //get language code of driver
    if ($usercursym == "" || $usercursym == NULL) {
        $usercursym = get_value('language_master', 'vSymbol', 'eDefault', 'Yes', '', 'true');
    }
    $sqld = "SELECT rd.vCurrencyDriver,rd.vLang,cu.vSymbol FROM register_driver as rd LEFT JOIN currency as cu ON rd.vCurrencyDriver = cu.vName WHERE iDriverId = '" . $iDriverId . "'";
    $driverData = $obj->MySQLSelect($sqld);
    $userlangcode = $driverData[0]['vLang'];
    $currencySymbol = $driverData[0]['vSymbol'];
    $currencycode = $driverData[0]['vCurrencyDriver'];
    
    if ($userlangcode == "" || $userlangcode == NULL) {
        $userlangcode = get_value('language_master', 'vCode', 'eDefault', 'Yes', '', 'true');  
    }
    $userdefaultlangcode = get_value('language_master', 'vCode', 'eDefault', 'Yes', '', 'true');  
    if ($currencySymbol == "" || $currencySymbol == NULL) {
        $currencySymbol = get_value('currency', 'vSymbol', 'eDefault', 'Yes', '', 'true');  
    }
    
    $userCurrencyRatio = get_value('currency', 'Ratio', 'vName', $currencycode, '', 'true');
    
    $languageLabelsArr = getLanguageLabelsArr($userlangcode, "1");
    $SubscriptionTxt = $languageLabelsArr['LBL_SUBSCRIPTION_TXT'];
    $DailySubscriptionTxt = $languageLabelsArr['LBL_DAILY_SUBSCRIPTION'];
    $WeeklySubscriptionTxt = $languageLabelsArr['LBL_WEEKLY_SUBSCRIPTION'];
    $MonthlySubscriptionTxt = $languageLabelsArr['LBL_MONTHLY_SUSCRIPTION'];
    $DailyDurTxt = $languageLabelsArr['LBL_DAYS'];
    $WeeklyDurTxt = $languageLabelsArr['LBL_SUB_WEEKS'];
    $MonthlyDurTxt = $languageLabelsArr['LBL_SUB_MONTH'];
    
    $getField = "p.iDriverSubscriptionPlanId, 
            CASE
                WHEN (p.vPlanName_$userlangcode is null or p.vPlanName_$userlangcode = '') THEN p.vPlanName_$userdefaultlangcode 
                ELSE p.vPlanName_$userlangcode
            END AS vPlanName,
            CASE
                WHEN (p.vPlanDescription_$userlangcode is null or p.vPlanDescription_$userlangcode = '') THEN p.vPlanDescription_$userdefaultlangcode
                ELSE p.vPlanDescription_$userlangcode
            END AS vPlanDescription,
            p.vPlanPeriod AS vPlanPeriod,
            p.ePlanValidity AS PlanType,
            CASE 
                WHEN p.ePlanValidity = 'Daily' THEN '$DailySubscriptionTxt'
                WHEN p.ePlanValidity = 'Weekly' THEN '$WeeklySubscriptionTxt'
                WHEN p.ePlanValidity = 'Monthly' THEN '$MonthlySubscriptionTxt'
                ELSE  'Subscribe'
            END AS PlanTypeTitle,
            CASE
                WHEN p.ePlanValidity = 'Daily' THEN CONCAT(p.vPlanPeriod,' $DailyDurTxt')
                WHEN p.ePlanValidity = 'Weekly' THEN CONCAT(p.vPlanPeriod,' $WeeklyDurTxt')
                WHEN p.ePlanValidity = 'Monthly' THEN CONCAT(p.vPlanPeriod,' $MonthlyDurTxt')
                ELSE  CONCAT(p.vPlanPeriod,'Subscribe')
            END AS PlanDuration,
            CONCAT('$currencySymbol',FORMAT(p.fPrice * $userCurrencyRatio,2)) as fPlanPrice,
            IFNULL(d.eSubscriptionStatus,'Unsubscribed') as eSubscriptionStatus,IFNULL(d.tSubscribeDate,'N/A') as tSubscribeDate,IFNULL(d.tExpiryDate,'N/A') as tExpiryDate,IFNULL(DATEDIFF(d.tExpiryDate,CURDATE()),'0') AS planLeftDays,p.eStatus,
            CASE
                WHEN p.eStatus = 'Inactive' AND d.eSubscriptionStatus = 'Subscribed' THEN 'No'
                WHEN p.eStatus = 'Deleted' AND d.eSubscriptionStatus = 'Subscribed' THEN 'No'
                WHEN d.eSubscriptionStatus = 'Subscribed' THEN 'Yes'
                ELSE 'No'
            END AS isRenew";
    
    //$sql = "SELECT $getField FROM $tblDetails d RIGHT JOIN $tblPlan p ON d.iDriverSubscriptionPlanId = p.iDriverSubscriptionPlanId AND d.iDriverId = $iDriverId AND d.eSubscriptionStatus = 'Subscribed' WHERE p.eStatus != 'Inactive' ORDER BY p.iDriverSubscriptionPlanId ASC";
    $sql = "SELECT $getField FROM $tblDetails d RIGHT JOIN $tblPlan p ON d.iDriverSubscriptionPlanId = p.iDriverSubscriptionPlanId AND d.iDriverId = $iDriverId AND d.eSubscriptionStatus = 'Subscribed' WHERE ((p.eStatus != 'Deleted' || (p.eStatus = 'Deleted' && d.eSubscriptionStatus = 'Subscribed')) && (p.eStatus != 'Inactive' || (p.eStatus = 'Inactive' && d.eSubscriptionStatus = 'Subscribed'))) ORDER BY p.iDriverSubscriptionPlanId ASC";
   
    //$sql = "SELECT $getField FROM $tblDetails d ,$tblPlan p ON d.iDriverSubscriptionPlanId = p.iDriverSubscriptionPlanId AND d.iDriverId = $iDriverId AND d.eSubscriptionStatus != 'Inactive'";
    $getDriverSubscription = $obj->MySQLSelect($sql);
    
    if(empty($getDriverSubscription)) {
        $returnArr['Action'] = 0;
        $returnArr['message'] = "LBL_NO_SUBSCRIPTION_PLANS";
    } else {
        $returnArr['Action'] = 1;
        $returnArr['message'] = $getDriverSubscription;
    }
    setDataResponse($returnArr);
}
if($type=='SubscribePlan') { 
    $iDriverId = isset($_REQUEST['iDriverId']) ? trim($_REQUEST['iDriverId']) : trim($_REQUEST['iUserId']);
    $isCard = isset($_REQUEST['isCard']) ? trim($_REQUEST['isCard']) : 'No';
    $isWallet = isset($_REQUEST['isWallet']) ? trim($_REQUEST['isWallet']) : 'No';
    $iDriverSubscriptionPlanId = isset($_REQUEST['iDriverSubscriptionPlanId']) ? trim($_REQUEST['iDriverSubscriptionPlanId']) : trim($_REQUEST['iOrderId']);
    $payStatus = isset($_REQUEST["payStatus"]) ? $_REQUEST["payStatus"] : '';
    $vPayMethod = isset($_REQUEST["vPayMethod"]) ? $_REQUEST["vPayMethod"] : '';
    $CheckUserWallet = isset($_REQUEST["CheckUserWallet"]) ? $_REQUEST["CheckUserWallet"] : 'No';
    $isUpgrade = isset($_REQUEST["isUpgrade"]) ? $_REQUEST["isUpgrade"] : '';
    
    if ($payStatus != "succeeded" && $payStatus != "") {
        $payStatus = "Failed";
    }

    $tblDetails = 'driver_subscription_details';
    $tblPlan = 'driver_subscription_plan';
    $flagerr = 1;

    $date = date("Y-m-d H:i:s");
    
    if(empty($isUpgrade)) { //it is used to display alert whether he want to upgrade the existing plan or not
        $selUpgraded = "SELECT count(*) as cntrec FROM $tblDetails WHERE iDriverId = $iDriverId AND eSubscriptionStatus = 'Subscribed'";
        $dbUpgraded = $obj->MySQLSelect($selUpgraded);
        if($dbUpgraded[0]['cntrec']>0) {
            $returnArr['Action'] = 1;
            $returnArr['message'] = "LBL_UPGRADE_PLANS";
            $returnArr['isUpgrade'] = 'Yes';
            setDataResponse($returnArr);
        }
    }
    
    $sqld = "SELECT rd.vCurrencyDriver,rd.vLang,cu.vSymbol FROM register_driver as rd LEFT JOIN currency as cu ON rd.vCurrencyDriver = cu.vName WHERE iDriverId = '" . $iDriverId . "'";
    $driverData = $obj->MySQLSelect($sqld);
    $currencycode = $driverData[0]['vCurrencyDriver'];
    $currencySymbol = $driverData[0]['vSymbol'];
    $userlangcode = $driverData[0]['vLang'];
    
    if ($userlangcode == "" || $userlangcode == NULL) {
        $userlangcode = get_value('language_master', 'vCode', 'eDefault', 'Yes', '', 'true');  
    }
    $languageLabelsArr = getLanguageLabelsArr($userlangcode, "1");
     
    if ($currencycode == "" || $currencycode == NULL) {
        $sql = "SELECT vName,vSymbol from currency WHERE eDefault = 'Yes'";
        $currencyData = $obj->MySQLSelect($sql);
        $currencycode = $currencyData[0]['vName'];
        $currencySymbol = $currencyData[0]['vSymbol'];
    }
    
    $WalletSubscriptionTxt = $languageLabelsArr['LBL_WALLET_SUBSRIPTION_PLAN'];
    
    $walletbalance = $cardbalance = $fPrice = get_value($tblPlan, 'fPrice', 'iDriverSubscriptionPlanId', $iDriverSubscriptionPlanId, '', 'true');  
    
    $userCurrencyRatio = get_value('currency', 'Ratio', 'vName', $currencycode, '', 'true');
    //$walletbalance = $cardbalance = $fPrice = round($fPrice / $userCurrencyRatio, 2);
    
    if($fPrice==0 || empty($fPrice)) {
        $returnArr['Action'] = 0;
        $returnArr['message'] = "LBL_NO_SUBSCRIPTION_PLANS";
        setDataResponse($returnArr);
    }
    
    $user_available_balance = $generalobj->get_user_available_balance($iDriverId, "Driver");
    $user_available_balance_cur = $generalobj->get_user_currency_available_balance($iDriverId, "Driver");
    //echo $user_available_balance;
    $cardbalance_cur = $walletbalance_cur = $fPrice_cur = $walletbalance*$userCurrencyRatio;
    //$user_available_balance_cur = $user_available_balance*$userCurrencyRatio;
    //echo "aaa".$walletbalance_cur; exit;
    if($isWallet=='Yes' && $payStatus == "") {

        //if($user_available_balance<$fPrice && $isCard=='No') { 
        //if($user_available_balance<$fPrice && $isCard=='No') { 
        if($user_available_balance_cur<$fPrice_cur && $isCard=='No') { 
            $returnArr['Action'] = 0;
            $returnArr['message'] = "LBL_LOW_WALLET_BAL_NOTE";
            setDataResponse($returnArr);
        }
        //if($user_available_balance_cur>=$fPrice_cur) { 
        //if($user_available_balance>=$fPrice) { 
        if($user_available_balance_cur>=$fPrice_cur) { 
            $insid = $generalobj->InsertIntoUserWallet($iDriverId, 'Driver', $walletbalance, 'Debit', 0, 'Subscription', $WalletSubscriptionTxt, 'Unsettelled', $date);
            if(empty($insid)) {
                $returnArr['Action'] = 0;
                $returnArr['message'] = "LBL_NO_SUBSCRIPTION_PLANS";
                setDataResponse($returnArr);
            } else {
                updateDriverSubscription($iDriverSubscriptionPlanId,$iDriverId,$walletbalance,$insid);
                $returnArr['Action'] = 1;
                $returnArr['message'] = "LBL_SUBSCRIBED_SUCCESFULLY_TXT";
                setDataResponse($returnArr);
            }
        }
    } 
    
    $DefaultCurrencyData = get_value('currency', 'vName,Ratio', 'eDefault', 'Yes');
    $currencyCode = $DefaultCurrencyData[0]['vName'];
    $currencyratio = $DefaultCurrencyData[0]['Ratio'];
        
    if($user_available_balance_cur>0 && $isWallet=='Yes') {
    //if($user_available_balance>0 && $isWallet=='Yes') {
        //$cardbalance = $user_available_balance - $cardbalance;
        //$walletbalance = $user_available_balance;
        $walletbalance_cur = $user_available_balance_cur;
        $walletbalance = round($walletbalance_cur / $userCurrencyRatio,2);
        //$cardbalance_cur = $cardbalance * $userCurrencyRatio;
        $cardbalance_cur = ($fPrice_cur-$user_available_balance_cur);
        $cardbalance = round($cardbalance_cur / $userCurrencyRatio,2);
        //$walletbalance = $cardbalance - $walletbalance;
        //$cardbalance_cur = $cardbalance_cur - $walletbalance_cur;
    } else {
        $walletbalance = 0;
    }
    
    //if($isCard=='Yes' && $cardbalance>0 && $payStatus == "") { 
    if($isCard=='Yes' && $cardbalance_cur>0 && $payStatus == "") { 
        //$walletbalance = $cardbalance = $fPrice = ($fPrice * $userCurrencyRatio);
        //$walletbalance = round($walletbalance * $userCurrencyRatio,2);
        
        //$price_new = round($cardbalance * $currencyratio, 2);
        $price_new = round($cardbalance_cur,2);
        $price_new = $price_new * 100;
        $tDescription = "Amount charge for plan id".$iDriverSubscriptionPlanId;
        $ChargeidArrId = 0;
	/*Added By PM On 09-12-2019 For Flutterwave Code Start*/
        $Squery = "SELECT vStripeToken,vStripeCusId,vCreditCard,vBrainTreeToken,vSenangToken FROM register_driver WHERE iDriverId = '" . $iDriverId . "'";
	/*Added By PM On 09-12-2019 For Flutterwave Code End*/
        $Userdata = $obj->MySQLSelect($Squery);
        $vStripeToken = $Userdata[0]['vStripeToken'];
        $vStripeCusId = $Userdata[0]['vStripeCusId']; 
        $vSenangToken = $Userdata[0]['vSenangToken']; 
        $ePaymentOption = 'Card';
		$IsChargeCustomer ="No";
	  
	/*Added By PM On 09-12-2019 For Flutterwave Code Start*/
		if ($vStripeCusId != "" && $APP_PAYMENT_METHOD=="Stripe") {
		
			$vCreditCard = $Userdata[0]['vCreditCard'];
			$IsChargeCustomer = ($vCreditCard != "") ? "Yes" : "No";
		}
		if ($vSenangToken != "" && $APP_PAYMENT_METHOD=="Senangpay") {		
			$vCreditCard = $Userdata[0]['vCreditCard'];
			$IsChargeCustomer = ($vCreditCard != "") ? "Yes" : "No";
		}
	/*Added By PM On 09-12-2019 For Flutterwave Code End*/
		$CheckUserWallet = ($isWallet == 'Yes') ? "Yes" : "No";
        $vPayMethod = "Instant";
		 
        if ($IsChargeCustomer=='Yes') {
            $Charge_Array = array(
                "iFare" => $cardbalance,
                "price_new" => $price_new,
                "currency" => $currencyCode,
                "vStripeCusId" => $Userdata[0]['vStripeCusId'],
                "description" => $tDescription,
                "iTripId" => 0,
                "eCancelChargeFailed" => "No",
                "vBrainTreeToken" => $Userdata[0]['vBrainTreeToken'],
                "vRideNo" => 0,
                "iMemberId" => $iDriverId,
                "UserType" => "Driver",
                "iOrderId" => $iDriverSubscriptionPlanId,
                "vOrderNo" => 0
            );
            
            $result = ChargeCustomer($Charge_Array, "CollectPayment"); // function for charge customer
            
            $ChargeidArrId = $result['id'];
            $status = $result['status'];
            if ($status == "success") { 
                $where_payments = " iPaymentId = '" . $ChargeidArrId . "'";
                $data_payments['eEvent'] = "SubscribePayment";
                $obj->MySQLQueryPerform("payments", $data_payments, 'update', $where_payments);
            }
        } else { 
            if($SYSTEM_PAYMENT_FLOW != 'Method-1') {
                $REFERRAL_AMOUNT_ARR = getPriceUserCurrency($iDriverId, "Driver", $cardbalance);
                $REFERRAL_AMOUNT_USER = $REFERRAL_AMOUNT_ARR['fPricewithsymbol'];
                $eSystem = isset($_REQUEST["eSystem"]) ? $_REQUEST["eSystem"] : '';
                $themeColor = isset($_REQUEST["AppThemeColor"]) ? $_REQUEST["AppThemeColor"] : '000000';
                $textColor = isset($_REQUEST["AppThemeTxtColor"]) ? $_REQUEST["AppThemeTxtColor"] : 'FFFFFF';
                $GeneralAppVersion = $appVersion;
                $returnUrl = isset($_REQUEST['returnUrl']) ? trim($_REQUEST['returnUrl']) : 'include_webservice_shark.php';
                $extraPara = "&ePaymentOption=" . $ePaymentOption . "&CheckUserWallet=" . $CheckUserWallet . "&eSystem=" . $eSystem . "&vStripeToken=" . $vStripeToken . "&type=" . $type . "&Platform=" . $Platform . "&tSessionId=" . $tSessionId . "&GeneralMemberId=" . $GeneralMemberId . "&GeneralUserType=" . $GeneralUserType . "&GeneralDeviceType=" . $GeneralDeviceType . "&GeneralAppVersion=" . $GeneralAppVersion . "&vTimeZone=" . $vTimeZone . "&vUserDeviceCountry=" . $vUserDeviceCountry . "&iServiceId=" . $iServiceId . "&vCurrentTime=" . $vCurrentTime . "&returnUrl=" . $returnUrl . "&vPayMethod=" . $vPayMethod . "&AppThemeColor=" . $themeColor . "&AppThemeTxtColor=" . $textColor;
                $getWayUrl = $tconfig['tsite_url'] . "assets/libraries/webview/payment_configuration.php?iUserId=" . $iDriverId . "&iOrderId=".$iDriverSubscriptionPlanId . "&amount=" . $price_new . "&ccode=" . $currencyCode . "&userAmount=" . $REFERRAL_AMOUNT_USER . "&vOrderNo=0" . $extraPara;
                $returnArr = array();
                $returnArr['Action'] = "1";
                $returnArr['message'] = $getWayUrl;
                $returnArr['loadWebView'] = 'Yes';
                setDataResponse($returnArr);
            } else {
                $returnArr['Action'] = 0;
                $returnArr['message'] = "LBL_NO_SUBSCRIPTION_PLANS";
                setDataResponse($returnArr);
            }
        }          
    }
    if (isset($result['status']) && $result['status'] == "succeeded" && $result['paid'] == "1" || $status == "success" || $payStatus == "succeeded") { //|| $fNetTotal == 0) {
       
        $iTransactionId = 0;
        $insid = 0;
        if (isset($result) && $result != "") {
            $iTransactionId = $result['id'];
            if ($cardbalance == 0) {
                $iTransactionId = 0;
            }
        }
        ## Insert Into Payment Table ##
        if ($ChargeidArrId == 0) {
				
				if ($APP_PAYMENT_METHOD=="Stripe") {
						$payment_arr['STRIPE_SECRET_KEY'] = $STRIPE_SECRET_KEY;
						$payment_arr['STRIPE_PUBLISH_KEY'] = $STRIPE_PUBLISH_KEY;
					}
				if ($APP_PAYMENT_METHOD=="Flutterwave") {
					$payment_arr['FLUTTERWAVE_SECRET_KEY'] = $FLUTTERWAVE_SECRET_KEY;
					$payment_arr['FLUTTERWAVE_PUBLIC_KEY'] = $FLUTTERWAVE_PUBLIC_KEY;
				}	
					$tPaymentDetails = json_encode($payment_arr, JSON_UNESCAPED_UNICODE);
					$pay_data['tPaymentUserID'] = $iTransactionId;
            $pay_data['vPaymentUserStatus'] = "approved";
            $pay_data['iAmountUser'] = $cardbalance;
            $pay_data['tPaymentDetails'] = $tPaymentDetails;
            $pay_data['iOrderId'] = $iDriverSubscriptionPlanId;
            $pay_data['vPaymentMethod'] = $APP_PAYMENT_METHOD;
            $pay_data['iUserId'] = $iDriverId;
            $pay_data['eUserType'] = "Driver";
            $pay_data['eEvent'] = "SubscribePayment";

            $iTransactionId = $obj->MySQLQueryPerform("payments", $pay_data, 'insert');
        }
        ## Insert Into Payment Table ##
         
        if ($walletbalance > 0 && ($isWallet == "Yes" || $CheckUserWallet == 'Yes')) {
            $insid = $generalobj->InsertIntoUserWallet($iDriverId, 'Driver', $walletbalance, 'Debit', 0, 'Subscription', $WalletSubscriptionTxt, 'Unsettelled', $date);
        }
        updateDriverSubscription($iDriverSubscriptionPlanId,$iDriverId,$fPrice,$insid,$iTransactionId);
    }
    if ($vPayMethod == "Instant") {
        if ($payStatus == "succeeded") {
            $successUrl = $tconfig['tsite_url'] . "assets/libraries/webview/result.php?success=1";
            //header('Location: ' . $successUrl);
            ?>
            <script>window.location.replace("<?php echo $successUrl; ?>");
            </script>
            <?php
        } else if ($payStatus == "Failed") {
            $failedUrl = $tconfig['tsite_url'] . "assets/libraries/webview/result.php?success=0";
            //header('Location: ' . $failedUrl);
            ?> 
            <script>window.location.replace("<?php echo $successUrl; ?>");
            </script>
            <?php
        }
    }
    if($flagerr==0) {
        $returnArr['Action'] = 0;
        $returnArr['message'] = "LBL_NO_SUBSCRIPTION_PLANS";
    } else {
        $returnArr['Action'] = 1;
        $returnArr['message'] = "LBL_SUBSCRIBED_SUCCESFULLY_TXT";
    }
    setDataResponse($returnArr);
}

if($type=='CancelSubscription') {
    $iDriverId = isset($_REQUEST['iDriverId']) ? trim($_REQUEST['iDriverId']) : '';
    $iDriverSubscriptionPlanId = isset($_REQUEST['iDriverSubscriptionPlanId']) ? trim($_REQUEST['iDriverSubscriptionPlanId']) : '';
        
    $tblDetails = 'driver_subscription_details';
    $dataCancelSubscription['tClosedDate'] = date("Y-m-d H:i:s");
    $dataCancelSubscription['eSubscriptionStatus'] = 'Cancelled';

    /*$curdate = date("Y-m-d H:i:s");
    $where_exp = "WHERE iDriverId = '".$iDriverId."' AND iDriverSubscriptionPlanId = '".$iDriverSubscriptionPlanId."'";
    $selExpired = "SELECT iDriverSubscriptionDetailsId,iDriverId,tExpiryDate >= '$curdate' AS tExpiryDate, datediff(tExpiryDate,'$curdate') AS daysRemain,eSubscriptionStatus FROM $tblDetails $where_exp";
    $dataExpired = $obj->MySQLSelect($selExpired);
    if ($dataExpired[0]['tExpiryDate'] <= 0 && $dataExpired[0]['eSubscriptionStatus'] != 'Expired') {
        $where = "iDriverSubscriptionDetailsId = '" . $dataExpired[0]['iDriverSubscriptionDetailsId'] . "'";
        $DataUpdate['eSubscriptionStatus'] = 'Expired';
        $id = $obj->MySQLQueryPerform($tblDetails, $DataUpdate, 'update', $where);
        $returnArr['Action'] = 0;
        $returnArr['message'] = "LBL_NO_SUBSCRIPTION_PLANS";
        setDataResponse($returnArr);
    }*/
    
    $where = "iDriverId = '".$iDriverId."' AND iDriverSubscriptionPlanId = '".$iDriverSubscriptionPlanId."' AND eSubscriptionStatus = 'Subscribed'";
    $id = $obj->MySQLQueryPerform($tblDetails, $dataCancelSubscription, 'update', $where);
    
    if(empty($id)) {
        $returnArr['Action'] = 0;
        $returnArr['message'] = "LBL_ERROR_CANCEL_SUBSCRIPTION";
    } else {
        $driverData = get_value('register_driver', 'vEmail,vName,vLastName', 'iDriverId', $iDriverId);
        $getMaildata = array();
        $getMaildata['vEmail'] = $driverData[0]['vEmail'];
        $getMaildata['FromName'] = $driverData[0]['vName'].' '.$driverData[0]['vLastName'];
        $mail = $generalobj->send_email_user('DRIVER_SUBSCRIPTION_CANCEL',$getMaildata);
        
        $returnArr['Action'] = 1;
        $returnArr['message'] = "LBL_CANCELLED_SUCCESFULLY_TXT";
    }
    setDataResponse($returnArr);
}
function checkDriverSubscribed($iDriverId,$selectedVehicleId=-1) { //its used when driver online call this function to chk whether he is subscribed or not
    global $obj;
    $tblDetails = 'driver_subscription_details';
    $getField = "d.eSubscriptionStatus, p.vPlanName, p.vPlanDescription,p.vPlanPeriod,p.ePlanValidity,p.fPrice";
    $curdate = date("Y-m-d H:i:s");
    $sql = "SELECT count(iDriverSubscriptionPlanId) as cnt FROM $tblDetails WHERE iDriverId = $iDriverId AND (eSubscriptionStatus = 'Subscribed' or (eSubscriptionStatus = 'Cancelled' AND tExpiryDate>= '$curdate'))";
    $getDriverSubscription = $obj->MySQLSelect($sql);
    if($getDriverSubscription[0]['cnt']==0) {
        $etype = '';
        $pendingSubscription = $pendingSubscription_mix = 0;
        $i = 0;

        $sql = "SELECT make.vMake, model.vTitle, dv.*, rd.iDriverVehicleId as iSelectedVehicleId,rd.iDestinationCount,rd.tDestinationModifiedDate,rd.tOnline FROM `driver_vehicle` dv, make, model, register_driver as rd WHERE dv.iDriverId='$iDriverId' AND rd.iDriverId='$iDriverId' AND dv.`iMakeId` = make.`iMakeId` AND dv.`iModelId` = model.`iModelId` AND dv.`eStatus`='Active'";
        $Data_Car = $obj->MySQLSelect($sql);
        
        if(($Data_Car[0]['iSelectedVehicleId']=='' || $Data_Car[0]['iSelectedVehicleId']==0) && $selectedVehicleId==-1) {
            $pendingSubscription = 1;
        } else {
            while (count($Data_Car) > $i) {
                if(($Data_Car[0]['iSelectedVehicleId'] == $Data_Car[$i]['iDriverVehicleId'] && $selectedVehicleId==-1) || $selectedVehicleId!=-1 && $selectedVehicleId == $Data_Car[$i]['iDriverVehicleId'])  {
                    $vCarType = $Data_Car[$i]['vCarType'];    
                    if ($vCarType != "") {
                        $sql = "SELECT iVehicleTypeId,eType  FROM `vehicle_type` WHERE `iVehicleTypeId` IN ($vCarType)";
                        $db_cartype = $obj->MySQLSelect($sql);
                        if (count($db_cartype) > 0) {
                            for ($j = 0; $j < count($db_cartype); $j++) {
                                $eVehicleType = $db_cartype[$j]['eType'];
                                //if ($eVehicleType == "Ride" || $eVehicleType == "Deliver") {  
                                //} else 
                                if ($eVehicleType == "DeliverAll") {
                                    $pendingSubscription_mix = 1;
                                } else {
                                    $pendingSubscription = 1;
                                }
                            }
                        }
                    }
                } 
            $i++;
            }  
        }
        if($pendingSubscription_mix==1 && $pendingSubscription==1) {
            return 1;
        } else if ($pendingSubscription==1) { 
            return 2;
        } else {
            return 0;
        }
    }
}
function checkDriverPlanExpired($iDriverId,$selectedVehicleId=-1) { //When driver is online and his/her plan is expired then only notify to the driver for pending subscription, it is for the configdrivertripstatus type bc it is called 5min interval..
     global $obj;
    $tblDetails = 'driver_subscription_details';
    $getField = "d.eSubscriptionStatus, p.vPlanName, p.vPlanDescription,p.vPlanPeriod,p.ePlanValidity,p.fPrice";
    $sql = "SELECT count(iDriverSubscriptionPlanId) as cnt FROM $tblDetails WHERE iDriverId = $iDriverId AND eSubscriptionStatus = 'Expired'";
    $getDriverSubscription = $obj->MySQLSelect($sql);
    if($getDriverSubscription[0]['cnt']>0) {
        $etype = '';
        $pendingSubscription = $pendingSubscription_mix = 0;
        $i = 0;

        $sql = "SELECT make.vMake, model.vTitle, dv.*, rd.iDriverVehicleId as iSelectedVehicleId,rd.iDestinationCount,rd.tDestinationModifiedDate,rd.tOnline FROM `driver_vehicle` dv, make, model, register_driver as rd WHERE dv.iDriverId='$iDriverId' AND rd.iDriverId='$iDriverId' AND dv.`iMakeId` = make.`iMakeId` AND dv.`iModelId` = model.`iModelId` AND dv.`eStatus`='Active'";
        $Data_Car = $obj->MySQLSelect($sql);
        
        if(($Data_Car[0]['iSelectedVehicleId']=='' || $Data_Car[0]['iSelectedVehicleId']==0) && $selectedVehicleId==-1) {
            $pendingSubscription = 1;
        } else {
            while (count($Data_Car) > $i) {
                if(($Data_Car[0]['iSelectedVehicleId'] == $Data_Car[$i]['iDriverVehicleId'] && $selectedVehicleId==-1) || $selectedVehicleId!=-1 && $selectedVehicleId == $Data_Car[$i]['iDriverVehicleId'])  {
                    $vCarType = $Data_Car[$i]['vCarType'];    
                    if ($vCarType != "") {
                        $sql = "SELECT iVehicleTypeId,eType  FROM `vehicle_type` WHERE `iVehicleTypeId` IN ($vCarType)";
                        $db_cartype = $obj->MySQLSelect($sql);
                        if (count($db_cartype) > 0) {
                            for ($j = 0; $j < count($db_cartype); $j++) {
                                $eVehicleType = $db_cartype[$j]['eType'];
                                //if ($eVehicleType == "Ride" || $eVehicleType == "Deliver") {  
                                //} else 
                                if ($eVehicleType == "DeliverAll") {
                                    $pendingSubscription_mix = 1;
                                } else {
                                    $pendingSubscription = 1;
                                }
                            }
                        }
                    }
                } 
            $i++;
            }  
        }
        
        if($pendingSubscription_mix==1 && $pendingSubscription==1) {
            return 1;
        } else if ($pendingSubscription==1) { 
            return 2;
        } else {
            return 0;
        }
    }
}
function updateDriverSubscription($iDriverSubscriptionPlanId,$iDriverId,$balance,$walletId = 0,$paymentId = 0) {
    global $obj,$generalobj;
   
    $tblDetails = 'driver_subscription_details';
    $tblPlan = 'driver_subscription_plan';
    
    $userlangcode = get_value('register_driver', 'vLang', 'iDriverId', $iDriverId, '', 'true'); //get language code of driver
    
    $selPlan = "SELECT CASE 
                WHEN ePlanValidity = 'Daily' THEN 'Days'
                WHEN ePlanValidity = 'Weekly' THEN 'Weeks'
                WHEN ePlanValidity = 'Monthly' THEN 'Months'
                ELSE  'Days'
            END AS PlanTypeTitle,vPlanPeriod,vPlanName_$userlangcode as vPlanName,ePlanValidity,vPlanDescription_$userlangcode as vPlanDescription FROM $tblPlan WHERE iDriverSubscriptionPlanId = $iDriverSubscriptionPlanId";
    $dbPlans = $obj->MySQLSelect($selPlan);
    $expire = strtotime(date("Y-m-d H:i:s"). ' + '.$dbPlans[0]['vPlanPeriod'].' '.$dbPlans[0]['PlanTypeTitle']);
    $tExpiryDate = date("Y-m-d H:i:s",$expire);
    //it will count current month days like 31,30,28 if 2 months plan march going on then count 31*2 = 62 days..if april is going on then 30*2 = 60 days count
    $selSubDetails = "SELECT (DATEDIFF(tExpiryDate,CURDATE())) as days,iDriverSubscriptionDetailsId FROM $tblDetails WHERE iDriverId = $iDriverId AND eSubscriptionStatus = 'Subscribed'"; 
    $dbSubDetails = $obj->MySQLSelect($selSubDetails);
    if(!empty($dbSubDetails[0]['days'])) { 
        $data_status_inactive['eSubscriptionStatus'] = 'Inactive';
        $data_status_inactive['tClosedDate'] = date("Y-m-d H:i:s");
        $where = "iDriverSubscriptionDetailsId = '".$dbSubDetails[0]['iDriverSubscriptionDetailsId']."'";
        $obj->MySQLQueryPerform("driver_subscription_details", $data_status_inactive, 'update', $where);
        $expire = strtotime($tExpiryDate. ' + '.$dbSubDetails[0]['days'].' Days');
        $tExpiryDate = date("Y-m-d H:i:s",$expire);
    } else {
        $selSubDetailsCancel = "SELECT (DATEDIFF(tExpiryDate,CURDATE())) as days,iDriverSubscriptionDetailsId FROM $tblDetails WHERE iDriverId = $iDriverId AND eSubscriptionStatus = 'Cancelled' order by iDriverSubscriptionDetailsId DESC limit 1";
        $dbSubDetailsCancel = $obj->MySQLSelect($selSubDetailsCancel);
        if(!empty($dbSubDetailsCancel[0]['days'])) {
            $expire = strtotime($tExpiryDate. ' + '.$dbSubDetailsCancel[0]['days'].' Days');
            $tExpiryDate = date("Y-m-d H:i:s",$expire);
        }
    }
    
    
    $selDetails = "SELECT count(*) as cntrec FROM $tblDetails WHERE iDriverId = $iDriverId AND eSubscriptionStatus = 'Unsubscribed'";
    $dbDetails = $obj->MySQLSelect($selDetails);
    
    $data_currency_ratio['iDriverId'] = $iDriverId;
    $data_currency_ratio['iDriverSubscriptionPlanId'] = $iDriverSubscriptionPlanId;
    $data_currency_ratio['vPlanName'] = $dbPlans[0]['vPlanName'];
    $data_currency_ratio['vPlanPeriod'] = $dbPlans[0]['vPlanPeriod'];
    $data_currency_ratio['ePlanValidity'] = $dbPlans[0]['ePlanValidity'];
    $data_currency_ratio['vPlanDescription'] = $dbPlans[0]['vPlanDescription'];
    $data_currency_ratio['tSubscribeDate'] = date("Y-m-d H:i:s");
    $data_currency_ratio['tExpiryDate'] = $tExpiryDate;
    $data_currency_ratio['tClosedDate'] = $tExpiryDate;
    $data_currency_ratio['eSubscriptionStatus'] = 'Subscribed';
    $data_currency_ratio['iWalletId'] = $walletId;
    $data_currency_ratio['iPaymentId'] = $paymentId;
    $data_currency_ratio['fPrice'] = $balance;
    
    if($dbDetails[0]['cntrec']<=0) { 
        $iDriverSubscriptionDetailsId = $obj->MySQLQueryPerform("driver_subscription_details", $data_currency_ratio, 'insert');
    } else {
        $where = " iDriverId = '" . $iDriverId . "' AND eSubscriptionStatus = 'Unsubscribed' AND iDriverSubscriptionPlanId = '".$iDriverSubscriptionPlanId."'";
        $iDriverSubscriptionDetailsId = $obj->MySQLQueryPerform("driver_subscription_details", $data_currency_ratio, 'update',$where);
    }
    if($iDriverSubscriptionDetailsId>0) {
        $sql = "SELECT * FROM currency WHERE eStatus = 'Active'";
        $db_curr = $obj->MySQLSelect($sql);
        $where = " iDriverSubscriptionDetailsId = '" . $iDriverSubscriptionDetailsId . "'";
        for ($i = 0; $i < count($db_curr); $i++) {
            $data_currency_ratio['fRatio_' . $db_curr[$i]['vName']] = $db_curr[$i]['Ratio'];
            $obj->MySQLQueryPerform("driver_subscription_details", $data_currency_ratio, 'update', $where);
        }
        
        $currencycode = get_value('register_driver', 'vCurrencyDriver', 'iDriverId', $iDriverId, '', 'true'); 
        $userDetails = get_value('currency', 'Ratio,vSymbol', 'vName', $currencycode);
        $userCurrencyRatio = $userDetails[0]['Ratio'];
        $userCurrencySymbol = $userDetails[0]['vSymbol'];
    
        $userCurrencyRatio = get_value('currency', 'Ratio', 'vName', $currencycode, '', 'true');
        $balance_new = $balance * $userCurrencyRatio;
        $curbalance = $userCurrencySymbol.' '.$balance_new;
        $balance_new = round($balance_new,2);
        
        $languageLabelsArr = getLanguageLabelsArr($userlangcode, "1");
        $planTxt = $languageLabelsArr['LBL_SUBSCRIPTION_PLAN_NAME'].': '.$dbPlans[0]['vPlanName']."<br>".$languageLabelsArr['LBL_SUBSCRIPTION_PLAN_PRICE'].': '.$curbalance."<br>".$languageLabelsArr['LBL_DRIVER_SUBSCRIBE_DATE'].": ".date("Y-m-d");
        
        $driverData = get_value('register_driver', 'vEmail,vName,vLastName', 'iDriverId', $iDriverId);
        $getMaildata = array();
        $getMaildata['vEmail'] = $driverData[0]['vEmail'];
        $getMaildata['planName'] = $planTxt;
        $getMaildata['FromName'] = $driverData[0]['vName'].' '.$driverData[0]['vLastName'];
        $mail = $generalobj->send_email_user('DRIVER_SUBSCRIPTION_SUCCESS',$getMaildata);
    }
    return 1;
}
if($type=='Mytest') {
    updateDriverSubscription(1,36,4);
}


    
/*  Used to get old details only   
 * 
 * $getField = "p.iDriverSubscriptionPlanId, 
            CASE
                WHEN (d.vPlanName is null or d.vPlanName = '') and p.vPlanName_$userlangcode != '' THEN p.vPlanName_$userlangcode
               WHEN (p.vPlanName_$userlangcode is null or p.vPlanName_$userlangcode = '') THEN p.vPlanName_$userdefaultlangcode 
                ELSE d.vPlanName
            END AS vPlanName,
            CASE
                WHEN (p.vPlanDescription_$userlangcode is null or p.vPlanDescription_$userlangcode = '') THEN p.vPlanDescription_$userdefaultlangcode
                ELSE p.vPlanDescription_$userdefaultlangcode
            END AS vPlanDescription,
            CASE
                WHEN d.vPlanPeriod is null THEN p.vPlanPeriod
                ELSE d.vPlanPeriod
            END AS vPlanPeriod,
            CASE
                WHEN d.ePlanValidity is null THEN p.ePlanValidity
                ELSE d.ePlanValidity
            END AS PlanType,
            CASE 
                WHEN d.ePlanValidity = 'Daily' OR p.ePlanValidity = 'Daily' THEN 'Daily Subscription'
                WHEN d.ePlanValidity = 'Weekly' OR p.ePlanValidity = 'Weekly' THEN 'Weekly Subscription'
                WHEN d.ePlanValidity = 'Monthly' OR p.ePlanValidity = 'Monthly' THEN 'Monthly Subscription'
                ELSE  'Subscribe'
            END AS PlanTypeTitle,
            CASE
                WHEN d.ePlanValidity = 'Daily' OR p.ePlanValidity = 'Daily' THEN CONCAT(IFNULL(d.vPlanPeriod,p.vPlanPeriod),' Day/s')
                WHEN d.ePlanValidity = 'Weekly' OR p.ePlanValidity = 'Weekly' THEN CONCAT(IFNULL(d.vPlanPeriod,p.vPlanPeriod),' Week/s')
                WHEN d.ePlanValidity = 'Monthly' OR p.ePlanValidity = 'Monthly' THEN CONCAT(IFNULL(d.vPlanPeriod,p.vPlanPeriod),' Month/s')
                ELSE  CONCAT(p.vPlanPeriod,'Subscribe')
            END AS PlanDuration,
            CONCAT('$currencySymbol',FORMAT(p.fPrice * $userCurrencyRatio,2)) as fPlanPrice,
            IFNULL(d.eSubscriptionStatus,'Unsubscribed') as eSubscriptionStatus,IFNULL(d.tSubscribeDate,'N/A') as tSubscribeDate,IFNULL(d.tExpiryDate,'N/A') as tExpiryDate,IFNULL(DATEDIFF(d.tExpiryDate,CURDATE()),'0') AS planLeftDays,p.eStatus,
            CASE
                WHEN p.eStatus = 'Inactive' AND d.eSubscriptionStatus = 'Subscribed' THEN 'No'
                WHEN p.eStatus = 'Deleted' AND d.eSubscriptionStatus = 'Subscribed' THEN 'No'
                WHEN d.eSubscriptionStatus = 'Subscribed' THEN 'Yes'
                ELSE 'No'
            END AS isRenew"; */
###################### DriverSubscription end by SP ########################
?>