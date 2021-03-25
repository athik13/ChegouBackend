<?php //echo $template;die; ?>
<?php //if($generalobj->checkCubexThemOn() == 'Yes') { ?>
            
        <!--<link rel="stylesheet" href="<?= $siteUrl; ?>assets/css/apptype/Cubex/custom-order/cust_info.css" />
        <link rel="stylesheet" href="<?= $siteUrl; ?>assets/css/apptype/Cubex/custom-order/rest_listing.css" />
        <link rel="stylesheet" href="<?= $siteUrl; ?>assets/css/apptype/Cubex/custom-order/rest-menu.css" />
         <link rel="stylesheet" href="<?= $siteUrl; ?>assets/css/apptype/Cubex/custom-order/app_theme_color.css" /> -->
        <!--<link rel="stylesheet" type="text/less" href="<?= $siteUrl; ?>assets/css/apptype/Cubex/custom-order/app_theme_color.less" />-->
        
<?php //} else { 
	$siteUrl = $tconfig['tsite_url'];
?>
<link rel="stylesheet" href="<?= $siteUrl; ?>assets/css/apptype/<?= $template; ?>/custom-order/cust_info.css" />
<link rel="stylesheet" href="<?= $siteUrl; ?>assets/css/apptype/<?= $template; ?>/custom-order/rest_listing.css" />
<link rel="stylesheet" href="<?= $siteUrl; ?>assets/css/apptype/<?= $template; ?>/custom-order/rest-menu.css" />
<!--<link rel="stylesheet" href="<?= $siteUrl; ?>assets/css/apptype/<?= $template; ?>/custom-order/app_theme_color.css" />-->
<link rel="stylesheet" type="text/css" href="<?= $siteUrl; ?>assets/css/apptype/<?= $template; ?>/custom-order/restaurant-listing.css">
<link rel="stylesheet" type="text/less" href="<?= $siteUrl; ?>assets/css/apptype/<?= $template; ?>/custom-order/app_theme_color.less" />
<?php //} ?>