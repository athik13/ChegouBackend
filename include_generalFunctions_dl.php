<?php

include_once("generalFunctions.php");

/* to clean function */

function orderemaildata($iOrderId, $sendTo) {
    global $tconfig, $generalobj, $obj, $COPYRIGHT_TEXT, $COMPANY_NAME, $EMAIL_FROM_NAME, $NOREPLY_EMAIL;
    ob_start();
    include $tconfig["tpanel_path"] . "orderdetails_mail_format.php";
    $mail_content = ob_get_clean();
    return $mail_content;
}

function sendneworderwebadmin($vOrderNo, $iOrderId) {
    global $tconfig, $generalobj, $obj;
    ob_start();
    include $tconfig["tpanel_path"] . "socket_cluster.php";
    $content = ob_get_clean();
    return $content;
}

function getcuisinelist($CompanyId, $iUserId, $languageLabelsArr = array(), $serviceId = 0) {
    global $obj, $generalobj, $tconfig, $vTimeZone, $iServiceId;
    //ini_set('display_errors', 1);
    //error_reporting(E_ALL);
    //echo $vTimeZone;die;
    $vTimeZone = $_COOKIE['vUserDeviceTimeZone'];
    date_default_timezone_set($vTimeZone);
    $vCurrentTime = @date("Y-m-d H:i:s");
    $day = date('l', strtotime($vCurrentTime));
    $timingArray = array('vMonFromSlot', 'vMonToSlot', 'vTueFromSlot', 'vTueToSlot', 'vWedFromSlot', 'vWedToSlot', 'vThuFromSlot', 'vThuToSlot', 'vFriFromSlot', 'vFriToSlot', 'vSatFromSlot', 'vSatToSlot', 'vSunFromSlot', 'vSunToSlot');
    $orgtimingArray = array('vMonFromSlot1', 'vMonToSlot1', 'vTueFromSlot1', 'vTueToSlot1', 'vWedFromSlot1', 'vWedToSlot1', 'vThuFromSlot1', 'vThuToSlot1', 'vFriFromSlot1', 'vFriToSlot1', 'vSatFromSlot1', 'vSatToSlot1', 'vSunFromSlot1', 'vSunToSlot1', 'vMonFromSlot2', 'vMonToSlot2', 'vTueFromSlot2', 'vTueToSlot2', 'vWedFromSlot2', 'vWedToSlot2', 'vThuFromSlot2', 'vThuToSlot2', 'vFriFromSlot2', 'vFriToSlot2', 'vSatFromSlot2', 'vSatToSlot2', 'vSunFromSlot2', 'vSunToSlot2');
    $sltAry = array(1, 2);
    $returnArr = array();
    if ($day == "Sunday" || $day == "Saturday") {
        $vFromTimeSlot1 = "vFromSatSunTimeSlot1";
        $vFromTimeSlot2 = "vFromSatSunTimeSlot2";
        $vToTimeSlot1 = "vToSatSunTimeSlot1";
        $vToTimeSlot2 = "vToSatSunTimeSlot2";
    } else {
        $vFromTimeSlot1 = "vFromMonFriTimeSlot1";
        $vFromTimeSlot2 = "vFromMonFriTimeSlot2";
        $vToTimeSlot1 = "vToMonFriTimeSlot1";
        $vToTimeSlot2 = "vToMonFriTimeSlot2";
    }
    $sqlp = "SELECT ru.vCurrencyPassenger,ru.vLang,cu.vSymbol,cu.Ratio FROM register_user as ru LEFT JOIN currency as cu ON ru.vCurrencyPassenger = cu.vName WHERE iUserId = '" . $iUserId . "'";
    $passengerData = $obj->MySQLSelect($sqlp);
    $userCurrencyRatio = 1;
    $vLanguage = "EN";
    $currencySymbol = "$";
    //echo "<pre>";print_r($_SESSION);die;
    if (count($passengerData) > 0) {
        $vLanguage = $passengerData[0]['vLang'];
        $userCurrencyRatio = $passengerData[0]['Ratio'];
        $currencySymbol = $passengerData[0]['vSymbol'];
    } else {
        $vLanguage = get_value('language_master', 'vCode', 'eDefault', 'Yes', '', 'true');
        $sqlp = "SELECT vName,vSymbol,Ratio FROM currency WHERE eDefault = 'Yes'";
        $currencyData = $obj->MySQLSelect($sqlp);
        if (count($currencyData) > 0) {
            $userCurrencyRatio = $currencyData[0]['Ratio'];
            $currencySymbol = $currencyData[0]['vSymbol'];
        }
    }
    if (isset($_SESSION['sess_lang']) && $_SESSION['sess_lang'] != "") {
        $vLanguage = $_SESSION['sess_lang'];
    }
    //echo $vLanguage;die;
    if (count($languageLabelsArr) == 0) {
        //$iServiceId = 1;
        $languageLabelsArr = getLanguageLabelsArr($vLanguage, "1", $iServiceId);
    }
    $cuisine_all = $companyCuisineArr = $companyLatLangArr = $offerMsgArr = $restaurantStatusArr = $companyCuisineIdArr = $pricePerPersonArr = $storeMinOrdValueArr = $storeDemoImageArr = $cuisineArr = array();
    $db_cuisine_str = "";
    if (count($CompanyId) > 0) {
        $storeIds = implode(",", $CompanyId);
        $whereServiceId = "";
        if ($serviceId > 0) {
            $whereServiceId = " AND cu.iServiceId='" . $serviceId . "'";
        }
        //$sql = "SELECT cu.cuisineName_" . $vLanguage . " as cuisineName,cu.cuisineId,cmp.vRestuarantLocationLat as restaurantlat,cmp.vRestuarantLocationLong as restaurantlong,cmp.* FROM cuisine as cu INNER JOIN company_cuisine as ccu ON ccu.cuisineId=cu.cuisineId INNER JOIN company cmp ON ccu.iCompanyId=cmp.iCompanyId WHERE ccu.iCompanyId IN ($storeIds) $whereServiceId AND cu.eStatus = 'Active'";
        $sql = "SELECT cu.cuisineName_" . $vLanguage . " as cuisineName,cu.cuisineId,ccu.iCompanyId FROM cuisine as cu INNER JOIN company_cuisine as ccu ON ccu.cuisineId=cu.cuisineId WHERE ccu.iCompanyId IN ($storeIds) $whereServiceId AND cu.eStatus = 'Active'";
        $db_cuisine = $obj->MySQLSelect($sql);
        //Added By HJ On 26-07-2019 Start
        for ($f = 0; $f < count($db_cuisine); $f++) {
            $cId = $db_cuisine[$f]['iCompanyId'];
            $cuisineArr[$cId][] = $db_cuisine[$f];
            $companyCuisineArr[$cId][] = $db_cuisine[$f]['cuisineName'];
            $companyCuisineIdArr[$cId][] = $db_cuisine[$f]['cuisineId'];
            $db_cuisine_str = $db_cuisine[$f]['cuisineName'];
            array_push($cuisine_all, $db_cuisine_str);
        }
        //Added By HJ On 26-07-2019 End
        //echo "<pre>";print_r($cuisine_all);die;
        $getStoreLantLangData = $obj->MySQLSelect("SELECT cmp.vRestuarantLocationLat as restaurantlat,cmp.vRestuarantLocationLong as restaurantlong,cmp.* FROM company AS cmp WHERE iCompanyId IN($storeIds)");
        //echo "<pre>";print_r($getStoreLantLangData);die;
        $LBL_MINS_SMALL = $languageLabelsArr['LBL_MINS_SMALL'];
        //Commented By HJ On 26-07-2019 Start
        /* for ($re = 0; $re < count($getStoreLantLangData); $re++) {
          $companyLatLangArr[$getStoreLantLangData[$re]['iCompanyId']]['restaurantlat'] = $getStoreLantLangData[$re]['restaurantlat'];
          $companyLatLangArr[$getStoreLantLangData[$re]['iCompanyId']]['restaurantlong'] = $getStoreLantLangData[$re]['restaurantlong'];
          $pricePerPersonArr[$getStoreLantLangData[$re]['iCompanyId']] = $generalobj->setTwoDecimalPoint($getStoreLantLangData[$re]['fPricePerPerson'] * $userCurrencyRatio);
          $storeMinOrdValueArr[$getStoreLantLangData[$re]['iCompanyId']] = $generalobj->setTwoDecimalPoint($getStoreLantLangData[$re]['fMinOrderValue'] * $userCurrencyRatio);
          $storeDemoImageArr[$getStoreLantLangData[$re]['iCompanyId']] = $getStoreLantLangData[$re]['vDemoStoreImage'];
          $fDeliverytime = $getStoreLantLangData[$re]['fPrepareTime'];
          $returnArr[$getStoreLantLangData[$re]['iCompanyId']]['Restaurant_OrderPrepareTime'] = $fDeliverytime . " " . $LBL_MINS_SMALL;
          } */
        //Commented By HJ On 26-07-2019 End
        //echo "<pre>";print_r($storeDemoImageArr);die;
        if (count($getStoreLantLangData) > 0) {
            for ($i = 0; $i < count($getStoreLantLangData); $i++) {
                //echo "<pre>";print_r($getStoreLantLangData[$i]);die;
                $iCompanyId = $getStoreLantLangData[$i]['iCompanyId'];
                //$db_cuisine_str = $db_cuisine[$i]['cuisineName']; // Commented By HJ On 26-07-2019
                //$companyCuisineArr[$db_cuisine[$i]['iCompanyId']][] = $db_cuisine[$i]['cuisineName']; // Commented By HJ On 26-07-2019
                //$companyCuisineIdArr[$db_cuisine[$i]['iCompanyId']][] = $db_cuisine[$i]['cuisineId']; // Commented By HJ On 26-07-2019
                //array_push($cuisine_all, $db_cuisine_str); // Commented By HJ On 26-07-2019
                //START CODE FOR GET STORE OFFER MESSAGE BY HJ ON 01-04-2019
                //Added By HJ On 26-07-2019 Start
                $companyLatLangArr[$iCompanyId]['restaurantlat'] = $getStoreLantLangData[$i]['restaurantlat'];
                $companyLatLangArr[$iCompanyId]['restaurantlong'] = $getStoreLantLangData[$i]['restaurantlong'];
                $pricePerPersonArr[$iCompanyId] = $generalobj->setTwoDecimalPoint($getStoreLantLangData[$i]['fPricePerPerson'] * $userCurrencyRatio);
                $storeMinOrdValueArr[$iCompanyId] = $generalobj->setTwoDecimalPoint($getStoreLantLangData[$i]['fMinOrderValue'] * $userCurrencyRatio);
                $storeDemoImageArr[$iCompanyId] = $getStoreLantLangData[$i]['vDemoStoreImage'];
                $fDeliverytime = $getStoreLantLangData[$i]['fPrepareTime'];
                $returnArr[$iCompanyId]['Restaurant_OrderPrepareTime'] = $fDeliverytime . " " . $LBL_MINS_SMALL;
                $returnArr[$iCompanyId]['Restaurant_OrderPrepareTimeValue'] = $fDeliverytime;
                $returnArr[$iCompanyId]['Restaurant_OrderPrepareTimePostfix'] = $LBL_MINS_SMALL;
                //Added By HJ On 26-07-2019 End
                $fOfferType = $getStoreLantLangData[$i]['fOfferType'];
                $fOfferAppyType = $getStoreLantLangData[$i]['fOfferAppyType'];
                $fOfferAmt = $generalobj->setTwoDecimalPoint($getStoreLantLangData[$i]['fOfferAmt']);
                $fTargetAmt = $generalobj->setTwoDecimalPoint($getStoreLantLangData[$i]['fTargetAmt']);
                $fTargetAmt = $generalobj->setTwoDecimalPoint($fTargetAmt * $userCurrencyRatio);
                $fMaxOfferAmt = $generalobj->setTwoDecimalPoint($getStoreLantLangData[$i]['fMaxOfferAmt']);
                $fMaxOfferAmt = $generalobj->setTwoDecimalPoint($fMaxOfferAmt * $userCurrencyRatio);
                $MaxDiscountAmount = $ALL_ORDER_TXT = $offermsg = $offermsg_short = "";
                if ($fMaxOfferAmt > 0) {
                    $MaxDiscountAmount = " ( " . $languageLabelsArr['LBL_MAX_DISCOUNT_TXT'] . " " . $currencySymbol . "" . $fMaxOfferAmt . " )";
                }
                if ($fTargetAmt > 0) {
                    $TargerAmountTXT = $languageLabelsArr['LBL_OFF_TXT'] . " " . $languageLabelsArr['LBL_ORDERS_ABOVE_TXT'] . " " . $currencySymbol . "" . $fTargetAmt . " ";
                } else {
                    $TargerAmountTXT = $languageLabelsArr['LBL_OFF_TXT'];
                    $ALL_ORDER_TXT = $languageLabelsArr['LBL_ALL_ORDER_TXT'];
                }
                
                if ($fOfferType == "Percentage") {
                    if ($fOfferAppyType == "First") {
                        $offermsg = $languageLabelsArr['LBL_GET_TXT'] . " " . $fOfferAmt . "% " . $TargerAmountTXT ." ". $languageLabelsArr['LBL_FIRST_ORDER_TXT'] . "" . $MaxDiscountAmount;
                        $offermsg_short = $languageLabelsArr['LBL_GET_TXT'] . " " . $fOfferAmt . "% " . $TargerAmountTXT . " " . $languageLabelsArr['LBL_FIRST_ORDER_TXT'];
                    } elseif ($fOfferAppyType == "All") {
                        $offermsg = $languageLabelsArr['LBL_GET_TXT'] . " " . $fOfferAmt . "% " . $TargerAmountTXT . " " . $ALL_ORDER_TXT . " " . $MaxDiscountAmount;

                        // $offermsg =  $languageLabelsArr['LBL_GET_TXT']." ".$fOfferAmt."% ".$TargerAmountTXT." ".$MaxDiscountAmount;
                        $offermsg_short = $languageLabelsArr['LBL_GET_TXT'] . " " . $fOfferAmt . "% " . $TargerAmountTXT . " " . $ALL_ORDER_TXT;
                    }
                } else {
                    $fOfferAmt = $generalobj->setTwoDecimalPoint($fOfferAmt * $userCurrencyRatio);
                    $DiscountAmount = $currencySymbol . "" . $fOfferAmt;
                    if ($fOfferAppyType == "First" && $fOfferAmt > 0) {
                        $offermsg = $languageLabelsArr['LBL_GET_TXT'] . " " . $DiscountAmount . " " . $TargerAmountTXT . " " . $languageLabelsArr['LBL_FIRST_ORDER_TXT'];
                        $offermsg_short = $offermsg;
                    } elseif ($fOfferAppyType == "All" && $fOfferAmt > 0) {
                        $offermsg = $languageLabelsArr['LBL_GET_TXT'] . " " . $DiscountAmount . " " . $TargerAmountTXT . " " . $ALL_ORDER_TXT;

                        // $offermsg =  $languageLabelsArr['LBL_GET_TXT']." ".$DiscountAmount." ".$TargerAmountTXT;
                        $offermsg_short = $offermsg;
                    }
                }
                $offerMsgArr[$iCompanyId]['Restaurant_OfferMessage'] = $offermsg;
                $offerMsgArr[$iCompanyId]['Restaurant_OfferMessage_short'] = $offermsg_short;
                
                //END CODE FOR GET STORE OFFER MESSAGE BY HJ ON 01-04-2019
                //START CODE FOR GET RESTAURANT STATUS BY HJ ON 01-04-2019

                if (isset($getStoreLantLangData[$i][$vFromTimeSlot1]) && $getStoreLantLangData[$i][$vFromTimeSlot1] == "00:00:00" && $getStoreLantLangData[$i][$vToTimeSlot1] == "00:00:00" && $getStoreLantLangData[$i][$vFromTimeSlot2] == "00:00:00" && $getStoreLantLangData[$i][$vToTimeSlot2] == "00:00:00") {
                    $restaurantStatusArr[$iCompanyId]['status'] = "Closed";
                } else {
                    if ($getStoreLantLangData[$i][$vToTimeSlot1] < $getStoreLantLangData[$i][$vFromTimeSlot1]) {
                        $endTime = strtotime($getStoreLantLangData[$i][$vToTimeSlot1]);
                        $vFromTimeSlot_1 = date(("H:i"), strtotime($getStoreLantLangData[$i][$vFromTimeSlot1]));
                        $vToTimeSlot_1 = date(("H:i"), strtotime('+1 day', $endTime));
                    } else {
                        $vFromTimeSlot_1 = date(("H:i"), strtotime($getStoreLantLangData[$i][$vFromTimeSlot1]));
                        $vToTimeSlot_1 = date(("H:i"), strtotime($getStoreLantLangData[$i][$vToTimeSlot1]));
                    }

                    if ($getStoreLantLangData[$i][$vToTimeSlot2] < $getStoreLantLangData[$i][$vFromTimeSlot2]) {
                        $endTime2 = strtotime($getStoreLantLangData[$i][$vToTimeSlot2]);
                        $vFromTimeSlot_2 = date(("H:i"), strtotime($getStoreLantLangData[$i][$vFromTimeSlot2]));
                        $vToTimeSlot_2 = date(("H:i"), strtotime('+1 day', $endTime2));
                    } else {
                        $vFromTimeSlot_2 = date(("H:i"), strtotime($getStoreLantLangData[$i][$vFromTimeSlot2]));
                        $vToTimeSlot_2 = date(("H:i"), strtotime($getStoreLantLangData[$i][$vToTimeSlot2]));
                    }
                    //$date = @date("H:i");
                    $date = @date("H:i", strtotime($vCurrentTime));
                    // $currenttime = strtotime($date);
                    $status = "closed";
                    $status_display = $languageLabelsArr['LBL_RESTAURANT_CLOSED_STATUS_TXT'];
                    $opentime = $closetime = "";
                    $OpenAt = $languageLabelsArr['LBL_RESTAURANT_OPEN_TXT'];
                    $timeslotavailable = "No";
                    if (isBetween($vFromTimeSlot_1, $vToTimeSlot_1, $date) == 1 || isBetween($vFromTimeSlot_2, $vToTimeSlot_2, $date) == 1) {
                        $status = "open";
                        $timeslotavailable = "Yes";
                        $status_display = $languageLabelsArr['LBL_RESTAURANT_OPEN_STAUS_TXT'];
                        $currentdate = @date("Y-m-d H:i:s");
                        $enddate = @date("Y-m-d");
                        if (isBetween($vFromTimeSlot_1, $vToTimeSlot_1, $date) == 1) {
                            $enddate = $enddate . " " . $vToTimeSlot_1 . ":00";
                        } else {
                            $enddate = $enddate . " " . $vToTimeSlot_2 . ":00";
                        }

                        $datediff = strtotime($enddate) - strtotime($currentdate);
                        if ($datediff < 900) {
                            $closein = $languageLabelsArr['LBL_RESTAURANT_CLOSE_MINS_TXT'];
                            $closemins = round($datediff / 60);
                            $closetime = $closein . " " . $closemins . " " . $languageLabelsArr['LBL_MINS_SMALL'];
                        }
                    } else {
                        $newdate = @date("Y-m-d");
                        // $newdate = $newdate." ".$vFromTimeSlot_2.":00";
                        if (isBetween($vFromTimeSlot_1, $vFromTimeSlot_1, $date) == 1) {
                            $newdate = $newdate . " " . $vFromTimeSlot_1 . ":00";
                        } else {
                            if ($vFromTimeSlot_1 < $vFromTimeSlot_2 && $vFromTimeSlot_1 > $date) {
                                $newdate = $newdate . " " . $vFromTimeSlot_1 . ":00";
                            } else {
                                $newdate = ($vFromTimeSlot_2 == "00:00") ? $newdate . " " . $vFromTimeSlot_1 . ":00" : $newdate . " " . $vFromTimeSlot_2 . ":00";
                            }
                        }
                        $currentdate = @date("Y-m-d H:i:s");
                        $datediff = strtotime($newdate) - strtotime($currentdate);
                        if ($datediff > 0) {
                            $opentime = $OpenAt . " " . date("h:i a", strtotime($newdate));
                        }
                    }
                    $eAvailable = $getStoreLantLangData[$i]['eAvailable'];
                    $eLogout = $getStoreLantLangData[$i]['eLogout'];
                    $eStatus = $getStoreLantLangData[$i]['eStatus'];
                    if ($eAvailable == "No" || $eLogout == "Yes" || $eStatus != "Active") {
                        $status_display = $languageLabelsArr['LBL_RESTAURANT_CLOSED_STATUS_TXT'];
                        $closetime = "";
                        $status = "closed";
                    }

                    $restaurantStatusArr[$iCompanyId]['opentime'] = $opentime;
                    $restaurantStatusArr[$iCompanyId]['closetime'] = $closetime;
                    $restaurantStatusArr[$iCompanyId]['timeslotavailable'] = $timeslotavailable;
                    $restaurantStatusArr[$iCompanyId]['status'] = $status;
                }
                //END CODE FOR GET RESTAURANT STATUS BY HJ ON 01-04-2019
            }
        }
    }
    $cuisine_all = array_unique($cuisine_all);
    $count = count($cuisine_all);
    $returnArr['cuisinecount'] = $count;
    $returnArr['cuisineArr'] = $cuisine_all;
    $returnArr['companyCuisineArr'] = $companyCuisineArr;
    $returnArr['companyCuisineIdArr'] = $companyCuisineIdArr;
    $returnArr['latLangArr'] = $companyLatLangArr;
    $returnArr['offerMsgArr'] = $offerMsgArr;
    $returnArr['restaurantStatusArr'] = $restaurantStatusArr;
    $returnArr['restaurantPricePerPerson'] = $pricePerPersonArr;
    $returnArr['restaurantMinOrdValue'] = $storeMinOrdValueArr;
    $returnArr['storeDemoImageArr'] = $storeDemoImageArr;
    //$returnArr['Restaurant_OrderPrepareTime'] = $storePrepareTimeValueArr;
    $returnArr['currencySymbol'] = $currencySymbol;
    return $returnArr;
}

function sendApplePushNotificationweb_duplicate($PassengerToDriver = 0, $deviceTokens, $message, $alertMsg, $filterMsg, $fromDepart = '') {
    // global $generalobj, $obj, $IPHONE_PEM_FILE_PASSPHRASE,$APP_MODE,$ENABLE_PUBNUB, $PARTNER_APP_IPHONE_PEM_FILE_NAME, $PASSENGER_APP_IPHONE_PEM_FILE_NAME;
    global $generalobj, $obj;

    $sql = "select vValue,vName from configurations where vName in('IPHONE_PEM_FILE_PASSPHRASE','APP_MODE','ENABLE_PUBNUB','PARTNER_APP_IPHONE_PEM_FILE_NAME','PASSENGER_APP_IPHONE_PEM_FILE_NAME','PRO_PASSENGER_APP_IPHONE_PEM_FILE_NAME','PRO_PARTNER_APP_IPHONE_PEM_FILE_NAME','COMPANY_APP_IPHONE_PEM_FILE_NAME','PRO_COMPANY_APP_IPHONE_PEM_FILE_NAME','PRO_PASSENGER_DL_APP_IPHONE_PEM_FILE_NAME','PASSENGER_DL_APP_IPHONE_PEM_FILE_NAME')";
    $Data_config = $obj->MySQLSelect($sql);
    for ($i = 0; $i < count($Data_config); $i++) {
        $temp_val = $Data_config[$i]['vValue'];
        $temp_vName = $Data_config[$i]['vName'];
        $$temp_vName = $temp_val;
    }

    if ($message == "") {
        return "";
    }

    $passphrase = $IPHONE_PEM_FILE_PASSPHRASE;

    // $APP_MODE = $APP_MODE;
    // $ENABLE_PUBNUB = $generalobj->getConfigurations("configurations","ENABLE_PUBNUB");
    $prefix = "";
    $url_apns = 'ssl://gateway.sandbox.push.apple.com:2195';
    if ($APP_MODE == "Production") {
        $prefix = "PRO_";
        $url_apns = 'ssl://gateway.push.apple.com:2195';
    }

    if ($PassengerToDriver == 1) {

        // $name = $generalobj->getConfigurations("configurations", $prefix . "PARTNER_APP_IPHONE_PEM_FILE_NAME");    // send notification to driver
        $name1 = $prefix . "PARTNER_APP_IPHONE_PEM_FILE_NAME";
        $name = $$name1;
    } else if ($PassengerToDriver == 2) {

        // $name = $generalobj->getConfigurations("configurations", $prefix . "COMPANY_APP_IPHONE_PEM_FILE_NAME");    // send notification to company
        $name1 = $prefix . "COMPANY_APP_IPHONE_PEM_FILE_NAME";
        $name = $$name1;
    } else {

        // $name = $generalobj->getConfigurations("configurations", $prefix . "PASSENGER_APP_IPHONE_PEM_FILE_NAME");  // send notification to passenger
        $name1 = $prefix . "PASSENGER_DL_APP_IPHONE_PEM_FILE_NAME";
        $name = $$name1;
    }

    $ctx = stream_context_create();
    if ($fromDepart == 'admin') {
        $name = '../' . $name;
    }

    stream_context_set_option($ctx, 'ssl', 'local_cert', $name);
    stream_context_set_option($ctx, 'ssl', 'passphrase', $passphrase);
    $fp = stream_socket_client($url_apns, $err, $errstr, 60, STREAM_CLIENT_CONNECT | STREAM_CLIENT_PERSISTENT, $ctx);

    /* 		 echo "deviceTokens => <pre>";
      print_r($deviceTokens);
      echo "<pre>"; print_r($fp); die; */

    if (!$fp) {

        if ($ENABLE_PUBNUB == "No") {
            /*
              $returnArr['Action'] = "0";
              $returnArr['message'] = "LBL_SERVER_COMM_ERROR";
              $returnArr['ERROR'] = $err . $errstr . " " . PHP_EOL;
              setDataResponse($returnArr);
              exit;
             */
        }
    }

    // Create the payload body
    $body['aps'] = array(
        'alert' => $alertMsg,
        'content-available' => 1,
        'body' => $message,
        'sound' => 'default'
    );

    // Encode the payload as JSON
    $payload = json_encode($body, JSON_UNESCAPED_UNICODE);

    //        $payload= stripslashes(preg_replace("/[\n\r]/","",$payload));
    if ($filterMsg == 1) {
        $payload = stripslashes(preg_replace("/[\n\r]/", "", $payload));
    }

    for ($device = 0; $device < count($deviceTokens); $device++) {

        // Build the binary notification
        $msg = chr(0) . pack('n', 32) . pack('H*', $deviceTokens[$device]) . pack('n', strlen($payload)) . $payload;

        // Send it to the server
        $result = fwrite($fp, $msg, strlen($msg));
    }

    // Close the connection to the server
    fclose($fp);
}

function send_notificationweb_duplicate($registatoin_ids, $message, $filterMsg = 0) {

    // include config
    // include_once './config.php';
    // global $generalobj, $obj,$FIREBASE_API_ACCESS_KEY,$ENABLE_PUBNUB;
    global $generalobj, $obj;
    $FIREBASE_API_ACCESS_KEY = $generalobj->getConfigurations("configurations", "FIREBASE_API_ACCESS_KEY");
    $ENABLE_PUBNUB = $generalobj->getConfigurations("configurations", "ENABLE_PUBNUB");
    $fields = array(
        'registration_ids' => $registatoin_ids,
        'click_action' => ".MainActivity",
        'priority' => "high",
        // 'data'          => $msg
        'data' => $message
    );
    $finalFields = json_encode($fields, JSON_UNESCAPED_UNICODE);
    if ($filterMsg == 1) {
        $finalFields = stripslashes(preg_replace("/[\n\r]/", "", $finalFields));
    }

    $headers = array(
        'Authorization: key=' . $FIREBASE_API_ACCESS_KEY,
        'Content-Type: application/json',
    );

    // Setup headers:
    // echo "<pre>";print_r($headers);exit;
    // Setup curl, add headers and post parameters.
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'https://fcm.googleapis.com/fcm/send');
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $finalFields);

    // curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
    // curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    // Send the request
    $response = curl_exec($ch); //echo "<pre>";print_r($response);exit;
    if ($response === false) {

        /* 	die('Curl failed: ' . curl_error($ch)); */
        if ($ENABLE_PUBNUB == "No") {
            /* 	$returnArr['Action'] = "0";
              $returnArr['message'] = "LBL_SERVER_COMM_ERROR";
              $returnArr['ERROR'] = curl_error($ch);
              setDataResponse($returnArr);
              exit; */
        }
    }

    $responseArr = json_decode($response);
    $success = $responseArr->success;

    // Close request
    curl_close($ch);
    return $success;
}

/*
  function getOnlineDriverArr($sourceLat, $sourceLon, $address_data = array(), $DropOff = "No", $From_Autoassign = "No", $Check_Driver_UFX = "No", $Check_Date_Time = "", $destLat = "", $destLon = "") {
  global $generalobj, $obj, $RESTRICTION_KM_NEAREST_TAXI, $LIST_RESTAURANT_LIMIT_BY_DISTANCE, $LIST_DRIVER_LIMIT_BY_DISTANCE, $DRIVER_REQUEST_METHOD, $COMMISION_DEDUCT_ENABLE, $WALLET_MIN_BALANCE, $RESTRICTION_KM_NEAREST_TAXI, $APP_TYPE, $vTimeZone, $intervalmins;
  $cmpMinutes = ceil((fetchtripstatustimeMAXinterval() + $intervalmins) / 60);
  $str_date = @date('Y-m-d H:i:s', strtotime('-' . $cmpMinutes . ' minutes'));
  $From_Autoassign = "Yes";

  $LIST_DRIVER_LIMIT_BY_DISTANCE = $From_Autoassign == "Yes" ? $LIST_RESTAURANT_LIMIT_BY_DISTANCE : $LIST_RESTAURANT_LIMIT_BY_DISTANCE;

  $param = ($DRIVER_REQUEST_METHOD == "Time") ? "tOnline" : "tLocationUpdateDate";
  $sourceLocationArr = array(
  $sourceLat,
  $sourceLon
  );
  $destinationLocationArr = array(
  $destLat,
  $destLon
  );
  $ssql_available = "";
  $allowed_ans = "Yes";
  $allowed_ans_drop = "Yes";
  $vLatitude = 'vLatitude';
  $vLongitude = 'vLongitude';
  if ($Check_Driver_UFX == "No") {
  $ssql_available .= " AND vAvailability = 'Available' AND vTripStatus != 'Active' AND tLocationUpdateDate > '$str_date' ";
  }

  if ($allowed_ans == 'Yes' && $allowed_ans_drop == 'Yes') {
  $sql = "SELECT ROUND(( 3959 * acos( cos( radians(" . $sourceLat . ") )
 * cos( radians( ROUND(" . $vLatitude . ",8) ) )
 * cos( radians( ROUND(" . $vLongitude . ",8) ) - radians(" . $sourceLon . ") )
  + sin( radians(" . $sourceLat . ") )
 * sin( radians( ROUND(" . $vLatitude . ",8) ) ) ) ),2) AS distance, concat('+',register_driver.vCode,register_driver.vPhone) as vPhonenumber, register_driver.*  FROM `register_driver`
  WHERE (" . $vLatitude . " != '' AND " . $vLongitude . " != '' $ssql_available AND eStatus='active')
  HAVING distance < " . $LIST_DRIVER_LIMIT_BY_DISTANCE . " ORDER BY `register_driver`.`" . $param . "` ASC";
  $Data = $obj->MySQLSelect($sql);
  $newData = array();
  $j = 0;
  $driver_id_auto = "";
  for ($i = 0; $i < count($Data); $i++) {
  $sql = "select GROUP_CONCAT(iVehicleTypeId)as VehicleTypeId from `vehicle_type` where eType = 'DeliverAll'";
  $db_deliverall_vehicle = $obj->MySQLSelect($sql);
  $VehicleTypeId = $db_deliverall_vehicle[0]['VehicleTypeId'];
  $VehicleTypeIdArr = explode(",", $VehicleTypeId);
  $iDriverVehicleId = $Data[$i]['iDriverVehicleId'];
  $vCarType = get_value('driver_vehicle', 'vCarType', 'iDriverVehicleId', $iDriverVehicleId, '', 'true');
  $drivercartypeArr = explode(",", $vCarType);
  $vCarTypeArr = array_intersect($VehicleTypeIdArr, $drivercartypeArr);
  $vCarTypeArr = array_values($vCarTypeArr);
  $vCarType = $vCarTypeArr[0];
  $fRadius = get_value('vehicle_type', 'fRadius', 'iVehicleTypeId', $vCarType, '', 'true');
  $Data[$i]['DeliveryVehicleType'] = $vCarType;
  $distanceusercompany = distanceByLocation($sourceLat, $sourceLon, $destLat, $destLon, "K");

  $Data[$i]['vPhone'] = $Data[$i]['vPhonenumber'];
  if ($COMMISION_DEDUCT_ENABLE == 'Yes') {
  $user_available_balance = $generalobj->get_user_available_balance($Data[$i]['iDriverId'], "Driver");
  if ($WALLET_MIN_BALANCE > $user_available_balance) {
  $Data[$i]['ACCEPT_CASH_TRIPS'] = "No";
  } else {
  $Data[$i]['ACCEPT_CASH_TRIPS'] = "Yes";
  }
  } else {
  $Data[$i]['ACCEPT_CASH_TRIPS'] = "Yes";
  }

  // if($fRadius > $distanceusercompany){
  if (($fRadius > $distanceusercompany) && $vCarType > 0) {
  $driver_id_auto .= $Data[$i]['iDriverId'] . ",";
  $newData[$j] = $Data[$i];
  $j++;
  }
  }

  $driver_id_auto = substr($driver_id_auto, 0, -1);

  // $returnData['DriverList'] = $Data;
  $returnData['DriverList'] = $newData;
  $returnData['driver_id_auto'] = $driver_id_auto;
  $returnData['PickUpDisAllowed'] = $allowed_ans;
  $returnData['DropOffDisAllowed'] = $allowed_ans_drop;
  } else {

  $newData = array();
  $returnData['DriverList'] = $newData;
  $returnData['driver_id_auto'] = "";
  $returnData['PickUpDisAllowed'] = $allowed_ans;
  $returnData['DropOffDisAllowed'] = $allowed_ans_drop;
  }


  return $returnData;
  } */

function customGetOnlineDriverArr($sourceLat, $sourceLon, $address_data = array(), $DropOff = "No", $From_Autoassign = "No", $Check_Driver_UFX = "No", $Check_Date_Time = "", $destLat = "", $destLon = "") {

    global $generalobj, $obj, $RESTRICTION_KM_NEAREST_TAXI, $LIST_RESTAURANT_LIMIT_BY_DISTANCE, $LIST_DRIVER_LIMIT_BY_DISTANCE, $DRIVER_REQUEST_METHOD, $COMMISION_DEDUCT_ENABLE, $WALLET_MIN_BALANCE, $RESTRICTION_KM_NEAREST_TAXI, $APP_TYPE, $vTimeZone, $intervalmins;
    $cmpMinutes = ceil((fetchtripstatustimeMAXinterval() + $intervalmins) / 60);
    $str_date = @date('Y-m-d H:i:s', strtotime('-' . $cmpMinutes . ' minutes'));
    $From_Autoassign = "Yes";
    $LIST_DRIVER_LIMIT_BY_DISTANCE = $From_Autoassign == "Yes" ? $LIST_RESTAURANT_LIMIT_BY_DISTANCE : $LIST_RESTAURANT_LIMIT_BY_DISTANCE;
    $param = ($DRIVER_REQUEST_METHOD == "Time") ? "tOnline" : "tLocationUpdateDate";
    $sourceLocationArr = array(
        $sourceLat,
        $sourceLon
    );
    $destinationLocationArr = array(
        $destLat,
        $destLon
    );
    $ssql_available = "";
    $allowed_ans = "Yes";
    $allowed_ans_drop = "Yes";
    $vLatitude = 'vLatitude';
    $vLongitude = 'vLongitude';
    if ($Check_Driver_UFX == "No") {
        $ssql_available .= " AND vAvailability = 'Available' AND vTripStatus != 'Active' AND tLocationUpdateDate > '$str_date' ";
    }

    if ($allowed_ans == 'Yes' && $allowed_ans_drop == 'Yes') {
        //getiing new variable that we create for food deliver
        //iFoodDeliveryRadius

        $sql = "SELECT ROUND(( 3959 * acos( cos( radians(" . $sourceLat . ") )
        * cos( radians( ROUND(" . $vLatitude . ",8) ) )
            * cos( radians( ROUND(" . $vLongitude . ",8) ) - radians(" . $sourceLon . ") )
            + sin( radians(" . $sourceLat . ") )
            * sin( radians( ROUND(" . $vLatitude . ",8) ) ) ) ),2) AS distance, concat('+',register_driver.vCode,register_driver.vPhone) as vPhonenumber, register_driver.*, register_driver.iFoodDeliveryRadius
            FROM `register_driver`
            WHERE (" . $vLatitude . " != '' AND " . $vLongitude . " != '' $ssql_available AND eStatus='active')
            HAVING distance < " . $LIST_DRIVER_LIMIT_BY_DISTANCE . " 
            ORDER BY `register_driver`.`" . $param . "` ASC";

        $Data = $obj->MySQLSelect($sql);

        $newConditionalData = array();
        $counter = 0;
        foreach ($Data as $radiusDriverdata) {
            //driver radius
            $iFoodDeliveryRadius = $radiusDriverdata['iFoodDeliveryRadius'];
            //Driver
            $vLatitude = $radiusDriverdata['vLatitude'];
            $vLongitude = $radiusDriverdata['vLongitude'];

            //check driver to restaurant location
            $driverTorestaurantDist = distanceByLocation($vLatitude, $vLongitude, $sourceLat, $sourceLon, "k");
            $driverTodestinationDist = distanceByLocation($vLatitude, $vLongitude, $destLat, $destLon, "k");

            if ($driverTorestaurantDist < $iFoodDeliveryRadius) {
                //check driver to deliver location
                if ($driverTodestinationDist < $iFoodDeliveryRadius) {
                    $newConditionalData[$counter] = $radiusDriverdata;
                    $counter++;
                }
            }
        }

        $newData = array();
        $j = 0;
        $driver_id_auto = "";
        for ($i = 0; $i < count($newConditionalData); $i++) {
            $sql = "select GROUP_CONCAT(iVehicleTypeId)as VehicleTypeId from `vehicle_type` where eType = 'DeliverAll'";
            $db_deliverall_vehicle = $obj->MySQLSelect($sql);
            $VehicleTypeId = $db_deliverall_vehicle[0]['VehicleTypeId'];
            $VehicleTypeIdArr = explode(",", $VehicleTypeId);
            $iDriverVehicleId = $newConditionalData[$i]['iDriverVehicleId'];
            $vCarType = get_value('driver_vehicle', 'vCarType', 'iDriverVehicleId', $iDriverVehicleId, '', 'true');
            $drivercartypeArr = explode(",", $vCarType);
            $vCarTypeArr = array_intersect($VehicleTypeIdArr, $drivercartypeArr);
            $vCarTypeArr = array_values($vCarTypeArr);
            $vCarType = $vCarTypeArr[0];
            $fRadius = get_value('vehicle_type', 'fRadius', 'iVehicleTypeId', $vCarType, '', 'true');
            $newConditionalData[$i]['DeliveryVehicleType'] = $vCarType;
            $distanceusercompany = distanceByLocation($sourceLat, $sourceLon, $destLat, $destLon, "K");

            $newConditionalData[$i]['vPhone'] = $newConditionalData[$i]['vPhonenumber'];
            if ($COMMISION_DEDUCT_ENABLE == 'Yes') {
                $user_available_balance = $generalobj->get_user_available_balance($newConditionalData[$i]['iDriverId'], "Driver");
                if ($WALLET_MIN_BALANCE > $user_available_balance) {
                    $newConditionalData[$i]['ACCEPT_CASH_TRIPS'] = "No";
                } else {
                    $newConditionalData[$i]['ACCEPT_CASH_TRIPS'] = "Yes";
                }
            } else {
                $newConditionalData[$i]['ACCEPT_CASH_TRIPS'] = "Yes";
            }

            // if($fRadius > $distanceusercompany){
            if (($fRadius > $distanceusercompany) && $vCarType > 0) {
                $driver_id_auto .= $newConditionalData[$i]['iDriverId'] . ",";
                $newData[$j] = $newConditionalData[$i];
                $j++;
            }
        }
        //$driver_id_auto = substr($driver_id_auto, 0, -1);
        $driver_id_auto = trim($driver_id_auto, ",");

        // $returnData['DriverList'] = $Data;
        $returnData['DriverList'] = $newData;
        $returnData['driver_id_auto'] = $driver_id_auto;
        $returnData['PickUpDisAllowed'] = $allowed_ans;
        $returnData['DropOffDisAllowed'] = $allowed_ans_drop;
    } else {
        /* $Data = array();
          $returnData['DriverList'] = $Data; */
        $newData = array();
        $returnData['DriverList'] = $newData;
        $returnData['driver_id_auto'] = "";
        $returnData['PickUpDisAllowed'] = $allowed_ans;
        $returnData['DropOffDisAllowed'] = $allowed_ans_drop;
    }


    return $returnData;
}

function getNearRestaurantArr($sourceLat, $sourceLon, $iUserId, $fOfferType = "No", $searchword = "", $vAddress = "", $iServiceId = '') {
    global $generalobj, $obj, $LIST_RESTAURANT_LIMIT_BY_DISTANCE, $DRIVER_REQUEST_METHOD, $intervalmins;
    $cmpMinutes = ceil((fetchtripstatustimeMAXinterval() + $intervalmins) / 60);
    $str_date = @date('Y-m-d H:i:s', strtotime('-' . $cmpMinutes . ' minutes'));
    //$LIST_RESTAURANT_LIMIT_BY_DISTANCE = $generalobj->getConfigurations("configurations", "LIST_RESTAURANT_LIMIT_BY_DISTANCE");
    //$DRIVER_REQUEST_METHOD = $generalobj->getConfigurations("configurations", "DRIVER_REQUEST_METHOD");
    $param = ($DRIVER_REQUEST_METHOD == "Time") ? "tOnline" : "tLastOnline";
    $sourceLocationArr = array($sourceLat, $sourceLon);
    $allowed_ans = checkAllowedAreaNew($sourceLocationArr, "No");
    $ssql = $having_ssql = "";
    if ($fOfferType == "Yes") {
        $ssql .= " AND ( company.fOfferType = 'Flat' OR company.fOfferType = 'Percentage' )";
    }
    if (SITE_TYPE == "Demo" && $searchword == "") {
        $having_ssql .= " OR company.eDemoDisplay = 'Yes'";
        // $ResCountry = ($vUserDeviceCountry == "IN")?"('IN')":"('IN','".$vUserDeviceCountry."')";
        // $ssql .=  "AND ( eDemoDisplay = 'Yes' OR eLock = 'No' )";
        if ($vAddress != "") {
            //$ssql .= " AND ( company.vRestuarantLocation like '%$vAddress%' OR company.vRestuarantLocation like '%India%' OR company.eDemoDisplay = 'Yes')";
        } else {
            //$ssql .= " AND ( company.vRestuarantLocation like '%India%' OR company.eDemoDisplay = 'Yes')";
        }
    }

    if ($allowed_ans == 'Yes') {
        $sql = "SELECT ROUND(( 6371 * acos( cos( radians(" . $sourceLat . ") ) 
		* cos( radians( vRestuarantLocationLat ) ) 
			* cos( radians( vRestuarantLocationLong ) - radians(" . $sourceLon . ") ) 
			+ sin( radians(" . $sourceLat . ") ) 
			* sin( radians( vRestuarantLocationLat ) ) ) ),2) AS distance, company.* FROM `company`
			WHERE vRestuarantLocationLat != '' AND vRestuarantLocationLong != '' AND eStatus='Active' AND eSystem = 'DeliverAll' AND iServiceId = '" . $iServiceId . "' $ssql
			HAVING (distance < " . $LIST_RESTAURANT_LIMIT_BY_DISTANCE . $having_ssql . ") ORDER BY `company`.`iCompanyId` ASC";
        /* $sql = "SELECT ROUND(( 3959 * acos( cos( radians(".$sourceLat.") )
         * cos( radians( vLatitude ) )
         * cos( radians( vLongitude ) - radians(".$sourceLon.") )
          + sin( radians(".$sourceLat.") )
         * sin( radians( vLatitude ) ) ) ),2) AS distance,company.* FROM `company`
          WHERE (vLatitude != '' AND vLongitude != '' AND eStatus='active')
          ORDER BY `company`.`iCompanyId` ASC"; */
        $Data = $obj->MySQLSelect($sql);
        if (count($Data) > 0) {
            for ($i = 0; $i < count($Data); $i++) {
                $vAvgRating = $Data[$i]['vAvgRating'];
                $Data[$i]['vAvgRating'] = ($vAvgRating > 0) ? number_format($Data[$i]['vAvgRating'], 1) : 0;
                $Data[$i]['vAvgRatingOrig'] = $Data[$i]['vAvgRating'];
                $restaurant_status_arr = calculate_restaurant_time_span($Data[$i]['iCompanyId'], $iUserId);
                $Data[$i]['Restaurant_Status'] = $restaurant_status_arr['status'];
                $Data[$i]['Restaurant_Opentime'] = $restaurant_status_arr['opentime'];
                $Data[$i]['Restaurant_Closetime'] = $restaurant_status_arr['closetime'];
                $Data[$i]['restaurantstatus'] = $restaurant_status_arr['restaurantstatus']; // closed or open
                $Data[$i]['timeslotavailable'] = $restaurant_status_arr['timeslotavailable'];
                $CompanyDetailsArr = getCompanyDetails($Data[$i]['iCompanyId'], $iUserId, "No", "");
                $Data[$i]['Restaurant_Cuisine'] = $CompanyDetailsArr['Restaurant_Cuisine'];
                $Data[$i]['Restaurant_Cuisine_Id'] = $CompanyDetailsArr['Restaurant_Cuisine_Id'];
                if ($iServiceId == '1') {
                    $Data[$i]['Restaurant_PricePerPerson'] = $CompanyDetailsArr['Restaurant_PricePerPerson'];
                } else {
                    $Data[$i]['Restaurant_PricePerPerson'] = '';
                }

                $Data[$i]['Restaurant_OrderPrepareTime'] = $CompanyDetailsArr['Restaurant_OrderPrepareTime'];
                $Data[$i]['Restaurant_OfferMessage'] = $CompanyDetailsArr['Restaurant_OfferMessage'];
                $Data[$i]['Restaurant_OfferMessage_short'] = $CompanyDetailsArr['Restaurant_OfferMessage_short'];
                $Data[$i]['Restaurant_MinOrderValue'] = $CompanyDetailsArr['Restaurant_MinOrderValue'];

                // $Data[$i]['CompanyFoodData'] =  $CompanyDetailsArr['CompanyFoodData'];
                $Data[$i]['CompanyFoodDataCount'] = $CompanyDetailsArr['CompanyFoodDataCount'];
                $Data[$i]['CompanyFoodData'] = array();
            }

            /* foreach($Data as $row)
              {
              $Data_name[] = $row['restaurantstatus'];
              }

              array_multisort($Data_name, SORT_DESC, $Data); */

            // array_multisort(array_column($Data, 'fPrepareTime'), SORT_ASC,array_column($Data, 'restaurantstatus'),SORT_DESC,$mylist);
        }

        // echo "<pre>";print_r($Data);exit;
        return $Data;
    } else {
        $Data = array();
        return $Data;
    }
}

/* function checkSurgePrice($vehicleTypeID, $selectedDateTime = "", $iRentalPackageId = "0") {
  global $ENABLE_SURGE_CHARGE_RENTAL, $vTimeZone;
  if ($iRentalPackageId == "" || $iRentalPackageId == NULL) {
  $iRentalPackageId = 0;
  }
  $ePickStatus = get_value('vehicle_type', 'ePickStatus', 'iVehicleTypeId', $vehicleTypeID, '', 'true');
  $eNightStatus = get_value('vehicle_type', 'eNightStatus', 'iVehicleTypeId', $vehicleTypeID, '', 'true');
  $fPickUpPrice = 1;
  $fNightPrice = 1;
  if ($selectedDateTime == "") {
  $selectedTime = @date("Y-m-d H:i:s");
  $systemTimeZone = date_default_timezone_get();
  $currentDateTime = converToTz($selectedTime, $vTimeZone, $systemTimeZone);

  $currentTime = @date('H:i:s', strtotime($currentDateTime));
  $currentDay = @date('D', strtotime($currentDateTime));

  $PreviousDayDate = @date('Y-m-d', strtotime('-1 day'));
  $PreviousDay = @date('D', strtotime($PreviousDayDate));
  // $currentTime = @date("H:i:s");
  // $currentDay = @date("D");
  } else {

  // $currentTime = $selectedDateTime;
  $PreviousDayDate = @date('Y-m-d', strtotime($selectedDateTime . '-1 day'));
  $PreviousDay = @date('D', strtotime($PreviousDayDate));
  $currentTime = @date("H:i:s", strtotime($selectedDateTime));
  $currentDay = @date("D", strtotime($selectedDateTime));
  }

  ## Checking For Previous Day NightSurge Charge For 0-5 am ##
  if ($currentTime > "00:00:00" && $currentTime <= "05:00:00" && $eNightStatus == "Active" && ($iRentalPackageId == 0 || ($iRentalPackageId != 0 && $ENABLE_SURGE_CHARGE_RENTAL == "Yes"))) {
  $previousnightStartTime_str = "t" . $PreviousDay . "NightStartTime";
  $previousnightEndTime_str = "t" . $PreviousDay . "NightEndTime";
  $fpreviousNightPrice_str = "f" . $PreviousDay . "NightPrice";
  $tNightSurgeData_PrevDay = get_value('vehicle_type', 'tNightSurgeData', 'iVehicleTypeId', $vehicleTypeID, '', 'true');
  $tNightSurgeDataPrevDayArr = json_decode($tNightSurgeData_PrevDay, true);
  if (count($tNightSurgeDataPrevDayArr) > 0) {
  $nightStartTime_PrevDay = $tNightSurgeDataPrevDayArr[$previousnightStartTime_str];
  $nightEndTime_PrevDay = $tNightSurgeDataPrevDayArr[$previousnightEndTime_str];
  $fNightPrice_PrevDay = $tNightSurgeDataPrevDayArr[$fpreviousNightPrice_str];
  if ($nightStartTime_PrevDay > "00:00:00" && $nightEndTime_PrevDay <= "05:00:00" && $fNightPrice_PrevDay > 1) {
  $returnArr['Action'] = "0";
  $returnArr['message'] = "LBL_NIGHT_SURGE_NOTE";
  $returnArr['SurgePrice'] = $fNightPrice_PrevDay . "X";
  $returnArr['SurgePriceValue'] = $fNightPrice_PrevDay;
  return $returnArr;
  }
  }
  }
  ## Checking For Previous Day NightSurge Charge For 0-5 am ##
  if ($ePickStatus == "Active" || $eNightStatus == "Active") {
  $startTime_str = "t" . $currentDay . "PickStartTime";
  $endTime_str = "t" . $currentDay . "PickEndTime";
  $price_str = "f" . $currentDay . "PickUpPrice";
  $pickStartTime = get_value('vehicle_type', $startTime_str, 'iVehicleTypeId', $vehicleTypeID, '', 'true');
  $pickEndTime = get_value('vehicle_type', $endTime_str, 'iVehicleTypeId', $vehicleTypeID, '', 'true');
  $fPickUpPrice = get_value('vehicle_type', $price_str, 'iVehicleTypeId', $vehicleTypeID, '', 'true');

  $nightStartTime_str = "t" . $currentDay . "NightStartTime";
  $nightEndTime_str = "t" . $currentDay . "NightEndTime";
  $fNightPrice_str = "f" . $currentDay . "NightPrice";
  $tNightSurgeData = get_value('vehicle_type', 'tNightSurgeData', 'iVehicleTypeId', $vehicleTypeID, '', 'true');
  $tNightSurgeDataArr = json_decode($tNightSurgeData, true);
  if (count($tNightSurgeDataArr) > 0) {
  $nightStartTime = $tNightSurgeDataArr[$nightStartTime_str];
  $nightEndTime = $tNightSurgeDataArr[$nightEndTime_str];
  $fNightPrice = $tNightSurgeDataArr[$fNightPrice_str];
  } else {
  $nightStartTime = "00:00:00";
  $nightEndTime = "00:00:00";
  $fNightPrice = 1;
  }
  $tempNightHour = "12:00:00";
  if ($currentTime > $pickStartTime && $currentTime < $pickEndTime && $ePickStatus == "Active") {
  $returnArr['Action'] = "0";
  $returnArr['message'] = "LBL_PICK_SURGE_NOTE";
  $returnArr['SurgePrice'] = $fPickUpPrice . "X";
  $returnArr['SurgePriceValue'] = $fPickUpPrice;
  }

  // else if ($currentTime > $nightStartTime && $currentTime < $nightEndTime && $eNightStatus == "Active") {
  else if ((($currentTime > $nightStartTime && $currentTime < $nightEndTime && $nightEndTime > $tempNightHour) || ($currentTime < $nightStartTime && $currentTime < $nightEndTime && $nightEndTime < $tempNightHour && $nightStartTime > $tempNightHour) || ($currentTime > $nightStartTime && $currentTime > $nightEndTime && $nightEndTime < $tempNightHour && $nightStartTime > $tempNightHour) || ($currentTime > $nightStartTime && $currentTime < $nightEndTime && $nightEndTime < $tempNightHour)) && $eNightStatus == "Active") {
  $returnArr['Action'] = "0";
  $returnArr['message'] = "LBL_NIGHT_SURGE_NOTE";
  $returnArr['SurgePrice'] = $fNightPrice . "X";
  $returnArr['SurgePriceValue'] = $fNightPrice;
  } else {
  $returnArr['Action'] = "1";
  }
  } else {
  $returnArr['Action'] = "1";
  }

  return $returnArr;
  }

  function checkmemberemailphoneverification($iMemberId, $user_type = "Passenger") {
  global $obj, $DRIVER_EMAIL_VERIFICATION, $DRIVER_PHONE_VERIFICATION, $RIDER_EMAIL_VERIFICATION, $RIDER_PHONE_VERIFICATION, $COMPANY_EMAIL_VERIFICATION, $COMPANY_PHONE_VERIFICATION;
  if ($user_type == "Driver") {

  $EMAIL_VERIFICATION = $DRIVER_EMAIL_VERIFICATION;
  $PHONE_VERIFICATION = $DRIVER_PHONE_VERIFICATION;
  $sqld = "SELECT eEmailVerified,ePhoneVerified FROM register_driver WHERE iDriverId = '" . $iMemberId . "'";
  $driverData = $obj->MySQLSelect($sqld);
  $eEmailVerified = $driverData[0]['eEmailVerified'];
  $ePhoneVerified = $driverData[0]['ePhoneVerified'];
  } else if ($user_type == "Company") {

  $EMAIL_VERIFICATION = $COMPANY_EMAIL_VERIFICATION;
  $PHONE_VERIFICATION = $COMPANY_PHONE_VERIFICATION;
  $sqld = "SELECT eEmailVerified,ePhoneVerified FROM company WHERE iCompanyId = '" . $iMemberId . "'";
  $companyData = $obj->MySQLSelect($sqld);
  $eEmailVerified = $companyData[0]['eEmailVerified'];
  $ePhoneVerified = $companyData[0]['ePhoneVerified'];
  } else {

  $EMAIL_VERIFICATION = $RIDER_EMAIL_VERIFICATION;
  $PHONE_VERIFICATION = $RIDER_PHONE_VERIFICATION;
  $sqld = "SELECT eEmailVerified,ePhoneVerified FROM register_user WHERE iUserId = '" . $iMemberId . "'";
  $driverData = $obj->MySQLSelect($sqld);
  //$eEmailVerified = $driverData[0]['eEmailVerified'];
  $eEmailVerified = "Yes";
  $ePhoneVerified = $driverData[0]['ePhoneVerified'];
  }

  $email = $EMAIL_VERIFICATION == "Yes" ? ($eEmailVerified == "Yes" ? "true" : "false") : "true";
  $phone = $PHONE_VERIFICATION == "Yes" ? ($ePhoneVerified == "Yes" ? "true" : "false") : "true";
  if ($email == "false" && $phone == "false") {
  $returnArr['Action'] = "0";
  $returnArr['message'] = "DO_EMAIL_PHONE_VERIFY";
  setDataResponse($returnArr);
  } else if ($email == "true" && $phone == "false") {
  $returnArr['Action'] = "0";
  $returnArr['message'] = "DO_PHONE_VERIFY";
  setDataResponse($returnArr);
  } else if ($email == "false" && $phone == "true") {
  $returnArr['Action'] = "0";
  $returnArr['message'] = "DO_EMAIL_VERIFY";
  setDataResponse($returnArr);
  }
  } */

/* function checkmemberemailphoneverification($iMemberId, $user_type = "Passenger") {
  global $obj, $DRIVER_EMAIL_VERIFICATION, $DRIVER_PHONE_VERIFICATION, $RIDER_EMAIL_VERIFICATION, $RIDER_PHONE_VERIFICATION, $COMPANY_EMAIL_VERIFICATION, $COMPANY_PHONE_VERIFICATION;
  if ($user_type == "Driver") {
  /* $EMAIL_VERIFICATION = get_value('configurations', 'vValue', 'vName', 'DRIVER_EMAIL_VERIFICATION', '', 'true');
  $PHONE_VERIFICATION = get_value('configurations', 'vValue', 'vName', 'DRIVER_PHONE_VERIFICATION', '', 'true');
  $eEmailVerified = get_value('register_driver', 'eEmailVerified', 'iDriverId', $iMemberId, '', 'true');
  $ePhoneVerified = get_value('register_driver', 'ePhoneVerified', 'iDriverId', $iMemberId, '', 'true');
  $EMAIL_VERIFICATION = $DRIVER_EMAIL_VERIFICATION;
  $PHONE_VERIFICATION = $DRIVER_PHONE_VERIFICATION;
  $sqld = "SELECT eEmailVerified,ePhoneVerified FROM register_driver WHERE iDriverId = '" . $iMemberId . "'";
  $driverData = $obj->MySQLSelect($sqld);
  $eEmailVerified = $driverData[0]['eEmailVerified'];
  $ePhoneVerified = $driverData[0]['ePhoneVerified'];
  } else if ($user_type == "Company") {
  $EMAIL_VERIFICATION = $COMPANY_EMAIL_VERIFICATION;
  $PHONE_VERIFICATION = $COMPANY_PHONE_VERIFICATION;
  $sqld = "SELECT eEmailVerified,ePhoneVerified FROM company WHERE iCompanyId = '" . $iMemberId . "'";
  $companyData = $obj->MySQLSelect($sqld);
  $eEmailVerified = $companyData[0]['eEmailVerified'];
  $ePhoneVerified = $companyData[0]['ePhoneVerified'];
  } else {
  $EMAIL_VERIFICATION = $RIDER_EMAIL_VERIFICATION;
  $PHONE_VERIFICATION = $RIDER_PHONE_VERIFICATION;
  $sqld = "SELECT eEmailVerified,ePhoneVerified FROM register_user WHERE iUserId = '" . $iMemberId . "'";
  $driverData = $obj->MySQLSelect($sqld);
  //$eEmailVerified = $driverData[0]['eEmailVerified'];
  $eEmailVerified = "Yes";
  $ePhoneVerified = $driverData[0]['ePhoneVerified'];
  }

  $email = $EMAIL_VERIFICATION == "Yes" ? ($eEmailVerified == "Yes" ? "true" : "false") : "true";
  $phone = $PHONE_VERIFICATION == "Yes" ? ($ePhoneVerified == "Yes" ? "true" : "false") : "true";
  if ($email == "false" && $phone == "false") {
  $returnArr['Action'] = "0";
  $returnArr['message'] = "DO_EMAIL_PHONE_VERIFY";
  setDataResponse($returnArr);
  exit;
  } else if ($email == "true" && $phone == "false") {
  $returnArr['Action'] = "0";
  $returnArr['message'] = "DO_PHONE_VERIFY";
  setDataResponse($returnArr);
  } else if ($email == "false" && $phone == "true") {
  $returnArr['Action'] = "0";
  $returnArr['message'] = "DO_EMAIL_VERIFY";
  setDataResponse($returnArr);
  }
  }

  function sendemailphoneverificationcode($iMemberId, $user_type = "Passenger", $VerifyType) {
  global $generalobj, $obj, $iServiceId;
  if ($user_type == "Passenger") {
  $tblname = "register_user";
  $fields = 'iUserId, vPhone,vPhoneCode as vPhoneCode, vEmail, vName, vLastName';
  $condfield = 'iUserId';
  $vLangCode = get_value('register_user', 'vLang', 'iUserId', $iMemberId, '', 'true');
  } else {
  $tblname = "register_driver";
  $fields = 'iDriverId, vPhone,vCode as vPhoneCode, vEmail, vName, vLastName';
  $condfield = 'iDriverId';
  $vLangCode = get_value('register_driver', 'vLang', 'iDriverId', $iMemberId, '', 'true');
  }

  if ($vLangCode == "" || $vLangCode == NULL) {
  $vLangCode = get_value('language_master', 'vCode', 'eDefault', 'Yes', '', 'true');
  }

  $languageLabelsArr = getLanguageLabelsArr($vLangCode, "1", $iServiceId);
  $str = "select * from send_message_templates where vEmail_Code='VERIFICATION_CODE_MESSAGE'";
  $res = $obj->MySQLSelect($str);
  $prefix = $res[0]['vBody_' . $vLangCode];

  // $prefix = $languageLabelsArr['LBL_VERIFICATION_CODE_TXT'];
  $emailmessage = "";
  $phonemessage = "";
  if ($VerifyType == "email" || $VerifyType == "both") {
  $sql = "select $fields from $tblname where $condfield = '" . $iMemberId . "'";
  $db_member = $obj->MySQLSelect($sql);
  $Data_Mail['vEmailVarificationCode'] = $random = substr(number_format(time() * rand(), 0, '', ''), 0, 4);
  $Data_Mail['vEmail'] = isset($db_member[0]['vEmail']) ? $db_member[0]['vEmail'] : '';
  $vFirstName = isset($db_member[0]['vName']) ? $db_member[0]['vName'] : '';
  $vLastName = isset($db_member[0]['vLastName']) ? $db_member[0]['vLastName'] : '';
  $Data_Mail['vName'] = $vFirstName . " " . $vLastName;
  $Data_Mail['CODE'] = $Data_Mail['vEmailVarificationCode'];
  $sendemail = $generalobj->send_email_user("APP_EMAIL_VERIFICATION_USER", $Data_Mail);
  if ($sendemail) {
  $emailmessage = $Data_Mail['vEmailVarificationCode'];
  } else {
  $emailmessage = "LBL_EMAIL_VERIFICATION_FAILED_TXT";
  }
  }

  if ($VerifyType == "phone" || $VerifyType == "both") {
  $sql = "select $fields from $tblname where $condfield = '" . $iMemberId . "'";
  $db_member = $obj->MySQLSelect($sql);
  $mobileNo = $db_member[0]['vPhoneCode'] . $db_member[0]['vPhone'];
  $toMobileNum = "+" . $mobileNo;
  $verificationCode = mt_rand(1000, 9999);
  $message = $prefix . ' ' . $verificationCode;
  $result = sendEmeSms($toMobileNum, $message);
  if ($result == 0) {
  $phonemessage = "LBL_MOBILE_VERIFICATION_FAILED_TXT";
  } else {
  $phonemessage = $verificationCode;
  }
  }

  $returnArr['emailmessage'] = $emailmessage;
  $returnArr['phonemessage'] = $phonemessage;
  return $returnArr;
  }


  function getUserRatingAverage($iMemberId, $eUserType = "Passenger") {
  global $obj, $generalobj;
  if ($eUserType == "Passenger") {
  $iUserId = "iDriverId";
  $checkusertype = "Passenger";
  } else if ($eUserType == "Company") {
  $iUserId = "iCompanyId";
  $checkusertype = "Company";
  } else {
  $iUserId = "iUserId";
  $checkusertype = "Driver";
  }

  $usertotaltrips = get_value("orders", "iOrderId", $iUserId, $iMemberId);
  if (count($usertotaltrips) > 0) {
  for ($i = 0; $i < count($usertotaltrips); $i++) {
  $iOrderId .= $usertotaltrips[$i]['iOrderId'] . ",";
  }

  $iOrderId_str = substr($iOrderId, 0, -1);

  // echo  $iTripId_str;exit;
  $sql = "SELECT count(iRatingId) as ToTalTrips, SUM(vRating1) as ToTalRatings from ratings_user_driver WHERE iOrderId IN (" . $iOrderId_str . ") AND eToUserType = '" . $checkusertype . "'";
  $result_ratings = $obj->MySQLSelect($sql);
  $ToTalTrips = $result_ratings[0]['ToTalTrips'];
  $ToTalRatings = $result_ratings[0]['ToTalRatings'];

  // $average_rating = round($ToTalRatings / $ToTalTrips, 2);
  $average_rating = round($ToTalRatings / $ToTalTrips, 1);
  } else {
  $average_rating = 0;
  }

  return $average_rating;
  } */

function addToCompanyRequest2($data) {
    global $obj;
    $data['dAddedDate'] = @date("Y-m-d H:i:s");
    $id = $obj->MySQLQueryPerform("company_request", $data, 'insert');
    return $id;
}

############### Get User   Company's status check in App   ###################################################################

function calculate_restaurant_time_span_old($iCompanyId, $iUserId) {
    global $obj, $generalobj, $tconfig, $vTimeZone, $iServiceId, $vCurrentTime;

    // date_default_timezone_set($vTimeZone);
    if ($vCurrentTime == "" || $vCurrentTime == NULL) {
        $vCurrentTime = @date("Y-m-d H:i:s");
    }

    $serverTimeZone = date_default_timezone_get();
    $returnArr = array();
    $sql = "SELECT * FROM `company` WHERE iCompanyId = '" . $iCompanyId . "'";
    $Datasql = $obj->MySQLSelect($sql);
    $eStatus = $Datasql[0]['eStatus'];
    $vCountry = $Datasql[0]['vCountry'];
    if ($vCountry == "" || $vCountry == NULL) {
        $vCountry = $DEFAULT_COUNTRY_CODE_WEB;
    }

    //$vTimeZone = get_value('country', 'vTimeZone', 'vCountryCode', $vCountry, '', 'true');
    date_default_timezone_set($vTimeZone);
    $vLanguage = get_value('register_user', 'vLang', 'iUserId', $iUserId, '', 'true');
    if ($vLanguage == "" || $vLanguage == NULL) {
        $vLanguage = "EN";
    }

    //$day = date("l");
    $day = date('l', strtotime($vCurrentTime));
    if ($day == "Sunday" || $day == "Saturday") {
        $vFromTimeSlot1 = "vFromSatSunTimeSlot1";
        $vFromTimeSlot2 = "vFromSatSunTimeSlot2";
        $vToTimeSlot1 = "vToSatSunTimeSlot1";
        $vToTimeSlot2 = "vToSatSunTimeSlot2";
    } else {
        $vFromTimeSlot1 = "vFromMonFriTimeSlot1";
        $vFromTimeSlot2 = "vFromMonFriTimeSlot2";
        $vToTimeSlot1 = "vToMonFriTimeSlot1";
        $vToTimeSlot2 = "vToMonFriTimeSlot2";
    }

    $languageLabelsArr = getLanguageLabelsArr($vLanguage, "1", $iServiceId);
    if ($Datasql[0][$vFromTimeSlot1] == "00:00:00" && $Datasql[0][$vToTimeSlot1] == "00:00:00" && $Datasql[0][$vFromTimeSlot2] == "00:00:00" && $Datasql[0][$vToTimeSlot2] == "00:00:00") {
        $returnArr['status'] = "Closed";
        $returnArr['opentime'] = "";
        $returnArr['closetime'] = "";
        $returnArr['restaurantstatus'] = "closed";
    } else {
        /* $vFromTimeSlot1 = strtotime($Datasql[0]['vFromTimeSlot1']);
          $vToTimeSlot1 = strtotime($Datasql[0]['vToTimeSlot1']);
          $vFromTimeSlot2 = strtotime($Datasql[0]['vFromTimeSlot2']);
          $vToTimeSlot2 = strtotime($Datasql[0]['vToTimeSlot2']); */
        if ($Datasql[0][$vToTimeSlot1] < $Datasql[0][$vFromTimeSlot1]) {
            $endTime = strtotime($Datasql[0][$vToTimeSlot1]);
            $vFromTimeSlot_1 = date(("H:i"), strtotime($Datasql[0][$vFromTimeSlot1]));
            $vToTimeSlot_1 = date(("H:i"), strtotime('+1 day', $endTime));
        } else {
            $vFromTimeSlot_1 = date(("H:i"), strtotime($Datasql[0][$vFromTimeSlot1]));
            $vToTimeSlot_1 = date(("H:i"), strtotime($Datasql[0][$vToTimeSlot1]));
        }

        if ($Datasql[0][$vToTimeSlot2] < $Datasql[0][$vFromTimeSlot2]) {
            $endTime2 = strtotime($Datasql[0][$vToTimeSlot2]);
            $vFromTimeSlot_2 = date(("H:i"), strtotime($Datasql[0][$vFromTimeSlot2]));
            $vToTimeSlot_2 = date(("H:i"), strtotime('+1 day', $endTime2));
        } else {
            $vFromTimeSlot_2 = date(("H:i"), strtotime($Datasql[0][$vFromTimeSlot2]));
            $vToTimeSlot_2 = date(("H:i"), strtotime($Datasql[0][$vToTimeSlot2]));
        }

        //$date = @date("H:i");
        $date = @date("H:i", strtotime($vCurrentTime));
        // $currenttime = strtotime($date);
        $status = "closed";
        $status_display = $languageLabelsArr['LBL_RESTAURANT_CLOSED_STATUS_TXT'];
        $opentime = "";
        $OpenAt = $languageLabelsArr['LBL_RESTAURANT_OPEN_TXT'];
        $closetime = "";
        $timeslotavailable = "No";
        if (isBetween($vFromTimeSlot_1, $vToTimeSlot_1, $date) == 1 || isBetween($vFromTimeSlot_2, $vToTimeSlot_2, $date) == 1) {
            $status = "open";
            $timeslotavailable = "Yes";
            $status_display = $languageLabelsArr['LBL_RESTAURANT_OPEN_STAUS_TXT'];
            $currentdate = @date("Y-m-d H:i:s");
            $enddate = @date("Y-m-d");
            if (isBetween($vFromTimeSlot_1, $vToTimeSlot_1, $date) == 1) {
                $enddate = $enddate . " " . $vToTimeSlot_1 . ":00";
            } else {
                $enddate = $enddate . " " . $vToTimeSlot_2 . ":00";
            }

            $datediff = strtotime($enddate) - strtotime($currentdate);
            if ($datediff < 900) {
                $closein = $languageLabelsArr['LBL_RESTAURANT_CLOSE_MINS_TXT'];
                $closemins = round($datediff / 60);
                $closetime = $closein . " " . $closemins . " " . $languageLabelsArr['LBL_MINS_SMALL'];
            }
        } else {
            $newdate = @date("Y-m-d");

            // $newdate = $newdate." ".$vFromTimeSlot_2.":00";
            if (isBetween($vFromTimeSlot_1, $vFromTimeSlot_1, $date) == 1) {
                $newdate = $newdate . " " . $vFromTimeSlot_1 . ":00";
            } else {
                if ($vFromTimeSlot_1 < $vFromTimeSlot_2 && $vFromTimeSlot_1 > $date) {
                    $newdate = $newdate . " " . $vFromTimeSlot_1 . ":00";
                } else {
                    $newdate = ($vFromTimeSlot_2 == "00:00") ? $newdate . " " . $vFromTimeSlot_1 . ":00" : $newdate . " " . $vFromTimeSlot_2 . ":00";
                }
            }

            $currentdate = @date("Y-m-d H:i:s");
            $datediff = strtotime($newdate) - strtotime($currentdate);
            if ($datediff > 0) {
                $opentime = $OpenAt . " " . date("h:i a", strtotime($newdate));
            }
        }

        $eAvailable = $Datasql[0]['eAvailable'];
        $eLogout = $Datasql[0]['eLogout'];
        if ($eAvailable == "No" || $eLogout == "Yes" || $eStatus != "Active") {
            $status_display = $languageLabelsArr['LBL_RESTAURANT_CLOSED_STATUS_TXT'];

            // $opentime = "";
            $closetime = "";
            $status = "closed";
        }

        $returnArr['status'] = $status_display;
        $returnArr['opentime'] = $opentime;
        $returnArr['closetime'] = $closetime;
        $returnArr['restaurantstatus'] = $status;
        $returnArr['timeslotavailable'] = $timeslotavailable;
    }

    // echo "<pre>";print_r($returnArr);
    //date_default_timezone_set($serverTimeZone);
    return $returnArr;
}

function calculate_restaurant_time_span($iCompanyId, $iUserId, $vLanguage = "", $languageLabelsArr = array(), $storeDetails = array()) {
    global $obj, $generalobj, $tconfig, $vTimeZone, $iServiceId, $vCurrentTime;
    // date_default_timezone_set($vTimeZone);
    if ($vCurrentTime == "" || $vCurrentTime == NULL) {
        $vCurrentTime = @date("Y-m-d H:i:s");
    }
    //$serverTimeZone = date_default_timezone_get();
    $vTimeZone = $_COOKIE['vUserDeviceTimeZone']; //change here var as $vTimeZone bc it will only assigned and then that var not used any where...so insteadof $serverTimeZone var, i have used $vTimeZone
    $returnArr = array();
    if (isset($storeDetails[$iCompanyId])) {
        //echo "<pre>";print_r($storeDetails[$iCompanyId]);die;
        $Datasql = $storeDetails[$iCompanyId];
    } else {
        $Datasql = $obj->MySQLSelect("SELECT iCompanyId,eStatus,vCountry,vFromSatSunTimeSlot1,vFromSatSunTimeSlot2,vToSatSunTimeSlot1,vToSatSunTimeSlot2,vFromMonFriTimeSlot1,vFromMonFriTimeSlot2,vToMonFriTimeSlot1,vToMonFriTimeSlot2,eAvailable,eLogout FROM `company` WHERE iCompanyId = '" . $iCompanyId . "'");
    }
    //echo "<pre>";print_r($Datasql);die;
    $eStatus = $Datasql[0]['eStatus'];
    $vCountry = $Datasql[0]['vCountry'];
    if ($vCountry == "" || $vCountry == NULL) {
        $vCountry = $DEFAULT_COUNTRY_CODE_WEB;
    }
    //$vTimeZone = get_value('country', 'vTimeZone', 'vCountryCode', $vCountry, '', 'true');
    date_default_timezone_set($vTimeZone);
    if ($vLanguage == "" || $vLanguage == NULL) {
        $vLanguage = get_value('register_user', 'vLang', 'iUserId', $iUserId, '', 'true');
        if ($vLanguage == "" || $vLanguage == NULL) {
            $vLanguage = "EN";
        }
    }

    //$day = date("l");
    $vCurrentTime = @date("Y-m-d H:i:s");
    $day = date('l', strtotime($vCurrentTime));
    if ($day == "Sunday" || $day == "Saturday") {
        $vFromTimeSlot1 = "vFromSatSunTimeSlot1";
        $vFromTimeSlot2 = "vFromSatSunTimeSlot2";
        $vToTimeSlot1 = "vToSatSunTimeSlot1";
        $vToTimeSlot2 = "vToSatSunTimeSlot2";
    } else {
        $vFromTimeSlot1 = "vFromMonFriTimeSlot1";
        $vFromTimeSlot2 = "vFromMonFriTimeSlot2";
        $vToTimeSlot1 = "vToMonFriTimeSlot1";
        $vToTimeSlot2 = "vToMonFriTimeSlot2";
    }
    if (count($languageLabelsArr) == 0) {
        $languageLabelsArr = getLanguageLabelsArr($vLanguage, "1", $iServiceId);
    }
    if ($Datasql[0][$vFromTimeSlot1] == "00:00:00" && $Datasql[0][$vToTimeSlot1] == "00:00:00" && $Datasql[0][$vFromTimeSlot2] == "00:00:00" && $Datasql[0][$vToTimeSlot2] == "00:00:00") {
        $returnArr['status'] = "Closed";
        $returnArr['opentime'] = "";
        $returnArr['closetime'] = "";
        $returnArr['restaurantstatus'] = "closed";
    } else {
        /* $vFromTimeSlot1 = strtotime($Datasql[0]['vFromTimeSlot1']);
          $vToTimeSlot1 = strtotime($Datasql[0]['vToTimeSlot1']);
          $vFromTimeSlot2 = strtotime($Datasql[0]['vFromTimeSlot2']);
          $vToTimeSlot2 = strtotime($Datasql[0]['vToTimeSlot2']); */
        if ($Datasql[0][$vToTimeSlot1] < $Datasql[0][$vFromTimeSlot1]) {
            $endTime = strtotime($Datasql[0][$vToTimeSlot1]);
            $vFromTimeSlot_1 = date(("H:i"), strtotime($Datasql[0][$vFromTimeSlot1]));
            $vToTimeSlot_1 = date(("H:i"), strtotime('+1 day', $endTime));
        } else {
            $vFromTimeSlot_1 = date(("H:i"), strtotime($Datasql[0][$vFromTimeSlot1]));
            $vToTimeSlot_1 = date(("H:i"), strtotime($Datasql[0][$vToTimeSlot1]));
        }

        if ($Datasql[0][$vToTimeSlot2] < $Datasql[0][$vFromTimeSlot2]) {
            $endTime2 = strtotime($Datasql[0][$vToTimeSlot2]);
            $vFromTimeSlot_2 = date(("H:i"), strtotime($Datasql[0][$vFromTimeSlot2]));
            $vToTimeSlot_2 = date(("H:i"), strtotime('+1 day', $endTime2));
        } else {
            $vFromTimeSlot_2 = date(("H:i"), strtotime($Datasql[0][$vFromTimeSlot2]));
            $vToTimeSlot_2 = date(("H:i"), strtotime($Datasql[0][$vToTimeSlot2]));
        }

        //$date = @date("H:i");
        $date = @date("H:i", strtotime($vCurrentTime));
        // $currenttime = strtotime($date);
        $status = "closed";
        $status_display = $languageLabelsArr['LBL_RESTAURANT_CLOSED_STATUS_TXT'];
        $opentime = "";
        $OpenAt = $languageLabelsArr['LBL_RESTAURANT_OPEN_TXT'];
        $closetime = "";
        $timeslotavailable = "No";
        if (isBetween($vFromTimeSlot_1, $vToTimeSlot_1, $date) == 1 || isBetween($vFromTimeSlot_2, $vToTimeSlot_2, $date) == 1) {
            $status = "open";
            $timeslotavailable = "Yes";
            $status_display = $languageLabelsArr['LBL_RESTAURANT_OPEN_STAUS_TXT'];
            $currentdate = @date("Y-m-d H:i:s");
            $enddate = @date("Y-m-d");
            if (isBetween($vFromTimeSlot_1, $vToTimeSlot_1, $date) == 1) {
                $enddate = $enddate . " " . $vToTimeSlot_1 . ":00";
            } else {
                $enddate = $enddate . " " . $vToTimeSlot_2 . ":00";
            }

            $datediff = strtotime($enddate) - strtotime($currentdate);
            if ($datediff < 900) {
                $closein = $languageLabelsArr['LBL_RESTAURANT_CLOSE_MINS_TXT'];
                $closemins = round($datediff / 60);
                $closetime = $closein . " " . $closemins . " " . $languageLabelsArr['LBL_MINS_SMALL'];
            }
        } else {
            $newdate = @date("Y-m-d");

            // $newdate = $newdate." ".$vFromTimeSlot_2.":00";
            if (isBetween($vFromTimeSlot_1, $vFromTimeSlot_1, $date) == 1) {
                $newdate = $newdate . " " . $vFromTimeSlot_1 . ":00";
            } else {
                if ($vFromTimeSlot_1 < $vFromTimeSlot_2 && $vFromTimeSlot_1 > $date) {
                    $newdate = $newdate . " " . $vFromTimeSlot_1 . ":00";
                } else {
                    $newdate = ($vFromTimeSlot_2 == "00:00") ? $newdate . " " . $vFromTimeSlot_1 . ":00" : $newdate . " " . $vFromTimeSlot_2 . ":00";
                }
            }

            $currentdate = @date("Y-m-d H:i:s");
            $datediff = strtotime($newdate) - strtotime($currentdate);
            if ($datediff > 0) {
                $opentime = $OpenAt . " " . date("h:i a", strtotime($newdate));
            }
        }

        $eAvailable = $Datasql[0]['eAvailable'];
        $eLogout = $Datasql[0]['eLogout'];
        if ($eAvailable == "No" || $eLogout == "Yes" || $eStatus != "Active") {
            $status_display = $languageLabelsArr['LBL_RESTAURANT_CLOSED_STATUS_TXT'];

            // $opentime = "";
            $closetime = "";
            $status = "closed";
        }

        $returnArr['status'] = $status_display;
        $returnArr['opentime'] = $opentime;
        $returnArr['closetime'] = $closetime;
        $returnArr['restaurantstatus'] = $status;
        $returnArr['timeslotavailable'] = $timeslotavailable;
    }

    // echo "<pre>";print_r($returnArr);
    //date_default_timezone_set($serverTimeZone);
    return $returnArr;
}

############### Get Company's status check in admin web  ###################################################################

function calculate_restaurant_time_span_web($iCompanyId, $iUserId) {
    global $obj, $generalobj, $tconfig, $vTimeZone, $iServiceId, $vCurrentTime;
    $vTimeZone = $_COOKIE['vUserDeviceTimeZone']; //change here var as $vTimeZone bc it will only assigned and then that var not used any where...so insteadof $serverTimeZone var, i have used $vTimeZone
    date_default_timezone_set($vTimeZone); // Added By HJ On 02-08-2019 For Solved Time Zone Issue Same As getcuisinelist Function For Solved Sheet Bug
    if ($vCurrentTime == "" || $vCurrentTime == NULL) {
        $vCurrentTime = @date("Y-m-d H:i:s");
    }

    //$serverTimeZone = date_default_timezone_get();
    $returnArr = array();
    $sql = "SELECT * FROM `company` WHERE iCompanyId = '" . $iCompanyId . "'";
    $Datasql = $obj->MySQLSelect($sql);
    $eStatus = $Datasql[0]['eStatus'];
    $vCountry = $Datasql[0]['vCountry'];
    if ($vCountry == "" || $vCountry == NULL) {
        $vCountry = $DEFAULT_COUNTRY_CODE_WEB;
    }

    //$vTimeZone = get_value('country', 'vTimeZone', 'vCountryCode', $vCountry, '', 'true');
    //date_default_timezone_set($vTimeZone);
    $vLanguage = get_value('register_user', 'vLang', 'iUserId', $iUserId, '', 'true');
    if ($vLanguage == "" || $vLanguage == NULL) {
        $vLanguage = "EN";
    }

    //$day = date("l");
    $day = date('l', strtotime($vCurrentTime));
    if ($day == "Sunday" || $day == "Saturday") {
        $vFromTimeSlot1 = "vFromSatSunTimeSlot1";
        $vFromTimeSlot2 = "vFromSatSunTimeSlot2";
        $vToTimeSlot1 = "vToSatSunTimeSlot1";
        $vToTimeSlot2 = "vToSatSunTimeSlot2";
    } else {
        $vFromTimeSlot1 = "vFromMonFriTimeSlot1";
        $vFromTimeSlot2 = "vFromMonFriTimeSlot2";
        $vToTimeSlot1 = "vToMonFriTimeSlot1";
        $vToTimeSlot2 = "vToMonFriTimeSlot2";
    }

    $languageLabelsArr = getLanguageLabelsArr($vLanguage, "1", $iServiceId);
    if (isset($Datasql[0][$vFromTimeSlot1]) && $Datasql[0][$vFromTimeSlot1] == "00:00:00" && $Datasql[0][$vToTimeSlot1] == "00:00:00" && $Datasql[0][$vFromTimeSlot2] == "00:00:00" && $Datasql[0][$vToTimeSlot2] == "00:00:00") {

        $returnArr['status'] = "Closed";
        $returnArr['opentime'] = "";
        $returnArr['closetime'] = "";
        $returnArr['restaurantstatus'] = "closed";
    } else {
        /* $vFromTimeSlot1 = strtotime($Datasql[0]['vFromTimeSlot1']);
          $vToTimeSlot1 = strtotime($Datasql[0]['vToTimeSlot1']);
          $vFromTimeSlot2 = strtotime($Datasql[0]['vFromTimeSlot2']);
          $vToTimeSlot2 = strtotime($Datasql[0]['vToTimeSlot2']); */
        if ($Datasql[0][$vToTimeSlot1] < $Datasql[0][$vFromTimeSlot1]) {
            $endTime = strtotime($Datasql[0][$vToTimeSlot1]);
            $vFromTimeSlot_1 = date(("H:i"), strtotime($Datasql[0][$vFromTimeSlot1]));
            $vToTimeSlot_1 = date(("H:i"), strtotime('+1 day', $endTime));
        } else {
            $vFromTimeSlot_1 = date(("H:i"), strtotime($Datasql[0][$vFromTimeSlot1]));
            $vToTimeSlot_1 = date(("H:i"), strtotime($Datasql[0][$vToTimeSlot1]));
        }

        if ($Datasql[0][$vToTimeSlot2] < $Datasql[0][$vFromTimeSlot2]) {
            $endTime2 = strtotime($Datasql[0][$vToTimeSlot2]);
            $vFromTimeSlot_2 = date(("H:i"), strtotime($Datasql[0][$vFromTimeSlot2]));
            $vToTimeSlot_2 = date(("H:i"), strtotime('+1 day', $endTime2));
        } else {
            $vFromTimeSlot_2 = date(("H:i"), strtotime($Datasql[0][$vFromTimeSlot2]));
            $vToTimeSlot_2 = date(("H:i"), strtotime($Datasql[0][$vToTimeSlot2]));
        }

        //$date = @date("H:i");
        $date = @date("H:i", strtotime($vCurrentTime));
        // $currenttime = strtotime($date);
        $status = "closed";
        $status_display = $languageLabelsArr['LBL_RESTAURANT_CLOSED_STATUS_TXT'];
        $opentime = "";
        $OpenAt = $languageLabelsArr['LBL_RESTAURANT_OPEN_TXT'];
        $closetime = "";
        $timeslotavailable = "No";
        if (isBetween($vFromTimeSlot_1, $vToTimeSlot_1, $date) == 1 || isBetween($vFromTimeSlot_2, $vToTimeSlot_2, $date) == 1) {
            $status = "open";
            $timeslotavailable = "Yes";
            $status_display = $languageLabelsArr['LBL_RESTAURANT_OPEN_STAUS_TXT'];
            $currentdate = @date("Y-m-d H:i:s");
            $enddate = @date("Y-m-d");
            if (isBetween($vFromTimeSlot_1, $vToTimeSlot_1, $date) == 1) {
                $enddate = $enddate . " " . $vToTimeSlot_1 . ":00";
            } else {
                $enddate = $enddate . " " . $vToTimeSlot_2 . ":00";
            }

            $datediff = strtotime($enddate) - strtotime($currentdate);
            if ($datediff < 900) {
                $closein = $languageLabelsArr['LBL_RESTAURANT_CLOSE_MINS_TXT'];
                $closemins = round($datediff / 60);
                $closetime = $closein . " " . $closemins . " " . $languageLabelsArr['LBL_MINS_SMALL'];
            }
        } else {
            $newdate = @date("Y-m-d");

            // $newdate = $newdate." ".$vFromTimeSlot_2.":00";
            if (isBetween($vFromTimeSlot_1, $vFromTimeSlot_1, $date) == 1) {
                $newdate = $newdate . " " . $vFromTimeSlot_1 . ":00";
            } else {
                if ($vFromTimeSlot_1 < $vFromTimeSlot_2 && $vFromTimeSlot_1 > $date) {
                    $newdate = $newdate . " " . $vFromTimeSlot_1 . ":00";
                } else {
                    $newdate = ($vFromTimeSlot_2 == "00:00") ? $newdate . " " . $vFromTimeSlot_1 . ":00" : $newdate . " " . $vFromTimeSlot_2 . ":00";
                }
            }

            $currentdate = @date("Y-m-d H:i:s");
            $datediff = strtotime($newdate) - strtotime($currentdate);
            if ($datediff > 0) {
                $opentime = $OpenAt . " " . date("h:i a", strtotime($newdate));
            }
        }

        $eAvailable = $Datasql[0]['eAvailable'];
        $eLogout = $Datasql[0]['eLogout'];
        if ($eAvailable == "No" || $eLogout == "Yes" || $eStatus != "Active") {
            $status_display = $languageLabelsArr['LBL_RESTAURANT_CLOSED_STATUS_TXT'];

            // $opentime = "";
            $closetime = "";
            $status = "closed";
        }
        $returnArr['status'] = $status_display;
        $returnArr['opentime'] = $opentime;
        $returnArr['closetime'] = $closetime;
        $returnArr['restaurantstatus'] = $status;
        $returnArr['timeslotavailable'] = $timeslotavailable;
    }

    // echo "<pre>";print_r($returnArr);
    //date_default_timezone_set($serverTimeZone);
    return $returnArr;
}

function isBetween($from, $till, $input) {
    $f = DateTime::createFromFormat('!H:i', $from);
    $t = DateTime::createFromFormat('!H:i', $till);
    $i = DateTime::createFromFormat('!H:i', $input);
    if ($f > $t)
        $t->modify('+1 day');


    return ($f <= $i && $i <= $t) || ($f <= $i->modify('+1 day') && $i <= $t);
}

/* ==================================================================================================== */

function getStoreDetails($storeIds, $userId, $iToLocationId, $languageLabelsArr) {
    global $obj, $generalobj, $tconfig;
    //echo "<pre>";
    $vLanguage = "EN";
    $storePrepareTimeArr = array();
    //$languageLabelsArr = getLanguageLabelsArr($vLanguage, "1", $iServiceId);
    $storeData = getcuisinelist($storeIds, $userId, $languageLabelsArr);
    return $storeData;
}

function getCompanyDetails($iCompanyId, $iUserId, $CheckNonVegFoodType = "", $searchword = "", $iServiceId = "", $passengerLat = "", $passengerLon = "") {
    global $obj, $generalobj, $tconfig;
    if ($iUserId != "") {
        $sqlp = "SELECT ru.vCurrencyPassenger,ru.vLang,cu.vSymbol,cu.Ratio FROM register_user as ru LEFT JOIN currency as cu ON ru.vCurrencyPassenger = cu.vName WHERE iUserId = '" . $iUserId . "'";
        $passengerData = $obj->MySQLSelect($sqlp);
        $currencycode = $passengerData[0]['vCurrencyPassenger'];
        $vLanguage = $passengerData[0]['vLang'];
        $currencySymbol = $passengerData[0]['vSymbol'];
        $Ratio = $passengerData[0]['Ratio'];
        if ($vLanguage == "" || $vLanguage == NULL) {
            $vLanguage = get_value('language_master', 'vCode', 'eDefault', 'Yes', '', 'true');
        }
        if ($currencycode == "" || $currencycode == NULL) {
            $sqlp = "SELECT vName,vSymbol,Ratio FROM currency WHERE eDefault = 'Yes'";
            $currencyData = $obj->MySQLSelect($sqlp);
            $currencycode = $currencyData[0]['vName'];
            $currencySymbol = $currencyData[0]['vSymbol'];
            $Ratio = $currencyData[0]['Ratio'];
        }
    } else {
        $vLanguage = get_value('language_master', 'vCode', 'eDefault', 'Yes', '', 'true');
        $sqlp = "SELECT vName,vSymbol,Ratio FROM currency WHERE eDefault = 'Yes'";
        $currencyData = $obj->MySQLSelect($sqlp);
        $currencycode = $currencyData[0]['vName'];
        $currencySymbol = $currencyData[0]['vSymbol'];
        $Ratio = $currencyData[0]['Ratio'];
    }
    if (isset($_SESSION['sess_lang'])) {
        $vLanguage = $_SESSION['sess_lang'];
    }
    $languageLabelsArr = getLanguageLabelsArr($vLanguage, "1", $iServiceId);
    $LBL_PER_PERSON_TXT = $languageLabelsArr['LBL_PER_PERSON_TXT'];
    $ssql_fav_q = "";
    if (checkFavStoreModule() && !empty($iUserId)) {
        include_once('include/features/include_fav_store.php');
        $ssql_fav_q = getFavSelectQuery($iCompanyId, $iUserId);
    }
    $sql = "SELECT * " . $ssql_fav_q . " FROM `company` WHERE iCompanyId = '" . $iCompanyId . "'";
    $DataCompany = $obj->MySQLSelect($sql);
    //Added By HJ On 04-02-2019 For Convert All Amount In Store Wise Currency Ratio Start
    /* $priceRatio = 1;
      if (isset($DataCompany[0]['store_currency'])) {
      $store_currency = $DataCompany[0]['store_currency'];
      if (isset($currencyArr[$store_currency])) {
      $priceRatio = $currencyArr[$store_currency];
      $currencySymbol = $currencySymbolArr[$store_currency];
      }
      }
      $Ratio = $priceRatio; */
    //Added By HJ On 04-02-2019 For Convert All Amount In Store Wise Currency Ratio End
    if (isset($DataCompany[0]['fPricePerPerson'])) {
        $personprice = $DataCompany[0]['fPricePerPerson'];
        $PersonPrice = $generalobj->setTwoDecimalPoint($personprice * $Ratio);
        $returnArr['fPricePerPersonWithCurrency'] = $currencySymbol . " " . $generalobj->setTwoDecimalPoint($PersonPrice);
    }
    $fPricePerPerson = $DataCompany[0]['fPricePerPerson'];
    $fPricePerPerson = $generalobj->setTwoDecimalPoint($fPricePerPerson * $Ratio);
    $fPricePerPerson = $currencySymbol . "" . $fPricePerPerson . " " . $LBL_PER_PERSON_TXT;
    $returnArr['Restaurant_PricePerPerson'] = $fPricePerPerson;
    $CompanyTimeSlot = getCompanyTimeSlot($iCompanyId, $languageLabelsArr);
    $returnArr['monfritimeslot_TXT'] = $CompanyTimeSlot['monfritimeslot_TXT'];
    $returnArr['monfritimeslot_Time'] = $CompanyTimeSlot['monfritimeslot_Time_new'];
    $returnArr['satsuntimeslot_TXT'] = $CompanyTimeSlot['satsuntimeslot_TXT'];
    $returnArr['satsuntimeslot_Time'] = $CompanyTimeSlot['satsuntimeslot_Time_new'];

    // echo "<pre>";print_r($CompanyTimeSlot);exit;
    $sql = "SELECT cu.cuisineName_" . $vLanguage . " as cuisineName,cu.cuisineId FROM cuisine as cu LEFT JOIN company_cuisine as ccu ON ccu.cuisineId=cu.cuisineId WHERE ccu.iCompanyId = '" . $iCompanyId . "' AND cu.eStatus = 'Active'";
    $db_cuisine = $obj->MySQLSelect($sql);
    $db_cuisine_str = $db_cuisine_id_str = $MaxDiscountAmount = "";
    if (count($db_cuisine) > 0) {
        for ($i = 0; $i < count($db_cuisine); $i++) {
            $db_cuisine_str .= $db_cuisine[$i]['cuisineName'] . ", ";
            $db_cuisine_id_str .= $db_cuisine[$i]['cuisineId'] . ",";
        }
        $db_cuisine_str = trim($db_cuisine_str, ", ");
        $db_cuisine_id_str = trim($db_cuisine_id_str, ",");
    }

    $returnArr['Restaurant_Cuisine'] = $db_cuisine_str;
    $returnArr['Restaurant_Cuisine_Id'] = $db_cuisine_id_str;
    $LBL_MINS_SMALL = $languageLabelsArr['LBL_MINS_SMALL'];
    $fPrepareTime = $DataCompany[0]['fPrepareTime'];
    $fPrepareTime = $fPrepareTime . " " . $LBL_MINS_SMALL;
    $returnArr['Restaurant_OrderPrepareTime'] = $fPrepareTime;
    $fOfferType = $DataCompany[0]['fOfferType'];
    $fOfferAppyType = $DataCompany[0]['fOfferAppyType'];
    $fOfferAmt = $DataCompany[0]['fOfferAmt'];
    $fTargetAmt = $DataCompany[0]['fTargetAmt'];
    $fTargetAmt = $generalobj->setTwoDecimalPoint($fTargetAmt * $Ratio);
    $fMaxOfferAmt = $DataCompany[0]['fMaxOfferAmt'];
    $fMaxOfferAmt = $generalobj->setTwoDecimalPoint($fMaxOfferAmt * $Ratio);
    $MaxDiscountAmount = "";
    if ($fMaxOfferAmt > 0) {
        $MaxDiscountAmount = " ( " . $languageLabelsArr['LBL_MAX_DISCOUNT_TXT'] . " " . $currencySymbol . "" . $fMaxOfferAmt . " )";
    }
    $TargerAmountTXT = $languageLabelsArr['LBL_OFF_TXT'];
    $ALL_ORDER_TXT = $languageLabelsArr['LBL_ALL_ORDER_TXT'];
    if ($fTargetAmt > 0) {
        $TargerAmountTXT = $languageLabelsArr['LBL_OFF_TXT'] . " " . $languageLabelsArr['LBL_ORDERS_ABOVE_TXT'] . " " . $currencySymbol . "" . $fTargetAmt . " ";
        $ALL_ORDER_TXT = "";
    }
    $offermsg = $offermsg_short = "";
    if ($fOfferType == "Percentage") {
        if ($fOfferAppyType == "First") {
            $offermsg = $languageLabelsArr['LBL_GET_TXT'] . " " . $fOfferAmt . "% " . $TargerAmountTXT . $languageLabelsArr['LBL_FIRST_ORDER_TXT'] . "" . $MaxDiscountAmount;
            $offermsg_short = $languageLabelsArr['LBL_GET_TXT'] . " " . $fOfferAmt . "% " . $TargerAmountTXT . " " . $languageLabelsArr['LBL_FIRST_ORDER_TXT'];
        } elseif ($fOfferAppyType == "All") {
            $offermsg = $languageLabelsArr['LBL_GET_TXT'] . " " . $fOfferAmt . "% " . $TargerAmountTXT . " " . $ALL_ORDER_TXT . " " . $MaxDiscountAmount;
            $offermsg_short = $languageLabelsArr['LBL_GET_TXT'] . " " . $fOfferAmt . "% " . $TargerAmountTXT . " " . $ALL_ORDER_TXT;
        }
    } else {
        $fOfferAmt = $generalobj->setTwoDecimalPoint($fOfferAmt * $Ratio);
        $DiscountAmount = $currencySymbol . "" . $fOfferAmt;
        if ($fOfferAppyType == "First" && $fOfferAmt > 0) {
            $offermsg = $languageLabelsArr['LBL_GET_TXT'] . " " . $DiscountAmount . " " . $TargerAmountTXT . " " . $languageLabelsArr['LBL_FIRST_ORDER_TXT'];
            $offermsg_short = $offermsg;
        } else if ($fOfferAppyType == "All" && $fOfferAmt > 0) {
            $offermsg = $languageLabelsArr['LBL_GET_TXT'] . " " . $DiscountAmount . " " . $TargerAmountTXT . " " . $ALL_ORDER_TXT;
            $offermsg_short = $offermsg;
        }
    }
    $returnArr['Restaurant_OfferMessage'] = $offermsg;
    $returnArr['Restaurant_OfferMessage_short'] = $offermsg_short;
    $fMinOrderValue = $DataCompany[0]['fMinOrderValue'];
    $fMinOrderValue = $generalobj->setTwoDecimalPoint($fMinOrderValue * $Ratio);
    $returnArr['fMinOrderValueDisplay'] = $currencySymbol . " " . $fMinOrderValue;
    $returnArr['fMinOrderValue'] = $fMinOrderValue;
    $returnArr['Restaurant_MinOrderValue'] = ($fMinOrderValue > 0) ? $currencySymbol . $fMinOrderValue . " " . $languageLabelsArr['LBL_MIN_ORDER_TXT'] : $languageLabelsArr['LBL_NO_MIN_ORDER_TXT'];
    $fPackingCharge = $DataCompany[0]['fPackingCharge'];
    $fPackingCharge = $generalobj->setTwoDecimalPoint($fPackingCharge * $Ratio);
    $returnArr['fPackingCharge'] = $fPackingCharge;

    // echo "<pre>";print_r($returnArr);
    ## Check NonVeg Item Available of Restaaurant ##
    $eNonVegToggleDisplay = "No";
    $sql = "SELECT eFoodType,mi.eStatus,mi.eAvailable,mi.iFoodMenuId FROM menu_items as mi LEFT JOIN food_menu as fm ON fm.iFoodMenuId=mi.iFoodMenuId WHERE fm.iCompanyId = '" . $iCompanyId . "' AND fm.eStatus='Active' AND mi.eStatus='Active'";
    $db_foodtype_data = $obj->MySQLSelect($sql);
    $TotNonVegItems = $TotVegItems = 0;
    $foodItemCountArr = array();
    //echo "<pre>";print_r($db_foodtype_data);die;
    for ($r = 0; $r < count($db_foodtype_data); $r++) {
        $eFoodType = strtoupper($db_foodtype_data[$r]['eFoodType']);
        $iFoodMenuId = $db_foodtype_data[$r]['iFoodMenuId'];
        $eStatus = $db_foodtype_data[$r]['eStatus'];
        $eAvailable = $db_foodtype_data[$r]['eAvailable'];
        if ($eFoodType == "NONVEG") {
            $TotNonVegItems + 1;
        } else if ($eFoodType == "VEG") {
            $TotVegItems + 1;
        }
        if ($eStatus == "Active" && $eAvailable == "Yes") {
            if (isset($foodItemCountArr[$iFoodMenuId])) {
                $foodItemCountArr[$iFoodMenuId] += 1;
            } else {
                $foodItemCountArr[$iFoodMenuId] = 1;
            }
        }
    }
    /* $TotNonVegItems = $db_foodtype_data[0]['TotNonVegItems'];
      $sql = "SELECT count(mi.iMenuItemId) As TotVegItems FROM menu_items as mi LEFT JOIN food_menu as fm ON fm.iFoodMenuId=mi.iFoodMenuId WHERE fm.iCompanyId = '" . $iCompanyId . "' AND fm.eStatus='Active' AND mi.eStatus='Active' AND mi.eFoodType = 'Veg'";
      $db_vegfoodtype_data = $obj->MySQLSelect($sql);
      $TotVegItems = $db_vegfoodtype_data[0]['TotVegItems']; */
    if ($TotNonVegItems > 0 && $TotVegItems > 0) {
        $eNonVegToggleDisplay = "Yes";
    }
    $returnArr['eNonVegToggleDisplay'] = $eNonVegToggleDisplay;
    ## Check NonVeg Item Available of Restaaurant ##
    ## Get Company Rattings ##
    $rsql = "SELECT count(r.iRatingId) as totalratings FROM orders as o LEFT JOIN ratings_user_driver as r on r.iOrderId=o.iOrderId WHERE o.iCompanyId='" . $iCompanyId . "' AND r.eFromUserType='Passenger' AND r.eToUserType='Company'";
    $Rating_data = $obj->MySQLSelect($rsql);
    $ratingcounts = $Rating_data[0]['totalratings'];
    if ($ratingcounts <= 100) {
        $ratings = $ratingcounts . " " . $languageLabelsArr['LBL_RATING'];
    } else {
        $ratings = $ratingcounts . "+ " . $languageLabelsArr['LBL_RATING'];
    }
    $returnArr['RatingCounts'] = $ratings;
    ## End Get Company Rattings ##
    ## Get Company's menu details ##
    // $sql = "SELECT * FROM food_menu WHERE iCompanyId = '".$iCompanyId."' AND eStatus='Active' ORDER BY iDisplayOrder ASC";
    $sql = "SELECT fm.* FROM food_menu as fm WHERE fm.iCompanyId = '" . $iCompanyId . "' AND fm.eStatus='Active' ORDER BY fm.iDisplayOrder ASC";
    $db_food_data = $obj->MySQLSelect($sql);
    $CompanyFoodData = $MenuItemsDataArr = array();
    if (count($db_food_data) > 0) {
        $ssql = "";
        if ($CheckNonVegFoodType == "Veg") {
            $ssql .= " AND (eFoodType = 'Veg' OR eFoodType = '')";
        } else if ($CheckNonVegFoodType == "NonVeg") {
            $ssql .= " AND (eFoodType = 'NonVeg' OR eFoodType = '')";
        } else if ($CheckNonVegFoodType == "VegNonVeg") {
            $ssql .= " AND (eFoodType = 'NonVeg' OR eFoodType = 'Veg' OR eFoodType = '') ";
        } else if ($CheckNonVegFoodType == "NonVegVeg") {
            $ssql .= " AND (eFoodType = 'NonVeg' OR eFoodType = 'Veg' OR eFoodType = '') ";
        }
        if ($searchword != "") {
            $ssql .= " AND LOWER(vItemType_" . $vLanguage . ") LIKE '%" . $searchword . "%' ";
        }
        $foodMenuIteIds = "";
        for ($h = 0; $h < count($db_food_data); $h++) {
            $foodMenuIteIds .= ",'" . $db_food_data[$h]['iFoodMenuId'] . "'";
        }
        $foodItemArr = $menuItemArr = $topingArr = array();
        if ($foodMenuIteIds != "") {
            $foodItems = trim($foodMenuIteIds, ",");
            //$sqlf = "SELECT eRecommended,iMenuItemId,iFoodMenuId,vItemType_" . $vLanguage . " as vItemType,vItemDesc_" . $vLanguage . " as vItemDesc,fPrice,eFoodType,fOfferAmt,vImage,iDisplayOrder,vHighlightName FROM menu_items WHERE iFoodMenuId IN ($foodItems) AND eStatus='Active' AND eAvailable = 'Yes' $ssql ORDER BY iDisplayOrder ASC";
            $def_lang = get_value('language_master', 'vCode', 'eDefault', 'Yes', '', 'true');
            $sqlf = "SELECT eRecommended,iMenuItemId,iFoodMenuId,IFNULL(NULLIF(vItemType_" . $vLanguage . ", ''),vItemType_" . $def_lang . ") as vItemType,vItemDesc_" . $vLanguage . " as vItemDesc,fPrice,eFoodType,fOfferAmt,vImage,iDisplayOrder,vHighlightName FROM menu_items WHERE iFoodMenuId IN ($foodItems) AND eStatus='Active' AND eAvailable = 'Yes' $ssql ORDER BY iDisplayOrder ASC";
            $dbItemData = $obj->MySQLSelect($sqlf);
            //echo "<pre>";print_r($dbItemData);die;
            for ($d = 0; $d < count($dbItemData); $d++) {
                //Added By HJ On 17-10-2019 For Get Highlight Label Value Start
                $vHighlightNameLBL = $dbItemData[$d]['vHighlightName'];
                if (isset($languageLabelsArr[$dbItemData[$d]['vHighlightName']]) && $dbItemData[$d]['vHighlightName'] != "" && $dbItemData[$d]['vHighlightName'] != null) {
                    $vHighlightNameLBL = $languageLabelsArr[$dbItemData[$d]['vHighlightName']];
                }
                $dbItemData[$d]['vHighlightNameLBL'] = $vHighlightNameLBL;
                //Added By HJ On 17-10-2019 For Get Highlight Label Value End
                $foodItemArr[$dbItemData[$d]['iFoodMenuId']][] = $dbItemData[$d];
                $menuItemArr[] = $dbItemData[$d]['iMenuItemId'];
            }
        }
        if (count($menuItemArr) > 0) {
            $itemIds = implode(",", $menuItemArr);
            $topingArr = GetMenuItemOptionsTopping($itemIds, $currencySymbol, $Ratio, $vLanguage);
            //echo "<pre>";print_r($topingArr);die;
            //echo $itemIds . "<br>";
            $customerTopingArr = getMenuCustomeAllToppings($itemIds, $currencySymbol, $Ratio, $vLanguage, 0);
            //echo "<pre>";print_r($customerTopingArr);die;
        }
        $itemimimgUrl = $tconfig["tsite_upload_images_menu_item"];
        for ($i = 0; $i < count($db_food_data); $i++) {
            $iFoodMenuId = $db_food_data[$i]['iFoodMenuId'];
            if (isset($foodItemCountArr[$iFoodMenuId]) && $foodItemCountArr[$iFoodMenuId] > 0) {
                $vMenu = $db_food_data[$i]['vMenu_' . $vLanguage];
                $CompanyFoodData[$i]['iFoodMenuId'] = $iFoodMenuId;
                $CompanyFoodData[$i]['vMenu'] = $vMenu;
                $CompanyFoodData[$i]['vMenuItemCount'] = 0;
                if (isset($foodItemArr[$iFoodMenuId])) {
                    $db_item_data = $foodItemArr[$iFoodMenuId];
                    $CompanyFoodData[$i]['vMenuItemCount'] = count($db_item_data);
                    if (count($db_item_data) > 0) {
                        for ($j = 0; $j < count($db_item_data); $j++) {
                            $db_item_data[$j]['vCategoryName'] = '';
                            if (!empty($vMenu)) {
                                $db_item_data[$j]['vCategoryName'] = $vMenu;
                            }
                            $iMenuItemId = $db_item_data[$j]['iMenuItemId'];
                            $MenuItemPriceArr = getMenuItemPriceByCompanyOffer($iMenuItemId, $iCompanyId, 1, $iUserId, "Display", "", "", $iServiceId);
                            $fPrice = $generalobj->setTwoDecimalPoint($MenuItemPriceArr['fPrice'] * $Ratio);
                            $fOfferAmt = $generalobj->setTwoDecimalPoint($MenuItemPriceArr['fOfferAmt']);
                            $db_item_data[$j]['fOfferAmt'] = $fOfferAmt;
                            $db_item_data[$j]['fPrice'] = $generalobj->setTwoDecimalPoint($db_item_data[$j]['fPrice'] * $Ratio);
                            if ($fOfferAmt > 0) {
                                $fDiscountPrice = $generalobj->setTwoDecimalPoint($MenuItemPriceArr['fPrice'] * $Ratio);
                                $StrikeoutPrice = $generalobj->setTwoDecimalPoint($MenuItemPriceArr['fOriginalPrice'] * $Ratio);
                                $db_item_data[$j]['StrikeoutPrice'] = $currencySymbol . " " . $generalobj->setTwoDecimalPoint($StrikeoutPrice);
                                $db_item_data[$j]['fDiscountPrice'] = $generalobj->setTwoDecimalPoint($fDiscountPrice);
                                $db_item_data[$j]['fDiscountPricewithsymbol'] = $currencySymbol . " " . $generalobj->setTwoDecimalPoint($fDiscountPrice);
                                $db_item_data[$j]['currencySymbol'] = $currencySymbol;
                            } else {
                                $db_item_data[$j]['StrikeoutPrice'] = $currencySymbol . " " . $generalobj->setTwoDecimalPoint($fPrice);
                                $db_item_data[$j]['fDiscountPrice'] = $generalobj->setTwoDecimalPoint($fPrice);
                                $db_item_data[$j]['fDiscountPricewithsymbol'] = $currencySymbol . " " . $generalobj->setTwoDecimalPoint($fPrice);
                                $db_item_data[$j]['currencySymbol'] = $currencySymbol;
                            }
                            $itemimgpth = $tconfig["tsite_upload_images_menu_item_path"] . '/' . $db_item_data[$j]['vImage'];
                            if ($db_item_data[$j]['vImage'] != "" && file_exists($itemimgpth)) {
                                $db_item_data[$j]['vImageName'] = $db_item_data[$j]['vImage'];
                                $db_item_data[$j]['vImage'] = $itemimimgUrl . '/' . $db_item_data[$j]['vImage'];
                            } else {
                                $db_item_data[$j]['vImageName'] = '';
                                $db_item_data[$j]['vImage'] = $itemimimgUrl . '/sample_image.png';
                            }
                            //$MenuItemOptionToppingArr = GetMenuItemOptionsTopping($iMenuItemId, $currencySymbol, $Ratio, $vLanguage, $iServiceId);
                            $MenuItemOptionToppingArr = $customeToppings = array();
                            if (isset($topingArr[$iMenuItemId])) {
                                $MenuItemOptionToppingArr = $topingArr[$iMenuItemId];
                            }
                            $db_item_data[$j]['MenuItemOptionToppingArr'] = $MenuItemOptionToppingArr;
                            //Added By HJ On 25-01-2019 For Get Custome Topping Data Start
                            //$customeToppings = getMenuCustomeToppings($iMenuItemId, $currencySymbol, $Ratio, $vLanguage, 0); //Commnted By HJ On 08-05-2019 For Optimize Code
                            if (isset($customerTopingArr[$iMenuItemId])) {
                                $customeToppings = $customerTopingArr[$iMenuItemId];
                            }
                            $db_item_data[$j]['MenuItemOptionToppingArr']['customItemArray'] = $customeToppings;
                            //Added By HJ On 25-01-2019 For Get Custome Topping Data End
                            // echo "<pre>";print_r($MenuItemOptionToppingArr);exit;
                            $CompanyFoodData[$i]['menu_items'][] = $db_item_data[$j];
                            array_push($MenuItemsDataArr, $db_item_data[$j]);
                        }
                    }
                }
            }
        }
    }

    $CompanyFoodData_New = array();
    $CompanyFoodData = array_values($CompanyFoodData);
    $CompanyFoodData_New = $CompanyFoodData;
    for ($i = 0; $i < count($CompanyFoodData); $i++) {
        //echo "<pre>";print_r($CompanyFoodData);die;
        $vMenuItemCount = $CompanyFoodData[$i]['vMenuItemCount'];
        if ($vMenuItemCount == 0) {
            unset($CompanyFoodData_New[$i]);
        }
    }

    $CompanyFoodData = array_values($CompanyFoodData_New);
    $returnArr['CompanyFoodData'] = $CompanyFoodData;
    $returnArr['CompanyFoodDataCount'] = count($CompanyFoodData);
    $returnArr['MenuItemsDataArr'] = array();
    if ($searchword != "") {
        $returnArr['MenuItemsDataArr'] = $MenuItemsDataArr;
    }
    $Recomendation_Arr = getRecommendedBestSellerMenuItems($iCompanyId, $iUserId, "Recommended", $CheckNonVegFoodType, $searchword, $iServiceId, $vLanguage);
    //print_r($Recomendation_Arr);die;
    $returnArr['Recomendation_Arr'] = $Recomendation_Arr;
    ## Get Company's menu details ##
    return $returnArr;
}

function getCompanyOffer($iCompanyId, $iUserId, $fOfferAppyType, $fOfferType, $fOfferAmt, $fMaxOfferAmt) {
    global $obj, $generalobj, $tconfig;
    if ($iUserId != "") {
        $vLanguage = get_value('register_user', 'vLang', 'iUserId', $iUserId, '', 'true');
        if ($vLanguage == "" || $vLanguage == NULL) {
            $vLanguage = get_value('language_master', 'vCode', 'eDefault', 'Yes', '', 'true');
        }
    } else {
        $vLanguage = get_value('language_master', 'vCode', 'eDefault', 'Yes', '', 'true');
    }

    $LBL_GET_TXT = get_value('language_label', 'vValue', 'vLabel', 'LBL_GET_TXT', " and vCode='" . $vLanguage . "'", 'true');
    $LBL_ALL_ORDER_TXT = get_value('language_label', 'vValue', 'vLabel', 'LBL_ALL_ORDER_TXT', " and vCode='" . $vLanguage . "'", 'true');
    $LBL_FIRST_ORDER_TXT = get_value('language_label', 'vValue', 'vLabel', 'LBL_FIRST_ORDER_TXT', " and vCode='" . $vLanguage . "'", 'true');
    $offermsg = "";
    if ($fOfferType == "Percentage") {
        if ($fOfferAppyType == "First") {
            $offermsg = $LBL_GET_TXT . " " . $fOfferAmt . "% " . $LBL_FIRST_ORDER_TXT;
        } elseif ($fOfferAppyType == "All") {
            $offermsg = $LBL_GET_TXT . " " . $fOfferAmt . "% " . $LBL_ALL_ORDER_TXT;
        }
    }
    return $offermsg;
}

function getCompanyBySearchCuisine($iUserId, $SearchKeyword, $Restaurant_id_str = 0) {
    global $obj;
    $returnArr = array();
    $vLanguage = "";
    if ($iUserId != "") {
        $vLanguage = get_value('register_user', 'vLang', 'iUserId', $iUserId, '', 'true');
    }
    if ($vLanguage == "" || $vLanguage == NULL) {
        $vLanguage = get_value('language_master', 'vCode', 'eDefault', 'Yes', '', 'true');
    }
    //$LBL_RESTAURANTS_TXT = get_value('language_label_other', 'vValue', 'vLabel', 'LBL_RESTAURANTS_TXT', " and vCode='" . $vLanguage . "'", 'true');
    //$LBL_RESTAURANT_TXT = get_value('language_label_other', 'vValue', 'vLabel', 'LBL_RESTAURANT_TXT', " and vCode='" . $vLanguage . "'", 'true');
    $langLabels = $obj->MySQLSelect("SELECT vValue,vLabel FROM language_label_other WHERE (vLabel='LBL_RESTAURANTS_TXT' || vLabel='LBL_RESTAURANT_TXT') AND vCode='" . $vLanguage . "'");
    $LBL_RESTAURANTS_TXT = "Restaurants";
    $LBL_RESTAURANT_TXT = "Restaurant";
    for ($l = 0; $l < count($langLabels); $l++) {
        $vLabel = $langLabels[$l]['vLabel'];
        $vValue = $langLabels[$l]['vValue'];
        if ($vLabel == "LBL_RESTAURANTS_TXT") {
            $LBL_RESTAURANTS_TXT = $vValue;
        }
        if ($vLabel == "LBL_RESTAURANT_TXT") {
            $LBL_RESTAURANT_TXT = $vValue;
        }
    }
    $sql = "SELECT cuisineId, cuisineName_" . $vLanguage . " as cuisineName FROM cuisine WHERE eStatus='Active' AND cuisineName_" . $vLanguage . " LIKE '%" . $SearchKeyword . "%'";
    $CuisineDetail = $obj->MySQLSelect($sql);
    //echo $cuisineIds;die;
    if (count($CuisineDetail) > 0) {
        $CuisineTotalRestaurant = $obj->MySQLSelect("SELECT count(iCompanyId) as TotalRestaurant,cuisineId FROM company_cuisine WHERE iCompanyId IN($Restaurant_id_str) GROUP BY cuisineId");
        $cuisineRestArr = array();
        for ($c = 0; $c < count($CuisineTotalRestaurant); $c++) {
            $cuisineRestArr[$CuisineTotalRestaurant[$c]['cuisineId']] = $CuisineTotalRestaurant[$c]['TotalRestaurant'];
        }
        for ($i = 0; $i < count($CuisineDetail); $i++) {
            $cuisineId = $CuisineDetail[$i]['cuisineId'];
            $cuisineName = $CuisineDetail[$i]['cuisineName'];
            //$sqlr = "SELECT count(iCompanyId) as TotalRestaurant FROM company_cuisine WHERE cuisineId = '" . $cuisineId . "' AND iCompanyId IN($Restaurant_id_str)";
            //$CuisineTotalRestaurant = $obj->MySQLSelect($sqlr);
            //$TotalRestaurant = $CuisineTotalRestaurant[0]['TotalRestaurant'];
            $TotalRestaurant = 0;
            if (isset($cuisineRestArr[$cuisineId]) && $cuisineRestArr[$cuisineId] > 0) {
                $TotalRestaurant = $cuisineRestArr[$cuisineId];
            }
            if ($TotalRestaurant > 0) {
                $TotalRestaurantTxt = ($TotalRestaurant <= 1) ? $LBL_RESTAURANT_TXT : $LBL_RESTAURANTS_TXT;
                $returnArr[$i]['cuisineId'] = $cuisineId;
                $returnArr[$i]['cuisineName'] = $cuisineName;
                $returnArr[$i]['TotalRestaurant'] = $TotalRestaurant;
                $returnArr[$i]['TotalRestaurantWithLabel'] = $TotalRestaurant . " " . $TotalRestaurantTxt;
            }
        }
    }

    return $returnArr;
}

function getCompanyTimeSlot($iCompanyId, $languageLabelsArr) {
    global $obj, $generalobj, $tconfig;
    $sql = "SELECT vFromMonFriTimeSlot1,vToMonFriTimeSlot1,vFromMonFriTimeSlot2,vToMonFriTimeSlot2,vFromSatSunTimeSlot1,vToSatSunTimeSlot1,vFromSatSunTimeSlot2,vToSatSunTimeSlot2 FROM company WHERE iCompanyId = '" . $iCompanyId . "'";
    $DataCompanyTime = $obj->MySQLSelect($sql);

    // print_R($DataCompanyTime);die;
    $vFromMonFriTimeSlot1 = substr($DataCompanyTime[0]['vFromMonFriTimeSlot1'], 0, -3);
    $vToMonFriTimeSlot1 = substr($DataCompanyTime[0]['vToMonFriTimeSlot1'], 0, -3);
    $vFromMonFriTimeSlot2 = substr($DataCompanyTime[0]['vFromMonFriTimeSlot2'], 0, -3);
    $vToMonFriTimeSlot2 = substr($DataCompanyTime[0]['vToMonFriTimeSlot2'], 0, -3);
    $vFromSatSunTimeSlot1 = substr($DataCompanyTime[0]['vFromSatSunTimeSlot1'], 0, -3);
    $vToSatSunTimeSlot1 = substr($DataCompanyTime[0]['vToSatSunTimeSlot1'], 0, -3);
    $vFromSatSunTimeSlot2 = substr($DataCompanyTime[0]['vFromSatSunTimeSlot2'], 0, -3);
    $vToSatSunTimeSlot2 = substr($DataCompanyTime[0]['vToSatSunTimeSlot2'], 0, -3);
    $vFromMonFriTimeSlotNew1 = date("g:i a", strtotime($vFromMonFriTimeSlot1));
    $vToMonFriTimeSlotNew1 = date("g:i a", strtotime($vToMonFriTimeSlot1));
    $vFromMonFriTimeSlotNew2 = date("g:i a", strtotime($vFromMonFriTimeSlot2));
    $vToMonFriTimeSlotNew2 = date("g:i a", strtotime($vToMonFriTimeSlot2));
    $vFromSatSunTimeSlotNew1 = date("g:i a", strtotime($vFromSatSunTimeSlot1));
    $vToSatSunTimeSlotNew1 = date("g:i a", strtotime($vToSatSunTimeSlot1));
    $vFromSatSunTimeSlotNew2 = date("g:i a", strtotime($vFromSatSunTimeSlot2));
    $vToSatSunTimeSlotNew2 = date("g:i a", strtotime($vToSatSunTimeSlot2));
    if ($vFromMonFriTimeSlot1 == "00:00" && $vToMonFriTimeSlot1 == "00:00" && $vFromMonFriTimeSlot2 == "00:00" && $vToMonFriTimeSlot2 == "00:00") {
        $monfritimeslot_TXT = $monfritimeslot_Time = $monfritimeslot_Time_new = "";
    }
    if ($vFromMonFriTimeSlot1 != "00:00" && $vToMonFriTimeSlot1 != "00:00" && $vFromMonFriTimeSlot2 != "00:00" && $vToMonFriTimeSlot2 != "00:00") {
        $monfritimeslot_TXT = $languageLabelsArr['LBL_MON_FRI_TIME_TXT'];
        $monfritimeslot_Time = $vFromMonFriTimeSlot1 . "-" . $vToMonFriTimeSlot1 . " " . $languageLabelsArr['LBL_TIMSLOT_AND_OTHER_TXT'] . " " . $vFromMonFriTimeSlot2 . "-" . $vToMonFriTimeSlot2;
        $monfritimeslot_Time_new = $vFromMonFriTimeSlotNew1 . "-" . $vToMonFriTimeSlotNew1 . " " . $languageLabelsArr['LBL_TIMSLOT_AND_OTHER_TXT'] . "\n" . $vFromMonFriTimeSlotNew2 . "-" . $vToMonFriTimeSlotNew2;
    }

    if ($vFromMonFriTimeSlot1 == "00:00" && $vToMonFriTimeSlot1 != "00:00" && $vFromMonFriTimeSlot2 != "00:00" && $vToMonFriTimeSlot2 != "00:00") {
        $monfritimeslot_TXT = $languageLabelsArr['LBL_MON_FRI_TIME_TXT'];
        $monfritimeslot_Time = $vFromMonFriTimeSlot1 . "-" . $vToMonFriTimeSlot1 . " " . $languageLabelsArr['LBL_TIMSLOT_AND_OTHER_TXT'] . " " . $vFromMonFriTimeSlot2 . "-" . $vToMonFriTimeSlot2;
        $monfritimeslot_Time_new = $vFromMonFriTimeSlotNew1 . "-" . $vToMonFriTimeSlotNew1 . " " . $languageLabelsArr['LBL_TIMSLOT_AND_OTHER_TXT'] . "\n" . $vFromMonFriTimeSlotNew2 . "-" . $vToMonFriTimeSlotNew2;
    }
    if ($vFromMonFriTimeSlot1 != "00:00" && $vToMonFriTimeSlot1 != "00:00" && $vFromMonFriTimeSlot2 == "00:00" && $vToMonFriTimeSlot2 != "00:00") {
        $monfritimeslot_TXT = $languageLabelsArr['LBL_MON_FRI_TIME_TXT'];
        $monfritimeslot_Time = $vFromMonFriTimeSlot1 . "-" . $vToMonFriTimeSlot1 . " " . $languageLabelsArr['LBL_TIMSLOT_AND_OTHER_TXT'] . " " . $vFromMonFriTimeSlot2 . "-" . $vToMonFriTimeSlot2;
        $monfritimeslot_Time_new = $vFromMonFriTimeSlotNew1 . "-" . $vToMonFriTimeSlotNew1 . " " . $languageLabelsArr['LBL_TIMSLOT_AND_OTHER_TXT'] . "\n" . $vFromMonFriTimeSlotNew2 . "-" . $vToMonFriTimeSlotNew2;
    }
    if ($vFromMonFriTimeSlot1 != "00:00" && $vToMonFriTimeSlot1 != "00:00" && $vFromMonFriTimeSlot2 == "00:00" && $vToMonFriTimeSlot2 == "00:00") {
        $monfritimeslot_TXT = $languageLabelsArr['LBL_MON_FRI_TIME_TXT'];
        $monfritimeslot_Time = $vFromMonFriTimeSlot1 . "-" . $vToMonFriTimeSlot1;
        $monfritimeslot_Time_new = $vFromMonFriTimeSlotNew1 . "-" . $vToMonFriTimeSlotNew1;
    }
    if ($vFromMonFriTimeSlot1 == "00:00" && $vToMonFriTimeSlot1 == "00:00" && $vFromMonFriTimeSlot2 != "00:00" && $vToMonFriTimeSlot2 != "00:00") {
        $monfritimeslot_TXT = $languageLabelsArr['LBL_MON_FRI_TIME_TXT'];
        $monfritimeslot_Time = $vFromMonFriTimeSlot2 . "-" . $vToMonFriTimeSlot2;
        $monfritimeslot_Time_new = $vFromMonFriTimeSlotNew2 . "-" . $vToMonFriTimeSlotNew2;
    }
    if ($vFromSatSunTimeSlot1 == "00:00" && $vToSatSunTimeSlot1 == "00:00" && $vFromSatSunTimeSlot2 == "00:00" && $vToSatSunTimeSlot2 == "00:00") {
        $satsuntimeslot_TXT = $satsuntimeslot_Tim = $satsuntimeslot_Time_new = "";
    }
    if ($vFromSatSunTimeSlot1 != "00:00" && $vToSatSunTimeSlot1 != "00:00" && $vFromSatSunTimeSlot2 != "00:00" && $vToSatSunTimeSlot2 != "00:00") {
        $satsuntimeslot_TXT = $languageLabelsArr['LBL_SAT_SUN_TXT'];
        $satsuntimeslot_Time = $vFromSatSunTimeSlot1 . "-" . $vToSatSunTimeSlot1 . " " . $languageLabelsArr['LBL_TIMSLOT_AND_OTHER_TXT'] . " " . $vFromSatSunTimeSlot2 . "-" . $vToSatSunTimeSlot2;
        $satsuntimeslot_Time_new = $vFromSatSunTimeSlotNew1 . "-" . $vToSatSunTimeSlotNew1 . " " . $languageLabelsArr['LBL_TIMSLOT_AND_OTHER_TXT'] . "\n" . $vFromSatSunTimeSlotNew2 . "-" . $vToSatSunTimeSlotNew2;
    }
    if ($vFromSatSunTimeSlot1 == "00:00" && $vToSatSunTimeSlot1 != "00:00" && $vFromSatSunTimeSlot2 != "00:00" && $vToSatSunTimeSlot2 != "00:00") {
        $satsuntimeslot_TXT = $languageLabelsArr['LBL_SAT_SUN_TXT'];
        $satsuntimeslot_Time = $vFromSatSunTimeSlot1 . "-" . $vToSatSunTimeSlot1 . " " . $languageLabelsArr['LBL_TIMSLOT_AND_OTHER_TXT'] . " " . $vFromSatSunTimeSlot2 . "-" . $vToSatSunTimeSlot2;
        $satsuntimeslot_Time_new = $vFromSatSunTimeSlotNew1 . "-" . $vToSatSunTimeSlotNew1 . " " . $languageLabelsArr['LBL_TIMSLOT_AND_OTHER_TXT'] . "\n" . $vFromSatSunTimeSlotNew2 . "-" . $vToSatSunTimeSlotNew2;
    }
    if ($vFromSatSunTimeSlot1 != "00:00" && $vToSatSunTimeSlot1 != "00:00" && $vFromSatSunTimeSlot2 == "00:00" && $vToSatSunTimeSlot2 != "00:00") {
        $satsuntimeslot_TXT = $languageLabelsArr['LBL_SAT_SUN_TXT'];
        $satsuntimeslot_Time = $vFromSatSunTimeSlot1 . "-" . $vToSatSunTimeSlot1 . " " . $languageLabelsArr['LBL_TIMSLOT_AND_OTHER_TXT'] . " " . $vFromSatSunTimeSlot2 . "-" . $vToSatSunTimeSlot2;
        $satsuntimeslot_Time_new = $vFromSatSunTimeSlotNew1 . "-" . $vToSatSunTimeSlotNew1 . " " . $languageLabelsArr['LBL_TIMSLOT_AND_OTHER_TXT'] . "\n" . $vFromSatSunTimeSlotNew2 . "-" . $vToSatSunTimeSlotNew2;
    }
    if ($vFromSatSunTimeSlot1 != "00:00" && $vToSatSunTimeSlot1 != "00:00" && $vFromSatSunTimeSlot2 == "00:00" && $vToSatSunTimeSlot2 == "00:00") {
        $satsuntimeslot_TXT = $languageLabelsArr['LBL_SAT_SUN_TXT'];
        $satsuntimeslot_Time = $vFromSatSunTimeSlot1 . "-" . $vToSatSunTimeSlot1;
        $satsuntimeslot_Time_new = $vFromSatSunTimeSlotNew1 . "-" . $vToSatSunTimeSlotNew1;
    }
    if ($vFromSatSunTimeSlot1 == "00:00" && $vToSatSunTimeSlot1 == "00:00" && $vFromSatSunTimeSlot2 != "00:00" && $vToSatSunTimeSlot2 != "00:00") {
        $satsuntimeslot_TXT = $languageLabelsArr['LBL_SAT_SUN_TXT'];
        $satsuntimeslot_Time = $vFromSatSunTimeSlot2 . "-" . $vToSatSunTimeSlot2;
        $satsuntimeslot_Time_new = $vFromSatSunTimeSlotNew2 . "-" . $vToSatSunTimeSlotNew2;
    }
    $returnArr['monfritimeslot_TXT'] = $monfritimeslot_TXT;
    $returnArr['monfritimeslot_Time'] = $monfritimeslot_Time;
    $returnArr['monfritimeslot_Time_new'] = $monfritimeslot_Time_new;
    $returnArr['satsuntimeslot_TXT'] = $satsuntimeslot_TXT;
    $returnArr['satsuntimeslot_Time'] = $satsuntimeslot_Time;
    $returnArr['satsuntimeslot_Time_new'] = $satsuntimeslot_Time_new;
    return $returnArr;
}

function GetMenuItemOptionsTopping($iMenuItemId, $currencySymbol, $Ratio, $vLanguage, $iServiceId = '') {
    //echo $iMenuItemId;die;
    global $obj, $generalobj;
    $returnArr = array();
    $ispriceshow = '';
    if (isset($iServiceId) && !empty($iServiceId)) {
        $servFields = 'eType';
        $ServiceCategoryData = get_value('service_categories', $servFields, 'iServiceId', $iServiceId);
        if (!empty($ServiceCategoryData)) {
            if (!empty($ServiceCategoryData[0]['eType']) && $ServiceCategoryData[0]['eType'] == 'separate') {
                $ispriceshow = $ServiceCategoryData[0]['eType'];
            }
        }
    }
    if (isset($ispriceshow) && !empty($ispriceshow)) {
        $sql = "SELECT mo.iOptionId,mo.vOptionName,IF(mo.eDefault='Yes' AND mo.eOptionType='Options',mi.fprice,mo.fprice) as fPrice,mo.eOptionType,mo.eDefault,mo.iMenuItemId FROM menuitem_options as mo LEFT JOIN menu_items as mi ON mo.iMenuItemId = mi.iMenuItemId WHERE mo.iMenuItemId IN ($iMenuItemId) AND mo.eStatus = 'Active'";
        //$sql = "SELECT iOptionId,vOptionName,fPrice,eOptionType,eDefault FROM menuitem_options WHERE iMenuItemId = '" . $iMenuItemId . "' AND eStatus = 'Active'";
    } else {
        $sql = "SELECT iOptionId,vOptionName,fPrice,eOptionType,eDefault,iMenuItemId FROM menuitem_options WHERE iMenuItemId IN ($iMenuItemId) AND eStatus = 'Active'";
    }
    $db_options_data = $obj->MySQLSelect($sql);
    //echo "<pre>";print_r($db_options_data);die;
    if (count($db_options_data) > 0) {
        for ($i = 0; $i < count($db_options_data); $i++) {
            $fPrice = $db_options_data[$i]['fPrice'];
            $iMenuItemId = $db_options_data[$i]['iMenuItemId'];
            $fUserPrice = $generalobj->setTwoDecimalPoint($fPrice * $Ratio);
            $fUserPriceWithSymbol = $currencySymbol . " " . $fUserPrice;
            $db_options_data[$i]['fUserPrice'] = $fUserPrice;
            $db_options_data[$i]['fUserPriceWithSymbol'] = $fUserPriceWithSymbol;
            if (isset($db_options_data[$i]['eOptionType']) && $db_options_data[$i]['eOptionType'] == "Options") {
                $returnArr[$iMenuItemId]['options'][] = $db_options_data[$i];
            }
            if (isset($db_options_data[$i]['eOptionType']) && $db_options_data[$i]['eOptionType'] == "Addon") {
                $returnArr[$iMenuItemId]['addon'][] = $db_options_data[$i];
            }
        }
    }
    //echo "<pre>";print_r($returnArr);exit;
    return $returnArr;
}

/* function getUserCurrencyLanguageDetails($iUserId = "", $iOrderId = 0) {
  global $obj, $generalobj, $tconfig;
  $returnArr = array();
  if ($iUserId != "") {
  $sqlp = "SELECT ru.vCurrencyPassenger,ru.vLang,cu.vSymbol,cu.Ratio  FROM register_user as ru LEFT JOIN currency as cu ON ru.vCurrencyPassenger = cu.vName WHERE iUserId = '" . $iUserId . "'";
  $passengerData = $obj->MySQLSelect($sqlp);
  $currencycode = $passengerData[0]['vCurrencyPassenger'];
  $vLanguage = $passengerData[0]['vLang'];
  $currencySymbol = $passengerData[0]['vSymbol'];
  $Ratio = $passengerData[0]['Ratio'];
  if ($iOrderId > 0) {
  $sql = "SELECT fRatio_" . $currencycode . " as Ratio FROM orders WHERE iOrderId = '" . $iOrderId . "'";
  $CurrencyData = $obj->MySQLSelect($sql);
  $Ratio = $CurrencyData[0]['Ratio'];
  }

  if ($vLanguage == "" || $vLanguage == NULL) {
  $vLanguage = get_value('language_master', 'vCode', 'eDefault', 'Yes', '', 'true');
  }

  if ($currencycode == "" || $currencycode == NULL) {
  $sqlp = "SELECT vName,vSymbol,Ratio FROM currency WHERE eDefault = 'Yes'";
  $currencyData = $obj->MySQLSelect($sqlp);
  $currencycode = $currencyData[0]['vName'];
  $currencySymbol = $currencyData[0]['vSymbol'];
  $Ratio = $currencyData[0]['Ratio'];
  }
  } else {
  $vLanguage = get_value('language_master', 'vCode', 'eDefault', 'Yes', '', 'true');
  $sqlp = "SELECT vName,vSymbol,Ratio FROM currency WHERE eDefault = 'Yes'";
  $currencyData = $obj->MySQLSelect($sqlp);
  $currencycode = $currencyData[0]['vName'];
  $currencySymbol = $currencyData[0]['vSymbol'];
  $Ratio = $currencyData[0]['Ratio'];
  }

  $returnArr['currencycode'] = $currencycode;
  $returnArr['currencySymbol'] = $currencySymbol;
  $returnArr['Ratio'] = $Ratio;
  $returnArr['vLang'] = $vLanguage;
  return $returnArr;
  }

  function getDriverCurrencyLanguageDetails($iDriverId = "", $iOrderId = 0) {
  global $obj, $generalobj, $tconfig;
  $returnArr = array();
  if ($iDriverId != "") {
  $sqlp = "SELECT rd.vCurrencyDriver,rd.vLang,cu.vSymbol,cu.Ratio FROM register_driver as rd LEFT JOIN currency as cu ON rd.vCurrencyDriver = cu.vName WHERE iDriverId = '" . $iDriverId . "'";
  $passengerData = $obj->MySQLSelect($sqlp);
  $currencycode = $vLanguage = "";
  if (count($passengerData) > 0) {
  $currencycode = $passengerData[0]['vCurrencyDriver'];
  $vLanguage = $passengerData[0]['vLang'];
  $currencySymbol = $passengerData[0]['vSymbol'];
  $Ratio = $passengerData[0]['Ratio'];
  }
  if ($iOrderId > 0) {
  $sql = "SELECT fRatio_" . $currencycode . " as Ratio FROM orders WHERE iOrderId = '" . $iOrderId . "'";
  $CurrencyData = $obj->MySQLSelect($sql);
  $Ratio = $CurrencyData[0]['Ratio'];
  }

  if ($vLanguage == "" || $vLanguage == NULL) {
  $vLanguage = get_value('language_master', 'vCode', 'eDefault', 'Yes', '', 'true');
  }

  if ($currencycode == "" || $currencycode == NULL) {
  $sqlp = "SELECT vName,vSymbol,Ratio FROM currency WHERE eDefault = 'Yes'";
  $currencyData = $obj->MySQLSelect($sqlp);
  $currencycode = $currencyData[0]['vName'];
  $currencySymbol = $currencyData[0]['vSymbol'];
  $Ratio = $currencyData[0]['Ratio'];
  }
  } else {
  $vLanguage = get_value('language_master', 'vCode', 'eDefault', 'Yes', '', 'true');
  $sqlp = "SELECT vName,vSymbol,Ratio FROM currency WHERE eDefault = 'Yes'";
  $currencyData = $obj->MySQLSelect($sqlp);
  $currencycode = $currencyData[0]['vName'];
  $currencySymbol = $currencyData[0]['vSymbol'];
  $Ratio = $currencyData[0]['Ratio'];
  }

  $returnArr['currencycode'] = $currencycode;
  $returnArr['currencySymbol'] = $currencySymbol;
  $returnArr['Ratio'] = $Ratio;
  $returnArr['vLang'] = $vLanguage;
  return $returnArr;
  } */

function getCompanyCurrencyLanguageDetails($iCompanyId = "", $iOrderId = 0) {
    global $obj, $generalobj, $tconfig;
    $returnArr = array();
    if ($iCompanyId != "") {
        $sqlp = "SELECT co.vCurrencyCompany,co.vLang,cu.vSymbol,cu.Ratio FROM company as co LEFT JOIN currency as cu ON co.vCurrencyCompany = cu.vName WHERE iCompanyId = '" . $iCompanyId . "'";
        $passengerData = $obj->MySQLSelect($sqlp);
        $currencycode = $passengerData[0]['vCurrencyCompany'];
        $vLanguage = $passengerData[0]['vLang'];
        $currencySymbol = $passengerData[0]['vSymbol'];
        $Ratio = $passengerData[0]['Ratio'];
        if ($iOrderId > 0) {
            $sql = "SELECT fRatio_" . $currencycode . " as Ratio FROM orders WHERE iOrderId = '" . $iOrderId . "'";
            $CurrencyData = $obj->MySQLSelect($sql);
            $Ratio = $CurrencyData[0]['Ratio'];
        }

        if ($vLanguage == "" || $vLanguage == NULL) {
            $vLanguage = get_value('language_master', 'vCode', 'eDefault', 'Yes', '', 'true');
        }

        if ($currencycode == "" || $currencycode == NULL) {
            $sqlp = "SELECT vName,vSymbol,Ratio FROM currency WHERE eDefault = 'Yes'";
            $currencyData = $obj->MySQLSelect($sqlp);
            $currencycode = $currencyData[0]['vName'];
            $currencySymbol = $currencyData[0]['vSymbol'];
            $Ratio = $currencyData[0]['Ratio'];
        }
    } else {
        $vLanguage = get_value('language_master', 'vCode', 'eDefault', 'Yes', '', 'true');
        $sqlp = "SELECT vName,vSymbol,Ratio FROM currency WHERE eDefault = 'Yes'";
        $currencyData = $obj->MySQLSelect($sqlp);
        $currencycode = $currencyData[0]['vName'];
        $currencySymbol = $currencyData[0]['vSymbol'];
        $Ratio = $currencyData[0]['Ratio'];
    }

    $returnArr['currencycode'] = $currencycode;
    $returnArr['currencySymbol'] = $currencySymbol;
    $returnArr['Ratio'] = $Ratio;
    $returnArr['vLang'] = $vLanguage;
    return $returnArr;
}

function GetAllMenuItemOptionsTopping($iCompanyId, $currencySymbol, $Ratio, $vLanguage, $eFor = "", $iServiceId = "") {
    global $obj, $generalobj, $tconfig;
    $returnArr = array();
    $returnArr['options'] = $returnArr['addon'] = array();
    $ssql = "";
    if ($eFor == "Display") {
        $ssql .= " AND mo.eStatus = 'Active' ";
    }
    $ispriceshow = '';
    if (isset($iServiceId) && !empty($iServiceId)) {
        $servFields = 'eType';
        $ServiceCategoryData = get_value('service_categories', $servFields, 'iServiceId', $iServiceId);
        if (!empty($ServiceCategoryData)) {
            if (!empty($ServiceCategoryData[0]['eType']) && $ServiceCategoryData[0]['eType'] == 'separate') {
                $ispriceshow = $ServiceCategoryData[0]['eType'];
            }
        }
    }

    // $languageLabelsArr = getLanguageLabelsArr($vLanguage,"1");
    if (isset($ispriceshow) && !empty($ispriceshow)) {

        $sql = "SELECT mo.iOptionId, mo.iMenuItemId, mo.vOptionName,IF(mo.eDefault='Yes' AND mo.eOptionType='Options',mi.fprice,mo.fprice) as fPrice,mo.eOptionType,mo.eDefault,mo.eStatus,fm.iFoodMenuId FROM menuitem_options as mo LEFT JOIN menu_items as mi ON mo.iMenuItemId=mi.iMenuItemId LEFT JOIN food_menu as fm ON mi.iFoodMenuId=fm.iFoodMenuId LEFT JOIN company as co ON fm.iCompanyId=co.iCompanyId WHERE co.iCompanyId = '" . $iCompanyId . "' AND fm.eStatus = 'Active' AND mi.eAvailable = 'Yes'" . $ssql;
    } else {
        $sql = "SELECT mo.*,fm.iFoodMenuId FROM menuitem_options as mo LEFT JOIN menu_items as mi ON mo.iMenuItemId=mi.iMenuItemId LEFT JOIN food_menu as fm ON mi.iFoodMenuId=fm.iFoodMenuId LEFT JOIN company as co ON fm.iCompanyId=co.iCompanyId WHERE co.iCompanyId = '" . $iCompanyId . "' AND fm.eStatus = 'Active' AND mi.eAvailable = 'Yes'" . $ssql;
    }
    //$sql = "SELECT mo.*,fm.iFoodMenuId FROM menuitem_options as mo LEFT JOIN menu_items as mi ON mo.iMenuItemId=mi.iMenuItemId LEFT JOIN food_menu as fm ON mi.iFoodMenuId=fm.iFoodMenuId LEFT JOIN company as co ON fm.iCompanyId=co.iCompanyId WHERE co.iCompanyId = '" . $iCompanyId . "' AND fm.eStatus = 'Active' AND mi.eAvailable = 'Yes'" . $ssql;
    $db_options_data = $obj->MySQLSelect($sql);
    if (count($db_options_data) > 0) {
        for ($i = 0; $i < count($db_options_data); $i++) {
            $fPrice = $db_options_data[$i]['fPrice'];
            $fUserPrice = number_format($fPrice * $Ratio, 2);
            $fUserPriceWithSymbol = $currencySymbol . " " . $fUserPrice;
            $db_options_data[$i]['fUserPrice'] = $fUserPrice;
            $db_options_data[$i]['fUserPriceWithSymbol'] = $fUserPriceWithSymbol;
            if ($db_options_data[$i]['eOptionType'] == "Options") {
                $returnArr['options'][] = $db_options_data[$i];
            }

            if ($db_options_data[$i]['eOptionType'] == "Addon") {
                $returnArr['addon'][] = $db_options_data[$i];
            }
        }
    }

    // echo "<pre>";print_r($returnArr);exit;
    return $returnArr;
}

function GetUserSelectedAddress($iUserId, $eUserType = "Passenger") {
    global $obj, $generalobj, $tconfig;
    $returnArr = array();
    if ($eUserType == "Passenger") {
        $UserType = "Rider";
    } else {
        $UserType = "Driver";
    }

    $sql = "SELECT * from user_address WHERE iUserId = '" . $iUserId . "' AND eUserType = '" . $UserType . "' AND eStatus = 'Active'";
    $result_Address = $obj->MySQLSelect($sql);
    $ToTalAddress = count($result_Address);
    if ($ToTalAddress > 0) {

        ## Checking First Last Orders Selected Address ##
        $sqlo = "SELECT ord.iUserAddressId,ua.eStatus,ua.vServiceAddress,ua.vBuildingNo,ua.vLandmark,ua.vAddressType,ua.vLatitude,ua.vLongitude from orders as ord LEFT JOIN user_address as ua ON ord.iUserAddressId=ua.iUserAddressId WHERE ord.iUserId = '" . $iUserId . "' ORDER BY ord.iOrderId DESC limit 0,1";
        $last_order_Address = $obj->MySQLSelect($sqlo);
        $iUserAddressId = $last_order_Address[0]['iUserAddressId'];
        if (count($last_order_Address) > 0 && $iUserAddressId > 0) {
            $eStatus = $last_order_Address[0]['eStatus'];
            if ($eStatus == "Active") {
                $vAddressType = $last_order_Address[0]['vAddressType'];
                $vBuildingNo = $last_order_Address[0]['vBuildingNo'];
                $vLandmark = $last_order_Address[0]['vLandmark'];
                $vServiceAddress = $last_order_Address[0]['vServiceAddress'];
                $PickUpAddress = ($vAddressType != "") ? $vAddressType . "\n" : "";
                $PickUpAddress .= ($vBuildingNo != "") ? $vBuildingNo . "," : "";
                $PickUpAddress .= ($vLandmark != "") ? $vLandmark . "\n" : "";
                $PickUpAddress .= ($vServiceAddress != "") ? $vServiceAddress : "";
                $PickUpLatitude = $last_order_Address[0]['vLatitude'];
                $PickUpLongitude = $last_order_Address[0]['vLongitude'];
                $returnArr['UserSelectedAddress'] = $PickUpAddress;
                $returnArr['UserSelectedLatitude'] = $PickUpLatitude;
                $returnArr['UserSelectedLongitude'] = $PickUpLongitude;
                $returnArr['UserSelectedAddressId'] = $iUserAddressId;
            } else {
                $returnArr['UserSelectedAddress'] = "";
                $returnArr['UserSelectedLatitude'] = "";
                $returnArr['UserSelectedLongitude'] = "";
                $returnArr['UserSelectedAddressId'] = 0;
            }
        } else {
            $vAddressType = $result_Address[0]['vAddressType'];
            $vBuildingNo = $result_Address[0]['vBuildingNo'];
            $vLandmark = $result_Address[0]['vLandmark'];
            $vServiceAddress = $result_Address[0]['vServiceAddress'];
            $PickUpAddress = ($vAddressType != "") ? $vAddressType . "\n" : "";
            $PickUpAddress .= ($vBuildingNo != "") ? $vBuildingNo . "," : "";
            $PickUpAddress .= ($vLandmark != "") ? $vLandmark . "\n" : "";
            $PickUpAddress .= ($vServiceAddress != "") ? $vServiceAddress : "";
            $PickUpLatitude = $result_Address[0]['vLatitude'];
            $PickUpLongitude = $result_Address[0]['vLongitude'];
            $returnArr['UserSelectedAddress'] = $PickUpAddress;
            $returnArr['UserSelectedLatitude'] = $PickUpLatitude;
            $returnArr['UserSelectedLongitude'] = $PickUpLongitude;
            $returnArr['UserSelectedAddressId'] = $result_Address[0]['iUserAddressId'];
        }

        ## Checking First Last Orders Selected Address ##
    } else {
        $returnArr['UserSelectedAddress'] = "";
        $returnArr['UserSelectedLatitude'] = "";
        $returnArr['UserSelectedLongitude'] = "";
        $returnArr['UserSelectedAddressId'] = 0;
    }

    return $returnArr;
}

function GetUserAddressDetail($iUserId, $eUserType = "Passenger", $iUserAddressId) {
    global $obj, $generalobj, $tconfig;
    $returnArr = array();
    if ($eUserType == "Passenger") {
        $UserType = "Rider";
    } else {
        $UserType = "Driver";
    }

    $sql = "SELECT * from user_address WHERE iUserId = '" . $iUserId . "' AND eUserType = '" . $UserType . "' AND iUserAddressId = '" . $iUserAddressId . "'";
    $result_Address = $obj->MySQLSelect($sql);
    $ToTalAddress = count($result_Address);
    if ($ToTalAddress > 0) {
        $vAddressType = $result_Address[0]['vAddressType'];
        $vBuildingNo = $result_Address[0]['vBuildingNo'];
        $vLandmark = $result_Address[0]['vLandmark'];
        $vServiceAddress = $result_Address[0]['vServiceAddress'];
        $PickUpAddress = ($vAddressType != "") ? $vAddressType . "\n" : "";
        $PickUpAddress .= ($vBuildingNo != "") ? $vBuildingNo . "," : "";
        $PickUpAddress .= ($vLandmark != "") ? $vLandmark . "\n" : "";
        $PickUpAddress .= ($vServiceAddress != "") ? $vServiceAddress : "";
        $result_Address[0]['UserAddress'] = $PickUpAddress;
        $returnArr = $result_Address[0];
    }

    return $returnArr;
}

function GetTotalUserAddress($iUserId, $eUserType = "Passenger", $passengerLat, $passengerLon, $iCompanyId = 0) {
    global $obj, $generalobj, $tconfig, $LIST_RESTAURANT_LIMIT_BY_DISTANCE;
    $ToTalAddress = 0;
    if ($iUserId == "" || $iUserId == 0 || $iUserId == NULL) {
        return $ToTalAddress;
    }

    if ($eUserType == "Passenger") {
        $UserType = "Rider";
    } else {
        $UserType = "Driver";
    }

    $sql = "select * from `user_address` where iUserId = '" . $iUserId . "' AND eUserType = '" . $UserType . "' AND eStatus = 'Active' ORDER BY iUserAddressId DESC";
    $db_userdata = $obj->MySQLSelect($sql);
    $db_userdata_new = array();
    $db_userdata_new = $db_userdata;
    if (count($db_userdata) > 0) {
        for ($i = 0; $i < count($db_userdata); $i++) {
            $isRemoveAddressFromList = "No";
            $passengeraddlat = $db_userdata[$i]['vLatitude'];
            $passengeraddlong = $db_userdata[$i]['vLongitude'];
            if ($iCompanyId == 0) {
                $distance = distanceByLocation($passengerLat, $passengerLon, $passengeraddlat, $passengeraddlong, "K");
                if ($distance > $LIST_RESTAURANT_LIMIT_BY_DISTANCE) {
                    $isRemoveAddressFromList = "Yes";
                }
            }

            ## Checking Distance Between Company and User Address ##
            if ($iCompanyId > 0) {
                $sql = "select vRestuarantLocationLat,vRestuarantLocationLong from `company` where iCompanyId = '" . $iCompanyId . "'";
                $db_companydata = $obj->MySQLSelect($sql);
                $vRestuarantLocationLat = $db_companydata[0]['vRestuarantLocationLat'];
                $vRestuarantLocationLong = $db_companydata[0]['vRestuarantLocationLong'];
                $distancewithcompany = distanceByLocation($passengeraddlat, $passengeraddlong, $vRestuarantLocationLat, $vRestuarantLocationLong, "K");
                if ($distancewithcompany > $LIST_RESTAURANT_LIMIT_BY_DISTANCE) {
                    $isRemoveAddressFromList = "Yes";
                }
            }

            ## Checking Distance Between Company and User Address ##
            if ($isRemoveAddressFromList == "Yes") {
                unset($db_userdata_new[$i]);
            }
        }

        $db_userdata = array_values($db_userdata_new);
        $ToTalAddress = count($db_userdata);
    }

    return $ToTalAddress;
}

function GetUserSelectedLastOrderAddressCompanyLocationWise($iUserId, $eUserType = "Passenger", $passengerLat, $passengerLon, $iCompanyId, $iUserAddressId = "") {
    global $obj, $generalobj, $tconfig, $LIST_RESTAURANT_LIMIT_BY_DISTANCE;
    $ToTalAddress = 0;
    if ($iUserId == "" || $iUserId == 0 || $iUserId == NULL) {
        return $ToTalAddress;
    }

    if ($eUserType == "Passenger") {
        $UserType = "Rider";
    } else {
        $UserType = "Driver";
    }

    $ssql_user_address = "";
    if ($iUserAddressId != "" && $iUserAddressId > 0) {
        $ssql_user_address = " AND user_address.iUserAddressId = " . $iUserAddressId;
    }


    $sql = "select vRestuarantLocationLat,vRestuarantLocationLong from `company` where iCompanyId = '" . $iCompanyId . "'";
    $db_companydata = $obj->MySQLSelect($sql);
    $vRestuarantLocationLat = $db_companydata[0]['vRestuarantLocationLat'];
    $vRestuarantLocationLong = $db_companydata[0]['vRestuarantLocationLong'];

    $sql_userAddress = "SELECT ROUND(( 6371 * acos( cos( radians(cmp.vRestuarantLocationLat) )
            * cos( radians( ROUND(user_address.vLatitude ,8) ) )
            * cos( radians( ROUND(user_address.vLongitude,8) ) - radians(cmp.vRestuarantLocationLong) )
            + sin( radians(cmp.vRestuarantLocationLat) )
            * sin( radians( ROUND(user_address.vLatitude,8) ) ) ) ),2) AS distance, user_address.*  FROM `user_address`, `company` as cmp
            WHERE (user_address.vLatitude != '' AND user_address.vLatitude != '' AND user_address.eStatus='Active' AND user_address.iUserId='" . $iUserId . "' AND cmp.iCompanyId = '" . $iCompanyId . "' " . $ssql_user_address . ")
            HAVING distance < " . $LIST_RESTAURANT_LIMIT_BY_DISTANCE . " ORDER BY user_address.iUserAddressId DESC LIMIT 0,1";
    $UserAddressData = $obj->MySQLSelect($sql_userAddress);

    $UserSelectedAddressArr = array();
    if (count($UserAddressData) > 0) {
        $vAddressType = $UserAddressData[0]['vAddressType'];
        $vBuildingNo = $UserAddressData[0]['vBuildingNo'];
        $vLandmark = $UserAddressData[0]['vLandmark'];
        $vServiceAddress = $UserAddressData[0]['vServiceAddress'];
        $PickUpAddress = ($vAddressType != "") ? $vAddressType . "\n" : "";
        $PickUpAddress .= ($vBuildingNo != "") ? $vBuildingNo . "," : "";
        $PickUpAddress .= ($vLandmark != "") ? $vLandmark . "\n" : "";
        $PickUpAddress .= ($vServiceAddress != "") ? $vServiceAddress : "";
        $PickUpLatitude = $UserAddressData[0]['vLatitude'];
        $PickUpLongitude = $UserAddressData[0]['vLongitude'];
        $UserSelectedAddressArr['UserSelectedAddress'] = $PickUpAddress;
        $UserSelectedAddressArr['UserSelectedLatitude'] = $PickUpLatitude;
        $UserSelectedAddressArr['UserSelectedLongitude'] = $PickUpLongitude;
        $UserSelectedAddressArr['UserSelectedAddressId'] = $UserAddressData[0]['iUserAddressId'];
    }

    return $UserSelectedAddressArr;
}

function GenerateUniqueOrderNo() {
    global $generalobj, $obj, $tconfig;
    $random = substr(number_format(time() * rand(), 0, '', ''), 0, 10);
    $str = "select iOrderId from orders where vOrderNo ='" . $random . "'";
    $db_str = $obj->MySQLSelect($str);
    if (count($db_str) > 0) {
        $Generateuniqueorderno = GenerateUniqueOrderNo();
    } else {
        $Generateuniqueorderno = $random;
    }

    return $Generateuniqueorderno;
}

function FoodMenuItemBasicPrice($iMenuItemId, $iQty = 1) {
    global $generalobj, $obj, $tconfig;
    $fPrice = 0;
    $str = "select fPrice from menu_items where iMenuItemId ='" . $iMenuItemId . "'";
    $db_price = $obj->MySQLSelect($str);
    if (count($db_price) > 0) {
        $fPrice = $db_price[0]['fPrice'];
        $fPrice = $fPrice * $iQty;
    }

    return $fPrice;
}

function GetFoodMenuItemBasicPrice($iMenuItemId) {
    global $generalobj, $obj, $tconfig;
    $str = "select iFoodMenuId,fPrice,fOfferAmt from menu_items where iMenuItemId ='" . $iMenuItemId . "'";
    $db_price = $obj->MySQLSelect($str);
    $fPrice = $db_price[0]['fPrice'];
    $fOfferAmt = $db_price[0]['fOfferAmt'];
    if ($fOfferAmt > 0) {
        $fDiscountPrice = $fPrice - (($fPrice * $fOfferAmt) / 100);
    } else {
        $fDiscountPrice = $fPrice;
    }

    $fDiscountPrice = round($fDiscountPrice, 2);
    return $fDiscountPrice;
}

function GetFoodMenuItemOptionPrice($iOptionId = "") {
    global $generalobj, $obj, $tconfig;
    if ($iOptionId != "") {
        $str = "select iMenuItemId,fPrice from `menuitem_options` where iOptionId IN(" . $iOptionId . ")";
        $db_price = $obj->MySQLSelect($str);
        $fTotalPrice = 0;
        if (count($db_price) > 0) {
            for ($i = 0; $i < count($db_price); $i++) {
                $fPrice = $db_price[$i]['fPrice'];
                $fTotalPrice = $fTotalPrice + $fPrice;
            }
        }
    } else {
        $fTotalPrice = 0;
    }

    $fTotalPrice = round($fTotalPrice, 2);
    return $fTotalPrice;
}

function GetFoodMenuItemOptionIdPriceString($iOptionId = "") {
    global $generalobj, $obj, $tconfig;
    $OptionIdPriceString = "";
    if ($iOptionId != "") {
        $vOptionIdArr = explode(",", $iOptionId);
        if (count($vOptionIdArr) > 0) {
            for ($i = 0; $i < count($vOptionIdArr); $i++) {
                $OptionId = $vOptionIdArr[$i];
                $str = "select fPrice from `menuitem_options` where iOptionId = '" . $OptionId . "'";
                $db_price = $obj->MySQLSelect($str);
                $fPrice = $db_price[0]['fPrice'];
                $OptionIdPriceString .= $OptionId . "#" . $fPrice . ",";
            }
            //$OptionIdPriceString = substr($OptionIdPriceString, 0, -1);
            $OptionIdPriceString = trim($OptionIdPriceString, ",");
        }
    }
    return $OptionIdPriceString;
}

function GetFoodMenuItemAddOnPrice($vAddonId = "") {
    global $generalobj, $obj, $tconfig;
    if ($vAddonId != "") {
        $str = "select iMenuItemId,fPrice from `menuitem_options` where iOptionId IN(" . $vAddonId . ")";
        $db_price = $obj->MySQLSelect($str);
        $fTotalPrice = 0;
        if (count($db_price) > 0) {
            for ($i = 0; $i < count($db_price); $i++) {
                $fPrice = $db_price[$i]['fPrice'];
                $fTotalPrice = $fTotalPrice + $fPrice;
            }
        }
    } else {
        $fTotalPrice = 0;
    }

    $fTotalPrice = round($fTotalPrice, 2);
    return $fTotalPrice;
}

function GetFoodMenuItemAddOnIdPriceString($vAddonId = "") {
    global $generalobj, $obj, $tconfig;
    $AddOnIdPriceString = "";
    if ($vAddonId != "") {
        $vAddonIdArr = explode(",", $vAddonId);
        if (count($vAddonIdArr) > 0) {
            for ($i = 0; $i < count($vAddonIdArr); $i++) {
                $OptionId = $vAddonIdArr[$i];
                $str = "select fPrice from `menuitem_options` where iOptionId = '" . $OptionId . "'";
                $db_price = $obj->MySQLSelect($str);
                $fPrice = $db_price[0]['fPrice'];
                $AddOnIdPriceString .= $OptionId . "#" . $fPrice . ",";
            }
            //$AddOnIdPriceString = substr($AddOnIdPriceString, 0, -1);
            $AddOnIdPriceString = trim($AddOnIdPriceString, ",");
        }
    }
    return $AddOnIdPriceString;
}

function DisplayFoodMenuItemAddOnIdPriceString($vAddonId = "") {
    global $generalobj, $obj, $tconfig;
    $AddOnIdPriceString = "";
    if ($vAddonId != "") {
        $vAddonIdArr = explode(",", $vAddonId);
        if (count($vAddonIdArr) > 0) {
            for ($i = 0; $i < count($vAddonIdArr); $i++) {
                $OptionId = $vAddonIdArr[$i];
                $str = "select fPrice from `menuitem_options` where iOptionId = '" . $OptionId . "'";
                $db_price = $obj->MySQLSelect($str);
                $fPrice = $db_price[0]['fPrice'];
                $AddOnIdPriceString .= $OptionId . "#" . $fPrice . ",";
            }
            //$AddOnIdPriceString = substr($AddOnIdPriceString, 0, -1);
            $AddOnIdPriceString = trim($AddOnIdPriceString, ",");
        }
    }
    return $AddOnIdPriceString;
}

function getOrderDetailTotalPrice($iOrderId) {
    global $generalobj, $obj, $tconfig;
    $sql = "SELECT SUM( `fTotalPrice` ) AS totalprice FROM order_details WHERE iOrderId = '" . $iOrderId . "' AND eAvailable = 'Yes'";
    $data = $obj->MySQLSelect($sql);
    $totalprice = $data[0]['totalprice'];
    if ($totalprice == "" || $totalprice == NULL) {
        $totalprice = 0;
    }

    return $totalprice;
}

function getOrderDeliveryCharge($iOrderId, $fSubTotal) {
    global $generalobj, $obj, $tconfig;
    $fDeliveryCharge = 0;
    $sql = "SELECT ord.iUserId,ord.iCompanyId,ua.vLatitude as passengerlat,ua.vLongitude as passengerlong,co.vRestuarantLocationLat as restaurantlat,co.vRestuarantLocationLong as restaurantlong FROM orders as ord LEFT JOIN user_address as ua ON ord.iUserAddressId=ua.iUserAddressId LEFT JOIN company as co ON ord.iCompanyId=co.iCompanyId WHERE ord.iOrderId = '" . $iOrderId . "'";
    $data = $obj->MySQLSelect($sql);
    if (count($data) > 0) {
        $User_Address_Array = array(
            $data[0]['passengerlat'],
            $data[0]['passengerlong']
        );
        $iLocationId = GetUserGeoLocationId($User_Address_Array);
        $checkAllLocation = 1;
        if ($iLocationId > 0) {
            $sql = "SELECT * FROM `delivery_charges` WHERE iLocationId = '" . $iLocationId . "'";
            $data_location = $obj->MySQLSelect($sql);
            if (count($data_location) > 0) {
                $checkAllLocation = 0;
            }
        }
        if ($checkAllLocation == 1) {
            $sql = "SELECT * FROM `delivery_charges` WHERE iLocationId = '0' AND eStatus='Active'";
            $data_location = $obj->MySQLSelect($sql);
        }
        if (count($data_location) > 0) {
            $iFreeDeliveryRadius = $data_location[0]['iFreeDeliveryRadius'];
            $distance = distanceByLocation($data[0]['passengerlat'], $data[0]['passengerlong'], $data[0]['restaurantlat'], $data[0]['restaurantlong'], "K");
            if ($distance < $iFreeDeliveryRadius && $iFreeDeliveryRadius >= 0 && !empty($iFreeDeliveryRadius)) { //when zero for free order and radius then do not allow free order
                $fDeliveryCharge = 0;
                return $fDeliveryCharge;
            }

            $fFreeOrderPriceSubtotal = $data_location[0]['fFreeOrderPriceSubtotal'];
            if (!empty($fFreeOrderPriceSubtotal) && $fFreeOrderPriceSubtotal != 0 && !empty($fFreeOrderPriceSubtotal)) { //when zero for free order and radius then do not allow free order
            if ($fSubTotal > $fFreeOrderPriceSubtotal) {
                $fDeliveryCharge = 0;
                return $fDeliveryCharge;
            }
            }

            $fOrderPriceValue = $data_location[0]['fOrderPriceValue'];
            $fDeliveryChargeAbove = $data_location[0]['fDeliveryChargeAbove'];
            $fDeliveryChargeBelow = $data_location[0]['fDeliveryChargeBelow'];
            if ($fSubTotal >= $fOrderPriceValue) {
                $fDeliveryCharge = $fDeliveryChargeAbove;
                //$fDeliveryCharge = $fDeliveryChargeBelow;
                return $fDeliveryCharge;
            } else {
                $fDeliveryCharge = $fDeliveryChargeBelow;
                //$fDeliveryCharge = $fDeliveryChargeAbove;
                return $fDeliveryCharge;
            }
        } else {
            $fDeliveryCharge = 0;
            return $fDeliveryCharge;
        }
    }
}

########################### created by pratik 22-1-2019##########################

function checkamount($data) {
    global $generalobj, $obj, $ADMIN_COMMISSION, $ENABLE_STORE_COMMISSION;
    $defaultCurrency = get_value('currency', 'vName', 'eDefault', 'Yes', '', 'true');
    $couponCode = $data['vCouponCode'];
    $iCompanyId = $data['iCompanyId'];
    $iUserId = $data['iUserId'];
    $ePaymentOption = $data['ePaymentOption'];
    $fChangeAmount = $data['fChangeAmount'];
    $iUserAddressId = $data['iUserAddressId'];
    //Added By HJ On 30-01-2019 For Get Company Data Start
    $companyData = $obj->MySQLSelect("SELECT fPackingCharge FROM company WHERE iCompanyId='" . $iCompanyId . "'");
    $fPackingCharge = $fCompanyTax = $fComissionPerOrder = 0;
    if (count($companyData) > 0) {
        $fPackingCharge = $companyData[0]['fPackingCharge'];
        //$fCompanyTax = $companyData[0]['fCompanyTax'];
        //$fComissionPerOrder = $companyData[0]['fComissionPerOrder'];
    }
    //$fPackingCharge = get_value('company', 'fPackingCharge', 'iCompanyId', $iCompanyId, '', 'true');
    //$fCompanyTax = get_value('company', 'fCompanyTax', 'iCompanyId', $iCompanyId, '', 'true');
    //Added By HJ On 30-01-2019 For Get Company Data End
    $fSubTotal = $data['fSubTotal'];
    $TotalDiscountPrice = $data['fTotalDiscountPrice'];
    //$fOffersDiscount = CheckCalculateOrderDiscountPrice($fSubTotal, $iCompanyId, $iUserId, $TotalDiscountPrice); // Commented BY HJ On 05-02-2019 For Solved Error Mismatch Offeramount with Type CheckOutOrderEstimateDetails and Add Below Live
    $fOffersDiscount = $TotalDiscountPrice;
    $fDeliveryCharge = CheckgetOrderDeliveryCharge($iCompanyId, $iUserAddressId, $fSubTotal);
    $fTax = 0;
    if ($fSubTotal == 0) {
        $fPackingCharge = $fDeliveryCharge = $fTax = 0;
    }
    if ($fCompanyTax > 0) {
        $fcotaxamount = $fSubTotal - $fOffersDiscount + $fPackingCharge;
        $fCompanyTax = round((($fcotaxamount * $fCompanyTax) / 100), 2);
    } else {
        $TaxArr = getMemberCountryTax($iUserId, "Passenger");
        $fTax = $TaxArr['fTax1'];
        if ($fTax > 0) {
            $ftaxamount = $fSubTotal - $fOffersDiscount + $fPackingCharge;
            $fTax = round((($ftaxamount * $fTax) / 100), 2);
        }
    }
    $UserDetailsArr = getUserCurrencyLanguageDetails($iUserId);

    $Ratio = $UserDetailsArr['Ratio'];
    $currencySymbol = $UserDetailsArr['currencySymbol'];
    $sql = "select vCompany,fMaxOfferAmt,fTargetAmt,fOfferType,fOfferAppyType,iMaxItemQty,fOfferAmt,store_currency from `company` where iCompanyId = '" . $iCompanyId . "'";
    $db_companydata = $obj->MySQLSelect($sql);
    //Added By HJ On 04-02-2019 For Convert All Amount In Store Wise Currency Ration Start
    $currencyArr = $currencySymbolArr = array();
    $getCurrencyRation = $obj->MySQLSelect("SELECT Ratio,iCurrencyId,vSymbol FROM currency WHERE eStatus='Active'");
    for ($c = 0; $c < count($getCurrencyRation); $c++) {
        $currencyArr[$getCurrencyRation[$c]['iCurrencyId']] = $getCurrencyRation[$c]['Ratio'];
        $currencySymbolArr[$getCurrencyRation[$c]['iCurrencyId']] = $getCurrencyRation[$c]['vSymbol'];
    }
    if (isset($db_companydata[0]['store_currency'])) {
        $store_currency = $db_companydata[0]['store_currency'];
        if (isset($currencyArr[$store_currency])) {
            $Ratio = $currencyArr[$store_currency];
            $currencySymbol = $currencySymbolArr[$store_currency];
        }
    }
    //	print_r($Ratio);die;
    $fNetTotal = $fSubTotal + $fPackingCharge + $fDeliveryCharge + $fTax + $fCompanyTax;
    $fTotalGenerateFare = $fNetTotal;

    /*
      old  when Commission charge

     */
    $fOrderFare_For_Commission = $fSubTotal - $fOffersDiscount + $fPackingCharge + $fTax + $fCompanyTax;

    /*
      //Added By PM On 20-02-2019 For Applied Store Wise Commission Per Order Start
      //$fOrderFare_For_Commission = $fSubTotal;
      //Added By PM On 20-02-2019 For Applied Store Wise Commission Per Order end
     */

    //Added By HJ On 30-01-2019 For Applied Store Wise Commission Per Order If > 0 and ENABLE_STORE_COMMISSION==Yes Start
    $fCommision = $ADMIN_COMMISSION;
    if ($fComissionPerOrder > 0 && $ENABLE_STORE_COMMISSION == "Yes") {
        $fCommision = $fComissionPerOrder;
    }
    $fCommision = round((($fOrderFare_For_Commission * $fCommision) / 100), 2);
    //Added By HJ On 30-01-2019 For Applied Store Wise Commission Per Order If > 0 and ENABLE_STORE_COMMISSION==Yes End
    if ($fOffersDiscount > 0) {
        $fNetTotal = $fNetTotal - $fOffersDiscount;
    }
    /* Checking For Passenger Outstanding Amount */
    $fOutStandingAmount = 0;
    $fOutStandingAmount = GetPassengerOutstandingAmount($iUserId);
    if ($fOutStandingAmount > 0) {
        $fNetTotal = $fNetTotal + $fOutStandingAmount;
        $fTotalGenerateFare = $fTotalGenerateFare + $fOutStandingAmount;
    }
    /* Checking For Passenger Outstanding Amount */
    /* Check Coupon Code For Count Total Fare Start */
    $discountValue = 0;
    $discountValueType = "cash";
    if ($couponCode != '') {
        $couponCodeData = get_value('coupon', 'fDiscount,eType', 'vCouponCode', $couponCode, '', '');
        if (isset($couponCodeData[0]['fDiscount']) && $couponCodeData[0]['fDiscount'] > 0) {
            $discountValue = $couponCodeData[0]['fDiscount'];
        }
        if (isset($couponCodeData[0]['eType']) && $couponCodeData[0]['eType'] != "") {
            $discountValueType = $couponCodeData[0]['eType'];
        }
    }
    if ($couponCode != '' && $discountValue != 0) {
        if ($discountValueType == "percentage") {
            $vDiscount = round($discountValue, 1) . ' ' . "%";
            $discountValue = round(($fNetTotal * $discountValue), 1) / 100;
        } else {
            $curr_sym = get_value('currency', 'vSymbol', 'eDefault', 'Yes', '', 'true');
            if ($discountValue > $fNetTotal) {
                $vDiscount = round($fNetTotal, 1) . ' ' . $curr_sym;
            } else {
                $vDiscount = round($discountValue, 1) . ' ' . $curr_sym;
            }
        }
        $fNetTotal = $fNetTotal - $discountValue;
        if ($fNetTotal < 0) {
            $fNetTotal = 0;
        }
        $Order_data[0]['fDiscount'] = $discountValue;
        $Order_data[0]['vDiscount'] = $vDiscount;
    }
    /* Check Coupon Code Total Fare  End */
    /* Check debit wallet For Count Total Fare changeAmount Start */
    $wallet_adjustment = "No";
    $CheckUserWallet = $data['eCheckUserWallet'];
    if ($ePaymentOption == "Cash" && $CheckUserWallet == "Yes") {
        $user_available_balance = $generalobj->get_user_available_balance($iUserId, "Rider");
        $user_wallet_debit_amount = 0;
        if ($fNetTotal > $user_available_balance) {
            $fNetTotal = $fNetTotal - $user_available_balance;
            $user_wallet_debit_amount = $user_available_balance;
            $wallet_adjustment = "No";
        } else {
            $user_wallet_debit_amount = ($fNetTotal > 0) ? $fNetTotal : 0;
            $fNetTotal = 0;
            $wallet_adjustment = "Yes";
        }
    }
    if ($ePaymentOption == "Cash" && $CheckUserWallet == "Yes" && $wallet_adjustment == "No") {
        if ($fChangeAmount == 0 || $fChangeAmount == "0") {
            $finalFareData['Action'] = "1";
            $finalFareData['fChangeTotal'] = ($fChangeAmount > 0) ? $currencySymbol . " " . formatnum($fChangeAmount) : $currencySymbol . " 0";
            $finalFareData['fChangeAmount'] = $fChangeAmount;
            $payChangeAmount = $fChangeAmount - $fNetTotal;
            $payChangeAmount = round($payChangeAmount, 2);
            $finalFareData['fPayChangeTotal'] = ($payChangeAmount > 0) ? $currencySymbol . " " . formatnum($payChangeAmount) : $currencySymbol . " 0";
            $finalFareData['fPayChangeAmount'] = $payChangeAmount;
        } else {
            if (formatnum($fChangeAmount) >= formatnum($fNetTotal)) {
                $finalFareData['Action'] = "1";
                $finalFareData['fChangeTotal'] = ($fChangeAmount > 0) ? $currencySymbol . " " . formatnum($fChangeAmount) : $currencySymbol . " 0";
                $finalFareData['fChangeAmount'] = $fChangeAmount;
                $payChangeAmount = $fChangeAmount - $fNetTotal;
                $payChangeAmount = round($payChangeAmount, 2);
                $finalFareData['fPayChangeTotal'] = ($payChangeAmount > 0) ? $currencySymbol . " " . formatnum($payChangeAmount) : $currencySymbol . " 0";
                $finalFareData['fPayChangeAmount'] = $payChangeAmount;
            } else {
                $finalFareData['Action'] = "0";
                $finalFareData['message'] = "LBL_BILL_VALUE_ERROR_TXT";
            }
        }
    } elseif ($ePaymentOption == "Cash" && $CheckUserWallet == "No" && $wallet_adjustment == "No") {
        if ($fChangeAmount == 0 || $fChangeAmount == "0") {
            $finalFareData['Action'] = "1";
            $finalFareData['fChangeTotal'] = ($fChangeAmount > 0) ? $currencySymbol . " " . formatnum($fChangeAmount) : $currencySymbol . " 0";
            $finalFareData['fChangeAmount'] = $fChangeAmount;
            $payChangeAmount = $fChangeAmount - $fNetTotal;
            $payChangeAmount = round($payChangeAmount, 2);
            $finalFareData['fPayChangeTotal'] = ($payChangeAmount > 0) ? $currencySymbol . " " . formatnum($payChangeAmount) : $currencySymbol . " 0";
            $finalFareData['fPayChangeAmount'] = $payChangeAmount;
        } else {
            if (formatnum($fChangeAmount) >= formatnum($fNetTotal)) {
                $finalFareData['Action'] = "1";
                $finalFareData['fChangeTotal'] = ($fChangeAmount > 0) ? $currencySymbol . " " . formatnum($fChangeAmount) : $currencySymbol . " 0";
                $finalFareData['fChangeAmount'] = $fChangeAmount;
                $payChangeAmount = $fChangeAmount - $fNetTotal;
                $payChangeAmount = round($payChangeAmount, 2);
                $finalFareData['fPayChangeTotal'] = ($payChangeAmount > 0) ? $currencySymbol . " " . formatnum($payChangeAmount) : $currencySymbol . " 0";
                $finalFareData['fPayChangeAmount'] = $payChangeAmount;
            } else {
                $finalFareData['Action'] = "0";
                $finalFareData['message'] = "LBL_BILL_VALUE_ERROR_TXT";
            }
        }
    } else {
        $finalFareData['Action'] = "1";
        $finalFareData['fPayChangeTotal'] = $finalFareData['fPayChangeAmount'] = "0";
    }
    //print_r($finalFareData);exit;
    return $finalFareData;
}

########################### Get Total Order Discount Amount From order detail for menu item wise##########################
########################### created by pratik 22-1-2019##########################

function CheckCalculateOrderDiscountPrice($fSubTotal, $iCompanyId, $iUserId, $fTotalDiscountPrice) {
    global $obj, $generalobj, $tconfig;
    $sql = "select * from orders where iOrderId='" . $iOrderId . "'";
    $data_order = $obj->MySQLSelect($sql);

    // $fSubTotal = $data_order[0]['fSubTotal'];
    // $fSubTotal = getOrderDetailSubTotalPrice($iOrderId);

    $TotOrders = 1;
    if ($iUserId > 0) {
        $sql = "select count(iOrderId) as TotOrders from orders where iUserId ='" . $iUserId . "' AND iCompanyId = '" . $iCompanyId . "' AND iStatusCode NOT IN(12)";
        $db_order = $obj->MySQLSelect($sql);
        $TotOrders = $db_order[0]['TotOrders'];
    }

    $sql = "SELECT * FROM `company` WHERE iCompanyId = '" . $iCompanyId . "'";
    $DataCompany = $obj->MySQLSelect($sql);
    $fMinOrderValue = $DataCompany[0]['fMinOrderValue'];
    $fOfferAppyType = $DataCompany[0]['fOfferAppyType'];
    $fOfferType = $DataCompany[0]['fOfferType'];
    $fMaxOfferAmt = $DataCompany[0]['fMaxOfferAmt'];
    $fTargetAmt = $DataCompany[0]['fTargetAmt'];
    $fOfferAmt = $DataCompany[0]['fOfferAmt'];
    if ($fOfferAppyType == "None") {
        $TotalDiscountPrice = $fTotalDiscountPrice;
    } else if ($fOfferAppyType == "All") {
        if ($fSubTotal >= $fTargetAmt) {
            if ($fOfferType == "Percentage") {
                $fDiscount = (($fSubTotal * $fOfferAmt) / 100);
                $fDiscount = round($fDiscount, 2);
                $fDiscount = (($fDiscount > $fMaxOfferAmt) && ($fMaxOfferAmt > 0)) ? $fMaxOfferAmt : $fDiscount;
                $TotalDiscountPrice = $fDiscount;
            } else {
                $fDiscount = $fOfferAmt;
                //Added By HJ On 28-02-2020 For Solved 141 Mantis Issue #3793 Start 
                if($fOfferAmt > $fSubTotal){
                    $fDiscount = $fSubTotal;
                }
                //Added By HJ On 28-02-2020 For Solved 141 Mantis Issue #3793 End
                $fDiscount = round($fDiscount, 2);
                $TotalDiscountPrice = $fDiscount;
            }
        } else {
            $TotalDiscountPrice = 0;
        }
    } else {
        if ($TotOrders <= 1) {
            if ($fSubTotal >= $fTargetAmt) {
                if ($fOfferType == "Percentage") {
                    $fDiscount = (($fSubTotal * $fOfferAmt) / 100);
                    $fDiscount = round($fDiscount, 2);
                    $fDiscount = (($fDiscount > $fMaxOfferAmt) && ($fMaxOfferAmt > 0)) ? $fMaxOfferAmt : $fDiscount;
                    $TotalDiscountPrice = $fDiscount;
                } else {
                    $fDiscount = $fOfferAmt;
                    //Added By HJ On 28-02-2020 For Solved 141 Mantis Issue #3793 Start 
                    if($fOfferAmt > $fSubTotal){
                        $fDiscount = $fSubTotal;
                    }
                    //Added By HJ On 28-02-2020 For Solved 141 Mantis Issue #3793 End
                    $fDiscount = round($fDiscount, 2);
                    $TotalDiscountPrice = $fDiscount;
                }
            } else {
                $TotalDiscountPrice = 0;
            }
        } else {
            $TotalDiscountPrice = $fTotalDiscountPrice;
        }
    }

    return round($TotalDiscountPrice, 2);
}

function CheckgetOrderDeliveryCharge($iCompanyId, $iUserAddressId, $fSubTotal) {
    global $generalobj, $obj, $tconfig;
    $fDeliveryCharge = 0;

    $sql = "SELECT  ua.vLatitude as passengerlat,ua.vLongitude as passengerlong  FROM user_address as ua WHERE ua.iUserAddressId = '" . $iUserAddressId . "'";
    $data = $obj->MySQLSelect($sql);

    $sql = "SELECT  co.vRestuarantLocationLat as restaurantlat,co.vRestuarantLocationLong as restaurantlong FROM company as co WHERE co.iCompanyId = '" . $iCompanyId . "'";
    $datal = $obj->MySQLSelect($sql);
    if (count($data) > 0) {
        $User_Address_Array = array(
            $data[0]['passengerlat'],
            $data[0]['passengerlong']
        );

        $Rest_Address_Array = array(
            $datal[0]['restaurantlat'],
            $datal[0]['restaurantlong']
        );
        $iToLocationId = GetUserGeoLocationId($User_Address_Array);  /*  user location */
        $iLocationId = GetUserGeoLocationId($Rest_Address_Array);    /*  restaurant location */


        if ($iToLocationId != "0") {
            $sql = "SELECT * FROM  `delivery_charges` WHERE ";
            $iToLocationId = ltrim($iToLocationId, "0.,");
            $iLocationId = ltrim($iLocationId, "0.,");
            $iToLocationId = explode(",", $iToLocationId);
            $iLocationId = explode(",", $iLocationId);
            $countuser = count($iToLocationId);
            $countrest = count($iLocationId);
            $counttotal = $countrest * $countuser;
            $cott = $cot = 1;
            if ($countuser >= $countrest) {

                for ($ui = 0; $ui < $countuser; $ui++) {
                    for ($ri = 0; $ri < $countrest; $ri++) {
                        $sql .= "  ( iToLocationId =  '" . $iToLocationId[$ui] . "' AND iLocationId =  '" . $iLocationId[$ri] . "' AND eStatus =  'Active') ";

                        if ($cot != $counttotal) {
                            $sql .= " OR ";
                        }
                        $cot++;
                    }
                }
            } else {

                for ($rri = 0; $rri < $countrest; $rri++) {
                    for ($uui = 0; $uui < $countuser; $uui++) {
                        $sql .= "  ( iToLocationId =  '" . $iToLocationId[$uui] . "' AND iLocationId =  '" . $iLocationId[$rri] . "' AND eStatus =  'Active') ";

                        if ($cott != $counttotal) {
                            $sql .= " OR ";
                        }
                        $cott++;
                    }
                }
            }

            $sql .= " LIMIT 0,1";

            /*   $sql = "SELECT * FROM `delivery_charges` WHERE iLocationId	= '" . $iLocationId . "' and iToLocationId	= '" . $iToLocationId . "' AND eStatus='Active'"; */
            $data_location = $obj->MySQLSelect($sql);

            if (count($data_location) > 0) {
                $iFreeDeliveryRadius = $data_location[0]['iFreeDeliveryRadius'];
                $distance = distanceByLocation($data[0]['passengerlat'], $data[0]['passengerlong'], $datal[0]['restaurantlat'], $datal[0]['restaurantlong'], "K");
                if ($distance < $iFreeDeliveryRadius) {
                    $fDeliveryCharge = 0;
                    return $fDeliveryCharge;
                }
                $fFreeOrderPriceSubtotal = $data_location[0]['fFreeOrderPriceSubtotal'];
                if ($fSubTotal > $fFreeOrderPriceSubtotal && $fFreeOrderPriceSubtotal != 0) {
                    $fDeliveryCharge = 0;
                    return $fDeliveryCharge;
                }
                $fOrderPriceValue = $data_location[0]['fOrderPriceValue'];
                $fDeliveryChargeAbove = $data_location[0]['fDeliveryChargeAbove'];
                $fDeliveryChargeBelow = $data_location[0]['fDeliveryChargeBelow'];
                if ($fSubTotal > $fOrderPriceValue) {
                    $fDeliveryCharge = $fDeliveryChargeAbove;
                    return $fDeliveryCharge;
                } else {
                    $fDeliveryCharge = $fDeliveryChargeBelow;
                    return $fDeliveryCharge;
                }
            } else {
                $fDeliveryCharge = 0;
                return $fDeliveryCharge;
            }
        } else {
            $fDeliveryCharge = 0;
            return $fDeliveryCharge;

            /*
              $sql = "SELECT * FROM `delivery_charges` WHERE iLocationId = '0' AND eStatus='Active'";
              $data_location = $obj->MySQLSelect($sql);

              if (count($data_location) > 0) {
              $iFreeDeliveryRadius = $data_location[0]['iFreeDeliveryRadius'];
              $distance = distanceByLocation($data[0]['passengerlat'], $data[0]['passengerlong'], $data[0]['restaurantlat'], $data[0]['restaurantlong'], "K");
              if ($distance < $iFreeDeliveryRadius) {
              $fDeliveryCharge = 0;
              return $fDeliveryCharge;
              }

              $fFreeOrderPriceSubtotal = $data_location[0]['fFreeOrderPriceSubtotal'];
              if ($fSubTotal > $fFreeOrderPriceSubtotal) {
              $fDeliveryCharge = 0;
              return $fDeliveryCharge;
              }

              $fOrderPriceValue = $data_location[0]['fOrderPriceValue'];
              $fDeliveryChargeAbove = $data_location[0]['fDeliveryChargeAbove'];
              $fDeliveryChargeBelow = $data_location[0]['fDeliveryChargeBelow'];
              if ($fSubTotal >= $fOrderPriceValue) {
              $fDeliveryCharge = $fDeliveryChargeAbove;
              //$fDeliveryCharge = $fDeliveryChargeBelow;
              return $fDeliveryCharge;
              } else {
              $fDeliveryCharge = $fDeliveryChargeBelow;
              //$fDeliveryCharge = $fDeliveryChargeAbove;
              return $fDeliveryCharge;
              }
              }else{
              $fDeliveryCharge = 0;
              return $fDeliveryCharge;
              }
             */
        }
    }
}

function calculateOrderFare($iOrderId) {
    global $generalobj, $obj, $ADMIN_COMMISSION, $SYSTEM_PAYMENT_FLOW, $eWalletIgnore;
    $defaultCurrency = get_value('currency', 'vName', 'eDefault', 'Yes', '', 'true');
    $sql = "select * from orders where iOrderId='" . $iOrderId . "'";
    $data_order = $obj->MySQLSelect($sql);
    $couponCode = isset($data_order[0]['vCouponCode']) ? $data_order[0]['vCouponCode'] : '';
    $iCompanyId = isset($data_order[0]['iCompanyId']) ? $data_order[0]['iCompanyId'] : '0';
    $iUserId = isset($data_order[0]['iUserId']) ? $data_order[0]['iUserId'] : '0';
    $iTripId = isset($data_order[0]['iTripId']) ? $data_order[0]['iTripId'] : '';
    $ePaymentOption = isset($data_order[0]['ePaymentOption']) ? $data_order[0]['ePaymentOption'] : '';
    $fPackingCharge = get_value('company', 'fPackingCharge', 'iCompanyId', $iCompanyId, '', 'true');
    $fSubTotal = getOrderDetailTotalPrice($iOrderId);
    $fOffersDiscount = CalculateOrderDiscountPrice($iOrderId);
    $fDeliveryCharge = getOrderDeliveryCharge($iOrderId, $fSubTotal);
    $TaxArr = getMemberCountryTax($iUserId, "Passenger");
    $fTax = $TaxArr['fTax1'];
    if ($fTax > 0) {
        $ftaxamount = $fSubTotal - $fOffersDiscount + $fPackingCharge;
        $fTax = $generalobj->setTwoDecimalPoint((($ftaxamount * $fTax) / 100));
    }
    if ($fSubTotal == 0) {
        $fPackingCharge = $fDeliveryCharge = $fTax = 0;
    }
    $fNetTotal = $fTotalGenerateFare = $fSubTotal + $fPackingCharge + $fDeliveryCharge + $fTax;
    $fOrderFare_For_Commission = $fSubTotal - $fOffersDiscount + $fPackingCharge + $fTax;
    $fCommision = $generalobj->setTwoDecimalPoint((($fOrderFare_For_Commission * $ADMIN_COMMISSION) / 100));
    if ($fOffersDiscount > 0) {
        $fNetTotal = $fNetTotal - $fOffersDiscount;
    }
    /* Check Coupon Code For Count Total Fare Start */
    $discountValue = 0;
    $discountValueType = "cash";
    if ($couponCode != '') {
        //Added By HJ On 18-01-2019 For Check and Get Active Coupon Data Start
        $getCouponCode = $obj->MySQLSelect("SELECT fDiscount,eType FROM coupon WHERE vCouponCode='" . $couponCode . "' AND eStatus='Active'");
        if (count($getCouponCode) > 0) {
            $discountValue = $getCouponCode[0]['fDiscount'];
            $discountValueType = $getCouponCode[0]['eType'];
        }
        //Added By HJ On 18-01-2019 For Check and Get Active Coupon Data End
        //$discountValue = get_value('coupon', 'fDiscount', 'vCouponCode', $couponCode, '', 'true'); //Commented By HJ On 18-01-2019
        //$discountValueType = get_value('coupon', 'eType', 'vCouponCode', $couponCode, '', 'true'); //Commented By HJ On 18-01-2019
    }
    if ($couponCode != '' && $discountValue != 0) {
        if ($discountValueType == "percentage") {
            $discountApplyOn = $fNetTotal - ($fDeliveryCharge+$fTax); // Added By HJ On 27-06-2019 As Per Discuss With BM Mam // Tax Minus From Coupon Code As Per Discuss With CD sir and KS Sir On 31-01-2020
            $vDiscount = $generalobj->setTwoDecimalPoint($discountValue) . ' ' . "%";
            $discountValue = $generalobj->setTwoDecimalPoint(($discountApplyOn * $discountValue)) / 100;
        } else {
            $curr_sym = get_value('currency', 'vSymbol', 'eDefault', 'Yes', '', 'true');
            if ($discountValue > $fNetTotal) {
                $vDiscount = $generalobj->setTwoDecimalPoint($fNetTotal) . ' ' . $curr_sym;
            } else {
                $vDiscount = $generalobj->setTwoDecimalPoint($discountValue) . ' ' . $curr_sym;
            }
        }

        $fNetTotal = $fNetTotal - $discountValue;
        $Order_data[0]['fDiscount'] = $discountValue;
        $Order_data[0]['vDiscount'] = $vDiscount;
    }
    /* Checking For Passenger Outstanding Amount */
    $fOutStandingAmount = GetPassengerOutstandingAmount($iUserId);
    $fOutStandingAmount = $generalobj->setTwoDecimalPoint($fOutStandingAmount);
    if ($fOutStandingAmount > 0) {
        $fNetTotal = $fNetTotal + $fOutStandingAmount;
        $fTotalGenerateFare = $fTotalGenerateFare + $fOutStandingAmount;
    }
    /* Checking For Passenger Outstanding Amount */
    /* Check Coupon Code Total Fare  End */
    /* Check debit wallet For Count Total Fare  Start */
    $CheckUserWallet = $data_order[0]['eCheckUserWallet'];
    $user_wallet_debit_amount = 0;
    if ($iUserId > 0 && $ePaymentOption == "Cash" && $CheckUserWallet == "Yes") {
        $user_available_balance = $generalobj->get_user_available_balance($iUserId, "Rider");
        if ($fNetTotal > $user_available_balance) {
            $fNetTotal = $fNetTotal - $user_available_balance;
            $user_wallet_debit_amount = $user_available_balance;
        } else {
            $user_wallet_debit_amount = ($fNetTotal > 0) ? $fNetTotal : 0;
            $fNetTotal = 0;
        }
        // Update User Wallet
        if ($user_wallet_debit_amount > 0) {
            $vRideNo = $data_order[0]['vOrderNo'];
            $data_wallet['iUserId'] = $iUserId;
            $data_wallet['eUserType'] = "Rider";
            $data_wallet['iBalance'] = $user_wallet_debit_amount;
            $data_wallet['eType'] = "Debit";
            $data_wallet['dDate'] = date("Y-m-d H:i:s");
            $data_wallet['iTripId'] = $iTripId;
            $data_wallet['iOrderId'] = $iOrderId;
            $data_wallet['eFor'] = "Booking";
            $data_wallet['ePaymentStatus'] = "Unsettelled";
            $data_wallet['tDescription'] = "#LBL_DEBITED_BOOKING_DL#" . $vRideNo;
            $generalobj->InsertIntoUserWallet($data_wallet['iUserId'], $data_wallet['eUserType'], $data_wallet['iBalance'], $data_wallet['eType'], $data_wallet['iTripId'], $data_wallet['eFor'], $data_wallet['tDescription'], $data_wallet['ePaymentStatus'], $data_wallet['dDate'], $data_wallet['iOrderId']);

            // $obj->MySQLQueryPerform("user_wallet",$data_wallet,'insert');
        }
    }
    if ($iUserId > 0 && $ePaymentOption != 'Cash') {
        if ($SYSTEM_PAYMENT_FLOW == 'Method-2') {
            $user_available_balance_wallet = $generalobj->get_user_available_balance($iUserId, "Rider");
            if ($fNetTotal > $user_available_balance_wallet) {
                if ($eWalletIgnore == 'Yes') {
                    $fNetTotal = $fNetTotal - $user_available_balance_wallet;
                    $user_wallet_debit_amount = $user_available_balance_wallet;
                }
            } else {
                $user_wallet_debit_amount = ($fNetTotal > 0) ? $fNetTotal : 0;
                $fNetTotal = 0;
            }
            // Update User Wallet
            if ($user_wallet_debit_amount > 0) {
                $vRideNo = $data_order[0]['vOrderNo'];
                $data_wallet['iUserId'] = $iUserId;
                $data_wallet['eUserType'] = "Rider";
                $data_wallet['iBalance'] = $user_wallet_debit_amount;
                $data_wallet['eType'] = "Debit";
                $data_wallet['dDate'] = date("Y-m-d H:i:s");
                $data_wallet['iTripId'] = $iTripId;
                $data_wallet['iOrderId'] = $iOrderId;
                $data_wallet['eFor'] = "Booking";
                $data_wallet['ePaymentStatus'] = "Unsettelled";
                $data_wallet['tDescription'] = "#LBL_DEBITED_BOOKING_DL#" . $vRideNo;
                $generalobj->InsertIntoUserWallet($data_wallet['iUserId'], $data_wallet['eUserType'], $data_wallet['iBalance'], $data_wallet['eType'], $data_wallet['iTripId'], $data_wallet['eFor'], $data_wallet['tDescription'], $data_wallet['ePaymentStatus'], $data_wallet['dDate'], $data_wallet['iOrderId']);
            }
            $where = " iOrderId = '" . $iOrderId . "'";
            $Data_update_order_new['iStatusCode'] = 1;
            $Order_Update_Id = $obj->MySQLQueryPerform("orders", $Data_update_order_new, 'update', $where);
            $OrderLogId = createOrderLog($iOrderId, $Data_update_order_new['iStatusCode']);
        }
    }
    /* Check debit wallet For Count Total Fare  End */
    if ($fNetTotal < 0) {
        $fNetTotal = $fTotalGenerateFare = 0;
    }
    $finalFareData['fSubTotal'] = $fSubTotal;
    $finalFareData['fOffersDiscount'] = $fOffersDiscount;
    $finalFareData['fPackingCharge'] = $fPackingCharge;
    $finalFareData['fDeliveryCharge'] = $fDeliveryCharge;
    $finalFareData['fTax'] = $fTax;
    $finalFareData['fDiscount'] = isset($Order_data[0]['fDiscount']) ? $Order_data[0]['fDiscount'] : '0.00';
    $finalFareData['vDiscount'] = isset($Order_data[0]['vDiscount']) ? $Order_data[0]['vDiscount'] : '';
    $finalFareData['fCommision'] = $fCommision;
    $finalFareData['fNetTotal'] = $fNetTotal;
    $finalFareData['fTotalGenerateFare'] = $fTotalGenerateFare;
    $finalFareData['fOutStandingAmount'] = $fOutStandingAmount;
    $finalFareData['fWalletDebit'] = $user_wallet_debit_amount;
    return $finalFareData;
}

/* function calculateOrderFare($iOrderId) {
  global $generalobj, $obj, $ADMIN_COMMISSION, $ENABLE_STORE_COMMISSION;
  $defaultCurrency = get_value('currency', 'vName', 'eDefault', 'Yes', '', 'true');
  $sql = "select * from orders where iOrderId='" . $iOrderId . "'";
  $data_order = $obj->MySQLSelect($sql);
  $couponCode = $data_order[0]['vCouponCode'];
  $iCompanyId = $data_order[0]['iCompanyId'];
  $iUserId = $data_order[0]['iUserId'];
  $iTripId = $data_order[0]['iTripId'];
  $eOrderplaced_by = $data_order[0]['eOrderplaced_by'];
  $fChangeAmount = $data_order[0]['fChangeAmount'];
  $ePaymentOption = $data_order[0]['ePaymentOption'];
  //Added By HJ On 30-01-2019 For Get Company Data Start
  $companyData = $obj->MySQLSelect("SELECT fPackingCharge,fComissionPerOrder FROM company WHERE iCompanyId='" . $iCompanyId . "'");
  $fPackingCharge = $fCompanyTax = $fComissionPerOrder = 0;
  if (count($companyData) > 0) {
  $fPackingCharge = $companyData[0]['fPackingCharge'];
  //$fCompanyTax = $companyData[0]['fCompanyTax'];
  //$fComissionPerOrder = $companyData[0]['fComissionPerOrder'];
  }
  //$fPackingCharge = get_value('company', 'fPackingCharge', 'iCompanyId', $iCompanyId, '', 'true');
  //$fCompanyTax = get_value('company', 'fCompanyTax', 'iCompanyId', $iCompanyId, '', 'true');
  //Added By HJ On 30-01-2019 For Get Company Data End
  $fSubTotal = getOrderDetailTotalPrice($iOrderId);
  $fOffersDiscount = CalculateOrderDiscountPrice($iOrderId);
  $fDeliveryCharge = getOrderDeliveryCharge($iOrderId, $fSubTotal);
  //Added By HJ On 20-02-2019 For Apply Subscription Plan If Eligible User Start // Subscription Plan Discount By HJ
  $iPurchaseId = $fPlanDiscount = 0;
  $exDate = date("Y-m-d H:i:s");
  if ($fDeliveryCharge > 0 && $eOrderplaced_by == "User") {
  $charge_Array = array();
  $charge_Array['userid'] = $iUserId;
  $charge_Array['memberShipType'] = "Order";
  $planStatus = checkUserPrimeMemberShip($charge_Array);
  if (isset($planStatus['planId']) && $planStatus['planId'] > 0) {
  //$iPlanId = $planStatus['planId'];
  $iPurchaseId = $planStatus['iPurchaseId'];
  $fPlanDiscount = $fDeliveryCharge;
  $expireCond = "";
  if (isset($planStatus['eIsExpire']) && $planStatus['eIsExpire'] == "Yes") {
  $expireCond = ",eIsExpire = '" . $planStatus['eIsExpire'] . "',eSubscriptionStatus='No',dExpiredDate='" . $exDate . "'";
  sendSubscriptionExpiredMail($iUserId,$iPurchaseId);
  }
  $obj->sql_query("UPDATE plan_purchase_master set iAppliedCount = iAppliedCount+1 $expireCond WHERE iPurchaseId = " . $iPurchaseId);
  } else if (isset($planStatus['eIsExpire']) && $planStatus['eIsExpire'] == "Yes") {
  $iPurchaseId = $planStatus['eIsExpireId'];
  sendSubscriptionExpiredMail($iUserId,$iPurchaseId);
  $obj->sql_query("UPDATE plan_purchase_master set eIsExpire = '" . $planStatus['eIsExpire'] . "',eSubscriptionStatus='No',dExpiredDate='" . $exDate . "' WHERE iPurchaseId = " . $iPurchaseId);
  }
  }
  //Added By HJ On 20-02-2019 For Apply Subscription Plan If Eligible User End // Subscription Plan Discount By HJ
  if ($fSubTotal == 0) {
  $fPackingCharge = $fDeliveryCharge = $fTax = 0;
  }
  $fTax = 0;
  if ($fCompanyTax > 0) {
  $fcotaxamount = $fSubTotal - $fOffersDiscount + $fPackingCharge;
  $fCompanyTax = round((($fcotaxamount * $fCompanyTax) / 100), 2);
  } else {
  $TaxArr = getMemberCountryTax($iUserId, "Passenger");
  $fTax = $TaxArr['fTax1'];
  if ($fTax > 0) {
  $ftaxamount = $fSubTotal - $fOffersDiscount + $fPackingCharge;
  $fTax = round((($ftaxamount * $fTax) / 100), 2);
  }
  }
  // $fTax = 0;
  // $fCommision = 0;
  $fNetTotal = $fSubTotal + $fPackingCharge + $fDeliveryCharge + $fTax + $fCompanyTax - $fPlanDiscount;
  $fTotalGenerateFare = $fNetTotal;
  /*
  old  when Commission charge
  $fOrderFare_For_Commission = $fSubTotal - $fOffersDiscount + $fPackingCharge + $fTax + $fCompanyTax;

  $fOrderFare_For_Commission = $fSubTotal - $fOffersDiscount + $fPackingCharge + $fTax + $fCompanyTax;

  /*
  //Added By PM On 20-02-2019 For Applied Store Wise Commission Per Order Start
  //$fOrderFare_For_Commission = $fSubTotal;
  //Added By PM On 20-02-2019 For Applied Store Wise Commission Per Order end



  $fCommision = $ADMIN_COMMISSION;
  //Added By HJ On 30-01-2019 For Applied Store Wise Commission Per Order If > 0 and ENABLE_STORE_COMMISSION==Yes Start
  if ($fComissionPerOrder > 0 && $ENABLE_STORE_COMMISSION == "Yes") {
  $fCommision = $fComissionPerOrder;
  }
  //Added By HJ On 30-01-2019 For Applied Store Wise Commission Per Order If > 0 and ENABLE_STORE_COMMISSION==Yes End
  $fCommision = round((($fOrderFare_For_Commission * $fCommision) / 100), 2);
  if ($fOffersDiscount > 0) {
  $fNetTotal = $fNetTotal - $fOffersDiscount;
  }

  /* Checking For Passenger Outstanding Amount
  $fOutStandingAmount = 0;
  $fOutStandingAmount = GetPassengerOutstandingAmount($iUserId);
  if ($fOutStandingAmount > 0) {
  $fNetTotal = $fNetTotal + $fOutStandingAmount;
  $fTotalGenerateFare = $fTotalGenerateFare + $fOutStandingAmount;
  }

  /* Checking For Passenger Outstanding Amount */
/* Check Coupon Code For Count Total Fare Start 
  $discountValue = 0;
  $discountValueType = "cash";
  if ($couponCode != '') {
  $discountValue = get_value('coupon', 'fDiscount', 'vCouponCode', $couponCode, '', 'true');
  $discountValueType = get_value('coupon', 'eType', 'vCouponCode', $couponCode, '', 'true');
  }

  if ($couponCode != '' && $discountValue != 0) {
  if ($discountValueType == "percentage") {
  $vDiscount = round($discountValue, 1) . ' ' . "%";
  $discountValue = round(($fNetTotal * $discountValue), 1) / 100;
  } else {
  $curr_sym = get_value('currency', 'vSymbol', 'eDefault', 'Yes', '', 'true');
  if ($discountValue > $fNetTotal) {
  $vDiscount = round($fNetTotal, 1) . ' ' . $curr_sym;
  } else {
  $vDiscount = round($discountValue, 1) . ' ' . $curr_sym;
  }
  }
  $fNetTotal = $fNetTotal - $discountValue;
  if ($fNetTotal < 0) {
  $fNetTotal = 0;
  }
  $Order_data[0]['fDiscount'] = $discountValue;
  $Order_data[0]['vDiscount'] = $vDiscount;
  }
  /* Check Coupon Code Total Fare  End */
/* Check debit wallet For Count Total Fare  Start 

  $CheckUserWallet = $data_order[0]['eCheckUserWallet'];
  if ($ePaymentOption == "Cash" && $CheckUserWallet == "Yes") {
  $user_available_balance = $generalobj->get_user_available_balance($iUserId, "Rider");
  $user_wallet_debit_amount = 0;
  if ($fNetTotal > $user_available_balance) {
  $fNetTotal = $fNetTotal - $user_available_balance;
  $user_wallet_debit_amount = $user_available_balance;
  } else {
  $user_wallet_debit_amount = ($fNetTotal > 0) ? $fNetTotal : 0;
  $fNetTotal = 0;
  }
  // Update User Wallet
  if ($user_wallet_debit_amount > 0) {
  $vRideNo = $data_order[0]['vOrderNo'];
  $data_wallet['iUserId'] = $iUserId;
  $data_wallet['eUserType'] = "Rider";
  $data_wallet['iBalance'] = $user_wallet_debit_amount;
  $data_wallet['eType'] = "Debit";
  $data_wallet['dDate'] = date("Y-m-d H:i:s");
  $data_wallet['iTripId'] = $iTripId;
  $data_wallet['iOrderId'] = $iOrderId;
  $data_wallet['eFor'] = "Booking";
  $data_wallet['ePaymentStatus'] = "Unsettelled";
  $data_wallet['tDescription'] = "#LBL_DEBITED_BOOKING_DL#" . $vRideNo;
  $generalobj->InsertIntoUserWallet($data_wallet['iUserId'], $data_wallet['eUserType'], $data_wallet['iBalance'], $data_wallet['eType'], $data_wallet['iTripId'], $data_wallet['eFor'], $data_wallet['tDescription'], $data_wallet['ePaymentStatus'], $data_wallet['dDate'], $data_wallet['iOrderId']);

  // $obj->MySQLQueryPerform("user_wallet",$data_wallet,'insert');
  }
  }
  if ($eOrderplaced_by == "Admin") {
  /*  if ($ePaymentOption == "Cash")		{
  $finalFareData['fChangeTotal'] = ($fChangeAmount > 0) ? $currencySymbol . " " . formatnum($fChangeAmount) : $currencySymbol . " 0";
  $finalFareData['fChangeAmount'] = $fChangeAmount;
  $payChangeAmount = $fChangeAmount - $fNetTotal;
  $payChangeAmount = round($payChangeAmount, 2);
  $finalFareData['payChangeTotal'] = ($payChangeAmount > 0) ? $currencySymbol . " " . formatnum($payChangeAmount) : $currencySymbol . " 0";
  $finalFareData['payChangeAmount'] = $payChangeAmount;
  /* }	else{
  $finalFareData['payChangeTotal']="0";
  $finalFareData['payChangeAmount'] ="0";
  }
  } else {
  if ($ePaymentOption == "Cash") {
  /* 	if ($fChangeAmount>=$fNetTotal  || $fChangeAmount==0)	{
  $finalFareData['fChangeTotal'] = ($fChangeAmount > 0) ? $currencySymbol . " " . formatnum($fChangeAmount) : $currencySymbol . " 0";
  $finalFareData['fChangeAmount'] = $fChangeAmount;
  $payChangeAmount = $fChangeAmount - $fNetTotal;
  $payChangeAmount = round($payChangeAmount, 2);
  $finalFareData['payChangeTotal'] = ($payChangeAmount > 0) ? $currencySymbol . " " . formatnum($payChangeAmount) : $currencySymbol . " 0";
  $finalFareData['payChangeAmount'] = $payChangeAmount;
  /* 				}	else
  {
  $finalFareData['Action'] = "0";
  $finalFareData['message'] = "LBL_BILL_VALUE_ERROR_TXT";
  return ($finalFareData);
  }

  } else {
  $finalFareData['payChangeTotal'] = "0";
  $finalFareData['payChangeAmount'] = "0";
  }
  }
  /* Check debit wallet For Count Total Fare  End
  if ($fNetTotal < 0) {
  $fNetTotal = $fTotalGenerateFare = 0;
  }
  $finalFareData['fSubTotal'] = $fSubTotal;
  $finalFareData['fOffersDiscount'] = $fOffersDiscount;
  $finalFareData['fPackingCharge'] = $fPackingCharge;
  $finalFareData['fDeliveryCharge'] = $fDeliveryCharge;
  $finalFareData['fTax'] = $fTax;
  $finalFareData['fCompanyTax'] = $fCompanyTax;
  $finalFareData['fDiscount'] = $Order_data[0]['fDiscount'];
  $finalFareData['vDiscount'] = $Order_data[0]['vDiscount'];
  $finalFareData['fCommision'] = $fCommision;
  $finalFareData['fNetTotal'] = $fNetTotal;
  $finalFareData['fTotalGenerateFare'] = $fTotalGenerateFare;
  $finalFareData['fOutStandingAmount'] = $fOutStandingAmount;
  $finalFareData['fWalletDebit'] = $user_wallet_debit_amount;
  $finalFareData['iPurchaseId'] = $iPurchaseId; // Subscription Plan Discount By HJ
  $finalFareData['fPlanDiscount'] = $fPlanDiscount; // Subscription Plan Discount By HJ
  return $finalFareData;
  }

  // // new added
  function getPriceUserCurrency($iMemberId, $eUserType = "Passenger", $fPrice, $iOrderId = 0) {
  global $obj, $generalobj, $tconfig;
  $returnArr = array();
  if ($eUserType == "Passenger") {
  $UserDetailsArr = getUserCurrencyLanguageDetails($iMemberId, $iOrderId);
  } else if ($eUserType == "Driver") {
  $UserDetailsArr = getDriverCurrencyLanguageDetails($iMemberId, $iOrderId);
  } else {
  $UserDetailsArr = getCompanyCurrencyLanguageDetails($iMemberId, $iOrderId);
  }
  $currencySymbol = $UserDetailsArr['currencySymbol'];
  $Ratio = $UserDetailsArr['Ratio'];
  $fPrice = round(($fPrice * $Ratio), 2);
  $fPricewithsymbol = $currencySymbol . " " . $fPrice;
  $returnArr['fPrice'] = $fPrice;
  $returnArr['fPricewithsymbol'] = $fPricewithsymbol;
  $returnArr['currencySymbol'] = $currencySymbol;
  return $returnArr;
  }

  function DisplayOrderDetailItemList($iOrderDetailId, $iMemberId, $eUserType = "Passenger", $iOrderId = 0) {
  global $obj, $generalobj, $tconfig;
  $returnArr = array();
  $ssql = "";
  if ($eUserType == "Passenger") {
  $UserDetailsArr = getUserCurrencyLanguageDetails($iMemberId, $iOrderId);
  } else if ($eUserType == "Driver") {
  $UserDetailsArr = getDriverCurrencyLanguageDetails($iMemberId, $iOrderId);
  } else {
  $UserDetailsArr = getCompanyCurrencyLanguageDetails($iMemberId, $iOrderId);
  }

  $currencySymbol = $UserDetailsArr['currencySymbol'];
  $Ratio = $UserDetailsArr['Ratio'];
  $vLang = $UserDetailsArr['vLang'];
  $sql = "select od.*,mi.vItemType_" . $vLang . " as MenuItem from `order_details` as od LEFT JOIN  `menu_items` as mi ON od.iMenuItemId=mi.iMenuItemId where od.iOrderDetailId='" . $iOrderDetailId . "'";
  $data_order_detail = $obj->MySQLSelect($sql);
  $MenuItem = $data_order_detail[0]['MenuItem'];
  $fPrice = $data_order_detail[0]['fOriginalPrice'];

  // $fPrice = $data_order_detail[0]['fOriginalPrice']+$data_order_detail[0]['vOptionPrice']+$data_order_detail[0]['vAddonPrice'];
  $eAvailable = $data_order_detail[0]['eAvailable'];
  $fPriceArr = getPriceUserCurrency($iMemberId, $eUserType, $fPrice, $iOrderId);
  $fPrice = $fPriceArr['fPricewithsymbol'];
  $vsymbol = $fPriceArr['currencySymbol'];
  $fPricewithoutsymbol = $fPriceArr['fPrice'];
  $fTotalprice = $fPricewithoutsymbol * $data_order_detail[0]['iQty'];
  $returnArr['iQty'] = $data_order_detail[0]['iQty'];
  $returnArr['MenuItem'] = $MenuItem;
  $returnArr['fPrice'] = $fPrice;
  $returnArr['fTotPrice'] = $vsymbol . " " . formatnum($fTotalprice);
  $returnArr['eAvailable'] = $eAvailable;
  $returnArr['iOrderDetailId'] = $iOrderDetailId;
  if ($iOrderId > 0) {
  $sqlo = "select fOfferType,fOfferAppyType from `orders` where iOrderId = '" . $iOrderId . "'";
  $db_orderdata = $obj->MySQLSelect($sqlo);
  $fOfferType = $db_orderdata[0]['fOfferType'];
  $fOfferAppyType = $db_orderdata[0]['fOfferAppyType'];
  $TotalDiscountPrice = "";
  if (($fOfferAppyType == "None" && ($fOfferType == "Flat" || $fOfferType == "")) || $fOfferType == "Percentage") {
  $fTotalDiscountPrice = $data_order_detail[0]['fTotalDiscountPrice'];
  $TotalPrice = $data_order_detail[0]['fTotalPrice'];
  if ($fTotalDiscountPrice > 0) {
  $Strikeprice = ($TotalPrice - $fTotalDiscountPrice) * $Ratio;
  $TotalDiscountPrice = $vsymbol . " " . formatnum($Strikeprice);
  }
  }

  $returnArr['TotalDiscountPrice'] = $TotalDiscountPrice;
  }

  $vOptionId = $data_order_detail[0]['vOptionId'];
  if ($vOptionId != "") {
  $vOptionName = get_value('menuitem_options', 'vOptionName', 'iOptionId', $vOptionId, '', 'true');
  $vOptionPrice = $data_order_detail[0]['vOptionPrice'];
  $vOptionPriceArr = getPriceUserCurrency($iMemberId, $eUserType, $vOptionPrice, $iOrderId);
  $vOptionPrice = $vOptionPriceArr['fPricewithsymbol'];
  $returnArr['vOptionName'] = $vOptionName;
  $returnArr['vOptionPrice'] = $vOptionPrice;
  } else {
  $returnArr['vOptionName'] = "";
  $returnArr['vOptionPrice'] = "";
  }

  $tAddOnIdOrigPrice = $data_order_detail[0]['tAddOnIdOrigPrice'];
  if ($tAddOnIdOrigPrice != "") {
  $AddonItemsArr = array();
  $AddonItemsDetailArr = explode(",", $tAddOnIdOrigPrice);
  for ($i = 0; $i < count($AddonItemsDetailArr); $i++) {
  $AddonItemsStrArr = explode("#", $AddonItemsDetailArr[$i]);
  $AddonItemsId = $AddonItemsStrArr[0];
  $AddonItemsPrice = $AddonItemsStrArr[1];
  $AddonItemsPriceArr = getPriceUserCurrency($iMemberId, $eUserType, $AddonItemsPrice, $iOrderId);
  $AddonItemPrice = $AddonItemsPriceArr['fPricewithsymbol'];
  $AddonItemName = get_value('menuitem_options', 'vOptionName', 'iOptionId', $AddonItemsId, '', 'true');
  $AddonItemsArr[$i]['vAddOnItemName'] = $AddonItemName;
  $AddonItemsArr[$i]['AddonItemPrice'] = $AddonItemPrice;
  }

  $returnArr['AddOnItemArr'] = $AddonItemsArr;
  } else {
  $returnArr['AddOnItemArr'] = array();
  }

  return $returnArr;
  } */

function DisplayOrderDetailItemList_ForReorder($iOrderDetailId, $iMemberId, $eUserType = "Passenger", $iCompanyId, $iServiceId = "") {
    global $obj, $generalobj, $tconfig;
    $returnArr = array();
    $ssql = "";
    if ($eUserType == "Passenger") {
        $UserDetailsArr = getUserCurrencyLanguageDetails($iMemberId);
    } else if ($eUserType == "Driver") {
        $UserDetailsArr = getDriverCurrencyLanguageDetails($iMemberId);
    } else {
        $UserDetailsArr = getCompanyCurrencyLanguageDetails($iMemberId);
    }

    $currencySymbol = $UserDetailsArr['currencySymbol'];
    $Ratio = $UserDetailsArr['Ratio'];
    $vLang = $UserDetailsArr['vLang'];
    $sql = "select od.*,mi.vItemType_" . $vLang . " as MenuItem,mi.vImage,mi.eFoodType from `order_details` as od LEFT JOIN  `menu_items` as mi ON od.iMenuItemId=mi.iMenuItemId where od.iOrderDetailId='" . $iOrderDetailId . "'";
    $data_order_detail = $obj->MySQLSelect($sql);
    $MenuItem = $data_order_detail[0]['MenuItem'];
    $iMenuItemId = $data_order_detail[0]['iMenuItemId'];

    // $fPrice = GetFoodMenuItemBasicPrice($data_order_detail[0]['iMenuItemId']);
    $fPrice = FoodMenuItemBasicPrice($data_order_detail[0]['iMenuItemId']);
    $eAvailable = $data_order_detail[0]['eAvailable'];
    $fPriceArr = getPriceUserCurrency($iMemberId, $eUserType, $fPrice);
    $fPrice = $fPriceArr['fPrice'];
    $vsymbol = $fPriceArr['currencySymbol'];
    $fPricewithoutsymbol = $fPriceArr['fPrice'];
    $fTotalprice = $fPricewithoutsymbol * $data_order_detail[0]['iQty'];
    $returnArr['iQty'] = $data_order_detail[0]['iQty'];
    $returnArr['MenuItem'] = $MenuItem;
    $returnArr['iMenuItemId'] = $data_order_detail[0]['iMenuItemId'];
    $returnArr['eFoodType'] = $data_order_detail[0]['eFoodType'];
    $returnArr['iFoodMenuId'] = $data_order_detail[0]['iFoodMenuId'];
    $returnArr['fPrice'] = $fPrice;
    $returnArr['fTotPrice'] = $vsymbol . " " . $fTotalprice;
    $returnArr['eAvailable'] = $eAvailable;
    $returnArr['iOrderDetailId'] = $iOrderDetailId;
    $returnArr['vImage'] = "";
    if ($data_order_detail[0]['vImage'] != "") {
        $returnArr['vImage'] = $tconfig["tsite_upload_images_menu_item"] . "/" . $data_order_detail[0]['vImage'];
    }

    $vOptionId = $data_order_detail[0]['vOptionId'];
    if ($vOptionId != "") {
        $vOptionName = get_value('menuitem_options', 'vOptionName', 'iOptionId', $vOptionId, '', 'true');
        $vOptionPrice = GetFoodMenuItemOptionPrice($vOptionId);
        $vOptionPriceArr = getPriceUserCurrency($iMemberId, $eUserType, $vOptionPrice);
        $vOptionPrice = $vOptionPriceArr['fPrice'];
        $returnArr['vOptionId'] = $vOptionId;
        $returnArr['vOptionName'] = $vOptionName;
        $returnArr['vOptionPrice'] = $vOptionPrice;
    } else {
        $returnArr['vOptionId'] = "";
        $returnArr['vOptionName'] = "";
        $returnArr['vOptionPrice'] = "";
    }

    $tAddOnIdOrigPrice = $data_order_detail[0]['tAddOnIdOrigPrice'];
    if ($tAddOnIdOrigPrice != "") {
        $AddonItemsArr = array();
        $AddonItemsDetailArr = explode(",", $tAddOnIdOrigPrice);
        $AddonItemPrice_Total = 0;
        for ($i = 0; $i < count($AddonItemsDetailArr); $i++) {
            $AddonItemsStrArr = explode("#", $AddonItemsDetailArr[$i]);
            $AddonItemsId = $AddonItemsStrArr[0];
            $AddonItemsPrice = GetFoodMenuItemAddOnPrice($AddonItemsId);
            $AddonItemPrice_Total = $AddonItemPrice_Total + $AddonItemsPrice;
            $AddonItemsPriceArr_Total = getPriceUserCurrency($iMemberId, $eUserType, $AddonItemPrice_Total);
            $AddonItemPrice_Total = $AddonItemsPriceArr_Total['fPrice'];
            $AddonItemName = get_value('menuitem_options', 'vOptionName', 'iOptionId', $AddonItemsId, '', 'true');
            $AddonItemsArr[$i]['vAddonId'] = $AddonItemsId;
            $AddonItemsArr[$i]['vAddOnItemName'] = $AddonItemName;
            $AddonItemsArr[$i]['AddonItemPrice'] = $AddonItemPrice_Total;
        }

        $returnArr['AddOnItemArr'] = $AddonItemsArr;
    } else {
        $returnArr['AddOnItemArr'] = array();
    }

    ## Return Selected  ##
    /* $returnArr['options'] = array();
      $returnArr['addon'] = array();
      $sql = "SELECT mo.*,fm.iFoodMenuId FROM menuitem_options as mo LEFT JOIN menu_items as mi ON mo.iMenuItemId=mi.iMenuItemId LEFT JOIN food_menu as fm ON mi.iFoodMenuId=fm.iFoodMenuId LEFT JOIN company as co ON fm.iCompanyId=co.iCompanyId WHERE co.iCompanyId = '".$iCompanyId."' AND fm.eStatus = 'Active' AND mi.eAvailable = 'Yes' AND mi.iMenuItemId = '".$iMenuItemId."'";
      $db_options_data = $obj->MySQLSelect($sql);
      if(count($db_options_data) > 0){
      for($i=0;$i<count($db_options_data);$i++){
      $fPrice = $db_options_data[$i]['fPrice'];
      $fUserPrice = number_format($fPrice*$Ratio,2);
      $fUserPriceWithSymbol = $currencySymbol." ".$fUserPrice;
      $db_options_data[$i]['fUserPrice'] = $fUserPrice;
      $db_options_data[$i]['fUserPriceWithSymbol'] = $fUserPriceWithSymbol;
      if($db_options_data[$i]['eOptionType'] == "Options"){
      $returnArr['options'][] = $db_options_data[$i];
      }

      if($db_options_data[$i]['eOptionType'] == "Addon"){
      $returnArr['addon'][] = $db_options_data[$i];
      }
      }
      } */

    ## Get Menu Items Array ##
    $returnArr['menu_items'] = array();
    $sqlf = "SELECT iMenuItemId,iFoodMenuId,vItemType_" . $vLang . " as vItemType,vItemDesc_" . $vLang . " as vItemDesc,fPrice,eFoodType,fOfferAmt,vImage,iDisplayOrder FROM menu_items WHERE iMenuItemId = '" . $iMenuItemId . "'";
    $db_item_data = $obj->MySQLSelect($sqlf);
    if (count($db_item_data) > 0) {
        $MenuItemPriceArr = getMenuItemPriceByCompanyOffer($iMenuItemId, $iCompanyId, 1, $iMemberId, "Display", "", "", $iServiceId);
        $fPrice = round($MenuItemPriceArr['fPrice'] * $Ratio, 2);
        $fOfferAmt = round($MenuItemPriceArr['fOfferAmt'], 2);
        $db_item_data[0]['fOfferAmt'] = $fOfferAmt;
        if ($fOfferAmt > 0) {
            $fDiscountPrice = round($MenuItemPriceArr['fPrice'] * $Ratio, 2);
            $StrikeoutPrice = round($MenuItemPriceArr['fOriginalPrice'] * $Ratio, 2);
            $db_item_data[0]['StrikeoutPrice'] = $currencySymbol . " " . formatNum($StrikeoutPrice);
            $db_item_data[0]['fDiscountPrice'] = formatNum($fDiscountPrice);
            $db_item_data[0]['fDiscountPricewithsymbol'] = $currencySymbol . " " . formatNum($fDiscountPrice);
            $db_item_data[0]['currencySymbol'] = $currencySymbol;
        } else {
            $db_item_data[0]['StrikeoutPrice'] = $currencySymbol . " " . formatNum($fPrice);
            $db_item_data[0]['fDiscountPrice'] = formatNum($fPrice);
            $db_item_data[0]['fDiscountPricewithsymbol'] = $currencySymbol . " " . formatNum($fPrice);
            $db_item_data[0]['currencySymbol'] = $currencySymbol;
        }

        if ($db_item_data[0]['vImage'] != "") {
            $db_item_data[0]['vImage'] = $tconfig["tsite_upload_images_menu_item"] . "/" . $db_item_data[0]['vImage'];
        }
        $iMenuItemId = $db_item_data[0]['iMenuItemId'];
        //$MenuItemOptionToppingArr = GetMenuItemOptionsTopping($iMenuItemId, $currencySymbol, $Ratio, $vLang, $iServiceId);
        $MenuItemOptionToppingArray = array();
        if (isset($MenuItemOptionToppingArr[$iMenuItemId])) {
            $MenuItemOptionToppingArray = $MenuItemOptionToppingArr[$iMenuItemId];
        }
        $db_item_data[0]['MenuItemOptionToppingArr'] = $MenuItemOptionToppingArr;

        // echo "<pre>";print_r($MenuItemOptionToppingArr);exit;
        $returnArr['menu_items'] = $db_item_data[0];
    }

    ## Get Menu Items Array ##
    return $returnArr;
}

function GetUserGeoLocationId($Address_Array) {
    global $generalobj, $obj;
    $iLocationId = "0";
    if (!empty($Address_Array)) {
        $sqlaa = "SELECT * FROM location_master WHERE eStatus='Active' AND eFor = 'UserDeliveryCharge'";
        $allowed_data = $obj->MySQLSelect($sqlaa);
        if (!empty($allowed_data)) {
            $polygon = array();
            foreach ($allowed_data as $key => $val) {
                $latitude = explode(",", $val['tLatitude']);
                $longitude = explode(",", $val['tLongitude']);
                for ($x = 0; $x < count($latitude); $x++) {
                    if (!empty($latitude[$x]) || !empty($longitude[$x])) {
                        $polygon[$key][] = array(
                            $latitude[$x],
                            $longitude[$x]
                        );
                    }
                }

                // print_r($polygon[$key]);
                if ($polygon[$key]) {
                    $address = contains($Address_Array, $polygon[$key]) ? 'IN' : 'OUT';
                    if ($address == 'IN') {
                        $iLocationId = $val['iLocationId'];
                        break;
                    }
                }
            }
        }
    }

    return $iLocationId;
}

function getOrderFare($iOrderId, $eUserType = "Passenger", $IS_FROM_HISTORY = "No") {
    global $generalobj, $obj;
    $OrderFareDetailsArr = array();
    $sql = "select * from orders where iOrderId='" . $iOrderId . "'";
    $data_order = $obj->MySQLSelect($sql);
    if ($eUserType == "Passenger") {
        $UserDetailsArr = getUserCurrencyLanguageDetails($data_order[0]['iUserId'], $iOrderId);
    } else if ($eUserType == "Driver") {
        $UserDetailsArr = getDriverCurrencyLanguageDetails($data_order[0]['iDriverId'], $iOrderId);
    } else {
        $UserDetailsArr = getCompanyCurrencyLanguageDetails($data_order[0]['iCompanyId'], $iOrderId);
    }

    $vSymbol = $UserDetailsArr['currencySymbol'];
    $priceRatio = $UserDetailsArr['Ratio'];
    $vLang = $UserDetailsArr['vLang'];
    $iServiceId = $data_order[0]['iServiceId'];
    $languageLabelsArr = getLanguageLabelsArr($vLang, "1", $iServiceId);
    $returnArr['subtotal'] = $data_order[0]['fSubTotal'] * $priceRatio;
    $returnArr['fOffersDiscount'] = $data_order[0]['fOffersDiscount'] * $priceRatio;
    $returnArr['fPackingCharge'] = $data_order[0]['fPackingCharge'] * $priceRatio;
    $returnArr['fDeliveryCharge'] = $data_order[0]['fDeliveryCharge'] * $priceRatio;
    $returnArr['fTax'] = $data_order[0]['fTax'] * $priceRatio;
    $returnArr['fTotalGenerateFare'] = $data_order[0]['fTotalGenerateFare'] * $priceRatio;
    $returnArr['fDiscount'] = $data_order[0]['fDiscount'] * $priceRatio;
    $returnArr['fCommision'] = $data_order[0]['fCommision'] * $priceRatio;
    $returnArr['fNetTotal'] = $data_order[0]['fNetTotal'] * $priceRatio;
    $returnArr['fWalletDebit'] = $data_order[0]['fWalletDebit'] * $priceRatio;
    $returnArr['fOutStandingAmount'] = $data_order[0]['fOutStandingAmount'] * $priceRatio;
    $returnArr['fDriverPaidAmount'] = $data_order[0]['fDriverPaidAmount'] * $priceRatio;
    $subtotal = formatNum($returnArr['subtotal']);
    $fOffersDiscount = formatNum($returnArr['fOffersDiscount']);
    $fPackingCharge = formatNum($returnArr['fPackingCharge']);
    $fDeliveryCharge = formatNum($returnArr['fDeliveryCharge']);
    $fTax = formatNum($returnArr['fTax']);
    $fTotalGenerateFare = formatNum($returnArr['fTotalGenerateFare']);
    $fDiscount = formatNum($returnArr['fDiscount']);
    $fCommision = formatNum($returnArr['fCommision']);
    $fWalletDebit = formatNum($returnArr['fWalletDebit']);
    $fOutStandingAmount = formatNum($returnArr['fOutStandingAmount']);
    $fNetTotal = formatNum($returnArr['fNetTotal']);
    $EarningAmount = $returnArr['fTotalGenerateFare'] - $returnArr['fOffersDiscount'] - $returnArr['fDeliveryCharge'] - $returnArr['fCommision'] - $returnArr['fOutStandingAmount'];
    $arrindex = 0;
    if ($eUserType == "Driver") {
        $tripsql = "SELECT fDeliveryCharge,eDriverPaymentStatus FROM trips WHERE iOrderId='" . $iOrderId . "'";
        $DataTrips = $obj->MySQLSelect($tripsql);
        if ($data_order[0]['iStatusCode'] == '7' || $data_order[0]['iStatusCode'] == '8') {
            if ($DataTrips[0]['eDriverPaymentStatus'] == 'Settelled') {
                $fDeliveryChargeDriver = $returnArr['fDriverPaidAmount'];
            } else {
                $fDeliveryChargeDriver = $DataTrips[0]['fDeliveryCharge'];
            }
        } else {
            $fDeliveryChargeDriver = $DataTrips[0]['fDeliveryCharge'];
        }

        $returnArr['fDeliveryChargeDriver'] = $fDeliveryChargeDriver * $priceRatio;
        $fDeliveryChargesDriver = formatNum($returnArr['fDeliveryChargeDriver']);
        $OrderFareDetailsArr[$arrindex][$languageLabelsArr['LBL_DELIVERY_EARNING_APP']] = $vSymbol . " " . $fDeliveryChargesDriver;
        $arrindex++;
    } else if ($eUserType == "Company") {
        if ($data_order[0]['fSubTotal'] > 0) {
            $OrderFareDetailsArr[$arrindex][$languageLabelsArr['LBL_BILL_SUB_TOTAL']] = $vSymbol . " " . $subtotal;
            $arrindex++;
        }

        if ($data_order[0]['fOffersDiscount'] > 0) {
            $OrderFareDetailsArr[$arrindex][$languageLabelsArr['LBL_OFFERS_DISCOUNT_TXT']] = "-" . $vSymbol . " " . $fOffersDiscount;
            $arrindex++;
        }

        if ($data_order[0]['fPackingCharge'] > 0) {
            $OrderFareDetailsArr[$arrindex][$languageLabelsArr['LBL_PACKING_CHARGE']] = $vSymbol . " " . $fPackingCharge;
            $arrindex++;
        }

        if ($data_order[0]['fTax'] > 0) {
            $OrderFareDetailsArr[$arrindex][$languageLabelsArr['LBL_TOTAL_TAX_TXT']] = $vSymbol . " " . $fTax;
            $arrindex++;
        }

        if ($IS_FROM_HISTORY == "No") {

            // if($data_order[0]['fTotalGenerateFare'] > 0){
            // $OrderFareDetailsArr[$arrindex][$languageLabelsArr['LBL_TOTAL_BILL_AMOUNT_TXT']." ".$payment_str] = $vSymbol." ".$fTotalGenerateFare;
            $TotalDisplayAmount = $returnArr['subtotal'] - $returnArr['fOffersDiscount'] + $returnArr['fPackingCharge'] + $returnArr['fTax'];
            $OrderFareDetailsArr[$arrindex][$languageLabelsArr['LBL_TOTAL_BILL_AMOUNT_TXT'] . " " . $payment_str] = $vSymbol . " " . formatnum($TotalDisplayAmount);
            $arrindex++;

            // }
        } else {
            if ($data_order[0]['fCommision'] > 0) {
                $OrderFareDetailsArr[$arrindex][$languageLabelsArr['LBL_Commision']] = "-" . $vSymbol . " " . $fCommision;
                $arrindex++;
            }

            if ($EarningAmount > 0) {
                $EarningAmount = formatNum($EarningAmount);
                if ($data_order[0]['iStatusCode'] == '7' || $data_order[0]['iStatusCode'] == '8') {
                    $OrderFareDetailsArr[$arrindex][$languageLabelsArr['LBL_EXPECTED_EARNING'] . " " . $payment_str] = $vSymbol . " " . $EarningAmount;
                } else {
                    $OrderFareDetailsArr[$arrindex][$languageLabelsArr['LBL_AMT_EARNED'] . " " . $payment_str] = $vSymbol . " " . $EarningAmount;
                }

                $arrindex++;
            }
        }

        /* if ($fNetTotal > 0) {
          $OrderFareDetailsArr[$arrindex]['SubTotal'] = $vSymbol.$fNetTotal;
          $arrindex++;
          } */
    } else {
        if ($data_order[0]['fSubTotal'] > 0) {
            $OrderFareDetailsArr[$arrindex][$languageLabelsArr['LBL_BILL_SUB_TOTAL']] = $vSymbol . " " . $subtotal;
            $arrindex++;
        }

        if ($data_order[0]['fOffersDiscount'] > 0) {
            $OrderFareDetailsArr[$arrindex][$languageLabelsArr['LBL_OFFERS_DISCOUNT_TXT']] = "-" . $vSymbol . " " . $fOffersDiscount;
            $arrindex++;
        }

        if ($data_order[0]['fPackingCharge'] > 0) {
            $OrderFareDetailsArr[$arrindex][$languageLabelsArr['LBL_PACKING_CHARGE']] = $vSymbol . " " . $fPackingCharge;
            $arrindex++;
        }

        if ($data_order[0]['fDeliveryCharge'] > 0) {
            $OrderFareDetailsArr[$arrindex][$languageLabelsArr['LBL_DELIVERY_CHARGES_TXT']] = $vSymbol . " " . $fDeliveryCharge;
            $arrindex++;
        }

        if ($data_order[0]['fOutStandingAmount'] > 0) {
            $OrderFareDetailsArr[$arrindex][$languageLabelsArr['LBL_OUTSTANDING_AMOUNT_TXT']] = $vSymbol . " " . $fOutStandingAmount;
            $arrindex++;
        }

        if ($data_order[0]['fTax'] > 0) {
            $OrderFareDetailsArr[$arrindex][$languageLabelsArr['LBL_TOTAL_TAX_TXT']] = $vSymbol . " " . $fTax;
            $arrindex++;
        }

        if ($data_order[0]['fDiscount'] > 0) {
            $OrderFareDetailsArr[$arrindex][$languageLabelsArr['LBL_DISCOUNT_TXT']] = "-" . $vSymbol . " " . $fDiscount;
            $arrindex++;
        }

        if ($data_order[0]['fWalletDebit'] > 0) {
            $OrderFareDetailsArr[$arrindex][$languageLabelsArr['LBL_WALLET_ADJUSTMENT']] = "-" . $vSymbol . " " . $fWalletDebit;
            $arrindex++;
        }

        if ($IS_FROM_HISTORY == "No") {

            // if($data_order[0]['fTotalGenerateFare'] > 0){
            // $OrderFareDetailsArr[$arrindex][$languageLabelsArr['LBL_TOTAL_BILL_AMOUNT_TXT']." ".$payment_str] = $vSymbol." ".$fTotalGenerateFare;
            $OrderFareDetailsArr[$arrindex][$languageLabelsArr['LBL_TOTAL_BILL_AMOUNT_TXT'] . " " . $payment_str] = $vSymbol . " " . $fNetTotal;
            $arrindex++;

            // }
        } else {
            if ($data_order[0]['fCommision'] > 0) {
                $OrderFareDetailsArr[$arrindex][$languageLabelsArr['LBL_Commision']] = "-" . $vSymbol . " " . $fCommision;
                $arrindex++;
            }

            if ($EarningAmount > 0) {
                $EarningAmount = formatNum($EarningAmount);
                $OrderFareDetailsArr[$arrindex][$languageLabelsArr['LBL_AMT_EARNED'] . " " . $payment_str] = $vSymbol . " " . $EarningAmount;
                $arrindex++;
            }
        }

        /* if ($fNetTotal > 0) {
          $OrderFareDetailsArr[$arrindex]['SubTotal'] = $vSymbol.$fNetTotal;
          $arrindex++;
          } */
    }

    return $OrderFareDetailsArr;
}

function DisplayOrderDetailList($iOrderId, $vTimeZone = 'Asia/Kolkata', $UserType = "Company", $IS_FROM_HISTORY = "No") {
    global $obj, $generalobj, $tconfig;
    $returnArr = array();
    $sql = "SELECT o.iOrderId,o.vOrderNo,o.fNetTotal,o.iCompanyId,o.iServiceId,o.iDriverId,o.iUserId,o.tOrderRequestDate,o.iStatusCode,o.ePaid,o.ePaymentOption,o.iUserAddressId,concat(ru.vName,' ',ru.vLastName) as UserName,ru.vPhone,ru.vPhoneCode FROM orders as o LEFT JOIN register_user as ru on ru.iUserId = o.iUserId WHERE o.iOrderId = '" . $iOrderId . "'";
    $db_order = $obj->MySQLSelect($sql);

    // echo "<pre>";print_r($db_order);exit;
    if ($UserType == "Driver") {
        $query = "SELECT vImage,eImgSkip,iVehicleTypeId FROM `trips` WHERE iOrderId = '" . $iOrderId . "'";
        $TripsData = $obj->MySQLSelect($query);
        $Vehiclefields = "iVehicleTypeId,vVehicleType";
        $VehicleTypeDataDriver = get_value('vehicle_type', $Vehiclefields, 'iVehicleTypeId', $TripsData[0]['iVehicleTypeId']);
    }
    //echo "<pre>";
    //print_r($iMemberId);die;
    foreach ($db_order as $key => $value) {
        $ssql1 = $whereCond = $vUserImage = $vDriverImage = '';
        $userId = $value['iUserId'];
        $driverId = $value['iDriverId'];
        if ($UserType == "Passenger") {
            $iMemberId = $value['iUserId'];
            $UserDetailsArr = getUserCurrencyLanguageDetails($iMemberId, $iOrderId);
        } else if ($UserType == "Driver") {
            $iMemberId = $value['iDriverId'];
            $ssql1 .= "AND eAvailable = 'Yes'";
            $UserDetailsArr = getDriverCurrencyLanguageDetails($iMemberId, $iOrderId);
        } else {
            $iMemberId = $value['iCompanyId'];
            $UserDetailsArr = getCompanyCurrencyLanguageDetails($iMemberId, $iOrderId);
        }
        //Added By HJ On 28-12-2018 For Get User and Driver Image Name Start
        if ($userId > 0) {
            $whereCond = "iUserId='" . $userId . "'";
            $tableName = "register_user";
            $fieldName = "vImgName AS vUserImage";
            $getUserImage = $obj->MySQLSelect("SELECT $fieldName FROM " . $tableName . " WHERE $whereCond");
            if (count($getUserImage) > 0) {
                $vUserImage = $getUserImage[0]['vUserImage'];
            }
        }
        if ($driverId > 0) {
            $whereCond = "iDriverId='" . $driverId . "'";
            $tableName = "register_driver";
            $fieldName = "vImage AS vDriverImage";
            $getDriverImage = $obj->MySQLSelect("SELECT $fieldName FROM " . $tableName . " WHERE $whereCond");
            if (count($getDriverImage) > 0) {
                $vDriverImage = $getDriverImage[0]['vUserImage'];
            }
        }
        //Added By HJ On 28-12-2018 For Get User and Driver Image Name End
        //echo "<pre>";
        $vcurSymbol = $UserDetailsArr['currencySymbol'];
        $curpriceRatio = $UserDetailsArr['Ratio'];
        $vLangu = $UserDetailsArr['vLang'];
        $iServiceId = $db_order[0]['iServiceId'];
        $languageLabelsArr = getLanguageLabelsArr($vLangu, "1", $iServiceId);
        $iDriverId = $value['iDriverId'];
        $returnArr[$key]['DriverName'] = "";
        if ($iDriverId > 0) {
            $DriverData = get_value('register_driver', 'vName,vLastName', 'iDriverId', $iDriverId);
            $DriverName = $DriverData[0]['vName'] . " " . $DriverData[0]['vLastName'];
            $returnArr[$key]['DriverName'] = $DriverName;
        }
        $returnArr[$key]['iOrderId'] = $iOrderId;
        $returnArr[$key]['iServiceId'] = $value['iServiceId'];
        $returnArr[$key]['iUserId'] = $value['iUserId'];
        $returnArr[$key]['iCompanyId'] = $value['iCompanyId'];
        $returnArr[$key]['vOrderNo'] = $value['vOrderNo'];
        $returnArr[$key]['iStatusCode'] = $value['iStatusCode'];
        $returnArr[$key]['vUserImage'] = $vUserImage;
        $returnArr[$key]['vDriverImage'] = $vDriverImage;
        //print_r($returnArr);
        //die;
        $StatusDisplay = getOrderStatus($iOrderId);
        if ($StatusDisplay == 'Refunded') {
            $StatusDisplay = 'Cancelled';
        }

        $servFields = 'iServiceId,vServiceName_' . $vLangu . ' as vServiceName';
        $ServiceCategoryData = get_value('service_categories', $servFields, 'iServiceId', $value['iServiceId']);
        if (!empty($ServiceCategoryData)) {
            if (!empty($ServiceCategoryData[0]['vServiceName'])) {
                $returnArr[$key]['vServiceCategoryName'] = '';
            } else {
                $returnArr[$key]['vServiceCategoryName'] = $ServiceCategoryData[0]['vServiceName'];
            }
        } else {
            $returnArr[$key]['vServiceCategoryName'] = '';
        }

        $returnArr[$key]['vStatus'] = $StatusDisplay;
        $returnArr[$key]['UserName'] = $value['UserName'];
        $returnArr[$key]['UserPhone'] = '+' . $value['vPhoneCode'] . $value['vPhone'];
        $returnArr[$key]['ePaid'] = $value['ePaid'];
        $returnArr[$key]['ePaymentOption'] = $value['ePaymentOption'];
        $returnArr[$key]['eConfirm'] = checkOrderStatus($iOrderId, "2");
        $returnArr[$key]['eDecline'] = checkOrderStatus($iOrderId, "9");
        $restFields = 'vCompany,vRestuarantLocation,vRestuarantLocation,vPhone,vImage,vCode,vRestuarantLocationLat,vRestuarantLocationLong';
        $CompanyData = get_value('company', $restFields, 'iCompanyId', $value['iCompanyId']);
        $returnArr[$key]['vCompany'] = $CompanyData[0]['vCompany'];
        if ($UserType == 'Driver') {
            $returnArr[$key]['RestuarantPhone'] = '+' . $CompanyData[0]['vCode'] . $CompanyData[0]['vPhone'];
        }
        $returnArr[$key]['vRestuarantLocation'] = $CompanyData[0]['vRestuarantLocation'];
        $returnArr[$key]['vRestuarantImage'] = $CompanyData[0]['vImage'];
        if ($UserType == 'Driver') {
            $returnArr[$key]['RestuarantLat'] = $CompanyData[0]['vRestuarantLocationLat'];
            $returnArr[$key]['RestuarantLong'] = $CompanyData[0]['vRestuarantLocationLong'];
        }

        $UserAddressArr = GetUserAddressDetail($value['iUserId'], "Passenger", $value['iUserAddressId']);
        $returnArr[$key]['DeliveryAddress'] = $UserAddressArr['UserAddress'];
        if ($UserType == 'Driver') {
            $returnArr[$key]['UserAddress'] = $UserAddressArr['UserAddress'];
            $userFields = 'vLatitude,vLongitude';
            $userData = get_value('user_address', $userFields, 'iUserAddressId', $value['iUserAddressId']);
            $returnArr[$key]['UserLatitude'] = $userData[0]['vLatitude'];
            $returnArr[$key]['UserLongitude'] = $userData[0]['vLongitude'];
            $isPhotoUploaded = 'No';
            if (!empty($TripsData)) {
                if ($returnArr[$key]['iStatusCode'] == '5' && $TripsData[0]['eImgSkip'] == 'None') {
                    $isPhotoUploaded = 'No';
                } else if ($returnArr[$key]['iStatusCode'] == '5' && $TripsData[0]['eImgSkip'] == 'No') {
                    $isPhotoUploaded = 'Yes';
                } else if ($returnArr[$key]['iStatusCode'] == '5' && $TripsData[0]['eImgSkip'] == 'Yes') {
                    $isPhotoUploaded = 'Yes';
                } else {
                    $isPhotoUploaded = 'No';
                }

                if ($returnArr[$key]['iStatusCode'] == '5') {
                    $returnArr[$key]['PickedFromRes'] = 'Yes';
                } else {
                    $returnArr[$key]['PickedFromRes'] = 'No';
                }

                $SelectdVehicleTypeId = ($VehicleTypeDataDriver[0]['iVehicleTypeId'] != '') ? $VehicleTypeDataDriver[0]['iVehicleTypeId'] : "";
                $SelectdVehicleType = ($VehicleTypeDataDriver[0]['vVehicleType'] != '') ? $VehicleTypeDataDriver[0]['vVehicleType'] : "";
                $returnArr[$key]['iVehicleTypeId'] = $SelectdVehicleTypeId;
                $returnArr[$key]['vVehicleType'] = $SelectdVehicleType;
            }

            $returnArr[$key]['isPhotoUploaded'] = $isPhotoUploaded;
            $eUnit = getMemberCountryUnit($value['iDriverId'], "Driver");
            if ($eUnit == 'KMs') {
                $fDistance = distanceByLocation($userData[0]['vLatitude'], $userData[0]['vLongitude'], $CompanyData[0]['vRestuarantLocationLat'], $CompanyData[0]['vRestuarantLocationLong'], "K");
            } else {
                $fDistance = distanceByLocation($userData[0]['vLatitude'], $userData[0]['vLongitude'], $CompanyData[0]['vRestuarantLocationLat'], $CompanyData[0]['vRestuarantLocationLong'], "");
            }

            $returnArr[$key]['UserDistance'] = round($fDistance, 2) . " " . $eUnit;
        }

        $serverTimeZone = date_default_timezone_get();
        $date = converToTz($value['tOrderRequestDate'], $vTimeZone, $serverTimeZone, "Y-m-d H:i:s");
        $OrderTime = date('d M, Y h:i A', strtotime($date));
        $returnArr[$key]['tOrderRequestDate_Org'] = $date;
        $returnArr[$key]['tOrderRequestDate'] = $OrderTime;
        if ($value['iDriverId'] == '0') {
            $returnArr[$key]['DriverAssign'] = 'No';
        } else {
            $returnArr[$key]['DriverAssign'] = 'Yes';
        }

        $query = "SELECT iOrderDetailId FROM order_details WHERE iOrderId = '" . $iOrderId . "' $ssql1";
        $orderDetailId = $obj->MySQLSelect($query);
        $returnArr[$key]['TotalItems'] = strval(count($orderDetailId));
        if ($UserType == 'Driver') {
            $ePaid = $value['ePaid'];
            $ePaymentOption = $value['ePaymentOption'];
            $returnArr[$key]['vSymbol'] = $vcurSymbol;
            if ($ePaid == 'Yes' && $ePaymentOption == 'Card') {
                $returnArr[$key]['originalTotal'] = formatNum($value['fNetTotal'] * $curpriceRatio);
                $CardNetTotal = 0;
                $returnArr[$key]['SubTotal'] = $vcurSymbol . formatNum($CardNetTotal); // $languageLabelsArr['LBL_SUBTOTAL_APP_TXT']
            } else {
                $returnArr[$key]['SubTotal'] = $vcurSymbol . formatNum($value['fNetTotal'] * $curpriceRatio);
                //$languageLabelsArr['LBL_SUBTOTAL_APP_TXT']
            }
        }

        foreach ($orderDetailId as $k => $val) {
            $ItemLists[] = DisplayOrderDetailItemList($val['iOrderDetailId'], $iMemberId, $UserType, $iOrderId);
        }

        // echo "<pre>";print_r($ItemLists);exit;
        $all_data_new = array();
        if ($ItemLists != '') {
            foreach ($ItemLists as $k => $item) {
                $iQty = ($item['iQty'] != '') ? $item['iQty'] : '';
                $MenuItem = ($item['MenuItem'] != '') ? $item['MenuItem'] : '';
                $fTotPrice = ($item['fTotPrice'] != '') ? $item['fTotPrice'] : '';
                $TotalDiscountPrice = ($item['TotalDiscountPrice'] != '') ? $item['TotalDiscountPrice'] : '';
                $eAvailable = ($item['eAvailable'] != '') ? $item['eAvailable'] : '';
                $AddOnItemArr = ($item['AddOnItemArr'] != '') ? $item['AddOnItemArr'] : '';
                $iOrderDetailId = ($item['iOrderDetailId'] != '') ? $item['iOrderDetailId'] : '';
                $all_data_new[$k]['iOrderDetailId'] = $iOrderDetailId;
                $all_data_new[$k]['iQty'] = $iQty;
                $all_data_new[$k]['MenuItem'] = $MenuItem;
                $all_data_new[$k]['fTotPrice'] = $fTotPrice;
                $all_data_new[$k]['TotalDiscountPrice'] = $TotalDiscountPrice;
                $all_data_new[$k]['eAvailable'] = $eAvailable;
                $vOptionName = ($item['vOptionName'] != '') ? $item['vOptionName'] : '';
                $addonTitleArr = array();
                if (!empty($AddOnItemArr)) {
                    foreach ($AddOnItemArr as $addonkey => $addonvalue) {
                        $addonTitleArr[] = $addonvalue['vAddOnItemName'];
                    }

                    $addonTitle = implode(",", $addonTitleArr);
                } else {
                    $addonTitle = '';
                }

                if ($vOptionName != '' && $addonTitle == '') {
                    $all_data_new[$k]['SubTitle'] = $vOptionName;
                } else if ($vOptionName == '' && $addonTitle != '') {
                    $all_data_new[$k]['SubTitle'] = $addonTitle;
                } else if ($vOptionName != '' && $addonTitle != '') {
                    $all_data_new[$k]['SubTitle'] = $vOptionName . "," . $addonTitle;
                } else {
                    $all_data_new[$k]['SubTitle'] = '';
                }
                //Added By HJ On 05-02-2020 For Get Options and Topping Data Start
                $all_data_new[$k]['MenuItemToppings'] = $addonTitle;
                $all_data_new[$k]['MenuItemOptions'] = $vOptionName;
                //Added By HJ On 05-02-2020 For Get Options and Topping Data End
            }
        }

        $returnArr[$key]['itemlist'] = $all_data_new;
    }

    $orderData = getOrderFare($iOrderId, $UserType, $IS_FROM_HISTORY);
    $returnArr[$key]['FareDetailsArr'] = $orderData;
    return $returnArr;
}

function getOrderStatus($iOrderId) {
    global $generalobj, $obj;
    $sql = "SELECT os.vStatus_Track FROM order_status as os LEFT JOIN orders as ord ON os.iStatusCode = ord.iStatusCode WHERE ord.iOrderId = '" . $iOrderId . "'";
    $OrderStatus = $obj->MySQLSelect($sql);
    $vStatus = $OrderStatus[0]['vStatus_Track'];
    return $vStatus;
}

function createOrderLog($iOrderId, $iStatusCode) {
    global $generalobj, $obj;
    $sql = "SELECT * FROM order_status_logs WHERE iOrderId = '" . $iOrderId . "' AND iStatusCode = '" . $iStatusCode . "'";
    $OrderStatuslog = $obj->MySQLSelect($sql);
    if (count($OrderStatuslog) == 0) {
        $data['iOrderId'] = $iOrderId;
        $data['iStatusCode'] = $iStatusCode;
        $data['dDate'] = @date("Y-m-d H:i:s");
        $data['vIP'] = $generalobj->get_client_ip();
        $id = $obj->MySQLQueryPerform("order_status_logs", $data, 'insert');
    } else {
        $id = $OrderStatuslog[0]['iOrderLogId'];
    }

    return $id;
}

function UpdateCardPaymentPendingOrder() {
    global $generalobj, $obj;
    $currentdate = @date("Y-m-d H:i:s");
    $checkdate = date('Y-m-d H:i:s', strtotime("-120 minutes", strtotime($currentdate)));
    $sql = "SELECT iOrderId FROM orders WHERE dDeliveryDate < '" . $checkdate . "' AND iStatusCode = 12 AND ePaymentOption = 'Card'";
    $db_order = $obj->MySQLSelect($sql);
    if (count($db_order) > 0) {
        for ($i = 0; $i < count($db_order); $i++) {
            $iOrderId = $db_order[$i]['iOrderId'];
            $sql = "delete from order_details where iOrderId='" . $iOrderId . "'";
            $obj->sql_query($sql);
            $sqld = "delete from orders where iOrderId='" . $iOrderId . "'";
            $obj->sql_query($sqld);
        }
    }

    return true;
}

function checkOrderStatus($iOrderId, $iStatusCode) {
    global $generalobj, $obj;
    $orderexist = "No";
    $sql = "SELECT count(iOrderLogId) as TotOrderLogId from order_status_logs WHERE iOrderId ='" . $iOrderId . "' AND iStatusCode IN($iStatusCode)";
    $db_status = $obj->MySQLSelect($sql);
    $TotOrderLogId = $db_status[0]['TotOrderLogId'];
    if ($TotOrderLogId > 0) {
        $orderexist = "Yes";
    }

    return $orderexist;
}

function checkOrderRequestStatus($iOrderId) {
    global $generalobj, $obj, $PROVIDER_BOOKING_ACCEPT_TIME_INTERVAL;
    $sql = "SELECT * from driver_request WHERE iOrderId ='" . $iOrderId . "'";
    $db_driver_request = $obj->MySQLSelect($sql);
    if (count($db_driver_request) > 0) {
        $sql = "SELECT iDriverId from orders WHERE iOrderId ='" . $iOrderId . "'";
        $db_order_driver = $obj->MySQLSelect($sql);
        $iDriverId = $db_order_driver[0]['iDriverId'];
        if ($iDriverId > 0) {
            $returnArr['Action'] = "1";
            $returnArr["message"] = "LBL_REQUEST_FAILED_TXT";
            $returnArr["message1"] = "DRIVER_ASSIGN";
        } else {
            $PROVIDER_BOOKING_ACCEPT_TIME_INTERVAL = $PROVIDER_BOOKING_ACCEPT_TIME_INTERVAL + 5;
            $currentdate = @date("Y-m-d H:i:s");
            $checkdate = date('Y-m-d H:i:s', strtotime("+" . $PROVIDER_BOOKING_ACCEPT_TIME_INTERVAL . " seconds", strtotime($currentdate)));
            $checkdate1 = date('Y-m-d H:i:s', strtotime("-" . $PROVIDER_BOOKING_ACCEPT_TIME_INTERVAL . " seconds", strtotime($currentdate)));
            $sql = "SELECT iDriverRequestId from driver_request WHERE iOrderId ='" . $iOrderId . "' AND ( dAddedDate > '" . $checkdate1 . "' AND dAddedDate < '" . $checkdate . "')";
            $db_status = $obj->MySQLSelect($sql);
            if (count($db_status) > 0) {
                $returnArr['Action'] = "0";
                $returnArr["message"] = "LBL_REQUEST_INPROCESS_TXT";
                $returnArr["message1"] = "REQ_PROCESS";
            } else {
                $returnArr['Action'] = "1";
                $returnArr["message"] = "LBL_REQUEST_FAILED_TXT";
                $returnArr["message1"] = "REQ_FAILED";
            }
        }
    } else {
        $returnArr['Action'] = "1";
        $returnArr["message"] = "LBL_REQUEST_INPROCESS_TXT";
        $returnArr["message1"] = "REQ_NOT_FOUND";
    }

    return $returnArr;
}

function get_day_name($timestamp) {
    $date = date('d M Y', $timestamp);
    if ($date == date('d M Y')) {
        $date = 'Today';
    } else if ($date == date('d M Y', strtotime("-1 days"))) {
        $date = 'Yesterday';
    }

    return $date;
}

function checkDistanceBetweenUserCompany($iUserAddressId, $iCompanyId) {
    global $generalobj, $obj, $LIST_RESTAURANT_LIMIT_BY_DISTANCE;
    $sql = "select vLatitude,vLongitude from `user_address` where iUserAddressId = '" . $iUserAddressId . "'";
    $db_userdata = $obj->MySQLSelect($sql);
    $passengeraddlat = $db_userdata[0]['vLatitude'];
    $passengeraddlong = $db_userdata[0]['vLongitude'];
    $sql = "select vRestuarantLocationLat,vRestuarantLocationLong from `company` where iCompanyId = '" . $iCompanyId . "'";
    $db_companydata = $obj->MySQLSelect($sql);
    $vRestuarantLocationLat = $db_companydata[0]['vRestuarantLocationLat'];
    $vRestuarantLocationLong = $db_companydata[0]['vRestuarantLocationLong'];
    $distance = distanceByLocation($passengeraddlat, $passengeraddlong, $vRestuarantLocationLat, $vRestuarantLocationLong, "K");
    if ($distance > $LIST_RESTAURANT_LIMIT_BY_DISTANCE) {
        $returnArr['Action'] = "0";
        $returnArr["message"] = "LBL_REQUEST_INPROCESS_TXT";
        setDataResponse($returnArr);
    }
}

function getremainingtimeorderrequest($iOrderId) {
    global $generalobj, $obj, $PROVIDER_BOOKING_ACCEPT_TIME_INTERVAL;
    $sql = "SELECT * from driver_request WHERE iOrderId ='" . $iOrderId . "' ORDER BY iDriverRequestId DESC LIMIT 0,1";
    $db_driver_request = $obj->MySQLSelect($sql);
    $datedifference = 0;
    if (count($db_driver_request) > 0) {
        $currentdate = @date("Y-m-d H:i:s");
        $currentdate = strtotime($currentdate);
        $dAddedDate = $db_driver_request[0]['dAddedDate'];
        $dAddedDate = strtotime($dAddedDate);
        $datedifference = $currentdate - $dAddedDate;
    }

    $Remaining_Time_In_Seconds = $PROVIDER_BOOKING_ACCEPT_TIME_INTERVAL - $datedifference;
    $Remaining_Time_In_Seconds = $Remaining_Time_In_Seconds + 10;
    if ($datedifference > 30) {
        $Remaining_Time_In_Seconds = 0;
    }

    return $Remaining_Time_In_Seconds;
}

function getTotalOrderDetailItemsCount($iOrderId) {
    global $generalobj, $obj, $tconfig;
    $sql = "SELECT count(iOrderDetailId) as TotalOrderItems FROM order_details WHERE iOrderId = '" . $iOrderId . "'";
    $data = $obj->MySQLSelect($sql);
    $TotalOrderItems = $data[0]['TotalOrderItems'];
    if ($TotalOrderItems == "" || $TotalOrderItems == NULL) {
        $TotalOrderItems = 0;
    }

    return $TotalOrderItems;
}

function OrderTotalEarningForRestaurant($iGeneralUserId, $vConvertFromDate, $vConvertToDate, $UserType = 'Company', $vTimeZone) {
    global $generalobj, $obj;
    $systemTimeZone = date_default_timezone_get();
    $vConvertFromDate = converToTz($vConvertFromDate, $vTimeZone, $systemTimeZone, "Y-m-d");
    $vConvertToDate = converToTz($vConvertToDate, $vTimeZone, $systemTimeZone, "Y-m-d");
    $conditonalFields = 'iCompanyId';
    $UserDetailsArr = getCompanyCurrencyLanguageDetails($iGeneralUserId);
    $currencycode = $UserDetailsArr['currencycode'];
    $vSymbol = $UserDetailsArr['currencySymbol'];

    // $priceRatio = $UserDetailsArr['Ratio'];
    $sql2 = "SELECT vOrderNo, iOrderId, tOrderRequestDate, iUserId, fTotalGenerateFare, fCommision, iStatusCode, fNetTotal, fOffersDiscount, fRatio_" . $currencycode . " as Ratio,fRestaurantPaidAmount,fDeliveryCharge FROM `orders` WHERE (DATE(tOrderRequestDate) BETWEEN '$vConvertFromDate' AND '$vConvertToDate') AND $conditonalFields='$iGeneralUserId' AND  `iStatusCode` IN (6, 7, 8, 11, 9)";
    $OrderData = $obj->MySQLSelect($sql2);
    $ToTalEarning = 0;
    $TotalEarningFare = 0;
    foreach ($OrderData as $key => $value) {
        $priceRatio = $value['Ratio'];
        $iStatusCode = $value['iStatusCode'];
        $fRestaurantPaidAmount = $value['fRestaurantPaidAmount'];
        if ($iStatusCode == '7' || $iStatusCode == '8') {
            $EarningFare = $fRestaurantPaidAmount;
        } else {
            $EarningFare = $value['fTotalGenerateFare'] - ($value['fCommision'] + $value['fOffersDiscount'] + $value['fDeliveryCharge']);
        }

        $EarningFare = $EarningFare * $priceRatio;
        $ToTalEarning += $EarningFare;
        $TotalEarningFare = $generalobj->setTwoDecimalPoint($ToTalEarning);

        // $TotalEarningFare = $ToTalEarning * $priceRatio;
    }

    return $TotalEarningFare;
}

function OrderTotalEarningForDriver($iGeneralUserId, $vConvertFromDate, $vConvertToDate, $UserType = 'Company', $vTimeZone) {
    global $generalobj, $obj;
    $systemTimeZone = date_default_timezone_get();
    $vConvertFromDate = converToTz($vConvertFromDate, $vTimeZone, $systemTimeZone, "Y-m-d");
    $vConvertToDate = converToTz($vConvertToDate, $vTimeZone, $systemTimeZone, "Y-m-d");
    $conditonalFields = 'iDriverId';
    $UserDetailsArr = getDriverCurrencyLanguageDetails($iGeneralUserId);
    $currencycode = $UserDetailsArr['currencycode'];
    $vSymbol = $UserDetailsArr['currencySymbol'];

    // $priceRatio = $UserDetailsArr['Ratio'];
    $sql2 = "SELECT vOrderNo, iOrderId, tOrderRequestDate, iUserId, fTotalGenerateFare, fNetTotal, fCommision, iStatusCode, fRatio_" . $currencycode . " as Ratio,fDriverPaidAmount FROM `orders` WHERE (DATE(tOrderRequestDate) BETWEEN '$vConvertFromDate' AND '$vConvertToDate') AND $conditonalFields='$iGeneralUserId' AND  `iStatusCode` IN (6, 7, 8, 11, 9)";
    $OrderData = $obj->MySQLSelect($sql2);
    $ToTalEarning = 0;
    foreach ($OrderData as $key => $value) {
        $priceRatio = $value['Ratio'];
        $OrderId = $value['iOrderId'];
        $iStatusCode = $value['iStatusCode'];
        $fDriverPaidAmount = $value['fDriverPaidAmount'];
        $subquery = "SELECT fDeliveryCharge FROM trips WHERE iOrderId = '" . $OrderId . "'";
        $DriverCharge = $obj->MySQLSelect($subquery);
        if ($iStatusCode == '7' || $iStatusCode == '8') {
            $EarningFare = $fDriverPaidAmount;
        } else {
            $EarningFare = $DriverCharge[0]['fDeliveryCharge'];
        }

        $EarningFare = $EarningFare * $priceRatio;
        $ToTalEarning += $EarningFare;
        $TotalEarningFare = $generalobj->setTwoDecimalPoint($ToTalEarning);
    }

    return $TotalEarningFare;
}

function OrderTotalEarningForPassanger($iGeneralUserId, $vConvertFromDate, $vConvertToDate, $UserType = 'Company', $vTimeZone) {
    global $generalobj, $obj;
    $systemTimeZone = date_default_timezone_get();
    $vConvertFromDate = converToTz($vConvertFromDate, $vTimeZone, $systemTimeZone, "Y-m-d");
    $vConvertToDate = converToTz($vConvertToDate, $vTimeZone, $systemTimeZone, "Y-m-d");
    $conditonalFields = 'iUserId';
    $UserDetailsArr = getUserCurrencyLanguageDetails($iGeneralUserId);
    $currencycode = $UserDetailsArr['currencycode'];
    $vSymbol = $UserDetailsArr['currencySymbol'];

    // $priceRatio = $UserDetailsArr['Ratio'];
    $sql2 = "SELECT vOrderNo, iOrderId, tOrderRequestDate, iUserId, fTotalGenerateFare, fCommision, fNetTotal, iStatusCode, fRatio_" . $currencycode . " as Ratio FROM `orders` WHERE (DATE(tOrderRequestDate) BETWEEN '$vConvertFromDate' AND '$vConvertToDate') AND $conditonalFields='$iGeneralUserId' AND  `iStatusCode` IN (6, 7, 8, 11, 9)";
    $OrderData = $obj->MySQLSelect($sql2);
    $ToTalEarning = 0;
    foreach ($OrderData as $key => $value) {
        $priceRatio = $value['Ratio'];
        $EarningFare = $value['fNetTotal'];
        $EarningFare = $EarningFare * $priceRatio;
        $ToTalEarning += $EarningFare;
        $TotalEarningFare = $generalobj->setTwoDecimalPoint($ToTalEarning);
    }

    return $TotalEarningFare;
}

########################### Get Passenger Outstanding Amount#############################################################

/* function GetPassengerOutstandingAmount($iUserId) {
  global $generalobj, $obj;
  $sql = "SELECT SUM( `fCancellationFare` ) AS fCancellationFare FROM trip_outstanding_amount WHERE iUserId='" . $iUserId . "' AND ePaidByPassenger = 'No'";
  $tripoutstandingdata = $obj->MySQLSelect($sql);
  $fCancellationFare = $tripoutstandingdata[0]['fCancellationFare'];
  if ($fCancellationFare == "" || $fCancellationFare == NULL) {
  $fCancellationFare = 0;
  }

  return $fCancellationFare;
  } */

########################### Get Passenger  Outstanding Amount#############################################################
########################### Get Total Order Discount Amount From order detail for menu item wise##########################

function getOrderDetailTotalDiscountPrice($iOrderId) {
    global $generalobj, $obj, $tconfig;
    $sql = "SELECT SUM( `fTotalDiscountPrice` ) AS TotalDiscountPrice FROM order_details WHERE iOrderId = '" . $iOrderId . "' AND eAvailable = 'Yes'";
    $data = $obj->MySQLSelect($sql);
    $TotalDiscountPrice = $data[0]['TotalDiscountPrice'];
    if ($TotalDiscountPrice == "" || $TotalDiscountPrice == NULL) {
        $TotalDiscountPrice = 0;
    }

    return $TotalDiscountPrice;
}

########################### Get Total Order Discount Amount From order detail for menu item wise##########################
########################### Get Total Order Discount Amount From order detail for menu item wise##########################

function getOrderDetailSubTotalPrice($iOrderId) {
    global $generalobj, $obj, $tconfig;

    // $sql = "SELECT SUM( `fOriginalPrice` * `iQty` ) AS TotalOriginalPrice FROM order_details WHERE iOrderId = '".$iOrderId."' AND eAvailable = 'Yes'";
    $sql = "SELECT SUM( `fTotalPrice` ) AS TotalPrice FROM order_details WHERE iOrderId = '" . $iOrderId . "' AND eAvailable = 'Yes'";
    $data = $obj->MySQLSelect($sql);
    $TotalPrice = $data[0]['TotalPrice'];
    if ($TotalPrice == "" || $TotalPrice == NULL) {
        $TotalPrice = 0;
    }

    return $TotalPrice;
}

########################### Get Total Order Discount Amount From order detail for menu item wise##########################
########################### Calculate Order Discount Amount By Company Offer and menu item wise###########################

function CalculateOrderDiscountPrice($iOrderId) {
    global $obj, $generalobj, $tconfig;
    $sql = "select * from orders where iOrderId='" . $iOrderId . "'";
    $data_order = $obj->MySQLSelect($sql);
    $iCompanyId = $data_order[0]['iCompanyId'];

    // $fSubTotal = $data_order[0]['fSubTotal'];
    $fSubTotal = getOrderDetailSubTotalPrice($iOrderId);
    $iUserId = $data_order[0]['iUserId'];
    $TotOrders = 1;
    if ($iUserId > 0) {
        $sql = "select count(iOrderId) as TotOrders from orders where iUserId ='" . $iUserId . "' AND iCompanyId = '" . $iCompanyId . "' AND iStatusCode NOT IN(12)";
        $db_order = $obj->MySQLSelect($sql);
        $TotOrders = $db_order[0]['TotOrders'];
    }

    $sql = "SELECT * FROM `company` WHERE iCompanyId = '" . $iCompanyId . "'";
    $DataCompany = $obj->MySQLSelect($sql);
    $fMinOrderValue = $DataCompany[0]['fMinOrderValue'];
    $fOfferAppyType = $DataCompany[0]['fOfferAppyType'];
    $fOfferType = $DataCompany[0]['fOfferType'];
    $fMaxOfferAmt = $DataCompany[0]['fMaxOfferAmt'];
    $fTargetAmt = $DataCompany[0]['fTargetAmt'];
    $fOfferAmt = $DataCompany[0]['fOfferAmt'];
    if ($fOfferAppyType == "None") {
        $TotalDiscountPrice = getOrderDetailTotalDiscountPrice($iOrderId);
    } else if ($fOfferAppyType == "All") {
        if ($fSubTotal >= $fTargetAmt) {
            if ($fOfferType == "Percentage") {
                $fDiscount = (($fSubTotal * $fOfferAmt) / 100);
                $fDiscount = round($fDiscount, 2);
                $fDiscount = (($fDiscount > $fMaxOfferAmt) && ($fMaxOfferAmt > 0)) ? $fMaxOfferAmt : $fDiscount;
                $TotalDiscountPrice = $fDiscount;
            } else {
                $fDiscount = $fOfferAmt;
                $fDiscount = round($fDiscount, 2);
                $TotalDiscountPrice = $fDiscount;
            }
        } else {
            $TotalDiscountPrice = 0;
        }
    } else {
        if ($TotOrders <= 1) {
            if ($fSubTotal >= $fTargetAmt) {
                if ($fOfferType == "Percentage") {
                    $fDiscount = (($fSubTotal * $fOfferAmt) / 100);
                    $fDiscount = round($fDiscount, 2);
                    $fDiscount = (($fDiscount > $fMaxOfferAmt) && ($fMaxOfferAmt > 0)) ? $fMaxOfferAmt : $fDiscount;
                    $TotalDiscountPrice = $fDiscount;
                } else {
                    $fDiscount = $fOfferAmt;
                    $fDiscount = round($fDiscount, 2);
                    $TotalDiscountPrice = $fDiscount;
                }
            } else {
                $TotalDiscountPrice = 0;
            }
        } else {
            $TotalDiscountPrice = getOrderDetailTotalDiscountPrice($iOrderId);
        }
    }

    return round($TotalDiscountPrice, 2);
}

########################### Calculate Order Discount Amount By Company Offer and menu item wise###########################
########################### Get Menu Item Price By Restaurant Offer Wise##################################################

function getMenuItemPriceByCompanyOffer($iMenuItemId, $iCompanyId, $iQty = 1, $iUserId = 0, $eFor = "Display", $vOptionId = "", $vAddonId = "", $iServiceId = "") {
    global $obj, $generalobj, $tconfig;
    $TotOrders = $fPrice = 0;
    if ($iUserId > 0) {
        $sql = "select count(iOrderId) as TotOrders from orders where iUserId ='" . $iUserId . "' AND iCompanyId = '" . $iCompanyId . "' AND iStatusCode NOT IN(12)";
        $db_order = $obj->MySQLSelect($sql);
        $TotOrders = $db_order[0]['TotOrders'];
    }
    $ispriceshow = '';
    if (isset($iServiceId) && !empty($iServiceId)) {
        $servFields = 'eType';
        $ServiceCategoryData = get_value('service_categories', $servFields, 'iServiceId', $iServiceId);
        if (!empty($ServiceCategoryData)) {
            if (!empty($ServiceCategoryData[0]['eType']) && $ServiceCategoryData[0]['eType'] == 'separate') {
                $ispriceshow = $ServiceCategoryData[0]['eType'];
            }
        }
    }
    $db_price = $obj->MySQLSelect("select iFoodMenuId,fPrice,fOfferAmt from menu_items where iMenuItemId ='" . $iMenuItemId . "'");
    if (isset($db_price[0]['fPrice']) && $db_price[0]['fPrice'] > 0) {
        $fPrice = $db_price[0]['fPrice'];
    }
    //print_r($iMenuItemId);die;
    if (isset($ispriceshow) && !empty($ispriceshow)) {
        $fPrice = 0;
        $vOptionPrice = GetFoodMenuItemOptionPrice($vOptionId);
        if ($vOptionPrice == 0) {
            $fPrice = $db_price[0]['fPrice'];
        }
    }
    if ($vOptionId != "") {
        $vOptionPrice = GetFoodMenuItemOptionPrice($vOptionId);
        $fPrice += $vOptionPrice;
    }

    if ($vAddonId != "") {
        $vAddonPrice = GetFoodMenuItemAddOnPrice($vAddonId);
        $fPrice += $vAddonPrice;
    }
    $DataCompany = $obj->MySQLSelect("SELECT * FROM `company` WHERE iCompanyId = '" . $iCompanyId . "'");
    $fOfferAppyType = "None";
    $fOfferType = "Flat";
    $fMaxOfferAmt = $fTargetAmt = $fOfferAmt = 0;
    if (count($DataCompany) > 0) {
        $fOfferAppyType = $DataCompany[0]['fOfferAppyType'];
        $fOfferType = $DataCompany[0]['fOfferType'];
        $fMaxOfferAmt = $DataCompany[0]['fMaxOfferAmt'];
        $fTargetAmt = $DataCompany[0]['fTargetAmt'];
    }
    $fPrice = $fPrice * $iQty;
    $fOriginalPrice = $fPrice;
    $fOfferAmt = $fDiscountPrice = 0;
    if ($fOfferAppyType == "None") {
        $fOfferAmt = $db_price[0]['fOfferAmt'];
        if ($fOfferAmt > 0) {
            $fDiscountPrice = (($fPrice * $fOfferAmt * $iQty) / 100);
            $fDiscountPrice = round($fDiscountPrice, 2);
            $fPrice = $fPrice - $fDiscountPrice;
        }
        $returnArr['fOriginalPrice'] = $fOriginalPrice;
        $returnArr['fDiscountPrice'] = $fDiscountPrice;
        $returnArr['fPrice'] = $fPrice;
        $returnArr['fOfferAmt'] = $fOfferAmt;
        $returnArr['TotOrders'] = $TotOrders;
    } else if ($fOfferAppyType == "All") {
        $fOfferAmt = $DataCompany[0]['fOfferAmt'];
        if ((($fTargetAmt == 0 || $fTargetAmt == "") && $eFor == "Display") || $eFor == "Calculate") {
            if ($fOfferType == "Percentage") {
                if ($fOfferAmt > 0) {
                    $fDiscountPrice = (($fPrice * $fOfferAmt * $iQty) / 100);
                    $fDiscountPrice = round($fDiscountPrice, 2);
                    $fDiscountPrice = (($fDiscountPrice > $fMaxOfferAmt) && ($fMaxOfferAmt > 0)) ? $fMaxOfferAmt : $fDiscountPrice;
                    $fPrice = $fOriginalPrice - $fDiscountPrice;
                }
            } else {
                if ($eFor == "Calculate") {
                    if ($fOfferAmt > 0) {
                        $fDiscountPrice = $fOfferAmt * $iQty;
                        $fDiscountPrice = ($fDiscountPrice < 0) ? 0 : $fDiscountPrice;
                        $fPrice = $fOriginalPrice;
                    }
                }
            }
        }
        $returnArr['fOriginalPrice'] = $fOriginalPrice;
        $returnArr['fDiscountPrice'] = $fDiscountPrice;
        $returnArr['fPrice'] = $fPrice;
        $returnArr['fOfferAmt'] = $fOfferAmt;
        $returnArr['TotOrders'] = $TotOrders;
    } else {
        if ($TotOrders == 0) {
            $fOfferAmt = $DataCompany[0]['fOfferAmt'];
            if ((($fTargetAmt == 0 || $fTargetAmt == "") && $eFor == "Display") || $eFor == "Calculate") {
                if ($fOfferType == "Percentage") {
                    if ($fOfferAmt > 0) {
                        $fDiscountPrice = (($fPrice * $fOfferAmt * $iQty) / 100);
                        $fDiscountPrice = round($fDiscountPrice, 2);

                        // $fDiscountPrice = (($fDiscountPrice > $fMaxOfferAmt) && ($fMaxOfferAmt > 0))?$fMaxOfferAmt:$fDiscountPrice;
                        $fPrice = $fOriginalPrice - $fDiscountPrice;
                    }
                } else {
                    if ($eFor == "Calculate") {
                        if ($fOfferAmt > 0) {
                            $fDiscountPrice = $fOfferAmt;
                            $fDiscountPrice = ($fDiscountPrice < 0) ? 0 : $fDiscountPrice;
                            $fPrice = $fOriginalPrice;
                        }
                    }
                }
            }
        } else {
            $fOfferAmt = $db_price[0]['fOfferAmt'];
            if ($fOfferAmt > 0) {
                $fDiscountPrice = (($fPrice * $fOfferAmt * $iQty) / 100);
                $fDiscountPrice = round($fDiscountPrice, 2);
                $fPrice = $fOriginalPrice - $fDiscountPrice;
            }
        }

        $returnArr['fOriginalPrice'] = $fOriginalPrice;
        $returnArr['fDiscountPrice'] = $fDiscountPrice;
        $returnArr['fPrice'] = $fPrice;
        $returnArr['fOfferAmt'] = $fOfferAmt;
        $returnArr['TotOrders'] = $TotOrders;
    }

    //echo "<pre>";print_r($returnArr);exit;
    return $returnArr;
}

function getMenuItemPriceByCompanyOfferweb($iMenuItemId, $iCompanyId, $iQty = 1, $iUserId = 0, $eFor = "Display", $vOptionId = "", $vAddonId = "", $iServiceId = "") {
    global $obj, $generalobj, $tconfig;
    $TotOrders = 0;
    if ($iUserId > 0) {
        $sql = "select count(iOrderId) as TotOrders from orders where iUserId ='" . $iUserId . "' AND iCompanyId = '" . $iCompanyId . "' AND iStatusCode NOT IN(12)";
        $db_order = $obj->MySQLSelect($sql);
        $TotOrders = $db_order[0]['TotOrders'];
    }
    $ispriceshow = '';
    if (isset($iServiceId) && !empty($iServiceId)) {
        $servFields = 'eType';
        $ServiceCategoryData = get_value('service_categories', $servFields, 'iServiceId', $iServiceId);
        if (!empty($ServiceCategoryData)) {
            if (!empty($ServiceCategoryData[0]['eType']) && $ServiceCategoryData[0]['eType'] == 'separate') {
                $ispriceshow = $ServiceCategoryData[0]['eType'];
            }
        }
    }
    $db_price = $obj->MySQLSelect("select iFoodMenuId,fPrice,fOfferAmt from menu_items where iMenuItemId ='" . $iMenuItemId . "'");
    $fPrice = 0;
    if (isset($db_price[0]['fPrice']) && $db_price[0]['fPrice'] > 0) {
        $fPrice = $db_price[0]['fPrice'];
    }
    if (isset($ispriceshow) && !empty($ispriceshow)) {
        $fPrice = 0;
        $vOptionPrice = GetFoodMenuItemOptionPrice($vOptionId);
        if ($vOptionPrice == 0) {
            $fPrice = $db_price[0]['fPrice'];
        }
    }
    if ($vOptionId != "") {
        $vOptionPrice = GetFoodMenuItemOptionPrice($vOptionId);
        $fPrice += $vOptionPrice;
    }
    if ($vAddonId != "") {
        $vAddonPrice = GetFoodMenuItemAddOnPrice($vAddonId);
        $fPrice += $vAddonPrice;
    }
    $fPrice = $fPrice * $iQty;
    $fOriginalPrice = $fPrice;
    $fOfferAppyType = "None";
    $fOfferType = "Flat";
    $fMaxOfferAmt = $fTargetAmt = $fOfferAmt = $fDiscountPrice = 0;
    $DataCompany = $obj->MySQLSelect("SELECT * FROM `company` WHERE iCompanyId = '" . $iCompanyId . "'");
    if (count($DataCompany) > 0) {
        $fOfferAppyType = $DataCompany[0]['fOfferAppyType'];
        $fOfferType = $DataCompany[0]['fOfferType'];
        $fMaxOfferAmt = $DataCompany[0]['fMaxOfferAmt'];
        $fTargetAmt = $DataCompany[0]['fTargetAmt'];
    }
    if ($fOfferAppyType == "None") {
        if (isset($DataCompany[0]['fOfferAmt']) && $DataCompany[0]['fOfferAmt'] > 0) {
            $fOfferAmt = $db_price[0]['fOfferAmt'];
        }
        if ($fOfferAmt > 0) {
            $fDiscountPrice = (($fPrice * $fOfferAmt * $iQty) / 100);
            $fDiscountPrice = round($fDiscountPrice, 2);
            $fPrice = $fPrice - $fDiscountPrice;
        }
        $returnArr['fOriginalPrice'] = $fOriginalPrice;
        $returnArr['fDiscountPrice'] = $fDiscountPrice;
        $returnArr['fPrice'] = $fPrice;
        $returnArr['fOfferAmt'] = $fOfferAmt;
        $returnArr['TotOrders'] = $TotOrders;
    } else if ($fOfferAppyType == "All") {
        if (isset($DataCompany[0]['fOfferAmt']) && $DataCompany[0]['fOfferAmt'] > 0) {
            $fOfferAmt = $DataCompany[0]['fOfferAmt'];
        }
        if ((($fTargetAmt == 0 || $fTargetAmt == "") && $eFor == "Display") || $eFor == "Calculate") {
            if ($fOfferType == "Percentage") {
                if ($fOfferAmt > 0) {
                    $fDiscountPrice = (($fPrice * $fOfferAmt * $iQty) / 100);
                    $fDiscountPrice = round($fDiscountPrice, 2);
                    $fDiscountPrice = (($fDiscountPrice > $fMaxOfferAmt) && ($fMaxOfferAmt > 0)) ? $fMaxOfferAmt : $fDiscountPrice;
                    $fPrice = $fOriginalPrice - $fDiscountPrice;
                }
            } else {
                if ($eFor == "Calculate") {
                    if ($fOfferAmt > 0) {
                        $fDiscountPrice = $fOfferAmt * $iQty;
                        $fDiscountPrice = ($fDiscountPrice < 0) ? 0 : $fDiscountPrice;
                        $fPrice = $fOriginalPrice;
                    }
                }
            }
        }
        $returnArr['fOriginalPrice'] = $fOriginalPrice;
        $returnArr['fDiscountPrice'] = $fDiscountPrice;
        $returnArr['fPrice'] = $fPrice;
        $returnArr['fOfferAmt'] = $fOfferAmt;
        $returnArr['TotOrders'] = $TotOrders;
    } else {
        if ($TotOrders == 0) {
            $fOfferAmt = $DataCompany[0]['fOfferAmt'];
            if ((($fTargetAmt == 0 || $fTargetAmt == "") && $eFor == "Display") || $eFor == "Calculate") {
                if ($fOfferType == "Percentage") {
                    if ($fOfferAmt > 0) {
                        $fDiscountPrice = (($fPrice * $fOfferAmt * $iQty) / 100);
                        $fDiscountPrice = round($fDiscountPrice, 2);
                        // $fDiscountPrice = (($fDiscountPrice > $fMaxOfferAmt) && ($fMaxOfferAmt > 0))?$fMaxOfferAmt:$fDiscountPrice;
                        $fPrice = $fOriginalPrice - $fDiscountPrice;
                    }
                } else {
                    if ($eFor == "Calculate") {
                        if ($fOfferAmt > 0) {
                            $fDiscountPrice = $fOfferAmt;
                            $fDiscountPrice = ($fDiscountPrice < 0) ? 0 : $fDiscountPrice;
                            $fPrice = $fOriginalPrice;
                        }
                    }
                }
            }
        } else {
            $fOfferAmt = $db_price[0]['fOfferAmt'];
            if ($fOfferAmt > 0) {
                $fDiscountPrice = (($fPrice * $fOfferAmt * $iQty) / 100);
                $fDiscountPrice = round($fDiscountPrice, 2);
                $fPrice = $fOriginalPrice - $fDiscountPrice;
            }
        }
        $returnArr['fOriginalPrice'] = $fOriginalPrice;
        $returnArr['fDiscountPrice'] = $fDiscountPrice;
        $returnArr['fPrice'] = $fPrice;
        $returnArr['fOfferAmt'] = $fOfferAmt;
        $returnArr['TotOrders'] = $TotOrders;
    }
    // echo "<pre>";print_r($returnArr);exit;
    return $returnArr;
}

########################### Get Menu Item Price By Restaurant Offer Wise##################################################
############################# Get Menu Item Option / AddOn Name ##################################################################

function GetMenuItemOptionsToppingName($iOptionId = "") {
    global $generalobj, $obj, $tconfig;
    $vOptionName = "";
    if ($iOptionId != "") {
        $str = "select vOptionName from `menuitem_options` where iOptionId IN(" . $iOptionId . ")";
        $db_options_data = $obj->MySQLSelect($str);
        if (count($db_options_data) > 0) {
            for ($i = 0; $i < count($db_options_data); $i++) {
                $vOptionName .= $db_options_data[$i]['vOptionName'] . ", ";
            }
        }
    }

    return trim($vOptionName, ",");
}

############################# Get Menu Item Option Name ##################################################################
############################# Get Order Status Code Text ##################################################################

function GetOrderStatusLogText($iOrderId, $UserType = "Passenger") {
    global $generalobj, $obj, $tconfig;
    $sql = "SELECT ord.iUserId,ord.iDriverId,ord.iCompanyId,ord.iStatusCode,ord.iServiceId,os.vStatus_Track,os.vStatus,osl.dDate FROM order_status as os LEFT JOIN orders as ord ON os.iStatusCode = ord.iStatusCode LEFT JOIN order_status_logs as osl ON osl.iStatusCode = ord.iStatusCode WHERE ord.iOrderId = '" . $iOrderId . "' ORDER BY osl.dDate DESC LIMIT 0,1";
    $data_order = $obj->MySQLSelect($sql);
    $iCompanyId = $data_order[0]['iCompanyId'];
    $iUserId = $data_order[0]['iUserId'];
    $iDriverId = $data_order[0]['iDriverId'];
    $sql = "SELECT CONCAT(vName,' ',vLastName) AS driverName FROM `register_driver` WHERE iDriverId = '$iDriverId'";
    $Data_vehicle = $obj->MySQLSelect($sql);
    $drivername = $Data_vehicle[0]['driverName'];
    $iStatusCode = $data_order[0]['iStatusCode'];
    $dDate = $data_order[0]['dDate'];
    $vStatus = $data_order[0]['vStatus'];
    $iServiceId = $data_order[0]['iServiceId'];

    // $StatusDate = date('l, dS M Y',strtotime($dDate));
    $StatusDate = date('F d, Y h:iA', strtotime($dDate)); //h:iA
    if ($UserType == "Passenger") {
        $UserDetailsArr = getUserCurrencyLanguageDetails($iUserId, $iOrderId);
    } else if ($UserType == "Driver") {
        $UserDetailsArr = getDriverCurrencyLanguageDetails($iDriverId, $iOrderId);
    } else {
        $UserDetailsArr = getCompanyCurrencyLanguageDetails($iCompanyId, $iOrderId);
    }

    $vLang = $UserDetailsArr['vLang'];
    $languageLabelsArr = getLanguageLabelsArr($vLang, "1", $iServiceId);
    $Displaytext = "";
    if ($iStatusCode == "8") {
        $Displaytext = $languageLabelsArr['LBL_CANCELLED_ON'] . " " . $StatusDate;
    }

    if ($iStatusCode == "6") {
        $Displaytext = $languageLabelsArr['LBL_ORDER_DELIVERED_ON'] . " " . $StatusDate . " " . $languageLabelsArr['LBL_BY'] . " " . $drivername;
    }

    return $Displaytext;
}

############################# Get Order Status Code Text ##################################################################
############################# Check Menu Item Availability When Order Placed By User#######################################

function checkmenuitemavailability($OrderDetails = array()) {
    global $obj, $generalobj, $tconfig;
    $isAllItemAvailable = "Yes";
    $isAllItemOptionsAvailable = "Yes";
    $isAllItemToppingssAvailable = "Yes";
    if (count($OrderDetails) > 0) {
        for ($i = 0; $i < count($OrderDetails); $i++) {
            $iMenuItemId = $OrderDetails[$i]['iMenuItemId'];
            $str = "select eAvailable,eStatus from menu_items where iMenuItemId ='" . $iMenuItemId . "'";
            $db_menu_item = $obj->MySQLSelect($str);
            $eStatus = $db_menu_item[0]['eStatus'];
            $eAvailable = $db_menu_item[0]['eAvailable'];
            if ($eAvailable == "No" || $eStatus != "Active") {
                $isAllItemAvailable = "No";
                break;
            }
        }

        for ($j = 0; $j < count($OrderDetails); $j++) {
            $vOptionId = $OrderDetails[$j]['vOptionId'];
            if ($vOptionId != "") {
                $str = "select eStatus from menuitem_options where iOptionId IN(" . $vOptionId . ")";
                $db_menu_item_option = $obj->MySQLSelect($str);
                $eStatus1 = $db_menu_item_option[0]['eStatus'];
                if ($eStatus1 != "Active") {
                    $isAllItemOptionsAvailable = "No";
                    break;
                }
            }
        }

        for ($k = 0; $k < count($OrderDetails); $k++) {
            $vAddonId = $OrderDetails[$k]['vAddonId'];
            if ($vAddonId != "") {
                $str = "select eStatus from menuitem_options where iOptionId IN(" . $vAddonId . ")";
                $db_menu_item_Addon = $obj->MySQLSelect($str);
                $eStatus2 = $db_menu_item_Addon[0]['eStatus'];
                if ($eStatus2 != "Active") {
                    $isAllItemToppingssAvailable = "No";
                    break;
                }
            }
        }
    }

    $returnArr['isAllItemAvailable'] = $isAllItemAvailable;
    $returnArr['isAllItemOptionsAvailable'] = $isAllItemOptionsAvailable;
    $returnArr['isAllItemToppingssAvailable'] = $isAllItemToppingssAvailable;
    return $returnArr;
}

############################# Check Menu Item Availability When Order Placed By User#######################
############# Get Text For Order Refund Or Cancelled ###############

function GetOrderStatusLogTextForCancelled($iOrderId, $UserType = "Passenger") {
    global $generalobj, $obj, $tconfig;
    $sql = "SELECT ord.iUserId,ord.iDriverId,ord.iCompanyId,ord.fRefundAmount,ord.iStatusCode,ord.iServiceId,os.vStatus_Track,os.vStatus,osl.dDate,ord.fCancellationCharge,ord.fRestaurantPaidAmount,ord.fDriverPaidAmount FROM order_status as os LEFT JOIN orders as ord ON os.iStatusCode = ord.iStatusCode LEFT JOIN order_status_logs as osl ON osl.iStatusCode = ord.iStatusCode WHERE ord.iOrderId = '" . $iOrderId . "' ORDER BY osl.dDate DESC LIMIT 0,1";
    $data_order = $obj->MySQLSelect($sql);
    $iCompanyId = $data_order[0]['iCompanyId'];
    $iUserId = $data_order[0]['iUserId'];
    $iDriverId = $data_order[0]['iDriverId'];
    $sql = "SELECT CONCAT(vName,' ',vLastName) AS driverName FROM `register_driver` WHERE iDriverId = '$iDriverId'";
    $Data_vehicle = $obj->MySQLSelect($sql);
    $drivername = $Data_vehicle[0]['driverName'];
    $iStatusCode = $data_order[0]['iStatusCode'];
    $dDate = $data_order[0]['dDate'];
    $vStatus = $data_order[0]['vStatus'];
    $fRefundAmount = $data_order[0]['fRefundAmount'];
    $fCancellationCharge = $data_order[0]['fCancellationCharge'];
    $fRestaurantPaidAmount = $data_order[0]['fRestaurantPaidAmount'];
    $fDriverPaidAmount = $data_order[0]['fDriverPaidAmount'];
    $iServiceId = $data_order[0]['iServiceId'];

    // $StatusDate = date('l, dS M Y',strtotime($dDate));
    $StatusDate = date('F d, Y h:iA', strtotime($dDate)); //h:iA
    if ($UserType == "Passenger") {
        $UserDetailsArr = getUserCurrencyLanguageDetails($iUserId, $iOrderId);
    } else if ($UserType == "Driver") {
        $UserDetailsArr = getDriverCurrencyLanguageDetails($iDriverId, $iOrderId);
    } else {
        $UserDetailsArr = getCompanyCurrencyLanguageDetails($iCompanyId, $iOrderId);
    }

    $Ratio = $UserDetailsArr['Ratio'];
    $currencySymbol = $UserDetailsArr['currencySymbol'];
    $vLang = $UserDetailsArr['vLang'];
    $languageLabelsArr = getLanguageLabelsArr($vLang, "1", $iServiceId);
    $Displaytext = "";
    if ($UserType == "Passenger") {
        if ($iStatusCode == "8") {
            $fCancellationChargeNew = $fCancellationCharge * $Ratio;
            $fCancellationCharge = formatNum($fCancellationChargeNew);
            $CancellationCharge = $currencySymbol . $fCancellationCharge;
            $CancellationChargeTxt = $languageLabelsArr["LBL_CANCELLATION_CHARGE"] . ":" . $CancellationCharge;
            $Displaytext = $languageLabelsArr["LBL_ORDER_CANCEL_TEXT"] . "\n" . $CancellationChargeTxt;
        }

        if ($iStatusCode == "7") {

            // $Displaytext = $languageLabelsArr["LBL_ORDER_REFUND_TEXT"];
            $fCancellationChargeNew = $fCancellationCharge * $Ratio;
            $fCancellationCharge = formatNum($fCancellationChargeNew);
            $CancellationCharge = $currencySymbol . $fCancellationCharge;
            $CancellationChargeTxt = $languageLabelsArr["LBL_CANCELLATION_CHARGE"] . ":" . $CancellationCharge;
            $fRefundAmountnew = $fRefundAmount * $Ratio;
            $fRefundAmount = formatNum($fRefundAmountnew);
            $RefundAmount = $currencySymbol . $fRefundAmount;
            $RefundAmountTxt = $languageLabelsArr["LBL_REFUND_APP_TXT"] . ":" . $RefundAmount;
            $Displaytext = $languageLabelsArr["LBL_ORDER_REFUND_TEXT"] . "\n" . $CancellationChargeTxt . "\n" . $RefundAmountTxt;
        }
    } else if ($UserType == "Company") {
        if ($iStatusCode == "8" || $iStatusCode == "7") {
            $fRestaurantPaidAmountNew = $fRestaurantPaidAmount * $Ratio;
            $fRestaurantPaidAmount = formatNum($fRestaurantPaidAmountNew);
            $fRestaurantPaidAmount = $currencySymbol . $fRestaurantPaidAmount;
            if ($data_order[0]['fRestaurantPaidAmount'] > 0) {
                $fRestaurantPaidAmountTxt = $languageLabelsArr["LBL_ADJUSTMENT_AMOUNT_MESSAGE"] . ":" . $fRestaurantPaidAmount;
            } else {
                $fRestaurantPaidAmountTxt = $languageLabelsArr["LBL_AMT_GENERATE_PENDING"];
            }

            if ($iStatusCode == "8") {
                $Displaytext = $languageLabelsArr["LBL_ORDER_CANCEL_TEXT"] . "\n" . $fRestaurantPaidAmountTxt;
            } else if ($iStatusCode == "7") {
                $Displaytext = $languageLabelsArr["LBL_ORDER_REFUND_TEXT"] . "\n" . $fRestaurantPaidAmountTxt;
            }
        }
    } else {
        if ($iStatusCode == "8" || $iStatusCode == "7") {
            $fDriverPaidAmountNew = $fDriverPaidAmount * $Ratio;
            $fDriverPaidAmount = formatNum($fDriverPaidAmount);
            $fDriverPaidAmount = $currencySymbol . $fDriverPaidAmount;
            if ($data_order[0]['fDriverPaidAmount'] > 0) {
                $fDriverPaidAmountTxt = $languageLabelsArr["LBL_ADJUSTMENT_AMOUNT_MESSAGE"] . ":" . $fDriverPaidAmount;
            } else {
                $fDriverPaidAmountTxt = $languageLabelsArr["LBL_AMT_GENERATE_PENDING"];
            }

            if ($iStatusCode == "8") {
                $Displaytext = $languageLabelsArr["LBL_ORDER_CANCEL_TEXT"] . "\n" . $fDriverPaidAmountTxt;
            } else if ($iStatusCode == "7") {
                $Displaytext = $languageLabelsArr["LBL_ORDER_REFUND_TEXT"] . "\n" . $fDriverPaidAmountTxt;
            }
        }
    }

    // $returnArr['Displaytext'] = $Displaytext;
    return $Displaytext;
}

############# ENd Text For Order Refund Or Cancelled ###############
############# Update Company LAt Long For Demo Mode ###############

function updatecompanylatlong($latitude, $longitude, $iCompanyId) {
    global $obj, $generalobj, $tconfig, $GOOGLE_SEVER_API_KEY_WEB;
    if (SITE_TYPE == "Demo") {
        $url = "https://maps.googleapis.com/maps/api/geocode/json?key=" . $GOOGLE_SEVER_API_KEY_WEB . "&language=en&latlng=" . $latitude . "," . $longitude;
        $jsonfile = file_get_contents($url);
        $jsondata = json_decode($jsonfile);
        $location_Address = $jsondata->results[0]->formatted_address;
        $latitude_new = $jsondata->results[0]
                ->geometry
                ->location->lat;
        $longitude_new = $jsondata->results[0]
                ->geometry
                ->location->lng;
        if ($location_Address == "" || $location_Address == NULL) {
            $FilterArray = array(
                0.0015,
                0.0020,
                0.0025,
                0.0030,
                0.0035,
                0.0040
            );
            $k = array_rand($FilterArray);
            $num = $FilterArray[$k];
            $latitude_new = $latitude + $num;
            $longitude_new = $longitude + $num;
            $location_Address = getAddressFromLocation($latitude_new, $longitude_new, $GOOGLE_SEVER_API_KEY_WEB);
        }

        $where = " iCompanyId = '" . $iCompanyId . "'";
        $Data['vRestuarantLocation'] = $location_Address;
        $Data['vCaddress'] = $location_Address;
        $Data['vRestuarantLocationLat'] = $latitude_new;
        $Data['vRestuarantLocationLong'] = $longitude_new;
        $Data['eLock'] = "Yes";
        $id = $obj->MySQLQueryPerform("company", $Data, 'update', $where);
    }

    return $iCompanyId;
}

############# Update Company LAt Long For Demo Mode ###############
################# Display Recommended and Best Seller Menu Items#############################

function getRecommendedBestSellerMenuItems($iCompanyId, $iUserId, $DisplayType = "Recommended", $CheckNonVegFoodType = "", $searchword = "", $iServiceId = "", $vLang = "EN") {
    global $obj, $generalobj, $tconfig;
    $returnArr = array();
    $UserDetailsArr = getUserCurrencyLanguageDetails($iUserId, 0);
    if (!empty($vLang)) {
        $vLanguage = $vLang;
    } else {
        $vLanguage = $UserDetailsArr['vLang'];
    }
    $currencySymbol = $UserDetailsArr['currencySymbol'];
    $currencycode = $UserDetailsArr['currencycode'];
    $Ratio = $UserDetailsArr['Ratio'];
    $ssql1 = "";
    if ($DisplayType == "Recommended") {
        $ssql1 .= " AND eRecommended = 'Yes' ";
    } else {
        $ssql1 .= " AND eBestSeller = 'Yes' ";
    }
    //Added By HJ On 04-02-2019 For Convert All Amount In Store Wise Currency Ration Start
    $store_currency = get_value('company', 'store_currency', 'iCompanyId', $iCompanyId, '', 'true');
    $currencyArr = $currencySymbolArr = array();
    $getCurrencyRation = $obj->MySQLSelect("SELECT Ratio,iCurrencyId,vSymbol FROM currency WHERE eStatus='Active'");
    for ($c = 0; $c < count($getCurrencyRation); $c++) {
        $currencyArr[$getCurrencyRation[$c]['iCurrencyId']] = $getCurrencyRation[$c]['Ratio'];
        $currencySymbolArr[$getCurrencyRation[$c]['iCurrencyId']] = $getCurrencyRation[$c]['vSymbol'];
    }
    if (isset($currencyArr[$store_currency])) {
        $Ratio = $currencyArr[$store_currency];
        $currencySymbol = $currencySymbolArr[$store_currency];
    }
    //Added By HJ On 04-02-2019 For Convert All Amount In Store Wise Currency Ration End
    $sql = "SELECT fm.* FROM food_menu as fm WHERE fm.iCompanyId = '" . $iCompanyId . "' AND fm.eStatus='Active' ORDER BY fm.iDisplayOrder ASC";
    $db_food_data = $obj->MySQLSelect($sql);
    $MenuItemsDataArr = array();
    $itemimimgUrl = $tconfig["tsite_upload_images_menu_item"];
    if (count($db_food_data) > 0) {
        $ssql = "";
        if ($CheckNonVegFoodType == "Veg") {
            $ssql .= " AND (eFoodType = 'Veg' OR eFoodType = '') ";
        } else if ($CheckNonVegFoodType == "NonVeg") {
            $ssql .= " AND (eFoodType = 'NonVeg' OR eFoodType = '') ";
        } else if ($CheckNonVegFoodType == "VegNonVeg") {
            $ssql .= " AND (eFoodType = 'NonVeg' OR eFoodType = 'Veg' OR eFoodType = '') ";
        } else if ($CheckNonVegFoodType == "NonVegVeg") {
            $ssql .= " AND (eFoodType = 'NonVeg' OR eFoodType = 'Veg' OR eFoodType = '') ";
        }
        if ($searchword != "") {
            $ssql .= " AND LOWER(vItemType_" . $vLanguage . ") LIKE '%" . $searchword . "%' ";
        }
        $foodMenuIteIds = "";
        for ($h = 0; $h < count($db_food_data); $h++) {
            $foodMenuIteIds .= ",'" . $db_food_data[$h]['iFoodMenuId'] . "'";
        }
        $foodItemArr = $menuItemArr = $topingArr = $itemPriceArr = array();
        if ($foodMenuIteIds != "") {
            $languageLabelsArr = getLanguageLabelsArr($vLanguage, "1", $iServiceId);
            $foodItems = trim($foodMenuIteIds, ",");
            //$sqlf = "SELECT mi.iMenuItemId,mi.iFoodMenuId,mi.vItemType_" . $vLanguage . " as vItemType,mi.vItemDesc_" . $vLanguage . " as vItemDesc, mi.fPrice, mi.eFoodType, mi.fOfferAmt,mi.vImage, mi.iDisplayOrder, mi.vHighlightName FROM menu_items as mi WHERE mi.iFoodMenuId IN ($foodItems) AND mi.eStatus='Active' AND mi.eAvailable = 'Yes' $ssql $ssql1 ORDER BY iDisplayOrder ASC";
            $sqlf = "SELECT eRecommended,iMenuItemId,iFoodMenuId,vItemType_" . $vLanguage . " as vItemType,vItemDesc_" . $vLanguage . " as vItemDesc,fPrice,eFoodType,fOfferAmt,vImage,iDisplayOrder,vHighlightName FROM menu_items WHERE iFoodMenuId IN ($foodItems) AND eStatus='Active' AND eAvailable = 'Yes' $ssql $ssql1 ORDER BY iDisplayOrder ASC";
            $dbItemData = $obj->MySQLSelect($sqlf);
            //echo "<pre>";print_r($dbItemData);die;
            for ($d = 0; $d < count($dbItemData); $d++) {
                //Added By HJ On 17-10-2019 For Get Highlight Label Value Start
                $vHighlightNameLBL = $dbItemData[$d]['vHighlightName'];
                if (isset($languageLabelsArr[$dbItemData[$d]['vHighlightName']]) && $dbItemData[$d]['vHighlightName'] != "" && $dbItemData[$d]['vHighlightName'] != null) {
                    $vHighlightNameLBL = $languageLabelsArr[$dbItemData[$d]['vHighlightName']];
                }
                $dbItemData[$d]['vHighlightNameLBL'] = $vHighlightNameLBL;
                //Added By HJ On 17-10-2019 For Get Highlight Label Value End
                $foodItemArr[$dbItemData[$d]['iFoodMenuId']][] = $dbItemData[$d];
                $menuItemArr[] = $dbItemData[$d]['iMenuItemId'];
            }
        }
        //echo "<pre>";print_r($menuItemArr);die;
        if (count($menuItemArr) > 0) {
            $itemIds = implode(",", $menuItemArr);
            $topingArr = GetMenuItemOptionsTopping($itemIds, $currencySymbol, $Ratio, $vLanguage);
            //$itemPriceArr = getMenuItemPriceByCompanyOffer($itemIds, $iCompanyId, 1, $iUserId, "Display", "", "");
            //echo "<pre>";print_r($itemPriceArr);die;
            //$customerTopingArr = getMenuCustomeAllToppings($itemIds, $currencySymbol, $Ratio, $vLanguage, 0);
        }
        $itemimimgUrl = $tconfig["tsite_upload_images_menu_item"];
        for ($i = 0; $i < count($db_food_data); $i++) {
            $iFoodMenuId = $db_food_data[$i]['iFoodMenuId'];
            $vMenu = $db_food_data[$i]['vMenu_' . $vLanguage];
            //$sqlf = "SELECT mi.eRecommended,mi.iMenuItemId,mi.iFoodMenuId,mi.vItemType_" . $vLanguage . " as vItemType,mi.vItemDesc_" . $vLanguage . " as vItemDesc, mi.fPrice, mi.eFoodType, mi.fOfferAmt,mi.vImage, mi.iDisplayOrder, mi.vHighlightName FROM menu_items as mi WHERE mi.iFoodMenuId = '" . $iFoodMenuId . "' AND mi.eStatus='Active' AND mi.eAvailable = 'Yes' $ssql $ssql1 ORDER BY iDisplayOrder ASC";
            //$db_item_data = $obj->MySQLSelect($sqlf);
            if (isset($foodItemArr[$iFoodMenuId])) {
                $db_item_data = $foodItemArr[$iFoodMenuId];
                for ($j = 0; $j < count($db_item_data); $j++) {
                    $db_item_data[$j]['vCategoryName'] = '';
                    if (!empty($vMenu)) {
                        $db_item_data[$j]['vCategoryName'] = $vMenu;
                    }
                    $iMenuItemId = $db_item_data[$j]['iMenuItemId'];
                    $MenuItemPriceArr = getMenuItemPriceByCompanyOffer($iMenuItemId, $iCompanyId, 1, $iUserId, "Display", "", "", $iServiceId);
                    $fPrice = round($MenuItemPriceArr['fPrice'] * $Ratio, 2);
                    $fOfferAmt = round($MenuItemPriceArr['fOfferAmt'], 2);
                    $db_item_data[$j]['fOfferAmt'] = $fOfferAmt;
                    $db_item_data[$j]['fPrice'] = round($db_item_data[$j]['fPrice'] * $Ratio, 2);
                    if ($fOfferAmt > 0) {
                        $fDiscountPrice = round($MenuItemPriceArr['fPrice'] * $Ratio, 2);
                        $StrikeoutPrice = round($MenuItemPriceArr['fOriginalPrice'] * $Ratio, 2);
                        $db_item_data[$j]['StrikeoutPrice'] = $currencySymbol . " " . formatNum($StrikeoutPrice);
                        $db_item_data[$j]['fDiscountPrice'] = formatNum($fDiscountPrice);
                        $db_item_data[$j]['fDiscountPricewithsymbol'] = $currencySymbol . " " . formatNum($fDiscountPrice);
                        $db_item_data[$j]['currencySymbol'] = $currencySymbol;
                    } else {
                        $db_item_data[$j]['StrikeoutPrice'] = $currencySymbol . " " . formatNum($fPrice);
                        $db_item_data[$j]['fDiscountPrice'] = formatNum($fPrice);
                        $db_item_data[$j]['fDiscountPricewithsymbol'] = $currencySymbol . " " . formatNum($fPrice);
                        $db_item_data[$j]['currencySymbol'] = $currencySymbol;
                    }

                    $itemimgpth = $tconfig["tsite_upload_images_menu_item_path"] . '/' . $db_item_data[$j]['vImage'];
                    if ($db_item_data[$j]['vImage'] != "" && file_exists($itemimgpth)) {
                        $db_item_data[$j]['vImageName'] = $db_item_data[$j]['vImage'];
                        $db_item_data[$j]['vImage'] = $itemimimgUrl . '/' . $db_item_data[$j]['vImage'];
                    } else {
                        $db_item_data[$j]['vImageName'] = '';
                        $db_item_data[$j]['vImage'] = $itemimimgUrl . '/sample_image.png';
                    }

                    //$MenuItemOptionToppingArr = GetMenuItemOptionsTopping($iMenuItemId, $currencySymbol, $Ratio, $vLanguage, $iServiceId);
                    $MenuItemOptionToppingArr = $customeToppings = array();
                    if (isset($topingArr[$iMenuItemId])) {
                        $MenuItemOptionToppingArr = $topingArr[$iMenuItemId];
                    }
                    $db_item_data[$j]['MenuItemOptionToppingArr'] = $MenuItemOptionToppingArr;
                    array_push($MenuItemsDataArr, $db_item_data[$j]);
                }
            }
        }
    }

    /*   $sqlf = "SELECT mi.iMenuItemId,mi.iFoodMenuId,mi.vItemType_".$vLanguage." as vItemType,mi.vItemDesc_".$vLanguage." as vItemDesc,mi.fPrice,mi.eFoodType,mi.fOfferAmt,mi.vImage,mi.iDisplayOrder FROM menu_items as mi LEFT JOIN food_menu as f on f.iFoodMenuId=mi.iFoodMenuId LEFT JOIN company as c on c.iCompanyId=f.iCompanyId WHERE mi.eStatus='Active' AND mi.eAvailable = 'Yes' AND f.iCompanyId = '".$restaId."'  $ssql ORDER BY RAND()";
      $db_item_data = $obj->MySQLSelect($sqlf);
      for($j=0;$j<count($db_item_data);$j++){
      $fPrice= round($db_item_data[$j]['fPrice']*$Ratio,2);
      $db_item_data[$j]['fPrice'] = formatNum($fPrice);
      if($db_item_data[$j]['vImage'] != ""){
      $db_item_data[$j]['vImage'] = $tconfig["tsite_upload_images_menu_item"]."/".$db_item_data[$j]['vImage'];
      }
      } */

    // $returnArr['Recomendation_Arr'] = $MenuItemsDataArr;
    // echo "<pre>";print_r($returnArr);exit;
    return $MenuItemsDataArr;
}

################# Display Recommended and Best Seller Menu Items#############################
########################## Check Cancel Order Status ########################################

function checkCancelOrderStatus($iOrderId) {
    global $generalobj, $obj;
    $sql = "SELECT iStatusCode from orders WHERE iOrderId ='" . $iOrderId . "'";
    $db_status = $obj->MySQLSelect($sql);
    $iStatusCode = $db_status[0]['iStatusCode'];
    if ($iStatusCode == 8) {
        $returnArr['Action'] = "0";
        $returnArr['message'] = "LBL_TRY_AGAIN_LATER_TXT";
    }

    return $iOrderId;
}

########################## Check Cancel Order Status ########################################

function getServiceCategoryCounts() {
    global $generalobj, $obj;
    $sqlN = "SELECT count(iServiceId) as TotalSerivce FROM service_categories WHERE eStatus='Active'";
    $datar = $obj->MySQLSelect($sqlN);
    $serviceCatCount = $datar[0]['TotalSerivce'];
    return $serviceCatCount;
}

############### Check  User's  SMS Resending Limit and Reset Verification count and date if restriction time is  over  ###################################################################

function CheckUserSmsLimit($iMemberId, $UserType = "Passenger") {
    global $obj, $generalobj, $tconfig, $VERIFICATION_CODE_RESEND_COUNT, $VERIFICATION_CODE_RESEND_TIME_IN_SECONDS, $iServiceId;
    if ($UserType == "Passenger") {
        $tblname = "register_user";
        $fields = 'vVerificationCount,dSendverificationDate,vLang';
        $condfield = 'iUserId';
    } else if ($UserType == "Company") {
        $tblname = "company";
        $fields = 'vVerificationCount,dSendverificationDate,vLang';
        $condfield = 'iCompanyId';
    } else {
        $tblname = "register_driver";
        $fields = 'vVerificationCount,dSendverificationDate,vLang';
        $condfield = 'iDriverId';
    }

    $sql = "select $fields from $tblname where $condfield='" . $iMemberId . "'";
    $db_user = $obj->MySQLSelect($sql);
    $vVerificationCount = $db_user[0]['vVerificationCount'];
    $dSendverificationDate = $db_user[0]['dSendverificationDate'];
    if (($dSendverificationDate < $currentdate) && $dSendverificationDate != "0000-00-00 00:00:00") {
        $updateQuery = "UPDATE $tblname set dSendverificationDate='0000-00-00 00:00:00',vVerificationCount = 0 WHERE $condfield = " . $iMemberId;
        $obj->sql_query($updateQuery);
        $vVerificationCount = 0;
        $dSendverificationDate = "0000-00-00 00:00:00";
    }
    $vLang = $db_user[0]['vLang'];
    if ($vLang == "" || $vLang == NULL) {
        $vLang = get_value('language_master', 'vCode', 'eDefault', 'Yes', '', 'true');
    }

    $languageLabelsArr = getLanguageLabelsArr($vLang, "1", $iServiceId);
    $currentdate = @date("Y-m-d H:i:s");
    $totalMinute = @round(abs(strtotime($dSendverificationDate) - strtotime($currentdate)) / 60);
    $hours = floor($totalMinute / 60); // No. of mins/60 to get the hours and round down
    $mins = $totalMinute % 60; // No. of mins/60 - remainder (modulus) is the minutes
    $LBL_HOURS_TXT = ($hours > 1) ? $languageLabelsArr['LBL_HOURS_TXT'] : $languageLabelsArr['LBL_HOUR_TXT'];

    // $LBL_MINUTES_TXT = ($mins > 1)? $languageLabelsArr['LBL_MINUTES_TXT'] : $languageLabelsArr['LBL_MINUTE'];
    $LBL_MINUTES_TXT = ($mins > 1) ? $languageLabelsArr['LBL_MINS_SMALL'] : $languageLabelsArr['LBL_MINUTE'];
    if ($hours >= 1) {
        $timeDurationDisplay = $hours . " " . $LBL_HOURS_TXT . " " . $mins . " " . $LBL_MINUTES_TXT;
    } else {
        $timeDurationDisplay = $mins . " " . $LBL_MINUTES_TXT;
    }

    $message = $languageLabelsArr['LBL_SMS_MAXIMAM_LIMIT_TXT'] . " " . $timeDurationDisplay;
    if (($dSendverificationDate < $currentdate) && $dSendverificationDate != "0000-00-00 00:00:00") {
        $updateQuery = "UPDATE $tblname set dSendverificationDate='0000-00-00 00:00:00',vVerificationCount = 0 WHERE $condfield = " . $iMemberId;
        $obj->sql_query($updateQuery);
        $vVerificationCount = 0;
        $dSendverificationDate = "0000-00-00 00:00:00";
    }

    if ($vVerificationCount == $VERIFICATION_CODE_RESEND_COUNT) {
        $returnArr['Action'] = "0";
        $returnArr['message'] = $message;
        setDataResponse($returnArr);
    }

    return $iMemberId;
}

############### Check  User's  SMS Resending Limit and Reset Verification count and date if restriction time is  over  ###################################################################
############################# Update  User's  SMS Resending Limit and Rest Verification count and date  ###################################################################

function UpdateUserSmsLimit($iMemberId, $UserType = "Passenger") {
    global $obj, $generalobj, $tconfig, $VERIFICATION_CODE_RESEND_COUNT, $VERIFICATION_CODE_RESEND_COUNT_RESTRICTION;
    if ($UserType == "Passenger") {
        $tblname = "register_user";
        $fields = 'vVerificationCount,dSendverificationDate';
        $condfield = 'iUserId';
    } else if ($UserType == "Company") {
        $tblname = "company";
        $fields = 'vVerificationCount,dSendverificationDate';
        $condfield = 'iCompanyId';
    } else {
        $tblname = "register_driver";
        $fields = 'vVerificationCount,dSendverificationDate';
        $condfield = 'iDriverId';
    }

    $sql = "select $fields from $tblname where $condfield='" . $iMemberId . "'";
    $db_user = $obj->MySQLSelect($sql);
    $vVerificationCount = $db_user[0]['vVerificationCount'];
    $dSendverificationDate = $db_user[0]['dSendverificationDate'];
    $currentdate = @date("Y-m-d H:i:s");
    $checklastcount = $VERIFICATION_CODE_RESEND_COUNT - 1;
    if ($vVerificationCount == $checklastcount) {
        $minutes = $VERIFICATION_CODE_RESEND_COUNT_RESTRICTION;
        $expire_stamp = date('Y-m-d H:i:s', strtotime("+" . $minutes . " minute"));
        $updateQuery = "UPDATE $tblname set dSendverificationDate='" . $expire_stamp . "',vVerificationCount = vVerificationCount+1 WHERE $condfield = " . $iMemberId;
        $obj->sql_query($updateQuery);
    } else {
        $vVerificationCount = $vVerificationCount + 1;
        if ($vVerificationCount > $VERIFICATION_CODE_RESEND_COUNT) {
            $vVerificationCount = $VERIFICATION_CODE_RESEND_COUNT;
        }

        $updateQuery = "UPDATE $tblname set vVerificationCount = '" . $vVerificationCount . "' WHERE $condfield = " . $iMemberId;
        $obj->sql_query($updateQuery);
    }

    return $iMemberId;
}

############################# Update  User's  SMS Resending Limit and Rest Verification count and date  ###################################################################
############### Check  User's  SMS Resending Limit and Reset Verification count and date if restriction time is  over  For Emergency Contact ###################################################################

function CheckUserSmsLimitForEmergency($iMemberId, $UserType = "Passenger") {
    global $obj, $generalobj, $tconfig, $VERIFICATION_CODE_RESEND_COUNT_EMERGENCY, $VERIFICATION_CODE_RESEND_TIME_IN_SECONDS_EMERGENCY, $iServiceId;
    if ($UserType == "Passenger") {
        $tblname = "register_user";
        $fields = 'vVerificationCountEmergency,dSendverificationDateEmergency,vLang';
        $condfield = 'iUserId';
    } else {
        $tblname = "register_driver";
        $fields = 'vVerificationCountEmergency,dSendverificationDateEmergency,vLang';
        $condfield = 'iDriverId';
    }
    $sql = "SELECT * FROM user_emergency_contact WHERE iUserId = '" . $iMemberId . "' AND eUserType='" . $UserType . "'";
    $dataArr = $obj->MySQLSelect($sql);
    if (count($dataArr) == 0) {
        $returnArr['Action'] = "0";
        $returnArr['message'] = "LBL_ADD_EME_CONTACTS";
        $returnArr['message1'] = "ContactError";
        setDataResponse($returnArr);
    }

    $sql = "select $fields from $tblname where $condfield='" . $iMemberId . "'";
    $db_user = $obj->MySQLSelect($sql);
    $vVerificationCount = $db_user[0]['vVerificationCountEmergency'];
    $dSendverificationDate = $db_user[0]['dSendverificationDateEmergency'];
    $vLang = $db_user[0]['vLang'];
    if ($vLang == "" || $vLang == NULL) {
        $vLang = get_value('language_master', 'vCode', 'eDefault', 'Yes', '', 'true');
    }

    $languageLabelsArr = getLanguageLabelsArr($vLang, "1", $iServiceId);
    $currentdate = @date("Y-m-d H:i:s");
    $totalMinute = @round(abs(strtotime($dSendverificationDate) - strtotime($currentdate)) / 60);
    $totalSeconds = abs(strtotime($dSendverificationDate) - strtotime($currentdate));
    $hours = floor($totalMinute / 60); // No. of mins/60 to get the hours and round down
    $mins = $totalMinute % 60; // No. of mins/60 - remainder (modulus) is the minutes
    $LBL_HOURS_TXT = ($hours > 1) ? $languageLabelsArr['LBL_HOURS_TXT'] : $languageLabelsArr['LBL_HOUR_TXT'];

    // $LBL_MINUTES_TXT = ($mins > 1)? $languageLabelsArr['LBL_MINUTES_TXT'] : $languageLabelsArr['LBL_MINUTE'];
    $LBL_MINUTES_TXT = ($mins > 1) ? $languageLabelsArr['LBL_MINS_SMALL'] : $languageLabelsArr['LBL_MINUTE'];
    if ($hours >= 1) {
        $timeDurationDisplay = $hours . " " . $LBL_HOURS_TXT . " " . $mins . " " . $LBL_MINUTES_TXT;
    } else {
        if ($mins > 1) {
            $timeDurationDisplay = $mins . " " . $LBL_MINUTES_TXT;
        } else {
            $timeDurationDisplay = $totalSeconds . " " . $languageLabelsArr['LBL_SECONDS_TXT'];
        }
    }

    $message = $languageLabelsArr['LBL_SMS_MAXIMAM_LIMIT_TXT'] . " " . $timeDurationDisplay;
    if (($dSendverificationDate < $currentdate) && $dSendverificationDate != "0000-00-00 00:00:00") {
        $updateQuery = "UPDATE $tblname set dSendverificationDateEmergency='0000-00-00 00:00:00',vVerificationCountEmergency = 0 WHERE $condfield = " . $iMemberId;
        $obj->sql_query($updateQuery);
        $vVerificationCount = 0;
        $dSendverificationDate = "0000-00-00 00:00:00";
    }

    $totalSeconds1 = abs(strtotime($dSendverificationDate) - strtotime($currentdate));
    if ($totalSeconds1 < $VERIFICATION_CODE_RESEND_TIME_IN_SECONDS_EMERGENCY) {
        $returnArr['Action'] = "0";
        $returnArr['message'] = $message;
        $returnArr['message1'] = "SmsError";
        setDataResponse($returnArr);
    }

    if ($vVerificationCount == $VERIFICATION_CODE_RESEND_COUNT_EMERGENCY) {
        $returnArr['Action'] = "0";
        $returnArr['message'] = $message;
        $returnArr['message1'] = "SmsError";
        setDataResponse($returnArr);
    }

    return $iMemberId;
}

############### Check  User's  SMS Resending Limit and Reset Verification count and date if restriction time is  over  For Emergency Contact###################################################################
############################# Update  User's  SMS Resending Limit and Rest Verification count and date For Emergency Contact###################################################################

function UpdateUserSmsLimitForEmergency($iMemberId, $UserType = "Passenger") {
    global $obj, $generalobj, $tconfig, $VERIFICATION_CODE_RESEND_COUNT_EMERGENCY, $VERIFICATION_CODE_RESEND_COUNT_RESTRICTION_EMERGENCY;
    if ($UserType == "Passenger") {
        $tblname = "register_user";
        $fields = 'vVerificationCountEmergency,dSendverificationDateEmergency';
        $condfield = 'iUserId';
    } else {
        $tblname = "register_driver";
        $fields = 'vVerificationCountEmergency,dSendverificationDateEmergency';
        $condfield = 'iDriverId';
    }

    $sql = "select $fields from $tblname where $condfield='" . $iMemberId . "'";
    $db_user = $obj->MySQLSelect($sql);
    $vVerificationCount = $db_user[0]['vVerificationCountEmergency'];
    $dSendverificationDate = $db_user[0]['dSendverificationDateEmergency'];
    $currentdate = @date("Y-m-d H:i:s");
    $checklastcount = $VERIFICATION_CODE_RESEND_COUNT_EMERGENCY - 1;
    if ($vVerificationCount == $checklastcount) {
        $minutes = $VERIFICATION_CODE_RESEND_COUNT_RESTRICTION_EMERGENCY;
        $expire_stamp = date('Y-m-d H:i:s', strtotime("+" . $minutes . " minute"));
        $updateQuery = "UPDATE $tblname set dSendverificationDateEmergency='" . $expire_stamp . "',vVerificationCountEmergency = vVerificationCountEmergency+1 WHERE $condfield = " . $iMemberId;
        $obj->sql_query($updateQuery);
    } else {
        $vVerificationCount = $vVerificationCount + 1;
        if ($vVerificationCount > $VERIFICATION_CODE_RESEND_COUNT_EMERGENCY) {
            $vVerificationCount = $VERIFICATION_CODE_RESEND_COUNT_EMERGENCY;
        }

        $updateQuery = "UPDATE $tblname set vVerificationCountEmergency = '" . $vVerificationCount . "' WHERE $condfield = " . $iMemberId;
        $obj->sql_query($updateQuery);
    }

    return $iMemberId;
}

############################# Update  User's  SMS Resending Limit and Rest Verification count and date For Emergency Contact ###################################################################
############################################################## Get Socket URL ###############################################################################################################

function getSocketURL() {
    global $obj, $generalobj, $tconfig, $http;
    if ($_SERVER["HTTP_HOST"] == "192.168.1.131" || $_SERVER["HTTP_HOST"] == "www.mobileappsdemo.com" || $_SERVER["HTTP_HOST"] == "cubejekbeta.bbcsproducts.com") {

        // $url = "http://142.93.244.42:8000/socketcluster/";
        $url = $http . $tconfig["tsite_sc_host"] . ":" . $tconfig["tsite_host_sc_port"] . $tconfig["tsite_host_sc_path"];
    } else {
        $url = $http . $_SERVER["HTTP_HOST"] . ":" . $tconfig["tsite_host_sc_port"] . $tconfig["tsite_host_sc_path"];
    }

    return $url;
}

############################################################## Get Socket URL ###############################################################################################################
############################################################## Get publishEventMessage ###############################################################################################################

function publishEventMessage_duplicate($channelName, $message) {
    global $tconfig, $ENABLE_SOCKET_CLUSTER;
    if ($ENABLE_SOCKET_CLUSTER == "Yes") {
        /*  old one 
          $optionsOrUri = ['secure' => false, 'host' => $tconfig['tsite_sc_host'], 'port' => $tconfig['tsite_host_sc_port'], 'path' => $tconfig['tsite_host_sc_path']];
          $websocket = SocketClusterWebSocket::factory($optionsOrUri);
          $socket = new SocketClusterSocketCluster($websocket);
          $dataCHK = $socket->publish($channelName, $message);
          $websocket->close();
         */ /*  new  one from cubjeckdev / app_common_functions.php  file code shift by PM 22-2-2019  */
        $optionsOrUri = ['secure' => false, 'host' => $tconfig['tsite_sc_host'], 'port' => $tconfig['tsite_host_sc_port'], 'path' => $tconfig['tsite_host_sc_path']];
        $websocket = \SocketCluster\WebSocket::factory($optionsOrUri);
        $socket = new \SocketCluster\SocketCluster($websocket);
        $dataCHK = $socket->publish($channelName, $message);
        $websocket->close();
    }

    return true;
}

############################################################## Get publishEventMessage ###############################################################################################################
########################### General Icon Banner #############################################################

function getGeneralVarAll_IconBanner() {
    global $obj, $APP_TYPE;
    //$listField = $obj->MySQLGetFieldsQuery("setting");
    $ssql = "";
    /* if(ENABLE_RENTAL_OPTION == 'No') {
      $ssql .= " AND eRentalType = 'No' ";
      } */
    $wri_usql = "SELECT iSettingId,vName,TRIM(vValue) as vValue,eImageType,eRentalType FROM configurations_cubejek where 1" . $ssql;
    $wri_ures = $obj->MySQLSelect($wri_usql);

    return $wri_ures;
}

########################### General Icon Banner #############################################################
########################### Change Driver's Selected Vehicle  to  0 if Ride Delivery Feature Enable ##############################################

function ChangeDriverVehicleRideDeliveryFeatureDisable($iDriverId) {
    global $obj, $APP_TYPE, $generalobj;
    $eShowRideVehicles = "Yes";
    $eShowDeliveryVehicles = "Yes";

    $sqldata = "SELECT iTripId FROM `trips` WHERE ( iActive='On Going Trip' OR iActive='Active' ) AND iDriverId='" . $iDriverId . "'";
    $TripData = $obj->MySQLSelect($sqldata);
    $TripRunCount = count($TripData);

    if ($APP_TYPE == "Ride-Delivery-UberX" && $TripRunCount == 0) {
        $RideDeliveryIconArr = getGeneralVarAll_IconBanner();
        for ($i = 0; $i < count($RideDeliveryIconArr); $i++) {
            $vName = $RideDeliveryIconArr[$i]['vName'];
            $vValue = $RideDeliveryIconArr[$i]['vValue'];
            $$vName = $vValue;
            $Data[0][$vName] = $$vName;
        }
        if ($Data[0]['RIDE_SHOW_SELECTION'] == 'None' && $Data[0]['RENTAL_SHOW_SELECTION'] == 'None' && $Data[0]['MOTO_RIDE_SHOW_SELECTION'] == 'None' && $Data[0]['MOTO_RENTAL_SHOW_SELECTION'] == 'None') {
            $eShowRideVehicles = "No";
            $sql = "SELECT eType FROM `driver_vehicle` as dv LEFT JOIN register_driver as rd ON dv.iDriverVehicleId=rd.iDriverVehicleId WHERE rd.iDriverId='" . $iDriverId . "'";
            $DriverVehicleType = $obj->MySQLSelect($sql);
            $eType = $DriverVehicleType[0]['eType'];
            if ($eType == "Ride") {
                $sql = "UPDATE register_driver set iDriverVehicleId='0' WHERE iDriverId='" . $iDriverId . "'";
                $obj->sql_query($sql);
            }
        }
        if ($Data[0]['DELIVERY_SHOW_SELECTION'] == 'None' && $Data[0]['MOTO_DELIVERY_SHOW_SELECTION'] == 'None') {
            $eShowDeliveryVehicles = "No";
            $sql = "SELECT eType FROM `driver_vehicle` as dv LEFT JOIN register_driver as rd ON dv.iDriverVehicleId=rd.iDriverVehicleId WHERE rd.iDriverId='" . $iDriverId . "'";
            $DriverVehicleType = $obj->MySQLSelect($sql);
            $eType = $DriverVehicleType[0]['eType'];
            if ($eType == "Delivery") {
                $sql = "UPDATE register_driver set iDriverVehicleId='0' WHERE iDriverId='" . $iDriverId . "'";
                $obj->sql_query($sql);
            }
        }
    }

    return $iDriverId;
}

########################### Change Driver's Selected Vehicle  to  0 if Ride Delivery Feature Enable ##############################################
############################################################## Display Trip Charge To Driver For UberX Trip ####################################################################################

function DisplayTripChargeForUberX($TripID) {
    global $obj, $generalobj, $tconfig;

    $sql_vehicle_category_table_name = $generalobj->getVehicleCategoryTblName();

    $returnArr = array();
    $where = " iTripId = '" . $TripID . "'";

    $sql = "SELECT * from trips WHERE iTripId = '" . $TripID . "'";
    $tripData = $obj->MySQLSelect($sql);
    // echo "<pre>"; print_r($tripData); die;
    $eType = $tripData[0]['eType'];
    if ($eType == "UberX") {
        $iDriverVehicleId = $tripData[0]['iDriverVehicleId'];
        $iVehicleTypeId = $tripData[0]['iVehicleTypeId'];
        $fVisitFee = $tripData[0]['fVisitFee'];
        $startDate = $tripData[0]['tStartDate'];
        $endDateOfTrip = $tripData[0]['tEndDate'];
        $iQty = $tripData[0]['iQty'];
        $destination_lat = $tripData[0]['tEndLat'];
        $destination_lon = $tripData[0]['tEndLong'];
        //$endDateOfTrip=@date("Y-m-d H:i:s");
        /* $iVehicleCategoryId=get_value('vehicle_type', 'iVehicleCategoryId', 'iVehicleTypeId',$iVehicleTypeId,'','true');
          $iParentId = get_value($sql_vehicle_category_table_name, 'iParentId', 'iVehicleCategoryId', $iVehicleCategoryId,'','true'); */
        $sql = "SELECT vc.iParentId from " . $sql_vehicle_category_table_name . " as vc LEFT JOIN vehicle_type as vt ON vc.iVehicleCategoryId=vt.iVehicleCategoryId WHERE vt.iVehicleTypeId = '" . $iVehicleTypeId . "'";
        $VehicleCategoryData = $obj->MySQLSelect($sql);
        $iParentId = $VehicleCategoryData[0]['iParentId'];
        if ($iParentId == 0) {
            $ePriceType = get_value($sql_vehicle_category_table_name, 'ePriceType', 'iVehicleCategoryId', $iVehicleCategoryId, '', 'true');
        } else {
            $ePriceType = get_value($sql_vehicle_category_table_name, 'ePriceType', 'iVehicleCategoryId', $iParentId, '', 'true');
        }
        //$ePriceType=get_value($sql_vehicle_category_table_name, 'ePriceType', 'iVehicleCategoryId',$iVehicleCategoryId,'','true');
        $ALLOW_SERVICE_PROVIDER_AMOUNT = $ePriceType == "Provider" ? "Yes" : "No";

        if ($tripData[0]['eFareType'] == 'Hourly') {
            $sql22 = "SELECT * FROM `trip_times` WHERE iTripId='$TripID'";
            $db_tripTimes = $obj->MySQLSelect($sql22);

            $totalSec = 0;
            $iTripTimeId = '';
            foreach ($db_tripTimes as $dtT) {
                if ($dtT['dPauseTime'] != '' && $dtT['dPauseTime'] != '0000-00-00 00:00:00') {
                    $totalSec += strtotime($dtT['dPauseTime']) - strtotime($dtT['dResumeTime']);
                }
            }
            $totalTimeInMinutes_trip = @round(abs($totalSec) / 60, 2);
        } else {
            $totalTimeInMinutes_trip = @round(abs(strtotime($startDate) - strtotime($endDateOfTrip)) / 60, 2);
        }
        $totalHour = $totalTimeInMinutes_trip / 60;
        $tripDistance = calcluateTripDistance($TripID);
        $sourcePointLatitude = $tripData[0]['tStartLat'];
        $sourcePointLongitude = $tripData[0]['tStartLong'];
        if ($totalTimeInMinutes_trip <= 1) {
            $FinalDistance = $tripDistance;
        } else {
            $FinalDistance = checkDistanceWithGoogleDirections($tripDistance, $sourcePointLatitude, $sourcePointLongitude, $destination_lat, $destination_lon);
        }
        $tripDistance = $FinalDistance;
        $fPickUpPrice = $tripData[0]['fPickUpPrice'];
        $fNightPrice = $tripData[0]['fNightPrice'];
        $eFareType = get_value('trips', 'eFareType', 'iTripId', $TripID, '', 'true');
        $surgePrice = $fPickUpPrice > 1 ? $fPickUpPrice : ($fNightPrice > 1 ? $fNightPrice : 1);
        $fAmount = 0;
        $Fare_data = getVehicleFareConfig("vehicle_type", $iVehicleTypeId);
        // echo "<pre>"; print_r($tripData); die;
        $fPricePerKM = getVehicleCountryUnit_PricePerKm($iVehicleTypeId, $Fare_data[0]['fPricePerKM']);
        /* $Minute_Fare = round($Fare_data[0]['fPricePerMin'] * $totalTimeInMinutes_trip * $surgePrice,2);
          $Distance_Fare = round($fPricePerKM * $tripDistance * $surgePrice,2);
          $iBaseFare = round($Fare_data[0]['iBaseFare'] * $surgePrice,2);
          $total_fare = $iBaseFare + $Minute_Fare + $Distance_Fare; */
        $Minute_Fare = $Fare_data[0]['fPricePerMin'] * $totalTimeInMinutes_trip;
        $Distance_Fare = $fPricePerKM * $tripDistance;
        $iBaseFare = $Fare_data[0]['iBaseFare'];
        $total_fare = $iBaseFare + $Minute_Fare + $Distance_Fare;
        $fSurgePriceDiff = (($total_fare * $surgePrice) - $total_fare);
        $total_fare = $total_fare + $fSurgePriceDiff;

        $iMinFare = round($Fare_data[0]['iMinFare'] * $surgePrice, 2);
        if ($iMinFare > $total_fare) {
            $total_fare = $iMinFare;
        }
        $fMinHour = $Fare_data[0]['fMinHour'];
        if ($totalHour > $fMinHour) {
            $miniminutes = $fMinHour * 60;
            $TripTimehours = $totalTimeInMinutes_trip / 60;
            $tothours = intval($TripTimehours);
            $extrahours = $TripTimehours - $tothours;
            $extraminutes = $extrahours * 60;
        }
        if ($ALLOW_SERVICE_PROVIDER_AMOUNT == "Yes") {

            $sqlServicePro = "SELECT * FROM `service_pro_amount` WHERE iDriverVehicleId='" . $iDriverVehicleId . "' AND iVehicleTypeId='" . $iVehicleTypeId . "'";
            $serviceProData = $obj->MySQLSelect($sqlServicePro);

            if (count($serviceProData) > 0) {
                $fAmount = $serviceProData[0]['fAmount'];
                if ($eFareType == "Fixed") {
                    $fAmount = $fAmount * $iQty;
                } else if ($eFareType == "Hourly") {
                    if ($totalHour > $fMinHour) {
                        $fTimeSlot = $Fare_data[0]['fTimeSlot'];
                        $pricetimeslot = 60 / $fTimeSlot;
                        $pricepertimeslot = $fAmount / $pricetimeslot;
                        $fTimeSlotPrice = $pricepertimeslot;
                        $extratimeslot = ceil($extraminutes / $fTimeSlot);
                        $extraprice = $extratimeslot * $fTimeSlotPrice;
                        $fAmount = ($fAmount * $tothours) + $extraprice;
                    } else {
                        $fAmount = $fAmount * $fMinHour;
                    }
                    //$fAmount = $fAmount * $totalHour;
                } else {
                    $fAmount = $total_fare;
                }
            } else {
                if ($eFareType == "Fixed") {
                    $fAmount = round($Fare_data[0]['fFixedFare'] * $iQty, 2);
                } else if ($eFareType == "Hourly") {
                    $extraprice = 0;
                    if ($totalHour > $fMinHour) {
                        $fTimeSlot = $Fare_data[0]['fTimeSlot'];
                        if ($fTimeSlot > 0) {
                            $pricetimeslot = 60 / $fTimeSlot;
                            $pricepertimeslot = $Fare_data[0]['fPricePerHour'] / $pricetimeslot;
                            $fTimeSlotPrice = $pricepertimeslot;
                            //$fTimeSlotPrice = $Fare_data[0]['fTimeSlotPrice'];
                            $extratimeslot = ceil($extraminutes / $fTimeSlot);
                            $extraprice = $extratimeslot * $fTimeSlotPrice;
                        }
                        $fAmount = round((($Fare_data[0]['fPricePerHour'] * $tothours) + $extraprice), 2);
                    } else {
                        $fAmount = round($Fare_data[0]['fPricePerHour'] * $fMinHour, 2);
                        // $fAmount = round($Fare_data[0]['fPricePerHour'] * $totalHour,2);
                    }
                } else {
                    $fAmount = $total_fare;
                }
            }
        } else {
            if ($eFareType == "Fixed") {
                $fAmount = round($Fare_data[0]['fFixedFare'] * $iQty, 2);
            } else if ($eFareType == "Hourly") {
                if ($totalHour > $fMinHour) {
                    $fTimeSlot = $Fare_data[0]['fTimeSlot'];
                    $pricetimeslot = 60 / $fTimeSlot;
                    $pricepertimeslot = $Fare_data[0]['fPricePerHour'] / $pricetimeslot;
                    $fTimeSlotPrice = $pricepertimeslot;
                    //$fTimeSlotPrice = $Fare_data[0]['fTimeSlotPrice'];
                    $extratimeslot = ceil($extraminutes / $fTimeSlot);
                    $extraprice = $extratimeslot * $fTimeSlotPrice;
                    $fAmount = round((($Fare_data[0]['fPricePerHour'] * $tothours) + $extraprice), 2);
                } else {
                    $fAmount = round($Fare_data[0]['fPricePerHour'] * $fMinHour, 2);
                    //$fAmount = round($Fare_data[0]['fPricePerHour'] * $totalHour,2);
                }
            } else {
                $fAmount = $total_fare;
            }
        }

        $final_display_charge = $fAmount + $fVisitFee;
        $returnArr['Action'] = "1";
        /* $vCurrencyDriver=get_value('register_driver', 'vCurrencyDriver', 'iDriverId', $tripData[0]['iDriverId'],'','true');
          $currencySymbolRationDriver = get_value('currency', 'vSymbol,Ratio', 'vName', $vCurrencyDriver);
          $returnArr['message']=$currencySymbolRationDriver[0]['vSymbol']." ".number_format(round($final_display_charge * $currencySymbolRationDriver[0]['Ratio'],1),2); */
        //$currencySymbol = get_value('currency', 'vSymbol', 'eDefault', 'Yes','',true);
        $vCurrencyDriver = get_value('register_driver', 'vCurrencyDriver', 'iDriverId', $tripData[0]['iDriverId'], '', 'true');
        $currencySymbolRationDriver = get_value('currency', 'vSymbol,Ratio', 'vName', $vCurrencyDriver);
        $currencySymbol = $currencySymbolRationDriver[0]['vSymbol'];
        $currencyRationDriver = $currencySymbolRationDriver[0]['Ratio'];
        $final_display_charge = $final_display_charge * $currencyRationDriver;
        $final_display_charge = round($final_display_charge, 2);
        //$final_display_charge = formatNum($final_display_charge);
        $returnArr['TotalFareUberX'] = $currencySymbol . ' ' . formatNum($final_display_charge);
        $returnArr['TotalFareUberXValue'] = $final_display_charge;
        $returnArr['UberXFareCurrencySymbol'] = $currencySymbol;
    } else {
        $returnArr['TotalFareUberX'] = "";
        $returnArr['TotalFareUberXValue'] = "";
        $returnArr['UberXFareCurrencySymbol'] = "";
    }
    return $returnArr;
}

############################################################## Display Trip Charge To Driver For UberX Trip ####################################################################################
########################### GenerateCustomer App Payment Method Wise #############################################################

/* function GenerateCustomer($Data) {
  global $generalobj, $obj, $STRIPE_SECRET_KEY, $STRIPE_PUBLISH_KEY, $gateway, $BRAINTREE_TOKEN_KEY, $BRAINTREE_ENVIRONMENT, $BRAINTREE_MERCHANT_ID, $BRAINTREE_PUBLIC_KEY, $BRAINTREE_PRIVATE_KEY, $BRAINTREE_CHARGE_AMOUNT, $PAYMAYA_API_URL, $tconfig, $XENDIT_PUBLIC_KEY, $XENDIT_SECRET_KEY, $APP_PAYMENT_METHOD, $SYSTEM_PAYMENT_ENVIRONMENT; // Stripe,Braintree
  foreach ($Data as $key => $value) {
  //$value = urldecode(stripslashes($value));
  $$key = $value;
  }

  if ($UserType == "Passenger") {
  $tbl_name = "register_user";
  $vEmail = "vEmail";
  $iMemberId = "iUserId";
  $eUserType = "Rider";
  $eMemberType = "Passenger";
  $UserDetailPaymaya = get_value($tbl_name, 'vName,vLastName,vEmail,vPhone,vPhoneCode as phonecode,vPaymayaCustId,' . $vEmail . ' as memberemail', $iMemberId, $iUserId);
  } else {
  $tbl_name = "register_driver";
  $vEmail = "vEmail";
  $iMemberId = "iDriverId";
  $eUserType = "Driver";
  $eMemberType = "Driver";
  $UserDetailPaymaya = get_value($tbl_name, 'vName,vLastName,vEmail,vPhone,vCode as phonecode,vPaymayaCustId,' . $vEmail . ' as memberemail', $iMemberId, $iUserId);
  }

  if ($APP_PAYMENT_METHOD == "Stripe") {
  require_once ('assets/libraries/stripe/config.php');
  require_once ('assets/libraries/stripe/stripe-php-2.1.4/lib/Stripe.php');
  $UserDetail = get_value($tbl_name, 'vStripeCusId,' . $vEmail . ' as memberemail', $iMemberId, $iUserId);
  $vEmail = $UserDetail[0]['memberemail'];
  $vStripeCusId = $UserDetail[0]['vStripeCusId'];

  try {
  if ($vStripeCusId != "") {
  $customer = Stripe_Customer::retrieve($vStripeCusId);
  $sources = $customer->sources;
  $stripeData = $sources->data;

  if (count($stripeData) > 0 && $stripeData[0]['id'] != '') {
  $customer
  ->sources
  ->retrieve($stripeData[0]['id'])->delete();
  }

  $card = $customer
  ->sources
  ->create(array(
  "source" => $vStripeToken
  ));
  } else {
  try {
  $customer = Stripe_Customer::create(array(
  "source" => $vStripeToken,
  "email" => $vEmail
  ));
  $vStripeCusId = $customer->id;
  } catch (Exception $e) {
  $error3 = $e->getMessage();
  $returnArr['Action'] = "0";
  $returnArr['message'] = $error3;
  setDataResponse($returnArr);
  }
  }
  } catch (Exception $e) {
  $errMsg = $e->getMessage();
  if (strpos($errMsg, 'No such customer') !== false) {
  try {
  $customer = Stripe_Customer::create(array(
  "source" => $vStripeToken,
  "email" => $vEmail
  ));
  } catch (Exception $e) {
  $error3 = $e->getMessage();
  $returnArr['Action'] = "0";
  $returnArr['message'] = $error3;

  setDataResponse($returnArr);
  }

  $vStripeCusId = $customer->id;
  } else {
  $returnArr['Action'] = "0";
  $returnArr['message'] = $errMsg;

  setDataResponse($returnArr);
  }
  }

  $where = " $iMemberId = '$iUserId'";
  $updateData['vStripeToken'] = $vStripeToken;
  $updateData['vStripeCusId'] = $vStripeCusId;
  $updateData['vCreditCard'] = $CardNo;

  $id = $obj->MySQLQueryPerform($tbl_name, $updateData, 'update', $where);
  if ($eMemberType == "Passenger") {
  $profileData = getPassengerDetailInfo($iUserId);
  } else {
  $profileData = getDriverDetailInfo($iUserId);
  }

  if ($id > 0) {
  $returnArr['Action'] = "1";
  $returnArr['message'] = $profileData;
  } else {
  $returnArr['Action'] = "0";
  $returnArr['message'] = "LBL_TRY_AGAIN_LATER_TXT";
  }
  } else if ($APP_PAYMENT_METHOD == "Braintree") {
  require_once ('assets/libraries/braintree/lib/Braintree.php');
  $UserDetail = get_value($tbl_name, 'vBrainTreeCustId,vName,vLastName,' . $vEmail . ' as memberemail', $iMemberId, $iUserId);
  $vEmail = $UserDetail[0]['memberemail'];
  $vBrainTreeCustId = $UserDetail[0]['vBrainTreeCustId'];
  $vName = $UserDetail[0]['vName'];
  $vLastName = $UserDetail[0]['vLastName'];

  try {
  if ($vBrainTreeCustId != "") {
  ## Charge First Transaction Amount For existing customer##
  try {
  $charge = $gateway->transaction()
  ->sale(['amount' => $BRAINTREE_CHARGE_AMOUNT, 'paymentMethodNonce' => $paymentMethodNonce, 'customerId' => $vBrainTreeCustId, 'options' => ['storeInVaultOnSuccess' => true,]]);

  $result = $charge->success;
  if ($result == 1) {
  $transaction_id = $charge
  ->transaction->id;
  $creditCardArr = $charge
  ->transaction->creditCard;
  $paypalArr = $charge
  ->transaction->paypal;
  $payerEmail = $paypalArr['payerEmail'];
  if ($payerEmail != "") {
  $vBrainTreeCustEmail = $payerEmail;
  $vBrainTreeToken = $paypalArr['token'];
  $CardNo = "";
  $message1 = "LBL_SUCESS_ADD_PAYPAL_BRAINTREE_TXT";
  } else {
  $vBrainTreeCustEmail = "";
  $vBrainTreeToken = $creditCardArr['token'];
  $CardNo = "XXXXXXXXXXXX" . $creditCardArr['last4'];
  $message1 = "LBL_SUCESS_ADD_BRAINTREE_TXT";
  }
  $WalletId = $generalobj->InsertIntoUserWallet($iUserId, $eUserType, $BRAINTREE_CHARGE_AMOUNT, 'Credit', 0, 'Deposit', '#LBL_AMOUNT_CREDIT#', 'Unsettelled', Date('Y-m-d H:i:s'));

  $braintree_arr['BRAINTREE_TOKEN_KEY'] = $BRAINTREE_TOKEN_KEY;
  $braintree_arr['BRAINTREE_ENVIRONMENT'] = $BRAINTREE_ENVIRONMENT;
  $braintree_arr['BRAINTREE_MERCHANT_ID'] = $BRAINTREE_MERCHANT_ID;
  $braintree_arr['BRAINTREE_PUBLIC_KEY'] = $BRAINTREE_PUBLIC_KEY;
  $braintree_arr['BRAINTREE_PRIVATE_KEY'] = $BRAINTREE_PRIVATE_KEY;
  $braintree_arr['BRAINTREE_CHARGE_AMOUNT'] = $BRAINTREE_CHARGE_AMOUNT;
  $tPaymentDetails = json_encode($braintree_arr, JSON_UNESCAPED_UNICODE);

  $pay_data['tPaymentUserID'] = $transaction_id;
  $pay_data['vPaymentUserStatus'] = "approved";
  $pay_data['iUserWalletId'] = $WalletId;
  $pay_data['iAmountUser'] = $BRAINTREE_CHARGE_AMOUNT;
  $pay_data['tPaymentDetails'] = $tPaymentDetails;
  $pay_data['vPaymentMode'] = $SYSTEM_PAYMENT_ENVIRONMENT;
  $pay_data['vPaymentMethod'] = $APP_PAYMENT_METHOD;
  $pay_data['eEvent'] = "Wallet";
  $pay_data['iUserId'] = $iUserId;
  $pay_data['eUserType'] = $UserType;
  $paymentid = $obj->MySQLQueryPerform("payments", $pay_data, 'insert');
  } else {
  $returnArr['Action'] = "0";
  $returnArr['message'] = "LBL_TRY_AGAIN_LATER_TXT";
  setDataResponse($returnArr);
  }
  } catch (Exception $e) {
  $error3 = $e->getMessage();
  $returnArr['Action'] = "0";
  $returnArr['message'] = $error3;
  setDataResponse($returnArr);
  }
  ## Charge First Transaction Amount For existing customer##
  } else {
  try {
  $customer = $gateway->customer()
  ->create(['firstName' => $vName, 'lastName' => $vLastName, 'email' => $vEmail,]);
  $vBrainTreeCustId = $customer
  ->customer->id;
  ## Charge First Transaction Amount ##
  try {
  $charge = $gateway->transaction()
  ->sale(['amount' => $BRAINTREE_CHARGE_AMOUNT, 'paymentMethodNonce' => $paymentMethodNonce, 'customerId' => $vBrainTreeCustId, 'options' => ['storeInVaultOnSuccess' => true,]]);

  $result = $charge->success;
  if ($result == 1) {
  $transaction_id = $charge
  ->transaction->id;
  $creditCardArr = $charge
  ->transaction->creditCard;
  $paypalArr = $charge
  ->transaction->paypal;
  $payerEmail = $paypalArr['payerEmail'];
  if ($payerEmail != "") {
  $vBrainTreeCustEmail = $payerEmail;
  $vBrainTreeToken = $paypalArr['token'];
  $CardNo = "";
  $message1 = "LBL_SUCESS_ADD_PAYPAL_BRAINTREE_TXT";
  } else {
  $vBrainTreeCustEmail = "";
  $vBrainTreeToken = $creditCardArr['token'];
  $CardNo = "XXXXXXXXXXXX" . $creditCardArr['last4'];
  $message1 = "LBL_SUCESS_ADD_BRAINTREE_TXT";
  }
  $WalletId = $generalobj->InsertIntoUserWallet($iUserId, $eUserType, $BRAINTREE_CHARGE_AMOUNT, 'Credit', 0, 'Deposit', '#LBL_AMOUNT_CREDIT#', 'Unsettelled', Date('Y-m-d H:i:s'));

  $braintree_arr['BRAINTREE_TOKEN_KEY'] = $BRAINTREE_TOKEN_KEY;
  $braintree_arr['BRAINTREE_ENVIRONMENT'] = $BRAINTREE_ENVIRONMENT;
  $braintree_arr['BRAINTREE_MERCHANT_ID'] = $BRAINTREE_MERCHANT_ID;
  $braintree_arr['BRAINTREE_PUBLIC_KEY'] = $BRAINTREE_PUBLIC_KEY;
  $braintree_arr['BRAINTREE_PRIVATE_KEY'] = $BRAINTREE_PRIVATE_KEY;
  $braintree_arr['BRAINTREE_CHARGE_AMOUNT'] = $BRAINTREE_CHARGE_AMOUNT;
  $tPaymentDetails = json_encode($braintree_arr, JSON_UNESCAPED_UNICODE);

  $pay_data['tPaymentUserID'] = $transaction_id;
  $pay_data['vPaymentUserStatus'] = "approved";
  $pay_data['iUserWalletId'] = $WalletId;
  $pay_data['iAmountUser'] = $BRAINTREE_CHARGE_AMOUNT;
  $pay_data['tPaymentDetails'] = $tPaymentDetails;
  $pay_data['vPaymentMode'] = $SYSTEM_PAYMENT_ENVIRONMENT;
  $pay_data['vPaymentMethod'] = $APP_PAYMENT_METHOD;
  $pay_data['eEvent'] = "Wallet";
  $pay_data['iUserId'] = $iUserId;
  $pay_data['eUserType'] = $UserType;
  $paymentid = $obj->MySQLQueryPerform("payments", $pay_data, 'insert');
  } else {
  $returnArr['Action'] = "0";
  $returnArr['message'] = "LBL_TRY_AGAIN_LATER_TXT";
  setDataResponse($returnArr);
  }
  } catch (Exception $e) {
  $error3 = $e->getMessage();
  $returnArr['Action'] = "0";
  $returnArr['message'] = $error3;
  setDataResponse($returnArr);
  }
  ## Charge First Transaction Amount ##
  } catch (Exception $e) {
  $error3 = $e->getMessage();
  $returnArr['Action'] = "0";
  $returnArr['message'] = $error3;
  setDataResponse($returnArr);
  }
  }
  } catch (Exception $e) {
  $errMsg = $e->getMessage();
  $returnArr['Action'] = "0";
  $returnArr['message'] = $errMsg;

  setDataResponse($returnArr);
  }

  $where = " $iMemberId = '$iUserId'";
  $updateData['vBrainTreeToken'] = $vBrainTreeToken;
  $updateData['vBrainTreeCustEmail'] = $vBrainTreeCustEmail;
  $updateData['vBrainTreeCustId'] = $vBrainTreeCustId;
  $updateData['vCreditCard'] = $CardNo;

  $id = $obj->MySQLQueryPerform($tbl_name, $updateData, 'update', $where);
  if ($eMemberType == "Passenger") {
  $profileData = getPassengerDetailInfo($iUserId);
  } else {
  $profileData = getDriverDetailInfo($iUserId);
  }

  if ($id > 0) {
  $returnArr['Action'] = "1";
  $returnArr['message'] = $profileData;
  $returnArr['message1'] = $message1;
  } else {
  $returnArr['Action'] = "0";
  $returnArr['message'] = "LBL_TRY_AGAIN_LATER_TXT";
  }
  } else if ($APP_PAYMENT_METHOD == "Paymaya") {
  $vName = $UserDetailPaymaya[0]['vName'];
  $vLastName = $UserDetailPaymaya[0]['vLastName'];
  $vPhone = $UserDetailPaymaya[0]['vPhone'];
  $phonecode = $UserDetailPaymaya[0]['phonecode'];
  $phone = "+" . $phonecode . $vPhone;
  $vEmail = $UserDetailPaymaya[0]['memberemail'];
  $vPaymayaCustId = $UserDetailPaymaya[0]['vPaymayaCustId'];

  if ($vPaymayaCustId == "") {
  $POST_URL = $PAYMAYA_API_URL . "payments/v1/customers";
  $postdata = array(
  'firstName' => $vName,
  'lastName' => $vLastName,
  'contact' => array(
  'phone' => $phone,
  'email' => $vEmail
  )
  );

  $result = check_paymaya_api($POST_URL, $postdata);
  $vPaymayaCustId = $result['id'];
  if ($vPaymayaCustId != "") {
  ## Vault a Card ##
  $postdata_vault = array(
  'paymentTokenId' => $vPaymayaToken,
  'isDefault' => true,
  'redirectUrl' => array(
  'success' => $tconfig['tsite_url'] . '/assets/libraries/paymaya/success.php',
  'failure' => $tconfig['tsite_url'] . '/assets/libraries/paymaya/failure.php',
  'cancel' => $tconfig['tsite_url'] . '/assets/libraries/paymaya/cancel.php'
  )
  );
  $POST_URL_Vault = $PAYMAYA_API_URL . "payments/v1/customers/" . $vPaymayaCustId . "/cards";
  $result_vault = check_paymaya_api($POST_URL_Vault, $postdata_vault);
  $verificationUrl = $result_vault['verificationUrl'];
  if ($verificationUrl == "" || $verificationUrl == NULL) {
  $error = $result_vault['message'];
  $returnArr['Action'] = "0";
  $returnArr['message'] = $error;
  setDataResponse($returnArr);
  }
  ## Vault a Card ##
  $updateData['vPaymayaCustId'] = $vPaymayaCustId;
  } else {
  $error = $result['message'];
  $returnArr['Action'] = "0";
  $returnArr['message'] = $error;
  setDataResponse($returnArr);
  }
  } else {
  ## Vault a Card ##
  $postdata_vault = array(
  'paymentTokenId' => $vPaymayaToken,
  'isDefault' => true,
  'redirectUrl' => array(
  'success' => $tconfig['tsite_url'] . '/assets/libraries/paymaya/success.php',
  'failure' => $tconfig['tsite_url'] . '/assets/libraries/paymaya/failure.php',
  'cancel' => $tconfig['tsite_url'] . '/assets/libraries/paymaya/cancel.php'
  )
  );
  $POST_URL_Vault = $PAYMAYA_API_URL . "payments/v1/customers/" . $vPaymayaCustId . "/cards";
  $result_vault = check_paymaya_api($POST_URL_Vault, $postdata_vault);
  $verificationUrl = $result_vault['verificationUrl'];
  if ($verificationUrl == "" || $verificationUrl == NULL) {
  $error = $result_vault['message'];
  $returnArr['Action'] = "0";
  $returnArr['message'] = $error;
  setDataResponse($returnArr);
  }
  ## Vault a Card ##
  }

  $where = " $iMemberId = '$iUserId'";
  $updateData['vCreditCard'] = $CardNo;
  $id = $obj->MySQLQueryPerform($tbl_name, $updateData, 'update', $where);

  if ($id > 0) {
  $returnArr['Action'] = "1";
  $returnArr['message'] = $verificationUrl;
  } else {
  $returnArr['Action'] = "0";
  $returnArr['message'] = "LBL_TRY_AGAIN_LATER_TXT";
  }
  } else if ($APP_PAYMENT_METHOD == "Omise") {
  require_once ('assets/libraries/omise/config.php');
  $UserDetail = get_value($tbl_name, 'vOmiseCustId,vOmiseToken,' . $vEmail . ' as memberemail', $iMemberId, $iUserId);
  $vEmail = $UserDetail[0]['memberemail'];
  $vOmiseCustId = $UserDetail[0]['vOmiseCustId'];
  $vOldOmiseToken = $UserDetail[0]['vOmiseToken'];

  try {
  if ($vOmiseCustId != "") {
  //$customer = OmiseCustomer::retrieve($vOmiseCustId);
  //$card = $customer->getCards()->retrieve($vOldOmiseToken);
  //$card->destroy();
  //$card->isDestroyed(); # => true

  $customer = OmiseCustomer::retrieve($vOmiseCustId);
  $customer->update(array(
  'card' => $vOmiseToken
  ));

  $customer1 = OmiseCustomer::retrieve($vOmiseCustId);
  $cards = $customer1->getCards();
  $cardArr = $cards['data'];
  $lastcardArr = end($cardArr);

  $vOmiseCardId = $lastcardArr['id'];
  $LastFour = "XXXXXXXXXXXX" . $lastcardArr['last_digits'];

  //$vOmiseCardId = $customer['default_card'];
  //$LastFour = "XXXXXXXXXXXX".$customer['cards']['data'][0]['last_digits'];
  } else {
  try {

  $customer = OmiseCustomer::create(array(
  'email' => $vEmail,
  'description' => $eMemberType . "_" . $iUserId,
  'card' => $vOmiseToken
  ));

  $vOmiseCustId = $customer['id'];
  $vOmiseCardId = $customer['default_card'];
  $LastFour = "XXXXXXXXXXXX" . $customer['cards']['data'][0]['last_digits'];
  } catch (Exception $e) {
  $error3 = $e->getMessage();
  $returnArr['Action'] = "0";
  $returnArr['message'] = $error3;
  setDataResponse($returnArr);
  }
  }
  } catch (Exception $e) {
  $errMsg = $e->getMessage();
  $returnArr['Action'] = "0";
  $returnArr['message'] = $errMsg;
  setDataResponse($returnArr);
  }

  $where = " $iMemberId = '$iUserId'";
  $updateData['vOmiseToken'] = $vOmiseCardId;
  $updateData['vOmiseCustId'] = $vOmiseCustId;
  $updateData['vCreditCard'] = $LastFour;

  $id = $obj->MySQLQueryPerform($tbl_name, $updateData, 'update', $where);
  if ($eMemberType == "Passenger") {
  $profileData = getPassengerDetailInfo($iUserId);
  } else {
  $profileData = getDriverDetailInfo($iUserId);
  }

  if ($id > 0) {
  $returnArr['Action'] = "1";
  $returnArr['message'] = $profileData;
  $returnArr['message1'] = "LBL_SUCESS_ADD_BRAINTREE_TXT";
  } else {
  $returnArr['Action'] = "0";
  $returnArr['message'] = "LBL_TRY_AGAIN_LATER_TXT";
  }
  } else if ($APP_PAYMENT_METHOD == "Adyen") {
  $returnArr['Action'] = "1";
  $returnArr['message'] = $tconfig['tsite_url'] . "/assets/libraries/adyen/clienttoken.php?iUserId=" . $iUserId . "&UserType=" . $eMemberType;
  } else if ($APP_PAYMENT_METHOD == "Xendit") {
  require_once ('assets/libraries/xendit/config.php');
  require_once ('assets/libraries/xendit/src/XenditPHPClient.php');
  $options['secret_api_key'] = $XENDIT_SECRET_KEY;
  $xenditPHPClient = new XenditClient\XenditPHPClient($options);
  $external_id = substr(number_format(time() * rand(), 0, '', ''), 0, 15);
  $token_id = $vXenditToken;
  $amount = 0;
  $response = $xenditPHPClient->captureCreditCardPayment($external_id, $token_id, $amount);
  $result = $response['status'];
  $CardNo = $response['masked_card_number'];
  if ($result == "AUTHORIZED") {
  $where = " $iMemberId = '$iUserId'";
  $updateData['vXenditToken'] = $vXenditToken;
  $updateData['vCreditCard'] = $CardNo;
  $id = $obj->MySQLQueryPerform($tbl_name, $updateData, 'update', $where);
  if ($eMemberType == "Passenger") {
  $profileData = getPassengerDetailInfo($iUserId);
  } else {
  $profileData = getDriverDetailInfo($iUserId);
  }
  if ($id > 0) {
  $returnArr['Action'] = "1";
  $returnArr['message'] = $profileData;
  } else {
  $returnArr['Action'] = "0";
  $returnArr['message'] = "LBL_TRY_AGAIN_LATER_TXT";
  }
  } else {
  $error3 = $response['message'];
  $returnArr['Action'] = "0";
  $returnArr['message'] = $error3;
  setDataResponse($returnArr);
  }
  }

  return $returnArr;
  }

  ########################### GenerateCustomer App Payment Method Wise #############################################################
  ########################### Charge Customer App Payment Method Wise ##############################################################

  function ChargeCustomer($Data, $eChargeEvent = "CollectPayment") {
  global $generalobj, $obj, $STRIPE_SECRET_KEY, $STRIPE_PUBLISH_KEY, $gateway, $BRAINTREE_TOKEN_KEY, $BRAINTREE_ENVIRONMENT, $BRAINTREE_MERCHANT_ID, $BRAINTREE_PUBLIC_KEY, $BRAINTREE_PRIVATE_KEY, $BRAINTREE_CHARGE_AMOUNT, $PAYMAYA_API_URL, $PAYMAYA_SECRET_KEY, $PAYMAYA_PUBLISH_KEY, $PAYMAYA_ENVIRONMENT_MODE, $OMISE_SECRET_KEY, $OMISE_PUBLIC_KEY, $ADYEN_MERCHANT_ACCOUNT, $ADYEN_USER_NAME, $ADYEN_PASSWORD, $ADYEN_API_URL, $XENDIT_PUBLIC_KEY, $XENDIT_SECRET_KEY, $APP_PAYMENT_METHOD, $SYSTEM_PAYMENT_ENVIRONMENT; // Stripe,Braintree
  $iOrderId = $Data['iOrderId'];
  $vOrderNo = $Data['vOrderNo'];
  $iFare = $Data['iFare'];
  $price_new = $Data['price_new'];
  $currency = $Data['currency'];
  $vStripeCusId = $Data['vStripeCusId'];
  $description = $Data['description'];
  $iTripId = $Data['iTripId'];
  $eCancelChargeFailed = $Data['eCancelChargeFailed'];
  $vBrainTreeToken = $Data['vBrainTreeToken'];
  $vRideNo = $Data['vRideNo'];
  $iMemberId = $Data['iMemberId'];
  $UserType = $Data['UserType'];
  $vBrainTreeChargePrice = $price_new / 100;
  $vPaymayaChargePrice = $price_new / 100;
  //$vAdyenChargePrice = $price_new/100;
  $vAdyenChargePrice = $price_new;
  if ($UserType == "Passenger") {
  $tbl_name = "register_user";
  $iUserId = "iUserId";
  $UserDetailPaymaya = get_value($tbl_name, 'vPaymayaCustId,vPaymayaToken,vAdyenToken,vName,vLastName,vEmail,vXenditAuthId,vXenditToken,vCurrencyPassenger as vCurrency', $iUserId, $iMemberId);
  } else {
  $tbl_name = "register_driver";
  $iUserId = "iDriverId";
  $UserDetailPaymaya = get_value($tbl_name, 'vPaymayaCustId,vPaymayaToken,vAdyenToken,vName,vLastName,vEmail,vXenditAuthId,vXenditToken,vCurrencyDriver as vCurrency', $iUserId, $iMemberId);
  }

  if ($APP_PAYMENT_METHOD == "Stripe") {
  require_once ('assets/libraries/stripe/config.php');
  require_once ('assets/libraries/stripe/stripe-php-2.1.4/lib/Stripe.php');

  try {
  if ($iFare > 0) {
  $charge_create = Stripe_Charge::create(array(
  "amount" => $price_new,
  "currency" => $currency,
  "customer" => $vStripeCusId,
  "description" => $description
  ));

  $details = json_decode($charge_create);
  $result = get_object_vars($details);
  }

  if ($iFare == 0 || ($result['status'] == "succeeded" && $result['paid'] == "1")) {

  $stripe_arr['STRIPE_SECRET_KEY'] = $STRIPE_SECRET_KEY;
  $stripe_arr['STRIPE_PUBLISH_KEY'] = $STRIPE_PUBLISH_KEY;
  $tPaymentDetails = json_encode($stripe_arr, JSON_UNESCAPED_UNICODE);
  $pay_data['tPaymentUserID'] = $iFare == 0 ? "" : $result['id'];
  $pay_data['vPaymentUserStatus'] = "approved";
  $pay_data['iTripId'] = $iTripId;
  $pay_data['iAmountUser'] = $iFare;
  $pay_data['tPaymentDetails'] = $tPaymentDetails;
  $pay_data['iOrderId'] = $iOrderId;
  $pay_data['vPaymentMethod'] = $APP_PAYMENT_METHOD;
  $pay_data['iUserId'] = $iMemberId;
  $pay_data['eUserType'] = $UserType;

  $id = $obj->MySQLQueryPerform("payments", $pay_data, 'insert');
  $returnArr['status'] = "success";
  } else {
  $returnArr['status'] = "fail";
  if ($eChargeEvent == "cancelTrip" || $eChargeEvent == "addMoneyUserWallet" || $eChargeEvent == "ChargePassengerOutstandingAmount") {
  $eCancelChargeFailed = "Yes";
  } else {

  $where = " iOrderId = '$iOrderId'";
  $data['iStatusCode'] = 11;
  $errorid = $obj->MySQLQueryPerform("orders", $data, 'update', $where);
  $OrderLogId = createOrderLog($iOrderId, "11");
  $error3 = $e->getMessage();
  $returnArr["Action"] = "0";
  $returnArr['message'] = "LBL_CHARGE_COLLECT_FAILED";
  setDataResponse($returnArr);
  }
  }
  } catch (Exception $e) {
  $returnArr['status'] = "fail";
  $error3 = $e->getMessage();
  if ($eChargeEvent == "cancelTrip") {
  $eCancelChargeFailed = 'Yes';
  } else {
  $where = " iOrderId = '$iOrderId'";
  $data['iStatusCode'] = 11;
  $errorid = $obj->MySQLQueryPerform("orders", $data, 'update', $where);
  $OrderLogId = createOrderLog($iOrderId, "11");
  $error3 = $e->getMessage();
  $returnArr["Action"] = "0";
  $returnArr['message'] = $error3;
  setDataResponse($returnArr);
  }
  }
  } else if ($APP_PAYMENT_METHOD == "Braintree") {
  require_once ('assets/libraries/braintree/lib/Braintree.php');
  try {
  if ($iFare > 0) {
  $charge_create = $gateway->transaction()
  ->sale(['paymentMethodToken' => $vBrainTreeToken, 'amount' => $vBrainTreeChargePrice]);

  $status = $charge_create->success;
  $transactionid = $charge_create
  ->transaction->id;
  }

  if ($iFare == 0 || $status == "1") {

  $braintree_arr['BRAINTREE_TOKEN_KEY'] = $BRAINTREE_TOKEN_KEY;
  $braintree_arr['BRAINTREE_ENVIRONMENT'] = $BRAINTREE_ENVIRONMENT;
  $braintree_arr['BRAINTREE_MERCHANT_ID'] = $BRAINTREE_MERCHANT_ID;
  $braintree_arr['BRAINTREE_PUBLIC_KEY'] = $BRAINTREE_PUBLIC_KEY;
  $braintree_arr['BRAINTREE_PRIVATE_KEY'] = $BRAINTREE_PRIVATE_KEY;
  $tPaymentDetails = json_encode($braintree_arr, JSON_UNESCAPED_UNICODE);
  $pay_data['tPaymentUserID'] = $iFare == 0 ? "" : $transactionid;
  $pay_data['vPaymentUserStatus'] = "approved";
  $pay_data['iTripId'] = $iTripId;
  $pay_data['iAmountUser'] = $iFare;
  $pay_data['tPaymentDetails'] = $tPaymentDetails;
  $pay_data['iOrderId'] = $iOrderId;
  $pay_data['vPaymentMethod'] = $APP_PAYMENT_METHOD;
  $pay_data['iUserId'] = $iMemberId;
  $pay_data['eUserType'] = $UserType;

  $id = $obj->MySQLQueryPerform("payments", $pay_data, 'insert');
  $returnArr['status'] = "success";
  } else {
  $returnArr['status'] = "fail";
  if ($eChargeEvent == "cancelTrip" || $eChargeEvent == "addMoneyUserWallet" || $eChargeEvent == "ChargePassengerOutstandingAmount") {
  $eCancelChargeFailed = "Yes";
  } else {
  $where = " iOrderId = '$iOrderId'";
  $data['iStatusCode'] = 11;
  $errorid = $obj->MySQLQueryPerform("orders", $data, 'update', $where);
  $OrderLogId = createOrderLog($iOrderId, "11");
  $error3 = $e->getMessage();
  $returnArr["Action"] = "0";
  $returnArr['message'] = "LBL_CHARGE_COLLECT_FAILED";
  setDataResponse($returnArr);
  }
  }
  } catch (Exception $e) {
  $returnArr['status'] = "fail";
  $error3 = $e->getMessage();
  if ($eChargeEvent == "cancelTrip") {
  $eCancelChargeFailed = 'Yes';
  } else {
  $where = " iOrderId = '$iOrderId'";
  $data['iStatusCode'] = 11;
  $errorid = $obj->MySQLQueryPerform("orders", $data, 'update', $where);
  $OrderLogId = createOrderLog($iOrderId, "11");
  $error3 = $e->getMessage();
  $returnArr["Action"] = "0";
  $returnArr['message'] = $error3;
  setDataResponse($returnArr);
  }
  }
  } else if ($APP_PAYMENT_METHOD == "Paymaya") {
  $vPaymayaCustId = $UserDetailPaymaya[0]['vPaymayaCustId'];
  $vPaymayaToken = $UserDetailPaymaya[0]['vPaymayaToken'];
  $Ratio = get_value('currency', 'Ratio', 'vName', 'PHP', '', 'true');
  $vPaymayaChargePrice = $vPaymayaChargePrice * $Ratio;
  $vPaymayaChargePrice = round($vPaymayaChargePrice, 2);
  $postdata_charge = array(
  'totalAmount' => array(
  'amount' => $vPaymayaChargePrice,
  'currency' => 'PHP'
  ),
  'requestReferenceNumber' => 'REF' . $vRideNo
  );
  $url = $PAYMAYA_API_URL . "/payments/v1/customers/" . $vPaymayaCustId . "/cards/" . $vPaymayaToken . "/payments";
  $result_charge = check_paymaya_api($url, $postdata_charge);
  $PaymentId = $result_charge['id'];
  $paymentstatus = $result_charge['status']; //PAYMENT_SUCCESS
  if ($vPaymayaChargePrice == 0 || $paymentstatus == 'PAYMENT_SUCCESS') {
  $paymaya_arr['PAYMAYA_API_URL'] = $PAYMAYA_API_URL;
  $paymaya_arr['PAYMAYA_SECRET_KEY'] = $PAYMAYA_SECRET_KEY;
  $paymaya_arr['PAYMAYA_PUBLISH_KEY'] = $PAYMAYA_PUBLISH_KEY;
  $paymaya_arr['PAYMAYA_ENVIRONMENT_MODE'] = $PAYMAYA_ENVIRONMENT_MODE;
  $tPaymentDetails = json_encode($paymaya_arr, JSON_UNESCAPED_UNICODE);
  $pay_data['tPaymentUserID'] = $PaymentId;
  $pay_data['vPaymentUserStatus'] = "approved";
  $pay_data['iTripId'] = $iTripId;
  $pay_data['iAmountUser'] = $iFare;
  $pay_data['tPaymentDetails'] = $tPaymentDetails;
  $pay_data['iOrderId'] = $iOrderId;
  $pay_data['vPaymentMethod'] = $APP_PAYMENT_METHOD;
  $pay_data['iUserId'] = $iMemberId;
  $pay_data['eUserType'] = $UserType;
  $id = $obj->MySQLQueryPerform("payments", $pay_data, 'insert');
  $returnArr['status'] = "success";
  } else {
  $returnArr['status'] = "fail";
  if ($eChargeEvent == "cancelTrip" || $eChargeEvent == "addMoneyUserWallet" || $eChargeEvent == "ChargePassengerOutstandingAmount") {
  $eCancelChargeFailed = "Yes";
  } else {
  $where = " iOrderId = '$iOrderId'";
  $data['iStatusCode'] = 11;
  $errorid = $obj->MySQLQueryPerform("orders", $data, 'update', $where);
  $OrderLogId = createOrderLog($iOrderId, "11");
  $error3 = $e->getMessage();
  $returnArr["Action"] = "0";
  $returnArr['message'] = "LBL_CHARGE_COLLECT_FAILED";
  setDataResponse($returnArr);
  }
  }
  } else if ($APP_PAYMENT_METHOD == "Omise") {
  require_once ('assets/libraries/omise/config.php');
  $UserDetailOmise = get_value($tbl_name, 'vOmiseCustId,vOmiseToken', $iUserId, $iMemberId);
  $vOmiseCustId = $UserDetailOmise[0]['vOmiseCustId'];
  $vOmiseToken = $UserDetailOmise[0]['vOmiseToken'];

  try {
  if ($iFare > 0) {
  $charge = OmiseCharge::create(array(
  'amount' => $price_new,
  'currency' => $currency,
  'customer' => $vOmiseCustId,
  'card' => $vOmiseToken
  ));
  }

  if ($iFare == 0 || ($charge['status'] == "successful" && $charge['paid'] == "1")) {

  $omise_arr['OMISE_SECRET_KEY'] = $OMISE_SECRET_KEY;
  $omise_arr['OMISE_PUBLIC_KEY'] = $OMISE_PUBLIC_KEY;
  $tPaymentDetails = json_encode($omise_arr, JSON_UNESCAPED_UNICODE);
  $pay_data['tPaymentUserID'] = $iFare == 0 ? "" : $charge['transaction'];
  $pay_data['vPaymentUserStatus'] = "approved";
  $pay_data['iTripId'] = $iTripId;
  $pay_data['iAmountUser'] = $iFare;
  $pay_data['tPaymentDetails'] = $tPaymentDetails;
  $pay_data['iOrderId'] = $iOrderId;
  $pay_data['vPaymentMethod'] = $APP_PAYMENT_METHOD;
  $pay_data['iUserId'] = $iMemberId;
  $pay_data['eUserType'] = $UserType;

  $id = $obj->MySQLQueryPerform("payments", $pay_data, 'insert');
  $returnArr['status'] = "success";
  } else {
  $returnArr['status'] = "fail";
  if ($eChargeEvent == "cancelTrip" || $eChargeEvent == "addMoneyUserWallet" || $eChargeEvent == "ChargePassengerOutstandingAmount") {
  $eCancelChargeFailed = "Yes";
  } else {
  $where = " iOrderId = '$iOrderId'";
  $data['iStatusCode'] = 11;
  $id = $obj->MySQLQueryPerform("orders", $data, 'update', $where);
  $OrderLogId = createOrderLog($iOrderId, "11");
  $error3 = $e->getMessage();
  $returnArr["Action"] = "0";
  $returnArr['message'] = "LBL_CHARGE_COLLECT_FAILED";
  setDataResponse($returnArr);
  }
  }
  } catch (Exception $e) {
  $returnArr['status'] = "fail";
  $error3 = $e->getMessage();
  if ($eChargeEvent == "cancelTrip" || $eChargeEvent == "addMoneyUserWallet" || $eChargeEvent == "ChargePassengerOutstandingAmount") {
  $eCancelChargeFailed = 'Yes';
  } else {
  $where = " iOrderId = '$iOrderId'";
  $data['iStatusCode'] = 11;
  $id = $obj->MySQLQueryPerform("orders", $data, 'update', $where);
  $OrderLogId = createOrderLog($iOrderId, "11");
  $error3 = $e->getMessage();
  $returnArr["Action"] = "0";
  $returnArr['message'] = $error3;
  setDataResponse($returnArr);
  }
  }
  } else if ($APP_PAYMENT_METHOD == "Adyen") {
  $vAdyenToken = $UserDetailPaymaya[0]['vAdyenToken'];
  $shopperReference = $UserDetailPaymaya[0]['vName'] . " " . $UserDetailPaymaya[0]['vLastName'];
  $shopperEmail = $UserDetailPaymaya[0]['vEmail'];
  $reference = rand(111111, 999999);
  $USERPWD = $ADYEN_USER_NAME . ":" . $ADYEN_PASSWORD;
  $result = array();
  // Pass the customer's authorisation code, email and amount
  $postdata = array(
  "selectedRecurringDetailReference" => $vAdyenToken,
  "recurring" => array(
  "contract" => "RECURRING"
  ),
  "merchantAccount" => $ADYEN_MERCHANT_ACCOUNT,
  "amount" => array(
  "value" => $vAdyenChargePrice,
  "currency" => $currency
  ),
  "reference" => $reference,
  "shopperEmail" => $shopperEmail,
  "shopperReference" => $shopperReference,
  "shopperInteraction" => "ContAuth"
  );

  $ch = curl_init();
  curl_setopt($ch, CURLOPT_URL, $ADYEN_API_URL);
  curl_setopt($ch, CURLOPT_HEADER, false);
  curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
  curl_setopt($ch, CURLOPT_USERPWD, $USERPWD);
  curl_setopt($ch, CURLOPT_POST, count(json_encode($postdata)));
  curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postdata));
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  curl_setopt($ch, CURLOPT_HTTPHEADER, array(
  "Content-type: application/json"
  ));

  $request = curl_exec($ch); //echo "<pre>";print_r($request);exit;
  curl_close($ch);
  if ($request) {
  $result = json_decode($request, true);
  $resultCode = $result['resultCode']; //Authorised
  $authCode = $result['authCode'];

  if ($resultCode == "Authorised") {
  $Adyen_arr['ADYEN_MERCHANT_ACCOUNT'] = $ADYEN_MERCHANT_ACCOUNT;
  $Adyen_arr['ADYEN_USER_NAME'] = $ADYEN_USER_NAME;
  $Adyen_arr['ADYEN_PASSWORD'] = $ADYEN_PASSWORD;
  $Adyen_arr['ADYEN_API_URL'] = $ADYEN_API_URL;
  $tPaymentDetails = json_encode($Adyen_arr, JSON_UNESCAPED_UNICODE);
  $pay_data['tPaymentUserID'] = $authCode;
  $pay_data['vPaymentUserStatus'] = "approved";
  $pay_data['iTripId'] = $iTripId;
  $pay_data['iAmountUser'] = $iFare;
  $pay_data['tPaymentDetails'] = $tPaymentDetails;
  $pay_data['iOrderId'] = $iOrderId;
  $pay_data['vPaymentMethod'] = $APP_PAYMENT_METHOD;
  $pay_data['iUserId'] = $iMemberId;
  $pay_data['eUserType'] = $UserType;
  $id = $obj->MySQLQueryPerform("payments", $pay_data, 'insert');
  $returnArr['status'] = "success";
  } else {
  $returnArr['status'] = "fail";
  if ($eChargeEvent == "cancelTrip" || $eChargeEvent == "addMoneyUserWallet" || $eChargeEvent == "ChargePassengerOutstandingAmount") {
  $eCancelChargeFailed = "Yes";
  } else {
  $returnArr['Action'] = "0";
  $returnArr['message'] = "LBL_CHARGE_COLLECT_FAILED";
  setDataResponse($returnArr);
  }
  }
  } else {
  $returnArr['status'] = "fail";
  if ($eChargeEvent == "cancelTrip" || $eChargeEvent == "addMoneyUserWallet" || $eChargeEvent == "ChargePassengerOutstandingAmount") {
  $eCancelChargeFailed = "Yes";
  } else {
  $where = " iOrderId = '$iOrderId'";
  $data['iStatusCode'] = 11;
  $errorid = $obj->MySQLQueryPerform("orders", $data, 'update', $where);
  $OrderLogId = createOrderLog($iOrderId, "11");
  $error3 = $e->getMessage();
  $returnArr["Action"] = "0";
  $returnArr['message'] = "LBL_CHARGE_COLLECT_FAILED";
  setDataResponse($returnArr);
  }
  }
  } else if ($APP_PAYMENT_METHOD == "Xendit") {
  require_once ('assets/libraries/xendit/config.php');
  require_once ('assets/libraries/xendit/src/XenditPHPClient.php');
  $options['secret_api_key'] = $XENDIT_SECRET_KEY;
  $xenditPHPClient = new XenditClient\XenditPHPClient($options);
  $external_id = substr(number_format(time() * rand(), 0, '', ''), 0, 15);
  $IDRCurrencyRatio = get_value('currency', 'Ratio', 'vName', 'IDR', '', 'true');
  $famount = $iFare * $IDRCurrencyRatio;
  $famount = round($famount);
  $vXenditAuthId = $UserDetailPaymaya[0]['vXenditAuthId'];
  $vXenditToken = $UserDetailPaymaya[0]['vXenditToken'];
  $response = $xenditPHPClient->captureCreditCardPayment($external_id, $vXenditToken, $famount);
  $resultCode = $response['status'];
  if ($resultCode == "CAPTURED") {
  $xendit_arr['XENDIT_SECRET_KEY'] = $XENDIT_SECRET_KEY;
  $xendit_arr['XENDIT_PUBLIC_KEY'] = $XENDIT_PUBLIC_KEY;
  $tPaymentDetails = json_encode($xendit_arr, JSON_UNESCAPED_UNICODE);
  $pay_data['tPaymentUserID'] = $response["id"];
  $pay_data['vPaymentUserStatus'] = "approved";
  $pay_data['iTripId'] = $iTripId;
  $pay_data['iAmountUser'] = $iFare;
  $pay_data['tPaymentDetails'] = $tPaymentDetails;
  $pay_data['iOrderId'] = $iOrderId;
  $pay_data['vPaymentMethod'] = $APP_PAYMENT_METHOD;
  $pay_data['iUserId'] = $iMemberId;
  $pay_data['eUserType'] = $UserType;
  $id = $obj->MySQLQueryPerform("payments", $pay_data, 'insert');
  $returnArr['status'] = "success";
  } else {
  $returnArr['status'] = "fail";
  if ($eChargeEvent == "cancelTrip" || $eChargeEvent == "addMoneyUserWallet" || $eChargeEvent == "ChargePassengerOutstandingAmount") {
  $eCancelChargeFailed = "Yes";
  } else {
  $error3 = $response['message'];
  $where = " iOrderId = '$iOrderId'";
  $data['iStatusCode'] = 11;
  $errorid = $obj->MySQLQueryPerform("orders", $data, 'update', $where);
  $OrderLogId = createOrderLog($iOrderId, "11");
  $error3 = $e->getMessage();
  $returnArr["Action"] = "0";
  $returnArr['message'] = $error3;
  setDataResponse($returnArr);
  }
  }
  }
  $returnArr['id'] = $id;
  $returnArr['eCancelChargeFailed'] = $eCancelChargeFailed;

  return $returnArr;
  }
 */
########################### Charge Customer App Payment Method Wise ##############################################################
########################### Paymaya Payment API  ##############################################################################

/* function check_paymaya_api($url, $postdata = array()) {
  global $generalobj, $obj, $PAYMAYA_SECRET_KEY, $PAYMAYA_PUBLISH_KEY;
  $result = array();
  $ch = curl_init();
  curl_setopt($ch, CURLOPT_URL, $url);
  curl_setopt($ch, CURLOPT_POST, 1);
  curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postdata)); //Post Fields
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  $paymaya_auth = base64_encode($PAYMAYA_SECRET_KEY . ":");
  $headers = ['Authorization: Basic ' . $paymaya_auth, 'Content-Type: application/json',];
  curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
  $request = curl_exec($ch); //echo "<pre>";print_r($request);exit;
  curl_close($ch);
  if ($request) {
  $result = json_decode($request, true);
  }
  return $result;
  } */

########################### Paymaya Payment API  ##############################################################################
//Added By HJ On 25-01-2019 For Get Custome Topping Data Start

function getMenuCustomeToppings($iMenuItemId, $currencySymbol, $Ratio, $vLanguage, $eFor) {
    global $obj, $generalobj, $tconfig;
    //ini_set("display_errors", 1);
    //echo "<pre>";
    //error_reporting(E_ALL);
    $returnArr = array();
    $ssql = "";
    //echo $eFor;die
    if ($eFor == 1) {
        $ssql = "";
        //$ssql .= " AND mo.eStatus = 'Active' ";
        // $languageLabelsArr = getLanguageLabelsArr($vLanguage,"1");
        $sql = "SELECT mo.*,fm.iFoodMenuId,co.iCompanyId FROM menuitem_options as mo LEFT JOIN menu_items as mi ON mo.iMenuItemId=mi.iMenuItemId LEFT JOIN food_menu as fm ON mi.iFoodMenuId=fm.iFoodMenuId LEFT JOIN company as co ON fm.iCompanyId=co.iCompanyId WHERE co.iCompanyId = '" . $iMenuItemId . "' AND mi.eStatus = 'Active' AND `eOptionType` NOT IN ('Options',  'Addon') AND mo.eStatus='Active' AND mi.eAvailable = 'Yes'" . $ssql;
    } else {
        $sql = "SELECT mo.*,fm.iFoodMenuId FROM menuitem_options mo LEFT JOIN menu_items as mi ON mo.iMenuItemId=mi.iMenuItemId LEFT JOIN food_menu as fm ON mi.iFoodMenuId=fm.iFoodMenuId WHERE mo.iMenuItemId = '" . $iMenuItemId . "' AND `eOptionType` NOT IN ('Options') AND mo.eStatus = 'Active'";
    }
    //echo $sql;die;
    $db_options_data = $obj->MySQLSelect($sql);
    for ($i = 0; $i < count($db_options_data); $i++) {
        $eOptionType = $db_options_data[$i]['eOptionType'];
        $eOptionInputType = $db_options_data[$i]['eOptionInputType'];
        $vOptionMinSelection = $db_options_data[$i]['vOptionMinSelection'];
        $vOptionMaxSelection = $db_options_data[$i]['vOptionMaxSelection'];
        $fPrice = $db_options_data[$i]['fPrice'];
        $fUserPrice = number_format($fPrice * $Ratio, 2);
        $fUserPriceWithSymbol = $currencySymbol . " " . $fUserPrice;
        $db_options_data[$i]['fUserPrice'] = $fUserPrice;
        $db_options_data[$i]['fUserPriceWithSymbol'] = $fUserPriceWithSymbol;
        $returnArr[$eOptionType]['eOptionType'] = $eOptionType;
        $returnArr[$eOptionType]['iMenuItemId'] = $db_options_data[$i]['iMenuItemId'];
        $returnArr[$eOptionType]['iFoodMenuId'] = $db_options_data[$i]['iFoodMenuId'];
        $returnArr[$eOptionType]['eOptionInputType'] = $eOptionInputType;
        $returnArr[$eOptionType]['vOptionMinSelection'] = $vOptionMinSelection;
        $returnArr[$eOptionType]['vOptionMaxSelection'] = $vOptionMaxSelection;
        $returnArr[$eOptionType]['subItemArr'][] = $db_options_data[$i];
    }
    $finalReturnArr = array();
    foreach ($returnArr as $key => $val) {
        $finalReturnArr[] = $val;
    }
    //echo "<pre>";print_r($finalReturnArr);die;
    return $finalReturnArr;
}

//Added By HJ On 25-01-2019 For Get Custome Topping Data End

function check_user_mr() {

    if ((isset($_SESSION['sess_iUserId']) && !empty($_SESSION['sess_iUserId']) ) || (isset($_SESSION['sess_iAdminUserId']) && !empty($_SESSION['sess_iAdminUserId']) )) {

        if ((isset($_SESSION['sess_iAdminUserId']) && !empty($_SESSION['sess_iAdminUserId']))) {
            return "admin";
        } elseif ((isset($_SESSION['sess_user']) && !empty($_SESSION['sess_user']) && $_SESSION['sess_user'] == 'rider')) {

            return "user";
        } elseif ((isset($_SESSION['postDetail']) && !empty($_SESSION['postDetail']) && $_SESSION['postDetail']['user_type'] == 'company' && $_SESSION['sess_eSystem'] == 'DeliverAll')) {
            return "store";
        } else {
            return "guest";
        }
    } else {
        return "guest";
    }
}

function check_type_wise_mr($page_name_mr) {
    global $obj;
            
    $user_type = check_user_mr();
    //ini_set('display_errors', 1);
    //error_reporting(E_ALL);
    $fromOrder = $loggedInUserType = "guest";
    if (isset($_REQUEST['order']) && $_REQUEST['order'] != "") {
        $fromOrder = $_REQUEST['order'];
    }
    checkCartItemStatus($fromOrder); // Added By HJ On 18-01-2020 For Check Cart's Item Status 141 Mantis Bug - #2367
    $userSession = "MANUAL_ORDER_" . strtoupper($fromOrder);
    $orderServiceSession = "MAUAL_ORDER_SERVICE_" . strtoupper($fromOrder);
    $orderUserIdSession = "MANUAL_ORDER_USERID_" . strtoupper($fromOrder);
    $orderAddressIdSession = "MANUAL_ORDER_ADDRESSID_" . strtoupper($fromOrder);
    $orderAddressSession = "MANUAL_ORDER_ADDRESS_" . strtoupper($fromOrder);
    $orderLatitudeSession = "MANUAL_ORDER_LATITUDE_" . strtoupper($fromOrder);
    $orderLongitudeSession = "MANUAL_ORDER_LONGITUDE_" . strtoupper($fromOrder);
    $orderUserSession = "MANUAL_ORDER_USER_" . strtoupper($fromOrder);
    $orderUserNameSession = "MANUAL_ORDER_USER_NAME_" . strtoupper($fromOrder);
    $orderCompanyNameSession = "MANUAL_ORDER_COMPANY_NAME_" . strtoupper($fromOrder);
    $orderUserEmailSession = "MANUAL_ORDER_USER_EMAIL_" . strtoupper($fromOrder);
    getCurrencyUserWiseMr($fromOrder);
    $_SESSION[$userSession] = $fromOrder;
    //$_SESSION['sess_page_name'] = $page_name_mr;
    //echo "<pre>";print_r($fromOrder);die;
    $extraParameter = "?order=" . $fromOrder;
    $loggedInUserId = 0;
    $screenName = "store-listing";
    if ($page_name_mr == "customer_info") {
        $screenName = "user-order-information";
    } else if ($page_name_mr == "restaurant_place-order") {
        $screenName = "store-order";
    } else if ($page_name_mr == "restaurant_menu") {
        $screenName = "store-items";
    } else if ($page_name_mr == "restaurant_listing") {
        $screenName = "store-listing";
    }
    //echo "<pre>";print_R($_SESSION);die;
    $changedUser = "";
    //echo $screenName;die;
    if (isset($_SESSION['sess_iUserId'])) {
        $loggedInUserId = $_SESSION['sess_iUserId'];
    }
    if (isset($_SESSION['sess_user'])) {
        $loggedInUserType = strtolower($_SESSION['sess_user']);
    }
    if (isset($_SESSION['sess_iUserId']) && $_SESSION['sess_iUserId'] != "" && $fromOrder == "guest" && $loggedInUserType != "driver" && $loggedInUserType != "organization") {
        $changedUser = "user";
        changeManualOrderSession($fromOrder, $changedUser);
        //echo "<pre>";print_R($_SESSION);die;
        header('Location:' . $screenName . "?order=" . $changedUser);
        exit;
    }
    if ($loggedInUserId > 0 && $fromOrder != "user" && $user_type == "user") {
        $changedUser = "user";
        //echo $changedUser;die;
        changeManualOrderSession($fromOrder, $changedUser);
    } else if ($loggedInUserId == 0 && $fromOrder == "user") {
        $changedUser = "guest";
        //echo $loggedInUserId;die;
        changeManualOrderSession($fromOrder, $changedUser);
    }
    
    if ($changedUser != "") {
        header('Location:' . $screenName . "?order=" . $changedUser);
    }

    if (isset($_SESSION['sess_iAdminUserId']) && $_SESSION['sess_iAdminUserId'] > 0 && $fromOrder == 'admin') {
       $_SESSION[$orderUserSession] = $_SESSION['sess_iAdminUserId'];
        $page_name_mr_array = array('customer_info', 'service_listing', 'restaurant_listing', 'restaurant_menu', 'restaurant_place-order');
        if (in_array($page_name_mr, $page_name_mr_array)) {
            if ($page_name_mr != 'customer_info') {
                if (empty($_SESSION[$orderAddressIdSession]) && empty($_SESSION[$userSession]) && $_SESSION[$userSession] == 'admin') {
                    header('Location:user-order-information' . $extraParameter);
                }
            }
        } else {
            if ($page_name_mr != 'customer_info') {
                if (!empty($_SESSION[$orderAddressIdSession]) && !empty($_SESSION[$userSession]) && $_SESSION[$userSession] == 'admin' && !empty($_SESSION[$orderServiceSession])) {
                    header('Location:store-listing' . $extraParameter);
                } else {
                    header('Location:user-order-information' . $extraParameter);
                }
            }
        }
    } else if (isset($_SESSION['sess_iUserId']) && $_SESSION['sess_iUserId'] > 0 && $fromOrder == 'store') {
       $_SESSION[$orderUserSession] = $_SESSION['sess_iUserId'];
        $page_name_mr_array = array('customer_info', 'restaurant_menu', 'restaurant_place-order');
        if (in_array($page_name_mr, $page_name_mr_array)) {
            if ($page_name_mr != 'customer_info') {
                if (empty($_SESSION[$orderAddressIdSession]) && empty($_SESSION[$userSession]) && $_SESSION[$userSession] == 'store') {
                    header('Location:user-order-information' . $extraParameter);
                }
            }
        } else {
            if ($page_name_mr != 'customer_info') {
                if (!empty($_SESSION[$orderAddressIdSession]) && !empty($_SESSION[$userSession]) && $_SESSION[$userSession] == 'store') {
                    header('Location:store-items?id=' . $_SESSION['sess_iUserId'] . "&order=" . $fromOrder);
                    exit;
                } else {
                    header('Location:user-order-information' . $extraParameter);
                }
            }
        }
    } else if ($fromOrder == 'user') {
        $_SESSION[$orderUserSession] = 0;
        $page_name_mr_array = array('user_info', 'service_listing', 'restaurant_listing', 'restaurant_menu', 'restaurant_place-order', 'thank_you');
        if (in_array($page_name_mr, $page_name_mr_array)) {
            if (!empty($_SESSION[$orderLatitudeSession]) && !empty($_SESSION[$orderLongitudeSession]) && empty($_SESSION[$orderUserIdSession])) {
                $_SESSION[$orderUserIdSession] = $_SESSION['sess_iUserId'];
                $_SESSION[$orderUserNameSession] = $_SESSION['sess_vName'];
                $_SESSION[$orderCompanyNameSession] = "";
                $_SESSION[$orderUserEmailSession] = $_SESSION['sess_vEmail'];
                $_SESSION[$orderAddressIdSession] = "";
                //$_SESSION["sess_user_mr"] = "rider";
                $maildata['EMAIL_mr'] = $_SESSION[$orderUserEmailSession];
                $maildata['NAME_mr'] = $_SESSION[$orderUserNameSession];
            }
            if (empty($_SESSION[$orderLatitudeSession]) && empty($_SESSION[$orderLongitudeSession]) && empty($_SESSION[$orderAddressSession]) && empty($_SESSION[$orderServiceSession]) && $page_name_mr != 'user_info') {
                header('Location:order-items' . $extraParameter);
            }
            //if (!empty($_SESSION[$orderLatitudeSession]) && !empty($_SESSION[$orderLongitudeSession]) && !empty($_SESSION[$orderAddressSession]) && !empty($_SESSION[$orderServiceSession])) {
            //    header('Location:store-listing' . $extraParameter);exit;
            //} else {
            //    
            //    if ($page_name_mr != 'restaurant_menu' && $page_name_mr != 'user_info') {
            //        header('Location:order-items' . $extraParameter);
            //    }
            //}
        } else {
            if (!empty($_SESSION[$orderLatitudeSession]) && !empty($_SESSION[$orderLongitudeSession]) && !empty($_SESSION[$orderAddressSession]) && !empty($_SESSION[$orderServiceSession])) {
                header('Location:store-listing' . $extraParameter);
            } else {
                 //if ($page_name_mr != 'user_info' && $page_name_mr!='customer_info') {
                if ($page_name_mr != 'user_info') {
                    header('Location:order-items' . $extraParameter);
                }
            }
        }
    } else if ($fromOrder == 'guest') {
        $page_name_mr_array = array('user_info', 'service_listing', 'restaurant_listing', 'restaurant_menu', 'restaurant_place-order', 'thank_you');
        if (in_array($page_name_mr, $page_name_mr_array)) {
            //$_SESSION['sess_currentpage_url_mr'] = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
           if (empty($_SESSION[$orderLatitudeSession]) && empty($_SESSION[$orderLongitudeSession]) && empty($_SESSION[$orderAddressSession]) && empty($_SESSION[$orderServiceSession]) && $page_name_mr != 'user_info') {
                header('Location:order-items' . $extraParameter);
            }
        } else {
            //if (empty($_SESSION[$orderLatitudeSession]) && empty($_SESSION[$orderLongitudeSession]) && empty($_SESSION[$orderAddressSession]) && empty($_SESSION[$orderServiceSession]) && $page_name_mr != 'user_info') {
            //    header('Location:order-items' . $extraParameter);
            //}
             if (!empty($_SESSION[$orderLatitudeSession]) && !empty($_SESSION[$orderLongitudeSession]) && !empty($_SESSION[$orderAddressSession]) && !empty($_SESSION[$orderServiceSession])) {
                header('Location:store-listing' . $extraParameter);
            } else {
                 //if ($page_name_mr != 'user_info' && $page_name_mr!='customer_info') {
                if ($page_name_mr != 'user_info') {
                    header('Location:order-items' . $extraParameter);
                }
            }
        }
    } else {
        $extraParameter = "?order=guest";
        header('Location:order-items' . $extraParameter);
    }
}

// Added By HJ On 18-01-2020 For Check Cart's Item Status 141 Mantis Bug - #2367 Start
function checkCartItemStatus($fromOrder) {
    global $obj;
    /* $fromOrder = "guest";
      if (isset($_REQUEST['order']) && $_REQUEST['order'] != "") {
      $fromOrder = $_REQUEST['order'];
      } */
    $orderDetailsSession = "ORDER_DETAILS_" . strtoupper($fromOrder);
    //echo "<pre>";print_r($_SESSION[$orderDetailsSession]);die;
    $newItemArr = array();
    if (isset($_SESSION[$orderDetailsSession])) {
        $OrderDetails = json_encode($_SESSION[$orderDetailsSession]);
        $OrderDetails = json_decode(stripcslashes($OrderDetails), true);
        if (count($OrderDetails) > 0) {
            unset($_SESSION[$orderDetailsSession]);
            $getItemStatus = $obj->MySQLSelect("SELECT iMenuItemId,eStatus FROM menu_items");
            //echo "<pre>";print_r($_SESSION[$orderDetailsSession]);die;
            $itemStatusArr = array();
            for ($l = 0; $l < count($getItemStatus); $l++) {
                $itemStatusArr[$getItemStatus[$l]['iMenuItemId']] = $getItemStatus[$l]['eStatus'];
            }
            ///echo "<pre>";print_r($itemStatusArr);die;
            for ($j = 0; $j < count($OrderDetails); $j++) {
                $itemId = $OrderDetails[$j]['iMenuItemId'];
                $itemStatus = "Inactive";
                if (isset($itemStatusArr[$itemId])) {
                    $itemStatus = $itemStatusArr[$itemId];
                }
                if (strtolower(trim($itemStatus)) != "inactive") {
                    $newItemArr[] = $OrderDetails[$j];
                }
            }
        }
    }
    $_SESSION[$orderDetailsSession] = $newItemArr;
}

// Added By HJ On 18-01-2020 For Check Cart's Item Status 141 Mantis Bug - #2367 End
function getCurrencyUserWiseMr($user_type) {
    //$user_type = check_user_mr();
    $orderCurrencyNameSession = "MANUAL_ORDER_CURRENCY_NAME_" . strtoupper($user_type);
    if ($user_type == 'admin') {
        $_SESSION[$orderCurrencyNameSession] = $_SESSION["sess_currency"];
        $_SESSION["sess_vCurrency"] = $_SESSION["sess_currency"];
    } else if ($user_type == 'store' && $sess_eSystem = 'DeliverAll') {
        $_SESSION[$orderCurrencyNameSession] = $_SESSION["sess_currency"];
    } else if ($user_type == 'user') {
        $_SESSION[$orderCurrencyNameSession] = $_SESSION["sess_vCurrency"];
    }
}


function checkPaymentMethod($iUserId) {
    global $APP_PAYMENT_METHOD;
    $servFields = 'vStripeToken,vCreditCard,vStripeCusId,vXenditToken,vBrainTreeCustEmail';
    $ServiceCategoryData = get_value('register_user', $servFields, 'iUserId', $iServiceId);
    $vStripeCusId = $ServiceCategoryData[0]['vStripeToken'];
    $vCreditCard = $ServiceCategoryData[0]['vCreditCard'];
    $vStripeCusId = $ServiceCategoryData[0]['vStripeCusId'];
    $vXenditToken = $ServiceCategoryData[0]['vXenditToken'];
    $isCardEmapy = 'No';
    if ($APP_PAYMENT_METHOD == "Stripe") {
        if (empty($vStripeCusId)) {
            $isCardEmapy = 'Yes';
        }
    } else if ($APP_PAYMENT_METHOD == "Braintree") {
        $vBrainTreeCustEmail = $ServiceCategoryData[0]['vBrainTreeCustEmail'];

        if (empty($vBrainTreeCustEmail)) {
            $isCardEmapy = 'Yes';
        } else if (empty($vCreditCard)) {
            $isCardEmapy = 'Yes';
        }
    } else if ($APP_PAYMENT_METHOD == "Paymaya" || $APP_PAYMENT_METHOD == "Omise" || $APP_PAYMENT_METHOD == "Adyen") {
        if (empty($vCreditCard)) {
            $isCardEmapy = 'Yes';
        }
    } else if ($APP_PAYMENT_METHOD == "Xendit") {
        if (empty($vXenditToken)) {
            $isCardEmapy = 'Yes';
        } else if (empty($vCreditCard)) {
            $isCardEmapy = 'Yes';
        }
    }
    return $isCardEmapy;
}

//Added By HJ On 25-01-2019 For Get Custome Topping Data End
function getMenuCustomeAllToppings($iMenuItemId, $currencySymbol, $Ratio, $vLanguage, $eFor) {
    global $obj, $generalobj, $tconfig;
    //ini_set("display_errors", 1);
    //echo "<pre>";
    //error_reporting(E_ALL);
    $returnArr = array();
    $ssql = "";
    //echo $eFor;die
    if ($eFor == 1) {
        $ssql = "";
        //$ssql .= " AND mo.eStatus = 'Active' ";
        // $languageLabelsArr = getLanguageLabelsArr($vLanguage,"1");
        $sql = "SELECT mo.*,fm.iFoodMenuId,co.iCompanyId FROM menuitem_options as mo LEFT JOIN menu_items as mi ON mo.iMenuItemId=mi.iMenuItemId LEFT JOIN food_menu as fm ON mi.iFoodMenuId=fm.iFoodMenuId LEFT JOIN company as co ON fm.iCompanyId=co.iCompanyId WHERE co.iCompanyId IN ($iMenuItemId) AND mi.eStatus = 'Active' AND `eOptionType` NOT IN ('Options',  'Addon') AND mo.eStatus='Active' AND mi.eAvailable = 'Yes'" . $ssql;
    } else {
        $sql = "SELECT mo.*,fm.iFoodMenuId FROM menuitem_options mo LEFT JOIN menu_items as mi ON mo.iMenuItemId=mi.iMenuItemId LEFT JOIN food_menu as fm ON mi.iFoodMenuId=fm.iFoodMenuId WHERE mo.iMenuItemId IN ($iMenuItemId) AND `eOptionType` NOT IN ('Options',  'Addon') AND mo.eStatus = 'Active'";
    }
    //echo $sql;die;
    $db_options_data = $obj->MySQLSelect($sql);
    //echo "<pre>";print_r($db_options_data);die;
    $menuItemArr = array();
    if (count($db_options_data) > 0) {
        for ($i = 0; $i < count($db_options_data); $i++) {
            if (isset($db_options_data[$i])) {
                $iMenuItemId = $db_options_data[$i]['iMenuItemId'];
                $eOptionType = $db_options_data[$i]['eOptionType'];
                $eOptionInputType = $db_options_data[$i]['eOptionInputType'];
                $vOptionMinSelection = $db_options_data[$i]['vOptionMinSelection'];
                $vOptionMaxSelection = $db_options_data[$i]['vOptionMaxSelection'];
                $fPrice = $db_options_data[$i]['fPrice'];
                $fUserPrice = number_format($fPrice * $Ratio, 2);
                $fUserPriceWithSymbol = $currencySymbol . " " . $fUserPrice;
                $db_options_data[$i]['fUserPrice'] = $fUserPrice;
                $db_options_data[$i]['fUserPriceWithSymbol'] = $fUserPriceWithSymbol;
                //$returnArr = array();
                $returnArr[$iMenuItemId][$eOptionType]['eOptionType'] = $eOptionType;
                $returnArr[$iMenuItemId][$eOptionType]['iMenuItemId'] = $db_options_data[$i]['iMenuItemId'];
                $returnArr[$iMenuItemId][$eOptionType]['iFoodMenuId'] = $db_options_data[$i]['iFoodMenuId'];
                $returnArr[$iMenuItemId][$eOptionType]['eOptionInputType'] = $eOptionInputType;
                $returnArr[$iMenuItemId][$eOptionType]['vOptionMinSelection'] = $vOptionMinSelection;
                $returnArr[$iMenuItemId][$eOptionType]['vOptionMaxSelection'] = $vOptionMaxSelection;
                $returnArr[$iMenuItemId][$eOptionType]['subItemArr'][] = $db_options_data[$i];
                //$menuItemArr[$iMenuItemId][] =  $returnArr;
            }
        }
    }
    //echo "<pre>";print_r($returnArr);die;
    $finalReturnArr = array();
    foreach ($returnArr as $key => $val) {
        foreach ($val as $key1 => $val2) {
            //echo "<pre>";print_r($val2);die;
            $finalReturnArr[$key][] = $val2;
        }
    }
    //echo "<pre>";print_r($finalReturnArr);die;
    return $finalReturnArr;
}

//Added By HJ On 25-01-2019 For Get Custome Topping Data End
//Added By HJ On 09-05-2019 For Get All Option and Addon Price Array Start
function getAllOptionAddonPriceArr() {
    global $obj;
    $optionPriceArr = array();
    $getAllMenuOptionPrice = $obj->MySQLSelect("select iMenuItemId,fPrice,iOptionId from `menuitem_options`");
    for ($r = 0; $r < count($getAllMenuOptionPrice); $r++) {
        if (isset($optionPriceArr[$getAllMenuOptionPrice[$r]['iOptionId']])) {
            $optionPriceArr[$getAllMenuOptionPrice[$r]['iOptionId']] += $getAllMenuOptionPrice[$r]['fPrice'];
        } else {
            $optionPriceArr[$getAllMenuOptionPrice[$r]['iOptionId']] = $getAllMenuOptionPrice[$r]['fPrice'];
        }
    }
    return $optionPriceArr;
}

//Added By HJ On 09-05-2019 For Get All Option and Addon Price Array End
//Added By HJ On 09-05-2019 For Get All Menu Items Price Array Start
function getAllMenuItemPriceArr() {
    global $obj;
    $ordItemPriceArr = array();
    $getAllItemsPrice = $obj->MySQLSelect("select fPrice,iMenuItemId from menu_items");
    for ($ai = 0; $ai < count($getAllItemsPrice); $ai++) {
        $ordItemPriceArr[$getAllItemsPrice[$ai]['iMenuItemId']] = $getAllItemsPrice[$ai]['fPrice'];
    }
    return $ordItemPriceArr;
}

//Added By HJ On 09-05-2019 For Get All Menu Items Price Array End
//Added By HJ On 09-05-2019 For Manage Manual Store Order Session Start
function changeManualOrderSession($existSession, $changeSession) {
    session_start();
    $existSession = strtoupper($existSession);
    $changeSession = strtoupper($changeSession);
    $userSession = "MANUAL_ORDER_";
    $orderDetailsSession = "ORDER_DETAILS_";
    $orderServiceSession = "MAUAL_ORDER_SERVICE_";
    $orderUserIdSession = "MANUAL_ORDER_USERID_";
    $orderAddressIdSession = "MANUAL_ORDER_ADDRESSID_";
    $orderAddressSession = "MANUAL_ORDER_ADDRESS_";
    $orderCouponSession = "MANUAL_ORDER_PROMOCODE_";
    $orderCouponNameSession = "MANUAL_ORDER_PROMOCODE_NAME_";
    $orderCurrencyNameSession = "MANUAL_ORDER_CURRENCY_NAME_";
    $orderLatitudeSession = "MANUAL_ORDER_LATITUDE_";
    $orderLongitudeSession = "MANUAL_ORDER_LONGITUDE_";
    $orderServiceNameSession = "MANUAL_ORDER_SERVICE_NAME_";
    $orderDataSession = "MANUAL_ORDER_DATA_";
    $orderUserSession = "MANUAL_ORDER_USER_";
    $orderUserNameSession = "MANUAL_ORDER_USER_NAME_";
    $orderCompanyNameSession = "MANUAL_ORDER_COMPANY_NAME_";
    $orderUserEmailSession = "MANUAL_ORDER_USER_EMAIL_";
    $orderStoreIdSession = "MANUAL_ORDER_STORE_ID_";

    $_SESSION[$userSession . $changeSession] = $_SESSION[$userSession . $existSession];
    $_SESSION[$orderDetailsSession . $changeSession] = $_SESSION[$orderDetailsSession . $existSession];
    $_SESSION[$orderServiceSession . $changeSession] = $_SESSION[$orderServiceSession . $existSession];
    $_SESSION[$orderUserIdSession . $changeSession] = $_SESSION[$orderUserIdSession . $existSession];
    $_SESSION[$orderAddressIdSession . $changeSession] = $_SESSION[$orderAddressIdSession . $existSession];
    $_SESSION[$orderAddressSession . $changeSession] = $_SESSION[$orderAddressSession . $existSession];
    $_SESSION[$orderCouponSession . $changeSession] = $_SESSION[$orderCouponSession . $existSession];
    //$_SESSION[$orderCouponNameSession . $changeSession] = $_SESSION[$orderCouponNameSession . $existSession];
    //$_SESSION[$orderCouponSession . $changeSession] = $_SESSION[$orderCouponSession . $existSession];
    $_SESSION[$orderCouponNameSession . $changeSession] = "";
    $_SESSION[$orderCurrencyNameSession . $changeSession] = "";
    $_SESSION[$orderLatitudeSession . $changeSession] = $_SESSION[$orderLatitudeSession . $existSession];
    $_SESSION[$orderLongitudeSession . $changeSession] = $_SESSION[$orderLongitudeSession . $existSession];
    $_SESSION[$orderServiceNameSession . $changeSession] = $_SESSION[$orderServiceNameSession . $existSession];
    $_SESSION[$orderDataSession . $changeSession] = $_SESSION[$orderDataSession . $existSession];
    $_SESSION[$orderUserSession . $changeSession] = $_SESSION[$orderUserSession . $existSession];
    $_SESSION[$orderUserNameSession . $changeSession] = $_SESSION[$orderUserNameSession . $existSession];
    $_SESSION[$orderCompanyNameSession . $changeSession] = $_SESSION[$orderCompanyNameSession . $existSession];
    $_SESSION[$orderUserEmailSession . $changeSession] = $_SESSION[$orderUserEmailSession . $existSession];
    $_SESSION[$orderStoreIdSession . $changeSession] = $_SESSION[$orderStoreIdSession . $existSession];

    unset($_SESSION[$orderDetailsSession . $existSession]);
    unset($_SESSION[$userSession . $existSession]);
    unset($_SESSION[$orderUserSession . $existSession]);
    unset($_SESSION[$orderServiceSession . $existSession]);
    unset($_SESSION[$orderUserIdSession . $existSession]);
    unset($_SESSION[$orderAddressIdSession . $existSession]);
    unset($_SESSION[$orderCouponSession . $existSession]);
    unset($_SESSION[$orderCouponNameSession . $existSession]);

    unset($_SESSION[$orderCurrencyNameSession . $existSession]);
    //unset($_SESSION['sess_currentpage_url_mr']);
    unset($_SESSION[$orderLatitudeSession . $existSession]);
    unset($_SESSION[$orderLongitudeSession . $existSession]);
    unset($_SESSION[$orderAddressSession . $existSession]);
    unset($_SESSION[$orderDataSession . $existSession]);

    unset($_SESSION[$orderUserNameSession . $existSession]);
    unset($_SESSION[$orderCompanyNameSession . $existSession]);
    unset($_SESSION[$orderUserEmailSession . $existSession]);
    unset($_SESSION[$orderStoreIdSession . $existSession]);
    unset($_SESSION[$orderServiceNameSession . $existSession]);
}

//Added By HJ On 09-05-2019 For Manage Manual Store Order Session End

function getCurrentActiveTripsTotal($iMemberId) {
    global $obj;
    // $sql_trips_chk = "SELECT iTripId FROM trips as tr WHERE tr.iActive != 'Canceled' AND tr.iActive != 'Finished' AND tr.tUserWalletBalance != '' AND tr.vTripPaymentMode = 'Card' AND tr.iUserId = '" . $iMemberId . "'";
    $sql_trips_chk = "SELECT iTripId, iOrderId FROM trips as tr WHERE tr.iActive != 'Canceled' AND tr.iActive != 'Finished' AND tr.tUserWalletBalance != '' AND tr.tUserWalletBalance != '0' AND tr.iUserId = '" . $iMemberId . "'";
    $data_trips = $obj->MySQLSelect($sql_trips_chk);
    $totalCount = 0;
    if (strtoupper(DELIVERALL) == "YES") {
        $ssql_orderIds = "";
        if (!empty($data_trips) && count($data_trips) > 0) {
            for ($i = 0; $i < count($data_trips); $i++) {
                if (!empty($data_trips[$i]['iOrderId']) && $data_trips[$i]['iOrderId'] > 0) {
                    $ssql_orderIds = $ssql_orderIds == "" ? " AND NOT IN( " . $data_trips[$i]['iOrderId'] : $ssql_orderIds . ", " . $data_trips[$i]['iOrderId'];
                }
            }

            if (!empty($ssql_orderIds)) {
                $ssql_orderIds = $ssql_orderIds . ")";
            }
        }
        // $sql_orders_chk = "SELECT iOrderId FROM orders as ord WHERE ord.ePaid = 'No' ".$ssql_orderIds." AND ord.iStatusCode IN(1,2,4,5,12) AND ord.ePaymentOption = 'Card' AND ord.iUserId = '" . $iMemberId . "'";
        $sql_orders_chk = "SELECT iOrderId FROM orders as ord WHERE ord.ePaid = 'No' " . $ssql_orderIds . " AND ord.iStatusCode IN(1,2,4,5,12)  AND ord.tUserWalletBalance != '' AND ord.tUserWalletBalance != '0' AND ord.iUserId = '" . $iMemberId . "'";
        $data_orders = $obj->MySQLSelect($sql_orders_chk);
    }

    $tripIdsArr = array();
    $orderIdsArr = array();
    if (!empty($data_trips) && count($data_trips) > 0) {
        $totalCount = count($data_trips);

        foreach ($data_trips as $data_trips_tmp) {
            $tripIdsArr[] = $data_trips_tmp['iTripId'];
        }
    }

    if (!empty($data_orders) && count($data_orders) > 0) {
        $totalCount = $totalCount + count($data_orders);

        foreach ($data_orders as $data_orders_tmp) {
            $orderIdsArr[] = $data_orders_tmp['iOrderId'];
        }
    }

    $returnArr['TotalCount'] = $totalCount;
    $returnArr['ActiveTripIds'] = $tripIdsArr;
    $returnArr['ActiveOrderIds'] = $orderIdsArr;
    return $returnArr;
}

function searchForTitle($title, $array) {
   foreach ($array as $key => $val) {
       if ($val['vTitle'] === $title) {
           return $key;
       }
   }
   return null;
} 

?>