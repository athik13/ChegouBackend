<?php
//print_r($_REQUEST);
if (isset($_REQUEST['edit']) && $_REQUEST['edit'] == 'yes') {
    $_SESSION['edita'] = 1;
}
if (isset($_REQUEST['edit']) && $_REQUEST['edit'] == 'no') {
    //setcookie('edit', $cookie_value, time() - (86400 * 30));
    unset($_SESSION['edita']);
    $_SESSION['edita'] = "";
}
include_once "include/config.php";
include_once $templatePath . "top/top_script.php";
include_once "validation.php";
$DEFAULT_COUNTRY_CENTER_LATITUDE =$DEFAULT_COUNTRY_CENTER_LONGITUDE= "";

if(!empty($country_data_arr[$DEFAULT_COUNTRY_CODE_WEB]) && !empty($country_data_arr[$DEFAULT_COUNTRY_CODE_WEB]['tLatitude']) && !empty($country_data_arr[$DEFAULT_COUNTRY_CODE_WEB]['tLongitude'])){
    $DEFAULT_COUNTRY_CENTER_LATITUDE = $country_data_arr[$DEFAULT_COUNTRY_CODE_WEB]['tLatitude'];
    $DEFAULT_COUNTRY_CENTER_LONGITUDE = $country_data_arr[$DEFAULT_COUNTRY_CODE_WEB]['tLongitude'];
}
?>

<script>
        var GOOGLE_SEVER_API_KEY_WEB = "<?=$GOOGLE_SEVER_API_KEY_WEB?>";
        var GOOGLE_SEVER_GCM_API_KEY = "<?=$GOOGLE_SEVER_GCM_API_KEY?>";
        var DEFAULT_COUNTRY_CENTER_LATITUDE = "<?=$DEFAULT_COUNTRY_CENTER_LATITUDE?>";
        var DEFAULT_COUNTRY_CENTER_LONGITUDE = "<?=$DEFAULT_COUNTRY_CENTER_LONGITUDE?>";
        var sess_lang = "<?=$_SESSION['sess_lang'];?>";
        var strategy = "<?=$MAPS_API_REPLACEMENT_STRATEGY;?>";
        var MAPS_API_REPLACEMENT_STRATEGY = "<?=$MAPS_API_REPLACEMENT_STRATEGY;?>";
        var GOOGLE_API_REPLACEMENT_URL = "<?=GOOGLE_API_REPLACEMENT_URL;?>";
        var TSITE_DB = "<?=TSITE_DB;?>";
		var tsite_url_base = "<?= $siteUrl;?>";
        //MAPS_API_REPLACEMENT_STRATEGY = "Google";
		if(MAPS_API_REPLACEMENT_STRATEGY.toUpperCase() != "ADVANCE"){
			MAPS_API_REPLACEMENT_STRATEGY = "None";
		}
		
		function isRetinaDisplay() {
			if (window.matchMedia) {
				var mq = window.matchMedia("only screen and (min--moz-device-pixel-ratio: 1.3), only screen and (-o-min-device-pixel-ratio: 2.6/2), only screen and (-webkit-min-device-pixel-ratio: 1.3), only screen  and (min-device-pixel-ratio: 1.3), only screen and (min-resolution: 1.3dppx)");
				return (mq && mq.matches || (window.devicePixelRatio > 1)); 
			}
		}
		
		var isRatinaDisplay = isRetinaDisplay();
		
		
