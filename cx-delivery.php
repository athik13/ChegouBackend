<?php
include_once("common.php");

$showSignRegisterLinks = 1; 
//$table_name = 'content_cubex_details';
$table_name = $generalobj->getContentCMSHomeTable();
$vCode = $_SESSION['sess_lang'];
$eFor = 'Delivery';
$_SESSION["navigatedPage"] = $eFor;

$ride_data_query = "SELECT * FROM ".$table_name." WHERE eFor = '" . $eFor . "'";
$ride_data = $obj->MySQLSelect($ride_data_query);

$banner_section = json_decode($ride_data[0]['lBannerSection'],true);
if(empty($banner_section['title_'.$vCode])) {
    $vCode = 'EN';
}

$inner_key = array('menu_title_','title_','sub_title_','desc_','img_','title_first_','desc_first_','img_first_','title_sec_','desc_sec_','img_sec_','title_third_','desc_third_','img_third_','title_fourth_','desc_fourth_','img_fourth_','title_fifth_','desc_fifth_','img_fifth_','title_six_','desc_six_','img_six_','main_title_','main_desc_','img2_');

$banner_section = $generalobj->checkOtherLangDataExist(json_decode($ride_data[0]['lBannerSection'],true),$vCode,$inner_key);
$how_it_work_section = $generalobj->checkOtherLangDataExist(json_decode($ride_data[0]['lHowitworkSection'],true),$vCode,$inner_key);
$secure_section = $generalobj->checkOtherLangDataExist(json_decode($ride_data[0]['lSecuresafeSection'],true),$vCode,$inner_key);
$download_section = $generalobj->checkOtherLangDataExist(json_decode($ride_data[0]['lDownloadappSection'],true),$vCode,$inner_key);
$call_section = $generalobj->checkOtherLangDataExist(json_decode($ride_data[0]['lCalltobookSection'],true),$vCode,$inner_key);
$earn_section = $generalobj->checkOtherLangDataExist(json_decode($ride_data[0]['lEarnSection'],true),$vCode,$inner_key);
$calculate_section = $generalobj->checkOtherLangDataExist(json_decode($ride_data[0]['lCalculateSection'],true),$vCode,$inner_key);
$cartype_section = $generalobj->checkOtherLangDataExist(json_decode($ride_data[0]['lCartypeSection'],true),$vCode,$inner_key);
$service_section = $generalobj->checkOtherLangDataExist(json_decode($ride_data[0]['lServiceSection'],true),$vCode,$inner_key);
$benefit_section = $generalobj->checkOtherLangDataExist(json_decode($ride_data[0]['lBenefitSection'],true),$vCode,$inner_key);

$menutitleHow = !empty($how_it_work_section['menu_title_'.$vCode]) ? $how_it_work_section['menu_title_'.$vCode] : $how_it_work_section['title_'.$vCode]; 
$menutitleSecure = !empty($secure_section['menu_title_'.$vCode]) ? $secure_section['menu_title_'.$vCode] : $secure_section['title_'.$vCode]; 
$menutitleDown = !empty($download_section['menu_title_'.$vCode]) ? $download_section['menu_title_'.$vCode] : $download_section['title_'.$vCode]; 
$menutitleCall = !empty($call_section['menu_title_'.$vCode]) ? $call_section['menu_title_'.$vCode] : $call_section['title_'.$vCode]; 
$menutitleEarn = !empty($earn_section['menu_title_'.$vCode]) ? $earn_section['menu_title_'.$vCode] : $earn_section['title_'.$vCode]; 
$menutitleCalc = !empty($calculate_section['menu_title_'.$vCode]) ? $calculate_section['menu_title_'.$vCode] : $calculate_section['title_'.$vCode]; 
$menutitleCar = !empty($cartype_section['menu_title_'.$vCode]) ? $cartype_section['menu_title_'.$vCode] : $cartype_section['title_'.$vCode];
$menutitleService = !empty($service_section['menu_title_'.$vCode]) ? $service_section['menu_title_'.$vCode] : $service_section['title_'.$vCode];
$menutitleBenefit = !empty($benefit_section['menu_title_'.$vCode]) ? $benefit_section['menu_title_'.$vCode] : $benefit_section['title_'.$vCode];

