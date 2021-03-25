<?php
	include_once('../common.php');
	require_once(TPATH_CLASS."Imagecrop.class.php");
	
	if(!isset($generalobjAdmin)){
		require_once(TPATH_CLASS."class.general_admin.php");
		$generalobjAdmin = new General_admin();
	}
	////$generalobjAdmin->check_member_login();
	
	$default_lang = $generalobj->get_default_lang();
	$id 		= isset($_REQUEST['id'])?$_REQUEST['id']:''; // iUniqueId
	$success	= isset($_REQUEST['success'])?$_REQUEST['success']:'';
	$action 	= ($id != '')?'Edit':'Add';	
	//$temp_gallery = $tconfig["tpanel_path"];
	$tbl_name 	= 'hotel_banners';
	$script 	= 'Hotel Banner';
	
	
	
	// fetch all lang from language_master table 
	$sql = "SELECT vCode FROM `language_master` where eStatus='Active' ORDER BY `iDispOrder`";
	$db_master = $obj->MySQLSelect($sql);
	$count_all = count($db_master);	
	
	
	$vImage = isset($_POST['vImage_old']) ? $_POST['vImage_old'] : '';
	$eStatus_check 	= isset($_POST['eStatus'])?$_POST['eStatus']:'off';
	$vTitle = isset($_POST['vTitle'])?$_POST['vTitle']:'';
	$eStatus 		= ($eStatus_check == 'on')?'Active':'Inactive';
	$thumb = new thumbnail();
	/* to fetch max iDisplayOrder from table for insert */
	$select_order	= $obj->MySQLSelect("SELECT MAX(iDisplayOrder) AS iDisplayOrder FROM ".$tbl_name." WHERE vCode = '".$default_lang."'");
	$iDisplayOrder	= isset($select_order[0]['iDisplayOrder'])?$select_order[0]['iDisplayOrder']:0;
	$iDisplayOrder	= $iDisplayOrder + 1; // Maximum order number
	
	$iDisplayOrder	= isset($_POST['iDisplayOrder'])?$_POST['iDisplayOrder']:$iDisplayOrder;
	$temp_order 	= isset($_POST['temp_order'])? $_POST['temp_order'] : "";
		
	if(isset($_POST['submit'])) { //form submit

		if($action == "Add" && !$userObj->hasPermission('create-banner')){
	        $_SESSION['success'] = 3;
	        $_SESSION['var_msg'] = 'You do not have permission to create hotel banner.';
	        header("Location:hotel_banner.php");
	        exit;
	    }

	    if($action == "Edit" && !$userObj->hasPermission('edit-banner')){
	        $_SESSION['success'] = 3;
	        $_SESSION['var_msg'] = 'You do not have permission to update hotel banner.';
	        header("Location:hotel_banner.php");
	        exit;
	    }


		if(!empty($id) && SITE_TYPE =='Demo'){
			$_SESSION['success'] = 2;
			header("Location:hotel_banner.php");exit;
		}

		if($temp_order > $iDisplayOrder) {
			for($i = $temp_order; $i >= $iDisplayOrder; $i--) { 
				$obj->sql_query("UPDATE ".$tbl_name." SET iDisplayOrder = ".($i+1)." WHERE iDisplayOrder = ".$i);
			}
			} else if($temp_order < $iDisplayOrder) {
			for($i = $temp_order; $i <= $iDisplayOrder; $i++) {
				$obj->sql_query("UPDATE ".$tbl_name." SET iDisplayOrder = ".($i-1)." WHERE iDisplayOrder = ".$i);
			}
		}
		
		$select_order = $obj->MySQLSelect("SELECT MAX(iUniqueId) AS iUniqueId FROM ".$tbl_name." WHERE vCode = '".$default_lang."'");
		$iUniqueId = isset($select_order[0]['iUniqueId'])?$select_order[0]['iUniqueId']:0;
		$iUniqueId = $iUniqueId + 1; // Maximum order number
		
		if($count_all > 0) {
			for($i=0;$i<$count_all;$i++) {
				
				$q = "INSERT INTO ";
				$where = '';
				
				if($id != '' ){ 
					$q = "UPDATE ";
					$where = " WHERE `iUniqueId` = '".$id."' AND vCode = '".$db_master[$i]['vCode']."'";
					$iUniqueId = $id;
				}
				$image_object = $_FILES['vImage']['tmp_name'];  
				$image_name   = $_FILES['vImage']['name'];
				
				if($image_name != ""){
					$filecheck = basename($_FILES['vImage']['name']);                            
					$fileextarr = explode(".",$filecheck);
					$ext=strtolower($fileextarr[count($fileextarr)-1]);
					$flag_error = 0;
					if($ext != "jpg" && $ext != "gif" && $ext != "png" && $ext != "jpeg" && $ext != "bmp"){
						$flag_error = 1;
						$var_msg = "Not valid image extension of .jpg, .jpeg, .gif, .png";
					}

					$image_info = getimagesize($_FILES["vImage"]["tmp_name"]);
					$image_width = $image_info[0];
					$image_height = $image_info[1];
					
					if($flag_error == 1){
					//echo $tconfig['tsite_url'];
						$_SESSION['success'] = '3';
						$_SESSION['var_msg'] = $var_msg;
						header("Location:hotel_banner.php");
						exit;
						/*$generalobj->getPostForm($_POST,$var_msg,"banner_action.php?success=0&var_msg=".$var_msg);
						exit;*/
                    } else {
						$Photo_Gallery_folder = $tconfig["tsite_upload_images_hotel_banner_path"].'/';
						if(!is_dir($Photo_Gallery_folder)){
							mkdir($Photo_Gallery_folder, 0777);
						}  
						$img = $generalobj->fileupload($Photo_Gallery_folder,$image_object,$image_name, '','jpg,png,gif,jpeg');
						$vImage = $img[0];
					}
				}
				//$vTitle = 'vTitle_'.$db_master[$i]['vCode'];
				
				$query = $q ." `".$tbl_name."` SET 	
					`vTitle` = '".$vTitle."',
					`vImage` = '".$vImage."',
					`eStatus` = '".$eStatus."',
					`iUniqueId` = '".$iUniqueId."',
					`iDisplayOrder` = '".$iDisplayOrder."',
					`vCode` = '".$db_master[$i]['vCode']."'"
				.$where;
				$obj->sql_query($query);

				if($id != '' ){ 
				$_SESSION['success'] = '1';
					$_SESSION['var_msg'] = $langage_lbl_admin['LBL_Record_Updated_successfully'];
				} else {
					$_SESSION['success'] = '1';
				$_SESSION['var_msg'] = $langage_lbl_admin['LBL_RECORD_INSERT_MSG'];
				}
			}
			 header("Location:hotel_banner.php");
			 exit();
		}
	}
	
	// for Edit
	if($action == 'Edit') {
		$sql = "SELECT vTitle,eStatus,vImage,iDisplayOrder FROM ".$tbl_name." WHERE iUniqueId = '".$id."'";
		$db_data = $obj->MySQLSelect($sql);
		$iUniqueId = $id;
		if(count($db_data) > 0) {
			foreach($db_data as $key => $value) {
				//$vTitle 			= 'vTitle_'.$value['vCode'];
				$vTitle 			= $value['vTitle'];				
				$eStatus 			= $value['eStatus'];
				$vImage 			= $value['vImage'];
				$iDisplayOrder 		= $value['iDisplayOrder'];
			}
		}
	}
