<?
include_once("../common.php");

if (!isset($generalobjAdmin)) {
    require_once(TPATH_CLASS . "class.general_admin.php");
    $generalobjAdmin = new General_admin();
}
////$generalobjAdmin->check_member_login();

 $iDriverId = isset($_REQUEST['iDriverId'])?$_REQUEST['iDriverId']:'';  

 $sql="select iDriverId,eIsBlocked from register_driver rd  where iDriverId = '$iDriverId'";
$data_user = $obj->MySQLSelect($sql);

 
?>  <form name="frmfeatured" id="frmfeatured" action="" method="post">
	  <input type="hidden" name="iDriverId" value="<?php echo $data_user[0]['iDriverId']; ?>" >
		<input type="hidden" name="eIsBlocked1" value="<?= ($data_user[0]['eIsBlocked'] == "Yes") ? 'No' : 'Yes' ?>" >
		<input type="hidden" name="action" value="Blocked" >
						
			<div class="modal-footer">
			<button type="button" class="btn btn-ok" data-dismiss="modal">Not Now</button>
			<button class="btn btn-danger">
			<i class="<?= ($data_user[0]['eIsBlocked'] == "Yes") ? 'fa fa-check-circle' : 'fa fa-check-circle-o' ?>"></i>&nbsp;Yes
			</button>
			</div>  
	</form>