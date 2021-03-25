<!-- <style>
<?php if(isset($data[0]['home_banner_left_image']) && (!empty($data[0]['home_banner_left_image']))) {?>
.home-hero-page-left {
	background: rgba(0, 0, 0, 0) url('<?=$tconfig["tsite_upload_page_images"]."home/".$data[0]['home_banner_left_image'];?>') no-repeat scroll center top / cover
}
<?php } 
if(isset($data[0]['home_banner_right_image']) && (!empty($data[0]['home_banner_right_image']))) { ?>
.home-hero-page-right {
	background-image: url('<?=$tconfig["tsite_upload_page_images"]."home/".$data[0]['home_banner_right_image'];?>');
}
<?php } 
if(isset($data[0]['mobile_app_bg_img']) && (!empty($data[0]['mobile_app_bg_img']))) {?>
.home-mobile-app {
	background-image: url('<?=$tconfig["tsite_upload_page_images"]."home/".$data[0]['mobile_app_bg_img'];?>');
	}
<?php } 
if(isset($data[0]['taxi_app_bg_img']) && (!empty($data[0]['taxi_app_bg_img']))) { ?>
.taxi-app {
	background: url('<?=$tconfig["tsite_upload_page_images"]."home/".$data[0]['taxi_app_bg_img'];?>') repeat-x;
}
<?php } 
if(isset($data[0]['taxi_app_left_img']) && (!empty($data[0]['taxi_app_left_img']))) { ?>
.taxi-app1 {
	background: url('<?=$tconfig["tsite_upload_page_images"]."home/".$data[0]['taxi_app_left_img'];?>') no-repeat scroll center top;
}
<?php } ?>
</style> -->
<style>
<?php if(isset($data[0]['vDeliveryPartBgImg']) && (!empty($data[0]['vDeliveryPartBgImg']))) { ?>
 .delivery-part-inner {background-image: url(<?=$tconfig["tsite_img"]."/home-new/".$data[0]['vDeliveryPartBgImg'];?>)}
<?php } ?>
</style>
<script type="text/javascript" src="assets/js/amazingcarousel.js"></script>
<script type="text/javascript" src="assets/js/initcarousel.js"></script>
<!-- css -->
<link rel="stylesheet" type="text/css" href="assets/css/animate.css">
<? if($SITE_VERSION != 'v5') { ?> 
<link rel="stylesheet" type="text/css" href="assets/css/gallery.css"/>
<? } else { ?>
<link rel="stylesheet" type="text/css" href="assets/css/gallery_v5.css"/>
<? } ?>
<!-- js -->
<script type="text/javascript" src="assets/js/jquery-1.11.0.js"></script>
<script type="text/javascript" src="assets/js//waypoints.min.js"></script>
<link rel="stylesheet" type="text/css" href="assets/css/tooltip-one/doc/css/prettify.css" />
<link rel="stylesheet" type="text/css" href="assets/css/tooltip-one/doc/css/style.css" />
<link rel="stylesheet" type="text/css" href="assets/css/tooltip-one/css/tooltipster.css" />
<script type="text/javascript" src="assets/css/tooltip-one/doc/js/prettify.js"></script>
<script type="text/javascript" src="assets/css/tooltip-one/doc/js/scripts.js"></script>
<script type="text/javascript" src="assets/css/tooltip-one/js/jquery.tooltipster.js"></script>
<script src="https://maps.googleapis.com/maps/api/js?v=3.exp&sensor=false&libraries=places&language=en&key=<?=$GOOGLE_SEVER_API_KEY_WEB?>"></script>
<script type="text/javascript">//<![CDATA[ 
	$(function(){
		function onScrollInit( items, trigger ) {
			items.each( function() {
                var osElement = $(this),
				osAnimationClass = osElement.attr('data-os-animation'),
				osAnimationDelay = osElement.attr('data-os-animation-delay');
				
				osElement.css({
					'-webkit-animation-delay':  osAnimationDelay,
					'-moz-animation-delay':     osAnimationDelay,
					'animation-delay':          osAnimationDelay
				});
				var osTrigger = ( trigger ) ? trigger : osElement;
				
				osTrigger.waypoint(function() {
					osElement.addClass('animated').addClass(osAnimationClass);
					},{
					triggerOnce: true,
					offset: '100%'
				});
			});
		}
		onScrollInit( $('.os-animation') );
		onScrollInit( $('.staggered-animation'), $('.staggered-animation-container') );
	});//]]>  