?>
<!DOCTYPE html>
<!--[if !IE]><!--> <html lang="en"> <!--<![endif]-->
	
	<!-- BEGIN HEAD-->
	<head>
		<meta charset="UTF-8" />
		<title>Admin | Hotel Banner <?=$action;?></title>
		<meta content="width=device-width, initial-scale=1.0" name="viewport" />
		<link href="../assets/plugins/dataTables/dataTables.bootstrap.css" rel="stylesheet" />
		
		<? include_once('global_files.php');?>
		<!-- On OFF switch -->
		<link href="../assets/css/jquery-ui.css" rel="stylesheet" />
		<link rel="stylesheet" href="../assets/plugins/switch/static/stylesheets/bootstrap-switch.css" />	
	</head>
	<!-- END  HEAD-->
	<!-- BEGIN BODY-->
	<body class="padTop53 " >
		
		<!-- MAIN WRAPPER -->
		<div id="wrap">
			<? include_once('header.php'); ?>
			<? include_once('left_menu.php'); ?>       
			<!--PAGE CONTENT -->
			<div id="content">
				<div class="inner">
					<div class="row">
						<div class="col-lg-12">
							<h2><?=$action;?> Hotel Banner</h2>
							<a href="hotel_banner.php">
								<input type="button" value="Back to Listing" class="add-btn">
							</a>
						</div>
					</div>
					<hr />	
					<div class="body-div">
						<div class="form-group">
						<? if ($success == 0 && $_REQUEST['var_msg'] != "") {?>
							<div class="alert alert-danger alert-dismissable">
							<button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>
								<? echo $_REQUEST['var_msg']; ?>
							</div><br/>
						<?} ?>
						<? if($success == 1) { ?>
								<div class="alert alert-success alert-dismissable">
									<button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>
									<?php echo $langage_lbl_admin['LBL_Record_Updated_successfully']; ?>
							</div><br/>
						<? } ?>
						<? if ($success == 2) {?>
		                 <div class="alert alert-danger alert-dismissable">
		                      <button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>
		                      <?php echo $langage_lbl_admin['LBL_EDIT_DELETE_RECORD']; ?>
								</div><br/>
							<? } ?>
							<form method="post" action="" enctype="multipart/form-data">
								<input type="hidden" name="id" value="<?=$id;?>"/>
								<input type="hidden" name="vImage_old" value="<?=$vImage?>">

								<div class="row">
									<div class="col-lg-12">
										<label>Image<?=($vImage == '')?'<span class="red"> *</span>':'';?></label>
									</div>
									<div class="col-lg-6">
										<? if($vImage != '') { ?>
											<!-- <img src="<?=$tconfig['tsite_upload_images_hotel_banner'].'/'.$vImage;?>" style="width:200px;height:100px;"> -->

											<img src="<?=$tconfig['tsite_url'].'resizeImg.php?w=400&h=200&src='.$tconfig['tsite_upload_images_hotel_banner'] . '/' . $vImage ?>" style="width:200px;height:100px;"> 

											<input type="file" name="vImage" id="vImage" value="<?=$vImage;?>"/>
										<? } else { ?>
											<input type="file" name="vImage" id="vImage" value="<?=$vImage;?>" required/>
										<? } ?>
										<br/>
										[Note: Recommended dimension for Hotel banner image is 2880 * 1620.]
									</div>
								</div>
								<div class="row">
									<div class="col-lg-12">
										<label>Title</label>
									</div>
									<div class="col-lg-6">
										<input type="text" name="vTitle" id="vTitle" value="<?=$vTitle?>" class="form-control" />
									</div>
								</div>
								<div class="row">
									<div class="col-lg-12">
										<label>Status</label>
									</div>
									<div class="col-lg-6">
										<div class="make-switch" data-on="success" data-off="warning">
											<input type="checkbox" name="eStatus" <?=($id != '' && $eStatus == 'Inactive')?'':'checked';?>/>
										</div>
									</div>
								</div>
								<div class="row">
									<div class="col-lg-12">
										<label>Order</label>
									</div>
									<div class="col-lg-6">
										<?
											$temp = 1;
											
											$dataArray = array();
											
											$query1 = "SELECT iDisplayOrder FROM ".$tbl_name." WHERE vCode = '".$default_lang."' ORDER BY iDisplayOrder";
											$data_order = $obj->MySQLSelect($query1);
											
											foreach($data_order as $value)
											{
												$dataArray[] = $value['iDisplayOrder'];
												$temp = $iDisplayOrder;
											}
										?>
										<input type="hidden" name="temp_order" id="temp_order" value="<?=$temp?>">
										<select name="iDisplayOrder" class="form-control">
											<? foreach($dataArray as $arr):?>
											<option <?= $arr == $temp ? ' selected="selected"' : '' ?> value="<?=$arr;?>" >
												-- <?= $arr ?> --
											</option>
											<? endforeach; ?>
											<?if($action=="Add") {?>
												<option value="<?=$temp;?>" >
													-- <?= $temp ?> --
												</option>
											<? }?>
										</select>
									</div>
								</div>
                                                                
								<div class="row">
									<?php if(($action == 'Edit' && $userObj->hasPermission('edit-banner')) || ($action == 'Add' &&  $userObj->hasPermission('create-banner'))){ ?>
										<div class="col-lg-12">
											<input type="submit" class="save btn-info" name="submit" id="submit" value="<?=$action;?> Hotel Banner">
										</div>
									<?php } ?>
								</div>
							</form>
						</div>
					</div>
				</div>
			</div>
			<!--END PAGE CONTENT -->
		</div>
		<!--END MAIN WRAPPER -->
		<? include_once('footer.php');?>
		<script src="../assets/plugins/switch/static/js/bootstrap-switch.min.js"></script>
	</body>
	<!-- END BODY-->    
</html>