$btitle = $bdesc = $bimg = array();
if(!empty($benefit_section['title_first_'.$vCode])) {
    $btitle[] = $benefit_section['title_first_'.$vCode];
    $bdesc[] = $benefit_section['desc_first_'.$vCode];
    $bimg[] = $benefit_section['img_first_'.$vCode];
}
if(!empty($benefit_section['title_sec_'.$vCode])) {
    $btitle[] = $benefit_section['title_sec_'.$vCode];
    $bdesc[] = $benefit_section['desc_sec_'.$vCode];
    $bimg[] = $benefit_section['img_sec_'.$vCode];
}
if(!empty($benefit_section['title_third_'.$vCode])) {
    $btitle[] = $benefit_section['title_third_'.$vCode];
    $bdesc[] = $benefit_section['desc_third_'.$vCode];
    $bimg[] = $benefit_section['img_third_'.$vCode];
}
if(!empty($benefit_section['title_fourth_'.$vCode])) {
    $btitle[] = $benefit_section['title_fourth_'.$vCode];
    $bdesc[] = $benefit_section['desc_fourth_'.$vCode];
    $bimg[] = $benefit_section['img_fourth_'.$vCode];
}
if(!empty($benefit_section['title_fifth_'.$vCode])) {
    $btitle[] = $benefit_section['title_fifth_'.$vCode];
    $bdesc[] = $benefit_section['desc_fifth_'.$vCode];
    $bimg[] = $benefit_section['img_fifth_'.$vCode];
}
if(!empty($benefit_section['title_six_'.$vCode])) {
    $btitle[] = $benefit_section['title_six_'.$vCode];
    $bdesc[] = $benefit_section['desc_six_'.$vCode];
    $bimg[] = $benefit_section['img_six_'.$vCode];
}
$key_arr = array("#SUPPORT_PHONE#","#SUPPORT_ADDRESS#","#SUPPORT_EMAIL#","#ANDROID_APP_LINK#","#IPHONE_APP_LINK#");
$val_arr = array($SUPPORT_PHONE,$COMPANY_ADDRESS,$SUPPORT_MAIL,$ANDROID_APP_LINK,$IPHONE_APP_LINK);
?>
<!DOCTYPE html>
<html lang="en" dir="<?=(isset($_SESSION['eDirectionCode']) && $_SESSION['eDirectionCode'] != "")?$_SESSION['eDirectionCode']:'ltr';?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0, user-scalable=no, target-densityDpi=device-dpi">
    <title><?=$SITE_NAME?> | <?= $langage_lbl['LBL_DELIVERY'] ?></title>
    <!--<title><?php echo $meta_arr['meta_title'];?></title>
	<meta name="keywords" value="<?=$meta_arr['meta_keyword'];?>"/>
	<meta name="description" value="<?=$meta_arr['meta_desc'];?>"/>-->
    <!-- Default Top Script and css -->
    <?php include_once("top/top_script.php");?>
    <!-- End: Default Top Script and css-->
    <script src="https://maps.googleapis.com/maps/api/js?v=3.exp&sensor=false&libraries=places&language=en&key=<?=$GOOGLE_SEVER_API_KEY_WEB?>"></script>
</head>
<body id="wrapper">
    <!-- home page -->
    <!-- home page -->
    <?php if($template!='taxishark'){?>
    <div id="main-uber-page">
    <?php } ?>
        <!-- Left Menu -->
    <?php include_once("top/left_menu.php");?>
    <!-- End: Left Menu-->
        <!-- Top Menu -->
        <?php include_once("top/header_topbar.php");?>
        <!-- End: Top Menu-->
        <!-- First Section -->
		<?php include_once("top/header.php");?>
        <!-- End: First Section -->
