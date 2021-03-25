<?php
include_once('../common.php');

if (!isset($generalobjAdmin)) {
     require_once(TPATH_CLASS . "class.general_admin.php");
     $generalobjAdmin = new General_admin();
}
////$generalobjAdmin->check_member_login();

if(!$userObj->hasPermission('view-rental-packages')){
  $userObj->redirect();
}

if($default_lang == ""){
  $default_lang = "EN";
}

$script = 'Rental Package'; 
//Start Sorting
$id = isset($_GET['id']) ? $_GET['id'] : ''; 
$sortby = isset($_REQUEST['sortby']) ? $_REQUEST['sortby'] : 0;
//$frm_action = isset($_REQUEST['frm_action'])?$_REQUEST['frm_action']:'view';



$order = isset($_REQUEST['order']) ? $_REQUEST['order'] : '';

$tbl_name= 'rental_package';

$iVehicleTypeId = isset($_REQUEST['iVehicleTypeId'])?$_REQUEST['iVehicleTypeId']:''; 
$success = isset($_REQUEST['success']) ? $_REQUEST['success'] : 0;


$fPrice = isset($_POST['fPrice']) ? $_POST['fPrice'] : '';
$fKiloMeter = isset($_POST['fKiloMeter']) ? $_POST['fKiloMeter'] : '';
$fHour = isset($_POST['fHour']) ? $_POST['fHour'] : '';
$fPricePerKM = isset($_POST['fPricePerKM']) ? $_POST['fPricePerKM'] : '';
$fPricePerHour = isset($_POST['fPricePerHour']) ? $_POST['fPricePerHour'] : '';
$new_iVehicleTypeId = isset($_POST['iVehicleTypeId']) ? $_POST['iVehicleTypeId'] : '';

$ord = ' ORDER BY vPackageName_'.$default_lang.' ASC';
if($sortby == 1){
  if($order == 0)
  $ord = " ORDER BY vPackageName_".$default_lang." ASC";
  else
  $ord = " ORDER BY vPackageName_".$default_lang." DESC";
}

if($sortby == 2){
  if($order == 0)
  $ord = " ORDER BY fPrice ASC";
  else
  $ord = " ORDER BY fPrice DESC";
}

if($sortby == 3){
  if($order == 0)
  $ord = " ORDER BY fKiloMeter ASC";
  else
  $ord = " ORDER BY fKiloMeter DESC";
}

if($sortby == 4){
  if($order == 0)
  $ord = " ORDER BY fHour ASC";
  else
  $ord = " ORDER BY fHour DESC";
}
if($sortby == 4){
  if($order == 0)
  $ord = " ORDER BY fPricePerKM ASC";
  else
  $ord = " ORDER BY fPricePerKM DESC";
}
if($sortby == 4){
  if($order == 0)
  $ord = " ORDER BY fPricePerHour ASC";
  else
  $ord = " ORDER BY fPricePerHour DESC";
}

