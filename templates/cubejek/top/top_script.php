<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0, user-scalable=no, target-densityDpi=device-dpi" />
<meta content="" name="author" />
<?php
$colorcsspath  ='';
$favicon  = 'favicon.ico';
?>
<link rel="icon" href="<?php echo $favicon;?>" type="image/x-icon">
<?php
$lang = isset($_SESSION['sess_lang']) ? $_SESSION['sess_lang'] : "EN";
$lang_arr = array('AR', 'UR', 'HW', 'PS');
$lang_ltr = "";
if (in_array($lang, $lang_arr)) {
    $lang_ltr = 'yes';
}
?>
<link rel="stylesheet" href="<?php echo $templatePath; ?>assets/plugins/bootstrap/css/bootstrap.css" />
<link rel="stylesheet" href="<?php echo $templatePath; ?>assets/css/sign-up.css" />
<link rel="stylesheet" href="<?php echo $templatePath; ?>assets/plugins/magic/magic.css" />
<link rel="stylesheet" href="assets/css/bootstrap-front.css" />
<link rel="stylesheet" type="text/css" href="assets/css/jquery-ui.css">
<link rel="stylesheet" href="assets/plugins/Font-Awesome/css/font-awesome.css" />
<script src="assets/js/jquery.min.js" type="text/javascript"></script>
<? //if ($SITE_VERSION != 'v5') { ?> 
    
        
    
    <?php /*<?php if($_SESSION['sess_lang']=='EN'){?>
    <link rel="stylesheet" href="assets/css/style_v5.css">
<?php }else if(in_array($_SESSION['sess_lang'],array('ID','ES','AZ','PT','SI','VI','TL','BN','JA','TR','RU'))){?>
    <link rel="stylesheet" href="assets/css/style_v5_specific.css">
<?php }else if(in_array($_SESSION['sess_lang'],array('TA'))){?>
    <link rel="stylesheet" href="assets/css/style_v5_ta.css">
<?php }else{?>
    <link rel="stylesheet" href="assets/css/style_v5_other.css">
   <?php } ?> 
    <link rel="stylesheet" href="assets/css/style_v5_color.css">
<? } else { ?><?php */?>
    <link rel="stylesheet" href="assets/css/design_v5_cubejek.css">
    <link rel="stylesheet" href="assets/css/style_v5_cubejek.css">  
    <link rel="stylesheet" href="assets/css/style_v5_color_cubejek.css"> 
    <link href="assets/css/red.css" rel="alternate stylesheet" title="red" media="screen">
    <link href="assets/css/green.css" rel="alternate stylesheet" title="green" media="screen">
    <link href="assets/css/blue.css" rel="alternate stylesheet" title="blue" media="screen">
    <link href="assets/css/yellow.css" rel="alternate stylesheet" title="yellow" media="screen">
    <link href="assets/css/switcher.css" rel="stylesheet" media="screen">
    <script src="assets/js/interface.js"></script> 
    <script src="assets/js/styleswitch.js"></script>
    <script src="assets/js/styleswitch-demo.js"></script>
<? //} ?>
<link rel="stylesheet" href="assets/css/fa-icon.css">
<link href="assets/css/initcarousel.css" rel="stylesheet" type="text/css">
<link rel="stylesheet" href="assets/css/media_cubejek.css">
<?php if ($lang_ltr == "yes") { ?>
    <link rel="stylesheet" href="assets/css/style_rtl_cubejek.css">
<?php } ?>
<link href='//fonts.googleapis.com/css?family=Raleway:400,700,300,500,900,800,600,200,100' rel='stylesheet' type='text/css'>
<?php if (isset($_SESSION['sess_lang']) && $_SESSION['sess_lang'] != '') { ?>
    <link rel="stylesheet" href="assets/css/lang/<?= strtolower($_SESSION['sess_lang']); ?>.css"> 
<?php } ?>
<!-- Default js-->