<!-- *************banner section start************* -->
<section class="banner-section taxi-app">
    <div class="tab-row-holding">
        <ul class="tab-row">
            <li data-src="1" class="tab active">
                <a href="#how-it-works"><?= $menutitleHow ?></a>
            </li>
            <li data-src="2" class="tab">
                <a href="#fare-estimate"><?= $menutitleCalc ?></a>
            </li>
            <li data-src="3" class="tab">
                <a href="#our-services"><?= $menutitleService; ?></a>
            </li>
            <li data-src="4" class="tab">
                <a href="#our-benefits"><?= $menutitleBenefit ?></a>
            </li>
            <li data-src="5" class="tab">
                <a href="#our-fleet"><?= $menutitleSecure ?></a>
            </li>
            <li data-src="6" class="tab">
                <a href="#download-apps"><?= $menutitleDown ?></a>
            </li>
            <li data-src="7" class="tab">
                <a href="#book-delivery"><?= $menutitleCall ?></a>
            </li>
        </ul>
    </div>
    <div class="banner-section-inner">
        <div class="categories-block">
            <div class="categories-caption active">
                <h2><?php echo $banner_section['title_'.$vCode];?> <span><?php echo $banner_section['sub_title_'.$vCode];?></span></h2>
                <?php echo $banner_section['desc_'.$vCode];?>
            </div>
        </div>
    </div>
    <div class="banner-back">
        <div class="banner-image" id="1" style="background-image: url(<?php  echo $tconfig["tsite_upload_apptype_page_images"].$template.'/'.$banner_section['img_'.$vCode]; ?>); display: block;"></div>
    </div>
</section>
<!-- *************banner section end************* -->

<!-- *************hot it works section start************* -->
<section class="howitworks page-section" id="how-it-works">
    <div class="howitworks-inner">
        <div class="horizonatal-title">
            <h3><?php echo $how_it_work_section['title_'.$vCode];?></h3>
            <strong><?php echo $how_it_work_section['subtitle_'.$vCode];?></strong>
        </div>
        <?php echo $how_it_work_section['desc_'.$vCode];?>
            <!-- How it Works sub Topics -->
            <ul>
            <?php for ($i=1; $i <= 4; $i++) { ?>
                <?php if (!empty($how_it_work_section['hiw_title'.$i.'_'.$vCode]) && !empty($how_it_work_section['hiw_desc'.$i.'_'.$vCode])) { ?>


                    <li>
                        <i><img alt="" class="proc_ico" src="<?php echo $tconfig["tsite_upload_apptype_page_images"].$template.'/'.$how_it_work_section['hiw_img'.$i.'_'.$vCode];?>" xss="removed">  </i>
                        <div class="works-caption"><strong><?php echo $how_it_work_section['hiw_title'.$i.'_'.$vCode];?></strong>
                        <p><?php echo $how_it_work_section['hiw_desc'.$i.'_'.$vCode];?></p>
                        </div>
                    </li>

                <?php } ?>
            <?php } ?>
            </ul>
            <!-- How it Works sub Topics End -->        
    </div>
   
</section>
<!-- *************hot it works section end************* -->

<!-- *************safty-section section start************* -->
<section class="safety-section taxi-variant page-section" id="fare-estimate">
    <div class="safety-section-inner">
        <div class="safety-section-left">
           <!--  <div class="safty-image-hold" style="background-image:url(<?php  echo $tconfig["tsite_upload_apptype_page_images"].$template.'/'.$calculate_section['img_'.$vCode]; ?>)"></div> -->
           <div class="safty-image-hold" style="background-image:url(<?= $tconfig["tsite_url"].'resizeImg.php?w=861&h=442&src='.$tconfig["tsite_upload_apptype_page_images"].$template.'/'.$calculate_section['img_'.$vCode]; ?>)"></div>
        </div>
        <div class="safety-section-right">
            <h3><?php echo $calculate_section['title_'.$vCode];?></h3>
            <?php echo $calculate_section['desc_'.$vCode];?>
            <form name="_fare_estimate_form" id="_fare_estimate_form" method="post" action="cx-fareestimate.php" class="gen-from">
				<input type="hidden" name="distance" id="distance" value="">
				<input type="hidden" name="duration" id="duration" value="">
				<input type="hidden" name="from_lat_long" id="from_lat_long" value="" >
				<input type="hidden" name="from_lat" id="from_lat" value="" >
				<input type="hidden" name="from_long" id="from_long" value="" >
				<input type="hidden" name="to_lat_long" id="to_lat_long" value="" >
				<input type="hidden" name="to_lat" id="to_lat" value="" >
				<input type="hidden" name="to_long" id="to_long" value="" >
				<input type="hidden" name="location_found" id="location_found" value="" >
                <input type="hidden" name="etype" id="etype" value="Deliver" >
				
                <div class="form-group pickup-location">
                    <label><?=$langage_lbl['LBL_HOME_ADD_PICKUP_LOC']; ?></label>
                    <input name="vPickup" type="text" id="from" placeholder="" />
                </div>
                <div class="form-group drop-location">
                    <label><?=$langage_lbl['LBL_ADD_DESTINATION_LOCATION_TXT']; ?></label>
                    <input name="vDest" type="text" id="to"  placeholder="" />
                </div>
                <div class="button-block">
                    <div class="btn-hold">
                        <input type="submit" name="btn_submit" value="Submit">
                        <img src="assets/img/apptype/<?php echo $template;?>/arrow.svg" alt="">
                    </div>
                </div>    
			</form>
        </div>
    </div>
