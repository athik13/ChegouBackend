 <?php if(!empty($data[0]['mobile_app_left_img'])){ ?>
    	<section class="home-banner" style="background-image:url(<?php echo $tconfig["tsite_upload_page_images"].'home/'.$data[0]['home_banner_left_image']; ?>);">
	<?php } else { ?>
		<section class="home-banner">
	<?php } ?>
    <div class="home-banner-inner">
        <div class="banner-data">
            <h1><?php echo $data[0]['header_first_label'];?></h1>
           <?php echo $data[0]['third_sec_desc'];?>
            <div class="logins-holder">
			<?php echo $data[0]['third_mid_desc_two1'];?>
			<?php echo $data[0]['third_mid_desc_one'];?>
			
        </div>
    </div>
</section>