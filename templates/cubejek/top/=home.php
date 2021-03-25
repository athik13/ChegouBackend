
<?php 
	$default_lang = isset($_SESSION['sess_lang']) ? $_SESSION['sess_lang'] : "EN";
	$sql="select count('iDriverId') as Total from home_driver where eStatus='Active'";
	$count_driver = $obj->MySQLSelect($sql);
	
	if($count_driver[0]['Total'] > 4){
		$ssql = " order by iDisplayOrder";	
	}else{
		$ssql = " order by iDisplayOrder";
	}
	$sql="select * from home_driver where eStatus='Active' $ssql limit 18";
	$db_home_drv=$obj->MySQLSelect($sql);	
	//for default country
	$sql = "SELECT vCountry from country where eStatus = 'Active' and vCountryCode = '$DEFAULT_COUNTRY_CODE_WEB'" ;
	$db_def_con = $obj->MySQLSelect($sql);
	
	$catquery = "SELECT iVehicleCategoryId,vHomepageLogo,vCategory_".$default_lang." as vehicalcategory FROM  `vehicle_category` WHERE iParentId = 0 and eStatus = 'Active' and vHomepageLogo != '' $ssql LIMIT 18";
	$vcatdata = $obj->MySQLSelect($catquery);
	
?>


<section class="how-it-works-section">
    <div class="how-it-works-section-inner">
        <div class="how-it-works-left">
            <h3><?php echo $data[0]['header_second_label'];?></h3>
			<?php echo $data[0]['third_mid_desc_two'];?>
        </div>
        <div class="how-it-works-right">
            <img src="<?php  echo $tconfig["tsite_upload_page_images"].'home/'.$data[0]['home_banner_right_image']; ?>" alt="">
            <!-- <img src="<?php // echo $tconfig["tsite_upload_page_images"].'home/'.$data[0]['home_banner_right_image']; ?>" alt=""> -->
        </div>
    </div>
</section>
<section class="categories-section">
    <div class="categories-section-inner">
        <h3 class="heading-style"><?php echo $data[0]['third_mid_title_two1']; ?></h3>
        <!--<strong>Lorem Ipsum content goes here and your lorem text goes here and your lorem ipusm limpusm Lorem Ipsum content goes here  text goes here and your lorem ipusm lorem ipsum content goes.</strong>-->
        <?php echo $data[0]['third_mid_desc_one1']; ?>
        <ul class="flex-row">
        <?php
        if(!empty($vcatdata)){
            for($i=0;$i<count($vcatdata);$i++){
        if(!empty($vcatdata[$i]['vHomepageLogo'])){
        ?>
        <li><i class="icon-tag"><img alt="<?= $vcatdata[$i]['vehicalcategory'];?>" src="<?= $tconfig["tsite_upload_home_page_service_images"].'/'.$vcatdata[$i]['vHomepageLogo']?>"></i><strong><?= $vcatdata[$i]['vehicalcategory'];?></strong></li>
        <?php 
        }
            } 
        }
        ?>
        </ul>
        <center><a class="gen-button" align="" href="services.php"><span><?php echo $langage_lbl['LBL_MORE_INFO'];?></span></a></center>
    </div>
</section>

<!-- <section class="download-section" style="background-image:url(<?php // echo $tconfig["tsite_upload_page_images"].'home/'.$data[0]['third_mid_image_two']; ?>);">   -->

<section class="download-section" style="background-image:url(<?php  echo $tconfig["tsite_upload_page_images"].'home/'.$data[0]['third_mid_image_two']; ?>);">  
    <div class="download-section-inner">
        <h3><?php echo $data[0]['third_sec_title'];?></h3>
        <strong><?php echo $data[0]['third_mid_title_one'];?></strong>
        <?php echo $data[0]['third_mid_desc_three'];?>
    </div>
