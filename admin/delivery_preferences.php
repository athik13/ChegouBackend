<?php
    include_once('../common.php');
    
    if (!isset($generalobjAdmin)) {
        require_once(TPATH_CLASS . "class.general_admin.php");
        $generalobjAdmin = new General_admin();
    }

    if(!$userObj->hasPermission('view-delivery-preference')){
      	$userObj->redirect();
    }
    
    $script = 'DeliveryPreferences';
    $tbl_name = 'delivery_preferences';
    
    if ($default_lang == "") {
        $default_language = 'EN';
    }else{
        $default_language = $default_lang;
    }
    
    //Start Sorting
    $sortby = isset($_REQUEST['sortby']) ? $_REQUEST['sortby'] : 0;
    $order = isset($_REQUEST['order']) ? $_REQUEST['order'] : '';

    $ord = ' ORDER BY iDisplayOrder ASC';
    if($sortby == 1){
      if($order == 0)
      $ord = " ORDER BY iDisplayOrder ASC";
      else
      $ord = " ORDER BY iDisplayOrder DESC";
    }
    
    /*if ($sortby == 2) {
	    if ($order == 0) {
	        $ord = " ORDER BY iDisplayOrder ASC";
	    } else {
	        $ord = " ORDER BY iDisplayOrder DESC";
	    }
	}

    if($sortby == 3){
      if($order == 0)
      $ord = " ORDER BY eStatus ASC";
      else
      $ord = " ORDER BY eStatus DESC";
    }*/
    
    // Start Search Parameters
	$option = isset($_REQUEST['option']) ? stripslashes($_REQUEST['option']) : "";
	$keyword = isset($_REQUEST['keyword']) ? stripslashes($_REQUEST['keyword']) : "";
	$eStatus = isset($_REQUEST['eStatus']) ? $_REQUEST['eStatus'] : "";
	$ePreferenceFor = isset($_REQUEST['ePreferenceFor']) ? $_REQUEST['ePreferenceFor'] : "";
	$ssql = '';
	if($keyword != ''){ 
	    $keyword_new = $keyword;
	    $chracters = array("(", "+", ")");
	    $removespacekeyword =  preg_replace('/\s+/', '', $keyword);
	    $keyword_new = trim(str_replace($chracters, "", $removespacekeyword));
	    if(is_numeric($keyword_new)){
	      $keyword_new = $keyword_new;
	    } else {
	      $keyword_new = $keyword;
	    }  
	    if($option != '') {
			if($ePreferenceFor != "" && $eStatus != "")
			{
				$ssql .= " WHERE (" . stripslashes($option) . " LIKE '%" . $generalobjAdmin->clean($keyword_new) . "%' AND ePreferenceFor = '".$ePreferenceFor."' AND eStatus = '" . $generalobjAdmin->clean($eStatus) . "')";
			}
			elseif ($ePreferenceFor != "" && $eStatus == "") {
				$ssql .= " WHERE (" . stripslashes($option) . " LIKE '%" . $generalobjAdmin->clean($keyword_new) . "%' AND ePreferenceFor = '".$ePreferenceFor."')";
			}
			elseif ($ePreferenceFor == "" && $eStatus != "") {
				$ssql .= " WHERE (" . stripslashes($option) . " LIKE '%" . $generalobjAdmin->clean($keyword_new) . "%' AND eStatus = '" . $generalobjAdmin->clean($eStatus) . "')";
			}
			else {
				$ssql .= " WHERE (" . stripslashes($option) . " LIKE '%" . $generalobjAdmin->clean($keyword_new) . "%')";
			}
	    }else {
	    	if($ePreferenceFor != "" && $eStatus != "")
			{
				$ssql .= " WHERE ((tTitle LIKE '%" . $generalobjAdmin->clean($keyword_new) . "%' OR tDescription LIKE '%" . $generalobjAdmin->clean($keyword_new) . "%') AND ePreferenceFor = '".$ePreferenceFor."' AND eStatus = '" . $generalobjAdmin->clean($eStatus) . "')";
			}
			elseif ($ePreferenceFor != "" && $eStatus == "") {
				$ssql .= " WHERE ((tTitle LIKE '%" . $generalobjAdmin->clean($keyword_new) . "%' OR tDescription LIKE '%" . $generalobjAdmin->clean($keyword_new) . "%') AND ePreferenceFor = '".$ePreferenceFor."')";
			}
			elseif ($ePreferenceFor == "" && $eStatus != "") {
				$ssql .= " WHERE ((tTitle LIKE '%" . $generalobjAdmin->clean($keyword_new) . "%' OR tDescription LIKE '%" . $generalobjAdmin->clean($keyword_new) . "%') AND eStatus = '" . $generalobjAdmin->clean($eStatus) . "')";
			}
			else {
				$ssql .= " WHERE (tTitle LIKE '%" . $generalobjAdmin->clean($keyword_new) . "%' OR tDescription LIKE '%" . $generalobjAdmin->clean($keyword_new) . "%')";
			}
	    }
	}
	elseif ($ePreferenceFor != '' && $eStatus != "") {
        $ssql .= " WHERE (ePreferenceFor = '".$ePreferenceFor."' AND eStatus = '" . $generalobjAdmin->clean($eStatus) . "')";
    } elseif ($ePreferenceFor != "" && $eStatus == "") {
		$ssql .= " WHERE ePreferenceFor = '".$ePreferenceFor."'";
	}
	elseif ($ePreferenceFor == "" && $eStatus != "") {
		$ssql .= " WHERE eStatus = '" . $generalobjAdmin->clean($eStatus) . "'";
	}

	if($ssql != "")
	{
		$ssql .= " AND is_deleted = 0";
	}
	else{
		$ssql = " WHERE is_deleted = 0";
	}
	
    $sql = "SELECT iPreferenceId,JSON_UNQUOTE(JSON_EXTRACT(tTitle, '$.tTitle_".$default_language."')) as tTitle, JSON_UNQUOTE(JSON_EXTRACT(tDescription, '$.tDescription_".$default_language."')) as tDescription, ePreferenceFor, eImageUpload, iDisplayOrder, eContactLess, eStatus FROM ".$tbl_name." $ssql $ord";

    $data_drv = $obj->MySQLSelect($sql);
