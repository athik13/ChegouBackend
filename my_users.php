<?php
include_once('common.php');

//added by SP for cubex changes on 07-11-2019
if($generalobj->checkXThemOn() == 'Yes') {
        include_once("cx-my_users.php");
        exit;
}

$generalobj->check_member_login();
$abc = 'organization';
$url = $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
$generalobj->setRole($abc, $url);

/*error_reporting(-1);
error_reporting(E_ALL);  
ini_set('display_errors','1');*/

$id = isset($_REQUEST['id']) ? $_REQUEST['id'] : '';
$hdn_del_id = isset($_REQUEST['hdn_del_id']) ? $_REQUEST['hdn_del_id'] : '';
$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : 'view';
$iUserProfileId = isset($_REQUEST['iUserProfileId']) ? $_REQUEST['iUserProfileId'] : '';
$status = isset($_REQUEST['status']) ? $_REQUEST['status'] : '';
$var_msg = isset($_REQUEST["var_msg"]) ? $_REQUEST["var_msg"] : '';
$iOrganizationId = $_SESSION['sess_iOrganizationId'];

$sql = "SELECT * FROM country WHERE eStatus = 'Active'";
$db_country = $obj->MySQLSelect($sql);

$sql = "SELECT * FROM language_master WHERE eStatus = 'Active'";
$db_lang = $obj->MySQLSelect($sql);
$script = 'MyUsers';

/*if ($action == 'delete') {
	if(SITE_TYPE != 'Demo')
	{
		$query = "UPDATE register_driver SET eStatus = 'Deleted' WHERE iDriverId = '" . $hdn_del_id . "'";
		$obj->sql_query($query);
		$var_msg = $langage_lbl['LBL_COMPNAY_FRONT_DELETE_TEXT'];
		header("Location:driver.php?success=1&var_msg=". $var_msg);
		exit();
	} else {
		header("Location:driver.php?success=2");
		exit();
	}
}*/

if ($action == 'changeStatusAction') {

	/*if(SITE_TYPE != 'Demo')
	{*/
		$query = "UPDATE user_profile  SET eStatus = '".$status."'  WHERE iUserProfileId = '" .$iUserProfileId . "'";
		$obj->sql_query($query);
		$userEmailData  =  getUserInfoForMail($iUserProfileId);

		$maildata['User_Name'] =$userEmailData[0]['name'];
		$maildata['User_Profile_Email'] =$userEmailData[0]['vProfileEmail'];
		$maildata['User_Email'] =$userEmailData[0]['vEmail'];
		$maildata['Organization_Name'] =$userEmailData[0]['vCompany'];
		$maildata['Profile_Status'] =$userEmailData[0]['eStatus'];
		$maildata['Company_Name'] =$COMPANY_NAME;
		$generalobj->send_email_user("ORGANIZATION_UPDATE_USERPROFILESTATUS_TO_USER",$maildata);

		//$var_msg = $langage_lbl['LBL_COMPNAY_FRONT_DELETE_TEXT'];
		$var_msg = "Status Update Successfully";
		header("Location:my_users.php?success=1&var_msg=". $var_msg);
		exit();
	/*} else {
		header("Location:my_users.php?success=2");
		exit();
	}*/
}

$vName = isset($_POST['vName']) ? $_POST['vName'] : '';
$vLname = isset($_POST['vLname']) ? $_POST['vLname'] : '';
$vEmail = isset($_POST['vEmail']) ? $_POST['vEmail'] : '';
$vPassword = isset($_POST['vPassword']) ? $_POST['vPassword'] : '';
$vPhone = isset($_POST['vPhone']) ? $_POST['vPhone'] : '';
$vCode = isset($_POST['vCode']) ? $_POST['vCode'] : '';
$vCountry = isset($_POST['vCountry']) ? $_POST['vCountry'] : '';
$vLang = isset($_POST['vLang']) ? $_POST['vLang'] : '';
$vPass = $generalobj->encrypt($vPassword);
$eStatus = isset($_POST['eStatus']) ? $_POST['eStatus'] : '';
$tbl_name = "register_driver";

