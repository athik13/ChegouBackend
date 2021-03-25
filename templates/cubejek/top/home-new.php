<?php 
$sql="select vTitle, vCode, vCurrencyCode, eDefault from language_master where eStatus='Active' ORDER BY iDispOrder ASC";
$db_lng_mst=$obj->MySQLSelect($sql);
$count_lang = count($db_lng_mst);
 
if($host_system == 'cubetaxiplus') {
  $logo = "logo.png";
} else if($host_system == 'ufxforall') {
  $logo = "ufxforall-logo.png";
} else if($host_system == 'uberridedelivery4') {
  $logo = "ride-delivery-logo.png";
} else if($host_system == 'uberdelivery4') {
  $logo = "delivery-logo-only.png";
} else {
  $logo = "logo.png";
}

?>
<style>
<?php if(isset($data[0]['BannerBgImage']) && (!empty($data[0]['BannerBgImage']))) {?>
  .top-part-inner{ background: url('<?=$tconfig["tsite_img"]."/home-new/".$data[0]['BannerBgImage'];?>') no-repeat scroll top center; }
<?php } else { ?>
  .top-part-inner{ background:url('<?=$tconfig["tsite_img"].'/home-new/banner.jpg'?>') no-repeat scroll top center;}
<?php  } ?>
</style>
<!-- page -->
<div id="home-page">
  <!-- -->
  <div class="lang-part-top">
  <div class="lang-part-top-inner">
    <div class="phone-part"> 
      <span>
        <b><img src="assets/img/home-new/phone.png" alt="" /> +123-456-7890</b>
        <b><img src="assets/img/home-new/mgs.png" alt="" /> <a href="#">info@cubejek.com</a></b>
      </span>
    </div>
    <div class="lang-part">
      <div class="special-offer-left">
