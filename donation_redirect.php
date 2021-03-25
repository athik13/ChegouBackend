<?php include_once('common.php'); ?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width,initial-scale=1">
        <title><?= $SITE_NAME ?></title>
        <style>
        .main-part{margin:0px; padding:0px; float:left; width:100%;}
        .inner-part{margin:0px auto; padding:0px; max-width:1200px; position:relative;}
        .inner-part h1{ margin:50px 0 0 ; padding:0px; float:left; width:100%; text-transform:capitalize; text-align:center; font-size:51px;line-height: 50px; font-family: 'maven_proregular';}
        @media screen and (min-width:1px) and (max-width:767px) {
        .inner-part{max-width:100%;}
		.inner-part h1{font-size: 50px; line-height: 58px;}
        }
        @media screen and (min-width:1px) and (max-width:567px) {
        .inner-part{max-width:100%;}
		.inner-part h1{font-size: 33px; line-height: 58px;}
        }
        
        @media screen and (min-width:1px) and (max-width:414px) {
        .inner-part{max-width:100%;}
		.inner-part h1{font-size: 33px; line-height: 58px;}
        }
        
        @media screen and (min-width:1px) and (max-width:320px) {
        .inner-part{max-width:100%;}
		.inner-part h1{font-size: 33px; line-height: 58px;}
        }
        
		@media screen and (min-width:768px) and (max-width:1023px) {
		 .inner-part{max-width:100%;}
		.inner-part h1{font-size: 66px; line-height: 100px;}
		}
		
        </style>
        
        <div class="main-part">
        <div class="inner-part">
        <h1><?= $langage_lbl['LBL_DONATION_FILE_TXT']; ?></h1></div>
        </div>
    </head>
</html>
