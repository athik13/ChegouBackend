<?php
$showSignRegisterLinks = 1;
$how_it_work_section = json_decode($data[0]['lHowitworkSection'],true);
//Added By HJ On 16-05-2020 For Solved Issue to Be Fixed Issue #1330 Start
if (strpos($how_it_work_section['desc'], "icon") !== false) {
    $imgCount = 4;
    $tsiteUrl = $tconfig['tsite_url'];
    for ($g = 1; $g <= $imgCount; $g++) {
        $imgUrl = $tsiteUrl . "assets/img/apptype/" . $template . "/icon" . $g . ".jpg";
        //echo $imgUrl."<br>";
        $how_it_work_section['desc'] = str_replace("icon" . $g . ".jpg", $imgUrl, $how_it_work_section['desc']);
    }
}
//Added By HJ On 16-05-2020 For Solved Issue to Be Fixed Issue #1330 End
$secure_section = json_decode($data[0]['lSecuresafeSection'],true);
$download_section = json_decode($data[0]['lDownloadappSection'],true);
$call_section = json_decode($data[0]['lCalltobookSection'],true);
$service_section = $data[0]['lServiceSection'];
?>
<!-- ************* services section section start ************* -->
<section class="services homepage page-section" id="our-services">
    <div class="services-inner">
        <h3><?= $service_section; ?></h3>
        <?php if (!empty($data[0]['booking_ids'])) {
            $vehicleFirstImage = $data[0]['booking_ids'];
            $lang = isset($_SESSION['sess_lang']) ? $_SESSION['sess_lang'] : "EN";
            $vcatdata = $generalobj->getSeviceCategoryDataForHomepage($vehicleFirstImage,0,1);
            //$catquery = "SELECT iVehicleCategoryId,vCatTitleHomepage,vServiceCatTitleHomepage,vServiceHomepageBanner FROM  `vehicle_category` WHERE iParentId = 0 and eStatus = 'Active' $ssql and iVehicleCategoryId IN($vehicleFirstImage) ORDER BY iDisplayOrderHomepage ASC";
            //$vcatdata = $obj->MySQLSelect($catquery);
            
            $vCatTitleHomepage = json_decode($vcatdata[$i]['vCatTitleHomepage'], true);
            if (empty($vCatTitleHomepage['vCatTitleHomepage_' . $lang])) {
                $lang = 'EN';
                $vcatdata = $generalobj->getSeviceCategoryDataForHomepage($vehicleFirstImage,0,1);
                /*$catquery = "SELECT iVehicleCategoryId,vCatTitleHomepage,vServiceCatTitleHomepage,vServiceHomepageBanner FROM  `vehicle_category` WHERE iParentId = 0 and eStatus = 'Active' $ssql and iVehicleCategoryId IN($vehicleFirstImage) ORDER BY iDisplayOrderHomepage ASC";
                $vcatdata = $obj->MySQLSelect($catquery);*/
            }
            //$urlCat = array('174'=>'Ride','178'=>'Delivery','175'=>'Moto','276'=>'Fly'); //redirect to booking page
            //$urlCat = array('174'=>'taxi','178'=>'delivery','175'=>'moto','276'=>'fly','182'=>'food','183'=>'grocery');
        ?>
        <ul>
            <?php for ($i = 0; $i < count($vcatdata); $i++) {
                //$vServiceCatTitleHomepage = json_decode($vcatdata[$i]['vServiceCatTitleHomepage'], true);
                if(file_exists($tconfig["tsite_upload_home_page_service_images_panel"] . '/' . $vcatdata[$i]['vServiceHomepageBanner']) && !empty($vcatdata[$i]['vServiceHomepageBanner'])) {
                    $back_img = "";
                } else {
                    $back_img = "";
                }
                //if(empty($urlCat[$vcatdata[$i]['iVehicleCategoryId']])) $urlCat[$vcatdata[$i]['iVehicleCategoryId']] = 'UberX'; //redirect to booking page
                //$url = "userbooking.php?userType1=rider&navigatedPage=".$urlCat[$vcatdata[$i]['iVehicleCategoryId']]; //redirect to booking page
                //if(empty($urlCat[$vcatdata[$i]['iVehicleCategoryId']])) $urlCat[$vcatdata[$i]['iVehicleCategoryId']] = 'otherservices';
                //$url = $urlCat[$vcatdata[$i]['iVehicleCategoryId']];
            ?>
            <li>
                <div class="service-block">
                    <? if(file_exists($tconfig["tsite_upload_home_page_service_images_panel"] . '/' . $vcatdata[$i]['vServiceHomepageBanner']) && !empty($vcatdata[$i]['vServiceHomepageBanner'])) { ?>
                    <div style="background-image:url(<?= $tconfig["tsite_upload_home_page_service_images"] . '/' . $vcatdata[$i]['vServiceHomepageBanner']; ?>)" class="services-images"></div>
                    <? } else { ?>
                    <div style="background-color:#dddddd;" class="services-images"></div>
                    <? } ?>
                    <a href="<?= $vcatdata[$i]['url']; ?>" target="_blank"><?= $vcatdata[$i]['vCatName'];  ?></a>
                </div>
            </li>
            <?php } ?>
        </ul>
        <?php } ?>
        <ul class="services-listing">
            <?php
            //$catquery = "SELECT vHomepageLogo,vCategory_$lang as vCatName FROM  `vehicle_category` WHERE iParentId = 0 and eStatus = 'Active' ORDER BY iDisplayOrderHomepage ASC";
            //$vcatdata = $obj->MySQLSelect($catquery);
            $vcatdata = $generalobj->getSeviceCategoryDataForHomepage($vehicleFirstImage,1,1);
            $j = 0;
            for ($i = 0; $i < count($vcatdata); $i++) {
                $j++;
               if(file_exists($tconfig["tsite_upload_home_page_service_images_panel"] . '/' . $vcatdata[$i]['vHomepageLogo']) && !empty($vcatdata[$i]['vHomepageLogo'])) {
            ?>
            <li><a href="<?= $vcatdata[$i]['url']; ?>" style="text-decoration: none;"><b><img src="<?= $tconfig["tsite_url"].'resizeImg.php?w=140&h=140&src='.$tconfig["tsite_upload_home_page_service_images"] . '/' . $vcatdata[$i]['vHomepageLogo']; ?>" alt="<?= $vcatdata[$i]['vCatName'] ?>" title="<?= $vcatdata[$i]['vCatName'] ?>"></b><strong><?= $vcatdata[$i]['vCatName'] ?></strong>
            <?php if(count($vcatdata)!=$j) { ?><img src="assets/img/apptype/<?php echo $template;?>/sap.png" class="sap-shape" alt=""><?php } ?>
            </a>
            </li>
            <?php } else { ?>
            <!--<li><b  class="noimg"  style="background-color: #dddddd"><img src="" class="sap-shape" alt=""></b><strong><?= $vcatdata[$i]['vCatName'] ?></strong></li>-->
            <?php } } ?>
        </ul>
    </div>