<!--         <form action="action.php" method="post" class="se-in">
          <select name="timepass" class="custom-select">
            <option>USD</option>
            <option>USD</option>
            <option>USD</option>
            <option>USD</option>
          </select>
        </form> -->
        <form action="" method="post" class="se-in">
          <select name="timepass" class="custom-select" id="Languageids">
              <?php foreach ($db_lng_mst as $key => $value) { ?>
              <option id="<?php echo $value['vCode']; ?>" value="<?php echo $value['vCode']; ?>" <?if($_SESSION['sess_lang']==$value['vCode']) { ?> selected="selected" <?} ?> ><?php echo ucfirst(strtolower($value['vTitle'])); ?></option>
              <?php } ?>
          </select>
        </form>
      </div>
    </div>
    <div style="clear:both;"></div>
  </div>
  </div>
  <!-- -->
    <!-- top part -->
  <div class="top-part">
    <div class="top-part-inner">
      <div class="menu-part">
        <div class="menu-part-inner">
          <div class="logo"><img src="assets/img/home-new/<?php echo $logo;?>" alt=""></div>
            <?php if(isset($_REQUEST['edit_lbl'])){ ?>
              <div class="menu">
                <ul>
                  <li><a href="index-new.php" class="<?=(isset($script) && $script == 'Home')?'active':'';?>">Home</a>
                  <li><a href="#">services</a></li>
                  <li><a href="#">sign up</a></li>
                  <li><a href="help-center" class="<?=(isset($script) && $script == 'Help Center')?'active':'';?>"><?=$langage_lbl['LBL_HEADER_HELP_TXT'];?></a></li>
                  <li><a href="sign-in"  class="<?php echo strstr($_SERVER['SCRIPT_NAME'],'/sign-in') || strstr($_SERVER['SCRIPT_NAME'],'/login-new')?'active':'' ?>"><?=$langage_lbl['LBL_HEADER_TOPBAR_SIGN_IN_TXT'];?></a></li>
                </ul>
            </div>
           <?php } else { ?>
            <div class="menu">
              <ul>
                <li><a href="index-new.php" class="<?=(isset($script) && $script == 'Home')?'active':'';?>">Home</a>
                <li><a href="#">services</a></li>
                <li><a href="#">sign up</a></li>
                <li><a href="help-center" class="<?=(isset($script) && $script == 'Help Center')?'active':'';?>"><?=$langage_lbl['LBL_HEADER_HELP_TXT'];?></a></li>
                <li><a href="sign-in"  class="<?php echo strstr($_SERVER['SCRIPT_NAME'],'/sign-in') || strstr($_SERVER['SCRIPT_NAME'],'/login-new')?'active':'' ?>"><?=$langage_lbl['LBL_HEADER_TOPBAR_SIGN_IN_TXT'];?></a></li>
              </ul>
            </div>
           <?php } ?>
          <div style="clear:both;"></div>
        </div>
      </div>
      <!-- -->
      <div class="banner-part">
        <div class="banner-part-inner">
          <div class="banner-part-left">
            <?php if(isset($data[0]['BannerLeftImg']) && (!empty($data[0]['BannerLeftImg']))) { ?>
              <img src="<?=$tconfig["tsite_img"]."/home-new/".$data[0]['BannerLeftImg'];?>" alt="" />
            <?php } else { ?>
              <img src="assets/img/home-new/mobile-app.png" alt="" />
            <?php }?>   
          </div>
          <div class="banner-part-right">
            <h1><?php echo $data[0]['BannerRightTitle'] ?></h1>
            <p><?php echo $data[0]['BannerRightContent'] ?></p>
            <span><a href="#">Know more</a></span>
          </div>
          <div style="clear:both;"></div>
        </div>
      </div>
      <!-- -->
      <img src="assets/img/home-new/banner-bottom.png" alt="" class="banner-bottom"/>
      <div style="clear:both;"></div>
    </div>
  </div>
  <!-- body part -->
  <div class="our-services">
    <div class="our-services-inner">
      <h2><?php echo $data[0]['MidSectionTitle'] ?></h2>
      <ul>
        <li><b><img alt="Taxi Ride App" src="assets/img/home-new/services/33.jpg"></b><span>Taxi Ride</span></li>
        <li><b><img alt="Moto Ride App" src="assets/img/home-new/services/36.jpg"></b><span>Moto Ride</span></li>
        <li><b><img alt="Car Rental App" src="assets/img/home-new/services/37.jpg"></b><span>Car Rental</span></li>
        <li><b><img alt="Moto Rental App" src="assets/img/home-new/services/38.jpg"></b><span>Moto Rental</span></li>
        <li><b><img alt="Cargo Delivery App" src="assets/img/home-new/services/39.jpg"></b><span>Box (Cargo Delivery)</span></li>
        <li><b><img alt="Small packages delivery App" src="assets/img/home-new/services/40.jpg"></b><span>Send (Small Packages Delivery)</span></li>
        <li><b><img alt="Babysitting App" src="assets/img/home-new/services/1.jpg"></b><span>Baby Sitting</span></li>
        <li><b><img alt="Beauty Service App" src="assets/img/home-new/services/2.jpg"></b><span>Beauty Service </span></li>
        <li><b><img alt="Car Wash App" src="assets/img/home-new/services/3.jpg"></b><span>Car Wash</span></li>
        <li><b><img alt="Carpenter App" src="assets/img/home-new/services/4.jpg"></b><span>Carpenter</span></li>
        <li><b><img alt="Cuddling App" src="assets/img/home-new/services/5.jpg"></b><span>Cuddling</span></li>
        <li><b><img alt="DJ App" src="assets/img/home-new/services/6.jpg"></b><span>DJ</span></li>
        <li><b><img alt="Doctor App" src="assets/img/home-new/services/7.jpg"></b><span>Doctor</span></li>
        <li><b><img alt="Dog Grooming App" src="assets/img/home-new/services/8.jpg"></b><span>Dog Grooming</span></li>
        <li><b><img alt="Dog Walking App" src="assets/img/home-new/services/9.jpg"></b><span>Dog Walking</span></li>
        <li><b><img alt="Electrician App" src="assets/img/home-new/services/10.jpg"></b><span>Electrician</span></li>
        <li><b><img alt="Escort App" src="assets/img/home-new/services/11.jpg"></b><span>Escort</span></li>
        <li><b><img alt="Fitness Coach App" src="assets/img/home-new/services/12.jpg"></b><span>Fitness Coach</span></li>
        <li><b><img alt="Handyman App" src="assets/img/home-new/services/13.jpg"></b><span>Handy Man</span></li>
        <li><b><img alt="Home Cleaning App" src="assets/img/home-new/services/14.jpg"></b><span>Home Cleaning</span></li>
        <li><b><img alt="Home Painting Service App" src="assets/img/home-new/services/15.jpg"></b><span>Home Painting Service</span></li>
        <li><b><img alt="Insurance Agent App" src="assets/img/home-new/services/16.jpg"></b><span>Insurance Agent</span></li>
        <li><b><img alt="Lawn Moving App" src="assets/img/home-new/services/17.jpg"></b><span>Lawn Moving</span></li>
        <li><b><img alt="lawyer app" src="assets/img/home-new/services/18.jpg"></b><span>Lawyer</span></li>
        <li><b><img alt="Lock smith app" src="assets/img/home-new/services/19.jpg"></b><span>Lock Smith</span></li>
        <li><b><img alt="Maids App" src="assets/img/home-new/services/20.jpg"></b><span>Maids</span></li>
        <li><b><img alt="Massage App" src="assets/img/home-new/services/21.jpg"></b><span>Massage</span></li>
        <li><b><img alt="Packers Movers App" src="assets/img/home-new/services/22.jpg"></b><span>Packer Movers</span></li>
        <li><b><img alt="Pest Control App" src="assets/img/home-new/services/23.jpg"></b><span>Pest Control</span></li>
        <li><b><img alt="Physiotheraphy App" src="assets/img/home-new/services/24.jpg"></b><span>Physiotheraphy</span></li>
        <li><b><img alt="Plumber App" src="assets/img/home-new/services/25.jpg"></b><span>Plumber</span></li>
        <li><b><img alt="Real Estate Agent App" src="assets/img/home-new/services/26.jpg"></b><span>Real Estate Agent</span></li>
        <li><b><img alt="Security Guard App" src="assets/img/home-new/services/27.jpg"></b><span>Security Guard</span></li>
        <li><b><img alt="Snow Plows App" src="assets/img/home-new/services/28.jpg"></b><span>Snow Plows</span></li>
        <li><b><img alt="Tour Guide App" src="assets/img/home-new/services/29.jpg"></b><span>Tour Guide</span></li>
        <li><b><img alt="Tow Truck App" src="assets/img/home-new/services/30.jpg"></b><span>Tow Truck</span></li>
        <li><b><img alt="Travel Agent App" src="assets/img/home-new/services/31.jpg"></b><span>Travel Agent</span></li>
        <li><b><img alt="Tutor App" src="assets/img/home-new/services/32.jpg"></b><span>Tutor</span></li>
        <li><b><img alt="Vet App" src="assets/img/home-new/services/34.jpg"></b><span>Vet</span></li>
        <li><b><img alt="Worker App" src="assets/img/home-new/services/35.jpg"></b><span>Worker</span></li>
      </ul>
    </div>
  </div>