$vTitle_store = array();
	$sql = "SELECT * FROM `language_master` where eStatus='Active' ORDER BY `iDispOrder`";
	$db_master = $obj->MySQLSelect($sql);
	$count_all = count($db_master);
	 if ($count_all > 0) {
		for ($j = 0; $j < $count_all; $j++) {
			$vValue = 'vPackageName_'. $db_master[$j]['vCode'];
			array_push($vTitle_store, $vValue);
			$$vValue = isset($_POST[$vValue]) ? $_POST[$vValue] : '';
		}
}
if (isset($_POST['btnsubmitnew'])) {	
		$iRentalPackageId = isset($_POST['iRentalPackageId'])?$_POST['iRentalPackageId']:''; 
		require_once("library/validation.class.php");
		$validobj = new validation();
		$validobj->add_fields($_POST['fPrice'], 'req', 'Price is required.');
		$validobj->add_fields($_POST['fKiloMeter'], 'req', 'KiloMeter is required.');
		$validobj->add_fields($_POST['fHour'], 'req', 'Hour is required.');
		$validobj->add_fields($_POST['fPricePerKM'], 'req', 'Price Per KiloMeter plate Id is required.');
		$validobj->add_fields($_POST['fPricePerHour'], 'req', 'Price Per Hour is required.');
		 $error = $validobj->validate();
		if ($error) {
        $success = 3;
        $newError = $error;
    }else{
		for ($k = 0; $k < count($vTitle_store); $k++) {
		
		if(SITE_TYPE =='Demo'){
      $_SESSION['success'] = 2;
      header("Location:rental_package.php?id=".$id);exit;
    }
			$vValue = 'vPackageName_' . $db_master[$k]['vCode'];
			
			$q = "INSERT INTO ";
			$where = '';
			if ($iRentalPackageId != '') {

				$q = "UPDATE ";
				$where = " WHERE `iRentalPackageId` = '" . $iRentalPackageId . "'";
			}
				$query = $q . " `" . $tbl_name . "` SET
				`iVehicleTypeId` = '" . $new_iVehicleTypeId . "',
				`fPrice` = '" . $fPrice . "',
				`fKiloMeter` = '" . $fKiloMeter . "',				
				`fHour` = '" . $fHour . "',				
				`fPricePerKM` = '" . $fPricePerKM . "',				
				`fPricePerHour` = '" . $fPricePerHour . "',				
				" . $vValue . " = '" . $_POST[$vTitle_store[$k]] . "'"
				. $where;
			$obj->sql_query($query);
			$iRentalPackageId = ($iRentalPackageId != '') ? $iRentalPackageId : $obj->GetInsertId();		
		}
	
	}
}
//Pagination Start
$per_page = $DISPLAY_RECORD_NUMBER; // number of results to show per page
$sql="";
$sql = "SELECT count(iRentalPackageId) as Total from rental_package where iVehicleTypeId='".$id."'";
$totalData = $obj->MySQLSelect($sql);
$total_results = $totalData[0]['Total'];
$total_pages = ceil($total_results / $per_page); //total pages we going to have
$show_page = 1;

//-------------if page is setcheck------------------//
if (isset($_GET['page'])) {
    $show_page = $_GET['page'];             //it will telles the current page
    if ($show_page > 0 && $show_page <= $total_pages) {
        $start = ($show_page - 1) * $per_page;
        $end = $start + $per_page;
    } else {
        // error - show first set of results
        $start = 0;
        $end = $per_page;
    }
} else {
    // if page isn't set, show first set of results
    $start = 0;
    $end = $per_page;
}
// display pagination
$page = isset($_GET['page']) ? intval($_GET['page']) : 0;
$tpages=$total_pages;
if ($page <= 0)
    $page = 1;
//Pagination End

 $sql="";

$sql = "SELECT rp.iRentalPackageId, rp.iVehicleTypeId, rp.vPackageName_".$default_lang." as packageName, rp.fPrice, rp.fKiloMeter, rp.fHour, rp.fPricePerKM, rp.fPricePerHour ,vt.vVehicleType FROM rental_package as rp LEFT JOIN vehicle_type as vt ON vt.iVehicleTypeId=rp.iVehicleTypeId  WHERE rp.iVehicleTypeId = '".$id."' $ssql $ord LIMIT $start, $per_page";
$data_drv = $obj->MySQLSelect($sql);

$query = "SELECT vVehicleType,iLocationid FROM vehicle_type WHERE iVehicleTypeId = '".$id."'";
$data_main = $obj->MySQLSelect($query);

$endRecord = count($data_drv);
$var_filter = "";
foreach ($_REQUEST as $key=>$val)
{
    if($key != "tpages" && $key != 'page')
    $var_filter.= "&$key=".stripslashes($val);
}
$reload = $_SERVER['PHP_SELF'] . "?tpages=" . $tpages.$var_filter;

