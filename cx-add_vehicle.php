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
							<h1>Add Vehicle</h1>
						</div>
						<div class="button-block end">
							<a href="#" class="gen-btn">Back To My Taxi Listing</a>
						</div>
					</div>
				</div>
			</section>
			<section class="profile-earning">
				<div class="profile-earning-inner">
					<div class="general-form">
						<div class="card-block">
							<h1>Vehicle Details</h1>
							<div class="partation">
								<div class="form-group half">
									<strong>Select Make<span class="red">*</span></strong>
									<select required>
										<option value="">Select Make</option>
										<option value="1" >ACURA</option>
										<option value="2" >ALFA ROMEO</option>
										<option value="3" >AM GENERAL</option>
										<option value="4" >AMERICAN IRONHORSE</option>
										<option value="5" >APRILIA</option>
										<option value="6" >ARCTIC CAT</option>
										<option value="7" >ASTON MARTIN</option>
										<option value="8" >ATK</option>
										<option value="9" >AUDI</option>
										<option value="10" >AUTOCAR LLC.</option>
										<option value="11" >AVANTI</option>
										<option value="12" >BENTLEY</option>
										<option value="13" >BIG DOG</option>
										<option value="14" >BIMOTA</option>
										<option value="15" >BLUE BIRD</option>
										<option value="16" >BMW</option>
										<option value="17" >BOMBARDIER</option>
										<option value="18" >BUELL</option>
										<option value="19" >BUICK</option>
										<option value="20" >CADILLAC</option>
										<option value="21" >CANNONDALE</option>
										<option value="22" >CHANCE COACH TRANSIT BUS</option>
										<option value="23" >CHEVROLET</option>
										<option value="24" >CHRYSLER</option>
										<option value="25" >COBRA</option>
										<option value="26" >DAEWOO</option>
										<option value="27" >DODGE</option>
										<option value="28" >DUCATI</option>
										<option value="29" >E-TON</option>
										<option value="30" >EL DORADO</option>
										<option value="31" >FERRARI</option>
										<option value="32" >FORD</option>
										<option value="33" >FREIGHTLINER</option>
										<option value="34" >GAS GAS</option>
										<option value="35" >GILLIG</option>
										<option value="36" >GMC</option>
										<option value="37" >HARLEY DAVIDSON</option>
										<option value="38" >HINO</option>
										<option value="39" >HM</option>
										<option value="40" >HONDA</option>
										<option value="41" >HUSABERG</option>
										<option value="42" >HUSQVARNA</option>
										<option value="43" >HYUNDAI</option>
										<option value="44" >INDIAN</option>
										<option value="45" >INFINITI</option>
										<option value="46" >INTERNATIONAL</option>
										<option value="47" >ISUZU</option>
										<option value="48" >JAGUAR</option>
										<option value="49" >JEEP</option>
										<option value="50" >KAWASAKI</option>
										<option value="51" >KENWORTH</option>
										<option value="52" >KIA</option>
										<option value="54" >KYMCO</option>
										<option value="55" >LAFORZA</option>
										<option value="56" >LAMBORGHINI</option>
										<option value="57" >LAND ROVER</option>
										<option value="58" >LEXUS</option>
										<option value="59" >LINCOLN</option>
										<option value="60" >LOTUS</option>
										<option value="61" >MACK</option>
										<option value="62" >MAZDA</option>
										<option value="63" >MERCEDES-BENZ</option>
										<option value="64" >MERCURY</option>
										<option value="65" >MITSUBISHI</option>
										<option value="66" >MITSUBISHI FUSO</option>
										<option value="67" >MOTO GUZZI</option>
										<option value="68" >MOTOR COACH INDUSTRIES</option>
										<option value="69" >MV AGUSTA</option>
										<option value="70" >NEW FLYER</option>
										<option value="71" >NISSAN</option>
										<option value="72" >NOVA BUS CORPORATION</option>
										<option value="73" >OLDSMOBILE</option>
										<option value="74" >ORION BUS</option>
										<option value="75" >OSHKOSH MOTOR TRUCK CO.</option>
										<option value="76" >OTTAWA</option>
										<option value="77" >PANOZ</option>
										<option value="78" >PETERBILT</option>
										<option value="79" >PEUGEOT</option>
										<option value="80" >PLYMOUTH</option>
										<option value="81" >POLARIS</option>
										<option value="82" >PONTIAC</option>
										<option value="83" >PORSCHE</option>
										<option value="84" >QVALE</option>
										<option value="85" >RENAULT</option>
										<option value="86" >ROLLS ROYCE</option>
										<option value="88" >SATURN</option>
										<option value="89" >SEA-DOO</option>
										<option value="90" >SEAT</option>
										<option value="91" >SKI-DOO</option>
										<option value="92" >STERLING</option>
										<option value="93" >STERLING TRUCK</option>
										<option value="94" >SUBARU</option>
										<option value="95" >SUZUKI</option>
										<option value="96" >TM</option>
										<option value="97" >TOYOTA</option>
										<option value="98" >TRIUMPH</option>
										<option value="100" >VICTORY</option>
										<option value="101" >VOLKSWAGEN</option>
										<option value="102" >VOLVO</option>
										<option value="103" >WESTERN STAR</option>
										<option value="104" >WORKHORSE</option>
										<option value="105" >YAMAHA</option>
									</select>
								</div>
								<div class="form-group half">
									<strong>Select Vehicle Model<span class="red">*</span></strong>
									<select required>
										<option value="">Select Vehicle Model</option>
									</select>
								</div>
								<div class="form-group half">
									<strong>Select Year<span class="red">*</span></strong>
									<select required>
										<option value="">Select Year </option>
										<option value="2019" >2019</option>
										<option value="2018" >2018</option>
										<option value="2017" >2017</option>
										<option value="2016" >2016</option>
										<option value="2015" >2015</option>
										<option value="2014" >2014</option>
										<option value="2013" >2013</option>
										<option value="2012" >2012</option>
										<option value="2011" >2011</option>
										<option value="2010" >2010</option>
										<option value="2009" >2009</option>
										<option value="2008" >2008</option>
										<option value="2007" >2007</option>
										<option value="2006" >2006</option>
										<option value="2005" >2005</option>
										<option value="2004" >2004</option>
										<option value="2003" >2003</option>
										<option value="2002" >2002</option>
										<option value="2001" >2001</option>
										<option value="2000" >2000</option>
										<option value="1999" >1999</option>
										<option value="1998" >1998</option>
										<option value="1997" >1997</option>
										<option value="1996" >1996</option>
										<option value="1995" >1995</option>
										<option value="1994" >1994</option>
										<option value="1993" >1993</option>
										<option value="1992" >1992</option>
										<option value="1991" >1991</option>
										<option value="1990" >1990</option>
										<option value="1989" >1989</option>
										<option value="1988" >1988</option>
										<option value="1987" >1987</option>
										<option value="1986" >1986</option>
										<option value="1985" >1985</option>
										<option value="1984" >1984</option>
										<option value="1983" >1983</option>
										<option value="1982" >1982</option>
										<option value="1981" >1981</option>
										<option value="1980" >1980</option>
										<option value="1979" >1979</option>
										<option value="1978" >1978</option>
										<option value="1977" >1977</option>
										<option value="1976" >1976</option>
										<option value="1975" >1975</option>
										<option value="1974" >1974</option>
										<option value="1973" >1973</option>
										<option value="1972" >1972</option>
										<option value="1971" >1971</option>
										<option value="1970" >1970</option>
									</select>
								</div>
								<div class="form-group half">
									<strong>&nbsp;</strong>
									<div class="relation-parent">
										<label>Licence Plate<span class="red">*</span></label>
										<input type="text" name="" autocomplete="off">
									</div>
								</div>
								<div class="form-group half">
									<label>Vehicle Color<span class="red">*</span></label>
									<input type="text" name="" autocomplete="off">
								</div>
							</div>
						</div>
						<div class="card-block">
							<h1>Car Types</h1>
							<div class="partation">
								<ul class="setings-list">
									<?php  
									for ($i = 1; $i <= 18; $i++) {
									echo '<li>
											<div class="toggle-list-inner">
												<div class="toggle-combo">
													<label>Cargo Car (Delivery)<small>( Location : Vtype)</small> </label>
													<span class="toggle-switch">
														<input id="'.$i.'handycapt" type="checkbox">
														<span class="toggle-base"></span>
													</span>
												</div>
												<div class="check-combo">
													<div class="check-main">
														<span class="check-hold small">
															<input id="'.$i.'" type="checkbox" name="other-option">
															<span class="check-button"></span>
														</span>
													</div><label>Accept rental request for Luxurious vehicle type?</label>
												</div>
											</div>
										 </li>';
									}
									?> 
								</ul>
							</div>
							<h1>DeliverAll Vehicle Type <small>(Choose only one vehicle)</small></h1>
							<div class="partation">
								<ul class="setings-list">
									<?php  
									for ($i = 19; $i <= 21; $i++) {
									echo '<li>
												<div class="toggle-combo">
													<label>Cargo Car (Delivery)<small>( Location : Vtype)</small> </label>
													<span class="toggle-switch">
														<input id="'.$i.'handycapt" name="delivery_vehicle" type="radio">
														<span class="toggle-base"></span>
													</span>
												</div>
											</li>';
									}
									?> 
								</ul>
							</div>
						</div>
						<div class="card-block">
							<h1>Settings</h1>
							<div class="partation">
								<ul class="setings-list">
									<li>
										<div class="toggle-combo">
											<label>Handicap accessibility available?</label>
											<span class="toggle-switch">
												<input id="handycapt" type="checkbox">
												<span class="toggle-base"></span>
											</span>
										</div>
									</li>
									<li>
										<div class="toggle-combo">
											<label>Child Seat available?</label>
											<span class="toggle-switch">
												<input id="child-seat" type="checkbox">
												<span class="toggle-base"></span>
											</span>
										</div>
									</li>
									<li>
										<div class="toggle-combo">
											<label>Ladies Driver</label>
											<span class="toggle-switch">
												<input id="ladies-driver" type="checkbox">
												<span class="toggle-base"></span>
											</span>
										</div>
									</li>
								</ul>
							</div>
						</div>
						<div class="button-block justify-left">
							<input type="submit" class="save-vehicle gen-btn" name="submit" id="submit" value="Add Vehicle">
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
		$lang = get_langcode($_SESSION['sess_lang']);?>
		<style>
		.upload-error .help-block{
		    color:#b94a48;
		}
		</style>
		<script src="assets/plugins/jasny/js/bootstrap-fileupload.js"></script>
		<script type="text/javascript" src="assets/js/validation/jquery.validate.min.js" ></script>
		<?php if($lang != 'en') { ?>
		<!--<script type="text/javascript" src="assets/js/validation/localization/messages_<?= $lang; ?>.js" ></script>-->
		<? include_once('otherlang_validation.php');?>
		<?php } ?>
		<script type="text/javascript" src="assets/js/validation/additional-methods.js" ></script>
		<!-- End: Footer Script -->
	</body>
</html>
