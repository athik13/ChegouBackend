<?php
define('ENABLE_ADD_PROVIDER_FROM_STORE','Yes');
define('ENABLE_SAFETY_PRACTICE','Yes');
define('ENABLE_TAKE_AWAY','Yes');
define('IS_SINGLE_STORE_SELECTION','No');
define('ENABLE_DELIVERY_PREFERENCE','Yes');
define('ENABLE_STORE_CATEGORIES_MODULE','Yes');
define('DELIVERY_MODULE_AVAILABLE','Yes');
define('DELIVERALL_MODULE_AVAILABLE','Yes');
define('RIDE_MODULE_AVAILABLE','Yes');
define('ENABLE_DELIVERALL_X_THEME','Yes');
define('ENABLE_RIDE_CX_THEME','No');
define('ENABLE_MONGO_CONNECTION','No');
define('ENABEL_SERVICE_PROVIDER_MODULE','Yes');
define('COUNTRY_IMAGE_UPLOAD','No');
define('ENABLE_CUBEJEK_X_THEME','Yes');
define('IS_CUBE_X_THEME','No');
define('ENABLE_RENTAL_OPTION','Yes');
define('ENABLE_MULTI_DELIVERY','Yes');
define('ENABLEHOTELPANEL','Yes');
define('ENABLEKIOSKPANEL','Yes');
define('ONLYDELIVERALL','No');
define('DELIVERALL','Yes');
define('T_SITE_FOLDER_PANEL_PATH','/');
if(defined('T_SITE_FOLDER_PANEL_PATH')){
    $tconfig["tsite_folder"] = T_SITE_FOLDER_PANEL_PATH;
}else{
    $tconfig["tsite_folder"] = ($_SERVER["HTTP_HOST"] == "localhost") ? "/" : "/";
	if ($_SERVER["HTTP_HOST"] == "192.168.1.131" || $_SERVER["HTTP_HOST"] == "192.168.1.141" || $_SERVER["HTTP_HOST"] == "192.168.1.151") {
		$hst_arr = explode("/", $_SERVER["REQUEST_URI"]);
		$hst_var = $hst_arr[1];
		$tconfig["tsite_folder"] = "/" . $hst_arr[1] . "/";
	}
}
if (isset($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] == "on") {
    $http = "https://";
} else {
    $http = "http://";
}
include_once('server_sc_configuration.php');
/* For admin URL */
define('SITE_ADMIN_URL', 'admin');
$tconfig["tsite_url"] = $http . $_SERVER["HTTP_HOST"] . $tconfig["tsite_folder"];
$tconfig["tsite_url_main_admin"] = $http . $_SERVER["HTTP_HOST"] . $tconfig["tsite_folder"] . SITE_ADMIN_URL . '/';
$tconfig["tsite_url_admin"] = $http . $_SERVER["HTTP_HOST"] . $tconfig["tsite_folder"] . 'appadmin/';
$tconfig["tpanel_path"] = $_SERVER["DOCUMENT_ROOT"] . "" . $tconfig["tsite_folder"];
$tconfig["tsite_libraries"] = $tconfig["tsite_url"] . "assets/libraries/";
$tconfig["tsite_libraries_v"] = $tconfig["tpanel_path"] . "assets/libraries/";
$tconfig["tsite_img"] = $tconfig["tsite_url"] . "assets/img";
$tconfig["tsite_home_images"] = $tconfig["tsite_img"] . "/home/";
$tconfig["tsite_upload_images"] = $tconfig["tsite_img"] . "/images/";
$tconfig["tsite_upload_images_panel"] = $tconfig["tpanel_path"] . "assets/img/images/";
//Start ::Company folder
$tconfig["tsite_upload_images_compnay_path"] = $tconfig["tpanel_path"] . "webimages/upload/Company";
$tconfig["tsite_upload_images_compnay"] = $tconfig["tsite_url"] . "webimages/upload/Company";
//End ::Company folder
//Start :: Organization folder
$tconfig["tsite_upload_images_organization_path"] = $tconfig["tpanel_path"] . "webimages/upload/Organization";
$tconfig["tsite_upload_images_organization"] = $tconfig["tsite_url"] . "webimages/upload/Organization";
//End ::Organization folder
/* To upload compnay documents */
$tconfig["tsite_upload_compnay_doc_path"] = $tconfig["tpanel_path"] . "webimages/upload/documents/company";
$tconfig["tsite_upload_compnay_doc"] = $tconfig["tsite_url"] . "webimages/upload/documents/company";
$tconfig["tsite_upload_documnet_size1"] = "250";
$tconfig["tsite_upload_documnet_size2"] = "800";
//Start ::Driver folder
$tconfig["tsite_upload_images_driver_path"] = $tconfig["tpanel_path"] . "webimages/upload/Driver";
$tconfig["tsite_upload_images_driver"] = $tconfig["tsite_url"] . "webimages/upload/Driver";
/* To upload driver documents */
$tconfig["tsite_upload_driver_doc_path"] = $tconfig["tpanel_path"] . "webimages/upload/documents/driver";
$tconfig["tsite_upload_driver_doc"] = $tconfig["tsite_url"] . "webimages/upload/documents/driver";
//Start ::Passenger Profile Image
$tconfig["tsite_upload_images_passenger_path"] = $tconfig["tpanel_path"] . "webimages/upload/Passenger";
$tconfig["tsite_upload_images_passenger"] = $tconfig["tsite_url"] . "webimages/upload/Passenger";
//Start ::Hotel Passenger Profile Image
$tconfig["tsite_upload_images_hotel_passenger_path"] = $tconfig["tpanel_path"] . "webimages/upload/Hotel_Passenger";
$tconfig["tsite_upload_images_hotel_passenger"] = $tconfig["tsite_url"] . "webimages/upload/Hotel_Passenger";
$tconfig["tsite_upload_images_hotel_passenger_size1"] = "64";
$tconfig["tsite_upload_images_hotel_passenger_size2"] = "150";
$tconfig["tsite_upload_images_hotel_passenger_size3"] = "256";
$tconfig["tsite_upload_images_hotel_passenger_size4"] = "512";
$tconfig["tsite_upload_images_hotel_banner_size1"] = "1024";
//Start ::Hotel Banners
$tconfig["tsite_upload_images_hotel_banner_path"] = $tconfig["tpanel_path"] . "webimages/upload/Hotel_Banners";
$tconfig["tsite_upload_images_hotel_banner"] = $tconfig["tsite_url"] . "webimages/upload/Hotel_Banners";
$tconfig["tsite_upload_images_hotel_banner_size1"] = "128";
$tconfig["tsite_upload_images_hotel_banner_size2"] = "256";
$tconfig["tsite_upload_images_hotel_banner_size3"] = "512";
$tconfig["tsite_upload_images_hotel_banner_size4"] = "640";
//Start ::news feed folder
$tconfig["tsite_upload_images_news_feed_path"] = $tconfig["tpanel_path"] . "webimages/upload/newsfeed";
$tconfig["tsite_upload_images_news_feed"] = $tconfig["tsite_url"] . "webimages/upload/newsfeed";
//End ::news feed folder
//Start ::Donation folder
$tconfig["tsite_upload_images_donation_path"] = $tconfig["tpanel_path"] . "webimages/upload/donation";
$tconfig["tsite_upload_images_donation"] = $tconfig["tsite_url"] . "webimages/upload/donation";
//End ::Donation folder
//Start ::Store Categories folder
$tconfig["tsite_upload_images_store_categories_path"] = $tconfig["tpanel_path"] . "webimages/upload/store_categories";
$tconfig["tsite_upload_images_store_categories"] = $tconfig["tsite_url"] . "webimages/upload/store_categories";
//End ::Store Categories folder
/* To upload images for static pages */
$tconfig["tsite_upload_page_images"] = $tconfig["tsite_img"] . "/page/";
$tconfig["tsite_upload_page_images_panel"] = $tconfig["tpanel_path"] . "assets/img/page";
/* To upload images for new home pages */
$tconfig["tsite_upload_home_page_images"] = $tconfig["tsite_img"] . "/home-new";
$tconfig["tsite_upload_home_page_images_panel"] = $tconfig["tpanel_path"] . "assets/img/home-new";
// for home page icon
$tconfig["tsite_upload_home_page_service_images"] = $tconfig["tsite_img"] . "/home-new/services";
$tconfig["tsite_upload_home_page_service_images_panel"] = $tconfig["tpanel_path"] . "assets/img/home-new/services";
/* To upload passenger Docunment */
$tconfig["tsite_upload_vehicle_doc"] = $tconfig["tpanel_path"] . "webimages/upload/documents/vehicles";
$tconfig["tsite_upload_vehicle_doc_panel"] = $tconfig["tsite_url"] . "webimages/upload/documents/vehicles/";
/* To upload driver documents */
//$tconfig["tsite_upload_driver_doc"] = $tconfig["tsite_upload_vehicle_doc"]."driver/";
//$tconfig["tsite_upload_driver_doc_panel"] = $tconfig["tsite_upload_vehicle_doc_panel"]."driver/";
/* To upload images for Appscreenshort pages */
$tconfig["tsite_upload_apppage_images"] = $tconfig["tpanel_path"] . "webimages/upload/Appscreens/";
$tconfig["tsite_upload_apppage_images_panel"] = $tconfig["tsite_url"] . "webimages/upload/Appscreens/";
//Start ::Vehicle Type
$tconfig["tsite_upload_images_vehicle_type_path"] = $tconfig["tpanel_path"] . "webimages/icons/VehicleType";
$tconfig["tsite_upload_images_vehicle_type"] = $tconfig["tsite_url"] . "webimages/icons/VehicleType";
$tconfig["tsite_upload_images_vehicle_type_size1_android"] = "60";
$tconfig["tsite_upload_images_vehicle_type_size2_android"] = "90";
$tconfig["tsite_upload_images_vehicle_type_size3_both"] = "120";
$tconfig["tsite_upload_images_vehicle_type_size4_android"] = "180";
$tconfig["tsite_upload_images_vehicle_type_size5_both"] = "240";
$tconfig["tsite_upload_images_vehicle_type_size5_ios"] = "360";
$tconfig["tsite_upload_images_member_size1"] = "64";
$tconfig["tsite_upload_images_member_size2"] = "150";
$tconfig["tsite_upload_images_member_size3"] = "256";
$tconfig["tsite_upload_images_member_size4"] = "512";
//Start ::Vehicle category
$tconfig["tsite_upload_images_vehicle_category_path"] = $tconfig["tpanel_path"] . "webimages/icons/VehicleCategory";
$tconfig["tsite_upload_images_vehicle_category"] = $tconfig["tsite_url"] . "webimages/icons/VehicleCategory";
$tconfig["tsite_upload_images_vehicle_category_size1_android"] = "60";
$tconfig["tsite_upload_images_vehicle_category_size2_android"] = "90";
$tconfig["tsite_upload_images_vehicle_category_size3_both"] = "120";
$tconfig["tsite_upload_images_vehicle_category_size4_android"] = "180";
$tconfig["tsite_upload_images_vehicle_category_size5_both"] = "240";
$tconfig["tsite_upload_images_vehicle_category_size5_ios"] = "360";
/* $tconfig["tsite_upload_images_member_size1"] = "64";
  $tconfig["tsite_upload_images_member_size2"] = "150";
  $tconfig["tsite_upload_images_member_size3"] = "256";
  $tconfig["tsite_upload_images_member_size4"] = "512"; */