</section>
<!-- *************safty-section section end************* -->

<!-- ************* delivery section section start ************* -->
<section class="delivery page-section" id="our-services">
    <div class="delivery-inner">
        <div class="horizonatal-title">
            <h3><?php echo $service_section['main_title_'.$vCode];?></h3>
            <strong><?php echo $service_section['main_desc_'.$vCode];?></strong>
        </div>
        <div class="delivery-row single_delivery">
            <div class="delivery-left">
                <div class="delivery-image-block" style="background-image:url(<?= $tconfig["tsite_url"].'resizeImg.php?h=405&src='.$tconfig["tsite_upload_apptype_page_images"].$template.'/'.$service_section['img_first_'.$vCode]; ?>)">
                    <button class="go-md"><img src="assets/img/apptype/<?php echo $template;?>/down.svg" alt=""></button>
                </div>
            </div>
            <div class="delivery-right">
                <div class="delivery-block-caption">
                    <h4><?php echo $service_section['title_first_'.$vCode];?></h4>
                    <p><?php echo $service_section['desc_first_'.$vCode];?></p>                    
                </div>
            </div>
        </div>
        <div class="delivery-row invert multi_delivery">
            <div class="delivery-left">
                <div class="delivery-image-block" style="background-image:url(<?= $tconfig["tsite_url"].'resizeImg.php?h=405&src='.$tconfig["tsite_upload_apptype_page_images"].$template.'/'.$service_section['img_sec_'.$vCode]; ?>)">
                    <button class="go-sd"><img src="assets/img/apptype/<?php echo $template;?>/up.svg" alt=""></button>
                </div>
            </div>
            <div class="delivery-right">
                <div class="delivery-block-caption">
                    <h4><?php echo $service_section['title_sec_'.$vCode];?></h4>
                    <p><?php echo $service_section['desc_sec_'.$vCode];?></p>
                </div>
            </div>
        </div>
    </div>
</section>
<!-- ************* delivery section section end ************* -->

<!-- ************* benefits section section start ************* -->
<section class="benefits page-section" id="our-benefits">
    <div class="benefits-inner">
        <div class="horizonatal-title">
            <h3><?= $benefit_section['main_title_'.$vCode]; ?></h3>
            <strong><?= $benefit_section['main_desc_'.$vCode]; ?></strong>
        </div>    
        <div class="benefits-row">
            <div class="benefits-left">
                <ul>
                    <?php if(!empty($btitle[0])) { ?>
                    <li>
                        <i><img src="<?php  echo $tconfig["tsite_upload_apptype_page_images"].$template.'/'.$bimg[0]; ?>" alt=""></i>
                        <strong><?= $btitle[0]; ?></strong>
                        <p><?= $bdesc[0]; ?></p>
                    </li>
                    <?php } if(!empty($btitle[2])) { ?>
                    <li>
                        <i><img src="<?php  echo $tconfig["tsite_upload_apptype_page_images"].$template.'/'.$bimg[2]; ?>" alt=""></i>
                        <strong><?= $btitle[2]; ?></strong>
                        <p><?= $bdesc[2]; ?></p>
                    </li>
                    <?php } if(!empty($btitle[4])) { ?>
                    <li>
                        <i><img src="<?php  echo $tconfig["tsite_upload_apptype_page_images"].$template.'/'.$bimg[4]; ?>" alt=""></i>
                        <strong><?= $btitle[4]; ?></strong>
                        <p><?= $bdesc[4]; ?></p>
                    </li>
                    <?php } ?>
                </ul>
            </div>
            <div class="benefits-middle data-middle">
                <img src="<?php  echo $tconfig["tsite_upload_apptype_page_images"].$template.'/'.$benefit_section['img_'.$vCode]; ?>" alt="">
            </div>
            <div class="benefits-right">
                <ul>
                    <?php if(!empty($btitle[1])) { ?>
                    <li>
                        <i><img src="<?php  echo $tconfig["tsite_upload_apptype_page_images"].$template.'/'.$bimg[1]; ?>" alt=""></i>
                        <strong><?= $btitle[1]; ?></strong>
                        <p><?= $bdesc[1]; ?></p>
                    </li>
                    <?php } if(!empty($btitle[3])) { ?>
                    <li>
                        <i><img src="<?php  echo $tconfig["tsite_upload_apptype_page_images"].$template.'/'.$bimg[3]; ?>" alt=""></i>
                        <strong><?= $btitle[3]; ?></strong>
                        <p><?= $bdesc[3]; ?></p>
                    </li>
                    <?php } if(!empty($btitle[5])) { ?>
                    <li>
                        <i><img src="<?php  echo $tconfig["tsite_upload_apptype_page_images"].$template.'/'.$bimg[5]; ?>" alt=""></i>
                        <strong><?= $btitle[5]; ?></strong>
                        <p><?= $bdesc[5]; ?></p>
                    </li>
                    <?php } ?>
                </ul>
            </div>
        </div>
    </div>
