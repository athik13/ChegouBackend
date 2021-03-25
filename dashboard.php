<?php
include_once('common.php');
require_once('assets/libraries/class.ExifCleaning.php');

$script = "Dashboard";
$user = isset($_SESSION["sess_user"]) ? $_SESSION["sess_user"] : '';
$success = isset($_REQUEST["success"]) ? $_REQUEST["success"] : '';
$var_msg = isset($_REQUEST["var_msg"]) ? $_REQUEST["var_msg"] : '';
$new = '';
$Today = Date('Y-m-d');

$db_doc = array();

if(isset($_SESSION['sess_new'])){
	$new = $_SESSION['sess_new'];
	unset($_SESSION['sess_new']);
}
$generalobj->check_member_login();

$access = 'store';
$url = $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
$generalobj->setRole($access, $url);

$sql = "select * from company where iCompanyId = '" . $_SESSION['sess_iUserId'] . "'";
$db_user = $obj->MySQLSelect($sql);

/*$sql= "SELECT dm.doc_masterid masterid, dm.doc_usertype ,dm.doc_name_".$_SESSION['sess_lang']."  as d_name , dm.doc_name ,dm.ex_status,dm.status, dl.doc_masterid masterid_list ,dl.ex_date,dl.doc_file , dl.status FROM document_master dm left join (SELECT * FROM `document_list` where doc_userid='" . $_SESSION['sess_iUserId'] ."' and doc_usertype='company') dl on dl.doc_masterid=dm.doc_masterid where dm.doc_usertype='company' and dm.status='Active' and (dm.country ='".$db_user[0]['vCountry']."' OR dm.country ='All')";
*/

$sql= "SELECT dm.doc_masterid masterid, dm.doc_usertype ,dm.doc_name_".$_SESSION['sess_lang']."  as d_name , dm.doc_name ,dm.ex_status,dm.status, dl.doc_masterid masterid_list ,dl.ex_date,dl.doc_file , dl.status FROM document_master dm left join (SELECT * FROM `document_list` where doc_userid='" . $_SESSION['sess_iUserId'] ."' and doc_usertype='store') dl on dl.doc_masterid=dm.doc_masterid where dm.doc_usertype='store' and dm.status='Active' and (dm.country ='".$db_user[0]['vCountry']."' OR dm.country ='All')";

$db_userdoc = $obj->MySQLSelect($sql);
$count_all_doc = count($db_userdoc);

$sql = "select * from language_master where eStatus = 'Active' ORDER BY vTitle ASC ";
$db_lang = $obj->MySQLSelect($sql);
$lang = "";
for ($i = 0;$i < count($db_lang);$i++) {
	if ($db_user[0]['vLang'] == $db_lang[$i]['vCode']) {
		$lang_user = $db_lang[$i]['vTitle'];
	}
}

