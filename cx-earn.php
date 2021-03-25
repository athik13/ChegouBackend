<?php
include_once("common.php");
$showSignRegisterLinks = 1;

//$table_name = 'content_cubex_details';
$table_name = $generalobj->getContentCMSHomeTable();
$vCode = $_SESSION['sess_lang'];
$eFor = 'Earn';
$ride_data_query = "SELECT * FROM ".$table_name." WHERE eFor = '" . $eFor . "'";
$ride_data = $obj->MySQLSelect($ride_data_query);

$banner_section = json_decode($ride_data[0]['lBannerSection'],true);
if(empty($banner_section['title_'.$vCode])) {
    $vCode = 'EN';
}

$inner_key = array('menu_title_','title_','sub_title_','desc_','img_','title_first_','desc_first_','img_first_','title_sec_','desc_sec_','img_sec_','title_third_','desc_third_','img_third_','title_fourth_','desc_fourth_','img_fourth_','title_fifth_','desc_fifth_','img_fifth_','title_six_','desc_six_','img_six_','main_title_','main_desc_','img2_');

$banner_section = $generalobj->checkOtherLangDataExist(json_decode($ride_data[0]['lBannerSection'],true),$vCode,$inner_key);
$how_it_work_section = $generalobj->checkOtherLangDataExist(json_decode($ride_data[0]['lHowitworkSection'],true),$vCode,$inner_key);
$earn_section = $generalobj->checkOtherLangDataExist(json_decode($ride_data[0]['lEarnSection'],true),$vCode,$inner_key);
$secure_section = $generalobj->checkOtherLangDataExist(json_decode($ride_data[0]['lSecuresafeSection'],true),$vCode,$inner_key);
$download_section = $generalobj->checkOtherLangDataExist(json_decode($ride_data[0]['lDownloadappSection'],true),$vCode,$inner_key);
$calculate_section = $generalobj->checkOtherLangDataExist(json_decode($ride_data[0]['lCalculateSection'],true),$vCode,$inner_key);
$cartype_section = $generalobj->checkOtherLangDataExist(json_decode($ride_data[0]['lCartypeSection'],true),$vCode,$inner_key);
$service_section = $generalobj->checkOtherLangDataExist(json_decode($ride_data[0]['lServiceSection'],true),$vCode,$inner_key);
$benefit_section = $generalobj->checkOtherLangDataExist(json_decode($ride_data[0]['lBenefitSection'],true),$vCode,$inner_key);

$menutitleHow = !empty($how_it_work_section['menu_title_'.$vCode]) ? $how_it_work_section['menu_title_'.$vCode] : $how_it_work_section['title_'.$vCode]; 
$menutitleSecure = !empty($secure_section['menu_title_'.$vCode]) ? $secure_section['menu_title_'.$vCode] : $secure_section['title_'.$vCode]; 
$menutitleEarn = !empty($earn_section['menu_title_'.$vCode]) ? $earn_section['menu_title_'.$vCode] : $earn_section['title_'.$vCode]; 
$menutitleDown = !empty($download_section['menu_title_'.$vCode]) ? $download_section['menu_title_'.$vCode] : $download_section['title_'.$vCode]; 
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
    <title><?=$SITE_NAME?> | <?= $langage_lbl['LBL_FOOTER_LINK_EARN'] ?></title>
    <!--	<title><?php echo $meta_arr['meta_title'];?></title>
	<meta name="keywords" value="<?=$meta_arr['meta_keyword'];?>"/>
	<meta name="description" value="<?=$meta_arr['meta_desc'];?>"/>-->
    <!-- Default Top Script and css -->
    <?php include_once("top/top_script.php");?>
    <!-- End: Default Top Script and css-->
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
            <li class="tab active">
                <a href="#how-it-works"><?= $menutitleHow ?></a>
            </li>
             <li class="tab">
                <a href="#our-benefits"><?= $menutitleBenefit ?></a>
            </li>
             <li class="tab">
                <a href="#earn"><?= $menutitleEarn ?></a>
            </li>
            
            <li class="tab">
                <a href="#security"><?= $menutitleSecure ?></a>
            </li>
            <li class="tab">
                <a href="#download-apps"><?= $menutitleDown ?></a>
            </li>
             <li class="tab">
                <a href="#profile"><?= $menutitleService ?></a>
            </li>
            <!-- <li class="tab mob">
                <a href="#">&nbsp</a>
            </li> -->
        </ul>
    </div>
    <div class="banner-section-inner">
        <div class="categories-block">
            <div class="categories-caption active">
                <h2><?= $banner_section['title_'.$vCode];?><span><?= $banner_section['sub_title_'.$vCode];?></span></h2>
                <p><?= $banner_section['desc_'.$vCode];?></p>
            </div>
        </div>
    </div>
    <div class="banner-back">
        <div class="banner-image" id="1" style="background-image: url(<?= $tconfig["tsite_upload_apptype_page_images"].$template.'/'.$banner_section['img_'.$vCode]; ?>); display: block;"></div>
    </div>
</section>
<!-- *************banner section end************* -->
<!-- *************hot it works section start************* -->
<section class="features page-section corporate" id="how-it-works">
    <div class="features-inner">
        <div class="features-row">
            <div class="features-left">
                <div class="features-main-image" style="background-image:url(<?= $tconfig["tsite_url"].'resizeImg.php?h=534&src='.$tconfig["tsite_upload_apptype_page_images"].$template.'/'.$how_it_work_section['img_'.$vCode]; ?>)"></div>
            </div>
            <div class="features-right">
                <h3><?= $how_it_work_section['title_'.$vCode] ?></h3>
                <?= $how_it_work_section['desc_'.$vCode] ?>
            </div>
        </div>
    </div>