/*if (isset($_POST['submit'])) {
	$q = "INSERT INTO ";
	$where = '';
	
	if ($action == 'Edit') {
		$eStatus = ", eStatus = 'Inactive' ";
	} else {
		$eStatus = '';
	}
	
	if ($id != '') {
		$q = "UPDATE ";
		$where = " WHERE `iDriverId` = '" . $id . "'";
	}
	
	$query = $q . " `" . $tbl_name . "` SET
    `vName` = '" . $vName . "',
    `vLastName` = '" . $vLname . "',
    `vCountry` = '" . $vCountry . "',
    `vCode` = '" . $vCode . "',
    `vEmail` = '" . $vEmail . "',
    `vLoginId` = '" . $vEmail . "',
    `vPassword` = '" . $vPass . "',
    `vPhone` = '" . $vPhone . "',
    `vLang` = '" . $vLang . "',
    `eStatus` = '" . $eStatus . "',
    `iCompanyId` = '" . $iCompanyId . "'" . $where;
	$obj->sql_query($query);

	$id = ($id != '') ? $id : $obj->GetInsertId();
	if(SITE_TYPE != 'Demo'){
		if ($action == 'Edit') {
			$var_msg = $langage_lbl['LBL_COMPNAY_FRONT_UPDATE_DRIVER_TEXT'];
			header("Location:driver.php?id=" . $id . "&success=1&var_msg=". $var_msg);
			exit;
      	} else {
      		$var_msg = $langage_lbl['LBL_COMPNAY_FRONT_ADD_DRIVER_TEXT'];
			header("Location:driver.php?id=" . $id . "&success=1&var_msg=". $var_msg);
			exit;
	    }
	} else {
		header("Location:driver.php?success=2");
		exit;
	}
}*/

/*$dri_ssql = "";
if (SITE_TYPE == 'Demo') {
	$dri_ssql = " And tRegistrationDate > '" . WEEK_DATE . "'";
}*/

if ($action == 'view') {
	
	/*$sql = "SELECT * FROM register_driver where iCompanyId = '" . $iCompanyId . "' and eStatus != 'Deleted' $dri_ssql order by tRegistrationDate DESC";*/
	
	$sql = "SELECT up.*,CONCAT(ru.vName,' ',ru.vLastName) AS name,ru.vEmail,ru.vPhone,ru.vPhoneCode FROM user_profile up LEFT JOIN register_user ru on  up.iUserId = ru.iUserId   where iOrganizationId = '" .$iOrganizationId . "' and up.eStatus != 'Deleted'";
	
	$data_drv = $obj->MySQLSelect($sql);

	/*if($APP_TYPE == 'Ride-Delivery') {
	    $eTypeQuery = " AND (eType='Ride' OR eType='Delivery')";
	} else if($APP_TYPE == 'Ride-Delivery-UberX'){
	    $eTypeQuery = " AND (eType='Ride' OR eType='Delivery' OR eType='UberX')";
	} else {
	    $eTypeQuery = " AND eType='".$APP_TYPE."'"; 
	}*/


	/*$sql1 = "SELECT doc_masterid as total FROM `document_master` WHERE `doc_usertype` ='driver' AND status = 'Active' $eTypeQuery";
	$doc_count_query = $obj->MySQLSelect($sql1);
	$doc_count = count($doc_count_query);*/

}

/*if ($action == 'edit') {
	// echo "<script>document.getElementById('cancel-add-form').style.display='';document.getElementById('show-add-form').style.display='none';document.getElementById('add-hide-div').style.display='none';</script>";
}*/

function getUserTrip($iUserId,$iOrganizationId)
{
	global $obj;
	$queryTotalTrip = "SELECT count(iTripId) AS totalTrip FROM trips   where iUserId = '" .$iUserId . "' and iOrganizationId = '" .$iOrganizationId . "'";
	
	$getUserTrip = $obj->MySQLSelect($queryTotalTrip);
	
	return $getUserTrip[0]['totalTrip'];
}


function getUserInfoForMail($iUserProfileId)
{
	global $obj;	

	$sql = "SELECT up.*,CONCAT(ru.vName,' ',ru.vLastName) AS name,ru.vEmail,org.vCompany FROM user_profile up LEFT JOIN register_user ru on  up.iUserId = ru.iUserId  LEFT JOIN organization org on  up.iOrganizationId = org.iOrganizationId   where iUserProfileId = '" .$iUserProfileId . "' and up.eStatus != 'Deleted'";
	
	$getUserData = $obj->MySQLSelect($sql);
	return $getUserData;
}