if ($action='document' && isset($_POST['doc_type'])) {
	$expDate=$_POST['dLicenceExp'];

	if (SITE_TYPE == 'Demo') {
		header("location:dashboard.php?success=2&id=" . $_REQUEST['id'] . "&var_msg=" . $var_msg);
		exit;
	}

    $user=$_POST['user'];
    $masterid= $_REQUEST['master'];
	
	if (isset($_POST['doc_path'])) {
		$doc_path = $_POST['doc_path'];
	}
	$temp_gallery = $doc_path . '/';
	$image_object = $_FILES['driver_doc']['tmp_name'];
	$image_name = $_FILES['driver_doc']['name'];

	if(empty($image_name )) {
		$image_name = $_POST['driver_doc_hidden']; 
	}

	if ($image_name == "") {

		if($expDate != "") {
            $sql = "select ex_date from document_list where doc_userid='".$_REQUEST['id']."' and doc_usertype='store' and doc_masterid='".$masterid."'";

			$query = $obj->MySQLSelect($sql);
			$fetch = $query[0];
			
            if($fetch['ex_date'] == $expDate) {    
                $sql="UPDATE `document_list` SET  ex_date='".$expDate."' WHERE doc_userid='".$_REQUEST['id']."' and doc_usertype='store' and doc_masterid='".$masterid."'";
			} else {
                $sql ="INSERT INTO `document_list` ( `doc_masterid`, `doc_usertype`, `doc_userid`, `ex_date`, `doc_file`, `status`, `edate`) VALUES ( '".$_REQUEST['doc_type']."', 'store', '".$_REQUEST['id']."', '".$expDate."', '', 'Inactive', CURRENT_TIMESTAMP)";
			}
				$query= $obj->sql_query($sql);
		}
        
				$var_msg = $langage_lbl['LBL_UPLOAD_IMG_ERROR'];
				header("location:dashboard.php?success=0&id=" . $_REQUEST['id'] . "&var_msg=" . $var_msg);        	
	}
	
	if ($_FILES['driver_doc']['name'] != "") {

		$filecheck = basename($_FILES['driver_doc']['name']);
		$fileextarr = explode(".", $filecheck);
		$ext = strtolower($fileextarr[count($fileextarr) - 1]);
		$flag_error = 0;
		if ($ext != "jpg" && $ext != "gif" && $ext != "png" && $ext != "jpeg" && $ext != "bmp" && $ext != "pdf" && $ext != "doc" && $ext != "docx") {
			$flag_error = 1;
			$var_msg = $langage_lbl['LBL_WRONG_FILE_SELECTED_TXT'];
		}
		
		if ($flag_error == 1) {
			header("location:dashboard.php?success=0&id=" . $_REQUEST['id'] . "&var_msg=" . $var_msg);
			exit;
			
		} else {

			$Photo_Gallery_folder = $doc_path . '/' . $_REQUEST['id'] . '/';
			if (!is_dir($Photo_Gallery_folder)) {
				mkdir($Photo_Gallery_folder, 0777);
			}

			if($ext == "jpg" || $ext == "gif" || $ext == "png" || $ext == "jpeg" || $ext == "bmp"){
                if($image_object){
                    ExifCleaning::adjustImageOrientation($image_object); 
                }
            }

			$vFile = $generalobj->fileupload($Photo_Gallery_folder, $image_object, $image_name, $prefix = '', $vaildExt = "pdf,doc,docx,jpg,jpeg,gif,png");
			$vImage = $vFile[0];
			$var_msg = $langage_lbl['LBL_UPLOAD_MSG'];
			$tbl = 'document_list';
			
			// $sql = "select dl.doc_id from  ".$tbl." as dl where dl.doc_userid='".$_REQUEST[id]."' and dl.doc_usertype='store'  and dl.doc_masterid=".$_REQUEST['doc_type'] ;
			
			$sql = "select dl.doc_id,dm.ex_status,c.eStatus from  ".$tbl." as dl left join company as c ON c.iCompanyId = dl.doc_userid left join document_master as dm ON dm.doc_masterid = dl.doc_masterid where dl.doc_userid='".$_REQUEST[id]."' and dl.doc_usertype='store'  and dl.doc_masterid=".$_REQUEST['doc_type'] ;

			$db_data = $obj->MySQLSelect($sql);

			// echo '<pre>';print_r($db_data);die;


            $ex_dateField = 'ex_date';
            $doc_fileField = 'doc_file';
            
            $allowDate = date('Y-m-d', strtotime($db_data[0]['ex_date']. ' - '.$BEFORE_DAYS_ALLLOW_UPDATE_DOCS.' days'));
            
            if($Today >= $allowDate && $SET_DRIVER_OFFLINE_AS_DOC_EXPIRED == 'Yes' && $db_data[0]['ex_status'] == 'yes' && $db_data[0]['eStatus'] == 'Active') {
                $ex_dateField = 'req_date';
                $doc_fileField = 'req_file';
                $var_msg .= " ".$langage_lbl['LBL_FOR_DOCS_UNDER_REVIEW'];
            }


			$q = "INSERT INTO ";
			$where = '';
			
			if (count($db_data) > 0) {

				$query="UPDATE `".$tbl."` SET $doc_fileField='".$vImage."' , $ex_dateField='".$expDate."' WHERE doc_userid='".$_REQUEST[id]."' and doc_usertype='store'  and doc_masterid=".$_REQUEST['doc_type'];
				
				$q = "UPDATE ";
				$where = " WHERE `iDriverId` = '" . $_REQUEST['id'] . "'";

			} else {
                    
				$query =" INSERT INTO `".$tbl."` ( `doc_masterid`, `doc_usertype`, `doc_userid`, `ex_date`, `doc_file`, `status`, `edate`) "
					. "VALUES ". "( '".$_REQUEST['doc_type']."', 'store', '".$_REQUEST['id']."', '".$expDate."', '".$vImage."', 'Inactive', CURRENT_TIMESTAMP)";
			}   
			
			$obj->sql_query($query);

            $vNocPath = $vImage;
			$generalobj->save_log_data($_SESSION['sess_iUserId'], $_REQUEST['id'], 'store', 'noc', $vNocPath);
			header("location:dashboard.php?success=1&id=" . $_REQUEST['id'] . "&var_msg=" . $var_msg);
			exit();
		}
	} else {
		$vImage = $_POST['driver_doc_hidden'];
		$var_msg = $langage_lbl['LBL_UPLOAD_DOC_SUCCESS_UPLOAD_DOC'];
		$tbl = 'document_list';
 
		$sql = "select dl.doc_id,dm.ex_status,c.eStatus from  ".$tbl." as dl left join company as c ON c.iCompanyId = dl.doc_userid left join document_master as dm ON dm.doc_masterid = dl.doc_masterid where dl.doc_userid='".$_REQUEST[id]."' and dl.doc_usertype='store'  and dl.doc_masterid=".$_REQUEST['doc_type'] ;

		$db_data = $obj->MySQLSelect($sql);

   
        $ex_dateField = 'ex_date';
        $doc_fileField = 'doc_file';
        
        $allowDate = date('Y-m-d', strtotime( $db_data[0]['ex_date']. ' - '.$BEFORE_DAYS_ALLLOW_UPDATE_DOCS.' days'));

        if($Today >= $allowDate && $SET_DRIVER_OFFLINE_AS_DOC_EXPIRED == 'Yes' && $db_data[0]['ex_status'] == 'yes' && $db_data[0]['eStatus'] == 'Active') {
            $ex_dateField = 'req_date';
            $doc_fileField = 'req_file';
            $var_msg .= " ".$langage_lbl['LBL_FOR_DOCS_UNDER_REVIEW'];
            $vImage = $_POST['driver_req_doc_hidden'] ? $_POST['driver_req_doc_hidden'] : $_POST['driver_doc_hidden'];
        }
                		
		$q = "INSERT INTO ";
		$where = '';
		if (count($db_data) > 0) {
			$query="UPDATE `".$tbl."` SET $doc_fileField='".$vImage."' , $ex_dateField='".$expDate."' WHERE doc_userid='".$_REQUEST[id]."' and doc_usertype='store'  and doc_masterid=".$_REQUEST['doc_type'];
			$q = "UPDATE ";
			$where = " WHERE `iDriverId` = '" . $_REQUEST['id'] . "'";
		} else {

			$query =" INSERT INTO `".$tbl."` ( `doc_masterid`, `doc_usertype`, `doc_userid`, `ex_date`, `doc_file`, `status`, `edate`) " . "VALUES ". "( '".$_REQUEST['doc_type']."', 'store', '".$_REQUEST['id']."', '".$expDate."', '".$vImage."', 'Inactive', CURRENT_TIMESTAMP)";
				
		}   
		$obj->sql_query($query);
		$vNocPath = $vImage;
		$generalobj->save_log_data($_SESSION['sess_iUserId'], $_REQUEST['id'], 'store', 'noc', $vNocPath);
		header("location:dashboard.php?success=1&id=" . $_REQUEST['id'] . "&var_msg=" . $var_msg);
	}
}