</div>
<!-- -->
<div class="services-part">
  <div class="services-part-inner">
    <ul>
      <li>
        <?php if(isset($data[0]['MidFirstImg']) && (!empty($data[0]['MidFirstImg']))) { ?>
          <b><img src="<?=$tconfig["tsite_img"]."/home-new/".$data[0]['MidFirstImg'];?>" alt="" /></b>
        <?php } else { ?>
           <b><img src="assets/img/home-new/choose-your-service.png" alt="" /></b>
        <?php } ?>
          <strong><?= $data[0]['MidFirstTitle']?></strong>
          <?= $data[0]['MidFirstContent']?>
      </li>
      <li>
          <?php if(isset($data[0]['MidSecondImg']) && (!empty($data[0]['MidSecondImg']))) { ?>
            <b><img src="<?=$tconfig["tsite_img"]."/home-new/".$data[0]['MidSecondImg'];?>" alt="" /></b>
          <?php } else { ?> 
            <b><img src="assets/img/home-new/book-a-single-tap.png" alt="" /></b> 
          <?php } ?>
          <strong><?= $data[0]['MidSecondTitle']?></strong>
          <?= $data[0]['MidSecondContent']?>
      </li>
      <li>
        <?php if(isset($data[0]['MidThirdImg']) && (!empty($data[0]['MidThirdImg']))) { ?>
          <b><img src="<?=$tconfig["tsite_img"]."/home-new/".$data[0]['MidThirdImg'];?>" alt="" /></b>
        <?php } else { ?>
          <b><img src="assets/img/home-new/live-track-pay.png" alt="" /></b>
        <?php } ?>
        <strong><?= $data[0]['MidThirdTitle']?></strong>
        <?= $data[0]['MidThirdContent']?>
      </li>
    </ul>
    <div style="clear:both;"></div>
  </div>