?>
<!DOCTYPE html>
<html lang="en" dir="<?=(isset($_SESSION['eDirectionCode']) && $_SESSION['eDirectionCode'] != "")?$_SESSION['eDirectionCode']:'ltr';?>">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width,initial-scale=1">
	<title><?=$SITE_NAME?> | <?php echo $langage_lbl['LBL_ORGANIZATION_USERS_WEB']; ?></title>
	<!-- Default Top Script and css -->
	<?php include_once("top/top_script.php");?>
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
		<!-- Driver page-->
		<div class="page-contant">
			<div class="page-contant-inner-new">
				<h2 class="header-page-d1 trip-detail driver-detail1">
					<?php echo $langage_lbl['LBL_ORGANIZATION_USERS_WEB']; ?> <!-- <a href="javascript:void(0);" onClick="add_driver_form();"><?=$langage_lbl['LBL_ADD_DRIVER_COMPANY_TXT']; ?></a> --></h2>
					<!-- driver list page -->
					<div class="trips-page trips-page1">
						<? if ($_REQUEST['success']==1) {?>
						<div class="alert alert-success alert-dismissable">
							<button aria-hidden="true" data-dismiss="alert" class="close" type="button">x</button> 
							<?= $var_msg ?>
						</div>
						<?}else if($_REQUEST['success']==2){ ?>
						<div class="alert alert-danger alert-dismissable">
							<button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>
							<?= $langage_lbl['LBL_EDIT_DELETE_RECORD']; ?>
						</div>
						<?php 
					} else if(isset($_REQUEST['success']) && $_REQUEST['success']==0){?>
					<div class="alert alert-danger alert-dismissable">
						<button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button> 
						<?= $var_msg ?>
					</div>
					<? }
					?>

					<div class="trips-table trips-table-driver trips-table-driver-res"> 
						<div class="trips-table-inner">
							<div class="driver-trip-table">
								<table width="100%" border="0" cellpadding="0" cellspacing="0" id="dataTables-example">
									<thead>
										<tr>
											<th width="20%"><?php echo $langage_lbl['LBL_USER_NAME_HEADER_SLIDE_TXT']; ?></th>

												<!-- <?php if($APP_TYPE != "UberX" && $APP_TYPE != "Ride-Delivery-UberX"){?>
												<th width="25%"><?=$langage_lbl['LBL_USER_NAME_HEADER_SLIDE_TXT']; ?></th>
												<?php } ?> -->
												
												<th width="20%"><?=$langage_lbl['LBL_DRIVER_EMAIL_LBL_TXT']; ?></th>
												<!--<th>Service Location</th>-->
												<th width="10%"><?php echo $langage_lbl['LBL_PHONE']; ?></th>

												<!-- <th width="15%" style="width: 67px;">
													<? if($APP_TYPE != "UberX" && $APP_TYPE != "Ride-Delivery-UberX"){
														echo $langage_lbl['LBL_SHORT_LANG_TXT']; 
													} else {
														echo $langage_lbl['LBL_SERVICES_WEB'];
													} ?>
												</th> -->
												
												<!-- <?php if($APP_TYPE == "UberX" || $APP_TYPE == "Ride-Delivery-UberX") { ?>
												<th>
													<?php echo $langage_lbl['LBL_AVAILABILITY'];?>
												</th>
												<?php } ?> -->
												<!-- <?php if($doc_count != 0 ) { ?>
												<th width="14%"><?php echo $langage_lbl['LBL_EDIT_DOCUMENTS_TXT'];?></th>
												<?php } ?> -->

												<!-- <th width="8%"><?=$langage_lbl['LBL_DRIVER_EDIT']; ?></th>
													<th width="8%"><?=$langage_lbl['LBL_DRIVER_DELETE']; ?></th> -->

	
													<th width="10%"><?php echo $langage_lbl['LBL_Status']; ?></th>
													<th width="10%"><?php echo $langage_lbl['LBL_TOTAL_TRIPS_WEB']; ?></th>
													<th width="10%"><?php echo $langage_lbl['LBL_ACTION_WEB']; ?></th>

												</tr>
											</thead>
											<tbody>
												<? for ($i = 0; $i < count($data_drv); $i++) { ?>

												<?php $bgRowColor = ($data_drv[$i]['eStatus'] == 'Pending') ? 'bgRowColor' : ''?>

												<tr class="gradeA <?= $bgRowColor ?>" >
													
													<!-- <?php if($APP_TYPE != "UberX" && $APP_TYPE != "Ride-Delivery-UberX"){?>
													<td><?= $generalobj->clearName($data_drv[$i]['vName'] . ' ' . $data_drv[$i]['vLastName']); ?></td>
													<?php } ?> -->

													<td><?= $generalobj->clearName($data_drv[$i]['name']); ?></td>
													<td><?= $generalobj->clearEmail($data_drv[$i]['vProfileEmail']); ?></td>
													
													<td>
														<?php if(!empty($data_drv[$i]['vPhone'])){?>
														(+<?= $data_drv[$i]['vPhoneCode'];?>) 
														<?= $generalobj->clearMobile($data_drv[$i]['vPhone']);?>
														<?php } ?>
													</td>

													<td><?= $generalobj->clearName($data_drv[$i]['eStatus']); ?></td>
													<td><?php echo getUserTrip($data_drv[$i]['iUserId'],$data_drv[$i]['iOrganizationId']) ?></td>	

													<td>
														<li class="entypo-twitter status-left" data-network="twitter"> 
															<a href="my_users.php?iUserProfileId=<?= $data_drv[$i]['iUserProfileId']; ?>&action=changeStatusAction&status=Pending" data-toggle="tooltip" title="Pending" onClick="return confirm('Are you sure You Want to Change Status ?')">
																<img src="<?= SITE_ADMIN_URL;?>/img/stopwatch.png" alt="Pending">	
															</a>
														</li>

														<li class="entypo-twitter status-left" data-network="twitter"> 

															<a href="my_users.php?iUserProfileId=<?= $data_drv[$i]['iUserProfileId']; ?>&action=changeStatusAction&status=Active" data-toggle="tooltip" title="Active" onClick="return confirm('Are you sure You Want to Change Status ?')">
																<img src="<?= SITE_ADMIN_URL;?>/img/active-icon.png" alt="Active">	
															</a>

														</li>

														<li class="entypo-twitter status-left" data-network="twitter"> 
															<a href="my_users.php?iUserProfileId=<?= $data_drv[$i]['iUserProfileId']; ?>&action=changeStatusAction&status=Inactive" data-toggle="tooltip" title="InActive" onClick="return confirm('Are you sure You Want to Change Status ?')">
																<img src="<?= SITE_ADMIN_URL;?>/img/inactive-icon.png" alt="InActive">	
															</a>
														</li>
														<li class="entypo-twitter status-left" data-network="twitter"> 
															<a href="my_users.php?iUserProfileId=<?= $data_drv[$i]['iUserProfileId']; ?>&action=changeStatusAction&status=Terminate" data-toggle="tooltip" title="Terminate" onClick="return confirm('Are you sure You Want to Change Status ?')">
																<img src="<?= SITE_ADMIN_URL;?>/img/delete-icon.png" alt="Terminate">	
															</a>
														</li>	
														<li class="entypo-twitter status-left" data-network="twitter"> 
															<a href="my_users.php?iUserProfileId=<?= $data_drv[$i]['iUserProfileId']; ?>&action=changeStatusAction&status=Reject" data-toggle="tooltip" title="Reject" onClick="return confirm('Are you sure You Want to Change Status ?')">
																<img src="<?= SITE_ADMIN_URL;?>/img/reject_icon.png" alt="Reject">	
															</a>
														</li>
													</td>

													<!-- <td>
														<? if($APP_TYPE != "UberX" && $APP_TYPE != "Ride-Delivery-UberX"){
																echo $data_drv[$i]['vLang']; 
															} else {?>
															<a href="add_services.php?iDriverId=<?= base64_encode(base64_encode($data_drv[$i]['iDriverId'])); ?>">
																<button class="btn btn-primary">
																	<i class="icon-pencil icon-white"></i>
																	<?=$langage_lbl['LBL_SERVICES_WEB'];?>
																</button>
															</a><?
															}
														 ?>
														</td> -->
													<!-- <?php if($APP_TYPE == "UberX" || $APP_TYPE == "Ride-Delivery-UberX") { ?>
													<td>
														<a href="add_availability.php?iDriverId=<?= $data_drv[$i]['iDriverId']; ?>">
															<button class="btn btn-primary">
																<i class="icon-pencil icon-white"></i>
																<?= $langage_lbl['LBL_AVAILABILITY'];?>
															</button>
														</a>
													</td>
													<?php } ?> -->

													<!-- <?php if($doc_count != 0 ) { ?>
													<td align="center" >
														<a href="driver_document_action.php?id=<?= $data_drv[$i]['iDriverId']; ?>&action=edit">
															<button class="btn btn-primary">
																<i class="icon-pencil icon-white"></i> <?=$langage_lbl['LBL_EDIT_DOCUMENTS_TXT']; ?>
															</button>
														</a>
													</td>
													<?php } ?> -->

													<!-- <td align="center" >
														<a href="driver_action.php?id=<?= $data_drv[$i]['iDriverId']; ?>&action=edit">
															<button class="btn btn-primary">
																<i class="icon-pencil icon-white"></i> <?=$langage_lbl['LBL_DRIVER_EDIT']; ?>
															</button>
														</a>
													</td> -->
													<!-- <td align="center" >
														<form name="delete_form_<?= $data_drv[$i]['iDriverId']; ?>" id="delete_form_<?= $data_drv[$i]['iDriverId']; ?>" method="post" action="" class="margin0">
															<input type="hidden" name="hdn_del_id" id="hdn_del_id" value="<?= $data_drv[$i]['iDriverId']; ?>">
															<input type="hidden" name="action" id="action" value="delete">
															<button type="button" class="btn btn-danger" onClick="confirm_delete('<?= $data_drv[$i]['iDriverId']; ?>');">
																<i class="icon-remove icon-white"></i> <?=$langage_lbl['LBL_DRIVER_DELETE']; ?>
															</button>
														</form>
													</td> -->
												</tr>
												<? } ?>
											</tbody>
										</table>
									</div>  
								</div>
							</div>
						</div>
						<div style="clear:both;"></div>
					</div>
				</div>
				<!-- footer part -->
				<?php include_once('footer/footer_home.php');?>
				<!-- footer part end -->
				<!-- End:contact page-->
				<div style="clear:both;"></div>
			</div>
			<!-- home page end-->
			<!-- Footer Script -->
			<?php include_once('top/footer_script.php');?>

			<script src="assets/js/jquery-ui.min.js"></script>
			<script src="assets/plugins/dataTables/jquery.dataTables.js"></script>
			<script type="text/javascript">
				$(document).ready(function () {
					$('#dataTables-example').dataTable({
						"oLanguage": langData,
						"aaSorting": []
					});
				});

	/*function confirm_delete(id)
	{
		bootbox.confirm('<?= addslashes($langage_lbl['LBL_DELETE_DRIVER_CONFIRM_MSG']); ?>', function(result) {
			if(result){
				document.getElementById('delete_form_'+id).submit();
			}
		});
	}*/
	
	/*function changeCode(id)
	{
		var request = $.ajax({
			type: "POST",
			url: 'change_code.php',
			data: 'id=' + id,
			success: function (data)
			{
				document.getElementById("code").value = data;
			}
		});
	}*/
	
	/*function add_driver_form(){
		window.location.href = "driver_action.php";
	}*/
</script>

<script type="text/javascript">
	$(document).ready(function(){
		$("[name='dataTables-example_length']").each(function(){
			$(this).wrap("<em class='select-wrapper'></em>");
			$(this).after("<em class='holder'></em>");
		});
		$("[name='dataTables-example_length']").change(function(){
			var selectedOption = $(this).find(":selected").text();
			$(this).next(".holder").text(selectedOption);
		}).trigger('change');
	})
</script>

<script type="text/javascript">
	$('.bgRowColor').children('td').css('background-color','#F97A7A');
</script>

<!-- End: Footer Script -->
</body>
</html>
