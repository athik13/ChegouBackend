<?
include_once("../common.php");

if (!isset($generalobjAdmin)) {
    require_once(TPATH_CLASS . "class.general_admin.php");
    $generalobjAdmin = new General_admin();
}
////$generalobjAdmin->check_member_login();

$iCompanyId = isset($_REQUEST['iCompanyId'])?$_REQUEST['iCompanyId']:''; 

// $sql="select cmp.* from company where iCompanyId = '$iCompanyId'";

$sql="select cmp.*,cn.vCountry as country,ct.vCity as city,st.vState as state from company cmp left join country cn on cn.vCountryCode = cmp.vCountry left join city ct on ct.iCityId = cmp.vCity left join state st on st.iStateId = cmp.vState where iCompanyId = '$iCompanyId'";
$data_company = $obj->MySQLSelect($sql);

$reg_date1 = $data_company[0]['tRegistrationDate'];
// Tuesday, Aug  22<sup>nd</sup> 2017
if($reg_date1 != "0000-00-00 00:00:00"){
	$reg_date = date("l, M d \<\s\u\p\>S\<\/\s\u\p\>\ Y",strtotime($reg_date1));
}else{
	$reg_date = "";
}

if($data_company[0]['vImage'] != "")
	$image_path = $tconfig["tsite_url"].'resizeImg.php?h=300&src='.$tconfig["tsite_upload_images_compnay"].'/'.$iCompanyId.'/'.$data_company[0]['vImage'];
else{
	$image_path = "../assets/img/profile-user-img.png";
}

$rating_width = ($data_company[0]['vAvgRating'] * 100) / 5;
if($data_company[0]['vAvgRating'] > 0){
	$Rating = '<span title="'.$data_company[0]['vAvgRating'].'" style="display: block; width: 65px; height: 13px; background: url('.$tconfig['tsite_upload_images'].'star-rating-sprite.png) 0 0;">
	<span style="margin: 0;float:left;display: block; width: '.$rating_width.'%; height: 13px; background: url('.$tconfig['tsite_upload_images'].'star-rating-sprite.png) 0 -13px;"></span>
	</span>';
}else{
	$Rating = "No ratings received";
}
?>
<style>
.text_design{
	font-size: 12px;
	font-weight: bold;
	font-family: verdana;
}
.border_table{
	border:1px solid #dddddd;
}
.no-cursor{
    cursor: text;
}
</style>
<!--<div class="modal-dialog">
<div class="modal-content">
<div class="modal-header">
	<h4><i aria-hidden="true" class="fa fa-building-o" style="margin:2px 5px 0 2px;"></i>Company Details
	<button type="button" class="close" data-dismiss="modal">x</button>
	</h4>
</div>
<div class="modal-body" style="max-height: 450px;overflow: auto;"> -->


	<table border="1" class="table table-bordered" width="100%" align="center" cellspacing="5" cellpadding="10px">
		<tbody>
		<tr>
			<td rowspan="3" height="150px" width="150px" ><img width="150px" src="<?=$image_path?>"></td>
			<td>
				<table border="0" width="100%" height="150px" cellspacing="5" cellpadding="5px">
					<tr>
						<td width="140px" class="text_design"><?php echo $langage_lbl_admin['LBL_RESTAURANT_TXT_ADMIN']; ?> Name</td>
						<td><?=$generalobjAdmin->clearCmpName($data_company[0]['vCompany'])?></td>
					</tr>
					<tr>
						<td class="text_design">Email</td>
						<td><?=$generalobjAdmin->clearEmail($data_company[0]['vEmail'])?></td>
					</tr>
					
					<tr>
						<td class="text_design">Phone Number</td>
						<td>
							<?php
								$phone = "+";
								if($data_company[0]['vCode'] != ""){
									$phone .= $data_company[0]['vCode']."-";
								}
								$phone .= $data_company[0]['vPhone'];
								echo $generalobjAdmin->clearPhone($phone);
							?>
						</td>
					</tr>
					<tr>
						<td class="text_design">Rating</td>
						<td><?=$Rating?></td>
					</tr>
					<? if($reg_date != ""){?>
					<tr>
						<td class="text_design">Registration Date</td>
						<!-- <td>Tuesday, Aug  22<sup>nd</sup> 2017</td> -->
						<td><?=$reg_date?></td>
					</tr>
					<? } ?>
					<tr>
						<td class="text_design">Status</td>
						<td>
							<?
								$class="";
								if($data_company[0]['eStatus'] == "Active"){
									$class = "btn-success";
								}else if($data_company[0]['eStatus'] == "Inactive"){
									$class = "btn";
								}else{
									$class = "btn-danger";
								}
							?>
							<button class="btn <?=$class?> no-cursor"><?=$data_company[0]['eStatus']?></button>
						</td>
					</tr>
					
				</table>
			</td>
		</tr><tr></tr><tr></tr><tr></tr>
		<tr>
			<td class="text_design"><?php echo $langage_lbl_admin['LBL_RESTAURANT_TXT_ADMIN']; ?>  Address</td>
			<td>
				<?
					$address1 = $data_company[0]['vCaddress'];
					if($data_company[0]['vCadress2'] != ""){
						$conc = ($address1 != "") ? ", " : "";
						$address1 .= $conc.$data_company[0]['vCadress2'];
					}
					if($data_company[0]['city'] != ""){
						$conc = ($address1 != "") ? ", " : "";
						$address1 .= $conc.$data_company[0]['city'];
					}
					if($data_company[0]['vZip'] != ""){
						$conc = ($address1 != "") ? ", " : "";
						$address1 .= $conc.$data_company[0]['vZip'];
					}
					if($data_company[0]['state'] != ""){
						$conc = ($address1 != "") ? ", " : "";
						$address1 .= $conc.$data_company[0]['state'];
					}
					
					if($data_company[0]['country'] != ""){
						$conc = ($address1 != "") ? ", " : "";
						$address1 .= $conc.$data_company[0]['country'];
					}
					echo $address1;
				?>	
			</td>
		</tr>
		<? if($data_company[0]['vVat'] != ""){?>
		<tr>
			<td class="text_design">Vat Number</td>
			<td>
				<?=$data_company[0]['vVat'];?>
			</td>
		</tr>
		<? } ?>
		
		</tbody>
	</table>
</div>
<div style="margin-top: 20px; text-align: right;">
	<a href="store_action.php?id=<?=$iCompanyId; ?>" class="btn btn-primary btn-ok" target="blank">Edit</a>
	<button type="button" class="btn btn-danger btn-ok" data-dismiss="modal">Close</button>
</div>
				
				
	<!--</div>
	 </div> -->