</div>
<!-- -->
<div class="download-app-today">
  <div class="download-app-today-inner"> <img src="assets/img/home-new/up.jpg" alt="" class="up" />
    <div class="download-app-today-text">
      <h3><?= $data[0]['ThirdSectionRightTitle']?></h3>
      <?= $data[0]['ThirdSectionRightContent']?>
      <span>
          <a href="#"><?php if(isset($data[0]['ThirdSectionAPPImgAPPStore']) && (!empty($data[0]['ThirdSectionAPPImgAPPStore']))) { ?><img src="<?=$tconfig["tsite_img"]."/home-new/".$data[0]['ThirdSectionAPPImgAPPStore'];?>" alt="" /><?php } else { ?><img src="assets/img/home-new/app-store.jpg" alt="" /> <?php } ?></a>

          <a href="#"><?php if(isset($data[0]['ThirdSectionAPPImgPlayStore']) && (!empty($data[0]['ThirdSectionAPPImgPlayStore']))) { ?><img src="<?=$tconfig["tsite_img"]."/home-new/".$data[0]['ThirdSectionAPPImgPlayStore'];?>" alt="" /><?php } else { ?><img src="assets/img/home-new/play-store.jpg" alt="" /> <?php } ?></a>
      </span>
    </div>
    <div class="mobile-app-screen"> 
      <span><?php if(isset($data[0]['ThirdSectionImg1']) && (!empty($data[0]['ThirdSectionImg1']))) { ?><img src="<?=$tconfig["tsite_img"]."/home-new/".$data[0]['ThirdSectionImg1'];?>" alt="" /><?php } else { ?><img src="assets/img/home-new/app-screen1.jpg" alt="" /><?php } ?></span>

      <b><?php if(isset($data[0]['ThirdSectionImg2']) && (!empty($data[0]['ThirdSectionImg2']))) { ?><img src="<?=$tconfig["tsite_img"]."/home-new/".$data[0]['ThirdSectionImg2'];?>" alt="" /><?php } else { ?><img src="assets/img/home-new/app-screen2.jpg" alt="" /><?php } ?></b>

      <em><?php if(isset($data[0]['ThirdSectionImg3']) && (!empty($data[0]['ThirdSectionImg3']))) { ?><img src="<?=$tconfig["tsite_img"]."/home-new/".$data[0]['ThirdSectionImg3'];?>" alt="" /><?php } else { ?><img src="assets/img/home-new/app-screen3.jpg" alt="" /><?php } ?></em>
    </div>
    <div style="clear:both;"></div>
  </div>
</div>
<!-- -->
<div class="why-choose-us">
  <div class="why-choose-us-inner">
    <h3><?= $data[0]['LastSectionTitle']?></h3>
    <div class="why-choose-us-left">
      <ul>
        <li>
          <b><img src="assets/img/home-new/why-choose-us-icon.jpg" alt="" /></b> 
          <span>
            <strong><?= $data[0]['LastSectionFirstTitle']?></strong>
            <?= $data[0]['LastSectionFirstContent']?>
          </span>
        </li>
        <li>
          <b><img src="assets/img/home-new/why-choose-us-icon.jpg" alt="" /></b> 
          <span>
            <strong><?= $data[0]['LastSectionSecondTitle']?></strong>
           <?= $data[0]['LastSectionSecondContent']?>
          </span> 
        </li>
        <li> 
          <b><img src="assets/img/home-new/why-choose-us-icon.jpg" alt="" /></b> 
          <span>
            <strong><?= $data[0]['LastSectionThirdTitle']?></strong>
            <?= $data[0]['LastSectionThirdContent']?>
        </span> 
        </li>
      </ul>
    </div>
    <div class="why-choose-us-mid"><?php if(isset($data[0]['LastSectionImg']) && (!empty($data[0]['LastSectionImg']))) { ?><img src="<?=$tconfig["tsite_img"]."/home-new/".$data[0]['LastSectionImg'];?>" alt="" /><?php } else { ?><img src="assets/img/home-new/why-choose-us.jpg" alt="" /><?php } ?></div>
    <div class="why-choose-us-right">
      <ul>
        <li>
          <b><img src="assets/img/home-new/why-choose-us-icon.jpg" alt="" /></b> 
          <span><strong><?= $data[0]['LastSectionFourthTitle']?></strong> <?= $data[0]['LastSectionFourthContent']?></span> 
        </li>
        <li> 
          <b><img src="assets/img/home-new/why-choose-us-icon.jpg" alt="" /></b> 
          <span><strong> <?= $data[0]['LastSectionFifthTitle']?></strong> <?= $data[0]['LastSectionFifthContent']?></span> 
        </li>
        <li> 
          <b><img src="assets/img/home-new/why-choose-us-icon.jpg" alt="" /></b>
           <span><strong> <?= $data[0]['LastSectionSixthTitle']?></strong> <?= $data[0]['LastSectionSixthContent']?></span> 
        </li>
      </ul>
    </div>
 <div style="clear:both;"></div>
  </div>
