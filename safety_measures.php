<?
	include_once("common.php");
	//error_reporting(E_ALL);
	global $generalobj;
	$script="Terms Condition";
	
	$fromapp = !empty($_REQUEST['fromapp']) ? $_REQUEST['fromapp'] : "No";
	$fromweb = !empty($_REQUEST['fromweb']) ? $_REQUEST['fromweb'] : "No";
	
	if($fromweb=='Yes') {
		$lang = !empty($_SESSION['sess_lang']) ? $_SESSION['sess_lang'] : "EN";
	} else {
		$lang = !empty($_REQUEST['fromlang']) ? $_REQUEST['fromlang'] : "EN";
	}
	if(empty($lang)) $lang = "EN";
	
	$safetyimg = "/webimages/icons/DefaultImg/ic_safety.png";
	$safetyimgUrl = (file_exists($tconfig["tpanel_path"].$safetyimg)) ? $tconfig["tsite_url"].$safetyimg : "";
	
	$meta = $generalobj->getStaticPage(54,$lang);
?>
<!DOCTYPE html>
<html lang="en" dir="<?=(isset($_SESSION['eDirectionCode']) && $_SESSION['eDirectionCode'] != "")?$_SESSION['eDirectionCode']:'ltr';?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title><?=$meta['meta_title'];?></title>
	<meta name="keywords" value="<?=$meta['meta_keyword'];?>"/>
	<meta name="description" value="<?=$meta['meta_desc'];?>"/>
    <!-- Default Top Script and css -->
    <?php include_once("top/top_script.php");?>
    <!-- End: Default Top Script and css-->
</head>
<body>
    <!-- home page -->
    <div id="main-uber-page">
    <!-- Left Menu -->
    <?php include_once("top/left_menu.php");?>
    <!-- End: Left Menu-->
        <!-- Top Menu -->
        <?php if($fromweb=="Yes") { include_once("top/header_topbar.php"); } ?>
        <!-- End: Top Menu-->
        <!-- contact page-->
         <?php if($generalobj->checkXThemOn() == 'Yes') { ?>
	<div class="gen-cms-page">
		<div class="gen-cms-page-inner">
			<!--<img src="<?= $safetyimgUrl ?>">-->
			<div style="text-align: center;"><img src="<?= $safetyimgUrl ?>" width="150" height="150"></div>
			<h2 class="header-page">
			<?php } else { ?>
		<div class="page-contant">
		<div class="page-contant-inner">
		      <h2 class="header-page trip-detail"><?php } ?><?=$meta['page_title'];?></h2>
		      <!-- trips detail page -->
		      <div class="static-page">
		        <?=$meta['page_desc'];?>
		      </div>
		    </div>
		</div>
    <!-- footer part -->
    <?php if($fromweb=="Yes") { include_once('footer/footer_home.php'); } ?>
    <!-- footer part end -->
            <!-- End:contact page-->
            <div style="clear:both;"></div>
    </div>
    <!-- home page end-->
    <!-- Footer Script -->
    <?php include_once('top/footer_script.php');?>
    <!-- End: Footer Script -->
</body>
</html>
