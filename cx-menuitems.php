<?php
	include_once('common.php');
	$generalobj->check_member_login();
	$abc = 'company';
	$url = $_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
	$generalobj->setRole($abc,$url);
	$id = isset($_REQUEST['id']) ? $_REQUEST['id'] : '';
	$hdn_del_id = isset($_REQUEST['hdn_del_id']) ? $_REQUEST['hdn_del_id'] : '';
	$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : 'view';
	$var_msg = isset($_REQUEST["var_msg"]) ? $_REQUEST["var_msg"] : '';
	$iCompanyId = $_SESSION['sess_iUserId'];
	$menu_itemid = isset($_REQUEST['menu_itemid']) ? $_REQUEST['menu_itemid'] : "";

	$iMenuItemId = isset($_REQUEST["iMenuItemId"]) ? $_REQUEST["iMenuItemId"] : '';
	$Status = isset($_REQUEST["Status"]) ? $_REQUEST["Status"] : '';

	$script = 'MenuItems';
	$tbl_name = "menu_items";

	if($iMenuItemId != '' && $Status != ''){
		if(SITE_TYPE !='Demo'){
			$query = "UPDATE menu_items SET eStatus = '".$Status."' WHERE iMenuItemId = '".$iMenuItemId ."'";
			$obj->sql_query($query);
			$_REQUEST['success'] = '1';
			if($Status == 'Active') {
				$var_msg = "Item activated successfully.";
			}else {
				$var_msg = "Item inactivated successfully.";
			}
		}
		else{
			header("Location:menuitems.php?success=2");exit;
		}
	}

	if(!empty($menu_itemid)){
	  $ssql .= " AND f.iFoodMenuId = '".$menu_itemid."'"; 
	}

	if ($action == 'delete') {
		 if(SITE_TYPE != 'Demo')
		 {
		 	$sql = "SELECT * FROM `$tbl_name` WHERE iMenuItemId = '".$hdn_del_id."'";
			$db_oldData = $obj->MySQLSelect($sql);
			if(!empty($db_oldData)) {
				$iDisplayOrder = $db_oldData[0]['iDisplayOrder'];
				$iFoodMenuId = $db_oldData[0]['iFoodMenuId'];

	            //$query = "DELETE FROM menu_items WHERE iMenuItemId = '" . $hdn_del_id . "'";
				$query = "UPDATE menu_items SET eStatus = 'Deleted' WHERE iMenuItemId = '".$hdn_del_id ."'";
	            $obj->sql_query($query);

	            /*$query1 = "DELETE FROM menuitem_options WHERE iMenuItemId = '" . $hdn_del_id . "'";
            	$obj->sql_query($query1);*/

	            //Update Display Order
				/*$sql = "SELECT * FROM `$tbl_name` where iFoodMenuId = '$iFoodMenuId' AND `eStatus`!='Deleted' AND iDisplayOrder >= '$iDisplayOrder' ORDER BY iDisplayOrder ASC";
				$db_orders = $obj->MySQLSelect($sql);
				
				if(!empty($db_orders)){
					$j = $iDisplayOrder;
					for($i=0;$i<count($db_orders);$i++){
						$query = "UPDATE `$tbl_name` SET iDisplayOrder = '$j' WHERE iMenuItemId = '".$db_orders[$i]['iMenuItemId']."'";
						$obj->sql_query($query);
						$j++;
					}
				}*/
	            $DeleteMsg = $langage_lbl['LBL_MENU_ITEM_DELETE_MSG'];
	            header("Location:menuitems.php?success=1&menu_itemid=".$menu_itemid."&var_msg=".$DeleteMsg);
	            exit();
			}

		 }
		 else
		 {
			header("Location:menuitems.php?success=2");
			exit();
		 }
	}

	if ($action == 'view') {
		$sql = "SELECT mi.*,f.vMenu_".$default_lang.",c.vCompany FROM  `menu_items` as mi LEFT JOIN food_menu f ON f.iFoodMenuId = mi.iFoodMenuId LEFT JOIN company as c on c.iCompanyId=f.iCompanyId  WHERE 1=1 AND f.iCompanyId = '" . $iCompanyId . "' AND mi.eStatus != 'Deleted' $ssql";
		$data_drv = $obj->MySQLSelect($sql);
	}

?>
<!DOCTYPE html>
<html lang="en" dir="<?=(isset($_SESSION['eDirectionCode']) && $_SESSION['eDirectionCode'] != "")?$_SESSION['eDirectionCode']:'ltr';?>">
	<head>
		<meta charset="UTF-8">
		<meta name="viewport" content="width=device-width,initial-scale=1">
		<title><?=$SITE_NAME?> | <?=$langage_lbl['LBL_MENU_ITEM_FRONT']; ?></title>
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
			<!-- contact page-->

<style>
	.modal-backdrop, .modal-backdrop.fade.in {
		display:block
	}
</style>

<section class="profile-section my-trips">
    <div class="profile-section-inner">
        <div class="profile-caption">
            <div class="page-heading">
                <h1><?=$langage_lbl['LBL_MENU_ITEMS_FRONT']; ?></h1>
            </div>
			<div class="button-block end">
			<a href="javascript:void(0);" class="gen-btn" onClick="add_menu_item_form();"><?= $langage_lbl['LBL_ACTION_ADD']; ?> <?=$langage_lbl['LBL_MENU_ITEM_FRONT']; ?></a>
            </div>			
			
        </div>
    </div>
</section>


