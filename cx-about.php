<?php
include_once("common.php");

$vCode = $_SESSION['sess_lang'];
$showSignRegisterLinks = 1; 
$db_about = $generalobj->getStaticPage(52,$_SESSION['sess_lang']);

$page_title = $db_about['page_title'];
$pagesubtitle = json_decode($db_about[0]['pageSubtitle'],true);
if(empty($pagesubtitle["pageSubtitle_".$vCode])) {
    $vCode = 'EN';
    $db_about = $generalobj->getStaticPage(52,$vCode);
    $page_title = $db_about['page_title'];
}
$pagesubtitle_lang = $pagesubtitle["pageSubtitle_".$vCode];

$page_desc = json_decode($db_about['page_desc'],true);

//if(empty($template)) $template = 'Cubex';

$image = $image1 = $image2 = '';
if(!empty($db_about['vImage2'])) $image2 = "assets/img/apptype/$template/".$db_about['vImage2'];
if(!empty($db_about['vImage1'])) $image1 = "assets/img/apptype/$template/".$db_about['vImage1'];
if(!empty($db_about['vImage'])) $image = "assets/img/apptype/$template/".$db_about['vImage'];

?>
<!DOCTYPE html>
<html lang="en" dir="<?=(isset($_SESSION['eDirectionCode']) && $_SESSION['eDirectionCode'] != "")?$_SESSION['eDirectionCode']:'ltr';?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0, user-scalable=no, target-densityDpi=device-dpi">
    <!--<title><?=$SITE_NAME?></title>-->
	<title><?php echo $db_about['meta_title'];?></title>
	<meta name="keywords" value="<?=$db_about['meta_keyword'];?>"/>
	<meta name="description" value="<?=$db_about['meta_desc'];?>"/>
    <!-- Default Top Script and css -->
    <?php include_once("top/top_script.php");?>
    <!-- End: Default Top Script and css-->
</head>
<body id="wrapper">
    <!-- home page -->
    <!-- home page -->
    <?php if($template!='taxishark'){?>
    <div id="main-uber-page">
    <?php } ?>
        <!-- Left Menu -->
    <?php include_once("top/left_menu.php");?>
    <!-- End: Left Menu-->
        <!-- Top Menu -->
        <?php include_once("top/header_topbar.php");?>
        <!-- End: Top Menu-->
        <!-- First Section -->
		<?php include_once("top/header.php");?>
<div class="about-main">
    <div class="heading-area">
        <div class="heading-area-inner">
            <h1><?=$page_title ?></h1>
        </div>
    </div>
    <div class="main-page-wrap">
        <div class="about-caption">
            <div class="about-caption-inner">
                <?= $pagesubtitle_lang; ?>
            </div>
        </div>
        <article>
            <div class="article-inner">
                <div class="artical-left">
                    <div class="article-image">
                        <div class="article-image-inner" style="background-image:url('<?= $tconfig["tsite_url"].'resizeImg.php?w=570&h=639&src='.$image; ?>')"></div>
                    </div>
                </div>
                <div class="artical-right">
                    <?= $page_desc['FirstDesc']; ?>
                </div>
            </div>
        </article>
		
        <article class="inverse">
            <div class="article-inner">
                <div class="artical-left">
                    <div class="article-image">
                        <div class="article-image-inner" style="background-image:url('<?= $tconfig["tsite_url"].'resizeImg.php?w=570&h=639&src='.$image1; ?>')"></div>
                    </div>
                </div>
                <div class="artical-right">
                    <?= $page_desc['SecDesc']; ?>
                </div>
            </div>
        </article>
		
		<article>
            <div class="article-inner">
                <div class="artical-left">
                    <div class="article-image">
                        <div class="article-image-inner" style="background-image:url('<?= $tconfig["tsite_url"].'resizeImg.php?w=570&h=639&src='.$image2; ?>')"></div>
                    </div>
                </div>
                <div class="artical-right">
                    <?= $page_desc['ThirdDesc']; ?>
                </div>
            </div>
        </article>
    </div>
</div>
<!-- *************call to section end************* -->

  <!-- footer part -->
    <?php include_once('footer/footer_home.php');?>

    <div style="clear:both;"></div>
     <?php if($template!='taxishark'){?>
     </div>
     <?php } ?>
    <!-- footer part end -->
<!-- Footer Script -->
<?php include_once('top/footer_script.php');

$lang = get_langcode($vCode);
        ?>                  
<!-- End: Footer Script -->
</body>
</html>