/* To upload images for trips */
$tconfig["tsite_upload_trip_images_path"] = $tconfig["tpanel_path"] . "webimages/upload/beforeafter/";
$tconfig["tsite_upload_trip_images"] = $tconfig["tsite_url"] . "webimages/upload/beforeafter/";
/* To upload images for order proof */
$tconfig["tsite_upload_order_images_path"] = $tconfig["tpanel_path"] . "webimages/upload/order_proof/";
$tconfig["tsite_upload_order_images"] = $tconfig["tsite_url"] . "webimages/upload/order_proof/";
/* To upload images for order delivery preference */
$tconfig["tsite_upload_order_delivery_pref_images_path"] = $tconfig["tpanel_path"] . "webimages/upload/order_delivery_pref/";
$tconfig["tsite_upload_order_delivery_pref_images"] = $tconfig["tsite_url"] . "webimages/upload/order_delivery_pref/";
/* For Back-up Database */
$tconfig["tsite_upload_files_db_backup_path"] = $tconfig["tpanel_path"] . "webimages/upload/backup/";
$tconfig["tsite_upload_files_db_backup"] = $tconfig["tsite_url"] . "webimages/upload/backup/";
/* To upload preference images */
$tconfig["tsite_upload_preference_image"] = $tconfig["tpanel_path"] . "webimages/upload/preferences/";
$tconfig["tsite_upload_preference_image_panel"] = $tconfig["tsite_url"] . "webimages/upload/preferences/";
/* Home Page Image Size */
$tconfig["tsite_upload_images_home"] = "300";
/* To upload images for trip delivery signatures */
$tconfig["tsite_upload_trip_signature_images_path"] = $tconfig["tpanel_path"] . "webimages/upload/trip_signature/";
$tconfig["tsite_upload_trip_signature_images"] = $tconfig["tsite_url"] . "webimages/upload/trip_signature/";
$tconfig["tsite_upload_docs_file_extensions"] = "pdf,jpg,png,gif,bmp,jpeg,doc,docx,txt,xls,xlxs";
/* To upload images for serive categories */
$tconfig["tsite_upload_service_categories_images_path"] = $tconfig["tpanel_path"] . "webimages/upload/ServiceCategories/";
$tconfig["tsite_upload_service_categories_images"] = $tconfig["tsite_url"] . "webimages/upload/ServiceCategories/";
//Start ::Food Menu
$tconfig["tsite_upload_images_food_menu_path"] = $tconfig["tpanel_path"] . "webimages/upload/FoodMenu";
$tconfig["tsite_upload_images_food_menu"] = $tconfig["tsite_url"] . "webimages/upload/FoodMenu";
$tconfig["tsite_upload_images_food_menu_size1_android"] = "60";
$tconfig["tsite_upload_images_food_menu_size2_android"] = "90";
$tconfig["tsite_upload_images_food_menu_size3_both"] = "120";
$tconfig["tsite_upload_images_food_menu_size4_android"] = "180";
$tconfig["tsite_upload_images_food_menu_size5_both"] = "240";
$tconfig["tsite_upload_images_food_menu_size5_ios"] = "360";
//Start ::Cuisines
$tconfig["tsite_upload_images_menu_item_type_path"] = $tconfig["tpanel_path"] . "webimages/upload/ItemTypeImages";
$tconfig["tsite_upload_images_menu_item_type"] = $tconfig["tsite_url"] . "webimages/upload/ItemTypeImages";
//Start ::Menu Items
$tconfig["tsite_upload_images_menu_item_path"] = $tconfig["tpanel_path"] . "webimages/upload/MenuItem";
$tconfig["tsite_upload_images_menu_item"] = $tconfig["tsite_url"] . "webimages/upload/MenuItem";
$tconfig["tsite_upload_images_menu_item_size1_android"] = "60";
$tconfig["tsite_upload_images_menu_item_size2_android"] = "90";
$tconfig["tsite_upload_images_menu_item_size3_both"] = "120";
$tconfig["tsite_upload_images_menu_item_size4_android"] = "180";
$tconfig["tsite_upload_images_menu_item_size5_both"] = "240";
$tconfig["tsite_upload_images_menu_item_size5_ios"] = "360";
//Start ::Profile Master Icons 
$tconfig["tsite_upload_profile_master_path"] = $tconfig["tpanel_path"] . "webimages/upload/ProfileMaster";
$tconfig["tsite_upload_images_profile_master"] = $tconfig["tsite_url"] . "webimages/upload/ProfileMaster";
$tconfig["tsite_upload_images_profile_master_size1"] = "16";
$tconfig["tsite_upload_images_profile_master_size2"] = "32";
$tconfig["tsite_upload_images_profile_master_size3"] = "48";
$tconfig["tsite_upload_images_profile_master_size4"] = "64";
$tconfig["tsite_upload_advertise_banner_path"] = $tconfig["tpanel_path"] . "webimages/upload/AdvImages"; //Added By HJ On 12-12-2018 For Advertisement Banners Path
$tconfig["tsite_upload_advertise_banner"] = $tconfig["tsite_url"] . "webimages/upload/AdvImages"; //Added By HJ On 12-12-2018 For Advertisement Banners URL
$tconfig["tsite_upload_manage_app_screen_path"] = $tconfig["tpanel_path"] . "webimages/upload/AppScreenImages";
$tconfig["tsite_upload_manage_app_screen"] = $tconfig["tsite_url"] . "webimages/upload/AppScreenImages"; //Added By HJ On 12-12-2018 For Advertisement Banners URL Y:\rabbitsend\webimages\upload\AppScreenImages
$tconfig["tsite_upload_provider_image_path"] = $tconfig["tpanel_path"] . "webimages/upload/Provider_Images/"; //Added By Hasmukh On 24-01-2019 For Provider Image Path
$tconfig["tsite_upload_provider_image"] = $tconfig["tsite_url"] . "webimages/upload/Provider_Images"; //Added By Hasmukh On 24-01-2019 For Provider Image URL
$tconfig["tsite_upload_prescription_image_path"] = $tconfig["tpanel_path"] . "webimages/upload/Prescription_Images/"; //For Prescription required added by Sneha
$tconfig["tsite_upload_prescription_image"] = $tconfig["tsite_url"] . "webimages/upload/Prescription_Images"; //For Prescription required added by Sneha
//Added By HJ On 26-06-2019 For Define Store Demo Image Folder Path and URL Start
$tconfig["tsite_upload_demo_compnay_doc_path"] = $tconfig["tpanel_path"] . "webimages/upload/demo_store_img/";
$tconfig["tsite_upload_demo_compnay_doc"] = $tconfig["tsite_url"] . "webimages/upload/demo_store_img/";
//Added By HJ On 26-06-2019 For Define Store Demo Image Folder Path and URL End
### ==================================== label configuration =========================================
//store sample image path
$tconfig["tsite_sample_images_store_path"] = $tconfig["tpanel_path"] . "webimages/icons/company_sample_images/";
/* Change appropriate values only. Below settings are related to socket cluster */
// if ($IS_INHOUSE_DOMAINS) {
if(empty($tconfig["tsite_sc_host"])){
	$tconfig["tsite_sc_protocol"] = "http://"; // Protocol to access Socket Cluster.
	$tconfig["tsite_sc_host"] = 'www.chegou.site'; // In which socket cluster is installed.
	$tconfig["tsite_host_sc_port"] = "3589"; // In which socket cluster is running on.
	$tconfig["tsite_host_sc_path"] = "/socketcluster/"; // This path should not change.
	/* Yalgaar settings url */
	$tconfig["tsite_yalgaar_url"] = "http://" . $_SERVER['SERVER_ADDR'] . ":0000";
	/* Yalgaar settings url */
}
if(empty($tconfig["tsite_gmap_replacement_host"])){
	//google api replacement start
	$tconfig["tsite_gmap_replacement_protocol"] = "http://";
	$tconfig["tsite_gmap_replacement_host"] = $_SERVER['SERVER_ADDR'];
	$tconfig["tsite_host_gmap_replacement_port"] = "0000";
	$tconfig["tsite_host_gmap_replacement_path"] = "/";
	//google api replacement end
}
/* Socket cluster settings are finished. For any new settings related to socket cluster should be declare above this line. */
define('GOOGLE_API_REPLACEMENT_URL', $tconfig["tsite_gmap_replacement_protocol"] . $tconfig["tsite_gmap_replacement_host"] . ":" . $tconfig["tsite_host_gmap_replacement_port"] . $tconfig["tsite_host_gmap_replacement_path"]);
/* Change appropriate values only. Below settings are related to MongoDB */
$tconfig["tmongodb_port"] = "27017";
$tconfig["tmongodb_databse"] = TSITE_DB;
/* Settings related to MongoDB is finished */
define('ENABLE_RENTAL_OPTION', 'Yes');
define('ENABLE_MULTI_DELIVERY', 'Yes');
/* To add enable deliveryall portion in cubejek */
/* old define('DELIVERALL','Yes'); */
define('DELIVERALL', isset($_REQUEST['DELIVERALL']) && !empty($_REQUEST['DELIVERALL']) ? $_REQUEST['DELIVERALL'] : 'Yes');
/* To add enable only deliveryall portion and hide all portion in cubejek */
/* old define('ONLYDELIVERALL','No'); */
//define('DELIVERALL','No');
define('ONLYDELIVERALL', isset($_REQUEST['ONLYDELIVERALL']) && !empty($_REQUEST['ONLYDELIVERALL']) ? $_REQUEST['ONLYDELIVERALL'] : 'No');
if (!empty($CUS_CUBE_X_THEME)) {
    define('IS_CUBE_X_THEME', $CUS_CUBE_X_THEME);
} else {
    define('IS_CUBE_X_THEME', 'No');
}
// for enable hotel panel in web
define('ENABLEHOTELPANEL', 'Yes');
define('HotelAPIUrl', 'webservice_shark.php');
// for enable kiosk
define('ENABLEKIOSKPANEL', 'Yes');
define('ManualBookingAPIUrl', 'webservice_shark.php');
///Added By HJ On 10-08-2019 For Define URL name For Login and Sign Up Of Front Panel Start
$cjSignIn = "cj-sign-in";
$cjSignUp = "cj-SignUp";
$cjProviderLogin = "cj-provider-login";
$cjDriverLogin = "cj-driver-login";
$cjUserLogin = "cj-user-login";
$cjRiderLogin = "cj-rider-login";
$cjCompanyLogin = "cj-company-login";
$cjOrganizationLogin = "cj-organization-login";
$cjSignUpUser = "cj-sign-up-user";
$cjSignUpRider = "cj-sign-up-rider";
$cjSignupCompany = "cj-sign-up";
$cjSignupRestaurant = "cj-sign-up-restaurant";
$cjSignupOrganization = "cj-sign-up-organization";
///Added By HJ On 10-08-2019 For Define URL name For Login and Sign Up Of Front Panel End
define('ENABLE_CHANGE_CURRENCY_ROUNDING_OPTION', 'No');
/* * * Used in provider application ** */
define('RANDOM_COLORS_ARR', array("#2EAA0C", "#0b89fe", "#4BB5F5", "#7a497f", "#00537b", "#363e4f", "#078a01", "#e97318", "#FFA60A", "#3CCA59", "#027BFF", "#e6008b", "#e9b600", "#FC6542", "#eb4b01", "#00d094", "#5773c2", "#C60C0C", "#7a00e5", "#4D0ED6", "#c000e2", "#343438", "#F98766", "#d25179", "#903258", "#5855D6", "#fea208"));
/* * * Used in provider application ** */
//BG_COLOR
//TEXT_COLOR - fff
$color = (array(array("BG_COLOR" => "#2EAA0C", "TEXT_COLOR" => "#ffffff"), array("BG_COLOR" => "#0b89fe", "TEXT_COLOR" => "#ffffff"), array("BG_COLOR" => "#4BB5F5", "TEXT_COLOR" => "#ffffff"), array("BG_COLOR" => "#7a497f", "TEXT_COLOR" => "#ffffff"), array("BG_COLOR" => "#00537b", "TEXT_COLOR" => "#ffffff"), array("BG_COLOR" => "#363e4f", "TEXT_COLOR" => "#ffffff"), array("BG_COLOR" => "#078a01", "TEXT_COLOR" => "#ffffff"), array("BG_COLOR" => "#e97318", "TEXT_COLOR" => "#ffffff"), array("BG_COLOR" => "#FFA60A", "TEXT_COLOR" => "#ffffff"), array("BG_COLOR" => "#3CCA59", "TEXT_COLOR" => "#ffffff"), array("BG_COLOR" => "#027BFF", "TEXT_COLOR" => "#ffffff"), array("BG_COLOR" => "#e6008b", "TEXT_COLOR" => "#ffffff"), array("BG_COLOR" => "#e9b600", "TEXT_COLOR" => "#ffffff"), array("BG_COLOR" => "#FC6542", "TEXT_COLOR" => "#ffffff"), array("BG_COLOR" => "#eb4b01", "TEXT_COLOR" => "#ffffff"), array("BG_COLOR" => "#00d094", "TEXT_COLOR" => "#ffffff"), array("BG_COLOR" => "#5773c2", "TEXT_COLOR" => "#ffffff"), array("BG_COLOR" => "#C60C0C", "TEXT_COLOR" => "#ffffff"), array("BG_COLOR" => "#7a00e5", "TEXT_COLOR" => "#ffffff"), array("BG_COLOR" => "#4D0ED6", "TEXT_COLOR" => "#ffffff"), array("BG_COLOR" => "#c000e2", "TEXT_COLOR" => "#ffffff"), array("BG_COLOR" => "#343438", "TEXT_COLOR" => "#ffffff"), array("BG_COLOR" => "#F98766", "TEXT_COLOR" => "#ffffff"), array("BG_COLOR" => "#d25179", "TEXT_COLOR" => "#ffffff"), array("BG_COLOR" => "#903258", "TEXT_COLOR" => "#ffffff"), array("BG_COLOR" => "#5855D6", "TEXT_COLOR" => "#ffffff"), array("BG_COLOR" => "#fea208", "TEXT_COLOR" => "#ffffff")));
define('RANDOM_COLORS_KEY_VAL_ARR', $color);
//added by SP for country images on 07-10-2019 start
define('COUNTRY_IMAGE_UPLOAD', 'Yes');
$tconfig["tsite_upload_country_images_path"] = $tconfig["tpanel_path"] . "webimages/icons/country_flags/";
$tconfig["tsite_upload_country_images"] = $tconfig["tsite_url"] . "webimages/icons/country_flags/";
//added by SP for country images on 07-10-2019 end
//Added By HJ for App Type Wise Home Content Images Start
$tconfig["tsite_upload_apptype_page_images"] = $tconfig["tsite_img"] . "/page/home/apptype/";
$tconfig["tsite_upload_apptype_page_images_panel"] = $tconfig["tpanel_path"] . "assets/img/page/home/apptype/";
//Added By HJ for App Type Wise Home Content Images End
$tconfig["tsite_upload_apptype_images"] = $tconfig["tsite_img"] . "/apptype/";
$tconfig["tsite_upload_apptype_images_panel"] = $tconfig["tpanel_path"] . "assets/img/apptype/";
define('ENABLE_EXPIRE_DOCUMENT', 'No'); // Added By HJ On 09-12-2019 For Show/Hide (Restrict Drivers to be online if one or more document is expired) Button As Per Discuss with KS
//added by SP for cubejkx theme on 05-12-2019
if (!empty($CUS_ENABLE_CUBEJEK_X_THEME)) {
    define('ENABLE_CUBEJEK_X_THEME', $CUS_ENABLE_CUBEJEK_X_THEME);
} else {
    define('ENABLE_CUBEJEK_X_THEME', 'No');
}
define('ENABLE_EXTENDED_VERSION_MANUAL_BOOKING', 'Yes');
define('ENABLE_MONGO_CONNECTION', 'No');
define('ENABLE_MEMCACHED', 'No');
define('AUTH_EMAIL_SYSTEM', 'systemuser@system.com');
define('INTERVAL_SECONDS', '86400'); // Added By HJ On 13-03-2020 Which is Used in Webservice and God's View
if (IS_CUBE_X_THEME == "Yes") {
    define('ENABEL_SERVICE_PROVIDER_MODULE', 'No');
} else {
    define('ENABEL_SERVICE_PROVIDER_MODULE', 'Yes');
}
//added by SP for cubejkx theme on 05-03-2020
if (!empty($CUS_ENABLE_RIDE_CX_THEME)) {
    define('ENABLE_RIDE_CX_THEME', $CUS_ENABLE_RIDE_CX_THEME);
} else {
    define('ENABLE_RIDE_CX_THEME', 'No');
}
//added by SP for cubejkx theme on 14-03-2020
if (!empty($CUS_ENABLE_DELIVERALL_X_THEME)) {
    define('ENABLE_DELIVERALL_X_THEME', $CUS_ENABLE_DELIVERALL_X_THEME);
} else {
    define('ENABLE_DELIVERALL_X_THEME', 'No');
}
if (!empty($CUS_ENABLE_RIDE_DELIVERY_X_THEME)) {
    define('ENABLE_RIDE_DELIVERY_X_THEME', $CUS_ENABLE_RIDE_DELIVERY_X_THEME);
} else {
    define('ENABLE_RIDE_DELIVERY_X_THEME', 'No');
}
if (!empty($CUS_ENABLE_DELIVERY_X_THEME)) {
    define('ENABLE_DELIVERY_X_THEME', $CUS_ENABLE_DELIVERY_X_THEME);
} else {
    define('ENABLE_DELIVERY_X_THEME', 'No');
}
if (IS_CUBE_X_THEME == 'Yes' || ENABLE_CUBEJEK_X_THEME == 'Yes' || ENABLE_RIDE_CX_THEME == 'Yes' || ENABLE_DELIVERALL_X_THEME == 'Yes' || ENABLE_DELIVERY_X_THEME=='Yes' || ENABLE_RIDE_DELIVERY_X_THEME=='Yes') {
    $cjSignIn = $cjUserLogin = $cjRiderLogin = "sign-in?type=user";
    $cjCompanyLogin = "sign-in?type=company";
    $cjOrganizationLogin = "sign-in?type=organization";
    if (IS_CUBE_X_THEME == 'Yes' || ENABLE_CUBEJEK_X_THEME == 'Yes') {
        $cjProviderLogin = $cjDriverLogin = "sign-in?type=provider";
    } else {
        $cjProviderLogin = $cjDriverLogin = "sign-in?type=driver";
    }
}
//if(ENABLE_DELIVERALL_X_THEME == 'Yes'){
	define('ENABLE_STORE_CATEGORIES_MODULE', 'Yes');	
