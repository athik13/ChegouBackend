<?php
include_once('common.php');
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
		$var_msg = $langage_lbl['LBL_Record_Updated_successfully'];
		header("Location:organization-user?success=1&var_msg=". $var_msg);
		exit();
	/*} else {
		header("Location:organization-user?success=2");
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

if ($action == 'view') {
	
	$sql = "SELECT up.*,CONCAT(ru.vName,' ',ru.vLastName) AS name,ru.vEmail,ru.vPhone,ru.vPhoneCode FROM user_profile up LEFT JOIN register_user ru on  up.iUserId = ru.iUserId   where iOrganizationId = '" .$iOrganizationId . "' and up.eStatus != 'Deleted'";
	
	$data_drv = $obj->MySQLSelect($sql);

}

/* commnented bc in my_users file already included this function
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
}*/


?>
<!DOCTYPE html>
<html lang="en" dir="<?=(isset($_SESSION['eDirectionCode']) && $_SESSION['eDirectionCode'] != "")?$_SESSION['eDirectionCode']:'ltr';?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0, user-scalable=no, target-densityDpi=device-dpi">
    <!--<title><?=$SITE_NAME?></title>-->
    <title><?=$SITE_NAME?> | <?php echo $langage_lbl['LBL_ORGANIZATION_USERS_WEB']; ?></title>
    <!-- Default Top Script and css -->
    <?php
    include_once("top/top_script.php");
    $rtls = "";
    if ($lang_ltr == "yes") {
        $rtls = "dir='rtl'";
    }
        ?>
    <!-- End: Default Top Script and css-->

</head>
<body id="wrapper">
    <!-- home page -->
    <!-- home page -->
    <?php if($template!='taxishark'){?>
    <div id="main-uber-page">
    <?php } ?>
        <!-- Left Menu -->
    <?php include_once("top/left_menu.php");?>
    <!-- End: Left Menu-->
        <!-- Top Menu -->
        <?php include_once("top/header_topbar.php");?>
        <!-- End: Top Menu-->
        <!-- First Section -->
        <?php include_once("top/header.php");?>
        <!-- End: First Section -->
<section class="profile-section my-trips">
    <div class="profile-section-inner">
        <div class="profile-caption">
            <div class="page-heading">
                <h1><?php echo $langage_lbl['LBL_ORGANIZATION_USERS_WEB']; ?></h1>
            </div>          
					<div class="trips-page trips-page1" style="width: 100%;">
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

        </div>
    </div>