</section>
<!-- *************hot it works section end************* -->

<!-- ************* benefits section section start ************* -->
<section class="benefits page-section dark-variant" id="our-benefits">
    <div class="benefits-inner">
        <h3><?= $benefit_section['main_title_'.$vCode]; ?></h3>
        <strong><?= $benefit_section['main_desc_'.$vCode]; ?></strong>
        <ul class="listing-style">
            <?php if(!empty($btitle[0])) { ?>
            <li>
                <i><img src="<?php  echo $tconfig["tsite_upload_apptype_page_images"].$template.'/'.$bimg[0]; ?>" alt=""></i>
                <strong><?= $btitle[0]; ?></strong>
                <p><?= $bdesc[0]; ?></p>
            </li>
            <?php } if(!empty($btitle[1])) { ?>
            <li>
                <i><img src="<?php  echo $tconfig["tsite_upload_apptype_page_images"].$template.'/'.$bimg[1]; ?>" alt=""></i>
                <strong><?= $btitle[1]; ?></strong>
                <p><?= $bdesc[1]; ?></p>
            </li>
            <?php } if(!empty($btitle[2])) { ?>
            <li>
                <i><img src="<?php  echo $tconfig["tsite_upload_apptype_page_images"].$template.'/'.$bimg[2]; ?>" alt=""></i>
                <strong><?= $btitle[2]; ?></strong>
                <p><?= $bdesc[2]; ?></p>
            </li>
            <?php } if(!empty($btitle[3])) { ?>
            <li>
                <i><img src="<?php  echo $tconfig["tsite_upload_apptype_page_images"].$template.'/'.$bimg[3]; ?>" alt=""></i>
                <strong><?= $btitle[3]; ?></strong>
                <p><?= $bdesc[3]; ?></p>
            </li>
            <?php } if(!empty($btitle[4])) { ?>
            <li>
                <i><img src="<?php  echo $tconfig["tsite_upload_apptype_page_images"].$template.'/'.$bimg[4]; ?>" alt=""></i>
                <strong><?= $btitle[4]; ?></strong>
                <p><?= $bdesc[4]; ?></p>
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
</section>
<!-- ************* benefits section section end ************* -->

<!-- *************article section section start************* -->
<article class="blog-article">
    <div class="article-inner">
        <div class="article-row page-section" id="earn">
            <div class="article-left">
                <div class="article-mage" style="background-image:url(<?= $tconfig["tsite_url"].'resizeImg.php?h=493&src='.$tconfig["tsite_upload_apptype_page_images"].$template.'/'.$earn_section['img_'.$vCode]; ?>)"></div>
            </div>
            <div class="article-right">
                <h4><?= $earn_section['title_'.$vCode] ?></h4>
                <p><?= $earn_section['desc_'.$vCode] ?></p>
            </div>
        </div>
        <div class="article-row invert page-section" id="security">
            <div class="article-left">
                <div class="article-mage" style="background-image:url(<?= $tconfig["tsite_url"].'resizeImg.php?h=493&src='.$tconfig["tsite_upload_apptype_page_images"].$template.'/'.$secure_section['img_'.$vCode]; ?>)"></div>
            </div>
            <div class="article-right">
                <h4><?= $secure_section['title_'.$vCode] ?></h4>
                <p><?= $secure_section['desc_'.$vCode] ?></p>
            </div>
        </div>
    </div>
</article>
<!-- *************article section section end************* -->

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



<!-- ************* profiletype section start ************* -->
<section class="step-sec page-section" id="profile">
    <div class="step-sec-inner">
        <h3><?= $service_section['title_'.$vCode] ?></h3>
        <strong><?= $service_section['desc_'.$vCode] ?></strong>
        <ul>
            <?php if(!empty($service_section['title_first_'.$vCode]) && !empty($service_section['desc_first_'.$vCode])) { ?>    
                <li>
                    <img src="<?= $tconfig["tsite_upload_apptype_page_images"].$template.'/'.$service_section['img_first_'.$vCode]; ?>" alt="">
                    <strong><?= $service_section['title_first_'.$vCode] ?></strong>
                    <p><?= $service_section['desc_first_'.$vCode] ?></p>
                </li>
            <?php } ?>
            <?php if(!empty($service_section['title_sec_'.$vCode]) && !empty($service_section['desc_sec_'.$vCode])) { ?>    
                <li>
                    <img src="<?= $tconfig["tsite_upload_apptype_page_images"].$template.'/'.$service_section['img_sec_'.$vCode]; ?>" alt="">
                    <strong><?= $service_section['title_sec_'.$vCode] ?></strong>
                    <p><?= $service_section['desc_sec_'.$vCode] ?></p>
                </li>
            <?php } ?>
            <?php if(!empty($service_section['title_third_'.$vCode]) && !empty($service_section['desc_third_'.$vCode])) { ?>    
                <li>
                    <img src="<?= $tconfig["tsite_upload_apptype_page_images"].$template.'/'.$service_section['img_third_'.$vCode]; ?>" alt="">
                    <strong><?= $service_section['title_third_'.$vCode] ?></strong>
                    <p><?= $service_section['desc_third_'.$vCode] ?></p>
                </li>
            <?php } ?>
        </ul>
    </div>
</section>

<!-- ************* profiletype section end ************* -->




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
<script>
    $(document).ready(function(){
        $(window).scroll(function () {
            var scrollDistance = $(window).scrollTop()+75;
            target = $('.features.corporate#how-it-works').offset().top
            if(scrollDistance > target) {
                $('.features.corporate#how-it-works').addClass('zisuue');
            }else {
                $('.features.corporate#how-it-works').removeClass('zisuue');
            }
        })
    })
</script>
</body>
</html>