$sql = "select vName,vSymbol from currency where eDefault = 'Yes'";
$db_currency = $obj->MySQLSelect($sql);
?>
<!DOCTYPE html>
<!--[if IE 8]> <html lang="en" class="ie8"> <![endif]-->
<!--[if IE 9]> <html lang="en" class="ie9"> <![endif]-->
<!--[if !IE]><!--> <html lang="en"> <!--<![endif]-->

     <!-- BEGIN HEAD-->
     <head>
          <meta charset="UTF-8" />
          <title><?=$SITE_NAME;?> | Rental Packages </title>
          <meta content="width=device-width, initial-scale=1.0" name="viewport" />
          <link href="../assets/plugins/dataTables/dataTables.bootstrap.css" rel="stylesheet" />
          <? include_once('global_files.php');?>
     </head>
     <!-- END  HEAD-->
     <!-- BEGIN BODY-->
    <body class="padTop53">
        <!-- Main LOading -->
        <!-- MAIN WRAPPER -->
        <div id="wrap">
            <?php include_once('header.php'); ?>
            <?php include_once('left_menu.php'); ?>
            <!--PAGE CONTENT -->
            <div id="content">
                <div class="inner">
                    <div id="add-hide-show-div">
                        <div class="row">
                            <div class="col-lg-12">
                                <h2><?php echo $data_main[0]['vVehicleType'];?> Rental Packages</h2>
                            </div>
                        </div>
                        <hr />
                    </div>
                    <? if ($success == 3) {?>
                            <div class="alert alert-danger alert-dismissable">
                                <button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>
                            <?php print_r($error); ?>
                            </div><br/>
                            <?} ?>
                <form name="frmsearch" id="frmsearch" action="javascript:void(0);" method="post">
                  <table width="100%" border="0" cellpadding="0" cellspacing="0" class="admin-nir-table">
                  <tbody>
                  <tr>
                  <td width="30%"><a href="rental_vehicle_list.php" class="back_link">
                      <input type="button" value="Back to Listing" class="add-btn">
                      <?php if($userObj->hasPermission('create-rental-packages')){ ?>
                        </a><a id="flip" class="add-btn" href="javascript:void(0)" onClick="show_package_detail('<?=$id;?>','Add')" style="text-align: center;">Add new Package</a></td>
                      <?php } ?> 
                  </tr>
                  </tbody>
                  </table>
                </form>
                <div class="admin-nir-export">
                      <div class="changeStatus col-lg-12 option-box-left">
                      <span class="col-lg-2 new-select001">
                        <?php if($userObj->hasPermission('delete-rental-packages')){ ?>
                              <select name="changeStatus" id="changeStatus" class="form-control" onChange="ChangeStatusAll(this.value);">
                                      <option value="" >Select Action</option>
                                      <option value="Deleted" <?php if ($option == 'Delete') {echo "selected"; } ?> >Delete</option>
                              </select>
                        <?php } ?>
                      </span>
                      </div>
                      <?php if(!empty($data_drv)) { ?>
                      <!--<div class="panel-heading">
                          <form name="_export_form" id="_export_form" method="post" >
                              <button type="button" onclick="showExportTypes('vehicle_rental_package')" >Export</button>
                          </form>
                     </div>-->
                     <?php }?>
                </div>
                <div style="clear:both;"></div>
                <?php include('valid_msg.php'); ?>
                    <div class="table-list">
                        <div class="row">
                            <div class="col-lg-12">
                              <div class="table-responsive">
                              <form class="_list_form" id="_list_form" method="post" action="<?php echo $_SERVER['PHP_SELF'] ?>">
                              <table class="table table-striped table-bordered table-hover">
                              <thead>
                              <tr>
                              <th align="center" width="3%" style="text-align:center;"><input type="checkbox" id="setAllCheck" ></th>

                              <th width="15%"><a href="javascript:void(0);" onClick="Redirect(1,<?php if($sortby == '1'){ echo $order; }else { ?>0<?php } ?>)">Package Name<?php if ($sortby == 1) { if($order == 0) { ?><i class="fa fa-sort-amount-asc" aria-hidden="true"></i> <?php } else { ?><i class="fa fa-sort-amount-desc" aria-hidden="true"></i><?php } }else { ?>  <i class="fa fa-sort" aria-hidden="true"></i> <?php } ?></a></th>   
														
														 <th width="15%"><a href="javascript:void(0);" onClick="Redirect(2,<?php if($sortby == '2'){ echo $order; }else { ?>0<?php } ?>)">Rental Total Price(In <?=$db_currency[0]['vName']?>) <?php if ($sortby == 2) { if($order == 0) { ?><i class="fa fa-sort-amount-asc" aria-hidden="true"></i> <?php } else { ?><i class="fa fa-sort-amount-desc" aria-hidden="true"></i><?php } }else { ?>  <i class="fa fa-sort" aria-hidden="true"></i> <?php } ?></a></th>
														 
														  <th width="15%"><a href="javascript:void(0);" onClick="Redirect(3,<?php if($sortby == '3'){ echo $order; }else { ?>0<?php } ?>)">Rental <em class="change_eUnit" style="font-style: normal"><?=$DEFAULT_DISTANCE_UNIT;?><?php if ($sortby == 3) { if($order == 0) { ?><i class="fa fa-sort-amount-asc" aria-hidden="true"></i> <?php } else { ?><i class="fa fa-sort-amount-desc" aria-hidden="true"></i><?php } }else { ?>  <i class="fa fa-sort" aria-hidden="true"></i> <?php } ?></a></th>
														  
														   <th width="15%"><a href="javascript:void(0);" onClick="Redirect(4,<?php if($sortby == '4'){ echo $order; }else { ?>0<?php } ?>)">Rental Hour<?php if ($sortby == 4) { if($order == 0) { ?><i class="fa fa-sort-amount-asc" aria-hidden="true"></i> <?php } else { ?><i class="fa fa-sort-amount-desc" aria-hidden="true"></i><?php } }else { ?>  <i class="fa fa-sort" aria-hidden="true"></i> <?php } ?></a></th>
														   
														   <th width="15%"><a href="javascript:void(0);" onClick="Redirect(5,<?php if($sortby == '5'){ echo $order; }else { ?>0<?php } ?>)">Additional Price/<em class="change_eUnit" style="font-style: normal"><?=$DEFAULT_DISTANCE_UNIT;?> <br/>(In <?=$db_currency[0]['vName']?>) <?php if ($sortby == 5) { if($order == 0) { ?><i class="fa fa-sort-amount-asc" aria-hidden="true"></i> <?php } else { ?><i class="fa fa-sort-amount-desc" aria-hidden="true"></i><?php } }else { ?>  <i class="fa fa-sort" aria-hidden="true"></i> <?php } ?></a></th>
														   
														    <th width="15%"><a href="javascript:void(0);" onClick="Redirect(6,<?php if($sortby == '6'){ echo $order; }else { ?>0<?php } ?>)">Additional Price/Min <br/>(In <?=$db_currency[0]['vName']?>) <?php if ($sortby == 6) { if($order == 0) { ?><i class="fa fa-sort-amount-asc" aria-hidden="true"></i> <?php } else { ?><i class="fa fa-sort-amount-desc" aria-hidden="true"></i><?php } }else { ?>  <i class="fa fa-sort" aria-hidden="true"></i> <?php } ?></a></th>                                          
														<th width="8%" align="center" style="text-align:center;">Action</th>
                              </tr>
                          </thead>
                         <tbody>
												<?php 
												if(!empty($data_drv)) {
														for ($i = 0; $i < count($data_drv); $i++) {
															$sql = "SELECT count(iRentalPackageId) AS TotalPackage from  rental_package where iVehicleTypeId = '".$data_drv[$i]['iVehicleTypeId']."'";
															$rental_package = $obj->MySQLSelect($sql);
															$total_rental_package = $rental_package[0]['TotalPackage'];
															?>
                              <tr class="gradeA">
                                <td style="text-align:center;"><input type="checkbox" id="checkbox" name="checkbox[]" <?php echo $default; ?> value="<?php echo $data_drv[$i]['iRentalPackageId']; ?>" />&nbsp;</td>
																<td><?= $data_drv[$i]['packageName'] ?></td>
																<td><?= $data_drv[$i]['fPrice'] ?></td> 
																<td><?= $data_drv[$i]['fKiloMeter'] ?></td> 
																<td><?= $data_drv[$i]['fHour'] ?></td> 
																<td><?= $data_drv[$i]['fPricePerKM'] ?></td> 
																<td><?= $data_drv[$i]['fPricePerHour'] ?></td>
																<td align="center" style="text-align:center;" class="action-btn001">
																	<div class="share-button share-button-new openHoverAction-class" style="display: block;">
                                    <label class="entypo-export"><span><img src="images/settings-icon.png" alt=""></span></label>
                                    <div class="social show-moreOptions for-two openPops_<?= $data_drv[$i]['iRentalPackageId']; ?>">
                                        <ul>
                                            <li class="entypo-twitter" data-network="twitter"><a href="javascript:void(0);" onClick="show_package_detail('<?=$id;?>','Edit','<?php echo $data_drv[$i]['iRentalPackageId']?>')" data-toggle="tooltip" title="Edit">
                                                <img src="img/edit-icon.png" alt="Edit">
                                            </a></li>
                                            <?php if($userObj->hasPermission('delete-rental-packages')){ ?>
                                              <li class="entypo-gplus" data-network="gplus"><a href="javascript:void(0);" onClick="changeStatusDelete('<?php echo $data_drv[$i]['iRentalPackageId']; ?>')"data-toggle="tooltip" title="Delete">
                                                  <img src="img/delete-icon.png" alt="Delete" >
                                              </a></li>
                                            <?php } ?>
                                        </ul>
                                    </div>
                                </div>
															</td>
														 </tr>    
														<? } } else {?>
														<tr class="gradeA">
															<td colspan="7"> No Records Found.</td>
														</tr>
														<?php } ?>
													</tbody>
                          </table>
                      </form>
                      <?php include('pagination_n.php'); ?>
              </div>
              <div class="admin-notes">
                     <h4>Notes:</h4>
                     <ul>
                          <li>Commision for the car rental will be the commission for the vehicle type </li>
                          <li>Please enter minimum package duration to be 1 hour</li>
                          <li>Package hours can be round number like (2 hour), and it cannot be half past values like( 2:30).</li>
                          <li>Surge prices are not applicable to the car rental trips.Also, waiting charges and
  cancellation charges are applied to the rent rate if applicable to vehicle type.</li>
                      </ul> 
              </div>
									<div class="ddmo">
									<div class="ddmo2" style="display:none;" id="rental_package1">
                    <div>
										<div class="body-div">
                      <img src="img/cancel-icon.png" alt="" class="cancel-icon" onClick="close_popup();">
											<div class="form-group">
												<form id="rental_package" name="rental_package" method="post" action="rental_package.php?id=<?php echo $id;?>" enctype="multipart/form-data" >			  
												   <input type="hidden" name="iVehicleTypeId" value="<?php echo $id; ?>">
												   <input type="hidden" id= "iRentalPackageId" name="iRentalPackageId" value="">
												
												  
												   <input type="hidden" name="frm_action" value="<?php echo $frm_action ?>">		
												   <?
														if($count_all > 0) {
														for($i=0;$i<$count_all;$i++) {
														$vCode = $db_master[$i]['vCode'];
														$vTitle = $db_master[$i]['vTitle'];
														$eDefault = $db_master[$i]['eDefault'];

														$vValue = 'vPackageName_'.$vCode;
														$default_value = 'vPackageName_'.$default_lang;

													//	$required = ($eDefault == 'Yes')?'required':'';
														$required_msg = ($eDefault == 'Yes')?'<span class="red"> *</span>':'';
														?>  
														<div class="row">
															<div class="col-lg-12">
																<label><?php echo $langage_lbl_admin['LBL_RENTAL_TYPE_SMALL_TXT']; ?> (<?= $vTitle; ?>) <?php echo $required_msg; ?></label>

															</div>
															<div class="col-lg-6">
																<input type="text" class="form-control" name="<?= $vValue; ?>" id="<?= $vValue; ?>" value="" placeholder="<?= $vTitle; ?>" <?= $required; ?>>

															</div>
															<?		 
																if($vCode == $default_lang  && count($db_master) > 1){
																?>
																<div class="col-lg-6">
																	<button type ="button" name="allLanguage" id="allLanguage" class="btn btn-primary" onClick="getAllLanguageCode();">Convert To All Language</button>
																</div>
															
															  <?php
															  }
															  ?>
														</div>
														<? }
														} ?>
														 <div class="row">
															<div class="col-lg-12">
																<label>Rental Total Price  (In <?=$db_currency[0]['vName']?>) <span class="red"> *</span></label>
															</div>
															<div class="col-lg-6">
																<input type="text" class="form-control" name="fPrice"  id="fPrice" value="" onkeypress="return isNumberKey(event)"> 

															</div>
														</div>
														 <div class="row">
															<div class="col-lg-12">
																<label>Rental <em class="change_eUnit" style="font-style: normal"><?=$DEFAULT_DISTANCE_UNIT;?></em><span class="red"> *</span></label>
															</div>
															<div class="col-lg-6">
																<input type="text" class="form-control" name="fKiloMeter"  id="fKiloMeter" value="" onkeypress="return isNumberKey(event)"> 
																</div>
														</div>
														 <div class="row">
															<div class="col-lg-12">
																<label>Rental Hour<span class="red"> *</span></label>
															</div>
															<div class="col-lg-6">
																<input type="number" class="form-control" name="fHour"  id="fHour" value="" min="1"  step="1" onkeypress="return event.charCode >= 48 && event.charCode <= 57" ></div>
                                Note : Enter minimum package duration to be 1 hour.
														</div>
														 <div class="row">
															<div class="col-lg-12">
																<label>Additional Price Per <em class="change_eUnit" style="font-style: normal"><?=$DEFAULT_DISTANCE_UNIT;?></em> (In <?=$db_currency[0]['vName']?>) <span class="red"> *</span></label>
															</div>
															<div class="col-lg-6">
																<input type="text" class="form-control" name="fPricePerKM"  id="fPricePerKM" value="" onkeypress="return isNumberKey(event)">
																</div>
														</div>
														<div class="row">
															<div class="col-lg-12">
																<label>Additional Price Per Min (In <?=$db_currency[0]['vName']?>) <span class="red"> *</span></label>
															</div>
															<div class="col-lg-6">
																<input type="text" class="form-control" name="fPricePerHour"  id="fPricePerHour" value="" onkeypress="return isNumberKey(event)">
																</div>
														</div>
														<div class="col-lg-12">
                              <?php if($userObj->hasPermission(['create-rental-packages', 'edit-rental-packages'])){ ?>
														  <input type="submit" class="btn btn-default" name="btnsubmitnew" id="btnsubmit"  value="Add Rental Package" >
                              <?php  } ?>
													</div>
												</form>
												</div>
												 <div class="row loding-action" id="imageIcon" style="display:none;">
													<div align="center">                                           
														<img src="default.gif">
														<span>Language Translation is in Process. Please Wait...</span>                       
													</div>           
												</div>
											</div>
										</div> 
                      <div style="clear:both;"></div>
                    </div>
            </div> <!--TABLE-END-->
        </div>
    </div>

