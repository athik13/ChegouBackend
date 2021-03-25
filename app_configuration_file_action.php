<?php
//ini_set('display_errors', 1);
//ini_set('display_startup_errors', 1);
$svn_url = "";
if (isset($_POST['svnurl'])) {
    $svn_url = $_POST['svnurl'];
}
if (isset($_POST['APP_CONFIG_PARAMS_PACKAGE']) && !empty($_POST['APP_CONFIG_PARAMS_PACKAGE'])) {
    $APP_CONFIG_PARAMS_PACKAGE = json_decode(urldecode($_POST['APP_CONFIG_PARAMS_PACKAGE']));

    // echo "<pre>";print_r($APP_CONFIG_PARAMS_PACKAGE);die;
    if (property_exists($APP_CONFIG_PARAMS_PACKAGE, 'GeneralDeviceType')) {
        $PACKAGE_NAME = $GeneralDeviceType = $UserType = "";
        if (isset($APP_CONFIG_PARAMS_PACKAGE->GeneralDeviceType)) {
            $GeneralDeviceType = $APP_CONFIG_PARAMS_PACKAGE->GeneralDeviceType;
        }
        if (isset($APP_CONFIG_PARAMS_PACKAGE->PACKAGE_NAME)) {
            $PACKAGE_NAME = $APP_CONFIG_PARAMS_PACKAGE->PACKAGE_NAME;
        }
        if (isset($APP_CONFIG_PARAMS_PACKAGE->UserType)) {
            $UserType = $APP_CONFIG_PARAMS_PACKAGE->UserType;
        }
        //$svn_url = "http://192.168.1.120:8080/svn/v3c_prodution_test_v5_workspace/Android/Taxi/CubeRideEnterprise/PassengerApp";
        $svnUsername = $_POST['svnUsername'];
        $svnPassword = $_POST['svnPassword'];
        $arrOfGeneratedLinks = $arrOfLinksToDelete = array();
        if (property_exists($APP_CONFIG_PARAMS_PACKAGE, 'WAYBILL_MODULE') && $APP_CONFIG_PARAMS_PACKAGE->WAYBILL_MODULE == "Yes") {
            if (property_exists($APP_CONFIG_PARAMS_PACKAGE, 'WAYBILL_MODULE_FILES') && !empty($APP_CONFIG_PARAMS_PACKAGE->WAYBILL_MODULE_FILES)) {
                $RENTAL_SERVICE_FILES_arr = explode(",", $APP_CONFIG_PARAMS_PACKAGE->WAYBILL_MODULE_FILES);
                foreach ($RENTAL_SERVICE_FILES_arr as $value) {
                    $arrOfGeneratedLinks[] = $value;
                }
            }
        }
        if (property_exists($APP_CONFIG_PARAMS_PACKAGE, 'VOIP_SERVICE') && $APP_CONFIG_PARAMS_PACKAGE->VOIP_SERVICE == "Yes") {
            if (property_exists($APP_CONFIG_PARAMS_PACKAGE, 'VOIP_SERVICE_FILES') && !empty($APP_CONFIG_PARAMS_PACKAGE->VOIP_SERVICE_FILES)) {
                $RENTAL_SERVICE_FILES_arr = explode(",", $APP_CONFIG_PARAMS_PACKAGE->VOIP_SERVICE_FILES);
                foreach ($RENTAL_SERVICE_FILES_arr as $value) {
                    $arrOfGeneratedLinks[] = $value;
                }
            }
        }
        if (property_exists($APP_CONFIG_PARAMS_PACKAGE, 'ADVERTISEMENT_MODULE') && $APP_CONFIG_PARAMS_PACKAGE->ADVERTISEMENT_MODULE == "Yes") {
            if (property_exists($APP_CONFIG_PARAMS_PACKAGE, 'ADVERTISEMENT_MODULE_FILES') && !empty($APP_CONFIG_PARAMS_PACKAGE->ADVERTISEMENT_MODULE_FILES)) {
                $RENTAL_SERVICE_FILES_arr = explode(",", $APP_CONFIG_PARAMS_PACKAGE->ADVERTISEMENT_MODULE_FILES);
                foreach ($RENTAL_SERVICE_FILES_arr as $value) {
                    $arrOfGeneratedLinks[] = $value;
                }
            }
        }
        if (property_exists($APP_CONFIG_PARAMS_PACKAGE, 'LINKEDIN_MODULE') && $APP_CONFIG_PARAMS_PACKAGE->LINKEDIN_MODULE == "Yes") {
            if (property_exists($APP_CONFIG_PARAMS_PACKAGE, 'LINKEDIN_MODULE_FILES') && !empty($APP_CONFIG_PARAMS_PACKAGE->LINKEDIN_MODULE_FILES)) {
                $RENTAL_SERVICE_FILES_arr = explode(",", $APP_CONFIG_PARAMS_PACKAGE->LINKEDIN_MODULE_FILES);
                foreach ($RENTAL_SERVICE_FILES_arr as $value) {
                    $arrOfGeneratedLinks[] = $value;
                }
            }
        }
        if (property_exists($APP_CONFIG_PARAMS_PACKAGE, 'POOL_MODULE') && $APP_CONFIG_PARAMS_PACKAGE->POOL_MODULE == "Yes") {
            if (property_exists($APP_CONFIG_PARAMS_PACKAGE, 'POOL_MODULE_FILES') && !empty($APP_CONFIG_PARAMS_PACKAGE->POOL_MODULE_FILES)) {
                $RENTAL_SERVICE_FILES_arr = explode(",", $APP_CONFIG_PARAMS_PACKAGE->POOL_MODULE_FILES);
                foreach ($RENTAL_SERVICE_FILES_arr as $value) {
                    $arrOfGeneratedLinks[] = $value;
                }
            }
        }
        if (property_exists($APP_CONFIG_PARAMS_PACKAGE, 'CARD_IO') && $APP_CONFIG_PARAMS_PACKAGE->CARD_IO == "Yes") {
            if (property_exists($APP_CONFIG_PARAMS_PACKAGE, 'CARD_IO_FILES') && !empty($APP_CONFIG_PARAMS_PACKAGE->CARD_IO_FILES)) {
                $RENTAL_SERVICE_FILES_arr = explode(",", $APP_CONFIG_PARAMS_PACKAGE->CARD_IO_FILES);
                foreach ($RENTAL_SERVICE_FILES_arr as $value) {
                    $arrOfGeneratedLinks[] = $value;
                }
            }
        }
        if (property_exists($APP_CONFIG_PARAMS_PACKAGE, 'LIVE_CHAT') && $APP_CONFIG_PARAMS_PACKAGE->LIVE_CHAT == "Yes") {
            if (property_exists($APP_CONFIG_PARAMS_PACKAGE, 'LIVE_CHAT_FILES') && !empty($APP_CONFIG_PARAMS_PACKAGE->LIVE_CHAT_FILES)) {
                $RENTAL_SERVICE_FILES_arr = explode(",", $APP_CONFIG_PARAMS_PACKAGE->LIVE_CHAT_FILES);
                foreach ($RENTAL_SERVICE_FILES_arr as $value) {
                    $arrOfGeneratedLinks[] = $value;
                }
            }
        }
        if (property_exists($APP_CONFIG_PARAMS_PACKAGE, 'DELIVER_ALL') && $APP_CONFIG_PARAMS_PACKAGE->DELIVER_ALL == "Yes") {
            if (property_exists($APP_CONFIG_PARAMS_PACKAGE, 'DELIVER_ALL_FILES') && !empty($APP_CONFIG_PARAMS_PACKAGE->DELIVER_ALL_FILES)) {
                $RENTAL_SERVICE_FILES_arr = explode(",", $APP_CONFIG_PARAMS_PACKAGE->DELIVER_ALL_FILES);
                foreach ($RENTAL_SERVICE_FILES_arr as $value) {
                    $arrOfGeneratedLinks[] = $value;
                }
            }
        }
        if (property_exists($APP_CONFIG_PARAMS_PACKAGE, 'MULTI_DELIVERY') && $APP_CONFIG_PARAMS_PACKAGE->MULTI_DELIVERY == "Yes") {
            if (property_exists($APP_CONFIG_PARAMS_PACKAGE, 'MULTI_DELIVERY_FILES') && !empty($APP_CONFIG_PARAMS_PACKAGE->MULTI_DELIVERY_FILES)) {
                $RENTAL_SERVICE_FILES_arr = explode(",", $APP_CONFIG_PARAMS_PACKAGE->MULTI_DELIVERY_FILES);
                foreach ($RENTAL_SERVICE_FILES_arr as $value) {
                    $arrOfGeneratedLinks[] = $value;
                }
            }
        }
        if (property_exists($APP_CONFIG_PARAMS_PACKAGE, 'UBERX_SERVICE') && $APP_CONFIG_PARAMS_PACKAGE->UBERX_SERVICE == "Yes") {
            if (property_exists($APP_CONFIG_PARAMS_PACKAGE, 'UBERX_FILES') && !empty($APP_CONFIG_PARAMS_PACKAGE->UBERX_FILES)) {
                $RENTAL_SERVICE_FILES_arr = explode(",", $APP_CONFIG_PARAMS_PACKAGE->UBERX_FILES);
                foreach ($RENTAL_SERVICE_FILES_arr as $value) {
                    $arrOfGeneratedLinks[] = $value;
                }
            }
        }
        if (property_exists($APP_CONFIG_PARAMS_PACKAGE, 'ON_GOING_JOB_SECTION') && $APP_CONFIG_PARAMS_PACKAGE->ON_GOING_JOB_SECTION == "Yes") {
            if (property_exists($APP_CONFIG_PARAMS_PACKAGE, 'ON_GOING_JOB_SECTION_FILES') && !empty($APP_CONFIG_PARAMS_PACKAGE->ON_GOING_JOB_SECTION_FILES)) {
                $RENTAL_SERVICE_FILES_arr = explode(",", $APP_CONFIG_PARAMS_PACKAGE->ON_GOING_JOB_SECTION_FILES);
                foreach ($RENTAL_SERVICE_FILES_arr as $value) {
                    $arrOfGeneratedLinks[] = $value;
                }
            }
        }
        if (property_exists($APP_CONFIG_PARAMS_PACKAGE, 'COMMON_DELIVERY_TYPE_SECTION') && $APP_CONFIG_PARAMS_PACKAGE->COMMON_DELIVERY_TYPE_SECTION == "Yes") {
            if (property_exists($APP_CONFIG_PARAMS_PACKAGE, 'COMMON_DELIVERY_TYPE_SECTION_FILES') && !empty($APP_CONFIG_PARAMS_PACKAGE->COMMON_DELIVERY_TYPE_SECTION_FILES)) {
                $RENTAL_SERVICE_FILES_arr = explode(",", $APP_CONFIG_PARAMS_PACKAGE->COMMON_DELIVERY_TYPE_SECTION_FILES);
                foreach ($RENTAL_SERVICE_FILES_arr as $value) {
                    $arrOfGeneratedLinks[] = $value;
                }
            }
        }
        if (property_exists($APP_CONFIG_PARAMS_PACKAGE, 'NEWS_SECTION') && $APP_CONFIG_PARAMS_PACKAGE->NEWS_SECTION == "Yes") {
            if (property_exists($APP_CONFIG_PARAMS_PACKAGE, 'NEWS_SERVICE_FILES') && !empty($APP_CONFIG_PARAMS_PACKAGE->NEWS_SERVICE_FILES)) {
                $RENTAL_SERVICE_FILES_arr = explode(",", $APP_CONFIG_PARAMS_PACKAGE->NEWS_SERVICE_FILES);
                foreach ($RENTAL_SERVICE_FILES_arr as $value) {
                    $arrOfGeneratedLinks[] = $value;
                }
            }
        }
        if (property_exists($APP_CONFIG_PARAMS_PACKAGE, 'RENTAL_FEATURE') && $APP_CONFIG_PARAMS_PACKAGE->RENTAL_FEATURE == "Yes") {
            if (property_exists($APP_CONFIG_PARAMS_PACKAGE, 'RENTAL_SERVICE_FILES') && !empty($APP_CONFIG_PARAMS_PACKAGE->RENTAL_SERVICE_FILES)) {
                $RENTAL_SERVICE_FILES_arr = explode(",", $APP_CONFIG_PARAMS_PACKAGE->RENTAL_SERVICE_FILES);
                foreach ($RENTAL_SERVICE_FILES_arr as $value) {
                    $arrOfGeneratedLinks[] = $value;
                }
            }
        }
        if (property_exists($APP_CONFIG_PARAMS_PACKAGE, 'BUSINESS_PROFILE_FEATURE') && $APP_CONFIG_PARAMS_PACKAGE->BUSINESS_PROFILE_FEATURE == "Yes") {
            if (property_exists($APP_CONFIG_PARAMS_PACKAGE, 'BUSINESS_PROFILE_FILES') && !empty($APP_CONFIG_PARAMS_PACKAGE->BUSINESS_PROFILE_FILES)) {
                $RENTAL_SERVICE_FILES_arr = explode(",", $APP_CONFIG_PARAMS_PACKAGE->BUSINESS_PROFILE_FILES);
                foreach ($RENTAL_SERVICE_FILES_arr as $value) {
                    $arrOfGeneratedLinks[] = $value;
                }
            }
        }
        if (property_exists($APP_CONFIG_PARAMS_PACKAGE, 'DELIVERY_MODULE') && $APP_CONFIG_PARAMS_PACKAGE->DELIVERY_MODULE == "Yes") {
            if (property_exists($APP_CONFIG_PARAMS_PACKAGE, 'DELIVERY_MODULE_FILES') && !empty($APP_CONFIG_PARAMS_PACKAGE->DELIVERY_MODULE_FILES)) {
                $RENTAL_SERVICE_FILES_arr = explode(",", $APP_CONFIG_PARAMS_PACKAGE->DELIVERY_MODULE_FILES);
                foreach ($RENTAL_SERVICE_FILES_arr as $value) {
                    $arrOfGeneratedLinks[] = $value;
                }
            }
        }
        if (property_exists($APP_CONFIG_PARAMS_PACKAGE, 'RIDE_SECTION') && $APP_CONFIG_PARAMS_PACKAGE->RIDE_SECTION == "Yes") {
            if (property_exists($APP_CONFIG_PARAMS_PACKAGE, 'RIDE_SECTION_FILES') && !empty($APP_CONFIG_PARAMS_PACKAGE->RIDE_SECTION_FILES)) {
                $RENTAL_SERVICE_FILES_arr = explode(",", $APP_CONFIG_PARAMS_PACKAGE->RIDE_SECTION_FILES);
                foreach ($RENTAL_SERVICE_FILES_arr as $value) {
                    $arrOfGeneratedLinks[] = $value;
                }
            }
        }

        if (property_exists($APP_CONFIG_PARAMS_PACKAGE, 'RDU_SECTION') && $APP_CONFIG_PARAMS_PACKAGE->RDU_SECTION == "Yes") {
            if (property_exists($APP_CONFIG_PARAMS_PACKAGE, 'RDU_SECTION_FILES') && !empty($APP_CONFIG_PARAMS_PACKAGE->RDU_SECTION_FILES)) {
                $RENTAL_SERVICE_FILES_arr = explode(",", $APP_CONFIG_PARAMS_PACKAGE->RDU_SECTION_FILES);
                foreach ($RENTAL_SERVICE_FILES_arr as $value) {
                    $arrOfGeneratedLinks[] = $value;
                }
            }
        }

        if (property_exists($APP_CONFIG_PARAMS_PACKAGE, 'FAV_DRIVER_SECTION') && $APP_CONFIG_PARAMS_PACKAGE->FAV_DRIVER_SECTION == "Yes") {
            if (property_exists($APP_CONFIG_PARAMS_PACKAGE, 'FAV_DRIVER_SECTION_FILES') && !empty($APP_CONFIG_PARAMS_PACKAGE->FAV_DRIVER_SECTION_FILES)) {
                $FAV_DRIVER_SECTION_FILES_arr = explode(",", $APP_CONFIG_PARAMS_PACKAGE->FAV_DRIVER_SECTION_FILES);
                foreach ($FAV_DRIVER_SECTION_FILES_arr as $value) {
                    $arrOfGeneratedLinks[] = $value;
                }
            }
        }

        if (property_exists($APP_CONFIG_PARAMS_PACKAGE, 'BOOK_FOR_ELSE_SECTION') && $APP_CONFIG_PARAMS_PACKAGE->BOOK_FOR_ELSE_SECTION == "Yes") {
            if (property_exists($APP_CONFIG_PARAMS_PACKAGE, 'BOOK_FOR_ELSE_SECTION_FILES') && !empty($APP_CONFIG_PARAMS_PACKAGE->BOOK_FOR_ELSE_SECTION_FILES)) {
                $BOOK_FOR_ELSE_SECTION_FILES_arr = explode(",", $APP_CONFIG_PARAMS_PACKAGE->BOOK_FOR_ELSE_SECTION_FILES);
                foreach ($BOOK_FOR_ELSE_SECTION_FILES_arr as $value) {
                    $arrOfGeneratedLinks[] = $value;
                }
            }
        }

        if (property_exists($APP_CONFIG_PARAMS_PACKAGE, 'END_OF_DAY_TRIP_SECTION') && $APP_CONFIG_PARAMS_PACKAGE->END_OF_DAY_TRIP_SECTION == "Yes") {
            if (property_exists($APP_CONFIG_PARAMS_PACKAGE, 'END_OF_DAY_TRIP_SECTION_FILES') && !empty($APP_CONFIG_PARAMS_PACKAGE->END_OF_DAY_TRIP_SECTION_FILES)) {
                $END_OF_DAY_TRIP_SECTION_FILES_arr = explode(",", $APP_CONFIG_PARAMS_PACKAGE->END_OF_DAY_TRIP_SECTION_FILES);
                foreach ($END_OF_DAY_TRIP_SECTION_FILES_arr as $value) {
                    $arrOfGeneratedLinks[] = $value;
                }
            }
        }

        if (property_exists($APP_CONFIG_PARAMS_PACKAGE, 'STOP_OVER_POINT_SECTION') && $APP_CONFIG_PARAMS_PACKAGE->STOP_OVER_POINT_SECTION == "Yes") {
            if (property_exists($APP_CONFIG_PARAMS_PACKAGE, 'STOP_OVER_POINT_SECTION_FILES') && !empty($APP_CONFIG_PARAMS_PACKAGE->STOP_OVER_POINT_SECTION_FILES)) {
                $STOP_OVER_POINT_SECTION_FILES_arr = explode(",", $APP_CONFIG_PARAMS_PACKAGE->STOP_OVER_POINT_SECTION_FILES);
                foreach ($STOP_OVER_POINT_SECTION_FILES_arr as $value) {
                    $arrOfGeneratedLinks[] = $value;
                }
            }
        }

        if (property_exists($APP_CONFIG_PARAMS_PACKAGE, 'DRIVER_SUBSCRIPTION_SECTION') && $APP_CONFIG_PARAMS_PACKAGE->DRIVER_SUBSCRIPTION_SECTION == "Yes") {
            if (property_exists($APP_CONFIG_PARAMS_PACKAGE, 'DRIVER_SUBSCRIPTION_SECTION_FILES') && !empty($APP_CONFIG_PARAMS_PACKAGE->DRIVER_SUBSCRIPTION_SECTION_FILES)) {
                $DRIVER_SUBSCRIPTION_SECTION_FILES_arr = explode(",", $APP_CONFIG_PARAMS_PACKAGE->DRIVER_SUBSCRIPTION_SECTION_FILES);
                foreach ($DRIVER_SUBSCRIPTION_SECTION_FILES_arr as $value) {
                    $arrOfGeneratedLinks[] = $value;
                }
            }
        }

        if (property_exists($APP_CONFIG_PARAMS_PACKAGE, 'GO_PAY_SECTION') && $APP_CONFIG_PARAMS_PACKAGE->GO_PAY_SECTION == "Yes") {
            if (property_exists($APP_CONFIG_PARAMS_PACKAGE, 'GO_PAY_SECTION_FILES') && !empty($APP_CONFIG_PARAMS_PACKAGE->GO_PAY_SECTION_FILES)) {
                $GO_PAY_SECTION_FILES_arr = explode(",", $APP_CONFIG_PARAMS_PACKAGE->GO_PAY_SECTION_FILES);
                foreach ($GO_PAY_SECTION_FILES_arr as $value) {
                    $arrOfGeneratedLinks[] = $value;
                }
            }
        }

        if (property_exists($APP_CONFIG_PARAMS_PACKAGE, 'DONATION_SECTION') && $APP_CONFIG_PARAMS_PACKAGE->DONATION_SECTION == "Yes") {
            if (property_exists($APP_CONFIG_PARAMS_PACKAGE, 'DONATION_SECTION_FILES') && !empty($APP_CONFIG_PARAMS_PACKAGE->DONATION_SECTION_FILES)) {
                $DONATION_SECTION_FILES_arr = explode(",", $APP_CONFIG_PARAMS_PACKAGE->DONATION_SECTION_FILES);
                foreach ($DONATION_SECTION_FILES_arr as $value) {
                    $arrOfGeneratedLinks[] = $value;
                }
            }
        }
        if (property_exists($APP_CONFIG_PARAMS_PACKAGE, 'THERMAL_PRINT_MODULE') && $APP_CONFIG_PARAMS_PACKAGE->THERMAL_PRINT_MODULE == "Yes") {
            if (property_exists($APP_CONFIG_PARAMS_PACKAGE, 'THERMAL_PRINT_MODULE_FILES') && !empty($APP_CONFIG_PARAMS_PACKAGE->THERMAL_PRINT_MODULE_FILES)) {
                $THERMAL_PRINT_MODULE_FILES_arr = explode(",", $APP_CONFIG_PARAMS_PACKAGE->THERMAL_PRINT_MODULE_FILES);
                foreach ($THERMAL_PRINT_MODULE_FILES_arr as $value) {
                    $arrOfGeneratedLinks[] = $value;
                }
            }
        }
        if (strtoupper($GeneralDeviceType) == "ANDROID") {
            foreach ($arrOfGeneratedLinks as $value) {
                if (strpos($value, "res/layout") === 0) {
                    $resourceFileURL = $svn_url . "/app/src/main/" . $value . ".xml";
                    $arrOfLinksToDelete[] = $resourceFileURL;
                } else if (strpos($value, "libs/") === 0) {
                    $resourceFileURL = $svn_url . "/app/" . $value;
                    $arrOfLinksToDelete[] = $resourceFileURL;
                } else {
                    $filePath = "";
                    $filePathArr = explode(".", $value);
                    for ($i = 0; $i < count($filePathArr); $i++) {
                        $filePath = $filePath . "/" . $filePathArr[$i];
                    }
                    $filePath = $svn_url . "/app/src/main/java" . $filePath . ".java";
                    $arrOfLinksToDelete[] = $filePath;
                }
            }
        } else if (strtoupper($GeneralDeviceType) == "IOS") {
            foreach ($arrOfGeneratedLinks as $value) {
                $filePath = $svn_url . "/" . $value;
                $arrOfLinksToDelete[] = $filePath;
            }
        }

        if (count($arrOfLinksToDelete) > 0) {
            foreach ($arrOfLinksToDelete as $value) {
                exec("/usr/bin/svn delete --no-auth-cache -m 'FilterCode' --username '" . $svnUsername . "' --password '" . $svnPassword . "' " . $value, $o, $m);
            }
        }
    }
}
?>
<html>
    <body>
        <div>
            <h1>Process Done! Files has been deleted as per your project and package type</h1>
            <p><strong>Please take update in your project and clean a project with mentioned URL: "<?= $svn_url; ?>"</strong> <br>If you have any trobule on project setup, please consult develpment team.</p>
        </div>
    </body>
</html>