</section>
<section class="profile-earning">
    <div class="profile-earning-inner">
    <div class="table-holder">
        <table id="my-trips-data" class="ui celled table custom-table" style="width:100%">
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
						<?php
						$eStatus = $data_drv[$i]['eStatus'];
						if($eStatus=='Pending') {
							$eStatus = $langage_lbl['LBL_PENDING'];
						} else if($eStatus=='Active') {
							$eStatus = $langage_lbl['LBL_ACTIVE'];
						} else if($eStatus=='Inactive') {
							$eStatus = $langage_lbl['LBL_INACTIVE'];
						} else if($eStatus=='Deleted') {
							$eStatus = $langage_lbl['LBL_DELETED'];
						} else if($eStatus=='Terminate')  {
							$eStatus = $langage_lbl['LBL_TERMINATE'];
						} else if($eStatus=='Reject')  {
							$eStatus = $langage_lbl['LBL_DELETED'];
						}
						?>
						<td><?= $generalobj->clearName($eStatus); ?></td>
						<td><?php echo getUserTrip($data_drv[$i]['iUserId'],$data_drv[$i]['iOrganizationId']) ?></td>	

						<td>
							<ul class="actionListing">
							<li class="entypo-twitter status-left" data-network="twitter"> 
								<a href="organization-user?iUserProfileId=<?= $data_drv[$i]['iUserProfileId']; ?>&action=changeStatusAction&status=Pending" data-toggle="tooltip" title="Pending" onClick="return confirm('<?= $langage_lbl['LBL_CONFIRM_CHANGE_STATUS'] ?>')">
									<!-- <img src="admin/img/stopwatch.png" alt="Pending">	 -->
									<i class="icon-time-left"></i>
								</a>
							</li>

							<li class="entypo-twitter status-left" data-network="twitter"> 
								<a href="organization-user?iUserProfileId=<?= $data_drv[$i]['iUserProfileId']; ?>&action=changeStatusAction&status=Active" data-toggle="tooltip" title="Active" onClick="return confirm('<?= $langage_lbl['LBL_CONFIRM_CHANGE_STATUS'] ?>')">
									<!-- <img src="admin/img/active-icon.png" alt="Active">	 -->
									<i class="icon-verified"></i>
								</a>
							</li>

							<li class="entypo-twitter status-left" data-network="twitter"> 
								<a href="organization-user?iUserProfileId=<?= $data_drv[$i]['iUserProfileId']; ?>&action=changeStatusAction&status=Inactive" data-toggle="tooltip" title="InActive" onClick="return confirm('<?= $langage_lbl['LBL_CONFIRM_CHANGE_STATUS'] ?>')">
									<!-- <img src="admin/img/inactive-icon.png" alt="InActive">	 -->
									<i class="icon-cancel"></i>
								</a>
							</li>
							<li class="entypo-twitter status-left" data-network="twitter"> 
								<a href="organization-user?iUserProfileId=<?= $data_drv[$i]['iUserProfileId']; ?>&action=changeStatusAction&status=Terminate" data-toggle="tooltip" title="Terminate" onClick="return confirm('<?= $langage_lbl['LBL_CONFIRM_CHANGE_STATUS'] ?>')">
									<!-- <img src="admin/img/delete-icon.png" alt="Terminate">	 -->
									<i class="icon-delete"></i>

								</a>
							</li>	
							<li class="entypo-twitter status-left" data-network="twitter"> 
								<a href="organization-user?iUserProfileId=<?= $data_drv[$i]['iUserProfileId']; ?>&action=changeStatusAction&status=Reject" data-toggle="tooltip" title="Reject" onClick="return confirm('<?= $langage_lbl['LBL_CONFIRM_CHANGE_STATUS'] ?>')">
									<!-- <img src="admin/img/reject_icon.png" alt="Reject">	 -->
									<i class="icon-reject-user"></i>

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
</section>
    <!-- home page end-->
    <!-- footer part -->
    <?php include_once('footer/footer_home.php');?>

    <div style="clear:both;"></div>
     <?php if($template!='taxishark'){?>
     </div>
     <?php } ?>
    <!-- footer part end -->
<!-- Footer Script -->
<?php include_once('top/footer_script.php');?>
<script src="assets/js/jquery-ui.min.js"></script>
<script src="assets/plugins/dataTables/jquery.dataTables.js"></script>


<script type="text/javascript">
      if($('#my-trips-data').length > 0) {
        $('#my-trips-data').DataTable({
			"oLanguage": langData,
        	 "columnDefs": [
	          { "orderable": false, "targets": [-1] },
	          { "searchable": false, "targets": [-1] }
	        ]
        });
    }
    



    $(document).on('change','#timeSelect',function(e){
        e.preventDefault();
        
        var timeSelect = $(this).val();
        
        if(timeSelect == 'today'){ todayDate('dp4', 'dp5') }
        if(timeSelect == 'yesterday'){yesterdayDate('dFDate', 'dTDate')}
        if(timeSelect == 'currentWeek'){currentweekDate('dFDate', 'dTDate')}
        if(timeSelect == 'previousWeek'){previousweekDate('dFDate', 'dTDate')}
        if(timeSelect == 'currentMonth'){currentmonthDate('dFDate', 'dTDate')}
        if(timeSelect == 'previousMonth'){previousmonthDate('dFDate', 'dTDate')}
        if(timeSelect == 'currentYear'){currentyearDate('dFDate', 'dTDate')}
        if(timeSelect == 'previousYear'){previousyearDate('dFDate', 'dTDate')}

    });




</script>

<script type="text/javascript">
	$(document).ready(function () {
		$('#dataTables-example').dataTable({
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