$totalItemsQuery = "SELECT count(mi.iMenuItemId) as TotalItems FROM  `food_menu` as f LEFT JOIN menu_items as mi on mi.iFoodMenuId= f.iFoodMenuId WHERE f.iCompanyId = '".$_SESSION['sess_iUserId']."' AND f.eStatus = 'Active' AND mi.eStatus = 'Active' ";
$TotalItemsData = $obj->MySQLSelect($totalItemsQuery);
$TotalItems = $TotalItemsData[0]['TotalItems'];

$OrderArray = array('6','7','8');
$iStatusCode = '('.implode(',',$OrderArray).')';
$sqldata = "SELECT count(iOrderId) as TotalOrderscount FROM orders WHERE iCompanyId = '".$_SESSION['sess_iUserId']."' AND iStatusCode IN $iStatusCode";
$TotalData = $obj->MySQLSelect($sqldata);
$TotalOrderscount = $TotalData[0]['TotalOrderscount'];

$eQuery = "SELECT fCommision, fTotalGenerateFare, fDeliveryCharge, fOffersDiscount, fRestaurantPayAmount, fRestaurantPaidAmount,iStatusCode FROM orders WHERE iCompanyId = '".$_SESSION['sess_iUserId']."' AND iStatusCode IN $iStatusCode";