</section>
<!-- ************* benefits section section end ************* -->

<!-- ************* Fleet section section start ************* -->
<section class="fleet page-section parallax-window page-section delivery-variant" id="our-fleet" style="background-image:url(<?php  echo $tconfig["tsite_upload_apptype_page_images"].$template.'/'.$secure_section['img_'.$vCode]; ?>)">
    <div class="fleet-inner">
        <h3><?= $secure_section['main_title_'.$vCode]; ?></h3>
        <strong><?= $secure_section['main_desc_'.$vCode]; ?></strong>
        <ul>
            <?php if(!empty($secure_section['title_first_'.$vCode])) { ?>
            <li>
                <i class="fleet-icon blue_color"><img src="<?php  echo $tconfig["tsite_upload_apptype_page_images"].$template.'/'.$secure_section['img_first_'.$vCode]; ?>" alt=""></i>
                <strong><?= $secure_section['title_first_'.$vCode]; ?></strong>
            </li>
            <?php } if(!empty($secure_section['title_sec_'.$vCode])) { ?>
            <li>
                <i class="fleet-icon carrot_color"><img src="<?php  echo $tconfig["tsite_upload_apptype_page_images"].$template.'/'.$secure_section['img_sec_'.$vCode]; ?>" alt=""></i>
                <strong><?= $secure_section['title_sec_'.$vCode]; ?></strong>
                    
            </li>
            <?php } if(!empty($secure_section['title_third_'.$vCode])) { ?>
            <li>
                <i class="fleet-icon cyan_color"><img src="<?php  echo $tconfig["tsite_upload_apptype_page_images"].$template.'/'.$secure_section['img_third_'.$vCode]; ?>" alt=""></i>
                <strong><?= $secure_section['title_third_'.$vCode]; ?></strong>
                    
            </li>
            <?php } if(!empty($secure_section['title_fourth_'.$vCode])) { ?>
            <li>
                <i class="fleet-icon green_color"><img src="<?php  echo $tconfig["tsite_upload_apptype_page_images"].$template.'/'.$secure_section['img_fourth_'.$vCode]; ?>" alt=""></i>
                <strong><?= $secure_section['title_fourth_'.$vCode]; ?></strong>
                
            </li>
            <?php } if(!empty($secure_section['title_fifth_'.$vCode])) { ?>
            <li>
                <i class="fleet-icon purple_color"><img src="<?php  echo $tconfig["tsite_upload_apptype_page_images"].$template.'/'.$secure_section['img_fifth_'.$vCode]; ?>" alt=""></i>
                <strong><?= $secure_section['title_fifth_'.$vCode]; ?></strong>
                
            </li>
            <?php } if(!empty($secure_section['title_six_'.$vCode])) { ?>
            <li>
                <i class="fleet-icon yellow_color"><img src="<?php  echo $tconfig["tsite_upload_apptype_page_images"].$template.'/'.$secure_section['img_six_'.$vCode]; ?>" alt=""></i>
                <strong><?= $secure_section['title_six_'.$vCode]; ?></strong>
            </li>  
        <?php } ?>    
        </ul>
    </div>
