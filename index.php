<?php
include_once("common.php");
include_once("app_common_functions.php");
$enable_fly = 'No';
if (checkFlyStationsModule()) {
    $enable_fly = 'Yes';
}
$sess_lang = "EN";
if(isset($_SESSION['sess_lang']) && trim($_SESSION['sess_lang']) != ""){
   $sess_lang = $_SESSION['sess_lang'];
}
$script="Home";
//Added By HJ On 12-06-2020 For Optimization Query Start
$idsArr = array(19,23,24,25,26,27,28,29,30,31);
$getStaticPageData = $generalobj->getStaticPage($idsArr,$sess_lang);
//echo "<pre>";print_r($getStaticPageData);die;
if(isset($getStaticPageData[23])){
    $meta1 = $getStaticPageData[23];
}
if(isset($getStaticPageData[24])){
    $meta2 = $getStaticPageData[24];
}
if(isset($getStaticPageData[25])){
    $meta3 = $getStaticPageData[25];
}
if(isset($getStaticPageData[26])){
    $meta4 = $getStaticPageData[26];
}
if(isset($getStaticPageData[19])){
    $homepage_banner = $getStaticPageData[19];
}
if(isset($getStaticPageData[27])){
    $meta5 = $getStaticPageData[27];
}
if(isset($getStaticPageData[28])){
    $meta6 = $getStaticPageData[28];
}
if(isset($getStaticPageData[29])){
    $image3 = $getStaticPageData[29];
}
if(isset($getStaticPageData[30])){
    $image4 = $getStaticPageData[30];
}
if(isset($getStaticPageData[32])){
    $meta7 = $getStaticPageData[32];
}
//Added By HJ On 12-06-2020 For Optimization Query End
$meta_arr = $generalobj->getsettingSeo(7);
//Commented By HJ On 12-06-2020 For Optimization Query Start
//$meta1 = $generalobj->getStaticPage(23,$sess_lang);
//$meta2 = $generalobj->getStaticPage(24,$sess_lang);
//$meta3 = $generalobj->getStaticPage(25,$sess_lang);
//$meta4 = $generalobj->getStaticPage(26,$sess_lang);
//$homepage_banner = $generalobj->getStaticPage(19,$sess_lang);
//$meta5 = $generalobj->getStaticPage(27,$sess_lang);
//$meta6 = $generalobj->getStaticPage(28,$sess_lang);
//$image3 = $generalobj->getStaticPage(29,$sess_lang);
//$image4 = $generalobj->getStaticPage(30,$sess_lang);
//$meta7 = $generalobj->getStaticPage(32,$sess_lang);
//Commented By HJ On 12-06-2020 For Optimization Query End
// if(ONLYDELIVERALL == 'Yes'){
//     $data = $generalobj->gethomeDataFood($sess_lang);
//     //$data = $generalobj->gethomeDataNew($sess_lang);
// } else {
//     if($APP_TYPE == 'Ride-Delivery-UberX') {
//         $data = $generalobj->gethomeDataNew($sess_lang);
//         //$data = $generalobj->gethomeData($sess_lang);
//     } else {
//         $data = $generalobj->gethomeData($sess_lang);
//     }
// }
$data = $generalobj->gethomeContentData($sess_lang);
//added by SP on 18-10-2019
if($generalobj->checkCubexThemOn() == 'Yes' || $generalobj->checkCubeJekXThemOn() == 'Yes' || $generalobj->checkRideCXThemOn() == 'Yes') {
    if(empty($data[0]['lHowitworkSection'])) {
        $vCode = 'EN';
        $data = $generalobj->gethomeContentData($vCode);
    }
}
if($generalobj->checkDeliverallXThemOn()=='Yes') {	
//	 if(empty($data[0]['lHowitworkSection'])) {
//        $vCode = 'EN';
//        $data = $generalobj->gethomeContentData($vCode);
//    }
}

$manualOrderMenu = $langage_lbl['LBL_MANUAL_STORE_ORDER_TXT'];
if (isset($_SESSION[$orderServiceNameSession])) {
    $manualOrderMenu = $_SESSION[$orderServiceNameSession];
}
$manualOrderMenu = $langage_lbl['LBL_MANUAL_STORE_ORDER_TXT'];
$RideDeliveryIconArrStatus = $generalobj->CheckRideDeliveryFeatureDisableWeb();
$RideDeliveryBothFeatureDisable = $RideDeliveryIconArrStatus['RideDeliveryBothFeatureDisable'];
$DONATION = $DRIVER_DESTINATION = $FAVOURITE_DRIVER = $FAVOURITE_STORE = $DRIVER_SUBSCRIPTION = $GOJEK_GOPAY = $MULTI_STOPOVER_POINTS = $MANUAL_STORE_ORDER_WEBSITE = $MANUAL_STORE_ORDER_STORE_PANEL = $MANUAL_STORE_ORDER_ADMIN_PANEL = "No"; // Added By HJ On 12-07-2019
$setupData = $obj->sql_query("select lAddOnConfiguration from setup_info");
if (isset($setupData[0]['lAddOnConfiguration'])) {
    $addOnData = json_decode($setupData[0]['lAddOnConfiguration'], true);
    foreach ($addOnData as $addOnKey => $addOnVal) {
        $$addOnKey = $addOnVal;
    }
}
$siteUrl = $tconfig['tsite_url'];
//echo "<pre>";print_r($_SESSION);die;
?>
<!DOCTYPE html>
<html lang="en" dir="<?=(isset($_SESSION['eDirectionCode']) && $_SESSION['eDirectionCode'] != "")?$_SESSION['eDirectionCode']:'ltr';?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0, user-scalable=no, target-densityDpi=device-dpi">
    <!--<title><?=$SITE_NAME?></title>-->
	<title><?php echo $meta_arr['meta_title'];?></title>
	<meta name="keywords" value="<?=$meta_arr['meta_keyword'];?>"/>
	<meta name="description" value="<?=$meta_arr['meta_desc'];?>"/>
    <!-- Default Top Script and css -->
    <?php include_once("top/top_script.php");?>
    <!-- End: Default Top Script and css-->
</head>
<body>
    <!-- home page -->
    <!-- home page -->
    <?php if($template!='taxishark'){?>
    <div id="main-uber-page">
    <?php } ?>  
        <!-- Left Menu --> 
    <?php  include_once("top/left_menu.php");?>
    <!-- End: Left Menu-->
        <!-- Top Menu -->
        <?php include_once("top/header_topbar.php");?>
        <!-- End: Top Menu-->
        <!-- First Section -->
		<?php include_once("top/header.php");?>
        <!-- End: First Section -->
        <?php include_once("top/home.php");?>
   
    <!-- home page end-->
    <!-- footer part -->
    <?php include_once('footer/footer_home.php');?>
    
    <div style="clear:both;"></div>
     <?php if($template!='taxishark'){?>
     </div>
     <?php } ?> 
    <!-- footer part end -->
<!-- Footer Script -->
<?php include_once('top/footer_script.php');?>
<!-- End: Footer Script -->
</body>
</html>
