 <?php if(!empty($data[0]['mobile_app_left_img'])){ ?>
        <section class="banner-section" style="background-image:url(<?php  echo $tconfig["tsite_upload_page_images"].'home/'.$data[0]['home_banner_left_image']; ?>);">
    <?php } else { ?>
<section class="banner-section">
    <?php } ?>
    <div class="banner-section-inner">
        <div class="banner-top">
            <div class="banner-caption">
                <h1><?php echo $data[0]['header_first_label'];?></h1>
                <?php  echo $data[0]['third_sec_desc'];?>
                 <?php $data1 = $data[0]['third_mid_desc_two1']; 
               $data1=str_ireplace('<p>','',$data1);
$data1=str_ireplace('</p>','',$data1);    
echo $data1;
                ?>
                <?php if(empty($_SESSION['sess_user'])){?>
                <a href="sign-in" target="_blank"  class="know-more-btn hidden-md  hidden-lg  hidden-sm btn-singin-new <?php echo strstr($_SERVER['SCRIPT_NAME'], '/sign-in') || strstr($_SERVER['SCRIPT_NAME'], '/login-new') ? 'active' : '' ?>"><?= $langage_lbl['LBL_HEADER_TOPBAR_SIGN_IN_TXT']; ?></a>
            <?php }?>
            </div>
        </div>
    </div>
</section>
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