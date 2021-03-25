<?php
$sql="select vLabel,vValue from language_label where vCode='".$_SESSION['sess_lang']."'";
$db_lbl=$obj->MySQLSelect($sql);
//echo '<pre>';print_r($db_lbl);exit;
foreach ($db_lbl as $key => $value) {
		$vLabel=$value['vLabel'];
		$$vLabel=$value['vValue'];
}

?>
<section class="header">

	<div class="container">
	<a href="index.php"><img class="logo" src="<?=$tconfig["tsite_home_images"]?>logo.png" alt=""></a>
    <div class="top-right-part">
    <div class="login">
		<span>
			<a href="login-signup.php" class="login-signup"><?echo $LBL_SIGN_UP;?></a>
		</span>
		<span>
			<a href="sign-in.php" class="login-signup"><?echo $LBL_SIGN_IN_TXT;?></a>
		</span>
		</div>
	<div class="lang">
		<span>
			<select name="sess_language" id="sess_language" onchange="change_lang(this.value);">
					<?php
					$sql="select vTitle, vCode, vCurrencyCode, eDefault from language_master where eStatus='Active'";
					$db_lng_mst=$obj->MySQLSelect($sql);
					foreach ($db_lng_mst as $key => $value) {
						echo '
							<option value="'.$value['vCode'].'"'.($_SESSION['sess_lang']==$value['vCode']?'selected':'').'>'.$value['vTitle'].'</option>
						';
					}
					?>
			</select>
		</span>
	</div>
    </div>
	</div>
</section>
<script>
function change_lang(lang){
	document.location='common.php?lang='+lang;
}
</script>