</section>
<!-- ************* fleet section section end ************* -->

<!-- *************download section section start************* -->
<?php
$tMessage_link1 = $download_section['link1_'.$vCode];
$tMessage_link1 = str_replace($key_arr, $val_arr, $tMessage_link1);
$tMessage_link2 = $download_section['link2_'.$vCode];
$tMessage_link2 = str_replace($key_arr, $val_arr, $tMessage_link2); 
?>
<section class="get_app_area sec_pad page-section" id="download-apps">
    <div class="get_app_area-inner">
        <div class="get_app_area-left">
            <div class="get_app_content">
                <div class="section_title">
                    <h2><?php echo $download_section['title_'.$vCode];?></h2>
                </div>
                <?php echo $download_section['desc_'.$vCode];?>
                <a href="<?= $tMessage_link1; ?>" class="app_btn slider_btn"><img src="assets/img/apptype/<?php echo $template;?>/play-store.png" alt=""><?=$langage_lbl['LBL_GOOGLE_PLAY']; ?></a>
                <a href="<?= $tMessage_link2; ?>" class="app_btn_two slider_btn"><img src="assets/img/apptype/<?php echo $template;?>/apple-store.png" alt=""><?=$langage_lbl['LBL_APP_STORE']; ?></a>
            </div>
        </div>
        <div class="get_app_area-right app_image">
            <div class="image_first">
                <img src="<?php  echo $tconfig["tsite_upload_apptype_page_images"].$template.'/'.$download_section['img_'.$vCode]; ?>" alt="">
                <div class="shadow_bottom"></div>
            </div>
            <div class="image_two">
                <img src="<?php  echo $tconfig["tsite_upload_apptype_page_images"].$template.'/'.$download_section['img2_'.$vCode]; ?>" alt="">
                <div class="shadow_bottom"></div>
            </div>
        </div>
    </div>
</section>
<!-- *************download section section end************* -->

<!-- *************call to section end************* -->
<?php
$tMessage_call = $call_section['desc_'.$vCode];
$tMessage_call = str_replace($key_arr, $val_arr, $tMessage_call); ?>
<div class="delivery-use">
<section class="call-section page-section taxi-variant" id="book-delivery">  
    <div class="call-section-inner">
        <div class="call-section-right">
            <div class="call-section-image" style="background-image:url(<?= $tconfig["tsite_url"].'resizeImg.php?h=412&src='.$tconfig["tsite_upload_apptype_page_images"].$template.'/'.$call_section['img_'.$vCode]; ?>)"></div>
        </div>
        <div class="call-section-left">
            <h3><?php echo $call_section['title_'.$vCode];?></h3>
            <?php echo $tMessage_call;?>
        </div>
    </div>
</section>
</div>
<!-- *************call to section end************* -->


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
<script>
	var autocomplete_from;
	var autocomplete_to;
	$(function () {
		
		var from = document.getElementById('from');
		autocomplete_from = new google.maps.places.Autocomplete(from);
		google.maps.event.addListener(autocomplete_from, 'place_changed', function() {
			var place = autocomplete_from.getPlace();
			$("#from_lat_long").val(place.geometry.location);
			$("#from_lat").val(place.geometry.location.lat());
			$("#from_long").val(place.geometry.location.lng());
		});
		
		var to = document.getElementById('to');
		autocomplete_to = new google.maps.places.Autocomplete(to);
		google.maps.event.addListener(autocomplete_to, 'place_changed', function() {
			var place = autocomplete_to.getPlace();
			$("#to_lat_long").val(place.geometry.location);
			$("#to_lat").val(place.geometry.location.lat());
			$("#to_long").val(place.geometry.location.lng());
		});
	});
    /* for do not fire enter key to submit the form */
    document.getElementById('from').addEventListener('keypress', function(event) {
        if (event.keyCode == 13) {
            event.preventDefault();
        }
    });
    document.getElementById('to').addEventListener('keypress', function(event) {
        if (event.keyCode == 13) {
            event.preventDefault();
        }
    });
</script>