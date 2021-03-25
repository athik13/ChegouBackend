
<section class="how-it-works-section">
    <div class="how-it-works-section-inner">
        <div class="how-it-works-left">
            <h3><?php echo $data[0]['header_second_label'];?></h3>
			<?php echo $data[0]['third_mid_desc_two'];?>
        </div>
        <div class="how-it-works-right">
            <img src="<?php echo $tconfig["tsite_upload_page_images"].'home/'.$data[0]['home_banner_right_image']; ?>" alt="">
        </div>
    </div>
</section>
<section class="download-section" style="background-image:url(<?php echo $tconfig["tsite_upload_page_images"].'home/'.$data[0]['third_mid_image_two']; ?>);">  
    <div class="download-section-inner">
        <h3><?php echo $data[0]['third_sec_title'];?></h3>
        <strong><?php echo $data[0]['third_mid_title_one'];?></strong>
        <?php echo $data[0]['third_mid_desc_three'];?>
    </div>
</section>
<section class="map-section" >
    <div class="map-section-inner">
        <div id="map"></div>
        <a href="fare-estimate" target="_blank" class="map-caption">
            <h3><?php echo $data[0]['third_mid_title_two'];?></h3>
            <strong><?php echo $data[0]['third_mid_title_three'];?></strong>
            <span>
                <img src="<?php echo $tconfig["tsite_upload_page_images"].'home/'.$data[0]['third_mid_image_three']; ?>" alt="">
            </span>
        </a>
    </div>
</section>
<section class="safety-section">
    <section class="safety-section-inner">
        <div class="safety-section-left">
            <img src="<?php echo $tconfig["tsite_upload_page_images"].'home/'.$data[0]['taxi_app_bg_img']; ?>" alt="">
        </div>
        <div class="safety-section-right">
            <h3><?php echo $data[0]['third_mid_title_one1'];?></h3>
            <?php echo $data[0]['taxi_app_right_desc'];?>
        </div>
    </section>
</section>

<section class="personal-ride" style="background-image:url(<?php echo $tconfig["tsite_upload_page_images"].'home/'.$data[0]['taxi_app_left_img']; ?>);">
    <section class="personal-ride-inner">
        <div class="personal-ride-caption">
            <h3><?php echo $data[0]['mobile_app_right_title'];?></h3>
            <?php echo $data[0]['mobile_app_right_desc'];?>
        </div>
    </section>
</section>
<section class="call-section" style="background-image:url(<?php echo $tconfig["tsite_upload_page_images"].'home/'.$data[0]['mobile_app_bg_img1']; ?>);">  
    <div class="call-section-inner">
        <h3><?php echo $data[0]['third_mid_title_three1'];?></h3>
        <?php echo $data[0]['third_mid_desc_three1'];?>
    </div>
</section>
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