//}else{
	//define('ENABLE_STORE_CATEGORIES_MODULE', 'No');
//}
if (!isset($service_categories_ids_arr) && empty($service_categories_ids_arr)) {
    if (!empty($FOOD_ONLY) /* && strtoupper($FOOD_ONLY) == "YES" */) {
        //$service_categories_ids_arr = [1];
        $service_categories_ids_arr = explode(",", $FOOD_ONLY);
    } else {
        if (IS_CUBE_X_THEME == 'Yes') {
            $service_categories_ids_arr = [1, 2];
        } else {
            $service_categories_ids_arr = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10];
            //$service_categories_ids_arr = [2];
        }
    }
}
if (!empty($_REQUEST['type']) && $_REQUEST['type'] == "getServiceCategories") {
    $_REQUEST['DEFAULT_SERVICE_CATEGORY_ID'] = "";
}
//$service_categories_ids_arr = [2];
if (isset($_REQUEST['DEFAULT_SERVICE_CATEGORY_ID']) && $_REQUEST['DEFAULT_SERVICE_CATEGORY_ID'] != "") {
    $service_categories_ids_arr_new_arr = $_REQUEST['DEFAULT_SERVICE_CATEGORY_ID'];
    $service_categories_ids_arr = (array) $service_categories_ids_arr_new_arr;
}
$enablesevicescategory = implode(",", $service_categories_ids_arr);
if (!empty($_REQUEST['CUS_ENABLE_DELIVERY_PREFERENCE'])) {
    define('ENABLE_DELIVERY_PREFERENCE', $_REQUEST['CUS_ENABLE_DELIVERY_PREFERENCE']);
} else {
    define('ENABLE_DELIVERY_PREFERENCE', 'Yes');
}
if (!empty($_REQUEST['CUS_IS_SINGLE_STORE_SELECTION'])) {
    define('IS_SINGLE_STORE_SELECTION', $_REQUEST['CUS_IS_SINGLE_STORE_SELECTION']);
} else {
    define('IS_SINGLE_STORE_SELECTION', 'No');
}
if (!empty($_REQUEST['CUS_ENABLE_TAKE_AWAY'])) {
    define('ENABLE_TAKE_AWAY', $_REQUEST['CUS_ENABLE_TAKE_AWAY']);
} else {
    define('ENABLE_TAKE_AWAY', 'Yes');
}
if (!empty($_REQUEST['CUS_ENABLE_ADD_PROVIDER_FROM_STORE'])) {
    define('ENABLE_ADD_PROVIDER_FROM_STORE', $_REQUEST['CUS_ENABLE_ADD_PROVIDER_FROM_STORE']);
} else {
    define('ENABLE_ADD_PROVIDER_FROM_STORE', 'Yes');
}
define('ENABLE_SAFETY_PRACTICE', 'Yes');
define('DELIVERY_MODULE_AVAILABLE','Yes');
define('DELIVERALL_MODULE_AVAILABLE','Yes');
define('RIDE_MODULE_AVAILABLE','Yes');
define('ENABLE_SERVER_REQUIREMENT_VALIDATION', $IS_INHOUSE_DOMAINS ? 'Yes' : 'No');
if (!empty($CUS_ENABLE_DELIVERALL_X_THEME_V2)) {
    define('ENABLE_DELIVERALL_X_THEME_V2', $CUS_ENABLE_DELIVERALL_X_THEME_V2);
} else {
    define('ENABLE_DELIVERALL_X_THEME_V2', 'No');
}
if (!empty($CUS_ENABLE_SERVICE_X_THEME)) {
    define('ENABLE_SERVICE_X_THEME', $CUS_ENABLE_SERVICE_X_THEME);
} else {
    define('ENABLE_SERVICE_X_THEME', 'No');
}
define('ENABLE_NEW_WALLET_WITHDRAWAL_FLOW_DRIVER', $IS_INHOUSE_DOMAINS  ? 'Yes' : 'No');
?>