?>
<!DOCTYPE html>
<html lang="en">
    <!-- BEGIN HEAD-->
    <head>
        <meta charset="UTF-8" />
        <title><?=$SITE_NAME?> | <?= $langage_lbl_admin['LBL_DELIVERY_PREF'] ?> </title>
        <meta content="width=device-width, initial-scale=1.0" name="viewport" />
        <?php include_once('global_files.php');?>
    </head>
    <!-- END  HEAD-->
    <!-- BEGIN BODY-->
    <body class="padTop53 " >
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
                                <h2><?= $langage_lbl_admin['LBL_DELIVERY_PREF'] ?></h2>
                            </div>
                        </div>
                        <hr />
                    </div>
                    <?php include('valid_msg.php'); ?>
                    
                    <form name="frmsearch" id="frmsearch" action="javascript:void(0);" method="post">
					    <table width="100%" border="0" cellpadding="0" cellspacing="0" class="admin-nir-table">
					        <tbody>
					            <tr>
					                <td width="1%"><label for="textfield"><strong>Search:</strong></label></td>
					                <td width="3%" class=" padding-right10">
					                    <select name="option" id="option" class="form-control">
					                        <option value="">All</option>
					                        <option value="tTitle">Name</option>
					                        <option value="tDescription">Description</option>
					                    </select>
					                </td>
					                <td width="10%" class="searchform"><input type="Text" id="keyword" name="keyword" value="<?php echo $keyword; ?>"  class="form-control" /></td>
					                <td width="6%">
					                    <select name="ePreferenceFor" id="ePreferenceFor" class="form-control">
					                        <option value="" >Select Delivery Preference</option>
					                        <option value="Store" <?php
					                            if ($ePreferenceFor == 'Store') {
					                                echo "selected";
					                            }
					                            ?> >Store</option>
					                        <option value="Provider" <?php
					                            if ($ePreferenceFor == 'Provider') {
					                                echo "selected";
					                            }
					                            ?> >Provider</option>
					                    </select>
					                </td>
					                <td width="4%">
					                    <select name="eStatus" id="eStatus" class="form-control">
					                        <option value="" >Select Status</option>
					                        <option value="Active" <?php
					                            if ($eStatus == 'Active') {
					                                echo "selected";
					                            }
					                            ?> >Active</option>
					                        <option value="Inactive" <?php
					                            if ($eStatus == 'Inactive') {
					                                echo "selected";
					                            }
					                            ?> >Inactive</option>
					                    </select>
					                </td>
					                <td width="7%">
					                    <input type="submit" value="Search" class="btnalt button11" id="Search" name="Search" title="Search" />
					                    <input type="button" value="Reset" class="btnalt button11" onClick="window.location.href='delivery_preferences.php'"/>
					                </td>
					                <td width="10%">
					                	<a class="add-btn" href="delivery_preference_action.php" style="text-align: center;">Add Delivery Preference </a>
					                </td>
					            </tr>
					        </tbody>
					    </table>
					</form>
					<table width="20%" border="0" cellpadding="0" cellspacing="0" class="admin-nir-table" style="margin-top: 20px">
						<tbody>
							<tr>
								<td width="1%" style="vertical-align: middle;"><label for="textfield"><strong>Bulk Action:</strong></label></td>
								<td width="5%">
									<?php //if ($userObj->hasPermission(['update-status-users', 'delete-users'])) { ?>
							            <select name="changeStatus" id="changeStatus" class="form-control" onChange="ChangeStatusAll(this.value);">
							                <option value="" >Select Action</option>
							                <?php //if ($userObj->hasPermission('update-status-users')) { ?>
							                <option value='Active' <?php
							                    if ($option == 'Active') {
							                        echo "selected";
							                    }
							                    ?> >Activate</option>
							                <option value="Inactive" <?php
							                    if ($option == 'Inactive') {
							                        echo "selected";
							                    }
							                    ?> >Deactivate</option>
							                <?php //} ?>
							                <?php //if ($eStatus != 'Deleted' && $userObj->hasPermission('delete-users')) { ?>
							                <option value="Deleted" <?php
							                    if ($option == 'Delete') {
							                        echo "selected";
							                    }
							                    ?> >Delete</option>
							                <?php //} ?>
							            </select>
							        <?php //} ?>
								</td>
							</tr>
						</tbody>
					</table>

                    <div class="table-list">
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="table-responsive">
                                	<form class="_list_form" id="_list_form" method="post" action="<?php echo $_SERVER['PHP_SELF'] ?>">
	                                    <table class="table table-striped table-bordered table-hover">
	                                        <thead>
	                                            <tr>
	                                            	<th width="1%" style="text-align:center;"><input type="checkbox" id="setAllCheck"></th>
	                                                <th width="8%">Name</th>
	                                                <th width="20%">Description</th>
	                                                <th width="5%">Delivery Preference For</th>
	                                                <th width="5%">Contactless Delivery</th>
	                                                <th width="5%" style="text-align:center;"><a href="javascript:void(0);" onClick="Redirect(1,<?php if($sortby == '1'){ echo $order; }else { ?>0<?php } ?>)">Display Order <?php if ($sortby == 1) { if($order == 1) { ?><i class="fa fa-sort-amount-asc" aria-hidden="true"></i> <?php } else { ?><i class="fa fa-sort-amount-desc" aria-hidden="true"></i><?php } }else { ?><i class="fa fa-sort" aria-hidden="true"></i> <?php } ?></a></th>
	                                                <th width="5%" align="center" style="text-align:center;">Status </th>
	                                                <th width="5%" align="center" style="text-align:center;">Action</th>
	                                            </tr>
	                                        </thead>
	                                        <tbody>
	                                            <?php 
	                                                if(!empty($data_drv)) {
	                                                for ($i = 0; $i < count($data_drv); $i++) { 
	                                                    ?>
	                                            <tr class="gradeA">
	                                            	<td style="text-align:center;">
	                                            		<input type="checkbox" name="delivery_preference_ids[]" value="<?= $data_drv[$i]['iPreferenceId'] ?>">
	                                            	</td>
	                                                <td><?= $data_drv[$i]['tTitle'] ?></td>
	                                                <td><?= $data_drv[$i]['tDescription'] ?></td>
	                                                <td><?= $data_drv[$i]['ePreferenceFor'] ?></td>
	                                                <td><?= $data_drv[$i]['eContactLess'] ?></td>
	                                                <td align="center" style="text-align:center;">
	                                                    <?php echo $data_drv[$i]['iDisplayOrder'] ?>
	                                                </td>
	                                                <td align="center" style="text-align:center;">
	                                                    <?php if($data_drv[$i]['eStatus'] == 'Active') {
	                                                        $dis_img = "img/active-icon.png";
	                                                        }else {
	                                                        $dis_img = "img/inactive-icon.png";
	                                                        }?>
	                                                    <img src="<?= $dis_img; ?>" alt="<?=$data_drv[$i]['eStatus'];?>" data-toggle="tooltip" title="<?=$data_drv[$i]['eStatus'];?>">
	                                                </td>
	                                                <td align="center" style="text-align:center;" class="action-btn001">
	                                                    <div class="share-button openHoverAction-class" style="display: block;">
	                                                        <label class="entypo-export"><span><img src="images/settings-icon.png" alt=""></span></label>
	                                                        <div class="social show-moreOptions openPops_<?= $data_drv[$i]['iPreferenceId']; ?>">
	                                                            <ul>
	                                                                <li class="entypo-twitter" data-network="twitter">
	                                                                	<a href="delivery_preference_action.php?id=<?= $data_drv[$i]['iPreferenceId']; ?>" data-toggle="tooltip" title="Edit">
	                                                                    	<img src="img/edit-icon.png" alt="Edit">
	                                                                    </a>
	                                                                </li>
	                                                                <li class="entypo-facebook" data-network="facebook">
	                                                                	<a href="javascript:void(0);" onclick="changeStatus('<?php echo $data_drv[$i]['iPreferenceId']; ?>','Inactive')"  data-toggle="tooltip" title="Activate">
	                                                                    	<img src="img/active-icon.png" alt="<?php echo $data_drv[$i]['eStatus']; ?>" >
	                                                                    </a>
	                                                                </li>
	                                                                <li class="entypo-gplus" data-network="gplus">
	                                                                	<a href="javascript:void(0);" onclick="changeStatus('<?php echo $data_drv[$i]['iPreferenceId']; ?>','Active')" data-toggle="tooltip" title="Deactivate">
	                                                                    	<img src="img/inactive-icon.png" alt="<?php echo $data_drv[$i]['eStatus']; ?>" >    
	                                                                    </a>
	                                                                </li>
	                                                                <?php if($data_drv[$i]['eContactLess'] == "No") { ?>
	                                                                <li class="entypo-gplus" data-network="gplus">
	                                                                	<a href="javascript:void(0);" data-toggle="tooltip" title="" data-original-title="Delete" onclick="changeStatusDelete('<?php echo $data_drv[$i]['iPreferenceId']; ?>')">
                                                                        	<img src="img/delete-icon.png" alt="Delete">
                                                                        </a>
                                                                	</li>
                                                                	<? } ?>
	                                                            </ul>
	                                                        </div>
	                                                    </div>
	                                                </td>
	                                            </tr>
	                                            <?php } }else { ?>
	                                            <tr class="gradeA">
	                                                <td colspan="8"> No Records Found.</td>
	                                            </tr>
	                                            <?php } ?>
	                                        </tbody>
	                                    </table>
	                                </form>
	                                <?php include('pagination_n.php'); ?>
                                </div>
                            </div>
                            <!--TABLE-END-->
                        </div>
                    </div>
                    <div class="admin-notes">
                        <h4>Notes:</h4>
                        <ul>
                            <li>Delivery Preference module will list all data added by the admin on this page.</li>
                            <li>Administrator can Activate / Deactivate / Delete any Delivery Preference data.</li>
                            <li>All the data added under this Delivery Preference module would be shown on the cart page of Web and Mobile Apps.</li>
                            <li>5 Records (configurable) are including the default record.</li>
                        </ul>
                    </div>
                </div>
            </div>
            <!--END PAGE CONTENT -->
        </div>
        <form name="pageForm" id="pageForm" action="action/delivery_preferences.php" method="post" >
		<input type="hidden" name="sortby" id="sortby" value="<?php echo $sortby; ?>" >
		<input type="hidden" name="order" id="order" value="<?php echo $order; ?>" >
		<input type="hidden" name="iPreferenceId" id="iMainId01" value="" >
		<input type="hidden" name="status" id="status01" value="" >
		<input type="hidden" name="statusVal" id="statusVal" value="" >
		<input type="hidden" name="method" id="method" value="" >
		</form>
        <!--END MAIN WRAPPER -->
        <?php
            include_once('footer.php');
            ?>
        <script>
        	$("#Search").on('click', function(){
                var action = $("#_list_form").attr('action');
                var formValus = $("#frmsearch").serialize();
                window.location.href = action+"?"+formValus;
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
            
            $("#setAllCheck").on('click', function () {
                if ($(this).prop("checked")) {
                    $("#_list_form input[type=checkbox]").each(function () {
                        if ($(this).attr('disabled') != 'disabled') {
                            this.checked = 'true';
                        }
                    });
                } else {
                    $("#_list_form input[type=checkbox]").each(function () {
                        this.checked = '';
                    });
                }
            });
        </script>
    </body>
    <!-- END BODY-->
</html>