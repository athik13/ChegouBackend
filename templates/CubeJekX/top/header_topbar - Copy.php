<?php
if ($_SESSION['sess_user'] == 'company') {
    $sql = "select * from company where iCompanyId = '" . $_SESSION['sess_iUserId'] . "'";
    $db_user = $obj->MySQLSelect($sql);
}
if ($_SESSION['sess_user'] == 'driver') {
    $sql = "select * from register_driver where iDriverId = '" . $_SESSION['sess_iUserId'] . "'";
    $db_user = $obj->MySQLSelect($sql);
}
if ($_SESSION['sess_user'] == 'rider') {
    $sql = "select * from register_user where iUserId = '" . $_SESSION['sess_iUserId'] . "'";
    $db_user = $obj->MySQLSelect($sql);
}
$col_class = "";
if ($user != "") {
    $col_class = "top-inner-color";
}
$logo = "logo.png";
$sql = "select vTitle, vCode, vCurrencyCode, eDefault from language_master where eStatus='Active' ORDER BY iDispOrder ASC";
$db_lng_mst = $obj->MySQLSelect($sql);
$count_lang = count($db_lng_mst);
$langCodeArr = array();
for ($l = 0; $l < $count_lang; $l++) {
    $langCodeArr[$db_lng_mst[$l]['vCode']] = $db_lng_mst[$l]['vTitle'];
}
$currency = "SELECT iCurrencyId,eDefault,vName FROM currency WHERE eStatus='Active' ORDER BY iDispOrder ASC";
$db_cur_mst = $obj->MySQLSelect($currency);
$count_cur = count($db_cur_mst);
$languageText = "LANGUAGE";
if (isset($langCodeArr[$_SESSION['sess_lang']])) {
    $languageText = $langCodeArr[$_SESSION['sess_lang']];
}