</script>
<!-- -->
<?php 
	$sql="select count('iDriverId') as Total from home_driver where eStatus='Active'";
	$count_driver = $obj->MySQLSelect($sql);
	
	if($count_driver[0]['Total'] > 4){
		$ssql = " order by rand()";	
	}else{
		$ssql = " order by iDisplayOrder";
	}
	$sql="select * from home_driver where eStatus='Active' $ssql limit 4";
	$db_home_drv=$obj->MySQLSelect($sql);	
	//for default country
	$sql = "SELECT vCountry from country where eStatus = 'Active' and vCountryCode = '$DEFAULT_COUNTRY_CODE_WEB'" ;
	$db_def_con = $obj->MySQLSelect($sql);
	
	$catquery = "SELECT iVehicleCategoryId,vHomepageLogo,vCategory_" . $_SESSION['sess_lang'] . " as vehicalcategory FROM  `vehicle_category` WHERE iParentId = 0 and eStatus = 'Active' and vHomepageLogo != '' LIMIT 18";
	$vcatdata = $obj->MySQLSelect($catquery);
	
?>
<div class="our-services">
    <div class="our-services-inner">
      <h2><?php echo $data[0]['vMidSectionTitle']; ?></h2>
      <ul>
		<?php
		if(!empty($vcatdata)){
			for($i=0;$i<count($vcatdata);$i++){
        if(!empty($vcatdata[$i]['vHomepageLogo'])){
		?>
        <li><b><img alt="<?= $vcatdata[$i]['vehicalcategory'];?>" src="<?= $tconfig["tsite_upload_home_page_service_images"].'/'.$vcatdata[$i]['vHomepageLogo']?>"></b><span><?= $vcatdata[$i]['vehicalcategory'];?></span></li>
		<?php 
        }
			} 
		}
		?>
		<!--<span><a href="vehical-cat-icon" class=""><em><?php //echo $langage_lbl['LBL_MORE_INFO'];?></em></a></span>-->
		
		
      </ul>
      <center><a class="gen-button" align="" href="vehical_category_icon.php"><span><?php echo $langage_lbl['LBL_MORE_INFO'];?></span></a></center>
    </div>