</div>
</div>
<!--END PAGE CONTENT -->
</div>
<!--END MAIN WRAPPER -->
			
			

<form name="pageForm" id="pageForm" action="action/rental_package.php" method="post" >
  <input type="hidden" name="page" id="page" value="<?php echo $page; ?>">
  <input type="hidden" name="tpages" id="tpages" value="<?php echo $tpages; ?>">
  <input type="hidden" name="iRentalPackageId" id="iMainId01" value="" >
  <input type="hidden" name="status" id="status01" value="" >
  <input type="hidden" name="eType" id="eType" value="<?php echo $eType; ?>" >
  <input type="hidden" name="statusVal" id="statusVal" value="" >
  <input type="hidden" name="option" value="<?php echo $option; ?>" >
  <input type="hidden" name="keyword" value="<?php echo $keyword; ?>" >
  <input type="hidden" name="id" id="id" value="<?php echo $id ?>" >
  <input type="hidden" name="sortby" id="sortby" value="<?php echo $sortby; ?>" >
  <input type="hidden" name="order" id="order" value="<?php echo $order; ?>" >
  <input type="hidden" name="method" id="method" value="" >
</form>

<?php
include_once('footer.php');
?>
	
<script>
				
			$('#rental_package').validate({	
		
				rules: {
						<?php echo $default_value; ?>: {
							required: true
						},
						fPrice: {
							required: true
						},
						fKiloMeter: {
							required: true
						},
						fHour: {
							required: true
						},
						fPricePerKM: {
							required: true
						},
						fPricePerHour: {
							required: true
						}
					}
			});

					