$fromOrder = "guest";
if (isset($_REQUEST['order']) && $_REQUEST['order'] != "") {
    $fromOrder = $_REQUEST['order'];
}else if(isset($_SESSION['MANUAL_ORDER_USER'])){
    $fromOrder = $_SESSION['MANUAL_ORDER_USER'];
}
$orderDetailsSession = "ORDER_DETAILS_" . strtoupper($fromOrder);
$orderServiceSession = "MAUAL_ORDER_SERVICE_".strtoupper($fromOrder);
$userSession = "MANUAL_ORDER_".strtoupper($fromOrder);
$orderServiceNameSession = "MANUAL_ORDER_SERVICE_NAME_".strtoupper($fromOrder);
$orderLatitudeSession = "MANUAL_ORDER_LATITUDE_".strtoupper($fromOrder);
$orderAddressSession = "MANUAL_ORDER_ADDRESS_".strtoupper($fromOrder);
$orderStoreIdSession = "MANUAL_ORDER_STORE_ID_".strtoupper($fromOrder);
//Added By HJ On 08-07-2019 For Hide/Show Order Address and Cart Icon Start
//echo "<pre>";print_r($_SESSION);die;
if (isset($_SESSION[$orderStoreIdSession]) && $_SESSION[$orderStoreIdSession] > 0) {
    $orderCompanyId = $_SESSION[$orderStoreIdSession];
} else if (isset($_REQUEST['id']) && $_REQUEST['id'] > 0) {
    $orderCompanyId = $_REQUEST['id'];
}
//echo $orderCompanyId;die;
$orderItemCount = $confirlAlert = 0;
$siteUrl = $tconfig['tsite_url'];
$orderItemListingUrl = $siteUrl . "order-items?order=" . $fromOrder;
$orderLogin = "order-items";
//echo "<pre>";print_r($_REQUEST);die;
if ($fromOrder == 'store' || $fromOrder == 'admin') {
    $orderItemListingUrl = $siteUrl . "user-order-information?order=" . $fromOrder;
    $orderLogin = "user-order-information?order=" . $fromOrder;
}
//print_R($_SESSION[$orderDetailsSession]); exit;
if (isset($_SESSION[$orderDetailsSession]) && count($_SESSION[$orderDetailsSession]) > 0 && $orderCompanyId > 0) {
    $orderItems = $_SESSION[$orderDetailsSession];
    for ($d = 0; $d < count($orderItems); $d++) {
        if (isset($orderItems[$d]['typeitem']) && $orderItems[$d]['typeitem'] == "remove") {
            //Removed Items here
        } else {
            $orderItemCount += 1;
            $confirlAlert += 1;
        }
    }
    $orderItemListingUrl = $siteUrl . "store-items?order=" . $fromOrder . "&id=" . $orderCompanyId;
}
//echo $orderItemCount."aaaa";die;
$addressPageArr = array("restaurant_listing.php", "restaurant_place-order.php", "restaurant_menu.php");
$addressEnable = $breadcumbArr = array();
//echo "<pre>";print_r($_SERVER['PHP_SELF']);die;
//Added By HJ On 22-06-2019 For Display Breadcumb Start
for ($d = 0; $d < count($addressPageArr); $d++) {
    if (strpos($_SERVER['PHP_SELF'], $addressPageArr[$d]) !== false) {
        $addressEnable[] = 1;
        if ($addressPageArr[$d] == "restaurant_listing.php") {
            if ($fromOrder == 'store') {
                $breadcumbArr = array($langage_lbl['LBL_ORDER_ITEMS_MANUAL_TXT'] => $orderLogin);
            } else {
                $breadcumbArr = array($langage_lbl['LBL_ORDER_ITEMS_MANUAL_TXT'] => $orderLogin, $langage_lbl['LBL_STORE_LISTING_MANUAL_TXT'] => "store-listing");
            }
        } else if ($addressPageArr[$d] == "restaurant_menu.php") {
            if ($fromOrder == 'store') {
                $breadcumbArr = array($langage_lbl['LBL_ORDER_ITEMS_MANUAL_TXT'] => $orderLogin, $langage_lbl['LBL_STORE_ITEMS_MANUAL_TXT'] => $orderItemListingUrl);
            } else {
                $breadcumbArr = array($langage_lbl['LBL_ORDER_ITEMS_MANUAL_TXT'] => $orderLogin, $langage_lbl['LBL_STORE_LISTING_MANUAL_TXT'] => "store-listing", $langage_lbl['LBL_STORE_ITEMS_MANUAL_TXT'] => $orderItemListingUrl);
            }
        } else if ($addressPageArr[$d] == "restaurant_place-order.php") {
            if ($fromOrder == 'store') {
                $breadcumbArr = array($langage_lbl['LBL_ORDER_ITEMS_MANUAL_TXT'] => $orderLogin, $langage_lbl['LBL_STORE_ITEMS_MANUAL_TXT'] => $orderItemListingUrl, $langage_lbl['LBL_CHECKOUT_ORDER_MANUAL_TXT'] => "store-order");
            } else {
                $breadcumbArr = array($langage_lbl['LBL_ORDER_ITEMS_MANUAL_TXT'] => $orderLogin, $langage_lbl['LBL_STORE_LISTING_MANUAL_TXT'] => "store-listing", $langage_lbl['LBL_STORE_ITEMS_MANUAL_TXT'] => $orderItemListingUrl, $langage_lbl['LBL_CHECKOUT_ORDER_MANUAL_TXT'] => "store-order");
            }
        }
    }
}
$breadcumbArr = $service_categories = array();
//$checkUser = check_user_mr();
$breadCumbCount = count($breadcumbArr);
//Added By HJ On 22-06-2019 For Display Breadcumb End
//print_r($breadcumbArr);die;
if (isset($serviceCategoriesTmp) && !empty($serviceCategoriesTmp)) {
    $service_categories = $serviceCategoriesTmp;
}
//echo "<pre>";print_r($service_categories);die;
$selectedServiceId = 1;
if (isset($_SESSION[$orderServiceSession]) && $_SESSION[$orderServiceSession] > 0) {
    $selectedServiceId = $_SESSION[$orderServiceSession];
}
//print_r($_SESSION[$orderServiceSession]);die;
//Added By HJ On 08-07-2019 For Hide/Show Order Address and Cart Icon End

$sql = "select vTitle, vCode, vCurrencyCode, eDefault from language_master where eStatus='Active' ORDER BY iDispOrder ASC";
$db_lng_mst = $obj->MySQLSelect($sql);
$count_lang = count($db_lng_mst);
$langCodeArr = array();
for ($l = 0; $l < $count_lang; $l++) {
    $langCodeArr[$db_lng_mst[$l]['vCode']] = $db_lng_mst[$l]['vTitle'];
}
$currency = "SELECT iCurrencyId,eDefault,vName FROM currency WHERE eStatus='Active' ORDER BY iDispOrder ASC";
$db_cur_mst = $obj->MySQLSelect($currency);
$count_cur = count($db_cur_mst);
$languageText = "LANGUAGE";
if (isset($langCodeArr[$_SESSION['sess_lang']])) {
    $languageText = $langCodeArr[$_SESSION['sess_lang']];
}