</div>
<!-- -->
<div class="footer">
<div class="footer-inner">
<div class="footer-part1">
<h4>Contact Us</h4>
<p>123, Grand Villey, Jhon Brown Street,<br />
Johonson Road, Texas - 123456<br />
USA</p>
<span>
<p><b>P :</b>+1-234-567-8900</p>
<p><b>E :</b><a href="#">info@companyname.com</a></p>
</span>
</div>
<div class="footer-part2">
<h4>Company</h4>
<ul>
<li><a href="#">Contact Us</a></li>
<li><a href="#">About Us</a></li>
<li><a href="#">Help</a></li>
<li><a href="#">Become Driver</a></li>
</ul>
</div>
<div class="footer-part3">
<h4>My Account</h4>
<ul>
<li><a href="#">Sign In</a></li>
<li><a href="#">Sign Up</a></li>
<li><a href="#">Order History</a></li>
<li><a href="#">My Account</a></li>
</ul>
</div>
<div class="footer-part4">
<h4>Subscribe Now</h4>
<span><input name="" type="text" placeholder="Enter your E-mail Address" /><a href="#">Subscribe</a></span>
<b>
<a href="#" target="_blank" rel="nofollow"><img onmouseout="this.src='assets/img/home-new/fb.jpg'" onmouseover="this.src='assets/img/home-new/fb-hover.jpg'" onclick="return submitsearch(document.frmsearch);" src="assets/img/home-new/fb.jpg" alt=""></a>
<a href="#" target="_blank" rel="nofollow"><img onmouseout="this.src='assets/img/home-new/twitter.jpg'" onmouseover="this.src='assets/img/home-new/twitter-hover.jpg'" onclick="return submitsearch(document.frmsearch);" src="assets/img/home-new/twitter.jpg" alt=""></a>
<a href="#" target="_blank" rel="nofollow"><img onmouseout="this.src='assets/img/home-new/linkedin.jpg'" onmouseover="this.src='assets/img/home-new/linkedin-hover.jpg'" onclick="return submitsearch(document.frmsearch);" src="assets/img/home-new/linkedin.jpg" alt=""></a>
<a href="#" target="_blank" rel="nofollow"><img onmouseout="this.src='assets/img/home-new/pinterest.jpg'" onmouseover="this.src='assets/img/home-new/pinterest-hover.jpg'" onclick="return submitsearch(document.frmsearch);" src="assets/img/home-new/pinterest.jpg" alt=""></a>
</b>
</div>
<!-- -->
<div class="footer-bottom-part">
<p>&copy; Copyright 2018 - <a href="#">GoJek</a></p>
</div>
<!-- -->
<div style="clear:both;"></div>
</div>
</div>
<!-- -->
<!-- css -->
<link rel="stylesheet" type="text/css" href="assets/css/home-new/home-new.css">
<script src="assets/js/jquery-1.7.1.min.js" type="text/javascript"></script>
<script type="text/javascript">
    $(document).ready(function(){
        $(".custom-select").each(function(){
            $(this).wrap("<em class='select-wrapper'></em>");
            $(this).after("<em class='holder'></em>");
        });
        $(".custom-select").change(function(){
            var selectedOption = $(this).find(":selected").text();
            $(this).next(".holder").text(selectedOption);
        }).trigger('change');
    })
    jQuery(function () {
        jQuery("#Languageids").change(function () {
          var lang = $(this).val();
          location.href = 'common.php?lang='+lang;
        });
    });
</script>