function close_popup(){
	$("#rental_package1").slideUp('slow');	
	 $("#iRentalPackageId").val("");
	 $('#btnsubmit').val("Add Rental Package");
		
}
function show_package_detail(id,action,iRentalPackageId=''){
	$("#rental_package1").slideDown('slow');
	if(action == 'Edit'){
		if(id != ""){	
		$(".error").html("");
			var request = $.ajax({
					type: "POST",
					url: "ajax_rental_package_detail.php",
					data: {iRentalPackageId:iRentalPackageId,iVehicleTypeId:id,action:action},
					datatype: "json",
					success: function(data){			
					$('#rental_package input').each(
						function(index) {  
							var input = $(this);
							var filedsname = input.attr('name');									
							var obj = jQuery.parseJSON(data);
							$.each(obj, function(key,value) {
								if(filedsname == key){
									$('#'+key).val(value);
									$('#'+iRentalPackageId).val(iRentalPackageId);
								}
							}); 
							$('#btnsubmit').val("Update");
						}
					);					
				}
			});
		}		
	}else{
    $("#rental_package")[0].reset();
  }
  changeCode_distance('<?= $data_main[0]['iLocationid'];?>');
}		
function getAllLanguageCode(){
    var def_lang = '<?=$default_lang?>';
	  var def_lang_name = '<?=$def_lang_name?>';
    var getEnglishText = $('#vPackageName_'+def_lang).val();
    var error = false;
    var msg = '';
      
    if(getEnglishText==''){
      msg += '<div class="alert alert-danger fade in"><a href="#" class="close" data-dismiss="alert"><icon class="fa fa-close"></icon></a><strong>Please Enter '+def_lang_name+' Value</strong></div> <br>';
      error = true;
    }
      
    if(error==true){
      $('#errorMessage').html(msg);
      return false;
    } else {
      $('#imageIcon').show();
      $.ajax({
          url: "ajax_get_all_language_translate.php",
          type: "post",
          data: {'englishText':getEnglishText},
          dataType:'json',
          success:function(response){
		          // $("#vVehicleType_EN").val(getEnglishText);
            $.each(response,function(name, Value){
              var key = name.split('_');
              $('#vPackageName_'+key[1]).val(Value);
            });
            $('#imageIcon').hide();
          }
      });
    }
}

