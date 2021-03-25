<!-- *************banner section start************* -->
<?php
$CURRENT_FILE_NAME = basename($_SERVER['SCRIPT_FILENAME']);
if (!empty($data[0]['vehicle_category_ids']) && $CURRENT_FILE_NAME=='index.php') {
    $earnData = json_decode($data[0]['learnServiceCatSection'], true);
    $businessData = json_decode($data[0]['lbusinessServiceCatSection'], true);
    $vearnbusiness_categoryJson = json_decode($data[0]['vearnbusiness_category'], true);
    $ssql = "";
    if ($enable_fly != 'Yes') {
        $ssql .= " AND eCatType != 'Fly'";
    }
    ?>
    <section class="banner-section">

        <div class="banner-back">
            <?php
            $vehicleFirstImage = $data[0]['vehicle_category_ids'];
            $lang = isset($_SESSION['sess_lang']) ? $_SESSION['sess_lang'] : "EN";
            $catquery = "SELECT iVehicleCategoryId,vHomepageLogo,vHomepageBanner,vCategory_$lang,vCatNameHomepage,vCatTitleHomepage,vCatSloganHomepage,lCatDescHomepage,vCatDescbtnHomepage,iDisplayOrderHomepage FROM  `vehicle_category` WHERE iParentId = 0 and eStatus = 'Active' $ssql and iVehicleCategoryId IN($vehicleFirstImage) ORDER BY iDisplayOrderHomepage ASC";
            $vcatdata = $obj->MySQLSelect($catquery);
            
            /* sorting data using iDisplayOrderHomepage for earn and business data
            $earnData['iDisplayOrderHomepage'] = 3;
            $earnData_tmp[] = $earnData;
            $vcatdata = array_merge($vcatdata,$earnData_tmp);
            
            $volume = array_column($vcatdata, 'iDisplayOrderHomepage');
            array_multisort($volume, SORT_ASC, $vcatdata);
            print_R($vcatdata); exit; */

            $vCatTitleHomepage = json_decode($vcatdata[$i]['vCatTitleHomepage'], true);
            if (empty($vCatTitleHomepage['vCatTitleHomepage_' . $lang])) {
                $lang = 'EN';
                $catquery = "SELECT iVehicleCategoryId,vHomepageLogo,vHomepageBanner,vCategory_$lang,vCatNameHomepage,vCatTitleHomepage,vCatSloganHomepage,lCatDescHomepage,vCatDescbtnHomepage FROM  `vehicle_category` WHERE iParentId = 0 and eStatus = 'Active' $ssql and iVehicleCategoryId IN($vehicleFirstImage) ORDER BY iDisplayOrderHomepage ASC";
                $vcatdata = $obj->MySQLSelect($catquery);
            }
            $rightalign = 0;
            if(count($vcatdata)<=1) {
                $rightalign = 1;
            }
            if(count($vcatdata)==2) {
                $rightalign = 2;
            }
            for ($i = 0; $i < count($vcatdata); $i++) {
                ?>
                <div class="banner-image" id="<?= $i + 1 ?>" style="background-image:url(<?= $tconfig["tsite_upload_home_page_service_images"] . '/' . $vcatdata[$i]['vHomepageBanner']; ?>)"></div>
            <?php
            }

            if (isset($vearnbusiness_categoryJson['earn']) && $vearnbusiness_categoryJson['earn'] == 1) {
                $vHomepageBanner = $earnData['vHomepageBanner'];
                ?><div class="banner-image" id="earn" style="background-image:url(<?= $tconfig["tsite_upload_home_page_service_images"] . '/' . $vHomepageBanner; ?>)"></div><?php
            }
            if (isset($vearnbusiness_categoryJson['business']) && $vearnbusiness_categoryJson['business'] == 1) {
                $vHomepageBanner = $businessData['vHomepageBanner'];
                ?><div class="banner-image" id="business" style="background-image:url(<?= $tconfig["tsite_upload_home_page_service_images"] . '/' . $vHomepageBanner; ?>)"></div><?php
            }
            ?>
        </div>
        <div class="banner-section-inner">
            <?php //if($rightalign!=1) { ?>
            <div class="tab-row-holding" <?php if($rightalign==1) { ?>style="height:0px;display:none" <?php } ?>>
                <ul class="tab-row homepage <?php if($rightalign==2) { ?>twobanner<?php } if(count($vcatdata)>7) { ?>morerows<?php } ?>">
                    <?php
                    for ($i = 0; $i < count($vcatdata); $i++) {
                        $vCatNameHomepage = json_decode($vcatdata[$i]['vCatNameHomepage'], true);
                        $vCatSloganHomepage = json_decode($vcatdata[$i]['vCatSloganHomepage'], true);
                        ?>
                        <li data-id="DESC<?= $i + 1 ?>" data-src="<?= $i + 1 ?>" class="tab <?php if ($i == 0) { ?>active<?php } ?>">
                        <?php if ($vcatdata[$i]['vHomepageLogo'] == '' || !file_exists($tconfig["tsite_upload_home_page_service_images_panel"] . '/' . $vcatdata[$i]['vHomepageLogo'])) { ?>
                                <div class="defaultimg-banner-icon"></div>
                        <?php } else { ?>
                                <img src="<?= $tconfig["tsite_upload_home_page_service_images"] . '/' . $vcatdata[$i]['vHomepageLogo']; ?>" alt="">
                        <?php } ?>
                            <span data-slogan="<?= $vCatSloganHomepage['vCatSloganHomepage_' . $lang]; ?>"><?= $vCatNameHomepage['vCatNameHomepage_' . $lang]; ?></span>
                        </li>
                        <?php
                        }
                        if (isset($vearnbusiness_categoryJson['earn']) && $vearnbusiness_categoryJson['earn'] == 1) {
                            ?>
                        <li data-id="DESCearn" data-src="earn" class="tab">
                        <?php if ($earnData['vHomepageLogo'] == '' || !file_exists($tconfig["tsite_upload_home_page_service_images_panel"] . '/' . $earnData['vHomepageLogo'])) { ?>
                                <div class="defaultimg-banner-icon"></div>
                        <?php } else { ?>
                                <img src="<?= $tconfig["tsite_upload_home_page_service_images"] . '/' . $earnData['vHomepageLogo']; ?>" alt="">
                        <?php } ?>
                            <span data-slogan="<?= $earnData['vCatSloganHomepage']; ?>"><?= $earnData['vCatNameHomepage']; ?></span>
                        </li>
                        <?php
                        }
                        if (isset($vearnbusiness_categoryJson['business']) && $vearnbusiness_categoryJson['business'] == 1) {
                            ?>
                        <li data-id="DESCbusiness" data-src="business" class="tab">
                        <?php if ($businessData['vHomepageLogo'] == '' || !file_exists($tconfig["tsite_upload_home_page_service_images_panel"] . '/' . $businessData['vHomepageLogo'])) { ?>
                                <div class="defaultimg-banner-icon"></div>
                        <?php } else { ?>
                                <img src="<?= $tconfig["tsite_upload_home_page_service_images"] . '/' . $businessData['vHomepageLogo']; ?>" alt="">
                        <?php } ?>
                            <span data-slogan="<?= $businessData['vCatSloganHomepage']; ?>"><?= $businessData['vCatNameHomepage']; ?></span>
                        </li>
                <?php } ?>
                </ul>
            </div>
            <?php //} else { ?>
           <!-- <ul class="tab-row" style="display:none"><li data-id="DESC1" data-src="1" class="tab active">
                <?php if ($vcatdata[0]['vHomepageLogo'] != '') { ?><img src="<?= $tconfig["tsite_upload_home_page_service_images"] . '/' . $vcatdata[0]['vHomepageLogo']; ?>" alt=""><?php } else { ?>
                        <img src="<?= $tconfig["tsite_upload_apptype_page_images"] . $template . '/chrysanthemum-copy-test_ES.jpg'; ?>" alt="">
                <?php } ?>
                    <span data-slogan="<?= $vCatSloganHomepage['vCatSloganHomepage_' . $lang]; ?>"><?= $vCatNameHomepage['vCatNameHomepage_' . $lang]; ?></span>
                </li></ul>-->
            <?php //} ?>
            <div class="categories-block" <?php if($rightalign==1) { ?>style="float: right"<?php } ?>>
                <?php
                $catlinks = array('174'=>'taxi','182'=>'food','178'=>'delivery','175'=>'moto','276'=>'fly');
                //$catlinks = array('taxi', 'food', 'delivery', 'moto', 'fly', 'earn', 'corporate-ride');
                for ($i = 0; $i < count($vcatdata); $i++) {
                    $vCatTitleHomepage = json_decode($vcatdata[$i]['vCatTitleHomepage'], true);
                    $lCatDescHomepage = json_decode($vcatdata[$i]['lCatDescHomepage'], true);
                    $vCatDescbtnHomepage = json_decode($vcatdata[$i]['vCatDescbtnHomepage'], true);
                    ?>
                    <div id="DESC<?= $i + 1 ?>" class="categories-caption <?php if ($i == 0) { ?>active<?php } ?>">
                        <h2><?= $vCatTitleHomepage['vCatTitleHomepage_' . $lang]; ?></h2>
                        <p><?= $lCatDescHomepage['lCatDescHomepage_' . $lang]; ?></p>
                        <a href="<?= $catlinks[$vcatdata[$i]['iVehicleCategoryId']] ?>" class="book-btn"><?= $vCatDescbtnHomepage['vCatDescbtnHomepage_' . $lang]; ?><img src="<?= $tconfig["tsite_upload_apptype_images"] . $template . "/arrow.svg" ?>" alt=""></a>
                    </div>
    <?php } ?>

                <div id="DESCearn" class="categories-caption">
                    <h2><?= $earnData['vCatTitleHomepage']; ?></h2>
                    <p><?= $earnData['lCatDescHomepage']; ?></p>
                    <!--<a href="<?= $catlinks[$i] ?>" class="book-btn"><?= $earnData['vCatDescbtnHomepage']; ?><img src="<?= $tconfig["tsite_upload_apptype_images"] . $template . "/arrow.svg" ?>" alt=""></a>-->
                    <a href="earn" class="book-btn"><?= $earnData['vCatDescbtnHomepage']; ?><img src="<?= $tconfig["tsite_upload_apptype_images"] . $template . "/arrow.svg" ?>" alt=""></a>
                </div>
                <div id="DESCbusiness" class="categories-caption">
                    <h2><?= $businessData['vCatTitleHomepage']; ?></h2>
                    <p><?= $businessData['lCatDescHomepage']; ?></p>
                    <a href="corporate-ride" class="book-btn"><?= $businessData['vCatDescbtnHomepage']; ?><img src="<?= $tconfig["tsite_upload_apptype_images"] . $template . "/arrow.svg" ?>" alt=""></a>
                </div>
            </div>
        </div>
    </section>
<?php } else {
$generalData = json_decode($data[0]['lGeneralBannerSection'], true);
if(!empty($generalData['title']) || !empty($generalData['desc'])) {
    $vHomepageBanner =  $generalData['img'];
    $vHomepageBannerSec =  $generalData['img_sec'];
?>
    <section class="banner-section home-sec-pg-banner">
        <ul class="tab-row" style="display:none">
            <li data-id="DESC1" data-src="1" class="tab active"></li>
        </ul>
        
        <div class="banner-section-inner">
            <div class="banner-both-img"> 
                <div class="banner-image-first <?php if(!empty($vHomepageBannerSec)) { ?> first-overlay <?php } ?>" style="background-image:url(<?= $tconfig["tsite_upload_apptype_page_images"].$template.'/'.$vHomepageBanner; ?>)"></div>
                <?php if(!empty($vHomepageBannerSec)) { ?>
                <div class="home-sec-pg-banner-backimg"><img src="<?= $tconfig["tsite_upload_apptype_page_images"].$template.'/'.$vHomepageBannerSec; ?>" class="banner-img-sec"></div>
                <?php } ?>
            </div>
                <div class="categories-block" style="float: right">
                        <div id="DESC1" class="categories-caption active">
                            <h2><?= $generalData['title']; ?></h2>
                            <p><?= $generalData['desc']; ?></p>
                        </div>
                </div>
        </div>
    </section>
<?php } } ?>
<!-- *************banner section end************* -->
<style type="text/css">
    .know-more-btn.hidden-md{ 
        display: none !important;
    }

    @media (max-width: 767px){
        .know-more-btn.hidden-md.btn-singin-new {
            display: block !important;
        }
    }
</style>
