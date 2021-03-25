<?php
	include_once('common.php');
	$generalobj->check_member_login();
	
	
	

?>
<!DOCTYPE html>
<html lang="en" dir="<?=(isset($_SESSION['eDirectionCode']) && $_SESSION['eDirectionCode'] != "")?$_SESSION['eDirectionCode']:'ltr';?>">
	<head>
		<meta charset="UTF-8">
		<meta name="viewport" content="width=device-width,initial-scale=1">
		<title><?=$SITE_NAME?> | <?=$langage_lbl['LBL_HEADER_PROFILE_TXT']; ?></title>
		<!--<link rel="stylesheet" href="templates/Cubex/assets/plugins/bootstrap/css/bootstrap.css" />-->
		<!--<link rel="stylesheet" href="assets/css/bootstrap-front.css" />-->
		<!-- Default Top Script and css -->
		<?php include_once("top/top_script.php");?>
		
		

		<link rel="stylesheet" href="assets/css/bootstrap-fileupload.min.css" >
		<link rel="stylesheet" href="assets/validation/validatrix.css" />
		

		<!-- End: Default Top Script and css-->
	</head>
	<body>
		<!-- home page -->
		<div id="main-uber-page">
			<!-- Left Menu -->
			<?php include_once("top/left_menu.php");?>
			<!-- End: Left Menu-->
			<!-- Top Menu -->
			<?php include_once("top/header_topbar.php");?>
			<!-- End: Top Menu-->
			<!-- contact page-->
			<section class="profile-section my-trips">
				<div class="profile-section-inner">
					<div class="profile-caption">
						<div class="page-heading">
							<h1>My Availability</h1>
						</div>
					</div>
				</div>
			</section>
			<section class="profile-earning">
				<div class="profile-earning-inner">
					<div class="general-form">
						<div class="card-block">
							<h1>Monday</h1>
							<div class="partation">
								<ul class="setings-list has-four-column middle-data has-background">
									<?php  
									for ($i = 1; $i <= 18; $i++) {
									echo '<li>
											<div class="toggle-list-inner">
												<div class="check-combo">
													<div class="check-main">
														<span class="check-hold small">
															<input id="'.$i.'" type="checkbox" name="other-option">
															<span class="check-button"></span>
														</span>
													</div><label>01 AM - 02 AM</label>
												</div>
											</div>
										 </li>';
									}
									?> 
								</ul>
							</div>
							<h1>Tuesday</h1>
							<div class="partation">
								<ul class="setings-list has-four-column middle-data has-background">
									<?php  
									for ($i = 1; $i <= 18; $i++) {
									echo '<li>
											<div class="toggle-list-inner">
												<div class="check-combo">
													<div class="check-main">
														<span class="check-hold small">
															<input id="'.$i.'" type="checkbox" name="other-option">
															<span class="check-button"></span>
														</span>
													</div><label>01 AM - 02 AM</label>
												</div>
											</div>
										 </li>';
									}
									?> 
								</ul>
							</div>
							<h1>Wednesday</h1>
							<div class="partation">
								<ul class="setings-list has-four-column middle-data has-background">
									<?php  
									for ($i = 1; $i <= 18; $i++) {
									echo '<li>
											<div class="toggle-list-inner">
												<div class="check-combo">
													<div class="check-main">
														<span class="check-hold small">
															<input id="'.$i.'" type="checkbox" name="other-option">
															<span class="check-button"></span>
														</span>
													</div><label>01 AM - 02 AM</label>
												</div>
											</div>
										 </li>';
									}
									?> 
								</ul>
							</div>
							<h1>Thursday</h1>
							<div class="partation">
								<ul class="setings-list has-four-column middle-data has-background">
									<?php  
									for ($i = 1; $i <= 18; $i++) {
									echo '<li>
											<div class="toggle-list-inner">
												<div class="check-combo">
													<div class="check-main">
														<span class="check-hold small">
															<input id="'.$i.'" type="checkbox" name="other-option">
															<span class="check-button"></span>
														</span>
													</div><label>01 AM - 02 AM</label>
												</div>
											</div>
										 </li>';
									}
									?> 
								</ul>
							</div>
							<h1>Friday</h1>
							<div class="partation">
								<ul class="setings-list has-four-column middle-data has-background">
									<?php  
									for ($i = 1; $i <= 18; $i++) {
									echo '<li>
											<div class="toggle-list-inner">
												<div class="check-combo">
													<div class="check-main">
														<span class="check-hold small">
															<input id="'.$i.'" type="checkbox" name="other-option">
															<span class="check-button"></span>
														</span>
													</div><label>01 AM - 02 AM</label>
												</div>
											</div>
										 </li>';
									}
									?> 
								</ul>
							</div>
							<h1>Saturday</h1>
							<div class="partation">
								<ul class="setings-list has-four-column middle-data has-background">
									<?php  
									for ($i = 1; $i <= 18; $i++) {
									echo '<li>
											<div class="toggle-list-inner">
												<div class="check-combo">
													<div class="check-main">
														<span class="check-hold small">
															<input id="'.$i.'" type="checkbox" name="other-option">
															<span class="check-button"></span>
														</span>
													</div><label>01 AM - 02 AM</label>
												</div>
											</div>
										 </li>';
									}
									?> 
								</ul>
							</div>
						</div>
						<div class="button-block justify-left">
							<input type="submit" class="gen-btn" name="submit" id="submit" value="Submit">
							<input type="submit" class="gen-btn" value="Cancel">
						</div>
					</div>
				</div>
			</section>
			<!-- footer part -->
			<?php include_once('footer/footer_home.php');?>
			<!-- footer part end -->
            <!-- -->
			<div  class="clearfix"></div>
		</div>
		<!-- home page end-->
		<!-- Footer Script -->
		<?php include_once('top/footer_script.php');
		$lang = get_langcode($_SESSION['sess_lang']);
		?>
		<style>
		.upload-error .help-block{
		    color:#b94a48;
		}
		</style>
		<script src="assets/plugins/jasny/js/bootstrap-fileupload.js"></script>
		<script type="text/javascript" src="assets/js/validation/jquery.validate.min.js" ></script>
		<?php if($lang != 'en') { ?>
			<? include_once('otherlang_validation.php');?>
		<!-- <script type="text/javascript" src="assets/js/validation/localization/messages_<?= $lang; ?>.js" ></script> -->
		<?php } ?>
		<script type="text/javascript" src="assets/js/validation/additional-methods.js" ></script>
		<!-- End: Footer Script -->
	</body>
</html>