</section>
<!-- ************* services section section end ************* -->

<!-- *************download section section start************* -->
<section class="download-section homepage parallax-window" style="background-image:url(<?= $tconfig["tsite_upload_apptype_page_images"] . $template . '/' . $download_section['img']; ?>)">
    <div class="download-section-inner">
        <div class="download-heading-area">
            <h3><?= $download_section['title']; ?></h3>
            <strong><?= $download_section['subtitle']; ?></strong>
        </div>
        <div class="download-caption">
            <?= $download_section['desc']; ?>
            <div class="download-links">
                <a href="<?= $ANDROID_APP_LINK ?>" target="_blank">
                    <img alt="" src="assets/img/apptype/<?php echo $template;?>/google-play.png">
                </a
                ><a href="<?= $IPHONE_APP_LINK ?>" target="_blank">
                <img alt="" src="assets/img/apptype/<?php echo $template;?>/istore.png">
            </a>
            </div>
        </div>
        <div class="screens-row">
            <div class="app-screens-block">
                <div class="app-screen-col">
                    <img src="<?= $tconfig["tsite_upload_apptype_page_images"] . $template . '/' . $download_section['img_first']; ?>" alt="">
                </div>
                <div class="app-screen-col">
                    <img src="<?= $tconfig["tsite_upload_apptype_page_images"] . $template . '/' . $download_section['img_sec']; ?>" alt="">
                </div>
                <div class="app-screen-col">
                    <img src="<?= $tconfig["tsite_upload_apptype_page_images"] . $template . '/' . $download_section['img_third']; ?>" alt="">
                </div>
            </div>
        </div>
    </div>
</section>
<!-- *************download section section end************* -->

<!-- *************hot it works section start************* -->
<section class="how-it-works-section homepage-variant page-section" id="how-it-works">
    <div class="how-it-works-section-inner">
        <div class="how-it-works-left">
            <h3><?= $how_it_work_section['title'] ?></h3>
            <!-- How it Works sub Topics -->
            <?= $how_it_work_section['desc'] ?>
        </div>
        <div class="how-it-works-right">
            <img src="<?php  echo $tconfig["tsite_upload_apptype_page_images"] . $template . '/'.$how_it_work_section['img']; ?>" alt="">
        </div>
    </div>
</section>
<!-- *************hot it works section end************* -->

<!-- *************article to section start************* -->
<?php if(!empty($secure_section['title']) && !empty($secure_section['desc'])) { ?>
<section class="article-section">
    <div class="article-section-inner">
        <div class="article-section-left">
            <img src="<?php  echo $tconfig["tsite_upload_apptype_page_images"] . $template . '/'.$secure_section['img']; ?>" alt="">
        </div>
        <div class="article-section-right">
            <h3><?= $secure_section['title'] ?></h3>
            <?= $secure_section['desc'] ?>
        </div>
    </div>
</section>
<?php } ?>
<?php if(!empty($call_section['title']) && !empty($call_section['desc'])) { ?>
<section class="article-section reverse">
    <div class="article-section-inner">
        <div class="article-section-left">
            <img src="<?php  echo $tconfig["tsite_upload_apptype_page_images"] . $template . '/'.$call_section['img']; ?>" alt="">
        </div>
        <div class="article-section-right">
            <h3><?= $call_section['title'] ?></h3>
            <?= $call_section['desc'] ?>
        </div>
    </div>
</section>
<?php } ?>
<!-- *************article to section end************* -->