?>
<link href="https://fonts.googleapis.com/css?family=Montserrat:100,300,400,500,600,700" rel="stylesheet">
<header class="<? if ($_SESSION['sess_user'] != "") { ?>loggedin<?php } ?>">
    <div class="header-inner">
        <div class="header-left">
            <!--<div class="menu-icoholder">-->
            <!--    <i class="menu-ico">-->
            <!--        <span></span>-->
            <!--    </i>-->
            <!--</div>-->
            <? if ($_SESSION['sess_user'] != "") { ?>
            <div class="menu-icoholder-side">
                <i class="menu-ico">
                    <span></span>
                </i>
            </div>
            <?php }
            $url = explode('/',$_SERVER['REQUEST_URI']);
            $url_name = $url[count($url)-1];
            //print_R($url[count($url)-1]);
            //exit;
            ?>
            <div class="logo"><a href="index.php"><img src="assets/img/apptype/<?php echo $template;?>/logo.png" alt=""></a></div>
            <ul class="navmenu-links">
                <li class="has-level-menu">
                    <a href="#"><?= $langage_lbl['LBL_OUR_PRODUCTS']; ?></a>
                    <ul class="level-menu">
                        <li><a <? if($url_name=="earn") { ?> class="active" <?php } ?> href="earn"><? if($url_name=="earn") { ?><img src="assets/img/apptype/<?php echo $template;?>/mark.svg" alt=""><? } ?><?php echo  $langage_lbl['LBL_FOOTER_LINK_EARN']; ?></a></li>
                        <li><a <? if($url_name=="taxi") { ?> class="active" <?php } ?> href="taxi"><? if($url_name=="taxi") { ?><img src="assets/img/apptype/<?php echo $template;?>/mark.svg" alt=""><? } ?><?php echo  $langage_lbl['LBL_RIDE']; ?></a></li>
                         <li><a <? if($url_name=="moto") { ?> class="active" <?php } ?> href="moto"><? if($url_name=="moto") { ?><img src="assets/img/apptype/<?php echo $template;?>/mark.svg" alt=""><? } ?><?php echo  $langage_lbl['LBL_FOOTER_LINK_MOTO']; ?></a></li>
                         <li><a <? if($url_name=="delivery") { ?> class="active" <?php } ?> href="delivery"><? if($url_name=="delivery") { ?><img src="assets/img/apptype/<?php echo $template;?>/mark.svg" alt=""><? } ?><?php echo  $langage_lbl['LBL_PARCEL_DELIVERY']; ?></a></li>
                        <li><a <? if($url_name=="food") { ?> class="active" <?php } ?> href="food"><? if($url_name=="food") { ?><img src="assets/img/apptype/<?php echo $template;?>/mark.svg" alt=""><? } ?><?php echo  $langage_lbl['LBL_FOOTER_LINK_EAT']; ?></a></li>
                        <li><a <? if($url_name=="grocery") { ?> class="active" <?php } ?> href="grocery"><? if($url_name=="grocery") { ?><img src="assets/img/apptype/<?php echo $template;?>/mark.svg" alt=""><? } ?><?php echo  $langage_lbl['LBL_GROCERY_APP_DELIVERY']; ?></a></li>
                    </ul>
                </li>
                <li><a href="about"><?= $langage_lbl['LBL_ABOUT_US_HEADER_TXT']; ?></a></li>
                <li><a href="contact-us"><?= $langage_lbl['LBL_FOOTER_HOME_CONTACT_US_TXT']; ?></a></li>
            </ul>
        </div>
        <div class="header-right">
            <ul>
                <? //if ($_SESSION['sess_user'] != "") { ?>
                <!--<li><a href="cx-sign-in"><img class="cart" src="assets/img/apptype/<?php echo $template;?>/cart-icon.svg" alt=""/>5</a></li>-->

                
             
                <? //} ?> 
                
               
            
                <?php if ($fromOrder != "admin") { ?>
                <li class="lang">
                <?php if ($fromOrder != "admin") { ?>
                <div class="dynamic-data" style="display: none">
                    <select name="language" id="lang_select" onchange="change_lang(this.value);">
                        <?php
                        $srNo = 1;
                        foreach ($db_lng_mst as $key => $value) {
                            $totlLang = count($db_lng_mst);
                            $status_lang = "";
                            if ($_SESSION['sess_lang'] == $value['vCode']) {
                                $status_lang = "selected";
                            }
                            $addStyle = "";
                            if ($totlLang == $srNo && SITE_TYPE != "Demo") {
                                $addStyle = 'style="width:14.6%;"';
                            }
                            $srNo++;
                            ?>
                            <option <?php echo $status_lang; ?> value="<?php echo $value['vCode']; ?>"><?php echo ucfirst(strtolower($value['vTitle'])); ?></option>
                        <?php } ?>
                    </select>
                </div>
                <?php } ?>    
                <a href="#"><img src="assets/img/apptype/<?php echo $template;?>/globe.svg" alt=""/><?=$_SESSION['sess_lang']?></a>
                    <div class="dropdown-content">
                        <div class="row">
                            <h3>Select language</h3>
                            <?php $i = 0;
                            echo '<div class="column">'; 
                            foreach ($db_lng_mst as $key => $value) {
                                //if($i==4) $i = 0;
                                //if($i==0) ?>
                                <a href="javascript:void(0)" onclick="change_language('<?php echo $value['vCode']; ?>')" <?php if($value['vCode']==$_SESSION['sess_lang']) { ?> class="active" <? } ?>>
                                <?php if($value['vCode']==$_SESSION['sess_lang']) { ?><b><img src="assets/img/apptype/<?php echo $template;?>/mark.svg" alt=""/></b><?php } ?>
                                <?php echo ucfirst(strtolower($value['vTitle'])); ?></a>
                                <?php //if($i==3) echo '</div>'; $i++;
                                
                                ?>
                            <?php } echo '</div>';  ?>
                        </div>
                    </div>
                <!--</div>-->
                </li>
                <?php 
                if (!empty($_SESSION[$orderLatitudeSession]) && ($_SESSION[$userSession] == 'user' || $_SESSION[$userSession] == 'guest') || $_SESSION['sess_iAdminUserId'] > 0) {  ?>
            <?php if (in_array(1, $addressEnable)) { 
                
                ?>
                <!--<div class="location-element">-->
                     <!--<a href="javascript:;" onclick="window.location.href = 'order-items'"><strong><?= $_SESSION[$orderServiceNameSession]; ?></strong><span><?= $_SESSION[$orderAddressSession]; ?></span></a>-->
                            
                            <?php /* ?>
                            <strong><select name="servicename" id="servicename" onchange="resetServiceCatagory()"><?php
                            for ($s = 0; $s < count($service_categories); $s++) {
                                $iServiceId = $service_categories[$s]['iServiceId'];
                                $selectedTxt = "";
                                if ($selectedServiceId == $iServiceId) {
                                    $selectedTxt = "selected";
                                }
                                ?><option value="<?php echo $iServiceId; ?>" <?= $selectedTxt; ?> data-servicename="<?php echo ucfirst($service_categories[$s]['vServiceName']); ?>"><?php echo ucfirst($service_categories[$s]['vServiceName']); ?> </option><?php } ?></select></strong>
                            <?php */ ?>    

                                <!--<a href="javascript:;" onclick="window.location.href = 'order-items'"><span><?= $_SESSION[$orderAddressSession]; ?></span></a>-->
                <!--</div>-->
                <?php } else if ($orderItemCount > 0) { ?>
                <li>
                    <a href="<?= $orderItemListingUrl; ?>"><span class="deliveraddressIcon-clo-av"><span class="header_cart_icon"><img class="cart" src="assets/img/apptype/<?php echo $template;?>/cart-icon.svg" alt=""/></span><?= $orderItemCount; ?></span></a>
                </li>
                <!--<div class="header-right">
                   <a class="cart-element" href="<?= $orderItemListingUrl; ?>"><span class="deliveraddressIcon-clo-av"><span class="header_cart_icon"><img src="<?= $siteUrl; ?>assets/img/cart-icon.svg" class="deliveraddressIcon-clo" alt=""></span><?= $orderItemCount; ?></span></a>-->
                    <?php
                }
            }
            ?>
                <?php 
                } if ($_SESSION['sess_user'] != "") { ?>
                
                <li class="login"><a href="Logout"><img src="assets/img/apptype/<?php echo $template;?>/power.svg" alt=""/><?= $langage_lbl['LBL_HEADER_LOGOUT']; ?></a></li>
                <?php } else {

                    // if (strpos($_SERVER['REQUEST_URI'], 'cx-sign-in') !== false) {
                    //     $activeclass = "active";
                    // }else if(strpos($_SERVER['REQUEST_URI'], 'cx-sign-up') !== false){
                    //     $activeclass = "active";
                    // } else {
                    //     $activeclass = '';
                    // }
                    //print_R($_SERVER['REQUEST_URI']); exit;
                ?>
                
                <?php //if ($orderItemCount > 0) { ?>
                <!--<li>
                    <a href="<?= $orderItemListingUrl; ?>"><span class="deliveraddressIcon-clo-av"><span class="header_cart_icon"><img class="cart" src="assets/img/apptype/<?php echo $template;?>/cart-icon.svg" alt=""/></span><?= $orderItemCount; ?></span></a>
                </li>-->
                <?php //}  ?>

                <li class="login <?php if(strpos($_SERVER['REQUEST_URI'], 'sign-in')){echo 'active'; } ?>"><a href="sign-in"><img src="assets/img/apptype/<?php echo $template;?>/key.svg" alt=""/><?= $langage_lbl['LBL_HEADER_TOPBAR_SIGN_IN_TXT']; ?></a></li>
                <li class="login <?php if(strpos($_SERVER['REQUEST_URI'], 'sign-up-rider')){echo 'active'; } ?>"><a href="sign-up-rider"><img src="assets/img/apptype/<?php echo $template;?>/user.svg" alt=""><?= $langage_lbl['LBL_SIGNUP']; ?></a></li>
                <?php } ?>
            </ul>
        </div>
    </div>