</section>
<section class="safety-section">
    <section class="safety-section-inner">
        <div class="safety-section-left">
            <!-- <img src="assets/img/home/alchohol.jpg" alt=""> -->
             <img src="<?php echo $tconfig["tsite_upload_page_images"].'home/'.$data[0]['third_mid_image_three']; ?>" alt="">
        </div>
        <div class="safety-section-right deliveryall">
            <h3><?php echo $data[0]['third_mid_title_two'];?></h3>
            <?php echo $data[0]['third_mid_desc_one'];?>
            <!-- <p>Get groceries and alcohol delivered in under an hour so you can spend your time living your best life. Whether you need a gallon of milk or a handle of vodka, we get it.</p> -->
            <!-- <h3><?php// echo $data[0]['third_mid_title_one1'];?></h3>
            <?php //echo $data[0]['taxi_app_right_desc'];?> -->
        </div>
    </section>
</section>
<section class="safety-section reverse">
    <section class="safety-section-inner">
        <div class="safety-section-left">
            <!-- <img src="assets/img/home/food.jpg" alt=""> -->
             <img src="<?php echo $tconfig["tsite_upload_page_images"].'home/'.$data[0]['taxi_app_bg_img']; ?>" alt=""> 
        </div>
        <div class="safety-section-right bookingservice">
            <h3><?php echo $data[0]['third_mid_title_one1'];?></h3>
            <?php echo $data[0]['taxi_app_right_desc'];?>
            <!-- <p>No fees, just fast service. Order takeout with Deliveryall Pickup and we’ll let you know when it’s ready. No wallet. No wait.</p>
            <a href="#">TRY NOW</a> -->
            <!-- <h3><?php// echo $data[0]['third_mid_title_one1'];?></h3>
            <?php //echo $data[0]['taxi_app_right_desc'];?> -->
        </div>
    </section>
</section>
<!-- <section class="personal-ride" style="background-image:url(<?php // echo $tconfig["tsite_upload_page_images"].'home/'.$data[0]['taxi_app_left_img']; ?>);"> -->
<section class="personal-ride">
    <section class="personal-ride-inner">
        <div class="personal-ride-caption">
            <div class="personal-ride-left">
                <img src="<?php echo $tconfig["tsite_upload_page_images"].'home/'.$data[0]['taxi_app_left_img']; ?>" class="otherservice" alt="">
            </div>
            <div class="personal-ride-right">
                <h3><?php  echo $data[0]['mobile_app_right_title'];?></h3>
                <!-- <p>Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever</p>
                <a href="#">try unlimited free</a> -->
                <?php  echo $data[0]['mobile_app_right_desc'];?>
            </div>
            <!-- <h3><?php // echo $data[0]['mobile_app_right_title'];?></h3>
            <?php // echo $data[0]['mobile_app_right_desc'];?> -->
        </div>
    </section>
</section>

<!-- <section class="call-section">  
    <div class="call-section-inner">
        <div class="call-section-left">
            <h3><?php  echo $data[0]['third_mid_title_three1'];?></h3>
            <?php  echo $data[0]['third_mid_desc_three1'];?>
        </div>
        <div class="call-section-right">
            <img src="<?php  echo $tconfig["tsite_upload_page_images"].'home/'.$data[0]['mobile_app_bg_img1']; ?>" alt="">
        </div>
    </div>
</section> -->




<!-- <section class="call-section" style="background-image:url(<?php // echo $tconfig["tsite_upload_page_images"].'home/'.$data[0]['mobile_app_bg_img1']; ?>);">  
    <div class="call-section-inner">
        <h3><?php // echo $data[0]['third_mid_title_three1'];?></h3>
        <?php // echo $data[0]['third_mid_desc_three1'];?>
    </div>
</section> -->
<script>
      function initMap() {
        var map = new google.maps.Map(document.getElementById('map'), {zoom: 5});
        var geocoder = new google.maps.Geocoder;
        geocoder.geocode({componentRestrictions: {
    country: '<?php echo $DEFAULT_COUNTRY_CODE_WEB; ?>',
  }}, function(results, status) {
          if (status === 'OK') {
            map.setCenter(results[0].geometry.location);
            new google.maps.Marker({
              map: map,
              position: results[0].geometry.location
            });
          } else {
            //window.alert('Geocode was not successful for the following reason: ' +status);

          }
        });
      }
    </script>
	<script async defer
    src="https://maps.googleapis.com/maps/api/js?libraries=places&language=en&key=<?=$GOOGLE_SEVER_API_KEY_WEB?>&callback=initMap">
    </script>