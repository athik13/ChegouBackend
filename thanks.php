<?php
include_once("common.php");
//include_once ('include_generalFunctions_dl.php'); 
//check_type_wise_mr('user_info');
$order_id = $_REQUEST['orderid'];
$order_id = base64_encode(base64_encode(trim($_REQUEST['orderid'])));
//invoice_deliverall.php?iOrderId=T1Rreg==

?>
<!DOCTYPE html>
<html lang="en" dir="<?= (isset($_SESSION['eDirectionCode']) && $_SESSION['eDirectionCode'] != "") ? $_SESSION['eDirectionCode'] : 'ltr'; ?>">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width,initial-scale=1">
        <title><?php echo $langage_lbl['LBL_MANUAL_STORE_THANK_YOU']; ?></title>
        <meta name="keywords" value="<?= $meta_arr['meta_keyword']; ?>"/>
        <meta name="description" value="<?= $meta_arr['meta_desc']; ?>"/>
        <link href="assets/css/apptype/<?= $template ?>/custom-order/OverlayScrollbars.css" rel="stylesheet">
        <!-- Default Top Script and css -->
        <?php include_once("top/top_script.php"); ?>
        <?php include_once("top/validation.php"); ?>
        <!-- End: Default Top Script and css--> 
        <script>
            $(document).ready(function () {
                setTimeout(function () {
                    window.location.href = "<?php echo $tconfig["tsite_url"]; ?>invoice_deliverall.php?action=manual&iOrderId=<?php echo $order_id; ?>";
                }, 5000);
            });
        </script>

    </head>
    <body>
        <div id="main-uber-page">
            <!-- Left Menu -->
            <?php include_once("top/left_menu.php"); ?>
            <!-- End: Left Menu-->
            <!-- home page -->
            <!-- Top Menu -->
            <?php include_once("top/header_topbar.php"); ?>
            <!-- End: Top Menu-->
            <!-- contact page-->
            <div class="page-contant page-contant-new">
                <div class="page-contant-inner clearfix">

                    <!-- trips detail page -->
		<?php if($generalobj->checkXThemOn() == 'Yes') { ?>
		<div class="thanks-holder">
			<div class="thanks-caption">
                    <?php } else { ?>
		    <div class="static-page-a">
			<div class="static-page-aa">
		    <?php } ?>
                            <img src="assets/img/custome-store/checked.svg" alt="" />
                        </div>
                        <h2> <?php echo $langage_lbl['LBL_MANUAL_STORE_THANK_YOU']; ?>!</h2>
                        <p><?php echo $langage_lbl['LBL_MANUAL_STORE_THANK_YOU_ORDER_PLACE_ORDER']; ?></p>
                    </div>
                </div>
            </div>
            <!-- home page end-->
            <!-- footer part -->
            <?php include_once('footer/footer_home.php'); ?>
            <!-- End:contact page-->
            <div style="clear:both;"></div>
        </div>
	      <?php include_once('top/footer_script.php'); ?>
    </body>
</html>
<?php 
if($_SESSION[$orderDetailsSession]){
      unset($_SESSION[$orderDetailsSession]);
        unset($_SESSION[$userSession]);
        unset($_SESSION[$orderUserSession]);
        unset($_SESSION[$orderServiceSession]);
        unset($_SESSION[$orderUserIdSession]);
        unset($_SESSION[$orderAddressIdSession]);
        unset($_SESSION[$orderCouponSession]);
        unset($_SESSION[$orderCouponNameSession]);

        unset($_SESSION[$orderCurrencyNameSession]);
        //unset($_SESSION['sess_currentpage_url_mr']);
        unset($_SESSION[$orderLatitudeSession]);
        unset($_SESSION[$orderLongitudeSession]);
        unset($_SESSION[$orderAddressSession]);
        unset($_SESSION[$orderDataSession]);

        unset($_SESSION[$orderUserNameSession]);
        unset($_SESSION[$orderCompanyNameSession]);
        unset($_SESSION[$orderUserEmailSession]);
        unset($_SESSION[$orderStoreIdSession]);
        unset($_SESSION[$orderServiceNameSession]);
}
?>