</header>


<?php /* if (!empty($_SESSION[$orderLatitudeSession]) && ($_SESSION[$userSession] == 'user' || $_SESSION[$userSession] == 'guest') || $_SESSION['sess_iAdminUserId'] > 0) { ?>
        <?php if (in_array(1, $addressEnable)) { ?>
            <div class="page-contant-inner address-holder">                    
                <div class="">
                     <!--<a href="javascript:;" onclick="window.location.href = 'order-items'"><strong><?= $_SESSION[$orderServiceNameSession]; ?></strong><span><?= $_SESSION[$orderAddressSession]; ?></span></a>-->
            
                        <select name="servicename" id="servicename" onchange="resetServiceCatagory()"><?php
                            for ($s = 0; $s < count($service_categories); $s++) {
                                $iServiceId = $service_categories[$s]['iServiceId'];
                                $selectedTxt = "";
                                if ($selectedServiceId == $iServiceId) {
                                    $selectedTxt = "selected";
                                }
                                ?>
                            <option value="<?php echo $iServiceId; ?>" <?= $selectedTxt; ?> data-servicename="<?php echo ucfirst($service_categories[$s]['vServiceName']); ?>"><?php echo ucfirst($service_categories[$s]['vServiceName']); ?> </option><?php } ?>
                        </select>
                  
                        <a href="javascript:;" onclick="window.location.href = 'order-items'"><span><?= $_SESSION[$orderAddressSession]; ?></span></a>
                </div>
                </div>
                <?php } else if ($orderItemCount > 0) { ?>
            
                    <!-- <a href="<?= $orderItemListingUrl; ?>">
                        <span class="deliveraddressIcon-clo-av"><span class="header_cart_icon">
                            <img class="cart" src="assets/img/apptype/<?php echo $template;?>/cart-icon.svg" alt=""/></span><?= $orderItemCount; ?>
                        </span
                    ></a> -->
                
                    
<?php } } */ ?>


<script>
    function change_language(lang) {
        $('#lang_select').val(lang).trigger('change');
    }
    function change_curr(currency) {
        var request = $.ajax({
            type: "POST",
            url: 'ajax_fpass_action.php',
            data: {
                action: 'changecurrency',
                q: currency,
            },
            dataType: 'json',
            beforeSend: function ()
            {
                //alert(id);
            },
            success: function (data)
            {
                location.reload();
            }
        });
        request.fail(function (jqXHR, textStatus) {
            alert("Request failed: " + textStatus);
        });
    }
</script>
