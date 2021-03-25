<?php
$showSignRegisterLinks = 1;
$how_it_work_section = json_decode($data[0]['lHowitworkSection'],true);
//print_R($how_it_work_section);
//exit;
$secure_section = json_decode($data[0]['lSecuresafeSection'],true);
$download_section = json_decode($data[0]['lDownloadappSection'],true);
$call_section = json_decode($data[0]['lCalltobookSection'],true);
?>
<!-- *************hot it works section start************* -->
<section class="how-it-works-section">
    <div class="how-it-works-section-inner">
        <div class="how-it-works-left">
            <h3><?php echo $how_it_work_section['title'];?></h3>
            <?php echo $how_it_work_section['desc'];?>
				</div>
        <div class="how-it-works-right">
            <img src="<?php  echo $tconfig["tsite_upload_apptype_page_images"] . $template . '/'.$how_it_work_section['img']; ?>" alt="">
        </div>
    </div>
</section>
<!-- *************hot it works section end************* -->

<!-- *************safty-section section start************* -->
<section class="safety-section">
    <div class="safety-section-inner">
        <div class="safety-section-left">
						<img src="<?php  echo $tconfig["tsite_upload_apptype_page_images"] . $template . '/'.$secure_section['img']; ?>" alt="">
        </div>
        <div class="safety-section-right">
            <h3><?php echo $secure_section['title'];?></h3>
            <?php echo $secure_section['desc'];?>
				</div>
    </div>
</section>
<!-- *************safty-section section end************* -->

<!-- *************download section section start************* -->
<section class="download-section parallax-window" data-parallax="scroll" data-image-src="<?= $tconfig["tsite_upload_apptype_page_images"] . $template . '/' . $download_section['img']; ?>">
    <div class="download-section-inner">
        <h3><?= $download_section['title']; ?></h3>
        <strong><?= $download_section['subtitle']; ?></strong>
        <?= $download_section['desc']; ?>
        </p>
        <div class="download-links">
            <a href="<?= $ANDROID_APP_LINK ?>" target="_blank">
                <img alt="" src="assets/img/apptype/<?php echo $template;?>/google-play.png">
            </a
            ><a href="<?= $IPHONE_APP_LINK ?>" target="_blank">
            <img alt="" src="assets/img/apptype/<?php echo $template;?>/istore.png">
        </a>
        </div>
    </div>
</section>

<!-- *************download section section end************* -->

<!-- *************call to section start************* -->
<section class="call-section">
    <div class="call-section-inner">
        <div class="call-section-left">
            <h3><?= $call_section['title']; ?></h3>
			<?= $call_section['desc']; ?>
        </div>
        <div class="call-section-right">
            <img src="<?= $tconfig["tsite_upload_apptype_page_images"] . $template . '/' . $call_section['img']; ?>" alt="">
        </div>
    </div>
</section>
<!-- *************call to section end************* -->


