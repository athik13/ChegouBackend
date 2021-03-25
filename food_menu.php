<?php
	include_once('common.php');
	
	//added by SP for cubex changes on 07-11-2019
	if($generalobj->checkXThemOn() == 'Yes') {
	        include_once("cx-food_menu.php");
	        exit;
	}

	$generalobj->check_member_login();
	
	$abc = 'company';
	$url = $_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
	$generalobj->setRole($abc,$url);
	
	if($_SESSION["sess_eSystem"] != "DeliverAll")
	{
		header('Location:profile.php');
	}

	$id = isset($_REQUEST['id']) ? $_REQUEST['id'] : '';
	$hdn_del_id = isset($_REQUEST['hdn_del_id']) ? $_REQUEST['hdn_del_id'] : '';
	$iFoodMenuId = isset($_REQUEST['iFoodMenuId']) ? $_REQUEST['iFoodMenuId'] : '';
	$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : 'view';
	$Status = isset($_REQUEST['Status']) ? $_REQUEST['Status'] : '';

	$var_msg = isset($_REQUEST["var_msg"]) ? $_REQUEST["var_msg"] : '';
	$iCompanyId = $_SESSION['sess_iUserId'];
	
	$script = 'FoodMenu';
	$tbl_name = "food_menu";

	if($iFoodMenuId != '' && $Status != '') {
		$check_items1 = "SELECT count(iMenuItemId) as FoodItems FROM  `menu_items` WHERE iFoodMenuId = '" . $iFoodMenuId . "' AND eStatus='Active'";
		$total_items_active = $obj->MySQLSelect($check_items1);
	  	if(SITE_TYPE !='Demo') {

		  	//if($Status == 'Active' && $total_items_active[0]['FoodItems'] > 0) {
	  		if($Status == 'Active'){
	            $query = "UPDATE food_menu SET eStatus = '" . $Status . "' WHERE iFoodMenuId = '" . $iFoodMenuId . "'";
	            $obj->sql_query($query);
	            $_REQUEST['success'] = '1';
	            $var_msg = "Food Menu activated successfully.";
	        }
	       /* } else if ($Status == 'Active' && $total_items_active[0]['FoodItems'] <= 0) {
	        	$_REQUEST['success'] = '0';
	        	$var_msg = 'This category does not have any active menu items. Please add or check menu items to Active this category.';
	        }*/

	        if($Status == 'Inactive' && $total_items_active[0]['FoodItems'] <= 0) {
	            $query = "UPDATE food_menu SET eStatus = '" . $Status . "' WHERE iFoodMenuId = '" . $iFoodMenuId . "'";
	            $obj->sql_query($query);
	            $_REQUEST['success'] = '1';
	            $var_msg = "Food Menu inactivated successfully.";
	        } else if($Status == 'Inactive' && $total_items_active[0]['FoodItems'] > 0){
	        	$_REQUEST['success'] = '0';
	        	$var_msg = 'This category contains Active Menu items. Please inactive menu items to inactive this category.';
	        }

	 	} else {
			header("Location:food_menu.php?success=2");exit;
	  	}
	}

	if ($action == 'delete') {
		if($hdn_del_id != ''){
		    $check_items = "SELECT count(iMenuItemId) as FoodItems FROM  `menu_items` WHERE iFoodMenuId = '" . $hdn_del_id . "' AND eStatus !='Deleted'";
		    $total_items = $obj->MySQLSelect($check_items);
		}

		if(SITE_TYPE != 'Demo')
		{	
			if($total_items[0]['FoodItems'] <= 0) {
				$sql = "SELECT * FROM `$tbl_name` WHERE iFoodMenuId = '".$hdn_del_id."'";
				$db_oldData = $obj->MySQLSelect($sql);
				if(!empty($db_oldData)){
					$iDisplayOrder = $db_oldData[0]['iDisplayOrder'];
					
					//$query = "DELETE FROM `$tbl_name` WHERE iFoodMenuId = '".$hdn_del_id."'";
					$query = "UPDATE food_menu SET eStatus = 'Deleted' WHERE iFoodMenuId = '" . $hdn_del_id . "'";
					$obj->sql_query($query);

					//Update Display Order
					/* $sql = "SELECT * FROM `$tbl_name` where iCompanyId = '$iCompanyId' AND iDisplayOrder >= '$iDisplayOrder' AND eStatus != 'Deleted' ORDER BY iDisplayOrder ASC";
					$db_orders = $obj->MySQLSelect($sql);
					if(!empty($db_orders)){
						$j = $iDisplayOrder;
						for($i=0;$i<count($db_orders);$i++){
							$query = "UPDATE $tbl_name SET iDisplayOrder = '$j' WHERE iFoodMenuId = '".$db_orders[$i]['iFoodMenuId']."'";
							$obj->sql_query($query);
							$j++;
						}
					}*/
					$_REQUEST['success'] = '1';
		            $var_msg = $langage_lbl['LBL_FOOD_CATEGORY_DELETE_MSG'];
				}
		            $action = 'view';

			} else {
				$_REQUEST['success'] = '0';
	            $var_msg = $langage_lbl['LBL_FOOD_CATEOGRIES_EXIST_MSG'];
	            $action = 'view';
	        }
		} else {
			header("Location:food_menu.php?success=2");
			exit;
		}
	}

if ($action == 'view') {	
	$sql = "SELECT f.*,c.vCompany,(select count(iMenuItemId) from menu_items where iFoodMenuId = f.iFoodMenuId AND eStatus != 'Deleted') as MenuItems FROM  `food_menu` as f LEFT JOIN company c ON f.iCompanyId = c.iCompanyId  WHERE 1=1 AND f.iCompanyId = '" . $iCompanyId . "' AND f.eStatus != 'Deleted' $dri_ssql";
	$data_drv = $obj->MySQLSelect($sql);
}