<section class="profile-earning">
    <div class="profile-earning-inner">
		<div class="table-holder">

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
									<table width="100%" border="0" class="custom-table" cellpadding="0" cellspacing="0" id="dataTables-example">
										<thead>
											<tr>
												<th width="20%"><?= $langage_lbl['LBL_MENU_TITLE'];?></th>
												<th width="10%"><?= $langage_lbl['LBL_CATEGORY_FRONT'];?></th>
												<!-- <th width="10%"><?= $langage_lbl['LBL_RESTAURANT_FRONT'];?></th> -->
												<th width="10%"><?= $langage_lbl['LBL_ITEM_IMAGE_FRONT'];?></th>
												<th width="8%"><?= $langage_lbl['LBL_DISPLAY_ORDER_FRONT'];?> </th>
												<th width="15%"><?=$langage_lbl['LBL_Status']; ?></th>
												<th width="8%"><?=$langage_lbl['LBL_FOOD_CATEOGRY_EDIT']; ?></th>
												<th width="8%"><?=$langage_lbl['LBL_FOOD_CATEOGRY_DELETE']; ?></th>
											</tr>
										</thead>
										<tbody>
											<? for ($i = 0; $i < count($data_drv); $i++) { ?>
												<tr class="gradeA">
													<td><?= $data_drv[$i]['vItemType_'.$default_lang] ; ?></td>
													<td><?= $data_drv[$i]['vMenu_'.$default_lang] ; ?></td>
													<!-- <td><?= $generalobj->clearName($data_drv[$i]['vCompany']); ?></td> -->
													<td>
														<? $imgpth = $tconfig["tsite_upload_images_menu_item_path"] . '/' . $data_drv[$i]['vImage'];
						                                    $imgUrl = $tconfig["tsite_upload_images_menu_item"] . '/' . $data_drv[$i]['vImage'];
						                                    if ($data_drv[$i]['vImage'] != "" && file_exists($imgpth)) {
						                                        ?>
						                                        <img src="<?php echo $imgUrl; ?>" alt="Image preview" class="thumbnail" style="max-width: 100px; max-height: 100px;margin:0;display: initial;">
						                                <?php } ?>
						                            </td>
													<td><?= $data_drv[$i]['iDisplayOrder']; ?></td>
													<td width="10%" align="center">
													 <a href="menuitems.php?iMenuItemId=<?= $data_drv[$i]['iMenuItemId']; ?>&Status=<?= ($data_drv[$i]['eStatus'] == "Active") ? 'Inactive' : 'Active' ?>" class="gen-btn small-btn"><?php if(strtolower($data_drv[$i]['eStatus']) == "active"){
                                                                                                                      $statusLabel = $langage_lbl['LBL_ACTIVE'];
                                                                                                                 }else { 
                                                                                                                     $statusLabel = $langage_lbl['LBL_INACTIVE'];
                                                                                                                 } ?>
                                                                                                                 <?= $statusLabel; ?></a>
													</td>
													<td align="center" >
														<a href="menu_item_action.php?id=<?= $data_drv[$i]['iMenuItemId']; ?>&action=edit" class="gen-btn small-btn"><?=$langage_lbl['LBL_DRIVER_EDIT']; ?>
														</a>
													</td>
													<td align="center" >
														<form name="delete_form_<?= $data_drv[$i]['iMenuItemId']; ?>" id="delete_form_<?= $data_drv[$i]['iMenuItemId']; ?>" method="post" action="" class="margin0">
															<input type="hidden" name="hdn_del_id" id="hdn_del_id" value="<?= $data_drv[$i]['iMenuItemId']; ?>">
															<input type="hidden" name="action" id="action" value="delete">
															<button type="button" class="gen-btn small-btn" onClick="confirm_delete('<?= $data_drv[$i]['iMenuItemId']; ?>');">
																<i class="icon-remove icon-white"></i> <?=$langage_lbl['LBL_DRIVER_DELETE']; ?>
															</button>
														</form>
													</td>
												</tr>
											<? } ?>
										</tbody>
									</table>
					
						</div>
					</div>
					<div style="clear:both;"></div>
				</div>
			</div>

		</div>
	</div>
</section>	

			<!-- footer part -->
			<?php include_once('footer/footer_home.php');?>
			<!-- footer part end -->
            <!-- End:food menu page-->
            <div style="clear:both;"></div>
		</div>
		<!-- Footer Script -->
		<?php include_once('top/footer_script.php');?> 
		<script src="assets/js/jquery-ui.min.js"></script>
		<script src="assets/plugins/dataTables/jquery.dataTables.js"></script>
		<script src="assets/js/modal_alert.js"></script>
		<script type="text/javascript">
			$(document).ready(function () {
				$('#dataTables-example').dataTable({
					"oLanguage": langData,
					"aaSorting": []
				});
			});
			function confirm_delete(id)
			{
				$("#hdn_del_id").val(id);
				show_alert("<?= addslashes($langage_lbl['LBL_DELETE']); ?>","<?= addslashes($langage_lbl['LBL_MENU_ITEM_DELETE']); ?>","<?= addslashes($langage_lbl['LBL_CONFIRM_TXT']); ?>","<?= addslashes($langage_lbl['LBL_CANCEL_TXT']); ?>","",function (btn_id) {
					if (btn_id==0) {
						id = $("#hdn_del_id").val();
						document.getElementById("delete_form_" + id).submit();
					}
				});
				//bootbox.confirm('<?=$langage_lbl['LBL_MENU_ITEM_DELETE']; ?>', function(result) {
				//	if(result){
				//		document.getElementById('delete_form_'+id).submit();
				//	}
				//});
			}
			function add_menu_item_form(){
				window.location.href = "menu_item_action.php";
			}
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
		<!-- End: Footer Script -->
	</body>
</html>
