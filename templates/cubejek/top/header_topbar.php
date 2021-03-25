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

$logopath  ='';

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
<header class="<?if($_SESSION['sess_user']!=""){?>LOGGEDIN<?}else{?>LOGGEDOUT<?}?>">
    <div class="header-inner">
        <button id="navBtnShow" onClick="menuOpen()">
            <div></div>
            <div></div>
            <div></div>
        </button>
        <div class="header-left">
            <a href="index.php" class="logo_new"> <img src="assets/img/<?php echo $logo;?>" alt=""></a>
            <ul>
			 <?php if ($user == "") { ?>
				 <li><a href="about" class="<?= (isset($script) && $script == 'About Us') ? 'active' : ''; ?>"><?= $langage_lbl['LBL_ABOUT_US_HEADER_TXT']; ?></a></li>
				 <li><a href="contact-us" class="<?= (isset($script) && $script == 'Contact Us') ? 'active' : ''; ?>"><?= $langage_lbl['LBL_FOOTER_HOME_CONTACT_US_TXT']; ?></a></li>
				 <?php } else { ?>
				  <?php if ($user == 'rider') { ?>
					<?php if ($APP_TYPE == 'Ride-Delivery-UberX' || $APP_TYPE == 'UberX') { ?>
						<li><a href="profile-user" class="<?= (isset($script) && $script == 'Profile') ? 'active' : ''; ?>"><?= $langage_lbl['LBL_HEADER_TOPBAR_PROFILE_TITLE_TXT']; ?></a></li><li><a href="logout"><?= $langage_lbl['LBL_HEADER_LOGOUT']; ?></a></li>
					<?php } else { ?>
						<li><a href="profile-rider" class="<?= (isset($script) && $script == 'Profile') ? 'active' : ''; ?>"><?= $langage_lbl['LBL_HEADER_TOPBAR_PROFILE_TITLE_TXT']; ?></a></li><li><a href="logout"><?= $langage_lbl['LBL_HEADER_LOGOUT']; ?></a></li>
					<? } ?>
				<? } else if ($user == 'organization') { ?>
					<li><a href="organization-profile" class="<?= (isset($script) && $script == 'Organization-Profile') ? 'active' : ''; ?>"><?= $langage_lbl['LBL_HEADER_TOPBAR_PROFILE_TITLE_TXT']; ?></a></li><li>
					<a href="Organization-Logout"><?= $langage_lbl['LBL_HEADER_LOGOUT']; ?></a></li>
				<?php } else { ?>
					<li><a href="profile" class="<?= (isset($script) && $script == 'Profile') ? 'active' : ''; ?>"><?= $langage_lbl['LBL_HEADER_TOPBAR_PROFILE_TITLE_TXT']; ?></a></li><li>
					<a href="logout"><?= $langage_lbl['LBL_HEADER_LOGOUT']; ?></a></li>
				<? } ?>
				 <?php } ?>
            </ul>
        </div>
		 
        <div class="dynamic-data">
            <select name="language" onchange="change_lang(this.value);">
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
               <?php }?>
            </select>
        </div>
		<?php if ($user == "") { ?>
        <div class="header-right">
            <ul>
                <li><a href="help-center" class="<?= (isset($script) && $script == 'Help Center') ? 'active' : ''; ?>"><?= $langage_lbl['LBL_HEADER_HELP_TXT']; ?></a></li>
				    
                        
				
                <li><a href="sign-in"  class="unic-btn <?php echo strstr($_SERVER['SCRIPT_NAME'], '/sign-in') || strstr($_SERVER['SCRIPT_NAME'], '/login-new') ? 'active' : '' ?>"><?= $langage_lbl['LBL_HEADER_TOPBAR_SIGN_IN_TXT']; ?></a></li>
            </ul>
        </div>
		<?php } ?>
    </div>
</header>
<script>
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