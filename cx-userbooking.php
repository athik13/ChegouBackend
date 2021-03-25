<?php
include_once("common.php");

if ($_REQUEST['userType1'] != 'admin') {
    $generalobj->check_manual_taxi_member_login();
    /* $abc = 'company';
      $url = $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
      $generalobj->setRole($abc, $url); */
}
// print_r($_SESSION); exit;
$script = "booking";
?>
<!DOCTYPE html>
<html lang="en" dir="<?= (isset($_SESSION['eDirectionCode']) && $_SESSION['eDirectionCode'] != "") ? $_SESSION['eDirectionCode'] : 'ltr'; ?>">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width,initial-scale=1">

        <title><?= $SITE_NAME ?> | <?= $langage_lbl['LBL_MANUAL_TAXI_DISPATCH']; ?></title>
        <meta name="keywords" value="<?= $meta_arr['meta_keyword']; ?>"/>
        <meta name="description" value="<?= $meta_arr['meta_desc']; ?>"/>
        <link rel="stylesheet" href="assets/plugins/bootstrap/css/bootstrap.css" /><!-- This is only used for calendar only--> 
        <!-- Default Top Script and css -->
        <?php include_once("top/top_script.php"); ?>
        <?php include_once("top/validation.php"); ?>
        <!-- End: Default Top Script and css-->
        <link rel="stylesheet" href="assets/css/MoneAdmin.css" />
        <link rel="stylesheet" href="assets/plugins/Font-Awesome/css/font-awesome.css" />
        <link rel="stylesheet" href="assets/plugins/Font-Awesome/css/font-awesome_new.css" />
        <!--<link rel="stylesheet" href="assets/css/manualstyle.css" />-->
        <link rel="stylesheet" href="assets/css/apptype/<?php echo $template; ?>/manualstyle.css" />
        <!--<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.min.js" type="text/javascript"></script> -->
        <script src="https://maps.google.com/maps/api/js?sensor=true&key=<?= $GOOGLE_SEVER_API_KEY_WEB ?>&libraries=places" type="text/javascript"></script>
        <script type='text/javascript' src='assets/map/gmaps.js'></script>
        <script type='text/javascript' src='assets/js/jquery-ui.min.js'></script>
        <script type='text/javascript' src='assets/js/bootbox.min.js'></script>
        <link href="assets/css/jquery.mCustomScrollbar.css" rel="stylesheet" />
        <script src="assets/js/jquery.mCustomScrollbar.concat.min.js"></script>
        <script>
            (function ($) {
                $(window).load(function () {
                    $(".content").mCustomScrollbar({
                        scrollInertia: 400,
                        scrollButtons: {
                            enable: true,
                        },
                        advanced: {
                            updateOnBrowserResize: true, /*update scrollbars on browser resize (for layouts based on percentages): boolean*/
                            updateOnContentResize: true, /*auto-update scrollbars on content resize (for dynamic content): boolean*/autoExpandHorizontalScroll: true, /*auto-expand width for horizontal scrolling: boolean*/
                        },
                        autoHideScrollbar: true
                    });
                    /* disable */
                    $("#disable-scrollbar").click(function (e) {
                        e.preventDefault();
                        $("#content_1").mCustomScrollbar("disable", true);
                    });
                    $("#disable-scrollbar-no-reset").click(function (e) {
                        e.preventDefault();
                        $("#content_1").mCustomScrollbar("disable");
                    });
                    $("#enable-scrollbar").click(function (e) {
                        e.preventDefault();
                        $("#content_1").mCustomScrollbar("update");
                    });
                    /* destroy */
                    $("#destroy-scrollbar").click(function (e) {
                        e.preventDefault();
                        $("#content_1").mCustomScrollbar("destroy");
                    });
                    $("#rebuild-scrollbar").click(function (e) {
                        e.preventDefault();
                        $("#content_1").mCustomScrollbar({
                            scrollButtons: {
                                enable: true
                            }
                        });
                    });
                });
            })(jQuery);
        </script>
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
            <!--<div class="page-contant1">-->
            <div class="page-contant-inner1">
                <? include_once('booking/cx-add_booking.php'); ?>
            </div>
            <!--</div>-->
            <!-- footer part -->
            <?php
            if (empty($_SESSION['sess_iAdminUserId']) && strpos($_SERVER['SCRIPT_FILENAME'], 'userbooking.php') !== false) {
                include_once('footer/footer_home.php');
            }
            ?>
            <!-- footer part end -->
            <!-- End:contact page-->
            <div style="clear:both;"></div>
        </div>
        <!-- home page end-->
        <!-- Footer Script -->
<?php include_once('top/footer_script.php'); ?>
    </body>
</html>