$(document).ready(function() {  
	$('#eType_options').hide(); 
	$('#option').each(function(){
	  if (this.value == 'vt.eType') {
		  $('#eType_options').show(); 
		  $('.searchform').hide(); 
	  }
	});
});    

$('.entypo-export').click(function(e){
	 e.stopPropagation();
	 var $this = $(this).parent().find('div');
	 $(".openHoverAction-class div").not($this).removeClass('active');
	 $this.toggleClass('active');
});

$(document).on("click", function(e) {
	if ($(e.target).is(".openHoverAction-class,.show-moreOptions,.entypo-export") === false) {
	  $(".show-moreOptions").removeClass("active");
	}
});
$(document).ready(function() { 
  changeCode_distance('<?= $data_main[0]['iLocationid'];?>');
});

$("#setAllCheck").on('click',function(){
    if($(this).prop("checked")) {
        jQuery("#_list_form input[type=checkbox]").each(function() {
            if($(this).attr('disabled') != 'disabled'){ this.checked = 'true'; }
        });
    }else {
        jQuery("#_list_form input[type=checkbox]").each(function() { this.checked = ''; });
    }
});

function changeCode_distance(id) {
	$.ajax({
		type: "POST",
		url: 'ajax_get_unit.php',
		data: {id: id},
		success: function (dataHTML2)
		{
			if(dataHTML2 != null) {
				if(dataHTML2 == 'KMs'){
					$(".change_eUnit").text('KiloMeter');
				} else {
					$(".change_eUnit").text(dataHTML2);
				}
			}
			
		}
	});
}

 function isNumberKey(evt)
 {
    var charCode = (evt.which) ? evt.which : evt.keyCode;
    if (charCode != 46 && charCode > 31 
      && (charCode < 48 || charCode > 57))
       return false;

    return true;
 }
</script>
    </body>
     <!-- END BODY-->    
</html>