$TotalOrderData = $obj->MySQLSelect($eQuery);
$TotalResEarning = 0;
foreach ($TotalOrderData as $key => $value) {
	$fCommision = $value['fCommision'];
	$fTotalGenerateFare = $value['fTotalGenerateFare'];
	$fDeliveryCharge = $value['fDeliveryCharge'];
	$fOffersDiscount = $value['fOffersDiscount'];
	$fRestaurantPaidAmount = $value['fRestaurantPaidAmount'];

	if($value['iStatusCode'] == '7' || $value['iStatusCode'] == '8'){
		$fRestearning = $fRestaurantPaidAmount;
	} else {
		$fRestearning = $fTotalGenerateFare - $fCommision - $fDeliveryCharge- $fOffersDiscount;
	}
	$TotalResEarning += $fRestearning;
}

?>
<!DOCTYPE html>
<html lang="en" dir="<?= (isset($_SESSION['eDirectionCode']) && $_SESSION['eDirectionCode'] != "")?$_SESSION['eDirectionCode']:'ltr'; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title><?= $SITE_NAME ?> |<?=$langage_lbl['LBL_DASHBOARD_RESTAURANT']; ?> </title>
    <!-- Start: Default Top Script and css-->   
    <?php include_once("top/top_script.php"); ?>
    <link rel="stylesheet" href="assets/css/bootstrap-fileupload.min.css" >
    <!-- End: Default Top Script and css-->   