?>
<!DOCTYPE html>
<html lang="en" dir="<?=(isset($_SESSION['eDirectionCode']) && $_SESSION['eDirectionCode'] != "")?$_SESSION['eDirectionCode']:'ltr';?>">
	<head>
		<meta charset="UTF-8">
		<meta name="viewport" content="width=device-width,initial-scale=1">
		<title><?=$SITE_NAME?> | <?=$langage_lbl['LBL_FOOD_CATEOGRIES_FRONT']; ?></title>
		<!-- Default Top Script and css -->
		<?php include_once("top/top_script.php");?>
		
		<!-- <link href="assets/plugins/dataTables/dataTables.bootstrap.css" rel="stylesheet" /> -->
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
			<div class="page-contant">
				<div class="page-contant-inner">
					<h2 class="header-page-d1 trip-detail driver-detail1"><?=$langage_lbl['LBL_FOOD_CATEOGRIES_FRONT']; ?><a href="javascript:void(0);" onClick="add_food_form();">Add <?=$langage_lbl['LBL_FOOD_CATEGORY_FRONT']; ?></a></h2>
					<!-- trips page -->
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
												<th width="20%"><?= $langage_lbl['LBL_FOOD_TITLE'];?></th>
												<th width="10%"><?= $langage_lbl['LBL_RESTAURANT_FRONT'];?></th>
												<th width="15%"><?= $langage_lbl['LBL_DISPLAY_ORDER_FRONT'];?> </th>
												<th><?= $langage_lbl['LBL_MENU_ITEMS_FRONT'];?></th>
												<th><?=$langage_lbl['LBL_Status']; ?></th>
												<th width="8%"><?=$langage_lbl['LBL_FOOD_CATEOGRY_EDIT']; ?></th>
												<th width="8%"><?=$langage_lbl['LBL_FOOD_CATEOGRY_DELETE']; ?></th>
											</tr>
										</thead>
										<tbody>
											<? for ($i = 0; $i < count($data_drv); $i++) { ?>
												<tr class="gradeA">
													<td><?= $generalobj->clearName($data_drv[$i]['vMenu_'.$default_lang]) ; ?></td>
													<td><?= $generalobj->clearName($data_drv[$i]['vCompany']); ?></td>
													<td><?= $data_drv[$i]['iDisplayOrder']; ?></td>
													<td><a href="menuitems.php?menu_itemid=<?= $data_drv[$i]['iFoodMenuId']?>"><?= $data_drv[$i]['MenuItems']; ?></a></td>
													<td width="10%" align="center">
													 <a href="food_menu.php?iFoodMenuId=<?= $data_drv[$i]['iFoodMenuId']; ?>&Status=<?= ($data_drv[$i]['eStatus'] == "Active") ? 'Inactive' : 'Active' ?>">
														  <button class="btn">
															<i class="<?= ($data_drv[$i]['eStatus'] == "Active") ? 'icon-eye-open' : 'icon-eye-close' ?>"></i> <?= ucfirst($data_drv[$i]['eStatus']); ?>
														  </button>
														</a>
													</td>		
													<td align="center" >
														<a href="food_menu_action.php?id=<?= $data_drv[$i]['iFoodMenuId']; ?>&action=edit">
															<button class="btn btn-primary">
																<i class="icon-pencil icon-white"></i> <?=$langage_lbl['LBL_DRIVER_EDIT']; ?>
															</button>
														</a>
													</td>
													<td align="center" >
														<form name="delete_form_<?= $data_drv[$i]['iFoodMenuId']; ?>" id="delete_form_<?= $data_drv[$i]['iFoodMenuId']; ?>" method="post" action="" class="margin0">
															<input type="hidden" name="hdn_del_id" id="hdn_del_id" value="<?= $data_drv[$i]['iFoodMenuId']; ?>">
															<input type="hidden" name="action" id="action" value="delete">
															<button type="button" class="btn btn-danger" onClick="confirm_delete('<?= $data_drv[$i]['iFoodMenuId']; ?>');">
																<i class="icon-remove icon-white"></i> <?=$langage_lbl['LBL_DRIVER_DELETE']; ?>
															</button>
														</form>
													</td>
												</tr>
											<? } ?>
										</tbody>
									</table>
								</div>  </div>
						</div>
						<!-- <? if(SITE_TYPE=="Demo"){?>
							<div class="record-feature"> <span><strong>“Edit / Delete Record Feature”</strong> has been disabled on the Demo Admin Version you are viewing now.
							This feature will be enabled in the main product we will provide you.</span> </div>
						<?php }?> -->
					</div>
					<div style="clear:both;"></div>
					<input type="hidden" name="del_btn_id" id="del_btn_id" value="">
				</div>
			</div>
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
				var btnid = "delete_form_"+id;
				$("#del_btn_id").val(btnid);
				var content = '<?php echo $langage_lbl['LBL_FOOD_CATEGORY_DELETE']; ?>';
				show_alert('',content,'<?= $langage_lbl['LBL_OK'] ?>','<?= $langage_lbl['LBL_CANCEL_TXT'] ?>','',function (btn_id) {
					if (btn_id==0) {
                        document.getElementById($("#del_btn_id").val()).submit();
                    }
				});
				//bootbox.confirm('<?=$langage_lbl['LBL_FOOD_CATEGORY_DELETE']; ?>', function(result) {
				//	if(result){
				//		document.getElementById('delete_form_'+id).submit();
				//	}
				//});
			}
			function add_food_form(){
				window.location.href = "food_menu_action.php";
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
