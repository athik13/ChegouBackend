<?php

include_once('common.php');
ini_set('display_errors', 1);
error_reporting(E_ALL);
$tableName = "menu_items";
$imageField = "vImage";
$pkId = "iMenuItemId";
$data_drv = $obj->MySQLSelect("SELECT $imageField,$pkId FROM " . $tableName . " WHERE " . $imageField . " != '' AND eImgDownload='No'");
//echo "<pre>";print_r($data_drv);die;
$menuItemPath = $tconfig['tsite_upload_images_menu_item_path'];
$menuItemUrl = $tconfig['tsite_upload_images_menu_item'];
$imgUrlArr = array("http://cubejekshark.bbcsproducts.com/webimages/upload/MenuItem/", "http://deliverall.bbcsproducts.com/webimages/upload/MenuItem/");
$count = 1;
$tables_arr = array('airport_location_master', 'all_database_details', 'backup_database', 'cab_booking', 'cab_request_now', 'coupon', 'document_list', 'driver_doc', 'driver_location_airport', 'driver_log_report', 'driver_manage_timing', 'driver_preferences', 'driver_request', 'driver_user_messages', 'driver_vehicle', 'location_wise_fare', 'log_file', 'masking_numbers', 'member_log', 'newsletter', 'passenger_requests', 'payments', 'pushnotification_log', 'ratings_user_driver', 'register_driver', 'register_user', 'restricted_negative_area', 'service_pro_amount', 'temp_trip_order_details', 'trips', 'trips_delivery_locations', 'trips_locations', 'trip_call_masking', 'trip_delivery_fields', 'trip_destinations', 'trip_help_detail', 'trip_messages', 'trip_order_details', 'trip_outstanding_amount', 'trip_status_messages', 'trip_times', 'user_address', 'user_emergency_contact', 'user_fave_address', 'user_profile', 'user_wallet', 'visit_address', 'provider_images', "banner_impression", "driver_insurance_report", "master_currency", "hotel_banners", "configurations_payment_log", "orders", "order_details", "order_status_logs", "order_later");
$tables_arr = array();
for ($t = 0; $t < count($tables_arr); $t++) {
    echo "TRUNCATE TABLE " . $tables_arr[$t] . ";<br>";
    $obj->sql_query("TRUNCATE TABLE " . $tables_arr[$t]);
    echo "Success TRUNCATE TABLE " . $tables_arr[$t] . ";<br>";
    echo "===================================================================================<br>";
}
//die;

foreach ($imgUrlArr as $url) {
    for ($h = 0; $h < count($data_drv); $h++) {
        $imgUrl = $url . $data_drv[$h][$imageField];
        $handle = @fopen($imgUrl, 'r');
        echo $count . ") " . $data_drv[$h][$imageField] . "-> Image URL : " . $imgUrl . " ====>";
        if (@GetImageSize($imgUrl)) {
            echo "Image Found<br>";
            file_put_contents($menuItemPath . "/" . $data_drv[$h][$imageField], file_get_contents($imgUrl));
        } else {
            echo "Image Not Found <br>";
        }
        $obj->sql_query("UPDATE " . $tableName . " SET eImgDownload='Yes' where $pkId='" . $data_drv[$h][$pkId] . "'");
        $count++;
    }
}
echo "All Done";
die;
?>