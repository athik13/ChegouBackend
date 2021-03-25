<?php
	if ($_SESSION['sess_user'] == 'company') {
		$sql = "select * from company where iCompanyId = '" . $_SESSION['sess_iUserId'] . "'";
		$db_user = $obj->MySQLSelect($sql);
	}
	if ($_SESSION['sess_user'] == 'driver') {
		$sql = "select * from register_driver where iDriverId = '" . $_SESSION['sess_iUserId'] . "'";
		$db_user = $obj->MySQLSelect($sql);
	}
	if ($_SESSION['sess_user'] == 'rider'){
		$sql = "select * from register_user where iUserId = '".$_SESSION['sess_iUserId']."'";
		$db_user = $obj->MySQLSelect($sql);
	}
	$col_class = "";
	if($user != "") { 
		$col_class = "top-inner-color";
	}

$logo = "logo.png";
if(isset($data[0]['BannerBgImage']) && (!empty($data[0]['BannerBgImage']))) {?>
<style>
.top-part-inner-home {
  background: url('<?=$tconfig["tsite_upload_page_images"]."home/".$data[0]['BannerBgImage'];?>') no-repeat scroll top center;
}
</style>
<?php } ?>
<?php if($script == 'Home'){ ?>
	<!-- top part -->
	<div class="top-part-home <?=$col_class;?>">
		<div class="top-part-inner-home">
		  <?php $logoName = strstr($_SERVER['SCRIPT_NAME'],'/') && strstr($_SERVER['SCRIPT_NAME'],'/index.php')?'logo.png':'logo-inner.png' ;?>
		  <div class="top-logo-menu-part">
		      <div class="top-logo-menu-part-inner">
			        <?php if($user=="") { ?>
			          	<div class="logo">
				            <a href="index.php"><img src="assets/img/<?php echo $logo;?>" alt=""></a>

				            <span class="top-logo-link" ><a href="about" class="<?=(isset($script) && $script == 'About Us')?'active':'';?>"><?=$langage_lbl['LBL_ABOUT_US_HEADER_TXT']; ?></a><a href="contact-us" class="<?=(isset($script) && $script == 'Contact Us')?'active':'';?>"><?=$langage_lbl['LBL_FOOTER_HOME_CONTACT_US_TXT']; ?></a></span>
			          	</div>
				        <div class="menu-part">
				            <ul>
				              <?php if(isset($_REQUEST['edit_lbl'])){ ?>
				                <li>
				                  <a href="help-center" class="<?=(isset($script) && $script == 'Help Center')?'active':'';?>"><?=$langage_lbl['LBL_HEADER_HELP_TXT'];?></a>
				                </li>
				                <li>
				                  <a href="sign-in"  class="<?php echo strstr($_SERVER['SCRIPT_NAME'],'/sign-in') || strstr($_SERVER['SCRIPT_NAME'],'/login-new')?'active':'' ?>"><?=$langage_lbl['LBL_HEADER_TOPBAR_SIGN_IN_TXT'];?></a>
				                </li>
				               <?php } else {?>
				                <li>
				                  <a href="help-center" class="<?=(isset($script) && $script == 'Help Center')?'active':'';?>"><?=$langage_lbl['LBL_HEADER_HELP_TXT'];?></a>
				                </li>
				                <li>
				                  <a href="sign-in"  class="<?php echo strstr($_SERVER['SCRIPT_NAME'],'/sign-in') || strstr($_SERVER['SCRIPT_NAME'],'/login-new')?'active':'' ?>"><?=$langage_lbl['LBL_HEADER_TOPBAR_SIGN_IN_TXT'];?></a>
				                </li>
				               <?php }?>
				            </ul>
				        </div>
				    <?php } else { ?>
				    	<?php if($user != "") { 
				    		if (($db_user[0]['vImage'] == 'NONE' || $db_user[0]['vImage'] == '') && ($db_user[0]['vImgName'] == 'NONE' || $db_user[0]['vImgName'] == ''))  {
					          $img_url = "assets/img/profile-user-img.png";
					        } else {
					          	if($_SESSION['sess_user'] == 'company') {
						            $img_path = $tconfig["tsite_upload_images_compnay"];
						            $img_url = $img_path . '/' . $_SESSION['sess_iUserId'] . '/2_' . $db_data[0]['vImage'];
					            } else if($_SESSION['sess_user'] == 'driver') {
						            $img_path = $tconfig["tsite_upload_images_driver"];
						            $img_url = $img_path . '/' . $_SESSION['sess_iUserId'] . '/2_' . $db_data[0]['vImage'];
					            } else {
						            $img_path = $tconfig["tsite_upload_images_passenger"];
						            $img_url = $img_path . '/' . $_SESSION['sess_iUserId'] . '/2_' . $db_data[0]['vImgName'];
					          	}  
					        } ?>
					        <div class="logo">
					            <a href="index.php"><img src="assets/img/<?php echo $logo; ?>" alt=""></a>
					            <?php if($user == 'driver'){ ?>
						          <span class="top-logo-link" ><a href="profile" class="<?=(isset($script) && $script == 'Dashboard')?'active':'';?>"><?=$langage_lbl['LBL_HEADER_TOPBAR_PROFILE_TITLE_TXT'];?></a><a href="logout"><?=$langage_lbl['LBL_HEADER_LOGOUT']; ?></a></span>
						        <?php } else { ?>
						          <span class="top-logo-link" ><a href="dashboard" class="<?=(isset($script) && $script == 'Dashboard')?'active':'';?>"><?=$langage_lbl['LBL_HEADER_TOPBAR_PROFILE_TITLE_TXT'];?></a><a href="logout"><?=$langage_lbl['LBL_HEADER_LOGOUT']; ?></a></span>
						        <?php } ?>
				          	</div>
				          	<div class="top-link-login-new">
					          <div class="user-part-login">
					            <b><img src="<?= $img_url ?>" alt=""></b>
					            <div class="top-link-login-hold">
													<div class="top-link-login">
															<label><img src="assets/img/arrow-menu.png" alt=""></label>
															<ul>
																	<?php if($user == 'driver'){ ?>
																		<li><a href="profile" class="<?=(isset($script) && $script == 'Profile')?'active':'';?>"><i class="fa fa-user" aria-hidden="true"></i><?=$langage_lbl['LBL_HEADER_TOPBAR_PROFILE_TITLE_TXT'];?></a></li>

																		<li><a href="logout"><i class="fa fa-power-off" aria-hidden="true"></i><?=$langage_lbl['LBL_HEADER_LOGOUT']; ?></a></li>
																	<?php } else if($user == 'company'){ ?>
																		<li><a href="dashboard" class="<?=(isset($script) && $script == 'Dashboard')?'active':'';?>"><i class="fa fa-user" aria-hidden="true"></i><?=$langage_lbl['LBL_HEADER_TOPBAR_PROFILE_TITLE_TXT'];?></a></li>

																		<li><a href="logout"><i class="fa fa-power-off" aria-hidden="true"></i><?=$langage_lbl['LBL_HEADER_LOGOUT']; ?></a></li>
																	<?php } else if($user == 'rider') { ?>
																		<li><a href="profile-rider" class="<?=(isset($script) && $script == 'Profile')?'active':'';?>"><i class="fa fa-user" aria-hidden="true"></i><?=$langage_lbl['LBL_HEADER_TOPBAR_PROFILE_TITLE_TXT'];?></a></li>
																		<li><a href="logout"><i class="fa fa-power-off" aria-hidden="true"></i><?=$langage_lbl['LBL_HEADER_LOGOUT']; ?></a></li>
																	<?php } ?>
															</ul>
													</div>
											</div>
					          </div>
					        </div>
				    	<?php } ?>
				    <?php } ?>
		      </div>
		  </div>

		  <div class="banner-text">
		    <div class="banner-text-inner">
		      <h1>
		      	<?if(!empty($data[0]['BannerBigTitle'])) { 
		      		echo $data[0]['BannerBigTitle']; 
		      	} else {
		      		echo 'FOOD DELIVERY';
		      	} ?>
		      	<b>
		      		<?if(!empty($data[0]['BannerSmallTitle'])) { 
		      			echo $data[0]['BannerSmallTitle']; 
		      		} else {
		      			echo 'ON DEMAND';
		      		}?>
		      	</b>
		      </h1>
		      <?if(!empty($data[0]['BannerContent'])) { 
		      		echo $data[0]['BannerContent']; 
		      	} else { 
		      			echo '<p>Satisfy your cravings with CubeFood with a culinary experience that beats any five-star restaurants<span><a href="about">Read More</a></span></p>';
		      		} ?>
		      
		      <div style="clear:both;"></div>
		    </div>
		  </div>

		</div>
	</div>
<?php } else { ?>
		<!-- top part -->
	<div class="top-part <?=$col_class;?>" id="top-part">
		<div class="top-part-inner">
		  <?php $logoName = strstr($_SERVER['SCRIPT_NAME'],'/') && strstr($_SERVER['SCRIPT_NAME'],'/index.php')?'logo.png':'logo-inner.png' ;?>
		  <div class="top-logo-menu-part">
		      <div class="top-logo-menu-part-inner">
			        <?php if($user=="") { ?>
			          	<div class="logo">
				            <a href="index.php"><img src="assets/img/<?php echo $logo;?>" alt=""></a>

				            <span class="top-logo-link" ><a href="about" class="<?=(isset($script) && $script == 'About Us')?'active':'';?>"><?=$langage_lbl['LBL_ABOUT_US_HEADER_TXT']; ?></a><a href="contact-us" class="<?=(isset($script) && $script == 'Contact Us')?'active':'';?>"><?=$langage_lbl['LBL_FOOTER_HOME_CONTACT_US_TXT']; ?></a></span>
			          	</div>
				        <div class="menu-part">
				            <ul>
				              <?php if(isset($_REQUEST['edit_lbl'])){ ?>
				                <li>
				                  <a href="help-center" class="<?=(isset($script) && $script == 'Help Center')?'active':'';?>"><?=$langage_lbl['LBL_HEADER_HELP_TXT'];?></a>
				                </li>
				                <li>
				                  <a href="sign-in"  class="<?php echo strstr($_SERVER['SCRIPT_NAME'],'/sign-in') || strstr($_SERVER['SCRIPT_NAME'],'/login-new')?'active':'' ?>"><?=$langage_lbl['LBL_HEADER_TOPBAR_SIGN_IN_TXT'];?></a>
				                </li>
				               <?php } else {?>
				                <li>
				                  <a href="help-center" class="<?=(isset($script) && $script == 'Help Center')?'active':'';?>"><?=$langage_lbl['LBL_HEADER_HELP_TXT'];?></a>
				                </li>
				                <li>
				                  <a href="sign-in"  class="<?php echo strstr($_SERVER['SCRIPT_NAME'],'/sign-in') || strstr($_SERVER['SCRIPT_NAME'],'/login-new')?'active':'' ?>"><?=$langage_lbl['LBL_HEADER_TOPBAR_SIGN_IN_TXT'];?></a>
				                </li>
				               <?php }?>
				            </ul>
				        </div>
				    <?php } else { ?>
				    	<?php if($user != "") { 
				    		if (($db_user[0]['vImage'] == 'NONE' || $db_user[0]['vImage'] == '') && ($db_user[0]['vImgName'] == 'NONE' || $db_user[0]['vImgName'] == ''))  {
					          $img_url = "assets/img/profile-user-img.png";
					        } else {
					          	if($_SESSION['sess_user'] == 'company') {
						            $img_path = $tconfig["tsite_upload_images_compnay"];
						            $img_url = $img_path . '/' . $_SESSION['sess_iUserId'] . '/2_' . $db_data[0]['vImage'];
					            } else if($_SESSION['sess_user'] == 'driver') {
						            $img_path = $tconfig["tsite_upload_images_driver"];
						            $img_url = $img_path . '/' . $_SESSION['sess_iUserId'] . '/2_' . $db_data[0]['vImage'];
					            } else {
						            $img_path = $tconfig["tsite_upload_images_passenger"];
						            $img_url = $img_path . '/' . $_SESSION['sess_iUserId'] . '/2_' . $db_data[0]['vImgName'];
					          	}  
					        } ?>
					        <div class="logo">
					            <a href="index.php"><img src="assets/img/<?php echo $logo; ?>" alt=""></a>
					            <?php if($user == 'driver'){ ?>
						          <span class="top-logo-link" ><a href="profile" class="<?=(isset($script) && $script == 'Dashboard')?'active':'';?>"><?=$langage_lbl['LBL_HEADER_TOPBAR_PROFILE_TITLE_TXT'];?></a><a href="logout"><?=$langage_lbl['LBL_HEADER_LOGOUT']; ?></a></span>
						        <?php } else { ?>
						          <span class="top-logo-link" ><a href="dashboard" class="<?=(isset($script) && $script == 'Dashboard')?'active':'';?>"><?=$langage_lbl['LBL_HEADER_TOPBAR_PROFILE_TITLE_TXT'];?></a><a href="logout"><?=$langage_lbl['LBL_HEADER_LOGOUT']; ?></a></span>
						        <?php } ?>
				          	</div>
				          	<div class="top-link-login-new">
					          <div class="user-part-login">
											<b><img src="<?= $img_url ?>" alt=""></b>
											<div class="top-link-login-hold">
													<div class="top-link-login">
															<label><img src="assets/img/arrow-menu.png" alt=""></label>
															<ul>
																	<?php if($user == 'driver'){ ?>
																		<li><a href="profile" class="<?=(isset($script) && $script == 'Profile')?'active':'';?>"><i class="fa fa-user" aria-hidden="true"></i><?=$langage_lbl['LBL_HEADER_TOPBAR_PROFILE_TITLE_TXT'];?></a></li>

																		<li><a href="logout"><i class="fa fa-power-off" aria-hidden="true"></i><?=$langage_lbl['LBL_HEADER_LOGOUT']; ?></a></li>
																	<?php } else if($user == 'company'){ ?>
																		<li><a href="dashboard" class="<?=(isset($script) && $script == 'Dashboard')?'active':'';?>"><i class="fa fa-user" aria-hidden="true"></i><?=$langage_lbl['LBL_HEADER_TOPBAR_PROFILE_TITLE_TXT'];?></a></li>

																		<li><a href="logout"><i class="fa fa-power-off" aria-hidden="true"></i><?=$langage_lbl['LBL_HEADER_LOGOUT']; ?></a></li>
																	<?php } else if($user == 'rider') { ?>
																		<li><a href="profile-rider" class="<?=(isset($script) && $script == 'Profile')?'active':'';?>"><i class="fa fa-user" aria-hidden="true"></i><?=$langage_lbl['LBL_HEADER_TOPBAR_PROFILE_TITLE_TXT'];?></a></li>
																		<li><a href="logout"><i class="fa fa-power-off" aria-hidden="true"></i><?=$langage_lbl['LBL_HEADER_LOGOUT']; ?></a></li>
																	<?php } ?>
															</ul>
													</div>
											</div>
					          </div>
					        </div>
				    	<?php } ?>
				    <?php } ?>
		      </div>
		  </div>
		</div>
	</div>
<?php } ?>
<div style="clear:both;"></div>
