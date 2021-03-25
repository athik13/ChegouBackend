<?
include_once("common.php");
global $generalobj;
$script = "Help Center";
$meta = $generalobj->getStaticPage(2, $_SESSION['sess_lang']);
?>
<!DOCTYPE html>
<html lang="en" dir="<?= (isset($_SESSION['eDirectionCode']) && $_SESSION['eDirectionCode'] != "") ? $_SESSION['eDirectionCode'] : 'ltr'; ?>">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width,initial-scale=1">
        <title><?php if ($_GET['action'] == 'Notsubscribe') { ?><?= $langage_lbl['LBL_INFO_FAIL_TXT']; ?><?php } else if ($_GET['action'] == 'Subscribe') { ?><?= $langage_lbl['LBL_INFO_SUCCESS_TXT']; ?><?php } ?></title>
        <meta name="keywords" value="<?= $meta['meta_keyword']; ?>"/>
        <meta name="description" value="<?= $meta['meta_desc']; ?>"/>
        <!-- Default Top Script and css -->
        <?php include_once("top/top_script.php"); ?>
        <!-- End: Default Top Script and css-->
    </head>
    <body>
        <!-- home page -->
        <div id="main-uber-page">
            <!-- Left Menu -->
            <?php include_once("top/left_menu.php"); ?>
            <!-- End: Left Menu-->
            <!-- Top Menu -->
            <?php include_once("top/header_topbar.php"); ?>
            <!-- End: Top Menu-->
            <!-- contact page-->
	<?php if($generalobj->checkXThemOn() == 'Yes') { ?>
	 <div class="page-contant page-contant-new" align="center">
                <div class="gen-cms-page-inner">
                    <div class="thanks-holder">
                        <div class="thanks-caption">
                            <img src="assets/img/custome-store/checked.svg" alt="" />
                        </div>
                        <h2><?php if ($_GET['action'] == 'Notsubscribe') { ?><?= $langage_lbl['LBL_INFO_FAIL_TXT']; ?><?php } else if ($_GET['action'] == 'Subscribe') { ?><?= $langage_lbl['LBL_INFO_SUCCESS_TXT']; ?><?php } ?></h2>
                        <?php if ($_GET['action'] == 'Subscribe') { ?>
                            <strong><?= $langage_lbl['LBL_SUBSCRIBE_MESSAGE']; ?></strong>
                        <?php } elseif ($_GET['action'] == 'Alreadyunsubscribe') { ?>
                            <strong><?= $langage_lbl['LBL_ALREADY_UNSUBSCRIBE_MESSAGE']; ?></strong>
                        <?php } elseif ($_GET['action'] == 'Alreadysubscribe') { ?>
                            <strong><?= $langage_lbl['LBL_ALREADY_SUBSCRIBE_MESSAGE']; ?></strong>
                        <?php } elseif ($_GET['action'] == 'Notsubscribe') { ?>

                            <strong><?= $langage_lbl['LBL_NOTSUBSCRIBE_MESSAGE']; ?></strong>
                            <strong><?= $langage_lbl['LBL_NOTSUBSCRIBE_MESSAGE_BELOW']; ?>&nbsp;</strong>
                            <a href="#" data-target="#newsletter" data-toggle="modal" class="MainNavText gen-btn" id="MainNavHelp"><?= $langage_lbl['LBL_NEWSLETTER']; ?></a>
                            
                        <?php } elseif ($_GET['action'] == 'Unsubscribe') { ?>
                            <strong><?= $langage_lbl['LBL_UNSUBSCRIBE_SUCCESS_MESSAGE']; ?></strong>
                            <p><?= $langage_lbl['LBL_NOTSUBSCRIBE_MESSAGE_BELOW']; ?></p>
                            <div class="button-block justify-center">
                                <a href="#" data-target="#newsletter" data-toggle="modal" class="MainNavText gen-btn" id="MainNavHelp"><?= $langage_lbl['LBL_NEWSLETTER']; ?></a>
                                <a class='gen-btn' href="index.php">Home</a>
                            </div>
                        <?php }elseif($_GET['action'] == 'Recaptchafail') {?>
                            <strong><?= $langage_lbl['LBL_CAPTCHA_MATCH_MSG']; ?></strong>
                        <?php } ?>
                    </div>
                </div>
            </div>
	<?php } else { ?>	
            <div class="page-contant" align="center">
                <div class="page-contant-inner">
                    <h2 style="font-size: 40px;"><?php if ($_GET['action'] == 'Notsubscribe') { ?><?= $langage_lbl['LBL_INFO_FAIL_TXT']; ?><?php } else if ($_GET['action'] == 'Subscribe') { ?><?= $langage_lbl['LBL_INFO_SUCCESS_TXT']; ?><?php } ?></h2>
                    <br>
                    <?php if ($_GET['action'] == 'Subscribe') { ?>
                        <p style="font-size: 30px;"><?= $langage_lbl['LBL_SUBSCRIBE_MESSAGE']; ?></p>
                    <?php } elseif ($_GET['action'] == 'Alreadyunsubscribe') { ?>
                        <p style="font-size: 30px;"><?= $langage_lbl['LBL_ALREADY_UNSUBSCRIBE_MESSAGE']; ?></p>
                    <?php } elseif ($_GET['action'] == 'Alreadysubscribe') { ?>
                        <p style="font-size: 30px;"><?= $langage_lbl['LBL_ALREADY_SUBSCRIBE_MESSAGE']; ?></p>
                    <?php } elseif ($_GET['action'] == 'Notsubscribe') { ?>
                        <p style="font-size: 30px;"><?= $langage_lbl['LBL_NOTSUBSCRIBE_MESSAGE']; ?></p>
                        <p style="font-size: 15px;"><?= $langage_lbl['LBL_NOTSUBSCRIBE_MESSAGE_BELOW']; ?>&nbsp;<a href="#" data-target="#newsletter" data-toggle="modal" class="MainNavText" id="MainNavHelp"><?= $langage_lbl['LBL_NEWSLETTER']; ?></a>.</p>

                    <?php } elseif ($_GET['action'] == 'Unsubscribe') { ?>
                        <p style="font-size: 30px;"><?= $langage_lbl['LBL_UNSUBSCRIBE_SUCCESS_MESSAGE']; ?></p>
                        <br><?= $langage_lbl['LBL_NOTSUBSCRIBE_MESSAGE_BELOW']; ?>&nbsp;<a href="#" data-target="#newsletter" data-toggle="modal" class="MainNavText" id="MainNavHelp"><?= $langage_lbl['LBL_NEWSLETTER']; ?></a>. 
                    <?php }elseif($_GET['action'] == 'Recaptchafail') {?>
                        <p style="font-size: 30px;"><?= $langage_lbl['LBL_CAPTCHA_MATCH_MSG']; ?></p>
                    <?php } ?>
                </div>
                <br><br>
                <a class='btn btn-success' href="index.php" style="background:#000; border:1px solid #000;">Home</a>
            </div>
	  <?php } ?>

            <!-- footer part -->
            <?php include_once('footer/footer_home.php'); ?>
            <!-- footer part end -->
            <!-- End:contact page-->
            <div style="clear:both;"></div>
        </div>
        <!-- home page end-->
        <!-- Footer Script -->
        <?php include_once('top/footer_script.php'); ?>
        <!-- End: Footer Script -->
    </body>
</html>