</head>
<body>
    <!-- Dashboard Page -->
    <div id="main-uber-page">
        <!-- Left Menu -->
        <?php include_once("top/left_menu.php"); ?>
        <!-- End: Left Menu-->
        <!-- Top Menu -->
        <?php include_once("top/header_topbar.php"); ?>
        <!-- End: Top Menu-->
        <!-- Dashboard Inner Page--> 
        <div class="page-contant">
            <div class="page-contant-inner dashboard-page">
                <h2 class="header-page-p"><?= $langage_lbl['LBL_DASHBOARD_RESTAURANT']; ?></h2>
                <?php if(SITE_TYPE == 'Demo'){ ?>
                	<div class="demo-warning">
						<p><?= $langage_lbl['LBL_WE_SEE_YOU_HAVE_REGISTERED_AS_A_COMPANY_DL']; ?></p>
						<p><?= $langage_lbl['LBL_SINCE_IT_IS_DEMO_VERSION']; ?></p>
						<p><?= $langage_lbl['LBL_STEP1_DL']; ?></p>
						<p><?= $langage_lbl['LBL_STEP3_DL']; ?></p>
						<p><?= $langage_lbl['LBL_STEP2_DL']; ?></p>
						<p><?= $langage_lbl['LBL_HOWEVER_IN_REAL_SYSTEM']; ?></p>
					</div>
				<? } else { ?>
					<!--<div class="demo-warning">
						<p><?= $langage_lbl['LBL_WE_SEE_YOU_HAVE_REGISTERED_AS_A_COMPANY_DL']; ?>
						<? if ($db_user[0]['vCerti'] == '' || $db_user[0]['vNoc'] == ''){ ?>
							<?= $langage_lbl['LBL_KINDLY_PROVIDE_BELOW']; ?>
						<? } ?></p>
						<p><?= $langage_lbl['LBL_STEP1_DL']; ?></p>
						<p><?= $langage_lbl['LBL_STEP3_DL']; ?></p>
						<p><?= $langage_lbl['LBL_STEP2_DL']; ?></p>
					</div>-->
					<?php if ((count($db_userdoc[0]['masterid'])!=count($db_userdoc[0]['masterid_list'])) || $TotalItems==0 || $db_user[0]['eLogout']=="Yes") { ?>
					<div class="demo-warning">
						<p><?= $langage_lbl['LBL_WE_SEE_YOU_HAVE_REGISTERED_AS_A_COMPANY_DL']; ?>
						<? if ($db_user[0]['vCerti'] == '' || $db_user[0]['vNoc'] == ''){ ?>
							<?= $langage_lbl['LBL_KINDLY_PROVIDE_BELOW']; ?>
						<? } ?></p>
						<?php if (count($db_userdoc[0]['masterid'])!=count($db_userdoc[0]['masterid_list'])) { ?><p><?= $langage_lbl['LBL_STEP1_DL']; ?></p><?php }  ?>
						<?php if ($TotalItems==0 && count($db_userdoc[0]['masterid'])!=count($db_userdoc[0]['masterid_list'])) { ?>
							<p><?= $langage_lbl['LBL_STEP3_DL']; ?></p>
						<?php } else if ($TotalItems==0 && count($db_userdoc[0]['masterid'])==count($db_userdoc[0]['masterid_list'])) { ?>
							<p><?= $langage_lbl['LBL_STEP_THREE_DL_MV']; ?></p>
						<?php }  ?>
						<?php if ((count($db_userdoc[0]['masterid'])==count($db_userdoc[0]['masterid_list'])) && $TotalItems>0 && $db_user[0]['eLogout']=="No"){ ?>
							<p><?= $langage_lbl['LBL_STEP2_DL']; ?></p>
								<?php } else if ($db_user[0]['eLogout']=="Yes" && ($TotalItems==0 && count($db_userdoc[0]['masterid'])==count($db_userdoc[0]['masterid_list']))) { ?>
									<p><?= $langage_lbl['LBL_STEP_TWO_DL_MVTWO']; ?></p>
								 <?php } else if ($db_user[0]['eLogout']=="Yes" &&  $TotalItems>0 && count($db_userdoc[0]['masterid'])==count($db_userdoc[0]['masterid_list'])) { ?>
									<p><?= $langage_lbl['LBL_STEP_TWO_DL_MV']; ?></p>
								<?php }  ?>
					</div>
					<?php } } ?>
					<!-- profile page -->
					<div class="driver-profile-page">
						<?php if ($success == 1) { ?>
							<div class="demo-success msgs_hide">
								<button class="demo-close" type="button">×</button>
								<?= $var_msg ?>
							</div>
						<?php } else if($success == 2) { ?>
						<div class="demo-danger msgs_hide">
							<button class="demo-close" type="button">×</button>
							<?= $langage_lbl['LBL_EDIT_DELETE_RECORD']; ?>
						</div>
						<?php } else if($success == 0 && $var_msg != "") { ?>
						<div class="demo-danger msgs_hide">
							<button class="demo-close" type="button">×</button>
							<?= $var_msg; ?>
						</div>
						<?php } ?>
						<div class="driver-profile-top-part <?=$class;?>" id="hide-profile-div">
							<div class="driver-profile-img">
								<span>
									<?php $img_path = $tconfig["tsite_upload_images_compnay"];
									if (($db_user[0]['vImage'] == 'NONE' || $db_user[0]['vImage'] == '') || !file_exists($tconfig["tsite_upload_images_compnay_path"]. '/' . $_SESSION['sess_iUserId'] . '/2_' . $db_user[0]['vImage'])) { ?>
										<img src="assets/img/profile-user-img.png" alt="">
									<?php } else { ?>
										<img src = "<?= $img_path . '/' . $_SESSION['sess_iUserId'] . '/2_' . $db_user[0]['vImage'] ?>" style="height:150px;"/>
									<?php } ?>
								</span>
								<b><a data-toggle="modal" data-target="#uiModal_4"><i class="fa fa-pencil" aria-hidden="true"></i></a></b>
							</div>
							<div class="driver-profile-info">
								<h3><?= $generalobj->cleanall(htmlspecialchars($db_user[0]['vCompany']));?>&nbsp;&nbsp;<?if($db_user[0]['vAvgRating'] != 0){?><b style="font-size: 14px;"><?= $db_user[0]['vAvgRating']?> (<img src="assets/img/star.jpg" alt="">)</b><?php } ?></h3>
								
								<p><?= $db_user[0]['vEmail'] ?></p>
								<?php if($db_user[0]['vCode'] != '') {?>
									<p><?= "(+".$db_user[0]['vCode'].") ".$db_user[0]['vPhone'] ?></p>
								<?php } else { ?>
									<p><?= $db_user[0]['vPhone'] ?></p>
								<?php } ?>
								<span><a href="settings.php"><i class="fa fa-pencil" aria-hidden="true"></i><?= $langage_lbl['LBL_PROFILE_EDIT']; ?></a></span>
							</div>
						</div>

						<div class='driver-profile-mid-part' >
							<ul >
								<li class='company-profile-mid-part-details' >
									<div class="driver-profile-mid-inner">
										<div class="profile-icon"><i class="fa fa-money" aria-hidden="true"></i></div>
										<h3><?= $langage_lbl['LBL_TOTAL_EARNING_FRONT']; ?></h3>
										<p style="font-size: 20px;font-weight: bold;"><?=$generalobj->trip_currency($TotalResEarning);?></p>
									</div> 
								</li>
								<li class='company-profile-mid-part-details'>
									<div class="driver-profile-mid-inner-a">
										<div class="profile-icon"><i class="fa fa-dot-circle-o" aria-hidden="true"></i></div>
										<h3><?= $langage_lbl['LBL_TOTAL_ORDERS']; ?></h3>
										<p style="font-size: 20px;font-weight: bold;"><? echo $TotalOrderscount;?></p>
									</div>
								</li>
								<li class='company-profile-mid-part-details'>
									<div class="driver-profile-mid-inner">
										<div class="profile-icon"><i class="fa fa-list-alt" aria-hidden="true"></i></div>
										<h3><?= $langage_lbl['LBL_TOTAL_ITEMS_FRONT']; ?></h3>
										<p style="font-size: 20px;font-weight: bold;"><?=$TotalItems?></p>
									</div>
								</li>
							</ul>
						</div>
						<?php if($count_all_doc != 0) {?>
							<div class="driver-profile-bottom-part required-documents-bottom-part">
								<h3><?= $langage_lbl['LBL_REQUIRED_DOCS']; ?></h3>
								<div class="profile-req-doc driver-document-action-page">
									<div class="profile-req-doc-inner pro-required">
										<?php for ($i = 0; $i < $count_all_doc; $i++) { ?>
											<div class="panel panel-default upload-clicking">
												<input  type="hidden" id="ex_status" value="<?php echo $db_userdoc[$i]['ex_status']; ?>">
												<div class="panel-heading"><?php echo $db_userdoc[$i]['d_name']; ?> </div>
												<input type="hidden" id="doc_id" value="<?php  $db_userdoc[$i]['doc_file']; ?>">
												<div class="panel-body">
													<label>
														<?php if ($db_userdoc[$i]['doc_file'] != '') { ?>
															<?php
																$file_ext = $generalobj->file_ext($db_userdoc[$i]['doc_file']);
																if ($file_ext == 'is_image') {
																	$path=$tconfig["tsite_upload_compnay_doc"];
																?>
																<a href="<?= $path. '/' .$_SESSION['sess_iUserId'].'/' . $db_userdoc[$i]['doc_file'] ?>" target="_blank"><img src = "<?= $path. '/' .$_SESSION['sess_iUserId']. '/' . $db_userdoc[$i]['doc_file'] ?>" style="width:200px;cursor:pointer;" alt ="<?= $db_userdoc[$i]['d_name']; ?> Image" /></a>
																
																<?php } else { 
																
																	$tconfig=$tconfig["tsite_upload_compnay_doc"];
																?>
																<p><a href="<?= $tconfig. '/' .$_SESSION['sess_iUserId'].'/' . $db_userdoc[$i]['doc_file'] ?>" target="_blank"><?php echo $db_userdoc[$i]['d_name']; ?></a></p>
															<?php } ?>
														<?php } else {
																echo '<p>'.$db_userdoc[$i]['d_name'] . " " .$langage_lbl['LBL_NOT_FOUND'] .'</p>';
															}
														?>
													</label>
													<br/>
													<b><button class="btn btn-info" data-toggle="modal" data-target="#uiModal" id="custId"  onClick="setModel001('<?php echo $db_userdoc[$i]['masterid']; ?>')" >
													<?php
														if ($db_userdoc[$i]['doc_file'] != '') {
															echo $db_userdoc[$i]['d_name'];
														} else {
															echo $db_userdoc[$i]['d_name'];
														}
													?>
													</button></b>
                                                    <?php 
                                                    $Today=Date('Y-m-d');
                                                    
                                                    if($db_userdoc[$i]['ex_date'] != '' && $Today > $db_userdoc[$i]['ex_date'] && $db_userdoc[$i]['ex_status'] == 'yes' && $SET_DRIVER_OFFLINE_AS_DOC_EXPIRED == 'Yes') { ?>
                                                            <div><small><?= $langage_lbl['LBL_DOC_EXPIRED_NOTICE']; ?></small></div>
                                                    <?php } ?>
                                                            													
												</div>
											</div>
										<?php } ?>
									</div>  
								</div>
							</div>
						<?php } ?>
                        <div class="col-lg-12">
							<div class="modal fade" id="uiModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
								<div class="modal-content image-upload-1">
									<div class="fetched-data"></div>
								</div>
							</div>
						</div>				
					</div>
					<div style="clear:both;"></div>
				</div>
			</div>
			<div class="col-lg-12">
				<div class="modal fade" id="uiModal_4" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
					<div class="modal-content image-upload-1 popup-box3">
						<div class="upload-content">
							<h4><?=$langage_lbl['LBL_PROFILE_PICTURE'];?></h4>
							<form class="form-horizontal frm9" id="frm9" method="post" enctype="multipart/form-data" action="upload_doc_company.php" name="frm9">

								<input type="hidden" name="action" value ="photo"/>
								<input type="hidden" name="img_path" value ="<?php echo $tconfig["tsite_upload_images_compnay_path"];?>"/>
								<div class="form-group">
									<div class="col-lg-12">
										<div class="fileupload fileupload-new" data-provides="fileupload">
											<div class="fileupload-preview thumbnail">
												<?php $img_path = $tconfig["tsite_upload_images_compnay"]; 
												if (($db_user[0]['vImage'] == 'NONE' || $db_user[0]['vImage'] == '') || !file_exists($tconfig["tsite_upload_images_compnay_path"]. '/' . $_SESSION['sess_iUserId'] . '/2_' . $db_user[0]['vImage'])) { ?>
													<img src="assets/img/profile-user-img.png" alt="">
													<? } else { ?>
														<img src = "<?= $img_path . '/' . $_SESSION['sess_iUserId'] . '/2_' . $db_user[0]['vImage'] ?>" style="height:150px;"/>
												<?php } ?>
											</div>
											<div>
												<span class="btn btn-file btn-success"><span class="fileupload-new"><?=$langage_lbl['LBL_UPLOAD_PHOTO']; ?></span><span class="fileupload-exists"><?=$langage_lbl['LBL_CHANGE']; ?></span>
													<input type="file" name="photo"/>
													<input type="hidden" name="photo_hidden"  id="photo" value="<?php echo ($db_data[0]['vImage'] !="") ? $db_data[0]['vImage'] : '';?>" />
												</span>
												<a href="#" class="btn btn-danger" id="cancel-btn" data-dismiss="fileupload">X</a>
											</div>
											<div class="upload-error"><span class="file_error"></span></div>
										</div>
									</div>
								</div>
								<input type="submit" class="save" name="save" value="<?=$langage_lbl['LBL_Save']; ?>"><input type="button" class="cancel" data-dismiss="modal" name="cancel" value="<?=$langage_lbl['LBL_BTN_PROFILE_CANCEL_TRIP_TXT']; ?>">
							</form>
							<div style="clear:both;"></div>
						</div>
					</div>
				</div>
			</div>
			
			<div class="col-lg-12">
				<div class="modal fade" id="formModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
					<div class="modal-dialog">
						<div class="modal-content modal-content-profile">
							<div class="modal-header">
								<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
								<h4 class="modal-title" id="H2"><?= $langage_lbl['LBL_NOTE_FOR_DEMO']; ?></h4>
							</div>
							<div class="modal-body">
								<form role="form" name="verification" id="verification">
									<p><?= $langage_lbl['LBL_WE_SEE_YOU_HAVE_REGISTERED_AS_A_COMPANY']; ?></p>
									<p><?= $langage_lbl['LBL_SINCE_IT_IS_DEMO_VERSION']; ?></p>
									<p><?= $langage_lbl['LBL_STEP1_DL']; ?></p>
									<p><?= $langage_lbl['LBL_STEP3_DL']; ?></p>
									<p><?= $langage_lbl['LBL_HOWEVER_IN_REAL_SYSTEM']; ?></p>
									<div class="form-group">
									</div>
									<p class="help-block" id="verification_error"></p>
								</form>
							</div>
							
						</div>
					</div>
				</div>
			</div>
		<!-- footer part -->
		<?php include_once('footer/footer_home.php'); ?>
		<!-- footer part end -->
		<!-- -->
		<div style="clear:both;"></div>
	</div>
	<!-- home page end-->
	<!-- Footer Script -->
	<?php include_once('top/footer_script.php'); 
	$lang = get_langcode($_SESSION['sess_lang']);?>
	<link rel="stylesheet" href="assets/plugins/datepicker/css/datepicker.css" />
	<style>
	.upload-error .help-block{
	color:#b94a48;
	}
	</style>
	<script src="assets/plugins/datepicker/js/bootstrap-datepicker.js"></script> 
	<link rel="stylesheet" href="assets/validation/validatrix.css" />
	<script type="text/javascript" src="assets/plugins/jasny/js/bootstrap-fileupload.js"></script>
	<link rel="stylesheet" type="text/css" media="screen" href="<?= $tconfig["tsite_url_main_admin"]?>css/bootstrap-datetimepicker/bootstrap-datetimepicker.min.css">
	<script type="text/javascript" src="<?= $tconfig["tsite_url_main_admin"]?>js/moment.min.js"></script>
	<script type="text/javascript" src="<?= $tconfig["tsite_url_main_admin"]?>js/bootstrap-datetimepicker.min.js"></script>
	<script type="text/javascript" src="<?= $tconfig["tsite_url_main_admin"]?>js/validation/jquery.validate.min.js" ></script>
	<?php if($lang != 'en') { ?>
	<!-- <script type="text/javascript" src="assets/js/validation/localization/messages_<?= $lang; ?>.js" ></script> -->
	<? include_once('otherlang_validation.php');?>
	<?php } ?>
	<script type="text/javascript" src="assets/js/validation/additional-methods.js" ></script>
	<!-- End: Footer Script -->
	<script type="text/javascript">
		var successMSG1 = '<?php echo $success; ?>';
		if (successMSG1 != '') {
			setTimeout(function () {
				$(".msgs_hide").hide(1000)
			}, 5000);
		}
		
		$(document).ready(function () {

			$('.demo-close').click(function (e) {
				$(this).parent().hide(1000);
			});
			
			var user = '<?= SITE_TYPE; ?>';
			if (user == 'Demo') {
				var a = '<?= $new; ?>';
				if (a != undefined && a != '') {
					$('#formModal').modal('show');
				}
			}
			
			$('[data-toggle="tooltip"]').tooltip();
			
			$('#cancel-btn').on( 'click', function () {
				$('#photo').val('');
			});
			
			$('.frm9').validate({
				ignore: 'input[type=hidden]',
				errorClass: 'help-block',
				errorElement: 'span',
				errorPlacement: function(error, element) {
					if (element.attr("name") == "photo")
					{
						error.insertAfter("span.file_error");
						} else {
						error.insertAfter(element);
					}
				},
				rules: {
					photo: {
						required: {
							depends: function(element) {
								if ($("#photo").val() == "NONE" || $("#photo").val() == "") { 
									return true;
									} else { 
									return false;
								} 
							}
						},
						extension: "jpg|jpeg|png|gif"
					}
				},
				messages: {
					photo: {
						required: '<?= addslashes($langage_lbl['LBL_UPLOAD_IMG']); ?>',
						extension: '<?= addslashes($langage_lbl['LBL_UPLOAD_IMG_ERROR']); ?>'
					}
				}
			});                    
		});
		function setModel001(idVal) {
			
			var id = '<?php echo $_SESSION['sess_iUserId']; ?>';
			var user = '<?php echo $_SESSION['sess_user']; ?>';
			var eSystem = '<?php echo $_SESSION['sess_eSystem']; ?>';

		
			$.ajax({
				type: 'post',
				url: 'company_document_fetch.php', //Here you will fetch records 
				data: 'rowid=' + idVal + '-' + id+'-'+user+'-'+eSystem, //Pass $id
				success: function (data) {
					$('#uiModal').modal('show');
					$('.fetched-data').html(data);//Show fetched data from database 
				}
			});
		}
	</script>
</body>
</html>