</div>
<? if (DELIVERALL == 'Yes') { ?>
<div class="delivery-part">
  <div class="delivery-part-inner">
      <div class="delivery-part-left">
        <div class="del-banner-caption">
          <h3><?= $data[0]['vDeliveryPartTitle']?></h3>
          <?= $data[0]['vDeliveryPartContent']?>
        </div>
      </div>
      <div class="delivery-part-right">
        <?php if(isset($data[0]['vDeliveryPartImg']) && (!empty($data[0]['vDeliveryPartImg']))) { ?>
        <img src="<?=$tconfig["tsite_img"]."/home-new/".$data[0]['vDeliveryPartImg'];?>" alt="">
        <? } else { ?>
        <img src="assets/img/home-new/del-all-app.png" alt="">
        <? } ?>
      </div>
  </div>
</div>
<? } ?>
<div class="services-part">
  <div class="services-part-inner">
    <ul>
      <li>
        <?php if(isset($data[0]['vMidFirstImg']) && (!empty($data[0]['vMidFirstImg']))) { ?>
          <b><img src="<?=$tconfig["tsite_img"]."/home-new/".$data[0]['vMidFirstImg'];?>" alt="" /></b>
        <?php } else { ?>
           <b><img src="assets/img/home-new/choose-your-service.png" alt="" /></b>
        <?php } ?>
          <strong><?= $data[0]['vMidFirstTitle']?></strong>
          <?= $data[0]['tMidFirstContent']?>
      </li>
      <li>
          <?php if(isset($data[0]['vMidSecondImg']) && (!empty($data[0]['vMidSecondImg']))) { ?>
            <b><img src="<?=$tconfig["tsite_img"]."/home-new/".$data[0]['vMidSecondImg'];?>" alt="" /></b>
          <?php } else { ?> 
            <b><img src="assets/img/home-new/book-a-single-tap.png" alt="" /></b> 
          <?php } ?>
          <strong><?= $data[0]['vMidSecondTitle']?></strong>
          <?= $data[0]['tMidSecondContent']?>
      </li>
      <li>
        <?php if(isset($data[0]['vMidThirdImg']) && (!empty($data[0]['vMidThirdImg']))) { ?>
          <b><img src="<?=$tconfig["tsite_img"]."/home-new/".$data[0]['vMidThirdImg'];?>" alt="" /></b>
        <?php } else { ?>
          <b><img src="assets/img/home-new/live-track-pay.png" alt="" /></b>
        <?php } ?>
        <strong><?= $data[0]['vMidThirdTitle']?></strong>
        <?= $data[0]['tMidThirdContent']?>
      </li>
    </ul>
    <div style="clear:both;"></div>
    <img src="assets/img/home-new/up.jpg" alt="" class="up" />
  </div>
</div>
<!-- -->
<div class="download-app-today">
  <div class="download-app-today-inner"> 
    <div class="download-app-today-text">
      <h3><?= $data[0]['vThirdSectionRightTitle']?></h3>
      <?= $data[0]['tLastSectionFirstContent']?>
      <span class="app-links">
          <a href="<?=$IPHONE_APP_LINK?>" target="_blank"><?php if(isset($data[0]['vThirdSectionAPPImgAPPStore']) && (!empty($data[0]['vThirdSectionAPPImgAPPStore']))) { ?><img src="<?=$tconfig["tsite_img"]."/home-new/".$data[0]['vThirdSectionAPPImgAPPStore'];?>" alt="" /><?php } else { ?><img src="assets/img/home-new/app-store.jpg" alt="" /> <?php } ?></a>

          <a href="<?=$ANDROID_APP_LINK?>" target="_blank"><?php if(isset($data[0]['vThirdSectionAPPImgPlayStore']) && (!empty($data[0]['vThirdSectionAPPImgPlayStore']))) { ?><img src="<?=$tconfig["tsite_img"]."/home-new/".$data[0]['vThirdSectionAPPImgPlayStore'];?>" alt="" /><?php } else { ?><img src="assets/img/home-new/play-store.jpg" alt="" /> <?php } ?></a>
      </span>
    </div>
    <div class="mobile-app-screen"> 
        <ul>
            <li>
              <?php if(isset($data[0]['vThirdSectionImg1']) && (!empty($data[0]['vThirdSectionImg1']))) { ?><img src="<?=$tconfig["tsite_img"]."/home-new/".$data[0]['vThirdSectionImg1'];?>" alt="" /><?php } else { ?><img src="assets/img/home-new/app-screen1.jpg" alt="" /><?php } ?>
            </li>
            <li>
                <?php if(isset($data[0]['vThirdSectionImg2']) && (!empty($data[0]['vThirdSectionImg2']))) { ?><img src="<?=$tconfig["tsite_img"]."/home-new/".$data[0]['vThirdSectionImg2'];?>" alt="" /><?php } else { ?><img src="assets/img/home-new/app-screen2.jpg" alt="" /><?php } ?>
            </li>
            <li>
                <?php if(isset($data[0]['vThirdSectionImg3']) && (!empty($data[0]['vThirdSectionImg3']))) { ?><img src="<?=$tconfig["tsite_img"]."/home-new/".$data[0]['vThirdSectionImg3'];?>" alt="" /><?php } else { ?><img src="assets/img/home-new/app-screen3.jpg" alt="" /><?php } ?>
            </li>
        </ul>
    </div>
    <div style="clear:both;"></div>
  </div>