</script>
<script type='text/javascript' src='<?= $siteUrl;?>assets/map/gmaps.js'></script>
<script type='text/javascript' src='<?= $siteUrl;?>assets/js/jquery-ui.min.js'></script>
<script type='text/javascript' src='<?= $siteUrl;?>assets/js/bootbox.min.js'></script>
<script type='text/javascript' src='<?= $siteUrl;?>assets/js/ajax_for_advance_strategy.js'></script>
<script type='text/javascript' src='<?= $siteUrl;?>assets/js/network_js.js'></script>
<script type='text/javascript' src='<?= $siteUrl;?>assets/js/reverse_geo_code.js'></script>
<script type='text/javascript' src='<?= $siteUrl;?>assets/js/reverse_geo_direction_code.js'></script>
<link href="<?= $siteUrl;?>assets/css/autocomplete_box.css" rel="stylesheet" type="text/css" />
<link href="<?= $siteUrl;?>assets/css/restriction_modal.css" rel="stylesheet" type="text/css" />
<link href="<?= $siteUrl;?>assets/css/blur.css" rel="stylesheet" type="text/css" />
<style>
/* Progress bar start */
	.progress-indeterminate{
			height: 2px;
			position: relative; 
			bottom: 0;
			padding: 0 ;
			margin-bottom: 0px !important;
			overflow:hidden;
			box-shadow:none !important;
			background-color: transparent;
	}
	.progress-bar{
		background-color:#219201;
	}
	.progress-bar.indeterminate {
		box-shadow:none !important;
		position: relative;
	  /* animation: progress-indeterminate 2s linear infinite; */
	  animation : progress-indeterminate 2s cubic-bezier(.6,.04,.98,.34) infinite;
	    background-image:
		-webkit-linear-gradient(-60deg, transparent 33%, rgba(0, 0, 0, .1) 33%, rgba(0, 0, 0, 0) 33%, transparent 33%), -webkit-linear-gradient(top, rgba(255, 255, 255, .10), rgba(241, 234, 234, 0.88)), -webkit-linear-gradient(left, #ffff,#219201);
		border-radius: 50px; 
		background-size: 35px 20px, 100% 100%, 100% 100%;
	}
	@keyframes progress-indeterminate {
	   from { left: -85%; width: 85%; }
	   to { left: 100%; width: 85%;}
	}
	.box_in_map{position:relative;} /* box_in_map use for textbox in google map  */
	.box_in_map .progress-indeterminate { position:absolute; bottom:0px;height: 2px;width: 21.50% !important;top: 27px;right: 0px;}
	.form-column-full .progress-indeterminate{width:100% !important;}
	.half.newrow .progress-indeterminate{width:100% !important;}
	.map_to_createrequest .progress-indeterminate{ position:absolute; top:45px !important;height: 2px;width: 44.50% !important;right:25px !important;}
	.drop-location .progress-indeterminate{height: 2px;width: 97% !important;z-index: 1;margin-left: 10px;bottom: 0px;right: auto;}
	.ui-autocomplete .ui-menu-item{
		list-style-image:none !important;
	}
	
 /* Progress bar end */
</style>

<script>
    var timezone = Intl.DateTimeFormat().resolvedOptions().timeZone;
    document.cookie = "vUserDeviceTimeZone=" + timezone;
</script>
<?=$GOOGLE_ANALYTICS;?>
<?php

//added by SP on 29-06-2019 for disallow other css and apply css which are given by ckeditor
$filename = basename($_SERVER['REQUEST_URI'], '?' . $_SERVER['QUERY_STRING']);
if ($filename == 'Page-Not-Found' || $filename == 'about' || $filename == 'help-center' || $filename == 'terms-condition' || $filename == 'how-it-works' || $filename == 'trust-safty-insurance' || $filename == 'privacy-policy' || $filename == 'legal') {
    ?>

    <script>
        $(document).ready(function () {
            $(".static-page ol li").each(function (index) {
                $(this).attr('data-number', index + 1);
            });
        })
    </script>

    <style>
        strong {
            font-weight : bold;
        }
        em {
            font-style : italic;
        }
        u {
            text-decoration: underline;
        }
        s {
            text-decoration: line-through;
        }
        .static-page ol li:before {
            content: attr(data-number);
            position: absolute;
            left: 0;
            font-size: 14px;
            font-weight: 600;
        }
        .static-page ol li {
            background:none;
            position:relative;
        }
    </style>
<?php }?>