</div>
<!-- -->
<div class="why-choose-us">
  <div class="why-choose-us-inner">
    <h3><?= $data[0]['vLastSectionTitle']?></h3>
    <div class="flex_row">
        <div class="why-choose-us-left">
          <ul>
            <li>
              <b><img src="assets/img/home-new/why-choose-us-icon.jpg" alt="" /></b> 
              <span>
                <strong><?= $data[0]['vLastSectionFirstTitle']?></strong>
                <?= $data[0]['tLastSectionFirstContent']?>
              </span>
            </li>
            <li>
              <b><img src="assets/img/home-new/why-choose-us-icon.jpg" alt="" /></b> 
              <span>
                <strong><?= $data[0]['vLastSectionSecondTitle']?></strong>
              <?= $data[0]['tLastSectionSecondContent']?>
              </span> 
            </li>
            <li> 
              <b><img src="assets/img/home-new/why-choose-us-icon.jpg" alt="" /></b> 
              <span>
                <strong><?= $data[0]['vLastSectionThirdTitle']?></strong>
                <?= $data[0]['tLastSectionThirdContent']?>
            </span> 
            </li>
          </ul>
        </div>
        <div class="why-choose-us-mid"><?php if(isset($data[0]['vLastSectionImg']) && (!empty($data[0]['vLastSectionImg']))) { ?><img src="<?=$tconfig["tsite_img"]."/home-new/".$data[0]['vLastSectionImg'];?>" alt="" /><?php } else { ?><img src="assets/img/home-new/why-choose-us.jpg" alt="" /><?php } ?></div>
        <div class="why-choose-us-right">
          <ul>
            <li>
              <b><img src="assets/img/home-new/why-choose-us-icon.jpg" alt="" /></b> 
              <span><strong><?= $data[0]['vLastSectionFourthTitle']?></strong> <?= $data[0]['tLastSectionFourthContent']?></span> 
            </li>
            <li> 
              <b><img src="assets/img/home-new/why-choose-us-icon.jpg" alt="" /></b> 
              <span><strong> <?= $data[0]['vLastSectionFifthTitle']?></strong> <?= $data[0]['tLastSectionFifthContent']?></span> 
            </li>
            <li> 
              <b><img src="assets/img/home-new/why-choose-us-icon.jpg" alt="" /></b>
              <span><strong> <?= $data[0]['vLastSectionSixthTitle']?></strong> <?= $data[0]['tLastSectionSixthContent']?></span> 
            </li>
          </ul>
        </div>
    </div>
  </div>
</div>


<!-- <?php if(!empty($db_home_drv)) { ?>
<div class="gallery-part">
	<div class="gallery-page">
		<h2><?php echo $data[0]['driver_sec_first_label']?></h2>		
		<em><?php echo $data[0]['driver_sec_second_label']?></em>
        <div class="gallery-page-inner">
		<?php
			
			$dlang = $_SESSION['sess_lang'];
			
			for($i=0;$i<count($db_home_drv);$i++)
			{
			?>
				<div id="box-2" class="box"> <b>
					<img width="290" height="270" id="image-1" src="<?=$tconfig["tsite_upload_images"].$db_home_drv[$i]['vImage']?>"/></b>
					<span class="caption full-caption">
					<h3>
						<p><?=$db_home_drv[$i]['tText_'.$dlang];?></p>
						<strong><?=$db_home_drv[$i]['vName_'.$dlang]?>
							<?php if($db_home_drv[$i]['vDesignation_'.$dlang] != ""){
								echo ",".$db_home_drv[$i]['vDesignation_'.$dlang];}?>
						</strong>
					</h3>
				</span>
				</div>
		<?php } ?>
        </div>
	</div>
</div>
<?php } else{ ?>
	<div class="gallery-part"></div>
<?php } ?> -->